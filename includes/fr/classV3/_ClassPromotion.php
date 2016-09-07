<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 20 décembre 2004

 Mises à jour :

       31 mai 2005 : = nouveau gestionnaire de rangs
                     + gestion session sécurisée avec contrôle adresse ip

 Fichier : /includes/classV2/ManagerUser.php4
 Description : Classe utilisateur manager

/=================================================================*/

class Promotion
{
	/* Connection Handle */
	private $handle = NULL;

	/* Promotion DB's fields */
	public $id = 0;
	public $type = 0;
	public $type_value = "";
	public $apply = 0;
	public $apply_value = "";
	public $ava = array();
	public $avc = array();
	public $avp = array();
	public $end_trigger = 0;
	public $end_trigger_value = 0;
	public $end_trigger_current = 0;
	public $code = "";
	public $picture = "";
	public $start_time = 0;
	public $end_time = 0;
	public $active = 0;
	public $create_time = 0;
	public $timestamp = 0;
	
	/* Promotion usefull vars */
	public $exist = false;
	public $lastErrorMessage = "";

	/* Constructor */
	function __construct(& $handle, $id = NULL) {
		$this->handle = & $handle;
		if ($id != NULL) {
			$this->id = $id;
			$this->Load();
		}
	}
	
	public static function GetActivePromotionsIDsByTime ($time, $handle) {
		$time = (int)$time;
		$promoIDs = array();
		$res = $handle->query("select id from promotions where start_time <= " . $time . " and end_time >= " . $time, __FILE__, __LINE__);
		while ($rec = $handle->fetch($res)) $promoIDs[] = $rec[0];
		return $promoIDs;
	}
	
