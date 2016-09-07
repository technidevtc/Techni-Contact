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

$title = 'Exports';
$navBar = 'Liste des exportations';

require(ADMIN . 'head.php');
$lastpage = 100;
$page = 24;
?>
<div class="titreStandard">Exports</div>
<br />

<div class="bg" style="position: relative">
<link href="HN.css" rel="stylesheet" type="text/css"/>
<style type="text/css">
#ExportsTable table { min-width: 1000px; }

#ExportsTable table .column-edit { min-width: 30px; text-align: center; }
#ExportsTable table .column-edit .check { float: left; }
#ExportsTable table .column-edit .edit { float: left; padding: 0 2px; width: 16px; height: 16px; background: url(b_edit.png) 2px 0px no-repeat; }
#ExportsTable table .column-edit .del { float: left; padding: 0 2px; width: 16px; height: 16px; background: url(b_drop.png) 2px 0px no-repeat; }
#ExportsTable table .column-0 { min-width: 150px; text-align: center; }
#ExportsTable table .column-1 { min-width: 130px; text-align: center; }
#ExportsTable table .column-2 { min-width: 130px; text-align: center; }
#ExportsTable table .column-3 { min-width: 80px; text-align: center; }
#ExportsTable table .column-4 { min-width: 80px; text-align: center; }

#ExportWindowShad { z-index: 1; position: absolute; top: -50px; left: 55px; width: 364px; height: 79px; background-color: #000000; visibility: hidden; filter: Alpha (opacity=50, finishopacity=50, style=1) -moz-opacity:.50; opacity:.50; }
#ExportWindow { z-index: 2; position: absolute; top: -55px; left: 50px; width: 360px; height: 75px; border: 2px solid #999999; background-color: #E9EFF8; visibility: hidden; }
#ExportWindowBG { height: 10px; }

#export_partner_label	{ top: 40px; left: 20px; }
#export_partner			{ top: 40px; left: 120px; width: 220px; }

</style>
<script type="text/javascript">
var __SID__ = '<?php echo $sid ?>';
var __MAIN_SEPARATOR__ = '<?php echo __MAIN_SEPARATOR__ ?>';
var __OUTPUT_SEPARATOR__ = '<?php echo __OUTPUT_SEPARATOR__ ?>';
var __OUTPUTID_SEPARATOR__ = '<?php echo __OUTPUTID_SEPARATOR__ ?>';
</script>
<script src="Classes.js" type="text/javascript"></script>
<script src="../js/ManagerFunctions.js" type="text/javascript"></script>
<div id="PerfReqExports" class="PerfReqLabel"></div>
<div id="ProductGetError" class="InfosError">
<?php
if (isset($_GET['error']))
{
	switch($_GET['error'])
	{
		case "file" : print "Erreur Fatale lors du chargement du fichier<br/>"; break;
		case "fileext" : print "Erreur Fatale : type de l'extension du fichier non reconnu<br/>"; break;
		case "advertiser" : print "Erreur Fatale : L'annonceur/fournisseur n'est pas reconnu<br/>"; break;
		case "colstitle" : print "Erreur Fatale : Plusieurs colonnes sont manquantes dans le fichier XLS pour qu'il puisse être reconnu comme valide<br/>"; break;
		default : print "<br/>"; break;
	}
}
else print "<br/>";
?>
</div>

<div class="blocka"><a href="javascript: newexport.Show()">Créer un nouvel export</a></div>
<div id="ExportWindow">
	<div id="ExportWindowBG" class="window_bg">
		<span id="export_partner_label" class="label">Partenaire : </span>
		<select id="export_partner" class="field">
			<option> - </option>
<?php
$result = & $handle->query("select id, name from exports_partner order by name", __FILE__, __LINE__, false);
while ($rec = & $handle->fetch($result))
	print			'<option value="' . $rec[0] . '">' . $rec[1] . '</option>';
?>
		</select>
	</div>
</div>

<div id="PageSwitcher1"></div>
<div class="zero"></div>
<div id="ExportsTable"></div>
<div id="PageSwitcher2"></div>
<div class="zero"></div>
<br />
<script type="text/javascript">
newexport = new HN.Window();
newexport.setID("ExportWindow");
newexport.setTitleText("Nouvel Export");
newexport.setMovable(true);
newexport.showCancelButton(true);
newexport.showValidButton(true);
newexport.setValidFct( function() {
	var epname = document.getElementById("export_partner");
	document.location.href = "export.php?id=new&partner_id=" + epname.options[epname.options.selectedIndex].value;
} );
newexport.setShadow(true);
newexport.Build();

