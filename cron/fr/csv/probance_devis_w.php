<?php

/* Contacts */
$fields = array(
	"devis_id" => array("type" => "varchar", "length" => 50),
	"devis_detail_id" => array("type" => "varchar", "length" => 50),
	"devis_date" => array("type" => "datetime", "length" => 0),
	"email" => array("type" => "varchar", "length" => 255),
	"devis_type" => array("type" => "varchar", "length" => 1),
	"devis_annonceur" => array("type" => "varchar", "length" => 50),
	"devis_amount" => array("type" => "float", "length" => 0),
	"product_amount" => array("type" => "float", "length" => 0),
	"product_idfiche" => array("type" => "varchar", "length" => 50),
	"product_idtc" => array("type" => "varchar", "length" => 50),
	"product_qte" => array("type" => "int", "length" => 0),
	"devis_promo" => array("type" => "int", "length" => 0),
	"devis_codepromo" => array("type" => "varchar", "length" => 50),
	"devis_type_paiement" => array("type" => "varchar", "length" => 50),
	"devis_origin" => array("type" => "varchar", "length" => 100),
	"sendingid_prob" => array("type" => "int", "length" => 0)
);

// Opening file for write
$filename = CSV_PATH."probance/".date("Ymd")."_devis_w.csv";
fwrite($flog, date("Y-m-d H:i:s")." CREATING FILE : ".$filename."\n");
$fh = fopen($filename, "w+");

// Writing col header
fwrite($fh, implode("|", array_keys($fields)));

/*
// Prepend old files for which upload failed
$fflist = array();
foreach(glob(CSV_PATH."probance/*_devis_w.csv.failed") as $ff)
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
while ($time_start <= $time_current) {
	
	// sql query
	$res = $db->query("
		SELECT
			c.id AS devis_id,
			c.id AS devis_detail_id,
			c.timestamp AS devis_date,
			c.email,
			a.category AS devis_type,
			a.nom1 AS devis_annonceur,
			c.income_total AS devis_amount,
			null AS product_amount,
			c.idProduct AS product_idfiche,
			null AS product_idtc,
			1 AS product_qte,
			null AS devis_promo,
			null AS devis_codepromo,
			null AS devis_type_paiement,
			null AS devis_origin,
			c.campaignID AS sendingid_prob
		FROM contacts c, advertisers a
		WHERE
			c.idAdvertiser = a.id AND
			a.category != ".__ADV_CAT_SUPPLIER__." AND
			c.parent = 0 AND
			c.timestamp >= ".$time_start." AND
			c.timestamp < ".$time_end."
		ORDER BY c.timestamp ASC", __FILE__, __LINE__);
	
	while($c = $db->fetchAssoc($res)) {
		foreach($c as $k => &$v) {
			switch ($k) {
				case "email": $v = strtolower($v); break;
				case "devis_type": $v = $v == 1 ? "F" : "A"; break;
				default: break;
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
		mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $c);
		fwrite($fh, "\n".implode("|", $c));
	}

	$time_start += $time_interval;
	$time_end += $time_interval;
}

fclose($fh);

?>
