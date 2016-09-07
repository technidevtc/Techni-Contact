<?php

	require_once('functions.php'); 

	if(!empty($_SESSION['marketing_user_id'])){ 
	
	//Execution of the Query
		$res_get_messages_query	= "SELECT 
										m_message.id, 
										m_message.date_creation, 
										m_message.date_last_edit,
										m_message.name AS message_name, 
										m_message.object, 
										
										m_segment.name AS segment_name, 
										m_segment.id_table, 

										m_campaigns.name AS campaigne_name	
										
									FROM
										marketing_messages m_message
											INNER JOIN marketing_segment AS m_segment	ON m_segment.id=m_message.id_segment
											LEFT JOIN marketing_campaigns AS m_campaigns	ON m_campaigns.id_message=m_message.id
											
								ORDER BY m_message.date_creation DESC ";

								

		$res_get_messages = $db->query($res_get_messages_query, __FILE__, __LINE__);
			
		if(mysql_num_rows($res_get_messages)!=0){
			
			//Var for Excel Row
			$local_loop_excel		= 0;
	
			//Building the Excel file
			require_once("Spreadsheet/Excel/Writer.php");
			$workbook = new Spreadsheet_Excel_Writer();
			$workbook->send("Listing messages ".date('d/m/Y h:i:s').".xls");
			
			//Writing the headers
			$worksheet = $workbook->addWorksheet('Messages');
			
			
			$worksheet->write($local_loop_excel, 0, 'ID ');
			$worksheet->write($local_loop_excel, 1, 'Date création');
			$worksheet->write($local_loop_excel, 2, 'Nom');
			$worksheet->write($local_loop_excel, 3, 'Objet');
			$worksheet->write($local_loop_excel, 4, 'Campagne');
			$worksheet->write($local_loop_excel, 5, 'Segment');
			$worksheet->write($local_loop_excel, 6, 'Date dernière modification');

			//$worksheet->write($local_loop_excel, 9, 'Champ de calcul');
			//$worksheet->write($local_loop_excel, 10, 'Résultat');			
			
			$local_loop_excel++;
			
			while($content_get_messages	= $db->fetchAssoc($res_get_messages)){
		
				$worksheet->write($local_loop_excel, 0, $content_get_messages['id']);
				$worksheet->write($local_loop_excel, 1, date('d/m/Y H:i:s',strtotime($content_get_messages['date_creation'])));
				$worksheet->write($local_loop_excel, 2, utf8_decode($content_get_messages['message_name']));
				$worksheet->write($local_loop_excel, 3, utf8_decode($content_get_messages['object']));
				
				if(!empty($content_get_messages['campaigne_name']) && strcmp($content_get_messages['campaigne_name'],"NULL")!=0){
					$worksheet->write($local_loop_excel, 4, utf8_decode($content_get_messages['campaigne_name']));
				}else{
					$worksheet->write($local_loop_excel, 4, ' - ');
				}
				
				$worksheet->write($local_loop_excel, 5, utf8_decode($content_get_messages['segment_name']));
				
				if(strcmp($content_get_messages['date_last_edit'],'0000-00-00 00:00:00')==0){
					$worksheet->write($local_loop_excel, 6, ' - ');
				}else{
					$worksheet->write($local_loop_excel, 6, date('d/m/Y H:i:s',strtotime($content_get_messages['date_last_edit'])));
				}				

				
				$local_loop_excel++;
			}//end while 
		
			//Sending Excel file !
			$workbook->close();
				
			//Log the actions (Global Count, Count Add, Count Edit).
			$action_log	= "Export Messages List \n ";
			
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