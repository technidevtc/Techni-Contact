<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
$handle = DBHandle::get_instance();

	//Importing file function mail send
	require_once(dirname(__FILE__).'/../../../../includes/fr/classV3/phpmailer_2014/PHPMailerAutoload.php');
	
	//Importing the file send function
	require_once(dirname(__FILE__).'/../../../../includes/fr/classV3/phpmailer_2014/Email_functions_external_mail_send.php');

$action = $_GET['action'];

if($action == 'add_form'){
	
	$name    = mysql_real_escape_string($_GET['name']);
	$prenom  = mysql_real_escape_string($_GET['prenom']);
	$email   = mysql_real_escape_string($_GET['email']);
	$societe = mysql_real_escape_string($_GET['societe']);
	$tel	 = mysql_real_escape_string($_GET['tel']);
	
	$sql_insert = "INSERT INTO `contacts_form_leads_catalogs` (
						`id` ,
						`nom` ,
						`prenom` ,
						`societe` ,
						`email` ,
						`tel` ,
						`timestamp`
						)
						VALUES (
						NULL , '$name', '$prenom', '$societe', '$email', '$tel', NOW())";
	mysql_query($sql_insert);
	
	
	//******************************    ***********************************/
	
	
	$header_from_name_new	= "Techni-Contact - Ornella PATTI";
	$header_from_email	= 'o.patti@techni-contact.com';
	
	$header_send1_name	= '';  
	// $header_send1_email	= 'outarocht.zakaria@gmail.com';
	$header_send1_email	= $email;
	
	$header_send2_name	= '';
	$header_send2_email	= '';
	$header_send2_email	= '';
	
	$header_reply1_name	= '';
	$header_reply1_email= 'o.patti@techni-contact.com';
	
	$header_copy1_email	= '';
	$header_copy1_name	= '';
	$header_copy1_email	= '';
	
	$header_copy2_name	= '';
	$header_copy2_email	= '';
	
	$subject_envoi = 'Présentation des beaux catalogues Techni-Contact ';
	$subject = utf8_decode($subject_envoi) ;
	$path_attachement = URL."pres-catalogue-techni-contact.pdf";
	$message_header = "<html><head>
					  <meta http-equiv=Content-Type content=text/html; charset=iso-8859-1>
					  </head>
					  <body bgcolor=#FFFFFF>";
	$message_text_serv	.= '<a href="http://www.techni-contact.com?utm_source=email&utm_medium=email&utm_campaign=mail-confirmation-lead-catalogue"><img src="http://www.techni-contact.com/media/emailings/mails-serveur-tc/logo-tc.jpg"></a>';
	$message_text_serv	.= '<p>';
			$message_text_serv	.= 'Bonjour '.$prenom.' '.$name.' ,';
			$message_text_serv	.= '<br /><br />';
			$message_text_serv	.= 'Je suis Ornella PATTI, responsable des partenariats chez Techni-Contact.';
			$message_text_serv	.= '<br /><br />';
			$message_text_serv	.= 'Vous trouverez ci-dessous notre brochure pr&eacute;sentant nos beaux catalogues publi&eacute;s chaque ann&eacute;e.';
			$message_text_serv	.= '<br /><br />';
			$message_text_serv	.= '<a href="'.$path_attachement.'">D&eacute;couvrez notre brochure.</a>';
			$message_text_serv	.= '<br /><br />';
			$message_text_serv	.= 'Ces catalogues sont diffusés auprès d’une très large audience de décideurs en version papier, électronique et sur les salons professionnels.';
			$message_text_serv	.= '<br /><br />';
			$message_text_serv	.= 'Ils sont un formidable outil de travail pour des dirigeants et responsables achats qui s’y réfèrent pour répondre à leurs besoins hors production. ';
			$message_text_serv	.= '<br /><br />';
			$message_text_serv	.= 'En effet, selon une étude FEVAD, 80% des acheteurs utilisent les « Gros catalogues » pour préparer leurs achats. ';
			$message_text_serv	.= '<br /><br />';
			$message_text_serv	.= 'Je serai ravie de répondre à toutes vos questions que vous vous posez concernant notre offre.';
			$message_text_serv	.= '<br /><br />';
			$message_text_serv	.= 'Ornella PATTI<br />
									Responsable partenariats<br />
									o.patti@techni-contact.com<br />
									01.72.08.01.28
									';
			$message_text_serv	.= '<br /><br />';
			
			
			$message_text_serv	.= 'Techni-Contact est &eacute;dit&eacute; par<br />
								Md2i<br />
								253 rue Gallieni<br />
								F-92774 BOULOGNE BILLANCOURT cedex<br />
								Tel : 01 55 60 29 29 (appel local)<br />
								Fax: 01 83 62 36 12<br />
								<a href="http://www.techni-contact.com?utm_source=email&utm_medium=email&utm_campaign=mail-confirmation-lead-catalogue">http://www.techni-contact.com</a>';
			$message_text_serv	.= '</p>';				
			$message_text_serv	.= '</div>';
	$message_bottom = "</body></html>";
	
	$message_to_send_serv = $message_header . $message_text_serv . $message_bottom;
	
	$mail_send_etat	= php_mailer_external_send($header_from_name_new, $header_from_email, $header_send1_name, $header_send1_email, $header_send2_name, $header_send2_email, $header_reply1_email, $header_reply1_name, $header_copy1_email, $header_copy1_name, $header_copy2_email, $header_copy2_name, $subject, $message_to_send_serv,$path_attachement);
	
	
	/************************* Fin mail Client   ********************************/
	
	$header_from_name_new	= "Lead catalogue TC";
	$header_from_email	= 'notif-intern-form-catalog-tc@techni-contact.com';
	
	$header_send1_name	= '';  
	// $header_send1_email	= 'outarocht.zakaria@gmail.com';
	$header_send1_email	= 'notif-intern-form-catalog-tc@techni-contact.com';
	
	
	$header_send2_name	= '';
	$header_send2_email	= '';
	$header_send2_email	= '';
	
	$header_reply1_name	= '';
	$header_reply1_email= 'notif-intern-form-catalog-tc@techni-contact.com';
	
	$header_copy1_email	= '';
	$header_copy1_name	= '';
	$header_copy1_email	= '';
	
	$header_copy2_name	= '';
	$header_copy2_email	= '';
	
	$subject_envoi = $societe.' est intéressé par les catalogues TC';
	$subject = utf8_decode($subject_envoi) ;
	
	$message_header_inte = "<html><head>
					  <meta http-equiv=Content-Type content=text/html; charset=iso-8859-1>
					  </head>
					  <body bgcolor=#FFFFFF>";
	$message_text_inte	.= '<img src="http://www.techni-contact.com/media/emailings/mails-serveur-tc/logo-tc.jpg">';
	$message_text_inte	.= '<p>';
			$message_text_inte	.= 'Bonjour ,';
			$message_text_inte	.= '<br /><br />';
			$message_text_inte	.= 'Un annonceur s’est intéressé aux catalogues TC et à envoyé un formulaire de demande de documentation.';
			$message_text_inte	.= '<br /><br />';
			$message_text_inte	.= '<ul>';
			$message_text_inte	.= '<li><strong>Date : </strong> '.date('Y/m/d H:i').'</li>';
			$message_text_inte	.= '<li><strong>Société : </strong>'.$societe.'</li>';
			$message_text_inte	.= '<li><strong>Prénom  : </strong>'.$prenom.'</li>';
			$message_text_inte	.= '<li><strong>Nom  : </strong>'.$name.'</li>';
			$message_text_inte	.= '<li><strong>Email  : </strong>'.$email.'</li>';
			$message_text_inte	.= '<li><strong>Téléphone  : </strong>'.$tel.'</li>';
			$message_text_inte	.= '</ul>';
			$message_text_inte	.= '<br />';
			$message_text_inte	.= 'Cet annonceur a reçu un email avec la <a href="">documentation catalogue</a> ';
			$message_text_inte	.= '<br /><br />';				
			$message_text_inte	.= 'Techni-Contact est &eacute;dit&eacute; par<br />
								Md2i<br />
								253 rue Gallieni<br />
								F-92774 BOULOGNE BILLANCOURT cedex<br />
								Tel : 01 55 60 29 29 (appel local)<br />
								Fax: 01 83 62 36 12<br />
								<a href="http://www.techni-contact.com">http://www.techni-contact.com</a>';
			$message_text_inte	.= '</p>';				
			$message_text_inte	.= '</div>';
	$message_bottom_inte = "</body></html>";
	
	$message_to_send_inte = $message_header_inte . $message_text_inte . $message_bottom_inte;
	
	$mail_send_etat	= php_mailer_external_send($header_from_name_new, $header_from_email, $header_send1_name, $header_send1_email, $header_send2_name, $header_send2_email, $header_reply1_email, $header_reply1_name, $header_copy1_email, $header_copy1_name, $header_copy2_email, $header_copy2_name, $subject, $message_to_send_inte);
	
	echo '<div style="margin-bottom: 60px;">Merci pour l’intérêt que vous portez aux catalogues Techni-Contact. Un email vous a été transmis avec la documentation complète expliquant pourquoi ils peuvent être un formidable canal de visibilité pour vos produits ou vos services.</div>';
