<?php
	require_once('extranet_v3_functions.php'); 
	
	$id						= mysql_escape_string($_POST['id']);
	$title					= mysql_escape_string($_POST['title']);
	$desc_fast				= mysql_escape_string($_POST['desc_fast']);
	$cat					= mysql_escape_string($_POST['cat']);
	$desc_long				= mysql_escape_string($_POST['desc_long']);
	$keywords				= mysql_escape_string($_POST['keywords']);
	$product_price			= mysql_escape_string($_POST['product_price']);
	

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
			
				//1st Query
				$sql_delete_products_add_adv	= "UPDATE products_add_adv SET name='".$title."',
													fastdesc='".$desc_fast."',
													families='".$cat."',
													keywords='".$keywords."',
													descc='".$desc_long."',
													price='".$product_price."'
													
													WHERE
														id=".$id."
													AND
														idAdvertiser=".$_SESSION['extranet_user_id']."
													";
				
				$db->query($sql_delete_products_add_adv, __FILE__, __LINE__);
				
				
				//2nd Query
				$sql_delete_products_extranet_history	= "DELETE FROM products_extranet_history
															WHERE
																p_add_adv___id=".$id."
															AND
																user_operation='Brouillon ajout'
													";
				
				$db->query($sql_delete_products_extranet_history, __FILE__, __LINE__);
				
				
				//3rd query insert 
				$sql_insert_products_extranet_history	= "insert into products_extranet_history 
															(id_demande, p_add_adv___id,
															
															pfr__name, pfr__fastdesc, 
															pfam__idFamily, pfr__keywords, 
															pfr__descc, p__price,
															
															
															date_demande, user_operation)
															
															values(NULL, ".$id.", 
															
															'".$title."', '".$desc_fast."',
															'".$cat."', '".$keywords."',
															'".$desc_long."', '".$product_price."',
															
															
															NOW(), 'Demande ajout')
													";
													
				$db->query($sql_insert_products_extranet_history, __FILE__, __LINE__);
			
			
			
			//That is OK
			echo('1');
		
		}else{
			echo('0');
		}//end else if !empty fields
	
	}else{
		echo('-1');
	}//End else if(!empty($_SESSION['extranet_user_id']))
?>