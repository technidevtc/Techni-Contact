<?php
/*================================================================/

 Techni-Contact V4 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Outarocht Zakaria
 Date de création : 18/04/2016

 Mises à jour :

 Fichier : /secure/manager/reporting/rejected-leads.php
 Description : Tableau de reporting des leads rejetés

/=================================================================*/

if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

$title = 'Notation Opérateurs';
$navBar = $title;

require(ADMIN . 'head.php');
//if ((!$userPerms->has($fntByName["m-comm--sm-pile-appel-personaliser"], "re")) || (!$userPerms->has($fntByName["m-comm--sm-pile-appels-complete"], "re")) ) {

if ((!$userChildScript->get_permissions()->has("m-reporting--sm-sav","r")) && (!$userChildScript->get_permissions()->has("m-reporting--sm-sav","r")) ) {
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

function FillMonthOptions(yID, mID, dID)
{
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

<div class="titreStandard">Notation Opérateurs</div>
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
              <input type="hidden" id="views_date_not_in_visible" value="0"/>
              
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
                      <input type="button" value="OK"  onClick="change_date_type();send_repporting();" />
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


<div id="kpi_requalif_lead1">


<div id="notation_operateurs"></div>

<div class="listing"></div>

</div>
  </div>
  
<script type="text/javascript">

var dateCur   = new Date();

    $('#MonthID option[value='+(dateCur.getMonth()+1)+']').attr('selected', 'selected');
    FillDayOptions('YearID', 'MonthID', 'DayID');
    $('#DayID option[value='+dateCur.getDate()+']').attr('selected', 'selected');
    
    //getInfos();

//-->
</script>
<script>
$(document).ready(function() {
	send_repporting();
});

function change_date_type(){
		$("#views_date_not_in_visible").val('1');		
	}

function send_repporting(){
	var check_interval  = $("#views_date_interval").val();
	var views_date_not_in_visible  = $("#views_date_not_in_visible").val();
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
	

		$.ajax({
				url: 'Ajax_sav/statsGlobal.php?date_start='+date_start+'&date_end='+date_end+'&views_date_interval='+check_interval+'&views_date_not_in_visible='+views_date_not_in_visible,
				type: 'GET',
				success:function(data){
					
					$('#notation_operateurs').html(data);
				}
		});		
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
