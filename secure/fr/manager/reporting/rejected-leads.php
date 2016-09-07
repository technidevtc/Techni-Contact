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

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$title = 'Taux de rejets';
$navBar = $title;

require(ADMIN . 'head.php');

if (!$userChildScript->get_permissions()->has("m-reporting--sm-rejected-leads","r")) {
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
        $('input[name=dateFilterType]').val("simple") ;
}

function ShowDateIntervalSection()
{
	document.getElementById('DateFilter').style.display = "none";
	document.getElementById('DateIntervalFilter').style.display = "block";
        $('input[name=dateFilterType]').val("interval");
}

var AJAXHandle = {
	type : "GET",
	url: "AJAX_rejected-leads.php",
	dataType: "json",
	error: function (XMLHttpRequest, textStatus, errorThrown) {
                var tbody = $("#rejected-list");
			tbody.empty();
                        tbody.append( '<tr class="tr-new"><td class="date" colspan="5"> '+textStatus+'</td></tr>');
	},
	success: function (data, textStatus) {
            var rejectedLeads_list = $("#rejectedLeads_list_body");
                        rejectedLeads_list.empty();

                        if(data.error){
                          rejectedLeads_list.append( '<tr class="tr-new"><td class="date" colspan="5" style="color : red"> '+data.error+'</td></tr>');
                        }
                        else if(data.reponses == 'vide'){
                          rejectedLeads_list.append( '<tr class="tr-new"><td class="date" colspan="5"> Aucune information n\'a été retrouvé sur cette période. </td></tr>');
                        }else{

                          var adv_cat_list = <?php mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $adv_cat_list);echo json_encode($adv_cat_list) ?>;

                          $.each(data.reponses, function(index){
                            var html = '';
                            // tr type
                            var tr = '';
                            tr = '<tr class="tr-normal" onmouseover="this.className=\'tr-hover\'" onmouseout="this.className=\'tr-normal\'">';

                            html = tr+"	<td class=\"date\">"+this.nom1+"</td>";
//                            Le taux de rejet est calculé de la façon suivante :
//                            Nb de leads rejetés dans la période / Nb de leads rejetés dans la période + Nb de leads facturables ou facturés * 100
//                            var rejection_rate = parseInt(this.nb_rejected) /(parseInt(this.nb_rejected)+parseInt(this.nb_charged))*100;

//                            rejection_rate = !isNaN(rejection_rate) ? rejection_rate.toFixed(2) : 0;
                            html += '<td>'+adv_cat_list[this.category].name+'</td><td>'+this.nb_primary+'</td><td>'+this.nb_leads+'</td><td>'+this.nb_rejected+'</td><td>'+(this.tx_reject).toFixed(2)+' %</td>';
                            
                            html += "</tr>";
                            rejectedLeads_list.append(html);
                          });

                          if(data.pagination){
//                            var divPagination = $(".listing");
//                                divPagination.empty();
//                                var visible1 ;
//                                var visible2 ;
//                                var visible3 ;
//                                var visible4 ;
//                                var page = parseInt(data.pagination['page']) ;
//                                var lastpage = parseInt(data.pagination['lastpage']) ;
//                                if(page > 2){visible1 = 'visible'}else{visible1 = 'hidden'};
//                                if(page > 1){visible2 = 'visible'}else{visible2 = 'hidden'};
//                                if(page < lastpage){visible3 = 'visible'}else{visible3 = 'hidden'};
//                                if(page < lastpage-1){visible4 = 'visible'}else{visible4 = 'hidden'};
//                                var html = "<span style=\"visibility: "+visible1+"\"><a href=\"javascript: gotoPage(1)\">&lt;&lt;</a></span> "+
//					"<span style=\"visibility: "+visible2+"\"><a href=\"javascript: gotoPage("+(page-1)+")\">&lt;</a> ... |</span> "+
//					"<span style=\"visibility: "+visible2+"\"><a href=\"javascript: gotoPage("+(page-1)+")\">"+(page-1)+"</a> |</span> "+
//					"<span class=\"listing-current\">"+page+"</span> "+
//					"<span style=\"visibility: "+visible3+"\">| <a href=\"javascript: gotoPage("+(page+1)+")\">"+(page+1)+"</a></span> "+
//					"<span style=\"visibility: "+visible3+"\">| ... <a href=\"javascript: gotoPage("+(page+1)+")\">&gt;</a></span> "+
//					"<span style=\"visibility: "+visible4+"\"><a href=\"javascript: gotoPage("+lastpage+")\">&gt;&gt;</a></span> ";
//
//                                divPagination.append(html);

//                                $('input[name=page]')[0].value = page;
//                                $('input[name=formerpage]')[0].value = data.pagination['formerpage'];
                                $('input[name=sort]')[0].value = data.pagination['sort'];
                                $('input[name=lastsort]')[0].value = data.pagination['lastsort'];
                                $('input[name=sortway]')[0].value = data.pagination['sortway'];
                         }


                        }
	}
  };

  function getInfos(){

   var rejected_list = $("#rejected_list_body");
   var rejected_detail = $("#rejected_detail_body");
      rejected_list.empty();
      rejected_list.append( '<tr class="tr-new"><td class="date" colspan="7"><img src="../../ressources/images/lightbox-ico-loading.gif" alt="chargement..."></td></tr>');
      rejected_detail.empty();
      rejected_detail.append( '<tr class="tr-new"><td class="date" colspan="7"><img src="../../ressources/images/lightbox-ico-loading.gif" alt="chargement..."></td></tr>');

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

    var dateStart = monthS2 != 0 ? (dayS2>9?dayS2:'0'+dayS2)+'/'+(monthS2>9?monthS2:'0'+monthS2)+'/'+yearS2 : (dayS>9?dayS:'0'+dayS)+'/'+(monthS>9?monthS:'0'+monthS)+'/'+yearS;
    $('input[name=DateBegin]').val(dateStart);
    var dateEnd = monthE != 0 ? (dayE>9?dayE:'0'+dayE)+'/'+(monthE>9?monthE:'0'+monthE)+'/'+yearE : '';
    $('input[name=DateEnd]').val(dateEnd);

    AJAXHandle.data = "dateFilterType="+dateFilterType+"&yearS="+yearS+"&monthS="+monthS+"&dayS="+dayS
      +"&yearS2="+yearS2+"&monthS2="+monthS2+"&dayS2="+dayS2+"&yearE="+yearE+"&monthE="+monthE+"&dayE="+dayE
      +"&sort="+sort+"&lastsort="+lastsort+"&sortway="+sortway;
    $.ajax(AJAXHandle);

    return false;
  }


