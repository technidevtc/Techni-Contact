<?php

/*================================================================/

 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 2 avril 2006

 Mises à jour :

 Fichier : /secure/manager/tva/index.php
 Description : Accueil gestion des taux de TVA

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
require(ADMIN . 'tva.php');

$title = $navBar = 'Gestion des taux de TVA';
require(ADMIN . 'head.php');


///////////////////////////////////////////////////////////////////////////

$error = false;
$errorstring = '';

if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	if($user->rank == HOOK_NETWORK || $user->rank == COMMADMIN)
	{
	    $intitule = isset($_POST['intitule']) ? substr(trim($_POST['intitule']), 0, 255) : '';
	    $taux = isset($_POST['taux']) ? substr(trim($_POST['taux']), 0, 255) : '';
		$TVAisDft = isset($_POST['TVAisDft']) ? ($_POST['TVAisDft'] != '' ? '1' : '0') : '0';
		
	    if($intitule == '')
	    {
	        $error = true;
	        $errorString .= '- Vous n\'avez pas saisi d\'intitulé<br>';
		}
		
	    if($taux == '')
	    {
	        $error = true;
	        $errorString .= '- Vous n\'avez pas saisi de taux<br>';
		}
		elseif(!preg_match('/^[0-9]+((\.|\,)[0-9]{0,5})?$/',$taux))
		{
			$error = true;
			$errorString .= '- Le taux de TVA saisi est invalide <br>';
		}
		
	}
	else
	{
		$error = true;
		$errorString .= "- Vous n'avez pas les droits requies pour effectuer cette opération <br />\n";
	}
	
    if(!$error)
    {
		$ok = addTVA($handle, $intitule, $taux, $TVAisDft);
	}
}
else
{
	$intitule = '';
	$taux = '';
	$TVAisDft = '0';
}

$listeTVAs = & displayTVAs($handle, ' order by taux desc');
$idTVAdft = getConfig($handle, 'idTVAdft');

///////////////////////////////////////////////////////////////////////////


?>
<link rel="stylesheet" type="text/css" href="tva.css" />
<div class="titreStandard">Liste des taux de TVA</div><br>
<div class="bg">
 <table id="TauxTVA" cellpadding="0" cellspacing="0" border="0">
  <thead>
   <tr><th style="width: 240px">Intitulé</th><th style="width: 100px">Taux</th></tr>
  </thead>
  <tbody>
<?php
	
	foreach ($listeTVAs as $k => $v)
    {
        $tdbgcolor = ($k%2) ? 'F4FAFF' : 'DDE3EC'; //E9EFF8
		print('<tr><td style="background-color: #' . $tdbgcolor .  '">' . to_entities($v[1]) . (($idTVAdft == $v[0]) ? ' (par défaut)' : '') . '</td><td style="background-color: #' . $tdbgcolor .  '">' . to_entities($v[2]) . '%</td><td class="modTVA"><a href="edit.php?id=' . $v[0] . '&' . session_name() . '=' . session_id() . '">modifier</a></td></tr>' . "\n");
    }
	
?>
  </tbody>
 </table>
</div>
<br><br><div class="titreStandard">Ajouter un nouveau taux de TVA</div><br>
<div class="bg"><?php

$next = true;

if($_SERVER['REQUEST_METHOD'] == 'POST')
{
    if(!$error)
    {
        if($ok)
        {
            $out = 'Taux de TVA créé avec succès.';
        }
        else
        {
            $out = 'Erreur lors de la création du taux de TVA';
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


?>
<script type="text/javascript">
<!--

//-->
</script>

<form name="addTVA" method="post" action="index.php?<?php print(session_name() . '=' . session_id()) ?>" class="formulaire" enctype="multipart/form-data">
<table>
 <tr><td class="intitule">Intitulé :</td><td><input class="champstexte" type="text" size="25" maxlength="255" name="intitule" onBlur="this.value = trim(this.value)" value="<?php print(to_entities($intitule)) ?>"> *</td></tr>
 <tr><td class="intitule">Taux :</td><td><input class="champstexte" type="text" size="25" maxlength="255" name="taux" onBlur="this.value = trim(this.value)" value="<?php print(to_entities($taux)) ?>"> *</td></tr>
 <tr><td class="intitule">Par défaut :</td><td><input class="champstexte" type="checkbox" name="TVAisDft"<?php if($TVAisDft == '1') print(' checked')?>></td></tr>
</table>

<br>
<div class="commentaire">Note : * signifie que le champ est obligatoire.</div><br>
<center><input type="button" class="bouton" value="Valider" name="ok" onClick="this.form.submit(); this.disabled=true"> &nbsp; <input type="reset" value="Annuler" class="bouton" name="nok"></center>
</form>

<?php

} // fin affichage form
?>
</div>
<?php

require(ADMIN . 'tail.php');

?>
