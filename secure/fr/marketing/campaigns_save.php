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

	$campaign_id					= mysql_escape_string($_POST['campaign_id']);
	$campaign_title					= mysql_escape_string($_POST['campaign_title']);
	$campaign_message_selection		= mysql_escape_string($_POST['campaign_message_selection']);
	$campaign_type					= mysql_escape_string($_POST['campaign_type']);
	$campaign_date					= mysql_escape_string($_POST['campaign_date']);
	$campaign_hour					= mysql_escape_string($_POST['campaign_hour']);
	$campaign_minute				= mysql_escape_string($_POST['campaign_minute']);
	$campaign_actived				= mysql_escape_string($_POST['campaign_actived']);
	
	
	
	//To use in the log !
	$campaign_log			= "";
	
	//Get the Table Informations
	$query_get_table_info	="SELECT 									
									m_segment.id_table, 
									m_segment.results_count 

								FROM
									marketing_messages AS m_messages 
										INNER JOIN marketing_segment AS m_segment ON m_messages.id_segment=m_segment.id

								WHERE
									m_messages.id=".$campaign_message_selection."";
							
	$res_get_table_info 	= $db->query($query_get_table_info, __FILE__, __LINE__);
	$data_get_table_info 	= $db->fetchAssoc($res_get_table_info);
	
	//Check the permissions to access to this Page
	//Every page or module have a different ID !	
	require_once('check_session_page_query.php');
	
	
	//Check the permissions to access to this Table
	require_once('check_session_table_query.php');

	
	
	//Create Campaign => 13
	//Edit Campaign => 14
	
	//Check if the user have the right to access to this page !
	if(strpos($content_get_user_page_permissions['content'],'#13#')===FALSE && strpos($content_get_user_page_permissions['content'],'#14#')===FALSE){
		echo('<a href="/fr/marketing/">Vous n\'avez pas le droit d\'acc&eacute;der &agrave; cette page !</a>');
	}else if(strpos($content_get_user_tables_access_permissions['content'],'#'.$data_get_table_info['id_table'].'#')===FALSE){
		echo('<a href="/fr/marketing/">Vous n\'avez pas le droit d\'acc&eacute;der &agrave; cette table !</a>');
	}else if(!empty($_SESSION['marketing_user_id']) && !empty($campaign_title) && !empty($campaign_message_selection) && !empty($campaign_type) && !empty($campaign_hour) && !empty($campaign_actived)){
		
		if(empty($campaign_date)){
			$campaign_date = "0000-00-00";
		}else{
			//Convert the Date
			$campaign_date_array= explode('-',$campaign_date);
			$campaign_date 		=$campaign_date_array[2].'-'.$campaign_date_array[1].'-'.$campaign_date_array[0]; 
		}
		
		if(strcmp($campaign_actived,'oui')==0){
			$campaign_actived_sql	= "Programmed";
		}else{
			$campaign_actived_sql	= "Saved";
		}
		
		//Detect the campaign_id if empty we have to insert else we have to update
			if(empty($campaign_id)){
				
				//We have to Insert
				$query_campaign_insert	="INSERT INTO marketing_campaigns(id, id_message,
												type, name,  
												date_last_sent, emails_brut,
												emails_sent, date_send, 
												hour_send,minute_send, etat, 
												date_creation, date_last_edit, 
												created_user, edited_user)
											VALUES(NULL, ".$campaign_message_selection.",
												'".$campaign_type."', '".$campaign_title."', 
												'0000-00-00 00:00:00', ".$data_get_table_info['results_count'].",
												0, '".$campaign_date."', 
												'".$campaign_hour."','".$campaign_minute."', '".$campaign_actived_sql."', 
												NOW(), '0000-00-00 00:00:00', 
												".$_SESSION['marketing_user_id'].", 0)";
							
				$res_campaign_insert 	= $db->query($query_campaign_insert, __FILE__, __LINE__);
				
				
				/*Dans le cas d'ajout
				Il faut retourner une action pour affecter le nouveau ID dans le formulaire pour que ça soit operationnel si on clique sur le meme bouton/action dans la même page.*/

				//Get the last inserted ID
				$last_campaign_inserted	= mysql_insert_id();
				
				//Print ID to a hidden Div that be executed by the Javascript !
				echo('<div id="campaign_final_results_container">');
					echo('<div>');
						echo('<font color="green">Campagne cr&eacute;&eacute;e avec succ&egrave;s !</font>');
					echo('</div>');
					
					echo('<div id="campaign_final_results_container_js" style="display:none;">');
						echo('document.getElementById("campaigns_hidden_id").value="'.$last_campaign_inserted.'"; ');
					echo('</div>');
				echo('</div>');
				
	
				$campaign_log	= "Campaign: Create campaign ID: ".$last_campaign_inserted;
				$query_marketing_log	="INSERT INTO marketing_users_history(id, action,
												id_user, action_date)
											VALUES(NULL, '".$campaign_log."',
												".$_SESSION['marketing_user_id'].", NOW())";
							
				$res_marketing_log 	= $db->query($query_marketing_log, __FILE__, __LINE__);
				
			}else{
				//We have to Update		
				
				$query_campaign_insert	="UPDATE marketing_campaigns
											SET id_message=".$campaign_message_selection.", 
												type='".$campaign_type."', 
												name='".$campaign_title."', 
												emails_brut=".$data_get_table_info['results_count'].", 
												date_send='".$campaign_date."', 
												hour_send='".$campaign_hour."',
												minute_send='".$campaign_minute."',
												etat='".$campaign_actived_sql."', 
												date_last_edit=NOW(), 
												edited_user=".$_SESSION['marketing_user_id']."
											WHERE 
												id=".$campaign_id;
							
				$res_campaign_insert 	= $db->query($query_campaign_insert, __FILE__, __LINE__);
				
				//Print ID to a hidden Div that be executed by the Javascript !
				echo('<div id="campaign_final_results_container">');
					echo('<div>');
						echo('<font color="green">Campagne enregistr&eacute;e avec succ&egrave;s !</font>');
					echo('</div>');
					
					echo('<div id="campaign_final_results_container_js" style="display:none;">');
						//echo('document.getElementById("campaigns_hidden_id").value="'.$campaign_id.'";');
					echo('</div>');
				echo('</div>');
				
				
				$campaign_log	= "Campaign: Edit campaign ID: ".$campaign_id;
				$query_marketing_log	="INSERT INTO marketing_users_history(id, action,
												id_user, action_date)
											VALUES(NULL, '".$campaign_log."',
												".$_SESSION['marketing_user_id'].", NOW())";
							
				$res_marketing_log 	= $db->query($query_marketing_log, __FILE__, __LINE__);
				
			}//end else update 
		
	}else{
		echo('Vous avez des erreurs dans votre formulaire !');
	}//End Else If !empty($_SESSION['marketing_user_id'] && !empty all the fields)
	
?>