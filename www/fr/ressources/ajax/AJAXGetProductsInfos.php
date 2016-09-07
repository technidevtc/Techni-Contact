<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

function get_full_pdts_infos($pdtIds = array(), $idTCs = array()) {
  
  if (empty($pdtIds) && empty($idTCs)) {
    return array();
  }
  
  $db = DBHandle::get_instance();
  $querySelectionCommon = "
    p.id,
    p.price AS pdt_price,
    p.shipping_fee,
    pfr.name,
    pfr.ref_name,
    pfr.fastdesc,
    ffr.id AS cat_id,
    ffr.name AS cat_name,
    rc.price+rc.ecotax AS ref_price,
    rc.ecotax AS ref_ecotax,
    a.category AS adv_category,
    a.delai_livraison AS adv_delivery_time,
    (p.as_estimate + a.as_estimate) as product_as_estimate,
    (IFNULL(rc.price, p.price) REGEXP '^[1-9]{1}[0-9]*((\\.|,)[0-9]+){0,1}$' AND a.category = ".__ADV_CAT_SUPPLIER__.") AS saleable,
    (select count(idProduct) from references_content where idProduct = p.id) as nb_refs";
  
  $queryJoinCommon = "
    INNER JOIN products_fr pfr ON p.id = pfr.id AND pfr.active = 1
    INNER JOIN products_families pf ON p.id = pf.idProduct
    INNER JOIN families_fr ffr ON pf.idFamily = ffr.id
    INNER JOIN advertisers a ON p.idAdvertiser = a.id AND a.actif = 1";
  
  $queries = array();
  if (!empty($pdtIds)) {
    $queries[] = "
      SELECT
        IFNULL(rc.id, p.idTC) AS idTC,
        ".$querySelectionCommon."
      FROM products p".
      $queryJoinCommon."
      LEFT JOIN references_content rc ON p.id = rc.idProduct AND rc.classement = 1 AND rc.vpc = 1 AND rc.deleted = 0
      WHERE pfr.id IN (".implode(",",$pdtIds).")
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
    //pp($e);
  }
  
  if ($db->numrows($res) < 1) // bad result count
    return "Aucun produit n'a été trouvé";

  $pdtList = array();
  $pdtI = 0;
  $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off';
  while ($pdt["infos"] = $db->fetchAssoc($res)) {
    
    $pdt["urls"]["fo_url"] = Utils::get_pdt_fo_url($pdt["infos"]["id"], $pdt["infos"]["ref_name"], $pdt["infos"]["cat_id"]);

    $pdt["pics"] = Utils::get_pdt_pic_url_list($pdt["infos"]["id"], $pdt["infos"]["ref_name"], $isHttps);
    $pdt["infos"]["pic_count"] = count($pdt["pics"]);
    
    $productPrice = null;
    $pdt["infos"]["hasPrice"] = false;
    if ($pdt["infos"]["pdt_price"] == "ref") {
      $pdt["infos"]["price"] = $productPrice = $pdt["infos"]["ref_price"];
    }
    else {
      $pdt["infos"]["price"] = $productPrice = $pdt["infos"]["pdt_price"];
    }
    if (empty($pdt["infos"]["price"])) {
      $pdt["infos"]["price"] = $productPrice = "sur devis";
    }
    elseif (preg_match('/^[0-9]+((\.|,)[0-9]+){0,1}$/',$pdt["infos"]["price"])) { // real price
      $pdt["infos"]["hasPrice"] = true;
      $productPrice = $pdt["infos"]["price"];
      $pdt["infos"]["price"] = sprintf("%.2f",$pdt["infos"]["price"])."&euro;";
    }
    else {
      $pdt["infos"]["price"] = $productPrice = "sur devis";
    }
    // if product is set as default estimate
    if ($pdt["infos"]["as_estimate"] != 0 || $pdt["infos"]['price'] >= __THRESHOLD_PRICE_FOR_ESTIMATE__) {
      $pdt["infos"]["price"] = $productPrice = "sur devis";
    }

    // Shipping fee
    $res2 = $db->query("select config_name, config_value from config where config_name = 'fdp' or config_name = 'fdp_franco' or config_name = 'fdp_sentence'", __FILE__, __LINE__ );
    while ($rec = $db->fetch($res2)) {
      $$rec[0] = $rec[1];
    }
    $pdt["infos"]["shipping_fee"] = empty($pdt["infos"]["shipping_fee"]) ? ($pdt["infos"]["shipping_fee"] = $pdt["infos"]["hasPrice"] ? ($productPrice > $fdp_franco ? "Offert" : $fdp." &euro; HT") : "N/D") : $pdt["infos"]["shipping_fee"]." &euro; HT";
    // set if product is set as default estimate
    $pdt["infos"]['pdt_set_as_estimate'] = false;
    if($pdt["infos"]['price'] >= __THRESHOLD_PRICE_FOR_ESTIMATE__) $pdt["infos"]['pdt_set_as_estimate'] = true;
    if($pdt["infos"]['product_as_estimate']) $pdt["infos"]['pdt_set_as_estimate'] = true;

    $notations = ProductNotation::get('id_product = '.$pdt["infos"]['id'], 'inactive = 0');
      if(!empty ($notations)){
        $a=0;
        foreach ($notations as $notation){
          $sumNote += $notation['note'];
          $a++;
        }
        $pdt["infos"]['average_note'] = round($sumNote/$a);
        $pdt["infos"]['nb_comments'] = $a;
      }

    // cart url
    $pdt["urls"]["cart_add_url"] = "panier:".$pdt["infos"]["cat_id"]."-".$pdt["infos"]["id"]."-".$pdt["infos"]["idTC"];

    $pdtList[$pdtI] = $pdt;
    $pdtListIds[$pdt['infos']['id']] = $pdtI;
    $pdtListIdTCs[$pdt['infos']['idTC']] = $pdtI;
    $pdtI++;
  }
  
  // respect original order
  $pdtListOrdered = array();
  foreach ($pdtIds as $pdtId) {
    if (isset($pdtListIds[$pdtId]))
      $pdtListOrdered[] = $pdtList[$pdtListIds[$pdtId]];
  }
  foreach ($idTCs as $idTC) {
    if (isset($pdtListIdTCs[$idTC]))
      $pdtListOrdered[] = $pdtList[$pdtListIdTCs[$idTC]];
  }
  
  return $pdtListOrdered;
}

