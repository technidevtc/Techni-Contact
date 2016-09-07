<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 13 juillet 2005

 Fichier : /includes/extranet/logs.php4
 Description : Log action utilisateur sur l'extranet

/=================================================================*/

/* Enregistrer une action
   i : référence handle connexion
   i : référence login utilisateur
   i : référence ip utilisateur
   i : action effectuée */
function Extranetlog(& $handle, & $login, & $ip, $action)
{
   $data = $login . ' | ' . $ip;
   $handle->query('insert into extranetlogs (timestamp, session, action) values(' . time() . ', \'' . $handle->escape($data) . '\', \'' . $handle->escape($action) . '\')', __FILE__, __LINE__);

}


/* Retourner les actions d'un jour donné
   i : timestamp début jour
   i : timestamp fin jour
   o : référence tableau enregistrements */
function & Eday(& $handle, $begin, $end)
{
    $ret = array();

    if($result = & $handle->query('select timestamp, session, action from extranetlogs where timestamp >= \'' . $begin . '\' and timestamp < \'' . $end . '\' order by timestamp', __FILE__, __LINE__))
    {
        while($row = & $handle->fetch($result))
        {
            $ret[] = & $row;
        }

    }

    return $ret;
}

?>
