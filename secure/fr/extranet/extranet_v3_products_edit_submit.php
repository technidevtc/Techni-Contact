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
	
	
	//Files document part
	$product_doc1			= mysql_escape_string($_POST['product_doc1']);
	$product_doc2			= mysql_escape_string($_POST['product_doc2']);
	$product_doc3			= mysql_escape_string($_POST['product_doc3']);
	
	
	//Files params to delete documents or not !
	$product_no_doc1		= mysql_escape_string($_POST['nodoc1']);
    $product_no_doc2 		= mysql_escape_string($_POST['nodoc2']);
    $product_no_doc3 		= mysql_escape_string($_POST['nodoc3']);
	
	
	function uploadDoc($field, $name, $dir, $type) {
		if(is_uploaded_file($_FILES[$field]['tmp_name'])){
			copy($_FILES[$field]['tmp_name'], $dir.$name.$type);
		}
	}

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
			
			$video		= str_replace("'","\'",$video);
			
			//Building & Execution of Query's
			
			//Looking for a pending request
			$sql_check_products_add_adv	= "SELECT 
												id
											FROM
												products_add_adv
											WHERE
												id=".$id."
											AND
													idAdvertiser=".$_SESSION['extranet_user_id']."
											AND
												type='m'
											";
			
			$res_check_products_add_adv	=  $db->query($sql_check_products_add_adv, __FILE__, __LINE__);
			//$content_get_products_count	= $db->fetchAssoc($res_check_products_add_adv);
				
			if(mysql_num_rows($res_check_products_add_adv)==1){
				
				//1st Query
				$sql_update_products_add_adv	= "UPDATE products_add_adv SET name='".addslashes($title)."',
													fastdesc='".addslashes($desc_fast)."',
													families='".$cat."',
													keywords='".addslashes($keywords)."',
													descc='".addslashes($desc_long)."',
													price='".$product_price."',
													video_code='".$video."'
													
													WHERE
														id=".$id."
													AND
														idAdvertiser=".$_SESSION['extranet_user_id']."
													AND
														type='m'
													";
				
				$db->query($sql_update_products_add_adv, __FILE__, __LINE__);
				
			}else{
				//This fiche is not pending
				
				//1st Query
				$sql_insert_products_add_adv	= "insert into products_add_adv 
													(id, idAdvertiser, idTC,
													name, fastdesc, type,
													families, keywords, descd,
													descc, timestamp, reject,
													refSupplier, price, price2,
													unite, marge, idTVA, 
													delai_livraison, contrainteProduit, tauxRemise,
													ref, ean, warranty, 
													title_tag, meta_desc_tag, shipping_fee,
													productsLinked, video_code)
													
													values(".$id.", ".$_SESSION['extranet_user_id'].", 0,
													'".$title."', '".$desc_fast."', 'm',
													'".$cat."', '".$keywords."', '',
													'".$desc_long."', ".time().", 0,
													'', '".$product_price."', '', 
													1, 0, 1,
													'', 0, '',
													'', '', '',
													'', '', '',
													'', '".$video."')";
				
				$db->query($sql_insert_products_add_adv, __FILE__, __LINE__);
				
			
			}//end else if mysql_num_rows($res_check_products_add_adv)
				
			
			
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
			
			
			
			//Delete a pending Draft Edit if that exist !
			$sql_delete_products_extranet_history	= "DELETE FROM products_extranet_history 
														WHERE
															p_add_adv___id=".$id."
														AND
															user_operation='Brouillon modification'
														";
												
			$db->query($sql_delete_products_extranet_history, __FILE__, __LINE__);
			
			
			//2nd Query (Insert 'demande modification')
			$sql_insert_products_extranet_history	= "insert into products_extranet_history 
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
													
													
													'".addslashes($content_get_one_products['id'])."', 
													'".addslashes($content_get_one_products['idAdvertiser'])."',
													'".addslashes($content_get_one_products['idTC'])."', 
													'".addslashes($content_get_one_products['timestamp'])."',
													'".addslashes($content_get_one_products['create_time'])."', 
													'".addslashes($content_get_one_products['cg'])."',
													'".addslashes($content_get_one_products['ci'])."', 
													'".addslashes($content_get_one_products['cc'])."',
													'".addslashes($content_get_one_products['refSupplier'])."', 
													'".addslashes($content_get_one_products['price2'])."',
													'".addslashes($content_get_one_products['unite'])."', 
													'".addslashes($content_get_one_products['marge'])."',
													'".addslashes($content_get_one_products['idTVA'])."', 
													'".addslashes($content_get_one_products['contrainteProduit'])."',
													'".addslashes($content_get_one_products['tauxRemise'])."', 
													'".addslashes($content_get_one_products['cat3_si'])."',
													'".addslashes($content_get_one_products['adv_si'])."', 
													'".addslashes($content_get_one_products['ean'])."',
													'".addslashes($content_get_one_products['warranty'])."', 
													'".addslashes($content_get_one_products['title_tag'])."',
													'".addslashes($content_get_one_products['meta_desc_tag'])."', 
													'".addslashes($content_get_one_products['shipping_fee'])."',
													'".addslashes($content_get_one_products['docs'])."', 
													'".addslashes($content_get_one_products['as_estimate'])."',
													'".addslashes($content_get_one_products['score_algo_cat3'])."',

													'".addslashes($content_get_one_products_fr['ref_name'])."',
													'".addslashes($content_get_one_products_fr['alias'])."',
													'".addslashes($content_get_one_products_fr['descd'])."',
													'".addslashes($content_get_one_products_fr['delai_livraison'])."',
													'".addslashes($content_get_one_products_fr['active'])."',
													'".addslashes($content_get_one_products_fr['deleted'])."',
													'".addslashes($content_get_one_products_fr['locked'])."',
													0,
													
													
													'".addslashes($title)."', '".addslashes($desc_fast)."',
													'".$cat."', '".addslashes($keywords)."',
													'".$desc_long."', '".$product_price."',
													
													'".$video."',
													
													NOW(), 'Demande Modification')";
			
			$db->query($sql_insert_products_extranet_history, __FILE__, __LINE__);
			
			
			//Managing docs !
			
			//Flag to know if the user has uploaded a file 
			//So we don't need to get the old one !
			$flag_upload_doc1	= 0;
			$flag_upload_doc2	= 0;
			$flag_upload_doc3	= 0;
			
			//Copy vers the temp filder "products_adv"
			if(!empty($_FILES["product_doc1"]["tmp_name"])) {			
				/*if(is_file(PRODUCTS_FILES_INC.$id.'-1.doc')){
					copy(PRODUCTS_FILES_INC.$id.'-1.doc', PRODUCTS_FILES_ADV_INC.$id.'-1.doc');
				}*/
				
				//Get the extensions
				if($_FILES['product_doc1']['type']=='application/msword'){
					//.doc
					$extension1	= '.doc';
				}else{
					//.pdf
					$extension1	= '.pdf';
				}
				
				//Move the new uploads and replace the old one last moved to this folder !
				uploadDoc('product_doc1', $id . '-1', PRODUCTS_FILES_ADV_INC, $extension1);
			
				$flag_upload_doc1	= 1;		
			}//end if isset($product_doc1)
			
			if(!empty($_FILES["product_doc2"]["tmp_name"])) {
				/*if(is_file(PRODUCTS_FILES_INC.$id.'-2.doc')){
					copy(PRODUCTS_FILES_INC.$id.'-2.doc', PRODUCTS_FILES_ADV_INC.$id.'-2.doc');
				}*/
				
				//Get the extensions
				if($_FILES['product_doc2']['type']=='application/msword'){
					//.doc
					$extension2	= '.doc';
				}else{
					//application/pdf
					//.pdf
					$extension2	= '.pdf';
				}
				
				//Move the new uploads and replace the old one last moved to this folder !
				uploadDoc('product_doc2', $id . '-2', PRODUCTS_FILES_ADV_INC, $extension2);
				
				$flag_upload_doc2	= 1;
			}//end if isset($product_doc2)

			
			if(!empty($_FILES["product_doc3"]["tmp_name"])) {
				/*if(is_file(PRODUCTS_FILES_INC.$id.'-3.doc')){
					copy(PRODUCTS_FILES_INC.$id.'-3.doc', PRODUCTS_FILES_ADV_INC.$id.'-3.doc');
				}*/
				
				//Get the extensions
				if($_FILES['product_doc3']['type']=='application/msword'){
					//.doc
					$extension3	= '.doc';
				}else{
					//application/pdf
					//.pdf
					$extension3	= '.pdf';
				}
				
				//Move the new uploads and replace the old one last moved to this folder !
				uploadDoc('product_doc3', $id . '-3', PRODUCTS_FILES_ADV_INC, $extension3);
				
				$flag_upload_doc3	= 1;
			}//end if isset($product_doc3)

			
			//We force the copy of the files exist in "products" to "products_adv"
			//Because when we submit for review we must not lose files
			//Because when it's a edit query the manager takes the "products_adv"
			//Files and move them to "products"
			//So we have to move them always even the user did not make a modification !
			
			
			if(is_file(PRODUCTS_FILES_INC.$id.'-1.pdf') && $flag_upload_doc1==0){

				//Detect if the user request the delete of this document
				//If the user want to delete it we don't move the document to the temp folder "files/data/products_adv"
				if(empty($product_no_doc1)){
					copy(PRODUCTS_FILES_INC.$id.'-1.pdf', PRODUCTS_FILES_ADV_INC.$id.'-1.pdf');
				}//end if(!$product_no_doc1)
			}
			
			
			if(is_file(PRODUCTS_FILES_INC.$id.'-2.pdf') && $flag_upload_doc2==0){
			
				//Detect if the user request the delete of this document
				//If the user want to delete it we don't move the document to the temp folder "files/data/products_adv"
				if(empty($product_no_doc2)){
					copy(PRODUCTS_FILES_INC.$id.'-2.pdf', PRODUCTS_FILES_ADV_INC.$id.'-2.pdf');
				}//end if(!$product_no_doc2)
			}
		
		
			if(is_file(PRODUCTS_FILES_INC.$id.'-3.pdf') && $flag_upload_doc3==0){
				
				//Detect if the user request the delete of this document
				//If the user want to delete it we don't move the document to the temp folder "files/data/products_adv"
				if(empty($product_no_doc3)){				
					copy(PRODUCTS_FILES_INC.$id.'-3.pdf', PRODUCTS_FILES_ADV_INC.$id.'-3.pdf');
				}//end if(!$product_no_doc3)
			}
			
			
			//That is OK
			echo('1');
		
		}else{
			echo('0');
		}//end else if !empty fields
	
	}else{
		echo('-1');
	}//End else if(!empty($_SESSION['extranet_user_id']))
?>