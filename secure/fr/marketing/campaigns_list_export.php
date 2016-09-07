<?php

	require_once('functions.php'); 

	if(!empty($_SESSION['marketing_user_id'])){ 
	
	//Execution of the Query
		$res_get_campaigns_query	= "SELECT
										m_campaigns.id, 
										m_campaigns.type, 
										m_campaigns.name AS c_name, 
										m_campaigns.date_creation, 
										m_campaigns.date_last_edit, 

										m_messages.name AS m_name, 

										m_segment.name AS s_name, 
										m_segment.id_table, 
										m_segment.results_count, 

										m_campaigns.date_last_sent, 
										m_campaigns.emails_brut, 
										m_campaigns.emails_sent, 
										m_campaigns.etat 
										
									FROM 
										marketing_campaigns AS m_campaigns 
											INNER JOIN marketing_messages m_messages ON m_campaigns.id_message=m_messages.id
											INNER JOIN marketing_segment AS m_segment	ON m_segment.id=m_messages.id_segment
											
								ORDER BY m_campaigns.id DESC ";

								

		$res_get_campaigns = $db->query($res_get_campaigns_query, __FILE__, __LINE__);
			
		if(mysql_num_rows($res_get_campaigns)!=0){
			
			//Var for Excel Row
			$local_loop_excel		= 0;
	
			//Building the Excel file
			require_once("Spreadsheet/Excel/Writer.php");
			$workbook = new Spreadsheet_Excel_Writer();
			$workbook->send("Listing campagnes ".date('d/m/Y h:i:s').".xls");
			
			//Writing the headers
			$worksheet = $workbook->addWorksheet('Campagnes');
			
			
			$worksheet->write($local_loop_excel, 0, 'ID ');
			$worksheet->write($local_loop_excel, 1, 'Type');
			$worksheet->write($local_loop_excel, 2, 'Nom');
			$worksheet->write($local_loop_excel, 3, 'Message');
			$worksheet->write($local_loop_excel, 4, 'Segment');
			$worksheet->write($local_loop_excel, 5, 'Date création');
			$worksheet->write($local_loop_excel, 6, 'Date dernière modification');
			$worksheet->write($local_loop_excel, 7, 'Date Envoi');
			$worksheet->write($local_loop_excel, 8, 'Emails Bruts');
			$worksheet->write($local_loop_excel, 9, 'Emails Envoyés');
			$worksheet->write($local_loop_excel, 10, 'Etat');
			
			
			$local_loop_excel++;
			
			while($content_get_campaigns	= $db->fetchAssoc($res_get_campaigns)){
		
				$worksheet->write($local_loop_excel, 0, $content_get_campaigns['id']);
				$worksheet->write($local_loop_excel, 1, $content_get_campaigns['type']);
				$worksheet->write($local_loop_excel, 2, utf8_decode($content_get_campaigns['c_name']));
				$worksheet->write($local_loop_excel, 3, utf8_decode($content_get_campaigns['m_name']));
				$worksheet->write($local_loop_excel, 4, utf8_decode($content_get_campaigns['s_name']));
				
				if(!empty($content_get_campaigns['date_creation']) && strcmp($content_get_campaigns['date_creation'],"NULL")!=0){
					$worksheet->write($local_loop_excel, 5, $content_get_campaigns['date_creation']);
				}else{
					$worksheet->write($local_loop_excel, 5, ' - ');
				}
				
				if(!empty($content_get_campaigns['date_last_edit']) && strcmp($content_get_campaigns['date_last_edit'],"NULL")!=0){
					$worksheet->write($local_loop_excel, 6, $content_get_campaigns['date_last_edit']);
				}else{
					$worksheet->write($local_loop_excel, 6, ' - ');
				}
				
				if(!empty($content_get_campaigns['date_last_sent']) && strcmp($content_get_campaigns['date_last_sent'],"NULL")!=0){
					$worksheet->write($local_loop_excel, 7, $content_get_campaigns['date_last_sent']);
				}else{
					$worksheet->write($local_loop_excel, 7, ' - ');
				}
				
				if(strcmp($content_get_campaigns['etat'],"Finalized")==0){
					$worksheet->write($local_loop_excel, 8, $content_get_campaigns['results_count']);
				}else{
					$worksheet->write($local_loop_excel, 8, $content_get_campaigns['emails_brut']);
				}
				
				
				$worksheet->write($local_loop_excel, 9, $content_get_campaigns['emails_sent']);
				
				
				
				switch($content_get_campaigns['etat']){
					case 'Saved':
						$campaign_etat_temp = "Enregistrée";
					break;
					
					case 'Programmed':
						$campaign_etat_temp = "Programmée";
					break;
					
					case 'Processing':
						$campaign_etat_temp = "En cours";
					break;
					
					case 'Finalized':
						$campaign_etat_temp = "Finalisée";
					break;
					
					default:
						$campaign_etat_temp = " - ";
					break;
				}				

				$worksheet->write($local_loop_excel, 10, $campaign_etat_temp);
				
				$local_loop_excel++;
			}//end while 
		
			//Sending Excel file !
			$workbook->close();
				
			//Log the actions (Global Count, Count Add, Count Edit).
			$action_log	= "Export Campagnes List \n ";
			
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