<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
require(ICLASS . "CUserSession.php");
require(ICLASS . "CCart.php");
require(ICLASS . "CStatisticsManager.php");

$handle = DBHandle::get_instance();
$session = & new UserSession($handle);
$cart = & new Cart($handle, $session->getID());
$stats = & new StatisticsManager($handle);

if (isset($_POST["famille"]) && isset($_POST["produit"]) && isset($_POST["quantity"]) && isset($_POST["idTC"]) &&
	preg_match("/^\d+$/",$_POST["famille"]) && preg_match("/^\d+$/",$_POST["produit"]) && preg_match("/^[0-9]+$/",$_POST["quantity"]) && preg_match("/^\d+$/",$_POST["idTC"])) {
	
	$cart->AddProduct($_POST["produit"], $_POST["idTC"], $_POST["famille"], $_POST["quantity"]);
	$stats->AddProductToCart($_POST["produit"], $_POST["idTC"], $_POST["famille"], $card->items[$_POST["idTC"]]["idAdvertiser"], $_POST["quantity"]);
	$cart->calculateCart();
	
	header("Location: " . URL . "panier.html");
	exit();
}
else {
	header("Location: " . URL);
    exit();
}

?>