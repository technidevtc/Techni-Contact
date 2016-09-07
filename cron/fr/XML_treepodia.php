<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
define("REMOTE_FILE", 'products-db.xml');
define("CATALOG_FILE", WWW_PATH."media/auto/treepodia/".REMOTE_FILE);

error_reporting(E_ALL & ~E_NOTICE);

$db = DBHandle::get_instance();

// Fields to write in the XML file (from id to condition, mandatory attributes)
$fields_list = array (
  "SKU" => array("source_type" => "UC", "default" => "__PRODUCT_ID__", "filter_type" => ""),
  "Name" => array("source_type" => "UC", "default" => "__PRODUCT_NAME__", "filter_type" => ""),
  "URL" => array("source_type" => "UC", "default" => "__PRODUCT_URL__", "filter_type" => "url"),
  "Image" => array("source_type" => "UC", "default" => "__PRODUCT_IMAGE_URL__", "filter_type" => "url"),
  "Description" => array("source_type" => "UC", "default" => "__PRODUCT_FASTDESC__", "filter_type" => ""),
  "Category" => array("source_type" => "UC", "default" => "__PRODUCT_FAMILY_TREE__", "filter_type" => ""),
  "Logo" => array("source_type" => "UI", "default" => "Techni-Contact", "filter_type" => ""),
  "Brand-Logo" => array("source_type" => "UC", "default" => "__LOGO_TECHNICONTACT__", "filter_type" => "url"),
  "Catch-Phrase" => array("source_type" => "UC", "default" => "__OPERATOR_PHONE__", "filter_type" => ""),
  "Price" => array("source_type" => "UC", "default" => "__PRODUCT_PRICE_HT__", "filter_type" => "")
    
    
    
    /*
  "g:mpn" => array("source_type" => "UC", "default" => "__PRODUCT_REFSUPPLIER__", "filter_type" => ""),
  
  
  "g:availability" => array("source_type" => "UI", "default" => "available for order", "filter_type" => ""),
  "g:shipping_price" => array("source_type" => "UC", "default" => "__PRODUCT_SHIP_FEE__", "filter_type" => ""),
  "g:adwords_grouping" => array("source_type" => "UC", "default" => "__PRODUCT_FAMILY_3_NAME__", "filter_type" => ""),
  "g:adwords_labels" => array("source_type" => "UC", "default" => "__PRODUCT_ADV_NAME__", "filter_type" => ""),*/
    

);

// VAT id to rate Initialisation
$tauxTVA = array();
$res = $db->query("select id, taux from tva", __FILE__, __LINE__ );
while($record = $db->fetch($res))
  $tauxTVA[$record[0]] = $record[1];

// Categories Initialisation
$families = array();
$families[0]['name'] = '';
$families[0]['ref_name'] = '';
$families[0]['idParent'] = 0;

$res = $db->query("select f.id, fr.name, fr.ref_name, f.idParent from families f, families_fr fr where f.id = fr.id", __FILE__, __LINE__);
while ($family = $db->fetchAssoc($res)) {
  $families[$family['id']]['name'] = $family['name'];
  $families[$family['id']]['ref_name'] = $family['ref_name'];
  $families[$family['id']]['idParent'] = $family['idParent'];
  if (!isset($families[$family['idParent']]['nbchildren']))
    $families[$family['idParent']]['nbchildren'] = 1;
  else
    $families[$family['idParent']]['nbchildren']++;
  $families[$family['idParent']]['children'][$families[$family['idParent']]['nbchildren']-1] = $family['id'];
}

// Starting to fill the XML string
$os = "<" . '?xml version="1.0" encoding="UTF-8"?' . ">\n";
$os .= '<products>'."\n";


