<?php

class Rdv extends BaseObject {

  protected $IdMax = 999999999;

  public static $_tables = array(
    array(
      "name" => "rdv",
      "key" => "id",
      "fields" => array(
        "id" => 0,
        "id_relation" => 0,
        "type_relation" => 0,
        "id_campaign" => 0,
        "id_call" => 0,
        "operator" => 0,
        "timestamp" => 0,
        "timestamp_call" => 0,
        "comment" => '',
        "active" => 1
      )
    )
  );

  public static $_type_relation = array(
    'lead' => 1,
    'client' => 2,
    'client-prospect' => 3,
    'client-relance-supplier-lead' => 4,
    'estimate' => 5
  );

  public static $_source_label = array(
    3 => 'prospect / relance',
    4 => 'prospect / relance',
    5 => 'devis manager'
  );
  
  public static function get() {
    $args = func_get_args();
    return BaseObject::get(self::$_tables, $args);
  }

  public static function getRelationList(){
    return self::$_type_relation;
  }


  public static function delete($id) {
    return BaseObject::delete($id, self::$_tables);
  }
  
  public function __construct( $args = null) {
    $this->tables = self::$_tables;
    parent::__construct($args);
  }

  public function create($data = null) {
    parent::create($data);
  }
  
  public function load() {
    $r = parent::load();
    return $r;
  }
  
  public function save() {
    $r = parent::save();
    return $r;
  }
  
}
