<?php 
	require_once('functions.php'); 
	
	
	$login			= mysql_escape_string($_POST['flogin']);
	$password		= MD5(mysql_escape_string($_POST['fpassword']));
	$user_os		= mysql_escape_string($_POST['fos']);
	$user_navigator	= mysql_escape_string($_POST['fnavigator']);
	$user_time		= mysql_escape_string($_POST['fusertime']);
		
	if(!empty($login) && !empty($password)){
		$res_get_user = $db->query("SELECT 
											id, name,
											login, date_creation,
											active
										FROM
											marketing_users
										WHERE
											login='".$login."'
										AND
											password='".$password."'
										AND
											deleted='no'	
										", __FILE__, __LINE__);
											
		if(mysql_num_rows($res_get_user)==1){
			$content_get_user = $db->fetchAssoc($res_get_user);
			
			if(strcmp($content_get_user['active'],'yes')==0){
			
				$_SESSION['marketing_user_ip']				= client_ip();
				$_SESSION['marketing_user_id']				= $content_get_user['id'];		
				$_SESSION['marketing_user_name']			= $content_get_user['name'];
				$_SESSION['marketing_user_email']			= $content_get_user['login'];
				$_SESSION['marketing_user_date_creation']	= $content_get_user_normal['date_creation'];
				
				//Save the informations and redirect to Home Sweet Home
				//$res_get_user_normal = $db->query("INSERT INTO..", __FILE__, __LINE__);
				

				$log_action		= 'Identification de l\'utilisateur ('.$_SESSION['marketing_user_id'].') # ';
				$log_action		.= $user_os.' # '.$user_navigator.' # '.date('Y-m-d H:i:s',$user_time);
				$log_action		= addslashes($log_action);
				
				$res_save_log = $db->query("INSERT INTO marketing_connexion(id, id_user,
											ip, date_action,
											type, extra)
											values(NULL, ".$content_get_user['id'].", 
											'".$_SESSION['marketing_user_ip']."', NOW(),
											'connect', '".$log_action."')", __FILE__, __LINE__);
				
				header('Location: '.MARKETING_URL.'home.php');
			
			}else{
				//The user is disabled!
				header('Location: '.MARKETING_URL.'login.php?c=1387945');
			}//end else if check if the user is enabled
			
		
		}else{
			//Login and password doesn't match
			header('Location: '.MARKETING_URL.'login.php?c=7032698');
		}
		
	}else{
		//Empty Login or Password redirect to login form
		header('Location: '.MARKETING_URL.'login.php?c=6521589');
	}//end else if(!empty($login) && !empty($password))
		

?>