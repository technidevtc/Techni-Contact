<?php

/*================================================================/

 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com

 Auteur : OD pour Hook Network SARL - http://www.hook-network.com
 Date de création : février 2011

 Fichier : /secure/fr/extranet/AJAX-commandes-listes.php
 Description : résultat ajax du listing des commandes extranet

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

header("Content-Type: text/plain; charset=utf-8");

require_once(ADMIN."logs.php");
require_once(ADMIN."logo.php");
require_once(ICLASS."ExtranetUser.php");

$handle = DBHandle::get_instance();
$user = new ExtranetUser($handle);

if (!$user->login() || !$user->active) {
	$o["error"] = "Votre session a expirée, veuillez vous identifier à nouveau après avoir rafraichi votre page";
	print json_encode($o);
	exit();
}

$db = DBHandle::get_instance();
$o = array();

$NB = !empty($_GET['NB']) && is_numeric($_GET['NB']) ? $_GET['NB'] : 30;
define('NB', $NB);
//define("__BEGIN_TIME__", mktime(0,0,0,1,1,2004));
define("__BEGIN_TIME__", mktime(0,0,0,3,1,2011));

// time interval
$yearS = isset($_GET['yearS']) ? (int)trim($_GET['yearS']) : date("Y");
$monthS = isset($_GET['monthS']) ? (int)trim($_GET['monthS']) : date("m");
$dayS = isset($_GET['dayS']) ? (int)trim($_GET['dayS']) : date("d");
$yearS2 = isset($_GET['yearS2']) ? (int)trim($_GET['yearS2']) : date("Y");
$monthS2 = isset($_GET['monthS2']) ? (int)trim($_GET['monthS2']) : date("m");
$dayS2 = isset($_GET['dayS2']) ? (int)trim($_GET['dayS2']) : date("d");
$yearE = isset($_GET['yearE']) ? (int)trim($_GET['yearE']) : date("Y");
$monthE = isset($_GET['monthE']) ? (int)trim($_GET['monthE']) : date("m");
$dayE = isset($_GET['dayE']) ? (int)trim($_GET['dayE']) : date("d");


if (isset($_GET['yearS'])) {
//	$dateFilterType = "simple";
	$yearS	= (int)trim($_GET['yearS']);
	$monthS	= isset($_GET['monthS'])	? (int)trim($_GET['monthS']) : 0;
	$dayS	= isset($_GET['dayS'])		? (int)trim($_GET['dayS']) : 0;

	if (isset($_GET['yearE'])) {
//		$dateFilterType = "interval";
		$yearE	= (int)trim($_GET['yearE']);
		$monthE	= isset($_GET['monthE'])	? (int)trim($_GET['monthE']) : 0;
		$dayE	= isset($_GET['dayE'])		? (int)trim($_GET['dayE']) : 0;
	}
}

$page	= isset($_GET['page'])	? (int)trim($_GET['page']) : 1; if ($page < 1) $page = 1;
$sort     = isset($_GET['sort'])     ? trim($_GET['sort']) : '';
$lastsort = isset($_GET['lastsort']) ? trim($_GET['lastsort']) : '';
$sortway  = isset($_GET['sortway'])  ? trim($_GET['sortway']) : '';
$findText  = isset($_GET['findText'])  ? trim($_GET['findText']) : '';
$findType  = isset($_GET['findType'])  ? trim($_GET['findType']) : '';

$queryFilter = array();
$dateFilterType = isset($_GET["dateFilterType"]) ? ($_GET["dateFilterType"] == "interval" ? "interval" : "simple") : "simple";
if ($dateFilterType == "simple") {
	if ($yearS != 0) {
		if ($monthS == 0)	{ $dateStart = mktime(0,0,0,      1,    1,$yearS);	$dateEnd = mktime(0,0,0,        1,      1,$yearS+1); }
		elseif ($dayS == 0)	{ $dateStart = mktime(0,0,0,$monthS,    1,$yearS);	$dateEnd = mktime(0,0,0,$monthS+1,      1,$yearS  ); }
		else				{ $dateStart = mktime(0,0,0,$monthS,$dayS,$yearS);	$dateEnd = mktime(0,0,0,$monthS  ,$dayS+1,$yearS  ); }
	}
}
elseif ($dateFilterType == "interval") {
	if ($yearS2 != 0 && $yearE != 0) {
		if ($monthS2 == 0)   { $dateStart = mktime(0,0,0,       1,     1,$yearS2); }
		elseif ($dayS2 == 0) { $dateStart = mktime(0,0,0,$monthS2,     1,$yearS2); }
		else                 { $dateStart = mktime(0,0,0,$monthS2,$dayS2,$yearS2); }

		if ($monthE == 0)   { $dateEnd = mktime(0,0,0,      1,      1,$yearE); }
		elseif ($dayE == 0) { $dateEnd = mktime(0,0,0,$monthE,      1,$yearE); }
		else                { $dateEnd = mktime(0,0,0,$monthE,$dayE+1,$yearE); }
	}
}
if (!isset($dateStart) || !isset($dateEnd)) {
	$dateStart = __BEGIN_TIME__;
	$dateEnd = time() + 86400 - (time() % 86400);
}
// recherche entre le 01/03/2011 et aujourd'hui (eu Tristan par tel le 03/10/2011)
$dateStart = 1298930400;
$dateEnd = time();

$queryFilter[] = "(c.create_time >= ".$dateStart." AND c.create_time < ".$dateEnd.")";
$queryFilter[] = " ca.timestampIMS > ".__BEGIN_TIME__;

$limitstart = ($page - 1) * NB;

$errorstring = '';

$query = "";
if ($findText != '') {
	switch($findType) {
		case '0': break;
		case '1': // // ref commande
			if (preg_match("/^[1-9]{1}[0-9]{0,8}\-[1-9]{1}[0-9]{0,8}$/", $findText)){
				$ids = explode('-', $findText);
				$queryFilter[] = "ca.idCommande = '".$ids[1]."' and ca.idAdvertiser = '".$ids[0]."'";
                        }else
				$errorstring .= "- La référence saisie est invalide<br/>\n";
			break;

		case '2': // ref produit fournisseur
			$queryFilter[] = "c.produits like  '%".$db->escape($findText)."%'";
			break;

		default :
			$errorstring .= "- Ce type de recherche n'existe pas.<br/>\n";
	}
}

$queryWhere = "WHERE ".
	($user->id==__ID_TECHNI_CONTACT__ ?
		"(a.id = ".$user->id." OR a.parent = ".$user->id.")" :
		"ca.idAdvertiser = ".$user->id).
	(empty($queryFilter) ?
		"" :
		" AND ".implode(" AND ",$queryFilter));
//var_dump($queryFilter,$queryWhere);

//if (!empty($statut))
//{
//
//	switch ($statut)
//	{
//		case '1' : $query .= " and ca.statut_traitement < 20"; break;
//		case '2' : $query .= " and ca.statut_traitement >= 20 and ca.statut_traitement < 30"; break;
//		case '3' : $query .= " and ca.statut_traitement >= 30"; break;
//		default  : $errorstring .= COMMANDS_FILTER_ERROR_STATUS;
//	}
//}

$queryC = "select count(c.id) from commandes c
  left join commandes_advertisers ca on c.id = ca.idCommande
  left join clients cl on c.idClient = cl.id  " . $queryWhere. " " . $query;
$res = & $handle->query($queryC, __FILE__, __LINE__);
$record = & $handle->fetch($res);
$nbcmd = $record[0];


if (($page-1) * NB >= $nbcmd) $page = ($nbcmd - $nbcmd%NB) / NB + 1;

$query  = "select c.id, c.idClient, c.create_time, c.totalPrice2HT, c.produits, c.statut_traitement as statut_commande, ".
"ca.statut_traitement, ca.dispatch_time, ca.isMailSent, ca.timestampIMS, ca.arc, ca.annulation, ca.attente_info, ca.totalOrdreTTC ".
"from commandes c ".
"left join commandes_advertisers ca on c.id = ca.idCommande ".
"left join clients cl on c.idClient = cl.id  " . $queryWhere. " " . $query;

$query .= " order by ";

// ordre de tri
if ($sort == $lastsort && $sort != '')
{
	$sortway = ($sortway == 'asc' ? 'asc' : 'desc');
}
else $sortway = 'asc';
switch ($sort)
{
	case 'ref'    : $query .= "c.id " . $sortway; break;
	case 'date'   : $query .= "c.create_time " . ($sortway == 'asc' ? 'desc' : 'asc') . ", c.id"; break;
	case 'status' : $query .= "ca.statut_traitement " . $sortway . ", c.create_time desc"; break;
	case 'amount' : $query .= "c.totalPrice2TTC " . $sortway . ", c.create_time desc"; break;
	default : $query .= "c.create_time " . ($sortway == 'asc' ? 'desc' : 'asc') . ", c.id"; $sort = 'date';
}

$lastsort = $sort;
$lastpage = $page;

$query .= " limit " . (($page-1)*NB) . "," . NB;
//var_dump($query);
//echo '<br /><br />';
if(!$errorstring){
  $res = & $handle->query($query, __FILE__, __LINE__);
  if( $handle->numrows($res, __FILE__, __LINE__) == 0)
    $o['reponses'] = 'vide';
  else
    while ( $reponse = & $handle->fetchAssoc($res, __FILE__, __LINE__)){
      $reponse['produits'] = unserialize($reponse['produits']);
      $customColsIndex = array_search("customCols",$reponse["produits"][0]);
      foreach($reponse["produits"] as &$pdt)
        unset($pdt[$customColsIndex]);
      unset($pdt);
      array_shift($reponse['produits']);
      $produits_a_conserver = array();
      $pop = false;
      foreach($reponse['produits'] as $cle => $produit)
        if($reponse['produits'][$cle][13] == $user->id){
//           if(empty ($findType))
//              $produits_a_conserver[] = $reponse['produits'][$cle];
//           elseif(!empty ($findText) && $findType == 2 && strpos ($reponse['produits'][$cle][14], $findText) === false){ // le moteur a pu détecter une occurence de la chaine recherchée pour la commande mais pour un autre produit, on teste ici la présence de l'occurence dans le produit
//              $pop = true;
//           }else
             $produits_a_conserver[] = $reponse['produits'][$cle];
        }
      $reponse['produits'] = $produits_a_conserver;
            $o['reponses'][] = $reponse;
            if( $pop) array_pop($o['reponses']);
    }
}else
  $o['error'] = $errorstring;

  if ($nbcmd > NB) $lastpage = ceil($nbcmd/NB);

  $o['pagination'] = array('lastsort' => $lastsort , 'sort' =>  $sort, 'sortway' =>  $sortway, 'lastpage' => $lastpage , 'page' => $page, 'NB' => NB, 'nbcmd' => $nbcmd);

//var_dump($o['reponses']);
mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $o);
print json_encode($o);
