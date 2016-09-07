<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

if (!isset($_GET["category"]) || !preg_match("/^[0-9a-z\-]+$/", $_GET["category"])) {
  //require("404.php");
  exit();
}else{
    global $conn;
  }

$conn->setCharset('latin1'); // fpdf doesn't work in utf-8

$curCategory = $_GET["category"];

function cleanDescHtml($str){
  $descc3 = preg_replace("`<font .*>`siU", '', $str);
  $descc2 = preg_replace("`</font.*>`siU", '', $descc3);
  $descc = preg_replace("`style=\".*\"`siU", '', $descc2);
  return $descc;
}

//include_once(LANG_LOCAL_INC."meta-titles-".DB_LANGUAGE."_local.php");

//define("CAT3_EXAMPLES_COUNT_MAX", 3);
//define("PDT_SELECTION_COUNT", 5);
//define("FAMILIES_PAGES", true);

$db = DBHandle::get_instance();
//$session = new UserSession($db);

// Loading XML
$dom = new DomDocument();
$dom->validateOnParse = true;
$dom->load(XML_CATEGORIES_ALL);
$xPath = new DOMXPath($dom);

$curCatDOMNode = $dom->getElementById(XML_KEY_PREFIX.$curCategory);
if (!$curCatDOMNode) {
  //require("404.php");
  exit();
}

$catTree = $xPath->query("ancestor-or-self::category",$curCatDOMNode);

// Globals stats
$cat0 = $xPath->query("parent::categories",$dom->getElementById(XML_KEY_PREFIX."0"))->item(0);
$stats_key = explode("|",$xPath->query("child::stats_key",$cat0)->item(0)->nodeValue);
$stats = explode("|",$xPath->query("child::stats",$cat0)->item(0)->nodeValue);
for($sk = 0, $slen = count($stats); $sk < $slen; $sk++) $global[$stats_key[$sk]] = $stats[$sk];

$cat1 = $catTree->item(0);
$stats = explode("|",$xPath->query("child::stats",$cat1)->item(0)->nodeValue);
for($sk = 0, $slen = count($stats); $sk < $slen; $sk++)
  $scat1[$stats_key[$sk]] = $stats[$sk];

// Settings global categories vars

switch($catTree->length) {
  case 1 : 
    $cat2List = $xPath->query("child::category", $cat1);
    $cat3List = $xPath->query("child::category/child::category", $cat1);
    $cur_cat = $cat1;
    foreach($cat3List as $cat3)
      $cat3IDs[] = (int)$cat3->getAttribute("id");
    break;
  case 2 :
    $cat2 = $catTree->item(1);
    $cat3List = $xPath->query("child::category", $cat2);
    $cur_cat = $cat2;
    $stats = explode("|",$xPath->query("child::stats",$cat2)->item(0)->nodeValue);
    for($sk = 0, $slen = count($stats); $sk < $slen; $sk++)
      $scat2[$stats_key[$sk]] = $stats[$sk];
    foreach($cat3List as $cat3)
      $cat3IDs[] = (int)$cat3->getAttribute("id");
    break;
  case 3 :
    $cat2 = $catTree->item(1);
    $cat3 = $catTree->item(2);
    $cat3List = $xPath->query("child::category", $cat2);
    $cat3IDs[] = (int)$cat3->getAttribute("id");
    $cur_cat = $cat3;
    $stats = explode("|",$xPath->query("child::stats",$cat2)->item(0)->nodeValue);
    for($sk = 0, $slen = count($stats); $sk < $slen; $sk++)
      $scat2[$stats_key[$sk]] = $stats[$sk];
    $stats = explode("|",$xPath->query("child::stats",$cat3)->item(0)->nodeValue);
    for($sk = 0, $slen = count($stats); $sk < $slen; $sk++)
      $scat3[$stats_key[$sk]] = $stats[$sk];
    break;
  default : break;
}


// --------------------------------------------------------------------------------
// SELECTION/FAVOURITE/MOST VIEWED/LATEST PDT
// Getting the products ID from xml and/or from the DB
// --------------------------------------------------------------------------------
// Shipping fee
$res = $db->query("select config_name, config_value from config where config_name = 'fdp' or config_name = 'fdp_franco' or config_name = 'fdp_sentence'", __FILE__, __LINE__ );
while ($rec = $db->fetch($res)) {
  $$rec[0] = $rec[1];
}

if(empty($cat3IDs)){
  $cat3IDs[] = $cat3->getAttribute("id");
}
// Mini Stores
/*$msl = MiniStore::getMiniStoresByCatIDs($cat3IDs, true, false, true);
if (!empty($msl)) {
  $k = 0;
  foreach($msl as $ms) {
    if ($k++ >= 3) break;
    $msnl[] = $ms["name"]; // mini store name list
  }
  $msnl = implode(", ",$msnl);
}*/

// category custom title & meta desc
$cc_title = ($cc_title_node = $xPath->query("child::title", $cur_cat)) >= 1 ? $cc_title_node->item(0)->nodeValue : "";
$cc_meta_desc = ($cc_meta_desc_node = $xPath->query("child::meta_desc", $cur_cat)) >= 1 ? $cc_meta_desc_node->item(0)->nodeValue : "";

$pdt_infos = array();
if($catTree->length != 3)
  exit;

