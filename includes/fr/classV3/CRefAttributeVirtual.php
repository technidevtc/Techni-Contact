<?php

class RefAttributeVirtual extends BaseObject {

  protected $IdMax = 0xffffffff;
  protected $values = null;

  public static $_tables = array(
    array(
      "name" => "ref_attributes_virtual",
      "key" => "id",//categoryId
      "fields" => array(
        "id" => 0,
        "categoryId" => 0,
        "attributeId" => 0,
        "name" => "",
        "value" => '',
        "position" => 0
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

  public function save() {
//    if (isset($this->values))
//      $this->values->update();
    return parent::save();
  }

  public function get_values($args = null) { // lazy loading
    if (!isset($this->values))
      $this->values = new RefAttributeVirtualCollection($args);
    return $this->values;
  }

  public function get_virtual_products_collection($args = null) { // lazy loading
    if (!isset($this->values))
      $this->values = new RefAttributeVirtualProductsCollection($args);
    return $this->values;
  }

}
