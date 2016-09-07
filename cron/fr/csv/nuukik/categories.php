<?php

// col headers
$headers = array(
  'ID Catégorie',
  'ID Catégorie mère',
  'level',
  'Nom'
);
fputcsv($fh, $headers, ';');

set_time_limit(120); // query could be long, preventing timeout

$sth = $db->prepare("
  SELECT
    f.id,
    f.idParent,
    ffr.name,
    COUNT(pfr.id) AS pdt_count
  FROM families f
  INNER JOIN families_fr ffr ON f.id = ffr.id
  LEFT JOIN products_families pf ON f.id = pf.idFamily
  LEFT JOIN products_fr pfr ON pfr.id = pf.idProduct
  LEFT JOIN advertisers a ON a.id = pfr.idAdvertiser
  WHERE pfr.id IS NULL OR (pfr.active = 1 AND pfr.deleted = 0 AND a.actif = 1)
  GROUP BY f.id
  ORDER BY ffr.name");
$sth->execute();
while ($cat = $sth->fetch(PDO::FETCH_ASSOC)) {
  
  // small trick to know the category level
  $cat_level = $cat['pdt_count'] > 0 ? 3 : ($cat['idParent'] == 0 ? 1 : 2);
  
  $catValues = array(
    $cat['id'],
    $cat['idParent'],
    $cat_level,
    trim($cat['name'])
  );
  fputcsv($fh, $catValues, ';');
}
