<?php
if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

$db = DBHandle::get_instance();

	$sql_users  = "SELECT id,nbr_jrs_relance FROM bo_users WHERE appels_commerciale='1' ";
	$req_users  = mysql_query($sql_users);
	
	$date_now	  	   = date('Y-m-d');
	$yesterday_start   = strtotime($date_now.' 00:00:00');
	$yesterday_end     = strtotime($date_now.' 23:59:59');
	
		while($data_users = mysql_fetch_object($req_users)){
		$assigned_operator   = $data_users->id;	
			$sql_contact  = "SELECT DISTINCT(cc.id),idAdvertiser,campaignID,cc.create_time 
							 FROM contacts cc ,advertisers aa
							 WHERE cc.idAdvertiser = aa.id
							 AND id_user_commercial='".$data_users->id."'
							 AND processing_status='1'
							 AND cc.pays IN ('FRANCE','Suisse','belgique')
							 AND cc.create_time BETWEEN '".$yesterday_start."' AND '".$yesterday_end."' 
							 GROUP BY cc.id ";
			//echo $sql_contact.'<br />';
			$req_contact  =  mysql_query($sql_contact);
			while($data_contact =  mysql_fetch_object($req_contact)){
				$sql_check_contact  = "SELECT id_contact 
									   FROM call_spool_vpc 
									   WHERE id_contact='".$data_contact->id."' ";
				$req_check_contact  =  mysql_query($sql_check_contact);
				$rows_check_contact =  mysql_num_rows($req_check_contact);
				if($rows_check_contact == 0){
						$sql_insert = "INSERT INTO `call_spool_vpc` (
													`id`, 
													`order_id`, 
													`client_id`, 
													`rdv_id`, 
													`campaignID`, 
													`estimate_id`, 
													`id_contact`, 
													`timestamp_created`, 
													`timestamp_campaign`, 
													`campaign_name`, 
													`call_type`, 
													`assigned_operator`, 
													`call_operator`, 
													`timestamp_rdv`, 
													`timestamp_first_call`, 
													`timestamp_second_call`, 
													`timestamp_third_call`, 
													`calls_count`, 
													`call_result`) 
										VALUES (NULL, '0', 
													'', 
													'', 
													'".$data_contact->campaignID."', 
													'0', 
													'".$data_contact->id."', 
													NOW(),
													'0000-00-00 00:00:00', 
													'Requalif lead', 
													'5', 
													'".$assigned_operator."', 
													'', 
													'0000-00-00 00:00:00', 
													'0000-00-00 00:00:00', 
													'0000-00-00 00:00:00', 
													'0000-00-00 00:00:00', 
													'0', 
													'not_called')";
					echo $sql_insert.'<br /><br />';
					mysql_query($sql_insert);
				}else {
					echo 'deja enregistré<br />'; 
				}
			}
			
		}
		
	$sql_status 	 = "SELECT id,id_contact FROM call_spool_vpc WHERE call_type='5' ";
	$req_status  	 =  mysql_query($sql_status);
	while($data_data = mysql_fetch_object($req_status)){
		$sql_contact = "SELECT processing_status FROM contacts WHERE id='".$data_data->id_contact."' ";
		$req_contact =  mysql_query($sql_contact);
		$data_contact = mysql_fetch_object($req_contact);
		if($data_contact->processing_status != '1'){
			$sql_delete = "DELETE FROM call_spool_vpc WHERE id='".$data_data->id."' ";
			mysql_query($sql_delete);
			echo $sql_delete.'<br />';
		}		
	}
	
	
	$sql_operator  =  "SELECT id,assigned_operator FROM call_spool_vpc WHERE call_type='5' ";
	$req_operator  =   mysql_query($sql_operator);
	while($data_operator =   mysql_fetch_object($req_operator)){
		$sql_total  = "SELECT COUNT(id) as total,id_contact,id
					   FROM call_spool_vpc 
					   WHERE call_type='5' 
					   AND assigned_operator='".$data_operator->assigned_operator."' ";
		$req_total  =  mysql_query($sql_total);
		$data_total =  mysql_fetch_object($req_total);
		//echo $sql_total.'<br />';
		if($data_total->total == '2'){
			$sql_delete  = "DELETE FROM call_spool_vpc WHERE id='".$data_total->id."' ";
			mysql_query($sql_delete);
		}		
	}
	
	$sql_contact_email  =  "SELECT csv.id,assigned_operator,cc.email 
							FROM call_spool_vpc csv, contacts cc 
						    WHERE call_type='5'
							AND csv.id_contact = cc.id ";
	$req_contact_email  =   mysql_query($sql_contact_email);
	while($data_contact_email =   mysql_fetch_object($req_contact_email)){
		$data = array('id' => $data_contact_email->id, 'email' => $data_contact_email->email);
		$tableau[] = $data;
	}
	
	$dupe_array = array();
	foreach($tableau as $key => $value){
	if(++$dupe_array[$value['email']] > 1){
		   $sql_delete 	 =  "DELETE FROM call_spool_vpc WHERE id='".$value['id']."' ";
		   mysql_query($sql_delete);
		}
		
	}
?>