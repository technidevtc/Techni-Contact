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
$workbook->send(date("Y-m-d-h-i-s")." Extrait liste annonceurs sans facturation personnalisee.xls");
$worksheet = $workbook->addWorksheet('Liste annonceurs');

// Headers
$col_headers = array(
  "ID" => "adv_id",
  "Nom" => "adv_name",
  "Type" => "adv_category",
  "Email" => "adv_email"
);

$l = $c = 0;
$chi = array_flip($col_headers); // Cols Headers Index
foreach ($chi as $col_header_name)
  $worksheet->write($l, $c++, $col_header_name);
$l++;

$res = $db->query("
  SELECT
    a.id AS adv_id, a.nom1 AS adv_name, a.category AS adv_category, a.email AS adv_email, is_fields AS adv_is
  FROM advertisers a
  WHERE a.category = '".__ADV_CAT_ADVERTISER__."' AND a.deleted != 1", __FILE__, __LINE__);

while ($cols = $db->fetchAssoc($res)) {
  $cols["adv_category"] = $adv_cat_list[$cols["adv_category"]]["name"];
  $cols["adv_is"] = !empty($cols["adv_is"]) ? mb_unserialize($cols["adv_is"]) : "";
  // there is a setting
  if (!empty($cols["adv_is"]) && !empty($cols["adv_is"][0]))
    continue;
  $c = 0;
  foreach($col_headers as $col_header) {
    $worksheet->write($l, $c++, $cols[$col_header]);
  }
  $l++;
}

$workbook->close();

?>
