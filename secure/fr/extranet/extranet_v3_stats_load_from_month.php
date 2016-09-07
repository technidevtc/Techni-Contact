<?php
	
	require_once('extranet_v3_functions.php'); 
	
	$stats_simple_v1		= mysql_escape_string($_POST['stats_simple_v1']);
	
	
	if(!empty($_SESSION['extranet_user_id'])){
	
		if(!empty($stats_simple_v1)){

			//Message entete to show
			$stats_generated_header	= '';
			
			//Compteur global statistiques
			$stats_global_count		= '0';
			
			//vars for the Javascript Bar
			$bar_data_days			= '[';
			$bar_data_values		= '[';

			
			//The user had selected one month
			//Processing with days (From the first to last second of every day !!)
			//Here we know that the user had select one month so we gonna
			//Use a external file (to use it twice for interval and for one month other call)
			
			//declaration global variables for the external file
			$stat_day_start		= substr($stats_simple_v1,0,4).'/'.substr($stats_simple_v1,4,2).'/01';
			
			$stats_get_number_days_of_a_month	= cal_days_in_month(CAL_GREGORIAN, date('m',strtotime($stat_day_start)), date('Y',strtotime($stat_day_start)));
			$stat_day_end		= substr($stats_simple_v1,0,4).'/'.substr($stats_simple_v1,4,2).'/'.$stats_get_number_days_of_a_month;
			
			require_once('extranet_v3_stats_load_from_month_external_file.php');
			

		}//!empty($stats_interval_v1) && !empty($stats_interval_v2)
	}else{
		header('Location: /login.html');
	}//end else if session//end if session

?>