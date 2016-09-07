<?php

if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
		require_once '../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}
	require("../../../../includes/fr/classV3/phpmailer_2014/PHPMailerAutoload.php");
	require("../../../../includes/fr/classV3/phpmailer_2014/Email_functions_external_mail_send.php");
	define('CHARSET', 'ISO-8859-1');
$db = DBHandle::get_instance();
$user = new BOUser();
$action = $_GET['action'];

if(!empty($action)){
	if($action == 'verify_id_products'){
		$id_fiche_products = $_POST['id_fiche_products'];
		$sql_verify = "SELECT id FROM products_fr WHERE id='$id_fiche_products' ";
		$req_verify = mysql_query($sql_verify);
		$rows_verify= mysql_num_rows($req_verify);
		if($rows_verify > 0){
			header('Location: fiche_q_a.php?id_product='.$id_fiche_products);
		}else{
			header('Location: q-a-fiches-produits.php?message_error=error');
		}
	}
	
	if($action == 'activer_question'){
		$id_question = $_GET['id_question'];
		$etat 		 = $_GET['etat'];
		
		if($etat == "activer"){
			$sql="UPDATE `q_a_questions` SET `etat` =  '1' WHERE `id` =$id_question";
			mysql_query($sql);
			
			$sql_reponse = "UPDATE `q_a_reponses` SET `etat` = '1' WHERE `id_question` =$id_question";
			mysql_query($sql_reponse);
			echo "<a href='#' onclick=desactiver_reponse('".$id_question."','desactiver')>D&eacute;sactiver </a>";
		}else if($etat == "desactiver"){
			$sql="UPDATE `q_a_questions` SET `etat` =  '0' WHERE `id` =$id_question";
			mysql_query($sql);
			
			$sql_reponse = "UPDATE `q_a_reponses` SET `etat` = '0' WHERE `id_question` =$id_question";
			mysql_query($sql_reponse);
			echo "<a href='#' onclick=desactiver_reponse('".$id_question."','activer')>Activer </a>";
		}
	}
	
	if($action == 'delete_reponse'){
		$id_reponse = $_GET['id_reponse'];
		$sql_delete = "DELETE FROM `q_a_reponses` WHERE `id` =$id_reponse ";
		mysql_query($sql_delete);
	}
	
	if($action == 'update_question'){
		$question    = mysql_real_escape_string($_GET['question']);
		$id_question = $_GET['id_question'];
		$sql_update = "UPDATE `q_a_questions` SET `question` = '$question' WHERE `id` =$id_question ";
		mysql_query($sql_update);
	}
	
	if($action == 'update_reponse'){
		$reponse    = mysql_real_escape_string($_GET['reponse']);
		$id_reponse = $_GET['id_reponse'];
		$sql_update = "UPDATE `q_a_reponses` SET `reponse` = '$reponse' WHERE `id` =$id_reponse ";
		mysql_query($sql_update);
	}
	
	if($action == 'create_question'){
		$question_create    = mysql_real_escape_string($_GET['question_create']);
		$pseudo_create    = mysql_real_escape_string($_GET['pseudo_create']);
		$id_product_create    = mysql_real_escape_string($_GET['id_product_create']);
		
		$sql_question  = "INSERT INTO  `q_a_questions` (
						`id` ,
						`id_user` ,
						`id_produit` ,
						`question` ,
						`vote` ,
						`pseudo` ,
						`etat` ,
						`date_create`
						)VALUES (
						NULL ,  '982650439',  '$id_product_create',  '$question_create',  '0','$pseudo_create' , '1', NOW( ))";
		mysql_query($sql_question);
	}
	
	if($action == 'create_reponse'){
		$id_question     = $_GET['id_question'];
		$reponse         = mysql_real_escape_string($_GET['reponse']);
		$pseudo_reponse  = mysql_real_escape_string($_GET['pseudo_reponse']);
		$name_products   = $_GET['name_products'];
		$sql_quest = "SELECT id,question,pseudo ,id_user,id_produit
					  FROM q_a_questions 
					  WHERE id='".$id_question."' ";
		$req_quest  = mysql_query($sql_quest);
		$data_quest = mysql_fetch_object($req_quest);
	
	
		$sql_email_client = "SELECT email
						 FROM clients
						 WHERE id='".$data_quest->id_user."' ";
		$req_email_client = mysql_query($sql_email_client);
		$data_email_client= mysql_fetch_object($req_email_client);
		
		$sql_famille  = "SELECT idFamily FROM products_families 
						WHERE idProduct='".$data_quest->id_produit."' ";
		$req_famille  = mysql_query($sql_famille);
		$data_famille = mysql_fetch_object($req_famille);
		
		$sql_ref_name = "SELECT ref_name FROM  products_fr 
						 WHERE id='".$data_quest->id_produit."' ";
		$req_ref_name = mysql_query($sql_ref_name);
		$data_ref_name= mysql_fetch_object($req_ref_name);
		$url = ''.URL.'produits/'.$data_famille->idFamily.'-'.$data_quest->id_produit.'-'.$data_ref_name->ref_name.'.html';
		
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
					NULL ,  '982650439',  '$id_question',  '$reponse',  '0',  '1',  '$pseudo_reponse', NOW( ) ,  '0000-00-00 00:00:00')";
		mysql_query($sql_insert);
	
		$sql_idAdvertiser = "SELECT bou.name,bou.phone,bou.email
						 FROM products_fr pfr ,  advertisers aa , bo_users bou 
						 WHERE  pfr.idAdvertiser  = aa.id 
						 AND bou.id  = aa.idCommercial
						 AND pfr.id ='".$data_quest->id_produit."' ";
		$req_idAdvertiser = mysql_query($sql_idAdvertiser);
		$data_idAdvertiser= mysql_fetch_object($req_idAdvertiser);
		
