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

$title = 'Import';
$navBar = '<a href="imports.php?SESSION" class="navig">Liste des importations</a> &raquo; Editer un import';

require(ADMIN . 'head.php');

require('_ClassImport.php');

$es = "";
if (!isset($_GET['id']))
	$es = "Le numéro identifiant de l'import n'a pas été spécifié";
elseif (($ImportID = (int)($_GET['id'])) <= 0)
	$es = "Le numéro identifiant de l'import est incorrect";
else
{
	$imp = & new Import($handle, $ImportID);
	if (!$imp->exist) $es = "L'import n° " . $ImportID . " n'existe pas.";
}
?>
<div class="titreStandard">Import</div>
<br />
<link href="HN.css" rel="stylesheet" type="text/css"/>
<div class="bg" id="bg_parent">
<?php
if (!empty($es))
{
?>
	<div class="InfosError"><?php echo $es ?></div>
</div>
<?php
	exit();
}
?>
<style type="text/css">
.legend-group { font: bold 11px Tahoma, Helvetica, sans-serif; border: 1px solid #000000; padding: 10px; display: inline; }
.legend-label { padding: 2px 10px 2px 15px; font-variant: small-caps; border: 1px solid #000000; }
.not-valid { background-color: #FFE0E0; }
.not-valid-update { background-color: #FFB0B0; }
.valid { background-color: #E0FFE0; }
.valid-update { background-color: #B0FFB0; }
.finalized { background-color: #E0E0FF; }
.finalized-update { background-color: #B0B0FF; }
#ProductsTable table { min-width: 1000px; }

#ProductsTable table .column-edit { min-width: 70px; text-align: center; }
#ProductsTable table .column-edit .check { float: left; }
#ProductsTable table .column-edit .edit { float: left; padding: 0 2px; width: 16px; height: 16px; background: url(b_edit.png) 2px 0px no-repeat; }
#ProductsTable table .column-edit .del { float: left; padding: 0 2px; width: 16px; height: 16px; background: url(b_drop.png) 2px 0px no-repeat; }
#ProductsTable table .column-0 { display: none; }
#ProductsTable table .column-1 { min-width: 115px; text-align: center; }
#ProductsTable table .column-2 { min-width: 115px; text-align: center; }
#ProductsTable table .column-3 { min-width: 125px; text-align: center; }
#ProductsTable table .column-4 { min-width: 175px; text-align: center; }
#ProductsTable table .column-5 { min-width: 175px; text-align: center; }
#ProductsTable table .column-6 { min-width: 75px; text-align: center; }
#ProductsTable table .column-7 { min-width: 100px; text-align: center; }
#ProductsTable table .column-8 { min-width: 50px; text-align: center; }
#ProductsTable .page-change { float: right; width: 200px; }

#EditProductWindowShad { z-index: 1; position: absolute; top: 35px; left: 55px; width: 904px; height: 514px; background-color: #000000; visibility: hidden; filter: Alpha (opacity=50, finishopacity=50, style=1) -moz-opacity:.50; opacity:.50; }
#EditProductWindow { z-index: 2; position: absolute; top: 30px; left: 50px; width: 900px; height: 510px; border: 2px solid #999999; background-color: #E9EFF8; visibility: hidden; }
#EditProductWindowBG { position: relative; width: 858px; height: 445px; }

#label_name				{ top: 13px; left: 10px; }
#name					{ top: 10px; left: 150px; width: 200px; height: 15px; }
#label_fastdesc			{ top: 35px; left: 10px; }
#fastdesc				{ top: 32px; left: 150px; width: 200px; height: 15px; }
#button_descc			{ top: 54px; left: 10px; width: 140px; padding: 0; }
#button_descd			{ top: 54px; left: 150px; width: 203px; padding: 0; }
#label_alias			{ top: 79px; left: 10px; }
#alias					{ top: 76px; left: 150px; width: 200px; height: 15px; }
#label_keywords			{ top: 101px; left: 10px; }
#keywords				{ top: 98px; left: 150px; width: 200px; height: 15px; }
#label_price			{ top: 123px; left: 10px; }
#price					{ top: 120px; left: 150px; width: 200px; height: 15px; }

#label_family_name		{ top: 13px; left: 380px; }
#family_name			{ top: 10px; left: 520px; width: 145px; }
#button_family_name		{ top: 10px; left: 670px; width: 53px; font-size: 11px; }
#label_url_image		{ top: 35px; left: 380px; }
#url_image				{ top: 32px; left: 520px; width: 200px; height: 15px; }
#label_url_doc1			{ top: 57px; left: 380px; }
#url_doc1				{ top: 54px; left: 520px; width: 200px; height: 15px; }
#label_url_doc2			{ top: 79px; left: 380px; }
#url_doc2				{ top: 76px; left: 520px; width: 200px; height: 15px; }
#label_url_doc3			{ top: 101px; left: 380px; }
#url_doc3				{ top: 98px; left: 520px; width: 200px; height: 15px; }
#label_delivery_time	{ top: 123px; left: 380px; }
#delivery_time			{ top: 120px; left: 520px; width: 200px; height: 15px; }

#label_online_sell		{ top: 32px; left: 760px; }
#online_sell			{ top: 54px; left: 760px; width: 100px; }

#ProductAlterError { position: absolute; top: 400px; left: 10px; width: 650px; }
#ReferencesSection { position: absolute; top: 140px; left: 10px; width: 858px; }
#button_finalize_product { top: 435px; left: 668px; width: 200px; }

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

#DescriptionWindowShad { z-index: 3; position: absolute; top: 65px; left: 80px; width: 754px; height: 404px; background-color: #000000; visibility: hidden; filter: Alpha (opacity=50, finishopacity=50, style=1) -moz-opacity:.50; opacity:.50; }
#DescriptionWindow { z-index: 4; position: absolute; top: 60px; left: 75px; width: 750px; height: 400px; border: 2px solid #999999; background-color: #E9EFF8; visibility: hidden; }

#DetailedDescriptionWindowShad { z-index: 3; position: absolute; top: 95px; left: 80px; width: 754px; height: 404px; background-color: #000000; visibility: hidden; filter: Alpha (opacity=50, finishopacity=50, style=1) -moz-opacity:.50; opacity:.50; }
#DetailedDescriptionWindow { z-index: 4; position: absolute; top: 90px; left: 75px; width: 750px; height: 400px; border: 2px solid #999999; background-color: #E9EFF8; visibility: hidden; }

/*.field, .fieldfl, .fieldfr { padding: 2px; height: 20px; }*/
.fieldfl { float: left; }
.fieldfr { float: right; }
/*.field input, .fieldfl input, .fieldfr input { border: 1px solid #C6D6D8; }*/


#MultiFamilySearchWindowShad { z-index: 3; position: absolute; top: 65px; left: 80px; width: 788px; height: 432px; background-color: #000000; visibility: hidden; filter: Alpha (opacity=50, finishopacity=50, style=1) -moz-opacity:.50; opacity:.50; }
#MultiFamilySearchWindow { width: 784px; height: 428px; z-index: 4; position: absolute; top: 60px; left: 75px; border: 2px solid #999999; visibility: hidden; }

#multi_family_name { height: 22px; margin: 5px; padding: 5px; border: 3px solid #C6D6D8; font: 12px Arial, Helvetica, sans-serif; }
#mfn_label { font-weight: bold; letter-spacing: 1px; color: #272727; }
#mfn_input { width: 200px; }
#mfn_helper { font-size: 11px; font-style: italic; }
#mfn_checkbox { float: left; width: 180px; }

#FamilySearchWindowShad { z-index: 3; position: absolute; top: 65px; left: 80px; width: 788px; height: 404px; background-color: #000000; visibility: hidden; filter: Alpha (opacity=50, finishopacity=50, style=1) -moz-opacity:.50; opacity:.50; }
#FamilySearchWindow { z-index: 4; position: absolute; top: 60px; left: 75px; border: 2px solid #999999; visibility: hidden; }

</style>
<script type="text/javascript">
var __SID__ = '<?php echo $sid ?>';
var __ADMIN_URL__ = '<?php echo ADMIN_URL ?>';
var __MAIN_SEPARATOR__ = '<?php echo __MAIN_SEPARATOR__ ?>';
var __ERROR_SEPARATOR__ = '<?php echo __ERROR_SEPARATOR__ ?>';
var __ERRORID_SEPARATOR__ = '<?php echo __ERRORID_SEPARATOR__ ?>';
var __OUTPUT_SEPARATOR__ = '<?php echo __OUTPUT_SEPARATOR__ ?>';
var __OUTPUTID_SEPARATOR__ = '<?php echo __OUTPUTID_SEPARATOR__ ?>';
var __DATA_SEPARATOR__ = '<?php echo __DATA_SEPARATOR__ ?>';
var __IP_NOT_VALID__ = '<?php echo __IP_NOT_VALID__ ?>';
var __IP_NOT_VALID_UPDATE__ = '<?php echo __IP_NOT_VALID_UPDATE__ ?>';
var __IP_VALID__ = '<?php echo __IP_VALID__ ?>';
var __IP_VALID_UPDATE__ = '<?php echo __IP_VALID_UPDATE__ ?>';
var __IP_FINALIZED__ = '<?php echo __IP_FINALIZED__ ?>';
var __IP_FINALIZED_UPDATE__ = '<?php echo __IP_FINALIZED_UPDATE__ ?>';

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
	$menu_families .= '<a href="familles/' . $id .'">' . to_entities($families[$id]['name']) . "</a> ";

?>
// TODO intégrer le get des familles en ajax dans l'objet FamiliesBrowser
var families = [];
var familiesIndexByName = [];
var familiesIndexByRefName = [];
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
	print  ']; ';
	//print 'familiesIndexById[' . $id . '] = ' . $id . '; ';
	print 'familiesIndexByName["' . str_replace('"', '\"', $fam['name']) . '"] = ' . $id . '; ';
	print 'familiesIndexByRefName["' . $fam['ref_name'] . '"] = ' . $id . ';';
	print "\n";
}
?>
</script>
<script src="Classes.js" type="text/javascript"></script>
<script src="../js/ManagerFunctions.js" type="text/javascript"></script>
<script src="../editor/fckeditor.js" type="text/javascript"></script>
<div id="PerfReqImports"></div>
<div id="DescriptionWindow">
	<script type="text/javascript">
	var sBasePath = '<?php echo ADMIN_URL ?>editor/';
	var oFCKeditor = new FCKeditor('FCKdescc');
		oFCKeditor.BasePath	= sBasePath;
		oFCKeditor.Height = 377;
		oFCKeditor.Width  = 750;
		oFCKeditor.Config['CustomConfigurationsPath'] = '<?php echo ADMIN_URL ?>files/myconfig.js';
		oFCKeditor.Value = '';
		oFCKeditor.Create();
	</script>
</div>
<div id="DetailedDescriptionWindow">
	<script type="text/javascript">
	var pFCKeditor = new FCKeditor('FCKdescd');
		pFCKeditor.BasePath	= sBasePath;
		pFCKeditor.Height = 377;
		pFCKeditor.Width  = 750;
		pFCKeditor.Config['CustomConfigurationsPath'] = '<?php echo ADMIN_URL ?>files/myconfig.js';
		pFCKeditor.Value = '';
		pFCKeditor.Create();
	</script>
</div>
<div id="EditProductWindow">
	<div id="EditProductWindowBG" class="window_bg">
		<input type="hidden" id="id"/>
		<input type="hidden" id="ref_count"/>
		<div id="ProductAlterError" class="InfosError"><br/></div>
		
		<span id="label_name" class="label">Nom (mot clé google) : </span><input id="name" type="text" maxlength="255" class="field"/>
		<span id="label_fastdesc" class="label">Description rapide : </span><input id="fastdesc" type="text" maxlength="255" class="field"/>
		<input id="button_descc" type="button" class="button bouton" value="Editer la description" onclick="dw.Show();"/>
		<input id="button_descd" type="button" class="button bouton" value="Editer la description technique" onclick="ddw.Show();"/>
		<span id="label_alias" class="label">Alias : </span><input id="alias" type="text" maxlength="255" class="field"/>
		<span id="label_keywords" class="label">Keywords : </span><input id="keywords" type="text" maxlength="255" class="field"/>
		<span id="label_price" class="label">Prix/Disponibilité : </span><input id="price" type="text" maxlength="255" class="field"/>
		
		<span id="label_family_name" class="label">Famille : </span><input id="family_name" type="text" class="field"/><input id="button_family_name" type="button" class="button bouton" value="Choisir" onclick="SelectProductFamily();"/>
		<span id="label_url_image" class="label">image : </span><input id="url_image" type="text" maxlength="255" class="field"/>
		<span id="label_url_doc1" class="label">document 1 : </span><input id="url_doc1" type="text" maxlength="255" class="field"/>
		<span id="label_url_doc2" class="label">document 2 : </span><input id="url_doc2" type="text" maxlength="255" class="field"/>
		<span id="label_url_doc3" class="label">document 3 : </span><input id="url_doc3" type="text" maxlength="255" class="field"/>
		<span id="label_delivery_time" class="label">Délai de livraison : </span><input id="delivery_time" type="text" maxlength="255" class="field"/>
		
		<span id="label_online_sell" class="label">Vente en ligne : </span><select id="online_sell" class="field"><option value="0">Désactivée</option><option value="1">Activée</option></select>
		
		<div id="ReferencesSection">
			<div id="ReferencesTableSwitcher1"></div>
			<div class="zero"></div>
			<div id="ReferencesTable"></div>
			<div id="ReferencesTableSwitcher2"></div>
		</div>
		<input id="button_finalize_product" type="button" class="button bouton" value="Finaliser l'import de ce produit" onclick="FinalizeProduct();"/>
	</div>
</div>
<div id="FamilySearchWindow"></div>
<div id="MultiFamilySearchWindow">
	<div id="multi_family_name">
		<div id="mfn_checkbox"><input id="mfn_create_tree" type="checkbox"/>Créer un arbre de famille</div>
		<span id="mfn_label">Famille :</span>
		<input id="mfn_input" type="text"/>
		<span id="mfn_helper"><-- saisissez ici l'arbre complet à créer automatiquement</span>
	</div>
</div>
<div id="PerfReqLabelProducts" class="PerfReqLabel"><br/></div>
<div id="ProductGetError" class="InfosError"><br/></div>
<br/>
<div class="legend-group">
	Légende :
	<span class="legend-label not-valid">non valide</span>
	<span class="legend-label not-valid-update">maj non valide</span>
	<span class="legend-label valid">valide</span>
	<span class="legend-label valid-update">maj valide</span>
	<span class="legend-label finalized">finalisé</span>
	<span class="legend-label finalized-update">maj finalisé</span>
</div>
<div id="ProductsTableSwitcher1"></div>
<div class="zero"></div>
<div id="ProductsTable"></div>
<div id="ProductsTableSwitcher2"></div>
<div class="zero"></div>
<div>
	<img src="arrow_ltr.png"/>
	Pour la sélection
	<input type="button" class="bouton" value="Changer la famille" onclick="SelectProductsFamily();"/>
	<input type="button" class="bouton" value="Finaliser l'import" onclick="MultiFinalizeProducts();"/>
</div>
<br />
<input type="button" class="bouton" value="Finaliser l'import de tous les produits valides" onclick="FinalizeAllValidProducts();"/>
<script type="text/javascript">
// FAMILIES //

fb = new HN.FamiliesBrowser();
fb.setID("FamilySearchWindow");

mfb = new HN.FamiliesBrowser();
mfb.setID("MultiFamilySearchWindow");

function SelectProductsFamily()
{
	mfsw.Show();
	mfb.Build();
}
function SelectProductFamily ()
{
	fb.Build();
	var fid;
	if (!(fid = familiesIndexByName[document.getElementById("family_name").value])) fid = 0;
	fb.SelectFamByID(fid);
	fsw.Show();
}

function FinalizeProduct() {
	var id = parseInt(document.getElementById("id").value);
	ProductsAJAXHandle.QueryA("ProductsManagment.php?action=finalize&id=" + id);
}

function MultiFinalizeProducts() {
	var rs = pt.getSelectedRows();
	var s = "";
	for (var rowNum in rs) s += rs[rowNum].cc[0].textvalue + "-";
	ProductsAJAXHandle.QueryA("ProductsManagment.php?action=multi-finalize&ids=" + s);
}

function FinalizeAllValidProducts() {
	var i = 0, s = "";
	while (row = pt.getRow(i++))
		if (row.className == "valid" || row.className == "valid-update") s += row.cc[0].textvalue + "-";
	
	ProductsAJAXHandle.QueryA("ProductsManagment.php?action=multi-finalize&ids=" + s);
}

var FCKInstancesloaded = 0;
var FCKdesccInstance = null;
var FCKdescdInstance = null;

function FCKeditor_OnComplete( editorInstance ) {
	switch (editorInstance.Name)
	{
		case "FCKdescc" : FCKdesccInstance = editorInstance; break;
		case "FCKdescd" : FCKdescdInstance = editorInstance; break;
	}
	FCKInstancesloaded++;
}

var ProductsAJAXHandle = new AJAXHandle(ProductResponse, "PerfReqLabelProducts");


function ShowProduct(row) {
	if (FCKInstancesloaded != 2) return false
	ProductsAJAXHandle.QueryA("ProductsManagment.php?action=get&id=" + parseInt(pt.getCell(row,0).textvalue));
}

function AlterMultiProductFamily (fname)
{
	var rs = pt.getSelectedRows();
	var s = "";
	for (var rowNum in rs) s += rs[rowNum].cc[0].textvalue + "-";
	ProductsAJAXHandle.QueryA("ProductsManagment.php?action=multi-alter&ids=" + s + "&family_name=" + escape(fname));
}

function AlterProduct() {
	if (FCKInstancesloaded != 2) return false
	
	var as = "action=alter";
	as += "&id=" + document.getElementById("id").value;
	as += "&name=" + escape(document.getElementById("name").value);
	as += "&family_name=" + escape(document.getElementById("family_name").value);
	as += "&fastdesc=" + escape(document.getElementById("fastdesc").value);
	as += "&descc=" + escape(FCKdesccInstance.GetHTML());
	as += "&descd=" + escape(FCKdescdInstance.GetHTML());
	as += "&delivery_time=" + escape(document.getElementById("delivery_time").value);
	as += "&url_image=" + escape(document.getElementById("url_image").value);
	as += "&url_doc1=" + escape(document.getElementById("url_doc1").value);
	as += "&url_doc2=" + escape(document.getElementById("url_doc2").value);
	as += "&url_doc3=" + escape(document.getElementById("url_doc3").value);
	as += "&price=" + escape(document.getElementById("price").value);
	as += "&alias=" + escape(document.getElementById("alias").value);
	as += "&keywords=" + escape(document.getElementById("keywords").value);
	var ref_count = parseInt(document.getElementById("ref_count").value);
	as += "&ref_count=" + ref_count;
	as += "&online_sell=" + document.getElementById("online_sell").options[document.getElementById("online_sell").options.selectedIndex].value;
	
	headers = rt.getHeaders();
	var i = 3;
	while (headers[i] != "Unité") as += "&mixed_data_entitle_" + (i-3) + "=" + escape(headers[i++]);
	var nb_mixed_header = i-3;
	
	for (i = 0; i < ref_count; i++)
	{
		var col = 0;
		var row = rt.getRow(i);
		as += "&id_ref_" + i + "=" + rt.getCell(row,col++).textvalue;
		as += "&ref_supplier_" + i + "=" + escape(rt.getCell(row,col++).textvalue);
		as += "&label_" + i + "=" + escape(rt.getCell(row,col++).textvalue);
		for (var j = 0; j < nb_mixed_header; j++)
			as += "&mixed_data_" + i + "_" + j + "=" + escape(rt.getCell(row,col++).textvalue);
		as += "&unit_" + i + "=" + rt.getCell(row,col++).textvalue;
		as += "&VAT_" + i + "=" + rt.getCell(row,col++).textvalue;
		as += "&price2_" + i + "=" + rt.getCell(row,col++).textvalue;
		as += "&marge_" + i + "=" + rt.getCell(row,col++).textvalue;
		as += "&price_" + i + "=" + rt.getCell(row,col++).textvalue;
		as += "&order_" + i + "=" + rt.getCell(row,col++).textvalue;
	}
	
	//alert(as.replace(/&/g, "\n"));
	ProductsAJAXHandle.QueryA("ProductsManagment.php?" + as);
}

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
						case "family_name" :
						case "fastdesc" :
						case "delivery_time" :
						case "url_image" :
						case "price" :
						case "alias" :
						case "keywords" :
						case "online_sell" :
							document.getElementById(outputID[0]).value = outputID[1];
							break;
						
						case "descc" :
							//document.getElementById(outputID[0]).innerHTML = outputID[1].replace(/(<br>)|(<br\/>)/gi, " ").substr(0,30);
							FCKdesccInstance.SetHTML(outputID[1]);
							break;
							
						case "descd" :
							//document.getElementById(outputID[0]).innerHTML = outputID[1].replace(/(<br>)|(<br\/>)/gi, " ").substr(0,30);
							FCKdescdInstance.SetHTML(outputID[1]);
							break;
						
						case "mixed_data_entitle" :
							var data = outputID[1].split(__DATA_SEPARATOR__);
							for (var j = 0; j < data.length-1; j++) RefHeaders.push(data[j]);
							break;
							
						case "url_docs" :
							var data = outputID[1].split(__DATA_SEPARATOR__);
							for (var j = 0; j < data.length-1; j++) document.getElementById("url_doc" + (j+1)).value = data[j];
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
							break;
							
						case "status" :
							if (outputID[1] == __IP_FINALIZED__) document.getElementById("button_finalize_product").disabled = true;
							else document.getElementById("button_finalize_product").disabled = false;
							break;
							
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
				var rowData = [];
				for (var i = 1; i < outputs.length-1; i++)
				{
					var outputID = outputs[i].split(__OUTPUTID_SEPARATOR__);
					if (outputID.length != 2) break;
					switch (outputID[0])
					{
						case "id" : rowData[0] = parseInt(outputID[1]); break;
						case "name" : rowData[1] = outputID[1]; break;
						case "family_name" : rowData[2] = outputID[1]; break;
						case "fastdesc" : rowData[3] = outputID[1]; break;
						case "descc" : rowData[4] = outputID[1]; break;
						case "descd" : rowData[5] = outputID[1]; break;
						case "delivery_time" : rowData[6] = outputID[1]; break;
						case "url_image" : rowData[7] = outputID[1]; break;
						case "ref_count" : rowData[8] = parseInt(outputID[1]); break;
						case "status" :
							switch (outputID[1])
							{
								case __IP_NOT_VALID__ : rowData[9] = "not-valid"; break;
								case __IP_NOT_VALID_UPDATE__ : rowData[9] = "not-valid-update"; break;
								case __IP_VALID__ : rowData[9] = "valid"; break;
								case __IP_VALID_UPDATE__ : rowData[9] = "valid-update"; break;
								case __IP_FINALIZED__ : rowData[9] = "finalized"; break;
								case __IP_FINALIZED_UPDATE__ : rowData[9] = "finalized-update"; break;
								default : break;
							}
							break;
						default: break;
					}
				}
				var row = pt.getRowByIndex(rowData[0], 0);
				pt.AlterRow(row, rowData);
				epw.Hide();
				document.getElementById("ProductAlterError").innerHTML = "";
				break;
				
			case "delete" :
				break;
			
			case "finalize" :
				var statusClass = "";
				switch (outputs[2])
				{
					case __IP_FINALIZED__ : statusClass = "finalized"; break;
					case __IP_FINALIZED_UPDATE__ : statusClass = "finalized-update"; break;
					default : break;
				}
				var row = pt.getRowByIndex(parseInt(outputs[1]), 0);
				pt.AlterCell(row, 9, statusClass);
				epw.Hide();
				document.getElementById("ProductGetError").innerHTML = "<br/>";
				break;
			
			case "multi-alter" :
				var fields = [], ids_status;
				for (var j = 1; j < outputs.length-1; j++)
				{
					var outputID = outputs[j].split(__OUTPUTID_SEPARATOR__);
					if (outputID.length != 2) break;
					switch (outputID[0])
					{
						case "family_name" : fields[2] = outputID[1]; break;
						case "fastdesc" : fields[3] = outputID[1]; break;
						case "descc" : fields[4] = outputID[1]; break;
						case "descd" : fields[5] = outputID[1]; break;
						case "delivery_time" : fields[6] = outputID[1]; break;
						case "url_image" : fields[7] = outputID[1]; break;
						case "ids" : ids_status = outputID[1].split(__DATA_SEPARATOR__); break;
						default: break;
					}
				}
				for (var k = 0; k < ids_status.length-1; k+=2)
				{
					var row = pt.getRowByIndex(parseInt(ids_status[k]), 0);
					for (var field in fields)
						pt.AlterCell(row, parseInt(field), fields[field]);
					
					var statusClass = "";
					switch (ids_status[k+1])
					{
						case __IP_NOT_VALID__ : statusClass = "not-valid"; break;
						case __IP_NOT_VALID_UPDATE__ : statusClass = "not-valid-update"; break;
						case __IP_VALID__ : statusClass = "valid"; break;
						case __IP_VALID_UPDATE__ : statusClass = "valid-update"; break;
						case __IP_FINALIZED__ : statusClass = "finalized"; break;
						case __IP_FINALIZED_UPDATE__ : statusClass = "finalized-update"; break;
						default : break;
					}
					pt.AlterCell(row, 9, statusClass);
				}
				document.getElementById("ProductGetError").innerHTML = "<br/>";
				mfsw.Hide();
				break;
				
			case "multi-finalize" :
				for (var j = 1; j < outputs.length-1; j+=2)
				{
					var statusClass = "";
					switch (outputs[j+1])
					{
						case __IP_FINALIZED__ : statusClass = "finalized"; break;
						case __IP_FINALIZED_UPDATE__ : statusClass = "finalized-update"; break;
						default : break;
					}
					var row = pt.getRowByIndex(parseInt(outputs[j]), 0);
					pt.AlterCell(row, 9, statusClass);
				}
				document.getElementById("ProductGetError").innerHTML = "<br/>";
				break;
			default : break;
		}
	}
	else
	{
		var errors = mainsplit[0].replace(__ERROR_SEPARATOR__, "<br/>");
		for (var i = 0; i < errors.length; i++) 
		switch (outputs[0])
		{
			case "get" : document.getElementById("ProductGetError").innerHTML = errors; break;
			case "alter" : document.getElementById("ProductAlterError").innerHTML = errors; break;
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
dw.setMovable(true);
dw.showValidButton(true);
dw.setValidFct( function() { dw.Hide(); } );
dw.setShadow(true);
dw.Build();

ddw = new HN.Window();
ddw.setID("DetailedDescriptionWindow");
ddw.setTitleText("Edition de la description détaillée du produit");
ddw.setMovable(true);
ddw.showValidButton(true);
ddw.setValidFct( function() { ddw.Hide(); } );
ddw.setShadow(true);
ddw.Build();

fsw = new HN.Window();
fsw.setID("FamilySearchWindow");
fsw.setTitleText("Choisir la famille d'un produit");
fsw.setMovable(true);
fsw.showCancelButton(true);
fsw.showValidButton(true);
fsw.setValidFct( function() {
	document.getElementById("family_name").value = families[fb.getCurFamID()][name];
	fsw.Hide();
} );
fsw.setShadow(true);
fsw.Build();

mfsw = new HN.Window();
mfsw.setID("MultiFamilySearchWindow");
mfsw.setTitleText("Choisir une famille pour un ensemble de produit");
mfsw.setMovable(true);
mfsw.showCancelButton(true);
mfsw.showValidButton(true);
mfsw.setValidFct( function() {
	var fname = "";
	if (document.getElementById('mfn_create_tree').checked)
		fname = document.getElementById('mfn_input').value;
	else
		fname = families[mfb.getCurFamID()][name];
	AlterMultiProductFamily(fname);
	} );
mfsw.setShadow(true);
mfsw.Build();

//Product :	%id% name family %ref_name% fastdesc descc descd delai_livraison image 'contrainteProduit' 'tauxRemise' 'alias' 'keywords'
//Ref :		%idTC% refSupplier label mixed_headers price price2 unite marge idTVA place

// PRODUCTS //
pt = new HN.JSTable();
pt.setID("ProductsTable");
pt.setClass("CommonTable");
pt.setHeaders(["ID", "Nom", "Famille", "Description rapide", "Description détaillée", "Description Technique", "Délai livr.", "URL image", "Nb refs", function(val, rowh) { rowh.className = val; } ]);
pt.setColIndex([0]);
pt.setInitialData([
<?php
$result = & $handle->query("select id, id_final, name, family_name, fastdesc, descc, descd, delivery_time, url_image, ref_count, status from imports_products where id_import = " . $ImportID, __FILE__, __LINE__, false);
$nbpdt = $handle->numrows($result, __FILE__, __LINE__);
for ($i = 0; $i < $nbpdt; $i++)
{
	$rec = & $handle->fetchAssoc($result);
	switch ($rec['status'])
	{
		case __IP_NOT_VALID__ : $statusClass = "not-valid"; break;
		case __IP_NOT_VALID_UPDATE__ : $statusClass = "not-valid-update"; break;
		case __IP_VALID__ : $statusClass = "valid"; break;
		case __IP_VALID_UPDATE__ : $statusClass = "valid-update"; break;
		case __IP_FINALIZED__ : $statusClass = "finalized"; break;
		case __IP_FINALIZED_UPDATE__ : $statusClass = "finalized-update"; break;
		default : break;
	}
	$array_search = array("/<br[[:space:]]*\/?>/i", "/\"/", "/\r\n/", "/\r/", "/\n/");
	$array_replace = array(' ', '\"', "", "", "");
	print '	[' . $rec['id'] . ', "' .
	preg_replace($array_search, $array_replace, substr($rec['name'], 0, 30)) . '", "' .
	preg_replace($array_search, $array_replace, substr($rec['family_name'], 0, 30)) . '", "' .
	preg_replace($array_search, $array_replace, substr($rec['fastdesc'], 0, 30)) . '", "' .
	preg_replace($array_search, $array_replace, substr($rec['descc'], 0, 30)) . '...", "' .
	preg_replace($array_search, $array_replace, substr($rec['descd'], 0, 30)) . '...", "' .
	preg_replace($array_search, $array_replace, substr($rec['delivery_time'], 0, 30)) . '", "' .
	preg_replace($array_search, $array_replace, substr($rec['url_image'], 0, 30)) . '", ' .
	$rec['ref_count'] . ', "' . $statusClass . '"]' . ($i < ($nbpdt-1) ? "," : "")  . "\n";
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
pt.setColumnCount(9);
pt.setMultiPage(true);
pt.setRowCount(20);
pt.setCurrentPage(1);
pt.setRowFct( {
	"onmousedown" : function() { pt.ToggleSelect(this); this.check.checked = !this.check.checked; },
	"onmouseover" : function() { this.style.backgroundColor = "#CCCCCC"; },
	"onmouseout" : function() { this.style.backgroundColor = ""; }
} );
pt.setEditTools( {
	"check" : {"element" : "input", "attributes" : { "type" : "checkbox", "onclick" : function() { return false; } } },
	"edit" : {"element" : "div", "attributes" : { "onclick" : function() { ShowProduct(this.parentNode.parentNode); this.parentNode.parentNode.onmousedown(); } } }
	//"del" : {"element" : "div", "attributes" : { "onclick" : function() { alert(this.parentNode.parentNode.cc[0].textvalue + " deleted !") } } }
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
