<?php
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
	require_once(dirname(__FILE__).'/../../includes/fr/classV3/phpmailer_2014/PHPMailerAutoload.php');
	require_once(dirname(__FILE__).'/../../includes/fr/classV3/phpmailer_2014/Email_functions_external_mail_send.php');

	$db = DBHandle::get_instance();
	
	require_once(ICLASS.'CCommand.php');
	$clone_order	= new Command($db);
	
	
	$hours_condition		= '48';
	//FROM_UNIXTIME( o.forecasted_ship ) BETWEEN NOW() AND  DATE_ADD( NOW( ) , INTERVAL ".$hours_condition." HOUR )
	$res_get_orders = "SELECT
								ad.nom1 ,so.order_id , FROM_UNIXTIME( o.forecasted_ship ) as date_expedition , o.total_ht,bou.name,o.payment_status
							FROM
								`order` AS o
							LEFT JOIN supplier_order so ON o.id   = so.order_id
							LEFT JOIN advertisers 	 ad ON so.sup_id = ad.id
							LEFT JOIN bo_users  	 bou ON so.sender_id  = bou.id
							WHERE
								 
								(FROM_UNIXTIME(o.forecasted_ship, '%Y/%m/%d' ) = DATE_ADD(CURDATE(), INTERVAL 2 DAY) )
							AND 
								o.shipped=0
							AND 
								o.sav_opened=0
							AND
								o.partly_cancelled=0
							AND
								o.cancelled=0
							order by o.forecasted_ship ASC";	
	
	$query_get_order = mysql_query($res_get_orders);
	$rows_get_order  = mysql_num_rows($query_get_order);
	
	$query_get_order2 = mysql_query($res_get_orders);
	$data_get_order2  = mysql_fetch_assoc($query_get_order2);	
		
				$header_from_name_new	= "Alerte ADV";
				$header_from_email	= 'alerte-adv-supp-orders@techni-contact.com';
				
				$header_send1_name	= '';
				//$header_send1_email	= 't.henryg@techni-contact.com';
				//$header_send1_email	= 'outarocht.zakaria@gmail.com';
									   			
				$header_send1_email	= 'alerte-adv-supp-orders@techni-contact.com';
				
				$header_send2_name	= '';
				$header_send2_email	= 'alerte-adv-supp-orders@techni-contact.com';
				$header_send2_email	= '';
				
				$header_reply1_name	= '';
				$header_reply1_email= '';
				
				$header_copy1_email	= '';
				$header_copy1_name	= '';
				$header_copy1_email	= 'z.abidi@techni-contact.com';
				
				$header_copy2_name	= '';
				$header_copy2_email	= '';
				
				$date_sub = substr($data_get_order2['date_expedition'],0,-9);
				$newDate = date("d/m/Y", strtotime($date_sub));
				$subject = 'Liste des commandes en instance d\'expedition le '.$newDate.' ';
				
				$message_header = "<html><head>
								  <meta http-equiv=Content-Type content=text/html; charset=iso-8859-1>
								  </head>
								  <body bgcolor=#FFFFFF>";
				
				$message_text	.= '<p>';
						$message_text	.= 'Bonjour';
						$message_text	.= '<br /><br />';
						$message_text	.= 'Les ordres fournisseurs suivants sont li&eacute;es &agrave; des commandes exp&eacute;di&eacute;es dans 48 H.';
						$message_text	.= '<br /><br />';
					$message_text	.= '</p>';
				
				$message_text	.= '<p>';
						$message_text	.= '<table style="	border-style: solid;font-family: verdana,arial,sans-serif;font-size:13px;color:#333333;border-width: 1px;border-color: #666666;	border-collapse: collapse;">';
							$message_text	.= '<tr style="background-color: #b8bab4;">';
								$message_text	.= '<th>';
									$message_text	.= 'N&deg; ordre';
								$message_text	.= '</th>';
								
								$message_text	.= '<th>';
									$message_text	.= 'Fournisseur ';
								$message_text	.= '</th>';
								
								$message_text	.= '<th>';
									$message_text	.= 'Date d\'exp&eacute;dition pr&eacute;visionnelle';
								$message_text	.= '</th>';
								
								$message_text	.= '<th>';
									$message_text	.= 'Montant commande';
								$message_text	.= '</th>';
								
								$message_text	.= '<th>';
									$message_text	.= 'Nom de l\'op&eacute;rateur';
								$message_text	.= '</th>';
								
								$message_text	.= '<th>';
									$message_text	.= 'Statut de Paiement';
								$message_text	.= '</th>';
								
								
							$message_text	.= '</tr>';
				if($rows_get_order > 0) {			
				while($content_get_orders = mysql_fetch_assoc($query_get_order)){
					
					$date_sub = substr($content_get_orders['date_expedition'],0,-9);
					$newDate = date("d/m/Y", strtotime($date_sub));
					$message_text	.= '<tr>';
								$message_text	.= '<td style="border-width: 1px;padding: 8px;	border-style: solid;	border-color: #666666;	background-color: #ffffff;">';
									$message_text	.= '<a href="'.SECURE_URL.'manager/orders/order-detail.php?id='.$content_get_orders['order_id'].'" target="_blank">';
										$message_text	.= $content_get_orders['order_id'];
									$message_text	.= '</a>';
								$message_text	.= '</td>';
								
								$message_text	.= '<td style="border-width: 1px;padding: 8px;	border-style: solid;	border-color: #666666;	background-color: #ffffff;">';
									$message_text	.= $content_get_orders['nom1'];
								$message_text	.= '</td>';
								
								$message_text	.= '<td style="border-width: 1px;padding: 8px;	border-style: solid;	border-color: #666666;	background-color: #ffffff;">';
									$message_text	.= $newDate;
								$message_text	.= '</td>';
								
								$message_text	.= '<td style="border-width: 1px;padding: 8px;	border-style: solid;	border-color: #666666;	background-color: #ffffff;">';
									$message_text	.= $content_get_orders['total_ht'];
								$message_text	.= '</td>';
								
								$message_text	.= '<td style="border-width: 1px;padding: 8px;	border-style: solid;	border-color: #666666;	background-color: #ffffff;">';
									$message_text	.= $content_get_orders['name'];
								$message_text	.= '</td>';
								
								$message_text	.= '<td style="border-width: 1px;padding: 8px;	border-style: solid;	border-color: #666666;	background-color: #ffffff;">';
									$message_text	.= $clone_order->getPaymentStatusText($content_get_orders['payment_status']); 
								$message_text	.= '</td>';
								
							$message_text	.= '</tr>';
					
				} // end while
				
				$message_text	.= '</table>';
					$message_text	.= '</p>';
					
				$message_text	.= '</div>';
				$message_bottom = "</body></html>";
				 
				$message_to_send = $message_header . $message_text . $message_bottom;
				
				//Send mail 
				$mail_send_etat	= php_mailer_external_send($header_from_name_new, $header_from_email, $header_send1_name, $header_send1_email, $header_send2_name, $header_send2_email, $header_reply1_email, $header_reply1_name, $header_copy1_email, $header_copy1_name, $header_copy2_email, $header_copy2_name, $subject, $message_to_send);
				
				//echo $message_to_send;
				
	}else {
		$message_text	.= '<tr>';
			$message_text	.= '<td style="border-width: 0px;padding: 8px;	border-style: solid;	border-color: #666666;	background-color: #ffffff;"> </td>';
			
			$message_text	.= '<td style="border-width: 0px;padding: 8px;	border-style: solid;	border-color: #666666;	background-color: #ffffff;">  </td>';
			
			$message_text	.= '<td style="  text-align: center;border-width: 0px;padding: 8px;	border-style: solid;	border-color: #666666;	background-color: #ffffff;"> <strong> 0 </strong> resultat trouvé  </td>';
			
			$message_text	.= '<td style="border-width: 0px;padding: 8px;	border-style: solid;	border-color: #666666;	background-color: #ffffff;">  </td>';
			
			
		$message_text	.= '</tr>';
		$message_text	.= '</table>';
		$message_text	.= '</p>';
					
				$message_text	.= '</div>';
				$message_bottom = "</body></html>";
			$message_to_send = $message_header . $message_text . $message_bottom;
		
		$mail_send_etat	= php_mailer_external_send($header_from_name_new, $header_from_email, $header_send1_name, $header_send1_email, $header_send2_name, $header_send2_email, $header_reply1_email, $header_reply1_name, $header_copy1_email, $header_copy1_name, $header_copy2_email, $header_copy2_name, $subject, $message_to_send);
	}	
?>
	