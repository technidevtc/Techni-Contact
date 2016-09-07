<?php

/*================================================================/

 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 2 avril 2006

 Mises à jour :

 Fichier : /secure/manager/tva/edit.php
 Description : Edition taux de TVA

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
require(ADMIN."tva.php");

$title = "Gestion des taux de TVA";
$navBar = "<a href=\"index.php?SESSION\" class=\"navig\">Gestion des taux de TVA</a> &raquo; Editer un taux de TVA";
require(ADMIN."head.php");

///////////////////////////////////////////////////////////////////////////
if(!isset($_GET['id']) || !preg_match('/^[0-9]+$/', $_GET['id']) || !($data = & loadTVA($handle, $_GET['id']))) { ?>
  <div class="bg">
    <div class="fatalerror">Identifiant taux de TVA incorrect.</div>
  </div>
<?php
}
else {

	$error = false;
	$errorstring = '';

  if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if($user->rank == HOOK_NETWORK || $user->rank == COMMADMIN || !$user->get_permissions()->has("m-admin--sm-tva","e")) {
      $intitule = isset($_POST['intitule']) ? substr(trim($_POST['intitule']), 0, 255) : '';
      $taux = isset($_POST['taux']) ? substr(trim($_POST['taux']), 0, 255) : '';
      $TVAisDft = isset($_POST['TVAisDft']) ? ($_POST['TVAisDft'] != '' ? '1' : '0') : '0';

      if($intitule == '') {
        $error = true;
        $errorString .= '- Vous n\'avez pas saisi d\'intitulé<br>';
      }

      if($taux == '') {
        $error = true;
        $errorString .= '- Vous n\'avez pas saisi de taux<br>';
      }
      elseif(!preg_match('/^[0-9]+((\.|\,)[0-9]{0,5})?$/',$taux)) {
        $error = true;
        $errorString .= '- Le taux de TVA saisi est invalide <br>';
      }
    }
    else {
      $error = true;
      $errorstring .= "- Vous n'avez pas les droits requies pour effectuer cette opération <br/>\n";
    }

    if(!$error) {
      $ok = updateTVA($handle, $_GET['id'], $intitule, $taux, $TVAisDft, $data[1]);
    }
  }
  else {
    $intitule = $data[1];
    $taux = $data[2];
    $TVAisDft = $data[3];
  }

  $listeTVAs = displayTVAs($handle, " order by taux desc");
  $idTVAdft = getConfig($handle, "idTVAdft");


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
<?php foreach ($listeTVAs as $k => $v) { ?>
    <tr style="background-color: #<?php echo $k%2?"f4faff":"dde3ec" ?>">
      <td><?php echo to_entities($v[1]).(($idTVAdft == $v[0]) ? " (par défaut)" : "") ?></td>
      <td><?php echo to_entities($v[2]) ?>%</td>
      <td class="modTVA"><a href="edit.php?id=<?php echo $v[0] ?>">modifier</a></td>
    </tr>
<?php } ?>
  </tbody>
 </table>
</div>
<br><br>
<div class="titreStandard">Modifier un taux de TVA - 
<?php 
if (count($listeTVAs) > 1 ) // On interdit la suppression du dernier taux de TVA
{
	if ($_GET['id'] == $idTVAdft)
	{
		foreach ($listeTVAs as $k => $v)
		{
			if ($v[0] != $idTVAdft)
			{
				$newTVAdft = $v;
				break;
			}
		}
		print('<a href="del.php?id=' . $_GET['id'] . '&' . session_name() . '=' . session_id() . '" onClick="return confirm(\'Etes-vous sûr de vouloir supprimer ce taux de TVA qui est le taux de TVA par défaut pour les fournisseurs ?\n\nAttention, toutes les fiches produits ou fournisseurs avec ce taux de TVA se verront attribuer le nouveau taux de TVA par défaut \\\'' . to_entities($newTVAdft[1]) . '\\\' de ' . to_entities($newTVAdft[2]) . '% !\n\nEn cas de doute modifiez simplement un taux de TVA existant.\')">Supprimer</a>');
	}
	else
	{
		$TVAdft = & loadTVA($handle, $idTVAdft);
		print('<a href="del.php?id=' . $_GET['id'] . '&' . session_name() . '=' . session_id() . '" onClick="return confirm(\'Etes-vous sûr de vouloir supprimer ce taux de TVA ?\n\nAttention, toutes les fiches produits ou fournisseurs avec ce taux de TVA se verront attribuer le taux de TVA par défaut \\\'' . to_entities($TVAdft[1]) . '\\\' de ' . to_entities($TVAdft[2]) . '% !\n\nEn cas de doute modifiez simplement un taux de TVA existant.\')">Supprimer</a>');
	}
}
else
{
	print('<a href="del.php?id=' . $_GET['id'] . '&' . session_name() . '=' . session_id() . '" onClick="alert(\'Impossible de supprimer ce taux de TVA, car il est le seul actuellement disponible !\n\nVous pouvez simplement le modifier.\'); return false;">Supprimer</a>');
}
?>
</div>
<br>
<div class="bg">
<?php

$next = true;

if($_SERVER['REQUEST_METHOD'] == 'POST')
{
    if(!$error)
    {
        if($ok)
        {
            $out = 'Taux de TVA modifié avec succès.';
        }
        else
        {
            $out = 'Erreur lors de la modification du taux de TVA';
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

<form name="editTVA" method="post" action="edit.php?<?php print(session_name() . '=' . session_id() . '&id=' . $_GET['id']) ?>" class="formulaire" enctype="multipart/form-data">
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
}

require(ADMIN . 'tail.php');

?>
