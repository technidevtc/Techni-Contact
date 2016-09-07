<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

define("SEARCH", true);
define("MAX_RESULTS", 1000);

include LANG_LOCAL_INC."includes-".DB_LANGUAGE."_local.php";
include LANG_LOCAL_INC."www-".DB_LANGUAGE."_local.php";
//include LANG_LOCAL_INC."common-".DB_LANGUAGE."_local.php";
//include LANG_LOCAL_INC."infos-".DB_LANGUAGE."_local.php";

$db = DBHandle::get_instance();

$q = isset($_GET["search"]) ? trim($_GET["search"]) : "";
$search_type = isset($_GET["search_type"]) ? trim($_GET["search_type"]) : ""; // let's keep the search type attribute in case of further demands

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
  $match_expr = "+*".$q."*";
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
      $terms[$i] = (strlen($terms[$i]) > 3 ? "+*" : "").Utils::get_singular($terms[$i])."* <<".$terms[$i]."*";
    }
    else {
      $terms[$i] = (strlen($terms[$i]) > 3 ? "+*" : "").$terms[$i]."*";
    }
    //if ($i == 0) $terms[$i] = ">".$terms[$i];
  }
  $match_expr = implode(" ", $terms);
}

if ($ql >= 3 || $numericSearch) {
  
  $results = array("data_row" => array(), "data_col" => array(), "count" => 0);
//  $queries = array();
  
  switch ($search_type) {

    
    case "2" :
      if ($numericSearch) { // numeric search
        
        // generating sql intervals for cat id
        $sql_cat_id_intervals = array("ffr.id = ".$num_intervals[0]);
        for ($k=1; $k < $num_intervals_len-(GBL_MAX_ID_LEN-PDT_MAX_ID_LEN); $k++)
          $sql_cat_id_intervals[] = "(ffr.id >= ".$num_intervals[$k][0]." AND ffr.id <= ".$num_intervals[$k][1].")";
        $sql_cat_id_intervals = implode(" OR ", $sql_cat_id_intervals);
        
        // search in category id
        $query = "
          SELECT ".
        "f.id, f.idParent, ".
            "ffr.id AS cat_id, ffr.name AS cat_name, ffr.ref_name AS cat_ref_name , count(pfr.id) as nbr_active_products ".
        "FROM families f ".
          "INNER JOIN families_fr ffr ON f.id = ffr.id  ".
          "INNER JOIN products_families pf on pf.idFamily = f.id ".
          "INNER JOIN products_fr pfr on pf.idProduct = pfr.id and pfr.active = 1 ".
          "WHERE ".$sql_cat_id_intervals." ".
          "group by f.id ".
          " order by ffr.name LIMIT 0,".MAX_RESULTS;
      }
      else { // name search
        $query = "
          SELECT ".
        "f.id, f.idParent, ".
            "ffr.id AS cat_id, ffr.name AS cat_name, ffr.ref_name AS cat_ref_name , count(pfr.id) as nbr_active_products ".
                "FROM families f ".
          "INNER JOIN families_fr ffr ON f.id = ffr.id ".
          "INNER JOIN products_families pf on pf.idFamily = f.id ".
          "INNER JOIN products_fr pfr on pf.idProduct = pfr.id and pfr.active = 1 ".
          "WHERE MATCH (ffr.name) AGAINST ('".$db->escape($match_expr)."' IN BOOLEAN MODE) ".
          "group by f.id ".
          "order by ffr.name LIMIT 0,".MAX_RESULTS;
      }
      break;
    
  }

  $queriesTimes = array("global" => 0);
//  foreach ($queries as $queryName => $query) {

    $startTime = microtime(true);
//    if (is_string($query)) {
      $resource = $db->query($query, __FILE__, __LINE__);
//    }
//    elseif(is_array($query)) {
//      $query2 = $query[0];
//      for ($i=1,$l=count($query); $i<$l; $i++) {
//        $resource = $db->query($query2, __FILE__, __LINE__);
//        while ($row = $db->fetch($resource))
//          $rows[] = $row[0];
//        $query2 = str_replace("%PREV_QUERY_RESULTS%", "'".implode("','",$rows)."'", $query[$i]);
//      }
//      $resource = $db->query($query2, __FILE__, __LINE__);
//
//    }
    $queriesTimes[$queryName] = microtime(true)-$startTime;
    $queriesTimes["global"] += $queriesTimes[$queryName];

    //pp($query2);

    while ($result = $db->fetchAssoc($resource)) {
      $results["data_row"][] = $result;
      foreach ($result as $k => $v)
        $results["data_col"][$k][] = $v;

      $results["count"]++;
      if ($results["count"] >= MAX_RESULTS) break;
    }

//  }
$families = array();
//var_dump($query, $result);exit;
// families level 2 request
$a = 0;
if(!$results["data_col"]['idParent']){
  $error = array('error' => 'Le terme saisi ne permet pas une recherche satisfaisante');
  mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $error);
  print json_encode($error);
  exit;
}
foreach ($results["data_col"]['idParent'] as $key => $idParent){
  $or = $a != 0 ? ' OR ' : ' WHERE ';
  $queryWhereLevel2 .= $or.' f.id = '.$idParent.' ';
  $a++;
}

$queryLevel2 = "SELECT ".
        "f.id, f.idParent, ".
            "ffr.id AS cat_id, ffr.name AS cat_name, ffr.ref_name AS cat_ref_name ".
                "FROM families f ".
          "INNER JOIN families_fr ffr ON f.id = ffr.id ".
          $queryWhereLevel2.
          " order by ffr.name LIMIT 0,".MAX_RESULTS;

  $resource2 = $db->query($queryLevel2, __FILE__, __LINE__);
  $level2 = array();

  while ($resultsLevel2 = $db->fetchAssoc($resource2))
    $level2[] = $resultsLevel2;

  // families level 1 request
$a = 0;
foreach ($level2 as $key => $familyLevel2){
  $or = $a != 0 ? ' OR ' : ' WHERE ';
  $queryWhereLevel1 .= $or.' f.id = '.$familyLevel2['idParent'].' ';
  $a++;
}

$queryLevel1 = "SELECT ".
        "f.id, f.idParent, ".
            "ffr.id AS cat_id, ffr.name AS cat_name, ffr.ref_name AS cat_ref_name ".
                "FROM families f ".
          "INNER JOIN families_fr ffr ON f.id = ffr.id ".
          $queryWhereLevel1.
          " order by ffr.name LIMIT 0,".MAX_RESULTS;

  $resource1 = $db->query($queryLevel1, __FILE__, __LINE__);
  $level1 = array();

  while ($resultsLevel1 = $db->fetchAssoc($resource1)){ //array re-ordering
    $collectionLevel2 = array();
    foreach($level2 as $keyLevel2 => $familyLevel2){
      if($familyLevel2['idParent'] == $resultsLevel1['id']){
        
        foreach($results["data_row"] as $keyLevel3 => $familyLevel3)
          if($familyLevel3['idParent'] == $familyLevel2['id'])
            $familyLevel2['children'][] = $familyLevel3;

          $collectionLevel2[] = $familyLevel2;
      }
    }
    $resultsLevel1['children'] = $collectionLevel2;
    $families['firstLevel'][] = $resultsLevel1;
  }

mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $families);
print json_encode($families);

}


?>

