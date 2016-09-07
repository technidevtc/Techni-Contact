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

$title = 'Export';
$navBar = '<a href="exports.php?SESSION" class="navig">Liste des exports</a> &raquo; Editer un export';

require(ADMIN . 'head.php');

require('_ClassExport.php');

/*
effiliation Cédric Dausse 01 72 36 61 43
--------------------------------------------------------------------------------
-- partner_tags
--------------------------------------------------------------------------------
$tags = array
(
	"catalogue" => array
	(
		"tag_occurence" => "once",
		"tag_attributes" => array
		(
			"lang" => array("type" => "var", "value" => "__LANG__"),
			"date" => array("type" => "func", "value" => "func"),
			"GMT" => array("type" => "func", "value" => "func")
		),
		"tag_content" => array
		(
			array("type" => "tag", "value" => "product")
		)
	),
	"product" => array
	(
		"tag_occurence" => "product",
		"tag_attributes" => array
		(
			"place" => array("type" => "var", "value" => "__NUM_PRODUCT__")
		),
		"tag_content" => array
		(
			array("type" => "fields", "value" => "compulsory_field"),
			array("type" => "fields", "value" => "facultative_field")
		)
	),
)

spec :
----------------------------------------
for each tag we have 3 properties :
	tag_occurence
		once : only one time in the xml (much probably a top hierarchy tag)
		product : each time a product is read
		family : each time a new family is read (so we need to have an index providing a sort by family to have a readable xml)
		advertiser : each time a new advertiser is read (sort needed to here)
	tag_attributes
		type = value
			cst = "constant"			// a text to always put her
			var = __LANG__, __FAMILY...	// a predefined var
			func = date("Y-m-d H:i")	// a php func to eval
	tag_content
		type = value
			tag = product										// refers to a defined tag which is contained in the current one
			fields = compulsory_fields or facultative_fields	// one or both of the group of fields
			text = "this is some text"							// some text

--------------------------------------------------------------------------------
-- compulsory_fields and facultative_fields
--------------------------------------------------------------------------------

$_ckeys = array
(
	"categorie" => array("type" => "edit", "source_type" => "UC", "default" => "__PRODUCT_FAMILY_TREE__", "filter_type" => "nb=3 sep=>"),
	"identifiant_unique" => array("type" => "edit", "source_type" => "UC", "default" => "__PRODUCT_IDTC__", "filter_type" => ""),
	"titre" => array("type" => "edit", "source_type" => "UC", "default" => "__PRODUCT_NAME__", "filter_type" => ""),
	"prix" => array("type" => "edit", "source_type" => "UC", "default" => "__PRODUCT_PRICE_P__", "filter_type" => ""),
	"URL_produit" => array("type" => "edit", "source_type" => "UC", "default" => "__PRODUCT_URL__", "filter_type" => "url")
);

$_fkeys = array(
	"URL_image" => array("type" => "edit", "source_type" => "UC", "default" => "__PRODUCT_IMAGE_URL__", "filter_type" => "url"),
	"description" => array("type" => "text", "source_type" => "UC", "default" => "__PRODUCT_DESCC__", "filter_type" => ""),
	"livraison" => array("type" => "edit", "source_type" => "UC", "default" => "__PRODUCT_DELIVERY_TIME__", "filter_type" => ""),
	"disponibilite" => array("type" => "list", "source_type" => "UI", "default" => "en stock", "filter_type" => "", "option1" => "en stock", "option2" => "en cours de réapprovisionnement", "option3" => "disponible chez le fournisseur", "option4" => "non disponible"),
	"marque" => array("type" => "edit", "source_type" => "UI", "default" => "Techni-Contact", "filter_type" => "")
);


$_ckeys = array (
	"sku" => array("type" => "edit", "source_type" => "UC", "default" => "__PRODUCT_IDTC__", "filter_type" => ""),
	"StandardProductID" => array("type" => "edit", "source_type" => "UC", "default" => "__PRODUCT_EAN__", "filter_type" => ""),
	"ProductIDType" => array("type" => "edit", "source_type" => "UI", "default" => "EAN", "filter_type" => ""),
	"ProductName" => array("type" => "edit", "source_type" => "UC", "default" => "__PRODUCT_NAME__", "filter_type" => ""),
	"Brand" => array("type" => "edit", "source_type" => "UI", "default" => "Techni-Contact", "filter_type" => ""),
	"Manufacturer" => array("type" => "edit", "source_type" => "UI", "default" => "Techni-Contact", "filter_type" => ""),
	"ProductType" => array("type" => "edit", "source_type" => "UI", "default" => "Kitchen", "filter_type" => ""),
	"RecommendedBrowseNode1" => array("type" => "edit", "source_type" => "UI", "default" => "", "filter_type" => "url"),
	"MainImageURL" => array("type" => "edit", "source_type" => "UC", "default" => "__PRODUCT_MAIN_IMAGE_URL__", "filter_type" => ""),
	"LaunchDate" => array("type" => "edit", "source_type" => "UI", "default" => "", "filter_type" => ""),
	"ItemPrice" => array("type" => "edit", "source_type" => "UC", "default" => "__PRODUCT_PRICE_TTC__", "filter_type" => ""),
	"Currency" => array("type" => "edit", "source_type" => "UI", "default" => "EUR", "filter_type" => ""),
	"Quantity" => array("type" => "edit", "source_type" => "UI", "default" => "100", "filter_type" => ""),
	"Description" => array("type" => "edit", "source_type" => "UC", "default" => "__PRODUCT_DESCC__", "filter_type" => ""),
	"item-condition" => array("type" => "edit", "source_type" => "UI", "default" => "New", "filter_type" => ""),
	"leadtime-to-ship" => array("type" => "edit", "source_type" => "UI", "default" => "3", "filter_type" => "")
);

$_fkeys = array (
);

print_r($_ckeys);
print "<br/><br/>CKEYS<br/>\n" . serialize($_ckeys) . "\n<br/><br/>";

print_r($_fkeys);
print "<br/><br/>FKEYS<br/>\n" . serialize($_fkeys) . "\n<br/><br/>";

exit();


spec :
----------------------------------------
for each field we have : [type, source_type, default, filter_type, var1, var2, var3..., varn]

type :	// the type of input which will be used
- edit : edit value
- medit : multiple edit value (new window)
- text : textareae (new window)
- list : select with list
- cst  : constant not modifiable

source_type :	// where does the data come from
- UI : User Input : Free user input
- UC : User Common : One of the Common Product var (from DB)
- US : User Select : Selection over a list of value

default : default value while creating the product if source is empty, or var if UC

filter_type : // filter to use while reading the data
- int   : converto to integer
- float : convert to float
- cdata : add cdata tags
- noaccent : filter all accent
- google : make the content google (no accent, no space, no special char, no uppercase, only "-" allowed)
- allowtags : list of allowed html tags permitted separated by a coma (none = no tag at all, span,b,i = <span> <b> and <i> allowed)

var1, var2, var3..., varn :
Variables used by the source_type
- UI : none
- UC : none
- US : var1 = option1, var2 = option2..., varn = optionn

// TODO TODAY
// rajouter balise propre au partenaire pour chaque produit <product place="#1">
// régler ebay/xinok/leguide
// rajouter commentaire produit
*/

