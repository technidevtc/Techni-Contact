<?php
/*================================================================/

 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com

 Auteur : OD�pour Hook Network SARL - http://www.hook-network.com
 Date de cr�ation : 21/10/2011 OD

 Fichier : /secure/fr/manager/ressources/ajax/AJAX_surqualificationSecteursActivites.php
 Description : requete ajax de recherche de secteurs d'activit� d'apr�s mot cl�s pr�sents en nom soci�t�
 *
 * i : raison sociale
 * o : secteur d'activit� correspondant (unique)
 * o : erreur et type d'erreur

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
header("Content-Type: text/plain; charset=utf-8");

function throwError($message){
  $o["error"] =  $message;
  mb_convert_variables("UTF-8","ASCII,UTF-8,ISO-8859-1,CP1252",$o);
  print json_encode($o);
  exit();
}

$o = array("data" => array(),"error" => "");

if(!isset($_GET['params']) || empty ($_GET['params'])){
  throwError("Structure de requ�te invalide.");
}elseif(isset($_GET) && !empty ($_GET)){
  $params = $_GET['params'][0];
}
if(!isset($params['action']) || $params['action'] != 'processSector')
  throwError("Structure de requ�te invalide.");

if(!isset($params['raison_sociale']) || empty ($params['raison_sociale'])){
  throwError("Donn�e fournies vides.");
}else{

  $terms = preg_replace('/ de /', ' ', $params['raison_sociale']);
  $terms = explode(' ', $terms);
  $ActivitySector = Doctrine_Core::getTable('ActivitySectorSurqualification');
  $ActivitySector->batchUpdateIndex();
  $array_results = array();
  foreach($terms as $term){
    $term = Utils::toDashAz09(utf8_decode($term));

    if($result = $ActivitySector->search($term)){
      $q = Doctrine_Query::create()
        ->from('ActivitySector as')
        ->leftJoin('as.Surqualifications ass')
        ->where('ass.id = ?', $result[0]['id']);

      $array_results[$result[0]['id']] = $result[0]['id'];
      $sector = $q->fetchArray();

      if(count($results) > 1)
      throwError ('R�ponses multiples, v�rifier les param�tres de surqualification des secteurs d\'activit�s');
      $results[] = $result;
    }
  }

  if(count($array_results) > 1)
    throwError ('R�ponses multiples, impossible de d�terminer le secteur');
  else{
    $o["retour"] = $results[0];
    $o["data"] = $sector;
  }

  mb_convert_variables("UTF-8","ASCII,UTF-8,ISO-8859-1,CP1252",$o);
  print json_encode($o);
}

?>
