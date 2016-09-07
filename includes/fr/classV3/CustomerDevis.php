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

class CustomerDevis
{
	/* id */
	var $id = 0;

	/* Handle connexion */
	var $handle = NULL;

	/* Constructeur, set la session à utiliser
	i : référence sur la connexion au SGBDR */
	function CustomerDevis(& $handle)
	{
		$this->handle = & $handle;
	}

	/******************** fonctions panier ********************/
  
	function getID() { return $this->id; }  
	function setID(& $id) { $this->id = $id; return true; }
	//function getCount() { return $this->count; }
  
	function generateID()
	{
		$id = mt_rand(1, 999999999);
		
		do $result = & $this->handle->query("select id from devis where id = " . $id, __FILE__, __LINE__);
		while ($this->handle->numrows($result, __FILE__, __LINE__) == 1);
		
		$this->id = & $id;
	}
  
	function createFromBasket($idBasket)
	{
		$ret = false;
		
		$result = & $this->handle->query("select p.totalHT, p.totalTTC, p.locked, p.idClient, c.titre, c.nom, c.prenom, c.societe, c.adresse, c.complement, c.ville, c.cp, c.pays, c.tel1, c.titre_l, c.nom_l, c.prenom_l, c.societe_l, c.adresse_l, c.complement_l, c.ville_l, c.cp_l, c.pays_l, c.coord_livraison from paniers p, clients c where p.id = '" . $this->handle->escape($idBasket) . "' and p.idClient = c.id", __FILE__, __LINE__);
		
		if ($this->handle->numrows($result, __FILE__, __LINE__) == 1)
		{
			$data = & $this->handle->fetchAssoc($result);
			
			$this->generateID();
			
			$query = "insert into devis (id, timestamp, create_time";
			$query2 .= "values (" . $this->id . ", " . time() . ", " . time();
			foreach ($data as $type => $value)
			{
				$query .= ", " . $type;
				$query2 .= ", '" . $this->handle->escape($value) . "'";
			}
			$query .= ") " . $query2 . ")";
			
			$this->handle->query($query, __FILE__, __LINE__);
			
			$result = & $this->handle->query("select idProduct, idTC, idFamily, quantity from paniers_produits where idPanier = '" . $this->handle->escape($idBasket) . "'", __FILE__, __LINE__);
			
			while ($pdt = & $this->handle->fetchAssoc($result))
			$this->handle->query("insert into devis_produits (idDevis, idProduct, idTC, idFamily, quantity) values (" . $this->id . ", '" . $pdt['idProduct'] . "', '" . $pdt['idTC'] . "', '" . $pdt['idFamily'] . "', '" . $pdt['quantity'] . "')", __FILE__, __LINE__);
			
			$ret = true;
		}
		
		return $this->id;
	}
    
	function delete()
	{
		$this->handle->query("delete from devis_produits where idDevis = " . $this->id, __FILE__, __LINE__);
		$this->handle->query("delete from devis where id = " . $this->id, __FILE__, __LINE__);
	}
  
  /******************** fonctions produits panier ********************/
  
	function getCount()
	{
		$result = & $this->handle->query("select count(*) from devis_produits where idDevis = " . $this->id, __FILE__, __LINE__);
		$record = & $this->handle->fetch($result);
		
		return $record[0];
	}
  
	function updateValues()
	{
    
		$totalHT = 0;
		$totalTTC = 0;
		
		$res = $this->handle->query("select dp.idProduct, pg.price, dp.quantity, pg.tauxRemise, tva.taux from products pg, devis_produits dp, tva where dp.idProduct = pg.id and dp.idTC = 0 and pg.idTVA = tva.id and dp.idDevis = '" . $this->id . "'");
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
		
		$res = $this->handle->query("select dp.idproduct, sum(rc.price*dp.quantity) as sumpriceHT, sum(rc.price*dp.quantity*(100+tva.taux)/100) as sumpriceTTC, sum(dp.quantity) as sumqty, pg.tauxRemise from references_content rc, devis_produits dp, products pg, tva where dp.idtc = rc.id and dp.idproduct = pg.id and dp.idtc !=0 and rc.idTVA = tva.id and dp.idDevis = '" . $this->id . "' group by dp.idproduct");
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
		
		$this->handle->query("update devis set totalHT = " . $totalHT . ", totalTTC = " . $totalTTC . ", timestamp = " . time() . ", locked = 0 where id = " . $this->id, __FILE__, __LINE__);

		
		return true;
	}

  
	function updateQuantities(& $listpdt)
	{
		$ret = true;
		foreach ($listpdt as $pdt)
		{
			$pdt[0] = (int)$pdt[0]; $pdt[1] = (int)$pdt[1]; $pdt[2] = (int)$pdt[2];
			if (!$this->handle->query("update devis_produits set quantity = " . $pdt[2] . " where idDevis = " . $this->id . " and idProduct = " . $pdt[0] . " and idTC = " . $pdt[1], __FILE__, __LINE__) || $this->handle->affected( __FILE__, __LINE__) > 1)
			{
				$ret = false;
				break;
			}
		}
		if ($ret) $this->updateValues();
		return $ret;
	}
  

