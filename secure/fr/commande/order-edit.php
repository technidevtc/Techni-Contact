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

  $orderID = isset($_POST['orderID']) && preg_match("/^\d+$/", $_POST['orderID']) ? $_POST['orderID'] : 0;
  if (empty($orderID))
    throw new Exception("Numéro de commande invalide.");
  
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
  
  $order = Doctrine_Query::create()
      ->select('*')
      ->from('Order')
      ->where('id = ?', $orderID)
      ->andWhere('client_id = ?', $session->userID)
      ->fetchOne();
  
  if (empty($order->id))
    throw new Exception("Vous ne possédez pas de commande ayant pour numéro ".$orderID);
  
  $order->payment_mean = $type_paiement;
  $order->payment_status = $statut_paiement;
  $order->processing_status = $statut_traitement;
  $order->save();
  
  $o['response']['orderID'] = $order->id;

  if ($statut_paiement == 0) {
    // keep the order id until it's considered as paid
    $session->orderID = $order->id;
  } else {
    // empty the cart now, the order is considered as paid with those means
    $cart = new Cart($db, $session->getID());
    $cart->clearProducts();
    $cart->save();
    unset($session->orderID);
  }
  
  print json_encode($o);

} catch (Exception $e) {
  header($_SERVER['SERVER_PROTOCOL'].' 500 Internal Server Error', true, 500);
  echo "Erreur fatale : ".$e->getMessage();
}
