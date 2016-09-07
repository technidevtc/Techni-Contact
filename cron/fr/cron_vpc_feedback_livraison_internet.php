<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
$db = DBHandle::get_instance();

	$sql_users  = "SELECT id,nbr_jrs_apres_expedition FROM bo_users WHERE appels_commerciale='1' ";
	$req_users  = mysql_query($sql_users);
	
	$timenow = date('Y-m-d H:i:s');	
		while($data_users = mysql_fetch_object($req_users)){
			
				$sql_order_line  = "SELECT MAX(ol.total_ttc) as total,ol.pdt_id ,aa.idCommercial,ol.order_id,aa.client_id,oo.forecasted_ship
								    FROM order_line ol,products pp ,advertisers aa,`order` oo
									WHERE ol.pdt_id = pp.id
									AND   oo.id		= ol.order_id
									AND   aa.id = pp.idAdvertiser
									AND   DATE_FORMAT(FROM_UNIXTIME(`forecasted_ship`), '%Y-%m-%d') = DATE_SUB(CURDATE(), INTERVAL ".$data_users->nbr_jrs_apres_expedition." DAY)
									AND   aa.idCommercial = '".$data_users->id."'
									AND   oo.estimate_id ='0'
									";
				$req_order_line  =  mysql_query($sql_order_line);
				$data_order_line =  mysql_fetch_object($req_order_line);  
				
				$sql_camp  = "SELECT campaign_id FROM order WHERE id='".$data_order_line->order_id."' ";
				$req_camp  =  mysql_query($sql_camp);
				$data_camp =  mysql_fetch_object($req_camp);
				
				//echo $sql_order_line.'<br />';
				
				
				$sql_vpc  = "SELECT id FROM call_spool_vpc WHERE order_id='".$data_order_line->order_id."' ";
				$req_vpc  =  mysql_query($sql_vpc);
				$rows_vpc =  mysql_num_rows($req_vpc);
				
				if($rows_vpc == 0){
				if($data_order->forecasted_ship != '0'){
					$timestamp_created   = strtotime($timenow);
					$timestamp_campaign  = strtotime($timenow);
					$assigned_operator   = $data_order_line->idCommercial;
					$estimate_id  		 = '';
					$order_id	  		 = $data_order_line->order_id;
					$client_id 	  		 = $data_order_line->client_id;
					$campaignID  		 = $data_camp->campaign_id;
					$calls_count		 = '0';
					$call_result  		 = 'not_called';
					
					//echo $order_id.'<br />';
					
					$sql_check_id = "SELECT appels_commerciale FROM bo_users WHERE id='".$assigned_operator."'";
					$req_check_id =  mysql_query($sql_check_id);
					$data_check_id = mysql_fetch_object($req_check_id);

					if($data_check_id->appels_commerciale == '1' ){
					
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
										'Feedback livraison internet', 
										'6', 
										'$assigned_operator', 
										'', 
										'0000-00-00 00:00:00', 
										'0000-00-00 00:00:00', 
										'0000-00-00 00:00:00', 
										'0000-00-00 00:00:00', 
										'0', 
										'not_called')";
					//echo $sql_insert.';<br /><br /><br />';
					mysql_query($sql_insert);
				}
				}
				
				//}
			//}
			}
			
		}
?>