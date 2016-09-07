<?php
/* DecoFinder */
/*
$_ckeys = array
(
	"Reference" =>			array("type" => "edit",		"source_type" => "UC",	"default" => "__PRODUCT_IDTC__",			"filter_type" => ""),
	"Image" =>				array("type" => "edit",		"source_type" => "UC",	"default" => "__PRODUCT_IMAGE_URL__",		"filter_type" => ""),
	"Type_Produit" =>		array("type" => "edit",		"source_type" => "UC",	"default" => "__PRODUCT_FAMILY_TREE__",		"filter_type" => "nb=1 sep=>")
);

$_fkeys = array(
	"Modele" =>				array("type" => "edit",		"source_type" => "UC",	"default" => "__PRODUCT_FASTDESC__",		"filter_type" => ""),
	"Collection" =>			array("type" => "edit",		"source_type" => "UC",	"default" => "",							"filter_type" => ""),
	"Annee_Creation" =>		array("type" => "edit",		"source_type" => "UC",	"default" => "__CURRENT_YEAR__",			"filter_type" => ""),
	"Designer" =>			array("type" => "edit",		"source_type" => "UC",	"default" => "",							"filter_type" => ""),
	"Materiau" =>			array("type" => "edit",		"source_type" => "UC",	"default" => "",							"filter_type" => ""),
	"Couleur" =>			array("type" => "edit",		"source_type" => "UC",	"default" => "",							"filter_type" => ""),
	"Style" =>				array("type" => "edit",		"source_type" => "UC",	"default" => "",							"filter_type" => ""),
	"Motif" =>				array("type" => "edit",		"source_type" => "UC",	"default" => "",							"filter_type" => ""),
	"Pays" =>				array("type" => "edit",		"source_type" => "UC",	"default" => "",							"filter_type" => ""),
	"Prix" =>				array("type" => "edit",		"source_type" => "UC",	"default" => "__PRODUCT_PRICE_TTC__",		"filter_type" => ""),
	"Date_Nouveaute" => 	array("type" => "edit",		"source_type" => "UC",	"default" => "",							"filter_type" => ""),
	"Date_Promotion" => 	array("type" => "edit",		"source_type" => "UC",	"default" => "",							"filter_type" => ""),
	"Prix_Promotion" => 	array("type" => "edit",		"source_type" => "UC",	"default" => "",							"filter_type" => ""),
	"Url_Page_Produit" =>	array("type" => "edit",		"source_type" => "UC",	"default" => "__PRODUCT_URL__",				"filter_type" => ""),
	"Description_FR" =>		array("type" => "edit",		"source_type" => "UC",	"default" => "",							"filter_type" => ""),
	"Description_GB" =>		array("type" => "edit",		"source_type" => "UC",	"default" => "",							"filter_type" => ""),
	"Description_DE" =>		array("type" => "edit",		"source_type" => "UC",	"default" => "",							"filter_type" => ""),
	"Description_ES" =>		array("type" => "edit",		"source_type" => "UC",	"default" => "",							"filter_type" => ""),
	"Description_IT" =>		array("type" => "edit",		"source_type" => "UC",	"default" => "",							"filter_type" => "")
);
*/

/* Shopping */
/*

$_ckeys = array
(
	"Référence_Produit" =>	array("type" => "edit",		"source_type" => "UC",	"default" => "__PRODUCT_IDTC__",			"filter_type" => ""),
	"UPC" =>				array("type" => "edit",		"source_type" => "UC",	"default" => "__PRODUCT_IDTC__",			"filter_type" => ""),
	"Fabricant" =>			array("type" => "edit",		"source_type" => "UI",	"default" => "Techni-Contact",				"filter_type" => ""),
	"Nom_Produit" =>		array("type" => "edit",		"source_type" => "UC",	"default" => "__PRODUCT_NAME__",			"filter_type" => ""),
	"Description_Produit" =>array("type" => "edit",		"source_type" => "UC",	"default" => "__PRODUCT_FASTDESC__",		"filter_type" => ""),
	"Prix" =>				array("type" => "edit",		"source_type" => "UC",	"default" => "__PRODUCT_PRICE_TTC__",		"filter_type" => ""),
	"Stock" =>				array("type" => "edit",		"source_type" => "UI",	"default" => "o",							"filter_type" => ""),
	"URL_Produit" =>		array("type" => "edit",		"source_type" => "UC",	"default" => "__PRODUCT_URL__",				"filter_type" => ""),
	"URL_Image" =>			array("type" => "edit",		"source_type" => "UC",	"default" => "__PRODUCT_IMAGE_URL__",		"filter_type" => ""),
	"Catégorie" =>			array("type" => "edit",		"source_type" => "UC",	"default" => "__PRODUCT_FAMILY_TREE__",		"filter_type" => "nb=3 sep=/"),
	"Frais_de_Port" =>		array("type" => "edit",		"source_type" => "UC",	"default" => "__PRODUCT_SHIP_FEE__",		"filter_type" => ""),
);

$_fkeys = array(
	"Description_Stock" =>	array("type" => "edit",		"source_type" => "UC",	"default" => "__PRODUCT_DELIVERY_TIME__",	"filter_type" => ""),
	"Poids_Article" =>		array("type" => "edit",		"source_type" => "UC",	"default" => "__PRODUCT_IDTC__",			"filter_type" => ""),
	"Ecotaxe" =>			array("type" => "edit",		"source_type" => "UC",	"default" => "__PRODUCT_IDTC__",			"filter_type" => "")
);
*/

print_r($_ckeys);
print "<br/>\n" . serialize($_ckeys) . "\n<br/>";

print_r($_fkeys);
print "<br/>\n" . serialize($_fkeys) . "\n<br/>";

exit();

?>