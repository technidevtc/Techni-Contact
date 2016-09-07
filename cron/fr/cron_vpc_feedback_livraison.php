<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
$db = DBHandle::get_instance();

	$sql_users  = "SELECT id,nbr_jrs_apres_expedition FROM bo_users WHERE appels_commerciale='1' ";
	$req_users  = mysql_query($sql_users);
	
	$timenow = date('Y-m-d H:i:s');	
		while($data_users = mysql_fetch_object($req_users)){
			$sql_order  = "SELECT DISTINCT(oo.client_id),oo.id,oo.estimate_id,oo.campaign_id,oo.forecasted_ship
						   FROM  `order` oo ,estimate ee
						   WHERE oo.estimate_id = ee.id
						   AND ee.created_user_id='".$data_users->id."'
						   AND DATE_FORMAT(FROM_UNIXTIME(`forecasted_ship`), '%Y-%m-%d') = DATE_SUB(CURDATE(), INTERVAL ".$data_users->nbr_jrs_apres_expedition." DAY)
						   AND oo.estimate_id >0
						   GROUP BY oo.client_id";
			$req_order  = mysql_query($sql_order);
			$rows_order = mysql_num_rows($req_order);	
			
			echo $sql_order.'<br />';
			
			if($rows_order > 0){
				
			while($data_order = mysql_fetch_object($req_order)){
				$sql_vpc  = "SELECT id FROM call_spool_vpc WHERE estimate_id='".$data_order->estimate_id."' ";
				$req_vpc  =  mysql_query($sql_vpc);
				$rows_vpc =  mysql_num_rows($req_vpc);
				
				if($rows_vpc == 0){
				if($data_order->forecasted_ship != '0'){
					$timestamp_created   = strtotime($timenow);
					$timestamp_campaign  = strtotime($timenow);
					$assigned_operator   = $data_users->id;
					$estimate_id  		 = $data_order->estimate_id;
					$order_id	  		 = $data_order->id;
					$client_id 	  		 = $data_order->client_id;
					$campaignID  		 = $data_order->campaign_id;
					$calls_count		 = '0';
					$call_result  		 = 'not_called';
					
					//echo $order_id.'<br />';
					
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
								VALUES (NULL, 
										'".$order_id."', 
										'".$client_id."', 
										'', 
										'$campaignID', 
										'$estimate_id', 
										NOW(),
										NOW(), 
										'Feedback livraison', 
										'2', 
										'$assigned_operator', 
										'', 
										'0000-00-00 00:00:00', 
										'0000-00-00 00:00:00', 
										'0000-00-00 00:00:00', 
										'0000-00-00 00:00:00', 
										'0', 
										'not_called')";
					echo $sql_insert.';<br /><br /><br />';
					mysql_query($sql_insert);
				}
				}
			}
			}
			
		}
?>