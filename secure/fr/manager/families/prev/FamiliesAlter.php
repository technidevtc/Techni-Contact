<?php

/*================================================================/

 Techni-Contact V4 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 2 janvier 2006

 Fichier : /secure/manager/families/FamiliesAlter.php
 Description : Fichier interface de manipulation des familles AJAX

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

require(ADMIN."logs.php");
require(ADMIN."generator.php");

$handle = DBHandle::get_instance();
$user = new BOUser();

/* Vérifier l'unicité d'un champ
   i : référence handle connexion
   i : champ à tester
   i : référence valeur à tester
   o : true si unique false sinon */
function isFUnique(& $handle, $field, & $value, $id2ignore = -1)
{
	$result = & $handle->query("select id from families_fr where " . $field . " = '" . $handle->escape($value) . "' and id != " . $id2ignore, __FILE__, __LINE__);
	if ($handle->numrows($result, __FILE__, __LINE__) == 0) return true;
	else return false;
}

header("Content-Type: text/html; charset=UTF-8");

if(!$user->login())
{
	print "FamiliesError" . __ERRORID_SEPARATOR__ . "Votre session a expirée, vous devez vous relogger." . __ERROR_SEPARATOR__ . __MAIN_SEPARATOR__;
	exit();
}

$FamilyAction = isset($_GET['FamilyAction']) ? trim($_GET['FamilyAction']) : '';
$FamilyID = isset($_GET['FamilyID'])  ? trim($_GET['FamilyID'])  : '';
$FamilyName = isset($_GET['FamilyName']) ? trim($_GET['FamilyName']) : '';
$FamilyParentID = isset($_GET['FamilyParentID'])  ? trim($_GET['FamilyParentID'])  : '';
$FamilyRefName = isset($_GET['FamilyRefName']) ? trim($_GET['FamilyRefName']) : '';
$FamilyTitle = isset($_GET['FamilyTitle']) ? trim($_GET['FamilyTitle']) : '';
$FamilyDesc = isset($_GET['FamilyDesc']) ? trim($_GET['FamilyDesc']) : '';
$FamilyContent = isset($_GET['FamilyContent']) ? trim($_GET['FamilyContent']) : '';

$es = $os = '';

$name = urldecode($FamilyName);
$ref_name = urldecode($FamilyRefName);
if (empty($ref_name))
  $ref_name = Utils::toDashAz09($name);
