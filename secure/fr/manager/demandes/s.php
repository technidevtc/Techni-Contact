<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 16 juin 2005

 Fichier : /secure/manager/demandes/s.php
 Description : Suppressions de demandes

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$title  = $navBar = 'Recherche / Suppression des demandes de catalogues de certains organismes';
require(ADMIN . 'head.php');

?>
<script language="JavaScript">
<!--

function del(id)
{
    document.search.del.value = id;
   
    document.search.submit();
    document.search.rok.disabled = true;
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
    
    document.search.submit();
    document.search.rok.disabled = true;

}


//-->
</script>
<div class="titreStandard">Recherche / Suppression des demandes de catalogues de certains organismes </div><br><div class="bg">
<form name="search" method="post" action="s.php?<?php print(session_name() . '=' . session_id()) ?>">
<input type="hidden" name="action" value="search">Rechercher les enregistrement dont le champs <select name="champ">
<option value="societe" <?php if(isset($_POST['champ']) && $_POST['champ'] == 'societe') print('selected') ?>>société</option>
<option value="nom"<?php if(isset($_POST['champ']) && $_POST['champ'] == 'nom') print('selected') ?>>nom</option>
<option value="prenom"<?php if(isset($_POST['champ']) && $_POST['champ'] == 'prenom') print('selected') ?>>prénom</option>
<option value="cp"<?php if(isset($_POST['champ']) && $_POST['champ'] == 'cp') print('selected') ?>>code postal</option>
<option value="ville"<?php if(isset($_POST['champ']) && $_POST['champ'] == 'ville') print('selected') ?>>ville</option>
</select> contient <input type="text" name="pattern" size="15" maxlength="15" value="<?php if(isset($_POST['pattern'])) print(to_entities(substr($_POST['pattern'], 0, 15))) ?>"> &nbsp;
<input type="hidden" name="del" value="">
<input type="button" class="bouton" name="rok" value="Ok" onClick="isOk()">
</form>
<?php

if(isset($_POST['champ']) && isset($_POST['pattern']) && isset($_POST['del']))
{
    print('<p>&nbsp;</p>Résultat de votre recherche - Cette page vous permet de supprimer d\'éventuels indésirables :<br><br>');

    $c = & $_POST['champ'];
    
    if($c == 'societe' || $c == 'nom' || $c == 'prenom' || $c == 'cp' || $c == 'ville')
    {

        // Supprimer l'indésirable
        if($_POST['del'] != '')
        {
            $handle->query('delete from catalogues where id = \'' . $handle->escape($_POST['del']) . '\'', __FILE__, __LINE__);

            $handle->query('delete from demandes where id = \'' . $handle->escape($_POST['del']) . '\'', __FILE__, __LINE__);

            ManagerLog($handle, $_SESSION['id'], $_SESSION['login'], $_SESSION['pass'], $_SESSION['ip'], 'Suppression de demandes de catalogues effectuées par ' . $_POST['del']);


            print('<div class="confirm">Indésirable supprimé avec succès de l\'ensemble des demandes de catalogues (dernières et prochaines éditions).</div><br><br>');
        }


        if($result = & $handle->query('select id, societe, nom, prenom, fonction, adresse, cp, ville from demandes where ' . $c . ' like \'%' . $handle->escape(substr($_POST['pattern'], 0, 15)) . '%\'', __FILE__, __LINE__))
        {
            if($handle->numrows($result, __FILE__, __LINE__) == 0)
            {
                print('<div class="error">Aucun résultat.</div>');
            }
            else
            {

                print('<center><table border="1" cellspacing="5" cellpadding="5">');
                print('<tr><td class="intitule"><div align="center">Action</div></td><td class="intitule"><div align="center">Société</div></td><td class="intitule"><div align="center">Nom</div></td><td class="intitule"><div align="center">Prénom</div></td><td class="intitule"><div align="center">Fonction</div></td><td class="intitule"><div align="center">Adresse</div></td><td class="intitule"><div align="center">Code postal</div></td><td class="intitule"><div align="center">Ville</div></td></tr>');


                while($row = & $handle->fetch($result))                       
                {
                   print('<tr><td class="intitule"><div align="center"><a href="javascript:del(\'' . $row[0] . '\')">Effacer</a></div></td><td class="intitule"><div align="center">' . to_entities($row[1]) . '</div></td><td class="intitule"><div align="center">' . to_entities($row[2]) . '</div></td><td class="intitule"><div align="center">' . to_entities($row[3]) . '</div></td><td class="intitule"><div align="center">' . to_entities($row[4]) . '</div></td><td class="intitule"><div align="center">' . to_entities($row[5]) . '</div></td><td class="intitule"><div align="center">' . to_entities($row[6]) . '</div></td><td class="intitule"><div align="center">' . to_entities($row[7]) . '</div></td></tr>');
                }


                print('</table>');

            }
        }

    } // fin recherche valide

}    // fin recherche


?>
</div><?php

require(ADMIN . 'tail.php');

?>
