<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 20 décembre 2004

 Mises à jour :

       15 juin 2005 : + gestion des factures

 Fichier : /secure/manager/contacts/index.php
 Description : Demandes de contacts

/=================================================================*/

if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

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



$title  = $navBar = 'Demandes de contacts';
require(ADMIN . 'head.php');



?>
<script language="JavaScript">
<!--

var isClicked = 0;

function valid()
{
    if(isClicked == 0)
    {
        alert('Merci de sélectionner la demande pour laquelle vous souhaitez créer le modèle de fax.');
    }
    else
    {
        document.generate.submit();
    }

}



//-->
</script>
<div class="titreStandard">Demandes de contacts</div><br><div class="bg">
Afficher <select name="d" onChange="goTo('index.php?<?php print(session_name() . '=' . session_id()) ?>&d=' + this.options[this.selectedIndex].value)">
<?php

$sel = (!isset($_GET['d']) || $_GET['d'] == 50) ? 'selected' : '';
print('<option value="50" ' . $sel . '>les 50 dernières demandes</option>');

$fin   = mktime(0, 0, 0, date('m') , 1, date('Y'));
$debut = mktime(0, 0, 0, 2, 1, 2005);


for( ; $fin >= $debut; $fin = mktime(0, 0, 0, $tmonth - 1, 1, $tyear))
{
    $sel = (isset($_GET['d']) && $_GET['d'] == $fin) ? 'selected' : '';
    print('<option value="' . $fin . '" ' . $sel . '>Les demandes de ' . toMonth(date('m', $fin)) . date(' Y', $fin) . '</option>');
    
    list($tmonth, $tyear) = explode('-', date('m-Y', $fin));
}

print('</select>');


$q = 'select c.timestamp, c.societe, c.nom, c.prenom, c.fonction, p.name, a.nom1, c.type, c.id, c.gen, c.sent, a.parent from contacts c, advertisers a, products_fr p where c.idProduct = p.id and c.idAdvertiser = a.id';

if(!isset($_GET['d']) || $_GET['d'] == 50)
{
    $q .= ' order by c.timestamp desc limit 50';
}
else if(isset($_GET['d']) && preg_match('/^[0-9]{10}$/', $_GET['d']))
{
    list($tmonth, $tyear) = explode('-', date('m-Y', $_GET['d']));
    $q .= ' and c.timestamp >= \'' . $_GET['d'] . '\' and c.timestamp < \'' . mktime(0, 0, 0, $tmonth + 1, 1, $tyear) . '\' order by c.timestamp' ;
}

if($result = & $handle->query($q, __FILE__, __LINE__))
{

    print('<form name="generate" target="_blank" action="create.php?' . session_name() . '=' . session_id() . '" method="post"><center><table border="1" cellspacing="5" cellpadding="5">');
    print('<tr><td class="intitule"><div align="center">Date</div></td><td class="intitule"><div align="center">Société</div></td><td class="intitule"><div align="center">Nom</div></td><td class="intitule"><div align="center">Prénom</div></td><td class="intitule"><div align="center">Fonction</div></td><td class="intitule"><div align="center">Produit</div></td><td class="intitule"><div align="center">Type de la demande</div></td><td class="intitule"><div align="center">Modèle Généré ?</div></td><td class="intitule"><div align="center">Sélectionner</div></td></tr>');
    while($row = & $handle->fetch($result))
    {
        switch($row[7])
        {
            case 1  : $row[7] = 'Informations';         break;
            case 2  : $row[7] = 'Contact téléphonique'; break;
            case 3  : $row[7] = 'Devis';                break;
            default : $row[7] = 'Commande';
        }

        $row[9] = ($row[9] == 1) ? 'oui' : 'non';

        $sent  = $row[10] == 0 ? '<font color="red">' : '';
        $esent = $row[10] == 0 ? '</font>' : '';
		
		$extra_adv = ($row[11] != 0) ? '<br><font color="red">Fournisseur Techni-Contact</font>' : '';
		

        print('<tr><td class="intitule"><div align="center">' . $sent . date('d/m/Y', $row[0]) . $esent . '</div></td><td class="intitule"><div align="center">' . $sent . to_entities($row[1]) . $esent . '</div></td><td class="intitule"><div align="center">' . $sent . to_entities($row[2]) . $esent . '</div></td><td class="intitule"><div align="center">' . $sent . to_entities($row[3]) . $esent . '</div></td><td class="intitule"><div align="center">' . $sent . to_entities($row[4]) . $esent . '</div></td><td class="intitule"><div align="center">' . $sent . to_entities($row[5]) . ' (' . to_entities($row[6]) . ')' . $esent . $extra_adv . '</div></td><td class="intitule"><div align="center">' . $sent . $row[7] . $esent . '</div></td><td class="intitule"><div align="center">' . $sent . $row[9] . $esent . '</div></td><td class="intitule"><div align="center"><input type="radio" name="sel" value="' . $row[8] . '" onClick="isClicked = 1"></div></td></tr>');
    }

    print('</table><br><br><input type="button" class="bouton" value="Générer le modèle de fax rattaché à cette demande" name="ok" onClick="valid()"> &nbsp; <input type="reset" class="bouton" value="Annuler" onClick="isClicked=0"></center>');

}


?>
</div><?php


require(ADMIN . 'tail.php');

?>
