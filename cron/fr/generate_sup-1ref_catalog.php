<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$handle = DBHandle::get_instance();

define("CATALOG_DIR", "/data/technico/www/" . DB_LANGUAGE . "/media/auto/twenga/");
define("CATALOG_FILE", CATALOG_DIR . "techni-contact_sup-1ref_catalog.xml");

// Fields to write in the XML file
$fields_list = array (
	"product_url" => array("source_type" => "UC", "default" => "__PRODUCT_URL__", "filter_type" => "url"),
	"designation" => array("source_type" => "UC", "default" => "__PRODUCT_NAME__", "filter_type" => ""),
	"price" => array("source_type" => "UC", "default" => "__PRODUCT_PRICE_TTC__", "filter_type" => ""),
	"brand" => array("source_type" => "UI", "default" => "Techni-Contact", "filter_type" => ""),
	"image_url" => array("source_type" => "UC", "default" => "__PRODUCT_IMAGE_URL__", "filter_type" => "url"),
	"description" => array("source_type" => "UC", "default" => "__PRODUCT_DESCC_NO_TAG__", "filter_type" => ""),
	"category" => array("source_type" => "UC", "default" => "__PRODUCT_FAMILY_TREE__", "filter_type" => "nb=3 sep=->"),
	"upc_ean" => array("source_type" => "UI", "default" => "-", "filter_type" => ""),
	"shipping_cost" => array("source_type" => "UC", "default" => "__PRODUCT_SHIP_FEE_TTC__", "filter_type" => ""),
//	"in_stock" => array("source_type" => "UI", "default" => "__PRODUCT_SHIP_FEE__", "filter_type" => ""),
//	"stock_detail" => array("source_type" => "UI", "default" => "__PRODUCT_SHIP_FEE__", "filter_type" => ""),
	"ship_to" => array("source_type" => "UC", "default" => "__PRODUCT_DELIVERY_TIME__", "filter_type" => "")
);

// VAT id to rate Initialisation
$tauxTVA = array();
$res = & $handle->query("select id, taux from tva", __FILE__, __LINE__ );
while($record = & $handle->fetch($res))
	$tauxTVA[$record[0]] = $record[1];

// Categories Initialisation
$families = array();
$families[0]['name'] = '';
$families[0]['ref_name'] = '';
$families[0]['idParent'] = 0;

$res = & $handle->query("select f.id, fr.name, fr.ref_name, f.idParent from families f, families_fr fr where f.id = fr.id", __FILE__, __LINE__);
while ($family = & $handle->fetchAssoc($res)) {
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
$os = "<" . '?xml version="1.0" encoding="ISO-8859-1"?' . ">\n";
$os .= "<products>\n";

// Getting every Supplier
$res_adv = & $handle->query("
	select a.id, a.nom1, a.prixPublic, a.delai_livraison, a.idTVA, a.parent
	from advertisers a
	where a.category = " . __ADV_CAT_SUPPLIER__, __FILE__, __LINE__, false);
	
// Processing every products for each advertiser (to avoid a gigantic single SQL query)
while ($adv = & $handle->fetchAssoc($res_adv)) {
	
	$res_ref = & $handle->query("
		SELECT
			p.id AS pdtID, pf.idFamily, pfr.name, pfr.fastdesc, pfr.ref_name,
			pfr.alias, pfr.keywords, pfr.descc, pfr.descd,	pfr.delai_livraison,
			rc.id AS idTC, rc.label, rc.content, rc.refSupplier, rc.price,
			rc.price2, rc.unite, rc.idTVA, rc.classement, rcols.content AS ref_cols_headers
		FROM
			products p, products_fr pfr, products_families pf, references_content rc, references_cols rcols
		WHERE
			p.id = pfr.id AND
      p.id = pf.idProduct AND
      p.id = rc.idProduct AND
      p.id = rcols.idProduct AND
      p.price = 'ref' AND
      p.idAdvertiser = " . $adv["id"] . " AND
      rc.classement = 1 AND
      rc.vpc = 1 AND
      rc.deleted = 0
		GROUP BY
			p.id", __FILE__, __LINE__, false);
	
	while ($ref = & $handle->fetchAssoc($res_ref)) {
		
		/*
		$content_headers = unserialize($ref["content_header"]);
		$content_headers = array_slice($content_headers, 3, -5);
		$content_len = count($content_headers);
		$custom_content = array();
		
		$ref['content'] = unserialize($ref['content']);
		for ($i = 0; $i < $content_len; $i++)
			$custom_content[$content_headers[$i]] = $ref['content'][$i];
		$ref['content'] = $custom_content;
		*/
		$ref_cols_headers = unserialize($ref["ref_cols_headers"]);
		if ($ref["price2"] == "0" || $ref_cols_headers[2] != "Référence Fournisseur")
			continue;
		
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
						case "__PRODUCT_PRICE_PS__" :				$pdt[$field_name] = (int)$adv['prixPublic'] == 1 ? $ref["price"] : $ref["price2"]; break;
						case "__PRODUCT_PRICE_P__" :				$pdt[$field_name] = $ref["price"]; break;
						case "__PRODUCT_PRICE_S__" :				$pdt[$field_name] = $ref["price2"]; break;
						case "__PRODUCT_PRICE_TTC__" :			$pdt[$field_name] = ceil($ref["price"] * (100+$tauxTVA[$ref["idTVA"]])) / 100; break;
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
						case "__PRODUCT_DESCC_NO_TAG__" :		$pdt[$field_name] = preg_replace('/&euro;/i', '€', html_entity_decode(filter_var($ref["descc"], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW), ENT_QUOTES)); break;
						case "__PRODUCT_DESCD_NO_TAG__" :		$pdt[$field_name] = preg_replace('/&euro;/i', '€', html_entity_decode(filter_var($ref["descd"], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW), ENT_QUOTES)); break;
						case "__PRODUCT_DELIVERY_TIME__" :	$pdt[$field_name] = empty($ref["delai_livraison"]) ? $adv["delai_livraison"] : $ref["delai_livraison"]; break;
						//case "__PRODUCT_CUSTOM_COLS__" :		$pdt[$field_name] = $ref['content']; break;
						case "__PRODUCT_REF_COUNT__" :			$pdt[$field_name] = count($refs); break;
						case "__PRODUCT_REF_ORDER__" :			$pdt[$field_name] = $ref["classement"];
						case "__PRODUCT_SHIP_FEE__" :
						case "__PRODUCT_SHIP_FEE_TTC__" :
							$fdpInfos = array();
							if ($result = & $handle->query("select config_name, config_value from config where config_name in ('fdp', 'fdp_franco', 'fdp_idTVA')", __FILE__, __LINE__, false) && $handle->numrows($result, __FILE__, __LINE__) == 3) {
								while ($record = & $handle->fetch($result)) $fdpInfos[$record[0]] = $record[1];
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
							
						case "__PRODUCT_URL__" :
							$pdt[$field_name] = URL . 'produits/' . $ref["idFamily"] . '-' . $ref["pdtID"] . '-' . $ref["ref_name"] . '.html';
							break;
							
						case "__PRODUCT_IMAGE_URL__" :
							$pdt[$field_name] = PRODUCTS_IMAGE_URL . $ref["pdtID"] . "-card.jpg";
							break;
							
						case "__PRODUCT_FAMILY_TREE__" :
							
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
		
		$os .= " <product>\n";
		foreach($pdt as $key => $val) {
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
		$os .= " </product>\n";
	}
}

$os .= "</products>\n";

if ($f = fopen(CATALOG_FILE, 'w')) {
	fwrite($f, $os);
	fclose($f);
}

?>