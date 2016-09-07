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

/*$oldid = session_id();
session_regenerate_id();
unlink('/tmp/sess_' . $oldid);*/

define("__COMMAND_DISPATCHED__", 40);

class Command
{
	/* Handle connexion */
	var $handle = NULL;
	
	/* Statut de la commande
	0 = n'existe pas dans la DB et n'est pas en cours de création
	1 = n'existe pas dans la DB mais est en cours de création
	5 = existe mais est dupliquée (ne peut normalement pas arriver)
	10 = existe mais non initialisé
	11 = existe et initialisé
	*/
	var $statut = 0;
	var $lastErrorMessage = '';
	
	/* Champs de la commande*/
	var $id = 0;
	var $idClient = 0;
	var $timestamp = 0;
	var $create_time = 0;
	var $totalHT = 0;
	var $totalTTC = 0;
	var $fdp = 0;
	var $fdp_tva = 0;
	var $type_paiement = 0;
	var $transaction_id = 0;
	var $statut_paiement = 0;
	var $statut_traitement = 0;
	var $dispatch_time = 0;
	
	/* Indique si l'objet doit mettre à jour les statistiques */
	var $stats = true;
	
	/* Tableau contenant les différents status de traitement des fournisseurs de la commande */
	var $s_status = NULL;
	
	/* Tableau contenant les coordonnées du client */
	var $coord = NULL;
	
	/* Tableau contenant la liste des produits de la commande */
	var $produits = NULL;

	/* Tableau contenant la liste des taux de tva */
	var $tauxTVA = NULL;
	
	/* Constructeur, set la session à utiliser
	i : référence sur la connexion au SGBDR */
	function Command(& $handle, $id = NULL, $init = '', $stats = true)
	{
		$this->handle = & $handle;
		$this->stats = $stats;
		if ($id != NULL)
		{
			$this->id = $id;
			$this->Load();
			if ($this->statut == 0 && $init == 'create') $this->Create(false);
		}
		elseif ($init == 'create') $this->Create(true);
	}
	
	function generateID()
	{
		do
		{
			$id = mt_rand(1, 999999999);
			$result = & $this->handle->query("select id from commandes where id = '$id'", __FILE__, __LINE__);
		}
		while ($this->handle->numrows($result, __FILE__, __LINE__) == 1);
		
		$this->id = $id;
	}
	
	function Create($generate_id = true)
	{
		if ($generate_id) $this->generateID();
		
		$this->coord = array('idClient' => '', 'totalHT' => 0, 'totalTTC' => 0, 'titre' => '', 'nom' => '', 'prenom' => '', 'societe' => '', 'adresse' => '', 'complement' => '',
		'ville' => '', 'cp' => '', 'pays' => '', 'titre_l' => '', 'nom_l' => '', 'prenom_l' => '', 'societe_l' => '', 'adresse_l' => '', 'complement_l' => '', 'ville_l' => '',
		'cp_l' => '', 'pays_l' => '', 'coord_livraison' => 0);
		
		$this->produits = array();
		$this->s_status = array();
		
		$this->statut = 1;
	}
	
