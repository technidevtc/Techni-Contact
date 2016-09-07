<?php

	require_once('functions.php'); 

	if(!empty($_SESSION['marketing_user_id'])){ 
	
	//Execution of the Query
		$res_get_segments_query	= "SELECT 
										m_seg.id,
										m_seg.name,
										m_seg.type,
										m_seg.results_count,
										m_seg.date_creation,
										m_seg.date_change,
										m_seg.date_last_execution_start,
										m_seg.date_last_execution_end,
										
										m_tables.id AS table_id,
										m_tables.name_fo AS table_name	
										
									FROM
										marketing_segment m_seg
											INNER JOIN marketing_tables AS m_tables ON m_seg.id_table=m_tables.id
											
								ORDER BY m_seg.date_creation DESC ";

/*
	m_t_f_calculate.name_fo,	
	m_t_f_cresults.results
	...
	LEFT JOIN 
	marketing_tables_fields_calculate AS m_t_f_calculate ON 
			m_tables.id=m_t_f_calculate.id_table
			
	
		LEFT JOIN marketing_tables_fields_calculate_results AS m_t_f_cresults ON m_t_f_calculate.id=m_t_f_cresults.id_field_calculate
*/

								
										
											
		$res_get_segments = $db->query($res_get_segments_query, __FILE__, __LINE__);
			
		if(mysql_num_rows($res_get_segments)!=0){
			
			//Var for Excel Row
			$local_loop_excel		= 0;
	
			//Building the Excel file
			require_once("Spreadsheet/Excel/Writer.php");
			$workbook = new Spreadsheet_Excel_Writer();
			$workbook->send("Listing segments ".date('d/m/Y h:i:s').".xls");
			
			//Writing the headers
			$worksheet = $workbook->addWorksheet('Segments');
			
			
			$worksheet->write($local_loop_excel, 0, 'ID ');
			$worksheet->write($local_loop_excel, 1, 'Nom');
			$worksheet->write($local_loop_excel, 2, 'Type');
			$worksheet->write($local_loop_excel, 3, 'Résultat');
			$worksheet->write($local_loop_excel, 4, 'Date création');
			$worksheet->write($local_loop_excel, 5, 'Date dernière modification');
			$worksheet->write($local_loop_excel, 6, 'Date dernière exécution (Start)');
			$worksheet->write($local_loop_excel, 7, 'Date dernière exécution (End)');
			$worksheet->write($local_loop_excel, 8, 'Table(Source)');
			//$worksheet->write($local_loop_excel, 9, 'Champ de calcul');
			//$worksheet->write($local_loop_excel, 10, 'Résultat');			
			
			$local_loop_excel++;
			
			while($content_get_segments	= $db->fetchAssoc($res_get_segments)){
		
				$worksheet->write($local_loop_excel, 0, $content_get_segments['id']);
				$worksheet->write($local_loop_excel, 1, $content_get_segments['name']);
				$worksheet->write($local_loop_excel, 2, ucfirst($content_get_segments['type']));
				$worksheet->write($local_loop_excel, 3, $content_get_segments['results_count']);
				$worksheet->write($local_loop_excel, 4, date('d/m/Y H:i:s',strtotime($content_get_segments['date_creation'])));
				
				if(strcmp($content_get_segments['date_change'],'0000-00-00 00:00:00')==0){
					$worksheet->write($local_loop_excel, 5, ' - ');
				}else{
					$worksheet->write($local_loop_excel, 5, date('d/m/Y H:i:s',strtotime($content_get_segments['date_change'])));
				}
				
				if(strcmp($content_get_segments['date_last_execution_start'],'0000-00-00 00:00:00')==0){
					$worksheet->write($local_loop_excel, 6, ' - ');
				}else{
					$worksheet->write($local_loop_excel, 6, date('d/m/Y H:i:s',strtotime($content_get_segments['date_last_execution_start'])));
				}
				
				if(strcmp($content_get_segments['date_last_execution_end'],'0000-00-00 00:00:00')==0){
					$worksheet->write($local_loop_excel, 7, ' - ');
				}else{
					$worksheet->write($local_loop_excel, 7, date('d/m/Y H:i:s',strtotime($content_get_segments['date_last_execution_end'])));
				}
				
				$worksheet->write($local_loop_excel, 8, $content_get_segments['table_name']);
				
				/*if(strcmp($content_get_segments['name_fo'],'NULL')==0){
					$worksheet->write($local_loop_excel, 8, ' - ');
				}else{
					$worksheet->write($local_loop_excel, 8, $content_get_segments['name_fo']);
				}
				
				if(strcmp($content_get_segments['results'],'NULL')==0){
					$worksheet->write($local_loop_excel, 9, ' - ');
				}else{
					$worksheet->write($local_loop_excel, 9, $content_get_segments['results']);
				}*/
				
				$local_loop_excel++;
			}//end while 
		
			//Sending Excel file !
			$workbook->close();
			
			
			//Log the actions (Global Count, Count Add, Count Edit).
			$action_log	= "Export Segments List \n ";
			
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