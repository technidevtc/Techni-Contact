<?php 
	require_once('functions.php'); 
	//require_once('check_session.php');

	if(empty($_SESSION['marketing_user_id'])){
		throw new Exception('<a href="/fr/marketing/login.php">Veuillez vous reconnecter</a>.');
	}
	

	$start_execution		= date('Y-m-d H:i:s');

	//Check if we can use the platform (if we not sync the Database !) !
	$query_get_sync_flag	="SELECT
								sync_end
							FROM 
								marketing_synchronization_flag  
							WHERE 
								id=1 ";
	$res_get_sync_flag 	= $db->query($query_get_sync_flag, __FILE__, __LINE__);
	$data_get_sync_flag 	= $db->fetchAssoc($res_get_sync_flag);
	
	if(strcmp($data_get_sync_flag['sync_end'],'no')==0){
		//We can't use the platfom
		
		echo('<br /><br /><br />');
		echo('<div id="segmet_final_results_container">');
			echo('<div style="width: 75px; float:left; margin-top: 2px">');
				echo('<img src="ressources/images/icons/stop-icon.png" alt="- "/>');
			echo('</div>');
			echo('<div>');
				echo('<font color="red">');
					echo('La synchronisation de la base de donn&eacute;es est en cours.. ');
					echo('<br />');
					echo('Merci de r&eacute;essayer ult&eacute;rieurement !');
				echo('</font>');
			echo('</div>');
			
			//echo('<div id="segmet_final_results_container_js" style="display:none;">');
				//echo('refresh_page_after_segment_execution();');
			//echo('</div>');
		echo('</div>');
		echo('<br /><br />');
		
	}else if(strcmp($data_get_sync_flag['sync_end'],'yes')==0){
		//We can use the platfom
	
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
							";
							//AND type='statique'
								
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
				
				
				//$data_get_table_id['condition_where'] = str_replace("'","\'",$data_get_table_id['condition_where']);
				
				//We will collect the informations from the previous Query to build the new one !
				$query_personnalized	= "SELECT 
											count(*) c 
											FROM 
												".$data_get_table_id['condition_from']." 
											WHERE 
												".$data_get_table_id['condition_where']." ";

				echo $query_personnalized.'<br /><br /><br />';
				if(!empty($data_get_table_id['condition_group'])){
					$query_personnalized	.= " GROUP BY ".$data_get_table_id['condition_group']." ";
				}
				
				
				//Execution of the Query !
				$res_query_personnalized 	= $db->query($query_personnalized, __FILE__, __LINE__);
				$data_query_personnalized 	= $db->fetchAssoc($res_query_personnalized);
		
				//Update the segment informations !
				$query_update_segment	="UPDATE marketing_segment SET 
											results_count=".$data_query_personnalized['c'].",
											date_last_execution_start='".$start_execution."',
											date_last_execution_end=NOW()
										WHERE 
											id=".$segment_id." 
										";
										
				$res_update_segment 	= $db->query($query_update_segment, __FILE__, __LINE__);

				echo('<br /><br /><br />');
				echo('<div id="segmet_final_results_container">');
					echo('<div style="width: 25px; float:left; margin-top: -2px">');
						echo('<img src="ressources/images/icons/green_ok.png" alt="- "/>');
					echo('</div>');
					echo('<div>');
						echo('<font color="green">Segment execut&eacute; avec succ&egrave;s !</font>');
					echo('</div>');
					
					echo('<div id="segmet_final_results_container_js" style="display:none;">');
						echo('refresh_page_after_segment_execution();');
					echo('</div>');
				echo('</div>');
				echo('<br /><br />');
				
				
				//Insert into history !
				$query_insert_history	="INSERT INTO  marketing_users_history(id, action, 
																	id_user, action_date)
								VALUES(NULL, 'Refresh Segment ID: ".$segment_id."',
								".$_SESSION['marketing_user_id'].", NOW())";
				$db->query($query_insert_history, __FILE__, __LINE__);
				
			}catch(Exception $e){
				echo('Erreur : '.$e);
			}
		
		}else{
			echo('<a href="/fr/marketing/login.php">Veuillez vous reconnecter</a>.');
		}//ENd the first IF the user has the right for this page and he's connected !
	
	}//End check use of the platform !
?>