<?php	
	require_once('extranet_v3_functions.php'); 

	
	if(!empty($_SESSION['extranet_user_id'])){
	
		//Getting params
		$f_ps							= mysql_escape_string($_POST['f_ps']);
		$f_pp							= mysql_escape_string($_POST['f_pp']);
		//$pff							= mysql_escape_string($_POST['pff']);
		
		//Category filter
		//Building the Query
		/*$product_familie_filter_query	= '';
		
		$product_familie_filter_array	= explode('|',$pff);
		$product_familie_local_loop		= 0;
		while(!empty($product_familie_filter_array[$product_familie_local_loop]) && $product_familie_local_loop<30){
			$product_familie_filter_query .="".$product_familie_filter_array[$product_familie_local_loop].", ";
			$product_familie_local_loop++;
		}
		
		//Delete the last two chars
		$product_familie_filter_query = substr($product_familie_filter_query, 0, -2);
		
		if(!empty($product_familie_filter_query)){
			//$query_suite_param	= " 	EXISTS(".$product_familie_filter_query.") AND";
			$query_suite_param	= " 	 pr_fam.idFamily IN (".$product_familie_filter_query.")
									AND";				
		}
		*/
		
		
		
		

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
		$res_get_products_count_query	= "SELECT 

												count(DISTINCT(p_exh.p_add_adv___id)) c
												
											FROM
												products_extranet_history p_exh
											WHERE
												".$query_suite_param."
													p_exh.p__idAdvertiser=".$_SESSION['extranet_user_id']."
												AND
													user_operation='Brouillon ajout'";
									
		$res_get_products_count = $db->query($res_get_products_count_query, __FILE__, __LINE__);
		
		$content_get_products_count	= $db->fetchAssoc($res_get_products_count);
		$total_count_results		= $content_get_products_count['c'];
		
		
		
		if($total_count_results!=0){
			$res_get_products_query	= "SELECT 
			
										p_exh.p_add_adv___id product_id, 
										p_exh.pfr__name product_name,
										p_exh.pfr__fastdesc product_fastdesc,
										
										p_exh.pfam__idFamily AS families,
										
										p_exh.date_demande
										
									FROM
										products_extranet_history p_exh
									WHERE
										".$query_suite_param."
											p_exh.p__idAdvertiser=".$_SESSION['extranet_user_id']."
										AND
											user_operation='Brouillon ajout'
									ORDER BY date_traitement DESC 
									".$query_suite_param_limit_ready."";	
									
			$res_get_products = $db->query($res_get_products_query, __FILE__, __LINE__);
		
			//echo('We got products => '.$total_count_results.' * '.mysql_num_rows($res_get_products));
			
			echo('<div id="page_list_selector_container" style="padding: 25px 0 15px 10px; width:40%; float:left;">');

				echo('<div id="page_list_selector_text">');
					echo('Produits par page ');
				echo('</div>');//end div #page_list_selector_text
				
				echo('<div id="page_list_selector_select">');
				
					echo('<select id="f_select_p-add" onchange="products_draft_get_more_now(\'add\')">');
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
			
			//Start writing pagination
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
										echo('<div class="disabled"><a href="javascript:products_draft_load_other_page(\'add\',\''.$precedent.'\')">< Prec.</a></div>'); 
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
								echo('<a title="page num&eacute;ro 1" href="javascript:products_draft_load_other_page(\'add\',\'1\')" class="pgnt_nmbr"> 1 </a><span style="float:left; padding: 0px 3px 0px 3px;">..</span>');
							}

							$count_local_stop=$page+4;

							while( ($count_pagination<=$nb_pages) && ($count_local<$count_local_stop) ){
								if($count_pagination==$page){
									echo('<div class="disabled">&nbsp;'.$count_pagination.'&nbsp;</div>');
								}else{
										echo('<a title="page num&eacute;ro '.$count_pagination.'" href="javascript:products_draft_load_other_page(\'add\',\''.$count_pagination.'\')" class="pgnt_nmbr"> '.$count_pagination.' </a>');	
								}
								$count_pagination++;
								$count_local++;
							}

							if(($count_pagination <=$nb_pages) ){ //pour ne pas afficher suivant si on est dans la derniere page	
									echo('<span style="float:left; padding: 0px 3px 0px 3px;">..</span><a title="page num&eacute;ro '.$nb_pages.'" href="javascript:products_draft_load_other_page(\'add\',\''.$nb_pages.'\')" class="pgnt_nmbr"> '.$nb_pages.' </a>');
							}
							
							//echo('] ');
							if($page<$nb_pages ){//partir a la derniere page (30) si on a les resultats qui depasse 300 
								$suivant = $page +1;
									echo('<a title="page num&eacute;ro '.$suivant.'" href="javascript:products_draft_load_other_page(\'add\',\''.$suivant.'\')" style="text-decoration:none;" class="pgnt_nmbr">&nbsp;Suiv.&nbsp;</a>');
							}
						}
						
						// Fin pagination
						
					
				}//end if(strcmp($f_pp,'tout')!=0)
			
			echo('</div>');//end div .form-right .pagination
				
			echo('<div class="products table-responsive">');
				echo('<table class="table" style="width: 100%;">');
					echo('<tr>');
						echo('<th style="width: 15%;">');
							echo('Photo');
						echo('</th>');
						
						echo('<th style="width: 25%;">');
							echo('Nom');
						echo('</th>');
						
						echo('<th style="width: 37%;">');
							echo('Desc. rapide');
						echo('</th>');
						
						echo('<th style="width: 15%;">');
							echo('Cat&eacute;gorie');
						echo('</th>');
							
						echo('<th style="width: 8%;">');
							echo('Actions');
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
					
					$row_link	= EXTRANET_URL.'extranet-v3-products-draft-add.html?id='.$content_get_products['product_id'];
					
					echo('<tr class="rs '.$modulo_local_class.'">');
						echo('<td class="valign" onclick="javascript:open_link_blank(\'products_external_formid\', \''.$row_link.'\', \'_self\')">');
						
								if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
									//$dir_URL 		= 'http://local-pictures-products.techni-contact.com/';
									$dir_URL 		= 'http://local-pictures-products-adv.techni-contact.com/';
								}else{
									//$dir_URL 		= PRODUCTS_IMAGE_URL;
									$dir_URL 		= PRODUCTS_IMAGE_ADV_URL;
								}
									
								if(is_file(PRODUCTS_IMAGE_ADV_INC.'thumb_small/'.$content_get_products['product_id'].'-1.jpg')){
								
									//Difference Windows & Linux
									
									$product_picture	= $dir_URL.'thumb_small/'.$content_get_products['product_id'].'-1.jpg';
								
								}else{
									$product_picture	= $dir_URL.'no-pic-thumb_small.gif';
								}//end else if isfile
								
								echo('<img src="'.$product_picture.'" />');
						echo('</td>');
							
						echo('<td class="valign" onclick="javascript:open_link_blank(\'products_external_formid\', \''.$row_link.'\', \'_self\')">');
								echo $content_get_products['product_name'];
						echo('</td>');
						
						echo('<td class="valign" onclick="javascript:open_link_blank(\'products_external_formid\', \''.$row_link.'\', \'_self\')">');
								echo $content_get_products['product_fastdesc'];
						echo('</td>');
						
						echo('<td class="valign" onclick="javascript:open_link_blank(\'products_external_formid\', \''.$row_link.'\', \'_self\')">');
						
							//Looking for the familie name

							if(!empty($content_get_products['families'])){
								$res_get_products_familie	= "SELECT 
															ffr.name
														FROM
															families_fr ffr
														WHERE
															ffr.id=".$content_get_products['families']."";	
								
								$res_get_products_familie = $db->query($res_get_products_familie, __FILE__, __LINE__);
							
								$content_get_products_count	= $db->fetchAssoc($res_get_products_familie);
								
								echo $content_get_products_count['name'];
							}else{
								echo(' - ');
							}
							
						echo('</td>');
						
						echo('<td class="valign cursor-default">');
							
							echo('<a href="'.EXTRANET_URL.'extranet_v3_product_preview.html?id='.$content_get_products['product_id'].'&t=c" title="Voir" target="blank">');
								echo('<i class="fa fa-eye"></i>');
							echo('</a>&nbsp;');
							
							echo('<a href="extranet-v3-products-draft-add.html?id='.$content_get_products['product_id'].'" title="Modifier"><i class="fa fa-pencil"></i></a>');
								echo('&nbsp;');
								
							echo('<a href="javascript:void(0);" onclick="product_delete_draft(\''.$content_get_products['product_id'].'\',\'list_draft_add\');" title="Supprimer"><i class="fa fa-remove"></i></a>');
								echo('&nbsp;');
								

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
				echo('<div class="products-bottom-pagination pagination prdct">');
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
											echo('<div class="disabled"><a href="javascript:products_draft_load_other_page(\'add\',\''.$precedent.'\')">< Prec.</a></div>'); 
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
									echo('<a title="page num&eacute;ro 1" href="javascript:products_draft_load_other_page(\'add\',\'1\')" class="pgnt_nmbr"> 1 </a><span style="float:left; padding: 0px 3px 0px 3px;">..</span>');
								}

								$count_local_stop=$page+4;

								while( ($count_pagination<=$nb_pages) && ($count_local<$count_local_stop) ){
									if($count_pagination==$page){
										echo('<div class="disabled">&nbsp;'.$count_pagination.'&nbsp;</div>');
									}else{
											echo('<a title="page num&eacute;ro '.$count_pagination.'" href="javascript:products_draft_load_other_page(\'add\',\''.$count_pagination.'\')" class="pgnt_nmbr"> '.$count_pagination.' </a>');	
									}
									$count_pagination++;
									$count_local++;
								}

								if(($count_pagination <=$nb_pages) ){ //pour ne pas afficher suivant si on est dans la derniere page	
										echo('<span style="float:left; padding: 0px 3px 0px 3px;">..</span><a title="page num&eacute;ro '.$nb_pages.'" href="javascript:products_draft_load_other_page(\'add\',\''.$nb_pages.'\')" class="pgnt_nmbr"> '.$nb_pages.' </a>');
								}
								
								//echo('] ');
								if($page<$nb_pages ){//partir a la derniere page (30) si on a les resultats qui depasse 300 
									$suivant = $page +1;
										echo('<a title="page num&eacute;ro '.$suivant.'" href="javascript:products_draft_load_other_page(\'add\',\''.$suivant.'\')" style="text-decoration:none;" class="pgnt_nmbr">&nbsp;Suiv.&nbsp;</a>');
								}
							}
							
							// Fin pagination
							
						
					}//end if(strcmp($f_pp,'tout')!=0)
				
				echo('</div>');//end div .form-right .pagination
				
			echo('</div>');//end div .row	
			
			?>
			<!-- Start Delete Draft -->
				
				<div id="product-dialog-draft-delete" title="Confirmation de suppression brouillon">
					&Ecirc;tes vous s&ucirc;r de vouloir supprimer ce brouillon ?
					<div id="product-dialog-draft-preview-response">
 						<div id="product-dialog-draft-preview-response-msg">&nbsp;</div>
					</div>
				</div>
				
				
			<!-- End Delete Draft -->
			
			<?php
			
		}else{
			echo("Vous n'avez actuellement aucun brouillon de cr&eacute;ation.");
		}//end else if global count 	if($total_count_results!=0)

	}else{
		echo('<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;<strong><a href="login.html">Merci de vous reconnecter.</a></strong>');
	}
?>