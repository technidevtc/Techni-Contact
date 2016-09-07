<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$db = DBHandle::get_instance();

// getting every lead which has the rejected and visible flags*
// -> only corresponds to leads that are not confirmed as rejected
$rejected_mask = __LEAD_REJECTED__ | __LEAD_VISIBLE__;
$res = $db->query("
  SELECT
    c.id,
    c.idFamily,
    c.parent,
    c.income,
    c.income_total,
    c.invoice_status,
    c.timestamp,
    c.nom,
    c.prenom,
    c.email,
	c.reject_reason,
	c.idProduct,
	c.parent,
	
    pfr.name AS pdt_name,
	
    a.id AS adv_id,
    a.nom1 AS adv_name
	
  FROM contacts c
  LEFT JOIN products_fr pfr ON c.idProduct = pfr.id
  INNER JOIN advertisers a ON c.idAdvertiser = a.id
  WHERE c.invoice_status & ".$rejected_mask." = ".$rejected_mask,__FILE__,__LINE__);

while ($lead = $db->fetchAssoc($res)) {

  $now = time();
  $thisDay = date('d',$now);
  $thisMonth = date('m',$now);
  $thisYear = date('Y',$now);
  $dateLeadDay = date('d',$lead["timestamp"]);
  $dateLeadMonth = date('m',$lead["timestamp"]);
  $dateLeadYear = date('Y',$lead["timestamp"]);
  $nextMonth = mktime(0,0,0,$dateLeadMonth + 1,$dateLeadDay,$dateLeadYear);

  if ($lead["parent"] == 0) { // it's a primary lead
    // a charged lead waiting to be rejected and validated turns credited
//    if ($lead["invoice_status"] == __LEAD_INVOICE_STATUS_REJECTED_WAIT__) {
//      $lead["invoice_status"] = __LEAD_INVOICE_STATUS_CREDITED__;
//      $income_init = '';
//      $rejectTimestamp = ", `reject_timestamp` = '".$now."', `credited_on` = '".$nextMonth."'";
//    }
//    elseif ($lead["invoice_status"] != __LEAD_INVOICE_STATUS_CREDITED__) { // in any other case, it is rejected
      $lead["invoice_status"] = __LEAD_INVOICE_STATUS_REJECTED__;
      // we update both its income and its total income
      $lead["income_total"] -= $lead["income"];
      $rejectTimestamp = ", `reject_timestamp` = '".$now."'";
      $income_init = ", `income_init` = '".$lead["income"]."'";
      $lead["income"] = 0;
//    }

    $db->query("
      UPDATE contacts SET
        `invoice_status` = '".$lead["invoice_status"]."',
        `income_total` = '".$lead["income_total"]."',
        `income` = '".$lead["income"]."'
        ".$rejectTimestamp.$income_init."
      WHERE id = ".$lead["id"],__FILE__,__LINE__);
  }
  else { // it's a secondary lead
    // we get the parent primary lead and change its total income
    $res1 = $db->query("
        SELECT c.id, c.idProduct, c.income_total, pfr.name AS pdt_name
        FROM contacts c
        INNER JOIN products_fr pfr ON pfr.id = c.idProduct
        WHERE c.id = ".$lead["parent"],__FILE__,__LINE__);
    $lead1 = $db->fetchAssoc($res1);
    $lead["pdt_name"] = $lead1["pdt_name"];

    // then we update the original secondary lead
    // a charged lead waiting to be rejected and validated turns credited
//    if ($lead["invoice_status"] == __LEAD_INVOICE_STATUS_REJECTED_WAIT__) {
//      $lead["invoice_status"] = __LEAD_INVOICE_STATUS_CREDITED__;
//      $lead1["income_total"] = $lead["income_total"];
//      $rejectTimestamp = ", `reject_timestamp` = '".$now."', `credited_on` = '".$nextMonth."'";
//      $income_init = '';
//    }
//    elseif ($lead["invoice_status"] != __LEAD_INVOICE_STATUS_CREDITED__) { // in any other case, it is rejected
      $lead["invoice_status"] = __LEAD_INVOICE_STATUS_REJECTED__;
      $lead1["income_total"] -= $lead["income"];
      $db->query("UPDATE contacts SET `income_total` = '".$lead1["income_total"]."' WHERE id = ".$lead["parent"],__FILE__,__LINE__);
      $rejectTimestamp = ", `reject_timestamp` = '".$now."'";
      $income_init = ", `income_init` = '".$lead["income"]."'";
      $lead["income"] = 0;
//    }

    $db->query("
      UPDATE contacts SET
        `invoice_status` = '".$lead["invoice_status"]."',
        `income` = '".$lead["income"]."'
        ".$rejectTimestamp.$income_init."
      WHERE id = ".$lead["id"],__FILE__,__LINE__);

    // checking for auto reject
    $res2 = $db->query("SELECT idAdvertiser FROM auto_reject_links WHERE idAdvertiser = ".$lead["adv_id"]." AND idProduct = ".$lead1["idProduct"]." AND idFamily = ".$lead["idFamily"],__FILE__,__LINE__);
    if ($db->numrows($res2,__FILE__,__LINE__) == 0) {
      // partner auto reject threshold
      $res2 = $db->query("SELECT auto_reject_threshold FROM advertisers WHERE id = ".$lead["adv_id"],__FILE__,__LINE__);
      list($art) = $db->fetch($res2);
      if (!$art) { // getting the global one if no partner specific setting
        $res2 = $db->query("SELECT config_value FROM config WHERE config_name = 'auto_reject_threshold'",__FILE__,__LINE__);
        list($art) = $db->fetch($res2);
      }
      // getting every secondary leads for this partner which have the same product for primary lead
      $res2 = $db->query("SELECT id, invoice_status, timestamp FROM contacts WHERE idFamily = ".$lead["idFamily"]." AND idAdvertiser = ".$lead["adv_id"]." AND parent IN (
        SELECT id FROM contacts WHERE idProduct = ".$lead1["idProduct"]." AND id IN (
          SELECT parent FROM contacts WHERE idFamily = ".$lead["idFamily"]." AND idAdvertiser = ".$lead["adv_id"]." AND parent != 0
        )
      ) ORDER BY timestamp desc",__FILE__,__LINE__);
      $src = 0; // successive reject count
      while ($lead2 = $db->fetchAssoc($res2)) {
        if ($lead2["invoice_status"] == __LEAD_INVOICE_STATUS_REJECTED__)
          $src++;
        else
          break;
      }
      if ($src >= $art) { // activate auto rejection of secondary leads from this product in this category for this advertiser
        $db->query("INSERT INTO auto_reject_links (idAdvertiser, idProduct, idFamily) VALUES (".$lead["adv_id"].", ".$lead1["idProduct"].", ".$lead["idFamily"].")",__FILE__,__LINE__);
      }
    }
  }
  
	//Building Product URL

	if(empty($lead["idProduct"])){
		$res_product = $db->query("SELECT
									p.id,
									
									pf.idFamily as catID,
									
									pfr.ref_name
									
								FROM products p
									INNER JOIN products_fr pfr ON p.id = pfr.id
									INNER JOIN products_families pf ON p.id = pf.idProduct
									INNER JOIN  contacts c ON c.idProduct=p.id
									
								  WHERE c.id=".$lead["parent"],__FILE__,__LINE__);
	  
		$res_product_url = $db->fetchAssoc($res_product);
	
		$fo_pdt_url = URL."produits/".$res_product_url["catID"]."-".$res_product_url["id"]."-".$res_product_url["ref_name"].".html";
		
	}else{
		$res_product = $db->query("SELECT
									p.id,
									
									pf.idFamily as catID,
									
									pfr.ref_name
									
								FROM products p
									INNER JOIN products_fr pfr ON p.id = pfr.id
									INNER JOIN products_families pf ON p.id = pf.idProduct
									
								  WHERE p.id=".$lead["idProduct"],__FILE__,__LINE__);
	  
		$res_product_url = $db->fetchAssoc($res_product);
		
		if(!empty($res_product_url["id"])){
			$fo_pdt_url = URL."produits/".$res_product_url["catID"]."-".$res_product_url["id"]."-".$res_product_url["ref_name"].".html";
		}else{
			$fo_pdt_url = URL;
		}
	}//end else if(empty($lead["parent"]))
	
	//If we are in Preprod
	if(TEST){
		echo(' In test ! <br />');
		// mail the customer
		  $mail = new Email(array(
			"email" => "t.henryg@techni-contact.com",
			"subject" => "Message de la société ".$lead["adv_name"]." concernant votre devis",
			"headers" => "From: Service client Techni-Contact <web@techni-contact.com>\nReply-To: Service client Techni-Contact <web@techni-contact.com>\r\n",
			"template" => "user-bo_leads-lead_rejected",
			"data" => array(
			  "FO_URL" => URL,
			  "CUSTOMER_FIRSTNAME" => $lead["prenom"],
			  "CUSTOMER_LASTNAME" => $lead["nom"],
			  "PRODUCT_NAME" => $lead["pdt_name"],
			  "LEAD_ID" => $lead["id"],
			  "PARTNER_NAME" => $lead["adv_name"],
			  "PRODUCT_LINK" => $fo_pdt_url,
			  "REJECT_RAISON" => $lead["reject_reason"]
			)
		  ));
		  $mail->send();
	
		echo(' End Send <br />');
		
	}else{
		// mail the customer
		  $mail = new Email(array(
			"email" => $lead["email"],
			"subject" => "Message de la société ".$lead["adv_name"]." concernant votre devis",
			"headers" => "From: Service client Techni-Contact <web@techni-contact.com>\nReply-To: Service client Techni-Contact <web@techni-contact.com>\r\n",
			"template" => "user-bo_leads-lead_rejected",
			"data" => array(
			  "FO_URL" => URL,
			  "CUSTOMER_FIRSTNAME" => $lead["prenom"],
			  "CUSTOMER_LASTNAME" => $lead["nom"],
			  "PRODUCT_NAME" => $lead["pdt_name"],
			  "LEAD_ID" => $lead["id"],
			  "PARTNER_NAME" => $lead["adv_name"],
			  "PRODUCT_LINK" => $fo_pdt_url,
			  "REJECT_RAISON" => $lead["reject_reason"]
			)
		  ));
		  $mail->send();
	
	}
  
  print "<br/><br/><br/>";
}
