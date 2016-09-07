<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

require(ADMIN."logs.php");
require(ADMIN."statut.php");

$handle = DBHandle::get_instance();
$user = new BOUser();

if(!$user->login()) {
	header("Location: ".ADMIN_URL."login.html");
	exit();
}

$dateFilterType = isset($_POST["dateFilterType"]) ? ($_POST["dateFilterType"] == "interval" ? "interval" : "simple") : "simple";

if (!isset($_POST["DateBegin"])
	|| !preg_match('/^[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{4}$/', $_POST["DateBegin"])
	) {
        header("Location: rejected-leads.php");
	exit();
}elseif($dateFilterType == 'interval' && (!isset($_POST["DateEnd"])|| !preg_match('/^[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{4}$/', $_POST["DateEnd"]))){
        header("Location: rejected-leads.php");
	exit();
}else {
  if (!$user->get_permissions()->has("m-reporting--sm-rejected-leads","r")) {
    exit();
  }
	require_once("Spreadsheet/Excel/Writer.php");

	$workbook = new Spreadsheet_Excel_Writer();

        // time interval
        $dateBegin = explode("/", $_POST["DateBegin"]);
	$dateEnd = explode("/", $_POST["DateEnd"]);

        $yearS2 = isset($_POST['DateBegin']) && $dateFilterType == 'interval' ? (int)trim($dateBegin[2]) : date("Y");
        $monthS2 = isset($_POST['DateBegin']) && $dateFilterType == 'interval' ? (int)trim($dateBegin[1]) : date("m");
        $dayS2 = isset($_POST['DateBegin']) && $dateFilterType == 'interval' ? (int)trim($dateBegin[0]) : date("d");

        if (isset($dateBegin[2])) {
        //	$dateFilterType = "simple";
                $yearS	= (int)trim($dateBegin[2]);
                $monthS	= isset($dateBegin[1])	? (int)trim($dateBegin[1]) : 0;
                $dayS	= isset($dateBegin[0])		? (int)trim($dateBegin[0]) : 0;

                if (isset($dateEnd[2])) {
        //		$dateFilterType = "interval";
                        $yearE	= (int)trim($dateEnd[2]);
                        $monthE	= isset($dateEnd[1])	? (int)trim($dateEnd[1]) : 0;
                        $dayE	= isset($dateEnd[0])		? (int)trim($dateEnd[0]) : 0;
                }
        }
	
	
        if ($dateFilterType == "simple") {
                if ($yearS != 0) {
                        if ($monthS == 0)	{ $dateStart = mktime(0,0,0,      1,    1,$yearS);	$dateEnd = mktime(0,0,0,        1,      1,$yearS+1); }
                        elseif ($dayS == 0)	{ $dateStart = mktime(0,0,0,$monthS,    1,$yearS);	$dateEnd = mktime(0,0,0,$monthS+1,      1,$yearS  ); }
                        else				{ $dateStart = mktime(0,0,0,$monthS,$dayS,$yearS);	$dateEnd = mktime(0,0,0,$monthS  ,$dayS+1,$yearS  ); }
                }
        }
        elseif ($dateFilterType == "interval") {
                if ($yearS2 != 0 && $yearE != 0) {
                        if ($monthS2 == 0)   { $dateStart = mktime(0,0,0,       1,     1,$yearS2); }
                        elseif ($dayS2 == 0) { $dateStart = mktime(0,0,0,$monthS2,     1,$yearS2); }
                        else                 { $dateStart = mktime(0,0,0,$monthS2,$dayS2,$yearS2); }

                        if ($monthE == 0)   { $dateEnd = mktime(0,0,0,      1,      1,$yearE); }
                        elseif ($dayE == 0) { $dateEnd = mktime(0,0,0,$monthE,      1,$yearE); }
                        else                { $dateEnd = mktime(0,0,0,$monthE,$dayE+1,$yearE); }
                }
        }

        // dates for tests
        //$dateStart = 1296050725;
        //$dateEnd = 1305800128;

        if (!isset($dateStart) || !isset($dateEnd)) {
          $dateStart = 0;
          $dateEnd = 0x7fffffff;
        }
        // sending HTTP headers
	$workbook->send("Reporting taux de rejets du " . $dateBegin[0] . "-" . $dateBegin[1] . "-" . $dateBegin[2] . " au " . $dateEnd[0] . "-" . $dateEnd[1] . "-" . $dateEnd[2] . ".xls");


        $EOQ = " and c.timestamp >= " . $dateStart . " and c.timestamp < " . $dateEnd . " order by c.timestamp desc";

	// Creating a worksheet
	$worksheet = $workbook->addWorksheet('Reporting taux de rejets');

	// Headers

	$col_headers = array("Nom partenaire", "Typologie partenaire", "Nb de leads total reçus", "Nb de leads primaires reçus",
            "Nb de leads rejetés", "Taux de rejet");
	
	$l = 0;
	foreach ($col_headers as $col_header) $worksheet->write($l, $c++, $col_header);
	$chi = array_flip($col_headers); // Cols Headers Index
	$nfchi = count($chi); // Next Free Col Header Index
	$l++;

	$result = $handle->query("
          SELECT
          a.nom1, a.category,
          COUNT(c.id) AS nb_leads,
          SUM(IF(c.parent=0,1,0)) AS nb_primary,
          SUM(IF(c.invoice_status=".__LEAD_INVOICE_STATUS_REJECTED__.",1,0)) AS nb_rejected,
          SUM(IF(c.invoice_status=".__LEAD_INVOICE_STATUS_CHARGED__." || c.invoice_status=".__LEAD_INVOICE_STATUS_CHARGEABLE__.",1,0)) AS nb_charged
          FROM contacts c
          LEFT JOIN advertisers a ON a.id = c.idAdvertiser
          WHERE c.timestamp >= ".$dateStart." AND c.timestamp < ".$dateEnd."
          GROUP BY c.idAdvertiser
          ORDER BY a.nom1 ASC
        ", __FILE__, __LINE__);

	while ($cols = $handle->fetchAssoc($result)) {
//          var_dump($cols);
                if(empty($cols['nom1'])) $cols['nom1'] = 'Indéfini';
		$cols['category'] = $adv_cat_list[$cols['category']]['name'];	
//		Nb de leads rejetés dans la période / Nb de leads rejetés dans la période + Nb de leads facturables ou facturés * 100

		$cols["tx_rejet"] = ($cols["nb_rejected"]+$cols["nb_charged"])*100 != 0 ? $cols["nb_rejected"]/($cols["nb_rejected"]+$cols["nb_charged"])*100 : 0;
		
		$c = 0;//var_dump($cols);
		foreach($cols as $colName => &$colData) {
                                    
//			if ($colName == 'customFields') {
//				$customFields = mb_unserialize($colData);
//				if (empty($customFields)) $customFields = array();
//				foreach($customFields as $cfName => $cfValue) {
//					if (!isset($chi[$cfName])) { // If the col header does not exist, we create it
//						$chi[$cfName] = $nfchi++;
////						$worksheet->write(0, $chi[$cfName], $cfName);
//					}
////					$worksheet->write($l, $chi[$cfName], $cfValue);
//				}
//			}
//			else
                         if ($colName != 'nb_charged')  {
 				$worksheet->write($l, $c++, $colData);
			}
		}
		unset($colData);
		
		$l++;	
	}

	// Let's send the file
	$workbook->close();
}
?>
