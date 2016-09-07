<?php
session_start();
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
$db = DBHandle::get_instance();
?>

<div class="section">KPI campagne</div>
<table  class="item-list">
	<thead>
		<tr>
			<th width="16,66%"></th>
			<th width="16,66%">Date création campagne </th>
			<th width="16,66%">Nom campagne </th>
			<th width="16,66%">Appels sortants</th>
			<th width="16,66%">Total a joindre</th>
			<th width="16,66%">Reste à appeler</th>
			<th width="16,66%">A rappeler</th>
			<th width="16,66%">Transformés </th>
			<th width="16,66%">Taux de transformation </th>
			<th width="16,66%">Taux joignabilité</th>
		</tr>
	</thead>
	<tbody>
	<?php
		$date_start  = $_GET['date_start'].' 00:00:00';
		$date_end	 = $_GET['date_end'].' 23:59:59';
		$sql_relance_comm  = "SELECT DISTINCT(csv.assigned_operator) ,bu.name,timestamp_campaign,campaign_name
							  FROM call_spool_vpc csv,bo_users bu
							  WHERE csv.assigned_operator = bu.id
							  AND call_type='3'
							  GROUP BY campaign_name ";						  
		// AND timestamp_first_call  between '$date_start' AND '$date_end'
		$req_relance_comm  =  mysql_query($sql_relance_comm);
        $rows_all		   =  mysql_num_rows($req_relance_comm);			  
		while($data_relance_comm = mysql_fetch_object($req_relance_comm)){
			$sql_total_sortant1 = "SELECT DISTINCT(client_id) as client_id  
								   FROM call_spool_vpc 
								   WHERE assigned_operator='".$data_relance_comm->assigned_operator."'
								   AND call_type='3'
								   AND timestamp_first_call  between '$date_start' AND '$date_end' 
								   AND call_result IN ('absence','call_ok','call_ok_conversion') ";
			      
			$req_total_sortant1  =  mysql_query($sql_total_sortant1);
			$rows_total_sortant1 =  mysql_num_rows($req_total_sortant1);
			
			$sql_total_sortant2 = "SELECT DISTINCT(client_id) as client_id  
								  FROM call_spool_vpc 
								  WHERE assigned_operator='".$data_relance_comm->assigned_operator."'
								  AND call_type='3'
								  AND timestamp_second_call  between '$date_start' AND '$date_end' 
								  AND call_result IN ('absence','call_ok','call_ok_conversion') ";
			
			$req_total_sortant2  =  mysql_query($sql_total_sortant2);
			$rows_total_sortant2 =  mysql_num_rows($req_total_sortant2);
			
			
			$sql_total_sortant3 = "SELECT DISTINCT(client_id) as client_id  
								  FROM call_spool_vpc 
								  WHERE assigned_operator='".$data_relance_comm->assigned_operator."'
								  AND call_type='3'
								  AND timestamp_third_call  between '$date_start' AND '$date_end' 
								  AND call_result IN ('absence','call_ok','call_ok_conversion') ";
			
			$req_total_sortant3  =  mysql_query($sql_total_sortant3);
			$rows_total_sortant3 =  mysql_num_rows($req_total_sortant3);
			$total_sortant_final = $rows_total_sortant1 + $rows_total_sortant2 + $rows_total_sortant3;
			
			$rows_total_camp 		+= $total_sortant_final;
			
			$sql_not_called  = "SELECT DISTINCT(client_id) as client_id 
								FROM call_spool_vpc 
								WHERE call_result='not_called'
								AND assigned_operator='".$data_relance_comm->assigned_operator."'
								AND call_type='3'
								GROUP BY client_id";
			$req_not_called  =  mysql_query($sql_not_called);
			$rows_not_called =  mysql_num_rows($req_not_called);
			$rows_not_called_camp 		+= $rows_not_called;
			
			$sql_absent   = "SELECT DISTINCT(client_id) as client_id 
								FROM call_spool_vpc 
								WHERE call_result='absence'
								AND assigned_operator='".$data_relance_comm->assigned_operator."'
								AND call_type='3'
								AND calls_count < 3
								GROUP BY client_id";
			$req_absent   =  mysql_query($sql_absent);
			$rows_absent  =  mysql_num_rows($req_absent);
			$rows_absent_camp 		+= $rows_absent;
			
			$sql_call_ok  =  "SELECT DISTINCT(client_id) as client_id 
								FROM call_spool_vpc 
								WHERE call_result IN('call_ok_conversion','call_ok')
								AND assigned_operator='".$data_relance_comm->assigned_operator."'
								AND call_type='3'
								AND timestamp_first_call  between '$date_start' AND '$date_end'
								GROUP BY client_id ";
			$req_call_ok  =   mysql_query($sql_call_ok);
			$rows_call_ok =   mysql_num_rows($req_call_ok);
			$rows_call_ok_camp 		+= $rows_call_ok;
			
			$total_joindre  = $rows_not_called + $rows_absent;
			$total_joindre_camp 		+= $total_joindre;
			
			
			$sql_total_contacts  = "SELECT DISTINCT(client_id) as client_id 
									FROM call_spool_vpc
								    WHERE assigned_operator='".$data_relance_comm->assigned_operator."'
									AND call_type='3'
									AND call_result NOT IN('not_called')
									AND timestamp_first_call  between '$date_start' AND '$date_end'
									GROUP BY client_id ";
			$req_total_contacts  =  mysql_query($sql_total_contacts);
			$rows_total_contacts =   mysql_num_rows($req_total_contacts);
			
			$calcul_taux = $rows_call_ok / $rows_total_contacts*100;
		 	$calcul_taux_camp 		+= $calcul_taux;
			
			$sql_transform   = "SELECT DISTINCT(client_id) as client_id 
								FROM call_spool_vpc 
								WHERE call_result IN('call_ok_conversion','call_ok')
								AND assigned_operator='".$data_relance_comm->assigned_operator."'
								AND call_type='3'
								AND calls_count < 3
								GROUP BY client_id";
			$req_transform   =  mysql_query($sql_transform);
			$rows_transform  =  mysql_num_rows($req_transform);
			$rows_transform_camp 		+= $rows_transform;
			
			$taux_transform = $rows_transform / $rows_total_contacts*100;
			$taux_transform_camp += $taux_transform;
			
			$date_com = date('d/m/Y H:i', strtotime(str_replace('-', '/', $data_relance_comm->timestamp_campaign)));
			
	?>
	  <tr >
		<td><?= $data_relance_comm->name ?></td>
		<td><?= $date_com ?></td>
		<td><?= $data_relance_comm->campaign_name ?></td>
		<td><?= $total_sortant_final ?></td>
		<td><?= $total_joindre ?></td>
		<td><?= $rows_not_called ?></td>
		<td><?= $rows_absent ?></td>
		<td><?= $rows_transform ?></td>
		<td><?= number_format($taux_transform, 2, ',', ''); ?> % </td>
		<td><?= number_format($calcul_taux, 2, ',', ''); ?> %</td>
	  </tr>
	<?php } ?>
	<?php 
	if($rows_all > 0){ 
	?>
	  <tr>
		<td><b>Total : </b> </td>
		<td><b> - </b> </td>
		<td><b> - </b> </td>
		<td><b><?= $rows_total_camp ?></b> </td>
		<td><b><?= $total_joindre_camp ?></b> </td>
		<td><b><?= $rows_not_called_camp ?></b> </td>
		<td><b><?= $rows_absent_camp ?></b> </td>
		<td><b><?= $rows_transform_camp ?></b> </td>
		<td><b>-</b> </td>
		<td><b>-</b> </td>
	  </tr>
	<?php }else{ ?>
		<tr>
		<td><b>Total : </b> </td>
		<td><b> - </b> </td>
		<td><b> - </b> </td>
		<td><b>0</b> </td>
		<td><b>0</b> </td>
		<td><b>0</b> </td>
		<td><b>0</b> </td>
		<td><b>0</b> </td>
		<td><b>-</b> </td>
		<td><b>-</b> </td>
	  </tr>
	<?php } ?>
	
	</tbody>
</table>

<style>
 .section {
    background: #333333 url("https://secure-test.techni-contact.com/fr/manager/css/themes/apple_pie/images/ui-bg_highlight-soft_50_dddddd_1x100.png") repeat-x scroll 50% 50%;
    border: 1px solid #333333;
    color: #fffbf3;
    font-weight: bold;
    padding: 3px 5px;
    position: relative;
    text-shadow: 1px 1px 0 #4c3000;
    text-transform: uppercase;
}
.period-text {
    float: left;
    font-size: 15px;
    padding: 30px 0 0 30px;
}
</style>