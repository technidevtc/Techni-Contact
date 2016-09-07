<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

define("CATALOG_FILE", WWW_PATH."media/auto/eperflex/products.xml");

error_reporting(E_ALL & ~E_NOTICE);

$db = DBHandle::get_instance();

// Fields to write in the XML file
$fields_list = array (
	"id" => array("source_type" => "UC", "default" => "__PRODUCT_ID__", "filter_type" => "url"),
	"name" => array("source_type" => "UC", "default" => "__PRODUCT_NAME__", "filter_type" => ""),
	"producturl" => array("source_type" => "UC", "default" => "__PRODUCT_URL__", "extra" => "campaignID=28&utm_source=email-retarget&utm_medium=email&utm_campaign=retarget-eperflex"),
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
	"categoryid3" => array("source_type" => "UC", "default" => "__PRODUCT_CATEGORY_3__", "filter_type" => "")
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
$os .= "<products>\n";

// Getting every Supplier
$res_adv = $db->query("
	SELECT a.id, a.nom1, a.prixPublic, a.delai_livraison, a.idTVA, a.parent
	FROM advertisers a
	WHERE a.actif = 1", __FILE__, __LINE__, false);
	
// Processing every products for each advertiser (to avoid a gigantic single SQL query)
while ($adv = $db->fetchAssoc($res_adv)) {
	
	// sql query
	$res_ref = $db->query("
		SELECT
			p.id AS pdtID, p.idTC, p.as_estimate AS pdt_as_estimate,
			rc.id AS idTC, rc.label, rc.content, rc.refSupplier, rc.price+rc.ecotax AS price, rc.price2, rc.unite, rc.idTVA, rc.classement,
			pfr.name, pfr.fastdesc, pfr.ref_name, pfr.alias, pfr.keywords, pfr.descc, pfr.descd, pfr.delai_livraison,
			pf.idFamily,
			a.as_estimate AS a_as_estimate
		FROM products p
		INNER JOIN products_fr pfr ON p.id = pfr.id AND pfr.active = 1 AND pfr.deleted = 0
		INNER JOIN products_families pf ON p.id = pf.idProduct
		INNER JOIN advertisers a ON p.idAdvertiser = a.id AND a.id = ".$adv['id']."
		LEFT JOIN references_content rc ON p.id = rc.idProduct AND rc.classement = 1 AND rc.vpc = 1 AND rc.deleted = 0
		GROUP BY p.id, rc.id", __FILE__, __LINE__);
	
	while ($ref = $db->fetchAssoc($res_ref)) {
		
		$pdt = array();
		foreach ($fields_list as $field_name => $set) {
			
			switch($set["source_type"]) {
				
				case "UI" :
				case "US" :
					$pdt[$field_name] = $set["default"];
					break;
				case "UC" :
					switch($set["default"]) {
						
						case "__CURRENT_YEAR__" :						$pdt[$field_name] = date('Y'); break;
						case "__PRODUCT_ID__" :							$pdt[$field_name] = $ref["pdtID"]; break;
						case "__PRODUCT_IDTC__" :						$pdt[$field_name] = $ref["idTC"]; break;
						case "__PRODUCT_ADV_ID__" :					$pdt[$field_name] = $adv["id"]; break;
						case "__PRODUCT_ADV_NAME__" :				$pdt[$field_name] = $adv["nom1"]; break;
						case "__PRODUCT_REFSUPPLIER__" :		$pdt[$field_name] = $ref["refSupplier"]; break;
						case "__PRODUCT_PRICE_PS__" :					$pdt[$field_name] = (int)$adv['prixPublic'] == 1 ? $ref["price"] : $ref["price2"]; 	break;
						case "__PRODUCT_PRICE_P__" :		

														//Modification on 06/08/2014
														//Do not fill price for the products that have 
														//([products > as_estimate =1] OR [advertisers > as_estimate = 1])
														//To ignore the price if this product has 1 as_estimate in table Products Or Advertisers
														if(strcmp($ref["pdt_as_estimate"],'1')!==0 && strcmp($ref["a_as_estimate"],'1')!==0){
															$pdt[$field_name] = $ref["price"]; 
														}
														break;
						case "__PRODUCT_PRICE_S__" :					$pdt[$field_name] = $ref["price2"]; break;
						
						
						case "__PRODUCT_PRICE_TTC__" :					$pdt[$field_name] = ceil($ref["price"] * (100+$tauxTVA[$ref["idTVA"]])) / 100; break;
						case "__PRODUCT_UNIT__" :						$pdt[$field_name] = $ref["unite"]; break;
						case "__PRODUCT_LABEL__" :					$pdt[$field_name] = $ref["label"]; break;
						case "__PRODUCT_TVA__" :						$pdt[$field_name] = $tauxTVA[$ref["idTVA"]]; break;
						case "__PRODUCT_NAME__" :						$pdt[$field_name] = $ref["name"]; break;
						case "__PRODUCT_FASTDESC__" :				$pdt[$field_name] = $ref["fastdesc"]; break;
						case "__PRODUCT_REF_NAME__" :				$pdt[$field_name] = $ref["ref_name"]; break;
						case "__PRODUCT_ALIAS__" :					$pdt[$field_name] = $ref["alias"]; break;
						case "__PRODUCT_KEYWORDS__" :				$pdt[$field_name] = $ref["keywords"]; break;
						case "__PRODUCT_DESCC__" :					$pdt[$field_name] = $ref["descc"]; break;
						case "__PRODUCT_DESCD__" :					$pdt[$field_name] = $ref["descd"]; break;
						case "__PRODUCT_DESCC_NO_TAG__" :		$pdt[$field_name] = preg_replace('/&euro;/i', '€', html_entity_decode(filter_var($ref["descc"], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW), ENT_QUOTES, 'UTF-8')); break;
						case "__PRODUCT_DESCD_NO_TAG__" :		$pdt[$field_name] = preg_replace('/&euro;/i', '€', html_entity_decode(filter_var($ref["descd"], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW), ENT_QUOTES, 'UTF-8')); break;
						case "__PRODUCT_DELIVERY_TIME__" :	$pdt[$field_name] = empty($ref["delai_livraison"]) ? $adv["delai_livraison"] : $ref["delai_livraison"]; break;
						//case "__PRODUCT_CUSTOM_COLS__" :		$pdt[$field_name] = $ref['content']; break;
						case "__PRODUCT_REF_COUNT__" :			$pdt[$field_name] = count($refs); break;
						case "__PRODUCT_REF_ORDER__" :			$pdt[$field_name] = $ref["classement"]; break;
						case "__PRODUCT_CATEGORY_1__" :			$pdt[$field_name] = $families[$families[$ref["idFamily"]]["idParent"]]["idParent"]; break;
						case "__PRODUCT_CATEGORY_2__" :			$pdt[$field_name] = $families[$ref["idFamily"]]["idParent"]; break;
						case "__PRODUCT_CATEGORY_3__" :			$pdt[$field_name] = $ref["idFamily"]; break;
						case "__PRODUCT_SHIP_FEE__" :
						case "__PRODUCT_SHIP_FEE_TTC__" :
							$fdpInfos = array();
							$res = $db->query("select config_name, config_value from config where config_name in ('fdp', 'fdp_franco', 'fdp_idTVA')", __FILE__, __LINE__);
							if ($db->numrows($res, __FILE__, __LINE__) == 3) {
								while ($rec = $db->fetch($res))
									$fdpInfos[$rec[0]] = $rec[1];
							}
							else {
								$fdpInfos["fdp"] = 20;
								$fdpInfos["fdp_franco"] = 300;
								$fdpInfos["fdp_idTVA"] = 1;
							}
							if ($ref["price"] > $fdpInfos["fdp_franco"]) $fdpInfos["fdp"] = 0;
							
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
							
							$tree_deepness = 3;		// Number of family to take into account = 3 by default
							$tree_separator = ">";	// Default separator = '>'
							$filters = explode(" ", $set["filter_type"]);
							foreach($filters as $filter) {
								$filter_set = explode("=", $filter);
								switch($filter_set[0]) {
									case "nb" : $tree_deepness = $filter_set[1]; break;
									case "sep" : $tree_separator = $filter_set[1]; break;
									default : break;
								}
							}
							
							$fam_tree = array($families[$ref["idFamily"]]["name"]);	// Family Tree
							$nb_loop = 1;					// Number of loop fot the number of parent families to show
							$idFamTemp = $ref["idFamily"];			// Temp id for tree construction purpose
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
		
		$os .= " <product id=\"".$pdt["id"]."\">\n";
		foreach($pdt as $key => $val) {
			if ($key != "id" && $val != "") {
				$os .= "  <" . $key . ">";
				if (is_array($val)) {
					foreach($val as $ckey => $cval) {
						$os .= "\n";
						$os .= "   <" . $ckey . ">";
						$os .= (!preg_match("/^[0-9]+(\,|\.[0-9]+)?$/", $cval) ? "<![CDATA[" . $cval . "]]>" : $cval);
						$os .= "</" . $ckey . ">";
					}
					$os .= "\n";
					$os .= "  ";
				}
				else $os .= (!preg_match("/^[0-9]+(\,|\.[0-9]+)?$/", $val) ? "<![CDATA[" . $val . "]]>" : $val);
				$os .= "</" . $key . ">\n";
			}
		}
		$os .= " </product>\n";
	}
}

$os .= "</products>\n";

if ($f = fopen(CATALOG_FILE, 'w')) {
	fwrite($f, $os);
	fclose($f);
}
