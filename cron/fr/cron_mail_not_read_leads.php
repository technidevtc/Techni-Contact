<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
		require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

	//Importing file function mail send
	require_once(dirname(__FILE__).'/../../includes/fr/classV3/phpmailer_2014/PHPMailerAutoload.php');
	
	//Importing the file send function
	require_once(dirname(__FILE__).'/../../includes/fr/classV3/phpmailer_2014/Email_functions_external_mail_send.php');	

	
$db = DBHandle::get_instance();


/*$sql_check  = "SELECT id, cread, FROM_UNIXTIME( create_time ),idAdvertiser,idProduct,societe
			   FROM contacts
			   WHERE DATE_SUB( CURDATE( ) , INTERVAL 60 DAY ) <= FROM_UNIXTIME( create_time, '%Y-%m-%d' )
			   AND  (DATE_SUB( CURDATE( ) , INTERVAL 2  DAY ) >= FROM_UNIXTIME( create_time, '%Y-%m-%d' ))
			   AND cread = '0'
			   AND  invoice_status IN('152','25','9','72')
			   order by idAdvertiser
			   "; 
*/			   


$sql_fist_month 		= "SELECT DATE_SUB( CURDATE( ) , INTERVAL 2 DAY ) as dayys ";
$req_fist_month			=  mysql_query($sql_fist_month);
$data_fist_month  		=  mysql_fetch_object($req_fist_month);

$date_coupe				=  explode("-",$data_fist_month->dayys);

$years = $date_coupe[0];
$month = $date_coupe[1];
$days  = $date_coupe[2];

$dateMothFirstDay		= date("Y-m");
$dateMothDayNow			= date("d");

$yesterday_start		= strtotime($years.'-'.$month.'-1 00:00:00');
$yesterday_end			= strtotime($data_fist_month->dayys.' 23:59:59');					   


$sql_check  ="SELECT cc.id, cc.cread, FROM_UNIXTIME( cc.create_time ),cc.idAdvertiser,cc.idProduct,cc.societe,cc.invoice_status
			   FROM contacts cc, advertisers aa
			   WHERE cc.idAdvertiser = aa.id
			   AND cc.create_time BETWEEN '$yesterday_start' AND '$yesterday_end'
			   AND cc.cread = '0'
			   AND cc.invoice_status IN('152','25','9','72')
			   order by cc.idAdvertiser
			 ";			   
			   
$req_check  =  mysql_query($sql_check);
  
$previous_id	= "";
$local_loop		= 0;
$local_array	= -1;
$array_for_one_advertiser	= Array();	

while($array_result = mysql_fetch_assoc($req_check)){
	if($previous_id==$array_result["idAdvertiser"]){
		//ID de la ligne actuelle est &eacute;gale à celui de la ligne pr&eacute;c&eacute;dente
		//On ajoute les valeurs dans le tableau
		$array_for_one_advertiser[$local_array]["idcontact"] .= $array_result["id"].'#';		
	}else {
		$local_array++;
		
		$array_for_one_advertiser[$local_array]["idadvertiser"] = $array_result["idAdvertiser"];
		$array_for_one_advertiser[$local_array]["idcontact"] 	= $array_result["id"].'#';
		
		$previous_id	= $array_result["idAdvertiser"];		
	}	
	$local_loop++;
}

// print_r($array_for_one_advertiser);
 	$header_from_name_new	= "Techni-Contact";
	$header_from_email	= 'lead@techni-contact';
	
	$header_send1_name	= '';
