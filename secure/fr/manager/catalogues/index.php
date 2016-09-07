<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 20 décembre 2004

 Mises à jour :

       15 juin 2005 : + gestion des factures

 Fichier : /secure/manager/catalogues/index.php
 Description : Demandes immédiate de catalogues

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



$title  = $navBar = 'Demandes de la dernière édition des catalogues';
require(ADMIN . 'head.php');

?>
<script language="JavaScript">
<!--

var isClicked = 0;

function valid()
{
    if(isClicked == 0)
    {
        alert('Merci de sélectionner la demande pour laquelle vous souhaitez créer la lettre.');

    }
    else
    {
        document.generate.submit();
        document.generate.ok.value = "Merci de patienter";

        document.generate.ok.disabled = true;
        document.generate.facture.disabled = true;
        
        window.location.reload();
    }


}

function ffacture()
{
    if(isClicked == 0)
    {
        alert('Merci de sélectionner la demande pour laquelle vous souhaitez créer la facture.');

    }
    else if(document.generate.idfacture.value == '')
    {
        alert('Merci de saisir l\'identifiant de la facture que vous souhaitez créer.');

    }
    else
    {

        document.generate.action = "facture.php"; 
        document.generate.submit();
        document.generate.facture.value = "Merci de patienter";
       
        document.generate.facture.disabled = true;
        document.generate.ok.disabled = true;
       
        window.location.reload();
    }
}



function isOk()
{
    var t = trim(document.search.pattern.value);

    if(t.length < 3)
    {
        alert('Vous devez saisir au minimum 3 caractères avant de lancer la recherche');
        document.search.pattern.value = t;
        document.search.pattern.focus();
        return;
    }
    
    document.search.pattern.value = escape(t);
    document.search.submit();
    document.search.rok.disabled = true;

}


//-->
</script>
<div class="titreStandard">Demandes de catalogues - <?php

if(isset($_GET['action']))
{
    switch($_GET['action'])
    {
        // Effacer des demandes imprimées
        case 'del' : if(isset($_GET['type']) && $_GET['type'] == 1)
                     {
                         $eoq = ' where imp = 1'; $eoqt = ' imprimés';
                     }
                     // Non imprimées
                     else
                     {
                         $eoq = '';               $eoqt = '';
                     }

                     $handle->query('delete from catalogues' . $eoq, __FILE__, __LINE__);

                     ManagerLog($handle, $_SESSION['id'], $_SESSION['login'], $_SESSION['pass'], $_SESSION['ip'], 'Suppression de l\'ensemble des demandes' . $eoqt . ' de la dernière édition des catalogues');


                     $out .= '<div class="confirm">Demandes de catalogues' . $eoqt . ' effacées avec succès.</div><br><br>';

                     break;

        // Effacer les demandes antérieures à la date X
        case 'sdel' : if(isset($_GET['day']) && isset($_GET['month']) && isset($_GET['year']))
                      {
                          if(!checkdate($_GET['month'], $_GET['day'], $_GET['year']))
                          {
                              $out .= '<div class="confirm">La date spécifiée est invalide.</div><br><br>';
                          }
                          else
                          {
                              $handle->query('delete from catalogues where timestamp < \'' . mktime(0, 0, 0, $_GET['month'], $_GET['day'], $_GET['year']) . '\'', __FILE__, __LINE__);
                          
                              ManagerLog($handle, $_SESSION['id'], $_SESSION['login'], $_SESSION['pass'], $_SESSION['ip'], 'Suppression de l\'ensemble des demandes de la dernière édition des catalogues précédant le ' . $_GET['day'] . '/' . $_GET['month'] . '/' . $_GET['year']);


                              $out .= '<div class="confirm">Demandes de catalogues précédant le '  .$_GET['day'] . ' ' . toMonth($_GET['month']) . ' ' . $_GET['year'] . ' effacées avec succès.</div><br><br>';
                          }
                      }
                      
                      break;

    }

}


if($result = & $handle->query('select id from catalogues where imp = 0', __FILE__, __LINE__))
{
    switch($nb = $handle->numrows($result, __FILE__, __LINE__))
    {
        case 0  : print('Aucun demande non imprimée');    break;
        case 1  : print('1 demande non imprimée');        break;
        default : print($nb . ' demandes non imprimées');
    }
}
else
{
    print('Aucune demande non imprimée');
}


?> en attente</div><br><div class="bg">
<?php


print($out);


?>


