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
		
		$email_id						= mysql_escape_string($_POST['email_id']);
		
	
		
		if(!empty($f_search)){
			
			/*$search_sql_where		=" AND (
										m_seg.name like '".$f_search."'
										)
									";*/	

			$search_sql_where		=" AND (
										m_base_emails.email like '".$f_search."%'
										)
									";								
			
			$search_sql_order_by	=  "order by ((m_base_emails.email*0.8)) DESC";
			
			
		}else{
			$search_sql_order_by	=  "order by m_b_e_operations.date_insert DESC";
		}
		
		//The switch case for the Order in the table !
		switch($table_order){
		
			//Date Insert
			case 'date_insert_asc':
				$search_sql_order_by	=  "order by m_b_e_operations.date_insert ASC";
			break;
			case 'date_insert_desc':
				$search_sql_order_by	=  "order by m_b_e_operations.date_insert DESC";
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
			
			//Disable source
			case 'segment_name_asc':
				$search_sql_order_by	=  "order by m_segment.name ASC";
			break;
			case 'segment_name_desc':
				$search_sql_order_by	=  "order by m_segment.name DESC";
			break;
			
			//Fitred
			case 'filtred_asc':
				$search_sql_order_by	=  "order by m_b_e_operations.email_etat ASC";
			break;
			case 'filtred_desc':
				$search_sql_order_by	=  "order by m_b_e_operations.email_etat DESC";
			break;
			
			
			default :
				$search_sql_order_by	=  "order by m_b_e_operations.date_insert DESC";
				
				//To use in the Table Header
				$table_order			= "date_insert_desc";
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
	
		
		$res_get_base_emails_detail_count_query	= "SELECT 
														count(*) c													
													FROM
															marketing_base_emails_operations AS m_b_e_operations 
																INNER JOIN marketing_base_emails AS m_base_emails ON m_b_e_operations.id_email=m_base_emails.id
																LEFT JOIN marketing_campaigns AS m_campaigns ON m_b_e_operations.id_campaign=m_campaigns.id 
																LEFT JOIN marketing_messages AS m_messages ON m_messages.id=m_campaigns.id_message
																LEFT JOIN marketing_segment AS m_segment ON m_messages.id_segment=m_segment.id 

													WHERE 
														m_base_emails.id=".$email_id."
											 
													".$search_sql_where."
														
													".$search_sql_order_by."";
	
	
		$res_get_base_emails_detail_count = $db->query($res_get_base_emails_detail_count_query, __FILE__, __LINE__);
		
		$content_get_base_emails_detail_count	= $db->fetchAssoc($res_get_base_emails_detail_count);
		$total_count_results		= $content_get_base_emails_detail_count['c'];
	
	
		if($total_count_results!=0){
			$res_get_base_emails_detail_query	= "SELECT 
														m_b_e_operations.date_insert, 
														m_b_e_operations.email_etat, 

														m_campaigns.name AS c_name, 
														m_messages.name AS m_name, 
														m_segment.name	AS s_name 
														
													FROM
															marketing_base_emails_operations AS m_b_e_operations 
																INNER JOIN marketing_base_emails AS m_base_emails ON m_b_e_operations.id_email=m_base_emails.id
																LEFT JOIN marketing_campaigns AS m_campaigns ON m_b_e_operations.id_campaign=m_campaigns.id 
																LEFT JOIN marketing_messages AS m_messages ON m_messages.id=m_campaigns.id_message
																LEFT JOIN marketing_segment AS m_segment ON m_messages.id_segment=m_segment.id 

													WHERE 
														m_base_emails.id=".$email_id."
														
													".$search_sql_where."
													
													".$search_sql_order_by."  
													".$query_suite_param_limit_ready."";	
										
			$res_get_base_emails_detail = $db->query($res_get_base_emails_detail_query, __FILE__, __LINE__);
		
			//echo('We got segments => '.$total_count_results.' * '.mysql_num_rows($res_get_base_emails_detail));
			
			echo('<div id="page_list_selector_container" style="padding: 25px 0 15px 10px; width:40%; float:left;">');

				echo('<div id="page_list_selector_text">');
					echo('Emails par page ');
				echo('</div>');//end div #page_list_selector_text
				
				echo('<div id="page_list_selector_select">');
				
					echo('<select id="f_select_p" onchange="base_emails_detail_get_more_now()">');
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
				echo('<div style="color:#52BFEA; float:left; padding-top: 2px;">'.$total_count_results.' Op&eacute;ration');
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
										echo('<div class="disabled"><a href="javascript:base_emails_detail_load_other_page(\''.$precedent.'\')">< Prec.</a></div>'); 
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
								echo('<a title="page num&eacute;ro 1" href="javascript:base_emails_detail_load_other_page(\'1\')" class="pgnt_nmbr"> 1 </a><span style="float:left; padding: 0px 3px 0px 3px;">..</span>');
							}

							$count_local_stop=$page+4;

							while( ($count_pagination<=$nb_pages) && ($count_local<$count_local_stop) ){
								if($count_pagination==$page){
									echo('<div class="disabled">&nbsp;'.$count_pagination.'&nbsp;</div>');
								}else{
										echo('<a title="page num&eacute;ro '.$count_pagination.'" href="javascript:base_emails_detail_load_other_page(\''.$count_pagination.'\')" class="pgnt_nmbr"> '.$count_pagination.' </a>');	
								}
								$count_pagination++;
								$count_local++;
							}

							if(($count_pagination <=$nb_pages) ){ //pour ne pas afficher suivant si on est dans la derniere page	
									echo('<span style="float:left; padding: 0px 3px 0px 3px;">..</span><a title="page num&eacute;ro '.$nb_pages.'" href="javascript:base_emails_detail_load_other_page(\''.$nb_pages.'\')" class="pgnt_nmbr"> '.$nb_pages.' </a>');
							}
							
							//echo('] ');
							if($page<$nb_pages ){//partir a la derniere page (30) si on a les resultats qui depasse 300 
								$suivant = $page +1;
									echo('<a title="page num&eacute;ro '.$suivant.'" href="javascript:base_emails_detail_load_other_page(\''.$suivant.'\')" style="text-decoration:none;" class="pgnt_nmbr">&nbsp;Suiv.&nbsp;</a>');
							}
						}
						
						// Fin pagination
						
					
				}//end if(strcmp($f_pp,'tout')!=0)
			
			echo('</div>');//end div .form-right .pagination
				
			echo('<div class="base_emails table-responsive">');
				echo('<table class="table" style="width:100%;">');
					echo('<tr>');
						echo('<th style="width: 20%; cursor:pointer;" ');
						if(strcmp($table_order,'date_insert_asc')==0){
							echo(' onclick="base_emails_detail_list_order_by(\'date_insert_desc\');"> <i class="fa fa-level-up"></i>');
						}else if(strcmp($table_order,'date_insert_desc')==0){
							echo(' onclick="base_emails_detail_list_order_by(\'date_insert_asc\');">  <i class="fa fa-level-down"></i>');
						}else{
							echo(' onclick="base_emails_detail_list_order_by(\'date_insert_desc\');">');
						}
						
							echo('Date d\'envoi');
						echo('</th>');
						
						echo('<th style="width: 25%; cursor:pointer;" ');
						if(strcmp($table_order,'campaign_name_asc')==0){
							echo(' onclick="base_emails_detail_list_order_by(\'campaign_name_desc\');"> <i class="fa fa-level-up"></i>');
						}else if(strcmp($table_order,'campaign_name_desc')==0){
							echo(' onclick="base_emails_detail_list_order_by(\'campaign_name_asc\');">  <i class="fa fa-level-down"></i>');
						}else{
							echo(' onclick="base_emails_detail_list_order_by(\'campaign_name_desc\');">');
						}
						
							echo('Campagne');
						echo('</th>');
						
						echo('<th style="width: 20%; cursor:pointer;" ');
						if(strcmp($table_order,'message_name_asc')==0){
							echo(' onclick="base_emails_detail_list_order_by(\'message_name_desc\');"> <i class="fa fa-level-up"></i>');
						}else if(strcmp($table_order,'message_name_desc')==0){
							echo(' onclick="base_emails_detail_list_order_by(\'message_name_asc\');">  <i class="fa fa-level-down"></i>');
						}else{
							echo(' onclick="base_emails_detail_list_order_by(\'message_name_desc\');">');
						}
						
							echo('Message');
						echo('</th>');
						
						echo('<th style="width: 20%; cursor:pointer;" ');
						if(strcmp($table_order,'segment_name_asc')==0){
							echo(' onclick="base_emails_detail_list_order_by(\'segment_name_desc\');"> <i class="fa fa-level-up"></i>');
						}else if(strcmp($table_order,'segment_name_desc')==0){
							echo(' onclick="base_emails_detail_list_order_by(\'segment_name_asc\');">  <i class="fa fa-level-down"></i>');
						}else{
							echo(' onclick="base_emails_detail_list_order_by(\'segment_name_desc\');">');
						}
						
							echo('Segment');
						echo('</th>');
						
						echo('<th style="width: 15%; cursor:pointer;" ');
						if(strcmp($table_order,'filtred_asc')==0){
							echo(' onclick="base_emails_detail_list_order_by(\'filtred_desc\');"> <i class="fa fa-level-up"></i>');
						}else if(strcmp($table_order,'filtred_desc')==0){
							echo(' onclick="base_emails_detail_list_order_by(\'filtred_asc\');">  <i class="fa fa-level-down"></i>');
						}else{
							echo(' onclick="base_emails_detail_list_order_by(\'filtred_desc\');">');
						}
						
							echo('Filtr&eacute;e ?');
						echo('</th>');

						
					echo('</tr>');
		
				$modulo_local_loop	= 0;
				$modulo_local_class	= 'alt';
				
				while($content_get_base_emails_detail	= $db->fetchAssoc($res_get_base_emails_detail)){
				
					//Calculating modulo to make a difference between the table rows !!
					if($modulo_local_loop%2){
						$modulo_local_class	= 'alt';
					}else{
						$modulo_local_class	= '';
					}
					
					
					//$row_link	= 'segments-edit.php?id='.$content_get_base_emails_detail['id'];
					
					
					//If the product is Pending the click link will be => Show in front => Blank
					//ELse go to Edit page => Self
					
					
					
					echo('<tr class="rs '.$modulo_local_class.'" >');
						echo('<td class="valign">');
							if(!empty($content_get_base_emails_detail['date_insert']) && strcmp($content_get_base_emails_detail['date_insert'],"NULL")!=0 && strcmp($content_get_base_emails_detail['date_insert'],"0000-00-00 00:00:00")!=0){
								echo(date('d/m/Y H:i:s', strtotime($content_get_base_emails_detail['date_insert'])));
							}else{
								echo(' - ');
							}
						echo('</td>');
						
						echo('<td class="valign">');
							echo($content_get_base_emails_detail['c_name']);
						echo('</td>');
						
						echo('<td class="valign">');
							echo($content_get_base_emails_detail['m_name']);
						echo('</td>');
						
						echo('<td class="valign">');
							echo($content_get_base_emails_detail['s_name']);
						echo('</td>');
						
						echo('<td class="valign">');
							if(strcmp($content_get_base_emails_detail['email_etat'],'ok')==0){
								echo('Non');
							}else{
								echo('Oui');
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
				echo('<div class="base_emails-bottom-pagination pagination prdct">');
					echo('<div style="color:#52BFEA; float:left; padding-top: 2px;">'.$total_count_results.' Op&eacute;ration');
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
											echo('<div class="disabled"><a href="javascript:base_emails_detail_load_other_page(\''.$precedent.'\')">< Prec.</a></div>'); 
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
									echo('<a title="page num&eacute;ro 1" href="javascript:base_emails_detail_load_other_page(\'1\')" class="pgnt_nmbr"> 1 </a><span style="float:left; padding: 0px 3px 0px 3px;">..</span>');
								}

								$count_local_stop=$page+4;

								while( ($count_pagination<=$nb_pages) && ($count_local<$count_local_stop) ){
									if($count_pagination==$page){
										echo('<div class="disabled">&nbsp;'.$count_pagination.'&nbsp;</div>');
									}else{
											echo('<a title="page num&eacute;ro '.$count_pagination.'" href="javascript:base_emails_detail_load_other_page(\''.$count_pagination.'\')" class="pgnt_nmbr"> '.$count_pagination.' </a>');	
									}
									$count_pagination++;
									$count_local++;
								}

								if(($count_pagination <=$nb_pages) ){ //pour ne pas afficher suivant si on est dans la derniere page	
										echo('<span style="float:left; padding: 0px 3px 0px 3px;">..</span><a title="page num&eacute;ro '.$nb_pages.'" href="javascript:base_emails_detail_load_other_page(\''.$nb_pages.'\')" class="pgnt_nmbr"> '.$nb_pages.' </a>');
								}
								
								//echo('] ');
								if($page<$nb_pages ){//partir a la derniere page (30) si on a les resultats qui depasse 300 
									$suivant = $page +1;
										echo('<a title="page num&eacute;ro '.$suivant.'" href="javascript:base_emails_detail_load_other_page(\''.$suivant.'\')" style="text-decoration:none;" class="pgnt_nmbr">&nbsp;Suiv.&nbsp;</a>');
								}
							}
							
							// Fin pagination
							
						
					}//end if(strcmp($f_pp,'tout')!=0)
				
				echo('</div>');//end div .form-right .pagination
				
			echo('</div>');//end div .row	
			
		}else{
			echo("Cette adresse Email n'a aucune op&eacute;ration");
		}//end else if global count 	if($total_count_results!=0)
	
	}
?>