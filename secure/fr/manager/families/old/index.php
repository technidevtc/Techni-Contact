<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 20 décembre 2004

 Fichier : /secure/manager/families/index.php
 Description : Accueil gestion des familles

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
require(ADMIN . 'families.php');

$title = $navBar = 'Base de données des familles';
require(ADMIN . 'head.php');


///////////////////////////////////////////////////////////////////////////

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

    else if(!isFUnique($handle, 'name', $nom))
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
        $ok = addFamily($handle, $nom, $parent);
    }


}
else
{
    $nom = '';
    $parent = '0';
}


$families = & displayFamilies($handle);

?><div class="titreStandard">Liste des familles existantes</div><br>
<div class="bg"><?php

$i = 0;

if(count($families) > 0)
{
    print('<table cellspacing=15 cellpadding=5 border=1>');

    $i = 0;
    
    foreach($families as $k => $v)
    {
        if($i++ == 0)
        {
            print('<tr>');
        }

        $tab = explode('<!>', $k);
        print('<td class="intitule" width="250" valign="top"><b>' . $tab[1] . ' : </b><br><br>');

        if(count($v) == 0)
        {
            print('Aucune sous-famille<br><br><br>');
        }
        else
        {

            foreach($v as $k_i => $v_i)
            {
                print('<ul>');

                $tab = explode('<!>', $k_i);

                print('<li> <a href="edit.php?id=' . $tab[0] . '&' . session_name() . '=' . session_id(). '">' . to_entities($tab[1]) . '</a><br>');
                
                if(count($v_i) == 0)
                {
                    print('Aucune sous-famille<br><br><br>');
                }
                else
                {

                    foreach($v_i as $k_j => $v_j)
                    {
                        print('<ul>');

                        $tab = explode('<!>', $v_j);

                        print('<li> <a href="edit.php?id=' . $tab[0] . '&' . session_name() . '=' . session_id(). '">' . to_entities($tab[1]) . '</a><br>');
                        print('</ul>');

                    }
                }

                print('</ul>');

            }
        }
        

        print('</td>');

        if($i == 3)
        {
            print('</tr>');
            $i = 0;
        }


    }
    
            
    if($i != 0)
    {
        print('</tr>');
    }

    print('</table>');
}




?></div><br><br>


<div class="titreStandard">Ajouter une nouvelle familles</div><br>
<div class="bg"><?php

$next = true;

if($_SERVER['REQUEST_METHOD'] == 'POST')
{
    print('<a name="infos"></a>');
    if(!$error)
    {
        if($ok)
        {
            $out = 'Famille créée avec succès.';
        }
        else
        {
            $out = 'Erreur lors de la création de la famille.';
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


?><form name="addFamily" method="post" action="index.php?<?php print(session_name() . '=' . session_id()) ?>#infos" class="formulaire" enctype="multipart/form-data">
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

} // fin affichage form

print('</div>');

require(ADMIN . 'tail.php');

?>
