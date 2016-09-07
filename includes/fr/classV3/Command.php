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

/*$oldid = session_id();
session_regenerate_id();
unlink('/tmp/sess_' . $oldid);*/

class Command
{
	/* id */
	var $id          = 0;

	/* Handle connexion */
	var $handle = NULL;

	/* Constructeur, set la session à utiliser
	i : référence sur la connexion au SGBDR */
	function Command(& $handle)
	{
		$this->handle = & $handle;
	}

	function getID() { return $this->id; }  
	function setID($id) { $this->id = $id; return true; }
  
  
	function & getCommandAdvertisers()
	{
		$res = & $this->handle->query("select idAdvertiser from commandes_advertisers where idCommande = " . $this->id, __FILE__, __LINE__);
		if ($this->handle->numrows($res, __FILE__, __LINE__) > 0)
		{
			$listadv = array();
			while ($ret = & $this->handle->fetch($res))
			{
				$listadv[$ret[0]] = 1;
			}
			return $listadv;
		}
		else return false;
	}
  
	function advertiserIsInCommand($idAdvertiser)
	{
		$res = & $this->handle->query("select idAdvertiser from commandes_advertisers where idCommande = " . $this->id . " and idAdvertiser = '" . $this->handle->escape($idAdvertiser) . "'", __FILE__, __LINE__);
		if ($this->handle->numrows($res, __FILE__, __LINE__) == 1) return true;
		else return false;
	}
  
	function getClientID()
	{
		$res = & $this->handle->query("select idClient from commandes where id = " . $this->id, __FILE__, __LINE__);
		if ($this->handle->numrows($res, __FILE__, __LINE__) == 1)
		{
			$ret = & $this->handle->fetch($res);
			return $ret[0];
		}
		else return false;
	}
  
	function generateID()
	{
		do
		{
			$id = mt_rand(1, 999999999);
			$res = & $this->handle->query("select id from commandes where id = " . $id, __FILE__, __LINE__);
		}
		while ($this->handle->numrows($res, __FILE__, __LINE__) == 1);
		
		$this->id = $id;
	}

	function & getTVAs()
	{
	    $ret = array();
	  
	    $res = & $this->handle->query("select id, taux from tva order by taux desc" . $exp, __FILE__, __LINE__ );
		while($record = & $this->handle->fetch($res))
			$ret[$record[0]] = $record[1];
	    
		return $ret;
	}
	
