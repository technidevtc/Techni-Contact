<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 20 décembre 2004

 Fichier : /secure/manager/stats/year.php
 Description : Page statistiques par année

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



if(!isset($_GET['id']) || !preg_match('/^[0-9]{4}$/', $_GET['id']) || $_GET['id'] < 2005 || $_GET['id'] > date('Y'))
{
    header('Location: ' . ADMIN_URL);
    exit;
}

$title  = 'Pages vues produits';
$navBar = '<a href="index.php?SESSION" class="navig">Pages vues produits</a> &raquo; ' . $_GET['id'];

require(ADMIN . 'head.php');

$a = mktime(0, 0, 0, 1, 1, $_GET['id']);
$n = mktime(0, 0, 0, 1, 1, $_GET['id'] + 1);


?>
<div class="titreStandard">Pages vues produits de l'année <?php print($_GET['id']) ?></div><br><div class="bg">
<center><table border="1"><tr><td class="intitule"><center>Mois</center></td><td class="intitule"><center>Nombre de pages vues</center></td></tr>
<?php

if($result = & $handle->query('select day, number from stats_pap where day >= ' . $a . ' and day < ' . $n . ' order by day', __FILE__, __LINE__))
{
    $prec = '';
    $pap = $total = 0;

    while($row = & $handle->fetch($result))
    {
        $month = date('m', $row[0]);

        if($prec == '')
        {
            $prec = $month;
        }


        if($prec != $month)
        {
            print('<tr><td class="intitule"><center><a href="month.php?m=' . $prec . '&id=' . $_GET['id'] . '&' . session_name() . '=' . session_id() . '">' . ucwords(toMonth($prec)) . '</a></center></td><td class="intitule"><center>' . $pap . '</center></td></tr>');

            $pap  = 0;
            $prec = $month;

        }

        $pap   += $row[1];
        $total += $row[1];


    }

}

print('<tr><td class="intitule"><center><a href="month.php?m=' . $prec . '&id=' . $_GET['id'] . '&' . session_name() . '=' . session_id() . '">' . ucwords(toMonth($prec)) . '</a></center></td><td class="intitule"><center>' . $pap . '</center></td></tr>');


?><tr><td class="intitule"><center>Total</center></td><td class="intitule"><center><?php print($total) ?></center></td></tr></table>
</center>
</div><?php


require(ADMIN . 'tail.php');

?>
