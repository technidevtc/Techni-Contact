<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

header("Content-Type: text/plain; charset=utf-8");

require_once(ADMIN."logs.php");
require_once(ADMIN."logo.php");
require_once(ICLASS."ExtranetUser.php");

$handle = DBHandle::get_instance();
$user = new ExtranetUser($handle);

if (!$user->login($login, $pass) || !$user->active) {
	$o["error"] = "Votre session a expirée, veuillez vous identifier à nouveau après avoir rafraichi votre page";
	print json_encode($o);
	exit();
}

$db = DBHandle::get_instance();
$o = array();

$pdtID = isset($_GET["pdtID"]) ? (int)$_GET["pdtID"] : "";
if (empty($pdtID)) { $o["error"] = "Product ID missing"; break; }

$waitingForValidation = false; // If the product is already waiting for validation
$res = $db->query("select id from products_add_adv where id =".$pdtID." and idAdvertiser = ".$user->id, __FILE__, __LINE__);
if ($db->numrows($res, __FILE__, __LINE__) != 1) {
	$res = $db->query("select id from products where id =".$pdtID." and idAdvertiser = ".$user->id, __FILE__, __LINE__);
	if ($db->numrows($res, __FILE__, __LINE__) != 1) {
		$o["error"] = "There is no product with ID ".$pdtID." that owns to you";
	}
}
else
	$waitingForValidation = true;

if (empty($o["error"])) {
	$o["pdtID"] = $pdtID;
	switch($_GET["action"]) {
		case "getpics": 
			$dirINC = PRODUCTS_IMAGE_ADV_INC;
			$dirURL = PRODUCTS_IMAGE_ADV_URL;
			
			$o["pics"] = array();
			$num = 1;
			if (!$waitingForValidation && !is_file($dirINC."zoom/".$pdtID."-".$num.".jpg")) {
				while (is_file(PRODUCTS_IMAGE_INC."zoom/".$pdtID."-".$num.".jpg")) {
					$fileName = $pdtID."-".$num.".jpg";
					copy(PRODUCTS_IMAGE_INC."zoom/".$fileName, PRODUCTS_IMAGE_ADV_INC."zoom/".$fileName);
					copy(PRODUCTS_IMAGE_INC."card/".$fileName, PRODUCTS_IMAGE_ADV_INC."card/".$fileName);
					copy(PRODUCTS_IMAGE_INC."thumb_big/".$fileName, PRODUCTS_IMAGE_ADV_INC."thumb_big/".$fileName);
					copy(PRODUCTS_IMAGE_INC."thumb_small/".$fileName, PRODUCTS_IMAGE_ADV_INC."thumb_small/".$fileName);
					$num++;
				}
				$num = 1;
			}
			while (is_file($dirINC."zoom/".$pdtID."-".$num.".jpg")) {
				$fileName = $pdtID."-".$num.".jpg";
				$o["pics"][] = array(
					"num" => $num,
					"zoom" => $dirURL."zoom/".$fileName,
					"card" => $dirURL."card/".$fileName,
					"thumb_big" => $dirURL."thumb_big/".$fileName,
					"thumb_small" => $dirURL."thumb_small/".$fileName
				);
				$num++;
			}
			break;
			
		case "delpics":
			$dirINC = PRODUCTS_IMAGE_ADV_INC;
			
			$num = isset($_GET["num"]) ? explode(",", $_GET["num"]) : array("1");
			foreach($num as $n) {
				deleteImageAndProceed($pdtID, $dirINC, $n);
			}
			$o["success"] = true;
			
			break;
			
		default:
			$o["error"] = "Action type missing";
			break;
	}
}

mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $o);
print json_encode($o);

?>