<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 20 décembre 2004

 Mises à jour :

       2 juin 2005 : = réécriture avec nouveau gestionnaire du formulaire + affichage

 Fichier : /secure/manager/users/edit.php
 Description : Edition des paramètres d'un utilisateur

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
require(ADMIN . 'users.php');


$title  = 'Base de données des utilisateurs';
$navBar = '<a href="index.php?SESSION" class="navig">Base de données des utilisateurs</a> &raquo; Editer un utilisateur';
require(ADMIN . 'head.php');


if(!isset($_GET['id']) || !preg_match('/^[1-9][0-9]*$/', $_GET['id']) || $_GET['id'] == $user->id)
{
     // Utilisateur par défaut
    $id   = $user->id;
    $data = array($user->login, $user->email, $user->rank, $user->pass);
}
// Compte différent seulement pour admin ou tech
else if($user->rank == COMMADMIN || $user->rank == HOOK_NETWORK)
{
    $id   = $_GET['id'];
    $data = & loadUserData($handle, $_GET['id']);
}
else
{
    print('<div class="bg"><div class="fatalerror">Vous n\'avez pas les droits adéquats pour réaliser cette opération.</div></div>');
}

// Id invalide
if(!$data)
{
    print('<div class="bg"><div class="fatalerror">Identifiant utilisateur inconnu.</div></div>');
}
// Modif d'un admin / tech compte différent impossible
else if(($data[2] == COMMADMIN || $data[2] == HOOK_NETWORK) && $id != $user->id)
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
            $errorString .= '- Nouveau nom d\'utilisateur est incorrect<br>';
        }
        else if($login != $data[0] && !isUnique($handle, 'login', $login))
        {
            $error = true;
            $errorString .= '- Ce nom d\'utilisateur est déjà utilisé<br>';
        }

        
        $email = isset($_POST['email']) ? trim(substr($_POST['email'], 0, 50)) : '';
        $email = strtolower($email);

        if($email == '' || strlen($email) < 6 || !preg_match('`^[[:alnum:]]([-_.]?[[:alnum:]])*@[[:alnum:]]([-_.]?[[:alnum:]])*\.([a-z]{2,4})$`', $email))
        {
            $error = true;
            $errorString .= '- Nouvelle adresse email est incorrecte<br>';
        }
        else if($email != $data[1] && !isUnique($handle, 'email', $email))
        {
            $error = true;
            $errorString .= '- Cette adresse email est déjà utilisée<br>';
        }
        
        $rank = isset($_POST['rank']) ? $_POST['rank'] : CONTRIB;
        
        // Si valeur rang incorrecte, action de la part d'un contrib / commercial ou bien aciton sur un admin / technicien on ne change pas le rang !
        if(getRank($rank) == '' || ($user->rank != COMMADMIN && $user->rank != HOOK_NETWORK) || $data[2] == COMMADMIN || $data[2] == HOOK_NETWORK)
        {
            $rank = & $data[2];
        }
        
        
        if(isset($_POST['pass']) && isset($_POST['cpass']))
        {
            $pass  = trim($_POST['pass']);
            $cpass = trim($_POST['cpass']);
        }
        else
        {
            $pass = $cpass = '';
        }
        
        if($pass != '')
        {
            if($user->id != $id)
            {
                $error = true;
                $errorString .= '- Vous ne pouvez pas modifier le mot de passe associé à ce compte<br>';
            }
            else if(strlen($pass) < 8)
            {
                $error = true;
                $errorString .= '- Nouveau mot de passe doit comporter au moins 8 caractères<br>';
            }
            else if($pass != $cpass)
            {
                $error = true;
                $errorString .= '- Nouveau mot de passe et confirmation doivent être identiques<br>';
            }
        }
        
        
        $result = false; 

        if(!$error)
        {
            if($login != $data[0] || $email != $data[1] || $rank != $data[2] || ($pass != '' && $pass != $data[3]))
            {
                // Effectuer l'update
                $result = updateUser($handle, $id, $login, $email, $rank, $pass);
            
                if($result)
                {
                    ManagerLog($handle, $user->id, $user->login, $user->pass, $user->ip, 'Mise à jour des données utilisateur de ' . $login . ' - Anciennes données : ' .$data[0] . ' - ' . $data[1] . ' - ' . getRank($data[2]));
                }
                else
                {
                    $error = true;
                    $errorString = '- Erreur interne lors de la mise à jour';
                }
            }
            else
            {
                $result = true;
            }
        }


    }     // fin méthode post
    else
    {
        $login = & $data[0];
        $email = & $data[1];
        $rank  = & $data[2];

        $error = $result = false;
    }


    // Listing users pour admin et tecj
    if($user->rank == COMMADMIN || $user->rank == HOOK_NETWORK)
    {

?>
<div class="titreStandard">Liste des utilisateurs existants</div><br>
<div class="bg"><div class="commentaire">Sélectionnez dans la liste suivante l'utilisateur dont vous souhaitez modifier les informations personnelles et validez.
<br>Vous pourrez également effacer l'utilisateur depuis la page d'édition de ses informations.</div>
<br><br><form method="get" action="edit.php" class="formulaire"><select name="id"><?php

        $users = & getUsers($handle);

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
<?php

    }   // fin affichage liste utilisateurs
    

