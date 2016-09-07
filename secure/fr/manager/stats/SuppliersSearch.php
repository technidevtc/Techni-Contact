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

require(ICLASS . 'ManagerUser.php');

$handle = DBHandle::get_instance();
$user   = & new ManagerUser($handle);

//header("Content-Type: text/html; charset=iso-8859-1");
header("Content-Type: text/html; charset=utf-8");

if(!$user->login())
{
	print "Votre session a expirée, veuillez réactualiser la page pour retourner à la page de login" . __MAIN_SEPARATOR__;
	exit();
}

$es = $os = '';
$SuppliersSearchText = isset($_GET['SuppliersSearchText']) ? trim($_GET['SuppliersSearchText']) : '';
if ($SuppliersSearchText != '')
{
	$SuppliersBeginBy = isset($_GET['SuppliersBeginBy']) ? trim($_GET['SuppliersBeginBy']) : '';
	$SuppliersCaseSensitive = isset($_GET['SuppliersCaseSensitive']) ? trim($_GET['SuppliersCaseSensitive']) : '';

	$sst = urldecode($SuppliersSearchText);
	$sbb = $SuppliersBeginBy == 'false' ? false : true;
	$scs = $SuppliersCaseSensitive == 'true' ? true : false;

	$regexp = '';
	if ($sbb) $regexp .= '^';
	$regexp .= $sst;
	if ($result = $handle->query("select id, nom1 from advertisers where nom1 regexp " . ($scs ? 'binary ' : '') . "\"" . $handle->escape($regexp) . "\" and parent = " . __ID_TECHNI_CONTACT__ . " order by nom1", __FILE__, __LINE__))
	{
		while ($adv = & $handle->fetch($result))
		{
			$os .= $adv[0] . __OUTPUTID_SEPARATOR__ . $adv[1] . __OUTPUT_SEPARATOR__;
		}
	}
	else $es .= "Erreur fatale lors de l'acquisition de la liste des fournisseurs correspondant à votre critère de recherche";
}
else $es .= "Veuillez spécifier un critère de recherche";

print $es . __MAIN_SEPARATOR__ . $os;

exit();

?>
