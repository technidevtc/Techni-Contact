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
$workbook->send(date("Y-m-d-h-i-s")." Ensemble des produits présents dans 2 familles minimum.xls");
$worksheet = $workbook->addWorksheet('Liste des produits');
// Headers
$col_headers = array(
  "nom produit" => "name",
  "ID fiche" => "idProduct",
  "Famille 1" => "nameFamily3",
  "Famille 2" => "nameFamily2",
  "Famille 3" => "nameFamily1"
);

$l = $c = 0;
$chi = array_flip($col_headers); // Cols Headers Index
foreach ($chi as $col_header_name)
  $worksheet->write($l, $c++, $col_header_name);
$l++;

$res = $db->query("SELECT idProduct FROM products_families GROUP BY idProduct  HAVING COUNT(idfamily) >1");

$listProducts = '';
while ($cols = $db->fetchAssoc($res))
  $listProducts .= $listProducts == '' ? $cols['idProduct'] : ', '.$cols['idProduct'];

if ($listProducts != ''){
  $res2 = $db->query("SELECT pf.idProduct, pfr.name, pf.idFamily AS idFamily3, f.idParent  AS idFamily2, (
          SELECT idParent AS idFamily1 FROM families WHERE id  = idFamily2
  ) AS idFamily1,(
	SELECT name
	FROM families_fr
	WHERE id = idFamily3
) AS nameFamily3
,(
	SELECT name
	FROM families_fr
	WHERE id = idFamily2
) AS nameFamily2
,(
	SELECT name
	FROM families_fr
	WHERE id = idFamily1
) AS nameFamily1
  FROM products_families pf
  LEFT JOIN families f ON f.id = pf.idFamily
  LEFT JOIN products_fr pfr ON pfr.id = pf.idProduct
  WHERE idProduct IN (
          ".$listProducts."
  ) AND pfr.deleted != 1;");

  while ($cols = $db->fetchAssoc($res2)) {
    $c = 0;
    foreach($col_headers as $col_header) {
      $worksheet->write($l, $c++, $cols[$col_header]);
    }
    $l++;
  }
}
$workbook->close();

?>
