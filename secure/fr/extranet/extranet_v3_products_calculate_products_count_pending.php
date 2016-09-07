<?php	
	require_once('extranet_v3_functions.php'); 

	
	if(!empty($_SESSION['extranet_user_id'])){
	
		//Counting the PRoducts pending "Creation", "Modification" and "Deleting"
		$products_pending_count	= 0;
		
		$res_get_products_p1_count_query	= "SELECT 

												count(DISTINCT(p_add_adv.id)) c
												
											FROM
												products_add_adv p_add_adv
											WHERE
													p_add_adv.idAdvertiser=".$_SESSION['extranet_user_id']."
												AND
													(
															p_add_adv.type='c'
														OR
															p_add_adv.type='m'
													)
												AND
													p_add_adv.name not like '##########%'	
												";
									
		$res_get_products_p1_count = $db->query($res_get_products_p1_count_query, __FILE__, __LINE__);
		
		$content_get_products_p1_count	= $db->fetchAssoc($res_get_products_p1_count);
		
		$products_pending_count	= $content_get_products_p1_count['c'];
		
		
		//Start pending delete
		
		$res_get_products_p2_count_query	= "SELECT 
													count(DISTINCT(p_fr.id)) c
												FROM
													products_fr p_fr 
														LEFT JOIN advertisers AS a ON a.id=p_fr.idAdvertiser 
														LEFT JOIN products_families AS pr_fam ON p_fr.id=pr_fam.idProduct
														LEFT JOIN families_fr AS ffr ON pr_fam.idFamily=ffr.id
														INNER JOIN sup_requests sup_req	ON p_fr.id=sup_req.idProduct
												WHERE
														p_fr.idAdvertiser=".$_SESSION['extranet_user_id']."
													AND
														p_fr.active='1'
													AND	
														p_fr.deleted='0'
													AND
														pr_fam.orderFamily<= 1";
									
		$res_get_products_p2_count = $db->query($res_get_products_p2_count_query, __FILE__, __LINE__);
		
		$content_get_products_p2_count	= $db->fetchAssoc($res_get_products_p2_count);
		
		$products_pending_count	+= $content_get_products_p2_count['c'];
		
		
		if($products_pending_count>0){
			echo($products_pending_count);
		}
	
	}
?>