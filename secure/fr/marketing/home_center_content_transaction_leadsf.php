<?php
	require_once('functions.php'); 

	if(!empty($_SESSION['marketing_user_id'])){

		$res_get_lead_f_count_query	= "SELECT
											count(cont.id) AS c
										FROM
											contacts cont
												INNER JOIN advertisers AS adv ON cont.idadvertiser=adv.id AND adv.category=1 AND cont.parent=0";
											
		$res_get_lead_f_count = $db->query($res_get_lead_f_count_query, __FILE__, __LINE__);
												
		$content_get_lead_f_count = $db->fetchAssoc($res_get_lead_f_count);
		
		echo(separate_every_three_chars_right_to_left($content_get_lead_f_count['c']));

		
	}
?>