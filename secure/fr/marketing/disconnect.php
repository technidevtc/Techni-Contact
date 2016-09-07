<?php
	require_once('functions.php'); 
	
	if(!empty($_SESSION['marketing_user_id'])){
		
		$res_save_log = $db->query("INSERT INTO marketing_connexion(id, id_user,
									ip, date_action, 
									type, extra)
									values(NULL, ".$_SESSION['marketing_user_id'].", 
									'".$_SESSION['marketing_user_ip']."', NOW(),
									'disconnect', '')", __FILE__, __LINE__);
	}//end empty

		
	session_destroy();
	
	header('Location: '.MARKETING_URL.'');
?>