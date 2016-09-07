<?php
	require_once('extranet_v3_functions.php'); 
	
	//Importing file function mail send
	require_once(dirname(__FILE__).'/../../../includes/fr/classV3/phpmailer_2014/PHPMailerAutoload.php');
	
	//Importing the file send function
	require_once(dirname(__FILE__).'/../../../includes/fr/classV3/phpmailer_2014/Email_functions_external_mail_send.php');
	
	
	$service				= mysql_escape_string($_POST['service']);
	$support_text			= mysql_escape_string($_POST['support_text']);
	
	//Types of service
	//	gfp => Gestion des fiches produit
	//	gcm => Service commercial
	//	gcp => Service comptabilité
	
	
	
	if(!empty($_SESSION['extranet_user_id'])){

		if(!empty($service) && !empty($support_text)){
		
		
			//Building mail 
				
				$header_from_name	= $_SESSION['extranet_user_name1'];
				$header_from_email	= $_SESSION['extranet_user_email'];
				
				$header_send1_name	= '';
				$header_send1_email	= '';
				
				$header_send2_name	= '';
				$header_send2_email	= '';
				
				$header_reply1_name	= '';
				$header_reply1_email= $_SESSION['extranet_user_email'];
				
				$header_copy1_email	= '';
				$header_copy1_name	= '';
				
				$header_copy2_name	= '';
				$header_copy2_email	= '';
				
				
				switch($service){
					case 'gfp':
						$service_show	= 'Gestion des fiches produit';
						
						$header_send1_name 	= $service_show;
						$header_send1_email	= 's.katfy@techni-contact.com';
						$header_send2_email	= 'b.dupont@techni-contact.com';
					break;
					
					case 'gcm':
						$service_show	= 'Service commercial';
						
						$header_send1_name 	= $service_show;
						$header_send1_email	= 'o.patti@techni-contact.com';
					break;
					
					case 'gcp':
						$service_show	= 'Service comptabilité';
						
						$header_send1_name 	= $service_show;
						$header_send1_email	= 'comptabilite@techni-contact.com';
					break;	
				}
				
				if(TEST){
					$header_send1_email	= 'z.abidi@techni-contact.com';
					$header_send2_email	= '';
				}
				
					
				$subject = "Message de l'annonceur ".$_SESSION['extranet_user_id']." [".$_SESSION['extranet_user_name1']."] - [".$service_show."]";
		
		
		
				$message_header = "<html><head>
								  <meta http-equiv=Content-Type content=text/html; charset=iso-8859-1>
								  </head>
								  <body bgcolor=#FFFFFF>";
  
				$message_text	= '<div style="font: normal 12px verdana,arial,sans-serif">';
				$message_text	.= '<img src="http://www.techni-contact.com/media/emailings/mails-serveur-tc/logo-tc.jpg">';
				
				if(TEST){
					$message_text	.= 'Adresse envoi : '.$header_send1_email.'<br /><br />';
				}
				
				
				$message_text	.= '<p>';
					$message_text	.= 'Bonjour';
					$message_text	.= ',<br /><br />';
					
					$message_text	.= 'L\'annonceur '.$_SESSION['extranet_user_name1'].' a post&eacute; un message depuis son extranet.';
					$message_text	.= '<br /><br />';
					
					$message_text	.= 'Date du message : '.date('d/m/Y H:i:s');
					$message_text	.= '<br /><br />';
					
					$message_text	.= 'Service concern&eacute; : '.$service_show;
					$message_text	.= '<br /><br />';
					
					$message_text	.= 'Message : '.$support_text;
					$message_text	.= '<br /><br />';
					
					//$message_text	.= 'Cordialement';
					
				$message_text	.= '</p>';
				
			$message_text	.= '</div>';	
			$message_bottom = "</body></html>";
			 
			$message_to_send = $message_header . $message_text . $message_bottom;	
				
				
			$mail_send_etat	= php_mailer_external_send($header_from_name, $header_from_email, $header_send1_name, $header_send1_email, $header_send2_name, $header_send2_email, $header_reply1_email, $header_reply1_name, $header_copy1_email, $header_copy1_name, $header_copy2_email, $header_copy2_name, $subject, $message_to_send);
				
				
			if($mail_send_etat==1){
				//That is OK
				echo('1');
			}else{
				//That is KO
				echo('0');
			}
			
			
		}else{
			echo('0');
		}//end else if !empty fields
	
	}else{
		echo('-1');
	}//End else if(!empty($_SESSION['extranet_user_id']))
?>