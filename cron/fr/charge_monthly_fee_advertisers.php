<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
$db = DBHandle::get_instance();
// last month timestamps
$lastMonthStart = mktime(0,0,0,date('m')-1,1);
$lastMonthEnd = mktime(0,0,0,date('m'),1)-1;

//$lastMonthStart = mktime(date('H')-1,0,0);
//$lastMonthEnd = mktime(date('H')+1,0,0)-1;


setlocale(LC_TIME, 'fr_FR.UTF8');
$month = strftime('%B', mktime(0,0,0,date('m')-1,1));

	$prep_month = (stripos('aeiouy',substr($month,0,1)) === false ? "de " : "d'") . $month;
 
	 
	$sql_date_month  = "SELECT DATE_FORMAT( DATE_SUB( NOW( ) , INTERVAL 1 MONTH ) ,  '%Y-%m' ) as date_month";
	$req_date_month  =  mysql_query($sql_date_month);
	$date_date_month =  mysql_fetch_object($req_date_month);
	
	$date_explode  = explode('-',$date_date_month->date_month);
	$mois = $date_explode[1];
	
	switch($mois) {
    case '01': $mois = 'Janvier'; break;
    case '02': $mois = 'Février'; break;
    case '03': $mois = 'Mars'; break;
    case '04': $mois = 'Avril'; break;
    case '05': $mois = 'Mai'; break;
    case '06': $mois = 'Juin'; break;
    case '07': $mois = 'Juillet'; break;
    case '08': $mois = 'Août'; break;
    case '09': $mois = 'Septembre'; break;
    case '10': $mois = 'Octobre'; break;
    case '11': $mois = 'Novembre'; break;
    case '12': $mois = 'Decembre'; break;
    default: $mois =''; break;
	}
	
    $prep_month_final  =  $mois.' '.$date_explode[0];
	
// set all chargeable lead to charged lead for the last month


// charged lead status list
$chargedStatusList = array();
$statusConstList = array_keys(Contacts::$statusList);
foreach ($statusConstList as $statusConst)
  if ($statusConst & Contacts::STATUS_IS_CHARGED)
    $chargedStatusList[] = $statusConst;

// get due sum for each advertiser for this same month
$adv_list = Doctrine_Query::create()
  ->select('a.id AS adv_id,
            c.login AS email,
            a.nom1 AS societe,
            a.adresse1 AS adresse,
            a.adresse2 AS cadresse,
            a.ville,
            a.category,
            a.cp,
            a.pays,
            a.tel1 AS tel,
            a.fax1 AS fax,
            a.nom1 AS societe2,
            a.adresse1 AS adresse2,
            a.adresse2 AS cadresse2,
            a.ville AS ville2,
            a.cp AS cp2,
            a.pays AS pays2,
            a.tel1 AS tel2,
            a.fax1 AS fax2,
            a.url,
            a.is_fields,
            a.client_id,
            a.direct_debit,
            a.idCommercial,
            c.code AS code,
            COUNT(l.id) AS lead_count,
            SUM(l.income) AS total_ht')
  ->from('Advertisers a')
  ->innerJoin('a.contacts l')
  ->innerJoin('a.client c')
  ->where('a.category =2 ')
  // ->andWhere(implode(' OR ',array_fill(0,count($chargedStatusList),'l.invoice_status = ?')), $chargedStatusList)
  ->andWhere('l.timestamp >= ? AND l.timestamp <= ?', array($lastMonthStart, $lastMonthEnd))
  ->groupBy('a.id')
  ->fetchArray();

$tva_rates = Doctrine_Query::create()->select('*')->from('Tva')->fetchArray();
foreach ($tva_rates as $tva)
  $tvasById[$tva['id']] = $tva;

// load common virtual product
$vp = Doctrine_Query::create()
  ->select('id AS idtc, sup_id, refSupplier AS sup_ref, label_long AS desc, idTVA AS tva_code')
  ->from('ReferencesContent')
  ->where('id = ?', 1)
  ->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);

// get latest rid
$latest_rid = Doctrine_Query::create()
  ->select("rid")
  ->from("Invoice")
  ->where("type = ?", Invoice::TYPE_INVOICE)
  ->orderBy("rid DESC")
  ->fetchOne(array(), Doctrine_Core::HYDRATE_SINGLE_SCALAR);

