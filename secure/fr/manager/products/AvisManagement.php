<?php

/*================================================================/

 Techni-Contact V4 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 29/07/2011

 Fichier : /secure/manager/products/AvisManagement.php
 Description : gestion ajax des avis et note client

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
		//return action, id, datetime, note, comment, show
		case "get" :
			$os .= "get" . __OUTPUT_SEPARATOR__;
			if (isset($_GET['id']))
			{
				$id = (int)$_GET['id'];
                                $note = new ProductNotation($id);
				if ($note->existsInDB() && count($note) == 1)
				{
					$os .= "id" . __OUTPUTID_SEPARATOR__ . $note->id . __OUTPUT_SEPARATOR__;
					$os .= "datetime" . __OUTPUTID_SEPARATOR__ . date("d/m/Y H:i", $note->timestamp) . __OUTPUT_SEPARATOR__;
					$os .= "note" . __OUTPUTID_SEPARATOR__ . $note->note . __OUTPUT_SEPARATOR__;
                                        $os .= "comment" . __OUTPUTID_SEPARATOR__ . $note->comment . __OUTPUT_SEPARATOR__;
					$os .= "show" . __OUTPUTID_SEPARATOR__ . $note->inactive . __OUTPUT_SEPARATOR__;
				}
				else $es .= "Erreur fatale SQL lors de l'obtention des informations du commentaire " . $id . " : le commentaire n'existe peut-être pas" . __ERROR_SEPARATOR__;
			}
			else $es .= "ID du commentaire non spécifié" . __ERROR_SEPARATOR__;
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
			
			if (isset($_GET["comment"])) $comment = rawurldecode($_GET["comment"]);
			else $es .= "Pas de texte de commentaire spécifié" . __ERROR_SEPARATOR__;
			
			$show = isset($_GET["show"]) ? ((int)(bool)$_GET["show"]) : 1;
			
			if (empty($es))
			{
                          $notation = new ProductNotation($id);
				if ($notation->existsInDB() && (count($notation) == 1))
				{
                                  $data = array(
                                      'comment' => $comment,
                                      'inactive' => ($show == 1 ? 0 : 1)
                                  );
                                  $notation->setData($data);
                                        if ($notation->save())	
					{
						$os .= "id" . __OUTPUTID_SEPARATOR__ . $id . __OUTPUT_SEPARATOR__;
						$os .= "datetime" . __OUTPUTID_SEPARATOR__ . date("d/m/Y H:i", $timestamp) . __OUTPUT_SEPARATOR__;
						$os .= "comment" . __OUTPUTID_SEPARATOR__ . $comment . __OUTPUT_SEPARATOR__;
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
				if (ProductNotation::delete($id))
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
				$notation = new ProductNotation($id);
				if ($notation->existsInDB() && (count($notation) == 1))
				{
					$show = $notation->inactive;
					$data = array(
                                      'inactive' => ($show == 1 ? 0 : 1)
                                  );
                                  $notation->setData($data);
                                        if ($notation->save())
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
