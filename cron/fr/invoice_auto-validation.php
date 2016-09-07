<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$date_th = mktime(0,0,0) - 86400*3; // date threshold

// invoices with a valid shipping date of more than 3 days ago that are still not validated
$invoice_list = Doctrine_Query::create()
  ->select('i.*,
            o.id,
            o.forecasted_ship')
  ->from('Invoice i')
  ->leftJoin('i.order o')
  ->where('o.forecasted_ship > ? AND o.forecasted_ship < ? AND i.issued = ?', array(0, $date_th, 0))
  ->execute();

foreach ($invoice_list as $invoice)
  $invoice->validate();
