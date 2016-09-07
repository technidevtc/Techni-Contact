<?php
	require_once('functions.php'); 
	require('mailin-api/mailin_webhook.php');
	
	$mailin = new Mailin("https://api.sendinblue.com/v2.0","MnYUwd05CQZy8aWh");
		// $date_serv = date("Y-m-d");	
		$date_serv = "2016-01-01";
		
		$limit = 100000;
		$start_date = "";
		$end_date = "";
		$offset = 1;
		$date = $date_serv;
		$days = 0;
		//$email = "outarocht.zakaria@gmail.com";
		$content_array = $mailin->get_report($limit,$start_date,$end_date,$offset,$date,$days);
	
		$content_array_count = count($content_array['data']);
		//echo $content_array_count;
		for($i=0;$i<= $content_array_count ;$i++){
			$email         = $content_array['data'][$i]["email"];
			$events 	   = $content_array['data'][$i]["event"];
			$date_envents  = $content_array['data'][$i]["date"];
			$id_campagns   = $content_array['data'][$i]["tag"];
			$date_final     = substr($date_envents, 0, 10);
			
			
			$sql_insert  = "Email : ".$email." -- Date : ".$date_final." -- Evenement : ".$events." -- Tag : ".$id_campagns;
			
			//echo $sql_insert.'<br />';
			$sql_check     = "SELECT id
							  FROM   marketing_check_send_mail 
							  WHERE  id_campaign='".$id_campagns."'
							  AND    email='".$email."'
							  AND    date_send='".$date_final."'";
			$req_check     =  mysql_query($sql_check);
			$rows_check	   =  mysql_num_rows($req_check);
			
			if($rows_check > 0){
				$data_check  = mysql_fetch_object($req_check);
				
				if($events == 'views'){
					$sql_update  = "UPDATE  marketing_check_send_mail SET views='1',first_views='1' 
									WHERE id='".$data_check->id."' ";
					mysql_query($sql_update);
				}
				
				if($events == 'delivery'){
					$sql_update  = "UPDATE  marketing_check_send_mail SET delivery='1'
									WHERE id='".$data_check->id."' ";
					mysql_query($sql_update);
				}
				
				if($events == 'requests'){
					$sql_update  = "UPDATE  marketing_check_send_mail SET requests='1'
									WHERE id='".$data_check->id."' ";
					mysql_query($sql_update);
				}
				
				if($events == 'clicks'){
					$sql_update  = "UPDATE  marketing_check_send_mail SET clicks='1'
									WHERE id='".$data_check->id."' ";
					mysql_query($sql_update);
				}
				
				if($events == 'hard_bounce'){
					$sql_update  = "UPDATE  marketing_check_send_mail SET hard_bounce='1'
									WHERE id='".$data_check->id."' ";
					mysql_query($sql_update);
				}
				
				if($events == 'soft_bounce'){
					$sql_update  = "UPDATE  marketing_check_send_mail SET soft_bounce='1'
									WHERE id='".$data_check->id."' ";
					mysql_query($sql_update);
				}
				
				if($events == 'blocks'){
					$sql_update  = "UPDATE  marketing_check_send_mail SET blocks='1'
									WHERE id='".$data_check->id."' ";
					mysql_query($sql_update);
				}
				
				if($events == 'invalid'){
					$sql_update  = "UPDATE  marketing_check_send_mail SET invalid='1'
									WHERE id='".$data_check->id."' ";
					mysql_query($sql_update);
				}
				
				if($events == 'deferred'){
					$sql_update  = "UPDATE  marketing_check_send_mail SET deferred='1'
									WHERE id='".$data_check->id."' ";
					mysql_query($sql_update);
				}
				
			}
			
		}
?>