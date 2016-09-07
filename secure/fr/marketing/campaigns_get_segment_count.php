<?php

	require_once('functions.php'); 

	$message_id			= mysql_escape_string($_POST['message_id']);
		
	if(!empty($_SESSION['marketing_user_id'])){

		if(!empty($message_id)){ 
	
			//Execution of the Query
				$res_get_segments_query	= "SELECT 
												m_segment.results_count 
											FROM 
												marketing_segment AS m_segment
												INNER JOIN marketing_messages AS m_messages ON m_segment.id=m_messages.id_segment
											WHERE 
												m_messages.id=".$message_id."
												 ";

				$res_get_segments = $db->query($res_get_segments_query, __FILE__, __LINE__);
			
				if(mysql_num_rows($res_get_segments)>0){
					$content_get_segments	= $db->fetchAssoc($res_get_segments);
				
					echo($content_get_segments['results_count']);
				}else{
					echo('Information in&eacute;xistante !');
				}
		}else{
			echo('Erreur envoi informations !');
		}
	}else{
		echo('Vous devez vous reconnecter !');
	}//end if session

?>