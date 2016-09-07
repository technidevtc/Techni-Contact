<?php	
	require_once('extranet_v3_functions.php'); 

	
	if(!empty($_SESSION['extranet_user_id'])){
	
		//Getting params
		$id_fiche						= mysql_escape_string($_POST['idf']);
		$operation						= mysql_escape_string($_POST['operation']);
		
		$dir_INCLUDES 					= PRODUCTS_IMAGE_ADV_INC;
		$dir_files_INCLUDES				= PRODUCTS_FILES_ADV_INC;
		
		//	list_draft_add			=> Delete from Add Draft list 
		//	list_draft_edit			=> Delete from Edit Draft list
		//	detail_page_draft_add	=> Delete from Draft add detail page
		//	detail_page_draft_edit	=> Delete from Draft edit detail page
	
	
		//Type of operation "Edit" or "Add"
			//Case Add delete from table "products_add_adv" & "products_extranet_history" => user_operation="Brouillon ajout"
			//Case Edit delete from table "products_extranet_history" => user_operation="Brouillon modification"
		$global_operation				= '';
		
		if($operation=='list_draft_add' || $operation=='detail_page_draft_add'){
			$global_operation				= 'add';
		}else{
			$global_operation				= 'edit';
		}
	
	
		//Start deleting from the Database
		
		if($global_operation=='add'){
			//First Query
			$res_delete_draft_product_query_s1	= "DELETE FROM products_add_adv
													WHERE
														id=".$id_fiche."
													AND
														idAdvertiser=".$_SESSION['extranet_user_id']."
													AND
														type='c'";
									
			$res_delete_draft_product_s1 = $db->query($res_delete_draft_product_query_s1, __FILE__, __LINE__);
		
			//Second Query
			$res_delete_draft_product_query_s2	= "DELETE FROM products_extranet_history
													WHERE
														p_add_adv___id=".$id_fiche."
													AND
														p__idAdvertiser=".$_SESSION['extranet_user_id']."
													AND
														user_operation='Brouillon ajout'";
									
			$res_delete_draft_product_s1 = $db->query($res_delete_draft_product_query_s2, __FILE__, __LINE__);
		
		}else{
		
			$res_delete_draft_product_query_s1	= "DELETE FROM products_extranet_history
													WHERE
														p_add_adv___id=".$id_fiche."
													AND
														p__idAdvertiser=".$_SESSION['extranet_user_id']."
													AND
														user_operation='Brouillon modification'";
													
			$res_delete_draft_product_s1 = $db->query($res_delete_draft_product_query_s1, __FILE__, __LINE__);
	
		}//end if else type of query
		
		
		//Start deleting files (Pictures)
		
		$num = 1;
		//Deleting the existing pictures on the "products_adv"
		while(is_file($dir_INCLUDES."zoom/".$id_fiche."-".$num.".jpg")){
		
			$fileName = $id_fiche."-".$num.".jpg";
			unlink($dir_INCLUDES."zoom/".$fileName);
			unlink($dir_INCLUDES."card/".$fileName);
			unlink($dir_INCLUDES."thumb_big/".$fileName);
			unlink($dir_INCLUDES."thumb_small/".$fileName);
		
			$num++;
		}//end while
			
			
		//Start deleting files (Docs)
		if(is_file($dir_files_INCLUDES.$id.'-1.pdf')){
			unlink($dir_files_INCLUDES.$id.'-1.pdf');
		}
		
		if(is_file($dir_files_INCLUDES.$id.'-2.pdf')){
			unlink($dir_files_INCLUDES.$id.'-2.pdf');
		}
		
		if(is_file($dir_files_INCLUDES.$id.'-3.pdf')){
			unlink($dir_files_INCLUDES.$id.'-3.pdf');
		}
		
		
		//If everything is ok it will show only "1"
		echo('1');
		
	}//end if !empty session

?>