<?php

	//Importing file function mail send
	require_once(dirname(__FILE__).'/../../includes/fr/classV3/phpmailer_2014/PHPMailerAutoload.php');
	
	//Importing the file send function
	require_once(dirname(__FILE__).'/../../includes/fr/classV3/phpmailer_2014/Email_functions_external_mail_send.php');
	
	if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
		require_once '../../config.php';
	}else{
		require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
	}

	$db = DBHandle::get_instance();
	
	//To get the Static Array "payment_status"	
	require_once(ICLASS.'CCommand.php');
	$clone_order	= new Command($db);
	
	

	error_reporting(E_ALL & ~E_NOTICE);

	$execution_date_start	= date('Y-m-d H:i:s');
	
	$hours_condition		= '48';
	
	
	//Dynamic date => 2months + 10days ((31*2)+10)*24
	//$hours_condition_end		= '1728';
	//$interval_condition_sql	= "FROM_UNIXTIME( o.validated ) >= DATE_SUB( NOW( ) , INTERVAL ".$hours_condition_end." HOUR )";
	
	//Fixed date => 2014-08-01
	$hours_condition_end		= '1406847600';
	$interval_condition_sql		= "o.validated>=".$hours_condition_end."";
	
	
	$res_get_orders = $db->query("SELECT
										FROM_unixtime(validated) unixtime_validated, 
										DATE_SUB( NOW() , INTERVAL ".$hours_condition." HOUR ) AS operation_time,

										o.id,
										o.societe,
										o.total_ttc,
										o.payment_status,
										ad.nom1,
										bou.name

									FROM
										`order` AS o
										LEFT JOIN supplier_order so ON o.id   = so.order_id
										LEFT JOIN advertisers 	 ad ON so.sup_id = ad.id
										LEFT JOIN bo_users  	 bou ON so.sender_id  = bou.id
									WHERE
										FROM_UNIXTIME( o.validated ) <= DATE_SUB( NOW( ) , INTERVAL ".$hours_condition." HOUR ) 
									AND 
										".$interval_condition_sql." 
									AND
										o.forecasted_ship=0
									AND 
										o.shipped=0
									AND 
										o.sav_opened=0
									AND
										o.partly_cancelled=0
									AND
										o.cancelled=0

									order by unixtime_validated desc", __FILE__, __LINE__);		
			
			$total_commandes	= mysql_num_rows($res_get_orders);
			if($total_commandes>0){
			
				// E-mail headers:
				//$headers ="MIME-Version: 1.0 \n";
				//$headers .= "Content-Type: text/html; charset=iso-8859-1 \n";
				//$headers .= "From: Alerte ADV<commandes@techni-contact.com>\n";
				//$headers .= "Reply-To: commandes@techni-contact.com\r\n";
				
				$header_from_name	= 'Alerte ADV';
				$header_from_email	= 'commandes@techni-contact.com';
				
				$header_send1_name	= '';
				//$header_send1_email	= 'z.abidi@techni-contact.com';
				$header_send1_email	= 'alerte-adv-supp-orders@techni-contact.com';
				
				$header_send2_name	= '';
				//$header_send2_email	= 't.henryg@techni-contact.com';
				$header_send2_email	= '';
				
				$header_reply1_name	= '';
				$header_reply1_email= '';
				
				
				$header_copy1_email	= '';
				$header_copy1_name	= '';
				//$header_copy1_email	= 'derroteteufel@gmail.com';
				
				$header_copy2_name	= '';
				$header_copy2_email	= '';
				
				$subject = 'Dates d\'expedition manquantes sur commandes';
  
				$message_header = "<html><head>
								  <meta http-equiv=Content-Type content=text/html; charset=iso-8859-1>
								  </head>
								  <body bgcolor=#FFFFFF>";
  
				$message_text	= '<div style="font: normal 12px verdana,arial,sans-serif">';
				$message_text	.= '<img src="http://www.techni-contact.com/media/emailings/mails-serveur-tc/logo-tc.jpg">';
				
					$message_text	.= '<p>';
						$message_text	.= 'Bonjour';
						$message_text	.= '<br /><br />';
						$message_text	.= 'Voici la liste des commandes valid&eacute;es depuis '.$hours_condition.'H sans date d\'exp&eacute;dition pr&eacute;visionnelle';
						$message_text	.= '<br /><br />';
					$message_text	.= '</p>';
					
					$message_text	.= '<p>';
						$message_text	.= '<table style="	border-style: solid;font-family: verdana,arial,sans-serif;font-size:13px;color:#333333;border-width: 1px;border-color: #666666;	border-collapse: collapse;">';
							$message_text	.= '<tr style="background-color: #b8bab4;">';
								$message_text	.= '<th>';
									$message_text	.= 'N&deg; commande';
								$message_text	.= '</th>';
								
								$message_text	.= '<th>';
									$message_text	.= 'Fournisseur';
								$message_text	.= '</th>';
								
								$message_text	.= '<th>';
									$message_text	.= 'Date validation';
								$message_text	.= '</th>';
								
								$message_text	.= '<th>';
									$message_text	.= 'Soci&eacute;t&eacute;';
								$message_text	.= '</th>';
								
								$message_text	.= '<th>';
									$message_text	.= 'Montant total TTC';
								$message_text	.= '</th>';
								
								$message_text	.= '<th>';
									$message_text	.= 'Mode de paiement';
								$message_text	.= '</th>';
								
								$message_text	.= '<th>';
									$message_text	.= 'Nom de l\'op&eacute;rateur';
								$message_text	.= '</th>';
								
								
								
								
							$message_text	.= '</tr>';
						
						while($content_get_orders = $db->fetchAssoc($res_get_orders)){
							$message_text	.= '<tr>';
								$message_text	.= '<td style="border-width: 1px;padding: 8px;	border-style: solid;	border-color: #666666;	background-color: #ffffff;">';
									$message_text	.= '<a href="'.SECURE_URL.'manager/orders/order-detail.php?id='.$content_get_orders['id'].'" target="_blank">';
										$message_text	.= $content_get_orders['id'];
									$message_text	.= '</a>';
								$message_text	.= '</td>';
								
								$message_text	.= '<td style="border-width: 1px;padding: 8px;	border-style: solid;	border-color: #666666;	background-color: #ffffff;">';
									$message_text	.= utf8_decode($content_get_orders['nom1']);
								$message_text	.= '</td>';
								
								$message_text	.= '<td style="border-width: 1px;padding: 8px;	border-style: solid;	border-color: #666666;	background-color: #ffffff;">';
									$message_text	.= $content_get_orders['unixtime_validated'];
								$message_text	.= '</td>';
								
								$message_text	.= '<td style="border-width: 1px;padding: 8px;	border-style: solid;	border-color: #666666;	background-color: #ffffff;">';
									$message_text	.= utf8_decode($content_get_orders['societe']);
								$message_text	.= '</td>';
								
								$message_text	.= '<td style="border-width: 1px;padding: 8px;	border-style: solid;	border-color: #666666;	background-color: #ffffff;">';
									$message_text	.= $content_get_orders['total_ttc'];
								$message_text	.= '</td>';
								
								
								$message_text	.= '<td style="border-width: 1px;padding: 8px;	border-style: solid;	border-color: #666666;	background-color: #ffffff;">';
									//Constant from file includes/fr/classV3/CCommand.php								
									$message_text	.= $clone_order->getPaymentStatusText($content_get_orders['payment_status']);
								$message_text	.= '</td>';
								
								
								$message_text	.= '<td style="border-width: 1px;padding: 8px;	border-style: solid;	border-color: #666666;	background-color: #ffffff;">';
									$message_text	.= $content_get_orders['name'];
								$message_text	.= '</td>';
								
							$message_text	.= '</tr>';
							
						}//end while
					
						$message_text	.= '</table>';
					$message_text	.= '</p>';
					
					$message_text	.= '<p>';
						$message_text	.= 'Merci d\'int&eacute;grer d&egrave;s que possible les dates';
						$message_text	.= '<br /><br />';
					$message_text	.= '</p>';
				
				
				$message_text	.= '</div>';
				$message_bottom = "</body></html>";
				 
				$message_to_send = $message_header . $message_text . $message_bottom;
				//Send mail 
				
				$mail_send_etat	= php_mailer_external_send($header_from_name, $header_from_email, $header_send1_name, $header_send1_email, $header_send2_name, $header_send2_email, $header_reply1_email, $header_reply1_name, $header_copy1_email, $header_copy1_name, $header_copy2_email, $header_copy2_name, $subject, $message_to_send);
				//echo($message_to_send);
				
			}//end if($total_commandes>1){
			
			
			
			
	$execution_date_end		= date('Y-m-d H:i:s');	
	
	//send reporting mail
	$header_from_name	= 'Alerte ADV';
	$header_from_email	= 'cronreporting@techni-contact.com';
	
	$header_send1_name	= 'Zakaria Outarocht';
	$header_send1_email	= 'z.abidi@techni-contact.com';
	
	//$header_send2_name	= '';
	//$header_send2_email	= '';
	$header_send2_name	= 'Tristan HENRY-GREARD';
	$header_send2_email	= 't.henryg@techni-contact.com';
	
	$header_reply1_email= '';
	$header_reply1_name	= '';
	
	$header_copy1_email	= '';
	$header_copy1_name	= '';
	
	$header_copy2_email	= '';
	$header_copy2_name	= '';
	
	$subject = 'Alerte ADV date expedition manquante '.date('Y-m-d');
	
	$message_header = "<html><head>
						  <meta http-equiv=Content-Type content=text/html; charset=iso-8859-1>
						  </head>
						  <body bgcolor=#FFFFFF>";
  
	$message_text	= '<div style="font: normal 12px verdana,arial,sans-serif">';
	//$message_text	.= '<img src="http://www.techni-contact.com/media/emailings/mails-serveur-tc/logo-tc.jpg">';
	
	$message_text	.= '<p>';
		$message_text	.= 'Bonjour';
		$message_text	.= '<br /><br />';
		if(strcmp($mail_send_etat,'1')!='0'){
			$message_text	.= 'Erreur ex&eacute;cution Cron '.$header_from_name.' '.$mail_send_etat.'';
		}else{
			$message_text	.= 'Le Cron '.$header_from_name.' a &eacute;t&eacute; ex&eacute;cut&eacute; avec succ&egrave;s';
		}
		$message_text	.= '<br /><br />';
		
		$message_text	.= 'Date d&eacute;but : <strong>'.$execution_date_start.'</strong>';
		$message_text	.= '<br />';
		$message_text	.= 'Date fin : <strong>'.$execution_date_end.'</strong>';
		$message_text	.= '<br />';
		$message_text	.= 'Condition : <strong>'.$hours_condition.'H</strong>';
		$message_text	.= '<br />';
		$message_text	.= 'Nombre commandes : <strong>'.$total_commandes.'</strong>';
		$message_text	.= '<br /><br />';
	$message_text	.= '</p>';
	
	$message_text	.= '</div>';
	
	$message_bottom = "</body></html>";
	
	$message_to_send = $message_header . $message_text . $message_bottom;
	
	$reporting_mail_send_etat	= php_mailer_external_send($header_from_name, $header_from_email, $header_send1_name, $header_send1_email, $header_send2_name, $header_send2_email, $header_reply1_email, $header_reply1_name, $header_copy1_email, $header_copy1_name, $header_copy2_email, $header_copy2_name, $subject, $message_to_send);

	//echo('<br /><br />**<br />'.$message_to_send.' ');
?>