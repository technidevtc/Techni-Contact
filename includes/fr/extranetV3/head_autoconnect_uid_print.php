<?php
// require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
if(!isset($_SESSION)) {
    session_name('extranet');
	session_start();
}

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
		$sql_get_user_by_uid = "SELECT 
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
										a.actif=1";	
		$req_get_user_by_uid  = mysql_query($sql_get_user_by_uid);
		$rows_get_user_by_uid = mysql_num_rows($req_get_user_by_uid);
		
		if($rows_get_user_by_uid >= 1){
			
			$content_get_user_by_uid = mysql_fetch_assoc($req_get_user_by_uid);
			// echo $_SESSION['login'];
			// echo $_SESSION['pass'];
			
			$_SESSION['extranet_user_ip']				= client_ip_extranet();			
			$_SESSION['extranet_user_id']				= $content_get_user_by_uid['id'];
			$_SESSION['extranet_user_c']				= $content_get_user_by_uid['c'];	
			$_SESSION['extranet_user_webpass']			= $content_get_user_by_uid['webpass'];
			$_SESSION['extranet_user_contact']			= $content_get_user_by_uid['contact'];
			$_SESSION['extranet_user_name1']			= $content_get_user_by_uid['nom1'];
			$_SESSION['extranet_user_email']			= $content_get_user_by_uid['email'];
			$_SESSION['extranet_user_category']			= $content_get_user_by_uid['category'];
			$_SESSION['extranet_user_litigation_time']	= $content_get_user_by_uid['litigation_time'];
			$_SESSION['extranet_user_parent']			= $content_get_user_by_uid['parent'];
			
			if(strcmp($_SESSION['extranet_user_category'],__ADV_CAT_SUPPLIER__)==0){			
				$_SESSION['id'] 	= $content_get_user_by_uid['id'];
				$_SESSION['ip'] 	= $_SERVER['REMOTE_ADDR'];
				$_SESSION['login'] 	= $content_get_user_by_uid['login'];
				$_SESSION['pass']  	= $content_get_user_by_uid['pass'];
				
				$login 	= $_SESSION['login'];
				$pass	= $_SESSION['pass'];	
				$user_id = $content_get_user_by_uid['id'];
				
			}
			
		}
	}

?>