	function Load()
	{
		$this->lastErrorMessage = '';
		
		$query = "select idClient, timestamp, create_time, totalHT, totalTTC, fdp, fdp_tva, type_paiement, transaction_id, statut_paiement, statut_traitement, dispatch_time, coord, produits from commandes where id = '$this->id'";
		$result = & $this->handle->query($query, __FILE__, __LINE__);
		if ($this->handle->numrows($result, __FILE__, __LINE__) > 0)
		{
			$record = & $this->handle->fetchAssoc($result);
			
			foreach($record as $name => $value)
			{
				$this->$name = $value;
			}
			
			$this->coord = unserialize($this->coord);
			
			$data = unserialize($this->produits);
			foreach($data[0] as $pos => $key)
			{
				if ($key == 'idTC')
				{
					$idTCpos = $pos;
					break;
				}
			}
			if (!isset($idTCpos))
			{
				$this->statut = 0;
				$this->lastErrorMessage = "Tableau d'entré des produits invalide";
			}
			else
			{
				$this->produits = array();
				$size = count($data);
				for ($i = 1; $i < $size; $i++)
				{
					$j = 0;
					// $data[0] = intitulé des colonnes
					foreach($data[0] as $k)
					{
						// on stock chaque produit avec son idTC comme index, et le nom de la colonne pour accéder aux données
						$this->produits[$data[$i][$idTCpos]][$k] = & $data[$i][$j++];
					}
				}
				
				$result = & $this->handle->query("select idAdvertiser, statut_traitement from commandes_advertisers where idCommande = $this->id", __FILE__, __LINE__);
				
				if ($this->handle->numrows($result, __FILE__, __LINE__) > 0)
				{
					$this->s_status = array();
					while ($record = & $this->handle->fetch($result))
					{
						$this->s_status[$record[0]] = $record[1];
					}
				}
				
				//print nl2br(print_r($this->s_status, true));
				
				$this->statut = 11;
			}
		}
		else
		{
			$this->statut = 0;
			$this->lastErrorMessage = "La commande n'existe pas, veuillez la créer avant de pouvoir la charger";
		}
	}
	
	function save()
	{
		$query = '';
		
		$listpdt = array();
		$listpdt[] = array('idProduct', 'idTC', 'name', 'fastdesc', 'label', 'price', 'price2', 'unite', 'quantity', 'tauxTVA', 'tauxRemise', 'idAdvertiser', 'refSupplier', 'comment');
		foreach($this->produits as $pdt) $listpdt[] = array_values($pdt);
		
		if ($this->statut_traitement == __COMMAND_DISPATCHED__) $this->dispatch_time = time();
		
		if ($this->statut == 1)
		{
			$query = "insert into commandes (id, idClient, timestamp, create_time, totalHT, totalTTC, fdp, fdp_tva, type_paiement, statut_paiement, statut_traitement, dispatch_time, coord, produits) " .
			"values (" . $this->id . ", " . $this->idClient . ", '" . time() . "', '" . time() . "', '" . $this->totalHT . "', '" . $this->totalTTC . "', '" . $this->fdp . "', '" . $this->fdp_tva .
			"', " . $this->type_paiement . ", " . $this->statut_paiement . ", " . $this->dispatch_time . ", 0, '" . $this->handle->escape(serialize($this->coord)) . "', '" . $this->handle->escape(serialize($listpdt)) . "')";
		}
		elseif($this->statut >= 10)
		{
			$query .= "update commandes set" . 
			" idClient = " . $this->idClient .
			", timestamp = " . time() .
			", create_time = " . $this->create_time .
			", totalHT = " . $this->totalHT .
			", totalTTC = " . $this->totalTTC .
			", fdp = " . $this->fdp .
			", fdp_tva = " . $this->fdp_tva .
			", type_paiement = " . $this->type_paiement .
			", transaction_id = " . $this->transaction_id .
			", statut_paiement = " . $this->statut_paiement .
			", statut_traitement = " . $this->statut_traitement .
			", dispatch_time = " . $this->dispatch_time .
			", coord = '" . $this->handle->escape(serialize($this->coord)) .
			"', produits = '" . $this->handle->escape(serialize($listpdt)) .
			"' where id = " . $this->id;
			
			$this->handle->query("delete from commandes_advertisers where idCommande = " . $this->id, __FILE__, __LINE__);
		}
		
		foreach($this->s_status as $adv => $s_status)
		{
			if ($this->statut_paiement >= 10 && $s_status < 10) $s_status = 10;
			$this->handle->query("insert into commandes_advertisers (idCommande, idAdvertiser, statut_traitement) values (" . $this->id . ", " . $adv . ", " . $s_status . ")", __FILE__, __LINE__);
		}
		
		$this->handle->query($query, __FILE__, __LINE__);
		$this->updateStats();
		$this->statut = 11;
	}
	
