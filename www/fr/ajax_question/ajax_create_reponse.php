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
	$id_question 	= $_GET['id_question'];
	$telephone 		= $_GET['telephone'];
	$url 			= $_GET['url'];
	
	$pseudo 		= mysql_real_escape_string($_GET['pseudo']);
	$reponse 		= mysql_real_escape_string($_GET['reponse']);
	
	$today = date("Y-m-d H:i:s");  
	$id 		= generated_code(9);
	$password   = generated_pass(5);
	$timestape  = unix_timestamp($today);
	$activationCode = generated_pass(30);
	
	$sql_idAdvertiser = "SELECT bou.name,bou.phone,bou.email
						 FROM products_fr pfr ,  advertisers aa , bo_users bou 
						 WHERE  pfr.idAdvertiser  = aa.id 
						 AND bou.id  = aa.idCommercial
						 AND pfr.id =$ids_products ";
	$req_idAdvertiser = mysql_query($sql_idAdvertiser);
	$data_idAdvertiser= mysql_fetch_object($req_idAdvertiser);
	
	
	$sql_quest = "SELECT id,question,pseudo ,id_user,date_create
					  FROM q_a_questions 
					  WHERE id='".$id_question."' 
					  AND etat='1' ";
	$req_quest  = mysql_query($sql_quest);
	$data_quest = mysql_fetch_object($req_quest);
	
	
	$sql_email_client = "SELECT email
						 FROM clients
						 WHERE id='".$data_quest->id_user."' ";
	$req_email_client = mysql_query($sql_email_client);
	$data_email_client= mysql_fetch_object($req_email_client);
	
	if(!empty($rows_email)){
		$data_users  = mysql_fetch_object($req_email);
		echo '<div class="message_views">
			<p>
			Merci d\'avoir apport&eacute; votre expertise &agrave; la communaut&eacute; Techni-contact. Pour valider votre r&eacute;ponse, merci de cliquer sur le lien du mail qui vient de vous &ecirc;tre envoy&eacute; sur <strong>'.$email.'</strong>
			</p>
		</div>
		<div><center><a href="#" onclick="close_popup_repondre()" style="color: rgb(0, 113, 188); font-weight: bold; font-size: 13px;">[Fermer]</a></center></div>';
		  
	$sql_update_users = "UPDATE clients SET activationCode ='$activationCode',tel1='$telephone' WHERE id='".$data_users->id."' ";	
	mysql_query($sql_update_users);
		
		$sql_insert = "INSERT INTO `q_a_reponses` (
					`id` ,
					`id_user` ,
					`id_question` ,
					`reponse` ,
					`vote` ,
					`etat` ,
					`pseudo` ,
					`date_create` ,
					`date_update`
					)
					VALUES (
					NULL ,  '".$data_users->id."',  '$id_question',  '$reponse',  '0',  '0',  '$pseudo', NOW( ) ,  '0000-00-00 00:00:00')";
		mysql_query($sql_insert);
	
	$sql_max_rep  = "SELECT MAX(id) as total FROM q_a_reponses ";
	$req_max_rep  = mysql_query($sql_max_rep);
	$data_max_rep = mysql_fetch_object($req_max_rep);
	
	$sql_get_rep  = "SELECT date_create FROM q_a_reponses WHERE id='".$data_max_rep->total."' ";
	$req_get_rep  = mysql_query($sql_get_rep);
	$data_get_rep = mysql_fetch_object($req_get_rep);
	$date_creation = date("d/m/Y",strtotime($data_get_rep->date_create));
	
	
	
