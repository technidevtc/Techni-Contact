<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 20 décembre 2004

 Fichier : /secure/manager/advertisers/ve.php
 Description : Redir extranet annonceur donné
 Changed on: 12 Novembre 2014

/=================================================================*/

session_name('extranet');
session_start();

if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
		require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

//Instantiate the call of the DB
$db = DBHandle::get_instance();



//Code commented on 12/11/2014
//To allow the connection in the new extranet and the old one !
/*

if(!isset($_GET['id']) || !preg_match('/^[1-9][0-9]*$/', $_GET['id']))
	exit();

/////////////////////////////

$ok = false;

if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
		require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

require(ADMIN."logs.php");

$db = DBHandle::get_instance();
$user = new BOUser();

if(!$user->login()) exit();


session_name('extranet');
session_start();

if(($result = $handle->query('select login, pass from extranetusers where id = \'' . $handle->escape($_GET['id']). '\'', __FILE__, __LINE__)) && $handle->numrows($result, __FILE__, __LINE__) == 1)
	$r = $handle->fetch($result);
else exit;

session_regenerate_id();

$_SESSION['id'] = $_GET['id'];
$_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
$_SESSION['login'] = $r[0];
$_SESSION['pass']  = $r[1];

header('Location: ' . EXTRANET_URL . 'index.html?' . session_name() . '=' . session_id());
exit;
*/

//Start new code 12/11/2014





	function client_ip(){
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
	
	$id				= mysql_escape_string($_GET['id']);

	if(!empty($id)){

		$res_get_user_normal = $db->query("SELECT 
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
											e_users.id=".$id." 
										AND 
											a.deleted != 1 
										AND 
											a.actif=1", __FILE__, __LINE__);
											
											
		if(mysql_num_rows($res_get_user_normal)==1){
		
			$content_get_user_normal = $db->fetchAssoc($res_get_user_normal);
			
			$_SESSION['extranet_user_ip']				= client_ip();
			$_SESSION['extranet_user_id']				= $content_get_user_normal['id'];
			$_SESSION['extranet_user_actif']			= $content_get_user_normal['actif'];
			$_SESSION['extranet_user_c']				= $content_get_user_normal['c'];	
			$_SESSION['extranet_user_webpass']			= $content_get_user_normal['webpass'];				
			$_SESSION['extranet_user_contact']			= $content_get_user_normal['contact'];
			$_SESSION['extranet_user_name1']			= $content_get_user_normal['nom1'];
			$_SESSION['extranet_user_email']			= $content_get_user_normal['email'];
			$_SESSION['extranet_user_category']			= $content_get_user_normal['category'];
			$_SESSION['extranet_user_litigation_time']	= $content_get_user_normal['litigation_time'];
			$_SESSION['extranet_user_parent']			= $content_get_user_normal['parent'];
			
			//Save the informations and redirect to Home Sweet Home
		
			$log_session	= addslashes($_SESSION['extranet_user_name1']).' | '.$_SESSION['extranet_user_ip'];
			$log_action		= 'Identification de l\'utilisateur ('.$_SESSION['extranet_user_id'].') # manager # ';
			$log_action		.= $user_os.' # '.$user_navigator.' # '.date('Y-m-d H:i:s',time());
			$log_action		= addslashes($log_action);
			
			$res_save_log = $db->query("INSERT INTO extranetlogs(id, timestamp, session, action)
										values(NULL, ".time().", '".$log_session."', '".$log_action."')", __FILE__, __LINE__);

										
			if(strcmp($_SESSION['extranet_user_category'],__ADV_CAT_SUPPLIER__)==0){
				//Annonceur	=> Category ==1
			
				$_SESSION['id'] 	= $id;
				$_SESSION['ip'] 	= $_SERVER['REMOTE_ADDR'];
				$_SESSION['login'] 	= $content_get_user_normal['login'];
				$_SESSION['pass']  	= $content_get_user_normal['pass'];

				header('Location: '.EXTRANET_URL.'index-old-extranet.html');
				
			}else{		
				header('Location: '.EXTRANET_URL.'extranet-v3-home.html');
			}
					
		}else{
			exit();
		}
		
		
	}else{
		exit();
	}//!empty	
	
?>
		