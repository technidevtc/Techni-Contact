<?php 
	require_once('functions.php'); 
	//require_once('check_session.php');

	try{
		if(empty($_SESSION['marketing_user_id'])){
			throw new Exception('<a href="/fr/marketing/login.php">Veuillez vous reconnecter</a>.');
		}
		
		
		$message_id		= mysql_escape_string($_POST['message_id']);
		
		if(empty($message_id)){
			echo('<br /><font color="red">Vous avez des erreurs dans votre formulaire !</font>');
			die;
		}
		
		
		//Delete the message !
		$query_message_delete	= "DELETE FROM marketing_messages WHERE id=".$message_id."";
		$res_message_delete		= $db->query($query_message_delete, __FILE__, __LINE__);
		

			echo('<br /><br />');
			echo('<div id="message_final_results_container">');
				echo('<div style="width: 25px; float:left; margin-top: -2px">');
					echo('<img src="ressources/images/icons/green_ok.png" alt="- "/>');
				echo('</div>');
				echo('<div>');
					echo('<font color="green">Message supprim&eacute; avec succ&egrave;s !</font>');
				echo('</div>');
				
				echo('<div id="message_final_results_container_js" style="display:none;">');
					echo('setTimeout(function(){ messages_load_list_show(); }, 1500);');
				echo('</div>');
			echo('</div>');
			echo('<br />');
				
				
			//Insert into history !
			$query_insert_history	="INSERT INTO  marketing_users_history(id, action, 
																id_user, action_date)
							VALUES(NULL, 'Message: Delete Message ID: ".$message_id."',
							".$_SESSION['marketing_user_id'].", NOW())";
			$db->query($query_insert_history, __FILE__, __LINE__);
			
	}catch(Exception $e){
		echo('Erreur : '.$e);
	}
?>