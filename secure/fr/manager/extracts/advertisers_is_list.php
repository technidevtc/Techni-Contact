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
$workbook->send(date("Y-m-d-h-i-s")." Extrait liste annonceurs avec leur type de facturation.xls");
$worksheet = $workbook->addWorksheet('Liste annonceurs');

// Headers
$col_headers = array(
  "ID" => "adv_id",
  "Nom" => "adv_name",
  "Type" => "adv_category",
  "Email" => "adv_email",
  "Type facturation" => "adv_is.type",
  "Coût" => "adv_is.cost",
  "Nb leads max" => "adv_is.nb_leads_max",
  "Périodicité" => "adv_is.periodicity",
  "Dernier réglage le" => "adv_is.date"
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
  WHERE a.category != '".__ADV_CAT_SUPPLIER__."' AND a.deleted != 1", __FILE__, __LINE__);

while ($cols = $db->fetchAssoc($res)) {
  $cols["adv_category"] = $adv_cat_list[$cols["adv_category"]]["name"];
  $cols["adv_is"] = !empty($cols["adv_is"]) ? mb_unserialize($cols["adv_is"]) : array();
  $is = $cols["adv_is"][0];
  
  $c = 0;
  foreach ($col_headers as $col_header) {
    if (strpos($col_header, 'adv_is') !== 0) {
      if ($col_header === 'adv_name')
        $worksheet->write($l, $c++, utf8_decode($cols[$col_header]));
      else
        $worksheet->write($l, $c++, $cols[$col_header]);
    }
  }
  
  switch ($is['type']) {
    case 'lead':
      $type = "au lead";
      $cost = (float)$is['fields']->lead_unit_cost;
      $maxLeads = '';
      $periodicity = '';
      break;
    case 'forfeit':
      $type = "au forfait";
      $cost = (float)$is['fields']->forfeit_amount;
      $maxLeads = '';
      $periodicity = $is['fields']->forfeit_periodicity === 'month' ? 'par mois' : 'par année';
      break;
    case 'budget':
      $type = "au budget";
      $cost = (float)$is['fields']->budget_unit_cost;
      $maxLeads = $is['fields']->budget_max_leads;
      $periodicity = $is['fields']->budget_capping_periodicity === 'month' ? 'par mois' : 'par année';
      break;
    default:
      $type = $cost = $maxLeads = $periodicity = '';
  }
  $date = $type !== '' ? date('Y-m-d', $is['date']) : '';
  
  $worksheet->write($l, $c++, $type);
  $worksheet->write($l, $c++, $cost);
  $worksheet->write($l, $c++, $maxLeads);
  $worksheet->write($l, $c++, $periodicity);
  $worksheet->write($l, $c++, $date);
  
  $l++;
}

$workbook->close();