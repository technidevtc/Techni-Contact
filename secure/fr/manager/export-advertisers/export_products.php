<?php
	if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
		require_once '../../../../config.php';
	}else{
		require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
	}
	
	require_once 'functions_export.php';
	
	$db 	= DBHandle::get_instance();
	$user 	= new BOUser();
	
	$id		= mysql_real_escape_string(trim($_GET['id']));
	
	try{
	
		if(!$user->login())
			throw new Exception("Votre session a expiré, veuillez vous identifier à nouveau après avoir rafraîchi votre page.");
			
		//User permissions !
		$userPerms = $user->get_permissions();
		if(!$userPerms->has("m-prod--sm-maj-fournisseurs", "x") || !$userPerms->has("m-prod--sm-partners", "x"))
						throw new Exception("Vous n'avez pas les droits adéquats pour réaliser cette opération. 1");
			
			
		if(!empty($id)){

			//Detecting if this advertiser is a fournisseurs
			
			$res_get_category_fournisseur = $db->query("SELECT
															adv.id, 					adv.category															
															
														FROM
															advertisers adv
														WHERE
															adv.id='".$id."'", __FILE__, __LINE__);
			
			if(mysql_num_rows($res_get_category_fournisseur)>0){
			
				$content_get_category_fournisseur = $db->fetchAssoc($res_get_category_fournisseur);
				
				if(strcmp($content_get_category_fournisseur['category'],'1')=='0'){

					$res_get_informations = $db->query("SELECT
															adv.id 				AS id_partenaire,	
															adv.nom1 			AS nom_partenaire,
															adv.prixPublic 		AS prix_public,

															ref_c.id 			AS id_tc,
															ref_c.label			AS libelle,
															ref_c.classement	AS classement,
															ref_c.content		AS content,
															ref_c.marge			AS ref_c_marge_remise,
															ref_c.refSupplier	AS ref_fournisseur,
															ref_c.price			AS ref_price,
															ref_c.price2		AS ref_price2,
                              ref_c.ecotax    AS ref_ecotax,

															prod_fr.id 			AS id_fiche,
															prod_fr.name		AS nom_fiche_produit,
															prod_fr.descc		AS prod_fr_descc,
															prod_fr.descd		AS prod_fr_descd,
															prod_fr.alias		AS prod_fr_alias,
															prod_fr.keywords	AS prod_fr_keywords,

															prod.ean			AS prod_ean,
															prod.warranty		AS prod_warranty,
															prod.title_tag		AS prod_title_tag,
															prod.meta_desc_tag 	AS prod_meta_desc_tag,
															FROM_UNIXTIME(prod.timestamp) 	AS date_derniere_maj,
																
															prod_fm.idFamily 	AS id_famille_3,
															fm_fr.name 			AS nom_famille_3,
															fm.idParent			AS id_parent_fm3,
															
															tva.intitule		AS tva_intitule
															
														FROM
															`references_content` ref_c
															INNER JOIN advertisers 		adv ON adv.id = ref_c.sup_id
															INNER JOIN products 		prod ON prod.id = ref_c.idProduct 
															
															INNER JOIN products_fr 		prod_fr ON prod_fr.id = ref_c.idProduct
															INNER JOIN products_families 	prod_fm ON prod_fm.idProduct = ref_c.idProduct
															INNER JOIN families_fr 		fm_fr ON prod_fm.idFamily = fm_fr.id
															INNER JOIN families 		fm ON prod_fm.idFamily = fm.id
															
															INNER JOIN tva				ON tva.id=ref_c.idTVA
															
														WHERE

															ref_c.sup_id=".$id."
														AND

															(
																prod_fr.active = 1
															AND 
																prod_fr.deleted = 0
															AND 
																ref_c.deleted= 0
															AND 
																ref_c.vpc=1
															)
															
														ORDER BY prod.timestamp DESC", __FILE__, __LINE__);
					
					
					if(mysql_num_rows($res_get_informations)>0){
					
						$name_file	= 'export-products-'.$id.'-'.date('d-m-Y_H-i-s');
						
						header('Content-Description: File Transfer');
						header("Content-type: application/vnd.ms-excel");
						header("Content-disposition: csv" . date("Y-m-d") . ".csv");
						header("Content-disposition: filename=".$name_file.".csv");
						header('Content-Transfer-Encoding: binary');
						header('Pragma: public');
						//echo "\xEF\xBB\xBF"; // UTF-8 BOM
						
						
					
						$export_data	= " identifiant fournisseur;";
						$export_data	.= " Nom fournisseur;";
						$export_data	.= " ID TC;";
						
						$export_data	.= " Classement;";
						$export_data	.= " Date dernière MAJ;";
						$export_data	.= " ID fiche;";
						
						$export_data	.= " Nom de la fiche produit;";
						$export_data	.= " Description;";
						$export_data	.= " Description technique; ";
						
						$export_data	.= " Code EAN;";
						$export_data	.= " Garantie;";
						$export_data	.= " SEO title;";

						$export_data	.= " SEO meta desc;";
						$export_data	.= " Alias;";
						$export_data	.= " Mots cles;";

						$export_data	.= " ID famille 1;";
						$export_data	.= " Nom Famille 1;";
						$export_data	.= " ID famille 2;";

						$export_data	.= " Nom Famille 2;";
						$export_data	.= " ID famille 3;";
						$export_data	.= " Nom Famille 3;";
						
						$export_data	.= " Libelle;";
						$export_data	.= " Colonnes tableaux prix;";
						$export_data	.= " TVA;";
						
						$export_data	.= " Marge ou remise;";
						$export_data	.= " Type prix par defaut;";
						$export_data	.= " Ref fournisseur;";
						
						$export_data	.= " Prix public;";//Price
						$export_data	.= " Prix fournisseur;";//Price2
            $export_data	.= " Éco Taxe";
						
						$export_data	.= "\n";

						
						while($content_get_informations = $db->fetchAssoc($res_get_informations)){
					
							$export_data	.= $content_get_informations['id_partenaire'].";";
							$export_data	.= clean_export($content_get_informations['nom_partenaire']).";";
							$export_data	.= $content_get_informations['id_tc'].";";
							
							$export_data	.= $content_get_informations['classement'].";";
							$export_data	.= $content_get_informations['date_derniere_maj'].";";
							$export_data	.= $content_get_informations['id_fiche'].";";
							
							$export_data	.= clean_export($content_get_informations['nom_fiche_produit']).";";
							$export_data	.= clean_export_description($content_get_informations['prod_fr_descc']).";";
							$export_data	.= clean_export_description($content_get_informations['prod_fr_descd']).";";
							
							$export_data	.= clean_export($content_get_informations['prod_ean']).";";
							$export_data	.= clean_export($content_get_informations['prod_warranty']).";";
							$export_data	.= clean_export($content_get_informations['prod_title_tag']).";";
							
							$export_data	.= clean_export($content_get_informations['prod_meta_desc_tag']).";";
							$export_data	.= clean_export($content_get_informations['prod_fr_alias']).";";
							$export_data	.= clean_export($content_get_informations['prod_fr_keywords']).";";
							
							//Looking for the Famille level 2 & 1
							//Level 2
							$res_get_families_level2 = $db->query("SELECT
																		fm.id,
																		fm.idParent,
																		fm_fr.name
																	FROM
																		families fm
																		INNER JOIN families_fr fm_fr ON fm.id=fm_fr.id
																	WHERE
																		fm.id=".$content_get_informations['id_parent_fm3']."
							
																	", __FILE__, __LINE__);
							
							$content_get_families_level2 = $db->fetchAssoc($res_get_families_level2);
							
							
							//Level 1
							$res_get_families_level1 = $db->query("SELECT
																		fm.id,
																		fm.idParent,
																		fm_fr.name
																	FROM
																		families fm
																		INNER JOIN families_fr fm_fr ON fm.id=fm_fr.id
																	WHERE
																		fm.id=".$content_get_families_level2['id']."
							
																	", __FILE__, __LINE__);
							$content_get_families_level1 = $db->fetchAssoc($res_get_families_level1);
							
																	
							//Get references Header by product
							$res_get_references_header = $db->query("SELECT
																		ref_cols.content ref_headers
																	FROM
																		references_cols ref_cols
																	WHERE
																		ref_cols.idProduct=".$content_get_informations['id_fiche']."
							
																	", __FILE__, __LINE__);
							$content_get_references_header = $db->fetchAssoc($res_get_references_header);										
							
							
							
							$export_data	.= $content_get_families_level1['id'].";";
							$export_data	.= clean_export($content_get_families_level1['name']).";";
							
							
							$export_data	.= $content_get_families_level2['id'].";";
							$export_data	.= clean_export($content_get_families_level2['name']).";";
							$export_data	.= $content_get_informations['id_famille_3'].";";
							
							
							$export_data	.= clean_export($content_get_informations['nom_famille_3']).";";
							$export_data	.= clean_export($content_get_informations['libelle']).";";

							$export_data	.= clean_references_arrays($content_get_references_header['ref_headers'],$content_get_informations['content']).";";
							
							$export_data	.= clean_export($content_get_informations['tva_intitule']).";";
							$export_data	.= $content_get_informations['ref_c_marge_remise'].";";
							if(strcmp($content_get_informations['prix_public'],'0')=='0'){
								$export_data	.= "Marge;";
							}else{
								$export_data	.= "Remise;";
							}
							
							$export_data	.= clean_export($content_get_informations['ref_fournisseur']).";";
							
							//Voir si c Public ou fournisseur
							//Price		=> Prix public
							//Price2	=> Prix fournisseur
							$export_data	.= clean_export($content_get_informations['ref_price']).";";
							$export_data	.= clean_export($content_get_informations['ref_price2']).";";
              $export_data	.= clean_export($content_get_informations['ref_ecotax']).";";
							
							
							$export_data	.= "\n";
							
							print $export_data;
							$export_data	= '';
						}//end while
						
						
						exit;
						
						/*$name_file	= 'export-products-'.$id.'-'.date('d-m-Y_H-i-s');
						
						header('Content-Description: File Transfer');
						header("Content-type: application/vnd.ms-excel");
						header("Content-disposition: csv" . date("Y-m-d") . ".csv");
						header("Content-disposition: filename=".$name_file.".csv");
						header('Content-Transfer-Encoding: binary');
						header('Pragma: public');
						//echo "\xEF\xBB\xBF"; // UTF-8 BOM
						
						//header("Content-type: application/vnd.ms-excel");
						//header("Content-disposition: attachment; filename=".$name_file.".csv");
						print $export_data;
						exit;*/
					
					}else{
						throw new Exception("Aucun produit &agrave; t&eacute;l&eacute;lecharger.");
					}
					
				}else{
				
					//if it is not a group=1
					$res_get_informations = $db->query("SELECT
															adv.id AS id_partenaire,	
															adv.nom1 AS nom_partenaire,
															
															FROM_UNIXTIME(prod.timestamp) 	AS date_derniere_maj,	

															prod_fr.id 			AS id_fiche,
															prod_fr.name		AS nom_fiche_produit,
															prod_fr.descc		AS prod_fr_descc,
															prod_fr.descd		AS prod_fr_descd,
															prod_fr.alias		AS prod_fr_alias,
															prod_fr.keywords	AS prod_fr_keywords,

															prod.ean			AS prod_ean,
															prod.warranty		AS prod_warranty,
															prod.title_tag		AS prod_title_tag,
															prod.meta_desc_tag 	AS prod_meta_desc_tag,
															
															prod_fm.idFamily 	AS id_famille_3,
															fm_fr.name 			AS nom_famille_3,
															fm.idParent			AS id_parent_fm3
															
														FROM
															products 		prod
															INNER JOIN advertisers 		adv ON adv.id = prod.idAdvertiser 
															
															INNER JOIN products_fr 		prod_fr ON prod_fr.id = prod.id
															INNER JOIN products_families 	prod_fm ON prod_fm.idProduct = prod.id
															INNER JOIN families_fr 		fm_fr ON prod_fm.idFamily = fm_fr.id
															INNER JOIN families 		fm ON prod_fm.idFamily = fm.id
															
														WHERE

															prod.idAdvertiser=".$id."
														AND

															(
																prod_fr.active = 1
															AND 
																prod_fr.deleted = 0
															)
														ORDER BY prod.timestamp DESC", __FILE__, __LINE__);
															
					if(mysql_num_rows($res_get_informations)>0){
					
						$name_file	= 'export-products-'.$id.'-'.date('d-m-Y_H-i-s');
						
						header('Content-Description: File Transfer');
						header("Content-type: application/vnd.ms-excel");
						header("Content-disposition: csv" . date("Y-m-d") . ".csv");
						header("Content-disposition: filename=".$name_file.".csv");
						header('Content-Transfer-Encoding: binary');
						header('Pragma: public');
						//echo "\xEF\xBB\xBF"; // UTF-8 BOM
						
						
						$export_data	= " identifiant partenaire; Nom partenaire; Date dernière MAJ;";
						$export_data	.= " ID fiche ;Nom de la fiche produit; Description;";
						$export_data	.= " Description technique; Code EAN; Garantie;";
						$export_data	.= " SEO title; SEO meta desc; Alias;";
						$export_data	.= " Mots cles; ID famille 1; Nom Famille 1;";
						$export_data	.= " ID famille 2; Nom Famille 2; ID famille 3;";
						$export_data	.= " Nom Famille 3";
						$export_data	.= "\n";

						
						while($content_get_informations = $db->fetchAssoc($res_get_informations)){
						
							$export_data	.= $content_get_informations['id_partenaire'].";";
							$export_data	.= clean_export($content_get_informations['nom_partenaire']).";";
							$export_data	.= $content_get_informations['date_derniere_maj'].";";
							
							$export_data	.= $content_get_informations['id_fiche'].";";
							$export_data	.= clean_export($content_get_informations['nom_fiche_produit']).";";
							$export_data	.= clean_export_description($content_get_informations['prod_fr_descc']).";";
							
							$export_data	.= clean_export_description($content_get_informations['prod_fr_descd']).";";
							$export_data	.= clean_export($content_get_informations['prod_ean']).";";
							$export_data	.= clean_export($content_get_informations['prod_warranty']).";";
							
							$export_data	.= clean_export($content_get_informations['prod_title_tag']).";";
							$export_data	.= clean_export($content_get_informations['prod_meta_desc_tag']).";";
							$export_data	.= clean_export($content_get_informations['prod_fr_alias']).";";
							
							$export_data	.= clean_export($content_get_informations['prod_fr_keywords']).";";
							
							
							//Looking for the Famille level 2 & 1
							//Level 2
							$res_get_families_level2 = $db->query("SELECT
																		fm.id,
																		fm.idParent,
																		fm_fr.name
																	FROM
																		families fm
																		INNER JOIN families_fr fm_fr ON fm.id=fm_fr.id
																	WHERE
																		fm.id=".$content_get_informations['id_parent_fm3']."
							
																	", __FILE__, __LINE__);
							
							$content_get_families_level2 = $db->fetchAssoc($res_get_families_level2);
							
							
							//Level 1
							$res_get_families_level1 = $db->query("SELECT
																		fm.id,
																		fm.idParent,
																		fm_fr.name
																	FROM
																		families fm
																		INNER JOIN families_fr fm_fr ON fm.id=fm_fr.id
																	WHERE
																		fm.id=".$content_get_families_level2['id']."
							
																	", __FILE__, __LINE__);
							
							$content_get_families_level1 = $db->fetchAssoc($res_get_families_level1);
							
							$export_data	.= $content_get_families_level1['id'].";";
							$export_data	.= clean_export($content_get_families_level1['name']).";";
							
							
							$export_data	.= $content_get_families_level2['id'].";";
							$export_data	.= clean_export($content_get_families_level2['name']).";";
							$export_data	.= $content_get_informations['id_famille_3'].";";
							
							
							$export_data	.= clean_export($content_get_informations['nom_famille_3'])."";
							
							$export_data	.= "\n";
							print $export_data;
							$export_data	= '';

						}//end while
						
						exit;
						
						/*$name_file	= 'export-products-'.$id.'-'.date('d-m-Y_H-i-s');
						
						header('Content-Description: File Transfer');
						header("Content-type: application/vnd.ms-excel");
						header("Content-disposition: csv" . date("Y-m-d") . ".csv");
						header("Content-disposition: filename=".$name_file.".csv");
						header('Content-Transfer-Encoding: binary');
						header('Pragma: public');
						//echo "\xEF\xBB\xBF"; // UTF-8 BOM
						
						//header("Content-type: application/vnd.ms-excel");
						//header("Content-disposition: attachment; filename=".$name_file.".csv");
						print $export_data;
						exit;*/
					
					}else{
						throw new Exception("Aucun produit &agrave; t&eacute;l&eacute;lecharger.");
					}
						
				}//end else if category 1
				
			}else{
				throw new Exception("Erreur, Fournisseur inexistant !");
			}//end else if(mysql_num_rows($res_get_category_fournisseur)){
			
		}else{
			throw new Exception("Erreur, merci de v&eacute;rifier votre formulaire !");
		
		}//end else if(!empty($id)){
		
		
	}catch(Exception $e){
		echo($e);
	}
?>