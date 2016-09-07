<?php
/*================================================================/

 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com

 Auteur : OD pour Hook Network SARL - http://www.hook-network.com
 Date de création : 31 mai 2011

 Fichier : /includes/classV3/CMktCampaign.php
 Description : Classe de gestion des campagnes marketing

/=================================================================*/

class MktCampaign extends BaseObject {

  protected $IdMax = 0xffffffff;
  protected $db = null;
  protected static $_tables = array(
      array(
          "name" => "mkt_campaigns",
          "key" => "id",
          "fields" => array(
              "id" => 0,
              "id_mkt_campaigns_type" => "",
              "nom" => 0,
              "timestamp" => 0,
              )
      ),
      
  );

  protected static $_linkedTables = array(
      array(
          "name" => "mkt_campaigns_type",
          "key" => "id",
          "fields" => array(
              "id" => 0,
              "type" => ""
              )
      )
  );

  public $exists = false;
  public $nb;

  public static function get() {
    $args = func_get_args();
    $args = $args[0];
    return BaseObject::get(self::$_tables, $args);
  }

  public function getFields($id = null) {
    $this->fields['id'] = preg_match("/^[1-9]{1}[0-9]{0,9}$/", $id) ? $id : $this->id;
    $ret = $this->fields;
    return $ret;
  }

  public static function delete($id) {
    return BaseObject::delete($id, self::$_tables);
  }

  public static function getAllTypes(){
    return BaseObject::get(self::$_linkedTables, $args);
  }

  public static function getTypeName($id){
    $obj = BaseObject::get(self::$_linkedTables, array('id = '.$id));
    return $obj[0]['type'];
  }

  public static function getCount($col = null, $args = null){
    $db = DBHandle::get_instance();
    $args = !empty ($args) ? ' WHERE '.$args : '';
    $col = !empty ($col) ? $col : 'id';
    $query = "SELECT count(".$col.") FROM ".self::$_tables[0]['name'].$args;
    $res = $db->query($query);
    $ret = $db->fetch($res);
    return $ret[0];
  }

  public function __construct($args = null) {
    $this->tables = self::$_tables;
    parent::__construct($args);
    if ($this->existsInDB)
      $this->exists = true;
  }

  public function create($data = null) {
    parent::create($data);
    $this->built = false;
  }

  public function createType($type) {
    $id = parent::generateId(self::$_linkedTables);
    $type = trim($type);
    if(!empty($type)){
      $data['id'] = $id;
      $data['type'] = $type;
    }

    $this->tables = self::$_linkedTables;
    parent::create($data);
    return parent::save() ? true : false ;
  }

  public function load($args = null) {
    $r = parent::load($args);
//    $this->fields["data"] = unserialize($this->fields["data"]);
    $this->built = false;
    return $r;
  }

  public function save() {
//    $this->fields["data"] = serialize($this->fields["data"]);
    $r = parent::save();
//    $this->fields["data"] = unserialize($this->fields["data"]);
    return $r;
  }


}

?>
