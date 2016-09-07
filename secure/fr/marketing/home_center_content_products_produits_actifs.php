<?php
	require_once('functions.php'); 

	if(!empty($_SESSION['marketing_user_id'])){

		$res_get_products_actif_count_query	= "SELECT
												count(*) AS c
											FROM
												products_fr pfr
											WHERE
												pfr.active=1
											AND
												pfr.deleted=0";
		$res_get_products_actif_count = $db->query($res_get_products_actif_count_query, __FILE__, __LINE__);
												
		$content_get_products_actif_count = $db->fetchAssoc($res_get_products_actif_count);
		
		echo(separate_every_three_chars_right_to_left($content_get_products_actif_count['c']));
		
	}
?>