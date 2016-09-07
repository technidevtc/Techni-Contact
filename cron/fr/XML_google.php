<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
define("REMOTE_FILE", 'products-db.xml');
define("CATALOG_FILE", WWW_PATH."media/auto/google/".REMOTE_FILE);
define("GOOGLE_FTP_SERVER", 'uploads.google.com');
define("GOOGLE_FTP_USERNAME", 'md2itcfluxgoogle');
define("GOOGLE_FTP_PASS", 'Gopm263R');

error_reporting(E_ALL & ~E_NOTICE);

$db = DBHandle::get_instance();

// Fields to write in the XML file (from id to condition, mandatory attributes)
$fields_list = array (
  "id" => array("source_type" => "UC", "default" => "__PRODUCT_ID__", "filter_type" => "url"),
  "title" => array("source_type" => "UC", "default" => "__PRODUCT_NAME__", "filter_type" => ""),
  "link" => array("source_type" => "UC", "default" => "__PRODUCT_URL__", "filter_type" => "url,urlext=?pricettc=true&amp;utm_source=google-shopping&amp;utm_medium=google&amp;utm_campaign=google-shopping&amp;campaignID=3"),
  "g:price" => array("source_type" => "UC", "default" => "__PRODUCT_PRICE_TTC__", "filter_type" => ""),
  "summary" => array("source_type" => "UC", "default" => "__PRODUCT_DESCC_NO_TAG__", "filter_type" => ""),
  "g:condition" => array("source_type" => "UI", "default" => "new", "filter_type" => ""),
  //"g:brand" => array("source_type" => "UI", "default" => "Techni-Contact", "filter_type" => ""), 27/12/2010 let's put the supplier's name in the flux instead of techni-contact
  "g:brand" => array("source_type" => "UC", "default" => "__PRODUCT_ADV_NAME__", "filter_type" => ""),
  "g:mpn" => array("source_type" => "UC", "default" => "__PRODUCT_IDTC__", "filter_type" => ""),
  "g:image_link" => array("source_type" => "UC", "default" => "__PRODUCT_IMAGE_URL__", "filter_type" => "url"),
  "g:product_type" => array("source_type" => "UC", "default" => "__PRODUCT_FAMILY_TREE__", "filter_type" => ""),
  "g:availability" => array("source_type" => "UI", "default" => "available for order", "filter_type" => ""),
  "g:shipping_price" => array("source_type" => "UC", "default" => "__PRODUCT_SHIP_FEE__", "filter_type" => ""),
  "g:adwords_grouping" => array("source_type" => "UC", "default" => "__PRODUCT_FAMILY_3_NAME__", "filter_type" => ""),
  "g:adwords_labels" => array("source_type" => "UC", "default" => "__PRODUCT_ADV_NAME__", "filter_type" => ""),
  "g:adwords_redirect" => array("source_type" => "UC", "default" => "__PRODUCT_URL__", "filter_type" => "url,urlext=?pricettc=true&amp;utm_source=google-shopping&amp;utm_medium=google&amp;utm_campaign=google-shopping-cpc&amp;campaignID=4"),
  "g:google_product_category" => array("source_type" => "UC", "default" => "__GOOGLE_CAT__", "filter_type" => "")
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
$os .= '<feed xmlns="http://www.w3.org/2005/Atom" xmlns:g="http://base.google.com/ns/1.0">'."\n";


// Getting every Supplier
$res_adv = $db->query("
  SELECT a.id, a.nom1, a.prixPublic, a.delai_livraison, a.idTVA, a.parent
  FROM advertisers a
  WHERE a.actif = 1 and a.category = ".__ADV_CAT_SUPPLIER__, __FILE__, __LINE__, false);

// Processing every products for each advertiser (to avoid a gigantic single SQL query)
while ($adv = $db->fetchAssoc($res_adv)) {

  // sql query
  $res_ref = $db->query("
    SELECT
      p.id AS pdtID, p.idTC,
      rc.id AS rcidTC, rc.label, rc.content, rc.refSupplier, rc.price, rc.price2, rc.unite, rc.idTVA, rc.classement,
      pfr.name, pfr.fastdesc, pfr.ref_name, pfr.alias, pfr.keywords, pfr.descc, pfr.descd, pfr.delai_livraison,
      pf.idFamily, a.contraintePrix, fgc.google_category
    FROM products p
    INNER JOIN products_fr pfr ON p.id = pfr.id AND pfr.active = 1 AND pfr.deleted = 0
    INNER JOIN products_families pf ON p.id = pf.idProduct
    INNER JOIN advertisers a ON p.idAdvertiser = a.id AND a.id = ".$adv['id']."
    LEFT JOIN families_google_categories fgc ON fgc.id = pf.idFamily
    LEFT JOIN references_content rc ON p.id = rc.idProduct AND rc.classement = 1 AND rc.vpc = 1 AND rc.deleted = 0
    where rc.id is not null and rc.price is not null and a.contraintePrix = 0
    GROUP BY p.id, rc.id", __FILE__, __LINE__);

  while ($ref = $db->fetchAssoc($res_ref)) {

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
            case "__PRODUCT_IDTC__" :           $pdt[$field_name] = $ref["rcidTC"] != null ? $ref["rcidTC"] : $ref["idTC"]; break;
//            case "__PRODUCT_ADV_ID__" :         $pdt[$field_name] = $adv["id"]; break;
            case "__PRODUCT_ADV_NAME__" :       $pdt[$field_name] = $adv["nom1"]; break;
            case "__PRODUCT_REFSUPPLIER__" :    $pdt[$field_name] = $ref["refSupplier"]; break;
//            case "__PRODUCT_PRICE_PS__" :       $pdt[$field_name] = (int)$adv['prixPublic'] == 1 ? (empty($ref["price"]) ? "Sur devis" : $ref["price"]) : (empty($ref["price2"]) ? "Sur devis" : $ref["price2"]); break;
            case "__PRODUCT_PRICE_P__" :        $pdt[$field_name] = empty($ref["price"]) ? "" : sprintf("%.02f",$ref["price"]); break;
            case "__PRODUCT_PRICE_S__" :        $pdt[$field_name] = empty($ref["price2"]) ? "" : sprintf("%.02f",$ref["price2"]); break;
            case "__PRODUCT_PRICE_TTC__" :      $pdt[$field_name] = ceil($ref["price"] * (100+$tauxTVA[$ref["idTVA"]])) / 100; break;
//            case "__PRODUCT_UNIT__" :           $pdt[$field_name] = $ref["unite"]; break;
//            case "__PRODUCT_LABEL__" :          $pdt[$field_name] = $ref["label"]; break;
//            case "__PRODUCT_TVA__" :            $pdt[$field_name] = $tauxTVA[$ref["idTVA"]]; break;
            case "__PRODUCT_NAME__" :           $pdt[$field_name] = (empty($ref["price"]) ? "Sur devis - " : "") . $ref["name"];
              $pdt[$field_name] = htmlspecialchars_decode($pdt[$field_name], ENT_QUOTES);
              $pdt[$field_name] = preg_replace('#&[a-z]{4,6};#', ' ', $pdt[$field_name]);
              $pdt[$field_name] = str_replace("'", ' ', $pdt[$field_name]);
              break;
//            case "__PRODUCT_FASTDESC__" :       $pdt[$field_name] = $ref["fastdesc"]; break;
//            case "__PRODUCT_REF_NAME__" :       $pdt[$field_name] = $ref["ref_name"]; break;
//            case "__PRODUCT_ALIAS__" :          $pdt[$field_name] = $ref["alias"]; break;
//            case "__PRODUCT_KEYWORDS__" :       $pdt[$field_name] = $ref["keywords"]; break;
            case "__PRODUCT_DESCC__" :          $pdt[$field_name] = $ref["descc"]; break;
            case "__PRODUCT_DESCD__" :          $pdt[$field_name] = $ref["descd"]; break;
            case "__PRODUCT_DESCC_NO_TAG__" :   $pdt[$field_name] = preg_replace('/&euro;/i', '?', html_entity_decode(filter_var($ref["descc"], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW), ENT_QUOTES, 'UTF-8'));
              $pdt[$field_name] = htmlspecialchars_decode($pdt[$field_name], ENT_QUOTES);
              $pdt[$field_name] = preg_replace('#&[a-z]{4,6};#', ' ', $pdt[$field_name]);
//              $pdt[$field_name] = str_replace($carARemp, $carDeRemp, $pdt[$field_name]);
              break;
            case "__PRODUCT_DESCD_NO_TAG__" :   $pdt[$field_name] = preg_replace('/&euro;/i', '?', html_entity_decode(filter_var($ref["descd"], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW), ENT_QUOTES, 'UTF-8'));
              $pdt[$field_name] = htmlspecialchars_decode($pdt[$field_name], ENT_QUOTES);
              $pdt[$field_name] = preg_replace('#&[a-z]{4,6};#', ' ', $pdt[$field_name]);
//              $pdt[$field_name] = str_replace($carARemp, $carDeRemp, $pdt[$field_name]);
              break;
//            case "__PRODUCT_DELIVERY_TIME__" :  $pdt[$field_name] = empty($ref["delai_livraison"]) ? $adv["delai_livraison"] : $ref["delai_livraison"]; break;
            //case "__PRODUCT_CUSTOM_COLS__" :    $pdt[$field_name] = $ref['content']; break;
            case "__PRODUCT_REF_COUNT__" :      $pdt[$field_name] = count($refs); break;
            case "__PRODUCT_REF_ORDER__" :      $pdt[$field_name] = $ref["classement"]; break;
            case "__PRODUCT_CATEGORY_1__" :     $pdt[$field_name] = $families[$families[$ref["idFamily"]]["idParent"]]["idParent"]; break;
            case "__PRODUCT_CATEGORY_2__" :     $pdt[$field_name] = $families[$ref["idFamily"]]["idParent"]; break;
            case "__PRODUCT_CATEGORY_3__" :     $pdt[$field_name] = $ref["idFamily"]; break;
            case "__PRODUCT_FAMILY_3_NAME__" :  $pdt[$field_name] = $families[$ref["idFamily"]]["name"];
            case "__PRODUCT_SHIP_FEE__" :
              $pdt['g:shipping']['g:country'] = 'FR';
              $pdt['g:shipping']['g:service'] = 'Standard';
              $pdt['g:shipping']['g:price'] = (ceil($ref["price"] * (100+$tauxTVA[$ref["idTVA"]])) / 100) >= 358.8 ? 0 : 10.16;
              break;
//            case "__PRODUCT_SHIP_FEE_TTC__" :
//              $fdpInfos = array();
//              $res = $db->query("select config_name, config_value from config where config_name in ('fdp', 'fdp_franco', 'fdp_idTVA')", __FILE__, __LINE__);
//              if ($db->numrows($res, __FILE__, __LINE__) == 3) {
//                while ($rec = $db->fetch($res))
//                  $fdpInfos[$rec[0]] = $rec[1];
//              }
//              else {
//                $fdpInfos["fdp"] = 20;
//                $fdpInfos["fdp_franco"] = 300;
//                $fdpInfos["fdp_idTVA"] = 1;
//              }
//              if ($ref["price"] > $fdpInfos["fdp_franco"]) $fdpInfos["fdp"] = 0;
//
//              if ($set["default"] == "__PRODUCT_SHIP_FEE_TTC__")
//                $pdt[$field_name] = ceil($fdpInfos["fdp"]*(100 + $tauxTVA[$fdpInfos["fdp_idTVA"]]))/100;
//              else
//                $pdt[$field_name] = $fdpInfos["fdp"];
//
//              break;

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

//            case "__PRODUCT_IMAGE_THUMB_SMALL_URL__" :
//              $pdt[$field_name] = is_file(PRODUCTS_IMAGE_INC."thumb_small/".$ref["pdtID"]."-1".".jpg") ? PRODUCTS_IMAGE_URL."thumb_small/".$ref["pdtID"]."-1".".jpg" : PRODUCTS_IMAGE_URL."no-pic-thumb_small.gif";;
//              break;

            case "__PRODUCT_IMAGE_URL__" :
              $pdt[$field_name] = is_file(PRODUCTS_IMAGE_INC."card/".$ref["pdtID"]."-1".".jpg") ? PRODUCTS_IMAGE_URL."card/".$ref["pdtID"]."-1".".jpg" : '';;
              break;
            
            case "__GOOGLE_CAT__" :
              $pdt[$field_name] =$ref["google_category"];
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

    // title must be between 15 and 70 caracter long, lowercase
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
    }

    if( ( !empty($pdt['g:price']) && $pdt['g:price'] > 0) && $tailleTitre == true && $foundBrand == false ){
      $os .= " <entry>\n";
      foreach($pdt as $key => $val) {
//        if($key == 'g:image_link'){
//            echo $val.PHP_EOL;
//          }
        if ($val != ""){

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
        }
      }
      $os .= " </entry>\n";
    }
  }
}

$os .= "</feed>\n";


if ($f = fopen(CATALOG_FILE, 'w')) {
  fwrite($f, $os);
  fclose($f);
}

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
