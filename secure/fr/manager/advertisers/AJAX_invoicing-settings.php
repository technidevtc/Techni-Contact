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

require(ADMIN."logs.php");

$handle = DBHandle::get_instance();
$user = new BOUser();

header("Content-Type: text/plain; charset=utf-8");

function rawurldecodeEuro ($str) { return str_replace("%u20AC", "€", rawurldecode($str)); }

$o = array();
if(!$user->login()) {
	$o["error"] = "Votre session a expirée, veuillez réactualiser la page pour retourner à la page de login";
	print json_encode($o);
	exit();
}

if (isset($_GET['action'])) {
	switch ($_GET['action']) {
		case "get" :
			if (isset($_GET['advID'])) {
				$res = & $handle->query("SELECT is_fields FROM advertisers WHERE id = ".(int)$_GET['advID'], __FILE__, __LINE__);
				if ($handle->numrows($res, __FILE__, __LINE__) > 0) {
					list($is_fields) = $handle->fetch($res);
					$is_fields = mb_unserialize($is_fields);
          if (!is_array($is_fields))
            $is_fields = array();
					$o = $is_fields;
				}
			}
			else $o["error"] = "ID du fournisseur non spécifiée";
			break;
			
		case "add" :
			if (isset($_GET['advID']) && isset($_GET['is_type']) && isset($_GET['is_field'])) {
				if (empty($_GET['is_type']) || empty($_GET['is_field']))
					$o["error"] = "Valeur manquante";
				else {
					$advID = (int)$_GET['advID'];
					$is_type = $_GET['is_type'];
					$is_field = json_decode(rawurldecode($_GET['is_field']));
					$valid = true;
					foreach($is_field as $v) {
						if (empty($v)) {
							$valid = false;
							break;
						}
					}
					if ($valid) {
						$res = $handle->query("SELECT category, nom1 AS name, is_fields FROM advertisers WHERE id = ".$advID, __FILE__, __LINE__);
						if ($handle->numrows($res, __FILE__, __LINE__) > 0) {
							$adv = $handle->fetchAssoc($res);
              $is_fields = mb_unserialize($adv['is_fields']);
              if (!is_array($is_fields))
                $is_fields = array();
							array_unshift($is_fields, array("date" => time(), "type" => $is_type, "fields" => $is_field));
							if (count($is_fields) > 10)
                array_pop($is_fields);
							$o = $is_fields;
							// update
              $is_fields_s = serialize($is_fields);
							$handle->query("UPDATE advertisers SET is_fields = '".$handle->escape($is_fields_s)."', timestamp = ".time()." WHERE id = ".$advID, __FILE__, __LINE__);
              // log
              $mlog = "Edition ".$adv_cat_list[$adv['category']]['pre']." ".$adv['name']." / Ajout d'un nouveau mode de facturation : ".json_encode($is_fields[0]).(empty($is_fields[1]) ? "" : " / Ancien : ".json_encode($is_fields[1]));
              ManagerLog($handle, $_SESSION['id'], $_SESSION['login'], $_SESSION['pass'], $_SESSION['ip'], $mlog);
						}
						else $o["error"] = "Erreur fatale : l'ID de l'annonceur spécifié n'existe pas";
					}
					else $o["error"] = "Un ou plusieurs champs sont manquants";
				}
			}
			else $o["error"] = "Un ou plusieurs paramètres sont manquants";
			break;
			
		case "undo" :
			if (isset($_GET['advID'])) {
				$advID = (int)$_GET['advID'];
				$res = $handle->query("SELECT category, nom1 AS name, is_fields FROM advertisers WHERE id = ".$advID , __FILE__, __LINE__);
				if ($handle->numrows($res, __FILE__, __LINE__) > 0) {
					$adv = $handle->fetchAssoc($res);
					$is_fields = mb_unserialize($adv['is_fields']);
					
					if (is_array($is_fields)) {
						$istd = array_shift($is_fields);
						$o = $is_fields;
						// update
            $is_fields_s = serialize($is_fields);
						$handle->query("UPDATE advertisers SET is_fields = '".$handle->escape($is_fields_s)."', timestamp = ".time()." WHERE id = ".$advID , __FILE__, __LINE__);
            // log
            $mlog = "Edition ".$adv_cat_list[$adv['category']]['pre']." ".$adv['name']." / Suppression du dernier mode de facturation".(empty($is_fields[0]) ? "" : " / Nouveau mode de facturation : ".json_encode($is_fields[0]));
            ManagerLog($handle, $_SESSION['id'], $_SESSION['login'], $_SESSION['pass'], $_SESSION['ip'], $mlog);
					}
				}
			}
			else $o["error"] = "ID de l'annonceur non spécifiée";
			break;
			
		default : break;
	}
}
else $o = "Aucune action n'a été spécifiée";

print json_encode($o);
