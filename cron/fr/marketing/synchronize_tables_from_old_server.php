<?php

	try {
		
		if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
			require_once '../../../config.php';
		}else{
			require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
		}
		
		$db = DBHandle::get_instance();
		
		//Steps
		//Block the executions on the platform (Segment refresh and Export) !
		//Detect if the file exist and is newer than the last synchronization !
		//1. Delete the Copy Tables
		//2. Copy the actual Tables
		//3. Delete the Prod tables
		//4. Import the new tables from the .gz File !
		//5. Insert Log !
		//6. Release the block of the executions on the platform (Segment refresh and Export) !
		
		//We use this Tables	
			/*clients contacts advertisers estimate_line estimate bo_users products_fr invoice order_line order products families families_fr products_families references_content*/
		
	
		$start_execution		= date('d/m/Y H:i:s');
		$start_execution_query	= date('Y-m-d H:i:s');
		
		$file_size_bytes		= filesize('/data/technico/backup_sql_marketing/technico.gz');
		//We will calculate the file size From Bytes to Kylobytes and Megabytes
		$file_size_mega_bytes	= (($file_size_bytes/1024)/1024);
		
echo('# Start Execution '.$start_execution.' \n\r ');
		
		$file_path			= "/data/technico/backup_sql_marketing/technico.gz";
		$file_last_change	= filemtime($file_path);
		$Database_last_sync	= "";

