<?php

	//Get the Field Informations
	$query_get_field_info	="SELECT
									name_sql
								FROM
									marketing_tables_fields
								WHERE
									id=".$local_field_id_A."
							
							";
							
	$res_get_field_info 	= $db->query($query_get_field_info, __FILE__, __LINE__);
	$data_get_field_info 	= $db->fetchAssoc($res_get_field_info);
	
	if(strcmp($local_field_operation_A,"1")==0){
		$local_query_where .=" \n ( ";
	}else if(strcmp($local_field_operation_A,"AND")==0){
		$local_query_where .=" \n AND ( ";
	}else if(strcmp($local_field_operation_A,"OR")==0){
		$local_query_where .=" \n OR ( ";
	}
	
	
	//Get the selected value
	$local_query_where .=$data_get_field_info['name_sql']."='".$local_field_value1_A."' ";
		
	
	//Close the condition
	$local_query_where .=" ) \n";
	
	
?>