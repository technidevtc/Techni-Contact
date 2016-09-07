<?php
/*================================================================/

 Techni-Contact V4 - MD2I SAS
 http://www.techni-contact.com

 Auteur : OD pour Hook Network SARL - http://www.hook-network.com
 Date de création : 14/9/2011

 Mises à jour :

 Fichier : /secure/manager/reporting/rejected-leads.php
 Description : Tableau de reporting des leads rejetés

/=================================================================*/

if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

$title = 'KPI pile commerciaux';
$navBar = $title;

require(ADMIN . 'head.php');
//if ((!$userPerms->has($fntByName["m-comm--sm-pile-appel-personaliser"], "re")) || (!$userPerms->has($fntByName["m-comm--sm-pile-appels-complete"], "re")) ) {
if ((!$userChildScript->get_permissions()->has("m-comm--sm-pile-appel-personaliser","r")) && (!$userChildScript->get_permissions()->has("m-comm--sm-pile-appels-complete","r")) ) {
    print "Vous n'avez pas les droits adéquats pour réaliser cette opération";
    exit();
  }

$errorstring = "";

define('NB', 30);
define("__BEGIN_TIME__", mktime(0,0,0,6,15,2011));

// time interval
$yearS = isset($_GET['yearS']) ? (int)trim($_GET['yearS']) : date("Y");
$monthS = isset($_GET['monthS']) ? (int)trim($_GET['monthS']) : date("m");
$dayS = isset($_GET['dayS']) ? (int)trim($_GET['dayS']) : date("d");
$yearS2 = isset($_GET['yearS2']) ? (int)trim($_GET['yearS2']) : date("Y");
$monthS2 = isset($_GET['monthS2']) ? (int)trim($_GET['monthS2']) : date("m");
$dayS2 = isset($_GET['dayS2']) ? (int)trim($_GET['dayS2']) : date("d");
$yearE = isset($_GET['yearE']) ? (int)trim($_GET['yearE']) : date("Y");
$monthE = isset($_GET['monthE']) ? (int)trim($_GET['monthE']) : date("m");
$dayE = isset($_GET['dayE']) ? (int)trim($_GET['dayE']) : date("d");

$originList = Lead::getOriginList();
unset($originList['internaute']);
unset($originList['probance']);
unset($originList['chat']);
?>

<script type="text/javascript">
// date form
var COMMON_ALL_M = "Tous";
var COMMON_ALL_F = "Toutes";
var COMMON_ALL_CHOICE = "COMMON_ALL_CHOICE";

var MonthLabels = new Array('janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre');
var DayLabes = new Array('Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi');

var dateBegin = new Date(); dateBegin.setTime(<?php echo __BEGIN_TIME__ ?>*1000);
var dateCur   = new Date();

function FillYearOptions(yID, mID, dID)
{
	var y = $('#'+yID)[0];
	yb = parseInt(dateBegin.getFullYear());
	yc = parseInt(dateCur.getFullYear());
	y.options.length = (yc-yb) + 2;

	y.options[0].value = 0;
	y.options[0].text  = COMMON_ALL_F;
	for (var i = 1; i < y.options.length; i++)
	{
		y.options[y.options.length-i].value = yb + i - 1;
		y.options[y.options.length-i].text  = yb + i - 1;
	}
	FillMonthOptions(yID, mID, dID);
}

function FillMonthOptions(yID, mID, dID){
	var y = $('#'+yID)[0];
	var m = $('#'+mID)[0];
	var yearSelectedIndex = y.options.selectedIndex;
	var year = $(y.options[yearSelectedIndex]).val();

	if (year == 0) m.options.length = 1;
	else if (year < dateCur.getFullYear()) m.options.length = 13;
	else m.options.length = dateCur.getMonth() + 2;

	m.options[0].value = 0;
	m.options[0].text  = COMMON_ALL_M;
	for (var i = 1; i < m.options.length; i++)
	{
		m.options[i].value = i;
		m.options[i].text  = MonthLabels[i-1];
	}
        if(year == dateBegin.getFullYear())
          for (var j = 1; j <= dateBegin.getMonth(); j++){
            $('option[value='+j+']').remove();
          }

	FillDayOptions(yID, mID, dID);
}

function FillDayOptions(yID, mID, dID)
{
	var y = $('#'+yID)[0];
	var m = $('#'+mID)[0];
	var d = $('#'+dID)[0];

	var year  = parseInt(y.value);
        var month = parseInt(m.value);

	if (year == parseInt(dateCur.getFullYear()) && month == parseInt(dateCur.getMonth()+1))
	{
		var date = new Date(dateCur);
		d.options.length = date.getDate() + 1;
	}
	else
	{
		var date = new Date(year, month, 0);
		if (month == 0) d.options.length = 1;
		else d.options.length = date.getDate() + 1;
	}

	d.options.value = 0;
	d.options.text  = COMMON_ALL_M;
	for (var i = 1; i < d.options.length; i++)
	{
		date.setDate(i);
		d.options[i].value = i;
		d.options[i].text  = DayLabes[date.getDay()] + " " + i;
	}
}

function FillYearOptions2(yID, mID, dID)
{
	var y = $('#'+yID)[0];
	yb = parseInt(dateBegin.getFullYear());
	yc = parseInt(dateCur.getFullYear());
	y.options.length = (yc-yb) + 2;

	y.options[0].value = 0;
	y.options[0].text  = " - ";
	for (var i = 1; i < y.options.length; i++)
	{
		y.options[y.options.length-i].value = yb + i - 1;
		y.options[y.options.length-i].text  = yb + i - 1;
	}
	FillMonthOptions2(yID, mID, dID);
}

function FillMonthOptions2(yID, mID, dID)
{
	var y = $('#'+yID)[0];
	var m = $('#'+mID)[0];
	var year = parseInt(y.options[y.options.selectedIndex].value);

	if (year == 0) m.options.length = 1;
	else if (year < dateCur.getFullYear()) m.options.length = 13;
	else m.options.length = dateCur.getMonth() + 2;

	m.options[0].value = 0;
	m.options[0].text  = " - ";
	for (var i = 1; i < m.options.length; i++)
	{
		m.options[i].value = i;
		m.options[i].text  = MonthLabels[i-1];
	}
	FillDayOptions2(yID, mID, dID);
}