echo('# File Size '.$file_size_mega_bytes.'M \n\r ');
		
		//Get the last sync
		$query_get_last_sync	="SELECT 	
										MAX(UNIX_TIMESTAMP(date_last_synchronisation_start)) m
									FROM
										marketing_last_db_synchronisation 
									";						
									
		$res_last_sync 			= $db->query($query_get_last_sync, __FILE__, __LINE__);	
		$data_last_sync 		= $db->fetchAssoc($res_last_sync);
		$Database_last_sync		= $data_last_sync['m'];
	
	
		//Check if the file Exists and the last date change of the file and earlier than the last synchronization
		//And the file size must be higher than 308 MegaBytes !
		if(file_exists($file_path) && ($file_last_change>$Database_last_sync) && ($file_size_mega_bytes>308)){	
		
			//Block the execution on the platform !
			$query_block_executions	="UPDATE marketing_synchronization_flag SET sync_end='no' WHERE id=1 ";
			$res_block_executions	= $db->query($query_block_executions, __FILE__, __LINE__);
		
echo('* Block the executions OK ! \n\r ');		
		
			//Deleting the Copy
			/*$query_delete_copy_tables	="
										DROP TABLE IF EXISTS clients_copy; 
										DROP TABLE IF EXISTS contacts_copy; 
										DROP TABLE IF EXISTS advertisers_copy; 
										DROP TABLE IF EXISTS estimate_line_copy; 
										DROP TABLE IF EXISTS estimate_copy; 
										DROP TABLE IF EXISTS bo_users_copy; 
										DROP TABLE IF EXISTS products_fr_copy; 
										DROP TABLE IF EXISTS invoice_copy; 
										DROP TABLE IF EXISTS order_line_copy; 
										DROP TABLE IF EXISTS order_copy; 
										DROP TABLE IF EXISTS products_copy; 
										DROP TABLE IF EXISTS families_copy; 
										DROP TABLE IF EXISTS families_fr_copy; 
										DROP TABLE IF EXISTS products_families_copy; 
										DROP TABLE IF EXISTS references_content_copy; 									
											";						
										
			$res_delete_copy_tables 	= $db->query($query_delete_copy_tables, __FILE__, __LINE__);
			*/
			
			$query_delete_copy_table1	="DROP TABLE IF EXISTS clients_copy ";	
			$res_delete_copy_table1 	= $db->query($query_delete_copy_table1, __FILE__, __LINE__);
			
			$query_delete_copy_table2	="DROP TABLE IF EXISTS contacts_copy ";	
			$res_delete_copy_table2		= $db->query($query_delete_copy_table2, __FILE__, __LINE__);
			
			$query_delete_copy_table3	="DROP TABLE IF EXISTS advertisers_copy ";	
			$res_delete_copy_table3 	= $db->query($query_delete_copy_table3, __FILE__, __LINE__);
			
			$query_delete_copy_table4	="DROP TABLE IF EXISTS estimate_line_copy ";	
			$res_delete_copy_table4 	= $db->query($query_delete_copy_table4, __FILE__, __LINE__);
			
			$query_delete_copy_table5	="DROP TABLE IF EXISTS estimate_copy ";	
			$res_delete_copy_table5 	= $db->query($query_delete_copy_table5, __FILE__, __LINE__);
			
			$query_delete_copy_table6	="DROP TABLE IF EXISTS bo_users_copy ";	
			$res_delete_copy_table6 	= $db->query($query_delete_copy_table6, __FILE__, __LINE__);
			
			$query_delete_copy_table7	="DROP TABLE IF EXISTS products_fr_copy ";	
			$res_delete_copy_table7 	= $db->query($query_delete_copy_table7, __FILE__, __LINE__);
			
			$query_delete_copy_table8	="DROP TABLE IF EXISTS invoice_copy ";	
			$res_delete_copy_table8 	= $db->query($query_delete_copy_table8, __FILE__, __LINE__);
			
			$query_delete_copy_table9	="DROP TABLE IF EXISTS order_line_copy ";	
			$res_delete_copy_table9 	= $db->query($query_delete_copy_table9, __FILE__, __LINE__);
			
			$query_delete_copy_table10	="DROP TABLE IF EXISTS order_copy ";	
			$res_delete_copy_table10 	= $db->query($query_delete_copy_table10, __FILE__, __LINE__);
			
			$query_delete_copy_table11	="DROP TABLE IF EXISTS products_copy ";	
			$res_delete_copy_table11 	= $db->query($query_delete_copy_table11, __FILE__, __LINE__);
			
			$query_delete_copy_table12	="DROP TABLE IF EXISTS families_copy ";	
			$res_delete_copy_table12 	= $db->query($query_delete_copy_table12, __FILE__, __LINE__);
			
			$query_delete_copy_table13	="DROP TABLE IF EXISTS families_fr_copy ";	
			$res_delete_copy_table13 	= $db->query($query_delete_copy_table13, __FILE__, __LINE__);
			
			$query_delete_copy_table14	="DROP TABLE IF EXISTS products_families_copy ";	
			$res_delete_copy_table14 	= $db->query($query_delete_copy_table14, __FILE__, __LINE__);
			
			$query_delete_copy_table15	="DROP TABLE IF EXISTS references_content_copy ";	
			$res_delete_copy_table15 	= $db->query($query_delete_copy_table15, __FILE__, __LINE__);
			
			
echo('1. Delete OK ! \n\r ');
			
			//Make the copy of ours tables existing on the Database !
			
			/*$query_make_copy_tables1	="
											CREATE TABLE clients_copy LIKE clients; 
											INSERT clients_copy SELECT * FROM clients; 
											
											CREATE TABLE contacts_copy LIKE contacts; 
											INSERT contacts_copy SELECT * FROM contacts; 
											
											CREATE TABLE advertisers_copy LIKE advertisers; 
											INSERT advertisers_copy SELECT * FROM advertisers; 
											
											CREATE TABLE estimate_line_copy LIKE estimate_line; 
											INSERT estimate_line_copy SELECT * FROM estimate_line; 
											
											CREATE TABLE estimate_copy LIKE estimate; 
											INSERT estimate_copy SELECT * FROM estimate; 
											
											CREATE TABLE bo_users_copy LIKE bo_users; 
											INSERT bo_users_copy SELECT * FROM bo_users; 
											
											CREATE TABLE products_fr_copy LIKE products_fr; 
											INSERT products_fr_copy SELECT * FROM products_fr; 
											
											CREATE TABLE invoice_copy LIKE invoice; 
											INSERT invoice_copy SELECT * FROM invoice; 	
											";						
										
			$res_make_copy_tables1 	= $db->query($query_make_copy_tables1, __FILE__, __LINE__);
			
			$query_make_copy_tables2	="
											CREATE TABLE order_line_copy LIKE order_line; 
											INSERT order_line_copy SELECT * FROM order_line; 
											
											CREATE TABLE order_copy LIKE order; 
											INSERT order_copy SELECT * FROM order; 
											
											CREATE TABLE products_copy LIKE products; 
											INSERT products_copy SELECT * FROM products; 
											
											CREATE TABLE families_copy LIKE families; 
											INSERT families_copy SELECT * FROM families; 
											
											CREATE TABLE families_fr_copy LIKE families_fr; 
											INSERT families_fr_copy SELECT * FROM families_fr; 
											
											CREATE TABLE products_families_copy LIKE products_families; 
											INSERT products_families_copy SELECT * FROM products_families; 
											
											CREATE TABLE references_content_copy LIKE references_content; 
											INSERT references_content_copy SELECT * FROM references_content; 
												
											";						
										
			$res_make_copy_tables2 	= $db->query($query_make_copy_tables2, __FILE__, __LINE__);
			*/
		
			$query_create_copy_table1		="CREATE TABLE clients_copy LIKE clients ";	
			$res_create_copy_table1 		= $db->query($query_create_copy_table1, __FILE__, __LINE__);
			$query_insert_into_copy_table1	="INSERT clients_copy SELECT * FROM clients ";	
			$res_insert_into_copy_table1	= $db->query($query_insert_into_copy_table1, __FILE__, __LINE__);
			
			$query_create_copy_table2		="CREATE TABLE contacts_copy LIKE contacts ";	
			$res_create_copy_table2 		= $db->query($query_create_copy_table2, __FILE__, __LINE__);
			$query_insert_into_copy_table2	="INSERT contacts_copy SELECT * FROM contacts ";	
			$res_insert_into_copy_table2	= $db->query($query_insert_into_copy_table2, __FILE__, __LINE__);
			
			$query_create_copy_table3		="CREATE TABLE advertisers_copy LIKE advertisers ";	
			$res_create_copy_table3 		= $db->query($query_create_copy_table3, __FILE__, __LINE__);
			$query_insert_into_copy_table3	="INSERT advertisers_copy SELECT * FROM advertisers ";	
			$res_insert_into_copy_table3	= $db->query($query_insert_into_copy_table3, __FILE__, __LINE__);
			
			$query_create_copy_table4		="CREATE TABLE estimate_line_copy LIKE estimate_line ";	
			$res_create_copy_table4 		= $db->query($query_create_copy_table4, __FILE__, __LINE__);
			$query_insert_into_copy_table4	="INSERT estimate_line_copy SELECT * FROM estimate_line ";	
			$res_insert_into_copy_table4	= $db->query($query_insert_into_copy_table4, __FILE__, __LINE__);
			
			$query_create_copy_table5		="CREATE TABLE estimate_copy LIKE estimate ";	
			$res_create_copy_table5 		= $db->query($query_create_copy_table5, __FILE__, __LINE__);
			$query_insert_into_copy_table5	="INSERT estimate_copy SELECT * FROM estimate ";	
			$res_insert_into_copy_table5	= $db->query($query_insert_into_copy_table5, __FILE__, __LINE__);
			
			$query_create_copy_table6		="CREATE TABLE bo_users_copy LIKE bo_users ";	
			$res_create_copy_table6 		= $db->query($query_create_copy_table6, __FILE__, __LINE__);
			$query_insert_into_copy_table6	="INSERT bo_users_copy SELECT * FROM bo_users ";	
			$res_insert_into_copy_table6	= $db->query($query_insert_into_copy_table6, __FILE__, __LINE__);
			
			$query_create_copy_table7		="CREATE TABLE products_fr_copy LIKE products_fr ";	
			$res_create_copy_table7 		= $db->query($query_create_copy_table7, __FILE__, __LINE__);
			$query_insert_into_copy_table7	="INSERT products_fr_copy SELECT * FROM products_fr ";	
			$res_insert_into_copy_table7	= $db->query($query_insert_into_copy_table7, __FILE__, __LINE__);
			
			$query_create_copy_table8		="CREATE TABLE invoice_copy LIKE invoice ";	
			$res_create_copy_table8 		= $db->query($query_create_copy_table8, __FILE__, __LINE__);
			$query_insert_into_copy_table8	="INSERT invoice_copy SELECT * FROM invoice ";	
			$res_insert_into_copy_table8	= $db->query($query_insert_into_copy_table8, __FILE__, __LINE__);
			
			$query_create_copy_table9		="CREATE TABLE order_line_copy LIKE order_line ";	
			$res_create_copy_table9 		= $db->query($query_create_copy_table9, __FILE__, __LINE__);
			$query_insert_into_copy_table9	="INSERT order_line_copy SELECT * FROM order_line ";	
			$res_insert_into_copy_table9	= $db->query($query_insert_into_copy_table9, __FILE__, __LINE__);
			
			$query_create_copy_table10		="CREATE TABLE order_copy LIKE `order` ";	
			$res_create_copy_table10 		= $db->query($query_create_copy_table10, __FILE__, __LINE__);
			$query_insert_into_copy_table10	="INSERT order_copy SELECT * FROM `order` ";	
			$res_insert_into_copy_table10	= $db->query($query_insert_into_copy_table10, __FILE__, __LINE__);
			
			$query_create_copy_table11		="CREATE TABLE products_copy LIKE products ";	
			$res_create_copy_table11 		= $db->query($query_create_copy_table11, __FILE__, __LINE__);
			$query_insert_into_copy_table11	="INSERT products_copy SELECT * FROM products ";	
			$res_insert_into_copy_table11	= $db->query($query_insert_into_copy_table11, __FILE__, __LINE__);
			
			$query_create_copy_table12		="CREATE TABLE families_copy LIKE families ";	
			$res_create_copy_table12 		= $db->query($query_create_copy_table12, __FILE__, __LINE__);
			$query_insert_into_copy_table12	="INSERT families_copy SELECT * FROM families ";	
			$res_insert_into_copy_table12	= $db->query($query_insert_into_copy_table12, __FILE__, __LINE__);
			
			$query_create_copy_table13		="CREATE TABLE families_fr_copy LIKE families_fr ";	
			$res_create_copy_table13 		= $db->query($query_create_copy_table13, __FILE__, __LINE__);
			$query_insert_into_copy_table13	="INSERT families_fr_copy SELECT * FROM families_fr ";	
			$res_insert_into_copy_table13	= $db->query($query_insert_into_copy_table13, __FILE__, __LINE__);
			
			$query_create_copy_table14		="CREATE TABLE products_families_copy LIKE products_families ";	
			$res_create_copy_table14 		= $db->query($query_create_copy_table14, __FILE__, __LINE__);
			$query_insert_into_copy_table14	="INSERT products_families_copy SELECT * FROM products_families ";	
			$res_insert_into_copy_table14	= $db->query($query_insert_into_copy_table14, __FILE__, __LINE__);
			
			$query_create_copy_table15		="CREATE TABLE references_content_copy LIKE references_content ";	
			$res_create_copy_table15 		= $db->query($query_create_copy_table15, __FILE__, __LINE__);
			$query_insert_into_copy_table15	="INSERT references_content_copy SELECT * FROM references_content ";	
			$res_insert_into_copy_table15	= $db->query($query_insert_into_copy_table15, __FILE__, __LINE__);


			
echo('2. Create Copy  OK ! \n\r ');
		

			//Deleting the Prod Tables
			/*$query_delete_prod_tables	="
										DROP TABLE IF EXISTS clients; 
										DROP TABLE IF EXISTS contacts; 
										DROP TABLE IF EXISTS advertisers; 
										DROP TABLE IF EXISTS estimate_line; 
										DROP TABLE IF EXISTS estimate; 
										DROP TABLE IF EXISTS bo_users; 
										DROP TABLE IF EXISTS products_fr; 
										DROP TABLE IF EXISTS invoice; 
										DROP TABLE IF EXISTS order_line; 
										DROP TABLE IF EXISTS order; 
										DROP TABLE IF EXISTS products; 
										DROP TABLE IF EXISTS families; 
										DROP TABLE IF EXISTS families_fr; 
										DROP TABLE IF EXISTS products_families; 
										DROP TABLE IF EXISTS references_content; 									
											";						
			$res_delete_prod_tables 	= $db->query($query_delete_prod_tables, __FILE__, __LINE__);
			*/
		
			$query_delete_prod_table1	="DROP TABLE IF EXISTS clients ";						
			$res_delete_prod_table1 	= $db->query($query_delete_prod_table1, __FILE__, __LINE__);
			
			$query_delete_prod_table2	="DROP TABLE IF EXISTS contacts ";						
			$res_delete_prod_table2 	= $db->query($query_delete_prod_table2, __FILE__, __LINE__);
			
			$query_delete_prod_table3	="DROP TABLE IF EXISTS advertisers ";						
			$res_delete_prod_table3 	= $db->query($query_delete_prod_table3, __FILE__, __LINE__);
			
			$query_delete_prod_table4	="DROP TABLE IF EXISTS estimate_line ";						
			$res_delete_prod_table4 	= $db->query($query_delete_prod_table4, __FILE__, __LINE__);
			
			$query_delete_prod_table5	="DROP TABLE IF EXISTS estimate ";						
			$res_delete_prod_table5 	= $db->query($query_delete_prod_table5, __FILE__, __LINE__);
			
			$query_delete_prod_table6	="DROP TABLE IF EXISTS bo_users ";						
			$res_delete_prod_table6 	= $db->query($query_delete_prod_table6, __FILE__, __LINE__);
			
			$query_delete_prod_table7	="DROP TABLE IF EXISTS products_fr ";						
			$res_delete_prod_table7 	= $db->query($query_delete_prod_table7, __FILE__, __LINE__);
			
			$query_delete_prod_table8	="DROP TABLE IF EXISTS invoice ";						
			$res_delete_prod_table8 	= $db->query($query_delete_prod_table8, __FILE__, __LINE__);
			
			$query_delete_prod_table9	="DROP TABLE IF EXISTS order_line ";						
			$res_delete_prod_table9 	= $db->query($query_delete_prod_table9, __FILE__, __LINE__);
			
			$query_delete_prod_table10	="DROP TABLE IF EXISTS `order` ";						
			$res_delete_prod_table10 	= $db->query($query_delete_prod_table10, __FILE__, __LINE__);
			
			$query_delete_prod_table11	="DROP TABLE IF EXISTS products ";						
			$res_delete_prod_table11 	= $db->query($query_delete_prod_table11, __FILE__, __LINE__);
			
			$query_delete_prod_table12	="DROP TABLE IF EXISTS families ";						
			$res_delete_prod_table12 	= $db->query($query_delete_prod_table12, __FILE__, __LINE__);
			
			$query_delete_prod_table13	="DROP TABLE IF EXISTS families_fr ";						
			$res_delete_prod_table13 	= $db->query($query_delete_prod_table13, __FILE__, __LINE__);
			
			$query_delete_prod_table14	="DROP TABLE IF EXISTS products_families ";						
			$res_delete_prod_table14 	= $db->query($query_delete_prod_table14, __FILE__, __LINE__);
			
			$query_delete_prod_table15	="DROP TABLE IF EXISTS references_content ";						
			$res_delete_prod_table15 	= $db->query($query_delete_prod_table15, __FILE__, __LINE__);

			
echo('3. Delete Prod Tables OK ! \n\r ');
			
			//Import the New Tables !
			$user		= "technico";
			$password	= "os2GL72yOF6wBl6m";
			exec("gunzip < ".$file_path." | mysql -h localhost -u ".$user." -p".$password." technico ");
			
echo('4. Import new Tables OK ! \n\r ');
			
			//Insert Log !
			$query_insert_log	=" INSERT INTO marketing_last_db_synchronisation
											(id, date_last_synchronisation_start, 
											date_last_synchronisation_end, tables_affected) 
										VALUES
											(NULL, '".$start_execution_query."',
											NOW(), 'clients contacts advertisers estimate_line estimate bo_users products_fr invoice order_line `order` products families families_fr products_families references_content')
								";						
										
			$res_insert_log 	= $db->query($query_insert_log, __FILE__, __LINE__);

echo('5. Insert Log OK ! \n\r ');

			//Block the execution on the platform !
			$query_release_executions	="UPDATE marketing_synchronization_flag SET sync_end='yes' WHERE id=1 ";
			$res_release_executions		= $db->query($query_release_executions, __FILE__, __LINE__);

echo('6. Release the executions OK ! \n\r ');			
			
			
		}//End if test
	
	} catch (Exception $e) {
		echo('Time '.date('d/m/Y H:i:s').' **Error: '.$e);
	}
?>