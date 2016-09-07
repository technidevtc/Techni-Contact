<?php	
	require_once('extranet_v3_functions.php'); 

	
	if(!empty($_SESSION['extranet_user_id'])){
	
		$res_get_products_count_query	= "SELECT 

												count(p_exh.id_demande) c
												
											FROM
												products_extranet_history p_exh
											WHERE
													p_exh.p__idAdvertiser=".$_SESSION['extranet_user_id']."
												AND
													(
															user_operation='Brouillon modification'
														OR
															user_operation='Brouillon ajout'
													)";
									
		$res_get_products_count = $db->query($res_get_products_count_query, __FILE__, __LINE__);
		
		$content_get_products_count	= $db->fetchAssoc($res_get_products_count);
		
		if($content_get_products_count['c']>0){
			echo($content_get_products_count['c']);
		}
	
	}
?>