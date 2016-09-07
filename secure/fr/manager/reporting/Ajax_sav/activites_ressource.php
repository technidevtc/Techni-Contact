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
			var table = $('#example2').DataTable();
		});
</script>
 <div class="titleTab">Activités par ressource  </div>
 <table id="example2" class="display item-list" cellspacing="0" width="100%">
    <thead>
      <tr>
	  <?php	  
			echo '<th> </th>';
			echo '<th>%  </th>';
			echo '<th>Déclarés sur la période  </th>';			
			echo '<th>Résolus   </th>';			
			echo '<th>Taux de résolution (%) </th>';			
			echo '<th>Délai de traitement SAV résolus (J) </th>';			
			echo '<th>Appels total </th>';			
			echo '<th>Appels aboutis </th>';			
			echo '<th>Taux joignabilité </th>';			
	  ?>
		</tr>
    </thead>
    <tbody>
	<?php
		$date_start = $_GET['date_start'].' 00:00:00';
		$date_end 	= $_GET['date_end'].' 23:59:59';
		
		$sqlRessou   = "SELECT id_operator_created as idUser , name
						  FROM after_sales_description asd, bo_users bu
					    WHERE bu.id = asd.id_operator_created
						AND timestamp_created BETWEEN '$date_start' AND '$date_end' GROUP by `id_operator_created` " ;
		$reqRessou	 =  mysql_query($sqlRessou);
		$rowsRessou  =  mysql_num_rows($reqRessou);
		
		if($rowsRessou != 0){
		
		$sqlAllType  = "SELECT count(`type`) as totalType FROM after_sales_description 
					    WHERE timestamp_created BETWEEN '$date_start' AND '$date_end' GROUP by `type` ";
		$reqAllType  =  mysql_query($sqlAllType);
		while($dataAllType =  mysql_fetch_object($reqAllType)){
			$totalAll += $dataAllType->totalType;
		}
		// echo $sqlAllType;
		while($dataRessou = mysql_fetch_object($reqRessou)){
		
		$sqlAllType  =  "SELECT COUNT(`type`) as allType
							FROM after_sales_description
						 WHERE id_operator_created='".$dataRessou->idUser."' 
						 AND timestamp_created BETWEEN '$date_start' AND '$date_end'  ";
		$reqAllType  =  mysql_query($sqlAllType);
		$dataAllType =  mysql_fetch_object($reqAllType); 
		
		$sqlOuvert = "SELECT COUNT(id) as totalOuvert
					   FROM after_sales_description 
					  WHERE status='0' AND id_operator_created='".$dataRessou->idUser."'
					  AND timestamp_created BETWEEN '$date_start' AND '$date_end'	 ";
		$reqOuvert =  mysql_query($sqlOuvert);
		$dataOuvert=  mysql_fetch_object($reqOuvert);
		
		$tauxFirstRessource = ($dataAllType->allType /  $totalAll) * 100;
		$tauxFirstRessource = number_format($tauxFirstRessource, 2, ',', ' ');
		
		$tauxResoluRess = ($dataOuvert->totalOuvert / $dataAllType->allType) * 100;
		$tauxResoluRess = number_format($tauxResoluRess, 2, ',', ' ');
		
			
		$sqlTotalApp  = "SELECT COUNT(id) as totalAppel
							FROM after_sales_calls_history
						 WHERE id_operator='".$dataRessou->idUser."' ";
		$reqTotalApp  =  mysql_query($sqlTotalApp);
		$dataTotalApp =  mysql_fetch_object($reqTotalApp);
		
		$sqlTotalAbouti  = "SELECT COUNT(id) as totalAppelAbouti
							FROM after_sales_calls_history
						 WHERE id_operator='".$dataRessou->idUser."'
							AND call_result='call_ok'";
		$reqTotalAbouti  =  mysql_query($sqlTotalAbouti);
		$dataTotalAbouti =  mysql_fetch_object($reqTotalAbouti);
		
		$sqlDelaiDays  = "SELECT processing_time 
						  FROM after_sales_description 
						  WHERE id_operator_created='".$dataRessou->idUser."'
						  AND timestamp_created BETWEEN '$date_start' AND '$date_end' 
						  AND status='0' 						  ";
		$reqDelaiDays  =  mysql_query($sqlDelaiDays);
		while($dataDelaiDays  =  mysql_fetch_object($reqDelaiDays)){
			$processing_time += $dataDelaiDays->processing_time;
		}
		
		$processing_timeDevis = $processing_time/ $dataOuvert->totalOuvert;
		// echo $sqlDelaiDays.'<br />';
		
		if(empty($processing_timeDevis)) $DiffDateConvert = ' En cours '; 
		else $DiffDateConvert = secondsToTime($processing_timeDevis); 
		
		
		$tauxJoignabilite = ($dataTotalAbouti->totalAppelAbouti / $dataTotalApp->totalAppel)*100;
		$tauxJoignabilite = number_format($tauxJoignabilite, 2, ',', ' ');
	?>
		<tr>
			<td><?= $dataRessou->name ?></td>
			<td><?= $tauxFirstRessource ?> %</td>
			<td><?= $dataAllType->allType ?></td>
			<td><?= $dataOuvert->totalOuvert ?></td>
			<td><?= $tauxResoluRess ?> %</td>
			<td><?= $DiffDateConvert ?></td>
			<td><?= $dataTotalApp->totalAppel ?></td>
			<td><?= $dataTotalAbouti->totalAppelAbouti ?></td>
			<td><?= $tauxJoignabilite ?> %</td>
		</tr>
		<?php
			
			
			$totalDaclaPeriodeRes  	+= $dataAllType->allType;
			$totalResoluRes			+= $dataOuvert->totalOuvert;
			// $TotaltauxResoluRes		+= ($dataOuvert->totalOuvert / $dataAllType->allType)*100;
			$totalAppelRes		    +=  $dataTotalApp->totalAppel;
			$TotalAbouti		    +=  $dataTotalAbouti->totalAppelAbouti ;
			
			$TotalProcessing_time 	+= $processing_time;
		$processing_time ="";	
			
		?>
		<?php }
		// echo $totalResoluRes;
		$tauxFirstRessource		= ($totalDaclaPeriodeRes  / $totalDaclaPeriodeRes  )*100;
		$TotalDiffDateConvert		 = $TotalProcessing_time/$totalResoluRes;
		$tauxJoignabiliteT 	    = ($TotalAbouti / $totalAppelRes )*100;
		if(empty($TotalDiffDateConvert)) $TotalDiffDateConvert = ' - '; 
		else $TotalDiffDateConvert = secondsToTime($TotalDiffDateConvert);
		// $TotalDiffDateConvert 		 = secondsToTime($TotalDiffDateConvert); 
		
		$TotaltauxResoluRes		= ($totalResoluRes / $totalDaclaPeriodeRes)*100;
		
		}
		
		?>
		
    </tbody>
	<tr>
		<td><strong>TOTAL</strong></td>
		<td><?= number_format($tauxFirstRessource, 2, ',', ' ')  ?> %</td>
		<td><?= $totalDaclaPeriodeRes ?></td>
		<td><?= $totalResoluRes ?></td>
		<td><?= number_format($TotaltauxResoluRes, 2, ',', ' ')  ?> %</td>
		<td><?= $TotalDiffDateConvert ?></td>
		<td><?= $totalAppelRes ?></td>
		<td><?= $TotalAbouti ?></td>
		<td><?= number_format($tauxJoignabiliteT, 2, ',', ' ')  ?> %</td>
	</tr>
  </table>
	
 <style>
 #example2_length, #example2_paginate, #example2_filter,#example2_info{
	 display:none;
 }
 
 </style>