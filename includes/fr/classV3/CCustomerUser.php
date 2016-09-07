<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de cr�ation : 20 d�cembre 2004

 Mises � jour :

       31 mai 2005 : = nouveau gestionnaire de rangs
                     + gestion session s�curis�e avec contr�le adresse ip

 Fichier : /includes/classV2/ManagerUser.php4
 Description : Classe utilisateur manager

/=================================================================*/

require_once(LANG_LOCAL_INC . "infos-" . DB_LANGUAGE . "_local.php");

class CustomerUser
{
  
  private $handle = NULL;
  private $existsInDB = false;
  public $exists = false;


  // Fields w/ default values in table Clients : usefull to load/save
  private $fields = array(
    "id" => 0,
    "last_update" => 0,
    "login" => "",
    "pass" => "",
    "timestamp" => 0,
    "titre" => "",
    "nom" => "",
    "prenom" => "",
    "fonction" => "",
    "societe" => "",
    "nb_salarie" => "",
    "secteur_activite" => "",
    "secteur_qualifie" => "",
    "code_naf" => "",
    "num_siret" => "",
    "adresse" => "",
    "complement" => "",
    "ville" => "",
    "cp" => "",
    "pays" => "",
    "infos_sup" => "",
    "titre_l" => "",
    "nom_l" => "",
    "prenom_l" => "",
    "societe_l" => "",
    "adresse_l" => "",
    "complement_l" => "",
    "ville_l" => "",
    "cp_l" => "",
    "pays_l" => "",
    "infos_sup_l" => "",
    "coord_livraison" => 0,
    "tel1" => "",
    "tel2" => "",
    "tel_match" => "",
    "fax1" => "",
    "fax2" => "",
    "url" => "",
    "activationCode" => "",
    "death" => 0,
    "actif" => 1,
    "email" => "",
    "origin" => "O",
    "website_origin" => "TC",
    "code" => "", // compta
    "tva_intra" => "",
    "default_adresse" => "O",
    "default_adresse_l" => "O",
  );

  // Will containe the object fields data
  //private $data = null;
  
  public static function canLogin($login, $pass, &$handle) {
    $ret = false;
    if (!empty($login) && !empty($pass)) {
      $pass = & md5($pass);
      $res = & $handle->query("select id from clients where login = '" . $handle->escape(trim($login)) . "' and pass = '" . $pass . "' and actif = 1", __FILE__, __LINE__);
      if ($handle->numrows($res, __FILE__, __LINE__) == 1) {
        list($ret) = $handle->fetch($res);
      }
    }
    return $ret;
  }
  
  public static function getCustomerIdFromLogin($login, $handle = null) {
    $db = DBHandle::get_instance();
    $ret = false;
    if (!empty($login)) {
      $res = $db->query("select `id` from `clients` where `login` = '" . $db->escape(trim($login)) . "'", __FILE__, __LINE__);
      if ($db->numrows($res, __FILE__, __LINE__) >= 1) {
        list($ret) = $db->fetch($res);
      }
    }
    return $ret;
  }
  
  public static function getCustomerOriginFromLogin($login, $handle) {
    $ret = false;
    if (!empty($login)) {
      $res = & $handle->query("SELECT `origin` FROM `clients` WHERE `login` = '".$handle->escape(trim($login))."'", __FILE__, __LINE__);
      if ($handle->numrows($res, __FILE__, __LINE__) >= 1) {
        list($ret) = $handle->fetch($res);
      }
    }
    return $ret;
  }
  
  public static function getCustomerIdFromEmail($email, $handle) {
    $ret = false;
    if (!empty($email)) {
      $res = & $handle->query("select id from clients where email = '" . $handle->escape($email) . "'", __FILE__, __LINE__);
      if ($handle->numrows($res, __FILE__, __LINE__) >= 1) {
        list($ret) = $handle->fetch($res);
      }
    }
    return $ret;
  }
  
  public static function getTitle($titleID) {
    switch ($titleID) {
      case 1  : $title = INFOS_MR; break;
      case 2  : $title = INFOS_MRS; break;
      case 3  : $title = INFOS_MISS; break;
      default : $title = INFOS_MR; break;
    }
    return $title;
  }

