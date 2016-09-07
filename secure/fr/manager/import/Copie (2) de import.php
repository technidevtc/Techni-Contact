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

require('../config.php');

$title = $navBar = 'Imports';

require(ADMIN . 'head.php');

if (!isset($_GET['id']) || ($ImportID = (int)($_GET['id'])) <= 0)
{
?>
<div class="titreStandard">Import</div>
<br />
<div class="bg" id="bg_parent">
	Cet import n'existe pas.
</div>
<?php
	exit();
}
?>
<div class="titreStandard">Import</div>
<br />
<div class="bg" id="bg_parent">
<link href="HN.css" rel="stylesheet" type="text/css"/>
<style type="text/css">
#ProductsTable table { min-width: 1000px; }
#ProductsTable table tr.not-valid { background-color: #FFD0D0; }
#ProductsTable table tr.valid { background-color: #D0FFD0; }
#ProductsTable table tr.finalized { background-color: #D0D0FF; }

#ProductsTable table .column-edit { width: 70px; text-align: center; }
#ProductsTable table .column-edit .check { float: left; }
#ProductsTable table .column-edit .edit { float: left; padding: 0 2px; width: 16px; height: 16px; background: url(b_edit.png) 2px 0px no-repeat; }
#ProductsTable table .column-edit .del { float: left; padding: 0 2px; width: 16px; height: 16px; background: url(b_drop.png) 2px 0px no-repeat; }
#ProductsTable table .column-0 { display: none; }
#ProductsTable table .column-1 { display: none; }
#ProductsTable table .column-2 { width: 115px; text-align: center; }
#ProductsTable table .column-3 { width: 115px; text-align: center; }
#ProductsTable table .column-4 { width: 125px; text-align: center; }
#ProductsTable table .column-5 { width: 175px; text-align: center; }
#ProductsTable table .column-6 { width: 175px; text-align: center; }
#ProductsTable table .column-7 { width: 75px; text-align: center; }
#ProductsTable table .column-8 { width: 100px; text-align: center; }
#ProductsTable table .column-9 { width: 50px; text-align: center; }
#ProductsTable .page-change { float: right; width: 200px; }

.label { position: absolute; }
.field { position: absolute; }
.button { position: absolute; }
.description { position: absolute; font-size: 11px; font-style: italic; white-space: nowrap; overflow: hidden; }
.zero { clear: both; }

#EditProductWindowShad { z-index: 1; position: absolute; top: 35px; left: 55px; width: 904px; height: 504px; background-color: #000000; visibility: hidden; filter: Alpha (opacity=50, finishopacity=50, style=1) -moz-opacity:.50; opacity:.50; }
#EditProductWindow { z-index: 2; position: absolute; top: 30px; left: 50px; width: 900px; height: 500px; border: 2px solid #999999; background-color: #E9EFF8; visibility: hidden; }
#EditProductWindowBG { position: relative; width: 858px; height: 435px; }

#label_name				{ top: 10px; left: 10px; }
#name					{ top: 10px; left: 160px; width: 200px; height: 15px; }
#label_fastdesc			{ top: 32px; left: 10px; }
#fastdesc				{ top: 32px; left: 160px; width: 200px; height: 15px; }
#label_descc			{ top: 54px; left: 10px; }
#descc					{ top: 56px; left: 160px; width: 130px; }
#button_descc			{ top: 54px; left: 303px; width: 60px; }
#label_descd			{ top: 76px; left: 10px; }
#descd					{ top: 78px; left: 160px; width: 130px; }
#button_descd			{ top: 76px; left: 303px; width: 60px; }

#button_valid			{ top: 5px; left: 700px; width: 80px; }
#button_cancel			{ top: 5px; left: 790px; width: 80px; }
#label_family_name		{ top: 32px; left: 420px; }
#family_name			{ top: 34px; left: 570px; width: 130px; }
#button_family_name		{ top: 32px; left: 700px; width: 73px; }
#label_url_image		{ top: 54px; left: 420px; }
#url_image				{ top: 54px; left: 570px; width: 200px; height: 15px; }
#label_delivery_time	{ top: 76px; left: 420px; }
#delivery_time			{ top: 76px; left: 570px; width: 200px; height: 15px; }

#ReferencesSection { position: absolute; top: 108px; left: 10px; width: 858px; }

#ReferencesTable table { min-width: 858px; }
#ReferencesTable table .column-edit { width: 20px; text-align: center; }
#ReferencesTable table .column-edit .check { float: left; }
#ReferencesTable table .column-edit .edit { float: left; padding: 0 2px; width: 16px; height: 16px; background: url(b_edit.png) 2px 0px no-repeat; }
#ReferencesTable table .column-edit .del { float: left; padding: 0 2px; width: 16px; height: 16px; background: url(b_drop.png) 2px 0px no-repeat; }

#ReferencesTable table th { text-align: center }
#ReferencesTable table .column-0 { display: none; }
/*
#ReferencesTable .page-change { float: right; width: 200px; }
*/

