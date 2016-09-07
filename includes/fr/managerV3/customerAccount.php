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
require_once(ADMIN . 'actions.php');
require_once(ADMIN . 'logo.php');

function isUniqueLogin($handle, $login)
{
	if(($result = & $handle->query('select id from clients where login = \'' . $handle->escape($login) . '\'', __FILE__, __LINE__)) && $handle->numrows($result, __FILE__, __LINE__) == 0)
		return true;
	else return false;
}

function validateAccount($handle, $idClient)
{
	return $handle->query('update clients set actif = 1, timestamp = \'' . time() . '\' where id = \'' . $handle->escape($idClient) . '\'', __FILE__, __LINE__);
}

/* Ajouter un compte client
   i : réf handle connexion
   o : true si ok, false si erreur */
function createAccount($handle, $tab_coord, $pass)
{
	$ret = false;
	/*		
	$nom_id  = substr($nom_p, 0, 4);
	$cp_id   = substr($cp_p, 0, 2);
	$pays_id =  substr($pays_p, 0, 2);
	
	$idCustomer = generateID(1, 9999, 'id', 'clients', $handle);
	$idCustomer = $cp_id . $nom_id . $pays_id . $idCustomer;
	*/		
	
	$idCustomer = generateID(1, 999999999, 'id', 'clients', $handle);
	$activationCode = generatePassword(8);
	
	$query = "insert into clients (id, timestamp, pass, last_update, activationCode, actif";
	$query2 .= "values ('$idCustomer', '" . time() . "', '" . md5($handle->escape($pass)) . "', '" . time() . "', '$activationCode', 1";
	foreach ($tab_coord as $coord => $value)
	{
		$query .= ', ' . $coord;
		$query2 .= ', \'' . $handle->escape($value) . '\'';
	}
	$query .= ') ' . $query2 . ')';
	
	if ($handle->query($query, __FILE__, __LINE__))
	{
		$ret = $idCustomer;
	}
	
	return $ret;
}

?>
