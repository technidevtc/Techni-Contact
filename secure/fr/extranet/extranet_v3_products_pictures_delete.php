<?php
	require_once('extranet_v3_functions.php'); 
	require_once(ADMIN.'logo.php');
	
	$id_fiche			= mysql_escape_string($_POST['idf']);
	$id_picture			= mysql_escape_string($_POST['idp']);
	$type				= mysql_escape_string($_POST['type']);
	
	if(!empty($_SESSION['extranet_user_id']) && !empty($id_picture) && !empty($id_fiche)){
		
		//$dirNames = array('zoom', 'thumb_small', 'thumb_big', 'card');
		if(strcmp($type,'new')==0){
			$dir_INCLUDES 	= PRODUCTS_IMAGE_ADV_INC;
		}else if(strcmp($type,'edit')==0){
			//$dir_INCLUDES 	= PRODUCTS_IMAGE_INC;
			$dir_INCLUDES 	= PRODUCTS_IMAGE_ADV_INC;
		}
		
		//Deleting the picture
		$fileName = $id_fiche."-".$id_picture.".jpg";
		unlink($dir_INCLUDES."zoom/".$fileName);
		unlink($dir_INCLUDES."card/".$fileName);
		unlink($dir_INCLUDES."thumb_big/".$fileName);
		unlink($dir_INCLUDES."thumb_small/".$fileName);
		
		//Ordering the next pictures
		$id_picture++;
		while (is_file($dir_INCLUDES."zoom/".$id_fiche."-".$id_picture.".jpg")) {

			$oldFileName = $id_fiche."-".$id_picture.".jpg";
			$newFileName = $id_fiche."-".($id_picture-1).".jpg";
			rename($dir_INCLUDES."zoom/".$oldFileName, $dir_INCLUDES."zoom/".$newFileName);
			rename($dir_INCLUDES."card/".$oldFileName, $dir_INCLUDES."card/".$newFileName);
			rename($dir_INCLUDES."thumb_big/".$oldFileName, $dir_INCLUDES."thumb_big/".$newFileName);
			rename($dir_INCLUDES."thumb_small/".$oldFileName, $dir_INCLUDES."thumb_small/".$newFileName);
			
			$id_picture++;
		}
	
	
		echo('1');
	
	}//end if empty session

?>