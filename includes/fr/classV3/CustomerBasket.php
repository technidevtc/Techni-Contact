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

class CustomerBasket
{
	/* id */
	var $id       = '0';
	var $exist    = false;
	var $count    = 0;
	var $hasCoord = false;

	/* Handle connexion */
	var $handle = NULL;

	/* Constructeur, set la session à utiliser
	i : référence sur la connexion au SGBDR */
	function CustomerBasket(& $handle)
	{
		$this->handle = & $handle;
	}

	/******************** fonctions panier ********************/

	function getID() { return $this->id; }  
	function getExist() { return $this->exist; }
	function getCount() { return $this->count; }
	function getHasCoord() { return $this->hasCoord; }

	function setID($id, $check = true)
	{
		if ($check)
		{
			if (preg_match('/^[0-9a-zA-Z]{32}$/', $id))
			{
				$this->id = $id;
				if ($this->checkExistence())
				{
					$this->doCount();
					$this->hasCoord = true;
				}
				else
				{
					$this->create();
					$this->hasCoord = false;
				}
			}
			else return false;
		}
		else
		{  
			$this->id    = $id;
			$this->exist = false;
			$this->count = 0;
			$this->hasCoord = false;
		}
		
		return true;
	}

	function checkExistence()
	{
		$ret = false;
		
		if ($this->id != '0')
		{
			$result = & $this->handle->query("select id from paniers where id = '" . $this->id . "'", __FILE__, __LINE__);
			
			if ($this->handle->numrows($result, __FILE__, __LINE__) == 1)
			{
				$this->exist = true;
				$ret = true;
			}
			else
			{
				$this->exist = false;
			}
		}
		
		return $ret;
	}

	function doTimestamp()
	{
		return ($this->exist && $this->handle->query("update paniers set timestamp = " . time() . " where id = '" . $this->id . "'", __FILE__, __LINE__) && $this->handle->affected( __FILE__, __LINE__) <= 1);
	}

	function create()
	{
		$ret = false;
		
		if ($this->id != '0')
		{
			$this->handle->query("insert into paniers (id, timestamp) values ('" . $this->id . "', " . time() . ")", __FILE__, __LINE__);
			$this->count = 0;
			$this->exist = true;
			$ret = true;
		}
		
		return $ret;
	}

	function delete()
	{
		return ($this->exist &&
		$this->handle->query("delete from paniers_produits where idPanier = '" . $this->id . "'", __FILE__, __LINE__) &&
		$this->handle->query("delete from paniers where id = '" . $this->id . "'", __FILE__, __LINE__));
	}

	/******************** fonctions produits panier ********************/

	function doCount()
	{
		if ($this->exist)
		{
			$result = & $this->handle->query("select count(*) from paniers_produits where idPanier = '" . $this->id . "'", __FILE__, __LINE__);
			$record = & $this->handle->fetch($result);
			$this->count = $record[0];
		}
		else
		{
			$this->count = 0;
		}
		
		return true;
	}
/*
	function productExist(& $id)
	{
		if (($result = $this->handle->query('select p.id from products_fr p, products pg, advertisers a where p.id = \'' . $this->handle->escape($id) . '\' and p.id = pg.id and pg.idAdvertiser = a.id and a.actif = 1', __FILE__, __LINE__)) && $this->handle->numrows($result, __FILE__, __LINE__) == 1)
		return true;
		else return false;
	}
*/
	function updateValues()
	{
		$ret = false;
		
		if($this->exist)
		{
			$totalHT = 0;
			$totalTTC = 0;
			
			$res = $this->handle->query("select pp.idProduct, pg.price, pp.quantity, pg.tauxRemise, tva.taux from products pg, paniers_produits pp, tva where pp.idProduct = pg.id and pp.idTC = 0 and pg.idTVA = tva.id and pp.idPanier = '" . $this->id . "'");
			while ($pdt = $this->handle->fetch($res, 'assoc'))
			{
				$sum = $pdt['price'] * $pdt['quantity'];
				if ($pdt['tauxRemise'] != '')
				{
					$tauxRemise = unserialize($pdt['tauxRemise']);
					if ($pdt['quantity'] > $tauxRemise[0])
					{
						if ($pdt['quantity'] > $tauxRemise[2])
							$sum *= (100 - $tauxRemise[3]) / 100;
						else
							$sum *= (100 - $tauxRemise[1]) / 100;
					}
				}
				$totalHT  += $sum;
				$totalTTC += $sum * (100 + $pdt['taux']) / 100;
			}
			
			$res = $this->handle->query("select pp.idproduct, sum(rc.price*pp.quantity) as sumpriceHT, sum(rc.price*pp.quantity*(100+tva.taux)/100) as sumpriceTTC, sum(pp.quantity) as sumqty, pg.tauxRemise from references_content rc, paniers_produits pp, products pg, tva where pp.idtc = rc.id and pp.idproduct = pg.id and pp.idtc !=0 and rc.idTVA = tva.id and pp.idpanier = '" . $this->id . "' group by pp.idproduct");
			while ($pdt = $this->handle->fetch($res, 'assoc'))
			{
				$sumHT  = $pdt['sumpriceHT'];
				$sumTTC = $pdt['sumpriceTTC'];
				if ($pdt['tauxRemise'] != '')
				{
					$tauxRemise = unserialize($pdt['tauxRemise']);
					if ($pdt['sumqty'] > $tauxRemise[0])
					{
						if ($pdt['sumqty'] > $tauxRemise[2])
						{
							$sumHT  *= (100 - $tauxRemise[3]) / 100;
							$sumTTC *= (100 - $tauxRemise[3]) / 100;
						}
						else
						{
							$sumHT  *= (100 - $tauxRemise[1]) / 100;
							$sumTTC *= (100 - $tauxRemise[1]) / 100;
						}
					}
				}
				$totalHT  += $sumHT;
				$totalTTC += $sumTTC;
			}
			
			$totalHT  = ceil($totalHT * 100) / 100;
			$totalTTC = ceil($totalTTC * 100) / 100;
			
			$this->handle->query("update paniers set totalHT = " . $totalHT . ", totalTTC = " . $totalTTC . ", timestamp = " . time() . ", locked = 0 where id = '" . $this->id . "'", __FILE__, __LINE__);
			$this->doCount();
			$ret = true;
		}
		
		return $ret;
	}

