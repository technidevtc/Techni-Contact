<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
require(ICLASS . "CUserSession.php");
require(ICLASS . "CCart.php");

$handle = DBHandle::get_instance();
$session = & new UserSession($handle);

$gotoaccount = false;
$cartID = isset($_POST["idDevis"]) ? $_POST["idDevis"] : (isset($_GET["idDevis"]) ? $_GET["idDevis"] : 0);
if (!preg_match("/^[0-9a-v]{26,32}$/", $cartID)) {
	$gotoaccount = true;
}
else {
	if (!$session->logged){
		$session->pageAfterLogin = COMPTE_URL . "panier_add_devis.php?idDevis=" . $cartID;
		header("Location: " . COMPTE_URL . "login.html");
		exit();
	}
	$cart = new Cart($handle, $cartID);
	if (!$cart->existsInDB) {
		$gotoaccount = true;
	}
	elseif ($cart->idClient != $session->userID) {
		$gotoaccount = true;
	}
}

$cart->makeMainCart($session->getID());

//require(ICLASS . "CStatisticsManager.php");
//$stats = & new StatisticsManager($handle);
//$cart->completeItemsInfos();
//$stats->SaveCartAsEstimate($cart);

header("Location: " . URL . "panier.html");
exit();
?>