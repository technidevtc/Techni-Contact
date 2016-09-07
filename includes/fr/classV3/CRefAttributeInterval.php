<?php

class RefAttributeInterval extends BaseObject {

  protected $IdMax = 0xffffffff;
  protected $values = null;

  public static $_tables = array(
    array(
      "name" => "ref_attributes_intervals",
      "key" => "id",//categoryId
      "fields" => array(
        "id" => 0,
        "categoryId" => 0,
        "attributeId" => 0,
        "name" => "",
        "start_from" => 0,
        "goes_to" => 0,
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

  public function get_interval_values($args = null) { // lazy loading
    if (!isset($this->values))
      $this->values = new RefAttributeValueCollection($args);
    return $this->values;
  }

}