	function addProduct($id, $idTC, $idFamily, $quantity)
	{
		$ret = false;

		if ($this->exist)
		{
			$id = (int)$id;
			$idTC = (int)$idTC;
			$idFamily = (int)$idFamily;
			$quantity = (int)$quantity;
			
			$result = & $this->handle->query("select quantity from paniers_produits where idPanier = '" . $this->id . "' and idProduct = " . $id . " and idTC = " . $idTC, __FILE__, __LINE__);
			// si le produit est déjà présent dans le panier
			if ($this->handle->numrows($result, __FILE__, __LINE__) == 1)
			{
				$qty = & $this->handle->fetch($result);
				if (($this->handle->query("update paniers_produits set quantity = " . ($qty[0] + $quantity) . " where idPanier = '" . $this->id . "' and idProduct = " . $id . " and idTC = " . $idTC, __FILE__, __LINE__)) && $this->handle->affected( __FILE__, __LINE__) <= 1)
				{
					$ret = true;
				}
			}
			else
			{
				$this->handle->query("insert into paniers_produits (idPanier, idProduct, idTC, idFamily, quantity) values ('" . $this->id . "', " . $id . ", " . $idTC . ", " . $idFamily . ", " . $quantity . ")", __FILE__, __LINE__);
				$ret = true;
			}
			
			if ($ret && !$this->updateValues()) $ret = false;
		}
		
		return $ret;
	}

	function delProduct($id, $idTC)
	{
		$ret = false;
		
		if ($this->exist)
		{
			$id = (int)$id;
			$idTC = (int)$idTC;
			$this->handle->query("delete from paniers_produits where idPanier = '" . $this->id . "' and idProduct = " . $id . " and idTC = " . $idTC, __FILE__, __LINE__);
			$ret = true;
		}
		
		if ($ret && !$this->updateValues()) $ret = false;
		
		return $ret;
		
	}

	function addFromDevis($idDevis)
	{
		$ret = false;
		
		if ($this->exist)
		{
			$idDevis = (int)$idDevis;
			$result = & $this->handle->query("select totalHT, totalTTC from devis where id = " . $idDevis, __FILE__, __LINE__);
			
			if ($this->handle->numrows($result, __FILE__, __LINE__) == 1)
			{
				$ei = & $this->handle->fetchAssoc($result);
				$this->handle->query("update paniers set totalHT = " . $ei['totalHT'] . ", totalTTC = " . $ei['totalTTC'] . " where id = '" . $this->id . "'", __FILE__, __LINE__);
				
				$PdtCart = array();
				$result = & $this->handle->query("select idProduct, idTC, quantity from paniers_produits where idPanier = '" . $this->id . "'", __FILE__, __LINE__);
				while ($pdtC = & $this->handle->fetch($result)) $PdtCart[$pdtC[0].'-'.$pdtC[1]] = $pdtC[2];
				
				$result = & $this->handle->query("select idProduct, idTC, idFamily, quantity from devis_produits where idDevis = " . $idDevis, __FILE__, __LINE__);
				while ($pdtE = & $this->handle->fetch($result))
				{
					if (isset($PdtCart[$pdtE[0].'-'.$pdtE[1]]))
						$this->handle->query("update paniers_produits set quantity = " . ($PdtCart[$pdtE[0].'-'.$pdtE[1]] + $pdtE[3]) . " where idPanier = '" . $this->id . "' and idProduct = " . $pdtE[0] . " and idTC =  " . $pdtE[1], __FILE__, __LINE__);
					else
						$this->handle->query("insert into paniers_produits (idPanier, idProduct, idTC, idFamily, quantity) values ('" . $this->id . "', " . $pdtE[0] . ", " . $pdtE[1] . ", " . $pdtE[2] . ", " . $pdtE[3] . ")", __FILE__, __LINE__);
				}
				
				$ret = true;
			}
		}
		
		return $ret;
	}

