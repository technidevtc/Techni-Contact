<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$db = DBHandle::get_instance();

$handle = DBHandle::get_instance();
$user = new BOUser();

if (!$user->login()) {
	header("Location: ".ADMIN_URL."login.html");
	exit();
}

function generateProductID() {
	$db = $GLOBALS['db'];
	do {
		$id = mt_rand(0, 16777215);
		$res = $db->query("SELECT id FROM products WHERE id = ".$id, __FILE__, __LINE__);
	} while ($db->numrows($res, __FILE__, __LINE__) > 0);
	return $id;
}

/* Générer un identifiant (compatibilité id utilisés dans me MDD excepté pour les users & temppass)
   i : réf handle connexion
   o : false ou id généré */
function generateIDTC() {
	$db = $GLOBALS['db'];
	do {
		$idTC = mt_rand(0,999999999);

		$res = $db->query("SELECT idTC FROM products WHERE idTC = ".$idTC, __FILE__, __LINE__);
		if ($db->numrows($res, __FILE__, __LINE__) > 0) continue;

		$res = $db->query("SELECT idTC FROM products_add WHERE idTC = ".$idTC, __FILE__, __LINE__);
		if ($db->numrows($res, __FILE__, __LINE__) > 0) continue;

		$res = $db->query("SELECT idTC FROM products_add_adv WHERE idTC = ".$idTC, __FILE__, __LINE__);
		if ($db->numrows($res, __FILE__, __LINE__) > 0) continue;

		$res = $db->query("SELECT id FROM references_content WHERE id = ".$idTC, __FILE__, __LINE__);
		if ($db->numrows($res, __FILE__, __LINE__) > 0) continue;

		break;
		
	} while(true);
	
	return $idTC;
}

if ($user->rank == CONTRIB || !isset($_GET["id"]) || !preg_match("/^[0-9]+$/",$_GET["id"])) {
	header("Location: ".ADMIN_URL."products/edit.php?id=".$_GET["id"]);
	exit();
}
$id = (int)$_GET["id"];

$res = $db->query("SELECT * FROM products WHERE id = ".$id, __FILE__, __LINE__);
if ($db->numrows($res, __FILE__, __LINE__) != 1) {
	header("Location: ".ADMIN_URL."products/edit.php?id=".$_GET["id"]);
	exit();
}

$idProduct = generateProductID();
$idTC = generateIDTC();

// main table
$pdt = $db->fetchAssoc($res);
$pdt["id"] = $idProduct;
$pdt["idTC"] = $idTC;
$pdt["timestamp"] = time();
$pdt["create_time"] = time();
$docs = mb_unserialize($pdt["docs"]);
foreach($pdt as &$v) $v = $db->escape($v); unset($v);
$db->query("INSERT INTO products (`".implode("`,`",array_keys($pdt))."`) VALUES ('".implode("','",$pdt)."')", __FILE__, __LINE__);

// local language table
$res = $db->query("select * from products_fr where id = ".$id, __FILE__, __LINE__);
$pdt_fr = $db->fetchAssoc($res);
$pdt_fr["id"] = $idProduct;
$pdt_fr["name"] = "Copie de ".$pdt_fr["name"];
foreach($pdt_fr as &$v) $v = $db->escape($v); unset($v);
$db->query("INSERT INTO products_fr (`".implode("`,`",array_keys($pdt_fr))."`) VALUES ('".implode("','",$pdt_fr)."')", __FILE__, __LINE__);

// stats table
$db->query("INSERT INTO products_stats (`id`, `hits`, `orders`, `estimates`, `leads`, `first_hit_time`) VALUES (".$idProduct.",0,0,0,0,0)", __FILE__, __LINE__);

// Categories table
$res = $db->query("select * from products_families where idProduct = ".$id, __FILE__, __LINE__);
while ($pdt_fam = $db->fetchAssoc($res)) {
	$pdt_fam["idProduct"] = $idProduct;
	foreach($pdt_fam as &$v) $v = $db->escape($v); unset($v);
	$db->query("INSERT INTO products_families (`".implode("`,`",array_keys($pdt_fam))."`) VALUES ('".implode("','",$pdt_fam)."')", __FILE__, __LINE__);
}

// Linked products
$res = $db->query("select * from productslinks where idProduct = ".$id, __FILE__, __LINE__);
while ($pdt_links = $db->fetchAssoc($res)) {
	$pdt_links["idProduct"] = $idProduct;
	foreach($pdt_links as &$v) $v = $db->escape($v); unset($v);
	$db->query("INSERT INTO productslinks (`".implode("`,`",array_keys($pdt_links))."`) VALUES ('".implode("','",$pdt_links)."')", __FILE__, __LINE__);
}

// References cols headers
$res = $db->query("select * from references_cols where idProduct = ".$id, __FILE__, __LINE__);
$ref_cols = $db->fetchAssoc($res);
$ref_cols["idProduct"] = $idProduct;
foreach($ref_cols as &$v) $v = $db->escape($v); unset($v);
$db->query("INSERT INTO references_cols (`".implode("`,`",array_keys($ref_cols))."`) VALUES ('".implode("','",$ref_cols)."')", __FILE__, __LINE__);

// References
$res = $db->query("SELECT * FROM references_content WHERE idProduct = ".$id." AND deleted = 0", __FILE__, __LINE__);
while ($refs = $db->fetchAssoc($res)) {
	$refs["id"] = generateIDTC();
	$refs["idProduct"] = $idProduct;
	foreach($refs as &$v) $v = $db->escape($v); unset($v);
	$db->query("INSERT INTO references_content (`".implode("`,`",array_keys($refs))."`) VALUES ('".implode("','",$refs)."')", __FILE__, __LINE__);
}

// Images
$dir = PRODUCTS_IMAGE_INC;
$num = 1;
while (is_file($dir."zoom/".$id."-".$num.".jpg")) {
	@copy($dir."zoom/".$id."-".$num.".jpg", $dir."zoom/".$idProduct."-".$num.".jpg");
	@copy($dir."card/".$id."-".$num.".jpg", $dir."card/".$idProduct."-".$num.".jpg");
	@copy($dir."thumb_big/".$id."-".$num.".jpg", $dir."thumb_big/".$idProduct."-".$num.".jpg");
	@copy($dir."thumb_small/".$id."-".$num.".jpg", $dir."thumb_small/".$idProduct."-".$num.".jpg");
	$num++;
}

// Documents
$dir = PRODUCTS_FILES_INC;
if (is_array($docs)) {
	foreach($docs as $doc) {
		if ($doc["uploaded"])
			@copy($dir.$id."-".$doc["num"].".pdf", $dir.$idProduct."-".$doc["num"].".pdf");
	}
}

header("Location: ".ADMIN_URL."products/edit.php?id=".$idProduct);
exit();

?>