#MultiFamilySearchWindowShad { z-index: 3; position: absolute; top: 65px; left: 80px; width: 788px; height: 404px; background-color: #000000; visibility: hidden; filter: Alpha (opacity=50, finishopacity=50, style=1) -moz-opacity:.50; opacity:.50; }
#MultiFamilySearchWindow { z-index: 4; position: absolute; top: 60px; left: 75px; width: 784px; height: 400px; border: 2px solid #999999; background-color: #E9EFF8; visibility: hidden; }

#DescriptionWindowShad { z-index: 3; position: absolute; top: 65px; left: 80px; width: 754px; height: 404px; background-color: #000000; visibility: hidden; filter: Alpha (opacity=50, finishopacity=50, style=1) -moz-opacity:.50; opacity:.50; }
#DescriptionWindow { z-index: 4; position: absolute; top: 60px; left: 75px; width: 750px; height: 400px; border: 2px solid #999999; background-color: #E9EFF8; visibility: hidden; }

#DetailedDescriptionWindowShad { z-index: 3; position: absolute; top: 95px; left: 80px; width: 754px; height: 404px; background-color: #000000; visibility: hidden; filter: Alpha (opacity=50, finishopacity=50, style=1) -moz-opacity:.50; opacity:.50; }
#DetailedDescriptionWindow { z-index: 4; position: absolute; top: 90px; left: 75px; width: 750px; height: 400px; border: 2px solid #999999; background-color: #E9EFF8; visibility: hidden; }

/*.field, .fieldfl, .fieldfr { padding: 2px; height: 20px; }*/
.fieldfl { float: left; }
.fieldfr { float: right; }
/*.field input, .fieldfl input, .fieldfr input { border: 1px solid #C6D6D8; }*/


#FamilySearchWindowShad { z-index: 3; position: absolute; top: 65px; left: 80px; width: 788px; height: 404px; background-color: #000000; visibility: hidden; filter: Alpha (opacity=50, finishopacity=50, style=1) -moz-opacity:.50; opacity:.50; }
#FamilySearchWindow { z-index: 4; position: absolute; top: 60px; left: 75px; width: 800px; height: 400px; border: 2px solid #999999; background-color: #FCFCFF; visibility: hidden; }

#FamilySearchWindowBG { height: 377px; overflow: scroll; }
#FamilySearchWindowBG #menu { width: 780px; text-align: center; background: #A00100; font: 13px Arial, Helvetica, sans-serif; color: white; padding: 6px 2px; margin: 4px 0; }
#FamilySearchWindowBG #menu a { font-weight: normal; background: #cd2d2c; color: white; text-decoration: none; padding: 2px 3px; }
#FamilySearchWindowBG #menu a:hover { background: #FFFFFF; color: #A00100; }
#FamilySearchWindowBG #menu a.current { background: #A00100; color: #FFFFFF; outline: none; }
#FamilySearchWindowBG #menu a.current:hover { text-decoration: underline; }

#FamilySearchWindowBG #colg { float: left; width: 190px; text-align: left; padding-bottom: 2px; background-color: #FFFFFF; }
#FamilySearchWindowBG #colg .titre { text-align: center; display: block; background: #637382; font: bold 12px Arial, Helvetica, sans-serif; letter-spacing: 1px; text-transform: uppercase; color: white; padding: 5px 13px; }
#FamilySearchWindowBG #colg .sf { padding-top: 3px; }
#FamilySearchWindowBG #colg .sf a { display: block; color: #3d4b58; text-decoration: none; font: 11px Arial, Helvetica, sans-serif; letter-spacing: 1px; font-weight: bold; padding: 3px 0 5px 20px; background: url(../ressources/flecheUn.gif) no-repeat left bottom; }
#FamilySearchWindowBG #colg .sf a:hover { text-decoration: underline; }
#FamilySearchWindowBG #colg .sf a.currentFolded { background-color: #FFDD82; }
#FamilySearchWindowBG #colg .sf a.currentUnfolded { background: #FFDD82 url(../ressources/flecheDeux.gif) no-repeat left bottom; }
#FamilySearchWindowBG #colg .sf a.notCurrentUnfolded { background: url(../ressources/flecheDeux.gif) no-repeat left bottom; }

#FamilySearchWindowBG #colc { float: left; width: 584px; text-align: left; background-color: #FFFFFF; padding: 0 4px 0 0; }
#FamilySearchWindowBG #colc .ssf { background: url(../ressources/flecheTrois.gif) no-repeat left bottom; padding-bottom: 3px; }
#FamilySearchWindowBG #colc .ssf a { float: left; width: 272px; display: block; color: #3D4B58; text-decoration: none; font: 11px Arial, Helvetica, sans-serif; letter-spacing: 1px; padding: 0 0 0 10px; margin: 2px 0 1px 8px; border-left: solid 2px #889c48; background: #f6f6f6; }
#FamilySearchWindowBG #colc .ssf a.current { background-color: #C6D6D8; padding: 0 0 0 10px; }
#FamilySearchWindowBG #colc .ssf a:hover { background-color: #C6D6D8; text-decoration: none; }
#FamilySearchWindowBG #colc .ssf a.current:hover { background-color: #889C48; color: #FFFFFF; }
#FamilySearchWindowBG #colc h1 { font: bold 12px Arial, Helvetica, sans-serif; letter-spacing: 1px; color: #272727; text-decoration: none; padding: 8px 2px; border-bottom: solid 3px #c6d6d8; margin: 0; }

