<?php

/*================================================================/

 Techni-Contact V4 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 11 mars 2011

 Mises à jour :
 * AS IT IS REQUIRED THROUGH AJAX, THIS FILE MUST BE ENCODED IN UTF-8 *

 Fichier : /secure/manager/clients/estimatesList.php
 Description : Affichage de la liste des devis d'un client
 
/=================================================================*/

if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

$handle = DBHandle::get_instance();

$user = new BOUser();

if(!$user->login())
{
	print "CustomerError" . __ERRORID_SEPARATOR__ . "Votre session a expirée, vous devez vous relogger." . __ERROR_SEPARATOR__ . __MAIN_SEPARATOR__;
	exit();
}
if (!$user->get_permissions()->has("m-comm--sm-customers","e")) {
  print "CustomerError" . __ERRORID_SEPARATOR__ . "Vous n'avez pas les droits adéquats pour réaliser cette opération." . __ERROR_SEPARATOR__ . __MAIN_SEPARATOR__;
  exit();
}

$clientID = empty ($clientID) && $_GET['customerID'] ? $_GET['customerID'] : '';
if ($clientID != '') {
  if (preg_match('/^\d+$/',$clientID)) {
    $clientInfos = new CustomerUser($handle,$clientID);
    if ($clientInfos === false) {
      $error = true;
      $errorstring .= "- Il n'existe pas de client ayant pour num&eacute;ro identifiant ".$clientID."<br />\n";
      exit;
    } else {
      $page = 'client';
    }
  } else {
    $error = true;
    $errorstring .= "- Le num&eacute;ro d'identifiant client est invalide<br />\n";
    exit;
  }
}else
  exit;

if (isset($_GET['searchcrit']) && isset($_GET['searchvalue']))
{
	$searchcrit = isset($_GET['searchcrit']) ? trim($_GET['searchcrit']) : '';
	$searchvalue = isset($_GET['searchvalue']) ? trim($_GET['searchvalue']) : '';
	$mois = '';
}
elseif (isset($_GET['mois']))
{
	$mois   = isset($_GET['mois'])   ? trim($_GET['mois']) : '';
	$searchcrit = $searchvalue = '';
}

//$page = isset($_GET['page'])? trim($_GET['page']) : 1;
settype($page, 'integer');
if ($page < 1) $page = 1;

$errorstring = '';

$query = "";

//if ($searchcrit != '' && $searchvalue != '')
//{
//	switch($searchcrit)
//	{
//		case '1' : // par référence
//			if (preg_match('/^\d+$/', $searchvalue))
//				$query = " and p.estimate = '" . $searchvalue . "'";
//			else
//				$errorstring .= "- La rÃ©fÃ©rence saisie du devis à rechercher est invalide<br />\n";
//			break;
//
//		case '2' : // par date
//			$date = explode('/', $searchvalue);
//			if (count($date) == 3 && preg_match('/^(0[1-9])|(1\d)|(2\d)|(3[0-1])$/', $date[0]) && preg_match('/^(0[1-9])|(1[0-2])$/', $date[1]) &&
//				((preg_match('/^\d{4}$/', $date[2])) || (preg_match('/^\d{2}$/', $date[2]))))
//				$query = " and p.create_time >= '" . mktime(00,00,00,$date[1],$date[0],$date[2]) . "' and p.create_time <= '" . mktime(23,59,59,$date[1],$date[0],$date[2]) ."'";
//			else
//				$errorstring .= "- Le format de la date de recherche saisie est invalide<br />\n";
//			break;
//
//		case '3' : // par nom de sociÃ©tÃ© cliente
//			$query = " and p.idClient = cl.id and cl.societe = '" . $handle->escape($searchvalue) . "'";
//			break;

//		default :
//			$query = " and p.idClient = '" . $handle->escape($clientID) . "'"; //911103495
//			break;
//	}
//	if ($errorstring != '')
//	{
//		$searchcrit = $searchvalue = '';
//	}
//
//}
//elseif ($mois != '')
//{
//	if (preg_match('/^[0-9]{1,3}$/', $mois))
//	{
//		if ($mois != '0')
//		{
//			list($curY, $curM) = explode('-', date('Y-m'));
//			$query = " and p.create_time >= '" . mktime(00,00,00,($curM + 1 - $mois),01,$curY) . "' and p.create_time < '" . mktime(00,00,00,($curM + 2 - $mois),01,$curY) ."'";
//		}
//		else $query = "";
//	}
//	else
//	{
//		$mois = "";
//		$errorstring .= "- Le mois choisi pour filtrage est invalide<br />\n";
//	}
//}
$query .= " and p.estimate != 0";