/*************         •	Mail de demande de confirmation de la r&eacute;ponse               ***********/	
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

	$subject = 'Validez votre réponse sur Techni-Contact';
	$name_ptd = htmlentities($name_products, ENT_QUOTES); 
	
	//$question = htmlentities($data_quest->question, ENT_QUOTES); 
	//$question_ff = str_replace("<br />","<br />",$question);
	
	//$reponse  = htmlentities($reponse, ENT_QUOTES);
	//$reponse_ff = str_replace("<br />","<br />",$reponse);
	
	$question = utf8_decode($data_quest->question); 
	$reponse  = utf8_decode($reponse);
	$pseudo   = htmlentities($pseudo, ENT_QUOTES);

	$message_header = "<html><head>
						<meta http-equiv=Content-Type content=text/html; charset=iso-8859-1>
						</head>
						<body bgcolor=#FFFFFF>";
	$message_text	.= '<img src="http://www.techni-contact.com/media/emailings/mails-serveur-tc/logo-tc.jpg">';
	$message_text	.= '<p>';
						$message_text	.= 'Bonjour';
						$message_text	.= '<br /><br />';
						
						$message_text	.= 'Vous venez de r&eacute;pondre &agrave; une question pos&eacute;e sur notre site internet concernant le produit <a href="'.$url.'" target="_blink">'.$name_ptd.'</a>.';
						$message_text	.= '<br /><br />';
						$message_text	.= 'Merci de valider votre r&eacute;ponse en cliquant sur le lien ci-dessous :';
						$message_text	.= '<br /><br />';
						$message_text	.= '<a href="'.$url.'?tokens_active_reponse='.$activationCode.'" target="_blink">'.$url.'?tokens_active_reponse='.$activationCode.'</a>';
						$message_text	.= '<br /><br />';
						$message_text	.= 'Votre r&eacute;ponse est pr&eacute;cieuse et nous vous remercions infiniment pour avoir contribu&eacute; &agrave; la communaut&eacute; Techni-contact.';
						$message_text	.= '<br /><br />';
						$message_text	.= '<strong>Question : </strong><br />'.$question.'<br /> ';
						$message_text	.= '<strong>Votre r&eacute;ponse : </strong><br />'.$reponse.'<br /> ';
						$message_text	.= '<br />';
						$message_text	.= 'Si d\'autres utilisateurs apportent aussi leur contribution &agrave; la question pos&eacute;e, nous vous en informerons par email.';
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
/*************         •	Mail de demande de confirmation de la r&eacute;ponse               ***********/	



/*************         •	  Mail de notification &agrave; TC             ***********/	

	$header_from_name_new2	= "Techni-contact";
	$header_from_email2	    = 'ugc@techni-contact.com';

	$header_send1_name2	    = '';
	$header_send1_email2	= 'ugc@techni-contact.com';
	
	$header_send2_name2	    = '';
	$header_send2_email2	= '';
	
	$header_reply1_name2	= '';
	$header_reply1_email2   = '';
	
	$header_copy2_name2	    = '';
	$header_copy2_email2	= '';
	
	$header_copy1_email2    = '';
	$header_copy1_name2	    = '';
	$header_copy1_email2	= '';

	
	$subject_name = utf8_decode($name_products);
	
	
	$subject2 = 'Réponse à question posée sur fiche '.$subject_name;
					
					$message_header2 = "<html><head>
						<meta http-equiv=Content-Type content=text/html; charset=iso-8859-1>
						</head>
						<body bgcolor=#FFFFFF>";
					$message_text2	.= '<img src="http://www.techni-contact.com/media/emailings/mails-serveur-tc/logo-tc.jpg">';
					$message_text2	.= '<p>';
						$message_text2	.= 'Bonjour,';
						$message_text2	.= '<br /><br />';
						
						$message_text2	.= 'Le '.$date_creation.', <strong>'.$pseudo.'</strong> un internaute, vient de publier une r&eacute;ponse &agrave; une question sur la fiche du produit <a href="'.$url.'" target="_blink">'.$name_ptd.'</a>.';
						$message_text2	.= '<br /><br />';
						$message_text2	.= '<strong>Adresse mail :   </strong>'.$email.'<br />';
						$message_text2	.= '<strong>Question :       </strong>'.$question.'<br />';
						$message_text2	.= '<strong>R&eacute;ponse : </strong>'.$reponse.'<br />';
						$message_text2	.= '<br />';
						$message_text2	.= '<a href="'.ADMIN_URL.'q_a_products/fiche_q_a.php?id_product='.$ids_products.'">Acc&eacute;der &agrave; la question </a> &nbsp;|&nbsp; <a href="'.$url.'"> Voir la fiche produit </a>';
						$message_text2	.= '<br /><br />';
						$message_text2	.= '</p>';
					$message_bottom2 = "</body></html>";
								 
	$message_to_send2 = $message_header2 . $message_text2 . $message_bottom2;		
	$mail_send_etat	= php_mailer_external_send($header_from_name_new2, $header_from_email2, $header_send1_name2, $header_send1_email2, $header_send2_name2, $header_send2_email2, $header_reply1_email2, $header_reply1_name2, $header_copy1_email2, $header_copy1_name2, $header_copy2_email2, $header_copy2_name2, $subject2, $message_to_send2);
	
