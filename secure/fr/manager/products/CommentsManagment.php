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

//header("Content-Type: text/plain; charset=iso-8859-1");
header("Content-Type: text/plain; charset=utf-8");

if(!$user->login())
{
	print "Votre session a expirée, veuillez réactualiser la page pour retourner à la page de login" . __MAIN_SEPARATOR__;
	exit();
}

function rawurldecodeEuro ($str) { return str_replace("%u20AC", "€", rawurldecode($str)); }

$es = $os = '';

if (isset($_GET['action']))
{
	switch ($_GET['action'])
	{
		//ProductsManagement.php?action=get&id=13513357
		//return action, id, datetime, text, show
		case "get" :
			$os .= "get" . __OUTPUT_SEPARATOR__;
			if (isset($_GET['id']))
			{
				$id = (int)$_GET['id'];
				if (($result = & $handle->query("select id, timestamp, text, `show` from products_comments where id = " . $id, __FILE__, __LINE__, false)) && ($handle->numrows($result, __FILE__, __LINE__) == 1))
				{
					list($id, $timestamp, $text, $show) = $handle->fetch($result);
					$os .= "id" . __OUTPUTID_SEPARATOR__ . $id . __OUTPUT_SEPARATOR__;
					$os .= "datetime" . __OUTPUTID_SEPARATOR__ . date("d/m/Y H:i", $timestamp) . __OUTPUT_SEPARATOR__;
					$os .= "text" . __OUTPUTID_SEPARATOR__ . $text . __OUTPUT_SEPARATOR__;
					$os .= "show" . __OUTPUTID_SEPARATOR__ . $show . __OUTPUT_SEPARATOR__;
				}
				else $es .= "Erreur fatale SQL lors de l'obtention des informations du commentaire " . $id . " : le commentaire n'existe peut-être pas" . __ERROR_SEPARATOR__;
			}
			else $es .= "ID du commentaire non spécifié" . __ERROR_SEPARATOR__;
			break;
			
		//ProductsManagement.php?action=add&productID=51658&datetime=11/01/2007 11:35&text=Some_escaped_text&show=1
		//return action, id, productID, datetime, text, show
		case "add" :
			$os .= "add" . __OUTPUT_SEPARATOR__;
			
			if (isset($_GET['productID'])) $productID = (int)$_GET['productID'];
			else $es .= "ID du produit auquel rajouter le commentaire non spécifié" . __ERROR_SEPARATOR__;
			
			if (isset($_GET["datetime"]))
			{
				if (preg_match("/\s*([0-9]{1,2})[-.\/\\\]([0-9]{1,2})[-.\/\\\]([0-9]{4})\s*([0-9]{1,2})[\:-hH]([0-9]{1,2})/", rawurldecode($_GET["datetime"]), $datetime))
				{
					if ($datetime[1] > 0 && $datetime[1] <= 31
					&& $datetime[2] > 0 && $datetime[2] <=12
					&& $datetime[3] > 2000 && $datetime[3] <= ((int)date('Y')+1)
					&& $datetime[4] >= 0 && $datetime[4] <= 23
					&& $datetime[5] >= 0 && $datetime[5] <= 59)
					{
						$timestamp = mktime($datetime[4],$datetime[5],0,$datetime[2],$datetime[1],$datetime[3]);
					}
					else $es .= "La date du commentaire n'est pas valide" . __ERROR_SEPARATOR__;
				}
				else $es .= "La date du commentaire n'est pas d'un format valide" . __ERROR_SEPARATOR__;
			}
			else $es .= "Pas de date de commentaire spécifiée" . __ERROR_SEPARATOR__;
			
			$text = (isset($_GET["text"])) ? rawurldecode(trim($_GET["text"])) : "";
			if (empty($text)) $es .= "Pas de texte de commentaire spécifié" . __ERROR_SEPARATOR__;
			
			$show = isset($_GET["show"]) ? ((int)(bool)$_GET["show"]) : 1;
			
			if (empty($es))
			{
				if (($result = & $handle->query("select p.id from products p, products_fr pfr where p.id = pfr.id and p.id = " . $productID, __FILE__, __LINE__, false))
					&& $handle->numrows($result, __FILE__, __LINE__) == 1)
				{
					do
					{
						$id = mt_rand(1, 999999999);
						$result = & $handle->query("select id from products_comments where id = " . $id, __FILE__, __LINE__);
					}
					while ($handle->numrows($result, __FILE__, __LINE__) >= 1);
					
					$contactID = 0; // 0 = TC
					$query = "insert into products_comments (";		$query2 = "values (";
					$query .= "id, ";								$query2 .= $id . ", ";
					$query .= "productID, ";						$query2 .= $productID . ", ";
					$query .= "contactID, ";						$query2 .= $contactID . ", ";
					$query .= "timestamp, ";						$query2 .= $timestamp . ", ";
					$query .= "text, ";								$query2 .= "'" . $handle->escape($text) . "', ";
					$query .= "`show`) ";							$query2 .= $show . ") ";
					$query .= $query2;
					
					if (($handle->query($query, __FILE__, __LINE__, false)) && ($handle->affected(__FILE__, __LINE__) == 1))
					{
						$os .= "id" . __OUTPUTID_SEPARATOR__ . $id . __OUTPUT_SEPARATOR__;
						$os .= "productID" . __OUTPUTID_SEPARATOR__ . $productID . __OUTPUT_SEPARATOR__;
						$os .= "contactID" . __OUTPUTID_SEPARATOR__ . $contactID . __OUTPUT_SEPARATOR__;
						$os .= "datetime" . __OUTPUTID_SEPARATOR__ . date("d/m/Y H:i", $timestamp) . __OUTPUT_SEPARATOR__;
						$os .= "text" . __OUTPUTID_SEPARATOR__ . $text . __OUTPUT_SEPARATOR__;
						$os .= "show" . __OUTPUTID_SEPARATOR__ . $show . __OUTPUT_SEPARATOR__;
					}
					else $es .= "Erreur fatal SQL lors de l'insertion du nouveau commentaire" . __ERROR_SEPARATOR__;
				}
				else $es .= "Erreur fatal : le produit ayant pour ID " . $productID . " n'existe pas dans la base de donnée" . __ERROR_SEPARATOR__;
			}
			break;
			
		//ProductsManagement.php?action=alter&id=13513357&datetime=11/01/2007 11:35&text=Some_escaped_text&show=1
		//return action, id, datetime, text, show
		case "alter" :
			$os .= "alter" . __OUTPUT_SEPARATOR__;
			
			if (isset($_GET['id'])) $id = (int)$_GET['id'];
			else $es .= "ID du commentaire à modifier non spécifié" . __ERROR_SEPARATOR__;
			
			if (isset($_GET["datetime"]))
			{
				if (preg_match("/\s*([0-9]{1,2})[-.\/\\\]([0-9]{1,2})[-.\/\\\]([0-9]{4})\s*([0-9]{1,2})[\:-hH]([0-9]{1,2})/", rawurldecode($_GET["datetime"]), $datetime))
				{
					if ($datetime[1] > 0 && $datetime[1] <= 31
					&& $datetime[2] > 0 && $datetime[2] <=12
					&& $datetime[3] > 2000 && $datetime[3] <= ((int)date('Y')+1)
					&& $datetime[4] >= 0 && $datetime[4] <= 23
					&& $datetime[5] >= 0 && $datetime[5] <= 59)
					{
						$timestamp = mktime($datetime[4],$datetime[5],0,$datetime[2],$datetime[1],$datetime[3]);
					}
					else $es .= "La date du commentaire n'est pas valide" . __ERROR_SEPARATOR__;
				}
				else $es .= "La date du commentaire n'est pas d'un format valide" . __ERROR_SEPARATOR__;
			}
			else $es .= "Pas de date de commentaire spécifiée" . __ERROR_SEPARATOR__;
			
			if (isset($_GET["text"])) $text = rawurldecode($_GET["text"]);
			else $es .= "Pas de texte de commentaire spécifié" . __ERROR_SEPARATOR__;
			
			$show = isset($_GET["show"]) ? ((int)(bool)$_GET["show"]) : 1;
			
			if (empty($es))
			{
				if (($result = & $handle->query("select id from products_comments where id = " . $id, __FILE__, __LINE__, false)) && ($handle->numrows($result) == 1))
				{
					if ($handle->query("update products_comments set " .
						"timestamp = " . $timestamp . ", " .
						"text = '" . $handle->escape($text) . "', " .
						"`show` = " . $show . " " .
						"where id = " . $id, __FILE__, __LINE__, false))
						
					{
						$os .= "id" . __OUTPUTID_SEPARATOR__ . $id . __OUTPUT_SEPARATOR__;
						$os .= "datetime" . __OUTPUTID_SEPARATOR__ . date("d/m/Y H:i", $timestamp) . __OUTPUT_SEPARATOR__;
						$os .= "text" . __OUTPUTID_SEPARATOR__ . $text . __OUTPUT_SEPARATOR__;
						$os .= "show" . __OUTPUTID_SEPARATOR__ . $show . __OUTPUT_SEPARATOR__;
					}
					else $es .= "Erreur fatal SQL lors de la mise à jour des informations du commentaire " . $id . __ERROR_SEPARATOR__;
				}
				else $es .= "Erreur fatale SQL lors de l'obtention des informations du commentaire " . $id . " : le commentaire n'existe peut-être pas" . __ERROR_SEPARATOR__;
			}
			break;
			
		//ProductsManagement.php?action=delete&id=13513357
		//return action, id
		case "delete" :
			$os .= "delete" . __OUTPUT_SEPARATOR__;
			if (isset($_GET['id']))
			{
				$id = (int)$_GET['id'];
				if (($handle->query("delete from products_comments where id = " . $id, __FILE__, __LINE__, false)) && ($handle->affected(__FILE__, __LINE__) == 1))
					$os .= "id" . __OUTPUTID_SEPARATOR__ . $id . __OUTPUT_SEPARATOR__;
				else $es .= "Erreur fatale SQL lors de la suppression du commentaire " . $id . " : le commentaire n'existe peut-être pas" . __ERROR_SEPARATOR__;
			}
			else $es .= "ID du commentaire à supprimer non spécifié" . __ERROR_SEPARATOR__;
			break;
			
		//ProductsManagement.php?action=toggleshow&id=13513357
		//return action, id, show
		case "toggleshow" :
			$os .= "toggleshow" . __OUTPUT_SEPARATOR__;
			if (isset($_GET['id']))
			{
				$id = (int)$_GET['id'];
				if (($result = & $handle->query("select `show` from products_comments where id = " . $id, __FILE__, __LINE__, false)) && ($handle->numrows($result, __FILE__, __LINE__) == 1))
				{
					list($show) = $handle->fetch($result);
					//print "gettype(show)=" . gettype($show) . "  ";
					$show = (int)!(bool)$show;
					if (($handle->query("update products_comments set `show` = " . $show . " where id = " . $id, __FILE__, __LINE__, false)) && ($handle->affected(__FILE__, __LINE__) == 1))
					{
						$os .= "id" . __OUTPUTID_SEPARATOR__ . $id . __OUTPUT_SEPARATOR__;
						$os .= "show" . __OUTPUTID_SEPARATOR__ . $show . __OUTPUT_SEPARATOR__;
					}
					else $es .= "Erreur fatal SQL lors de la mise à jour de l'affichage du commentaire " . $id . __ERROR_SEPARATOR__;
					
				}
				else $es .= "Erreur fatale SQL lors de l'obtention des informations d'affichage du commentaire " . $id . " : le commentaire n'existe peut-être pas" . __ERROR_SEPARATOR__;
			}
			else $es .= "ID du commentaire à supprimer non spécifié" . __ERROR_SEPARATOR__;
			break;
			
		default : break;
	}
}
else $es .= "Aucune action n'a été spécifiée";

print $es . __MAIN_SEPARATOR__ . $os;

exit();

?>