	function updateStats()
	{
		if ($this->stats)
		{
			$this->handle->query("delete from stats_cmd where idCommand = " . $this->id, __FILE__, __LINE__);
			$timestamp = time();
			foreach($this->produits as $pdt)
			{
				// TODO a prendre en compte les futurs remises/promo
				// TODO prendre en compte le numéro de famille
				$this->handle->query("insert into stats_cmd (idProduct, idTC, idAdvertiser, idFamily, quantity, idCommand, price, price2, timestamp)" .
				" values (" . $pdt['idProduct'] . ", " . $pdt['idTC'] . ", " . $pdt['idAdvertiser'] . ", " . 0 . ", " . $pdt['quantity'] . ", " . $this->id . ", " . $pdt['price'] . ", " . $pdt['price2'] . ", " . $timestamp . ")", __FILE__, __LINE__);
			}
			
		}
	}
	
	function getTauxTVA($idTVA)
	{
	    if ($this->tauxTVA === NULL)
		{
			$this->tauxTVA = array();
		    $result = & $this->handle->query("select id, taux from tva", __FILE__, __LINE__ );
			while($record = & $this->handle->fetch($result))
				$this->tauxTVA[$record[0]] = $record[1];
		}
		
		return $this->tauxTVA[$idTVA];
	}
	
	function updateTotals()
	{
		$totalHT = 0;
		$totalTTC = 0;
		$total_par_Taux = array();
		$pdtTauxRemise = $pdtQteList = $pdtSumList = array();
		
		foreach ($this->produits as $pdt)
		{
			$sumHT = $pdt['price'] * $pdt['quantity'];
			$totalHT += $sumHT;
			
			if (isset($pdtQteList[$pdt['idProduct']]))
			{
				$pdtQteList[$pdt['idProduct']]['qty'] += $pdt['quantity'];
			}
			else
			{
				$pdtQteList[$pdt['idProduct']]['qty'] = $pdt['quantity'];
				$pdtSumList[$pdt['idProduct']] = array();
			}
			$pdtSumList[$pdt['idProduct']][$pdt['idTC']]['sum'] = $sumHT;
			$pdtSumList[$pdt['idProduct']][$pdt['idTC']]['tva'] = $pdt['tauxTVA'];
			
			if ($pdt['tauxRemise'] != '' && !isset($pdtTauxRemise[$pdt['idProduct']]))
				$pdtTauxRemise[$pdt['idProduct']] = unserialize($pdt['tauxRemise']);
			
			$total_par_Taux[$pdt['tauxTVA']] += $sumHT;
			
		}
		
		foreach ($pdtTauxRemise as $idProduct => $remises)
		{
			if ($pdtQteList[$idProduct]['qty'] >= $remises[0])
			{
				if (isset($remises[2]) && $pdtQteList[$idProduct]['qty'] >= $remises[2])
					$remise = $remises[3];
				else
					$remise = $remises[1];
				
				$sommeRemise = 0;
				foreach ($pdtSumList[$idProduct] as $_idtc => $_ref)
				{
					$refRemise = $_ref['sum'] * $remise / 100;
					$total_par_Taux[$_ref['tva']] -= $refRemise;
					$sommeRemise += $refRemise;
					
				}
				$totalHT -= $sommeRemise;
			}
		}
		
		krsort($total_par_Taux);
		$totalTVA_par_Taux = array();
		$totalTVA = 0;
		foreach ($total_par_Taux as $_taux => $_total)
		{
			$totalTVA_par_Taux[$_taux] = $_total * $_taux / 100;
			$totalTVA += $totalTVA_par_Taux[$_taux];
		}
		
		$totalTVA = ceil($totalTVA*100)/100;
		$this->totalHT  = ceil($totalHT *100)/100;
		$this->totalTTC = $this->totalHT + $totalTVA;;
	}
	
	function UpdateProductsQuantities($listqty)
	{
		$nbpdtupdated = 0;
		foreach($listqty as $idTC => $qty)
		{
			if (isset($this->produits[$idTC]))
			{
				$this->produits[$idTC]['quantity'] = $qty;
				$this->s_status[$this->produits[$idTC]['idAdvertiser']] = 0;
				$nbpdtupdated++;
			}
		}
		if ($nbpdtupdated > 0)
		{
			$this->updateTotals();
			$this->statut_traitement = 0;
		}
	}
	