$es = "";
if (!isset($_GET['id']))
	$es = "Le numéro identifiant de l'export n'a pas été spécifié";
elseif ($_GET['id'] != "new" && ($ExportID = (int)($_GET['id'])) <= 0)
	$es = "Le numéro identifiant de l'export est incorrect";
elseif ($_GET['id'] == "new")
{
	if (!isset($_GET['partner_id']))
		$es = "Aucun partenaire n'a été spécifié pour ce nouvel export.";
	elseif (($partner_id = (int)$_GET['partner_id']) <= 0)
		$es = "Le partenaire spécifié pour ce nouvel export n'existe pas.";
	else
	{
		$exp = & new Export($handle);
		if (!$exp->Create($partner_id)) $es = "Impossible de créer le nouvel export : " . $exp->lastErrorMessage;
		else $exp->Save();
	}
}
else
{
	$exp = & new Export($handle, $ExportID);
	if (!$exp->exist) $es = "L'export n° " . $ExportID . " n'existe pas.";
}
?>
<div class="titreStandard">Export</div>
<br />
<link href="HN.css" rel="stylesheet" type="text/css"/>
<link href="Command.css" rel="stylesheet" type="text/css"/>
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
define("__LABEL_OFFSET__", 3);
define("__LABEL_WIDTH__", 140);
define("__LEFT_MARGIN_1__", 10);
define("__LEFT_MARGIN_2__", 380);
define("__LINE_HEIGHT__", 22);
define("__INPUT_WIDTH__", 200);
define("__INPUT_HEIGHT__", 15);
?>
<style type="text/css">
#ProductsTable table { min-width: 1000px; }
#ProductsTable table tr.not-valid { background-color: #FFD0D0; }
#ProductsTable table tr.valid { background-color: #D0FFD0; }
#ProductsTable table tr.finalized { background-color: #D0D0FF; }

#ProductsTable table .column-edit { width: 55px; }
#ProductsTable table .column-edit .check { float: left; }
#ProductsTable table .column-edit .edit { float: left; padding: 0 2px; width: 16px; height: 16px; background: url(b_edit.png) 2px 0px no-repeat; }
#ProductsTable table .column-edit .del { float: left; padding: 0 2px; width: 16px; height: 16px; background: url(b_drop.png) 2px 0px no-repeat; }
#ProductsTable table .column-0 { width: 60px; text-align: center; }
#ProductsTable table .column-1 { width: auto; text-align: center; }
#ProductsTable table .column-2 { width: auto; text-align: center; }
#ProductsTable table .column-3 { width: auto; text-align: center; }
#ProductsTable table .column-4 { width: auto; text-align: center; }
#ProductsTable table .column-5 { width: auto; text-align: center; }
#ProductsTable table .column-6 { width: auto; text-align: center; }
#ProductsTable table .column-7 { width: auto; text-align: center; }
#ProductsTable table .column-8 { width: auto; text-align: center; }
#ProductsTable .page-change { float: right; width: 200px; }
.label_dynamic { width: <?php echo __LABEL_WIDTH__ ?>px; height: 22px; float: left; }
.field_dynamic { width: <?php echo __INPUT_WIDTH__ ?>px; height: <?php echo __INPUT_HEIGHT__ ?>px; }

<?php

$styles = $fields_html = $js_code = "";

$top_c_section = 10;
$styles .= "#cfield_section { position: absolute; top: " . $top_c_section . "px; left: 10px; font-size: 14px; font-weight: bold; width: 671px; padding: 2px 20px; border: 1px solid black; background-color: #F8FDFF; }\n";
define("__TOP_MARGIN__", $top_c_section + 25);

$fields_html .= '		<div id="cfield_section">Champs obligatoires</div>';

