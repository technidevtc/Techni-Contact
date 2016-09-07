<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

// this file is in ANSI and not in UTF-8 because the Excel Writer plugin seems to not handle it correctly

$user = new BOUser();
if (!$user->login()) {
	header('Location: '.ADMIN_URL.'login.html');
	exit();
}

$start = filter_input(INPUT_GET, 'start', FILTER_VALIDATE_INT);
$end = filter_input(INPUT_GET, 'end', FILTER_VALIDATE_INT);
if (!$start || !$end)
  exit();

require_once('Spreadsheet/Excel/Writer.php');

$workbook = new Spreadsheet_Excel_Writer();
$workbook->send('Extrait Reporting Commandes Détaillé du '.date('d-m-Y',$start).' au '.date('d-m-Y',$end-86400).'.xls');
$worksheet = $workbook->addWorksheet('Reporting Commandes');

// Headers
$col_headers = array(
  'Date commande' => 'validated',
  'N° ligne commande' => 'line.num',
  'Source commande' => 'type',
  'Commercial créateur du devis' => 'in_charge_user_name',
  'N° devis associé' => 'estimate_id',
  'ID client' => 'client_id',
  'Société client' => 'societe',
  'Mail client' => 'email',
  'ID commande' => 'id',
  'Nom produit' => 'line.pdt_name',
  'Nom fournisseur' => 'line.sup_name',
  'Libellé' => 'line.desc',
  'Ref fournisseur' => 'line.sup_ref',
  'Ref TC' => 'line.idtc',
  'Nb items ligne produit' => 'line.quantity',
  'Total PA HT du produit' => 'line.total_a_ht',
  'Facture fournisseur' => 'line.so_total_ht',
  'P vente HT total ligne produit' => 'line.total_ht',
  'Total factures fournisseur' => 'so_total_ht',
  'Montant total HT commande' => 'total_ht',
  'Marge brute' => 'margin',
  'Statut commande' => 'processing_status',
  'N°ordre founisseur' => 'line.so_rid',
  'ID campaign' => 'campaign_id',
  'N° lead' => 'lead_id',
  'Typologie lead manager ou nom campagne marketing' => 'lead_source',
  'Utilisateur lead manager' => 'lead_processed_user_name',
  'N°facture' => 'invoice_id'
);

$l = $c = 0;
$chi = array_flip($col_headers); // Cols Headers Index
foreach ($chi as $col_header_name)
  $worksheet->write($l, $c++, $col_header_name);
$l++;


$t1 = 0;//mktime(0,0,0);
$t2 = 0x7fffffff; //$t1+86400;

$offset = 0;
$limit = 1000;
do {
  set_time_limit(60);
  $q = Doctrine_Query::create()
      ->select('o.id,
                FROM_UNIXTIME(o.validated, "%d/%m/%Y %h:%i:%s") AS validated,
                o.type,
                IF(o.estimate_id,o.estimate_id,"N/A") as estimate_id,
                o.societe,
                o.client_id,
                o.email,
                o.processing_status,
                IF(o.campaign_id,o.campaign_id,"") AS campaign_id,
                IF(o.lead_id,o.lead_id,"") AS lead_id,
                o.total_ht,
                o.fdp_ht,
                IF(ol.pdt_id,olpfr.name,"-") AS pdt_name,
                ol.sup_id,
                ol.sup_ref,
                ol.desc,
                IF(ol.sup_id,ols.nom1,"-") AS sup_name,
                ol.pdt_ref_id AS idtc,
                ol.quantity,
                ol.pau_ht,
                ol.total_ht,
                ol.total_ttc,
                IFNULL(icu.name,"N/A") AS in_charge_user_name,
                i.rid AS invoice_id,
                IFNULL(l.origin, "") AS lead_source,
                IFNULL(lpu.name, "N/A") AS lead_processed_user_name,
                so.total_ht_real,
                so.order_id,
                so.sup_id')
      ->from('Order o')
      ->innerJoin('o.lines ol')
      ->leftJoin('ol.supplier ols')
      ->leftJoin('ol.product olp')
      ->leftJoin('olp.product_fr olpfr')
      ->leftJoin('o.in_charge_user icu')
      ->leftJoin('o.invoice i')
      ->leftJoin('o.lead l')
      ->leftJoin('l.processed_user lpu')
      ->innerJoin('o.supplier_orders so')
      ->where('o.processing_status >= ?', Order::GLOBAL_PROCESSING_STATUS_PROCESSING)
      ->andWhere('o.validated >= ? AND o.validated < ?', array($start, $end))
      ->orderBy('o.validated DESC')
      ->offset($offset)
      ->limit($limit);
  $oc = $q->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
  //pp($oc);
  $tab_microtime['query fetch'] = microtime(true);

  foreach ($oc as $o) {
    
    $sol = array();
    $o['so_total_ht'] = 0;
    foreach ($o['supplier_orders'] as $so) { // indexing supplier orders
      $sol[$so['sup_id']] = $so;
      $o['so_total_ht'] += $so['total_ht_real'];
    }
    $o['margin'] = $o['total_ht'] - $o['so_total_ht'];
    
    if ($o['fdp_ht'] > 0) { // add fdp as a product line
      $o['lines'][] = array(
        'sup_ref' => "",
        'desc' => "",
        'quantity' => "",
        'total_ht' => $o['fdp_ht'],
        'pdt_name' => "Frais de port",
        'sup_name' => "",
        'idtc' => ""
      );
    }
    foreach ($o['lines'] as $li => $line) {
      $line['num'] = $li+1;
      $line['so_rid'] = $sol[$line['sup_id']]['rid'];
      $line['so_total_ht'] = $sol[$line['sup_id']]['total_ht_real'];
      $c = 0;
      foreach($col_headers as $col_header) {
        $chp = explode('.',$col_header); // col header parts
        $val = "";
        if (isset($chp[1])) {
          $val = utf8_decode(${$chp[0]}[$chp[1]]);
        }
        elseif (!$li) {
          switch ($chp[0]) {
            case 'type':
              $val = utf8_decode($o['type_text']);
              break;
            case 'processing_status':
              $val = utf8_decode(Order::getGlobalProcessingStatusText($o['processing_status']));
              break;
            default:
              $val = utf8_decode($o[$chp[0]]);
          }
        }
        $worksheet->write($l, $c++, $val);
      }
      $l++;
    }
  }
  $offset += $limit;
} while (!empty($oc));

$workbook->close();

