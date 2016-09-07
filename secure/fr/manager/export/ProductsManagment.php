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

require('_ClassExport.php');

//header("Content-Type: text/plain; charset=iso-8859-1");
header("Content-Type: text/plain; charset=utf-8");

if(!$user->login())
{
	print "Votre session a expirée, veuillez réactualiser la page pour retourner à la page de login" . __MAIN_SEPARATOR__;
	exit();
}

function rawurldecodeEuro ($str) { return str_replace("%u20AC", "€", rawurldecode($str)); }

/*
ProductsManagement.php?action=delete&id=13513357&
ProductsManagement.php?action=finalize&id=13513357
ProductsManagement.php?action=multi-alter&ids=13513357,573654,16846,357384&family_ref_name=new_family_ref_name
ProductsManagement.php?action=multi-delete&ids=13513357,573654,16846,357384
ProductsManagement.php?action=multi-finalize&ids=13513357,573654,16846,357384
*/
$es = $os = '';
///$action = isset($_GET['action']) ? trim($_GET['action']) : '';
//$id = isset($_GET['action']) ? trim($_GET['action']) : '';

if (isset($_GET['action']))
{
	switch ($_GET['action'])
	{
		//ProductsManagement.php?action=get&id=13513357
		case "get" :
      if (!$user->get_permissions()->has("m-prod--sm-export","r")) {
        $es .= "Vous n'avez pas les droits adéquats pour réaliser cette opération".__ERROR_SEPARATOR__;
        break;
      }
			$os .= "get" . __OUTPUT_SEPARATOR__;
			if (isset($_GET['id']))
			{
				settype($_GET['id'], "integer");
				$exp = & new Export($handle, $_GET['id']);
				if ($exp->exist)
				{
					if (isset($_GET['idTC']))
					{
						settype($_GET['idTC'], "integer");
						if ($exp->LoadProduct($_GET['idTC']))
						{
							$os .= "idTC" . __OUTPUTID_SEPARATOR__ . $_GET['idTC'] . __OUTPUT_SEPARATOR__;
							$cf_keys = array_merge($exp->c_keys, $exp->f_keys);
							foreach ($cf_keys as $key)
							{
								$pdt = & $exp->products[$_GET['idTC']];
								$os .= $key;
								if (is_array($pdt[$key]))
								{
									foreach($pdt[$key] as $ckey => $cvalue)
										$os .= __OUTPUTID_SEPARATOR__ . $ckey . __OUTPUTID_SEPARATOR__ . $cvalue;
								}
								else $os .= __OUTPUTID_SEPARATOR__ . $pdt[$key];
								$os .= __OUTPUT_SEPARATOR__;
							}
						}
						else $es .= $exp->lastErrorMessage . __ERROR_SEPARATOR__;
					}
					else $es .= "L'identifiant TC du produit à éditer n'a pas été spécifié" . __ERROR_SEPARATOR__;
				}
				else $es .= $exp->lastErrorMessage . __ERROR_SEPARATOR__;
			}
			else $es .= "ID de l'export non spécifié" . __ERROR_SEPARATOR__;
			break;
			
/*
ProductsManagement.php?action=alter
						&id=13513357&name=newname&fastdesc=new_fastdesc&descc=newdesc&descd=....
						&mixed_data_entitle_0=poids&mixed_data_entitle_1=largeur
						&ref_count=3
						&id_ref_1=646354&ref_supplier_1=QSDGS65765&label_1=hohoho&price_1=149.95&price_deleted_1=&price2_1=&marge=&unit_1=&VAT_1=19.6&order_1=1
							&mixed_data_1_1=15Kg&mixed_data_1_2=150cm
						&id_ref_2=51357&ref_supplier_2=QSGQSDF6657&label_2=hahaha&price_2=119.95&price_deleted_2=&price2_2=&marge=&unit_2=&VAT_2=19.6&order_2=2
							&mixed_data_2_1=13Kg&mixed_data_3_2=140cm
						&id_ref_3=646354&ref_supplier_3=AQZGAZSD6168&label_3=hehehe&price_3=99.95&price_deleted_3=&price2_3=&marge=&unit_3=&VAT_3=19.6&order_3=3
							&mixed_data_3_1=10Kg&mixed_data_3_2=125cm
*/
		case "alter" :
      if (!$user->get_permissions()->has("m-prod--sm-export","e")) {
        $es .= "Vous n'avez pas les droits adéquats pour réaliser cette opération".__ERROR_SEPARATOR__;
        break;
      }
			$os .= "alter" . __OUTPUT_SEPARATOR__;
			if (isset($_GET['id']))
			{
				settype($_GET['id'], "integer");
				$exp = & new Export($handle, $_GET['id']);
				if ($exp->exist)
				{
					if (isset($_GET['idTC']))
					{
						settype($_GET['idTC'], "integer");
						if ($exp->LoadProduct($_GET['idTC']))
						{
							$cf_keys = array_merge($exp->c_keys, $exp->f_keys);
							$pdt = & $exp->products[$_GET['idTC']];
							foreach($cf_keys as $key)
							{
								if (is_array($pdt[$key]))
								{
									foreach($pdt[$key] as $ckey => $cvalue)
										$pdt[$key][$ckey] = isset($_GET[$ckey]) ? $_GET[$ckey] : "";
								}
								else $pdt[$key] = isset($_GET[$key]) ? $_GET[$key] : "";
							}
						}
						else $es .= $exp->lastErrorMessage . __ERROR_SEPARATOR__;
					}
					else $es .= "L'identifiant TC du produit à éditer n'a pas été spécifié" . __ERROR_SEPARATOR__;
				}
				else $es .= $exp->lastErrorMessage . __ERROR_SEPARATOR__;
				
				if ($exp->SaveProducts() && $exp->Save())
				{
					//$os .= "id"					. __OUTPUTID_SEPARATOR__ . $ip->id . __OUTPUT_SEPARATOR__;
					$os .= "OK" . __OUTPUT_SEPARATOR__;
				}
				else $es .= $exp->lastErrorMessage . __ERROR_SEPARATOR__;
			}
			else $es .= "ID de l'export non spécifié" . __ERROR_SEPARATOR__;
			break;
			
		case "delete" :
      if (!$user->get_permissions()->has("m-prod--sm-export","d")) {
        $es .= "Vous n'avez pas les droits adéquats pour réaliser cette opération".__ERROR_SEPARATOR__;
        break;
      }
			$os .= "delete" . __OUTPUT_SEPARATOR__;
			if (isset($_GET['id']))
			{
				settype($_GET['id'], "integer");
				$exp = & new Export($handle, $_GET['id']);
				if ($exp->DelProduct($_GET['idTC']))
				{
					$os .= "OK" . __OUTPUT_SEPARATOR__;
				}
				else $es .= "Erreur fatale SQL lors de la supression du produit " . $_GET['id'] . ".";
			}
			else $es .= "ID du produit à supprimer non spécifiée" . __ERROR_SEPARATOR__;
			break;
			
		case "multi-alter" :
      if (!$user->get_permissions()->has("m-prod--sm-export","e")) {
        $es .= "Vous n'avez pas les droits adéquats pour réaliser cette opération".__ERROR_SEPARATOR__;
        break;
      }
			$os .= "multi-alter" . __OUTPUT_SEPARATOR__;
			if (isset($_GET['ids']))
			{
				$ids = explode("-", $_GET['ids']);
				$len = count($ids) - 1;
				if ($len > 0)
				{
					if (isset($_GET['family_name'])) $os .= "family_name" . __OUTPUTID_SEPARATOR__ . rawurldecode($_GET['family_name']) . __OUTPUT_SEPARATOR__;
					if (isset($_GET['fastdesc'])) $os .= "fastdesc" . __OUTPUTID_SEPARATOR__ . rawurldecode($_GET['fastdesc']) . __OUTPUT_SEPARATOR__;
					if (isset($_GET['descc'])) $os .= "descc" . __OUTPUTID_SEPARATOR__ . rawurldecode($_GET['descc']) . __OUTPUT_SEPARATOR__;
					if (isset($_GET['descd'])) $os .= "descd" . __OUTPUTID_SEPARATOR__ . rawurldecode($_GET['descd']) . __OUTPUT_SEPARATOR__;
					if (isset($_GET['delivery_time'])) $os .= "delivery_time" . __OUTPUTID_SEPARATOR__ . rawurldecode($_GET['delivery_time']) . __OUTPUT_SEPARATOR__;
					if (isset($_GET['url_image'])) $os .= "url_image" . __OUTPUTID_SEPARATOR__ . rawurldecode($_GET['url_image']) . __OUTPUT_SEPARATOR__;
					if (isset($_GET['alias'])) $os .= "alias" . __OUTPUTID_SEPARATOR__ . rawurldecode($_GET['alias']) . __OUTPUT_SEPARATOR__;
					if (isset($_GET['keywords'])) $os .= "keywords" . __OUTPUTID_SEPARATOR__ . rawurldecode($_GET['keywords']) . __OUTPUT_SEPARATOR__;
					$os .= "ids" . __OUTPUTID_SEPARATOR__;
					$importsAffected = array();
					
					for ($i = 0; $i < $len; $i++)
					{
						settype($ids[$i], "integer");
						$ip = & new ImportProduct($handle, $ids[$i]);
						if ($ip->exist)
						{
							if (isset($_GET['family_name'])) $ip->family_name = rawurldecode($_GET['family_name']);
							if (isset($_GET['fastdesc'])) $ip->fastdesc = rawurldecode($_GET['fastdesc']);
							if (isset($_GET['descc'])) $ip->descc = rawurldecode($_GET['descc']);
							if (isset($_GET['descd'])) $ip->descd = rawurldecode($_GET['descd']);
							if (isset($_GET['delivery_time'])) $ip->delivery_time = rawurldecode($_GET['delivery_time']);
							if (isset($_GET['url_image'])) $ip->delivery_time = rawurldecode($_GET['url_image']);
							if (isset($_GET['alias'])) $ip->alias = rawurldecode($_GET['alias']);
							if (isset($_GET['keywords'])) $ip->keywords = rawurldecode($_GET['keywords']);
							
							$ip->UpdateStatus();
							if (!$ip->Save()) $es .= $ip->lastErrorMessage . __ERROR_SEPARATOR__;
							
							$os .= $ids[$i] . __DATA_SEPARATOR__ . $ip->status . __DATA_SEPARATOR__;
							$importsAffected[$ip->id_import] = true;
						}
						unset($ip);
					}
					
					foreach ($importsAffected as $id => $value)
					{
						$imp = & new Import($handle, $id);
						$imp->UpdateStatus();
						$imp->Save();
						unset($imp);
					}
					$os .= __OUTPUT_SEPARATOR__;
				}
				else $es .= "Il n'y a aucun produit à modifier" . __ERROR_SEPARATOR__;
			}
			else $es .= "IDs des produits à charger non spécifiées" . __ERROR_SEPARATOR__;
			break;
			
		case "multi-delete" :
      if (!$user->get_permissions()->has("m-prod--sm-export","d")) {
        $es .= "Vous n'avez pas les droits adéquats pour réaliser cette opération".__ERROR_SEPARATOR__;
        break;
      }
			if (isset($_GET['ids']))
			{
				
			}
			else $es .= "IDs des produits à charger non spécifiées" . __ERROR_SEPARATOR__;
			break;
			
		case "addProduct" :
      if (!$user->get_permissions()->has("m-prod--sm-export","e")) {
        $es .= "Vous n'avez pas les droits adéquats pour réaliser cette opération".__ERROR_SEPARATOR__;
        break;
      }
			$os .= "addProduct" . __OUTPUT_SEPARATOR__;
			if (isset($_GET['id']))
			{
				settype($_GET['id'], "integer");
				$exp = & new Export($handle, $_GET['id']);
				
				if (!isset($_GET["idProduct"]) || !isset($_GET["idFamily"]))
				{
					$es .= "Identifiant TC ou famille non spécifiée pour l'ajout du produit" . __ERROR_SEPARATOR__;
					break;
				}
				if ($exp->AddProduct((int)$_GET["idProduct"], (int)$_GET["idFamily"]))
				{
					$exp->SaveProducts();
					$exp->Save();
					$os .= "OK" . __OUTPUT_SEPARATOR__;
				}
				else $es .= "Erreur fatale lors de l'ajout du produit : " . $exp->lastErrorMessage . __ERROR_SEPARATOR__;
			}
			else $es .= "ID du produit à ajouter non spécifié" . __ERROR_SEPARATOR__;
			break;
			
		case "addFamily" :
      if (!$user->get_permissions()->has("m-prod--sm-export","e")) {
        $es .= "Vous n'avez pas les droits adéquats pour réaliser cette opération".__ERROR_SEPARATOR__;
        break;
      }
			$os .= "addFamily" . __OUTPUT_SEPARATOR__;
			if (isset($_GET['id']))
			{
				settype($_GET['id'], "integer");
				$exp = & new Export($handle, $_GET['id']);
				
				if (!isset($_GET["idFamily"]))
				{
					$es .= "Famille de produit à ajouter non spécifiée" . __ERROR_SEPARATOR__;
					break;
				}
				$result = & $handle->query("select idProduct from products_families where idFamily = " . $_GET["idFamily"], __FILE__, __LINE__, false);
				while (list($idProduct) = $handle->fetch($result))
				{
					if ($exp->AddProduct($idProduct, (int)$_GET["idFamily"]))
					{
						$os .= "OK" . __OUTPUT_SEPARATOR__;
					}
					else
					{
						$es .= "Erreur fatale lors de l'ajout du produit : " . $exp->lastErrorMessage . __ERROR_SEPARATOR__;
						break;
					}
				}
				if (empty($es))
				{
					$exp->SaveProducts();
					$exp->Save();
				}
			}
			else $es .= "ID du produit à ajouter non spécifié" . __ERROR_SEPARATOR__;
			break;
			
		case "addAdvertisers" :
      if (!$user->get_permissions()->has("m-prod--sm-export","e")) {
        $es .= "Vous n'avez pas les droits adéquats pour réaliser cette opération".__ERROR_SEPARATOR__;
        break;
      }
			$os .= "addAdvertisers" . __OUTPUT_SEPARATOR__;
			if (isset($_GET['id']))
			{
				settype($_GET['id'], "integer");
				$exp = & new Export($handle, $_GET['id']);
				
				if (!isset($_GET["advIDlist"]))
				{
					$es .= "Les identifiants des annonceurs ou fournisseurs dont les produits doivent être ajoutés n'ont pas été spécifiés" . __ERROR_SEPARATOR__;
					break;
				}
				
				$advIDlist = explode("-",$_GET["advIDlist"]);
				$advIDlistSQL = "";
				foreach($advIDlist as $advID) $advIDlistSQL .= (empty($advIDlistSQL) ? "" : " or ") . "a.id = " . (int)$advID;
				$result = & $handle->query("select p.id, pf.idFamily from products p, advertisers a, products_families pf where p.idAdvertiser = a.id and p.id = pf.idProduct and (" . $advIDlistSQL . ") group by p.id", __FILE__, __LINE__, false);
				while (list($idProduct, $idFamily) = $handle->fetch($result))
				{
					if ($exp->AddProduct($idProduct, $idFamily))
					{
						$os .= "OK" . __OUTPUT_SEPARATOR__;
					}
					else
					{
						$es .= "Erreur fatale lors de l'ajout du produit : " . $exp->lastErrorMessage . __ERROR_SEPARATOR__;
						break;
					}
				}
				if (empty($es))
				{
					$exp->SaveProducts();
					$exp->Save();
				}
				//print str_replace("\n", "<br/>", print_r($exp, true));
			}
			else $es .= "ID du produit à ajouter non spécifié" . __ERROR_SEPARATOR__;
			break;
			
		default : break;
	}
}

print $es . __MAIN_SEPARATOR__ . $os;

exit();

?>