$nfield = 0;
$top = __TOP_MARGIN__;
$left = __LEFT_MARGIN_1__;
foreach ($exp->c_keys as $field_name)
{
	switch($exp->compulsory_fields[$field_name]["type"])
	{
		case "edit" :
			$top_label = $top + __LABEL_OFFSET__;
			$left_input = $left + __LABEL_WIDTH__;
			$styles .= "#label_" . $field_name . " { top: " . $top_label . "px; left: " . $left . "px; }\n";
			$styles .= "#field_" . $field_name . " { top: " . $top . "px; left: " . $left_input . "px; width: " . __INPUT_WIDTH__ . "px; height: " . __INPUT_HEIGHT__ . "px; }\n";
			$fields_html .= '		<span id="label_' . $field_name . '" class="label">' . $field_name . ' : </span><input id="field_' . $field_name . '" type="text" maxlength="255" class="field"/>' . "\n";
			break;
		case "medit" :
			$top_label = $top + __LABEL_OFFSET__;
			$left_button = $left + __LABEL_WIDTH__;
			$styles .= "#label_" . $field_name . " { top: " . $top_label . "px; left: " . $left . "px; width: 140px; padding: 0; }\n";
			$styles .= "#button_" . $field_name . " { top: " . $top . "px; left: " . $left_button . "px; width: " . __INPUT_WIDTH__ . "px; padding: 0; }\n";
			$styles .= "#" . $field_name . "Window { z-index: 4; position: absolute; top: " . $top . "px; left: " . $left . "px; width: 450px; border: 2px solid #999999; background-color: #E9EFF8; visibility: hidden; }\n";
			$fields_html .= '		<span id="label_' . $field_name . '" class="label">' . $field_name . ' : </span><input id="button_' . $field_name . '" type="button" class="button bouton" value="Editer la liste des propriétés" onclick="' . $field_name . '_win.Show();"/>' . "\n";
			$fields_html .= '		<div id="' . $field_name . 'Window"><div id="field_' . $field_name . '" class="window_bg"></div></div>';
			$js_code .= $field_name . "_win = new HN.Window();\n" .
				$field_name . "_win.setID('" . $field_name . "Window');\n" .
				$field_name . "_win.setTitleText('Editer la liste des propriétés de " . $field_name . "');\n" .
				$field_name . "_win.setMovable(true);\n" .
				$field_name . "_win.showValidButton(true);\n" .
				$field_name . "_win.setValidFct( function() { " . $field_name . "_win.Hide(); } );\n" .
				$field_name . "_win.setShadow(false);\n" .
				$field_name . "_win.Build();\n";
			break;
		case "text" :
			$top_label = $top + __LABEL_OFFSET__;
			$left_button = $left + __LABEL_WIDTH__;
			$styles .= "#label_" . $field_name . " { top: " . $top_label . "px; left: " . $left . "px; width: 140px; padding: 0; }\n";
			$styles .= "#button_" . $field_name . " { top: " . $top . "px; left: " . $left_button . "px; width: " . __INPUT_WIDTH__ . "px; padding: 0; }\n";
			$styles .= "#field_" . $field_name . " { width: 496px; height: 272px; }\n";
			$styles .= "#" . $field_name . "WindowShad { z-index: 3; position: absolute; top: " . ($top+5) . "px; left: " . ($left+5) . "px; width: 504px; height: 304px; background-color: #000000; visibility: hidden; filter: Alpha (opacity=50, finishopacity=50, style=1) -moz-opacity:.50; opacity:.50; }\n";
			$styles .= "#" . $field_name . "Window { z-index: 4; position: absolute; top: " . $top . "px; left: " . $left . "px; width: 500px; height: 300px; border: 2px solid #999999; background-color: #E9EFF8; visibility: hidden; }\n";
			$fields_html .= '		<span id="label_' . $field_name . '" class="label">' . $field_name . ' : </span><input id="button_' . $field_name . '" type="button" class="button bouton" value="Editer le champ ' . $field_name . '" onclick="' . $field_name . '_win.Show();"/>' . "\n";
			$fields_html .= '		<div id="' . $field_name . 'Window"><textarea id="field_' . $field_name. '"></textarea></div>';
			$js_code .= $field_name . "_win = new HN.Window();\n" .
				$field_name . "_win.setID('" . $field_name . "Window');\n" .
				$field_name . "_win.setTitleText('Edition du champ " . $field_name . " du produit');\n" .
				$field_name . "_win.setMovable(true);\n" .
				$field_name . "_win.showValidButton(true);\n" .
				$field_name . "_win.setValidFct( function() { " . $field_name . "_win.Hide(); } );\n" .
				$field_name . "_win.setShadow(true);\n" .
				$field_name . "_win.Build();\n";
			break;
		case "list" :
			$top_label = $top + __LABEL_OFFSET__;
			$left_input = $left + __LABEL_WIDTH__;
			$styles .= "#label_" . $field_name . " { top: " . $top_label . "px; left: " . $left . "px; }\n";
			$styles .= "#field_" . $field_name . " { top: " . $top . "px; left: " . $left_input . "px; width: " . __INPUT_WIDTH__ . "px; height: " . __INPUT_HEIGHT__ . "px; }\n";
			$fields_html .= '		<span id="label_' . $field_name . '" class="label">' . $field_name . " : </span>\n" .
				'		<select id="field_' . $field_name . '" class="field"/>' . "\n";
			$n = 0;
			while (isset($exp->compulsory_fields[$field_name]['option'.++$n]))
				$fields_html .= '			<option value="' . str_replace('"', '\"', $exp->compulsory_fields[$field_name]['option'.$n]). '">' . $exp->compulsory_fields[$field_name]['option'.$n] . '</option>' . "\n";
			$fields_html .= "</select>\n";
			break;
		case "cst" :
			break;
		default : break;
	}
	
	if ($nfield%2 == 0)
	{
		$left = __LEFT_MARGIN_2__;
	}
	elseif ($nfield%2 == 1)
	{
		$left = __LEFT_MARGIN_1__;
		$top += __LINE_HEIGHT__;
	}
	$nfield++;
}

$top_f_section = ((($nfield>>1) + ($nfield&1)) *__LINE_HEIGHT__ + __TOP_MARGIN__ + 20);
$styles .= "#ffield_section { position: absolute; top: " . $top_f_section . "px; left: 10px; font-size: 14px; font-weight: bold; width: 671px; padding: 2px 20px; border: 1px solid black; background-color: #F8FDFF; }\n";
define("__TOP_MARGIN_2__", $top_f_section + 25);

$fields_html .= '		<div id="ffield_section">Champs facultatifs</div>';