function FillDayOptions2(yID, mID, dID)
{
	var y = $('#'+yID)[0];
	var m = $('#'+mID)[0];
	var d = $('#'+dID)[0];
	var year  = parseInt(y.value);
	var month = parseInt(m.value);

	if (year == parseInt(dateCur.getFullYear()) && month == parseInt(dateCur.getMonth()+1))
	{
		var date = new Date(dateCur);
		d.options.length = date.getDate() + 1;
	}
	else
	{
		var date = new Date(year, month, 0);
		if (month == 0) d.options.length = 1;
		else d.options.length = date.getDate() + 1;
	}

	d.options.value = 0;
	d.options.text  = " - ";
	for (var i = 1; i < d.options.length; i++)
	{
		date.setDate(i);
		d.options[i].value = i;
		d.options[i].text  = DayLabes[date.getDay()] + " " + i;
	}
}

function SetDateOptions(yid, mid, did, year, month, day)
{
	$('#'+yid)[0].value = year;
	$('#'+yid)[0].onchange();
	$('#'+mid)[0].value = month;
	$('#'+mid)[0].onchange();
	$('#'+did)[0].value = day;
}

function ShowDateSection(){
	$("#views_date_interval").val('0');
	document.getElementById('DateFilter').style.display = "block";
	document.getElementById('DateIntervalFilter').style.display = "none";
        $('input[name=dateFilterType]').val("simple") ;
}

function ShowDateIntervalSection(){
	$("#views_date_interval").val('1');
	document.getElementById('DateFilter').style.display = "none";
	document.getElementById('DateIntervalFilter').style.display = "block";
        $('input[name=dateFilterType]').val("interval");
}

</script>
<link type="text/css" rel="stylesheet" href="<?php echo ADMIN_URL ?>ressources/css/command.css">
<link rel="stylesheet" type="text/css" href="reporting.css" />

<div class="titreStandard">Call / VPC</div>
<br />
<div class="bg" style="min-width: 980px">
<style type="text/css">
#DateFilter { display: none; float: left; height: 80px; width: 780px;}
#DateIntervalFilter { display: none;  float: left;height: 140px; width: 780px;}
#filtre_status{margin-top: 60px; width: 510px;}
.commandesForm { font-family: Arial,Helvetica,sans-serif;font-size: 12px;}
fieldset { margin: 0 0 5px; padding: 4px 8px 8px; border: 2px groove threedface ; width: 400px; }
legend { margin: 0 2px; font-weight: bold; font-size: 15px; }
</style>

<div class="centre">

<form id="commandesList" class="commandesForm" action="" method="get" style="display:inline-block">
              <input type="hidden" id="views_date_interval" value="0"/>
              
              <br/>
              <div id="DateFilter">
                <div style="float:left">
                      <fieldset class="date-picker">
                              <legend>Interval prédéfini :</legend>
                              Année : <select name="yearS" id="YearID" onchange="FillMonthOptions(this.id, 'MonthID', 'DayID');"></select>
                              Mois : <select name="monthS" id="MonthID" onchange="FillDayOptions('YearID', this.id, 'DayID');"></select>
                              Jour : <select name="dayS" id="DayID"></select>
                              <br/>
                      </fieldset>
                      <input type="button" value="Choisir un interval de temps" onclick="ShowDateIntervalSection()">
                      <input type="button" value="OK"  onClick="send_repporting();return false;" />
                      </div>
                      <div class="zero"></div>
              </div>
              <div id="DateIntervalFilter">
                <div style="float:left">
                      <fieldset>
                              <legend>Date de début :</legend>
                              Année :
                              <select name="yearS2" id="YearSID" onchange="FillMonthOptions2(this.id, 'MonthSID', 'DaySID');">
                              </select>
                              Mois :
                              <select name="monthS2" id="MonthSID" onchange="FillDayOptions2('YearSID', this.id, 'DaySID');">
                              </select>
                              Jour :
                              <select name="dayS2" id="DaySID">
                              </select>
                      </fieldset>
                      <fieldset>
                              <legend>Date de Fin :</legend>
                              Année :
                              <select name="yearE" id="YearEID" onchange="FillMonthOptions2(this.id, 'MonthEID', 'DayEID');">
                              </select>
                              Mois :
                              <select name="monthE" id="MonthEID" onchange="FillDayOptions2('YearEID', this.id, 'DayEID');">
                              </select>
                              Jour :
                              <select name="dayE" id="DayEID">
                              </select>
                      </fieldset>
                      <script type="text/javascript">/* <![CDATA[ */
                      FillYearOptions('YearID', 'MonthID', 'DayID');
                      SetDateOptions('YearID', 'MonthID', 'DayID', '<?php echo $yearS ?>','<?php echo $monthS ?>','<?php echo $dayS ?>');
                      FillYearOptions2('YearSID', 'MonthSID', 'DaySID');
                      FillYearOptions2('YearEID', 'MonthEID', 'DayEID');
                      SetDateOptions('YearSID', 'MonthSID', 'DaySID', '<?php echo $yearS ?>','<?php echo $monthS ?>','<?php echo $dayS ?>');
                      SetDateOptions('YearEID', 'MonthEID', 'DayEID', '<?php echo $yearE ?>','<?php echo $monthE ?>','<?php echo $dayE ?>');
//                      getInfos();
                      /* ]]>*/ </script>
                      <input type="button" value="Choisir une date simple" onclick="ShowDateSection()">
                      <input type="button" value="OK"  onClick="send_repporting();return false;" />
                 </div>
                <div class="zero"></div>
              </div>
          <div class="zero"></div>
      </form>
      
 
      <script type="text/javascript">/* <![CDATA[ */<?php  echo ($dateFilterType == "interval") ? "ShowDateIntervalSection();" : "ShowDateSection();" ?>/* ]]> */</script>

<br/>
<br/>

<?php
	//Global array count 
	$global_array_count	= Array();
if(isset($_GET['date_start'])){
		$date_start  = $_GET['date_start'].' 00:00:00';
		$date_end	 = $_GET['date_end'].' 23:59:59';
	}else {
		$date_start  = date('Y-m-d').' 00:00:00';
		$date_end	 = date('Y-m-d').' 23:59:59';
}
	
?>

<div id="row_total_array">

</div>
<br /><br />
<div id="kpi_requalif_lead1">

