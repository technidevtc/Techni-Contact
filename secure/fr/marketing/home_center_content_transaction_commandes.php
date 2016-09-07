<?php
	require_once('functions.php'); 

	if(!empty($_SESSION['marketing_user_id'])){

		$res_get_validated_orders_query	= "SELECT
												count(*) AS c
											FROM
												`order` o
											WHERE
												o.processing_status!=0
											AND
												o.processing_status!=10
											AND
												o.processing_status!=90
											AND
												o.processing_status!=99";
											
		$res_get_validated_orders = $db->query($res_get_validated_orders_query, __FILE__, __LINE__);
												
		$content_get_validated_orders = $db->fetchAssoc($res_get_validated_orders);
		
		echo(separate_every_three_chars_right_to_left($content_get_validated_orders['c']));
		
	}
?>