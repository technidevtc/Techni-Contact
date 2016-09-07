<?php 
	require_once('functions.php'); 
	//require_once('check_session.php');

	if(empty($_SESSION['marketing_user_id'])){
		throw new Exception('<a href="/fr/marketing/login.php">Veuillez vous reconnecter</a>.');
	}
		
	$segment_id		= mysql_escape_string($_POST['segment_id']);
	
	if(empty($segment_id)){
		throw new Exception('ID Segment incorrect !');
	}
	
	
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
												INNER JOIN marketing_segment AS m_segment ON m_segment.id_table=mt_fields.id_table
												AND 
													m_segment.id=".$segment_id." 
										WHERE
											mt_fields.filter_flag='yes' 
											
										ORDER BY mt_fields.position ASC";	

	$res_get_tables_fields = $db->query($res_get_tables_fields_query, __FILE__, __LINE__);

	if(mysql_num_rows($res_get_tables_fields)>0){
		
		echo('<table id="message_sf_array">');
		
		$field_id	= 0;
		while($content_get_tables_fields	= $db->fetchAssoc($res_get_tables_fields)){
			
			echo('<tr>');
				echo('<td>'.$content_get_tables_fields['name_fo'].'</td>');
				echo('<td>{{'.$content_get_tables_fields['id'].'#'.$content_get_tables_fields['name_sql'].'}}</td>');
				echo('<td>');
					//onclick="message_copy_that_field(\'{{'.$content_get_tables_fields['name_sql'].'}}\')"
					
					//echo('<input id="message_copy_field_btn_id_'.$field_id.'" type="button" value="Copier" class="btn btn-default message_copy_field_btn" style="padding: 4px 15px; margin-bottom: 4px;" onclick="message_copy_that_field(\'message_copy_field_btn_id_'.$field_id.'\', \'{{'.$content_get_tables_fields['name_sql'].'}}\')" />');
					
					echo('<i class="fa fa-files-o btn btn-default" id="message_copy_field_btn_id_'.$field_id.'" title="Copier" style="padding: 4px 15px; margin-bottom: 4px; cursor: pointer;" onclick="message_copy_that_field(\'message_copy_field_btn_id_'.$field_id.'\', \'{{'.$content_get_tables_fields['id'].'#'.$content_get_tables_fields['name_sql'].'}}\')"></i>');
					
					//echo('<input type="text" id="message_copy_field_btn_id_'.$field_id.'" class="btn btn-default message_copy_field_btn" style="padding: 4px 15px; margin-bottom: 4px;" text="'.$content_get_tables_fields['name_sql'].'" >{{'.$content_get_tables_fields['name_sql'].'}}</span>');
				echo('</td>');
			echo('</tr>');
			
			$field_id++;
		}//End while !
		
	}//end if(mysql_num_rows($res_get_tables_fields)>0)
	
?>