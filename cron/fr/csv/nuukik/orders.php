<?php

// col headers
$headers = array(
  'ID Client',
  'ID Article',
  'Date',
  'Quantité'
);
fputcsv($fh, $headers, ';');

set_time_limit(120); // query could be long, preventing timeout

$sth = $db->prepare("
  SELECT
    o.client_id,
    ol.pdt_ref_id,
    o.validated,
    ol.quantity
  FROM `order` o
  INNER JOIN `order_line` ol ON ol.order_id = o.id
  WHERE o.validated > 0 AND o.cancelled = 0 AND ol.pdt_ref_id != 0");
$sth->execute();
while ($order = $sth->fetch(PDO::FETCH_ASSOC)) {
  $orderValues = array(
    $order['client_id'],
    $order['pdt_ref_id'],
    date('Y-m-d H:i', $order['validated']),
    $order['quantity']
  );
  fputcsv($fh, $orderValues, ';');
}

$sth = $db->prepare("
  SELECT
    c.id AS client_id,
    p.idTC AS pdt_idtc,
    l.timestamp
  FROM contacts l
  INNER JOIN clients c ON c.login = l.email AND c.login != ''
  INNER JOIN products p ON p.id = l.idProduct
  INNER JOIN products_fr pfr ON pfr.id = p.id AND pfr.active = 1 AND pfr.deleted = 0
  INNER JOIN advertisers a ON a.id = p.idAdvertiser AND a.actif = 1
  WHERE l.parent = 0");
$sth->execute();
while ($lead = $sth->fetch(PDO::FETCH_ASSOC)) {
  $leadValues = array(
    $lead['client_id'],
    $lead['pdt_idtc'],
    date('Y-m-d H:i', $lead['timestamp']),
    1
  );
  fputcsv($fh, $leadValues, ';');
}
