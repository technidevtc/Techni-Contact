<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

/*if(!empty ($_GET["nbResults"]) && is_numeric($_GET["nbResults"]) && $_GET["nbResults"]>0 && $_GET["nbResults"]<100)
  define("MAX_AUTOCOMPLETION_RESULTS", $_GET["nbResults"]);
else*/
  define("MAX_AUTOCOMPLETION_RESULTS", 10);
define("AUTOCOMPLETION_CATEGORIES_RESULT_COUNT", 5);
define("AUTOCOMPLETION_PRODUCTS_TITLE_RESULT_COUNT", 5);

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
$match_expr = implode(" ", $terms);

if ($ql >= 1) {

	$results = array("total" => array("count" => 0, "start_time" => microtime(true), "end_time" => 0));

// Search in categories
	$results["categories"] = array("data" => array(), "count" => 0, "start_time" => microtime(true), "end_time" => 0);
	if ($results["total"]["count"] < MAX_AUTOCOMPLETION_RESULTS) {
		$ressource = $db->query("
			select
				ffr.name, ffr.id, ffr.ref_name,
				MATCH (ffr.name) AGAINST ('" . $db->escape($match_expr) . "' IN BOOLEAN MODE) as score,
                                (select count(idProduct) from products_families pf inner join products_fr pfr on pfr.id = pf.idProduct where idFamily = ffr.id and pfr.deleted != 1) as count_product
			from
				families_fr ffr
			where
				MATCH (ffr.name) AGAINST ('" . $db->escape($match_expr) . "' IN BOOLEAN MODE)
			order by
				score desc, ffr.name asc");
                
		$results["categories"]["end_time"] = microtime(true);
		while ($result = $db->fetchAssoc($ressource)) {
                        if($result['count_product'] != 0){
                          array_push($results["categories"]["data"], array($result["name"],$result['count_product'],$result['ref_name']));
                        
                          $results["categories"]["count"]++;
                          $results["total"]["count"]++;
                          if ($results["categories"]["count"] >= AUTOCOMPLETION_CATEGORIES_RESULT_COUNT) break;
                          if ($results["total"]["count"] >= MAX_AUTOCOMPLETION_RESULTS) break;
                        }
		}
	}

	// Search in products titles
	$results["products-title"] = array("data" => array(), "count" => 0, "start_time" => microtime(true), "end_time" => 0);
	if ($results["total"]["count"] < MAX_AUTOCOMPLETION_RESULTS) {
		$ressource = $db->query("
			select
				pfr.name, (select count(idFamily) from products_families where idProduct = pfr.id) as count_families, pfr.id, pfr.fastdesc, pfr.ref_name,
                                (select idFamily from products_families where idProduct = pfr.id and orderFamily <= 1 LIMIT 0,1) as cat_id,
				MATCH (pfr.name) AGAINST ('" . $db->escape($match_expr) . "' IN BOOLEAN MODE) as score
			from
				products_fr pfr, advertisers a
			where
				pfr.idAdvertiser = a.id and
				a.actif = 1 and
                                pfr.active = 1 and
                                pfr.deleted != 1 and
				MATCH (pfr.name) AGAINST ('" . $db->escape($match_expr) . "' IN BOOLEAN MODE)
			group by
				pfr.ref_name
			order by
				score desc, pfr.name asc");

		$results["products-title"]["end_time"] = microtime(true);
		while ($result = $db->fetchAssoc($ressource)) {
			array_push($results["products-title"]["data"], array($result["name"], $result['count_families'], $result["id"], $result['fastdesc'], URL.'produits/'.$result['cat_id'].'-'.$result['id'].'-'.$result['ref_name'].'.html'));
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
		select
			rc.id, rc.classement, rc.idProduct, pfr.name, pfr.ref_name, pf.idFamily, (select count(idFamily) from products_families where idProduct = pfr.id) as count_families,  pfr.fastdesc
		from
			references_content rc, products_fr pfr, products_families pf, advertisers a
		where
			rc.idProduct = pfr.id and
      rc.vpc = 1 AND
      rc.deleted = 0 AND
			pfr.id = pf.idProduct and
			pfr.idAdvertiser = a.id and
			a.actif = 1 and
                        pfr.active = 1 and
                        pfr.deleted != 1 and
			MATCH (rc.id) AGAINST ('" . $db->escape($match_expr) . "' IN BOOLEAN MODE)
		group
			by rc.id
		order
			by rc.id asc");

		$results["references-idtc"]["end_time"] = microtime(true);
		while ($result = $db->fetchAssoc($ressource)) {
			array_push($results["references-idtc"]["data"], array($result["name"].' '.$result["id"], $result["count_families"], $result["idProduct"], $result['fastdesc'], URL.'produits/'.$result['idFamily'].'-'.$result['idProduct'].'-'.$result['ref_name'].'.html'));
			$results["references-idtc"]["count"]++;
			$results["total"]["count"]++;
			if ($results["total"]["count"] >= MAX_AUTOCOMPLETION_RESULTS) break;
		}
	}

	$results["total"]["end_time"] = microtime(true);

	$out['categories'] = $results["categories"]["data"];// $results["products-title"]["data"], $results["references-idtc"]["data"]);
	$out['products'] = array_merge($results["products-title"]["data"], $results["references-idtc"]["data"]);
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
