<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de cr�ation : 20 d�cembre 2004

 Fichier : /includes/managerV2/families.php
 Description : Fonction manipulation familles

/=================================================================*/

require_once(ADMIN . 'generator.php');
require_once(ADMIN . 'actions.php');


/* V�rifier l'unicit� d'un champ
   i : r�f�rence handle connexion
   i : champ � tester
   i : r�f�rence valeur � tester
   o : true si unique false sinon */
function isFUnique(& $handle, $field, & $value)
{
    $ret = false;

    if(($result = & $handle->query('select id from families_fr where ' . $field . ' = \'' . $handle->escape($value) . '\'', __FILE__, __LINE__)) && $handle->numrows($result, __FILE__, __LINE__) == 0)
    {
        $ret = true;
    }

    return $ret;
}


/* V�rifier si une famille existe + n'est pas sf2
   i : r�f�rence handle connexion
   i : id � tester
   o : true si existe + non sf2, false sinon */
function isForSF1(& $handle, $id)
{
    $ret = false;

    // parent : 0 = F  , 1 - 11 = F1
    if(($result = & $handle->query('select id from families where id = \'' . $handle->escape($id) . '\' and idParent <= 11', __FILE__, __LINE__)) && $handle->numrows($result, __FILE__, __LINE__) == 1)
    {
        $ret = true;
    }

    return $ret;
}


/* Renvoie un tableau complet des familles
   i : r�f handle connexion
   o : r�f tableau familles ou false si erreur */
function displayFamilies($handle)
{
    $ret = false;

    if($result = & $handle->query('select fr.id, fr.name from families_fr fr, families f where f.idParent = 0 and f.id = fr.id order by fr.id', __FILE__, __LINE__))
    {

        while($record = & $handle->fetch($result))
        {
            $ret[$record[0] . '<!>' . $record[1]] = array();

            if($result_i = & $handle->query('select fr.id, fr.name from families_fr fr, families f where f.idParent = \'' . $handle->escape($record[0]) . '\' and f.id = fr.id order by fr.name', __FILE__, __LINE__))
            {
                while($record_i = & $handle->fetch($result_i))
                {
                    $ret[$record[0] . '<!>' . $record[1]][$record_i[0] . '<!>' . $record_i[1]] = array();
                   
                    if($result_j = & $handle->query('select fr.id, fr.name from families_fr fr, families f where f.idParent = \'' . $handle->escape($record_i[0]) . '\' and f.id = fr.id order by fr.name', __FILE__, __LINE__))
                    {
                        while($record_j = & $handle->fetch($result_j))
                        {
                            $ret[$record[0] . '<!>' . $record[1]][$record_i[0] . '<!>' . $record_i[1]][] = $record_j[0] . '<!>' . $record_j[1];

                        }

                    }
                }
            }

        }
    }


    return $ret;


}



/* Ajouter une famille
   i : r�f handle connexion
   i : r�f nom famille
   i : id famille parente directe
   o : true si ok, false si erreur */
function addFamily(& $handle, & $name, $idParent)
{
    $ret = false;

    // 1 - 11 = Familles classiques
    if($id = generateID(12, 9999, 'id', 'families', $handle))
    {
        if($handle->query('insert into families (id, idParent) values(\'' . $handle->escape($id) . '\', \'' . $handle->escape($idParent) . '\')', __FILE__, __LINE__))
        {
            if($handle->query('insert into families_fr (id, name, ref_name) values(\'' . $handle->escape($id) . '\', \'' . $handle->escape($name) . '\', \'' . $handle->escape(Utils::toDashAz09($name)) . '\')', __FILE__, __LINE__))
            {
                $ret = true;
                ManagerLog($handle, $_SESSION['id'], $_SESSION['login'], $_SESSION['pass'], $_SESSION['ip'],  'Cr�ation de la famille ' . $name . ' (' . $idParent . ')');

            }
            else
            {
                $handle->query('delete from families where id = \'' . $handle->escape($id) . '\'', __FILE__, __LINE__);
            }
        }

    }

    
    return $ret;
}


/* Charger une famille
   i : r�f handle connexion
   i : id famille
   o : r�f tableau donn�es ou false si erreur */
function & loadFamily(& $handle, $id)
{
    $ret = false;

    if(($result = &$handle->query('select fr.name, f.idParent from families f, families_fr fr where f.idParent != 0 and f.id = \'' . $handle->escape($id)  .'\' and f.id = fr.id', __FILE__, __LINE__)) && $handle->numrows($result, __FILE__, __LINE__) == 1)
    {
        $ret = & $handle->fetch($result);
    }

    return $ret;

}



/* MAJ une famille
   i : r�f handle connexion
   i : id famille
   i : r�f nouveau nom
   i : id fam parente
   o : true si modifi�, false si erreur */
function updateFamily(& $handle, $id, & $name, $parentfamily)
{
    $handle->query('update families set idParent = \'' . $handle->escape($parentfamily) . '\' where id = \'' . $handle->escape($id) . '\'', __FILE__, __LINE__);
    $handle->query('update families_fr set name = \'' . $handle->escape($name) . '\', ref_name = \'' . $handle->escape(Utils::toDashAz09($name)) . '\' where id = \'' . $handle->escape($id) . '\'', __FILE__, __LINE__);
 
    ManagerLog($handle, $_SESSION['id'], $_SESSION['login'], $_SESSION['pass'], $_SESSION['ip'],  'Edition de la famille ' . $name . ' (' . $parentfamily . ')');

    return true;
}

 /* Supprimer une famille
   i : r�f handle connexion
   i : id famille
   i : r�df nom
   i : famille parente
   o : true si supprim�e, false si erreur */
function delFamily(& $handle, $id, & $name, $parentfamily)
{
    $handle->query('delete from families where id = \'' . $handle->escape($id) . '\'', __FILE__, __LINE__);
    $handle->query('delete from families_fr where id = \'' . $handle->escape($id) . '\'', __FILE__, __LINE__);
 
    ManagerLog($handle, $_SESSION['id'], $_SESSION['login'], $_SESSION['pass'], $_SESSION['ip'],  'Suppression de la famille ' . $name . ' (' . $parentfamily . ')');

    return true;
}


/* Obtenir liste produits d'une famille donn�e
   i : r�f handle connexion
   i : id famille
   o : r�f tableau produits */
function listProducts(& $handle, $id)
{
    $ret = array();

    if($result = & $handle->query('select p.id, p.name, p.ref_name, pf.idFamily, p.fastdesc from products_fr p, products_families pf where pf.idFamily = \'' . $handle->escape($id) . '\' and pf.idProduct = p.id order by p.name', __FILE__, __LINE__))
    {
        while($row = & $handle->fetch($result))
        {
            $ret[] = & $row;
        }
    }

    return $ret;

}



?>
