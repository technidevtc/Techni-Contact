<?php

class BOFunctionality extends BaseObject {

	protected $IdMax = 65535;
  
  public static $_tables = array(
    array(
      "name" => "bo_functionalities",
      "key" => "id",
      "fields" => array(
        "id" => 0,
        "name" => "",
        "ref_name" => "",
        "desc" => ""
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
  
}

?>