$queryC = "select count(p.id) from paniers p, clients c where p.idClient =  c.id and p.idClient = '".$clientID."' " . $query;
$res = & $handle->query($queryC, __FILE__, __LINE__);
$record = & $handle->fetch($res);
$nbesti = $record[0];

define('NB', 20);

if (($page-1) * NB >= $nbesti) $page = ($nbesti - $nbesti%NB) / NB + 1;

$query = "select p.id, p.estimate, p.idClient, c.societe, p.create_time, p.timestamp, p.totalHT, p.totalTTC from paniers p, clients c where p.idClient = c.id and p.idClient = '".$clientID."' " . $query;
$query .= " order by p.create_time desc, p.timestamp " ;

$result = & $handle->query($query, __FILE__, __LINE__);

?>
<br />
<div class="bg">
  
  <div class="block">
    <div class="title">Historique devis PDF</div>

  </div>

  <input type="button" class="button" value="Nouveau Devis" onClick="document.location.href='../devis/EstimateCreate.php'" />
  <br />
  <br />
  <script type="text/javascript">
  <!--
  function editdevis(value)
  {
  	document.location.href = '../devis/EstimateMain.php?<?php echo $sid ?>&estimateID=' + value + '&type=edit';
  }

  function changeRowColor(elementName)
  {
          trs = document.getElementById(elementName).getElementsByTagName('tr');
          for (var i=1; i < trs.length; i++)
          {
                  trs[i].onmouseover = function() { this.style.backgroundColor = "#FFE28D"; }
                  trs[i].onmousedown = function() { this.style.backgroundColor = "#FFC36F"; }
                  if (i%2 == 1)
                  {
                          trs[i].style.backgroundColor = '#FFFFFF';
                          trs[i].onmouseout  = function() { this.style.backgroundColor = "#FFFFFF"; }
                          trs[i].onmouseup   = function() { this.style.backgroundColor = "#FFFFFF"; }
                  }
                  else
                  {
                          trs[i].style.backgroundColor = '#F2F2F2';
                          trs[i].onmouseout  = function() { this.style.backgroundColor = "#F2F2F2"; }
                          trs[i].onmouseup   = function() { this.style.backgroundColor = "#F2F2F2"; }
                  }
          }
  }

  function initChangeRowColor()
  {
          changeRowColor('liste_devis');
  }

  document.onload = initChangeRowColor;

  //function gotoPage(page)
  //{
  //	if (!isNaN(page = parseInt(page)))
  //	{
  //		document.listing.page.value = page;
  //		document.listing.submit();
  //	}
  //}

  //-->
  </script>
  <?php
  if ($handle->numrows($result, __FILE__, __LINE__) > 0) {
  ?>
          <div style="max-width: 1000px">
                  <table id="liste_devis" class="liste_cmd" cellspacing="0" cellpadding="0">
                          <thead>
                                  <tr>
                                          <th style="width: 90px">Réf.</th>
                                          <th style="width: 90px">ID Client</th>
                                          <th style="width: 300px">Société</th>
                                          <th style="width: 125px">Créé le</th>
                                          <th style="width: 125px">Modif. le</th>
                                          <th style="width: 90px">Total TTC</th>
                                  </tr>
                          </thead>
                          <tbody>
  <?php	while ($esti = & $handle->fetchAssoc($result)) { ?>
                                  <tr onclick="editdevis('<?php echo $esti["id"] ?>')" style="cursor: pointer">
                                          <td style="text-align: center"><?php echo $esti["estimate"] ?></td>
                                          <td style="text-align: center"><?php echo $esti["idClient"] ?></td>
                                          <td style="text-align: center"><?php echo utf8_encode($esti["societe"]) ?></td>
                                          <td><?php echo date("d/m/Y à H:i", $esti["create_time"]) ?></td>
                                          <td><?php echo date("d/m/Y à H:i", $esti["timestamp"]) ?></td>
                                          <td style="font-weight: bold"><?php echo sprintf("%.02f", $esti["totalTTC"]) ?>€</td>
                                  </tr>
  <?php	} ?>
                          </tbody>
                  </table>
  <?php
          if ($nbesti > NB) {
                  $lastpage = ceil($nbesti/NB);
  ?>
  <!--		<form name="listing" method="get" action="index.php?<?php echo $sid ?>">
  <?php
                  if ($searchcrit != '' && $searchvalue != '') {
  ?>
                          <input type="hidden" name="searchcrit" value="<?php echo $searchcrit ?>" />
                          <input type="hidden" name="searchvalue" value="<?php echo $searchvalue ?>" />
  <?php
                  }
                  else {
                          if ($mois != '')   { print '			<input type="hidden" name="mois" value="' . $mois . "\"/>\n"; }
                  }
  ?>
                          <input type="hidden" name="page" value="<?php echo $page ?>" />
                          <div class="listing">
                                  <span style="visibility: <?php echo $page > 2 ? 'visible' : 'hidden' ?>"><a href="javascript: gotoPage(1)">&lt;&lt;</a></span>
                                  <span style="visibility: <?php echo $page > 1 ? 'visible' : 'hidden'?>"><a href="javascript: gotoPage(<?php echo ($page-1)  ?>)">&lt;</a> ... |</span>
                                  <span style="visibility: <?php echo $page > 1 ? 'visible' : 'hidden'?>"><a href="javascript: gotoPage(<?php echo ($page-1) ?>)"><?php echo ($page-1)  ?></a> |</span>
                                  <span class="listing-current"><?php echo $page ?></span>
                                  <span style="visibility: <?php echo $page < $lastpage ? 'visible' : 'hidden'?>">| <a href="javascript: gotoPage(<?php echo ($page+1) ?>)"><?php echo ($page+1)  ?></a></span>
                                  <span style="visibility: <?php echo $page < $lastpage ? 'visible' : 'hidden'?>">| ... <a href="javascript: gotoPage(<?php echo ($page+1)  ?>)">&gt;</a></span>
                                  <span style="visibility: <?php echo $page < $lastpage-1 ? 'visible' : 'hidden'?>"><a href="javascript: gotoPage(<?php echo $lastpage  ?>)">&gt;&gt;</a></span>
                          </div>
                  </form>-->
          </div>
  <?php
          }

          if ($errorstring != '')
                  print '	<div style="color: #FF0000">' . $errorstring . "</div>\n";
  }
  else {
  ?>
          <br />
          <h4>Il n'existe aucun devis pour ce client.</h4>
          <br />
  <?php
  }
  ?>
          <br />
	