echo '<div id="close-send-message" style="cursor: pointer;font-size: 16px;text-align: center;"  onclick="close_popup_send_commande();"><span>[Fermer]</span></div>';
}


if($action == 'add_form_catalogue'){
	
	$string1 = "";
	$user_ramdom_key = "1234567890";
	srand((double)microtime()*time());
		for($i=0; $i<8; $i++) {
		$string1 .= $user_ramdom_key[rand()%strlen($user_ramdom_key)];
		}
	$random_key = $string1;	
	
	
	$type	 	= mysql_real_escape_string($_GET['type']);
	$name    	= mysql_real_escape_string($_GET['name']);
	$prenom  	= mysql_real_escape_string($_GET['prenom']);
	$email   	= mysql_real_escape_string($_GET['email']);
	$societe 	= mysql_real_escape_string($_GET['societe']);
	$tel	 	= mysql_real_escape_string($_GET['tel']);
	$cp	 		= mysql_real_escape_string($_GET['cp']);
	$adresse	= mysql_real_escape_string($_GET['adresse']);
	$ville   	= mysql_real_escape_string($_GET['ville']);
	
	if($type == "gen"){
		$gen = '1';
		$ind = '0';
		$header_mail = "Votre catalogue Général Techni-Contact";
		$message_mail1 = " G&eacute;n&eacute;ral ";
		$message_mail_zip = " -  <a href='http://www.techni-contact.com/media/catalogues/Techni-Contact-Catalogue-General-2016.zip'>T&eacute;l&eacute;charger (.zip) </a>";
		$message_mail_intera = " -  <a href='https://fr.calameo.com/read/00000395813701c308e34'>Feuilleter la version int&eacute;ractive  </a>";
	}else{
		$gen = '0';
		$ind = '1';
		$header_mail = "Votre catalogue Industries Techni-Contact";
		$message_mail1 = " Industries ";
		$message_mail_zip = " -  <a href='http://www.techni-contact.com/media/catalogues/Techni-Contact-Catalogue-Industries-2016.zip'>T&eacute;l&eacute;charger (.zip) </a>";
		$message_mail_intera = " -  <a href='https://fr.calameo.com/read/0000039587ba25ff5bfc7'>Feuilleter la version interactive  </a>";
	} 
	
	$data_now = date("Y-m-d H:i:s");
	$date_now_final = strtotime($data_now);
	$sql_insert  = "INSERT INTO `catalogues` (
					`id` ,
					`nom` ,
					`prenom` ,
					`fonction` ,
					`societe` ,
					`salaries` ,
					`secteur` ,
					`naf` ,
					`siret` ,
					`adresse` ,
					`cadresse` ,
					`cp` ,
					`ville` ,
					`pays` ,
					`tel` ,
					`fax` ,
					`email` ,
					`url` ,
					`infos_sup` ,
					`gen` ,
					`ind` ,
					`col` ,
					`timestamp` ,
					`imp`
					)
					VALUES (
					'$random_key', '$name', '$prenom', '', '$societe', '', '', '', '', '$adresse', '', '$cp', '$ville', '', '$tel', '', '$email', '', '', '$gen', '$ind', '0', '$date_now_final', '0' )";
	// mysql_query($sql_insert);
	
	
	//******************************    ***********************************/
	
	
	$header_from_name_new	= "Catalogue Techni-Contact";
	$header_from_email	= 'notif-intern-demande-catalog-tc@techni-contact.com';
	
	$header_send1_name	= '';  
	// $header_send1_email	= 'outarocht.zakaria@gmail.com';
	$header_send1_email	= $email;
	
	$header_send2_name	= '';
	$header_send2_email	= '';
	$header_send2_email	= '';
	
	$header_reply1_name	= '';
	$header_reply1_email= 'notif-intern-demande-catalog-tc@techni-contact.com';
	
	$header_copy1_email	= '';
	$header_copy1_name	= '';
	$header_copy1_email	= '';
	
	$header_copy2_name	= '';
	$header_copy2_email	= '';

	
	$subject = utf8_decode($header_mail) ;
	
	$message_header = "<html><head>
					  <meta http-equiv=Content-Type content=text/html; charset=iso-8859-1>
					  </head>
					  <body bgcolor=#FFFFFF>";
	$message_text_serv	.= '<a href="http://www.techni-contact.com?utm_source=email&utm_medium=email&utm_campaign=mail-confirmation-commande-catalogue"><img src="http://www.techni-contact.com/media/emailings/mails-serveur-tc/logo-tc.jpg"></a>';
	$message_text_serv	.= '<p>';
			$message_text_serv	.= 'Bonjour '.$prenom.' '.$name.' ,';
			$message_text_serv	.= '<br /><br />';
			$message_text_serv	.= 'Nous vous confirmons la bonne r&eacute;ception de votre demande de catalogue '.$message_mail1.' Techni-Contact.';
			$message_text_serv	.= '<br /><br />';
			$message_text_serv	.= 'Nous esp&eacute;rons que vous prendrez plaisir &agrave; le feuilleter et qu’il vous permettra de mieux pr&eacute;parer vos achats.';
			$message_text_serv	.= '<br /><br />';
			$message_text_serv	.= 'Afin que vous puissiez sans attendre d&eacute;couvrir son contenu, nous vous proposons de le d&eacute;couvrir en version &eacute;lectronique, &agrave; t&eacute;l&eacute;charger ou &agrave; feuilleter en ligne :';
			$message_text_serv	.= '<br /><br />';
			$message_text_serv	.= $message_mail_zip.'<br />';
			$message_text_serv	.= $message_mail_intera;
			$message_text_serv	.= '<br /><br />';
			$message_text_serv	.= 'Nous restons &agrave; votre disposition pour tous vos besoins professionnels.';
			$message_text_serv	.= '<br /><br />';
			$message_text_serv	.= 'Cordialement.';
			$message_text_serv	.= '<br /><br />';
			$message_text_serv	.= 'Le service clients';
			$message_text_serv	.= '<br /><br />';
			
			$message_text_serv	.= 'Techni-Contact est &eacute;dit&eacute; par<br />
								Md2i<br />
								253 rue Gallieni<br />
								F-92774 BOULOGNE BILLANCOURT cedex<br />
								Tel : 01 55 60 29 29 (appel local)<br />
								Fax: 01 83 62 36 12<br />
								<a href="http://www.techni-contact.com?utm_source=email&utm_medium=email&utm_campaign=mail-confirmation-commande-catalogue">http://www.techni-contact.com</a>';
			$message_text_serv	.= '</p>';				
			$message_text_serv	.= '</div>';
	$message_bottom = "</body></html>";
	
	$message_to_send_serv = $message_header . $message_text_serv . $message_bottom;
	
	$mail_send_etat	= php_mailer_external_send($header_from_name_new, $header_from_email, $header_send1_name, $header_send1_email, $header_send2_name, $header_send2_email, $header_reply1_email, $header_reply1_name, $header_copy1_email, $header_copy1_name, $header_copy2_email, $header_copy2_name, $subject, $message_to_send_serv);
	
	
	/************************* Fin mail Client   ********************************/
	
	
	if($type == "gen"){
		$header_mail_tt = $societe." souhaite recevoir le catalogue Général ";
		$message_mail_tt = " G&eacute;n&eacute;ral ";
	}else{
		$header_mail_tt = $societe." souhaite recevoir le catalogue Industries";
		$message_mail_tt = " Industrie ";
	}
	
	
	$header_from_name_new	= "Demande de catalogue TC";
	$header_from_email	= 'notif-intern-demande-catalog-tc@techni-contact.com';
	
	$header_send1_name	= '';  
	$header_send1_email	= 'notif-intern-demande-catalog-tc@techni-contact.com';
	// $header_send1_email	= 't.henryg@techni-contact.com';
	
	
	$header_send2_name	= '';
	$header_send2_email	= '';
	$header_send2_email	= '';
	
	$header_reply1_name	= '';
	$header_reply1_email= 'notif-intern-demande-catalog-tc@techni-contact.com';
	
	$header_copy1_email	= '';
	$header_copy1_name	= '';
	$header_copy1_email	= '';
	
	$header_copy2_name	= '';
	$header_copy2_email	= '';
	
	
	$subject = utf8_decode($header_mail_tt) ;
	
	$message_header_inte = "<html><head>
					  <meta http-equiv=Content-Type content=text/html; charset=iso-8859-1>
					  </head>
					  <body bgcolor=#FFFFFF>";
	$message_text_inte	.= '<img src="http://www.techni-contact.com/media/emailings/mails-serveur-tc/logo-tc.jpg">';
	$message_text_inte	.= '<p>';
			$message_text_inte	.= 'Bonjour ,';
			$message_text_inte	.= '<br /><br />';
			$message_text_inte	.= 'Un utilisateur souhaite recevoir le catalogue '.$message_mail_tt;
			$message_text_inte	.= '<br /><br />';
			$message_text_inte	.= 'Voici ses coordonn&eacute;es :';
			$message_text_inte	.= '<ul>';
			$message_text_inte	.= '<li><strong>Date : </strong> '.date('d/m/Y H:i').'</li>';
			$message_text_inte	.= '<li><strong>Société : </strong>'.$societe.'</li>';
			$message_text_inte	.= '<li><strong>Prénom  : </strong>'.$prenom.'</li>';
			$message_text_inte	.= '<li><strong>Nom  : </strong>'.$name.'</li>';
			$message_text_inte	.= '<li><strong>Email  : </strong>'.$email.'</li>';
			$message_text_inte	.= '<li><strong>Téléphone  : </strong>'.$tel.'</li>';
			$message_text_inte	.= '<li><strong>Adresse  : </strong>'.$adresse.'</li>';
			$message_text_inte	.= '<li><strong>Code postal  : </strong>'.$cp.'</li>';
			$message_text_inte	.= '<li><strong>Ville  : </strong>'.$ville.'</li>';
			$message_text_inte	.= '</ul>';
			$message_text_inte	.= '<br />';
			$message_text_inte	.= 'Cet utilisateur a reçu un mail de confirmation de la bonne r&eacute;ception par TC de sa demande. Ce mail contient aussi les versions PDF et int&eacute;ractive du catalogue demand&eacute;. ';
			$message_text_inte	.= '<br /><br />';				
			$message_text_inte	.= 'Cordialement<br />';
			$message_text_inte	.= '</p>';				
			$message_text_inte	.= '</div>';
	$message_bottom_inte = "</body></html>";
	
	$message_to_send_inte = $message_header_inte . $message_text_inte . $message_bottom_inte;
	
	$mail_send_etat	= php_mailer_external_send($header_from_name_new, $header_from_email, $header_send1_name, $header_send1_email, $header_send2_name, $header_send2_email, $header_reply1_email, $header_reply1_name, $header_copy1_email, $header_copy1_name, $header_copy2_email, $header_copy2_name, $subject, $message_to_send_inte);
	
	
	
	echo '<div style="margin-bottom: 190px;">Merci pour l’intérêt que vous portez aux catalogues Techni-Contact. <br /><br />
		  Votre commande va rapidement être traitée par nos services.</div>';
	echo '<div id="close-send-message" style="cursor: pointer;font-size: 16px;text-align: center;" onclick="close_popup_send_commande();"><span>[Fermer]</span></div>';
}

?>

<style>

