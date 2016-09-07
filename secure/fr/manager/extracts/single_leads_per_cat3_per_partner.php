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
$workbook->send(date("Y-m-d-h-i-s")." Extrait revenu lead par familles 3 et annonceur.xls");
$worksheet = $workbook->addWorksheet('Liste des demandes de Contact');
// Headers
$col_headers = array(
  "ID famille 1" => "cat1_id",
  "Nom famille 1" => "cat1_name",
  "ID famille 2" => "cat2_id",
  "Nom famille 2" => "cat2_name",
  "ID famille 3" => "cat3_id",
  "Nom famille 3" => "cat3_name",
  "ID partenaire" => "adv_id",
  "Nom partenaire" => "adv_name",
  "Type partenaire" => "adv_category",
  "Revenu par lead" => "adv_lead_income",
  "Nombre produits dans la famille 3" => "adv_pdt_count"
);

$l = $c = 0;
$chi = array_flip($col_headers); // Cols Headers Index
foreach ($chi as $col_header_name)
  $worksheet->write($l, $c++, $col_header_name);
$l++;

$res = $db->query("
  SELECT
    (SELECT idParent FROM families WHERE id = f.idParent) AS cat1_id,
    (SELECT name FROM families_fr WHERE id = (SELECT idParent FROM families WHERE id = f.idParent)) AS cat1_name,
    f.idParent AS cat2_id,
    (SELECT name FROM families_fr WHERE id = f.idParent) AS cat2_name,
    ffr.id AS cat3_id,
    ffr.name AS cat3_name,
    a.id AS adv_id, a.nom1 AS adv_name, a.category AS adv_category, a.is_fields AS adv_lead_income, count(p.id) AS adv_pdt_count
  FROM `families_fr` ffr
  INNER JOIN families f ON ffr.id = f.id
  INNER JOIN products_families pf ON ffr.id = pf.idFamily
  INNER JOIN products p ON pf.idProduct = p.id
  INNER JOIN advertisers a ON p.idAdvertiser = a.id
  GROUP BY ffr.id, a.id
  ORDER BY cat1_id, cat2_name, cat3_name ASC", __FILE__, __LINE__);

while ($cols = $db->fetchAssoc($res)) {
  $cols["adv_category"] = $adv_cat_list[$cols["adv_category"]]["name"];
  $cols["adv_lead_income"] = !empty($cols["adv_lead_income"]) ? mb_unserialize($cols["adv_lead_income"]) : 0;
  if (!empty($cols["adv_lead_income"])) {
    $is_cur = $cols["adv_lead_income"][0];
    if (!empty($is_cur)) {
      switch($is_cur["type"]) {
        case "lead":
          $cols["adv_lead_income"] = (float)$is_cur["fields"]->lead_unit_cost;
          break;
        case "budget":
          $cols["adv_lead_income"] = (float)$is_cur["fields"]->budget_unit_cost;
          break;
        case "forfeit":
          $cols["adv_lead_income"] = 0;
          break;
        default:
          $cols["adv_lead_income"] = 0;
          break;
      }
    }
  }
  $c = 0;
  foreach($col_headers as $col_header) {
    $worksheet->write($l, $c++, $cols[$col_header]);
  }
  $l++;
}

$workbook->close();

?>
