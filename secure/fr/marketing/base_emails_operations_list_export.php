<?php

	require_once('functions.php'); 
	
	$email_id					= mysql_escape_string($_POST['email_id']);
	
	if(!empty($_SESSION['marketing_user_id']) && !empty($email_id)){ 
	
	//Execution of the Query
		$res_get_base_emails_query	= "SELECT 
											m_b_e_operations.id, 
											m_b_e_operations.date_insert, 
											m_b_e_operations.email_etat, 

											m_campaigns.name AS c_name, 
											m_messages.name AS m_name, 
											m_segment.name	AS s_name 
											
										FROM
											marketing_base_emails_operations AS m_b_e_operations 
												INNER JOIN marketing_base_emails AS m_base_emails ON m_b_e_operations.id_email=m_base_emails.id
												LEFT JOIN marketing_campaigns AS m_campaigns ON m_b_e_operations.id_campaign=m_campaigns.id 
												LEFT JOIN marketing_messages AS m_messages ON m_messages.id=m_campaigns.id_message
												LEFT JOIN marketing_segment AS m_segment ON m_messages.id_segment=m_segment.id 

										WHERE 
											m_base_emails.id=".$email_id." 
											
										ORDER BY m_b_e_operations.id DESC ";

		$res_get_base_emails = $db->query($res_get_base_emails_query, __FILE__, __LINE__);
			
		if(mysql_num_rows($res_get_base_emails)!=0){
			
			//Var for Excel Row
			$local_loop_excel		= 0;
	
			//Building the Excel file
			require_once("Spreadsheet/Excel/Writer.php");
			$workbook = new Spreadsheet_Excel_Writer();
			$workbook->send("Listing Base Email Operations ".date('d/m/Y h:i:s').".xls");
			
			//Writing the headers
			$worksheet = $workbook->addWorksheet("Base Email");
			$worksheet->write($local_loop_excel, 0, "ID ");
			$worksheet->write($local_loop_excel, 1, "Date d'envoi");
			$worksheet->write($local_loop_excel, 2, "Campagne");
			$worksheet->write($local_loop_excel, 3, "Message");
			$worksheet->write($local_loop_excel, 4, "Segment");
			$worksheet->write($local_loop_excel, 5, "Filtrée?");
			
			$local_loop_excel++;
			while($content_get_base_emails	= $db->fetchAssoc($res_get_base_emails)){
		
				$worksheet->write($local_loop_excel, 0, $content_get_base_emails['id']);
				
				if(strcmp($content_get_base_emails['date_insert'],'0000-00-00 00:00:00')==0){
					$worksheet->write($local_loop_excel, 1, " - ");
				}else{
					$worksheet->write($local_loop_excel, 1, date('d/m/Y H:i:s',strtotime($content_get_base_emails['date_insert'])));
				}
				
				$worksheet->write($local_loop_excel, 1, $content_get_base_emails['email']);
				
				
				if(empty($content_get_base_emails['c_name'])){
					$worksheet->write($local_loop_excel, 2, " - ");
				}else{
					$worksheet->write($local_loop_excel, 2, $content_get_base_emails['c_name']);
				}
				
				if(empty($content_get_base_emails['m_name'])){
					$worksheet->write($local_loop_excel, 3, " - ");
				}else{
					$worksheet->write($local_loop_excel, 3, $content_get_base_emails['m_name']);
				}
				
				if(empty($content_get_base_emails['s_name'])){
					$worksheet->write($local_loop_excel, 4, " - ");
				}else{
					$worksheet->write($local_loop_excel, 4, $content_get_base_emails['s_name']);
				}
				
				
				if(strcmp($content_get_base_emails['email_etat'],"ok")==0){
					$worksheet->write($local_loop_excel, 5, "Non");
				}else{
					$worksheet->write($local_loop_excel, 5, "Oui");
				}
				
				$local_loop_excel++;
			}//end while 
		
			//Sending Excel file !
			$workbook->close();
			
			
			//Log the actions (Global Count, Count Add, Count Edit).
			$action_log	= "Export Base Email Operations List \n ";
			
			$query_marketing_log	="INSERT INTO marketing_users_history(id, action,
											id_user, action_date)
										VALUES(NULL, '".$action_log."',
											".$_SESSION['marketing_user_id'].", NOW())";
						
			$res_marketing_log 	= $db->query($query_marketing_log, __FILE__, __LINE__);
				
		}else{
			//Closing Window
			echo('<script type="text/javascript">');
				echo('self.close()');
			echo('</script>');
		}//end else if(mysql_num_rows($res_get_category)!=0)	

	}else{
		header('Location: /fr/marketing/login.php');
	}//end if session

?>