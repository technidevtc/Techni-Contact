<?php

if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

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
        $sql_pdt_id_intervals = array("pfr.id = ".$num_intervals[0]." AND pfr.deleted != 1");
        for ($k=1; $k < $num_intervals_len-(GBL_MAX_ID_LEN-PDT_MAX_ID_LEN); $k++)
          $sql_pdt_id_intervals[] = "(pfr.id >= ".$num_intervals[$k][0]." AND pfr.id <= ".$num_intervals[$k][1]."  AND pfr.deleted != 1)";
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
          LEFT JOIN references_content rc ON pfr.id = rc.idProduct AND rc.classement = 1 AND rc.deleted != 1
          WHERE ".$sql_pdt_id_intervals."
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
          WHERE MATCH (rc.id) AGAINST ('".$db->escape($match_expr)."' IN BOOLEAN MODE)  AND pfr.deleted != 1
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
          WHERE MATCH (pfr.name) AGAINST ('".$db->escape($match_expr)."' IN BOOLEAN MODE) AND pfr.deleted != 1
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
          WHERE MATCH (pfr.fastdesc) AGAINST ('".$db->escape($match_expr)."' IN BOOLEAN MODE) AND pfr.deleted != 1
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
        WHERE MATCH (rc.refSupplier) AGAINST ('".$db->escape($match_expr)."' IN BOOLEAN MODE) AND pfr.deleted != 1
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
        /*$queries["id famille"] = "
          SELECT
            pf.idProduct, pfr.name, pfr.ref_name, pfr.fastdesc, p.price AS pdt_price,
            ffr.id AS cat_id, ffr.name AS cat_name, ffr.ref_name AS cat_ref_name,
            ps.hits, ps.orders, ps.leads,
            a.id AS adv_id, a.nom1 AS adv_name, a.category AS adv_cat,
            rc.id AS ref_idtc, rc.refSupplier AS ref_refSupplier, rc.price AS ref_price,
            1 AS score
          FROM products_fr pfr
          INNER JOIN products p ON pfr.id = p.id
          INNER JOIN products_families pf ON (pfr.id = pf.idProduct AND pfr.deleted != 1)
          INNER JOIN families_fr ffr ON pf.idFamily = ffr.id
          INNER JOIN advertisers a ON pfr.idAdvertiser = a.id
          LEFT JOIN products_stats ps ON pfr.id = ps.id
          LEFT JOIN references_content rc ON pfr.id = rc.idProduct AND rc.classement = 1
          WHERE ".$sql_cat_id_intervals."
          ORDER BY ffr.id ASC
          LIMIT 0,".MAX_RESULTS;*/
		  
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
          INNER JOIN products_families pf ON (pfr.id = pf.idProduct AND pfr.deleted != 1)
          INNER JOIN families_fr ffr ON pf.idFamily = ffr.id
          INNER JOIN advertisers a ON pfr.idAdvertiser = a.id
          LEFT JOIN products_stats ps ON pfr.id = ps.id
          LEFT JOIN references_content rc ON pfr.id = rc.idProduct AND rc.classement = 1
          WHERE 
			ffr.id = ".$num_intervals[0]."
          ORDER BY ffr.id ASC
          LIMIT 0,".MAX_RESULTS;
		  
      }
      else { // name search
		
		/*
		echo(' ** '.$db->escape($match_expr).' ** ');
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
			  INNER JOIN products_families pf ON (pfr.id = pf.idProduct AND pfr.deleted != 1)
			  INNER JOIN families_fr ffr ON pf.idFamily = ffr.id
			  INNER JOIN advertisers a ON pfr.idAdvertiser = a.id
			  LEFT JOIN products_stats ps ON pfr.id = ps.id
			  LEFT JOIN references_content rc ON pfr.id = rc.idProduct AND rc.classement = 1
			  WHERE MATCH (ffr.name) AGAINST ('".$db->escape($match_expr)."' IN BOOLEAN MODE)
			  LIMIT 0,".MAX_RESULTS;
		  */
		  
		  $queries["nom famille"] = "
			  SELECT
				pf.idProduct, pfr.name, pfr.ref_name, pfr.fastdesc, p.price AS pdt_price,
				ffr.id AS cat_id, ffr.name AS cat_name, ffr.ref_name AS cat_ref_name,
				ps.hits, ps.orders, ps.leads,
				a.id AS adv_id, a.nom1 AS adv_name, a.category AS adv_cat,
				rc.id AS ref_idtc, rc.refSupplier AS ref_refSupplier, rc.price AS ref_price
			  FROM products_fr pfr
			  INNER JOIN products p ON pfr.id = p.id
			  INNER JOIN products_families pf ON (pfr.id = pf.idProduct AND pfr.deleted != 1)
			  INNER JOIN families_fr ffr ON pf.idFamily = ffr.id
			  INNER JOIN advertisers a ON pfr.idAdvertiser = a.id
			  LEFT JOIN products_stats ps ON pfr.id = ps.id
			  LEFT JOIN references_content rc ON pfr.id = rc.idProduct AND rc.classement = 1
			  WHERE ffr.name='".addslashes($q)."'
			  LIMIT 0,".MAX_RESULTS;

		  
      }
      break;
    
    case "3" :
      if ($numericSearch) { // numeric search
        
        // generating sql intervals for adv id
        $sql_adv_id_intervals = array("a.id = ".$num_intervals[0]);
        for ($k=1; $k < $num_intervals_len-(GBL_MAX_ID_LEN-ADV_MAX_ID_LEN); $k++)
          $sql_adv_id_intervals[] = "(a.id >= ".$num_intervals[$k][0]." AND a.id <= ".$num_intervals[$k][1].")";
        $sql_adv_id_intervals = implode(" OR ", $sql_adv_id_intervals);
        
        $queries["id partenaire"] = "
          SELECT
            a.id AS adv_id, a.nom1 AS adv_name, a.category AS adv_cat,
            COUNT(DISTINCT p.id) AS adv_pdt_count,
            1 AS score
          FROM advertisers a
          INNER JOIN products p ON a.id = p.idAdvertiser
          INNER JOIN products_families pf ON p.id = pf.idProduct
          WHERE ".$sql_adv_id_intervals." AND a.deleted != 1
          GROUP BY a.id
          LIMIT 0,".MAX_RESULTS;
      }
      else {
        // search in partner name
        $queries["nom partenaire"] = "
          SELECT
            a.id AS adv_id, a.nom1 AS adv_name, a.category AS adv_cat,
            COUNT(DISTINCT p.id) AS adv_pdt_count,
            MATCH (a.nom1) AGAINST ('".$db->escape($match_expr)."' IN BOOLEAN MODE) AS score
          FROM advertisers a
          INNER JOIN products p ON a.id = p.idAdvertiser
          INNER JOIN products_families pf ON p.id = pf.idProduct
          WHERE MATCH (a.nom1) AGAINST ('".$db->escape($match_expr)."' IN BOOLEAN MODE)
          AND a.deleted != 1
          GROUP BY a.id
          LIMIT 0,".MAX_RESULTS;
      }
      break;
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
      $results["data_row"][] = $result;
      foreach ($result as $k => $v)
        $results["data_col"][$k][] = $v;
      
      $results["count"]++;
      if ($results["count"] >= MAX_RESULTS) break;
    }
    
  }
  
  $page     = isset($_GET["page"])     ? (int)(trim($_GET["page"])) : 1;
  $lastpage = isset($_GET["lastpage"]) ? (int)(trim($_GET["lastpage"])) : 1;
  $sort     = isset($_GET["sort"])     ? trim($_GET["sort"]) : "";
  $lastsort = isset($_GET["lastsort"]) ? trim($_GET["lastsort"]) : "";
  $sortway  = isset($_GET["sortway"])  ? trim($_GET["sortway"]) : "";
  $selectedList = isset($_GET["selectList"]) ? explode("|",trim($_GET["selectList"])) : array();
  $selectedFamily = isset($_GET["selectedFamily"]) ? $_GET["selectedFamily"] : '';
  
  if ($results["count"] > 0) {
    if ($page < 1) $page = 1;
    if ($lastpage < 1) $lastpage = 1;
    
    define("NB", 20);
    if (($page-1) * NB >= $results["count"]) $page = ($results["count"] - $results["count"]%NB) / NB + 1;
    if (($lastpage-1) * NB >= $results["count"]) $lastpage = ($results["count"] - $results["count"]%NB) / NB + 1;

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
          case "id"       : array_multisort($results["data_col"]["idProduct"], $sortway_const, $results["data_col"]["score"], SORT_DESC, $results["data_row"]); break;
          case "name"     : array_multisort($results["data_col"]["name"], $sortway_const, $results["data_col"]["score"], SORT_DESC, $results["data_row"]); break;
          case "fastdesc" : array_multisort($results["data_col"]["fastdesc"], $sortway_const, $results["data_col"]["score"], SORT_DESC, $results["data_row"]); break;
          case "cat_id"   : array_multisort($results["data_col"]["cat_id"], $sortway_const, SORT_NUMERIC, $results["data_col"]["score"], SORT_DESC, $results["data_row"]); break;
		  case "cat_name"   : 
		  
			array_multisort($results["data_col"]["cat_name"], $sortway_const, SORT_NUMERIC, $results["data_col"]["score"], SORT_DESC, $results["data_row"]); 
			
			break;
          case "price"    : array_multisort($results["data_col"]["price"], $sortway_const, $results["data_col"]["score"], SORT_DESC, $results["data_row"]); break;
          case "adv_name" : array_multisort($results["data_col"]["adv_name"], $sortway_const, $results["data_col"]["score"], SORT_DESC, $results["data_row"]); break;
          case "adv_cat"  : array_multisort($results["data_col"]["adv_cat"], $sortway_const, $results["data_col"]["score"], SORT_DESC, $results["data_row"]); break;
          case "hits"     : array_multisort($results["data_col"]["hits"], $sortwayi_const, SORT_NUMERIC, $results["data_col"]["score"], SORT_ASC, $results["data_row"]); break;
          case "leads"    : array_multisort($results["data_col"]["leads"], $sortwayi_const, SORT_NUMERIC, $results["data_col"]["score"], SORT_ASC, $results["data_row"]); break;
          case "orders"   : array_multisort($results["data_col"]["orders"], $sortwayi_const, SORT_NUMERIC, $results["data_col"]["score"], SORT_ASC, $results["data_row"]); break;
          case "transfo"  : array_multisort($results["data_col"]["transfo"], $sortwayi_const, SORT_NUMERIC, $results["data_col"]["score"], SORT_ASC, $results["data_row"]); break;
          case "score"    : array_multisort($results["data_col"]["score"], $sortway_const, SORT_NUMERIC, $results["data_col"]["name"], SORT_DESC, $results["data_row"]); break;
          default : array_multisort($results["data_col"]["score"], $sortwayi_const, $results["data_col"]["name"], SORT_DESC, $results["data_row"]); break;
        }
        break;
      case "3" :
        switch ($sort) {
          case "adv_id"   : array_multisort($results["data_col"]["adv_id"], $sortway_const, SORT_NUMERIC, $results["data_col"]["score"], SORT_DESC, $results["data_row"]); break;
          case "adv_name" : array_multisort($results["data_col"]["adv_name"], $sortway_const, $results["data_col"]["score"], SORT_DESC, $results["data_row"]); break;
          case "adv_cat"  : array_multisort($results["data_col"]["adv_cat"], $sortway_const, $results["data_col"]["score"], SORT_DESC, $results["data_row"]); break;
          case "adv_pdt_count" : array_multisort($results["data_col"]["adv_pdt_count"], $sortway_const, SORT_NUMERIC, $results["data_col"]["score"], SORT_DESC, $results["data_row"]); break;
          case "score"    : array_multisort($results["data_col"]["score"], $sortway_const, SORT_NUMERIC, $results["data_col"]["adv_name"], SORT_DESC, $results["data_row"]); break;
          default : array_multisort($results["data_col"]["score"], $sortway_const, $results["data_col"]["adv_name"], SORT_DESC, $results["data_row"]); break;
        }
        break;
    }
    $lastsort = $sort;
    $lastpage = $page;
  }
  
}

