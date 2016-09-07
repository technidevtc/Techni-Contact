<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 20 décembre 2004
 
     3 juillet 2005 :  + gestion nom simplifié annonceur

 Fichier : /includes/managerV2/advertisers.php
 Description : Fonction manipulation annonceurs

/=================================================================*/

require_once(ADMIN . 'generator.php');


/* Retourner un tableau d'annonceurs
   i : référence handle connexion
   i : fin requete
   i : id utilisateur pour filtre optionnel
   o : référence tableau annonceurs */
function & displayDevis(& $handle, $exp = '', $idClient = '')
{
	if (empty($idClient))
        $query = 'select d.id, d.timestamp, d.create_time, d.totalHT, d.totalTTC, d.locked from devis d ' . $exp;
    else
        $query = 'select d.id, d.timestamp, d.create_time, d.totalHT, d.totalTTC, d.locked from devis d, clients c where c.id = \'' . $handle->escape($idClient) . '\' and c.id = d.idClient ' . $exp;
	
	$result = & $handle->query($query, __FILE__, __LINE__);

	$ret = array();
	
	while($record = & $handle->fetchArray($result, 'assoc'))
		$ret[] = & $record;
	
	return $ret;

}

function setTotalDevis(& $handle, $idDevis)
{
	$total = 0;
	
	if ($result = & $handle->query('select sum(pg.price*dp.quantity) from products pg, devis_produits dp where dp.idProduct = pg.id and dp.idTC = 0 and dp.idDevis = \'' . $handle->escape($idDevis). '\'', __FILE__, __LINE__))
	{
		$record = & $handle->fetch($result);
		$total += $record[0];
	}
	
	if ($result = & $handle->query('select sum(rc.price*dp.quantity) from references_content rc, devis_produits dp where rc.id = dp.idTC and dp.idTC != 0 and dp.idDevis = \'' . $handle->escape($idDevis). '\'', __FILE__, __LINE__))
	{
		$record = & $handle->fetch($result);
		$total += $record[0];
	}
	
	if ($handle->query('update devis set totalHT = \'' . $total . '\'')) return true;
	else return false;
	
}

/* Ajouter un annonceur
   i : réf handle connexion
   i : id client
   i : réf tableau avec la liste des produits/références
   i : validité par défaut du devis
   o : true si ok, false si erreur */
function addDevis(& $handle, $idClient, & $listpdt, $valid = '0')
{
	$idDevis = generateID(1, 999999999, 'id', 'devis', $handle);
	$ret = false;
	
	if ($handle->query('insert into devis (id, idClient, edit_time, create_time, valid) values(\'' . $idDevis . '\', \'' . $handle->escape($idClient) . '\', \'' . time() . '\', \'' . time() . '\', \'' . $handle->escape($valid). '\')'))
	{
		$ret = $idDevis;
		for ($i = 0; $i < count($listpdt); $i++)
		{
			$pdt = explode('-', $listpdt[$i]);
			if (!($handle->query('insert into devis_produits (idDevis, idProduct, idTC, quantity) values(\'' . $idDevis . '\', \'' . $handle->escape($pdt[0]) . '\', \'' . $handle->escape($pdt[1]) . '\', \'' . $handle->escape($pdt[2]) . '\')')))
			{
				$ret = false;
				break;
			}
		}
	}
	
	if ($ret) setTotalDevis($handle, $idDevis);

	return $ret;
	
}

function updateDevis(& $handle, $idDevis, & $listpdt)
{
    $ret = true;
	
	if ($handle->query('update devis set edit_time = \'' . time() . '\', timestamp = \'' . time() . '\' where id = \'' . $idDevis . '\'') && $handle->affected( __FILE__, __LINE__) <= 1)
	{
		for ($i = 0; $i < count($listpdt); $i++)
		{
			$pdt = explode('-', $listpdt[$i]);
			if (!($handle->query('update devis_produits set quantity = \'' . $handle->escape($pdt[2]) . '\', timestamp = \'' . time() . '\' where idDevis = \'' . $handle->escape($idDevis) . '\' and idProduct = \'' . $handle->escape($pdt[0]) . '\' and idTC = \'' . $handle->escape($pdt[1]) . '\'')) || $handle->affected( __FILE__, __LINE__) > 1)
			{
				$ret = false;
				break;
			}
		}
	}
	else $ret = false;
	
	if ($ret) setTotalDevis($handle, $idDevis);
	
	return $ret;

}


/* Vérifie si un devis appartient bien à un client spécifique, ce client devant exister
   i : réf handle connexion
   i : id devis
   i : id client
   o : true si le devis appartient au client false sinon */
