<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
require(ICLASS . "CUserSession.php");
require(ICLASS . "CCart.php");

$handle = DBHandle::get_instance();
$session = new UserSession($handle);

function rawurldecodeEuro ($str) { return str_replace("%u20AC", "€", rawurldecode($str)); }

header("Content-Type: text/plain; charset=utf-8");

$o = array();

$cartID = isset($_GET["cartID"]) ? $_GET["cartID"] : 0;
if (!preg_match("/^[0-9a-v]{26,32}$/", $cartID)) {
	$o["error"] = "Erreur fatale";
}
else {
	$cart = new Cart($handle, $cartID);
	if ($cartID != $session->getID()) {
		if (!$session->logged) {
			$o["error"] = "Session expirée";
		}
		elseif (!$cart->existsInDB) {
			$o["error"] = "Erreur fatale";
		}
		elseif ($cart->idClient != $session->userID) {
			$o["error"] = "Erreur fatale";
		}
	}
}

if (!isset($o["error"]) && isset($_GET['action'])) {
	switch ($_GET['action']) {
		// ProductsManagement.php?action=updatecomment&idTC=13513357&comment=Commentaire%20de%20test
		case "updatecomment" :
			if (isset($_GET['idTC']) && preg_match("/^\d+$/", $_GET['idTC']) && isset($_GET['comment'])) {
				$idTC = (int)$_GET['idTC'];
				$comment = substr(rawurldecodeEuro(trim($_GET['comment'])), 0, 1023);
				$cart->updateProductComment($idTC, $comment);
				//$o["data"] = $comment;
			}
			else {
				$o["error"] = "Erreur Fatale";
			}
			break;
			
		default :
			$o["error"] = "Erreur Fatale";
			break;
	}
}
else {
	$o["error"] = "Erreur Fatale";
}

print json_encode($o);

exit();

?>
