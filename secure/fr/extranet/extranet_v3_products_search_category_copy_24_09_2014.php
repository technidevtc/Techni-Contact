<?php
	require_once('extranet_v3_functions.php'); 
	

	if(!empty($_SESSION['extranet_user_id'])){
	
		$category				= mysql_escape_string($_POST['category']);
		
		if(!empty($category)){
		
			$category_prepare_sql	= str_replace(' ','*" OR "*',$category);
			$category_sql			= '*'.$category_prepare_sql.'*';
			$category_sql			= addslashes($category_sql);
			
			$autocomplete_category = array("");



			$res_get_categorys = $db->query("select
							f.id, f.idParent, fr.name,
							MATCH (fr.name) AGAINST (\"".$category_sql."\" IN BOOLEAN MODE) as name_score
						from
							families f, 
							families_fr fr
						where
							f.id = fr.id
						AND
							idParent!=0
						AND
							f.id NOT IN (
									SELECT
										f.idParent
									FROM
										families f
									)
							AND
								(
									MATCH (fr.name) AGAINST (\"".$category_sql."\" IN BOOLEAN MODE)
								)
						group by
							f.idParent
						order by
							(name_score) DESC
						", __FILE__, __LINE__);
			
							
			while($res_content_categorys = $db->fetchAssoc($res_get_categorys)){
			
				//Looking for the Nd Level !
					$res_get_categorys_nd_level = $db->query("select
								f.id, f.idParent, fr.name
							from
								families f, 
								families_fr fr
							where
								f.id = ".$res_content_categorys['idParent']."
							AND
								fr.id=f.id	
							LIMIT 0, 1", __FILE__, __LINE__);
					$res_categorys_nd_level = $db->fetchAssoc($res_get_categorys_nd_level);		
				
				//Looking for the St Level
					$res_get_categorys_st_level = $db->query("select
								f.id, f.idParent, fr.name
							from
								families f, 
								families_fr fr
							where
								f.id = ".$res_categorys_nd_level['idParent']."
							AND
								fr.id=f.id
							LIMIT 0, 1", __FILE__, __LINE__);
					$res_categorys_st_level = $db->fetchAssoc($res_get_categorys_st_level);		

				
				//echo('id:'.$res_content_categorys['id'].' * parentid:'.$res_content_categorys['idParent'].' * '.$res_content_categorys['name'].' ## 2ndid:'.$res_categorys_nd_level['id'].' * 2ndparentid:'.$res_categorys_nd_level['idParent'].' * '.$res_categorys_nd_level['name'].' ### 1stid:'.$res_categorys_st_level['id'].' * '.$res_categorys_st_level['name']);
				//echo('<br /><br />');
				
				$autocomplete_category_temp = array($res_categorys_st_level['name'], $res_categorys_nd_level['name'], $res_content_categorys['name'], $res_content_categorys['id']);
				
				array_push($autocomplete_category, $autocomplete_category_temp);
				
			}//end while
			
			//Delete the first row 
			unset($autocomplete_category[0]);	
			
			asort($autocomplete_category);
			$autocomplete_category = array_values($autocomplete_category);
			//print_r($autocomplete_category);
			
			$category_local_loop	= 0;
			$actual_st_level		= '';
			$actual_nd_level		= '';
			
			$first_level_flushed	= false;
			//$first_level_flushed	= false;
			
			//echo($autocomplete_category[12][0].' * '.$autocomplete_category[12][1].' * '.$autocomplete_category[12][2]);
			
			//Now Building the results
			
			if(!empty($autocomplete_category[$category_local_loop][1])){
				echo('<div class="products_category_autocomplete_top_close">');
					echo('<img src="ressourcesv3/icons/cross.png" onclick="javascript:product_category_autocomplete_hide()" alt="Fermer" title="Fermer" />');
				echo('</div>');
				while($autocomplete_category[$category_local_loop][1]){
					if(!empty($autocomplete_category[$category_local_loop][0])){
					
						//Start First Level
							if(empty($actual_st_level)){
								echo('<ul class="product_autocomp_first_level">');
									echo('<li class="accordionButton">'.$autocomplete_category[$category_local_loop][0].'</li>');
							}
							
								
							if($actual_st_level!=$autocomplete_category[$category_local_loop][0] && $category_local_loop>0){
								//Close the list of the Third LEVEL
								//Close the list of the Second LEVEL
								
									echo('</ul>');
								echo('</ul>');
								
								echo('<li class="accordionButton">'.$autocomplete_category[$category_local_loop][0].'</li>');
								
								$first_level_flushed = true;
							}
						
						
						//Start Second Level
							if(empty($actual_nd_level)){
								echo('<ul class="product_autocomp_second_level">');
									echo('<li>'.$autocomplete_category[$category_local_loop][1].'</li>');
									
										echo('<ul class="product_autocomp_third_level">');
							}
							
							if($actual_st_level!=$autocomplete_category[$category_local_loop][1] && $category_local_loop>0){
								//Close the list of the Third LEVEL
							
								//Test if the first level closed already the Third one
								if(!$first_level_flushed){
									echo('</ul>');
								}else{
									echo('<ul class="product_autocomp_second_level">');
								}
								
								echo('<li>'.$autocomplete_category[$category_local_loop][1].'</li>');
								
								echo('<ul class="product_autocomp_third_level">');
							}
						
							echo('<li onclick="javascript:product_get_this_category(\''.$autocomplete_category[$category_local_loop][3].'\',\''.addslashes($autocomplete_category[$category_local_loop][2]).'\')">'.$autocomplete_category[$category_local_loop][2].'</li>');
						
						
					
						$actual_st_level	= $autocomplete_category[$category_local_loop][0];
						$actual_nd_level	= $autocomplete_category[$category_local_loop][1];
						$first_level_flushed = false;
						
					}//end first if FIRST LEVEL
					$category_local_loop++;
				}//end while
				
				//Close the list of the Third LEVEL
				//Close the list of the Second LEVEL
				//Close the list of the First LEVEL
						echo('</ul>');
					echo('</ul>');
				echo('</ul>');
			
			}//end empty array
			
		}else{
			//Erreur, Merci de recharger la page
			echo('0');
		}//end else if !empty
		
	}else{
		//Forward reconnect !
		echo('-1');
	}//end if empty session
	
?>