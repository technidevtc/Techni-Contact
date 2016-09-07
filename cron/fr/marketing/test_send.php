<?php

require_once(dirname(__FILE__).'/../../../includes/fr/classV3/phpmailer_2014/PHPMailerAutoload.php');
	
	//Importing the file send function
	require_once(dirname(__FILE__).'/../../../includes/fr/classV3/phpmailer_2014/Email_functions_external_mail_send.php');
		
		
		$message_to_send = "message";
		$header_from_name	= 'test envoie';
		$header_from_email	= 'test@techni-contact.com';
		
		$header_send1_name	= '';
		$header_send1_email	= 'z.outarocht@techni-contact.com';
		//$header_send1_email	= 'aborded-transactions@techni-contact.com';
		

		
		$header_send2_name	= '';
		//$header_send2_email	= 't.henryg@techni-contact.com';
		$header_send2_email	= '';
		
		$header_reply1_name	= '';
		$header_reply1_email= 'test@techni-contact.com';
		
		
		
		$header_copy1_name	= '';
		$header_copy1_email	= '';
		//$header_copy1_email	= 'derroteteufel@gmail.com';
		
		$header_copy2_name	= '';
		$header_copy2_email	= '';

		//Send mail 
		$mail_send_etat	= php_mailer_external_send($header_from_name, $header_from_email, $header_send1_name, $header_send1_email, $header_send2_name, $header_send2_email, $header_reply1_email, $header_reply1_name, $header_copy1_email, $header_copy1_name, $header_copy2_email, $header_copy2_name, $subject, $message_to_send);
?>