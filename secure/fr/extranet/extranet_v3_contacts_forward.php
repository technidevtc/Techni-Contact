<?php
	require_once('extranet_v3_functions.php'); 
	
	//Importing file function mail send
	require_once(dirname(__FILE__).'/../../../includes/fr/classV3/phpmailer_2014/PHPMailerAutoload.php');
	
	//Importing the file send function
	require_once(dirname(__FILE__).'/../../../includes/fr/classV3/phpmailer_2014/Email_functions_external_mail_send.php');
	
	if(!empty($_SESSION['extranet_user_id'])){
	
		$br				= mysql_escape_string($_POST['br']);
		$contact_id		= mysql_escape_string($_POST['id']);
	
		$local_loop			= 0;
		$row_informations	= explode('#',$br);
		
		//Reporting
		$send_ok 				= array();
		$send_ko 				= array();

		$mail_send_etat			= '';
		$mail_send_etat_query	= '';
		
		
		if(!empty($row_informations[$local_loop]) && !empty($contact_id)){
		
			while(!empty($row_informations[$local_loop])){
			
				$row_informations_one	= explode('|',$row_informations[$local_loop]);
				
				//Building mail 
				
				$header_from_name	= $_SESSION['extranet_user_name1'];
				$header_from_email	= $_SESSION['extranet_user_email'];
				
				$header_send1_name	= '';
				//mail in condition
				
				$header_send2_name	= '';
				$header_send2_email	= '';
				
				$header_reply1_name	= '';
				$header_reply1_email= $_SESSION['extranet_user_email'];
				
				$header_copy1_email	= '';
				$header_copy1_name	= '';
				
				$header_copy2_name	= '';
				$header_copy2_email	= '';
					
				$subject = 'Transfert du lead Techni-Contact n°'.$contact_id;
				
				if(TEST){
					$header_send1_email	= 'z.abidi@techni-contact.com';					
				}else{
					$header_send1_email	= $row_informations_one[1];
					//$header_send1_email	= 'derroteteufel@gmail.com';
				}
				
				
				$message_header = "<html><head>
								  <meta http-equiv=Content-Type content=text/html; charset=iso-8859-1>
								  </head>
								  <body bgcolor=#FFFFFF>";
  
				$message_text	= '<div style="font: normal 12px verdana,arial,sans-serif">';
				$message_text	.= '<img src="http://www.techni-contact.com/media/emailings/mails-serveur-tc/logo-tc.jpg">';
				
					if(TEST){
						$message_text	.= '<br />Adresse envoi : '.$row_informations_one[1].'<br /><br />';
					}
					
					
					$message_text	.= '<p>';
						$message_text	.= 'Bonjour';
						if(!empty($row_informations_one[0])){
							$message_text	.= ' '.$row_informations_one[0];
						}
						$message_text	.= ',<br /><br />';
						$message_text	.= $_SESSION['extranet_user_email'].' a pens&eacute; que cette opportunit&eacute; commerciale devrait vous int&eacute;resser';
						$message_text	.= '<br /><br />';
						
						$message_text	.= '<a href="'.EXTRANET_URL.'/extranet-v3-contacts-detail-print.html?id='.$contact_id.'&uid='.$_SESSION['extranet_user_webpass'].'&params=print">[Voir le contact]</a>';
						$message_text	.= '<br /><br />';
						
						$message_text	.= 'Cordialement';
		      				
					$message_text	.= '</p>';
					
				$message_text	.= '</div>';	
				$message_bottom = "</body></html>";
				 
				$message_to_send = $message_header . $message_text . $message_bottom;	
				  
				
				$mail_send_etat	= php_mailer_external_send($header_from_name, $header_from_email, $header_send1_name, $header_send1_email, $header_send2_name, $header_send2_email, $header_reply1_email, $header_reply1_name, $header_copy1_email, $header_copy1_name, $header_copy2_email, $header_copy2_name, $subject, $message_to_send);
				
				
				if($mail_send_etat==1){
					array_push($send_ok, $row_informations_one[1]);
					$mail_send_etat_query	= 'ok';
				}else{
					array_push($send_ko, $row_informations_one[1]);
					$mail_send_etat_query	= 'ko';
				}
				
				
				//Query Save history
				$res_save_forward_history = $db->query("INSERT INTO 
														extranet_contacts_forward_history(id, idAdvertiser, 
															lead_id, name, 
															email, date_send, 
															etat_send, source)
														
														values(NULL, ".$_SESSION['extranet_user_id'].", 
															".$contact_id.", '".$row_informations_one[0]."', 
															'".$row_informations_one[1]."', NOW(), 
															'".$mail_send_etat_query."', '".$row_informations_one[2]."')
														", __FILE__, __LINE__);
				
				$local_loop++;
			}
			
			//End operation show the reporting
			if(!empty($send_ok)){
				echo('<font color="green">Transfert effectu&eacute; avec succ&egrave;s pour '.count($send_ok));
					if(count($send_ok)>1){
						echo(' adresses');
					}else{
						echo(' adresse');
					}
				echo('</font>');
			}
			
			if(!empty($send_ko)){
				echo('<font color="red">Erreur de transfert pour ');
					if(count($send_ko)>1){
						echo('ces adresses : <br />');
					}else{
						echo('cette adresse : <br />');
					}
					
					$reporting_send_ko = 0;
					while(!empty($send_ko[$reporting_send_ko])){
						echo('&nbsp;&nbsp;'.$send_ko[$reporting_send_ko].' <br />');
						$reporting_send_ko++;
					}
				echo('</font>');
			}
			
			
			//echo('<br />'.$mail_send_etat);
			
		}else{
			echo('<font color="red">Erreur, Merci de v&eacute;rifier vos adresses mail.</font>');
		}
	
	}else{
		echo('&nbsp;<strong><a href="login.html">Merci de vous reconnecter.</a></strong>');
	}//end if empty session
?>