<?php

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

require(ADMIN."logs.php");
require(ADMIN  . 'customers.php');
require(ADMIN  . 'statut.php');

$handle = DBHandle::get_instance();
$user = new BOUser();

if (!$user->login()) {
	header('Location: ' . ADMIN_URL . 'login.html');
	exit();
}
if (!$user->get_permissions()->has("m-comm--sm-estimates","e")) {
  header("Location: ".ADMIN_URL."devis/index.php?error=permissions");
  exit();
}

$sid = session_name() . '=' . session_id();

$esti = & new Cart($handle, NULL);
//$esti->create();
$esti->makeEstimate(1);
$esti->save();
header('Location: ' . ADMIN_URL . 'devis/EstimateMain.php?' . $sid . '&estimateID=' . $esti->id);
exit();

?>