</style>
<script type="text/javascript">
var __SID__ = '<?=$sid?>';
var __ADMIN_URL__ = '<?=ADMIN_URL?>';
var __MAIN_SEPARATOR__ = '<?=__MAIN_SEPARATOR__?>';
var __ERROR_SEPARATOR__ = '<?=__ERROR_SEPARATOR__?>';
var __ERRORID_SEPARATOR__ = '<?=__ERRORID_SEPARATOR__?>';
var __OUTPUT_SEPARATOR__ = '<?=__OUTPUT_SEPARATOR__?>';
var __OUTPUTID_SEPARATOR__ = '<?=__OUTPUTID_SEPARATOR__?>';
var __DATA_SEPARATOR__ = '<?=__DATA_SEPARATOR__?>';
var __IP_NOT_VALID__ = '<?=__IP_NOT_VALID__?>';
var __IP_VALID__ = '<?=__IP_VALID__?>';
var __IP_FINALIZED__ = '<?=__IP_FINALIZED__?>';

// FAMILIES //
<?php
$families = array();
$families[0]['name'] = '';
$families[0]['ref_name'] = '';
$families[0]['idParent'] = 0;

$result = & $handle->query("select f.id, fr.name, fr.ref_name, f.idParent from families f, families_fr fr where f.id = fr.id", __FILE__, __LINE__);
while ($family = & $handle->fetchAssoc($result))
{
	$families[$family['id']]['name'] = $family['name'];
	$families[$family['id']]['ref_name'] = $family['ref_name'];
	$families[$family['id']]['idParent'] = $family['idParent'];
	if (!isset($families[$family['idParent']]['nbchildren']))
		$families[$family['idParent']]['nbchildren'] = 1;
	else
		$families[$family['idParent']]['nbchildren']++;
	$families[$family['idParent']]['children'][$families[$family['idParent']]['nbchildren']-1] = $family['id'];
}

$menu_families = '';
foreach ($families[0]['children'] as $id)
	$menu_families .= '<a href="familles/' . $id .'">' . htmlentities($families[$id]['name']) . "</a> ";

?>
var families = new Array();
var name = 0; var ref_name = 1; var idParent = 2; var nbchildren = 3; var children = 4;

function fam_sort_ref_name(a, b)
{
	if (families[a][ref_name] > families[b][ref_name]) return 1;
	if (families[a][ref_name] < families[b][ref_name]) return -1;
	return 0;
}

<?php
foreach ($families as $id => $fam)
{
	print 'families[' . $id . '] = ["' . str_replace('"', '\"', $fam['name']) . '", "' . $fam['ref_name'] . '", ' . $fam['idParent'] . ', ';
	if (isset($fam['nbchildren']))
	{
		print $fam['nbchildren'] . ', [' . $fam['children'][0];
		for ($i = 1; $i < $fam['nbchildren']; $i++)
			print ", " . $fam['children'][$i];
		print "]";
	}
	else
	{
		print "0, []";
	}
	print  "];\n";
}
?>
</script>
<script src="Classes.js" type="text/javascript"></script>
<script src="../js/ManagerFunctions.js" type="text/javascript"></script>
<script src="../editor/fckeditor.js" type="text/javascript"></script>
<div id="PerfReqImports"></div>
<div id="DescriptionWindow">
	<script type="text/javascript">
	var sBasePath = '<?=ADMIN_URL?>editor/';
	var oFCKeditor = new FCKeditor('FCKdescc');
		oFCKeditor.BasePath	= sBasePath;
		oFCKeditor.Height = 377;
		oFCKeditor.Width  = 750;
		oFCKeditor.Config['CustomConfigurationsPath'] = '<?=ADMIN_URL?>files/myconfig.js';
		oFCKeditor.Value = '<?php print(str_replace(array("'", chr(13) . chr(10)), array("\'", "\\\n"), $desc)); ?>';
		oFCKeditor.Create();
	</script>
	<input type="button" value="Valider" onclick="dw.Hide();"/>
</div>
<div id="DetailedDescriptionWindow">
	<script type="text/javascript">
	var pFCKeditor = new FCKeditor('FCKdescd');
		pFCKeditor.BasePath	= sBasePath;
		pFCKeditor.Height = 377;
		pFCKeditor.Width  = 750;
		pFCKeditor.Config['CustomConfigurationsPath'] = '<?=ADMIN_URL?>files/myconfig.js';
		pFCKeditor.Value = '<?php print(str_replace(array("'", chr(13) . chr(10)), array("\'", "\\\n"), $descd)); ?>';
		pFCKeditor.Create();
	</script>
	<input type="button" value="Valider" onclick="ddw.Hide();"/>
