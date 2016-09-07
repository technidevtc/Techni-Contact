<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 20 décembre 2004

 Fichier : /secure/manager/stats/advertiser.php
 Description : Page statistiques par annonceurs

/=================================================================*/


require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';


function toMonth($m)
{
   switch($m)
   {
      case 1 : $int = 'janvier';   break;
      case 2 : $int = 'février';   break;
      case 3 : $int = 'mars';      break;
      case 4 : $int = 'avril';     break;
      case 5 : $int = 'mai';       break;
      case 6 : $int = 'juin';      break;
      case 7 : $int = 'juillet';   break;
      case 8 : $int = 'août';      break;
      case 9 : $int = 'septembre'; break;
      case 10: $int = 'octobre';   break;
      case 11: $int = 'novembre';  break;
      case 12: $int = 'décembre';  break;
      default : '';
   }

   return $int;

}

// Id annonceurs
if(!isset($_GET['id']) || !preg_match('/^[0-9]{1,5}$/', $_GET['id']))
{
    header('Location: ' . ADMIN_URL);
    exit;

}

if(isset($_GET['y']) && (!preg_match('/^[0-9]{4}$/', $_GET['y']) ||$_GET['y'] < 2005 || $_GET['y'] > date('Y')))
{
    header('Location: ' . ADMIN_URL);
    exit;
}

if(isset($_GET['m'])  && (!preg_match('/^[0-9]{2}$/', $_GET['m']) || $_GET['m']  < 1 || $_GET['m'] > 12 || ($_GET['y'] == date('Y') && $_GET['m'] > date('m'))))
{
    header('Location: ' . ADMIN_URL);
    exit;
}



if(isset($_GET['m']))
{
    $navBar = '<a href="index.php?SESSION" class="navig">Pages vues produits</a> &raquo; <a href="advertiser.php?id=' . $_GET['id'] . '&SESSION" class="navig">Annonceur</a> &raquo; <a href="advertiser.php?id=' . $_GET['id'] . '&y=' . $_GET['y'] . '&SESSION" class="navig">' . $_GET['y'] . '</a> &raquo; ' . ucwords(toMonth($_GET['m']));
}
else if(isset($_GET['y']))
{
    $navBar = '<a href="index.php?SESSION" class="navig">Pages vues produits</a> &raquo; <a href="advertiser.php?id=' . $_GET['id'] . '&SESSION" class="navig">Annonceur</a> &raquo; ' . $_GET['y'];
}
else
{
    $navBar = '<a href="index.php?SESSION" class="navig">Pages vues produits</a> &raquo; Annonceur';
}


$title = 'Pages vues produits';


require(ADMIN . 'head.php');




if((!$result = & $handle->query('select a.nom1, p.id, p.name, p.ref_name, pf.idFamily, p.fastdesc from products_fr p, advertisers a, products_families pf where a.id = \''  .$_GET['id'] . '\' and p.idAdvertiser = a.id and p.id = pf.idProduct group by p.id order by p.name', __FILE__, __LINE__)) || $handle->numrows($result, __FILE__, __LINE__) == 0)
{
  exit;
}

$data = & $handle->fetch($result);


if(isset($_GET['y']))
{
    $a = mktime(0, 0, 0, 1, 1, $_GET['y']);
    $n = mktime(0, 0, 0, 1, 1, $_GET['y'] + 1);
}


?>
<div class="titreStandard">Statistiques de l'annonceur <?php print($data[0]) ?></a></div><br><div class="bg">
<center>

<?php


