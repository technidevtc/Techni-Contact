<?php
		require_once('../functions.php'); 
		require('mailin_webhook.php');
		$mailin = new Mailin('https://api.sendinblue.com/v2.0', 'MnYUwd05CQZy8aWh');

		$date_serv = "2015-05-27";	
		$limit = 1000;
		$start_date = "";
		$end_date = "";
		$offset = 4;
		$date = $date_serv;
		$days = 0;
		//$email = "outarocht.zakaria@gmail.com";
		$content_array = $mailin->get_report($limit,$start_date,$end_date,$offset,$date,$days);
		
		$content_array_count = count($content_array['data']);
		
		
		for($i=0;$i<= $content_array_count ;$i++){
			$email  = $content_array['data'][$i]["email"];
			$events = $content_array['data'][$i]["event"];
				$sql_email = "SELECT id FROM marketing_base_emails WHERE email='$email' "; 
				$req_email = mysql_query($sql_email);
				$rows = mysql_num_rows($req_email);
				
				if($rows > 0){
					$data_email	= mysql_fetch_object($req_email);
					$id_email 	= $data_email->id; 		
				
				
				if($events == 'blocked'){
					$sql_update = "UPDATE `marketing_base_emails_operations` SET  `special_1` =  'Bloqué' WHERE  `id_email` ='$id_email' ";
					mysql_query($sql_update);
				}
				
				if($events == 'opened'){
					$sql_update = "UPDATE `marketing_base_emails_operations` SET  `special_2` =  'Ouvreurs' WHERE  `id_email` ='$id_email' ";
					mysql_query($sql_update);
				}
				
				if($events == 'click'){
					$sql_update = "UPDATE `marketing_base_emails_operations` SET  `special_3` =  'Cliqueurs' WHERE  `id_email` ='$id_email' ";
					mysql_query($sql_update);
				}
				
				if($events == 'views'){
					$sql_update = "UPDATE `marketing_base_emails_operations` SET  `special_4` =  'vues' WHERE  `id_email` ='$id_email' ";
					mysql_query($sql_update);
				}
				
				if($events == 'request'){
					$sql_update = "UPDATE `marketing_base_emails_operations` SET  `special_5` =  'Envoyé' WHERE  `id_email` ='$id_email' ";
					mysql_query($sql_update);
				}
				
				
				}
				
				
			
		}
		
		
?>
