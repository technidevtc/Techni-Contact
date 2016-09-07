<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$db = DBHandle::get_instance();

$session = new UserSession($db);

$o = array();

try {
  // should not happen
  if ($session->estimate)
    throw new Exception("Un devis ne peut faire l'objet d'une commande.");

  // not logged in
  if (!$session->logged)
    throw new Exception("Vous n'êtes plus identifié, veuillez réactualiser la page.");

  switch ($_POST['payment_mode']) {
    case 0 :
      $type_paiement = 0;
      $statut_paiement = 0;
      $statut_traitement = 0; //"Attente confirmation paiement Be2bill"
      break;
    
    case 1 :
      $type_paiement = 10;
      $statut_paiement = 1;
      $statut_traitement = 0; //"Attente réception chèque"
      break;
      
    case 2 :
      $type_paiement = 20;
      $statut_paiement = 2;
      $statut_traitement = 0; //"Attente réception virement"
      break;
      
    case 3 :
      $type_paiement = 30;
      $statut_paiement = 3;
      $statut_traitement = 0; //"Attente validation paiement"
      break;
      
    case 4 :
      $type_paiement = 40;
      $statut_paiement = 4;
      $statut_traitement = 0; //"Attente validation paiement"
      break;
      
    case 5 :
      $type_paiement = 50;
      $statut_paiement = 5;
      $statut_traitement = 0; //"Attente validation paiement"
      break;
    default :
      throw new Exception("Le mode de paiement choisi est invalide.");
  }
  
  $cart2 = Doctrine_Query::create()
    ->select('ct.*, ctl.*, c.*, cda.*, cba.*')
    ->from('Paniers ct')
    ->innerJoin('ct.lines ctl')
    ->innerJoin('ct.client c')
    ->innerJoin('ct.delivery_address cda')
    ->innerJoin('ct.billing_address cba')
    ->where('ct.id = ?', $session->getID())
    ->andWhere('ct.idClient = ?', $session->userID)
    ->andWhere('ct.locked = ?', 1)
    ->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);

  // cart is not locked/does not belong to the current client/has no address set -> the second step will handle it
  // if the client changed, order-step 2 will also handle the addresses, no need to check here
  if (!$cart2)
    throw new Exception("Le panier n'est plus valide, veuillez réactualiser la page.");

  // empty cart, nothing to do here
  if (!count($cart2['lines']) || !$cart2['valid'])
    throw new Exception("Le panier est vide ou a été modifié, veuillez réactualiser la page.");

  $cart = new Cart($db, $session->getID());

  $order = new Order();
  $order->type = Order::TYPE_INTERNET;
  $order->importFromCart($cart, $cart2);
  $order->activity = Order::ACTIVITY_VPC_COMPTANT;
  $order->payment_mode = Order::PAYMENT_MODE_AT_ORDER;
  $order->payment_mean = $type_paiement;
  $order->payment_status = $statut_paiement;
  $order->processing_status = $statut_traitement;
  $order->campaign_id = !empty($_COOKIE['campaignID']) ? $_COOKIE['campaignID'] : 0;
  setCookie('campaignID', "", time()+86400*15, '/', DOMAIN); 
  //$order->genFreeId();
  $order->save();
  $order->sendClientEmail();
  
  $o['response']['orderID'] = $order->id;
  
  if ($type_paiement == 0) {
    
    list($b2b_o_desc) = preg_split("`(\r\n|\r|\n)`", $order->lines[0]->desc, 2);
    $b2b_o_desc = substr(Utils::toASCII($b2b_o_desc), 0, 100);
    
    $b2b_params = array(
      'IDENTIFIER' => BE2BILL_IDENTIFIER,
      'OPERATIONTYPE' => 'payment',
      'CLIENTIDENT' => $order['client_id'],
      'DESCRIPTION' => $b2b_o_desc,
      'ORDERID' => $order->id,
      'AMOUNT' => intval($order->total_ttc * 100),
      'VERSION' => '2.0',
      'CLIENTEMAIL' => $order['email'],
      'CLIENTREFERRER' => COMMANDE_URL."order-step3.html"
      //'CLIENTADDRESS' => $order['adresse'].$order['caddress'].$order['cp'].$order['ville'].$order['pays'],
    );
    $b2b_params['HASH'] = Utils::be2bill_signature(BE2BILL_PASSWORD, $b2b_params);
    
    $o['response']['b2b_params'] = $b2b_params;
    
    $b2b_params['BASE64'] = base64_encode(json_encode($b2b_params));
    flog("TIME : ".date('d/m/Y H:i:s')."\n".print_r($b2b_params, true)."----------------------------------------", 'b2b-params.log');
  }
  
  /*if (!TEST) {
    // Avail
    $api = new JsonRpcClient(AVAIL_JSONRPC_API_URL);

    $ProductIDs = $Prices = array();
    foreach ($cart->items as $item) {
      $ProductIDs = array_merge($ProductIDs, array_fill(0,$item['quantity'],(string)$item['idProduct']));
      $Prices = array_merge($Prices, array_fill(0,$item['quantity'],preg_replace('`,`', '.', $item['priceHT'])));
    }
    try {
      $api->logPurchase(array(
        "SessionID" => !empty($_COOKIE['__avail_session__']) ? $_COOKIE['__avail_session__'] : "null01",
        "UserID" => (string)($session->userID),
        "ProductIDs" => $ProductIDs,
        "Prices" => $Prices,
        "OrderID" => "O".$order->id,
        "Currency" => "EUR"
      ));
    } catch (Exception $e) {
      //echo $e->getMessage();
    }
  }*/
  
  if ($statut_paiement == 0) {
    // keep the order id until it's considered as paid
    $session->orderID = $order->id;
  } else {
    // empty the cart now, the order is considered as paid with those means
    $cart->clearProducts();
    $cart->save();
    unset($session->orderID);
  }
  
  print json_encode($o);
  
} catch (Exception $e) {
  header($_SERVER['SERVER_PROTOCOL'].' 500 Internal Server Error', true, 500);
  echo "Erreur fatale : ".$e->getMessage();
}

