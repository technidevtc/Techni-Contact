<?php

class ProductNotation extends BaseObject {

  protected $IdMax = 999999999;

  public static $_tables = array(
    array(
      "name" => "products_notations",
      "key" => "id",
      "fields" => array(
        "id" => 0,
        "id_product" => 0,
        "id_client" => 0,
        "id_commande" => 0,
        "token" => 0,
        "timestamp" => 0,
        "note" => 0,
        "comment" => '',
        "anonymous" => 0,
        "inactive" => 0
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

?>