$count_total = count($array_for_one_advertiser);
$j=0;
for ($row = 0; $row <= count($array_for_one_advertiser); $row++){
	$idadvertiser =  $array_for_one_advertiser[$row]["idadvertiser"];
	
	$sql_advertiser  = "SELECT id,contacts_not_read_notification,nom1,email 
					    FROM   advertisers
					    WHERE  id='".$idadvertiser."' ";
	$req_advertiser  =  mysql_query($sql_advertiser);
	$data_advertiser =  mysql_fetch_object($req_advertiser);
	
	
	if($data_advertiser->contacts_not_read_notification == 1){
	
	$sql_get_infos = "SELECT
						extra_u.id, extra_u.login,
						extra_u.c,	extra_u.webpass
					  FROM
						extranetusers extra_u
					  WHERE
						extra_u.id='$idadvertiser'";
	$req_get_infos     =  mysql_query($sql_get_infos);
	$content_get_infos =  mysql_fetch_object($req_get_infos);

	$id_contact = explode("#",$array_for_one_advertiser[$row]["idcontact"]);
	foreach($id_contact as $value_id){
			if(!empty($value_id)){
			$j++;
			}
	}
	
	$header_send1_email	= $data_advertiser->email;
	// $header_send1_email	= 't.henryg@techni-contact.com';
	
	$header_send2_name	= '';
	$header_send2_email	= 'lead@techni-contact';
	$header_send2_email	= '';
	
	$header_reply1_name	= '';
	$header_reply1_email= '';
	
	$header_copy1_email	= '';
	$header_copy1_name	= '';
	$header_copy1_email	= '';
	
	$header_copy2_name	= '';
	$header_copy2_email	= '';
	
	if($j == 1) $contact = "contact non lu";
	else $contact = "contacts non lus";
	
	$subject_envoi = $data_advertiser->nom1.' vous avez '.$j.' '.$contact.'  ';
	$subject = utf8_decode($subject_envoi) ;
	
	$message_header = "<html><head>
					  <meta http-equiv=Content-Type content=text/html; charset=iso-8859-1>
					  </head>
					  <body bgcolor=#FFFFFF>";
	$message_text	.= '<img src="http://www.techni-contact.com/media/emailings/mails-serveur-tc/logo-tc.jpg">';
	$message_text	.= '<p>';
			$message_text	.= 'Cher partenaire,';
			$message_text	.= '<br /><br />';
			$message_text	.= 'Nous avons nous avons transmis il y a quelques jours des contacts int&eacute;ress&eacute;s par vos produits ou vos services.';
			$message_text	.= '<br /><br />';
			$message_text	.= 'Ces contacts n\'ont pas encore &eacute;t&eacute; consult&eacute;s et ils sont donc toujours en attente de r&eacute;ponse de votre part...';
			$message_text	.= '<br /><br />';
			$message_text	.= 'Voici la liste de ces contacts <strong>('.$j.')</strong> :';
			$message_text	.= '<br /><br />';
			$message_text	.= '<table>
									<tr style="background-color:#b8bab4">
									<th>N° Contact</th>
									<th>Soci&eacute;t&eacute;</th>';
			
			
	$id_contact = explode("#",$array_for_one_advertiser[$row]["idcontact"]);
	$j="";
	foreach($id_contact as $value_id){
			if(!empty($value_id)){
				
				$sql_contact_societe  =  "SELECT societe
										  FROM contacts
										  WHERE id='".$value_id."' "; 
				$req_contact_societe  =   mysql_query($sql_contact_societe);
				$rows_contact_societe =   mysql_num_rows($req_contact_societe);
				
				if($rows_contact_societe > 0){
					$data_contact_societe =   mysql_fetch_object($req_contact_societe);
					$message_text	.= '<tr><td style="border-width:1px;padding:8px;border-style:solid;border-color:#666666;background-color:#ffffff">
					'.$value_id.' </td>
					<td style="border-width:1px;padding:8px;border-style:solid;border-color:#666666;background-color:#ffffff">
					<a href="'.EXTRANET_URL.'extranet-v3-contacts-detail.html?id='.$value_id.'&uid='.$content_get_infos->webpass.'">'.$data_contact_societe->societe.'</a></td></tr>';
				}
			}			
	}
			$message_text	.= '</table>';
			$message_text	.= '<br /><br />';
			$message_text	.= 'Notre conseil : Soyez r&eacute;actifs !';
			$message_text	.= '<br /><br />';
			$message_text	.= 'Afin de multiplier vos chances de clore l\'affaire et de passer devant la concurrence, ayez un premier &eacute;change avec le contact dans l\'heure qui suit le lead.';
			$message_text	.= '<br /><br />';
			$message_text	.= 'Une &eacute;tude de l\'&eacute;diteur de logiciels InsideSales.com a en effet d&eacute;montr&eacute; qu\'au del&agrave;, il y a 10 fois moins de chances de pouvoir le joindre et de le requalifier correctement.';
			$message_text	.= '<br /><br />';
			$message_text	.= 'Nous restons &agrave; votre disposition pour tout renseignement ';
			$message_text	.= '<br /><br />';
			$message_text	.= 'L\'&eacute;quipe qualit&eacute; Techni-Contact';
			$message_text	.= '<br /><br />';
			
			$message_text	.= 'Techni-Contact est &eacute;dit&eacute; par<br />
								Md2i<br />
								253 rue Gallieni<br />
								F-92774 BOULOGNE BILLANCOURT cedex<br />
								Tel : 01 55 60 29 29 (appel local)<br />
								Fax: 01 83 62 36 12<br />
								<a href="http://www.techni-contact.com">http://www.techni-contact.com</a>';
			
			$message_text	.= '<br /><br />';
			$message_text	.= 'SAS au capital de 160.000 &euro;<br />
								SIRET : 392 772 497 000 39<br />
								NAF : 4791B<br />
								TVA n&deg; FR12 392 772 497<br />
								R.C. NANTERRE B 392 772 497<br />';
			$message_text	.= '</p>';
			$message_text	.= '</tr>';
			$message_text	.= '</table>';
			$message_text	.= '</p>';				
			$message_text	.= '</div>';
	$message_bottom = "</body></html>";
	
	$message_to_send = $message_header . $message_text . $message_bottom;
	
	//echo 		$path_attachement;	
	$mail_send_etat1	= php_mailer_external_send($header_from_name_new, $header_from_email, $header_send1_name, $header_send1_email, $header_send2_name, $header_send2_email, $header_reply1_email, $header_reply1_name, $header_copy1_email, $header_copy1_name, $header_copy2_email, $header_copy2_name, $subject, $message_to_send);
	// echo $message_text;
	$message_text="";
	
	}
}