	function updateQuantities(& $listpdt)
	{
		//0 = id ; 1 = idTC ; 2 = quantity
		$ret = true;
		
		if ($this->exist)
		{
			foreach($listpdt as $pdt)
			{
				$pdt[0] = (int)$pdt[0]; $pdt[1] = (int)$pdt[1]; $pdt[2] = (int)$pdt[2];
				if ($pdt[2] == 0)
				{
					$this->handle->query("delete from paniers_produits where idPanier = '" . $this->id . "' and idProduct = " . $pdt[0] . " and idTC = " . $pdt[1], __FILE__, __LINE__);
				}
				else
				{
					if (!$this->handle->query("update paniers_produits set quantity = " . $pdt[2] . " where idPanier = '" . $this->id . "' and idProduct = " . $pdt[0] . " and idTC = " . $pdt[1], __FILE__, __LINE__) || $this->handle->affected( __FILE__, __LINE__) > 1)
					{
						$ret = false;
						break;
					}
				}
			}
		}
		else $ret = false;
		
		if ($ret && !$this->updateValues()) $ret = false;
		
		return $ret;
	}
	
	function UpdateComment($idTC, $comment)
	{
		if ($this->exist)
		{
			if ($this->handle->query("update paniers_produits set comment = '" . $this->handle->escape($comment) . "' where idPanier = '" . $this->id . "' and idTC = " . $idTC, __FILE__, __LINE__))
				return true;
			else return false;
		}
		else return false;
	}


	function & loadProducts($expr = '')
	{
		
		if ($this->exist && ($result = & $this->handle->query("select p.id, pp.idTC, pp.idFamily, pp.quantity, pp.comment from paniers_produits pp, products p where pp.idPanier = '" . $this->id . "' and p.id = pp.idProduct" . $expr, __FILE__, __LINE__)))
		{
			$listpdt = array();
			while($pdt = & $this->handle->fetchAssoc($result)) $listpdt[] = & $pdt;
			
			return $listpdt;
		}
		else return false;
		
	}

	function clearProducts()
	{
		if ($this->exist)
		{
			$this->handle->query("delete from paniers_produits where idPanier = '" . $this->id . "'");
			$this->handle->query("update paniers set locked = 0 where id = '" . $this->id . "'", __FILE__, __LINE__);
		}
		return true;
	}

	/******************** fonctions commande ********************/

	function getOldClientID()
	{
		if ($this->exist && ($result = & $this->handle->query("select idClient from paniers where id = '" . $this->id . "'", __FILE__, __LINE__)) && $this->handle->numrows($result, __FILE__, __LINE__) == 1)
		{
			$ret = & $this->handle->fetch($result);
			return $ret[0];
		}
		else return false;
	}
		
	function affectClient($idClient)
	{
		$ret = false;
		
		if($this->exist)
		{
			$idClient = (int)$idClient;
			$result = & $this->handle->query("select titre, nom, prenom, societe, adresse, complement, ville, cp, pays, tel1, titre_l, nom_l, prenom_l, societe_l, adresse_l, complement_l, ville_l, cp_l, pays_l, coord_livraison from clients where id = " . $idClient . " and actif = 1", __FILE__, __LINE__);
			
			$clientInfos = & $this->handle->fetchAssoc($result);
			
			$query = "update paniers set idClient = " . $idClient;
			
			foreach($clientInfos as $infos => $value)
				$query .= ", " . $infos . " = '" . $this->handle->escape($value) . "'";
			
			$query .= " where id = '" . $this->id . "'";
			
			$this->handle->query($query, __FILE__, __LINE__);
			
			$this->hasCoord = true;
			
			$ret = true;
		}
		
		return $ret;
	}

	function lock()
	{
		return ($this->exist && $this->handle->query("update paniers set locked = 1 where id = '" . $this->id . "'", __FILE__, __LINE__));
	}

	function unlock()
	{
		return ($this->exist && $this->handle->query("update paniers set locked = 0 where id = '" . $this->id . "'", __FILE__, __LINE__));
	}

