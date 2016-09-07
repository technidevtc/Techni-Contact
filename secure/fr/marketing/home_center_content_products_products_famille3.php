<?php
	require_once('functions.php'); 

	if(!empty($_SESSION['marketing_user_id'])){

		$res_get_families3_count_query	= "SELECT 
												COUNT(fr.id) AS c

											FROM 
												families f, 
												families_fr fr 

											WHERE 
												f.id = fr.id
											AND
												idParent!=0
											AND
												f.id NOT IN (
														SELECT
															f.idParent
														FROM
															families f
														)";
		$res_get_families3_count = $db->query($res_get_families3_count_query, __FILE__, __LINE__);
												
		$content_get_families3_count = $db->fetchAssoc($res_get_families3_count);
		
		echo(separate_every_three_chars_right_to_left($content_get_families3_count['c']));
		
	}
?>