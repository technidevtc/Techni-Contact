<?php 
	require_once('functions.php'); 
	//require_once('check_session.php');

	if(empty($_SESSION['marketing_user_id'])){
		throw new Exception('<a href="/fr/marketing/login.php">Veuillez vous reconnecter</a>.');
	}
	
	
	
	//Get All the Fields
	//Get the Table ID From the Segment ID
	//Check the roles (Page) And (Action)
	//Test if The Message ID is Empty (Add) Else it's a Edit
	//Replace "#and#" By "&" For "message_object" AND "message_content"

	$message_id				= mysql_escape_string($_POST['message_id']);
	$message_title			= mysql_escape_string($_POST['message_title']);
	$sender_email			= mysql_escape_string($_POST['sender_email']);
	$sender_name			= mysql_escape_string($_POST['sender_name']);
	$reply_email			= mysql_escape_string($_POST['reply_email']);
	$message_object			= mysql_escape_string($_POST['message_object']);
	$message_content		= mysql_escape_string($_POST['message_content']);
	
	$id_segment				= mysql_escape_string($_POST['id_segment']);
	
	
	//To use in the log !
	$message_log			= "";
	
	/*if(empty($_SESSION['marketing_user_id'])){
		throw new Exception('Erreur, Merci de v&eacute;rifier votre formulaire !');
	}*/
	
	//Get the Table Informations
	$query_get_table_info	="SELECT
									id_table
								FROM
									marketing_segment
								WHERE
									id=".$id_segment."";
							
	$res_get_table_info 	= $db->query($query_get_table_info, __FILE__, __LINE__);
	$data_get_table_info 	= $db->fetchAssoc($res_get_table_info);

	
	//Check the permissions to access to this Page
	//Every page or module have a different ID !	
	require_once('check_session_page_query.php');
	
	
	//Check the permissions to access to this Table
	require_once('check_session_table_query.php');

	
	
	//Create Message => 9
	//Edit Message => 10
	
	//Check if the user have the right to access to this page !
	if(strpos($content_get_user_page_permissions['content'],'#9#')===FALSE && strpos($content_get_user_page_permissions['content'],'#10#')===FALSE){
		echo('<a href="/fr/marketing/">Vous n\'avez pas le droit d\'acc&eacute;der &agrave; cette page !</a>');
	}else if(strpos($content_get_user_tables_access_permissions['content'],'#'.$data_get_table_info['id_table'].'#')===FALSE){
		echo('<a href="/fr/marketing/">Vous n\'avez pas le droit d\'acc&eacute;der &agrave; cette table !</a>');
	}else if(!empty($_SESSION['marketing_user_id']) && !empty($message_title) && !empty($sender_email) && !empty($sender_name) && !empty($reply_email) && !empty($message_object) && !empty($message_content) && !empty($id_segment)){
		
		//Check if the fields are not empty
			
			//Replace "#and#" By "&"
			$message_object		= str_replace("#and#", "&", $message_object);
			$message_content	= str_replace("#and#", "&", $message_content);
			
			//Detect the message_id if empty we have to insert else we have to update
			if(empty($message_id)){
				//We have to Insert
				$query_message_insert	="INSERT INTO marketing_messages(id, id_segment,
												name, email_sender, 
												name_sender, email_reply,
												object, content, 
												etat, date_creation, 
												date_last_edit, created_user, 
												edited_user)
											VALUES(NULL, ".$id_segment.",
												'".$message_title."', '".$sender_email."', 
												'".$sender_name."', '".$reply_email."',
												'".$message_object."', '".$message_content."',  
												'saved', NOW(), 
												'0000-00-00 00:00:00', ".$_SESSION['marketing_user_id'].",
												0)";
							
				$res_message_insert 	= $db->query($query_message_insert, __FILE__, __LINE__);
				
				
				/*Dans le cas d'ajout
				Il faut retourner une action pour affecter le nouveau ID dans le formulaire pour que ça soit operationnel si on clique sur le meme bouton/action dans la même page.*/

				//Get the last inserted ID
				$last_message_inserted	= mysql_insert_id();
				
				//Print ID to a hidden Div that be executed by the Javascript !
				echo('<div id="message_final_results_container">');
					echo('<div>');
						echo('<font color="green">Message cr&eacute;&eacute; avec succ&egrave;s !</font>');
					echo('</div>');
					
					echo('<div id="message_final_results_container_js" style="display:none;">');
						echo('document.getElementById("message_hidden_id").value="'.$last_message_inserted.'"; ');
						echo('document.getElementById("message_preview_hidden_id").value="'.$last_message_inserted.'"; ');
						echo('document.getElementById("message_test_hidden_id").value="'.$last_message_inserted.'"; ');
					echo('</div>');
				echo('</div>');
				
	
				$message_log	= "Message: Create message ID: ".$last_message_inserted;
				$query_marketing_log	="INSERT INTO marketing_users_history(id, action,
												id_user, action_date)
											VALUES(NULL, '".$message_log."',
												".$_SESSION['marketing_user_id'].", NOW())";
							
				$res_marketing_log 	= $db->query($query_marketing_log, __FILE__, __LINE__);
				
			}else{
				//We have to Update
				$query_message_insert	="UPDATE marketing_messages
											SET id_segment=".$id_segment.", 
												name='".$message_title."', 
												email_sender='".$sender_email."', 
												name_sender='".$sender_name."', 
												email_reply='".$reply_email."',
												object='".$message_object."', 
												content='".$message_content."', 
												etat='saved', 
												date_last_edit=NOW(), 
												edited_user=".$_SESSION['marketing_user_id']."
											WHERE 
												id=".$message_id;
							
				$res_message_insert 	= $db->query($query_message_insert, __FILE__, __LINE__);
				
				//Print ID to a hidden Div that be executed by the Javascript !
				echo('<div id="message_final_results_container">');
					echo('<div>');
						echo('<font color="green">Message enregistr&eacute; avec succ&egrave;s !</font>');
					echo('</div>');
					
					echo('<div id="message_final_results_container_js" style="display:none;">');
						//echo('document.getElementById("message_hidden_id").value="'.$message_id.'";');
					echo('</div>');
				echo('</div>');
				
				
				$message_log	= "Message: Edit message ID: ".$message_id;
				$query_marketing_log	="INSERT INTO marketing_users_history(id, action,
												id_user, action_date)
											VALUES(NULL, '".$message_log."',
												".$_SESSION['marketing_user_id'].", NOW())";
							
				$res_marketing_log 	= $db->query($query_marketing_log, __FILE__, __LINE__);
				
			}//end else update 
				
	
	}else{
		echo('Vous avez des erreurs dans votre formulaire !');
	}//End Else If !empty($_SESSION['marketing_user_id'] && !empty all the fields)
	
?>