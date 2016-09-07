<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
	//Importing file function mail send
	require_once(dirname(__FILE__).'/../../includes/fr/classV3/phpmailer_2014/PHPMailerAutoload.php');	
	//Importing the file send function
	require_once(dirname(__FILE__).'/../../includes/fr/classV3/phpmailer_2014/Email_functions_external_mail_send.php');
$db = DBHandle::get_instance();
	$header_from_name_new	= "Q&A Techni-Contact";
	$header_from_email	= 'ugc@techni-contact.com';

	$header_send1_name	= '';
	//$header_send1_email	= 't.henryg@techni-contact.com';
	$header_send1_email	= 'ugc@techni-contact.com';
	
	$header_send2_name	= '';
	$header_send2_email	= '';
	
	$header_reply1_name	= '';
	$header_reply1_email= '';
	
	$header_copy2_name	= '';
	$header_copy2_email	= '';
	
	$header_copy1_email	= '';
	$header_copy1_name	= '';
	$header_copy1_email	= '';
	$chaine = "Liste des Q&A non repondues";
	$objet = utf8_decode($chaine);
	
	$subject = $objet;
	
	
	$message_header = "<html><head>
						<meta http-equiv=Content-Type content=text/html; charset=iso-8859-1>
						</head>
						<body bgcolor=#FFFFFF>";
	$message_text	.= '<img src="http://www.techni-contact.com/media/emailings/mails-serveur-tc/logo-tc.jpg">';
	$message_text	.= '<p>';
						$message_text	.= 'Bonjour,';
						$message_text	.= '<br /><br />';
						$message_text	.= 'Les fiches produits suivantes ont re&ccedil;u des questions utilisateurs.';
						$message_text	.= '<br /><br />';
						$message_text	.= 'Merci d\'y r&eacute;pondre rapidement:';
						$message_text	.= '<br /><br />';

	//$sql_ques  = "SELECT DISTINCT(id_produit),id,date_create FROM q_a_questions GROUP BY id_produit ORDER BY date_create DESC ";
	$sql_ques  = "SELECT pfr.name,pfr.id,bu.name as comme,aa.nom1,pfr.ref_name,aa.idCommercial,qaq.id as id_question,qaq.date_create
						FROM q_a_questions qaq, products_fr pfr , advertisers aa, bo_users bu
						WHERE  pfr.idAdvertiser = aa.id
						AND    aa.idCommercial  = bu.id 
						AND    qaq.id_produit   = pfr.id
						GROUP BY idCommercial 
						ORDER BY date_create DESC  ";
	$req_ques  =  mysql_query($sql_ques);
	
	while($data_ques = mysql_fetch_object($req_ques)){
		
					
		$sql_product = "SELECT pfr.name,pfr.id,bu.name as comme,aa.nom1,pfr.ref_name,qaq.id as id_question,qaq.date_create
						FROM q_a_questions qaq, products_fr pfr , advertisers aa, bo_users bu
						WHERE  qaq.id NOT IN (SELECT id_question  FROM q_a_reponses)
						AND    pfr.idAdvertiser = aa.id
						AND    aa.idCommercial  = bu.id 
						AND    qaq.id_produit   = pfr.id
						AND    aa.idCommercial='".$data_ques->idCommercial."'
						GROUP BY id_produit 
						ORDER BY date_create DESC";
						
		//echo $sql_product.'<br />';
		$req_product_comm	   = mysql_query($sql_product);
		$data_product_comm = mysql_fetch_object($req_product_comm);
		$rows_ques         = mysql_num_rows($req_product_comm);
		
		$message_text	.= '<strong>'.$data_product_comm->comme.' ('.$rows_ques.')</strong>';
		$message_text	.= '<br /><br />';
		$req_product 	   = mysql_query($sql_product);
		while($data_product= mysql_fetch_object($req_product)){
			$date_send = date('d/m/Y', strtotime($data_product->date_create));
			$name_ptd = htmlentities($data_product->name, ENT_QUOTES);
			//$url = ''.URL.'produits/'.$data_famille->idFamily.'-'.$data_ques->id_produit.'-'.$data_product->ref_name.'.html';
			$url = ''.SECURE_URL.'manager/q_a_products/fiche_q_a.php?id_product='.$data_product->id.'';
			$message_text	.= $date_send.' - <a href="'.$url.'" target="_blink">'.$name_ptd.'</a>';
			$message_text	.= '<br /><br />';
		}
	}
	
	$message_text	.= 'Vous recevrez un email d&egrave;s lors qu\'une r&eacute;ponse sera apport&eacute;e &agrave; votre question.';
	$message_text	.= '<br /><br />';
	$message_text	.= 'A tr&egrave;s bient&ocirc;t sur Techni-contact ';
	$message_text	.= '<br /><br />';
	$message_text	.= '<strong>Techni-Contact est &eacute;dit&eacute; par</strong> <br /> Md2i <br />';
	$message_text	.= '253 rue Gallieni <br />';
	$message_text	.= 'F-92774 BOULOGNE BILLANCOURT cedex <br />';
	$message_text	.= 'Tel : 01 55 60 29 29 (appel local)<br /> Fax: 01 72 08 01 18<br />';
	$message_text	.= '<a href="http://www.techni-contact.com">http://www.techni-contact.com</a><br /><br />';
	$message_text	.= 'SAS au capital de 160.000 &euro;<br />
		SIRET : 392 772 497 000 39<br />
		NAF : 4791B<br />
		TVA n&deg; FR12 392 772 497<br />
		R.C. NANTERRE B 392 772 497<br />';
	$message_text	.= '</p>';

	$message_bottom = "</body></html>";
				 
	$message_to_send = $message_header . $message_text . $message_bottom;	
	
	echo $message_to_send;
	$mail_send_etat	= php_mailer_external_send($header_from_name_new, $header_from_email, $header_send1_name, $header_send1_email, $header_send2_name, $header_send2_email, $header_reply1_email, $header_reply1_name, $header_copy1_email, $header_copy1_name, $header_copy2_email, $header_copy2_name, $subject, $message_to_send);
	
	
?>