// create invoices
$issued = mktime(0,0,0);
$ic = new Doctrine_Collection('Invoice');
foreach ($adv_list as $adv) {
  if (!is_array($adv['is_fields'])) // when array hydrating, ATTR_AUTO_ACCESSOR_OVERRIDE doesn't change the data for is_fields, so we dot it here for the time being
    $adv['is_fields'] = mb_unserialize($adv['is_fields']);
	// print_r($adv['is_fields'] ); 
	
	if ($adv['is_fields'][0]['type'] == 'forfeit'){		
		if(!empty($adv['is_fields'][0]['fields']->forfeit_amount) && ($adv['is_fields'][0]['fields']->forfeit_periodicity == 'month')){
			
			$lead_cost = $adv['is_fields'][0]['fields']->lead_unit_cost;
			$mtn_forfait = $adv['is_fields'][0]['fields']->forfeit_amount;
			// echo "ID ".$adv['adv_id'].' Nom  : '.$adv['societe'].' Category : '.$adv['category'].'<br />';  
			// echo "lead cost ".$lead_cost.'<br />';
			// echo "forfait montant : ".$adv['is_fields'][0]['fields']->forfeit_amount.'<br />';
			// echo "Type : ".$adv['is_fields'][0]['type'].'<br />';
			// echo "Périodicité : ".$adv['is_fields'][0]['fields']->forfeit_periodicity.'<br />';
			
						
			  $isFrench = preg_match('/\bfrance\b/i', $adv['pays']);
			  unset($adv['id']); // don't copy the id field
			  $user = new BOUser($adv['idCommercial']);
			  $i = new Invoice();
			  $i->fromArray($adv);
			  $i->activity = $isFrench ? Invoice::ACTIVITY_ANNONCEUR : Invoice::ACTIVITY_ANNONCEUR_INTRA;
			  $i->payment_mode = Invoice::PAYMENT_MODE_30_DAYS_INVOICING;
			  
			  if($adv['direct_debit']== ''){
				  $i->payment_mean = 20;
			  }else if($adv['direct_debit'] == 0 ){
				  $i->payment_mean = 0;
			  }else if($adv['direct_debit'] == 1 ){
				  $i->payment_mean = 1;
			  }else if($adv['direct_debit'] == 2 ){
				  $i->payment_mean = 2;
			  }else if($adv['direct_debit'] == 3 ){
				  $i->payment_mean = 3;
			  }else if($adv['direct_debit'] == 4 ){
				  $i->payment_mean =4;
			  }else if($adv['direct_debit'] == 5 ){
				  $i->payment_mean = 5;
			  }else if($adv['direct_debit'] == 10 ){
				  $i->payment_mean =10;
			  }else if($adv['direct_debit'] == 20 ){
				  $i->payment_mean = 20;
			  }else if($adv['direct_debit'] == 30 ){
				  $i->payment_mean = 30;
			  }else if($adv['direct_debit'] == 40 ){
				  $i->payment_mean = 40;
			  }else if($adv['direct_debit'] == 50 ){
				  $i->payment_mean = 50;
			  }else if($adv['direct_debit'] == 60 ){
				  $i->payment_mean = 60;
			  }else if($adv['direct_debit'] == 70 ){
				  $i->payment_mean = 70;
			  } 
			  
			  // echo "ID ".$adv['adv_id'].' Nom  : '.$adv['societe'].' Direct debit :  '.$i->payment_mean.'<br />';
			  // $i->payment_mean = $adv['direct_debit'] == 1 ? Invoice::PAYMENT_BANKER_ORDER : Invoice::PAYMENT_MEAN_CHEQUE;
			  $i->total_ht = $mtn_forfait;
			  $i->total_tva = $isFrench ? round($i->total_ht * $tvasById[1]['taux']/100,2) : 0;
			  $i->total_ttc = $i->total_ht + $i->total_tva;
			  
			 
			  
			  // add a line
			  $il = new InvoiceLine();
			  $il->pdt_ref_id = $vp['idtc'];
			  $il->sup_id = $vp['sup_id'];
			  $il->sup_ref = $vp['sup_ref'];
			  $il->desc = "Leads Techni-Contact periode du mois ".$prep_month_final;
			  $il->tva_code = $isFrench ? $vp['tva_code'] : 5;
			  //$il->pau_ht = $lead_cost;
			  $il->pu_ht = $mtn_forfait;
			  // $il->quantity = $adv['lead_count'];
			  $il->quantity = 1;
			  $il->total_ht = $i->total_ht;
			  $il->total_tva = $i->total_tva;
			  $il->total_ttc = $i->total_ttc;
			  $i->lines[] = $il;
			  
			  // valid it
			  $i->status = Invoice::STATUS_VALIDATED;
			  $i->issued = $issued;
			  $i->due_date = $issued + 29*86400; // 29 days
			  $i->rid = ++$latest_rid;
			  
			  // add it to the collection
			  $ic[] = $i;				
		}		
	}
}

//pp($ic->toArray());
//exit();
// save collection (set id's and web_id's)

$ic->save();


// then send mails
foreach ($ic as $i)
  $i->sendMail(Invoice::STATUS_NOT_VALIDATED);


// pp(count($ic)." factures générées");
