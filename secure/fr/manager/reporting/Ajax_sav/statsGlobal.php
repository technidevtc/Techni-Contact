<?php
if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}
$user = new BOUser();

require_once(ADMIN."logs.php");

$handle = DBHandle::get_instance();
$db = DBHandle::get_instance();
$user = $userChildScript = new BOUser();
if (!$user->login()) {
	header("Location: ".ADMIN_URL."login.html");
	exit();
}
$userPerms = $user->get_permissions();
// usefull functionalities index ny name
$fntl_tmp = BOFunctionality::get("name, id");
$fntByName = array();
foreach($fntl_tmp as $fnt)
  $fntByName[$fnt["name"]] = $fnt["id"];
?>
	<link rel="stylesheet" type="text/css" href="css/jquery.dataTables.css">
	
	<!--<script type="text/javascript" language="javascript" src="js/dataTables.fixedHeader.js"></script>-->
	<script type="text/javascript" language="javascript" class="init">
		$(document).ready(function () {
			var table = $('#example').DataTable();
		});
	</script>

	<div class="titleTab">Stats globales </div>
	
<table id="example" class="display item-list" cellspacing="0" width="100%">
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
	// if($_GET['views_date_interval']  == '1' ){
		
		
		function secondsToTime($seconds) {
			$dtF = new DateTime("@0");
			$dtT = new DateTime("@$seconds");
			return $dtF->diff($dtT)->format('%aJ - %hH - %iM');
		}
		
		function returnNbrExclureWEEK($DateEnd,$dateStar){
			$sqlExc   = "SELECT WEEK(  '$DateEnd' ) - WEEK(  '$dateStar' ) NbrJEx ";
			$reqExc   =  mysql_query($sqlExc);
			$dataExc  =  mysql_fetch_object($reqExc);
			return $dataExc->NbrJEx;
		}
		
		
		
		$date_start = $_GET['date_start'].' 00:00:00';
		$date_end 	= $_GET['date_end'].' 23:59:59';
		
		
		
		$sqlSAV  = "SELECT id,`type`, COUNT(`type`) as allType,status
						FROM after_sales_description 
					  WHERE timestamp_created BETWEEN '$date_start' AND '$date_end' GROUP by `type` ORDER by COUNT(`type`) DESC" ;
		$reqSAV	 =  mysql_query($sqlSAV);
		$rowsSAV =  mysql_num_rows($reqSAV);
		
		if($rowsSAV != 0){
			
			
		$sqlAllType  = "SELECT count(`type`) as totalType FROM after_sales_description 
					    WHERE timestamp_created BETWEEN '$date_start' AND '$date_end' GROUP by `type` ";
		$reqAllType  =  mysql_query($sqlAllType);
		while($dataAllType =  mysql_fetch_object($reqAllType)){
			$totalAll += $dataAllType->totalType;
		}
		// echo $totalAll ;
		$CountLigneSAV = 0;
		while($dataSAV  =  mysql_fetch_object($reqSAV)){
			
			if($dataSAV->type == 1) $typeSAV = "SAVD";
			if($dataSAV->type == 2) $typeSAV = "SAVC";
			if($dataSAV->type == 3) $typeSAV = "SAVR";
			if($dataSAV->type == 4) $typeSAV = "SAVI";
			if($dataSAV->type == 5) $typeSAV = "SAVA";
		
		$tauxFirst = ($dataSAV->allType /  $totalAll) * 100;
		$tauxFirst = number_format($tauxFirst, 2, ',', ' ');
		
		$sqlOuvert = "SELECT COUNT(id) as totalOuvert
					   FROM after_sales_description 
					  WHERE status='0' AND `type`='".$dataSAV->type."' AND timestamp_created BETWEEN '$date_start' AND '$date_end' ";
		$reqOuvert =  mysql_query($sqlOuvert);
		$dataOuvert=  mysql_fetch_object($reqOuvert);
		
		$tauxResolu = ($dataOuvert->totalOuvert / $dataSAV->allType) * 100;
		$tauxResolu = number_format($tauxResolu, 2, ',', ' ');
		
		
			$sqlDiff  = "SELECT id , timestamp_created, timestamp_closed, processing_time,type
							FROM  after_sales_description
						 WHERE `type`='".$dataSAV->type."'
						 AND timestamp_created BETWEEN '$date_start' AND '$date_end' 
						 AND status='0' ";
			$reqDiff  =  mysql_query($sqlDiff);
			$rowsDiff =  mysql_num_rows($reqDiff);
			
			if($rowsDiff == 1){
				$dataDiff = mysql_fetch_object($reqDiff);
					
					if(!empty($dataDiff->processing_time)){
						$DiffDate 		 =  secondsToTime($dataDiff->processing_time);
					}else $DiffDate = "";
					
					
					if($dataDiff->type == 1) $typeSAVBB = "SAVD";
					if($dataDiff->type == 2) $typeSAVBB = "SAVC";
					if($dataDiff->type == 3) $typeSAVBB = "SAVR";
					if($dataDiff->type == 4) $typeSAVBB = "SAVI";
					if($dataDiff->type == 5) $typeSAVBB = "SAVA";
				
				$totalDateCalculFirst += $dataDiff->processing_time;
					
			}else{
				$j=0;
				while($dataDiff = mysql_fetch_object($reqDiff)){
					$processing_timeAll += $dataDiff->processing_time;
				
					if($dataDiff->type == 1) $typeSAVBB = "SAVD";
					if($dataDiff->type == 2) $typeSAVBB = "SAVC";
					if($dataDiff->type == 3) $typeSAVBB = "SAVR";
					if($dataDiff->type == 4) $typeSAVBB = "SAVI";
					if($dataDiff->type == 5) $typeSAVBB = "SAVA";
					$j++;				
				}
				
				$processing_timeCC = $processing_timeAll/$j;
				
				if(!empty($processing_timeCC)){
					  $DiffDate = secondsToTime($processing_timeCC);	
				}else $DiffDate = "";
				
			}
	?>
		<tr>
			<td><?= $typeSAV ?></td>
			<td><?= $tauxFirst ?> %</td>
			<td><?= $dataSAV->allType ?></td>
			<td><?= $dataOuvert->totalOuvert ?></td>
			<td><?= $tauxResolu ?> %</td>
		<?php
			if($typeSAVBB == $typeSAV){
				echo "<td>".$DiffDate."</td>";
			}else echo "<td>En cours</td>"; 
		?>
			<td><span id="statsGlobal"  onclick="statsGraphGlobal('<?= $typeSAV ?>')">Voir</span></td>
		</tr>
	<?php 
		$CountLigneSAV++;
		$DiffDate = "";
		$totalTaux += ($dataSAV->allType /  $totalAll)*100;
		$totalDaclaPeriode  += $dataSAV->allType;
		$totalResolu  += $dataOuvert->totalOuvert;
		}
		
		$totalDateCalcul =  ($processing_timeAll + $totalDateCalculFirst) / $totalResolu;
		
		if(!empty($totalDateCalcul)){
			  $totalDateCalcul = secondsToTime($totalDateCalcul);	
		}else $totalDateCalcul = "";
		// $totalDateCalcul = secondsToTime($totalDateCalcul);
		$TotaltauxResolu = ($totalResolu / $totalDaclaPeriode)*100;	
		
		}
	?>	
		<tr>
			<td><strong>TOTAL</strong></td>
			<td><?=  number_format($totalTaux, 2, ',', ' ')  ?> %</td>
			<td><?=  $totalDaclaPeriode ?></td>
			<td><?=  $totalResolu ?></td>
			<td><?=  number_format($TotaltauxResolu, 2, ',', ' ')  ?> %</td>
			<td><?= $totalDateCalcul ?></td>
			<td><span id="statsGlobal" class="textChange-all" onclick="statsGraphGlobal('all')">Voir</span></td>
		</tr>
		
    </tbody>
  </table>
  
  
  
  <script>
  function statsGraphGlobal(typeG){
		
		var startDate = $("#startDate").val();
		var endDate   = $("#endDate").val();
		$("#placeholderGraph").show();
		$("#graphGlobal").show();
		ajaxLoadChart(startDate, endDate,typeG);
		
		if(typeG == "all"){
			$("#txtGraph").text("TOTAL");
		}else{
			$("#txtGraph").text(""+typeG);
		}
	}
  </script>
  
<style>
  .dataTables_wrapper .dataTables_length{
	     margin-bottom: 15px;
  }
  
  #statsGlobal{
	  cursor:pointer;
  }
  #graphGlobal,#example_filter,#example_info {
	  display:none;
  }
  </style>
  
