<?php

if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

error_reporting(E_ALL & ~E_NOTICE);

$db = $conn->getDbh();
$path = WWW_PATH."media/auto/eperflex/products-targeting.xml";

// Fields to write in the XML file
$fields_list = array (
  "id" => array("source_type" => "UC", "default" => "__PRODUCT_ID__", "filter_type" => "url"),
  "name" => array("source_type" => "UC", "default" => "__PRODUCT_NAME__", "filter_type" => ""),
  "producturl" => array("source_type" => "UC", "default" => "__PRODUCT_URL__", "extra" => "campaignID=29&utm_source=email-retarget&utm_medium=email&utm_campaign=target-eperflex"),
  "smallimage" => array("source_type" => "UC", "default" => "__PRODUCT_IMAGE_THUMB_SMALL_URL__"),
  "price" => array("source_type" => "UC", "default" => "__PRODUCT_PRICE_P__", "filter_type" => ""),
  "description" => array("source_type" => "UC", "default" => "__PRODUCT_DESCC_NO_TAG__", "filter_type" => ""),
  "instock" => array("source_type" => "UI", "default" => "1", "filter_type" => ""),
  "bigimage" => array("source_type" => "UC", "default" => "__PRODUCT_IMAGE_URL__", "filter_type" => "url"),
  "retailprice" => array("source_type" => "UI", "default" => "", "filter_type" => ""),
  "discount" => array("source_type" => "UI", "default" => "", "filter_type" => ""),
  "recommendable" => array("source_type" => "UI", "default" => "1", "filter_type" => ""),
  "categoryid1" => array("source_type" => "UC", "default" => "__PRODUCT_CATEGORY_1__", "filter_type" => ""),
  "categoryid2" => array("source_type" => "UC", "default" => "__PRODUCT_CATEGORY_2__", "filter_type" => ""),
  "categoryid3" => array("source_type" => "UC", "default" => "__PRODUCT_CATEGORY_3__", "filter_type" => ""),
  "categoryid3_name" => array("source_type" => "UC", "default" => "__PRODUCT_CATEGORY_3_NAME__", "filter_type" => ""),
  "promo" => array("source_type" => "UI", "default" => "", "filter_type" => ""),
  "BestOf" => array("source_type" => "UC", "default" => "__PRODUCT_IS_BESTOF__", "filter_type" => "")
);

// VAT id to rate Initialisation
$tauxTVA = array();
foreach ($db->query("SELECT id, taux FROM tva", PDO::FETCH_ASSOC) as $row)
  $tauxTVA[$row['id']] = $row['taux'];

// shipping fee infos
$fdpInfos = array();
foreach ($db->query("SELECT config_name, config_value FROM config WHERE config_name IN ('fdp', 'fdp_franco', 'fdp_idTVA')", PDO::FETCH_ASSOC) as $row)
  $fdpInfos[$rec['config_name']] = $rec['config_value'];

// Categories Initialisation
$catList = array(0 => array(
  'name' => '',
  'ref_name' => '',
  'idParent' => 0
));
$cat3List = array();
$sql = "
  SELECT
    f.id,
    f.idParent,
    ffr.name,
    ffr.ref_name,
    COUNT(pfr.id) AS pdt_count
  FROM families f
  INNER JOIN families_fr ffr ON f.id = ffr.id
  LEFT JOIN products_families pf ON f.id = pf.idFamily
  LEFT JOIN products_fr pfr ON pfr.id = pf.idProduct
  LEFT JOIN advertisers a ON a.id = pfr.idAdvertiser
  WHERE pfr.id IS NULL OR (pfr.active = 1 AND pfr.deleted = 0 AND a.actif = 1)
  GROUP BY f.id
  ORDER BY ffr.name";
foreach ($db->query($sql, PDO::FETCH_ASSOC) as $cat) {
  $catList[$cat['id']] = $cat;
  if ($cat['pdt_count'] > 0)
    $cat3List[] = $cat;
  /*if (!isset($catList[$cat['idParent']]['nbchildren']))
    $catList[$cat['idParent']]['nbchildren'] = 1;
  else
    $catList[$cat['idParent']]['nbchildren']++;
  $catList[$cat['idParent']]['children'][] = $cat['id'];*/
}

// Starting to fill the XML string
$os = "<" . '?xml version="1.0" encoding="UTF-8"?' . ">\n";
$os .= "<products>\n";

