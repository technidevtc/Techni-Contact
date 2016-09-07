<?php
/*================================================================/

  Techni-Contact V3 - MD2I SAS
  http://www.techni-contact.com

  Auteur : OD pour Hook Network SARL - http://www.hook-network.com
  Date de création : 17 novembre 2011


  Fichier : /secure/fr/manager/ressources/files-upload/AJAX_files_upload.php
  Description : gestion php de l'upload ajax de fichier

 * i: uploaded file
 * i: fileElementId -> file input tag name
 * i: pageOrigin -> page where the call comes from
 * i: itemId -> reference id
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

require(ADMIN.'generator.php');

$o = array();
$fileElementName = $_POST['fileElementId']; // file input tag name
try {
  
  if (!empty($_FILES[$fileElementName]['error'])) {
    switch($_FILES[$fileElementName]['error']) {
      case '1': $error = 'The uploaded file exceeds the upload_max_filesize directive in php.ini'; break;
      case '2': $error = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form'; break;
      case '3': $error = 'The uploaded file was only partially uploaded'; break;
      case '4': $error = 'No file was uploaded.'; break;
      case '6': $error = 'Missing a temporary folder'; break;
      case '7': $error = 'Failed to write file to disk'; break;
      case '8': $error = 'File upload stopped by extension'; break;
      case '999':
      default:  $error = 'No error code avaiable';
    }
    throw new Exception($error);
  }
  
  if (empty($_FILES[$fileElementName]['tmp_name']) || $_FILES[$fileElementName]['tmp_name'] == 'none' || !is_uploaded_file($_FILES[$fileElementName]['tmp_name']))
    throw new Exception("Aucun fichier n'a été uploadé");
  
  require(ICLASS."ExtranetUser.php");
  $db = DBHandle::get_instance();
  $user = new ExtranetUser($db);
  if (empty($_SESSION['extranet_user_id']))
    throw new Exception("Votre session a expiré, veuillez vous identifier à nouveau après avoir rafraîchi votre page.");
  
  // TODO check that the item actually exists in the DB and that the user has right on it
  if (!is_numeric($_POST['itemId']))
    throw new Exception("Identifiant incorrect");
  
  $ctxData = $uploadContextData[$_POST['context']];
  if (!isset($ctxData))
    throw new Exception("Les droits à l'upload de fichiers ne sont pas configurés pour le contexte ".$_POST['context']);
  
  // permission test
  if (isset($ctxData['credential'])) // if credential are present, it's only for BO users
    throw new Exception("Vous n'avez pas les droits adéquats pour réaliser cette opération");
  
  $dir = ADMIN_UPLOAD_DIR.$ctxData['dir'];
  if (!is_dir($dir))
    throw new Exception("Répertoire de destination inexistant");
  
  $fileMimetype = $boValidMimeTypes[$_FILES[$fileElementName]['type']];
  if (!isset($fileMimetype))
    throw new Exception("Type de fichier incorrect : ".$_FILES[$fileElementName]['type']);
  
  $extension = '.'.$fileMimetype;
  $prefix = $ctxData['file_prefix'];
  $idxFile = 1;
  while (is_file($dir.$prefix.$_POST['itemId'].'-'.$idxFile.$extension))
    $idxFile++;
  
  $nameFile = $prefix.$_POST['itemId'].'-'.$idxFile;
  $labelName = !empty($_POST['aliasFileName']) ? Utils::toDashAz09($_POST['aliasFileName']).$extension : $nameFile.$extension;
  
  // file not correctly uploaded/moved
  if (!@move_uploaded_file($_FILES[$fileElementName]['tmp_name'], $dir.$nameFile.$extension))
    throw new Exception("Le fichier uploadé n'a pu être copié correctement");
  
  $o['response'] = "Le fichier ".$labelName." a été correctement uploadé";
  
  // database record
  $uploaded_file = new UploadedFiles();
  $uploaded_file->id = doctrine_generateID(1, 0x7fffffff,UploadedFiles, 'id');
  $uploaded_file->timestamp = time();
  $uploaded_file->context = $upCtxPre.$_POST['context'];
  $uploaded_file->item_id = $_POST['itemId'];
  $uploaded_file->filename = $nameFile;
  $uploaded_file->alias_filename = Utils::toDashAz09($_POST['aliasFileName']);
  $uploaded_file->extension = $fileMimetype;
  $uploaded_file->save();

} catch (Exception $e) {
  header($_SERVER['SERVER_PROTOCOL'].' 500 Internal Server Error', true, 500);
  $o['error'] = "Erreur fatale: ".$e->getMessage();
}

print json_encode($o);
