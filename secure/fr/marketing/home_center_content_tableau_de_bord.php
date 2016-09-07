<?php
	require_once('functions.php'); 

	if(!empty($_SESSION['marketing_user_id'])){

		
		$res_get_last_sync_query	= "SELECT
											MAX(date_last_synchronisation_start) AS dlast_sync
										FROM
											marketing_last_db_synchronisation
										";
		$res_get_last_sync = $db->query($res_get_last_sync_query, __FILE__, __LINE__);
												
		$content_get_last_sync = $db->fetchAssoc($res_get_last_sync);
			
		echo('<i class="fa fa-users"></i> Tableau de bord Au '.date('d/m/Y - H:i:s',strtotime($content_get_last_sync['dlast_sync'])));
		
	
	}
?>