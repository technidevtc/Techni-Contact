<?php
	require_once('functions.php'); 
	
	$id								= mysql_escape_string($_POST['id']);

	
	if(empty($_SESSION['marketing_user_id'])){
		echo('<a href="login.php">Session expir&eacute;e. Vous devez vous connecter</a>');
	}else if(!empty($id)){
		
		try{
		
			$update_the_user		= "UPDATE marketing_users 
										SET 
											deleted='yes',
											active='no',
											date_last_change=NOW()
										WHERE
											id=".$id."";
	
			$res_update_the_user 			= $db->query($update_the_user, __FILE__, __LINE__);
			
			
			echo('1');
			
			//Insert into history !
			$query_insert_history	="INSERT INTO  marketing_users_history(id, action, 
																id_user, action_date)
							VALUES(NULL, 'Delete User ID: ".$id."',
							".$_SESSION['marketing_user_id'].", NOW())";
			$db->query($query_insert_history, __FILE__, __LINE__);
			
			
			
		}catch(Exception $e){
			echo('Erreur '.$e);
		}
		
	}else{
		echo('Vous avez des erreurs dans votre formulaire !');
	}
?>