</div>
<div id="EditProductWindow">
	<div id="EditProductWindowBG" class="window_bg">
		<input type="hidden" id="id"/>
		<input type="hidden" id="ref_count"/>
		<input type="hidden" id="idFamily"/>
		<span id="label_name" class="label">Nom (mot clé google) : </span><input id="name" type="text" maxlength="255" class="field"/>
		<span id="label_fastdesc" class="label">Description rapide : </span><input id="fastdesc" type="text" maxlength="255" class="field"/>
		<span id="label_descc" class="label">Description : </span><div id="descc" class="description">qmsfdgbm qsibgdm igbm qiebgmq izebmgisdbm gqsigbmzqiebgm qibgm sd</div><input id="button_descc" type="button" class="button" value="Editer" onclick="dw.Show();"/><br />
		<span id="label_descd" class="label">Description détaillée : </span><div id="descd" class="description"></div><input id="button_descd" type="button" class="button" value="Editer" onclick="ddw.Show();"/><br />
		<span id="label_family_name" class="label">Famille : </span><div id="family_name" class="description"></div><input id="button_family_name" type="button" class="button" value="Changer" onclick="ShowProductFamily();"/>
		<span id="label_url_image" class="label">image : </span><input id="url_image" type="text" maxlength="255" class="field"/>
		<span id="label_delivery_time" class="label">Délai de livraison : </span><input id="delivery_time" type="text" maxlength="255" class="field"/>
		<div id="ReferencesSection">
			<div id="ReferencesTableSwitcher1"></div>
			<div class="zero"></div>
			<div id="ReferencesTable"></div>
			<div id="ReferencesTableSwitcher2"></div>
		</div>
	</div>
</div>
<div id="FamilySearchWindow">
	<div id="FamilySearchWindowBG">
		<div id="menu"></div>
		<div id="colg">
			<div class="titre" id="colg_titre">Familles</div>
			<div class="sf" id="colg_sf"></div>
		</div>
		<div id="colc" style="">
			<h1 id="desc">Choisissez une famille</h1>
			<div class="ssf" id="colc_ssf"></div>
		</div>
	</div>
</div>
<div id="PerfReqLabelProducts"><br /></div>
<div id="ProductGetError"></div>
<div id="ProductsTableSwitcher1"></div>
<div class="zero"></div>
<div id="ProductsTable"></div>
<div id="ProductsTableSwitcher2"></div>
<div class="zero"></div>
<div>
</div>
<input type="button" value="show me the money" onclick="showsel();"/>
<br />
<script type="text/javascript">
// FAMILIES //
function showsel()
{
	var rs = pt.getSelectedRows();
	var s = "";
	for (var rowNum in rs) s += rowNum + " " + rs[rowNum].cc[2].textvalue + "\n";
	alert(s);
}
function ShowProductFamily ()
{
	var fid = parseInt(document.getElementById("idFamily").value);
	BuildFamilyWindow();
	selectFamByID(fid);
	fsw.Show();
}

function selectFamByID (fid)
{
	var fidr = fid;
	var famTree = [];
	var n = 0;
	while (fidr != 0)
	{
		famTree[n++] = fidr;
		fidr = families[fidr][idParent];
	}
	famTree.reverse();
	for (var i = 0; i < n; i++)
	{
		switch (i)
		{
			case 0:
				var menu = document.getElementById('menu');
				menu.children[famTree[0]].onclick();
				break;

			case 1:
				var colg_sf = document.getElementById('colg_sf');
				colg_sf.children[famTree[1]].onclick();
				break;
				
			case 2:
				var colc_ssf = document.getElementById('colc_ssf');
				colc_ssf.children[famTree[2]].onclick();
				break;
			
			default : break;
		}
	}
}

