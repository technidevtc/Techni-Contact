<?php

/* ================================================================/

  Techni-Contact V3 - MD2I SAS
  http://www.techni-contact.com

  Auteur : OD pour Hook Network SARL - http://www.hook-network.com
  Date de création : 10/05/2011


  Fichier : /includes/classV3/CCampaign.php
  Description : Classe de gestion des campagnes d'appels

  /================================================================= */

class Campaign extends BaseObject {

  protected $IdMax = 999999999;
  protected static $_tables = array(
      array(
          "name" => "campaigns",
          "key" => "id",
          "fields" => array(
              "id" => 0,
              "nom" => "",
              "timestamp" => 0,
              )
      )
  );
  protected $_statusTitle = array(
    0 => 'Non démarrée',
    1 => 'En cours',
    2 => 'Terminée',
    99 => 'Statut érroné',
  );
  
  public $exists = false;
  public $status;

  public static function get() {
    $args = func_get_args();
    return BaseObject::get(self::$_tables, $args);
  }

  public static function delete($id) {
    $db = DBHandle::get_instance();
    $query = 'delete from campaigns_spool where id_campaign = '.$db->escape($id);
    $db->query($query);
    return BaseObject::delete($id, self::$_tables);
  }

  public function __construct($args = null) {
    $this->tables = self::$_tables;
    parent::__construct($args);
    if ($this->existsInDB)
      $this->exists = true;
  }

  public function create($data = null) {
    parent::create($data);
    $this->built = false;
  }

  public function load($args = null) {
    $r = parent::load($args);
//    $this->fields["data"] = unserialize($this->fields["data"]);
    $this->built = false;
    return $r;
  }

  public function save() {
//    $this->fields["data"] = serialize($this->fields["data"]);
    $r = parent::save();
//    $this->fields["data"] = unserialize($this->fields["data"]);
    return $r;
  }

  public static function getStatusTitle($index){

    return $this->_statusTitle[$index];
    
  }

  public function isDeletable(){
    if($this->exists && $this->getStatus()->value == 0){
      return true;
    }  else {
      return false;
    }
  }

  public function getNbrCalls(){
    if(!$this->exists)
      return false;
    
    $query = 'select count(id) from campaigns_spool where id_campaign = '.$this->db->escape($this->id);
    $res = $this->db->query($query);
    $ret = $this->db->fetch($res);
    return $ret[0];
  }

  public function getNbrEffectiveCalls(){ //Abouti sans lead + avec lead + recontact direct + en absence
    if(!$this->exists)
      return false;

    $query = 'select count(id) from campaigns_spool where id_campaign = '.$this->db->escape($this->id).' and (call_result = \'call_ok\' or call_result = \'call_ok_no_lead\' or call_result = \'customer_calls_back\' or call_result = \'absence\')';
    $res = $this->db->query($query);
    $ret = $this->db->fetch($res);
    return $ret[0];
  }

  public function getNbrCallsMade(){ //Abouti sans lead + abouti avec lead + recontact direct
    if(!$this->exists)
      return false;

    $query = 'select count(id) from campaigns_spool where id_campaign = '.$this->db->escape($this->id).' and (call_result = \'call_ok\' or call_result = \'call_ok_no_lead\' or call_result = \'customer_calls_back\')';
    $res = $this->db->query($query);
    $ret = $this->db->fetch($res);
    return $ret[0];
  }

  public function getNbrCallsMadeByOperator($idOp){
    if(!$this->exists)
      return false;

    $operator = new BOUser($idOp);
    if(!$operator->existsInDB)
      return false;

    $query = 'select count(id) from campaigns_spool where id_campaign = '.$this->db->escape($this->id).' and operator = '.$this->db->escape($operator->id).' and (call_result = \'call_ok\' or call_result = \'call_ok_no_lead\' or call_result = \'customer_calls_back\')';
    $res = $this->db->query($query);
    $ret = $this->db->fetch($res);
    return $ret[0];
  }

  public function getNbrCallsToDo(){
    if(!$this->exists)
      return false;

    $query = 'select count(id) from campaigns_spool where id_campaign = '.$this->db->escape($this->id).' and call_result = \'not_called\'';
    $res = $this->db->query($query);
    $ret = $this->db->fetch($res);
    return $ret[0];
  }

  public function getNbrCallsInAbsence(){
    if(!$this->exists)
      return false;

    $query = 'select count(id) from campaigns_spool where id_campaign = '.$this->db->escape($this->id).' and call_result = \'absence\'';
    $res = $this->db->query($query);
    $ret = $this->db->fetch($res);
    return $ret[0];
  }

  public function getNbrCallsDefinitelyAbsent(){
    if(!$this->exists)
      return false;

    $query = 'select count(id) from campaigns_spool where id_campaign = '.$this->db->escape($this->id).' and call_result = \'absence\' and length(timestamp_calls) = 177';
    $res = $this->db->query($query);
    $ret = $this->db->fetch($res);
    return $ret[0];
  }

  public function getNbrLeadsMade(){
    if(!$this->exists)
      return false;

    $query = 'select count(id) from campaigns_spool where id_campaign = '.$this->db->escape($this->id).' and call_result = \'call_ok\'';
    $res = $this->db->query($query);
    $ret = $this->db->fetch($res);
    return $ret[0];
  }

  public function getNbrLeadsMadeByOperator($idOp){
    if(!$this->exists)
      return false;

    $operator = new BOUser($idOp);
    if(!$operator->existsInDB)
      return false;

    $query = 'select count(id) from campaigns_spool where id_campaign = '.$this->db->escape($this->id).' and operator = '.$this->db->escape($operator->id).' and call_result = \'call_ok\'';
    $res = $this->db->query($query);
    $ret = $this->db->fetch($res);
    return $ret[0];
  }

  public function getNbrCallsOkByOperator($idOp){ //Nombre de contact aboutis avec lead + Nombre de contact aboutis sans lead
    if(!$this->exists)
      return false;

    $operator = new BOUser($idOp);
    if(!$operator->existsInDB)
      return false;

    $query = 'select count(id) from campaigns_spool where id_campaign = '.$this->db->escape($this->id).' and operator = '.$this->db->escape($operator->id).' and (call_result = \'call_ok\' or call_result = \'call_ok_no_lead\')';
    $res = $this->db->query($query);
    $ret = $this->db->fetch($res);
    return $ret[0];
  }

  public function getStatus(){

    $nbrMade = $this->getNbrCallsMade();
    $nbrCalls = $this->getNbrCalls();
    $nbrDefinitelyAbsent = $this->getNbrCallsDefinitelyAbsent();
    $nbrAbsent = $this->getNbrCallsInAbsence();
    
    if($nbrMade+$nbrAbsent == 0)
      $this->status->value = 0;
    elseif($nbrMade+$nbrDefinitelyAbsent != $nbrCalls)
      $this->status->value = 1;
    elseif($nbrCalls == $nbrMade+$nbrDefinitelyAbsent)
      $this->status->value = 2;
    if(!isset($this->status))
      $this->status->value = 99;

    $this->status->title = $this->_statusTitle[$this->status->value];

    return $this->status;
  }

  public function getOperators(){
    if(!$this->exists)
      return false;

    $users = array();
    $query = 'select distinct(operator) from campaigns_spool where id_campaign = '.$this->db->escape($this->id);
    $res = $this->db->query($query);
    while($ret = $this->db->fetchAssoc($res)){

      $user = new BOUser($ret['operator']);
      if($user->existsInDB){
        $op['id'] = $user->id;
        $op['name'] = $user->name;
        $users[] = $op;
      }

    }
    
    return $users;
  }

}