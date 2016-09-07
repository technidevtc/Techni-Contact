<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$user = new BOUser();

if(!$user->login() || !$user->get_permissions()->has("m-prod--sm-categories","r") || !isset($_GET["cat3Id"]) || !is_numeric($_GET["cat3Id"]))
	exit();

$cat3Id = $_GET["cat3Id"];

$db = DBHandle::get_instance();

// category tree
$res = $db->query("
  SELECT
    ffr1.name AS cat1_name,
    ffr2.name AS cat2_name,
    ffr3.name AS cat3_name
  FROM families_fr ffr3
  INNER JOIN families f3 ON f3.id = ffr3.id
  INNER JOIN families_fr ffr2 ON ffr2.id = f3.idParent
  INNER JOIN families f2 ON f2.id = ffr2.id
  INNER JOIN families_fr ffr1 ON ffr1.id = f2.idParent
  WHERE ffr3.id = ".$cat3Id, __FILE__, __LINE__);
list($cat1_name, $cat2_name, $cat3_name) = $db->fetch($res);

// attributes of this category
$res = $db->query("
  SELECT ra.name
  FROM ref_attributes ra
  WHERE ra.categoryId = ".$cat3Id, __FILE__, __LINE__);
while (list($attr) = $db->fetch($res))
  $attrList[] = trim($attr);

require_once("Spreadsheet/Excel/Writer.php");
$workbook = new Spreadsheet_Excel_Writer();
$workbook->send("Extrait Produits Famille ".$cat3_name.".xls");

// Creating a worksheet
$worksheet = $workbook->addWorksheet('Liste des Produits');

// Headers
$col_headers = array_merge(array(
  "ID fiche",
  "Titre",
  "Liste des mots clé",
  "Famille 1",
  "Famille 2",
  "Famille 3",
  "Nom partenaire",
  "Type partenaire",
  "Description produit",
  "Description technique",
  "Ref TC",
  "Libellé ligne"
), $attrList, array("Ref fourniseur"));

$l = $c = 0;
foreach ($col_headers as $col_header)
  $worksheet->write($l, $c++, $col_header);
$chi = array_flip($col_headers); // Cols Headers Index
$chc = $c;
$l++;

$res = $db->query("
  SELECT
    p.id,
    pfr.name,
    pfr.alias,
    a.nom1 AS adv_name,
    a.category AS adv_cat,
    pfr.descc,
    pfr.descd,
    rc.id AS idtc,
    rc.label,
    rcols.content as attributes_headers,
    rc.content as attributes,
    rc.refSupplier
  FROM products p
  INNER JOIN products_fr pfr ON p.id = pfr.id AND pfr.active = 1
  INNER JOIN products_stats ps ON p.id = ps.id
  INNER JOIN products_families pf ON p.id = pf.idProduct AND pf.idFamily = ".$cat3Id."
  INNER JOIN advertisers a ON p.idAdvertiser = a.id AND a.actif = 1
  LEFT JOIN references_cols rcols ON rcols.idProduct = p.id
  LEFT JOIN references_content rc ON rc.idProduct = p.id AND rc.deleted = 0
  ORDER BY p.id, rc.classement", __FILE__, __LINE__);

while ($pdt_line = $db->fetchAssoc($res)) {
  $pdt_line["adv_cat"] = $adv_cat_list[$pdt_line["adv_cat"]]["name"];
  if (isset($pdt_line["idtc"])) {
    $attrHeaders = array_slice(mb_unserialize($pdt_line["attributes_headers"]), 3, -6);
    $attr = mb_unserialize($pdt_line["attributes"]);
  }
  else {
    $attrHeaders = $attr = array();
  }

  $worksheet->write($l, 0, $pdt_line["id"]);
  $worksheet->write($l, 1, preg_replace("/^\@/"," @",$pdt_line["name"]));
  $worksheet->write($l, 2, preg_replace("/^\@/"," @",$pdt_line["alias"]));
  $worksheet->write($l, 3, $cat1_name);
  $worksheet->write($l, 4, $cat2_name);
  $worksheet->write($l, 5, $cat3_name);
  $worksheet->write($l, 6, preg_replace("/^\@/"," @",$pdt_line["adv_name"]));
  $worksheet->write($l, 7, $pdt_line["adv_cat"]);
  $worksheet->write($l, 8, preg_replace("/^\@/"," @",$pdt_line["descc"]));
  $worksheet->write($l, 9, preg_replace("/^\@/"," @",$pdt_line["descd"]));
  $worksheet->write($l, 10, $pdt_line["idtc"]);
  $worksheet->write($l, 11, preg_replace("/^\@/"," @",$pdt_line["label"]));
  for ($ai=0, $al=count($attr); $ai<$al; $ai++)
    if (isset($chi[$attrHeaders[$ai]]))
      $worksheet->write($l, $chi[$attrHeaders[$ai]], $attr[$ai]);
  $worksheet->write($l, $chc-1, preg_replace("/^\@/"," @",$pdt_line["refSupplier"]));

  $l++;
}

// Let's send the file
$workbook->close();
