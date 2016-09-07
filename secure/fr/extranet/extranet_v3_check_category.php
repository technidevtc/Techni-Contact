<?php

	//The category of users to block
	$category_block_menu	= array(3, 4, 5);
	
	
	if(in_array($_SESSION['extranet_user_category'], $category_block_menu)){
		header('Location: extranet-v3-home.html');
	}
?>