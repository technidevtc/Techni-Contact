<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de cr�ation : 20 d�cembre 2004

 Fichier : /includes/managerV2/actions.php
 Description : Fonction manipulation actions

/=================================================================*/


/* Notifier une action
  i : r�f handle connexion
  i : action
  i : nom utilisateur */
function notify(& $handle, $action, $user)
{
    $handle->query('insert into actions (id, username, action) values(\'' . time() . '\', \'' . $handle->escape($user) . '\', \'' . $handle->escape($action) . '\')');
}




?>
