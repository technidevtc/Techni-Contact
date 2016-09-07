<?php
/*================================================================/

 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com

 Auteur : OD pour Hook Network SARL - http://www.hook-network.com
 Date de création : 26 octobre 2011

 Fichier : /manager/config/AJAX_activity-sector.php
 Description : traitement ajax de gestion des secteurs d'activité

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
require(ADMIN.'generator.php');

$user = new BOUser();

//header("Content-Type: text/plain; charset=utf-8");

function throwError($message){
  $o["error"] =  $message;
  mb_convert_variables("UTF-8","ASCII,UTF-8,ISO-8859-1,CP1252",$o);
  print json_encode($o);
  exit();
}

if (!$user->login()) 
  throwError("Votre session a expiré, veuillez vous identifier à nouveau après avoir rafraîchi votre page");

$handle = DBHandle::get_instance();
$db = DBHandle::get_instance();
$o = array("data" => array(),"error" => "");

if (!$user->get_permissions()->has("m-admin--sm-activity-sector","re")) {
  throwError("Vous n'avez pas les droits adéquats pour réaliser cette opération");
}

if((!isset($_POST['params']) || empty ($_POST['params'])) && (!isset($_GET['params']) || empty ($_GET['params']))){
  throwError("VStructure de requête invalide.");
}  elseif(isset($_POST) && !empty ($_POST)){
  $params = $_POST['params'][0];
}elseif(isset($_GET) && !empty ($_GET)){
  $params = $_GET['params'][0];
}

if(!isset($params['action']) || empty ($params['action'])){
  throwError("Structure de requête invalide.");

}

if($params['action'] == 'save'){

  if(!isset($params['sector']) || !is_numeric($params['sector'])){
    throwError("Structure de requête invalide.");
  }

  if(!empty ($params['qualifList']))
    foreach($params['qualifList'] as $qualif){
      $qualif = $qualif[0];
      $surqualif = Doctrine_Core::getTable('ActivitySectorSurqualification');
      if(!empty ($qualif['qualification']))
        if(is_numeric($qualif['id']) && $item = $surqualif->find($qualif['id'])){
          $item->qualification = $qualif['qualification'];
          $item->naf = $qualif['naf'];
          $item->save();
        }  else {
          $genId = doctrine_generateID(1, 0x7fffffff,ActivitySectorSurqualification, 'id');
          $surqualif = new ActivitySectorSurqualification;
          $surqualif['id'] = $genId;
          $surqualif['qualification'] = $qualif['qualification'];
          $surqualif['naf'] = $qualif['naf'];
          $surqualif['activity_sector_id'] = (int) $params['sector'];
          $surqualif->save();
        }
    }
}elseif($params['action'] == 'saveKeywords'){
  if(!isset($params['qualifId']) || !is_numeric($params['qualifId']))
    throwError("Structure de requête invalide.");

  $surqualif = Doctrine_Core::getTable('ActivitySectorSurqualification');
  $item = $surqualif->find($params['qualifId']);
  $item->keywords = $params['keywords'];
  $item->save();

}elseif($params['action'] == 'delete'){

  if(!isset($params['qualifId']) || !is_numeric($params['qualifId']))
    throwError("Structure de requête invalide.");

  $surqualif = Doctrine_Core::getTable('ActivitySectorSurqualification');
  $surqualif->find($params['qualifId'])->delete();

}elseif($params['action'] == 'getSurqualification'){
  if(!isset($params['sector']) || !is_numeric($params['sector']))
    throwError("Structure de requête invalide.");

  $q = Doctrine_Query::create()
      ->from('ActivitySectorSurqualification')
      ->where('activity_sector_id = ?', $params['sector']);
  $activitySectors = $q->fetchArray();
  $o["retour"] = $activitySectors;
}elseif($params['action'] == 'getKeywords'){
  if(!isset($params['qualifId']) || !is_numeric($params['qualifId']))
    throwError("Structure de requête invalide.");


  $surqualif = Doctrine_Core::getTable('ActivitySectorSurqualification');
  $s = $surqualif->find($params['qualifId']);
  $o["keywords"] = $s->keywords;
}

//mb_convert_variables("UTF-8","ASCII,UTF-8,ISO-8859-1,CP1252",$o);
print json_encode($o);

?>
