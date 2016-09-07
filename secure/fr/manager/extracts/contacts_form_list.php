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
$workbook->send(date("Y-m-d-h-i-s")." Extrait formulaire contact générique.xls");
$worksheet = $workbook->addWorksheet('Liste annonceurs');

// Headers
$col_headers = array(
  "ID" => "id",
  "Date" => "timestamp",
  "Nom" => "nom",
  "Prénom" => "prenom",
  "Fonction" => "fonction",
  "Nom société" => "societe",
  "Nombre salariés" => "salaries",
  "Secteur d'activité" => "secteur",
  "NAF" => "naf",
  "SIRET" => "siret",
  "Adresse" => "adresse",
  "Complément" => "cadresse",
  "CP" => "cp",
  "Ville" => "ville",
  "Pays" => "pays",
  "Tel" => "tel",
  "Fax" => "fax",
  "Email" => "email",
  "Précision" => "precisions",
  "Source" => "source",
  "Object" => "objet",
  "Message" => "message"
);

$l = $c = 0;
$chi = array_flip($col_headers); // Cols Headers Index
foreach ($chi as $col_header_name)
  $worksheet->write($l, $c++, $col_header_name);
$l++;

$res = $db->query("
  SELECT ".implode(",",array_values($col_headers))."
  FROM contacts_form
  ORDER BY timestamp desc", __FILE__, __LINE__);

while ($cols = $db->fetchAssoc($res)) {
  $cols["timestamp"] = date("d/m/Y H:i:s", $cols["timestamp"]);
  $c = 0;
  foreach($col_headers as $col_header) {
    $worksheet->write($l, $c++, $cols[$col_header]);
  }
  $l++;
}

$workbook->close();

?>
