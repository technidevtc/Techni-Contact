<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

require(ICLASS."ExtranetUser.php");
require(ADMIN   ."statut.php");

$handle = DBHandle::get_instance();
$user = new ExtranetUser($handle);

if(!$user->login()) {
	header("Location: ".ADMIN_URL."login.html");
	exit();
}


if (!isset($_POST['DateBegin'])
	|| !isset($_POST['DateEnd'])
	|| !preg_match('/^[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{4}$/', $_POST['DateBegin'])
	|| !preg_match('/^[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{4}$/', $_POST['DateEnd'])) {
	header("Location: ".ADMIN_URL."requests.html");
	exit();
}
else {
	require_once("Spreadsheet/Excel/Writer.php");

	$workbook = new Spreadsheet_Excel_Writer();

	// sending HTTP headers
	$dateBegin = explode("/", $_POST['DateBegin']);
	$dateEnd = explode("/", $_POST['DateEnd']);
	
	$workbook->send("Extrait Demandes de Contact du " . $dateBegin[0] . "-" . $dateBegin[1] . "-" . $dateBegin[2] . " au " . $dateEnd[0] . "-" . $dateEnd[1] . "-" . $dateEnd[2] . ".xls");

        $dateBegin = mktime(0,0,0,$dateBegin[1],$dateBegin[0],$dateBegin[2]);
        $dateEnd = mktime(23,59,59,$dateEnd[1],$dateEnd[0],$dateEnd[2]);

        if($user->category == __ADV_CAT_LITIGATION__) // si l'annonceur est en litige de paiement
          if($user->litigation_time <= $dateEnd)
            $dateEnd = $user->litigation_time;
          elseif($user->litigation_time >= $dateBegin)
            $dateBegin = $user->litigation_time;
          

        $EOQ = " and c.timestamp >= " . $dateBegin . " and c.timestamp < " . $dateEnd . " order by c.timestamp desc";

	// Creating a worksheet
	$worksheet = $workbook->addWorksheet('Liste des demandes de Contact');

	// Headers
	$col_headers = array("Date", "Nom produit", "Famille", "ID demande", "Type demande", "Raison Sociale", "Adresse", "Cplt adresse",
		"Code Postal", "Ville", "Pays", "Tel", "Fax", "Prénom", "Nom", "Fonction", "Email", "SIRET", "NAF",
		"Taille salariale", "Secteur", "Url", "Précisions", "Etat", "Date de rejet");
	
	// headers Hidden If Not Charged
	$col_headers_hinc = array("societe", "adresse", "cadresse", "tel", "fax", "nom", "prenom", "email", "naf", "siret", "url");

	$l = 0;
	foreach ($col_headers as $col_header) $worksheet->write($l, $c++, utf8_decode($col_header));
	$chi = array_flip($col_headers); // Cols Headers Index
	$nfchi = count($chi); // Next Free Col Header Index
	$l++;
	
	$result = $handle->query("
		SELECT
			c.timestamp, pfr.name, f.name as familyName, c.id, c.type, c.societe, c.adresse,
			c.cadresse, c.cp, c.ville, c.pays, c.tel,
			c.fax, c.prenom, c.nom, c.fonction, c.email,
			c.siret, c.naf, c.salaries, c.secteur, c.url,
			c.precisions, c.invoice_status, c.credited_on, c.customFields, c.reject_timestamp
		FROM contacts c
		INNER JOIN advertisers a ON c.idAdvertiser = a.id
                INNER JOIN families_fr	f ON c.idFamily = f.id
		LEFT JOIN products_fr pfr ON c.idProduct = pfr.id
		WHERE ".($user->id == __ID_TECHNI_CONTACT__ ? "(a.id = ".$user->id." OR a.parent = ".$user->id.")" : "a.id = ".$user->id).$EOQ, __FILE__, __LINE__);
	
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
		if (!($cols["invoice_status"] & __LEAD_VISIBLE__)) {
			foreach($col_headers_hinc as $col_header_hinc)
				$cols[$col_header_hinc] = "-";
		}
		
		$cols["invoice_status"] = $lead_invoice_status_list[$cols["invoice_status"]].getCreditMonth($cols);
		
		$c = 0;
		foreach($cols as $colName => &$colData) {
			if ($colName == 'customFields') {
				$customFields = unserialize($colData);
				if (empty($customFields)) $customFields = array();
				foreach($customFields as $cfName => $cfValue) {
					if (!isset($chi[$cfName])) { // If the col header does not exist, we create it
						$chi[$cfName] = $nfchi++;
						$worksheet->write(0, $chi[$cfName], utf8_decode($cfName));
					}
					$worksheet->write($l, $chi[$cfName], utf8_decode($cfValue));
				}
			}
			elseif ($colName != 'credited_on')  {
				$worksheet->write($l, $c++, utf8_decode($colData));
			}
		}
		unset($colData);
		
		$l++;	
	}

	// Let's send the file
	$workbook->close();
}
?>
