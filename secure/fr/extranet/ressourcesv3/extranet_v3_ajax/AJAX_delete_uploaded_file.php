<?php
/*================================================================/

	Techni-Contact V3 - MD2I SAS
	http://www.techni-contact.com

	Auteur : OD pour Hook Network SARL - http://www.hook-network.com
	Date de création : 17 novembre 2011


	Fichier : /secure/fr/manager/ressources/files-upload/AJAX_delete_uploaded_file.php
	Description : supprime un fichier uploadé pour un idfichier donné, dans un contexte donné
 * 
 * i: pageOrigin -> page where the call comes from
 * i: fileId -> id fichier
 * i: context -> if explicitly given, replace pageOrigin as context in db
 *
 * o: confirmation message
 * o: error message

/=================================================================*/
session_name('extranet');
session_start();

if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

$o = array();
try {

  require(ICLASS."ExtranetUser.php");
  $db = DBHandle::get_instance();
  $user = new ExtranetUser($db);
  if (empty($_SESSION['extranet_user_id']))
    throw new Exception("Votre session a expiré, veuillez vous identifier à nouveau après avoir rafraîchi votre page.");

  // TODO check that the item actually exists in the DB and that the user has right on it
  if (!is_numeric($_POST['fileId']))
    throw new Exception("Identifiant de fichier incorect".$_POST['fileId']);
  
  $ctxData = $uploadContextData[$_POST['context']];
  if (!isset($ctxData))
    throw new Exception("Les droits à l'upload de fichiers ne sont pas configurés pour le contexte ".$_POST['context']);

  // permission test
  if (isset($ctxData['credential'])) // if credential are present, it's only for BO users
    throw new Exception("Vous n'avez pas les droits adéquats pour réaliser cette opération");

  $uploaded_filesTable = Doctrine_Core::getTable('UploadedFiles');
  $file = $uploaded_filesTable->findByIdAndContext($_POST['fileId'], $upCtxPre.$_POST['context']);
  if ($file->count() != 1)
    throw new Exception("Fichier non trouvé");
  
  $fileAlias = (!empty($file[0]->alias_filename) ? $file[0]->alias_filename : $file[0]->filename).".".$file[0]->extension;
  if (!@unlink(ADMIN_UPLOAD_DIR.$ctxData['dir'].$file[0]->filename.'.'.$file[0]->extension))
    throw new Exception("Erreur lors de la suppression du fichier ".$fileAlias);
  
  $file[0]->delete();
  $o['response'] = "Suppression du fichier ".$fileAlias." effectuée avec succès";

} catch (Exception $e) {
  header($_SERVER['SERVER_PROTOCOL'].' 500 Internal Server Error', true, 500);
  $o['error'] = "Erreur fatale: ".$e->getMessage();
}

print json_encode($o);
