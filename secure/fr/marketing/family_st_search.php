<?php
	require_once('functions.php'); 
	
	function zikosoft2_replace_accents($string){
		return str_replace( array('à','á','â','ã','ä', 'ç', 'è','é','ê','ë', 'ì','í','î','ï', 'ñ', 'ò','ó','ô','õ','ö', 'ù','ú','û','ü', 'ý','ÿ', 'À','Á','Â','Ã','Ä', 'Ç', 'È','É','Ê','Ë', 'Ì','Í','Î','Ï', 'Ñ', 'Ò','Ó','Ô','Õ','Ö', 'Ù','Ú','Û','Ü', 'Ý'), array('a','a','a','a','a', 'c', 'e','e','e','e', 'i','i','i','i', 'n', 'o','o','o','o','o', 'u','u','u','u', 'y','y', 'A','A','A','A','A', 'C', 'E','E','E','E', 'I','I','I','I', 'N', 'O','O','O','O','O', 'U','U','U','U', 'Y'), $string);
	}

	if(!empty($_SESSION['marketing_user_id'])){
		
		$category					= mysql_escape_string($_POST['field_value']);
		$id_row						= mysql_escape_string($_POST['id_row']);
		
		//$category				= zikosoft2_replace_accents($category);
		
		$array_chars_to_avoid	= array("<", "<b", "<b>", "b", "b>", "</", "</b", "</b>", "/b", "/b>", ">");
		
		if(!empty($category)){
			
			
			$category_prepare_sql	= str_replace(' ','* ',$category);
			$category_sql			= '*'.$category_prepare_sql.'*';
			
			$category_exact_sql		= $category;
			
			$autocomplete_category = array("");
			
			$res_get_categorys_query	= "	SELECT
												f.id, f.idParent, fr.name,
												
												MATCH (fr.name) AGAINST ('".$category_sql."' IN BOOLEAN MODE) as name_score,
												MATCH (fr.name) AGAINST ('".$category_exact_sql."') as name_score_exact
												
											FROM
												families f, 
												families_fr fr
											WHERE
												f.id = fr.id
											AND
												idParent=0
											AND
													(		
															MATCH (fr.name) AGAINST ('".$category_sql."'  IN BOOLEAN MODE)
														OR
															MATCH (fr.name) AGAINST ('".$category_exact_sql."')
													)

											order by
												((name_score_exact*0.99)+(name_score*0.1)) DESC";
			
		
			$res_get_categorys = $db->query($res_get_categorys_query, __FILE__, __LINE__);
			
			echo('<ul class="familly_autocomplete_list">');
			
			while($res_content_categorys = $db->fetchAssoc($res_get_categorys)){
				
				echo('<li onclick="fill_family_from_autocomplete(\'st\', \''.$id_row.'\', \''.$res_content_categorys['id'].'\', \''.addslashes($res_content_categorys['name']).'\')">'.$res_content_categorys['name'].'</li>');
				
			}//End while !
			
			echo('</ul>');
		
		}else{
			//Erreur, Merci de recharger la page
			echo('R&eacute;essayez');
		}//end else if !empty
		
	}else{
		//Forward reconnect !
		echo('Vous devez vous reconnecter pour continuer !');
	}//end if empty session
?>