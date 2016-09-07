<?php	
	require_once('extranet_v3_functions.php'); 
	
	
	if(!empty($_SESSION['extranet_user_id'])){
	
		//Getting params
		$f_ps							= mysql_escape_string($_POST['f_ps']);
		$f_pp							= mysql_escape_string($_POST['f_pp']);
		

		if(strcmp($f_pp,'tout')!=0){
		
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
		}else{
		
		}
		$res_get_invoices_count_query	= "SELECT
												count(i.rid) c 
											FROM
												invoice i 
													INNER JOIN  advertisers a ON a.client_id=i.client_id

											WHERE
												a.id=".$_SESSION['extranet_user_id']." ";
									
		$res_get_invoices_count = $db->query($res_get_invoices_count_query, __FILE__, __LINE__);
		
		$content_get_invoices_count	= $db->fetchAssoc($res_get_invoices_count);
		$total_count_results		= $content_get_invoices_count['c'];
		
		
		
		if($total_count_results!=0){
			$res_get_invoices_query	= "SELECT 
											i.rid, i.type,
											i.issued, i.due_date,
											i.total_ttc, i.web_id

										FROM
											invoice i 
												INNER JOIN  advertisers a ON a.client_id=i.client_id

										WHERE
											a.id=".$_SESSION['extranet_user_id']."  
										
										ORDER BY i.issued DESC	
										
										".$query_suite_param_limit_ready." ";	
									
			$res_get_invoices = $db->query($res_get_invoices_query, __FILE__, __LINE__);
		
			//echo('We got invoices => '.$total_count_results.' * '.mysql_num_rows($res_get_invoices));
			
			echo('<div id="page_list_selector_container" style="padding: 25px 0 15px 10px; width:40%; float:left;">');

				echo('<div id="page_list_selector_text">');
					echo('Factures par page ');
				echo('</div>');//end div #page_list_selector_text
				
				echo('<div id="page_list_selector_select">');
				
					echo('<select id="f_select_p" onchange="invoices_get_more_now()">');
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
			echo('<div class="pagination invoices-top-pagination">');
				echo('<div style="color:#52BFEA; float:left; padding-top: 2px;">'.$total_count_results.' Facture');
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
										echo('<div class="disabled"><a href="javascript:invoices_load_other_page(\''.$precedent.'\')">< Prec.</a></div>'); 
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
								echo('<a title="page num&eacute;ro 1" href="javascript:invoices_load_other_page(\'1\')" class="pgnt_nmbr"> 1 </a><span style="float:left; padding: 0px 3px 0px 3px;">..</span>');
							}

							$count_local_stop=$page+4;

							while( ($count_pagination<=$nb_pages) && ($count_local<$count_local_stop) ){
								if($count_pagination==$page){
									echo('<div class="disabled">&nbsp;'.$count_pagination.'&nbsp;</div>');
								}else{
										echo('<a title="page num&eacute;ro '.$count_pagination.'" href="javascript:invoices_load_other_page(\''.$count_pagination.'\')" class="pgnt_nmbr"> '.$count_pagination.' </a>');	
								}
								$count_pagination++;
								$count_local++;
							}

							if(($count_pagination <=$nb_pages) ){ //pour ne pas afficher suivant si on est dans la derniere page	
									echo('<span style="float:left; padding: 0px 3px 0px 3px;">..</span><a title="page num&eacute;ro '.$nb_pages.'" href="javascript:invoices_load_other_page(\''.$nb_pages.'\')" class="pgnt_nmbr"> '.$nb_pages.' </a>');
							}
							
							//echo('] ');
							if($page<$nb_pages ){//partir a la derniere page (30) si on a les resultats qui depasse 300 
								$suivant = $page +1;
									echo('<a title="page num&eacute;ro '.$suivant.'" href="javascript:invoices_load_other_page(\''.$suivant.'\')" style="text-decoration:none;" class="pgnt_nmbr">&nbsp;Suiv.&nbsp;</a>');
							}
						}
						
						// Fin pagination
						
					
				}//end if(strcmp($f_pp,'tout')!=0)
			
			echo('</div>');//end div .form-right .pagination
				
				
			
			
			echo('<div class="invoices table-responsive">');
				echo('<table class="table">');
					echo('<tr>');
						echo('<th style="width: 15%;">');
							echo('Num&eacute;ro');
						echo('</th>');
						
						echo('<th style="width: 20%;">');
							echo('Type');
						echo('</th>');
						
						echo('<th style="width: 30%;">');
							echo('Date');
						echo('</th>');
						
						echo('<th style="width: 15%;">');
							echo('&Eacute;ch&eacute;ance');
						echo('</th>');
						
						echo('<th style="width: 15%;">');
							echo('Montant');
						echo('</th>');
							
						echo('<th style="width: 8%;">');
							echo('T&eacute;l&eacute;charger');
						echo('</th>');
						
					echo('</tr>');
		
				$modulo_local_loop	= 0;
				$modulo_local_class	= 'alt';
			
				while($content_get_invoices	= $db->fetchAssoc($res_get_invoices)){
				
					//Calculating modulo to make a difference between the table rows !!
					if($modulo_local_loop%2){
						$modulo_local_class	= 'alt';
					}else{
						$modulo_local_class	= '';
					}
				
					$row_link	= '';
					if(strcmp($content_get_invoices['type'],'0')==0){
						$row_link = URL.'pdf/facture/'.$content_get_invoices['web_id'];
					}else{
						$row_link = URL.'pdf/avoir/'.$content_get_invoices['web_id'];
					}
				
					echo('<tr class="rs '.$modulo_local_class.'" >');
						echo('<td class="valign a-display-block">');
							echo '<a href="'.$row_link.'" target="_blank">'.$content_get_invoices['rid'].'</a>';
						echo('</td>');
							
						echo('<td class="valign a-display-block">');
							if(strcmp($content_get_invoices['type'],'1')==0){
								echo('<a href="'.$row_link.'" target="_blank">Avoir</a>');
							}else{
								echo('<a href="'.$row_link.'" target="_blank">Facture</a>');
							}
						echo('</td>');
						
						echo('<td class="valign a-display-block">');
							echo ('<a href="'.$row_link.'" target="_blank">'.date('d/m/Y H:i:s',$content_get_invoices['issued']).'</a>');
						echo('</td>');
						
						echo('<td class="valign a-display-block">');
							//If it's a Invoice
							if(strcmp($content_get_invoices['type'],'0')==0){
								echo '<a href="'.$row_link.'" target="_blank">'.date('d/m/Y H:i:s',$content_get_invoices['due_date']).'</a>';
							}else{
								echo('<a href="'.$row_link.'" target="_blank"> - </a>');
							}
						echo('</td>');
						
						echo('<td class="valign a-display-block">');
								//echo $content_get_invoices['total_ttc'].' &euro;';
								echo '<a href="'.$row_link.'" target="_blank">'.number_format($content_get_invoices['total_ttc'], 2, ',', ' ').' &euro;</a>';
						echo('</td>');	
						
						echo('<td class="valign a-display-block">');
					
							//echo('<a href="'.$row_link.'" target="_blank">');	
							echo('<a href="'.$row_link.'" target="_blank">');
								echo('<i class="fa fa-file-pdf-o" title="T&eacute;l&eacute;charger" alt="T&eacute;l&eacute;charger"></i>');
							echo('</a>');
							
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
					echo('&nbsp;');
				echo('</div>');
				*/
				
				//Start writing pagination
				echo('<div class="invoices-top-pagination pagination prdct">');
					echo('<div style="color:#52BFEA; float:left; padding-top: 2px;">'.$total_count_results.' Facture');
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
											echo('<div class="disabled"><a href="javascript:invoices_load_other_page(\''.$precedent.'\')">< Prec.</a></div>'); 
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
									echo('<a title="page num&eacute;ro 1" href="javascript:invoices_load_other_page(\'1\')" class="pgnt_nmbr"> 1 </a><span style="float:left; padding: 0px 3px 0px 3px;">..</span>');
								}

								$count_local_stop=$page+4;

								while( ($count_pagination<=$nb_pages) && ($count_local<$count_local_stop) ){
									if($count_pagination==$page){
										echo('<div class="disabled">&nbsp;'.$count_pagination.'&nbsp;</div>');
									}else{
											echo('<a title="page num&eacute;ro '.$count_pagination.'" href="javascript:invoices_load_other_page(\''.$count_pagination.'\')" class="pgnt_nmbr"> '.$count_pagination.' </a>');	
									}
									$count_pagination++;
									$count_local++;
								}

								if(($count_pagination <=$nb_pages) ){ //pour ne pas afficher suivant si on est dans la derniere page	
										echo('<span style="float:left; padding: 0px 3px 0px 3px;">..</span><a title="page num&eacute;ro '.$nb_pages.'" href="javascript:invoices_load_other_page(\''.$nb_pages.'\')" class="pgnt_nmbr"> '.$nb_pages.' </a>');
								}
								
								//echo('] ');
								if($page<$nb_pages ){//partir a la derniere page (30) si on a les resultats qui depasse 300 
									$suivant = $page +1;
										echo('<a title="page num&eacute;ro '.$suivant.'" href="javascript:invoices_load_other_page(\''.$suivant.'\')" style="text-decoration:none;" class="pgnt_nmbr">&nbsp;Suiv.&nbsp;</a>');
								}
							}
							
							// Fin pagination
							
						
					}//end if(strcmp($f_pp,'tout')!=0)
				
				echo('</div>');//end div .form-right .pagination
				
			echo('</div>');//end div .row	
			
		}else{
			echo("Aucune information &agrave; afficher !");
		}//end else if global count 	if($total_count_results!=0)

	}else{
		echo('<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;<strong><a href="login.html">Merci de vous reconnecter.</a></strong>');
	}
?>