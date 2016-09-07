<?php

/*================================================================/

 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 2 avril 2006

 Mises à jour :

 Fichier : /secure/manager/tva/index.php
 Description : Supprimer un taux de TVA

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
require(ADMIN . 'tva.php');

$title = 'Gestion des taux de TVA';
$navBar = '<a href="index.php?SESSION" class="navig">Gestion des taux de TVA</a> &raquo; Supprimer un taux de TVA';
require(ADMIN . 'head.php');


///////////////////////////////////////////////////////////////////////////

if(!isset($_GET['id']) || !preg_match('/^[0-9]+$/', $_GET['id']) || !($data = & loadTVA($handle, $_GET['id'])))
{
    print('<div class="bg"><div class="fatalerror">Identifiant taux de TVA incorrect.</div></div>');
}
// Seul admin / tech autorisé
else if($user->rank != HOOK_NETWORK && $user->rank != COMMADMIN)
{
    print('<div class="bg"><div class="fatalerror">Vous n\'avez pas les droits adéquats pour réaliser cette opération.</div></div>');
}
else
{
	$error = false;
	$errorstring = '';

	$listeTVAs = & displayTVAs($handle, ' order by taux desc');

	if (count($listeTVAs) <= 1 ) // On interdit la suppression du dernier taux de TVA
	{
		$error = true;
		$errorstring .= 'Impossible de supprimer le dernier taux de TVA restant.';
	}
	else
	{
		$idTVAdft = getConfig($handle, 'idTVAdft');
		
		if ($_GET['id'] == $idTVAdft) // si le taux à supprimer est celui par défaut actuellement, on en choisi un nouveau
		{
			foreach ($listeTVAs as $k => $v)
			{
				if ($v[0] != $idTVAdft)
				{
					$newTVAdft = $v;
					break;
				}
			}
			$ok = delTVA($handle, $_GET['id'], $newTVAdft[0]);
			$idTVAdft = $newTVAdft[0];
		}
		else
		{
			$ok = delTVA($handle, $_GET['id'], $idTVAdft);
		}
		
		$listeTVAs = & displayTVAs($handle, ' order by taux desc');
		
	}


?>
<link rel="stylesheet" type="text/css" href="tva.css" />
<div class="titreStandard">Liste des taux de TVA</div>
<br>
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
<br><br>
<div class="titreStandard">Suppression du taux de TVA '<?php echo to_entities($data[1]) ?>' à <?php echo to_entities($data[2]) ?>%</div>
<br>
<div class="bg">
<?php
	if (!$error)
	{
		if ($ok)
			print('<div class="confirm">Taux de TVA supprimé avec succès.</div>');
		else
			print('<div class="confirm">Erreur lors de la suppression du taux de TVA.</div>');
	}
	else
		print('<div class="confirm">' . $errorstring . '</div>');
?>
</div>

<?php
}

require(ADMIN . 'tail.php');

?>
