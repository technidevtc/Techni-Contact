<?php

//$db = $conn->getDbh();
$db = Doctrine_Manager::connection()->getDbh();

$selectionTypeList = array( // all preselection types by category level
  1 => array(
    'pdt_selection',
    'pdt_favourite',
    'pdt_mostviewed',
    'pdt_latest'
  ),
  2 => array(
    'pdt_pic',
    'pdt_selection',
    'pdt_favourite',
    'pdt_mostviewed',
    'pdt_latest'
  ),
  3 => array(
    'pdt_pic',
    'pdt_selection',
    'preview_pdt_selection'
  )
);

// getting all db products with stats, ordered by category 3
$sth = $db->prepare("
  SELECT
    p.id,
    pf.idFamily AS cat_id,
    ps.hits / (" . mktime(0,0,0) . "- ps.first_hit_time) * 86400 AS hpd,
    IF(ps.hits>0, ps.orders / ps.hits, 0) AS orders2hits,
    IF(ps.hits>0, ps.leads / ps.hits, 0) AS leads2hits,
    ps.hits
  FROM products p
  INNER JOIN products_fr pfr ON pfr.id = p.id AND pfr.active = 1 AND pfr.deleted = 0
  INNER JOIN advertisers a ON a.id = p.idAdvertiser AND a.actif = 1
  INNER JOIN products_families pf ON pf.idProduct = p.id
  LEFT JOIN products_stats ps ON ps.id = p.id");
$sth->execute();
$cat3PdtList = array();
while ($pdt = $sth->fetch(PDO::FETCH_ASSOC))
  $cat3PdtList[$pdt['cat_id']][] = $pdt;


// arbitrary weigth vars, see /www/fr/rechercher.html for more details
define('HITS_2_LEADS_W', 0.2);
define('HITS_2_ORDERS_W', 0.8);
// Frequency weight if 2 times more
define('HITS_FREQ_W2', 1.1);
define('HITS_PER_DAY_FREQ_W2', 1.2);
// precalculating some vars
define('HITS_FREQ_W2P', log(HITS_FREQ_W2, 2));
define('HITS_PER_DAY_FREQ_W2P', log(HITS_PER_DAY_FREQ_W2, 2));

function sortCat3PdtByScore2(&$cat) {
  global $cat3PdtList;
  if (count($cat3PdtList[$cat['id']]) > 0) {
    $cat3_hits_avg = $cat['hits'] / $cat['pdt_count'];
    $cat3_leads_avg = $cat['leads'] / $cat['pdt_count'];
    $cat3_orders_avg = $cat['orders'] / $cat['pdt_count'];
    $cat3_hpd_avg = ($cat['hits'] / $cat['age_avg'] * 86400) / $cat['pdt_count'];

    foreach ($cat3PdtList[$cat['id']] as &$pdt) {
      $leads_conv_ratio = $cat3_leads_avg == 0 ? 0 : $pdt['leads2hits'] * ($cat3_hits_avg / $cat3_leads_avg) * HITS_2_LEADS_W;
      $orders_conv_ratio = $cat3_orders_avg == 0 ? 0 : $pdt['orders2hits'] * ($cat3_hits_avg / $cat3_orders_avg) * HITS_2_ORDERS_W;
      $final_conv_ratio = $leads_conv_ratio + $orders_conv_ratio;
      $pdt['score2'] = ($final_conv_ratio == 0 ? 1 : $final_conv_ratio) // 1 if no conversion, to order them by a weight between hits and hpd
                       * ($cat3_hits_avg ? pow($pdt['hits']/$cat3_hits_avg, HITS_FREQ_W2P) : 1)
                       * ($cat3_hpd_avg ? pow($pdt['hpd']/$cat3_hpd_avg, HITS_PER_DAY_FREQ_W2P) : 1);
    }
    unset($pdt);
    Utils::sortDbInPlace($cat3PdtList[$cat['id']], "score2", SORT_DESC, SORT_NUMERIC, "hits", SORT_DESC, SORT_NUMERIC);
  }
}

// main graph var
$categories = array();

// root cat, necessary to get the main categories
$categories[0] = array();

// getting the category graph
$sth = $db->prepare("
  SELECT
    f.id,
    f.idParent,
    f.pdt_overwrite,
    fr.name,
    fr.ref_name,
    fr.title,
    fr.meta_desc,
    COUNT(pfr.id) AS pdt_count,
    (".mktime(0,0,0)." - AVG(ps.first_hit_time)) AS age_avg,
    SUM(ps.hits) AS hits,
    SUM(ps.leads) AS leads,
    SUM(ps.orders) AS orders,
    SUM(ps.estimates) AS estimates,
    pfr.idAdvertiser,
    a.actif,
    pfr.active,
    pfr.deleted,
    pf.idFamily
  FROM families f
  INNER JOIN families_fr fr ON f.id = fr.id
  LEFT JOIN products_families pf ON f.id = pf.idFamily
  LEFT JOIN products_fr pfr ON pfr.id = pf.idProduct
  LEFT JOIN advertisers a ON a.id = pfr.idAdvertiser
  LEFT JOIN products_stats ps ON pf.idProduct = ps.id
  WHERE pfr.id IS NULL OR (pfr.active = 1 AND pfr.deleted = 0 AND a.actif = 1)
  GROUP BY f.id
  ORDER BY fr.name");
$sth->execute();
while ($cat = $sth->fetch(PDO::FETCH_ASSOC)) {

  $cat['name'] = htmlspecialchars($cat['name']); // for & < > ' " in the name attribute

  if (!$cat['age_avg']) // should not happen
    $cat['age_avg'] = FIRST_YEAR_STATS;

  // small trick to know the category level
  $cat_level = $cat['pdt_count'] > 0 ? 3 : ($cat['idParent'] == 0 ? 1 : 2);

  $pdt_overwrite = empty($cat['pdt_overwrite']) ? array() : mb_unserialize($cat['pdt_overwrite']);
  foreach ($pdt_overwrite as $selectionType => $v) {
    if (!in_array($selectionType, $selectionTypeList[$cat_level])) // selection type is not supported
      continue;
    $asic = explode("|", $v); // Array of Selected Items by Categories
    $itemList = array();
    foreach ($asic as $asi) { // Array of Selected Items
      $asi = explode(",", $asi); // category and items
      $itemList = array_merge($itemList, array_slice($asi, 1));
    }
    $cat[$selectionType] = $itemList;
  }

  foreach ($selectionTypeList[$cat_level] as $selectionType) {
    $itemList = isset($cat[$selectionType]) ? $cat[$selectionType] : array();
    $itemListCount = count($itemList);
    switch ($cat_level) {
      case 1:
        break;
      case 2:
         break;
      case 3:
        // if the current category isn't in $cat3PdtList, something went wrong somewhere, so let's ignore this category
        if (!isset($cat3PdtList[$cat['id']])) // something went wrong somewhere, let's ignore this category
          continue 2;

        switch ($selectionType) {
          case 'pdt_pic':
            if ($itemListCount < CAT3_PDT_PIC_COUNT) {
              sortCat3PdtByScore2($cat);
              $itemList[] = $cat3PdtList[$cat['id']][0]['id'];
            }
            break;
          case 'preview_pdt_selection':
            // it's a category 3 that lacks selected products on the category 2 page, so add some with weighted stats
            if ($itemListCount < CAT3_PREVIEW_PDT_SELECTION_COUNT) {
              sortCat3PdtByScore2($cat);
              $items2push = array_slice($cat3PdtList[$cat['id']], 0, CAT3_PREVIEW_PDT_SELECTION_COUNT-$itemListCount);
              foreach ($items2push as $item2push)
                $itemList[] = $item2push['id'];
            }
            break;
        }
        break;
    }

    $cat[$selectionType] = implode("|", $itemList);
  }

  unset($cat['pdt_overwrite']);
  if (isset($categories[$cat['id']])) // category already has children
    $categories[$cat['id']] = array_merge($cat, $categories[$cat['id']]);
  else
    $categories[$cat['id']] = $cat;
  $categories[$cat['idParent']]['children'][] = $cat['id'];

}

//mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $categories);
$xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
$xml .= "<!DOCTYPE categories SYSTEM \"categories.dtd\">\n";

$cat0ID = 0;
if (isset($categories[0]['children'])) {
  sort($categories[0]['children']);
  $cat0_inner_xml = "";
  $cat0 = array(
    "pdt_count" => 0,
    "hits" => 0,
    "leads" => 0,
    "orders" => 0,
    "estimates" => 0,
    "age_avg" => 0,
    "cumul_age" => 0,
    "cat3_count" => 0,
    "cat2_count" => 0,
    "cat1_count" => 0);

  foreach($categories[0]['children'] as $cat1ID) {

    if (isset($categories[$cat1ID]['children'])) {
      $cat1_inner_xml = "";
      $cat1 = array(
        "pdt_count" => 0,
        "hits" => 0,
        "leads" => 0,
        "orders" => 0,
        "estimates" => 0,
        "age_avg" => 0,
        "cumul_age" => 0,
        "cat3_count" => 0,
        "cat2_count" => 0);

      foreach ($categories[$cat1ID]['children'] as $cat2ID) {

        if (isset($categories[$cat2ID]['children'])) {
          $cat2_inner_xml = "";
          $cat2 = array(
            "pdt_count" => 0,
            "hits" => 0,
            "leads" => 0,
            "orders" => 0,
            "estimates" => 0,
            "age_avg" => 0,
            "cumul_age" => 0,
            "cat3_count" => 0);

          foreach ($categories[$cat2ID]['children'] as $cat3ID) {
            $cat3 = array(
              "pdt_count" => $categories[$cat3ID]['pdt_count'],
              "hits" => $categories[$cat3ID]['hits'],
              "leads" => $categories[$cat3ID]['leads'],
              "orders" => $categories[$cat3ID]['orders'],
              "estimates" => $categories[$cat3ID]['estimates'],
              "age_avg" => (int)$categories[$cat3ID]['age_avg']);
            if ($cat3['pdt_count'] > 0) {
              $cat2_inner_xml .=
                "   <category id=\"".$cat3ID."\" ref_name=\"".$categories[$cat3ID]['ref_name']."\" name=\"".$categories[$cat3ID]['name']."\">\n".
                "    <kid v=\"".XML_KEY_PREFIX.$cat3ID."\"/>\n".
                "    <krn v=\"".XML_KEY_PREFIX.$categories[$cat3ID]['ref_name']."\"/>\n".
                "    <stats>".implode("|",$cat3)."</stats>\n".
                (!empty($categories[$cat3ID]['title']) ?
                "    <title><![CDATA[".$categories[$cat3ID]['title']."]]></title>\n" : "").
                (!empty($categories[$cat3ID]['meta_desc']) ?
                "    <meta_desc><![CDATA[".$categories[$cat3ID]['meta_desc']."]]></meta_desc>\n" : "").
                (!empty($categories[$cat3ID]['pdt_pic']) ?
                "    <pdt_pic>".$categories[$cat3ID]['pdt_pic']."</pdt_pic>\n" : "").
                (!empty($categories[$cat3ID]['pdt_selection']) ?
                "    <pdt_selection>".$categories[$cat3ID]['pdt_selection']."</pdt_selection>\n" : "").
                (!empty($categories[$cat3ID]['preview_pdt_selection']) ?
                "    <preview_pdt_selection>".$categories[$cat3ID]['preview_pdt_selection']."</preview_pdt_selection>\n" : "").
                "   </category>\n";
              $cat2['pdt_count'] += $cat3['pdt_count'];
              $cat2['hits'] += $cat3['hits'];
              $cat2['leads'] += $cat3['leads'];
              $cat2['orders'] += $cat3['orders'];
              $cat2['estimates'] += $cat3['estimates'];
              $cat2['cumul_age'] += $cat3['age_avg'] * $cat3['pdt_count'];
              $cat2['cat3_count']++;
            }
          }

          if ($cat2['pdt_count'] > 0) {
            $cat2['age_avg'] = (int)($cat2['cumul_age'] / $cat2['pdt_count']);
            unset($cat2['cumul_age']);
            $cat1_inner_xml .=
              "  <category id=\"".$cat2ID."\" ref_name=\"".$categories[$cat2ID]['ref_name']."\" name=\"".$categories[$cat2ID]['name']."\">\n".
              "   <kid v=\"".XML_KEY_PREFIX.$cat2ID."\"/>\n".
              "   <krn v=\"".XML_KEY_PREFIX.$categories[$cat2ID]['ref_name']."\"/>\n".
              "   <stats>".implode("|",$cat2)."</stats>\n".
              (!empty($categories[$cat2ID]['title']) ?
              "   <title><![CDATA[".$categories[$cat2ID]['title']."]]></title>\n" : "").
              (!empty($categories[$cat2ID]['meta_desc']) ?
              "   <meta_desc><![CDATA[".$categories[$cat2ID]['meta_desc']."]]></meta_desc>\n" : "").
              (!empty($categories[$cat2ID]['pdt_pic']) ?
              "   <pdt_pic>".$categories[$cat2ID]['pdt_pic']."</pdt_pic>\n" : "").
              (!empty($categories[$cat2ID]['pdt_selection']) ?
              "   <pdt_selection>".$categories[$cat2ID]['pdt_selection']."</pdt_selection>\n" : "");
            $cat1_inner_xml .= $cat2_inner_xml;
            $cat1_inner_xml .= "  </category>\n";
            $cat1['pdt_count'] += $cat2['pdt_count'];
            $cat1['hits'] += $cat2['hits'];
            $cat1['leads'] += $cat2['leads'];
            $cat1['orders'] += $cat2['orders'];
            $cat1['estimates'] += $cat2['estimates'];
            $cat1['cumul_age'] += $cat2['age_avg'] * $cat2['pdt_count'];
            $cat1['cat2_count']++;
            $cat1['cat3_count'] += $cat2['cat3_count'];
          }
        }
      }

      if ($cat1['pdt_count'] > 0) {
        $cat1['age_avg'] = (int)($cat1['cumul_age'] / $cat1['pdt_count']);
        unset($cat1['cumul_age']);
        $cat0_inner_xml .=
          " <category id=\"".$cat1ID."\" ref_name=\"".$categories[$cat1ID]['ref_name']."\" name=\"".$categories[$cat1ID]['name']."\">\n".
          "  <kid v=\"".XML_KEY_PREFIX.$cat1ID."\"/>\n".
          "  <krn v=\"".XML_KEY_PREFIX.$categories[$cat1ID]['ref_name']."\"/>\n".
          "  <stats>".implode("|",$cat1)."</stats>\n".
          (!empty($categories[$cat1ID]['title']) ?
          "  <title><![CDATA[".$categories[$cat1ID]['title']."]]></title>\n" : "").
          (!empty($categories[$cat1ID]['meta_desc']) ?
          "  <meta_desc><![CDATA[".$categories[$cat1ID]['meta_desc']."]]></meta_desc>\n" : "").
          (!empty($categories[$cat1ID]['pdt_selection']) ?
          "  <pdt_selection>".$categories[$cat1ID]['pdt_selection']."</pdt_selection>\n" : "");
        $cat0_inner_xml .= $cat1_inner_xml;
        $cat0_inner_xml .= " </category>\n";
        $cat0['pdt_count'] += $cat1['pdt_count'];
        $cat0['hits'] += $cat1['hits'];
        $cat0['leads'] += $cat1['leads'];
        $cat0['orders'] += $cat1['orders'];
        $cat0['estimates'] += $cat1['estimates'];
        $cat0['cumul_age'] += $cat1['age_avg'] * $cat1['pdt_count'];
        $cat0['cat1_count']++;
        $cat0['cat2_count'] += $cat1['cat2_count'];
        $cat0['cat3_count'] += $cat1['cat3_count'];
      }
    }
  }

  if ($cat0['pdt_count'] > 0) {
    $cat0['age_avg'] = (int)($cat0['cumul_age'] / $cat0['pdt_count']);
    unset($cat0['cumul_age']);
    $xml .=
      "<categories>\n".
      " <kid v=\"".XML_KEY_PREFIX.$cat0ID."\"/>\n".
      " <stats_key>".implode("|",array_keys($cat0))."</stats_key>\n".
      " <stats>".implode("|",$cat0)."</stats>\n";
    $xml .= $cat0_inner_xml;
    $xml .= "</categories>\n";
  }
}

$fh = fopen(XML_CATEGORIES_ALL, "w+");
fwrite($fh, $xml);
fclose($fh);
