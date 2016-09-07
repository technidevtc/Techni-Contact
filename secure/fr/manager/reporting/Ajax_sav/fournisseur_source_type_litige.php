<?php
 require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php'; 
 $db = DBHandle::get_instance();
?>
<script type="text/javascript" language="javascript" class="init">
		$(document).ready(function () {
			var table = $('#example6').DataTable();
		});
</script>
<div class="titleTab">Fournisseur source et type de litige   </div>
 <table id="example6" class="display item-list" cellspacing="0" width="100%">
    <thead>
      <tr>
	  <?php	  
			echo '<th> </th>';
			echo '<th>Déclarés sur la période  </th>';			
			echo '<th>SAVD </th>';			
			echo '<th>SAVC</th>';			
			echo '<th>SAVR </th>';			
			echo '<th>SAVI </th>';			
			echo '<th>SAVA</th>';			
	  ?>
		</tr>
    </thead>
    <tbody>
	<?php
		$date_start = $_GET['date_start'].' 00:00:00';
		$date_end 	= $_GET['date_end'].' 23:59:59';
	
		$sqlFourni  = "SELECT nom1 , id_supplier
					    FROM after_sales_description asd, advertisers aa
					   WHERE asd.id_supplier = aa.id
						AND timestamp_created BETWEEN '$date_start' AND '$date_end'
					   GROUP BY id_supplier ";
		$reqFourni  =  mysql_query($sqlFourni);
		$rowsFourni =  mysql_num_rows($reqFourni);
		if($rowsFourni > 0){
		$i=0;
		while($dataFourni = mysql_fetch_object($reqFourni)){
			
		$sqlFirstTtt  = "SELECT COUNT(`type`) as allType
							FROM after_sales_description
						 WHERE id_supplier='".$dataFourni->id_supplier."' 
						 AND timestamp_created BETWEEN '$date_start' AND '$date_end' ";
		$reqFirstTtt  =  mysql_query($sqlFirstTtt);
		$dataFirstTtt =  mysql_fetch_object($reqFirstTtt);
		
		$sqlSAVD 	= "SELECT COUNT(id) as totalD
						   FROM  after_sales_description
					   WHERE id_supplier='".$dataFourni->id_supplier."' 
					   AND timestamp_created BETWEEN '$date_start' AND '$date_end'
					   AND type='1' ";
		$reqSAVD    =  mysql_query($sqlSAVD);
		$dataSAVD	=  mysql_fetch_object($reqSAVD);
		
		$sqlSAVC 	= "SELECT COUNT(id) as totalC
						   FROM  after_sales_description
					   WHERE id_supplier='".$dataFourni->id_supplier."' 
					   AND timestamp_created BETWEEN '$date_start' AND '$date_end'
					   AND type='2' ";
		$reqSAVC    =  mysql_query($sqlSAVC);
		$dataSAVC	=  mysql_fetch_object($reqSAVC);
		
		$sqlSAVR 	= "SELECT COUNT(id) as totalR
						   FROM  after_sales_description
					   WHERE id_supplier='".$dataFourni->id_supplier."' 
					   AND timestamp_created BETWEEN '$date_start' AND '$date_end'
					   AND type='3' ";
		$reqSAVR    =  mysql_query($sqlSAVR);
		$dataSAVR	=  mysql_fetch_object($reqSAVR);
		
		$sqlSAVI 	= "SELECT COUNT(id) as totalI
						   FROM  after_sales_description
					   WHERE id_supplier='".$dataFourni->id_supplier."' 
					   AND timestamp_created BETWEEN '$date_start' AND '$date_end'
					   AND type='4' ";
		$reqSAVI    =  mysql_query($sqlSAVI);
		$dataSAVI	=  mysql_fetch_object($reqSAVI);
		
		$sqlSAVA 	= "SELECT COUNT(id) as totalA
						   FROM  after_sales_description
					   WHERE id_supplier='".$dataFourni->id_supplier."' 
					   AND timestamp_created BETWEEN '$date_start' AND '$date_end'
					   AND type='5' ";
		$reqSAVA    =  mysql_query($sqlSAVA);
		$dataSAVA	=  mysql_fetch_object($reqSAVA);
	?>
		<tr>
			<td><?= $dataFourni->nom1 ?></td>
			<td><?= $dataFirstTtt->allType  ?></td>
			<td><?= $dataSAVD->totalD ?></td>
			<td><?= $dataSAVC->totalC ?></td>
			<td><?= $dataSAVR->totalR ?></td>
			<td><?= $dataSAVI->totalI ?></td>
			<td><?= $dataSAVA->totalA ?></td>
		</tr>
		<?php 
		$totalDaclaPeriodeRes  	+=  $dataFirstTtt->allType;
		$totalSAVD  		+=  $dataSAVD->totalD;
		$totalSAVC  		+=  $dataSAVC->totalC;
		$totalSAVR  		+=  $dataSAVR->totalR;
		$totalSAVI  		+=  $dataSAVI->totalI;
		$totalSAVA  		+=  $dataSAVA->totalA;
		}
		}
		?>
	</tbody>
	
	<tr>
		<td><strong>TOTAL</strong></td>
		<td><?= $totalDaclaPeriodeRes ?></td>
		<td><?= $totalSAVD ?></td>
		<td><?= $totalSAVC ?></td>
		<td><?= $totalSAVR ?></td>
		<td><?= $totalSAVI ?></td>
		<td><?= $totalSAVA ?></td>
	</tr>
  </table>
	
 <style>
 #example6_length, #example6_paginate{
	 display:none;
 }
 
 </style>