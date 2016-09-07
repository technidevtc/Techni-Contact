<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
$db = DBHandle::get_instance();
?>

<div class="section">Kpi globaux </div>
<table  class="item-list">
	<thead>
		<tr>
			<th width="16,66%"></th>
			<th width="16,66%">Appels sortants</th>
			<th width="16,66%">Total a joindre</th>
			<th width="16,66%">Reste à appeler</th>
			<th width="16,66%">A rappeler</th>
			<th width="16,66%">Taux joignabilité</th>
		</tr>
	</thead>
	<tbody>
	<?php
	$date_start  = $_GET['date_start'].' 00:00:00';
	$date_end	 = $_GET['date_end'].' 23:59:59';
		$sql_all =  "SELECT DISTINCT(csv.assigned_operator) ,bu.name,call_type 
					 FROM call_spool_vpc csv,bo_users bu 
					 WHERE csv.assigned_operator = bu.id 
					 AND call_type IN('1','2','3','4','5') 
					  
					 GROUP BY assigned_operator";
		//AND timestamp_first_call between '$date_start' AND '$date_end'
		$req_all  =  mysql_query($sql_all);
		$rows_all =  mysql_num_rows($req_all);
		if($rows_all > 0){
		while($data_all = mysql_fetch_object($req_all)){
		
			/***************       KPI relances commerciales Appels sortants    *************************************/
			$sql_total_sortant_kpi_relance1 = "SELECT DISTINCT(client_id) as client_id  
								  FROM call_spool_vpc 
								  WHERE assigned_operator='".$data_all->assigned_operator."'
								  AND call_type='1'
								  AND timestamp_first_call  between '$date_start' AND '$date_end' 
								  AND call_result IN ('absence','call_ok') ";
			
			$req_total_sortant_kpi_relance1  =  mysql_query($sql_total_sortant_kpi_relance1);
			$rows_total_sortant_kpi_relance1 =  mysql_num_rows($req_total_sortant_kpi_relance1);
			
			
			$sql_total_sortant_kpi_relance2 = "SELECT DISTINCT(client_id) as client_id  
								  FROM call_spool_vpc 
								  WHERE assigned_operator='".$data_all->assigned_operator."'
								  AND call_type='1'
								  AND timestamp_second_call  between '$date_start' AND '$date_end' 
								  AND call_result IN ('absence','call_ok') ";
			
			$req_total_sortant_kpi_relance2  =  mysql_query($sql_total_sortant_kpi_relance2);
			$rows_total_sortant_kpi_relance2 =  mysql_num_rows($req_total_sortant_kpi_relance2);
			
			$sql_total_sortant_kpi_relance3 = "SELECT DISTINCT(client_id) as client_id  
								  FROM call_spool_vpc 
								  WHERE assigned_operator='".$data_all->assigned_operator."'
								  AND call_type='1'
								  AND timestamp_third_call  between '$date_start' AND '$date_end' 
								  AND call_result IN ('absence','call_ok') ";
			
			$req_total_sortant_kpi_relance3  =  mysql_query($sql_total_sortant_kpi_relance3);
			$rows_total_sortant_kpi_relance3 =  mysql_num_rows($req_total_sortant_kpi_relance3);
			$total_sortant_final_kpi_relance = $rows_total_sortant_kpi_relance1 + $rows_total_sortant_kpi_relance2 + $rows_total_sortant_kpi_relance3;
			
			$rows_total_kpi_relance_ff 		+= $total_sortant_final_kpi_relance;
			//$total_generale_relance			+= $rows_total_kpi_relance_ff;
			
			
			
			/*************************** Feedback   ******************************************/
			$sql_total_sortant_feed1 = "SELECT DISTINCT(client_id) as client_id  
								  FROM call_spool_vpc 
								  WHERE assigned_operator='".$data_all->assigned_operator."'
								  AND call_type='2'
								  AND timestamp_first_call  between '$date_start' AND '$date_end' 
								  AND call_result IN ('absence','call_ok','call_ok_conversion') ";
			
			$req_total_sortant_feed1  =  mysql_query($sql_total_sortant_feed1);
			$rows_total_sortant_feed1 =  mysql_num_rows($req_total_sortant_feed1);
			
			$sql_total_sortant_feed2 = "SELECT DISTINCT(client_id) as client_id  
								  FROM call_spool_vpc 
								  WHERE assigned_operator='".$data_all->assigned_operator."'
								  AND call_type='2'
								  AND timestamp_second_call  between '$date_start' AND '$date_end' 
								  AND call_result IN ('absence','call_ok','call_ok_conversion') ";
			
			$req_total_sortant_feed2  =  mysql_query($sql_total_sortant_feed2);
			$rows_total_sortant_feed2 =  mysql_num_rows($req_total_sortant_feed2);
			
			
			$sql_total_sortant_feed3 = "SELECT DISTINCT(client_id) as client_id  
								  FROM call_spool_vpc 
								  WHERE assigned_operator='".$data_all->assigned_operator."'
								  AND call_type='2'
								  AND timestamp_third_call  between '$date_start' AND '$date_end' 
								  AND call_result IN ('absence','call_ok','call_ok_conversion') ";
			
			$req_total_sortant_feed3  =  mysql_query($sql_total_sortant_feed3);
			$rows_total_sortant_feed3 =  mysql_num_rows($req_total_sortant_feed3);
			$total_sortant_final_feed = $rows_total_sortant_feed1 + $rows_total_sortant_feed2 + $rows_total_sortant_feed3;
			
			$rows_total_ff_feed 		+= $total_sortant_final_feed;
			
			/*************************** Campagne   ******************************************/
			$sql_total_sortant_camp1 = "SELECT DISTINCT(client_id) as client_id  
								   FROM call_spool_vpc 
								   WHERE assigned_operator='".$data_all->assigned_operator."'
								   AND call_type='3'
								   AND timestamp_first_call  between '$date_start' AND '$date_end' 
								   AND call_result IN ('absence','call_ok','call_ok_conversion') ";
			      
			$req_total_sortant_camp1  =  mysql_query($sql_total_sortant_camp1);
			$rows_total_sortant_camp1 =  mysql_num_rows($req_total_sortant_camp1);
			
			$sql_total_sortant_camp2 = "SELECT DISTINCT(client_id) as client_id  
								  FROM call_spool_vpc 
								  WHERE assigned_operator='".$data_all->assigned_operator."'
								  AND call_type='3'
								  AND timestamp_second_call  between '$date_start' AND '$date_end' 
								  AND call_result IN ('absence','call_ok','call_ok_conversion') ";
			
			$req_total_sortant_camp2  =  mysql_query($sql_total_sortant_camp2);
			$rows_total_sortant_camp2 =  mysql_num_rows($req_total_sortant_camp2);
			
			
			$sql_total_sortant_camp3 = "SELECT DISTINCT(client_id) as client_id  
								  FROM call_spool_vpc 
								  WHERE assigned_operator='".$data_all->assigned_operator."'
								  AND call_type='3'
								  AND timestamp_third_call  between '$date_start' AND '$date_end' 
								  AND call_result IN ('absence','call_ok','call_ok_conversion') ";
			
			$req_total_sortant_camp3  =  mysql_query($sql_total_sortant_camp3);
			$rows_total_sortant_camp3 =  mysql_num_rows($req_total_sortant_camp3);
			$total_sortant_final_camp = $rows_total_sortant_camp1 + $rows_total_sortant_camp2 + $rows_total_sortant_camp3;
			
			$rows_total_camp_camp 		+= $total_sortant_final_camp;
			
			/*************************** RDV   ******************************************/
			$sql_total_sortant_rdv1 = "SELECT DISTINCT(client_id) as client_id  
								  FROM call_spool_vpc 
								  WHERE assigned_operator='".$data_all->assigned_operator."'
								  AND call_type='4'
								  AND timestamp_first_call  between '$date_start' AND '$date_end' 
								  AND call_result IN ('absence','call_ok','call_ok_conversion') ";
			$req_total_sortant_rdv1  =  mysql_query($sql_total_sortant_rdv1);
			$rows_total_sortant_rdv1 =  mysql_num_rows($req_total_sortant_rdv1);
			
			
			$sql_total_sortant_rdv2 = "SELECT DISTINCT(client_id) as client_id  
								  FROM call_spool_vpc 
								  WHERE assigned_operator='".$data_all->assigned_operator."'
								  AND call_type='4'
								  AND timestamp_second_call  between '$date_start' AND '$date_end' 
								  AND call_result IN ('absence','call_ok','call_ok_conversion') ";
			
			$req_total_sortant_rdv2  =  mysql_query($sql_total_sortant_rdv2);
			$rows_total_sortant_rdv2 =  mysql_num_rows($req_total_sortant_rdv2);
			
			
			$sql_total_sortant_rdv3 = "SELECT DISTINCT(client_id) as client_id  
								  FROM call_spool_vpc 
								  WHERE assigned_operator='".$data_all->assigned_operator."'
								  AND call_type='4'
								  AND timestamp_third_call  between '$date_start' AND '$date_end' 
								  AND call_result IN ('absence','call_ok','call_ok_conversion') ";
			
			$req_total_sortant_rdv3  =  mysql_query($sql_total_sortant_rdv3);
			$rows_total_sortant_rdv3 =  mysql_num_rows($req_total_sortant_rdv3);
			$total_sortant_final_rdv = $rows_total_sortant_rdv1 + $rows_total_sortant_rdv2 + $rows_total_sortant_rdv3;
			
			$rows_total_ff_rdv 		+= $total_sortant_final_rdv;
			
			
			/*************************** Requalif Lead   ******************************************/
			$sql_total_sortant_requalif1 = "SELECT DISTINCT(id_contact) as id_contact  
								  FROM call_spool_vpc 
								  WHERE assigned_operator='".$data_all->assigned_operator."'
								  AND call_type='5'
								  AND timestamp_first_call  between '$date_start' AND '$date_end' 
								  AND call_result IN ('absence','call_ok','call_ok_conversion') ";
			$req_total_sortant_requalif1  =  mysql_query($sql_total_sortant_requalif1);
			$rows_total_sortant_requalif1 =  mysql_num_rows($req_total_sortant_requalif1);
			
			
			$sql_total_sortant_requalif2 = "SELECT DISTINCT(id_contact) as id_contact  
								  FROM call_spool_vpc 
								  WHERE assigned_operator='".$data_all->assigned_operator."'
								  AND call_type='5'
								  AND timestamp_second_call  between '$date_start' AND '$date_end' 
								  AND call_result IN ('absence','call_ok','call_ok_conversion') ";
			
			$req_total_sortant_requalif2  =  mysql_query($sql_total_sortant_requalif2);
			$rows_total_sortant_requalif2 =  mysql_num_rows($req_total_sortant_requalif2);
			
			
			$sql_total_sortant_requalif3 = "SELECT DISTINCT(id_contact) as id_contact  
								  FROM call_spool_vpc 
								  WHERE assigned_operator='".$data_all->assigned_operator."'
								  AND call_type='5'
								  AND timestamp_third_call  between '$date_start' AND '$date_end' 
								  AND call_result IN ('absence','call_ok','call_ok_conversion') ";
			
			$req_total_sortant_requalif3  =  mysql_query($sql_total_sortant_requalif3);
			$rows_total_sortant_requalif3 =  mysql_num_rows($req_total_sortant_requalif3);
			$total_sortant_final_requalif = $rows_total_sortant_requalif1 + $rows_total_sortant_requalif2 + $rows_total_sortant_requalif3;
			
			
			$relance_all = $rows_total_kpi_relance_ff+$rows_total_ff_feed+$rows_total_camp_camp+$rows_total_ff_rdv+$total_sortant_final_requalif;
			$relance_all_total += $relance_all;
			/***************    Fin KPI relances commerciales Appels sortants    *************************************/
			
			/***************    KPI    **************************/
			$sql_not_called_relance  = "SELECT id
								FROM call_spool_vpc 
								WHERE call_result='not_called'
								AND assigned_operator='".$data_all->assigned_operator."'
								";
			$req_not_called_relance  =  mysql_query($sql_not_called_relance);
			$rows_not_called_relance =  mysql_num_rows($req_not_called_relance);
			$reste_appeler = $rows_not_called_relance;
			
			$reste_appeler_total += $reste_appeler;
			/***************    Fin KPI    **************************/
			
			/***************     KPI a rappeler   **************************/
			$sql_absent_relance   = "SELECT DISTINCT(client_id) as client_id 
								FROM call_spool_vpc 
								WHERE call_result='absence'
								AND assigned_operator='".$data_all->assigned_operator."'
								AND calls_count < 3
								GROUP BY client_id";
			
			$req_absent_relance   	 =  mysql_query($sql_absent_relance);
			$rows_absent_ff_relance  =  mysql_num_rows($req_absent_relance);
			
			$rows_a_rappeler = $rows_absent_ff_relance;
			$rows_a_rappeler_total += $rows_a_rappeler;
			/***************    Fin KPI a rappeler    **************************/
			
			
			/***************    KPI Total a joindre    **************************/
			
			$total_joindre = $rows_a_rappeler + $reste_appeler;
			$total_joindre_total += $total_joindre;
			/***************    Fin KPI Total a joindre    **************************/
			
			
			
			/***************    Fin KPI Total a joindre    **************************/
			$sql_call_ok_relance  =  "SELECT DISTINCT(client_id) as client_id 
								FROM call_spool_vpc 
								WHERE call_result IN('call_ok_conversion','call_ok')
								AND assigned_operator='".$data_all->assigned_operator."'
								AND timestamp_first_call  between '$date_start' AND '$date_end'
								GROUP BY client_id ";
			$req_call_ok_relance  =   mysql_query($sql_call_ok_relance);
			$rows_call_ok_relance =   mysql_num_rows($req_call_ok_relance);
			$rows_call_ok_ff_relance 		+= $rows_call_ok_relance;
			
			$calcul_taux = $rows_call_ok_ff_relance;
			
			$sql_total_contacts_relance  = "SELECT DISTINCT(client_id) as client_id 
									FROM call_spool_vpc
								    WHERE assigned_operator='".$data_all->assigned_operator."'
									AND call_result NOT IN('not_called')
									AND timestamp_first_call  between '$date_start' AND '$date_end'
									GROUP BY client_id ";
			$req_total_contacts_relance  =  mysql_query($sql_total_contacts_relance);
			$rows_total_contacts_relance =   mysql_num_rows($req_total_contacts_relance);
			
			$rows_total_contacts = $rows_total_contacts_relance;
			
			$taux_final = $calcul_taux / $rows_total_contacts*100;
			$taux_final_total += $taux_final;
			
			/***************    Fin KPI Total a joindre    **************************/
			
			
				echo '<tr>
						<td>'.$data_all->name.'</td>		
						<td>'.$relance_all.'</td>
						<td>'.$total_joindre.'</td>
						<td>'.$reste_appeler.'</td>
						<td>'.$rows_a_rappeler.'</td>
						<td>'.number_format($taux_final, 2, ',', '').' %</td>
					  </tr>';
					  
$relance_all ='';
$rows_total_kpi_relance_ff='';
$rows_total_ff_feed='';
$rows_total_camp_camp='';
$rows_total_ff_rdv='';
$total_sortant_final_requalif='';


$reste_appeler='';
$rows_not_called_ff_relance='';
$rows_not_called_ff_feed='';
$rows_not_called_camp='';
$rows_not_called_ff_rdv='';
$rows_not_called_ff_requalif='';


$rows_a_rappeler ='';
$rows_absent_ff_relance='';
$rows_absent_ff_feed='';
$rows_absent_camp_camp='';
$rows_absent_ff_rdv='';
$rows_absent_ff_requalif='';


$total_joindre =''; 


$calcul_taux =''; 
$rows_call_ok_ff_relance='';
$rows_call_ok_ff_feed='';
$rows_call_ok_camp_camp='';
$rows_call_ok_ff_rdv='';


$rows_call_ok_ff_requalif='';
$rows_total_contacts ='';
$rows_total_contacts_relance='';
$rows_total_contacts_feed='';
$rows_total_contacts_camp='';
$rows_total_contacts_rdv='';
$rows_total_contacts_requalif='';


$taux_final ='';
$calcul_taux='';
$rows_total_contacts='';
		} ?>
	<tr>
		<td><b>Total : </b> </td>	
		<td><b><?= $relance_all_total ?></b> </td>
		<td><b><?= $total_joindre_total ?></b> </td>
		<td><b><?= $reste_appeler_total ?></b> </td>
		<td><b><?= $rows_a_rappeler_total ?></b> </td>
		<td><b>-</b> </td>
	</tr>	
		<?php
		}else {
			echo '<tr>
					<td>Total</td>		
					<td>0</td>
					<td>0</td>
					<td>0</td>
					<td>0</td>
					<td>-</td>
				  </tr>';
		}
	?>
	  
	 
	
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