/*************         •	Mail de notification &agrave; TC               ***********/		
	

/*************         •	Mail d’information &agrave; l’auteur de la question                ***********/	
	
	$header_from_name_new3	= "Techni-contact";
	$header_from_email3	= 'ugc@techni-contact.com';

	$header_send1_name3	= '';
	$header_send1_email3	= $data_email_client->email;
	
	$header_send2_name3	= '';
	$header_send2_email3	= '';
	
	$header_reply1_name3	= '';
	$header_reply1_email3= '';
	
	$header_copy2_name3	= '';
	$header_copy2_email3	= '';
	
	$header_copy1_email3	= '';
	$header_copy1_name3	= '';
	$header_copy1_email3	= '';
	
	$subject3 = 'Réponse à votre question sur le produit  '.$subject_name;
					
					$message_header3 = "<html><head>
						<meta http-equiv=Content-Type content=text/html; charset=iso-8859-1>
						</head>
						<body bgcolor=#FFFFFF>";
					$message_text3	.= '<img src="http://www.techni-contact.com/media/emailings/mails-serveur-tc/logo-tc.jpg">';
					$message_text3	.= '<p>';
						$message_text3	.= 'Bonjour '.$data_email_client->email.',';
						$message_text3	.= '<br /><br />';
						$message_text3	.= 'Nous avons une bonne nouvelle !';
						$message_text3	.= '<br /><br />';
						$message_text3	.= 'Le '.$date_creation.', <strong>'.$pseudo.'</strong>, un internaute, a r&eacute;pondu &agrave; la question que vous aviez post&eacute;e pour le produit <a href="'.$url.'" target="_blink">'.$name_ptd.'</a> sur Techni-Contact.';
						$message_text3	.= '<br /><br />';
						$message_text3	.= '<strong>Rappel de votre question :</strong> <br />';
						$message_text3	.= ''.$question.'<br /><br />';
						$message_text3	.= '<strong>R&eacute;ponse de '.$pseudo .' </strong><br />';
						$message_text3	.= ''.$reponse.'';
						$message_text3	.= '<br /><br />';
						
						$message_text3	.= 'N\'h&eacute;sitez pas &agrave; contacter notre expert pour tout compl&eacute;ment d\'information sur le produit : ';
						$message_text3	.= '<br /><br />';
						
						$message_text3	.= ''.$data_idAdvertiser->name;
						$message_text3	.= '<br />';
								$message_text3	.= ''.$data_idAdvertiser->phone;
						$message_text3	.= '<br />';
								$message_text3	.= ''.$data_idAdvertiser->email;
						$message_text3	.= '<br /><br />';
						$message_text3	.= 'A tr&egrave;s bient&ocirc;t sur <a href="http://www.techni-contact.com">Techni-Contact</a> ';
						$message_text3	.= '<br /><br />';
						$message_text3	.= '<strong>Techni-Contact est &eacute;dit&eacute; par</strong> <br /> Md2i <br />';
						$message_text3	.= '253 rue Gallieni <br />';
						$message_text3	.= 'F-92774 BOULOGNE BILLANCOURT cedex <br />';
						$message_text3	.= 'Tel : 01 55 60 29 29 (appel local)<br /> Fax: 01 72 08 01 18<br />';
						$message_text3	.= '<a href="http://www.techni-contact.com">http://www.techni-contact.com</a><br /><br />';
						$message_text3	.= 'SAS au capital de 160.000 &euro;<br />
							SIRET : 392 772 497 000 39<br />
							NAF : 4791B<br />
							TVA n&deg; FR12 392 772 497<br />
							R.C. NANTERRE B 392 772 497<br />';
						
						$message_text3	.= '</p>';

					$message_bottom3 = "</body></html>";
								 
	$message_to_send3 = $message_header3 . $message_text3 . $message_bottom3;		
	$mail_send_etat	= php_mailer_external_send($header_from_name_new3, $header_from_email3, $header_send1_name3, $header_send1_email3, $header_send2_name3, $header_send2_email3, $header_reply1_email3, $header_reply1_name3, $header_copy1_email3, $header_copy1_name3, $header_copy2_email3, $header_copy2_name3, $subject3, $message_to_send3);
	