<div class="section">Requalif lead</div>
<table  class="item-list">
	<thead>
		<tr>
			<th width="16,66%"></th>
			<th width="16,66%">Appels sortants</th>
			<th width="16,66%">Total a joindre</th>
			<th width="16,66%">Reste à appeler</th>
			<th width="16,66%">A rappeler</th>
			<!--<th width="16,66%">Taux joignabilité</th>-->
		</tr>
	</thead>
	<tbody>
	<?php
		
		$sql_relance_comm  = "SELECT DISTINCT(csv.assigned_operator) ,UPPER(bu.name) AS name,bu.id
							  FROM call_spool_vpc csv,bo_users bu
							  WHERE csv.assigned_operator = bu.id
							  AND call_type='5' ";
		//AND timestamp_first_call  between '$date_start' AND '$date_end'
		$req_relance_comm  =  mysql_query($sql_relance_comm);
        $rows_all		   =  mysql_num_rows($req_relance_comm);			  
		while($data_relance_comm = mysql_fetch_object($req_relance_comm)){
			$sql_total_sortant1 = "SELECT DISTINCT(id_contact) as id_contact  
								  FROM call_spool_vpc 
								  WHERE assigned_operator='".$data_relance_comm->assigned_operator."'
								  AND call_type='5'
								  AND timestamp_first_call  between '$date_start' AND '$date_end' 
								  AND call_result IN ('absence','call_ok','call_ok_conversion') ";
			$req_total_sortant1  =  mysql_query($sql_total_sortant1);
			$rows_total_sortant1 =  mysql_num_rows($req_total_sortant1);
			
			$sql_total_sortant2 = "SELECT DISTINCT(id_contact) as id_contact  
								  FROM call_spool_vpc 
								  WHERE assigned_operator='".$data_relance_comm->assigned_operator."'
								  AND call_type='5'
								  AND timestamp_second_call  between '$date_start' AND '$date_end' 
								  AND call_result IN ('absence','call_ok','call_ok_conversion') ";
			
			$req_total_sortant2  =  mysql_query($sql_total_sortant2);
			$rows_total_sortant2 =  mysql_num_rows($req_total_sortant2);
			
			
			$sql_total_sortant3 = "SELECT DISTINCT(id_contact) as id_contact  
								  FROM call_spool_vpc 
								  WHERE assigned_operator='".$data_relance_comm->assigned_operator."'
								  AND call_type='5'
								  AND timestamp_third_call  between '$date_start' AND '$date_end' 
								  AND call_result IN ('absence','call_ok','call_ok_conversion') ";
			
			$req_total_sortant3  =  mysql_query($sql_total_sortant3);
			$rows_total_sortant3 =  mysql_num_rows($req_total_sortant3);
			$total_sortant_final = $rows_total_sortant1 + $rows_total_sortant2 + $rows_total_sortant3;
			
			$rows_total_ff_requalif 		+= $total_sortant_final;
			
			
			$sql_not_called  = "SELECT DISTINCT(id_contact) as id_contact 
								FROM call_spool_vpc 
								WHERE call_result='not_called'
								AND assigned_operator='".$data_relance_comm->assigned_operator."'
								AND call_type='5'
								GROUP BY id_contact";
			$req_not_called  =  mysql_query($sql_not_called);
			$rows_not_called =  mysql_num_rows($req_not_called);
			
			$rows_not_called_ff_requalif 		+= $rows_not_called;
			
			$sql_absent   = "SELECT DISTINCT(id_contact) as id_contact 
								FROM call_spool_vpc 
								WHERE call_result='absence'
								AND assigned_operator='".$data_relance_comm->assigned_operator."'
								AND call_type='5'
								AND calls_count < 3
								GROUP BY id_contact";
			$req_absent   =  mysql_query($sql_absent);
			$rows_absent  =  mysql_num_rows($req_absent);
			$rows_absent_ff_requalif 		+= $rows_absent;
			
			$sql_call_ok  =  "SELECT DISTINCT(id_contact) as id_contact 
								FROM call_spool_vpc 
								WHERE call_result IN('call_ok_conversion','call_ok')
								AND assigned_operator='".$data_relance_comm->assigned_operator."'
								AND call_type='5'
								AND timestamp_first_call  between '$date_start' AND '$date_end'
								GROUP BY id_contact ";
			$req_call_ok  =   mysql_query($sql_call_ok);
			$rows_call_ok =   mysql_num_rows($req_call_ok);
			$rows_call_ok_ff_requalif 		+= $rows_call_ok;
			
			$total_joindre  = $rows_not_called + $rows_absent;
			$total_joindre_ff_requalif 		+= $total_joindre;
			
			
			$sql_total_contacts  = "SELECT DISTINCT(id_contact) as id_contact 
									FROM call_spool_vpc
								    WHERE assigned_operator='".$data_relance_comm->assigned_operator."'
									AND call_result NOT IN('not_called')
									AND call_type='5'
									AND timestamp_first_call  between '$date_start' AND '$date_end'
									GROUP BY id_contact ";
			$req_total_contacts  =  mysql_query($sql_total_contacts);
			$rows_total_contacts =   mysql_num_rows($req_total_contacts);
			
			$calcul_taux = $rows_call_ok / $rows_total_contacts*100;
			
			
		 	$calcul_taux_ff_requalif 		+= $calcul_taux;
			
			
	?>
	  <tr>
		<td><?= $data_relance_comm->name ?></td>		
		<td><?= $total_sortant_final ?></td>
		<td><?= $total_joindre ?></td>
		<td><?= $rows_not_called ?></td>
		<td><?= $rows_absent ?></td>
		</tr>
	
	
	<?php 
		//For the global array count
		//Test if we have a record (make the addition)
		//Else add it to the array
		if(in_array($data_relance_comm->id, array_column($global_array_count, 0))){
		//if(array_search($data_relance_comm->id, array_column($global_array_count, 0), false)){
			
		//Inrement the old values with the new one's
			$array_index	= array_search($data_relance_comm->id, array_column($global_array_count, 0));

			$global_array_count[$array_index][2]	= $global_array_count[$array_index][2] + $total_sortant_final;
			$global_array_count[$array_index][3]	= $global_array_count[$array_index][3] + $total_joindre;
			$global_array_count[$array_index][4]	= $global_array_count[$array_index][4] + $rows_not_called;
			$global_array_count[$array_index][5]	= $global_array_count[$array_index][5] + $rows_absent;
			$global_array_count[$array_index][6]	= $global_array_count[$array_index][6] + $calcul_taux;
		}else{
			$array_temp	= array($data_relance_comm->id, $data_relance_comm->name, $total_sortant_final, $total_joindre, $rows_not_called, $rows_absent,$calcul_taux);
			array_push($global_array_count, $array_temp);

		}		
		
	} 	// END WHILE	
	
	if($rows_all > 0){ 
	?>
	  <tr>
		<td><b>Total : </b> </td>	
		<td><b><?= $rows_total_ff_requalif ?></b> </td>
		<td><b><?= $total_joindre_ff_requalif ?></b> </td>
		<td><b><?= $rows_not_called_ff_requalif ?></b> </td>
		<td><b><?= $rows_absent_ff_requalif ?></b> </td>
		
	  </tr>
	<?php }else{ ?>
		<tr>
		<td><b>Total : </b> </td>
		<td><b>0</b> </td>
		<td><b>0</b> </td>
		<td><b>0</b> </td>
		<td><b>0</b> </td>
		
	  </tr>
	<?php } ?>
	
	</tbody>
