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

$title = $navBar = 'Statistiques';

require(ADMIN . 'head.php');

define("__BEGIN_TIME__", mktime(0,0,0,1,1,2004));


?>
<link rel="stylesheet" type="text/css" href="Stats.css" />
<script type="text/javascript">
var families = new Array();
var name = 0; var ref_name = 1; var idParent = 2; var nbchildren = 3; var children = 4;
var __SID__ = '<?php echo $sid ?>';
var __ADMIN_URL__ = '<?php echo ADMIN_URL ?>';
var __MAIN_SEPARATOR__ = '<?php echo __MAIN_SEPARATOR__ ?>';
var __ERROR_SEPARATOR__ = '<?php echo __ERROR_SEPARATOR__ ?>';
var __ERRORID_SEPARATOR__ = '<?php echo __ERRORID_SEPARATOR__ ?>';
var __OUTPUT_SEPARATOR__ = '<?php echo __OUTPUT_SEPARATOR__ ?>';
var __OUTPUTID_SEPARATOR__ = '<?php echo __OUTPUTID_SEPARATOR__ ?>';
var __DATA_SEPARATOR__ = '<?php echo __DATA_SEPARATOR__ ?>';
</script>
<script src="../js/ManagerFunctions.js" type="text/javascript"></script>
<script src="Classes.js" type="text/javascript"></script>
<!--<script src="families.js" type="text/javascript"></script>-->
<script type="text/javascript">
function showtab(tc, layerID)
{
	for (var t in tc)
	{
		if (t == layerID) document.getElementById(layerID).style.display = "block";
		else document.getElementById(t).style.display = "none";
	}
}

var MenuTabs = new TabList("main_menu", showtab, {"General" : "Général", "Advertisers" : "Annonceurs", "Suppliers" : "Fournisseur", "Products" : "Produit"})

// Tab General
var cur_G_ID = 0;
function ChangeStatsG() { ShowStats('G', cur_G_ID); }
function ShowStatsG(id) { cur_G_ID = id; ChangeStatsG(); }

// Tab Advertisers
var SearchMenuA = new SearchMenu("search_menuA", {"Rechercher un Annonceur " : ShowAdvertisersSearchDB, "0-9" : ShowAdvertisersNumList, "[A-Z]" : ShowAdvertisersLetterList }, "span");
var ElementListA = new ElementList("listA", "li", ShowStatsA); ElementListA.handle = new AJAXHandle(ElementListA, "PerfReqStats");
function ShowAdvertisersSearchDB()
{
	document.getElementById('graphA').innerHTML = "Choisissez un annonceur";
}
function ShowAdvertisersLetterList(letter)
{
	ElementListA.handle.QueryA('AdvertisersSearch.php?' + __SID__ + '&AdvertisersSearchText=' + escape(letter));
	document.getElementById('graphA').innerHTML = "Choisissez un annonceur";
}
function ShowAdvertisersNumList()
{
	ElementListA.handle.QueryA('AdvertisersSearch.php?' + __SID__ + '&AdvertisersSearchText=' + escape('[_0-9]'));
	document.getElementById('graphA').innerHTML = "Choisissez un annonceur";
}
var cur_A_ID = 0;
function ChangeStatsA() { if (cur_A_ID != 0) ShowStats('A', cur_A_ID); }
function ShowStatsA(id) { cur_A_ID = id; ChangeStatsA(); }

