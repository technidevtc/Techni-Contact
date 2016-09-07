<?php

class Lead extends BaseObject {

  protected $IdMax = 16777215;
  protected static $_tables = array(
      array(
          "name" => "contacts",
          "key" => "id",
          "fields" => array(
              "id" => 0,
              "idProduct" => 0,
              "idFamily" => 0,
              "idAdvertiser" => 0,
              "type" => 0,
              "create_time" => 0,
              "nom" => "",
              "prenom" => "",
              "fonction" => "",
              "societe" => "",
              "salaries" => "",
              "secteur" => "",
              "qualification" => "",
              "naf" => "",
              "siret" => "",
              "adresse" => "",
              "cadresse" => "",
              "cp" => "",
              "ville" => "",
              "pays" => "",
              "tel" => "",
              "fax" => "",
              "email" => "",
              "url" => "",
              "infos_sup" => "",
              "precisions" => "",
              "timestamp" => 0,
              "gen" => 0,
              "cread" => 0,
              "sent" => 1,
              "campaignID" => 0,
              "customFields" => "",
              "invoice_status" => 1,
              "income" => 0,
              "income_total" => 0,
              "parent" => 0,
              "reject_reason" => "",
              "reject_timestamp" => 0,
              "credited_on" => 0,
              "id_user" => 0,
              "id_user_commercial" => 0,
              "id_user_processed" => 0,
              "processing_status" => 1,
              "processing_time" => 0,
              "processing_reason" => "",
              "processing_comment" => "",
              "origin" => ""
          )
      )
  );

  public static function get() {
    $args = func_get_args();
    $args = !is_array($args) ? $args : $args[0];// évite l'inclusion d'un tableau d'arguments dans un autre tableau
    return BaseObject::get(self::$_tables, $args);
  }

  public static function getOriginList(){
    $db = DBHandle::get_instance();
    $query = 'SELECT DISTINCT origin FROM contacts';

    $list = false;
    $res = $db->query($query);
    while($ret = $db->fetchAssoc($res)){
      if(!empty ($ret['origin']))
      $list[Utils::toDashAz09($ret['origin'])] = $ret['origin'];
    }
    return $list;

  }

  public static function delete($id) {
    return BaseObject::delete($id, self::$_tables);
  }

  public function __construct($args = null) {
    $this->tables = self::$_tables;
    parent::__construct($args);
  }
  
//  public function __construct($id = null) {
//    $this->tables = self::$_tables;
//    $this->linkedTables = self::$_linkedTables;
//    parent::__construct($id);
//  }

  public function load(){
    $r = parent::load();
    $this->fields["customFields"] = unserialize($this->fields["customFields"]);
    return $r;
  }
  
  public function save() {
    $customFields = $this->fields["customFields"];
    $this->fields["customFields"] = serialize($customFields);
    $r = parent::save();
    $this->fields["customFields"] = $customFields;
    return $r;
  }

}

?>
