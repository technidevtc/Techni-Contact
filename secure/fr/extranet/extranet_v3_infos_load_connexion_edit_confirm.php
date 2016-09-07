<?php
	require_once('extranet_v3_functions.php'); 
	
	$login					= mysql_escape_string($_POST['login']);
	$password				= mysql_escape_string($_POST['password']);

	
	
	if(!empty($_SESSION['extranet_user_id'])){
	
	
		if(!empty($login) && !empty($password)){
		
		
			//We have to check for the login if it exist 
			//And it is not reserved by a other user
			$sql_check_login	= "SELECT
										login									
									FROM
										extranetusers
									WHERE
										login='".$login."'
									AND
										id!=".$_SESSION['extranet_user_id']."";
				
			$res_check_login	=  $db->query($sql_check_login, __FILE__, __LINE__);
			
			if(mysql_num_rows($res_check_login)==0){
				//Proceed the lgin is not reserved by a other user so we have to 
				//Save the new login (or the old one) and the new password
			
				$sql_update_connexion	= "UPDATE extranetusers 
											SET 
												login='".$login."', 
												pass='".$password."'
											WHERE 
												id=".$_SESSION['extranet_user_id']."
											";
				
				$res_update_connexion	=  $db->query($sql_update_connexion, __FILE__, __LINE__);
			
				//That is OK
				echo('1');
				
			}else{
				//The login is already reserved by a user !
				echo('-2');
			}//end else if mysql_num_rows()==0
				
		
		}else{
			echo('0');
		}//end else if !empty fields
	
	}else{
		echo('-1');
	}//End else if(!empty($_SESSION['extranet_user_id']))
?>