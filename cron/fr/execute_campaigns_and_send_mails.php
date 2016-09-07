<?php
	 
	require_once('execute_campaigns_and_send_mails_functions.php');
	require("../../../includes/fr/classV3/phpmailer_2014/PHPMailerAutoload.php");
	require("../../../includes/fr/classV3/phpmailer_2014/Email_functions_external_mail_send.php");

	
	try{
		
		//require_once('functions.php'); 
		require('mailin-api/mailin_webhook.php');
		$date_minute = date(i);
		//if($date_minute)
		$sql_verify_company_send_trigger = "SELECT id, name,
												TYPE , id_message, date_send, hour_send
												FROM marketing_campaigns
												WHERE
												TYPE = 'trigger'
												AND hour_send <= HOUR( NOW( ) )
												AND hour_send >  (HOUR( NOW( ) ) -1 ) 
												";
		$req_verify_company_send_trigger = mysql_query($sql_verify_company_send_trigger);
		
		
		$rows_trigger = mysql_num_rows($req_verify_company_send_trigger); 
		
		if($rows_trigger > 0){
			
			while($data_verify_company_send_trigger = mysql_fetch_object($req_verify_company_send_trigger)){
				
				//echo $data_verify_company_send_trigger->id_message.'<br /><br />';
				$send_mail->traitement_adhoc($data_verify_company_send_trigger->id_message,$data_verify_company_send_trigger->id);
					
					
					/*if($reult_check == 'no'){ //la platform est vérouillé 
						$result_send = $send_mail->send_mail_platforms_look($data_verify_company_send_trigger->id,$data_verify_company_send_trigger->name);
					}else if($reult_check == 'yes'){
						
					}*/
			}
		}
		
		$sql_verify_company_send_adhoc = "SELECT id, name,
											TYPE , id_message, date_send, hour_send
											FROM marketing_campaigns
											WHERE
											TYPE = 'adhoc'
											AND date_send = CURDATE( )
											AND hour_send <= HOUR( NOW( ) )
											AND hour_send > ( HOUR( NOW( ) ) -1 )  ";
		
		$req_verify_company_send_adhoc = mysql_query($sql_verify_company_send_adhoc);
		
		$rows_adhoc = mysql_num_rows($req_verify_company_send_adhoc);
		if($rows_adhoc > 0){
			while($data_verify_company_send_adhoc = mysql_fetch_object($req_verify_company_send_adhoc)){
				$reult_check = $send_mail->check_platforms_locked();
					if($reult_check == 'no'){ //la platform est vérouillé 
						$result_send = $send_mail->send_mail_platforms_look($data_verify_company_send_adhoc->id,$data_verify_company_send_adhoc->name);
					}else if($reult_check == 'yes'){
						$send_mail->traitement_adhoc($data_verify_company_send_adhoc->id_message,$data_verify_company_send_adhoc->id);
					}
			}
			
		}
		
	
	}catch(Exception $e){
		echo('Error '.date('Y/m/d H:i:s').' : '.$e);
	}
?>