</table>


</div>
<br /><br />


<div id="relances_commerciales1">
<div class="section">Relance devis</div>
<table  class="item-list">
	<thead>
		<tr>
			<th width="16,66%"></th>
			<th width="16,66%">Appels sortants</th>
			<th width="16,66%">Total a joindre</th>
			<th width="16,66%">Reste à appeler</th>
			<th width="16,66%">A rappeler</th>
		
		</tr>
	</thead>
	<tbody>
	<?php
	
		$sql_relance_comm  = "SELECT DISTINCT(csv.assigned_operator) ,UPPER(bu.name) AS name,bu.id
							  FROM call_spool_vpc csv,bo_users bu
							  WHERE csv.assigned_operator = bu.id
							  AND call_type='1'
							 ";
		// AND timestamp_first_call  between '$date_start' AND '$date_end' 
		$req_relance_comm  =  mysql_query($sql_relance_comm);
        $rows_all		   =  mysql_num_rows($req_relance_comm);
		$i=0;	
		while($data_relance_comm = mysql_fetch_object($req_relance_comm)){
			$sql_total_sortant1 = "SELECT DISTINCT(client_id) as client_id  
								  FROM call_spool_vpc 
								  WHERE assigned_operator='".$data_relance_comm->assigned_operator."'
								  AND call_type='1'
								  AND timestamp_first_call  between '$date_start' AND '$date_end' 
								  AND call_result IN ('absence','call_ok') ";
			
			$req_total_sortant1  =  mysql_query($sql_total_sortant1);
			$rows_total_sortant1 =  mysql_num_rows($req_total_sortant1);
			
			
			$sql_total_sortant2 = "SELECT DISTINCT(client_id) as client_id  
								  FROM call_spool_vpc 
								  WHERE assigned_operator='".$data_relance_comm->assigned_operator."'
								  AND call_type='1'
								  AND timestamp_second_call  between '$date_start' AND '$date_end' 
								  AND call_result IN ('absence','call_ok') ";
			
			$req_total_sortant2  =  mysql_query($sql_total_sortant2);
			$rows_total_sortant2 =  mysql_num_rows($req_total_sortant2);
			
			
			$sql_total_sortant3 = "SELECT DISTINCT(client_id) as client_id  
								  FROM call_spool_vpc 
								  WHERE assigned_operator='".$data_relance_comm->assigned_operator."'
								  AND call_type='1'
								  AND timestamp_third_call  between '$date_start' AND '$date_end' 
								  AND call_result IN ('absence','call_ok') ";
			
			$req_total_sortant3  =  mysql_query($sql_total_sortant3);
			$rows_total_sortant3 =  mysql_num_rows($req_total_sortant3);
			
			$total_sortant_final = $rows_total_sortant1 + $rows_total_sortant2 + $rows_total_sortant3;
			
			$rows_total_ff_relance 		+= $total_sortant_final;
			
			//GROUP BY client_id
			//DISTINCT(client_id) as
			$sql_not_called  = "SELECT  id 
								FROM call_spool_vpc 
								WHERE call_result='not_called'
								AND assigned_operator='".$data_relance_comm->assigned_operator."'
								AND call_type='1'
								";
			$req_not_called  =  mysql_query($sql_not_called);
			$rows_not_called =  mysql_num_rows($req_not_called);
			$rows_not_called_ff_relance 		+= $rows_not_called;
			// echo $sql_not_called.'<br />';
			$sql_absent   = "SELECT DISTINCT(client_id) as client_id 
								FROM call_spool_vpc 
								WHERE call_result='absence'
								AND assigned_operator='".$data_relance_comm->assigned_operator."'
								AND call_type='1'
								AND calls_count < 3
								GROUP BY client_id";
			
			$req_absent   =  mysql_query($sql_absent);
			$rows_absent  =  mysql_num_rows($req_absent);
			$rows_absent_ff_relance 		+= $rows_absent;
			
			$sql_call_ok  =  "SELECT DISTINCT(client_id) as client_id 
								FROM call_spool_vpc 
								WHERE call_result IN('call_ok_conversion','call_ok')
								AND assigned_operator='".$data_relance_comm->assigned_operator."'
								AND call_type='1'
								AND timestamp_first_call  between '$date_start' AND '$date_end'
								GROUP BY client_id ";
			$req_call_ok  =   mysql_query($sql_call_ok);
			$rows_call_ok =   mysql_num_rows($req_call_ok);
			$rows_call_ok_ff_relance 		+= $rows_call_ok;
			
			$total_joindre  = $rows_not_called + $rows_absent;
			$total_joindre_ff_relance		+= $total_joindre;
			
			
			$sql_total_contacts  = "SELECT DISTINCT(client_id) as client_id 
									FROM call_spool_vpc
								    WHERE assigned_operator='".$data_relance_comm->assigned_operator."'
									AND call_type='1'
									AND call_result NOT IN('not_called')
									AND timestamp_first_call  between '$date_start' AND '$date_end'
									GROUP BY client_id ";
			$req_total_contacts  =  mysql_query($sql_total_contacts);
			$rows_total_contacts =   mysql_num_rows($req_total_contacts);
			
			//echo $rows_call_ok.' / '.$rows_total_contacts.'<br />';
			
			$calcul_taux = ($rows_call_ok / $rows_total_contacts)*100;
		 	$calcul_taux_ff 		+= $calcul_taux;
		?>
		  <tr>
			<td><?= $data_relance_comm->name ?></td>		
			<td><?= $total_sortant_final ?></td>
			<td><?= $total_joindre ?></td>
			<td><?= $rows_not_called ?></td>
			<td><?= $rows_absent ?></td>
			
		  </tr>
		<?php
		//Test if we have a record (make the addition)
		//Else add it to the array
		if(in_array($data_relance_comm->id, array_column($global_array_count, 0))){
		//if(array_search($data_relance_comm->id, array_column($global_array_count, 0), false)){
		
			//Inrement the old values with the new one's
			$array_index	= array_search($data_relance_comm->id, array_column($global_array_count, 0));
			$global_array_count[$array_index][2]	= $global_array_count[$array_index][2] + $total_sortant_final;
			$global_array_count[$array_index][3]	= $global_array_count[$array_index][3] + $total_joindre;
			$global_array_count[$array_index][4]	= $global_array_count[$array_index][4] + $rows_not_called;
			$global_array_count[$array_index][5]	= $global_array_count[$array_index][5] + $rows_absent;
			$global_array_count[$array_index][6]	= $global_array_count[$array_index][6] + $calcul_taux;
		}else{
			$array_temp	= array($data_relance_comm->id, $data_relance_comm->name, $total_sortant_final, $total_joindre, $rows_not_called, $rows_absent,$calcul_taux);
			array_push($global_array_count, $array_temp);

		}		
		
	}//End while	
	
	
	if($rows_all > 0){ 
	?>
	  <tr>
		<td><b>Total : </b> </td>	
		<td><b><?= $rows_total_ff_relance ?></b> </td>
		<td><b><?= $total_joindre_ff_relance ?></b> </td>
		<td><b><?= $rows_not_called_ff_relance ?></b> </td>
		<td><b><?= $rows_absent_ff_relance ?></b> </td>
		
	  </tr>
	<?php }else{ ?>
		<tr>
		<td><b>Total : </b> </td>
		<td><b>0</b> </td>
		<td><b>0</b> </td>
		<td><b>0</b> </td>
		<td><b>0</b> </td>
		
	  </tr>
	<?php } ?>
	
	</tbody>
