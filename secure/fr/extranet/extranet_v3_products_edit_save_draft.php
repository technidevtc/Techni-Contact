<?php
	require_once('extranet_v3_functions.php'); 
	
	$id						= mysql_escape_string($_POST['id']);
	$title					= mysql_escape_string($_POST['title']);
	$desc_fast				= mysql_escape_string($_POST['desc_fast']);
	$cat					= mysql_escape_string($_POST['cat']);
	$desc_long				= mysql_escape_string($_POST['desc_long']);
	$keywords				= mysql_escape_string($_POST['keywords']);
	$product_price			= mysql_escape_string($_POST['product_price']);
	$video					= mysql_escape_string($_POST['video']);
	
	
	//In the draft mode we do not make changes on the Attachments !
	

	$etat_price		= 1;
	if(strcmp(strtolower($product_price),'sur devis')==0){
		//sur devis
	}else if($product_price<0 || $product_price==0){
		$etat_price=0;
	}
	
	
	if(!empty($_SESSION['extranet_user_id'])){
	
		if(!empty($id) && !empty($title) && !empty($desc_fast) && !empty($cat) && !empty($desc_long) && $etat_price==1){
		
			//Updating table	"products_add_adv" => Update fileds
			//DELETE FROM		"products_extranet_history"	=> user_operation:"Brouillon ajout"
 
			
			//Standarisation keywords "|" separator
			$keywords	= str_replace('/', '|', $keywords);
			
			
			//Building & Execution of Query's
			
			//Looking for a pending request
			$sql_check_products_history	= "SELECT 
												id_demande
											FROM
												products_extranet_history
											WHERE
												p_add_adv___id=".$id."
											AND
												user_operation='Brouillon modification'
											";
			
			$res_check_products_history	=  $db->query($sql_check_products_history, __FILE__, __LINE__);
			//$content_get_products_count	= $db->fetchAssoc($res_check_products_add_adv);
				
			$video		= str_replace("'","\'",$video);
			
			
			
			
			/************************* Start Loading Data FROM ********************/
			/************************* 		products 		***********************/
			/************************* 	& 	products_fr		***********************/
			
			//products
			$sql_get_one_products	= "SELECT
											p.id, 
											p.idAdvertiser,
											p.idTC,
											p.timestamp,
											p.create_time,
											p.cg,
											p.ci,
											p.cc,
											p.refSupplier,
											p.price,
											p.price2,
											p.unite,
											p.marge,
											p.idTVA,
											p.contrainteProduit,
											p.tauxRemise,
											p.cat3_si,
											p.adv_si,
											p.ean,
											p.warranty,
											p.title_tag,
											p.meta_desc_tag,
											p.shipping_fee,
											p.video_code,
											p.docs,
											p.as_estimate,
											p.score_algo_cat3 
								
										FROM 
											products p
										WHERE 
											p.id=".$id." 
										AND 
											p.idAdvertiser=".$_SESSION['extranet_user_id']."";
				
			$res_get_one_products		= $db->query($sql_get_one_products, __FILE__, __LINE__);
			
			$content_get_one_products	= $db->fetchAssoc($res_get_one_products);
			
			
			//products_fr
			$sql_get_one_products_fr	= "SELECT
											pfr.name, 
											pfr.fastdesc, 
											pfr.ref_name, 
											pfr.alias, 
											pfr.keywords, 
											pfr.descc, 
											pfr.descd, 
											pfr.delai_livraison, 
											pfr.active, 
											pfr.deleted, 
											pfr.locked 
								
										FROM 
											products_fr pfr
										WHERE 
											pfr.id=".$id." 
										AND 
											pfr.idAdvertiser=".$_SESSION['extranet_user_id']."";
				
			$res_get_one_products_fr		= $db->query($sql_get_one_products_fr, __FILE__, __LINE__);
			
			$content_get_one_products_fr	= $db->fetchAssoc($res_get_one_products_fr);
						
			
			if(mysql_num_rows($res_check_products_history)==1){
				
				//1st Query
				$sql_update_products_history	= "UPDATE products_extranet_history  SET 
				
													p__id='".$content_get_one_products['id']."', 
													p__idAdvertiser='".$content_get_one_products['idAdvertiser']."', 
													p__idTC='".$content_get_one_products['idTC']."', 
													p__timestamp='".$content_get_one_products['timestamp']."', 
													p__create_time='".$content_get_one_products['create_time']."', 
													p__cg='".$content_get_one_products['cg']."', 
													p__ci='".$content_get_one_products['ci']."', 
													p__cc='".$content_get_one_products['cc']."', 
													p__refSupplier='".$content_get_one_products['refSupplier']."', 
													p__price2='".$content_get_one_products['price2']."', 
													p__unite='".$content_get_one_products['unite']."', 
													p__marge='".$content_get_one_products['marge']."', 
													p__idTVA='".$content_get_one_products['idTVA']."', 
													p__contrainteProduit='".$content_get_one_products['contrainteProduit']."', 
													p__tauxRemise='".$content_get_one_products['tauxRemise']."', 
													p__cat3_si='', 
													p__adv_si='', 
													p__ean='".$content_get_one_products['ean']."', 
													p__warranty='', 
													p__title_tag='".$content_get_one_products['title_tag']."', 
													p__meta_desc_tag='".$content_get_one_products['meta_desc_tag']."', 
													p__shipping_fee='".$content_get_one_products['shipping_fee']."', 
													p__docs='".$content_get_one_products['docs']."', 
													p__as_estimate='".$content_get_one_products['as_estimate']."', 
													p__score_algo_cat3='".$content_get_one_products['score_algo_cat3']."', 


													pfr__ref_name='".$content_get_one_products_fr['ref_name']."', 
													pfr__alias='".$content_get_one_products_fr['alias']."', 
													pfr__descd='".$content_get_one_products_fr['descd']."', 
													pfr__delai_livraison='".$content_get_one_products_fr['delai_livraison']."', 
													pfr__active='".$content_get_one_products_fr['active']."', 
													pfr__deleted='".$content_get_one_products_fr['deleted']."', 
													pfr__locked='".$content_get_one_products_fr['locked']."', 

													pfam__orderFamily=0, 



													pfr__name='".$title."', 
													pfr__fastdesc='".$desc_fast."', 
													pfam__idFamily='".$cat."', 
													pfr__keywords='".$keywords."', 
													pfr__descc='".$desc_long."', 
													p__price='".$product_price."', 
													p__video_code='".$video."', 
													date_demande=NOW() 
													
													WHERE 
														p_add_adv___id=".$id."
													AND 
														user_operation='Brouillon modification'
													";
				
				
				$db->query($sql_update_products_history, __FILE__, __LINE__);
				
			}else{
				//This fiche is not pending
				
				//1st Query
				$sql_insert_products_history	= "insert into products_extranet_history 
														(id_demande, p_add_adv___id,
														
														
														p__id, 				
														p__idAdvertiser,
														p__idTC,			
														p__timestamp,
														p__create_time, 	
														p__cg,
														p__ci,				
														p__cc,
														p__refSupplier,		
														p__price2,
														p__unite,			
														p__marge,
														p__idTVA,			
														p__contrainteProduit,
														p__tauxRemise,		
														p__cat3_si,
														p__adv_si,			
														p__ean,
														p__warranty,		
														p__title_tag,
														p__meta_desc_tag, 	
														p__shipping_fee,
														p__docs,			
														p__as_estimate,
														p__score_algo_cat3,
														
														
														pfr__ref_name,
														pfr__alias,
														pfr__descd,
														pfr__delai_livraison,
														pfr__active,
														pfr__deleted,
														pfr__locked,
														pfam__orderFamily,
													
													
														
														pfr__name, pfr__fastdesc, 
														pfam__idFamily, pfr__keywords, 
														pfr__descc, p__price,
														
														p__video_code,
														
														date_demande, user_operation)
														
														values(NULL, ".$id.", 
														
														
														'".$content_get_one_products['id']."', 
														'".$content_get_one_products['idAdvertiser']."',
														'".$content_get_one_products['idTC']."', 
														'".$content_get_one_products['timestamp']."',
														'".$content_get_one_products['create_time']."', 
														'".$content_get_one_products['cg']."',
														'".$content_get_one_products['ci']."', 
														'".$content_get_one_products['cc']."',
														'".$content_get_one_products['refSupplier']."', 
														'".$content_get_one_products['price2']."',
														'".$content_get_one_products['unite']."', 
														'".$content_get_one_products['marge']."',
														'".$content_get_one_products['idTVA']."', 
														'".$content_get_one_products['contrainteProduit']."',
														'".$content_get_one_products['tauxRemise']."', 
														'',
														'', 
														'".$content_get_one_products['ean']."',
														'', 
														'".$content_get_one_products['title_tag']."',
														'".$content_get_one_products['meta_desc_tag']."', 
														'".$content_get_one_products['shipping_fee']."',
														'".$content_get_one_products['docs']."', 
														'".$content_get_one_products['as_estimate']."',
														'".$content_get_one_products['score_algo_cat3']."',

														'".$content_get_one_products_fr['ref_name']."',
														'".$content_get_one_products_fr['alias']."',
														'".$content_get_one_products_fr['descd']."',
														'".$content_get_one_products_fr['delai_livraison']."',
														'".$content_get_one_products_fr['active']."',
														'".$content_get_one_products_fr['deleted']."',
														'".$content_get_one_products_fr['locked']."',
														0,
														
														
														'".$title."', '".$desc_fast."',
														'".$cat."', '".$keywords."',
														'".$desc_long."', '".$product_price."',
														
														'".$video."',
														
														NOW(), 'Brouillon modification')";
								
				
				$db->query($sql_insert_products_history, __FILE__, __LINE__);
				
			
			}//end else if mysql_num_rows($res_check_products_add_adv)
				
		
			
			
			//In draft mode we do not make changes on the Attachments 
			//Because we read always from the prod folder !
			

			//That is OK
			echo('1');
		
		}else{
			echo('0');
		}//end else if !empty fields
	
	}else{
		echo('-1');
	}//End else if(!empty($_SESSION['extranet_user_id']))
?>