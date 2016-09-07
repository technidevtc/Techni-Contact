<?php
 require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php'; 
 $db = DBHandle::get_instance();
 
 function secondsToTime($seconds) {
	$dtF = new DateTime("@0");
	$dtT = new DateTime("@$seconds");
	return $dtF->diff($dtT)->format('%aJ - %hH - %iM');
 }
 
?>
<script type="text/javascript" language="javascript" class="init">
		$(document).ready(function () {
			var table = $('#example3').DataTable();
		});
</script>
<div class="titleTab">Source des commandes  </div>
 <table id="example3" class="display item-list" cellspacing="0" width="100%">
    <thead>
      <tr>
	  <?php	  
			echo '<th> </th>';
			echo '<th>%  </th>';
			echo '<th>Déclarés sur la période  </th>';			
			echo '<th>Résolus   </th>';			
			echo '<th>Taux de résolution (%) </th>';			
			echo '<th>Délai de traitement SAV résolus (J) </th>';						
			echo '<th>Evolution </th>';			
	  ?>
		</tr>
    </thead>
    <tbody>
	<?php
		$date_start = $_GET['date_start'].' 00:00:00';
		$date_end 	= $_GET['date_end'].' 23:59:59';
		
		$sqlAllType  = "SELECT count(`type`) as totalType FROM after_sales_description 
					    WHERE timestamp_created BETWEEN '$date_start' AND '$date_end' GROUP by `type` ";
		$reqAllType  =  mysql_query($sqlAllType);
		while($dataAllType =  mysql_fetch_object($reqAllType)){
			$totalAll += $dataAllType->totalType;
		}
		
		$sqlInternet  = "SELECT COUNT(`type`) as allType
							FROM after_sales_description
						 WHERE order_type='0'
						 AND timestamp_created BETWEEN '$date_start' AND '$date_end' ";
		$reqInternet  =  mysql_query($sqlInternet);
		
		
		
		$rowsInternet =  mysql_num_rows($reqInternet);
		if($rowsInternet !=0){
		$dataInternet =  mysql_fetch_object($reqInternet);
		$tauxFirstSource 	= ($dataInternet->allType /  $totalAll) * 100;
		$tauxFirstSource = number_format($tauxFirstSource, 2, ',', ' ');
		
		$sqlOuvertInternet  = " SELECT COUNT(id) as totalOuvert
									FROM after_sales_description 
								WHERE status='0' AND order_type='0'  AND timestamp_created BETWEEN '$date_start' AND '$date_end' ";
		$reqOuvertInternet  =  mysql_query($sqlOuvertInternet);
		$dataOuvertInternet =  mysql_fetch_object($reqOuvertInternet);
		
		
		
		$sqlDelaiDaysI  = " SELECT processing_time FROM after_sales_description WHERE order_type='0'  
							AND status='0'
							AND timestamp_created BETWEEN '$date_start' AND '$date_end'	";
		$reqDelaiDaysI  =  mysql_query($sqlDelaiDaysI);
		while($dataDelaiDaysI  =  mysql_fetch_object($reqDelaiDaysI)){
			$processing_timeI += $dataDelaiDaysI->processing_time;
		}
		
		if(!empty($processing_timeI)){
			$DiffDateConvert 	= secondsToTime($processing_timeI);
		}else{
			$DiffDateConvert = " En cours ";
		}
		$tauxResoluInternet = ($dataOuvertInternet->totalOuvert / $dataInternet->allType) * 100;
		$tauxResoluInternet = number_format($tauxResoluInternet, 2, ',', ' ');
		if($dataInternet->allType != 0 ){
	?>
		<tr>
			<td>Internet </td>
			<td><?= $tauxFirstSource ?> %</td>
			<td><?= $dataInternet->allType ?></td>
			<td><?= $dataOuvertInternet->totalOuvert ?></td>
			<td><?= $tauxResoluInternet ?> %</td>
			<td><?= $DiffDateConvert ?></td>
			<td><span id="statsGlobal"  onclick="statsGraphSource('Internet')">Voir</span></td>
		</tr>
	<?php } }?>
	<?php
		$sqlDevis  = "SELECT COUNT(`type`) as allType
							FROM after_sales_description
						 WHERE order_type IN('1','2') 
						 AND timestamp_created BETWEEN '$date_start' AND '$date_end' ";
		$reqDevis  =  mysql_query($sqlDevis);
		$rowsDevis =  mysql_num_rows($reqDevis);
		
		if($rowsDevis != 0){
		$dataDevis =  mysql_fetch_object($reqDevis);
		
		$tauxFirstSourceDevis 	= ($dataDevis->allType /  $totalAll) * 100;
		$tauxFirstSourceDevis = number_format($tauxFirstSourceDevis, 2, ',', ' ');
		
		$sqlOuvertDevis  = " SELECT COUNT(id) as totalOuvert
									FROM after_sales_description 
								WHERE status='0' AND order_type IN('1','2')
								 AND timestamp_created BETWEEN '$date_start' AND '$date_end' ";
		$reqOuvertDevis  =  mysql_query($sqlOuvertDevis);
		$dataOuvertDevis =  mysql_fetch_object($reqOuvertDevis);
		
		$sqlDelaiDaysP  = "SELECT processing_time FROM after_sales_description WHERE order_type IN('1','2') AND status='0'  AND timestamp_created BETWEEN '$date_start' AND '$date_end'";
		$reqDelaiDaysP  =  mysql_query($sqlDelaiDaysP);
		while($dataDelaiDaysP  =  mysql_fetch_object($reqDelaiDaysP)){
			$processing_timeP += $dataDelaiDaysP->processing_time;
		}
		
		if(!empty($processing_timeP)){
			$DiffDateConvertP 	= secondsToTime($processing_timeP);
		}else{
			$DiffDateConvertP = " En cours ";
		}
		
		$tauxResoluDevis = ($dataOuvertDevis->totalOuvert / $dataDevis->allType) * 100;
		$tauxResoluDevis = number_format($tauxResoluDevis, 2, ',', ' ');
		if($dataDevis->allType != 0 ){
	?>
	
		<tr>
			<td>Devis </td>
			<td><?= $tauxFirstSourceDevis ?> %</td>
			<td><?= $dataDevis->allType ?></td>
			<td><?= $dataOuvertDevis->totalOuvert ?></td>
			<td><?= $tauxResoluDevis ?> %</td>
			<td><?= $DiffDateConvertP ?></td>
			<td><span id="statsGlobal"  onclick="statsGraphSource('Devis')">Voir</span></td>
		</tr>
	<?php } 
		} ?>
	<?php
		$sqlAutre  = "SELECT COUNT(`type`) as allType
							FROM after_sales_description
						 WHERE order_type IN('3','4','5','9') 
						 AND timestamp_created BETWEEN '$date_start' AND '$date_end' ";
		$reqAutre  =  mysql_query($sqlAutre);
		$rowsAutre =  mysql_num_rows($reqAutre);
		$dataAutre =  mysql_fetch_object($reqAutre);
		if($rowsAutre != 0){
		$tauxFirstSourceAutre 	= ($dataAutre->allType /  $totalAll) * 100;
		$tauxFirstSourceAutre   = number_format($tauxFirstSourceAutre, 2, ',', ' ');
		
		$sqlOuvertAutre  = " SELECT COUNT(id) as totalOuvert
									FROM after_sales_description 
								WHERE status='0' AND order_type IN('3','4','5','9')
								 AND timestamp_created BETWEEN '$date_start' AND '$date_end' ";
		$reqOuvertAutre  =  mysql_query($sqlOuvertAutre);
		$dataOuvertAutre =  mysql_fetch_object($reqOuvertAutre);
		
		$tauxResoluAutre = ($dataOuvertAutre->totalOuvert / $dataDevis->allType) * 100;
		$tauxResoluAutre = number_format($tauxResoluAutre, 2, ',', ' ');
		
		$sqlDelaiDaysA  = "SELECT processing_time FROM after_sales_description WHERE order_type IN('3','4','5','9') AND status='0'  AND timestamp_created BETWEEN '$date_start' AND '$date_end'  ";
		$reqDelaiDaysA  =  mysql_query($sqlDelaiDaysA);
		while($dataDelaiDaysA  =  mysql_fetch_object($reqDelaiDaysA)){
			$processing_timeA += $dataDelaiDaysA->processing_time;
		}
		
		if(!empty($processing_timeA)){
			$DiffDateConvertA 	= secondsToTime($processing_timeA);
		}else{
			$DiffDateConvertA = " En cours ";
		}
		
		// $tauxFirsttt2  = ($tauxFirstSource + $tauxFirstSourceDevis + $tauxFirstSourceDevis);
		$totalPeriode = $dataAutre->allType + $dataDevis->allType + $dataInternet->allType;
		$totalResolu  = $dataOuvertAutre->totalOuvert + $dataOuvertDevis->totalOuvert + $dataOuvertInternet->totalOuvert;
		$totalTauxResolu  = ($tauxResoluAutre + $tauxResoluDevis + $tauxResoluInternet)/3;
		
		if($dataAutre->allType != 0 ){
	?>
	
		<tr>
			<td>Autre </td>
			<td><?= $tauxFirstSourceAutre ?> %</td>
			<td><?= $dataAutre->allType ?></td>
			<td><?= $dataOuvertAutre->totalOuvert ?></td>
			<td><?= $tauxResoluAutre ?> %</td>
			<td><?= $DiffDateConvertA ?></td>
			<td><span id="statsGlobal"  onclick="statsGraphSource('Autre')">Voir</span></td>
		</tr>
	<?php } 
		} ?>
	</tbody>
	<?php
	
	$totalDate  = ($processing_timeP +  $DiffDateConvertA + $processing_timeI) / $totalResolu;
	
	$tauxFirsttt = ($totalPeriode/$totalPeriode)*100;
	// $totalTauxResolu  = ($tauxResoluAutre + $tauxResoluDevis + $tauxResoluInternet)/3;
	$totalTauxResolu  = ($totalResolu / $totalPeriode )*100;

	
	if( ($rowsAutre != 0) || ($rowsDevis != 0) || ($rowsInternet != 0)  ){
		if(!empty($totalDate)){
			$totalDate 	= secondsToTime($totalDate);
		}else{
			$totalDate = " - ";
		}
		// $totalDate	= secondsToTime($totalDate);
	}
	?>
	<tr>
		<td><strong>TOTAL</strong></td>
		<td><?= number_format($tauxFirsttt, 2, ',', ' ') ?> %</td>
		<td><?= $totalPeriode ?></td>
		<td><?= $totalResolu ?></td>
		<td><?= number_format($totalTauxResolu, 2, ',', ' ') ?> %</td>
		<td><?= $totalDate ?></td>
		<td><span id="statsGlobal"  onclick="statsGraphSource('all')">Voir</span></td>
	</tr>
  </table>
	
	<script>
  
  function statsGraphSource(typeS){
		
		var startDate = $("#startDate").val();
		var endDate   = $("#endDate").val();
		$("#placeholderGraphSource").show();
		$("#graphGlobal").show();
		ajaxLoadChartSource(startDate, endDate,typeS);
		
		if(typeS == "all"){
			$("#txtGraphSource").text("TOTAL");
		}else{
			$("#txtGraphSource").text(""+typeS);
		}
	}
  
  </script>
	
	
 <style>
 #example3_length, #example3_paginate, #example3_filter,#example3_info{
	 display:none;
 }
 
 </style>