var cur_family_id = 0;
function BuildFamilyWindow ()
{
	var menu = document.getElementById('menu');
	var colg_sf = document.getElementById('colg_sf');
	var colc_ssf = document.getElementById('colc_ssf');
	purge(menu);
	purge(colg_sf);
	purge(colc_ssf);
	for (var node = menu.childNodes.length-1; node >= 0; node--) menu.removeChild(menu.childNodes[node]);
	for (var node = colg_sf.childNodes.length-1; node >= 0; node--) colg_sf.removeChild(colg_sf.childNodes[node]);
	for (var node = colc_ssf.childNodes.length-1; node >= 0; node--) colc_ssf.removeChild(colc_ssf.childNodes[node]);
	menu.current_f = null;
	colg_sf.current_sf = null;
	colc_ssf.current_ssf = null;
	menu.children = [];
	colg_sf.children = [];
	colc_ssf.children = [];
	
	for (var i = 0; i < families[0][nbchildren]; i++)
	{
		var a = document.createElement("a");
		a.href = "#";
		a.family_id = families[0][children][i];
		a.appendChild(document.createTextNode(families[a.family_id][name]));
		menu.children[a.family_id] = a;
		menu.appendChild(a);
		menu.appendChild(document.createTextNode(" "));

		a.Select = function () { // lors de la sélection
			if (this.parentNode.current_f && this.parentNode.current_f != this) this.parentNode.current_f.UnSelect();
			this.parentNode.current_f = this;
			this.className = 'current';
			
			document.getElementById('colg_titre').innerHTML = families[this.family_id][name];
			families[this.family_id][children].sort(fam_sort_ref_name); // Tri par nom référence pour affichage
			
			var colg_sf = document.getElementById('colg_sf');
			var colc_ssf = document.getElementById('colc_ssf');
			purge(colg_sf);
			purge(colc_ssf);
			for (var node = colg_sf.childNodes.length-1; node >= 0; node--) colg_sf.removeChild(colg_sf.childNodes[node]);
			for (var node = colc_ssf.childNodes.length-1; node >= 0; node--) colc_ssf.removeChild(colc_ssf.childNodes[node]);
			colg_sf.current_sf = null;
			colc_ssf.current_ssf = null;
			colg_sf.children = [];
			colc_ssf.children = [];
			
			for (var j = 0; j < families[this.family_id][nbchildren]; j++)
			{
				var a2 = document.createElement("a");
				a2.href = "#";
				a2.family_id = families[this.family_id][children][j];
				a2.appendChild(document.createTextNode(families[a2.family_id][name]));
				colg_sf.children[a2.family_id] = a2;
				colg_sf.appendChild(a2);
				
				a2.Select = function () {
					if (this.parentNode.current_sf && this.parentNode.current_sf != this) this.parentNode.current_sf.UnSelect();
					this.parentNode.current_sf = this;
					this.className = 'currentUnfolded';
					
					families[this.family_id][children].sort(fam_sort_ref_name); // Tri par nom référence pour affichage
					
					var colc_ssf = document.getElementById('colc_ssf');
					purge(colc_ssf);
					for (var node = colc_ssf.childNodes.length-1; node >= 0; node--) colc_ssf.removeChild(colc_ssf.childNodes[node]);
					colc_ssf.current_ssf = null;
					colc_ssf.children = [];
					for (var k = 0; k < families[this.family_id][nbchildren]; k++)
					{
						var a3 = document.createElement("a");
						a3.href = "#";
						a3.family_id = families[this.family_id][children][k];
						a3.appendChild(document.createTextNode(families[a3.family_id][name]));
						colc_ssf.children[a3.family_id] = a3;
						colc_ssf.appendChild(a3);
						
						a3.onclick = function () {
							cur_family_id = this.family_id;
							if (this.parentNode.current_ssf && this.parentNode.current_ssf != this) this.parentNode.current_ssf.className = '';
							this.parentNode.current_ssf = this;
							this.className = 'current';
							
							document.getElementById('desc').innerHTML = "Famille " + families[this.family_id][name];
							
							return false;
						}
					}
					
					return false;
				}
				
				a2.UnSelect = function () { // fonction pour cacher la ssf courante
					this.className = '';
				}
				
				a2.onclick = a2.Select;
			}
			
			return false;
		}
		
		a.UnSelect = function () { // lors de la déselection
			this.className = '';
		}
		
		a.onclick = a.Select;
		aa = a.cloneNode(true);
		aa.apointer = a;
		aa.onclick = function() { this.apointer.onclick(); }
		document.getElementById('colg_sf').appendChild(aa);
	}
}

var FCKInstancesloaded = 0;
var FCKdesccInstance = null;
var FCKdescdInstance = null;
var RowEdited = {};

function FCKeditor_OnComplete( editorInstance ) {
	switch (editorInstance.Name)
	{
		case "FCKdescc" : FCKdesccInstance = editorInstance; break;
		case "FCKdescd" : FCKdescdInstance = editorInstance; break;
	}
	FCKInstancesloaded++;
}

var ProductsAJAXHandle = new AJAXHandle(ProductResponse, "PerfReqLabelProducts");

function ShowProduct(rowNum) {
	if (FCKInstancesloaded != 2) return false
	RowEdited.rowNum = rowNum;
	RowEdited.data = [];
	ProductsAJAXHandle.QueryA("ProductsManagment.php?action=get&id=" + parseInt(pt.getCell(rowNum,0).textvalue));
}

