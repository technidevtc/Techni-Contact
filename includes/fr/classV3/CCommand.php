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

require_once(ICLASS . '_ClassDiscount.php');

define("__COMMAND_DISPATCHED__", 40);

class Command
{
	/* Handle connexion */
	private $handle = NULL;
	private $db = null;
	
	/* Statut de la commande
	0 = n'existe pas dans la DB et n'est pas en cours de création
	1 = n'existe pas dans la DB mais est en cours de création
	5 = existe mais est dupliquée (ne peut normalement pas arriver)
	10 = existe mais non initialisé
	11 = existe et initialisé
	*/
	public $statut = 0;
	public $lastErrorMessage = "";
        public $exists = false;


        private static $fields = array(
		"id" => "",
		"idClient" => 0,
		"timestamp" => 0,
		"create_time" => 0,
		"totalHT" => 0,
		"totalTTC" => 0,
        "totalPrice2HT" => 0,
		"totalPrice2TTC" => 0,
		"fdp" => 0,
		"fdp_tva" => 0,
        "insurance" => 0,
		"promotionCode" => "",
		"campaignID" => 0,
		"type_paiement" => 0,
                "type_commande" => 0,
		"transaction_id" => 0,
		"statut_paiement" => 0,
		"statut_traitement" => 0,
                "statut_timestamp" => 0,
                "planned_delivery_date" => "",
                "cancel_reason" => "",
                "partially_cancelled_reason" => "",
                "open_sav" => "",
                "close_sav" => "",
		"dispatch_time" => 0,
                "dispatch_comment" => "",
		"coord" => array(),
		"produits" => array(),
                "attente_info" => 0);
	
	private static $coordFields = array(
		"idClient" => "",
		"totalHT" => 0,
		"totalTTC" => 0,
                "totalPrice2HT" => 0,
		"totalPrice2TTC" => 0,
		"titre" => "",
		"nom" => "",
		"prenom" => "",
		"societe" => "",
		"adresse" => "",
		"complement" => "",
		"ville" => "",
		"cp" => "",
		"pays" => "",
                "tel1" => "",
		"infos_sup" => "",
		"titre_l" => "",
		"nom_l" => "",
		"prenom_l" => "",
		"societe_l" => "",
		"adresse_l" => "",
		"complement_l" => "",
		"ville_l" => "",
		"cp_l" => "",
		"pays_l" => "",
                "tel2" => "",
		"infos_sup_l" => "",
		"coord_livraison" => 0);
	
	private static $itemFields = array(
		"idProduct" => 0,
		"idTC" => 0,
		"name" => "",
		"fastdesc" => "",
		"label" => "",
		"price" => 0,
		"price2" => 0,
		"unite" => 1,
		"idFamily" => 0,
		"quantity" => 0,
		"tauxTVA" => 0,
		"promotion" => 0,
		"discount" => 0,
		"idAdvertiser" => 0,
		"refSupplier" => "",
		"comment" => "",
    "customCols" => array());
	
	/* Champs de la commande*/
	public $id = 0;
	public $idClient = 0;
	public $timestamp = 0;
	public $create_time = 0;
	public $totalHT = 0;
	public $totalTTC = 0;
        public $totalPrice2HT = 0;
	public $totalPrice2TTC = 0;
	public $fdpHT = 0;
	public $fdp_tva = 0;
    public $insurance = 0;
	public $type_paiement = 0;
        public $type_commande = 0;
	public $transaction_id = 0;
	public $statut_paiement = 0;
	public $statut_traitement = 0;
        public $statut_timestamp = 0;
  public $planned_delivery_date = "";
  public $cancel_reason = "";
  public $partially_cancelled_reason = "";
  public $open_sav = "";
  public $close_sav = "";
	public $dispatch_time = 0;
        public $dispatch_comment = "";
        public $attente_info = 0;
	
	/* Indique si l'objet doit mettre à jour les statistiques */
	public $stats = true;
	
	/* Tableau contenant les différents status de traitement des fournisseurs de la commande */
	public $suppliersProccessingStatus = NULL;
	
	/* Tableau contenant les coordonnées du client */
	public $coord = NULL;
	
	/* Tableau contenant la liste des produits de la commande */
	public $items = NULL;
	public $itemCount = 0;

	/* Tableau contenant la liste des taux de tva */
	public $TVArate = NULL;
	
	// Calculated properties
	public $stotalHT = 0;
	public $totalTVA = 0;
        public $stotalPrice2HT = 0;
	public $totalPrice2TVA = 0;
	public $fdpTVA = 0;
	public $fdpTTC = 0;
	
	/* Calculated VAT table = array(
	 *  VAT_rate => array(
	 *   "total" => product's sum for this rate,
	 *   "tva" => product's tva sum for this rate)) */
	public $tvaTable = null;
        public $tvaPrice2Table = null;
	
	/* Calculated properties per items, set in initCalculatedVars() and calculateCommand()
	 * for items
	 *  priceHT => price with promotions/discounts
	 *  priceTVA => item VAT amount
	 *  priceTTC => final price w/ VAT
	 *  sum_base => price * quantity
	 *  sum_promotion => promotion * quantity
	 *  sum_discount => discount * quantity
	 *  sumHT => final product's w/o VAT sum ( = sum + sum_promotion + sum_discount)
	 *  sumTVA => final product's VAT amount (priceTVA * quantities to have a item based TVA/TTC rounded price)
	 *  sumTTC => final product's w/ VAT sum */
	
