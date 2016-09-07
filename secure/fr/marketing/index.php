<?php 
	require_once('functions.php'); 
	
	if(!empty($_SESSION['marketing_user_id'])){
		header('Location: '.MARKETING_URL.'home.php');
	}else{
		header('Location: '.MARKETING_URL.'login.php');
	}
?>