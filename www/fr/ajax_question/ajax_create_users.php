<?php
if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
		require_once '../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

	require("../../../includes/fr/classV3/phpmailer_2014/PHPMailerAutoload.php");
	require("../../../includes/fr/classV3/phpmailer_2014/Email_functions_external_mail_send.php");
	define('CHARSET', 'ISO-8859-1');

$db = DBHandle::get_instance();
	
	function generated_code($tours){
		$string1 = "";
				$user_ramdom_key = "1234567890";
				srand((double)microtime()*time());
					for($i=0; $i<$tours; $i++) {
					$string1 .= $user_ramdom_key[rand()%strlen($user_ramdom_key)];
					}
		$random_key = $string1;
		return $random_key;
	}
	
	function generated_pass($tours){
		$string1 = "";
				$user_ramdom_key = "1234567890AZERTYUIOPQSDFGHJKLMWXCVBN";
				srand((double)microtime()*time());
					for($i=0; $i<$tours; $i++) {
					$string1 .= $user_ramdom_key[rand()%strlen($user_ramdom_key)];
					}
		$random_key = $string1;
		return $random_key;
	}
	
	function unix_timestamp($date){
	$date = str_replace(array(' ', ':'), '-', $date);
	$c    = explode('-', $date);
	$c    = array_pad($c, 6, 0);
	array_walk($c, 'intval');
	return mktime($c[3], $c[4], $c[5], $c[1], $c[2], $c[0]);
	}
	
	
	$email = $_GET['email'];
	$sql_email  = "SELECT id,email FROM clients WHERE email='$email' ";
	$req_email  = mysql_query($sql_email);
	$rows_email = mysql_num_rows($req_email);
	
	$name_products	= $_GET['name_products'];
	$ids_products 	= $_GET['ids_products'];
	$siteweb 		= $_GET['siteweb'];
	$telephone 		= $_GET['telephone'];
	$url 	= $_GET['url'];
	
	$pseudo 		= mysql_real_escape_string($_GET['pseudo']);
	//$question		= mysql_real_escape_string($_GET['question']);
	$question_final	= mysql_real_escape_string($_GET['question']);
	
	//$question_final = str_replace("\r","<br />",$question);
	//$question_final = str_replace("\n","<br />",$question_final);
	//$question_final = htmlentities(addslashes(nl2br($question)));
	//
	$today = date("Y-m-d H:i:s");  
	$id_users = generated_code(9);
	$password   = generated_pass(8);
	$timestape  = unix_timestamp($today);
	$activationCode = generated_pass(30);
	if($rows_email > 0){
		$data_email = mysql_fetch_object($req_email);
		
		$sql_update_users = "UPDATE clients SET activationCode ='$activationCode',tel1='".$telephone."' WHERE id='".$data_email->id."' ";
		mysql_query($sql_update_users);
		
		$sql_question  = "INSERT INTO  `q_a_questions` (
						`id` ,
						`id_user` ,
						`id_produit` ,
						`question` ,
						`vote` ,
						`pseudo` ,
						`etat` ,
						`date_create`
						)
						VALUES (
						NULL ,  '".$data_email->id."',  '$ids_products',  '$question_final',  '0','$pseudo' , '0', NOW( ))";
		mysql_query($sql_question);
	
	$sql_max_rep  = "SELECT MAX(id) as total FROM q_a_questions ";
	$req_max_rep  = mysql_query($sql_max_rep);
	$data_max_rep = mysql_fetch_object($req_max_rep);	
	
	$header_from_name_new	= "Techni-contact";
	$header_from_email	= 'ugc@techni-contact.com';

	$header_send1_name	= '';
	$header_send1_email	= $email;
	
	$header_send2_name	= '';
	$header_send2_email	= '';
	
	$header_reply1_name	= '';
	$header_reply1_email= '';
	
	$header_copy2_name	= '';
	$header_copy2_email	= '';
	
	$header_copy1_email	= '';
	$header_copy1_name	= '';
	$header_copy1_email	= '';

	$subject = 'Validez votre question sur Techni-Contact';
	
	$name_ptd = htmlentities($name_products, ENT_QUOTES); 
	$message_header = "<html><head>
						<meta http-equiv=Content-Type content=text/html; charset=iso-8859-1>
						</head>
						<body bgcolor=#FFFFFF>";
	$message_text	.= '<img src="http://www.techni-contact.com/media/emailings/mails-serveur-tc/logo-tc.jpg">';
	$message_text	.= '<p>';
						$message_text	.= 'Bonjour,';
						$message_text	.= '<br /><br />';
						$message_text	.= 'Vous venez de poser une question sur notre site internet concernant le produit <a href="'.$url.'" target="_blink">'.$name_ptd.'</a>.';
						$message_text	.= '<br /><br />';
						$message_text	.= 'Afin que les internautes ou nos experts puissent y r&eacute;pondre, veuillez cliquer sur le lien suivant';
						$message_text	.= '<br /><br />';
						$message_text	.= '<a href="'.$url.'?tokens_active='.$activationCode.'&id_product='.$ids_products.'&id_question='.$data_max_rep->total.'" target="_blink">'.$url.'?tokens_active='.$activationCode.'</a>';
						$message_text	.= '<br /><br />';
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
	
	$mail_send_etat	= php_mailer_external_send($header_from_name_new, $header_from_email, $header_send1_name, $header_send1_email, $header_send2_name, $header_send2_email, $header_reply1_email, $header_reply1_name, $header_copy1_email, $header_copy1_name, $header_copy2_email, $header_copy2_name, $subject, $message_to_send);
	
	echo '<div class="message_views"><p>Merci pour votre question. Afin que nous puissions y répondre, veuillez simplement cliquer sur le lien du mail qui vient d’être envoyé à l’adresse <strong>'.$email.'</strong> </p></div>
	<div><center><a href="#" onclick="close_popup_create()" style="color: rgb(0, 113, 188); font-weight: bold; font-size: 13px;">[Fermer]</a></center></div>
	';
	}else {
	$email = $_GET['email'];
		$sql_insert = "INSERT INTO `clients` (
						`id` ,
						`web_id` ,
						`last_update` ,
						`login` ,
						`pass` ,
						`timestamp` ,
						`titre` ,
						`nom` ,
						`prenom` ,
						`fonction` ,
						`societe` ,
						`nb_salarie` ,
						`secteur_activite` ,
						`secteur_qualifie` ,
						`code_naf` ,
						`num_siret` ,
						`adresse` ,
						`complement` ,
						`ville` ,
						`cp` ,
						`pays` ,
						`infos_sup` ,
						`titre_l` ,
						`nom_l` ,
						`prenom_l` ,
						`societe_l` ,
						`adresse_l` ,
						`complement_l` ,
						`ville_l` ,
						`cp_l` ,
						`pays_l` ,
						`infos_sup_l` ,
						`coord_livraison` ,
						`tel1` ,
						`tel2` ,
						`tel_match` ,
						`fax1` ,
						`fax2` ,
						`url` ,
						`activationCode` ,
						`death` ,
						`actif` ,
						`email` ,
						`origin` ,
						`website_origin` ,
						`code` ,
						`tva_intra` ,
						`cegid_exported` ,
						`default_adresse` ,
						`default_adresse_l`
						)
						VALUES (
						'$id_users',  
						'',  
						'0',  
						'$email', 
						MD5(  '$password' ) , 
						$timestape ,  
						'',  
						'',  
						'',  
						'',  
						'',  
						'',  
						'',  
						'',  
						'',  
						'',  
						'',  
						'',  
						'',  
						'',  
						'',  
						'',  
						'',  
						'',  
						'',  
						'',  
						'',  
						'',  
						'',  
						'',  
						'',  
						'',  
						'0',  
						'$telephone',  
						'',  
						'',  
						'',  
						'',  
						'$siteweb',  
						'$activationCode',  
						'0',  
						'1', 
						'$email',  
						'O',  
						'TC',  
						'',  
						'',  
						'0',  
						'0',  
						'0')";
	mysql_query($sql_insert);	
	
	
	$sql_question  = "INSERT INTO  `q_a_questions` (
						`id` ,
						`id_user` ,
						`id_produit` ,
						`question` ,
						`vote` ,
						`pseudo` ,
						`etat` ,
						`date_create`
						)
						VALUES (
						NULL ,  '$id_users',  '$ids_products',  '$question_final',  '0','$pseudo' , '0', NOW( ))";
	mysql_query($sql_question);
	
	$sql_max_rep  = "SELECT MAX(id) as total FROM q_a_questions ";
	$req_max_rep  = mysql_query($sql_max_rep);
	$data_max_rep = mysql_fetch_object($req_max_rep);
	
	$header_from_name_new	= "Techni-contact";
	$header_from_email	= 'ugc@techni-contact.com';

	$header_send1_name	= '';
	$header_send1_email	= $email;
	
	$header_send2_name	= '';
	$header_send2_email	= '';
	
	$header_reply1_name	= '';
	$header_reply1_email= '';
	
	$header_copy2_name	= '';
	$header_copy2_email	= '';
	
	$header_copy1_email	= '';
	$header_copy1_name	= '';
	$header_copy1_email	= '';

	$subject = 'Validez votre question sur Techni-Contact';
	
	$name_ptd = htmlentities($name_products, ENT_QUOTES); 
	$message_header = "<html><head>
						<meta http-equiv=Content-Type content=text/html; charset=iso-8859-1>
						</head>
						<body bgcolor=#FFFFFF>";
	$message_text	.= '<img src="http://www.techni-contact.com/media/emailings/mails-serveur-tc/logo-tc.jpg">';
	$message_text	.= '<p>';
						$message_text	.= 'Bonjour,';
						$message_text	.= '<br /><br />';
						$message_text	.= 'Vous venez de poser une question sur notre site internet concernant le produit <a href="'.$url.'" target="_blink">'.$name_ptd.'</a>.';
						$message_text	.= '<br /><br />';
						$message_text	.= 'Afin que les internautes ou nos experts puissent y r&eacute;pondre, veuillez cliquer sur le lien suivant';
						$message_text	.= '<br /><br />';
						$message_text	.= '<a href="'.$url.'?tokens_active='.$activationCode.'&id_product='.$ids_products.'&id_question='.$data_max_rep->total.'" target="_blink">'.$url.'?tokens_active='.$activationCode.'</a>';
						$message_text	.= '<br /><br />';
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
	
	$mail_send_etat	= php_mailer_external_send($header_from_name_new, $header_from_email, $header_send1_name, $header_send1_email, $header_send2_name, $header_send2_email, $header_reply1_email, $header_reply1_name, $header_copy1_email, $header_copy1_name, $header_copy2_email, $header_copy2_name, $subject, $message_to_send);
	
	
	/********************************************************************/
	
	
	$header_from_name_new2	= "Techni-contact";
	$header_from_email2	= 'ugc@techni-contact.com';

	$header_send1_name2	 = '';
	$header_send1_email2 = $email;
	
	$header_send2_name2	  = '';
	$header_send2_email2  = '';
	
	$header_reply1_name2   = 'ugc';
	$header_reply1_email2  = 'ugc@techni-contact.com';
	
	$header_copy2_name2	= '';
	$header_copy2_email2	= '';
	
	$header_copy1_email2	= '';
	$header_copy1_name2	= '';
	$header_copy1_email2	= '';

	$subject2 = 'Creation votre compte gratuit';
	
	$name_ptd2 = htmlentities($name_products, ENT_QUOTES); 
	$message_header2 = "<html><head>
						<meta http-equiv=Content-Type content=text/html; charset=iso-8859-1>
						</head>
						<body bgcolor=#FFFFFF>";
	$message_text2	.= '<img src="http://www.techni-contact.com/media/emailings/mails-serveur-tc/logo-tc.jpg">';
	$message_text2	.= '<p>';
	
	$message_text2	.= 'Bonjour '.$email;
	$message_text2	.= '<br /><br />';
	$message_text2	.= 'Vous venez de poser une question sur <a href="http://www.techni-contact.com">Techni-Contact</a>.  Nous vous en remercions.';
	$message_text2	.= '<br /><br />';
	$message_text2	.= 'Nous nous connaissons un peu mieux d&eacute;sormais, c\'est pour cela que vous pouvez b&eacute;n&eacute;ficier maintenant d\'un compte client sur notre site :';
	$message_text2	.= '<br /><br />';
	$message_text2	.= 'Adresse e-mail : '.$email;
	$message_text2	.= '<br />';
	$message_text2	.= 'Mot de passe : '.$password;
	$message_text2	.= '<br />';
	$message_text2	.= 'Num&eacute;ro de client : '.$id_users;
	$message_text2	.= '<br /><br />';
	$message_text2	.= 'Vous pouvez changer votre mot de passe &agrave; tout moment depuis votre <a href="https://secure.techni-contact.com/fr/compte/login.html">espace mon compte.</a>';
    $message_text2	.= '<br /><br />';
	$message_text2	.= 'Ce compte va vous permettre de profiter pleinement des services Techni-Contact :';
	$message_text2	.= '<ul>';
			$message_text2	.= '<li>Demandes de devis</li>';
			$message_text2	.= '<li>Commandes en ligne</li>';
			$message_text2	.= '<li>Dialogue avec nos experts</li>';
	$message_text2	.= '</ul>';

	$message_text2	.= 'Nous esp&eacute;rons vous revoir tr&egrave;s bient&ocirc;t sur Techni-Contact.';
	$message_text2	.= '<br /><br />';
	
	$message_text2	.= '<br /><br />';
	//$message_text2	.= 'Nous vous souhaitons une bonne DAYTIME_TERM.<br />';
	$message_text2	.= '<strong>Techni-Contact est &eacute;dit&eacute; par</strong> <br /> Md2i <br />';
	$message_text2	.= '253 rue Gallieni <br />';
	$message_text2	.= 'F-92774 BOULOGNE BILLANCOURT cedex <br />';
	$message_text2	.= 'Tel : 01 55 60 29 29 (appel local)<br /> Fax: 01 72 08 01 18<br />';
	$message_text2	.= '<a href="http://www.techni-contact.com">http://www.techni-contact.com</a><br /><br />';
	$message_text2	.= 'SAS au capital de 160.000 &euro;<br />
							SIRET : 392 772 497 000 39<br />
							NAF : 4791B<br />
							TVA n&deg; FR12 392 772 497<br />
							R.C. NANTERRE B 392 772 497<br />';
	$message_text	.= '</p>';					
	$message_bottom2 = "</body></html>";
				 
	$message_to_send2 = $message_header2 . $message_text2 . $message_bottom2;	
	
	$mail_send_etat2	= php_mailer_external_send($header_from_name_new2, $header_from_email2, $header_send1_name2, $header_send1_email2, $header_send2_name2, $header_send2_email2, $header_reply1_email2, $header_reply1_name2, $header_copy1_email2, $header_copy1_name2, $header_copy2_email2, $header_copy2_name2, $subject2, $message_to_send2);
	
	/********************************************************************/
	
	echo '<div class="message_views"><p>Merci pour votre question. </p><p>Afin que nous puissions y répondre, veuillez simplement cliquer sur le lien du mail qui vient d’être envoyé à l’adresse  <strong>'.$email.'</strong> </p><p>Vous êtes un nouvel utilisateur de notre site internet. Afin de profiter de l’ensemble de nos services, nous vous avons attribué un compte client</p></div>
	<div><center><a href="#" onclick="close_popup_create()" style="color: rgb(0, 113, 188); font-weight: bold; font-size: 13px;">[Fermer]</a></center></div>
	';
	
	}
	

?>