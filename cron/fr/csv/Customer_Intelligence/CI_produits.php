<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$db = DBHandle::get_instance();

$upload = true;

$flog = fopen(CSV_PATH."Customer_Intelligence/CI_upload_historic.log", "a+");
fwrite($flog, date("Y-m-d H:i:s")." SESSION BEGIN\n");


/* Products */
$fields = array(
        //"Record_Type" => array("type" => "varchar", "length" => 1),
	"ProductCode" => array("type" => "varchar", "length" => 20),
	"Name" => array("type" => "varchar", "length" => 50),
        "SKUCode" => array("type" => "varchar", "length" => 50),
	"Brand" => array("type" => "varchar", "length" => 50),
        "Category1" => array("type" => "varchar", "length" => 50),
	"Category2" => array("type" => "varchar", "length" => 50),
        "Category3" => array("type" => "varchar", "length" => 50),
	"Category4" => array("type" => "varchar", "length" => 50),
        "Category5" => array("type" => "varchar", "length" => 50),
	"Category6" => array("type" => "varchar", "length" => 50),
        "CostPrice" => array("type" => "varchar", "length" => 50),
	"SalesPrice" => array("type" => "varchar", "length" => 50),
        "IsActive" => array("type" => "varchar", "length" => 1)
);

// Loading XML
$dom = new DomDocument();
$dom->validateOnParse = true;
$dom->load(XML_CATEGORIES_ALL);
$xPath = new DOMXPath($dom);

// Constant var
$brand = "Techni-Contact";

// Opening file for write
$path = CSV_PATH."Customer_Intelligence/upload/";
$csvfilename2 = date("Ymd")."_produits.csv";
$csvPath2 = $path.$csvfilename2;
$zipfilename2 = '7R33F75372-9D6F-4B66-9988-9408CD3D2C15.zip';
$zipPath2 = CSV_PATH."Customer_Intelligence/upload/".$zipfilename2;
if(is_file($zipPath2))
  unlink ($zipPath2);
fwrite($flog, date("Y-m-d H:i:s")." CREATING FILE : ".$csvfilename2."\n");
$fh = fopen($csvPath2, "w+");

// Writing col header
fwrite($fh, "\xEF\xBB\xBF".implode("\t", array_keys($fields)));

// Prepend old files for which upload failed
$fflist = array();
foreach(glob(CSV_PATH."Customer_Intelligence/upload/*_produits.csv.failed") as $ff)
	$fflist[] = $ff;

sort($fflist);

foreach($fflist as $ff) {
	fwrite($flog, date("Y-m-d H:i:s")." DELETING PREVIOUS FAILED TO UPLOAD FILE : ".$ff."\n");
	unlink($ff);
}

// Writing the file with multiple request to avoid memory hog
$time_current = time();
$time_interval = 86400*30;
$time_origin = mktime(0,0,0,2,1,2005);
$time_start = $time_origin;
$time_end = $time_origin + $time_interval;

