<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

require(ADMIN . 'head.php');
define("MAX_RESULTS", 1000);
define("__BEGIN_TIME__", mktime(0,0,0,6,15,2011));
$db = DBHandle::get_instance();

if (!$userChildScript->get_permissions()->has("m-reporting--sm-production","e")) {
    print "Vous n'avez pas les droits adéquats pour réaliser cette opération";
    exit();
  }

if(!empty ($_GET['type']))
  $action = strtoupper (str_replace ('_', ' ', $_GET['type']));

if(!empty ($_GET['userId']) && preg_match("/^[1-9]{1}[0-9]{0,8}$/", $_GET['userId'])){
  $currUser = BOUser::get('id = '.$_GET['userId']);
  if(!empty ($currUser[0]['id']))
    $userName = $currUser[0]['name'];
  else{
    print "Utilisateur recherché incorrect";
    exit();
  }
}else{
  print "Utilisateur recherché incorrect";
  exit();
}

//$dateBegin = date(__BEGIN_TIME__);

$yearS = !empty ($_GET['yearS']) ? (int)trim($_GET['yearS']) : date("Y");
$monthS = !empty($_GET['monthS']) ? (int)trim($_GET['monthS']) : date("m");
$dayS = !empty($_GET['dayS']) ? (int)trim($_GET['dayS']) : date("d");
$yearS2 = !empty($_GET['yearS2']) ? (int)trim($_GET['yearS2']) : date("Y");
$monthS2 = !empty($_GET['monthS2']) ? (int)trim($_GET['monthS2']) : date("m");
$dayS2 = !empty($_GET['dayS2']) ? (int)trim($_GET['dayS2']) : date("d");
$yearE = !empty($_GET['yearE']) ? (int)trim($_GET['yearE']) : date("Y");
$monthE = !empty($_GET['monthE']) ? date('m',time(0,0,0,(int)trim($_GET['monthE']),0,0)) : date("m");
$dayE = !empty($_GET['dayE']) ? (int)trim($_GET['dayE']) : date("d");

$dates = $dayS.'/'.$monthS.'/'.$yearS;
if(!empty($_GET['dateFilterType']) && $_GET['dateFilterType'] == 'interval'){
  $dates = $dayS2.'/'.$monthS2.'/'.$yearS2.' au '.$dayE.'/'.$monthE.'/'.$yearE;
}

/*
 * result search
 */
