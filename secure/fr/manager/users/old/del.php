<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de cr�ation : 2 juin 2005

 Fichier : /secure/manager/users/del.php
 Description : Suppression d'un utilisateur

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
require(ADMIN . 'users.php');


$title  = 'Base de donn�es des utilisateurs';
$navBar = '<a href="index.php?SESSION" class="navig">Base de donn�es des utilisateurs</a> &raquo; Supprimer un utilisateur';
require(ADMIN . 'head.php');

// Seul admin / tech autoris�
if($user->rank != COMMADMIN && $user->rank != HOOK_NETWORK)
{
    print('<div class="bg"><div class="fatalerror">Vous n\'avez pas les droits ad�quats pour r�aliser cette op�ration.</div></div>');
}
// Contr�le idenfifiant
else if(!isset($_GET['id']) || !preg_match('/^[1-9][0-9]*$/', $_GET['id']) || !($data = loadUserData($handle, $_GET['id'])))
{
    print('<div class="bg"><div class="fatalerror">Identifiant utilisateur inconnu.</div></div>');
}
else
{
    // Suppression admin ou tech impossible
    if($data[2] == COMMADMIN || $data[2] == HOOK_NETWORK)
    {
        print('<div class="bg"><div class="fatalerror">Vous n\'avez pas les droits ad�quats pour r�aliser cette op�ration.</div></div>');
    }
    else
    {
        $result = delUser($handle, $_GET['id']);
        
        if($result)
        {
            ManagerLog($handle, $user->id, $user->login, $user->pass, $user->ip, 'Suppression de l\'utilisateur : ' . $data[0]);
        }

        $users = & getUsers($handle);


?>
<div class="titreStandard">Liste des utilisateurs existants</div><br>
<div class="bg"><div class="commentaire">S�lectionnez dans la liste suivante l'utilisateur dont vous souhaitez modifier les informations personnelles et validez.
<br>Vous pourrez �galement effacer l'utilisateur depuis la page d'�dition de ses informations.</div>
<br><br><form method="get" action="edit.php" class="formulaire"><select name="id"><?php

        foreach($users as $k => $v)
        {
            if($v[2] == COMMADMIN || $v[2] == HOOK_NETWORK)
            {
                continue;
            }

            print('<option value="' . $v[0] . '">' . to_entities($v[1]) . '</option>');
        }

?></select><?php print('<input type="hidden" name="' . session_name() . '" value="' . session_id() . '">') ?>
<br><center><input type="button" class="bouton" value="Editez le profil de cet utilisateur" onClick="this.form.submit(); this.disabled = true"></center></form></div>
<br><br>
<div class="titreStandard">Suppression du compte utilisateur <?php print(to_entities($data[0])) ?></div><br>
<div class="bg"><?php

        if($result)
        {
            print('<div class="confirm">Utilisateur ' . to_entities($data[0]) . ' supprim� avec succ�s.</div>');
        }
        else
        {
            print('<div class="error">Erreur lors de la suppression.</div>');
        }

?></div><?php

    } // suppression compte ok

}  // fin autorisation ou id valide

require(ADMIN . 'tail.php');

?>