$nfield = 0;
$top = __TOP_MARGIN_2__;
$left = __LEFT_MARGIN_1__;
foreach ($exp->f_keys as $field_name)
{
	switch($exp->facultative_fields[$field_name]["type"])
	{
		case "edit" :
			$top_label = $top + __LABEL_OFFSET__;
			$left_input = $left + __LABEL_WIDTH__;
			$styles .= "#label_" . $field_name . " { top: " . $top_label . "px; left: " . $left . "px; }\n";
			$styles .= "#field_" . $field_name . " { top: " . $top . "px; left: " . $left_input . "px; width: " . __INPUT_WIDTH__ . "px; height: " . __INPUT_HEIGHT__ . "px; }\n";
			$fields_html .= '		<span id="label_' . $field_name . '" class="label">' . $field_name . ' : </span><input id="field_' . $field_name . '" type="text" maxlength="255" class="field"/>' . "\n";
			break;
		case "medit" :
			$top_label = $top + __LABEL_OFFSET__;
			$left_button = $left + __LABEL_WIDTH__;
			$styles .= "#label_" . $field_name . " { top: " . $top_label . "px; left: " . $left . "px; width: 140px; padding: 0; }\n";
			$styles .= "#button_" . $field_name . " { top: " . $top . "px; left: " . $left_button . "px; width: " . __INPUT_WIDTH__ . "px; padding: 0; }\n";
			$styles .= "#" . $field_name . "Window { z-index: 4; position: absolute; top: " . $top . "px; left: " . $left . "px; width: 450px; border: 2px solid #999999; background-color: #E9EFF8; visibility: hidden; }\n";
			$fields_html .= '		<span id="label_' . $field_name . '" class="label">' . $field_name . ' : </span><input id="button_' . $field_name . '" type="button" class="button bouton" value="Editer la liste des propriétés" onclick="' . $field_name . '_win.Show();"/>' . "\n";
			$fields_html .= '		<div id="' . $field_name . 'Window"><div id="field_' . $field_name . '" class="window_bg"></div></div>';
			$js_code .= $field_name . "_win = new HN.Window();\n" .
				$field_name . "_win.setID('" . $field_name . "Window');\n" .
				$field_name . "_win.setTitleText('Editer la liste des propriétés de " . $field_name . "');\n" .
				$field_name . "_win.setMovable(true);\n" .
				$field_name . "_win.showValidButton(true);\n" .
				$field_name . "_win.setValidFct( function() { " . $field_name . "_win.Hide(); } );\n" .
				$field_name . "_win.setShadow(false);\n" .
				$field_name . "_win.Build();\n";
			break;
		case "text" :
			$top_label = $top + __LABEL_OFFSET__;
			$left_button = $left + __LABEL_WIDTH__;
			$styles .= "#label_" . $field_name . " { top: " . $top_label . "px; left: " . $left . "px; width: 140px; padding: 0; }\n";
			$styles .= "#button_" . $field_name . " { top: " . $top . "px; left: " . $left_button . "px; width: " . __INPUT_WIDTH__ . "px; padding: 0; }\n";
			$styles .= "#field_" . $field_name . " { width: 496px; height: 272px; }\n";
			$styles .= "#" . $field_name . "WindowShad { z-index: 3; position: absolute; top: " . ($top+5) . "px; left: " . ($left+5) . "px; width: 504px; height: 304px; background-color: #000000; visibility: hidden; filter: Alpha (opacity=50, finishopacity=50, style=1) -moz-opacity:.50; opacity:.50; }\n";
			$styles .= "#" . $field_name . "Window { z-index: 4; position: absolute; top: " . $top . "px; left: " . $left . "px; width: 500px; height: 300px; border: 2px solid #999999; background-color: #E9EFF8; visibility: hidden; }\n";
			$fields_html .= '		<span id="label_' . $field_name . '" class="label">' . $field_name . ' : </span><input id="button_' . $field_name . '" type="button" class="button bouton" value="Editer le champ ' . $field_name . '" onclick="' . $field_name . '_win.Show();"/>' . "\n";
			$fields_html .= '		<div id="' . $field_name . 'Window"><textarea id="field_' . $field_name. '"></textarea></div>';
			$js_code .= $field_name . "_win = new HN.Window();\n" .
				$field_name . "_win.setID('" . $field_name . "Window');\n" .
				$field_name . "_win.setTitleText('Edition du champ " . $field_name . " du produit');\n" .
				$field_name . "_win.setMovable(true);\n" .
				$field_name . "_win.showValidButton(true);\n" .
				$field_name . "_win.setValidFct( function() { " . $field_name . "_win.Hide(); } );\n" .
				$field_name . "_win.setShadow(true);\n" .
				$field_name . "_win.Build();\n";
			break;
		case "list" :
			$top_label = $top + __LABEL_OFFSET__;
			$left_input = $left + __LABEL_WIDTH__;
			$styles .= "#label_" . $field_name . " { top: " . $top_label . "px; left: " . $left . "px; }\n";
			$styles .= "#field_" . $field_name . " { top: " . $top . "px; left: " . $left_input . "px; width: " . __INPUT_WIDTH__ . "px; height: " . __INPUT_HEIGHT__ . "px; }\n";
			$fields_html .= '		<span id="label_' . $field_name . '" class="label">' . $field_name . " : </span>\n" .
				'		<select id="field_' . $field_name . '" class="field"/>' . "\n";
			$n = 0;
			while (isset($exp->facultative_fields[$field_name]['option'.++$n]))
				$fields_html .= '			<option value="' . str_replace('"', '\"', $exp->facultative_fields[$field_name]['option'.$n]). '">' . $exp->facultative_fields[$field_name]['option'.$n] . '</option>' . "\n";
			$fields_html .= "</select>\n";
			break;
		case "cst" :
			break;
		default : break;
	}
	
	if ($nfield%2 == 0)
	{
		$left = __LEFT_MARGIN_2__;
	}
	elseif ($nfield%2 == 1)
	{
		$left = __LEFT_MARGIN_1__;
		$top += __LINE_HEIGHT__;
	}
	$nfield++;
}

$epw_height = __TOP_MARGIN_2__ + (($nfield>>1) + ($nfield&1)) * __LINE_HEIGHT__ + 10;

$styles .= "#EditProductWindowShad { z-index: 1; position: absolute; top: 35px; left: 55px; width: 759px; height: " . ($epw_height+49) . "px; background-color: #000000; visibility: hidden; filter: Alpha (opacity=50, finishopacity=50, style=1) -moz-opacity:.50; opacity:.50; }\n";
$styles .= "#EditProductWindow { z-index: 2; position: absolute; top: 30px; left: 50px; width: 755px; height: " . ($epw_height+45) . "px; border: 2px solid #999999; background-color: #E9EFF8; visibility: hidden; }\n";
$styles .= "#EditProductWindowBG { position: relative; width: 733px; height: " . $epw_height . "px; padding: 0; }\n";

print $styles;
?>