$search_type = 1;
$results = array("data_row" => array(), "data_col" => array(), "count" => 0);
  $queries = array();

  if($_GET['type'] == 'modification-annonceur' || $_GET['type'] == 'creation-annonceur' || $_GET['type'] == 'modification-fournisseur' || $_GET['type'] == 'creation-fournisseur' || $_GET['type'] == 'total'){

    if(!empty ($_GET['prod']))
      $listeProduits = explode('|', $_GET['prod']);
    elseif($_GET['type'] == 'modification' || $_GET['type'] == 'creation'){
      print "Aucun produit disponible";
      exit();
    }

    if(!empty ($listeProduits)){
      $a = 0;
      foreach ($listeProduits as $produit){
        if(preg_match('/^[1-9]{1}[0-9]{0,8}$/', $produit)){
          $sql_pdt_id_intervals .= $a > 0 ? ' or ' : '';
          $sql_pdt_id_intervals .= "pfr.id = ".$produit;
        }
        $a++;
      }
    }
  }

  if( $_GET['type'] == 'suppression-annonceur'  || $_GET['type'] == 'suppression-fournisseur'  || $_GET['type'] == 'total' ){

    if(!empty ($_GET['entries']))
      $listeEntries = explode('|', $_GET['entries']);
    elseif($_GET['type'] == 'suppression'){
      print "Aucune entrée disponible";
      exit();
    }

    if(!empty ($listeEntries)){
      foreach ($listeEntries as $entry){
        if(preg_match('/^[1-9]{1}[0-9]{0,8}$/', $entry)){
          $where .= $a > 0 ? ' or ' : '';
          $where .= "id = ".$entry;
        }
        $a++;
      }

      $log_entries = Logs::get(array($where));
    }


  }
  
  if(!empty ($sql_pdt_id_intervals)){

//  switch ($search_type) {
//
//    case "1" :
        // generating sql intervals for pdt id
//        $sql_pdt_id_intervals = array("pfr.id = ".$num_intervals[0]);
//        for ($k=1; $k < $num_intervals_len-(GBL_MAX_ID_LEN-PDT_MAX_ID_LEN); $k++)
//          $sql_pdt_id_intervals[] = "(pfr.id >= ".$num_intervals[$k][0]." AND pfr.id <= ".$num_intervals[$k][1].")";
//        $sql_pdt_id_intervals = implode(" OR ", $sql_pdt_id_intervals);

        
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
//        $queries["idtc"] = "
//          SELECT
//            pf.idProduct, pfr.name, pfr.ref_name, pfr.fastdesc, p.price AS pdt_price,
//            ffr.id AS cat_id, ffr.name AS cat_name, ffr.ref_name AS cat_ref_name,
//            ps.hits, ps.orders, ps.leads,
//            a.id AS adv_id, a.nom1 AS adv_name, a.category AS adv_cat,
//            rc.id AS ref_idtc, rc.refSupplier AS ref_refSupplier, rc.price AS ref_price,
//            (MATCH (rc.id) AGAINST ('".$db->escape($match_expr)."' IN BOOLEAN MODE))*2 AS score
//          FROM products_fr pfr
//          INNER JOIN products p ON pfr.id = p.id
//          INNER JOIN products_families pf ON pfr.id = pf.idProduct
//          INNER JOIN families_fr ffr ON pf.idFamily = ffr.id
//          INNER JOIN advertisers a ON pfr.idAdvertiser = a.id
//          LEFT JOIN products_stats ps ON pfr.id = ps.id
//          INNER JOIN references_content rc ON pfr.id = rc.idProduct
//          WHERE MATCH (rc.id) AGAINST ('".$db->escape($match_expr)."' IN BOOLEAN MODE)
//          LIMIT 0,".MAX_RESULTS;
//
//      // search in ref fournisseur in both case
//      $queries["ref fournisseur"] = "
//        SELECT
//          pf.idProduct, pfr.name, pfr.ref_name, pfr.fastdesc, p.price AS pdt_price,
//          ffr.id AS cat_id, ffr.name AS cat_name, ffr.ref_name AS cat_ref_name,
//          ps.hits, ps.orders, ps.leads,
//          a.id AS adv_id, a.nom1 AS adv_name, a.category AS adv_cat,
//          rc.id AS ref_idtc, rc.refSupplier AS ref_refSupplier, rc.price AS ref_price,
//          MATCH (rc.refSupplier) AGAINST ('".$db->escape($match_expr)."' IN BOOLEAN MODE) AS score
//        FROM products_fr pfr
//        INNER JOIN products p ON pfr.id = p.id
//        INNER JOIN products_families pf ON pfr.id = pf.idProduct
//        INNER JOIN families_fr ffr ON pf.idFamily = ffr.id
//        INNER JOIN advertisers a ON pfr.idAdvertiser = a.id
//        LEFT JOIN products_stats ps ON pfr.id = ps.id
//        INNER JOIN references_content rc ON pfr.id = rc.idProduct
//        WHERE MATCH (rc.refSupplier) AGAINST ('".$db->escape($match_expr)."' IN BOOLEAN MODE)
//        LIMIT 0,".MAX_RESULTS;

//      break;
//
//    case "2" :
//
//        // generating sql intervals for cat id
//        $sql_cat_id_intervals = array("ffr.id = ".$num_intervals[0]);
//        for ($k=1; $k < $num_intervals_len-(GBL_MAX_ID_LEN-PDT_MAX_ID_LEN); $k++)
//          $sql_cat_id_intervals[] = "(ffr.id >= ".$num_intervals[$k][0]." AND ffr.id <= ".$num_intervals[$k][1].")";
//        $sql_cat_id_intervals = implode(" OR ", $sql_cat_id_intervals);
//
//        // search in category id
//        $queries["id famille"] = "
//          SELECT
//            pf.idProduct, pfr.name, pfr.ref_name, pfr.fastdesc, p.price AS pdt_price,
//            ffr.id AS cat_id, ffr.name AS cat_name, ffr.ref_name AS cat_ref_name,
//            ps.hits, ps.orders, ps.leads,
//            a.id AS adv_id, a.nom1 AS adv_name, a.category AS adv_cat,
//            rc.id AS ref_idtc, rc.refSupplier AS ref_refSupplier, rc.price AS ref_price,
//            1 AS score
//          FROM products_fr pfr
//          INNER JOIN products p ON pfr.id = p.id
//          INNER JOIN products_families pf ON pfr.id = pf.idProduct
//          INNER JOIN families_fr ffr ON pf.idFamily = ffr.id
//          INNER JOIN advertisers a ON pfr.idAdvertiser = a.id
//          LEFT JOIN products_stats ps ON pfr.id = ps.id
//          LEFT JOIN references_content rc ON pfr.id = rc.idProduct AND rc.classement = 1
//          WHERE ".$sql_cat_id_intervals."
//          ORDER BY ffr.id ASC
//          LIMIT 0,".MAX_RESULTS;
//
//      break;
//
//    case "3" :
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
//
//      break;
//  }

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

/*
 * fin result search
 */
  
$title = "Production [".$action."] ".$userName.' - '.$dates;// [« Création » ou « Modification » ou « total »] nom opérateur
$navBar = $title;


?>

<div class="titreStandard">Listing fiches</div>

<br />
<div class="bg" style="background: #f8f6f6">
  <div id="search-results" class="results">
    <h1><strong><?php echo $title ?></strong></h1>
    <form name="results" method="get" action="#">
    <input type="hidden" name="type" value="<?php echo $_GET['type'] ?>" />
    <input type="hidden" name="userId" value="<?php echo $_GET['userId'] ?>" />
    <input type="hidden" name="dayE" value="<?php echo $_GET['dayE'] ?>" />
    <input type="hidden" name="monthE" value="<?php echo $_GET['monthE'] ?>" />
    <input type="hidden" name="yearE" value="<?php echo $_GET['yearE'] ?>" />
    <input type="hidden" name="dayS2" value="<?php echo $_GET['dayS2'] ?>" />
    <input type="hidden" name="monthS2" value="<?php echo $_GET['monthS2'] ?>" />
    <input type="hidden" name="yearS2" value="<?php echo $_GET['yearS2'] ?>" />
    <input type="hidden" name="dayS" value="<?php echo $_GET['dayS'] ?>" />
    <input type="hidden" name="monthS" value="<?php echo $_GET['monthS'] ?>" />
    <input type="hidden" name="yearS" value="<?php echo $_GET['yearS'] ?>" />
    <input type="hidden" name="dateFilterType" value="<?php echo $_GET['dateFilterType'] ?>" />
    <input type="hidden" name="prod" value="<?php echo $_GET['prod'] ?>" />
    <input type="hidden" name="entries" value="<?php echo $_GET['entries'] ?>" />
    <input type="hidden" name="page" value="<?php echo $page ?>" />
    <input type="hidden" name="lastpage" value="<?php echo $lastpage ?>" />
    <input type="hidden" name="sort" value="<?php echo $sort ?>" />
    <input type="hidden" name="lastsort" value="<?php echo $lastsort ?>" />
    <input type="hidden" name="sortway" value="<?php echo $sortway ?>" />
    </form>

<?php   if(!empty ($sql_pdt_id_intervals)){ ?>
    <table class="php-list" cellspacing="0" cellpadding="0">
      <thead>
        <tr>
          <th style="width: auto">Image</th>
          <th style="width: 20%"><a href="javascript: document.results.sort.value = 'name'; document.results.submit();">Nom</a></th>
          <th style="width: 5%"><a href="javascript: document.results.sort.value = 'id'; document.results.submit();">ID</a></th>
          <th style="width: 20%"><a href="javascript: document.results.sort.value = 'fastdesc'; document.results.submit();">Description rapide</a></th>
          <th style="width: 5%"><a href="javascript: document.results.sort.value = 'cat_id'; document.results.submit();">ID fam</a></th>
          <th style="width: auto">Réf. Four. 1</th>
          <th style="width: auto"><a href="javascript: document.results.sort.value = 'price'; document.results.submit();">Prix</a></th>
          <th style="width: 15%"><a href="javascript: document.results.sort.value = 'adv_name'; document.results.submit();">Annonceur/Fournisseur</a></th>
          <th style="width: auto"><a href="javascript: document.results.sort.value = 'adv_cat'; document.results.submit();">Catégorie</a></th>
          <th style="width: auto"><a href="javascript: document.results.sort.value = 'hits'; document.results.submit();">Nombre de vues total</a></th>
          <th style="width: auto"><a href="javascript: document.results.sort.value = 'leads'; document.results.submit();">Nombre de lead</a></th>
          <th style="width: auto"><a href="javascript: document.results.sort.value = 'orders'; document.results.submit();">Nombre de commandes</a></th>
          <th style="width: auto"><a href="javascript: document.results.sort.value = 'transfo'; document.results.submit();">Taux de transfo</a></th>
          <!--<th style="width: 50px"><a href="javascript: document.results.sort.value = 'score'; document.results.submit();">Score</a></th>-->
          <th style="width: auto"></th>
        </tr>
      </thead>
      <tbody>
      <?php foreach($results["data_row"] as $result) {
              $fo_pdt_url = URL."produits/".$result["cat_id"]."-".$result["idProduct"]."-".$result["ref_name"].".html";
              $fo_pdt_pic_url = is_file(PRODUCTS_IMAGE_INC."thumb_small/".$result["idProduct"]."-1.jpg") ? PRODUCTS_IMAGE_SECURE_URL."thumb_small/".$result["idProduct"]."-1.jpg" : PRODUCTS_IMAGE_SECURE_URL."no-pic-thumb_small.gif";
              $bo_pdt_url = ADMIN_URL."products/edit.php?id=".$result["idProduct"];
              $bo_adv_url = ADMIN_URL."advertisers/edit.php?id=".$result["adv_id"];
              $bo_lead_create_url = ADMIN_URL."contacts/lead-create.php?pdtId=".$result["idProduct"] ?>
        <tr>
          <td><a href="<?php echo $bo_pdt_url ?>" title="Voir la fichie produit"><img src="<?php echo $fo_pdt_pic_url ?>" alt=""></a></td>
          <td class="title"><a href="<?php echo $bo_pdt_url ?>"><?php echo $result["name"] ?></a></td>
          <td><a href="<?php echo $bo_lead_create_url ?>"><?php echo $result["idProduct"] ?></a></td>
          <td><?php echo $result["fastdesc"] ?></td>
          <td><?php echo $result["cat_id"] ?></td>
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
          <td><?php echo $result["hits"]?></td>
          <td><?php echo $result["leads"]?></td>
          <td><?php echo $result["orders"]?></td>
          <td><?php echo $result["transfo"]?>%</td>
          <!--<td><?php echo $result["score"]?></td>-->
          <td><a href="<?php echo $fo_pdt_url ?>" target="_blank"><img src="<?php echo ADMIN_URL ?>ressources/icons/monitor_go.png" alt="" title="Voir la fiche en ligne"></a></td>
        </tr>
      <?php } ?>
      </tbody>
    </table>
<?php }
if(!empty ($log_entries)){ ?>
  <?php   if(!empty ($sql_pdt_id_intervals)){ ?>
    <h1><strong>Suppressions</strong></h1>
  <?php } ?>
        <table class="php-list" cellspacing="0" cellpadding="0">
      <thead>
        <tr>
          <th style="width: 20%">ID fiche supprimée</th>
          <th style="width: 20%"><a href="javascript: document.results.sort.value = 'name'; document.results.submit();">Ancien nom</a></th>
          <th style="width: 20%"><a href="javascript: document.results.sort.value = 'cat_id'; document.results.submit();">ID fam</a></th>
          <th style="width: 20%"><a href="javascript: document.results.sort.value = 'adv_name'; document.results.submit();">Annonceur/Fournisseur</a></th>
        </tr>
      </thead>
      <tbody>
      <?php foreach($log_entries as $entry) {
        // recup info annonceur
        if(strpos($entry['action'], '[ID Annonceur : ') !== false)
          preg_match ('/\[ID Annonceur : ((?:.|\n)*?)\]/', $entry['action'], $idAdv);
        // recup id produit
        if(strpos($entry['action'], '[ID : ') !== false)
          preg_match ('/\[ID : ((?:.|\n)*?)\]/', $entry['action'], $idProd); // ^*\[ID : ((?:.|\n)*?)\]
        // recup id family
        if(strpos($entry['action'], '| old_product_name : ') !== false)
          preg_match ('/\| old_product_name : ((?:.|\n)*?)\-/', $entry['action'], $idFam);
        // recup ancien nom
        if(strpos($entry['action'], '| old_product_name : ') !== false)
          preg_match ('/Suppression de la fiche produit ((?:.|\n)*?)\[ID : /', $entry['action'], $oldName);

      if(!empty($idAdv[1]))
        $adv = AdvertiserOld::get('id = '.$idAdv[1]);
      ?>
        <tr>
          <td><?php echo $idProd[1]?></td>
          <td class="title"><?php echo $oldName[1] ?></td>
          <td><?php echo $idFam[1] ?></td>
          <td><?php echo !empty ($adv[0]['nom1']) ? $adv[0]['nom1'] : 'Annonceur introuvable' ?></td>
        </tr>
      <?php } ?>
      </tbody>
    </table>

<?php } ?>

  </div>
</div>
<?php
require(ADMIN."tail.php");
?>