?>
<div class="titreStandard">Edition du compte utilisateur <?php

    if(!$error && $result)
    {
        print(to_entities($login));
    }
    else
    {
        print(to_entities($data[0]));
    }

    // Droit sup pour admin et tech sur un compte commercial ou contributeur
    if(($user->rank == COMMADMIN || $user->rank == HOOK_NETWORK) && $rank != COMMADMIN && $rank != HOOK_NETWORK)
    {
        print(' - <a href="del.php?id=' . $id . '&' . session_name() . '=' . session_id() . '" onClick="return confirm(\'Etes-vous sûr de vouloir supprimer cet utilisateur ? Les annonceurs de cet utilisateur seront alors rattachés au commercial administrateur !\')">Supprimer cet utilisateur</a>');
    }

?></div><br>
<div class="bg"><?php

    if(!$result)
    {

        if($error)
        {
            print('<font color="red">Une ou plusieurs erreurs sont survenues lors de la validation : <br>' . $errorString  . '</font><br><br>');
        }

?><form method="post" action="edit.php?id=<?php print($id . '&' . session_name() . '=' . session_id()) ?>" class="formulaire">
<table><tr><td class="intitule" valign="top">Nom d'utilisateur :</td><td><input type="text" class="champstexte" name="login" value="<?php print(to_entities($login)) ?>" size="25" maxlength="15"> * <div class="commentaire">Le nom d'utilisateur doit comporter entre 6 et 15 caractères.</div></td></tr>
<tr><td class="intitule">Adresse e-mail : </td><td><input type="text" class="champstexte" name="email" value="<?php print(to_entities($email)) ?>" size="25" maxlength="50"> *</td></tr>
<?php
        // Gestion du rang
        if($user->rank == COMMADMIN || $user->rank == HOOK_NETWORK)
        {
            // Impossible modifier rang admin ou tech
            if($rank == COMMADMIN || $rank == HOOK_NETWORK)
            {
                print('<tr><td class="intitule">Rang : </td><td class="intitule">' . getRank($rank) . '<input type="hidden" name="rank" value="' . $rank . '"></td></tr>');
            }
            else
            {

                print('<tr><td class="intitule">Rang : </td><td class="intitule"><select name="rank"><option value="' . $rank . '">' . getRank($rank) . '</option>');

                $ranks = & getRanks($rank);

                foreach($ranks as $k => $v)
                {
                    if($k == COMMADMIN || $k == HOOK_NETWORK)
                    {
                        continue;
                    }
                    
                    print('<option value="' . $k . '">' . $v . '</option>');
                }

                print('</select></td></tr>');
            }
        }

        // Gestion mot de passe
        if($user->id == $id)
        {

?><tr><td class="intitule">Nouveau mot de passe :</td><td><input type="password" size="25" class="champstexte" name="pass"></td></tr>
<tr><td class="intitule" valign="top">Confirmer votre nouveau mot de passe :</td><td><input type="password" size="25" class="champstexte" name="cpass"> <div class="commentaire">Votre nouveau mot de passe doit faire au minimum 8 caractères.</div></td></tr>
<?php
        }

?></table>
<br><br><div class="commentaire">
<?php
        if($user->id == $id)
        {
            print('Laissez les 2 derniers champs vides si vous ne souhaitez pas changer votre mot de passe.<br>Si vous modifiez votre login / mot de passe vous serez amené à vous identifier à nouveau.');
        }
        else
        {
            print('Seul le propriétaire du compte peut changer son mot de passe.');
        }

?><br>* signifie que le champ est obligatoire.</div><br>
<center><input type="button" class="bouton" value="Valider" onClick="this.form.submit(); this.disabled = true"> &nbsp; <input type="reset" value="Annuler" class="bouton"></center></form><?php

    }  // fin affichage formulaire si non validé
    else
    {
        print('<div class="confirm">Utilisateur ' . to_entities($login) . ' édité avec succès.</div>');
    }

?></div><?php

}  // fin autorisation

require(ADMIN . 'tail.php');

?>
