<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$db = DBHandle::get_instance();
$session = new UserSession($db);
$o = array();

if ($session->logged) {
  
  $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
  if (!empty($message)) {
    flog("TIME : ".date('d/m/Y H:i:s')."\n".$message."\n----------------------------------------", 'b2b-message.log');
    $o['success'] = true;
  } else {
    $o['error'] = "message empty";
  }
} else {
  $o['error'] = "not logged in";
}

echo json_encode($o);