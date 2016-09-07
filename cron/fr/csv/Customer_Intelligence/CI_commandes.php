<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$db = DBHandle::get_instance();

$upload = true;
function no_entities($string) {
  $string = str_replace(array("Æ", "æ", "Œ", "œ"), array("AE", "ae", "OE", "oe"), $string);
  $accent   = "ŠšŸŽžƒ€¢£¥§©ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÚÛÜÝÞßàáâãäåçèéêëìíîïðñòóôõöøùúûüýþÿ";
  $noAccent = "SsYZzfEcLYScAAAAAACEEEEIIIIDNOOOOOxOUUUUYBBaaaaaaceeeeiiiidnoooooouuuuyby";
  $string = utf8_decode($string);
  $string = strtr($string, $accent, $noAccent);
  return $string;
}
function no_space_entities($string) {
  $string = preg_replace('/-{2,}/', '-', preg_replace("/[^a-z0-9-]/i", "-", strtolower(no_entities(trim($string)))));
  return preg_replace(array('/^-{1,}/', '/-{1,}$/'), array('', ''), $string);
}
require_once(DOCTRINE_MODEL_PATH.'Order.php');
require_once(DOCTRINE_MODEL_PATH.'Estimate.php');

$flog = fopen(CSV_PATH."Customer_Intelligence/CI_upload_historic.log", "a+");
fwrite($flog, date("Y-m-d H:i:s")." SESSION BEGIN\n");

/* status list */
$statusList = array(
  'Lead_F' => array(
      __LEAD_P_STATUS_NOT_PROCESSED__ => 'F1',
      __LEAD_P_STATUS_PROCESSED__ => 'F2',
      __LEAD_P_STATUS_NOT_PROCESSABLE__ => 'F3'
  ),
  'Lead_A' => array(
      __LEAD_INVOICE_STATUS_NOT_CHARGED__ => 'A1',
      __LEAD_INVOICE_STATUS_CHARGEABLE__ => 'A2',
      __LEAD_INVOICE_STATUS_CHARGED__ => 'A3',
      __LEAD_INVOICE_STATUS_CHARGED_PERMANENT__ => 'A4',
      __LEAD_INVOICE_STATUS_REJECTED__ => 'A5',
      __LEAD_INVOICE_STATUS_REJECTED_WAIT__ => 'A6',
      __LEAD_INVOICE_STATUS_CHARGEABLE_REJECTED_WAIT__ => 'A7',
      __LEAD_INVOICE_STATUS_REJECTED_REFUSED__ => 'A8',
      __LEAD_INVOICE_STATUS_DOUBLET__ => 'A9',
      __LEAD_INVOICE_STATUS_IN_FORFEIT__ => '1A',
      __LEAD_INVOICE_STATUS_CREDITED__ => '2A',
      __LEAD_INVOICE_STATUS_DISCHARGED__ => '3A'
  ),
  'Commande' => array(
      Order::GLOBAL_PROCESSING_STATUS_WAITING_PAYMENT_VALIDATION => 'C1',
      Order::GLOBAL_PROCESSING_STATUS_WAITING_PROCESSING => 'C2',
      Order::GLOBAL_PROCESSING_STATUS_PROCESSING => 'C3',
      Order::GLOBAL_PROCESSING_STATUS_ASS_OPEN => 'C4',
      Order::GLOBAL_PROCESSING_STATUS_ASS_CLOSED => 'C5',
      Order::GLOBAL_PROCESSING_STATUS_FORECAST_SHIPPING_DATE => 'C6',
      Order::GLOBAL_PROCESSING_STATUS_PARTLY_SHIPPED => 'C7',
      Order::GLOBAL_PROCESSING_STATUS_SHIPPED => 'C8',
      Order::GLOBAL_PROCESSING_STATUS_PARTLY_CANCELED => 'C9',
      Order::GLOBAL_PROCESSING_STATUS_CANCELED => '1C'
  ),
  'Devis_manager' => array(
      Estimate::STATUS_IN_PROCESS => 'D1',
      Estimate::STATUS_SENT => 'D2',
      Estimate::STATUS_UPDATED => 'D3',
      Estimate::STATUS_WON => 'D4',
      Estimate::STATUS_LOST => 'D5'
  )
);