	function AddProduct($id, $idTC, $quantity)
	{
		//print "ProductsError" . __ERRORID_SEPARATOR__ . $id . " " . $idTC . " " . $quantity . __ERROR_SEPARATOR__ . __MAIN_SEPARATOR__;
		$product_exist = false;
		$keys = array_keys($this->produits); $size = count($keys);
		for ($i=0; $i < $size; $i++)
		{
			if ($this->produits[$keys[$i]]['idProduct'] == $id)
			{
				if (empty($idTC))
				{
					$product_exist = true;
					break;
				}
				elseif ($this->produits[$keys[$i]]['idTC'] == $idTC)
				{
					$product_exist = true;
					break;
				}
			}
		}
			
		if ($product_exist)
		{
			$this->produits[$keys[$i]]['quantity'] += $quantity;
			$this->s_status[$this->produits[$keys[$i]]['idAdvertiser']] = 0;
		}
		else
		{
			if (empty($idTC))
				$result = & $this->handle->query("select p.id as idProduct, pg.idTC, p.name, p.fastdesc, '' as label, pg.price, pg.price2, pg.unite, " . $quantity . " as quantity, pg.idTVA as tauxTVA, pg.tauxRemise, pg.idAdvertiser, pg.refSupplier from products_fr p, products pg where p.id = " . $id . " and p.id = pg.id", __FILE__, __LINE__);
			else
				$result = & $this->handle->query("select p.id as idProduct, rc.id as idTC, p.name, p.fastdesc, rc.label, rc.price, rc.price2, rc.unite, " . $quantity . " as quantity, rc.idTVA as tauxTVA, pg.tauxRemise, pg.idAdvertiser, rc.refSupplier from products_fr p, products pg, references_content rc where p.id = " . $id . " and rc.id = " . $idTC . " and p.id = rc.idProduct and p.id = pg.id", __FILE__, __LINE__);
			
			$pdt = & $this->handle->fetchAssoc($result);
			$pdt['tauxTVA'] = $this->getTauxTVA($pdt['tauxTVA']);
			$this->produits[$pdt['idTC']] = & $pdt;
			$this->s_status[$pdt['idAdvertiser']] = 0;
		}
		
		$this->updateTotals();
		$this->statut_traitement = 0;
	}
	
	function DelProduct($id, $idTC)
	{
		$pdt_adv2del = 0;
		if (isset($this->produits[$idTC]) && $this->produits[$idTC]['idProduct'] == $id)
		{
			$pdt_adv2del = $this->produits[$idTC]['idAdvertiser'];
			unset($this->produits[$idTC]);
			$this->updateTotals();
			$keys = array_keys($this->produits); $nbpdt = count($keys);
			for ($i=0; $i < $nbpdt; $i++)
			{
				if ($this->produits[$keys[$i]]['idAdvertiser'] == $pdt_adv2del)
				{
					$pdt_adv2del = false;
					break;
				}
			}
			if ($pdt_adv2del) unset($this->s_status[$pdt_adv2del]);
			$this->set_statut_traitement();
		}
	}
	
	function ClearProducts()
	{
		unset($this->produits);
		$this->produits = array();
		$this->updateTotals();
		unset($this->s_statut);
		$this->s_statut = array();
		$this->statut_traitement = 0;
	}
	
	function set_statut_traitement()
	{
		$statutList = array(0 => 0, 10 => 0, 20 => 0, 30 => 0, 40 => 0);
		foreach ($this->s_status as $s_status) $statutList[$s_status - $s_status%10]++;
		
		if ($statutList[0] != 0) $this->statut_traitement = 0;
		elseif ($statutList[10] == $statutList[20] && $statutList[10] == 0) $this->statut_traitement = 40;
		elseif ($statutList[30] != 0) $this->statut_traitement = 30;
		elseif ($statutList[20] != 0) $this->statut_traitement = 20;
		else $this->statut_traitement = 10;
	}
	
}

?>