</table>

</div>
<br /><br />


<div id="feedback_livraison1">


<div class="section">KPI feedback livraison</div>
<table  class="item-list">
	<thead>
		<tr>
			<th width="16,66%"></th>
			<th width="16,66%">Appels sortants</th>
			<th width="16,66%">Total a joindre</th>
			<th width="16,66%">Reste à appeler</th>
			<th width="16,66%">A rappeler</th>
			
		</tr>
	</thead>
	<tbody>
	<?php
		//$date_start  = $_GET['date_start'].' 00:00:00';
		//$date_end	 = $_GET['date_end'].' 23:59:59';
		$sql_relance_comm  = "SELECT DISTINCT(csv.assigned_operator) ,UPPER(bu.name) AS name,bu.id
							  FROM call_spool_vpc csv,bo_users bu
							  WHERE csv.assigned_operator = bu.id
							  AND call_type='2' ";
		//AND timestamp_first_call  between '$date_start' AND '$date_end'			  
		$req_relance_comm  =  mysql_query($sql_relance_comm);
		$rows_all		   =  mysql_num_rows($req_relance_comm);
		while($data_relance_comm = mysql_fetch_object($req_relance_comm)){
			$sql_total_sortant1 = "SELECT DISTINCT(client_id) as client_id  
								  FROM call_spool_vpc 
								  WHERE assigned_operator='".$data_relance_comm->assigned_operator."'
								  AND call_type IN ('2','6')
								  AND timestamp_first_call  between '$date_start' AND '$date_end' 
								  AND call_result IN ('absence','call_ok','call_ok_conversion') ";
			
			$req_total_sortant1  =  mysql_query($sql_total_sortant1);
			$rows_total_sortant1 =  mysql_num_rows($req_total_sortant1);
			
			$sql_total_sortant2 = "SELECT DISTINCT(client_id) as client_id  
								  FROM call_spool_vpc 
								  WHERE assigned_operator='".$data_relance_comm->assigned_operator."'
								  AND call_type IN ('2','6')
								  AND timestamp_second_call  between '$date_start' AND '$date_end' 
								  AND call_result IN ('absence','call_ok','call_ok_conversion') ";
			
			$req_total_sortant2  =  mysql_query($sql_total_sortant2);
			$rows_total_sortant2 =  mysql_num_rows($req_total_sortant2);
			
			
			$sql_total_sortant3 = "SELECT DISTINCT(client_id) as client_id  
								  FROM call_spool_vpc 
								  WHERE assigned_operator='".$data_relance_comm->assigned_operator."'
								  AND call_type IN ('2','6')
								  AND timestamp_third_call  between '$date_start' AND '$date_end' 
								  AND call_result IN ('absence','call_ok','call_ok_conversion') ";
			
			$req_total_sortant3  =  mysql_query($sql_total_sortant3);
			$rows_total_sortant3 =  mysql_num_rows($req_total_sortant3);
			$total_sortant_final = $rows_total_sortant1 + $rows_total_sortant2 + $rows_total_sortant3;
			
			$rows_total_ff_feedback 		+= $total_sortant_final;
			
			$sql_not_called  = "SELECT id
								FROM call_spool_vpc 
								WHERE call_result='not_called'
								AND assigned_operator='".$data_relance_comm->assigned_operator."'
								AND call_type IN ('2','6')
								";
			$req_not_called  =  mysql_query($sql_not_called);
			$rows_not_called =  mysql_num_rows($req_not_called);
			$rows_not_called_ff_feedback 		+= $rows_not_called;
			
			
			$sql_absent   = "SELECT DISTINCT(client_id) as client_id 
							 FROM call_spool_vpc 
							 WHERE call_result='absence'
							 AND assigned_operator='".$data_relance_comm->assigned_operator."'
							 AND call_type IN ('2','6')
							AND calls_count < 3
							GROUP BY client_id";
			$req_absent   =  mysql_query($sql_absent);
			$rows_absent  =  mysql_num_rows($req_absent);
			
			$rows_absent_ff_feedback 		+= $rows_absent;
			
			$sql_call_ok  =  "SELECT DISTINCT(client_id) as client_id 
								FROM call_spool_vpc 
								WHERE call_result IN('call_ok_conversion','call_ok')
								AND assigned_operator='".$data_relance_comm->assigned_operator."'
								AND call_type IN ('2','6')
								AND timestamp_first_call  between '$date_start' AND '$date_end'
								GROUP BY client_id ";
			$req_call_ok  =   mysql_query($sql_call_ok);
			$rows_call_ok =   mysql_num_rows($req_call_ok);
			$rows_call_ok_ff_feedback 		+= $rows_call_ok;
			
			
			
			$total_joindre  = $rows_not_called + $rows_absent;
			$total_joindre_ff_feedback 		+= $total_joindre;
			
			
			$sql_total_contacts  = "SELECT DISTINCT(client_id) as client_id 
									FROM call_spool_vpc
								    WHERE assigned_operator='".$data_relance_comm->assigned_operator."'
									AND call_type IN ('2','6')
									AND call_result NOT IN('not_called')
									AND timestamp_first_call  between '$date_start' AND '$date_end'
									GROUP BY client_id ";
			$req_total_contacts  =  mysql_query($sql_total_contacts);
			$rows_total_contacts =   mysql_num_rows($req_total_contacts);
			
			
			$calcul_taux = $rows_call_ok / $rows_total_contacts*100;
		 	$calcul_taux_ff 		+= $calcul_taux;
				
		?>
		  <tr >
			<td><?= $data_relance_comm->name ?></td>
			<td><?= $total_sortant_final ?></td>
			<td><?= $total_joindre ?></td>
			<td><?= $rows_not_called ?></td>
			<td><?= $rows_absent ?></td>
			
		  </tr>
		<?php 
		//Test if we have a record (make the addition)
		//Else add it to the array
		if(in_array($data_relance_comm->id, array_column($global_array_count, 0))){
		//if(array_search($data_relance_comm->id, array_column($global_array_count, 0), false)){
			
			//Inrement the old values with the new one's
			$array_index	= array_search($data_relance_comm->id, array_column($global_array_count, 0));
			$global_array_count[$array_index][2]	= $global_array_count[$array_index][2] + $total_sortant_final;
			$global_array_count[$array_index][3]	= $global_array_count[$array_index][3] + $total_joindre;
			$global_array_count[$array_index][4]	= $global_array_count[$array_index][4] + $rows_not_called;
			$global_array_count[$array_index][5]	= $global_array_count[$array_index][5] + $rows_absent;
			$global_array_count[$array_index][6]	= $global_array_count[$array_index][6] + $calcul_taux;
		}else{
			$array_temp	= array($data_relance_comm->id, $data_relance_comm->name, $total_sortant_final, $total_joindre, $rows_not_called, $rows_absent,$calcul_taux);
			array_push($global_array_count, $array_temp);
		}				
	}//End while	
	if($rows_all > 0){ 
	?>
	  <tr>
		<td><b>Total : </b> </td>
		<td><b><?= $rows_total_ff_feedback ?></b> </td>
		<td><b><?= $total_joindre_ff_feedback ?></b> </td>
		<td><b><?= $rows_not_called_ff_feedback ?></b> </td>
		<td><b><?= $rows_absent_ff_feedback ?></b> </td>
		
	  </tr>
	<?php }else{ ?>
		<tr>
		<td><b>Total : </b> </td>
		<td><b>0</b> </td>
		<td><b>0</b> </td>
		<td><b>0</b> </td>
		<td><b>0</b> </td>
		
	  </tr>
	<?php } ?>	
	</tbody>
