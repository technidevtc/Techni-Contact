<?php
	
	require_once('extranet_v3_functions.php'); 
	
	$stats_h_month			= mysql_escape_string($_POST['stats_h_month']);
	$stats_h_data			= mysql_escape_string($_POST['stats_h_data']);
	$stats_h_values			= mysql_escape_string($_POST['stats_h_values']);
	
	$local_loop_excel		= 0;
	$local_loop_while		= 0;
	
	if(!empty($_SESSION['extranet_user_id'])){
	
		if(!empty($stats_h_data) && !empty($stats_h_values)){
	
	
			require_once("Spreadsheet/Excel/Writer.php");
			$workbook = new Spreadsheet_Excel_Writer();
			$workbook->send("Statistiques globales ".date('d/m/Y h:i:s').".xls");

			$worksheet = $workbook->addWorksheet('Statistiques globales');
			
			$worksheet->write($local_loop_excel, 0, utf8_decode('Période'));
			$worksheet->write($local_loop_excel, 1, 'Nombre de vues');
			$local_loop_excel++;
			
			if(!empty($stats_h_month)){
				$worksheet->write($local_loop_excel, 0, ucfirst(utf8_decode($stats_h_month)));
				$local_loop_excel++;
			}
			
			
			//echo('<br />'.$stats_h_month);
			//echo('<br />'.$stats_h_data);
			//echo('<br />'.$stats_h_values);
			
			//Removing "[" and "]" from the Begin and the End
			$stats_h_data	= substr($stats_h_data, 1, -1);
			$stats_h_values	= substr($stats_h_values, 1, -1);
			
			
			//Separating the values
			$stats_h_data_array 	= explode(',',$stats_h_data);
			$stats_h_data_values 	= explode(',',$stats_h_values);
			
			
			$local_loop_while		= 0;
			while(!empty($stats_h_data_array[$local_loop_while])){
			
				//removing anti-slashes
				$stats_h_data_array[$local_loop_while]	= stripslashes($stats_h_data_array[$local_loop_while]);
				//Removing Quotes 
				$stats_h_data_array[$local_loop_while]	= str_replace('"','',$stats_h_data_array[$local_loop_while]);
				
				/*echo(utf8_decode($stats_h_data_array[$local_loop_while]));
				echo(' => ');
				echo($stats_h_data_values[$local_loop_while]);
				echo('<br />');*/
				
				$worksheet->write($local_loop_excel, 0, utf8_decode($stats_h_data_array[$local_loop_while]));
				$worksheet->write($local_loop_excel, 1, $stats_h_data_values[$local_loop_while]);
				
				
				$local_loop_excel++;
				$local_loop_while++;
			}
			
			
			// Let's send the file
			$workbook->close();


		}//!empty($stats_interval_v1) && !empty($stats_interval_v2)
	}else{
		header('Location: /login.html');
	}//end if session

?>