if (DEBUG) {
  $queriesCount = count($queriesTimes)-1;
  $queryTimesHtml = $queriesCount." requête".($queriesCount>1?"s":"")." SQL effectuée".($queriesCount>1?"s":"")." en ".sprintf("%.03f",$queriesTimes["global"]*1000)."ms (";
  $queryTimesHtmlList = array();
  foreach ($queriesTimes as $queryName => $queryTime) {
    if ($queryName != "global")
      $queryTimesHtmlList[] = $queryName." en ".sprintf("%.03f",$queryTime*1000)."ms";
  }
  $queryTimesHtml .= implode(", ",$queryTimesHtmlList).")";
}
$title = "Recherche";
$navBar = "Résultats de la recherche";

require(ADMIN."head.php");
// update families
  if ($userPerms->has("m-prod--sm-products","e")) {
  // Processing products family change
   if(!empty($_GET["selectList"]) && !empty($selectedFamily))
    foreach($selectedList as $selectedElem) {
      list($pdtID) = explode(",",$selectedElem);
      $pdtID = (int)$pdtID;
      $selectedFamily = ((int)$selectedFamily) > 0 ? (int)$selectedFamily : 0;
      $res = $db->query("select count(idProduct) FROM products_families WHERE idProduct = ".$pdtID, __FILE__, __LINE__);
      $nbFamiliesPerProduct = $db->fetch($res);
      if($nbFamiliesPerProduct[0] > 1){
        $db->query("delete FROM products_families  WHERE idProduct = ".$pdtID, __FILE__, __LINE__);
        $db->query("INSERT INTO products_families (idProduct, idFamily) VALUES ( ".$pdtID.", ".$selectedFamily.")", __FILE__, __LINE__);
      }elseif($nbFamiliesPerProduct[0] == 1)
        $db->query("UPDATE products_families SET idFamily = ".$selectedFamily." WHERE idProduct = ".$pdtID, __FILE__, __LINE__);
      else
        $db->query("INSERT INTO products_families (idProduct, idFamily) VALUES ( ".$pdtID.", ".$selectedFamily.")", __FILE__, __LINE__);
    }
}

