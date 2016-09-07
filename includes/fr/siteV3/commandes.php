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


function & displayCommands(& $handle, $exp = '', $idClient = '')
{
	if (empty($idClient))
        $query = 'select cmd.id, cmd.timestamp, cmd.create_time, cmd.totalHT, cmd.totalTTC, cmd.statut_paiement, cmd.statut_traitement from commandes cmd ' . $exp;
    else
        $query = 'select cmd.id, cmd.timestamp, cmd.create_time, cmd.totalHT, cmd.totalTTC, cmd.statut_paiement, cmd.statut_traitement from commandes cmd, clients c where c.id = \'' . $handle->escape($idClient) . '\' and c.id = cmd.idClient ' . $exp;
	
	$result = & $handle->query($query, __FILE__, __LINE__);

	$ret = array();
	
	while($record = & $handle->fetchArray($result, 'assoc'))
		$ret[] = & $record;
	
	return $ret;

}


?>