	/*
	 * input : array(pdtID1, pdtID2, ... pdtIDn), timestamp, DB handle
	 * output : array (
	 *  promos => products IDs indexed by promotion IDs,
	 *  pdts => promotions IDs indexed by products IDs
	 * )
	 */
	public static function GetActivePromotionIDsFromProductIDs($pdtIDs, $time, $handle) {
		
		$time = (int)$time;
		$ret = array("promos" => array(), "pdts" => array()); // return Array : 
		$advs = array(); // Index by Advertisers IDs
		$fams = array(); // Index by Families IDs
		
		if (empty($pdtIDs)) return $ret;
		
		// Global Promotions
		$res = & $handle->query("
			select p.id
			from promotions p
			where
				p.apply = 0
				and p.start_time <= " . $time . "
				and p.end_time >= " . $time, __FILE__, __LINE__, false);
		while ($promo = & $handle->fetchAssoc($res)) {
			foreach ($pdtIDs as $pdtID) {
				$ret["promos"][$promo["id"]][$pdtID] = true;
				$ret["pdts"][$pdtID][$promo["id"]] = true;
			}
		}
		
		// Specific Promotions
		$res = & $handle->query("
			select p.id, p.idAdvertiser, pf.idFamily
			from products p, products_families pf
			where p.id = pf.idProduct and p.id in (" . implode(",", $pdtIDs) . ")", __FILE__, __LINE__, false);
		while ($pdt = & $handle->fetchAssoc($res)) {
			$advs[$pdt["idAdvertiser"]][$pdt["id"]] = $pdt["id"];
			$fams[$pdt["idFamily"]][$pdt["id"]] = $pdt["id"];
		}
		
		$res = & $handle->query("
			select pa.PromotionID, pa.AdvertiserID, pa.CategoryID, pa.ProductID
			from promotions p, promotions_application pa
			where
				p.id = pa.PromotionID
				and p.apply = 1
				and (pa.AdvertiserID in (" . implode(",", array_keys($advs)) . ") or pa.CategoryID in (" . implode(",", array_keys($fams)) . "))
				and p.start_time <= " . $time . "
				and p.end_time >= " . $time, __FILE__, __LINE__, false);
		while ($promo = & $handle->fetchAssoc($res)) {
			
			if (empty($promo["ProductID"])) {
				if (empty($promo["AdvertiserID"])) {
					// advID = 0 ; famID = 0 ; pdtID = 0
					if (empty($promo["CategoryID"])) {
						continue;
					}
					// advID = 0 ; famID = y ; pdtID = 0
					else {
						foreach ($fams[$promo["CategoryID"]] as $pdtID) {
							$ret["promos"][$promo["PromotionID"]][$pdtID] = true;
							$ret["pdts"][$pdtID][$promo["PromotionID"]] = true;
						}
					}
				}
				else {
					// advID = x ; famID = 0 ; pdtID = 0
					if (empty($promo["CategoryID"])) {
						foreach ($advs[$promo["AdvertiserID"]] as $pdtID) {
							$ret["promos"][$promo["PromotionID"]][$pdtID] = true;
							$ret["pdts"][$pdtID][$promo["PromotionID"]] = true;
						}
					}
					// advID = x ; famID = y ; pdtID = 0
					else {
						foreach ($advs[$promo["AdvertiserID"]] as $pdtID) {
							if (isset($fams[$promo["CategoryID"]][$pdtID])) {
								$ret["promos"][$promo["PromotionID"]][$pdtID] = true;
								$ret["pdts"][$pdtID][$promo["PromotionID"]] = true;
							}
						}
					}
				}
			}
			// advID = x ; famID = 0 ; pdtID = z
			else {
				$ret["promos"][$promo["PromotionID"]][$promo["ProductID"]] = true;
				$ret["pdts"][$promo["ProductID"]][$promo["PromotionID"]] = true;
			}
		}
		
		return $ret;
	}
	
	private function GenerateID() {
		do {
			$id = mt_rand(1, 999999999);
			$result = & $this->handle->query("select id from promotions where id = " . $id, __FILE__, __LINE__);
		}
		while ($this->handle->numrows($result, __FILE__, __LINE__) >= 1);
		
		$this->id = $id;
	}
	
	// TODO copy from parent_id
	public function Create() {
		$this->GenerateID();
	}
	
	public function Load() {
		$this->exist = false;
		
		$res = & $this->handle->query("
		select
			p.id, p.type, p.type_value, p.apply, p.apply_value,
			p.end_trigger, p.end_trigger_value, p.end_trigger_current, p.code, p.picture,
			p.start_time, p.end_time, p.active, p.create_time, p.timestamp
		from
			promotions p
		where
			p.id = " . $this->id, __FILE__, __LINE__, false);
		
		if ($this->handle->numrows($res, __FILE__, __LINE__) > 0) {
			$rec = & $this->handle->fetchAssoc($res);
			
			$this->id = (int)$rec["id"];
			$this->type = (int)$rec["type"];
			$this->type_value = (float)$rec["type_value"];
			$this->apply = (int)$rec["apply"];
			$this->apply_value = $rec["apply_value"];
			$this->end_trigger = (int)$rec["end_trigger"];
			$this->end_trigger_value = (float)$rec["end_trigger_value"];
			$this->end_trigger_current = (float)$rec["end_trigger_current"];
			$this->code = $rec["code"];
			$this->picture = $rec["picture"];
			$this->start_time = (int)$rec["start_time"];
			$this->end_time = (int)$rec["end_time"];
			$this->active = (int)$rec["active"];
			$this->create_time = (int)$rec["create_time"];
			$this->timestamp = (int)$rec["timestamp"];
			
			$this->exist = true;
		}
		else $this->lastErrorMessage = "La promotion n'existe pas dans la base de donnée.";
	}
	
	public function DecodeApplyValues () {
		list($this->ava, $this->avc, $this->avp) = explode(";", $this->apply_value);
		$this->ava = explode(",", $this->ava);
		$this->avc = explode(",", $this->avc);
		$this->avp = explode("|", $this->avp);
		foreach ($this->avp as $k => $v) $this->avp[$k] = explode(",", $v);
	}
	
	public function Save() {
		$this->timestamp = time();
		
		$queries = array();
		
		if (!$this->exist) {
			$this->create_time = $this->timestamp;
			if (empty($this->id)) $this->generateID();
			$queries[] = "
			insert into promotions (
				id, type, type_value, apply, apply_value,
				end_trigger, end_trigger_value, end_trigger_current, code, picture,
				start_time, end_time, active, create_time, timestamp)
			values (" .
				$this->id . ", " .
				$this->type . ", " .
				"'" . $this->handle->escape($this->type_value)  . "', " .
				$this->apply . ", " .
				"'" . $this->handle->escape($this->apply_value) . "', " .
				$this->end_trigger . ", " .
				$this->end_trigger_value . ", " .
				$this->end_trigger_current . ", " .
				"'" . $this->handle->escape($this->code) . "', " .
				"'" . $this->handle->escape($this->picture) . "', " .
				$this->start_time . ", " .
				$this->end_time . ", " .
				$this->active . ", " .
				$this->create_time . ", " .
				$this->timestamp . ")
			";
		}
		else {
			$queries[] = "
			update promotions set
				type = " . $this->type . ",
				type_value = '" . $this->handle->escape($this->type_value) . "',
				apply = " . $this->apply . ",
				apply_value = '" . $this->handle->escape($this->apply_value) . "',
				end_trigger = " . $this->end_trigger . ",
				end_trigger_value = " . $this->end_trigger_value . ",
				end_trigger_current = " . $this->end_trigger_current . ",
				code = '" . $this->handle->escape($this->code) . "',
				picture = '" . $this->handle->escape($this->picture) . "',
				start_time = " . $this->start_time . ",
				end_time = " . $this->end_time . ",
				active = " . $this->active . ",
				create_time = " . $this->create_time . ",
				timestamp = " . $this->timestamp . "
			where id = " . $this->id;
		}
		
		list($avas, $avcs, $avpcs) = explode(";", $this->apply_value);
		$queries[] = "delete from promotions_application where PromotionID = " . $this->id;
		
		// Apply Values for Advertisers
		if (!empty($avas)) {
			$avas = explode(",", $avas);
			foreach ($avas as $ava)
				$queries[] = "insert into promotions_application (PromotionID, AdvertiserID) values (" . $this->id . ", " . $ava . ")";
		}
		
		// Apply Values for Categories
		if (!empty($avcs)) {
			$avcs = explode(",", $avcs);
			foreach ($avcs as $avc)
				$queries[] = "insert into promotions_application (PromotionID, CategoryID) values (" . $this->id . ", " . $avc . ")";
		}
		
		// Apply Values for Products by Categories
		if (!empty($avpcs)) {
			$avpcs = explode("|", $avpcs);
			foreach ($avpcs as $avpc) {
				$avpc = explode(",", $avpc);
				$avpcCount = count($avpc);
				for($i = 1; $i < $avpcCount; $i++)
					$queries[] = "insert into promotions_application (PromotionID, CategoryID, ProductID) values (" . $this->id . ", " . $avpc[0] . ", " . $avpc[$i] . ")";
			}
		}
		
		try {
			foreach ($queries as $query) {
				if (!$this->handle->query($query, __FILE__, __LINE__, false)) {
					throw new Exception("MySQL : Error while Updating the Promotion.");
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
			if (!$this->handle->query("delete from promotions_application where PromotionID = " . $this->id, __FILE__, __LINE__, false))
				throw new Exception("MySQL : Error while Archiving the Promotion " . $this->id);
			return true;
		}
		catch (Exception $e) {
			echo "Error : " . $e->getMessage() . "\n";
			return false;
		}
	}

}

?>
