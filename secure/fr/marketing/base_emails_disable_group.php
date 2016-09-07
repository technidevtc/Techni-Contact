<?php 
	require_once('functions.php'); 
	//require_once('check_session.php');

	if(empty($_SESSION['marketing_user_id'])){
		throw new Exception('<a href="/fr/marketing/login.php">Veuillez vous reconnecter</a>.');
	}
	
	$emails					= mysql_escape_string($_POST['emails']);
	$motif					= mysql_escape_string($_POST['motif']);
	
	//Check the permissions to access to this Page
	//Every page or module have a different ID !	
	require_once('check_session_page_query.php');
	
	//Check if the user have the right to access to this page !
	if(strpos($content_get_user_page_permissions['content'],'#18#')===FALSE){
		echo('<br />Vous n\'avez pas le droit d\'effectuer cette action !<br />');
	}else if(empty($motif) || empty($emails)){
		echo('<br />Vous avez des erreurs dans votre formulaire !<br />');
	}else{
		
		try{
			
		//Get the maximum emails autorized to BlackList !
		$res_get_maximum_blacklist_query	= "SELECT
													maximum_adresse_to_disable_from_interface AS Maximum
												FROM
													marketing_base_email_disable_max 
												WHERE 
													id=1";
		
		$res_get_maximum_blacklist 	= $db->query($res_get_maximum_blacklist_query, __FILE__, __LINE__);
		$content_maximum_blacklist	= $db->fetchAssoc($res_get_maximum_blacklist);
		
		$emails_array				= explode('\n',$emails);
		$emails_count				= count($emails_array);
		
		//Count for the Log
		$emails_count_empty_rows	= 0;
		$emails_count_add			= 0;
		$emails_count_edit			= 0;
		$log_action					= "";
		
		//echo('Count Adress: '.$emails_count.'<br />');
		//echo('Count Autorized: '.$content_maximum_blacklist['Maximum'].'<br />');
			
		if($emails_count<$content_maximum_blacklist['Maximum']){
			
			$log_action	= "Executed";
			
			
			$local_count	= 0;
			while($local_count<$emails_count){
				
				//Check if the Email Exist => Update
				//Else => Add
				
				//Check If the Row is Empty
				if(!empty($emails_array[$local_count])){
					
					$res_get_email_query	= "SELECT
													id, 
													email 
												FROM
													marketing_base_emails 
												WHERE 
													email like '".$emails_array[$local_count]."'";

					$res_get_email 	= $db->query($res_get_email_query, __FILE__, __LINE__);
					$content_email	= $db->fetchAssoc($res_get_email);
					
					if(!empty($content_email['id'])){
						
						//Edit
						$res_edit_email_query	= "UPDATE marketing_base_emails 
														SET etat='ko', 
														disable_source='human', 
														motif=".$motif.", 
														date_last_edit=NOW(), 
														id_user_edit=".$_SESSION['marketing_user_id']." 
													WHERE 
														id=".$content_email['id']."";

						$res_edit_email 	= $db->query($res_edit_email_query, __FILE__, __LINE__);
						
						$emails_count_edit++;
					}else{
						
						//Insert
						$res_add_email_query	= "INSERT INTO marketing_base_emails 
														(id, email, 
														motif, disable_source, 
														etat, date_insert, 
														date_last_edit, id_user_add, 
														id_user_edit) 
														VALUES(NULL, '".$emails_array[$local_count]."', 
														".$motif.", 'human', 
														'ko', NOW(), 
														'0000-00-00 00:00:00', ".$_SESSION['marketing_user_id'].", 
														0)
														";

						$res_add_email 	= $db->query($res_add_email_query, __FILE__, __LINE__);
						
						$emails_count_add++;
					}
					
				}else{
					$emails_count_empty_rows++;
				}

				$local_count++;
			}//End while !
			
			echo('<br /><br />');
			echo('<div id="base_email_final_results_container">');
				echo('<div style="width: 25px; float:left; margin-top: -2px">');
					echo('<img src="ressources/images/icons/green_ok.png" alt="- "/>');
				echo('</div>');
				echo('<div>');
					echo('<font color="green">Action effectu&eacute;e avec succ&egrave;s !</font>');
					echo('<br /><br />');
					echo('Total: <b>'.$emails_count.'</b><br />');
					echo('Emails Ajout&eacute;: <b>'.$emails_count_add.'</b><br />');
					echo('Lignes vides: <b>'.$emails_count_empty_rows.'</b><br />');
				echo('</div>');
				
				echo('<div id="base_email_final_results_container_js" style="display:none;">');
					echo('document.getElementById("f_blacklist_area").value="";');
				echo('</div>');
				
			echo('</div>');
			
		}else{
			$log_action	= "Blocked";
			
			echo('<br />Vous avez d&eacute;passer le nombre d\'adresses autoris&eacute;es &agrave; BlackLister d\'un seul coup ! (Max <b>'.$content_maximum_blacklist['Maximum'].'</b>)');
		}//End else if Test Count Autorized Emails !
		
		
		
		
		//Log the actions (Global Count, Count Add, Count Edit).
		$base_emails_log	= "Base Email: BlackList Grouped \n ";
		$base_emails_log	.= "Action: ".$log_action." \n ";
		$base_emails_log	.= "Count Limit: ".$content_maximum_blacklist['Maximum']." \n ";
		$base_emails_log	.= "Count Total: ".$emails_count." \n ";
		$base_emails_log	.= "Count Empty Rows: ".$emails_count_empty_rows." \n ";
		$base_emails_log	.= "Count Add: ".$emails_count_add." \n ";
		$base_emails_log	.= "Count Edit: ".$emails_count_edit." \n ";
		
		$query_marketing_log	="INSERT INTO marketing_users_history(id, action,
										id_user, action_date)
									VALUES(NULL, '".$base_emails_log."',
										".$_SESSION['marketing_user_id'].", NOW())";
					
		$res_marketing_log 	= $db->query($query_marketing_log, __FILE__, __LINE__);
		
		}catch(Exception $e){
			echo('Erreur : '.$e);
		}
		
	}//End else test access page !
	
?>