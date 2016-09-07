<?php

	require_once('functions.php'); 

	if(!empty($_SESSION['marketing_user_id'])){
	
		//Call files to get the user's roles (Navigation and Tables)
		require_once('check_session_table_query.php');
		require_once('check_session_page_query.php');
	
	
		//Getting params
		$f_ps							= mysql_escape_string($_POST['f_ps']);
		$f_pp							= mysql_escape_string($_POST['f_pp']);
		$f_search						= mysql_escape_string($_POST['f_search']);
		
		$table_order					= mysql_escape_string($_POST['table_order']);
		
	
		
		if(!empty($f_search)){
			
			/*$search_sql_where		=" AND (
										m_seg.name like '".$f_search."'
										)
									";*/	

			$search_sql_where		=" WHERE (
										m_campaigns.name like '".$f_search."%'
										)
									";								
			
			$search_sql_order_by	=  "order by ((m_campaigns.name*0.8)) DESC";
			
			
		}else{
			$search_sql_order_by	=  "order by m_campaigns.id DESC";
		}
		
		//The switch case for the Order in the table !
		switch($table_order){
		
			//ID
			case 'id_asc':
				$search_sql_order_by	=  "order by m_campaigns.id ASC";
			break;
			case 'id_desc':
				$search_sql_order_by	=  "order by m_campaigns.id DESC";
			break;
			
			//Type
			case 'type_asc':
				$search_sql_order_by	=  "order by m_campaigns.type ASC";
			break;
			case 'type_desc':
				$search_sql_order_by	=  "order by m_campaigns.type DESC";
			break;
			
			//Campaign Name
			case 'campaign_name_asc':
				$search_sql_order_by	=  "order by m_campaigns.name ASC";
			break;
			case 'campaign_name_desc':
				$search_sql_order_by	=  "order by m_campaigns.name DESC";
			break;
			
			//Message Name
			case 'message_name_asc':
				$search_sql_order_by	=  "order by m_messages.name ASC";
			break;
			case 'message_name_desc':
				$search_sql_order_by	=  "order by m_messages.name DESC";
			break;
			
			//Segment Name
			case 'segment_name_asc':
				$search_sql_order_by	=  "order by m_segment.name ASC";
			break;
			case 'segment_name_desc':
				$search_sql_order_by	=  "order by m_segment.name DESC";
			break;
			
			//Sent Date
			case 'sent_date_asc':
				$search_sql_order_by	=  "order by m_campaigns.date_last_sent ASC";
			break;
			case 'sent_date_desc':
				$search_sql_order_by	=  "order by m_campaigns.date_last_sent DESC";
			break;
		
			//Brut Emails
			case 'brut_emails_asc':
				$search_sql_order_by	=  "order by m_campaigns.emails_brut ASC";
			break;
			case 'brut_emails_desc':
				$search_sql_order_by	=  "order by m_campaigns.emails_brut DESC";
			break;
			
			//Sent Emails
			case 'sent_emails_asc':
				$search_sql_order_by	=  "order by m_campaigns.emails_sent ASC";
			break;
			case 'sent_emails_desc':
				$search_sql_order_by	=  "order by m_campaigns.emails_sent DESC";
			break;
			
			//Campaign Etat
			case 'campaign_etat_asc':
				$search_sql_order_by	=  "order by m_campaigns.etat ASC";
			break;
			case 'campaign_etat_desc':
				$search_sql_order_by	=  "order by m_campaigns.etat DESC";
			break;
			
			
			default :
				$search_sql_order_by	=  "order by m_campaigns.id DESC";
				
				//To use in the Table Header
				$table_order			= "id_desc";
			break;
			
		
		}//end switch
	
	
		//Limites
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
	
		
		$res_get_campaigns_count_query	= "SELECT
												count(*) AS c
												
											FROM 
												marketing_campaigns AS m_campaigns 
													INNER JOIN marketing_messages m_messages ON m_campaigns.id_message=m_messages.id
													INNER JOIN marketing_segment AS m_segment	ON m_segment.id=m_messages.id_segment
											
											
											 
											".$search_sql_where."
											
											".$search_sql_order_by."";
	
	
		$res_get_campaigns_count = $db->query($res_get_campaigns_count_query, __FILE__, __LINE__);
		
		$content_get_campaigns_count	= $db->fetchAssoc($res_get_campaigns_count);
		$total_count_results		= $content_get_campaigns_count['c'];
	
	
		if($total_count_results!=0){
			$res_get_campaigns_query	= "SELECT
											m_campaigns.id, 
											m_campaigns.type, 
											m_campaigns.name AS c_name, 

											m_messages.name AS m_name, 

											m_segment.name AS s_name, 
											m_segment.id_table, 
											m_segment.results_count, 

											m_campaigns.date_last_sent, 
											m_campaigns.emails_brut, 
											m_campaigns.emails_sent, 
											m_campaigns.etat 
											
										FROM 
											marketing_campaigns AS m_campaigns 
												INNER JOIN marketing_messages m_messages ON m_campaigns.id_message=m_messages.id
												INNER JOIN marketing_segment AS m_segment	ON m_segment.id=m_messages.id_segment
										
										".$f_type_params." 
										".$search_sql_where."
										
										".$search_sql_order_by."  
										".$query_suite_param_limit_ready."";	
									
			$res_get_campaigns = $db->query($res_get_campaigns_query, __FILE__, __LINE__);
		
			//echo('We got segments => '.$total_count_results.' * '.mysql_num_rows($res_get_campaigns));
			
			echo('<div id="page_list_selector_container" style="padding: 25px 0 15px 10px; width:40%; float:left;">');

				echo('<div id="page_list_selector_text">');
					echo('Campagnes par page ');
				echo('</div>');//end div #page_list_selector_text
				
				echo('<div id="page_list_selector_select">');
				
					echo('<select id="f_select_p" onchange="campaigns_get_more_now()">');
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
					
			echo('</div>');//end div #page_list_selector_container
			
			//Start writing pagination
			echo('<div class="pagination campaigns-top-pagination">');
				echo('<div style="color:#52BFEA; float:left; padding-top: 2px;">'.$total_count_results.' Campagne');
				if($total_count_results>1){
					echo("s");
				}
				echo("&nbsp;&nbsp;</div>");
				
				
				if(strcmp($f_pp,'tout')!=0){

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
										echo('<div class="disabled"><a href="javascript:campaigns_load_other_page(\''.$precedent.'\')">< Prec.</a></div>'); 
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
								echo('<a title="page num&eacute;ro 1" href="javascript:campaigns_load_other_page(\'1\')" class="pgnt_nmbr"> 1 </a><span style="float:left; padding: 0px 3px 0px 3px;">..</span>');
							}

							$count_local_stop=$page+4;

							while( ($count_pagination<=$nb_pages) && ($count_local<$count_local_stop) ){
								if($count_pagination==$page){
									echo('<div class="disabled">&nbsp;'.$count_pagination.'&nbsp;</div>');
								}else{
										echo('<a title="page num&eacute;ro '.$count_pagination.'" href="javascript:campaigns_load_other_page(\''.$count_pagination.'\')" class="pgnt_nmbr"> '.$count_pagination.' </a>');	
								}
								$count_pagination++;
								$count_local++;
							}

							if(($count_pagination <=$nb_pages) ){ //pour ne pas afficher suivant si on est dans la derniere page	
									echo('<span style="float:left; padding: 0px 3px 0px 3px;">..</span><a title="page num&eacute;ro '.$nb_pages.'" href="javascript:campaigns_load_other_page(\''.$nb_pages.'\')" class="pgnt_nmbr"> '.$nb_pages.' </a>');
							}
							
							//echo('] ');
							if($page<$nb_pages ){//partir a la derniere page (30) si on a les resultats qui depasse 300 
								$suivant = $page +1;
									echo('<a title="page num&eacute;ro '.$suivant.'" href="javascript:campaigns_load_other_page(\''.$suivant.'\')" style="text-decoration:none;" class="pgnt_nmbr">&nbsp;Suiv.&nbsp;</a>');
							}
						}
						
						// Fin pagination
						
					
				}//end if(strcmp($f_pp,'tout')!=0)
			
			echo('</div>');//end div .form-right .pagination
				
			echo('<div class="campaigns table-responsive">');
				echo('<table class="table" style="width:100%;">');
					echo('<tr>');
						echo('<th style="width: 5%; cursor:pointer;" ');
						if(strcmp($table_order,'id_asc')==0){
							echo(' onclick="campaigns_list_order_by(\'id_desc\');"> <i class="fa fa-level-up"></i>');
						}else if(strcmp($table_order,'id_desc')==0){
							echo(' onclick="campaigns_list_order_by(\'id_asc\');">  <i class="fa fa-level-down"></i>');
						}else{
							echo(' onclick="campaigns_list_order_by(\'id_desc\');">');
						}
						
							echo('ID');
						echo('</th>');
						
						echo('<th style="width: 10%; cursor:pointer;" ');
						if(strcmp($table_order,'type_asc')==0){
							echo(' onclick="campaigns_list_order_by(\'type_desc\');"> <i class="fa fa-level-up"></i>');
						}else if(strcmp($table_order,'type_desc')==0){
							echo(' onclick="campaigns_list_order_by(\'type_asc\');">  <i class="fa fa-level-down"></i>');
						}else{
							echo(' onclick="campaigns_list_order_by(\'type_desc\');">');
						}
						
							echo('Type');
						echo('</th>');
						
						echo('<th style="width: 15%; cursor:pointer;" ');
						if(strcmp($table_order,'campaign_name_asc')==0){
							echo(' onclick="campaigns_list_order_by(\'campaign_name_desc\');"> <i class="fa fa-level-up"></i>');
						}else if(strcmp($table_order,'campaign_name_desc')==0){
							echo(' onclick="campaigns_list_order_by(\'campaign_name_asc\');">  <i class="fa fa-level-down"></i>');
						}else{
							echo(' onclick="campaigns_list_order_by(\'campaign_name_desc\');">');
						}
						
							echo('Nom');
						echo('</th>');
						
						echo('<th style="width: 15%; cursor:pointer;" ');
						if(strcmp($table_order,'message_name_asc')==0){
							echo(' onclick="campaigns_list_order_by(\'message_name_desc\');"> <i class="fa fa-level-up"></i>');
						}else if(strcmp($table_order,'message_name_desc')==0){
							echo(' onclick="campaigns_list_order_by(\'message_name_asc\');">  <i class="fa fa-level-down"></i>');
						}else{
							echo(' onclick="campaigns_list_order_by(\'message_name_desc\');">');
						}
						
							echo('Message');
						echo('</th>');
						
						echo('<th style="width: 15%; cursor:pointer;" ');
						if(strcmp($table_order,'segment_name_asc')==0){
							echo(' onclick="campaigns_list_order_by(\'segment_name_desc\');"> <i class="fa fa-level-up"></i>');
						}else if(strcmp($table_order,'segment_name_desc')==0){
							echo(' onclick="campaigns_list_order_by(\'segment_name_asc\');">  <i class="fa fa-level-down"></i>');
						}else{
							echo(' onclick="campaigns_list_order_by(\'segment_name_desc\');">');
						}
						
							echo('Segment');
						echo('</th>');
						
						echo('<th style="width: 8%; cursor:pointer;" ');
						if(strcmp($table_order,'sent_date_asc')==0){
							echo(' onclick="campaigns_list_order_by(\'sent_date_desc\');"> <i class="fa fa-level-up"></i>');
						}else if(strcmp($table_order,'sent_date_desc')==0){
							echo(' onclick="campaigns_list_order_by(\'sent_date_asc\');">  <i class="fa fa-level-down"></i>');
						}else{
							echo(' onclick="campaigns_list_order_by(\'sent_date_desc\');">');
						}
						
							echo('D. Envoi');
						echo('</th>');
						
						echo('<th style="width: 7%; cursor:pointer;" ');
						if(strcmp($table_order,'brut_emails_asc')==0){
							echo(' onclick="campaigns_list_order_by(\'brut_emails_desc\');"> <i class="fa fa-level-up"></i>');
						}else if(strcmp($table_order,'brut_emails_desc')==0){
							echo(' onclick="campaigns_list_order_by(\'brut_emails_asc\');">  <i class="fa fa-level-down"></i>');
						}else{
							echo(' onclick="campaigns_list_order_by(\'brut_emails_desc\');">');
						}
						
							echo('@ Bruts');
						echo('</th>');
						
						echo('<th style="width: 7%; cursor:pointer;" ');
						if(strcmp($table_order,'sent_emails_asc')==0){
							echo(' onclick="campaigns_list_order_by(\'sent_emails_desc\');"> <i class="fa fa-level-up"></i>');
						}else if(strcmp($table_order,'sent_emails_desc')==0){
							echo(' onclick="campaigns_list_order_by(\'sent_emails_asc\');">  <i class="fa fa-level-down"></i>');
						}else{
							echo(' onclick="campaigns_list_order_by(\'sent_emails_desc\');">');
						}
						
							echo('@ Envoy&eacute;s');
						echo('</th>');
						
						echo('<th style="width: 8%; cursor:pointer;" ');
						if(strcmp($table_order,'campaign_etat_asc')==0){
							echo(' onclick="campaigns_list_order_by(\'campaign_etat_desc\');"> <i class="fa fa-level-up"></i>');
						}else if(strcmp($table_order,'campaign_etat_desc')==0){
							echo(' onclick="campaigns_list_order_by(\'campaign_etat_asc\');">  <i class="fa fa-level-down"></i>');
						}else{
							echo(' onclick="campaigns_list_order_by(\'campaign_etat_desc\');">');
						}
						
							echo('Etat');
						echo('</th>');
							
						echo('<th style="width: 10%;">');
							echo('Actions');
						echo('</th>');
						
					echo('</tr>');
		
				$modulo_local_loop	= 0;
				$modulo_local_class	= 'alt';
				
				while($content_get_campaigns	= $db->fetchAssoc($res_get_campaigns)){
				
					//Calculating modulo to make a difference between the table rows !!
					if($modulo_local_loop%2){
						$modulo_local_class	= 'alt';
					}else{
						$modulo_local_class	= '';
					}
					
					
					//$row_link	= 'segments-edit.php?id='.$content_get_campaigns['id'];
					
					
					//If the product is Pending the click link will be => Show in front => Blank
					//ELse go to Edit page => Self
					
					
					
					echo('<tr class="rs '.$modulo_local_class.'" >');
						echo('<td class="valign">');
							echo($content_get_campaigns['id']);
						echo('</td>');
						
						echo('<td class="valign">');
							echo($content_get_campaigns['type']);
						echo('</td>');
						
						echo('<td class="valign">');
							echo($content_get_campaigns['c_name']);
						echo('</td>');
						
						echo('<td class="valign">');
							echo($content_get_campaigns['m_name']);
						echo('</td>');
						
						echo('<td class="valign">');
							echo($content_get_campaigns['s_name']);
						echo('</td>');
							
						echo('<td class="valign">');
							if(!empty($content_get_campaigns['date_last_sent']) && strcmp($content_get_campaigns['date_last_sent'],"NULL")!=0 && strcmp($content_get_campaigns['date_last_sent'],"0000-00-00 00:00:00")!=0){
								echo(date('d/m/Y H:i:s', strtotime($content_get_campaigns['date_last_sent'])));
							}else{
								echo(' - ');
							}
						echo('</td>');
						
						echo('<td class="valign">');
							if(strcmp($content_get_campaigns['etat'],"Finalized")==0){
								echo($content_get_campaigns['results_count']);
							}else{
								echo($content_get_campaigns['emails_brut']);
							}
						echo('</td>');
						
						echo('<td class="valign">');
							echo($content_get_campaigns['emails_sent']);
						echo('</td>');
						
						
						echo('<td class="valign">');
							switch($content_get_campaigns['etat']){
								case 'Saved':
									echo('Enregistr&eacute;e');
								break;
								
								case 'Programmed':
									echo('Programm&eacute;e');
								break;
								
								case 'Processing':
									echo('En cours');
								break;
								
								case 'Finalized':
									echo('Finalis&eacute;e');
								break;
								
								default:
									echo(' - ');
								break;
							}
						echo('</td>');
						
						
						echo('<td class="valign cursor-default">');
						
							//Test 3 en attente Modification ou Suppression
							
							
								//echo('<i class="fa fa-eye"></i>');
							/*
							echo($content_get_user_page_permissions['content']);
							echo('<br />****Access**<br />');
							echo($content_get_user_tables_access_permissions['content']);
							echo('<br />****Export**<br />');
							echo($content_get_user_tables_export_permissions['content']);
							*/
								//Test on privilege on the page head ! or from session !
								
								//$content_get_campaigns['id'];
								
								
								//Edition
								if(strpos($content_get_user_page_permissions['content'],'#14#')!==FALSE && strpos($content_get_user_tables_access_permissions['content'],'#'.$content_get_campaigns['id_table'].'#')!==FALSE){
									
									//And The campaign must be "Saved" Or "Programmed"
									if(strcmp($content_get_campaigns['etat'],'Saved')==0 || strcmp($content_get_campaigns['etat'],'Programmed')==0){
										echo('<a href="/fr/marketing/edit-campaign.php?id='.$content_get_campaigns['id'].'" title="Modifier"><i class="fa fa-pencil"></i></a>');
										echo('&nbsp;');
									}
								}
								
								//Suppression
								if(strpos($content_get_user_page_permissions['content'],'#15#')!==FALSE && strpos($content_get_user_tables_access_permissions['content'],'#'.$content_get_campaigns['id_table'].'#')!==FALSE){
								
									//And The campaign must be "Saved" Or "Programmed"
									if(strcmp($content_get_campaigns['etat'],'Saved')==0 || strcmp($content_get_campaigns['etat'],'Programmed')==0){
										echo('<a href="javascript:void(0);" onclick="javascript:campaign_ask_delete_display_modal(\''.$content_get_campaigns['id'].'\');" title="Supprimer"><i class="fa fa-trash-o"></i></a>');
									}
								}
								
						echo('</td>');
							
					echo('</tr>');		
			
			
					$modulo_local_loop++;
				}//end while
				
				
				echo('</table>');
			echo('</div>');
			
			echo('<div class="row">');
				echo('<div class="form-left">');					
				echo('</div>');
				
			echo('</div>');//end div .row	
			
			echo('<div class="row" style="margin-right:0;">');
				echo('<br />');
				
				/*
				echo('<div class="form-left">');
				echo('</div>');
				
				echo('<div class="form-middle">');
				echo('</div>');
				*/
				
				//Start writing pagination
				echo('<div class="campaigns-bottom-pagination pagination prdct">');
					echo('<div style="color:#52BFEA; float:left; padding-top: 2px;">'.$total_count_results.' Campagne');
					if($total_count_results>1){
						echo("s");
					}
					echo("&nbsp;&nbsp;</div>");
					
					
					if(strcmp($f_pp,'tout')!=0){

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
											echo('<div class="disabled"><a href="javascript:campaigns_load_other_page(\''.$precedent.'\')">< Prec.</a></div>'); 
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
									echo('<a title="page num&eacute;ro 1" href="javascript:campaigns_load_other_page(\'1\')" class="pgnt_nmbr"> 1 </a><span style="float:left; padding: 0px 3px 0px 3px;">..</span>');
								}

								$count_local_stop=$page+4;

								while( ($count_pagination<=$nb_pages) && ($count_local<$count_local_stop) ){
									if($count_pagination==$page){
										echo('<div class="disabled">&nbsp;'.$count_pagination.'&nbsp;</div>');
									}else{
											echo('<a title="page num&eacute;ro '.$count_pagination.'" href="javascript:campaigns_load_other_page(\''.$count_pagination.'\')" class="pgnt_nmbr"> '.$count_pagination.' </a>');	
									}
									$count_pagination++;
									$count_local++;
								}

								if(($count_pagination <=$nb_pages) ){ //pour ne pas afficher suivant si on est dans la derniere page	
										echo('<span style="float:left; padding: 0px 3px 0px 3px;">..</span><a title="page num&eacute;ro '.$nb_pages.'" href="javascript:campaigns_load_other_page(\''.$nb_pages.'\')" class="pgnt_nmbr"> '.$nb_pages.' </a>');
								}
								
								//echo('] ');
								if($page<$nb_pages ){//partir a la derniere page (30) si on a les resultats qui depasse 300 
									$suivant = $page +1;
										echo('<a title="page num&eacute;ro '.$suivant.'" href="javascript:campaigns_load_other_page(\''.$suivant.'\')" style="text-decoration:none;" class="pgnt_nmbr">&nbsp;Suiv.&nbsp;</a>');
								}
							}
							
							// Fin pagination
							
						
					}//end if(strcmp($f_pp,'tout')!=0)
				
				echo('</div>');//end div .form-right .pagination
				
			echo('</div>');//end div .row	
			
		}else{
			echo("Vous ne disposez d'aucune campagne correspondante &agrave; cette recherche");
		}//end else if global count 	if($total_count_results!=0)
	
	}
?>