<?php 
	require_once('functions.php'); 
	//require_once('check_session.php');

	if(empty($_SESSION['marketing_user_id'])){
		throw new Exception('<a href="/fr/marketing/login.php">Veuillez vous reconnecter</a>.');
	}
	
	$segment_id		= mysql_escape_string($_POST['segment_id']);
	
	//We will look for the Table ID to check if the user has the right to refresh it's segment !
	$query_get_table_id	="SELECT
							id_table,
							condition_select,
							condition_from,
							condition_where,
							condition_group
						FROM 
							marketing_segment 
						WHERE 
							id=".$segment_id." 
						AND
							type='statique'";
							
	$res_get_table_id 	= $db->query($query_get_table_id, __FILE__, __LINE__);
	$data_get_table_id 	= $db->fetchAssoc($res_get_table_id);
	
	
	//Check the permissions to access to this Page
	//Every page or module have a different ID !	
	require_once('check_session_page_query.php');
	$page_permission_id = "7";
	$page_id	= '#'.$page_permission_id.'#';
	
	//Check the permissions to access to this Table
	require_once('check_session_table_query.php');
	$table_id	= '#'.$data_get_table_id['id_table'].'#';
	
	//Check if the user have the right to access to this Page or Table !
	if(strpos($content_get_user_page_permissions['content'],$page_id)===FALSE){
		echo('<a href="/fr/marketing/">Vous n\'avez pas le droit d\'acc&eacute;der &agrave; cette page !</a>');
	}else if(strpos($content_get_user_tables_access_permissions['content'],$table_id)===FALSE){
		echo('<a href="/fr/marketing/">Vous n\'avez pas le droit d\'acc&eacute;der &agrave; cette table !</a>');
	}else if(!empty($_SESSION['marketing_user_id'])){
		
		try{
			
			//Looking for the groups 
			//Delete all the fields of every group !
			//And Finally delete the segment !
			$query_get_groups_to_delete	= "SELECT 
												id
											FROM	
												marketing_groupes 
											WHERE 
												id_segment=".$segment_id."";
													
			$res_get_groups_to_delete 			= $db->query($query_get_groups_to_delete, __FILE__, __LINE__);
			
			//Fetch all the groups and delete all the fields !
			while($data_get_groups_to_delete 	= $db->fetchAssoc($res_get_groups_to_delete)){
				
				$query_segment_fields_delete	= "DELETE FROM marketing_groupes_fields 
													WHERE 
														id_groupe=".$data_get_groups_to_delete['id']."";
				$res_segment_fields_delete		= $db->query($query_segment_fields_delete, __FILE__, __LINE__);
				
			}//End while fetching groups to delete it's Fields !
			
			//Delete the groups !
			$query_segment_groups_delete	= "DELETE FROM marketing_groupes WHERE id_segment=".$segment_id."";
			$res_segment_groups_delete		= $db->query($query_segment_groups_delete, __FILE__, __LINE__);
			
			
			//Delete the segment !
			$query_segment_delete	= "DELETE FROM marketing_segment WHERE id=".$segment_id."";
			$res_segment_delete		= $db->query($query_segment_delete, __FILE__, __LINE__);
			
			
			
			//Everyting is OK !
			echo('<br /><br /><br />');
			echo('<div id="segmet_final_results_container">');
				echo('<div style="width: 25px; float:left; margin-top: -2px">');
					echo('<img src="ressources/images/icons/green_ok.png" alt="- "/>');
				echo('</div>');
				echo('<div>');
					echo('<font color="green">Segment supprim&eacute; avec succ&egrave;s !</font>');
				echo('</div>');
				
				echo('<div id="segmet_final_results_container_js" style="display:none;">');
					echo('redirect_page_after_segment_delete();');
				echo('</div>');
			echo('</div>');
			echo('<br /><br />');
			
			
			//Insert into history !
			$query_insert_history	="INSERT INTO  marketing_users_history(id, action, 
																id_user, action_date)
							VALUES(NULL, 'Delete Segment ID: ".$segment_id."',
							".$_SESSION['marketing_user_id'].", NOW())";
			$db->query($query_insert_history, __FILE__, __LINE__);
			
			
			
		}catch(Exception $e){
			echo('Erreur : '.$e);
		}
		
	}else{
		echo('<a href="/fr/marketing/login.php">Veuillez vous reconnecter</a>.');
	}//ENd the first IF the user has the right for this page and he's connected !
?>