</table>


</div>
<br /><br />


<div id="kpi_rdv1">


<div class="section">KPI RDV</div>
<table  class="item-list">
	<thead>
		<tr>
			<th width="16,66%"></th>
			<th width="16,66%">Appels sortants</th>
			<th width="16,66%">Nombre total RDV</th>
			<th width="16,66%">Total a joindre</th>
			<th width="16,66%">Reste à appeler</th>
			<th width="16,66%">A rappeler</th>
			
		</tr>
	</thead>
	<tbody>
	<?php

		$date_end_all = '2010-01-01 23:59:59';
		$sql_relance_comm  = "SELECT DISTINCT(csv.assigned_operator) ,UPPER(bu.name) AS name,bu.id
							  FROM call_spool_vpc csv,bo_users bu
							  WHERE csv.assigned_operator = bu.id
							  AND call_type='4' ";
		//AND timestamp_first_call  between '$date_start' AND '$date_end'
		$req_relance_comm  =  mysql_query($sql_relance_comm);
        $rows_all		   =  mysql_num_rows($req_relance_comm);			  
		while($data_relance_comm = mysql_fetch_object($req_relance_comm)){
			$sql_total_sortant1 = "SELECT DISTINCT(client_id) as client_id  
								  FROM call_spool_vpc 
								  WHERE assigned_operator='".$data_relance_comm->assigned_operator."'
								  AND call_type='4'
								  AND timestamp_first_call  between '$date_start' AND '$date_end' 
								  AND call_result IN ('absence','call_ok','call_ok_conversion') ";
			$req_total_sortant1  =  mysql_query($sql_total_sortant1);
			$rows_total_sortant1 =  mysql_num_rows($req_total_sortant1);
			
			$sql_total_rdv    = "SELECT DISTINCT(id_relation)
								 FROM   rdv
								 WHERE  operator = '".$data_relance_comm->assigned_operator."'
								 AND active ='1' 
								 GROUP BY id_relation";
								
			$req_total_rdv    =  mysql_query($sql_total_rdv);
			$rows_total_rdv   =  mysql_num_rows($req_total_rdv);
			$rows_total_rdv_ff_rdv +=   $rows_total_rdv;
			
			$sql_total_sortant2 = "SELECT DISTINCT(client_id) as client_id  
								  FROM call_spool_vpc 
								  WHERE assigned_operator='".$data_relance_comm->assigned_operator."'
								  AND call_type='4'
								  AND timestamp_second_call  between '$date_start' AND '$date_end' 
								  AND call_result IN ('absence','call_ok','call_ok_conversion') ";
			
			$req_total_sortant2  =  mysql_query($sql_total_sortant2);
			$rows_total_sortant2 =  mysql_num_rows($req_total_sortant2);
			
			
			$sql_total_sortant3 = "SELECT DISTINCT(client_id) as client_id  
								  FROM call_spool_vpc 
								  WHERE assigned_operator='".$data_relance_comm->assigned_operator."'
								  AND call_type='4'
								  AND timestamp_third_call  between '$date_start' AND '$date_end' 
								  AND call_result IN ('absence','call_ok','call_ok_conversion') ";
			
			$req_total_sortant3  =  mysql_query($sql_total_sortant3);
			$rows_total_sortant3 =  mysql_num_rows($req_total_sortant3);
			$total_sortant_final = $rows_total_sortant1 + $rows_total_sortant2 + $rows_total_sortant3;
			
			$rows_total_ff_rdv 		+= $total_sortant_final;
			
			//DISTINCT(client_id) as client_id
			$sql_not_called  = "SELECT  id
								FROM call_spool_vpc 
								WHERE call_result='not_called'
								AND assigned_operator='".$data_relance_comm->assigned_operator."'
								AND call_type='4'
								AND timestamp_rdv  between '$date_end_all' AND '$date_start' 
								";
								
			$req_not_called  =  mysql_query($sql_not_called);
			$rows_not_called =  mysql_num_rows($req_not_called);
			$rows_not_called_ff_rdv 		+= $rows_not_called;
			
			
			
			$sql_absent   = "SELECT DISTINCT(client_id) as client_id 
								FROM call_spool_vpc 
								WHERE call_result='absence'
								AND assigned_operator='".$data_relance_comm->assigned_operator."'
								AND call_type='4'
								AND calls_count < 3
								GROUP BY client_id";
			$req_absent   =  mysql_query($sql_absent);
			$rows_absent  =  mysql_num_rows($req_absent);
			$rows_absent_ff_rdv 		+= $rows_absent;
			
			$sql_call_ok  =  "SELECT DISTINCT(client_id) as client_id 
								FROM call_spool_vpc 
								WHERE call_result IN('call_ok_conversion','call_ok')
								AND assigned_operator='".$data_relance_comm->assigned_operator."'
								AND call_type='4'
								AND timestamp_first_call  between '$date_start' AND '$date_end'
								GROUP BY client_id ";
			
			$req_call_ok  =   mysql_query($sql_call_ok);
			$rows_call_ok =   mysql_num_rows($req_call_ok);
			$rows_call_ok_ff_rdv 		+= $rows_call_ok;
			
			$total_joindre  = $rows_not_called + $rows_absent;
			$total_joindre_ff_rdv 		+= $total_joindre;
			
			
			$sql_total_contacts  = "SELECT DISTINCT(client_id) as client_id 
									FROM call_spool_vpc
								    WHERE assigned_operator='".$data_relance_comm->assigned_operator."'
									AND call_type='4'
									AND call_result NOT IN('not_called')
									AND timestamp_first_call  between '$date_start' AND '$date_end'
									GROUP BY client_id ";
			$req_total_contacts  =  mysql_query($sql_total_contacts);
			$rows_total_contacts =   mysql_num_rows($req_total_contacts);
			
		
			$calcul_taux = $rows_call_ok / $rows_total_contacts*100;
		 	$calcul_taux_ff 		+= $calcul_taux;					
		?>
		  <tr >
			<td><?= $data_relance_comm->name ?></td>		
			<td><?= $total_sortant_final ?></td>		
			<td><?= $rows_total_rdv?></td>
			<td><?= $total_joindre ?></td>
			<td><?= $rows_not_called ?></td>
			<td><?= $rows_absent ?></td>
			
		  </tr>
		<?php 	
		//Test if we have a record (make the addition)
		//Else add it to the array
		if(in_array($data_relance_comm->id, array_column($global_array_count, 0))){
		//if(array_search($data_relance_comm->id, array_column($global_array_count, 0))){
			
			//Inrement the old values with the new one's
			$array_index	= array_search($data_relance_comm->id, array_column($global_array_count, 0));
			$global_array_count[$array_index][2]	+= $total_sortant_final;
			$global_array_count[$array_index][3]	+= $total_joindre;
			$global_array_count[$array_index][4]	+= $rows_not_called;
			$global_array_count[$array_index][5]	+= $rows_absent;
			$global_array_count[$array_index][6]	+= $calcul_taux;
		
		}else{
			$array_temp	= array($data_relance_comm->id, $data_relance_comm->name, $total_sortant_final, $total_joindre, $rows_not_called, $rows_absent,$calcul_taux);
			array_push($global_array_count, $array_temp);
		}
	}//End while	
	
	if($rows_all > 0){ 
	?>
	  <tr>
		<td><b>Total : </b> </td>	
		<td><b><?= $rows_total_ff_rdv ?></b> </td>
		<td><b><?= $rows_total_rdv_ff_rdv ?></b> </td>
		<td><b><?= $total_joindre_ff_rdv ?></b> </td>
		<td><b><?= $rows_not_called_ff_rdv ?></b> </td>
		<td><b><?= $rows_absent_ff_rdv ?></b> </td>
		
	  </tr>
	<?php }else{ ?>
		<tr>
		<td><b>Total : </b> </td>
		<td><b>0</b> </td>
		<td><b>0</b> </td>
		<td><b>0</b> </td>
		<td><b>0</b> </td>
		<td><b>0</b> </td>
		
	  </tr>
	<?php } ?>
	
	</tbody>
