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
require(ICLASS . "_ClassDiscount.php");

$handle = DBHandle::get_instance();
$user = new BOUser();

header("Content-Type: text/plain; charset=utf-8");

if(!$user->login())
{
	print "Votre session a expirée, veuillez réactualiser la page pour retourner à la page de login" . __MAIN_SEPARATOR__;
	exit();
}

function rawurldecodeEuro ($str) { return str_replace("%u20AC", "€", rawurldecode($str)); }

$o = array();

if (isset($_GET['action']))
{
	$o["action"] = $_GET['action'];
	switch ($_GET['action'])
	{
		case "get" :
      if (!$user->get_permissions()->has("m-mark--sm-discounts-promotions","r")) {
        $o["error"] = "Vous n'avez pas les droits adéquats pour réaliser cette opération.";
        break;
      }
			if (isset($_GET['id']))
			{
				$disc = new Discount($handle, (int)$_GET['id']);
				if (!$disc->exist)
				{
					$o["error"] = "La Remise ayant pour id " . $_GET['id'] . " n'existe pas";
					break;
				}
				$o["data"]["id"] = $disc->id;
				$o["data"]["idAdvertiser"] = $disc->idAdvertiser;
				$o["data"]["advName"] = $disc->advName;
				$o["data"]["type"] = $disc->type;
				$o["data"]["type_value"] = $disc->type_value;
				$o["data"]["value"] = $disc->value;
				$o["data"]["apply"] = $disc->apply;
				$o["data"]["apply_value"] = $disc->apply_value;
				$o["data"]["priority"] = $disc->priority;
				$o["data"]["create_time"] = $disc->create_time;
				$o["data"]["timestamp"] = $disc->timestamp;
			}
			else $o["error"] = "ID de la promotion non spécifiée";
			break;
			
		case "alter" :
      if (!$user->get_permissions()->has("m-mark--sm-discounts-promotions","e")) {
        $o["error"] = "Vous n'avez pas les droits adéquats pour réaliser cette opération.";
        break;
      }
			if (isset($_GET['id'])
			&& isset($_GET['idAdvertiser'])
			&& isset($_GET['type'])
			&& isset($_GET['type_value'])
			&& isset($_GET['value'])
			&& isset($_GET['apply'])
			&& isset($_GET['apply_value'])
			&& isset($_GET['priority']))
			{
				$disc = new Discount($handle, (int)$_GET['id']);
				if (!$disc->exist)
				{
					$o["error"] = "La Remise ayant pour id " . $_GET['id'] . " n'existe pas";
					break;
				}
				
				$disc->idAdvertiser = (int)$_GET['idAdvertiser'];
				$disc->type = (int)$_GET['type'];
				$disc->type_value = (float)$_GET['type_value'];
				$disc->value = (float)$_GET['value'];
				
				$disc->apply = (int)$_GET['apply'];
				$apply_values = explode("|", $_GET['apply_value']);
				foreach ($apply_values as $k1 => $v1) {
					$apply_values[$k1] = explode(",", $v1);
					foreach($apply_values[$k1] as $k2 => $v2) $apply_values[$k1][$k2] = (int)$v2;
					$apply_values[$k1] = implode(",", $apply_values[$k1]);
				}
				$disc->apply_value = implode("|", $apply_values);
				
				$disc->priority = (int)$_GET['priority'];
				
				$disc->Save();
				$disc->Load();
				
				$o["data"]["id"] = $disc->id;
				$o["data"]["idAdvertiser"] = $disc->idAdvertiser;
				$o["data"]["advName"] = $disc->advName;
				$o["data"]["type"] = $disc->type;
				$o["data"]["type_value"] = $disc->type_value;
				$o["data"]["value"] = $disc->value;
				$o["data"]["apply"] = $disc->apply;
				$o["data"]["apply_value"] = $disc->apply_value;
				$o["data"]["priority"] = $disc->priority;
				$o["data"]["create_time"] = $disc->create_time;
				$o["data"]["timestamp"] = $disc->timestamp;
				
			}
			else $o["error"] = "Un ou plusieurs paramètres sont manquants";
			break;
			
		case "add" :
      if (!$user->get_permissions()->has("m-mark--sm-discounts-promotions","e")) {
        $o["error"] = "Vous n'avez pas les droits adéquats pour réaliser cette opération.";
        break;
      }
			if (isset($_GET['idAdvertiser'])
			&& isset($_GET['type'])
			&& isset($_GET['type_value'])
			&& isset($_GET['value'])
			&& isset($_GET['apply'])
			&& isset($_GET['apply_value'])
			&& isset($_GET['priority']))
			{
				$disc = new Discount($handle);
				$disc->Create();
				
				$disc->idAdvertiser = (int)$_GET['idAdvertiser'];
				$disc->type = (int)$_GET['type'];
				$disc->type_value = (float)$_GET['type_value'];
				$disc->value = (float)$_GET['value'];
				
				$disc->apply = (int)$_GET['apply'];
				$apply_values = explode("|", $_GET['apply_value']);
				foreach ($apply_values as $k1 => $v1) {
					$apply_values[$k1] = explode(",", $v1);
					foreach($apply_values[$k1] as $k2 => $v2) $apply_values[$k1][$k2] = (int)$v2;
					$apply_values[$k1] = implode(",", $apply_values[$k1]);
				}
				$disc->apply_value = implode("|", $apply_values);
				
				$disc->priority = (int)$_GET['priority'];
				
				$disc->Save();
				$disc->Load();
				
				$o["data"]["id"] = $disc->id;
				$o["data"]["idAdvertiser"] = $disc->idAdvertiser;
				$o["data"]["advName"] = $disc->advName;
				$o["data"]["type"] = $disc->type;
				$o["data"]["type_value"] = $disc->type_value;
				$o["data"]["value"] = $disc->value;
				$o["data"]["apply"] = $disc->apply;
				$o["data"]["apply_value"] = $disc->apply_value;
				$o["data"]["priority"] = $disc->priority;
				$o["data"]["create_time"] = $disc->create_time;
				$o["data"]["timestamp"] = $disc->timestamp;
				
			}
			else $o["error"] = "Un ou plusieurs paramètres sont manquants";
			break;
			
		case "delete" :
      if (!$user->get_permissions()->has("m-mark--sm-discounts-promotions","d")) {
        $o["error"] = "Vous n'avez pas les droits adéquats pour réaliser cette opération.";
        break;
      }
			if (isset($_GET['id']))
			{
				$id = (int)$_GET['id'];
				if ($handle->query("delete from discounts where id = " . $id, __FILE__, __LINE__))
				{
					$o["data"]["id"] = $id;
				}
				else $o["error"] = "Erreur lors de la supression de la remise " . $id;
			}
			else $o["error"] = "ID de la remise non spécifiée";
			break;
			
		default : break;
	}
}
else $o["error"] = "Aucune action n'a été spécifiée";

print json_encode($o);

exit();

?>
