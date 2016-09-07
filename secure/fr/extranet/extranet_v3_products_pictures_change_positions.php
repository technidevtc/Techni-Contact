<?php
	require_once('extranet_v3_functions.php'); 
	require_once(ADMIN.'logo.php');
	
	$ppictures_list		= $_POST['ppictures_list'];
	$idproduct			= mysql_escape_string($_POST['idp']);
	$type				= mysql_escape_string($_POST['type']);
	
	if(!empty($_SESSION['extranet_user_id']) && !empty($ppictures_list) && !empty($idproduct) && !empty($type)){
		
		
		$dirNames = array('zoom', 'thumb_small', 'thumb_big', 'card');
		
		if(strcmp($type,'new')==0){
			$dir_INCLUDES 	= PRODUCTS_IMAGE_ADV_INC;
		}else if(strcmp($type,'edit')==0){
			//$dir_INCLUDES 	= PRODUCTS_IMAGE_INC;
			$dir_INCLUDES 	= PRODUCTS_IMAGE_ADV_INC;
		}
		
		
		$temp_count			= 1;
		while(is_file($dir_INCLUDES."thumb_small/".$idproduct."-".$temp_count.".jpg")){
			foreach($dirNames as $dirName){
				$currentFileName	= $idproduct.'-'.$temp_count.'.jpg';
				$tmpCurrentFileName	= $idproduct.'-xtemp-'.$temp_count.'.jpg';
				rename($dir_INCLUDES.$dirName."/".$currentFileName, $dir_INCLUDES.$dirName."/".$tmpCurrentFileName);
			}	//End foreach	
			
			$temp_count++;
		}//end while
		
		
		
		//echo('<br /><br />Creation Tmp OK !<br />');
		
		
		//Start Renaming 
		$rename_count_array	= 0;
		$rename_count		= 1;
		while($ppictures_list[$rename_count_array]){
		
			foreach($dirNames as $dirName){
				$currentFileName	= $idproduct.'-xtemp-'.$ppictures_list[$rename_count_array].'.jpg';
				$newFileName		= $idproduct.'-'.$rename_count.'.jpg';
			
				//echo($dir_INCLUDES.$dirName."/".$currentFileName.', '.$dir_INCLUDES.$dirName."/".$newFileName.'<br />');
				
				rename($dir_INCLUDES.$dirName."/".$currentFileName, $dir_INCLUDES.$dirName."/".$newFileName);
				
			}//End foreach
			
			
			$rename_count++;
			$rename_count_array++;
		}//end while
		
		//echo('<br /> Moving files OK');
		
		
		//Start Deleting the tmp files
		$unlink_count=1;
		while(is_file($dir_INCLUDES."thumb_small/".$idproduct."-xtemp-".$unlink_count.".jpg")){
			unlink($dir_INCLUDES."thumb_small/".$idproduct."-xtemp-".$unlink_count.".jpg");
			unlink($dir_INCLUDES."thumb_big/".$idproduct."-xtemp-".$unlink_count.".jpg");
			unlink($dir_INCLUDES."zoom/".$idproduct."-xtemp-".$unlink_count.".jpg");
			unlink($dir_INCLUDES."card/".$idproduct."-xtemp-".$unlink_count.".jpg");
	
			$unlink_count++;
		}//end while
		
		
		//echo('<br /> Deleting tmp files OK');
		
		echo('Enregistrement d\'odre avec succ&egrave;s');
		
	}//end if empty session

?>