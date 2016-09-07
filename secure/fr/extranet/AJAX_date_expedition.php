<?php
/*================================================================/

 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com

 Auteur : ODpour Hook Network SARL - http://www.hook-network.com
 Date de cr?ion : 18 janvier 2012

 Fichier : /secure/extranet/AJAX_date_exp?tion.php
 Description : gestion ajax des dates d'exp?tion

/=================================================================*/
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

header('Content-Type: text/plain; charset=utf-8');
$conn->setCharset('utf8');

require_once(ADMIN.'logs.php');
require_once(ADMIN.'logo.php');
require_once(ICLASS.'ExtranetUser.php');
require(ADMIN.'statut.php');

$handle = DBHandle::get_instance();
$user = new ExtranetUser($handle);

$o = array();
$inputs = filter_input_array(
  INPUT_POST,
  array(
    'idUser' => FILTER_VALIDATE_INT,
    'action' => FILTER_SANITIZE_STRING,
    'ordre' => FILTER_VALIDATE_INT,
    'date' => FILTER_SANITIZE_STRING
  )
);
foreach ($inputs as $k => $v)
  $$k = $v;

if (empty($idUser) || empty($action) || empty($ordre) || empty($date)) {
  $o['error'] = "Requète incorrecte";
  print json_encode($o);
  exit();
}
if (!$user->login($login, $pass) || !$user->active) {
  $o['error'] = "Votre session a expirée, veuillez vous identifier à nouveau après avoir rafraichi votre page";
  print json_encode($o);
  exit();
}
if ($idUser != $user->id) {
  $o['error'] = "Erreur d'identification";
  print json_encode($o);
  exit();
}

if ($action == 'set_date') {
  if (empty($date)) {
    $o['error'] = "Information manquante";
    print json_encode($o);
    exit();
  }

  $so = Doctrine_Query::create()
      ->select('so.*, o.*, s.nom1, s.email')
      ->from('SupplierOrder so')
      ->innerJoin('so.supplier s')
      ->innerJoin('so.order o')
      ->where('so.order_id = ?', $ordre)
      ->andWhere('so.sup_id = ?', $user->id)
      ->fetchOne();
  if (!$so) {
    $o['error'] = "La commande n'existe pas";
    print json_encode($o);
    exit();
  }
  // inform messenger about forecast delivery date about supplier order
  $m = new Messenger();
  $m->context = __MSGR_CTXT_ORDER_CMD__;
  $m->type_sender = __MSGR_USR_TYPE_ADV__;
  $m->id_sender = $user->id;
  $m->type_recipient = __MSGR_USR_TYPE_BOU__;
  $m->id_recipient = __ID_TECHNI_CONTACT_BOUSER__;
  $m->reference_to = $so['order_id'];
  $m->text = "Le fournisseur a défini lui même la date d'expédition de la commande au ".$date;
  $m->save();
  
  // inform messenger about forecast delivery date about order
  $m = new Messenger();
  $m->context = __MSGR_CTXT_ORDER_CMD__;
  $m->type_sender = __MSGR_USR_TYPE_ADV__;
  $m->id_sender = $user->id;
  $m->type_recipient = __MSGR_USR_TYPE_INT__;
  $m->id_recipient = $so['order']['client_id'];
  $m->reference_to = $so['id'];
  $m->text = "Le fournisseur a défini lui même la date d'expédition de la commande au ".$date;
  $m->save();
  
  preg_match('`(\d+)/(\d+)/(\d+)`', $date, $dateParts);
  $timestamp = mktime(0,0,0,$dateParts[2],$dateParts[1],$dateParts[3]);
  if ($timestamp > $so->order->forecasted_ship) {
    $so->order->forecasted_ship = $timestamp;
    $so->order->forecast_shipping_text = $date;
  }
  $so->order->processing_status = Order::GLOBAL_PROCESSING_STATUS_FORECAST_SHIPPING_DATE;
  $so->order->send_mail = true; // to automatically send and order status update email
  $so->order->save(); // avoid a cascade update temporary bug (supplier orders being refreshed in Order.php)
  $so->processing_status = SupplierOrder::PROCESSING_STATUS_FORECAST_SHIPPING_DATE;
  $so->forecast_shipping_text = $date;
  $so->save();
  $so->sendForecastShippingDateFixedByPartnerEmail();
  
  $o['result'] = "Message envoyé avec succès";
}
else {
  $o['error'] = "Action impossible";
  print json_encode($o);
  exit();
}
print json_encode($o);
