<?php
	require_once('extranet_v3_functions.php'); 
	
	$id						= mysql_escape_string($_POST['id']);
	
	if(!empty($_SESSION['extranet_user_id'])){
	
		if(!empty($id)){
		
			
			$sql_delete_contact	= "DELETE FROM extranet_contacts_users 
									WHERE
										id=".$id." 
									AND 
										idadvertiser=".$_SESSION['extranet_user_id']." ";
		
			$res_delete_contact	=  $db->query($sql_delete_contact, __FILE__, __LINE__);
		
			//That is OK
			echo('1');
				
		
		}else{
			echo('0');
		}//end else if !empty fields
	
	}else{
		echo('-1');
	}//End else if(!empty($_SESSION['extranet_user_id']))
?>