/*************         •	Mail d’information &agrave; l’auteur de la question              ***********/
	
	
		
	}else {
		echo '<div class="message_views">		
		<p>Vous &agrave;tes un nouvel utilisateur de notre site internet. Afin de profiter de l\'ensemble de nos services, nous vous avons attribu&eacute; un compte client </p></div>
		<div><center><a href="#" onclick="close_popup_repondre()" style="color: rgb(0, 113, 188); font-weight: bold; font-size: 13px;">[Fermer]</a></center></div>
		';
		
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
						'$id',  
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
		
		
	$sql_insert = "INSERT INTO `q_a_reponses` (
					`id` ,
					`id_user` ,
					`id_question` ,
					`reponse` ,
					`vote` ,
					`etat` ,
					`pseudo` ,
					`date_create` ,
					`date_update`
					)
					VALUES (
					NULL ,  '".$id."',  '$id_question',  '$reponse',  '0',  '0',  '$pseudo', NOW( ) ,  '0000-00-00 00:00:00')";
	mysql_query($sql_insert);
	
	$name_ptd = htmlentities($name_products, ENT_QUOTES); 
	
	$question = utf8_decode($data_quest->question); 
	$reponse  = utf8_decode($reponse);
	
	$pseudo   = htmlentities($pseudo, ENT_QUOTES);	
	$subject_name = utf8_decode($name_products);

	
	$sql_max_rep  = "SELECT MAX(id) as total FROM q_a_reponses ";
	$req_max_rep  = mysql_query($sql_max_rep);
	$data_max_rep = mysql_fetch_object($req_max_rep);
	
	$sql_get_rep  = "SELECT date_create FROM q_a_reponses WHERE id='".$data_max_rep->total."' ";
	$req_get_rep  = mysql_query($sql_get_rep);
	$data_get_rep = mysql_fetch_object($req_get_rep);
	$date_creation = date("d/m/Y",strtotime($data_get_rep->date_create));
	
/*************         •	Mail de demande de confirmation de la r&eacute;ponse               ***********/	
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

	$subject = 'Validez votre réponse sur Techni-Contact';


	$name_ptd = htmlentities($name_products, ENT_QUOTES); 
	$message_header = "<html><head>
						<meta http-equiv=Content-Type content=text/html; charset=iso-8859-1>
						</head>
						<body bgcolor=#FFFFFF>";
	$message_text	.= '<img src="http://www.techni-contact.com/media/emailings/mails-serveur-tc/logo-tc.jpg">';
	$message_text	.= '<p>';
						$message_text	.= 'Bonjour';
						$message_text	.= '<br /><br />';
						
						$message_text	.= 'Vous venez de r&eacute;pondre &agrave; une question pos&eacute;e sur notre site internet concernant le produit <a href="'.$url.'" target="_blink">'.$name_ptd.'</a>.';
						$message_text	.= '<br /><br />';
						$message_text	.= 'Merci de valider votre r&eacute;ponse en cliquant sur le lien ci-dessous :';
						$message_text	.= '<br /><br />';
						$message_text	.= '<a href="'.$url.'?tokens_active_reponse='.$activationCode.'" target="_blink">'.$url.'?tokens_active_reponse='.$activationCode.'</a>';
						$message_text	.= '<br /><br />';
						$message_text	.= 'Votre r&eacute;ponse est pr&eacute;cieuse et nous vous remercions infiniment pour avoir contribu&eacute; &agrave; la communaut&eacute; Techni-contact.';
						$message_text	.= '<br /><br />';
						$message_text	.= '<strong>Question : </strong><br />'.$question.'<br /> ';
						$message_text	.= '<strong>Votre r&eacute;ponse : </strong><br />'.$reponse.'<br /> ';
						$message_text	.= '<br /><br />';
						$message_text	.= 'Si d\'autres utilisateurs apportent aussi leur contribution &agrave; la question pos&eacute;e, nous vous en informerons par email.';
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
/*************         •	Mail de demande de confirmation de la r&eacute;ponse               ***********/	



