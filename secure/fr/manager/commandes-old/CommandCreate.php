<?php

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

require(ADMIN."logs.php");
//require(ICLASS . '_ClassCommand.php');
require(ADMIN  . 'customers.php');
require(ADMIN  . 'statut.php');

$handle = DBHandle::get_instance();
$user = new BOUser();

if(!$user->login())
{
    header('Location: ' . ADMIN_URL . 'login.html');
    exit();
}
if (!$user->get_permissions()->has("m-comm--sm-orders","e")) {
  header("Location: ".ADMIN_URL."commandes/index.php?error=permissions");
  exit();
}

$sid = session_name() . '=' . session_id();

$cmd = new Command($handle, NULL, 'create');

if(isset($_GET['createCommandFromCustomer']) && $_GET['createCommandFromCustomer'] == 1 && isset($_GET['idCustomer']) && preg_match('/^[1-9]{1}[0-9]{0,8}$/', $_GET['idCustomer'])){
  $customer = new CustomerUser($handle, $_GET['idCustomer']);
  if($customer->exists){
    
    $newClientInfos = & loadCustomer($handle, $customer->id);
    $cmd->idClient = $customer->id;
    $cmd->setCoordFromArray($newClientInfos);
  }
}

$cmd->save();
header('Location: ' . ADMIN_URL . 'commandes/CommandMain.php?' . $sid . '&commandID=' . $cmd->id );
exit();

?>