if(isset($_GET['m']))
{

    $tab = array();
    $listing = '';

    do
    {

        if(($result_i = & $handle->query('select data from stats_products where id = \'' . $data[1] . '\'', __FILE__, __LINE__)) && $handle->numrows($result_i, __FILE__, __LINE__))
        {
            $row = & $handle->fetch($result_i);
            $row = mb_unserialize($row[0]);
            
            foreach($row as $k => $v)
            {
                if(!array_key_exists($k, $tab))
                {
                    $tab[$k] = $v;
                }
                else
                {
                    $tab[$k] += $v;
                }
            }
        }
        
        if($data[5])
        {
            $extra = ' - ' . $data[5];
        }
        else
        {
            $extra = '';
        }

        $listing .= '<a href="product.php?id=' . $data[1] . '&' . session_name() . '=' . session_id() . '">' . $data[2] . '</a> ' . $extra . '<a href="' . URL . 'produits/' . $data[4] . '-' . $data[1] . '-' . $data[3] . '.html" target="_blank"><img src="' . ADMIN_URL . 'images/web.gif" border="0"></a><br>';

        
    } while($data = & $handle->fetch($result));

    $a = mktime(0, 0, 0, $_GET['m'], 1, $_GET['y']);
    $n = mktime(0, 0, 0, $_GET['m'] + 1, 1, $_GET['y']);

    ksort($tab);
    if(count($tab) > 0)
    {
        print('<table border="1"><tr><td class="intitule"><center>Jour</center></td><td class="intitule"><center>Nombre de pages vues</center></td></tr>');


        $prec = '';
        $pap = $total = 0;


        $s = false;

        foreach($tab as $k => $v)
        {
            if($k >= $a && $k < $n)
            {
                $s = true;

                $day = date('d', $k);

                if($prec == '')
                {
                    $prec = $day;
                }


                if($prec != $day)
                {
                    print('<tr><td class="intitule"><center>' . $prec . '</center></td><td class="intitule"><center>' . $pap . '</center></td></tr>');

                    $pap  = 0;
                    $prec = $day;

                }

                $pap   += $v;
                $total += $v;

            }
        }

        if($s)
        {
            print('<tr><td class="intitule"><center>' . $prec . '</center></td><td class="intitule"><center>' . $pap . '</center></td></tr><tr><td class="intitule"><center>Total</center></td><td class="intitule"><center>' . $total . '</center></td></tr></table>');
        }
    }
    else
    {
        print('Aucune statistique pour cet annonceur ce mois-ci.');
    }
}
else if(isset($_GET['y']))
{
    $tab = array();
    $listing = '';

    do
    {
        if(($result_i = & $handle->query('select data from stats_products where id = \'' . $data[1] . '\'', __FILE__, __LINE__)) && $handle->numrows($result_i, __FILE__, __LINE__))
        {
            $row = & $handle->fetch($result_i);
            $row = mb_unserialize($row[0]);
            
            foreach($row as $k => $v)
            {
                if(!array_key_exists($k, $tab))
                {
                    $tab[$k] = $v;
                }
                else
                {
                    $tab[$k] += $v;
                }
            }
        }
        
        if($data[5])
        {
            $extra = ' - ' . $data[5];
        }
        else
        {
            $extra = '';
        }

        $listing .= '<a href="product.php?id=' . $data[1] . '&' . session_name() . '=' . session_id(). '">' . $data[2] . '</a> ' . $extra . '<a href="' . URL . 'produits/' . $data[4] . '-' . $data[1] . '-' . $data[3] . '.html" target="_blank"><img src="' . ADMIN_URL . 'images/web.gif" border="0"></a><br>';

        
    } while($data = & $handle->fetch($result));


    $a = mktime(0, 0, 0, 1, 1, $_GET['y']);
    $n = mktime(0, 0, 0, 1, 1, $_GET['y'] + 1);

    ksort($tab);
    if(count($tab) > 0)
    {
        print('<table border="1"><tr><td class="intitule"><center>Mois</center></td><td class="intitule"><center>Nombre de pages vues</center></td></tr>');

        $prec = ''; $pap = 0; $total = 0;

        $s = false;

        foreach($tab as $k => $v)
        {
            if($k >= $a && $k < $n)
            {
                $s = true;

                $month = date('m', $k);

                if($prec == '')
                {
                    $prec = $month;
                }


                if($prec != $month)
                {
                    print('<tr><td class="intitule"><center><a href="advertiser.php?id=' . $_GET['id'] . '&y=' . $_GET['y'] . '&m=' . $prec . '&' . session_name() . '=' . session_id(). '">' . ucwords(toMonth($prec)) . '</a></center></td><td class="intitule"><center>' . $pap . '</center></td></tr>');

                    $pap  = 0;
                    $prec = $month;

                }

                $pap   += $v;
                $total += $v;

            }
        }

        if($s)
        {
            print('<tr><td class="intitule"><center><a href="advertiser.php?id=' . $_GET['id'] . '&y=' . $_GET['y'] . '&m=' . $prec . '&' . session_name() . '=' . session_id(). '">' . ucwords(toMonth($prec)) . '</a></center></td><td class="intitule"><center>' . $pap . '</center></td></tr><tr><td class="intitule"><center>Total</center></td><td class="intitule"><center>' . $total . '</center></td></tr></table>');
        }
    }
    else
    {
        print('Aucune statistique pour cet annonceur cette année.');
    }

}
else
{
    $tab = array();
    $listing = '';

    do
    {


        if(($result_i = & $handle->query('select data from stats_products where id = \'' . $data[1] . '\'', __FILE__, __LINE__)) && $handle->numrows($result_i, __FILE__, __LINE__))
        {
            $row = & $handle->fetch($result_i);
            $row = mb_unserialize($row[0]);
            
            foreach($row as $k => $v)
            {
                if(!array_key_exists($k, $tab))
                {
                    $tab[$k] = $v;
                }
                else
                {
                    $tab[$k] += $v;
                }
            }
        }

        if($data[5])
        {
            $extra = ' - ' . $data[5];
        }
        else
        {
            $extra = '';
        }


        $listing .= '<a href="product.php?id=' . $data[1] . '&' . session_name() . '=' . session_id(). '">' . $data[2] . '</a> ' . $extra . '<a href="' . URL . 'produits/' . $data[4] . '-' . $data[1] . '-' . $data[3] . '.html" target="_blank"><img src="' . ADMIN_URL . 'images/web.gif" border="0"></a><br>';

        
    } while($data = & $handle->fetch($result));
    

    if(count($tab) > 0)
    {
    
        print('<table border="1"><tr><td class="intitule"><center>Année</center></td><td class="intitule"><center>Nombre de pages vues</center></td></tr>');


        $prec = '';
        $pap = $total = 0;

        foreach($tab as $k => $v)
        {
            $year = date('Y', $k);

            if($prec == '')
            {
                $prec = $year;
            }


            if($prec != $year)
            {
                print('<tr><td class="intitule"><center><a href="advertiser.php?id=' . $_GET['id'] . '&y=' . $prec . '&' . session_name() . '=' . session_id(). '">' . $prec . '</a></center></td><td class="intitule"><center>' . $pap . '</center></td></tr>');

                $pap  = 0;
                $prec = $year;

            }

            $pap   += $v;
            $total += $v;


        }


        print('<tr><td class="intitule"><center><a href="advertiser.php?id=' . $_GET['id'] . '&y=' . $prec . '&' . session_name() . '=' . session_id(). '">' . $prec . '</a></center></td><td class="intitule"><center>' . $pap . '</center></td></tr><tr><td class="intitule"><center>Total</center></td><td class="intitule"><center>'.$total.'</center></td></tr></table>');

    }
    else
    {
        print('Aucune statistique pour cet annonceur.');
    }


}

?>
</center>
<br><br>Liste des produits de cet annonceur :<br><br>
<?php print($listing) ?>
</div><?php



require(ADMIN . 'tail.php');

?>