Vous souhaitez :
<br><br><ul><li> <a href="index.php?action=create&<?php print(session_name() . '=' . session_id()) ?>">Générer les demandes de catalogues</a><br><br>
<li> <form name="sup" action="index.php?">
<input type="hidden" name="<?php print(session_name()) ?>" value="<?php print(session_id()) ?>">
<input type="hidden" name="action" value="sdel">Supprimer les demandes effectuées avant le <select name="day">
<?php

for($i = 1; $i <= 31; ++$i)
{
   $sel = (date('d') == $i) ? 'selected' : '';
   print('<option value="' . $i . '" ' . $sel . '>' . $i . '</option>');
}

?>
</select> <select name="month">
<?php

for($i = 1; $i <= 12; ++$i)
{
   $sel = (date('m') == $i) ? 'selected' : '';
   

   print('<option value="' . $i . '" ' . $sel . '>' . toMonth($i) . '</option>');
}

?>
</select> <select name="year">
<?php

for($i = 2005; $i <= date('Y'); ++$i)
{
   $sel = (date('Y') == $i) ? 'selected' : '';
   print('<option value="' . $i . '" ' . $sel . '>' . $i . '</option>');
}

?>
</select> &nbsp; <input type="button" class="bouton" name="sok" value="Ok" onClick="this.form.submit(); this.disabled=true"></form><br>
<li> Supprimer <a href="index.php?action=del&<?php print(session_name() . '=' . session_id()) ?>" onClick="return confirm('Etes-vous sûr de vouloir supprimer l\'ensemble des demandes ponctuelles des catalogues ?')">Toutes les demandes de catalogues</a> / <a href="index.php?action=del&type=1&<?php print(session_name() . '=' . session_id()) ?>" onClick="return confirm('Etes-vous sûr de vouloir supprimer l\'ensemble des demandes ponctuelles des catalogues imprimées ?')">Toutes les demandes de catalogues imprimées</a>
</ul>
<?php

if(isset($_GET['action']) && $_GET['action'] == 'create')
{
    print('<p>&nbsp;</p>Liste des demandes :<br><br>');

    if($result = & $handle->query('select id, societe, gen, ind, col, timestamp, imp from catalogues order by timestamp desc', __FILE__, __LINE__))
    {
    
        print('<form name="generate" target="_blank" action="create.php?' . session_name() . '=' . session_id() . '" method="post"><center><table border="1" cellspacing="5" cellpadding="5">');
        print('<tr><td class="intitule"><div align="center">Société</div></td><td class="intitule"><div align="center">Catalogue Général</div></td><td class="intitule"><div align="center">Catalogue Industries</div></td><td class="intitule"><div align="center">Catalogue Collectivités</div></td><td class="intitule"><div align="center">Date</div></td><td class="intitule"><div align="center">Imprimée</div></td><td class="intitule"><div align="center">Sélectionner</div></td></tr>');
        while($row = & $handle->fetch($result))
        {
            $g = ($row[2] == 1) ? 'X' : '-';
            $i = ($row[3] == 1) ? 'X' : '-';
            $c = ($row[4] == 1) ? 'X' : '-';
            
            $imp = ($row[6] == 1) ? 'Oui' : 'Non';

            print('<tr><td class="intitule"><div align="center">' . to_entities($row[1]) . '</div></td><td class="intitule"><div align="center">' . $g . '</div></td><td class="intitule"><div align="center">' . $i . '</div></td><td class="intitule"><div align="center">' . $c . '</div></td><td class="intitule"><div align="center">' . date('d/m/Y', $row[5]) . '</div></td><td class="intitule"><div align="center">' . $imp . '</div></td><td class="intitule"><div align="center"><input type="radio" name="sel" value="' . $row[0] . '" onClick="isClicked = 1"></div></td></tr>');


        }

        print('</table><br><br><input type="button" class="bouton" value="Générer la lettre rattachée à cette demande" name="ok" onClick="valid()"> &nbsp; <input type="reset" class="bouton" value="Annuler" onClick="isClicked=0"><br><br>ID Facture : <input type="text" size="5" name="idfacture" > <input type="button" class="bouton" name="facture" value="Générer la facture de cette commande" onClick="ffacture()"></center><br><br><div class="commentaire">Note : il est nécessaire de saisir un identifiant de facture si vous souhaitez en générer une.</div>');

    }


}


?>
</div><?php


require(ADMIN . 'tail.php');

?>
