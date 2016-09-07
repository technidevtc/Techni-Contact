<?php

/*================================================================/

 Techni-Contact V4 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 15 février 2007

 Mises à jour :

 Fichier : /secure/manager/stats/index.php
 Description : Index des statistiques
 
/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

require(ADMIN."logs.php");

$handle = DBHandle::get_instance();
$user = new BOUser();

if(!$user->login())
{
	header('Location: ' . ADMIN_URL . 'login.html');
	exit();
}
if (!isset($_POST['mod_export'])
	|| ($_POST['mod_export'] != "all" && $_POST['mod_export'] != "date")
	|| ($_POST['mod_export'] == "date" && ( (!isset($_POST['DateBegin']) || !isset($_POST['DateEnd'])) || (!preg_match('/^[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{4}$/', $_POST['DateBegin']) || !preg_match('/^[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{4}$/', $_POST['DateEnd'])) ) ) )
{
	header("Location: contacts.php");
	exit();
}
else
{
  if (!$user->get_permissions()->has("m-mark--sm-leads-export","r")) {
    exit();
  }
	require_once("Spreadsheet/Excel/Writer.php");

	$workbook = new Spreadsheet_Excel_Writer();

	// sending HTTP headers
	if ($_POST['mod_export'] == "all")
	{
		$workbook->send("Base Contacts Globale " . date("d-m-Y") . ".xls");
		$EOQ = "";
	}
	elseif ($_POST['mod_export'] == "date")
	{
		$dateBegin = explode("/", $_POST['DateBegin']);
		$dateEnd = explode("/", $_POST['DateEnd']);
		
		$workbook->send("Base Contacts du " . $dateBegin[0] . "-" . $dateBegin[1] . "-" . $dateBegin[2] . " au " . $dateEnd[0] . "-" . $dateEnd[1] . "-" . $dateEnd[2] . ".xls");
		$EOQ = " c.timestamp > " . mktime(0,0,0,$dateBegin[1],$dateBegin[0],$dateBegin[2]) . " and c.timestamp < " . mktime(0,0,0,$dateEnd[1],$dateEnd[0],$dateEnd[2]);
	}

	// Creating a worksheet
	$worksheet = & $workbook->addWorksheet('Liste des clients');

/*	$result = & $handle->query("select societe, adresse, cadresse, cp, ville, tel, fax, null, prenom, nom, fonction, email, siret from contacts" . $EOQ, __FILE__, __LINE__);
	$col_headers = array("Raison Sociale", "Adresse", "Cplt adresse", "Code Postal", "Ville", "Tel", "Fax", "Civilité", "Prénom", "Nom", "Fonction", "Email", "Siret");*/
	
	// Headers
	$col_headers = array("ID", "Date", "ID produit", "Nom produit", "Famille 1", "Famille 2", "Famille 3", "ID annonceur", "Nom annonceur", "Catégorie", "Type demande",
		"Raison Sociale", "Adresse", "Cplt adresse", "Code Postal", "Ville", "Pays", "Tel", "Fax", "Prénom", "Nom", "Fonction", "Email", "SIRET", "NAF",
		"Taille salariale", "Secteur", "Url", "Précisions");

	$l = 0;
	foreach ($col_headers as $col_header) $worksheet->write($l, $c++, $col_header);
	$chi = array_flip($col_headers); // Cols Headers Index
	$nfchi = count($chi); // Next Free Col Header Index
	$l++;
	
	/* Getting families DB */
	$families = array();
	$res = & $handle->query("
	select f.id, f.idParent, ffr.name, ffr.ref_name
	from families f, families_fr ffr
	where f.id = ffr.id", __FILE__, __LINE__);

	while ($fam = & $handle->fetchAssoc($res))
		$families[$fam['id']] = $fam;
	
	/* Getting contacts */
	$result = & $handle->query("
	select
		c.id, c.timestamp, c.idProduct, pfr.name, null as fam1, null as fam2, pf.idFamily as fam3, c.idAdvertiser, a.nom1, a.category, c.type, 
		c.societe, c.adresse, c.cadresse, c.cp, c.ville, c.pays, c.tel, c.fax, c.prenom, c.nom, c.fonction, c.email, c.siret, c.naf,
		c.salaries, c.secteur, c.url, c.precisions, c.customFields
	from
		contacts c, products_fr pfr, advertisers a, products_families pf
	where
		c.idProduct = pfr.id and c.idAdvertiser = a.id and pfr.id = pf.idProduct" . ($EOQ != "" ? " and" . $EOQ : "") . "
	group by c.id", __FILE__, __LINE__);
	while ($cols = & $handle->fetchAssoc($result))
	{
		$cols['timestamp'] = date("Y/m/d H:i", $cols['timestamp']);
		$cols['category'] = $adv_cat_list[$cols['category']]['name'];
		switch($cols['type'])
		{
			case 1 : $cols['type'] = "Demande d'informations"; break;
			case 2 : $cols['type'] = "Demande de contact téléphonique"; break;
			case 3 : $cols['type'] = "Demande de devis"; break;
			case 4 : $cols['type'] = "Commande"; break;
			default : $cols['type'] = "Demande d'informations";
		}
		$cols['fam2'] = $families[$cols['fam3']]['idParent'];
		$cols['fam1'] = $families[$cols['fam2']]['idParent'];
		$cols['fam1'] = $families[$cols['fam1']]['name'];
		$cols['fam2'] = $families[$cols['fam2']]['name'];
		$cols['fam3'] = $families[$cols['fam3']]['name'];
		
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
			else {
				$worksheet->write($l, $c++, $colData);
			}
		}
		unset($colData);
		
		$l++;	
	}
	
	$result = & $handle->query("select c.id, c.timestamp, null as idProduct, null as name, null as fam1, null as fam2, null as fam3, null as idAdvertiser, null as nom1, null as type, " .
		"c.societe, c.adresse, c.cadresse, c.cp, c.ville, c.pays, c.tel, c.fax, c.prenom, c.nom, c.fonction, c.email, c.siret, c.naf, " .
		"c.salaries, c.secteur, c.url, null as precisions " .
		"from demandes c" . ($EOQ != "" ? " where" . $EOQ : ""), __FILE__, __LINE__);
//	$result = & $handle->query("select societe, adresse, cadresse, cp, ville, tel, fax, null, prenom, nom, fonction, email, siret from demandes" . $EOQ, __FILE__, __LINE__);
	while ($cols = & $handle->fetchAssoc($result))
	{
		$cols['type'] = "Demande de catalogue";
		$cols['timestamp'] = date("Y/m/d H:i", $cols['timestamp']);
		
		$c = 0;
		foreach($cols as $col) $worksheet->write($l, $c++, $col);
		
		$l++;	
	}

	// Let's send the file
	$workbook->close();
}
?>
