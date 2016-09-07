<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 20 décembre 2004

 Mises à jour :

       2 juin 2005 : = réécriture avec optimisation

 Fichier : /includes/managerV2/users.php
 Description : Fonction de manipulation des utilisateurs

/=================================================================*/

require_once(ADMIN . 'generator.php');        // Générer le mot de passe



/* Vérifier l'unicité d'un champ
   i : référence handle connexion
   i : champ à tester
   i :référence valeur à tester
   o : true si unique false sinon */
function isUnique (& $handle, $field, & $value)
{
    $ret = false;

    if(($result = & $handle->query('select id from usersV2 where ' . $field . ' = \'' . $handle->escape($value) . '\'', __FILE__, __LINE__)) && $handle->numrows($result, __FILE__, __LINE__) == 0)
    {
        $ret = true;
    }

    return $ret;
}


/* Obtenir le nom d'un rang
   i : identifiant rang
   o : référence nom du rang */
function & getRank($id)
{
    switch($id)
    {
        case 0  : $rank = 'Contributeur';   break;
        case 1  : $rank = 'Commercial';     break;
        case 2  : $rank = 'Administrateur'; break;
        case 4  : $rank = 'Technicien';     break;
        default : $rank = '';
    }

    return $rank;
}


/* Obtenir les noms des rangs
   i : identifiant rang à ignorer (optionnel)
   o : référence tableau de rangs */
function & getRanks($id = 9)
{
    $ranks = array();
    
    if($id != 0)
    {
        $ranks[0] = 'Contributeur';
    }
    if($id != 1)
    {
        $ranks[1] = 'Commercial';
    }
    if($id != 2)
    {
        $ranks[2] = 'Administrateur';
    }
    if($id != 4)
    {
        $ranks[4] = 'Technicien';
    }

    return $ranks;
}



/* Obtenir la liste des utilisateurs du manager
   i : référence connexion sgbd
   i : filtre rang optionnel
   o : référence tableau de réponses */
function & getUsers(& $handle, $rank = -1)
{
    $ret = array();

    $rankQuery = ($rank != -1) ? 'where rank = \'' . $handle->escape($rank) . '\'' : '';

    if($result = & $handle->query('select id, login, rank from usersV2 ' . $rankQuery . ' order by login', __FILE__, __LINE__))
    {
        while($record = & $handle->fetch($result))
        {
            $ret[] = & $record;
        }

    }

    return $ret;

}



/* Ajouter un utilisateur
   i : référence handle connexion
   i : référence login
   i : référence email
   i : rang
   o : true si ajouté, false si erreur */
function addUser(& $handle, & $login, & $email, $rank)
{
    $ret  = false;
    $pass = & generatePassword();

    if(($idUser = generateID(1, 65535, 'id', 'usersV2', $handle)) &&
       $handle->query('insert into usersV2 (id, login, pass, rank, email) values(\'' . $idUser . '\', \'' . $handle->escape($login) . '\', \'' . md5($pass) . '\', \'' . $handle->escape($rank) . '\', \'' . $handle->escape($email) . '\')'))
    {
        $ret = true;
        
        $subject = 'Techni Contact - Création de votre compte';

        $headers  = 'From: ' . SEND_MAIL_NAME . ' <' . SEND_MAIL . '>' . "\n";
        $headers .= 'MIME-Version: 1.0' . "\n";
        $headers .= 'Content-type: text/html' . "\n";

        $content  = 'Bonjour,<br><br>L\'administrateur du site web Techni-Contact vient de créer votre compte utilisateur. Pour vous connecter au manager rendez-vous à <a href="' . ADMIN_URL . '" target="_blank">l\'adresse suivante</a><br><br>Votre identifiant : ' . htmlentities($login) . '<br>';
        $content .= 'Votre mot de passe : ' . $pass . '<br><br>L\'adresse e-mail associée à ce compte est ' . htmlentities($email) . ', elle vous sera demandée si vous oubliez votre mot de passe afin de vous en attribuer un nouveau.';

        mail($email, $subject, $content, $headers);
    }
    
    return $ret;
}



/* Charger les données utilisateur
   i : référence handle connexion
   i : id utilisateur
   o : référence données ou false si erreur */
function & loadUserData(& $handle, $id)
{
    $ret = false;

    if(($result = & $handle->query('select login, email, rank, pass from usersV2 where id = \'' . $handle->escape($id) . '\'', __FILE__, __LINE__)) && $handle->numrows($result, __FILE__, __LINE__) == 1)
    {
        $ret = & $handle->fetch($result);
    }

    return $ret;

}


/* Supprimer utilisateur
   i : référence handle connexion
   o : true si ok false si erreur */
function delUser(& $handle, $id)
{
    $ret = false;

    if(($result = & $handle->query('select id from usersV2 where rank = \'' . COMMADMIN . '\'', __FILE__, __LINE__)) && $handle->numrows($result, __FILE__, __LINE__) == 1)
    {
        $row = & $handle->fetch($result);

        // Mise à jour id + sup
        if($handle->query('update advertisers set idCommercial = \'' . $row[0] . '\' where idCommercial = \'' . $handle->escape($id) . '\'') &&
           $handle->query('delete from logs       where idUser = \'' . $handle->escape($id). '\'') &&
           $handle->query('delete from tempPassV2 where idUser = \'' . $handle->escape($id). '\'') &&
           $handle->query('delete from usersV2    where id = \'' . $handle->escape($id). '\' limit 1', __FILE__, __LINE__) &&
           $handle->affected() == 1)
        {
            $ret = true;
        }

    }

    return $ret;
}



/* Mettre à jour les données d'un utilisateur
   i : référence handle connexion
   i : id utilisateur
   i : référence nouveau login utilisateur
   i : référence nouvel email utilisateur
   i : nouveau rang utilisateur
   i : référence nouveau pass utilisateur */
function updateUser(& $handle, $id, & $login, & $email, $rank, & $pass)
{
    $ret = false;

    $passQuery = ($pass != '') ? ', pass = \'' . md5($pass) . '\'' : '';

    if($handle->query('update usersV2 set login = \'' . $handle->escape($login) . '\', email = \'' . $handle->escape($email) . '\', rank = \'' . $handle->escape($rank) . '\'' . $passQuery . ' where id = \'' . $handle->escape($id) . '\' limit 1', __FILE__, __LINE__) && $handle->affected() == 1)
    {
        $ret = true;
    }

    return $ret;

}


/* Vérifier si commercial au sens large du terme
   i : réf handle connexion
   i : id à tester
   o : true si commercial, false sinon */
function manageAdv(& $handle, $id)
{
    $ret = false;

    if(($result = & $handle->query('select id from usersV2 where (rank = ' . COMM . ' or rank = ' . COMMADMIN . ') and id = \'' . $handle->escape($id). '\'', __FILE__, __LINE__)) && $handle->numrows($result, __FILE__, __LINE__) == 1)
    {
        $ret = true;
    }

    return $ret;
}


?>
