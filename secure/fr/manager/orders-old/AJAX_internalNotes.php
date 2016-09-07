<?php
/*================================================================/

 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com

 Auteur : OD�pour Hook Network SARL - http://www.hook-network.com
 Date de cr�ation : 31 mars 2011

 Fichier : /secure/manager/orders/AJAX_internal-notes.php
 Description : retour d'appel ajax de gestion de note interne dans le context des ordres fournisseurs

/=================================================================*/
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$user = new BOUser();

header("Content-Type: text/plain; charset=utf-8");

if (!$user->login()) {
  $o["error"] = "Votre session a expir�e, veuillez vous identifier � nouveau apr�s avoir rafraichi votre page";
  print json_encode($o);
  exit();
}

require_once(ADMIN."logs.php");

if (!$user->login($login, $pass) || !$user->active) {
	$o["error"] = "Votre session a expirée, veuillez vous identifier à nouveau après avoir rafraichi votre page";
	print json_encode($o);
	exit();
}

$o = array();

$context = 'ordre_fournisseur';


if($_SERVER['REQUEST_METHOD'] == 'POST'){
  $idUser = !empty($_POST['idUser']) ? $_POST['idUser'] : '';
  $contenu = !empty($_POST['contenu']) ? $_POST['contenu'] : '';
  $action = !empty($_POST['action']) ? $_POST['action'] : '';
  $ordre = !empty($_POST['ordre']) ? $_POST['ordre'] : '';
}
elseif($_SERVER['REQUEST_METHOD'] == 'GET'){
  $idUser = !empty($_GET['idUser']) ? $_GET['idUser'] : '';
  $contenu = !empty($_GET['contenu']) ? $_GET['contenu'] : '';
  $action = !empty($_GET['action']) ? $_GET['action'] : '';
  $ordre = !empty($_GET['ordre']) ? $_GET['ordre'] : '';
}
else {
  $o["error"] = "Erreur grave";
	print json_encode($o);
	exit();
}
$id_reference = $idUser.'-'.$ordre;

if (empty($idUser) || empty($action) || empty($ordre)) {
	$o["error"] = "Requète incorrecte";
	print json_encode($o);
	exit();
}
if (!$user->login($login, $pass) || !$user->active) {
	$o["error"] = "Votre session a expirée, veuillez vous identifier à nouveau après avoir rafraichi votre page";
	print json_encode($o);
	exit();
}
$supplier = new AdvertiserOld($idUser);
if($action == 'get'){

  $notesInternes = new InternalNotesOld($context);
  $notes = $notesInternes->getAllNotesByIdRef($id_reference);

  if(!empty($notes))
    foreach ( $notes as $note => $contenu){
      $notes[$note]['date'] = date('d/m/Y � H:i:s', $contenu['timestamp']);
      $sender = new BOUser($contenu['operator']);
      $notes[$note]['sender_name'] = $sender->name;
      $notes[$note]['content'] = to_entities($notes[$note]['content']);
    }
  
  $o['notes'] = !empty($notes) ? $notes : 'vide';

}elseif($action == 'add'){
  if (empty($contenu)) {
	$o["error"] = "Note interne sans contenu";
	print json_encode($o);
	exit();
  }

  $noteInterne = new InternalNotesOld($context);
  if($noteInterne->addNote($user->id, $contenu, $id_reference))
    $o["result"] = 'ok';

} else {
  $o["error"] = "Action impossible";
	print json_encode($o);
	exit();
}

mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $o);
print json_encode($o);
?>