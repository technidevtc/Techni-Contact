<?php
/*================================================================/

	Techni-Contact V3 - MD2I SAS
	http://www.techni-contact.com

	Auteur : Hook Network SARL - http://www.hook-network.com
	Date de création : 09 novembre 2011


	Fichier : /secure/fr/manager/ressources/ajax/AJAX_tabbed_search.php
	Description : moteur ajax de recherche globale en onglet

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

define("SEARCH", true);
define("MAX_RESULTS", 1000);

include LANG_LOCAL_INC."includes-".DB_LANGUAGE."_local.php";
include LANG_LOCAL_INC."www-".DB_LANGUAGE."_local.php";
//include LANG_LOCAL_INC."common-".DB_LANGUAGE."_local.php";
//include LANG_LOCAL_INC."infos-".DB_LANGUAGE."_local.php";

$db = DBHandle::get_instance();

$q = isset($_GET["search"]) ? trim($_GET["search"]) : "";
$search_type = isset($_GET["search_type"]) ? trim($_GET["search_type"]) : "";

$ql = strlen($q);
$numericSearch = false;

define("GBL_MAX_ID_LEN", 10); // global max id length
define("PDT_MAX_ID_LEN", 8);
define("REF_MAX_ID_LEN", 9);
define("CAT_MAX_ID_LEN", 5);
define("ADV_MAX_ID_LEN", 5);

// particular case of a number
if (is_numeric($q)) {
  // using 'LIKE' or 'MATCH' for substring search in integer fields is quite slow, so we create a list of intervals to use in the sql query
  // example for search = 159 and max id length = 6 :
  // search for id = 159 OR (>= 1590 AND <= 1599) OR (>= 15900 AND <= 15999) OR (>= 159000 AND <= 159999)
  $numericSearch = true;
  $match_expr = "+".$q."*";
  $num_intervals[] = $cur_interval_start = $cur_interval_end = $q;
  while (strlen($cur_interval_start) < GBL_MAX_ID_LEN) {
    $cur_interval_start .= "0";
    $cur_interval_end .= "9";
    $num_intervals[] = array($cur_interval_start, $cur_interval_end);
  }
  $num_intervals_len = count($num_intervals);
}
else {
  // "Google-ing" terms and ignoring plurals
  $terms = preg_split("`\s|-`", Utils::noDiphthong($q));
  for ($i = 0; $i < count($terms); $i++) {
    if (Utils::is_plural($terms[$i])) {
      $terms[$i] = (strlen($terms[$i]) > 3 ? "+" : "").Utils::get_singular($terms[$i])."* <<".$terms[$i]."*";
    }
    else {
      $terms[$i] = (strlen($terms[$i]) > 3 ? "+" : "").$terms[$i]."*";
    }
    //if ($i == 0) $terms[$i] = ">".$terms[$i];
  }
  $match_expr = implode(" ", $terms);
}

if ($ql >= 3 || $numericSearch) {
  
  $results = array("data_row" => array(), "data_col" => array(), "count" => 0);
  $queries = array();
  
  switch ($search_type) {
    
    case "1" :
      if ($numericSearch) { // numeric search
      
        // generating sql intervals for pdt id
        $sql_pdt_id_intervals = array("pfr.id = ".$num_intervals[0]);
        for ($k=1; $k < $num_intervals_len-(GBL_MAX_ID_LEN-PDT_MAX_ID_LEN); $k++)
          $sql_pdt_id_intervals[] = "(pfr.id >= ".$num_intervals[$k][0]." AND pfr.id <= ".$num_intervals[$k][1].")";
        $sql_pdt_id_intervals = implode(" OR ", $sql_pdt_id_intervals);
        
        // search in product id
        $queries["id produit"] = "
          SELECT
            pf.idProduct, pfr.name, pfr.ref_name, pfr.fastdesc, p.price AS pdt_price,
            ffr.id AS cat_id, ffr.name AS cat_name, ffr.ref_name AS cat_ref_name,
            ps.hits, ps.orders, ps.leads,
            a.id AS adv_id, a.nom1 AS adv_name, a.category AS adv_cat,
            rc.id AS ref_idtc, rc.refSupplier AS ref_refSupplier, rc.price AS ref_price,
            3 AS score
          FROM products_fr pfr
          INNER JOIN products p ON pfr.id = p.id
          INNER JOIN products_families pf ON pfr.id = pf.idProduct
          INNER JOIN families_fr ffr ON pf.idFamily = ffr.id
          INNER JOIN advertisers a ON pfr.idAdvertiser = a.id
          LEFT JOIN products_stats ps ON pfr.id = ps.id
          LEFT JOIN references_content rc ON pfr.id = rc.idProduct AND rc.classement = 1
          WHERE ".$sql_pdt_id_intervals."
          AND pfr.deleted != 1
          LIMIT 0,".MAX_RESULTS;
        /*$queries["id produit"] = array(
         "SELECT pfr.id FROM products_fr pfr WHERE ".$sql_pdt_id_intervals." LIMIT 0,".MAX_RESULTS,
         "SELECT
            pf.idProduct, pfr.name, pfr.ref_name, pfr.fastdesc, p.price AS pdt_price,
            ffr.id AS cat_id, ffr.name AS cat_name, ffr.ref_name AS cat_ref_name,
            ps.hits, ps.orders, ps.leads,
            a.id AS adv_id, a.nom1 AS adv_name, a.category AS adv_cat,
            rc.id AS ref_idtc, rc.refSupplier AS ref_refSupplier, rc.price AS ref_price,
            3 AS score
          FROM products_fr pfr
          INNER JOIN products p ON pfr.id = p.id
          INNER JOIN products_families pf ON pfr.id = pf.idProduct
          INNER JOIN families_fr ffr ON pf.idFamily = ffr.id
          INNER JOIN advertisers a ON pfr.idAdvertiser = a.id
          LEFT JOIN products_stats ps ON pfr.id = ps.id
          LEFT JOIN references_content rc ON pfr.id = rc.idProduct AND rc.classement = 1
          WHERE pfr.id IN (%PREV_QUERY_RESULTS%)"
        );*/
        
        // generating sql intervals for ref id
      /*$sql_ref_id_intervals = array("rc.id = ".$num_intervals[0]);
        for ($k=1; $k < $num_intervals_len-(GBL_MAX_ID_LEN-REF_MAX_ID_LEN); $k++)
          $sql_ref_id_intervals[] = "(rc.id >= ".$num_intervals[$k][0]." AND rc.id <= ".$num_intervals[$k][1].")";
        $sql_ref_id_intervals = implode(" OR ", $sql_ref_id_intervals);
        
        $queries["idtc"] = "
          SELECT
            pf.idProduct, pfr.name, pfr.ref_name, pfr.fastdesc, p.price AS pdt_price,
            ffr.id AS cat_id, ffr.name AS cat_name, ffr.ref_name AS cat_ref_name,
            ps.hits, ps.orders, ps.leads,
            a.id AS adv_id, a.nom1 AS adv_name, a.category AS adv_cat,
            rc.id AS ref_idtc, rc.refSupplier AS ref_refSupplier, rc.price AS ref_price,
            1 AS score
          FROM products_fr pfr
          INNER JOIN products p ON pfr.id = p.id
          INNER JOIN products_families pf ON pfr.id = pf.idProduct
          INNER JOIN families_fr ffr ON pf.idFamily = ffr.id
          INNER JOIN advertisers a ON pfr.idAdvertiser = a.id
          LEFT JOIN products_stats ps ON pfr.id = ps.id
          INNER JOIN references_content rc ON pfr.id = rc.idProduct
          WHERE ".$sql_ref_id_intervals."
          LIMIT 0,".MAX_RESULTS;*/
          
        // search in IDTC
        $queries["idtc"] = "
          SELECT
            pf.idProduct, pfr.name, pfr.ref_name, pfr.fastdesc, p.price AS pdt_price,
            ffr.id AS cat_id, ffr.name AS cat_name, ffr.ref_name AS cat_ref_name,
            ps.hits, ps.orders, ps.leads,
            a.id AS adv_id, a.nom1 AS adv_name, a.category AS adv_cat,
            rc.id AS ref_idtc, rc.refSupplier AS ref_refSupplier, rc.price AS ref_price,
            (MATCH (rc.id) AGAINST ('".$db->escape($match_expr)."' IN BOOLEAN MODE))*2 AS score
          FROM products_fr pfr
          INNER JOIN products p ON pfr.id = p.id
          INNER JOIN products_families pf ON pfr.id = pf.idProduct
          INNER JOIN families_fr ffr ON pf.idFamily = ffr.id
          INNER JOIN advertisers a ON pfr.idAdvertiser = a.id
          LEFT JOIN products_stats ps ON pfr.id = ps.id
          INNER JOIN references_content rc ON pfr.id = rc.idProduct
          WHERE MATCH (rc.id) AGAINST ('".$db->escape($match_expr)."' IN BOOLEAN MODE)
          AND pfr.deleted != 1
          LIMIT 0,".MAX_RESULTS;
      }
      else { // standard search
        
        // search in product name
        $queries["nom produit"] = "
          SELECT
            pf.idProduct, pfr.name, pfr.ref_name, pfr.fastdesc, p.price AS pdt_price,
            ffr.id AS cat_id, ffr.name AS cat_name, ffr.ref_name AS cat_ref_name,
            ps.hits, ps.orders, ps.leads,
            a.id AS adv_id, a.nom1 AS adv_name, a.category AS adv_cat,
            rc.id AS ref_idtc, rc.refSupplier AS ref_refSupplier, rc.price AS ref_price,
            MATCH (pfr.name) AGAINST ('".$db->escape($match_expr)."' IN BOOLEAN MODE) AS score
          FROM products_fr pfr
          INNER JOIN products p ON pfr.id = p.id
          INNER JOIN products_families pf ON pfr.id = pf.idProduct
          INNER JOIN families_fr ffr ON pf.idFamily = ffr.id
          INNER JOIN advertisers a ON pfr.idAdvertiser = a.id
          LEFT JOIN products_stats ps ON pfr.id = ps.id
          LEFT JOIN references_content rc ON pf.idProduct = rc.idProduct AND rc.classement = 1
          WHERE MATCH (pfr.name) AGAINST ('".$db->escape($match_expr)."' IN BOOLEAN MODE)
          AND pfr.deleted != 1
          GROUP BY pf.idProduct
          LIMIT 0,".MAX_RESULTS;
        
        // search in desc rapide
        $queries["description produit"] = "
          SELECT
            pf.idProduct, pfr.name, pfr.ref_name, pfr.fastdesc, p.price AS pdt_price,
						ffr.id AS cat_id, ffr.name AS cat_name, ffr.ref_name AS cat_ref_name,
            ps.hits, ps.orders, ps.leads,
            a.id AS adv_id, a.nom1 AS adv_name, a.category AS adv_cat,
            rc.id AS ref_idtc, rc.refSupplier AS ref_refSupplier, rc.price AS ref_price,
            MATCH (pfr.fastdesc) AGAINST ('".$db->escape($match_expr)."' IN BOOLEAN MODE) AS score
          FROM products_fr pfr
          INNER JOIN products p ON pfr.id = p.id
          INNER JOIN products_families pf ON pfr.id = pf.idProduct
          INNER JOIN families_fr ffr ON pf.idFamily = ffr.id
          INNER JOIN advertisers a ON pfr.idAdvertiser = a.id
          LEFT JOIN products_stats ps ON pfr.id = ps.id
          LEFT JOIN references_content rc ON pfr.id = rc.idProduct AND rc.classement = 1
          WHERE MATCH (pfr.fastdesc) AGAINST ('".$db->escape($match_expr)."' IN BOOLEAN MODE)
          AND pfr.deleted != 1
          LIMIT 0,".MAX_RESULTS;
      }
      
      // search in ref fournisseur in both case
      $queries["ref fournisseur"] = "
        SELECT
          pf.idProduct, pfr.name, pfr.ref_name, pfr.fastdesc, p.price AS pdt_price,
          ffr.id AS cat_id, ffr.name AS cat_name, ffr.ref_name AS cat_ref_name,
          ps.hits, ps.orders, ps.leads,
          a.id AS adv_id, a.nom1 AS adv_name, a.category AS adv_cat,
          rc.id AS ref_idtc, rc.refSupplier AS ref_refSupplier, rc.price AS ref_price,
          MATCH (rc.refSupplier) AGAINST ('".$db->escape($match_expr)."' IN BOOLEAN MODE) AS score
        FROM products_fr pfr
        INNER JOIN products p ON pfr.id = p.id
        INNER JOIN products_families pf ON pfr.id = pf.idProduct
        INNER JOIN families_fr ffr ON pf.idFamily = ffr.id
        INNER JOIN advertisers a ON pfr.idAdvertiser = a.id
        LEFT JOIN products_stats ps ON pfr.id = ps.id
        INNER JOIN references_content rc ON pfr.id = rc.idProduct
        WHERE MATCH (rc.refSupplier) AGAINST ('".$db->escape($match_expr)."' IN BOOLEAN MODE)
        AND pfr.deleted != 1
        LIMIT 0,".MAX_RESULTS;
      
      break;
    
    case "2" :
      if ($numericSearch) { // numeric search
        
        // generating sql intervals for cat id
        $sql_cat_id_intervals = array("ffr.id = ".$num_intervals[0]);
        for ($k=1; $k < $num_intervals_len-(GBL_MAX_ID_LEN-PDT_MAX_ID_LEN); $k++)
          $sql_cat_id_intervals[] = "(ffr.id >= ".$num_intervals[$k][0]." AND ffr.id <= ".$num_intervals[$k][1].")";
        $sql_cat_id_intervals = implode(" OR ", $sql_cat_id_intervals);
        
        // search in category id
        $queries["id famille"] = "
          SELECT
            pf.idProduct, pfr.name, pfr.ref_name, pfr.fastdesc, p.price AS pdt_price,
            ffr.id AS cat_id, ffr.name AS cat_name, ffr.ref_name AS cat_ref_name,
            ps.hits, ps.orders, ps.leads,
            a.id AS adv_id, a.nom1 AS adv_name, a.category AS adv_cat,
            rc.id AS ref_idtc, rc.refSupplier AS ref_refSupplier, rc.price AS ref_price,
            1 AS score
          FROM products_fr pfr
          INNER JOIN products p ON pfr.id = p.id
          INNER JOIN products_families pf ON pfr.id = pf.idProduct
          INNER JOIN families_fr ffr ON pf.idFamily = ffr.id
          INNER JOIN advertisers a ON pfr.idAdvertiser = a.id
          LEFT JOIN products_stats ps ON pfr.id = ps.id
          LEFT JOIN references_content rc ON pfr.id = rc.idProduct AND rc.classement = 1
          WHERE ".$sql_cat_id_intervals."
          AND pfr.deleted != 1
          ORDER BY ffr.id ASC
          LIMIT 0,".MAX_RESULTS;
      }
      else { // name search
        $queries["nom famille"] = "
          SELECT
            pf.idProduct, pfr.name, pfr.ref_name, pfr.fastdesc, p.price AS pdt_price,
            ffr.id AS cat_id, ffr.name AS cat_name, ffr.ref_name AS cat_ref_name,
            ps.hits, ps.orders, ps.leads,
            a.id AS adv_id, a.nom1 AS adv_name, a.category AS adv_cat,
            rc.id AS ref_idtc, rc.refSupplier AS ref_refSupplier, rc.price AS ref_price,
            MATCH (ffr.name) AGAINST ('".$db->escape($match_expr)."' IN BOOLEAN MODE) AS score
          FROM products_fr pfr
          INNER JOIN products p ON pfr.id = p.id
          INNER JOIN products_families pf ON pfr.id = pf.idProduct
          INNER JOIN families_fr ffr ON pf.idFamily = ffr.id
          INNER JOIN advertisers a ON pfr.idAdvertiser = a.id
          LEFT JOIN products_stats ps ON pfr.id = ps.id
          LEFT JOIN references_content rc ON pfr.id = rc.idProduct AND rc.classement = 1
          WHERE MATCH (ffr.name) AGAINST ('".$db->escape($match_expr)."' IN BOOLEAN MODE)
          AND pfr.deleted != 1
          LIMIT 0,".MAX_RESULTS;
      }
      break;

