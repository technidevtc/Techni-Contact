<?php

class CodesPostaux extends BaseObject {
  
  public static $_tables = array(
    array(
      "name" => "codes_postaux",
      "key" => "code_postal",
      "fields" => array(
        "insee" => 0,
        "code_postal" => 0,
        "commune" => '',
        "departement" => ''
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

?>
