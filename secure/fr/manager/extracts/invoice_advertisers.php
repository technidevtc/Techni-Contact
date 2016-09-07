<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

require(ADMIN."logs.php");

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
	header("Location: ../advertisers/invoices_list.php");
	exit();
}
else {
  if (!$user->get_permissions()->has("m-comm--sm-invoices-partners","r")) {
    header("Location: ../");
    exit();
  }
	require_once("Spreadsheet/Excel/Writer.php");

	$workbook = new Spreadsheet_Excel_Writer();

	// sending HTTP headers
	$dateBegin = explode("/", $_POST["DateBegin"]);
	$dateEnd = explode("/", $_POST["DateEnd"]);
        $dateBeginTimestamp = mktime(0,0,0,$dateBegin[1],$dateBegin[0],$dateBegin[2]);
        $dateEndTimestamp = mktime(23,59,59,$dateEnd[1],$dateEnd[0],$dateEnd[2]);

	$workbook->send("Facturation annonceurs du " . $dateBegin[0] . "-" . $dateBegin[1] . "-" . $dateBegin[2] . " au " . $dateEnd[0] . "-" . $dateEnd[1] . "-" . $dateEnd[2] . ".xls");

	// Creating a worksheet
	$worksheet = $workbook->addWorksheet('Facturation annonceurs');

	// Headers
	$col_headers = array(
		"Nom partenaire", "Type partenaire", 
		"Nb contacts à facturer", "CA à Facturer",
                "Nb contacts reçus", "Nb Contact Non facturé",
                "Nb contacts Rejetés", "Nb contacts en avoir",
	);

	$l = 0;

	foreach ($col_headers as $col_header) $worksheet->write($l, $c++, $col_header);
	$chi = array_flip($col_headers); // Cols Headers Index
	$nfchi = count($chi); // Next Free Col Header Index
	$l++;

        $query = "
		SELECT
			c.timestamp, c.reject_timestamp, c.credited_on,
			a.nom1 AS adv_name, a.id AS adv_id, a.category AS adv_category,
			count(c.invoice_status) as nbr_invoice_by_type, c.invoice_status, sum(c.income) as income, sum(c.income_total) as income_total
		FROM contacts c
		LEFT JOIN advertisers a ON c.idAdvertiser = a.id
		WHERE
                  ((c.timestamp > ".$dateBeginTimestamp." AND c.timestamp < ".$dateEndTimestamp.")
                  or (c.credited_on > ".$dateBeginTimestamp." AND c.credited_on < ".$dateEndTimestamp." ))
		group by adv_id, invoice_status";

	$result = $handle->query($query, __FILE__, __LINE__);
        
        $partners = array();

	while ($cols = $handle->fetchAssoc($result)) {

          if( !isset($partners[$cols['adv_id']]) )
            $partners[$cols['adv_id']] = array(
                'name' => $cols['adv_name'], //Nom partenaire : Nom annonceur
                'type' => $cols['adv_category'], //Etat partenaire : Annonceur, Annonceur non facturé....
            );

            if($cols['timestamp']  > $dateBeginTimestamp && $cols['timestamp']  < $dateEndTimestamp )
              $partners[$cols['adv_id']]['nbLeadsReceived'] += $cols['nbr_invoice_by_type']; //Nb contacts reçus : tous les contacts reçus

            switch ( $cols['invoice_status'] ){
              case __LEAD_INVOICE_STATUS_CHARGED_PERMANENT__:
              case __LEAD_INVOICE_STATUS_REJECTED_REFUSED__:
              case __LEAD_INVOICE_STATUS_CHARGED__:
              case __LEAD_INVOICE_STATUS_CHARGEABLE__:
                  $partners[$cols['adv_id']]['nbLeadsCharged'] += $cols['nbr_invoice_by_type']; //Nb contacts à facturer :
                  $partners[$cols['adv_id']]['income'] += sprintf("%.02f", $cols["income"]); //CA à Facturer :
                break;

              case __LEAD_INVOICE_STATUS_NOT_CHARGED__ :
              case __LEAD_INVOICE_STATUS_DOUBLET__ :
              case __LEAD_INVOICE_STATUS_IN_FORFEIT__ :
                  $partners[$cols['adv_id']]['nbLeadsNotCharged'] += $cols['nbr_invoice_by_type']; //Nb Contact "Non facturé" : Doublons, hors champs de facturation
                  $partners[$cols['adv_id']]['income'] += sprintf("%.02f", $cols["income"]); //CA à Facturer :
                break;

              case __LEAD_REJECTED__:
                  $partners[$cols['adv_id']]['nbLeadsRejected'] += $cols['nbr_invoice_by_type'];//Nb contacts "Rejetés" : "Facturables" Rejetés
                   //CA à Facturer : x+0=x
                break;

              case __LEAD_INVOICE_STATUS_CREDITED__:
                // in this case, the total income of the charging period is wanted
                if($cols['timestamp']  > $dateBeginTimestamp && $cols['timestamp']  < $dateEndTimestamp )  {

                    $partners[$cols['adv_id']]['nbLeadsCharged'] += $cols['nbr_invoice_by_type'];
                    $partners[$cols['adv_id']]['income'] += sprintf("%.02f", $cols["income"]); //CA à Facturer :
                
                // in this case, the total income of the credited period is wanted
                }elseif($cols['credited_on']  > $dateBeginTimestamp &&  $cols['credited_on']  < $dateEndTimestamp )  {

                    $partners[$cols['adv_id']]['nbLeadsCredited'] += $cols['nbr_invoice_by_type']; //Nb contacts "Rejeté - déduits de facture de..." : "Facturés" rejetés
                    $partners[$cols['adv_id']]['income'] -= sprintf("%.02f", $cols["income"]); //CA à Facturer :
                  
                }
                break;

              case __LEAD_INVOICE_STATUS_DISCHARGED__:
                if($cols['timestamp']  > $dateBeginTimestamp && $cols['timestamp']  < $dateEndTimestamp )  {
                  $partners[$cols['adv_id']]['nbLeadsCharged'] += $cols['nbr_invoice_by_type'];
                  $partners[$cols['adv_id']]['income'] += sprintf("%.02f", $cols["income"]); //CA à Facturer :
                }
                break;
            }

            if(!isset($partners[$cols['adv_id']]['nbLeadsReceived'])) $partners[$cols['adv_id']]['nbLeadsReceived'] = 0;
            if(!isset($partners[$cols['adv_id']]['nbLeadsCharged'])) $partners[$cols['adv_id']]['nbLeadsCharged'] = 0;
            if(!isset($partners[$cols['adv_id']]['nbLeadsNotCharged'])) $partners[$cols['adv_id']]['nbLeadsNotCharged'] = 0;
            if(!isset($partners[$cols['adv_id']]['nbLeadsRejected'])) $partners[$cols['adv_id']]['nbLeadsRejected'] = 0;
            if(!isset($partners[$cols['adv_id']]['nbLeadsCredited'])) $partners[$cols['adv_id']]['nbLeadsCredited'] = 0;
            if(!isset($partners[$cols['adv_id']]['income'])) $partners[$cols['adv_id']]['income'] = 0;

	}

        foreach($partners as $partner){
          $partner["type"] = $adv_cat_list[$partner["type"]]["name"];
                $worksheet->write($l, 0, $partner["name"]); //"Nom partenaire"
                $worksheet->write($l, 1, $partner["type"]); //"Type partenaire"
                $worksheet->write($l, 2, $partner["nbLeadsCharged"]-$partner["nbLeadsCredited"]); //"Nb contacts à facturer"
                $worksheet->write($l, 3, $partner["income"]); //"CA à Facturer"
                $worksheet->write($l, 4, $partner["nbLeadsReceived"]); //"Nb contacts reçus"
                $worksheet->write($l, 5, $partner["nbLeadsNotCharged"]); //"Nb Contact Non facturé"
                $worksheet->write($l, 6, $partner["nbLeadsRejected"]); //"Nb contacts Rejetés"
                $worksheet->write($l, 7, $partner["nbLeadsCredited"]); //"Nb contacts en avoir"

                $l++;
        }

	// Let's send the file
	$workbook->close();
}
?>
