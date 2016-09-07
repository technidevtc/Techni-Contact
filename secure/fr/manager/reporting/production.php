<?php
/*================================================================/

 Techni-Contact V4 - MD2I SAS
 http://www.techni-contact.com

 Auteur : OD pour Hook Network SARL - http://www.hook-network.com
 Date de création : 10/06/2011

 Mises à jour :

 Fichier : /secure/manager/reporting/production.php
 Description : Tableau de reporting production

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$title = 'Reporting prodution';
$navBar = $title;

require(ADMIN . 'head.php');

if (!$userChildScript->get_permissions()->has("m-reporting--sm-production","r")) {
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
        if(year == dateBegin.getFullYear())
          for (var j = 1; j <= dateBegin.getMonth(); j++){
            $('option[value='+j+']').remove();
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

function ShowDateSection()
{
	document.getElementById('DateFilter').style.display = "block";
	document.getElementById('DateIntervalFilter').style.display = "none";
        $('input[name=dateFilterType]')[0].value = "simple";
}

function ShowDateIntervalSection()
{
	document.getElementById('DateFilter').style.display = "none";
	document.getElementById('DateIntervalFilter').style.display = "block";
        $('input[name=dateFilterType]')[0].value = "interval";
}

var AJAXHandle = {
	type : "GET",
	url: "AJAX_production.php",
	dataType: "json",
	error: function (XMLHttpRequest, textStatus, errorThrown) {
                var tbody = $("#production_list_body");
			tbody.empty();
                        tbody.append( '<tr class="tr-new"><td class="date" colspan="8"> '+textStatus+'</td></tr>');
	},
	success: function (data, textStatus) {
            var production_list = $("#production_list_body");
			production_list.empty();

                        if(data.error){
                          production_list.append( '<tr class="tr-new"><td class="date" colspan="9" style="color : red"> '+data.error+'</td></tr>');
                        }
                        else if(data.reponses == 'vide'){
                          production_list.append( '<tr class="tr-new"><td class="date" colspan="9"> Aucune entrée n\'a été retrouvé sur cette période. </td></tr>');
                        }else{

                          $.each(data.reponses, function(index){
                            // tr type
                            var tr = '';
                            tr = '<tr class="tr-normal" onmouseover="this.className=\'tr-hover\'" onmouseout="this.className=\'tr-normal\'">';

                            var dateFilterType = $('input[name=dateFilterType]').val();
                            var yearS = $('#YearID').val();
                            var monthS = $('#MonthID').val();
                            var dayS = $('#DayID').val();
                            var yearS2 = $('#YearSID').val();
                            var monthS2 = $('#MonthSID').val();
                            var dayS2 = $('#DaySID').val();
                            var yearE = $('#YearEID').val();
                            var monthE = $('#MonthEID').val();
                            var dayE = $('#DayEID').val();
                            var href = "detail-production.php?dateFilterType="+dateFilterType+"&yearS="+yearS+"&monthS="+monthS+"&dayS="+dayS
                            +"&yearS2="+yearS2+"&monthS2="+monthS2+"&dayS2="+dayS2+"&yearE="+yearE+"&monthE="+monthE+"&dayE="+dayE+'&userId='+index;

                            
                            var advertiser_creation = href+"&prod="+this.advertiser_creation.join('|');
                            var advertiser_modification = href+"&prod="+this.advertiser_modification.join('|');
                            var advertiser_suppression = href+"&entries="+this.advertiser_suppression.join('|');
                            var supplier_creation = href+"&prod="+this.supplier_creation.join('|');
                            var supplier_modification = href+"&prod="+this.supplier_modification.join('|');
                            var supplier_suppression = href+"&entries="+this.supplier_suppression.join('|');

                            var creations = this.advertiser_creation.concat(this.supplier_creation);
                            var modification = this.advertiser_modification.concat(this.supplier_modification);
                            var suppression = this.advertiser_suppression.concat(this.supplier_suppression);
                            var total = creations.concat(modification);
                            var lnk_total = href+"&prod="+total.join('|')+'&entries='+suppression.join('|');

                            var nbr_advertiser_creation = this.advertiser_creation.length ? this.advertiser_creation.length : 0;
                            var nbr_advertiser_modification = this.advertiser_modification.length ? this.advertiser_modification.length : 0;
                            var nbr_advertiser_suppression = this.advertiser_suppression.length ? this.advertiser_suppression.length : 0;
                            var nbr_supplier_creation = this.supplier_creation.length ? this.supplier_creation.length : 0;
                            var nbr_supplier_modification = this.supplier_modification.length ? this.supplier_modification.length : 0;
                            var nbr_supplier_suppression = this.supplier_suppression.length ? this.supplier_suppression.length : 0;
                            var nbr_total = nbr_advertiser_creation+nbr_advertiser_modification+nbr_advertiser_suppression+nbr_supplier_creation+nbr_supplier_modification+nbr_supplier_suppression;

                            var lnk_advertiser_creation = nbr_advertiser_creation != 0 ? '<a href="'+advertiser_creation+'&type=creation-annonceur" target="_blank">'+nbr_advertiser_creation+' '+'</a>' : 0;
                            var lnk_advertiser_modification = nbr_advertiser_modification != 0 ? '<a href="'+advertiser_modification+'&type=modification-annonceur" target="_blank">'+nbr_advertiser_modification+' '+'</a>' : 0;
                            var lnk_advertiser_suppression = nbr_advertiser_suppression != 0 ? '<a href="'+advertiser_suppression+'&type=suppression-annonceur" target="_blank">'+nbr_advertiser_suppression+'</a>' : 0;
                            var lnk_supplier_creation = nbr_supplier_creation != 0 ? '<a href="'+supplier_creation+'&type=creation-fournisseur" target="_blank">'+nbr_supplier_creation+' '+'</a>' : 0;
                            var lnk_supplier_modification = nbr_supplier_modification != 0 ? '<a href="'+supplier_modification+'&type=modification-fournisseur" target="_blank">'+nbr_supplier_modification+'</a>' : 0;
                            var lnk_supplier_suppression = nbr_supplier_suppression != 0 ? '<a href="'+supplier_suppression+'&type=suppression-fournisseur" target="_blank">'+nbr_supplier_suppression+'</a>' : 0;
                            var lnk_total = nbr_total != 0 ? '<a href="'+lnk_total+'&type=total" target="_blank">'+nbr_total+'</a>' : 0;

                            production_list.append(
                              tr +
                               "	<td class=\"date\">"+this.userName+"</td>" +
                              "   <td class=\"date\">"+lnk_advertiser_creation+"</td>" +
                              "   <td class=\"date\">"+lnk_advertiser_modification+"</td>" +
                              "   <td class=\"date\">"+lnk_advertiser_suppression+"</td>" +
                              "   <td class=\"date\">"+lnk_supplier_creation+"</td>" +
                              "   <td class=\"date\">"+lnk_supplier_modification+"</td>" +
                              "   <td class=\"date\">"+lnk_supplier_suppression+"</td>" +
                              "   <td class=\"date\">"+lnk_total+"</td>" +
                               "</tr>");
                          });



                        }

	}
  };

  function getInfos(){

   var production_list = $("#production_listt_body");
      production_list.empty();
      production_list.append( '<tr class="tr-new"><td class="date" colspan="7"><img src="../../ressources/images/lightbox-ico-loading.gif" alt="chargement..."></td></tr>');

    var dateFilterType = $('input[name=dateFilterType]').val();
    var yearS = $('#YearID').val();
    var monthS = $('#MonthID').val();
    var dayS = $('#DayID').val();
    var yearS2 = $('#YearSID').val();
    var monthS2 = $('#MonthSID').val();
    var dayS2 = $('#DaySID').val();
    var yearE = $('#YearEID').val();
    var monthE = $('#MonthEID').val();
    var dayE = $('#DayEID').val();

    AJAXHandle.data = "dateFilterType="+dateFilterType+"&yearS="+yearS+"&monthS="+monthS+"&dayS="+dayS
      +"&yearS2="+yearS2+"&monthS2="+monthS2+"&dayS2="+dayS2+"&yearE="+yearE+"&monthE="+monthE+"&dayE="+dayE;
    $.ajax(AJAXHandle);

    return false;
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
                              <legend>Interval prédéfini :</legend>
                              Année : <select name="yearS" id="YearID" onchange="FillMonthOptions(this.id, 'MonthID', 'DayID');"></select>
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

        <table  id="production_list" class="item-list">
                <thead>
                        <tr>
                                <th width="150"></th>
                                <th colspan="3">Annonceurs</th>
                                <th colspan="3">Fournisseurs</th>
                                <th width="150"></th>
                        </tr>
                        <tr>
                                <th width="150"></th>
                                <th style="min-width:90px;">Création</th>
                                <th style="min-width:90px;">Modification</th>
                                <th style="min-width:90px;">Suppression</th>
                                <th style="min-width:90px;">Création</th>
                                <th style="min-width:90px;">Modification</th>
                                <th style="min-width:90px;">Suppression</th>
                                <th width="150">Total</th>
                        </tr>
                </thead>
                <tbody id="production_list_body">
                  <tr class="tr-new"><td class="date" colspan="8"><img src="../../ressources/images/lightbox-ico-loading.gif" alt="chargement..."></td></tr>
                </tbody>
        </table>


<script type="text/javascript">
<!--

    var dateCur   = new Date();

    $('#MonthID option[value='+(dateCur.getMonth()+1)+']').attr('selected', 'selected');
    FillDayOptions('YearID', 'MonthID', 'DayID');
    $('#DayID option[value='+dateCur.getDate()+']').attr('selected', 'selected');
    getInfos();

//-->
</script>

				<div class="listing"></div>

</div>
  </div>
<?php

require(ADMIN . 'tail.php');

?>
