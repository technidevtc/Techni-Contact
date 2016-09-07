<?php

class ProductsFamiliesOld extends BaseObject {
  
  protected $IdMax = 999999999;
  protected $limitLevel1 = 11;



  public static $_tables = array(
    array(
      "name" => "products_families",
      "key" => "idProduct",
      "fields" => array(
        "idProduct" => 0,
        "idFamily" => 0,
        "orderFamily" => 0
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
