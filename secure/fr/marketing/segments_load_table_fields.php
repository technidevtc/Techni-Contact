<?php

	require_once('functions.php');
	
	$table_id		= mysql_escape_string($_POST['table_id']);
	
	if(!empty($_SESSION['marketing_user_id']) && !empty($table_id)){
	
		$res_get_tables_fields_query	= "SELECT
												mt_fields.id,
												mt_fields.name_fo,
												mt_fields.name_sql,
												mt_fields.name_sql_as,
												mt_fields.special_field,
												mt_fields.special_field_query,
												mt_fields.field_type,
												mt_fields.field_str_replace
												
											FROM 
												marketing_tables_fields AS mt_fields 
											WHERE
												mt_fields.id_table=".$table_id." 
											AND
												mt_fields.filter_flag='yes' 
												
											ORDER BY mt_fields.position ASC";	

		$res_get_tables_fields = $db->query($res_get_tables_fields_query, __FILE__, __LINE__);
	
		if(mysql_num_rows($res_get_tables_fields)>0){
			
			while($content_get_tables_fields	= $db->fetchAssoc($res_get_tables_fields)){
				echo('<div class="table_field" 
						data-field-id="'.$content_get_tables_fields['id'].'"
						data-field-special="'.$content_get_tables_fields['special_field'].'"
						data-field-type="'.$content_get_tables_fields['field_type'].'"
						data-field-select="'.$content_get_tables_fields['field_str_replace'].'"
						data-field-name-sql="'.$content_get_tables_fields['name_sql'].'"
						data-field-name-sql-as="'.$content_get_tables_fields['name_sql_as'].'"
					>');
					echo($content_get_tables_fields['name_fo']);
				echo('</div>');
			}
			
		}//end if(mysql_num_rows($res_get_tables_fields)>0)
		
		
	
	}
?>