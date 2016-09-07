<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 20 décembre 2004

 Fichier : /includes/managerV2/hook.php
 Description : Fonctions pour les techniciens Hook

/=================================================================*/


define('RESET_SQL_LOG', 0);
define('CAT_SQL_LOG',   1);
define('RESET_PHP_LOG', 2);
define('CAT_PHP_LOG',   3);


function resetLog($which)
{
    $ret = false;

    $handle = fopen($which, 'w');

    if($handle)
    {
        $ret = true;
        fclose($handle);
    }

    return $ret;
}


function catLog($which)
{
    $ret = false;

    $data = file($which);

    if($data)
    {
        $ret = true;
        print('<div class="commentaire">' . nl2br(implode('', $data)) . '</div>');
    }

    return $ret;
}



?>
