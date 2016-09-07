<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 20 décembre 2004

 Fichier : /secure/manager/stats/index.php
 Description : Page principale statistiques

/=================================================================*/


require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';


$title = $navBar = 'Pages vues produits';
require_once(ADMIN . 'head.php');


?>
<div class="titreStandard">Pages vues produits</div><br><div class="bg">
<center><table border="1"><tr><td class="intitule"><center>Année</center></td><td class="intitule"><center>Nombre de pages vues</center></td></tr>
<?php

if($result = & $handle->query('select day, number from stats_pap order by day desc', __FILE__, __LINE__))
{
    $prec = '';
    
    $pap = $total = 0;

    while($row = & $handle->fetch($result))
    {
        $year = date('Y', $row[0]);
        
        if($prec == '')
        {
            $prec = $year;
        }


        if($prec != $year)
        {
            print('<tr><td class="intitule"><center><a href="year.php?id=' . $prec . '&' . session_name() . '=' . session_id() . '">' . $prec . '</a></center></td><td class="intitule"><center>' . $pap . '</center></td></tr>');

            $pap  = 0;
            $prec = $year;

        }

        $pap   += $row[1];
        $total += $row[1];


    }

}

print('<tr><td class="intitule"><center><a href="year.php?id=' .$prec . '&' . session_name() . '=' . session_id() . '">' . $prec . '</a></center></td><td class="intitule"><center>' . $pap . '</center></td></tr>');


?><tr><td class="intitule"><center>Total</center></td><td class="intitule"><center><?php print($total) ?></center></td></tr></table>
</center><p>&nbsp;</p>
<div align="center">Choix par produit : <a href="index.php?lettre=0&<?php print(session_name() . '=' . session_id()) ?>">0-9</a>
<?php

for($i = ord('a'); $i <= ord('z'); ++$i)
{
    print(' - <a href="index.php?lettre=' . chr($i) . '&' . session_name() . '=' . session_id() . '">' . strtoupper(chr($i)) . '</a> ');

}

print('</div><br><br><div align="center">Choix par annonceur : <a href="index.php?alettre=0&' . session_name() . '=' . session_id() . '">0-9</a>');

for($i = ord('a'); $i <= ord('z'); ++$i)
{
    print(' - <a href="index.php?alettre=' . chr($i) . '&' . session_name() . '=' . session_id() . '">' . strtoupper(chr($i)) . '</a> ');

}

print('</div>');

if(isset($_GET['lettre']) && preg_match('/^[0a-z]$/', $_GET['lettre']))
{
    $lettre = $_GET['lettre'];

    print('<br><br><b>Liste des produits dont le nom commence par ');
    if($lettre == '0')
    {
        $pattern = 'REGEXP(\'^[0-9]\')';
        print('un chiffre :</b><br><br>');
    }
    else
    {
        $pattern = 'like \'' . $handle->escape($lettre) . '%\'';
        print('la lettre ' . strtoupper($lettre) . ' :</b><br><br>');

    }


    if($result = & $handle->query('select p.id, pfr.name, pf.idFamily, pfr.ref_name, pfr.fastdesc from products_fr pfr, products p, products_families pf where p.id = pfr.id and pf.idProduct = p.id and pfr.name ' . $pattern, __FILE__, __LINE__))
    {
        if($handle->numrows($result, __FILE__, __LINE__) == 0)
        {
            print('<center><b>Aucun résultat</b></center>');
        
        }
        else
        {
            $liste = array();

            print('<ul>');
            while($record = & $handle->fetch($result))
            {
                if(in_array($record[0], $liste))
                {
                    continue;
                }

                $liste[] = $record[0];
                
                if($record[4])
                {
                    $extra = ' - ' . $record[4];
                }
                else
                {
                    $extra = '';
                }


                print('<li> <a href="product.php?id=' . $record[0] . '&' . session_name() . '=' . session_id() . '">' . $record[1] . '</a> ' . $extra . '<a href="' . URL . 'produits/' . $record[2] . '-' . $record[0] . '-' . $record[3] . '.html" target="_blank"><img src="' . ADMIN_URL . 'images/web.gif" border="0"></a><br>');

            }

            print('</ul>');
        }
    }


}
else if(isset($_GET['alettre']) && preg_match('/^[0a-z]$/', $_GET['alettre']))
{
    $lettre = $_GET['alettre'];

    print('<br><br><b>Liste des annonceurs dont le nom commence par ');
    if($lettre == '0')
    {
        $pattern = 'REGEXP(\'^[0-9]\')';
        print('un chiffre :</b><br><br>');
    }
    else
    {
        $pattern = 'like \'' . $handle->escape($lettre) . '%\'';
        print('la lettre ' . strtoupper($lettre) . ' :</b><br><br>');

    }

    if($result = & $handle->query('select id, nom1 from advertisers where nom1 ' . $pattern, __FILE__, __LINE__))
    {
        if($handle->numrows($result, __FILE__, __LINE__) == 0)
        {
            print('<center><b>Aucun résultat</b></center>');
        
        }
        else
        {      

            print('<ul>');
            while($record = & $handle->fetch($result))
            {

                print('<li> <a href="advertiser.php?id=' . $record[0] . '&' . session_name() . '=' . session_id() . '">' . $record[1] . '</a><br>');

            }
            print('</ul>');
        }
    }


}


?>

</div><?php



require_once(ADMIN . 'tail.php');

?>
