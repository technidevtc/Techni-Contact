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
			var table = $('#example5').DataTable();
		});
</script>
<div class="titleTab">Fournisseur source </div>
 <table id="example5" class="display item-list" cellspacing="0" width="100%">
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
		
		$sqlFourni  = "SELECT nom1 , id_supplier
					    FROM after_sales_description asd, advertisers aa
					   WHERE asd.id_supplier = aa.id 
					   AND timestamp_created BETWEEN '$date_start' AND '$date_end'
					   GROUP BY id_supplier ";
		$reqFourni  =  mysql_query($sqlFourni);
		$rowsFourni =  mysql_num_rows($reqFourni);
		if($rowsFourni != 0){
		$i=0;
		while($dataFourni = mysql_fetch_object($reqFourni)){
			
		$sqlAllType  =  "SELECT COUNT(`type`) as allType
							FROM after_sales_description
						 WHERE id_supplier='".$dataFourni->id_supplier."' ";
		$reqAllType  =  mysql_query($sqlAllType);
		$dataAllType =  mysql_fetch_object($reqAllType);
		
		$sqlFirstTtt  = "SELECT COUNT(`type`) as allType
							FROM after_sales_description
						 WHERE id_supplier='".$dataFourni->id_supplier."' 
						 AND timestamp_created BETWEEN '$date_start' AND '$date_end' ";
		$reqFirstTtt  =  mysql_query($sqlFirstTtt);
		$dataFirstTtt =  mysql_fetch_object($reqFirstTtt);
		
		$sqlOuvert = "SELECT COUNT(id) as totalOuvert
					   FROM after_sales_description 
					  WHERE status='0' AND id_supplier='".$dataFourni->id_supplier."' ";
		$reqOuvert =  mysql_query($sqlOuvert);
		$dataOuvert=  mysql_fetch_object($reqOuvert);
		
		
		$sqlDelaiDays  = "SELECT processing_time FROM after_sales_description WHERE id_supplier='".$dataFourni->id_supplier."' ";
		$reqDelaiDays  =  mysql_query($sqlDelaiDays);
		while($dataDelaiDays  =  mysql_fetch_object($reqDelaiDays)){
			$processing_time += $dataDelaiDays->processing_time;
		}
		$processing_timeTOTAL += $processing_time; 
		$processing_timeDevis  = $processing_time / $dataOuvert->totalOuvert;
		if(empty($processing_timeDevis)) $DiffDateConvert = 'En cours'; 
		else $DiffDateConvert = secondsToTime($processing_timeDevis); 
		
		
		$tauxFirstSource 	= ($dataFirstTtt->allType /  $totalAll) * 100;
		$tauxFirstSource = number_format($tauxFirstSource, 2, ',', ' ');
		
		$tauxResoluComm = ($dataOuvert->totalOuvert / $dataAllType->allType) * 100;
		$tauxResoluComm = number_format($tauxResoluComm, 2, ',', ' ');
	?>
		<tr>
			<td><?= $dataFourni->nom1 ?></td>
			<td><?= $tauxFirstSource ?> %</td>
			<td><?= $dataFirstTtt->allType ?></td>
			<td><?= $dataOuvert->totalOuvert ?></td>
			<td><?= $tauxResoluComm  ?> %</td>
			<td><?= $DiffDateConvert ?></td>
			<td><span id="statsGlobal"  onclick="statsGraphFournisseur('<?= $dataFourni->id_supplier ?>')">Voir</span></td>
		</tr>
	<?php $i++; 
		$tauxFirstRessource		+= ($dataFirstTtt->allType /  $totalAll)*100;
		$totalDaclaPeriodeRes  	+=  $dataFirstTtt->allType;
		$totalDaclaPeriodeRes2  +=  $dataFirstTtt->allType;
		$totalResoluRes			+= $dataOuvert->totalOuvert;
		$TotaltauxResoluRes		+= ($dataOuvert->totalOuvert / $dataAllType->allType)*100;
		$DiffDateConvert = "";
		$processing_time = "";
	} ?>
	</tbody>
	<?php
		$tauxFirstRessource = $tauxFirstRessource/$i;
		// $TotaltauxResoluRes = $TotaltauxResoluRes/$i;
		$TotaltauxResoluRes		= ($totalResoluRes / $totalDaclaPeriodeRes)*100;
		$processingTOTAL   		= $processing_timeTOTAL  / $totalResoluRes;
		
		if(empty($processingTOTAL)) $processingTOTAL = ' En cours '; 
		else $processingTOTAL 		= secondsToTime($processingTOTAL);
	}
	
	$tauxFirstRessource = ($totalDaclaPeriodeRes2/$totalDaclaPeriodeRes)*100;
	?>
	<tr> 
		<td><strong>TOTAL</strong></td>
		<td><?= number_format($tauxFirstRessource, 2, ',', ' ') ?> %</td>
		<td><?= $totalDaclaPeriodeRes ?></td>
		<td><?= $totalResoluRes ?></td>
		<td><?= number_format($TotaltauxResoluRes, 2, ',', ' ') ?> %</td>
		<td><?= $processingTOTAL ?></td>
		<td><span id="statsGlobal"  onclick="statsGraphFournisseur('all')">Voir</span></td>
	</tr>
  </table>
	<script>
	function statsGraphFournisseur(typeS){
		
		var startDate = $("#startDate").val();
		var endDate   = $("#endDate").val();
		$("#placeholderGraphFournisseur").show();
		$("#graphGlobal").show();
		ajaxLoadChartFournisseur(startDate, endDate,typeS);
		if(typeS == "all"){
			$("#txtGraphFournisseur").text("TOTAL");
		}else{
			$("#txtGraphFournisseur").text(""+typeS);
		}
	}
  </script>
 <style>
 #example5_length, #example5_paginate{
	 display:none;
 }
 
 </style>