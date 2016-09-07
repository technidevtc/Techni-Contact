<?php

/*
 * Require $itemList var { 0 : { ProductID1, IDTC1, Q1 }, 1 : {}, .. }
 * Return array (
 * itemCount,
	items => array (
		[id] => Reference's idTC
		[idTC] => Product's idTC
		[idFamily] => Product family ID
		[quantity] => Reference quantity from card
		[comment] => Reference comment from card
		[idProduct] => Product ID
		[idAdvertiser] => Adv ID
		[timestamp] => as named
		[cg] => 0
		[ci] => 0
		[cc] => 0
		[refSupplier] => Supplier's Product's Reference
		[price] => Reference's public price
		[price2] => Reference's supplier price
		[unite] => Reference's unit
		[marge] => Reference's marge
		[idTVA] => Referenc's VAT ID
		[contrainteProduit] => deprecated - Product's constraints 
		[tauxRemise] => deprecated - Product's discounts
		[similar_items] => serialized string of similar products
		[name] => Product's name
		[fastdesc] => Product's fastdesc
		[ref_name] => Product's references name
		[alias] => Product's alias
		[keywords] => Product's keywords
		[descc] => Product's description
		[descd] => Product's advanced description
		[delai_livraison] => Product's delivery time
		[active] => if the product is active or not
		[label] => Reference's label
		[content] => Reference's custom properties
		[classement] => Reference's order
		[url] => Product's URL
		[promotion] => Reference's promotion amount
		[sum] => Reference's total amount (*quantity)
		[sum-promotion] => Reference's total promotion amount
		[discount] => Reference's discount amount
		[sum-discount] => Reference's total discount amount
		[sumHT] => Reference's w/o VAT total amount (w/ promotion & discount)
		[sumTVA] => Reference's VAT total amount
		[sumTTC] => Reference's w/ VAT total amount),
 * tvaTable : array( idTVA1 : { rate : x, total : y, tva : z }, {}, .. ),
 * totalHT,
 * stotalHT,
 * totalTVA,
 * totalTTC,
 * fdpHT,
 * fdpTVA,
 * fdpTTC,
 * fdp_idTVA
 * dft_qte_list
 */

require_once(ICLASS . '_ClassProduct.php');
require_once(ICLASS . '_ClassPromotion.php');
require_once(ICLASS . '_ClassDiscount.php');

