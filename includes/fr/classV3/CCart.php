<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de cr�ation : 20 d�cembre 2004

 Mises � jour :

       31 mai 2005 : = nouveau gestionnaire de rangs
                     + gestion session s�curis�e avec contr�le adresse ip

 Fichier : /includes/classV2/ManagerUser.php4
 Description : Classe utilisateur manager

/=================================================================*/

require_once(ICLASS . 'CPromotion.php');
require_once(ICLASS . '_ClassDiscount.php');
require_once(ICLASS . '_ClassProduct.php');

class Cart
{
	
	private $handle = NULL;
	private $db = null;
	private $existsInDB = false;
	private $oldID = 0;
	private $overwrite = false;
	private $altered = false;
	
	private $id = "";
	private $timestamp = 0;
	private $create_time = 0;
	private $totalHT = 0;
	private $totalTTC = 0;
	private $locked = 0;
	private $valid = 0;
	private $idClient = 0;
	private $estimate = 0;
	private $promotionCode = "";
  private $delivery_address_id = 0;
  private $billing_address_id = 0;
	
	// Fields w/ default values in table Clients : usefull to load/save
	private static $fields = array(
		"id" => "",
		"timestamp" => 0,
		"create_time" => 0,
		"totalHT" => 0,
		"totalTTC" => 0,
		"locked" => 0,
		"valid" => 0,
		"idClient" => 0,
		"estimate" => 0,
		"promotionCode" => "",
    "insured" => 0,
    "delivery_address_id" => 0,
    "billing_address_id" => 0);
		
	// Will containe the object fields data
	//private $data = null;
	
	// Saved items properties
	private $itemFields = array(
		"idPanier" => 0,
		"idProduct" => 0,
		"idTC" => 0,
		"idFamily" => 0,
		"quantity" => 0,
		"comment" => 0);
	
	private $items = array();
	private $itemCount = 0;
	/* Tableau contenant la liste des taux de tva */
	private $TVArate = NULL;
	
	// Insurance amount
    private $insured = false;
    private $can_be_insured = false;
    private $insurance = 0;
    
    // Calculated properties
	private $stotalHT = 0;
	private $totalTVA = 0;
	private $fdp_tva = 0;
	private $fdpHT = 0;
	private $fdpTVA = 0;
	private $fdpTTC = 0;
	
	/* Calculated VAT table = array(
	 *  VAT_rate => array(
	 *   "total" => product's sum for this rate,
	 *   "tva" => product's tva sum for this rate)) */
	private $tvaTable = null;
	
	// List of Advertisers and Products witch does not meet the constraints requirement
	private $notValidAdvList = array();
	private $notValidPdtList = array();
	
