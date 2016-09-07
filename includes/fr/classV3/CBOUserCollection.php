<?php

class BOUserCollection {

  private $db = null;
  private $existsInDB = false;
  private $altered = false;

  private $id = 0;
  private $name = "";
  private $login = "";
  private $pass = "";
  private $rank = 1;
  private $email = "";
  private $active = 1;
  private $timestamp = 0;

  // fields w/ default values in table: usefull to load/save
  private static $fields = array(
    "id" => 0,
    "name" => "",
    "login" => "",
    "pass" => "",
    "rank" => 1,
    "email" => "",
    "active" => 1,
    "timestamp" => 0
  );

  // user functionalities permissions
	private $permissionFields = array(
		"id_user" => 0,
		"id_functionality" => 0,
		"permissions" => 0
  );
  
  private $permissions = array();
  private $permissionCount = 0;
  
  public static function getUsers () {
    $db = DBHandle::get_instance();
    $sql = "";
    if (!empty($sql)) $sql = " where ".$sql;
    $res = $db->query("SELECT `".implode("`,`", array_keys(self::$fields))."` FROM `bo_users` ORDER BY `name` DESC", __FILE__, __LINE__);

    $users = array();
    while ($user = $db->fetchAssoc($res))
      $users[] = $user;

    return $users;
  }

  public static function delete ($userId) {
    $db = DBHandle::get_instance();
    $db->query("DELETE FROM `bo_users` WHERE `id` = '".$userId."'", __FILE__, __LINE__);
    $db->query("DELETE FROM `bo_users_permissions` WHERE `id_user` = '".$userId."'", __FILE__, __LINE__);
  }

  public function __construct($id = null) {
    $this->db = DBHandle::get_instance();
    if (!empty($id)) {
      $this->id = $id;
      $this->load();
    }
    else
      $this->id = null;
  }

  public function __destruct() {
    /*if ($this->altered) {
    $this->save();
    }*/
  }

  public function __set($name, $value) {
    $this->$name = $value;
    $this->altered = true;
  }

  public function __get($name) {
    if (isset($this->$name)) {
      return $this->$name;
    }
  }

  private function generateID() {
    do {
      $id = mt_rand(1, 65535);
      $res = $this->db->query("SELECT `id` FROM bo_users WHERE `id` = '".$id."'", __FILE__, __LINE__);
    }
    while ($this->db->numrows($res, __FILE__, __LINE__) == 1);

    $this->id = $id;
    $this->altered = true;
  }

  public function create() {
    $this->existsInDB = false;

    foreach (self::$fields as $fieldName => $dftValue) {
      $this->$fieldName = $dftValue;
    }
    $this->permissions = array();
    $this->permissionCount = 0;
    $this->altered = false;
  }

  public function load() {
    $res = $this->db->query("SELECT `".implode("`,`", array_keys(self::$fields))."` FROM `bo_users` WHERE `id` = '".$this->id."'", __FILE__, __LINE__);
    if ($this->db->numrows($res, __FILE__, __LINE__) == 1) {
      $data = $this->db->fetchAssoc($res);
      foreach (self::$fields as $fieldName => $dftValue) {
        $this->$fieldName = isset($data[$fieldName]) ? $data[$fieldName] : $dftValue;
      }

      $res = $this->db->query("SELECT `".implode("`,`" , array_keys($this->permissionFields))."` FROM `bo_users_permissions` WHERE `id_user` = '".$this->id."'", __FILE__, __LINE__);
      while ($permission = $this->db->fetchAssoc($res)) {
        $this->permissions[] = $permission;
      }

      $this->permissionCount = count($this->permissions);
      $this->existsInDB = true;
    }
    else
      $this->existsInDB = false;

    $this->altered = false;
    return $this->existsInDB;
  }

  public function save() {
    $this->timestamp = time();

    $queries[] = "DELETE FROM `bo_users_permissions` WHERE `id_user` = '".$this->id."'";

    if ($this->existsInDB) {
      $fields = array();
      foreach (self::$fields as $fieldName => $dftValue) {
        $fields[] = "`".$fieldName."` = '".$this->db->escape($this->$fieldName)."'";
      }
      $queries[] = "UPDATE `bo_users` SET ".implode(",", $fields)." WHERE `id` = '".$this->id."'";
    }
    else {
      $this->create_time = time();
      if (empty($this->id)) $this->generateID();
      $fields = array();
      foreach (self::$fields as $fieldName => $dftValue) {
        $fields[] = "'".$this->db->escape($this->$fieldName)."'";
      }
      $queries[] = "INSERT INTO `bo_users` (`".implode("`,`", array_keys(self::$fields))."`) VALUES(".implode(",", $fields).")";
    }

    foreach ($this->permissions as $permission) {
      $fields = array();
      foreach ($this->permissionFields as $field => $aa) {
        $fields[] = "'".$this->db->escape($permission[$field])."'";
      }
      $queries[] = "INSERT INTO `bo_users_permissions` (`".implode("`,`", array_keys($this->permissionFields))."`) VALUES(".implode(",", $fields).")";
    }

    foreach ($queries as $query) {
      $this->db->query($query, __FILE__, __LINE__, false);
    }

    $this->existsInDB = true;
  }