function AlterProduct() {
	if (FCKInstancesloaded != 2) return false
	RowEdited.data.push(document.getElementById("id").value);
	var fid = parseInt(document.getElementById("idFamily").value);
	RowEdited.data.push(fid);
	RowEdited.data.push(document.getElementById("name").value);
	RowEdited.data.push(families[fid][name]);
	RowEdited.data.push(document.getElementById("fastdesc").value);
	RowEdited.data.push(FCKdesccInstance.GetHTML().replace(/(<br>)|(<br\/>)/gi, " ").substr(0,30) + "...");
	RowEdited.data.push(FCKdescdInstance.GetHTML().replace(/(<br>)|(<br\/>)/gi, " ").substr(0,30) + "...");
	RowEdited.data.push(document.getElementById("delivery_time").value);
	RowEdited.data.push(document.getElementById("url_image").value);
	var ref_count = parseInt(document.getElementById("ref_count").value);
	RowEdited.data.push(ref_count);
	
	var as = "action=alter"; var col = 0;
	as += "&id=" + RowEdited.data[col++];
	as += "&idFamily=" + escape(RowEdited.data[col++]);
	as += "&name=" + escape(RowEdited.data[col++]); col++;
	as += "&fastdesc=" + escape(RowEdited.data[col++]);
	as += "&descc=" + escape(FCKdesccInstance.GetHTML()); col++;
	as += "&descd=" + escape(FCKdescdInstance.GetHTML()); col++;
	as += "&delivery_time=" + escape(RowEdited.data[col++]);
	as += "&url_image=" + escape(RowEdited.data[col++]);
	as += "&ref_count=" + RowEdited.data[col++];
	
	headers = rt.getHeaders();
	var i = 3;
	while (headers[i] != "Unité") as += "&mixed_data_entitle_" + (i-3) + "=" + escape(headers[i++]);
	var nb_mixed_header = i-3;
	
	for (i = 0; i < ref_count; i++)
	{
		col = 0;
		as += "&id_ref_" + i + "=" + rt.getCell(i,col++).textvalue;
		as += "&ref_supplier_" + i + "=" + escape(rt.getCell(i,col++).textvalue);
		as += "&label_" + i + "=" + escape(rt.getCell(i,col++).textvalue);
		for (var j = 0; j < nb_mixed_header; j++)
			as += "&mixed_data_" + i + "_" + j + "=" + escape(rt.getCell(i,col++).textvalue);
		as += "&unit_" + i + "=" + rt.getCell(i,col++).textvalue;
		as += "&VAT_" + i + "=" + rt.getCell(i,col++).textvalue;
		as += "&price_" + i + "=" + rt.getCell(i,col++).textvalue;
		as += "&marge_" + i + "=" + rt.getCell(i,col++).textvalue;
		as += "&price2_" + i + "=" + rt.getCell(i,col++).textvalue;
		as += "&order_" + i + "=" + rt.getCell(i,col++).textvalue;
	}
	
	//alert(as.replace(/&/g, "\n"));
	ProductsAJAXHandle.QueryA("ProductsManagment.php?" + as);
}
/*
Module pour changer la famille d'un produit ou de plusieurs produits
Code pour suppression d'un produit
Faire le code pour valider un/plusieurs/tous les produits et le/les exporter vers la db finale (si valide) --> modif statut global de l'import

Faire le code d'importation a partir d'un fichier xml vers la db
Faire le code d'importation a partir d'un fichier xls vers la db
	
	Rajouter Filtrage par colonne (comme sws)

*/
function ProductResponse(xhr) {
	
	var mainsplit = xhr.responseText.split(__MAIN_SEPARATOR__);
	var outputs = mainsplit[1].split(__OUTPUT_SEPARATOR__);
	
	if (mainsplit[0] == '') // Pas d'erreur
	{
		switch (outputs[0])
		{
			case "get" :
				var RefHeaders = ["ID", "Réf. F.", "Libellé"];
				var ref_count = 0;
				var RefData = [];
				for (var i = 1; i < outputs.length-1; i++)
				{
					var outputID = outputs[i].split(__OUTPUTID_SEPARATOR__);
					if (outputID.length != 2) break;
					switch (outputID[0])
					{
						case "id" :
						case "name" :
						case "fastdesc" :
						case "delivery_time" :
						case "url_image" :
							document.getElementById(outputID[0]).value = outputID[1];
							break;
						
						case "idFamily" :
							var fid = parseInt(outputID[1]);
							document.getElementById(outputID[0]).value = fid;
							document.getElementById("family_name").innerHTML = families[fid][name];
							break;
							
						case "descc" :
							document.getElementById(outputID[0]).innerHTML = outputID[1].replace(/(<br>)|(<br\/>)/gi, " ").substr(0,30);
							FCKdesccInstance.SetHTML(outputID[1]);
							break;
							
						case "descd" :
							document.getElementById(outputID[0]).innerHTML = outputID[1].replace(/(<br>)|(<br\/>)/gi, " ").substr(0,30);
							FCKdescdInstance.SetHTML(outputID[1]);
							break;
						
						case "mixed_data_entitle" :
							data = outputID[1].split(__DATA_SEPARATOR__);
							for (var j = 0; j < data.length-1; j++) RefHeaders.push(data[j]);
							break;
							
						case "ref_count" :
							ref_count = parseInt(outputID[1]);
							document.getElementById(outputID[0]).value = outputID[1];
							break;
						
						case "reference" :
							RefLine = outputID[1].split(__DATA_SEPARATOR__);
							RefLine.pop();
							len = RefLine.length;
							RefLine[0] = parseInt(RefLine[0]);
							RefLine[len-6] = parseInt(RefLine[len-6]);
							RefLine[len-5] = parseFloat(RefLine[len-5]);
							RefLine[len-4] = parseFloat(RefLine[len-4]);
							RefLine[len-3] = parseFloat(RefLine[len-3]);
							RefLine[len-2] = parseFloat(RefLine[len-2]);
							RefLine[len-1] = parseInt(RefLine[len-1]);
							RefData.push(RefLine);
							break;
							
						case "id_final" :
						case "status" :
						default : break;
					}
				}
				RefHeaders.push("Unité", "Taux TVA", "Prix F.", "Marge/remise", "Prix P.", "Ordre");
				rt.Destroy();
				rt.setHeaders(RefHeaders);
				rt.setInitialData(RefData);
				rt.setMultiPage(true);
				rt.setRowCount(10);
				rt.setColumnCount(RefHeaders.length);
				rt.setCurrentPage(1);
				rt.Refresh();
				rts.setCurrentPage(1);					rts2.setCurrentPage(1);
				rts.setLastPage(rt.getLastPage());		rts2.setLastPage(rt.getLastPage());
				rts.Refresh();							rts2.Refresh();
				epw.Show();
				document.getElementById("ProductGetError").innerHTML = "<br/>";
				break;
			
			case "alter" :
				var statusClass = "";
				switch (outputs[1])
				{
					case __IP_NOT_VALID__ : statusClass = "not-valid"; break;
					case __IP_VALID__ : statusClass = "valid"; break;
					case __IP_FINALIZED__ : statusClass = "finalized"; break;
					default : break;
				}
				RowEdited.data.push(statusClass);
				pt.AlterRow(RowEdited.rowNum, RowEdited.data);
				RowEdited = {};
				epw.Hide();
				document.getElementById("ProductGetError").innerHTML = "<br/>";
				break;
				
			case "delete" :
			default : break;
		}
	}
	else
	{
		var errors = mainsplit[0].replace(__ERROR_SEPARATOR__, "<br />");
		for (var i = 0; i < errors.length; i++) 
		switch (outputs[0])
		{
			case "get" : document.getElementById("ProductGetError").innerHTML = errors; RowEdited = {}; break;
			case "alter" : document.getElementById("ProductGetError").innerHTML = errors; RowEdited.data = []; break;
			default : document.getElementById("ProductGetError").innerHTML = errors; break;
		}
	}
}

