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

header("Content-Type: text/plain; charset=utf-8");

function rawurldecodeEuro ($str) { return str_replace("%u20AC", "€", rawurldecode($str)); }

$o = array();

if(!$user->login())
{
	$o['error'] = "Votre session a expirée, veuillez réactualiser la page pour retourner à la page de login";
}
else
{
	if (isset($_GET['action']))
	{
		switch ($_GET['action'])
		{
			case "get" :
				if (isset($_GET['filter']) && isset($_GET['ids']))
				{
					
					/* Facultative - Information Level
					 * 0 = Full
					 * 1 = Full - cg, ci, cc, contrainteProduit, alias, keywords, marge
					 * 2 = id, idAdvertiser, idTC, timestamp, price, name, fastdesc, ref_name
					 * 3 = id, idAdvertiser
					 * 0 by default */
					$info_lvl = 0;
					if (isset($_GET['info_lvl'])) $info_lvl = (int)$_GET['info_lvl'];
					if ($info_lvl < 0 || $info_lvl > 2) $info_lvl = 0;
					
					switch($info_lvl)
					{
						case 0 :
							$fields_list = "
								p.id, p.idAdvertiser, p.idTC, p.timestamp, p.cg,
								p.ci, p.cc, p.refSupplier, p.price, p.price2,
								p.unite, p.marge, p.idTVA, p.contrainteProduit, p.tauxRemise,
								p.similar_items, pfr.name, pfr.fastdesc, pfr.ref_name, pfr.alias,
								pfr.keywords, pfr.descc, pfr.descd, pfr.delai_livraison, pfr.active, pfr.deleted
							";
							break;
						case 1 :
							$fields_list = "
								p.id, p.idAdvertiser, p.idTC, p.timestamp,
								p.refSupplier, p.price, p.price2,
								p.unite, p.idTVA, p.tauxRemise,
								p.similar_items, pfr.name, pfr.fastdesc, pfr.ref_name,
								pfr.descc, pfr.descd, pfr.delai_livraison, pfr.active, pfr.deleted
							";
							break;
							
						case 2 :
							$fields_list = "
								p.id, p.idAdvertiser, p.idTC, p.timestamp, p.price,
								pfr.name, pfr.fastdesc, pfr.ref_name
							";
							break;
							
						case 3 :
							$fields_list = "
								p.id, p.idAdvertiser
							";
							break;
						
						default : break;
						
					}
					
					if ($info_lvl < 0 || $info_lvl > 2) $info_lvl = 0;
					
					/* Facultative - Sort options (default is by name) */
					$sort = "pfr.name";
					if (isset($_GET['sort']))
					{
						switch ($_GET['sort'])
						{
							case "name" : $sort = "pfr.name"; break;
							case "id" : $sort = "p.id"; break;
							case "date" : $sort = "p.timestamp"; break;
							case "fastdesc" : $sort = "pfr.fastdesc"; break;
							case "price" :  $sort = "p.price"; break;
							default : $sort = "pfr.name"; break;
						}
					}
					/* Facultative - Order (desc by default) */
					$sortway = "asc";
					if (isset($_GET['sortway'])) $sortway = strtolower($_GET['sortway']) == "desc" ? "desc" : "asc";
					
					/* Required - Filter to get the products : by Products, Families or Advertisers */
					switch ($_GET['filter'])
					{
						case "pdtID" :
							if (preg_match("/^([0-9]+\,)*([0-9]+){1}$/", $_GET['ids']))
							{
								$res = $handle->query("
									select" . $fields_list . "
									from
										products p, products_fr pfr
									where
										pfr.active = 1 and
                                                                                pfr.deleted != 1 and
										p.id = pfr.id and
										p.id in (" . $_GET['ids'] . ")
									order by " . $sort . " " . $sortway, __FILE__, __LINE__);
								if ($handle->numrows($res, __FILE__, __LINE__) > 0)
								{
									while ($pdt = $handle->fetchAssoc($res))
										$o[$pdt['id']] = $pdt;
								}
							}
							else $o["error"] = "Products GET Error : FILTER : No Valid Product's ID's specified";
							break;
						
						case "famID" :
							if (preg_match("/^([0-9]+\,)*([0-9]+){1}$/", $_GET['ids']))
							{
								$res = $handle->query("
									select" . $fields_list . "
									from
										products p, products_fr pfr, products_families pf
									where
										pfr.active = 1 and
                                                                                pfr.deleted != 1 and
										p.id = pfr.id and
										p.id = pf.idProduct and
										pf.idFamily in (" . $_GET['ids'] . ")
									order by " . $sort . " " . $sortway, __FILE__, __LINE__);
								if ($handle->numrows($res, __FILE__, __LINE__) > 0)
								{
									while ($pdt = $handle->fetchAssoc($res))
										$o[$pdt['id']] = $pdt;
								}
							}
							else $o["error"] = "Products GET Error : FILTER : No Valid Product's ID's specified";
							break;
						
						case "advID" :
							if (preg_match("/^([0-9]+\,)*([0-9]+){1}$/", $_GET['ids']))
							{
								$res = $handle->query("
									select" . $fields_list . "
									from
										products p, products_fr pfr, advertisers a
									where
										pfr.active = 1 and
                                                                                pfr.deleted != 1 and
										p.id = pfr.id and
										p.id = a.id and
										a.id in (" . $_GET['ids'] . ")
									order by " . $sort . " " . $sortway, __FILE__, __LINE__);
								if ($handle->numrows($res, __FILE__, __LINE__) > 0)
								{
									while ($pdt = $handle->fetchAssoc($res))
										$o[$pdt['id']] = $pdt;
								}
							}
							else $o["error"] = "Products GET Error : FILTER : No Valid Product's ID's specified";
							break;
						
						default :
							$o['error'] = "Products GET Error : FILTER : Invalid Filter";
							break;
					}
				}
				else $o['error'] = "Products GET Error : No Filter specified";
				break;
				
/*			case "add" :
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
*/
			default :
				$o['error'] = "Products : Invalid Action";
				break;
		}
	}
	else $o['error'] = "Products : No action specified";
}

mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $o);
print json_encode($o);

exit();

?>
