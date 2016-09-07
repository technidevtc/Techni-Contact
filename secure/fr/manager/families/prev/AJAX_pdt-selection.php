<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

header("Content-Type: text/plain; charset=utf-8");

require_once(ADMIN."logs.php");

$handle = DBHandle::get_instance();
$user = new BOUser();

if (!$user->login()) {
	$o["error"] = "Votre session a expirée, veuillez vous identifier à nouveau après avoir rafraichi votre page";
	print json_encode($o);
	exit();
}

$db = DBHandle::get_instance();
$o = array();
switch($_GET["action"]) {
	case "get": 
		if (!$user->get_permissions()->has("m-mark--sm-products-priorities","r")) {
      $o["error"] = "Vous n'avez pas les droits adéquats pour réaliser cette opération.";
      break;
    }
		$catID = isset($_GET["catID"]) ? (int)$_GET["catID"] : "";
		if (empty($catID)) { $o["error"] = "Category ID missing"; break; }
		$res = $db->query("SELECT f.pdt_overwrite FROM families f WHERE f.id = " . $catID, __FILE__, __LINE__);
		$row = $db->fetchAssoc($res);
		$pdt_overwrite = empty($row["pdt_overwrite"]) ? array() : mb_unserialize($row["pdt_overwrite"]);
		
		$pdt_infos = array();
		foreach($pdt_overwrite as $k => $v) {
			$asic = explode("|", $v); // Array of Selected Items by Categories
			$o[$k] = array();
			foreach($asic as $i => $asi) { // Array of Selected Items
				$asi = explode(",", $asi); // category and items
				$o[$k][$i] = array("catID" => $asi[0], "itemList" => array_slice($asi, 1));
				foreach($o[$k][$i]["itemList"] as $id)
					$pdt_infos[$id] = true;
			}
		}
		
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
				LEFT JOIN references_content rc ON p.id = rc.idProduct AND rc.classement = 1
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
				$pdt["pic_url"] = is_file(PRODUCTS_IMAGE_INC."thumb_small/".$pdt["id"]."-1.jpg") ? PRODUCTS_IMAGE_URL."thumb_small/".$pdt["id"]."-1.jpg" : PRODUCTS_IMAGE_URL."no-pic-thumb_small.gif";
				
				$pdt_infos[$pdt["id"]] = $pdt;
			}
			
			foreach($o as &$asic) {
				foreach($asic as &$asi) {
					foreach($asi["itemList"] as &$id) {
						$id = $pdt_infos[$id];
					}
				}
				unset($asi);
			}
			unset($asic);
		}
		break;
		
	case "set":
		if (!$user->get_permissions()->has("m-mark--sm-products-priorities","e")) {
      $o["error"] = "Vous n'avez pas les droits adéquats pour réaliser cette opération.";
      break;
    }
		$catID = isset($_GET["catID"]) ? (int)$_GET["catID"] : "";
		$type = isset($_GET["type"]) ? $_GET["type"] : "";
		$asic = isset($_GET["asic"]) ? $_GET["asic"] : "";
		if (empty($catID)) { $o["error"] = "Category ID missing"; break; }
		if (empty($type)) { $o["error"] = "Type of product list missing"; break; }
		//if (empty($asic)) { $o["error"] = "Array of Selected Items by Categories missing"; break; }
		
		$asic = explode("|", $asic); // items by category list
		$f_asic = array(); // Final Array of Selected Items by Categories
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
				if ($asi[$i])
					$f_asi[] = $asi[$i];
			}
			if (count($f_asi) > 1)
				$f_asic[] = implode(",", $f_asi);
		}
		$f_asic = implode("|", $f_asic);
		$res = $db->query("SELECT f.pdt_overwrite FROM families f WHERE f.id = " . $catID, __FILE__, __LINE__);
		$row = $db->fetchAssoc($res);
		$pdt_overwrite = empty($row["pdt_overwrite"]) ? array() : mb_unserialize($row["pdt_overwrite"]);
		$o[$type] = $pdt_overwrite[$type] = $f_asic;
		$db->query("UPDATE families f SET f.pdt_overwrite = '".$db->escape(serialize($pdt_overwrite))."' WHERE f.id = " . $catID, __FILE__, __LINE__);
		break;
		
	case "updateFO":
		if (!$user->get_permissions()->has("m-mark--sm-products-priorities","e")) {
      $o["error"] = "Vous n'avez pas les droits adéquats pour réaliser cette opération.";
      break;
    }
		include_once(CRON_PATH."xml/XML_Generator.php");
		break;
		
	default:
		$o["error"] = "Action type missing";
		break;
}

//mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $o);
print json_encode($o);

?>