<?php

class BOUserPermissionCollection extends BaseCollection {

  protected $childObjectName = "BOUserPermission";
  protected $keyName = "id_user";
  
  public function __construct($args) {
    parent::__construct($args);
  }
	
  public function __destruct() {}
  
  public function add($fnt, $permissions) {
    if (!is_numeric($fnt)) {
      $f = BOFunctionality::get("name='".$fnt."'");
      $fnt = (!empty($f)) ? $f[0]["id"] : -1;
    }
    if ($fnt >= 0) {
      $permLineFound = false;
      foreach($this->collection as &$perm) {
        if ($perm->id_functionality == $fnt) {
          $permLineFound = true;
          $rightList = str_split($permissions);
          foreach($rightList as $right) {
            if (stripos($perm->permissions, $right) === false) {
              $perm->permissions .= $right;
            }
          }
        }
      }
      unset($perm);
      if (!$permLineFound) {
        parent::add(array(
          "id_functionality" => $fnt,
          "permissions" => $permissions
        ));
      }
    }
    return $this;
  }
  
  public function remove($fnt, $permissions) {
    if (!is_numeric($fnt)) {
      $f = BOFunctionality::get("name='".$fnt."'");
      $fnt = (!empty($f)) ? $f[0]["id"] : -1;
    }
    if ($fnt >= 0) {
      foreach($this->collection as &$perm) {
        if ($perm->id_functionality == $fnt) {
          $rightList = str_split($permissions);
          foreach($rightList as $right)
            $perm->permissions = str_replace($right, "", $perm->permissions);
         if ($perm->permissions == "")
            parent::remove($perm);
        }
      }
      unset($perm);
    }
    
    return $this;
  }
  
  // convenient alias of parent function "contains"
  public function has($fnt, $permissions) {
    $hasPermissions = false;
    if (!is_numeric($fnt)) {
      $f = BOFunctionality::get("name='".$fnt."'");
      $fnt = (!empty($f)) ? $f[0]["id"] : -1;
    }
    if ($fnt >= 0) {
      foreach($this->collection as &$perm) {
        if ($perm->id_functionality == $fnt) {
          $hasPermissions = true;
          $rightList = str_split($permissions);
          foreach($rightList as $right)
            $hasPermissions &= stripos($perm->permissions, $right) !== false;
        }
      }
      unset($perm);
    }
    
    return $hasPermissions;
  }
  
}

?>