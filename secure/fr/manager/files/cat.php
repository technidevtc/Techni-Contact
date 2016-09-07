<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 11 juin 2005

 Fichier : /secure/manager/files/cat.php
 Description : Gestion images catalogues ...

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$title = $navBar = 'Gestion des couvertures / exemplaires / dates de sortie des catalogues';
require(ADMIN . 'head.php');


if($user->rank != COMMADMIN && $user->rank != HOOK_NETWORK)
{
    print('<div class="bg"><div class="fatalerror">Vous n\'avez pas les droits adéquats pour réaliser cette opération.</div></div>');
}
else
{
  
    $upload = $upex = false;

    if($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        if(isset($_POST['ex_gen']) && isset($_POST['ex_col']) && isset($_POST['ex_ind']) && isset($_POST['ds_gen']) && isset($_POST['ds_col']) && isset($_POST['ds_ind']))
        {
            $string = '<?php $nbGen = \'' . str_replace("'", "\'", $_POST['ex_gen']) . '\'; $nbCol = \'' . str_replace("'", "\'", $_POST['ex_col']) . '\'; $nbInd = \'' . str_replace("'", "\'", $_POST['ex_ind']) . '\'; $dsGen = \'' . str_replace("'", "\'", $_POST['ds_gen']) . '\'; $dsCol = \'' . str_replace("'", "\'", $_POST['ds_col']) . '\'; $dsInd = \'' . str_replace("'", "\'", $_POST['ds_ind']) . '\' ?>';

            $f = fopen(MISC_INC . 'exemplaires.dat', 'w');

            if($f)
            {
                fputs($f, $string);

                ManagerLog($handle, $_SESSION['id'], $_SESSION['login'], $_SESSION['pass'], $_SESSION['ip'], 'Mise à jour du nombre d\'exemplaires / dates de sortie des catalogues');

                $upex = true;
                fclose($f);
            }
        }
        else
        {

            include(ADMIN . 'logo.php');
        
            upload('gen', 'gif', '1', 0, 0, CAT_INC);
            upload('col', 'gif', '2', 0, 0, CAT_INC);
            upload('ind', 'gif', '3', 0, 0, CAT_INC);

            ManagerLog($handle, $_SESSION['id'], $_SESSION['login'], $_SESSION['pass'], $_SESSION['ip'], 'Mise en ligne de nouvelles couvertures de catalogues');


            $upload = true;
        }
    }

?>
<div class="titreStandard">Couvertures actuelle</div><br>
<div class="bg"><form method="post" action="cat.php?<?php print(session_name() . '=' . session_id()) ?>">
<?php

if($upex)
{
    print('<div class="confirm">Nombre d\'exemplaires / Dates de sortie mis à jour avec succès.</div><br>');
}

require(MISC_INC . 'exemplaires.dat');

?>
<table align="center" width="600">
<tr><td width="200"><center><img src="see.php?type=1&id=1.gif"></center></td><td width="200"><center><img src="see.php?type=1&id=2.gif"></center></td><td><center><img src="see.php?type=1&id=3.gif"></center></td></tr>
<tr><td width="200" class="intitule"><center>Catalogue général<br><br><input type="text" size="6" name="ex_gen" value="<?php print(to_entities($nbGen)) ?>"> exemplaires<br>Date de sortie : <input type="text" size="10" name="ds_gen" value="<?php print(to_entities($dsGen)) ?>"></center></td><td class="intitule"><center>Catalogue collectivités<br><br><input type="text" size="6" name="ex_col" value="<?php print(to_entities($nbCol)) ?>"> exemplaires<br>Date de sortie : <input type="text" size="10" name="ds_col" value="<?php print(to_entities($dsCol)) ?>"></center></td><td class="intitule"><center>Catalogue industries<br><br><input type="text" size="6" name="ex_ind" value="<?php print(to_entities($nbInd)) ?>"> exemplaires<br>Date de sortie : <input type="text" size="10" name="ds_ind" value="<?php print(to_entities($dsInd)) ?>"></center></td></tr>
</table><br>
<center><input type="button" value="MAJ le nombre d'exemplaires et les dates de sortie" onClick="this.form.submit(); this.disabled=true"> &nbsp; <input type="reset" value="Annuler"></center></form>
</div><br><br><div class="titreStandard">Nouvelle(s) couverture(s)</div><br>
<div class="bg"><?php

if($upload)
{
    print('<div class="confirm">Couverture(s) uploadée(s) avec succès.</div><br>');
}


?><form method="post" action="cat.php?<?php print(session_name() . '=' . session_id()) ?>" enctype="multipart/form-data">
<table><tr><td class="intitule">Catalogue général :</td><td> <input type="file" name="gen" size="50"></td></tr>
<tr><td class="intitule">Catalogue collectivités :</td><td> <input type="file" name="col" size="50"></td></tr>
<tr><td class="intitule">Catalogue industries :</td><td> <input type="file" name="ind" size="50"></td></tr></table>
<br><div class="commentaire">Note : les images doivent être au format GIF. De plus, elles ne seront pas redimensionnées après upload</div><br><center><input type="button" value="Valider" onClick="this.form.submit(); this.disabled=true"> &nbsp; <input type="reset" value="Annuler"></center>
</form></div>
<?php

}  // fin autorisation

require(ADMIN . 'tail.php');

?>