$path_attachement = CSV_PATH."attachment/annonceurs_not_read.xls";


require_once("Spreadsheet/Excel/Writer.php");
$workbook = new Spreadsheet_Excel_Writer($path_attachement,true,"UTF-8");
//$workbook->send();

$worksheet =& $workbook->addWorksheet();
$workbook->setVersion(8);
// $worksheet->setInputEncoding('utf-8');
$worksheet->setInputEncoding('ISO-8859-7');

    $format_top =& $workbook->addFormat();
    $format_top->setAlign('top');
    $format_top->setTextWrap(1);

	$format_center =& $workbook->addFormat();
	$format_center->setAlign('center');

	$worksheet->write(0, 0, 'Nom de l\'annonceur');
	$worksheet->write(0, 1, 'Date de création de l\'annonceur');
	$worksheet->write(0, 2, 'Nb de contacts éligibles non lus');
	$worksheet->write(0, 3, 'Type partenaire');


$tableau  = array();
$i = 0;
for ($row_serv = 0; $row_serv <= count($array_for_one_advertiser); $row_serv++){
	$idadvertiser =  $array_for_one_advertiser[$row_serv]["idadvertiser"];
	$sql_advertiser  = "SELECT id,contacts_not_read_notification,nom1,create_time,category
					FROM   advertisers
					WHERE  id='".$idadvertiser."' ";
	// echo $sql_advertiser.'<br />';
	$req_advertiser  =  mysql_query($sql_advertiser);
	$rows_advertiser =  mysql_num_rows($req_advertiser);
	
	if($rows_advertiser > 0){
	$data_advertiser =  mysql_fetch_object($req_advertiser);
	
	if($data_advertiser->create_time != 0)	$create_time = date('Y/m/d', $data_advertiser->create_time);
	else $create_time = ' - ';
	
	
	if($data_advertiser->category == '1'){
		$type_adve  = "Fournisseur";
	}
	if($data_advertiser->category == '2'){
		$type_adve  = "Annonceur non facture";
	}
	if($data_advertiser->category == '3'){
		$type_adve  = "Prospect";	
	}
	if($data_advertiser->category == '4') {
		$type_adve  = "Annonceur bloque";
	}
 	if($data_advertiser->category == '5') {
		$type_adve  = "Litige de paiement";
	}
 	if($data_advertiser->category == '0') {
		$type_adve  = "Annonceur";
	}
	
	//echo "type : ".$type_adve;
	
	$id_contact = explode("#",$array_for_one_advertiser[$row_serv]["idcontact"]);
	foreach($id_contact as $value_id){
			if(!empty($value_id)){
			$i++;
			}
	}
	$tableau[]  = $data_advertiser->nom1;
	$tableau[]  = $create_time;
	$tableau[]  = $i;
	
	$worksheet->write($row_serv+1, 0, utf8_decode($data_advertiser->nom1));
	$worksheet->write($row_serv+1, 1, utf8_decode($create_time));
	$worksheet->write($row_serv+1, 2, utf8_decode($i));
	$worksheet->write($row_serv+1, 3, htmlentities($type_adve, ENT_QUOTES));
	
	$type_adve ="";
	$i="";
	
	// fputcsv($fp, $tableau,"\t", '"');
	//fwrite($fp, $tableau);
	 
	unset($tableau);
	
	}	
}
$workbook->close();
//fclose($fp);

	$header_from_name_new	= "Techni-Contact";
	$header_from_email	= 'notif-intern-leads-annonc-non-lus@techni-contact.com';
	
	$header_send1_name	= '';
	// $header_send1_email	= 't.henryg@techni-contact.com ';
	$header_send1_email	= 'notif-intern-leads-annonc-non-lus@techni-contact.com';
	
	$header_send2_name	= '';
	$header_send2_email	= 'notif-intern-leads-annonc-non-lus@techni-contact.com';
	$header_send2_email	= '';
	
	$header_reply1_name	= '';
	$header_reply1_email= '';
	
	$header_copy1_email	= '';
	$header_copy1_name	= '';
	$header_copy1_email	= 'z.abidi@techni-contact.com';
	
	$header_copy2_name	= '';
	$header_copy2_email	= '';

	$subject_envoi = ' Liste des Annonceurs n\'ayant pas lu leurs contacts - '.date('Y/m/d');
	$subject = utf8_decode($subject_envoi) ;
	
	$message_header = "<html><head>
					  <meta http-equiv=Content-Type content=text/html; charset=iso-8859-1>
					  </head>
					  <body bgcolor=#FFFFFF>";
	
	$message_text_serv	.= '<p>';
			$message_text_serv	.= 'Bonjour,';
			$message_text_serv	.= '<br /><br />';
			$message_text_serv	.= 'Vous trouverez en pi&egrave;ce jointe la liste des annonceurs n\'ayant pas lu leurs contacts dans les 48H suivant leur r&eacute;ception.';
			$message_text_serv	.= '<br /><br />';
			$message_text_serv	.= 'Les contacts en question sont uniquement des contacts facturables ou au forfait. Seuls les contacts du mois courant sont remont&eacute;s';
			
			$message_text_serv	.= '<br /><br />';
			$message_text_serv	.= 'Nb d\'annonceurs :'.count($array_for_one_advertiser);
			$message_text_serv	.= '<br /><br />';
			$message_text_serv	.= 'Cette liste doit &ecirc;tre plus importante le lundi, pour les contacts re&ccedil;us le WE.';
			$message_text_serv	.= '<br /><br />';
			$message_text_serv	.= 'Les annonceurs param&eacute;tr&eacute;s en Manager ont &eacute;t&eacute; pr&eacute;venus par email de la liste des contacts dont ils doivent prendre connaissance.';
			$message_text_serv	.= '<br /><br />';
			;
			
			$message_text_serv	.= 'Techni-Contact est &eacute;dit&eacute; par<br />
								Md2i<br />
								253 rue Gallieni<br />
								F-92774 BOULOGNE BILLANCOURT cedex<br />
								Tel : 01 55 60 29 29 (appel local)<br />
								Fax: 01 83 62 36 12<br />
								<a href="http://www.techni-contact.com">http://www.techni-contact.com</a>';
			$message_text_serv	.= '</p>';				
			$message_text_serv	.= '</div>';
	$message_bottom = "</body></html>";
	
	$message_to_send_serv = $message_header . $message_text_serv . $message_bottom;
	
	$mail_send_etat	= php_mailer_external_send($header_from_name_new, $header_from_email, $header_send1_name, $header_send1_email, $header_send2_name, $header_send2_email, $header_reply1_email, $header_reply1_name, $header_copy1_email, $header_copy1_name, $header_copy2_email, $header_copy2_name, $subject, $message_to_send_serv,$path_attachement);
	// echo $message_text_serv;   
?>