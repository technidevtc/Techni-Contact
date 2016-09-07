<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
require(ICLASS . "CUserSession.php");
require(ICLASS . "CCart.php");

$handle = DBHandle::get_instance();
$session = new UserSession($handle);

function rawurldecodeEuro ($str) { return str_replace("%u20AC", "€", rawurldecode($str)); }

header("Content-Type: text/plain; charset=utf-8");

$o = array();

try {
	// Getting action type. If none specified, we stop here
	if (!isset($_GET['action']))
		throw new Exception("Internal error : No action specified");
	$action = strtolower($_GET['action']);
	
	if (!isset($_GET["umail"]) || !preg_match("`^[[:alnum:]]([-_.]?[[:alnum:]])*@[[:alnum:]]([-_.]?[[:alnum:]])*\.([a-z]{2,4})$`", $_GET["umail"]))
		throw new Exception("Email utilisateur non valide");
	$umail = $_GET["umail"];
        if($umail != $_COOKIE["email"])
          throw new Exception('Incorrect email address');
	
	$fel = array();		// Friend Email List
	$fen = 1;			// Friend Email Num
	$fef = 0;			// Friend Email Found
	while ($fen <= 5) {
		if (isset($_GET["fmail".$fen]) && preg_match("`^[[:alnum:]]([-_.]?[[:alnum:]])*@[[:alnum:]]([-_.]?[[:alnum:]])*\.([a-z]{2,4})$`", $_GET["fmail".$fen]))
			$fel[] = $_GET["fmail".$fen];
		$fen++;
	}
	$fef = count($fel);
	if ($fef == 0) {
		throw new Exception("Pas d'email de collègue renseigné valide");
	}
	
	switch ($action) {
		case "sendproduct" :
			if (!isset($_GET["pdtID"]) || !preg_match("/^\d+$/",$_GET["pdtID"]) || !isset($_GET["catID"]) || !preg_match("/^\d+$/",$_GET["catID"]))
				throw new Exception("Internal error : Product ID or Category ID not specified");
			
			$pdtID = $_GET["pdtID"];
			$catID = $_GET["catID"];
				
			$cur_friend_mail_time = time();
			if (!isset($session->last_friend_mail_time)) $session->last_friend_mail_time = 0;
			$time_left = $session->last_friend_mail_time + __MAIL_FLOOD_PROTECTION_TIME__ - $cur_friend_mail_time;
			if ($time_left <= 0) { // Flood protection is OK
				$session->last_friend_mail_time = $cur_friend_mail_time;
				$db = DBHandle::get_instance();
				$res = $db->query("
					SELECT
						pfr.name, pfr.ref_name
					FROM products_fr pfr
					INNER JOIN products_families pf ON pfr.id = pf.idProduct AND pf.idFamily = ".$catID."
					INNER JOIN advertisers a ON pfr.idAdvertiser = a.id AND a.actif = 1
					WHERE
						pfr.id = ".$pdtID."", __FILE__, __LINE__);
				if ($db->numrows($res) == 0)
					throw new Exception("Internal error : Product ID or Category ID not valid");
				$pdt = $db->fetchAssoc($res);
				$pdt["url"] = URL."produits/".$catID."-".$pdtID."-".$pdt["ref_name"].".html";
				
				require(ICLASS . '_ClassEmail.php');
				
				foreach($fel as $fe) {
					$mail_infos = array(
						"SITE_MAIN_URL" => URL,
						"FRIEND_EMAIL" => $fe,
						"CUSTOMER_EMAIL" => $umail,
						"PRODUCT_URL" => $pdt["url"]
					);
					
					$mail->Build($umail." a pensé qu'un produit pourrait vous intéresser sur Techni-Contact.com", "", "bloc-viral-produit", "From: Suggestion Techni-Contact <web@techni-contact.com>\n", $mail_infos);
                                        $mail->Send($fe.",info12@techni-contact.com,f.stumm@techni-contact.com,t.henryg@techni-contact.com");
					$mail->Save();
				}
				
				if ($fef == 1) $o["data"] = "1 email a été envoyé avec succès";
				else $o["data"] = $fef . " emails ont été envoyés avec succès";
			}
			break;
			
		case "sendestimate" :
			// Getting Cart ID
			// By default, the current cart is used. If a different one is specified, we do some checks
			$cartID = isset($_GET["cartID"]) ? $_GET["cartID"] : "";
			if (!preg_match("/^[0-9a-v]{26,32}$/",$cartID))
				throw new Exception("Bad Cart ID");
			$cart = new Cart($handle, $cartID);
			if (!$session->logged)
				throw new Exception("Session expired");
			if (!$cart->existsInDB)
				throw new Exception("Cart does not exist in DataBase");
			if ($cart->idClient != $session->userID)
				throw new Exception("The Cart does not belong to the customer");
			
			require(ICLASS."CCustomerUser.php");
			require(ICLASS."_ClassEmail.php");
			$user = new CustomerUser($handle, $session->userID);
			
			foreach($fel as $fe) {
				$mail_infos = array(
					"SITE_MAIN_URL" => URL,
					"FRIEND_EMAIL" => $fe,
					"CUSTOMER_LASTNAME" => $user->nom,
					"CUSTOMER_FIRSTNAME" => $user->prenom,
					"ESTIMATE_URL" => URL."pdf/devis_generate.php?cartID=".$cartID
				);
				
        $mail = new Email($handle);
				$mail->Build($user->prenom." ".$user->nom." souhaite vous faire voir un devis", "", "bloc-viral-devis", "From: Devis Techni-Contact <web@techni-contact.com>\n", $mail_infos);
				$mail->Send($fe.",info12@techni-contact.com,f.stumm@techni-contact.com,t.henryg@techni-contact.com");
				$mail->Save();
			}
			
			if ($fef == 1) $o["data"] = "1 email a été envoyé avec succès";
			else $o["data"] = $fef . " emails ont été envoyés avec succès";
			
			break;
			
		default :
			throw new Exception("The action " . $action . " does not exist");
			break;
	}

} catch (Exception $e) {
	$o["error"] = $e->getMessage();
}

//mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $o);
print json_encode($o);
exit();
