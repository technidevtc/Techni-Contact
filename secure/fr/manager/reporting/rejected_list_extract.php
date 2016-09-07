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
	$workbook->send("Liste des rejets du " . date('d', $dateStart) . "-" . date('m', $dateStart) . "-" . date('Y', $dateStart) . " au " . date('d', $dateEnd) . "-" . date('m', $dateEnd) . "-" . date('Y', $dateEnd) . ".xls");

        $EOQ = " and c.timestamp >= " . $dateStart . " and c.timestamp < " . $dateEnd . " order by c.timestamp desc";

	// Creating a worksheet
	$worksheet = $workbook->addWorksheet('Liste des demandes de Contact');

	// Headers
	$col_headers = array("Date", "Nom produit", "Famille", "ID demande", "Type demande", "Raison Sociale", "Adresse", "Cplt adresse",
		"Code Postal", "Ville", "Pays", "Tel", "Fax", "Prénom", "Nom", "Fonction", "Email", "SIRET", "NAF",
		"Taille salariale", "Secteur", "Url", "Précisions", "Etat", "Nom partenaire", "Date de rejet", "Motif rejet", "Litige");

	// headers Hidden If Not Charged
	$col_headers_hinc = array("societe", "adresse", "cadresse", "tel", "fax", "nom", "prenom", "email", "naf", "siret", "url");

	$l = 0;
	foreach ($col_headers as $col_header) $worksheet->write($l, $c++, $col_header);
	$chi = array_flip($col_headers); // Cols Headers Index
	$nfchi = count($chi); // Next Free Col Header Index
	$l++;

	$result = $handle->query("
		SELECT
			c.timestamp, pfr.name, f.name as familyName, c.id, c.type, c.societe, c.adresse,
			c.cadresse, c.cp, c.ville, c.pays, c.tel,
			c.fax, c.prenom, c.nom, c.fonction, c.email,
			c.siret, c.naf, c.salaries, c.secteur, c.url,
			c.precisions, c.invoice_status, c.credited_on, c.customFields, a.nom1, c.reject_timestamp, c.reject_reason, a.category
		FROM contacts c
		INNER JOIN advertisers a ON c.idAdvertiser = a.id
                INNER JOIN families_fr	f ON c.idFamily = f.id
		LEFT JOIN products_fr pfr ON c.idProduct = pfr.id
		WHERE c.invoice_status=".__LEAD_INVOICE_STATUS_REJECTED__.$EOQ, __FILE__, __LINE__);

	while ($cols = $handle->fetchAssoc($result)) {
                if(empty($cols['name'])) $cols['name'] = 'Indéfini';
		$cols['timestamp'] = date("d/m/Y H:i", $cols['timestamp']);
                $cols['reject_timestamp'] = $cols['reject_timestamp'] != 0 ? date("d/m/Y H:i", $cols['reject_timestamp']) : '-';
		switch($cols['type']) {
			case 1 : $cols['type'] = "Demande d'informations"; break;
			case 2 : $cols['type'] = "Demande de contact téléphonique"; break;
			case 3 : $cols['type'] = "Demande de devis"; break;
			case 4 : $cols['type'] = "Demande de rendez-vous"; break;
			default : $cols['type'] = "Demande d'informations";
		}

		// Hiding fields if necessary
//		if (!($cols["invoice_status"] & __LEAD_VISIBLE__)) {
//			foreach($col_headers_hinc as $col_header_hinc)
//				$cols[$col_header_hinc] = "-";
//		}

		$cols["invoice_status"] = $lead_invoice_status_list[$cols["invoice_status"]].getCreditMonth($cols);
                $cols["category"] = $cols["category"] == __ADV_CAT_LITIGATION__ ? 'oui' : 'non';

		$c = 0;
		foreach($cols as $colName => &$colData) {
			if ($colName == 'customFields') {
				$customFields = mb_unserialize($colData);
				if (empty($customFields)) $customFields = array();
				foreach($customFields as $cfName => $cfValue) {
					if (!isset($chi[$cfName])) { // If the col header does not exist, we create it
						$chi[$cfName] = $nfchi++;
						$worksheet->write(0, $chi[$cfName], $cfName);
					}
					$worksheet->write($l, $chi[$cfName], $cfValue);
				}
			}
			elseif ($colName != 'credited_on')  {
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
