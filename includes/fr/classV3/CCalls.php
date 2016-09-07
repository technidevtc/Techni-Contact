<?php

/* ================================================================/

  Techni-Contact V3 - MD2I SAS
  http://www.techni-contact.com

  Auteur : OD pour Hook Network SARL - http://www.hook-network.com
  Date de création : 21 mars 2011


  Fichier : /includes/classV2/CCall.php
  Description : Classe de gestion de la pile d'appels sortants (call center SMPO)

  /================================================================= */

class Calls extends BaseObject {
  /* Handle connexion */

  protected $IdMax = 999999999;
  protected static $_tables = array(
    array(
      "name" => "call_spool",
      "key" => "id",
      "fields" => array(
        "id" => 0,
        "id_lead" => 0,
        "id_client" => "",
        "operator" => 0,
        "timestamp" => 0,
        "timestamp_in_line" => 0,
        "timestamp_calls" => array(),
        "calls_count" => 0,
        "daily_absence" => 0,
        "call_result" => "not_called"
      )
    )
  );

  protected $_statusTitle = array(
    'not_called' => 'Non encore contacté',
    'call_ok' => 'Client contacté',
    'absence' => 'Appel non abouti',
    'call_ok_no_lead' => 'Appel abouti sans lead',
    'customer_calls_back' => 'Rappel par le client'
  );
  
  public static $maxCallsInAbsence = 10;
  public static $maxPendingCalls = 5;
  public $call_resultTitle = "";

  public static function get() {
    $args = func_get_args();
    return BaseObject::get(self::$_tables, $args);
  }

  public static function delete($id) {
    return BaseObject::delete($id, self::$_tables);
  }

  public static function getLastConvIdFromEmail($email) {
    $db = DBHandle::get_instance();
    $last_conv_id = self::get("id", "id_client = '".$db->escape($email)."'", "order by timestamp desc", "limit 0,1");
    return $last_conv_id[0]["id"];
  }
  
  // true si dernier lead datant de plus d'un mois ou 10 appells en absence successif
  public static function isAddableCallForEmail($email) {
    $db = DBHandle::get_instance();
    
    if (self::hasPendingCall($email)) // there is already a lead waiting to be called, we don't add a new one
      return false;
    
    $now = time();
    $h = date('H', $now);
    $m = date('i', $now);
    $thisDay = date('d', $now);
    $thisMonth = date('m', $now);
    $thisYear = date('Y', $now);
    $lastMonth = mktime($h, $m, 0, $thisMonth-1, $thisDay, $thisYear);

    // there are no leads to be called so
    // whether there was an unanswered call
    // or the call was answered then test duration
    $lastConvId = self::getLastConvIdFromEmail($email);
    if (isset($lastConvId)) {
      $res = $db->query("
        SELECT
          (calls_count >= ".self::$maxCallsInAbsence." AND call_result = 'absence') OR
          (timestamp_in_line > 0 AND timestamp_in_line < ".$lastMonth.")
        FROM call_spool
        WHERE id = ".$lastConvId, __FILE__, __LINE__);
      list($ret) = $db->fetch($res);
      return !!$ret;
    }
    else {
      return true;
    }
  }

  // true si moins de 6 appels en absence ou non effectués et non 'in line'
  public static function hasPendingCall($email) {
    $db = DBHandle::get_instance();
    $lastConvId = self::getLastConvIdFromEmail($email);
    if (isset($lastConvId)) {
      $res = $db->query("
        SELECT COUNT(id) AS nb_calls
        FROM call_spool
        WHERE
          id = ".$lastConvId." AND
          timestamp_in_line = 0 AND
          calls_count <= ".self::$maxPendingCalls." AND
          (call_result = 'absence' OR call_result = 'not_called')", __FILE__, __LINE__);
      list($nbrPending) = $db->fetch($res);
      return !!$nbrPending;
    }
    else {
      return false;
    }
  }
  
  public function __construct($args = null) {
    $this->tables = self::$_tables;
    parent::__construct($args);
  }

  public function load($args = null) {
    if ($r = parent::load($args)) {
      $this->fields["timestamp_calls"] = unserialize($this->fields["timestamp_calls"]);
      if (empty($this->fields["timestamp_calls"]))
        $this->fields["timestamp_calls"] = array();
      $this->call_resultTitle = $this->_statusTitle[$this->call_result];
    }
    return $r;
  }

  public function save() {
    $this->fields['timestamp'] = time();
    $this->fields["timestamp_calls"] = serialize($this->fields["timestamp_calls"]);
    $r = parent::save();
    $this->fields["timestamp_calls"] = unserialize($this->fields["timestamp_calls"]);
    return $r;
  }

  public function setInLine($operator) {
    $op = new BOUser($operator);
    if ($op->existsInDB() && $this->timestamp_in_line == 0 && $this->fields["calls_count"] < self::$maxCallsInAbsence) {
      $this->fields['operator'] = $op->id;
      $this->fields['timestamp_in_line'] = time();
      $this->altered = true;
      return $this->save();
    }
    else {
      return false;
    }
  }

  public function setCallOk() {
    if ($this->fields["calls_count"] < self::$maxCallsInAbsence) {
      $this->addTimestampToCalls();
      $this->fields['call_result'] = 'call_ok';
      $this->fields['timestamp_in_line'] = 0;
      $this->altered = true;
      return $this->save();
    }
    else {
      return false;
    }
  }

  public function setCallNok() {
    if ($this->fields["calls_count"] < self::$maxCallsInAbsence) {
      $this->addTimestampToCalls();
      $this->fields['call_result'] = 'absence';
      $this->fields['timestamp_in_line'] = 0;
      $this->fields['daily_absence']++;
      $this->altered = true;
      return $this->save();
    }
    else {
      return false;
    }
  }

  public function setCallOkNoLead() {
    if($this->fields["calls_count"] < self::$maxCallsInAbsence) {
      $this->addTimestampToCalls();
      $this->fields['call_result'] = 'call_ok_no_lead';
      $this->fields['timestamp_in_line'] = 0;
      $this->altered = true;
      return $this->save();
    }
    else {
      return false;
    }
  }

  public function setCustomerCallsBack() { 
    if ($this->fields["calls_count"] < self::$maxCallsInAbsence) {
      $this->addTimestampToCalls();
      $this->fields['call_result'] = 'customer_calls_back';
      $this->fields['timestamp_in_line'] = 0;
      $this->altered = true;
      $this->save();
    }
  }

  private function addTimestampToCalls() {
    $this->fields['timestamp_calls'][] = time();
    $this->fields["calls_count"]++;
  }

}