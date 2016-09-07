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

	$execution_date_start	= date('Y-m-d H:i:s');
	
	//Yesterday Date
	//Making the conversion for the date to be able to make an operation on the DB
	//$yesterday_date			= date('Y-m-d', time() - (60*60*24));
	//To put a static date
	$yesterday_date			= "2015-02-05";
	$yesterday_start		= strtotime($yesterday_date.' 00:00:00');
	$yesterday_end			= strtotime($yesterday_date.' 23:59:59');
	
	$res_check_existing_record = $db->query("SELECT
												s_l.id
											FROM
												stats_leads s_l
											WHERE
												s_l.date_operation='".$yesterday_date."'", __FILE__, __LINE__);

	if(mysql_num_rows($res_check_existing_record)==0){	
		//Informations origin
			//Campagne Adwords
			//Campagne d'appels
			//Chat
			//Internaute
			//Mail
			//Probance
			//Téléphone entrant
			//Téléphone sortant
		
		//********************************** Statistiques globales ******************************************** 	
			//First Query	FROM_UNIXTIME(c.create_time) convert_date
			//Total Leads
			$res_total_leads = $db->query("SELECT
												COUNT(c.id) c_id
											FROM
												contacts c
													LEFT JOIN advertisers AS adv on adv.id=c.idAdvertiser
											WHERE
												c.create_time 
													BETWEEN '".$yesterday_start."' AND '".$yesterday_end."'", __FILE__, __LINE__);	
												
			$content_total_leads = $db->fetchAssoc($res_total_leads);		
			
			
			//Second Query
			//Primary Leads
			$res_total_leads_primary = $db->query("SELECT
														COUNT(c.id) c_id
													FROM
														contacts c
															LEFT JOIN advertisers AS adv on adv.id=c.idAdvertiser
													WHERE
														c.create_time 
															BETWEEN '".$yesterday_start."' AND '".$yesterday_end."'
														AND
															c.parent=0", __FILE__, __LINE__);	
												
			$content_total_leads_primary = $db->fetchAssoc($res_total_leads_primary);
			
			
			//Third Query
			//Secondary Leads
			$res_total_leads_secondary = $db->query("SELECT
														COUNT(c.id) c_id
													FROM
														contacts c
															LEFT JOIN advertisers AS adv on adv.id=c.idAdvertiser
													WHERE
														c.create_time 
															BETWEEN '".$yesterday_start."' AND '".$yesterday_end."'
														AND
															c.parent!=0", __FILE__, __LINE__);	
												
			$content_total_leads_secondary = $db->fetchAssoc($res_total_leads_secondary);
			
			
			
		//********************************** Type de partenaire **********************************************
			//First Query
			//Primary Leads annonceur
			$res_total_leads_primary_annonceur = $db->query("SELECT
																COUNT(c.id) c_id
															FROM
																contacts c
																	LEFT JOIN advertisers AS adv on adv.id=c.idAdvertiser
															WHERE
																c.create_time 
																	BETWEEN '".$yesterday_start."' AND '".$yesterday_end."'
																AND
																	c.parent=0
																AND
																	adv.category!=1", __FILE__, __LINE__);	
											
			$content_total_leads_primary_annonceur = $db->fetchAssoc($res_total_leads_primary_annonceur);
			
			
			//Second Query
			//Primary Leads fournisseur
			$res_total_leads_primary_fournisseur = $db->query("SELECT
																	COUNT(c.id) c_id
																FROM
																	contacts c
																		LEFT JOIN advertisers AS adv on adv.id=c.idAdvertiser
																WHERE
																	c.create_time 
																		BETWEEN '".$yesterday_start."' AND '".$yesterday_end."'
																	AND
																		c.parent=0
																	AND
																		adv.category=1", __FILE__, __LINE__);	
											
			$content_total_leads_primary_fournisseur = $db->fetchAssoc($res_total_leads_primary_fournisseur);
			
			
		//********************************** Leads internet **********************************************
			//First Query
			//Primary Leads Internet
			$res_total_leads_primary_internet = $db->query("SELECT
																COUNT(c.id) c_id
															FROM
																contacts c
																	LEFT JOIN advertisers AS adv on adv.id=c.idAdvertiser
															WHERE
																c.create_time 
																	BETWEEN '".$yesterday_start."' AND '".$yesterday_end."'
																AND
																	c.parent=0
																AND
																	c.origin='Internaute'", __FILE__, __LINE__);	
											
			$content_total_leads_primary_internet = $db->fetchAssoc($res_total_leads_primary_internet);
			
			
			//Second Query
			//Primary Leads campagnes mkt
			$res_total_leads_primary_probance = $db->query("SELECT
																COUNT(c.id) c_id
															FROM
																contacts c
																	LEFT JOIN advertisers AS adv on adv.id=c.idAdvertiser
															WHERE
																c.create_time 
																	BETWEEN '".$yesterday_start."' AND '".$yesterday_end."'
																AND
																	c.parent=0
																AND
																	c.origin='Probance'", __FILE__, __LINE__);	
											
			$content_total_leads_primary_probance = $db->fetchAssoc($res_total_leads_primary_probance);
			
			
			//Third Query
			//Primary Lead annonceur Internet + Mkt
			$res_total_leads_primary_aninmkt = $db->query("SELECT
																COUNT(c.id) c_id
															FROM
																contacts c
																	LEFT JOIN advertisers AS adv on adv.id=c.idAdvertiser
															WHERE
																c.create_time 
																	BETWEEN '".$yesterday_start."' AND '".$yesterday_end."'
																AND
																	c.parent=0
																AND
																	adv.category!=1
																AND
																	(
																		c.origin='Internaute'
																	OR
																		c.origin='Probance'
																	)", __FILE__, __LINE__);	
											
			$content_total_leads_primary_aninmkt = $db->fetchAssoc($res_total_leads_primary_aninmkt);
			
			
			//Fourth Query
			//Primary Lead fournisseur Internet + Mkt
			$res_total_leads_primary_foinmkt = $db->query("SELECT
																COUNT(c.id) c_id
															FROM
																contacts c
																	LEFT JOIN advertisers AS adv on adv.id=c.idAdvertiser
															WHERE
																c.create_time 
																	BETWEEN '".$yesterday_start."' AND '".$yesterday_end."'
																AND
																	c.parent=0
																AND
																	adv.category=1
																AND
																	(
																		c.origin='Internaute'
																	OR
																		c.origin='Probance'
																	)", __FILE__, __LINE__);	
											
			$content_total_leads_primary_foinmkt = $db->fetchAssoc($res_total_leads_primary_foinmkt);
			
			//lead_p_internet_mkt
			$total_leads_primary_internet_probance = $content_total_leads_primary_internet['c_id']+$content_total_leads_primary_probance['c_id'];
			
			
		//********************************** Production SMPO **********************************************

		
		
		//********************************** Call entrant *************************************************
			//First Query
			//Primary Leads call entrant
			$res_total_leads_primary_call_entrant = $db->query("SELECT
																	COUNT(c.id) c_id
																FROM
																	contacts c
																		LEFT JOIN advertisers AS adv on adv.id=c.idAdvertiser
																WHERE
																	c.create_time 
																		BETWEEN '".$yesterday_start."' AND '".$yesterday_end."'
																	AND
																		c.parent=0
																	AND
																		c.origin='Telephone entrant'", __FILE__, __LINE__);	
											
			$content_total_leads_primary_call_entrant = $db->fetchAssoc($res_total_leads_primary_call_entrant);
		
			
			//Second Query
			//Primary Leads entrant internautes uniques
			$res_total_leads_primary_call_entrant_inter_unique = $db->query("SELECT
																				COUNT(DISTINCT(c.email)) c_email
																			FROM
																				contacts c
																					LEFT JOIN advertisers AS adv on adv.id=c.idAdvertiser
																			WHERE
																				c.create_time 
																					BETWEEN '".$yesterday_start."' AND '".$yesterday_end."'
																				AND
																					c.parent=0
																				AND
																					c.origin='Telephone entrant'", __FILE__, __LINE__);	
											
			$content_total_leads_primary_call_entrant_inter_unique = $db->fetchAssoc($res_total_leads_primary_call_entrant_inter_unique);
			
			
			//Nb leads par appelé converti
			if(!empty($content_total_leads_primary_call_entrant_inter_unique['c_email'])){
				$number_leads_converted_by_call_ingoing	= $content_total_leads_primary_call_entrant['c_id']/$content_total_leads_primary_call_entrant_inter_unique['c_email'];
				$number_leads_converted_by_call_ingoing = round($number_leads_converted_by_call_ingoing, 2);
			}else{
				$number_leads_converted_by_call_ingoing	= 0;
			}
			
			
			
		//********************************** Call sortant ***********************************************
			//First Query
			//Primary Leads call sortant
			$res_total_leads_primary_call_sortant = $db->query("SELECT
																	COUNT(c.id) c_id
																FROM
																	contacts c
																		LEFT JOIN advertisers AS adv on adv.id=c.idAdvertiser
																WHERE
																	c.create_time 
																		BETWEEN '".$yesterday_start."' AND '".$yesterday_end."'
																	AND
																		c.parent=0
																	AND
																		c.origin='Telephone sortant'", __FILE__, __LINE__);	
											
			$content_total_leads_primary_call_sortant = $db->fetchAssoc($res_total_leads_primary_call_sortant);
			
			
			//Second Query
			//Primary Leads call sortant internautes uniques
			$res_total_leads_primary_call_sortant_inter_unique = $db->query("SELECT
																	COUNT(DISTINCT(c.email)) c_email
																FROM
																	contacts c
																		LEFT JOIN advertisers AS adv on adv.id=c.idAdvertiser
																WHERE
																	c.create_time 
																		BETWEEN '".$yesterday_start."' AND '".$yesterday_end."'
																	AND
																		c.parent=0
																	AND
																		c.origin='Telephone sortant'", __FILE__, __LINE__);	
											
			$content_total_leads_primary_call_sortant_inter_unique = $db->fetchAssoc($res_total_leads_primary_call_sortant_inter_unique);

			
			//Nb leads par appelé converti
			if(!empty($content_total_leads_primary_call_sortant_inter_unique['c_email'])){
				$number_leads_converted_by_call_outgoing	= $content_total_leads_primary_call_sortant['c_id']/$content_total_leads_primary_call_sortant_inter_unique['c_email'];
				//Get only two decimal numbers
				$number_leads_converted_by_call_outgoing	= round($number_leads_converted_by_call_outgoing,2);
			}else{
				$number_leads_converted_by_call_outgoing	= 0;
			}
			
			
			
		//********************************** Autres ***********************************************
			//First Query
			//Primary Leads sur email
			$res_total_leads_primary_mail = $db->query("SELECT
																COUNT(c.id) c_id
															FROM
																contacts c
																	LEFT JOIN advertisers AS adv on adv.id=c.idAdvertiser
															WHERE
																c.create_time 
																	BETWEEN '".$yesterday_start."' AND '".$yesterday_end."'
																AND
																	c.parent=0
																AND
																	c.origin='Mail'", __FILE__, __LINE__);	
											
			$content_total_leads_primary_mail = $db->fetchAssoc($res_total_leads_primary_mail);
			
			
			//Landing page Adwords

			
			//Primary Leads adwords LP
			$res_total_leads_primary_c_adword = $db->query("SELECT
																COUNT(c.id) c_id
															FROM
																contacts c
																	LEFT JOIN advertisers AS adv on adv.id=c.idAdvertiser
															WHERE
																c.create_time 
																	BETWEEN '".$yesterday_start."' AND '".$yesterday_end."'
															AND
																c.parent=0
															AND
																c.origin='Campagne Adwords'", __FILE__, __LINE__);	
											

			$content_total_leads_primary_c_adword = $db->fetchAssoc($res_total_leads_primary_c_adword);
			
			
			//Primary Leads calls campaigns
			$res_total_leads_primary_calls_campaigns = $db->query("SELECT
																COUNT(c.id) c_id
															FROM
																contacts c
																	LEFT JOIN advertisers AS adv on adv.id=c.idAdvertiser
															WHERE
																c.create_time 
																	BETWEEN '".$yesterday_start."' AND '".$yesterday_end."'
																AND
																	c.parent=0
																AND
																	c.origin='Campagne d\'appels'", __FILE__, __LINE__);	
											
			$content_total_leads_primary_calls_campaigns = $db->fetchAssoc($res_total_leads_primary_calls_campaigns);
			
			
			//Primary Leads chat
			$res_total_leads_primary_chat = $db->query("SELECT
																COUNT(c.id) c_id
															FROM
																contacts c
																	LEFT JOIN advertisers AS adv on adv.id=c.idAdvertiser
															WHERE
																c.create_time 
																	BETWEEN '".$yesterday_start."' AND '".$yesterday_end."'
																AND
																	c.parent=0
																AND
																	c.origin='Chat'", __FILE__, __LINE__);	
											
			$content_total_leads_primary_chat = $db->fetchAssoc($res_total_leads_primary_chat);
			
			
			//Primary Leads Click to call
			$res_total_leads_primary_click_to_call = $db->query("SELECT
																COUNT(c.id) c_id
															FROM
																contacts c
																	LEFT JOIN advertisers AS adv on adv.id=c.idAdvertiser
															WHERE
																c.create_time 
																	BETWEEN '".$yesterday_start."' AND '".$yesterday_end."'
																AND
																	c.parent=0
																AND
																	c.origin='Click to call'", __FILE__, __LINE__);	
											
			$content_total_leads_primary_click_to_call = $db->fetchAssoc($res_total_leads_primary_click_to_call);
			
			
			//TOTAL SMPO
			$total_smpo	= $content_total_leads_primary_call_entrant['c_id']+$content_total_leads_primary_call_sortant['c_id']+$content_total_leads_primary_mail['c_id']+$content_total_leads_primary_c_adword['c_id']+$content_total_leads_primary_calls_campaigns['c_id']+$content_total_leads_primary_chat['c_id']+$content_total_leads_primary_click_to_call['c_id'];
			
		
		
		//******************** Start saving the informations	
		$res_saving_infos = $db->query("INSERT INTO stats_leads(id, date_operation, 
																leads_total, leads_primary, 
																leads_secondary, leads_p_advertisers,
																leads_p_suppliers, leads_p_int_mkt, 
																leads_p_internet, leads_p_mkt_camp, 
																leads_p_int_mkt_advertisers, leads_p_int_mkt_suppliers,
																leads_smpo_total, leads_smpo_incom_call, 
																leads_incom_clients_distinct, leads_converted_incom_clients, 
																leads_smpo_outcom_call, leads_outcom_clients_distinct,
																leads_converted_outcom_clients, leads_p_email, 
																leads_p_adwords, leads_p_camp_calls,leads_p_click_to_call,leads_p_chat)
														values(NULL, '".$yesterday_date."', 
																".$content_total_leads['c_id'].", ".$content_total_leads_primary['c_id'].", 
																".$content_total_leads_secondary['c_id'].", ".$content_total_leads_primary_annonceur['c_id'].",
																".$content_total_leads_primary_fournisseur['c_id'].", ".$total_leads_primary_internet_probance.",
																".$content_total_leads_primary_internet['c_id'].", ".$content_total_leads_primary_probance['c_id'].",
																".$content_total_leads_primary_aninmkt['c_id'].", ".$content_total_leads_primary_foinmkt['c_id'].",
																".$total_smpo.", ".$content_total_leads_primary_call_entrant['c_id'].",
																".$content_total_leads_primary_call_entrant_inter_unique['c_email'].", '".$number_leads_converted_by_call_ingoing."',
																".$content_total_leads_primary_call_sortant['c_id'].", ".$content_total_leads_primary_call_sortant_inter_unique['c_email'].",
																'".$number_leads_converted_by_call_outgoing."', ".$content_total_leads_primary_mail['c_id'].",
																".$content_total_leads_primary_c_adword['c_id'].", ".$content_total_leads_primary_calls_campaigns['c_id'].",
																".$content_total_leads_primary_click_to_call['c_id'].",
																".$content_total_leads_primary_chat['c_id'].")
																", __FILE__, __LINE__);
														
		//******************** Start building mails 
		
			$header_from_name	= 'Reporting Techni-Contact';
			$header_from_email	= 'e.adil@techni-contact.com';
			
			$header_send1_name	= '';
			//$header_send1_email	= 'z.abidi@techni-contact.com';
			$header_send1_email	= 'reporting@techni-contact.com';
			
			$header_send2_name	= '';
			//$header_send2_email	= 't.henryg@techni-contact.com';
			$header_send2_email	= '';
			
			$header_reply1_name	= '';
			$header_reply1_email= '';
			
			
			
			$header_copy1_name	= '';
			$header_copy1_email	= 'e.adil@techni-contact.com';
			//$header_copy1_email	= 'derroteteufel@gmail.com';
			
			$header_copy2_name	= '';
			$header_copy2_email	= '';
			
			$subject = 'Reporting leads - '.$yesterday_date;
			
			$message_header = "<html><head>
									  <meta http-equiv=Content-Type content=text/html; charset=iso-8859-1>
									  </head>
									  <body bgcolor=#FFFFFF>";
									  
			$message_text	= '<div style="font: normal 12px verdana,arial,sans-serif">';
			$message_text	.= '<img src="http://www.techni-contact.com/media/emailings/mails-serveur-tc/logo-tc.jpg">';
				
				$message_text	.= '<p>';
					$message_text	.= 'Bonjour';
					$message_text	.= '<br /><br />';
					$message_text	.= 'Voici les donn&eacute;es cl&eacute; des contacts re&ccedil;us le '.$yesterday_date;
					$message_text	.= '<br />';
				$message_text	.= '</p>';
				
				
				$message_text	.= '<p>';
					$message_text	.= '<h2>';
					$message_text	.= 'Statistiques globales :';
					$message_text	.= '</h2>';
				$message_text	.= '</p>';
				
				$message_text	.= '<p>';		
					$message_text	.= '&nbsp;Leads total : <strong>'.$content_total_leads['c_id'].'</strong>';
					$message_text	.= '<br />';
					$message_text	.= '&nbsp;Dont :';
					$message_text	.= '<br />';
					$message_text	.= '&nbsp;Leads primaires : <strong>'.$content_total_leads_primary['c_id'].'</strong>';
					$message_text	.= '<br />';
					$message_text	.= '&nbsp;Leads secondaires : <strong>'.$content_total_leads_secondary['c_id'].'</strong>';
				$message_text	.= '</p>';
				
				
				$message_text	.= '<p>';
					$message_text	.= '<h3>';
					$message_text	.= 'Type de partenaire :';
					$message_text	.= '</h3>';
					
					$message_text	.= '&nbsp;Leads P annonceur : <strong>'.$content_total_leads_primary_annonceur['c_id'].'</strong>';
					$message_text	.= '<br />';
					$message_text	.= '&nbsp;Leads P fournisseur : <strong>'.$content_total_leads_primary_fournisseur['c_id'].'</strong>';
				$message_text	.= '</p>';
				
				
				$message_text	.= '<p>';
					$message_text	.= '<h3>';
					$message_text	.= 'Leads internet :';
					$message_text	.= '</h3>';
					
					$message_text	.= '&nbsp;Leads P internet + mkt : <strong>'.$total_leads_primary_internet_probance.'</strong>';
					$message_text	.= '<br />';
					$message_text	.= '&nbsp;Leads P internet : <strong>'.$content_total_leads_primary_internet['c_id'].'</strong>';
					$message_text	.= '<br />';
					$message_text	.= '&nbsp;Leads P campagnes mkt : <strong>'.$content_total_leads_primary_probance['c_id'].'</strong>';
					
					$message_text	.= '<br />';
					$message_text	.= '&nbsp;Leads P annonceur Internet + Mkt : <strong>'.$content_total_leads_primary_aninmkt['c_id'].'</strong>';
					
					$message_text	.= '<br />';
					$message_text	.= '&nbsp;Leads P fournisseur Internet + Mkt : <strong>'.$content_total_leads_primary_foinmkt['c_id'].'</strong>';				
				$message_text	.= '</p>';
				
				
				$message_text	.= '<p>';
					$message_text	.= '<h2>';
					$message_text	.= 'Production SMPO :';
					$message_text	.= '</h2>';
					
					$message_text	.= '&nbsp;TOTAL SMPO : <strong>'.$total_smpo.'</strong>';
				$message_text	.= '</p>';
				
				
				
				
				$message_text	.= '<p>';
					$message_text	.= '<h3>';
					$message_text	.= 'Call entrant :';
					$message_text	.= '</h3>';
					
					$message_text	.= '&nbsp;Leads P call entrant : <strong>'.$content_total_leads_primary_call_entrant['c_id'].'</strong>';
					$message_text	.= '<br />';
					$message_text	.= '&nbsp;Clients entrants convertis : <strong>'.$content_total_leads_primary_call_entrant_inter_unique['c_email'].'</strong>';
					$message_text	.= '<br />';
					$message_text	.= '&nbsp;Nb leads par appelant converti : <strong>'.$number_leads_converted_by_call_ingoing.'</strong>';
				$message_text	.= '</p>';
				
				
				$message_text	.= '<p>';
					$message_text	.= '<h3>';
					$message_text	.= 'Call sortant :';
					$message_text	.= '</h3>';
					
					$message_text	.= '&nbsp;Leads P call sortant : <strong>'.$content_total_leads_primary_call_sortant['c_id'].'</strong>';
					$message_text	.= '<br />';
					$message_text	.= '&nbsp;Clients sortants convertis : <strong>'.$content_total_leads_primary_call_sortant_inter_unique['c_email'].'</strong>';
					$message_text	.= '<br />';
					$message_text	.= '&nbsp;Nb leads par appel&eacute; converti : <strong>'.$number_leads_converted_by_call_outgoing.'</strong>';
				$message_text	.= '</p>';
				
				
				$message_text	.= '<p>';
					$message_text	.= '<h3>';
					$message_text	.= 'Autres :';
					$message_text	.= '</h3>';
					
					$message_text	.= '&nbsp;Leads P sur email : <strong>'.$content_total_leads_primary_mail['c_id'].'</strong>';
					$message_text	.= '<br />';
					$message_text	.= '&nbsp;Leads P adwords LP : <strong>'.$content_total_leads_primary_c_adword['c_id'].'</strong>';
					$message_text	.= '<br />';
					$message_text	.= '&nbsp;Leads P campagnes d\'appels : <strong>'.$content_total_leads_primary_calls_campaigns['c_id'].'</strong>';
				$message_text	.= '</p>';

				
			$message_text	.= '</div>';
			$message_bottom = "</body></html>";
					
			$message_to_send = $message_header . $message_text . $message_bottom;


			//Send mail 
			$mail_send_etat	= php_mailer_external_send($header_from_name, $header_from_email, $header_send1_name, $header_send1_email, $header_send2_name, $header_send2_email, $header_reply1_email, $header_reply1_name, $header_copy1_email, $header_copy1_name, $header_copy2_email, $header_copy2_name, $subject, $message_to_send);
			//echo($message_to_send);
			
			
			$execution_date_end		= date('Y-m-d H:i:s');	
		
		//send reporting mail
		$header_from_name	= 'Reporting Techni-Contact';
		$header_from_email	= 'e.adil@techni-contact.com';
		
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
		
		$subject = 'Reporting leads - '.$yesterday_date;
		
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
			$message_text	.= 'Condition Jour : <strong>'.$yesterday_date.' 00:00:00</strong> &agrave; <strong>'.$yesterday_date.' 23:59:59</strong>';
			$message_text	.= '<br />';

		$message_text	.= '</p>';
		
		$message_text	.= '<br /><br />';
		
		$message_text	.= '</div>';
		
		$message_bottom = "</body></html>";
		
		$message_to_send = $message_header . $message_text . $message_bottom;
		$reporting_mail_send_etat	= php_mailer_external_send($header_from_name, $header_from_email, $header_send1_name, $header_send1_email, $header_send2_name, $header_send2_email, $header_reply1_email, $header_reply1_name, $header_copy1_email, $header_copy1_name, $header_copy2_email, $header_copy2_name, $subject, $message_to_send);

		//echo('<br /><br />**<br />'.$message_to_send.' ** '.$reporting_mail_send_etat);	
		
	}//end if(mysql_num_rows($res_check_existing_record)==0){
	
?>