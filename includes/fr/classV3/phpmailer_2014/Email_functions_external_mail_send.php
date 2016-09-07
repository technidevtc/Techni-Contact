<?php
	
	function php_mailer_external_send($header_from_name, $header_from_email, $header_send1_name, $header_send1_email, $header_send2_name, $header_send2_email, $header_reply1_email, $header_reply1_name, $header_copy1_email, $header_copy1_name, $header_copy2_email, $header_copy2_name, $subject, $message_to_send,$attachment= null){
	
		//****************************************************************************************************************/
		//**************************** Start Integration PHPMailer *********************************************************/
		//***************************************************************************************************************/

			//require_once('../../includes/fr/classV3/phpmailer_2014/PHPMailerAutoload.php');
			$mail = new PHPMailer;
			$mail->setLanguage('fr', 'language/directory/');



			$mail->isSMTP();                                      // Set mailer to use SMTP
			$mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
			//$mail->Port       = 587;
			$mail->SMTPAuth = true;                               // Enable SMTP authentication
			$mail->Username = 'evmail@techni-contact.com';                 // SMTP username
			$mail->Password = '4698gk-mpo';                           // SMTP password
			$mail->SMTPSecure = 'tls';                            // Enable encryption, 'ssl' also accepted

			$mail->From = $header_from_email;
			$mail->FromName = $header_from_name;
			$mail->addAddress($header_send1_email, $header_send1_name);     // Add a recipient
			if(!empty($header_send2_email)){
				$mail->addAddress($header_send2_email, $header_send2_name);     // Add a recipient
			}
			//$mail->addAddress('z.abidi@techni-contact.com');               // Name is optional
			
			
			if(!empty($header_reply1_email)){
				$mail->addReplyTo($header_reply1_email, $header_reply1_name);
			}
			
			//$mail->addReplyTo('info@example.com', 'Information');
			
			if(!empty($header_copy1_email)){
				$mail->addCC($header_copy1_email, $header_copy1_name);
			}
			
			if(!empty($header_copy2_email)){
				$mail->addCC($header_copy2_email, $header_copy2_name);
			}
			
			if(!empty($attachment)){
				$mail->AddAttachment($attachment);
			}
			
			
			
			//$mail->addCC('cc@example.com');
			//$mail->addBCC('bcc@example.com');

			$mail->WordWrap = 50;                                 // Set word wrap to 50 characters
			//$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
			//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
			$mail->isHTML(true);                                  // Set email format to HTML

			$mail->Subject = $subject;
			$mail->Body    = $message_to_send;
			//$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

			if(!$mail->send()) {
				//echo 'Message could not be sent.';
				//echo 'Erreurs: ' . $mail->ErrorInfo;
				return 'Erreurs: ' . $mail->ErrorInfo;
			} else {
				//echo 'Message has been sent';
				return '1';
			}


		//****************************************************************************************************************/
		//**************************** End Integration PHPMailer *********************************************************/
		//***************************************************************************************************************/
	
	}


?>