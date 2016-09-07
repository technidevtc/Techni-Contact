<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

define("MAX_AUTOCOMPLETION_RESULTS", 10);
define("AUTOCOMPLETION_CATEGORIES_RESULT_COUNT", 5);
define("AUTOCOMPLETION_PRODUCTS_TITLE_RESULT_COUNT", 10);

include LANG_LOCAL_INC . "includes-" . DB_LANGUAGE . "_local.php";
include LANG_LOCAL_INC . "www-" . DB_LANGUAGE . "_local.php";
//include LANG_LOCAL_INC . "common-" . DB_LANGUAGE . "_local.php";
//include LANG_LOCAL_INC . "infos-" . DB_LANGUAGE . "_local.php";

$db = DBHandle::get_instance();

header("Content-Type: text/plain; charset=utf-8");

$q = isset($_GET["search"]) ? trim($_GET["search"]) : '';
$ql = strlen($q);

$terms = preg_split("`\s|-`", Utils::noDiphthong(urldecode($q)));
for ($i = 0; $i < count($terms); $i++) {
	if (Utils::is_plural($terms[$i])) {
		$terms[$i] = "".Utils::get_singular($terms[$i])."* <<".$terms[$i]."*";
	}
	else
		$terms[$i] = $terms[$i]."*";
	if ($i == 0) $terms[$i] = ">".$terms[$i];
}
$match_expr = mb_convert_encoding(implode(" ", $terms), "ISO-8859-1", "UTF-8");

if ($ql >= 1) {

	$results = array("total" => array("count" => 0, "start_time" => microtime(true), "end_time" => 0));

	// Search in products titles
	$results["products-title"] = array("data" => array(), "count" => 0, "start_time" => microtime(true), "end_time" => 0);
	if ($results["total"]["count"] < MAX_AUTOCOMPLETION_RESULTS) {
		$ressource = $db->query("
			SELECT
				pfr.name, count(pfr.id) AS count_ref,
				MATCH (pfr.name) AGAINST ('" . $db->escape($match_expr) . "' IN BOOLEAN MODE) AS score
			FROM products_fr pfr
      INNER JOIN products_families pf ON pfr.id = pf.idProduct
			WHERE MATCH (pfr.name) AGAINST ('" . $db->escape($match_expr) . "' IN BOOLEAN MODE)
			GROUP BY pfr.ref_name
			ORDER BY score DESC, pfr.name ASC");
		$results["products-title"]["end_time"] = microtime(true);
		while ($result = $db->fetchAssoc($ressource)) {
			array_push($results["products-title"]["data"], array(mb_convert_encoding($result["name"], "UTF-8","ISO-8859-1"), ""));
			$results["products-title"]["count"]++;
			$results["total"]["count"]++;
			if ($results["products-title"]["count"] >= AUTOCOMPLETION_PRODUCTS_TITLE_RESULT_COUNT) break;
			if ($results["total"]["count"] >= MAX_AUTOCOMPLETION_RESULTS) break;
		}
	}

	// Search in IDTC
	$results["references-idtc"] = array("data" => array(), "count" => 0, "start_time" => microtime(true), "end_time" => 0);
	if ($results["total"]["count"] < MAX_AUTOCOMPLETION_RESULTS) {
		$ressource = $db->query("
		SELECT rc.id, rc.classement, rc.idProduct, pfr.name, pfr.ref_name, pf.idFamily, count(rc.id) AS count_id
		FROM references_content rc
    INNER JOIN products_fr pfr ON rc.idProduct = pfr.id
    INNER JOIN products_families pf ON pfr.id = pf.idProduct
		WHERE MATCH (rc.id) AGAINST ('" . $db->escape($match_expr) . "' IN BOOLEAN MODE)
		GROUP BY rc.id
		ORDER BY rc.id ASC");
		$results["references-idtc"]["end_time"] = microtime(true);
		while ($result = $db->fetchAssoc($ressource)) {
			array_push($results["references-idtc"]["data"], array(mb_convert_encoding($result["id"], "UTF-8","ISO-8859-1"), ""));
			$results["references-idtc"]["count"]++;
			$results["total"]["count"]++;
			if ($results["total"]["count"] >= MAX_AUTOCOMPLETION_RESULTS) break;
		}
	}

	$results["total"]["end_time"] = microtime(true);

	$out = array_merge($results["products-title"]["data"], $results["references-idtc"]["data"]);
	echo json_encode($out);
}
else {
	echo json_encode(array());
}

/*
foreach($results as $k => $v) {
	print "\n" . $k . " = " . $v["count"] . " résults\t\tTime = " . ($v["end_time"]-$v["start_time"]) . "\n";
	print "data =" . print_r($v["data"], true) . "\n";
}
*/
?>