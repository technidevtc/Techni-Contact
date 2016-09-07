<?php
	require_once('extranet_v3_functions.php'); 
		
	if(!empty($_SESSION['extranet_user_id']) && (!in_array($_SESSION['extranet_user_category'], $array_contacts_id_advertisers))){

		$f_ps							= mysql_escape_string($_GET['f_ps']);
		$f_pp							= mysql_escape_string($_GET['f_pp']);
		$fetat							= mysql_escape_string($_GET['fetat']);
		$f_date_debut					= mysql_escape_string($_GET['f_date_debut']);
		$f_date_fin						= mysql_escape_string($_GET['f_date_fin']);
		
		
		// Ajouter Le 01/07/2015 a 15:19 MA Par Zak Out
		$f_date_jours						    = mysql_escape_string($_GET['f_date_jours']);
		$f_mois						    = mysql_escape_string($_GET['f_mois']);
		$f_mois_debut					= mysql_escape_string($_GET['f_mois_debut']);
		$f_mois_fin						= mysql_escape_string($_GET['f_mois_fin']);
		
		
		// Ajouter Le 01/07/2015 a 15:19 MA Par Zak Out
		
		$f_search						= mysql_escape_string($_GET['f_search']);
		$f_contacts_type				= mysql_escape_string($_GET['f_contacts_type']);

		
		$f_date_debut_ready				= '';
		$f_date_fin_ready				= '';
		$f_contacts_type_ready			= '';
		$query_suite_param				= '';
		$query_suite_param_limit_ready	= '';
		
		
	
		if(!empty($f_mois)){
			
			$f_mois_debut_ready	= strtotime($f_mois.'-01 00:00:00'); 
			$f_mois_fin_ready	= strtotime($f_mois.'-31 23:59:59');
		
			$query_suite_param	= " AND
									  c.create_time BETWEEN ".$f_mois_debut_ready." AND ".$f_mois_fin_ready." ";
			$check_date_show_table			= 1;
		}
		
		
		
		if( (!empty($f_mois_debut)) && (!empty($f_mois_fin)) ){
			
			$f_mois_debut_ready	= strtotime($f_mois_debut.'-01 00:00:00');
			$f_mois_fin_ready	= strtotime($f_mois_fin.'-31 23:59:59');
			
			$query_suite_param	= " AND
									  c.create_time BETWEEN ".$f_mois_debut_ready." AND ".$f_mois_fin_ready." ";
			$check_date_show_table			= 1;
		}
		
		if(!empty($f_date_debut)){
			$f_date_debut_array	= explode('/',$f_date_debut);
			$f_date_debut_ready	= strtotime($f_date_debut_array[2].'-'.$f_date_debut_array[1].'-'.$f_date_debut_array[0].' 00:00:00');
			
			$query_suite_param	= " AND 
										c.create_time>".$f_date_debut_ready;
										
			//To show the table of resume
			$check_date_show_table			= 1;
		}
		
		if(!empty($f_date_fin)){
			$f_date_fin_array	= explode('/',$f_date_fin);
			$f_date_fin_ready	= strtotime($f_date_fin_array[2].'-'.$f_date_fin_array[1].'-'.$f_date_fin_array[0].' 23:59:59');
			
			$query_suite_param	= " AND 
										c.create_time<".$f_date_fin_ready;
			//To show the table of resume
			$check_date_show_table			= 1;			
		}
		
		if(!empty($f_date_jours)){
			
			$f_date_jours_array	= explode('/',$f_date_jours);
			$f_date_jours_debut	= strtotime($f_date_jours_array[2].'-'.$f_date_jours_array[1].'-'.$f_date_jours_array[0].' 00:00:00');
			$f_date_jours_fin	= strtotime($f_date_jours_array[2].'-'.$f_date_jours_array[1].'-'.$f_date_jours_array[0].' 23:59:59');
			
			$query_suite_param	= " AND c.create_time BETWEEN ".$f_date_jours_debut." AND ".$f_date_jours_fin." ";
		}
		
		if(!empty($f_date_debut) && !empty($f_date_fin) ){
			
			$f_date_debut_array	= explode('/',$f_date_debut);
			$f_date_jours_debut	= strtotime($f_date_debut_array[2].'-'.$f_date_debut_array[1].'-'.$f_date_debut_array[0].' 00:00:00');
			
			$f_date_fin_array	= explode('/',$f_date_fin);
			$f_date_jours_fin	= strtotime($f_date_fin_array[2].'-'.$f_date_fin_array[1].'-'.$f_date_fin_array[0].' 23:59:59');
			
			$query_suite_param	= " AND 
										c.create_time BETWEEN ".$f_date_jours_debut." AND ".$f_date_jours_fin;
		}
		
		
		
		if(!empty($f_search)){
			$f_search_params					= '';
			
			if(is_numeric($f_search)){
				//if it's a numeric
				$f_search_params					= "c.id=".$f_search."";
			}else{
				//if it's a mail
				if(eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $f_search)) {
					$f_search_params					= "c.email like '".$f_search."'";
				}else{
					//We know that is a string
					$f_search_params					= "c.societe like '".$f_search."' OR c.nom like '".$f_search."'";
				}//end else
			
			}//end else
			
			$query_suite_param	.= " AND ( ".$f_search_params.") ";
		}
		
		
		switch($f_contacts_type){
			case 'not-read':
				$query_suite_param	.= ' AND
												c.cread=0';
				if(!empty($fetat) || $fetat=='0' ){
					if($fetat == '25-9'){
						$query_suite_param_etat	.= ' AND  c.invoice_status IN (\'25\',\'9\') ';
					}else{
						$query_suite_param_etat	.= ' AND  c.invoice_status ='.$fetat.'  ';
					}
				}
			break;
			
			case 'processing':
				$query_suite_param	.= ' AND
												(c.cread!=0 AND c.archived=\'0\')';
				if(!empty($fetat) || $fetat=='0' ){
					if($fetat == '25-9'){
						$query_suite_param_etat	.= ' AND  c.invoice_status IN (\'25\',\'9\') ';
					}else{
						$query_suite_param_etat	.= ' AND  c.invoice_status ='.$fetat.'  ';
					}
				}
												
			break;
			
			case 'archived':
				$query_suite_param	.= ' AND
												c.archived=\'1\'';
				if(!empty($fetat) || $fetat=='0' ){
					if($fetat == '25-9'){
						$query_suite_param_etat	.= ' AND  c.invoice_status IN (\'25\',\'9\') ';
					}else{
						$query_suite_param_etat	.= ' AND  c.invoice_status ='.$fetat.'  ';
					}
				}
			break;
			
			case 'deleted':
				$query_suite_param	.= ' AND
												c.archived=\'2\'';
				if(!empty($fetat) || $fetat=='0' ){
					if($fetat == '25-9'){
						$query_suite_param_etat	.= ' AND  c.invoice_status IN (\'25\',\'9\') ';
					}else{
						$query_suite_param_etat	.= ' AND  c.invoice_status ='.$fetat.'  ';
					}
				}
			break;
			
			
			case 'all':
				$query_suite_param	.= ' AND
												c.archived IN (\'0\',\'1\') ';
				if(!empty($fetat) || $fetat=='0' ){
					if($fetat == '25-9'){
						$query_suite_param_etat	.= ' AND  c.invoice_status IN (\'25\',\'9\') ';
					}else{
						$query_suite_param_etat	.= ' AND  c.invoice_status ='.$fetat.'  ';
					}
				}
			break;
			
		}//end switch
		
		
		if(isset($f_ps)){
			//$query_suite_param_limit1 	= $f_ps;
			$page		= $f_ps;
			if(strcmp($f_ps,1)==0){
				$query_suite_param_limit1 	= 0;
			}else{
				$f_ps--;
				$query_suite_param_limit1 	= $f_pp*$f_ps;
			}
		}else{
			$f_ps						= 0;
			$query_suite_param_limit1 	= " 0";
		}
		
		
		
		$query_suite_param_limit_ready	= " LIMIT ".$query_suite_param_limit1.", ".$query_suite_param_limit2;
		
		$res_get_contacts_req	= "SELECT 
												count(c.id) c
												
											FROM
												contacts c 
													LEFT JOIN advertisers a ON a.id=c.idAdvertiser
													LEFT JOIN products_fr prod_fr ON prod_fr.id=c.idProduct
													LEFT JOIN families_fr f_fr	ON f_fr.id=c.idFamily
											WHERE
												c.idAdvertiser=".$_SESSION['extranet_user_id']."
											".$query_suite_param."
											".$query_suite_param_etat."
											";
											
		$res_get_contacts_cout = $db->query($res_get_contacts_req, __FILE__, __LINE__);
		

		$content_get_contacts_cout	= $db->fetchAssoc($res_get_contacts_cout);
		$total_count_results		= $content_get_contacts_cout['c'];
		
		
		if($total_count_results!=0){
			$result				 = $db->query("SELECT
												c.timestamp, pfr.name, f.name as familyName, c.id, c.type, c.societe, c.adresse,
												c.cadresse, c.cp, c.ville, c.pays, c.tel,
												c.fax, c.prenom, c.nom, c.fonction, c.email,
												c.siret, c.naf, c.salaries, c.secteur, c.url,
												c.precisions, c.invoice_status, c.credited_on, c.customFields, c.reject_timestamp,
												
												c.archived, c.cread
												
											FROM contacts c
											
												LEFT JOIN advertisers a ON c.idAdvertiser = a.id
												LEFT JOIN products_fr pfr ON c.idProduct = pfr.id
												LEFT JOIN families_fr	f ON c.idFamily = f.id
												
											WHERE
												c.idAdvertiser=".$_SESSION['extranet_user_id']."
											".$query_suite_param." 
											".$query_suite_param_etat."
											ORDER BY c.create_time DESC ", __FILE__, __LINE__);
											

			//require(ICLASS."ExtranetUser.php");
			require(ADMIN   ."statut.php");
			

			//Folder Classes
			//require(ICLASS."ExtranetUser.php");
											
			require_once("Spreadsheet/Excel/Writer.php");
			$workbook = new Spreadsheet_Excel_Writer();
			$workbook->send("Extrait Demandes de Contact du ".date('Y-m-d h-i-s').".xls");
			
			// Creating a worksheet
			$worksheet = $workbook->addWorksheet('Liste des demandes de Contact');
	
			//The headers to put in the export 
			//And it's matched with the rows of the results (first one to first one, nd to nd, ..)
			//So we declare them all and we pick those we want to block if the informations must be hidden !
			
			
			// Headers
			$col_headers = array("Date", "Nom produit", "Famille", "ID demande", "Type demande", "Raison Sociale", "Adresse", "Cplt adresse", "Code Postal", "Ville", "Pays", "Tel", "Fax", "Prénom", "Nom", "Fonction", "Email", "SIRET", "NAF", "Taille salariale", "Secteur", "Url", "Précisions", "Etat", "Date de rejet", "Etat traitement", "");
			
			// headers Hidden If Not Charged
			$col_headers_hinc = array("societe", "adresse", "cadresse", "tel", "fax", "nom", "prenom", "email", "naf", "siret", "url");
	
			$l = 0;
			foreach ($col_headers as $col_header) $worksheet->write($l, $c++, utf8_decode(utf8_encode($col_header)));
			$chi = array_flip($col_headers); // Cols Headers Index
			$nfchi = count($chi); // Next Free Col Header Index
			$l++;
	
	
			//while($content_get_contacts	= $db->fetchAssoc($res_get_contacts)){
			while ($cols = $db->fetchAssoc($result)) {
                if(empty($cols['name'])) $cols['name'] = 'Indéfini';
				$cols['timestamp'] = date("d/m/Y H:i", $cols['timestamp']);
						$cols['reject_timestamp'] = $cols['reject_timestamp'] != 0 ? date("d/m/Y H:i", $cols['reject_timestamp']) : '-';
						
				
					switch($cols['type']) {
						case 1 : $cols['type'] = "Demande d'informations"; break;
						case 2 : $cols['type'] = "Demande de contact téléphonique"; break;
						case 3 : $cols['type'] = "Demande de devis"; break;
						case 4 : $cols['type'] = "Demande de rendez-vous"; break;
						default : $cols['type'] = "Demande d'informations";
					}
					
				
				// Hiding fields if necessary
				if (!($cols["invoice_status"] & __LEAD_VISIBLE__)) {
					foreach($col_headers_hinc as $col_header_hinc)
						$cols[$col_header_hinc] = "-";
				}
				
				if(in_array($cols['invoice_status'], $array_contacts_invoice_status)){
					//Start hidding informations for the invoice_status that is not autorized
					$cols['timestamp'] 			= ' - ';
					$cols['name'] 				= ' - ';
					$cols['familyName'] 		= ' - ';
					$cols['type'] 				= ' - ';
					$cols['societe'] 			= ' - ';
					$cols['adresse'] 			= ' - ';
					$cols['cadresse'] 			= ' - ';
					$cols['ville'] 				= ' - ';
					$cols['pays'] 				= ' - ';
					$cols['tel'] 				= ' - ';
					$cols['fax'] 				= ' - ';
					$cols['prenom'] 			= ' - ';
					$cols['nom'] 				= ' - ';
					$cols['email'] 				= ' - ';
					$cols['siret'] 				= ' - ';
					$cols['naf'] 				= ' - ';
					$cols['salaries'] 			= ' - ';
					$cols['url'] 				= ' - ';
					//$cols['invoice_status'] 	= ' - ';
					$cols['credited_on'] 		= ' - ';
					$cols['customFields'] 		= ' - ';
					
					//Changes on 04/12/2014 to show always the "reject_timestamp" and the "invoice_status"
					//$cols['reject_timestamp'] 	= ' - ';
					
				}
				
				$cols["invoice_status"] = $lead_invoice_status_list[$cols["invoice_status"]].getCreditMonth($cols);
	
				
				$etat_traitement_export	= '';
				if($cols["archived"]=="1"){
					//Archivé
					$etat_traitement_export	= utf8_encode("Archivé");
				}else if($cols["cread"]=="1" && $cols["archived"]=="1"){
					//En traitement
					$etat_traitement_export	= "En traitement";
				}else{
					//Non lus
					$etat_traitement_export	= "Non lu";
				}
				
				$cols["archived"]	= $etat_traitement_export;
				
				//To clear the "cread" field
				//Because we use this field (result row) in the condition
				//And we do not want to show it !
				$cols["cread"]		= "";
				
				$c = 0;
				
				foreach($cols as $colName => &$colData) {
					if ($colName == 'customFields') {
						$customFields = unserialize($colData);
						if (empty($customFields)) $customFields = array();
						foreach($customFields as $cfName => $cfValue) {
							if (!isset($chi[$cfName])) { // If the col header does not exist, we create it
								$chi[$cfName] = $nfchi++;
								$worksheet->write(0, $chi[$cfName], utf8_decode($cfName));
							}
							$worksheet->write($l, $chi[$cfName], utf8_decode($cfValue));
						}
					}
					elseif ($colName != 'credited_on')  {
						$worksheet->write($l, $c++, utf8_decode($colData));
					}
				}
				unset($colData);
				
				$l++;	
			}//end while
			
			
			
			// Let's send the file
			$workbook->close();
		
		}else{
			//Nothing to extract !
		}//end else if global count 	if($total_count_results!=0)
	
	}//end check validity session !
?>