<?php

	if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
		require_once '../../../../config.php';
	}else{
		require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
	}

	$title = $navBar = "MAJ Fournisseurs";
	$db = DBHandle::get_instance();
	$user = new BOUser();

	require(ADMIN.'head.php');
	require_once 'supplier_update_functions.php';
?>
<?php 
	if ($user->get_permissions()->has("m-prod--sm-maj-fournisseurs", "r")){	
?>
		<link rel="stylesheet" type="text/css" href="<?php echo ADMIN_URL ?>supplier-update/supplier-update.css" />
		<script type="text/javascript" src="<?php echo ADMIN_URL ?>supplier-update/supplier-update.js"></script>
		<div id="module_maj_fournisseurs">
		
			<div class="titreStandard">Importation fichier</div>
			<div class="bg">
			
				<div id="mmf_import_results">
				
					<div id="mmf_steps_container">
					
						<div class="one_bloc">

							<div class="titreBloc">
								Importation
							</div>
						
							<div class="coordCustomer">
						
								<div class="mmf_one_row">
									<div id="mmf_import_step_1" class="pending">
										<span>1.</span> Chargement fichier 
									</div>
								</div> <!-- end div .mmf_one_row -->
							
								
								<div class="mmf_one_row">
									<div id="mmf_import_step_2" class="pending">
										<span>2.</span> Importation fichier 
									</div>
								</div> <!-- end div .mmf_one_row -->
							

								<div class="mmf_one_row">
									<div id="mmf_import_step_3" class="pending">
										<span>3.</span> Recherche produits fournisseur 
									</div>
								</div> <!-- end div .mmf_one_row -->
								
								
								<div class="mmf_one_row">
									<div id="mmf_import_step_4" class="pending">
										<span>4.</span> Rercherche correspondance 
									</div>
								</div> <!-- end div .mmf_one_row -->
							
								
								<div class="mmf_one_row">
									<div id="mmf_import_step_5" class="pending">
										<span>5.</span> Finalisation proc&eacute;dure 
									</div>
								</div> <!-- end div .mmf_one_row -->
								
								<div class="mmf_one_row">
									<div id="mmf_import_step_6" class="pending">
										<span>6.</span> Cr&eacute;ation de note interne 
									</div>
								</div> <!-- end div .mmf_one_row -->
								
						
							</div><!-- end div .coordCustomer -->
						
						</div><!-- end div .one_bloc -->
					
					</div><!-- end div #mmf_steps_container -->
			
			
					<script type="text/javascript">
						mmf_processing_step(1);
					</script>
								
					<?php
					
						$mmf_import_id_advertiser			= mysql_real_escape_string($_POST['mmf_import_id_advertiser']);
						$mmf_import_number_reference		= mmf_convert_excel_params_columns_to_number(mysql_real_escape_string($_POST['mmf_import_number_reference']));
						$mmf_import_number_tarif			= mmf_convert_excel_params_columns_to_number(mysql_real_escape_string($_POST['mmf_import_number_tarif']));
						$mmf_import_number_famille			= mmf_convert_excel_params_columns_to_number(mysql_real_escape_string($_POST['mmf_import_number_famille']));
						$mmf_import_number_nom				= mmf_convert_excel_params_columns_to_number(mysql_real_escape_string($_POST['mmf_import_number_nom']));
						$mmf_import_number_unite_vente		= mmf_convert_excel_params_columns_to_number(mysql_real_escape_string($_POST['mmf_import_number_unite_vente']));
						$mmf_import_number_quantite_carton	= mmf_convert_excel_params_columns_to_number(mysql_real_escape_string($_POST['mmf_import_number_quantite_carton']));
						$mmf_import_number_ecopart			= mmf_convert_excel_params_columns_to_number(mysql_real_escape_string($_POST['mmf_import_number_ecopart']));
						
						$fileElementName					= 'mmf_pjMessFile';
						$mmf_context						= 'production-fournisseurs-update-price';
						
						//Search for the advertiser to get infos
						//Optional and test if it's exist
						$res_get_advertiser = $db->query(" SELECT
																adv.nom1,			adv.id, 	
																adv.prixPublic, 	adv.margeRemise
													
															FROM
																advertisers adv
															WHERE
																adv.id=".$mmf_import_id_advertiser."", __FILE__, __LINE__);
																
						$content_get_advertiser = $db->fetchAssoc($res_get_advertiser);
						
						try {
						
							if(empty($content_get_advertiser['id']) || empty($mmf_import_number_reference) || empty($mmf_import_number_tarif) || empty($_FILES[$fileElementName]['tmp_name'])){
							
								echo('<span class="rederror">');
									echo('Erreur, merci de verifier votre formulaire !');
								echo('</span>');
							}else{
							
								//Start import 
								if (!empty($_FILES[$fileElementName]['error'])) {
									switch($_FILES[$fileElementName]['error']) {
									  case '1': $error = 'The uploaded file exceeds the upload_max_filesize directive in php.ini'; break;
									  case '2': $error = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form'; break;
									  case '3': $error = 'The uploaded file was only partially uploaded'; break;
									  case '4': $error = 'No file was uploaded.'; break;
									  case '6': $error = 'Missing a temporary folder'; break;
									  case '7': $error = 'Failed to write file to disk'; break;
									  case '8': $error = 'File upload stopped by extension'; break;
									  case '999':
									  default:  $error = 'No error code avaiable';
									}
									throw new Exception($error);
								}
								
								if (empty($_FILES[$fileElementName]['tmp_name']) || $_FILES[$fileElementName]['tmp_name'] == 'none' || !is_uploaded_file($_FILES[$fileElementName]['tmp_name']))
									throw new Exception("Aucun fichier n'a &eacute;t&eacute; uploadé");
							
								$ctxData = $uploadContextData[$mmf_context];
								if (!isset($ctxData))
									throw new Exception("Les droits à l'upload de fichiers ne sont pas configur&eacute;s pour le contexte ".$_POST['context']);						  
		  
								if (!$user->get_permissions()->has($ctxData['credential'],"e"))
									throw new Exception("Vous n'avez pas les droits adéquats pour r&eacute;aliser cette op&eacute;ration");
			
								$dir = ADMIN_UPLOAD_DIR.$ctxData['dir'];
								if (!is_dir($dir))
									throw new Exception("Répertoire de destination inexistant ");	
			
								$fileMimetype = $boValidMimeTypes[$_FILES[$fileElementName]['type']];
								if (!isset($fileMimetype))
									throw new Exception("Type de fichier incorrect : ");	
			
								//If exist increment name
								$extension = '.'.$fileMimetype;
								$prefix = $ctxData['file_prefix'];
								$idxFile = 1;
								while (is_file($dir.$prefix.$_POST['itemId'].'-'.$idxFile.$extension))
									$idxFile++;
									
								$nameFile = $prefix.$_POST['itemId'].'-'.$idxFile;
								$labelName = !empty($_POST['aliasFileName']) ? Utils::toDashAz09($_POST['aliasFileName']).$extension : $nameFile.$extension;
								  
								// file not correctly uploaded/moved
								$file_full_path	= $dir.$nameFile.$extension;
								if (!@move_uploaded_file($_FILES[$fileElementName]['tmp_name'], $file_full_path))
									throw new Exception("Le fichier uploadé n'a pu être copié correctement");

			
								/*************************************************************************************/
								/************************** Step2 Importing File to DB *******************************/
								/*************************************************************************************/
								echo('<script type="text/javascript">');
									echo('mmf_validate_step(1);');
									echo('mmf_processing_step(2);');
								echo('</script>');
								
								
								require_once('../../../../includes/fr/classV3/PHPExcel/Excel_r/oleread.inc');
								require_once('../../../../includes/fr/classV3/PHPExcel/Excel_r/reader.php');
								$data = new Spreadsheet_Excel_Reader();
								$data->setOutputEncoding('CP1251');
								//$file_full_path	= str_replace('\\','/',$file_full_path);
								$data->read($file_full_path);
								
								//Creation for a new operation
								$res_create_new_operation = $db->query("INSERT INTO supplier_update_info(id, advertiser,
																			id_operator_start, date_start,
																			date_end, id_operator_cancel,
																			date_cancel, total_product_manager,
																			total_product_file, total_product_affected,
																			total_product_file_not_found, total_product_manager_not_found,
																			total_price_manager, total_price_file,
																			etat)
																		values(NULL, ".$mmf_import_id_advertiser.",
																				".$user->id.", NOW(),
																				'0000-00-00 00:00:00', 0,
																				'0000-00-00 00:00:00', 0,
																				0, 0,
																				0, 0,
																				0, 0,
																				'en cours')", __FILE__, __LINE__);
								$id_operation	= mysql_insert_id();
								
								//echo('*** ID Operation: '.$id_operation.'<br />');
								
								
								$count_manager_products				= 0;
								$count_manager_price				= 0;
								$count_manager_not_found_producs	= 0;
								
								$count_file_products				= 0;
								$count_file_price					= 0;
								$count_file_not_found_producs		= 0;
								
								$count_products_affected			= 0;
								
								$different_price_pourcent			= 0;

								
								for ($x = 2; $x <= count($data->sheets[0]["cells"]); $x++) {
								
									$f_reference	 		= $data->sheets[0]["cells"][$x][$mmf_import_number_reference];
									$f_tarif				= $data->sheets[0]["cells"][$x][$mmf_import_number_tarif];
									$f_famille				= utf8_encode($data->sheets[0]["cells"][$x][$mmf_import_number_famille]);
									$f_nom					= utf8_encode($data->sheets[0]["cells"][$x][$mmf_import_number_nom]);
									$f_unite_vente			= utf8_encode($data->sheets[0]["cells"][$x][$mmf_import_number_unite_vente]);
									$f_quantite_par_carton	= utf8_encode($data->sheets[0]["cells"][$x][$mmf_import_number_quantite_carton]);
									$f_ecopart 				= $data->sheets[0]["cells"][$x][$mmf_import_number_ecopart];
								
									
									$f_tarif				=	str_replace(',','.',$f_tarif);
									$f_ecopart				=	str_replace(',','.',$f_ecopart);
									
									$clean_reference		= 	mmf_clean_reference($f_reference);
							
									//echo($f_reference.' - '.$f_tarif.' - '.$f_famille.' - '.$f_nom.' - '.$f_unite_vente.' - '.$f_quantite_par_carton.' ***<br />');

									//Search if this reference exist go to the next one!
									$res_check_existing_record = $db->query(" SELECT
																				id
																			FROM
																				supplier_update_info_details
																			WHERE
																				id_operation=".$id_operation."
																			AND
																				file_new_reference='".$clean_reference."'", __FILE__, __LINE__);
										
									if(mysql_num_rows($res_check_existing_record)==0){											
										if(!empty($f_reference) && !empty($f_tarif)){
											$res_insert_new_row = $db->query("INSERT INTO supplier_update_info_details(id, id_operation,
																					file_reference, 					file_new_reference,
																					file_price,							file_price2,
																					file_marge,							manager_price,
																					manager_price2,						file_ecopart,
																					manager_ecopart,					file_famille,
																					file_nom,							file_unite_vente,
																					file_quantite_par_carton)
																				values(NULL, ".$id_operation.",
																						'".addslashes($f_reference)."', '".$clean_reference."',
																						'".$f_tarif."', 				'',
																						'',								'',	
																						'',								'".$f_ecopart."',
																						'',								'".addslashes($f_famille)."',
																						'".addslashes($f_nom)."',		'".addslashes($f_unite_vente)."',
																						'".addslashes($f_quantite_par_carton)."')", __FILE__, __LINE__);
											
											$count_file_products++;
										}//end if(!empty($f_reference) && !empty($f_tarif)){
									}//end if(mysql_num_rows($res_check_existing_record)){
									
								}//End for fetching imported file 
								
								
								
								
								/*************************************************************************************/
								/******************* Step3 Looking for advertiser's products *************************/
								/*************************************************************************************/
								echo('<script type="text/javascript">');
									echo('mmf_validate_step(2);');
									echo('mmf_processing_step(3);');
								echo('</script>');
								
								
								$res_get_products_fournisseur = $db->query(" SELECT
																				ref_c.id, 					ref_c.refSupplier,															
																				ref_c.price, 				ref_c.price2,
																				ref_c.marge,				ref_c.ecotax
																	
																			FROM
																				references_content ref_c 
																					LEFT JOIN products_fr AS prod_fr ON ref_c.idProduct=prod_fr.id
																			WHERE
																				ref_c.sup_id='".$mmf_import_id_advertiser."'	
																			AND
																				ref_c.deleted=0
																			AND	
																				ref_c.classement!=0
																			AND
																				prod_fr.deleted=0
																			AND
																				prod_fr.active=1
																				", __FILE__, __LINE__);
								
								
								//Traitement test if =0 ==> update table table operation
								//else clean reference search compare update ref_c and increment vars	==> End update table operation
								
								
								/*************************************************************************************/
								/****** Step4 Cleaning manager product and search correspondance in file import ******/
								/*************************************************************************************/
								echo('<script type="text/javascript">');
									echo('mmf_validate_step(3);');
									echo('mmf_processing_step(4);');
								echo('</script>');
								
								$count_manager_products	= mysql_num_rows($res_get_products_fournisseur);
								if($count_manager_products>0){
								
									while($content_get_products_fournisseur = $db->fetchAssoc($res_get_products_fournisseur)){
										$m_reference	= mmf_clean_reference($content_get_products_fournisseur['refSupplier']);
										
										//Looking for correspondance file import
										
										$res_search_correspondance_products = $db->query(" SELECT
																							sup_up_idet.id, 			sup_up_idet.file_price,															
																							sup_up_idet.file_ecopart
																	
																						FROM
																							supplier_update_info_details sup_up_idet
																						WHERE
																							sup_up_idet.file_new_reference='".$m_reference."'	
																						AND
																							sup_up_idet.id_operation=".$id_operation."", __FILE__, __LINE__);
													
										
										if(mysql_num_rows($res_search_correspondance_products)>0){
											$content_search_correspondance_products = $db->fetchAssoc($res_search_correspondance_products);
											//if correspondance exist
											//Update table "supplier_update_info_details" 	*id_tc		*manager_price		*manager_ecopart
											//Update table "references_content"		*price1 || *price2
											//Increment vars
											
											
											$copy_manager_price		= 0;
											$query_field			= '';
											$calculating_new_price	= 0;
											$marge_to_apply			= 0;
											
											$copy_manager_ecotax	= 0;
											if(strcmp($content_get_advertiser['prixPublic'],'0')=='0'){
												//Case fournisseur 	=> price2
												//We store the 4 values old manger price && price2 
												//&& File price && calculate price2 on depend if it's a public or fournisseur
												//$copy_manager_price		= $content_get_products_fournisseur['price2'];
												//$copy_manager_price2	= $content_get_products_fournisseur['price'];
												$copy_manager_price		= $content_get_products_fournisseur['price'];
												$copy_manager_price2	= $content_get_products_fournisseur['price2'];
												$query_field_operation1	= 'price2';
												$query_field_operation2	= 'price';
												
												$copy_manager_ecotax	= $content_get_products_fournisseur['ecotax']; 
												
												//Sum manager price
												$count_manager_price	+= 	$copy_manager_price;
												
												
												//Test if the marge is negatif => take the advertiser marge
												if($content_get_products_fournisseur['marge']<0){
													$marge_to_apply	=	$content_get_advertiser['margeRemise'];
												}else{
													$marge_to_apply	=	$content_get_products_fournisseur['marge'];
												}
												$calculating_new_price	= mmf_calculate_marge_or_remise($content_search_correspondance_products['file_price'], 'marge', $marge_to_apply);
											}else{
												//Case public 		=> price1
												//We store the 4 values old manger price && price2 
												//&& File price && calculate price2 on depend if it's a public or fournisseur
												$copy_manager_price		= $content_get_products_fournisseur['price'];
												$copy_manager_price2	= $content_get_products_fournisseur['price2'];
												$query_field_operation1	= 'price';
												$query_field_operation2	= 'price2';
												
												$copy_manager_ecotax	= $content_get_products_fournisseur['ecotax']; 
												
												//Sum manager price
												$count_manager_price	+= 	$copy_manager_price;
												
												
												//Test if the marge is negatif => take the advertiser marge
												if($content_get_products_fournisseur['marge']<0){
													$marge_to_apply	=	$content_get_advertiser['margeRemise'];
												}else{
													$marge_to_apply	=	$content_get_products_fournisseur['marge'];
												}
												$calculating_new_price	= mmf_calculate_marge_or_remise($content_search_correspondance_products['file_price'], 'remise', $marge_to_apply);
											}

											//Add ecotax (ecopart) 
											//$calculating_new_price = $calculating_new_price+ $content_search_correspondance_products['file_ecopart'];
											
											//Sum file price
											$count_file_price	+= 	$content_search_correspondance_products['file_price'];
											
											//Query update product imported (save the old manager price)
											//Query update product in manager
											$res_update_file_products = $db->query("UPDATE supplier_update_info_details sup_up_idet
																					SET 
																						sup_up_idet.manager_price='".$copy_manager_price."',
																						sup_up_idet.manager_price2='".$copy_manager_price2."',
																						
																						sup_up_idet.manager_ecopart='".$copy_manager_ecotax."',
																						
																						sup_up_idet.manager_marge='".$content_get_products_fournisseur['marge']."',
																						sup_up_idet.file_price2='".$calculating_new_price."',
																						sup_up_idet.file_marge='".$marge_to_apply."'
																					WHERE
																						sup_up_idet.id=".$content_search_correspondance_products['id']."
																					AND	
																						sup_up_idet.id_operation=".$id_operation."", __FILE__, __LINE__);
											
											
											$res_insert_new_correspondance = $db->query("INSERT INTO supplier_update_info_correspondance
																								(id, id_tc, 
																								id_file, id_operation)
																							VALUES
																								(NULL, ".$content_get_products_fournisseur['id'].", 
																								".$content_search_correspondance_products['id'].", ".$id_operation."
																								)", __FILE__, __LINE__);
											
		

//Update On 09/02/2015 10h49M
//Check Only the IDTC
											if(empty($content_search_correspondance_products['file_ecopart'])){
												$ecotax = '0.00';
											}else {
												$ecotax = $content_search_correspondance_products['file_ecopart'];
											}
											$res_update_manager_products = $db->query("UPDATE references_content ref_c
																					SET 
																						".$query_field_operation1."=".$content_search_correspondance_products['file_price'].",
																						".$query_field_operation2."=".$calculating_new_price.",
																						ecotax=".$ecotax." 
																					WHERE
																						ref_c.id=".$content_get_products_fournisseur['id']."", __FILE__, __LINE__);
			

/*											
											$res_update_manager_products = $db->query("UPDATE references_content ref_c
																					SET 
																						".$query_field_operation1."=".$content_search_correspondance_products['file_price'].",
																						".$query_field_operation2."=".$calculating_new_price."
																					WHERE
																						ref_c.id=".$content_get_products_fournisseur['id']."
																					AND	
																						ref_c.refSupplier='".$content_get_products_fournisseur['refSupplier']."'", __FILE__, __LINE__);
	
*/

	
																						
											//Increment the number of affected product
											$count_products_affected++;
											
										}else{
											//Obsolete product
											//Exist in the Manager and not found in the file									
											$count_manager_not_found_producs++;
										}

			
									}//End while fetching product manager
								}//end if detect existing manager product
								
														
								
								/*************************************************************************************/
								/*************************** Step5 Finalisation process ******************************/
								/*************************************************************************************/
								echo('<script type="text/javascript">');
									echo('mmf_validate_step(4);');
									echo('mmf_processing_step(5);');
								echo('</script>');
								
							
								//Calcul produits nouveaux 
								$res_get_new_products = $db->query("SELECT	
																		count(sup_up_id.id) ref_c_nouveaux
																	FROM
																		supplier_update_info_details sup_up_id
																	WHERE
																		sup_up_id.id_operation=".$id_operation."
																	AND
																		sup_up_id.id NOT IN (SELECT
																								id_file
																							FROM
																								supplier_update_info_correspondance sup_uic
																							WHERE
																								sup_uic.id_operation=".$id_operation.")
																		", __FILE__, __LINE__);
								$content_get_new_products = $db->fetchAssoc($res_get_new_products);	
								$count_file_not_found_producs	= 	$content_get_new_products['ref_c_nouveaux'];	
								
								
								//Calculating the number of affected products
								/*$res_get_products_affected = $db->query("SELECT
																			count(sup_up_id.id) ref_c_affected
																		FROM
																			supplier_update_info_details sup_up_id
																		WHERE
																			sup_up_id.id_operation=".$id_operation."	
																		AND
																			sup_up_id.id_tc!=0", __FILE__, __LINE__);
								$content_get_products_affected 	= $db->fetchAssoc($res_get_products_affected);	
								$count_products_affected	= 	$content_get_products_affected['ref_c_affected'];*/
								
								
								//Calculating the total manager price
								//Calculating the total file price
								/*if(strcmp($content_get_advertiser['prixPublic'],'0')=='0'){
									$query_field_operation	= 'manager_price2';
								}else{
									$query_field_operation	= 'manager_price';
								}
								
								$res_get_file_price = $db->query("SELECT	
																			SUM(sup_up_id.file_price) count_file_price
																		FROM	
																			supplier_update_info_details sup_up_id
																		WHERE
																			sup_up_id.id_operation=".$id_operation."
																		AND
																			sup_up_id.id_tc!=0", __FILE__, __LINE__);
								$content_get_file_price 	= $db->fetchAssoc($res_get_file_price);	
								$count_file_price	= 	$content_get_file_price['count_file_price'];
								
								
								$res_get_manager_price = $db->query("SELECT	
																			SUM(sup_up_id.".$query_field_operation.") count_manager_price
																		FROM	
																			supplier_update_info_details sup_up_id
																		WHERE
																			sup_up_id.id_operation=".$id_operation."
																		AND
																			sup_up_id.id_tc!=0", __FILE__, __LINE__);
								$content_get_manager_price 	= $db->fetchAssoc($res_get_manager_price);	
								$count_manager_price	= 	$content_get_manager_price ['count_manager_price'];
								*/
								
								
								//Updating to save the operation stats
								$res_update_manager_products = $db->query("UPDATE supplier_update_info sup_upi
																			SET 
																				sup_upi.date_end=NOW(),
																				sup_upi.total_product_manager=".$count_manager_products.",
																				sup_upi.total_product_file=".$count_file_products.",
																				sup_upi.total_product_affected=".$count_products_affected.",
																				sup_upi.total_product_file_not_found=".$count_file_not_found_producs.",
																				sup_upi.total_product_manager_not_found=".$count_manager_not_found_producs.",
																				sup_upi.total_price_manager=".$count_manager_price.",
																				sup_upi.total_price_file=".$count_file_price.",
																				sup_upi.etat='finalise'
																				
																			WHERE
																				sup_upi.id=".$id_operation."
																			AND	
																				sup_upi.advertiser=".$mmf_import_id_advertiser."
																			AND	
																				sup_upi.id_operator_start=".$user->id."", __FILE__, __LINE__);
																				
												

								/*************************************************************************************/
								/**************** Step5 Finalisation process & creation internal_note ****************/
								/*************************************************************************************/
								echo('<script type="text/javascript">');
									echo('mmf_validate_step(5);');
									echo('mmf_processing_step(6);');
								echo('</script>');
								
										//Looking for the exact Operation Date
										
										
										$res_get_operation_date = $db->query(" SELECT
																					sup_upi.date_start,	sup_upi.date_end
																				FROM
																					supplier_update_info sup_upi
																				WHERE
																					sup_upi.id=".$id_operation."", __FILE__, __LINE__);
																
										$content_get_operation_date = $db->fetchAssoc($res_get_operation_date);
					
					
										//Create a new note !	Fournisseur => context:6

										

										$different_price_pourcent	= mmf_percent_difference_price($count_file_price, $count_manager_price);
										$internal_note_content		= 'Fournisseur mis &agrave; jour le '.$content_get_operation_date['date_start'].'<br />';
										$internal_note_content		.= $count_products_affected.' r&eacute;f&eacute;rences TC mises &agrave; jour <br />';
										//$internal_note_content		.= 'Modification global de tarif : '.$different_price_pourcent.' %';
										
										
										echo('<script type="text/javascript">');
											echo('mmf_add_internal_note('.$mmf_import_id_advertiser.', \'6\', \''.addslashes($internal_note_content).'\');');
										echo('</script>');
										
								
								//Need the $id_operation
								require_once('import_results.php');
								
								//Call external file result operation
								//Call external file last 5 imports for this advertiser
					
							}//end else if empty 
						
						
						} catch (Exception $e) {
							echo("Erreur fatale: ".$e->getMessage());
						}
					?>
			
				</div><!-- end div #mmf_import_results -->
				
			</div><!-- end div .bg -->
		</div><!-- end div #module_maj_fournisseurs -->
		

		
<?php 
	}else{ 
?>
		<div class="bg" style="position: relative">
			<h2>Vous n'avez pas les droits adéquats pour réaliser cette opération.</h2>
		</div>
<?php
	}
?>
<?php require(ADMIN.'tail.php') ?>