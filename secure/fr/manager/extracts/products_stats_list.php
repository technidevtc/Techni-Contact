<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$user = new BOUser();

if(!$user->login()) {
	header("Location: ".ADMIN_URL."login.html");
	exit();
}

$db = DBHandle::get_instance();

require_once("Spreadsheet/Excel/Writer.php");

$workbook = new Spreadsheet_Excel_Writer();
$workbook->send(date("Y-m-d-h-i-s")." Extrait 20000 meilleurs taux transfo produits.xls");
$worksheet = $workbook->addWorksheet('Liste taux transfo produits');

// Headers
$col_headers = array(
  "ID" => "id",
  "Nom" => "name",
  "Taux transfo" => "hits2leadsorders",
  "Nb Lead" => "leads",
  "Nb commandes" => "orders",
  "Famille 1" => "cat1_name",
  "Famille 2" => "cat2_name",
  "Famille 3" => "cat3_name"
);

$l = $c = 0;
$chi = array_flip($col_headers); // Cols Headers Index
foreach ($chi as $col_header_name)
  $worksheet->write($l, $c++, $col_header_name);
$l++;

$res = $db->query("
  SELECT
    pfr.id,
    pfr.name,
    IF(ps.hits!=0,(ps.orders+ps.leads)/ps.hits,0) AS hits2leadsorders,
    ps.leads,
    ps.orders,
    ffr1.name AS cat1_name,
    ffr2.name AS cat2_name,
    ffr3.name AS cat3_name
  FROM products_fr pfr
  INNER JOIN products_families pf ON pfr.id = pf.idProduct
  INNER JOIN families_fr ffr3 ON ffr3.id = pf.idFamily
  INNER JOIN families f3 ON f3.id = ffr3.id
  INNER JOIN families_fr ffr2 ON ffr2.id = f3.idParent
  INNER JOIN families f2 ON f2.id = ffr2.id
  INNER JOIN families_fr ffr1 ON ffr1.id = f2.idParent
  INNER JOIN advertisers a ON pfr.idAdvertiser = a.id AND a.actif = 1
  INNER JOIN products_stats ps ON pfr.id = ps.id
  WHERE pfr.active = 1
  ORDER BY hits2leadsorders DESC
  LIMIT 0,20000", __FILE__, __LINE__);

while ($cols = $db->fetchAssoc($res)) {
  $c = 0;
  foreach($col_headers as $col_header) {
    $worksheet->write($l, $c++, $cols[$col_header]);
  }
  $l++;
}

$workbook->close();
