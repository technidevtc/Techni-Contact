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
$workbook->send(date("Y-m-d-h-i-s")." Extrait liste des partenaires.xls");
$worksheet = $workbook->addWorksheet('Liste des partenaires');
// Headers
$col_headers = array(
  "ID" => "adv_id",
  "Nom" => "adv_name",
  "Type" => "adv_category",
  "Email" => "adv_email",
  "Email commercial" => "adv_com_email",
  "Nombre produits" => "adv_nb_pdt"
);

$l = $c = 0;
$chi = array_flip($col_headers); // Cols Headers Index
foreach ($chi as $col_header_name)
  $worksheet->write($l, $c++, $col_header_name);
$l++;

$res = $db->query("
  SELECT
    a.id AS adv_id, a.nom1 AS adv_name, a.category AS adv_category, a.email AS adv_email, bou.email as adv_com_email,
    count(p.id) AS adv_nb_pdt
  FROM advertisers a
  INNER JOIN bo_users bou ON bou.id = a.idCommercial
  INNER JOIN products p ON a.id = p.idAdvertiser
  INNER JOIN (SELECT DISTINCT idProduct FROM products_families) pf ON p.id = pf.idProduct
  WHERE a.deleted != 1
  GROUP BY a.id");

while ($cols = $db->fetchAssoc($res)) {
  $cols["adv_category"] = $adv_cat_list[$cols["adv_category"]]["name"];
  $c = 0;
  foreach($col_headers as $col_header) {
    $worksheet->write($l, $c++, $cols[$col_header]);
  }
  $l++;
}

$workbook->close();

?>
