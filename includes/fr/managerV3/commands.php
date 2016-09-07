<?php

/*================================================================/

 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 3 avril 2006
 
Mises à jour :

 Fichier : /includes/managerV2/tva.php
 Description : Fonction manipulation taux de TVA

/=================================================================*/

require_once(ADMIN . 'generator.php');
require_once(ADMIN . 'actions.php');
require_once(ADMIN . 'logo.php');
require_once(ADMIN . 'config.php');



/* Retourner un tableau des taux de TVA
   i : réf handle connexion 
   o : réf tableau taux TVA */
function & displayCustomers(& $handle, $exp = '')
{
    $ret = array();
  
    if($result = & $handle->query('select id, timestamp, nom_f from clients' . $exp, __FILE__, __LINE__ ))
    {
        while($record = & $handle->fetchArray($result, 'assoc'))
        {
            $ret[] = & $record;
        }
		
    }

    return $ret;

}


function & loadCustomer(& $handle, $id)
{
	$result = & $handle->query('select * from clients where id = \'' . $handle->escape($id) . '\'', __FILE__, __LINE__);
	if ($handle->numrows($result, __FILE__, __LINE__) == 1) return $result;
	else return false;
}


/* Test l'existence d'un taux de TVA
   i : réf handle connexion 
   i : taux de TVA
   o : true si existe, sinon false */
function existTVA(& $handle, $idTVA)
{
	$idTVAexist = false;
	
	$listeTVAs = & displayTVAs($handle, '');

	foreach ($listeTVAs as $k => $v)
	{
		if ($idTVA == $v[0])
		{
			$idTVAexist = true;
			break;
		}
	}

	return $idTVAexist;
}


/* Charger un taux de TVA
   i : réf handle connexion 
   o : réf tableau élément ou false si erreur */
function & loadTVA(& $handle, $id)
{
	if(($result = & $handle->query('select id, intitule, taux from tva where id = \'' . $handle->escape($id) . '\'', __FILE__, __LINE__)) && $handle->numrows($result, __FILE__, __LINE__) == 1)
    {
        $ret = & $handle->fetch($result);
		if ($ret[0] == getConfig($handle, 'idTVAdft')) $ret[3] = '1';
		return $ret;
    }
	else
	{
		return false;
	}
}

/* Ajoute un taux de TVA
   i : réf handle connexion
   i : id taux TVA
   i : réf intitulé
   i : taux
   i : le taux TVA est celui par défaut
   o : true si ok, false si erreur */
function addCustomer(& $handle)
{

	$id = generateID(1, 999999999, 'id', 'clients', $handle);

	$nomrand = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ\'';
	$prenomrand = 'abcdefghijklmnopqrstuvwxyz-\'';
	
	$nom_f = 'M';
	for($i = mt_rand(4,11); $i >= 0; $i--)
		$nom_f .= $nomrand[mt_rand(0, strlen($nomrand) - 1)];
	
	$prenom_f = '';
	for($i = mt_rand(4,20); $i >= 0; $i--)
		$prenom_f .= $prenomrand[mt_rand(0, strlen($prenomrand) - 1)];

	$handle->query('insert into clients (id, timestamp, nom_f, prenom_f) values (\'' . $id . '\', \'' . time() . '\', \'' . addslashes($nom_f) . '\', \'' . addslashes($prenom_f) . '\')');

	return true;
}

/* Mettre à jour un taux de TVA
   i : réf handle connexion
   i : id Taux TVA
   i : réf intitulé
   i : taux
   i : le taux TVA est celui par défaut
   o : true si ok, false si erreur */
function updateTVA(& $handle, $id, & $intitule, $taux, $TVAisDft, $oldintitule)
{
    $ret = false;

	if($handle->query('update tva set timestamp = \'' . time() . '\', intitule = \'' . $handle->escape($intitule) . '\', taux = \'' . $handle->escape($taux) . '\' where id = \'' . $handle->escape($id) . '\'') && $handle->affected() == 1)
	{
		
		$EOLog = '';
		$TVAdft = & loadTVA($handle, getConfig($handle, 'idTVAdft'));
		
		
		if ($TVAisDft == '1')
		{
			if ($TVAdft[0] != $id)
			{
				if (setConfig($handle, 'idTVAdft', $id)) $EOLog .= ' (nouveau taux par défaut)';
			}
		}
		else
		{
			if ($TVAdft[0] == $id)
			{
				$EOLog .= ' (ce taux reste celui par défaut)';
			}
		}
		
		ManagerLog($handle, $_SESSION['id'], $_SESSION['login'], $_SESSION['pass'], $_SESSION['ip'],  'Modification du taux de TVA \'' . $oldintitule . '\' / Nouvelles données : Taux de TVA \'' . $intitule . '\' à ' . $taux . '%' . $EOLog);
		
		$ret = true;
		
	}

	return $ret;
}

/* Supprime un taux de TVA
   i : réf handle connexion
   i : id taux TVA
   i : id taux de TVA qui le remplacera
   o : true si ok, false si erreur */
function delTVA(& $handle, $id, $idReplace)
{
    $ret = false;

	if ($id != $idReplace)
	{
		$TVAdeleted = & loadTVA($handle, $id);
		if($handle->query('delete from tva where id = \'' . $handle->escape($id) . '\'', __FILE__, __LINE__))
		{
			if ($id == getConfig($handle, 'idTVAdft')) setConfig($handle, 'idTVAdft', $idReplace);
			
			// Changement des taux de TVA des fournisseurs
			$handle->query('update advertisers set idTVA = \'' . $handle->escape($idReplace) . '\' where idTVA = \'' . $handle->escape($id) . '\'');
			//$handle->query('update advertisers_adv set idTVA = \'' . $handle->escape($idReplace) . '\' where idTVA = \'' . $handle->escape($id) . '\'');
			
			// Changement des taux de TVA des produits
			$handle->query('update products set idTVA = \'' . $handle->escape($idReplace) . '\' where idTVA = \'' . $handle->escape($id) . '\'');
			$handle->query('update products_add set idTVA = \'' . $handle->escape($idReplace) . '\' where idTVA = \'' . $handle->escape($id) . '\'');
			$handle->query('update products_add_adv set idTVA = \'' . $handle->escape($idReplace) . '\' where idTVA = \'' . $handle->escape($id) . '\'');
			
			// Changement des taux de TVA des références
			$handle->query('update references_content set idTVA = \'' . $handle->escape($idReplace) . '\' where idTVA = \'' . $handle->escape($id) . '\'');
			
			ManagerLog($handle, $_SESSION['id'], $_SESSION['login'], $_SESSION['pass'], $_SESSION['ip'],  'Suppression du taux de TVA ' . $TVAdeleted[1] . ' à ' . $TVAdeleted[2] . '%');
			
			$ret = true;
			
		}
	}

	return $ret;
}

?>