</script>
<link type="text/css" rel="stylesheet" href="<?php echo ADMIN_URL ?>ressources/css/command.css">
<link rel="stylesheet" type="text/css" href="reporting.css" />

<div class="titreStandard">Taux de rejets</div>
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
      <form style="float: right; margin-left: 10px" method="post" action="rejected_list_extract.php">
        <div>
          <input type="hidden" value="simple" name="dateFilterType">
                <input type="hidden" value="<?php echo $dayS.'/'.$monthS.'/'.$yearS ?>" name="DateBegin">
                <input type="hidden" value="<?php echo $dayE.'/'.$monthE.'/'.$yearE ?>" name="DateEnd">
                <input type="submit" value="Télécharger leads rejetés en xls">
        </div>
      </form>
  <form style="float: right;" method="post" action="rejected_reporting_extract.php">
              <div>
                <input type="hidden" value="simple" name="dateFilterType">
                      <input type="hidden" value="<?php echo $dayS.'/'.$monthS.'/'.$yearS ?>" name="DateBegin">
                      <input type="hidden" value="<?php echo $dayE.'/'.$monthE.'/'.$yearE ?>" name="DateEnd">
                      <input type="submit" value="Télécharger le tableau en xls">
              </div>
      </form>
      <script type="text/javascript">/* <![CDATA[ */<?php  echo ($dateFilterType == "interval") ? "ShowDateIntervalSection();" : "ShowDateSection();" ?>/* ]]> */</script>

<br/>
<br/>

       <table  id="rejectedLeads_list" class="item-list">
                <thead>
                        <tr>
                                <th width="16,66%">Nom partenaire</th>
                                <th width="16,66%">Typologie partenaire</th>
                                <th width="16,66%">Nb de leads primaires reçus</th>
                                <th width="16,66%"><a href="javascript: rejectSort('nbLeads')">Nb de leads total reçus</a></th>
                                <th width="16,66%">Nb de leads rejetés</th>
                                <th width="16,66%"><a href="javascript: rejectSort('txRejet')">Taux de rejet</a></th>
                        </tr>
                </thead>
                <tbody id="rejectedLeads_list_body">
                  <tr class="tr-new"><td class="date" colspan="8"><img src="../../ressources/images/lightbox-ico-loading.gif" alt="chargement..."></td></tr>
                </tbody>
        </table>

<script type="text/javascript">
<!--
var dateCur   = new Date();

    $('#MonthID option[value='+(dateCur.getMonth()+1)+']').attr('selected', 'selected');
    FillDayOptions('YearID', 'MonthID', 'DayID');
    $('#DayID option[value='+dateCur.getDate()+']').attr('selected', 'selected');

    function rejectSort(order){
      var sortway = $("input[name=sortway]").val();
      if($("input[name=sort]").val() == order){
        if(sortway == '')
          sortway = 'asc';
        else if(sortway == 'desc')
          sortway = 'asc';
        else if(sortway == 'asc')
          sortway = 'desc';
      }else
          sortway = 'asc';

      $("input[name=sort]").val(order);
      $("input[name=sortway]").val(sortway);
      
      var lastsort = $("input[name=lastsort]").val();
      AJAXHandle.data = 'sort='+order+'&sortway='+sortway+'&lastsort='+lastsort;
      getInfos();
    }
    
    getInfos();

//-->
</script>

				<div class="listing"></div>

</div>
  </div>
<?php

require(ADMIN . 'tail.php');

?>