	public static function getEstimates ($customerID = null, $order = null, $way = "desc") {
		$db = DBHandle::get_instance();
		$res = $db->query("
		select " . implode(",", array_keys(self::$fields)) . "
		from paniers
		where estimate != 0 " . (!empty($customerID) ? "and idClient = " . $customerID : "") . "
		" . (!empty($order) ? "order by " . $order . " " . ($way != "asc" ? "desc" : "asc") : ""), __FILE__, __LINE__);
		
		$estimates = array();
		while ($estimate = $db->fetchAssoc($res))
			$estimates[] = $estimate;
		
		return $estimates;
	}
	
	public static function delete ($handle, $cartID) {
		$db = DBHandle::get_instance();
		$db->query("delete from paniers where id = '" . $cartID . "'", __FILE__, __LINE__);
		$db->query("delete from paniers_produits where idPanier = '" . $cartID . "'", __FILE__, __LINE__);
	}
	
	public function __construct(& $handle, $id = null) {
		$this->handle = $this->db = DBHandle::get_instance();
		//$this->handle = $handle;
		if ($id != NULL) {
			$this->id = $id;
			$this->load();
		}
	}
	
	public function getHandle() {
		//$this->db->escape("hoho");
		//var_dump($this->handle);
		//var_dump($this->db);
	}
	
	public function __destruct() {
    if ($this->altered) {
			$this->save();
		}
	}
	
	public function __set($name, $value) {
		if ($this->$name != $value) {
      $this->$name = $value;
      $this->altered = true;
    }
	}

	public function __get($name) {
		if (isset($this->$name)) {
			return $this->$name;
		}
	}
	
	private function generateID() {
		$chars = "0123456789abcdef";
		$max = strlen($chars) - 1;
		$id = "";
		for ($i = 0; $i < 32; $i++) {
			$id .= $chars[mt_rand(0, $max)];
		}
		$this->oldID = $this->id;
		$this->id = $id;
		$this->altered = true;
	}
	
	private function generateEstimateID() {
		do {
			$id = mt_rand(1, 999999999);
			$result = & $this->db->query("select estimate from paniers where estimate = " . $id, __FILE__, __LINE__);
		}
		while ($this->db->numrows($result, __FILE__, __LINE__) == 1);
		
		$this->estimate = $id;
		$this->altered = true;
	}
	
	public function create() {
		$this->existsInDB = false;
		
		foreach (self::$fields as $fieldName => $dftValue) {
			$this->$fieldName = $dftValue;
		}
		$this->items = array();
		$this->itemCount = 0;
		$this->altered = false;
	}
	
	public function load() {
		$res = $this->db->query("
			select " . implode(",", array_keys(self::$fields)) . "
			from paniers
			where id = '" . $this->id . "'", __FILE__, __LINE__);
		if ($this->db->numrows($res, __FILE__, __LINE__) == 1) {
			$data = $this->db->fetchAssoc($res);
			foreach (self::$fields as $fieldName => $dftValue) {
				$this->$fieldName = isset($data[$fieldName]) ? $data[$fieldName] : $dftValue;
			}
			
			$res = $this->db->query("
				select " . implode("," , array_keys($this->itemFields)) . "
				from paniers_produits
				where idPanier = '" . $this->id ."'", __FILE__, __LINE__);
			while ($item = $this->db->fetchAssoc($res)) {
				$this->items[$item["idTC"]] = $item;
			}
			
			$this->itemCount = count($this->items);
			$this->existsInDB = true;
			$this->oldID = $this->id;
		}
		else
			$this->existsInDB = false;
		
		$this->altered = false;
		return $this->existsInDB;
	}
	
	public function save() {
		$this->timestamp = time();
		
		if ($this->overwrite && $this->oldID != $this->id) {
			$queries[] = "delete from paniers where id = '" . $this->id . "'";
			$queries[] = "delete from paniers_produits where idPanier = '" . $this->id . "'";
		}
		
		$queries[] = "delete from paniers_produits where idPanier = '" . $this->oldID . "'";
		
		if ($this->existsInDB) {
			$fields = array();
			foreach (self::$fields as $fieldName => $dftValue) {
				$fields[] = $fieldName . " = '" . $this->db->escape($this->$fieldName) . "'";
			}
			$queries[] = "
				update paniers
				set " . implode(",", $fields) . "
				where id = '" . $this->oldID . "'";
		}
		else {
			$this->create_time = time();
			$fields = array();
			foreach (self::$fields as $fieldName => $dftValue) {
				$fields[] = "'" . $this->db->escape($this->$fieldName) . "'";
			}
			$queries[] = "
				insert into paniers (" . implode(",", array_keys(self::$fields)) . ")
				values(" . implode(",", $fields) . ")";
		}
		
		foreach ($this->items as $item) {
			$fields = array();
			foreach ($this->itemFields as $field => $aa) {
				$fields[] = "'" . $this->db->escape($item[$field]) . "'";
			}
			$queries[] = "
				insert into paniers_produits (" . implode(",", array_keys($this->itemFields)) . ")
				values(" . implode(",", $fields) . ")";
		}
		
		foreach ($queries as $query) {
			$this->db->query($query, __FILE__, __LINE__, false);
		}
		
		$this->existsInDB = true;
	}
	
	public function makeEstimate($customerID) {
		$this->generateEstimateID();
		$this->create_time = time();
		$this->idClient = $customerID;
		$this->generateID();
		foreach ($this->items as &$item)
			$item["idPanier"] = $this->id;
		$this->altered = true;
	}
	
	public function makeMainCart($newID) {
		$this->estimate = 0;
		$this->oldID = $this->id;
		$this->overwrite = true;
		$this->id = $newID;
		foreach ($this->items as &$item)
			$item["idPanier"] = $this->id;
		$this->altered = true;
	}
	
	private function getTVArate($idTVA) {
		if ($this->TVArate === NULL) {
			$this->TVArate = array();
			$result = $this->db->query("select id, taux from tva order by taux desc", __FILE__, __LINE__ );
			while($record = $this->db->fetch($result))
				$this->TVArate[$record[0]] = $record[1];
		}
		
		return $this->TVArate[$idTVA];
	}
	
	// Deprecated
	public function updateProductQuantity($idTC, $quantity) {
		if ($quantity == 0) {
			$this->delProduct($idTC);
		}
		elseif (isset($this->items[$idTC])) {
				$this->items[$idTC]["quantity"] = $quantity;
				$this->locked = 0;
				$this->altered = true;
		}
	}
	
	// Deprecated
	public function updateProductComment($idTC, $comment) {
		if (isset($this->items[$idTC])) {
			$this->items[$idTC]["comment"] = $comment;
		}
		$this->altered = true;
	}
        
        public function updateAdresses(array $adressesList) {
          foreach ($adressesList as $adress){
            if($adress[0] == 0)
              $this->adress_bill = $adress[1];
            elseif($adress[0] == 1)
              $this->adress_ship = $adress[1];
          }
		$this->altered = true;
	}
	
	public function updateProduct($idTC, &$data) {
		if (isset($this->items[$idTC])) {
			foreach($data as $field => $value) {
				$field = trim(strtolower($field));
				switch($field) {
					case "qty" :
					case "quantity" :
						if ($value == 0)
							$this->delProduct($idTC);
						else
							$this->items[$idTC]["quantity"] = $value;
						$this->locked = 0;
						break;
						
					case "comment" :
					case "commentary" :
						$this->items[$idTC]["comment"] = $value;
						break;
				}
				$this->altered = true;
			}
		}
	}
	
	// Return true if the item has been added properly, false otherwise
	public function addProduct($idProduct, $idTC, $idFamily = null, $quantity = 1, $comment = null) {
		$quantity = (int)$quantity;
		if ($quantity <= 0)
			return false;
		
		if (empty($idFamily)) {
			$res = $this->db->query("select idFamily from products_families where idProduct = " . $idProduct,__FILE__, __LINE__);
			list($idFamily) = $this->db->fetch($res);
		}
		
		if (empty($comment)) {
			$comment = "";
		}
		
		if (isset($this->items[$idTC])) {
			$this->items[$idTC]["quantity"] += $quantity;
		}
		else {
			$item = array(
				"idPanier" => $this->id,
				"idProduct" => $idProduct,
				"idTC" => $idTC,
				"idFamily" => $idFamily,
				"quantity" => $quantity,
				"comment" => $comment);
			
			$this->items[$idTC] = & $item;
			$this->itemCount++;
		}
		
		$this->locked = 0;
		$this->altered = true;
		return true;
	}
	
	public function delProduct($idTC) {
		if (isset($this->items[$idTC])) {
			unset($this->items[$idTC]);
			$this->itemCount--;
		}
		
		$this->locked = 0;
		$this->altered = true;
	}
	
	public function clearProducts() {
		$this->items = array();
		$this->itemCount = 0;
		$this->totalHT = 0;
		$this->totalTTC = 0;
		$this->locked = 0;
        $this->insured = false;
		$this->altered = true;
	}
	
	public function addInsurance() {
      $this->insured = !!$this->insurance;
      $this->can_be_insured = false;
      $this->altered = true;
    }
    
    public function removeInsurance() {
      $this->insured = false;
      $this->can_be_insured = !!$this->insurance;
      $this->altered = true;
    }
    
    public function completeItemsInfos() {
		$refIDs = array();
		foreach ($this->items as $item)
			$refIDs[] = $item["idTC"];
		
		$pm = new ProductsManager($this->handle);
		$refs = $pm->GetCompleteReferencesByReferencesID($refIDs);
		foreach ($this->items as &$item) {
			// If the reference wasn't found, we delete the item from the cart
			if (empty($refs[$item["idTC"]]))
				unset($this->items[$item["idTC"]]);
			else {
				$item = array_merge($item, $refs[$item["idTC"]]);
				if ($item["delivery_time"] == "") $item["delivery_time"] = $item["adv_delivery_time"];
				if ($item["warranty"] == "") $item["warranty"] = $item["adv_warranty"];
        if (!empty($item["customCols"])) {
          $item["cart_desc"] = $item["label"];
          foreach($item["customCols"] as $ccol_header => $ccol_content)
            $item["cart_desc"] .= " - ".$ccol_header.": ".$ccol_content;
        }
        else {
          $item["cart_desc"] = $item["name"] . (empty($item["label"]) ? "" : " - " . $item["label"]);
        }
				$item["url"] = URL."produits/".$item["idFamily"]."-".$item["idProduct"]."-".$item["ref_name"].".html";
				$item["pic_url"] = is_file(PRODUCTS_IMAGE_INC."thumb_small/".$item["idProduct"]."-1.jpg") ? PRODUCTS_IMAGE_URL."thumb_small/".$item["idProduct"]."-1.jpg" : PRODUCTS_IMAGE_URL."no-pic-thumb_small.gif";
				$item["secure_pic_url"] = is_file(PRODUCTS_IMAGE_INC."thumb_small/".$item["idProduct"]."-1.jpg") ? PRODUCTS_IMAGE_SECURE_URL."thumb_small/".$item["idProduct"]."-1.jpg" : PRODUCTS_IMAGE_SECURE_URL."no-pic-thumb_small.gif";
				$item["secure_pic_url_zoom"] = Utils::get_secure_pdt_pic_url($item["idProduct"], "zoom");
				$item["cart_add_url"] = URL."panier/ajouter/".$item["idFamily"]."-".$item["idProduct"]."-".$item["idTC"];
			}
		}
	}
	
	public function calculateCart() {
		
		// Filling usefull vars
		$pdtIDs = array();
		foreach ($this->items as $item)
			$pdtIDs[] = $item["idProduct"];
		
		$this->completeItemsInfos();
		
		// Loading active Discounts and Promotions
		$discIDs = Discount::GetActiveDiscountIDsFromProductIDs($pdtIDs, time(), $this->handle);
		$promoIDs = Promotion::GetActivePromotionIDsFromProductIDs($pdtIDs, time());
		foreach ($discIDs["discs"] as $discID => $pdtIDs)
			$discs[$discID] = new Discount($this->handle, $discID);
		foreach ($promoIDs["promos"] as $promoID => $pdtIDs)
			$promos[$promoID] = new Promotion($promoID);
		
		// Getting product's idTCs to loop faster
		$sumByAdv = array(); // Sum by Advertiser for Discounts
		
		////$items = array(); // Saving usefull item information for sorting
		// First loop to apply promotions and modify some vars
		foreach ($this->items as &$item) {
			
			////$items[] = array($item["catID"], $item["idProduct"], $item["advID"], $item["idTC"]);
			$item["price"] = round($item["price"] + $item["ecotax"], 6);
			// $item["quantity"] being always a positive integer, we only have to reround after VAT application
			
			// Applying Promotions
			$item["promotion"] = 0;
			if (!empty($promoIDs["promos"]) && isset($promoIDs["pdts"][$item["idProduct"]])) {
				foreach ($promoIDs["pdts"][$item["idProduct"]] as $promoID => $pdtIDs) {
					// If no code specified, or if the code entered by the user is ok, apply the promotion
					if ($promos[$promoID]->code == "" || $promos[$promoID]->code == $this->promotionCode) {
						switch ($promos[$promoID]->type) {
							case PROM_TYPE_RELATIVE :
								$item["promotion"] += round($item["price"] * $promos[$promoID]->type_value / 100, 6);
								break;
							case PROM_TYPE_FIXED :
								$item["promotion"] += round($promos[$promoID]->type_value, 6);
								break;
						}
						break; // Only One promotion to apply
					}
				}
			}
			$item["promotionpc"] = round($item["promotion"] / $item["price"] * 100, 6);
			if ($item["promotion"] > $item["price"])
				$item["promotion"] = $item["price"];
			
			// Setting calculated vars needed for next loops
			$item["sum_base"] = $item["quantity"] * $item["price"];
			$item["sum_promotion"] = $item["quantity"] * $item["promotion"];
			
			// Sum by Advertisers taking account the promotions for next loops
			$sumByAdv[$item["idAdvertiser"]] = (isset($sumByAdv[$item["idAdvertiser"]]) ? $sumByAdv[$item["idAdvertiser"]] : 0) + $item["sum_base"];
		}
		unset($item);
		
		$minPerAdv = array();
		$minPerPdt = array();
		// Second loop to apply discounts and calculate totals
		$this->stotalHT = $this->totalTVA = 0;
		$this->tvaTable = array();
		foreach ($this->items as &$item) {
			
			// Applying Discounts
			$item["discount"] = 0;
			if (!empty($discIDs["discs"]) && $discIDs["pdts"][$item["idProduct"]]) {
				foreach ($discIDs["pdts"][$item["idProduct"]] as $discID => $pdtIDs) {
					switch ($discs[$discID]->type) {
						case DISC_TYPE_AMOUNT :
							if ($sumByAdv[$item["idAdvertiser"]] >= $discs[$discID]->type_value)
							$item["discount"] += round($item["price"] * $discs[$discID]->value / 100, 6);
							break;
						case DISC_TYPE_QUANTITY :
							if ($item["quantity"] >= $discs[$discID]->type_value)
							$item["discount"] += round($item["price"] * $discs[$discID]->value / 100, 6);
							break;
					}
				}
			}
			
			$item["discountpc"] = round($item["discount"] / $item["price"] * 100, 6);
			if ($item["discount"] > $item["price"])
				$item["discount"] = $item["price"];
			
			// Setting calculated vars
			$item["tauxTVA"] = $this->getTVArate($item["idTVA"]);
			
			$item["priceHT"] = $item["price"] - ($item["promotion"] + $item["discount"]);
			$item["priceTVA"] = round($item["priceHT"] * $item["tauxTVA"] / 100, 6);
			$item["priceTTC"] = $item["priceHT"] + $item["priceTVA"];
			
			$item["sum_discount"] = $item["quantity"] * $item["discount"];
			$item["sumEcotax"] = $item["quantity"] * $item["ecotax"];
      $item["sumHT"] = $item["quantity"] * $item["priceHT"];
			$item["sumTVA"] = $item["quantity"] * $item["priceTVA"];
			$item["sumTTC"] = $item["sumHT"] + $item["sumTVA"];
			$this->stotalHT += $item["sumHT"];
			$this->totalTVA += $item["sumTVA"];
			
			// Updating the TVA table
			if (!isset($this->tvaTable[$item["tauxTVA"]]))
				$this->tvaTable[$item["tauxTVA"]] = array("total" => 0, "tva" => 0);
			$this->tvaTable[$item["tauxTVA"]]["total"] += $item["sumHT"];
			$this->tvaTable[$item["tauxTVA"]]["tva"] += $item["sumTVA"];
			
			// Constraints
			if (!isset($minPerPdt[$item["idProduct"]]))
				$minPerPdt[$item["idProduct"]] = array("qty" => 0, "qty_min" => $item["quantity_min"], "sum" => 0, "sum_min" => 0);
			$minPerPdt[$item["idProduct"]]["qty"] += $item["quantity"];
			$minPerPdt[$item["idProduct"]]["sum"] += $item["sumHT"];
			
			if (!isset($minPerAdv[$item["advID"]]))
				$minPerAdv[$item["advID"]] = array("qty" => 0, "qty_min" => 0, "sum" => 0 , "sum_min" => $item["adv_amount_min"], "max_margin" => 0);
			$minPerAdv[$item["advID"]]["qty"] += $item["quantity"];
			$minPerAdv[$item["advID"]]["sum"] += $item["sumHT"];
			if ($item["price2"] != 0) {
				$itemMargin = $item["price"] / $item["price2"];
				if ($itemMargin > $minPerAdv[$item["advID"]]["max_margin"]) {
					$minPerAdv[$item["advID"]]["max_margin"] = $itemMargin;
					$minPerAdv[$item["advID"]]["sum_min"] = $item["adv_amount_min"] * $itemMargin;
				}
			}
		}
		
		foreach($minPerPdt as $pdtID => $pdtMin) {
			if (($pdtMin["qty_min"] != 0 && $pdtMin["qty"] < $pdtMin["qty_min"]) || ($pdtMin["sum_min"] != 0 && $pdtMin["sum"] < $pdtMin["sum_min"]))
				$this->notValidPdtList[$pdtID] = $pdtMin;
		}
		
		foreach($minPerAdv as $advID => $advMin) {
			if (($advMin["qty_min"] != 0 && $advMin["qty"] < $advMin["qty_min"]) || ($advMin["sum_min"] != 0 && $advMin["sum"] < $advMin["sum_min"]))
				$this->notValidAdvList[$advID] = $advMin;
		}
		
		$this->valid = 1;
		if (count($this->notValidAdvList) > 0 || count($this->notValidPdtList) > 0) {
			$this->valid = 0;
			Utils::sortDbInPlace($this->items, "advID", SORT_ASC, SORT_NUMERIC, "idProduct", SORT_ASC, SORT_NUMERIC);
			foreach($this->items as &$item) {
				if (isset($this->notValidAdvList[$item["advID"]])) {
					if (!$this->notValidAdvList[$item["advID"]]["idTC1"])
						$this->notValidAdvList[$item["advID"]]["idTC1"] = $item["idTC"];
					$this->notValidAdvList[$item["advID"]]["idTCn"] = $item["idTC"];
				}
				if (isset($this->notValidPdtList[$item["idProduct"]])) {
					if (!$this->notValidPdtList[$item["idProduct"]]["idTC1"])
						$this->notValidPdtList[$item["idProduct"]]["idTC1"] = $item["idTC"];
					$this->notValidPdtList[$item["idProduct"]]["idTCn"] = $item["idTC"];
				}
			}
			unset($item);
		}
		
		Utils::sortDbInPlace($this->items, "idProduct", SORT_ASC, SORT_NUMERIC, "advID", SORT_ASC, SORT_NUMERIC);
		
		// Delivery Fee
		$this->fdpHT = 20;
		$fdp_franco = 300;
		$fdp_idTVA = 0;
		if ($res = $this->db->query("select config_name, config_value from config where config_name = 'fdp' or config_name = 'fdp_franco' or config_name = 'fdp_idTVA'", __FILE__, __LINE__ )) {
			while ($rec = $this->db->fetch($res)) {
				$$rec[0] = $rec[1];
			}
			$this->fdpHT = $fdp;
		}
		
		// Setting calculated vars
		if ($this->stotalHT > $fdp_franco) $this->fdpHT = 0;
		$this->fdp_tva = $this->getTVArate($fdp_idTVA);
		$this->fdpTVA = round($this->fdpHT * $this->fdp_tva / 100, 6);
		$this->fdpTTC = $this->fdpHT + $this->fdpTVA;
		
		// Setting saved vars
		$this->totalTVA = round($this->totalTVA + $this->fdpTVA, 2);
		$this->totalHT = $this->stotalHT != 0 ? round($this->stotalHT + $this->fdpHT, 2) : 0; // prevent having a cart with totalHT = fdpHT
		$this->totalTTC = $this->totalHT + $this->totalTVA;
        
        // setting insurance nominal value
        if ($this->totalTTC < 60) {
          $this->insured = false;
          $this->can_be_insured = false;
          $this->insurance = 0;
        }
        if ($this->totalTTC >= 60 && $this->totalTTC < 501) {
          $this->can_be_insured = !$this->insured;
          $this->insurance = 4;
        }
        elseif ($this->totalTTC >= 501 && $this->totalTTC < 1001) {
          $this->can_be_insured = !$this->insured;
          $this->insurance = 6;
        }
        elseif ($this->totalTTC >= 1001 && $this->totalTTC < 2001) {
          $this->can_be_insured = !$this->insured;
          $this->insurance = 8;
        }
        else { // >= 2001
          $this->insured = false;
          $this->can_be_insured = false;
          $this->insurance = 0;
        }
        
        $this->totalTTC += $this->insured ? $this->insurance : 0;
	}
	
}

?>
