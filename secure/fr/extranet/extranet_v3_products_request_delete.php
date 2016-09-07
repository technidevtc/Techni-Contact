<?php	
	require_once('extranet_v3_functions.php'); 
		
	if(!empty($_SESSION['extranet_user_id'])){
	
		$product_id			= mysql_escape_string($_POST['id']);
		$product_id_family	= 0;
		$product_f_order	= 0;
			
		//Search for the product from the "products_fr" table
		$res_get_product_query	= "	SELECT 
	
										p_fr.id,
										p_fr.idAdvertiser,
										p_fr.name,
										p_fr.fastdesc,
										p_fr.ref_name,
										p_fr.alias,
										p_fr.keywords,
										p_fr.descc,
										p_fr.descd,
										p_fr.delai_livraison,
										p_fr.active,
										p_fr.deleted,
										p_fr.locked
										
									FROM
										products_fr p_fr
									WHERE
										p_fr.idAdvertiser=".$_SESSION['extranet_user_id']."
									AND
										p_fr.active='1'
									AND	
										p_fr.deleted='0'
									AND
										p_fr.id=".$product_id."";
									
		$res_get_product = $db->query($res_get_product_query, __FILE__, __LINE__);
			
		
		if(mysql_num_rows($res_get_product)!=0){
		
			//Search for the product in the process Queue
			//If it's found show product already processing
			//Else add the product in the Queue list
			
				$content_get_product	= $db->fetchAssoc($res_get_product);
				
				$res_get_product_qlist_query	= "SELECT 
	
														s_req.id
														
													FROM
														sup_requests s_req
													WHERE
														s_req.idProduct=".$content_get_product['id']."";
									
				$res_get_product_qlist = $db->query($res_get_product_qlist_query, __FILE__, __LINE__);
		
				if(mysql_num_rows($res_get_product_qlist)==0){
				
					$res_insert_product_qlist_query	= "INSERT INTO sup_requests (id, idProduct, timestamp)
														values(NULL, ".$content_get_product['id'].", ".time().")";
									
					$db->query($res_insert_product_qlist_query, __FILE__, __LINE__);
				
					echo(PRODUCT_DEL_OK);
					
					//After insert into Queue list 
					//Get and Insert All the informations of this product
					//In a new table "products_extranet_history"
					
			
					$res_get_p_product_query	= "	SELECT 
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
														p.idAdvertiser=".$_SESSION['extranet_user_id']."
													AND
														p.id=".$content_get_product['id']."";
									
					$res_get_p_product = $db->query($res_get_p_product_query, __FILE__, __LINE__);
					$content_get_p_product	= $db->fetchAssoc($res_get_p_product);
		
					//Search for Family product
					//And get order 0 else get 1
					//To get the Primary one 
					
					$res_get_family_product_query	= "	SELECT 
														pfam.idFamily,
														pfam.orderFamily
														
													FROM
														products_families pfam
													WHERE
														pfam.idProduct=".$content_get_product['id']."
													AND
														orderFamily<2
													";
									
					$res_get_family_product = $db->query($res_get_family_product_query, __FILE__, __LINE__);
					$content_get_family_product	= $db->fetchAssoc($res_get_family_product);
					
					$product_id_family			= $content_get_family_product['idFamily'];
					$product_f_order			= $content_get_family_product['orderFamily'];
					
					
					$res_insert_product_history	= "INSERT INTO products_extranet_history
	(id_demande, 
	
	p_add_adv___id,
	
	p__id, p__idAdvertiser,
	p__idTC, p__timestamp,
	p__create_time, p__cg,
	p__ci, p__cc,
	p__refSupplier, p__price,
	p__price2, p__unite,
	p__marge, p__idTVA,
	p__contrainteProduit, p__tauxRemise,
	p__cat3_si, p__adv_si,
	p__ean, p__warranty,
	p__title_tag, p__meta_desc_tag,
	p__shipping_fee, p__video_code,
	p__docs, p__as_estimate,
	p__score_algo_cat3, 

	pfr__name, pfr__fastdesc,
	pfr__ref_name, pfr__alias,
	pfr__keywords, pfr__descc,
	pfr__descd, pfr__delai_livraison,
	pfr__active, pfr__deleted,
	pfr__locked, 

	pfam__idFamily, pfam__orderFamily,
	
	date_demande, date_traitement,
	id_operator, user_operation)
	
	values(NULL, 
	
	0,
	
	".$content_get_p_product['id'].", ".$content_get_p_product['idAdvertiser'].",
	".$content_get_p_product['idTC'].", ".$content_get_p_product['timestamp'].",
	".$content_get_p_product['create_time'].", ".$content_get_p_product['cg'].",
	".$content_get_p_product['ci'].", ".$content_get_p_product['cc'].",
	'".addslashes($content_get_p_product['refSupplier'])."', '".addslashes($content_get_p_product['price'])."',
	'".addslashes($content_get_p_product['price2'])."', ".$content_get_p_product['unite'].",
	'".$content_get_p_product['marge']."', ".$content_get_p_product['idTVA'].",
	".$content_get_p_product['contrainteProduit'].", '".addslashes($content_get_p_product['tauxRemise'])."',
	'".addslashes($content_get_p_product['cat3_si'])."', '".addslashes($content_get_p_product['adv_si'])."',
	'".addslashes($content_get_p_product['ean'])."', '".addslashes($content_get_p_product['warranty'])."',
	'".addslashes($content_get_p_product['title_tag'])."', '".addslashes($content_get_p_product['meta_desc_tag'])."',
	'".addslashes($content_get_p_product['shipping_fee'])."', '".addslashes($content_get_p_product['video_code'])."',
	'".addslashes($content_get_p_product['docs'])."', ".$content_get_p_product['as_estimate'].",
	'".$content_get_p_product['score_algo_cat3']."', 
	
	'".addslashes($content_get_product['name'])."', '".addslashes($content_get_product['fastdesc'])."',
	'".addslashes($content_get_product['ref_name'])."', '".addslashes($content_get_product['alias'])."',
	'".addslashes($content_get_product['keywords'])."', '".addslashes($content_get_product['descc'])."',
	'".addslashes($content_get_product['descd'])."', '".addslashes($content_get_product['delai_livraison'])."',
	".$content_get_product['active'].", ".$content_get_product['deleted'].",
	'".$content_get_product['locked']."',
	
	".$product_id_family.", ".$product_f_order.",
	
	NOW(), '0000-00-00 00:00:00',
	0, 'Demande suppression')";
						

						
					$db->query($res_insert_product_history, __FILE__, __LINE__);
			
				}else{
				
					//Product exist on queue list !
					echo(PRODUCT_DEL_ERROR_ALREADY);
				
				}//end else if(mysql_num_rows($res_get_product_qlist)==0)

		}else{
			echo(PRODUCT_DEL_ERROR_ID);
		}//end else if global count 	if($total_count_results!=0)

	}else{
		echo('<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;<strong><a href="login.html">Merci de vous reconnecter.</a></strong>');
	}
?>