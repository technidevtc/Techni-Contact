<?php

	require_once('functions.php');

	$query_access_tables	= "SELECT 
								id, name_fo								
							FROM
								marketing_tables
							ORDER BY name_fo ASC ";
	$res_get_access_tables 	= $db->query($query_access_tables, __FILE__, __LINE__);
		
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
					echo('<input type="checkbox" name="user_access_table_segment'.$local_count.'" id="user_access_table_segment'.$local_count.'" value="'.$content_get_access_tables['id'].'" />');
				echo('</td>');
				
				echo('<td class="ac">');
					echo('<input type="checkbox" name="user_access_table_export'.$local_count.'" id="user_access_table_export'.$local_count.'" value="'.$content_get_access_tables['id'].'" />');
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