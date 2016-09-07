<?php
	
	require_once('extranet_v3_functions.php'); 
	
	//Receive the informations
	$stats_interval_v1					= mysql_escape_string($_POST['stats_interval_v1_export']);
	$stats_interval_v2					= mysql_escape_string($_POST['stats_interval_v2_export']);
	$stats_simple_v1					= mysql_escape_string($_POST['stats_simple_v1_export']);
	$stats_products_search_hidden		= mysql_escape_string($_POST['stats_products_search_hidden_export']);
	$stat_searchtype					= mysql_escape_string($_POST['stat_searchtype_export']);
	
	//For the Query
	$stats_products_start_query			= '';
	$stats_products_end_query			= '';
	
	//For the title in the excel doc !
	$stats_products_start_excel_title	= '';
	$stats_products_end_excel_title		= '';
	
	//Var for Excel Row
	$local_loop_excel		= 0;
	
	if(!empty($_SESSION['extranet_user_id'])){
	
		if(!empty($stat_searchtype)){
	
	
			//Building The query 
			
			//Check if the export will be from a interval date or simple date "Month"
			if(strcmp($stat_searchtype,'interval')==0){
				//It's a interval !
				$stats_products_start_query		= strtotime(substr($stats_interval_v1,0,4).'/'.substr($stats_interval_v1,4,2).'/01 00:00:00');
			
				$stats_products_end_temp		= substr($stats_interval_v2,0,4).'/'.substr($stats_interval_v2,4,2).'/01';
				
				$stats_get_number_days_of_a_month	= cal_days_in_month(CAL_GREGORIAN, date('m',strtotime($stats_products_end_temp)), date('Y',strtotime($stats_products_end_temp)));
				
				$stats_products_end_query		= strtotime(substr($stats_interval_v2,0,4).'/'.substr($stats_interval_v2,4,2).'/'.$stats_get_number_days_of_a_month.' 23:59:59');
				
				//For Excel title !
				$stats_products_start_excel_title	= '01/'.substr($stats_interval_v1,4,2).'/'.substr($stats_interval_v1,0,4).' 00:00:00';
				$stats_products_end_excel_title		= $stats_get_number_days_of_a_month.'/'.substr($stats_interval_v2,4,2).'/'.substr($stats_interval_v2,0,4).' 23:59:59';
			
			}else{
				//It's simple
				$stats_products_start_query		= strtotime(substr($stats_simple_v1,0,4).'/'.substr($stats_simple_v1,4,2).'/01 00:00:00');
			
				$stats_products_end_temp		= substr($stats_simple_v1,0,4).'/'.substr($stats_simple_v1,4,2).'/01';
				
				$stats_get_number_days_of_a_month	= cal_days_in_month(CAL_GREGORIAN, date('m',strtotime($stats_products_end_temp)), date('Y',strtotime($stats_products_end_temp)));
				
				$stats_products_end_query		= strtotime(substr($stats_simple_v1,0,4).'/'.substr($stats_simple_v1,4,2).'/'.$stats_get_number_days_of_a_month.' 23:59:59');
			
			
				//For Excel title !
				$stats_products_start_excel_title	= '01/'.substr($stats_simple_v1,4,2).'/'.substr($stats_simple_v1,0,4).' 00:00:00';
				$stats_products_end_excel_title		= $stats_get_number_days_of_a_month.'/'.substr($stats_simple_v1,4,2).'/'.substr($stats_simple_v1,0,4).' 23:59:59';
			}
			
			
			if(!empty($stats_products_search_hidden)){
				$stats_condition_query	= " AND 
												pfr.id=".$stats_products_search_hidden." ";
			}else{
				$stats_condition_query	= " ";
			}
		
		
			//Execution of the Query
			
			$res_get_products_query	= "SELECT 
											pfr.id AS product_id,
											pfr.name AS product_name,
											pfr.fastdesc AS product_fastdesc,
											count(sh.idProduct) AS c

										FROM
											advertisers a,
											products_fr	pfr LEFT JOIN stats_hit sh ON sh.idProduct=pfr.id AND sh.timestamp	BETWEEN ".$stats_products_start_query." AND ".$stats_products_end_query." 
										WHERE
											a.id=".$_SESSION['extranet_user_id']."
										AND
											pfr.idAdvertiser=a.id
											
										".$stats_condition_query." 	

										GROUP BY pfr.id
										ORDER BY c DESC, pfr.name ASC
										
										";							
										
											
			$res_get_products = $db->query($res_get_products_query, __FILE__, __LINE__);
			
			if(mysql_num_rows($res_get_products)!=0){
			
				//Building the Excel file
				require_once("Spreadsheet/Excel/Writer.php");
				$workbook = new Spreadsheet_Excel_Writer();
				$workbook->send("Statistiques par produit ".date('d/m/Y h:i:s').".xls");
				
				//Writing the headers
				$worksheet = $workbook->addWorksheet('Statistiques par produit');
				
				
				$worksheet->write($local_loop_excel, 0, 'Nombre de vues par produits du '.$stats_products_start_excel_title.' au '.$stats_products_end_excel_title.' ');
				$local_loop_excel++;
				$local_loop_excel++;
				
				
				$worksheet->write($local_loop_excel, 0, 'Titre produit');
				$worksheet->write($local_loop_excel, 1, 'ID');
				$worksheet->write($local_loop_excel, 2, 'Description rapide');
				$worksheet->write($local_loop_excel, 3, 'Nb vues');
				$local_loop_excel++;
				
				while($content_get_products	= $db->fetchAssoc($res_get_products)){
			
					$worksheet->write($local_loop_excel, 0, utf8_decode($content_get_products['product_name']));
					$worksheet->write($local_loop_excel, 1, $content_get_products['product_id']);
					$worksheet->write($local_loop_excel, 2, utf8_decode($content_get_products['product_fastdesc']));
					$worksheet->write($local_loop_excel, 3, $content_get_products['c']);
					
					$local_loop_excel++;
				}//end while 
			
			
				//Sending Excel file !
				$workbook->close();
				
				
			}else{
				//Closing Window
				echo('<script type="text/javascript">');
					echo('self.close()');
				echo('</script>');
			}//end else if(mysql_num_rows($res_get_products)!=0)
			
			
			
			

		}//!empty($stat_searchtype)
	}else{
		header('Location: /login.html');
	}//end if session

?>