</table>


</div>
<br /><br />



<div id="kpi_campagne1">

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
			
		</tr>
	</thead>
	<tbody>
	<?php
		$sql_relance_comm  = "SELECT DISTINCT(csv.assigned_operator) ,UPPER(bu.name) AS name,timestamp_campaign,campaign_name,bu.id
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
			
			//$data_relance_comm->name = strtoupper($data_relance_comm->name);
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
		
	  </tr>
	<?php 
	
	//Test if we have a record (make the addition)
		//Else add it to the array
		if(in_array($data_relance_comm->id, array_column($global_array_count, 0))){
		//if(array_search($data_relance_comm->id, array_column($global_array_count, 0))){
			
			//Inrement the old values with the new one's
			$array_index	= array_search($data_relance_comm->id, array_column($global_array_count, 0));
			$global_array_count[$array_index][2]	+= $total_sortant_final;
			$global_array_count[$array_index][3]	+= $total_joindre;
			$global_array_count[$array_index][4]	+= $rows_not_called;
			$global_array_count[$array_index][5]	+= $rows_absent;
			$global_array_count[$array_index][6]	+= $calcul_taux;
		
		}else{
			$array_temp	= array($data_relance_comm->id, $data_relance_comm->name, $total_sortant_final, $total_joindre, $rows_not_called, $rows_absent,$calcul_taux);
			array_push($global_array_count, $array_temp);
		}
	}//End while
	
	
	
	
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
		
	  </tr>
	<?php } ?>
	
	</tbody>
</table>
</div>
<br /><br /> 



<div id="all_kpi1">

<div class="section">Kpi globaux </div>

