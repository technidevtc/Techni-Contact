<?php
	require_once('extranet_v3_functions.php'); 
	
	if(!empty($_SESSION['extranet_user_name1'])){
		
		$log_session	= addslashes($_SESSION['extranet_user_name1']).' | '.$_SESSION['extranet_user_ip'];
		$log_action		= 'Déconnexion';
		
		$res_save_log = $db->query("INSERT INTO extranetlogs(id, timestamp, session, action)
											values(NULL, ".time().", '".$log_session."', '".$log_action."')", __FILE__, __LINE__);
	}//end empty

		
	session_destroy();
	
	header('Location: '.EXTRANET_URL.'');
?>