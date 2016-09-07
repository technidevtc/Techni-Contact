<?php
  if ($standalone = $_SERVER["SCRIPT_NAME"] == "/".basename(__FILE__)) {

    // same code as in categories.html in the case of a cat3

    require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

    header("content-type: text/html; charset=utf-8");

    $curCategory = $_REQUEST["cat3Id"];

    $db = DBHandle::get_instance();
    $session = new UserSession($db);

    // Loading XML
    $dom = Families::getDomXML();
    $xPath = Families::getXPathXML();

    $curCatDOMNode = $dom->getElementById(XML_KEY_PREFIX.$curCategory);
    if (!$curCatDOMNode)
      exit();

    $catTree = $xPath->query("ancestor-or-self::category",$curCatDOMNode);

    // Globals stats

    // cat1, cat2 and cat3 vars
    $xmlCat1 = $catTree->item(0);
    $cat1 = array(
      'id' => (int)$xmlCat1->getAttribute('id'),
      'name' => $xmlCat1->getAttribute('name'),
      'ref_name' => $xmlCat1->getAttribute('ref_name')
    );
    $xmlCat2 = $catTree->item(1);
    $cat2 = array(
      'id' => (int)$xmlCat2->getAttribute('id'),
      'name' => $xmlCat2->getAttribute('name'),
      'ref_name' => $xmlCat2->getAttribute('ref_name')
    );
    $xmlCat3 = $catTree->item(2);
    $cat3 = array(
      'id' => (int)$xmlCat3->getAttribute('id'),
      'name' => $xmlCat3->getAttribute('name'),
      'ref_name' => $xmlCat3->getAttribute('ref_name')
    );
    $xmlCurCat = $xmlCat3;
    $curCat = $cat3;

    $pdt_infos = array();
  }

  $pdt_selection = array();
  $pdt_selection_node = $xPath->query("child::pdt_selection", $xmlCurCat);
  if ($pdt_selection_node->length >= 1) {
    $pdt_selection = explode("|",$pdt_selection_node->item(0)->nodeValue);
  }
  $pdt_selection_count = count($pdt_selection);

  $results = array("data" => array(), "count" => 0);

  $page    = isset($_REQUEST["page"])     ? $_REQUEST["page"] : null;
  $sort    = !empty($_REQUEST["sort"])    ? trim($_REQUEST["sort"]) : "";
  $filters = !empty($_REQUEST["filters"]) ? $_REQUEST["filters"] : array();

  $validFilters = array();
  foreach ($filters as $filterName => $filterArg) {
    switch ($filterName) {
      case "all":
      case "saleable":
      case "partner": $validFilters[$filterName] = true; break;
      case "price":
        if (!empty($filterArg))
          foreach($filterArg as $arg => $v)
            if (preg_match("/^[0-9]+_[0-9]+$/", $arg))
              $validFilters[$filterName][] = explode('_', $arg);
        break;
      case "facet":
        if (!empty($filterArg))
          foreach($filterArg as $arg => $v)
            if (preg_match("/^([a-z0-9-]+)_([a-z0-9-]+)$/i", $arg, $matches))
              $validFilters[$filterName][$matches[1]][] = $matches[2];
        break;
      default : break;
    }
  }

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
  }

  if ($validFilters['facet']) {
    $q = Doctrine_Query::create()
      ->select('fc.*, fcl.*, a.id, p.id, pa.value, pra.value')
      ->from('Facet fc')
      ->innerJoin('fc.lines fcl WITH fcl.active = 1')
      ->innerJoin('fc.attribute a')
      ->innerJoin('a.product_attributes pa')
      ->leftJoin('pa.product_reference_attributes pra')
      ->innerJoin('pa.product p')
      ->innerJoin('p.product_fr pfr WITH pfr.active = 1 AND pfr.deleted = 0')
      ->innerJoin('p.advertiser adv WITH adv.actif = 1')
      ->innerJoin('p.families f WITH f.id = fc.family_id')
      ->where('fc.active = 1')
      ->andWhere('fc.family_id = ?', $cat3['id']);

    $facetFilterWhere = [];
    $facetFilterValues = [];
    foreach ($validFilters['facet'] as $facetName => $facetValues) {
      $facetFilterWhere[] = '(fc.ref_title = ? AND fcl.ref_value IN ('.implode(',', array_fill(0, count($facetValues), '?')).'))';
      $facetFilterValues[] = $facetName;
      $facetFilterValues = array_merge($facetFilterValues, $facetValues);
    }

    $q->andWhere(implode(' OR ', $facetFilterWhere), $facetFilterValues);

    $facets = $q->fetchArray();

    $facetPdtIdList = [];
    foreach ($facets as $facet) {
      foreach ($facet['lines'] as $facetLine) {
        foreach ($facet['attribute']['product_attributes'] as $pa) {
          if (!empty($pa['product_reference_attributes'])) {
            foreach ($pa['product_reference_attributes'] as $pra) {
              if (($facetLine['type'] == FacetLine::TYPE_VALUE && $pra['value'] == $facetLine['value'])
                  || ($facetLine['type'] == FacetLine::TYPE_INTERVAL && $pra['value'] >= $facetLine['start'] && $pra['value'] <= $facetLine['end'])) {
                $facetPdtIdList[$pa['product']['id']] = true;
                break;
              }
            }
          } elseif (($facetLine['type'] == FacetLine::TYPE_VALUE && $pa['value'] == $facetLine['value'])
                    || ($facetLine['type'] == FacetLine::TYPE_INTERVAL && $pa['value'] >= $facetLine['start'] && $pa['value'] <= $facetLine['end'])) {
            $facetPdtIdList[$pa['product']['id']] = true;
          }
        }
      }
    }
    if (count($facetPdtIdList) > 0) {
      $where[] = 'p.id IN ('.implode(',', array_keys($facetPdtIdList)).')';
    } else {
      if ($standalone)
        $where[] = '0';
      else
        header("Location: ".Utils::get_family_fo_url($cat3['ref_name']), true, 301);
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
      pfr.descc,
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
      a.idCommercial as adv_comm_id,
      rc.id AS ref_idtc,
      rc.refSupplier AS ref_refSupplier,
      (IFNULL(rc.price, p.price) REGEXP '^[1-9]{1}[0-9]*((\\.|,)[0-9]+){0,1}$') AS hasPrice,
      (IFNULL(rc.price, p.price) REGEXP '^[1-9]{1}[0-9]*((\\.|,)[0-9]+){0,1}$' AND a.category = ".__ADV_CAT_SUPPLIER__.") AS saleable,
      (p.as_estimate + a.as_estimate) as product_as_estimate,
      (select count(idProduct) from references_content where idProduct = p.id) as nb_refs
    FROM products p
    INNER JOIN products_fr pfr ON p.id = pfr.id AND pfr.active = 1 AND pfr.deleted != 1
    INNER JOIN products_families pf ON p.id = pf.idProduct AND pf.idFamily = ".$cat3['id']."
    INNER JOIN products_stats ps ON p.id = ps.id
    INNER JOIN advertisers a ON p.idAdvertiser = a.id AND a.actif = 1
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
		// reset($comm_pdt_count);
      $pdt_referent_commercial_id = key($comm_pdt_count);
    }
	
	//$pdt_referent_commercial_id = "55215";
	 
	
	
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
        $pdt["pic_url"] = is_file(PRODUCTS_IMAGE_INC."thumb_big/".$pdt["id"]."-1".".jpg") ? PRODUCTS_IMAGE_URL."thumb_big/".$pdt["ref_name"].'-'.$pdt["id"]."-1".".jpg" : PRODUCTS_IMAGE_URL."no-pic-thumb_big.gif";;
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
    $tab_microtime["PRODUCTS LIST"] = microtime(true);

    // get score 2 (or product's performance score)
    Products::setProductsPerformanceScore($pdtList['data']);

    $max_score2 = 0;
    foreach ($pdtList['data'] as $pdt)
      if ($max_score2 < $pdt['score2'])
        $max_score2 = $pdt['score2'];

    // Assigning the top score2 to preselected products
    for ($i = 0; $i < $pdt_selection_count; $i++) {
      if (isset($pdt_infos[$pdt_selection[$i]]))
        $pdt_infos[$pdt_selection[$i]]["score2"] = $max_score2+1;
    }

    $tab_microtime["STATS COMPUTING"] = microtime(true);

  // Page calculations
    $maxpage = floor((($pdtList["count"]-1) - ($pdtList["count"]-1)%CAT3_PDT_COUNT_PER_PAGE) / CAT3_PDT_COUNT_PER_PAGE) + 1;
    if ($standalone || !isset($page)) {
      if (!is_numeric($page)) $page = 1;
      if ($page < 1) $page = 1;
      if ($page > $maxpage) $page = $maxpage;
    }
    elseif ($page < 2 || $page > $maxpage) {
      require("404.php");
      exit();
    }

  // Items to show
    $startItemIndex = ($page-1) * CAT3_PDT_COUNT_PER_PAGE;
    $endItemIndex = min($page * CAT3_PDT_COUNT_PER_PAGE, $pdtList["count"]) - 1;

  // Sorting
    switch ($sort) {
      case "updated"    : Utils::sortDbInPlace($pdtList["data"], "timestamp", SORT_DESC, SORT_NUMERIC, "name", SORT_ASC, SORT_STRING); break;
      case "view"       : Utils::sortDbInPlace($pdtList["data"], "hits", SORT_DESC, SORT_NUMERIC, "name", SORT_ASC, SORT_STRING); break;
      case "lead"       : Utils::sortDbInPlace($pdtList["data"], "leads", SORT_DESC, SORT_NUMERIC, "name", SORT_ASC, SORT_STRING); break;
      case "price-asc"  : Utils::sortDbInPlace($pdtList["data"], "price", SORT_ASC, SORT_NUMERIC, "name", SORT_ASC, SORT_STRING); break;
      case "price-desc" : Utils::sortDbInPlace($pdtList["data"], "price", SORT_DESC, SORT_NUMERIC, "name", SORT_ASC, SORT_STRING); break;
      case "relevant"   :
      default :
        $sort = "";
        Utils::sortDbInPlace($pdtList["data"], "score2", SORT_DESC, SORT_NUMERIC, "name", SORT_ASC, SORT_STRING);
        break;
    }

    // getting notation and commentaries
   require 'star-rater.php';
    foreach ($pdtList["data"] as &$pdt) {
      $notations = ProductNotation::get('id_product = '.$pdt['id'], 'inactive = 0');
      if(!empty ($notations)){
        $a=0;
        foreach ($notations as $notation){
          $sumNote += $notation['note'];
          $a++;
        }
        $pdt['average_note'] = round($sumNote/$a);
        $pdt['nb_comments'] = $a;
      }
    }
    unset($pdt);

    ob_start();

    // print '<pre style="font-size: 10px">';
    // print_r($facets);
    // print '</pre>';

    function getFamilyIntroText($familyId) {
      $dbh = Doctrine_Manager::connection()->getDbh();
      $sth = $dbh->query('SELECT text_content FROM families_fr WHERE id = '.$familyId);
      $row = $sth->fetch(PDO::FETCH_ASSOC);
      return $row['text_content'];
    }

    // criteo first 3 products
    $criteo = array('pdt_ids' => array());
    for ($k=$startItemIndex, $l=min($startItemIndex+2,$endItemIndex); $k<=$l; $k++)
      $criteo['pdt_ids'][] = $pdtList['data'][$k]['id'];

    // specific case when we access the facet value directly via url
    if (!$standalone && count($facets) == 1 && count($facets[0]['lines'] == 1)) {
      $facet = $facets[0];
      $facetLine = $facet['lines'][0];

      $title_cat3 = $facetLine['tag_title'];
      if (empty($title_cat3)) {
        $title_cat3 = $cat3['name'].' '.$facet['title'];
        if ($facetLine['type'] == FacetLine::TYPE_INTERVAL)
          $title_cat3 .= ' de '.$facetLine['start'].' à '.$facetLine['end'];
        else
          $title_cat3 .= ' '.$facetLine['value'];
        if (isset($facetLine['attribute_unit']))
          $title_cat3 .= ' '.$facetLine['attribute_unit']['name'];
      }

      $cc_title = $facetLine['meta_title'];
      if (empty($cc_title))
        $cc_title = $title_cat3.' - Techni-Contact';

      $cc_meta_desc = $facetLine['meta_desc'];
      if (empty($cc_meta_desc)) {
        $cc_meta_desc = "Vente et devis sur Techni-Contact de $title_cat3 : ";
        $shortPdtData = array_slice($pdtList['data'], 0, 3);
        $pdtNames = [];
        foreach ($shortPdtData as $pdtNum => $pdt)
          $pdtNames[] = $pdt['name'];
        $cc_meta_desc .= implode(', ', $pdtNames);
      }

      $text_content = $facetLine['intro_text'];
      // if (empty($text_content))
      //   $text_content = getFamilyIntroText($cat3['id']);

      $cat3Url = Utils::get_family_fo_url($cat3['ref_name'], $facet['ref_title'], $facetLine['ref_value']);
    } else {
      $title_cat3 = $cat3['name'];
      $text_content = getFamilyIntroText($cat3['id']);
      $cat3Url = Utils::get_family_fo_url($cat3['ref_name']);
    }
	
	// recuperer les 4 premiers phrase 
    if (!empty($text_content)) {
      $paragraphs = preg_split('/\s*\.\s*/', $text_content);
      $paragraphs = array_slice($paragraphs, 0, 4);
      $text_content2 = implode('. ', $paragraphs).'.';
    }

?>


<h1 id="title_cat3"><?php echo $title_cat3 ?></h1>
<?php if (!empty($text_content2)) : ?>
  <div id="text_desc_cat3">
    <?php echo $text_content2 ?>
  </div>
<?php endif ?>

<a <?php if(!TEST): ?>onClick="_gaq.push(['_trackEvent', 'Famille 3', 'Catalogues PDF', '<?php echo $cat3['ref_name'];?>']);" <?php endif; ?>href="<?php echo PDF_URL."catalogue-famille.php?category=".$cat3['ref_name']; ?>" target="_blank" class="cat3-pdf-catalog <?php echo (!empty ($text_content['text_content'])? 'fr' : 'fl') ?>">
  <div class="fl">Télécharger le catalogue</div>
  <img src="<?php echo URL; ?>ressources/images/dwnd-pdf-icon.png" />
 
  <div class="zero"></div>
</a>
  <?php
  $sql_check_f3 = "SELECT glf.id, glf.id_guide , ga.ref_name
                   FROM guides_linked_familles  glf, guides_achat ga
                   WHERE glf.id_guide = ga.id
                   AND id_familles_three='".$cat3['id']."' ";
  $req_check_f3  =  mysql_query($sql_check_f3);
  $rows_check_f3 =  mysql_num_rows($req_check_f3);
  //echo $sql_check_f3;
  if($rows_check_f3 > 0){ 
  $data_check_f3 = mysql_fetch_object($req_check_f3); 
   ?>
  <div class="lien_guide">
    <a href="<?= URL ?>guides-achat/<?= $data_check_f3->id_guide ?>-<?= $data_check_f3->ref_name ?>.html" <?php if(!TEST): ?> onclick="ga('send', 'event', 'Familles 3', 'Lien vers guide d'achat', '<?= $cat3['name'] ?>');"<?php endif; ?>> Guide d'achat <img src="<?= URL ?>ressources/images/buying-guide-picto-2.jpg" class="img-guide-picto" /></a>
  </div>
  <?php } ?>


<div class="zero"></div>


<div id="cat3-pagination-block">
  <div class="cat3-pdt-count"><?php echo $pdtList["count"];?> produit<?php echo $pdtList["count"] != 1 ? 's': '';?></div>

  <?php if ($showPriceSortBlock) { ?>
    <div class="filter-sort">
      Trier par : <select>
        <option value=""></option>
        <option value="+_sort_price-asc" <?php echo ($sort=="price-asc"?'selected="selected"':"") ?>>Prix - / +</option>
        <option value="+_sort_price-desc"<?php echo ($sort=="price-desc"?'selected="selected"':"") ?>>Prix + / -</option>
      </select>
    </div>
   <?php } ?>

  <div class="page-list">
    <img src="<?php echo URL; ?>ressources/images/pagination-bg-left.png" alt="" class="fl" />
    <div class="page-links">
     <?php for ($k=1; $k<=$maxpage; $k++) { ?>
       <?php if ($k == $page) { ?>
        <span class="cat3-current-page cat3-pagination-page"><?php echo $k ?></span>
       <?php } else { ?>
        <a class="pagination-link" data-action="+_page_<?php echo $k ?>" href="<?php echo $cat3Url.($k>1?'?page='.$k:'') ?>"><?php echo $k ?></a>
       <?php } ?>
     <?php } ?>
    </div>
    <img src="<?php echo URL; ?>ressources/images/pagination-bg-right.png" alt="" class="pagination-bg-right" />
  </div>

</div>
<div class="zero"></div>
   <?php for ($k = $startItemIndex; $k <= $endItemIndex; $k++) { $pdt = &$pdtList["data"][$k] ?>
   <?php
   // set if product is set as default estimate
    $pdt_set_as_estimate = false;
    if($pdt['price'] >= __THRESHOLD_PRICE_FOR_ESTIMATE__) $pdt_set_as_estimate = true;
    if($pdt['product_as_estimate']) $pdt_set_as_estimate = true;
    ?>

    <div class="grey-block product" data-id="<?php echo $pdt['id'] ?>">
        <div class="fl cat3-prod-list-pic">
          <div class="fr cat3-prod-list-infos">
            <h2><a class="blue-small-title blue-smaller-title" href="<?php echo $pdt["url"]; ?>"><?php echo $pdt["name"] ?></a></h2>
            <div class="fastdesc"><?php echo $pdt['fastdesc'] ?></div>
            <div class="desc">
              <?php echo htmlentities(substr(preg_replace('/&euro;/i', '€', html_entity_decode(filter_var($pdt["descc"], FILTER_SANITIZE_STRING), ENT_QUOTES)),0,150))."..." ?>
            </div>
           <?php if ($pdt["adv_cat"] == __ADV_CAT_SUPPLIER__ ) : ?>
            <p class="cat3-checked-line"><img src="<?php echo URL ?>ressources/images/green-check.png" alt="" />Livraison: <?php echo $pdt["delivery_time"] ?></p>
           <?php endif ?>

           <?php if (!empty($pdt['average_note'])) : ?>
            <span class="cat3-checked-line">
              <img src="<?php echo URL ?>ressources/images/picto-avis.png" alt="picto-avis" /> Avis client
              <?php showStarRater($pdt['average_note']) ?>
              <a class="color-blue ToggleProductFeedback" href="<?php echo $pdt['url'] ?>#block-product-notation">Lire les avis</a>
            </span>
           <?php endif ?>
          </div>
          <div class="picture fl">
            <div class="cat3-picture-border">
              <a href="<?php echo $pdt["url"]; ?>"><img src="<?php echo $pdt["pic_url"]; ?>" alt="<?= $pdt["name"] ?> - <?= $pdt["fastdesc"] ?>" class="vmaib"/></a><div class="vsma"></div>
              <div class="cat3-product-show"<?php if($_SERVER['PHP_SELF'] == '/categories.html' && !TEST) echo "onClick=\"_gaq.push(['_trackEvent', 'Famille 3', 'Ouverture pop up', 'Preview produit']);\""; ?>></div>
            </div>
          <?php if ($pdt["adv_cat"] == __ADV_CAT_SUPPLIER__ ) { ?>
            <span class="atseo expert-advice"></span>
          <?php } else{ ?>
            <span class="atseo multiple-estimates"></span>
          <?php } ?>
          </div>
        </div>
        <div class="fr cat3-prod-list-relations">
          <div class="cat3-price">
            <?php echo ($pdt["hasPrice"] ? ($pdt_set_as_estimate ? 'Sur devis' : 'à partir de : <span>'.sprintf("%.02f",$pdt["price"])."€ HT</span>") : 'à partir de : <span>'.$pdt["price"].'</span>'); ?>
          </div>
          <div class="cat3-action">
            <a href="<?php echo $pdt["cart_add_url"]; ?>" class="<?php if ($pdt["saleable"] && !$pdt_set_as_estimate) { echo $pdt['nb_refs'] > 1 ? 'btn-cart-add-big-pink': 'btn-cart-add-small-single'; } else { ?>btn-esti-ask-orange<?php } ?>" data-adv-type="<?php echo $pdt["adv_cat"]; ?>" ></a>
            <?php if ($pdt["adv_cat"] == __ADV_CAT_SUPPLIER__ && ($pdt["saleable"] && !$pdt_set_as_estimate)) : ?>
              <a href="<?php echo $pdt["cart_add_url"]; ?>"  data-adv-type="<?php echo $pdt["adv_cat"]; ?>" class="ask-estimate-link"><div class="puce puce-4"></div><span class="atseo ask-estimate-small"></span></a>
            <?php endif ?>
            <div class="savedProductsListZone_<?php echo $pdt['id'] ?>">
              <?php $productList = new ProductsSavedList();
              if ($productList->isProductInSavedList($pdt['id'])) : ?>
                <div class="puce puce-9"></div><span class="color-green">Produit sauvegardé</span>
                <a href="<?php echo $session->logged ? COMPTE_URL.'saved-products-list.html' : URL.'liste-produits-sauvegardes.html';?>" class="color-blue">Voir liste</a>
              <?php else : ?>
                <a href="saveProductList:add-<?php echo $pdt['id']; ?>" class="btn-users-product-list"><div class="puce puce-5"></div>Sauvegarder ce produit</a>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <div class="zero"></div>
    </div>
   <?php } ?>


<div id="cat3-pagination-block">
  <div class="cat3-pdt-count"><?php echo $pdtList["count"];?> produit<?php echo $pdtList["count"] != 1 ? 's': '';?></div>


  <?php if ($showPriceSortBlock) { ?>
    <div class="filter-sort">
      Trier par : <select>
        <option value=""></option>
        <option value="+_sort_price-asc" <?php echo ($sort=="price-asc"?'selected="selected"':"") ?>>Prix - / +</option>
        <option value="+_sort_price-desc"<?php echo ($sort=="price-desc"?'selected="selected"':"") ?>>Prix + / -</option>
      </select>
    </div>
   <?php } ?>

  <div class="page-list">
    <img src="<?php echo URL; ?>ressources/images/pagination-bg-left.png" alt="" class="fl" />
    <div class="page-links">
     <?php for ($k=1; $k<=$maxpage; $k++) { ?>
       <?php if ($k == $page) { ?>
        <span class="cat3-current-page cat3-pagination-page"><?php echo $k ?></span>
       <?php } else { ?>
        <a class="pagination-link" data-action="+_page_<?php echo $k ?>" href="<?php echo $cat3Url.($k>1?'?page='.$k:'') ?>"><?php echo $k ?></a>
       <?php } ?>
     <?php } ?>
    </div>
    <img src="<?php echo URL; ?>ressources/images/pagination-bg-right.png" alt="" class="pagination-bg-right" />
  </div>

</div>
<?php
	//
	//$text_content['text_content']
	// echo $facetLine['intro_text'];
	
	if(!empty ($text_content)){
	
		$paragraphs =explode(". ",$text_content);
		$i= 0;
		echo '<div id="text_desc_cat4">';
		foreach($paragraphs as $value_par){
			if($i > 3 ){
				echo ''.$value_par.'. ';
			}
		$i++;
		}
		echo '</div>';
	}
?>
<div class="zero"></div>
<div id="cat3-show-product-infos-dialog" title=""></div>
<?php
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