  public function hasFunctionnality($functionnality) {

    foreach ($this->permissionFields as $permissionArray)
      if($functionnality == $permissionArray['id_functionality'])
        return true;
      else
        return false;

  }

  public function hasPermission($permission) {
    $permission;
  }
  
  public function addPermission($functionalities, $permissions) {
  }
  
  public function removePermission($functionalities, $permissions) {
  }
  
  public function getItemsAsString() {
    $itemString = "";
    switch ($this->type) {
      case "pdt" :
        $i = 0;
        foreach($this->items as $item) {
          $itemString .= $item['categoryID'].",".$item['productID'].($i++ < $this->itemCount-1 ? "|" : "");
        }
        break;
      case "cat" :
        foreach($this->items as $item) {
          $itemString .= $item['categoryID'].($i++ < $this->itemCount-1 ? "|" : "");
        }
        break;
      default : break;
    }
    return $itemString;
  }

  public function getItemsAsJSON() {
    $o = array();
    $itemsInfos = array();
    switch ($this->type) {
      case "pdt" :
        $pdtIDs = array();
        foreach($this->items as $item)
        $pdtIDs[] = $item['productID'];
        if (!empty($pdtIDs)) {
          $res = $this->db->query("
          SELECT pfr.id, pfr.name, pfr.ref_name, pf.idFamily as catID
          FROM products_fr pfr, products_families pf
          WHERE pfr.id = pf.idProduct AND pfr.id IN (".implode(",", $pdtIDs).")");
          while ($pdt = $this->db->fetchAssoc($res)) $itemsInfos[$pdt['id']] = $pdt;
          foreach($this->items as $item) $o[] = $itemsInfos[$item['productID']];
        }
        break;
      case "cat" :
        $catIDs = array();
        foreach($this->items as $item)
          $catIDs[] = $item['categoryID'];
        if (!empty($catIDs)) {
          $res = $this->db->query("select id, name, ref_name from families_fr where id in (".implode(",", $catIDs).")");
          while ($cat = $this->db->fetchAssoc($res)) $itemsInfos[$cat['id']] = $cat;
          foreach($this->items as $item) $o[] = $itemsInfos[$item['categoryID']];
        }
        break;
      default : break;
    }

    mb_convert_variables("UTF-8","ISO-8859-1", $o);
    return json_encode($o);

  }

  public function updateItems($itemString) {
    $ret = false;
    switch ($this->type) {
      case "pdt" :
        $this->clearItems();
        $asic = explode("|", $itemString);
        $order = 1;
        foreach($asic as $asi) {
          $asi = explode(",", $asi); // category and items
          $asi[0] = (int)($asi[0]);
          if (!$asi[0]) continue;
          $nbi = count($asi)-1;
          if ($nbi < 1) continue;
          for ($i = 1; $i <= $nbi; $i++) {
            $asi[$i] = (int)$asi[$i];
            if ($asi[$i]) {
              $this->items[] = array(
                'miniStoreID' => $this->id,
                'categoryID' => $asi[0],
                'productID' => $asi[$i],
                'order' => $order);
              $order++;
            }
          }
        }
        $ret = true;
        break;

      case "cat" :
        $this->clearItems();
        $asc = explode("|", $itemString);
        $order = 1;
        foreach($asc as $catID) {
          $catID = (int)$catID;
          if ($catID) {
            $this->items[] = array(
            'miniStoreID' => $this->id,
            'categoryID' => $catID,
            'productID' => 0,
            'order' => $order);
            $order++;
          }
        }
        $ret = true;
        break;

      default : break;
    }

    $this->itemCount = count($this->items);

    return $ret;
  }

  public function clearItems() {
    $this->items = array();
    $this->itemCount = 0;
    $this->altered = true;
  }

  public static function getUsersFromFunctionnality($functionnality, $perms){

    $users = self::getUsers();

    $retUsers = array();
    
    foreach ($users as $key => $user) {

      $objUser = new BOUser($user['id']);

      $testPerm = new BOUserPermissionCollection($objUser);

      if($testPerm->has($functionnality, $perms))
              $retUsers[] = $user;

    }

    return $retUsers;
  }

}

?>