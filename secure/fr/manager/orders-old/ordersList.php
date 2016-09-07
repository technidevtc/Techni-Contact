<?php

/*================================================================/

 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com

 Auteur : OD pour Hook Network SARL - http://www.hook-network.com
 Date de création : 21 février 2011

 Fichier : /secure/fr/manager/orders/ordersList.php
 Description : Affichage liste des ordres

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$title = "Gestion des Ordres Fournisseurs";
//$navBar = "<a href=\"index.php?SESSION\" class=\"navig\">Gestion des Commandes Clients</a> &raquo; Editer une commande";
require(ADMIN."head.php");

require(ADMIN."statut.php");

$errorstring = "";

if (!$user->get_permissions()->has("m-comm--sm-partners-orders","red")) {
    print "Vous n'avez pas les droits adéquats pour réaliser cette opération";
    exit();
  }

  define('WHERE', WHERE_COMMANDS);

define('NB', 30);
define("__BEGIN_TIME__", mktime(0,0,0,1,1,2004));


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

var COMMON_ALL_M = "<?php echo COMMON_ALL_M ?>";
var COMMON_ALL_F = "<?php echo COMMON_ALL_F ?>";
var COMMON_ALL_CHOICE = "<?php echo COMMON_ALL_CHOICE ?>";

var MonthLabels = new Array('<?php echo COMMON_JANUARY ?>', '<?php echo COMMON_FEBRUARY ?>', '<?php echo COMMON_MARCH ?>', '<?php echo COMMON_APRIL ?>', '<?php echo COMMON_MAY ?>', '<?php echo COMMON_JUNE ?>', '<?php echo COMMON_JULY ?>', '<?php echo COMMON_AUGUST ?>', '<?php echo COMMON_SEPTEMBER ?>', '<?php echo COMMON_OCTOBER ?>', '<?php echo COMMON_NOVEMBER ?>', '<?php echo COMMON_DECEMBER ?>');
var DayLabes = new Array('<?php echo COMMON_SUNDAY ?>', '<?php echo COMMON_MONDAY ?>', '<?php echo COMMON_TUESDAY ?>', '<?php echo COMMON_WEDNESDAY ?>', '<?php echo COMMON_THURSDAY ?>', '<?php echo COMMON_FRIDAY ?>', '<?php echo COMMON_SATURDAY ?>');

var dateBegin = new Date(); dateBegin.setTime(<?php echo __BEGIN_TIME__ ?>*1000);
var dateCur   = new Date();

var JS__MSGR_CTXT_SUPPLIER_TC_ORDER__ = <?php echo __MSGR_CTXT_SUPPLIER_TC_ORDER__ ?>;
var JS__MSGR_CTXT_ORDER_CMD__ = <?php echo __MSGR_CTXT_ORDER_CMD__ ?>;

var AJAXHandle = {
	type : "GET",
	url: "AJAX_ordres-liste.php",
	dataType: "json",
	error: function (XMLHttpRequest, textStatus, errorThrown) {
                var tbody = $("#content-list");
			tbody.empty();
                        tbody.append( '<tr class="tr-new"><td class="date" colspan="9"> '+textStatus+'</td></tr>');
	},
	success: function (data, textStatus) {
            var tbody = $("#content-list");
			tbody.empty();

                        if(data.error){
                          tbody.append( '<tr class="tr-new"><td class="date" colspan="9" style="color : red"> '+data.error+'</td></tr>');
                        }
                        else if(data.reponses == 'vide'){
                          tbody.append( '<tr class="tr-new"><td class="date" colspan="9"> Aucun ordre n\'a été retrouvé sur cette période. </td></tr>');
                        }else{
                              for (i = 0; i < data.reponses.length; i++)
                              {
                                // tr type
                                var tr = '';
                                if(data.reponses[i].annulation == 1){
                                  tr = '<tr class="tr-cancelled">';
                                } else if(data.reponses[i].statut_traitement < 2){
                                  tr = '<tr class="tr-new" onmouseover="this.className=\'tr-newhover\'" onmouseout="this.className=\'tr-new\'">';
                                } else{
                                  tr = '<tr class="tr-normal" onmouseover="this.className=\'tr-hover\'" onmouseout="this.className=\'tr-normal\'">';
                                }

                                // date format
                                var date = new Date(data.reponses[i].timestampIMS*1000);
                                var year = date.getFullYear();
                                var month = date.getMonth()+1;
                                month = month.toString();
                                if(month.length !=2){month = '0'+month};
                                var day = date.getDate().toString();
                                if(day.length !=2){day = '0'+day};
                                var hours = date.getHours().toString();
                                if(hours.length !=2){hours = '0'+hours};
                                var minutes = date.getMinutes().toString();
                                if(minutes.length !=2){minutes = '0'+minutes};
                                var seconds = date.getSeconds().toString();
                                if(seconds.length !=2){seconds = '0'+seconds};
                                date = day+'/'+month+'/'+year+' '+hours+':'+minutes;

                                // format numero commande
                                var num_commande = data.reponses[i].idAdvertiser+'-'+data.reponses[i].idCommande;

                                // format statut
                                var statut = '-';
                                if(data.reponses[i].annulation == 1){
                                  statut = 'Annulée';
                                }else if((data.reponses[i].attente_info == JS__MSGR_CTXT_ORDER_CMD__) || (data.reponses[i].attente_info == JS__MSGR_CTXT_SUPPLIER_TC_ORDER__)){
                                  statut = 'Attente d\'information supp.';
                                }else{
                                    if(data.reponses[i].statut_traitement < 3){
                                      statut = 'Non encore consultée';
                                    }
                                    else if(data.reponses[i].statut_traitement == 3){
                                      statut = 'Attente Accusé Réception fournisseur';
                                    }
                                    else if(data.reponses[i].statut_traitement == 4){ //arc recu
                                      if(data.reponses[i].statut_commande >= 25){
                                        statut = 'Date expédition fixée';
                                      }else{
                                         statut = 'AR commande reçu';
                                      }
                                    }
                                }
                                var nom_client = data.reponses[i].nom+' '+data.reponses[i].prenom;
                                var attente_info = (data.reponses[i].attente_info == JS__MSGR_CTXT_ORDER_CMD__) || (data.reponses[i].attente_info == JS__MSGR_CTXT_SUPPLIER_TC_ORDER__) ? 'Oui' : 'Non';

                                // differenciation des produits + montant commande
                                var produits = '';
                                var montant = parseFloat(0);
                                $.each(data.reponses[i].produits, function(){
                                  produits = produits + this[2]+ ' x '+this[9] + "<br />";
                                  montant = montant+(((parseFloat(this[6]) * parseFloat(this[9])) * parseFloat(this[10]))/100) +parseFloat(this[6]);
                                });

                                      tbody.append(
                                              tr +
                                              "	<td class=\"date\"><a href=\"orderDetail.php?idOrdre="+data.reponses[i].idAdvertiser+"-"+data.reponses[i].idCommande+"\" target=\"_blank\"><img src=\"../../ressources/images/application_double.png\" alt=\"Nouvelle fenêtre\" /></a></td>" +
                                              "	<td class=\"date\" onclick=\"document.location='orderDetail.php?idOrdre="+data.reponses[i].idAdvertiser+"-"+data.reponses[i].idCommande+"'\">"+data.reponses[i].societe+"</td>" +
                                              "	<td class=\"date\" onclick=\"document.location='orderDetail.php?idOrdre="+data.reponses[i].idAdvertiser+"-"+data.reponses[i].idCommande+"'\">" +num_commande+"</td>" +
                                              "	<td class=\"date\" onclick=\"document.location='orderDetail.php?idOrdre="+data.reponses[i].idAdvertiser+"-"+data.reponses[i].idCommande+"'\">"+data.reponses[i].nom_advertiser+"</td>" +
                                              "	<td class=\"date\" onclick=\"document.location='orderDetail.php?idOrdre="+data.reponses[i].idAdvertiser+"-"+data.reponses[i].idCommande+"'\">"+date+"</td>" +
                                              "	<td class=\"produit\" onclick=\"document.location='orderDetail.php?idOrdre="+data.reponses[i].idAdvertiser+"-"+data.reponses[i].idCommande+"'\">" + data.reponses[i].nom_operateur + "</td>" +
                                              "	<td class=\"type\" onclick=\"document.location='orderDetail.php?idOrdre="+data.reponses[i].idAdvertiser+"-"+data.reponses[i].idCommande+"'\">" +statut+  "</td>" +
                                              "	<td class=\"date\" onclick=\"document.location='orderDetail.php?idOrdre="+data.reponses[i].idAdvertiser+"-"+data.reponses[i].idCommande+"'\">"+attente_info+"</td>" +
                                              "	<td class=\"nombre\" onclick=\"document.location='orderDetail.php?idOrdre="+data.reponses[i].idAdvertiser+"-"+data.reponses[i].idCommande+"'\">" +parseFloat(data.reponses[i].totalOrdreHT).toFixed(2)+" &#8364;</td>" +
                                              "	<td class=\"nombre\" onclick=\"document.location='orderDetail.php?idOrdre="+data.reponses[i].idAdvertiser+"-"+data.reponses[i].idCommande+"'\">" +parseFloat(data.reponses[i].totalOrdreTTC).toFixed(2)+" &#8364;</td>" +
                                              "</tr>");
                              }
                        }

                        if(data.pagination){
                            var divPagination = $(".listing");
                                divPagination.empty();
                                var visible1 ;
                                var visible2 ;
                                var visible3 ;
                                var visible4 ;
                                var page = parseInt(data.pagination['page']) ;
                                var lastpage = parseInt(data.pagination['lastpage']) ;
                                if(page > 2){visible1 = 'visible'}else{visible1 = 'hidden'};
                                if(page > 1){visible2 = 'visible'}else{visible2 = 'hidden'};
                                if(page < lastpage){visible3 = 'visible'}else{visible3 = 'hidden'};
                                if(page < lastpage-1){visible4 = 'visible'}else{visible4 = 'hidden'};
                                var html = "<span style=\"visibility: "+visible1+"\"><a href=\"javascript: gotoPage(1)\">&lt;&lt;</a></span> "+
					"<span style=\"visibility: "+visible2+"\"><a href=\"javascript: gotoPage("+(page-1)+")\">&lt;</a> ... |</span> "+
					"<span style=\"visibility: "+visible2+"\"><a href=\"javascript: gotoPage("+(page-1)+")\">"+(page-1)+"</a> |</span> "+
					"<span class=\"listing-current\">"+page+"</span> "+
					"<span style=\"visibility: "+visible3+"\">| <a href=\"javascript: gotoPage("+(page+1)+")\">"+(page+1)+"</a></span> "+
					"<span style=\"visibility: "+visible3+"\">| ... <a href=\"javascript: gotoPage("+(page+1)+")\">&gt;</a></span> "+
					"<span style=\"visibility: "+visible4+"\"><a href=\"javascript: gotoPage("+lastpage+")\">&gt;&gt;</a></span> ";

                                divPagination.append(html);

                                $('input[name=page]')[0].value = page;
                                $('input[name=lastpage]')[0].value = lastpage;
                                $('input[name=sort]')[0].value = data.pagination['sort'];
                                $('input[name=lastsort]')[0].value = data.pagination['lastsort'];
                                $('input[name=sortway]')[0].value = data.pagination['sortway'];

                        }
	}
  };

  function updateListe(){

   var tbody = $("#content-list");
                tbody.empty();
                tbody.append( '<tr class="tr-new"><td class="date" colspan="7"><img src="../../ressources/images/lightbox-ico-loading.gif" alt="chargement..."></td></tr>');

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
    var searchType = $('select[name=searchType]').val() ? $('select[name=searchType]').val() : 0;
    var searchText = $('input[name=searchText]').val();
    var filter_status = $('select[name=filter_status]').val() ? $('select[name=filter_status]').val() : 0;
    var info_requiered = $('input[name=info_requiered]:checked')[0] ? 1 : 0;

    AJAXHandle.data = "NB="+NB+"&page="+page+"&lastpage="+lastpage+"&sort="+sort+"&lastsort="+lastsort+"&sortway="+sortway
      +"&dateFilterType="+dateFilterType+"&yearS="+yearS+"&monthS="+monthS+"&dayS="+dayS
      +"&yearS2="+yearS2+"&monthS2="+monthS2+"&dayS2="+dayS2+"&yearE="+yearE+"&monthE="+monthE+"&dayE="+dayE
      +"&findType="+searchType+"&findText="+searchText+"&filter_status="+filter_status+"&info_requiered="+info_requiered;
    $.ajax(AJAXHandle);

 return false;
}

</script>
<link type="text/css" rel="stylesheet" href="<?php echo ADMIN_URL ?>ressources/css/command.css">
<script src="../js/ManagerFunctions.js" type="text/javascript"></script>
<!--<script src="<?php echo SECURE_RESSOURCES_URL ?>scripts/AJAX_search.js" type="text/javascript"></script>-->
<div class="titreStandard">Liste des Ordres Fournisseurs</div>
<br />
<div class="bg" style="min-width: 980px">
<style type="text/css">
#DateFilter { display: none; float: left; height: 80px; width: 780px;}
#DateIntervalFilter { display: none;  float: left;height: 140px; width: 780px;}
#otherFilters{display: block;float: right;height: 80px; width: 43%;}
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
                      <input type="button" value="OK"  onClick="updateListe();return false;" />
                      </div>
                      <div style="float:right; margin-top: 30px">
                        Rechercher :
                        <select name="searchType">
                                <option value="3"<?php if($searchType==3) { ?> selected="selected"<?php } ?>>nom fournisseur</option>
                                <option value="1"<?php if($searchType==1) { ?> selected="selected"<?php } ?>>ref commande</option>
                                <option value="2"<?php if($searchType==2) { ?> selected="selected"<?php } ?>>ref ordre</option>
                        </select>
                        <input type="text" name="searchText" value="<?php echo $searchText ?>" />
                        <input type="button" value="OK"  onClick="updateListe();return false;" />
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
                      updateListe();
                      /* ]]>*/ </script>
                      <input type="button" value="Choisir une date simple" onclick="ShowDateSection()">
                      <input type="button" value="OK"  onClick="updateListe();return false;" />
                 </div>
                <div style="float:right; margin-top: 30px">
                  Rechercher :
                  <select name="searchType">
                          <option value="0">-</option>
                          <option value="1"<?php if($searchType==1) { ?> selected="selected"<?php } ?>>ref commande</option>
                          <option value="2"<?php if($searchType==2) { ?> selected="selected"<?php } ?>>ref ordre</option>
                          <option value="3"<?php if($searchType==3) { ?> selected="selected"<?php } ?>>nom fournisseur</option>
                  </select>
                  <input type="text" name="searchText" value="<?php echo $searchText ?>" />
                  <input type="button" value="OK"  onClick="updateListe();return false;" />
                </div>
                <div class="zero"></div>
              </div>
              <div id="otherFilters">
                
                <div id="filtre_status">
                    Filtre État : <select name="filter_status">
                    <option value="0">-</option>
                    <option value="1">Non encore consultée</option>
                    <option value="2">Attente Accusé Réception fournisseur</option>
                    <option value="3">AR commande reçu</option>
                    <option value="4">Annulée</option>
                  </select>
                    <input type="button" value="OK"  onClick="updateListe();return false;" />
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    Ordres avec demande d'info :
                    <input type="checkbox" name="info_requiered" value="info_requiered" onClick="javascript:updateListe();" />
                </div>
              </div>
          <div class="zero"></div>
      </form>
      <script type="text/javascript">/* <![CDATA[ */<?php  echo ($dateFilterType == "interval") ? "ShowDateIntervalSection();" : "ShowDateSection();" ?>/* ]]> */</script>


