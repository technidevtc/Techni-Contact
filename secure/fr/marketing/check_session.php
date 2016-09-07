<?php


	if(!empty($_SESSION['marketing_user_id'])){
	
	}//end if empty $_SESSION['marketing_user_id']
	
	
	//if(empty($_SESSION['marketing_user_id']) || strcmp($_SESSION['marketing_user_category'],__ADV_CAT_SUPPLIER__)=='0'){
	if(empty($_SESSION['marketing_user_id'])){
		header('Location: '.MARKETING_URL.'login.php?c=6515654');
	}else if($_SESSION['marketing_user_ip']!=client_ip()){
		//if the ip client has changed we force the user to be connected again !
		header('Location: '.MARKETING_URL.'disconnect.php');
	}
?>