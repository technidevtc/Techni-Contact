<?php

	$uid	= mysql_escape_string($_GET['uid']);
	

	if(!empty($uid)){
	
		//Looking for the matching with the key
		$res_get_user_by_uid = $db->query("SELECT 
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
												e_users.webpass='".$uid."'
											AND
												a.deleted != 1
											AND
												a.actif=1", __FILE__, __LINE__);		
			
		//mysql_num_rows($res_get_user_by_uid);
		//$content_get_user_by_uid = $db->fetchAssoc($res_get_user_by_uid)
			
		if(mysql_num_rows($res_get_user_by_uid)==1){
			$content_get_user_by_uid = $db->fetchAssoc($res_get_user_by_uid);
			
			$_SESSION['extranet_user_ip']				= client_ip();
			$_SESSION['extranet_user_id']				= $content_get_user_by_uid['id'];
			$_SESSION['extranet_user_actif']			= $content_get_user_by_uid['actif'];
			$_SESSION['extranet_user_c']				= $content_get_user_by_uid['c'];	
			$_SESSION['extranet_user_webpass']			= $content_get_user_by_uid['webpass'];
			$_SESSION['extranet_user_contact']			= $content_get_user_by_uid['contact'];
			$_SESSION['extranet_user_name1']			= $content_get_user_by_uid['nom1'];
			$_SESSION['extranet_user_email']			= $content_get_user_by_uid['email'];
			$_SESSION['extranet_user_category']			= $content_get_user_by_uid['category'];
			$_SESSION['extranet_user_litigation_time']	= $content_get_user_by_uid['litigation_time'];
			$_SESSION['extranet_user_parent']			= $content_get_user_by_uid['parent'];
			
			//Save the informations and redirect to Home Sweet Home
			//$res_get_user_normal = $db->query("INSERT INTO..", __FILE__, __LINE__);
			
			$log_session	= addslashes($_SESSION['extranet_user_name1']).' | '.$_SESSION['extranet_user_ip'];
			$log_action		= 'Identification de l\\\'utilisateur ('.$_SESSION['extranet_user_id'].') # uid';
			
			$res_save_log = $db->query("INSERT INTO extranetlogs(id, timestamp, session, action)
										values(NULL, ".time().", '".$log_session."', '".$log_action."')", __FILE__, __LINE__);
										
			//For the old extranet
			if(strcmp($_SESSION['extranet_user_category'],__ADV_CAT_SUPPLIER__)==0){
				$_SESSION['id'] 	= $content_get_user_by_uid['id'];
				$_SESSION['ip'] 	= $_SERVER['REMOTE_ADDR'];
				$_SESSION['login'] 	= $content_get_user_by_uid['login'];
				$_SESSION['pass']  	= $content_get_user_by_uid['pass'];
			}
			
			
			/*if(strcmp($_SESSION['extranet_user_category'],__ADV_CAT_ADVERTISER__)==0){
				//Annonceur
				header('Location: '.EXTRANET_URL.'advertiser-home.html');
				
			}else if(strcmp($_SESSION['extranet_user_category'],__ADV_CAT_SUPPLIER__)==0){
				//Fournisseur
				header('Location: '.EXTRANET_URL.'supplier-home.html');
				
			}else if(strcmp($_SESSION['extranet_user_category'],__ADV_CAT_ADVERTISER_NOT_CHARGED__)==0){
				//Annonceur non chargé
				header('Location: '.EXTRANET_URL.'advertiser-not-charged-home.html');
				
			}else if(strcmp($_SESSION['extranet_user_category'],__ADV_CAT_PROSPECT__)==0){
				//Prospect
				header('Location: '.EXTRANET_URL.'prospect-home.html');
			
			}else if(strcmp($_SESSION['extranet_user_category'],__ADV_CAT_BLOCKED__)==0){
				//Blocké
				header('Location: '.EXTRANET_URL.'blocked-home.html');
				
			}else if(strcmp($_SESSION['extranet_user_category'],__ADV_CAT_LITIGATION__)==0){
				//Litige
				header('Location: '.EXTRANET_URL.'litigation-home.html');
			}else{
					header('Location: '.EXTRANET_URL.'login.html');
			}*/
			
		}else{
			//Key doesn't match
			header('Location: '.EXTRANET_URL.'login.html?c=3215687');
		}//end else if check key
	
	}//end if(!empty($uid))


?>