<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de cr�ation : 2 juin 2005

 Fichier : /includes/managerV2/logs.php
 Description : Log action utilisateur sur le manager

/=================================================================*/

/* Enregistrer une action
   i : r�f�rence handle connexion
   i : id utilisateur
   i : r�f�rence login utilisateur
   i : r�f�rence mot de passe utilisateur
   i : r�f�rence ip utilisateur
   i : action effectu�e */
function Managerlog(& $handle, $id, & $login, & $md5_pass, & $ip, $action)
{
   $data = $login . ' | ' . $ip;
   $handle->query('insert into logs (idUser, timestamp, session, action) values(\'' . $handle->escape($id) . '\', ' . time() . ', \'' . $handle->escape($data) . '\', \'' . $handle->escape($action) . '\')', __FILE__, __LINE__);

}



/* Retourner 15 derniers enregistrements
   i : r�f�rence handle connexion
   o : r�f�rence tableau enregistrements */
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

/* Retourner les actions d'un jour donn�
   i : timestamp d�but jour
   i : timestamp fin jour
   o : r�f�rence tableau enregistrements */
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
