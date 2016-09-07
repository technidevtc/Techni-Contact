<?php 
	require_once('functions.php'); 
	//require_once('check_session.php');

	if(empty($_SESSION['marketing_user_id'])){
		throw new Exception('<a href="/fr/marketing/login.php">Veuillez vous reconnecter</a>.');
	}
	
	$email_id				= mysql_escape_string($_POST['email_id']);

	
	//Check the permissions to access to this Page
	//Every page or module have a different ID !	
	require_once('check_session_page_query.php');
	
	//Check if the user have the right to access to this page !
	if(strpos($content_get_user_page_permissions['content'],'#18#')===FALSE){
		echo('<br />Vous n\'avez pas le droit d\'effectuer cette action !<br />');
	}else if(empty($email_id)){
		echo('<br />Vous avez des erreurs dans votre formulaire !<br />');
	}else{
		
		$res_edit_email_query	= "UPDATE marketing_base_emails 
										SET etat='ok', 
										disable_source='human', 
										date_last_edit=NOW(), 
										id_user_edit=".$_SESSION['marketing_user_id']." 
									WHERE 
										id=".$email_id."";

		$res_edit_email 	= $db->query($res_edit_email_query, __FILE__, __LINE__);
		
		
		echo('<br /><br />');
		echo('<div id="base_emails_final_results_container">');
			echo('<div style="width: 25px; float:left; margin-top: -2px">');
				echo('<img src="ressources/images/icons/green_ok.png" alt="- "/>');
			echo('</div>');
			echo('<div>');
				echo('<font color="green">Adresse activ&eacute;e avec succ&egrave;s !</font>');
			echo('</div>');
			
		echo('</div>');
		
		//Log the actions (Global Count, Count Add, Count Edit).
		$base_emails_log	= "Base Email: Autorize One \n ";
		$base_emails_log	.= "Email ID: ".$email_id." \n ";
		
		$query_marketing_log	="INSERT INTO marketing_users_history(id, action,
										id_user, action_date)
									VALUES(NULL, '".$base_emails_log."',
										".$_SESSION['marketing_user_id'].", NOW())";
					
		$res_marketing_log 	= $db->query($query_marketing_log, __FILE__, __LINE__);
		
	}
?>