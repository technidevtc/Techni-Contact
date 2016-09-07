<?php
	require_once('functions.php'); 
	
	$id								= mysql_escape_string($_POST['id']);
	$name							= mysql_escape_string($_POST['name']);
	//$login						= mysql_escape_string($_POST['login']);
	$password						= mysql_escape_string($_POST['password']);
	$description					= mysql_escape_string($_POST['description']);
	$active							= mysql_escape_string($_POST['active']);
	
	$access_pages					= mysql_escape_string($_POST['access_pages']);
	$access_tables_segment			= mysql_escape_string($_POST['access_tables_segment']);
	$access_tables_export			= mysql_escape_string($_POST['access_tables_export']);
	
	if(empty($_SESSION['marketing_user_id'])){
		echo('<a href="login.php">Session expir&eacute;e. Vous devez vous connecter</a>');
	}else if(!empty($id) && !empty($name) && !empty($active)){
		
		try{
			//Update the user
			if(!empty($password)){
				$optional_password	= " ,password='".MD5($password)."'";
			}		
			$update_the_user		= "UPDATE marketing_users 
										SET 
											name='".$name."',
											description='".$description."', 
											active='".$active."',
											date_last_change=NOW()
											".$optional_password." 
										WHERE
											id=".$id."";
	
			$res_update_the_user 			= $db->query($update_the_user, __FILE__, __LINE__);
			
			//$last_inserted_user_id	= mysql_insert_id();
			
			//Check and graint user roles
			//Pages
			
			//Check if the row exist 
			
			$check_user_page_access = "SELECT 
											id
										FROM
											marketing_roles_acces_users
										WHERE
											id_user=".$id."";
											
			$res_check_user_page_access	= $db->query($check_user_page_access, __FILE__, __LINE__);
			
			if(mysql_num_rows($res_check_user_page_access)>0){
			
				$content_check_user_page_access	= $db->fetchAssoc($res_check_user_page_access);
				
				//Update this role
				$update_user_page_access 	= "UPDATE marketing_roles_acces_users
												SET 
													content='".$access_pages."',
													date_last_change=NOW()
												WHERE
													id_user=".$id."
												AND
													id=".$content_check_user_page_access['id']."";
											
				$res_update_user_page_access	= $db->query($update_user_page_access, __FILE__, __LINE__);
			
			}else{
				//Insert role
				$insert_user_page_access 	= "INSERT INTO marketing_roles_acces_users (id, id_user,
													content, date_creation,
													date_last_change)
													values(NULL, ".$id.",
													'".$access_pages."', NOW(),
													'0000-00-00 00:00:00')";
											
				$res_insert_user_page_access	= $db->query($insert_user_page_access, __FILE__, __LINE__);
			}//end else if if(mysql_num_rows($res_check_user_page_access)>0)
				
			
			
			
			//Tables "Segments"
			//Checking existing row
			$check_user_table_segment 	= "SELECT 
												id
											FROM
												marketing_roles_acces_tables
											WHERE
												id_user=".$id."
											AND
												type='segment'";
											
			$res_check_user_table_segment	= $db->query($check_user_table_segment, __FILE__, __LINE__);
			
			if(mysql_num_rows($res_check_user_table_segment)>0){
				//Update row
				$content_check_user_table_segment	= $db->fetchAssoc($res_check_user_table_segment);
				
				$update_user_table_segment 	= "UPDATE marketing_roles_acces_tables
												SET 
													content='".$access_tables_segment."',
													date_last_change=NOW()
												WHERE
													id_user=".$id."
												AND
													type='segment'
												AND
													id=".$content_check_user_table_segment['id']."";
											
				$res_update_user_table_segment	= $db->query($update_user_table_segment, __FILE__, __LINE__);
				
			}else{
				//Insert row
				$insert_user_table_segment 	= "INSERT INTO marketing_roles_acces_tables (id, id_user,
												content, type, 
												date_creation, date_last_change)
												
												VALUES(NULL, ".$id.",
												'".$access_tables_segment."', 'segment',
												NOW(), '0000-00-00 00:00:00')";
											
				$res_insert_user_table_segment	= $db->query($insert_user_table_segment, __FILE__, __LINE__);
			
			}//end else if(mysql_num_rows($res_check_user_table_segment)>0)
			
			
			//Tables "Export"
			//Checking existing row
			$check_user_table_export 	= "SELECT 
												id
											FROM
												marketing_roles_acces_tables
											WHERE
												id_user=".$id."
											AND
												type='export'";
											
			$res_check_user_table_export	= $db->query($check_user_table_export, __FILE__, __LINE__);
			
			if(mysql_num_rows($res_check_user_table_export)>0){
				//Update row
				$content_check_user_table_export	= $db->fetchAssoc($res_check_user_table_export);
				
				$update_user_table_export 	= "UPDATE marketing_roles_acces_tables
												SET 
													content='".$access_tables_export."',
													date_last_change=NOW()
												WHERE
													id_user=".$id."
												AND
													type='export'
												AND
													id=".$content_check_user_table_export['id']."";
											
				$res_update_user_table_export	= $db->query($update_user_table_export, __FILE__, __LINE__);
				
			}else{
				//Insert row
				$insert_user_table_export 	= "INSERT INTO marketing_roles_acces_tables (id, id_user,
												content, type, 
												date_creation, date_last_change)
												
												VALUES(NULL, ".$id.",
												'".$access_tables_export."', 'export',
												NOW(), '0000-00-00 00:00:00')";
											
				$res_insert_user_table_export	= $db->query($insert_user_table_export, __FILE__, __LINE__);
			
			}//end else if(mysql_num_rows($res_check_user_table_export)>0)
			
			
			echo('1');
			
			
			//Insert into history !
			$query_insert_history	="INSERT INTO  marketing_users_history(id, action, 
																id_user, action_date)
							VALUES(NULL, 'Edit User ID: ".$id."',
							".$_SESSION['marketing_user_id'].", NOW())";
			$db->query($query_insert_history, __FILE__, __LINE__);
			
			
		}catch(Exception $e){
			echo('Erreur '.$e);
		}
		
	}else{
		echo('Vous avez des erreurs dans votre formulaire !');
	}
?>