// WINDOWS //
epw = new HN.Window();
epw.setID("EditProductWindow");
epw.setTitleText("Editer un produit");
epw.setMovable(true);
epw.showCancelButton(true);
epw.showValidButton(true);
epw.setValidFct( function() { AlterProduct(); } );
epw.setShadow(true);
epw.Build();

dw = new HN.Window();
dw.setID("DescriptionWindow");
dw.setTitleText("Edition de la description du produit");
dw.setMovable(false);
dw.showValidButton(true);
dw.setValidFct( function() { dw.Hide(); } );
dw.setShadow(true);
dw.Build();

ddw = new HN.Window();
ddw.setID("DetailedDescriptionWindow");
ddw.setTitleText("Edition de la description détaillée du produit");
ddw.setMovable(false);
ddw.showValidButton(true);
ddw.setValidFct( function() { ddw.Hide(); } );
ddw.setShadow(true);
ddw.Build();

fsw = new HN.Window();
fsw.setID("FamilySearchWindow");
fsw.setTitleText("Choisir une famille");
fsw.setMovable(true);
fsw.showCancelButton(true);
fsw.showValidButton(true);
fsw.setValidFct( function() {
	document.getElementById("idFamily").value = cur_family_id;
	document.getElementById("family_name").innerHTML = families[cur_family_id][name];
	fsw.Hide();
} );
fsw.setShadow(true);
fsw.Build();

//Product :	%id% name family %ref_name% fastdesc descc descd delai_livraison image 'contrainteProduit' 'tauxRemise' 'alias' 'keywords'
//Ref :		%idTC% refSupplier label mixed_headers price price2 unite marge idTVA place

