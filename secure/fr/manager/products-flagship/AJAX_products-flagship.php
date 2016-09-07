<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

require(ADMIN."logs.php");

$handle = DBHandle::get_instance();
$user = new BOUser();

header("Content-Type: text/plain; charset=utf-8");

if (!$user->login()) {
	$o["error"] = "Votre session a expirée, veuillez vous identifier à nouveau après avoir rafraichi votre page";
	print json_encode($o);
	exit();
}

$db = DBHandle::get_instance();
$o = array();
switch($_GET["action"]) {
	case "get": 
		if (!$user->get_permissions()->has("m-mark--sm-flagship-products","r")) {
      $o["error"] = "Vous n'avez pas les droits adéquats pour réaliser cette opération.";
      break;
    }
		$res = $db->query("SELECT pflag.idProduct, pflag.idFamily FROM products_flagship pflag ORDER BY pflag.`order`", __FILE__, __LINE__);
		while ($row = $db->fetchAssoc($res)) $o[] = $row;
		
		$pdt_infos = array();
		foreach($o as $v)
			$pdt_infos[$v["idProduct"]] = true;
		
		if (!empty($pdt_infos)) {
			$res = $db->query("
				SELECT
					p.id, p.price AS pdt_price,
					pfr.name, pfr.ref_name, pfr.fastdesc,
					pf.idFamily AS catID,
					rc.price AS ref_price
				FROM products p
				INNER JOIN products_fr pfr ON p.id = pfr.id
				INNER JOIN products_families pf ON p.id = pf.idProduct
				LEFT JOIN references_content rc ON p.id = rc.idProduct AND rc.classement = 1 AND rc.vpc = 1 AND rc.deleted = 0
				WHERE p.id in (".implode(",", array_keys($pdt_infos)).")
				GROUP BY p.id", __FILE__, __LINE__);
			
			while ($pdt = $db->fetchAssoc($res)) {
				$pdt["hasPrice"] = false;
				$pdt["price"] = $pdt["pdt_price"];
				if ($pdt["price"] == "ref") {
					$pdt["price"] = $pdt["ref_price"];
				}
				if (empty($pdt["price"])) { // no price
					$pdt["price"] = "sur devis";
				}
				elseif (preg_match('/^[0-9]+((\.|,)[0-9]+){0,1}$/', $pdt["price"])) { // real price
					$pdt["hasPrice"] = true;
				}
				$pdt["url"] = URL."produits/".$pdt["catID"]."-".$pdt["id"]."-".$pdt["ref_name"].".html";
				$pdt["admin_url"] = ADMIN_URL."products/edit.php?id=".$pdt["id"];
				$pdt["pic_url"] = is_file(PRODUCTS_IMAGE_INC."thumb_small/".$pdt["id"]."-1.jpg") ? PRODUCTS_IMAGE_SECURE_URL."thumb_small/".$pdt["id"]."-1.jpg" : PRODUCTS_IMAGE_SECURE_URL."no-pic-thumb_small.gif";
				
				$pdt_infos[$pdt["id"]] = $pdt;
			}
			
			foreach($o as $k => $v) {
				$o[$k] = $pdt_infos[$v["idProduct"]];
				if (empty($o[$k]["catID"])) $o[$k]["catID"] = $v["idFamily"];
			}
		}
		break;
		
	case "set":
		if (!$user->get_permissions()->has("m-mark--sm-flagship-products","e")) {
      $o["error"] = "Vous n'avez pas les droits adéquats pour réaliser cette opération.";
      break;
    }
		$ssi = isset($_GET["ssi"]) ? $_GET["ssi"] : "";
		
		$asic = explode("|", $ssi); // items by category list
		$f_asic = array(); // Final Array of Selected Items by Categories
		$db->query("TRUNCATE TABLE products_flagship");
		$order = 1;
		foreach($asic as $asi) {
			$asi = explode(",", $asi); // category and items
			$asi[0] = (int)($asi[0]);
			if (!$asi[0]) continue;
			$nbi = count($asi)-1;
			if ($nbi < 1) continue;
			$f_asi = array();
			$f_asi[0] = $asi[0];
			for ($i = 1; $i <= $nbi; $i++) {
				$asi[$i] = (int)$asi[$i];
				if ($asi[$i]) {
					$f_asi[] = $asi[$i];
					$db->query("INSERT INTO `products_flagship` (`idProduct`, `idFamily`, `order`) VALUES (".$asi[$i].", ".$asi[0].", ".$order++.")", __FILE__, __LINE__);
				}
			}
			if (count($f_asi) > 1)
				$f_asic[] = implode(",", $f_asi);
		}
		$f_asic = implode("|", $f_asic);
		$o[] = $f_asic;
		break;
		
	case "updateFO":
		if (!$user->get_permissions()->has("m-mark--sm-flagship-products","e")) {
      $o["error"] = "Vous n'avez pas les droits adéquats pour réaliser cette opération.";
      break;
    }
		include_once(CRON_PATH."xml/XML_Generator.php");
		break;
		
	default:
		$o["error"] = "Action type missing";
		break;
}

mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $o);
print json_encode($o);

?>