fwrite($flog, date("Y-m-d H:i:s")." WRITING FILE : ".$csvfilename2."\n");
$a = $b = 0;
while ($time_start <= $time_current) {
  set_time_limit(60);
	// sql query         null AS Record_Type,
	$res = $db->query("
		SELECT
                        IF(a.category = 1, (IFNULL(rc.id, p.idTC)), p.idTC) AS ProductCode,
                        pfr.name AS Name,
                        p.id AS SKUCode,
                        p.timestamp,
                        p.create_time,
                        a.nom1 AS Brand,
                        null AS Category1,
                        null AS Category2,
                        (SELECT MIN(idFamily) FROM products_families WHERE idProduct = p.id ORDER BY orderFamily ASC) as Category3,
                        a.category AS Category4,
                        null AS Category5,
                        ps.hits AS Category6,
                        ps.leads,
                        ps.orders,
                        IF(a.category = 0, 0, rc.price2) AS CostPrice,
			IF(a.category = 0, 0, rc.price) AS SalesPrice,
			IF(a.actif = 0 || pfr.active = 0, 0, 1) AS IsActive
		FROM products p
		INNER JOIN advertisers a ON p.idAdvertiser = a.id
		INNER JOIN products_fr pfr ON p.id = pfr.id
		INNER JOIN products_families pf ON p.id = pf.idProduct
		LEFT JOIN references_content rc ON p.id = rc.idProduct
    LEFT JOIN products_stats ps ON p.id = ps.id
		WHERE (p.timestamp >= ".$time_start." AND p.timestamp < ".$time_end.")
		GROUP BY p.id, rc.id
		ORDER BY p.timestamp asc, rc.classement asc", __FILE__, __LINE__);

	while($pdt = $db->fetchAssoc($res)) {

		$cat3Node = $dom->getElementById(XML_KEY_PREFIX.$pdt["Category3"]);
		if ($cat3Node) {
			$cat3 = $xPath->query("parent::category",$cat3Node)->item(0);
			$catTree = $xPath->query("ancestor-or-self::category", $cat3);
			$cat1 = $catTree->item(0);
			$cat2 = $catTree->item(1);
                        $cat1Name = $cat1->getAttribute("name");
                        $cat2Name = $cat2->getAttribute("name");
                        $cat3Name = $cat3->getAttribute("name");
                }else{
                  $query = "select f.id as cat3id,
                                  fr.name as cat3Name,
                                  f.idParent as cat2Id,
                                  (select name from families_fr where id = f.idParent) as cat2Name,
                                  (select idParent from families where id = f.idParent) as cat1Id,
                                  (select name from families_fr where id = cat1Id) as cat1Name
                                  from families f
                                  inner join families_fr fr on f.id = fr.id
                                  where f.id = ".$pdt["Category3"];
                        $res2 = $db->query($query);
                        $familyAscendency = $db->fetch($res2);
                        $cat1Name = $familyAscendency[5];
                        $cat2Name = $familyAscendency[3];
                        $cat3Name = $familyAscendency[1];

                }
                        //ajout de A devant productcode pour produits annonceur (13/04/2012 OD)
			$pdt["ProductCode"] = $pdt["Category4"] != 1 ? 'A'.$pdt["ProductCode"] :$pdt["ProductCode"];
			unset($pdt["idTC"]);
                        $leads = $pdt["leads"];
                        $orders = $pdt["orders"];
                        // for first record, all lead are inserted
                        // $Record_Type = "I";
                        //$Record_Type = $pdt["create_time"] == 0 ? "U" : (($pdt["timestamp"]-$pdt["create_time"]) < 86400 ? "I" : "U");
			foreach($pdt as $k => &$v) {
				switch($k) {
                                        //case "Record_Type": $v = $Record_Type; break;
					case "Category1": $v =  $pdt["Category1"] = $cat1Name; break;
					case "Category2": $v  = $pdt["Category2"] = $cat2Name; break;
					case "Category3": $v = $cat3Name; break;
                                        case "Category4": $v = $adv_cat_list[$v]['name']; break;
                                        case "Category5":

                                          $hits2leads = $pdt["Category6"] > 0 ? $leads / $pdt["Category6"] : 0;
                                          $hits2orders = $pdt["Category6"] > 0 ? $orders / $pdt["Category6"] : 0;
                                          $v = round(($hits2leads + $hits2orders)*100, 2);
                                          break;
					default: $v = mb_convert_encoding($v, "UTF-8", "ASCII,UTF-8,ISO-8859-1"); break;
				}
                                unset($pdt["leads"]);
                                unset($pdt["orders"]);
                                unset($pdt["timestamp"]);
                                unset($pdt["create_time"]);
				switch($fields[$k]["type"]) {
					case "int": $v = (int)$v; break;
					case "float": $v = (float)$v; break;
					case "varchar": $v = preg_replace(array("/\r\n/","/\n/","/\t/","/\r/",'/"/',"/\\\\$/"), "", trim(substr($v,0,$fields[$k]["length"]))); break;
					//case "varchar": $v = filter_var(trim($v), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW); break;
					//preg_replace('/&euro;/i', 'â‚¬', html_entity_decode(filter_var(trim($v), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW), ENT_QUOTES)); break;
					case "datetime": $v = empty($v)?"\"\"":date("Y-m-d H:i:s", $v); break;
					default: break;
				}
				if ($v === "\"\"") $v = " ";

			}

			unset($v);
                        mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $pdt);
			fwrite($fh, "\r\n".implode("\t", $pdt));
                       
		//}
	}

	$time_start += $time_interval;
	$time_end += $time_interval;
}

fclose($fh);

/* zipping */
$zip2 = new ZipArchive;
if ($zip2->open($zipPath2, ZipArchive::CREATE) === TRUE) {
  fwrite($flog, date("Y-m-d H:i:s")." OPEN ARCHIVE : ".$zipfilename2."\n");
    $zip2->addFile($csvPath2, $csvfilename2);
    if($zip2->close()){
      unlink ($csvPath2);
      fwrite($flog, date("Y-m-d H:i:s")." CLOSE ARCHIVE : ".$zipfilename2."\n");
      fwrite($flog, date("Y-m-d H:i:s")." DELETE FILE : ".$csvfilename2."\n");
    }
}

fwrite($flog, date("Y-m-d H:i:s")." SESSION END\n\n");

fclose($flog);

if ($upload) {
  /* ftp send file */
  define("REMOTE_FILE", $zipfilename2);
  define("CATALOG_FILE", $zipPath2);
  define("CI_FTP_SERVER", 'webe.emv3.com');
  define("CI_FTP_USERNAME", 'md2i_ftp');
  define("CI_FTP_PASS", 'md2i@ftp/1');
  define("CI_REMOVE_DIR", 'ccci/incoming/');

  $file = CATALOG_FILE;
  $remote_file = CI_REMOVE_DIR.REMOTE_FILE;

  if(is_file($file)){
    // Mise en place d'une connexion basique
    $conn_id = ftp_connect(CI_FTP_SERVER);

    // Identification avec un nom d'utilisateur et un mot de passe
    $login_result = ftp_login($conn_id, CI_FTP_USERNAME, CI_FTP_PASS);

    // Charge un fichier
    ftp_put($conn_id, $remote_file, $file, FTP_BINARY);

    // Fermeture de la connexion
    ftp_close($conn_id);
  }
}