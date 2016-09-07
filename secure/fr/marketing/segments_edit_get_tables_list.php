<?php

	if(!empty($_SESSION['marketing_user_id'])){
	
		$res_get_tables_query	= "SELECT
										mtables.id,
										mtables.name_fo
									FROM
										marketing_tables AS mtables
										
									ORDER BY mtables.name_fo ASC";	
	
		$res_get_tables = $db->query($res_get_tables_query, __FILE__, __LINE__);
	
		echo('<select id="segment_table_list" onchange="tables_get_fields_now()">');
			//echo('<option value="" selected="true"></option>');
			
		while($content_get_tables	= $db->fetchAssoc($res_get_tables)){
			
			//Get the global var that contain the user access table permission 
			if(strpos($content_get_user_tables_access_permissions['content'],'#'.$content_get_tables['id'].'#')!==FALSE){
				echo('<option value="'.$content_get_tables['id'].'"');
					if(strcmp($data_get_segment_informations['id_table'],$content_get_tables['id'])==0){
						echo(' selected="true" ');					
					}
				echo('>'.$content_get_tables['name_fo'].'</option>');
			}
		}
		
		echo('</select>');
	
	}
?>