$title = urldecode($FamilyTitle);
$meta_desc = urldecode($FamilyDesc);
$text_content = urldecode($FamilyContent);
switch ($FamilyAction)
{
	case 'add' :
		if (!preg_match('/^[0-9]+$/', $FamilyID)) $es .= "L'identifiant de la famille est invalide<br />\n";
		if (!preg_match('/^[0-9]+$/', $FamilyParentID)) $es .= "L'identifiant de la famille parente est invalide<br />\n";
		if ($name == '') $es .= "Veuillez saisir un nom de famille<br />\n";
		if (!$user->get_permissions()->has("m-prod--sm-categories","e")) {
      $es .= "Vous n'avez pas les droits adéquats pour réaliser cette opération.";
      break;
    }
		if ($es == '')
		{
			$FamilyID = generateID(12, 9999, 'id', 'families', $handle);
			if (isFUnique($handle, "ref_name", $ref_name))
			{
				if ($handle->query("insert into families (id, idParent) values(" . $FamilyID . ", '" . $FamilyParentID . "')", __FILE__, __LINE__))
				{
					if($handle->query("insert into families_fr (id, name, ref_name) values(" . $FamilyID . ", '" . $handle->escape($name) . "', '" . $handle->escape($ref_name) . "')", __FILE__, __LINE__))
					{
						$ret = true;
						$os .= "FamilyAdd" . __OUTPUTID_SEPARATOR__ .
						"id" . __DATA_SEPARATOR__ . $FamilyID . __DATA_SEPARATOR__ .
						"name" . __DATA_SEPARATOR__ .$name . __DATA_SEPARATOR__ .
						"ref_name" . __DATA_SEPARATOR__ . $ref_name . __DATA_SEPARATOR__ .
            "title" . __DATA_SEPARATOR__ . $title . __DATA_SEPARATOR__ .
            "meta_desc" . __DATA_SEPARATOR__ . $meta_desc . __DATA_SEPARATOR__ .
                                                "text_content" . __DATA_SEPARATOR__ . $text_content . __DATA_SEPARATOR__ .
						"idParent" . __DATA_SEPARATOR__ . $FamilyParentID . __DATA_SEPARATOR__ . __OUTPUT_SEPARATOR__;
						ManagerLog($handle, $_SESSION['id'], $_SESSION['login'], $_SESSION['pass'], $_SESSION['ip'],  'Création de la famille ' . $name . ' (ID : ' . $FamilyID . ', Nom Google : ' . $ref_name . ', Parent : ' . $FamilyParentID . ')');
					}
					else
					{
						$es .= "Erreur fatale lors de la création de la famille" . $name;
						$handle->query("delete from families where id = " . $FamilyID, __FILE__, __LINE__);
					}
				}
				else $es = "Erreur fatale lors de la création de la famille";
			}
			else $es = "Impossible de créer la famille " . $name . " : une famille portant ce nom existe déjà";
		}
		break;
	
	case 'editName' :
		if (!preg_match('/^[0-9]+$/', $FamilyID)) $es .= "L'identifiant de la famille est invalide<br />\n";
		if ($name == '') $es .= "Veuillez saisir un nom de famille<br />\n";
		if (!$user->get_permissions()->has("m-prod--sm-categories","e")) {
      $es .= "Vous n'avez pas les droits adéquats pour réaliser cette opération.";
      break;
    }
		if ($es == '')
		{
			if (isFUnique($handle, "ref_name", $ref_name, $FamilyID))
			{
				if ($handle->query("update families_fr set name = '" . $handle->escape($name) . "', ref_name = '" . $handle->escape($ref_name) . "' where id = " . $FamilyID, __FILE__, __LINE__, false))
				{
					$os .= "FamilyEdit" . __OUTPUTID_SEPARATOR__ .
					"id" . __DATA_SEPARATOR__ . $FamilyID . __DATA_SEPARATOR__ .
					"name" . __DATA_SEPARATOR__ .$name . __DATA_SEPARATOR__ .
					"ref_name" . __DATA_SEPARATOR__ . $ref_name . __DATA_SEPARATOR__ .
          "title" . __DATA_SEPARATOR__ . $title . __DATA_SEPARATOR__ .
          "meta_desc" . __DATA_SEPARATOR__ . $meta_desc . __DATA_SEPARATOR__ .
                                  "text_content" . __DATA_SEPARATOR__ . $text_content . __DATA_SEPARATOR__ .
					"idParent" . __DATA_SEPARATOR__ . $FamilyParentID . __DATA_SEPARATOR__ . __OUTPUT_SEPARATOR__;
					ManagerLog($handle, $_SESSION['id'], $_SESSION['login'], $_SESSION['pass'], $_SESSION['ip'],  'Edition de la famille ' . $name . ' (ID : ' . $FamilyID . ', Nom Google : ' . $ref_name . ')');
				}
				else $es .= "Erreur fatale lors de la mise à jour de la famille " . $name;
			}
			else $es = "Impossible de renommer la famille " . $name . " : une famille portant ce nom existe déjà";
		}
		break;
	
	case 'editParent' :
		if (!preg_match('/^[0-9]+$/', $FamilyID)) $es .= "L'identifiant de la famille est invalide<br />\n";
		if (!preg_match('/^[0-9]+$/', $FamilyParentID)) $es .= "L'identifiant de la famille parente est invalide<br />\n";
		if (!$user->get_permissions()->has("m-prod--sm-categories","e")) {
      $es .= "Vous n'avez pas les droits adéquats pour réaliser cette opération.";
      break;
    }
		if ($es == '')
		{
			if ($handle->query("update families set idParent = " . $FamilyParentID . " where id = " . $FamilyID, __FILE__, __LINE__, false))
			{
				$os .= "FamilyEdit" . __OUTPUTID_SEPARATOR__ .
				"id" . __DATA_SEPARATOR__ . $FamilyID . __DATA_SEPARATOR__ .
				"name" . __DATA_SEPARATOR__ .$name . __DATA_SEPARATOR__ .
				"ref_name" . __DATA_SEPARATOR__ . $ref_name . __DATA_SEPARATOR__ .
        "title" . __DATA_SEPARATOR__ . $title . __DATA_SEPARATOR__ .
        "meta_desc" . __DATA_SEPARATOR__ . $meta_desc . __DATA_SEPARATOR__ .
                          "text_content" . __DATA_SEPARATOR__ . $text_content . __DATA_SEPARATOR__ .
				"idParent" . __DATA_SEPARATOR__ . $FamilyParentID . __DATA_SEPARATOR__ . __OUTPUT_SEPARATOR__;
				ManagerLog($handle, $_SESSION['id'], $_SESSION['login'], $_SESSION['pass'], $_SESSION['ip'],  'Edition de la famille ' . $name . ' (ID : ' . $FamilyID . ', Nom Google : ' . $ref_name . ')');
			}
			else $es .= "Erreur fatale lors de la mise à jour de la famille " . $name;
		}
		break;
	
	case "editRefName" :
		if (!preg_match("/^[0-9]+$/", $FamilyID)) $es .= "L'identifiant de la famille est invalide<br/>\n";
		if (!$user->get_permissions()->has("m-prod--sm-categories","e")) {
      $es .= "Vous n'avez pas les droits adéquats pour réaliser cette opération.";
      break;
    }
		if ($es == "") {
      $handle->query("UPDATE families_fr SET ref_name = '".$handle->escape($ref_name)."' WHERE id = ".$FamilyID, __FILE__, __LINE__, false);
      $os .= "FamilyEdit" . __OUTPUTID_SEPARATOR__ .
      "id" . __DATA_SEPARATOR__ . $FamilyID . __DATA_SEPARATOR__ .
      "name" . __DATA_SEPARATOR__ .$name . __DATA_SEPARATOR__ .
      "ref_name" . __DATA_SEPARATOR__ . $ref_name . __DATA_SEPARATOR__ .
      "title" . __DATA_SEPARATOR__ . $title . __DATA_SEPARATOR__ .
      "meta_desc" . __DATA_SEPARATOR__ . $meta_desc . __DATA_SEPARATOR__ .
      "text_content" . __DATA_SEPARATOR__ . $text_content . __DATA_SEPARATOR__ .
      "idParent" . __DATA_SEPARATOR__ . $FamilyParentID . __DATA_SEPARATOR__ . __OUTPUT_SEPARATOR__;
      ManagerLog($handle, $_SESSION['id'], $_SESSION['login'], $_SESSION['pass'], $_SESSION['ip'],  'Edition de la famille ' . $name . ' (ID : ' . $FamilyID . ', Nom Google : ' . $ref_name . ')');
		}
    break;
  
	case "editTitle" :
		if (!preg_match("/^[0-9]+$/", $FamilyID)) $es .= "L'identifiant de la famille est invalide<br/>\n";
		if (!$user->get_permissions()->has("m-prod--sm-categories","e")) {
      $es .= "Vous n'avez pas les droits adéquats pour réaliser cette opération.";
      break;
    }
		if ($es == "") {
      $handle->query("UPDATE families_fr SET title = '".$handle->escape($title)."' WHERE id = ".$FamilyID, __FILE__, __LINE__, false);
      $os .= "FamilyEdit" . __OUTPUTID_SEPARATOR__ .
      "id" . __DATA_SEPARATOR__ . $FamilyID . __DATA_SEPARATOR__ .
      "name" . __DATA_SEPARATOR__ .$name . __DATA_SEPARATOR__ .
      "ref_name" . __DATA_SEPARATOR__ . $ref_name . __DATA_SEPARATOR__ .
      "title" . __DATA_SEPARATOR__ . $title . __DATA_SEPARATOR__ .
      "meta_desc" . __DATA_SEPARATOR__ . $meta_desc . __DATA_SEPARATOR__ .
      "text_content" . __DATA_SEPARATOR__ . $text_content . __DATA_SEPARATOR__ .
      "idParent" . __DATA_SEPARATOR__ . $FamilyParentID . __DATA_SEPARATOR__ . __OUTPUT_SEPARATOR__;
      ManagerLog($handle, $_SESSION['id'], $_SESSION['login'], $_SESSION['pass'], $_SESSION['ip'],  'Edition de la famille ' . $name . ' (ID : ' . $FamilyID . ', Nom Google : ' . $ref_name . ')');
		}
    break;
  
  case "editDesc" :
		if (!preg_match("/^[0-9]+$/", $FamilyID)) $es .= "L'identifiant de la famille est invalide<br/>\n";
		if (!$user->get_permissions()->has("m-prod--sm-categories","e")) {
      $es .= "Vous n'avez pas les droits adéquats pour réaliser cette opération.";
      break;
    }
		if ($es == "") {
      $handle->query("UPDATE families_fr SET meta_desc = '".$handle->escape($meta_desc)."' WHERE id = ".$FamilyID, __FILE__, __LINE__, false);
      $os .= "FamilyEdit" . __OUTPUTID_SEPARATOR__ .
      "id" . __DATA_SEPARATOR__ . $FamilyID . __DATA_SEPARATOR__ .
      "name" . __DATA_SEPARATOR__ .$name . __DATA_SEPARATOR__ .
      "ref_name" . __DATA_SEPARATOR__ . $ref_name . __DATA_SEPARATOR__ .
      "title" . __DATA_SEPARATOR__ . $title . __DATA_SEPARATOR__ .
      "meta_desc" . __DATA_SEPARATOR__ . $meta_desc . __DATA_SEPARATOR__ .
      "text_content" . __DATA_SEPARATOR__ . $text_content . __DATA_SEPARATOR__ .
      "idParent" . __DATA_SEPARATOR__ . $FamilyParentID . __DATA_SEPARATOR__ . __OUTPUT_SEPARATOR__;
      ManagerLog($handle, $_SESSION['id'], $_SESSION['login'], $_SESSION['pass'], $_SESSION['ip'],  'Edition de la famille ' . $name . ' (ID : ' . $FamilyID . ', Nom Google : ' . $ref_name . ')');
		}
    break;

  case "editContent" :
		if (!preg_match("/^[0-9]+$/", $FamilyID)) $es .= "L'identifiant de la famille est invalide<br/>\n";
		if (!$user->get_permissions()->has("m-prod--sm-categories","e")) {
      $es .= "Vous n'avez pas les droits adéquats pour réaliser cette opération.";
      break;
    }
		if ($es == "") {
      $handle->query("UPDATE families_fr SET text_content = '".$handle->escape($text_content)."' WHERE id = ".$FamilyID, __FILE__, __LINE__, false);
      $os .= "FamilyEdit" . __OUTPUTID_SEPARATOR__ .
      "id" . __DATA_SEPARATOR__ . $FamilyID . __DATA_SEPARATOR__ .
      "name" . __DATA_SEPARATOR__ .$name . __DATA_SEPARATOR__ .
      "ref_name" . __DATA_SEPARATOR__ . $ref_name . __DATA_SEPARATOR__ .
      "title" . __DATA_SEPARATOR__ . $title . __DATA_SEPARATOR__ .
      "meta_desc" . __DATA_SEPARATOR__ . $meta_desc . __DATA_SEPARATOR__ .
      "text_content" . __DATA_SEPARATOR__ . $text_content . __DATA_SEPARATOR__ .
      "idParent" . __DATA_SEPARATOR__ . $FamilyParentID . __DATA_SEPARATOR__ . __OUTPUT_SEPARATOR__;
      ManagerLog($handle, $_SESSION['id'], $_SESSION['login'], $_SESSION['pass'], $_SESSION['ip'],  'Edition de la famille ' . $name . ' (ID : ' . $FamilyID . ', Nom Google : ' . $ref_name . ')');
		}
    break;
  
  case 'delete' :
		if (!preg_match('/^[0-9]+$/', $FamilyID)) $es .= "L'identifiant de la famille est invalide<br />\n";
		if (!$user->get_permissions()->has("m-prod--sm-categories","d")) {
      $es .= "Vous n'avez pas les droits adéquats pour réaliser cette opération.";
      break;
    }
    if ($es == '')
		{
			$result = & $handle->query("select id from families where idParent = " . $FamilyID, __FILE__, __LINE__, false);
			if ($handle->numrows($result, __FILE__, __LINE__) == 0)
			{
				if ($handle->query("delete from families where id = " . $FamilyID, __FILE__, __LINE__, false)
				&& $handle->query("delete from families_fr where id = " . $FamilyID, __FILE__, __LINE__, false))
				{
					$os .= "FamilyDel" . __OUTPUTID_SEPARATOR__ . $FamilyID. __OUTPUT_SEPARATOR__;
					ManagerLog($handle, $_SESSION['id'], $_SESSION['login'], $_SESSION['pass'], $_SESSION['ip'],  'Suppression de la famille ' . $name . ' (ID : ' . $FamilyID . ', Nom Google : ' . $ref_name . ')');
				}
				else $es .= "Erreur fatale lors de la suppression de la famille"  . $name;
			}
			else $es .= "Supression non autorisée : cette famille est encore parente de plusieurs sous-familles";
		}
		break;
		
	default : $es .= "L'action demandée sur les familles est invalide";
}


if ($es != '') $es = "FamiliesError" . __ERRORID_SEPARATOR__ . $es . __ERROR_SEPARATOR__;

print $es . __MAIN_SEPARATOR__ . $os;

exit();
