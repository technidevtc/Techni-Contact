<?php

/* Orders */
$fields = array(
	"order_id" => array("type" => "varchar", "length" => 50),
	"order_detail_id" => array("type" => "varchar", "length" => 255),
	"order_date" => array("type" => "datetime", "length" => 0),
	"email" => array("type" => "varchar", "length" => 255),
	"order_amount" => array("type" => "float", "length" => 0),
	"product_amount" => array("type" => "float", "length" => 0),
	"product_idfiche" => array("type" => "varchar", "length" => 50),
	"product_idtc" => array("type" => "varchar", "length" => 50),
	"product_qte" => array("type" => "int", "length" => 0),
	"order_promo" => array("type" => "int", "length" => 0),
	"order_codepromo" => array("type" => "varchar", "length" => 50),
	"order_type_paiement" => array("type" => "varchar", "length" => 50),
	"order_origin" => array("type" => "varchar", "length" => 100),
	"devis_id" => array("type" => "varchar", "length" => 50),
	"sendingid_prob" => array("type" => "int", "length" => 0)
);


// Opening file for write
$filename = CSV_PATH."probance/".date("Ymd")."_commandes.csv";
fwrite($flog, date("Y-m-d H:i:s")." CREATING FILE : ".$filename."\n");
$fh = fopen($filename, "w+");

// Writing col header
fwrite($fh, implode("|", array_keys($fields)));

/*
// Prepend old files for which upload failed
$fflist = array();
foreach(glob(CSV_PATH."probance/*_commandes.csv.failed") as $ff)
	$fflist[] = $ff;

sort($fflist);

foreach($fflist as $ff) {
	fwrite($flog, date("Y-m-d H:i:s")." PREPENDING PREVIOUS FAILED TO UPLOAD FILE : ".$ff."\n");
	$fflines = file($ff, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	$fflinesLen = count($fflines);
	for($fflk=1; $fflk<$fflinesLen; $fflk++) {
		fwrite($fh, "\n".$fflines[$fflk]);
	}
	unlink($ff);
}
*/

// Writing the file with multiple request to avoid memory hog
$time_current = $historic ? time() : mktime(23,40,00);
$time_interval = $historic ? 86400*30 : 86400*1; // if historic, 30 days
$time_origin = $historic ? mktime(0,0,0,1,1,2003) : $time_current-$time_interval; // if historic, origin = 01/01/2005 00:00:00
$time_start = $time_origin;
$time_end = $time_origin + $time_interval;

fwrite($flog, date("Y-m-d H:i:s")." WRITING FILE : ".$filename."\n");

$itemFields = array(
	"idProduct" => 0,
	"idTC" => 0,
	"name" => "",
	"fastdesc" => "",
	"label" => "",
	"price" => 0,
	"price2" => 0,
	"unite" => 1,
	"idFamily" => 0,
	"quantity" => 0,
	"tauxTVA" => 0,
	"promotion" => 0,
	"discount" => 0,
	"idAdvertiser" => 0,
	"refSupplier" => "",
	"comment" => "");

$TypePaiementList = array(
	0 => "Carte Bancaire",
	1 => "Carte Bancaire (Carte Bleue)",
	2 => "Carte Bancaire (Visa)",
	3 => "Carte Bancaire (Mastercard)",
	4 => "Carte Bancaire (American Express)",
	5 => "Paypal",
	10 => "Chèque",
	20 => "Virement bancaire",
	30 => "Paiement différé",
	40 => "Contre-remboursement",
	50 => "Mandat administratif");

while ($time_start <= $time_current) {
	
	// sql query
	$res = $db->query("
		SELECT
			o.id,
			o.timestamp,
			o.totalHT,
			o.promotionCode,
			o.type_paiement,
			o.coord,
			o.produits as items,
			c.login as email
		FROM commandes o, clients c
		WHERE o.idClient = c.id AND o.timestamp >= ".$time_start." AND o.timestamp < ".$time_end, __FILE__, __LINE__);
	
	while($o = $db->fetchAssoc($res)) {
		$coord = unserialize($o["coord"]);
		$data = unserialize($o["items"]);
		
		// Searching the idTC key position
		foreach($data[0] as $pos => $key) {
			if ($key == "idTC") {
				$idTCpos = $pos;
				break;
			}
		}
		if (!isset($idTCpos)) continue;
		
		$items = array();
		$size = count($data);
		
		// $data[0] = column headers, so we flip the array to get the indexes of each named header
		// $data[0] = array(0 => "col1", 1 => "col2", ..)  ==>  $headersIndexes = array("col1" => 0, "col2" => 1, ..)
		$headersIndexes = array_flip($data[0]);
		for ($j=1; $j<$size; $j++) {
			foreach ($itemFields as $fieldName => $dftValue) {
				$items[$data[$j][$idTCpos]][$fieldName] = isset($data[$j][$headersIndexes[$fieldName]]) ? $data[$j][$headersIndexes[$fieldName]] : "";
			}
		}
		//$itemCount = count($items);
		
		$oln = 1; // order line num
		foreach($items as $item) {
			// order line
			$ol = array(
				"order_id" => $o["id"],
				"order_detail_id" => $o["id"]."-".$oln,
				"order_date" => $o["timestamp"],
				"email" => $o["email"],
				"order_amount" => $o["totalHT"],
				"product_amount" => $item["price"]*$item["quantity"],
				"product_idfiche" => $item["idProduct"],
				"product_idtc" => $item["idTC"],
				"product_qte" => $item["quantity"],
				"order_promo" => empty($o["promotionCode"]) ? 0 : 1,
				"order_codepromo" => $o["promotionCode"],
				"order_type_paiement" => $TypePaiementList[$o["type_paiement"]],
				"order_origin" => "",
				"devis_id" => "",
				"sendingid_prob" => 0);
			
			foreach($ol as $k => &$v) {
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
			mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $ol);
			fwrite($fh, "\n".implode("|", $ol));
			$oln++;
		}
	}

	$time_start += $time_interval;
	$time_end += $time_interval;
}

fclose($fh);

?>
