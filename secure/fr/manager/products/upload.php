<?php


/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 27 juin 2005

 Fichier : /secure/manager/products/upload.php
 Description : Upload images et docs dans le cadre d'une modif fiche
/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

require(ADMIN."products.php");

$title  = 'Base de données des produits';
$navBar = 'Base de données des produits &raquo; Upload de documents';
require(ADMIN . 'head.php');

$type = isset($_GET['type']) ? $_GET['type'] : '';


if(!isset($_GET['id']) || !preg_match('/^[0-9]+$/', $_GET['id']) || !($data = & loadProduct($handle, $_GET['id'], $type)) || (!isset($_POST['type1']) || ($_POST['type1'] != '.doc' && $_POST['type1'] != '.pdf')) || (!isset($_POST['type2']) || ($_POST['type2'] != '.doc' && $_POST['type2'] != '.pdf')) || (!isset($_POST['type3']) || ($_POST['type3'] != '.doc' && $_POST['type3'] != '.pdf')))
{
    print('<div class="bg"><div class="fatalerror">Identifiant produit incorrect.</div></div>');
}
else
{

    if($type == 'add_adv' || $type == 'edit_adv')
    {
        if(upload('image', 'jpg', $_GET['id'], 0, 0, PRODUCTS_IMAGE_ADV_INC . 'zoom/'))
        {
            ImageResize(100,  75, PRODUCTS_IMAGE_ADV_INC . 'zoom/' . $_GET['id'] . '.jpg', PRODUCTS_IMAGE_ADV_INC . $_GET['id'] . '.jpg');
            ImageResize(240, 240, PRODUCTS_IMAGE_ADV_INC . 'zoom/' . $_GET['id'] . '.jpg', PRODUCTS_IMAGE_ADV_INC . 'cards/' . $_GET['id'] . '.jpg');
        }

        uploadDoc('doc1', $_GET['id'] . '-1', PRODUCTS_FILES_ADV_INC, $_POST['type1']);
        uploadDoc('doc2', $_GET['id'] . '-2', PRODUCTS_FILES_ADV_INC, $_POST['type2']);
        uploadDoc('doc3', $_GET['id'] . '-3', PRODUCTS_FILES_ADV_INC, $_POST['type3']);

    }
    else
    {
        if(upload('image', 'jpg', $_GET['id'], 0, 0, PRODUCTS_IMAGE_INC . 'zoom/'))
        {
            ImageResize(100,  75, PRODUCTS_IMAGE_INC . 'zoom/' . $_GET['id'] . '.jpg', PRODUCTS_IMAGE_INC . $_GET['id'] . '.jpg');
            ImageResize(240, 240, PRODUCTS_IMAGE_INC . 'zoom/' . $_GET['id'] . '.jpg', PRODUCTS_IMAGE_INC . 'cards/' . $_GET['id'] . '.jpg');
        }

        uploadDoc('doc1', $_GET['id'] . '-1', PRODUCTS_FILES_INC, $_POST['type1']);
        uploadDoc('doc2', $_GET['id'] . '-2', PRODUCTS_FILES_INC, $_POST['type2']);
        uploadDoc('doc3', $_GET['id'] . '-3', PRODUCTS_FILES_INC, $_POST['type3']);
    }



?><div class="titreStandard">Upload de documents</div><br>
<div class="bg">
<div class="confirm">Documents uploadés avec succès.<br><br><br>[<a href="javascript:window.opener.location.reload();window.close()">Fermer la fenêtre</a>]</div>
</div>


<?php

}

require(ADMIN . 'tail.php');

?>