/*************         •	  Mail de notification &agrave; TC             ***********/	


	$header_from_name_new2	= "Techni-contact";
	$header_from_email2	= 'ugc@techni-contact.com';

	$header_send1_name2	= '';
	$header_send1_email2	= 'ugc@techni-contact.com';
	
	$header_send2_name2	= '';
	$header_send2_email2	= '';
	
	$header_reply1_name2	= '';
	$header_reply1_email2= '';
	
	$header_copy2_name2	= '';
	$header_copy2_email2	= '';
	
	$header_copy1_email2	= '';
	$header_copy1_name2	= '';
	$header_copy1_email2	= '';
	

	$subject2 = 'Réponse à question posée sur fiche '.$subject_name;
					
					$message_header2 = "<html><head>
						<meta http-equiv=Content-Type content=text/html; charset=iso-8859-1>
						</head>
						<body bgcolor=#FFFFFF>";
					$message_text2	.= '<img src="http://www.techni-contact.com/media/emailings/mails-serveur-tc/logo-tc.jpg">';
					$message_text2	.= '<p>';
						$message_text2	.= 'Bonjour,';
						$message_text2	.= '<br /><br />';
						
						$message_text2	.= 'Le '.$date_creation.', <strong>'.$pseudo.'</strong> un internaute, vient de publier une r&eacute;ponse &agrave; une question sur la fiche du produit <a href="'.$url.'" target="_blink">'.$name_ptd.'</a>.';
						$message_text2	.= '<br /><br />';
						$message_text2	.= '<strong>Adresse mail : </strong>'.$email.'<br />';
						$message_text2	.= '<strong>Question :     </strong>'.$question.'<br />';
						$message_text2	.= '<strong>R&eacute;ponse :     </strong>'.$reponse.'<br />';
						$message_text2	.= '<br />';
						$message_text2	.= '<a href="'.ADMIN_URL.'q_a_products/fiche_q_a.php?id_product='.$ids_products.'">Acc&eacute;der &agrave; la question</a> &nbsp;| &nbsp;<a href="'.$url.'"> Voir la fiche produit</a>';
						$message_text2	.= '<br /><br />';
						$message_text2	.= '</p>';

					$message_bottom2 = "</body></html>";
								 
	$message_to_send2 = $message_header2 . $message_text2 . $message_bottom2;		
	$mail_send_etat2	= php_mailer_external_send($header_from_name_new2, $header_from_email2, $header_send1_name2, $header_send1_email2, $header_send2_name2, $header_send2_email2, $header_reply1_email2, $header_reply1_name2, $header_copy1_email2, $header_copy1_name2, $header_copy2_email2, $header_copy2_name2, $subject2, $message_to_send2);
	
/*************         •	Mail de notification &agrave; TC               ***********/		
	

