<?php
	require_once('functions.php'); 

	if(!empty($_SESSION['marketing_user_id'])){

		$res_get_references_actives_count_query	= "SELECT
														count(*) AS c
													FROM
														references_content AS ref_c
													WHERE
														ref_c.classement!=0
													AND
														ref_c.deleted!=1";
		$res_get_references_actives_count = $db->query($res_get_references_actives_count_query, __FILE__, __LINE__);
												
		$content_get_references_actives_count = $db->fetchAssoc($res_get_references_actives_count);
		
		echo(separate_every_three_chars_right_to_left($content_get_references_actives_count['c']));
		
	}
?>