// Getting every Supplier
$res_adv = $db->query("
  SELECT a.id, a.nom1, a.prixPublic, a.delai_livraison, a.idTVA, a.parent, a.as_estimate, a.category
  FROM advertisers a
  WHERE a.actif = 1 and a.category < 3 and id IN(
  36983, 1729, 47951, 8465, 4232, 14463, 57342, 29085, 43109, 10813, 49797, 13773, 28029, 44359, 53164, 12921, 41473, 51873, 3976, 4477, 35158, 
  35195, 28892, 50804, 34820, 63572, 8989, 35801, 8180, 31514, 63329, 19353, 27572, 51087, 33842, 21701, 31315, 14679, 7830, 27366, 6901, 3366, 
  48771, 10706, 54656, 57237, 28485, 62599, 48945, 10053, 61049, 34531, 36193, 12174, 29272, 60146, 62669, 49474, 19304, 20111, 33241, 14457, 27572, 
  33842, 19248, 48945, 47484, 7830, 35697, 11321, 51873
  )", __FILE__, __LINE__, false);//

// Processing every products for each advertiser (to avoid a gigantic single SQL query)
while ($adv = $db->fetchAssoc($res_adv)) {
  // sql query
  $res_ref = $db->query("
    SELECT
      p.id AS pdtID, p.idTC,
      rc.id AS rcidTC, rc.label, rc.content, rc.refSupplier, rc.price, rc.price2, rc.unite, rc.idTVA, rc.classement,
      pfr.name, pfr.fastdesc, pfr.ref_name, pfr.alias, pfr.keywords, pfr.descc, pfr.descd, pfr.delai_livraison,
      pf.idFamily, a.contraintePrix, bou.phone
    FROM products p
    INNER JOIN products_fr pfr ON p.id = pfr.id AND pfr.active = 1 AND pfr.deleted = 0
    INNER JOIN products_families pf ON p.id = pf.idProduct
    INNER JOIN advertisers a ON p.idAdvertiser = a.id AND a.id = ".$adv['id']."
    INNER JOIN bo_users bou ON a.idCommercial = bou.id
    LEFT JOIN references_content rc ON p.id = rc.idProduct AND rc.classement = 1 AND rc.vpc = 1 AND rc.deleted = 0
    where rc.id is not null and rc.price is not null and a.contraintePrix = 0
    GROUP BY p.id, rc.id", __FILE__, __LINE__);

  while ($ref = $db->fetchAssoc($res_ref)) {
//var_dump($ref);
    $pdt = array();
//    $carARemp = array('&rsquo;', '&rdquo;', '&ldquo;', '&empty;', '&hellip;', '&bull;', '&');
//    $carDeRemp = array("'", "'", "'", '&#8709;', '&#8230;', '&#8226;', 'et');

    foreach ($fields_list as $field_name => $set) {
      
      switch($set["source_type"]) {

        case "UI" :
        case "US" :
          $pdt[$field_name] = $set["default"];
          break;
        case "UC" :
          switch($set["default"]) {

//            case "__CURRENT_YEAR__" :           $pdt[$field_name] = date('Y'); break;
            case "__PRODUCT_ID__" :             $pdt[$field_name] = $ref["pdtID"]; break;
            case "__PRODUCT_IDTC__" :           $pdt[$field_name] = $ref["idTC"]; break;
//            case "__PRODUCT_ADV_ID__" :         $pdt[$field_name] = $adv["id"]; break;
            case "__PRODUCT_ADV_NAME__" :       $pdt[$field_name] = $adv["nom1"]; break;
            //case "__PRODUCT_REFSUPPLIER__" :    $pdt[$field_name] = $ref["refSupplier"]; break;
//            case "__PRODUCT_PRICE_PS__" :       $pdt[$field_name] = (int)$adv['prixPublic'] == 1 ? (empty($ref["price"]) ? "Sur devis" : $ref["price"]) : (empty($ref["price2"]) ? "Sur devis" : $ref["price2"]); break;
            /*case "__PRODUCT_PRICE_P__" :        $pdt[$field_name] = empty($ref["price"]) ? "" : sprintf("%.02f",$ref["price"]); break;
            case "__PRODUCT_PRICE_S__" :        $pdt[$field_name] = empty($ref["price2"]) ? "" : sprintf("%.02f",$ref["price2"]); break;*/
            case "__PRODUCT_PRICE_TTC__" :      $pdt[$field_name] = ceil($ref["price"] * (100+$tauxTVA[$ref["idTVA"]])) / 100; break;
            case "__PRODUCT_PRICE_HT__" :      $pdt[$field_name] = ($adv["as_estimate"] == 1 || !is_numeric($ref["price"]) || $adv["category"] != __ADV_CAT_SUPPLIER__) ? 0 : $ref["price"]; break;
//            case "__PRODUCT_UNIT__" :           $pdt[$field_name] = $ref["unite"]; break;
//            case "__PRODUCT_LABEL__" :          $pdt[$field_name] = $ref["label"]; break;
//            case "__PRODUCT_TVA__" :            $pdt[$field_name] = $tauxTVA[$ref["idTVA"]]; break;
            case "__PRODUCT_NAME__" :           $pdt[$field_name] = (empty($ref["price"]) ? "Sur devis - " : "") . $ref["name"];
              $pdt[$field_name] = htmlspecialchars_decode($pdt[$field_name], ENT_QUOTES);
              $pdt[$field_name] = preg_replace('#&[a-z]{4,6};#', ' ', $pdt[$field_name]);
              $pdt[$field_name] = str_replace("'", ' ', $pdt[$field_name]);
              break;
            case "__PRODUCT_FASTDESC__" :       $pdt[$field_name] = $ref["fastdesc"]; break;
            case "__OPERATOR_PHONE__" :          $pdt[$field_name] = $ref["phone"]; break;
            case "__PRODUCT_DESCC__" :          $pdt[$field_name] = $ref["descc"]; break;
            case "__PRODUCT_DESCD__" :          $pdt[$field_name] = $ref["descd"]; break;
            case "__PRODUCT_DESCC_NO_TAG__" :   $pdt[$field_name] = preg_replace('/&euro;/i', '?', html_entity_decode(filter_var($ref["descc"], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW), ENT_QUOTES, 'UTF-8'));
              $pdt[$field_name] = htmlspecialchars_decode($pdt[$field_name], ENT_QUOTES);
              $pdt[$field_name] = preg_replace('#&[a-z]{4,6};#', ' ', $pdt[$field_name]);

              break;
            case "__PRODUCT_DESCD_NO_TAG__" :   $pdt[$field_name] = preg_replace('/&euro;/i', '?', html_entity_decode(filter_var($ref["descd"], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW), ENT_QUOTES, 'UTF-8'));
              $pdt[$field_name] = htmlspecialchars_decode($pdt[$field_name], ENT_QUOTES);
              $pdt[$field_name] = preg_replace('#&[a-z]{4,6};#', ' ', $pdt[$field_name]);

              break;

            case "__PRODUCT_REF_COUNT__" :      $pdt[$field_name] = count($refs); break;
            case "__PRODUCT_REF_ORDER__" :      $pdt[$field_name] = $ref["classement"]; break;
            case "__PRODUCT_CATEGORY_1__" :     $pdt[$field_name] = $families[$families[$ref["idFamily"]]["idParent"]]["idParent"]; break;
            case "__PRODUCT_CATEGORY_2__" :     $pdt[$field_name] = $families[$ref["idFamily"]]["idParent"]; break;
            case "__PRODUCT_CATEGORY_3__" :     $pdt[$field_name] = $ref["idFamily"]; break;
            case "__PRODUCT_FAMILY_3_NAME__" :  $pdt[$field_name] = $families[$ref["idFamily"]]["name"];
            /*case "__PRODUCT_SHIP_FEE__" :
              $pdt['g:shipping']['g:country'] = 'FR';
              $pdt['g:shipping']['g:service'] = 'Standard';
              $pdt['g:shipping']['Price'] = (ceil($ref["price"] * (100+$tauxTVA[$ref["idTVA"]])) / 100) >= 358.8 ? 0 : 10.16;
              break;*/
            case "__LOGO_TECHNICONTACT__":
              $pdt[$field_name] = URL.'ressources/images/header-TC-logo.png';
              break;

            case "__PRODUCT_URL__" :
              $pdt[$field_name] = URL . 'produits/' . $ref["idFamily"] . '-' . $ref["pdtID"] . '-' . $ref["ref_name"] . '.html';
              $filters = explode(",",$set["filter_type"]);
              foreach($filters as $filter) {
                list($filterName, $filterConfig) = explode("=",$filter,2);
                switch($filterName) {
                  case "url": break;
                  case "urlext":
                    $pdt[$field_name] .= $filterConfig;
                    break;
                  default: break;
                }
              }
              break;

            case "__PRODUCT_IMAGE_URL__" :
              $pdt[$field_name] = is_file(PRODUCTS_IMAGE_INC."card/".$ref["pdtID"]."-1".".jpg") ? PRODUCTS_IMAGE_URL."zoom/".$ref["pdtID"]."-1".".jpg" : '';
              break;

            case "__PRODUCT_FAMILY_TREE__" :

              $tree_deepness = 3;   // Number of family to take into account = 3 by default
              $tree_separator = " > ";  // Default separator = '>'
              $filters = explode(" ", $set["filter_type"]);
              foreach($filters as $filter) {
                $filter_set = explode("=", $filter);
                switch($filter_set[0]) {
                  case "nb" : $tree_deepness = $filter_set[1]; break;
                  case "sep" : $tree_separator = $filter_set[1]; break;
                  default : break;
                }
              }

              $fam_tree = array($families[$ref["idFamily"]]["name"]); // Family Tree
              $nb_loop = 1;         // Number of loop fot the number of parent families to show
              $idFamTemp = $ref["idFamily"];      // Temp id for tree construction purpose
              while ($families[$idFamTemp]['idParent'] != 0 && $nb_loop < $tree_deepness) {
                $idFamTemp = $families[$idFamTemp]["idParent"];
                $fam_tree[] = $families[$idFamTemp]["name"];
              }
              $fam_tree = array_reverse($fam_tree);
              $pdt[$field_name] = implode($tree_separator, $fam_tree);

              break;
            default : break;
          }

        default : break;
      }
    }

 /*   // title must be between 15 and 70 caracter long, lowercase
    $tailleTitre = false;
    if( strlen( $pdt['title'] ) >= 15 && strlen( $pdt['title'] ) <= 70 ){
      $pdt['title'] = strtolower($pdt['title']);
      $tailleTitre = true;
    }

    // brands are forbidden
    $foundBrand = false;
    $brandList = array('post-it', 'epson', 'caroll', 'optima', 'c4', 'hewlett', '2 faces', 'lexmark', 'laserjet', 'hp');
    foreach($brandList as $brand){
      if(strpos($pdt['title'], $brand) !== false){
        $foundBrand = true;
      }
    }*/

   // if( ( !empty($pdt['Price']) && $pdt['Price'] > 0) ){ // On supprime la restriction des produits sans prix http://www.hook-network.com/storm/tasks/2013/01/08/rajout-champs-dans-feed-treepodia
      $os .= " <product>\n";
      foreach($pdt as $key => $val) {
//        if($key == 'g:image_link'){
//            echo $val.PHP_EOL;
//          }
        //if ($val != ""){

          if($key == 'link'){
            $os .= '  <' . $key . ' href="' . $val . '" />'."\n";
          }  else {
            $os .= "  <" . $key . ">";
            mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $val);
            if (is_array($val)) {
              foreach($val as $ckey => $cval) {
                $os .= "\n";
                $os .= "   <" . $ckey . ">";
                $os .= (!empty($cval) && !preg_match("/^[0-9]+(\,|\.[0-9]+)?$/", $cval) ?  "<![CDATA[" . $cval . "]]>"  : $cval);
                $os .= "</" . $ckey . ">";
              }
              $os .= "\n";
              $os .= "  ";
            }
            else $os .= (!empty($val) && !preg_match("/^[0-9]+(\,|\.[0-9]+)?$/", $val) ?  "<![CDATA[" . $val . "]]>" : $val);
            $os .= "</" . $key . ">\n";
          }
        //}
      }
      $os .= " </product>\n";
    //}
  }
}

$os .= "</products>\n";


if ($f = fopen(CATALOG_FILE, 'w')) {
  fwrite($f, $os);
  fclose($f);
}
/*
$file = CATALOG_FILE;
$remote_file = REMOTE_FILE;

if(is_file($file)){
  // Mise en place d'une connexion basique
  $conn_id = ftp_connect(GOOGLE_FTP_SERVER);

  // Identification avec un nom d'utilisateur et un mot de passe
  $login_result = ftp_login($conn_id, GOOGLE_FTP_USERNAME, GOOGLE_FTP_PASS);

  // Charge un fichier
  ftp_put($conn_id, $remote_file, $file, FTP_ASCII);

  // Fermeture de la connexion
  ftp_close($conn_id);
}
*/