	function isLocked()
	{
		$ret = false;
		
		if ($this->exist && $result = & $this->handle->query("select locked from paniers where id = '" . $this->id . "'", __FILE__, __LINE__) && $this->handle->numrows($result, __FILE__, __LINE__) == 1)
		{
			$record = & $this->handle->fetch($result);
			if ($record[0] != 0) $ret = true;
		}
		
		return $ret;
	}

	function & getPNCCcommand()
	{
		$result = & $this->handle->query("select pp.idProduct, sum(quantity) as q, pg.contrainteProduit as qmin from paniers_produits pp, products pg where pp.idPanier = '" . $this->id . "' and pp.idProduct = pg.id group by pp.idProduct having q < qmin", __FILE__, __LINE__);
		$listpdt = array();
		
		while ($record = & $this->handle->fetch($result)) $listpdt[$record[0]] = $record[2];
		
		return $listpdt;
	}

	function & getANCCcommand()
	{

		$result = & $this->handle->query("select a.id, a.contrainteprix, sum(pg.price * pp.quantity) as s from advertisers a, products pg, paniers_produits pp where a.id = pg.idadvertiser and pg.id = pp.idproduct and pp.idPanier = '" . $this->id . "' and pp.idTC = 0 group by a.id order by a.id", __FILE__, __LINE__);
		$list_pdt = array();
		
		while ($record = & $this->handle->fetchArray($result))
		$list_pdt[] = & $record;
		
		$result = & $this->handle->query("select a.id, a.contrainteprix, sum(ref.price * pp.quantity) as s from advertisers a, products pg, references_content ref, paniers_produits pp where a.id = pg.idadvertiser and pg.id = pp.idproduct and pp.idTC = ref.id and pp.idPanier = '" . $this->id . "' group by a.id order by a.id", __FILE__, __LINE__);
		
		$i_pdt = 0;
		$listadv = array();
		
		while ($ref = & $this->handle->fetchArray($result))
		{
			if ($i_pdt == count($list_pdt))
			{
				if ($ref['s'] < $ref['contrainteprix'])
				$listadv[] = $ref['id'];
				continue;
			}
			if ($ref['id'] < $list_pdt[$i_pdt]['id'])
			{
				if ($ref['s'] < $ref['contrainteprix'])
				$listadv[] = $ref['id'];
			}
			else
			{
				if ($ref['id'] == $list_pdt[$i_pdt]['id'])
				{
					if ($ref['s'] + $list_pdt[$i_pdt]['s'] < $ref['contrainteprix'])
					$listadv[] = $ref['id'];
					$i_pdt++;
				}
				else
				{
					while ($i_pdt < count($list_pdt) && $list_pdt[$i_pdt]['id'] < $ref['id'])
					{
						if ($list_pdt[$i_pdt]['s'] < $list_pdt[$i_pdt]['contrainteprix'])
						$listadv[] = $list_pdt[$i_pdt]['id'];
						$i_pdt++;
					}
					
					if ($i_pdt == count($list_pdt))
					{
						if ($ref['s'] < $ref['contrainteprix'])
						$listadv[] = $ref['id'];
						continue;
					}
					
					if ($ref['id'] == $list_pdt[$i_pdt]['id'])
					{
						if ($ref['s'] + $list_pdt[$i_pdt]['s'] < $ref['contrainteprix'])
						$listadv[] = $ref['id'];
						$i_pdt++;
					}
					else
					{
						if ($ref['s'] < $ref['contrainteprix'])
						$listadv[] = $ref['id'];
					}
				}
			}
		}

		while ($i_pdt < count($list_pdt))
		{
			if ($list_pdt[$i_pdt]['s'] < $list_pdt[$i_pdt]['contrainteprix'])
			$listadv[] = $list_pdt[$i_pdt]['id'];
			$i_pdt++;
		}

		return $listadv;

	}

	function & loadCoord($kind = 'both')
	{
		if ($this->exist && ($result = & $this->handle->query("select totalHT, totalTTC, titre, nom, prenom, societe, adresse, complement, ville, cp, pays, infos_sup, tel1, titre_l, nom_l, prenom_l, societe_l, adresse_l, complement_l, ville_l, cp_l, pays_l, infos_sup_l, coord_livraison from paniers where id = '" . $this->id . "'", __FILE__, __LINE__)))
		{
			$ret = & $this->handle->fetchArray($result, $kind);  
			return $ret;
		}
		else return false;
	}

	function updateCoord(& $tab_coord)
	{
		$ret = false;
		
		if ($this->exist)
		{
			$query = "update paniers set timestamp = " . time();
			
			foreach($tab_coord as $coord => $value)
			$query .= ", " . $coord . " = '" . $this->handle->escape($value) . "'";
			
			$query .= " where id = '" . $this->id . "'";
			
			$this->handle->query($query, __FILE__, __LINE__);
			
			$ret = true;
		}
		
		return $ret;
	}

}


?>