// recherche partenaire désactivée
// 
//    case "3" :
//      if ($numericSearch) { // numeric search
//
//        // generating sql intervals for adv id
//        $sql_adv_id_intervals = array("a.id = ".$num_intervals[0]);
//        for ($k=1; $k < $num_intervals_len-(GBL_MAX_ID_LEN-ADV_MAX_ID_LEN); $k++)
//          $sql_adv_id_intervals[] = "(a.id >= ".$num_intervals[$k][0]." AND a.id <= ".$num_intervals[$k][1].")";
//        $sql_adv_id_intervals = implode(" OR ", $sql_adv_id_intervals);
//
//        $queries["id partenaire"] = "
//          SELECT
//            a.id AS adv_id, a.nom1 AS adv_name, a.category AS adv_cat,
//            COUNT(DISTINCT p.id) AS adv_pdt_count,
//            1 AS score
//          FROM advertisers a
//          INNER JOIN products p ON a.id = p.idAdvertiser
//          INNER JOIN products_families pf ON p.id = pf.idProduct
//          WHERE ".$sql_adv_id_intervals."
//          GROUP BY a.id
//          LIMIT 0,".MAX_RESULTS;
//      }
//      else {
//        // search in partner name
//        $queries["nom partenaire"] = "
//          SELECT
//            a.id AS adv_id, a.nom1 AS adv_name, a.category AS adv_cat,
//            COUNT(DISTINCT p.id) AS adv_pdt_count,
//            MATCH (a.nom1) AGAINST ('".$db->escape($match_expr)."' IN BOOLEAN MODE) AS score
//          FROM advertisers a
//          INNER JOIN products p ON a.id = p.idAdvertiser
//          INNER JOIN products_families pf ON p.id = pf.idProduct
//          WHERE MATCH (a.nom1) AGAINST ('".$db->escape($match_expr)."' IN BOOLEAN MODE)
//          GROUP BY a.id
//          LIMIT 0,".MAX_RESULTS;
//      }
//      break;
  }
  
  $queriesTimes = array("global" => 0);
  foreach ($queries as $queryName => $query) {
    
    $startTime = microtime(true);
    if (is_string($query)) {
      $resource = $db->query($query, __FILE__, __LINE__);
    }
    elseif(is_array($query)) {
      $query2 = $query[0];
      for ($i=1,$l=count($query); $i<$l; $i++) {
        $resource = $db->query($query2, __FILE__, __LINE__);
        while ($row = $db->fetch($resource))
          $rows[] = $row[0];
        $query2 = str_replace("%PREV_QUERY_RESULTS%", "'".implode("','",$rows)."'", $query[$i]);
      }
      $resource = $db->query($query2, __FILE__, __LINE__);
      
    }
    $queriesTimes[$queryName] = microtime(true)-$startTime;
    $queriesTimes["global"] += $queriesTimes[$queryName];
    
    //pp($query2);
    
    while ($result = $db->fetchAssoc($resource)) {
      $result["hits2leads"] = $result["hits"] > 0 ? $result["leads"] / $result["hits"] : 0;
      $result["hits2orders"] = $result["hits"] > 0 ? $result["orders"] / $result["hits"] : 0;
      $result["transfo"] = round(($result["hits2leads"] + $result["hits2orders"])*100, 2);
      $result['links_url']['fo_pdt_url'] = $fo_pdt_url = URL."produits/".$result["cat_id"]."-".$result["idProduct"]."-".$result["ref_name"].".html";
      $result['links_url']['fo_pdt_pic_url'] = $fo_pdt_pic_url = is_file(PRODUCTS_IMAGE_INC."thumb_small/".$result["idProduct"]."-1.jpg") ? PRODUCTS_IMAGE_SECURE_URL."thumb_small/".$result["idProduct"]."-1.jpg" : PRODUCTS_IMAGE_SECURE_URL."no-pic-thumb_small.gif";
      $result['links_url']['bo_pdt_url'] = $bo_pdt_url = ADMIN_URL."products/edit.php?id=".$result["idProduct"];
      $result['links_url']['bo_adv_url'] = $bo_adv_url = ADMIN_URL."advertisers/edit.php?id=".$result["adv_id"];
      $result['links_url']['bo_lead_create_url'] = $bo_lead_create_url = ADMIN_URL."contacts/lead-create.php?pdtId=".$result["idProduct"];
      $results["data_row"][] = $result;
      
      foreach ($result as $k => $v)
        $results["data_col"][$k][] = $v;
      
      $results["count"]++;
      if ($results["count"] >= MAX_RESULTS) break;
    }
    
  }

    $sort     = isset($_GET["sort"])     ? trim($_GET["sort"]) : "";
    $lastsort = isset($_GET["lastsort"]) ? trim($_GET["lastsort"]) : "";
    $sortway  = isset($_GET["sortway"])  ? trim($_GET["sortway"]) : "";

        if ($sort == $lastsort && $sort != '') {
      if ($lastpage == $page) $sortway = ($sortway == 'asc' ? 'desc' : 'asc');
      else $sortway = ($sortway == 'asc' ? 'asc' : 'desc');
    }
    else $sortway = 'asc';

    $sortway_const = $sortway == "asc" ? SORT_ASC : SORT_DESC;
    $sortwayi_const = $sortway_const == SORT_ASC ? SORT_DESC : SORT_ASC;
    switch ($search_type) {
      case "1" :
      case "2" :
        $results["data_col"]["price"] = array();
        foreach ($results["data_col"]["pdt_price"] as $k => $v) {
          if ($v == "ref")
            $results["data_row"][$k]["price"] = $results["data_col"]["price"][$k] = $results["data_col"]["ref_price"][$k];
          elseif (empty($v))
            $results["data_row"][$k]["price"] = $results["data_col"]["price"][$k] = "sur devis";
          else
            $results["data_row"][$k]["price"] = $results["data_col"]["price"][$k] = $v;
        }
        switch ($sort) {
//          case "id"       : array_multisort($results["data_col"]["idProduct"], $sortway_const, $results["data_col"]["score"], SORT_DESC, $results["data_row"]); break;
//          case "name"     : array_multisort($results["data_col"]["name"], $sortway_const, $results["data_col"]["score"], SORT_DESC, $results["data_row"]); break;
//          case "fastdesc" : array_multisort($results["data_col"]["fastdesc"], $sortway_const, $results["data_col"]["score"], SORT_DESC, $results["data_row"]); break;
//          case "cat_id"   : array_multisort($results["data_col"]["cat_id"], $sortway_const, SORT_NUMERIC, $results["data_col"]["score"], SORT_DESC, $results["data_row"]); break;
//          case "price"    : array_multisort($results["data_col"]["price"], $sortway_const, $results["data_col"]["score"], SORT_DESC, $results["data_row"]); break;
          case "adv_name" : array_multisort($results["data_col"]["adv_name"], $sortway_const, $results["data_col"]["score"], SORT_DESC, $results["data_row"]); break;
          case "adv_cat"  : array_multisort($results["data_col"]["adv_cat"], $sortway_const, $results["data_col"]["score"], SORT_DESC, $results["data_row"]); break;
//          case "hits"     : array_multisort($results["data_col"]["hits"], $sortwayi_const, SORT_NUMERIC, $results["data_col"]["score"], SORT_ASC, $results["data_row"]); break;
//          case "leads"    : array_multisort($results["data_col"]["leads"], $sortwayi_const, SORT_NUMERIC, $results["data_col"]["score"], SORT_ASC, $results["data_row"]); break;
//          case "orders"   : array_multisort($results["data_col"]["orders"], $sortwayi_const, SORT_NUMERIC, $results["data_col"]["score"], SORT_ASC, $results["data_row"]); break;
//          case "transfo"  : array_multisort($results["data_col"]["transfo"], $sortwayi_const, SORT_NUMERIC, $results["data_col"]["score"], SORT_ASC, $results["data_row"]); break;
//          case "score"    : array_multisort($results["data_col"]["score"], $sortway_const, SORT_NUMERIC, $results["data_col"]["name"], SORT_DESC, $results["data_row"]); break;
          default : array_multisort($results["data_col"]["score"], $sortwayi_const, $results["data_col"]["name"], SORT_DESC, $results["data_row"]); break;
        }
        break;
//      case "3" :
//        switch ($sort) {
//          case "adv_id"   : array_multisort($results["data_col"]["adv_id"], $sortway_const, SORT_NUMERIC, $results["data_col"]["score"], SORT_DESC, $results["data_row"]); break;
//          case "adv_name" : array_multisort($results["data_col"]["adv_name"], $sortway_const, $results["data_col"]["score"], SORT_DESC, $results["data_row"]); break;
//          case "adv_cat"  : array_multisort($results["data_col"]["adv_cat"], $sortway_const, $results["data_col"]["score"], SORT_DESC, $results["data_row"]); break;
//          case "adv_pdt_count" : array_multisort($results["data_col"]["adv_pdt_count"], $sortway_const, SORT_NUMERIC, $results["data_col"]["score"], SORT_DESC, $results["data_row"]); break;
//          case "score"    : array_multisort($results["data_col"]["score"], $sortway_const, SORT_NUMERIC, $results["data_col"]["adv_name"], SORT_DESC, $results["data_row"]); break;
//          default : array_multisort($results["data_col"]["score"], $sortway_const, $results["data_col"]["adv_name"], SORT_DESC, $results["data_row"]); break;
//        }
//        break;
    }
}


mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $results);
  print json_encode($results);



