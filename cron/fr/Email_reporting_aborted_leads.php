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
	
	$execution_date_start	= date('d-m-Y H:i:s');
	
	
	$includePath = array();
	//$includePath[] = '.';
	//$includePath[] = './../application';
	$includePath[] = BASE_PATH.'lib/zend/zend_1_12_9/library';
	$includePath[] = get_include_path();
	$includePath = implode(PATH_SEPARATOR,$includePath);
	set_include_path($includePath);

	//Get includes path
	//print_r(get_include_path());
	
		
	// load Zend Gdata libraries
	require_once 'Zend/Loader.php';
	Zend_Loader::loadClass('Zend_Gdata_Spreadsheets');
	Zend_Loader::loadClass('Zend_Gdata_ClientLogin');

	
	// set credentials for ClientLogin authentication
	$user = "evmail@techni-contact.com";
	$pass = "4698gk-mpo";
	
	//Counting the number of aborted leads
	$count_aborted_leads	= 0;

	try {
	
		// connect to API
		$service = Zend_Gdata_Spreadsheets::AUTH_SERVICE_NAME;
		$client = Zend_Gdata_ClientLogin::getHttpClient($user, $pass, $service);
		$service = new Zend_Gdata_Spreadsheets($client);
	
		//Read my Spreadsheet HEad infos ! 
		$feed = $service->getSpreadsheetFeed();

		foreach ($feed as $entry) {
			//echo 'Title: ' . $entry->title . ' - ';
			//echo 'Id: ' . $entry->id . '<br />';
		}
		
	
		// set target spreadsheet and worksheet
		//$spreadsheetsKey 	= '18NQR4ZPgg7CjiEAZ9XHNFdtVYqwdqD88b3HJosoK708';
		//For the first sheet "od6" or "1"
		//For the second "2" for the third "3"..
		//$worksheetId 		= '2';
		
		//Relances leads abandonnés
		$spreadsheetsKey 	= '0AqDUy03ZhRfcdEVaWlNtLXJtMk1ZMFBpc1Iwdl9Md2c';
		$worksheetId 		= '3';
		
		
		//Connecting to the Database and get the last rows 
		//To not have the double elements !
		$query_last_time_abort_leads		= 'SELECT 
													timestamp_execution
												FROM
													stats_contacts_aborted
												ORDER BY id DESC LIMIT 1';
		
		$res_last_time_abort_leads = $db->query($query_last_time_abort_leads, __FILE__, __LINE__);
												
		if(mysql_num_rows($res_last_time_abort_leads)>0){
			$content_last_time_abort_leads = $db->fetchAssoc($res_last_time_abort_leads);
			
			//Incrementing one hour !
			$query_condition_start_time		= strtotime($content_last_time_abort_leads['timestamp_execution']);
		}else{
			//Yesterday
			//$yesterday_date					= date('Y-m-d', time() - (60*60*24));
			
			//Get today becuase the cron will be executed every Hour !
			$yesterday_date					= date('Y-m-d');
			$query_condition_start_time		= strtotime($yesterday_date.' 00:45:00');
		}

		//Building end (start -15 Minutes)
		$query_condition_end_time			= strtotime('+1 hour', $query_condition_start_time);
		//$query_condition_end_time			= strtotime('-15 minutes', $query_condition_end_time);		

		
		//Building Query 
		//$yesterday_date			= date('Y-m-d', time() - (60*60*24));
		//To put a static date //$yesterday_date			= "2014-07-21";
		//$yesterday_start		= strtotime($yesterday_date.' 00:00:00');
		//$yesterday_end			= strtotime($yesterday_date.' 23:59:59');


		$query_abort_leads		= 'SELECT
									  from_unixtime(ca.timestamp) AS date_creation,
									  ca.nom,
									  ca.prenom,
									  ca.tel,
									  pfr.name AS nom_produit,
									  ca.precisions,
									 a.nom1 as "nom_partenaire",
									 ca.idProduct,
									 ca.id, 
									 ca.idFamily,
									 ca.idAdvertiser,               
									  ca.fonction, 
									  ca.societe,
									  ca.salaries,
									  ca.secteur,
									  ca.adresse,
									  ca.cp, 
									  ca.ville,
									  ca.pays, 
									  ca.email, 
									  ca.recall_mail_sent,  
									 a.category as "cat_partenaire",
									 c.email as "email_lead",
									 e.email as "email_devis",
									 c.id as "id_lead"

									FROM contacts_aborted ca

									LEFT JOIN products_fr pfr ON ca.idProduct = pfr.id
									LEFT JOIN advertisers a ON  ca.idAdvertiser = a.id
									LEFT JOIN contacts c ON ca.email = c.email
									LEFT JOIN contacts e ON ca.email = e.email

									WHERE 
										(ca.timestamp 
											BETWEEN 
											'.$query_condition_start_time.' 
											AND 
											'.$query_condition_end_time.'
										) 
									AND 
										(ca.tel > 0) 
									AND 
										(ca.pays = \'France\') 
									AND 
										(ca.fonction != \'Je suis un particulier\') 
									AND 
										(c.email IS NULL) 
									AND 
										(e.email IS NULL)

									GROUP BY ca.email';
		
		$res_abort_leads = $db->query($query_abort_leads, __FILE__, __LINE__);
												
		if(mysql_num_rows($res_abort_leads)>0){
		
			while($content_abort_leads = $db->fetchAssoc($res_abort_leads)){
			
				// create row content
				$row = array(
							"date-insertion" => date('d-m-Y H:i:s'),
							"attribue-a" => '',
							"statut" => '',
							"date-creation" => $content_abort_leads['date_creation'],		
							"nom" => $content_abort_leads['nom'],
							"prenom" => $content_abort_leads['prenom'],
							"tel" => $content_abort_leads['tel'],
							"nom-produit" => $content_abort_leads['nom_produit'],
							"precisions" => $content_abort_leads['precisions'],
							"nom-partenaire" => $content_abort_leads['nom_partenaire'],
							
							"idproduct" => $content_abort_leads['idProduct'],
							"id" => $content_abort_leads['id'],
							"idfamily" => $content_abort_leads['idFamily'],
							"idadvertiser" => $content_abort_leads['idAdvertiser'],
							"fonction" => $content_abort_leads['fonction'],
							"societe" => $content_abort_leads['societe'],
							"salaries" => $content_abort_leads['salaries'],
							"secteur" => $content_abort_leads['secteur'],
							"adresse" => $content_abort_leads['adresse'],
							"cp" => $content_abort_leads['cp'],

							"ville" => $content_abort_leads['ville'],
							"pays" => $content_abort_leads['pays'],
							"email" => $content_abort_leads['email'],
							"recall-mail-sent" => $content_abort_leads['recall_mail_sent'],
							"cat-partenaire" => $content_abort_leads['cat_partenaire'],
							"email-lead" => $content_abort_leads['email_lead'],
							"email-devis" => $content_abort_leads['email_devis'],
							"id-lead" => $content_abort_leads['id_lead'],
							);
							
							
				// insert new row
				$entryResult = $service->insertRow($row, $spreadsheetsKey, $worksheetId);
				//echo 'The ID of the new row entry is: ' . $entryResult->id;
			
				$count_aborted_leads++;
			}//end while
		}//end rows found test
	
	
	} catch (Exception $e) {
		die('ERROR: ' . $e->getMessage());
	}
	
	
	//Reporting in the Database 
	/******************************************************************************************************/
	/******************************************************************************************************/
	
	//Add one hour to insert it in the database
	$query_insert_start_time		= strtotime("+1 hour", $query_condition_start_time);
	
	
	$query_insert_report		= 'INSERT INTO stats_contacts_aborted(id, timestamp_execution,
																	total_abort, ok,
																	ko, repondeur,
																	no_num_wrong_num, autre)
														VALUES(NULL, \''.date('Y-m-d H:i:s',$query_insert_start_time).'\',
														'.$count_aborted_leads.', 0,
														0, 0,
														0, 0)';
		
	$res_insert_report = $db->query($query_insert_report, __FILE__, __LINE__);
		
	//Reporting send mails !
	/******************************************************************************************************/
	/******************************************************************************************************/
	//echo('<b>'.$count_aborted_leads.'</b>Rows founded ! ');
		
	$execution_date_end		= date('d-m-Y H:i:s');
	
	//******************** Start building mails 
		
		$header_from_name	= 'Leads Abandonnés';
		$header_from_email	= 'aborded-transactions@techni-contact.com';
		
		$header_send1_name	= '';
		//$header_send1_email	= 'z.abidi@techni-contact.com';
		$header_send1_email	= 'aborded-transactions@techni-contact.com';
		

		
		$header_send2_name	= '';
		//$header_send2_email	= 't.henryg@techni-contact.com';
		$header_send2_email	= '';
		
		$header_reply1_name	= '';
		$header_reply1_email= 'aborded-transactions@techni-contact.com';
		
		
		
		$header_copy1_name	= '';
		$header_copy1_email	= '';
		//$header_copy1_email	= 'derroteteufel@gmail.com';
		
		$header_copy2_name	= '';
		$header_copy2_email	= '';
		
		if($count_aborted_leads>1){
			$subject = $count_aborted_leads.' leads abandonné(s) au '.date('d-m-Y H:i:s', $query_condition_start_time);
		}else{
			$subject = $count_aborted_leads.' lead abandonné au '.date('d-m-Y H:i:s', $query_condition_start_time);
		}
		
		$message_header = "<html><head>
								  <meta http-equiv=Content-Type content=text/html; charset=iso-8859-1>
								  </head>
								  <body bgcolor=#FFFFFF>";
								  
		$message_text	= '<div style="font: normal 12px verdana,arial,sans-serif">';
		$message_text	.= '<img src="http://www.techni-contact.com/media/emailings/mails-serveur-tc/logo-tc.jpg">';
			
			$message_text	.= '<p>';
				$message_text	.= 'Bonjour';
				$message_text	.= '<br /><br />';
				$message_text	.= 'Voici les données des leads abandonnés au <b>'.date('d-m-Y H:i:s', $query_condition_start_time).'</b>';
				$message_text	.= '<br />';
			$message_text	.= '</p>';
			
			
			$message_text	.= '<p>';
				$message_text	.= '<h2>';
				$message_text	.= $count_aborted_leads.' leads abandonnés';
				$message_text	.= '</h2>';
			$message_text	.= '</p>';
			
			
			$message_text	.= '<p>';
				$message_text	.= '<a href="https://docs.google.com/a/techni-contact.com/spreadsheets/d/'.$spreadsheetsKey.'/edit#gid='.$worksheetId.'">Accéder au fichier complet</a>';
			$message_text	.= '</p>';
			
			
			$message_text	.= '<p>';
				$message_text	.= 'Le service notification';
			$message_text	.= '</p>';
			
			
			$message_text	.= '<p>';
				$message_text	.= 'Techni-Contact est édité par';
				$message_text	.= '<br />';
				$message_text	.= 'Md2i';
				$message_text	.= '<br />';
				$message_text	.= '253 rue Gallieni';
				$message_text	.= '<br />';
				$message_text	.= 'F-92774 BOULOGNE BILLANCOURT cedex';
				$message_text	.= '<br />';
				$message_text	.= 'Tel : 01 55 60 29 29 (appel local)';
				$message_text	.= '<br />';
				$message_text	.= 'Fax: 01 83 62 36 12';
				$message_text	.= '<br />';
				$message_text	.= '<a href="http://www.techni-contact.com/">http://www.techni-contact.com/</a>';
				
				$message_text	.= '<br />';
				$message_text	.= '<br />';
				
				$message_text	.= 'SAS au capital de 160.000 €';
				$message_text	.= '<br />';
				$message_text	.= 'SIRET : 392 772 497 000 39';
				$message_text	.= '<br />';
				$message_text	.= 'TVA n° FR12 392 772 497';
				$message_text	.= '<br />';
				$message_text	.= 'R.C. NANTERRE B 392 772 497';
			$message_text	.= '</p>';
			
			
			/*$message_text	.= '<br /><br />';
			$message_text	.= '<p>';
				$message_text	.= 'Date début d\'éxecution : '.$execution_date_start;
				$message_text	.= '<br />';
				$message_text	.= 'Date fin d\'éxecution : &nbsp;&nbsp;&nbsp;'.$execution_date_end;
			$message_text	.= '</p>';
			*/

		$message_text	.= '</div>';
		$message_bottom = "</body></html>";
				
		$message_to_send = $message_header . $message_text . $message_bottom;

		//Send mail 
		$mail_send_etat	= php_mailer_external_send($header_from_name, $header_from_email, $header_send1_name, $header_send1_email, $header_send2_name, $header_send2_email, $header_reply1_email, $header_reply1_name, $header_copy1_email, $header_copy1_name, $header_copy2_email, $header_copy2_name, $subject, $message_to_send);
		//echo($message_to_send);	
	
?>