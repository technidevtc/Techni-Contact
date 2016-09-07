<?php

/*================================================================/

 Techni-Contact V4 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 13 février 2006

 Fichier : /secure/manager/families/FamiliesSearch.php
 Description : Fichier interface de recherche des familles AJAX

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

require(ADMIN."logs.php");
require(ADMIN."generator.php");

$handle = DBHandle::get_instance();
$user = new BOUser();

//header("Content-Type: text/plain; charset=iso-8859-1");
header("Content-Type: text/plain; charset=utf-8");

if(!$user->login())
{
	print "FamiliesError" . __ERRORID_SEPARATOR__ . "Votre session a expiré, veuillez réactualiser la page pour retourner à la page de login" . __ERROR_SEPARATOR__ . __MAIN_SEPARATOR__;
	exit();
}

$FamiliesSearchText = isset($_GET['FamiliesSearchText']) ? trim($_GET['FamiliesSearchText']) : '';
if ($FamiliesSearchText != '')
{
	$FamiliesBeginBy = isset($_GET['FamiliesBeginBy']) ? trim($_GET['FamiliesBeginBy']) : '';
	$FamiliesCaseSensitive = isset($_GET['FamiliesCaseSensitive']) ? trim($_GET['FamiliesCaseSensitive']) : '';
	$es = $os = '';

	$fst = urldecode($FamiliesSearchText);
	$fbb = $FamiliesBeginBy == 'false' ? false : true;
	$fcs = $FamiliesCaseSensitive == 'true' ? true : false;

	$regexp = '';
	if ($fbb) $regexp .= '^';
	$regexp .= $fst;
	if ($result = $handle->query("select id, ref_name, name from families_fr where name regexp " . ($fcs ? 'binary ' : '') . "\"" . $handle->escape($regexp) . "\" order by name", __FILE__, __LINE__))
	{
		$os .= "FamiliesResults" . __OUTPUTID_SEPARATOR__;
		while ($family = & $handle->fetch($result))
		{
			$fst_o = strpos(mb_strtolower($family[2]), mb_strtolower($fst));
			$strhl = substr_replace($family[2], '<strong>', $fst_o, 0);
			$strhl = substr_replace($strhl, '</strong>', $fst_o+strlen('<strong>'.$fst), 0);
			$os .= $family[0] . __DATA_SEPARATOR__ . $family[1] . __DATA_SEPARATOR__ . $strhl . __DATA_SEPARATOR__;
		}
		$os .=  __OUTPUT_SEPARATOR__;
	}
	else $es .= "Erreur fatale lors de l'acquisition de la liste des familles correspondant à votre critère de recherche";
}
else $es .= "Veuillez spécifier un critère de recherche";

if ($es != '') $es = "FamiliesResultsError" . __ERRORID_SEPARATOR__ . $es . __ERROR_SEPARATOR__;

print $es . __MAIN_SEPARATOR__ . $os;

exit();

?>
