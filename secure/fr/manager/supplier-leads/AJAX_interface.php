<?php

if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

require ADMIN."statut.php";

$user = new BOUser();

if (!$user->login()) {
  $o["error"] = "Votre session a expiré, veuillez vous identifier à nouveau après avoir rafraîchi votre page";
  print json_encode($o);
  exit();
}

$db = DBHandle::get_instance();
$o = array("data" => array(),"error" => "");
$actions = $_POST["actions"];
foreach($actions as $action) {
  switch ($action["action"]) {
    case "get_supplier_leads":
      if (!$user->get_permissions()->has("m-comm--sm-supplier-leads","r")) {
        $o["error"] = "Vous n'avez pas les droits adéquats pour réaliser cette opération.";
        break;
      }
      
      $options["time"] = isset($action["time"]) ? time() - (int)$action["time"] : time()-3600*24*30*6;
      
      if (isset($action["commercial"])) {
       $options["comList"] = array();
       $options["comListSub"] = array();
        if (is_array($action["commercial"])) {
          foreach($action["commercial"] as $com) {
            if (is_numeric($com))
              $options["comList"][] = $options["comListSub"][] =$com;
          }
        }
        elseif(is_numeric($action["commercial"])) {
          $options["comList"][] = $options["comListSub"][] = $action["commercial"];
        }
      }
      if (!empty($options["comList"])){
        $options["comList"] = " AND bou1.id IN (".implode(",",$options["comList"]).")";
        $options["comListSub"] = " AND bou12.id IN (".implode(",",$options["comListSub"]).")";
      }else
        $options["comList"] = $options["comListSub"] = "";
      
      if (isset($action["search"])) {
        if (preg_match("`^[[:alnum:]]([-_.]?[[:alnum:]])*@[[:alnum:]]([-_.]?[[:alnum:]])*\.([a-z]{2,4})$`", $action["search"]))
          $options["search"] = " AND c.email LIKE '%".$db->escape($action["search"])."%'";
        else
          $options["search"] = " AND c.societe LIKE '%".$db->escape($action["search"])."%'";
      }
      else {
        $options["search"] = "";
      }
      //$options["search"] = isset($action["search"]) ? " AND c.societe LIKE '%".$db->escape($action["search"])."%'" : "";
      
      if (isset($action["france"]))
        if (isset($action["foreign"]))
          $options["origin"] = "";
        else
          $options["origin"] = " AND c.pays = 'FRANCE'";
      elseif (isset($action["foreign"]))
        $options["origin"] = " AND c.pays != 'FRANCE'";
      else
        $options["origin"] = " AND 1=0"; // always false
      
      $options["historic"] = isset($action["historic"]) ? "TRUE" : "c.processing_status = ".__LEAD_P_STATUS_NOT_PROCESSED__;
      
      $dateOrigin = mktime(0,0,0,9,16,2011);
      if ($options["historic"] != "TRUE" && $options["time"] < $dateOrigin)
        $options["time"] = $dateOrigin;

	  if(strcmp($action["etat_traitement"],'1')=='0'){
		$query_tables_etat_traitement	= " 
												LEFT JOIN estimate AS estim ON estim.lead_id = c.id  
										  ";
		$query_param_etat_traitement	= " 
												AND	 estim.status = 1
										  ";

	  }else if(strcmp($action["etat_traitement"],'-')=='0'){
		$query_tables_etat_traitement	= " 
												LEFT JOIN estimate AS estim ON estim.lead_id = c.id  
										  ";
		$query_param_etat_traitement	= " 
												AND	 estim.status is NULL
										  ";

	  }else{
		$query_tables_etat_traitement	= " 
											  
										  ";
		$query_param_etat_traitement	= " 
												
										  ";
	  }
	  

		
      /*$res = $db->query("
        SELECT
          c.id,
          c.societe,
          c.email,
          c.pays,
          c.timestamp,
          c.invoice_status,
          c.precisions AS msg,
          (SELECT name FROM bo_users WHERE id = c.id_user) AS name_user,
          c.origin,
          pfr.id AS pdt_id,
          pfr.name AS pdt_name,
          pfr.ref_name AS pdt_ref_name,
          a.id AS adv_id,
          a.nom1 AS adv_name,
          a.category AS adv_cat,
          ffr3.id AS cat3_id,
          ffr3.ref_name AS cat3_ref_name,
          ffr3.name AS cat3_name,
          IFNULL(bou1.id,'-') AS com_id,
          IFNULL(bou1.name,'-') AS com_name,
          IFNULL(bou1.login,'-') AS com_login,
          IFNULL(bou2.id,'-') AS com_p_id,
          IFNULL(bou2.name,'-') AS com_p_name,
          IFNULL(bou2.login,'-') AS com_p_login,
          FROM_UNIXTIME(c.timestamp,'%Y %j') as daytime,
	  (
                Select count(c2.id) from contacts   c2
                INNER JOIN products_fr pfr2 ON pfr2.id = c2.idProduct AND pfr2.active = 1
                INNER JOIN advertisers a2 ON a2.id = c2.idAdvertiser AND a2.actif = 1 AND a2.category = 1
                ".(empty($options["comListSub"])?"LEFT":"INNER")." JOIN bo_users bou12 ON bou12.id = c2.id_user_commercial".$options["comListSub"]."
                where c2.email=c.email and FROM_UNIXTIME(c2.timestamp,'%Y %j')=daytime and c2.processing_status = 1 AND c2.timestamp > ".$options["time"].$options["search"].$options["origin"]."  group by c2.email, daytime LIMIT 0,".($options["historic"]=="TRUE"?"300":"150")."
        ) AS nbr,
	  (
                Select min(c2.timestamp) from contacts   c2
                INNER JOIN products_fr pfr2 ON pfr2.id = c2.idProduct AND pfr2.active = 1
                INNER JOIN advertisers a2 ON a2.id = c2.idAdvertiser AND a2.actif = 1 AND a2.category = 1
                ".(empty($options["comListSub"])?"LEFT":"INNER")." JOIN bo_users bou12 ON bou12.id = c2.id_user_commercial".$options["comListSub"]."
                where c2.email=c.email and FROM_UNIXTIME(c2.timestamp,'%Y %j')=daytime and c2.processing_status = 1 AND c2.timestamp > ".$options["time"].$options["search"].$options["origin"]."  group by c2.email, daytime LIMIT 0,".($options["historic"]=="TRUE"?"300":"150")."
        ) AS min_daytime
        FROM contacts c
        INNER JOIN products_fr pfr ON pfr.id = c.idProduct AND pfr.active = 1
        INNER JOIN advertisers a ON a.id = c.idAdvertiser AND a.actif = 1 AND a.category = ".__ADV_CAT_SUPPLIER__."
        LEFT JOIN families_fr ffr3 ON ffr3.id = c.idFamily
        ".(empty($options["comList"])?"LEFT":"INNER")." JOIN bo_users bou1 ON bou1.id = c.id_user_commercial".$options["comList"]."
        LEFT JOIN bo_users bou2 ON bou2.id = c.id_user_processed
        WHERE ".$options["historic"]." AND c.timestamp > ".$options["time"].$options["search"].$options["origin"]."
        ORDER BY min_daytime desc, timestamp asc
        LIMIT 0,".($options["historic"]=="TRUE"?"300":"150"), __FILE__, __LINE__);*/

      // pre query to optimize contacts index 'supplier_leads'
      $res = $db->query("SELECT id FROM advertisers WHERE actif = 1 AND category = 1");
      while ($a = $db->fetch($res))
        $al[] = $a[0];
      $activeSupplierIds = implode(",", $al);

/*   
	  echo ("SELECT
          c.id,
          c.societe,
          c.email,
          c.pays,
          c.timestamp,
          c.invoice_status,
          c.precisions AS msg,
          IF(origin = 'Internaute','Internaute',IFNULL(bou3.name,'N/A')) AS name_user,
          origin,
          pfr.id AS pdt_id,
          pfr.name AS pdt_name,
          pfr.ref_name AS pdt_ref_name,
          a.id AS adv_id,
          a.nom1 AS adv_name,
          a.category AS adv_cat,
          ffr3.id AS cat3_id,
          ffr3.ref_name AS cat3_ref_name,
          ffr3.name AS cat3_name,
          IFNULL(bou1.id,'-') AS com_id,
          IFNULL(bou1.name,'-') AS com_name,
          IFNULL(bou1.login,'-') AS com_login,
          IFNULL(bou2.id,'-') AS com_p_id,
          IFNULL(bou2.name,'-') AS com_p_name,
          IFNULL(bou2.login,'-') AS com_p_login,
          nbc.min_timestamp,
          nbc.nbr
        FROM contacts c
        INNER JOIN products_fr pfr ON pfr.id = c.idProduct AND pfr.active = 1
        INNER JOIN advertisers a ON a.id = c.idAdvertiser AND a.actif = 1 AND a.category = ".__ADV_CAT_SUPPLIER__."
        LEFT JOIN families_fr ffr3 ON ffr3.id = c.idFamily
        ".(empty($options["comList"])?
          "LEFT":
          "INNER")." JOIN bo_users bou1 ON bou1.id = c.id_user_commercial".$options["comList"]."
        LEFT JOIN bo_users bou2 ON bou2.id = c.id_user_processed
        LEFT JOIN bo_users bou3 ON bou3.id = c.id_user
		".$query_tables_etat_traitement."
        INNER JOIN (
            SELECT
              COUNT(c.id) AS nbr,
              MIN(c.timestamp) AS min_timestamp,
              c.email,
              FROM_UNIXTIME(c.timestamp,'%Y %j') AS unique_day
            FROM contacts c
            INNER JOIN products_fr pfr2 ON pfr2.id = c.idProduct AND pfr2.active = 1
            INNER JOIN advertisers a2 ON a2.id = c.idAdvertiser
            ".(!empty($options["comList"]) ?
             "INNER JOIN bo_users bou1 ON bou1.id = c.id_user_commercial".$options["comList"]:
             "")."
            WHERE
              c.idAdvertiser IN (".$activeSupplierIds.") AND
              ".$options["historic"]." AND
              c.timestamp > ".$options["time"].
              $options["search"].
              $options["origin"]."
            GROUP BY c.email, unique_day
            LIMIT 0,".($options["historic"]=="TRUE"?"300":"150")."
        ) nbc ON (nbc.email = c.email AND nbc.unique_day = FROM_UNIXTIME(c.timestamp,'%Y %j'))
        WHERE
          ".$options["historic"].
          $options["search"].
          $options["origin"]."
		  ".$query_param_etat_traitement."
        ORDER BY nbc.min_timestamp DESC, timestamp ASC
        LIMIT 0,".($options["historic"]=="TRUE"?"300":"150"));
*/
		
      $res = $db->query("
        SELECT
          c.id,
          c.societe,
          c.email,
          c.pays,
          c.timestamp,
          c.invoice_status,
          c.precisions AS msg,
          c.precisions_additional AS msg_add,
          IF(origin = 'Internaute','Internaute',IFNULL(bou3.name,'N/A')) AS name_user,
          origin,
          pfr.id AS pdt_id,
          pfr.name AS pdt_name,
          pfr.ref_name AS pdt_ref_name,
          a.id AS adv_id,
          a.nom1 AS adv_name,
          a.category AS adv_cat,
          ffr3.id AS cat3_id,
          ffr3.ref_name AS cat3_ref_name,
          ffr3.name AS cat3_name,
          IFNULL(bou1.id,'-') AS com_id,
          IFNULL(bou1.name,'-') AS com_name,
          IFNULL(bou1.login,'-') AS com_login,
          IFNULL(bou2.id,'-') AS com_p_id,
          IFNULL(bou2.name,'-') AS com_p_name,
          IFNULL(bou2.login,'-') AS com_p_login,
          nbc.min_timestamp,
          nbc.nbr
        FROM contacts c
        INNER JOIN products_fr pfr ON pfr.id = c.idProduct AND pfr.active = 1
        INNER JOIN advertisers a ON a.id = c.idAdvertiser AND a.actif = 1 AND a.category = ".__ADV_CAT_SUPPLIER__."
        LEFT JOIN families_fr ffr3 ON ffr3.id = c.idFamily
        ".(empty($options["comList"])?
          "LEFT":
          "INNER")." JOIN bo_users bou1 ON bou1.id = c.id_user_commercial".$options["comList"]."
        LEFT JOIN bo_users bou2 ON bou2.id = c.id_user_processed
        LEFT JOIN bo_users bou3 ON bou3.id = c.id_user
		".$query_tables_etat_traitement."
        INNER JOIN (
            SELECT
              COUNT(c.id) AS nbr,
              MIN(c.timestamp) AS min_timestamp,
              c.email,
              FROM_UNIXTIME(c.timestamp,'%Y %j') AS unique_day
            FROM contacts c
            INNER JOIN products_fr pfr2 ON pfr2.id = c.idProduct AND pfr2.active = 1
            INNER JOIN advertisers a2 ON a2.id = c.idAdvertiser
            ".(!empty($options["comList"]) ?
             "INNER JOIN bo_users bou1 ON bou1.id = c.id_user_commercial".$options["comList"]:
             "")."
            WHERE
              c.idAdvertiser IN (".$activeSupplierIds.") AND
              ".$options["historic"]." AND
              c.timestamp > ".$options["time"].
              $options["search"].
              $options["origin"]."
            GROUP BY c.email, unique_day
            LIMIT 0,".($options["historic"]=="TRUE"?"300":"150")."
        ) nbc ON (nbc.email = c.email AND nbc.unique_day = FROM_UNIXTIME(c.timestamp,'%Y %j'))
        WHERE
          ".$options["historic"].
          $options["search"].
          $options["origin"]."
		  ".$query_param_etat_traitement."
        ORDER BY nbc.min_timestamp DESC, timestamp ASC
        LIMIT 0,".($options["historic"]=="TRUE"?"300":"150"), __FILE__, __LINE__);

      $sll = array();
      while ($sl = $db->fetchAssoc($res)) {
        $sl["timestamp"] = date("d/m/Y H:i", $sl["timestamp"]);
        $sl["invoice_status"] = $lead_invoice_status_list[$sl["invoice_status"]];
        $sl["pdt_fo_url"] = URL."produits/".$sl["cat3_id"]."-".$sl["pdt_id"]."-".$sl["pdt_ref_name"].".html";
        $sl["pdt_pic_url"] = is_file(PRODUCTS_IMAGE_INC."thumb_small/".$sl["pdt_id"]."-1.jpg") ? PRODUCTS_IMAGE_SECURE_URL."thumb_small/".$sl["pdt_id"]."-1.jpg" : PRODUCTS_IMAGE_SECURE_URL."no-pic-thumb_small.gif";
        $sl["adv_bo_url"] = ADMIN_URL."advertisers/edit.php?id=".$sl["adv_id"];
        $sl["cat3_bo_pdt_list"] = ADMIN_URL."search.php?search_type=2&search=".$sl["cat3_id"];
        $sl["adv_cat"] = $adv_cat_list[$sl["adv_cat"]]["name"];
        $sl["msg_add"] = $sl["msg_add"];
		

		$estimate_results_array = array();
		$res_estimate_etat = $db->query("SELECT 
											estim.id AS estimate_id,
											estim.status AS estimate_status,
											estim.created_user_id AS estimate_created_user_id,
											bo_user.login AS bo_ulogin
										FROM 
											estimate AS estim,
											bo_users AS bo_user
										WHERE 
											estim.lead_id = ".$sl["id"]."
										AND	
											estim.created_user_id = bo_user.id
										");
								
						
								
		$estimate_results_array = $db->fetchAssoc($res_estimate_etat);
		
		switch($estimate_results_array["estimate_status"]){
			/*case '0':
				$estimate_status_to_send	= 'Non d&eacute;marer';
			break;
			*/
			
			case '1':
				$estimate_status_to_send	= 'En cours';
			break;
			
			case '2':
				$estimate_status_to_send	= 'Envoy&eacute;';
			break;
			
			case '3':
				$estimate_status_to_send	= 'Mis &agrave; jour';
			break;
			
			case '4':
				$estimate_status_to_send	= 'Gagn&eacute;';
			break;
			
			case '5':
				$estimate_status_to_send	= 'Perdu';
			break;
			
			default:
				$estimate_status_to_send	= '-';
			break;
		
		}
		
		//$sl["lead_status"] = "*".$estimate_status_array["estimate_status"]."*";
		
		$sl["lead_status"] 		= "".$estimate_status_to_send."";
		$sl["bo_user_login"] 	= "".$estimate_results_array["bo_ulogin"]."";
		$sl["lead_id"] 			= "".$estimate_results_array["estimate_id"]."";
		
		
        $sll[] = $sl;
      }
      $o["data"]["sLeadList"] = $sll;
      break;
    
    case "set_processing_status":
      if (!$user->get_permissions()->has("m-comm--sm-supplier-leads","e")) {
        $o["error"] = "Vous n'avez pas les droits adéquats pour réaliser cette opération.";
        break;
      }
      $leadId = isset($action["leadId"]) ? (int)$action["leadId"] : 0;
      $status = isset($action["status"]) ? (int)$action["status"] : 0;
      $reason = isset($action["reason"]) ? trim($action["reason"]) : "";
      $comment = isset($action["comment"]) ? trim($action["comment"]) : "";
      
      if (!$leadId) {
        $o["error"] = "Identifiant devis fournisseur non valide.";
        break;
      }
      if (!isset($lead_processing_status_list[$status])) {
        $o["error"] = "Nouveau statut de traitement du devis fournisseur non valide.";
        break;
      }

      $res1 = $db->query("
        SELECT email, FROM_UNIXTIME(timestamp,'%Y%j') as daytime FROM contacts WHERE id = ".$leadId);

      $contactInfos = $db->fetch($res1);
      if(!empty ($contactInfos) && is_array($contactInfos))
        $db->query("
            UPDATE contacts c
              INNER JOIN advertisers a ON a.id = c.idAdvertiser AND a.actif = 1 AND a.category = 1
              SET
              processing_status = ".$status.",
              id_user_processed = ".$user->id.",
              processing_time = ".time().",
              processing_reason = '".$db->escape($reason)."',
              processing_comment = '".$db->escape($comment)."'
            WHERE c.email = '".$contactInfos[0]."' AND FROM_UNIXTIME(c.timestamp,'%Y%j') = ".$contactInfos[1], __FILE__, __LINE__);
      
      if ($status == __LEAD_P_STATUS_NOT_PROCESSABLE__) { // unprocessable
        $res = $db->query("
          SELECT
            c.email,
            c.timestamp,
            c.idFamily AS cat3_id,
            pfr.id AS pdt_id,
            pfr.name AS pdt_name,
            pfr.ref_name AS pdt_ref_name
          FROM contacts c
          INNER JOIN products_fr pfr ON pfr.id = c.idProduct
          WHERE c.id = ".$leadId, __FILE__, __LINE__);
        $sl = $db->fetchAssoc($res);
        
        // internal note
        $note = new InternalNotesOld("compte_client");
        $message = "Mail sortant par service commercial :\nLe devis demandé est intraitable pour la raison suivante :\n".$reason."\n\n".$comment;
        $note->addNote($user, $message, $sl["email"]);
        
        // email
        $mail = new Email(array(
          "email" => $sl["email"],
          "subject" => "Note importante concernant votre devis",
          "headers" => "From: Service client Techni-Contact <".$user->email.">\r\nReply-To: Service client Techni-Contact <".$user->email.">\r\n",
          "template" => "user-bo_supplier_leads-unprocessable",
          "data" => array(
            "FO_URL" => URL,
            "SLEAD_DATE" => date("d/m/Y", $sl["timestamp"]),
            "PRODUCT_NAME" => $sl["pdt_name"],
            "PRODUCT_URL" => URL."produits/".$sl["cat3_id"]."-".$sl["pdt_id"]."-".$sl["pdt_ref_name"].".html",
            "UNPROCESSABLE_REASON" => $reason,
            "UNPROCESSABLE_COMMENT" => $comment,
            "OPERATOR_NAME" => $user->name,
            "OPERATOR_TEL" => $user->phone,
            "OPERATOR_EMAIL" => $user->email
          )
        ));
        $mail->send();
      }
      
      $o["data"] = array(
        "processing-status" => $status,
        "processing-status-text" => $lead_processing_status_list[$status],
        "user-name-processed" => $user->name
      );
      break;
    
    default:
      $o["error"] .= "Action type is missing";
      break;
  }
}

print json_encode($o);