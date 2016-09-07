<?php

	if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
		require_once '../../../../config.php';
	}else{
		require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
	}
	
	$db = DBHandle::get_instance();
	$user = new BOUser();
	
	if (!$user->login()){
		echo('<div class="bg" style="position: relative">');
			echo('<h2>Votre session a expiré, veuillez vous identifier à nouveau après avoir rafraîchi votre page.</h2>');
		echo('</div>');
	}else{
	
		$mmf_id_operation	= mysql_real_escape_string($_POST['id_operation']);
		
		if(!empty($mmf_id_operation)){
			/*
			$res_cancel_operation = $db->query("UPDATE references_content ref_c
													INNER JOIN supplier_update_info_details sup_up_id
													ON ref_c.id=sup_up_id.id_tc
												SET
													ref_c.price=sup_up_id.manager_price,
													ref_c.price2=sup_up_id.manager_price2,
													ref_c.marge= sup_up_id.manager_marge
												WHERE
													sup_up_id.id_operation=".$mmf_id_operation."", __FILE__, __LINE__);
													
			*/		
			$res_cancel_operation = $db->query("UPDATE references_content ref_c
													INNER JOIN supplier_update_info_correspondance sup_uic ON ref_c.id=sup_uic.id_tc
													INNER JOIN supplier_update_info_details sup_up_id ON sup_uic.id_file=sup_up_id.id
												SET
													ref_c.price=sup_up_id.manager_price,
													ref_c.price2=sup_up_id.manager_price2,
													ref_c.marge= sup_up_id.manager_marge
												WHERE
													sup_up_id.id_operation=".$mmf_id_operation."", __FILE__, __LINE__);			
													
			$res_cancel_operation_change_status = $db->query("UPDATE supplier_update_info sup_upi
																SET
																	sup_upi.etat='annule',
																	id_operator_cancel=".$user->id.",
																	date_cancel=NOW()
																WHERE
																	sup_upi.id=".$mmf_id_operation."", __FILE__, __LINE__);										
																

			echo('<div class="bg" style="position: relative">');
				echo('Op&eacute;ration annul&eacute;e avec succ&egrave;s ! <strong><a href="/fr/manager/supplier-update/index.php">Retour</a></strong>');
			echo('</div>');
			
		}else{
			echo('<div class="bg" style="position: relative">');
				echo('<h2>Vous avez une erreur dans votre formulaire !</h2>');
			echo('</div>');
		}
		
	}

?>