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

	//Looking for the ID's of the Family RD
	$query_get_infos3		="SELECT
									id
								FROM
									families
								WHERE
									idparent=".$local_field_value1_A."
							
							";
							
	$res_get_infos3 		= $db->query($query_get_infos3, __FILE__, __LINE__);
	
	
	if(strcmp($local_field_operation_A,"1")==0){
		$local_query_where .=" \n ( ".$data_get_field_info['name_sql']." IN ( ";
	}else if(strcmp($local_field_operation_A,"AND")==0){
		$local_query_where .=" \n AND ( ".$data_get_field_info['name_sql']." IN (";
	}else if(strcmp($local_field_operation_A,"OR")==0){
		$local_query_where .=" \n OR ( ".$data_get_field_info['name_sql']." IN ( ";
	}
	
	
	
	$local_ids				= "";
	
	//Fetching all the results 
	while($data_get_infos3 	= $db->fetchAssoc($res_get_infos3)){
		
		$local_ids 			.= $data_get_infos3['id'].", ";
		
	}//End while fetching the third level
	
	
	//Remove the last two chars ", "
	
	$local_ids = substr($local_ids, 0, -2);
	
	$local_query_where	.= $local_ids;
	
	
	//Closing the opened elements
	$local_query_where .=" ) \n";
	$local_query_where .=" ) \n";
	
?>