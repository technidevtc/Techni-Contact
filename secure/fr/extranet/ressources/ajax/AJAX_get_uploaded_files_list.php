<?php
/*================================================================/

	Techni-Contact V3 - MD2I SAS
	http://www.techni-contact.com

	Auteur : OD pour Hook Network SARL - http://www.hook-network.com
	Date de création : 17 novembre 2011


	Fichier : /secure/fr/manager/ressources/files-upload/AJAX_get_uploaded_files_list.php
	Description : récupération ajax de la liste des fichier uploadés pour un item donné, dans un contexte donné
 * 
 * i: pageOrigin -> page where the call comes from
 * i: itemId -> reference id
 * i: context -> if explicitly given, replace pageOrigin as context in db
 *
 * o: list of uploaded files names

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$o = array();
try {

  require(ICLASS."ExtranetUser.php");
  $db = DBHandle::get_instance();
  $user = new ExtranetUser($db);
  if (!$user->login())
    throw new Exception("Votre session a expiré, veuillez vous identifier à nouveau après avoir rafraîchi votre page.");

  // TODO check that the item actually exists in the DB and that the user has right on it
  if (!is_numeric($_POST['itemId']))
    throw new Exception("Identifiant de fichier incorrect".$_POST['itemId']);
  
  $ctxData = $uploadContextData[$_POST['context']];
  if (!isset($ctxData))
    throw new Exception("Les droits à l'upload de fichiers ne sont pas configurés pour le contexte ".$_POST['context']);

  // permission test
  if (isset($ctxData['credential'])) // if credential are present, it's only for BO users
    throw new Exception("Vous n'avez pas les droits adéquats pour réaliser cette opération");
  
  $uploaded_filesTable = Doctrine_Core::getTable('UploadedFiles');
  $o['response'] = array(
    'list' => $uploaded_filesTable->findByItem_idAndContext($_POST['itemId'], $upCtxPre.$_POST['context'])->toArray(),
    'directory' => BO_UPLOAD_DIR.$ctxData['dir']
  );

} catch (Exception $e) {
  header($_SERVER['SERVER_PROTOCOL'].' 500 Internal Server Error', true, 500);
  $o['error'] = "Erreur fatale: ".$e->getMessage();
}

print json_encode($o);