#ProductAlterError { position: absolute; top: 105px; left: 10px; width: 710px; }
#ReferencesSection { position: absolute; top: 140px; left: 10px; width: 858px; }
#button_finalize_product { top: 435px; left: 668px; width: 200px; }

#MultiFamilySearchWindowShad { z-index: 3; position: absolute; top: 65px; left: 80px; width: 788px; height: 432px; background-color: #000000; visibility: hidden; filter: Alpha (opacity=50, finishopacity=50, style=1) -moz-opacity:.50; opacity:.50; }
#MultiFamilySearchWindow { width: 784px; height: 428px; z-index: 4; position: absolute; top: 60px; left: 75px; border: 2px solid #999999; visibility: hidden; }

#multi_family_name { height: 22px; margin: 5px; padding: 5px; border: 3px solid #C6D6D8; font: 12px Arial, Helvetica, sans-serif; }
#mfn_label { font-weight: bold; letter-spacing: 1px; color: #272727; }
#mfn_input { width: 200px; }
#mfn_helper { font-size: 11px; font-style: italic; }
#mfn_checkbox { float: left; width: 180px; }

#FamilySearchWindowShad { z-index: 3; position: absolute; top: 65px; left: 80px; width: 788px; height: 404px; background-color: #000000; visibility: hidden; filter: Alpha (opacity=50, finishopacity=50, style=1) -moz-opacity:.50; opacity:.50; }
#FamilySearchWindow { z-index: 4; position: absolute; top: 60px; left: 75px; border: 2px solid #999999; visibility: hidden; }

#FinalizeExportWindowShad { z-index: 3; position: absolute; top: 305px; left: 80px; width: 454px; height: 89px; background-color: #000000; visibility: hidden; filter: Alpha (opacity=50, finishopacity=50, style=1) -moz-opacity:.50; opacity:.50; }
#FinalizeExportWindow { z-index: 4; position: absolute; top: 300px; left: 75px; width: 450px; height: 85px; border: 2px solid #999999; background-color: #E9EFF8; visibility: hidden; }
 
