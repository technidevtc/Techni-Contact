<?php

	//Demande Tristan 30/10/2014 16h50
	//Auto check Advertiser category (in every page)
	if(!empty($_SESSION['extranet_user_id'])){
	
		$res_get_user_query	 = "SELECT 
												e_users.id, e_users.c,
												e_users.webpass,
												e_users.login, e_users.pass,
												
												a.nom1, a.contact,
												a.category, a.litigation_time,
												a.actif, a.email,
												a.parent
												
											FROM
												extranetusers e_users 
													LEFT JOIN advertisers a ON a.id=e_users.id
											WHERE
												e_users.id='".$_SESSION['extranet_user_id']."'
											AND
												a.deleted != 1
											AND
												a.actif=1";
												
		$res_get_user_normal = $db->query($res_get_user_query, __FILE__, __LINE__);
													
		$content_get_user_normal = $db->fetchAssoc($res_get_user_normal);

		
		//$_SESSION['extranet_user_ip']				= client_ip();
		//$_SESSION['extranet_user_id']				= $content_get_user_by_uid['id'];
		//$_SESSION['extranet_user_actif']			= $content_get_user_normal['actif'];
		//$_SESSION['extranet_user_c']				= $content_get_user_by_uid['c'];	
		//$_SESSION['extranet_user_webpass']			= $content_get_user_by_uid['webpass'];
		//$_SESSION['extranet_user_contact']			= $content_get_user_by_uid['contact'];
		//$_SESSION['extranet_user_name1']			= $content_get_user_by_uid['nom1'];
		//$_SESSION['extranet_user_email']			= $content_get_user_by_uid['email'];
		$_SESSION['extranet_user_category']			= $content_get_user_normal['category'];
		//$_SESSION['extranet_user_litigation_time']	= $content_get_user_by_uid['litigation_time'];
		//$_SESSION['extranet_user_parent']			= $content_get_user_by_uid['parent'];

	
		//For the old extranet
		if(strcmp($_SESSION['extranet_user_category'],__ADV_CAT_SUPPLIER__)==0){
			$_SESSION['id'] 	= $content_get_user_normal['id'];
			$_SESSION['ip'] 	= $_SERVER['REMOTE_ADDR'];
			$_SESSION['login'] 	= $content_get_user_normal['login'];
			$_SESSION['pass']  	= $content_get_user_normal['pass'];
		}
		
		
		
	}//end if empty $_SESSION['extranet_user_id']
	

	
	
	//if(empty($_SESSION['extranet_user_id']) || strcmp($_SESSION['extranet_user_category'],__ADV_CAT_SUPPLIER__)=='0'){
	if(empty($_SESSION['extranet_user_id'])){
		header('Location: '.EXTRANET_URL.'login.html?c=6515654');
	}else if(strcmp($_SESSION['extranet_user_category'],__ADV_CAT_SUPPLIER__)==0){
		//Redirect to the old extranet (Category==1)	
		header('Location: '.EXTRANET_URL.'index-old-extranet.html'); die;
	}else if($_SESSION['extranet_user_ip']!=client_ip()){
		//if the ip client has changed we force the user to be connected again !
		header('Location: '.EXTRANET_URL.'disconnect.php');
	}
?>