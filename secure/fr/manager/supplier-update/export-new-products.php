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


		$id_operation	= mysql_real_escape_string($_GET['id']);

		if(!empty($id_operation)){
			/*
			$res_get_new_products = $db->query("SELECT	
													sup_up_id.id, sup_up_id.file_reference,
													sup_up_id.file_price
												FROM
													supplier_update_info_details sup_up_id
												WHERE
													sup_up_id.id_operation=".$id_operation."
												AND
													sup_up_id.id NOT IN (SELECT
																			id_file
																		FROM
																			supplier_update_info_correspondance sup_uic
																		WHERE
																			sup_uic.id_operation=".$id_operation.")
													", __FILE__, __LINE__);
			*/	
			
			$res_get_new_products = $db->query("SELECT	
													sup_up_id.id, sup_up_id.file_reference,
													sup_up_id.file_price
												FROM
													supplier_update_info_details sup_up_id
												WHERE
													sup_up_id.id_operation=".$id_operation."
												AND
													NOT EXISTS (SELECT
																			id_file
																		FROM
																			supplier_update_info_correspondance sup_uic
																		WHERE
																			sup_uic.id_operation=".$id_operation."
																		AND
																			sup_up_id.id=sup_uic.id_file)
													", __FILE__, __LINE__);
													
			if(mysql_num_rows($res_get_new_products)>0){
			
			
				$export_data	= "Reference;Prix\n";
				

				while($content_get_new_products = $db->fetchAssoc($res_get_new_products)){

					// The actual data
					$content_get_new_products['file_reference']	= str_replace(';','',$content_get_new_products['file_reference']);
					$content_get_new_products['file_price']		= str_replace(';','',$content_get_new_products['file_price']);
					
					$export_data	.= "".$content_get_new_products['file_reference'].";".$content_get_new_products['file_price']."\n";

				}//end while

				
				$name_file	= 'new-products-'.date('d-m-Y_H-i-s');
				header("Content-type: application/vnd.ms-excel");
				header("Content-disposition: attachment; filename=".$name_file.".csv");
				echo $export_data;
				exit;
			}//end if num_rows

		}else{
			echo('<div class="bg" style="position: relative">');
				echo('<h2>Merci de v&eacute;rifier votre formulaire !</h2>');
			echo('</div>');
		}
	}//end else if check user
?>