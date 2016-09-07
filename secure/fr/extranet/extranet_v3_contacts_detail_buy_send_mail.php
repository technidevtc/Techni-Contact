<?php
	
	//Importing file function mail send
	require_once(dirname(__FILE__).'/../../../includes/fr/classV3/phpmailer_2014/PHPMailerAutoload.php');
	
	//Importing the file send function
	require_once(dirname(__FILE__).'/../../../includes/fr/classV3/phpmailer_2014/Email_functions_external_mail_send.php');
	

	//Building mail 
				
				$header_from_name	= 'Achat contact extranet';
				$header_from_email	= 'achat-lead-extranet@techni-contact.com';
				
				$header_send1_name	= '';
				$header_send1_email	= 'achat-lead-extranet@techni-contact.com';
				
				$header_send2_name	= '';
				$header_send2_email	= '';
				
				$header_reply1_name	= '';
				$header_reply1_email= '';
				
				$header_copy1_email	= '';
				$header_copy1_name	= '';
				
				$header_copy2_name	= '';
				$header_copy2_email	= '';
					
				$subject = 'Achat manuel du lead n°'.$contact_id.' par '.$_SESSION['extranet_user_name1'];
				
				
				
				$message_header = "<html><head>
								  <meta http-equiv=Content-Type content=text/html; charset=iso-8859-1>
								  </head>
								  <body bgcolor=#FFFFFF>";
  
				$message_text	= '<div style="font: normal 12px verdana,arial,sans-serif">';
				$message_text	.= '<img src="http://www.techni-contact.com/media/emailings/mails-serveur-tc/logo-tc.jpg">';
				
					
					
					$message_text	.= '<p>';
						$message_text	.= 'Bonjour';

						$message_text	.= ',<br /><br />';
						
						$message_text	.= 'L\'annonceur '.$_SESSION['extranet_user_name1'].' a acheté manuellement un contact non facturé dans son extranet.';
						//$message_text	.= 'Extranet Achat Contact n°'.$contact_id;
						$message_text	.= '<br /><br />';
						
						//$message_text	.= '<a href="'.EXTRANET_URL.'/extranet-v3-contacts-detail.html?id='.$contact_id.'&uid='.$_SESSION['extranet_user_webpass'].'">[Voir le contact]</a>';
						$message_text	.= '<a href="'.ADMIN_URL.'contacts/lead-detail.php?id='.$contact_id.'">[Voir le contact]</a>';
						$message_text	.= '<br /><br />';
						
						$message_text	.= 'Cordialement';
						
					$message_text	.= '</p>';
					
				$message_text	.= '</div>';	
				$message_bottom = "</body></html>";
				 
				$message_to_send = $message_header . $message_text . $message_bottom;	
				
				
				$mail_send_etat	= php_mailer_external_send($header_from_name, $header_from_email, $header_send1_name, $header_send1_email, $header_send2_name, $header_send2_email, $header_reply1_email, $header_reply1_name, $header_copy1_email, $header_copy1_name, $header_copy2_email, $header_copy2_name, $subject, $message_to_send);
				
				
				//if($mail_send_etat==1){

?>