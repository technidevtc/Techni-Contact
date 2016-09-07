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
	
	
	//Replace "-" By "/" TO build the TimeStamp !
	$local_field_value1_A	= str_replace("-","/",$local_field_value1_A);
	
	
	//Get the selected value
	switch($local_field_selectionned_A){
		case 'egale':
		
			$local_timestamp1		= strtotime($local_field_value1_A." 00:00:00");
			$local_timestamp2		= strtotime($local_field_value1_A." 23:59:59");
			
			
			$local_query_where .=$data_get_field_info['name_sql']." BETWEEN ".$local_timestamp1." AND ".$local_timestamp2." ";
		break;
		
		case 'entre':
		
			$local_timestamp1		= strtotime($local_field_value1_A." 00:00:00");
		
			//Replace "-" By "/" TO build the TimeStamp !
			$local_field_value2_A	= str_replace("-","/",$local_field_value2_A);
			$local_timestamp2		= strtotime($local_field_value2_A." 23:59:59");
	
			$local_query_where .=$data_get_field_info['name_sql']." BETWEEN ".$local_timestamp1." AND ".$local_timestamp2." ";
		break;
		
		case 'lt':
			$local_timestamp1		= strtotime($local_field_value1_A." 00:00:00");
			
			$local_query_where .=$data_get_field_info['name_sql'].">".$local_timestamp1." ";
		break;
		
		case 'lt_egale':
			$local_timestamp1		= strtotime($local_field_value1_A." 00:00:00");
			
			$local_query_where .=$data_get_field_info['name_sql'].">=".$local_timestamp1." ";
		break;
		
		case 'gt':
			$local_timestamp1		= strtotime($local_field_value1_A." 00:00:00");
			
			$local_query_where .=$data_get_field_info['name_sql']."<".$local_timestamp1." ";
		break;
		
		case 'gt_egale':
			$local_timestamp1		= strtotime($local_field_value1_A." 00:00:00");
			
			$local_query_where .=$data_get_field_info['name_sql']."<=".$local_timestamp1." ";
		break;
		
		case 'aujourdhui_plus':
			
			//$local_query_where .="DATE_ADD(FROM_UNIXTIME(".$data_get_field_info['name_sql'].", '%Y/%m/%d' ), INTERVAL ".$local_field_value1_A." DAY)=CURDATE()";
			$local_query_where .=" FROM_UNIXTIME(".$data_get_field_info['name_sql'].", '%Y/%m/%d' ) = DATE_ADD(CURDATE(), INTERVAL ".$local_field_value1_A." DAY)";
			   
			
		break;

		case 'aujourdhui_moins':
			
			$local_query_where .="DATE_SUB(CURDATE(), INTERVAL ".$local_field_value1_A." DAY)=FROM_UNIXTIME(".$data_get_field_info['name_sql'].", '%Y/%m/%d' )";
		break;		
	}//End switch
	
	
	
	
	//Close the condition
	$local_query_where .=" ) \n";
	
	
?>