et = new HN.JSTable();
et.setID("ExportsTable");
et.setClass("CommonTable");
et.setHeaders(["ID", "Nom", "Partenaire", "Nb pdt", "Date Création", "Date Génération"]);
et.setInitialData([
<?php
/*
TABLE exports
id
nom
id_partner
nb_pdt
create_time
timestamp
generate_time
id_parent

TABLE partner
id
name
url
flow_type (xml/csv/xls)
compulsory_fields
facultative_fields

TABLE exports_products
id
id_export
compulsory_fields
facultative_fields

[type, source_type, default, filter_type, var1, var2, var3..., varn]

type :
- edit : value
- text : textareae
- list : select with list
- cst  : constant not modifiable

source_type :
- UI : User Input : Free user input
- US : User Select : Selection over a list of value
- DB : From Database : using a table and a field
- DB-tree : recurrent DB sources using a table, a field, and a parent_field which refers to the same table

default : default value while creating the product if source is empty

var1, var2, var3..., varn :
Variables used by the source_type
- UI : none
- US : var1 = val1, var2 = val2..., varn = valn
- DB : var1 = table_name, var2 = field_name, var3 = id_field_name
- DB-tree : var1 = table_name, var2 = field_name, var3 = id_field_name, var4 = parent_id_field_name, var5 = recurrence_number

filter_type = filter to use while reading the data (num, tree, url, no-tag, no-accent...)

Examples :
prix => [ "edit", "DB", "sur demande", "none", "products", "price", "id" ]
marque => [ "edit, "UI", "Techni-contact", "none" ]
disponibilité => [ "list, "UI", "1", "0", "num", "1", "2", "3", "4", "5", "6" ]
categorie => [ "edit", "UI", __FAMILY_TREE_3__, "tree sep=>" ]
URL_produit => [ "edit", "UI", __PRODUCT_URL__, "url" ]
URL_image => [ "edit", "UI", __PRODUCT_IMAGE_URL__, "url" ]

*/
// TODO : Gérer quand l'annonceur n'existe plus
$result = & $handle->query("select e.id, e.name as ename, ep.name as epname, e.nb_pdt, e.create_time, e.generate_time from exports e, exports_partner ep where e.partner_id = ep.id order by e.create_time desc", __FILE__, __LINE__, false);
$nbexp = $handle->numrows($result, __FILE__, __LINE__);
for ($i = 0; $i < $nbexp; $i++)
{
	$rec = & $handle->fetchAssoc($result);
	if ($rec['generate_time'] == 0) $generated = "Non généré";
	else $generated = date("Y/m/d H:i", $rec['generate_time']);
	
	print '	[' . $rec['id'] . ', "' . $rec['ename'] . '", "' . $rec['epname'] . '", ' . $rec['nb_pdt'] . ', "' . date("Y/m/d H:i", $rec['create_time']) . '", "' . $generated . '"]' . ($i < ($nbexp-1) ? "," : "")  . "\n";
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
et.setColumnCount(6);
et.setMultiPage(true);
et.setRowCount(30);
et.setCurrentPage(1);
et.setRowFct( {
	"onmouseover" : function() { this.style.backgroundColor = "#CCCCCC"; },
	"onmouseout" : function() { this.style.backgroundColor = ""; }
} );
et.setEditTools( {
	"edit" : {"element" : "div", "attributes" : { "onclick" : function() { document.location.href = "export.php?id=" + this.parentNode.parentNode.cc[0].textvalue; } } }
} );
et.Refresh();
//it.PurgeAll();

ps = new HN.PageSwitcher();			ps2 = new HN.PageSwitcher();
ps.setID("PageSwitcher1");			ps2.setID("PageSwitcher2");
ps.setCurrentPage(1);				ps2.setCurrentPage(1);
ps.setLastPage(et.getLastPage());	ps2.setLastPage(et.getLastPage());
ps.setTriggerFct( function(page) { et.setCurrentPage(page); ps2.setCurrentPage(page); et.Refresh(); ps2.Refresh(); } );
ps2.setTriggerFct( function(page) { et.setCurrentPage(page); ps.setCurrentPage(page); et.Refresh(); ps.Refresh(); } );
ps.Refresh();						ps2.Refresh();

</script>
</div>
<?php

require(ADMIN . 'tail.php');

?>
