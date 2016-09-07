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
$ProductsSearchText = isset($_GET['ProductsSearchText']) ? trim($_GET['ProductsSearchText']) : '';
if ($ProductsSearchText != '')
{
	$ProductsBeginBy = isset($_GET['ProductsBeginBy']) ? trim($_GET['ProductsBeginBy']) : '';
	$ProductsCaseSensitive = isset($_GET['ProductsCaseSensitive']) ? trim($_GET['ProductsCaseSensitive']) : '';

	$pst = urldecode($ProductsSearchText);
	$pbb = $ProductsBeginBy == 'false' ? false : true;
	$pcs = $ProductsCaseSensitive == 'true' ? true : false;

	$regexp = '';
	if ($pbb) $regexp .= '^';
	$regexp .= $pst;
	if ($result = $handle->query("select id, name from products_fr f where name regexp " . ($pcs ? 'binary ' : '') . "\"" . $handle->escape($regexp) . "\" order by name", __FILE__, __LINE__))
	{
		while ($pdt = & $handle->fetch($result))
		{
			$os .= $pdt[0] . __OUTPUTID_SEPARATOR__ . $pdt[1] . __OUTPUT_SEPARATOR__;
		}
	}
	else $es .= "Erreur fatale lors de l'acquisition de la liste des produits correspondant à votre critère de recherche";
}
else $es .= "Veuillez spécifier un critère de recherche";

print $es . __MAIN_SEPARATOR__ . $os;

exit();

?>
