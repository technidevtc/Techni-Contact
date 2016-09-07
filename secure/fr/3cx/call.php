<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$phoneNumber = filter_input(INPUT_GET, 'phoneNumber', FILTER_SANITIZE_NUMBER_INT);

if (empty($phoneNumber)) {
  echo json_encode(array("error" => "Malformed phone number"));
  exit();
}

$client = Doctrine_Query::create()
  ->select('c.id, c.nom, c.prenom, c.societe, c.login, c.tel1, c.tel2')
  ->from('Clients c')
  ->where('MATCH (c.tel_match) AGAINST (? IN BOOLEAN MODE)', $phoneNumber.'*')
  ->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);

if (!empty($client)) {
  $client['link'] = ADMIN_URL.'clients/?idClient='.$client['id'];

  $order = Doctrine_Query::create()
    ->select('o.id,
              o.created AS date,
              s.nom1 AS supplier_name,
              sos.name AS sender_name')
    ->from('Order o')
    ->leftJoin('o.supplier_orders so')
    ->leftJoin('so.supplier s')
    ->leftJoin('so.sender sos')
    ->leftJoin('o.created_user cu')
    ->where('o.client_id = ?', $client['id'])
    ->orderBy('o.created DESC')
    ->limit(1)
    ->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);
  
  $estimate = Doctrine_Query::create()
    ->select('e.id,
              e.created AS date,
              cu.login AS createdUserName')
    ->from('Estimate e')
    ->leftJoin('e.created_user cu')
    ->where('e.client_id = ?', $client['id'])
    ->orderBy('e.created DESC')
    ->limit(1)
    ->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);
  
  if (!empty($order) || !empty($estimate)) {
    if (empty($order) || $order['date'] < $estimate['date']) {
      $estimate['date'] = "Le ".date('d/m/Y à H:i:s', $estimate['date']);
      $estimate['link'] = ADMIN_URL.'estimates/estimate-detail.php?id='.$estimate['id'];
      $client['lastEstimate'] = $estimate;
    } else {
      $order['date'] = "Le ".date('d/m/Y à H:i:s', $order['date']);
      $order['link'] = ADMIN_URL.'orders/order-detail.php?id='.$order['id'];
      $client['lastOrder'] = $order;
    }
  } else {
    $lead = Doctrine_Query::create()
      ->select('l.id,
                l.timestamp AS date,
                a.id AS partner_id,
                a.nom1 AS partner_name,
                a.category AS partner_category,
                p.id AS product_id,
                pfr.name AS product_name,
                pfr.ref_name AS product_ref_name,
                bouc.id AS comm_id,
                bouc.name AS comm_name')
      ->from('Contacts l')
      ->innerJoin('l.advertiser a')
      ->innerJoin('l.product p')
      ->innerJoin('p.product_fr pfr')
      ->innerJoin('l.comm_user bouc')
      ->where('l.email = ?', $client['login'])
      ->orderBy('l.timestamp DESC')
      ->limit(1)
      ->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);
    
    if (!empty($lead)) {
      if ($lead['partner_category'] == __ADV_CAT_SUPPLIER__) {
        $lead['link'] = ADMIN_URL.'supplier-leads/lead-detail.php?id='.$lead['id'];
      } else {
        $lead['link'] = ADMIN_URL.'contacts/lead-detail.php?id='.$lead['id'];
      }
      $lead['partner_category_text'] = $adv_cat_list[$lead['partner_category']]['name'];
      $lead['date'] = "Le ".date('d/m/Y à H:i:s', $lead['date']);
      $client['lastLead'] = $lead;
    }
  }
  echo json_encode($client);
} else {
  echo json_encode(array('id' => false));
}
