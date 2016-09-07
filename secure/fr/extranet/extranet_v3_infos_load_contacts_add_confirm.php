<?php
	require_once('extranet_v3_functions.php'); 
	
	$prenom					= mysql_escape_string($_POST['prenom']);
	$nom					= mysql_escape_string($_POST['nom']);
	$email					= mysql_escape_string($_POST['email']);
	
	if(!empty($_SESSION['extranet_user_id'])){
	
		if(!empty($prenom) && !empty($nom) && !empty($email)){
		
			//Check if this email exist already
			$sql_check_contact	= "SELECT 
										id
									FROM 
										extranet_contacts_users
									WHERE
										email='".$email."'
									AND
										idadvertiser=".$_SESSION['extranet_user_id']."";
			
			$res_check_contact	=  $db->query($sql_check_contact, __FILE__, __LINE__);
			
			if(mysql_num_rows($res_check_contact)==0){
			
				$sql_insert_contact	= "INSERT INTO extranet_contacts_users(id, idadvertiser,
										nom, prenom,
										email, date_creation,
										date_modification)
										
										VALUES(NULL, ".$_SESSION['extranet_user_id'].",
											'".$nom."', '".$prenom."',
											'".$email."',	NOW(), 
											'0000-00-00 00:00:00')";
			
				$res_insert_contact	=  $db->query($sql_insert_contact, __FILE__, __LINE__);
			
				//That is OK
				echo('1');
				
			}else{
				//Email exist already for this advertiser !
				echo('-2');
			}
		
		}else{
			echo('0');
		}//end else if !empty fields
	
	}else{
		echo('-1');
	}//End else if(!empty($_SESSION['extranet_user_id']))
?>