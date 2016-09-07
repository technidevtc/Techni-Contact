<?php

class Config {
  
  private static $instance = null;
  private $db = null;
  private $configList = array();
  
  private function __construct() {
    $conn = Doctrine_Manager::connection();
    $this->db = $conn->getDbh();
    $this->configList = $this->fetchAllFromDB();
  }
  
  private function __clone() {}
  
  public static function getInstance () {
    if (self::$instance == null)
      self::$instance = new self();
    return self::$instance;
  }
  
  private function fetchAllFromDB() {
    $sth = $this->db->query("SELECT * FROM config ORDER BY config_name ASC");
    $configLines = $sth->fetchAll(PDO::FETCH_ASSOC);
    foreach ($configLines as $configLine) {
      $configList[$configLine['config_name']] = array('value' => $configLine['config_value'], 'desc' => $configLine['config_desc']);
    }
    return $configList;
  }
  
  public function get($key) {
    if (isset($this->configList[$key]))
      return $this->configList[$key];
    else
      return null;
  }
  
  public function set($key, $value, $desc = null) {
    if (isset($this->configList[$key])) {
      $this->configList[$key]['value'] = $value;
      if ($desc !== null)
        $this->configList[$key]['desc'] = $desc;
    } else {
      $this->configList[$key] = array('value' => $value, 'desc' => $desc);
    }
    return $this;
  }
  
  public function save() {
    $this->db->beginTransaction();
    try {
      $count = $this->db->exec("DELETE FROM config WHERE 1");
      $sth = $this->db->prepare("INSERT INTO config (config_name, config_value, config_desc) VALUES (?, ?, ?)");
      foreach ($this->configList as $key => $valDesc) {
        $sth->execute(array($key, $valDesc['value'], $valDesc['desc']));
      }
      $this->db->commit();
    } catch (PDOException $e) {
      $this->db->rollBack();
    }
  }

}
