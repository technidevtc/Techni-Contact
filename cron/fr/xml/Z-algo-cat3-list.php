<?php
  //if ($standalone = $_SERVER["SCRIPT_NAME"] == "/".basename(__FILE__)) {
    require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),"/",stripos(dirname(__FILE__),"technico")+1)+1)."config.php";
    
    header("content-type: text/html; charset=utf-8");
    //$curCategory = $_REQUEST["cat3Id"];

    $db = DBHandle::get_instance();
    $session = new UserSession($db);

    // Loading XML
    $dom = new DomDocument();
    $dom->validateOnParse = true;
    $dom->load(XML_CATEGORIES_ALL);
    $xPath = new DOMXPath($dom);
    $listCat3 = $xPath->query("//categories/category/category/category");//$listCat3 = array(2230);//
    foreach ($listCat3 as $Cat3){
     $curCategory = $Cat3->getAttribute("id");//$curCategory = 2230;//

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
  //}
  
  $cat3Id = $cat3->getAttribute("id");
  
  $results = array("data" => array(), "count" => 0);
  
//  define("NB", 50);
  
  $page    = !empty($_REQUEST["page"])    ? (int)(trim($_REQUEST["page"])) : 1;
  $sort    = !empty($_REQUEST["sort"])    ? trim($_REQUEST["sort"]) : "";
  $filters = !empty($_REQUEST["filters"]) ? $_REQUEST["filters"] : array();
  
  $validFilters = array();
  foreach ($filters as $filterName => $filterArg) {
    switch ($filterName) {
      case "all":
     /* case "saleable":
      case "partner": $validFilters[$filterName] = true; break;
      case "price":
        if (!empty($filterArg))
          foreach($filterArg as $arg => $v)
            if (preg_match("/^[0-9]+-[0-9]+$/", $arg))
              $validFilters[$filterName][] = explode("-", $arg);
        break;
      case "attrVal":
        if (!empty($filterArg))
          foreach($filterArg as $arg => $v)
            if (preg_match("/^[1-9]{1}[0-9]*$/", $arg))
              $validFilters[$filterName][] = $arg;
        break;
      default : break;
      case "intervVal":
        if (!empty($filterArg))
          foreach($filterArg as $arg => $v)
            if (preg_match("/^[1-9]{1}[0-9]*$/", $arg))
              $validFilters[$filterName][] = $arg;
        break;
      case "virtAttrVal":
        if (!empty($filterArg))
          foreach($filterArg as $arg => $v)
            if (preg_match("/^[1-9]{1}[0-9]*$/", $arg))
              $validFilters[$filterName][] = $arg;
        break;*/

      default : break;
    }
  }
  
  $joins = $where = $having = array();
  /*if ($standalone) {
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
  }*/

  $res = $db->query("
    SELECT
      p.id,
      IFNULL(rc.price, p.price) AS price,
      p.timestamp,
      p.shipping_fee,
      pfr.name,
      pfr.ref_name,
      pfr.fastdesc,
      pfr.delai_livraison AS delivery_time,
      pf.idFamily AS catID,
      ps.hits,
      ps.orders,
      ps.leads,
      (".mktime(0,0,0)."- ps.first_hit_time) AS age,
      a.id AS adv_id,
      a.nom1 AS adv_name,
      a.category AS adv_cat,
      a.delai_livraison AS adv_delivery_time,
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
    LEFT JOIN references_content rc ON p.id = rc.idProduct AND rc.classement = 1
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
        $pdt["pic_url"] = is_file(PRODUCTS_IMAGE_INC."thumb_big/".$pdt["id"]."-1".".jpg") ? PRODUCTS_IMAGE_URL."thumb_big/".$pdt["id"]."-1".".jpg" : PRODUCTS_IMAGE_URL."no-pic-thumb_big.gif";;
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

      $q = Doctrine_Query::create()
      ->update ('Products')
      ->set('score_algo_cat3','?', $pdt["score2"])
      ->where('id = ?',$pdt['id']);
      //echo $q->getSqlQuery();
      $q->execute();
      //echo '<br />';
    }
    unset($pdt);

  }
  else {
   // ob_start();

  echo 'Aucun produit ne correspond aux critères sélectionnés';

  }
  
  }
  if ($standalone) {
   // ob_end_flush();
  }
  else {
   // $pdtListHTML = ob_get_clean();
   // ob_end_clean();
  }