function & GetCalculatedCard ($itemList, $handle) {
	$CC = array(); // Calculated Card
	
	$CC["itemCount"] = count($itemList);
	
	// TVA table
	$CC["tvaTable"] = array();
	$res = $handle->query("select id, taux from tva order by taux desc", __FILE__, __LINE__);
	while ($tva = $handle->fetch($res)) {
		$CC["tvaTable"][$tva[0]] = array(
			"rate" => $tva[1],
			"total" => 0,
			"tva" => 0);
	}

	// Filling 2 usefull arrays
	$refIDs = $pdtIDs = array();
	for ($i = 0; $i < $CC["itemCount"]; $i++) {
		if (!isset($itemList[$i]["idProduct"])) $itemList[$i]["idProduct"] = $itemList[$i]["id"];
		$pdtIDs[$i] = $itemList[$i]["idProduct"];
		$refIDs[$i] = $itemList[$i]["idTC"];
	}

	// Loading complete information for each references in the card
	$CC["items"] = array();
	$pm = new ProductsManager($handle);
	$refs = $pm->GetCompleteReferencesByReferencesID($refIDs);
	for ($i = 0; $i < $CC["itemCount"]; $i++)
		$CC["items"][$i] = array_merge($itemList[$i], $refs[$refIDs[$i]]);


	// Loading active Discounts and Promotions
	$discIDs = Discount::GetActiveDiscountIDsFromProductIDs($pdtIDs, time(), $handle);
	$promoIDs = Promotion::GetActivePromotionIDsFromProductIDs($pdtIDs, time(), $handle);
	foreach ($discIDs["discs"] as $discID => $data)
		$discs[$discID] = new Discount($handle, $discID);
	foreach ($promoIDs["promos"] as $promoID => $data)
		$promos[$promoID] = new Promotion($handle, $promoID);


	// First loop to apply promotions and modify some vars
	$CC["dft_qte_list"] = array();
	$sumByAdv = array(); // Sum by Advertiser for Discounts
	for ($i = 0; $i < $CC["itemCount"]; $i++) {
		$item = & $CC["items"][$i];
		
		$CC["dft_qte_list"][] = $item["quantity"];
		
		if ($item["fastdesc"] != "") $item["fastdesc"] = " - " . $item["fastdesc"];
		if ($item["label"] != "") $item["label"] = " - " . $item["label"];
		$item["url"] = URL . "produits/" . $item["idFamily"] . "-" . $item["idProduct"] . "-" . strtolower($item["ref_name"]) . ".html";
		
		// Applying Promotions
		$item["promotion"] = 0;
		if (!empty($promoIDs["promos"])) {
			foreach ($promoIDs["pdts"][$item["idProduct"]] as $promoID => $data) {
				switch ($promos[$promoID]->type) {
					case PROM_TYPE_RELATIVE :
						$item["promotion"] += round($item["price"] * $promos[$promoID]->type_value / 100, 2);
						break;
					case PROM_TYPE_FIXED :
						$item["promotion"] += round($promos[$promoID]->type_value, 2);
						break;
				}
			}
			break; // Only One promotion to apply
		}
		if ($item["promotion"] > $item["price"]) $item["promotion"] = $item["price"];
		
		$item["sum"] = round($item["quantity"] * $item["price"], 2);
		$item["sum-promotion"] = round($item["quantity"] * $item["promotion"], 2);
		
		// Sum by Advertisers taking account the promotions
		$sumByAdv[$item["idAdvertiser"]] = (isset($sumByAdv[$item["idAdvertiser"]]) ? $sumByAdv[$item["idAdvertiser"]] : 0) + $item["sum"] - $item["sum-promotion"];
	}

	// Second loop to apply discounts and calculate totals
	$CC["stotalHT"] = $CC["totalTVA"] = 0;
	for ($i = 0; $i < $CC["itemCount"]; $i++) {
		$item = & $CC["items"][$i];
		
		// Applying Discounts after promotions
		$item["discount"] = 0;
		if (!empty($discIDs["discs"])) {
			foreach ($discIDs["pdts"][$item["idProduct"]] as $discID => $data) {
				$item["discountpc"] = $discs[$discID]->value;
				switch ($discs[$discID]->type) {
					case DISC_TYPE_AMOUNT :
						if ($sumByAdv[$item["idAdvertiser"]] >= $discs[$discID]->type_value)
						$item["discount"] += round(($item["price"] - $item["promotion"]) * $discs[$discID]->value / 100, 2);
						break;
					case DISC_TYPE_QUANTITY :
						if ($item["quantity"] >= $discs[$discID]->type_value)
						$item["discount"] += round(($item["price"] - $item["promotion"]) * $discs[$discID]->value / 100, 2);
						break;
				}
			}
		}
		if ($item["discount"] > ($item["price"] - $item["promotion"])) $item["discount"] = $item["price"] - $item["promotion"];
		
		$item["sum-discount"] = round($item["quantity"] * $item["discount"], 2);
		
		$item["sumHT"] = $item["sum"] - ($item["sum-promotion"] + $item["sum-discount"]);
		$item["sumTVA"] = round($item["sumHT"] * $CC["tvaTable"][$item["idTVA"]]["rate"] / 100, 2);
		$item["sumTTC"] = $item["sumHT"] + $item["sumTVA"];
		$CC["stotalHT"] += $item["sumHT"];
		$CC["totalTVA"] += $item["sumTVA"];
		
		// Filling the TVA table
		$CC["tvaTable"][$item["idTVA"]]["total"] += $item["sumHT"];
		$CC["tvaTable"][$item["idTVA"]]["tva"] += $item["sumTVA"];
	}

	// Total without Delivery Fee
	if ($res = & $handle->query("select config_name, config_value from config where config_name = 'fdp' or config_name = 'fdp_franco' or config_name = 'fdp_idTVA'", __FILE__, __LINE__ ) && $handle->numrows($res, __FILE__, __LINE__) == 3) {
		while ($rec = & $handle->fetch($res)) {
			$$rec[0] = $rec[1];
		}
		$CC["fdpHT"] = $fdp;
	}
	else {
		$CC["fdpHT"] = 20;
		$fdp_franco = 300;
		$CC["fdp_idTVA"] = 0;
	}

	if ($CC["stotalHT"] > $fdp_franco) $CC["fdpHT"] = 0;
	$CC["fdpTVA"] = round($CC["fdpHT"] * $CC["tvaTable"][$CC["fdp_idTVA"]]["rate"] / 100, 2);
	$CC["fdpTTC"] = $CC["fdpHT"] + $CC["fdpTVA"];

	$CC["totalHT"] = $CC["stotalHT"] + $CC["fdpHT"];
	$CC["totalTTC"] = $CC["stotalHT"] + $CC["totalTVA"] + $CC["fdpTTC"];
	
	return $CC;

}
?>