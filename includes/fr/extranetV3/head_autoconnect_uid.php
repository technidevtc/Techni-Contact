<?php

if(!isset($_SESSION)) {
    session_name('extranet');
	session_start();
}

//Start Code add on 18/11/2014 to include the auto uid on the extranet

	function client_ip_extranet(){
		$ip		=	'';
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}

	$uid	= mysql_escape_string($_GET['uid']);
	
	if(!empty($uid)){	
		//Looking for the matching with the key
		$res_get_user_by_uid = $handle->query("SELECT 
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
			$content_get_user_by_uid = $handle->fetchAssoc($res_get_user_by_uid);
			
			$_SESSION['extranet_user_ip']				= client_ip_extranet();			
			$_SESSION['extranet_user_id']				= $content_get_user_by_uid['id'];
			$_SESSION['extranet_user_actif']			= $content_get_user_normal['actif'];
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
			
			$res_save_log = $handle->query("INSERT INTO extranetlogs(id, timestamp, session, action)
										values(NULL, ".time().", '".$log_session."', '".$log_action."')", __FILE__, __LINE__);
										
			//For the old extranet
			if(strcmp($_SESSION['extranet_user_category'],__ADV_CAT_SUPPLIER__)==0){			
				$_SESSION['id'] 	= $content_get_user_by_uid['id'];
				$_SESSION['ip'] 	= $_SERVER['REMOTE_ADDR'];
				$_SESSION['login'] 	= $content_get_user_by_uid['login'];
				$_SESSION['pass']  	= $content_get_user_by_uid['pass'];
				
				$login 	= $_SESSION['login'];
				$pass	= $_SESSION['pass'];	
			}
		}
	}


//End Code add on 18/11/2014 to include the auto uid on the extranet

?>