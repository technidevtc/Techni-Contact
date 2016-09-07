<?php

/*================================================================/

	Techni-Contact V5 - MD2I SAS
	http://www.techni-contact.com

	Auteur : Hook Network SARL - http://www.hook-network.com
	Date de création : 27 novembre 2007

	Fichier : /includes/siteV2/misc-send-request.php
	Description : Fonctions utilisées par le processus d'envoi des demandes de la boutique en ligne

/=================================================================*/

function loadProduct($id, $cat_id = 0) {
	$db = DBHandle::get_instance();
	$ret = false;
	
	// Get product's data
        try{
	$res = $db->query("
    SELECT
      p.id, p.idTC, p.price, p.cg, p.ci, p.cc,
      pfr.name, pfr.ref_name, pfr.fastdesc, pfr.descc, pfr.descd,
      ffr.id AS cat_id, ffr.name AS cat_name,
      a.idCommercial, a.nom1, a.adresse1, a.cp, a.ville, a.pays, a.tel1, a.fax1, a.contact, a.url AS adv_url, a.id AS adv_id,
      a.email, a.adresse2, a.category, a.econtact, a.contacts, a.from_web, a.cc_foreign, a.cc_intern, a.show_infos_online, a.notRequiredFields,
      a.noLeads2out, a.customFields, a.ic_active, a.ic_fields, a.is_fields,
      rc.id AS ref_idtc
    FROM products p
    INNER JOIN products_fr pfr ON p.id = pfr.id AND pfr.active = 1
    INNER JOIN products_families pf ON p.id = pf.idProduct
    INNER JOIN families_fr ffr ON pf.idFamily = ffr.id".($cat_id?" AND ffr.id = ".$cat_id:"")."
    INNER JOIN advertisers a ON p.idAdvertiser = a.id AND a.actif = 1
    LEFT JOIN references_content rc ON p.id = rc.idProduct AND rc.classement = 1 AND rc.vpc = 1 AND rc.deleted = 0
    WHERE p.id = ".$id."
    GROUP BY p.id", __FILE__, __LINE__);
        }  catch (Exception $e){
          $ret = false;
        }
      if($res)
	if ($db->numrows($res, __FILE__, __LINE__) >= 1) {
		$ret = $db->fetchAssoc($res);
		$ret["oadv_id"] = $ret["adv_id"];
		$ret["oadv_name"] = $ret["nom1"];
        // if there is at least a reference, the product is a supplier one, and so it's idTC is the 1st ref one
        if (isset($ret["ref_idtc"]))
          $ret["idTC"] = $ret["ref_idtc"];
		
		if ($ret["category"] == __ADV_CAT_SUPPLIER__) {
			// Get Techni-Contact data
			$res = $db->query("
			SELECT
				nom1, adresse1, cp, ville, pays,
				tel1, fax1, contact, url AS adv_url, id AS adv_id,
				email, adresse2, econtact, contacts, from_web
			FROM
				advertisers
			WHERE
				id = ".__ID_TECHNI_CONTACT__." AND actif = 1", __FILE__, __LINE__);
			
			if ($db->numrows($res, __FILE__, __LINE__) == 1) {
				$ret2 = $db->fetchAssoc($res);
				$ret = array_merge($ret, $ret2);
			}
			else $ret = false;
		}
		
		if ($ret) {
			$ret["mails_list"] = array();
			if (!empty($ret["email"])) $ret["mails_list"][] = $ret["email"];
			if (!empty($ret["econtact"])) $ret["mails_list"][] = $ret["econtact"];
			$contacts = unserialize($ret["contacts"]);
			if (empty($contacts)) $contacts = array();
			foreach ($contacts as $contact) if (!empty($contact["email"])) $ret["mails_list"][] = $contact["email"];
			
			$res = $db->query("SELECT webpass FROM extranetusers WHERE id = ".$ret["adv_id"], __FILE__, __LINE__);
			list($ret["webpass"]) = $db->fetch($res);
		}
	}
	return $ret;
}

// Number of generated contact for the advertiser from the company between 2 dates
function CountContact(& $handle, $company, $advID, $date_start, $date_end)
{
	$query = "select count(id) " .
		"from contacts " .
		"where societe like '" . $handle->escape($company) . "' " .
			"and idAdvertiser = " . $advID . " " .
			"and timestamp >= " . $date_start . " " .
			"and timestamp < " . $date_end;
			
	$res = & $handle->query($query, __FILE__, __LINE__);
	$cc = & $handle->fetch($res);
	return $cc[0];
}

function GetPriceData($handle, $pInfos) {
	switch ($pInfos["price"]) {
		case "sur demande" :	$pricedata = COMMON_PRICE_ON_DEMAND . "."; break;
		case "sur devis" :		$pricedata = COMMON_PRICE_ON_ESTIMATE . "."; break;
		case "nous contacter" :	$pricedata = COMMON_PRICE_CONTACT_US . "."; break;
		case "0" :				$pricedata = COMMON_PRICE_ON_DEMAND . "."; break;
		case "ref" :
			$pricedata = "";
			$tab_ref_cols = array();
			$tab_ref_lines = array();
			$isFromSupplier = $pInfos["category"] == __ADV_CAT_SUPPLIER__;
			
			$result = & $handle->query("select content from references_cols where idProduct = " . $pInfos["id"], __FILE__, __LINE__);
			$data = & $handle->fetch($result);
			$tab_ref_cols = unserialize($data[0]);
			if ($tab_ref_cols[0] != 'Référence TC')	{ // le tableau de référence est celui d'un annonceur avant lot3
				$nbcols = count($tab_ref_cols)+1;
				
				$tab_ref_cols2 = array();
				$tab_ref_cols2[0] = 'Référence TC';
				$tab_ref_cols2[1] = 'Libellé';
				for ($i = 1; $i < count($tab_ref_cols)-1; $i++) $tab_ref_cols2[$i+1] = $tab_ref_cols[$i];
				$tab_ref_cols2[$nbcols - 1] = 'Prix';
				
				$tab_ref_cols = & $tab_ref_cols2;
				$price2_present = false;
			}
			elseif ($isFromSupplier) {
				if ($tab_ref_cols[2] == 'Référence Fournisseur') $price2_present = true; // tableau de référence fournisseur normal
				else $price2_present = false; // le tableau de référence est celui d'un annonceur après lot3
			}
			
			$result = $handle->query("
        SELECT id, label, content, refSupplier, price, idTVA, unite
        FROM references_content
        WHERE idProduct = " . $pInfos["id"] . " AND vpc = 1 AND deleted = 0
        ORDER BY classement", __FILE__, __LINE__);
			while($data = & $handle->fetchAssoc($result)) $tab_ref_lines[] = $data;
			
			if ($isFromSupplier && $price2_present)
			{
				$pricedata .=	"<div id=\"ref\">\n" .
								"	<table id=\"ref_list\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\" style=\"width: auto; margin: 0\">\n" .
								"		<thead>\n" .
								"		<tr>\n" .
								"			<th>" . COMMON_IDTC_REF . "</th>\n" .
								"			<th>" . COMMON_LABEL . "</th>\n";
				
				for($i = 3; $i < count($tab_ref_cols)-5; ++$i)
					$pricedata .= "			<th>" . htmlentities($tab_ref_cols[$i]) . "</th>\n";
			
				$pricedata .=	"				<th>" . COMMON_UNIT . "</th>\n" .
								"				<th>" . COMMON_UP_EVAT . "</th>\n" .
								"			</tr>\n" .
								"			</thead>\n" .
								"			<tbody>\n";

				for($i=0, $l=count($tab_ref_lines); $i<$l; ++$i) {
					$ref = &$tab_ref_lines[$i];
					$pricedata .=	"		<tr>\n" .
									"			<td>" . htmlentities($ref["id"]) . "</td>\n" .
									"			<td>" . htmlentities($ref["label"]) . "</td>\n";
					$content = unserialize($ref["content"]);
					for($j=0,$l2=count($content); $j<$l2; ++$j) {
						if (trim($content[$j]) == '') $content[$j] = '-';
						$pricedata .= "			<td>" . htmlentities($content[$j]) . "</td>\n";
					}
					$pricedata .=	"			<td>" . htmlentities($ref["unite"]) . "</td>\n" .
									"			<td class=\"ref-prix\">" . htmlentities(sprintf("%.02f",$ref["price"])) . "€</td>\n" .
									"		</tr>\n";
				}
				$pricedata .=	"		</tbody>\n" .
								"	</table>\n" .
								"</div>\n";
			}
			else { // pas de données fournisseur
				$pricedata .=	"<div id=\"ref\">\n" .
								"	<table id=\"ref_list\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"width: auto; margin: 0\">\n" .
								"		<thead>\n" .
								"		<tr>\n";
				
				for($i=1,$l=count($tab_ref_cols); $i<$l; ++$i)
					$pricedata .= "			<th>" . htmlentities($tab_ref_cols[$i]) . "</th>\n";
				
				$pricedata .=	"		</tr>\n" .
								"		</thead>\n" .
								"		<tbody>\n";
				
				for($i=0,$l=count($tab_ref_lines); $i<$l; ++$i) {
					$ref = & $tab_ref_lines[$i];
					$pricedata .=	"		<tr>\n" .
									"			<td>" . htmlentities($ref["label"]) . "</td>\n";
					$content = unserialize($ref["content"]);
					for($j=0,$l2=count($content); $j<$l2; ++$j) {
						if (trim($content[$j]) == '') $content[$j] = '-';
						$pricedata .= "			<td>" . htmlentities($content[$j]) . "</td>\n";
					}
					$pricedata .=	"			<td>" . htmlentities($ref["price"]) . "</td>\n" .
									"		</tr>\n";
				}
				$pricedata .=	"		</tbody>\n" .
								"	</table>\n" .
								"</div>\n";
			}
			break;
		
		default : $pricedata = $pInfos["price"] . " " . COMMON_EUROS . ".";
	}
	
	return $pricedata;
}

function getLeadInvoice($infos, $advID) {
	$db = DBHandle::get_instance();
	$lead = array("invoice_status" => 0, "income" => 0);
	
  $res = $db->query("SELECT category, email, ic_active, ic_fields, is_fields FROM advertisers WHERE id = ".$advID, __FILE__, __LINE__);
  $aics = $db->fetchAssoc($res); // Advertiser Invoicing Customization and Setting
  if ($aics["email"] == "rsamson@centralweb.fr") { // advertiser is a central web advertiser
    $lead["invoice_status"] = __LEAD_INVOICE_STATUS_NOT_CHARGED__;
    $lead["income"] = 0;
  }
  elseif ($aics["category"] == __ADV_CAT_BLOCKED__ || $aics["category"] == __ADV_CAT_LITIGATION__) { // advertiser is blocked, or in litigation : nothing's visible nor charged
    $lead["invoice_status"] = __LEAD_INVOICE_STATUS_NOT_CHARGED__;
    $lead["income"] = 0;
  }
  elseif ($aics["category"] == __ADV_CAT_ADVERTISER_NOT_CHARGED__) { // advertiser is not charged, everything is always visible
    $lead["invoice_status"] = __LEAD_INVOICE_STATUS_IN_FORFEIT__;
    $lead["income"] = 0;
  }
  else {
    $aics["ic_fields"] = empty($aics["ic_fields"]) ? array() : unserialize($aics["ic_fields"]);
    $aics["is_fields"] = empty($aics["is_fields"]) ? array() : unserialize($aics["is_fields"]);
    //mb_convert_variables("ISO-8859-1","UTF-8",$aics);
    if (empty($aics["is_fields"])) { // no invoice settings = not charged
      $lead["invoice_status"] = __LEAD_INVOICE_STATUS_NOT_CHARGED__;
      $lead["income"] = 0;
    }
    else {
      $is_cur = $aics["is_fields"][0];
      if ($is_cur["type"] == "forfeit") { // in forfeit, always visible
        $lead["invoice_status"] = __LEAD_INVOICE_STATUS_IN_FORFEIT__;
        $lead["income"] = 0;
      }
      else {
        $res = $db->query("
          SELECT id
          FROM contacts
          WHERE idAdvertiser = ".$advID."
          AND email = '".$db->escape($infos["email"])."'
          AND (invoice_status = ".__LEAD_INVOICE_STATUS_CHARGEABLE__." OR invoice_status = ".__LEAD_INVOICE_STATUS_CHARGED_PERMANENT__.")
          AND timestamp > ".mktime(0,0,0,date("n"),1,date("Y"))." AND timestamp < ".mktime(0,0,0,date("n")+1,1,date("Y")), __FILE__, __LINE__);
        
        if ($db->numrows($res,__FILE__, __LINE__) != 0) { // it's a doublet
          $lead["invoice_status"] = __LEAD_INVOICE_STATUS_DOUBLET__;
          $lead["income"] = 0;
        }
        else {
          if (!$aics["ic_active"] // if no invoice customisation or if the validation criterions are met
          || ((!isset($aics["ic_fields"]->ic_job) || !in_array($infos["fonction"], $aics["ic_fields"]->ic_job))
           && (!isset($aics["ic_fields"]->ic_activity_sector) || !in_array($infos["secteur"], $aics["ic_fields"]->ic_activity_sector))
           && (!isset($aics["ic_fields"]->ic_company_size) || !in_array($infos["salaries"], $aics["ic_fields"]->ic_company_size))
           && (!isset($aics["ic_fields"]->ic_cp) || in_array(substr($infos["cp"],0,2), explode("|",$aics["ic_fields"]->ic_cp)))
           && (!isset($aics["ic_fields"]->ic_country) || in_array(strtoupper($infos["pays"]), explode("|",$aics["ic_fields"]->ic_country))))) {
            switch($is_cur["type"]) {
              case "lead":
                //print "advID=".$advID." lead charged";
                $lead["invoice_status"] = __LEAD_INVOICE_STATUS_CHARGEABLE__;
                $lead["income"] = (float)$is_cur["fields"]->lead_unit_cost;
                break;
              case "budget":
                switch($is_cur["fields"]->budget_capping_periodicity) {
                  case "year": $monthStart = 1; $monthEnd = 13; break;
                  case "month":
                  default: $monthStart = date("n"); $monthEnd = $monthStart+1;
                }
                $res = $db->query("
                  SELECT COUNT(id)
                  FROM contacts
                  WHERE idAdvertiser = ".$advID."
                  AND (invoice_status = ".__LEAD_INVOICE_STATUS_CHARGEABLE__.")
                  AND timestamp >= ".mktime(0,0,0,$monthStart,1,date("Y"))." AND timestamp < ".mktime(0,0,0,$monthEnd,1,date("Y")), __FILE__, __LINE__);
                list($leadCount) = $db->fetch($res);
                if ($leadCount < $is_cur["fields"]->budget_max_leads) {
                  $lead["invoice_status"] = __LEAD_INVOICE_STATUS_CHARGEABLE__;
                  $lead["income"] = (float)$is_cur["fields"]->budget_unit_cost;
                }
                else {
                  $lead["invoice_status"] = __LEAD_INVOICE_STATUS_NOT_CHARGED__;
                  $lead["income"] = 0;
                }
                break;
              case "forfeit": // cannot happen
                  $lead["invoice_status"] = __LEAD_INVOICE_STATUS_IN_FORFEIT__;
                  $lead["income"] = 0;
                break;
              default: break;
            }
          }
          else {
            $lead["invoice_status"] = __LEAD_INVOICE_STATUS_NOT_CHARGED__;
            $lead["income"] = 0;
          }
        }
      }
    }
  }
	
	return $lead;
}

function MakeCWSocketConnection($infos, $pdtName, $reqType, $advName, $contactID) {
	$CWdata = array();
	$CWdata["nom"] = $infos["nom"];						// Texte Nom
	$CWdata["prenom"] = $infos["prenom"];				// Texte Prénom
	$CWdata["fonction"] = $infos["fonction"];			// Texte Fonction
	$CWdata["telephone"] = $infos["telephone"];			// Texte Téléphone
	$CWdata["fax"] = $infos["fax"];						// Texte Fax
	$CWdata["email"] = $infos["email"];					// Texte Email
	$CWdata["societe"] = $infos["societe"];				// Texte Société
	$CWdata["taille_salariale"] = $infos["salaries"];	// Texte Taille Salariale
	$CWdata["site_internet"] = $infos["url"];			// Texte Site Internet
	$CWdata["secteur"] = $infos["secteur"];				// Texte Secteur d'activité
	$CWdata["naf"] = $infos["naf"];						// Texte Code NAF
	$CWdata["siret"] = $infos["siret"];					// Texte Numéro SIRET
	$CWdata["adresse"] = $infos["adresse"];				// Texte Adresse
	$CWdata["adresse2"] = $infos["adresse2"];			// Texte Complément
	$CWdata["code_postal"] = $infos["cp"];				// Texte Code Postal
	$CWdata["ville"] = $infos["ville"];					// Texte Ville
	$CWdata["pays"] = $infos["pays"];					// Texte Pays
	//$CWdata["infos_sup"] = $infos["infos_sup"];			// Texte Infos supplémentaires
	$CWdata["message"] = $infos["precisions"];			// Texte Message
	$CWdata["produit"] = $pdtName;						// Texte Produit
	$CWdata["type_demande"] = $reqType;					// Texte Type de demande
	$CWdata["partenaire"] = $advName;					// Texte Partenaire
	
	$CWdata1 = array();
	foreach ($CWdata as $key => $value) $CWdata1[] = $key . '=' . urlencode($value);
	$CWdataSocket = implode('&', $CWdata1);
	
	$send = "POST /contact.asp HTTP/1.0\n" .
	"Host: techni-contact.centralweb.fr\n" .
	"Accept: */*\n" .
	"User-Agent: www.techni-contact.com Socket Connection API for Data Exchange with Central WEB\n" .
	"Content-Type: application/x-www-form-urlencoded\n" .
	"Content-Length: " . strlen($CWdataSocket) . "\n\n" .
	$CWdataSocket . "\n";
	
	if (DEBUG || TEST) {
		$fh = fopen(BASE_PATH."logs/".DB_LANGUAGE."/central-web.log","ab");
		$text = str_repeat("-",80)."\n";
		$text .= date("d/m/Y H:i.s")."\n";
		$text .= "Contact ID : ".$contactID."\n";
		$text .= "-- QUERY : \n";
		$params = explode("&",$send);
		foreach($params as $param)
			$text .= $param."\n";
		fwrite($fh, $text);
	}
	else {
		$fh = fopen(BASE_PATH."logs/".DB_LANGUAGE."/central-web.log","ab");
		fwrite($fh, date("d/m/Y H:i.s") . "  ID : " . $contactID . "\n");
		$fp = fsockopen("techni-contact.centralweb.fr", 80, $errno, $errstr, 30);
		if (!$fp) { fwrite($fh, "ERROR : $errstr ($errno)\n"); }
		else {
			fwrite($fp, $send);
			$receiveBuffer = "";
			while (!feof($fp)) $receiveBuffer .= fgets($fp, 128);
			
			fwrite($fh, "-- QUERY : \n");
			fwrite($fh, $send . "\n");
			fwrite($fh, "-- ANSWER : \n");
			fwrite($fh, $receiveBuffer);
			
			fclose($fp);
		}
		fwrite($fh, "\n----------------------------------------\n");
		fclose($fh);
	}
	
	return true;
}
	
?>