	function addFromDevis($idDevis, $type_paiement, $statut_paiement, $statut_traitement) {
		$res = & $this->handle->query("
			select
				idClient, totalHT, totalTTC, titre, nom,
				prenom, societe, adresse, complement, ville,
				cp, pays, infos_sup, tel1, titre_l,
				nom_l, prenom_l, societe_l, adresse_l, complement_l,
				ville_l, cp_l, pays_l, infos_sup_l, coord_livraison
			from devis
			where id = '" . $this->handle->escape($idDevis) . "'" , __FILE__, __LINE__);
		
		if ($this->handle->numrows($res, __FILE__, __LINE__) == 1) {
			$devisInfos = $this->handle->fetchAssoc($res);
			
			$this->generateID();
			
			if ($devisInfos["coord_livraison"] == 0) {
				$devisInfos["titre_l"]      = $devisInfos["titre"];
				$devisInfos["nom_l"]        = $devisInfos["nom"];
				$devisInfos["prenom_l"]     = $devisInfos["prenom"];
				$devisInfos["societe_l"]    = $devisInfos["societe"];
				$devisInfos["adresse_l"]    = $devisInfos["adresse"];
				$devisInfos["complement_l"] = $devisInfos["complement"];
				$devisInfos["ville_l"]      = $devisInfos["ville"];
				$devisInfos["cp_l"]         = $devisInfos["cp"];
				$devisInfos["pays_l"]       = $devisInfos["pays"];
				$devisInfos["infos_sup_l"]  = $devisInfos["infos_sup"];
			}
			
			// Loading items of the estimate
			$res = & $this->handle->query("
				select idProduct, idTC, idFamily, quantity, comment
				from devis_produits
				where idDevis = '" . $this->handle->escape($idDevis) . "'", __FILE__, __LINE__);
			$itemList = array();
			while ($item = $this->handle->fetchAssoc($res)) {
				$itemList[] = $item;
			}
			
			// Calculate the cards
			$itemCount = count($itemList);
			include(SITE . "card.php");
			
			// Saving usefull references properties
			$listpdt = array();
			$listpdt[] = array("idProduct", "idTC", "name", "fastdesc", "label", "price", "price2", "unite", "idFamily", "quantity", "tauxTVA", "promotion", "discount", "idAdvertiser", "refSupplier", "comment");
			$listadv = array();
			
			for ($item_num = 0; $item_num < $itemCount; $item_num++) {
				$item = & $itemList[$item_num];
				$pdtInfos[0]  = $item["idProduct"];
				$pdtInfos[1]  = $item["id"];
				$pdtInfos[2]  = $item["name"];
				$pdtInfos[3]  = $item["fastdesc"];
				$pdtInfos[4]  = $item["label"];
				$pdtInfos[5]  = $item["price"];
				$pdtInfos[6]  = $item["price2"];
				$pdtInfos[7]  = $item["unite"];
				$pdtInfos[8]  = $item["idFamily"];
				$pdtInfos[9]  = $item["quantity"];
				$pdtInfos[10] = $tvaTable[$item["idTVA"]]["rate"];
				$pdtInfos[11] = $item["promotion"];
				$pdtInfos[12] = $item["discount"];
				$pdtInfos[13] = $item["idAdvertiser"];
				$pdtInfos[14] = $item["refSupplier"];
				$pdtInfos[15] = $item["comment"];
				
				$listpdt[] = $pdtInfos;
				$listadv[$item["idAdvertiser"]] = true;
			}
			
			// Setting involved advertisers
			foreach ($listadv as $adv => $val)
				$this->handle->query("insert into commandes_advertisers (idCommande, idAdvertiser, statut_traitement) values (" . $this->id . ", " . $adv . ", '" . $this->handle->escape($statut_traitement) . "')");
			
			$coord = serialize($devisInfos);
			$produits = serialize($listpdt);
			
			// Saving the command in a DB join independant way
			$this->handle->query("
				insert into commandes (id, idClient, timestamp, create_time, totalHT, totalTTC, fdp, fdp_tva, type_paiement, statut_paiement, statut_traitement, coord, produits)
				values (
					'" . $this->id . "',
					'" . $devisInfos["idClient"] . "',
					'" . time() . "',
					'" . time() . "',
					'" . $totalHT . "',
					'" . $totalTTC . "',
					'" . $fdpHT . "',
					'" . $tvaList[$fdp_idTVA] . "',
					'" . $type_paiement . "',
					'" . $this->handle->escape($statut_paiement) . "',
					0,
					'" . $this->handle->escape($coord) . "',
					'" . $this->handle->escape($produits) . "')", __FILE__, __LINE__);
		}
		
		return true;
	}
  
	function addFromBasket($idPanier, $type_paiement, $statut_paiement, $statut_traitement) {
		$res = & $this->handle->query("
			select
				idClient, totalHT, totalTTC, titre, nom,
				prenom, societe, adresse, complement, ville,
				cp, pays, infos_sup, tel1, titre_l,
				nom_l, prenom_l, societe_l, adresse_l, complement_l,
				ville_l, cp_l, pays_l, infos_sup_l, coord_livraison
			from paniers
			where id = '" . $this->handle->escape($idPanier) . "'" , __FILE__, __LINE__);
		
		if ($this->handle->numrows($res, __FILE__, __LINE__) == 1) {
			$panierInfos = $this->handle->fetchAssoc($res);
			
			$this->generateID();
			
			if ($panierInfos["coord_livraison"] == 0) {
				$panierInfos["titre_l"]      = $panierInfos["titre"];
				$panierInfos["nom_l"]        = $panierInfos["nom"];
				$panierInfos["prenom_l"]     = $panierInfos["prenom"];
				$panierInfos["societe_l"]    = $panierInfos["societe"];
				$panierInfos["adresse_l"]    = $panierInfos["adresse"];
				$panierInfos["complement_l"] = $panierInfos["complement"];
				$panierInfos["ville_l"]      = $panierInfos["ville"];
				$panierInfos["cp_l"]         = $panierInfos["cp"];
				$panierInfos["pays_l"]       = $panierInfos["pays"];
				$panierInfos["infos_sup_l"]  = $panierInfos["infos_sup"];
			}
			
			// Loading items of the card
			$res = & $this->handle->query("
			select idProduct, idTC, quantity, comment
			from paniers_produits
			where idPanier = '" . $this->handle->escape($idPanier) . "'", __FILE__, __LINE__);
			$itemList = array();
			while ($item = $this->handle->fetchAssoc($res)) {
				$itemList[] = $item;
			}
			
			// Calculate the cards
			$itemCount = count($itemList);
			include(SITE . "card.php");
			
			// Saving usefull references properties
			$listpdt = array();
			$listpdt[] = array("idProduct", "idTC", "name", "fastdesc", "label", "price", "price2", "unite", "idFamily", "quantity", "tauxTVA", "promotion", "discount", "idAdvertiser", "refSupplier", "comment");
			$listadv = array();
			
			for ($item_num = 0; $item_num < $itemCount; $item_num++) {
				$item = & $itemList[$item_num];
				$pdtInfos[0]  = $item["idProduct"];
				$pdtInfos[1]  = $item["id"];
				$pdtInfos[2]  = $item["name"];
				$pdtInfos[3]  = $item["fastdesc"];
				$pdtInfos[4]  = $item["label"];
				$pdtInfos[5]  = $item["price"];
				$pdtInfos[6]  = $item["price2"];
				$pdtInfos[7]  = $item["unite"];
				$pdtInfos[8]  = $item["idFamily"];
				$pdtInfos[9]  = $item["quantity"];
				$pdtInfos[10] = $tvaTable[$item["idTVA"]]["rate"];
				$pdtInfos[11] = $item["promotion"];
				$pdtInfos[12] = $item["discount"];
				$pdtInfos[13] = $item["idAdvertiser"];
				$pdtInfos[14] = $item["refSupplier"];
				$pdtInfos[15] = $item["comment"];
				
				$listpdt[] = $pdtInfos;
				$listadv[$item["idAdvertiser"]] = true;
			}
			
			// Setting involved advertisers
			foreach ($listadv as $adv => $val)
				$this->handle->query("insert into commandes_advertisers (idCommande, idAdvertiser, statut_traitement) values (" . $this->id . ", " . $adv . ", '" . $this->handle->escape($statut_traitement) . "')");
			
			$coord = serialize($panierInfos);
			$produits = serialize($listpdt);
			
			// Saving the command in a DB join independant way
			$this->handle->query("
				insert into commandes (id, idClient, timestamp, create_time, totalHT, totalTTC, fdp, fdp_tva, type_paiement, statut_paiement, statut_traitement, coord, produits)
				values (
					'" . $this->id . "',
					'" . $panierInfos["idClient"] . "',
					'" . time() . "',
					'" . time() . "',
					'" . $totalHT . "',
					'" . $totalTTC . "',
					'" . $fdpHT . "',
					'" . $tvaList[$fdp_idTVA] . "',
					'" . $type_paiement . "',
					'" . $this->handle->escape($statut_paiement) . "',
					0,
					'" . $this->handle->escape($coord) . "',
					'" . $this->handle->escape($produits) . "')", __FILE__, __LINE__);
		}
		
		return true;
	}

	function & loadProducts() {
		$res = & $this->handle->query("select produits from commandes where id = '" . $this->id . "'", __FILE__, __LINE__);
		$record = & $this->handle->fetch($res);
		$data = unserialize($record[0]);
		
		$listpdt = array();
		for ($i = 1; $i < count($data); $i++) {
			$listpdt[$i-1] = array();
			for($j = 0; $j < count($data[0]); $j++) {
				$listpdt[$i-1][$data[0][$j]] = $data[$i][$j];
			}
		}
		return $listpdt;
	}

	/******************** fonctions commande ********************/
  

	function & loadCoord($kind = "both")
	{
		$res = & $this->handle->query("select coord from commandes where id = '" . $this->id . "'" , __FILE__, __LINE__);
		$record = & $this->handle->fetch($res);
		$coords = unserialize($record[0]);
		return $coords;
	}
  
	function & loadInfos()
	{
		$res = & $this->handle->query("select timestamp, create_time, totalHT, totalTTC, fdp, fdp_tva, type_paiement, statut_paiement, statut_traitement, dispatch_time from commandes where id = '" . $this->id . "'" , __FILE__, __LINE__);
		$record = & $this->handle->fetchArray($res, "assoc");
		return $record;
	}
  
	function & loadAll()
	{
		$res = & $this->handle->query("select timestamp, create_time, totalHT, totalTTC, fdp, fdp_tva, type_paiement, statut_paiement, statut_traitement, dispatch_time, coord, produits from commandes where id = '" . $this->id . "'" , __FILE__, __LINE__);
		$record = & $this->handle->fetchArray($res, "assoc");
		
		$tabAll = array();
		$tabAll["coord"] = unserialize($record["coord"]);
		
		$data = unserialize($record["produits"]);
		$tabAll["produits"] = array();
		for ($i = 1; $i < count($data); $i++)
		{
			$tabAll["produits"][$i-1] = array();
			for($j = 0; $j < count($data[0]); $j++)
			{
				$tabAll["produits"][$i-1][$data[0][$j]] = $data[$i][$j];
			}
		}
		$tabAll["infos"] = array();
		$tabAll["infos"]["timestamp"]			= & $record["timestamp"];
		$tabAll["infos"]["create_time"]			= & $record["create_time"];
		$tabAll["infos"]["totalHT"]				= & $record["totalHT"];
		$tabAll["infos"]["totalTTC"]			= & $record["totalTTC"];
		$tabAll["infos"]["fdp"]					= & $record["fdp"];
		$tabAll["infos"]["fdp_tva"]				= & $record["fdp_tva"];
		$tabAll["infos"]["type_paiement"]		= & $record["type_paiement"];
		$tabAll["infos"]["statut_paiement"]		= & $record["statut_paiement"];
		$tabAll["infos"]["statut_traitement"]	= & $record["statut_traitement"];
		$tabAll["infos"]["dispatch_time"]		= & $record["dispatch_time"];
		
		return $tabAll;
	}
  
	function set_statut_paiement($val)
	{
		settype($val, "integer");
		$res = & $this->handle->query("update commandes set statut_paiement = " . $val . " where id = " . $this->id, __FILE__, __LINE__);
		if ($val >= 10)
		{
			$res = & $this->handle->query("update commandes_advertisers set statut_traitement = 10 where idCommande = " . $this->id . " and statut_traitement < 10" , __FILE__, __LINE__);
		}
		else
		{
			$res = & $this->handle->query("update commandes_advertisers set statut_traitement = 0 where idCommande = " . $this->id . " and statut_traitement >= 10 and statut_traitement < 20" , __FILE__, __LINE__);
		}
		
		$this->set_statut_traitement_global();
		
		return true;
	}
	
	function set_statut_traitement($adv, $val)
	{
		settype($val, "integer");
		if ($val >= 30) $dispatch_time = time(); else $dispatch_time = 0;
		$res = & $this->handle->query("update commandes_advertisers set statut_traitement = " . $val . ", dispatch_time = " . $dispatch_time . " where idCommande = " . $this->id . " and idAdvertiser = '" . $this->handle->escape($adv) . "'" , __FILE__, __LINE__);
		
		$this->set_statut_traitement_global();
		
		return true;
	}
  
	function get_statut_traitement($adv)
	{
		$res = & $this->handle->query("select statut_traitement from commandes_advertisers where idCommande = " . $this->id . " and idAdvertiser = '" . $this->handle->escape($adv) . "'" , __FILE__, __LINE__);
		$ret = & $this->handle->fetch($res);
		return $ret[0];
	}
	
	function set_statut_traitement_global()
	{
		$statutList = array(0 => 0, 10 => 0, 20 => 0, 30 => 0, 40 => 0);
		
		$res = & $this->handle->query("select statut_traitement from commandes_advertisers where idCommande = " . $this->id, __FILE__, __LINE__);
		while ($record = & $this->handle->fetch($res))
			$statutList[$record[0] - $record[0]%10]++;
		
		if ($statutList[0] != 0) $statut = 0;
		elseif ($statutList[10] == $statutList[2] && $statutList[1] == 0) $statut = 40;
		elseif ($statutList[30] != 0) $statut = 30;
		elseif ($statutList[20] != 0) $statut = 20;
		else $statut = 10;
		
		if ($statut == 40) $dispatch_time = time(); else $dispatch_time = 0;
		
		$res = & $this->handle->query("update commandes set statut_traitement = " . $statut . ", dispatch_time = " . $dispatch_time . " where id = " . $this->id, __FILE__, __LINE__);
		
		return $res;
	}
	
}

?>