$salesChannelCorrespondance = array(
  "telephone-entrant" => 'TE',
  "telephone-sortant" => 'TS',
  "chat" => 'Chat',
  "mail" => 'Mail',
  "campagne-d-appels" => 'CA'
);

$fields = array(
  //"Record_Type" => array("type" => "varchar", "length" => 1),
  "EmailAddress" => array("type" => "varchar", "length" => 255),
  "ProductCode" => array("type" => "varchar", "length" => 20),
  "Date" => array("type" => "datetime", "length" => 20),
  "AmountPaid" => array("type" => "float", "length" => 0),
  "Sales_channel" => array("type" => "varchar", "length" => 10),
  "Location" => array("type" => "varchar", "length" => 30),
  "OrderHeaderNumber" => array("type" => "varchar", "length" => 20),
  "Status" => array("type" => "varchar", "length" => 2),
  "CountItems" => array("type" => "int", "length" => 0),
  "PromotionCode" => array("type" => "varchar", "length" => 20),
  "UserField1" => array("type" => "varchar", "length" => 20),
  "UserField2" => array("type" => "varchar", "length" => 20),
  "UserField3" => array("type" => "varchar", "length" => 20),
  "UserField4" => array("type" => "varchar", "length" => 20),
  "UserField5" => array("type" => "varchar", "length" => 20)
);


// Opening file for write
$path = CSV_PATH."Customer_Intelligence/upload/";
$csvfilename = date("Ymd")."_commandes.csv";
$csvPath = $path.$csvfilename;
$zipfilename = "6R33F75372-9D6F-4B66-9988-9408CD3D2C15.zip";
$zipPath = CSV_PATH."Customer_Intelligence/upload/".$zipfilename;
if (is_file($zipPath))
  unlink ($zipPath);
fwrite($flog, date("Y-m-d H:i:s")." CREATING FILE : ".$csvfilename."\n");
$fh = fopen($csvPath, "w+");


// Writing col header
fwrite($fh, "\xEF\xBB\xBF".implode("\t", array_keys($fields)));

$time_current = $time_current_leads = $time_current_devis = time();
$time_interval = $time_interval_leads = $time_interval_devis = 86400*30;
$time_origin = mktime(0,0,0,3,1,2005);

fwrite($flog, date("Y-m-d H:i:s")." WRITING FILE : ".$csvfilename."\n");

