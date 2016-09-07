<?php

/*================================================================/

 Techni-Contact V4 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 13 novembre 2006

 Mises à jour :

 Fichier : /secure/manager/commandes/index.php
 Description : Accueil gestion des commandes clients
 
/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

require(ADMIN  . 'statut.php');

$title = $navBar = 'Gestion des Commandes Clients';

require(ADMIN . 'head.php');

$findType = isset($_GET['findType']) ? trim($_GET['findType']) : '';
$findText = isset($_GET['findText']) ? trim($_GET['findText']) : '';
$mois     = isset($_GET['mois'])     ? trim($_GET['mois']) : '';
$statut   = isset($_GET['statut'])   ? trim($_GET['statut']) : '';

$lastpage = isset($_GET['lastpage']) ? trim($_GET['lastpage']) : 1;
settype($lastpage, 'integer'); if ($lastpage < 1) $lastpage = 1;
$page     = isset($_GET['page'])     ? trim($_GET['page']) : 1;
settype($page, 'integer'); if ($page < 1) $page = 1;

$sort     = isset($_GET['sort'])     ? trim($_GET['sort']) : '';
$lastsort = isset($_GET['lastsort']) ? trim($_GET['lastsort']) : '';
$sortway  = isset($_GET['sortway'])  ? trim($_GET['sortway']) : '';
$error    = isset($_GET['error']) ? trim($_GET['error']) : '';

$attente_info    = isset($_GET['attenteInfo']) ? trim($_GET['attenteInfo']) : '';

switch($error) {
  case "permissions": $errorstring = "Vous n'avez pas les droits adéquats pour réaliser cette opération."; break;
  default: $errorstring = ""; break;
}

$query = '';
$ShowCommandSearchDB = $ShowCommandFilterDB = false;

if ($findText != '') {
	switch($findType) {
		case '1' : // par référence
			if (preg_match('/^[1-9]{1}[0-9]{0,8}$/', $findText))
				$query .= " where c.id = '" . $findText . "'";
			else
				$errorstring .= "- La référence saisie de la commande à rechercher est invalide<br />\n";
			break;
			
		case '2' : // par date
			$date = explode('/', $findText);
			if (count($date) == 3 && preg_match('/^(0[1-9])|(1\d)|(2\d)|(3[0-1])$/', $date[0]) && preg_match('/^(0[1-9])|(1[0-2])$/', $date[1]) &&
				((preg_match('/^\d{4}$/', $date[2])) || (preg_match('/^\d{2}$/', $date[2]))))
				$query .= " where c.create_time >= '" . mktime(00,00,00,$date[1],$date[0],$date[2]) . "' and c.create_time <= '" . mktime(23,59,59,$date[1],$date[0],$date[2]) ."'";
			else
				$errorstring .= "- Le format de la date de recherche saisie est invalide<br />\n";
			break;
			
		case '3' : // par nom de société cliente
			$query .= " where cl.societe = '" . $handle->escape($findText) . "'";
			break;
			
		default :
			$errorstring .= "- Ce type de recherche n'existe pas.<br />\n";
	}
	$ShowCommandSearchDB = true;
}

if (!empty($mois)) {
	$query .= $query == ''  ? " where " : " and ";
	if (preg_match('/^[0-9]{1,3}$/', $mois)) {
		list($curY, $curM) = explode('-', date('Y-m'));
		$query .= " c.create_time >= '" . mktime(00,00,00,($curM + 1 - $mois),01,$curY) . "' and c.create_time < '" . mktime(00,00,00,($curM + 2 - $mois),01,$curY) ."'";
	}
	else {
		$mois = '0';
		$errorstring .= "- Le mois choisi pour filtrage est invalide<br />\n";
	}
	$ShowCommandFilterDB = true;
}

if (!empty($statut)) {
	$query .= $query == ''  ? " where " : " and ";
	switch ($statut) {
		case '1' : $query .= " c.statut_traitement < 10"; break;
		case '2' : $query .= " c.statut_traitement >= 10 and c.statut_traitement < 20"; break;
		case '3' : $query .= " c.statut_traitement >= 20  and c.statut_traitement < 30 "; break;//  c.statut_traitement = 20 or (c.statut_traitement >= 22 and c.statut_traitement < 30) not to take open_sav into account
		case '4' : $query .= " c.statut_traitement >= 30"; break;
                case '5' : $query .= " c.statut_traitement = 21"; break;
		default  : $statut = '0';  $errorstring .= "- Le statut choisi pour filtrage est invalide<br />\n";
	}
	$ShowCommandFilterDB = true;
}

if (!empty($attente_info)) {
	$query .= $query == ''  ? " where " : " and ";
	$query .= " c.attente_info = '" . __MSGR_CTXT_CUSTOMER_TC_CMD__ ."'";
	$ShowCommandFilterDB = false;
}

$queryC = "select count(c.id) from commandes c left join clients cl on c.idClient = cl.id " . $query;
$res = & $handle->query($queryC, __FILE__, __LINE__);
$record = & $handle->fetch($res);
$nbcmd = $record[0];

define('NB', 30);

if (($page-1) * NB >= $nbcmd) $page = ($nbcmd - $nbcmd%NB) / NB + 1;
if (($lastpage-1) * NB >= $nbcmd) $lastpage = ($nbcmd - $nbcmd%NB) / NB + 1;

$query = "select c.id, c.idClient, c.coord, c.create_time, c.timestamp, c.totalHT, c.totalTTC, c.fdp, c.fdp_tva, c.campaignID, c.type_commande, c.statut_paiement, c.statut_traitement, c.planned_delivery_date, c.cancel_reason, c.open_sav, c.close_sav, c.dispatch_time, attente_info from commandes c left join clients cl on c.idClient = cl.id" . $query . " order by ";

// ordre de tri
if ($sort == $lastsort && $sort != '') {
	if ($lastpage == $page) $sortway = ($sortway == 'asc' ? 'desc' : 'asc');
	else $sortway = ($sortway == 'asc' ? 'asc' : 'desc');
}
else $sortway = 'asc';
switch ($sort) {
        case 'TypeCommande'    : $query .= "c.type_commande " . $sortway . ", c.create_time desc, c.id"; break;
	case 'Ref'              : $query .= "c.id " . $sortway; break;
	case 'CustomerID'       : $query .= "c.idClient " . $sortway . ", c.id"; break;
	case 'CustomerCpny'     : $query .= "cl.societe " . $sortway . ", c.create_time desc, c.id"; break;
	case 'Date'             : $query .= "c.create_time " . ($sortway == 'asc' ? 'desc' : 'asc') . ", c.id"; break;
	case 'PaymentStatus'    : $query .= "c.statut_paiement " . $sortway . ", c.create_time desc, c.id"; break;
	case 'ProcessingStatus' : $query .= "c.statut_traitement " . $sortway . ", c.create_time desc, c.id"; break;
	case 'Total'            : $query .= "c.totalTTC " . $sortway . ", c.create_time desc, c.id"; break;
	default : $query .= "c.create_time " . ($sortway == 'asc' ? 'desc' : 'asc' ) . ", c.id"; $sort = 'Date';
}

$lastsort = $sort;
$lastpage = $page;

$query .= " limit " . (($page-1)*NB) . "," . NB;

$result = $handle->query($query, __FILE__, __LINE__);

?>
<link type="text/css" rel="stylesheet" href="<?php echo ADMIN_URL ?>ressources/css/command.css">
<script src="../js/ManagerFunctions.js" type="text/javascript"></script>
<div class="titreStandard">Liste des Commandes</div>
<br />
<div class="bg" style="min-width: 980px">
<script type="text/javascript">
var __ADMIN_URL__ = '<?php echo ADMIN_URL ?>';

function swap_cbtn(img, action)
{
	switch (action)
	{
		case 'out': if (img.src == __ADMIN_URL__ + 'ressources/window_close_down.gif') { img.src = __ADMIN_URL__ + 'ressources/window_close.gif'; } break;
		case 'down': img.src = __ADMIN_URL__ + 'ressources/window_close_down.gif'; break;
		case 'up':
			if (img.src == __ADMIN_URL__ + 'ressources/window_close_down.gif')
			{
				img.src = __ADMIN_URL__ + 'ressources/window_close.gif';
				eval('Hide' + img.parentNode.parentNode.id+'();');
			}
			break;
		default: break;
	}
}

function editcommande(value)
{
	document.location.href = 'CommandMain.php?<?php echo $sid ?>&commandID=' + value;
}

function gotoPage(page)
{
	if (!isNaN(page = parseInt(page)))
	{
		document.CommandList.page.value = page;
		document.CommandList.submit();
	}
}

function FindCommand()
{
	if (document.getElementById('CommandFilterDB').style.display != 'inline')
	{
		document.CommandList.mois.value = '0';
		document.CommandList.statut.value = '0';
	}
	document.CommandList.lastsort.value = '';
	document.CommandList.submit();
}

function FilterCommand()
{
	if (document.getElementById('CommandSearchDB').style.display != 'inline')
	{
		document.CommandList.findType.value = '1';
		document.CommandList.findText.value = '';
	}
	document.CommandList.lastsort.value = '';
	document.CommandList.submit()
}

function CommandSort(order)
{
	document.CommandList.sort.value = order;
	document.CommandList.submit();
}

function ShowCommandSearchDB()
{
	if (document.getElementById('CommandSearchDB').style.display != 'inline')
	{
		document.getElementById('CommandSearchDB').style.display = 'inline';
		document.getElementById('CommandSearchDBShad').style.display = 'inline';
	}
}

function HideCommandSearchDB()
{
	document.getElementById('CommandSearchDBShad').style.display = 'none';
	document.getElementById('CommandSearchDB').style.display = 'none';
}

function ShowCommandFilterDB()
{
	if (document.getElementById('CommandFilterDB').style.display != 'inline')
	{
		document.getElementById('CommandFilterDB').style.display = 'inline';
		document.getElementById('CommandFilterDBShad').style.display = 'inline';
	}
}

function HideCommandFilterDB()
{
	document.getElementById('CommandFilterDBShad').style.display = 'none';
	document.getElementById('CommandFilterDB').style.display = 'none';
}

function ShowAllCommands()
{
	document.CommandList.findType.value = '1';
	document.CommandList.findText.value = '';
	document.CommandList.mois.value = '0';
	document.CommandList.statut.value = '0';
	document.CommandList.lastsort.value = '';
        document.CommandList.attenteInfo.value = '0';
	document.CommandList.submit();
}

function ShowOpenConversation()
{
	document.CommandList.findType.value = '1';
	document.CommandList.findText.value = '';
	document.CommandList.mois.value = '0';
	document.CommandList.statut.value = '0';
	document.CommandList.lastsort.value = '';
        document.CommandList.attenteInfo.value = '1';
	document.CommandList.submit();
}

</script>
	<form name="CommandList" method="get" action="index.php?<?php echo $sid ?>">
		<div>
			<input type="hidden" name="page" value="<?php echo $page ?>" />
			<input type="hidden" name="lastpage" value="<?php echo $lastpage ?>" />
			<input type="hidden" name="sort" value="<?php echo $sort ?>" />
			<input type="hidden" name="lastsort" value="<?php echo $lastsort ?>" />
			<input type="hidden" name="sortway" value="<?php echo $sortway ?>" />
                        <input type="hidden" name="attenteInfo" value="<?php echo $attenteInfo ?>" />
		</div>
		<div id="CommandSearchDBShad"></div>
		<div id="CommandSearchDB">
			<div class="window_title_bar">
				<img class="wtb_close_img" src="../ressources/window_close.gif" onMouseDown="swap_cbtn(this,'down')" onMouseOut="swap_cbtn(this,'out')" onMouseUp="swap_cbtn(this,'up')" />			
				<div onmousedown="grab(document.getElementById('CommandSearchDB'), document.getElementById('CommandSearchDBShad'))">
					<img class="wtb_move_img" src="../ressources/window_move.gif" />
					<div class="wtb_text">Recherche d'une commande</div>
					<div class="zero"></div>
				</div>
			</div>
			<div class="window_bg_small">
				<input name="findText" type="text" value="<?php echo $findText ?>">
				<select name="findType">
					<option value="1"<?php echo $findType == 1 ? ' selected' : '' ?>>par référence</option>
					<option value="2"<?php echo $findType == 2 ? ' selected' : '' ?>>par date (JJ/MM/AAAA)</option>
					<option value="3"<?php echo $findType == 3 ? ' selected' : '' ?>>par société</option>
				</select>
				<input type="button" class="button" style="width: 75px" value="Rechercher" onclick="FindCommand()" />
			</div>
		</div>
		<div id="CommandFilterDBShad"></div>
		<div id="CommandFilterDB">
			<div class="window_title_bar">
				<img class="wtb_close_img" src="../ressources/window_close.gif" onMouseDown="swap_cbtn(this,'down')" onMouseOut="swap_cbtn(this,'out')" onMouseUp="swap_cbtn(this,'up')" />			
				<div onmousedown="grab(document.getElementById('CommandFilterDB'), document.getElementById('CommandFilterDBShad'))">
					<img class="wtb_move_img" src="../ressources/window_move.gif" />
					<div class="wtb_text">Filtre d'affichage des commandes</div>
					<div class="zero"></div>
				</div>
			</div>
			<div class="window_bg_small">
				par mois : 
				<select name="mois">
<?php
	$listemois = array(1 => 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre');

	list($creY, $creM) = explode('-', date('Y-m', $user->create_time));
	list($curY, $curM) = explode('-', date('Y-m'));
	settype($curM, 'integer'); settype($curY, 'integer');
	settype($creM, 'integer'); settype($creY, 'integer');

	$choixMois = array('tous les mois');
	while ($curY > $creY) {
		while ($curM >= 1) {
			$choixMois[] = $listemois[$curM] . ' ' . $curY;
			$curM--;
		}
		$curM = 12;
		$curY--;
	}
	while ($curM >= $creM) {
		$choixMois[] = $listemois[$curM] . ' ' . $curY;
		$curM--;
	}

	foreach ($choixMois as $k => $v) {
		print
'					<option value="' . $k . '"' . ($mois == $k ? ' selected' : '') . '>' . $v . "</option>\n";
	}
?>
				</select>
				<input type="button" class="button" value="OK" onclick="FilterCommand()">
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;par statut : 
				<select name="statut">
					<option value="0"<?php echo $statut == '0' ? ' selected' : '' ?>>tous les statuts</option>
					<option value="1"<?php echo $statut == '1' ? ' selected' : '' ?>>non validées</option>
					<option value="2"<?php echo $statut == '2' ? ' selected' : '' ?>>non consultées</option>
					<option value="3"<?php echo $statut == '3' ? ' selected' : '' ?>>en cours de traitement</option>
                                        <option value="5"<?php echo $statut == '5' ? ' selected' : '' ?>>SAV ouvert</option>
					<option value="4"<?php echo $statut == '4' ? ' selected' : '' ?>>envoyées</option>
				</select>
				<input type="button" class="button" value="OK" onclick="FilterCommand()">
			</div>
		</div>
<?php	if ($nbcmd > NB) {
				$lastpage = ceil($nbcmd/NB) ?>
		<div class="listing" style="float: right">
			<span style="visibility: <?php echo $page > 2 ? 'visible' : 'hidden' ?>"><a href="javascript: gotoPage(1)">&lt;&lt;</a></span>
			<span style="visibility: <?php echo $page > 1 ? 'visible' : 'hidden'?>"><a href="javascript: gotoPage(<?php echo ($page-1)  ?>)">&lt;</a> ... |</span>
			<span style="visibility: <?php echo $page > 1 ? 'visible' : 'hidden'?>"><a href="javascript: gotoPage(<?php echo ($page-1) ?>)"><?php echo ($page-1)  ?></a> |</span>
			<span class="listing-current"><?php echo $page ?></span>
			<span style="visibility: <?php echo $page < $lastpage ? 'visible' : 'hidden'?>">| <a href="javascript: gotoPage(<?php echo ($page+1) ?>)"><?php echo ($page+1)  ?></a></span>
			<span style="visibility: <?php echo $page < $lastpage ? 'visible' : 'hidden'?>">| ... <a href="javascript: gotoPage(<?php echo ($page+1)  ?>)">&gt;</a></span>
			<span style="visibility: <?php echo $page < $lastpage-1 ? 'visible' : 'hidden'?>"><a href="javascript: gotoPage(<?php echo $lastpage  ?>)">&gt;&gt;</a></span>
		</div>
<?php	} ?>
		<div>
			<div class="blocka"><a href="javascript: document.location.href='CommandCreate.php?<?php echo $sid ?>'">Créer une nouvelle Commande</a></div>
			<div class="blocka"><a href="javascript: ShowCommandSearchDB()">Rechercher une commande</a></div>
			<div class="blocka"><a href="javascript: ShowCommandFilterDB()">Filtrer les commandes</a></div>
                        <div class="blocka"><a href="javascript: ShowOpenConversation()">Voir les conversations ouvertes</a></div>
			<div class="blocka"><a href="javascript: ShowAllCommands()">Voir toutes les commandes</a></div>
		</div>
		<br />
		<div class="zero"></div>
<?php	if ($errorstring != "") { ?>
		<div style="color: #FF0000"><?php echo $errorstring ?></div>
<?php	} ?>
		<table id="liste_commandes" class="liste_cmd" cellspacing="0" cellpadding="0">
			<thead>
				<tr>
                                  <th width="01%"></th>
                                  <th width="05%"><a href="javascript: CommandSort('TypeCommande')">Type Commande</a></th>
					<th width="05%"><a href="">ID Campagne</a></th>
					<th width="05%"><a href="javascript: CommandSort('Ref')">réf.</a></th>
					<th width="05%"><a href="javascript: CommandSort('CustomerID')">n° client</a></th>
					<th width="18%"><a href="javascript: CommandSort('CustomerCpny')">société</a></th>
					<th width="11%"><a href="javascript: CommandSort('Date')">date</a></th>
					<th width="22%"><a href="javascript: CommandSort('PaymentStatus')">Statut paiement</a></th>
					<th width="21%"><a href="javascript: CommandSort('ProcessingStatus')">Statut traitement</a></th>
					<th width="07%"><a href="javascript: CommandSort('Total')">Total TTC</a></th>
				</tr>
			</thead>
			<tbody>
<?php
while ($cmd = & $handle->fetchAssoc($result))
{

	$CustomerInfos = mb_unserialize($cmd['coord']);
	// We add the fdp for the commands before 8/10/2008 04:00:00
	if ($cmd["create_time"] < 1223438400)
		$cmd["totalTTC"] += $cmd["fdp"] + round(($cmd["fdp"] * $cmd["fdp_tva"])/100, 2);
?>
				<tr onclick="editcommande(<?php echo $cmd["id"] ?>)" class="<?php echo $cmd["statut_paiement"] >= 10 ? "paye" : "normal" ?>">
                                  <td><?php echo $cmd["attente_info"] ? '<img src="'.SECURE_RESSOURCES_URL.'images/email_go.png" alt="conv" style="height: 12px; vertical-align: middle" />' : '' ?></td>
                                  <td><?php echo getTypeCommande($cmd["type_commande"]) ?></td>
					<td><?php echo $cmd["campaignID"] ?></td>
					<td><?php echo $cmd["id"] ?></td>
					<td><?php echo $cmd["idClient"] ?></td>
					<td><?php echo $CustomerInfos["societe"] ?></td>
					<td><?php echo date("d/m/Y à H:i", $cmd["create_time"]) ?></td>
					<td><?php echo getStatutPaiement($cmd["statut_paiement"]) ?></td>
					<td class="<?php echo $cmd["statut_traitement"] <= 20 ? "status_processing" : "status" ?>">
						<?php echo getStatutTraitementGlobal($cmd["statut_traitement"]) ?>
						<?php echo ($cmd["statut_traitement"] == 40 && $cmd["dispatch_time"] != 0 ? "(" . date("d/m/Y", $cmd["dispatch_time"]) .")" : "") ?>
            <?php echo $cmd["planned_delivery_date"].$cmd["cancel_reason"].$cmd["open_sav"].$cmd["close_sav"] ?>
					</td>
					<td class="price"><?php echo sprintf("%.02f", $cmd["totalTTC"]) ?>€</td>
				</tr>
<?php
}
?>
			</tbody>
		</table>
<script type="text/javascript">
<?php
if ($ShowCommandSearchDB) print "ShowCommandSearchDB();\n";
if ($ShowCommandFilterDB) print "ShowCommandFilterDB();\n";
?>

trs = document.getElementById('liste_commandes').getElementsByTagName('tr');
for (var i = 1; i < trs.length; i++)
{
	trs[i].classNameNormal = trs[i].className;
	trs[i].classNameHover = trs[i].className + '_hover';
	trs[i].onmouseover = function() { this.className = this.classNameHover; }
	trs[i].onmouseout = function() { this.className = this.classNameNormal; }
}
</script>
<?php
if ($nbcmd > NB)
{
?>
		<div class="listing">
			<span style="visibility: <?php echo $page > 2 ? 'visible' : 'hidden' ?>"><a href="javascript: gotoPage(1)">&lt;&lt;</a></span>
			<span style="visibility: <?php echo $page > 1 ? 'visible' : 'hidden'?>"><a href="javascript: gotoPage(<?php echo ($page-1)  ?>)">&lt;</a> ... |</span>
			<span style="visibility: <?php echo $page > 1 ? 'visible' : 'hidden'?>"><a href="javascript: gotoPage(<?php echo ($page-1) ?>)"><?php echo ($page-1)  ?></a> |</span>
			<span class="listing-current"><?php echo $page ?></span>
			<span style="visibility: <?php echo $page < $lastpage ? 'visible' : 'hidden'?>">| <a href="javascript: gotoPage(<?php echo ($page+1) ?>)"><?php echo ($page+1)  ?></a></span>
			<span style="visibility: <?php echo $page < $lastpage ? 'visible' : 'hidden'?>">| ... <a href="javascript: gotoPage(<?php echo ($page+1)  ?>)">&gt;</a></span>
			<span style="visibility: <?php echo $page < $lastpage-1 ? 'visible' : 'hidden'?>"><a href="javascript: gotoPage(<?php echo $lastpage  ?>)">&gt;&gt;</a></span>
		</div>
<?php
}
?>
	</form>
</div>
<?php

require(ADMIN . 'tail.php');

?>
