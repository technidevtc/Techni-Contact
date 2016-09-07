<?php

class BOUserPermission extends BaseObject {

	protected $IdMax = 16777215;
  
  public static $_tables = array(
    array(
      "name" => "bo_users_permissions",
      "key" => "id",
      "fields" => array(
        "id" => 0,
        "id_user" => 0,
        "id_functionality" => 0,
        "permissions" => ""
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