/*************         Mail d’information &agrave; l’auteur de la question         ***********/	
	//$data_email_client->email;
	$header_from_name_new3	= "Q&A Techni-contact";
	$header_from_email3	= 'ugc@techni-contact.com';

	$header_send1_name3	= '';
	$header_send1_email3	= $data_email_client->email;
	//$header_send1_email3	= 'z.outarocht@gmail.com';
	
	$header_send2_name3	= '';
	$header_send2_email3	= '';
	
	$header_reply1_name3	= '';
	$header_reply1_email3= '';
	
	$header_copy2_name3	= '';
	$header_copy2_email3	= '';
	
	$header_copy1_email3	= '';
	$header_copy1_name3	= '';
	$header_copy1_email3	= '';
	$date_creation  =  date("d/m/Y");

	$name_ptd = htmlentities($name_products, ENT_QUOTES); 
	//$question = htmlentities($data_quest->question, ENT_QUOTES); 
	$question    = utf8_decode($data_quest->question); 
	$reponse_ff  = utf8_decode($reponse); 

	$pseudo   = htmlentities($pseudo_reponse, ENT_QUOTES);
	$subject_name = utf8_decode($name_products);
	$aa = "à";
	$aa_sub = utf8_decode($aa);
	$subject3 = 'Reponse '.$aa_sub.' votre question sur le produit  '.$subject_name;
			
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
						$message_text3	.= 'Le '.$date_creation.', <strong> Le'.$pseudo.'</strong>, a r&eacute;pondu &agrave; la question que vous aviez post&eacute;e pour le produit <a href="'.$url.'" target="_blink">'.$name_ptd.'</a> sur Techni-Contact.';
						$message_text3	.= '<br /><br />';
						$message_text3	.= '<strong>Rappel de votre question :</strong> <br />';
						$message_text3	.= ''.$question.'<br /><br />';
						$message_text3	.= '<strong>R&eacute;ponse de '.$pseudo .' </strong><br />';
						$message_text3	.= ''.$reponse_ff.'';
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
	
	}
	
	if($action == 'send_mail'){
		$id_question  = $_GET['id_question'];
		$id_user_send = $_SESSION["id"];
		$sql_check  = "SELECT number_of_send FROM q_a_send_mail WHERE id_question='".$id_question."' ";
		$req_check  =  mysql_query($sql_check);
		$data_check =  mysql_fetch_object($req_check);
		
		
		
		if(empty($data_check->number_of_send )){
			$sql_insert = "INSERT INTO `q_a_send_mail` (`id`, `id_question`, `id_user_send`, `date_send`, `number_of_send`) 
				           VALUES (NULL, '$id_question', '$id_user_send', NOW(), '1')";
			mysql_query($sql_insert);
		}else {
			$number_of_send = $data_check->number_of_send + 1;
			$sql_update = "UPDATE `q_a_send_mail` SET  
								  `number_of_send` =  '$number_of_send', 
								  `date_send`= NOW(),
								  `id_user_send`='$id_user_send'
						   WHERE  id_question='".$id_question."' ";
			mysql_query($sql_update);
		}
		$sql_send_mail  = "SELECT id,date_send,id_user_send,number_of_send FROM q_a_send_mail WHERE id_question='".$id_question."' ";
		$req_send_mail  =  mysql_query($sql_send_mail);
		$data_send_mail =  mysql_fetch_object($req_send_mail);
		
		$date_send   	=  date('d/m/Y H:i', strtotime($data_send_mail->date_send));
		$sql_bo_usr  	=  "SELECT name,phone FROM bo_users WHERE id='".$data_send_mail->id_user_send."' ";
		$req_bo_usr  	=  mysql_query($sql_bo_usr);
		$data_no_usr 	=  mysql_fetch_object($req_bo_usr); 
		echo '
				<div  style="float: left; margin-right: 15px;">
				<span>Envoyée le <strong>'.$date_send.'</strong> par <strong>'.$data_no_usr->name.'</strong></span>
				</div>
				<div>
				<span class="img_send"><a href="javascript:send_mail('.$id_question.')" id="result-ajax-send">[Envoyer au fournisseur ('.$data_send_mail->number_of_send.') ]</a></span>
				<span><img src="images/wait.gif" id="img-send-mail" /></span>
				</div>
			  ';
		
		$sql_question  = "SELECT id_produit,question FROM q_a_questions WHERE id='".$id_question."' ";
		$req_question  =  mysql_query($sql_question);
		$data_question =  mysql_fetch_object($req_question);
		
		$sql_famille  = "SELECT idFamily FROM products_families 
						WHERE idProduct='".$data_question->id_produit."' ";
		$req_famille  = mysql_query($sql_famille);
		$data_famille = mysql_fetch_object($req_famille);
		
		$sql_ref_name = "SELECT pp.ref_name,pp.name, aa.email
						 FROM products_fr pp , advertisers aa
						 WHERE pp.idAdvertiser = aa.id
						 AND pp.id='".$data_question->id_produit."' ";
		$req_ref_name = mysql_query($sql_ref_name);
		$data_ref_name= mysql_fetch_object($req_ref_name);
		$url = ''.URL.'produits/'.$data_famille->idFamily.'-'.$data_question->id_produit.'-'.$data_ref_name->ref_name.'.html';
	
	$annonceur = utf8_decode($data_no_usr->name);
	
	$header_from_name_new3	= "Techni-contact - ".$annonceur;
	$header_from_email3	= 'commercial@techni-contact.com';

	$header_send1_name3	= '';
	//$header_send1_email3	= 't.henryg@techni-contact.com';
	$header_send1_email3	= $data_ref_name->email;
	
	$header_send2_name3	= '';
	$header_send2_email3	= '';
	
	$header_reply1_name3	= '';
	$header_reply1_email3= '';
	
	$header_copy2_name3	= '';
	$header_copy2_email3	= '';
	
	$header_copy1_email3	= '';
	$header_copy1_name3	= '';
	$header_copy1_email3	= '';
	
	$name_ptd = htmlentities($data_ref_name->name, ENT_QUOTES); 

	$question  = utf8_decode($data_question->question);
	
	$reponse  = htmlentities($reponse, ENT_QUOTES);
	$pseudo   = htmlentities($pseudo_reponse, ENT_QUOTES);
	
	$subject_name = utf8_decode($data_ref_name->name);

	$subject3 = 'Question client sur produit '.$subject_name.' - ID fiche '.$data_question->id_produit;
			
					$message_header3 = "<html><head>
						<meta http-equiv=Content-Type content=text/html; charset=iso-8859-1>
						</head>
						<body bgcolor=#FFFFFF>";
					$message_text3	.= '<img src="http://www.techni-contact.com/media/emailings/mails-serveur-tc/logo-tc.jpg">';
					$message_text3	.= '<p>';
					$message_text3	.= '<br />';
					$message_text3	.= 'ID fiche produit : '.$data_question->id_produit;
						$message_text3	.= '<br /><br />';
						$message_text3	.= 'Cher partenaire,';
						$message_text3	.= '<br /><br />';
						$message_text3	.= 'Un client nous a soumis une question sur l\'un de vos produits: <a href="'.$url.'" target="_blink">'.$name_ptd.'</a>.';
						$message_text3	.= '<br /><br />';
						$message_text3	.= 'Pourriez-vous nous aider &agrave; lui r&eacute;pondre ? ';
						$message_text3	.= '<br /><br />';
						$message_text3	.= '<strong>Question :</strong>'.$question;
						$message_text3	.= '<br /><br />';
						$message_text3	.= 'Pour nous transmettre les &eacute;l&eacute;ments d\'information demand&eacute;s par le client, veuillez simplement r&eacute;pondre &agrave; cet email.';
						$message_text3	.= '<br /><br />';
						$message_text3	.= 'Nous vous remercions pour votre collaboration. ';
						$message_text3	.= '<br /><br />';
						$message_text3	.= 'Cordialement, ';
						$message_text3	.= '<br /><br />';
						$message_text3	.= ''.$annonceur.'<br />'.$data_no_usr->phone;
						$message_text3	.= '<br /><br />';
						$message_text3	.= '</p>';
					$message_bottom3 = "</body></html>";
								 
	$message_to_send3 = $message_header3 . $message_text3 . $message_bottom3;		
	$mail_send_etat	= php_mailer_external_send($header_from_name_new3, $header_from_email3, $header_send1_name3, $header_send1_email3, $header_send2_name3, $header_send2_email3, $header_reply1_email3, $header_reply1_name3, $header_copy1_email3, $header_copy1_name3, $header_copy2_email3, $header_copy2_name3, $subject3, $message_to_send3);	
	}
	
	if($action == 'delete_question'){
		$id_question  = $_GET['id_question'];
		
		$sql_delete_question  =  "DELETE FROM q_a_questions WHERE id='".$id_question."' ";
		mysql_query($sql_delete_question);
		
		$sql_delete_reponse   =  "DELETE FROM q_a_reponses WHERE id_question='".$id_question."' ";
		mysql_query($sql_delete_reponse);
		
		$sql_delete_reponse   =  "DELETE FROM q_a_send_mail WHERE id_question='".$id_question."' ";
		mysql_query($sql_delete_reponse);		
	}
	
	
	
}


?>