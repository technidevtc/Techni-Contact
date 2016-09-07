<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 20 décembre 2004

 Fichier : /secure/manager/families/edit.php
 Description : Edition d'une famille

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
require(ADMIN . 'families.php');

$title  = 'Base de données des familles';
$navBar = '<a href="index.php?SESSION" class="navig">Base de données des familles</a> &raquo; Editer une famille';
require(ADMIN . 'head.php');

if(!isset($_GET['id']) || !preg_match('/^[0-9]+$/', $_GET['id']) || $_GET['id'] < 11 || !($data = & loadFamily($handle, $_GET['id'])))
{
    print('<div class="bg"><div class="fatalerror">Identifiant famille incorrect.</div></div>');
}
else
{

$error = false;
$errorstring = '';

if($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $nom = isset($_POST['name']) ? substr(trim($_POST['name']), 0, 255) : '';
    
    if($nom == '')
    {
        $error = true;
        $errorString .= '- Vous n\'avez pas saisi le nom de la famille<br>';
    }

    else if($nom != $data[0] && !isFUnique($handle, 'name', $nom))
    {
        $error = true;
        $errorString .= '- Une famille porte déjà ce nom<br>';
    }
    
    $parent = isset($_POST['parentfamily']) ? trim($_POST['parentfamily']) : '0';

    if($parent == 0)
    {
        $error = true;
        $errorString .= '- Vous n\'avez pas sélectionné la famille parente<br>';
    }
    else if(!isForSF1($handle, $parent))
    {
        $error = true;
        $errorString .= '- Une famille ne peut avoir une sous-famille de niveau 2 comme parente<br>';
    }


    if(!$error)
    {
        if($data[0] == $nom && $data[1] == $parent)
        {
            $ok = true;
        }
        else
        {
            $ok = updateFamily($handle, $_GET['id'], $nom, $parent);
        }
    }


}
else
{
    $nom = $data[0];
    $parent = $data[1];
}

$families = & displayFamilies($handle);


// Nouveau nom affiché ou non (ancien si form mal rempli)
if(isset($ok) && $ok)
{
    $newname = $nom;
    $newparent = $parent;
}
else
{
    $newname = $data[0];
    $newparent = $data[1];
}


// Tester si produits ou SF2
if($newparent <= 11)
{
    foreach($families as $k => $v)
    {
        if(preg_match('/^' . $newparent . '<!>.*$/', $k))
        {          
            $under = & $v[$_GET['id'] . '<!>' . $newname];
            break;
        }
    }

}
else
{
    $under = & listProducts($handle, $_GET['id']);
}






// Possibilité de supprimer
if(count($under) > 0)
{
    $del = '';
}
else
{
    $del = ' - <a href="del.php?id=' . $_GET['id'] . '&' . session_name() . '=' . session_id() . '" onClick="return confirm(\'Etes-vous sûr de vouloir supprimer cette famille ?\')">Supprimer cette famille</a>';
}


?><div class="titreStandard">Edition de la famille <?php print(to_entities($newname) . $del) ?></div><br>
<div class="bg"><?php

$next = true;

if($_SERVER['REQUEST_METHOD'] == 'POST')
{
    print('<a name="infos"></a>');
    if(!$error)
    {
        if($ok)
        {
            $out = 'Famille éditée avec succès.';
        }
        else
        {
            $out = 'Erreur lors de la modification de la famille.';
        }
  
        print('<div class="confirm">' . $out . '</div><br><br>');
        
        $next = false;
        
    }
    else
    {
        print('<font color="red">Une ou plusieurs erreurs sont survenues lors de la validation : <br>' . $errorString  . '</font><br><br>');
        $next = true;
    }

}

if($next)
{


?><form name="editFamily" method="post" action="edit.php?id=<?php print($_GET['id'] .'&' . session_name() . '=' . session_id()) ?>#infos" class="formulaire" enctype="multipart/form-data">
<table><tr><td class="intitule">Nom de la famille :</td><td><input type="text" class="champstexte" name="name" size="40" maxlength="255" value="<?php print(to_entities($nom)) ?>" onBlur="this.value = trim(this.value); maj = this.value.charAt(0).toUpperCase(); if(this.value.length > 1) this.value = maj + this.value.substring(1, this.value.length); else this.value = maj"> *</td></tr>
<tr><td class="intitule">Famille parente directe : </td><td><select name="parentfamily"><option value="0"></option><?php

if(count($families) > 0)
{
    foreach($families as $k => $v)
    {

        $tab = explode('<!>', $k);

        $sel = ($tab[0] == $parent) ? 'selected' : '';
        print('<option value="' . $tab[0] . '" ' . $sel . '>' . to_entities($tab[1]) . '</option>');

        if(count($v) > 0)
        {
            foreach($v as $k_i => $v_i)
            {
                $tab = explode('<!>', $k_i);
                $sel = ($tab[0] == $parent) ? 'selected' : '';
                print('<option value="' . $tab[0] . '" ' . $sel . '>&nbsp; &nbsp; &nbsp; ' . to_entities($tab[1]) . '</option>');
            }

        }
    }
}


?></select> *</td></tr></table>
<br><br><div class="commentaire">Note : * signifie que le champ est obligatoire.</div><br><center><input type="button" class="bouton" value="Valider" name="ok" onClick="this.form.submit(); this.disabled=true"> &nbsp; <input type="reset" value="Annuler" class="bouton" name="nok"></center></form>

<?php

    if(count($under) > 0 )
    {
        if($newparent > 11)
        {
            print('</div><br><br><div class="titreStandard">Liste des produits dans cette famille</div><br><div class="bg"></ul>');
       
            foreach($under as $k => $v)
            {
                if($v[4])
                {
                    $extra = ' - ' . $v[4];
                }
                else
                {
                    $extra = '';
                }

                print('<li><a href="../products/edit.php?id=' . $v[0] . '&' . session_name() . '=' . session_id() . '">' . to_entities($v[1]). '</a> ' . $extra . ' <a href="' . URL . 'produits/' . $v[3] . '-' . $v[0] . '-' . $v[2] . '.html" target="_blank"><img src="' . ADMIN_URL . 'images/web.gif" border="0"></a>');
            }


            print('</ul>');

        }
        else
        {
            print('</div><br><br><div class="titreStandard">Liste des sous-familles de niveau 2 existantes</div><br><div class="bg"></ul>');
            
            foreach($under as $k => $v)
            {
               $tab = explode('<!>', $v);
               print('<li><a href="edit.php?id=' . $tab[0] . '&' . session_name() . '=' . session_id() . '">' . to_entities($tab[1]). '</a>');
            }

            print('</ul>');
        }
    }


} // fin affichage form

print('</div>');

} // fin id ok

require(ADMIN . 'tail.php');

?>
