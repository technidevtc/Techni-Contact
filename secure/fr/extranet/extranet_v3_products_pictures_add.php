<?php
	require_once('extranet_v3_functions.php'); 
	require_once(ADMIN.'logo.php');

	$id_fiche		= mysql_escape_string($_POST['idf']);
	$type			= mysql_escape_string($_POST['type']);
	$message 		= 'Erreur, merci de r&eacute;essayer !';
	
	
	if(strcmp($type,'new')==0){
		$dir = PRODUCTS_IMAGE_ADV_INC;
	}else if(strcmp($type,'edit')==0){
		//$dir = PRODUCTS_IMAGE_INC;
		$dir = PRODUCTS_IMAGE_ADV_INC;
	}
	
	
	if(!empty($_SESSION['extranet_user_id']) && !empty($id_fiche)){
	
		if (uploadAndProceedImage("product_picture_file", $id_fiche, $dir)) {
			//$message = '<font color="green">Upload effectu&eacute;e avec succ&eacute;s</font>';
			$message = '1';
		}else{
			$message = '<font color="red">Erreur lors de la copie de l\'image produit</font>';
		}
	}
	
	echo $message;
	
?>