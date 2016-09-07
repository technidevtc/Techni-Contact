<?php

	//To use in the Query !
	$local_segment_start_execution	= date('Y-m-d H:i:s');
	$count_executed_segment			= 0;

	
	//Build the query !
	$query_personnalized	= "SELECT 
								count(*) c 
								FROM 
									".$data_get_remaining_segment['condition_from']." 
								WHERE 
									".$data_get_remaining_segment['condition_where']." ";			
	if(!empty($data_get_remaining_segment['condition_group'])){
		$query_personnalized	.= " GROUP BY ".$data_get_remaining_segment['condition_group']." ";
	}

	//Execution of the Query !
	$res_query_personnalized 	= $db->query($query_personnalized, __FILE__, __LINE__);
	$data_query_personnalized 	= $db->fetchAssoc($res_query_personnalized);

	$count_executed_segment		= $data_query_personnalized['c'];
	
	//Update the segment informations !
	$query_update_segment	="UPDATE marketing_segment SET 
								results_count=".$count_executed_segment.",
								date_last_execution_start='".$local_segment_start_execution."',
								date_last_execution_end=NOW()
							WHERE 
								id=".$data_get_remaining_segment['id']." 
							AND
								type='dynamique'";
							
	$res_update_segment 	= $db->query($query_update_segment, __FILE__, __LINE__);

?>