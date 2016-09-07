<?php

session_name('manager');
session_start();

define('CONTRIB',         0);
define('COMM',            1);
define('COMMADMIN',       2);
define('HOOK_NETWORK',    4);

require_once(ADMIN."logs.php");

class BOUser extends BaseObject {

  protected $IdMax = 65535;
  protected $permissions = null;
  
  public static $_tables = array(
    array(
      "name" => "bo_users",
      "key" => "id",
      "fields" => array(
        "id" => 0,
        "name" => "",
        "login" => "",
        "pass" => "",
        "gmailCipher" => "",
        "rank" => 1,
        "email" => "",
        "phone" => "",
        "help_msg" => "",
        "active" => 1,
        "create_time" => 0,
        "timestamp" => 0,
        'leads_best_score' => 0
      )
    )
  );

  public static function get() {
    $args = func_get_args();
    return BaseObject::get(self::$_tables, $args);
  }
  
  public static function delete($id) {
    return BaseObject::delete($id, self::$_tables);
  }
  
  public function __construct($args = null) {
    $this->tables = self::$_tables;
    parent::__construct($args);
  }

  public function __destruct() {}
  
  public function save() {
    if (isset($this->permissions))
      $this->permissions->update();
    return parent::save();
  }
  
  public function get_permissions() {
    if (!isset($this->permissions))
      $this->permissions = new BOUserPermissionCollection($this);
    return $this->permissions;
  }
  
  // taken from old ManagerUser.php
  public function login($login = "", $pass = "") {
    $ret = false;

    //pp($_SESSION);
    if (isset($_SESSION["login"]) && isset($_SESSION["pass"]) && isset($_SESSION["ip"]) && isset($_SESSION["id"]) && $login == "") {
      $result = $this->db->query("
        SELECT id, name, login, pass, gmailCipher, rank, email, phone, help_msg, active, create_time, timestamp
        FROM bo_users
        WHERE
          id = '".$this->db->escape($_SESSION["id"])."' AND
          login = '".$this->db->escape($_SESSION["login"])."' AND
          pass = '".$this->db->escape($_SESSION["pass"])."' AND
          active = 1", __FILE__, __LINE__);
      
      if ($this->db->numrows($result, __FILE__, __LINE__) == 1 && ($_SESSION["ip"] == $this->getIP() || $this->getIP() == SERVER_IP)) {
        $ret = true;
        $rec = $this->db->fetchAssoc($result);
        $this->setData($rec);
        $this->id = $this->fields["id"];
      }
      else {
        // Données session falsifiées ou adresse ip a changé, on logge l'action !
        ManagerLog($this->db, $_SESSION["id"], $_SESSION["login"], $_SESSION["pass"], $_SESSION["ip"], "Erreur de session : piratage des données d'identification ou adresse IP invalide (Adresse courante : ".$this->getIP().")");
        @session_destroy();
      }
    
    } elseif ($login != "") {
      $md5pass = md5($pass);

      $ip = $this->getIP();
      $result = $this->db->query("
        SELECT id, name, login, pass, gmailCipher, rank, email, phone, help_msg, active, create_time, timestamp
        FROM bo_users
        WHERE
          login = '".$this->db->escape($login)."' AND
          pass = '".$md5pass."' AND
          active = 1", __FILE__, __LINE__);

      if ($this->db->numrows($result, __FILE__, __LINE__) == 1) {
        $ret = true;
        $rec = $this->db->fetchAssoc($result);
        $this->setData($rec);
        $this->id = $this->fields["id"];
        
        session_regenerate_id();

        // Données de la session
        $_SESSION['login']      = $login;
        $_SESSION['pass']       = $md5pass;
        $_SESSION['ip']         = $ip;
        $_SESSION['id']         = $this->id;

        // Login réussi, on logge l'action !
        ManagerLog($this->db, $this->id, $login, $md5pass, $ip, "Identification de l'utilisateur (".$md5pass.")");
      }
      else {               
        // Login échoué, on logge l'action !
        ManagerLog($this->db, 0, $login, $md5pass, $ip, "Erreur lors de l'identification - données soumises incorrectes");
      }
    }

    return $ret;
  }
  
  public function getGmailInfos() {
    $gmailInfos = $this->gmailCipher != '' ? Utils::decrypt($this->gmailCipher, $this->pass) : ' ';
    $gmailInfos = explode(' ', $gmailInfos, 2);
    return array(
      'login' => $gmailInfos[0],
      'pass' => $gmailInfos[1]
    );
  }
  
  public function setGmailInfos($gmailLogin, $gmailPass) {
    if (!empty($gmailLogin) && !empty($gmailPass))
      $this->gmailCipher = Utils::encrypt($gmailLogin.' '.$gmailPass, $this->pass);
    else
      $this->gmailCipher = '';
    return $this;
  }

  private function getIP() {
    return $_SERVER["REMOTE_ADDR"];
  }

}
