<?php

if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

require(ADMIN."logs.php");
require(ADMIN."statut.php");

$handle = DBHandle::get_instance();
$user = new BOUser();

if(!$user->login()) {
	header("Location: ".ADMIN_URL."login.html");
	exit();
}

if (!isset($_POST["DateBegin"])
	|| !isset($_POST["DateEnd"])
	|| !preg_match('/^[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{4}$/', $_POST["DateBegin"])
	|| !preg_match('/^[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{4}$/', $_POST["DateEnd"])) {
	header("Location: requests.html");
	exit();
}
else {
  if (!$user->get_permissions()->has("m-comm--sm-leads","r")) {
    header("Location: leads.php");
    exit();
  }
	$queryWhere = array();
    
    $dateBegin = explode("/", $_POST["DateBegin"]);
	$dateEnd = explode("/", $_POST["DateEnd"]);
    $queryWhere[] = "c.timestamp > ".mktime(0,0,0,$dateBegin[1],$dateBegin[0],$dateBegin[2])." AND c.timestamp < ".mktime(0,0,0,$dateEnd[1],$dateEnd[0],$dateEnd[2]);
    
	$findType = isset($_POST["findType"]) ? trim($_POST["findType"]) : "";
    $findText = isset($_POST["findText"]) ? trim($_POST["findText"]) : "";
    if ($findText != "") {
      switch($findType) {
        case "0": break;
        case "1": // product id
          if (preg_match("/^[1-9]{1}[0-9]{0,8}$/", $findText))
            $queryWhere[] = "pfr.id = '".$findText."'";
        break;

        case "2": // advertiser name
          $queryWhere[] = "a.ref_name = '".$handle->escape(Utils::toDashAz09($findText))."'";
        break;

        case "3": // lead id
          if (preg_match("/^[1-9]{1}[0-9]{0,8}$/", $findText))
            $queryWhere[] = "c.id = '".$findText."'";
        break;

        case "4": // lead email
          $queryWhere[] = "c.email = '".$handle->escape($findText)."'";
        break;
      }
    }
    
    $queryWhere = !empty($queryWhere) ? " WHERE ".implode(" AND ", $queryWhere) : "";
	
    require_once("Spreadsheet/Excel/Writer.php");
	$workbook = new Spreadsheet_Excel_Writer();
	$workbook->send("Extrait Demandes de Contact du " . $dateBegin[0] . "-" . $dateBegin[1] . "-" . $dateBegin[2] . " au " . $dateEnd[0] . "-" . $dateEnd[1] . "-" . $dateEnd[2] . ".xls");

	// Creating a worksheet
	$worksheet = $workbook->addWorksheet('Liste des demandes de Contact');

	// Headers
	$col_headers = array(
		"Date", "ID demande", "Raison sociale",
		"Nom produit", "ID produit",
		"Nom famille", "ID famille",
		"Nom partenaire", "ID partenaire", "Type partenaire",
		"Etat lead", "Revenu lead", "Revenu total", "Type lead", "ID lead primaire", "Date d'avoir",
		"Adresse", "Cplt adresse", "Code Postal", "Ville", "Pays", "Tel", "Fax",
		"Prénom", "Nom", "Fonction", "Email", "SIRET", "NAF", "Taille salariale", "Secteur", "Url", "Précisions",
        "Utilisateur","Source du lead","Code Probance","Raison du rejet"
	);

	$l = 0;
	foreach ($col_headers as $col_header) $worksheet->write($l, $c++, utf8_decode($col_header));
	$chi = array_flip($col_headers); // Cols Headers Index
	$nfchi = count($chi); // Next Free Col Header Index
	$l++;
	
	// customFields in last
	$result = $handle->query("
		SELECT
			c.timestamp, c.id, c.societe,
			pfr.name AS pdt_name, pfr.id AS pdt_id,
			ffr.name AS cat_name, ffr.id AS cat_id,
			a.nom1 AS adv_name, a.id AS adv_id, a.category AS adv_category,
			c.invoice_status, c.income, c.income_total, c.parent AS type_lead, c.parent, c.credited_on,
			c.adresse, c.cadresse, c.cp, c.ville, c.pays, c.tel, c.fax,
			c.prenom, c.nom, c.fonction, c.email, c.siret, c.naf, c.salaries, c.secteur, c.url, c.precisions,
            bou.name AS user_name, c.origin, c.campaignID,
			c.customFields,c.reject_reason
		FROM contacts c
		LEFT JOIN products_fr pfr ON c.idProduct = pfr.id
		LEFT JOIN families_fr ffr ON c.idFamily = ffr.id
		LEFT JOIN advertisers a ON c.idAdvertiser = a.id
        LEFT JOIN bo_users bou ON c.id_user = bou.id
        ".$queryWhere."
		ORDER BY c.timestamp DESC", __FILE__, __LINE__);

    
    while ($cols = $handle->fetchAssoc($result)) {
		
		
		$cols["timestamp"] = date("d/m/Y H:i:s", $cols["timestamp"]);
        $cols["credited_on"] = date("d/m/Y H:i:s", $cols["credited_on"]);
		$cols["invoice_status"] = $lead_invoice_status_list[$cols["invoice_status"]].getCreditMonth($cols);
		$cols["type_lead"] = $cols["parent"] == 0 ? "primaire" : "secondaire";
		$cols["adv_category"] = $adv_cat_list[$cols["adv_category"]]["name"];
		$cols["reject_reason"] = $cols["reject_reason"];
		if ($cols["user_name"] == "hook-network") // TODO: change hook-network's user id
          $cols["user_name"] = "Internaute";
//        if (!empty($cols["campaignID"]))
//          $cols["origin"] = "Probance";
        if (empty($cols["origin"]))
          $cols["origin"] = "Internaute";
        
		$c = 0;
        foreach($cols as $colName => &$colData) {
			if ($colName == 'customFields') {
				$customFields = mb_unserialize($colData);
				if (empty($customFields)) $customFields = array();
				foreach($customFields as $cfName => $cfValue) {
					if (!isset($chi[$cfName])) { // If the col header does not exist, we create it
						$chi[$cfName] = $nfchi++;
						$worksheet->write(0, $chi[$cfName], utf8_decode($cfName));
					}
					$worksheet->write($l, $chi[$cfName], utf8_decode($cfValue));
				}
				
			}
			else {
              $colData = preg_replace("/^\@/"," @",$colData); // if a line begins with @, it fails (bug). Was quite hard to find out.
		
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