	public static function getOrders ($customerID = null, $order = null, $way = "desc") {
		$db = DBHandle::get_instance();
		$res = $db->query("
		select " . implode(",", array_keys(self::$fields)) . "
		from commandes
		" . (!empty($customerID) ? "where idClient = " . $customerID : "") . "
		" . (!empty($order) ? "order by " . $order . " " . ($way != "asc" ? "desc" : "asc") : ""), __FILE__, __LINE__);
		
		$cmds = array();
		while ($cmd = $db->fetchAssoc($res))
			$cmds[] = $cmd;
		
		return $cmds;
	}
	
	public static function getTitle($titleID) {
		switch ($titleID) {
			case 1  : $title = "M."; break;
			case 2  : $title = "Mme."; break;
			case 3  : $title = "Mlle."; break;
			default : $title = "M."; break;
		}
		return $title;
	}
	

	
	public static function getProcessingStatusText($status) {
		$processingStatusList = array(
			0 => "Attente validation paiement",
			10 => "Commande reçue non consultée",
			20 => "Commande en cours de traitement",
			30 => "Commande expédiée");
		return isset($processingStatusList[$status]) ? $processingStatusList[$status] : "";
	}
	
	public static function getGlobalProcessingStatusText($status) {
		$globalProcessingStatusList = array(
			0 => "Attente validation paiement",
			10 => "Commande en attente de traitement",
			20 => "Commande en cours de traitement",
                        21 => "SAV ouvert",
                        22 => "SAV résolu",
                        25 => "Date d’expédition prévisionnelle :",
			30 => "Commande partiellement expédiée",
			40 => "Commande expédiée",
                        90 => "Commande partiellement annulée :",
                        99 => "Commande annulée :");
		return isset($globalProcessingStatusList[$status]) ? $globalProcessingStatusList[$status] : "";
	}
	
	public static function getPaymentTypeText($status) {
		$paymentTypeList = array(
			0 => "Carte Bancaire (type en attente)",
			1 => "Carte Bancaire (Carte Bleue)",
			2 => "Carte Bancaire (Visa)",
			3 => "Carte Bancaire (Mastercard)",
			4 => "Carte Bancaire (American Express)",
			5 => "Paypal",
			10 => "Chèque",
			20 => "Virement bancaire",
			30 => "Paiement différé",
			40 => "Contre-remboursement",
			50 => "Mandat administratif");
		return isset($paymentTypeList[$status]) ? $paymentTypeList[$status] : "";
	}
	
	public static function getPaymentStatusText($status) {
		$paymentStatusList = array(
			0 => "Attente confirmation BNP",
			1 => "Attente chèque",
			2 => "Attente virement",
			3 => "Paiement différé à valider",
			4 => "Paiement par contre-remboursement à valider",
			5 => "Paiement par mandat administratif à valider",
			//6 => "Paiement par Paypal à valider",
			10 => "Payé",
			11 => "Paiement différé validé");
		return isset($paymentStatusList[$status]) ? $paymentStatusList[$status] : "";
	}
	
	/* Constructeur, set la session à utiliser
	i : référence sur la connexion au SGBDR */
	function __construct (&$handle, $id = NULL, $init = "", $stats = true) {
		$this->db = DBHandle::get_instance();
		$this->handle = $handle;
		$this->stats = $stats;
		if ($id != NULL) {
			$this->id = $id;
			$this->load();
			if ($this->statut == 0 && $init == "create") $this->Create(false);
		}
		elseif ($init == "create") $this->Create(true);
	}
	
	private function generateID() {
		do {
			$id = mt_rand(1, 999999999);
			$result = & $this->db->query("select id from commandes where id = " . $id, __FILE__, __LINE__);
		}
		while ($this->db->numrows($result, __FILE__, __LINE__) == 1);
		
		$this->id = $id;
	}
	
	public function create($generate_id = true) {
		
		$this->statut = 1;
		$this->id = 0;
		$this->idClient = 0;
		$this->timestamp = 0;
		$this->create_time = 0;
		$this->totalHT = 0;
		$this->totalTTC = 0;
                $this->totalPrice2HT = 0;
		$this->totalPrice2TTC = 0;
		$this->fdpHT = 0;
		$this->fdp_tva = 0;
		$this->type_paiement = 0;
                $this->type_commande = 0;
		$this->transaction_id = 0;
		$this->statut_paiement = 0;
		$this->statut_traitement = 0;
                $this->statut_timestamp = 0;
    $this->planned_delivery_date = "";
    $this->cancel_reason = "";
    $this->partially_cancelled_reason = "";
    $this->open_sav = "";
    $this->close_sav = "";
		$this->dispatch_time = 0;
                $this->dispatch_comment = "";
		
		$this->items = array();
		$this->itemCount = 0;
		$this->coord = self::$coordFields;
		$this->suppliersProccessingStatus = array();
                $this->attente_info = 0;
		
		if ($generate_id) $this->generateID();
	}
	
	public function getRefNameById($id)
	{

		$res = $this->db->query("
			SELECT ref_name
			FROM products_fr
			WHERE id='".$this->db->escape($id)."'", __FILE__, __LINE__);
		if ($this->db->numrows($res, __FILE__, __LINE__) > 0) {
			$rec = & $this->db->fetchAssoc($res);
			return $rec['ref_name'];
		}else{
			return 'error';
		}
		
	}

        public static function getFromInterval(array $interval) {
		$lastErrorMessage = "";
                $db = DBHandle::get_instance();

                $args = implode(' AND ', $interval);
                $query = "
			select
				idClient, timestamp, create_time, totalHT, totalTTC, totalPrice2HT, totalPrice2TTC,
				fdp as fdpHT, fdp_tva, insurance, promotionCode, campaignID, type_paiement, type_commande,
				transaction_id, statut_paiement, statut_traitement, statut_timestamp, planned_delivery_date, cancel_reason, partially_cancelled_reason, open_sav, close_sav,
        dispatch_time, dispatch_comment, coord, produits as items, attente_info
			from commandes
			where " . $args;

		$res = $db->query($query, __FILE__, __LINE__);
		if ($db->numrows($res, __FILE__, __LINE__) > 0) {
                  while( $row = $db->fetchAssoc($res)){
                    $list[] = $row;
                  }
                  return $list;
                }else
                  return false;
        }

	public function load() {
		$this->lastErrorMessage = "";
		
		$res = $this->db->query("
			select
				idClient, timestamp, create_time, totalHT, totalTTC, totalPrice2HT, totalPrice2TTC,
				fdp as fdpHT, fdp_tva, insurance, promotionCode, campaignID, type_paiement, type_commande,
				transaction_id, statut_paiement, statut_traitement, statut_timestamp, planned_delivery_date, cancel_reason, partially_cancelled_reason, open_sav, close_sav,
        dispatch_time, dispatch_comment, coord, produits as items, attente_info
			from commandes
			where id = " . $this->id, __FILE__, __LINE__);
		if ($this->db->numrows($res, __FILE__, __LINE__) > 0) {
                  if ($this->db->numrows($res, __FILE__, __LINE__) == 1)
                        $this->exists = true;
                  
			$rec = & $this->db->fetchAssoc($res);
			
			foreach ($rec as $name => $value)
				$this->$name = $value;
			
			$this->coord = unserialize($this->coord);
			
			$data = unserialize($this->items);
			// Searching the idTC key position
			foreach($data[0] as $pos => $key) {
				if ($key == "idTC") {
					$idTCpos = $pos;
					break;
				}
			}
			if (!isset($idTCpos)) {
				$this->statut = 0;
				$this->lastErrorMessage = "Tableau d'entré des produits invalide";
			}
			else {
				$this->items = array();
				$size = count($data);
				
				// $data[0] = column headers, so we flip the array to get the indexes of each named header
				// $data[0] = array(0 => "col1", 1 => "col2", ..)  ==>  $headersIndexes = array("col1" => 0, "col2" => 1, ..)
				$headersIndexes = array_flip($data[0]);
				for ($i = 1; $i < $size; $i++) {
					foreach (self::$itemFields as $fieldName => $dftValue) {
						$this->items[$data[$i][$idTCpos]][$fieldName] = isset($data[$i][$headersIndexes[$fieldName]]) ? $data[$i][$headersIndexes[$fieldName]] : "";
					}
				}
				$this->itemCount = count($this->items);

				$res = & $this->db->query("select idAdvertiser, statut_traitement, totalOrdreHT, totalOrdreTTC, fdpOrdreHT, fdpOrdreTTC, dispatch_time, isMailSent, timestampIMS, mailComment, idSender, arc, timestampArc, annulation, motif_annulation, attente_info from commandes_advertisers where idCommande = " . $this->id, __FILE__, __LINE__);
				
				if ($this->db->numrows($res, __FILE__, __LINE__) > 0) {
					$this->suppliersProccessingStatus = array();
					while ($rec = & $this->db->fetchAssoc($res))
						$this->suppliersProccessingStatus[$rec['idAdvertiser']] = $rec;
				}
				
				$this->statut = 11;
				$this->initCalculatedVars();
			}
		}
		else {
			$this->statut = 0;
			$this->lastErrorMessage = "La commande n'existe pas, veuillez la créer avant de pouvoir la charger";
		}
	}


        public function getRefSupplier($idProduct, $idTC){

          $query = 'select refSupplier from references_content where idProduct = '.$idProduct.' and id = '.$idTC;
          
          $res = & $this->db->query($query);
          if ($this->db->numrows($res, __FILE__, __LINE__) == 1){
              $ret = $this->db->fetch($res, __FILE__, __LINE__) ;
            return $ret[0];
          }else
            return false;

        }


        public function getAdvertiserName($idAdvertiser)
	{
		$res = $this->db->query("
			select
				nom1
			from advertisers
			where id = " . $this->db->escape($idAdvertiser), __FILE__, __LINE__);
		$donnees = $this->db->fetchAssoc($res);
		return $donnees['nom1'];
	}

        public function getAdvertiserInfos($idAdvertiser)
	{
		$res = $this->db->query("
			select
				a.nom1, a.email, a.contacts, e.login, e.pass, a.pcontact, a.ncontact, a.econtact
			from advertisers a
                        left join extranetusers e on e.id = a.id
			where a.id = " . $this->db->escape($idAdvertiser), __FILE__, __LINE__);
		$donnees = $this->db->fetchAssoc($res);
                $donnees['contacts'] = unserialize($donnees['contacts']);
		return $donnees;
	}

	public function save() {
		$queries = array();
		
		$itemList = array();
		$itemList[] = array_keys(self::$itemFields);
		foreach ($this->items as $item) {
			$fields = array();
			foreach (self::$itemFields as $fieldName => $dftValue) {
				$fields[] = $item[$fieldName];
			}
			$itemList[] = $fields;
		}
		
		if ($this->statut_traitement == __COMMAND_DISPATCHED__)
			$this->dispatch_time = time();
                
		if ($this->statut == 1) {
			$queries[] = "
				insert into commandes (id, idClient, timestamp, create_time, totalHT, totalTTC, totalPrice2HT, totalPrice2TTC, fdp, fdp_tva, insurance, promotionCode, campaignID, type_paiement, type_commande, statut_paiement, statut_traitement, statut_timestamp, planned_delivery_date, cancel_reason, partially_cancelled_reason, open_sav, close_sav, dispatch_time, dispatch_comment, coord, produits, attente_info)
				values(
					" . $this->id . ",
					" . $this->idClient . ",
					'" . time() . "',
					'" . time() . "',
					'" . $this->totalHT . "',
					'" . $this->totalTTC . "',
                    '" . $this->totalPrice2HT . "',
					'" . $this->totalPrice2TTC . "',
					'" . $this->fdpHT . "',
					'" . $this->fdp_tva . "',
					'" . $this->insurance . "',
					'" . $this->db->escape($this->promotionCode) . "',
					'" . $this->campaignID . "',
					" . $this->type_paiement . ",
                                          " . $this->type_commande . ",
					" . $this->statut_paiement . ",
					" . $this->statut_traitement . ",
                                        " . $this->statut_timestamp . ",
                                        '" . $this->db->escape($this->planned_delivery_date) . "',
                                        '" . $this->db->escape($this->cancel_reason) . "',
                                          '" . $this->db->escape($this->partially_cancelled_reason) . "',
                                        '" . $this->db->escape($this->open_sav) . "',
                                        '" . $this->db->escape($this->close_sav) . "',
					" . $this->dispatch_time . ",
                                        '" . $this->db->escape($this->dispatch_comment) . "',
					'" . $this->db->escape(serialize($this->coord)) . "',
					'" . $this->db->escape(serialize($itemList)) . "',
					'" . $this->db->escape($this->attente_info) . "')";
		}
		elseif($this->statut >= 10) {
			$queries[] = "
				update commandes
				set
					idClient = " . $this->idClient . ",
					timestamp = " . time() . ",
					create_time = " . $this->create_time . ",
					totalHT = " . $this->totalHT . ",
					totalTTC = " . $this->totalTTC . ",
                    totalPrice2HT = " . $this->totalPrice2HT . ",
					totalPrice2TTC = " . $this->totalPrice2TTC . ",
					fdp = " . $this->fdpHT . ",
					fdp_tva = " . $this->fdp_tva . ",
					insurance = ". $this->insurance . ",
					promotionCode = '" . $this->db->escape($this->promotionCode) . "',
					campaignID = ". $this->campaignID . ",
					type_paiement = " . $this->type_paiement . ",
                                        type_commande = " . $this->type_commande . ",
					transaction_id = " . $this->transaction_id . ",
					statut_paiement = " . $this->statut_paiement . ",
					statut_traitement = " . $this->statut_traitement . ",
                                        statut_timestamp = " . $this->statut_timestamp . ",
					planned_delivery_date = '" . $this->db->escape($this->planned_delivery_date) . "',
                                        cancel_reason = '" . $this->db->escape($this->cancel_reason) . "',
                                        partially_cancelled_reason = '" . $this->db->escape($this->partially_cancelled_reason) . "',
                                        open_sav = '" . $this->db->escape($this->open_sav) . "',
                                        close_sav = '" . $this->db->escape($this->close_sav) . "',
					dispatch_time = " . $this->dispatch_time . ",
                                        dispatch_comment = '" . $this->db->escape($this->dispatch_comment) . "',
					coord = '" . $this->db->escape(serialize($this->coord)) . "',
					produits = '" . $this->db->escape(serialize($itemList)) . "',
					attente_info = " . $this->attente_info . "
				where id = " . $this->id;
			
			$queries[] = "delete from commandes_advertisers where idCommande = " . $this->id;
		}
		foreach($this->suppliersProccessingStatus as &$supplierStatus) {
			$queries[] = "
			insert into
				commandes_advertisers (idCommande, idAdvertiser, statut_traitement, dispatch_time, totalOrdreHT, totalOrdreTTC, fdpOrdreHT ,fdpOrdreTTC, isMailSent, timestampIMS, mailComment, idSender, arc, timestampArc, annulation, motif_annulation, attente_info)
			values
				('" . $this->id . "', '" . $supplierStatus["idAdvertiser"] . "', '" . $supplierStatus["statut_traitement"] . "', '" . $supplierStatus["dispatch_time"] .
                                "', '" . $supplierStatus["totalOrdreHT"] . "', '" . $supplierStatus["totalOrdreTTC"] ."', '" . $supplierStatus["fdpOrdreHT"] .
                                "', '" . $supplierStatus["fdpOrdreTTC"] . "', '" . $supplierStatus["isMailSent"] ."', '" . $supplierStatus["timestampIMS"] .
                                "', '" . $this->db->escape($supplierStatus["mailComment"]) . "', '" . $supplierStatus["idSender"] ."', '" . $this->db->escape($supplierStatus["arc"]) .
                                "', '" . $supplierStatus["timestampArc"] . "', '" . $supplierStatus["annulation"] .
                                "', '" .$this->db->escape($supplierStatus["motif_annulation"])  . "', '" . $supplierStatus["attente_info"] ."')";

		}

		try {
			foreach ($queries as $query) {
				if (!$this->db->query($query, __FILE__, __LINE__, false)) {
					throw new Exception("MySQL : Error while Updating the Command.");
				}

			}
		}
		catch (Exception $e) {
			echo "Error : " . $e->getMessage() . "\n";
			return false;
		}
		
		$this->updateStats();
		$this->statut = 11;
		return true;
	}
	
	/*function createFromDevis($idDevis, $type_paiement, $statut_paiement, $statut_traitement) {
		
		$this->create(true);
		
		// Loading Estimate's usefull informations
		$res = & $this->handle->query("
			select
				idClient, totalHT, totalTTC, titre, nom,
				prenom, societe, adresse, complement, ville,
				cp, pays, infos_sup, tel1, titre_l,
				nom_l, prenom_l, societe_l, adresse_l, complement_l,
				ville_l, cp_l, pays_l, infos_sup_l, coord_livraison
			from devis
			where id = '" . $this->handle->escape($idDevis) . "'" , __FILE__, __LINE__);
		
		if ($this->handle->numrows($res, __FILE__, __LINE__) == 1)
			throw new Exception("MySQL : Error while Loading the Estimate " . $idDevis . ".");
		
		$this->coord = $this->handle->fetchAssoc($res);
		$this->idClient = $this->coord["idClient"];
		
		if ($this->coord["coord_livraison"] == 0) {
			$this->coord["titre_l"]      = $this->coord["titre"];
			$this->coord["nom_l"]        = $this->coord["nom"];
			$this->coord["prenom_l"]     = $this->coord["prenom"];
			$this->coord["societe_l"]    = $this->coord["societe"];
			$this->coord["adresse_l"]    = $this->coord["adresse"];
			$this->coord["complement_l"] = $this->coord["complement"];
			$this->coord["ville_l"]      = $this->coord["ville"];
			$this->coord["cp_l"]         = $this->coord["cp"];
			$this->coord["pays_l"]       = $this->coord["pays"];
			$this->coord["infos_sup_l"]  = $this->coord["infos_sup"];
		}
		
		// Loading Estimate's items
		$res = & $this->handle->query("
			select
				dp.idProduct, dp.idTC, dp.idFamily, dp.quantity, dp.comment,
				pfr.name, pfr.fastdesc, rc.label, rc.price, rc.price2,
				rc.unite, rc.idTVA as tauxTVA, rc.refSupplier, p.idAdvertiser, 
			from
				devis_produits dp, products p, products_fr pfr, references_content rc
			where
				p.id = dp.idProduct and p.id = pfr.id and p.id = rc.idProduct and
				dp.idDevis = '" . $this->handle->escape($idDevis) . "'", __FILE__, __LINE__);
				
		if ($this->handle->numrows($res, __FILE__, __LINE__) == 0)
			throw new Exception("MySQL : Error while Loading the Estimate " . $idDevis . " Products.");
			
		while ($item = $this->handle->fetchAssoc($res)) {
			// Getting usefull item properties
			$item["tauxTVA"] = $this->getTVArate($item["tauxTVA"]);
			foreach ($this->savedFields as $field) {
				$this->items[$item["idTC"]][$field] = $item[$field];
			}
			// Setting the Supplier's processing status var
			if (!isset($this->suppliersProccessingStatus[$item["idAdvertiser"]]))
				$this->suppliersProccessingStatus[$item["idAdvertiser"]] = array(
					"idAdvertiser" => $item["idAdvertiser"],
					"statut_traitement" => $statut_traitement,
					"dispatch_time" => 0);
			
			$this->itemCount++;
		}
		
		$this->calculateCommand();
		$this->type_paiement = $type_paiement;
		$this->set_payment_status($statut_paiement);
		$this->set_global_processing_status();
	}
  */
	function createFromCart($cartID, $type_paiement, $statut_paiement, $statut_traitement) {
		
		$this->create(true);
		
		// Loading Cart's usefull informations
		$res = & $this->db->query("
			select
				p.idClient, p.totalHT, p.totalTTC , p.promotionCode, p.insured,
				c.titre, c.nom, c.prenom, c.societe, c.adresse, c.complement, c.ville, c.cp, c.pays, c.infos_sup, c.tel1, c.tel2,
				c.titre_l, c.nom_l, c.prenom_l, c.societe_l, c.adresse_l, c.complement_l, c.ville_l, c.cp_l, c.pays_l, c.infos_sup_l,
				c.coord_livraison
			from paniers p, clients c
			where p.idClient = c.id and p.id = '" . $this->db->escape($cartID) . "'", __FILE__, __LINE__);
		
		if ($this->db->numrows($res, __FILE__, __LINE__) != 1)
			throw new Exception("MySQL : Error while Loading the Cart " . $cartID . ".");
		
        $this->coord = $this->db->fetchAssoc($res);
		$this->idClient = $this->coord["idClient"];
		$this->totalHT = $this->coord["totalHT"];
		$this->totalTTC = $this->coord["totalTTC"];
		$this->promotionCode = $this->coord["promotionCode"];
		unset(
			$this->coord["totalHT"],
			$this->coord["totalTTC"],
			$this->coord["promotionCode"]);
		
        // setting insurance nominal value
        if ($this->coord["insured"]) {
          if ($this->totalTTC >= 60 && $this->totalTTC < 501)
            $this->insurance = 4;
          elseif ($this->totalTTC >= 501 && $this->totalTTC < 1001)
            $this->insurance = 6;
          elseif ($this->totalTTC >= 1001 && $this->totalTTC < 2001)
            $this->insurance = 8;
        }
        
		if ($this->coord["coord_livraison"] == 0) {
			$this->coord["titre_l"]      = $this->coord["titre"];
			$this->coord["nom_l"]        = $this->coord["nom"];
			$this->coord["prenom_l"]     = $this->coord["prenom"];
			$this->coord["societe_l"]    = $this->coord["societe"];
			$this->coord["adresse_l"]    = $this->coord["adresse"];
			$this->coord["complement_l"] = $this->coord["complement"];
			$this->coord["ville_l"]      = $this->coord["ville"];
			$this->coord["cp_l"]         = $this->coord["cp"];
			$this->coord["pays_l"]       = $this->coord["pays"];
                        $this->coord["tel2"]       = $this->coord["tel1"];
			$this->coord["infos_sup_l"]  = $this->coord["infos_sup"];
		}
		
		// Loading Cart's items
		$res = $this->db->query("
			SELECT
				pp.idProduct, pp.idTC, pp.idFamily, pp.quantity, pp.comment,
				pfr.name, pfr.fastdesc, pfr.idAdvertiser,
        rcols.content AS ccols_headers,
        rc.label, rc.price, rc.price2, rc.unite, rc.idTVA AS tauxTVA, rc.refSupplier, rc.content AS ccols_content
			FROM paniers_produits pp
      INNER JOIN products_fr pfr ON pp.idProduct = pfr.id
      INNER JOIN references_cols rcols ON pp.idProduct = rcols.idProduct
      INNER JOIN references_content rc ON pp.idTC = rc.id
			WHERE pp.idPanier = '".$this->db->escape($cartID)."'", __FILE__, __LINE__);
				
    if ($this->db->numrows($res, __FILE__, __LINE__) == 0)
			throw new Exception("MySQL : Error while Loading the Cart " . $cartID . " Products.");
			
		while ($item = $this->db->fetchAssoc($res)) {
      $ccols_headers = unserialize($item["ccols_headers"]);
      $ccols_headers = array_slice($ccols_headers, 3, -5); // get only custom cols headers
      $ccols_content = unserialize($item["ccols_content"]);
      
      for($k=0, $l=count($ccols_headers); $k<$l; $k++)
        $item["customCols"][$ccols_headers[$k]] = $ccols_content[$k];
      
			// Getting usefull item properties
			$item["tauxTVA"] = $this->getTVArate($item["tauxTVA"]);
			foreach (self::$itemFields as $fieldName => $dftValue) {
				$this->items[$item["idTC"]][$fieldName] = $item[$fieldName];
			}
			// Setting the Supplier's processing status var
			if (!isset($this->suppliersProccessingStatus[$item["idAdvertiser"]]))
				$this->suppliersProccessingStatus[$item["idAdvertiser"]] = array(
					"idAdvertiser" => $item["idAdvertiser"],
					"statut_traitement" => 2,
					"dispatch_time" => 0,
                                        "totalOrdreHT" => 0,
                                        "totalOrdreTTC" => 0,
                                        "fdpOrdreHT" => 0,
                                        "fdpOrdreTTC" => 0,
                                        "isMailSent" => 0,
                                        "timestampIMS" => 0,
                                        "mailComment" => '',
                                        "idSender" => 0,
                                        "arc" => '',
                                        "timestampArc" => 0,
                                        "annulation" => 0,
                                        "motif_annulation" => 0,
                                        "attente_info" => 0);
			
			$this->itemCount++;
		}
		
		$this->calculateCommand();
		$this->type_paiement = $type_paiement;
		$this->set_payment_status($statut_paiement);
		$this->set_global_processing_status();
	}
	
	private function updateStats() {
		if ($this->stats) {
			$this->db->query("delete from stats_cmd where idCommand = " . $this->id, __FILE__, __LINE__);
			$timestamp = time();
			foreach($this->items as $item) {
				// TODO a prendre en compte les futurs remises/promo
				// TODO prendre en compte le numéro de famille
				$this->db->query("insert into stats_cmd (idProduct, idTC, idAdvertiser, idFamily, quantity, idCommand, price, price2, timestamp)" .
				" values (" . $item["idProduct"] . ", " . $item["idTC"] . ", " . $item["idAdvertiser"] . ", " . 0 . ", " . $item["quantity"] . ", " . $this->id . ", " . $item["price"] . ", " . $item["price2"] . ", " . $timestamp . ")", __FILE__, __LINE__);
			}
		}
	}
	
	public function getTVArate($idTVA) {
		if ($this->TVArate === NULL) {
			$this->TVArate = array();
			$result = & $this->db->query("select id, taux from tva order by taux desc", __FILE__, __LINE__ );
			while($record = & $this->db->fetch($result))
				$this->TVArate[$record[0]] = $record[1];
		}
		
		return $this->TVArate[$idTVA];
	}
	
	public function UpdateProductQuantity($idTC, $quantity) {
		if (isset($this->items[$idTC])) {
			$this->items[$idTC]["quantity"] = $quantity;
			// Product's quantity changed -> payment not confirmed
			$this->set_processing_status($this->items[$idTC]["idAdvertiser"], 0);
			$this->calculateCommand();
		}
	}
	
	public function AddProduct($idTC, $quantity, $idFamily = null) {
		
		if (isset($this->items[$idTC])) {
			$this->items[$idTC]["quantity"] += $quantity;
			// Product's quantity changed -> payment not confirmed
			$this->set_processing_status($this->items[$idTC]["idAdvertiser"], 0);
		}
		else {
			$res = $this->db->query("
				select
					p.id as idProduct, rc.id as idTC, pfr.name, pfr.fastdesc, rc.label,
					rc.price, rc.price2, rc.unite, " . $quantity . " as quantity, rc.idTVA as tauxTVA,
					p.idAdvertiser, rc.refSupplier, rc.content as customCols
				from
					products p, products_fr pfr, products_families pf, references_content rc
				where
					p.id = pfr.id and p.id = pf.idProduct and p.id = rc.idProduct and rc.id = " . $idTC . (empty($idFamily) ? " group by rc.id" : " and pf.idFamily = " . $idFamily), __FILE__, __LINE__);
                        // correction 14/04/2011 , suppress  and p.id = " . $id . " for $id not declared
			
			$item = & $this->db->fetchAssoc($res);

			$item["tauxTVA"] = $this->getTVArate($item["tauxTVA"]);
			$this->items[$item["idTC"]] = & $item;

                        $query = 'SELECT p.idAdvertiser
                                          FROM references_content rc
                                          JOIN products p ON p.id = rc.idProduct
                                          WHERE rc.id = '.$this->db->escape($idTC);
                                $result = $this->db->query($query);
                                $num = $this->db->numrows($result);
                                if($num == 1){
                                  $ret = $this->db->fetchAssoc($result);
                                  $refSupplier = $ret['idAdvertiser'];

                                }  else {
                                  $errorstring .= "- id produit introuvable<br />\n";
                                }

                                if(!empty ( $refSupplier))
                                  $this->updateCommandeAdvertiser( $refSupplier);

			// New product added -> payment not confirmed

                        if(empty ($this->suppliersProccessingStatus[$refSupplier]) ){
                          $this->suppliersProccessingStatus[$refSupplier] = array(
					"idAdvertiser" => $refSupplier,
					"statut_traitement" => 2,
					"dispatch_time" => 0,
                                        "totalOrdreHT" => 0,
                                        "totalOrdreTTC" => 0,
                                        "fdpOrdreHT" => 0,
                                        "fdpOrdreTTC" => 0,
                                        "isMailSent" => 0,
                                        "timestampIMS" => 0,
                                        "mailComment" => '',
                                        "idSender" => 0,
                                        "arc" => '',
                                        "timestampArc" => 0,
                                        "annulation" => 0,
                                        "motif_annulation" => 0,
                                        "attente_info" => 0);

                        }
                        
			$this->set_processing_status($item["idAdvertiser"], 0);
			$this->itemCount++;
		}
		
		$this->calculateCommand();
	}
	
	public function DelProduct($idTC) {
		if (isset($this->items[$idTC])) {
			// Saving the supplier ID to delete if necessary
			$supplierID = $this->items[$idTC]["idAdvertiser"];
			
			// No supplier's processing status update when deleting a product
			unset($this->items[$idTC]);
			$this->itemCount--;
			$this->calculateCommand();
			
			// Searching if there are others products from this same supplier
			$keys = array_keys($this->items);
			for ($i = 0; $i < $this->itemCount; $i++) {
				if ($this->items[$keys[$i]]["idAdvertiser"] == $supplierID) {
					$supplierID = 0; // yes there is at least one
					break;
				}
			}
			// No other products from this supplier -> delete & update the global processing status
			if ($supplierID != 0) {
				unset($this->suppliersProccessingStatus[$supplierID]);
				$this->set_global_processing_status();
			}
		}
	}
	
	public function ClearProducts() {
		$this->items = array();
		$this->itemCount = 0;
		$this->calculateCommand();
		$this->suppliersProccessingStatus = array();
		$this->statut_traitement = 0;
                $this->statut_timestamp = 0;
		$this->planned_delivery_date = "";
    $this->dispatch_time = 0;
	}
	
	public function setCoordFromArray(&$coordArray) {
		foreach($coordArray as $fieldName => $value) {
			if (isset(self::$coordFields[$fieldName]))
				$this->coord[$fieldName] = $value;
		}
	}
	
	public function & getCoordFromArray() {
		$coordArray = array();
		foreach(self::$coordFields as $fieldName => $dftValue) {
			$coordArray[$fieldName] = $this->coord[$fieldName];
		}
		return $coordArray;
	}
	
	private function calculateCommand() {
		
		// Filling 2 usefull arrays
		$itemIDs = array();
		foreach ($this->items as $item) {
			$itemIDs[] = $item["idProduct"];
		}
		
		// Loading active Discounts and Promotions
		$discIDs = Discount::GetActiveDiscountIDsFromProductIDs($itemIDs, time(), $this->handle);
		$promoIDs = Promotion::GetActivePromotionIDsFromProductIDs($itemIDs, time());
		foreach ($discIDs["discs"] as $discID => $itemIDs)
			$discs[$discID] = new Discount($this->handle, $discID);
		foreach ($promoIDs["promos"] as $promoID => $itemIDs)
			$promos[$promoID] = new Promotion($promoID);
		
		// Getting product's idTCs to loop faster
		$sumByAdv = array(); // Sum by Advertiser for Discounts
		
		// First loop to apply promotions and modify some vars
		foreach ($this->items as &$item) {
			
			$item["price"] = round($item["price"], 6);
			// $item["quantity"] being always a positive integer, we only have to reround after VAT application
			
			// Applying Promotions
			$item["promotion"] = 0;
			if (!empty($promoIDs["promos"]) && isset($promoIDs["pdts"][$item["idProduct"]])) {
				foreach ($promoIDs["pdts"][$item["idProduct"]] as $promoID => $val) {
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
			
			// Setting calculated vars
			$item["sum_base"] = $item["quantity"] * $item["price"];
                        $item["sum_base_price2"] = $item["quantity"] * $item["price2"];
			$item["sum_promotion"] = $item["quantity"] * $item["promotion"];
			
			// Sum by Advertisers taking account the promotions
			$sumByAdv[$item["idAdvertiser"]] = (isset($sumByAdv[$item["idAdvertiser"]]) ? $sumByAdv[$item["idAdvertiser"]] : 0) + $item["sum_base"];
		}
		
		// Second loop to apply discounts and calculate totals
		$this->stotalHT = $this->stotalPrice2HT = $this->totalTVA = 0;
		$this->tvaTable = array();
                $this->tvaPrice2Table = array();

                $dataByAdvertisers = array();
		foreach ($this->items as &$item) {
			
			// Applying Discounts after promotions
			$item["discount"] = 0;
			if (!empty($discIDs["discs"]) && $discIDs["pdts"][$item["idProduct"]]) {
				foreach ($discIDs["pdts"][$item["idProduct"]] as $discID => $itemIDs) {
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
			$item["priceHT"] = $item["price"] - ($item["promotion"] + $item["discount"]);
			$item["priceTVA"] = round($item["priceHT"] * $item["tauxTVA"] / 100, 6);
			$item["priceTTC"] = $item["priceHT"] + $item["priceTVA"];

                        $item["pricePrice2HT"] = $item["price2"] - ($item["promotion"] + $item["discount"]);
			$item["pricePrice2TVA"] = round($item["pricePrice2HT"] * $item["tauxTVA"] / 100, 6);
			$item["pricePrice2TTC"] = $item["pricePrice2HT"] + $item["pricePrice2TVA"];


			$item["sum_discount"] = $item["quantity"] * $item["discount"];
			$item["sumHT"] = $item["quantity"] * $item["priceHT"];
			$item["sumTVA"] = $item["quantity"] * $item["priceTVA"]; // no round needed : != $item["sumHT"] * $item["tauxTVA"] because with great quantities the result is not the same
			$item["sumTTC"] = $item["sumHT"] + $item["sumTVA"];

                        $item["sumPrice2HT"] = $item["quantity"] * $item["pricePrice2HT"];
                        $item["sumPrice2TVA"] = $item["quantity"] * $item["pricePrice2TVA"]; // no round needed : != $item["sumHT"] * $item["tauxTVA"] because with great quantities the result is not the same
			$item["sumPrice2TTC"] = $item["sumPrice2HT"] + $item["sumPrice2TVA"];

			$this->stotalHT += $item["sumHT"];
			$this->totalTVA += $item["sumTVA"];
                        $this->stotalPrice2HT += $item["sumPrice2HT"];
                        $this->totalPrice2TVA += $item["sumPrice2TVA"];
			
			// Updating the TVA table
			if (!isset($this->tvaTable[$item["tauxTVA"]]))
				$this->tvaTable[$item["tauxTVA"]] = array("total" => 0, "tva" => 0);
			$this->tvaTable[$item["tauxTVA"]]["total"] += $item["sumHT"];
			$this->tvaTable[$item["tauxTVA"]]["tva"] += $item["sumTVA"];
                        if (!isset($this->tvaPrice2Table[$item["tauxTVA"]]))
				$this->tvaPrice2Table[$item["tauxTVA"]] = array("total" => 0, "tva" => 0);
			$this->tvaPrice2Table[$item["tauxTVA"]]["total"] += $item["sumPrice2HT"];
			$this->tvaPrice2Table[$item["tauxTVA"]]["tva"] += $item["sumPrice2TVA"];
                        
                        // gestion ordre update : 05/05/2011
                        $this->suppliersProccessingStatus[$item["idAdvertiser"]]['totalOrdreHT'] = 0;
                        $this->suppliersProccessingStatus[$item["idAdvertiser"]]['totalOrdreTTC'] = 0;

                        if(empty ($dataByAdvertisers[$item["idAdvertiser"]]))
                          $dataByAdvertisers[$item["idAdvertiser"]] = array(
                            'totalOrdreHT' => 0,
                            'totalOrdreTVA' => 0,
                          );

                        $dataByAdvertisers[$item["idAdvertiser"]]['totalOrdreHT'] +=  $item["quantity"] * $item["price2"];
                        $dataByAdvertisers[$item["idAdvertiser"]]['totalOrdreTVA'] +=  round(($item["quantity"] * $item["price2"]) * $item["tauxTVA"] / 100, 6);

                        $this->suppliersProccessingStatus[$item["idAdvertiser"]]['totalOrdreHT'] += $dataByAdvertisers[$item["idAdvertiser"]]['totalOrdreHT'];
                        $this->suppliersProccessingStatus[$item["idAdvertiser"]]['totalOrdreTTC'] += $dataByAdvertisers[$item["idAdvertiser"]]['totalOrdreHT']+$dataByAdvertisers[$item["idAdvertiser"]]['totalOrdreTVA'];

		}
		
		// Delivery Fee
		$this->fdpHT = 20;
		$fdp_franco = 300;
		$fdp_idTVA = 0;
		if ($res = & $this->db->query("select config_name, config_value from config where config_name = 'fdp' or config_name = 'fdp_franco' or config_name = 'fdp_idTVA'", __FILE__, __LINE__ )) {
			while ($rec = & $this->db->fetch($res)) {
				$$rec[0] = $rec[1];
			}
			$this->fdpHT = $fdp;
		}
		
		// Setting calculated vars
		if ($this->stotalHT > $fdp_franco) $this->fdpHT = 0;
		$this->fdp_tva = $this->getTVArate($fdp_idTVA);
		$this->fdpTVA = round($this->fdpHT * $this->fdp_tva / 100, 2);
		$this->fdpTTC = $this->fdpHT + $this->fdpTVA;
		
		// Setting saved vars
		$this->totalTVA = round($this->totalTVA + $this->fdpTVA, 2);
		$this->totalHT = round($this->stotalHT + $this->fdpHT, 2);
		$this->totalTTC = $this->totalHT + $this->totalTVA + $this->insurance;
        $this->totalPrice2HT = round($this->stotalPrice2HT + $this->fdpHT, 2);
		$this->totalPrice2TTC = $this->totalPrice2HT + $this->totalTVA;
	}
	
	public function initCalculatedVars () {
		
		$this->tvaTable = array();
                $this->tvaPrice2Table = array();
		//$this->stotalHT = $this->totalTVA = 0;
		foreach($this->items as &$item) {
			
			//$item["url"] = URL . "produits/" . $item["idFamily"] . "-" . $item["idProduct"] . "-" . strtolower($item["ref_name"]) . ".html";
			// $item["quantity"] being always a positive integer, we only have to reround after VAT application
			$item["price"] = round($item["price"], 6);
			$item["promotionpc"] = round($item["promotion"] / $item["price"] * 100, 6);
			$item["discountpc"] = round($item["discount"] / $item["price"] * 100, 6);
			
			$item["priceHT"] = $item["price"] - ($item["promotion"] + $item["discount"]);
			$item["priceTVA"] = round($item["priceHT"] * $item["tauxTVA"] / 100, 6);
			$item["priceTTC"] = $item["priceHT"] + $item["priceTVA"];
			
			$item["sum_base"] = $item["quantity"] * $item["price"];
                        $item["sum_base_price2"] = $item["quantity"] * $item["price2"];
			$item["sum_promotion"] = $item["quantity"] * $item["promotion"];
			$item["sum_discount"] = $item["quantity"] * $item["discount"];
			$item["sumHT"] = $item["quantity"] * $item["priceHT"];
			$item["sumTVA"] = $item["quantity"] * $item["priceTVA"];
			$item["sumTTC"] = $item["sumHT"] + $item["sumTVA"];
			
			//$this->stotalHT += $item["sumHT"];
			//$this->totalTVA += $item["sumTVA"];
			
			// Filling the TVA table
			if (!isset($this->tvaTable[$item["tauxTVA"]]))
				$this->tvaTable[$item["tauxTVA"]] = array("total" => 0, "tva" => 0);
			$this->tvaTable[$item["tauxTVA"]]["total"] += $item["sumHT"];
			$this->tvaTable[$item["tauxTVA"]]["tva"] += $item["sumTVA"];
                        if (!isset($this->tvaPrice2Table[$item["tauxTVA"]]))
				$this->tvaPrice2Table[$item["tauxTVA"]] = array("total" => 0, "tva" => 0);
			$this->tvaPrice2Table[$item["tauxTVA"]]["total"] += $item["sumPrice2HT"];
			$this->tvaPrice2Table[$item["tauxTVA"]]["tva"] += $item["sumPrice2TVA"];
		}
		
		$this->fdpTVA = round($this->fdpHT * $this->fdp_tva / 100, 2);
		$this->fdpTTC = $this->fdpHT + $this->fdpTVA;
		
		//$this->totalTVA = round($this->totalTVA + $this->fdpTVA, 2);
		$this->totalTVA = $this->totalTTC - ($this->totalHT + $this->insurance);
		$this->stotalHT = $this->totalHT - $this->fdpHT;
        $this->totalPrice2TVA = $this->totalPrice2TTC - $this->totalPrice2HT;
		$this->stotalPrice2HT = $this->totalPrice2HT - $this->fdpHT;
		//$this->totalHT = $this->stotalHT + $this->fdpHT;
		//$this->totalTTC = $this->stotalHT + $this->totalTVA + $this->fdpTTC;
	}
	
	public function set_payment_status($val) {
		settype($val, "integer");
		$this->statut_paiement = $val;
		
		// Updating each Advertiser's Order Processing Status
                // edit: the order status belongs to COrder class? OD 10/03/2011
//		foreach ($this->suppliersProccessingStatus as &$supplierStatus) {
//			if ($val >= 10) { // Payment as been confirmed
//				if ($supplierStatus["statut_traitement"] < 10) // Make the order visible
//					$supplierStatus["statut_traitement"] = 10;
//			}
//			else { // Payment not confirmed
//				if ($supplierStatus["statut_traitement"] >=10 && $supplierStatus["statut_traitement"] < 20) // If the order is still not processed, we hide it
//					$supplierStatus["statut_traitement"] = 0;
//			}
//		}
		
		// Update the global Order Processing Status
		$this->set_global_processing_status();
	}
	
	public function set_processing_status($supplierID, $val) {
		settype($val, "integer");
		if (isset($this->suppliersProccessingStatus[$supplierID]))
			$this->suppliersProccessingStatus[$supplierID]["dispatch_time"] = $val >= 30 ? time() : 0;
		
		$this->set_global_processing_status();
	}
  
	public function set_global_processing_status() {
		$statusList = array(0 => 0, 10 => 0, 20 => 0, 30 => 0, 40 => 0);

		foreach ($this->suppliersProccessingStatus as &$supplierStatus)
			$statusList[$supplierStatus["statut_traitement"] - $supplierStatus["statut_traitement"]%10]++;
		
		// At least one unconfirmed payment -> Waiting for confirmation
		if ($statusList[0] != 0) $this->statut_traitement = 0;
		
		// Every supplier dispatched the order -> Whole order is dispatched
		elseif ($statusList[10] == 0 && $statusList[20] == 0) {
			$this->statut_traitement = 40;
			$this->dispatch_time = time();
		}
		
		// At least one supplier dispatched the order -> order partially dispatched
		elseif ($statusList[30] != 0) $this->statut_traitement = 30;
		
		// At least one supplier began to process the order -> order partially processed
		elseif ($statusList[20] != 0) $this->statut_traitement = 20;
		
		// no unconfirmed payment, no dispatch and no processing -> waiting to be processed
		else $this->statut_traitement = 10;
	}

       public function setMessageSent() {
          if($this->attente_info == 0)
                  $this->attente_info = __MSGR_CTXT_CUSTOMER_TC_CMD__;
          elseif($this->attente_info == __MSGR_CTXT_SUPPLIER_TC_ORDER__)
                  $this->attente_info = __MSGR_CTXT_ORDER_CMD__;

          return $this->save();

        }

        public function setMessageAnswered() {

          if($this->attente_info == __MSGR_CTXT_ORDER_CMD__)
                  $this->attente_info = __MSGR_CTXT_SUPPLIER_TC_ORDER__;
          elseif($this->attente_info == __MSGR_CTXT_CUSTOMER_TC_CMD__)
                  $this->attente_info = 0;

          return $this->save();

        }

        public function isCmdAdvertiser($id){

          if(!isset($id) || !is_numeric($id) || $id==0)
            return false;
          
          $query = 'select idCommande from commandes_advertisers where idCommande = '.$this->id.' and idAdvertiser = '.$id;

          $res = & $this->db->query($query);
          if ($this->db->numrows($res, __FILE__, __LINE__) == 1)
            return true;
          else
            return false;

        }

        public function updateCommandeAdvertiser($idAdvertiser){

          if($this->isCmdAdvertiser($idAdvertiser)){

            $timestampIMS = $this->suppliersProccessingStatus[$idAdvertiser]['timestampIMS'] ? $this->suppliersProccessingStatus[$idAdvertiser]['timestampIMS'] : time();

            $query = "update commandes_advertisers set ".
                    "`isMailSent` = '".$this->suppliersProccessingStatus[$idAdvertiser]['isMailSent']."', ".
                    "`timestampIMS` = '".$timestampIMS."', ".
                    "`mailComment` = '".$this->db->escape($this->suppliersProccessingStatus[$idAdvertiser]['commentMail'])."', ".
                    "`idSender` = '".$this->suppliersProccessingStatus[$idAdvertiser]['idSender']."', ".
                    "`totalOrdreHT` = '".$this->suppliersProccessingStatus[$idAdvertiser]['totalOrdreHT']."', ".
                    "`totalOrdreTTC` = '".$this->suppliersProccessingStatus[$idAdvertiser]['totalOrdreTTC']."' ".
                    "WHERE `idCommande` = '".$this->id."' AND `idAdvertiser` = '".$idAdvertiser."';";
            return $this->db->query($query);
            
          }else
            return false;
        }

        public function updateArc($idAdvertiser, $arcName){

          if($this->isCmdAdvertiser($idAdvertiser)){

            $timestampArc = $this->suppliersProccessingStatus[$idAdvertiser]['timestampArc'] = time();
            $this->suppliersProccessingStatus[$idAdvertiser]['arc'] = trim($arcName);

            $query = "update commandes_advertisers set ".
                    "`arc` = '".$this->suppliersProccessingStatus[$idAdvertiser]['arc']."', ".
                    "`statut_traitement` = '4', ".
                    "`timestampArc` = '".$timestampArc."' ".
                    "WHERE `idCommande` = '".$this->id."' AND `idAdvertiser` = '".$idAdvertiser."';";

            if($this->db->query($query)){
              $order = new Order($this->handle, $idAdvertiser.'-'.$this->id);

              $order->setArcStatus();

              return true;
            }else
              return false;

          }else
            return false;
        }


        public function processSendMailToAdvertiser($idAdvertiser, $idUser, $comment = null){

          if(!$this->suppliersProccessingStatus[$idAdvertiser]['isMailSent'] && $this->isCmdAdvertiser($idAdvertiser)){
            $this->suppliersProccessingStatus[$idAdvertiser]['statut_traitement'] = 2;
            $this->suppliersProccessingStatus[$idAdvertiser]['isMailSent'] = 1;
            $this->suppliersProccessingStatus[$idAdvertiser]['timestampIMS'] = time();
            $this->suppliersProccessingStatus[$idAdvertiser]['commentMail'] = $comment;
            $this->suppliersProccessingStatus[$idAdvertiser]['idSender'] = $idUser;

            return $this->updateCommandeAdvertiser($idAdvertiser);

          }else
            return false;

        }


}

?>
