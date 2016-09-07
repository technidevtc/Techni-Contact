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

require(ADMIN."logs.php");
require(ICLASS . 'CustomerDevis.php');
require(ICLASS . 'Command.php');
require(ADMIN  . 'customers.php');
require(ADMIN  . 'tva.php');
require(SITE   . 'commandes.php');
require(SITE   . 'devis.php');

$handle = DBHandle::get_instance();
$user = new BOUser();

if(!$user->login())
{
    header('Location: ' . ADMIN_URL . 'login.html');
    exit();
}

//header("Content-Type: text/plain; charset=iso-8859-1");
header("Content-Type: text/plain; charset=utf-8");

$sid = session_name() . '=' . session_id();

//addCustomer($handle);

$searchType    = isset($_GET['searchType'])    ? $_GET['searchType'] : '';
$clientID      = isset($_GET['clientID'])      ? trim($_GET['clientID']) : '';
//$clientName    = isset($_GET['clientName'])    ? strtoupper(stripslashes(trim($_GET['clientName']))): '';
$clientSociety = isset($_GET['clientSociety']) ? strtoupper(stripslashes(trim($_GET['clientSociety']))): '';
$commandID     = isset($_GET['commandID'])     ? trim($_GET['commandID']) : '';

$errorstring = '';

// Afficher un client ou une liste de client en fonction de critère de recherche
switch ($searchType)
{
	case 'by_ID' :
		$commandID = $clientName = $clientSociety = '';
		if ($clientID == '') $errorstring .= "Veuillez spécifier un identifiant client<br />\n";
		break;
		
	/*case 'by_name' :
		$clientID = $commandID = $clientSociety = '';
		if ($clientName == '') $errorstring .= "- Veuillez spécifier une recherche de nom<br />\n";
		else $pattern = strtoupper(stripslashes($clientName));
		break;*/
		
	case 'by_society' :
		$clientID = $commandID = $clientName = '';
		if ($clientSociety == '') $errorstring .= "Veuillez spécifier une recherche de nom de société<br />\n";
		else $pattern = strtoupper(stripslashes($clientSociety));
		break;
		
	case 'by_cmdID' :
		$clientID = $clientName = $clientSociety = '';
		if ($commandID == '') $errorstring .= "Veuillez spécifier un identifiant commande<br />\n";
		break;
		
	case '' :
		if (isset($_GET['id'])) $clientID = $_GET['id'];
		elseif (isset($_GET['idCommande'])) $commandID = $_GET['idCommande'];
		elseif (isset($_GET['idDevis'])) $devisID = $_GET['idDevis'];
		if (isset($_GET['pattern'])) $pattern = strtoupper(stripslashes($_GET['pattern']));
		break;
		
	default :
		$errorstring .= "Le type de recherche spécifié n'existe pas<br />\n";
}

if ($clientID != '')
{
	if (preg_match('/^\d+$/', $clientID))
	{
		$clientInfos = & loadCustomer($handle, $clientID);
		if ($clientInfos === false) $errorstring .= "Il n'existe pas de client ayant pour numéro identifiant " . $clientID . "<br />\n";
	}
	else $errorstring .= "Le numéro d'identifiant client est invalide<br />\n";
}
elseif ($commandID != '')
{
	if (preg_match('/^[1-9]{1}[0-9]{0,8}$/', $commandID))
	{
		$commande = & new Command($handle);
		$commande->setID($commandID);
		$clientID = $commande->getClientID();
		
		if ($clientID == false) $errorstring .= 'La commande ayant pour numéro identifiant ' . $commandID . " n'existe pas<br />\n";
		else
		{
			$clientInfos = & loadCustomer($handle, $clientID);
			if ($clientInfos === false) $errorstring .= "Le numéro client inscrit dans la commande ayant pour numéro identifiant " . $commandID . " n'existe pas<br />\n";
		}
	}
	else $errorstring .= "Le numéro d'identifiant de commande est invalide<br />\n";
}
elseif ($devisID != '')
{
	if (preg_match('/^[1-9]{1}[0-9]{0,8}$/', $devisID))
	{
		$devis = & new CustomerDevis($handle);
		$devis->setID($devisID);
		$clientID = $devis->getClientID();
		
		if ($clientID == false) $errorstring .= 'Le devis ayant pour numéro identifiant ' . $devisID . " n'existe pas<br />\n";
		else
		{
			$clientInfos = & loadCustomer($handle, $clientID);
			if ($clientInfos === false) $errorstring .= "Le numéro client inscrit dans le devis ayant pour numéro identifiant " . $devisID . " n'existe pas<br />\n";
		}
	}
	else $errorstring .= "Le numéro d'identifiant de devis est invalide<br />\n";
}

