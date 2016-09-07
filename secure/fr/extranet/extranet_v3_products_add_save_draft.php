<?php
	require_once('extranet_v3_functions.php'); 
	
	$id						= mysql_escape_string($_POST['id']);
	$title					= mysql_escape_string($_POST['title']);
	$desc_fast				= mysql_escape_string($_POST['desc_fast']);
	$cat					= mysql_escape_string($_POST['cat']);
	$desc_long				= mysql_escape_string($_POST['desc_long']);
	$keywords				= mysql_escape_string($_POST['keywords']);
	$product_price			= mysql_escape_string($_POST['product_price']);
	

	if(!empty($_SESSION['extranet_user_id'])){
	
		if(!empty($id) && !empty($title) && !empty($desc_fast) && !empty($cat) && !empty($desc_long) && !empty($product_price)){
		
			//Updating of the two tables 
			//	"products_add_adv" => Update fileds
			//  "products_extranet_history"	=> user_operation:"Brouillon ajout"
			
			//Standarisation keywords "|" separator
			$keywords		= str_replace('/', '|', $keywords);
			
			$product_price	= str_replace('.', ',', $product_price);
			
			//Building & Execution of Query's
			
				//1st Query
				$sql_update_products_add_adv	= "UPDATE products_add_adv SET name='##########".$title."',
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
				
				$db->query($sql_update_products_add_adv, __FILE__, __LINE__);
				
				
				//2nd Query
				$sql_update_products_extranet_history	= "UPDATE products_extranet_history SET date_demande=NOW(),
				
															pfr__name='".$title."', 
															pfr__fastdesc='".$desc_fast."', 
															pfam__idFamily='".$cat."', 
															pfr__keywords='".$keywords."', 
															pfr__descc='".$desc_long."',
															p__price='".$product_price."'
															
															WHERE
																p_add_adv___id=".$id."
															AND
																user_operation='Brouillon ajout'
													";
				
				$db->query($sql_update_products_extranet_history, __FILE__, __LINE__);
			
			
			
			//That is OK
			echo('1');
		
		}else{
			echo('0');
		}//end else if !empty fields
	
	}else{
		echo('-1');
	}//End else if(!empty($_SESSION['extranet_user_id']))
?>