<?php

class RefAttributeValue extends BaseObject {

	protected $IdMax = 0xffffffff;
  
  public static $_tables = array(
    array(
      "name" => "ref_attributes_values",
      "key" => "id",
      "fields" => array(
        "id" => 0,
        "attributeId" => "",
        "value" => "",
        "order" => 0,
        "usedCount" => 0
      )
    )
  );

  public static function get($args = null) {
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
  
}
