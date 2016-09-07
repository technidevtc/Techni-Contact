<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$o = array();

try {

$user = new BOUser();

if (!$user->login())
  throw new Exception("Votre session a expirée, veuillez vous identifier à nouveau après avoir rafraichi votre page");

$inputs = filter_input_array(INPUT_POST, array(
  'type' => array('filter' => FILTER_VALIDATE_REGEXP,
                  'options' => array('regexp' => '`client|invoice`')
                 ),
  'start' => FILTER_VALIDATE_INT,
  'end' => FILTER_VALIDATE_INT
));

foreach ($inputs as $k => $v)
  if (!isset($v))
    throw new Exception("Requète incorrecte");
  else
    $$k = $v;

$time = time();
if ($type == "client") {
  $stmt = $conn->execute('
    UPDATE clients c
    INNER JOIN invoice i ON c.id = i.client_id
    SET c.cegid_exported = ?
    WHERE
      i.issued >= ? AND
      i.issued < ? AND
      c.cegid_exported = 0', array($time, $start, $end));
  $o['result'] = $stmt->rowCount();
  
} elseif ($type == "invoice") {
  $rows = Doctrine_Query::create()
    ->update('Invoice')
    ->set('cegid_exported','?',$time)
    ->where('issued >= ? AND issued < ? AND cegid_exported = ?', array($start, $end, 0))
    ->execute();
  $o['result'] = $rows;
}

$o['success'] = 1;

} catch (Exception $e) {
  header($_SERVER['SERVER_PROTOCOL'].' 500 Internal Server Error', true, 500);
  $o['error'] = "FATAL ERROR : ".$e->getMessage();
}

print json_encode($o);
