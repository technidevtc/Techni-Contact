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

$handle = DBHandle::get_instance();
$user = new BOUser();

//header("Content-Type: text/plain; charset=iso-8859-1");
header("Content-Type: text/plain; charset=utf-8");

if(!$user->login())
{
	print "Votre session a expirée, veuillez réactualiser la page pour retourner à la page de login" . __MAIN_SEPARATOR__;
	exit();
}

$es = $os = '';
$AdvertisersSearchText = isset($_GET['AdvertisersSearchText']) ? trim($_GET['AdvertisersSearchText']) : '';
if ($AdvertisersSearchText != '')
{
	$AdvertisersBeginBy = isset($_GET['AdvertisersBeginBy']) ? trim($_GET['AdvertisersBeginBy']) : '';
	$AdvertisersCaseSensitive = isset($_GET['AdvertisersCaseSensitive']) ? trim($_GET['AdvertisersCaseSensitive']) : '';

	$sst = urldecode($AdvertisersSearchText);
	$sbb = $AdvertisersBeginBy == 'false' ? false : true;
	$scs = $AdvertisersCaseSensitive == 'true' ? true : false;

	$regexp = '';
	if ($sbb) $regexp .= '^';
	$regexp .= $sst;
	if ($result = $handle->query("select id, nom1 from advertisers where nom1 regexp " . ($scs ? 'binary ' : '') . "\"" . $handle->escape($regexp) . "\" and parent = 0 order by nom1", __FILE__, __LINE__))
	{
		while ($adv = & $handle->fetch($result))
		{
			$os .= $adv[0] . __OUTPUTID_SEPARATOR__ . $adv[1] . __OUTPUT_SEPARATOR__;
		}
	}
	else $es .= "Erreur fatale lors de l'acquisition de la liste des annonceurs correspondant à votre critère de recherche";
}
else $es .= "Veuillez spécifier un critère de recherche";

print $es . __MAIN_SEPARATOR__ . $os;

exit();

?>
