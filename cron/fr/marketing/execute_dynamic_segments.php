<?php

	try {
		
		if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
			require_once '../../../config.php';
		}else{
			require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
		}
		
		$db = DBHandle::get_instance();
		
		$id_today_execution			= "";
		$id_last_segment			= "";
		
		$start_execution			= date('Y-m-d H:i:s');
		$start_execution_query		= date('Y-m-d');
		
		
		$number_executed_segments	= 0;
		
		//Steps
		//Detect if we can use the platform (if we not sync the Database !) !
		//Detect if the execution has already started else insert a new row 
		//Get the informations of Today's execution !
		//Get the remaining dynamic segments to execute
		//While we have segments execute them and save records 
			//(in the "marketing_segment", "marketing_segment_dynamic_execution" tables)
		
		$query_get_sync_flag	="SELECT
									sync_end
								FROM 
									marketing_synchronization_flag  
								WHERE 
									id=1 ";
		$res_get_sync_flag 	= $db->query($query_get_sync_flag, __FILE__, __LINE__);
		$data_get_sync_flag 	= $db->fetchAssoc($res_get_sync_flag);
		
	
		if(strcmp($data_get_sync_flag['sync_end'],'no')==0){
		
			//The program can't execute the segments !
			//Send mail to the administrator
			
		}else if(strcmp($data_get_sync_flag['sync_end'],'yes')==0){
			
			$query_get_today_exec	="SELECT 	
											id, 
											id_last_segment,
											count_executed_segment
										FROM
											marketing_segment_dynamic_execution
										WHERE 
											date_execution='".$start_execution_query."'
										";						
										
			$res_get_today_exec 	= $db->query($query_get_today_exec, __FILE__, __LINE__);	
			
			if(mysql_num_rows($res_get_today_exec)==0){
				$query_get_today_exec	="INSERT INTO marketing_segment_dynamic_execution
											(id, id_last_segment, 
											count_executed_segment, date_execution, 
											date_last_insert) 
										VALUES(NULL, 0,
												0, '".$start_execution_query."', 
												'".$start_execution."')";						
										
				$res_get_today_exec 	= $db->query($query_get_today_exec, __FILE__, __LINE__);
				
				//Init the vars manually !
				$id_today_execution		= mysql_insert_id();
				$id_last_segment		= 0;
				
			}else{
				
				//We have the row Fetch it and init the global vars !
				$data_get_today_exec 	= $db->fetchAssoc($res_get_today_exec);
				
				$id_today_execution			= $data_get_today_exec['id'];
				$id_last_segment			= $data_get_today_exec['id_last_segment'];
				
				$number_executed_segments	= $data_get_today_exec['count_executed_segment'];
			}
			
			
			//Get the remaining dynamic segments to execute
			$query_get_remaining_segment	="SELECT
												id,
												id_table,
												condition_select,
												condition_from,
												condition_where,
												condition_group
											FROM 
												marketing_segment 
											WHERE 
												type='dynamique' 
											AND 
												id>".$id_last_segment." 
											ORDER BY id";
									
			$res_get_remaining_segment 	= $db->query($query_get_remaining_segment, __FILE__, __LINE__);
			
			
			//While we have segments execute them and save the informations !!
			while($data_get_remaining_segment = $db->fetchAssoc($res_get_remaining_segment)){
				
				//Increment the number of the executed segments !
				$number_executed_segments++;
				
				
				//Execute a query to detect if this segment has a campaing !
				//If it has a campaing => call a external file that will send the mails..
				//Else  => call a external file that will calculate only the count !
				
				//if(){
					//require_once('execute_dynamic_segment_mail.php');
				//}else{
					require('execute_dynamic_segment_count.php');
				//}//End else if ..
				
				
				//Update the "marketing_segment_dynamic_execution"
				$query_update_today_exec	="UPDATE marketing_segment_dynamic_execution SET
											id_last_segment='".$data_get_remaining_segment['id']."', 
											count_executed_segment='".$number_executed_segments."',  
											date_last_insert=NOW()
											
											WHERE
												id=".$id_today_execution."";						
										
				$res_update_today_exec 	= $db->query($query_update_today_exec, __FILE__, __LINE__);
				
				
			}//End while !
		
		}//End check use of the platform !
		
	} catch (Exception $e) {
		echo('Time '.date('d/m/Y H:i:s').' **Error: '.$e);
	}
?>