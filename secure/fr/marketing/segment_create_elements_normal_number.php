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
	
	//Force replace Comma to Dot !
	$local_field_value1_A	= str_replace(',','.',$local_field_value1_A);
	
	//Get the selected value
	switch($local_field_selectionned_A){
		case 'egale':
			$local_query_where .=$data_get_field_info['name_sql']."=".$local_field_value1_A." ";
		break;
			
		case 'different':
			$local_query_where .=$data_get_field_info['name_sql']."!=".$local_field_value1_A." ";
		break;
		
		case 'lt':
			$local_query_where .=$data_get_field_info['name_sql'].">".$local_field_value1_A." ";
		break;
		
		case 'lt_egal':
			$local_query_where .=$data_get_field_info['name_sql'].">=".$local_field_value1_A." ";
		break;
		
		case 'gt':
			$local_query_where .=$data_get_field_info['name_sql']."<".$local_field_value1_A." ";
		break;
		
		case 'gt_egal':
			$local_query_where .=$data_get_field_info['name_sql']."<=".$local_field_value1_A." ";
		break;
		
		case 'vide':
			$local_query_where .=$data_get_field_info['name_sql']."='' OR ".$data_get_field_info['name_sql']." IS NULL ";
		break;
		
		case 'non_vide':
			$local_query_where .=$data_get_field_info['name_sql']."!='' OR ".$data_get_field_info['name_sql']." IS NOT NULL ";
		break;		
	}//End switch
	
	//Close the condition
	$local_query_where .=" ) \n";
	
	
?>