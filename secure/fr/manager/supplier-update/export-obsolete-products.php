<?php

	if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
		require_once '../../../../config.php';
	}else{
		require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
	}
	
	$db = DBHandle::get_instance();
	$user = new BOUser();
	
	if (!$user->login()){
		echo('<div class="bg" style="position: relative">');
			echo('<h2>Votre session a expiré, veuillez vous identifier à nouveau après avoir rafraîchi votre page.</h2>');
		echo('</div>');
	}else{


		$id_operation	= mysql_real_escape_string($_GET['id']);

		if(!empty($id_operation)){
		
		/*
			$res_get_obsoletes_products = $db->query("SELECT
														ref_c.id id_tc, 			ref_c.refSupplier,															
														ref_c.price, 				ref_c.price2,
														ref_c.marge,				ref_c.label,
														prod_fr.id id_product_fr,	prod_fr.name name_fr
														
													FROM
														references_content ref_c 
															LEFT JOIN products_fr AS prod_fr ON ref_c.idProduct=prod_fr.id
															LEFT JOIN supplier_update_info AS sup_ui ON sup_ui.advertiser=ref_c.sup_id
													WHERE
														sup_ui.id=".$id_operation."	
													AND
														ref_c.deleted=0
													AND	
														ref_c.classement!=0
													AND
														prod_fr.deleted=0
													AND
														prod_fr.active=1
													AND
														ref_c.id NOT IN	(SELECT
																			sup_uic.id_tc
																		FROM
																			supplier_update_info_correspondance AS sup_uic
																		WHERE
																			sup_uic.id_operation=".$id_operation.")
													", __FILE__, __LINE__);
		*/

			$res_get_obsoletes_products = $db->query("SELECT
														ref_c.id id_tc, 			ref_c.refSupplier,															
														ref_c.price, 				ref_c.price2,
														ref_c.marge,				ref_c.label,
														prod_fr.id id_product_fr,	prod_fr.name name_fr
														
													FROM
														references_content ref_c 
															LEFT JOIN products_fr 									AS prod_fr 	ON ref_c.idProduct=prod_fr.id
															LEFT JOIN supplier_update_info 							AS sup_ui 	ON sup_ui.advertiser=ref_c.sup_id	
													WHERE
														sup_ui.id=".$id_operation."
													AND
														NOT EXISTS
															(
																SELECT
																	sup_uic.id_tc
																FROM
																	supplier_update_info_correspondance AS sup_uic
																WHERE
																	sup_uic.id_operation=".$id_operation."
																AND
																	ref_c.id=sup_uic.id_tc
															)
													AND
														ref_c.deleted=0
													AND	
														ref_c.classement!=0
													AND
														prod_fr.deleted=0
													AND
														prod_fr.active=1 ", __FILE__, __LINE__);
														
			if(mysql_num_rows($res_get_obsoletes_products)>0){
			
				//Get Prix public or fournisseur for this advertiser from operation
				$res_get_advertiser = $db->query(" SELECT
																adv.prixPublic, 	adv.margeRemise
													
															FROM
																advertisers adv 
																	LEFT JOIN supplier_update_info sup_ui ON sup_ui.advertiser=adv.id
															WHERE
																sup_ui.id=".$id_operation."", __FILE__, __LINE__);
																
				$content_get_advertiser = $db->fetchAssoc($res_get_advertiser);
				if(strcmp($content_get_advertiser['prixPublic'],'0')=='0'){
					$query_field_operation	= 'price2';
				}else{
					$query_field_operation	= 'price';
				}
				


				$export_data	= "Titre;Label;ID Fiche;ID TC;Ref fournisseur;Prix produit\n";
				

				while($content_get_obsoletes_products = $db->fetchAssoc($res_get_obsoletes_products)){

					// The actual data
					$content_get_obsoletes_products['name_fr']				= str_replace(';','',$content_get_obsoletes_products['name_fr']);
					$content_get_obsoletes_products['label']				= str_replace(';','',$content_get_obsoletes_products['label']);
					$content_get_obsoletes_products['refSupplier']			= str_replace(';','',$content_get_obsoletes_products['refSupplier']);
					$content_get_obsoletes_products[$query_field_operation]	= str_replace(';','',$content_get_obsoletes_products[$query_field_operation]);
					
					
					$export_data	.= utf8_decode($content_get_obsoletes_products['name_fr']).";";
					$export_data	.= utf8_decode($content_get_obsoletes_products['label']).";";
					$export_data	.= $content_get_obsoletes_products['id_product_fr'].";";
					$export_data	.= $content_get_obsoletes_products['id_tc'].";";
					$export_data	.= $content_get_obsoletes_products['refSupplier'].";";
					$export_data	.= $content_get_obsoletes_products[$query_field_operation].";";
					$export_data	.= "\n";

				}//end while

				
				$name_file	= 'obsoletes-products-'.date('d-m-Y_H-i-s');
				header("Content-type: application/vnd.ms-excel");
				header("Content-disposition: attachment; filename=".$name_file.".csv");
				echo $export_data;
				exit;
			}//end if num_rows

		}else{
			echo('<div class="bg" style="position: relative">');
				echo('<h2>Merci de v&eacute;rifier votre formulaire !</h2>');
			echo('</div>');
		}
	}//end else if check user
?>