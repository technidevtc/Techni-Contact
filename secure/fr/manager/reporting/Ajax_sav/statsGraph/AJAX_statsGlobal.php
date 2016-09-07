<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
header('Content-Type: application/json');
$db = DBHandle::get_instance();
// require_once('../../lib/idiorm.php');
// Set up the ORM library
// require_once('../../setup.php');

function returnNbrMonth($DateD,$DateF){
	$datetime1 = new DateTime($DateD);
	$datetime2 = new DateTime($DateF);
	$interval = $datetime1->diff($datetime2);
	return $nbday= $interval->format('%m');
}

$nbrMonth	= returnNbrMonth($_GET['start'],$_GET['end']);

if (isset($_GET['start']) AND isset($_GET['end'])) {
	
	$start 		 = $_GET['start'];
	$end 		 = $_GET['end'];
	$typeG 		 = $_GET['type'];
	$data = array();
		
	if($typeG == 'all'){
		$sqlAdd	= "   ";
	}else{
				if($typeG == "SAVD") $sqlAdd = " AND `type` = '1' ";
				if($typeG == "SAVC") $sqlAdd = " AND `type` = '2' ";
				if($typeG == "SAVR") $sqlAdd = " AND `type` = '3' ";
				if($typeG == "SAVI") $sqlAdd = " AND `type` = '4' ";
				if($typeG == "SAVA") $sqlAdd = " AND `type` = '5' ";
	}
	
	
	if($nbrMonth > 0 ){
	// $start = '2016-07-01';
	// $end = '2016-07-20';
	$sqlDay ="select DATE(selected_date) as selected_date from (
				  select @maxDate - interval (a.a + (10 * b.a) + (100 * c.a)) month as selected_date from
				  (select 0 as a union all select 1 union all select 2 union all select 3
				   union all select 4 union all select 5 union all select 6 union all
				   select 7 union all select 8 union all select 9) a,
				  (select 0 as a union all select 1 union all select 2 union all select 3
				   union all select 4 union all select 5 union all select 6 union all
				   select 7 union all select 8 union all select 9) b,
				  (select 0 as a union all select 1 union all select 2 union all select 3
				   union all select 4 union all select 5 union all select 6 union all
				   select 7 union all select 8 union all select 9) c,
				  (select @minDate := '$start', @maxDate := '$end') d
				) e
				where selected_date between @minDate and @maxDate"; 
				
			
	$reqDay  =  mysql_query($sqlDay);
	
	}else{
	
	$sqlDay  ="select * from 
				(select adddate('1970-01-01',t4*10000 + t3*1000 + t2*100 + t1*10 + t0) selected_date from
				 (select 0 t0 union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t0,
				 (select 0 t1 union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t1,
				 (select 0 t2 union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t2,
				 (select 0 t3 union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t3,
				 (select 0 t4 union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t4) v
				where selected_date between '$start' and '$end' "; 
	$reqDay  =  mysql_query($sqlDay);
	}
	// echo $sqlDay;
	$key = 0;
	
	while($results =  mysql_fetch_assoc($reqDay)){
		
		if($nbrMonth > 0 ){
			$Today = date("d");
			$dateExplode  = explode('-',$results['selected_date']);
			$date_start   = $dateExplode[0].'-'.$dateExplode[1].'-01 00:00:00';
			$date_end     = $dateExplode[0].'-'.$dateExplode[1].'-31 23:59:59' ;
			$dateValue 	  = $date_start;
			// $dateValue 	  = $dateExplode[0].'-'.$dateExplode[1].'-0'.$JJ;
			// $dateX		  = $date_end
		}else{
			$date_start = $results['selected_date'].' 00:00:00';
			$date_end 	= $results['selected_date'].' 23:59:59';
			$dateValue	= $results['selected_date'];
		}
		
		$sqlCount  = "SELECT COUNT(id) as total 
					  	FROM after_sales_description  
					   WHERE timestamp_created BETWEEN '$date_start' AND '$date_end' $sqlAdd  ";
		// 
		$reqCount  =  mysql_query($sqlCount);
		$dataCount =  mysql_fetch_object($reqCount);
		// echo $sqlCount.'<br />';
		
		if( $dataCount->total == '0') $value = "0";
		else $value = $dataCount->total;
		
		if(empty($dataCount->total) ) $value = "0";
		else $value = $dataCount->total;
		
		$data[$key]['label'] = $dateValue;
		$data[$key]['value'] = $value;
	$key++;
	} 
	
	echo json_encode($data);
}
  