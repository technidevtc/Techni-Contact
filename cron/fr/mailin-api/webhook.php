<?php
		require_once('../functions.php'); 
		require('mailin_webhook.php');
		$mailin = new Mailin('https://api.sendinblue.com/v2.0', 'MnYUwd05CQZy8aWh');

		
		$date_serv = "2015-06-16";	
		$limit = 1000;
		$start_date = "";
		$end_date = "";
		$offset = 1;
		$date = $date_serv;
		$days = 0;
		//$email = "outarocht.zakaria@gmail.com";
		$content_array = $mailin->get_report($limit,$start_date,$end_date,$offset,$date,$days);

		

		
		$content_array_count = count($content_array['data']);
		
		for($i=0;$i<= $content_array_count ;$i++){
			$email         = $content_array['data'][$i]["email"];
			$events 	   = $content_array['data'][$i]["event"];
			$date_envents  = $content_array['data'][$i]["date"];
			$tag 		   = $content_array['data'][$i]["tag"];
			
			//$sql_insert  = "Email : ".$email." -- Date : ".$date_envents." -- Evenement : ".$events." -- Tag : ".$tag;
			
				if($events == 'blocks'){
					$sql_update = "UPDATE `marketing_base_emails_operations_copy` 
									SET  `evenement` =  '$events',
										 `date_evenement` = '$date_envents'				
									WHERE  `generated_key` ='$tag' ";
					mysql_query($sql_update);
				}
				
				if($events == 'opened'){
					$sql_update = "UPDATE `marketing_base_emails_operations_copy` 
									SET  `evenement` =  '$events',
										 `date_evenement` = '$date_envents'				
									WHERE  `generated_key` ='$tag' ";
					mysql_query($sql_update);
				}
				
				if($events == 'clicks'){
					$sql_update = "UPDATE `marketing_base_emails_operations_copy` 
									SET  `evenement` =  '$events',
										 `date_evenement` = '$date_envents'				
									WHERE  `generated_key` ='$tag' ";
					mysql_query($sql_update);
				}
				
				if($events == 'views'){
					$sql_update = "UPDATE `marketing_base_emails_operations_copy` 
									SET  `evenement` =  '$events',
										 `date_evenement` = '$date_envents'				
									WHERE  `generated_key` ='$tag' ";
					mysql_query($sql_update);
					
				}
				
				if($events == 'delivery'){
					$sql_update = "UPDATE `marketing_base_emails_operations_copy` 
									SET  `evenement` =  '$events',
										 `date_evenement` = '$date_envents'				
									WHERE  `generated_key` ='$tag' ";
					mysql_query($sql_update);
				}
				
				if($events == 'requests'){
					$sql_update = "UPDATE `marketing_base_emails_operations_copy` 
									SET  `evenement` =  '$events',
										 `date_evenement` = '$date_envents'				
									WHERE  `generated_key` ='$tag' ";
					mysql_query($sql_update);
				}
				
				if($events == 'spam'){
					$sql_update = "UPDATE `marketing_base_emails_operations_copy` 
									SET  `evenement` =  '$events',
										 `date_evenement` = '$date_envents'				
									WHERE  `generated_key` ='$tag' ";
					mysql_query($sql_update);
				}
				
				if($events == 'hard_bounce'){
					$sql_update = "UPDATE `marketing_base_emails_operations_copy` 
									SET  `evenement` =  '$events',
										 `date_evenement` = '$date_envents'				
									WHERE  `generated_key` ='$tag' ";
					mysql_query($sql_update);
				}
				
				if($events == 'soft_bounce'){
					$sql_update = "UPDATE `marketing_base_emails_operations_copy` 
									SET  `evenement` =  '$events',
										 `date_evenement` = '$date_envents'				
									WHERE  `generated_key` ='$tag' ";
					mysql_query($sql_update);
				}
			
		}
?>
