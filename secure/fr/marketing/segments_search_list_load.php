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

		$f_type							= mysql_escape_string($_POST['f_type']);
		
		$table_order					= mysql_escape_string($_POST['table_order']);
		
	
		if(!empty($f_type)){
			$f_type_params					= " AND m_seg.type='".$f_type."' ";
		}
		
		if(!empty($f_search)){
			
			/*$search_sql_fileds		= ", MATCH (m_seg.name) AGAINST (\"".$f_search."\" IN BOOLEAN 	MODE) as name_score";
										
			$search_sql_where		=" AND (
										MATCH (m_seg.name) AGAINST (\"".$f_search."\" IN BOOLEAN MODE)
										)
									";
			*/
			
			/*$search_sql_where		=" AND (
										m_seg.name like '".$f_search."'
										)
									";*/	

			$search_sql_where		=" AND (
										m_seg.name like '".$f_search."%'
										)
									";								
			
			$search_sql_order_by	=  "order by ((name_score*0.8)) DESC";
			
			
		}else{
			$search_sql_order_by	=  "order by date_creation DESC";
		}
		
		//The switch case for the Order in the table !
		switch($table_order){
		
			//ID
			case 'id_asc':
				$search_sql_order_by	=  "order by m_seg.id ASC";
			break;
			case 'id_desc':
				$search_sql_order_by	=  "order by m_seg.id DESC";
			break;
			
			//Creation Date
			case 'dcreate_asc':
				$search_sql_order_by	=  "order by m_seg.date_creation ASC";
			break;
			case 'dcreate_desc':
				$search_sql_order_by	=  "order by m_seg.date_creation DESC";
			break;
		
			//Name
			case 'name_asc':
				$search_sql_order_by	=  "order by m_seg.name ASC";
			break;
			case 'name_desc':
				$search_sql_order_by	=  "order by m_seg.name DESC";
			break;
			
			//NbCible
			case 'nb_cibles_asc':
				$search_sql_order_by	=  "order by m_seg.results_count ASC";
			break;
			case 'nb_cibles_desc':
				$search_sql_order_by	=  "order by m_seg.results_count DESC";
			break;
			
			//Type
			case 'type_asc':
				$search_sql_order_by	=  "order by m_seg.type ASC";
			break;
			case 'type_desc':
				$search_sql_order_by	=  "order by m_seg.type DESC";
			break;
			
			//Update Date
			case 'dmaj_asc':
				$search_sql_order_by	=  "order by m_seg.date_last_execution_start ASC";
			break;
			case 'dmaj_desc':
				$search_sql_order_by	=  "order by m_seg.date_last_execution_start DESC";
			break;
			
			default :
				$search_sql_order_by	=  "order by m_seg.date_creation DESC";
				
				//To use in the Table Header
				$table_order			= "dcreate_desc";
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
	
		
		$res_get_segments_count_query	= "SELECT
												count(*) AS c
												
											FROM
												marketing_segment m_seg, 
												marketing_tables m_tables
											WHERE
												m_seg.id_table=m_tables.id
											
											".$f_type_params." 
											".$search_sql_where."
											
											".$search_sql_order_by."";
	
	
		$res_get_segments_count = $db->query($res_get_segments_count_query, __FILE__, __LINE__);
		
		$content_get_segments_count	= $db->fetchAssoc($res_get_segments_count);
		$total_count_results		= $content_get_segments_count['c'];
	
	
		if($total_count_results!=0){
			$res_get_segments_query	= "SELECT 
											m_seg.id,
											m_seg.name,
											m_seg.type,
											m_seg.results_count,
											m_seg.date_creation,
											m_seg.date_last_execution_start,
											
											m_tables.id AS table_id,
											m_tables.name_fo AS table_name
											
										FROM
											marketing_segment m_seg, 
											marketing_tables m_tables
										WHERE
											m_seg.id_table=m_tables.id
										
										".$f_type_params." 
										".$search_sql_where."
										
										".$search_sql_order_by."  
										".$query_suite_param_limit_ready."";	
									
			$res_get_segments = $db->query($res_get_segments_query, __FILE__, __LINE__);
		
			//echo('We got segments => '.$total_count_results.' * '.mysql_num_rows($res_get_segments));
			
			echo('<div id="page_list_selector_container" style="padding: 25px 0 15px 10px; width:40%; float:left;">');

				echo('<div id="page_list_selector_text">');
					echo('Segments par page ');
				echo('</div>');//end div #page_list_selector_text
				
				echo('<div id="page_list_selector_select">');
				
					echo('<select id="f_select_p" onchange="segments_get_more_now()">');
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
			echo('<div class="pagination segments-top-pagination">');
				echo('<div style="color:#52BFEA; float:left; padding-top: 2px;">'.$total_count_results.' Segment');
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
										echo('<div class="disabled"><a href="javascript:segments_load_other_page(\''.$precedent.'\')">< Prec.</a></div>'); 
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
								echo('<a title="page num&eacute;ro 1" href="javascript:segments_load_other_page(\'1\')" class="pgnt_nmbr"> 1 </a><span style="float:left; padding: 0px 3px 0px 3px;">..</span>');
							}

							$count_local_stop=$page+4;

							while( ($count_pagination<=$nb_pages) && ($count_local<$count_local_stop) ){
								if($count_pagination==$page){
									echo('<div class="disabled">&nbsp;'.$count_pagination.'&nbsp;</div>');
								}else{
										echo('<a title="page num&eacute;ro '.$count_pagination.'" href="javascript:segments_load_other_page(\''.$count_pagination.'\')" class="pgnt_nmbr"> '.$count_pagination.' </a>');	
								}
								$count_pagination++;
								$count_local++;
							}

							if(($count_pagination <=$nb_pages) ){ //pour ne pas afficher suivant si on est dans la derniere page	
									echo('<span style="float:left; padding: 0px 3px 0px 3px;">..</span><a title="page num&eacute;ro '.$nb_pages.'" href="javascript:segments_load_other_page(\''.$nb_pages.'\')" class="pgnt_nmbr"> '.$nb_pages.' </a>');
							}
							
							//echo('] ');
							if($page<$nb_pages ){//partir a la derniere page (30) si on a les resultats qui depasse 300 
								$suivant = $page +1;
									echo('<a title="page num&eacute;ro '.$suivant.'" href="javascript:segments_load_other_page(\''.$suivant.'\')" style="text-decoration:none;" class="pgnt_nmbr">&nbsp;Suiv.&nbsp;</a>');
							}
						}
						
						// Fin pagination
						
					
				}//end if(strcmp($f_pp,'tout')!=0)
			
			echo('</div>');//end div .form-right .pagination
				
			echo('<div class="segments table-responsive">');
				echo('<table class="table" style="width:100%;">');
					echo('<tr>');
						echo('<th style="width: 5%; cursor:pointer;" ');
						if(strcmp($table_order,'id_asc')==0){
							echo(' onclick="segment_list_order_by(\'id_desc\');"> <i class="fa fa-level-up"></i>');
						}else if(strcmp($table_order,'id_desc')==0){
							echo(' onclick="segment_list_order_by(\'id_asc\');">  <i class="fa fa-level-down"></i>');
						}else{
							echo(' onclick="segment_list_order_by(\'id_desc\');">');
						}
						
							echo('ID');
						echo('</th>');
						
						echo('<th style="width: 10%; cursor:pointer;" ');
						if(strcmp($table_order,'dcreate_asc')==0){
							echo(' onclick="segment_list_order_by(\'dcreate_desc\');"> <i class="fa fa-level-up"></i>');
						}else if(strcmp($table_order,'dcreate_desc')==0){
							echo(' onclick="segment_list_order_by(\'dcreate_asc\');">  <i class="fa fa-level-down"></i>');
						}else{
							echo(' onclick="segment_list_order_by(\'dcreate_desc\');">');
						}
						
							echo('Date cr&eacute;ation');
						echo('</th>');
						
						echo('<th style="width: 30%; cursor:pointer;" ');
						if(strcmp($table_order,'name_asc')==0){
							echo(' onclick="segment_list_order_by(\'name_desc\');"> <i class="fa fa-level-up"></i>');
						}else if(strcmp($table_order,'name_desc')==0){
							echo(' onclick="segment_list_order_by(\'name_asc\');">  <i class="fa fa-level-down"></i>');
						}else{
							echo(' onclick="segment_list_order_by(\'name_desc\');">');
						}
							echo('Nom');
						echo('</th>');
						
						echo('<th style="width: 15%; cursor:pointer;" ');
						if(strcmp($table_order,'nb_cibles_asc')==0){
							echo(' onclick="segment_list_order_by(\'nb_cibles_desc\');"> <i class="fa fa-level-up"></i>');
						}else if(strcmp($table_order,'nb_cibles_desc')==0){
							echo(' onclick="segment_list_order_by(\'nb_cibles_asc\');">  <i class="fa fa-level-down"></i>');
						}else{
							echo(' onclick="segment_list_order_by(\'nb_cibles_desc\');">');
						}
							echo('Nb cibles');
						echo('</th>');
						
						echo('<th style="width: 10%; cursor:pointer;" ');
						if(strcmp($table_order,'type_asc')==0){
							echo(' onclick="segment_list_order_by(\'type_desc\');"> <i class="fa fa-level-up"></i>');
						}else if(strcmp($table_order,'type_desc')==0){
							echo(' onclick="segment_list_order_by(\'type_asc\');">  <i class="fa fa-level-down"></i>');
						}else{
							echo(' onclick="segment_list_order_by(\'type_desc\');">');
						}
							echo('Type');
						echo('</th>');
						
						echo('<th style="width: 15%; cursor:pointer;" ');
						if(strcmp($table_order,'dmaj_asc')==0){
							echo(' onclick="segment_list_order_by(\'dmaj_desc\');"> <i class="fa fa-level-up"></i>');
						}else if(strcmp($table_order,'dmaj_desc')==0){
							echo(' onclick="segment_list_order_by(\'dmaj_asc\');">  <i class="fa fa-level-down"></i>');
						}else{
							echo(' onclick="segment_list_order_by(\'dmaj_desc\');">');
						}
							echo('Date MAJ');
						echo('</th>');
							
						echo('<th style="width: 15%;">');
							echo('Actions');
						echo('</th>');
						
					echo('</tr>');
		
				$modulo_local_loop	= 0;
				$modulo_local_class	= 'alt';
				
				while($content_get_segments	= $db->fetchAssoc($res_get_segments)){
				
					//Calculating modulo to make a difference between the table rows !!
					if($modulo_local_loop%2){
						$modulo_local_class	= 'alt';
					}else{
						$modulo_local_class	= '';
					}
					
					
					//$row_link	= 'segments-edit.php?id='.$content_get_segments['id'];
					
					
					//If the product is Pending the click link will be => Show in front => Blank
					//ELse go to Edit page => Self
					
					
					
					echo('<tr class="rs '.$modulo_local_class.'" >');
						echo('<td class="valign">');
							echo($content_get_segments['id']);
						echo('</td>');
							
						echo('<td class="valign">');
							echo(date('d/m/Y H:i:s', strtotime($content_get_segments['date_creation'])));
						echo('</td>');
						
						echo('<td class="valign" title="'.$content_get_segments['table_name'].'">');
							echo($content_get_segments['name']);
						echo('</td>');
						
						echo('<td class="valign">');
							echo($content_get_segments['results_count']);
						echo('</td>');
						
						echo('<td class="valign">');
							echo(ucfirst($content_get_segments['type']));
						echo('</td>');
						
						echo('<td class="valign">');
							if(strcmp($content_get_segments['date_last_execution_start'],'0000-00-00 00:00:00')==0){
								echo(' - ');
							}else{
								echo(date('d/m/Y H:i:s', strtotime($content_get_segments['date_last_execution_start'])));
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
								
								//$content_get_segments['id'];
								
								//Execution
								//Check the user's role action on page 
								//And check the user's role action on table
								//if(strcmp($content_get_segments['type'],'statique')==0){
									if(strpos($content_get_user_page_permissions['content'],'#7#')!==FALSE && strpos($content_get_user_tables_access_permissions['content'],'#'.$content_get_segments['table_id'].'#')!==FALSE){
										echo('<a href="javascript:void(0);" title="Rafra&icirc;chir" onclick="javascript:segment_refresh(\''.$content_get_segments['id'].'\');"><i class="fa fa-refresh"></i></a>');
										echo('&nbsp;');
									}
								//}else{
									//echo('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
								//}
								
								//Export
								if(strpos($content_get_user_page_permissions['content'],'#3#')!==FALSE && strpos($content_get_user_tables_export_permissions['content'],'#'.$content_get_segments['table_id'].'#')!==FALSE){
									echo('<a href="/fr/marketing/segments-export.php?id='.$content_get_segments['id'].'" title="Exporter" target="_blank"><i class="fa fa-download"></i></a>');
									echo('&nbsp;');
									echo('&nbsp;');
								}
								
								//Edition
								if(strpos($content_get_user_page_permissions['content'],'#6#')!==FALSE && strpos($content_get_user_tables_access_permissions['content'],'#'.$content_get_segments['table_id'].'#')!==FALSE){
									echo('<a href="/fr/marketing/segments-edit.php?id='.$content_get_segments['id'].'" title="Modifier"><i class="fa fa-pencil"></i></a>');
									echo('&nbsp;');
								}
								
								//Suppression
								if(strpos($content_get_user_page_permissions['content'],'#5#')!==FALSE && strpos($content_get_user_tables_access_permissions['content'],'#'.$content_get_segments['table_id'].'#')!==FALSE){
								
									echo('<a href="javascript:void(0);" onclick="javascript:segment_ask_delete_segment_display_modal(\''.$content_get_segments['id'].'\');" title="Supprimer"><i class="fa fa-trash-o"></i></a>');
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
				echo('<div class="segments-bottom-pagination pagination prdct">');
					echo('<div style="color:#52BFEA; float:left; padding-top: 2px;">'.$total_count_results.' Segment');
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
											echo('<div class="disabled"><a href="javascript:segments_load_other_page(\''.$precedent.'\')">< Prec.</a></div>'); 
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
									echo('<a title="page num&eacute;ro 1" href="javascript:segments_load_other_page(\'1\')" class="pgnt_nmbr"> 1 </a><span style="float:left; padding: 0px 3px 0px 3px;">..</span>');
								}

								$count_local_stop=$page+4;

								while( ($count_pagination<=$nb_pages) && ($count_local<$count_local_stop) ){
									if($count_pagination==$page){
										echo('<div class="disabled">&nbsp;'.$count_pagination.'&nbsp;</div>');
									}else{
											echo('<a title="page num&eacute;ro '.$count_pagination.'" href="javascript:segments_load_other_page(\''.$count_pagination.'\')" class="pgnt_nmbr"> '.$count_pagination.' </a>');	
									}
									$count_pagination++;
									$count_local++;
								}

								if(($count_pagination <=$nb_pages) ){ //pour ne pas afficher suivant si on est dans la derniere page	
										echo('<span style="float:left; padding: 0px 3px 0px 3px;">..</span><a title="page num&eacute;ro '.$nb_pages.'" href="javascript:segments_load_other_page(\''.$nb_pages.'\')" class="pgnt_nmbr"> '.$nb_pages.' </a>');
								}
								
								//echo('] ');
								if($page<$nb_pages ){//partir a la derniere page (30) si on a les resultats qui depasse 300 
									$suivant = $page +1;
										echo('<a title="page num&eacute;ro '.$suivant.'" href="javascript:segments_load_other_page(\''.$suivant.'\')" style="text-decoration:none;" class="pgnt_nmbr">&nbsp;Suiv.&nbsp;</a>');
								}
							}
							
							// Fin pagination
							
						
					}//end if(strcmp($f_pp,'tout')!=0)
				
				echo('</div>');//end div .form-right .pagination
				
			echo('</div>');//end div .row	
			
		}else{
			echo("Vous ne disposez d'aucun segment correspondant &agrave; cette recherche");
		}//end else if global count 	if($total_count_results!=0)
	
	}
?>