  public static function delete($id) {
    $db = DBHandle::get_instance();
    
    $res = $db->query("SELECT login FROM `clients` WHERE `id` = '".$db->escape($id)."'", __FILE__, __LINE__);
    list($login) = $db->fetch($res);
    $db->query("DELETE FROM `clients` WHERE `id` = '".$db->escape($id)."'", __FILE__, __LINE__);
    $db->query("DELETE FROM `contacts` WHERE `email` = '".$db->escape($login)."'", __FILE__, __LINE__);
    $db->query("DELETE FROM `emails` WHERE `email` = '".$db->escape($login)."'", __FILE__, __LINE__);
    
    return true;
  }
  
  function __construct(& $handle, $id = null) {
    $this->handle = DBHandle::get_instance();
    if ($id != null) {
      $this->id = $id;
      $this->load();
    }
  }
  
  /*
  public function __set($name, $value) {
    $this->data[$name] = $value;
  }

  public function __get($name) {
    if (array_key_exists($name, $this->data)) {
      return $this->data[$name];
    }
    return null;
  }
  */
  
  private function generateID() {
    do {
      $id = mt_rand(1, 999999999);
      $res = & $this->handle->query("select id from clients where id = " . $id, __FILE__, __LINE__);
    }
    while ($this->handle->numrows($res, __FILE__, __LINE__) == 1);
    
    $this->id = $id;
  }
  
  public function generatePassword($length = 8) {
    $pass = '';
    for ($i = 0; $i < $length; $i++) {
      $type = mt_rand(1, 8);
      if ($type == 1) $pass .= mt_rand(0, 9);
      elseif ($type > 1 && $type < 4) $pass .= chr(mt_rand(ord('A'), ord('Z')));
      else $pass .= chr(mt_rand(ord('a'), ord('z')));
    }
    $this->pass = md5($pass);
    return $pass;
  }
  
  public function create($generate_id = true) {
    foreach ($this->fields as $fieldName => $dftValue) {
      $this->$fieldName = $dftValue;
    }
    
    if ($generate_id) $this->generateID();
      $this->existsInDB = false;
    $this->exists = false;
  }
  