if (isset($pattern) && ($pattern != '_NUMBER_') && !preg_match("/^([a-zA-Z0-9_-]|')+$/", $pattern))
	$errorstring .= "La recherche de nom spécifiée est invalide<br />\n";

if ($errorstring != '')
{
?>
<div style="font-weight: bold; color: #B00000">
<?php echo $errorstring ?>
</div>
<?php
}
else
{
	if (isset($pattern))
	{
		if (isset($_GET['offset']) && preg_match('/^[0-9]+$/', $_GET['offset']))
			$offset = $_GET['offset'];
		else
			$offset = 0;
		
		if ($pattern == '_NUMBER_')
		{
			$pattern_sql = 'REGEXP(\'^[0-9]\')';
			$pattern_desc = 'un chiffre';
		}
		else
		{
			$pattern_sql = "like '" . $pattern . "%'";
			if (strlen($pattern) == 1) $pattern_desc = "la lettre $pattern";
			else $pattern_desc = $pattern;
		}
		
		
		$societyList = & displayCustomersSociety($handle, ' where societe ' . $pattern_sql . ' order by societe limit ' . $offset . ', ' . 100 );
		
		if (count($societyList) == 0 && $offset != 0)
		{
			$offset = 0;
			$societyList = & displayCustomersSociety($handle, ' where societe ' . $pattern_sql . ' order by societe limit ' . $offset . ', ' . 100 );
		}
		
		if (count($societyList) > 1 || (count($societyList) == 1 && $offset > 0 ))
		{
?>	<b>Liste des sociétés commençant par <?php echo $pattern_desc ?> :</b>
	<br />
	<br />
	<table cellspacing="0" cellpadding="0">
		<tr>
<?php		$id2keep = $clientID != '' ? 'id=' . $clientID . '&' : '';
			for($i = 0; $i < count($societyList) && $i < 50; $i++)
			{
				if ($i%10 == 0)
				{
?>			<td style="width: 160px; vertical-align: top; font: 12px Arial, Helvetica, sans-serif">
				<ul>
<?php			}
?>					<li><a href="Javascript: getClients('&id=<?php echo $societyList[$i]['id'] ?>&pattern=<?php echo $pattern ?>&offset=<?php echo $offset ?>')" <?php echo ($clientID == $societyList[$i]['id'] ? ' style="color: #C00000"' : '') ?>>
						<?php echo to_entities($societyList[$i]['societe']) ?></a></li>
<?php			if ($i%10 == 9)
				{
?>				</ul>
			</td>
<?php			}
			}
			if ($i%10 != 0)
			{
?>				</ul>
			</td>
<?php		}
?>		</tr>
	</table>
<?php
			if ($offset > 0)
			{
?>	<a style="float: left" href="Javascript: getClients('&<?php echo $id2keep ?>pattern=<?php echo $pattern ?>&offset=<?php echo ($offset > 50 ? $offset-50 : 0) ?>')">
	<?php echo ($offset >= 50 ? '<< 50 sociétés précédentes' : '<< 50 premières sociétés') ?></a>
<?php
			}
			
			if (count($societyList) > 50)
			{
				$clientsSuivants = count($societyList)-50;
?>	<a style="float: right" href="Javascript: getClients('&<?php echo $id2keep ?>pattern=<?php echo $pattern ?>&offset=<?php echo ($offset+50) ?>')">
	<?php echo ($clientsSuivants > 1 ? $clientsSuivants . ' sociétés suivantes' : 'la société suivante') ?>>></a>
<?php
			}
?>
	<div class="zero"></div>
<?php
			
		}
		elseif (count($societyList) == 1)
		{
?>	<b>Une société commençant par <?php echo $pattern_desc ?> : </b><a href="Javascript: getClients('&id=<?php echo $societyList[0]['id'] ?>')"><?php echo to_entities($societyList[0]['societe']) ?></a>
<?php	}
		else
		{
			print "<b>Il n'existe aucune société commençant par <span style=\"color: #B00000\">$pattern_desc</span></b><br />\n";
		}
	}
	
	if (isset($clientInfos))
	{
		require(ADMIN  . 'statut.php');
		
		switch ($clientInfos['titre'])
		{
			case 1  : $titre = 'M.'; break;
			case 2  : $titre = 'Mlle'; break;
			case 3  : $titre = 'Mme'; break;
			default : $titre = 'M.'; break;
		}
		
		switch ($clientInfos['titre_l'])
		{
			case 1  : $titre_l = 'M.'; break;
			case 2  : $titre_l = 'Mlle'; break;
			case 3  : $titre_l = 'Mme'; break;
			default : $titre_l = 'M.'; break;
		}
		
		if (isset($_POST['field_account']) && isset($_POST['value_account']))
		{
			switch ($_POST['field_account'])
			{
				case 'activated' :
					$clientInfos['actif'] = $_POST['value_account'] != '0' ? 1 : 0;
					$handle->query("update clients set actif = {$clientInfos['actif']} where id = $clientID", __FILE__, __LINE__);
					break;
			}
		}
?>
<div class="window_subtitle_bar">Informations générales sur le client n°<span id="_clientID_"><?php echo $clientID ?></span></div>
<div id="recap">
	<div class="infosR" style="width: 350px">
		<div class="infos"><div class="intitule" style="width: 200px">Date de création du compte : </div><div class="valeurR">le <?php echo date("d/m/Y à H:i.s", $clientInfos['create_time']) ?></div></div>
		<div class="infos"><div class="intitule" style="width: 200px">Dernière mise à jour du compte: </div><div class="valeurR">le <?php echo date("d/m/Y à H:i.s", $clientInfos['timestamp']) ?></div></div>
	</div>
	<div class="infosL">
		<div class="infos"><div class="intitule" style="width: 100px">Login (email) : </div><div class="valeur"><a href="mailto:<?php echo $clientInfos['login'] ?>"><?php echo $clientInfos['login'] ?></a></div></div>
		<div class="infos"><div class="intitule" style="width: 100px">Ce client est : </div><div class="valeur"><i><?php echo ($clientInfos['actif'] ? '<b style="color: #00D000">actif</b>' : '<b style="color: #D00000">non actif</b>') ?></i></div></div>
	</div>
	<div class="zero"></div>
	<br />
	<br />
	<div class="livraison">
		<div class="titreBloc">Coordonnées de livraison<?php echo $clientInfos['coord_livraison'] != 0 ? ' (activée)' : '' ?></div>
		<div class="coord">
			<b><?php echo $titre_l ?> <?php echo $clientInfos['nom_l'] ?> <?php echo $clientInfos['prenom_l'] ?></b><br />
			<?php echo $clientInfos['societe_l'] != '' ? $clientInfos['societe_l'] . '<br />' : '' ?>
			<?php echo $clientInfos['adresse_l'] ?><br />
			<?php echo $clientInfos['complement_l'] ?> <?php echo $clientInfos['cp_l'] ?> <?php echo $clientInfos['ville_l'] ?><br />
			<?php echo $clientInfos['pays_l'] ?><br />
		</div>
	</div>
	<div class="facturation">
		<div class="titreBloc">Coordonnées</div>
		<div class="coord">
			<b><?php echo $titre ?> <?php echo $clientInfos['nom'] ?> <?php echo $clientInfos['prenom'] ?></b><br />
			<?php echo $clientInfos['societe'] != '' ? $clientInfos['societe'] . '<br />' : '' ?>
			<?php echo $clientInfos['adresse'] ?><br />
			<?php echo $clientInfos['complement'] ?> <?php echo $clientInfos['cp'] ?> <?php echo $clientInfos['ville'] ?><br />
			<?php echo $clientInfos['pays'] ?><br />
			<br />
			<table cellspacing="0" cellpadding="0">
				<tr><td class="intitule">tel 1 :</td><td><?php echo $clientInfos['tel1'] ?></td></tr>
				<tr><td class="intitule">tel 2 :</td><td><?php echo $clientInfos['tel2'] ?></td></tr>
				<tr><td class="intitule">fax 1 :</td><td><?php echo $clientInfos['fax1'] ?></td></tr>
				<tr><td class="intitule">fax 2 :</td><td><?php echo $clientInfos['fax2'] ?></td></tr>
				<tr><td class="intitule">URL :</td><td><?php echo $clientInfos['url'] ?></td></tr>
			</table>
		</div>
	</div>
</div>
<?php
	}
}

?>
