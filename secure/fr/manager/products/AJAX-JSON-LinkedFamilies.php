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


$handle = DBHandle::get_instance();
$user = new BOUser();

header("Content-Type: text/plain; charset=utf-8");

$o = array();

if(!$user->login())
{
	$o["error"] = "session expirée";
	exit();
}

function rawurldecodeEuro ($str) { return str_replace("%u20AC", "€", rawurldecode($str)); }
function getLinkedFamilies($productID, & $handle)
{
	$res = & $handle->query("select ffr.id, ffr.name, ffr.ref_name from families_fr ffr, products_families pf where pf.idProduct = " . $productID . " and pf.idFamily = ffr.id order by ffr.ref_name", __FILE__, __LINE__);
	$linked_families = array();
	if ($handle->numrows($res, __FILE__, __LINE__) > 0)
	{
		while ($linked_family = $handle->fetchAssoc($res))
		{
			$linked_families[] = array(
				"id" => $linked_family["id"],
				"name" => mb_convert_encoding($linked_family["name"], "UTF-8","ISO-8859-1"),
				"ref_name" => $linked_family["ref_name"]
			);
		}
	}
	return $linked_families;
}

if (isset($_GET['action']))
{
	switch ($_GET['action'])
	{
		case "get" :
			if (isset($_GET['productID']))
			{
				$o = getLinkedFamilies((int)$_GET['productID'], $handle);
			}
			else $o["error"] = "ID produit non spécifiée";
			break;
			
		case "add" :
			if (isset($_GET['productID']) && isset($_GET['familyID']))
			{
				$productID = (int)$_GET['productID'];
				$familyID = (int)$_GET['familyID'];
				
				if ($handle->query("insert into products_families (idProduct, idFamily) values (" . $productID . ", " . $familyID . ")", __FILE__, __LINE__))
				{
					$o = getLinkedFamilies($productID, $handle);
				}
			}
			else $o["error"] = "Un ou plusieurs paramètres manquants";
			break;
			
		case "update" :
			if (isset($_GET['productID']) && isset($_GET['familyID']) && isset($_GET['oldfamilyID']))
			{
				$productID = (int)$_GET['productID'];
				$familyID = (int)$_GET['familyID'];
				$oldfamilyID = (int)$_GET['oldfamilyID'];
				
				if ($handle->query("update products_families set idFamily = " . $familyID . " where idProduct = " . $productID . " and idfamily = " . $oldfamilyID, __FILE__, __LINE__))
				{
					$o = getLinkedFamilies($productID, $handle);
				}
			}
			else $o["error"] = "Un ou plusieurs paramètres manquants";
			break;
			
		case "delete" :
			if (isset($_GET['productID']) && isset($_GET['familyID']))
			{
				$productID = (int)$_GET['productID'];
				$familyID = (int)$_GET['familyID'];
				
				$res = & $handle->query("select count(idProduct) from products_families where idProduct = " . $productID, __FILE__, __LINE__);
				list($linked_families_count) = $handle->fetch($res);
				if ($linked_families_count > 1)
				{
					if ($handle->query("delete from products_families where idFamily = " . $familyID . " and idProduct = " . $productID, __FILE__, __LINE__))
					{
						$o = getLinkedFamilies($productID, $handle);
					}
				}
				else $o["error"] = "Suppression non autorisée";
			}
			else $o["error"] = "Un ou plusieurs paramètres manquants";
			break;
			
		default : break;
	}
}
else $o = "Aucune action spécifiée";
//$o["error"] = 'éà@ç_çèàé"_ç';
//print_r($o);
print json_encode($o);

exit();

?>
