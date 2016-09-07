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
require(ICLASS . "_ClassPromotion.php");

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
				$prom = new Promotion($handle, (int)$_GET['id']);
				if (!$prom->exist)
				{
					$o["error"] = "La Promotion ayant pour id " . $_GET['id'] . " n'existe pas";
					break;
				}
				$o["data"]["id"] = $prom->id;
				$o["data"]["type"] = $prom->type;
				$o["data"]["type_value"] = $prom->type_value;
				$o["data"]["apply"] = $prom->apply;
				$o["data"]["apply_value"] = $prom->apply_value;
				$o["data"]["end_trigger"] = $prom->end_trigger;
				$o["data"]["end_trigger_value"] = $prom->end_trigger_value;
				$o["data"]["end_trigger_current"] = $prom->end_trigger_current;
				$o["data"]["code"] = $prom->code;
				$o["data"]["picture"] = $prom->picture;
				$o["data"]["start_time"] = $prom->start_time;
				$o["data"]["end_time"] = $prom->end_time;
				$o["data"]["active"] = $prom->active;
				$o["data"]["create_time"] = $prom->create_time;
				$o["data"]["timestamp"] = $prom->timestamp;
			}
			else $o["error"] = "ID de la promotion non spécifiée";
			break;
			
		case "alter" :
      if (!$user->get_permissions()->has("m-mark--sm-discounts-promotions","e")) {
        $o["error"] = "Vous n'avez pas les droits adéquats pour réaliser cette opération.";
        break;
      }
			if (isset($_GET['id'])
			&& isset($_GET['type'])
			&& isset($_GET['type_value'])
			&& isset($_GET['apply'])
			&& isset($_GET['apply_value'])
			&& isset($_GET['end_trigger'])
			&& isset($_GET['end_trigger_value'])
			&& isset($_GET['end_trigger_current'])
			&& isset($_GET['code'])
			&& isset($_GET['picture'])
			&& isset($_GET['start_time'])
			&& isset($_GET['end_time'])
			&& isset($_GET['active']))
			{
				$prom = new Promotion($handle, (int)$_GET['id']);
				if (!$prom->exist)
				{
					$o["error"] = "La Promotion ayant pour id " . $_GET['id'] . " n'existe pas";
					break;
				}
				
				$prom->type = (int)$_GET['type'];
				$prom->type_value = (float)$_GET['type_value'];
				/*
				$apply_values = explode("|", $_GET['apply_value']);
				foreach ($apply_values as $k1 => $v1) {
					$apply_values[$k1] = explode(",", $v1);
					foreach($apply_values[$k1] as $k2 => $v2) $apply_values[$k1][$k2] = (int)$v2;
					$apply_values[$k1] = implode(",", $apply_values[$k1]);
				}
				$prom->apply_value = implode("|", $apply_values);
				*/
				$prom->apply = (int)$_GET['apply'];
				list($ava, $avc, $avp) = explode(";", $_GET['apply_value']);
				// Apply Values for Advertisers
				$ava = explode(",", $ava);
				foreach($ava as $k => $v) $ava[$k] = (int)$v;
				$ava = implode(",", $ava);
				// Apply Values for Categories
				$avc = explode(",", $avc);
				foreach($avc as $k => $v) $avc[$k] = (int)$v;
				$avc = implode(",", $avc);
				// Apply Values for Products
				$avp = explode("|", $avp);
				foreach ($avp as $k1 => $v1) {
					$avp[$k1] = explode(",", $v1);
					foreach($avp[$k1] as $k2 => $v2) $avp[$k1][$k2] = (int)$v2;
					$avp[$k1] = implode(",", $avp[$k1]);
				}
				$avp = implode("|", $avp);
				$prom->apply_value = implode(";", array($ava, $avc, $avp));
				
				$prom->end_trigger = (int)$_GET['end_trigger'];
				$prom->end_trigger_value = (float)$_GET['end_trigger_value'];
				$prom->end_trigger_current = (float)$_GET['end_trigger_current'];
				$prom->code = $_GET['code'];
				$prom->picture = $_GET['picture'];
				$prom->start_time = (int)$_GET['start_time'];
				$prom->end_time = (int)$_GET['end_time'];
				$prom->active = (int)$_GET['active'];
				
				$prom->Save();
				$prom->Load();
				
				$o["data"]["id"] = $prom->id;
				$o["data"]["type"] = $prom->type;
				$o["data"]["type_value"] = $prom->type_value;
				$o["data"]["apply"] = $prom->apply;
				$o["data"]["apply_value"] = $prom->apply_value;
				$o["data"]["end_trigger"] = $prom->end_trigger;
				$o["data"]["end_trigger_value"] = $prom->end_trigger_value;
				$o["data"]["end_trigger_current"] = $prom->end_trigger_current;
				$o["data"]["code"] = $prom->code;
				$o["data"]["picture"] = $prom->picture;
				$o["data"]["start_time"] = $prom->start_time;
				$o["data"]["end_time"] = $prom->end_time;
				$o["data"]["active"] = $prom->active;
				$o["data"]["create_time"] = $prom->create_time;
				$o["data"]["timestamp"] = $prom->timestamp;
				
			}
			else $o["error"] = "Un ou plusieurs paramètres sont manquants";
			break;
			
		case "add" :
      if (!$user->get_permissions()->has("m-mark--sm-discounts-promotions","e")) {
        $o["error"] = "Vous n'avez pas les droits adéquats pour réaliser cette opération.";
        break;
      }
			if (isset($_GET['type'])
			&& isset($_GET['type_value'])
			&& isset($_GET['apply'])
			&& isset($_GET['apply_value'])
			&& isset($_GET['end_trigger'])
			&& isset($_GET['end_trigger_value'])
			&& isset($_GET['end_trigger_current'])
			&& isset($_GET['code'])
			&& isset($_GET['picture'])
			&& isset($_GET['start_time'])
			&& isset($_GET['end_time'])
			&& isset($_GET['active']))
			{
				$prom = new Promotion($handle);
				$prom->Create();
				
				$prom->type = (int)$_GET['type'];
				$prom->type_value = (float)$_GET['type_value'];
				
				$prom->apply = (int)$_GET['apply'];
				$apply_values = explode("|", $_GET['apply_value']);
				foreach ($apply_values as $k1 => $v1) {
					$apply_values[$k1] = explode(",", $v1);
					foreach($apply_values[$k1] as $k2 => $v2) $apply_values[$k1][$k2] = (int)$v2;
					$apply_values[$k1] = implode(",", $apply_values[$k1]);
				}
				$prom->apply_value = implode("|", $apply_values);
				
				$prom->end_trigger = (int)$_GET['end_trigger'];
				$prom->end_trigger_value = (float)$_GET['end_trigger_value'];
				$prom->end_trigger_current = (float)$_GET['end_trigger_current'];
				$prom->code = $_GET['code'];
				$prom->picture = $_GET['picture'];
				$prom->start_time = (int)$_GET['start_time'];
				$prom->end_time = (int)$_GET['end_time'];
				$prom->active = (int)$_GET['active'];
				
				$prom->Save();
				$prom->Load();
				
				$o["data"]["id"] = $prom->id;
				$o["data"]["type"] = $prom->type;
				$o["data"]["type_value"] = $prom->type_value;
				$o["data"]["apply"] = $prom->apply;
				$o["data"]["apply_value"] = $prom->apply_value;
				$o["data"]["end_trigger"] = $prom->end_trigger;
				$o["data"]["end_trigger_value"] = $prom->end_trigger_value;
				$o["data"]["end_trigger_current"] = $prom->end_trigger_current;
				$o["data"]["code"] = $prom->code;
				$o["data"]["picture"] = $prom->picture;
				$o["data"]["start_time"] = $prom->start_time;
				$o["data"]["end_time"] = $prom->end_time;
				$o["data"]["active"] = $prom->active;
				$o["data"]["create_time"] = $prom->create_time;
				$o["data"]["timestamp"] = $prom->timestamp;
				
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
				if ($handle->query("delete from promotions where id = " . $id, __FILE__, __LINE__))
				{
					$o["data"]["id"] = $id;
				}
				else $o["error"] = "Erreur lors de la supression de la promotion " . $id;
			}
			else $o["error"] = "ID de la promotion non spécifiée";
			break;
			
		default : break;
	}
}
else $o["error"] = "Aucune action n'a été spécifiée";

print json_encode($o);

exit();

?>