$db = DBHandle::get_instance();

$o = array("data" => array(),"error" => "");
$actions = $_POST["actions"];
foreach($actions as $action) {
  switch ($action["action"]) {
    case 'get_nuukik_pdts_infos':
      
      // url encode to avoid any strange kind of url injection
      foreach ($action['params'] as &$param)
        $param = urlencode($param);
      unset($param);
      
      list($zoneId, $controller, $id, $type) = $action['params'];
      
      $idList = Nuukik::get($zoneId, $controller, array($id, $type));

      if (is_string($idList)) {
        $o['error'] = $idList;
      } else {
        $pdtList = get_full_pdts_infos($idList['pdtIdList'], $idList['idTCList']);
        if (is_string($pdtList))
          $o['error'] = $pdtList;
        else
          $o['data']['pdtList'] = $pdtList;
      }
      break;

    case "get_pdts_infos":
      $pdtIds = $idTCs = array();

      $keepInitialOrder = isset($action["keepInitialOrder"]) && $action["keepInitialOrder"] == true ? true : false;
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

      if($keepInitialOrder){
        foreach ($action["pdtIds"] as $pdtInitial){
          foreach ($pdtList as $pdt){
            if($pdt['infos']['id'] == $pdtInitial)
              $reorderedPdt[] = $pdt;
          }
        }
        $pdtList = $reorderedPdt;
      }
      if (is_string($pdtList)) {
        $o["error"] = $pdtList;
        break;
      }
      
      $o["data"]["pdtList"] = $pdtList;
      break;
      
    case "get_pdt_infos":
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
      
      case "get_pdt_refs":
      $pdtIds = isset($action["pdtId"]) && is_numeric($action["pdtId"]) ? array((int)$action["pdtId"] ): array();
      $idTCs = isset($action["idTC"]) && is_numeric($action["idTC"]) ? array((int)$action["idTC"]) : array();
      if (empty($pdtIds) && empty($idTCs)) {
        $o["error"] = "Id fiche produit ou idTC non spécifié ou invalide";
        break;
      }
      if (count($pdtIds) != 1) {
        $o["error"] = "Cette méthode ne récupère que les références d'un seul produit";
        break;
      }
      
      $pdtList = get_full_pdts_infos($pdtIds, $idTCs);

      $pdt_set_as_estimate = false;
      if($pdtList[0]['infos']['price'] >= __THRESHOLD_PRICE_FOR_ESTIMATE__) $pdt_set_as_estimate = true;
      if($pdtList[0]['infos']['product_as_estimate']) $pdt_set_as_estimate = true;

      if($pdtList[0]['infos']['pdt_price'] == 'ref'){

        // loading references if saleable
        $refs = array();
        $max_margin = 1;
        if ($pdtList[0]['infos']["saleable"]) {
          $res = $db->query("
            SELECT content
            FROM references_cols
            WHERE idProduct = ".$pdtIds[0], __FILE__, __LINE__);
          list($content_cols) = $db->fetch($res);
          $content_cols = mb_unserialize($content_cols);
          $custom_cols = array_slice($content_cols, 3, -6);
//id, label, content, refSupplier, price, price2, unite, marge, idTVA
          $res = $db->query("
            SELECT id, label, content, refSupplier, price+ecotax AS price, price2, ecotax, idTVA, unite
            FROM references_content
            WHERE idProduct = ".$pdtIds[0]." AND vpc = 1 AND deleted = 0
            ORDER BY classement", __FILE__, __LINE__);
          while ($ref = $db->fetchAssoc($res)) {
            $ref["content"] = mb_unserialize($ref["content"]);
            $ref["cart_add_url"] = "panier:".$pdtList[0]['infos']["cat_id"]."-".$pdtIds[0]."-".$ref["id"];
            if ($ref["price2"] > 0 && $max_margin < $ref["price"]/$ref["price2"])
              $max_margin = $ref["price"]/$ref["price2"];
            $refs[] = $ref;
          }
        }
        $o["data"]['custom_cols'] = $custom_cols;
        $o["data"]['pdt_set_as_estimate'] = $pdt_set_as_estimate;
        $o["data"]['pdt_infos'] = $pdtList;
      }
      if (is_string($refs)) {
        $o["error"] = $refs;
        break;
      }

      $o["data"]["refs"] = $refs;
      break;

    default:
      $o["error"] = "Type d'action manquant";
      break;
  }
}
mb_convert_variables("UTF-8","ASCII,UTF-8,ISO-8859-1,CP1252",$o);
print json_encode($o);
/*$mts["product"]["end"] = microtime(true);
foreach($mts as $mtn => $mt) 
  print $mtn." = <b>".($mt["end"]-$mt["start"])*1000 ."ms</b><br/>\n";*/
?>
