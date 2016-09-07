<?php


	if(!empty($stat_day_start) && !empty($stat_day_end)){
	
		echo(' var echo_print = "";');
		
		//Vars for the "While" Loop
		//From the first day to the last day 
		//Ex: 2014/10/01 To 2014/10/31
		$stats_timestamp_start_while	= strtotime($stat_day_start);
		$stats_timestamp_end_while		= strtotime($stat_day_end);
		
		while($stats_timestamp_start_while<=$stats_timestamp_end_while){
		
			//Creating a dynamic interval 
			//Ex: for every day between 2014/10/01 00:00:00 To 2014/10/01 23:59:59
			//And we repeat it while we reach the end day "Last day of this month"
			$stats_timestamp_start_query		= date('Y/m/d',$stats_timestamp_start_while).' 00:00:00';
			$stats_timestamp_end_query			= date('Y/m/d',$stats_timestamp_start_while).' 23:59:59';

			
			$query_day	= "SELECT 
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
			
			$res_query_day 			= $db->query($query_day, __FILE__, __LINE__);
					
			$content_get_query_day	= $db->fetchAssoc($res_query_day);
				
			
			//For the header message
			$stats_global_count		+= $content_get_query_day['c'];
					
			//For the Javascript Bar
			//Days
			$bar_data_days			.= '"'.date('d', strtotime($stats_timestamp_start_query)).'", ';	
			//Values
			$bar_data_values		.= ''.$content_get_query_day['c'].', ';
			
			
			//********Start debuging 
			//************************************************
			//echo(' echo_print += "<br />'.$stats_timestamp_start_query.' ** '.$stats_timestamp_end_query.'";');
			//************************************************
			//********End debuging 
			
			
			//Increment date +1 day to test in while again
			$stats_timestamp_start_while = strtotime("+1 day", $stats_timestamp_start_while);
			
		}//end while
		
		
		//********Start calculating the first line to show 
		//********Ex: 980 visites sur vos produits en Janvier 2014
		//************************************************		
		$stats_header_show_month_temp1		= date('F', strtotime($stat_day_start));
		$stats_header_show_month_temp2		= str_ireplace($months_en, $months_fr, $stats_header_show_month_temp1);
		
		$stats_generated_header_month		= $stats_header_show_month_temp2.' '.date('Y',strtotime($stat_day_start));
		$stats_generated_header				= '<b>'.$stats_global_count.'</b> visites sur vos produits en <b>'.$stats_generated_header_month.'</b>';
		echo(' document.getElementById(\'stats_load_head\').innerHTML = "'.$stats_generated_header.'<br />"; ');
		//***********************************************
		//*********End calculating the first line to show
		
		echo(' document.getElementById(\'stats_load_head\').innerHTML += echo_print; ');
	
	
		//Deleting the last comma ","
		$bar_data_days		= substr($bar_data_days, 0, -2);
		$bar_data_values	= substr($bar_data_values, 0, -2);
		//Closing the Javascript Array
		$bar_data_days		.= ']';
		$bar_data_values	.= ']';
		
		//Printing the Javascript Bar Months
		echo(' var data_days	= '.$bar_data_days.'; ');
		//Printing the Javascript Bar Values
		echo(' var data_values	= '.$bar_data_values.'; ');
?>
		document.getElementById('stats_h_month').value	= "<?php echo($stats_generated_header_month); ?>"; 
		document.getElementById('stats_h_data').value	= '<?php echo($bar_data_days); ?>'; 
		document.getElementById('stats_h_values').value	= '<?php echo($bar_data_values); ?>'; 
		
		<?php
			if($stats_global_count>0){
				echo('document.getElementById("stats_btn_export_container").style.display	= "block"; '); 
			}else{
				echo('document.getElementById("stats_btn_export_container").style.display	= "none"; '); 
			}
		?>
			
		var data = {
			labels: data_days,
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
	}//End if(!empty($stat_day_start) && !empty($stat_day_end))
?>