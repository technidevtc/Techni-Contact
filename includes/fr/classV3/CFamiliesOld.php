<?php

class FamiliesOld extends BaseObject {
  
  protected $IdMax = 999999999;
  protected $limitLevel1 = 11;



  public static $_tables = array(
    array(
      "name" => "families",
      "key" => "id",
      "fields" => array(
        "id" => 0,
        "idParent" => 0,
        "pdt_overwrite" => ""
        ),
        array(
              "name" => "families_fr",
              "join" => "inner",
              "key" => "id",
              "fields" => array(
                  "id" => 0,
                  "name" => "",
                  "ref_name" => "",
                  "title" => "",
                  "meta_desc" => ""
              )
      )
    )
  );

//  protected static $_linkedTables = array(
//    array(
//        "name" => "families_fr",
//        "key" => "id",
//        "fields" => array(
//            "id" => 0,
//            "name" => "",
//            "ref_name" => "",
//            "title" => "",
//            "meta_desc" => ""
//            )
//      )
//  );
  
  public static function get() {
    $args = func_get_args();
    return BaseObject::get(self::$_tables, $args);
  }

  public static function getFamilyInfo($idFamily) {
    $args = array( 'inner join families_fr on id', 'families.id = '.$idFamily, 'ffr.name, ffr.ref_name, ffr.title, ffr.meta_desc');
    $family = BaseObject::get(self::$_tables, $args);
    return $family[0];
  }

  public static function getFamilyLevel($idFamily){
    $level = 0;
    while($idFamily == true && $level < 3){
      $idFamily = self::getFamilyParent($idFamily);
      $level++;
    }
    return $level;
  }

  public static function getFamilyParent($idFamily){
    $familyInfo = self::getFamilyInfo($idFamily);
    return $familyInfo['idParent'];
  }

  public static function getChildren($idFamily){
    $args = array( 'inner join families_fr on id', 'families.idParent = '.$idFamily, 'ffr.name, ffr.ref_name, ffr.title, ffr.meta_desc');
    $families = BaseObject::get(self::$_tables, $args);
    foreach($families as $family)
      $familyList[] = $family;
    return $familyList;
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