<br/>

		
        <table id="liste_commandes" class="liste_cmd" border="0" cellpadding="2" cellspacing="0" width="100%">
                <thead>
                        <tr class="tr-titre">
                                <th style="width:40px;"></th>
                                <th width="250"><a href="javascript: CommandSort('customer_name')">Nom client</a></th>
                                <th width="150"><a href="javascript: CommandSort('ref')">Ref ordre fournisseur</a></th>
                                <th width="250"><a href="javascript: CommandSort('advertiser')">Fournisseur concerné</a></th>
                                <th style="min-width:90px;"><a href="javascript: CommandSort('date')">Date</a></th>
                                <th><a href="javascript: CommandSort('sender')">Donneur d'ordre</a></th>
                                <th style="min-width:230px;"><a href="javascript: CommandSort('status')">Etat</a></th>
                                <th><a href="javascript: CommandSort('messenger')">Attente Info</a></th>
                                <th width="80"><a href="javascript: CommandSort('amountHT')">Total HT</a></th>
                                <th width="80"><a href="javascript: CommandSort('amountTTC')">Total TTC</a></th>

                        </tr>
                </thead>
                <tbody id="content-list">
                  <tr class="tr-new"><td class="date" colspan="7"><img src="../../ressources/images/lightbox-ico-loading.gif" alt="chargement..."></td></tr>
                </tbody>
        </table>

