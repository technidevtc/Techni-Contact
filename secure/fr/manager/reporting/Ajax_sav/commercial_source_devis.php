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
			var table = $('#example4').DataTable();
		});
</script>
<div class="titleTab">Commercial source devis </div>
 <table id="example4" class="display item-list" cellspacing="0" width="100%">
    <thead>
      <tr>
	  <?php	  
			echo '<th> </th>';
			echo '<th>%  </th>';
			echo '<th>Déclarés sur la période  </th>';			
			echo '<th>Résolus   </th>';			
			echo '<th>Taux de résolution (%) </th>';			
			echo '<th>Délai de traitement SAV résolus (J) </th>';						
			echo '<th>Evolution  </th>';			
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
		
		$sqlUser11  = "SELECT name ,id_sales_operator
						FROM after_sales_description asd, bo_users bu
					 WHERE asd.id_sales_operator = bu.id 
					 AND timestamp_created BETWEEN '$date_start' AND '$date_end'
					 GROUP BY id_sales_operator ";
		$reqUser11  =  mysql_query($sqlUser11);
		while($dataUser11 = mysql_fetch_object($reqUser11)){
			$sqlFirstTtt  = "SELECT COUNT(`type`) as allType
								FROM after_sales_description
							 WHERE id_sales_operator='".$dataUser11->id_sales_operator."' 
							 AND timestamp_created BETWEEN '$date_start' AND '$date_end' ";
			$reqFirstTtt  =  mysql_query($sqlFirstTtt);
			$dataFirstTtt =  mysql_fetch_object($reqFirstTtt);
			$totalUserCount  +=  $dataFirstTtt->allType;
		}
		
		
		$sqlUser  = "SELECT name ,id_sales_operator
						FROM after_sales_description asd, bo_users bu
					 WHERE asd.id_sales_operator = bu.id 
					 AND timestamp_created BETWEEN '$date_start' AND '$date_end'
					 GROUP BY id_sales_operator ";
		$reqUser  =  mysql_query($sqlUser);
		$rowsUrser = mysql_num_rows($reqUser);
	
		if($rowsUrser != 0){
		$i = 0;
		while($dataUser = mysql_fetch_object($reqUser)){
		
		$sqlAllType  =  "SELECT COUNT(`type`) as allType
							FROM after_sales_description
						 WHERE id_sales_operator='".$dataUser->id_sales_operator."' ";
		$reqAllType  =  mysql_query($sqlAllType);
		$dataAllType =  mysql_fetch_object($reqAllType); 
		
		$sqlFirstTtt  = "SELECT COUNT(`type`) as allType
							FROM after_sales_description
						 WHERE id_sales_operator='".$dataUser->id_sales_operator."' 
						 AND timestamp_created BETWEEN '$date_start' AND '$date_end' ";
		$reqFirstTtt  =  mysql_query($sqlFirstTtt);
		$dataFirstTtt =  mysql_fetch_object($reqFirstTtt);
		
		// echo $dataFirstTtt->allType;
		
		$tauxFirstSource 	= ($dataFirstTtt->allType /  $totalUserCount) * 100;
		
		$sqlOuvert = "SELECT COUNT(id) as totalOuvert
					   FROM after_sales_description 
					  WHERE status='0' AND id_sales_operator='".$dataUser->id_sales_operator."' 
					  AND timestamp_created BETWEEN '$date_start' AND '$date_end' ";
		$reqOuvert =  mysql_query($sqlOuvert);
		$dataOuvert=  mysql_fetch_object($reqOuvert);
		// echo $sqlOuvert.'<br />';
		
		
		$sqlDelaiDays  = "SELECT processing_time 
							FROM after_sales_description 
						  WHERE id_sales_operator='".$dataUser->id_sales_operator."'
						   AND timestamp_created BETWEEN '$date_start' AND '$date_end'
						  GROUP BY id_sales_operator ";
		$reqDelaiDays  =  mysql_query($sqlDelaiDays);
		
		while($dataDelaiDays  =  mysql_fetch_object($reqDelaiDays)){
			if(!empty($dataDelaiDays->processing_time)){
				// echo 'AAA'.$dataDelaiDays->processing_time.'<br />';
				$processing_time += $dataDelaiDays->processing_time;
			}
		}
		
		if(empty($processing_time)) $DiffDateConvert = 'En cours'; 
		else $DiffDateConvert = secondsToTime($processing_time); 
		// echo $DiffDateConvert.' <br /> ';
		
		$DiffDateConvertTotal += $processing_time;
		
		$tauxResoluComm = ($dataOuvert->totalOuvert / $dataAllType->allType) * 100;
		$tauxResoluComm = number_format($tauxResoluComm, 2, ',', ' ');
		
		
		
		$tauxFirstSource = number_format($tauxFirstSource, 2, ',', ' ');
		
		$i++;
		
	?>
	
		<tr>
			<td><?= $dataUser->name  ?></td>
			<td><?= $tauxFirstSource ?> %</td>
			<td><?= $dataFirstTtt->allType ?></td>
			<td><?= $dataOuvert->totalOuvert ?></td>
			<td><?= $tauxResoluComm ?> %</td>
			<td><?= $DiffDateConvert ?></td>

			<td><span id="statsGlobal"  onclick="statsGraphCommercial('<?= $dataUser->id_sales_operator ?>')">Voir</span></td>
		</tr>
	<?php 
		$tauxFirstRessource		+= ($dataFirstTtt->allType /  $totalAll)*100;
		$totalDaclaPeriodeRes  	+=  $dataFirstTtt->allType;
		$totalDaclaPeriodeRes2  +=  $dataFirstTtt->allType;
		$totalResoluRes			+=  $dataOuvert->totalOuvert;
		$TotaltauxResoluRes		+= ($dataOuvert->totalOuvert / $dataAllType->allType)*100;
		$processing_time = "";
		}
	?>	
	
	<tbody>
	<?php
		
		// $TotaltauxResoluRes = $TotaltauxResoluRes/$i;
		$TotaltauxResoluRes		= ($totalResoluRes / $totalDaclaPeriodeRes)*100;
		$DiffDateConvertTotalTT = $DiffDateConvertTotal/ $totalResoluRes;
		
		// $DiffDateConvertTotalTT = secondsToTime($DiffDateConvertTotalTT); 
		
		if(empty($DiffDateConvertTotalTT)) $DiffDateConvertTotalTT = ' En cours '; 
		else $DiffDateConvertTotalTT = secondsToTime($DiffDateConvertTotalTT); 
		
		}
		$tauxFirstRessource = ($totalDaclaPeriodeRes2/$totalDaclaPeriodeRes)*100;
	?>
	<tr> 
		<td><strong>TOTAL</strong></td>
		<td><?= number_format($tauxFirstRessource, 2, ',', ' ') ?> %</td>
		<td><?= $totalDaclaPeriodeRes ?></td>
		<td><?= $totalResoluRes ?></td>
		<td><?= number_format($TotaltauxResoluRes, 2, ',', ' ') ?> %</td>
		<td><?= $DiffDateConvertTotalTT ?></td>
		<td><span id="statsGlobal"  onclick="statsGraphCommercial('all')">Voir</span></td>
	</tr>
	</tbody>
  </table>
  <script>
	function statsGraphCommercial(typeS){
		
		var startDate = $("#startDate").val();
		var endDate   = $("#endDate").val();
		$("#placeholderGraphCommercial").show();
		$("#graphGlobal").show();
		ajaxLoadChartCommercial(startDate, endDate,typeS);
		if(typeS == "all"){
			$("#txtGraphCommercial").text("TOTAL");
		}else{
			$("#txtGraphCommercial").text(""+typeS);
		}
	}
  </script>
 <style>
 #example4_length, #example4_paginate , #example4_filter,#example4_info{
	 display:none;
 }
 
 </style>