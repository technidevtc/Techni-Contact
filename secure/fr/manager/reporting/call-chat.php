<?php
/*================================================================/

 Techni-Contact V4 - MD2I SAS
 http://www.techni-contact.com

 Auteur : OD pour Hook Network SARL - http://www.hook-network.com
 Date de création : 30/06/2011

 Mises à jour :

 Fichier : /secure/manager/reporting/call-chat.php
 Description : Tableau de reporting des leads passés après contact client

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$title = 'Reporting call/chat';
$navBar = $title;

require(ADMIN . 'head.php');

if (!$userChildScript->get_permissions()->has("m-reporting--sm-call-chat","r")) {
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
	url: "AJAX_call-chat.php",
	dataType: "json",
	error: function (XMLHttpRequest, textStatus, errorThrown) {
                var tbody = $("#campaign-list");
			tbody.empty();
                        tbody.append( '<tr class="tr-new"><td class="date" colspan="4"> '+textStatus+'</td></tr>');
	},
	success: function (data, textStatus) {
            var callchat_list = $("#callchat_list_body");
                        callchat_list.empty();

                        if(data.error){
                          callchat_list.append( '<tr class="tr-new"><td class="date" colspan="4" style="color : red"> '+data.error+'</td></tr>');
                        }
                        else if(data.reponses == 'vide'){
                          callchat_list.append( '<tr class="tr-new"><td class="date" colspan="4"> Aucune information n\'a été retrouvé sur cette période. </td></tr>');
                        }else{

                          var nb_cols = $('th[class^=col_]').length;
                          var total_general_nb_lead = 0;
                          var total_general_nb_lead_rejected = 0;
                          var total_denominateurTxRejet = 0;

                          $.each(data.reponses, function(index){
                            var html = '';
                            // tr type
                            var tr = '';
                            tr = '<tr class="tr-normal" onmouseover="this.className=\'tr-hover\'" onmouseout="this.className=\'tr-normal\'">';

                            html = tr+"	<td class=\"date\">"+this.name+"</td>";

                            var cells = new Array();
                            var leads_total_P = 0;
                            var ca_leads_total = 0;

                            $.each(this, function(index2){
                              if((index2 != 'name') && (index2 != 'nb_leads_total') && (index2 != 'nb_leads_rejected') && (index2 != 'denominateurTxRejet')){
                                var col = $('#'+index2).attr('class').substring(4);
                                cells[col] = '<td>'+this.nb_leads+'</td><td>'+this.ca_leads+' &euro;</td>';
                                leads_total_P += this.nb_leads;
                                ca_leads_total += this.ca_leads;
                              }
                            });
                            
                            for(var i = 1; i <= nb_cols; i++){
                              html += cells[i] ? cells[i] : '<td>0</td><td>0 &euro;</td>';
                            }

                            html += '<td>'+leads_total_P+'</td><td>'+ca_leads_total+' &euro;</td>';

                            if(index != 'total_users'){
                              total_general_nb_lead += this.nb_leads_total;
                              total_general_nb_lead_rejected += this.nb_leads_rejected;
                              total_denominateurTxRejet += (!isNaN(this.denominateurTxRejet) && this.denominateurTxRejet != 0) ? this.denominateurTxRejet : 0;
                              var txRejet = (!isNaN(this.denominateurTxRejet) && this.denominateurTxRejet != 0) ? (this.nb_leads_rejected / this.denominateurTxRejet * 100).toFixed(2) : 0;
                              html += '<td>'+this.nb_leads_total+'</td><td>'+txRejet+' % ('+this.nb_leads_rejected+' / '+this.denominateurTxRejet+')</td>';
                            }else{
                              var txRejetTotal = (!isNaN(total_denominateurTxRejet) && total_denominateurTxRejet != 0) ? (total_general_nb_lead_rejected / total_denominateurTxRejet * 100).toFixed(2) : 0;
                              html += '<td>'+total_general_nb_lead+'</td><td>'+txRejetTotal+' % ('+total_general_nb_lead_rejected+' / '+total_denominateurTxRejet+')</td>';
                            }
                            
                            html += "</tr>";
                            callchat_list.append(html);
                          });
                        }

	}
  };

  function getInfos(){

   var campaign_list = $("#campaign_list_body");
   var campaign_detail = $("#campaign_detail_body");
      campaign_list.empty();
      campaign_list.append( '<tr class="tr-new"><td class="date" colspan="7"><img src="../../ressources/images/lightbox-ico-loading.gif" alt="chargement..."></td></tr>');
      campaign_detail.empty();
      campaign_detail.append( '<tr class="tr-new"><td class="date" colspan="7"><img src="../../ressources/images/lightbox-ico-loading.gif" alt="chargement..."></td></tr>');

    var NB = $('input[name=NB]').val();
    var page = $('input[name=page]').val();
    var lastpage = $('input[name=lastpage]').val();
    var sort = $('input[name=sort]').val();
    var lastsort = $('input[name=lastsort]').val();
    var sortway = $('input[name=sortway]').val();
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

  function calculROI(idTypeCampaign){

    var depense = $('#depense_'+idTypeCampaign).val();
    if((!isNaN(parseFloat(depense)) && isFinite(depense)) == true){
      var gain = parseFloat($('#revenu_'+idTypeCampaign).html()) - depense;
      var ROI = parseFloat($('#revenu_'+idTypeCampaign).html()) / depense;
      
      $('#gain_'+idTypeCampaign).html(gain.toFixed(2)+' \u20AC');
      $('#ROI_'+idTypeCampaign).html(ROI.toFixed(2)+' %');
    }else{
      $('#gain_'+idTypeCampaign).html('');
      $('#ROI_'+idTypeCampaign).html('');
    }

  }

</script>
<link type="text/css" rel="stylesheet" href="<?php echo ADMIN_URL ?>ressources/css/command.css">
<link rel="stylesheet" type="text/css" href="reporting.css" />

<div class="titreStandard">Reporting call/chat</div>
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

<form id="commandesList" class="commandesForm" action="" method="get">
              <input type="hidden" name="NB" value="<?php echo NB ?>"/>
              <input type="hidden" name="page" value="<?php echo $page ?>"/>
              <input type="hidden" name="lastpage" value="<?php echo $lastpage ?>"/>
              <input type="hidden" name="sort" value="<?php echo $sort ?>"/>
              <input type="hidden" name="lastsort" value="<?php echo $lastsort ?>"/>
              <input type="hidden" name="sortway" value="<?php echo $sortway ?>"/>
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
//                      getInfos();
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

       <table  id="callchat_list" class="item-list">
                <thead>
                        <tr>
                                <th width="150"></th>
                                <?php
                                $a = 1;
                                if(!empty ( $originList ))
                                  foreach( $originList as $cleOrigin => $origin){
                                      echo '<th colspan="2" id="'.$cleOrigin.'" class="col_'.$a.'">'.$origin.'</th>';
                                      $a++;
                                  }
                                ?>
                                <th width="150" colspan="4">Totaux</th>
                        </tr>
                        <tr>
                                <th width="150"></th>
                                <?php
                                if(!empty ( $originList ))
                                  foreach( $originList as $origin)
                                      echo '<th>Nb leads P</th><th>CA leads</th>';
                                ?>
                                <th>Nb leads P</th><th>CA leads</th><th>Nb leads T</th><th>Tx rejets</th>
                        </tr>
                </thead>
                <tbody id="callchat_list_body">
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
