<?php

/* Client */
$fields = array(
	"id" => array("type" => "int", "length" => ""),
	"email" => array("type" => "varchar", "length" => "255"),
	"client_date" => array("type" => "datetime", "length" => ""),
	"login" => array("type" => "varchar", "length" => "255"),
	"pass" => array("type" => "varchar", "length" => "255"),
	"titre" => array("type" => "varchar", "length" => "255"),
	"nom" => array("type" => "varchar", "length" => "255"),
	"prenom" => array("type" => "varchar", "length" => "255"),
	"fonction" => array("type" => "varchar", "length" => "255"),
	"societe" => array("type" => "varchar", "length" => "255"),
	"nb_salarie" => array("type" => "varchar", "length" => "255"),
	"secteur_activite" => array("type" => "varchar", "length" => "255"),
	"code_naf" => array("type" => "varchar", "length" => "255"),
	"num_siret" => array("type" => "varchar", "length" => "255"),
	"adresse" => array("type" => "varchar", "length" => "255"),
	"complement" => array("type" => "varchar", "length" => "255"),
	"ville" => array("type" => "varchar", "length" => "255"),
	"cp" => array("type" => "varchar", "length" => "255"),
	"pays" => array("type" => "varchar", "length" => "255"),
	"titre_l" => array("type" => "varchar", "length" => "255"),
	"nom_l" => array("type" => "varchar", "length" => "255"),
	"prenom_l" => array("type" => "varchar", "length" => "255"),
	"fonction_l" => array("type" => "varchar", "length" => "255"),
	"societe_l" => array("type" => "varchar", "length" => "255"),
	"adresse_l" => array("type" => "varchar", "length" => "255"),
	"complement_l" => array("type" => "varchar", "length" => "255"),
	"ville_l" => array("type" => "varchar", "length" => "255"),
	"cp_l" => array("type" => "varchar", "length" => "255"),
	"pays_l" => array("type" => "varchar", "length" => "255"),
	"coord_livraison" => array("type" => "int", "length" => ""),
	"tel1" => array("type" => "varchar", "length" => "255"),
	"tel2" => array("type" => "varchar", "length" => "255"),
	"fax1" => array("type" => "varchar", "length" => "255"),
	"fax2" => array("type" => "varchar", "length" => "255"),
	"url" => array("type" => "varchar", "length" => "255"),
	"actif" => array("type" => "int", "length" => ""),
	"optin" => array("type" => "varchar", "length" => "1"),
	"date_last_maj_optin" => array("type" => "datetime", "length" => "")
);

// Opening file for write
$filename = CSV_PATH."probance/".date("Ymd")."_clients_prospects_w.csv";
fwrite($flog, date("Y-m-d H:i:s")." CREATING FILE : ".$filename."\n");
$fh = fopen($filename, "w+");


// Writing col header
fwrite($fh, implode("|", array_keys($fields)));

/*
// Prepend old files for which upload failed
$fflist = array();
foreach(glob(CSV_PATH."probance/*_clients_prospects_w.csv.failed") as $ff)
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
$time_origin = $historic ? mktime(0,0,0,1,1,2005) : $time_current-$time_interval; // if historic, origin = 01/01/2005 00:00:00
$time_start = $time_origin;
$time_end = $time_origin + $time_interval;

fwrite($flog, date("Y-m-d H:i:s")." WRITING FILE : ".$filename."\n");
while ($time_start <= $time_current) {

	$res = $db->query("
		SELECT
			`id`,
			`login` AS email,
			`last_update` AS client_date,
			`login`,
			`pass`,
			`titre`,
			`nom`,
			`prenom`,
			`fonction`,
			`societe`,
			`nb_salarie`,
			`secteur_activite`,
			`code_naf`,
			`num_siret`,
			`adresse`,
			`complement`,
			`ville`,
			`cp`,
			`pays`,
			`titre_l`,
			`nom_l`,
			`prenom_l`,
			null as `fonction_l`,
			`societe_l`,
			`adresse_l`,
			`complement_l`,
			`ville_l`,
			`cp_l`,
			`pays_l`,
			`coord_livraison`,
			`tel1`,
			`tel2`,
			`fax1`,
			`fax2`,
			`url`,
			`actif`,
			0 as `optin`,
			null as `date_last_maj_optin`
		FROM `clients`
		WHERE `last_update` >= ".$time_start." AND `last_update` < ".$time_end."
		ORDER BY client_date ASC", __FILE__, __LINE__);
		
	while($c = $db->fetchAssoc($res)) {
		foreach($c as $k => &$v) {
			/*switch($k) {
				case "titre":
				case "titre_l":
					switch ($v) {
						case 1  : $v = "Mr"; break;
						case 2  : $v = "Mme"; break;
						case 3  : $v = "Mlle"; break;
						default : $v = "Mr"; break;
					}
					break;
				default: break;
			}*/
			switch ($k) {
				case "email":
				case "login": $v = strtolower($v); break;
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
