<?php
/*================================================================/

 Techni-Contact V4 - MD2I SAS
 http://www.techni-contact.com

 Auteur : ODÂ pour Hook Network SARL - http://www.hook-network.com
 Date de cr&eacute;ation : 10/06/2011

 Mises Ã  jour :

 Fichier : /secure/manager/reporting/production.php
 Description : Tableau de reporting production

/=================================================================*/

if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

$title = 'Reporting Devis VPC';
$navBar = $title;

require(ADMIN."head.php");

if (!$userChildScript->get_permissions()->has("m-reporting--sm-supplier-leads","r")) {
?>
  <div class="bg">
    <div class="fatalerror">Vous n'avez pas les droits ad&eacute;quats pour r&eacute;aliser cette op&eacute;ration.</div>
  </div>
<?php
  require(ADMIN."tail.php");
  exit();
}

define("NB", 30);

	// time interval
	$dateFilterType = $_GET["dateFilterType"] != "interval" ? "simple" : "interval";

	$yearS 		= isset($_GET['yearS']) ? (int)trim($_GET['yearS']) : date("Y");
	$monthS		= isset($_GET['monthS']) ? (int)trim($_GET['monthS']) : date("m");
	$dayS 		= isset($_GET['dayS']) ? (int)trim($_GET['dayS']) : date("d");
	$yearS2 	= isset($_GET['yearS2']) ? (int)trim($_GET['yearS2']) : 0;
	$monthS2 	= isset($_GET['monthS2']) ? (int)trim($_GET['monthS2']) : 0;
	$dayS2 		= isset($_GET['dayS2']) ? (int)trim($_GET['dayS2']) : 0;
	$yearE 		= isset($_GET['yearE']) ? (int)trim($_GET['yearE']) : 0;
	$monthE 	= isset($_GET['monthE']) ? (int)trim($_GET['monthE']) : 0;
	$dayE 		= isset($_GET['dayE']) ? (int)trim($_GET['dayE']) : 0;

	$filter_date_start	= '';
	$filter_date_end	= '';

	$query_date_start	= '';
	$query_date_end		= '';

	if ($dateFilterType == "simple") {
		$filter_date_start	= $yearS.'-'.$monthS.'-'.$dayS.' 00:00:00';
		$filter_date_end	= $yearS.'-'.$monthS.'-'.$dayS.' 23:59:59';
	}
	elseif ($dateFilterType == "interval") {
		$filter_date_start	= $yearS2.'-'.$monthS2.'-'.$dayS2.' 00:00:00';
		$filter_date_end	= $yearE.'-'.$monthE.'-'.$dayE.' 23:59:59';	
	}

	//To convert we can use the same vars :)
	$query_date_start	=	strtotime($filter_date_start);
	$query_date_end		=	strtotime($filter_date_end);

	$array_commercials_estimate = array();
	$array_commercials_contacts = array();
	$array_commercials_global 	= array();
	
	//Array where we will show the in the page
	$array_global_show			= array();

	//Get the commercial from the Contacts
	$res_get_commercials_estimate = $db->query("SELECT
												e.id AS e_id,
												bo_u.id AS user_id,	bo_u.name AS user_name 
											FROM
												estimate e
													LEFT JOIN bo_users bo_u	ON e.created_user_id=bo_u.id
											WHERE
												e.created 
													BETWEEN ".$query_date_start." AND ".$query_date_end."
											GROUP BY bo_u.name
											ORDER BY bo_u.name ASC
													", __FILE__, __LINE__);
													
	//Get the commercial from the advertisers
	$res_get_commercials_contacts = $db->query("SELECT
												c.id AS c_id,
												bo_u.id AS user_id,	bo_u.name AS user_name 
											FROM
												contacts c
													LEFT JOIN advertisers a	ON c.id_user_commercial=a.idCommercial AND a.category =1
													LEFT JOIN bo_users bo_u	ON a.idCommercial=bo_u.id
											WHERE
												c.create_time
													BETWEEN ".$query_date_start." AND ".$query_date_end."
											AND
												bo_u.id IS NOT NULL
											GROUP BY bo_u.name
											ORDER BY bo_u.name ASC
													", __FILE__, __LINE__);
	
	//Fusion of the all commercial's
	
	$local_loop_estimate	= 0;
	while($content_get_commercials_estimate = $db->fetchAssoc($res_get_commercials_estimate)){
		$array_commercials_estimate[$local_loop_estimate] 			= array();
		$array_commercials_estimate[$local_loop_estimate][$content_get_commercials_estimate['user_id']] = $content_get_commercials_estimate['user_name'];
		//$array_commercials_estimate[$local_loop_estimate]['c_id'] 	= $content_get_commercials_estimate['user_id'];
		//$array_commercials_estimate[$local_loop_estimate]['c_name'] = $content_get_commercials_estimate['user_name'];
		$local_loop_estimate++;
	}
	
	$local_loop_contacts	= 0;
	while($content_get_commercials_contacts = $db->fetchAssoc($res_get_commercials_contacts)){
		$array_commercials_contacts[$local_loop_contacts] 			= array();
		$array_commercials_contacts[$local_loop_contacts][$content_get_commercials_contacts['user_id']] = $content_get_commercials_contacts['user_name'];
		//$array_commercials_contacts[$local_loop_contacts]['c_id'] 	= $content_get_commercials_contacts['user_id'];
		//$array_commercials_contacts[$local_loop_contacts]['c_name'] = $content_get_commercials_contacts['user_name'];
		$local_loop_contacts++;
	}
	
	
	/*echo('<br >**** Estimate Array<br />');
	print_r($array_commercials_estimate);

	echo('<br >**** Contacts Array<br />');
	print_r($array_commercials_contacts);
	
	echo('<br ><br >**** Global Array<br />');*/
	
	//Fusionner les deux tableaux (This function make only the addition in case that we have a multidimentional Array)
	$array_commercials_global 	= array_merge($array_commercials_estimate,$array_commercials_contacts);
	
	//Delete the duplicated data
	$array_commercials_global	= array_map("unserialize", array_unique(array_map("serialize", $array_commercials_global)));
	
	//To delete the empty rows
	$array_commercials_temp		= Array();
	$local_loop_key				= 0;
	foreach($array_commercials_global AS $key => $value){
		if(!empty($key) || $key==0){
			$array_commercials_temp[$local_loop_key] = $value;
			$local_loop_key++;
		}
	}
	$array_commercials_global	= $array_commercials_temp;
	
	//print_r($array_commercials_global);
	
	//Building the Array to show in the page !
	//For every commercial we will look for hi's stats
	foreach($array_commercials_global AS $ag_key => &$ag_value){
		//We will use the same Key because we know that is clean (0, 1, 2..)
		//Reset Function to get The Key
		//Key function to get the Value
		
		//Name
		$array_global_show[$ag_key]['name']	= reset($ag_value);

		//Start the Query's for each Commercial	 key($ag_value)
		
		//Leads re&ccedil;us total


		$res_c_received_leads_total = $db->query("SELECT 
								COUNT(c.id) AS c 
							FROM 
								contacts c INNER JOIN advertisers AS a	ON a.id=c.idAdvertiser	AND a.category=1 
								INNER JOIN bo_users bo_u on bo_u.id=a.idCommercial

								WHERE 
									c.id_user_commercial=".key($ag_value)."

								AND (c.create_time BETWEEN ".$query_date_start." AND ".$query_date_end.")", __FILE__, __LINE__);

											
		$content_c_received_leads_total = $db->fetchAssoc($res_c_received_leads_total);
		if(isset($content_c_received_leads_total['c'])){
			$array_global_show[$ag_key]['received_leads_total']	= $content_c_received_leads_total['c'];
		}else{
			$array_global_show[$ag_key]['received_leads_total']	= 0;
		}
		
		
		//Leads uniques
		$res_c_received_unique_leads = $db->query("SELECT 
								COUNT(DISTINCT(c.email)) AS c 
							FROM 
								contacts c INNER JOIN advertisers AS a	ON a.id=c.idAdvertiser	AND a.category=1 
								INNER JOIN bo_users bo_u on bo_u.id=a.idCommercial

								WHERE 
									c.id_user_commercial=".key($ag_value)."

								AND (c.create_time BETWEEN ".$query_date_start." AND ".$query_date_end.")", __FILE__, __LINE__);

		$content_c_received_unique_leads = $db->fetchAssoc($res_c_received_unique_leads);
		if(isset($content_c_received_unique_leads['c'])){
			$array_global_show[$ag_key]['unique_leads']	= $content_c_received_unique_leads['c'];
		}else{
			$array_global_show[$ag_key]['unique_leads']	= 0;
		}										

		
		//Devis envoy&eacute;s
		$res_c_sent_estimate = $db->query("SELECT
												COUNT(e.id) AS c
											FROM
												estimate e
											WHERE
												e.created 
													BETWEEN ".$query_date_start." AND ".$query_date_end."
											AND
												e.created_user_id=".key($ag_value)."
											AND
												e.status>=2
													", __FILE__, __LINE__);
		$content_c_sent_estimate = $db->fetchAssoc($res_c_sent_estimate);
		if(isset($content_c_sent_estimate['c'])){
			$array_global_show[$ag_key]['sent_estimate']	= $content_c_sent_estimate['c'];
		}else{
			$array_global_show[$ag_key]['sent_estimate']	= 0;
		}

		
		//Devis uniques envoy&eacute;s
		$res_c_unique_sent_estimate = $db->query("SELECT
												COUNT(DISTINCT(e.email)) AS c
											FROM
												estimate e
											WHERE
												e.created 
													BETWEEN ".$query_date_start." AND ".$query_date_end."
											AND
												e.created_user_id=".key($ag_value)."
											AND
												e.status>=2
													", __FILE__, __LINE__);
		$content_c_unique_sent_estimate = $db->fetchAssoc($res_c_unique_sent_estimate);
		if(isset($content_c_unique_sent_estimate['c'])){
			$array_global_show[$ag_key]['unique_sent_estimate']	= $content_c_unique_sent_estimate['c'];
		}else{
			$array_global_show[$ag_key]['unique_sent_estimate']	= 0;
		}
		
		//Montant total
		$res_c_total_ht = $db->query("SELECT
										ROUND(SUM(e.total_ht),2) AS s_total
									FROM
										estimate e
									WHERE
										e.created 
											BETWEEN ".$query_date_start." AND ".$query_date_end."
									AND
										e.created_user_id=".key($ag_value)."
									AND
										e.status>=2
											", __FILE__, __LINE__);
		$content_c_total_ht = $db->fetchAssoc($res_c_total_ht);
		if(isset($content_c_total_ht['s_total'])){
			$array_global_show[$ag_key]['total_ht']	= $content_c_total_ht['s_total'];
		}else{
			$array_global_show[$ag_key]['total_ht']	= 0;
		}
		
		//Devis Transform&eacute;s
		$res_c_transformed_estimate = $db->query("SELECT
										COUNT(e.id) AS c
									FROM
										estimate e
									WHERE
										e.created 
											BETWEEN ".$query_date_start." AND ".$query_date_end."
									AND
										e.created_user_id=".key($ag_value)."
									AND
										e.status=4
											", __FILE__, __LINE__);
		$content_c_transformed_estimate = $db->fetchAssoc($res_c_transformed_estimate);
		if(isset($content_c_transformed_estimate['c'])){
			$array_global_show[$ag_key]['transformed_estimate']	= $content_c_transformed_estimate['c'];
		}else{
			$array_global_show[$ag_key]['transformed_estimate']	= 0;
		}


		//Taux de transfo
		if(!empty($array_global_show[$ag_key]['transformed_estimate']) && !empty($array_global_show[$ag_key]['unique_sent_estimate'])){
			$array_global_show[$ag_key]['rate_transformation']	= $array_global_show[$ag_key]['transformed_estimate']/$array_global_show[$ag_key]['unique_sent_estimate'];
			
			$array_global_show[$ag_key]['rate_transformation']	= round($array_global_show[$ag_key]['rate_transformation'], 2) * 100;
		}else{
			$array_global_show[$ag_key]['rate_transformation']	= 0;
		}
		
		
		//Leads intraitables
		$res_c_unprocessable_leads = $db->query("SELECT
										COUNT(DISTINCT(c.email)) AS c
									FROM
										contacts c
									WHERE
										c.create_time 
											BETWEEN ".$query_date_start." AND ".$query_date_end."
									AND
										c.id_user_commercial=".key($ag_value)."
									AND
										c.processing_status=3
											", __FILE__, __LINE__);
		$content_c_unprocessable_leads = $db->fetchAssoc($res_c_unprocessable_leads);
		if(isset($content_c_unprocessable_leads['c'])){
			$array_global_show[$ag_key]['unprocessable_leads']	= $content_c_unprocessable_leads['c'];
		}else{
			$array_global_show[$ag_key]['unprocessable_leads']	= 0;
		}
		
		//Taux intraitable
		if(!empty($array_global_show[$ag_key]['unprocessable_leads']) && !empty($array_global_show[$ag_key]['unique_leads'])){
			$array_global_show[$ag_key]['unprocessable_rate']	= ROUND(($array_global_show[$ag_key]['unprocessable_leads']/$array_global_show[$ag_key]['unique_leads']),2)*100;
		}else{
			$array_global_show[$ag_key]['unprocessable_rate']	= 0;
		}
		
		//Reste Ã  traiter
		$res_c_not_processed_leads = $db->query("SELECT
										COUNT(DISTINCT(c.id)) AS c
									FROM 
										`contacts` AS c
										INNER JOIN advertisers a ON c.idAdvertiser=a.id AND a.category=1 AND a.actif =1
										INNER JOIN products_fr	prod_fr ON c.idProduct=prod_fr.id AND prod_fr.active =1
									WHERE 
										`id_user_commercial` = ".key($ag_value)."
									AND
										a.idCommercial = ".key($ag_value)."
									AND
										processing_status=1
									AND	
										c.timestamp >1392303818
											", __FILE__, __LINE__);
		$content_c_not_processed_leads = $db->fetchAssoc($res_c_not_processed_leads);
		$array_global_show[$ag_key]['not_processed']	= $content_c_not_processed_leads['c'];
		
		/*$array_global_show[$ag_key]['not_processed']	= $array_global_show[$ag_key]['unique_leads']-$array_global_show[$ag_key]['unprocessable_leads'];
		if($array_global_show[$ag_key]['not_processed']<0){
			$array_global_show[$ag_key]['not_processed'] = 0;
		}*/		
			
	}//end foreach


	$dateOrigin_js = mktime(0,0,0,9,16,2011);
?>

<script type="text/javascript">
// date form
var COMMON_ALL_M = "Tous";
var COMMON_ALL_F = "Toutes";
var COMMON_ALL_CHOICE = "COMMON_ALL_CHOICE";

var MonthLabels = new Array('janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre');
var DayLabes = new Array('Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi');

var dateBegin = new Date(); dateBegin.setTime(<?php echo $dateOrigin_js; //echo __BEGIN_TIME__ ?>*1000);
var dateCur   = new Date();

function FillYearOptions(yID, mID, dID){
	var y = $('#'+yID)[0];
	var yb = parseInt(dateBegin.getFullYear());
	var yc = parseInt(dateCur.getFullYear());
	y.options.length = (yc-yb) + 2;

	y.options[0].value = 0;
	y.options[0].text  = COMMON_ALL_F;
	for (var i = 1; i < y.options.length; i++){
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
	for (var i = 1; i < m.options.length; i++){
		m.options[i].value = i;
		m.options[i].text  = MonthLabels[i-1];
	}
        if(year == dateBegin.getFullYear())
          for (var j = 1; j <= dateBegin.getMonth(); j++){
            $('option[value='+j+']').remove();
          }
        
	FillDayOptions(yID, mID, dID);
}

function FillDayOptions(yID, mID, dID){
	var y = $('#'+yID)[0];
	var m = $('#'+mID)[0];
	var d = $('#'+dID)[0];

	var year  = parseInt(y.value);
    var month = parseInt(m.value);

	if(year == parseInt(dateCur.getFullYear()) && month == parseInt(dateCur.getMonth()+1)){
		var date = new Date(dateCur);
		d.options.length = date.getDate() + 1;
	}else{
		var date = new Date(year, month, 0);
		if (month == 0) d.options.length = 1;
		else d.options.length = date.getDate() + 1;
	}

	d.options.value = 0;
	d.options.text  = COMMON_ALL_M;
	for(var i = 1; i < d.options.length; i++){
		date.setDate(i);
		d.options[i].value = i;
		d.options[i].text  = DayLabes[date.getDay()] + " " + i;
	}
}

function FillYearOptions2(yID, mID, dID){
	var y = $('#'+yID)[0];
	var yb = parseInt(dateBegin.getFullYear());
	var yc = parseInt(dateCur.getFullYear());
	y.options.length = (yc-yb) + 2;

	y.options[0].value = 0;
	y.options[0].text  = " - ";
	for (var i = 1; i < y.options.length; i++){
		y.options[y.options.length-i].value = yb + i - 1;
		y.options[y.options.length-i].text  = yb + i - 1;
	}
	FillMonthOptions2(yID, mID, dID);
}

function FillMonthOptions2(yID, mID, dID){
	var y = $('#'+yID)[0];
	var m = $('#'+mID)[0];
	var year = parseInt(y.options[y.options.selectedIndex].value);

	if (year == 0) m.options.length = 1;
	else if (year < dateCur.getFullYear()) m.options.length = 13;
	else m.options.length = dateCur.getMonth() + 2;

	m.options[0].value = 0;
	m.options[0].text  = " - ";
	for (var i = 1; i < m.options.length; i++){
		m.options[i].value = i;
		m.options[i].text  = MonthLabels[i-1];
	}
	FillDayOptions2(yID, mID, dID);
}

function FillDayOptions2(yID, mID, dID){
	var y = $('#'+yID)[0];
	var m = $('#'+mID)[0];
	var d = $('#'+dID)[0];
	var year  = parseInt(y.value);
	var month = parseInt(m.value);

	if (year == parseInt(dateCur.getFullYear()) && month == parseInt(dateCur.getMonth()+1)){
		var date = new Date(dateCur);
		d.options.length = date.getDate() + 1;
	}else{
		var date = new Date(year, month, 0);
		if (month == 0) d.options.length = 1;
		else d.options.length = date.getDate() + 1;
	}

	d.options.value = 0;
	d.options.text  = " - ";
	for (var i = 1; i < d.options.length; i++){
		date.setDate(i);
		d.options[i].value = i;
		d.options[i].text  = DayLabes[date.getDay()] + " " + i;
	}
}

function SetDateOptions(yid, mid, did, year, month, day){
  $("#"+yid).val(parseInt(year,10)).change();
  $("#"+mid).val(parseInt(month,10)).change();
  $("#"+did).val(parseInt(day,10));
}

function ShowDateSection(){
	document.getElementById('DateFilter').style.display = "block";
	document.getElementById('DateIntervalFilter').style.display = "none";
    $('input[name=dateFilterType]')[0].value = "simple";
}

function ShowDateIntervalSection(){
	document.getElementById('DateFilter').style.display = "none";
	document.getElementById('DateIntervalFilter').style.display = "block";
    $('input[name=dateFilterType]')[0].value = "interval";
}

function getInfos(){
  $("#commandesList").submit();
}

</script>
<link type="text/css" rel="stylesheet" href="<?php echo ADMIN_URL ?>ressources/css/command.css">
<link rel="stylesheet" type="text/css" href="reporting.css" />

<div class="titreStandard">Reporting production</div>
<br />
<div class="bg" style="min-width: 980px">
  <style type="text/css">
  #DateFilter { display: none; float: left; height: 80px; width: 780px;}
  #DateIntervalFilter { display: none;  float: left;height: 140px; width: 780px;}
  #unregisteredCampaigns{display: block;float: right;height: 80px; width: 43%;}
  #filtre_status{margin-top: 60px; width: 510px;}
  .commandesForm { font-family: Arial,Helvetica,sans-serif;font-size: 12px;}
  fieldset { margin: 0 0 5px; padding: 4px 8px 8px; border: 2px groove threedface ; width: 400px; }
  legend { margin: 0 2px; font-weight: bold; font-size: 15px; }
  </style>

  <div class="centre">

    <form id="commandesList" class="commandesForm" action="" method="get">
      <input type="hidden" name="dateFilterType" value="<?php echo $dateFilterType ?>"/>
      <br/>
      <div id="DateFilter">
        <div style="float:left">
          <fieldset class="date-picker">
            <legend>Interval pr&eacute;d&eacute;fini :</legend>
            Ann&eacute;e : <select name="yearS" id="YearID" onchange="FillMonthOptions(this.id, 'MonthID', 'DayID');"></select>
            Mois : <select name="monthS" id="MonthID" onchange="FillDayOptions('YearID', this.id, 'DayID');"></select>
            Jour : <select name="dayS" id="DayID"></select>
            <br/>
          </fieldset>
          <input type="button" value="Choisir un interval de temps" onclick="ShowDateIntervalSection()">
          <input type="button" value="OK"  onClick="getInfos();return false;" />
          </div>
        <div class="zero"></div>
      </div>
      <div id="DateIntervalFilter">
        <div style="float:left">
          <fieldset>
            <legend>Date de d&eacute;but :</legend>
            Ann&eacute;e :
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
          Ann&eacute;e :
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
			SetDateOptions('YearSID', 'MonthSID', 'DaySID', '<?php echo $yearS2 ?>','<?php echo $monthS2 ?>','<?php echo $dayS2 ?>');
          SetDateOptions('YearEID', 'MonthEID', 'DayEID', '<?php echo $yearE ?>','<?php echo $monthE ?>','<?php echo $dayE ?>');
          /* ]]>*/ </script>
          <input type="button" value="Choisir une date simple" onclick="ShowDateSection()">
          <input type="button" value="OK"  onClick="getInfos();return false;" />
        </div>
        <div class="zero"></div>
      </div>
      <div class="zero"></div>
    </form>
    <script type="text/javascript">/* <![CDATA[ */<?php  echo ($dateFilterType == "interval") ? "ShowDateIntervalSection();" : "ShowDateSection();" ?>/* ]]> */</script>

    <br/>
	<?php
		/*echo('Condition Start: '.$filter_date_start.' => '.$query_date_start);
		echo('<br />');
		echo('Condition End: '.$filter_date_end.' => '.$query_date_end);
		echo('<br />');*/
	?>
    <table  id="production_list" class="item-list">
      <thead>
        <tr>
        <th width="150"></th>
        <th style="min-width:90px;">Leads re&ccedil;us total</th>
        <th style="min-width:90px;">Leads uniques</th>
        <th style="min-width:90px;">Devis envoy&eacute;s</th>
		<th style="min-width:90px;">Devis uniques envoy&eacute;s</th>
        <th style="min-width:90px;">Montant total</th>
		<th style="min-width:90px;">Devis Transform&eacute;s</th>
		<th style="min-width:90px;">Taux de transfo</th>
        <th style="min-width:90px;">Leads intraitables</th>
        <th style="min-width:90px;">Taux intraitable</th>
        <th style="min-width:90px;">Reste &agrave; traiter</th>
        </tr>
      </thead>
      <tbody id="production_list_body">
       <?php 
			$total_received_leads			= 0;
			$total_unique_leads				= 0;
			$total_sent_estimate			= 0;
			$total_unique_sent_estimate		= 0;
			$total_total_ht					= 0;
			$total_transformed_estimate		= 0;
			//$total_rate_transformation		= 0;
			$total_unprocessable_leads		= 0;
			//$total_unprocessable_rate		= 0;
			$total_not_processed			= 0;
			
			foreach($array_global_show as $ags){ ?>
				<tr>
					<td>
						<?php
							echo $ags["name"]; 
						?>
					</td>
					<td>
						<?php 
							$total_received_leads += (int)$ags["received_leads_total"];
							echo $ags["received_leads_total"];
						?>
					</td>
					<td>
						<?php 
							$total_unique_leads += $ags["unique_leads"];
							echo $ags["unique_leads"];
						?>
					</td>
					<td>
						<?php 
							$total_sent_estimate += $ags["sent_estimate"];
							echo $ags["sent_estimate"];						
						?>
					</td>
					<td>
						<?php 
							$total_unique_sent_estimate	+= $ags["unique_sent_estimate"];
							echo $ags["unique_sent_estimate"];
						?>
					</td>
					<td>
						<?php 
							$total_total_ht	+= $ags["total_ht"];
							echo $ags["total_ht"];
						
						?>
					</td>
					<td>
						<?php 
							$total_transformed_estimate += $ags["transformed_estimate"];
							echo $ags["transformed_estimate"];
						?>
					</td>
					<td>
						<?php
							echo $ags["rate_transformation"].' %';
						?>
					</td>
					<td>
						<?php
							$total_unprocessable_leads += $ags["unprocessable_leads"];
							echo $ags["unprocessable_leads"];
						?>
					</td>
					<td>
						<?php 
							//$total_unprocessable_rate += $ags["unprocessable_rate"];
							echo $ags["unprocessable_rate"].' %';
						?>
					</td>
					<td>
						<?php 
							$total_not_processed += $ags["not_processed"];
							echo $ags["not_processed"];
						?>	
					</td>
				</tr>
       <?php } ?>
        <tr style="background:#f4f4f4">
          <td>Totaux</td>
          <td><?php echo $total_received_leads; ?></td>
          <td><?php echo $total_unique_leads;  ?></td>
          <td><?php echo $total_sent_estimate;  ?></td>
          <td><?php echo $total_unique_sent_estimate;  ?></td>
          <td><?php echo $total_total_ht;  ?></td>
          <td><?php echo $total_transformed_estimate;  ?></td>
		  <td>
			<?php 
				if($total_unique_sent_estimate>0){
					echo (ROUND($total_transformed_estimate/$total_unique_sent_estimate,2)*100);
				}else{
					echo('0');
				}			
			?> %</td>
		  <td><?php echo $total_unprocessable_leads;  ?></td>
		  <td>
			<?php 
				if($total_unique_leads>0){
					echo (ROUND($total_unprocessable_leads/$total_unique_leads,2)*100);
				}else{
					echo('0');
				}
			?> %</td>
		  <td><?php echo $total_not_processed;  ?></td>
        </tr>
      </tbody>
    </table>

    <div class="listing"></div>

  </div>
</div>

<?php require(ADMIN."tail.php") ?>