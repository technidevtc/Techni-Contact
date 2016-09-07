<?php
	require_once('functions.php'); 
		
	$name							= mysql_escape_string($_POST['name']);
	$login							= mysql_escape_string($_POST['login']);
	$password						= MD5(mysql_escape_string($_POST['password']));
	$description					= mysql_escape_string($_POST['description']);
	$active							= mysql_escape_string($_POST['active']);
	
	$access_pages					= mysql_escape_string($_POST['access_pages']);
	$access_tables_segment			= mysql_escape_string($_POST['access_tables_segment']);
	$access_tables_export			= mysql_escape_string($_POST['access_tables_export']);
	
	if(empty($_SESSION['marketing_user_id'])){
		echo('<a href="login.php">Session expir&eacute;e. Vous devez vous connecter</a>');
	}else if(!empty($name) && !empty($login) && !empty($password) && !empty($active)){
		
		try{
			//Insert the new user
			$insert_new_user		= "INSERT INTO marketing_users (id, name,
											description, login, 
											password, date_creation,
											date_last_change, active,
											deleted)
											values(NULL, '".$name."', 
												'".$description."', '".$login."',
												'".$password."', NOW(),
												'0000-00-00 00:00:00', '".$active."',
												'no')";
												
			$res_get_login 			= $db->query($insert_new_user, __FILE__, __LINE__);
			
			$last_inserted_user_id	= mysql_insert_id();
			
			//Check and graint user roles
			//Pages
			if(!empty($access_pages)){
				//In the Edit we have to search for the existing rows
				
				$insert_new_page_role	= "INSERT INTO marketing_roles_acces_users(id, id_user,
											content, date_creation, 
											date_last_change)
											values(NULL, ".$last_inserted_user_id.", 
												'".$access_pages."', NOW(),
												'0000-00-00 00:00:00')";
												
				$res_get_insert_new_page_role	= $db->query($insert_new_page_role, __FILE__, __LINE__);
			}
			
			
			//Tables "Segments"
			if(!empty($access_tables_segment)){
				//In the Edit we have to search for the existing rows
				
				$insert_new_tables_segment	= "INSERT INTO marketing_roles_acces_tables(id, id_user,
											content, type,
											date_creation, date_last_change)
											values(NULL, ".$last_inserted_user_id.", 
												'".$access_tables_segment."', 'segment',
												NOW(), '0000-00-00 00:00:00')";
												
				$res_get_insert_new_tables_segment	= $db->query($insert_new_tables_segment, __FILE__, __LINE__);
			}
			
			//Tables "Export"
			if(!empty($access_tables_export)){
				//In the Edit we have to search for the existing rows
				
				$insert_new_tables_export	= "INSERT INTO marketing_roles_acces_tables(id, id_user,
											content, type,
											date_creation, date_last_change)
											values(NULL, ".$last_inserted_user_id.", 
												'".$access_tables_export."', 'export',
												NOW(), '0000-00-00 00:00:00')";
												
				$res_get_insert_new_tables_export	= $db->query($insert_new_tables_export, __FILE__, __LINE__);
			}
			
			
			echo('1');
			
			
			//Insert into history !
			$query_insert_history	="INSERT INTO  marketing_users_history(id, action, 
																id_user, action_date)
							VALUES(NULL, 'Create User ID: ".$last_inserted_user_id."',
							".$_SESSION['marketing_user_id'].", NOW())";
			$db->query($query_insert_history, __FILE__, __LINE__);
			
			
			
		}catch(Exception $e){
			echo('Erreur '.$e);
		}
		
	}else{
		echo('Vous avez des erreurs dans votre formulaire !');
	}
?>