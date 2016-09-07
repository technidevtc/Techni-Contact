<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 2 juin 2005

 Fichier : /includes/managerV2/logs.php
 Description : Log action utilisateur sur le manager

/=================================================================*/

/* Enregistrer une action
   i : référence handle connexion
   i : id utilisateur
   i : référence login utilisateur
   i : référence mot de passe utilisateur
   i : référence ip utilisateur
   i : action effectuée */
function Managerlog(& $handle, $id, & $login, & $md5_pass, & $ip, $action)
{
   $data = $login . ' | ' . $ip;
   $handle->query('insert into logs (idUser, timestamp, session, action) values(\'' . $handle->escape($id) . '\', ' . time() . ', \'' . $handle->escape($data) . '\', \'' . $handle->escape($action) . '\')', __FILE__, __LINE__);

}



/* Retourner 15 derniers enregistrements
   i : référence handle connexion
   o : référence tableau enregistrements */
function & last15(& $handle)
{
    $ret = array();

    if($result = & $handle->query('select timestamp, session, action from logs order by timestamp desc limit 15', __FILE__, __LINE__))
    {
        while($row = & $handle->fetch($result))
        {
            $ret[] = & $row;
        }

    }

    return $ret;
}

/* Retourner les actions d'un jour donné
   i : timestamp début jour
   i : timestamp fin jour
   o : référence tableau enregistrements */
function & day(& $handle, $begin, $end)
{
    $ret = array();

    if($result = & $handle->query('select timestamp, session, action from logs where timestamp >= \'' . $begin . '\' and timestamp < \'' . $end . '\' order by timestamp', __FILE__, __LINE__))
    {
        while($row = & $handle->fetch($result))
        {
            $ret[] = & $row;
        }

    }

    return $ret;
}

?>
