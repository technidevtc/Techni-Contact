<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$user = new BOUser();

$cat3Id = filter_input(INPUT_GET, 'cat3Id', FILTER_VALIDATE_INT);

if (!$user->login() || !$user->get_permissions()->has('m-prod--sm-categories','r') || empty($cat3Id))
  exit();

// all the attributes of this category
$q = Doctrine_Query::create()
  ->select('a.name')
  ->from('Attribute a')
  ->innerJoin('a.product_attributes pa')
  ->innerJoin('pa.product p')
  ->innerJoin('p.product_fr pfr WITH pfr.active = 1 AND pfr.deleted = 0')
  ->innerJoin('p.advertiser adv WITH adv.actif = 1')
  ->innerJoin('p.families f')
  ->where('f.id = ?', $cat3Id);

$attrList = $q->fetchArray();
$attributes = [];
foreach ($attrList as $attr)
  $attributes[$attr['id']] = $attr['name'];

$os = fopen('php://temp', 'r+');

// Headers
$col_headers = [
  'id' => 'ID fiche',
  'name' => 'Titre',
  'alias' => 'Liste des mots clés',
  'fam1' => 'Famille 1',
  'fam2' => 'Famille 2',
  'fam3' => 'Famille 3',
  'adv_name' => 'Nom partenaire',
  'adv_cat' => 'Type partenaire',
  'descc' => 'Description produit',
  'descd' => 'Description technique',
  'idtc' => 'Ref TC',
  'label' => 'Libellé ligne',
] + $attributes + ['refSupplier' => 'Ref fourniseur'];

fputcsv($os, array_values($col_headers), ';', '"');

// col headers index
$chi = array_flip(array_keys($col_headers));

$q = Doctrine_Query::create()
  ->select('p.id, pfr.name AS name, pfr.alias AS alias, pfr.descc AS descc, pfr.descd AS descd,
            a.nom1 AS adv_name, a.category AS adv_cat,
            f1fr.name AS fam1, f2fr.name AS fam2, f3fr.name AS fam3,
            pa.attribute_id, pa.value,
            pra.id,
            rc.id AS idtc, rc.label, rc.refSupplier,
            rca.value,
            IF(0,rca.id,rcapa.attribute_id) AS attribute_id')
  ->from('Products p')
  ->innerJoin('p.product_fr pfr WITH pfr.active = 1 AND pfr.deleted = 0')
  ->innerJoin('p.advertiser a WITH a.actif = 1')
  ->innerJoin('p.families f3 WITH f3.id = ?', $cat3Id)
  ->innerJoin('f3.family_fr f3fr')
  ->innerJoin('f3.parent f2')
  ->innerJoin('f2.family_fr f2fr')
  ->innerJoin('f2.parent f1')
  ->innerJoin('f1.family_fr f1fr')
  ->leftJoin('p.product_attributes pa')
  ->leftJoin('pa.product_reference_attributes pra')
  ->leftJoin('p.references rc WITH rc.deleted = 0 AND p.price = \'ref\'')
  ->leftJoin('rc.attribute_values rca')
  ->leftJoin('rca.product_attribute rcapa')
  ->orderBy('pa.position');

$products = $q->fetchArray();

if (empty($products))
  exit();

$field2ignore = array_flip(['idtc']);
$refFields = ['idtc', 'label', 'refSupplier'];

// print_r($products);
// exit();

function writeLine($rowValues) {
  global $chi, $os;

  // don't forget to set custom cols with an empty value if they're not part of the reference
  foreach ($chi as $colIndex)
    if (!isset($rowValues[$colIndex]))
      $rowValues[$colIndex] = '';
  ksort($rowValues);
  fputcsv($os, $rowValues, ';', '"');
}

foreach($products as $pdt) {
  $pdt['adv_cat'] = $adv_cat_list[$pdt['adv_cat']]['name'];

  $baseRowValues = [];
  foreach ($pdt as $k => $v) {
    if (isset($col_headers[$k]) && !isset($field2ignore[$k]))
      $baseRowValues[$chi[$k]] = $v;
  }

  $rowValues = $baseRowValues;
  foreach ($pdt['product_attributes'] as $pdtAttr)
    $rowValues[$chi[$pdtAttr['attribute_id']]] = $pdtAttr['value'];

  writeLine($rowValues);

  if (!empty($pdt['references'])) {
    foreach ($pdt['references'] as $ref) {
      $rowValues = $baseRowValues;

      foreach ($refFields as $refField)
        $rowValues[$chi[$refField]] = $ref[$refField];

      foreach ($ref['attribute_values'] as $attrValue)
        $rowValues[$chi[$attrValue['attribute_id']]] = $attrValue['value'];

      writeLine($rowValues);
    }
  }
}

// utf8 BOM to make excel read it correctly
$csv = chr(239).chr(187).chr(191);

rewind($os);
while (($line = fgets($os)) !== false)
  $csv .= $line;
if (!feof($os))
  exit();
fclose($os);

$fileName = 'Extrait Produits Famille '.$products[0]['fam3'].' '.date('Y-m-d').'.csv';

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename='.$fileName);
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: '.strlen($csv));
ob_clean();
flush();
print $csv;