function checkDevisCustomer(& $handle, $idDevis, $idClient)
{
	
	if(($result = & $handle->query('select d.id from devis d, clients c where d.id = \'' . $handle->escape($idDevis) . '\' and d.idClient = \'' . $handle->escape($idClient) . '\' and d.idClient = c.id and c.actif = 1', __FILE__, __LINE__)) && $handle->numrows($result, __FILE__, __LINE__) == 1)
		return true;
	else return false;
	
}

function & getPNCCdevis(& $handle, $idDevis)
{
	if ($result = & $handle->query('select dp.idProduct, sum( quantity ) as q, pg.contrainteProduit as qmin from devis_produits dp, products pg where dp.idDevis = \'' . $handle->escape($idDevis) . '\' and dp.idProduct = pg.id group by dp.idProduct having q < qmin'))
	{
        $listpdt = array();
		
		while ($record = & $handle->fetch($result))
			$listpdt[] = $record[0];
		
		return $listpdt;
	}
	else
		return false;
}

function & getANCCdevis(& $handle, $idDevis)
{
	
	if ($result = & $handle->query('select a.id, a.contrainteprix, sum(pg.price * dp.quantity) as s from advertisers a, products pg, devis_produits dp where a.id = pg.idadvertiser and pg.id = dp.idproduct and dp.iddevis = \'' . $handle->escape($idDevis) . '\'' . ' and dp.idtc = 0 group by a.id having s < a.contrainteprix order by a.id'))
	{
		$listadv = array();
		
		while ($record = & $handle->fetch($result))
			$listadv[] = & $record;
		
		
		if ($result = & $handle->query('select a.id, a.contrainteprix, sum(ref.price * dp.quantity) as s from advertisers a, products pg, references_content ref, devis_produits dp where a.id = pg.idadvertiser and pg.id = dp.idproduct and dp.idtc = ref.id and dp.iddevis = \'' . $handle->escape($idDevis) . '\'' . ' group by a.id order by a.id'))
		{
			$i = 0;
			$listadv2 = array();
			while ($record = & $handle->fetch($result))
			{
				if ($i == count($listadv))
				{
					$listadv2[] = $record[0];
					continue;
				}
				if ($record[0] < $listadv[$i][0])
				{
					$listadv2[] = $record[0];
				}
				else
				{
					if ($record[0] == $listadv[$i][0])
					{
						if ($record[2] + $listadv[$i][2] < $record[1])
						{
							$listadv2[] = $record[0];
						}
						$i++;
					}
					else
					{
						while ($i < count($listadv)  && $record[0] > $listadv[$i][0])
						{
							$listadv2[] = $listadv[$i][0];
							$i++;
						}
						
						if ($i == count($listadv))
						{
							$listadv2[] = $record[0];
							continue;
						}
						
						if ($record[0] == $listadv[$i][0])
						{
							if ($record[2] + $listadv[$i][2] < $record[1])
							{
								$listadv2[] = $record[0];
							}
							$i++;
						}
						else
						{
							$listadv2[] = $record[0];
						}
					}
				}
			}
			
			while ($i < count($listadv))
			{
				$listadv2[] = $listadv[$i][0];
				$i++;
			}
			
			return $listadv2;
			
		}
		else
			return false;
		
	}
	else
		return false;
	
}

function validate(& $handle, $idDevis)
{
	if ($result = & $handle->query('update devis set valid = 1, timestamp = \'' . time() . '\' where id = \'' . $handle->escape($idDevis) . '\''))
		return true;
	else return false;
}

function unvalidate(& $handle, $idDevis)
{
	if ($result = & $handle->query('update devis set valid = 0, timestamp = \'' . time() . '\' where id = \'' . $handle->escape($idDevis) . '\''))
		return true;
	else return false;
}

function isValid(& $handle, $idDevis)
{
	if (($result = & $handle->query('select valid from devis where id = \'' . $handle->escape($idDevis) . '\'')) && $handle->numrows($result, __FILE__, __LINE__) == 1)
	{
		$record = & $handle->fetch($result);
		if ($record[0] != 0) return 1;
		else return 0;
	}
	else return -1;
}

function lockDevis(& $handle, $idDevis)
{
	if ($result = & $handle->query('update devis set locked = 1, timestamp = \'' . time() . '\' where id = \'' . $handle->escape($idDevis) . '\''))
		return true;
	else return false;
}

function unlockDevis(& $handle, $idDevis)
{
	if ($result = & $handle->query('update devis set locked = 0, timestamp = \'' . time() . '\' where id = \'' . $handle->escape($idDevis) . '\''))
		return true;
	else return false;
}