/* ESTIMATES */
$action_type = 'Devis_manager';
$time_start = $time_origin;
$time_end = $time_origin + $time_interval;
while ($time_start <= $time_current) {
  set_time_limit(60);
  $ec = Doctrine_Query::create()
    ->select('e.id,
              e.campaign_id,
              e.created,
              e.fdp_ht,
              e.payment_mean,
              e.status,
              e.email,
              cu.name AS created_user_name,
              el.id,
              el.pdt_id,
              el.pdt_ref_id,
              el.quantity,
              el.total_ht,
              l.id AS lead_id,
              l.origin AS lead_origin,
              "Probance" AS lead_campaign_type,
              lcu.name AS lead_created_user_name')
    ->from('Estimate e')
    ->innerJoin('e.lines el')
    ->leftJoin('e.lead l')
    ->leftJoin('e.created_user cu')
    ->leftJoin('l.created_user lcu')
    ->where('e.updated >= ? AND e.updated < ?', array($time_start, $time_end))
    ->fetchArray();
  
  foreach ($ec as $e) {
    
    if ($e['fdp_ht'] > 0) { // fdp as a line
      $e['lines'][] = array(
        'pdt_id' => 100,
        'pdt_ref_id' => 100,
        'quantity' => 1,
        'total_ht' => $e['fdp_ht']
      );
    }
    $Sales_channel = "";
    if (!empty($e['lead_origin'])) {
      if ($e['lead_origin'] == 'Probance')
        $Sales_channel = $e['campaign_type'];
      elseif ($e['lead_origin'] == 'Internaute')
        $Sales_channel = 'Internaute';
      else
        $Sales_channel = $salesChannelCorrespondance[no_space_entities($e['lead_origin'])];
    }
    
    $Location = "";
    if (!empty($e['lead_id'])) {
      
    }
    
    foreach ($e['lines'] as $line) {
      $el = array(
        'EmailAddress' => $e['email'],
        'ProductCode' => $line['pdt_ref_id'],
        'Date' => $e['created'],
        'AmountPaid' => $line['total_ht'],
        'Sales_channel' => $Sales_channel,
        'Location' => empty($e['lead_created_user_name']) ? "Internaute" : $e['lead_created_user_name'],
        'OrderHeaderNumber' => $e['id'],
        'Status' => $statusList[$action_type][$e['status']],
        'CountItems' => $line['quantity'],
        'PromotionCode' => "",
        'Type_Action' => $action_type,
        'Commercial' => $e['created_user_name'],
        'CampaignID' => $e['campaign_id'],
        'UserField4' => $e['payment_mean'],
        'UserField5' => "");

      foreach($el as $k => &$v) {
        switch($fields[$k]['type']) {
          case 'int': $v = (int)$v; break;
          case 'float': $v = (float)$v; break;
          case 'varchar': $v = preg_replace(array('/\r\n/','/\n/','/\r/','/\t/','/"/','/\\\\$/'), "", trim(substr($v,0,$fields[$k]['length']))); break;
          case 'datetime': $v = empty($v)?"\"\"":date('Y-m-d H:i:s', $v); break;
          default: break;
        }
        if ($v === "\"\"" && $k != 'Location')
          $v = " ";
      }
      unset($v);

      mb_convert_variables('UTF-8', 'ASCII,UTF-8,ISO-8859-1', $el);
      fwrite($fh, "\r\n".implode("\t", $el));
    }
  }

  $time_start += $time_interval;
  $time_end += $time_interval;
}


/* ORDERS */
// Pour une commande de plusieurs produits, il faut une ligne par produit commandé
//11/01/2012(15:40:18) Tristan HENRY-GREARD: ok donc on considère que les commandes passent toujours par un devis commercial au préalable.
// Quelques rares commandes pourrons êtres intégrées directement mais cela ne rentrera pas dans le domaine commercial
$action_type = 'Commande';
$time_start = $time_origin;
$time_end = $time_origin + $time_interval;
while ($time_start <= $time_current) {
  set_time_limit(60);
  $oc = Doctrine_Query::create()
    ->select('o.id,
              o.campaign_id,
              o.created,
              o.fdp_ht,
              o.promotion_code,
              o.payment_mean,
              o.payment_status,
              o.processing_status,
              icu.name AS in_charge_user_name,
              IF(o.email<>\'\',o.email,c.email) AS email,
              ol.id,
              ol.pdt_id,
              ol.pdt_ref_id,
              ol.quantity,
              ol.total_ht')
    ->from('Order o')
    ->innerJoin('o.lines ol')
    ->innerJoin('o.client c')
    ->leftJoin('o.in_charge_user AS icu')
    ->where('o.validated >= ? AND o.validated < ?', array($time_start, $time_end))
    ->fetchArray();
  
  foreach ($oc as $o) {
    
    if ($o['fdp_ht'] > 0) { // fdp as a line
      $o['lines'][] = array(
        'pdt_id' => 100,
        'pdt_ref_id' => 100,
        'quantity' => 1,
        'total_ht' => $o['fdp_ht']
      );
    }

    foreach ($o['lines'] as $line) {
      $ol = array(
        'EmailAddress' => $o['email'],
        'ProductCode' => $line['pdt_ref_id'],
        'Date' => $o['created'],
        'AmountPaid' => $line['total_ht'],
        'Sales_channel' => '',
        'Location' => "Internaute",
        'OrderHeaderNumber' => $o['id'],
        'Status' => $statusList[$action_type][$o['processing_status']],
        'CountItems' => $line['quantity'],
        'PromotionCode' => $o['promotion_code'],
        'Type_Action' => $action_type,
        'Commercial' => empty($e['in_charge_user_name']) ? "" : $e['in_charge_user_name'],
        'CampaignID' => $o['campaign_id'],
        'UserField4' => $o['payment_mean'],
        'UserField5' => $o['payment_status']);

      foreach($ol as $k => &$v) {
        switch($fields[$k]['type']) {
          case 'int': $v = (int)$v; break;
          case 'float': $v = (float)$v; break;
          case 'varchar': $v = preg_replace(array('/\r\n/','/\n/','/\r/','/\t/','/"/','/\\\\$/'), "", trim(substr($v,0,$fields[$k]['length']))); break;
          case 'datetime': $v = empty($v)?"\"\"":date('Y-m-d H:i:s', $v); break;
          default: break;
        }
        if ($v === "\"\"" && $k != 'Location')
          $v = " ";
      }
      unset($v);

      mb_convert_variables('UTF-8', 'ASCII,UTF-8,ISO-8859-1', $ol);
      fwrite($fh, "\r\n".implode("\t", $ol));
    }
  }

  $time_start += $time_interval;
  $time_end += $time_interval;
}


/* LEADS */
$time_start = $time_origin;
$time_end = $time_origin + $time_interval;
while ($time_start <= $time_current_leads) {
  set_time_limit(60);
  // sql query
  $resLead = $db->query("
    SELECT
      o.id,
      o.timestamp,
      o.reject_timestamp,
      o.create_time,
      o.campaignID,
      o.customFields AS items,
      o.email,
      o.origin,
      o.income_total,
      o.id_user,
      o.processing_status,
      o.invoice_status,
      bou.name AS bou_name,
      a.category,
      p.idTC AS idTC,
      p.id AS ProductId,
      mc.nom AS campaign_name,
      mct.type AS campaign_type
    FROM contacts o
    INNER JOIN products p ON o.idProduct = p.id
    LEFT JOIN advertisers a ON o.idAdvertiser = a.id
    LEFT JOIN bo_users bou ON bou.id = o.id_user
    LEFT JOIN mkt_campaigns mc ON o.campaignID = mc.id
    LEFT JOIN mkt_campaigns_type mct ON mc.id_mkt_campaigns_type = mct.id
    WHERE (o.timestamp >= ".$time_start." AND o.timestamp < ".$time_end.")", __FILE__, __LINE__);

  while($o = $db->fetchAssoc($resLead)) {

    $oln = 1; // order line num
    // for first record, all lead are inserted
    // $Record_Type = "I";
    //$Record_Type =  $o["timestamp"]-$o["create_time"] > 86400 ? "U" : ($o["reject_timestamp"]-$o["create_time"] > 86400 ? "U" :"I");
    $action_type = $o['category'] == __ADV_CAT_SUPPLIER__ ? 'Lead_F' : 'Lead_A' ;
    $status = $action_type == 'Lead_F' ? $statusList[$action_type][$o['processing_status']] : $statusList[$action_type][$o['invoice_status']];

    //si lead fournisseur productcode = 1ère ligne de ref content correspondant au produit
    //ajout de A devant productcode pour lead sur produit annonceur (13/04/2012 OD)
    switch($o['category']){
      case __ADV_CAT_ADVERTISER__:
        $productCode = 'A'.$o["idTC"];
        break;
      case __ADV_CAT_SUPPLIER__:
        $res2 = $db->query("select id from references_content where idProduct = ".$o["ProductId"]." LIMIT 1");
        $ret2 = $db->fetchArray($res2);

        // pour les leads fournisseurs n'ayant pas de id renference_content, on reprend l'idTC  (13/04/2012 OD)
        $productCode = empty($ret2['id']) ? $o["idTC"] : $ret2['id'];
        break;
     default:
       $productCode = 'A'.$o["idTC"];
       break;
    }

    unset($o["ProductId"]);
//		foreach($items as $item) {
      // order line
      if(!empty($o['origin'])){
        if($o['origin'] == 'Probance')
          $sh = $o['campaign_type'];
        elseif($o['origin'] == 'Internaute')
          $sh = 'Internaute';
        else
          $sh = $salesChannelCorrespondance[no_space_entities($o['origin'])];
      }else
        $sh = '';
        
      $ol = array(
        //"Record_Type" => $Record_Type,//$o["id"],
        "EmailAddress" => $o["email"],//$o["id"]."-".$oln,
        "ProductCode" => $productCode,
        "Date" => $o["timestamp"],
        "AmountPaid" =>$o["income_total"],// $o["totalHT"],
        "Sales_channel" => $sh,
        "Location" => $o["bou_name"],
        "OrderHeaderNumber" => $o["id"],//$item["idTC"],
        "Status" => $status,
        "CountItems" => 1,//empty($o["promotionCode"]) ? 0 : 1,
        "PromotionCode" => "",
        "Type_Action" => $action_type,//$TypePaiementList[$o["type_paiement"]]
        "Commercial" => "", // id_user, réservé devis…
        "CampaignID" => $o['campaignID'],
        "UserField4" => "",
        "UserField5" => "");

      foreach($ol as $k => &$v) {
        switch($fields[$k]["type"]) {
          case "int": $v = (int)$v; break;
          case "float": $v = (float)$v; break;
          case "varchar": $v = preg_replace(array("/\r\n/","/\n/","/\r/","/\t/",'/"/',"/\\\\$/"), "", trim(substr($v,0,$fields[$k]["length"]))); break;
          //case "varchar": $v = filter_var(trim($v), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW); break;
          //preg_replace('/&euro;/i', 'â‚¬', html_entity_decode(filter_var(trim($v), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW), ENT_QUOTES)); break;
          case "datetime": $v = empty($v)?"\"\"":date("Y-m-d H:i:s", $v); break;
          default: break;
        }
        if ($v === "\"\"" && $k != 'Location') $v = " ";
      }
      unset($v);

      mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $ol);
      fwrite($fh, "\r\n".implode("\t", $ol));
      $oln++;
//		}
  }

  $time_start += $time_interval_leads;
  $time_end += $time_interval_leads;
}

fclose($fh);

/* zipping */
$zip = new ZipArchive;
if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
  fwrite($flog, date("Y-m-d H:i:s")." OPEN ARCHIVE : ".$zipfilename."\n");
    $zip->addFile($csvPath, $csvfilename);
    if($zip->close()){
      unlink ($csvPath);
      fwrite($flog, date("Y-m-d H:i:s")." CLOSE ARCHIVE : ".$zipfilename."\n");
      fwrite($flog, date("Y-m-d H:i:s")." DELETE FILE : ".$csvfilename."\n");
    }
}

fwrite($flog, date("Y-m-d H:i:s")." SESSION END\n\n");

fclose($flog);

if ($upload) {
  /* ftp send file */
  define("REMOTE_FILE", $zipfilename);
  define("CATALOG_FILE", $zipPath);
  define("CI_FTP_SERVER", 'webe.emv3.com');
  define("CI_FTP_USERNAME", 'md2i_ftp');
  define("CI_FTP_PASS", 'md2i@ftp/1');
  define("CI_REMOVE_DIR", 'ccci/incoming/');

  $file = CATALOG_FILE;
  $remote_file = CI_REMOVE_DIR.REMOTE_FILE;

  if(is_file($file)){
    // Mise en place d'une connexion basique
    $conn_id = ftp_connect(CI_FTP_SERVER);

    // Identification avec un nom d'utilisateur et un mot de passe
    $login_result = ftp_login($conn_id, CI_FTP_USERNAME, CI_FTP_PASS);

    // Charge un fichier
    ftp_put($conn_id, $remote_file, $file, FTP_BINARY);

    // Fermeture de la connexion
    ftp_close($conn_id);
  }
}