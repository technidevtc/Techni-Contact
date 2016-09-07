<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

function addCustomer($login, $customer){
  
  $customer->getCustomerFromLogin($login);
  if ($customer->exists) {
    $response = array();
    $response[] = array(
      'customerID' => $customer->id,
      'company' => $customer->societe,
      'name' => $customer->nom.' '.$customer->prenom,
      'cp' => $customer->cp,
      'city' => $customer->ville,
      'email'  => $customer->email,
    );
    return $response;
  } else
    return false;
}

define("MAX_AUTOCOMPLETION_RESULTS", 10);
define("AUTOCOMPLETION_CATEGORIES_RESULT_COUNT", 5);
define("AUTOCOMPLETION_PRODUCTS_TITLE_RESULT_COUNT", 10);

//include LANG_LOCAL_INC . "includes-" . DB_LANGUAGE . "_local.php";
//include LANG_LOCAL_INC . "www-" . DB_LANGUAGE . "_local.php";

$handle = DBHandle::get_instance();

//header("Content-Type: text/plain; charset=utf-8");

$q = isset($_GET["term"]) ? trim($_GET["term"]) : '';

$tel = isset($_GET['telCheck']) && $_GET['telCheck'] == 'ok' ? true : false;

$collection = array();

if (strlen($q) > 1) {

  $customer = new CustomerUser($handle);

  if (preg_match('/^([\p{L}[\p{L} -_. ]{0,255})$/', $q)) { // company search if future match problem, bool on other matches then test
    $customers = array();
    $customers = $customer->getCustomerFromCompany($q);

    if (!empty($customers)) {
      $response = array();
      foreach ($customers as $row => $customer)
      $response[] = array(
        'customerID' => $customer->id,
        'company' => $customer->societe,
        'name' => $customer->nom.' '.$customer->prenom,
        'cp' => $customer->cp,
        'city' => $customer->ville,
        'email'  => $customer->email,
      );
      $collection['company'] = $response;
    }
    
    $customers = $customer->getCustomerFromName($q);

    if (!empty($customers)) {
      $response = array();
      foreach ($customers as $row => $customer)
      $response[] = array(
        'customerID' => $customer->id,
        'company' => $customer->societe,
        'name' => $customer->nom.' '.$customer->prenom,
        'cp' => $customer->cp,
        'city' => $customer->ville,
        'email'  => $customer->email,
      );
      $collection['name'] = $response;
    }
  }

  if (preg_match('/^[[:alnum:]]([-_.]?[[:alnum:]])*@[[:alnum:]]([-_.]?[[:alnum:]])*\.([a-z]{2,4})$/', $q)) { // email search
    $customer->getCustomerFromLogin($q);
    $response = array();
    if ($customer->exists) {
      $response[] = array(
        'customerID' => $customer->id,
        'company' => $customer->societe,
        'name' => $customer->nom.' '.$customer->prenom,
        'cp' => $customer->cp,
        'city' => $customer->ville,
        'email'  => $customer->email,
      );
    }
    
    $contact = $customer->getCustomerFromContact($q);
      if ($contact) {
        $customer = new CustomerUser($handle, $contact['cid']);
        if ($customer->exists) {
          //$response = array();
          $response[] = array(
            'customerID' => $customer->id,
            'company' => $customer->societe,
            'name' => $customer->nom.' '.$customer->prenom,
            'cp' => $customer->cp,
            'city' => $customer->ville,
            'email'  => $customer->email,
          );
        }
      }
      $collection['email'] = $response;

  } elseif ($tel) {
    $customers = $customer->getCustomerFromTelephone($q);
    foreach($customers as $customer){
      $response = array();
      $tel = !empty($customer->tel1) ? $customer->tel1 : (!empty($customer->tel2) ? $customer->tel2 : (!empty($customer->fax2) ? $customer->fax1 : (!empty($customer->fax2) ? $customer->fax2 : '')));
      $response[] = array(
        'customerID' => $customer->id,
        'company' => $customer->societe,
        'name' => $customer->nom.' '.$customer->prenom,
        'cp' => $customer->cp,
        'city' => $customer->ville,
        'email'  => $customer->email,
        'tel'  => $tel
      );
      $collection[] = $response;
    }
    
  } elseif (preg_match('/^\d+$/', $q)) { // customer ID, command ID , ID lead , devis manager, invoice search
  //  var_dump('ID search');
    $customerLogin = false;

    $customer->id = $q;
    $customer->load();
    if ($customer->exists) {
      $response = array();
      $response[] = array(
        'customerID' => $customer->id,
        'company' => $customer->societe,
        'name' => $customer->nom.' '.$customer->prenom,
        'cp' => $customer->cp,
        'city' => $customer->ville,
        'email'  => $customer->email,
      );
      $collection['customerId'] = $response;
    }
    
    /*  search commande */
    $order = Doctrine_Core::getTable('Order')->find($q);
    if ($order->email){
      $reponse = addCustomer(strtolower($order->email), $customer);
      if ($reponse)
        $collection['orderId'] = $reponse;
    }
    
    /*  search lead */
    $lead = new Lead($q);
    if ($lead->email) {
      $reponse = addCustomer(strtolower($lead->email), $customer);
      if ($reponse)
        $collection['leadId'] = $reponse;
    }


    /*  search devis manager */
    $estimate = Doctrine_Core::getTable('Estimate')->find($q);
    if ($estimate->email) {
      $reponse = addCustomer(strtolower($estimate->email), $customer);
      if ($reponse)
        $collection['estimateId'] = $reponse;
    }

    /*  search invoice/facture */
    $invoiceList = Doctrine_Query::create()
      ->select('DISTINCT(i.email) as email')
      ->from('Invoice i')
      ->where('i.rid = ?', $q)
      ->fetchArray(array(), Doctrine_Core::HYDRATE_ARRAY);

    foreach ($invoiceList as $invoice){
      if ($invoice['email']) {
        $reponse = addCustomer(strtolower($invoice['email']), $customer);
        if ($reponse)
          $invoices[] = $reponse[0];
      }
    }
    if ($invoices)
      $collection['invoiceId'] = $invoices;
    
  } elseif (preg_match('`^[a-zA-Z0-9_-]+$`', $q)) { // probably alternate id
    /*  search commande */
    $order = Doctrine_Query::create()
        ->select('o.*')
        ->from('Order o')
        ->where('o.alternate_id = ?', $q)
        ->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);
    
    if ($order['email']) {
      $reponse = addCustomer(strtolower($order['email']), $customer);
      if ($reponse)
        $collection['orderAlternateId'] = $reponse;
    }
  }
	echo json_encode($collection);

} else {
	echo json_encode(array());
}

/*
foreach($results as $k => $v) {
	print "\n" . $k . " = " . $v["count"] . " résults\t\tTime = " . ($v["end_time"]-$v["start_time"]) . "\n";
	print "data =" . print_r($v["data"], true) . "\n";
}
*/