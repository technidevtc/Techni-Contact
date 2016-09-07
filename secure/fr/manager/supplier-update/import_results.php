
		<?php
		
			//Search for this import
			
			
			$res_get_operation_import = $db->query(" SELECT
														adv.nom1 adv_nom,	adv.id adv_id, 	
														adv.prixPublic,
														
														sup_upi.id, 							sup_upi.advertiser,													
														sup_upi.id_operator_start,				sup_upi.date_start,
														sup_upi.date_end, 						sup_upi.id_operator_cancel,
														sup_upi.date_cancel,					sup_upi.total_product_manager,
														sup_upi.total_product_file,				sup_upi.total_product_affected,
														sup_upi.total_product_file_not_found,	sup_upi.total_product_manager_not_found,
														sup_upi.total_price_manager,			sup_upi.total_price_file,
														sup_upi.etat,
														
														bo_u.name	start_operator_name
											
													FROM
														advertisers adv,
														supplier_update_info sup_upi
															LEFT JOIN bo_users		AS bo_u	ON sup_upi.id_operator_start=bo_u.id
														
													WHERE
														adv.id=sup_upi.advertiser
													AND
														sup_upi.id=".$id_operation."", __FILE__, __LINE__);
														
			$content_get_operation_import = $db->fetchAssoc($res_get_operation_import);
				
				
			//$different_price_pourcent	= mmf_percent_difference_price($content_get_operation_import['total_price_manager'], $content_get_operation_import['total_price_file']);
			
			
			
			echo('<div class="one_bloc_c">');

				echo('<div class="titreBloc">');
					echo('Import '.$content_get_operation_import['adv_nom'].' n&deg; '.$id_operation.'');
				echo('</div>');
				
				echo('<div class="coordCustomer">');
				
					echo('<div class="mmf_one_row">');
						echo('<div class="mmf_left">');
							echo('Date cr&eacute;ation de l\'import :');
						echo('</div>');
						echo('<div class="mmf_right">');
							echo($content_get_operation_import['date_start']);
						echo('</div>');
					echo('</div>');//end div .mmf_one_row
					
					echo('<div class="mmf_one_row">');
						echo('<div class="mmf_left">');
							echo('Nom du fournisseur :');
						echo('</div>');
						echo('<div class="mmf_right">');
							echo('<a href="/fr/manager/advertisers/edit.php?id='.$content_get_operation_import['adv_id'].'" target="_blank">'.$content_get_operation_import['adv_nom'].'</a>');
						echo('</div>');
					echo('</div>');//end div .mmf_one_row
					
					echo('<div class="mmf_one_row">');
						echo('<div class="mmf_left">');
							echo('Etat :');
						echo('</div>');
						echo('<div class="mmf_right">');
						
							if(strcmp($content_get_operation_import['etat'],'en cours')=='0'){
								echo('En cours');
							}else if(strcmp($content_get_operation_import['etat'],'finalise')=='0'){
								echo('Finalis&eacute;');
							}else{
								echo('Annul&eacute;');
							}	
							
						echo('</div>');
					echo('</div>');//end div .mmf_one_row
					
					echo('<div class="mmf_one_row">');
						echo('<div class="mmf_left">');
							echo('Nb lignes en manager :');
						echo('</div>');
						echo('<div class="mmf_right">');
							echo($content_get_operation_import['total_product_manager']);
						echo('</div>');
					echo('</div>');//end div .mmf_one_row
					
					echo('<div class="mmf_one_row">');
						echo('<div class="mmf_left">');
							echo('Nb lignes ds fichier :');
						echo('</div>');
						echo('<div class="mmf_right">');
							echo($content_get_operation_import['total_product_file']);
						echo('</div>');
					echo('</div>');//end div .mmf_one_row
					
					echo('<div class="mmf_one_row">');
						echo('<div class="mmf_left">');
							echo('Nb lignes trait&egrave;es :');
						echo('</div>');
						echo('<div class="mmf_right">');
							echo($content_get_operation_import['total_product_affected']);
						echo('</div>');
					echo('</div>');//end div .mmf_one_row
					
					echo('<div class="mmf_one_row">');
						echo('<div class="mmf_left">');
							echo('Ref obsol&egrave;tes :');
						echo('</div>');
						echo('<div class="mmf_right">');
							echo('<a href="export-obsolete-products.php?id='.$id_operation.'" target="_blank">'.$content_get_operation_import['total_product_manager_not_found'].'</a>');
						echo('</div>');
					echo('</div>');//end div .mmf_one_row
					
					echo('<div class="mmf_one_row">');
						echo('<div class="mmf_left">');
							echo('Ref nouvelles :');
						echo('</div>');
						echo('<div class="mmf_right">');
							echo('<a href="export-new-products.php?id='.$id_operation.'" target="_blank">'.$content_get_operation_import['total_product_file_not_found'].'</a>');
						echo('</div>');
					echo('</div>');//end div .mmf_one_row
					
					echo('<div class="mmf_one_row">');
						echo('<div class="mmf_left">');
							echo('Op&eacute;rateur :');
						echo('</div>');
						echo('<div class="mmf_right" style="width:150px;">');
							echo($content_get_operation_import['start_operator_name']);
							if(strcmp($content_get_operation_import['id_operator_cancel'],'0')!=0){
								$res_get_end_bo_user_name	= $db->query("SELECT
																			bo_u.name	end_operator_name
																		FROM
																			bo_users bo_u
																		WHERE
																			bo_u.id=".$content_get_operation_import['id_operator_cancel']."
																		", __FILE__, __LINE__);
								$content_get_end_bo_user_name = $db->fetchAssoc($res_get_end_bo_user_name);
								echo('<br />Annul&eacute; par : '.$content_get_end_bo_user_name['end_operator_name']);
							}
						echo('</div>');
					echo('</div>');//end div .mmf_one_row
					
					/*
					echo('<div class="mmf_one_row">');
						echo('<div class="mmf_left">');
							echo('Variation tarifaire (%) :');
						echo('</div>');
						echo('<div class="mmf_right">');
							echo($different_price_pourcent);
						echo('</div>');
					echo('</div>');//end div .mmf_one_row
					*/
					
					echo('<br />');
					echo('<div class="mmf_one_row" id="cancel_operation_results">');
						if(strcmp($content_get_operation_import['etat'],'annule')!='0'){
							echo('<div class="mmf_left">');
								echo('&nbsp');
							echo('</div>');
							echo('<div class="mmf_right">');
								echo('<input type="button" value="Annuler" id="mmf_btn_cancel_operation" class="bouton" onclick="mmf_cancel_this_operation('.$id_operation.')">');
							echo('</div>');
						}
						echo('<div class="mmf_right">');
							echo('<input type="button" value="Retour" id="mmf_btn_back_import_home" class="bouton" onclick="javascript:window.location.assign(\'/fr/manager/supplier-update/index.php\')">');
						echo('</div>');
						
					echo('</div>');//end div .mmf_one_row
					
				
				echo('</div>');//end div .coordCustomer
				
			echo('</div>');//end div .one_bloc
		?>					
	
