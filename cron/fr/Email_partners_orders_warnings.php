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
	

	error_reporting(E_ALL & ~E_NOTICE);

	$execution_date_start		= date('Y-m-d H:i:s');
	
	//Looking for the Orders that was not seen from 48H
	//Actual time -48Hours
	$condition_start			= time()-(((60*60)*24)*2);
	//We have to set a interval (48H from now to the day before -5minutes	"24Hours-5minutes")
	$condition_end				= $condition_start-(((60*60)*24)-(60*5));
	
	//Condition valable only for the Third Type
	//Fixed date => 2014-08-28
	//$type3_hours_condition_end	= '1409176800';
	
//to debug orders create Between now and 10minutes ago !	
//$condition_start		= time();
//$condition_end			= $condition_start-600;
	
	//We will make 3 types of reporting
	//Type1: 	Orders sent between ("now-24Hours" AND "now-72Hours-5Minutes")
	//			And not seen by the advertiser
	
	//Type2: 	Orders seen between ("now-24Hours" AND "now-72Hours-5Minutes")
	//			And no acknowledgment was received
	
	//Type3: 	Orders sent between ("now-24Hours" AND "now-72Hours-5Minutes")
	//			And no acknowledgment 'AR' was receive
	
	$count_type1	= 0;
	$count_type2	= 0;
	$count_type3	= 0;
	
	//Count number mail send OK & KO
	$count_type1_send_ok	= 0;
	$count_type1_send_ko	= 0;
	$count_type2_send_ok	= 0;
	$count_type2_send_ko	= 0;
	$count_type3_send_ok	= 0;
	$count_type3_send_ko	= 0;
	
	
	$catch_error	= '';
	
	$except_ids		= '63180, 39464, 52639, 14972, 15508, 17088, 36754, 64586, 56972, 39599, 35473, 23830, 60052, 35382, 60079, 52999, 35083, 14138, 38597, 60603, 9428, 62038, 43807, 38977, 22197, 19248, 26633, 28910, 12251';
	
	//Define debug mode or prod => to use a real mails
	//$cron_debug_mode	= true;
	$cron_debug_mode	= false;
	
	try{
	
		//***********************************************************************
		//**************************** TYPE 1 ***********************************
		//***********************************************************************
		try{
			$res_get_type1 = $db->query("SELECT
											sup_o.id, sup_o.order_id AS idCommande,
											sup_o.sup_id, FROM_UNIXTIME(sup_o.mail_time) AS converted_time,
											sup_o.mail_time,
											
											adv.id AS idAdvertiser,	adv.nom1,
											adv.email 
											
										FROM
											supplier_order sup_o
											LEFT JOIN advertisers AS adv ON adv.id=sup_o.sup_id
										WHERE
											sup_o.mail_time
												BETWEEN
													".$condition_end."
												AND
													".$condition_start."
										AND
											sup_o.seen_time=0
										AND
											sup_o.sup_id NOT IN (".$except_ids.")", __FILE__, __LINE__);
			$count_type1	= mysql_num_rows($res_get_type1);
			if($count_type1!=0){
				while($content_get_type1 = $db->fetchAssoc($res_get_type1)){
				
					$header_from_name	= 'Service Achat Techni-Contact';
					$header_from_email	= 'achat@techni-contact.com';
						
					if($cron_debug_mode==true){						
						$header_send1_name	= '';
						$header_send1_email	= 'z.abidi@techni-contact.com';
						
						$header_send2_name	= '';
						$header_send2_email	= 't.henryg@techni-contact.com';
						//$header_send2_email	= '';
						
						$header_reply1_name	= 'Service Achat Techni-Contact';
						$header_reply1_email= 'achat@techni-contact.com';
						
						$header_copy1_email	= '';
						$header_copy1_name	= '';
						
						$header_copy2_name	= '';
						$header_copy2_email	= '';
					}else{
						$header_send1_name	= '';
						$header_send1_email	= $content_get_type1['email'];
						
						$header_send2_name	= '';
						//$header_send2_email	= 't.henryg@techni-contact.com';
						$header_send2_email	= '';
						
						$header_reply1_name	= 'Service Achat Techni-Contact';
						$header_reply1_email= 'achat@techni-contact.com';
						
						$header_copy1_email	= '';
						$header_copy1_name	= '';
						
						$header_copy2_name	= '';
						$header_copy2_email	= '';
					}
					
					$res_get_infos = $db->query("SELECT
													extra_u.id, extra_u.login,
													extra_u.c,	extra_u.webpass
												FROM
													extranetusers extra_u
												WHERE
													extra_u.id=".$content_get_type1['idAdvertiser']."", __FILE__, __LINE__);
					$content_get_infos = $db->fetchAssoc($res_get_infos);
					
					
					$subject = 'Relance concernant la commande '.$content_get_type1['idAdvertiser'].'-'.$content_get_type1['idCommande'].'';
					
					$external_var_send_time	= date('d/m/Y à H:i:s', $content_get_type1['mail_time']);
					$external_var_url_order	= EXTRANET_URL.'commande.html?idCommande='.$content_get_type1['idAdvertiser'].'-'.$content_get_type1['idCommande'].'&uid='.$content_get_infos['webpass'];
					
					$message_header = '';
						require(dirname(__FILE__)."/../../content/fr/emails_content/Email_supplier_orders_warnings_advertiser_header.php");
			
					$message_text 	= '';
						require(dirname(__FILE__)."/../../content/fr/emails_content/Email_supplier_orders_warnings_advertiser_content.php");
						
					$message_bottom = '';
						require(dirname(__FILE__)."/../../content/fr/emails_content/Email_supplier_orders_warnings_advertiser_footer.php");
						
					$message_to_send = $message_header . $message_text . $message_bottom;
					
					
					//Send mail 
					$mail_send_etat	= php_mailer_external_send($header_from_name, $header_from_email, $header_send1_name, $header_send1_email, $header_send2_name, $header_send2_email, $header_reply1_email, $header_reply1_name, $header_copy1_email, $header_copy1_name, $header_copy2_email, $header_copy2_name, $subject, $message_to_send);
					if(strcmp($mail_send_etat,'1')=='0'){
						$count_type1_send_ok++;
					}else{
						$count_type1_send_ko++;
					}
				
				}//end while

			}//end if($count_type1!=0){
			
		}catch(Exception $e){
			$catch_error	.= '<br /> - Erreur Try1 '.$e;
		}//end try catch Type1
		
		
		//***********************************************************************
		//**************************** TYPE 2 ***********************************
		//***********************************************************************
		
		//Demande Tristan 28/09/2014 Désactivation Envoi Type2
		/*
		try{
			$res_get_type2 = $db->query("SELECT
												sup_o.id, sup_o.order_id AS idCommande,
												sup_o.sup_id, FROM_UNIXTIME(sup_o.seen_time),
												sup_o.mail_time,
												
												adv.id AS idAdvertiser,	adv.nom1,
												adv.email
												
											FROM
												supplier_order sup_o
												LEFT JOIN advertisers AS adv ON adv.id=sup_o.sup_id
											WHERE
												sup_o.seen_time
													BETWEEN
														".$condition_end."
													AND
														".$condition_start."
											AND
												sup_o.arc_time=0
											AND
												sup_o.sup_id NOT IN (".$except_ids.")", __FILE__, __LINE__);
			$count_type2	= mysql_num_rows($res_get_type2);
			if($count_type2!=0){
				while($content_get_type2 = $db->fetchAssoc($res_get_type2)){
				
					$header_from_name	= 'Service Achat Techni-Contact';
					$header_from_email	= 'achat@techni-contact.com';
					
					if($cron_debug_mode==true){
						$header_send1_name	= '';
						$header_send1_email	= 'z.abidi@techni-contact.com';
						
						$header_send2_name	= '';
						$header_send2_email	= 't.henryg@techni-contact.com';
						//$header_send2_email	= '';
						
						$header_reply1_name	= 'Service Achat Techni-Contact';
						$header_reply1_email= 'achat@techni-contact.com';
						
						$header_copy1_email	= '';
						$header_copy1_name	= '';
						
						$header_copy2_name	= '';
						$header_copy2_email	= '';
					}else{
						$header_send1_name	= '';
						$header_send1_email	= $content_get_type2['email'];
						
						$header_send2_name	= '';
						//$header_send2_email	= 't.henryg@techni-contact.com';
						$header_send2_email	= '';
						
						$header_reply1_name	= 'Service Achat Techni-Contact';
						$header_reply1_email= 'achat@techni-contact.com';
						
						$header_copy1_email	= '';
						$header_copy1_name	= '';
						
						$header_copy2_name	= '';
						$header_copy2_email	= '';
					}
					
					if(!empty($content_get_type2['idAdvertiser'])){
						$res_get_infos2 = $db->query("SELECT
													extra_u.id, extra_u.login,
													extra_u.c,	extra_u.webpass
												FROM
													extranetusers extra_u
												WHERE
													extra_u.id=".$content_get_type2['idAdvertiser']."", __FILE__, __LINE__);
						$content_get_infos2 = $db->fetchAssoc($res_get_infos2);
					}
					
					$subject = 'Relance concernant la commande '.$content_get_type2['idAdvertiser'].'-'.$content_get_type2['idCommande'].'';
					
					$external_var_send_time	= date('d/m/Y à H:i:s', $content_get_type2['mail_time']);
					$external_var_url_order	= EXTRANET_URL.'commande.html?idCommande='.$content_get_type2['idAdvertiser'].'-'.$content_get_type2['idCommande'].'&uid='.$content_get_infos2['webpass'];
					
					$message_header = '';
						require(dirname(__FILE__)."/../../content/fr/emails_content/Email_supplier_orders_warnings_advertiser_header.php");
			
					$message_text 	= '';
						require(dirname(__FILE__)."/../../content/fr/emails_content/Email_supplier_orders_warnings_advertiser_content.php");
						
					$message_bottom = '';
						require(dirname(__FILE__)."/../../content/fr/emails_content/Email_supplier_orders_warnings_advertiser_footer.php");
						

					$message_to_send = $message_header . $message_text . $message_bottom;
					
					//Send mail 
					$mail_send_etat	= php_mailer_external_send($header_from_name, $header_from_email, $header_send1_name, $header_send1_email, $header_send2_name, $header_send2_email, $header_reply1_email, $header_reply1_name, $header_copy1_email, $header_copy1_name, $header_copy2_email, $header_copy2_name, $subject, $message_to_send);
					if(strcmp($mail_send_etat,'1')=='0'){
						$count_type2_send_ok++;
					}else{
						$count_type2_send_ko++;
					}
				
				}//end while
			
			}//end if($count_type2!=0){
			
		}catch(Exception $e){
			$catch_error	.= '<br /> - Erreur Try2 '.$e;
		}//end try catch Type2
		*/
		
		//***********************************************************************
		//**************************** TYPE 3 ***********************************
		//***********************************************************************
		try{
			$res_get_type3 = $db->query("SELECT
												sup_o.id AS sup_o_id, sup_o.order_id,
												sup_o.sup_id, FROM_UNIXTIME(sup_o.mail_time) unix_mail_time,
												
												adv.id,	adv.nom1
												
											FROM
												supplier_order sup_o
												LEFT JOIN advertisers AS adv ON adv.id=sup_o.sup_id
											WHERE
												sup_o.mail_time
													BETWEEN
														".$condition_end."
													AND
														".$condition_start."
											AND
												sup_o.arc_time=0
											AND
												sup_o.sup_id NOT IN (".$except_ids.")
											", __FILE__, __LINE__);
			$count_type3	= mysql_num_rows($res_get_type3);
			if($count_type3!=0){
			
					$header_from_name	= 'Alerte ADV';
					$header_from_email	= 'commandes@techni-contact.com';
					
					if($cron_debug_mode==true){
						$header_send1_name	= '';
						$header_send1_email	= 'z.abidi@techni-contact.com';
						
						$header_send2_name	= '';
						$header_send2_email	= 't.henryg@techni-contact.com';
						//$header_send2_email	= '';
						
						$header_reply1_name	= '';
						$header_reply1_email= '';
						
						
						$header_copy1_email	= '';
						$header_copy1_name	= '';
						
						$header_copy2_name	= '';
						$header_copy2_email	= '';
					}else{
						$header_send1_name	= '';
						$header_send1_email	= 'alerte-adv-supp-orders@techni-contact.com';
						
						$header_send2_name	= '';
						$header_send2_email	= '';
						
						$header_reply1_name	= '';
						$header_reply1_email= 'commandes@techni-contact.com';
						
						
						$header_copy1_email	= '';
						$header_copy1_name	= '';
						
						$header_copy2_name	= '';
						$header_copy2_email	= '';
					}
					
					
					$message_text	= '<p>';
						$message_text	.= 'Bonjour';
						$message_text	.= '<br /><br />';
						$message_text	.= 'Les ordres des commandes suivantes ont &eacute;t&eacute; envoy&eacute;s aux fournisseurs, mais n\'ont toujours pas d\'ARC li&eacute;s.';
						$message_text	.= '<br /><br />';
						$message_text	.= 'Tableau contenant les donn&eacute;es suivantes :';
						$message_text	.= '<br /><br />';
					$message_text	.= '</p>';
					
					$message_text	.= '<p>';
						$message_text	.= '<table>';
							$message_text	.= '<tr>';
								$message_text	.= '<th>';
									$message_text	.= 'N&deg; ordre';
								$message_text	.= '</th>';
								$message_text	.= '<th>';
									$message_text	.= 'Fournisseur';
								$message_text	.= '</th>';
								$message_text	.= '<th>';
									$message_text	.= 'Nom client';
								$message_text	.= '</th>';
								$message_text	.= '<th>';
									$message_text	.= 'Date d\'envoi de l\'ordre';
								$message_text	.= '</th>';
							$message_text	.= '</tr>';	
							
							while($content_get_type3 = $db->fetchAssoc($res_get_type3)){
							
								$message_text	.= '<tr>';
									$message_text	.= '<td>';
										$message_text	.= '<a href="'.SECURE_URL.'manager/supplier-orders/supplier-order-detail.php?id='.$content_get_type3['sup_o_id'].'">';
										$message_text	.= ''.$content_get_type3['sup_o_id'].'';
										$message_text	.= '</a>';
									$message_text	.= '</td>';
									
									$message_text	.= '<td>';
										$message_text	.= $content_get_type3['nom1'];
									$message_text	.= '</td>';
									
									
									$res_get_infos3 = $db->query("SELECT
																	o.id, o.nom AS nom_client
																FROM
																	`order` o
																WHERE
																	o.id=".$content_get_type3['order_id']."
																	", __FILE__, __LINE__);
									$content_get_infos3 = $db->fetchAssoc($res_get_infos3);
					
									
									$message_text	.= '<td>';
										$message_text	.= $content_get_infos3['nom_client'];
									$message_text	.= '</td>';

									$message_text	.= '<td>';
										$message_text	.= $content_get_type3['unix_mail_time'];
									$message_text	.= '</td>';								
								$message_text	.= '</tr>';

							}//end while
							
					$message_text	.= '<table>';
				$message_text	.= '</p>';			
				
				$message_text	.= '<p>';
					$message_text	.= 'Merci d\'ins&eacute;rer les ARC &agrave; ces ordres fournisseur.';
					$message_text	.= '<br /><br />';
				$message_text	.= '</p>';
					
				
				
				$subject = 'ARC manquants sur ordres fournisseurs - '.date('Y-m-d');
	
				$message_header = '';
					require(dirname(__FILE__)."/../../content/fr/emails_content/Email_supplier_orders_warnings_advertiser_header.php");
			
						
				$message_bottom = '';
					require(dirname(__FILE__)."/../../content/fr/emails_content/Email_supplier_orders_warnings_advertiser_footer.php");
					
				
				$message_to_send = $message_header . $message_text . $message_bottom;
				
				//Send mail 
				$mail_send_etat	= php_mailer_external_send($header_from_name, $header_from_email, $header_send1_name, $header_send1_email, $header_send2_name, $header_send2_email, $header_reply1_email, $header_reply1_name, $header_copy1_email, $header_copy1_name, $header_copy2_email, $header_copy2_name, $subject, $message_to_send);
				if(strcmp($mail_send_etat,'1')=='0'){
					$count_type3_send_ok++;
				}else{
					$count_type3_send_ko++;
				}
			
			}//end if($count_type3!=0){
		
		}catch(Exception $e){
			$catch_error	.= '<br /> - Erreur Try3 '.$e;
		}//end try catch Type3
		
		
	}catch(Exception $e){
		$catch_error	.= '<br /> - Erreur Global Try '.$e;
	}//end try catch global
	
	//***********************************************************************
	//************************ START REPORTING ******************************
	//***********************************************************************
	
	//Finalisation Process & send mail for reporting execution !
	$execution_date_end		= date('Y-m-d H:i:s');	
		
	//send reporting mail
	$header_from_name	= 'Alerte ADV';
	$header_from_email	= 'commandes@techni-contact.com';
	
	$header_send1_name	= 'Zakaria ABIDI';
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
	
	$subject = 'Alerte ADV - '.date('Y-m-d');
	
	$message_header = "<html><head>
						  <meta http-equiv=Content-Type content=text/html; charset=iso-8859-1>
						  </head>
						  <body bgcolor=#FFFFFF>";
  
	$message_text	= '<div style="font: normal 12px verdana,arial,sans-serif">';
	//$message_text	.= '<img src="http://www.techni-contact.com/media/emailings/mails-serveur-tc/logo-tc.jpg">';
	
	$message_text	.= '<p>';
		$message_text	.= 'Bonjour';
		$message_text	.= '<br /><br />';
		if(!empty($catch_error)){
			$message_text	.= 'Erreur ex&eacute;cution Cron '.$header_from_name.' "'.$catch_error.'"';
		}else{
			$message_text	.= 'Le Cron '.$header_from_name.' a &eacute;t&eacute; ex&eacute;cut&eacute; avec succ&egrave;s';
		}
		$message_text	.= '<br /><br />';
		
		$message_text	.= '<p>';
			$message_text	.= '<table>';
				$message_text	.= '<tr>';
					$message_text	.= '<th>';
						$message_text	.= 'Type &nbsp;&nbsp;';
					$message_text	.= '</th>';
					$message_text	.= '<th>';
						$message_text	.= 'Total &nbsp;&nbsp;';
					$message_text	.= '</th>';
					$message_text	.= '<th>';
						$message_text	.= 'Mail envoy&eacute; &nbsp;&nbsp;';
					$message_text	.= '</th>';
					$message_text	.= '<th>';
						$message_text	.= 'Mail non envoy&eacute; &nbsp;&nbsp;';
					$message_text	.= '</th>';
				$message_text	.= '</tr>';	
				
				$message_text	.= '<tr>';
					$message_text	.= '<td>';
						$message_text	.= 'Type 1';
					$message_text	.= '</td>';
					$message_text	.= '<td>';
						$message_text	.= $count_type1;
					$message_text	.= '</td>';
					$message_text	.= '<td>';
						$message_text	.= $count_type1_send_ok;
					$message_text	.= '</td>';
					$message_text	.= '<td>';
						$message_text	.= $count_type1_send_ko;
					$message_text	.= '</td>';
				$message_text	.= '</tr>';	
				
				$message_text	.= '<tr>';
					$message_text	.= '<td>';
						$message_text	.= 'Type 2';
					$message_text	.= '</td>';
					$message_text	.= '<td>';
						$message_text	.= $count_type2;
					$message_text	.= '</td>';
					$message_text	.= '<td>';
						$message_text	.= $count_type2_send_ok;
					$message_text	.= '</td>';
					$message_text	.= '<td>';
						$message_text	.= $count_type2_send_ko;
					$message_text	.= '</td>';
				$message_text	.= '</tr>';
				
				$message_text	.= '<tr>';
					$message_text	.= '<td>';
						$message_text	.= 'Type 3';
					$message_text	.= '</td>';
					$message_text	.= '<td>';
						$message_text	.= $count_type3;
					$message_text	.= '</td>';
					$message_text	.= '<td>';
						if($count_type3_send_ok>0){
							$message_text	.= '<font color="green">Mail</font>';
							//$message_text	.= 'Le mail a &eacute;t&eacute; envoy&eacute; avec succ&egrave;s';
						}else{
							$message_text	.= '<font color="red">Mail</font>';
							//$message_text	.= 'Mail non envoy&eacute;';
						}
					$message_text	.= '</td>';
					$message_text	.= '<td>';
						$message_text	.= '&nbsp;';
					$message_text	.= '</td>';
				$message_text	.= '</tr>';
		
			$message_text	.= '</table>';
			
		$message_text	.= '</p>';
		
		$message_text	.= '<br /><br />';
		$message_text	.= 'Date d&eacute;but : <strong>'.$execution_date_start.'</strong>';
		$message_text	.= '<br />';
		$message_text	.= 'Date fin : <strong>'.$execution_date_end.'</strong>';
		$message_text	.= '<br />';
		$message_text	.= 'Condition Jour : <strong>'.date('Y-m-d H:i:s',$condition_start).'</strong> &agrave; <strong>'.date('Y-m-d H:i:s',$condition_end).'</strong>';
		$message_text	.= '<br />';

	$message_text	.= '</p>';
	
	$message_text	.= '<br /><br />';
	$message_text	.= '</div>';
	
	$message_bottom = "</body></html>";
	
	$message_to_send = $message_header . $message_text . $message_bottom;
	$reporting_mail_send_etat	= php_mailer_external_send($header_from_name, $header_from_email, $header_send1_name, $header_send1_email, $header_send2_name, $header_send2_email, $header_reply1_email, $header_reply1_name, $header_copy1_email, $header_copy1_name, $header_copy2_email, $header_copy2_name, $subject, $message_to_send);

?>