<?php

class MiniStore
{
  
  private $db = null;
  private $existsInDB = false;
  private $altered = false;
  
  private $id = 0;
  private $name = "";
  private $ref_name = "";
  private $fastdesc = "";
  private $desc = "";
  private $desc_listing = "";
  private $type = "";
  private $imageURL = "";
  private $create_time = 0;
  private $edit_time = 0;
  private $home = 0;
  private $espace_thematique = 0;
  private $active = 1;
  
  // Fields w/ default values in table Clients : usefull to load/save
  private static $fields = array(
    "id" => 0,
    "name" => "",
    "ref_name" => "",
    "fastdesc" => "",
    "desc" => "",
    "desc_listing" => "",
    "type" => "",
    "imageURL" => "",
    "create_time" => 0,
    "edit_time" => 0,
    "standalone" => 0,
    "home" => 0,
    "espace_thematique" => 0,
    "active" => 1);
    
  // Will containe the object fields data
  //private $data = null;
  
  // Saved items properties
  private $itemFields = array(
    "miniStoreID" => 0,
    "categoryID" => 0,
    "productID" => 0,
    "order" => 0);
  
  private $items = array();
  private $itemCount = 0;
  
  // Calculated properties
  private $fdpTTC = 0;
  
  public static function getMiniStores ($onlyActive = true, $withStandalone = false, $onlyHome = false) {
    $db = DBHandle::get_instance();
    $sql = "";
    if ($onlyActive) $sql .= " `active` = 1";
    if ($onlyHome) $sql .= ($onlyActive?" and ":"")." `home` != 0";
    if (!$withStandalone) $sql .= ($onlyHome||$onlyActive?" and ":"")." `standalone` = 0";
    if (!empty($sql)) $sql = " where ".$sql;
    $res = $db->query("select `".implode("`,`", array_keys(self::$fields))."` from mini_stores".$sql." ORDER BY ".($onlyHome?'home, ':'')."edit_time DESC", __FILE__, __LINE__);
    
    $miniStores = array();
    while ($miniStore = $db->fetchAssoc($res))
      $miniStores[] = $miniStore;
    
    return $miniStores;
  }
  
  public static function getMiniStoresByCatIDs ($catIDs, $onlyActive = true, $withStandalone = false, $withEspaceThem = false) {
    $db = DBHandle::get_instance();
    
    $res = $db->query("select miniStoreID from mini_stores_application where categoryID in ('".implode("','",$catIDs)."')", __FILE__, __LINE__);
    $ids = array();
    while ($row = $db->fetch($res))
      $ids[$row[0]] = true;
    
    $sql = "";
    if ($onlyActive) $sql = " `active` = 1";
    if ($withEspaceThem) $sql .= ($withEspaceThem?" and ":"")." `espace_thematique` = 1";
    if (!$withStandalone) $sql .= ($onlyActive||$withEspaceThem?" and ":"")." `standalone` = 0";
    $res = $db->query("select `".implode("`,`", array_keys(self::$fields))."` from mini_stores where ".$sql.(empty($sql)?"":" and ")."id in ('".implode("','",array_keys($ids))."') ORDER BY edit_time DESC", __FILE__, __LINE__);
    $miniStores = array();
    while ($miniStore = $db->fetchAssoc($res)) {
      $miniStore['url'] = URL."miniboutiques/".$miniStore['id']."-".$miniStore['ref_name'].".html";
      $miniStore['url'] = MiniStores::getUrl($miniStore['id'], $miniStore['ref_name']);
      $miniStore['pics'] = array(
        'home' => MiniStores::getPic($miniStore['id'], 'home'),
        'vignette' => MiniStores::getPic($miniStore['id'], 'vignette'),
        'espace' => MiniStores::getPic($miniStore['id'], 'espace')
      );
      $miniStores[] = $miniStore;
    }
    
    return $miniStores;
  }
  
  public static function delete ($miniStoreID) {
    $db = DBHandle::get_instance();
    $db->query("delete from mini_stores where id = '".$miniStoreID."'", __FILE__, __LINE__);
    $db->query("delete from mini_stores_application where miniStoreID = '".$miniStoreID."'", __FILE__, __LINE__);
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
      $id = mt_rand(1, 999999999);
      $res = $this->db->query("select id from mini_stores where id = ". $id, __FILE__, __LINE__);
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
    $this->items = array();
    $this->itemCount = 0;
    $this->altered = false;
  }
  
  public function load() {
    $res = $this->db->query("select `".implode("`,`", array_keys(self::$fields))."` from mini_stores where id = '".$this->id."'", __FILE__, __LINE__);
    if ($this->db->numrows($res, __FILE__, __LINE__) == 1) {
      $data = $this->db->fetchAssoc($res);
      foreach (self::$fields as $fieldName => $dftValue) {
        $this->$fieldName = isset($data[$fieldName]) ? $data[$fieldName] : $dftValue;
      }
      
      $res = $this->db->query("select `".implode("`,`" , array_keys($this->itemFields))."` from mini_stores_application where miniStoreID = '".$this->id."' ORDER BY `order`", __FILE__, __LINE__);
      while ($item = $this->db->fetchAssoc($res)) {
        $this->items[] = $item;
      }
      
      $this->itemCount = count($this->items);
      $this->existsInDB = true;
    }
    else
      $this->existsInDB = false;
    
    $this->altered = false;
    return $this->existsInDB;
  }
  
  public function save() {
    $this->edit_time = time();
    
    $queries[] = "delete from mini_stores_application where miniStoreID = '" . $this->id . "'";
    
    if ($this->existsInDB) {
      $fields = array();
      foreach (self::$fields as $fieldName => $dftValue) {
        $fields[] = "`".$fieldName."` = '".$this->db->escape($this->$fieldName)."'";
      }
      $queries[] = "update mini_stores set ".implode(",", $fields)." where id = '".$this->id."'";
    }
    else {
      $this->create_time = time();
      if (empty($this->id)) $this->generateID();
      $fields = array();
      foreach (self::$fields as $fieldName => $dftValue) {
        $fields[] = "'".$this->db->escape($this->$fieldName)."'";
      }
      $queries[] = "insert into mini_stores (`".implode("`,`", array_keys(self::$fields))."`) values(".implode(",", $fields).")";
    }
    
    foreach ($this->items as $item) {
      $fields = array();
      foreach ($this->itemFields as $field => $aa) {
        $fields[] = "'".$this->db->escape($item[$field])."'";
      }
      $queries[] = "insert into mini_stores_application (`".implode("`,`", array_keys($this->itemFields))."`) values(".implode(",", $fields).")";
    }
    
    foreach ($queries as $query) {
      $this->db->query($query, __FILE__, __LINE__, false);
    }
    
    $this->existsInDB = true;
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
  
  
}
