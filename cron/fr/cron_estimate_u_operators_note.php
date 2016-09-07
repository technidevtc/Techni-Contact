<?php 

	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

	require_once(dirname(__FILE__).'/../../includes/fr/classV3/phpmailer_2014/PHPMailerAutoload.php');	
	require_once(dirname(__FILE__).'/../../includes/fr/classV3/phpmailer_2014/Email_functions_external_mail_send.php');
	
	
$db = DBHandle::get_instance();
	

	$sql_lead  = "SELECT id, email,created_user_id,prenom,nom,FROM_UNIXTIME( updated_mail_sent_pdf ),source
				  FROM estimate 
				  WHERE DATE_ADD( FROM_UNIXTIME( updated_mail_sent_pdf ) , INTERVAL 20 MINUTE ) >= NOW() 
				  AND source ='2' 
				  AND status >='2'
				 ";	  
				  
	$req_lead  = mysql_query($sql_lead);
	// $data_lead = mysql_fetch_object($req_lead);
	

		while($data_lead = mysql_fetch_object($req_lead)){
			 
			$sql_check_email = "SELECT id FROM feedback_u_operators_note
							    WHERE email='".$data_lead->email."' ";
			$req_check_email =  mysql_query($sql_check_email);
			$data_check_email=  mysql_fetch_object($req_check_email);
			// echo $sql_check_email;
			if(empty($data_check_email->id)){
				
				$sql_insert = "INSERT INTO `feedback_u_operators_note` (
								`id` ,
								`interaction_type` ,
								`noted_operator` ,
								`email` ,
								`id_event` ,
								`note` ,
								`comment` ,
								`timestamp_email_sent` ,
								`timestamp_note`
								)
								VALUES (NULL ,  '2',  '".$data_lead->created_user_id."',  '".$data_lead->email."',  '".$data_lead->id."',  
								'0', '', NOW() , '0000-00-00 00:00:00')";
				
				mysql_query($sql_insert);
				
				$sql_max  = "SELECT MAX(id) as id 
							 FROM	feedback_u_operators_note ";
				$req_max  =  mysql_query($sql_max);
				$data_max =  mysql_fetch_object($req_max);
				
	$header_from_name_new	= "Techni-Contact – Service client";
	$header_from_email	= 'feedback-qualite-op@techni-contact.com';			
	$header_send1_email	= $data_lead->email;
	// $header_send1_email	= 'outarocht.zakaria@gmail.com';
	
	$header_send2_name	= '';
	$header_send2_email	= 'feedback-qualite-op@techni-contact.com';
	$header_send2_email	= '';
	
	$header_reply1_name	= '';
	$header_reply1_email= '';
	
	$header_copy1_email	= '';
	$header_copy1_name	= '';
	$header_copy1_email	= '';
	
	$header_copy2_name	= '';
	$header_copy2_email	= '';
		
	
	$subject_envoi = ' Suite à votre conversation avec Techni-Contact ';
	$subject = utf8_decode($subject_envoi) ;
	
	$message_header = "<html><head>
					  <meta http-equiv=Content-Type content=text/html; charset=iso-8859-1>
					  </head>
					  <body bgcolor=#FFFFFF>";
	$message_text	.= '<img src="http://www.techni-contact.com/media/emailings/mails-serveur-tc/logo-tc.jpg">';
	$message_text	.= '<p>';
			$message_text	.= 'Bonjour '.$data_lead->prenom.' '.$data_lead->nom.',';
			$message_text	.= '<br /><br />';
			$message_text	.= 'Vous venez d\'avoir une conversation avec l\'un de nos consultants Techni-Contact.';
			$message_text	.= '<br /><br />';
			$message_text	.= 'Sens de l\'accueil, prise de besoin, qualit&eacute; d\'&eacute;coute; quelle note donnez vous &agrave; notre expert ?';
			$message_text	.= '<br /><br />';	
			
			
			$message_text	.= 'Ma note : 
								<a href="'.URL.'customers-surveys/operators-note.html?id='.$data_max->id.'&note=1&utm_source='.$data_lead->email.'&utm_medium='.$data_lead->email.'&utm_campaign=survey-note-qualite-operateurfeedback">1 |</a>
								
								<a href="'.URL.'customers-surveys/operators-note.html?id='.$data_max->id.'&note=2&utm_source='.$data_lead->email.'&utm_medium='.$data_lead->email.'&utm_campaign=survey-note-qualite-operateurfeedback">2 |</a>
								
								<a href="'.URL.'customers-surveys/operators-note.html?id='.$data_max->id.'&note=3&utm_source='.$data_lead->email.'&utm_medium='.$data_lead->email.'&utm_campaign=survey-note-qualite-operateurfeedback">3 |</a>
								
								<a href="'.URL.'customers-surveys/operators-note.html?id='.$data_max->id.'&note=4&utm_source='.$data_lead->email.'&utm_medium='.$data_lead->email.'&utm_campaign=survey-note-qualite-operateurfeedback">4 |</a>
								
								<a href="'.URL.'customers-surveys/operators-note.html?id='.$data_max->id.'&note=5&utm_source='.$data_lead->email.'&utm_medium='.$data_lead->email.'&utm_campaign=survey-note-qualite-operateurfeedback">5 |</a>
								
								<a href="'.URL.'customers-surveys/operators-note.html?id='.$data_max->id.'&note=6&utm_source='.$data_lead->email.'&utm_medium='.$data_lead->email.'&utm_campaign=survey-note-qualite-operateurfeedback">6 |</a>
								
								<a href="'.URL.'customers-surveys/operators-note.html?id='.$data_max->id.'&note=7&utm_source='.$data_lead->email.'&utm_medium='.$data_lead->email.'&utm_campaign=survey-note-qualite-operateurfeedback">7 |</a>
								
								<a href="'.URL.'customers-surveys/operators-note.html?id='.$data_max->id.'&note=8&utm_source='.$data_lead->email.'&utm_medium='.$data_lead->email.'&utm_campaign=survey-note-qualite-operateurfeedback">8 |</a>
								
								<a href="'.URL.'customers-surveys/operators-note.html?id='.$data_max->id.'&note=9&utm_source='.$data_lead->email.'&utm_medium='.$data_lead->email.'&utm_campaign=survey-note-qualite-operateurfeedback">9 |</a>
								
								<a href="'.URL.'customers-surveys/operators-note.html?id='.$data_max->id.'&note=10&utm_source='.$data_lead->email.'&utm_medium='.$data_lead->email.'&utm_campaign=survey-note-qualite-operateurfeedback">10 |</a>';
							
			$message_text	.= '<br /><br />';
			$message_text	.= 'L&eacute;gende : ';
			$message_text	.= '<br /><br />';
			$message_text	.= '1 = Très mauvais contact<br />
								10 = Prestation parfaite';
			$message_text	.= '<br /><br />';
			$message_text	.= 'Nous vous remercions chaleureusement de votre retour qui va participer directement à l’am&eacute;lioration de nos services.';
			$message_text	.= '<br /><br />';
			
			$message_text	.= 'Techni-Contact est &eacute;dit&eacute; par<br />
								Md2i<br />
								253 rue Gallieni<br />
								F-92774 BOULOGNE BILLANCOURT cedex<br />
								Tel : 01 55 60 29 29 (appel local)<br />
								Fax: 01 83 62 36 12<br />
								<a href="http://www.techni-contact.com">http://www.techni-contact.com</a>';
			
			$message_text	.= '<br /><br />';
			$message_text	.= 'SAS au capital de 160.000 &euro;<br />
								SIRET : 392 772 497 000 39<br />
								NAF : 4791B<br />
								TVA n&deg; FR12 392 772 497<br />
								R.C. NANTERRE B 392 772 497<br />';
			$message_text	.= '</p>';
			$message_text	.= '</tr>';
			$message_text	.= '</table>';
			$message_text	.= '</p>';				
			$message_text	.= '</div>';
	$message_bottom = "</body></html>";
	
	$message_to_send = $message_header . $message_text . $message_bottom;
	
	//echo 		$path_attachement;	
	$mail_send_etat1	= php_mailer_external_send($header_from_name_new, $header_from_email, $header_send1_name, $header_send1_email, $header_send2_name, $header_send2_email, $header_reply1_email, $header_reply1_name, $header_copy1_email, $header_copy1_name, $header_copy2_email, $header_copy2_name, $subject, $message_to_send);
	// echo $message_text;
	$message_text="";
				
			}			
		}
	