// Tab Suppliers
var SearchMenuS = new SearchMenu("search_menuS", {"Rechercher un Fournisseur " : ShowSuppliersSearchDB, "0-9" : ShowSuppliersNumList, "[A-Z]" : ShowSuppliersLetterList }, "span");
var ElementListS = new ElementList("listS", "li", ShowStatsS); ElementListS.handle = new AJAXHandle(ElementListS, "PerfReqStats");
function ShowSuppliersSearchDB()
{
	document.getElementById('graphS').innerHTML = "Choisissez un fournisseur";
}
function ShowSuppliersLetterList(letter)
{
	ElementListS.handle.QueryA('SuppliersSearch.php?' + __SID__ + '&SuppliersSearchText=' + escape(letter));
	document.getElementById('graphS').innerHTML = "Choisissez un fournisseur";
}
function ShowSuppliersNumList()
{
	ElementListS.handle.QueryA('SuppliersSearch.php?' + __SID__ + '&SuppliersSearchText=' + escape('[_0-9]'));
	document.getElementById('graphS').innerHTML = "Choisissez un fournisseur";
}
var cur_S_ID = 0;
function ChangeStatsS() { if (cur_S_ID != 0) ShowStats('S', cur_S_ID); }
function ShowStatsS(id) { cur_S_ID = id; ChangeStatsS(); }

// Tab Products
var SearchMenuP = new SearchMenu("search_menuP", {"Rechercher un Produit " : ShowProductsSearchDB, "0-9" : ShowProductsNumList, "[A-Z]" : ShowProductsLetterList }, "span");
var ElementListP = new ElementList("listP", "li", ShowStatsP); ElementListP.handle = new AJAXHandle(ElementListP, "PerfReqStats");
function ShowProductsSearchDB()
{
	document.getElementById('graphP').innerHTML = "Choisissez un produit";
}
function ShowProductsLetterList(letter)
{
	ElementListP.handle.QueryA('ProductsSearch.php?' + __SID__ + '&ProductsSearchText=' + escape(letter));
	document.getElementById('graphP').innerHTML = "Choisissez un produit";
}
function ShowProductsNumList()
{
	ElementListP.handle.QueryA('ProductsSearch.php?' + __SID__ + '&ProductsSearchText=' + escape('[_0-9]'));
	document.getElementById('graphP').innerHTML = "Choisissez un produit";
}
var cur_P_ID = 0;
function ChangeStatsP() { if (cur_P_ID != 0) ShowStats('P', cur_P_ID); }
function ShowStatsP(id) { cur_P_ID = id; ChangeStatsP(); }

// Common
function ShowStats(type, id)
{
	//document.getElementById('graph'+type).innerHTML = id;
	document.getElementById('graph'+type).innerHTML = '<img src="GetStats.php?' + __SID__ +
	'&Type=' + type +
	'&ID=' + id +
	'&Source=' + document.getElementById('Source'+type).value +
	'&Year=' + document.getElementById('Year'+type).value +
	'&Month=' + document.getElementById('Month'+type).value +
	'&Day=' + document.getElementById('Day'+type).value + '" alt="stats"/>';
}

var MonthLabels = new Array('janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre');
var DayLabes = new Array('Dimanche', 'Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi');

var dateBegin = new Date(); dateBegin.setTime(<?php echo __BEGIN_TIME__ ?>*1000);
var dateCur   = new Date();
//alert(DayLabes[dateCur.getDay()] + " " + dateCur.getDate() + MonthLabels[parseInt(dateCur.getMonth())+1] + dateCur.getFullYear());

FillYearOptions

function FillYearOptions(yID, mID, dID)
{
	var y = document.getElementById(yID);
	yb = parseInt(dateBegin.getFullYear());
	yc = parseInt(dateCur.getFullYear());
	y.options.length = (yc-yb) + 2;
	
	y.options[0].value = 0;
	y.options[0].text  = "Toutes";
	for (var i = 1; i < y.options.length; i++)
	{
		y.options[y.options.length-i].value = yb + i - 1;
		y.options[y.options.length-i].text  = yb + i - 1;
	}
	FillMonthOptions(yID, mID, dID);
}

