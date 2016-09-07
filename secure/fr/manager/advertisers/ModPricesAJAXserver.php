<?php

/*================================================================/

 Techni-Contact V4 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 13 février 2006

 Fichier : /secure/manager/families/FamiliesSearch.php
 Description : Fichier interface de recherche des familles AJAX

/=================================================================*/

if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
		require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

require_once(ADMIN."logs.php");

$handle = DBHandle::get_instance();
$user = new BOUser();

header("Content-Type: text/plain; charset=utf-8");

if(!$user->login())
{
	print "Votre session a expirée, veuillez réactualiser la page pour retourner à la page de login" . __MAIN_SEPARATOR__;
	exit();
}

function rawurldecodeEuro ($str) { return str_replace("%u20AC", "€", rawurldecode($str)); }
function UpdateSupplierProducts($advID, $pmv, $pmt)
{
	global $handle;
	if ($pmt == "f")
	{
		$res = $handle->query("SELECT prixPublic FROM advertisers WHERE id = " . $advID, __FILE__, __LINE__);
		list($advPrixPublic) = $handle->fetch($res);
		
		if ($advPrixPublic == 0)
		{
			$query =	"UPDATE " .
							"products " .
						"SET " .
							"price2 = ROUND((price2+".$pmv.")*100)/100, " .
							"price = ROUND((price2+".$pmv.")*(100+marge))/100 " .
						"WHERE price2 > 0 AND idAdvertiser = " . $advID;
			$query2 =	"UPDATE " .
							"references_content rc, products p " .
						"SET " .
							"rc.price2 = ROUND((rc.price2+".$pmv.")*100)/100, " . 
							"rc.price = ROUND((rc.price2+".$pmv.")*(100+rc.marge))/100 " . 
						"WHERE rc.price2 > 0 AND rc.idProduct = p.id AND rc.deleted = 0 AND p.idAdvertiser = " . $advID;
		}
		else
		{
			$query =	"UPDATE " .
							"products " .
						"SET " .
							"price = ROUND((price+".$pmv.")*100)/100, " .
							"price2 = ROUND((price2+".$pmv.")*(100-marge))/100 " .
						"WHERE price REGEXP \"^[0-9]+(,|.)[0-9]*$\" AND idAdvertiser = " . $advID;
			$query2 =	"UPDATE " .
							"references_content rc, products p " .
						"SET " .
							"rc.price = ROUND((rc.price+".$pmv.")*100)/100, " . 
							"rc.price2 = ROUND((rc.price2+".$pmv.")*(100-rc.marge))/100 " . 
						"WHERE rc.price REGEXP \"^[0-9]+(,|.)[0-9]*$\" AND rc.deleted = 0 AND rc.idProduct = p.id AND p.idAdvertiser = " . $advID;
		}
	}
	elseif ($pmt == "v")
	{
		$qm = "*" . (100+$pmv)/100;
		$query =	"UPDATE " .
						"products " .
					"SET " .
						"price2 = ROUND((price2".$qm.")*100)/100, " .
						"price = ROUND((price".$qm.")*100)/100 " .
					"WHERE price REGEXP \"^[0-9]+(,|.)[0-9]*$\" AND idAdvertiser = " . $advID;
		
		$query2 =	"UPDATE " .
						"references_content rc, products p " .
					"SET " .
						"rc.price2 = ROUND((rc.price2".$qm.")*100)/100, " . 
						"rc.price = ROUND((rc.price".$qm.")*100)/100 " . 
					"WHERE rc.price REGEXP \"^[0-9]+(,|.)[0-9]*$\" AND rc.deleted = 0 AND rc.idProduct = p.id AND p.idAdvertiser = " . $advID;
	}
	return ($handle->query($query, __FILE__, __LINE__) && $handle->query($query2, __FILE__, __LINE__));
}

$o = array();

if (isset($_GET['action']))
{
	switch ($_GET['action'])
	{
		case "get" :
			if (isset($_GET['advID']))
			{
				$res = & $handle->query("SELECT mod_prices FROM advertisers WHERE id = " . (int)$_GET['advID'], __FILE__, __LINE__);
				if ($handle->numrows($res, __FILE__, __LINE__) > 0)
				{
					list($mod_prices) = $handle->fetch($res);
					if ($mod_prices != "") $mod_prices = mb_unserialize($mod_prices);
					else $mod_prices = array();
					$o = $mod_prices;
				}
			}
			else $o["error"] = "ID du fournisseur non spécifiée";
			break;
			
		case "add" :
			if (isset($_GET['advID']) && isset($_GET['pmv']) && isset($_GET['pmt']))
			{
				if (empty($_GET['pmv']))
					$o["error"] = "Valeur manquante";
				else
				{
					$advID = (int)$_GET['advID'];
					$pmv = (float)$_GET['pmv'];
					$pmt = $_GET['pmt'] == "f" ? "f" : "v";
					
					$res = & $handle->query("select mod_prices from advertisers where id = " . $advID, __FILE__, __LINE__);
					if ($handle->numrows($res, __FILE__, __LINE__) > 0)
					{
						list($mod_prices) = $handle->fetch($res);
						if ($mod_prices != "") $mod_prices = mb_unserialize($mod_prices);
						else $mod_prices = array();
						
						if (UpdateSupplierProducts($advID,$pmv, $pmt))
						{
							array_unshift($mod_prices, array("date" => time(), "val" => $pmv, "type" => $pmt));
							if (count($mod_prices) > 10) array_pop($mod_prices);
							$o = $mod_prices;
							$mod_prices = serialize($mod_prices);
							$handle->query("update advertisers set mod_prices = '" . $handle->escape($mod_prices) . "' where id = " . $advID, __FILE__, __LINE__);
						}
						else $o["error"] = "Erreur fatale lors de la maj des prix produits";
					}
					else $o["error"] = "Erreur fatale : l'ID du fournisseur spécifié n'existe pas";
				}
			}
			else $o["error"] = "Un ou plusieurs paramètres sont manquants";
			break;
			
		case "undo" :
			if (isset($_GET['advID']))
			{
				$advID = (int)$_GET['advID'];
				$res = & $handle->query("select mod_prices from advertisers where id = " . $advID , __FILE__, __LINE__);
				if ($handle->numrows($res, __FILE__, __LINE__) > 0)
				{
					list($mod_prices) = $handle->fetch($res);
					if ($mod_prices != "") $mod_prices = mb_unserialize($mod_prices);
					else $mod_prices = array();
					
					if (!empty($mod_prices))
					{
						$mptd = array_shift($mod_prices);
						if ($mptd["type"] == "f") $mptd["val"] = -(float)$mptd["val"];
						elseif ($mptd["type"] == "v") $mptd["val"] = -100*(1-(100/(100+(float)$mptd["val"])));
						
						if (UpdateSupplierProducts($advID, $mptd["val"], $mptd["type"]))
						{
							$o = $mod_prices;
							$mod_prices = serialize($mod_prices);
							$handle->query("update advertisers set mod_prices = '" . $handle->escape($mod_prices) . "' where id = " . $advID , __FILE__, __LINE__);
						}
					}
				}
			}
			else $o["error"] = "ID du fournisseur non spécifiée";
			break;
			
		default : break;
	}
}
else $o = "Aucune action n'a été spécifiée";

print json_encode($o);

exit();

?>
