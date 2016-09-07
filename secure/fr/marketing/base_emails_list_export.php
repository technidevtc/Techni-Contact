<?php

	require_once('functions.php'); 

	if(!empty($_SESSION['marketing_user_id'])){ 
	
	//Execution of the Query
		$res_get_base_emails_query	= "SELECT
										m_base_emails.id, 
										m_base_emails.email,
										m_base_emails.etat, 
										m_base_emails.date_insert, 
										m_base_emails.date_last_edit, 
										m_base_emails.disable_source, 
										m_b_e_motifs.label 
									
									FROM 
										marketing_base_emails AS m_base_emails
											LEFT JOIN marketing_base_email_motifs AS m_b_e_motifs ON m_base_emails.motif=m_b_e_motifs.id 
											
								ORDER BY m_base_emails.id DESC ";

		$res_get_base_emails = $db->query($res_get_base_emails_query, __FILE__, __LINE__);
			
		if(mysql_num_rows($res_get_base_emails)!=0){
			
			//Var for Excel Row
			$local_loop_excel		= 0;
	
			//Building the Excel file
			require_once("Spreadsheet/Excel/Writer.php");
			$workbook = new Spreadsheet_Excel_Writer();
			$workbook->send("Listing Base Email ".date('d/m/Y h:i:s').".xls");
			
			//Writing the headers
			$worksheet = $workbook->addWorksheet("Base Email");
			$worksheet->write($local_loop_excel, 0, "ID ");
			$worksheet->write($local_loop_excel, 1, "Email");
			$worksheet->write($local_loop_excel, 2, "Etat");
			$worksheet->write($local_loop_excel, 3, "Date Insertion");
			$worksheet->write($local_loop_excel, 4, "Date dernière édition");
			$worksheet->write($local_loop_excel, 5, "Desactivée?");
			$worksheet->write($local_loop_excel, 6, "Motif");
			
			$local_loop_excel++;
			while($content_get_base_emails	= $db->fetchAssoc($res_get_base_emails)){
		
				$worksheet->write($local_loop_excel, 0, $content_get_base_emails['id']);
				$worksheet->write($local_loop_excel, 1, $content_get_base_emails['email']);
				
				if(strcmp($content_get_base_emails['etat'],"ok")==0){
					$worksheet->write($local_loop_excel, 2, "Activée");
				}else{
					$worksheet->write($local_loop_excel, 2, "Désabonnée");
				}
				
				if(strcmp($content_get_base_emails['date_insert'],'0000-00-00 00:00:00')==0){
					$worksheet->write($local_loop_excel, 3, " - ");
				}else{
					$worksheet->write($local_loop_excel, 3, date('d/m/Y H:i:s',strtotime($content_get_base_emails['date_insert'])));
				}
				
				if(strcmp($content_get_base_emails['date_last_edit'],'0000-00-00 00:00:00')==0){
					$worksheet->write($local_loop_excel, 4, " - ");
				}else{
					$worksheet->write($local_loop_excel, 4, date('d/m/Y H:i:s',strtotime($content_get_base_emails['date_last_edit'])));
				}
				
				
				if(strcmp($content_get_base_emails['disable_source'],"human")==0){
					$worksheet->write($local_loop_excel, 5, "Manuelle");
				}else{
					$worksheet->write($local_loop_excel, 5, "Programme");
				}
				
				if(empty($content_get_base_emails['label']) || strcmp($content_get_base_emails['label'],"NULL")==0){
					$worksheet->write($local_loop_excel, 6, " - ");
				}else{
					$worksheet->write($local_loop_excel, 6, $content_get_base_emails['label']);
				}
				
				$local_loop_excel++;
			}//end while 
		
			//Sending Excel file !
			$workbook->close();
			
			
			//Log the actions (Global Count, Count Add, Count Edit).
			$action_log	= "Export Base Email List \n ";
			
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