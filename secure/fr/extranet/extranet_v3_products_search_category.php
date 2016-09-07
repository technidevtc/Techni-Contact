<?php
	require_once('extranet_v3_functions.php'); 
	
	function zikosoft2_replace_accents($string){
		return str_replace( array('à','á','â','ã','ä', 'ç', 'è','é','ê','ë', 'ì','í','î','ï', 'ñ', 'ò','ó','ô','õ','ö', 'ù','ú','û','ü', 'ý','ÿ', 'À','Á','Â','Ã','Ä', 'Ç', 'È','É','Ê','Ë', 'Ì','Í','Î','Ï', 'Ñ', 'Ò','Ó','Ô','Õ','Ö', 'Ù','Ú','Û','Ü', 'Ý'), array('a','a','a','a','a', 'c', 'e','e','e','e', 'i','i','i','i', 'n', 'o','o','o','o','o', 'u','u','u','u', 'y','y', 'A','A','A','A','A', 'C', 'E','E','E','E', 'I','I','I','I', 'N', 'O','O','O','O','O', 'U','U','U','U', 'Y'), $string);
	}

	if(!empty($_SESSION['extranet_user_id'])){
	
		$category				= mysql_escape_string($_POST['category']);
		//$category				= zikosoft2_replace_accents($category);
		
		$array_chars_to_avoid	= array("<", "<b", "<b>", "b", "b>", "</", "</b", "</b>", "/b", "/b>", ">");
		
		if(!empty($category)){
		
		
			//$category_prepare_sql	= str_replace(' ','*" OR "*',$category);
			//$category_sql			= '*'.$category_prepare_sql.'*';
			//$category_sql			= addslashes($category_sql);
			
			
			$category_prepare_sql	= str_replace(' ','* ',$category);
			$category_sql			= '*'.$category_prepare_sql.'*';
			//$category_sql			= str_replace("'","\'",$category_sql);

			//$category_exact_sql		= str_replace("'","\'",$category);
			
			$category_exact_sql		= $category;
			
			$autocomplete_category = array("");

			
			$res_get_categorys_query	= "select
											f.id, f.idParent, fr.name,
											
											MATCH (fr.name) AGAINST ('".$category_sql."' IN BOOLEAN MODE) as name_score,
											MATCH (fr.name) AGAINST ('".$category_exact_sql."') as name_score_exact
											
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
														MATCH (fr.name) AGAINST ('".$category_sql."'  IN BOOLEAN MODE)
													OR
														MATCH (fr.name) AGAINST ('".$category_exact_sql."')
												)

											
										order by
											((name_score_exact*0.99)+(name_score*0.1)) DESC

										";
			
			/*
			$res_get_categorys_query	= "select
											f.id, f.idParent, fr.name
											
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
												fr.name like '%".$category."%' 
											)
											

										";
				*/						
					
//echo $res_get_categorys_query;

			$res_get_categorys = $db->query($res_get_categorys_query, __FILE__, __LINE__);
			
							
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
			
			//Tri par ordre alphabetique (Famille niveau 1)
			//asort($autocomplete_category);
			
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
				
				echo('<table>');
					echo('</tr>');
				while($autocomplete_category[$category_local_loop][1]){
					if(!empty($autocomplete_category[$category_local_loop][0])){
					
						echo('<tr>');
						
							echo('<td>');
								echo($autocomplete_category[$category_local_loop][0].'&nbsp;');
							echo('</td>');
							echo('<td>');
								echo('<b>&raquo;</b> '.$autocomplete_category[$category_local_loop][1]);
							echo('</td>');
							
							$res_content_category_name_show		= $autocomplete_category[$category_local_loop][2];
							
							//To eliminate the multiple blank space
							$category_new						= preg_replace('/\s+/', ' ',$category);
							
							$res_content_category_name_array	= explode(' ',$category_new);
							$local_count = 0;
							while(!empty($res_content_category_name_array[$local_count])){
							
								//Remove the antislashes for the comparaison (be fo bold)
								$res_content_category_name_array[$local_count] = stripslashes($res_content_category_name_array[$local_count]);
								
								
								//If the word is not in the array list and if it's length is more then 1 
								//Because we have a problem when it is a "à" string
								if(!in_array($res_content_category_name_array[$local_count], $array_chars_to_avoid) && strlen($res_content_category_name_array[$local_count])>1){
									$res_content_category_name_show = str_ireplace($res_content_category_name_array[$local_count],'<b>'.$res_content_category_name_array[$local_count].'</b>',$res_content_category_name_show);
								}
								
	//echo(utf8_decode(utf8_encode($res_content_category_name_array[$local_count])).'<br />');
								
								$local_count++;
							}
							
							echo('<td class="pac3fc" onclick="javascript:product_get_this_category(\''.$autocomplete_category[$category_local_loop][3].'\',\''.addslashes($autocomplete_category[$category_local_loop][2]).'\')">');
								echo('<b>&raquo;</b> '.$res_content_category_name_show);
							echo('</td>');
						
						echo('</tr>');
						
					}//end first if FIRST LEVEL
					$category_local_loop++;
				}//end while
				

					echo('</tr>');
				echo('</table>');
					
			
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