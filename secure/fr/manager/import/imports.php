<?php

/*================================================================/

 Techni-Contact V4 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 15 février 2007

 Mises à jour :

 Fichier : /secure/manager/stats/index.php
 Description : Index des statistiques
 
/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$title = 'Imports';
$navBar = 'Liste des importations';

require(ADMIN . 'head.php');
$lastpage = 100;
$page = 24;

?>
<div class="titreStandard">Imports</div>
<br />
<div class="bg" style="position: relative">
<link href="HN.css" rel="stylesheet" type="text/css"/>
<style type="text/css">
#ImportsTable table { min-width: 1000px; }
#ImportsTable table tr.status-nvf { background-color: #FFFFFF; }
#ImportsTable table tr.status-nv { background-color: #FFFFD0; }
#ImportsTable table tr.status-nf { background-color: #FFD0FF; }
#ImportsTable table tr.status-n { background-color: #FFD0D0; }
#ImportsTable table tr.status-vf { background-color: #D0FFFF; }
#ImportsTable table tr.status-v { background-color: #D0FFD0; }
#ImportsTable table tr.status-f { background-color: #D0D0FF; }
#ImportsTable table tr.status-0 { background-color: #D0D0D0; }

#ImportsTable table .column-edit { min-width: 30px; text-align: center; }
#ImportsTable table .column-edit .check { float: left; }
#ImportsTable table .column-edit .edit { float: left; padding: 0 2px; width: 16px; height: 16px; background: url(b_edit.png) 2px 0px no-repeat; }
#ImportsTable table .column-edit .del { float: left; padding: 0 2px; width: 16px; height: 16px; background: url(b_drop.png) 2px 0px no-repeat; }
#ImportsTable table .column-0 { min-width: 150px; text-align: center; }
#ImportsTable table .column-1 { min-width: 130px; text-align: center; }
#ImportsTable table .column-2 { min-width: 130px; text-align: center; }
#ImportsTable table .column-3 { min-width: 80px; text-align: center; }
#ImportsTable table .column-4 { min-width: 80px; text-align: center; }

#ImportWindowShad { z-index: 1; position: absolute; top: -50px; left: 55px; width: 424px; height: 127px; background-color: #000000; visibility: hidden; filter: Alpha (opacity=50, finishopacity=50, style=1) -moz-opacity:.50; opacity:.50; }
#ImportWindow { z-index: 2; position: absolute; top: -55px; left: 50px; width: 420px; height: 123px; border: 2px solid #999999; background-color: #E9EFF8; visibility: hidden; }
#ImportWindowBG { height: 58px; }

#label_file					{ top: 45px; left: 20px; }
#import_file				{ top: 45px; left: 120px; }
#label_advertiser			{ top: 80px; left: 20px; }
#import_advertiser			{ top: 80px; left: 120px; width: 190px; }
#import_advertiser_button	{ top: 80px; left: 314px; width: 82px; }

#Choose-adv { z-index: 3; width: 530px; position: absolute; top: 50px; left: 45px; }
.window-silver { padding: 5px; font: normal 11px Tahoma, Arial, Helvetica, sans-serif; }
.window-silver a { color: #000000; font-weight: normal; }
.window-silver a:hover { font-weight: normal; }

.tab_menu { height: 24px; padding: 0 5px 0 5px; position: relative; top: 1px; }

.tab_menu .tab { float: left; width: 118px; text-align: center; cursor: default; }

.tab_menu .tab_lb_i, .tab_menu .tab_lb_a, .tab_menu .tab_lb_s, .tab_menu .tab_rb_i, .tab_menu .tab_rb_a, .tab_menu .tab_rb_s, .tab_menu .tab_lb_c, .tab_menu .tab_rb_c { float: left; width: 4px; height: 23px; }
.tab_menu .tab_lb_i { background : url(tab-left-border.gif) repeat-x; }
.tab_menu .tab_lb_a { background : url(tab-active-left-border.gif) repeat-x; }
.tab_menu .tab_rb_i { background : url(tab-right-border.gif) repeat-x; }
.tab_menu .tab_rb_a { background : url(tab-active-right-border.gif) repeat-x; }
.tab_menu .tab_lb_s { height: 24px; background : url(tab-active-left-border.gif) repeat-x;}
.tab_menu .tab_rb_s { height: 24px; background : url(tab-active-right-border.gif) repeat-x; }
.tab_menu .tab_lb_c { height: 24px; background : url(tab-active-left-border.gif) repeat-x;}
.tab_menu .tab_rb_c { height: 24px; background : url(tab-active-right-border.gif) repeat-x; }

.tab_menu .tab_bg_i, .tab_menu .tab_bg_a, .tab_menu .tab_bg_s, .tab_menu .tab_bg_c { height: 17px; float: left; width: 90px; text-align: left; color: #000000; padding: 6px 10px 0px 10px; white-space: nowrap; }
.tab_menu .tab_bg_i { background: url(tab-bg.gif) repeat-x; }
.tab_menu .tab_bg_a { background: url(tab-active-bg.gif) repeat-x; }
.tab_menu .tab_bg_s { height: 18px; background: url(tab-active-bg.gif) repeat-x; font-weight: bold; }
.tab_menu .tab_bg_c { height: 18px; background: url(tab-active-bg.gif) repeat-x; font-weight: bold; }

.menu-below { border: 1px solid #808080; height: 2px; font-size: 0; border-bottom: none; background-color: #D8D4CD; }
.main { border: 1px solid #808080; background-color: #DEDCD6; }

.search_menu { width: 516px; cursor: default; padding: 3px 6px; border-bottom: 1px solid #808080; display: block; float: left}
.search_menu span { border: 1px solid #DEDCD6; padding: 2px 5px; outline: none; }
.search_menu span.over { border-color: #FFFFFF #808080 #808080 #FFFFFF; }
.search_menu span.down { border-color: #808080 #FFFFFF #FFFFFF #808080; }
.search_menu span.selected { border-color: #808080 #FFFFFF #FFFFFF #808080; }

.body { padding: 2px 4px; background-color: #DEDCD6; border-top: 1px solid #FFFFFF; clear: left}
.body .colg { float: left; width: 258px; margin-right: 5px; }
.body .colc { float: left; width: 257px; }
.body .col-title { cursor: default; font-weight: bold; margin: 2px; }
.body .colg .list { width: 252px; height: 298px; background-color: #FFFFFF; border: 2px inset #808080; margin: 0; padding: 1px; list-style-type: none; overflow: auto; }
.body .colg .list li { cursor: default; white-space: nowrap; }
.body .colg .list li.over { background-color: #316AC5; color: #FFFFFF; }
.body .colg .list li.selected { background-color: #0C266C; color: #FFFFFF; }

.body .colc .infos { position: relative; height: 290px; background-color: #FFFFFF; border: 2px inset #808080; padding: 5px; }

.body .colc .select_label { padding: 0 5px 0 20px; }
.body .colc input.button { top: 275px; left: 5px; width: 243px; }

</style>
<script type="text/javascript">
var __SID__ = '<?php echo $sid ?>';
var __MAIN_SEPARATOR__ = '<?php echo __MAIN_SEPARATOR__ ?>';
var __OUTPUT_SEPARATOR__ = '<?php echo __OUTPUT_SEPARATOR__ ?>';
var __OUTPUTID_SEPARATOR__ = '<?php echo __OUTPUTID_SEPARATOR__ ?>';
</script>
<script src="Classes.js" type="text/javascript"></script>
<script src="../js/ManagerFunctions.js" type="text/javascript"></script>
<div id="PerfReqImports" class="PerfReqLabel"></div>
<div id="ProductGetError" class="InfosError">
<?php
if (isset($_GET['error']))
{
	switch($_GET['error'])
	{
		case "permissions": print "Erreur Fatale : vous n'avez pas les droits adéquats pour réaliser cette opération."; break;
                case "file" : print "Erreur Fatale lors du chargement du fichier<br/>"; break;
		case "fileext" : print "Erreur Fatale : type de l'extension du fichier non reconnu<br/>"; break;
		case "advertiser" : print "Erreur Fatale : L'annonceur/fournisseur n'est pas reconnu<br/>"; break;
		case "colstitle" : print "Erreur Fatale : Plusieurs colonnes sont manquantes dans le fichier XLS pour qu'il puisse être reconnu comme valide<br/>"; break;
		case "importType": print "Erreur Fatale : type d'import non défini<br />."; break;
                default : print "<br/>"; break;
	}
}
else print "<br/>";
?>
</div>

<div class="blocka"><a href="javascript: newimport.Show()">Créer un nouvel import</a></div>
<div id="ImportWindow">
	<form id="ImportWindowForm" action="make_import.php?typeImport=<?php echo __IMPT_PDT__ ?>" method="post" enctype="multipart/form-data">
		<div id="ImportWindowBG" class="window_bg">
			<span id="label_file" class="label">Fichier : </span><input id="import_file" name="import_file" type="file" class="field" size="28"/>
			<span id="label_advertiser" class="label">Annonceur : </span><input id="import_advertiser" name="import_advertiser" type="text" maxlength="255" class="field"/><input id="import_advertiser_button" type="button" class="button" value="choisir" onclick="ShowAdvertiserSearchWindow();"/>
		</div>
	</form>
</div>

<div class="window-silver" id="Choose-adv" style="display: none;">
	<div id="main_menu" class="tab_menu">
          <div class="tab" style="-moz-user-select: none;float: right;" onclick="HideAdvertiserSearchWindow()">
            <img class="tab_lb_c">
            <div class="tab_bg_c">Fermer</div>
            <img class="tab_rb_c">
          </div>
        </div>
	<div class="menu-below"></div>
	<div class="main" id="main_tabs">
		<div id="Advertisers">
			<div id="search_menuA" class="search_menu"></div>
			<div class="body">
				<div class="colg">
					<div class="col-title" onmousedown="grab(document.getElementById('Choose-adv'))">Liste des annonceurs</div>
					<ul class="list" id="listA">
					</ul>
				</div>
				<div class="colc">
					<div class="col-title" onmousedown="grab(document.getElementById('Choose-adv'))">Informations</div>
					<div class="infos">
						<div id="infosA"></div>
						<input type="button" class="button" value="Valider" onclick="SetAdvertiserName(ElementListA);"/>
					</div>
				</div>
				<div class="zero"></div>
			</div>
		</div>
		<div id="Suppliers">
			<div id="search_menuS" class="search_menu"></div>
			<div class="body">
				<div class="colg">
					<div class="col-title" onmousedown="grab(document.getElementById('Choose-adv'))">Liste des fournisseurs</div>
					<ul class="list" id="listS">
					</ul>
				</div>
				<div class="colc">
					<div class="col-title" onmousedown="grab(document.getElementById('Choose-adv'))">Informations</div>
					<div class="infos">
						<div id="infosS"></div>
						<input type="button" class="button" value="Valider" onclick="SetAdvertiserName(ElementListS);"/>
					</div>
				</div>
				<div class="zero"></div>
			</div>
		</div>
	</div>
</div>

<div id="PageSwitcher1"></div>
<div class="zero"></div>
<div id="ImportsTable"></div>
<div id="PageSwitcher2"></div>
<div class="zero"></div>
<br />
<script type="text/javascript">
function ShowAdvertiserSearchWindow() { document.getElementById('Choose-adv').style.display = 'block'; }
function HideAdvertiserSearchWindow() { document.getElementById('Choose-adv').style.display = 'none'; }
function SetAdvertiserName(ElementList) { if (ElementList.SelectedObject) document.getElementById("import_advertiser").value = ElementList.SelectedObject.firstChild.nodeValue; HideAdvertiserSearchWindow(); }

function showtab(tc, layerID)
{
	for (var t in tc)
	{
		if (t == layerID) document.getElementById(layerID).style.display = "block";
		else document.getElementById(t).style.display = "none";
	}
}

var MenuTabs = new TabList("main_menu", showtab, { "Advertisers" : "Annonceurs", "Suppliers" : "Fournisseur" } );
MenuTabs.Draw();
MenuTabs.tc["Advertisers"].onclick();

// Tab Advertisers
var SearchMenuA = new SearchMenu("search_menuA",
	{"0-9" : function () {
			ElementListAHandle.QueryA('AdvertisersSearch.php?' + __SID__ + '&AdvertisersSearchText=' + escape('[_0-9]'));
			document.getElementById('infosA').innerHTML = "Choisissez un annonceur";
		},
	"[A-Z]" : function (letter) {
			ElementListAHandle.QueryA('AdvertisersSearch.php?' + __SID__ + '&AdvertisersSearchText=' + escape(letter));
			document.getElementById('infosA').innerHTML = "Choisissez un annonceur";
		}
	}, "span");
var ElementListA = new ElementList("listA", "li", ShowAdvertiserInfos);
var ElementListAHandle = new AJAXHandle(ElementListAProcessResponse, "PerfReqImports");
var InfosAHandle = new AJAXHandle(InfosAProcessResponse, "PerfReqImports");
SearchMenuA.Draw();

function ShowAdvertiserInfos(id) { InfosAHandle.QueryA('AdvertisersInfos.php?' + __SID__ + '&id=' + id); }
function ElementListAProcessResponse(xhr) { ElementListProcessResponse(ElementListA, xhr); }
function InfosAProcessResponse(xhr) { InfosProcessResponse("infosA", xhr); }

// Tab Suppliers
var SearchMenuS = new SearchMenu("search_menuS",
	{"0-9" : function () {
			ElementListSHandle.QueryA('SuppliersSearch.php?' + __SID__ + '&SuppliersSearchText=' + escape('[_0-9]'));
			document.getElementById('infosS').innerHTML = "Choisissez un fournisseur";
		},
	"[A-Z]" : function (letter) {
			ElementListSHandle.QueryA('SuppliersSearch.php?' + __SID__ + '&SuppliersSearchText=' + escape(letter));
			document.getElementById('infosS').innerHTML = "Choisissez un fournisseur";
		}
	}, "span");
var ElementListS = new ElementList("listS", "li", ShowSupplierInfos);
var ElementListSHandle = new AJAXHandle(ElementListSProcessResponse, "PerfReqImports");
var InfosSHandle = new AJAXHandle(InfosSProcessResponse, "PerfReqImports");
SearchMenuS.Draw();

function ShowSupplierInfos(id) { InfosSHandle.QueryA('AdvertisersInfos.php?' + __SID__ + '&id=' + id); }
function ElementListSProcessResponse(xhr) { ElementListProcessResponse(ElementListS, xhr); }
function InfosSProcessResponse(xhr) { InfosProcessResponse("infosS", xhr); }

// Common
function ElementListProcessResponse (el, xhr)
{
	el.Clean();
	el.Clear();
	
	var mainsplit = xhr.responseText.split(__MAIN_SEPARATOR__);
	if (mainsplit[0] == '') // Pas d'erreur
	{
		var outputs = mainsplit[1].split(__OUTPUT_SEPARATOR__);
		for (var i = 0; i < outputs.length-1; i++)
		{
			var outputID = outputs[i].split(__OUTPUTID_SEPARATOR__);
			if (outputID.length == 2)
			{
				el.Add(outputID[0],outputID[1]);
			}
		}
		el.Draw();
	}
	else
	{
		document.getElementById(el.id).innerHTML = mainsplit[0];
	}
}

function InfosProcessResponse(id, xhr)
{
	var mainsplit = xhr.responseText.split(__MAIN_SEPARATOR__);
	if (mainsplit[0] == '') // Pas d'erreur
	{
		document.getElementById(id).innerHTML = mainsplit[1];
	}
	else
	{
		document.getElementById("PerfReqImports").innerHTML = mainsplit[0];
	}
	
}

newimport = new HN.Window();
newimport.setID("ImportWindow");
newimport.setTitleText("Nouvelle Importation");
newimport.setMovable(true);
newimport.showCancelButton(true);
newimport.showValidButton(true);
newimport.setValidFct( function() { document.getElementById("ImportWindowForm").submit(); } );
newimport.setShadow(true);
newimport.Build();

it = new HN.JSTable();
it.setID("ImportsTable");
it.setClass("CommonTable");
it.setHeaders(["Réf", "Annonceur", "Date cré.", "Date mod.", "Fichier", function(val, rowh) { rowh.className = val; } ]);
/*it.setHeaders([
	{ "textvalue" : "Réf", "sort" : true, "filter" : false, "type" : "int", "func" : null },
	{ "textvalue" : "Annonceur", "sort" : true, "filter" : false, "type" : "int", "func" : null },
	{ "textvalue" : "Date cré.", "sort" : true, "filter" : false, "type" : "int", "func" : null },
	{ "textvalue" : "Date mod.", "sort" : true, "filter" : false, "type" : "int", "func" : null },
	{ "textvalue" : "Fichier", "sort" : true, "filter" : false, "type" : "int", "func" : null },
	{ "textvalue" : "", "sort" : false, "filter" : false, "type" : "int", "func" : function(val, rowh) { rowh.className = val; } }
]);
*/
it.setInitialData([
<?php
// TODO : Gérer quand l'annonceur n'existe plus
$result = & $handle->query("select i.id, a.nom1, i.create_time, i.timestamp, i.filename, i.status from imports i, advertisers a where i.idAdvertiser = a.id".
        " and type = ". __IMPT_PDT__ , __FILE__, __LINE__, false);
$nbimp = $handle->numrows($result, __FILE__, __LINE__);
for ($i = 0; $i < $nbimp; $i++)
{
	$rec = & $handle->fetchAssoc($result);
	switch ($rec['status'])
	{
		case __I_NVF__ : $statusClass = "status-nvf"; break;
		case __I_NV__ : $statusClass = "status-nv"; break;
		case __I_NF__ : $statusClass = "status-nf"; break;
		case __I_N__ : $statusClass = "status-n"; break;
		case __I_VF__ : $statusClass = "status-vf"; break;
		case __I_V__ : $statusClass = "status-v"; break;
		case __I_F__ : $statusClass = "status-f"; break;
		case __I_0__ : $statusClass = "status-0"; break;
		default : break;
	}
	print '	["' . $rec['id'] . '", "' . $rec['nom1'] . '", "' . date("Y/m/d H:i", $rec['create_time']) . '", "' . date("Y/m/d H:i", $rec['timestamp']) . '", "' . $rec['filename'] . '", "' . $statusClass . '"]' . ($i < ($nbimp-1) ? "," : "")  . "\n";
}
/*
for ($i = 0; $i < 10; $i++)
{
	$time = mktime(mt_rand(0,23),mt_rand(0,59),mt_rand(0,59),4,mt_rand(1,24),2007);
	$date = date("Y/m/d H:i", $time);
	$date2 = date("Y/m/d H:i", $time + mt_rand(0,86400*2));
	
	print '	[' . mt_rand(0,999999999) . ', "QSGQS' . mt_rand(0,9999) . '", "export_' . $i . '", "' . "DUPONT-" . mt_rand(0,9) . '", "' . $date . '", "' . $date2 . '", "' . sprintf("#%X%X%X", mt_rand(200,255), mt_rand(200,255), mt_rand(200,255)) . '"]' . ($i < 9 ? "," : "")  . "\n";
}
*/
?>
]);
it.setColumnCount(5);
it.setMultiPage(true);
it.setRowCount(30);
it.setCurrentPage(1);
it.setRowFct( {
	"onmouseover" : function() { this.style.backgroundColor = "#CCCCCC"; },
	"onmouseout" : function() { this.style.backgroundColor = ""; }
} );
it.setEditTools( {
	"edit" : {"element" : "div", "attributes" : { "onclick" : function() { document.location.href = "import.php?id=" + this.parentNode.parentNode.cc[0].textvalue; } } }
} );
it.Refresh();
//it.PurgeAll();

ps = new HN.PageSwitcher();			ps2 = new HN.PageSwitcher();
ps.setID("PageSwitcher1");			ps2.setID("PageSwitcher2");
ps.setCurrentPage(1);				ps2.setCurrentPage(1);
ps.setLastPage(it.getLastPage());	ps2.setLastPage(it.getLastPage());
ps.setTriggerFct( function(page) { it.setCurrentPage(page); ps2.setCurrentPage(page); it.Refresh(); ps2.Refresh(); } );
ps2.setTriggerFct( function(page) { it.setCurrentPage(page); ps.setCurrentPage(page); it.Refresh(); ps.Refresh(); } );
ps.Refresh();						ps2.Refresh();

</script>
</div>
<?php

require(ADMIN . 'tail.php');

?>
