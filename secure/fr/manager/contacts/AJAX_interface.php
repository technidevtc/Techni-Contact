<?php

if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

require ADMIN."statut.php";

$user = new BOUser();

header("Content-Type: text/plain; charset=utf-8");

if (!$user->login()) {
  $o["error"] = "Votre session a expiré, veuillez vous identifier à nouveau après avoir rafraîchi votre page";
  print json_encode($o);
  exit();
}

function get_full_pdts_infos($pdtIds, $idTCs = array()) {
  $db = DBHandle::get_instance();
  global $adv_cat_list;

  $querySelectionCommon = "
    p.id,
    p.refSupplier,
    p.price AS pdt_price,
    p.price2 AS pdt_price2,
    p.unite,
    p.idTVA,
    p.contrainteProduit,
    p.ean,
    p.warranty,
    p.shipping_fee,
    p.video_code,
    p.cat3_si,
    p.adv_si,
    p.docs,
    pfr.name,
    pfr.ref_name,
    pfr.alias,
    pfr.fastdesc,
    pfr.descc,
    pfr.descd,
    pfr.delai_livraison AS delivery_time,
    ffr.id AS cat_id,
    ffr.name AS cat_name,
    a.id AS adv_id,
    a.idCommercial AS adv_salesman_id,
    a.nom1 AS adv_name,
    a.nom2 AS adv_name2,
    a.adresse1 AS adv_address1,
    a.adresse2 AS adv_address2,
    a.cp AS adv_pc,
    a.ville AS adv_city,
    a.pays AS adv_country,
    a.delai_livraison AS adv_delivery_time,
    a.contraintePrix AS adv_min_amount,
    a.contact AS adv_contact,
    a.email AS adv_email,
    a.url AS adv_url,
    a.tel1 AS adv_tel1,
    a.tel2 AS adv_tel2,
    a.fax1 AS adv_fax1,
    a.fax2 AS adv_fax2,
    a.econtact AS adv_econtact,
    a.parent AS adv_parent,
    a.ref_name AS adv_ref_name,
    a.category AS adv_cat,
    a.contacts AS adv_contacts,
    a.from_web AS adv_from_web,
    a.cc_foreign AS adv_cc_foreign,
    a.cc_intern AS adv_cc_intern,
    a.help_show AS adv_help_show,
    a.help_msg AS adv_help_msg,
    a.show_infos_online AS adv_show_infos_online,
    a.shipping_fee AS adv_shipping_fee,
    a.warranty AS adv_warranty,
    a.catalog_code AS adv_catalog_code,
    a.notRequiredFields AS adv_notRequiredFields,
    a.customFields AS adv_customFields,
    a.noLeads2in AS adv_noLeads2in,
    a.noLeads2out AS adv_noLeads2out,
    a.ic_active AS adv_ic_active,
    a.ic_fields AS adv_ic_fields,
    a.is_fields AS adv_is_fields,
    eu.webpass AS adv_webpass,
    rc.refSupplier AS ref_refSupplier,
    rc.price AS ref_price,
    rc.price2 AS ref_price2,
    bou.name AS adv_salesman_name,
    bou.email AS adv_salesman_email,
    bou.phone AS adv_salesman_phone";

  $queryJoinCommon = "
    INNER JOIN products_fr pfr ON p.id = pfr.id AND pfr.active = 1
    INNER JOIN products_families pf ON p.id = pf.idProduct
    INNER JOIN families_fr ffr ON pf.idFamily = ffr.id
    INNER JOIN advertisers a ON p.idAdvertiser = a.id AND a.actif = 1
    INNER JOIN extranetusers eu ON a.id = eu.id
    INNER JOIN bo_users bou ON bou.id = a.idCommercial";

  $queries = array();
  if (!empty($pdtIds)) {
    $queries[] = "
      SELECT
        IFNULL(rc.id, p.idTC) AS idTC,
        ".$querySelectionCommon."
      FROM products p".
      $queryJoinCommon."
      LEFT JOIN references_content rc ON p.id = rc.idProduct AND rc.classement = 1 AND rc.vpc = 1 AND rc.deleted = 0
      WHERE pfr.id IN (".implode(",",$pdtIds).") AND pfr.deleted != 1
      GROUP BY p.id";
  }
  if (!empty($idTCs)) {
    $queries[] = "
      SELECT
        rc.id AS idTC,
        ".$querySelectionCommon."
      FROM products p".
      $queryJoinCommon."
      INNER JOIN references_content rc ON p.id = rc.idProduct AND rc.vpc = 1 AND rc.deleted = 0
      WHERE rc.id IN (".implode(",",$idTCs).")
      GROUP BY p.id";
    $queries[] = "
      SELECT
        p.idTC,
        ".$querySelectionCommon."
      FROM products p".
      $queryJoinCommon."
      LEFT JOIN references_content rc ON p.id = rc.idProduct AND rc.vpc = 1 AND rc.deleted = 0
      WHERE rc.id IS NULL AND p.idTC IN (".implode(",",$idTCs).")
      GROUP BY p.id";
  }
  try {
    $res = $db->query(implode(" UNION ", $queries),__FILE__,__LINE__);
  } catch (Exception $e) {
    pp($e);
  }

  if ($db->numrows($res) < 1) // bad result count
    return "Aucun produit n'a été trouvé";

  while ($pdt["infos"] = $db->fetchAssoc($res)) {
    // saving the original partner id and name
    $pdt["infos"]["oadv_id"] = $pdt["infos"]["adv_id"];
    $pdt["infos"]["oadv_name"] = $pdt["infos"]["adv_name"];
    // get techni-contact data in case of a supplier's product
    /*if ($pdt["infos"]["adv_cat"] == __ADV_CAT_SUPPLIER__) {
      $res2 = $db->query("
        SELECT
          a.id AS adv_id,
          a.nom1 AS adv_name,
          a.adresse1 AS adv_address,
          a.adresse2 AS adv_address2,
          a.cp AS adv_pc,
          a.ville AS adv_city,
          a.pays AS adv_country,
          a.contact AS adv_contact,
          a.email AS adv_email,
          a.url AS adv_url,
          a.tel1 AS adv_tel1,
          a.fax1 AS adv_fax1,
          a.econtact AS adv_econtact,
          a.contacts AS adv_contacts,
          a.from_web AS adv_from_web,
          eu.webpass AS adv_webpass
        FROM advertisers a
        INNER JOIN extranetusers eu ON a.id = eu.id
        WHERE a.id = ".__ID_TECHNI_CONTACT__." AND a.actif = 1",__FILE__,__LINE__);
      if ($db->numrows($res2,__FILE__,__LINE__) != 1)
        return "Fatal error while getting Techni-contact's information";

      $ai = $db->fetchAssoc($res2);
      $pdt["infos"] = array_merge($pdt["infos"],$ai);
    }*/

    $pdt["infos"]["adv_mails_list"] = array();
    if (!empty($pdt["infos"]["adv_email"]))
      $pdt["infos"]["adv_mails_list"][] = $pdt["infos"]["adv_email"];
    if (!empty($pdt["infos"]["adv_econtact"]))
      $pdt["infos"]["adv_mails_list"][] = $pdt["infos"]["adv_econtact"];
    $contacts = mb_unserialize($pdt["infos"]["adv_contacts"]);
    if (empty($contacts))
      $contacts = array();
    foreach ($contacts as $contact)
      if (!empty($contact["adv_email"]))
        $pdt["infos"]["adv_mails_list"][] = $contact["adv_email"];

    // Not Required Fields vars
    $pdt["infos"]["adv_notRequiredFields"] = explode(",", $pdt["infos"]["adv_notRequiredFields"]);
    if (empty($pdt["infos"]["adv_notRequiredFields"])) $pdt["infos"]["adv_notRequiredFields"] = array();

    // Custom Fields vars
    $pdt["infos"]["adv_customFields"] = mb_unserialize($pdt["infos"]["adv_customFields"]);
    if (empty($pdt["infos"]["adv_customFields"])) $pdt["infos"]["adv_customFields"] = array();

    $pdt["infos"]["descc"] = preg_replace("/<script [^>]*>[^<]*<\/script>/i","",$pdt["infos"]["descc"]);
    $pdt["infos"]["descd"] = preg_replace("/<script [^>]*>[^<]*<\/script>/i","",$pdt["infos"]["descd"]);

    $pdt["infos"]["adv_cat_name"] = $adv_cat_list[$pdt["infos"]["adv_cat"]]["name"];
    if ($pdt["infos"]["delivery_time"] == "")
      $pdt["infos"]["delivery_time"] = $pdt["infos"]["adv_delivery_time"];
    if ($pdt["infos"]["warranty"] == "")
      $pdt["infos"]["warranty"] = $pdt["infos"]["adv_warranty"];

    // process family
    $familyAscendency = FamiliesOld::getFamilyParent($pdt["infos"]["cat_id"]);
    $cat3 = FamiliesOld::getFamilyInfo($pdt["infos"]["cat_id"]);
    $cat2 = FamiliesOld::getFamilyInfo($familyAscendency);
    $pdt["infos"]["cat2_id"] = $familyAscendency;
    $pdt["infos"]["cat2_name"] = $cat2['name'];
    $pdt["cat2_children"][$familyAscendency] = FamiliesOld::getChildren($familyAscendency);
    $pdt["infos"]["cat3_id"] = $pdt["infos"]["cat_id"];
    $pdt["infos"]["cat3_name"] = $pdt["infos"]["cat_name"];

    // product's url's
    $pdt["urls"]["fo_url"] = URL."produits/".$pdt["infos"]["cat_id"]."-".$pdt["infos"]["id"]."-".$pdt["infos"]["ref_name"].".html";
    $pdt["urls"]["bo_url"] = ADMIN_URL."products/edit.php?id=".$pdt["infos"]["id"];
    $pdt["urls"]["adv_bo_url"] = ADMIN_URL."advertisers/edit.php?id=".$pdt["infos"]["adv_id"];
    $pdt["urls"]["cat3_bo_search_url"] = ADMIN_URL."search.php?search_type=2&search=".$pdt["infos"]["cat3_id"];

    // product's pics
    $pdt["pics"][0]["thumb_small"] = is_file(PRODUCTS_IMAGE_INC."thumb_small/".$pdt["infos"]["id"]."-1.jpg") ? PRODUCTS_IMAGE_SECURE_URL."thumb_small/".$pdt["infos"]["id"]."-1.jpg" : PRODUCTS_IMAGE_SECURE_URL."no-pic-thumb_small.gif";
    $pdt["pics"][0]["card"] = is_file(PRODUCTS_IMAGE_INC."card/".$pdt["infos"]["id"]."-1.jpg") ? PRODUCTS_IMAGE_SECURE_URL."card/".$pdt["infos"]["id"]."-1.jpg" : PRODUCTS_IMAGE_SECURE_URL."no-pic-card.gif";

    $pdt["refs"] = array();
    $pdt["infos"]["hasPrice"] = false;
    $max_margin = 0;
    if ($pdt["infos"]["pdt_price"] == "ref") {
      $pdt["infos"]["price"] = $pdt["infos"]["ref_price"];
      $pdt["infos"]["price2"] = $pdt["infos"]["ref_price2"];

      $res2 = $db->query("
        SELECT content
        FROM references_cols
        WHERE idProduct = ".$pdt["infos"]["id"],__FILE__,__LINE__);
      $row = $db->fetch($res2);
      $content_cols = mb_unserialize($row[0]);
      if ($pdt["infos"]["adv_cat"] == __ADV_CAT_SUPPLIER__)
        $custom_cols = array_slice($content_cols,3,-5);
      else
        $custom_cols = array_slice($content_cols,2,-1);

      $res2 = $db->query("
        SELECT id, label, content, refSupplier, price, price2, idTVA, unite
        FROM references_content
        WHERE idProduct = ".$pdt["infos"]["id"]." AND vpc = 1 AND deleted = 0
        ORDER BY classement",__FILE__,__LINE__);
      while ($row = $db->fetchAssoc($res2)) {
        $row["content"] = mb_unserialize($row["content"]);
        if (!empty($row["price2"]) && $row["price2"] > 0 && $max_margin < $row["price"] / $row["price2"])
          $max_margin = $row["price"] / $row["price2"];
        if ((float) $row["price"] < $fdp_franco)
          $pdt["infos"]["shipping_fee"] = $fdp."&euro;";
        $row["price"] = sprintf("%.2f",$row["price"]);
        $pdt["refs"][] = $row;
      }
    }
    else {
      $pdt["infos"]["price"] = $pdt["infos"]["pdt_price"];
      $pdt["infos"]["price2"] = $pdt["infos"]["pdt_price2"];
      if (!empty($pdt["infos"]["price2"]) && $pdt["infos"]["price2"] > 0)
        $max_margin = $row["price"] / $row["price2"];
    }

    if (empty($pdt["infos"]["price"])) {
      $pdt["infos"]["price"] = "sur devis";
    }
    elseif (preg_match('/^[0-9]+((\.|,)[0-9]+){0,1}$/',$pdt["infos"]["price"])) { // real price
      $pdt["infos"]["hasPrice"] = true;
      if ((float) $pdt["infos"]["price"] < $fdp_franco)
        $pdt["infos"]["shipping_fee"] = $fdp."&euro;";

      $pdt["infos"]["price"] = sprintf("%.2f",$pdt["infos"]["price"]);

      // Calculating the real minimum public amount
      if (!empty($pdt["infos"]["adv_min_amount"]) && preg_match('/^[0-9]+((\.|,)[0-9]+){0,1}$/',$pdt["infos"]["price2"]))
        $pdt["infos"]["adv_min_amount"] = $pdt["infos"]["adv_min_amount"] * $max_margin;
    }
    else {
      $pdt["infos"]["price"] = "sur devis";
    }

    $pdt["infos"]["adv_min_amount"] = $pdt["infos"]["adv_min_amount"] > 0 ? sprintf("%.0f", $pdt["infos"]["adv_min_amount"])."&euro;" : "non";

    $pdt["infos"]["saleable"] = $pdt["infos"]["hasPrice"] && $pdt["infos"]["adv_cat"] == __ADV_CAT_SUPPLIER__;
    $pdt["infos"]["ref_count"] = count($pdt["refs"]);
    if ($pdt["infos"]["ref_count"] > 0)
      array_unshift($pdt["refs"],$custom_cols); // put refs headers on the line 0

    // Loading Shipping Fee
    if ($res2 = $db->query("select config_name, config_value from config where config_name = 'fdp' or config_name = 'fdp_franco' or config_name = 'fdp_sentence'",__FILE__,__LINE__)) {
      while ($rec = $db->fetch($res2)) {
        $$rec[0] = $rec[1];
      }
    }

    $pdt["infos"]["shipping_fee"] = empty($pdt["infos"]["shipping_fee"]) ? ($pdt["infos"]["shipping_fee"] = $pdt["infos"]["hasPrice"] ? ($pdt["infos"]["price"] > $fdp_franco ? "Offerts" : $fdp." &euro; HT") : "N/D") : $pdt["infos"]["shipping_fee"]." &euro; HT";

    $pdtList[] = $pdt;
  }

  return $pdtList;
}

function GetPriceHtml($pdt) {
  $db = DBHandle::get_instance();
  $refs = array();
  if ($pdt["pdt_price"] == "ref") {
    $pdt["price"] = $pdt["ref_price"];
    $pdt["price2"] = $pdt["ref_price2"];

    $res = $db->query("
      SELECT content
      FROM references_cols
      WHERE idProduct = ".$pdt["id"],__FILE__,__LINE__);
    $row = $db->fetch($res);
    $content_cols = mb_unserialize($row[0]);
    if ($pdt["adv_cat"] == __ADV_CAT_SUPPLIER__)
      $custom_cols = array_slice($content_cols,3,-5);
    else
      $custom_cols = array_slice($content_cols,2,-1);

    $res = $db->query("
      SELECT id, label, content, refSupplier, price, price2, idTVA, unite
      FROM references_content
      WHERE idProduct = ".$pdt["id"]." AND vpc = 1 AND deleted = 0
      ORDER BY classement",__FILE__,__LINE__);
    while ($row = $db->fetchAssoc($res)) {
      $row["content"] = mb_unserialize($row["content"]);
      $row["price"] = sprintf("%.2f",$row["price"]);
      $refs[] = $row;
    }
  }
  else {
    $pdt["price"] = $pdt["pdt_price"];
    $pdt["price2"] = $pdt["pdt_price2"];
  }

  $hasPrice = false;
  if (empty($pdt["price"])) {
    $pdt["price"] = "sur devis";
  }
  elseif (preg_match('/^[0-9]+((\.|,)[0-9]+){0,1}$/',$pdt["price"])) { // real price
    $hasPrice = true;
    $pdt["price"] = sprintf("%.2f",$pdt["price"]);
  }
  else {
    $pdt["price"] = "sur devis";
  }
  $ref_count = count($refs);
  $saleable = $hasPrice && $pdt["adv_cat"] == __ADV_CAT_SUPPLIER__;

  if ($ref_count > 0) {
    ob_start();
?>
<div id="ref">
  <table id="ref_list" cellspacing="0" cellpadding="0" border="0" align="center" style="width: auto; margin: 0">
    <thead>
    <tr>
      <th>Réf. TC</th>
      <th>Libellé</th>
     <?php foreach($custom_cols as $colName) { ?>
      <th><?php echo to_entities($colName) ?></th>
     <?php } ?>
      <th>Unité</th>
      <th>Prix HT</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach($refs as $ref) {?>
      <tr>
        <td><?php echo $ref["id"] ?></td>
        <td><?php echo to_entities($ref["label"]) ?></td>
      <?php foreach($ref["content"] as $colValue) { ?>
        <td><?php echo to_entities($colValue) ?></td>
      <?php } ?>
        <td><?php echo $ref["unite"] ?></td>
        <td class="ref-prix"><?php echo sprintf("%.2f",$ref["price"]) ?>&euro;</td>
      </tr>
    <?php } ?>
    </tbody>
  </table>
</div>
<?php
    $pricedata = ob_get_clean();
  }
  else {
    if ($hasPrice)
      $pricedata = sprintf("%.2f",$pdt["price"])."&euro; HT";
    else
      $pricedata = $pdt["price"];
  }
  return $pricedata;
}

$db = DBHandle::get_instance();
$o = array("data" => array(),"error" => "");
$actions = $_POST["actions"];
foreach($actions as $action) {
  switch ($action["action"]) {
    case "get_pdts_infos":
      if (!$user->get_permissions()->has("m-prod--sm-products","r")) {
        $o["error"] = "Vous n'avez pas les droits adéquats pour réaliser cette opération.";
        break;
      }

      $pdtIds = $idTCs = array();
      if (isset($action["pdtIds"]) && is_array($action["pdtIds"])) {
        foreach ($action["pdtIds"] as $pdtId) {
          if (is_array($pdtId)) $pdtId = $pdtId[0];
          if (is_numeric(trim($pdtId)))
            $pdtIds[] = $pdtId;
        }
      }
      if (isset($action["idTCs"]) && is_array($action["idTCs"])) {
        foreach ($action["idTCs"] as $idTC) {
          if (is_array($idTC)) $idTC = $idTC[0];
          if (is_numeric(trim($idTC)))
            $idTCs[] = $idTC;
        }
      }
      if (empty($pdtIds) && empty($idTCs)) {
        $o["error"] = "Ids fiches produits ou idTC's non spécifiés ou invalides";
        break;
      }

      $pdtList = get_full_pdts_infos($pdtIds, $idTCs);
      if (is_string($pdtList)) {
        $o["error"] = $pdtList;
        break;
      }

      $o["data"]["pdtList"] = $pdtList;
      break;

    case "get_pdt_infos" :
      if (!$user->get_permissions()->has("m-prod--sm-products","r")) {
        $o["error"] = "Vous n'avez pas les droits adéquats pour réaliser cette opération.";
        break;
      }

      $pdtIds = isset($action["pdtId"]) && is_numeric($action["pdtId"]) ? array((int)$action["pdtId"] ): array();
      $idTCs = isset($action["idTC"]) && is_numeric($action["idTC"]) ? array((int)$action["idTC"]) : array();
      if (empty($pdtIds) && empty($idTCs)) {
        $o["error"] = "Id fiche produit ou idTC non spécifié ou invalide";
        break;
      }

      $pdtList = get_full_pdts_infos($pdtIds, $idTCs);
      if (is_string($pdtList)) {
        $o["error"] = $pdtList;
        break;
      }

      $o["data"]["pdtList"] = $pdtList;
      break;

    case "get_customer_infos":
      if (!$user->get_permissions()->has("m-comm--sm-customers","r")) {
        $o["error"] = "Vous n'avez pas les droits adéquats pour réaliser cette opération.";
        break;
      }
      if (!isset($action["customerEmail"])) {
        $o["error"]["text"] = "Aucun email client spécifié";
        break;
      }
      if (!preg_match("/^[[:alnum:]]([-_.]?[[:alnum:]])*@[[:alnum:]]([-_.]?[[:alnum:]])*\.([a-z]{2,4})$/",$action["customerEmail"])) {
        $o["error"]["text"] = "Email spécifié non valide";
        break;
      }

      $res = $db->query("
        SELECT
          id,
          tel1 AS telephone,
          nom,
          prenom,
          fax1 AS fax,
          email,
          fonction,
          societe,
          adresse,
          complement AS cadresse,
          cp,
          ville,
          pays,
          nb_salarie,
          secteur_activite,
          secteur_qualifie,
          code_naf,
          num_siret
        FROM clients
        WHERE email = '".$db->escape($action["customerEmail"])."'",__FILE__,__LINE__);
      if ($db->numrows($res,__FILE__,__LINE__) != 1) {
        $o["error"]["text"] = "Aucun client avec cet email n'a été trouvé";
        break;
      }
      $o["data"]["customer_infos"] = $db->fetchAssoc($res);

      $secteurList = Doctrine_Core::getTable('ActivitySector');
      $secteur = $secteurList->findBySector(htmlspecialchars($o["data"]["customer_infos"]['secteur_activite']));

      foreach($secteur[0]->Surqualifications as $surqualification)
        $surqualifications[] = $surqualification->qualification;

      if(!empty ($o["data"]["customer_infos"]['secteur_qualifie']))
        if(in_array ($o["data"]["customer_infos"]['secteur_qualifie'], $surqualifications) !== false)
           $o["data"]["customer_infos"]['sector_qualification'] = htmlspecialchars($o["data"]["customer_infos"]['secteur_qualifie']);
        else
          $o["data"]["customer_infos"]['qualification_sector_text'] = htmlspecialchars($o["data"]["customer_infos"]['secteur_qualifie']);

      $queryMain = "
        SELECT
          c.id,
          c.societe AS company,
          c.timestamp AS date,
          c.invoice_status,
          c.income,
          c.income_total,
          c.parent,
          c.reject_timestamp,
          c.credited_on,
          pfr.id AS pdt_id,
          pfr.name AS pdt_name,
          a.id AS adv_id,
          a.nom1 AS adv_name,
          a.category AS adv_category,
          a.is_fields AS adv_is_fields
        FROM contacts c
        LEFT JOIN products_fr pfr ON c.idProduct = pfr.id
        LEFT JOIN advertisers a ON c.idAdvertiser = a.id";
      $res = $db->query($queryMain." WHERE c.parent = 0 AND c.email = '".$db->escape($action["customerEmail"])."' ORDER BY c.timestamp DESC, c.id",__FILE__,__LINE__);
      $leadList = array();
      $leadIdList = array();
      while($lead = $db->fetchAssoc($res)) {
        $leadIdList[] = $lead["id"];
        $lead["date"] = date("d/m/Y à H:i", $lead["date"]);
        $lead["adv_category_name"] = $adv_cat_list[$lead["adv_category"]]["name"];
        $lead["invoice_status"] = $lead_invoice_status_list[$lead["invoice_status"]].getCreditMonth($lead);
        $leadList[] = $lead;
      }
      if (!empty($leadIdList)) {
        $res = $db->query($queryMain." WHERE c.parent IN (".implode(",",$leadIdList).") and c.invoice_status != ".__LEAD_INVOICE_STATUS_CREDITED__);
        $lead2List = array();
        while ($lead2 = $db->fetchAssoc($res)) {
          $lead2["date"] = date("d/m/Y à H:i", $lead2["date"]);
          $lead2["adv_category_name"] = $adv_cat_list[$lead2["adv_category"]]["name"];
          $lead2["pdt_id"] = empty($lead2["pdt_id"]) ? "" : $lead2["pdt_id"];
          $lead2["pdt_name"] = empty($lead2["pdt_name"]) ? "" : $lead2["pdt_name"];
          $lead2["invoice_status"] = $lead_invoice_status_list[$lead2["invoice_status"]].getCreditMonth($lead2);
          $lead2List[$lead2["parent"]][] = $lead2;
        }
      }
      foreach ($leadList as &$lead) {
        $lead["clc"] = isset($lead2List[$lead["id"]]) ? count($lead2List[$lead["id"]]) : 0;
        $lead["lead2_list"] = isset($lead2List[$lead["id"]]) ? $lead2List[$lead["id"]] : array();
      }
      unset($lead);

      $o["data"]["lead_list"] = $leadList;
      break;

    case "get_lead_infos":
      if (!$user->get_permissions()->has("m-comm--sm-leads","r")) {
        $o["error"] = "Vous n'avez pas les droits adéquats pour réaliser cette opération.";
        break;
      }
      $leadId = isset($action["leadId"]) && is_numeric($action["leadId"]) ? (int)$action["leadId"] : 0;
      if (empty($leadId)) {
        $o["error"] = "Id lead non spécifié ou invalide";
        break;
      }
      $res = $db->query("
        SELECT
          c.id, c.timestamp AS date, c.nom, c.prenom, c.fonction, c.societe, c.salaries, c.secteur, c.naf, c.siret, c.adresse, c.cadresse,
          c.cp, c.ville, c.pays, c.tel, c.fax, c.email, c.url, c.precisions, c.type, c.campaignID, c.customFields,
          pfr.name AS pdt_name, pfr.id AS pdt_id, pfr.ref_name AS pdt_ref_name,
          ffr.id AS cat_id, ffr.name AS cat_name, ffr.ref_name as cat_ref_name,
          a.id AS adv_id, a.nom1 AS adv_name, a.category AS adv_category, a.is_fields AS adv_is_fields
        FROM contacts c
        LEFT JOIN products_fr pfr ON c.idProduct = pfr.id
        LEFT JOIN families_fr ffr ON c.idFamily = ffr.id
        LEFT JOIN advertisers a ON c.idAdvertiser = a.id
        WHERE c.id = ".$leadId, __FILE__, __LINE__);
      $lead = $db->fetchAssoc($res);

      if (!empty($lead["customFields"])) {
        $lead["customFields"] = mb_unserialize($lead["customFields"]);
        if (empty($lead["customFields"]))
          $lead["customFields"] = array();
      }

      $lead["prenom"] = ucwords(strtolower($lead["prenom"]));
      $lead["fonction"] = ucwords(strtolower($lead["fonction"]));
      $lead["email"] = strtolower($lead["email"]);
      $lead["url"] = !empty($lead["url"]) ? $lead["url"] : "N/C";
      $lead["fax"] = !empty($lead["fax"]) ? $lead["fax"] : "N/C";
      $lead["salaries"] = !empty($lead["salaries"]) ? $lead["salaries"] : "N/C";
      $lead["secteur"] = ucwords(strtolower($lead["secteur"]));
      $lead["naf"] = !empty($lead["naf"]) ? $lead["naf"] : "N/C";
      $lead["siret"] = !empty($lead["siret"]) ? $lead["siret"] : "N/C";
      $lead["adresse"] = ucwords(strtolower($lead["adresse"])).(!empty($lead["cadresse"]) ? "<br/>".ucwords(strtolower($lead["cadresse"])):"");
      $lead["date"] = date("d/m/Y à H:i", $lead["date"]);
      $lead["pdt_name"] = !empty($lead["pdt_name"]) ? $lead["pdt_name"] : "lead secondaire";
      $lead["adv_category_name"] = $adv_cat_list[$lead["adv_category"]]["name"];

      $o["data"]["lead_detail"] = $lead;
      break;

    case "create_lead":
      if (!$user->get_permissions()->has("m-smpo--sm-lead-create","e")) {
        $o["error"] = "Vous n'avez pas les droits adéquats pour réaliser cette opération.";
        break;
      }
      if (!isset($action["pdtId"]) || !is_numeric(trim($action["pdtId"]))) {
        $o["error"] = "Pas de d'id produit spécifié";
        break;
      }
      $origin = isset($action["origin"]) ? trim($action["origin"]) : "Internaute";

      $pdtIds = array((int)$action["pdtId"]);
      $pdtList = get_full_pdts_infos($pdtIds);
      if (is_string($pdtList)) {
        $o["error"] = $pdtList;
        break;
      }
      $pdt = $pdtList[0];

      // Not Required Fields vars
      $notReqFields = array_flip($pdt["infos"]["adv_notRequiredFields"]);

      // Custom Fields vars
      $customFields = $pdt["infos"]["adv_customFields"];
      $cf_count = count($customFields);
      $cfv = array(); // Custom Fields Values

      $fields = $action["fields"];
      unset($fields["id"]); // in case an id is passed, we unset it to make sure we create a new lead
      foreach ($fields as &$field)
        $field = trim($field);
      
      // required fields
      if (empty($fields["nom"]))
        $o["error"]["list"]["nom"] = "Nom requis";

      if (empty($fields["prenom"]))
        $o["error"]["list"]["prenom"] = "Prénom requis";

      if (empty($fields["telephone"]))
        $o["error"]["list"]["telephone"] = "Téléphone requis";

      if (!preg_match("/^[[:alnum:]]([-_.]?[[:alnum:]])*@[[:alnum:]]([-_.]?[[:alnum:]])*\.([a-z]{2,4})$/",$fields["email"]))
        $o["error"]["list"]["email"] = "Email valide requis";

      // required fields if set as if
      if (empty($fields["fonction"]) && !isset($notReqFields["fonction"]))
        $o["error"]["list"]["fonction"] = "Fonction requise";

      if (empty($fields["societe"]) && !isset($notReqFields["societe"]))
        $o["error"]["list"]["societe"] = "Nom de la société requis";
      else{
        // restraining activity sector and naf
        $terms = preg_replace('/ de /', ' ', $fields["societe"]);
        $terms = explode(' ', $terms);
        $ActivitySector = Doctrine_Core::getTable('ActivitySectorSurqualification');
        $ActivitySector->batchUpdateIndex();
        $array_results = array();
        foreach($terms as $term){
          $term = Utils::toDashAz09($term);

          if($result = $ActivitySector->search($term)){
            $q = Doctrine_Query::create()
              ->from('ActivitySector as')
              ->leftJoin('as.Surqualifications ass')
              ->where('ass.id = ?', $result[0]['id']);

            $array_results[$result[0]['id']] = $result[0]['id'];
            $sector = $q->fetchArray();
            $results[] = $result;
          }
        }

        if(count($array_results) == 1){
          $fields["secteur_activite"] = $sector[0]['sector'];
          $fields["secteur_qualifie"] = $fields["qualification"] = $sector[0]['Surqualifications'][0]['qualification'];
          $fields["code_naf"] = $sector[0]['Surqualifications'][0]['naf'];
        }
        // restraining activity sector and naf
      }

      if (empty($fields["adresse"]) && !isset($notReqFields["adresse"]))
        $o["error"]["list"]["adresse"] = "Adresse requise";

      if (empty($fields["cadresse"]) && !isset($notReqFields["complement"]))
        $o["error"]["list"]["cadresse"] = "Complément d'adresse requis";

      if (empty($fields["cp"]) && !isset($notReqFields["cp"]))
        $o["error"]["list"]["cp"] = "Code postal requis";

      if (empty($fields["ville"]) && !isset($notReqFields["ville"]))
        $o["error"]["list"]["ville"] = "Ville requiss";

      if (empty($fields["pays"]) && !isset($notReqFields["pays"]))
        $o["error"]["list"]["pays"] = "Pays requis";

      if (empty($fields["nb_salarie"]) && !isset($notReqFields["nb_salarie"]))
        $o["error"]["list"]["nb_salarie"] = "Taille salariale requise";

      if (empty($fields["secteur_activite"]) && !isset($notReqFields["secteur_activite"]))
        $o["error"]["list"]["secteur_activite"] = "Secteur d'activité requis";

      if (empty($fields["code_naf"]) && !isset($notReqFields["code_naf"]))
        $o["error"]["list"]["code_naf"] = "Code NAF requis";

      if (empty($fields["num_siret"]) && !isset($notReqFields["num_siret"]))
        $o["error"]["list"]["num_siret"] = "Numéro SIRET requis";

      if (!empty($fields["url"])) {
        if (!preg_match('/^((http|https|ftp):\/\/)?(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)(:(\d+))?\/?$/i',$fields["url"]))
          $o["error"]["list"]["url"] = true;
        else {
          if (!preg_match('/^(http|https|ftp):\/\//i',$fields["url"]))
            $fields["url"] = 'http://'.$fields["url"];
          if (strrpos($fields["url"],'/') != strlen($fields["url"]) - 1)
            $fields["url"] .= '/';
        }
      }

      // Custom Fields
      for ($i=0; $i<$cf_count; $i++) {
        $cf = &$customFields[$i];

        $cf_value = trim($fields[$cf["name"]]);
        $cf["length"] = (int) $cf["length"];
        if ($cf["length"] > 0)
          $cf_value = substr($cf_value,0,$cf["length"]);
        $valueList = explode(",",$cf["valueList"]);
        if ($cf["type"] == "select" && !in_array($cf_value,$valueList))
          $o["error"]["list"][$cf["name"]] = true;
        if (!empty($cf_value)) {
          switch ($cf["validationType"]) {
            case "integer":
              if (!preg_match('`^[0-9]+$`',$cf_value))
                $o["error"]["list"][$cf["name"]] = true;
              break;
            case "date":
              if (!preg_match('`^[0-3]?[0-9]{1})(\/|-|\s)[0-2]?[0-9]{1}(\/|-|\s)[0-9]{4,4}$`',$cf_value))
                $o["error"]["list"][$cf["name"]] = true;
              break;
            case "email":
              if (!preg_match('`^[[:alnum:]]([-_.]?[[:alnum:]])*@[[:alnum:]]([-_.]?[[:alnum:]])*\.([a-z]{2,4})$`',$cf_value))
                $o["error"]["list"][$cf["name"]] = true;
              break;
            case "url":
              if (!preg_match('/^((http|https|ftp):\/\/)?(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)(:(\d+))?\/?$/i',$cf_value))
                $o["error"]["list"][$cf["name"]] = true;
              break;
            default: break;
          }
        }
        elseif ($cf["required"])
          $o["error"]["list"][$cf["name"]] = true;

        $cfv[$cf["name"]] = $cf_value;
      }

      if (isset($o["error"]["list"])) {
        $ec = count($o["error"]["list"]);
        $o["error"]["text"] = $ec." champ".($ec<=1?"":"s")." du formulaire ".($ec<=1?"n'est pas valide":"ne sont pas valides");
        break;
      }

      // set booleans fields as boolean values
      $fields["sl"] = $fields["sl"] == "false" ? false : true;

      // changing some fields names, because their names are different between the table and the serialized field notRequiredFields
      $fields["tel"] = $fields["telephone"];
      $fields["naf"] = $fields["code_naf"];
      $fields["siret"] = $fields["num_siret"];
      $fields["salaries"] = $fields["nb_salarie"];
      $fields["secteur"] = $fields["secteur_activite"];

      //pp($lead->getData());
      $lead1 = new Lead($fields);

      require(SITE."misc-send-request.php");
      //require(ICLASS."_ClassEmail.php");
      $queries = array();

      $lead1->idProduct = $pdt["infos"]["id"];
      $lead1->idFamily = $pdt["infos"]["cat_id"];
      $lead1->idAdvertiser = $pdt["infos"]["oadv_id"];
      $lead1->type = 3; // always an estimate since V3
      $lead1->create_time = time();
      $lead1->sent = !empty($fields["email"]) ? 1 : 0;
      $lead1->income_total = 0;
      $lead1InvoiceData = getLeadInvoice($fields, $pdt["infos"]["oadv_id"]);
      $lead1->invoice_status = $lead1InvoiceData["invoice_status"];
      $lead1->income = $lead1InvoiceData["income"];
      $lead1->income_total += $lead1InvoiceData["income"];
      $lead1->customFields = $cfv;
      $lead1->id_user = $user->id;
      $lead1->id_user_commercial = $pdt["infos"]["adv_salesman_id"];
      $lead1->processing_status = __LEAD_P_STATUS_NOT_PROCESSED__;
      $lead1->origin = $origin;

      // main product partner infos
	  $ml_adv_infos = '';

	  
	  
      // show infos if the partner is a supplier or if the lead is visible for him
      if ($pdt["infos"]["adv_cat"] == __ADV_CAT_SUPPLIER__ || $lead1->invoice_status & __LEAD_VISIBLE__) {
	  
		//Change on 03/12/2014 Add static text form the mail send
		$ml_adv_infos = 'Votre demande est archivée dans votre <a href="'.COMPTE_URL.'login.html">compte client gratuit</a><br />
          <br />
          Vous pourrez aussi depuis ce compte communiquer directement avec nos partenaires.<br />
          <br />
          Votre demande a été transmise au fournisseur suivant dont voici les coordonnées :<br /><br />';
		  
		  
        $ml_adv_infos .= "<strong>".$pdt["infos"]["adv_name"]."</strong><br/>".
        $pdt["infos"]["adv_address1"]."<br/>".
        $pdt["infos"]["adv_address2"]."<br/>".
        $pdt["infos"]["adv_pc"]." – ".$pdt["infos"]["adv_city"]."<br/>".
        $pdt["infos"]["adv_country"]."<br/>".
        "Téléphone : ".$pdt["infos"]["adv_tel1"]."<br/>".
        "Fax : ".$pdt["infos"]["adv_fax1"]."<br/>".
        "Contact : ".$pdt["infos"]["adv_contact"]." ".(!empty($pdt["infos"]["adv_email"]) ? "<a href=\"mailto:".$pdt["infos"]["adv_email"]."\">".$pdt["infos"]["adv_email"]."</a>" : "")."<br/>".
        (!empty($pdt["infos"]["adv_url"]) ? "<a href=\"".$pdt["infos"]["adv_url"]."\">".$pdt["infos"]["adv_url"]."</a><br/>" : "");
      }

      $lead2list = array();
      $sl_advCount = $lplCount = 0;
      if ($fields["sl"] && $pdt["infos"]["adv_noLeads2out"] == 0) { // if user wants to send his demand to linked/other advertisers

        // secondary leads from linked products
        // only one product per advertiser that is not the original one of the current product
        $lpl = array();
        $res = $db->query("
          SELECT pl.idProductLinked
          FROM productslinks pl
          INNER JOIN products p ON pl.idProduct = p.id
          WHERE p.id = ".$pdt["infos"]["id"]." AND p.idAdvertiser != ".$pdt["infos"]["oadv_id"]."
          GROUP BY p.idAdvertiser", __FILE__, __LINE__);

        while (list($lpdt_id) = $db->fetch($res)) {
          $lpdt = get_full_pdt_infos($lpdt_id);
          if (!is_string($lpdt))
            $lpl[] = $lpdt;
        }
        $lplCount = count($lpl);

        $sl_advList = array();
        if ($lplCount) { // secondary leads from linked products
          for ($k=0; $k<$lplCount; $k++) {
            $lpdt = $lpl[$k];
            $lead2InvoiceData = getLeadInvoice($fields, $lpdt["adv_id"]);
            $lpdt["lead_status"] = $lead2InvoiceData["invoice_status"];
            $lead2 = new Lead($fields);
            $lead2->idProduct = $lpdt["id"];
            $lead2->idFamily = $lpdt["cat_id"];
            $lead2->idAdvertiser = $lpdt["adv_id"];
            $lead2->type = 3; // always an estimate since V3
            $lead2->create_time = time();
            $lead2->sent = !empty($fields["email"]) ? 1 : 0;
            $lead2->invoice_status = $lead2InvoiceData["invoice_status"];
            $lead2->income = $lead2InvoiceData["income"];
            $lead2->income_total = 0;
            $lead2->parent = $lead1->id;
            $lead2->customFields = $cfv;
            $lead2->id_user = $user->id;
            $lead2->origin = $origin;
            $lead1->income_total += $lead2->income;

            $lead2list[] = $lead2;
            $sl_advList[] = $lpdt;
          }
        }
        else { // secondary leads from same category
          if ($pdt["infos"]["adv_cat"] != __ADV_CAT_SUPPLIER__) {
            $res = $db->query("
              SELECT DISTINCT
                a.id AS adv_id,
                a.nom1 AS adv_name,
                a.category AS adv_cat,
                a.adresse1 AS adv_address1,
                a.adresse2 AS adv_address2,
                a.cp AS adv_pc,
                a.ville AS adv_city,
                a.pays AS adv_country,
                a.contact AS adv_contact,
                a.email AS adv_email,
                a.url AS adv_url,
                a.tel1 AS adv_tel1,
                a.fax1 AS adv_fax1,
                a.econtact AS adv_econtact,
                a.contacts AS adv_contacts,
                a.from_web AS adv_from_web,
                eu.webpass AS adv_webpass
              FROM advertisers a
              INNER JOIN extranetusers eu ON a.id = eu.id
              INNER JOIN products p ON a.id = p.idAdvertiser
              INNER JOIN products_fr pfr ON p.id = pfr.id AND pfr.active = 1 AND pfr.deleted != 1
              INNER JOIN products_families pf ON p.id = pf.idProduct AND pf.idFamily = ".$pdt["infos"]["cat_id"]."
              LEFT JOIN auto_reject_links arl ON arl.idAdvertiser = a.id AND arl.idProduct = ".$pdt["infos"]["id"]." AND arl.idFamily = ".$pdt["infos"]["cat_id"]."
              WHERE
                a.id != ".$pdt["infos"]["adv_id"]." AND
                a.actif = 1 AND
                a.noLeads2in = 0 AND
                a.category != ".__ADV_CAT_SUPPLIER__."
                AND arl.idProduct IS NULL", __FILE__, __LINE__);
            while ($adv = $db->fetchAssoc($res)) {
              $adv["cat_name"] = $pdt["infos"]["cat_name"];

              // list every advertiser mails
              $adv["adv_mails_list"] = array();
              if (!empty($adv["adv_email"])) $adv["adv_mails_list"][] = $adv["adv_email"];
              if (!empty($adv["adv_econtact"])) $adv["adv_mails_list"][] = $adv["adv_econtact"];
              $contacts = mb_unserialize($adv["adv_contacts"]);
              if (empty($contacts)) $contacts = array();
              foreach ($contacts as $contact) if (!empty($contact["adv_email"])) $adv["adv_mails_list"][] = $contact["adv_email"];

              $lead2InvoiceData = getLeadInvoice($fields, $adv["adv_id"]);
              $adv["lead_status"] = $lead2InvoiceData["invoice_status"];
              $lead2 = new Lead($fields);
              $lead2->idProduct = 0;
              $lead2->idFamily = $pdt["infos"]["cat_id"];
              $lead2->idAdvertiser = $adv["adv_id"];
              $lead2->type = 3; // always an estimate since V3
              $lead2->create_time = time();
              $lead2->sent = !empty($fields["email"]) ? 1 : 0;
              $lead2->invoice_status = $lead2InvoiceData["invoice_status"];
              $lead2->income = $lead2InvoiceData["income"];
              $lead2->income_total = 0;
              $lead2->parent = $lead1->id;
              $lead2->customFields = $cfv;
              $lead2->id_user = $user->id;
              $lead2->origin = $origin;
              $lead1->income_total += $lead2->income;

              $lead2list[] = $lead2;
              $sl_advList[] = $adv;
            }
          }
        }
        $sl_advCount = count($sl_advList);
      }

      // saving every leads 1 & 2
      $lead1->save();
      foreach($lead2list as $lead2)
        $lead2->save();

      // custom fields
      $cfvs = "";
      foreach ($cfv as $cfk => $cfd)
        $cfvs .= "<div class=\"sousTitreBloc\">".$cfk." : <span class=\"coord2\">".$cfd."</span></div>\n";

      // secondary lead partner infos
      $sl_linked_pdt_infos = $sl_same_cat_infos = "";

      if ($sl_advCount) {
        if ($lplCount) { // linked products are present
          foreach ($sl_advList as $adv) {

            // only show infos if the partner is a supplier or if he is an advertiser and the lead is visible for him
            if ($adv["adv_cat"] == __ADV_CAT_SUPPLIER__
            || (($adv["adv_cat"] == __ADV_CAT_ADVERTISER__ || $adv["adv_cat"] == __ADV_CAT_ADVERTISER_NOT_CHARGED__)
             && ($adv["lead_status"] & __LEAD_VISIBLE__))) {

            //if ($adv["adv_email"] != "rsamson@centralweb.fr" && $adv["adv_cat"] != __ADV_CAT_PROSPECT__ && $adv["adv_cat"] != __ADV_CAT_BLOCKED__&& $adv["adv_cat"] != __ADV_CAT_LITIGATION__) {
              $sl_linked_pdt_infos .= "<br/>Produit : ";
              $sl_linked_pdt_infos .= "<b><a href=\"".URL."produits/".$adv["cat_id"]."-".$adv["id"]."-".$adv["ref_name"].".html\">".$adv["name"]."</a></b>";
              $sl_linked_pdt_infos .= "<br/><br/>\n".
              "<b>Fourni par :</b><br/>".
              "<br/>".
              "<b>".$adv["adv_name"]."</b><br/>".
              $adv["adv_address1"]."<br/>".
              (empty($adv["adv_address2"]) ? "" : $adv["adv_address2"]."<br/>").
              $adv["adv_pc"]." ".$adv["adv_city"]."<br/>".
              $adv["adv_country"]."<br/>".
              "Téléphone : ".$adv["adv_tel1"]."<br/>".
              "Fax : ".$adv["adv_fax1"]."<br/>".
              "Contact : ".$adv["adv_contact"]." ".(!empty($adv["adv_email"])?"<a href=\"mailto:".$adv["adv_email"]."\">".$adv["adv_email"]."</a>":"")."<br/>".
              (empty($adv["adv_url"]) ? "" : $adv["adv_url"]."<br/>");
            }
          }
        }
        else { // no linked product -> same cat secondary leads
          foreach ($sl_advList as $adv) {
            // only show infos if the partner is a supplier or if he is an advertiser and the lead is visible for him
            if ($adv["category"] == __ADV_CAT_SUPPLIER__
            || (($adv["category"] == __ADV_CAT_ADVERTISER__ || $adv["category"] == __ADV_CAT_ADVERTISER_NOT_CHARGED__)
             && ($adv["lead_status"] & __LEAD_VISIBLE__))) {
              $sl_same_cat_infos .= "<br/>".
              "<b>".$adv["adv_name"]."</b><br/>".
              $adv["adv_address1"]."<br/>".
              (empty($adv["adv_address2"]) ? "" : $adv["adv_address2"]."<br/>").
              $adv["adv_pc"]." ".$adv["adv_city"]."<br/>".
              $adv["adv_country"]."<br/>".
              "Téléphone : ".$adv["adv_tel1"]."<br/>".
              "Fax : ".$adv["adv_fax1"]."<br/>".
              "Contact : ".$adv["adv_contact"]." ".(!empty($adv["adv_email"])?"<a href=\"mailto:".$adv["adv_email"]."\">".$adv["adv_email"]."</a>":"")."<br/>".
              (empty($adv["adv_url"]) ? "" : $adv["adv_url"]."<br/>");
            }
          }
        }
      }

      $sl_adv_infos = "";
      if (!empty($sl_linked_pdt_infos) || !empty($sl_same_cat_infos)) {
        $sl_adv_infos .= "<br/><br/>Vous avez souhaité de plus prévenir d'autres prestataires suceptibles de répondre à votre besoin.<br/>";
        if (!empty($sl_linked_pdt_infos))
          $sl_adv_infos .= "Voici leurs coordonnées et le(s) produit suceptible(s) de répondre à votre demande:<br/>".$sl_linked_pdt_infos;
        else
          $sl_adv_infos .= "Voici leurs coordonnées :<br/>".$sl_same_cat_infos;
      }

      ////////////////////////////////////////////////////////////////////////////////
      // MAILS
      ////////////////////////////////////////////////////////////////////////////////
      // Customer and Main Product Data
      $CustomerMailData = array(
        "CUSTOMER_REQUESTTYPE" => "Demande de devis gratuit",
        "CUSTOMER_LASTNAME" => $fields["nom"],
        "CUSTOMER_FIRSTNAME" => $fields["prenom"],
        "CUSTOMER_JOB" => $fields["fonction"],
        "CUSTOMER_PHONE" => $fields["tel"],
        "CUSTOMER_FAX" => $fields["fax"],
        "CUSTOMER_FAX2" => $fields["fax"],
        "CUSTOMER_EMAIL" => $fields["email"],
        "CUSTOMER_EMAIL2" => $fields["email"],
        "CUSTOMER_COMPANY_NAME" => $fields["societe"],
        "CUSTOMER_COMPANY_WORKFORCE" => $fields["salaries"],
        "CUSTOMER_COMPANY_URL" => $fields["url"],
        "CUSTOMER_COMPANY_URL2" => $fields["url"],
        "CUSTOMER_COMPANY_SECTOR" => to_entities($fields["secteur"]),
        "CUSTOMER_COMPANY_NAF" => $fields["naf"],
        "CUSTOMER_COMPANY_SIREN" => $fields["siret"],
        "CUSTOMER_COMPANY_ADDRESS" => $fields["adresse"],
        "CUSTOMER_COMPANY_COMPLEMENT" => $fields["cadresse"],
        "CUSTOMER_COMPANY_PC" => to_entities($fields["cp"]),
        "CUSTOMER_COMPANY_CITY" => to_entities($fields["ville"]),
        "CUSTOMER_COMPANY_COUNTRY" => to_entities($fields["pays"]),
        "CUSTOMER_CUSTOM_FIELDS" => $cfvs,
        "SITE_MAIN_URL" => URL
        //"Customer-Company-DeleveryNote" => to_entities($fields["infos_sup"]),
      );

      $MainProductMailData = array(
        "MAINPRODUCT_ID" => $pdt["infos"]["id"],
        "MAINPRODUCT_CAT_ID" => $pdt["infos"]["cat_id"],
        "MAINPRODUCT_NAME" => $pdt["infos"]["name"],
        "MAINPRODUCT_REFNAME" => $pdt["infos"]["ref_name"],
        "MAINPRODUCT_FAMILYID" => $pdt["infos"]["cat_id"],
        "MAINPRODUCT_FASTDESC" => $pdt["infos"]["fastdesc"],
        "MAINPRODUCT_DESCRIPTION" => $pdt["infos"]["descc"],
        "MAINPRODUCT_DETAILEDDESCRIPTION" => empty($pdt["infos"]["descd"]) ? "" : $pdt["infos"]["descd"],
        "MAINPRODUCT_URL" => URL."produits/".$pdt["infos"]["cat_id"]."-".$pdt["infos"]["id"]."-".$pdt["infos"]["ref_name"].".html",
        "MAINPRODUCT_IMAGE" => is_file(PRODUCTS_IMAGE_INC."thumb_big/".$pdt["infos"]["id"]."-1".".jpg") ? PRODUCTS_IMAGE_URL."thumb_big/".$pdt["infos"]["id"]."-1".".jpg" : PRODUCTS_IMAGE_URL."no-pic-thumb_big.gif",
        "MAINPRODUCT_PRICEDATA" => GetPriceHtml($pdt["infos"]),
        "MAINPRODUCT_ADVERTISER_ID" => $pdt["infos"]["adv_id"],
        "MAINPRODUCT_ADVERTISER_NAME" => $pdt["infos"]["adv_name"],
        "MAINPRODUCT_ADVERTISER_CATEGORY" => $pdt["infos"]["adv_cat"],
        "MAINPRODUCT_ADVERTISER_ADDRESS" => $pdt["infos"]["adv_address1"],
        "MAINPRODUCT_ADVERTISER_PC" => $pdt["infos"]["adv_pc"],
        "MAINPRODUCT_ADVERTISER_CITY" => $pdt["infos"]["adv_city"],
        "MAINPRODUCT_ADVERTISER_COUNTRY" => $pdt["infos"]["adv_country"],
        "MAINPRODUCT_ADVERTISER_PHONE" => $pdt["infos"]["adv_tel1"],
        "MAINPRODUCT_ADVERTISER_FAX" => $pdt["infos"]["adv_fax1"],
        "MAINPRODUCT_ADVERTISER_COMPLEMENT" => empty($pdt["infos"]["adv_address2"]) ? "" : $pdt["infos"]["adv_address2"],
        "MAINPRODUCT_ADVERTISER_CONTACT" => empty($pdt["infos"]["adv_contact"]) ? "" : $pdt["infos"]["adv_contact"],
        "MAINPRODUCT_ADVERTISER_EMAIL" => empty($pdt["infos"]["adv_email"]) ? "" :  $pdt["infos"]["adv_email"],
        "MAINPRODUCT_ADVERTISER_URL" => empty($pdt["infos"]["adv_url"]) ? "" :  $pdt["infos"]["adv_url"],
        "MAINPRODUCT_ADVERTISER_WEBPASS" => $pdt["infos"]["adv_webpass"],
        "MAINPRODUCT_ORIGINAL_ADVERTISER_ID" => $pdt["infos"]["oadv_id"],
        "MAINPRODUCT_ORIGINAL_ADVERTISER_NAME" => $pdt["infos"]["oadv_name"],
        "MAINPRODUCT_PRECISIONS" => $fields["precisions"],
        "MAINPRODUCT_GENERATEDCONTACTID" => $lead1->id,
        "MAINPRODUCT_ADVERTISER_SHOWINFOSONLINE" => (int)$pdt["infos"]["adv_show_infos_online"],
        "ML_ADV_INFOS" => $ml_adv_infos,
        "SL_ADV_INFOS" => $sl_adv_infos,
        "BOUSER_NAME" => $pdt["infos"]["adv_salesman_name"],
        "BOUSER_EMAIL" => $pdt["infos"]["adv_salesman_email"],
        "BOUSER_PHONE" => $pdt["infos"]["adv_salesman_phone"]
      );

      // Recap Com Mail
      $recap_com = "";

      // Depending on the contact email
      switch ($pdt["infos"]["adv_email"]) {
        case "rsamson@centralweb.fr" : // CENTRAL WEB
          $recap_com .= "L'utilisateur <b>n'a pas été prévenu</b> par email car l'annonceur de ce produit est un partenaire CENTRAL WEB.<br/>\n";
          $recap_com .= "Une requête POST HTTP <b>a été effectuée</b> sur le serveur <b>CENTRAL WEB</b>.<br/>\n";
          if (!MakeCWSocketConnection($fields, $pdt["infos"]["name"], 3, $pdt["infos"]["adv_name"], $lead1->id))
            $recap_com .= "<b>Toutefois la requête semble ne pas s'être déroulée correctement.</b><br/>\n";
          break;

        default : // By default, mail the customer
          $ml_adv_infos_mail = "";
		  
          if (!empty($ml_adv_infos)) {
            $ml_adv_infos_mail .= $ml_adv_infos;
          } else { 

			$ml_adv_infos_mail .= 'Votre demande est archivée dans votre <a href="'.COMPTE_URL.'login.html">compte client gratuit</a>
									<br /><br />

									Vous pourrez aussi depuis ce compte communiquer directement avec nos partenaires.
									<br /><br />
									
									Votre demande a été transmise au fournisseur suivant dont voici les coordonnées :
									<br /><br />
								';
			  
            $ml_adv_infos_mail .= "<strong>Désolé, ce partenaire ne peut répondre à votre demande.</strong><br/>
              <br/>
              Les raisons peuvent être les suivantes :<br/><br/>
              <ul>
                <li>Ne fait plus ce produit ne l'a plus en stock ou en catalogue</li>
                <li>Ne travaille qu'avec les professionnels</li>
                <li>Ne couvre pas votre secteur géographique</li>
              </ul>";
          }

	  
        // adding tracking to every http links (products links)
          $ml_adv_infos_mail = array("ML_ADV_INFOS" => $ml_adv_infos_mail);
          $tracking = "?utm_source=email&utm_medium=email&utm_campaign=mail-ar-lead-annonceur";
          $sl_adv_infos_mail = array("SL_ADV_INFOS" => preg_replace("/<a([^>]+)href=\"(http[^\"]+)\"([^>]*)>/", "<a$1href=\"$2".$tracking."\"$3>", $sl_adv_infos));
          
          $mailContent = array(
            'email' => $fields["email"],
            'subject' => "Votre Demande de devis gratuit pour le produit ".$pdt["infos"]["name"],
            'headers' => "From: Service client Techni-Contact <web@techni-contact.com>\n",
            'template' => $pdt["infos"]["adv_cat"] == __ADV_CAT_SUPPLIER__ ? "customer-lead-fournisseur" : "customer-lead-annonceur",
            'data' => array_merge($MainProductMailData, $CustomerMailData, $ml_adv_infos_mail, $sl_adv_infos_mail)
          );
          $mail = new Email($mailContent);
         /* $mail->Build(
            "Votre Demande de devis gratuit pour le produit ".$pdt["infos"]["name"],
            "",
            $pdt["infos"]["adv_cat"] == __ADV_CAT_SUPPLIER__ ? "lead-fournisseur" : "lead-annonceur",
            "From: Service client Techni-Contact <web@techni-contact.com>\n",
            array_merge($MainProductMailData, $CustomerMailData, $ml_adv_infos_mail, $sl_adv_infos_mail),
            "user"
          );*/
          $mail->send();
          //$mail->Save();

          $recap_com .= "Cet utilisateur <b>a été prévenu</b> par email.<br/>\n";

          // Mail the advertiser if he has at least one valid email and he's not a supplier
          if ($pdt["infos"]["adv_cat"] != __ADV_CAT_SUPPLIER__) {
            if (empty($pdt["infos"]["adv_mails_list"])) {
              $recap_com .= "<span style=\"color: red\">L'annonceur <b>n'a pas été prévenu</b> car il n'a pas d'adresse email connue.</span>";
            }
            else {
              if ($pdt["infos"]["adv_cat"] == __ADV_CAT_BLOCKED__)
                $recap_com .= "Cet annonceur est actuellement <b>bloqué</b> et a été prévenu aux adresses suivantes :";
              elseif($pdt["infos"]["adv_cat"] == __ADV_CAT_LITIGATION__)
                $recap_com .= "Cet annonceur est actuellement <b>litige de paiement</b> et a été prévenu aux adresses suivantes :";
              else
                $recap_com .= "L'annonceur <b>a été prévenu</b> par email <b>avec lien sécurisé</b> aux adresses suivantes :";

			  //Modification on 03/12/2014 Cancel call this file and call the same file on Front	
              //$template = $pdt["infos"]["adv_cat"] == __ADV_CAT_LITIGATION__ ? 'customer-lead_annonceur-alerteLitigation' : 'customer-lead_annonceur-alerte';
			  
			  $template = $pdt["infos"]["adv_cat"] == __ADV_CAT_LITIGATION__ ? 'customer-lead_annonceur_litige-alerte' : 'customer-lead_annonceur-alerte';
			  
//echo(' template '.$template.' **** pdtinfos '.$pdt["infos"]["adv_cat"].' **** adv_cat '.__ADV_CAT_LITIGATION__.' <br /><br />');
//print_r($pdt["infos"]);			  
			/*******************************************************************/
			/*******************************************************************/			
			//Changes Start "01/12/2014"
			//Check if the advertiser has from_web "1" or "0" to send him
			//The complete informations of this lead in the mail  
			
			//Get the advertiser from_web
			$query_adv_get_web	= "SELECT
										a.from_web
									FROM
										contacts c INNER JOIN advertisers a ON a.id=c.idAdvertiser
									WHERE
										c.id=".$lead1->id."";
			$res_adv_get_web = $db->query($query_adv_get_web, __FILE__, __LINE__);
			$content_adv_get_web = $db->fetchAssoc($res_adv_get_web);

//echo('***1177 '.$content_adv_get_web['from_web'].' *timestamp: '.$lead1->timestamp.' *LeadID '.$lead1->id.' ');
	
			
			$template_temp1	= $template;
			
			if(strcmp($content_adv_get_web['from_web'],'0')==0 && strcmp($template,'customer-lead_annonceur-alerte')==0){
					//from_web is decoched so we have to send a personnalized mail
					//That contain all the informations of the leed
					
//echo('<br /> In if '.$content_adv_get_web['from_web'].' * '.$template.'<br />');
				
					if(!empty($MainProductMailData['MAINPRODUCT_NAME'])){
						$temp_infos_name	= '<br /><strong>Nom du produit : </strong> '.$MainProductMailData['MAINPRODUCT_NAME'];
					}else if(!empty($pdt["cat_name"])){
						$temp_infos_name	= '<br /><strong>Famille demand&eacute;e : </strong> '.$pdt["cat_name"];
					}
					
					$template_temp1	= 'customer-lead_annonceur-alerte_all_informations';
					$array_var =  array(
									"SITE_MAIN_URL" => URL,
									"EXTRANET_URL" => EXTRANET_URL,
									"ADVERTISER_WEBPASS" => $pdt["infos"]["adv_webpass"],
									"GENERATEDCONTACTID" => $lead1->id,
									
									"EXTRAINFO_COMPANY_NAME" => $CustomerMailData["CUSTOMER_COMPANY_NAME"],
									"EXTRAINFO_COMPANY_ADDRESS" => $CustomerMailData["CUSTOMER_COMPANY_ADDRESS"],
									"EXTRAINFO_COMPANY_PC" => $CustomerMailData["CUSTOMER_COMPANY_PC"],
									"EXTRAINFO_COMPANY_CITY" => $CustomerMailData["CUSTOMER_COMPANY_CITY"],
									"EXTRAINFO_COMPANY_COUNTRY" => $CustomerMailData["CUSTOMER_COMPANY_COUNTRY"],
									
									"EXTRAINFO_COMPANY_SECTOR" => $CustomerMailData["CUSTOMER_COMPANY_SECTOR"],
									"EXTRAINFO_COMPANY_NAF" => $CustomerMailData["CUSTOMER_COMPANY_NAF"],
									"EXTRAINFO_COMPANY_SIREN" => $CustomerMailData["CUSTOMER_COMPANY_SIREN"],
									
									"EXTRAINFO_LASTNAME" => $CustomerMailData["CUSTOMER_LASTNAME"],
									"EXTRAINFO_FIRSTNAME" => $CustomerMailData["CUSTOMER_FIRSTNAME"],
									"EXTRAINFO_EMAIL" => $CustomerMailData["CUSTOMER_EMAIL"],
									"EXTRAINFO_PHONE" => $CustomerMailData["CUSTOMER_PHONE"],
									"EXTRAINFO_JOB" => $CustomerMailData["CUSTOMER_JOB"],
									
									"EXTRAINFO_PRODUCT_NAME" => $temp_infos_name
								  );
				
			}else if(strcmp($content_adv_get_web['from_web'],'1')==0 && strcmp($template,'customer-lead_annonceur-alerte')==0){
			
//echo('<br /> In else '.$content_adv_get_web['from_web'].' * '.$template.'<br />');
			
				//Do not change anything the data is already ready before the condition
				$sql_societe  = "SELECT societe FROM contacts WHERE id='".$lead1->id."'  ";
				$req_societe  =  mysql_query($sql_societe);
				$data_societe =  mysql_fetch_object($req_societe);
				
				
				$template_temp1	= 'customer-lead_annonceur-alerte';
				
				$array_var =  array(
							  "SITE_MAIN_URL" => URL,
							  "EXTRANET_URL" => EXTRANET_URL,
							  "ADVERTISER_WEBPASS" => $pdt["infos"]["adv_webpass"],
							  "GENERATEDCONTACTID" => $lead1->id,
							  "SOCIETE_NAME" => $data_societe->societe
							);
				
			
			}//end else if test on template  
			  
			  
			//That concerns "customer-lead_annonceur_litige-alerte" 
			//Because we changed the call to the same file called on the Front
			//So we have to put the same variables
			if(strcmp($template,'customer-lead_annonceur_litige-alerte')==0){
//echo('<br /> In IF '.$content_adv_get_web['from_web'].' * '.$template.' advertiser_name '.$pdt["infos"]["adv_name"].'<br />');	
				
				
				$template_temp1	= 'customer-lead_annonceur_litige-alerte';
				$array_var =  array(
							  "SITE_MAIN_URL" => URL,
							  "EXTRANET_URL" => EXTRANET_URL,
							  "ADVERTISER_NAME" => $pdt["infos"]["adv_name"]
							);

			}
			
//echo(' **template '.$template_temp1.'<br /><br /> ########');
			
				$mailContent = array(
				  'email' => '',
				  'subject' => "Demande de devis n°".$lead1->id." sur www.techni-contact.com",
				  'headers' => "From: Service client Techni-Contact <lead@techni-contact.com>\nReply-To: Service client Techni-Contact <lead@techni-contact.com>\r\n",
				  'template' => $template_temp1,
				  'data' => $array_var
				);
				$mail = new Email($mailContent);
				
				
			//Changes End "01/12/2014"
            /*******************************************************************/
			/*******************************************************************/
			
              
              /*$mail->Build(
                "Demande de devis sur www.techni-contact.com",
                "",
                $template,
                "From: Service client Techni-Contact <web@techni-contact.com>\nReply-To: Service client Techni-Contact <web@techni-contact.com>\r\n",
                array(
                  "SITE_MAIN_URL" => URL,
                  "EXTRANET_URL" => EXTRANET_URL,
                  "ADVERTISER_WEBPASS" => $pdt["infos"]["adv_webpass"],
                  "GENERATEDCONTACTID" => $lead1->id
                ),
                "partner"
              );*/
              foreach($pdt["infos"]["adv_mails_list"] as $contact_mail) {
                $recap_com .= "<br/> &nbsp; - ".$contact_mail."\n";
                $mail->email = $contact_mail;
                $mail->send();
                //$mail->Save();
              }			  
            }
          }
          else
            $recap_com .= "Cet annonceur étant un fournisseur Techni-Contact <b>il n'a pas été contacté directement</b>. Vous pouvez lui transmettre les éléments de la demande afin d'obtenir les informations demandées puis répondre au client.";
      }

      $recap_com .= "<br/><br/>\n";

      // If there is a campaign
      if (!empty($campaignID))
        $recap_com .= "ID de la campagne source : <b>".$campaignID."</b><br/>\n<br/>\n";

      if ($sl_advCount) {
        if ($lplCount) { // linked product
          if ($sl_advCount == 1)
            $recap_com .= "Cette demande a généré un lead secondaire via une liaison produit auprès de l'annonceur suivant :<br/>\n";
          else // > 1
            $recap_com .= "Cette demande a généré ".$sl_advCount." leads secondaires via ".$lplCount." liaisons produits auprès des annonceurs suivants :<br/>\n";

          $recap_com .= "<br/><br/>\n";
          foreach ($sl_advList as $k => $adv) {
            $lead2 = $lead2list[$k];
            $recap_com .= "<b><a href=\"".ADMIN_URL."advertisers/edit.php?id=".$adv["adv_id"]."\">".$adv["adv_name"]."</a></b><br/>\n";
            $recap_com .= "Produit : ";
            $recap_com .= "<a href=\"".ADMIN_URL."products/edit.php?id=".$adv["id"]."\">".$adv["name"]."</a>";
            $recap_com .= "<br/>\n";
            if ($adv["adv_email"] == "rsamson@centralweb.fr") {
              $recap_com .= "- Cet annonceur <b>n'a pas a été prévenu</b> par email car il est un partenaire CENTRAL WEB.<br/>\n";
              $recap_com .= "- Une requête POST HTTP <b>a été effectuée</b> sur le serveur <b>CENTRAL WEB</b>.<br/>\n";
              if (!MakeCWSocketConnection($fields, $adv["cat_name"], 3, $adv["adv_name"], $lead2->id))
                $recap_com .= "--> <b>Toutefois la requête semble ne pas s'être déroulée correctement.</b><br/>\n";
            }
            else {
              if (empty($adv["adv_mails_list"])) {
                $recap_com .= "- <span style=\"color: red\">Cet annonceur <b>n'a pas été prévenu</b> car il n'a pas d'adresse email connue.</span>";
              }
              else {
                $recap_com .= "- Cet annonceur <b>a été prévenu</b> par email aux adresses suivantes :\n";
                foreach($adv["adv_mails_list"] as $contact_mail)
                  $recap_com .= "<br/> &nbsp; &nbsp; - ".$contact_mail."\n";
				  
				  
				/*******************************************************************/
				/*******************************************************************/			
				//Changes Start "01/12/2014"
				//Check if the advertiser has from_web "1" or "0" to send him
				//The complete informations of this lead in the mail  

//print_r($adv);			
				//Check every advertiser's category 
				if(strcmp($adv["adv_cat"],__ADV_CAT_LITIGATION__)!=0){
					$template = 'customer-lead_annonceur-alerte';
				
				}else if(strcmp($adv["adv_cat"],__ADV_CAT_LITIGATION__)==0){
					$template = 'customer-lead_annonceur_litige-alerte';
					$array_var = array(
									"SITE_MAIN_URL" => URL,
									"EXTRANET_URL" => EXTRANET_URL,
									"ADVERTISER_NAME" => $adv["adv_name"]
								);
				}
			
				//Get the advertiser from_web
				$query_adv_get_web	= "SELECT
											a.from_web
										FROM
											contacts c INNER JOIN advertisers a ON a.id=c.idAdvertiser
										WHERE
											c.create_time=".$lead1->timestamp."
										AND
											c.parent=".$lead1->id."
										AND
											c.idAdvertiser=".$adv['adv_id']."";
											
				$res_adv_get_web = $db->query($query_adv_get_web, __FILE__, __LINE__);
				$content_adv_get_web = $db->fetchAssoc($res_adv_get_web);

//echo('***1343 '.$content_adv_get_web['from_web'].' *timestamp: '.$lead1->timestamp.' *Lead1->id '.$lead1->id.' *Lead2->id '.$lead2->id.' *idAdvertiser '.$adv['adv_id'].' ');
				
				$template_temp2	= $template;
				
				if(strcmp($content_adv_get_web['from_web'],'0')==0 && strcmp($template,'customer-lead_annonceur-alerte')==0){
					//from_web is decoched so we have to send a personnalized mail
					//That contain all the informations of the leed

//echo('<br /> In if '.$content_adv_get_web['from_web'].' * '.$template.'<br />');
					
					if(!empty($MainProductMailData['MAINPRODUCT_NAME'])){
						$temp_infos_name	= '<br /><strong>Nom du produit : </strong> '.$MainProductMailData['MAINPRODUCT_NAME'];
					}else if(!empty($pdt["cat_name"])){
						$temp_infos_name	= '<br /><strong>Famille demand&eacute;e : </strong> '.$pdt["cat_name"];
					}
					
					$template_temp2	= 'customer-lead_annonceur-alerte_all_informations';
					$array_var =  array(
									"SITE_MAIN_URL" => URL,
									"EXTRANET_URL" => EXTRANET_URL,
									"ADVERTISER_WEBPASS" => $adv["adv_webpass"],
									"GENERATEDCONTACTID" => $lead2->id,
									
									"EXTRAINFO_COMPANY_NAME" => $CustomerMailData["CUSTOMER_COMPANY_NAME"],
									"EXTRAINFO_COMPANY_ADDRESS" => $CustomerMailData["CUSTOMER_COMPANY_ADDRESS"],
									"EXTRAINFO_COMPANY_PC" => $CustomerMailData["CUSTOMER_COMPANY_PC"],
									"EXTRAINFO_COMPANY_CITY" => $CustomerMailData["CUSTOMER_COMPANY_CITY"],
									"EXTRAINFO_COMPANY_COUNTRY" => $CustomerMailData["CUSTOMER_COMPANY_COUNTRY"],
									
									"EXTRAINFO_COMPANY_SECTOR" => $CustomerMailData["CUSTOMER_COMPANY_SECTOR"],
									"EXTRAINFO_COMPANY_NAF" => $CustomerMailData["CUSTOMER_COMPANY_NAF"],
									"EXTRAINFO_COMPANY_SIREN" => $CustomerMailData["CUSTOMER_COMPANY_SIREN"],
									
									"EXTRAINFO_LASTNAME" => $CustomerMailData["CUSTOMER_LASTNAME"],
									"EXTRAINFO_FIRSTNAME" => $CustomerMailData["CUSTOMER_FIRSTNAME"],
									"EXTRAINFO_EMAIL" => $CustomerMailData["CUSTOMER_EMAIL"],
									"EXTRAINFO_PHONE" => $CustomerMailData["CUSTOMER_PHONE"],
									"EXTRAINFO_JOB" => $CustomerMailData["CUSTOMER_JOB"],
									
									"EXTRAINFO_PRODUCT_NAME" => $temp_infos_name
								  );
					
				}else if(strcmp($content_adv_get_web['from_web'],'1')==0 && strcmp($template,'customer-lead_annonceur-alerte')==0){
//echo('<br /> In else '.$content_adv_get_web['from_web'].' * '.$template.'<br />');				
					//Do not change anything the data is already ready before the condition
					$sql_societe  = "SELECT societe FROM contacts WHERE id='".$lead2->id."'  ";
					$req_societe  =  mysql_query($sql_societe);
					$data_societe =  mysql_fetch_object($req_societe);
					
					$template_temp2	= 'customer-lead_annonceur-alerte';
					$array_var =  array(
								  "SITE_MAIN_URL" => URL,
								  "EXTRANET_URL" => EXTRANET_URL,
								  "ADVERTISER_WEBPASS" => $adv["adv_webpass"],
								  "GENERATEDCONTACTID" => $lead2->id,
								  "SOCIETE_NAME" => $data_societe->societe
								  );
								  
				}//end else if test on template
				
//echo(' **template '.$template_temp2.'<br /><br /> ########');
				
				$mailContent = array(
                    'email' => '',
                    'subject' => "Demande de devis n°".$lead2->id." sur www.techni-contact.com",
                    'headers' => "From: Service client Techni-Contact <lead@techni-contact.com>\nReply-To: Service client Techni-Contact <lead@techni-contact.com>\r\n",
                    'template' => $template_temp2,
                    'data' => $array_var
					);
                $mail = new Email($mailContent);
				
				//Changes End "01/12/2014"
				/*******************************************************************/
				/*******************************************************************/
				
				
                
                /*$mail->Build(
                  "Demande de devis sur www.techni-contact.com",
                  "",
                  "lead-annonceur-alerte",
                  "From: Service client Techni-Contact <web@techni-contact.com>\nReply-To: Service client Techni-Contact <web@techni-contact.com>\r\n",
                  array(
                    "SITE_MAIN_URL" => URL,
                    "EXTRANET_URL" => EXTRANET_URL,
                    "ADVERTISER_WEBPASS" => $adv["adv_webpass"],
                    "GENERATEDCONTACTID" => $lead2->id
                  ),
                  "partner"
                );*/
                foreach($adv["mails_list"] as $contact_mail) {
                  $mail->email = $contact_mail;
                  $mail->send();
                  //$mail->Save();
                }
              }
            }
            $recap_com .= "<br/><br/>\n";
          }
        }
        else {
          // Envoi auprès des annonceurs qu'un client a demandé des infos sur un des produits de la gamme d'un annonceur lié
          if ($sl_advCount == 1)
            $recap_com .= "Cette demande a généré un lead secondaire auprès de l'annonceur suivant :";
          else // > 1
            $recap_com .= "Cette demande a généré ".$sl_advCount." leads secondaires auprès des annonceurs suivants :";

          $recap_com .= "<br/><br/>\n";
          foreach ($sl_advList as $k => $adv) {
            $lead2 = $lead2list[$k];
            $recap_com .= "<b>".$adv["adv_name"]."</b><br/>\n";
            if ($adv["adv_email"] == "rsamson@centralweb.fr") {
              $recap_com .= "- Cet annonceur <b>n'a pas a été prévenu</b> par email car il est un partenaire CENTRAL WEB.<br/>\n";
              $recap_com .= "- Une requête POST HTTP <b>a été effectuée</b> sur le serveur <b>CENTRAL WEB</b>.<br/>\n";
              if (!MakeCWSocketConnection($fields, $adv["cat_name"], 3, $adv["adv_name"], $lead2->id))
                $recap_com .= "--> <b>Toutefois la requête semble ne pas s'être déroulée correctement.</b><br/>\n";
            }
            else {
              if (empty($adv["adv_mails_list"])) {
                $recap_com .= "- <span style=\"color: red\">Cet annonceur <b>n'a pas été prévenu</b> car il n'a pas d'adresse email connue.</span>";
              }
              else {
                $recap_com .= "- Cet annonceur <b>a été prévenu</b> par email aux adresses suivantes :\n";
                foreach($adv["adv_mails_list"] as $contact_mail)
                  $recap_com .= "<br/> &nbsp; &nbsp; - ".$contact_mail."\n";
				  
				  
				/*******************************************************************/
				/*******************************************************************/			
				//Changes Start "01/12/2014"
				//Check if the advertiser has from_web "1" or "0" to send him
				//The complete informations of this lead in the mail  
//echo('**** 1495<br />');
		
	
				//Check every advertiser's category 
				if(strcmp($adv["adv_cat"],__ADV_CAT_LITIGATION__)!=0){
					$template = 'customer-lead_annonceur-alerte';
				
				}else if(strcmp($adv["adv_cat"],__ADV_CAT_LITIGATION__)==0){
					$template = 'customer-lead_annonceur_litige-alerte';
					$array_var = array(
									"SITE_MAIN_URL" => URL,
									"EXTRANET_URL" => EXTRANET_URL,
									"ADVERTISER_NAME" => $adv["adv_name"]
								);
				}
				
				//Get the advertiser from_web
				$query_adv_get_web	= "SELECT
											a.from_web
										FROM
											contacts c INNER JOIN advertisers a ON a.id=c.idAdvertiser
										WHERE
											c.create_time=".$lead1->timestamp."
										AND
											c.parent=".$lead1->id."
										AND
											c.idAdvertiser=".$adv['adv_id']."";


											
				$res_adv_get_web = $db->query($query_adv_get_web, __FILE__, __LINE__);
				$content_adv_get_web = $db->fetchAssoc($res_adv_get_web);

//echo('***1489 '.$content_adv_get_web['from_web'].' *timestamp: '.$lead1->timestamp.' *Lead1->id '.$lead1->id.' *Lead2->id '.$lead2->id.' *idAdvertiser '.$adv['adv_id'].' *advname '.$adv["adv_name"].'');
				
				$template_temp3	= $template;

				if(strcmp($content_adv_get_web['from_web'],'0')==0 && strcmp($template,'customer-lead_annonceur-alerte')==0){
//echo('<br /> In if '.$content_adv_get_web['from_web'].' * '.$template.'<br />');				
						//from_web is decoched so we have to send a personnalized mail
						//That contain all the informations of the leed
					
					if(!empty($MainProductMailData['MAINPRODUCT_NAME'])){
						$temp_infos_name	= '<br /><strong>Nom du produit : </strong> '.$MainProductMailData['MAINPRODUCT_NAME'];
					}else if(!empty($pdt["cat_name"])){
						$temp_infos_name	= '<br /><strong>Famille demand&eacute;e : </strong> '.$pdt["cat_name"];
					}
					
					$template_temp3	= 'customer-lead_annonceur-alerte_all_informations';
					$array_var =  array(
									"SITE_MAIN_URL" => URL,
									"EXTRANET_URL" => EXTRANET_URL,
									"ADVERTISER_WEBPASS" => $adv["adv_webpass"],
									"GENERATEDCONTACTID" => $lead2->id,
									
									"EXTRAINFO_COMPANY_NAME" => $CustomerMailData["CUSTOMER_COMPANY_NAME"],
									"EXTRAINFO_COMPANY_ADDRESS" => $CustomerMailData["CUSTOMER_COMPANY_ADDRESS"],
									"EXTRAINFO_COMPANY_PC" => $CustomerMailData["CUSTOMER_COMPANY_PC"],
									"EXTRAINFO_COMPANY_CITY" => $CustomerMailData["CUSTOMER_COMPANY_CITY"],
									"EXTRAINFO_COMPANY_COUNTRY" => $CustomerMailData["CUSTOMER_COMPANY_COUNTRY"],
									
									"EXTRAINFO_COMPANY_SECTOR" => $CustomerMailData["CUSTOMER_COMPANY_SECTOR"],
									"EXTRAINFO_COMPANY_NAF" => $CustomerMailData["CUSTOMER_COMPANY_NAF"],
									"EXTRAINFO_COMPANY_SIREN" => $CustomerMailData["CUSTOMER_COMPANY_SIREN"],
									
									"EXTRAINFO_LASTNAME" => $CustomerMailData["CUSTOMER_LASTNAME"],
									"EXTRAINFO_FIRSTNAME" => $CustomerMailData["CUSTOMER_FIRSTNAME"],
									"EXTRAINFO_EMAIL" => $CustomerMailData["CUSTOMER_EMAIL"],
									"EXTRAINFO_PHONE" => $CustomerMailData["CUSTOMER_PHONE"],
									"EXTRAINFO_JOB" => $CustomerMailData["CUSTOMER_JOB"],
									
									"EXTRAINFO_PRODUCT_NAME" => $temp_infos_name
								  );
					
				}else if(strcmp($content_adv_get_web['from_web'],'1')==0 && strcmp($template,'customer-lead_annonceur-alerte')==0){
				
//echo('<br /> In else '.$content_adv_get_web['from_web'].' * '.$template.'<br />');
					$sql_societe  = "SELECT societe FROM contacts WHERE id='".$lead2->id."'  ";
					$req_societe  =  mysql_query($sql_societe);
					$data_societe =  mysql_fetch_object($req_societe);
					
					$template_temp3	= 'customer-lead_annonceur-alerte';
					$array_var 	=  array(
										"SITE_MAIN_URL" => URL,
										"EXTRANET_URL" => EXTRANET_URL,
										"ADVERTISER_WEBPASS" => $adv["adv_webpass"],
										"GENERATEDCONTACTID" => $lead2->id,
										"SOCIETE_NAME" => $data_societe->societe
								  );
				
				}//end else if test on template
				
//echo(' **template '.$template_temp3.'<br /><br /> ########');
				
                $mailContent = array(
                    'email' => '',
                    'subject' => "Demande de devis n°".$lead2->id." sur www.techni-contact.com",
                    'headers' => "From: Service client Techni-Contact <lead@techni-contact.com>\nReply-To: Service client Techni-Contact <lead@techni-contact.com>\r\n",
                    'template' => $template_temp3,
                    'data' => $array_var
                );
                $mail = new Email($mailContent);
				
				//Changes End "01/12/2014"
				/*******************************************************************/
				/*******************************************************************/
				
               /* $mail->Build(
                  "Demande de devis sur www.techni-contact.com",
                  "",
                  "lead-annonceur-alerte",
                  "From: Service client Techni-Contact <web@techni-contact.com>\nReply-To: Service client Techni-Contact <web@techni-contact.com>\r\n",
                  array(
                    "SITE_MAIN_URL" => URL,
                    "EXTRANET_URL" => EXTRANET_URL,
                    "ADVERTISER_WEBPASS" => $adv["adv_webpass"],
                    "GENERATEDCONTACTID" => $lead2->id
                  ),
                  "partner"
                );*/
                foreach($adv["adv_mails_list"] as $contact_mail) {
                  $mail->email = $contact_mail;
                  $mail->send();
                  //$mail->Save();
                }
              }
            }
            $recap_com .= "<br/><br/>\n";
          }
        }
      }

      ////////////////////////////////////////////////////////////////////////////////
      // PRODUCT COMMENT - Add the comment in product's comments
      ////////////////////////////////////////////////////////////////////////////////
      do {
        $idComment = mt_rand(1, 999999999);
        $res = $db->query("SELECT id FROM products_comments WHERE id = ".$idComment, __FILE__, __LINE__);
      }
      while ($db->numrows($res, __FILE__, __LINE__) >= 1);
      $query = "insert into products_comments (";	$query2 = "values (";
      $query .= "id, ";								$query2 .= $idComment.", ";
      $query .= "productID, ";						$query2 .= $pdt["infos"]["id"].", ";
      $query .= "contactID, ";						$query2 .= $lead1->id.", ";
      $query .= "timestamp, ";						$query2 .= $lead1->timestamp.", ";
      $query .= "text, ";							$query2 .= "'".$db->escape($fields["precisions"])."', ";
      $query .= "`show`) ";							$query2 .= (empty($fields["precisions"]) ? 0 : 1).") ";
      $query .= $query2;

      if (!($db->query($query, __FILE__, __LINE__, false)) || ($db->affected(__FILE__, __LINE__) != 1))
        print "Fatal Error while saving the comment";

      ////////////////////////////////////////////////////////////////////////////////
      // ACCOUNT CREATION AND UPDATE
      ////////////////////////////////////////////////////////////////////////////////
      // automatically create an account if email does not already exists in the db
      if (!($cuser_id = CustomerUser::getCustomerIdFromLogin($fields["email"], $db))) {
        $cuser = new CustomerUser($db);
        $accinfos = array(
          "coord_livraison" => 0,
          "login" => $fields["email"],
          "titre" => 1,
          "nom" => $fields["nom"],
          "prenom" => $fields["prenom"],
          "fonction" => $fields["fonction"],
          "societe" => $fields["societe"],
          "nb_salarie" => $fields["salaries"],
          "secteur_activite" => $fields["secteur"],
          "secteur_qualifie" => $fields["qualification"],
          "code_naf" => $fields["naf"],
          "num_siret" => $fields["siret"],
          "adresse" => $fields["adresse"],
          "complement" => $fields["cadresse"],
          "ville" => $fields["ville"],
          "cp" => $fields["cp"],
          "pays" => $fields["pays"],
          "infos_sup" => "",
          "tel1" => $fields["tel"],
          "fax1" => $fields["fax"],
          "titre_l" => 1,
          "nom_l" => $fields["nom"],
          "prenom_l" => $fields["prenom"],
          "societe_l" => $fields["societe"],
          "adresse_l" => $fields["adresse"],
          "complement_l" => $fields["cadresse"],
          "ville_l" => $fields["ville"],
          "cp_l" => $fields["cp"],
          "pays_l" => $fields["pays"],
          "infos_sup_l" => "",
          "tel2" => $fields["tel"],
          "fax2" => $fields["fax"],
          "url" => $fields["url"],
          "actif" => 1,
          "email" => $fields["email"]);
        $cuser->create();
        $cuser->setCoordFromArray($accinfos);
        $cuser->code = "9".substr(strtoupper(Utils::toASCII($cuser->societe)),0,4).substr($cuser->id,0, 6);
        $pass = $cuser->generatePassword();
        $cuser->save(time()-120);

        // sending mail
        $mailContent = array(
            'email' => $cuser->email,
            'subject' => "Création de votre compte gratuit",
                            'headers' => "From: Techni-Contact – Service clients <compte-lead@techni-contact.com>\nReply-To: Techni-Contact – Service clients <compte-lead@techni-contact.com>\n",
            'template' => "customer-creation_compte-lead",
            'data' => array(
            "CUSTOMER_ID" => $cuser->id,
            "CUSTOMER_FIRSTNAME" => $cuser->prenom,
            "CUSTOMER_LASTNAME" => $cuser->nom,
            "CUSTOMER_EMAIL" => $cuser->email,
            "CUSTOMER_PASSWORD" => $pass,
            "SITE_MAIN_URL" => URL,
            "SITE_HELP_URL" => URL."aide.html",
            "SITE_ACCOUNT_URL_LOGIN" => COMPTE_URL."login.html"
          )
        );
        $mail = new Email($mailContent);
       /* $mail->Build(
          "Création de votre compte",
          "",
          "creation-compte-lead",
          "From: Service client Techni-Contact <web@techni-contact.com>\n",
          array(
            "CUSTOMER_ID" => $cuser->id,
            "CUSTOMER_FIRSTNAME" => $cuser->prenom,
            "CUSTOMER_LASTNAME" => $cuser->nom,
            "CUSTOMER_EMAIL" => $cuser->email,
            "CUSTOMER_PASSWORD" => $pass,
            "SITE_MAIN_URL" => URL,
            "SITE_HELP_URL" => URL."aide.html",
            "SITE_ACCOUNT_URL_LOGIN" => COMPTE_URL."login.html"
          ),
          "user"
        );*/
        $mail->send();
        //$mail->Save();
      }
      else { // update account timestamp

        $cuser = new CustomerUser($db, $cuser_id);
        
        // update account datas OD: 08/12/2011:  http://www.hook-network.com/storm/tasks/2011/12/07/update-automatique-de-la-fiche-client
        $cuser->nom = $fields["nom"];
        $cuser->prenom = $fields["prenom"];
        $cuser->tel1 = $fields["tel"];
        $cuser->fax1 = $fields["fax"];
        $cuser->fonction = $fields["fonction"];
        $cuser->societe = $fields["societe"];
        $cuser->adresse = $fields["adresse"];
        $cuser->complement = $fields["cadresse"];
        $cuser->cp = $fields["cp"];
        $cuser->pays = $fields["pays"];
        $cuser->nb_salarie = $fields["salaries"];
        $cuser->secteur_activite = $fields["secteur"];
        $cuser->secteur_qualifie = $fields["qualification"];
        $cuser->code_naf = $fields["naf"];
        $cuser->num_siret = $fields["siret"];

        // activation of an unvisible user
        if ($cuser->origin == 'A') {
          $cuser->origin = 'L';
          $cuser->actif = 1;
          $pass = $cuser->generatePassword();
          
          $mailContent = array(
            'email' => $cuser->email,
            'subject' => "Création de votre compte gratuit",
                            'headers' => "From: Techni-Contact – Service clients <compte-lead@techni-contact.com>\nReply-To: Techni-Contact – Service clients <compte-lead@techni-contact.com>\n",
            'template' => "customer-creation_compte-lead",
            'data' => array(
              "CUSTOMER_ID" => $cuser->id,
              "CUSTOMER_FIRSTNAME" => $cuser->prenom,
              "CUSTOMER_LASTNAME" => $cuser->nom,
              "CUSTOMER_EMAIL" => $cuser->email,
              "CUSTOMER_PASSWORD" => $pass,
              "SITE_MAIN_URL" => URL,
              "SITE_HELP_URL" => URL."aide.html",
              "SITE_ACCOUNT_URL_LOGIN" => COMPTE_URL."login.html"
            )
        );
          $mail = new Email($mailContent);
          /*$mail->Build(
            "Création de votre compte",
            "",
            "customer-creation_compte-lead",
            "From: Service client Techni-Contact <web@techni-contact.com>\n",
            array(
              "CUSTOMER_ID" => $cuser->id,
              "CUSTOMER_FIRSTNAME" => $cuser->prenom,
              "CUSTOMER_LASTNAME" => $cuser->nom,
              "CUSTOMER_EMAIL" => $cuser->email,
              "CUSTOMER_PASSWORD" => $pass,
              "SITE_MAIN_URL" => URL,
              "SITE_HELP_URL" => URL."aide.html",
              "SITE_ACCOUNT_URL_LOGIN" => COMPTE_URL."login.html"
            ),
            "user"
          );*/
          $mail->send();
          //$mail->Save();
        }
        
        $cuser->save();
      }
      /*
      // Avail
      $api = new JsonRpcClient(AVAIL_JSONRPC_API_URL);
      try {
        $api->logPurchase(array(
          "SessionID" => $_COOKIE["__avail_session__"],
          "UserID" => (string)$cuser->id,
          "ProductIDs" => array((string)$pdt["infos"]["idTC"]),
          "Prices" => array("0.000001")
        ));
      } catch (Exception $e) {
        echo $e->getMessage();
      }
      */
      // Mail récapitulatif Commercial + Commercial admin
      $res = $db->query("
          SELECT bou.email
          FROM bo_users bou
          LEFT JOIN advertisers a ON bou.id = a.idCommercial AND a.id = ".$pdt["infos"]["adv_id"]." AND a.actif = 1
          WHERE a.id IS NOT NULL OR bou.id = 1", __FILE__, __LINE__);
      if ($db->numrows($res, __FILE__, __LINE__) >= 1) {
        while (list($com_email) = $db->fetch($res))
          $com_emails[] = $com_email;
        $to = implode(";",$com_emails);

        $mailContent = array(
            'email' => 'lead-web@techni-contact.com', //$to.", b.dieterlen@techni-contact.com",  http://www.hook-network.com/storm/tasks/2013/01/28/changement-mail-destinataire-tc-senddemandadmin-recap
            'subject' => "[www.Techni-Contact.com] - Demande de devis gratuit auprès de l'annonceur ".$pdt["infos"]["adv_name"]." (Lead n°".$lead1->id.")",
            'headers' => "From: Lead Techni-Contact <web@techni-contact.com>\n",
            'template' => "tc-send_demand_admin-recap",
            'data' => array_merge($CustomerMailData, $MainProductMailData, array("ADMIN_COMMERCIALRECAP" => $recap_com, "ADMIN_ORIGIN_USER" => $user->name, "ADMIN_LEAD_SRC" => $origin))
        );
        $mail = new Email($mailContent);
        /*$mail->Build(
          "[www.Techni-Contact.com] - Demande de devis gratuit auprès de l'annonceur ".$pdt["infos"]["adv_name"]." (Lead n°".$lead1->id.")",
          "",
          "send-demand-admin-recap",
          "From: Lead Techni-Contact <web@techni-contact.com>\n",
          array_merge($CustomerMailData, $MainProductMailData, array("ADMIN_COMMERCIALRECAP" => $recap_com, "ADMIN_ORIGIN_USER" => $user->name, "ADMIN_LEAD_SRC" => $origin)),
          "old"
        );*/
        $mail->send();
        //$mail->Save();
      }
	  $sql_max  = "SELECT id FROM clients WHERE login='".$fields["email"]."'";
	  $req_max  = mysql_query($sql_max);
      $data_max = mysql_fetch_object($req_max);

      $sql_update = "UPDATE  `clients` SET  `fonction_service` =  '".$fields["service"]."' WHERE  `id` ='".$data_max->id."' "; 
      mysql_query($sql_update);
	  
	  $sql_update_contact = "UPDATE `contacts` SET `fonction_service` = '".$fields["service"]."' WHERE `id` ='".$lead1->id."' ";
	  mysql_query($sql_update_contact);
	  
      $o["data"]["text"] = "OK";
    break;

    case 'update_qualified_sector':

      if (!preg_match("/^[[:alnum:]]([-_.]?[[:alnum:]])*@[[:alnum:]]([-_.]?[[:alnum:]])*\.([a-z]{2,4})$/",$action["email"]))
        $o["error"]["list"]["email"] = "Email valide requis";

      if (!($cuser_id = CustomerUser::getCustomerIdFromLogin($action["email"], $db))) {
        $o["error"]["list"]["email"] = "Client non identifié";
      }else {
        $cuser = new CustomerUser($db, $cuser_id);
        $cuser->secteur_qualifie = trim($action["qualification"]);
        $cuser->save();
      }
      break;

    default:
      $o["error"] .= "Action type is missing";
      break;
  }
}
print json_encode($o);