function isLocked(& $handle, $idDevis)
{
	if (($result = & $handle->query('select locked from devis where id = \'' . $handle->escape($idDevis) . '\'')) && $handle->numrows($result, __FILE__, __LINE__) == 1)
	{
		$record = & $handle->fetch($result);
		if ($record[0] != 0) return 1;
		else return 0;
	}
	else return -1;
}

function & loadDevis(& $handle, $idDevis, & $listpdt, & $expr)
{
	$ret = false;
	
	if(($result = & $handle->query('select edit_time, create_time, totalHT, totalTTC, valid from devis where id = \'' . $handle->escape($idDevis) . '\'', __FILE__, __LINE__)) && $handle->numrows($result, __FILE__, __LINE__) == 1)
	{
		$ret = & $handle->fetch($result);
		
		if ($result = & $handle->query('select pg.id, dp.idTC, dp.quantity from devis_produits dp, products pg where dp.idDevis = \'' . $handle->escape($idDevis) . '\' and pg.id = dp.idProduct' . $expr, __FILE__, __LINE__))
		{
			$listpdt = array();
			
			while($record = & $handle->fetch($result))
				$listpdt[] = & $record;
			
		}
		else $ret = false;
	}
	
	return $ret;
	
}


/* Chargement annonceur
   i : réf handle connexion
   i : id annonceur
   o : réf tableau élément ou false si erreur */
function & loadDevis2(& $handle, $idDevis, & $devisInfos, & $devisProducts, & $expr)
{
    
	if(($result = & $handle->query('select edit_time, create_time, totalHT, totalTTC, valid from devis where id = \'' . $handle->escape($idDevis) . '\'', __FILE__, __LINE__)) && $handle->numrows($result, __FILE__, __LINE__) == 1)
        $devisInfos = & $handle->fetch($result);
	else
		return false;
		
	if ($result = & $handle->query('select pg.id, dp.idTC, dp.quantity from devis_produits dp, products pg where dp.idDevis = \'' . $handle->escape($idDevis) . '\' and pg.id = dp.idProduct' . $expr, __FILE__, __LINE__))
	{
		$i = 0;
		$pdtinfos = array();
		while($pdt = & $handle->fetch($result))
		{
			$pdtinfos[$i] = array();
			$pdtinfos[$i][0] = $pdt[0];
			$pdtinfos[$i][1] = $pdt[1];
			$pdtinfos[$i][2] = $pdt[2];
			
			if ($pdt[1] == 0)
			{
				if(($result2 = & $handle->query('select pg.idAdvertiser, p.name, p.fastdesc, pg.refSupplier, pg.price, pg.idTVA, pg.contrainteProduit, pg.tauxRemise from products_fr p, products pg where p.id = \'' . $handle->escape($pdt[0]) . '\' and p.id = pg.id', __FILE__, __LINE__)) && $handle->numrows($result, __FILE__, __LINE__) == 1)
				{
					$pdtinfostmp = & $handle->fetch($result2);
					for ($j = 0; $j < count($pdtinfos); $j++)
						$pdtinfos[$i][$j+3] = $pdtinfostmp[$j];
					$i++;
				}
			}
			else
			{
				if(($result2 = & $handle->query('select pg.idAdvertiser, p.name, p.fastdesc, ref.label, ref.refSupplier, ref.price, ref.idTVA, pg.contrainteProduit, pg.tauxRemise from products_fr p, products pg, references_content ref where ref.id = \'' . $handle->escape($pdt[1]) . '\' and ref.idProduct = pg.id and p.id = pg.id', __FILE__, __LINE__)) && $handle->numrows($result, __FILE__, __LINE__) == 1)
				{
					$pdtinfostmp = & $handle->fetch($result2);
					for ($j = 0; $j < count($pdtinfos); $j++)
						$pdtinfos[$i][$j+3] = $pdtinfostmp[$j];
					$i++;
				}
			}
		}
		
		$devisProducts = & $pdtinfos;
		
		return true;
		
	}
	else
		return false;

}


/* Supprimer un annonceur
   i : réf handle connexion
   i : id annonceur
   i : réf nom annonceur */
function delDevis(& $handle, $idDevis)
{
    $handle->query('delete from devis where id = \'' . $handle->escape($idDevis) . '\'', __FILE__, __LINE__);
	$handle->query('delete from devis_produits where idDevis = \'' . $handle->escape($idDevis) . '\'', __FILE__, __LINE__);
}


?>
