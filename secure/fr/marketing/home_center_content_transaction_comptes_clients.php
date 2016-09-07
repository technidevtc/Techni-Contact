<?php
	require_once('functions.php'); 

	if(!empty($_SESSION['marketing_user_id'])){


		
		$res_get_custumers_count_query	= "SELECT
												count(*) AS c
											FROM
												clients c
											WHERE
												c.actif=1";
		$res_get_custumers_count = $db->query($res_get_custumers_count_query, __FILE__, __LINE__);
												
		$content_get_custumers_count = $db->fetchAssoc($res_get_custumers_count);
		
		echo(separate_every_three_chars_right_to_left($content_get_custumers_count['c']));

		
	}
?>