	function UpdateComment($idTC, $comment)
	{
		if ($this->handle->query("update devis_produits set comment = '" . $this->handle->escape($comment) . "' where idDevis = '" . $this->id . "' and idTC = " . $idTC, __FILE__, __LINE__))
			return true;
		else return false;
	}
	
	
	function & loadProducts($expr = '')
	{
		$result = & $this->handle->query("select p.id, dp.idTC, dp.idFamily, dp.quantity, dp.comment from devis_produits dp, products p where dp.idDevis = " . $this->id . " and p.id = dp.idProduct" . $expr, __FILE__, __LINE__);
		
		$listpdt = array();
		while ($pdt = & $this->handle->fetchAssoc($result)) $listpdt[] = & $pdt;
		
		return $listpdt;
	}
  
  /******************** fonctions commande ********************/
  
	function getClientID()
	{
		$result = & $this->handle->query('select idClient from devis where id = \'' . $this->id .'\'', __FILE__, __LINE__);
		if ($this->handle->numrows($result, __FILE__, __LINE__) == 1)
		{
			$ret = & $this->handle->fetch($result);
			return $ret[0];
		}
		else return false;
	}
  
	function lock()
	{
		return $this->handle->query('update devis set locked = 1 where id = \'' . $this->id . '\'', __FILE__, __LINE__);
	}
  
	function unlock()
	{
		return $this->handle->query('update devis set locked = 0 where id = \'' . $this->id . '\'', __FILE__, __LINE__);
	}
  
	function isLocked()
	{
		$result = & $this->handle->query('select locked from devis where id = \'' . $this->id . '\'', __FILE__, __LINE__);
		$record = & $this->handle->fetch($result);
		if ($record[0] != 0) return true;
		else return false;
	}
  
	function & getPNCCcommand()
	{
		$result = & $this->handle->query('select dp.idProduct, sum(quantity) as q, pg.contrainteProduit as qmin from devis_produits dp, products pg where dp.idDevis = \'' . $this->id . '\' and dp.idProduct = pg.id group by dp.idProduct having q < qmin', __FILE__, __LINE__);
		$listpdt = array();
		
		while ($record = & $this->handle->fetch($result)) $listpdt[$record[0]] = $record[2];
		
		return $listpdt;
	}

  function & getANCCcommand()
  {
    
    $result = & $this->handle->query('select a.id, a.contrainteprix, sum(pg.price * dp.quantity) as s from advertisers a, products pg, devis_produits dp where a.id = pg.idadvertiser and pg.id = dp.idproduct and dp.idDevis = \'' . $this->id . '\'' . ' and dp.idTC = 0 group by a.id order by a.id', __FILE__, __LINE__);
    $list_pdt = array();
    
    while ($record = & $this->handle->fetchArray($result))
      $list_pdt[] = & $record;
    
    $result = & $this->handle->query('select a.id, a.contrainteprix, sum(ref.price * dp.quantity) as s from advertisers a, products pg, references_content ref, devis_produits dp where a.id = pg.idadvertiser and pg.id = dp.idproduct and dp.idTC = ref.id and dp.idDevis = \'' . $this->id . '\'' . ' group by a.id order by a.id', __FILE__, __LINE__);
    
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
    $result = & $this->handle->query('select titre, nom, prenom, societe, adresse, complement, ville, cp, pays, infos_sup, tel1, titre_l, nom_l, prenom_l, societe_l, adresse_l, complement_l, ville_l, cp_l, pays_l, infos_sup_l, coord_livraison from devis where id = \'' . $this->id . '\'' , __FILE__, __LINE__);
    $ret    = & $this->handle->fetchArray($result, $kind);  
    return $ret;
  }

  function & loadTimes($kind = 'assoc')
  {
    $result = & $this->handle->query('select timestamp, create_time, totalHT, totalTTC from devis where id = \'' . $this->id . '\'' , __FILE__, __LINE__);
    $ret    = & $this->handle->fetchArray($result, $kind);
    return $ret;
  }
  
  function updateCoord(& $tab_coord)
  {
    $ret = false;
    
    $query = 'update devis set timestamp = \'' . time() .  '\'';
    
    foreach($tab_coord as $coord => $value)
      $query .= ', ' . $coord . ' = \'' . $this->handle->escape($value) . '\'';
    
    $query .= ' where id = \'' . $this->id .'\'';
    
    $this->handle->query($query, __FILE__, __LINE__);
    
    $ret = true;
    
    return $ret;
  }
  
}


?>