// PRODUCTS //
pt = new HN.JSTable();
pt.setID("ProductsTable");
pt.setClass("CommonTable");
pt.setHeaders(["ID", "ID Famille", "Nom", "Famille", "Description rapide", "Description détaillée", "Description Technique", "Délai livr.", "URL image", "Nb refs", function(val, rowh) { rowh.className = val; } ]);
pt.setInitialData([
<?php
$result = & $handle->query("select id, id_final, name, idFamily, fastdesc, descc, descd, delivery_time, url_image, ref_count, status from imports_products where id_import = " . $ImportID, __FILE__, __LINE__, false);
$nbpdt = $handle->numrows($result, __FILE__, __LINE__);
for ($i = 0; $i < $nbpdt; $i++)
{
	$rec = & $handle->fetchAssoc($result);
	switch ($rec['status'])
	{
		case __IP_NOT_VALID__ : $statusClass = "not-valid"; break;
		case __IP_VALID__ : $statusClass = "valid"; break;
		case __IP_FINALIZED__ : $statusClass = "finalized"; break;
		default : break;
	}
	$array_br = array("<br>", "<BR>", "<Br>", "<bR>", "<br/>", "<BR/>", "<Br/>", "<bR/>");
	print '	[' . $rec['id'] . ', ' . $rec['idFamily'] . ', "' . $rec['name'] . '", families['.$rec['idFamily'].'][name], "' . $rec['fastdesc'] . '", "' . substr(str_replace($array_br, " ", $rec['descc']), 0, 30) . '...", "' . substr(str_replace($array_br, " ", $rec['descd']), 0, 30) . '...", "' . $rec['delivery_time'] . '", "' . $rec['url_image'] . '", ' . $rec['ref_count'] . ', "' . $statusClass . '"]' . ($i < $nbpdt ? "," : "")  . "\n";
}
/*
for ($i = 0; $i < 100; $i++)
{
	$time = mktime(mt_rand(0,23),mt_rand(0,59),mt_rand(0,59),4,mt_rand(1,24),2007);
	$date = date("Y/m/d H:i", $time);
	$date2 = date("Y/m/d H:i", $time + mt_rand(0,86400*2));
	
	print '	["147574898", "pdt_test_'.mt_rand(0,999) . '", "family_'.mt_rand(0,9) . '", "Desc rapide", "Desc détaillée", "Desc tech.", "délai liv.", "URL image", ' . mt_rand(0,99) . ', "' . sprintf("#%X%X%X", mt_rand(200,255), mt_rand(200,255), mt_rand(200,255)) . '"]' . ($i < 99 ? "," : "")  . "\n";
}
*/
?>
]);
pt.setColumnCount(10);
pt.setMultiPage(true);
pt.setRowCount(30);
pt.setCurrentPage(1);
pt.setRowFct( {
	"onmousedown" : function() { pt.ToggleSelect(this); this.check.checked = !this.check.checked; },
	"onmouseover" : function() { this.style.backgroundColor = "#CCCCCC"; },
	"onmouseout" : function() { this.style.backgroundColor = ""; }
} );
pt.setEditTools( {
	"check" : {"element" : "input", "attributes" : { "type" : "checkbox", "onclick" : function() { return false; } } },
	"edit" : {"element" : "div", "attributes" : { "onclick" : function() { ShowProduct(this.parentNode.parentNode.rowNum); this.parentNode.parentNode.onmousedown(); } } },
	"del" : {"element" : "div", "attributes" : { "onclick" : function() { alert(this.parentNode.parentNode.cc[0].textvalue + " deleted !") } } }
} );
pt.Refresh();
//it.SelectAll();

ps = new HN.PageSwitcher();				ps2 = new HN.PageSwitcher();
ps.setID("ProductsTableSwitcher1");		ps2.setID("ProductsTableSwitcher2");
ps.setCurrentPage(1);					ps2.setCurrentPage(1);
ps.setLastPage(pt.getLastPage());		ps2.setLastPage(pt.getLastPage());
ps.setTriggerFct( function(page)  { pt.setCurrentPage(page); ps2.setCurrentPage(page); pt.Refresh(); ps2.Refresh(); } );
ps2.setTriggerFct( function(page) { pt.setCurrentPage(page); ps.setCurrentPage(page);  pt.Refresh(); ps.Refresh(); } );
ps.Refresh();							ps2.Refresh();

// REFERENCES //
rt = new HN.JSTable();
rt.setID("ReferencesTable");
rt.setClass("CommonTable");
rt.setRowFct( {
	"onmousedown" : function() { rt.ToggleSelect(this); }
} );
var CellFct = {
	"onmouseover" : function() { this.style.backgroundColor = "#A0A0FF"; },
	"onmouseout" : function() { this.style.backgroundColor = ""; },
	"ondblclick" : function() {
		this.onmouseover = "";
		this.style.padding = "0 0 0 0";
		this.style.width = this.offsetWidth + "px";
		this.innerHTML = '<input type="text" class="edit" value="' + this.textvalue + '" style="width: ' + this.offsetWidth  + 'px" onblur="this.parentNode.CellChange();"/>';
		this.firstChild.focus();
	},
	"CellChange" : function() {
		this.onmouseover = CellFct.onmouseover;
		this.style.padding = "";
		this.style.width = "";
		this.textvalue = this.innerHTML = this.firstChild.value;
	}
};
rt.setCellFct(CellFct);
/*rt.setEditTools( {
	"del" : {"element" : "div", "attributes" : { "onclick" : function() { alert(this.parentNode.parentNode.cc[0].textvalue + " deleted !") } } }
} );*/

rts = new HN.PageSwitcher();			rts2 = new HN.PageSwitcher();
rts.setID("ReferencesTableSwitcher1");	rts2.setID("ReferencesTableSwitcher2");
rts.setTriggerFct( function(page)  { rt.setCurrentPage(page); rts2.setCurrentPage(page); rt.Refresh(); rts2.Refresh(); } );
rts2.setTriggerFct( function(page) { rt.setCurrentPage(page); rts.setCurrentPage(page);  rt.Refresh(); rts.Refresh(); } );

rts.setCurrentPage(1);					rts2.setCurrentPage(1);
rts.setLastPage(rt.getLastPage());		rts2.setLastPage(rt.getLastPage());
rts.Refresh();							rts2.Refresh();

</script>
</div>
<?php

require(ADMIN . 'tail.php');

?>