<table  class="item-list">
	<thead>
		<tr>
			<th width="16,66%"></th>
			<th width="16,66%">Appels sortants</th>
			<th width="16,66%">Total a joindre</th>
			<th width="16,66%">Reste à appeler</th>
			<th width="16,66%">A rappeler</th>
			<!--<th width="16,66%">Taux joignabilité</th>-->
		</tr>
	</thead>
	<tbody>
	<?php
	
	//Rendre tout les noms miniscules
	/*$local_loop	= 0;
	while($global_array_count[$local_loop][1]){
		$global_array_count[$local_loop][1]	= strtolower($global_array_count[$local_loop][1]);
		
		$local_loop++;
	}*/
	
	function cmp($a, $b) {
		$sortby = 1;
		return strcmp($a[$sortby] , $b[$sortby]);
	}
	uasort($global_array_count, 'cmp');
	foreach($global_array_count as $global_array_one){
		
		
		$sql_total_contacts_taux  = "SELECT DISTINCT(client_id) as client_id 
									 FROM call_spool_vpc
								     WHERE assigned_operator='".$global_array_one[0]."'
									 AND call_result NOT IN('not_called')
									 AND timestamp_first_call  between '$date_start' AND '$date_end'
									 GROUP BY client_id ";
		$req_total_contacts_taux  =  mysql_query($sql_total_contacts_taux);
		$rows_total_contacts_taux =   mysql_num_rows($req_total_contacts_taux);
		
		
		$sql_transform_taux   = "SELECT DISTINCT(client_id) as client_id 
								 FROM call_spool_vpc 
								 WHERE call_result IN('call_ok_conversion','call_ok')
								 AND assigned_operator='".$global_array_one[0]."'
								 AND calls_count < 3
								 GROUP BY client_id";
		$req_transform_taux   =  mysql_query($sql_transform_taux);
		$rows_transform_taux  =  mysql_num_rows($req_transform_taux);
		
			
		$taux = ($rows_total_contacts_taux/$rows_transform_taux)*100;
		
		echo '<tr>';
			//echo '<td>'.ucfirst($global_array_one[1]).'</td>';
			echo '<td>'.$global_array_one[1].'</td>';			
			echo '<td>'.$global_array_one[2].'</td>';		
			echo '<td>'.$global_array_one[3].'</td>';		
			echo '<td>'.$global_array_one[4].'</td>';		
			echo '<td>'.$global_array_one[5].'</td>';		
			//echo '<td>'.number_format($taux, 2, ',', '') .'%</td>';
		echo '</tr>';
		
	}	
	?>
	<tr>
		<td><b>Total : </b> </td>	
		<td><b><?= array_sum(array_column($global_array_count, 2)); ?></b> </td>
		<td><b><?= array_sum(array_column($global_array_count, 3)); ?></b> </td>
		<td><b><?= array_sum(array_column($global_array_count, 4)); ?></b> </td>
		<td><b><?= array_sum(array_column($global_array_count, 5)); ?></b> </td>
		<!--<td><b>-</b> </td>-->
	</tr>	
		<?php
		/*}else {
			echo '<tr>
					<td>Total</td>		
					<td>0</td>
					<td>0</td>
					<td>0</td>
					<td>0</td>
					<td>-</td>
				  </tr>';
		}*/
	?>
	  
	 
	
	</tbody>
</table>

</div>




<div class="listing"></div>

</div>
  </div>
  
<script type="text/javascript">
  
	$( document ).ready(function() {
		if(document.getElementById('all_kpi1')){
			
			document.getElementById('row_total_array').innerHTML = document.getElementById('all_kpi1').innerHTML;
			document.getElementById('all_kpi1').innerHTML	= "";
			$("#row_total_array").show("fast");
		}
		
	});
  
<!--
var dateCur   = new Date();

    $('#MonthID option[value='+(dateCur.getMonth()+1)+']').attr('selected', 'selected');
    FillDayOptions('YearID', 'MonthID', 'DayID');
    $('#DayID option[value='+dateCur.getDate()+']').attr('selected', 'selected');
    
    //getInfos();

//-->
</script>
<script>
$(document).ready(function() {
	//send_repporting();
});
	
function send_repporting(){
	var check_interval  = $("#views_date_interval").val();
	if(check_interval == '0'){
		var YearID  		= $('#YearID').val();
		var MonthID 		= $('#MonthID').val();
		var DayID   		= $('#DayID').val();
		
		var date_start  	= YearID+'-'+MonthID+'-'+DayID;
		date_end = 	date_start;
	}else {
		var YearID  		= $('#YearSID').val();
		var MonthID 		= $('#MonthSID').val();
		var DayID   		= $('#DaySID').val();
		
		var YearEID  		= $('#YearEID').val();
		var MonthEID 		= $('#MonthEID').val();
		var DayEID   		= $('#DayEID').val();
		
		var date_start  	= YearID+'-'+MonthID+'-'+DayID;
		var date_end    	= YearEID+'-'+MonthEID+'-'+DayEID;
	}
	document.location.href="call_vpc.php?date_start="+date_start+"&date_end="+date_end;
	
	/*
		window.setTimeout(function(){
		$.ajax({
				url: 'Ajax_vpc/AJAX_table_relance_comm.php?date_start='+date_start+'&date_end='+date_end,
				type: 'GET',
				success:function(data){
					$('#relances_commerciales').html(data);
				}
		});
		}, 100);		
		
		window.setTimeout(function(){
		$.ajax({
				url: 'Ajax_vpc/AJAX_table_feedback_livraison.php?date_start='+date_start+'&date_end='+date_end,
				type: 'GET',
				success:function(data){
					$('#feedback_livraison').html(data);
				}
		});
		}, 100);
		
		window.setTimeout(function(){
		$.ajax({
				url: 'Ajax_vpc/AJAX_table_kpi_campagne.php?date_start='+date_start+'&date_end='+date_end,
				type: 'GET',
				success:function(data){
					$('#kpi_campagne').html(data);
				}
		});
		}, 100);
		
		window.setTimeout(function(){
		$.ajax({
				url: 'Ajax_vpc/AJAX_table_kpi_rdv.php?date_start='+date_start+'&date_end='+date_end,
				type: 'GET',
				success:function(data){
					$('#kpi_rdv').html(data);
				}
		});
		}, 100);
		
		window.setTimeout(function(){
		$.ajax({
				url: 'Ajax_vpc/AJAX_table_requalif_lead.php?date_start='+date_start+'&date_end='+date_end,
				type: 'GET',
				success:function(data){
					$('#kpi_requalif_lead').html(data);
				}
		});
		}, 100);	
		
		window.setTimeout(function(){
		$.ajax({
				url: 'Ajax_vpc/AJAX_table_all_kpi.php?date_start='+date_start+'&date_end='+date_end,
				type: 'GET',
				success:function(data){
					$('#all_kpi').html(data);
				}
		});
		}, 100);
	*/		
	}
</script>
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
<?php

require(ADMIN . 'tail.php');

?>
