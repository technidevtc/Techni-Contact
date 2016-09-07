<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 20 décembre 2004

 Mises à jour :

       2 juin 2005 : = réécriture avec nouveau gestionnaire du formulaire + affichage

 Fichier : /secure/manager/users/index.php
 Description : Ajout d'un utilisateur

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
require(ADMIN . 'users.php');


$title  = 'Base de données des utilisateurs';
$navBar = '<a href="index.php?SESSION" class="navig">Base de données des utilisateurs</a> &raquo; Ajouter un utilisateur';
require(ADMIN . 'head.php');


if($user->rank != COMMADMIN && $user->rank != HOOK_NETWORK)
{
    print('<div class="bg"><div class="fatalerror">Vous n\'avez pas les droits adéquats pour réaliser cette opération.</div></div>');
}
else
{
    
    if($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        $error      = false;
        $errorString = '';
        
        
        $login = isset($_POST['login']) ? trim(substr($_POST['login'], 0, 15)) : '';
        if(strlen($login) > 3)
        {
            $login = preg_replace('/ +/', ' ', $login);
        }

        if(strlen($login) < 6)
        {
            $error = true;
            $errorString .= '- Le nom d\'utilisateur est incorrect<br>';
        }
        else if(!isUnique($handle, 'login', $login))
        {
            $error = true;
            $errorString .= '- Ce nom d\'utilisateur est déjà utilisé<br>';
        }


        $email = isset($_POST['email']) ? trim(substr($_POST['email'], 0, 50)) : '';
        $email = strtolower($email);

        if($email == '' || strlen($email) < 6 || !preg_match('`^[[:alnum:]]([-_.]?[[:alnum:]])*@[[:alnum:]]([-_.]?[[:alnum:]])*\.([a-z]{2,4})$`', $email))
        {
            $error = true;
            $errorString .= '- L\'adresse email est incorrecte<br>';
        }
        else if(!isUnique($handle, 'email', $email))
        {
            $error = true;
            $errorString .= '- Cette adresse email est déjà utilisée<br>';
        }
        
        $rank = isset($_POST['rank']) ? $_POST['rank'] : CONTRIB;
        
        // Seulement création contrib + commercial
        if(getRank($rank) == '' || $rank == COMMADMIN || $rank == HOOK_NETWORK)
        {
            $rank = CONTRIB;
        }

        
        if(!$error)
        {
            // Effectuer l'insertion
            $result = addUser($handle, $login, $email, $rank);
            
            if($result)
            {
                ManagerLog($handle, $user->id, $user->login, $user->pass, $user->ip, 'Ajout de l\'utilisateur ' . $login . ' - ' . $email . ' - ' . getRank($rank));
            }
            else
            {
                $error = true;
                $errorString = '- Erreur interne lors de la création';
            }
        }
        else
        {
            $result = false;
        }


    }     // fin méthode post
    else
    {
        $login = '';
        $email = '';
        $rank  = CONTRIB;

        $error = $result = false;
    }

    $users = & getUsers($handle);

?>
<div class="titreStandard">Liste des utilisateurs existants</div><br>
<div class="bg"><div class="commentaire">Sélectionnez dans la liste suivante l'utilisateur dont vous souhaitez modifier les informations personnelles et validez.
<br>Vous pourrez également effacer l'utilisateur depuis la page d'édition de ses informations.</div>
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
<br><br><div class="titreStandard">Ajout d'un nouveau compte utilisateur</div><br>
<div class="bg"><?php

    if(!$result)
    {

        if($error)
        {
            print('<font color="red">Une ou plusieurs erreurs sont survenues lors de la validation : <br>' . $errorString  . '</font><br><br>');
        }

?><form method="post" action="index.php?<?php print(session_name() . '=' . session_id()) ?>" class="formulaire">
<table><tr><td class="intitule" valign="top">Nom d'utilisateur :</td><td><input type="text" class="champstexte" name="login" value="<?php print(to_entities($login)) ?>" size="25" maxlength="15"> * <div class="commentaire">Le nom d'utilisateur doit comporter entre 6 et 15 caractères.</div></td></tr>
<tr><td class="intitule">Adresse e-mail : </td><td><input type="text" class="champstexte" name="email" value="<?php print(to_entities($email)) ?>" size="25" maxlength="50"> *</td></tr>
<tr><td class="intitule">Rang : </td><td class="intitule"><select name="rank"><?php

        print('<option value="' . $rank . '">' . getRank($rank) . '</option>');

        $ranks = & getRanks($rank);

        foreach($ranks as $k => $v)
        {
            if($k == COMMADMIN || $k == HOOK_NETWORK)
            {
                continue;
            }

            print('<option value="' . $k . '">' . $v . '</option>');
        }

?></select></td></tr></table>
<br><br><div class="commentaire">* signifie que le champ est obligatoire.</div><br>
<center><input type="button" class="bouton" value="Valider" onClick="this.form.submit(); this.disabled = true"> &nbsp; <input type="reset" value="Annuler" class="bouton"></center></form><?php

    }  // fin affichage formulaire si non validé
    else
    {
        print('<div class="confirm">Utilisateur ' . to_entities($login) . ' créé avec succès.</div>');
    }

?></div><?php

}  // fin autorisation

require(ADMIN . 'tail.php');

?>