switch ($catTree->length) {
  /*case 1:
    $pageName = "liste_categories";
    break;
  case 2:
    $pageName = "liste_categories";
    break;*/
  case 3:
    $pageName = "liste_produits";
    break;
  default:
    break;
}





  if ($standalone = $_SERVER["SCRIPT_NAME"] == "/".basename(__FILE__)) {
    require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
    
    //header("content-type: text/html; charset=utf-8");
    
    $curCategory = $_REQUEST["cat3Id"];
    
    $db = DBHandle::get_instance();
    $session = new UserSession($db);

    // Loading XML
    $dom = new DomDocument();
    $dom->validateOnParse = true;
    $dom->load(XML_CATEGORIES_ALL);
    $xPath = new DOMXPath($dom);

    $curCatDOMNode = $dom->getElementById(XML_KEY_PREFIX.$curCategory);
    if (!$curCatDOMNode)
      exit();

    $catTree = $xPath->query("ancestor-or-self::category",$curCatDOMNode);

    // Globals stats
    $cat0 = $xPath->query("parent::categories",$dom->getElementById(XML_KEY_PREFIX."0"))->item(0);
    $stats_key = explode("|",$xPath->query("child::stats_key",$cat0)->item(0)->nodeValue);
    $stats = explode("|",$xPath->query("child::stats",$cat0)->item(0)->nodeValue);
    for($sk = 0, $slen = count($stats); $sk < $slen; $sk++) $global[$stats_key[$sk]] = $stats[$sk];

    $cat1 = $catTree->item(0);
    $stats = explode("|",$xPath->query("child::stats",$cat1)->item(0)->nodeValue);
    for($sk = 0, $slen = count($stats); $sk < $slen; $sk++)
      $scat1[$stats_key[$sk]] = $stats[$sk];

    $cat2 = $catTree->item(1);
    $cat3 = $catTree->item(2);
    $cat3List = $xPath->query("child::category", $cat2);
    $cat3IDs[] = (int)$cat3->getAttribute("id");
    $cur_cat = $cat3;
    $stats = explode("|",$xPath->query("child::stats",$cat2)->item(0)->nodeValue);
    for($sk = 0, $slen = count($stats); $sk < $slen; $sk++)
      $scat2[$stats_key[$sk]] = $stats[$sk];
    $stats = explode("|",$xPath->query("child::stats",$cat3)->item(0)->nodeValue);
    for($sk = 0, $slen = count($stats); $sk < $slen; $sk++)
      $scat3[$stats_key[$sk]] = $stats[$sk];
    
    $pdt_infos = array();
  }

  $pdt_selection = array();
  $pdt_selection_node = $xPath->query("child::pdt_selection", $cur_cat);
  if ($pdt_selection_node->length >= 1) {
    $pdt_selection = explode("|",$pdt_selection_node->item(0)->nodeValue);
    //foreach($pdt_selection as $v) $pdt_infos[$v] = true;
  }
  $pdt_selection_count = count($pdt_selection);
  
  $cat3Id = $cat3->getAttribute("id");
  
  $results = array("data" => array(), "count" => 0);
  
  define("NB", 50);
  

  
  $joins = $where = $having = array();
  if ($standalone) {
    if ($validFilters["partner"])
      $where[] = "a.category != ".__ADV_CAT_SUPPLIER__;
    if ($validFilters["saleable"])
      $having[] = "saleable = 1";
    if ($validFilters["price"]) {
      $having[] = "hasPrice = 1";
      foreach($validFilters["price"] as $range)
        $having[] = "price >= ".$range[0]." AND price < ".$range[1];
    }
    if ($validFilters["attrVal"]) {
      $where[] = "(
        SELECT COUNT(DISTINCT rav.attributeId)
        FROM products_ref_attributes_values prav
        INNER JOIN ref_attributes_values rav ON rav.id = prav.attributeValueId
        WHERE prav.productId = p.id AND prav.attributeValueId IN (".implode(",",$validFilters["attrVal"]).")
      ) = (
        SELECT COUNT(DISTINCT rav.attributeId)
        FROM products_ref_attributes_values prav
        INNER JOIN ref_attributes_values rav ON rav.id = prav.attributeValueId
        WHERE prav.attributeValueId IN (".implode(",",$validFilters["attrVal"]).")
      )";
    }
    // facettes intervales
    if ($validFilters["intervVal"]) {
      
      $listValues = array();
      foreach($validFilters["intervVal"] as $intervVal){
        $rai = new RefAttributeInterval();
        $raiValues = $rai->get('id = '.$intervVal);
        foreach ($rai->get_interval_values(array($raiValues[0]['attributeId'], 'value >= '.$raiValues[0]['start_from'], 'value <= '.$raiValues[0]['goes_to'])) as $av){
          $listValues[] = $av->id;
        }

      }
      $where[] = "(
        SELECT COUNT(DISTINCT rav.attributeId)
        FROM products_ref_attributes_values prav
        INNER JOIN ref_attributes_values rav ON rav.id = prav.attributeValueId
        WHERE prav.productId = p.id AND prav.attributeValueId IN (".implode(",",$listValues).")
      ) = (
        SELECT COUNT(DISTINCT rav.attributeId)
        FROM products_ref_attributes_values prav
        INNER JOIN ref_attributes_values rav ON rav.id = prav.attributeValueId
        WHERE prav.attributeValueId IN (".implode(",",$listValues).")
      )";
    }
    // facettes virtuelles
    if ($validFilters["virtAttrVal"]) {
      $listValues = array();
      foreach($validFilters["virtAttrVal"] as $virtAttrVal){
        $rav = new RefAttributeVirtualProducts();
        $ravValues = $rav->get('id_ref_attribute_virtual = '.$virtAttrVal);
        foreach ($ravValues as $av){
          $listValues[] = $av['id_ref_attribute_virtual'];
        }

      }

      $where[] = "(
        SELECT COUNT(DISTINCT ravi.attributeId)
        FROM ref_attributes_virtual_products ravp
        INNER JOIN ref_attributes_virtual ravi ON ravi.id = ravp.id_ref_attribute_virtual
        WHERE ravp.id_product = p.id AND ravp.id_ref_attribute_virtual IN (".implode(",",$listValues).")
      ) = (
        SELECT COUNT(DISTINCT ravi.attributeId)
        FROM ref_attributes_virtual_products ravp
        INNER JOIN ref_attributes_virtual ravi ON ravi.id = ravp.id_ref_attribute_virtual
        WHERE ravp.id_ref_attribute_virtual IN (".implode(",",$listValues).")
      )";
    }
  }

  $res = $db->query("
    SELECT
      p.id,
      IFNULL(rc.price+rc.ecotax, p.price) AS price,
      p.timestamp,
      p.shipping_fee,
      pfr.name,
      pfr.ref_name,
      pfr.fastdesc,
      pfr.delai_livraison AS delivery_time,
      pfr.descc,
      pfr.descd,
      pf.idFamily AS catID,
      ps.hits,
      ps.orders,
      ps.leads,
      (".mktime(0,0,0)."- ps.first_hit_time) AS age,
      a.id AS adv_id,
      a.nom1 AS adv_name,
      a.category AS adv_cat,
      a.delai_livraison AS adv_delivery_time,
      a.idCommercial as adv_comm_id,
      bou.name as bou_name,
      bou.phone as bou_phone,
      bou.id as id_bo_usr,
      rc.id AS ref_idtc,
      rc.refSupplier AS ref_refSupplier,
      (IFNULL(rc.price, p.price) REGEXP '^[1-9]{1}[0-9]*((\\.|,)[0-9]+){0,1}$') AS hasPrice,
      (IFNULL(rc.price, p.price) REGEXP '^[1-9]{1}[0-9]*((\\.|,)[0-9]+){0,1}$' AND a.category = ".__ADV_CAT_SUPPLIER__.") AS saleable,
      (p.as_estimate + a.as_estimate) as product_as_estimate,
      (select count(idProduct) from references_content where idProduct = p.id) as nb_refs
    FROM products p
    INNER JOIN products_fr pfr ON p.id = pfr.id AND pfr.active = 1 AND pfr.deleted != 1
    INNER JOIN products_families pf ON p.id = pf.idProduct AND pf.idFamily = ".$cat3Id."
    INNER JOIN products_stats ps ON p.id = ps.id
    INNER JOIN advertisers a ON p.idAdvertiser = a.id AND a.actif = 1
    INNER JOIN bo_users bou ON a.idCommercial = bou.id
    LEFT JOIN references_content rc ON p.id = rc.idProduct AND rc.classement = 1 AND rc.vpc = 1 AND rc.deleted = 0
    ".(!empty($joins) ? "INNER JOIN ".implode(" INNER JOIN ",$joins) : "")."
    ".(!empty($where) ? "WHERE ".implode(" AND ",$where) : "")."
    ".(!empty($having) ? "HAVING ".implode(" AND ",$having) : "")."
    ", __FILE__, __LINE__);
  
  // Fetching results
  while ($pdt = $db->fetchAssoc($res)) {
    $pdt_infos[$pdt["id"]] = $pdt;
    $results["data"][] = &$pdt_infos[$pdt["id"]];
    $results["count"]++;
  }
  
  if ($results["count"] > 0) {
    
    // determining the commercial with the most products for help message and tel
    $comm_pdt_count = array();
    foreach ($pdt_infos as $pdt)
      $comm_pdt_count[$pdt['adv_comm_id']]++;
    arsort($comm_pdt_count);
    reset($comm_pdt_count);
    if (current($comm_pdt_count) != next($comm_pdt_count)) { // 2 commercials have different products count{
      reset($comm_pdt_count);
      $pdt_referent_commercial_id = key($comm_pdt_count);
    }  else {
      $pdt_referent_commercial_id = false;
    }
    
    // Shipping fee
    $res = $db->query("select config_name, config_value from config where config_name = 'fdp' or config_name = 'fdp_franco' or config_name = 'fdp_sentence'", __FILE__, __LINE__ );
    while ($rec = $db->fetch($res)) {
      $$rec[0] = $rec[1];
    }
    
    // Loop for price calculation and filtering
    $pdtList = array(
      "data" => array(),
      "count" => 0,
      "totalCount" => 0,
      "saleableCount" => 0,
      "advCatCount" => array(),
      "advCatCountShowed" => array(),
      "priceSum" => 0,
      "priceCount" => 0,
      "priceCountShowed" => 0,
      "priceMax" => 0,
      "priceMin" => 0x7fffffff
    );
    foreach ($results["data"] as $k => &$pdt) {
      
      // Determing price
      if ($pdt["hasPrice"]) {
        $pdtList["priceCount"]++;
        $pdtList["priceSum"] += $pdt["price"];
        $pdtList["priceMax"] = max($pdt["price"], $pdtList["priceMax"]);
        $pdtList["priceMin"] = min($pdt["price"], $pdtList["priceMin"]);
        if ($pdt["saleable"])
          $pdtList["saleableCount"]++;
      }
      else
        $pdt["price"] = "sur devis";
      
      // Filtering
      $show = true;
      switch ($filter) {
        case "saleable" : if (!$pdt["saleable"]) $show = false; break;
        case "partner"  : if ($pdt["adv_cat"] == __ADV_CAT_SUPPLIER__) $show = false; break;
        case "price" : if (!$pdt["hasPrice"] || $pdt["price"] < $minPrice || $pdt["price"] >= $maxPrice) $show = false; break;
        default : break;
      }
      
      if (!isset($pdtList["advCatCount"][$pdt["adv_cat"]]))
        $pdtList["advCatCount"][$pdt["adv_cat"]] = 0;
      $pdtList["advCatCount"][$pdt["adv_cat"]]++;

      if ($show) {
        if (!isset($pdtList["advCatCountShowed"][$pdt["adv_cat"]]))
          $pdtList["advCatCountShowed"][$pdt["adv_cat"]] = 0;
        $pdtList["advCatCountShowed"][$pdt["adv_cat"]]++;
        
        $pdt["hpd"] = ($pdt["hits"] / $pdt["age"] * 86400);
        if ($pdt["hits"] > 0) {
          $pdt["hits2orders"] = $pdt["orders"] / $pdt["hits"] * 100;
          $pdt["hits2leads"] = $pdt["leads"] / $pdt["hits"] * 100;
        }
        else {
          $pdt["hits2orders"] = $pdt["hits2leads"] = 0;
        }
        $pdt["shipping_fee"] = empty($pdt["shipping_fee"]) ? ($pdt["shipping_fee"] = $pdt["hasPrice"] ? ($pdt["price"] > $fdp_franco ? "Offert" : $fdp." € HT") : "N/D") : $pdt["shipping_fee"]." € HT";
        if (empty($pdt["delivery_time"])) $pdt["delivery_time"] = $pdt["adv_delivery_time"];
        $pdt["url"] = URL."produits/".$pdt["catID"]."-".$pdt["id"]."-".$pdt["ref_name"].".html";
        $pdt["cart_add_url"] = "panier:".$pdt["catID"]."-".$pdt["id"]."-".$pdt["ref_idtc"];
        $pdt["pic_url"] = is_file(PRODUCTS_IMAGE_INC."card/".$pdt["id"]."-1".".jpg") ? "ressources/images/produits/card/".$pdt["id"]."-1".".jpg" : "ressources/images/produits/no-pic-card.gif";
        $pdtList["data"][] = &$pdt;
        if ($pdt["hasPrice"]) $pdtList["priceCountShowed"]++;
        $pdtList["count"]++;
      }
      $pdtList["totalCount"]++;
      
    }
    unset($pdt);
    
    // show the price ordering arrows
    $showPriceSortBlock = $pdtList["priceCountShowed"] > 1;
    
  // Stats computing
    $mts["STATS COMPUTING"]["start"] = microtime(true);
    
    // Setting some usefull vars
    $global["hits_avg"] = $global["hits"] / $global["pdt_count"];
    $global["leads_avg"] = $global["leads"] / $global["pdt_count"];
    $global["orders_avg"] = $global["orders"] / $global["pdt_count"];
    $global["hpd_avg"] = ($global["hits"] / $global["age_avg"] * 86400) / $global["pdt_count"];
  
    // Arbitrary user weight :
    // ----------------------------------------
    // Normal weight
    $hits2leads_w = 0.2;	// weight of products leads
    $hits2orders_w = 0.8;	// weight of products orders
    
    $global_w = 0.8;		// weight of the global(whole db) average hits/orders/...
    $cat1_w = 2.0;		// same for cat 1
    $cat2_w = 0.8;		// for cat 2
    $cat3_w = 0.4;		// for cat 3
    
    // Frequency weight if 2 times more
    $hits_freq_w2 = 1.1;				// 2 times more hits than average = 10% more weight
    $hitsPerDay_freq_w2 = 1.2;		// same for hits per day (ex: 20%)
    
    // Frequency for a category is it's relative part in the whole db
    $cat1_freq_w2 = 1.1;	// weight of a cat1 frequency relative to the average ; ex: if 2 times the average frequency, 10% more weight (1.1)
    $cat2_freq_w2 = 1.1;	// same for cat 2
    $cat3_freq_w2 = 1.1;	// for cat 3
    
    // Precalculating som vars
    // ----------------------------------------
    $hits_freq_w2p = log($hits_freq_w2, 2);
    $hitsPerDay_freq_w2p = log($hitsPerDay_freq_w2,2);
    $cat1_freq_w2p = log($cat1_freq_w2,2);
    $cat2_freq_w2p = log($cat2_freq_w2,2);
    $cat3_freq_w2p = log($cat3_freq_w2,2);
    $cat1_freq_c = pow(1/(1/(int)$global["cat1_count"]),$cat1_freq_w2p); // Coef to normalize to 1 if the frequency of the current cat 1 is the same as the average
    $cat2_freq_c = pow(1/(1/(int)$global["cat2_count"]),$cat2_freq_w2p); // same for cat 2
    $cat3_freq_c = pow(1/(1/(int)$global["cat3_count"]),$cat3_freq_w2p); // for cat 3
  
    $scat1["hits_avg"] = $scat1["hits"] / $scat1["pdt_count"];
    $scat1["leads_avg"] = $scat1["leads"] / $scat1["pdt_count"];
    $scat1["orders_avg"] = $scat1["orders"] / $scat1["pdt_count"];
    $scat1["hpd_avg"] = ($scat1["hits"] / $scat1["age_avg"] * 86400) / $scat1["pdt_count"];

    $scat2["hits_avg"] = $scat2["hits"] / $scat2["pdt_count"];
    $scat2["leads_avg"] = $scat2["leads"] / $scat2["pdt_count"];
    $scat2["orders_avg"] = $scat2["orders"] / $scat2["pdt_count"];
    $scat2["hpd_avg"] = ($scat2["hits"] / $scat2["age_avg"] * 86400) / $scat2["pdt_count"];

    $scat3["hits_avg"] = $scat3["hits"] / $scat3["pdt_count"];
    $scat3["leads_avg"] = $scat3["leads"] / $scat3["pdt_count"];
    $scat3["orders_avg"] = $scat3["orders"] / $scat3["pdt_count"];
    $scat3["hpd_avg"] = ($scat3["hits"] / $scat3["age_avg"] * 86400) / $scat3["pdt_count"];

    // Computation :
    // ----------------------------------------
    // We calculate an "average" hits/orders/... per product using a lot of weighted coefficient computed from
    // the whole db (global), and the relative size of the current cat1, cat2 and cat3
    // k0 k1 k2 k3 = arbitrary coef * computed relative size coef
    // for hits :
    $h_k0 = $global_w;
    $h_k1 = $cat1_w * $cat1_freq_c * pow($scat1["hits"]/$global["hits"], $cat1_freq_w2p);
    $h_k2 = $cat2_w * $cat2_freq_c * pow($scat2["hits"]/$global["hits"], $cat2_freq_w2p);
    $h_k3 = $cat3_w * $cat3_freq_c * pow($scat3["hits"]/$global["hits"], $cat3_freq_w2p);
    $final_hits_w_avg = ($h_k0*$global["hits_avg"] + $h_k1*$scat1["hits_avg"] + $h_k2*$scat2["hits_avg"] + $h_k3*$scat3["hits_avg"]) / ($h_k0+$h_k1+$h_k2+$h_k3); // Final Weighted average hits per product

    // for leads :
    $l_k0 = $global_w;
    $l_k1 = $cat1_w * $cat1_freq_c * pow($scat1["leads"]/$global["leads"], $cat1_freq_w2p);
    $l_k2 = $cat2_w * $cat2_freq_c * pow($scat2["leads"]/$global["leads"], $cat2_freq_w2p);
    $l_k3 = $cat3_w * $cat3_freq_c * pow($scat3["leads"]/$global["leads"], $cat3_freq_w2p);
    $final_leads_w_avg = ($l_k0*$global["leads_avg"] + $l_k1*$scat1["leads_avg"] + $l_k2*$scat2["leads_avg"] + $l_k3*$scat3["leads_avg"]) / ($l_k0+$l_k1+$l_k2+$l_k3); // Final Weighted average leads per product

    // for orders :
    $o_k0 = $global_w;
    $o_k1 = $cat1_w * $cat1_freq_c * pow($scat1["orders"]/$global["orders"], $cat1_freq_w2p);
    $o_k2 = $cat2_w * $cat2_freq_c * pow($scat2["orders"]/$global["orders"], $cat2_freq_w2p);
    $o_k3 = $cat3_w * $cat3_freq_c * pow($scat3["orders"]/$global["orders"], $cat3_freq_w2p);
    $final_orders_w_avg = ($o_k0*$global["orders_avg"] + $o_k1*$scat1["orders_avg"] + $o_k2*$scat2["orders_avg"] + $o_k3*$scat3["orders_avg"]) / ($o_k0+$o_k1+$o_k2+$o_k3); // Final Weighted average orders per product

    // for hits per day :
    $final_hpd_w_avg = ($h_k0*$global["hpd_avg"] + $h_k1*$scat1["hpd_avg"] + $h_k2*$scat2["hpd_avg"] + $h_k3*$scat3["hpd_avg"]) / ($h_k0+$h_k1+$h_k2+$h_k3); // Final Weighted average hitsPerDay per product

    // for hits to leads and hits to orders
    $final_hits2leads_w_freq = $final_hits_w_avg / $final_leads_w_avg;
    $final_hits2orders_w_freq = $final_hits_w_avg / $final_orders_w_avg;

    // Computing product selling score
    $max_score2 = 0;
    foreach ($pdtList["data"] as &$pdt) {
      $pdt["score2"] = ($pdt["hits2leads"] * $final_hits2leads_w_freq * $hits2leads_w + $pdt["hits2orders"] * $final_hits2orders_w_freq * $hits2orders_w)
        * pow($pdt["hits"]/$final_hits_w_avg, $hits_freq_w2p)
        * pow($pdt["hpd"]/$final_hpd_w_avg, $hitsPerDay_freq_w2p);
      $max_score2 = max($max_score2, $pdt["score2"]);
    }
    unset($pdt);

    
    // Assigning the top score2 to preselected products
    for ($i = 0; $i < $pdt_selection_count; $i++) {
      if (isset($pdt_infos[$pdt_selection[$i]]))
        $pdt_infos[$pdt_selection[$i]]["score2"] = $max_score2+1;
    }
    
    $mts["STATS COMPUTING"]["end"] = microtime(true);
    

    
  // Sorting
    switch ($sort) {
      /*case "updated"    : Utils::sortDbInPlace($pdtList["data"], "timestamp", SORT_DESC, SORT_NUMERIC, "name", SORT_ASC, SORT_STRING); break;
      case "view"       : Utils::sortDbInPlace($pdtList["data"], "hits", SORT_DESC, SORT_NUMERIC, "name", SORT_ASC, SORT_STRING); break;
      case "lead"       : Utils::sortDbInPlace($pdtList["data"], "leads", SORT_DESC, SORT_NUMERIC, "name", SORT_ASC, SORT_STRING); break;
      case "price-asc"  : Utils::sortDbInPlace($pdtList["data"], "price", SORT_ASC, SORT_NUMERIC, "name", SORT_ASC, SORT_STRING); break;
      case "price-desc" : Utils::sortDbInPlace($pdtList["data"], "price", SORT_DESC, SORT_NUMERIC, "name", SORT_ASC, SORT_STRING); break;
      case "relevant"   :*/
      default :
        $sort = "";
        Utils::sortDbInPlace($pdtList["data"], "score2", SORT_DESC, SORT_NUMERIC, "name", SORT_ASC, SORT_STRING);
        break;
    }

    
    ob_start();
    

    /*
    $query_text_content = $db->query('select name,text_content from families_fr where id = '.$cat3Id);
    $text_content = $db->fetchAssoc($query_text_content);
    if(!empty ($text_content['name'])) echo '<h1 id="title_cat3">'.$text_content['name'].'</h1>';
    if(!empty ($text_content['text_content'])) echo '<div id="text_desc_cat3">'.nl2br($text_content['text_content']).'</div>';
    */

  }
  else {
    ob_start();
?>
  Aucun produit ne correspond aux critères sélectionnés
<?php
  }
  if ($standalone) {
    ob_end_flush();
  }
  else {
    $pdtListHTML = ob_get_clean();
    ob_end_clean();
  }





  // wether we show the product type block or not
  $showPdtTypeBlock = true;
  if ($pdtList["saleableCount"] == 0 || $pdtList["totalCount"] == $pdtList["saleableCount"]
    || $pdtList["advCatCount"][__ADV_CAT_SUPPLIER__] == 0 || $pdtList["totalCount"] == $pdtList["advCatCount"][__ADV_CAT_SUPPLIER__])
  $showPdtTypeBlock = false;
  
  // Bayesian average of every products
  $bayesianAvg = (700 * TYPICAL_DATA_SET_SIZE + $pdtList["priceSum"]) / ($pdtList["priceCount"] + 10);
  
  if ($pdtList["priceCount"] > 5) {
    $priceBayesMean = round($bayesianAvg, -2);
    $priceMin = floor($pdtList["priceMin"]);
    if ($priceMin % 100 != 0)
      $priceMin = $priceMin - $priceMin % 100;
    $priceMax = ceil($pdtList["priceMax"]);
    if ($priceMax % 100 != 0)
      $priceMax = $priceMax - $priceMax % 100 + 100;

    $priceRange1 = ($priceBayesMean + $priceMin) / 2;
    $priceRange2 = ($priceBayesMean + $priceMax) / 2;
  }


  
switch($catTree->length) {

    
  case 3 : // Level 3 category
/*
require(INCLUDES_PATH.'fpdf/makefont/makefont.php');
MakeFont('georgia.ttf', 'ISO-8859-15');
//MakeFont(ttf_file($f), $f);
die();
*/


if ($results["count"] > 0) { 

  $pdf = new PDFCatalogModel();
  
  $pdf->SetAutoPageBreak(true, 45, 50);
  
  $pdf->addFont('Georgia', '', 'georgia.php');
  $pdf->addFont('Georgia', 'B', 'georgiab.php');
  $pdf->addFont('Georgia', 'I', 'georgiai.php');
  $pdf->addFont('Georgia', 'Z', 'georgiaz.php');
    
  //$pdf->SetAutoPageBreak(false);
// hides header and footer for front page
  $pdf->showHeader = false;
  $pdf->showFooter = false;
  $pdf->AddPage();
  
  $bx = $x = round($pdf->getX(),1); // default margin
  $by = round($pdf->GetY(),1); // base y
  $pdf->FrontHeader();
  $pdf->SetY($pdf->GetY()+100);
  $pdf->SetX($pdf->GetX()+70);
  $pdf->writeFrontBlueTitle(utf8_decode($cat3->getAttribute("name"))); //$cat3->getAttribute("name")
  $pdf->SetX($pdf->GetX()+70);
  $pdf->writeFrontGreyTitle("Notre catalogue");
  $pdf->SetY($pdf->GetY()+100);
  $pdf->SetX($pdf->GetX()+70);
  $pdf->writeFrontAddressBottom("www.techni-contact.com");
  $pdf->SetX($pdf->GetX()+70);
  $pdf->writeFrontAddressBottom("Tél : 01 55 60 29 29");
  $pdf->SetX($pdf->GetX()+70);
  $pdf->writeFrontAddressBottom("Fax : 01 83 62 32 12");
  $pdf->Image(SECURE_PATH.'ressources/images/catalogue_pdf_front_footer.jpg',28,260,160);
   //$pdf->SetFont('Georgia','B',9);
  
  $pdf->nomFamille3 = utf8_decode($cat3->getAttribute("name"));
   //$pdf->SetTextColor(0, 113, 188);
    
    // nettoyage des descriptions
    $array_search = array('&rsquo;');
    $array_replace = array("'");
    
    $productt = &$pdtList["data"];
     foreach($productt as $pdt){
     
     
     
     $res2 = $db->query("
      SELECT id, label, content, refSupplier, price+ecotax AS price, price2, idTVA, unite
      FROM references_content
      WHERE idProduct = ".$pdt["id"]." AND vpc = 1 AND deleted = 0
      ORDER BY classement", __FILE__, __LINE__);
    while ($ref = $db->fetchAssoc($res2)) {
      $ref["content"] = mb_unserialize($ref["content"]);
      $ref["cart_add_url"] = "panier:".$pdt["cat3_id"]."-".$pdt["id"]."-".$ref["id"];
      if ($ref["price2"] > 0 && $max_margin < $ref["price"]/$ref["price2"])
        $max_margin = $ref["price"]/$ref["price2"];
      //pp($ref);
      $pdt["refs"][] = $ref;
      }
      
      $res3 = $db->query("
        SELECT content
        FROM references_cols
        WHERE idProduct = ".$pdt["id"], __FILE__, __LINE__);
      list($content_cols) = $db->fetch($res3);
      $content_cols = mb_unserialize($content_cols);
      if($content_cols !== false)
        $pdt["refs_headers"] = array_slice($content_cols, 3, -5);
      else
        $pdt["refs_headers"] = array();
      //pp($pdt);
        
        
        $pdf->AddPage();
        $y = 0;
        $pdf->SetFont('Georgia','',18);
        $pdf->SetTextColor(0, 113, 188);
        $pdf->SetY($y+42);
        $pdf->SetX($x);
        $pdf->Cell(120, 10, utf8_decode($pdt['name']),0);
        $pdf->SetFont('Georgia','',13);
        $pdf->SetY($y+52);
        $pdf->SetX($x);
        $pdf->MultiCell(120, 5, utf8_decode($pdt['fastdesc']),0);
        $pdf->SetFont('Arial','I',10);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetY($y+68);
        $pdf->SetX($x);
        $pdf->Cell(120, 10, 'Code produit sur www.techni-contact.com : '.$pdt['id'],0);
        //images/contact-man.png
        $l = 41;
        $h = 11;
        $contactX = $x+120;
        $contactY = $y+20;
        $pdf->SetXY($contactX, $contactY);
        $pdf->SetFillColor(247,247,247);
        $pdf->SetTextColor(247,247,247);
        // MultiCell($w, $h, $txt, $border=0, $align='J', $fill=false)
        $pdf->MultiCell($l*2,$h*2,'O',0,'L', true);
        $pdf->SetTextColor(0, 113, 188);
        $pdf->SetFont('Arial','',10);
        $pdf->SetXY($contactX, $contactY);
        $pdf->MultiCell($l,5,'Contactez notre expert',0,'L', true);
        $pdf->SetTextColor(102, 102, 102);
        $pdf->SetXY($contactX, $contactY+$h);
        		
		$sql_tel_cat  = "SELECT tel_familiies_catalogs,name FROM bo_users WHERE id='".$pdt['id_bo_usr']."'";
		$req_tel_cat  =  mysql_query($sql_tel_cat);
		$data_tel_cat =  mysql_fetch_object($req_tel_cat);
		// echo __ADV_CAT_SUPPLIER__.'<br />';
		if(!empty($data_tel_cat->tel_familiies_catalogs)){
			if($pdt['adv_cat'] != 1){
			$pdf->MultiCell($l,5,'Demande de devis au '.$data_tel_cat->tel_familiies_catalogs,0,'L', true);
			}else {
			$pdf->MultiCell($l,5,utf8_decode($data_tel_cat->name).' au '.$data_tel_cat->tel_familiies_catalogs,0,'L', true);
			}
			// $pdf->MultiCell($l,5,($pdt['adv_cat'] != __ADV_CAT_SUPPLIER__ ? utf8_decode('Demande de devis au 01.72.08.01.14'): utf8_decode($pdt['bou_name']).(!empty($data_tel_cat->tel_familiies_catalogs) ? ' au '.$data_tel_cat->tel_familiies_catalogs : '')),0,'L', true);
		}else{
			
			$pdf->MultiCell($l,5,($pdt['adv_cat'] != __ADV_CAT_SUPPLIER__ ? utf8_decode('Demande de devis au 01.72.08.01.14'): utf8_decode($pdt['bou_name']).(!empty($pdt['bou_phone']) ? ' au '.$pdt['bou_phone'] : '')),0,'L', true);
        }
		$pdf->SetXY($contactX+$l+5, $contactY);
        $pdf->Image(SECURE_PATH.'ressources/images/catalogue_pdf_contact-man.png');
        
        // image produit
        $pdf->SetXY($x, $y+80);
        $size = getimagesize(SECURE_PATH.$pdt['pic_url']);
        $largeur=$size[0];
        $hauteur=$size[1];
        $ratio=60/$hauteur;	//hauteur imposée de 120mm
        $newlargeur=$largeur*$ratio;
        $posi=(80-$newlargeur)/2;	//210mm = largeur de page
//$pdf->image($image, $posi, 0,120);
        $pdf->Cell(80,60,'',1,0,'C',$pdf->Image(SECURE_PATH.$pdt['pic_url'], $x+$posi, $y+80,0,60));
        
        // zone prix
        $pdf->SetXY($x+90, $y+101);
        // orange #D55421 $pdf->SetTextColor(213, 84, 33);
        //$pdf->SetTextColor(212, 20, 90);// rose #D4145A 
        
        $price_text = '';
        // produits de partenaires non fournisseurs
        if($pdt['adv_cat'] != 1){
          if(is_numeric($pdt['price']) && $pdt['price'] != 0){ // on a un montant renseigné -> prix indicatif
            $pdf->SetTextColor(212, 20, 90);
            $pdf->SetFont('Georgia','',15);
            $pdf->Cell(30, 5, 'Prix indicatif : ');
            $pdf->SetXY($x+130, $y+100);
            $price_text = sprintf('%.02f',$pdt['price'])." ".chr(164);
          }else{ // montant à 0 ou texte -> sur devis
            $pdf->SetTextColor(213, 84, 33);
            $price_text = 'Sur devis';
          }
        }else{ // produits de partenaires fournisseurs
          if(is_numeric($pdt['price']) && $pdt['price'] != 0 && $pdt['product_as_estimate'] != 1 && $pdt['nb_refs'] != 0 ){ // le produit fournisseur a un montant non nul, et un tableau prix renseigné
            $pdf->SetTextColor(212, 20, 90);
            if($pdt['nb_refs'] > 1){ // références multiples -> à partir de
              $pdf->SetFont('Georgia','',15);
              $pdf->Cell(30, 5, 'à partir de');
              $pdf->SetXY($x+120, $y+100);
              $price_text = sprintf('%.02f',$pdt['price'])." ".chr(164);
            }else{ // référence unique -> prix
              $price_text = sprintf('%.02f',$pdt['price'])." ".chr(164);
            }
          }else{ // montant à 0 ou texte ou fournisseur paramétré sur devis ou tableau prix vide-> sur devis
            $pdf->SetTextColor(213, 84, 33);
            $price_text = 'Sur devis';
          }
        }
        

        $pdf->SetFont('Georgia','',20);
        $pdf->Cell(60, 5, $price_text,0); // .'   '.$pdt['adv_cat'].'   '.$pdt['price'].'   '.$pdt['nb_refs'].'   '.$pdt['product_as_estimate']
        
        
         if(!empty($pdt['delivery_time']) && $pdt['adv_cat'] == 1){
            $pdf->SetXY($x+90, $y+109);
            $pdf->SetFont('Arial','',11);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Cell(80, 5, 'Expédition : '.utf8_decode($pdt['delivery_time']),0);
          }
        
        //pp($pdt);
        $pdf->SetFont('Arial','',12);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY($x, $y+145);
        
        // descriptions
        $pdf->AcceptPageBreak();
        $pdf->nextPageTopMargin = 30;
        $pdf->MultiCell(190, 5, str_replace($array_search, $array_replace, strip_tags(html_entity_decode($pdt['descc'], ENT_QUOTES, 'ISO-8859-15'))), 0);
        
        
        // tableau ref
        if($pdt['nb_refs'] > 0){
          $y = $pdf->GetY();
          $modeleText = $pdt['nb_refs'] == 1 ? 'Modèle disponible' : 'Modèles disponibles';
          
          $pdf->SetFont('Georgia','',15);
          $pdf->SetTextColor(0, 113, 188);
          $pdf->SetY($y+5);
          $pdf->nextPageTopMargin = 30;
          $pdf->Cell(80,5,$modeleText,0);
          
          
          $rf_tabX = 10;
          $pdf->GetY($y)+15;
          $rf_tabY = $y+15; // limite d'explosion du tableau = 232
          $rf_tabY = $rf_tabY > 232 ? $rf_tabY-232 : $rf_tabY;
          if($rf_tabY<30){
            $pdf->AddPage();
            $rf_tabY = 30;
          }
          $rf_tabCellW = 40; // largeur de cellule
          
          $pdf->SetFont('Arial','',10);
          $pdf->SetTextColor(0, 0, 0);
          
          // creation de l'entete du tableau prix
          $pdf->SetXY($rf_tabX, $rf_tabY);
          
          $col_len = 190/(count($pdt['refs_headers'])+3);
          
          foreach ($pdt['refs_headers'] as $key_header => $pdt_ref_header)
            $header_height[$key_header] = $pdf->GetNbLines($col_len, 5, utf8_decode($pdt_ref_header), 0, $align='C', $fill=false);
          
          $HH = max($header_height);
          
          $pdf->SetLineWidth(0.1);
          $pdf->SetFillColor(176,176,176);
          $pdf->SetXY($rf_tabX, $rf_tabY);
          $pdf->MultiCell($col_len,$HH*5,"Réf. TC",1,"C",1);
          $pdf->SetXY( $col_len+$rf_tabX,$rf_tabY);
          $pdf->MultiCell($col_len,$HH*5,"Libellé",1,"C",1);
          $a=2;
          foreach ($pdt['refs_headers'] as $pdt_ref_header){
            $pdf->SetXY( $col_len*$a+$rf_tabX,$rf_tabY);
            $pdf->MultiCell($col_len,($HH*5)/$header_height[$a-2],utf8_decode($pdt_ref_header),1,"C",1);
            $a++;
          }
          $pdf->SetXY( $col_len*$a+$rf_tabX,$rf_tabY);
          $pdf->MultiCell($col_len,$HH*5,"Prix HT",1,"C",1);
          
          $pdf->SetFillColor(255,255,255);
          $data_refs = array();
          //pp($pdt); // traitement des références
          foreach($pdt['refs'] as $key => $ref){
            
            $data_refs[$key][] = $ref['id'];
            $data_refs[$key][] = $ref['label'];
            foreach($ref['content'] as $content)
              $data_refs[$key][] = $content;
            $data_refs[$key][] = $ref['price'];
          }
          //var_dump('tptp');
          //pp($data_refs);
          
          $y = $rf_tabY; //
          //$pdf->Cell(60, 5,$rf_tabY.'+'.$HH.'='.$y);
          $previousLineHeight = null;
          foreach ($data_refs as  $ligne){
          
            
            foreach($ligne as $k => $content)
              $line_height[$k] = $pdf->GetNbLines($col_len, 5, utf8_decode($content), 0, $align='C', $fill=false);
            
            $LH = max($line_height);
            
         // $pdf->SetY($rf_tabY+($LH*5));
          $y = $previousLineHeight != null ? $y+($previousLineHeight*5) : $y+($HH*5);
          if($y > 232){
            $pdf->AddPage();
            $y = 30;
            }
            foreach ($ligne as $l => $content){
              //$pdf->SetXY( $col_len*($l)+$rf_tabX,($LH*5)/$line_height[$l]);
              $pdf->SetXY( $col_len*($l)+$rf_tabX,$y);
              if($l == $k){ // la dernière info est le prix
                $content = $pdt['product_as_estimate'] != 1 ? sprintf($content)." ".chr(128) : 'Sur devis';
              }else
                $content = utf8_decode($content);
              $pdf->MultiCell($col_len,($LH*5)/$line_height[$l],$content,1,"C",1);
              
            }
            $previousLineHeight = $LH;
          }                    
        }       
     }    
  $pdf->Output("Catalogue Techni-Contact - ".utf8_decode($cat3->getAttribute("name")).".pdf", $dl?'D':'I');   
    ?>
<?php } ?>
<?php
    break;
    
  default :
    header("Location: ".URL);
    exit();
}

?>


