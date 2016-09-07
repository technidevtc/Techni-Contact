<?php
	require_once('functions.php'); 
		
	$login							= mysql_escape_string($_POST['login']);
	
	if(!empty($login)){
		
		try{
			//Preparing query !
			$check_login_query		= "SELECT
											id,
											login
										FROM
											marketing_users
										WHERE
											login like '".$login."'";
			$res_get_login 			= $db->query($check_login_query, __FILE__, __LINE__);
			
			if(mysql_num_rows($res_get_login)==0){
				echo('1');
			}else{
				echo('0');
			}
		}catch(Exception $e){
			echo('0');
		}
		
	}else{
		
		echo('0');
	
	}
?>