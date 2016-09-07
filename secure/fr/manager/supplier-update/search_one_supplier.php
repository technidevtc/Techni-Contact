<?php

	if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
		require_once '../../../../config.php';
	}else{
		require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
	}

	$db = DBHandle::get_instance();
	$user = new BOUser();
	
	try {
	  if (!$user->login())
		throw new Exception("Votre session a expiré, veuillez vous identifier à nouveau après avoir rafraîchi votre page.");
		
		//User permissions !
		$userPerms = $user->get_permissions();

		$nom_fournisseur	= mysql_real_escape_string(trim($_POST['nom_fournisseur']));
		
		if(!empty($nom_fournisseur)){

			$res_autocomplete_fournisseur = $db->query(" SELECT
															adv.id, 					adv.idCommercial,
															adv.nom1 as name, 			adv.nom2,
															adv.adresse1, 				adv.adresse2,
															adv.cp,						adv.ville,
															adv.email,
															
															adv.ncontact nom_contact, 	adv.pcontact prenom_contact,
															adv.econtact email_contact, adv.tel1 telephone1,
															
															adv.prixPublic,				adv.margeRemise
															
															
														FROM
															advertisers adv
														WHERE
															adv.nom1='".$nom_fournisseur."'	

														LIMIT 0,10", __FILE__, __LINE__);
			
			$result_count	= mysql_num_rows($res_autocomplete_fournisseur);
			if($result_count>1){
			
				echo('Il existe plusieurs fournisseurs avec le m&ecirc;me nom !');
			
			}else if($result_count==1){
			
				$content_autocomplete_fournisseur = $db->fetchAssoc($res_autocomplete_fournisseur);
				
				echo('<div id="mmf_one_fournisseur_infos_basic_container">');
					echo('<div class="bg">');
					
					/*****************************/
						echo('<div id="mmf_one_fournisseur_infos_basic_content">');
							echo('<input type="hidden" id="mmf_import_id_advertiser_returned" value="'.$content_autocomplete_fournisseur['id'].'" />');
						
							echo('<div class="one_bloc">');
					
								echo('<div class="titreBloc">');
									echo('Fiche identit&eacute; du fournisseur');
								echo('</div>');
								
								echo('<div class="coordCustomer">');
								
									echo('<div class="mmf_one_row">');
										echo('<div class="mmf_left">');
											echo('Identifiant :');
										echo('</div>');
										echo('<div class="mmf_right">');
											echo($content_autocomplete_fournisseur['id']);
										echo('</div>');
									echo('</div>');//end div .mmf_one_row
									
									echo('<div class="mmf_one_row">');
										echo('<div class="mmf_left">');
											echo('Nom :');
										echo('</div>');
										echo('<div class="mmf_right">');
											echo($content_autocomplete_fournisseur['name']);
										echo('</div>');
									echo('</div>');//end div .mmf_one_row
									
									echo('<div class="mmf_one_row">');
										echo('<div class="mmf_left">');
											echo('Nom du contact :');
										echo('</div>');
										echo('<div class="mmf_right">');
											echo($content_autocomplete_fournisseur['nom_contact']);
										echo('</div>');
									echo('</div>');//end div .mmf_one_row
									
									echo('<div class="mmf_one_row">');
										echo('<div class="mmf_left">');
											echo('Pr&eacute;nom du contact :');
										echo('</div>');
										echo('<div class="mmf_right">');
											echo($content_autocomplete_fournisseur['prenom_contact']);
										echo('</div>');
									echo('</div>');//end div .mmf_one_row
									
									echo('<div class="mmf_one_row">');
										echo('<div class="mmf_left">');
											echo('Email du contact :');
										echo('</div>');
										echo('<div class="mmf_right">');
											echo($content_autocomplete_fournisseur['email_contact']);
										echo('</div>');
									echo('</div>');//end div .mmf_one_row
									
									echo('<div class="mmf_one_row">');
										echo('<div class="mmf_left">');
											echo('T&eacute;l&eacute;phone du contact :');
										echo('</div>');
										echo('<div class="mmf_right">');
											echo($content_autocomplete_fournisseur['telephone1']);
										echo('</div>');
									echo('</div>');//end div .mmf_one_row
									
									echo('<div class="mmf_one_row">');
										echo('<div class="mmf_left">');
											echo('Adresse :');
										echo('</div>');
										echo('<div class="mmf_right">');
											echo($content_autocomplete_fournisseur['adresse1'].' <br />'.$content_autocomplete_fournisseur['cp'].' - '.$content_autocomplete_fournisseur['ville']);
										echo('</div>');
									echo('</div>');//end div .mmf_one_row
									
									echo('<div class="mmf_one_row">');
										echo('<div class="mmf_left">');
											echo('Mail :');
										echo('</div>');
										echo('<div class="mmf_right">');
											echo($content_autocomplete_fournisseur['email']);
										echo('</div>');
									echo('</div>');//end div .mmf_one_row
								
								echo('</div>');//end div .coordCustomer
								
							echo('</div>');//end div .one_bloc
							
							
							
							// *****************
							// Start Second part
							// *****************
							echo('<div class="one_bloc_c">');
								
								echo('<div class="titreBloc">');
									echo('Produits');
								echo('</div>');
								
								echo('<div class="coordCustomer">');
									
									echo('<div class="mmf_one_row">');
										echo('<div class="mmf_left">');
											echo('Nombre de fiches produits :');
										echo('</div>');
										echo('<div class="mmf_right">');
											
											$res_count_nb_products = $db->query(" SELECT
																						count(prod_fr.id) count_prod_fr

																					FROM
																						products_fr prod_fr
																					WHERE
																						prod_fr.idAdvertiser=".$content_autocomplete_fournisseur['id']."	
																					AND
																						prod_fr.deleted=0
																					AND
																						prod_fr.active=1", __FILE__, __LINE__);
										
											$content_count_nb_products = $db->fetchAssoc($res_count_nb_products);
										
											echo($content_count_nb_products['count_prod_fr']);
											
										echo('</div>');
									echo('</div>');//end div .mmf_one_row
									
									echo('<div class="mmf_one_row">');
										echo('<div class="mmf_left">');
											echo('Nombre de ref TC :');
										echo('</div>');
										echo('<div class="mmf_right">');
											
											$res_count_ref_tc = $db->query(" SELECT
																				count(ref_c.id) count_id

																			FROM
																				references_content ref_c,
																				products_fr prod_fr
																			WHERE
																				ref_c.idProduct=prod_fr.id
																			AND
																				ref_c.sup_id=".$content_autocomplete_fournisseur['id']."	
																			AND
																				ref_c.deleted=0
																			AND	
																				ref_c.classement!=0
																			AND	
																				prod_fr.deleted=0
																			AND
																				prod_fr.active=1", __FILE__, __LINE__);
										
											$content_count_ref_tc = $db->fetchAssoc($res_count_ref_tc);
										
											echo($content_count_ref_tc['count_id']);
												
										echo('</div>');
									echo('</div>');//end div .mmf_one_row
									
									echo('<div class="mmf_one_row">');
										echo('<div class="mmf_left">');
											echo('Nombre de ref fournisseur distinctes :');
										echo('</div>');
										echo('<div class="mmf_right">');
											
											$res_count_nb_fournisseurs_distinct = $db->query(" SELECT
																					count(DISTINCT ref_c.refSupplier) count_refSup

																				FROM
																					references_content ref_c,
																					products_fr prod_fr
																				WHERE
																					ref_c.idProduct=prod_fr.id
																				AND
																					ref_c.sup_id=".$content_autocomplete_fournisseur['id']."
																				AND
																					ref_c.deleted=0
																				AND	
																					ref_c.classement!=0
																				AND	
																					prod_fr.deleted=0
																				AND
																					prod_fr.active=1
																					
																					", __FILE__, __LINE__);
										
											$content_count_nb_fournisseurs_distinct = $db->fetchAssoc($res_count_nb_fournisseurs_distinct);
										
											echo($content_count_nb_fournisseurs_distinct['count_refSup']);
											
										echo('</div>');
									echo('</div>');//end div .mmf_one_row
									
									echo('<div class="mmf_one_row">');
										echo('<div class="mmf_left">');
											echo('Nombre de familles impact&eacute;es distinctes :');
										echo('</div>');
										echo('<div class="mmf_right">');
											
											$res_count_nb_familles_impacte = $db->query(" SELECT
																					count(DISTINCT prod_fam.idFamily) count_idFamily

																				FROM
																					products_fr prod_fr,
																					products_families prod_fam,
																					references_content ref_c
																				WHERE
																					ref_c.idProduct=prod_fr.id
																				AND
																					ref_c.deleted=0
																				AND	
																					ref_c.classement!=0
																				AND
																					prod_fr.idAdvertiser=".$content_autocomplete_fournisseur['id']."	
																				AND
																					prod_fr.id=prod_fam.idProduct
																				
																				AND
																					prod_fr.deleted=0
																				AND
																					prod_fr.active=1", __FILE__, __LINE__);
										
											$content_count_nb_familles_impacte = $db->fetchAssoc($res_count_nb_familles_impacte);
										
											echo($content_count_nb_familles_impacte['count_idFamily']);
											
										echo('</div>');
									echo('</div>');//end div .mmf_one_row
							
								echo('</div>');//end div .coordCustomer
							
							echo('</div>');//end div .one_bloc
							
							
							
							// *****************
							// Start Third part
							// *****************
							echo('<div class="one_bloc_c">');	
								echo('<div class="titreBloc">');
									echo('Prix');
								echo('</div>');
								
								echo('<div class="coordCustomer">');
									
									echo('<div class="mmf_one_row">');
										echo('<div class="mmf_left">');
											echo('Type de prix par d&eacute;faut :');
										echo('</div>');
										echo('<div class="mmf_right">');
											
											if(strcmp($content_autocomplete_fournisseur['prixPublic'],'0')=='0'){
												echo('Fournisseur');
											}else{
												echo('Public');
											}
											
										echo('</div>');
									echo('</div>');//end div .mmf_one_row
									
									echo('<div class="mmf_one_row">');
										echo('<div class="mmf_left">');
											echo('Marge ou remise par d&eacute;faut :');
										echo('</div>');
										echo('<div class="mmf_right">');
										
											if(strcmp($content_autocomplete_fournisseur['prixPublic'],'0')=='0'){
												echo($content_autocomplete_fournisseur['margeRemise'].'% de marge');
											}else{
												echo($content_autocomplete_fournisseur['margeRemise'].'% de remise');
											}
											
										echo('</div>');
									echo('</div>');//end div .mmf_one_row
									
									/*
									echo('<div class="mmf_one_row">');
										echo('<div class="mmf_left">');
											echo('Prix d\'achat moyen HT des produits :');
										echo('</div>');
										echo('<div class="mmf_right">');
											
											$res_avg_price_buy = $db->query(" SELECT
																				ROUND(AVG(price2),2) avg_buy_price

																			FROM
																				references_content ref_c
																			WHERE
																				ref_c.sup_id=".$content_autocomplete_fournisseur['id']."	
																			AND
																				ref_c.deleted=0
																			AND
																				ref_c.classement!=0
																			", __FILE__, __LINE__);
										
											$content_avg_price_buy = $db->fetchAssoc($res_avg_price_buy);
										
											echo($content_avg_price_buy['avg_buy_price'].' &euro;');
											
										echo('</div>');
									echo('</div>');//end div .mmf_one_row
									
									echo('<div class="mmf_one_row">');
										echo('<div class="mmf_left">');
											echo('Prix public moyen HT :');
										echo('</div>');
										echo('<div class="mmf_right">');
											
											$res_avg_public_price = $db->query(" SELECT
																					ROUND(AVG(price),2) avg_public_price

																				FROM
																					references_content ref_c
																				WHERE
																					ref_c.sup_id=".$content_autocomplete_fournisseur['id']."	
																				AND
																					ref_c.deleted=0
																				AND
																					ref_c.classement!=0
																				", __FILE__, __LINE__);
										
											$content_avg_public_price = $db->fetchAssoc($res_avg_public_price);
										
											echo($content_avg_public_price['avg_public_price'].' &euro;');
											
										echo('</div>');
									echo('</div>');//end div .mmf_one_row
									*/
							
								echo('</div>');//end div .coordCustomer
							
							echo('</div>');//end div .one_bloc
							
							
							
						echo('</div>'); //end #mmf_one_fournisseur_infos_basic_content
						
						
						
						//Export button
						if($userPerms->has("m-prod--sm-maj-fournisseurs", "x")){
						//if (!$userPerms->has("m-prod--sm-partners","x")){
							echo('<div id="adv_export_div_btn">');
								echo('<a href="/fr/manager/export-advertisers/export_products.php?id='.$content_autocomplete_fournisseur['id'].'" target="_blank" class="bouton">Exporter les produits</a>');
							echo('</div>');
							echo('<br />');
							
						}


						
						
					echo('</div>');//end div .bg
				echo('</div>');//end #mmf_one_fournisseur_infos_basic
				
				
			
			}//end if(mysql_num_rows($res_autocomplete_fournisseur)>0){

		}//end if(!empty($nom_fournisseur)){

	}catch(Exception $e){
		echo($e);
	}

?>