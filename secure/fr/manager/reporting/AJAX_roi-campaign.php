<?php

/*================================================================/

 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com

 Auteur : OD pour Hook Network SARL - http://www.hook-network.com
 Date de création : 21 février 2011

 Fichier : /secure/fr/manager/reporting/AJAX_roi-campaign.php
 Description : résultat ajax du reporting des ROI par campagne

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$user = new BOUser();

header("Content-Type: text/plain; charset=utf-8");

if (!$user->login()) {
  $o["error"] = "Votre session a expirée, veuillez vous identifier à nouveau après avoir rafraichi votre page";
  print json_encode($o);
  exit();
}

if (!$user->get_permissions()->has("m-reporting--sm-roi-campaign","r")) {
  $o["error"] = "Vous n'avez pas les droits adéquats pour réaliser cette opération";
  //mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $o);
  print json_encode($o);
  exit();
}

require_once(ADMIN."logs.php");
require_once(ADMIN."logo.php");

$o = array();

$NB = !empty($_GET['NB']) && is_numeric($_GET['NB']) ? $_GET['NB'] : 30;
define('NB', $NB);
define("__BEGIN_TIME__", mktime(0,0,0,6,15,2011));

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
	$yearS	= (int)trim($_GET['yearS']);
	$monthS	= isset($_GET['monthS'])	? (int)trim($_GET['monthS']) : 0;
	$dayS	= isset($_GET['dayS'])		? (int)trim($_GET['dayS']) : 0;

	if (isset($_GET['yearE'])) {
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
// dates for tests
//$dateStart = 1278077466;
//$dateEnd = 1302787108;

$queryFilter = array("timestamp >= ".$dateStart, "timestamp < ".$dateEnd, 'campaignID != 0');

$errorstring = '';

$reporting = new Reporting($queryFilter);

if(!$errorstring){

  if(empty ($reporting))
    $o['reponses'] = 'vide';
  else
    $o['reponses'] = $reporting;

}else
  $o['error'] = $errorstring;

mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $o);
print json_encode($o);

?>
