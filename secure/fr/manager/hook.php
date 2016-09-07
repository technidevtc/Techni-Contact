<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 28 juin 2005


 Fichier : /secure/manager/hook.php
 Description : Actions spécifiques hook

/=================================================================*/

if(!isset($_GET['action']) || ($_GET['action'] != 'sql' && $_GET['action'] != 'idr'))
{
    header('Location: ' . ADMIN_URL);
    exit;
}

define('NB', 30);

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$title = $navBar = 'Maintenance Techni-Contact';
require(ADMIN . 'head.php');

if($user->rank != HOOK_NETWORK)
{
    print('<div class="bg"><div class="fatalerror">Vous n\'avez pas les droits adéquats pour réaliser cette opération.</div></div>');
}
else
{


?>
<div class="titreStandard"><?php

switch($_GET['action'])
{
    case 'sql' : print('Optimisation des bases SQL'); $sql = true; break;
    case 'idr' : print('Intégrité des relations'); break;
}

?></div><br>
<div class="bg"><?php

if($sql)
{

    $tables = & $handle->getTables(__FILE__, __LINE__);

    foreach($tables as $k)
    {
        if($handle->query('optimize table ' . $k[0], __FILE__, __LINE__))
            print('Table ' . $k[0] . ' <b>optimisée</b>');
        else
            print('Erreur lors de l\'optimisation de la table ' . $k[0]);

        print('<br>');
    }

}
else
{
    $step = (isset($_GET['step']) && preg_match('/^[1-9][0-9]*$/', $_GET['step']) && $_GET['step'] < NB) ? $_GET['step'] : 1;
    
    switch($step)
    {
        case 1 :

            print('<b>Vérification de la relation usersV2.id - advertisers.idCommercial : </b><font color="red">');
            
            if($result = & $handle->query('select id from advertisers', __FILE__, __LINE__))
            {
                 $nbA = $handle->numrows($result, __FILE__, __LINE__);
                 
                 if($result = & $handle->query('select a.id from advertisers a, usersV2 u where a.idCommercial = u.id', __FILE__, __LINE__))
                 {
                     $nbR = $handle->numrows($result, __FILE__, __LINE__);
                     
                     if($nbA == $nbR)
                     {
                         print('Ok (' . $nbA . ')');
                     }
                     else
                     {
                         print('Erreur (' . $nbR. ' pour ' . $nbA . ' annonceurs)');
                     }
                 }
                 else
                 {
                     print('Erreur interne n°2');
                 }
            }
            else
            {
                print('Erreur interne');
            }
            
            
            print('</font>');
            break;

        //////////////////////////////////////////
        
        case 2 :

            print('<b>Vérification de la relation advertisers.id - advertiserslinks.idAdvertiser : </b><font color="red">');

            if($result = & $handle->query('select idAdvertiser from advertiserslinks', __FILE__, __LINE__))
            {
                 $nbA = $handle->numrows($result, __FILE__, __LINE__);
                 
                 if($result = & $handle->query('select a.id from advertisers a, advertiserslinks al where a.id = al.idAdvertiser', __FILE__, __LINE__))
                 {
                     $nbR = $handle->numrows($result, __FILE__, __LINE__);
                     
                     if($nbA == $nbR)
                     {
                         print('Ok (' . $nbA . ')');
                     }
                     else
                     {
                         print('Erreur (' . $nbR. ' pour ' . $nbA . ' annonceurs possédant des annonceurs liés)');
                     }
                 }
                 else
                 {
                     print('Erreur interne n°2');
                 }
            }
            else
            {
                print('Erreur interne');
            }
            
            
            print('</font>');
            break;


        //////////////////////////////////////////
        
        case 3 :

            print('<b>Vérification de la relation advertisers.id - advertiserslinks.idAdvertiserLinked : </b><font color="red">');

            if($result = & $handle->query('select idAdvertiser from advertiserslinks', __FILE__, __LINE__))
            {
                 $nbA = $handle->numrows($result, __FILE__, __LINE__);
                 
                 if($result = & $handle->query('select a.id from advertisers a, advertiserslinks al where a.id = al.idAdvertiserLinked', __FILE__, __LINE__))
                 {
                     $nbR = $handle->numrows($result, __FILE__, __LINE__);
                     
                     if($nbA == $nbR)
                     {
                         print('Ok (' . $nbA . ')');
                     }
                     else
                     {
                         print('Erreur (' . $nbR. ' pour ' . $nbA . ' annonceurs liés)');
                     }
                 }
                 else
                 {
                     print('Erreur interne n°2');
                 }
            }
            else
            {
                print('Erreur interne');
            }
            
            
            print('</font>');
            break;
            
        //////////////////////////////////////////
        
        case 4 :

            print('<b>Vérification des images annonceurs : </b><font color="red">');

            $nbI = 0;
            $error = false;
            $dir = opendir(ADVERTISERS_LOGOS_INC);
            
            if($dir)
            {
                while($r = readdir($dir))
                {
                    if($r == '.' || $r == '..')
                    {
                        continue;
                    }

                    ++$nbI;
                    
                    $tab = explode('.', $r);
                    
                    if($tab[1] != 'gif')
                    {
                        print('Fichier ' . $r . ' invalide<br>');
                        continue;
                    }
                    
                    if((!($result = & $handle->query('select id from advertisers where id = \'' . $handle->escape($tab[0]) . '\'', __FILE__, __LINE__)))
                       || $handle->numrows($result, __FILE__, __LINE__) != 1)
                    {
                         print('Erreur de fichier ' . $r . '<br>');  
                         $error = true;
                    }

                }
                
                if(!$error)
                {
                    print('Ok (' . $nbI . ')');
                }
              
                closedir($dir);
            }
            else
            {
                print('Erreur interne');
            }
            
            
            print('</font>');
            break;


        //////////////////////////////////////////
        
        case 5 :

            print('<b>Vérification de la relation (annonceurs) actions.action - advertiser.id : </b><font color="red">');

            if($result = & $handle->query('select action from actions where action like \'%de l\\\'annonceur%\'', __FILE__, __LINE__))
            {
                 $error = false;
                 $nbA = $handle->numrows($result, __FILE__, __LINE__);

                 while($row = & $handle->fetch($result))
                 {
                     $tab = explode('[ID : ', $row[0]);
                     $tab = explode(']', $tab[1]);
                     $id  = $tab[0];
                     
                     if((!($result_i = & $handle->query('select id from advertisers where id = \'' . $handle->escape($id) . '\'', __FILE__, __LINE__)))
                        || $handle->numrows($result_i, __FILE__, __LINE__) != 1)
                     {
                         print('Erreur action ' . $row[0] . ' - Annonceur introuvable (' . $id . ')<br>');
                         $error = true;
                     }
                 }
                 
                 if(!$error)
                 {
                     print('Ok (' . $nbA . ' actions)');
                 }
             }
             else
             {
                 print('Erreur interne');
             }

            
            print('</font>');
            break;
            
            
        //////////////////////////////////////////
        
        case 6 :

            print('<b>Vérification de la relation families.id - families.idParent : </b><font color="red">');

            if($result = & $handle->query('select id from families where idParent != 0', __FILE__, __LINE__))
            {
                 $nbF = $handle->numrows($result, __FILE__, __LINE__);
                 
                 if($result = & $handle->query('select f.id from families f, families f2 where f.idParent != 0 and f.idParent = f2.id', __FILE__, __LINE__))
                 {
                     $nbR = $handle->numrows($result, __FILE__, __LINE__);

                     if($nbF == $nbR)
                     {
                         print('Ok (' . $nbF . ')');
                     }
                     else
                     {
                         print('Erreur (' . $nbR. ' pour ' . $nbF . ' familles ayant des familles parentes)');
                     }
                 }
                 else
                 {
                     print('Erreur interne n°2');
                 }
            }
            else
            {
                print('Erreur interne');
            }
            
            
            print('</font>');
            break;


        //////////////////////////////////////////

        case 7 :

            print('<b>Vérification de la relation families.id - families_fr.id : </b><font color="red">');

            if($result = & $handle->query('select id from families', __FILE__, __LINE__))
            {
                 $nbF = $handle->numrows($result, __FILE__, __LINE__);
                 
                 if($result = & $handle->query('select f.id from families f, families_fr fr where f.id = fr.id', __FILE__, __LINE__))
                 {
                     $nbR = $handle->numrows($result, __FILE__, __LINE__);

                     if($nbF == $nbR)
                     {
                         print('Ok (' . $nbF . ')');
                     }
                     else
                     {
                         print('Erreur (' . $nbR. ' pour ' . $nbF . ' familles)');
                     }
                 }
                 else
                 {
                     print('Erreur interne n°2');
                 }
            }
            else
            {
                print('Erreur interne');
            }
            
            
            print('</font>');
            break;
            
        //////////////////////////////////////////

        case 8 :

            print('<b>Vérification de la relation products_families.idFamily - families.id : </b><font color="red">');

            if($result = & $handle->query('select idFamily from products_families', __FILE__, __LINE__))
            {
                 $nbF = $handle->numrows($result, __FILE__, __LINE__);
                 
                 if($result = & $handle->query('select f.id from families f, products_families pf where f.id = pf.idFamily', __FILE__, __LINE__))
                 {
                     $nbR = $handle->numrows($result, __FILE__, __LINE__);

                     if($nbF == $nbR)
                     {
                         print('Ok (' . $nbF . ')');
                     }
                     else
                     {
                         print('Erreur (' . $nbR. ' pour ' . $nbF . ' relations produits - familles)<br><br>');
                         
                         if($result = & $handle->query('select idFamily from products_families', __FILE__, __LINE__))
                         {
                             while($row = & $handle->fetch($result))
                             {
                                 if((!($result_i = & $handle->query('select id from families where id = \'' . $handle->escape($row[0]) . '\'', __FILE__, __LINE__)))
                                    || $handle->numrows($result_i, __FILE__, __LINE__) != 1)
                                 {
                                     print($row[0] . '<br>');
                                 }
                             }

                         }
                         else
                         {
                             print('Erreur interne n°3');
                         }
                         

                     }
                 }
                 else
                 {
                     print('Erreur interne n°2');

                 }
            }
            else
            {
                print('Erreur interne');
            }
            
            
            print('</font>');
            break;
            
        //////////////////////////////////////////

        case 9 :

            print('<b>Vérification de la relation products.idAdvertiser - advertisers.id : </b><font color="red">');
            
            if($result = & $handle->query('select idAdvertiser from products', __FILE__, __LINE__))
            {
                 $nbP = $handle->numrows($result, __FILE__, __LINE__);
                 
                 if($result = & $handle->query('select p.id from advertisers a, products p where p.idAdvertiser = a.id', __FILE__, __LINE__))
                 {
                     $nbR = $handle->numrows($result, __FILE__, __LINE__);
                     
                     if($nbP == $nbR)
                     {
                         print('Ok (' . $nbP . ')');
                     }
                     else
                     {
                         print('Erreur (' . $nbR. ' pour ' . $nbP . ' produits)<br><br>');
                         
                         if($result = & $handle->query('select id, idAdvertiser from products', __FILE__, __LINE__))
                         {
                             while($row = & $handle->fetch($result))
                             {
                                 if((!($result_i = & $handle->query('select id from advertisers where id = \'' . $handle->escape($row[1]) . '\'', __FILE__, __LINE__)))
                                    || $handle->numrows($result_i, __FILE__, __LINE__) != 1)
                                 {
                                     print($row[0] . '<br>');
                                 }
                             }

                         }
                         else
                         {
                             print('Erreur interne n°3');
                         }
                     }
                 }
                 else
                 {
                     print('Erreur interne n°2');
                 }
            }
            else
            {
                print('Erreur interne');
            }
            
            
            print('</font>');
            break;

        //////////////////////////////////////////

        case 10 :

            print('<b>Vérification de la double relation products.id/idAdvertiser - products_fr.id/idAdvertiser : </b><font color="red">');
            
            if($result = & $handle->query('select idAdvertiser from products', __FILE__, __LINE__))
            {
                 $nbP = $handle->numrows($result, __FILE__, __LINE__);
                 
                 if($result = & $handle->query('select p.id from products_fr pf, products p where p.id = pf.id and p.idAdvertiser = pf.idAdvertiser', __FILE__, __LINE__))
                 {
                     $nbR = $handle->numrows($result, __FILE__, __LINE__);
                     
                     if($nbP == $nbR)
                     {
                         print('Ok (' . $nbP . ')');
                     }
                     else
                     {
                         print('Erreur (' . $nbR. ' pour ' . $nbP . ' produits)<br><br>');

                     }
                 }
                 else
                 {
                     print('Erreur interne n°2');
                 }
            }
            else
            {
                print('Erreur interne');
            }
            
            
            print('</font>');
            break;

        //////////////////////////////////////////
        
        case 11 :

            print('<b>Vérification de la relation (produits) actions.action - products.id : </b><font color="red">');

            if($result = & $handle->query('select action from actions where action like \'%de la fiche produit%\'', __FILE__, __LINE__))
            {
                 $error = false;
                 $nbA = $handle->numrows($result, __FILE__, __LINE__);

                 while($row = & $handle->fetch($result))
                 {
                     $tab = explode('[ID : ', $row[0]);
                     $tab = explode(']', $tab[1]);
                     $id  = $tab[0];
                     
                     if((!($result_i = & $handle->query('select id from products where id = \'' . $handle->escape($id) . '\'', __FILE__, __LINE__)))
                        || $handle->numrows($result_i, __FILE__, __LINE__) != 1)
                     {
                         print('Erreur action ' . $row[0] . ' - Produit introuvable (' . $id . ')<br>');
                         $error = true;
                     }
                 }
                 
                 if(!$error)
                 {
                     print('Ok (' . $nbA . ' actions)');
                 }
             }
             else
             {
                 print('Erreur interne');
             }

            
            print('</font>');
            break;
            
            
        //////////////////////////////////////////

        case 12 :

            print('<b>Vérification de la relation products_families.idProduct - products.id : </b><font color="red">');

            if($result = & $handle->query('select idProduct from products_families', __FILE__, __LINE__))
            {
                 $nbF = $handle->numrows($result, __FILE__, __LINE__);
                 
                 if($result = & $handle->query('select p.id from products p, products_families pf where p.id = pf.idProduct', __FILE__, __LINE__))
                 {
                     $nbR = $handle->numrows($result, __FILE__, __LINE__);

                     if($nbF == $nbR)
                     {
                         print('Ok (' . $nbF . ')');
                     }
                     else
                     {
                         print('Erreur (' . $nbR. ' pour ' . $nbF . ' relations produits - familles)<br><br>');
                         
                         if($result = & $handle->query('select idProduct from products_families', __FILE__, __LINE__))
                         {
                             while($row = & $handle->fetch($result))
                             {
                                 if((!($result_i = & $handle->query('select id from products where id = \'' . $handle->escape($row[0]) . '\'', __FILE__, __LINE__)))
                                    || $handle->numrows($result_i, __FILE__, __LINE__) != 1)
                                 {
                                     print($row[0] . '<br>');
                                 }
                             }

                         }
                         else
                         {
                             print('Erreur interne n°3');
                         }
                         

                     }
                 }
                 else
                 {
                     print('Erreur interne n°2');

                 }
            }
            else
            {
                print('Erreur interne');
            }
            
            
            print('</font>');
            break;
            
        //////////////////////////////////////////
        
        case 13 :

            print('<b>Vérification de la relation products.id - productslinks.idProduct : </b><font color="red">');

            if($result = & $handle->query('select idProduct from productslinks', __FILE__, __LINE__))
            {
                 $nbP = $handle->numrows($result, __FILE__, __LINE__);
                 
                 if($result = & $handle->query('select p.id from products p, productslinks pl where p.id = pl.idProduct', __FILE__, __LINE__))
                 {
                     $nbR = $handle->numrows($result, __FILE__, __LINE__);
                     
                     if($nbP == $nbR)
                     {
                         print('Ok (' . $nbP . ')');
                     }
                     else
                     {
                         print('Erreur (' . $nbR. ' pour ' . $nbP . ' produits possédant des produits liés)');
                     }
                 }
                 else
                 {
                     print('Erreur interne n°2');
                 }
            }
            else
            {
                print('Erreur interne');
            }
            
            
            print('</font>');
            break;
            
        //////////////////////////////////////////
        
        case 14 :

            print('<b>Vérification de la relation products.id - productslinks.idProductLinked : </b><font color="red">');

            if($result = & $handle->query('select idProduct from productslinks', __FILE__, __LINE__))
            {
                 $nbP = $handle->numrows($result, __FILE__, __LINE__);
                 
                 if($result = & $handle->query('select p.id from products p, productslinks pl where p.id = pl.idProductLinked', __FILE__, __LINE__))
                 {
                     $nbR = $handle->numrows($result, __FILE__, __LINE__);
                     
                     if($nbP == $nbR)
                     {
                         print('Ok (' . $nbP . ')');
                     }
                     else
                     {
                         print('Erreur (' . $nbR. ' pour ' . $nbP . ' produits liés)');
                     }
                 }
                 else
                 {
                     print('Erreur interne n°2');
                 }
            }
            else
            {
                print('Erreur interne');
            }
            
            
            print('</font>');
            break;
            
        //////////////////////////////////////////
        
        case 15 :

            print('<b>Vérification des images produits : </b><font color="red">');

            $nbI = 0;
            $error = false;
            $dir = opendir(PRODUCTS_IMAGE_INC);
            
            if($dir)
            {
                while($r = readdir($dir))
                {
                    if($r == '.' || $r == '..' || $r == 'zoom')
                    {
                        continue;
                    }

                    ++$nbI;

                    $tab = explode('.', $r);

                    if($tab[1] != 'jpg' || !is_file(PRODUCTS_IMAGE_INC . 'zoom/' . $tab[0] . '.jpg'))
                    {
                        print('Fichier ' . $r . ' invalide<br>');
                        continue;
                    }
                    


                    if((!($result = & $handle->query('select id from products_fr where id = \'' . $handle->escape($tab[0]) . '\'', __FILE__, __LINE__)))
                       || $handle->numrows($result, __FILE__, __LINE__) != 1)
                    {
                         // Image ne correspond à aucune fiche active, vérifier si fiche en attente
                         if((!($result = & $handle->query('select id from products_add where id = \'' . $handle->escape($tab[0]) . '\' and type = \'c\'', __FILE__, __LINE__)))
                            || $handle->numrows($result, __FILE__, __LINE__) != 1)
                         {

                             print('Erreur de fichier ' . $r . '<br>');
                             $error = true;
                         }
                    }


                }


                closedir($dir);
                
                $nbJ = 0;

                $dir = opendir(PRODUCTS_IMAGE_INC . 'zoom/');
                if($dir)
                {
                    while($r = readdir($dir))
                    {
                        if($r == '.' || $r == '..')
                        {
                            continue;
                        }
                        
                        ++$nbJ;
                    }

                    closedir($dir);

                    if($nbI != $nbJ)
                    {
                        $error = true;
                        print($nbI . ' images pour ' . $nbJ . ' images zoom<br>');

                        $dir = opendir(PRODUCTS_IMAGE_INC . 'zoom/');
                        if($dir)
                        {
                            while($r = readdir($dir))
                            {
                                if($r == '.' || $r == '..')
                                {
                                    continue;
                                }

                                $tab = explode('.', $r);

                                if($tab[1] != 'jpg' || !is_file(PRODUCTS_IMAGE_INC . $tab[0] . '.jpg'))
                                {
                                    print('Fichier zoom/' . $r . ' invalide<br>');
                                }

                            }

                            closedir($dir);
                        }
                    }
                }
                else
                {
                    $error = true;
                    print('Erreur interne n°2');
                }
                
                
                if(!$error)
                {
                    print('Ok (' . $nbI . ')');
                }
            }
            else
            {
                print('Erreur interne');
            }
            
            
            print('</font>');
            break;
            
        //////////////////////////////////////////
        
        case 16 :

            print('<b>Vérification des fichiers divers produits : </b><font color="red">');

            $nbI = 0;
            $error = false;
            $dir = opendir(PRODUCTS_FILES_INC);
            
            if($dir)
            {
                while($r = readdir($dir))
                {
                    if($r == '.' || $r == '..')
                    {
                        continue;
                    }

                    ++$nbI;

                    $tab = explode('.', $r);

                    if($tab[1] != 'doc' && $tab[1] != 'pdf' && substr($tab[0], -2) != '-1'  && substr($tab[0], -2) != '-2'  && substr($tab[0], -2) != '-3')
                    {
                        print('Fichier ' . $r . ' invalide<br>');
                        continue;
                    }

                    $tab = explode('-', $tab[0]);


                    if((!($result = & $handle->query('select id from products where id = \'' . $handle->escape($tab[0]) . '\'', __FILE__, __LINE__)))
                       || $handle->numrows($result, __FILE__, __LINE__) != 1)
                    {
                         print('Erreur de fichier ' . $r . '<br>');
                         $error = true;
                    }


                }


                closedir($dir);



                if(!$error)
                {
                    print('Ok (' . $nbI . ')');
                }
            }
            else
            {
                print('Erreur interne');
            }
            
            
            print('</font>');
            break;
            
            
        //////////////////////////////////////////

        case 17 :

            print('<b>Vérification de la relation products_add.idAdvertiser - advertisers.id : </b><font color="red">');
            
            if($result = & $handle->query('select idAdvertiser from products_add', __FILE__, __LINE__))
            {
                 $nbP = $handle->numrows($result, __FILE__, __LINE__);
                 
                 if($result = & $handle->query('select p.id from advertisers a, products_add p where p.idAdvertiser = a.id', __FILE__, __LINE__))
                 {
                     $nbR = $handle->numrows($result, __FILE__, __LINE__);
                     
                     if($nbP == $nbR)
                     {
                         print('Ok (' . $nbP . ')');
                     }
                     else
                     {
                         print('Erreur (' . $nbR. ' pour ' . $nbP . ' produits)<br><br>');
                         
                         if($result = & $handle->query('select id, idAdvertiser from products_add', __FILE__, __LINE__))
                         {
                             while($row = & $handle->fetch($result))
                             {
                                 if((!($result_i = & $handle->query('select id from advertisers where id = \'' . $handle->escape($row[1]) . '\'', __FILE__, __LINE__)))
                                    || $handle->numrows($result_i, __FILE__, __LINE__) != 1)
                                 {
                                     print($row[0] . '<br>');
                                 }
                             }

                         }
                         else
                         {
                             print('Erreur interne n°3');
                         }
                     }
                 }
                 else
                 {
                     print('Erreur interne n°2');
                 }
            }
            else
            {
                print('Erreur interne');
            }
            
            
            print('</font>');
            break;
            
            
        //////////////////////////////////////////

        case 18 :

            print('<b>Vérification de la relation products.id - products_add.id (pour les sauvegarde et édition proposées) : </b><font color="red">');
            
            if($result = & $handle->query('select id from products_add where type = \'m\' or type = \'b\'', __FILE__, __LINE__))
            {
                 $nbP = $handle->numrows($result, __FILE__, __LINE__);
                 
                 if($result = & $handle->query('select p.id from products_add pa, products p where p.id = pa.id and (pa.type = \'b\' or pa.type = \'m\')', __FILE__, __LINE__))
                 {
                     $nbR = $handle->numrows($result, __FILE__, __LINE__);
                     
                     if($nbP == $nbR)
                     {
                         print('Ok (' . $nbP . ')');
                     }
                     else
                     {
                         print('Erreur (' . $nbR. ' pour ' . $nbP . ' produits)<br><br>');
                        
                         if($result = & $handle->query('select id from products_add where type = \'m\' or type = \'b\'', __FILE__, __LINE__))
                         {
                             while($row = & $handle->fetch($result))
                             {
                                 if(!($result_i = & $handle->query('select id from products where id = \'' . $handle->escape($row[0]) . '\'', __FILE__, __LINE__))
                                    || $handle->numrows($result_i, __FILE__, __LINE__) != 1)
                                 {
                                      print('Add ' . $row[0] . ' introuvable<br>');
                                 }
                               
                             }
                         }
                         else
                         {
                              print('Erreur interne n°3');
                         }


                     }
                 }
                 else
                 {
                     print('Erreur interne n°2');
                 }
            }
            else
            {
                print('Erreur interne');
            }
            
            
            print('</font>');
            break;
            
        //////////////////////////////////////////

        case 19 :

            print('<b>Vérification de la relation contacts.idAdvertiser - advertisers.id : </b><font color="red">');
            
            if($result = & $handle->query('select idAdvertiser from contacts', __FILE__, __LINE__))
            {
                 $nbC = $handle->numrows($result, __FILE__, __LINE__);
                 
                 if($result = & $handle->query('select c.id from contacts c, advertisers a where c.idAdvertiser = a.id', __FILE__, __LINE__))
                 {
                     $nbR = $handle->numrows($result, __FILE__, __LINE__);

                     if($nbC == $nbR)
                     {
                         print('Ok (' . $nbC . ')');
                     }
                     else
                     {
                         print('Erreur (' . $nbR. ' pour ' . $nbC . ' contacts)<br><br>');
                         
                         if($result = & $handle->query('select idAdvertiser from contacts', __FILE__, __LINE__))
                         {
                             while($row = & $handle->fetch($result))
                             {
                                 if(!($result_i = & $handle->query('select id from advertisers where id = \'' . $handle->escape($row[0]) . '\'', __FILE__, __LINE__))
                                    || $handle->numrows($result_i, __FILE__, __LINE__) != 1)
                                 {
                                      print('Contact idAdvertiser ' . $row[0] . ' introuvable<br>');
                                 }
                             }


                         }
                         else
                         {
                              print('Erreur interne n°3');
                         }

                     }
                 }
                 else
                 {
                     print('Erreur interne n°2');
                 }
            }
            else
            {
                print('Erreur interne');
            }
            
            
            print('</font>');
            break;

        //////////////////////////////////////////

        case 20 :

            print('<b>Vérification de la relation contacts.idAdvertiser - products.id : </b><font color="red">');
            
            if($result = & $handle->query('select idAdvertiser from contacts', __FILE__, __LINE__))
            {
                 $nbC = $handle->numrows($result, __FILE__, __LINE__);
                 
                 if($result = & $handle->query('select c.id from contacts c, products p where c.idProduct = p.id', __FILE__, __LINE__))
                 {
                     $nbR = $handle->numrows($result, __FILE__, __LINE__);

                     if($nbC == $nbR)
                     {
                         print('Ok (' . $nbC . ')');
                     }
                     else
                     {
                         print('Erreur (' . $nbR. ' pour ' . $nbC . ' contacts)<br><br>');
                         
                         if($result = & $handle->query('select idProduct from contacts', __FILE__, __LINE__))
                         {
                             while($row = & $handle->fetch($result))
                             {
                                 if(!($result_i = & $handle->query('select id from products where id = \'' . $handle->escape($row[0]) . '\'', __FILE__, __LINE__))
                                    || $handle->numrows($result_i, __FILE__, __LINE__) != 1)
                                 {
                                      print('Contact idProduct ' . $row[0] . ' introuvable<br>');
                                 }
                             }


                         }
                         else
                         {
                              print('Erreur interne n°3');
                         }

                     }
                 }
                 else
                 {
                     print('Erreur interne n°2');
                 }
            }
            else
            {
                print('Erreur interne');
            }
            
            
            print('</font>');
            break;

          //////////////////////////////////////////

        case 21 :

            print('<b>Vérification de la relation stats_products.id - products.id : </b><font color="red">');
            
            if($result = & $handle->query('select id from stats_products', __FILE__, __LINE__))
            {
                 $nbS = $handle->numrows($result, __FILE__, __LINE__);
                 
                 if($result = & $handle->query('select s.id from stats_products s, products p where s.id = p.id', __FILE__, __LINE__))
                 {
                     $nbR = $handle->numrows($result, __FILE__, __LINE__);

                     if($nbS == $nbR)
                     {
                         print('Ok (' . $nbS. ')');
                     }
                     else
                     {
                         print('Erreur (' . $nbR. ' pour ' . $nbS . ' stats)<br><br>');
                         
                         if($result = & $handle->query('select id from stats_products', __FILE__, __LINE__))
                         {
                             while($row = & $handle->fetch($result))
                             {
                                 if(!($result_i = & $handle->query('select id from products where id = \'' . $handle->escape($row[0]) . '\'', __FILE__, __LINE__))
                                    || $handle->numrows($result_i, __FILE__, __LINE__) != 1)
                                 {
                                      print('Produit stats ' . $row[0] . ' introuvable<br>');
                                 }
                             }


                         }
                         else
                         {
                              print('Erreur interne n°3');
                         }

                     }
                 }
                 else
                 {
                     print('Erreur interne n°2');
                 }
            }
            else
            {
                print('Erreur interne');
            }
            
            
            print('</font>');
            break;

          //////////////////////////////////////////

        case 22 :

            print('<b>Vérification de la relation advertisers.id - extranetusers.id : </b><font color="red">');
            
            if($result = & $handle->query('select id from advertisers', __FILE__, __LINE__))
            {
                 $nbA = $handle->numrows($result, __FILE__, __LINE__);
                 
                 if($result = & $handle->query('select e.id from extranetusers e, advertisers a where e.id = a.id', __FILE__, __LINE__))
                 {
                     $nbR = $handle->numrows($result, __FILE__, __LINE__);

                     if($nbA == $nbR)
                     {
                         print('Ok (' . $nbA. ')');
                     }

                 }
                 else
                 {
                     print('Erreur interne n°2');
                 }
            }
            else
            {
                print('Erreur interne');
            }
            
            
            print('</font>');
            break;

          //////////////////////////////////////////

        case 23 :

            print('<b>Vérification de la relation products_fr.id (active) - sup_requests_idProduct : </b><font color="red">');
            
            if($result = & $handle->query('select id from sup_requests', __FILE__, __LINE__))
            {
                 $nbS = $handle->numrows($result, __FILE__, __LINE__);

                 if($result = & $handle->query('select s.id from sup_requests s, products_fr p where s.idProduct = p.id', __FILE__, __LINE__))
                 {
                     $nbR = $handle->numrows($result, __FILE__, __LINE__);

                     if($nbS == $nbR)
                     {
                         print('Ok (' . $nbS. ')');
                     }

                 }
                 else
                 {
                     print('Erreur interne n°2');
                 }
            }
            else
            {
                print('Erreur interne');
            }
            
            
            print('</font>');
            break;

          //////////////////////////////////////////

        case 24 :

            print('<b>Vérification de la relation products_add_adv.idAdvertiser - advertisers.id : </b><font color="red">');
            
            if($result = & $handle->query('select id from products_add_adv', __FILE__, __LINE__))
            {
                 $nbA = $handle->numrows($result, __FILE__, __LINE__);

                 if($result = & $handle->query('select a.id from advertisers a, products_add_adv p where p.idAdvertiser = a.id', __FILE__, __LINE__))
                 {
                     $nbR = $handle->numrows($result, __FILE__, __LINE__);

                     if($nbA == $nbR)
                     {
                         print('Ok (' . $nbA. ')');
                     }

                 }
                 else
                 {
                     print('Erreur interne n°2');
                 }
            }
            else
            {
                print('Erreur interne');
            }
            
            
            print('</font>');
            break;

          //////////////////////////////////////////

        case 25 :

            print('<b>Vérification de la relation reject.id - products_add_adv.id : </b><font color="red">');
            
            if($result = & $handle->query('select id from rejects', __FILE__, __LINE__))
            {
                 $nbRE = $handle->numrows($result, __FILE__, __LINE__);

                 if($result = & $handle->query('select p.id from products_add_adv p, rejects r where p.reject = 1 and p.id = r.id', __FILE__, __LINE__))
                 {
                    $nbR = $handle->numrows($result, __FILE__, __LINE__);

                    if($nbRE == $nbR)
                    {
                        print('Ok (' . $nbRE. ')');
                    }
					else
					{
						print($nbRE . ' pour ' . $nbR);
					}
                 }
                 else
                 {
                     print('Erreur interne n°2');
                 }
            }
            else
            {
                print('Erreur interne');
            }
            
            
            print('</font>');
            break;
            
        //////////////////////////////////////////

        case 26 :

            print('<b>Vérification des images produits extranet : </b><font color="red">');

            $nbI = 0;
            $error = false;
            $dir = opendir(PRODUCTS_IMAGE_ADV_INC);
            
            if($dir)
            {
                while($r = readdir($dir))
                {
                    if($r == '.' || $r == '..' || $r == 'zoom')
                    {
                        continue;
                    }

                    ++$nbI;

                    $tab = explode('.', $r);

                    if($tab[1] != 'jpg' || !is_file(PRODUCTS_IMAGE_ADV_INC . 'zoom/' . $tab[0] . '.jpg'))
                    {
                        print('Fichier ' . $r . ' invalide<br>');
                        continue;
                    }
                    


                    if((!($result = & $handle->query('select id from products_add_adv where id = \'' . $handle->escape($tab[0]) . '\'', __FILE__, __LINE__)))
                       || $handle->numrows($result, __FILE__, __LINE__) != 1)
                    {
                         print('Erreur de fichier ' . $r . '<br>');
                         $error = true;

                    }


                }


                closedir($dir);
                
                $nbJ = 0;

                $dir = opendir(PRODUCTS_IMAGE_ADV_INC . 'zoom/');
                if($dir)
                {
                    while($r = readdir($dir))
                    {
                        if($r == '.' || $r == '..')
                        {
                            continue;
                        }
                        
                        ++$nbJ;
                    }

                    closedir($dir);

                    if($nbI != $nbJ)
                    {
                        $error = true;
                        print($nbI . ' images pour ' . $nbJ . ' images zoom<br>');

                        $dir = opendir(PRODUCTS_IMAGE_ADV_INC . 'zoom/');
                        if($dir)
                        {
                            while($r = readdir($dir))
                            {
                                if($r == '.' || $r == '..')
                                {
                                    continue;
                                }

                                $tab = explode('.', $r);

                                if($tab[1] != 'jpg' || !is_file(PRODUCTS_IMAGE_ADV_INC . $tab[0] . '.jpg'))
                                {
                                    print('Fichier zoom/' . $r . ' invalide<br>');
                                }

                            }

                            closedir($dir);
                        }
                    }
                }
                else
                {
                    $error = true;
                    print('Erreur interne n°2');
                }
                
                
                if(!$error)
                {
                    print('Ok (' . $nbI . ')');
                }
            }
            else
            {
                print('Erreur interne');
            }
            
            
            print('</font>');
            break;
            
        //////////////////////////////////////////
        
        case 27 :

            print('<b>Vérification des fichiers divers produits extranet : </b><font color="red">');

            $nbI = 0;
            $error = false;
            $dir = opendir(PRODUCTS_FILES_ADV_INC);
            
            if($dir)
            {
                while($r = readdir($dir))
                {
                    if($r == '.' || $r == '..')
                    {
                        continue;
                    }

                    ++$nbI;

                    $tab = explode('.', $r);

                    if($tab[1] != 'doc' && $tab[1] != 'pdf' && substr($tab[0], -2) != '-1'  && substr($tab[0], -2) != '-2'  && substr($tab[0], -2) != '-3')
                    {
                        print('Fichier ' . $r . ' invalide<br>');
                        continue;
                    }

                    $tab = explode('-', $tab[0]);


                    if((!($result = & $handle->query('select id from products_add_adv where id = \'' . $handle->escape($tab[0]) . '\'', __FILE__, __LINE__)))
                       || $handle->numrows($result, __FILE__, __LINE__) != 1)
                    {
                         print('Erreur de fichier ' . $r . '<br>');
                         $error = true;
                    }


                }


                closedir($dir);



                if(!$error)
                {
                    print('Ok (' . $nbI . ')');
                }
            }
            else
            {
                print('Erreur interne');
            }
            
            
            print('</font>');
            break;
			
		case 28 :

            print('<b>Vérification de la relation references_cols.idProduct - products.id : </b><font color="red">');

            if($result = & $handle->query('select idProduct from references_cols', __FILE__, __LINE__))
            {
                 $nbF = $handle->numrows($result, __FILE__, __LINE__);
                 
                 if($result = & $handle->query('select p.id from products p, references_cols r where r.idProduct = p.id', __FILE__, __LINE__))
                 {
                     $nbR = $handle->numrows($result, __FILE__, __LINE__);

                     if($nbF == $nbR)
                     {
                         print('Ok (' . $nbF . ')');
                     }
                     else
                     {
                         print('Erreur (' . $nbR. ' pour ' . $nbF . ' produits)');
                     }
                 }
                 else
                 {
                     print('Erreur interne n°2');
                 }
            }
            else
            {
                print('Erreur interne');
            }
            
            
            print('</font>');
            break;
            
		case 29 :

            print('<b>Vérification de la relation references_content.idProduct - products.id : </b><font color="red">');

            if($result = & $handle->query('select idProduct from references_content', __FILE__, __LINE__))
            {
                 $nbF = $handle->numrows($result, __FILE__, __LINE__);
                 
                 if($result = & $handle->query('select p.id from products p, references_content r where r.idProduct = p.id', __FILE__, __LINE__))
                 {
                     $nbR = $handle->numrows($result, __FILE__, __LINE__);

                     if($nbF == $nbR)
                     {
                         print('Ok (' . $nbF . ')');
                     }
                     else
                     {
                         print('Erreur (' . $nbR. ' pour ' . $nbF . ' produits)');
                     }
                 }
                 else
                 {
                     print('Erreur interne n°2');
                 }
            }
            else
            {
                print('Erreur interne');
            }
            
            
            print('</font>');
            break;


    }

    if($step < (NB - 1))
        print('<p><center><input type="button" value="Etape suivante" onClick="goTo(\'hook.php?' . session_name() . '=' . session_id() . '&action=idr&step=' . ++$step . '\')"></center>');
    else
    {
        print('<center> - End of process - </center>');
    }
}


?></div><br><br>
<?php

}   // fin droits

require(ADMIN . 'tail.php');

?>