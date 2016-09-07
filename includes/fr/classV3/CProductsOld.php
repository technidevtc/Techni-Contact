<?php

class ProductsOld extends BaseObject {
  
  protected $IdMax = 999999999;
  
  public static $_tables = array(
    array(
      "name" => "products",
      "key" => "id",
      "fields" => array(
        "id" => 0,
        "idAdvertiser" => 0,
        "idTC" => 0,
        "timestamp" => 0,
        "cg" => 0,
        "ci" => 0,
        "cc" => 0,
        "refSupplier" => "",
        "price" => "",
        "price2" => "",
        "unite" => 0,
        "marge" => 0,
        "idTVA" => 0,
        "contrainteProduit" => 0,
        "tauxRemise" => "",
        "cat3_si" => array(),
        "adv_si" => "",
        "ean" => "",
        "warranty" => "",
        "shipping_fee" => "",
        "video_code" => "",
        "docs" => array()
      )
    )
  );

    protected static $_linkedTables = array(
      array(
          "name" => "products_families",
          "key" => "idProduct",
          "fields" => array(
              "idProduct" => 0,
              "idFamily" => 0
              )
      )
  );


  public static function get() {
    $args = func_get_args();
    return parent::get(self::$_tables, $args);
  }

  public static function getProductsNumbersFromFamily($idFamily) {
    $db = DBHandle::get_instance();

    $query = 'select count(p.id) as nb_products from products p
      left join products_families pf on pf.idProduct = p.id
      left join products_fr pfr on pfr.id = p.id
      WHERE pf.idFamily = '.$db->escape($idFamily).' AND pfr.active = 1';
    
    $res = $db->query($query);
    $ret = $db->fetchAssoc($res);
    return $ret['nb_products'];
  }

  public static function getProductsNumbersFromAdvertiser($idAdvertiser) {
    $db = DBHandle::get_instance();

    $query = 'select count(p.id) as nb_products from products p
      left join products_families pf on pf.idProduct = p.id
      left join products_fr pfr on pfr.id = p.id
      WHERE p.idAdvertiser = '.$db->escape($idAdvertiser).' AND pfr.active = 1';
    
    $res = $db->query($query);
    $ret = $db->fetchAssoc($res);
    return $ret['nb_products'];
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
    $this->fields["cat3_si"] = unserialize($this->fields["cat3_si"]);
    $this->fields["docs"] = unserialize($this->fields["docs"]);
    return $r;
  }
  
  public function save() {
    $this->fields["cat3_si"] = serialize($this->fields["cat3_si"]);
    $this->fields["docs"] = serialize($this->fields["docs"]);
    $r = parent::save();
    $this->fields["cat3_si"] = unserialize($this->fields["cat3_si"]);
    $this->fields["docs"] = unserialize($this->fields["docs"]);
    return $r;
  }
  
}

?>
