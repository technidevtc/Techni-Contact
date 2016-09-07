<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 12 juin 2005

 Fichier : /secure/manager/files/editbibli.php
 Description : Maj une image de la bibli

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$title  = 'Bibliothèque d\'images';
$navBar = '<a href="bibli.php?SESSION" class="navig">Bibliothèque d\'images</a> &raquo; Mise à jour';
require(ADMIN . 'head.php');


if($user->rank != COMMADMIN && $user->rank != HOOK_NETWORK)
{
    print('<div class="bg"><div class="fatalerror">Vous n\'avez pas les droits adéquats pour réaliser cette opération.</div></div>');
}
else if(!isset($_GET['img']) || !preg_match('/^[1-9][0-9]*\.(gif|jpg)$/', $_GET['img']) || !is_file(BIBLI_INC . $_GET['img']))
{
    print('<div class="bg"><div class="fatalerror">Identifiant image incorrect.</div></div>');
}
else
{

    $upload = false;

    if($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        include(ADMIN . 'logo.php');

        $tab = explode('.', $_GET['img']);

        upload('1', $tab[1], $tab[0], 0, 0, BIBLI_INC);
    
        $upload = true;
        
        ManagerLog($handle, $_SESSION['id'], $_SESSION['login'], $_SESSION['pass'], $_SESSION['ip'], 'Edition de l\'image ' . $_GET['img'] . ' de la bibliothèque');

    }
    

?><div class="titreStandard">Mise à jour d'une image - <a href="bibli.php?<?php print(session_name() . '=' . session_id()) ?>">Retour</a></div><br>
<div class="bg"><?php

if($upload)
{
    print('<div class="confirm">Image mise à jour avec succès</div><br><br>');
}


?><center><img src="see.php?type=2&id=<?php print($_GET['img']) ?>"></center>
<br><br><form method="post" action="editbibli.php?img=<?php print($_GET['img'] . '&' . session_name() . '=' . session_id()) ?>" enctype="multipart/form-data">
Fichier : <input type="file" name="1" size="50"> <?php print(strtoupper(substr($_GET['img'], -3))) ?>
<br><div class="commentaire">Note : L'image ne sera pas redimensionnée après upload (excepté pour l'affichage dans la bibliothèque)</div><br><center><input type="button" value="Valider" onClick="this.form.submit(); this.disabled=true"> &nbsp; <input type="reset" value="Annuler"></center>
</form></div><?php

}  // fin autorisation

require(ADMIN . 'tail.php');

?>
