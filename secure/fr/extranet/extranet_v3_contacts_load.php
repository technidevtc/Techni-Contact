<?php
	require_once('extranet_v3_functions.php'); 
		
	if(!empty($_SESSION['extranet_user_id'])){
		
		//Getting params
		$f_ps							= mysql_escape_string($_POST['f_ps']);
		$f_pp							= mysql_escape_string($_POST['f_pp']);
		$fetat							= mysql_escape_string($_POST['fetat']);
		$f_date_debut					= mysql_escape_string($_POST['f_date_debut']);
		$f_date_fin						= mysql_escape_string($_POST['f_date_fin']);
		
		
		// Ajouter Le 01/07/2015 a 15:19 MA Par Zak Out
		$f_date_jours					= mysql_escape_string($_POST['f_date_jours']);
		$f_mois						    = mysql_escape_string($_POST['f_mois']);
		$f_mois_debut					= mysql_escape_string($_POST['f_mois_debut']);
		$f_mois_fin						= mysql_escape_string($_POST['f_mois_fin']);
		
		// Ajouter Le 01/07/2015 a 15:19 MA Par Zak Out
		
		$f_search						= mysql_escape_string($_POST['f_search']);
		$f_contacts_type				= mysql_escape_string($_POST['f_contacts_type']);
		

		$f_date_debut_ready				= '';
		$f_date_fin_ready				= '';
		$f_contacts_type_ready			= '';
		$query_suite_param				= '';
		$query_suite_param_etat			= '';
		$query_suite_param_limit_ready	= '';
		
		//To show the table of resume
		$check_date_show_table			= 0;
		
		
		
		/*echo($f_ps.'<br />');
		echo($f_pp.'<br />');
		echo($f_date_debut.' => '.$f_date_debut_ready.'<br />');
		echo($f_date_fin.' =>   '.$f_date_fin_ready.' <br /> ');
		echo($f_search.'<br />');
		echo($f_contacts_type.'<br />');*/
		
		
		//Building Query

		if(!empty($f_mois)){
			
			$sql_last_day  = "SELECT LAST_DAY(DATE_ADD('".$f_mois."-01', INTERVAL 0 MONTH)) as last_day";
			$req_last_day  =  mysql_query($sql_last_day);
			$data_last_day =  mysql_fetch_object($req_last_day);
			
			$f_mois_debut_ready	= strtotime($f_mois.'-01 00:00:00'); 
			$f_mois_fin_ready	= strtotime($data_last_day->last_day.' 23:59:59');
			
			$query_suite_param	= " AND
									  c.create_time BETWEEN ".$f_mois_debut_ready." AND ".$f_mois_fin_ready." ";
			$check_date_show_table			= 1;
		}

		if( (!empty($f_mois_debut)) && (!empty($f_mois_fin)) ){
			
			$sql_last_day  = "SELECT LAST_DAY(DATE_ADD('".$f_mois_debut."-01', INTERVAL 0 MONTH)) as last_day";
			$req_last_day  =  mysql_query($sql_last_day);
			$data_last_day =  mysql_fetch_object($req_last_day);
			
			
			$sql_last_day_fin  = "SELECT LAST_DAY(DATE_ADD('".$f_mois_fin."-01', INTERVAL 0 MONTH)) as last_day_fin";
			$req_last_day_fin  =  mysql_query($sql_last_day_fin);
			$data_last_day_fin =  mysql_fetch_object($req_last_day_fin);
			
			
			
			$f_mois_debut_ready	= strtotime($f_mois_debut.'-01 00:00:00');
			$f_mois_fin_ready	= strtotime($data_last_day_fin->last_day_fin.' 23:59:59');
			
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
			$check_date_show_table			= 1;
		}
		
		
		if(!empty($f_date_debut_ready) && !empty($f_date_fin_ready) ){
			$query_suite_param	= " AND 
										c.create_time BETWEEN ".$f_date_debut_ready." AND ".$f_date_fin_ready;
			$check_date_show_table			= 1;
		}
		
		if(!empty($f_search)){
			$f_search_params					= '';
			
			if(is_numeric($f_search)){
				//if it's a numeric
				$f_search_params			    = "c.id=".$f_search."";
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
												c.cread=0
										';
				if(!empty($fetat) || $fetat=='0' ){
					if($fetat == '25-9'){
						$query_suite_param_etat	.= ' AND  c.invoice_status IN (\'25\',\'9\') ';
					}else{
						$query_suite_param_etat	.= ' AND  c.invoice_status ='.$fetat.'  ';
					}
				}
			break;
			
			case 'read-not-read':
				$query_suite_param	.= ' AND c.cread IN (\'0\',\'1\') 
										 AND c.archived =\'0\' 
										';
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
												c.archived IN (\'0\',\'1\',\'2\')
										AND c.cread IN (\'0\',\'1\') 
										';
				if(!empty($fetat) || $fetat=='0' ){
					if($fetat == '25-9'){
						$query_suite_param_etat	.= ' AND  c.invoice_status IN (\'25\',\'9\') ';
					}else{
						$query_suite_param_etat	.= ' AND  c.invoice_status ='.$fetat.'  ';
					}
				}
			break;
			
		}//end switch
		
		
		
		if(isset($f_pp)){
			$query_suite_param_limit2 	= $f_pp;
		}else{
			$f_pp						= 10;
			$query_suite_param_limit2 	= " 10";
		}
		
		
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
		
		$res_get_contacts_cout = $db->query("SELECT 
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
											", __FILE__, __LINE__);
		$sql_req = "SELECT 
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
		// echo $sql_req;									
		
		$content_get_contacts_cout	= $db->fetchAssoc($res_get_contacts_cout);
		$total_count_results		= $content_get_contacts_cout['c'];
		
		if($total_count_results!=0){
		
			//Changes on 24/11/2014
			//Add the table resuming the activity
			//When the user do not filter with string
			//Reçu sur la période: X
			//Facturable ou facturés: XX
			//Hors champ de facturation: X
			//Rejetés: X
				if(empty($f_search) && strcmp($check_date_show_table,'1')==0){
					$testing_array_facturable	= array(152, 25, 9, 41);
					$testing_array_hors_champ	= array(0, 12);
					$testing_array_rejected		= array(2, 27, 154, 258, 514);
					
					$count_contacts_total		= 0;
					$count_contacts_facturable	= 0;
					$count_contacts_hors_champ	= 0;
					$count_contacts_rejected	= 0;
			
					$res_get_contacts_stats = $db->query("SELECT 
													c.invoice_status,
													count(c.invoice_status) c
												FROM
													contacts c 
														LEFT JOIN advertisers a ON a.id=c.idAdvertiser
														LEFT JOIN products_fr prod_fr ON prod_fr.id=c.idProduct
														LEFT JOIN families_fr f_fr	ON f_fr.id=c.idFamily
												WHERE
													c.idAdvertiser=".$_SESSION['extranet_user_id']."
												".$query_suite_param." 	
												".$query_suite_param_etat."
												GROUP BY invoice_status", __FILE__, __LINE__);
			
					while($content_get_contacts_stats	= $db->fetchAssoc($res_get_contacts_stats)){
					
						//Counting the total of the found
						$count_contacts_total	+= $content_get_contacts_stats['c'];
						
						//Testing of the facturable
						if(in_array($content_get_contacts_stats['invoice_status'], $testing_array_facturable)){
							$count_contacts_facturable += $content_get_contacts_stats['c'];
						}
						
						//Testing for the hors champ
						if(in_array($content_get_contacts_stats['invoice_status'], $testing_array_hors_champ)){
							$count_contacts_hors_champ += $content_get_contacts_stats['c'];
						}
						
						//Teting for the rejected
						if(in_array($content_get_contacts_stats['invoice_status'], $testing_array_rejected)){
							$count_contacts_rejected += $content_get_contacts_stats['c'];
						}
					
					}//end while fetching
					
					//Testing if the user have category 2 or other !
					
						echo('<table id="contacts_stats_resume">');
						
							echo('<tr>');
								echo('<td>');
									echo('Re&ccedil;us sur la p&eacute;riode ');
								echo('</td>');
								echo('<td>');
									echo($count_contacts_total);
								echo('</td>');
							echo('</tr>');
							
							if(strcmp($_SESSION['extranet_user_category'],'2')!=0){
							
								echo('<tr>');
									echo('<td>');
										echo('Facturables ou factur&eacute;s ');
									echo('</td>');
									echo('<td>');
										echo($count_contacts_facturable);
									echo('</td>');
								echo('</tr>');
								
								echo('<tr>');
									echo('<td>');
										echo('Hors champs de facturation ');
									echo('</td>');
									echo('<td>');
										echo($count_contacts_hors_champ);
									echo('</td>');
								echo('</tr>');
								
								echo('<tr>');
									echo('<td>');
										echo('Rejet&eacute;s ');
									echo('</td>');
									echo('<td>');
										echo($count_contacts_rejected);
									echo('</td>');
								echo('</tr>');
							
							}//end if 
						
						echo('</table>');
					
					
				}//end if empty string filter
		
			//Executing the query of contacts and loading the informations
			$res_get_contacts = $db->query("SELECT 
												c.id, c.type,
												c.create_time, c.nom,
												c.prenom, c.societe, 
												c.idProduct, c.invoice_status,
												c.parent,
												c.archived,
												c.cread,
												
												prod_fr.name prod_name, prod_fr.fastdesc prod_fastdesc,
												
												f_fr.name fam_name
											FROM
												contacts c 
													LEFT JOIN advertisers a ON a.id=c.idAdvertiser
													LEFT JOIN products_fr prod_fr ON prod_fr.id=c.idProduct
													LEFT JOIN families_fr f_fr	ON f_fr.id=c.idFamily
											WHERE
												c.idAdvertiser=".$_SESSION['extranet_user_id']."
											".$query_suite_param." 
											".$query_suite_param_etat."
											ORDER BY c.create_time DESC 
											".$query_suite_param_limit_ready." ", __FILE__, __LINE__);
				
											
			echo('<div id="page_list_selector_container">');

				echo('<div id="page_list_selector_text">');
					echo('Contacts par page ');
				echo('</div>');//end div #page_list_selector_text
				
				echo('<div id="page_list_selector_select">');
				
					echo('<select id="f_select_p" onchange="contacts_get_more_now()">');
						echo('<option value="5"');
						if(strcmp($f_pp,5)==0){
							echo(' selected="true" ');
						}
						echo('>5</option>');
						
						echo('<option value="10"');
						if(strcmp($f_pp,10)==0){
							echo(' selected="true" ');
						}
						echo('>10</option>');
						
						echo('<option value="15"');
						if(strcmp($f_pp,15)==0){
							echo(' selected="true" ');
						}
						echo('>15</option>');
						
						echo('<option value="50"');
						if(strcmp($f_pp,50)==0){
							echo(' selected="true" ');
						}
						echo('>50</option>');
						
						echo('<option value="100"');
						if(strcmp($f_pp,100)==0){
							echo(' selected="true" ');
						}
						echo('>100</option>');
						
					echo('</select>');
					
				echo('</div>');//end div #page_list_selector_select
				
				// ***********Filtrer par etat************/
				echo('<div id="page_list_selector_text" style="width: 92px;">');
					echo('Filtrer par etat ');
				echo('</div>');
				
				$sql_etat = "	SELECT 
									DISTINCT(c.invoice_status)
								FROM
									contacts c
								LEFT JOIN advertisers a ON a.id=c.idAdvertiser
								LEFT JOIN products_fr prod_fr ON prod_fr.id=c.idProduct
								LEFT JOIN families_fr f_fr	ON f_fr.id=c.idFamily 
								WHERE 
								c.idAdvertiser=".$_SESSION['extranet_user_id']."
								".$query_suite_param." AND c.invoice_status IN('25','9')  ";
				
				$req_etat = mysql_query($sql_etat);
				$rows_etat= mysql_num_rows($req_etat);
				
				$sql_etat2 = "	SELECT 
									DISTINCT(c.invoice_status)
								FROM
									contacts c
								LEFT JOIN advertisers a ON a.id=c.idAdvertiser
								LEFT JOIN products_fr prod_fr ON prod_fr.id=c.idProduct
								LEFT JOIN families_fr f_fr	ON f_fr.id=c.idFamily 
								WHERE 
								c.idAdvertiser=".$_SESSION['extranet_user_id']."
								".$query_suite_param." AND c.invoice_status NOT IN('25','9')  ";
				
				$req_etat2 = mysql_query($sql_etat2);
				$rows_etat2= mysql_num_rows($req_etat2);
				
				echo('<select id="filtre_etat" onchange="contact_get_filtre_etat()">');
				echo '<option></option>';
					if($rows_etat > 0){
						$fetat_explode = explode('-',$fetat);
						$data_etat = mysql_fetch_assoc($req_etat);
						if(($data_etat['invoice_status'] == $fetat_explode[0]) || ($data_etat['invoice_status'] == $fetat_explode[1])){
							echo '<option value="25-9" selected="true" >Facturé</option>';
						}else {
							echo '<option value="25-9">Facturé</option>';
						}
					}
					if($rows_etat2 > 0){	
					while($data_etat2 = mysql_fetch_assoc($req_etat2)){
						if($data_etat2['invoice_status'] == $fetat){
							echo '<option value="'.$data_etat['invoice_status'].'" selected="true">'.ucfirst($lead_invoice_status_list[$data_etat2['invoice_status']]).'</option>';
						}else {
							echo '<option value="'.$data_etat2['invoice_status'].'">'.ucfirst($lead_invoice_status_list[$data_etat2['invoice_status']]).'</option>';
							
						}
					}
					}
				echo('</select>');
				// *********** End filtrer par etat************/
				
				
				//Restriction export for some advertisers
				if(!in_array($_SESSION['extranet_user_category'], $array_contacts_id_advertisers)){
					echo('<div id="contacts_extract_container" style="float: right;">');
						echo('<input type="button" id="f_filtrer" value="Extraire" alt="Extraire" title="Extraire" onclick="contacts_extract()" class="btn-primary btn" style="padding: 3px 15px 3px 15px;" />');
					echo('</div>');//end div #contacts_extract_container
				}
					
			echo('</div>');//end div #page_list_selector_container
											
			echo('<div class="table-responsive">');
				echo('<table class="table">');
					echo('<tr>');
						echo('<th style="width: 6%;">');
							echo('ID');
						echo('</th>');
						
						echo('<th style="width: 10%;">');
							echo('Etat');
						echo('</th>');
						
						echo('<th style="width: 12%;">');
							echo('Date');
						echo('</th>');
						
						echo('<th style="width: 15%;">');
							echo('Nom et soci&eacute;t&eacute;');
						echo('</th>');
						
						echo('<th>');
							echo('Produit concern&eacute;');
						echo('</th>');
						
						echo('<th style="width: 8%;">');
							echo('Actions');
						echo('</th>');
						
					echo('</tr>');
				
			$modulo_local_loop	= 0;
			$modulo_local_class	= 'alt';
			
			$local_row_bold		= '';
			
			while($content_get_contacts	= $db->fetchAssoc($res_get_contacts)){
			
				//Calculating modulo to make a difference between the table rows !!
				if($modulo_local_loop%2){
					$modulo_local_class	= 'alt';
				}else{
					$modulo_local_class	= '';
				}
					
				$row_link	= EXTRANET_URL.'extranet-v3-contacts-detail.html?id='.$content_get_contacts['id'];
				
				if(strcmp($content_get_contacts['cread'],'0')==0){
					$local_row_bold	= 'nr';
				}else{
					$local_row_bold	= '';
				}
				
				echo('<tr class="rs '.$local_row_bold.' '.$modulo_local_class.'">');
					echo('<td class="valign" onclick="javascript:open_link_blank(\'contacts_external_formid\', \''.$row_link.'\', \'_self\')">');
					
						if(!in_array($_SESSION['extranet_user_category'], $array_contacts_id_advertisers)){
							if(!in_array($content_get_contacts['invoice_status'], $array_contacts_invoice_status)){
								echo($content_get_contacts['id']);
							}else{
								echo(' - ');
							}//end invoice_status restrictions
						}else{
							echo(' - ');
						}//end test advertisers category restrictions
						
					echo('</td>');
					
					echo('<td class="valign" onclick="javascript:open_link_blank(\'contacts_external_formid\', \''.$row_link.'\', \'_self\')">');
						echo(ucfirst($lead_invoice_status_list[$content_get_contacts['invoice_status']]));
					echo('</td>');
					
					echo('<td onclick="javascript:open_link_blank(\'contacts_external_formid\', \''.$row_link.'\', \'_self\')">');
						echo(date('d/m/Y H:i:s', $content_get_contacts['create_time']));
					echo('</td>');
					
					echo('<td onclick="javascript:open_link_blank(\'contacts_external_formid\', \''.$row_link.'\', \'_self\')">');
						if(!in_array($_SESSION['extranet_user_category'], $array_contacts_id_advertisers)){
							if(!in_array($content_get_contacts['invoice_status'], $array_contacts_invoice_status)){
								echo($content_get_contacts['nom'].' '.$content_get_contacts['prenom'].' ('.$content_get_contacts['societe'].')');
							}else{
								echo(' Hors champs de facturation ');
							}
						}else{
							echo(' Hors champs de facturation ');
						}//end test advertisers category restrictions
					echo('</td>');
					
					echo('<td onclick="javascript:open_link_blank(\'contacts_external_formid\', \''.$row_link.'\', \'_self\')">');
						if(strcmp($content_get_contacts['parent'],0)==0){
							echo($content_get_contacts['prod_name'].' - '.$content_get_contacts['prod_fastdesc']);
						}else{
							echo($content_get_contacts['fam_name']);
						}
					echo('</td>');
					
					echo('<td class="valign cursor-default">');
						
						echo('<a href="'.EXTRANET_URL.'extranet-v3-contacts-detail.html?id='.$content_get_contacts['id'].'" target="_blank"><i class="fa fa-eye"></i></a> ');
						
						if(!in_array($_SESSION['extranet_user_category'], $array_contacts_id_advertisers)){
							if(!in_array($content_get_contacts['invoice_status'], $array_contacts_invoice_status)){
								echo('<a href="'.EXTRANET_URL.'extranet-v3-contacts-detail-print.html?id='.$content_get_contacts['id'].'&uid='.$_SESSION['extranet_user_webpass'].'" target="_blank"><i class="fa fa-print" title="Imprimer"></i></a> ');
							}//end invoice_status restrictions
						}//end test advertisers category restriction
						
						if(strcmp($content_get_contacts['archived'],'0')=='0'){
							echo('<a href="javascript:void(0)" onclick="contact_archive_me(\''. $content_get_contacts['id'].';\', \'listing\')"><i class="fa fa-archive" title="Archiver"></i></a> ');
							
							echo('<a href="javascript:void(0)" onclick="contact_delete_me(\''. $content_get_contacts['id'].';\', \'listing\')"><i class="fa fa-trash" title="Supprimer"></i></a> ');
						}
						
						if(!in_array($_SESSION['extranet_user_category'], $array_contacts_id_advertisers)){
							if(!in_array($content_get_contacts['invoice_status'], $array_contacts_invoice_status)){
								echo('<a href="'.EXTRANET_URL.'extranet-v3-contacts-detail.html?id='.$content_get_contacts['id'].'#forward" target="_blank"><i class="fa fa-mail-forward" title="Transfert"></i></a>');
							}//end invoice_status restrictions
						}//end test advertisers category restrictions


					echo('</td>');
					
				echo('</tr>');
				
				$modulo_local_loop++;
				
			}//End while
			
				echo('</table>');
			echo('</div>');
				
			echo('<div class="row" style="margin-left:0;">');
				/*
				echo('<div class="form-left">');
					echo('&nbsp;');
				echo('</div>');
				
				echo('<div class="form-middle">');
					
				echo('</div>');
				*/
				
				echo('<div class="contacts-bottom-pagination pagination">');
					
					echo('<div style="color:#52BFEA; float:left; padding-top: 2px;">'.$total_count_results.' Contact');
					if($total_count_results>1){
						echo("s");
					}
					echo("</div>");
					
					$total_count_copy	= $total_count_results;
					
					$nb_pages=1;
					while(($total_count_copy  > $f_pp) ){ 
						$nb_pages ++;
						$total_count_copy  = $total_count_copy  -$f_pp;
					}
					
					
					if($nb_pages>0){
						$count_pagination = 1;
						if($page>=2){
							$precedent = $page-1;
									echo('<div class="disabled"><a href="javascript:contacts_load_other_page(\''.$precedent.'\',\''.$fetat.'\')">< Prec.</a></div>'); 
						}else{
							echo('<div class="disabled"><< Prec.</div>');
						}
						
						//echo(' [');

						if($page>10){
								$count_local=$page-5; 
								$count_pagination = $count_local+1;
						}else if($page==10){
								$count_local=5; 
								$count_pagination = $count_local+1;
						}else if($page==9){
								$count_local=4;
								$count_pagination = $count_local+1;
						}else if($page==8){
								$count_local=3;
								$count_pagination = $count_local+1;
						}else if($page==7){
								$count_local=2;
								$count_pagination = $count_local+1;
						}else if($page==6){
								$count_local=1;
								$count_pagination = $count_local+1;    
						}else{
								$count_local=0;
								$count_pagination = $count_local+1;
						}

						if($count_pagination >2){		
							echo('<a title="page num&eacute;ro 1" href="javascript:contacts_load_other_page(\'1\',\''.$fetat.'\')" class="pgnt_nmbr"> 1 </a><span style="float:left; padding: 0px 3px 0px 3px;">..</span>');
						}

						$count_local_stop=$page+4;

						while( ($count_pagination<=$nb_pages) && ($count_local<$count_local_stop) ){
							if($count_pagination==$page){
								echo('<div class="disabled">&nbsp;'.$count_pagination.'&nbsp;</div>');
							}else{
									echo('<a title="page num&eacute;ro '.$count_pagination.'" href="javascript:contacts_load_other_page(\''.$count_pagination.'\',\''.$fetat.'\')" class="pgnt_nmbr"> '.$count_pagination.' </a>');	
							}
							$count_pagination++;
							$count_local++;
						}

						if(($count_pagination <=$nb_pages) ){ //pour ne pas afficher suivant si on est dans la derniere page	
								echo('<span style="float:left; padding: 0px 3px 0px 3px;">..</span><a title="page num&eacute;ro '.$nb_pages.'" href="javascript:contacts_load_other_page(\''.$nb_pages.'\',\''.$fetat.'\')" class="pgnt_nmbr"> '.$nb_pages.' </a>');
						}
						
						//echo('] ');
						if($page<$nb_pages ){//partir a la derniere page (30) si on a les resultats qui depasse 300 
							$suivant = $page +1;
								echo('<a title="page num&eacute;ro '.$suivant.'" href="javascript:contacts_load_other_page(\''.$suivant.'\',\''.$fetat.'\')" style="text-decoration:none;" class="pgnt_nmbr">&nbsp;Suiv.&nbsp;</a>');
						}
					}
					
					// Fin pagination
					
				echo('</div>');
				
				
			echo('</div>');//end div .row	
				
										
		}else{
			echo("Aucune information &agrave; afficher !");
		}//end else if global count 	if($total_count_results!=0)

	}else{
		echo('<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;<strong><a href="login.html">Merci de vous reconnecter.</a></strong>');
	}
?>