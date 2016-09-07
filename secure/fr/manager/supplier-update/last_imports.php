<?php

	if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
		require_once '../../../../config.php';
	}else{
		require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
	}

	$db = DBHandle::get_instance();
	$user = new BOUser();
	require_once('supplier_update_functions.php');
	
	try {
	  if (!$user->login())
		throw new Exception("Votre session a expiré, veuillez vous identifier à nouveau après avoir rafraîchi votre page.");
		

		$nom_fournisseur		= mysql_real_escape_string(trim($_POST['nom_fournisseur']));
		if(!empty($nom_fournisseur)){
			//Query for one advertiser
			
			echo('<br />');
			echo('<div class="bg">');
			
			//Looking for all imports by advertiser
			$res_get_last_imports_advertiser	= $db->query("SELECT
														sup_ui.id AS id_operation,	sup_ui.advertiser,
														sup_ui.id_operator_start AS id_operator_start,	sup_ui.date_start,
														sup_ui.date_end,		sup_ui.id_operator_cancel,
														sup_ui.date_cancel,		sup_ui.total_product_manager,
														sup_ui.total_product_file,	sup_ui.total_product_affected,
														sup_ui.total_product_file_not_found,	sup_ui.total_product_manager_not_found,
														sup_ui.total_price_manager,	sup_ui.total_price_file,	
														sup_ui.etat,
														
														bo_u.name	start_operator_name
														

													FROM
														supplier_update_info	sup_ui	
															LEFT JOIN advertisers 	AS adv 	ON sup_ui.advertiser=adv.id
															LEFT JOIN bo_users		AS bo_u	ON sup_ui.id_operator_start=bo_u.id
													WHERE
														adv.nom1='".addslashes($nom_fournisseur)."'
													ORDER BY sup_ui.date_start	DESC", __FILE__, __LINE__);
													
			if(mysql_num_rows($res_get_last_imports_advertiser)>0){	
			
					echo('<div id="mmf_last_imports_content">');
						echo('<div class="mmf_lic_row_h">');
						
							echo('<div class="mmf_lic_voir">');
								echo('Voir');
							echo('</div>');
							
							echo('<div class="mmf_lic_datec">');
								echo('Date cr&eacute;ation de l\'import');
							echo('</div>');
							
							echo('<div class="mmf_lic_etat">');
								echo('Etat');
							echo('</div>');
							
							echo('<div class="mmf_lic_nrefdm">');
								echo('Nb lignes en manager');
							echo('</div>');
							
							echo('<div class="mmf_lic_nrefdf">');
								echo('Nb lignes ds fichier');
							echo('</div>');
							
							echo('<div class="mmf_lic_nrefdmaj">');
								echo('Nb lignes trait&eacute;es');
							echo('</div>');
							
							echo('<div class="mmf_lic_nrefdfob">');
								echo('Ref obsol&egrave;tes');
							echo('</div>');
							
							echo('<div class="mmf_lic_nrefdfar">');
								echo('Ref nouvelles');
							echo('</div>');
							
							echo('<div class="mmf_lic_nrefdope">');
								echo('Op&eacute;rateur ');
							echo('</div>');
							
							/*echo('<div class="mmf_lic_nrefdpab">');
								echo('Variation tarifaire (%)');
							echo('</div>');*/
							
						echo('</div>');//end div .mmf_lic_row
						
						while($content_get_last_imports_advertiser = $db->fetchAssoc($res_get_last_imports_advertiser)){
					
							//$different_price_pourcent	= mmf_percent_difference_price($content_get_last_imports_advertiser['total_price_file'], $content_get_last_imports_advertiser['total_price_manager']);
							
							echo('<div class="mmf_lic_row">');
						
								echo('<div class="mmf_lic_voir">');
									echo('<a href="/fr/manager/supplier-update/export-detail.php?id='.$content_get_last_imports_advertiser['id_operation'].'" target="_blank">');
										echo('<img src="/fr/manager/ressources/icons/monitor_go.png" alt="Voir" title="Voir" />');
									echo('</a>');
								echo('</div>');
								
								echo('<div class="mmf_lic_datec">');
									echo($content_get_last_imports_advertiser['date_start']);
								echo('</div>');
								
								
								echo('<div class="mmf_lic_etat">');
									if(strcmp($content_get_last_imports_advertiser['etat'],'en cours')=='0'){
										echo('En cours');
									}else if(strcmp($content_get_last_imports_advertiser['etat'],'finalise')=='0'){
										echo('Finalis&eacute;');
									}else{
										echo('Annul&eacute;');
									}
								echo('</div>');
								
								echo('<div class="mmf_lic_nrefdm">');
									echo($content_get_last_imports_advertiser['total_product_manager']);
								echo('</div>');
								
								echo('<div class="mmf_lic_nrefdf">');
									echo($content_get_last_imports_advertiser['total_product_file']);
								echo('</div>');
								
								echo('<div class="mmf_lic_nrefdmaj">');
									echo($content_get_last_imports_advertiser['total_product_affected']);
								echo('</div>');
								
								echo('<div class="mmf_lic_nrefdfob">');
									echo($content_get_last_imports_advertiser['total_product_manager_not_found']);
								echo('</div>');
								
								echo('<div class="mmf_lic_nrefdfar">');
									echo($content_get_last_imports_advertiser['total_product_file_not_found']);
								echo('</div>');
								
								echo('<div class="mmf_lic_nrefdope">');
									//echo('Trait&eacute; par : ');
									if(strlen($content_get_last_imports_advertiser['start_operator_name'])>22){
										echo(substr($content_get_last_imports_advertiser['start_operator_name'],0,19).' ...');
									}else{
										echo($content_get_last_imports_advertiser['start_operator_name']);
									}
									if(strcmp($content_get_last_imports_advertiser['id_operator_cancel'],'0')!=0){
										$res_get_end_bo_user_name	= $db->query("SELECT
																					bo_u.name	end_operator_name
																				FROM
																					bo_users bo_u
																				WHERE
																					bo_u.id=".$content_get_last_imports_advertiser['id_operator_cancel']."
																				", __FILE__, __LINE__);
										$content_get_end_bo_user_name = $db->fetchAssoc($res_get_end_bo_user_name);
										echo('<br />Annul&eacute; par : ');
										if(strlen($content_get_end_bo_user_name['end_operator_name'])>11){
											echo(substr($content_get_end_bo_user_name['end_operator_name'],0,10).' ...');
										}else{
											echo($content_get_end_bo_user_name['end_operator_name']);
										}
										
									}//end if
								echo('</div>');
								
								
								/*echo('<div class="mmf_lic_nrefdpab">');
									echo($different_price_pourcent);
								echo('</div>');*/
								
							echo('</div>');//end div .mmf_lic_row
					
						}//end while
						
					echo('</div>');//end div #mmf_last_imports_content
					
				}else{
					echo('Historique d\'import inexistant !');
				}
				
			echo('</div>'); //end div .bg
			
		}else{
			//Query for the last imports
			
			echo('<br />');
			echo('<div class="bg">');
	
				//Looking for the last 5 imports
				$res_get_last_imports	= $db->query("SELECT
														sup_ui.id AS id_operation,	sup_ui.advertiser,
														sup_ui.id_operator_start AS id_operator_start,	sup_ui.date_start,
														sup_ui.date_end,		sup_ui.id_operator_cancel,
														sup_ui.date_cancel,		sup_ui.total_product_manager,
														sup_ui.total_product_file,	sup_ui.total_product_affected,
														sup_ui.total_product_file_not_found,	sup_ui.total_product_manager_not_found,
														sup_ui.total_price_manager,	sup_ui.total_price_file,	
														sup_ui.etat,

														adv.nom1,
														
														bo_u.name	start_operator_name
														

													FROM
														supplier_update_info	sup_ui	
															LEFT JOIN advertisers AS adv ON sup_ui.advertiser=adv.id
															LEFT JOIN bo_users		AS bo_u	ON sup_ui.id_operator_start=bo_u.id

													ORDER BY sup_ui.date_start DESC
													LIMIT 0, 5", __FILE__, __LINE__);
													
				if(mysql_num_rows($res_get_last_imports)>0){
				
					echo('<div id="mmf_last_imports_content">');
						echo('<div class="mmf_lic_row_h">');
						
							echo('<div class="mmf_lic_voir">');
								echo('Voir');
							echo('</div>');
							
							echo('<div class="mmf_lic_datec">');
								echo('Date cr&eacute;ation de l\'import');
							echo('</div>');
							
							echo('<div class="mmf_lic_nomf">');
								echo('Nom fournisseur');
							echo('</div>');
							
							echo('<div class="mmf_lic_etat">');
								echo('Etat');
							echo('</div>');
							
							echo('<div class="mmf_lic_nrefdm">');
								echo('Nb lignes en manager');
							echo('</div>');
							
							echo('<div class="mmf_lic_nrefdf">');
								echo('Nb lignes ds fichier');
							echo('</div>');
							
							echo('<div class="mmf_lic_nrefdmaj">');
								echo('Nb lignes trait&eacute;es');
							echo('</div>');
							
							echo('<div class="mmf_lic_nrefdfob">');
								echo('Ref obsol&egrave;tes');
							echo('</div>');
							
							echo('<div class="mmf_lic_nrefdfar">');
								echo('Ref nouvelles');
							echo('</div>');
							
							echo('<div class="mmf_lic_nrefdope">');
								echo('Op&eacute;rateur ');
							echo('</div>');
							
							/*echo('<div class="mmf_lic_nrefdpab">');
								echo('Variation tarifaire (%)');
							echo('</div>');*/
							
						echo('</div>');//end div .mmf_lic_row
						
						while($content_get_last_imports = $db->fetchAssoc($res_get_last_imports)){
					
							//$different_price_pourcent	= mmf_percent_difference_price($content_get_last_imports['total_price_file'], $content_get_last_imports['total_price_manager']);
							
							echo('<div class="mmf_lic_row">');
						
								echo('<div class="mmf_lic_voir">');
									echo('<a href="/fr/manager/supplier-update/export-detail.php?id='.$content_get_last_imports['id_operation'].'" target="_blank">');
										echo('<img src="/fr/manager/ressources/icons/monitor_go.png" alt="Voir" title="Voir" />');
									echo('</a>');
								echo('</div>');
								
								echo('<div class="mmf_lic_datec">');
									echo($content_get_last_imports['date_start']);
								echo('</div>');
								
								echo('<div class="mmf_lic_nomf">');
									echo($content_get_last_imports['nom1']);
								echo('</div>');
								
								echo('<div class="mmf_lic_etat">');
									if(strcmp($content_get_last_imports['etat'],'en cours')=='0'){
										echo('En cours');
									}else if(strcmp($content_get_last_imports['etat'],'finalise')=='0'){
										echo('Finalis&eacute;');
									}else{
										echo('Annul&eacute;');
									}
								echo('</div>');
								
								echo('<div class="mmf_lic_nrefdm">');
									echo($content_get_last_imports['total_product_manager']);
								echo('</div>');
								
								echo('<div class="mmf_lic_nrefdf">');
									echo($content_get_last_imports['total_product_file']);
								echo('</div>');
								
								echo('<div class="mmf_lic_nrefdmaj">');
									echo($content_get_last_imports['total_product_affected']);
								echo('</div>');
								
								echo('<div class="mmf_lic_nrefdfob">');
									echo($content_get_last_imports['total_product_manager_not_found']);
								echo('</div>');
								
								echo('<div class="mmf_lic_nrefdfar">');
									echo($content_get_last_imports['total_product_file_not_found']);
								echo('</div>');
								
								echo('<div class="mmf_lic_nrefdope">');
									//echo('Trait&eacute; par : ');
									if(strlen($content_get_last_imports['start_operator_name'])>22){
										echo(substr($content_get_last_imports['start_operator_name'],0,19).' ...');
									}else{
										echo($content_get_last_imports['start_operator_name']);
									}
									if(strcmp($content_get_last_imports['id_operator_cancel'],'0')!=0){
										$res_get_end_bo_user_name	= $db->query("SELECT
																					bo_u.name	end_operator_name
																				FROM
																					bo_users bo_u
																				WHERE
																					bo_u.id=".$content_get_last_imports['id_operator_cancel']."
																				", __FILE__, __LINE__);
										$content_get_end_bo_user_name = $db->fetchAssoc($res_get_end_bo_user_name);
										echo('<br />Annul&eacute; par : ');
										if(strlen($content_get_end_bo_user_name['end_operator_name'])>11){
											echo(substr($content_get_end_bo_user_name['end_operator_name'],0,10).' ...');
										}else{
											echo($content_get_end_bo_user_name['end_operator_name']);
										}
									}//end if
								echo('</div>');
								
								/*echo('<div class="mmf_lic_nrefdpab">');
									echo($different_price_pourcent);
								echo('</div>');*/
								
							echo('</div>');//end div .mmf_lic_row
					
						}//end while
						
					echo('</div>');//end div #mmf_last_imports_content
					
				}else{
					echo('Historique d\'import inexistant !');
				}
				
			echo('</div>'); //end div .bg

		}//end else if!empty($nom_fournisseur)

	}catch(Exception $e){
		echo($e);
	}
?>