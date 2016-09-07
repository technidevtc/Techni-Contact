<?php
	require_once('extranet_v3_functions.php'); 
	

	if(!empty($_SESSION['extranet_user_id'])){
	
		$product				= mysql_escape_string($_POST['product']);
	
		//$product_prepare_sql	= str_replace(' ','* +*',$product);
		$product_prepare_sql	= str_replace(' ','*" OR "*',$product);
		$product_sql			= '*'.$product_prepare_sql.'*';
		$product_sql			= addslashes($product_sql);
		
		$array_chars_to_avoid	= array("<", "<b", "<b>", "b", "b>", "</", "</b", "</b>", "/b", "/b>", ">");
		
		if(!empty($product)){

		/*
		$res_get_products = $db->query("select
							pfr.name, (select count(idFamily) from products_families where idProduct = pfr.id) as count_families, pfr.id, pfr.fastdesc, pfr.ref_name,
											(select idFamily from products_families where idProduct = pfr.id and orderFamily <= 1 LIMIT 0,1) as cat_id,
							MATCH (pfr.name) AGAINST ('".$product_sql."' IN BOOLEAN MODE) as name_score,
							MATCH (pfr.fastdesc) AGAINST ('".$product_sql."' IN BOOLEAN MODE) as fastdesc_score
						from
							products_fr pfr, advertisers a
						where
								pfr.idAdvertiser = a.id 
							AND
								a.actif = 1 
							AND
								pfr.active = 1
							AND
								pfr.deleted != 1
							AND
								a.id=".$_SESSION['extranet_user_id']."
							AND
								(
									MATCH (pfr.name) AGAINST ('".$product_sql."' IN BOOLEAN MODE)
									OR
									MATCH (pfr.fastdesc) AGAINST ('".$product_sql."' IN BOOLEAN MODE)
								)
						group by
							pfr.ref_name
						order by
							((name_score*0.8)+(fastdesc_score*0.5)) DESC
						LIMIT 0, 5", __FILE__, __LINE__);
		
		*/
			$res_get_products = $db->query("select
							pfr.name, (select count(idFamily) from products_families where idProduct = pfr.id) as count_families, pfr.id, pfr.fastdesc, pfr.ref_name,
											(select idFamily from products_families where idProduct = pfr.id and orderFamily <= 1 LIMIT 0,1) as cat_id,
							MATCH (pfr.name) AGAINST (\"".$product_sql."\" IN BOOLEAN MODE) as name_score,
							MATCH (pfr.fastdesc) AGAINST (\"".$product_sql."\" IN BOOLEAN MODE) as fastdesc_score
						from
							products_fr pfr, advertisers a
						where
								pfr.idAdvertiser = a.id 
							AND
								a.actif = 1 
							AND
								pfr.active = 1
							AND
								pfr.deleted != 1
							AND
								a.id=".$_SESSION['extranet_user_id']."
							AND
								(
									MATCH (pfr.name) AGAINST (\"".$product_sql."\" IN BOOLEAN MODE)
									OR
									MATCH (pfr.fastdesc) AGAINST (\"".$product_sql."\" IN BOOLEAN MODE)
								)
						group by
							pfr.ref_name
						order by
							((name_score*0.8)+(fastdesc_score*0.5)) DESC
						LIMIT 0, 5", __FILE__, __LINE__);
			
							
			while($res_content_products = $db->fetchAssoc($res_get_products)){
				echo('<div class="row" onclick="javascript:open_link_blank(\'stats_external_formid\', \'extranet-v3-stats-products-detail.html?id='.$res_content_products['id'].'\', \'_self\')">');
				//echo('name_score: '.$res_content_products['name_score'].'<br />');
				//echo('fast_desc: '.$res_content_products['fastdesc_score'].'<br />');
				//echo('score combine: '.(($res_content_products['name_score']*0.8)+($res_content_products['fastdesc_score']*0.5)).'<br />');
				
					echo('<div class="products_autocomp_left">');
							echo('<img src="'.PRODUCTS_IMAGE_URL.'thumb_small/'.$res_content_products['id'].'-1.jpg" width="80" height="80" />');
					echo('</div>');
					echo('<div class="products_autocomp_right">');
						echo('<div class="products_autocomp_title">');
						
							$res_content_products_name_show		= $res_content_products['name'];
							
							//To eliminate the multiple blank space
							$product_new						= preg_replace('/\s+/', ' ',$product);
							
							$res_content_products_name_array	= explode(' ',$product_new);
							$local_count = 0;
							while(!empty($res_content_products_name_array[$local_count])){
								if(!in_array($res_content_products_name_array[$local_count], $array_chars_to_avoid)){
									$res_content_products_name_show = str_ireplace($res_content_products_name_array[$local_count],'<b>'.$res_content_products_name_array[$local_count].'</b>',$res_content_products_name_show);
								}
								$local_count++;
							}
							
							echo ucfirst($res_content_products_name_show);
							
						echo('</div>');
						echo('<div class="products_autocomp_desc">');
							echo $res_content_products['fastdesc'];
						echo('</div>');
					echo('</div>');
				echo('</div>');
			}//end while
			
			if(is_numeric($product)){
			
				$res_get_products = $db->query("SELECT
													pfr.name, (select count(idFamily) from products_families where idProduct = pfr.id) as count_families, pfr.id, pfr.fastdesc, pfr.ref_name,
													(select idFamily from products_families where idProduct = pfr.id and orderFamily <= 1 LIMIT 0,1) as cat_id
												FROM
													products_fr pfr, advertisers a
												WHERE
													pfr.idAdvertiser = a.id 
												AND
													a.actif = 1 
												AND
													pfr.active = 1
												AND
													pfr.deleted != 1
												AND
													a.id=".$_SESSION['extranet_user_id']."	
												AND
													MATCH (pfr.id) AGAINST ('>".$product."*' IN BOOLEAN MODE)
												group by
													pfr.ref_name
													
												LIMIT 0, 5");
				
				while($res_content_products = $db->fetchAssoc($res_get_products)){
					echo('<div class="row">');
						echo('<a href="javascript:void(0)" onclick="javascript:stats_filter_by_product(\''.$res_content_products['id'].'\')">');
						
							echo('<div class="products_autocomp_left">');
									echo('<img src="'.PRODUCTS_IMAGE_URL.'thumb_small/'.$res_content_products['id'].'-1.jpg" width="80" height="80" />');
							echo('</div>');
							echo('<div class="products_autocomp_right">');
								echo('<div class="products_autocomp_title">');
									echo $res_content_products['name'];
								echo('</div>');
								echo('<div class="products_autocomp_desc">');
									echo $res_content_products['fastdesc'];
								echo('</div>');
							echo('</div>');
							
						echo('</a>');
					echo('</div>');
				}//end while
			}//end if(is_numeric($product))
			
		}else{
			//Erreur, Merci de recharger la page
			echo('0');
		}//end else if !empty
		
	}else{
		//Forward reconnect !
		echo('-1');
	}//end if empty session
	
?>