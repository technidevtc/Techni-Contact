<?php
	if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
		require_once '../../config.php';
	}else{
		require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
	}

	function replace_special_chars($string){
		return preg_replace('~&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($string, ENT_QUOTES, 'UTF-8'));
	}
	
	function clean_hidden_chars($string){
		$string	= str_replace("\x1F", "", $string);
		$string	= str_replace("","",$string); //
		return $string;
	}


define("CATALOG_FILE", WWW_PATH."media/auto/shoppingflux/TC_XML_shoppingflux.xml");

error_reporting(E_ALL & ~E_NOTICE);

$db = DBHandle::get_instance();

try{

	/*** For Debug ***/
	//$f_log = fopen(WWW_PATH."media/auto/shoppingflux/TC_XML_shoppingflux_log.xml", 'w');
	//fwrite($f_log, "Writing\n");
		
		
		
	// Fields to write in the XML file
	$fields_list = array (
	  "id-produit" => array("source_type" => "UC", "default" => "__PRODUCT_ID__", "filter_type" => ""),//id-produit :ID fiche produit
	  "reference-du-produit" => array("source_type" => "UC", "default" => "__PRODUCT_IDTC__", "filter_type" => ""),//reference-du-produit : ID TC
	  "reference-fournisseur" => array("source_type" => "UC", "default" => "__PRODUCT_REFSUPPLIER__", "filter_type" => ""),//reference-fournisseur : ref fournisseur
	  "ean-13" => array("source_type" => "UC", "default" => "__PRODUCT_EAN13__", "filter_type" => ""),//ean-13 : champs EAN en base (n'existe pas toucjour, sinon vide
	  "isbn" => array("source_type" => "UC", "default" => "__PRODUCT_ISBN__", "filter_type" => ""),//isbn : vide
	  "nom-du-produit" => array("source_type" => "UC", "default" => "__PRODUCT_NAME__", "filter_type" => ""),//nom-du-produit : titre fiche produit
	  "nom-du-produit-court" => array("source_type" => "UC", "default" => "__PRODUCT_SHORTNAME__", "filter_type" => ""),//nom-du-produit-court : vide
	  "description-courte" => array("source_type" => "UC", "default" => "__PRODUCT_SHORT_DESC__", "filter_type" => ""),//2 cas :
	/// Le tableau prix contient des colonnes facultatives --> Reprendre le principe de la mise au panier (Nom caract : valeur - Nom carac 2 : valeur 2...)
	/// Le tableau prix ne contient pas de valeurs facultatives --> Mettre simplement le libellé en tableau prix
	  "description-longue" => array("source_type" => "UC", "default" => "__PRODUCT_LONG_DESC__", "filter_type" => ""),//description-longue : champs description du produit + technique
	  "mots-cles" => array("source_type" => "UC", "default" => "__PRODUCT_KEYWORDS__", "filter_type" => ""),//mots-cles : mots clés
	  "caracteristiques" => array("source_type" => "UC", "default" => "__PRODUCT_CARACTERISTICS__", "filter_type" => ""),//caracteristiques : idem descrption courte
	  "nombre-de-produits-en-stock" => array("source_type" => "UC", "default" => "__PRODUCT_STOCK_AMOUNT__", "filter_type" => ""),//nombre-de-produits-en-stock : vide
	  "en-stock" => array("source_type" => "UI", "default" => "available for order"),//en-stock : "neuf " par défaut
	  "url-de-la-fiche-produit" => array("source_type" => "UC", "default" => "__PRODUCT_URL_TTC__", "filter_type" => ""),//url-de-la-fiche-produit : self explaining
	  "url_photo-1" => array("source_type" => "UC", "default" => "__PRODUCT_IMAGE_URL__", "filter_type" => ""),//url_photo-1 : url de zoom vers la photo
	  "url_photo-2" => array("source_type" => "UC", "default" => "__PRODUCT_IMAGE_URL2__", "filter_type" => ""),//url_photo-2 : vide
	  "url_photo-3" => array("source_type" => "UC", "default" => "__PRODUCT_IMAGE_URL3__", "filter_type" => ""),//url_photo-3 : vide
	  "marque" => array("source_type" => "UC", "default" => "__PRODUCT_BRAND__", "filter_type" => ""),//marque : nom fournisseur
	  "categorie" => array("source_type" => "UC", "default" => "__PRODUCT_FAMILY_LEVEL_1__", "filter_type" => ""),//catégorie : famille 1
	  "sous-categorie" => array("source_type" => "UC", "default" => "__PRODUCT_FAMILY_LEVEL_2__", "filter_type" => ""),//sous-catégorie : famille 2
	  "sous-sous-categorie" => array("source_type" => "UC", "default" => "__PRODUCT_FAMILY_LEVEL_3__", "filter_type" => ""),//sous-sous-categorie : famille 3
	  "prix-du-produit-ttc" => array("source_type" => "UC", "default" => "__PRODUCT_PRICE_TTC__", "filter_type" => ""),//prix-du-produit-ttc : prix public TTC
	  "prix-avant-promotion" => array("source_type" => "UC", "default" => "__PRODUCT_PRICE_BEFORE_REDUCTION__", "filter_type" => ""),//prix-avant-promotion : vide
	  "montant-de-la-remise" => array("source_type" => "UC", "default" => "__PRODUCT_REDUCTION_AMOUNT__", "filter_type" => ""),//montant-de-la-remise : vide
	  "pourcentage-de-remise" => array("source_type" => "UC", "default" => "__PRODUCT_REDUCTION_PERCENTAGE__", "filter_type" => ""),
	  "poids" => array("source_type" => "UC", "default" => "__PRODUCT_WEIGHT__", "filter_type" => ""),//poids : vide
	  "ID-mere" => array("source_type" => "UC", "default" => "__PRODUCT_ID_PARENT__", "filter_type" => ""),//ID-mère : reprise de ID produit
	  "ID-fille" => array("source_type" => "UC", "default" => "__PRODUCT_ID_CHILDREN__", "filter_type" => ""),//ID-fille : ID mère_reftc --> si tableau prix avec 5 ligne, faire 5 "ligne XML" avec reprise à chaque fois de la même ID mère et du même ID produit
	  "SKU" => array("source_type" => "UC", "default" => "__PRODUCT_SKU__", "filter_type" => ""),//SKU : ref TC
	  "date-debut-promo" => array("source_type" => "UC", "default" => "__PRODUCT_PROMO_START__", "filter_type" => ""),//date-debut-promo : vide
	  "date-fin-promo" => array("source_type" => "UC", "default" => "__PRODUCT_PROMO_END__", "filter_type" => ""),//date-fin-promo : vide
	  "prix-de-comparaison" => array("source_type" => "UC", "default" => "__PRODUCT_COMPARISON_PRICE__", "filter_type" => ""),//prix-de-comparaison : vide
	  "eco-participation" => array("source_type" => "UC", "default" => "__PRODUCT_ECO_PARTICIPATION__", "filter_type" => ""),//eco-participation : vide
	  "tarif-de-livraison-par-defaut" => array("source_type" => "UC", "default" => "__PRODUCT_DEFAULT_SHIP_FEE__", "filter_type" => ""),//tarif-de-livraison-par-defaut : 10.16
	  "delais-de-livraison" => array("source_type" => "UC", "default" => "__PRODUCT_DELIVERY_TIME__", "filter_type" => ""),//delais-de-livraison : délai de livraison
	  "nom-de-la-livraison" => array("source_type" => "UC", "default" => "__PRODUCT_DELIVERY_NAME__", "filter_type" => ""),//nom-de-la-livraison : par transporteur
	  "delais-d-expedition" => array("source_type" => "UC", "default" => "__PRODUCT_EXPEDITION_DATE__", "filter_type" => ""),//delais-d-expedition : vide
	  "garantie" => array("source_type" => "UC", "default" => "__PRODUCT_WARANTY__", "filter_type" => ""),//garantie : garantie si existe
	  "genre" => array("source_type" => "UC", "default" => "__PRODUCT_GENDER__", "filter_type" => ""),//genre : vide
	  "matiere" => array("source_type" => "UC", "default" => "__PRODUCT_MATTER__", "filter_type" => ""),//matiere : vide
	  "couleur" => array("source_type" => "UC", "default" => "__PRODUCT_COLOR__", "filter_type" => ""),//couleur : vide
	  "taille" => array("source_type" => "UC", "default" => "__PRODUCT_SIZE__", "filter_type" => ""),//taille : vide
	  "quantite" => array("source_type" => "UC", "default" => "__PRODUCT_QUANTITY__", "filter_type" => ""),
	  "pointure" => array("source_type" => "UC", "default" => "__PRODUCT_SHOE_SIZE__", "filter_type" => ""),//pointure : vide
	  "dimension" => array("source_type" => "UC", "default" => "__PRODUCT_DIMENSION__", "filter_type" => ""),//dimension : vide
	  "option-n" => array("source_type" => "UC", "default" => "__PRODUCT_OPTION_N__", "filter_type" => ""),
	  "url_categorie" => array("source_type" => "UC", "default" => "__PRODUCT_URL_FAMILY_LEVEL_1__", "filter_type" => ""),//url_catégorie : lien vers famille 1
	  "url_sous-categorie" => array("source_type" => "UC", "default" => "__PRODUCT_URL_FAMILY_LEVEL_2__", "filter_type" => ""),//url_sous-catégorie : lien vers famille 2
	  "url_sous-sous-categorie" => array("source_type" => "UC", "default" => "__PRODUCT_URL_FAMILY_LEVEL_3__", "filter_type" => ""),//url_sous-sous-categorie : lien vers famille 3
	  "url_page_marque" => array("source_type" => "UC", "default" => "__PRODUCT_BRAND_PAGE__", "filter_type" => "")//url_page_marque : vide

	);


	// VAT id to rate Initialisation
	$tauxTVA = array();
	$res = $db->query("select id, taux from tva", __FILE__, __LINE__ );
	while($record = $db->fetch($res))
	  $tauxTVA[$record[0]] = $record[1];

	/*** For Debug ***/
	//fwrite($f_log, "Number Rows for TVA: ".mysql_num_rows($res)."\n");
	  
	  
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

	/*** For Debug ***/
	//fwrite($f_log, "Number Rows for Families: ".mysql_num_rows($res)."\n");
	  

	// Starting to fill the XML string
	$os = "<" . '?xml version="1.0" encoding="UTF-8"?' . ">\n";
	$os .= "<products>\n";

	// Getting every Supplier
	$res_adv = $db->query("
	  SELECT a.id, a.nom1, a.prixPublic, a.delai_livraison, a.idTVA, a.parent
	  FROM advertisers a
	  WHERE a.actif = 1 AND a.category = ".__ADV_CAT_SUPPLIER__, __FILE__, __LINE__, false);//
	  
	/*** For Debug ***/
	//fwrite($f_log, "Number Rows for advertisers: ".mysql_num_rows($res_adv)."\n");
	  
	// Processing every products for each advertiser (to avoid a gigantic single SQL query)
	$liste_ref = array();
	while (($adv = $db->fetchAssoc($res_adv)) ) {
	  set_time_limit(60);
	  
	  //Modification on 06/08/2014 Add condition to exclude products that have ([products > as_estimate =1] OR [advertisers > as_estimate = 1])
	  //First modification in INNER JOIN advertisers => AND a.as_estimate!=1
	  //Second modification in Where => AND p.as_estimate!=1
	  $res_ref = $db->query("
		SELECT
		  p.id AS pdtID, p.ean, pf.idFamily, pfr.name, pfr.fastdesc, pfr.ref_name,
		  pfr.alias, pfr.keywords, pfr.descc, pfr.descd,  pfr.delai_livraison,
		  rc.id AS idTC, rc.label, rc.content, rc.refSupplier, rc.price+rc.ecotax AS price,
		  rc.price2, rc.unite, rc.idTVA, rc.classement, rcols.content AS ref_cols_headers
		FROM products p
		INNER JOIN products_fr pfr ON p.id = pfr.id AND pfr.active = 1 AND pfr.deleted = 0
		INNER JOIN products_families pf ON p.id = pf.idProduct
		INNER JOIN references_content rc ON p.id = rc.idProduct AND rc.classement = 1 AND rc.vpc = 1 AND rc.deleted = 0
		INNER JOIN references_cols rcols ON p.id = rcols.idProduct
		INNER JOIN advertisers a ON p.idAdvertiser = a.id AND a.id = ".$adv["id"]." AND a.as_estimate!=1
		WHERE 
			p.price = 'ref'
		AND
			p.as_estimate!=1
		GROUP BY p.id", __FILE__, __LINE__, false);

		/** For Debug **/
		//fwrite($f_log, "	Number Rows for products advertisers: ".mysql_num_rows($res_ref)."\n");
		
	  $liste_ref[$adv["id"]] = array();
	  while (($ref = $db->fetchAssoc($res_ref)) ) { 
		$liste_ref[$adv["id"]][] = $ref;
		$content_headers = mb_unserialize($ref["ref_cols_headers"]);
		$content_headers = array_slice($content_headers, 3, -5);
		$content_header_list[$content_headers[0]] = $content_headers[0];
	  }
	  $advList[] = $adv;
	}

	// getting default shipping fee values
	$fdp_res = $db->query("
	  SELECT
		SUM(IF(config_name='fdp', config_value, 0)) AS fdp,
		SUM(IF(config_name='fdp_franco', config_value, 0)) AS fdp_franco,
		SUM(IF(config_name='fdp_idTVA', config_value, 0)) AS fdp_idTVA
	  FROM config
	  WHERE config_name IN ('fdp', 'fdp_franco', 'fdp_idTVA')
	", __FILE__, __LINE__, false);
	$fdpInfos = $db->fetchAssoc($fdp_res);

		/** For Debug **/
		//fwrite($f_log, "Number Rows Default shipping fee values: ".mysql_num_rows($fdp_res)."\n");

		/*** For Debug ***/
		//$count_number_in_switch_advertisers=0;
		
	// processing each advertiser
	foreach ($advList as $adv) {
		
		/*** For Debug ***/
		//$count_number_in_switch_products=0;
		  
	  foreach ($liste_ref[$adv["id"]] as $ref) {
		
		$content_len = count($content_headers);
		$custom_content = array();
		$ref['content'] = mb_unserialize($ref['content']);
		
		for ($i = 0; $i < $content_len; $i++)
		  $custom_content[Utils::toDashAz09($content_headers[$i])] = $ref['content'][$i];
		
		$ref['content'] = $custom_content;
		$basket_short_desc = $ref['label'];
		
		if (!empty ($custom_content)) {
		  foreach($custom_content as $cle => $valeur)
			$basket_short_desc .= ' - '.$cle.': '.$valeur;
		}
		
		$ref_cols_headers = mb_unserialize($ref["ref_cols_headers"]);
		
		/*if ($ref["price2"] == "0" || $ref_cols_headers[2] != "Référence Fournisseur")
		  continue;*/
		  
		if ($ref["price2"] == "0" || replace_special_chars($ref_cols_headers[2]) != "Reference Fournisseur")
		  continue;

		  /*** For Debug ***/
		  //$count_number_in_switch_array=0;
		  
		$pdt = array();
		foreach ($fields_list as $field_name => $set) {
		
		  switch ($set["source_type"]) {
			case "UI" :
			
			case "US" :
			  $pdt[$field_name] = $set["default"];
			  break;
			
			case "UC" :
			  switch ($set["default"]) {
			  
				case "__CURRENT_YEAR__" :                      $pdt[$field_name] = date('Y'); break;
				case "__PRODUCT_ID__" :                        $pdt[$field_name] = $ref["pdtID"]; break;//id-produit :ID fiche produit
				case "__PRODUCT_IDTC__" :                      $pdt[$field_name] = $ref["idTC"]; break;//reference-du-produit : ID TC
				case "__PRODUCT_REFSUPPLIER__" :               $pdt[$field_name] = $ref["refSupplier"]; break;//reference-fournisseur : ref fournisseur
				case "__PRODUCT_EAN13__" :                     $pdt[$field_name] = !empty($ref["ean"]) ? $ref["ean"] : ''; break;//ean-13 : champs EAN en base (n'existe pas toucjour, sinon vide
				case "__PRODUCT_ISBN__" :                      $pdt[$field_name] = ''; break;//isbn : vide
				case "__PRODUCT_NAME__" :                      $pdt[$field_name] = $ref["name"]; break;//nom-du-produit : titre fiche produit
				case "__PRODUCT_SHORTNAME__" :                 $pdt[$field_name] = ''; break;//nom-du-produit-court : vide
				case "__PRODUCT_SHORT_DESC__" :                $pdt[$field_name] = $basket_short_desc; break;//2 cas :
	// Le tableau prix contient des colonnes facultatives --> Reprendre le principe de la mise au panier (Nom caract : valeur - Nom carac 2 : valeur 2...)
	// Le tableau prix ne contient pas de valeurs facultatives --> Mettre simplement le libellé en tableau prix
				case "__PRODUCT_LONG_DESC__" :  				$pdt[$field_name] = clean_hidden_chars($ref["descc"]); break;
					//description-longue : champs description du produit + technique
				case "__PRODUCT_KEYWORDS__" :                  $pdt[$field_name] = $ref["keywords"]; break;//mots-cles : mots clés
				case "__PRODUCT_CARACTERISTICS__" :            $pdt[$field_name] = $basket_short_desc; break;//2 cas :
	// Le tableau prix contient des colonnes facultatives --> Reprendre le principe de la mise au panier (Nom caract : valeur - Nom carac 2 : valeur 2...)
	// Le tableau prix ne contient pas de valeurs facultatives --> Mettre simplement le libellé en tableau prix
				case "__PRODUCT_STOCK_AMOUNT__" :              $pdt[$field_name] = ''; break;//nombre-de-produits-en-stock : vide
				//url-de-la-fiche-produit : self explaining, voir plus bas
				//url_photo-1 : url de zoom vers la photo, voir plus bas
				case "__PRODUCT_IMAGE_URL2__" :                $pdt[$field_name] = ''; break;//url_photo-2 : vide
				case "__PRODUCT_BRAND__" :                     $pdt[$field_name] = $adv["nom1"]; break;//marque : nom fournisseur
				//case "__PRODUCT_FAMILY_LEVEL_1__" :    $pdt[$field_name] = $ref["fastdesc"]; break;//catégorie : famille 1
				//case "__PRODUCT_FAMILY_LEVEL_2__" :    $pdt[$field_name] = $ref["fastdesc"]; break;//sous-catégorie : famille 2
				//case "__PRODUCT_FAMILY_LEVEL_3__" :    $pdt[$field_name] = $ref["fastdesc"]; break;//sous-sous-catégorie : famille 3
				case "__PRODUCT_PRICE_TTC__" :                 $pdt[$field_name] = sprintf("%.2f", $ref['price'] + round($ref['price'] * $tauxTVA[$ref['idTVA']] / 100, 6)); break;// using sprint to match the rounded value in the FO
				case "__PRODUCT_PRICE_BEFORE_REDUCTION__" :    $pdt[$field_name] = ''; break;//prix-avant-promotion : vide
				case "__PRODUCT_REDUCTION_AMOUNT__" :          $pdt[$field_name] = ''; break;//montant-de-la-remise : vide
				case "__PRODUCT_REDUCTION_PERCENTAGE__" :      $pdt[$field_name] = ''; break;
				case "__PRODUCT_WEIGHT__" :                    $pdt[$field_name] = ''; break;//poids : vide
				case "__PRODUCT_ID_PARENT__" :                 $pdt[$field_name] = ''; break;//ID-mère : reprise de ID produit
				case "__PRODUCT_ID_CHILDREN__" :               $pdt[$field_name] = ''; break;//ID-fille : ID mère_reftc --> si tableau prix avec 5 ligne, faire 5 "ligne XML" avec reprise à chaque fois de la même ID mère et du même ID produit
				case "__PRODUCT_SKU__" :                       $pdt[$field_name] = $ref['idTC']; break;//SKU : ref TC
				case "__PRODUCT_PROMO_START__" :               $pdt[$field_name] = ''; break;//date-debut-promo : vide
				case "__PRODUCT_PROMO_END__" :                 $pdt[$field_name] = ''; break;//date-fin-promo : vide
				case "__PRODUCT_COMPARISON_PRICE__" :          $pdt[$field_name] = ''; break;//prix-de-comparaison : vide
				case "__PRODUCT_ECO_PARTICIPATION__" :         $pdt[$field_name] = ''; break;//eco-participation : vide
				case "__PRODUCT_DEFAULT_SHIP_FEE__" :          $pdt[$field_name] = sprintf("%.2f", $fdpInfos['fdp'] + round($fdpInfos['fdp'] * $tauxTVA[$fdpInfos['fdp_idTVA']] / 100, 6)); break;//tarif-de-livraison-par-defaut
				case "__PRODUCT_DELIVERY_TIME__" :             $pdt[$field_name] = empty($ref["delai_livraison"]) ? $adv["delai_livraison"] : $ref["delai_livraison"]; break;//delais-de-livraison : délai de livraison
				case "__PRODUCT_DELIVERY_NAME__" :             $pdt[$field_name] = 'standard'; break;//nom-de-la-livraison : par transporteur
				case "__PRODUCT_EXPEDITION_DATE__" :           $pdt[$field_name] = ''; break;//delais-d-expedition : vide
				case "__PRODUCT_WARANTY__" :                   $pdt[$field_name] = !empty($ref["warranty"]) ? $ref["warranty"] : ''; break;//garantie : garantie si existe
				case "__PRODUCT_GENDER__" :                    $pdt[$field_name] = ''; break;//genre : vide
				case "__PRODUCT_MATTER__" :                    $pdt[$field_name] = ''; break;//matiere : vide
				case "__PRODUCT_COLOR__" :                     $pdt[$field_name] = ''; break;//couleur : vide
				case "__PRODUCT_SIZE__" :                      $pdt[$field_name] = ''; break;//taille : vide
				case "__PRODUCT_QUANTITY__" :                  $pdt[$field_name] = ''; break;// quantités : vide
				case "__PRODUCT_SHOE_SIZE__" :                 $pdt[$field_name] = ''; break;//pointure : vide
				case "__PRODUCT_DIMENSION__" :                 $pdt[$field_name] = ''; break;//dimension : vide
				case "__PRODUCT_OPTION_N__" :                  $pdt[$field_name] = ''; break;//option : vide
				case "__PRODUCT_BRAND_PAGE__" :                $pdt[$field_name] = URL.'fournisseur/'.$adv["id"].'.html'; break;
				case "__PRODUCT_URL__" :
				  $pdt[$field_name] = URL . 'produits/' . $ref["idFamily"] . '-' . $ref["pdtID"] . '-' . $ref["ref_name"] . '.html';
				  break;
				case "__PRODUCT_URL_TTC__" :
				  $pdt[$field_name] = URL . 'produits/' . $ref["idFamily"] . '-' . $ref["pdtID"] . '-' . $ref["ref_name"] . '.html?pricettc=true';
				  break;
				case "__PRODUCT_IMAGE_URL__" :
				  $pdt[$field_name] = is_file(PRODUCTS_IMAGE_INC."card/".$ref["pdtID"]."-1".".jpg") ? PRODUCTS_IMAGE_URL."card/".$ref["pdtID"]."-1".".jpg" : PRODUCTS_IMAGE_URL."no-pic-card.gif";;
				  break;
				case "__PRODUCT_IMAGE_URL2__" :
				  $pdt[$field_name] = is_file(PRODUCTS_IMAGE_INC."card/".$ref["pdtID"]."-2".".jpg") ? PRODUCTS_IMAGE_URL."card/".$ref["pdtID"]."-2".".jpg" : "";;
				  break;
				case "__PRODUCT_IMAGE_URL3__" :
				  $pdt[$field_name] = is_file(PRODUCTS_IMAGE_INC."card/".$ref["pdtID"]."-3".".jpg") ? PRODUCTS_IMAGE_URL."card/".$ref["pdtID"]."-3".".jpg" : "";;
				  break;
				case "__PRODUCT_FAMILY_LEVEL_1__" :
				case "__PRODUCT_FAMILY_LEVEL_2__" :
				case "__PRODUCT_FAMILY_LEVEL_3__" :
				case "__PRODUCT_URL_FAMILY_LEVEL_1__" :
				case "__PRODUCT_URL_FAMILY_LEVEL_2__" :
				case "__PRODUCT_URL_FAMILY_LEVEL_3__" :
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

				  $fam_tree = array(array('name' => $families[$ref["idFamily"]]["name"], 'ref_name' => $families[$ref["idFamily"]]["ref_name"]));  // Family Tree
				  $nb_loop = 1;          // Number of loop fot the number of parent families to show
				  $idFamTemp = $ref["idFamily"];      // Temp id for tree construction purpose
				  while ($families[$idFamTemp]['idParent'] != 0 && $nb_loop < $tree_deepness) {
					$idFamTemp = $families[$idFamTemp]["idParent"];
					$fam_tree[] = array('name' => $families[$idFamTemp]["name"], 'ref_name' => $families[$idFamTemp]["ref_name"]);
				  }
				  
				  if($set["default"] == '__PRODUCT_FAMILY_LEVEL_3__')
					$pdt[$field_name] = $fam_tree[0]['name'];

				  if($set["default"] == '__PRODUCT_FAMILY_LEVEL_2__')
					$pdt[$field_name] = $fam_tree[1]['name'];

				  if($set["default"] == '__PRODUCT_FAMILY_LEVEL_1__')
					$pdt[$field_name] = $fam_tree[2]['name'];

				  if($set["default"] == '__PRODUCT_URL_FAMILY_LEVEL_3__')
					$pdt[$field_name] = URL . 'familles/' . $fam_tree[0]['ref_name'] . '.html';

				  if($set["default"] == '__PRODUCT_URL_FAMILY_LEVEL_2__')
					$pdt[$field_name] = URL . 'familles/' . $fam_tree[1]['ref_name'] . '.html';

				  if($set["default"] == '__PRODUCT_URL_FAMILY_LEVEL_1__')
					$pdt[$field_name] = URL . 'familles/' . $fam_tree[2]['ref_name'] . '.html';

				  break;
				
				default :
				  break;
			  }
			
			default : break;
		  }
		  
		  /*** For Debug ***/
		  //$count_number_in_switch_array++;
		}
		
		
		/** For Debug **/
		//fwrite($f_log, "		Count number in switch *Array: ".$count_number_in_switch_array."\n");
		
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
		
		
		/** For Debug **/
		//$count_number_in_switch_products++;
		
	  }//end if products
	  
		/** For Debug **/
		//fwrite($f_log, "	Count number in switch *Products: ".$count_number_in_switch_products."\n");
		
		/** For Debug **/
		//$count_number_in_switch_advertisers++;
	}//end if advertisers
	
	/** For Debug **/
	//fwrite($f_log, "Count number in switch *Advertisers: ".$count_number_in_switch_advertisers."\n");

	
	/** For Debug **/
	//fwrite($f_log, "##Closing file !\n");
	
	/** For Debug **/
	//fclose($f_log);

	$os .= "</products>\n";
	if ($f = fopen(CATALOG_FILE, 'w')) {
	  fwrite($f, $os);
	  fclose($f);
	}

}catch(Exception $e){
	echo('Error: '.$e);	
}