?>

<link type="text/css" rel="stylesheet" href="<?php echo ADMIN_URL ?>ressources/css/HN.Mods.DialogBox.blue.css"/>
<script type="text/javascript" src="products/Classes.js"></script>
<script type="text/javascript" src="<?php echo ADMIN_URL ?>ressources/js/ManagerFunctions.js"></script>
<style type="text/css">
#toggleSelectFamilies {cursor: pointer;}
#toggleSelectFamilies:hover {text-decoration: underline;}

/* Family confimation layer */
#FamilyChangeWrap {  position: absolute; top: 608px; left: 20px; display: none; }
.FamilyChangeLayer{ width: 788px; height: 404px;}
#FamilyChangeConfirmShad { z-index: 3; position: absolute; top: 5px; left: 5px; background-color: #000000; display: none;  filter: Alpha (opacity=50, finishopacity=50, style=1) -moz-opacity:.50; opacity:.50; }
#FamilyChangeConfirm { z-index: 4; position: absolute; top: 0px; left: 0px; display: none; border: 2px solid #999999; }
.contentConfirmFamilyChange{margin: 10px}

/* Family Selection Window */
#FamilySelectionWindowShad { z-index: 3; position: absolute; top: 613px; left: 25px; width: 788px; height: 404px; background-color: #000000; visibility: hidden; filter: Alpha (opacity=50, finishopacity=50, style=1) -moz-opacity:.50; opacity:.50; }
#FamilySelectionWindow { z-index: 4; position: absolute; top: 608px; left: 20px; border: 2px solid #999999; visibility: hidden; }

