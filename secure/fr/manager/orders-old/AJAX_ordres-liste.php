<?php

/*================================================================/

 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com

 Auteur : OD pour Hook Network SARL - http://www.hook-network.com
 Date de création : 21 février 2011

 Fichier : /secure/fr/manager/orders/AJAX-ordres-liste.php
 Description : résultat ajax du listing des ordres fournisseur manager

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$user = new BOUser();

header("Content-Type: text/plain; charset=utf-8");

if (!$user->login()) {
  $o["error"] = "Votre session a expirÃ©, veuillez vous identifier Ã  nouveau aprÃ¨s avoir rafraichi votre page";
  print json_encode($o);
  exit();
}

require_once(ADMIN."logs.php");
require_once(ADMIN."logo.php");

$handle = DBHandle::get_instance();

$db = DBHandle::get_instance();
$o = array();

$NB = !empty($_GET['NB']) && is_numeric($_GET['NB']) ? $_GET['NB'] : 30;
define('NB', $NB);
define("__BEGIN_TIME__", mktime(0,0,0,1,1,2004));

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
$status  = isset($_GET['filter_status'])  ? trim($_GET['filter_status']) : '';

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
//$queryFilter[] = "(c.create_time >= ".$dateStart." AND c.create_time < ".$dateEnd.")";
$queryFilter[] = "(ca.timestampIMS >= ".$dateStart." AND ca.timestampIMS < ".$dateEnd.")";
$queryFilter[] = "ca.timestampIMS > 0";
$info_requiered = !empty ($_GET["info_requiered"]) && $_GET["info_requiered"] == 1 ? true : false;

if ($info_requiered)
  $queryFilter[] = "(ca.attente_info = ".__MSGR_CTXT_SUPPLIER_TC_ORDER__." || ca.attente_info = ".__MSGR_CTXT_ORDER_CMD__.") ";

$limitstart = ($page - 1) * NB;

$errorstring = '';

$query = "";
if ($findText != '') {
	switch($findType) {
		case '0': break;
		case '1': // // ref commande
			if (preg_match("/^[1-9]{1}[0-9]{0,8}$/", $findText))
				$queryFilter[] = "ca.idCommande = '".$findText."'";
                        else
				$errorstring .= "- La référence saisie est invalide<br/>\n";
			break;

                case '2': // // ref commande
			if (preg_match("/^[1-9]{1}[0-9]{0,8}\-[1-9]{1}[0-9]{0,8}$/", $findText)){
                          $ids = explode('-', $findText);
				$queryFilter[] = "ca.idCommande = '".$ids[1]."' and ca.idAdvertiser = '".$ids[0]."'";
                        }else
				$errorstring .= "- La référence saisie est invalide<br/>\n";
			break;

		case '3': // nom fournisseur
			$queryFilter[] = "a.nom1 =  '".$db->escape($findText)."'";
			break;

		default :
			$errorstring .= "- Ce type de recherche n'existe pas.<br/>\n";
	}
}

$queryWhere = "WHERE ".
//	($user->id==__ID_TECHNI_CONTACT__ ?
//		"(a.id = ".$user->id." OR a.parent = ".$user->id.")" :
//		"ca.idAdvertiser = ".$user->id).
	(empty($queryFilter) ?
		"" :
		implode(" AND ",$queryFilter));
//var_dump($queryFilter,$queryWhere);

if (!empty($status))
{

	switch ($status)
	{
		case '1' : $query .= " and ca.statut_traitement < 3"; break;
		case '2' : $query .= " and ca.statut_traitement = 3"; break;
		case '3' : $query .= " and ca.statut_traitement = 4 and timestampArc > 0"; break;
                case '4' : $query .= " and ca.annulation = 1"; break;
		default  : $errorstring .= 'Aucun ordre n\'a pu être retrouvé avec ces critères';
	}
}

$queryC = "select count(ca.idCommande) from commandes_advertisers ca ".
  "left join commandes c on c.id = ca.idCommande " .
  "left join advertisers a on a.id = ca.idAdvertiser " .
  "left join clients cl on c.idClient = cl.id " .
  "left join bo_users bou on bou.id = ca.idSender " . $queryWhere. " " . $query;
$res = & $handle->query($queryC, __FILE__, __LINE__);
$record = & $handle->fetch($res);
$nbcmd = $record[0];


if (($page-1) * NB >= $nbcmd) $page = ($nbcmd - $nbcmd%NB) / NB + 1;
if (($lastpage-1) * NB >= $nbcmd) $lastpage = ($nbcmd - $nbcmd%NB) / NB + 1;

$query  = "select ca.idCommande, ca.idAdvertiser, ca.totalOrdreHT, ca.totalOrdreTTC, ca.fdpOrdreHT, ca.fdpOrdreTTC, ca.statut_traitement, ca.dispatch_time, ca.isMailSent, ".
  "ca.timestampIMS, ca.mailComment, ca.idSender, ca.arc, ca.timestampArc, ca.annulation, ca.attente_info, c.produits, c.create_time, a.nom1 as nom_advertiser, bou.name as nom_operateur,  ".
  "cl.nom, cl.prenom , cl.societe ".
  "from commandes_advertisers ca ".
  "left join commandes c on c.id = ca.idCommande " .
  "left join advertisers a on a.id = ca.idAdvertiser " .
  "left join clients cl on c.idClient = cl.id " .
  "left join bo_users bou on bou.id = ca.idSender " .
    $queryWhere. " " . $query;

$query .= " order by ";

// ordre de tri
if ($sort == $lastsort && $sort != '')
{
	if ($lastpage == $page) $sortway = ($sortway == 'asc' ? 'desc' : 'asc');
	else $sortway = ($sortway == 'asc' ? 'asc' : 'desc');
}
else $sortway = 'asc';
switch ($sort)
{
        case 'customer_name'    : $query .= "cl.nom " . $sortway; break;
	case 'ref'    : $query .= "ca.idAdvertiser, ca.idCommande " . $sortway; break;
        case 'advertiser'    : $query .= "a.nom1 " . $sortway; break;
	case 'date'   : $query .= "ca.timestampIMS " . ($sortway == 'asc' ? 'desc' : 'asc') . ", c.id"; break;
        case 'sender'    : $query .= "bou.name " . $sortway; break;
	case 'status' : $query .= "ca.statut_traitement " . $sortway . ", ca.timestampIMS desc"; break;
        case 'messenger'    : $query .= "ca.attente_info " . $sortway; break;
//	case 'amount' : $query .= "c.totalPrice2HT " . $sortway . ", c.create_time desc"; break;
        case 'amountHT'    : $query .= "ca.totalOrdreHT " . $sortway; break;
        case 'amountTTC'    : $query .= "ca.totalOrdreTTC " . $sortway; break;
	default : $query .= "ca.timestampIMS " . ($sortway == 'asc' ? 'desc' : 'asc') . ", c.id"; $sort = 'date';
}

$lastsort = $sort;
$lastpage = $page;

$query .= " limit " . (($page-1)*NB) . "," . NB;


if(!$errorstring){
  $res = & $handle->query($query, __FILE__, __LINE__);
  if( $handle->numrows($res, __FILE__, __LINE__) == 0)
    $o['reponses'] = 'vide';
  else
    while ( $reponse = & $handle->fetchAssoc($res, __FILE__, __LINE__)){
      $reponse['produits'] = mb_unserialize($reponse['produits']);
      array_shift($reponse['produits']);
      $produits_a_conserver = array();
      $pop = false;
      foreach($reponse['produits'] as $cle => $produit)
        if($reponse['produits'][$cle][13] == $user->id){
           if(empty ($findType))
              $produits_a_conserver[] = $reponse['produits'][$cle];
           elseif(!empty ($findText) && $findType == 2 && strpos ($reponse['produits'][$cle][14], $findText) === false){ // le moteur a pu détecter une occurence de la chaine recherchée pour la commande mais pour un autre produit, on teste ici la présence de l'occurence dans le produit
              $pop = true;
           }else
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


mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $o);
print json_encode($o);

?>
