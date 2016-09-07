<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

require_once(ICLASS."ManagerUser.php");

$handle = DBHandle::get_instance();
$user = new ManagerUser($handle);

if (!$user->login()) {
	print "not logged";
	exit();
}

$db = DBHandle::get_instance();

$type = "";

if ($type == "adv")
	$dirINC = PRODUCTS_FILES_ADV_INC;
else
	$dirINC = PRODUCTS_FILES_INC;

$pdt_num = 0;
$pdt_count = 0;
$doc_count = 0;
$res = $db->query("SELECT `id` FROM products", __FILE__, __LINE__);
while ($pdt = $db->fetchAssoc($res)) {
	echo "Processing product: ".$pdt["id"]."<br/>\n";
	//$docsDB = mb_unserialize($pdt["docs"]);
	$docsDB = array();
	
	// If there is docs in the FS that aren't in the DB
	$docsFS = array();
	$num = 0;
	while (is_file($dirINC.$pdt["id"]."-".($num+1).".pdf")) {
		$docsDB[$num] = array(
			"title" => "Documentation ".($num+1),
			"filename" => "documentation-".($num+1),
			"num" => $num+1,
			"uploaded" => 2,
			"filesize" => filesize($dirINC.$pdt["id"]."-".($num+1).".pdf"));
		echo "--> Adding documentation ".($num+1)." with a size of ".$docsDB[$num]["filesize"]." bytes<br/>\n";
		$num++;
		$doc_count++;
	}
	if ($num)
		$pdt_count++;
	
	$db->query("update products set `docs` = '".$db->escape(serialize($docsDB))."' where id = ".$pdt["id"], __FILE__, __LINE__);
	echo "Saving product: ".$pdt["id"]."<br/>\n";
	echo "<br/>\n";
	$pdt_num++;
	if ($pdt_num%1000 == 0) {
		echo "----------------------------------------------------------------------------------------------------------------------------------------------------------------"."<br/>\n";
		echo $pdt_num." products saved<br/>\n";
		echo $pdt_count." products having documentation (average of ".sprintf("%.02f", $doc_count/$pdt_count)." documentations per product)<br/>\n";
		echo $doc_count." total documentations<br/>\n";
		echo "----------------------------------------------------------------------------------------------------------------------------------------------------------------"."<br/>\n";
	}
}

?>