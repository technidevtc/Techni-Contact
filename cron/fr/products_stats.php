<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$db = DBHandle::get_instance();

$stats_keys = array("id","hits","orders","estimates","leads","first_hit_time");
$pdt_stats = array();

$res = $db->query("
	SELECT id, " . FIRST_YEAR_STATS . " as first_hit_time
	FROM products");
while ($row = $db->fetchAssoc($res)) {
	foreach($stats_keys as $key)
		$pdt_stats[$row["id"]][$key] = 0;
	$pdt_stats[$row["id"]]["id"] = $row["id"];
	$pdt_stats[$row["id"]]["first_hit_time"] = $row["first_hit_time"];
}

$res = $db->query("
	SELECT idProduct, MIN(timestamp) as first_hit_time
	FROM stats_hit
	GROUP BY idProduct");
while ($row = $db->fetchAssoc($res)) {
	if (isset($pdt_stats[$row["idProduct"]]))
		if ($row["first_hit_time"] > FIRST_YEAR_STATS)
			$pdt_stats[$row["idProduct"]]["first_hit_time"] = $row["first_hit_time"];
}

$res = $db->query("
	SELECT idProduct, count(*) as hits
	FROM stats_hit
	WHERE timestamp > " . FIRST_YEAR_STATS . "
	GROUP BY idProduct");
while ($row = $db->fetchAssoc($res)) {
	if (isset($pdt_stats[$row["idProduct"]]))
		$pdt_stats[$row["idProduct"]]["hits"] = $row["hits"];
}

$res = $db->query("
	SELECT idProduct, count(*) as orders
	FROM stats_cmd
	WHERE timestamp > " . FIRST_YEAR_STATS . "
	GROUP BY idProduct");
while ($row = $db->fetchAssoc($res)) {
	if (isset($pdt_stats[$row["idProduct"]]))
		$pdt_stats[$row["idProduct"]]["orders"] = $row["orders"];
}

$res = $db->query("
	SELECT idProduct, count(*) as estimates
	FROM stats_esti
	WHERE timestamp > " . FIRST_YEAR_STATS . "
	GROUP BY idProduct");
while ($row = $db->fetchAssoc($res)) {
	if (isset($pdt_stats[$row["idProduct"]]))
		$pdt_stats[$row["idProduct"]]["estimates"] = $row["estimates"];
}

$res = $db->query("
	SELECT idProduct, count(*) as leads
	FROM contacts
	WHERE timestamp > " . FIRST_YEAR_STATS . "
	GROUP BY idProduct");
while ($row = $db->fetchAssoc($res)) {
	if (isset($pdt_stats[$row["idProduct"]]))
		$pdt_stats[$row["idProduct"]]["leads"] = $row["leads"];
}

$db->query("TRUNCATE TABLE products_stats");
foreach($pdt_stats as &$pdt) {
	$db->query("
		INSERT INTO products_stats
			(id, hits, orders, estimates, leads, first_hit_time)
		VALUES
			(" . $pdt["id"] . "," . $pdt["hits"] . "," . $pdt["orders"] . "," . $pdt["estimates"] . "," . $pdt["leads"] . ", " . $pdt["first_hit_time"] . ")");
}
?>