</div>
<!--<br />
<div class="titreStandard">Rechercher un devis</div>
<br />
<div class="bg" style="text-align: center;">
	<form method="get" action="index.php?<?php echo $sid ?>">
		<input name="searchvalue" type="text" value="<?php echo $searchvalue ?>">
		<select name="searchcrit">
			<option value="1"<?php echo $searchcrit == 1 ? ' selected' : '' ?>>par rÃ©fÃ©rence</option>
			<option value="2"<?php echo $searchcrit == 2 ? ' selected' : '' ?>>par date (JJ/MM/AAAA)</option>
			<option value="3"<?php echo $searchcrit == 3 ? ' selected' : '' ?>>par sociÃ©tÃ©</option>
		</select>
		<input class="button" value="Rechercher" type="submit">
	</form>
</div>
<br />
<div class="titreStandard">Filtre d'affichage des devis</div>
<br />-->
<!--<div class="bg" style="text-align: center">
	<form method="get" action="index.php?<?php echo $sid ?>">
		par mois : 
		<select name="mois">
<?php

//	$listemois = array(1 => 'janvier', 'fÃ©vrier', 'mars', 'avril', 'mai', 'juin', 'juillet', 'aoÃ»t', 'septembre', 'octobre', 'novembre', 'dÃ©cembre');
//
//	list($creY, $creM) = explode('-', date('Y-m', $user->create_time));
//	list($curY, $curM) = explode('-', date('Y-m'));
//	settype($curM, 'integer'); settype($curY, 'integer');
//	settype($creM, 'integer'); settype($creY, 'integer');
//
//	$choixMois = array('tous les mois');
//	while ($curY > $creY)
//	{
//		while ($curM >= 1)
//		{
//			$choixMois[] = $listemois[$curM] . ' ' . $curY;
//			$curM--;
//		}
//		$curM = 12;
//		$curY--;
//	}
//	while ($curM >= $creM)
//	{
//		$choixMois[] = $listemois[$curM] . ' ' . $curY;
//		$curM--;
//	}
//
//	foreach ($choixMois as $k => $v)
//		print '			<option value="' . $k . '"' . ($mois == $k ? ' selected' : '') . '>' . $v . "</option>\n";
?>
		</select>
		<input class="button" value="OK" type="submit">
	</form>
</div>-->

