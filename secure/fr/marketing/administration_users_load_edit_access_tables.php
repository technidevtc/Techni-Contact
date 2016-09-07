<?php

	require_once('functions.php');

	$query_access_tables	= "SELECT 
								id, name_fo								
							FROM
								marketing_tables
							ORDER BY name_fo ASC ";
	$res_get_access_tables 	= $db->query($query_access_tables, __FILE__, __LINE__);
		
		
	//Looking for the user access roles 
	//Variable "$content_get_user" initied in the parent page
		$query_user_role_tables_segment		= "SELECT 
													id, content								
												FROM
													marketing_roles_acces_tables
												WHERE
													id_user=".$content_get_user['id']."
												AND
													type='segment'";
												
		$res_get_user_role_tables_segment 	= $db->query($query_user_role_tables_segment, __FILE__, __LINE__);
		
		//Fetchig one time !
		$content_get_user_role_tables_segment	= $db->fetchAssoc($res_get_user_role_tables_segment);
		
		
		$query_user_role_tables_export		= "SELECT 
													id, content								
												FROM
													marketing_roles_acces_tables
												WHERE
													id_user=".$content_get_user['id']."
												AND
													type='export'";
												
		$res_get_user_role_tables_export 	= $db->query($query_user_role_tables_export, __FILE__, __LINE__);
		
		//Fetchig one time !
		$content_get_user_role_tables_export	= $db->fetchAssoc($res_get_user_role_tables_export);
		
		
		
	echo('<table id="listing_tables_uses">');
		echo('<tr>');
			echo('<th>Segment&nbsp;</th>');
			echo('<th>Export&nbsp;</th>');
			echo('<th style="text-align:left;">Table</th>');
		echo('</tr>');
		
		$local_count	=1;
		while($content_get_access_tables	= $db->fetchAssoc($res_get_access_tables)){
		
			echo('<tr>');
			
				echo('<td class="ac">');
					echo('<input type="checkbox" name="user_access_table_segment'.$local_count.'" id="user_access_table_segment'.$local_count.'" value="'.$content_get_access_tables['id'].'" ');
					
					//Check if the user have the right for this "table => segment"
					if(strpos($content_get_user_role_tables_segment['content'],'#'.$content_get_access_tables['id'].'#')!==FALSE){
						echo('checked="true"');
					}
					
					echo(' />');
				echo('</td>');
				
				echo('<td class="ac">');
					echo('<input type="checkbox" name="user_access_table_export'.$local_count.'" id="user_access_table_export'.$local_count.'" value="'.$content_get_access_tables['id'].'" ');
					
					//Check if the user have the right for this "table => segment"
					if(strpos($content_get_user_role_tables_export['content'],'#'.$content_get_access_tables['id'].'#')!==FALSE){
						echo('checked="true"');
					}
					
					echo(' />');
				echo('</td>');
				
				echo('<td>');
					echo(''.utf8_decode($content_get_access_tables['name_fo']).'');
				echo('</td>');
				
			echo('</tr>');
			
		
			$local_count++;
		}//end while
	echo('</table>');
	
	//To use it on Javascript or Php action page
	echo('<input type="hidden" id="access_tables_count" name="access_tables_count" value="'.$local_count.'" />');
	


?>