// Processing every products for each category (to avoid a gigantic single SQL query)
$sth = $db->prepare("
  SELECT
    p.id AS pdtID,
    p.idTC,
	p.as_estimate AS pdt_as_estimate,
	
    rc.id AS idTC,
    rc.label,
    rc.content,
    rc.refSupplier,
    rc.price+rc.ecotax AS price,
    rc.price2,
    rc.unite,
    rc.idTVA,
    rc.classement,
	
    pfr.name,
    pfr.fastdesc,
    pfr.ref_name,
    pfr.alias,
    pfr.keywords,
    pfr.descc,
    pfr.descd,
    pfr.delai_livraison,
	
    pf.idFamily,
    IF(ps.hits>0, ps.leads/ps.hits*0.08 + ps.orders/ps.hits*0.8 + ps.estimates/ps.hits*0.12, 0) AS perf_score,
    ps.hits,
	
	a.as_estimate AS a_as_estimate
  FROM products p
  INNER JOIN products_fr pfr ON p.id = pfr.id AND pfr.active = 1 AND pfr.deleted = 0
  INNER JOIN products_families pf ON p.id = pf.idProduct AND pf.idFamily = :cat3Id
  INNER JOIN advertisers a ON p.idAdvertiser = a.id AND a.actif = 1
  LEFT JOIN products_stats ps ON ps.id = p.id
  LEFT JOIN references_content rc ON p.id = rc.idProduct AND rc.classement = 1 AND rc.vpc = 1 AND rc.deleted = 0
  GROUP BY p.id, rc.id
  ORDER BY perf_score DESC, hits DESC");
foreach ($cat3List as $cat3) {
  
  $sth->execute(array(':cat3Id' => $cat3['id']));
  
  $pdtNum = 0;
  while ($ref = $sth->fetch(PDO::FETCH_ASSOC)) {
    
    $pdt = array();
    foreach ($fields_list as $field_name => $set) {
      
      switch($set["source_type"]) {
        
        case "UI" :
        case "US" :
          $pdt[$field_name] = $set["default"];
          break;
        case "UC" :
          switch($set["default"]) {
            
            case "__CURRENT_YEAR__" :            $pdt[$field_name] = date('Y'); break;
            case "__PRODUCT_ID__" :              $pdt[$field_name] = $ref["pdtID"]; break;
            case "__PRODUCT_IDTC__" :            $pdt[$field_name] = $ref["idTC"]; break;
            case "__PRODUCT_ADV_ID__" :          $pdt[$field_name] = $adv["id"]; break;
            case "__PRODUCT_ADV_NAME__" :        $pdt[$field_name] = $adv["nom1"]; break;
            case "__PRODUCT_REFSUPPLIER__" :     $pdt[$field_name] = $ref["refSupplier"]; break;
            case "__PRODUCT_PRICE_PS__" :        $pdt[$field_name] = (int)$adv['prixPublic'] == 1 ? $ref["price"] : $ref["price2"]; break;
            case "__PRODUCT_PRICE_P__" :         
			
												//Modification on 06/08/2014
												//Do not fill price for the products that have 
												//([products > as_estimate =1] OR [advertisers > as_estimate = 1])
												//To ignore the price if this product has 1 as_estimate in table Products Or Advertisers
												if(strcmp($ref["pdt_as_estimate"],'1')!==0 && strcmp($ref["a_as_estimate"],'1')!==0){
													$pdt[$field_name] = $ref["price"]; 
												}
												break;
												 
            case "__PRODUCT_PRICE_S__" :         $pdt[$field_name] = $ref["price2"]; break;
            case "__PRODUCT_PRICE_TTC__" :       $pdt[$field_name] = ceil($ref["price"] * (100+$tauxTVA[$ref["idTVA"]])) / 100; break;
            case "__PRODUCT_UNIT__" :            $pdt[$field_name] = $ref["unite"]; break;
            case "__PRODUCT_LABEL__" :           $pdt[$field_name] = $ref["label"]; break;
            case "__PRODUCT_TVA__" :             $pdt[$field_name] = $tauxTVA[$ref["idTVA"]]; break;
            case "__PRODUCT_NAME__" :            $pdt[$field_name] = $ref["name"]; break;
            case "__PRODUCT_FASTDESC__" :        $pdt[$field_name] = $ref["fastdesc"]; break;
            case "__PRODUCT_REF_NAME__" :        $pdt[$field_name] = $ref["ref_name"]; break;
            case "__PRODUCT_ALIAS__" :           $pdt[$field_name] = $ref["alias"]; break;
            case "__PRODUCT_KEYWORDS__" :        $pdt[$field_name] = $ref["keywords"]; break;
            case "__PRODUCT_DESCC__" :           $pdt[$field_name] = $ref["descc"]; break;
            case "__PRODUCT_DESCD__" :           $pdt[$field_name] = $ref["descd"]; break;
            case "__PRODUCT_DESCC_NO_TAG__" :    $pdt[$field_name] = preg_replace('/&euro;/i', '€', html_entity_decode(filter_var($ref["descc"], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW), ENT_QUOTES, 'UTF-8')); break;
            case "__PRODUCT_DESCD_NO_TAG__" :    $pdt[$field_name] = preg_replace('/&euro;/i', '€', html_entity_decode(filter_var($ref["descd"], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW), ENT_QUOTES, 'UTF-8')); break;
            case "__PRODUCT_DELIVERY_TIME__" :   $pdt[$field_name] = empty($ref["delai_livraison"]) ? $adv["delai_livraison"] : $ref["delai_livraison"]; break;
            //case "__PRODUCT_CUSTOM_COLS__" :    $pdt[$field_name] = $ref['content']; break;
            case "__PRODUCT_REF_COUNT__" :       $pdt[$field_name] = count($refs); break;
            case "__PRODUCT_REF_ORDER__" :       $pdt[$field_name] = $ref["classement"]; break;
            case "__PRODUCT_IS_BESTOF__" :       $pdt[$field_name] = $pdtNum < 5 ? 1 : 0; break;
            case "__PRODUCT_CATEGORY_1__" :      $pdt[$field_name] = $catList[$catList[$ref["idFamily"]]["idParent"]]["idParent"]; break;
            case "__PRODUCT_CATEGORY_2__" :      $pdt[$field_name] = $catList[$ref["idFamily"]]["idParent"]; break;
            case "__PRODUCT_CATEGORY_3__" :      $pdt[$field_name] = $ref["idFamily"]; break;
			
			//To get the families 2eme degree
			//case "__PRODUCT_CATEGORY_2_NAME__" : $pdt[$field_name] = $catList[$catList[$ref["idFamily"]]["idParent"]]["name"]; break;
			case "__PRODUCT_CATEGORY_3_NAME__" : $pdt[$field_name] = $catList[$catList[$ref["idFamily"]]["id"]]["name"]; break;
			
            case "__PRODUCT_SHIP_FEE__" :
            case "__PRODUCT_SHIP_FEE_TTC__" :
              if ($ref["price"] > $fdpInfos["fdp_franco"])
                $fdpInfos["fdp"] = 0;
              
              if ($set["default"] == "__PRODUCT_SHIP_FEE_TTC__")
                $pdt[$field_name] = ceil($fdpInfos["fdp"]*(100 + $tauxTVA[$fdpInfos["fdp_idTVA"]]))/100;
              else
                $pdt[$field_name] = $fdpInfos["fdp"];
              break;
              
            case '__PRODUCT_URL__' :
              $pdt[$field_name] = Utils::get_pdt_fo_url($ref['pdtID'], $ref['ref_name'], $ref['idFamily']) . (!empty($set['extra']) ? '?'.$set['extra'] : '');
              break;
              
            case '__PRODUCT_IMAGE_THUMB_SMALL_URL__' :
              $pdt[$field_name] = Utils::get_pdt_pic_url($ref['pdtID']);
              break;

            case '__PRODUCT_IMAGE_URL__' :
              $pdt[$field_name] = Utils::get_pdt_pic_url($ref['pdtID'], 'card');
              break;
              
            case '__PRODUCT_FAMILY_TREE__' :
              
              $tree_deepness = 3;    // Number of family to take into account = 3 by default
              $tree_separator = ">";  // Default separator = '>'
              $filters = explode(" ", $set["filter_type"]);
              foreach($filters as $filter) {
                $filter_set = explode("=", $filter);
                switch($filter_set[0]) {
                  case "nb" : $tree_deepness = $filter_set[1]; break;
                  case "sep" : $tree_separator = $filter_set[1]; break;
                  default : break;
                }
              }
              
              $fam_tree = array($catList[$ref["idFamily"]]["name"]);  // Family Tree
              $nb_loop = 1;          // Number of loop fot the number of parent families to show
              $idFamTemp = $ref["idFamily"];      // Temp id for tree construction purpose
              while ($catList[$idFamTemp]['idParent'] != 0 && $nb_loop < $tree_deepness) {
                $idFamTemp = $catList[$idFamTemp]["idParent"];
                $fam_tree[] = $catList[$idFamTemp]["name"];
              }
              $fam_tree = array_reverse($fam_tree);
              $pdt[$field_name] = implode($tree_separator, $fam_tree);
              
              break;
            default : break;
          }
        
        default : break;
      }
    }
    
    $os .= " <product id=\"".$pdt["id"]."\">\n";
    foreach($pdt as $key => $val) {
      if ($key != "id" && $val !== "") {
        $os .= "  <" . $key . ">";
        if (is_array($val)) {
          foreach($val as $ckey => $cval) {
            $os .= "\n";
            $os .= "   <" . $ckey . ">";
            $os .= !preg_match("/^[0-9]+(\,|\.[0-9]+)?$/", $cval) ? "<![CDATA[" . $cval . "]]>" : $cval;
            $os .= "</" . $ckey . ">";
          }
          $os .= "\n";
          $os .= "  ";
        } else {
          $os .= !preg_match("/^[0-9]+(\,|\.[0-9]+)?$/", $val) ? "<![CDATA[" . $val . "]]>" : $val;
        }
        $os .= "</" . $key . ">\n";
      }
    }
    $os .= " </product>\n";
    
    $pdtNum++;
  }
}

$os .= "</products>\n";

if ($f = fopen($path, 'w')) {
  fwrite($f, $os);
  fclose($f);
}