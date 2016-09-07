<?php
/*================================================================/

 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com

 Auteur : OD pour Hook Network SARL - http://www.hook-network.com
 Date de création : 04/07/2011 OD

 Fichier : /secure/fr/manager/ressources/ajax/AJAX_rdv.php
 Description : requete ajax d'enregistrement et de récupération des rdv
 * POST timestamp, operateur, relationId, campagneId (optionnel), action, commentaire (optionnel) => crée un rdv
 * POST idRDV, action => supprime un rdv (desactivation)
 * GET id, id_lead, operateur => récupère un rdv ou une liste de rdv

/=================================================================*/

if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}


$user = new BOUser();
if (!$user->login()) {
  $o['error'] = 'Erreur d\'identification';
  print json_encode($o);
	exit();
}

$o = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (preg_match('/^[1-9]{1}[0-9]{0,9}$/', $_POST['timestamp']) && preg_match('/^\d+$/', $_POST['relationId']) && !empty($_POST['relationType']) && !empty($_POST['action']) && $_POST['action'] == 'createRDV'){

    $idCall = !empty($_POST['callId']) && preg_match('/^[1-9]{1}[0-9]{0,9}$/', $_POST['callId']) ? $_POST['callId'] : '';

    $listRelationType = Rdv::getRelationList();
    
    if (empty($listRelationType[$_POST['relationType']])) {
      $o['error'] = 'Erreur d\'instanciation 1';
      print json_encode($o);
      exit();
    } else {
      $relationType = $listRelationType[$_POST['relationType']];
    }
    
    if (!empty($_POST['operator'])) {
      $operatorId = Doctrine_Query::create()
        ->select('bou.id')
        ->from('BoUsers bou')
        ->where('bou.active = ?', 1)
        ->andWhere('bou.name like ?', array($_POST['operator'].'%'))
        ->fetchOne(array(), Doctrine_Core::HYDRATE_SINGLE_SCALAR);
      if (empty($operatorId)) {
        $o['error'] = "L'opérateur ".$_POST['operator']." n'existe pas";
        print json_encode($o);
        exit();
      }
    } else {
      $operatorId = $user->id;
    }
    
    $rdvData = array(
      'id_relation' => $_POST['relationId'],
      'type_relation' => $relationType,
      'id_call' => $idCall,
      'operator' => $operatorId,
      'timestamp' => time(),
      'timestamp_call' => $_POST['timestamp'],
      'active' => 1
    );
    
    $commentaire = trim($_POST['commentaire']);
    if (!empty($commentaire))
      $rdvData['comment'] = $commentaire;
    
    if (preg_match('/^[1-9]{1}[0-9]{0,8}$/', $_POST['campaignId']))
      $rdvData['id_campaign'] = $_POST['campaignId'];
    
    $idClient = CustomerUser::getCustomerIdFromLogin($_POST['idClient']);
    if (empty($idClient)) {
      $o['error'] = 'Erreur d\'instanciation';
      print json_encode($o);
      exit();
    } else {
      $loginUser = $_POST['idClient'];
    }
    
    $rdv = new Rdv();
    $rdv->setData($rdvData);
    switch ($relationType) {
      case 1:
      case 2:
        $source = !empty($rdvData['id_campaign']) ? 'Campagne d\'appels' : 'Pile d\'appels';
        break;
      case 3:
      case 4:
      case 5:
        $source = Rdv::$_source_label[$relationType];
        break;
    }
    
    if ($rdv->save()) {
      // record in internal notes
      $note = new InternalNotesOld("compte_client");
      $message = "RDV téléphonique le : ".date('d/m/Y H:i:s',$_POST['timestamp'])."\nSource : ".$source."\n".$rdvData['comment'];
      $note->addNote($user, $message, $loginUser);
      
	  
	  $sql_max  = "SELECT id FROM rdv WHERE timestamp = (SELECT MAX(timestamp) from rdv) ";
	  $req_max  = mysql_query($sql_max);
	  $data_max = mysql_fetch_object($req_max);
	  
	  $sql_get  = "SELECT type_relation,timestamp_call,operator,id_relation
				   FROM   rdv 
				   WHERE  id='".$data_max->id."' ";
	  $req_get  = mysql_query($sql_get);
	  $data_get = mysql_fetch_object($req_get);
	  
	  $operator 		= $data_get->operator;
	  $rdv_id   		= $data_max->id;
	  
	  $sql_check  = "SELECT appels_commerciale FROM bo_users WHERE id='".$operator."' ";
	  $req_check  =  mysql_query($sql_check);
	  $data_check =  mysql_fetch_object($req_check);
	  if($data_check->appels_commerciale == '1'){
	  $timestamp_call   = date('Y-m-d H:i:s', $data_get->timestamp_call);
	  
	  if($data_get->type_relation == '5'){
		   $type  =  'RDV devis';
		   $id_estimate = $data_get->id_relation;
		   $id_client   = "";
	  }
	  if($data_get->type_relation == '3'){
		   $type  	    =  'RDV client';
		   $id_client   =  $data_get->id_relation;
		   $id_estimate =  "";
	  }
	  if($data_get->type_relation == '4'){
		   $type  	    =  'RDV client';
		   $id_leads    =  $data_get->id_relation;
		   
		   $sql_contact   = "SELECT email FROM contacts WHERE id='$id_leads' ";
		   $req_contact   =  mysql_query($sql_contact); 
		   $data_contact  =  mysql_fetch_object($req_contact);
		   
		   $sql_id_client =  "SELECT id FROM clients WHERE email='".$data_contact->email."' ";
		   $req_id_client =   mysql_query($sql_id_client);
		   $data_id_client=   mysql_fetch_object($req_id_client);
		   $id_client     =   $data_id_client->id;
		   $id_estimate =  "";
	  }
	  
		
		$sql_insert = "INSERT INTO `call_spool_vpc` (
									`id`, 
									`order_id`, 
									`client_id`, 
									`rdv_id`, 
									`campaignID`, 
									`estimate_id`, 
									`timestamp_created`, 
									`timestamp_campaign`, 
									`campaign_name`, 
									`call_type`, 
									`assigned_operator`, 
									`call_operator`, 
									`timestamp_rdv`, 
									`timestamp_first_call`, 
									`timestamp_second_call`, 
									`timestamp_third_call`, 
									`calls_count`, 
									`call_result`) 
						  VALUES (NULL, 
										'', 
										'".$id_client."', 
										'".$rdv_id."', 
										'', 
										'".$id_estimate."', 
										NOW(),
										'0000-00-00 00:00:00', 
										'$type', 
										'4', 
										'$operator', 
										'', 
										'$timestamp_call', 
										'0000-00-00 00:00:00', 
										'0000-00-00 00:00:00', 
										'0000-00-00 00:00:00', 
										'0', 
										'not_called')";		
		mysql_query($sql_insert);
	} 
		$o['reponse'] = 'ok';
		//$o['reponse'] = $sql_insert;
	  
    } else {
      $o['error'] = 'Erreur à l\'enregistrement';
    }
  
  } elseif (preg_match('/^[1-9]{1}[0-9]{0,9}$/', $_POST['idRDV']) && !empty( $_POST['action']) && $_POST['action'] == 'deleteRDV') {
    $rdv = new Rdv($_POST['idRDV']);
    if ($rdv->operator == $user->id) { // a rdv can only be deactivated by its creator
      $rdv->active = 0;
      $rdv->save();
      $o['reponse'] = 'ok 11';
    } else {
      $o['error'] = 'Vous ne pouvez effectuer cette action';
    }
  } else {
    $o['error'] = 'Requête incorrecte';
  }


} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {

  $listRelationType = Rdv::getRelationList();

  if (!empty($_GET['relationType']) && strcasecmp($_GET['relationType'],'all') == 0) {
    $relationType = 'all';
  } elseif (empty($listRelationType[$_GET['relationType']])) {
    $o['error'] = 'Erreur d\'instanciation';
    print json_encode($o);
    exit();
  } else {
    $relationType = $listRelationType[$_GET['relationType']];
  }
  
  $rdvList = array();
  
  switch ($relationType) {
    case 'all':
      $rdvList = Rdv::get('active = 1', ' AND operator = '.$user->id, 'ORDER BY timestamp_call ASC');
      break;
    default:
      if (!empty($_GET['relationId']) && preg_match('/^\d+$/', $_GET['relationId']))
        $rdvList = Rdv::get('id_relation = '.$_GET['relationId'], ' AND active = 1', ' AND type_relation = '.$relationType, 'ORDER BY timestamp_call ASC');
      break;
  }
  
  
//  if( !empty ( $_GET['operateur']) && preg_match('/^[1-9]{1}[0-9]{0,8}$/', $_GET['operateur']))
//    $rdvList = Rdv::get('operator = '.$user->id, ' AND active = 1', 'order by timestamp_call asc');
  $coordInfo = array(
    'login' => '',
    'prenom' => '',
    'nom' => 'Impossibilité de récupérer le nom du client',
    'societe' => '',
    'tel' => ''
  );
  foreach ($rdvList as &$rendezvous) {
    $utilisat = new BOUser($rendezvous['operator']);
    $rendezvous['nom_operator'] = $utilisat->name;

    if ($rendezvous['id_campaign']) {
      $campaign = new Campaign($rendezvous['id_campaign']);
      $rendezvous['nom_campaign'] = $campaign->nom;
    }else {
      $rendezvous['nom_campaign'] = '';
    }
    
    switch ($rendezvous['type_relation']) {
      case 1:
      case 4:
        $lead = new Lead($rendezvous['id_relation']);
        if ($lead->existsInDB()) {
          $coordInfo = array(
            'login' => $lead->email,
            'prenom' => $lead->prenom,
            'nom' => $lead->nom,
            'societe' => $lead->societe,
            'tel' => $lead->tel
          );
        }
        break;
      case 3:
        $customer = new CustomerUser(DBHandle::get_instance(), $rendezvous['id_relation']);
        $coordInfo = array(
          'login' => $customer->login,
          'prenom' => $customer->prenom,
          'nom' => $customer->nom,
          'societe' => $customer->societe,
          'tel' => $customer->tel1
        );
        break;
      case 5:
        $q = Doctrine_Query::create()
            ->select('e.id, e.email, e.prenom, e.nom, e.societe, e.tel')
            ->from('Estimate e')
            ->where('e.id = ?', $rendezvous['id_relation']);
        $estimate = $q->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);
        if (!empty($estimate['id'])) {
          $coordInfo = array(
            'login' => $estimate['email'],
            'prenom' => $estimate['prenom'],
            'nom' => $estimate['nom'],
            'societe' => $estimate['societe'],
            'tel' => $estimate['tel']
          );
        }
        
    }
    $rendezvous['coordInfo'] = $coordInfo;
  }

  if (!empty($rdvList))
    $o['reponse'] = $rdvList;
  else
    $o['reponse'] = 'liste vide';
  
}

print json_encode($o);