function FillMonthOptions(yID, mID, dID)
{
	var y = document.getElementById(yID);
	var m = document.getElementById(mID);
	var year = parseInt(y.options[y.options.selectedIndex].value);
	
	if (year == 0) m.options.length = 1;
	else if (year < dateCur.getFullYear()) m.options.length = 13;
	else m.options.length = dateCur.getMonth() + 2;
	
	m.options[0].value = 0;
	m.options[0].text  = "Tous";
	for (var i = 1; i < m.options.length; i++)
	{
		m.options[i].value = i;
		m.options[i].text  = MonthLabels[i-1];
	}
	FillDayOptions(yID, mID, dID);
}

function FillDayOptions(yID, mID, dID)
{
	var y = document.getElementById(yID);
	var m = document.getElementById(mID);
	var d = document.getElementById(dID);
	var year  = parseInt(y.options[y.options.selectedIndex].value);
	var month = parseInt(m.options[m.options.selectedIndex].value);
	
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
	
	d.options[0].value = 0;
	d.options[0].text  = "Tous";
	for (var i = 1; i < d.options.length; i++)
	{
		date.setDate(i);
		d.options[i].value = i;
		d.options[i].text  = DayLabes[date.getDay()] + " " + i;
	}
}
</script>
<style type="text/css">
.bg-stats { padding: 5px; width: 1000px; font: normal 11px Tahoma, Arial, Helvetica, sans-serif; }
.bg-stats a { color: #000000; font-weight: normal; }
.bg-stats a:hover { font-weight: normal; }

.tab_menu { height: 24px; padding: 0 5px 0 5px; position: relative; top: 1px; }

.tab_menu .tab { float: left; width: 118px; text-align: center; cursor: default; }

.tab_menu .tab_lb_i, .tab_menu .tab_lb_a, .tab_menu .tab_lb_s, .tab_menu .tab_rb_i, .tab_menu .tab_rb_a, .tab_menu .tab_rb_s { float: left; width: 4px; height: 23px; }
.tab_menu .tab_lb_i { background : url(tab-left-border.gif) repeat-x; }
.tab_menu .tab_lb_a { background : url(tab-active-left-border.gif) repeat-x; }
.tab_menu .tab_rb_i { background : url(tab-right-border.gif) repeat-x; }
.tab_menu .tab_rb_a { background : url(tab-active-right-border.gif) repeat-x; }
.tab_menu .tab_lb_s { height: 24px; background : url(tab-active-left-border.gif) repeat-x;}
.tab_menu .tab_rb_s { height: 24px; background : url(tab-active-right-border.gif) repeat-x; }

.tab_menu .tab_bg_i, .tab_menu .tab_bg_a, .tab_menu .tab_bg_s { height: 17px; float: left; width: 90px; text-align: left; color: #000000; padding: 6px 10px 0px 10px; white-space: nowrap; }
.tab_menu .tab_bg_i { background: url(tab-bg.gif) repeat-x; }
.tab_menu .tab_bg_a { background: url(tab-active-bg.gif) repeat-x; }
.tab_menu .tab_bg_s { height: 18px; background: url(tab-active-bg.gif) repeat-x; font-weight: bold; }

.menu-below { border: 1px solid #808080; height: 2px; font-size: 0; border-bottom: none; background-color: #D8D4CD; }
.main { border: 1px solid #808080; background-color: #DEDCD6; }

.search_menu { cursor: default; padding: 3px 6px; border-bottom: 1px solid #808080; display: block; float: left}
.search_menu span { border: 1px solid #DEDCD6; padding: 2px 5px; outline: none; }
.search_menu span.over { border-color: #FFFFFF #808080 #808080 #FFFFFF; }
.search_menu span.down { border-color: #808080 #FFFFFF #FFFFFF #808080; }
.search_menu span.selected { border-color: #808080 #FFFFFF #FFFFFF #808080; }

.window { padding: 2px 4px; background-color: #DEDCD6; border-top: 1px solid #FFFFFF; clear: left}
.window .colg { float: left; width: 150px; margin-right: 5px; }
.window .colc { float: left; width: 835px; }
.window .colu { float: left; width: 990px; }
.window .col-title { font-weight: bold; margin: 2px; }
.window .colg .list { width: 144px; height: 498px; background-color: #FFFFFF; border: 2px inset #808080; margin: 0; padding: 1px; list-style-type: none; overflow: auto; }
.window .colg .list li { cursor: default; white-space: nowrap; }
.window .colg .list li.over { background-color: #316AC5; color: #FFFFFF; }
.window .colg .list li.selected { background-color: #0C266C; color: #FFFFFF; }

/*.window .colg .list a:hover { background-color: #316AC5; color: #FFFFFF; }*/
.window .colc .stats { height: 490px; background-color: #FFFFFF; border: 2px inset #808080; padding: 5px; }
.window .colu .stats { height: 510px; background-color: #FFFFFF; border: 2px inset #808080; padding: 5px; }

.window .colu .select_label { padding: 0 5px 0 20px; }
.window .colc .select_label { padding: 0 5px 0 20px; }

</style>
<div class="titreStandard">Statistiques (consulter également <a href="http://www.xiti.com/fr/Login.aspx" target="_blank">les statistiques XiTi</a>)</div>
<br />
<div class="bg-stats">
	<div id="PerfReqStats"><br /></div>
	<div id="main_menu" class="tab_menu"></div>
	<div class="menu-below"></div>
	<div class="main" id="main_tabs">
		<div id="General">
			<div class="window">
				<div class="colu">
					<div class="col-title">Statistiques</div>
					<div class="stats" id="statsG">
						<span class="select_label">Source :</span>
						<select id="SourceG" onchange="ChangeStatsG()">
							<option value="0">pages vues</option>
							<option value="1">ajouts aux paniers</option>
							<option value="2">nb compte client créés (lead)</option>
							<option value="3">nb compte client créés (commande)</option>
							<option value="4">nb devis générés</option>
							<option value="5">nb commandes</option>
							<option value="6">nb devis -> commande</option>
							<option value="7">marge totale</option>
							<option value="8">CA</option>
						</select>
						<span class="select_label">Année :</span>
						<select id="YearG" onchange="FillMonthOptions(this.id, 'MonthG', 'DayG'); ChangeStatsG()">
						</select>
						<span class="select_label">Mois :</span>
						<select id="MonthG" onchange="FillDayOptions('YearG', this.id, 'DayG'); ChangeStatsG()">
						</select>
						<span class="select_label">Jour :</span>
						<select id="DayG" onchange="ChangeStatsG()">
						</select>
						<script type="text/javascript">
						FillYearOptions('YearG', 'MonthG', 'DayG');
						</script>
						<br />
						<br />
						<div id="graphG"></div>
					</div>
				</div>
				<div class="zero"></div>
			</div>
		</div>
		<div id="Advertisers">
			<div id="search_menuA" class="search_menu">
				<script type="text/javascript">
				SearchMenuA.Draw();
				</script>
			</div>
			<div class="window">
				<div class="colg">
					<div class="col-title">Liste des annonceurs</div>
					<ul class="list" id="listA">
					</ul>
				</div>
				<div class="colc">
					<div class="col-title">Statistiques</div>
					<div class="stats" id="statsA">
						<span class="select_label">Source :</span>
						<select id="SourceA" onchange="ChangeStatsA()">
							<option value="0">pages vues</option>
							<option value="1">nb contact total</option>
							<option value="2">nb contact à comptabiliser</option>
						</select>
						<span class="select_label">Année :</span>
						<select id="YearA" onchange="FillMonthOptions(this.id, 'MonthA', 'DayA'); ChangeStatsA()">
						</select>
						<span class="select_label">Mois :</span>
						<select id="MonthA" onchange="FillDayOptions('YearA', this.id, 'DayA'); ChangeStatsA()">
						</select>
						<span class="select_label">Jour :</span>
						<select id="DayA" onchange="ChangeStatsA()">
						</select>
						<script type="text/javascript">
						FillYearOptions('YearA', 'MonthA', 'DayA');
						</script>
						<br />
						<br />
						<div id="graphA">Choisissez un annonceur</div>
					</div>
				</div>
				<div class="zero"></div>
			</div>
		</div>
		<div id="Suppliers">
			<div id="search_menuS" class="search_menu">
				<script type="text/javascript">
				SearchMenuS.Draw();
				</script>
			</div>
			<div class="window">
				<div class="colg">
					<div class="col-title">Liste des fournisseurs</div>
					<ul class="list" id="listS">
					</ul>
				</div>
				<div class="colc">
					<div class="col-title">Statistiques</div>
					<div class="stats" id="statsS">
						<span class="select_label">Source :</span>
						<select id="SourceS" onchange="ChangeStatsS()">
							<option value="0">pages vues</option>
							<option value="1">ajouts aux paniers</option>
							<option value="2">nb devis générés</option>
							<option value="3">nb commandes</option>
							<option value="4">nb devis -> commande</option>
							<option value="5">marge totale</option>
							<option value="6">CA</option>
							<option value="7">nb contact total</option>
							<option value="8">nb contact à comptabiliser</option>
						</select>
						<span class="select_label">Année :</span>
						<select id="YearS" onchange="FillMonthOptions(this.id, 'MonthS', 'DayS'); ChangeStatsS()">
						</select>
						<span class="select_label">Mois :</span>
						<select id="MonthS" onchange="FillDayOptions('YearS', this.id, 'DayS'); ChangeStatsS()">
						</select>
						<span class="select_label">Jour :</span>
						<select id="DayS" onchange="ChangeStatsS()">
						</select>
						<script type="text/javascript">
						FillYearOptions('YearS', 'MonthS', 'DayS');
						</script>
						<br />
						<br />
						<div id="graphS">Choisissez un fournisseur</div>
					</div>
				</div>
				<div class="zero"></div>
			</div>
		</div>
		<div id="Products">
			<div id="search_menuP" class="search_menu">
				<script type="text/javascript">
				SearchMenuP.Draw();
				</script>
			</div>
			<div class="window">
				<div class="colg">
					<div class="col-title">Liste des Produits</div>
					<ul class="list" id="listP">
					</ul>
				</div>
				<div class="colc">
					<div class="col-title">Statistiques</div>
					<div class="stats" id="statsP">
						<span class="select_label">Source :</span>
						<select id="SourceP" onchange="ChangeStatsP()">
							<option value="0">pages vues</option>
							<option value="1">ajouts aux paniers</option>
							<option value="2">nb devis générés</option>
							<option value="3">nb commandes</option>
							<option value="4">nb devis -> commande</option>
							<option value="5">marge totale</option>
							<option value="6">CA</option>
						</select>
						<span class="select_label">Année :</span>
						<select id="YearP" onchange="FillMonthOptions(this.id, 'MonthP', 'DayP'); ChangeStatsP()">
						</select>
						<span class="select_label">Mois :</span>
						<select id="MonthP" onchange="FillDayOptions('YearP', this.id, 'DayP'); ChangeStatsP()">
						</select>
						<span class="select_label">Jour :</span>
						<select id="DayP" onchange="ChangeStatsP()">
						</select>
						<script type="text/javascript">
						FillYearOptions('YearP', 'MonthP', 'DayP');
						</script>
						<br />
						<br />
						<div id="graphP">Choisissez un produit</div>
					</div>
				</div>
				<div class="zero"></div>
			</div>
		</div>
	</div>
	<script type="text/javascript">
	MenuTabs.Draw();
	MenuTabs.tc["General"].onclick();
	
	document.getElementById('YearG').value = dateCur.getFullYear();
	FillMonthOptions('YearG', 'MonthG', 'DayG');
	document.getElementById('MonthG').value = dateCur.getMonth()+1;
	FillDayOptions('YearG', 'MonthG', 'DayG');
	ChangeStatsG();
	</script>
</div>

<?php

require(ADMIN . 'tail.php');

?>