#Choose-adv { z-index: 3; width: 620px; position: absolute; top: 50px; left: 45px; }
.window-silver { padding: 5px; font: normal 11px Tahoma, Arial, Helvetica, sans-serif; }
.window-silver a { color: #000000; font-weight: normal; }
.window-silver a:hover { font-weight: normal; }

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

.search_menu { display: block; width: 606px; padding: 3px 6px; border-bottom: 1px solid #808080; cursor: default; }
.search_menu span { border: 1px solid #DEDCD6; padding: 2px 5px; outline: none; }
.search_menu span.over { border-color: #FFFFFF #808080 #808080 #FFFFFF; }
.search_menu span.down { border-color: #808080 #FFFFFF #FFFFFF #808080; }
.search_menu span.selected { border-color: #808080 #FFFFFF #FFFFFF #808080; }

.body { padding: 2px 4px; background-color: #DEDCD6; border-top: 1px solid #FFFFFF; clear: left}
.body .col-title { cursor: default; font-weight: bold; margin: 2px; }
.body .colg { float: left; width: 200px; margin-right: 5px; }
.body .colc { float: left; width: 200px; margin-right: 5px; }
.body .cold { float: left; width: 200px; }

.body .colg .list { position: relative; width: 194px; height: 298px; background-color: #FFFFFF; border: 2px inset #808080; margin: 0; padding: 1px; list-style-type: none; overflow: auto; }
.body .colg .list li { position: relative; height: 13px; cursor: default; white-space: nowrap; }
.body .colg .list li img { position: absolute; top: 1px; right: 2px; cursor: pointer; }
.body .colg .list li.over { background-color: #316AC5; color: #FFFFFF; }
.body .colg .list li.selected { background-color: #0C266C; color: #FFFFFF; }

.body .colc .slist { position: relative; width: 194px; height: 298px; background-color: #FFFFFF; border: 2px inset #808080; margin: 0; padding: 1px; list-style-type: none; overflow: auto; }
.body .colc .slist li { position: relative; cursor: default; white-space: nowrap; }
.body .colc .slist li img { position: absolute; top: 1px; right: 2px; cursor: pointer; }
.body .colc .slist li.over { background-color: #316AC5; color: #FFFFFF; }
.body .colc .slist li.selected { background-color: #0C266C; color: #FFFFFF; }

.body .cold .infos { position: relative; height: 290px; background-color: #FFFFFF; border: 2px inset #808080; padding: 5px; }
.body .cold .select_label { padding: 0 5px 0 20px; }
.body .cold input.button { top: 275px; left: 5px; width: 186px; }
.body .cold input.button1 { top: 250px }
.body .cold input.button2 { top: 275px }


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
var __IP_VALID__ = '<?php echo __IP_VALID__ ?>';
var __IP_FINALIZED__ = '<?php echo __IP_FINALIZED__ ?>';
var products_filter = <?php echo $exp->products_filter ?>;

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
<div id="EditProductWindow">
	<div id="EditProductWindowBG" class="window_bg">
		<input type="hidden" id="idTC"/>
		<div id="ProductAlterError" class="InfosError"><br/></div>
<?php echo $fields_html ?>
	</div>
</div>
<div id="FamilySearchWindow"></div>
<div class="window-silver" id="Choose-adv" style="display: none;">
	<div id="main_menu" class="tab_menu"></div>
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
					<div class="col-title" onmousedown="grab(document.getElementById('Choose-adv'))">Annonceurs sélectionnés</div>
					<ul class="slist" id="slistA"></ul>
				</div>
				<div class="cold">
					<div class="col-title" onmousedown="grab(document.getElementById('Choose-adv'))">Informations</div>
					<div class="infos">
						<div id="infosA"></div>
						<input type="button" class="button button1 bouton" value="Annuler" onclick="HideAdvertiserSearchWindow();"/>
						<input type="button" class="button button2 bouton" value="Valider" onclick="SetAdvertiserName(SAL);"/>
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
					<div class="col-title" onmousedown="grab(document.getElementById('Choose-adv'))">Fournisseurs sélectionnés</div>
					<ul class="slist" id="slistS"></ul>
				</div>
				<div class="cold">
					<div class="col-title" onmousedown="grab(document.getElementById('Choose-adv'))">Informations</div>
					<div class="infos">
						<div id="infosS"></div>
						<input type="button" class="button button1 bouton" value="Annuler" onclick="HideAdvertiserSearchWindow();"/>
						<input type="button" class="button button2 bouton " value="Valider" onclick="SetAdvertiserName(SSL);"/>
					</div>
				</div>
				<div class="zero"></div>
			</div>
		</div>
	</div>
</div>
<div id="ProductExplorerWindow">
	<div id="ProductExplorerLayers">
<?php
require('ProductExplorerFamilyLayer.php');
?>
		<div class="PELayer" id="ProductExplorerSearchLayer"></div>
	</div>
</div>
<div id="FinalizeExportWindow">
	<div id="FinalizeExportWindowBG" class="window_bg">
		Choisissez votre format d'export :
		<select id="ListExportFormats">
<?php
$ExportFormats = array(
	array("type" => "xml", "title" => "XML", "desc" => ""),
	array("type" => "csv", "title" => "csv (Séparateur: point-virgule)", "desc" => ""),
	array("type" => "txt", "title" => "texte (séparateur: tabulation)", "desc" => "")
);
foreach($ExportFormats as $format)
{
	print '			<option value="' . $format['type'] . '">' . $format['title'] . '</option>';
}
?>
		</select>
		<br/>
	</div>
</div>
<div id="PerfReqLabelProducts" class="PerfReqLabel"><br/></div>
<div id="ProductGetError" class="InfosError">
<?php
if (isset($_GET['error']))
{
	switch($_GET['error'])
	{
		case "permissions": print "Vous n'avez pas les droits adéquats pour réaliser cette opération<br/>"; break;
    case "loadproducts" : print "Erreur Fatale lors de la finalisation de l'export : Impossible de charger les produits<br/>"; break;
		default : print "<br/>"; break;
	}
}
else print "<br/>";
?>
</div>
<div id="ProductsTableSwitcher1"></div>
<div class="zero"></div>
<div id="ProductsTable"></div>
<div id="ProductsTableSwitcher2"></div>
<div class="zero"></div>
<div>
	<input type="button" class="bouton" value="Intégrer tous les produits d'une famille" onclick="SelectAllProductsFromAFamily();"/>
	<input type="button" class="bouton" value="Intégrer tous les produits d'un annonceur ou fournisseur" onclick="ShowAdvertiserSearchWindow();"/>
	<input type="button" class="bouton" value="Ajouter un produit" onclick="pew.Show();"/><br />
	<input type="button" class="bouton" value="Finaliser l'export" onclick="few.Show();"/>
</div>
<br />
<script type="text/javascript">
<?php echo $js_code ?>
// PRODUCTS //
function showProductExplorerWindow()
{
	document.getElementById('ProductExplorerWindowShad').style.visibility = 'inline';
	document.getElementById('ProductExplorerWindow').style.visibility = 'inline';
	document.getElementById('ProductExplorerWindowShad').style.height = document.getElementById('ProductExplorerWindow').offsetHeight + 'px';
}

function hideProductExplorerWindow()
{
	document.getElementById('ProductExplorerWindowShad').style.display = 'hidden';
	document.getElementById('ProductExplorerWindow').style.display = 'hidden';
}

// ADVERTISERS //
function ShowAdvertiserSearchWindow() { document.getElementById('Choose-adv').style.display = 'block'; }
function HideAdvertiserSearchWindow() { document.getElementById('Choose-adv').style.display = 'none'; }
function SetAdvertiserName(ElementList)
{
	if (ElementList)
	{
		var eidlist = "";
		for (eid in ElementList.ei) eidlist += eid + "-";
		eidlist = eidlist.substr(0,eidlist.length-1);
		
		//alert("ProductsManagment.php?action=addAdvertiser&id=" + <?php echo $exp->id ?> + "&advIDlist=" + eidlist);
		ProductsAJAXHandle.QueryA("ProductsManagment.php?action=addAdvertisers&id=" + <?php echo $exp->id ?> + "&advIDlist=" + eidlist);
		HideAdvertiserSearchWindow();
	}
}

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

/* Tab Advertisers
*******************************************************************************/
var ALH = new AJAXHandle(function(xhr) { ElementListProcessResponse(AL, xhr); }, "PerfReqImports");
var AIH = new AJAXHandle(function(xhr) { InfosProcessResponse("infosA", xhr); }, "PerfReqImports");

var SearchMenuA = new SearchMenu("search_menuA", {
	"0-9" : function () {
			ALH.QueryA('AdvertisersSearch.php?' + __SID__ + '&AdvertisersSearchText=' + escape('[_0-9]'));
			document.getElementById('infosA').innerHTML = "Choisissez un annonceur";
	},
	"[A-Z]" : function (letter) {
			ALH.QueryA('AdvertisersSearch.php?' + __SID__ + '&AdvertisersSearchText=' + escape(letter));
			document.getElementById('infosA').innerHTML = "Choisissez un annonceur";
	}
}, "span");

// Advertisers List
var AL = new ElementList("listA", "li", {
	onmousover: function () { if (AL.SelectedObject != this) this.className = 'over'; },
	onmouseout:function () { if (AL.SelectedObject != this) this.className = ''; },
	onmousedown: function () {
		if (AL.SelectedObject) AL.SelectedObject.className = '';
		AL.SelectedObject = this;
		this.className = 'selected';
		AIH.QueryA('AdvertisersInfos.php?' + __SID__ + '&id=' + this.ElementID);
	},
	ondblclick: function () {
		var imgd = document.createElement("img");
		imgd.src = "cross_12x12.png";
		imgd.alt = "delete";
		imgd.onclick = function() { SAL.Delete(this.parentNode); SAL.Draw(); }
		SAL.Add(this.ElementID, this.firstChild.nodeValue, imgd);
		SAL.Draw();
	}
});

// Selected Advertisers List
var SAL = new ElementList("slistA", "li", {
	onmousover: function () { if (SAL.SelectedObject != this) this.className = 'over'; },
	onmouseout:function () { if (SAL.SelectedObject != this) this.className = ''; },
	onmousedown: function () {
		if (SAL.SelectedObject) SAL.SelectedObject.className = '';
		SAL.SelectedObject = this;
		this.className = 'selected';
		AIH.QueryA('AdvertisersInfos.php?' + __SID__ + '&id=' + this.ElementID);
	},
	ondblclick: function () {
		SAL.Delete(this);
		SAL.Draw();
	}
});
SearchMenuA.Draw();

AL.selectList = SAL;

/* Tab Suppliers
*******************************************************************************/
var SLH = new AJAXHandle(function(xhr) { ElementListProcessResponse(SL, xhr); }, "PerfReqImports");
var SIH = new AJAXHandle(function(xhr) { InfosProcessResponse("infosS", xhr); }, "PerfReqImports");

var SearchMenuS = new SearchMenu("search_menuS", {
	"0-9" : function () {
			SLH.QueryA('SuppliersSearch.php?' + __SID__ + '&SuppliersSearchText=' + escape('[_0-9]'));
			document.getElementById('infosS').innerHTML = "Choisissez un fournisseur";
	},
	"[A-Z]" : function (letter) {
			SLH.QueryA('SuppliersSearch.php?' + __SID__ + '&SuppliersSearchText=' + escape(letter));
			document.getElementById('infosS').innerHTML = "Choisissez un fournisseur";
	}
}, "span");

// Suppliers List
var SL = new ElementList("listS", "li", {
	onmousover: function () { if (SL.SelectedObject != this) this.className = 'over'; },
	onmouseout: function () { if (SL.SelectedObject != this) this.className = ''; },
	onmousedown: function () {
		if (SL.SelectedObject) SL.SelectedObject.className = '';
		SL.SelectedObject = this;
		this.className = 'selected';
		SIH.QueryA('AdvertisersInfos.php?' + __SID__ + '&id=' + this.ElementID);
	},
	ondblclick: function () {
		var imgd = document.createElement("img");
		imgd.src = "cross_12x12.png";
		imgd.alt = "delete";
		imgd.onclick = function() { SSL.Delete(this.parentNode); SSL.Draw(); }
		SSL.Add(this.ElementID, this.firstChild.nodeValue, imgd);
		SSL.Draw();
	}
});

// Selected Suppliers List
var SSL = new ElementList("slistS", "li", {
	onmousover: function () { if (SSL.SelectedObject != this) this.className = 'over'; },
	onmouseout:function () { if (SSL.SelectedObject != this) this.className = ''; },
	onmousedown: function () {
		if (SAL.SelectedObject) SSL.SelectedObject.className = '';
		SSL.SelectedObject = this;
		this.className = 'selected';
		AIH.QueryA('AdvertisersInfos.php?' + __SID__ + '&id=' + this.ElementID);
	},
	ondblclick: function () {
		SSL.Delete(this);
		SSL.Draw();
	}
});
SearchMenuS.Draw();

SL.selectList = SSL;
/* AJAX Response Functions
*******************************************************************************/
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
				var imga = document.createElement("img");
				imga.src = "arrow_right_14x12.png";
				imga.alt = "add";
				imga.onclick = function() {
					var imgd = document.createElement("img");
					imgd.src = "cross_12x12.png";
					imgd.alt = "delete";
					imgd.onclick = function() { el.selectList.Delete(this.parentNode); el.selectList.Draw(); }
					el.selectList.Add(this.parentNode.ElementID, this.parentNode.firstChild.nodeValue, imgd);
					el.selectList.Draw();
				}
				el.Add(outputID[0], outputID[1], imga);
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

/* Families
*******************************************************************************/
fb = new HN.FamiliesBrowser();
fb.setID("FamilySearchWindow");

function SelectAllProductsFromAFamily()
{
	fsw.Show();
	fb.Build();
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
	ProductsAJAXHandle.QueryA("ProductsManagment.php?action=get&id=" + <?php echo $exp->id ?> + "&idTC=" + parseInt(pt.getCell(row,0).textvalue));
}

function DelProduct(row) {
	ProductsAJAXHandle.QueryA("ProductsManagment.php?action=delete&id=" + <?php echo $exp->id ?> + "&idTC=" + parseInt(pt.getCell(row,0).textvalue));
}

function AlterProduct() {
	
	var as = "action=alter";
	as += "&id=<?php echo $exp->id ?>";
	as += "&idTC=" + parseInt(document.getElementById("idTC").value);
	keys2update = [<?php
	$nk = 0;
	$cf_keys = array_merge($exp->c_keys, $exp->f_keys);
	foreach ($cf_keys as $key)
	{
		if ($nk > 0) print ",";
		print '"' . $key . '"';
		$nk++;
	}
	?>];
	for (var i = 0; i < keys2update.length; i++)
	{
		var subfields = document.getElementById("field_"+keys2update[i]).getElementsByTagName("input");
		if (subfields.length > 0)
		{
			for (var j = 0; j < subfields.length; j++)
			{
				field_pre_length = "field_".length;
				var field_name = subfields[j].id.substring(field_pre_length, subfields[j].id.length);
				as += "&" + field_name + "=" + escape(document.getElementById(subfields[j].id).value);
			}
		}
		else as += "&" + keys2update[i] + "=" + escape(document.getElementById("field_"+keys2update[i]).value);
	}
	//alert("ProductsManagment.php?" + as);
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
				for (var i = 1; i < outputs.length-1; i++)
				{
					var outputID = outputs[i].split(__OUTPUTID_SEPARATOR__);
					switch (outputID[0])
					{
						case "idTC" : 
							document.getElementById("idTC").value = outputID[1];
							if (document.getElementById("field_idTC")) document.getElementById("field_idTC").value = outputID[1];
							break;
						default :
							if (outputID.length == 2)
								document.getElementById("field_"+outputID[0]).value = outputID[1];
							else if (outputID.length >= 3 && ((outputID.length%2) == 1))
							{
								var custom_fields_html = "";
								for (var j = 1; j < outputID.length-1; j+=2)
								{
									custom_fields_html += '<div id="label_'+outputID[j]+'" class="label_dynamic">' + outputID[j] + ' : </div><input id="field_'+outputID[j]+'" value="'+outputID[j+1]+'" type="text" maxlength="255" class="field_dynamic"/>' + '<div class="zero"></div>' + "\n";
								}
								document.getElementById("field_"+outputID[0]).innerHTML = custom_fields_html;
							}
							break;
					}
				}
				epw.Show();
				document.getElementById("ProductGetError").innerHTML = "<br/>";
				break;
			
			case "alter" :
				if (outputs[1] == "OK") document.location.href = "export.php?id=<?php echo $exp->id ?>";
				break;
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
								case __IP_VALID__ : rowData[9] = "valid"; break;
								case __IP_FINALIZED__ : rowData[9] = "finalized"; break;
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
				if (outputs[1] == "OK") document.location.href = "export.php?id=<?php echo $exp->id ?>";
				break;
			
			case "multi-alter" :
				break;
				
			case "multi-finalize" :
				for (var j = 1; j < outputs.length-1; j++)
				{
					var row = pt.getRowByIndex(parseInt(outputs[j]), 0);
					pt.AlterCell(row, 9, "finalized");
				}
				document.getElementById("ProductGetError").innerHTML = "<br/>";
				break;
			
			case "addProduct" :
				if (outputs[1] == "OK") document.location.href = "export.php?id=<?php echo $exp->id ?>";
				//document.getElementById("ProductGetError").innerHTML = "<br/>";
				break;
				
			case "addFamily" :
				if (outputs[1] == "OK") document.location.href = "export.php?id=<?php echo $exp->id ?>";
				//document.getElementById("ProductGetError").innerHTML = "<br/>";
				break;
				
			case "addAdvertiser" :
			case "addAdvertisers" :
				if (outputs[1] == "OK") document.location.href = "export.php?id=<?php echo $exp->id ?>";
				//document.getElementById("ProductGetError").innerHTML = "<br/>";
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

pew = new HN.Window();
pew.setID("ProductExplorerWindow");
pew.setTitleText("Sélectionner un produit");
pew.setMovable(true);
pew.showCancelButton(true);
pew.showValidButton(true);
pew.setValidFct( function() {
	var ProductID, FamilyID;
	if ((ProductID = document.getElementById("FamiliesExplore_ProductID")) && (FamilyID = document.getElementById("FamiliesExplore_FamilyID")))
	{
		//alert("ProductsManagment.php?action=addProduct&id=" + <?php echo $exp->id ?> + "&idProduct=" + ProductID.value + "&idFamily=" + FamilyID.value);
		ProductsAJAXHandle.QueryA("ProductsManagment.php?action=addProduct&id=" + <?php echo $exp->id ?> + "&idProduct=" + ProductID.value + "&idFamily=" + FamilyID.value);
		pew.Hide();
	}
} );
pew.setShadow(true);
pew.Build();

fsw = new HN.Window();
fsw.setID("FamilySearchWindow");
fsw.setTitleText("Choisir une famille de produits à exporter");
fsw.setMovable(true);
fsw.showCancelButton(true);
fsw.showValidButton(true);
fsw.setValidFct( function() {
	if (fb.getCurFamID() != 0)
	{
		//alert("ProductsManagment.php?action=addFamily&id=" + <?php echo $exp->id ?> + "&idFamily=" + fb.getCurFamID());
		ProductsAJAXHandle.QueryA("ProductsManagment.php?action=addFamily&id=" + <?php echo $exp->id ?> + "&idFamily=" + fb.getCurFamID());
		fsw.Hide();
	}
} );
fsw.setShadow(true);
fsw.Build();

few = new HN.Window();
few.setID("FinalizeExportWindow");
few.setTitleText("Format de finalisation de l'export");
few.setMovable(true);
few.showCancelButton(true);
few.showValidButton(true);
few.setValidFct( function() {
	var lef_options = document.getElementById("ListExportFormats").options;
	document.location.href='make_export.php?id=<?php echo $exp->id ?>&flow_type=' + lef_options[lef_options.selectedIndex].value;
	few.Hide();
} );
few.setShadow(true);
few.Build();


//Product :	%id% name family %ref_name% fastdesc descc descd delai_livraison image 'contrainteProduit' 'tauxRemise' 'alias' 'keywords'
//Ref :		%idTC% refSupplier label mixed_headers price price2 unite marge idTVA place

// PRODUCTS //
pt = new HN.JSTable();
pt.setID("ProductsTable");
pt.setClass("CommonTable");

pt.setHeaders(["idTC"<?php
$nbckeys = count($exp->c_keys);
for ($i = 0; $i < $nbckeys; $i++) print ', "' . str_replace('"', '\"', $exp->c_keys[$i]) . '"';
?>]);
pt.setColIndex([1]);
pt.setInitialData([
<?php
if ($exp->exist)
{
	if ($exp->LoadAllProducts())
	{
		$ids = array_keys($exp->products); $nb_ids = count($ids);
		for ($i = 0; $i < $nb_ids; $i++)
		{
			print '	[' . $ids[$i];
			$nb_ckeys = count($exp->c_keys);
			for ($j = 0; $j < $nb_ckeys; $j++)
			{
				if ($exp->compulsory_fields[$exp->c_keys[$j]]["filter_type"] == "num")
					print $exp->products[$ids[$i]][$exp->c_keys[$j]];
				else
				{
					$array_search = array("/<br[[:space:]]*\/?>/i", "/\"/", "/\r\n/", "/\r/", "/\n/");
					$array_replace = array(' ', '\"', "", "", "");
					print ', "' . preg_replace($array_search, $array_replace, substr($exp->products[$ids[$i]][$exp->c_keys[$j]], 0, 30)) . '"';
				}
			}
			print ']' . ($i < ($nb_ids-1) ? "," : "") . "\n";
		}
	}
}
?>
]);
pt.setColumnCount(<?php echo ($nbckeys+1) ?>);
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
	"edit" : {"element" : "div", "attributes" : { "onclick" : function() { ShowProduct(this.parentNode.parentNode); this.parentNode.parentNode.onmousedown(); } } },
	"del" : {"element" : "div", "attributes" : { "onclick" : function() { DelProduct(this.parentNode.parentNode); this.parentNode.parentNode.onmousedown(); } } }
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

</script>
</div>
<?php

require(ADMIN . 'tail.php');

?>
