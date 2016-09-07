<?php	
	require_once('extranet_v3_functions.php'); 
	
	
	if(!empty($_SESSION['extranet_user_id'])){
	
		//Getting params
		$f_ps							= mysql_escape_string($_POST['f_ps']);
		$f_pp							= mysql_escape_string($_POST['f_pp']);
		$stats_products_search_hidden	= mysql_escape_string($_POST['stats_products_search_hidden']);
		
		$type							= mysql_escape_string($_POST['type']);
		$stats_interval_v1				= mysql_escape_string($_POST['stats_interval_v1']);
		$stats_interval_v2				= mysql_escape_string($_POST['stats_interval_v2']);
		$stats_simple_v1				= mysql_escape_string($_POST['stats_simple_v1']);

		/*
		echo('$f_ps: '.$f_ps.'<br />');
		echo('$f_pp: '.$f_pp.'<br />');
		echo('$stats_products_search_hidden: '.$stats_products_search_hidden.'<br />');
		echo('$type: '.$type.'<br />');
		echo('$stats_interval_v1: '.$stats_interval_v1.'<br />');
		echo('$stats_interval_v2: '.$stats_interval_v2.'<br />');
		echo('$stats_simple_v1: '.$stats_simple_v1.'<br />');
		*/
		
		//For the Query limit
		$stats_products_start_query	= '';
		$stats_products_end_query	= '';
	
		//if the type is "interval" we gona build the interval of the condition
		if(strcmp($type,'interval')==0){
			$stats_products_start_query		= strtotime(substr($stats_interval_v1,0,4).'/'.substr($stats_interval_v1,4,2).'/01 00:00:00');
			
			$stats_products_end_temp		= substr($stats_interval_v2,0,4).'/'.substr($stats_interval_v2,4,2).'/01';
			
			$stats_get_number_days_of_a_month	= cal_days_in_month(CAL_GREGORIAN, date('m',strtotime($stats_products_end_temp)), date('Y',strtotime($stats_products_end_temp)));
			
			$stats_products_end_query		= strtotime(substr($stats_interval_v2,0,4).'/'.substr($stats_interval_v2,4,2).'/'.$stats_get_number_days_of_a_month.' 23:59:59');
			
			//echo($stats_products_start_query.' ** '.$stats_products_end_query);
			
		}else{
			//Its simple
			$stats_products_start_query		= strtotime(substr($stats_simple_v1,0,4).'/'.substr($stats_simple_v1,4,2).'/01 00:00:00');
			
			$stats_products_end_temp		= substr($stats_simple_v1,0,4).'/'.substr($stats_simple_v1,4,2).'/01';
			
			$stats_get_number_days_of_a_month	= cal_days_in_month(CAL_GREGORIAN, date('m',strtotime($stats_products_end_temp)), date('Y',strtotime($stats_products_end_temp)));
			
			$stats_products_end_query		= strtotime(substr($stats_simple_v1,0,4).'/'.substr($stats_simple_v1,4,2).'/'.$stats_get_number_days_of_a_month.' 23:59:59');
			
			//echo($stats_products_start_query.' ** '.$stats_products_end_query);
		}
	
		
		if(!empty($stats_products_search_hidden)){
			$stats_condition_query	= " AND 
											pfr.id=".$stats_products_search_hidden." ";
		}else{
			$stats_condition_query	= " ";
		}
		
		
		//Query pagination Limit
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
		
		
		//Count Query
		$res_get_products_count_query	= "SELECT 
												count(pfr.id) AS c

											FROM
												advertisers a,
												products_fr	pfr
											WHERE
												a.id=".$_SESSION['extranet_user_id']."
											AND
												pfr.idAdvertiser=a.id 
											".$stats_condition_query." 
										";
									
		$res_get_products_count = $db->query($res_get_products_count_query, __FILE__, __LINE__);
		
		$content_get_products_count	= $db->fetchAssoc($res_get_products_count);
		$total_count_results		= $content_get_products_count['c'];
		
		
		
		if($total_count_results!=0){
		
			/*$res_get_products_query	= "SELECT 
											pfr.id AS id,
											pfr.name AS name,
											pfr.fastdesc AS fastdesc,
											count(sh.idProduct) AS c

										FROM
											advertisers a,
											products_fr	pfr,
											stats_hit sh
										WHERE
											a.id=".$_SESSION['extranet_user_id']."
										AND
											pfr.idAdvertiser=a.id
										AND
											sh.idProduct=pfr.id
										AND
											sh.timestamp	BETWEEN ".$stats_products_start_query." AND ".$stats_products_end_query."

										GROUP BY pfr.id
										ORDER BY c DESC
										
										".$query_suite_param_limit_ready."";*/
										
			$res_get_products_query	= "SELECT 
											pfr.id AS product_id,
											pfr.name AS product_name,
											pfr.fastdesc AS product_fastdesc,
											count(sh.idProduct) AS c

										FROM
											advertisers a,
											products_fr	pfr LEFT JOIN stats_hit sh ON sh.idProduct=pfr.id AND sh.timestamp	BETWEEN ".$stats_products_start_query." AND ".$stats_products_end_query." 
										WHERE
											a.id=".$_SESSION['extranet_user_id']."
										AND
											pfr.idAdvertiser=a.id
											
										".$stats_condition_query." 	

										GROUP BY pfr.id
										ORDER BY c DESC, pfr.name ASC
										
										".$query_suite_param_limit_ready."";							
										
											
			$res_get_products = $db->query($res_get_products_query, __FILE__, __LINE__);
			
			
			//echo('We got products => '.$total_count_results.' * '.mysql_num_rows($res_get_products));
			
			
			echo('<div id="page_list_selector_container" style="padding: 25px 0 15px 10px; width:40%; float:left;">');

				echo('<div id="page_list_selector_text">');
					echo('Produits par page ');
				echo('</div>');//end div #page_list_selector_text
				
				echo('<div id="page_list_selector_select">');
				
					echo('<select id="f_select_p" onchange="stats_products_get_more_now()">');
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
						
						echo('<option value="tout"');
						if(strcmp($f_pp,'tout')==0){
							echo(' selected="true" ');
						}
						echo('>Tout</option>');
						
					echo('</select>');
					
				echo('</div>');//end div #page_list_selector_select
					
			echo('</div>');//end div #page_list_selector_container
			
			
			//Start Top writing pagination
			echo('<div class="pagination products-top-pagination">');
				echo('<div style="color:#52BFEA; float:left; padding-top: 2px;">'.$total_count_results.' Produit');
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
										echo('<div class="disabled"><a href="javascript:stats_products_load_other_page(\''.$precedent.'\')">< Prec.</a></div>'); 
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
								echo('<a title="page num&eacute;ro 1" href="javascript:stats_products_load_other_page(\'1\')" class="pgnt_nmbr"> 1 </a><span style="float:left; padding: 0px 3px 0px 3px;">..</span>');
							}

							$count_local_stop=$page+4;

							while( ($count_pagination<=$nb_pages) && ($count_local<$count_local_stop) ){
								if($count_pagination==$page){
									echo('<div class="disabled">&nbsp;'.$count_pagination.'&nbsp;</div>');
								}else{
										echo('<a title="page num&eacute;ro '.$count_pagination.'" href="javascript:stats_products_load_other_page(\''.$count_pagination.'\')" class="pgnt_nmbr"> '.$count_pagination.' </a>');	
								}
								$count_pagination++;
								$count_local++;
							}

							if(($count_pagination <=$nb_pages) ){ //pour ne pas afficher suivant si on est dans la derniere page	
									echo('<span style="float:left; padding: 0px 3px 0px 3px;">..</span><a title="page num&eacute;ro '.$nb_pages.'" href="javascript:stats_products_load_other_page(\''.$nb_pages.'\')" class="pgnt_nmbr"> '.$nb_pages.' </a>');
							}
							
							//echo('] ');
							if($page<$nb_pages ){//partir a la derniere page (30) si on a les resultats qui depasse 300 
								$suivant = $page +1;
									echo('<a title="page num&eacute;ro '.$suivant.'" href="javascript:stats_products_load_other_page(\''.$suivant.'\')" style="text-decoration:none;" class="pgnt_nmbr">&nbsp;Suiv.&nbsp;</a>');
							}
						}
						
						// Fin Top pagination
					
				}//end if(strcmp($f_pp,'tout')!=0)
			
			echo('</div>');//end div .form-right .pagination
			
			
			echo('<div class="products table-responsive">');
				echo('<table class="table" style="width:100%;">');
					echo('<tr>');
						echo('<th style="width: 15%;">');
							echo('Photo');
						echo('</th>');
						
						echo('<th style="width: 20%;">');
							echo('ID');
						echo('</th>');
						
						echo('<th style="width: 20%;">');
							echo('Nom');
						echo('</th>');
						
						echo('<th style="width: 30%;">');
							echo('Desc. rapide');
						echo('</th>');
						
						echo('<th style="width: 15%;">');
							echo('Nombre vues');
						echo('</th>');
							
						echo('<th style="width: 8%;">');
							echo('Voir');
						echo('</th>');
						
					echo('</tr>');
		
				$modulo_local_loop	= 0;
				$modulo_local_class	= 'alt';
				
				while($content_get_products	= $db->fetchAssoc($res_get_products)){
				
					//Calculating modulo to make a difference between the table rows !!
					if($modulo_local_loop%2){
						$modulo_local_class	= 'alt';
					}else{
						$modulo_local_class	= '';
					}
					
					$row_link	= 'extranet-v3-stats-products-detail.html?id='.$content_get_products['product_id'];
					
					echo('<tr class="rs '.$modulo_local_class.'" >');
						echo('<td class="valign" onclick="javascript:open_link_blank(\'stats_external_formid\', \''.$row_link.'\', \'_self\')">');
								echo('<img src="'.PRODUCTS_IMAGE_URL.'thumb_small/'.$content_get_products['product_id'].'-1.jpg" />');
						echo('</td>');
							
						echo('<td class="valign" onclick="javascript:open_link_blank(\'stats_external_formid\', \''.$row_link.'\', \'_self\')">');
								echo $content_get_products['product_id'];
						echo('</td>');
						
						echo('<td class="valign" onclick="javascript:open_link_blank(\'stats_external_formid\', \''.$row_link.'\', \'_self\')">');
								echo $content_get_products['product_name'];
						echo('</td>');
						
						echo('<td class="valign" onclick="javascript:open_link_blank(\'stats_external_formid\', \''.$row_link.'\', \'_self\')">');
								echo $content_get_products['product_fastdesc'];
						echo('</td>');
						
						echo('<td class="valign" onclick="javascript:open_link_blank(\'stats_external_formid\', \''.$row_link.'\', \'_self\')">');
								echo $content_get_products['c'];
						echo('</td>');
						
						echo('<td class="valign cursor-default">');
						
							//Test 3 en attente Modification ou Suppression
							
							echo('<a href="extranet-v3-stats-products-detail.html?id='.$content_get_products['product_id'].'" title="Voir" target="self">');
								echo('<i class="fa fa-eye"></i>');
							echo('</a>&nbsp;');
							
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
				echo('<div class="stats-bottom-pagination pagination prdct">');
					echo('<div style="color:#52BFEA; float:left; padding-top: 2px;">'.$total_count_results.' Produit');
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
											echo('<div class="disabled"><a href="javascript:stats_products_load_other_page(\''.$precedent.'\')">< Prec.</a></div>'); 
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
									echo('<a title="page num&eacute;ro 1" href="javascript:stats_products_load_other_page(\'1\')" class="pgnt_nmbr"> 1 </a><span style="float:left; padding: 0px 3px 0px 3px;">..</span>');
								}

								$count_local_stop=$page+4;

								while( ($count_pagination<=$nb_pages) && ($count_local<$count_local_stop) ){
									if($count_pagination==$page){
										echo('<div class="disabled">&nbsp;'.$count_pagination.'&nbsp;</div>');
									}else{
											echo('<a title="page num&eacute;ro '.$count_pagination.'" href="javascript:stats_products_load_other_page(\''.$count_pagination.'\')" class="pgnt_nmbr"> '.$count_pagination.' </a>');	
									}
									$count_pagination++;
									$count_local++;
								}

								if(($count_pagination <=$nb_pages) ){ //pour ne pas afficher suivant si on est dans la derniere page	
										echo('<span style="float:left; padding: 0px 3px 0px 3px;">..</span><a title="page num&eacute;ro '.$nb_pages.'" href="javascript:stats_products_load_other_page(\''.$nb_pages.'\')" class="pgnt_nmbr"> '.$nb_pages.' </a>');
								}
								
								//echo('] ');
								if($page<$nb_pages ){//partir a la derniere page (30) si on a les resultats qui depasse 300 
									$suivant = $page +1;
										echo('<a title="page num&eacute;ro '.$suivant.'" href="javascript:stats_products_load_other_page(\''.$suivant.'\')" style="text-decoration:none;" class="pgnt_nmbr">&nbsp;Suiv.&nbsp;</a>');
								}
							}
							
							// Fin pagination
							
						
					}//end if(strcmp($f_pp,'tout')!=0)
				
				echo('</div>');//end div .form-right .pagination
				
			echo('</div>');//end div .row
			
			
			//Start Export
			echo('<div class="row" style="text-align:center;">');
				echo('<input type="button" id="stats_btn_export_listner" value="Exporter" onclick="javascript:stats_products_global_export();" class="btn btn-primary">');
			echo('</div>');//end div .row
			//End Export
			
		
		}else{
			echo("Aucune information &agrave; afficher !");
		}//end else if global count 	if($total_count_results!=0)
		

	}else{
		echo('<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;<strong><a href="login.html">Merci de vous reconnecter.</a></strong>');
	}//end else if(!empty($_SESSION['extranet_user_id'])){
?>