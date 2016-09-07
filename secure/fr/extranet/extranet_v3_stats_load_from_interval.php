<?php
	
	require_once('extranet_v3_functions.php'); 
	
	$stats_interval_v1		= mysql_escape_string($_POST['stats_interval_v1']);
	$stats_interval_v2		= mysql_escape_string($_POST['stats_interval_v2']);
	
	
	if(!empty($_SESSION['extranet_user_id'])){
	
		if(!empty($stats_interval_v1) && !empty($stats_interval_v2)){

			//Message entete to show
			$stats_generated_header	= '';
			
			//Compteur global statistiques
			$stats_global_count		= '0';
			
			//vars for the Javascript Bar
			$bar_data_months		= '[';
			$bar_data_days			= '[';
			$bar_data_values		= '[';
			
			
			if($stats_interval_v1==$stats_interval_v2){
				//The user had selected the same month on the two select
				//Processing with days (From the first to last second of every day !!)
				//Here we know that the user had select one month so we gonna
				//Use a external file (to use it twice for interval and for one month other call)
				
				//declaration global variables for the external file
				$stat_day_start		= substr($stats_interval_v1,0,4).'/'.substr($stats_interval_v1,4,2).'/01';
				
				$stats_get_number_days_of_a_month	= cal_days_in_month(CAL_GREGORIAN, date('m',strtotime($stat_day_start)), date('Y',strtotime($stat_day_start)));
				$stat_day_end		= substr($stats_interval_v2,0,4).'/'.substr($stats_interval_v2,4,2).'/'.$stats_get_number_days_of_a_month;
				
				require_once('extranet_v3_stats_load_from_month_external_file.php');
				
			}else{
				//Processing with months (From the first to last day for every month)
				
				echo(' var echo_print = "";');
			
				//Converting received params
				//Generating the first date (for boucle while)
				$stats_interval_v1_calcul		= substr($stats_interval_v1,0,4).'/'.substr($stats_interval_v1,4,2).'/01';
				
				//Generating the second date (for boucle while)
				$stats_interval_v2_calcul		= substr($stats_interval_v2,0,4).'/'.substr($stats_interval_v2,4,2).'/01';
				
				//Vars for the "While" Loop
				$stats_timestamp_start_while	= strtotime($stats_interval_v1_calcul);
				$stats_timestamp_end_while		= strtotime($stats_interval_v2_calcul);
				
				while($stats_timestamp_start_while<=$stats_timestamp_end_while){
				
					//Creating a dynamic interval 
					//Ex: for every month between 2014/01/01 00:00:00 To 2014/01/31 23:59:59
					//And we repeat it while we reach the end month "Second select list"
					$stats_timestamp_start_query		= date('Y/m/d',$stats_timestamp_start_while).' 00:00:00';
					
					$stats_get_number_days_of_a_month	= cal_days_in_month(CAL_GREGORIAN, date('m',$stats_timestamp_start_while), date('Y',$stats_timestamp_start_while));
					
					$stats_timestamp_end_query			= date('Y/m',$stats_timestamp_start_while).'/'.$stats_get_number_days_of_a_month.' 23:59:59';
					

					
					
					
					$query_month	= "SELECT 
											count(sh.idProduct) c 
										FROM  
											`stats_hit` sh
										WHERE  
											sh.idAdvertiser=".$_SESSION['extranet_user_id']."
										AND 
											EXISTS(
												SELECT
													p.id
												FROM
													products p
												WHERE
													p.idAdvertiser=".$_SESSION['extranet_user_id']."
											)
										AND 
											sh.TIMESTAMP BETWEEN ".strtotime($stats_timestamp_start_query)." AND ".strtotime($stats_timestamp_end_query)." ";
					
					$res_query_month 			= $db->query($query_month, __FILE__, __LINE__);
							
					$content_get_query_month	= $db->fetchAssoc($res_query_month);
						
					
					//For the header message
					$stats_global_count			+= $content_get_query_month['c'];
					
					//For the Javascript Bar
					//Months
					$bar_data_months_temp1	= date('F', strtotime($stats_timestamp_start_query));
					$bar_data_months_temp2	= str_ireplace($months_en, $months_fr, $bar_data_months_temp1);
						
					$bar_data_months		.= '"'.ucfirst($bar_data_months_temp2).' '.date('Y', strtotime($stats_timestamp_start_query)).'", ';	
					//Values
					$bar_data_values		.= ''.$content_get_query_month['c'].', ';
					
					
					
					//********Start debuging 
					//************************************************
					//echo(' echo_print += "<br />'.date('Y/m/d',$stats_timestamp_start_while).' ** '.date('y/m/d',$stats_timestamp_end_while).'    **   '.$stats_timestamp_start_query.' ** '.$stats_timestamp_end_query.' => '.$content_get_query_month['c'].'";');
					//************************************************
					//********End debuging 
					
				
					//Increment date +1 Month to test in while again
					$stats_timestamp_start_while = strtotime("+1 month", $stats_timestamp_start_while);
				
				}//end while
				
				
				//********Start calculating the first line to show 
				//********Ex: 1480 visites sur vos produits entre Janvier 2014 et Octobre 2014
				//************************************************
				$stats_timestamp_start_header_show				= date('d/m/Y',strtotime($stats_interval_v1_calcul));
				
				$stats_get_number_days_of_a_month_header_show	= cal_days_in_month(CAL_GREGORIAN, date('m',$stats_interval_v2_calcul), date('Y',$stats_interval_v2_calcul));
				
				$stats_timestamp_end_header_show				= $stats_get_number_days_of_a_month_header_show.'/'.date('m/Y',strtotime($stats_interval_v2_calcul));
				
				$stats_generated_header		= '<b>'.$stats_global_count.'</b> visites sur vos produits entre <b>'.$stats_timestamp_start_header_show.'</b> et <b>'.$stats_timestamp_end_header_show.'</b> ';
				echo(' document.getElementById(\'stats_load_head\').innerHTML = "'.$stats_generated_header.'<br />"; ');
				//***********************************************
				//*********End calculating the first line to show
				
				
				echo(' document.getElementById(\'stats_load_head\').innerHTML += echo_print; ');
				
				//Deleting the last comma ","
				$bar_data_months	= substr($bar_data_months, 0, -2);
				$bar_data_values	= substr($bar_data_values, 0, -2);
				//Closing the Javascript Array
				$bar_data_months	.= ']';
				$bar_data_values	.= ']';
				
				//Printing the Javascript Bar Months
				echo(' var data_months	= '.$bar_data_months.'; ');
				//Printing the Javascript Bar Values
				echo(' var data_values	= '.$bar_data_values.'; ');
				
			}//end else
?>
			document.getElementById('stats_h_month').value	= ""; 
			document.getElementById('stats_h_data').value	= '<?php echo($bar_data_months); ?>'; 
			document.getElementById('stats_h_values').value	= '<?php echo($bar_data_values); ?>'; 
		
			<?php
				if($stats_global_count>0){
					echo('document.getElementById("stats_btn_export_container").style.display	= "block"; '); 
				}else{
					echo('document.getElementById("stats_btn_export_container").style.display	= "none"; '); 
				}
			?>
			
			var data = {
				labels: data_months,
				datasets: [
					{
						label: "My First dataset",
						fillColor: "rgba(118, 196, 237, 0.87)",
						strokeColor: "#c1c1c1",
						highlightFill: "#11A0EB",
						highlightStroke: "green",
						data: data_values
					}
				]
			};
			
			
			var options 	= {
			
				responsive : true,
				
				
				//Boolean - Whether the scale should start at zero, or an order of magnitude down from the lowest value
				scaleBeginAtZero : true,

				//Boolean - Whether grid lines are shown across the chart
				scaleShowGridLines : true,

				//String - Colour of the grid lines
				scaleGridLineColor : "rgba(0,0,0,.05)",

				//Number - Width of the grid lines
				scaleGridLineWidth : 1,

				//Boolean - If there is a stroke on each bar
				barShowStroke : true,

				//Number - Pixel width of the bar stroke
				barStrokeWidth : 2,

				//Number - Spacing between each of the X value sets
				barValueSpacing : 5,

				//Number - Spacing between data sets within X values
				barDatasetSpacing : 1,

				//String - A legend template
				//tooltipTemplate: "<%if (label){%><%=label%>: <%}%><%= value %>",
				
				tooltipTitleFontColor: "red"

			}
			
			var ctx = document.getElementById("stats_canvas").getContext("2d");

			if(myBarChart){
				//To destroy the old one if the user generate a new one using "Ajax"
				myBarChart.destroy()
			}
			
			myBarChart = new Chart(ctx).Bar(data, options);


			
<?php

		}//!empty($stats_interval_v1) && !empty($stats_interval_v2)
	}else{
		header('Location: /login.html');
	}//end elseif session//end if session

?>
		