<?php

/* Products */
$fields = array(
	"product_idfiche" => array("type" => "varchar", "length" => 50),
	"product_idtc" => array("type" => "varchar", "length" => 50),
	"product_univers" => array("type" => "varchar", "length" => 20),
	"product_category" => array("type" => "varchar", "length" => 60),
	"product_subcategory" => array("type" => "varchar", "length" => 100),
	"product_name" => array("type" => "varchar", "length" => 100),
	"product_marque" => array("type" => "varchar", "length" => 60)
);

// Loading XML
$dom = new DomDocument();
$dom->validateOnParse = true;
$dom->load(XML_CATEGORIES_ALL);
$xPath = new DOMXPath($dom);

// Constant var
$brand = "Techni-Contact";

// Opening file for write
$filename = CSV_PATH."probance/".date("Ymd")."_produits.csv";
fwrite($flog, date("Y-m-d H:i:s")." CREATING FILE : ".$filename."\n");
$fh = fopen($filename, "w+");

// Writing col header
fwrite($fh, implode("|", array_keys($fields)));

/*// Prepend old files for which upload failed
$fflist = array();
foreach(glob(CSV_PATH."probance/*_produits.csv.failed") as $ff)
	$fflist[] = $ff;

sort($fflist);

foreach($fflist as $ff) {
	fwrite($flog, date("Y-m-d H:i:s")." DELETING PREVIOUS FAILED TO UPLOAD FILE : ".$ff."\n");
	unlink($ff);
}
*/

// Writing the file with multiple request to avoid memory hog
$time_current = time();
$time_interval = 86400*90;
$time_origin = mktime(0,0,0,1,1,2003);
$time_start = $time_origin;
$time_end = $time_origin + $time_interval;

fwrite($flog, date("Y-m-d H:i:s")." WRITING FILE : ".$filename."\n");
while ($time_start <= $time_current) {
	
	// sql query
	$res = $db->query("
		SELECT
			p.id AS product_idfiche,
			p.idTC,
			rc.id AS product_idtc,
			null AS product_univers,
			null AS product_category,
			pf.idFamily AS product_subcategory,
			pfr.name AS product_name,
			a.nom1 AS product_marque
		FROM products p
		INNER JOIN advertisers a ON p.idAdvertiser = a.id
		INNER JOIN products_fr pfr ON p.id = pfr.id
		INNER JOIN products_families pf ON p.id = pf.idProduct
		LEFT JOIN references_content rc ON p.id = rc.idProduct AND rc.vpc = 1 AND rc.deleted = 0
		WHERE p.timestamp >= ".$time_start." AND p.timestamp < ".$time_end."
		GROUP BY p.id, rc.id
		ORDER BY p.timestamp asc, rc.classement asc", __FILE__, __LINE__);
		
	while($pdt = $db->fetchAssoc($res)) {
		$cat3Node = $dom->getElementById(XML_KEY_PREFIX.$pdt["product_subcategory"]);
		if ($cat3Node) {
			$cat3 = $xPath->query("parent::category",$cat3Node)->item(0);
			$catTree = $xPath->query("ancestor-or-self::category", $cat3);
			$cat1 = $catTree->item(0);
			$cat2 = $catTree->item(1);
			
			if (empty($pdt["product_idtc"])) $pdt["product_idtc"] = $pdt["idTC"];
			unset($pdt["idTC"]);
			foreach($pdt as $k => &$v) {
				switch($k) {
					case "product_univers": $v = $cat1->getAttribute("name"); break;
					case "product_category": $v = $cat2->getAttribute("name"); break;
					case "product_subcategory": $v = $cat3->getAttribute("name"); break;
					default: $v = mb_convert_encoding($v, "UTF-8", "ASCII,UTF-8,ISO-8859-1"); break;
				}
				switch($fields[$k]["type"]) {
					case "int": $v = (int)$v; break;
					case "float": $v = (float)$v; break;
					case "varchar": $v = "\"".preg_replace(array("/\r\n/","/\n/","/\r/",'/"/',"/\\\\$/"), "", trim(substr($v,0,$fields[$k]["length"])))."\""; break;
					//case "varchar": $v = filter_var(trim($v), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW); break;
					//preg_replace('/&euro;/i', '€', html_entity_decode(filter_var(trim($v), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW), ENT_QUOTES)); break;
					case "datetime": $v = empty($v)?"\"\"":"\"".date("Y-m-d H:i:s", $v)."\""; break;
					default: break;
				}
				if ($v === "\"\"") $v = "\N";
			}
			unset($v);
			
			fwrite($fh, "\n".implode("|", $pdt));
		}
	}
	
	$time_start += $time_interval;
	$time_end += $time_interval;
}

fclose($fh);

?>
