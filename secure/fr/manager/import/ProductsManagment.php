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

require('_ClassImportProduct.php');
require('_ClassImport.php');

header("Content-Type: text/html; charset=utf-8");

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
      if (!$user->get_permissions()->has("m-prod--sm-import","r")) {
        $es .= "Vous n'avez pas les droits adéquats pour réaliser cette opération.".__ERROR_SEPARATOR__;
        break;
      }
			$os .= "get" . __OUTPUT_SEPARATOR__;
			if (isset($_GET['id']))
			{
				settype($_GET['id'], "integer");
				$ip = & new ImportProduct($handle, $_GET['id']);
				if ($ip->exist)
				{
					$mixed_data_entitle = mb_unserialize($ip->mixed_data_entitle);
					$url_docs = mb_unserialize($ip->url_docs);
					
					$os .= "id"					. __OUTPUTID_SEPARATOR__ . $ip->id . __OUTPUT_SEPARATOR__;
					$os .= "id_final"			. __OUTPUTID_SEPARATOR__ . $ip->id_final . __OUTPUT_SEPARATOR__;
					$os .= "online_sell"		. __OUTPUTID_SEPARATOR__ . $ip->online_sell . __OUTPUT_SEPARATOR__;
					$os .= "name"				. __OUTPUTID_SEPARATOR__ . $ip->name . __OUTPUT_SEPARATOR__;
					$os .= "ref_name"			. __OUTPUTID_SEPARATOR__ . $ip->ref_name . __OUTPUT_SEPARATOR__;
					$os .= "family_name"		. __OUTPUTID_SEPARATOR__ . $ip->family_name . __OUTPUT_SEPARATOR__;
					$os .= "fastdesc"			. __OUTPUTID_SEPARATOR__ . $ip->fastdesc . __OUTPUT_SEPARATOR__;
					$os .= "descc"				. __OUTPUTID_SEPARATOR__ . $ip->descc . __OUTPUT_SEPARATOR__;
					$os .= "descd"				. __OUTPUTID_SEPARATOR__ . $ip->descd . __OUTPUT_SEPARATOR__;
					$os .= "delivery_time"		. __OUTPUTID_SEPARATOR__ . $ip->delivery_time . __OUTPUT_SEPARATOR__;
					$os .= "url_image"			. __OUTPUTID_SEPARATOR__ . $ip->url_image . __OUTPUT_SEPARATOR__;
					$os .= "price"				. __OUTPUTID_SEPARATOR__ . $ip->price . __OUTPUT_SEPARATOR__;
					$os .= "alias"				. __OUTPUTID_SEPARATOR__ . $ip->alias . __OUTPUT_SEPARATOR__;
					$os .= "keywords"			. __OUTPUTID_SEPARATOR__ . $ip->keywords . __OUTPUT_SEPARATOR__;
					$os .= "ref_count"			. __OUTPUTID_SEPARATOR__ . $ip->ref_count . __OUTPUT_SEPARATOR__;
					$os .= "status"				. __OUTPUTID_SEPARATOR__ . $ip->status . __OUTPUT_SEPARATOR__;
					
					$os .= "mixed_data_entitle" . __OUTPUTID_SEPARATOR__;
					foreach ($mixed_data_entitle as $value) $os .= $value . __DATA_SEPARATOR__;
					$os .= __OUTPUT_SEPARATOR__;
					
					$os .= "url_docs" . __OUTPUTID_SEPARATOR__;
					for($i = 0; $i < 3; $i++) $os .= (isset($url_docs[$i]) ? $url_docs[$i] : "") . __DATA_SEPARATOR__;
					$os .= __OUTPUT_SEPARATOR__;
					
					$refnum = 0;
					foreach ($ip->references as $ref)
					{
						$mixed_data = mb_unserialize($ref['mixed_data']);
						
						$os .= "reference" . __OUTPUTID_SEPARATOR__;
						//$os .= $refnum++ . __DATA_SEPARATOR__;
						$os .= $ref['id'] . __DATA_SEPARATOR__;
						$os .= $ref['ref_supplier'] . __DATA_SEPARATOR__;
						$os .= $ref['label'] . __DATA_SEPARATOR__;
						foreach ($mixed_data as $value) $os .= $value . __DATA_SEPARATOR__;
						$os .= $ref['unit'] . __DATA_SEPARATOR__;
						$os .= $ref['VAT'] . __DATA_SEPARATOR__;
						$os .= $ref['price2'] . __DATA_SEPARATOR__;
						$os .= $ref['marge'] . __DATA_SEPARATOR__;
						$os .= $ref['price'] . __DATA_SEPARATOR__;
						//$os .= $ref['price_deleted'] . __DATA_SEPARATOR__;
						$os .= $ref['order'] . __DATA_SEPARATOR__;
						//$os .= __DATA_SEPARATOR2__;
						$os .= __OUTPUT_SEPARATOR__;
					}
				}
				else $es .= $ip->lastErrorMessage . __ERROR_SEPARATOR__;
			}
			else $es .= "ID du produit à charger non spécifiée" . __ERROR_SEPARATOR__;
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
      if (!$user->get_permissions()->has("m-prod--sm-import","e")) {
        $es .= "Vous n'avez pas les droits adéquats pour réaliser cette opération.".__ERROR_SEPARATOR__;
        break;
      }
			$os .= "alter" . __OUTPUT_SEPARATOR__;
			if (isset($_GET['id']))
			{
				settype($_GET['id'], "integer");
				$ip = & new ImportProduct($handle, $_GET['id']);
				
				$ip->online_sell = isset($_GET['online_sell']) ? (int)$_GET['online_sell'] : 0;
				$ip->name = isset($_GET['name']) ? (rawurldecodeEuro($_GET['name'])) : "";
				$ip->ref_name = Utils::toDashAz09($ip->name);
				$ip->family_name = isset($_GET['family_name']) ? rawurldecode($_GET['family_name']) : 0;
				$ip->fastdesc = isset($_GET['fastdesc']) ? rawurldecode($_GET['fastdesc']) : "";
				$ip->descc = isset($_GET['descc']) ? rawurldecode($_GET['descc']) : "";
				$ip->descd = isset($_GET['descd']) ? rawurldecode($_GET['descd']) : "";
				$ip->delivery_time = isset($_GET['delivery_time']) ? rawurldecode($_GET['delivery_time']) : "";
				$ip->url_image = isset($_GET['url_image']) ? rawurldecode($_GET['url_image']) : "";
				$ip->price = isset($_GET['price']) ? rawurldecode($_GET['price']) : "";
				$ip->alias = isset($_GET['alias']) ? rawurldecode($_GET['alias']) : "";
				$ip->keywords = isset($_GET['keywords']) ? rawurldecode($_GET['keywords']) : "";
				
				$mixed_data_entitle = array(); $i = 0;
				while (isset($_GET['mixed_data_entitle_' . $i])) $mixed_data_entitle[] = rawurldecode($_GET['mixed_data_entitle_' . $i++]);
				$ip->mixed_data_entitle = serialize($mixed_data_entitle);
				
				$url_docs = array();
				for ($i = 1; $i <= 3; $i++) if (isset($_GET['url_doc' . $i]) && !empty($_GET['url_doc' . $i])) $url_docs[] = rawurldecode($_GET['url_doc' . $i]);
				$ip->url_docs = serialize($url_docs);
				
				$ip->ref_count = isset($_GET['ref_count']) ? (int)$_GET['ref_count'] : 0;
				
				$ip->references = array();
				for ($i = 0; $i < $ip->ref_count; $i++)
				{
					$ip->references[$i] = array();
					$ip->references[$i]['id'] = isset($_GET['id_ref_'.$i]) ? (int)$_GET['id_ref_'.$i] : 0;
					$ip->references[$i]['ref_supplier'] = isset($_GET['ref_supplier_'.$i]) ? rawurldecode($_GET['ref_supplier_'.$i]) : "";
					$ip->references[$i]['label'] = isset($_GET['label_'.$i]) ? rawurldecode($_GET['label_'.$i]) : "";
					$ip->references[$i]['unit'] = isset($_GET['unit_'.$i]) ? (int)$_GET['unit_'.$i] : 1;
					$ip->references[$i]['VAT'] = isset($_GET['VAT_'.$i]) ? (float)$_GET['VAT_'.$i] : 0;
					$ip->references[$i]['price2'] = isset($_GET['price2_'.$i]) ? (float)$_GET['price2_'.$i] : 0;
					$ip->references[$i]['marge'] = isset($_GET['marge_'.$i]) ? (float)$_GET['marge_'.$i] : 0;
					$ip->references[$i]['price'] = isset($_GET['price_'.$i]) ? (float)$_GET['price_'.$i] : 0;
					$ip->references[$i]['price_deleted'] = isset($_GET['price_deleted_'.$i]) ? (float)$_GET['price_deleted_'.$i] : 0;
					$ip->references[$i]['order'] = isset($_GET['order_'.$i]) ? (int)$_GET['order_'.$i] : 0;
					
					$mixed_data = array(); $j = 0;
					while (isset($_GET['mixed_data_'.$i.'_'.$j])) $mixed_data[] = rawurldecode($_GET['mixed_data_'.$i.'_'.$j++]);
					$ip->references[$i]['mixed_data'] = serialize($mixed_data);
				}
				
				$ip->UpdateStatus();
				if ($ip->status == __IP_NOT_VALID__) $es .= $ip->lastErrorMessage . __ERROR_SEPARATOR__;
				if ($ip->Save())
				{
					$imp = & new Import($handle, $ip->id_import);
					$imp->UpdateStatus();
					$imp->Save();
					
					$array_br = array("<br>", "<BR>", "<Br>", "<bR>", "<br/>", "<BR/>", "<Br/>", "<bR/>");
					$os .= "id"					. __OUTPUTID_SEPARATOR__ . $ip->id . __OUTPUT_SEPARATOR__;
					$os .= "name"				. __OUTPUTID_SEPARATOR__ . $ip->name . __OUTPUT_SEPARATOR__;
					$os .= "family_name"		. __OUTPUTID_SEPARATOR__ . $ip->family_name . __OUTPUT_SEPARATOR__;
					$os .= "fastdesc"			. __OUTPUTID_SEPARATOR__ . $ip->fastdesc . __OUTPUT_SEPARATOR__;
					$os .= "descc"				. __OUTPUTID_SEPARATOR__ . substr(str_replace($array_br, " ", $ip->descc), 0, 30) . "..." . __OUTPUT_SEPARATOR__;
					$os .= "descd"				. __OUTPUTID_SEPARATOR__ . substr(str_replace($array_br, " ", $ip->descd), 0, 30) . "..." . __OUTPUT_SEPARATOR__;
					$os .= "delivery_time"		. __OUTPUTID_SEPARATOR__ . $ip->delivery_time . __OUTPUT_SEPARATOR__;
					$os .= "url_image"			. __OUTPUTID_SEPARATOR__ . $ip->url_image . __OUTPUT_SEPARATOR__;
					$os .= "ref_count"			. __OUTPUTID_SEPARATOR__ . $ip->ref_count . __OUTPUT_SEPARATOR__;
					$os .= "status"				. __OUTPUTID_SEPARATOR__ . $ip->status . __OUTPUT_SEPARATOR__;
				}
				else $es .= $ip->lastErrorMessage . __ERROR_SEPARATOR__;
				
			}
			else $es .= "ID du produit à modifier non spécifiée" . __ERROR_SEPARATOR__;
			break;
			
		case "delete" :
      if (!$user->get_permissions()->has("m-prod--sm-import","d")) {
        $es .= "Vous n'avez pas les droits adéquats pour réaliser cette opération.".__ERROR_SEPARATOR__;
        break;
      }
			$os .= "delete" . __OUTPUT_SEPARATOR__;
			if (isset($_GET['id']))
			{
				settype($_GET['id'], "integer");
				$ip = & new ImportProduct($handle, $_GET['id']);
				if (DeleteProduct($handle, $ip->id))
				{
					$imp = & new Import($handle, $ip->id_import);
					$imp->UpdateStatus();
					$imp->Save();
					$os .= "OK" . __OUTPUT_SEPARATOR__;
				}
				else $es .= "Erreur fatale SQL lors de la supression du produit " . $_GET['id'] . ".";
			}
			else $es .= "ID du produit à supprimer non spécifiée" . __ERROR_SEPARATOR__;
			break;
			
		case "finalize" :
      if (!$user->get_permissions()->has("m-prod--sm-import","e")) {
        $es .= "Vous n'avez pas les droits adéquats pour réaliser cette opération.".__ERROR_SEPARATOR__;
        break;
      }
			$os .= "finalize" . __OUTPUT_SEPARATOR__;
			if (isset($_GET['id']))
			{
				settype($_GET['id'], "integer");
				$ip = & new ImportProduct($handle, $_GET['id']);
				
				$ip->UpdateStatus();
				if ($ip->finalize())
				{
					if ($ip->Save())
					{
						$imp = & new Import($handle, $ip->id_import);
						$imp->UpdateStatus();
						$imp->Save();
						
						$os .= $ip->id . __OUTPUT_SEPARATOR__ . $ip->status . __OUTPUT_SEPARATOR__;
					}
					else $es .= $ip->lastErrorMessage . __ERROR_SEPARATOR__;
				}
				else $es .= "Impossible de finaliser l'import du produit : " . $ip->lastErrorMessage;
				
			}
			else $es .= "ID du produit à finaliser non spécifiée" . __ERROR_SEPARATOR__;
			break;
			
		case "multi-alter" :
      if (!$user->get_permissions()->has("m-prod--sm-import","e")) {
        $es .= "Vous n'avez pas les droits adéquats pour réaliser cette opération.".__ERROR_SEPARATOR__;
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
      if (!$user->get_permissions()->has("m-prod--sm-import","d")) {
        $es .= "Vous n'avez pas les droits adéquats pour réaliser cette opération.".__ERROR_SEPARATOR__;
        break;
      }
			if (isset($_GET['ids']))
			{
				
			}
			else $es .= "IDs des produits à charger non spécifiées" . __ERROR_SEPARATOR__;
			break;
			
		case "multi-finalize" :
      if (!$user->get_permissions()->has("m-prod--sm-import","e")) {
        $es .= "Vous n'avez pas les droits adéquats pour réaliser cette opération.".__ERROR_SEPARATOR__;
        break;
      }
			$os .= "multi-finalize" . __OUTPUT_SEPARATOR__;
			if (isset($_GET['ids']))
			{
				$ids = explode("-", $_GET['ids']);
				$len = count($ids) - 1;
				if ($len > 0)
				{
					$importsAffected = array();
					for ($i = 0; $i < $len; $i++)
					{
						settype($ids[$i], "integer");
						$ip = & new ImportProduct($handle, $ids[$i]);
						if ($ip->exist)
						{
							if ($ip->finalize())
							{
								if (!$ip->Save()) $es .= $ip->lastErrorMessage . __ERROR_SEPARATOR__;
								$os .= $ip->id . __OUTPUT_SEPARATOR__ . $ip->status . __OUTPUT_SEPARATOR__;
							}
							else $es .= "Impossible de finaliser l'import du produit : " . $ip->lastErrorMessage . __ERROR_SEPARATOR__;
							
						}
						$importsAffected[$ip->id_import] = true;
						unset($ip);
					}
					foreach ($importsAffected as $id => $value)
					{
						$imp = & new Import($handle, $id);
						$imp->UpdateStatus();
						$imp->Save();
						unset($imp);
					}
				}
				else $es .= "Il n'y a aucun produit à finaliser" . __ERROR_SEPARATOR__;
			}
			else $es .= "IDs des produits à finaliser non spécifiées" . __ERROR_SEPARATOR__;
			break;
			
		default : break;
	}
}

print $es . __MAIN_SEPARATOR__ . $os;

exit();

?>