<script type="text/javascript">
<!--
function gotoPage(page)
{
	if (!isNaN(page = parseInt(page)))
	{
		$('input[name=page]')[0].value = page;
		updateListe();
	}
}

function CommandSort(col){

  var sortway = $('input[name=sortway]').val();
  var formerSort = $('input[name=sort]').val();

    $('input[name=sort]').val(col);
    $('input[name=lastsort]').val(col);
    if(col == formerSort){
      sortway = sortway == 'asc' ? 'desc' : 'asc';
    }
    $('input[name=sortway]').val(sortway);
    updateListe();

}

var supplierSearch = false;
supplierAutocomplete = new HN.UI.AutoCompletion($('input[name=searchText]').get(0));
        supplierAutocomplete.queryURL = 'AJAX_autocompleteSupplier.php';
  var selectValue = $("select[name=searchType]");
  selectValue.change(function(){
    if($("select[name=searchType] option:selected")[0].value  == 3){
        supplierAutocomplete = new HN.UI.AutoCompletion($('input[name=searchText]').get(0));
        supplierAutocomplete.queryURL = 'AJAX_autocompleteSupplier.php';
    }else{
      delete HN.UI.AutoCompletion($('input[name=searchText]').get(0));
      $('.auto-completion-box')[1].remove;
    }
  }
);

//-->
</script>

				<div class="listing"></div>

</div>
  </div>
<?php

require(ADMIN . 'tail.php');

