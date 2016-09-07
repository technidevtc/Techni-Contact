<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$db = DBHandle::get_instance();
$session = new UserSession($db);
$user = new CustomerUser($db, $session->userID);

header("Content-Type: text/plain; charset=UTF-8");

$actionList = array(
  'create',
  'update',
  'remove'
);

try {
  if (!$session->logged)
    throw new Exception("Session expirée, veuillez réactualiser la page !");
  $o = array();
  try {
    $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING);
    if (!in_array($action, $actionList))
      throw new Exception("Type d'action inconnu");
    
    // always load the client with all its addresses, we will use it
    $client = Doctrine_Query::create()
      ->select('c.*, ca.*')
      ->from('Clients c')
      ->innerJoin('c.addresses ca')
      ->where('c.id = ?', $user->id)
      ->orderBy('ca.type_adresse ASC, ca.num ASC')
      ->fetchOne();
    
    // init the address by type array
    $addressesByType = ClientsAdresses::orderByType($client->addresses);
    
    // first, do the basic checks when editing/removing an address
    if ($action == 'update' || $action == 'remove') {
      $id = filter_input(INPUT_POST,'id', FILTER_VALIDATE_INT);
      if (empty($id))
        throw new Exception("Identifiant adresse non spécifié");
      
      // find the current address, making sure it belongs to the client
      foreach ($client->addresses as $ci => $ca) {
        if ($ca->id == $id) {
          $ccai = $ci;
          $cca = $ca;
          break;
        }
      }
      if (!isset($cca))
        throw new Exception("L'adresse spécifiée n'existe pas");
    }
    
    switch ($action) {
      case 'create':
        // check that the type is valid
        $type = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_NUMBER_INT);
        if (!isset(ClientsAdresses::$typeList[$type]))
          throw new Exception("Le type d'adresse spécifiée n'existe pas");
        
        // now check that we don't already have the CLIENT_MAX_ADDRESS_BY_TYPE for this type
        if ($addressesByType[$type]['length'] >= CLIENT_MAX_ADDRESS_BY_TYPE)
          throw new Exception("Vous ne pouvez pas créer plus de ".CLIENT_MAX_ADDRESS_BY_TYPE." adresses de ".ClientsAdresses::getTypeText($type));
        
        $data = filter_input(INPUT_POST, 'data', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_REQUIRE_ARRAY); // FILTER_FLAG_NO_ENCODE_QUOTES preserves ' against #39;
        if (!$data)
          throw new Exception("Données invalides");
        
        $cca = new ClientsAdresses();
        $cca->fromArray($data);
        $cca->type_adresse = $type;
        if (!$cca->isValid(false, false)) // no deep and no hook
          throw new FormException($cca->getErrorStack());
        
        $client->addresses[] = $cca;
        $addressesByType[$type]['list'][$cca->id] = $cca;
        $addressesByType[$type]['length']++;
        
        // it's a new main address, or simply the first one of its type -> simply increment every other address num by 1
        if ($data['set_as_main'] == 1 || $addressesByType[$type]['length'] == 1) {
          foreach ($addressesByType[$type]['list'] as $ca)
            $ca->num++;
          $cca->num = 0; // the new address was also incremented, make sure it's at 0
          $client->setNewDefaultAddress($type);
        } else {
          $cca->num = $addressesByType[$type]['length']-1;
        }
        
        $o['response']['text'] = "Création de l'adresse de ".ClientsAdresses::getTypeText($type)." «".$cca->nom_adresse."» effectuée avec succès !";
        break;
        
      case 'update':
        // only throw an error if the type was actually specified and is not valid
        if (isset($_POST['type'])) {
          $type = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_NUMBER_INT);
          if (!isset(ClientsAdresses::$typeList[$type]))
            throw new Exception("Le type d'adresse spécifiée n'existe pas");
          $cca->type_adresse = $type;
        }

        $data = filter_input(INPUT_POST, 'data', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_REQUIRE_ARRAY);
        if (!$data)
          throw new Exception("Données invalides");

        // it is now the new main address, increment all the previous one by 1 and make it the num 0
        if ($data['set_as_main'] == 1 && $cca->num != 0) {
          foreach ($addressesByType[$type]['list'] as $ca)
            if ($ca->num < $cca->num)
              $ca->num++;
          $cca->num = 0;
          $client->setNewDefaultAddress($type);
        }
        // no need to manage the case when set_as_main = 0, because :
        // - if it's the last address, we don't change anything
        // - if there is other addresses, we don't know which one to make the new main address
        
        $cca->fromArray($data);
        
        if (!$cca->isValid(false, false)) // no deep and no hooks
          throw new FormException($cca->getErrorStack());
        
        $o['response']['text'] = "Modification de l'adresse de ".ClientsAdresses::getTypeText($type)." «".$cca->nom_adresse."» effectuée avec succès !";
        //$o['response']['data'] = $data;
        //$o['response']['client_id'] = $user->id;
        break;
        
      case 'remove':
        // it's the last delivery one, do not delete
        if ($cca->type_adresse == ClientsAdresses::TYPE_DELIVERY && $addressesByType[$cca->type_adresse]['length'] <= 1)
          throw new Exception("Impossible de supprimer votre seule adresse de livraison");
        
        $client->addresses->remove($ccai); // does not directly delete the object, so $cca is style ok at this point
        unset($addressesByType[$cca->type_adresse]['list'][$cca->id]);
        $addressesByType[$cca->type_adresse]['length']--;
        
        if ($addressesByType[$cca->type_adresse]['length'] >= 1) { // there is still at least 1 address of the same type
          // decrement the num of the same type addresses
          foreach ($addressesByType[$cca->type_adresse]['list'] as $ca)
            if ($ca->num > $cca->num)
              $ca->num--;
          // it was the default one, update the client table
          if ($cca->num == 0)
            $client->setNewDefaultAddress($cca->type_adresse);
        } else {
          // no need to check the type here, it can only be the deletion of the last billing address
          // deleting the last billing address is like setting a new common delivery and billing address
          // see Clients class setNewDefaultAddress function comments for more infos
          $client->coord_livraison = 0;
          $client->setNewDefaultAddress(ClientsAdresses::TYPE_DELIVERY);
        }
        
        $o['response']['text'] = "Suppression de l'adresse de ".ClientsAdresses::getTypeText($cca->type_adresse)." «".$cca->nom_adresse."» effectuée avec succès !";
        break;
    }
    
    $client->save();
    
    // always send back the new address list when everything went ok, and order it by type/num
    function sortByNum($a ,$b) {
      return $a['num'] == $b['num'] ? 0 : ($a['num'] < $b['num'] ? -1 : 1);
    }
    $addressesByTypeArray = ClientsAdresses::orderByType($client->addresses->toArray());
    foreach ($addressesByTypeArray as $type => $ati) // address type info
      uasort($addressesByTypeArray[$type]['list'], 'sortByNum');
    
    $o['response']['address'] = $cca->toArray();
    $o['response']['addressesByType'] = $addressesByTypeArray;
    
  } catch (FormException $fe) {
    $o['error'] = array(
      'text' => "Veuillez corriger les champs en rouge",
      'fields' => $fe->getErrors()
    );
  }
  
  print json_encode($o);
  
} catch (Exception $e) {
  header($_SERVER['SERVER_PROTOCOL'].' 500 Internal Server Error', true, 500);
  echo "Erreur fatale : ".$e->getMessage();
}
