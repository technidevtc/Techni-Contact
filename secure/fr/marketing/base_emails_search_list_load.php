<?php

	require_once('functions.php'); 

	if(!empty($_SESSION['marketing_user_id'])){
	
		//Call files to get the user's roles (Navigation)
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
										m_base_emails.email like '".$f_search."%'
										)
									";								
			
			$search_sql_order_by	=  "order by ((m_base_emails.email*0.8)) DESC";
			
			
		}else{
			$search_sql_order_by	=  "order by m_base_emails.id DESC";
		}
		
		//The switch case for the Order in the table !
		switch($table_order){
		
			//ID
			case 'id_asc':
				$search_sql_order_by	=  "order by m_base_emails.id ASC";
			break;
			case 'id_desc':
				$search_sql_order_by	=  "order by m_base_emails.id DESC";
			break;
			
			//Message Name
			case 'email_asc':
				$search_sql_order_by	=  "order by m_base_emails.email ASC";
			break;
			case 'email_desc':
				$search_sql_order_by	=  "order by m_base_emails.email DESC";
			break;
			
			//Etat
			case 'etat_asc':
				$search_sql_order_by	=  "order by m_base_emails.email ASC";
			break;
			case 'etat_desc':
				$search_sql_order_by	=  "order by m_base_emails.email DESC";
			break;
			
			//Disable source
			case 'disable_source_asc':
				$search_sql_order_by	=  "order by m_base_emails.disable_source ASC";
			break;
			case 'disable_source_desc':
				$search_sql_order_by	=  "order by m_base_emails.disable_source DESC";
			break;
			
			//Date Insert
			case 'insert_date_asc':
				$search_sql_order_by	=  "order by m_base_emails.date_insert ASC";
			break;
			case 'insert_date_desc':
				$search_sql_order_by	=  "order by m_base_emails.date_insert DESC";
			break;
			
			//Date Edit
			case 'edit_date_asc':
				$search_sql_order_by	=  "order by m_base_emails.date_last_edit ASC";
			break;
			case 'edit_date_desc':
				$search_sql_order_by	=  "order by m_base_emails.date_last_edit DESC";
			break;
			
			
			default :
				$search_sql_order_by	=  "order by m_base_emails.id DESC";
				
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
	
		
		$res_get_base_emails_count_query	= "SELECT
												count(*) AS c
												
											FROM 
												marketing_base_emails AS m_base_emails
											 
											".$search_sql_where."
											
											".$search_sql_order_by."";
	
	
		$res_get_base_emails_count = $db->query($res_get_base_emails_count_query, __FILE__, __LINE__);
		
		$content_get_base_emails_count	= $db->fetchAssoc($res_get_base_emails_count);
		$total_count_results		= $content_get_base_emails_count['c'];
	
	
		if($total_count_results!=0){
			$res_get_base_emails_query	= "SELECT
												m_base_emails.id, 
												m_base_emails.email,
												m_base_emails.etat, 
												m_base_emails.date_insert, 
												m_base_emails.date_last_edit, 
												m_base_emails.disable_source 
											
											FROM 
												marketing_base_emails AS m_base_emails

										".$search_sql_where."
										
										".$search_sql_order_by."  
										".$query_suite_param_limit_ready."";	
									
			$res_get_base_emails = $db->query($res_get_base_emails_query, __FILE__, __LINE__);
		
			//echo('We got segments => '.$total_count_results.' * '.mysql_num_rows($res_get_base_emails));
			
			echo('<div id="page_list_selector_container" style="padding: 25px 0 15px 10px; width:40%; float:left;">');

				echo('<div id="page_list_selector_text">');
					echo('Emails par page ');
				echo('</div>');//end div #page_list_selector_text
				
				echo('<div id="page_list_selector_select">');
				
					echo('<select id="f_select_p" onchange="base_emails_get_more_now()">');
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
			echo('<div class="pagination base_emails-top-pagination">');
				echo('<div style="color:#52BFEA; float:left; padding-top: 2px;">'.$total_count_results.' Email');
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
										echo('<div class="disabled"><a href="javascript:base_emails_load_other_page(\''.$precedent.'\')">< Prec.</a></div>'); 
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
								echo('<a title="page num&eacute;ro 1" href="javascript:base_emails_load_other_page(\'1\')" class="pgnt_nmbr"> 1 </a><span style="float:left; padding: 0px 3px 0px 3px;">..</span>');
							}

							$count_local_stop=$page+4;

							while( ($count_pagination<=$nb_pages) && ($count_local<$count_local_stop) ){
								if($count_pagination==$page){
									echo('<div class="disabled">&nbsp;'.$count_pagination.'&nbsp;</div>');
								}else{
										echo('<a title="page num&eacute;ro '.$count_pagination.'" href="javascript:base_emails_load_other_page(\''.$count_pagination.'\')" class="pgnt_nmbr"> '.$count_pagination.' </a>');	
								}
								$count_pagination++;
								$count_local++;
							}

							if(($count_pagination <=$nb_pages) ){ //pour ne pas afficher suivant si on est dans la derniere page	
									echo('<span style="float:left; padding: 0px 3px 0px 3px;">..</span><a title="page num&eacute;ro '.$nb_pages.'" href="javascript:base_emails_load_other_page(\''.$nb_pages.'\')" class="pgnt_nmbr"> '.$nb_pages.' </a>');
							}
							
							//echo('] ');
							if($page<$nb_pages ){//partir a la derniere page (30) si on a les resultats qui depasse 300 
								$suivant = $page +1;
									echo('<a title="page num&eacute;ro '.$suivant.'" href="javascript:base_emails_load_other_page(\''.$suivant.'\')" style="text-decoration:none;" class="pgnt_nmbr">&nbsp;Suiv.&nbsp;</a>');
							}
						}
						
						// Fin pagination
						
					
				}//end if(strcmp($f_pp,'tout')!=0)
			
			echo('</div>');//end div .form-right .pagination
				
			echo('<div class="base_emails table-responsive">');
				echo('<table class="table" style="width:100%;">');
					echo('<tr>');
						echo('<th style="width: 10%; cursor:pointer;" ');
						if(strcmp($table_order,'id_asc')==0){
							echo(' onclick="base_emails_list_order_by(\'id_desc\');"> <i class="fa fa-level-up"></i>');
						}else if(strcmp($table_order,'id_desc')==0){
							echo(' onclick="base_emails_list_order_by(\'id_asc\');">  <i class="fa fa-level-down"></i>');
						}else{
							echo(' onclick="base_emails_list_order_by(\'id_desc\');">');
						}
						
							echo('ID');
						echo('</th>');
						
						echo('<th style="width: 20%; cursor:pointer;" ');
						if(strcmp($table_order,'email_asc')==0){
							echo(' onclick="base_emails_list_order_by(\'email_desc\');"> <i class="fa fa-level-up"></i>');
						}else if(strcmp($table_order,'email_desc')==0){
							echo(' onclick="base_emails_list_order_by(\'email_asc\');">  <i class="fa fa-level-down"></i>');
						}else{
							echo(' onclick="base_emails_list_order_by(\'email_desc\');">');
						}
						
							echo('Email');
						echo('</th>');
						
						echo('<th style="width: 15%; cursor:pointer;" ');
						if(strcmp($table_order,'etat_asc')==0){
							echo(' onclick="base_emails_list_order_by(\'etat_desc\');"> <i class="fa fa-level-up"></i>');
						}else if(strcmp($table_order,'etat_desc')==0){
							echo(' onclick="base_emails_list_order_by(\'etat_asc\');">  <i class="fa fa-level-down"></i>');
						}else{
							echo(' onclick="base_emails_list_order_by(\'etat_desc\');">');
						}
						
							echo('Etat');
						echo('</th>');
						
						echo('<th style="width: 15%; cursor:pointer;" ');
						if(strcmp($table_order,'disable_source_asc')==0){
							echo(' onclick="base_emails_list_order_by(\'disable_source_desc\');"> <i class="fa fa-level-up"></i>');
						}else if(strcmp($table_order,'disable_source_desc')==0){
							echo(' onclick="base_emails_list_order_by(\'disable_source_asc\');">  <i class="fa fa-level-down"></i>');
						}else{
							echo(' onclick="base_emails_list_order_by(\'disable_source_desc\');">');
						}
						
							echo('D&eacute;sactivation');
						echo('</th>');
						
						echo('<th style="width: 15%; cursor:pointer;" ');
						if(strcmp($table_order,'insert_date_asc')==0){
							echo(' onclick="base_emails_list_order_by(\'insert_date_desc\');"> <i class="fa fa-level-up"></i>');
						}else if(strcmp($table_order,'insert_date_desc')==0){
							echo(' onclick="base_emails_list_order_by(\'insert_date_asc\');">  <i class="fa fa-level-down"></i>');
						}else{
							echo(' onclick="base_emails_list_order_by(\'insert_date_desc\');">');
						}
						
							echo('D. Ajout');
						echo('</th>');
						
						echo('<th style="width: 15%; cursor:pointer;" ');
						if(strcmp($table_order,'edit_date_asc')==0){
							echo(' onclick="base_emails_list_order_by(\'edit_date_desc\');"> <i class="fa fa-level-up"></i>');
						}else if(strcmp($table_order,'edit_date_desc')==0){
							echo(' onclick="base_emails_list_order_by(\'edit_date_asc\');">  <i class="fa fa-level-down"></i>');
						}else{
							echo(' onclick="base_emails_list_order_by(\'edit_date_desc\');">');
						}
						
							echo('D. Modification');
						echo('</th>');
						
						echo('<th style="width: 10%;">');
							echo('Actions');
						echo('</th>');
						
					echo('</tr>');
		
				$modulo_local_loop	= 0;
				$modulo_local_class	= 'alt';
				
				while($content_get_base_emails	= $db->fetchAssoc($res_get_base_emails)){
				
					//Calculating modulo to make a difference between the table rows !!
					if($modulo_local_loop%2){
						$modulo_local_class	= 'alt';
					}else{
						$modulo_local_class	= '';
					}
					
					
					//$row_link	= 'segments-edit.php?id='.$content_get_base_emails['id'];
					
					
					//If the product is Pending the click link will be => Show in front => Blank
					//ELse go to Edit page => Self
					
					
					
					echo('<tr class="rs '.$modulo_local_class.'" >');
						echo('<td class="valign">');
							echo($content_get_base_emails['id']);
						echo('</td>');
						
						echo('<td class="valign">');
							echo($content_get_base_emails['email']);
						echo('</td>');
						
						echo('<td class="valign">');
							if(strcmp($content_get_base_emails['etat'],'ok')==0){
								echo('<img src="ressources/images/icons/green_ok.png" alt="Activ&eacute;e" title="Activ&eacute;e" style="width:13px;" />');
							}else{
								echo('<img src="ressources/images/icons/cross.png" alt="D&eacute;sactiv&eacute;e" title="D&eacute;sactiv&eacute;e" style="width:13px;" />');
							}
						echo('</td>');
						
						echo('<td class="valign">');
							if(strcmp($content_get_base_emails['disable_source'],'human')==0){
								echo('Manuelle');
							}else if(strcmp($content_get_base_emails['disable_source'],'robot')==0){
								echo('Programme');
							}else{
								echo(' - ');
							}
						echo('</td>');
						
						echo('<td class="valign">');
							if(!empty($content_get_base_emails['date_insert']) && strcmp($content_get_base_emails['date_insert'],"NULL")!=0 && strcmp($content_get_base_emails['date_insert'],"0000-00-00 00:00:00")!=0){
								echo(date('d/m/Y H:i:s', strtotime($content_get_base_emails['date_insert'])));
							}else{
								echo(' - ');
							}
						echo('</td>');
						
						echo('<td class="valign">');
							if(!empty($content_get_base_emails['date_last_edit']) && strcmp($content_get_base_emails['date_last_edit'],"NULL")!=0 && strcmp($content_get_base_emails['date_last_edit'],"0000-00-00 00:00:00")!=0){
								echo(date('d/m/Y H:i:s', strtotime($content_get_base_emails['date_last_edit'])));
							}else{
								echo(' - ');
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
								
								//$content_get_base_emails['id'];
								
								
								//Edition
								if(strpos($content_get_user_page_permissions['content'],'#17#')!==FALSE){
									
									echo('<a href="/fr/marketing/fiche-email-base-email.php?id='.$content_get_base_emails['id'].'" title="Voir"><i class="fa fa-eye"></i></a>');
									echo('&nbsp;');
								}
								
								//Suppression
								
								
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
				echo('<div class="base_emails-bottom-pagination pagination prdct">');
					echo('<div style="color:#52BFEA; float:left; padding-top: 2px;">'.$total_count_results.' Email');
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
											echo('<div class="disabled"><a href="javascript:base_emails_load_other_page(\''.$precedent.'\')">< Prec.</a></div>'); 
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
									echo('<a title="page num&eacute;ro 1" href="javascript:base_emails_load_other_page(\'1\')" class="pgnt_nmbr"> 1 </a><span style="float:left; padding: 0px 3px 0px 3px;">..</span>');
								}

								$count_local_stop=$page+4;

								while( ($count_pagination<=$nb_pages) && ($count_local<$count_local_stop) ){
									if($count_pagination==$page){
										echo('<div class="disabled">&nbsp;'.$count_pagination.'&nbsp;</div>');
									}else{
											echo('<a title="page num&eacute;ro '.$count_pagination.'" href="javascript:base_emails_load_other_page(\''.$count_pagination.'\')" class="pgnt_nmbr"> '.$count_pagination.' </a>');	
									}
									$count_pagination++;
									$count_local++;
								}

								if(($count_pagination <=$nb_pages) ){ //pour ne pas afficher suivant si on est dans la derniere page	
										echo('<span style="float:left; padding: 0px 3px 0px 3px;">..</span><a title="page num&eacute;ro '.$nb_pages.'" href="javascript:base_emails_load_other_page(\''.$nb_pages.'\')" class="pgnt_nmbr"> '.$nb_pages.' </a>');
								}
								
								//echo('] ');
								if($page<$nb_pages ){//partir a la derniere page (30) si on a les resultats qui depasse 300 
									$suivant = $page +1;
										echo('<a title="page num&eacute;ro '.$suivant.'" href="javascript:base_emails_load_other_page(\''.$suivant.'\')" style="text-decoration:none;" class="pgnt_nmbr">&nbsp;Suiv.&nbsp;</a>');
								}
							}
							
							// Fin pagination
							
						
					}//end if(strcmp($f_pp,'tout')!=0)
				
				echo('</div>');//end div .form-right .pagination
				
			echo('</div>');//end div .row	
			
		}else{
			echo("Vous ne disposez d'aucune adresse Email correspondante &agrave; cette recherche");
		}//end else if global count 	if($total_count_results!=0)
	
	}
?>