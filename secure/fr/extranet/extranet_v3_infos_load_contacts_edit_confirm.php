<?php
	require_once('extranet_v3_functions.php'); 
	
	$id						= mysql_escape_string($_POST['id']);
	$prenom					= mysql_escape_string($_POST['prenom']);
	$nom					= mysql_escape_string($_POST['nom']);
	$email					= mysql_escape_string($_POST['email']);
	
	if(!empty($_SESSION['extranet_user_id'])){
	
		if(!empty($id) && !empty($prenom) && !empty($nom) && !empty($email)){
		
			
			$sql_update_contact	= "UPDATE extranet_contacts_users set nom='".$nom."',
									prenom='".$prenom."',
									email='".$email."',
									date_modification=NOW()
									
									WHERE
										id=".$id." 
									AND 
										idadvertiser=".$_SESSION['extranet_user_id']." ";
		
			$res_update_contact	=  $db->query($sql_update_contact, __FILE__, __LINE__);
		
			//That is OK
			echo('1');
				
		
		}else{
			echo('0');
		}//end else if !empty fields
	
	}else{
		echo('-1');
	}//End else if(!empty($_SESSION['extranet_user_id']))
?>