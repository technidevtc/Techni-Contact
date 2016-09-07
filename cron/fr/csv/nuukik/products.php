<?php

// col headers
$headers = array(
  'ID Produit',
  'ID Categorie',
  'Nom',
  'URL Produit',
  'URL Image'
);
fputcsv($fh, $headers, ';');

set_time_limit(120); // query could be long, preventing timeout

$sth = $db->prepare("
  SELECT
    p.id,
    pff.idFamily AS cat_id,
    pfr.ref_name,
    pfr.name
  FROM products p
  INNER JOIN products_fr pfr ON pfr.id = p.id AND pfr.active = 1 AND pfr.deleted = 0
  INNER JOIN advertisers a ON a.id = p.idAdvertiser AND a.actif = 1
  INNER JOIN (
    SELECT idProduct, idFamily
    FROM products_families
    ORDER BY orderFamily ASC
  ) pff ON pff.idProduct = p.id
  GROUP BY p.id");
$sth->execute();
while ($pdt = $sth->fetch(PDO::FETCH_ASSOC)) {
  $pdtValues = array(
    $pdt['id'],
    $pdt['cat_id'],
    trim($pdt['name']),
    Utils::get_pdt_fo_url($pdt['id'], $pdt['ref_name'], $pdt['cat_id']),
    Utils::get_pdt_pic_url($pdt['id'], 'card', 1, $pdt['ref_name'])
  );
  fputcsv($fh, $pdtValues, ';');
}
