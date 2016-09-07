<?php

	require_once('check_session_table_query.php');

//Checking session by page !
//We have to receive a initiate var "$page_permission_id" in the parent page	(page of call)
//And "$ignore_verification" to ignore the verification (ex: home page)
//We have to read the user permissions


	require_once('check_session_page_query.php');
		
	if(strcmp($ignore_verification,'yes')!=0){
		$page_id	= '#'.$page_permission_id.'#';
		//Check if the user have the right to access to this page !
		if(strpos($content_get_user_page_permissions['content'],$page_id)===FALSE){
			//The user don't have the right 
			//So we gonna redirect him the the home page
			header('Location: '.MARKETING_URL.'home.php');
		}
	}

?>