<?php
	require_once('extranet_v3_functions.php'); 
	

	if(!empty($_SESSION['extranet_user_id'])){
	
		$contact_id				= mysql_escape_string($_POST['id']);
	
		if(!empty($contact_id)){
		
			$actual_unixtime	= time();
			$res_save_forward_history = $db->query("UPDATE contacts SET
														archived='2',
														acte_archive=".$actual_unixtime."
													WHERE
														id=".$contact_id."
														", __FILE__, __LINE__);
														
			//Contact archived
			echo('1');
			
		}else{
			//Erreur, Merci de recharger la page
			echo('0');
		}//end else if !empty
		
	}else{
		//Forward reconnect !
		echo('-1');
	}//end if empty session
	
?>