<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de cr�ation : 20 d�cembre 2004

 Mises � jour :

       2 juin 2005 : = r��criture avec optimisation

 Fichier : /includes/managerV2/users.php
 Description : Fonction de manipulation des utilisateurs

/=================================================================*/

require_once(ADMIN . 'generator.php');        // G�n�rer le mot de passe



/* V�rifier l'unicit� d'un champ
   i : r�f�rence handle connexion
   i : champ � tester
   i :r�f�rence valeur � tester
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
   o : r�f�rence nom du rang */
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
   i : identifiant rang � ignorer (optionnel)
   o : r�f�rence tableau de rangs */
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
   i : r�f�rence connexion sgbd
   i : filtre rang optionnel
   o : r�f�rence tableau de r�ponses */
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
   i : r�f�rence handle connexion
   i : r�f�rence login
   i : r�f�rence email
   i : rang
   o : true si ajout�, false si erreur */
function addUser(& $handle, & $login, & $email, $rank)
{
    $ret  = false;
    $pass = & generatePassword();

    if(($idUser = generateID(1, 65535, 'id', 'usersV2', $handle)) &&
       $handle->query('insert into usersV2 (id, login, pass, rank, email) values(\'' . $idUser . '\', \'' . $handle->escape($login) . '\', \'' . md5($pass) . '\', \'' . $handle->escape($rank) . '\', \'' . $handle->escape($email) . '\')'))
    {
        $ret = true;
        
        $subject = 'Techni Contact - Cr�ation de votre compte';

        $headers  = 'From: ' . SEND_MAIL_NAME . ' <' . SEND_MAIL . '>' . "\n";
        $headers .= 'MIME-Version: 1.0' . "\n";
        $headers .= 'Content-type: text/html' . "\n";

        $content  = 'Bonjour,<br><br>L\'administrateur du site web Techni-Contact vient de cr�er votre compte utilisateur. Pour vous connecter au manager rendez-vous � <a href="' . ADMIN_URL . '" target="_blank">l\'adresse suivante</a><br><br>Votre identifiant : ' . htmlentities($login) . '<br>';
        $content .= 'Votre mot de passe : ' . $pass . '<br><br>L\'adresse e-mail associ�e � ce compte est ' . htmlentities($email) . ', elle vous sera demand�e si vous oubliez votre mot de passe afin de vous en attribuer un nouveau.';

        mail($email, $subject, $content, $headers);
    }
    
    return $ret;
}



/* Charger les donn�es utilisateur
   i : r�f�rence handle connexion
   i : id utilisateur
   o : r�f�rence donn�es ou false si erreur */
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
   i : r�f�rence handle connexion
   o : true si ok false si erreur */
function delUser(& $handle, $id)
{
    $ret = false;

    if(($result = & $handle->query('select id from usersV2 where rank = \'' . COMMADMIN . '\'', __FILE__, __LINE__)) && $handle->numrows($result, __FILE__, __LINE__) == 1)
    {
        $row = & $handle->fetch($result);

        // Mise � jour id + sup
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



/* Mettre � jour les donn�es d'un utilisateur
   i : r�f�rence handle connexion
   i : id utilisateur
   i : r�f�rence nouveau login utilisateur
   i : r�f�rence nouvel email utilisateur
   i : nouveau rang utilisateur
   i : r�f�rence nouveau pass utilisateur */
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


/* V�rifier si commercial au sens large du terme
   i : r�f handle connexion
   i : id � tester
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