  public function load() {

    $res = & $this->handle->query("
      select " . implode(",", array_keys($this->fields)) . "
      from clients
      where id = " . $this->id, __FILE__, __LINE__);
    if ($this->handle->numrows($res, __FILE__, __LINE__) == 0)
      return false;//throw new Exception("MySQL : Error while loading the Customer.");
      
    $data = & $this->handle->fetchAssoc($res);
    foreach ($this->fields as $fieldName => $dftValue) {
      $this->$fieldName = isset($data[$fieldName]) ? $data[$fieldName] : $dftValue;
    }
    $this->existsInDB = true;
    $this->exists = true;
  }
  
  public function save($time = null) {
    $this->last_update = $this->timestamp = $time ? $time : time();
    // setting the field here to be sure this will be persisted to the DB
    $this->tel_match = Clients::getTelMatchString($this->tel1.'_'.$this->tel2);
    if ($this->existsInDB) {
      $fields = array();
      foreach ($this->fields as $fieldName => $dftValue) {
        $fields[] = $fieldName . " = '" . $this->handle->escape($this->$fieldName) . "'";
      }
      $queries[] = "
        UPDATE clients
        SET " . implode(",", $fields) . "
        WHERE id = " . $this->id;
    }
    else {
      $fields = array();
      foreach ($this->fields as $fieldName => $dftValue) {
        $fields[] = "'" . $this->handle->escape($this->$fieldName) . "'";
      }
      $queries[] = "
        INSERT INTO clients (" . implode(",", array_keys($this->fields)) . ")
        VALUES(" . implode(",", $fields) . ")";
    }
    
    foreach ($queries as $query) {
      if (!$this->handle->query($query, __FILE__, __LINE__, false)) {
        throw new Exception("MySQL : Error while Updating the Customer.");
      }
    }
    
    $this->updateAddresses();
    //$this->updateContacts();
    
    $this->existsInDB = true;
    $this->exists = true;
  }
  
  public function updateContacts(){
   /* $q = Doctrine_Query::create()
        ->select()
        ->from('ClientsContacts')
        ->where('client_id = ?', $this->id)
        ->andWhere('num = ?', 0);
    $cal = $q
        ->execute();
        
    foreach (ClientsContacts::$cf2ccf as $cf => $af)
        $cal[0]->$af = $this->$cf;
    $cal[0]->client_id = $this->id;
    
    $cal->save();*/
  }


  public function updateAddresses() {
    // temp until we don't use 2 kind of client object
    $q = Doctrine_Query::create()
      ->select('*')
      ->from('ClientsAdresses')
      ->where('client_id = ?', $this->id)
      ->andWhere('num = ?', 0);
    if ($this->coord_livraison == 0)
      $q->andWhere('type_adresse = ?', ClientsAdresses::TYPE_DELIVERY);
    $cal = $q
      ->orderBy('type_adresse ASC') // delivery fields type are always < to billing ones
      ->execute();
    
    // create the delivery address if it doesn't exist
    if (!count($cal)) {
      $cal = new Doctrine_Collection('ClientsAdresses');
      $cal[0] = new ClientsAdresses();
      $cal[0]->nom_adresse = "Adresse principale";
      $cal[0]->client_id = $this->id;
    }
    // no specified delivery fields, copy default fields to the main delivery one
    if ($this->coord_livraison == 0) {
      foreach (Clients::$cbf2caf as $cf => $af)
        $cal[0]->$af = $this->$cf;
    // specified delivery infos -> copy "_l" fields to the main delivery address, and the normal fields to the main billing one
    } else {
      // create the billing address if it's not present
      if (!isset($cal[1])) {
        $cal[1] = new ClientsAdresses();
        $cal[1]->nom_adresse = "Adresse de facturation";
        $cal[1]->type_adresse = ClientsAdresses::TYPE_BILLING;
        $cal[1]->client_id = $this->id;
      }
      foreach (Clients::$cdf2caf as $cf => $af)
        $cal[0]->$af = $this->$cf;
      foreach (Clients::$cbf2caf as $cf => $af)
        $cal[1]->$af = $this->$cf;
    }
    
    $cal->save();
  }
  
  public function setCoordFromArray(&$coordArray) {
    foreach($coordArray as $fieldName => $value) {
      if (isset($this->fields[$fieldName]))
        $this->$fieldName = $value;
    }
  }
  
  public function & getCoordFromArray($withDefaultAddress = false) {
    $coordArray = array();
    foreach($this->fields as $fieldName => $dftValue) {
      $coordArray[$fieldName] = $this->$fieldName;
    }
    if($coordArray['default_adresse'] && $withDefaultAddress == true){
      $default_adresse = Doctrine_Query::create()
              ->select()
              ->from('ClientsAdresses')
              ->where('id = ?', $coordArray['default_adresse'])
              ->fetchOne(array(), doctrine_core::HYDRATE_ARRAY);
      $coordArray['nom'] = $default_adresse['nom'];
      $coordArray['prenom'] = $default_adresse['prenom'];
      $coordArray['societe'] = $default_adresse['societe'];
      $coordArray['tel1'] = $default_adresse['tel1'];
      $coordArray['fax1'] = $default_adresse['fax1'];
      $coordArray['adresse'] = $default_adresse['adresse'];
      $coordArray['complement'] = $default_adresse['complement'];
      $coordArray['ville'] = $default_adresse['ville'];
      $coordArray['cp'] = $default_adresse['cp'];
      $coordArray['pays'] = $default_adresse['pays'];
      $coordArray['infos_sup'] = $default_adresse['infos_sup'];
    }

    if($coordArray['default_adresse_l'] && $withDefaultAddress == true){
      $default_adresse = Doctrine_Query::create()
              ->select()
              ->from('ClientsAdresses')
              ->where('id = ?', $coordArray['default_adresse_l'])
              ->fetchOne(array(), doctrine_core::HYDRATE_ARRAY);
      $coordArray['nom_l'] = $default_adresse['nom'];
      $coordArray['prenom_l'] = $default_adresse['prenom'];
      $coordArray['societe_l'] = $default_adresse['societe'];
      $coordArray['tel2'] = $default_adresse['tel1'];
      $coordArray['fax2'] = $default_adresse['fax1'];
      $coordArray['adresse_l'] = $default_adresse['adresse'];
      $coordArray['complement_l'] = $default_adresse['complement'];
      $coordArray['ville_l'] = $default_adresse['ville'];
      $coordArray['cp_l'] = $default_adresse['cp'];
      $coordArray['pays_l'] = $default_adresse['pays'];
      $coordArray['infos_sup_l'] = $default_adresse['infos_sup'];
    }
    return $coordArray;
  }

  public function getCustomerFromLogin($email) {
    $ret = false;
    if( ! preg_match('/^[[:alnum:]]([-_.]?[[:alnum:]])*@[[:alnum:]]([-_.]?[[:alnum:]])*\.([a-z]{2,4})$/', $email) ) // email test
      return $ret;
    if (!empty($email)) {
      $query = "select id from clients where login = '" . $this->handle->escape($email) . "'";
      $res = $this->handle->query($query, __FILE__, __LINE__);
      if ($this->handle->numrows($res, __FILE__, __LINE__) == 1) {
        $ret = $this->handle->fetchAssoc($res);
        $this->id = $ret['id'];
        $this->load();
        $this->exists = true;
        $this->existsInDB = true;
      }
    }
    return $ret;
  }
  public function getCustomerFromContact($email) {
    $ret = false;
    if( ! preg_match('/^[[:alnum:]]([-_.]?[[:alnum:]])*@[[:alnum:]]([-_.]?[[:alnum:]])*\.([a-z]{2,4})$/', $email) ) // email test
      return $ret;
    if (!empty($email)) {
      $query = "SELECT c.id AS cid FROM clients c INNER JOIN clients_contacts cc ON c.id = cc.client_id WHERE cc.email = '" . $this->handle->escape($email) . "'";
      $res = $this->handle->query($query, __FILE__, __LINE__);
      if ($this->handle->numrows($res, __FILE__, __LINE__) == 1) {
        $ret = $this->handle->fetchAssoc($res);
        $this->id = $ret['cid'];
        $this->load();
        $this->exists = true;
        $this->existsInDB = true;
      }
    }
    return $ret;
  }

  public function getCustomerFromCompany($company) {
    $ret = false;
    if (!empty($company)) {
      $query = "select id from clients where societe like '%" . $this->handle->escape($company) . "%'";
      $res = $this->handle->query($query, __FILE__, __LINE__);
      if ($this->handle->numrows($res, __FILE__, __LINE__) > 0) {
        $data = array();
        while($row = $this->handle->fetchAssoc($res)){
          $obj = new self($this->handle);
          $obj->id = $row['id'];                          
          $obj->load();
          $ret[] =  $obj ;
        }
      }
    }
    return $ret;
  }
  
  public function getCustomerFromName($customerName) {
    $ret = false;
    if (!empty($customerName)) {
      $query = "select id from clients where nom like '%" . $this->handle->escape($customerName) . "%'";
      $res = $this->handle->query($query, __FILE__, __LINE__);
      if ($this->handle->numrows($res, __FILE__, __LINE__) > 0) {
        $data = array();
        while($row = $this->handle->fetchAssoc($res)){
          $obj = new self($this->handle);
          $obj->id = $row['id'];                          
          $obj->load();
          $ret[] =  $obj ;
        }
      }
    }
    return $ret;
  }

  public function getCustomerFromTelephone($tel) {
    $ret = false;
    if (!empty($tel) && is_numeric($tel)) {
      $telArray = str_split($tel,1);
      $listRegex = implode('.*', $telArray);
      $query = "select id from clients where tel1 regexp '.*" . $this->handle->escape($listRegex) . ".*' or tel2 regexp '.*" . $this->handle->escape($listRegex) . ".*' or fax1 regexp '.*" . $this->handle->escape($listRegex) . ".*' or fax2 regexp '.*" . $this->handle->escape($listRegex) . ".*'";

      $res = $this->handle->query($query, __FILE__, __LINE__);
      if ($this->handle->numrows($res, __FILE__, __LINE__) > 0) {
        $data = array();
        while($row = $this->handle->fetchAssoc($res)){
          $obj = new self($this->handle);
          $obj->id = $row['id'];
          $obj->load();
          $ret[] =  $obj ;
        }
      }
    }
    return $ret;
  }
  
}
