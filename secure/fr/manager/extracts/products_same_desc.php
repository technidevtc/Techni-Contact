<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$user = new BOUser();

if(!$user->login()) {
	header("Location: ".ADMIN_URL."login.html");
	exit();
}

$db = DBHandle::get_instance();

$res = $db->query("CREATE TABLE IF NOT EXISTS tempProductsDescs (
`id` INT( 8 ) UNSIGNED NOT NULL DEFAULT '0',
`descc` VARCHAR( 255 ) NOT NULL,
`descc_md5` VARCHAR( 32 ) NOT NULL,
`descd` VARCHAR( 255 ) NOT NULL,
`descd_md5` VARCHAR( 32 ) NOT NULL,
INDEX ( `descc_md5` ),
INDEX ( `descd_md5` )
) ;");

$res1 = $db->query("SELECT pfr.id, descc, descd FROM products_fr pfr
LEFT JOIN advertisers a ON pfr.idAdvertiser = a.id
WHERE pfr.active = 1 AND pfr.deleted != 1 AND a.category = 1 AND a.actif = 1");

while ($row = $db->fetchAssoc($res1)){
  $descc = strip_tags($row['descc']);
  $descd = strip_tags($row['descd']);

  $db->query("INSERT INTO tempProductsDescs (id, descc, descc_md5, descd, descd_md5) VALUES ('".mysql_real_escape_string($row['id'])."', '".mysql_real_escape_string($row['descc'])."', '".md5(mysql_real_escape_string($descc))."', '".mysql_real_escape_string($row['descd'])."', '".md5(mysql_real_escape_string($descd))."')");
}

$res3 = $db->query("SELECT descc, descd FROM tempProductsDescs GROUP BY descc_md5, descd_md5 HAVING COUNT(id)>1");

$listProducts = '';
while ($cols = $db->fetchAssoc($res3)){

  $res4 = $db->query("SELECT id FROM tempProductsDescs WHERE descc = '".mysql_real_escape_string($cols['descc'])."' AND  descc = '".mysql_real_escape_string($cols['descc'])."'");
  while ($cols2 = $db->fetchAssoc($res4))
  $listProducts .= $listProducts == '' ? $cols2['id'] : ', '.$cols2['id'];
}

require_once("Spreadsheet/Excel/Writer.php");

$workbook = new Spreadsheet_Excel_Writer();
$workbook->send(date("Y-m-d-h-i-s")." Ensemble des produits présents dans 2 familles minimum.xls");
$worksheet = $workbook->addWorksheet('Liste des produits');
// Headers
$col_headers = array(
  "nom produit" => "name",
  "ID fiche" => "id",
  "nom partenaire" => "name_partner",
  "famille 3" => "nameFamily3",
  "description produit" => "short_descc",
  "description technique" => "short_descd"
);

$l = $c = 0;
$chi = array_flip($col_headers); // Cols Headers Index
foreach ($chi as $col_header_name)
  $worksheet->write($l, $c++, $col_header_name);
$l++;

if ($listProducts != ''){
  $res2 = $db->query("SELECT pf.id, pf.name, descc, descd, SUBSTRING(descc, 1, 100) as short_descc, SUBSTRING(descd, 1, 100) as short_descd, ffr.name as nameFamily3, a.nom1 as name_partner
  FROM products_fr pf
  LEFT JOIN products_families p ON p.idProduct = pf.id
  LEFT JOIN families_fr ffr ON p.idFamily = ffr.id
  LEFT JOIN advertisers a ON a.id = pf.idAdvertiser
  WHERE pf.id IN (
          ".$listProducts."
  ) ORDER BY descc, descd");
  
  while ($cols = $db->fetchAssoc($res2)) {
    $c = 0;
    foreach($col_headers as $col_header) {
      $worksheet->write($l, $c++, $cols[$col_header]);
    }
    $l++;
  }
}
$workbook->close();

$db->query("DROP TABLE tempProductsDescs");

?>
