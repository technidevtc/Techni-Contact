<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 12 juin 2005

 Fichier : /secure/manager/files/bibli.php
 Description : Bibliothèque d'images ...

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$title = $navBar = 'Bibliothèque d\'images';
require(ADMIN . 'head.php');


if($user->rank != COMMADMIN && $user->rank != HOOK_NETWORK)
{
    print('<div class="bg"><div class="fatalerror">Vous n\'avez pas les droits adéquats pour réaliser cette opération.</div></div>');
}
else
{

    $del = $upload = false;

    if($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        include(ADMIN . 'logo.php');
        
        for($i = 1; $i <=10; ++$i)
        {
            if(!isset($_POST['type_' . $i]) || ($_POST['type_' . $i] != 'gif' && $_POST['type_' . $i] != 'jpg'))
            {
                continue;
            }

            do
            {
                $id = rand(1, 99999);

            } while(is_file(BIBLI_INC . $id . '.gif') || is_file(BIBLI_INC . $id . '.jpg'));


            upload($i, $_POST['type_' . $i], $id, 0, 0, BIBLI_INC);
        }

        
        ManagerLog($handle, $_SESSION['id'], $_SESSION['login'], $_SESSION['pass'], $_SESSION['ip'], 'Mise en ligne de nouvelles images dans la bibliothèque');


        $upload = true;

    }
    else if(isset($_GET['del']) && isset($_GET['img']) && preg_match('/^[1-9][0-9]*\.(gif|jpg)$/', $_GET['img']) && is_file(BIBLI_INC . $_GET['img']))
    {
        if(unlink(BIBLI_INC . $_GET['img']))
        {
            ManagerLog($handle, $_SESSION['id'], $_SESSION['login'], $_SESSION['pass'], $_SESSION['ip'], 'Suppression d\'une image de la bibliothèque');

            $del = true;
        }
    }

?>
<div class="titreStandard">Contenu de la bibliothèque</div><br>
<div class="bg">
<div class="commentaire">Notes : Cliquez sur une image si vous souhaitez en connaitre l'adresse.</div>
<br><?php

if($del)
{
    print('<div class="confirm">Image supprimée</div><br>');
}


$f = opendir(BIBLI_INC);

if($f)
{
    print('<table align="center" width="600">');
    
    $j = 0; $k = 0;

    while($d = readdir($f))
    {
        if(preg_match('/^[1-9][0-9]*\.(gif|jpg)$/', $d))
        {
            if(substr($d, -3) == 'gif')
            {
                $img = imagecreatefromgif(BIBLI_INC . $d);
            }
            else
            {
                $img = imagecreatefromjpeg(BIBLI_INC . $d);
            }

            if(($x = imagesx($img)) > 200)
            {
                $ratio = $x / 200;

                $maxwidth = 'width="200" height="' . ceil(imagesy($img) / $ratio) . '"';

            }
            else
            {
                $maxwidth = '';
            }

            print('<td><table><tr><td><img src="see.php?type=2&id=' . $d . '" onClick="document.p.url.value=\'' . BIBLI_URL . $d . '\'" ' . $maxwidth . '></td></tr><tr><td class="intitule"><center><a href="editbibli.php?img=' . $d . '&' . session_name() . '=' . session_id() . '">Mettre à jour</a><br><a href="bibli.php?del=true&img=' . $d . '&' . session_name() . '=' . session_id() . '" onClick="return confirm(\'Etes-vous sûr de vouloir supprimer cette image ?\')">Supprimer</a></center></td></tr></table></td>');

            ++$k;

            if(++$j == 5)
            {
                print('</tr><tr>');
                
                $j = 0;
            }
        }
      
    }

    closedir($f);

    print('</table>');
    
    if($k == 0)
    {
        print('<div class="confirm">Actuellement aucune image dans la bibliothèque</div>');
    }
    else
    {
        print('<br><form name="p"><center>Adresse de l\'image : <input name="url" size="50" value="Cliquez sur une image pour en connaitre l\'adresse"></center></form>');
    }
}
else
{
    print('<div class="confirm">Erreur interne lors de l\'affichage du contenu de la bibliothèque d\'images</div>');
}


?>
</div><br><br><div class="titreStandard">Nouvelle(s) image(s)</div><br>
<div class="bg"><?php

if($upload)
{
    print('<div class="confirm">Nouvelle(s) image(s) uploadée(s) avec succès.</div><br>');
}


?><form method="post" action="bibli.php?<?php print(session_name() . '=' . session_id()) ?>" enctype="multipart/form-data">
<table><tr><td class="intitule">Fichier 1 :</td><td> <input type="file" name="1" size="50"> <select name="type_1"><option value="gif">GIF</option><option value="jpg">JPG</option></select></td></tr>
<tr><td class="intitule">Fichier 2 :</td><td> <input type="file" name="2" size="50"> <select name="type_2"><option value="gif">GIF</option><option value="jpg">JPG</option></select></td></tr>
<tr><td class="intitule">Fichier 3 :</td><td> <input type="file" name="3" size="50"> <select name="type_3"><option value="gif">GIF</option><option value="jpg">JPG</option></select></td></tr>
<tr><td class="intitule">Fichier 4 :</td><td> <input type="file" name="4" size="50"> <select name="type_4"><option value="gif">GIF</option><option value="jpg">JPG</option></select></td></tr>
<tr><td class="intitule">Fichier 5 :</td><td> <input type="file" name="5" size="50"> <select name="type_5"><option value="gif">GIF</option><option value="jpg">JPG</option></select></td></tr>
<tr><td class="intitule">Fichier 6 :</td><td> <input type="file" name="6" size="50"> <select name="type_6"><option value="gif">GIF</option><option value="jpg">JPG</option></select></td></tr>
<tr><td class="intitule">Fichier 7 :</td><td> <input type="file" name="7" size="50"> <select name="type_7"><option value="gif">GIF</option><option value="jpg">JPG</option></select></td></tr>
<tr><td class="intitule">Fichier 8 :</td><td> <input type="file" name="8" size="50"> <select name="type_8"><option value="gif">GIF</option><option value="jpg">JPG</option></select></td></tr>
<tr><td class="intitule">Fichier 9 :</td><td> <input type="file" name="9" size="50"> <select name="type_9"><option value="gif">GIF</option><option value="jpg">JPG</option></select></td></tr>
<tr><td class="intitule">Fichier 10 :</td><td> <input type="file" name="10" size="50"> <select name="type_10"><option value="gif">GIF</option><option value="jpg">JPG</option></select></td></tr>
</table>
<br><div class="commentaire">Note : Vous pouvez uploader au maximum 10 images à la fois. Les images ne seront pas redimensionnées après upload (excepté pour l'affichage dans la bibliothèque)</div><br><center><input type="button" value="Valider" onClick="this.form.submit(); this.disabled=true"> &nbsp; <input type="reset" value="Annuler"></center>
</form></div>
<?php

}  // fin autorisation

require(ADMIN . 'tail.php');

?>
