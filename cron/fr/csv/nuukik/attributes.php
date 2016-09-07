<?php

// col headers
$headers = array(
  'Type',
  'ID Item',
  'ID Attribut',
  'Nom',
  'Valeur'
);
fputcsv($fh, $headers, ';');

set_time_limit(120); // query could be long, preventing timeout

$sth = $db->prepare("
  SELECT
    p.id,
    a.category AS adv_cat,
    ps.hits,
    ps.leads,
    ps.orders,
    (ps.leads + ps.orders) AS leads_orders,
    IF(ps.hits>0, (ps.leads + ps.orders) / ps.hits, 0) AS conversion_rate,
    p.timestamp,
    fr3.name AS cat3_name,
    fr2.name AS cat2_name,
    fr1.name AS cat1_name
  FROM products p
  INNER JOIN products_fr pfr ON pfr.id = p.id AND pfr.active = 1 AND pfr.deleted = 0
  INNER JOIN advertisers a ON a.id = p.idAdvertiser AND a.actif = 1
  INNER JOIN products_families pf ON pf.idProduct = p.id
  INNER JOIN families f3 ON f3.id = pf.idFamily
  INNER JOIN families_fr fr3 ON fr3.id = f3.id
  INNER JOIN families f2 ON f2.id = f3.idParent
  INNER JOIN families_fr fr2 ON fr2.id = f2.id
  INNER JOIN families f1 ON f1.id = f2.idParent
  INNER JOIN families_fr fr1 ON fr1.id = f1.id
  LEFT JOIN products_stats ps ON pf.idProduct = ps.id
  GROUP BY p.id");
$sth->execute();
while ($pdt = $sth->fetch(PDO::FETCH_ASSOC)) {
  fputcsv($fh, array('P', $pdt['id'], '', 'Type partenaire', $adv_cat_list[$pdt['adv_cat']]['name']), ';');
  fputcsv($fh, array('P', $pdt['id'], '', 'Nombre de vues', $pdt['hits']), ';');
  fputcsv($fh, array('P', $pdt['id'], '', 'Nombre de leads', $pdt['leads']), ';');
  fputcsv($fh, array('P', $pdt['id'], '', 'Nombre de commandes', $pdt['orders']), ';');
  fputcsv($fh, array('P', $pdt['id'], '', 'Nombre de leads + commandes', $pdt['leads_orders']), ';');
  fputcsv($fh, array('P', $pdt['id'], '', 'Taux de transfo', round($pdt['conversion_rate']*100, 3)), ';');
  fputcsv($fh, array('P', $pdt['id'], '', 'Date mise à jour', date('Y-m-d H:i:s', $pdt['timestamp'])), ';');
  fputcsv($fh, array('P', $pdt['id'], '', 'Famille 1', $pdt['cat1_name']), ';');
  fputcsv($fh, array('P', $pdt['id'], '', 'Famille 2', $pdt['cat2_name']), ';');
  fputcsv($fh, array('P', $pdt['id'], '', 'Famille 3', $pdt['cat3_name']), ';');
}

