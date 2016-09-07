<?php

	require_once('functions.php');

	$query_access_pages	= "SELECT 
								id, name								
							FROM
								marketing_roles_access 
							ORDER BY name ASC ";
	$res_get_access_pages 	= $db->query($query_access_pages, __FILE__, __LINE__);
	
	//Looking for the user access roles 
	//Variable "$content_get_user" initied in the parent page
		$query_user_role_access_pages		= "SELECT 
													id, content								
												FROM
													marketing_roles_acces_users 
												WHERE
													id_user=".$content_get_user['id']." ";
												
		$res_get_user_role_access_pages 	= $db->query($query_user_role_access_pages, __FILE__, __LINE__);
		
		//Fetchig one time !
		$content_get_user_role_access_pages	= $db->fetchAssoc($res_get_user_role_access_pages);
		
	echo('<table>');
		echo('<tr>');
			echo('<th>&nbsp;</th>');
			echo('<th>Page / Action</th>');
		echo('</tr>');
		
		$local_count	=1;
		while($content_get_access_pages	= $db->fetchAssoc($res_get_access_pages)){
		
			echo('<tr>');
			
				echo('<td>');
					echo('<input type="checkbox" name="user_access_page_'.$local_count.'" id="user_access_page_'.$local_count.'" value="'.$content_get_access_pages['id'].'" ');
					
					//Check if the user have the right for this "page/action"
					if(strpos($content_get_user_role_access_pages['content'],'#'.$content_get_access_pages['id'].'#')!==FALSE){
						echo('checked="true"');
					}
					
					echo(' />');
				echo('</td>');
				echo('<td>');
					echo('<label for="user_access_page_'.$local_count.'">'.utf8_decode($content_get_access_pages['name']).'<label>');
				echo('</td>');
				
			echo('</tr>');
			
			$local_count++;
		
		}//end while
	echo('</table>');
	
	//To use it on Javascript or Php action page
	echo('<input type="hidden" id="access_pages_count" name="access_pages_count" value="'.$local_count.'" />');

?>