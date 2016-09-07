<?php
	require_once('functions.php'); 

	if(!empty($_SESSION['marketing_user_id'])){

		$res_get_annonceurs_actifs_count_query	= "SELECT
														count(*) AS c
													FROM
														advertisers adv
													WHERE
														adv.category=0
													AND
														adv.actif=1
													AND
														adv.deleted=0";
		$res_get_annonceurs_actifs_count = $db->query($res_get_annonceurs_actifs_count_query, __FILE__, __LINE__);
												
		$content_get_annonceurs_actifs_count = $db->fetchAssoc($res_get_annonceurs_actifs_count);
		
		echo(separate_every_three_chars_right_to_left($content_get_annonceurs_actifs_count['c']));
		
	}
?>