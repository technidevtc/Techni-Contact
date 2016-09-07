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
  
    if($result = & $handle->query('select id, timestamp, nom from clients' . $exp, __FILE__, __LINE__ ))
    {
        while($record = & $handle->fetchArray($result, 'assoc'))
        {
            $ret[] = & $record;
        }
		
    }

    return $ret;

}

function & displayCustomersSociety(& $handle, $exp = '')
{
    $ret = array();
  
    if($result = & $handle->query('select id, timestamp, societe from clients' . $exp, __FILE__, __LINE__ ))
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
	if ($handle->numrows($result, __FILE__, __LINE__) == 1)
	{
		$record = & $handle->fetchAssoc($result);
		return $record;
	}
	else return false;
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

	$handle->query('insert into clients (id, timestamp, nom, prenom) values (\'' . $id . '\', \'' . time() . '\', \'' . addslashes($nom_f) . '\', \'' . addslashes($prenom_f) . '\')');

	return true;
}


?>