/*************         •	Mail d’information &agrave; l’auteur de la question                ***********/	
		$header_from_name_new3	= "Techni-contact";
	$header_from_email3	= 'ugc@techni-contact.com';

	$header_send1_name3	= '';
	$header_send1_email3	= $data_email_client->email;
	
	$header_send2_name3	= '';
	$header_send2_email3	= '';
	
	$header_reply1_name3	= '';
	$header_reply1_email3= '';
	
	$header_copy2_name3	= '';
	$header_copy2_email3	= '';
	
	$header_copy1_email3	= '';
	$header_copy1_name3	= '';
	$header_copy1_email3	= '';

	
	$subject3 = 'Réponse à votre question sur le produit  '.$subject_name;
					
					$message_header3 = "<html><head>
						<meta http-equiv=Content-Type content=text/html; charset=iso-8859-1>
						</head>
						<body bgcolor=#FFFFFF>";
					$message_text3	.= '<img src="http://www.techni-contact.com/media/emailings/mails-serveur-tc/logo-tc.jpg">';
					$message_text3	.= '<p>';
						$message_text3	.= 'Bonjour '.$data_email_client->email.',';
						$message_text3	.= '<br /><br />';
						$message_text3	.= 'Nous avons une bonne nouvelle !';
						$message_text3	.= '<br /><br />';
						$message_text3	.= 'Le '.$date_creation.', <strong>'.$pseudo.'</strong>, un internaute, a r&eacute;pondu &agrave; la question que vous aviez post&eacute;e pour le produit <a href="'.$url.'" target="_blink">'.$name_ptd.'</a> sur Techni-Contact.';
						$message_text3	.= '<br /><br />';
						$message_text3	.= '<strong>Rappel de votre question :</strong> <br />';
						$message_text3	.= ''.$question.'<br /><br />';
						$message_text3	.= '<strong>R&eacute;ponse de '.$pseudo .' </strong><br />';
						$message_text3	.= ''.$reponse.'';
						$message_text3	.= '<br /><br />';
						
						$message_text3	.= 'N\'h&eacute;sitez pas &agrave; contacter notre expert pour tout compl&eacute;ment d\'information sur le produit : ';
						$message_text3	.= '<br /><br />';
						
						$message_text3	.= ''.$data_idAdvertiser->name;
						$message_text3	.= '<br />';
								$message_text3	.= ''.$data_idAdvertiser->phone;
						$message_text3	.= '<br />';
								$message_text3	.= ''.$data_idAdvertiser->email;
						$message_text3	.= '<br /><br />';
						$message_text3	.= 'A tr&egrave;s bient&ocirc;t sur <a href="http://www.techni-contact.com">Techni-Contact</a> ';
						$message_text3	.= '<br /><br />';
						$message_text3	.= '<strong>Techni-Contact est &eacute;dit&eacute; par</strong> <br /> Md2i <br />';
						$message_text3	.= '253 rue Gallieni <br />';
						$message_text3	.= 'F-92774 BOULOGNE BILLANCOURT cedex <br />';
						$message_text3	.= 'Tel : 01 55 60 29 29 (appel local)<br /> Fax: 01 72 08 01 18<br />';
						$message_text3	.= '<a href="http://www.techni-contact.com">http://www.techni-contact.com</a><br /><br />';
						$message_text3	.= 'SAS au capital de 160.000 &euro;<br />
							SIRET : 392 772 497 000 39<br />
							NAF : 4791B<br />
							TVA n&deg; FR12 392 772 497<br />
							R.C. NANTERRE B 392 772 497<br />';
						
						$message_text3	.= '</p>';

					$message_bottom3 = "</body></html>";
								 
	$message_to_send3 = $message_header3 . $message_text3 . $message_bottom3;	
	
	$mail_send_etat3	= php_mailer_external_send($header_from_name_new3, $header_from_email3, $header_send1_name3, $header_send1_email3, $header_send2_name3, $header_send2_email3, $header_reply1_email3, $header_reply1_name3, $header_copy1_email3, $header_copy1_name3, $header_copy2_email3, $header_copy2_name3, $subject3, $message_to_send3);
	
/*************         •	Mail d’information &agrave; l’auteur de la question              ***********/
	
	}

?>