<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : OD pour Hook Network SARL - http://www.hook-network.com
 Date de création : 14 février 2011


 Fichier : /includes/classV2/COrder.php
 Description : Classe de gestion des ordres (commandes fournisseurs)

/=================================================================*/


class OrderOld
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

        /* order fields */
	public $idOrder = 0;
        public $idCommande = 0;
        public $idAdvertiser = 0;
	public $idClient = 0;
	public $timestamp = 0;
	public $create_time = 0;
        public $timestampIMS = 0;
	public $totalHT = 0;
	public $totalTTC = 0;
        public $totalPrice2HT = 0;
	public $totalPrice2TTC = 0;
	public $fdpHT = 0;
	public $fdp_tva = 0;
	public $type_paiement = 0;
        public $type_commande = 0;
	public $transaction_id = 0;
	public $statut_paiement = 0;
	public $statut_traitement_order = 0;
        public $statut_traitement_commande = 0;
        public $statut_timestamp_order = 0;
  public $planned_delivery_date = "";
        public $cancel_reason = "";
        public $open_sav = "";
        public $close_sav = "";
	public $dispatch_time = 0;
        public $exists = false;

        private static $fields = array(
		"id" => "",
		"idClient" => 0,
		"timestamp" => 0,
		"create_time" => 0,
                "timestampIMS" => 0,
		"totalHT" => 0,
		"totalTTC" => 0,
            	"totalPrice2HT" => 0,
		"totalPrice2TTC" => 0,
		"fdp" => 0,
		"fdp_tva" => 0,
		"promotionCode" => "",
		"campaignID" => 0,
		"type_paiement" => 0,
            "type_commande" => 0,
		"transaction_id" => 0,
		"statut_paiement" => 0,
		"statut_traitement_order" => 0,
                "statut_traitement_commande" => 0,
            "statut_timestamp_order" => 0,
    "planned_delivery_date" => "",
            "cancel_reason" => "",
            "open_sav" => "",
            "close_sav" => "",
		"dispatch_time" => 0,
		"coord" => array(),
		"produits" => array());

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


        /* Constructeur, set la session à utiliser
	i : référence sur la connexion au SGBDR */
	function __construct (&$handle, $id = NULL) {
		$this->db = DBHandle::get_instance();
		$this->handle = $handle;
                if(!preg_match('/^[1-9]{1}[0-9]{0,8}\-[1-9]{1}[0-9]{0,8}$/', $id))                  return false;
		if ($id != NULL) {
			$this->idOrder = $id;
                        $idOrder = explode('-', $id);
                        $this->idAdvertiser = $idOrder[0];
                        $this->idCommande = $idOrder[1];
			$this->load();
		}
		else
                  return false;
	}

        public function getGlobalProcessingStatusText($status = null) {

          if(!$status)  $status = $this->statut_traitement_order;

          if($status <= 2)
            $status = 2;

          // whatever the status, cancelled and asking for info are prioritary
          if($this->attente_info) $status = 100;
    
          if($status >= 4){
            $cmd = new Command($this->handle,$this->idCommande);
            
            if($cmd->statut_traitement >= 25)
               $status = $cmd->statut_traitement;
          }

          if($this->annulation) $status = 101;
// defined as it was in CCommand
    
		$globalProcessingStatusList = array(
			0 => "Attente validation paiement",
                        2 => "Non encore consultée", // fiche commande non visitée via le mail de notification ou via la liste des commandes
                        3 => "Attente Accusé Réception", // commande lue et validée dont l'ARC n'a pas encore été lié
                        4 => "AR commande reçu", // commande lue avec ARC uploadé
			10 => "Commande en attente de traitement",
                        25 => "Date d’expédition fixée", // Commande dont la date d?expédition a été indiquée en manager sur fiche commande.
			20 => "Commande en cours de traitement",
			30 => "Commande partiellement expédiée",
			40 => "Commande expédiée",
                        100 => "Attente d'information supp.", // Le fournisseur a demandé un supplément d?information
                        101 => "Commande annulée"); // Annulée par Techni-Contact

		return isset($globalProcessingStatusList[$status]) ? $globalProcessingStatusList[$status] : "";
	}


        public function load() {
            $this->lastErrorMessage = "";

            $res = $this->db->query("
                    select
                        ca.idCommande, ca.idAdvertiser, ca.totalOrdreHT, ca.totalOrdreTTC, ca.fdpOrdreHT, ca.fdpOrdreTTC, ca.statut_traitement as statut_traitement_order,
                        ca.statut_timestamp as statut_timestamp_order, ca.dispatch_time,
                        ca.isMailSent, ca.timestampIMS, ca.mailComment, ca.idSender, ca.arc, ca.timestampArc, ca.timestampSeen, ca.annulation, ca.motif_annulation, ca.attente_info,
                            c.idClient, c.timestamp, c.create_time, c.totalHT, c.totalTTC, c.totalPrice2HT, c.totalPrice2TTC,
                            c.fdp as fdpHT, c.fdp_tva, c.promotionCode, c.campaignID, c.type_paiement, c.type_commande,
                            c.transaction_id, c.statut_paiement, c.statut_traitement as statut_traitement_commande, c.planned_delivery_date, 
                            c.cancel_reason, c.open_sav, c.close_sav,
                            c.dispatch_time, c.coord, c.produits as items
                    from commandes_advertisers ca inner join commandes c on ca.idCommande = c.id
                    where ca.idCommande = " . $this->idCommande. " and ca.idAdvertiser = " . $this->idAdvertiser, __FILE__, __LINE__);
            if ($this->db->numrows($res, __FILE__, __LINE__) > 0) {
              $this->exists = true;
              $this->stotalOrdreHT = 0;
              $this->stotalOrdreTTC = 0;

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
                             
                                    if($data[$i][13] != $this->idAdvertiser) unset ($this->items[$data[$i][$idTCpos]]); // erasing items from other suppliers
                            }

                            foreach($this->items as $nbItem => $data){
//                                     var_dump($data['price2'], $data['quantity'], $data['tauxTVA']);//var_dump($data[$i][13], $this->idAdvertiser, $data[$i][$idTCpos]);
//                                    echo '<br /><br />';
                                    $this->stotalOrdreHT += $data['price2']*$data['quantity'];
                                    $stotalTVA = round($this->stotalOrdreHT * $data['tauxTVA'] / 100, 2);
                                    $this->stotalOrdreTTC = $this->stotalOrdreHT+$stotalTVA;
//                                    var_dump($this->stotalOrdreHT, $stotalTVA, $this->stotalOrdreTTC);echo '<br /><br />';
                            }
//                            $this->itemCount = count($this->items);
//
//                            $res = & $this->db->query("select idAdvertiser, statut_traitement, dispatch_time, isMailSent, timestampIMS, mailComment, idSender, arc, timestampArc, annulation from commandes_advertisers where idCommande = " . $this->id, __FILE__, __LINE__);
//
//                            if ($this->db->numrows($res, __FILE__, __LINE__) > 0) {
//                                    $this->suppliersProccessingStatus = array();
//                                    while ($rec = & $this->db->fetchAssoc($res))
//                                            $this->suppliersProccessingStatus[$rec['idAdvertiser']] = $rec;
//                            }
//        var_dump($this->items);
                            $this->statut = 11;
//                            $this->initCalculatedVars();
                    }
            }
            else {
                    $this->statut = 0;
                    $this->lastErrorMessage = "La commande recherchée n'existe pas!";
            }
        }

        private function setStatus($status){

          if(!empty($status) && is_numeric($status)){
            $query = "update commandes_advertisers set ".
                    "`statut_traitement` = '".$status."', ".
                    "`statut_timestamp` = '".  time() ."' ".
                    "WHERE `idCommande` = '".$this->idCommande."' AND `idAdvertiser` = '".$this->idAdvertiser."';";

            if($this->db->query($query))
              return $this->statut_traitement_order = $status;
            else
              return false;
          }else
            return false;

        }

        public function setAsSeen(){
          if($this->statut_traitement_order == 2)
            if($this->setStatus(3)){
              $query = "update commandes_advertisers set ".
                    "`timestampSeen` = '".time()."' ".
                    "WHERE `idCommande` = '".$this->idCommande."' AND `idAdvertiser` = '".$this->idAdvertiser."';";

              if($this->db->query($query))
                return 3;
              else
                return false;
            }else
            return false;
          else
            return false;
        }

        public function setArcStatus(){
          if($this->statut_traitement_order == 0)
            return $this->setStatus(4);
          else
            return false;
        }

        public function setDeliveryDateStatus(){
          if($this->setStatus(4))
            return true;
          else
            return false;
        }

       public function getAdvertiserName()
	{
		$res = $this->db->query("
			select
				nom1
			from advertisers
			where id = " . $this->db->escape($this->idAdvertiser), __FILE__, __LINE__);
		$donnees = $this->db->fetchAssoc($res);
		return $donnees['nom1'];
	}

        public function getSenderName()
	{
		$res = $this->db->query("
			select
				name
			from bo_users
			where id = " . $this->db->escape($this->idSender), __FILE__, __LINE__);
                if($this->db->numrows($res, __FILE__, __LINE__) == 1)
                  $donnees = $this->db->fetchAssoc($res);
                else
                  return 'Information non disponible';
		return $donnees['name'];
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

        public function & getDeliveryInfos() {
		$coordArray = array();
		foreach(self::$coordFields as $fieldName => $dftValue) {
			$coordArray[$fieldName] = $this->coord[$fieldName];
		}
		return $coordArray;
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

        public function cancelOrder($motif) {
          if(!empty($motif)){
            $query = "update commandes_advertisers set ".
                    "`annulation` = 1, `motif_annulation` = '".$this->db->escape(utf8_decode($motif))."' ".
                    "WHERE `idCommande` = '".$this->idCommande."' AND `idAdvertiser` = '".$this->idAdvertiser."';";

            if($this->db->query($query))
              return true;
            else
              return false;
          }else
            return false;

        }

        public function setMessageSent() {
          if($this->attente_info == 0)
                  $attente_info = __MSGR_CTXT_SUPPLIER_TC_ORDER__;
          elseif($this->attente_info == __MSGR_CTXT_CUSTOMER_TC_CMD__)
                  $attente_info = __MSGR_CTXT_ORDER_CMD__;

            $query = "update commandes_advertisers set ".
                    "`attente_info` = ".$attente_info." ".
                    "WHERE `idCommande` = '".$this->idCommande."' AND `idAdvertiser` = '".$this->idAdvertiser."';";

            if($this->db->query($query))
              return true;
            else
              return false;

        }

        public function setMessageAnswered() {
          if($this->attente_info == __MSGR_CTXT_ORDER_CMD__)
                  $attente_info = __MSGR_CTXT_CUSTOMER_TC_CMD__;
          elseif($this->attente_info == __MSGR_CTXT_SUPPLIER_TC_ORDER__)
                  $attente_info = 0;
          elseif($this->attente_info == 0)
                  $attente_info = 0;

            $query = "update commandes_advertisers set ".
                    "`attente_info` = ".$attente_info." ".
                    "WHERE `idCommande` = '".$this->idCommande."' AND `idAdvertiser` = '".$this->idAdvertiser."';";

            if($this->db->query($query))
              return true;
            else
              return false;

        }

        public function updateAmounts(){

          $totalHT = 0;
          $totalTTC = 0;

          foreach( $this->items as $idProduit => $item){
            $totalHT += $item['price2'] * $item['quantity'];
          }
          $this->totalOrdreHT = $totalHT;
          $stotalTVA = round($totalHT * $item['tauxTVA'] / 100, 2);
          $this->totalOrdreTTC = $totalHT+$stotalTVA+$this->fdpOrdreTTC;

          $this->updateOrder();

        }

        public function updateOrder(){

          $query = "update commandes_advertisers set ".
                    "`totalOrdreHT` = ".$this->totalOrdreHT.", ".
                    "`totalOrdreTTC` = ".$this->totalOrdreTTC.", ".
                    "`fdpOrdreHT` = ".$this->fdpOrdreHT.", ".
                    "`fdpOrdreTTC` = ".$this->fdpOrdreTTC.", ".
                    "`arc` = '".$this->arc."', ".
                    "`timestampArc` = ".$this->timestampArc.", ".
                    "`timestampSeen` = ".$this->timestampSeen.", ".
                    "`statut_traitement` = '".$this->statut_traitement."', ".
                    "`statut_timestamp` = '".$this->statut_timestamp."' ".
                    "WHERE `idCommande` = '".$this->idCommande."' AND `idAdvertiser` = '".$this->idAdvertiser."';";
//            var_dump($query);exit;
            if($this->db->query($query))
              return true;
            else
              return false;
        }

}