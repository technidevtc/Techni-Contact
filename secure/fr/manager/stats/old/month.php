<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 20 décembre 2004

 Fichier : /secure/manager/stats/months.php
 Description : Page statistiques par mois d'une année

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


if(!isset($_GET['id']) || !preg_match('/^[0-9]{4}$/', $_GET['id']) || $_GET['id'] < 2005 || $_GET['id'] > date('Y') ||
   !isset($_GET['m'])  || !preg_match('/^[0-9]{2}$/', $_GET['m'])  || $_GET['m']  < 1 || $_GET['m'] > 12 ||
   ($_GET['id'] == date('Y') && $_GET['m'] > date('m')))
{

    header('Location: ' . ADMIN_URL);
    exit;
}

$title  = 'Pages vues produits';
$navBar = '<a href="index.php?SESSION" class="navig">Pages vues produits</a> &raquo; <a href="year.php?id=' . $_GET['id'] . '&SESSION" class="navig">' . $_GET['id'] . '</a> &raquo; ' . ucwords(toMonth($_GET['m']));

require(ADMIN . 'head.php');




$a = mktime(0, 0, 0, $_GET['m'], 1, $_GET['id']);
$n = mktime(0, 0, 0, $_GET['m'] + 1, 1, $_GET['id']);


?>
<div class="titreStandard">Pages vues produits de <?php print(toMonth($_GET['m']) . ' ' . $_GET['id']) ?></div><br><div class="bg">
<center><table border="1"><tr><td class="intitule"><center>Jour</center></td><td class="intitule"><center>Nombre de pages vues</center></td></tr>
<?php

if($result = & $handle->query('select day, number from stats_pap where day >= '.$a.' and day < '.$n.' order by day', __FILE__, __LINE__))
{
    $prec = '';
    $pap = $total = 0;

    while($row = & $handle->fetch($result))
    {
        $jour = date('d', $row[0]);

        if($prec == '')
        {
            $prec = $jour;
        }


        if($prec != $jour)
        {
            print('<tr><td class="intitule"><center>' . $prec . '</center></td><td class="intitule"><center>' . $pap . '</center></td></tr>');

            $pap  = 0;
            $prec = $jour;

        }

        $pap   += $row[1];
        $total += $row[1];


    }

}

print('<tr><td class="intitule"><center>' . $prec . '</center></td><td class="intitule"><center>' . $pap . '</center></td></tr>');


?><tr><td class="intitule"><center>Total</center></td><td class="intitule"><center><?php print($total) ?></center></td></tr></table>
</center>
</div><?php


require(ADMIN . 'tail.php');

?>