.window-silver { padding: 5px; font: normal 11px Tahoma, Arial, Helvetica, sans-serif; }
.window-silver a { color: #000000; font-weight: normal; }
.window-silver a:hover { font-weight: normal; }

.tab_menu { height: 24px; padding: 0 5px 0 5px; position: relative; top: 1px; }

.tab_menu .tab { float: left; width: 118px; text-align: center; cursor: default; }

.tab_menu .tab_lb_i, .tab_menu .tab_lb_a, .tab_menu .tab_lb_s, .tab_menu .tab_rb_i, .tab_menu .tab_rb_a, .tab_menu .tab_rb_s { float: left; width: 4px; height: 23px; }
.tab_menu .tab_lb_i { background : url(tab-left-border.gif) repeat-x; }
.tab_menu .tab_lb_a { background : url(tab-active-left-border.gif) repeat-x; }
.tab_menu .tab_rb_i { background : url(tab-right-border.gif) repeat-x; }
.tab_menu .tab_rb_a { background : url(tab-active-right-border.gif) repeat-x; }
.tab_menu .tab_lb_s { height: 24px; background : url(tab-active-left-border.gif) repeat-x;}
.tab_menu .tab_rb_s { height: 24px; background : url(tab-active-right-border.gif) repeat-x; }

.tab_menu .tab_bg_i, .tab_menu .tab_bg_a, .tab_menu .tab_bg_s { height: 17px; float: left; width: 90px; text-align: left; color: #000000; padding: 6px 10px 0px 10px; white-space: nowrap; }
.tab_menu .tab_bg_i { background: url(tab-bg.gif) repeat-x; }
.tab_menu .tab_bg_a { background: url(tab-active-bg.gif) repeat-x; }
.tab_menu .tab_bg_s { height: 18px; background: url(tab-active-bg.gif) repeat-x; font-weight: bold; }

.menu-below { border: 1px solid #808080; height: 2px; font-size: 0; border-bottom: none; background-color: #D8D4CD; }
.main { border: 1px solid #808080; background-color: #DEDCD6; }

.search_menu { width: 516px; cursor: default; padding: 3px 6px; border-bottom: 1px solid #808080; display: block; float: left}
.search_menu span { border: 1px solid #DEDCD6; padding: 2px 5px; outline: none; }
.search_menu span.over { border-color: #FFFFFF #808080 #808080 #FFFFFF; }
.search_menu span.down { border-color: #808080 #FFFFFF #FFFFFF #808080; }
.search_menu span.selected { border-color: #808080 #FFFFFF #FFFFFF #808080; }

.body { padding: 2px 4px; background-color: #DEDCD6; border-top: 1px solid #FFFFFF; clear: left}
.body .colg { float: left; width: 258px; margin-right: 5px; }
.body .colc { float: left; width: 257px; }
.body .col-title { cursor: default; font-weight: bold; margin: 2px; }
.body .colg .list { width: 252px; height: 298px; background-color: #FFFFFF; border: 2px inset #808080; margin: 0; padding: 1px; list-style-type: none; overflow: auto; }
.body .colg .list li { cursor: default; white-space: nowrap; }
.body .colg .list li.over { background-color: #316AC5; color: #FFFFFF; }
.body .colg .list li.selected { background-color: #0C266C; color: #FFFFFF; }

.body .colc .infos { position: relative; height: 290px; background-color: #FFFFFF; border: 2px inset #808080; padding: 5px; }

.body .colc .select_label { padding: 0 5px 0 20px; }
.body .colc input.button1 { top: 250px; left: 5px; width: 243px; }
.body .colc input.button2 { top: 275px; left: 5px; width: 243px; }

/* Common */
.app-form-edit-button { cursor: pointer; margin: 0 0 -3px 5px }
.app-table-add-icon { cursor: pointer; }
.field-fixed { height: 15px; padding: 0 0 0 3px; border: 1px solid #cccccc; background: #ffffff; font-size: 12px }
.fl { float: left }
.fr { float: right }
.w-100 { width: 100px }
.w-150 { width: 150px }
.w-200 { width: 200px }
.w-250 { width: 250px }
.w-300 { width: 300px }
.w-350 { width: 350px }
.w-400 { width: 400px }
.w-450 { width: 450px }
.w-500 { width: 500px }

/* HN.FamiliesBrowser */
.family-window { width: 784px; height: 400px; background-color: #FCFCFF; }
.family-window-bg {  }
.family-window-bg .menu { width: 780px; height: 16px; text-align: center; background: #A00100; font: 13px Arial, Helvetica, sans-serif; color: white; padding: 6px 2px; margin: 4px 0; }
.family-window-bg .menu a { font-weight: normal; background: #cd2d2c; color: white; text-decoration: none; padding: 2px 3px; }
.family-window-bg .menu a:hover { background: #FFFFFF; color: #A00100; }
.family-window-bg .menu a.current { background: #A00100; color: #FFFFFF; outline: none; }
.family-window-bg .menu a.current:hover { text-decoration: underline; }

.family-window-bg .cols { width: 785px; height: 325px; background-color: #FFFFFF; overflow: scroll; }
.family-window-bg .colg { float: left; width: 190px; text-align: left; padding-bottom: 2px; background-color: #FFFFFF; }
.family-window-bg .colg .titre { text-align: center; display: block; background: #637382; font: bold 12px Arial, Helvetica, sans-serif; letter-spacing: 1px; text-transform: uppercase; color: white; padding: 5px 13px; }
.family-window-bg .colg .sf { padding-top: 3px; }
.family-window-bg .colg .sf a { display: block; color: #3d4b58; text-decoration: none; font: 11px Arial, Helvetica, sans-serif; letter-spacing: 1px; font-weight: bold; padding: 3px 0 5px 20px; background: url(../ressources/flecheUn.gif) no-repeat left bottom; }
.family-window-bg .colg .sf a:hover { text-decoration: underline; }
.family-window-bg .colg .sf a.currentFolded { background-color: #FFDD82; }
.family-window-bg .colg .sf a.currentUnfolded { background: #FFDD82 url(../ressources/flecheDeux.gif) no-repeat left bottom; }
.family-window-bg .colg .sf a.notCurrentUnfolded { background: url(../ressources/flecheDeux.gif) no-repeat left bottom; }

.family-window-bg .colc { float: left; width: 570px; text-align: left; background-color: #FFFFFF; padding: 0 4px; }
.family-window-bg .colc .ssf { background: url(../ressources/flecheTrois.gif) no-repeat left bottom; padding-bottom: 3px; }
.family-window-bg .colc .ssf a { float: left; width: 264px; display: block; color: #3D4B58; text-decoration: none; font: 11px Arial, Helvetica, sans-serif; letter-spacing: 1px; padding: 0 0 0 10px; margin: 2px 0 1px 8px; border-left: solid 2px #889c48; background: #f6f6f6; }
.family-window-bg .colc .ssf a.current { background-color: #C6D6D8; padding: 0 0 0 10px; }
.family-window-bg .colc .ssf a:hover { background-color: #C6D6D8; text-decoration: none; }
.family-window-bg .colc .ssf a.current:hover { background-color: #889C48; color: #FFFFFF; }
.family-window-bg .colc h1 { font: bold 12px Arial, Helvetica, sans-serif; letter-spacing: 1px; color: #272727; text-decoration: none; padding: 8px 2px; border-bottom: solid 3px #c6d6d8; margin: 0; }

#references { width: 100% !important; }
#references table { width: 100%; border-collapse: collapse }
#references table td { border: 1px solid #000000; }
#references table td.isCat3SA { background: #80FF90!important }
#references table td.isCat3SA input { background: #80FF90!important }
#references table td.isCat3SA:hover { background: #316ac5!important }
#references table td.isCat3SA:hover input { background: #316ac5!important }
input.ref-col { width: 90%; }
#references center { text-align: left; }
#references .intitule { background-color: #E9EFF8;}
</style>

<div id="FamilySelectionWindow"></div>
<div id="FamilyChangeWrap" class="FamilyChangeLayer">
  <div id="FamilyChangeConfirm" class="FamilyChangeLayer family-window">
    <div class="window_title_bar">
      <div>
        <div class="move_img"></div>
        <div class="titletext">Confirmation</div>
        <div style="clear: both;"></div>
      </div>
    </div>
    <div class="family-window-bg">
      <div class="cols"></div>
    </div>
  </div>
  <div id="FamilyChangeConfirmShad" class="FamilyChangeLayer"></div>
</div>

<script type="text/javascript">
var __SID__ = '<?php echo $sid ?>';
var __ADMIN_URL__ = '<?php echo ADMIN_URL ?>';
var __MAIN_SEPARATOR__ = '<?php echo __MAIN_SEPARATOR__ ?>';
var __ERROR_SEPARATOR__ = '<?php echo __ERROR_SEPARATOR__ ?>';
var __ERRORID_SEPARATOR__ = '<?php echo __ERRORID_SEPARATOR__ ?>';
var __OUTPUT_SEPARATOR__ = '<?php echo __OUTPUT_SEPARATOR__ ?>';
var __OUTPUTID_SEPARATOR__ = '<?php echo __OUTPUTID_SEPARATOR__ ?>';
var __DATA_SEPARATOR__ = '<?php echo __DATA_SEPARATOR__ ?>';
var __IP_NOT_VALID__ = '<?php echo __IP_NOT_VALID__ ?>';
var __IP_VALID__ = '<?php echo __IP_VALID__ ?>';
var __IP_FINALIZED__ = '<?php echo __IP_FINALIZED__ ?>';

// FAMILIES //
<?php
$mts["JS CAT LIST"]["start"] = microtime(true);

$families = array();
$families[0]['name'] = '';
$families[0]['ref_name'] = '';
$families[0]['idParent'] = 0;

$result = & $handle->query("select f.id, fr.name, fr.ref_name, f.idParent from families f, families_fr fr where f.id = fr.id", __FILE__, __LINE__);
while ($family = & $handle->fetchAssoc($result))
{
	$families[$family['id']]['name'] = $family['name'];
	$families[$family['id']]['ref_name'] = $family['ref_name'];
	$families[$family['id']]['idParent'] = $family['idParent'];
	if (!isset($families[$family['idParent']]['nbchildren']))
		$families[$family['idParent']]['nbchildren'] = 1;
	else
		$families[$family['idParent']]['nbchildren']++;
	$families[$family['idParent']]['children'][$families[$family['idParent']]['nbchildren']-1] = $family['id'];
}

?>
// TODO intégrer le get des familles en ajax dans l'objet FamiliesBrowser
var families = [];
var familiesIndexByName = [];
var familiesIndexByRefName = [];
var name = 0; var ref_name = 1; var idParent = 2; var nbchildren = 3; var children = 4;

function fam_sort_ref_name(a, b)
{
	if (families[a][ref_name] > families[b][ref_name]) return 1;
	if (families[a][ref_name] < families[b][ref_name]) return -1;
	return 0;
}

<?php
foreach ($families as $id => $fam)
{
	print 'families[' . $id . '] = ["' . str_replace('"', '\"', $fam['name']) . '", "' . $fam['ref_name'] . '", ' . $fam['idParent'] . ', ';
	if (isset($fam['nbchildren']))
	{
		print $fam['nbchildren'] . ', [' . $fam['children'][0];
		for ($i = 1; $i < $fam['nbchildren']; $i++)
			print ", " . $fam['children'][$i];
		print "]";
	}
	else
	{
		print "0, []";
	}
	print  ']; ';
	//print 'familiesIndexById[' . $id . '] = ' . $id . '; ';
	print 'familiesIndexByName["' . str_replace('"', '\"', $fam['name']) . '"] = ' . $id . '; ';
	print 'familiesIndexByRefName["' . $fam['ref_name'] . '"] = ' . $id . ';';
	print "\n";
}
$mts["JS CAT LIST"]["end"] = microtime(true);
?>

// Product's main properties (namespace)
var PMP = {};
var selectedFamily = '';
/* Family selection window */
PMP.fb = new HN.FamiliesBrowser();
PMP.fb.setID("FamilySelectionWindow");
PMP.fb.Build();
PMP.fb.mod = "add";


PMP.fsw = new HN.Window();
PMP.fsw.setID("FamilySelectionWindow");
PMP.fsw.setTitleText("Choisir une famille");
PMP.fsw.setMovable(true);
PMP.fsw.showCancelButton(true);
PMP.fsw.showValidButton(true);
PMP.fsw.setValidFct(function() {
	var family = PMP.fb.getCurFam();
	if (family.id != 0)
	{
            selectedFamily = family.id;

            $("#selectedFamily").val(selectedFamily);
            PMP.fsw.Hide();
            
            $("#FamilyChangeWrap").css('top', ($("#FamilySelectionWindow").offset().top - 100));
            var FCC = '';
            if($('#selectedFamily').val() != ''){
              var prodList = new Array();
              var confirmFamilyButton = '';
              var liste = ''; //<ul>';
              $('input[name=select]:checked').each(function(index){
                prodList[index] = $(this).parent().parent().find("td.title a").text();
              });
              if(prodList.length != 0){
                var i=0;
//                for(i=0; i<prodList.length; i++){
//                  liste += '<li><b>'+prodList[i]+'</b></li>';
//                };
                liste += '<b>'+prodList.length+'</b>';
//                liste += '</ul>';
                confirmFamilyButton = '<input type="button" value="Confirmer" id="familyChangeConfirmButton" />';
              }else{
                liste += 'Aucun produit n\'est sélectionné</ul>';
              }
              FCC = '<span>Récapitulatif de l\'opération :</span><br /><br />Produits sélectionnés : '+liste+'<br /><br />Famille de destination : <b>'+family.name+'</b><br /><br />'+confirmFamilyButton+'<input type="button" value="Annuler" onClick="javascript:hideFamilyChangeWindow()" />';
            }else
              FCC = '<span>Vous n\'avez sélectionné aucun produit</span><input type="button" value="Annuler" onClick="javascript:hideFamilyChangeWindow()" />';
            $('#FamilyChangeConfirm').find('.cols').css({'height': '380px'});
            $('#FamilyChangeConfirm').find('.cols').html('<div class="contentConfirmFamilyChange">'+FCC+'</div>');
            $("#FamilyChangeWrap").show();
            $("#FamilyChangeConfirm").show();
            $("#FamilyChangeConfirmShad").show();

            $('#familyChangeConfirmButton').bind(
              'click', function(){
              $results = $("form[name='results']");
              var selectList = [];
              $("table.php-list input[name='select']:checked").each(function(){
                 selectList.push($(this).parent().parent().find("td.pdtID a").text());
              });
              $results.find("input[name='selectList']").val(selectList.join("|"));
              $results.submit();
            });
	}
});

PMP.fsw.setShadow(true);
PMP.fsw.Build();
</script>


<div class="titreStandard">Résultat de la recherche</div>

<br />
<div class="bg" style="background: #f8f6f6">
  <div id="search-results">
    <?php if (DEBUG) echo $queryTimesHtml."<br/>" ?>
	<?php
	
		try{
			//From the String we gonna look for the 3rd Family informations "id" "idParent"
			//From the 3rd Family informations we gonna look for the 2nd family informations "id", "FR name", "idParent"
			//From the 2nd Family informations we gonna look for the 1st family informations "id", "FR name"
		
			//***
			//Search for the 3rd Family informations
			//***
			
			$third_family_results_array = array();
			
			if ($numericSearch) {
				$res_third_family_results = $db->query("SELECT 
															families_fr.id AS fam_fr_id,
															families_fr.name AS fam_fr_name,
															families.id AS families_id_rd,
															families.idParent AS families_id_parent_rd
														FROM  
															`families_fr` AS families_fr,
															`families` AS families
														WHERE  
															families_fr.`id`=".addslashes($q)." 
														AND
															families.id=families_fr.id
												");	
			}else{	
				$res_third_family_results = $db->query("SELECT 
															families_fr.id AS fam_fr_id,
															families_fr.name AS fam_fr_name,
															families.id AS families_id_rd,
															families.idParent AS families_id_parent_rd
														FROM  
															`families_fr` AS families_fr,
															`families` AS families
														WHERE  
															families_fr.`name` LIKE  '".addslashes($q)."'
														AND
															families.id=families_fr.id
												");	
			}

									
			$third_family_results_array = $db->fetchAssoc($res_third_family_results);
			
			//echo('<br /><b>Infos Second:</b> fam_fr_id=> '.$third_family_results_array['fam_fr_id'].' ** ');
			//echo('<br /> fam_fr_name=> '.$third_family_results_array['fam_fr_name'].' ** ');
			//echo('<br /> families_id_rd=> '.$third_family_results_array['families_id_rd'].' ** ');
			//echo('<br /> families_id_parent_rd=> '.$third_family_results_array['families_id_parent_rd'].' ** ');
			
			//***
			//Search for the 2nd Family informations
			//***
			
			$second_family_results_array = array();
			$res_second_family_results = $db->query("SELECT 
														families_fr.id AS fam_fr_id,
														families_fr.name AS fam_fr_name,
														families.id AS families_id_nd,
														families.idParent AS families_id_parent_nd
													FROM  
														`families_fr` AS families_fr,
														`families` AS families
													WHERE  
														families_fr.id=".$third_family_results_array['families_id_parent_rd']."
													AND	
														families.id=".$third_family_results_array['families_id_parent_rd']."
													");	
									
			$second_family_results_array = $db->fetchAssoc($res_second_family_results);
			
			//echo('<br /><br /><b>Infos Second:</b> fam_fr_id=> '.$second_family_results_array['fam_fr_id'].' ** ');
			//echo('<br /> fam_fr_name=> '.$second_family_results_array['fam_fr_name'].' ** ');
			//echo('<br /> families_id_nd=> '.$second_family_results_array['families_id_nd'].' ** ');
			//echo('<br /> families_id_parent_rd=> '.$second_family_results_array['families_id_parent_nd'].' ** ');
			
			//***
			//Search for the 1st Family informations
			//***
			
			$first_family_results_array = array();
			$res_first_family_results = $db->query("SELECT 
														families_fr.id AS fam_fr_id,
														families_fr.name AS fam_fr_name,
														families.id AS families_id_st,
														families.idParent AS families_id_parent_st
													FROM  
														`families_fr` AS families_fr,
														`families` AS families
													WHERE  
														families_fr.id=".$second_family_results_array['families_id_parent_nd']."
													AND	
														families.id=".$second_family_results_array['families_id_parent_nd']."
													");	
								
			$first_family_results_array = $db->fetchAssoc($res_first_family_results);
			
			//echo('<br /><br /><b>Infos First:</b> fam_fr_id=> '.$first_family_results_array['fam_fr_id'].' ** ');
			//echo('<br /> fam_fr_name=> '.$first_family_results_array['fam_fr_name'].' ** ');
			//echo('<br /> families_id_nd=> '.$first_family_results_array['families_id_st'].' ** ');
			//echo('<br /> families_id_parent_rd=> '.$first_family_results_array['families_id_parent_st'].' ** ');
			
			echo('<div id="search_families_tree">');
				$st_family_url_show	= str_replace(' ','+',$first_family_results_array['fam_fr_name']);
				$nd_family_url_show	= str_replace(' ','+',$second_family_results_array['fam_fr_name']);
				$rd_family_url_show	= str_replace(' ','+',$third_family_results_array['fam_fr_name']);
				
				/*
				echo ('<h2 class="family_show_stlevel"><a href="/fr/manager/search.php?search_type=2&search='.$st_family_url_show.'" target="_blank">'.$first_family_results_array['fam_fr_name'].'</a></h2>');
				echo ('<h2 class="family_show_ndlevel"><a href="/fr/manager/search.php?search_type=2&search='.$nd_family_url_show.'" target="_blank">'.$second_family_results_array['fam_fr_name'].'</a></h2>');
				echo ('<h2 class="family_show_rdlevel"><a href="/fr/manager/search.php?search_type=2&search='.$rd_family_url_show.'" target="_blank">'.$third_family_results_array['fam_fr_name'].'</a></h2>');
				*/
				
				echo ('<h2 class="family_show_stlevel">'.$first_family_results_array['fam_fr_name'].'</h2>');
				echo ('<h2 class="family_show_ndlevel">'.$second_family_results_array['fam_fr_name'].'</h2>');
				echo ('<h2 class="family_show_rdlevel">'.$third_family_results_array['fam_fr_name'].' - ID '.$third_family_results_array['fam_fr_id'].'</h2>');
				
			echo('<div>');
		}catch(Exception $e){
		
		}
	?>
	
	<br />
    <h1><strong><?php echo Utils::word_results($results["count"]) ?></strong> pour `<strong><?php echo to_entities($q) ?></strong>`</h1>
<?php if ($results["count"] > 0) { ?>
    <form name="results" method="get" action="search.php">
      <div>
        <input type="hidden" name="search" value="<?php echo $q ?>" />
        <input type="hidden" name="search_type" value="<?php echo $search_type ?>" />
        <input type="hidden" name="page" value="<?php echo $page ?>" />
        <input type="hidden" name="lastpage" value="<?php echo $lastpage ?>" />
        <input type="hidden" name="sort" value="<?php echo $sort ?>" />
        <input type="hidden" name="lastsort" value="<?php echo $lastsort ?>" />
        <input type="hidden" name="sortway" value="<?php echo $sortway ?>" />
        <input type="hidden" name="selectList" value="" />
        <input type="hidden" id="selectedFamily" name="selectedFamily" value="" />
      </div>
    </form>
  <?php switch ($search_type) {
    case "1" :
    case "2" : ?>
    <table class="php-list" cellspacing="0" cellpadding="0">
      <thead>
        <tr>
          <th style="width: auto">Image</th>
          <th style="width: 20%"><a href="javascript: document.results.sort.value = 'name'; document.results.submit();">Nom</a></th>
          <th style="width: 5%"><a href="javascript: document.results.sort.value = 'id'; document.results.submit();">ID</a></th>
          <th style="width: 20%"><a href="javascript: document.results.sort.value = 'fastdesc'; document.results.submit();">Description rapide</a></th>
          <th style="width: 5%"><a href="javascript: document.results.sort.value = 'cat_id'; document.results.submit();">Famille</a></th>
          <th style="width: auto">Réf. Four. 1</th>
          <th style="width: auto"><a href="javascript: document.results.sort.value = 'price'; document.results.submit();">Prix</a></th>
          <th style="width: 15%"><a href="javascript: document.results.sort.value = 'adv_name'; document.results.submit();">Annonceur/Fournisseur</a></th>
          <th style="width: auto"><a href="javascript: document.results.sort.value = 'adv_cat'; document.results.submit();">Catégorie</a></th>
          <th style="width: auto"><a href="javascript: document.results.sort.value = 'hits'; document.results.submit();">Vues 60 derniers jours</a></th>
          <th style="width: auto"><a href="javascript: document.results.sort.value = 'leads'; document.results.submit();">Nombre de lead</a></th>
          <th style="width: auto"><a href="javascript: document.results.sort.value = 'orders'; document.results.submit();">Nombre de commandes</a></th>
          <th style="width: auto"><a href="javascript: document.results.sort.value = 'transfo'; document.results.submit();">Taux de transfo</a></th>
          <th style="width: auto">Cht.&nbsp;fam<div id="toggleSelectFamilies">Tout</div></th>
          <!--<th style="width: 50px"><a href="javascript: document.results.sort.value = 'score'; document.results.submit();">Score</a></th>-->
          <th style="width: auto"></th>
        </tr>
      </thead>
      <tbody>
      <?php foreach($results["data_row"] as $result) {
			
			$sql_hits = "SELECT COUNT(sh.idProduct) as total 
							FROM stats_hit as sh WHERE
							FROM_UNIXTIME(timestamp) BETWEEN DATE_SUB(CURDATE(), INTERVAL 60 DAY) AND CURDATE()
							AND sh.idProduct = '".$result["idProduct"]."'
							AND adresse_ip !='46.218.144.64'
							AND adresse_ip !='124.244.241.251'
							AND adresse_ip !='41.141.250.175'
							GROUP BY sh.idProduct";
			$req_hits = mysql_query($sql_hits);
			$data_hits= mysql_fetch_assoc($req_hits);
		  
              $fo_pdt_url = URL."produits/".$result["cat_id"]."-".$result["idProduct"]."-".$result["ref_name"].".html";
              $fo_pdt_pic_url = is_file(PRODUCTS_IMAGE_INC."thumb_small/".$result["idProduct"]."-1.jpg") ? PRODUCTS_IMAGE_SECURE_URL."thumb_small/".$result["idProduct"]."-1.jpg" : PRODUCTS_IMAGE_SECURE_URL."no-pic-thumb_small.gif";
              $bo_pdt_url = ADMIN_URL."products/edit.php?id=".$result["idProduct"];
              $bo_adv_url = ADMIN_URL."advertisers/edit.php?id=".$result["adv_id"];
              $bo_lead_create_url = ADMIN_URL."contacts/lead-create.php?pdtId=".$result["idProduct"] ?>
        <tr>
          <td><a href="<?php echo $bo_pdt_url ?>" title="Voir la fiche produit"><img src="<?php echo $fo_pdt_pic_url ?>" alt=""></a></td>
          <td class="title"><a href="<?php echo $bo_pdt_url ?>"><?php echo $result["name"] ?></a></td>
          <td class="pdtID"><a href="<?php echo $bo_lead_create_url ?>"><?php echo $result["idProduct"] ?></a></td>
          <td><?php echo $result["fastdesc"] ?></td>
          <td>
			<?php 
				
				$link_search_this_family	= str_replace(' ','+',$result["cat_name"]);
				echo ('<a href="/fr/manager/search.php?search='.$link_search_this_family.'&search_type=2" target="_blank">'.$result["cat_name"].'</a>'); //echo(" (".$result["cat_id"].")"); 
			
			?>
			
		  </td>
          <td>
            <?php if (!empty($result["ref_idtc"])) { ?>
              <?php echo $result["ref_refSupplier"] ?>
            <?php } else { ?>
              N.A.
            <?php } ?>
          </td>
          <td>
            <?php echo $result["price"] ?>
          </td>
          <td class="adv_name"><a href="<?php echo $bo_adv_url ?>"><?php echo $result["adv_name"]?></a></td>
          <td class="adv_cat"><?php echo $adv_cat_list[$result["adv_cat"]]["name"]?></td>
          <?php
					if(empty($data_hits["total"])){
						echo '<td>0</td>';
					}else {
						echo '<td>'.$data_hits["total"].'</td>';
					}
			?>
          <td><?php echo $result["leads"]?></td>
          <td><?php echo $result["orders"]?></td>
          <td><?php echo $result["transfo"]?>%</td>
          <!--<td><?php echo $result["score"]?></td>-->
          <td><input type="checkbox" name="select" /></td>
          <td><a href="<?php echo $fo_pdt_url ?>" target="_blank"><img src="<?php echo ADMIN_URL ?>ressources/icons/monitor_go.png" alt="" title="Voir la fiche en ligne"></a></td>
        </tr>
      <?php } ?>
      </tbody>
    </table>
    <?php break ?>

    <?php case "3" : ?>
    <table class="php-list" cellspacing="0" cellpadding="0">
      <thead>
        <tr>
          <th style="width: 10%"><a href="javascript: document.results.sort.value = 'adv_id'; document.results.submit();">ID</a></th>
          <th style="width: 50%"><a href="javascript: document.results.sort.value = 'adv_name'; document.results.submit();">Nom</a></th>
          <th style="width: 30%"><a href="javascript: document.results.sort.value = 'adv_cat'; document.results.submit();">Catégorie</a></th>
          <th style="width: 10%"><a href="javascript: document.results.sort.value = 'adv_pdt_count'; document.results.submit();">Nombre de produits</a></th>
        </tr>
      </thead>
      <tbody>
      <?php foreach($results["data_row"] as $result) {
              $bo_adv_url = ADMIN_URL."advertisers/edit.php?id=".$result["adv_id"] ?>
        <tr>
          <td><?php echo $result["adv_id"]?></td>
          <td class="adv_name"><a href="<?php echo $bo_adv_url ?>"><?php echo $result["adv_name"]?></a></td>
          <td class="adv_cat"><?php echo $adv_cat_list[$result["adv_cat"]]["name"]?></td>
          <td><?php echo $result["adv_pdt_count"]?></td>
        </tr>
      <?php } ?>
      </tbody>
    </table>
    <?php break ?>

  <?php } // end switch ?>
<?php } // end ($results["count"] > 0)?>

  <br/>
  <?php if($search_type == 1 || $search_type = 2){ ?>
  <input id="families-pdtList" type="button" value="Transfert vers famille" />
  <?php } ?>
  <br/>
  <script type="text/javascript">
   
$(function(){
//deactivation of the bottom list form
//  searchAC2 = new HN.UI.AutoCompletion($("#search-results .search-box .search").get(0));

	$results = $("form[name='results']");
        $("#families-pdtList").click(function(){

            PMP.fsw.Show();
            PMP.fb.Build();
            PMP.fb.mod='add';
            $("#FamilySelectionWindow").css('top', ($("#families-pdtList").offset().top - 600));
            $("#FamilySelectionWindowShad").css('top', ($("#families-pdtList").offset().top - 594));
	});

        $('#toggleSelectFamilies').click(function(){
          var text = $(this).text()
           $(this).text(text == 'Aucun' ? 'Tout': 'Aucun');
           if(text == 'Aucun'){
              $('input[name=select]:checked').removeAttr('checked');
           }else{
              $('input[name=select]').attr('checked', 'checked');
           }
        });
        $('#FamilyChangeWrap').draggable({ handle: '.window_title_bar',  containment: '#page-content'});

});
function hideFamilyChangeWindow(){
  $("#FamilyChangeWrap").hide();
  $("#FamilyChangeConfirm").hide();
  $("#FamilyChangeConfirmShad").hide();
}
  </script>
<!--   deactivated form due to upper and footer bars search forms

<form action="search.php" method="get">
      <div class="search-box">
        Nouvelle Recherche :<br/>
        <div class="input-box"><input type="text" name="search" class="search" value="<?php //echo $q ?>" title="Entrez ici votre recherche"/><input type="submit" class="go" value="" title="Lancer la recherche !"/></div>
        <div class="options">
          <div class="title">Options de recherche</div>
           <?php //if ($userPerms->has($fntByName["m-prod--sm-products"], "r")) { ?>
            <label>Produit</label><input type="radio" name="search_type" value="1" <?php //if ($search_type == "1") { ?>checked="checked"<?php //} ?> />
           <?php //} ?>
           <?php //if ($userPerms->has($fntByName["m-prod--sm-categories"], "r")) { ?>
            <label>Famille</label><input type="radio" name="search_type" value="2" <?php //if ($search_type == "2") { ?>checked="checked"<?php //} ?> />
           <?php //} ?>
           <?php //if ($userPerms->has($fntByName["m-prod--sm-partners"], "r")) { ?>
            <label>Partenaire</label><input type="radio" name="search_type" value="3" <?php //if ($search_type == "3") { ?>checked="checked"<?php //} ?> />
           <?php //} ?>
          <div class="zero"></div>
        </div>
        <div class="zero"></div>
      </div>
    </form>-->
  </div>
</div>
<?php
require(ADMIN."tail.php");
?>
