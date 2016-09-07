<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
$db = DBHandle::get_instance();


	$sql_users  = "SELECT id,nbr_jrs_relance FROM bo_users WHERE appels_commerciale='1' ";
	$req_users  = mysql_query($sql_users);
		// CRON alimente les types de contact -	Relance devis (- 3 jours)
		while($data_users = mysql_fetch_object($req_users)){
			$timenow = date('Y-m-d H:i:s');
			
			$sql_estimate  = "SELECT DISTINCT(client_id),id,order_id,campaign_id,updated_mail_sent_pdf
							  FROM   estimate
							  WHERE  created_user_id = '".$data_users->id."'
							  AND    DATE_FORMAT(FROM_UNIXTIME(`updated_mail_sent_pdf`), '%Y-%m-%d') =  DATE_SUB(CURDATE(), INTERVAL ".$data_users->nbr_jrs_relance." DAY)
							  AND 	 pays IN ('FRANCE','Suisse','belgique')
							  AND    status IN('2','3')
							  GROUP BY client_id";	
										  
			$req_estimate  = mysql_query($sql_estimate);
			while($data_estimate = mysql_fetch_object($req_estimate)){
				$sql_check_contact  = "SELECT id 
									   FROM call_spool_vpc 
									   WHERE estimate_id='".$data_estimate->id."' ";
				$req_check_contact  =  mysql_query($sql_check_contact);
				$rows_check_contact =  mysql_num_rows($req_check_contact);
				if($rows_check_contact == 0){
				
				if($data_estimate->updated_mail_sent_pdf != '0'){
					
					$timestamp_created   = strtotime($timenow);
					$timestamp_campaign  = strtotime($timenow);
					$assigned_operator   = $data_users->id;
					$estimate_id  		 = $data_estimate->id;
					$order_id	  		 = $data_estimate->order_id;
					$client_id 	  		 = $data_estimate->client_id;
					$campaignID  		 = $data_estimate->campaign_id;
					$calls_count		 = '0';
					$call_result  		 = 'not_called';
					
					$sql_insert = "INSERT INTO `call_spool_vpc` (
									`id`, 
									`order_id`, 
									`client_id`, 
									`rdv_id`, 
									`campaignID`, 
									`estimate_id`, 
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
						  VALUES (NULL, '".$order_id."', 
										'".$client_id."', 
										'', 
										'$campaignID', 
										'$estimate_id', 
										NOW(),
										NOW(), 
										'Relance devis', 
										'1', 
										'$assigned_operator', 
										'', 
										'0000-00-00 00:00:00', 
										'0000-00-00 00:00:00', 
										'0000-00-00 00:00:00', 
										'0000-00-00 00:00:00', 
										'0', 
										'not_called')";
					echo $sql_insert.'<br /><br />';
					mysql_query($sql_insert);
				}
				}
			
			}
		}
		
	$sql_status 	 = "SELECT id,estimate_id FROM call_spool_vpc WHERE call_type='1' ";
	$req_status  	 =  mysql_query($sql_status);
	while($data_data = mysql_fetch_object($req_status)){
		$sql_contact = "SELECT status FROM estimate WHERE id='".$data_data->estimate_id."' ";
		$req_contact =  mysql_query($sql_contact);
		$data_contact = mysql_fetch_object($req_contact);
		
		
		if($data_contact->status == '5'){
			$sql_delete = "DELETE FROM call_spool_vpc WHERE id='".$data_data->id."' ";
			mysql_query($sql_delete);
			echo $sql_delete.'<br />';
		}
		
		if($data_contact->status == '1'){
			$sql_delete = "DELETE FROM call_spool_vpc WHERE id='".$data_data->id."' ";
			mysql_query($sql_delete);
			echo $sql_delete.'<br />';
		}
		
		if($data_contact->status == '4'){
			$sql_delete = "DELETE FROM call_spool_vpc WHERE id='".$data_data->id."' ";
			mysql_query($sql_delete);
			echo $sql_delete.'<br />';
		}
			
		
	}


?>