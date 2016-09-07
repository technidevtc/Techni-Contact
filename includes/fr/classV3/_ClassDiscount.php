<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 20 décembre 2004

 Mises à jour :

       31 mai 2005 : = nouveau gestionnaire de rangs
                     + gestion session sécurisée avec contrôle adresse ip

 Fichier : /includes/classV2/ManagerUser.php
 Description : Classe utilisateur manager

/=================================================================*/

class Discount
{
	/* Connection Handle */
	var $handle = NULL;

	/* Discount DB's fields */
	var $id = 0;
	var $idAdvertiser = 0;
	var $type = 0;
	var $type_value = "";
	var $value = 0;
	var $apply = 0;
	var $apply_value = "";
	var $priority = 0;
	var $create_time = 0;
	var $timestamp = 0;
	
	/* Discount usefull vars */
	var $advName = "";

	var $exist = false;
	var $lastErrorMessage = "";

	/* Constructor */
	function __construct (& $handle, $id = NULL) {
		$this->handle = & $handle;
		if ($id != NULL) {
			$this->id = $id;
			$this->Load();
		}
	}
	
	/*
	 * input : array(pdtID1, pdtID2, ... pdtIDn), timestamp, DB handle
	 * output : array (
	 *  discs => product IDs indexed by discount IDs,
	 *  pdts => discount IDs indexed by products IDs
	 * )
	 */
	public static function GetActiveDiscountIDsFromProductIDs($pdtIDs, $time, $handle) {
		
		$time = (int)$time;
		$ret = array("discs" => array(), "pdts" => array()); // return Array : 
		$advs = array(); // Index by Advertisers IDs
		$fams = array(); // Index by Families IDs
		
		if (empty($pdtIDs)) return $ret;
		
		// Getting Products informations
		$res = & $handle->query("
			select p.id, p.idAdvertiser, pf.idFamily
			from products p, products_families pf
			where p.id = pf.idProduct and p.id in (" . implode(",", $pdtIDs) . ")", __FILE__, __LINE__, false);
		while ($pdt = & $handle->fetchAssoc($res)) {
			$advs[$pdt["idAdvertiser"]][$pdt["id"]] = $pdt["id"];
			$fams[$pdt["idFamily"]][$pdt["id"]] = $pdt["id"];
		}
		
		// Global Discounts
		$res = & $handle->query("
			select d.id, d.idAdvertiser
			from discounts d
			where d.apply = 0 and d.idAdvertiser in (" . implode(",", array_keys($advs)) . ")", __FILE__, __LINE__, false);
		while ($disc = & $handle->fetchAssoc($res)) {
			foreach ($advs[$disc["idAdvertiser"]] as $pdtID) {
				$ret["discs"][$disc["id"]][$pdtID] = true;
				$ret["pdts"][$pdtID][$disc["id"]] = true;
			}
		}
		
		// Specific Discounts
		$res = & $handle->query("
			select da.DiscountID, da.AdvertiserID, da.CategoryID, da.ProductID
			from discounts d, discounts_application da
			where
				d.id = da.DiscountID
				and d.apply = 1
				and (da.AdvertiserID in (" . implode(",", array_keys($advs)) . ") or da.CategoryID in (" . implode(",", array_keys($fams)) . "))", __FILE__, __LINE__, false);
		while ($disc = & $handle->fetchAssoc($res)) {
			
			if (empty($disc["ProductID"])) {
				if (empty($disc["AdvertiserID"])) {
					// advID = 0 ; famID = 0 ; pdtID = 0
					if (empty($disc["CategoryID"])) {
						continue;
					}
					// advID = 0 ; famID = y ; pdtID = 0
					else {
						foreach ($fams[$disc["CategoryID"]] as $pdtID) {
							$ret["discs"][$disc["DiscountID"]][$pdtID] = true;
							$ret["pdts"][$pdtID][$disc["DiscountID"]] = true;
						}
					}
				}
				else {
					// advID = x ; famID = 0 ; pdtID = 0
					if (empty($disc["CategoryID"])) {
						foreach ($advs[$disc["AdvertiserID"]] as $pdtID) {
							$ret["discs"][$disc["DiscountID"]][$pdtID] = true;
							$ret["pdts"][$pdtID][$disc["DiscountID"]] = true;
						}
					}
					// advID = x ; famID = y ; pdtID = 0
					else {
						foreach ($advs[$disc["AdvertiserID"]] as $pdtID) {
							if (isset($fams[$disc["CategoryID"]][$pdtID])) {
								$ret["discs"][$disc["DiscountID"]][$pdtID] = true;
								$ret["pdts"][$pdtID][$disc["DiscountID"]] = true;
							}
						}
					}
				}
			}
			// advID = x ; famID = 0 ; pdtID = z
			else {
				$ret["discs"][$disc["DiscountID"]][$disc["ProductID"]] = true;
				$ret["pdts"][$disc["ProductID"]][$disc["DiscountID"]] = true;
			}
		}
		
		return $ret;
	}
	
	function GenerateID() {
		do {
			$id = mt_rand(1, 999999999);
			$result = & $this->handle->query("select id from discounts where id = " . $id, __FILE__, __LINE__);
		}
		while ($this->handle->numrows($result, __FILE__, __LINE__) >= 1);
		
		$this->id = $id;
	}
	
	// TODO copy from parent_id
	function Create() {
		$this->GenerateID();
	}
	
	function Load() {
		$this->exist = false;
		
		$res = & $this->handle->query("
		select d.id, d.idAdvertiser, a.nom1 as advName, d.type, d.type_value, d.value, d.apply, d.apply_value, d.priority, d.create_time, d.timestamp
		from   discounts d, advertisers a
		where  d.idAdvertiser = a.id and d.id = " . $this->id, __FILE__, __LINE__, false);
		
		if ($this->handle->numrows($res, __FILE__, __LINE__) > 0) {
			$rec = & $this->handle->fetchAssoc($res);
			foreach($rec as $key => $value) $this->$key = $value;
			$this->exist = true;
		}
		else $this->lastErrorMessage = "L'export n'existe pas dans la base de donnée.";
	}
	
	function Save() {
		$this->timestamp = time();
		
		$queries = array();
		
		if (!$this->exist) {
			$this->create_time = $this->timestamp;
			if (empty($this->id)) $this->generateID();
			$queries[] = "
			insert into discounts (
				id, idAdvertiser, type, type_value, value,
				apply, apply_value, priority, create_time, timestamp)
			values (" .
				$this->id . ", " .
				$this->idAdvertiser . ", " .
				$this->type . ", " .
				"'" . $this->handle->escape($this->type_value)  . "', " .
				"'" . $this->handle->escape($this->value)  . "', " .
				$this->apply . ", " .
				"'" . $this->handle->escape($this->apply_value) . "', " .
				$this->priority . ", " .
				$this->create_time . ", " .
				$this->timestamp . ")
			";
		}
		else {
			$queries[] = "
			update discounts set
				idAdvertiser = " . $this->idAdvertiser . ",
				type = " . $this->type . ",
				type_value = '" . $this->handle->escape($this->type_value) . "',
				value = '" . $this->handle->escape($this->value) . "',
				apply = " . $this->apply . ",
				apply_value = '" . $this->handle->escape($this->apply_value) . "',
				priority = " . $this->priority . ",
				create_time = " . $this->create_time . ",
				timestamp = " . $this->timestamp . "
			where id = " . $this->id;
		}
		
		//list($avas, $avcs, $avpcs) = explode(";", $this->apply_value);
		$queries[] = "delete from discounts_application where DiscountID = " . $this->id;
		
		/*
		// Apply Values for Advertisers
		if (!empty($avas)) {
			$avas = explode(",", $avas);
			foreach ($avas as $ava)
				$queries[] = "insert into promotions_application (DiscountID, AdvertiserID) values (" . $this->id . ", " . $ava . ")";
		}
		
		// Apply Values for Categories
		if (!empty($avcs)) {
			$avcs = explode(",", $avcs);
			foreach ($avcs as $avc)
				$queries[] = "insert into promotions_application (DiscountID, CategoryID) values (" . $this->id . ", " . $avc . ")";
		}
		*/
		
		// Apply Values for Products by Categories
		if (!empty($this->apply_value)) {
			$avpcs = explode("|", $this->apply_value);
			foreach ($avpcs as $avpc) {
				$avpc = explode(",", $avpc);
				$avpcCount = count($avpc);
				for($i = 1; $i < $avpcCount; $i++)
					$queries[] = "insert into discounts_application (DiscountID, CategoryID, ProductID) values (" . $this->id . ", " . $avpc[0] . ", " . $avpc[$i] . ")";
			}
		}
		
		try {
			foreach ($queries as $query) {
				if (!$this->handle->query($query, __FILE__, __LINE__, false)) {
					throw new Exception("MySQL : Error while Updating the Discount.");
				}
			}
		}
		catch (Exception $e) {
			echo "Error : " . $e->getMessage() . "\n";
			return false;
		}
		
		$this->exist = true;
		
		return true;
	}
	
	public function archive() {
		try {
			if (!$this->handle->query("delete from discounts_application where DiscountID = " . $this->id, __FILE__, __LINE__, false))
				throw new Exception("MySQL : Error while Archiving the Promotion " . $this->id);
			return true;
		}
		catch (Exception $e) {
			echo "Error : " . $e->getMessage() . "\n";
			return false;
		}
	}
	
	function GetAdvName($advID) {
		$res = & $this->handle->query("select a.nom1 from advertisers a where a.id = " . (int)$advID, __FILE__, __LINE__, false);
		if ($this->handle->numrows($res, __FILE__, __LINE__) > 0)
		{
			list($advName) = $this->handle->fetch($res);
			return $advName;
		}
		else return "";
	}

}

?>
