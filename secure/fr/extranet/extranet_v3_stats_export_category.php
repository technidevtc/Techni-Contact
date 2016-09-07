<?php
	
	require_once('extranet_v3_functions.php'); 
	
	//Receive the informations
	$stats_interval_v1				= mysql_escape_string($_POST['stats_interval_v1_export']);
	$stats_interval_v2				= mysql_escape_string($_POST['stats_interval_v2_export']);
	$stats_simple_v1				= mysql_escape_string($_POST['stats_simple_v1_export']);
	$stat_searchtype				= mysql_escape_string($_POST['stat_searchtype_export']);
	
	//For the Query
	$stats_category_start_query		= '';
	$stats_category_end_query		= '';
	
	//Var for Excel Row
	$local_loop_excel		= 0;
	
	
	//For the title in the excel doc !
	$stats_products_start_excel_title	= '';
	$stats_products_end_excel_title		= '';
	
	
	if(!empty($_SESSION['extranet_user_id'])){
	
		if(!empty($stat_searchtype)){
	
	
			//Building The query 
			
			//Check if the export will be from a interval date or simple date "Month"
			if(strcmp($stat_searchtype,'interval')==0){
				//It's a interval !
				$stats_category_start_query		= strtotime(substr($stats_interval_v1,0,4).'/'.substr($stats_interval_v1,4,2).'/01 00:00:00');
			
				$stats_category_end_temp		= substr($stats_interval_v2,0,4).'/'.substr($stats_interval_v2,4,2).'/01';
				
				$stats_get_number_days_of_a_month	= cal_days_in_month(CAL_GREGORIAN, date('m',strtotime($stats_category_end_temp)), date('Y',strtotime($stats_category_end_temp)));
				
				$stats_category_end_query		= strtotime(substr($stats_interval_v2,0,4).'/'.substr($stats_interval_v2,4,2).'/'.$stats_get_number_days_of_a_month.' 23:59:59');
			
			
				//For Excel title !
				$stats_products_start_excel_title	= '01/'.substr($stats_interval_v1,4,2).'/'.substr($stats_interval_v1,0,4).' 00:00:00';
				$stats_products_end_excel_title		= $stats_get_number_days_of_a_month.'/'.substr($stats_interval_v2,4,2).'/'.substr($stats_interval_v2,0,4).' 23:59:59';
				
			}else{
				//It's simple
				$stats_category_start_query		= strtotime(substr($stats_simple_v1,0,4).'/'.substr($stats_simple_v1,4,2).'/01 00:00:00');
			
				$stats_category_end_temp		= substr($stats_simple_v1,0,4).'/'.substr($stats_simple_v1,4,2).'/01';
				
				$stats_get_number_days_of_a_month	= cal_days_in_month(CAL_GREGORIAN, date('m',strtotime($stats_category_end_temp)), date('Y',strtotime($stats_category_end_temp)));
				
				$stats_category_end_query		= strtotime(substr($stats_simple_v1,0,4).'/'.substr($stats_simple_v1,4,2).'/'.$stats_get_number_days_of_a_month.' 23:59:59');
			
			
				//For Excel title !
				$stats_products_start_excel_title	= '01/'.substr($stats_simple_v1,4,2).'/'.substr($stats_simple_v1,0,4).' 00:00:00';
				$stats_products_end_excel_title		= $stats_get_number_days_of_a_month.'/'.substr($stats_simple_v1,4,2).'/'.substr($stats_simple_v1,0,4).' 23:59:59';
			}
			
		
			//Execution of the Query
			
			$res_get_category_query	= "SELECT 
											ffr.id AS families_id,
											ffr.name AS families_name,
											count(sh.idFamily) AS c
										FROM
											advertisers a,
											products p,
											products_families pfam,
											families_fr	ffr
											LEFT JOIN stats_hit sh ON sh.idFamily=ffr.id AND sh.timestamp	BETWEEN ".$stats_category_start_query." AND ".$stats_category_end_query." AND sh.idAdvertiser=".$_SESSION['extranet_user_id']." 

										WHERE
											a.id=".$_SESSION['extranet_user_id']."
										AND
											p.idAdvertiser=a.id		
										AND
											p.id=pfam.idProduct
										AND
											pfam.idFamily=ffr.id
										AND
											pfam.orderFamily<2
											
										GROUP BY ffr.id
										order by c DESC, ffr.id  ASC	
										
										";							
										
											
			$res_get_category = $db->query($res_get_category_query, __FILE__, __LINE__);
			
			if(mysql_num_rows($res_get_category)!=0){
			
				//Building the Excel file
				require_once("Spreadsheet/Excel/Writer.php");
				$workbook = new Spreadsheet_Excel_Writer();
				$workbook->send("Statistiques par categorie ".date('d/m/Y h:i:s').".xls");
				
				//Writing the headers
				$worksheet = $workbook->addWorksheet('Statistiques par categorie');
				
				
				$worksheet->write($local_loop_excel, 0, 'Nb de vues par catégorie du '.$stats_products_start_excel_title.' au '.$stats_products_end_excel_title.' ');
				$local_loop_excel++;
				$local_loop_excel++;
				
				
				$worksheet->write($local_loop_excel, 0, 'Nom catégorie');
				$worksheet->write($local_loop_excel, 1, 'Nb visites');
				$local_loop_excel++;
				
				while($content_get_category	= $db->fetchAssoc($res_get_category)){
			
					$worksheet->write($local_loop_excel, 0, utf8_decode($content_get_category['families_name']));
					$worksheet->write($local_loop_excel, 1, $content_get_category['c']);
					
					$local_loop_excel++;
				}//end while 
			
			
				//Sending Excel file !
				$workbook->close();
				
				
			}else{
				//Closing Window
				echo('<script type="text/javascript">');
					echo('self.close()');
				echo('</script>');
			}//end else if(mysql_num_rows($res_get_category)!=0)
			

		}//!empty($stat_searchtype)
	}else{
		header('Location: /login.html');
	}//end if session

?>