<?php

abstract class BaseObject {

  protected $parent;
  protected $keyName = null;
  protected $db = null;
  protected $existsInDB = false;
  protected $altered = false;
  protected $tables;
  protected $fields;
  
  protected $id;
  
  protected static function get ($tables, $arg_list) {
    $db = DBHandle::get_instance();
    $table = $tables[0];
    
    if (!is_array($arg_list))
      $arg_list = array($arg_list);
    
    $filters = array();
    $orders = array();
    $limits = array();
    $joins = array();
    $selectedFieldsOK = array();
    for ($i = 0, $arg_count = count($arg_list); $i < $arg_count; $i++) {
      if (preg_match("/inner join (\S+)\s+on\s+(\S+)/i", $arg_list[$i], $matches)) { // inner join (first to avoid any wrong subsequent preg_match test)
        $joins[] = "INNER JOIN `".$matches[1]."` ON `".$matches[1]."`.`".$matches[2]."` = "."`".$table["name"]."`.`".$table["key"]."`";
      }
      if (preg_match("/((\S+)\.)?([^\s\.]+)\s*(!=|<>|>=|<=|=|>|<| like | not like )\s*(.+)/", $arg_list[$i], $matches)) { // arg is where x OPERATOR a
        if ($matches[2])
          $filters[] = "`".$matches[2]."`.`".$matches[3]."` ".strtoupper($matches[4])." ".$matches[5];
        elseif (isset($table["fields"][$matches[3]]))
          $filters[] = "`".$table["name"]."`.`".$matches[3]."` ".strtoupper($matches[4])." ".$matches[5];
      }
      elseif (preg_match("/(\S*)\s+(in|not in)\s+(\(.+\))/i", $arg_list[$i], $matches)) { // arg is where x in (a, b, ..)
        if (isset($table["fields"][$matches[1]]))
          $filters[] = "`".$table["name"]."`.`".$matches[1]."` ".strtoupper($matches[2])." ".$matches[3];
      }
      elseif (preg_match("/order by (((\S+)\.)?([^\s\.]+)\s+([^\s]+))+/i", $arg_list[$i], $matches)) { // arg is order by x
        if ($matches[3])
          $orders[] = "`".$matches[3]."`.`".$matches[4]."` ".(!strcasecmp($matches[5],"desc") ? "DESC" : "ASC");
        elseif (isset($table["fields"][$matches[4]]))
          $orders[] = "`".$table["name"]."`.`".$matches[4]."` ".(!strcasecmp($matches[5],"desc") ? "DESC" : "ASC");
      }
      elseif (preg_match("/limit \s*(\d+)(\s*,\s*(\d+))?/i", $arg_list[$i], $matches)) { // arg is limit x,y
        $limits[] = $matches[1];
        if (isset($matches[3]))
          $limits[] = $matches[3];
      }
      else {
        $selectedFields = explode(",", $arg_list[$i]);

        foreach($selectedFields as $selectedField) {
          $selectedField = trim($selectedField);
          if (isset($table["fields"][$selectedField]))
            $selectedFieldsOK[] = $selectedField;
        }
      }
    }
    
    $joins = implode(" ", $joins);
    if (!empty($joins))
      $joins = " ".$joins;
    
    $filters = implode(" AND ", $filters);
    if (!empty($filters))
      $filters = " WHERE ".$filters;
    
    $orders = implode(",", $orders);
    if (!empty($orders))
      $orders = " ORDER BY ".$orders;
    
    $limits = implode(",", $limits);
    if (!empty($limits))
      $limits = " LIMIT ".$limits;
      
    if (empty($selectedFieldsOK)){
      $selectedFieldsOK = array_keys($table["fields"]);
      if($table[0]['join'] == 'inner')
        $selectedFields2OK = array_keys($table[0]["fields"]);
    }
    $selectedFieldsOK = "`".$table["name"]."`.`".implode("`,`".$table["name"]."`.`", $selectedFieldsOK)."`";
    if($selectedFields2OK)
      $selectedFieldsOK .= " , `".$table[0]["name"]."`.`".implode("`,`".$table[0]["name"]."`.`", $selectedFields2OK)."`";
    
    $res = $db->query("SELECT ".$selectedFieldsOK." FROM `".$table["name"]."`".$joins.$filters.$orders.$limits, __FILE__, __LINE__);
    
    $rows = array();
    while ($row = $db->fetchAssoc($res))
      $rows[] = $row;
    
    return $rows;
  }
  
  protected static function delete($id, $tables) {
    $db = DBHandle::get_instance();
    $table = $tables[0];
    
    $db->query("DELETE FROM `".$table["name"]."` WHERE `".$table["key"]."` = '".$db->escape($id)."'", __FILE__, __LINE__);
    
    return $db->affected();
  }
  
  public function __construct($args = null) {
    $this->db = DBHandle::get_instance();
    $this->keyName = $this->tables[0]["key"];
    if (is_string($args) || is_numeric($args)) { // simple id to load from the DB
      $this->id = $args;
      $this->load();
    }
    else {
      $this->reset();
      if (!empty($args)) {
        $this->setData($args); // if args is an array, object vars will be set
        if (isset($args[$this->keyName])) { // id was set, the array was a db source
          $this->id = $args[$this->keyName];
          $this->existsInDB = true;
          $this->altered = false;
        }
        else {
          $this->generateId();
          if (isset($this->fields["create_time"]) && !isset($args["create_time"]))
            $this->fields["create_time"] = time();
        }
      }
      else {
        $this->generateId();
        if (isset($this->fields["create_time"]) && !isset($args["create_time"]))
          $this->fields["create_time"] = time();
      }
    }
  }
  
  public function __destruct() {
    /*if ($this->altered) {
      $this->save();
    }*/
  }
  
  public function __set($name, $value) {
    if (isset($this->fields[$name]) && $this->fields[$name] != $value) {
      $this->fields[$name] = $value;
      $this->altered = true;
    }
  }

  public function __get($name) {
    if (isset($this->fields[$name]))
      return $this->fields[$name];
    else return null;
  }
  
  public function setParent($parent) { $this->parent = $parent; }
  public function getParent() { return $this->parent; }
  
  public function existsInDB() {
    return $this->existsInDB;
  }
  
  public function isAltered() {
    return $this->altered;
  }
  
  // get data as an array
  public function getData($fields = null) {
    if (!isset($fields))
      return $this->fields;
    elseif (is_string($fields))
      return array_intersect_key($this->fields, array_flip(preg_split("/\s*,\s*/", $fields)));
    elseif(is_array($fields))
      return array_intersect_key($this->fields, array_flip($fields));
  }
  
  // set fields using an array
  public function setData($fields) {
    if (is_string($fields)) {
      $fv = func_get_args();
      for($k=0, $l=count($fv); $k<$l-$l%2; $k+=2) { // parse pair of elements, ignore the last odd indexed one if not paired
        if (isset($this->fields[$fv[$k]]) && $this->fields[$fv[$k]] != $fv[$k+1]) {
          $this->fields[$fv[$k]] = $fv[$k+1];
          $this->altered = true;
        }
      }
    }
    elseif (is_array($fields)) {
      foreach($fields as $name => $value) {
        if (isset($this->fields[$name]) && $this->fields[$name] !== $value) {
          $this->fields[$name] = $value;
          $this->altered = true;
        }
      }
    }
  }
  
  // TODO set a old id var if the object exists in the DB, to be able to track the change
  public function generateId($table = null) { // 21/06/2011 OD : to generate id from any table
    $table = !empty ($table) ? $table : $this->tables;
    do {
      $id = mt_rand(1, $this->IdMax);
      $res = $this->db->query("SELECT ".$this->keyName." FROM ".$table[0]["name"]." WHERE ".$this->keyName." = ". $id, __FILE__, __LINE__);
    }
    while ($this->db->numrows($res, __FILE__, __LINE__) == 1);
    
    $this->id = $id;
    $this->fields[$this->keyName] = $this->id;
    $this->altered = true;
  }
  
  // init object vars to default value
  public function reset() {
    
    $this->existsInDB = false;
    $this->altered = false;
    
    // reset fields
    $this->fields = array();
    foreach ($this->tables as $table) {
//      if (isset($table["join"]))
//        unset($table["fields"][$table["key"]]);
      foreach($table["fields"] as $fieldName => $dftValue)
        $this->fields[$fieldName] = $dftValue;
    }
    
    $this->id = null;
  }
  
  public function create($data = null) {
    
    $this->reset();
    
    // array pushed as arg ? -> setting the new object properties with it
    if (!empty($data)) {
      $this->setData($data);
      // generate id if not present in the data
      if (!isset($data[$this->keyName]))
        $this->generateId();
      else
        $this->id = $data[$this->keyName];
      
      // set create_time if present as a field but not present in the array
      if (isset($this->fields["create_time"]) && !isset($data["create_time"]))
        $this->fields["create_time"] = time();
    }
    else { // no array pushed, generate an id
      $this->generateId();
      if (isset($this->fields["create_time"]))
        $this->fields["create_time"] = time();
    }
  }
  
  public function load($id = null) {
    if (isset($id))
      $this->id = $this->fields[$this->keyName] = $id;
    $tablesFields = array();
    $tablesJoins = array();
    $mainTable = $this->tables[0];
    foreach ($this->tables as $table) {
      if (isset($table["join"])) {
//        unset($table["fields"][$table["key"]]);
        $tablesJoins[] = strtoupper($table["join"])." JOIN `".$table["name"]."` ON `".$mainTable["name"]."`.`".$mainTable["key"]."` = `".$table["name"]."`.`".$table["key"]."`";
      }
      $tablesFields[] = "`".$table["name"]."`.`".implode("`,`".$table["name"]."`.`", array_keys($table["fields"]))."`";
    }
    $sql = "SELECT ".implode(",",$tablesFields)." FROM `".$mainTable["name"]."`\n".implode("\n",$tablesJoins)."\n WHERE `".$mainTable["name"]."`.`".$mainTable["key"]."` = '".$this->id."'";
    
    $res = $this->db->query($sql, __FILE__, __LINE__);
    if ($this->db->numrows($res, __FILE__, __LINE__) == 1) {
      $data = $this->db->fetchAssoc($res);
      foreach($this->tables as &$table)
        foreach($table["fields"] as $fieldName => $dftValue)
          $this->fields[$fieldName] = isset($data[$fieldName]) ? $data[$fieldName] : $dftValue;
      unset($table);
      $this->existsInDB = true;
    }
    else
      $this->existsInDB = false;
    
    $this->altered = false;
    return $this->existsInDB;
  }
  
  public function save() {
    if ($this->altered) {
      if (isset($this->fields["timestamp"]))
        $this->fields["timestamp"] = time();
      $fields = array();
      if ($this->existsInDB) {
        $tablesNames = array();
        $tablesFields = array();
        $tablesJoins = array();
        $mainTable = $this->tables[0];
        foreach ($this->tables as $table) {
          $tablesNames[] = "`".$table["name"]."`";
          //unset($table["fields"][$table["key"]]);
          if (isset($table["join"]))
            $tablesJoins[] = strtoupper($table["join"])." JOIN `".$table["name"]."` ON `".$mainTable["name"]."`.`".$mainTable["key"]."` = `".$table["name"]."`.`".$table["key"]."`";
          $tableFields = array();
          foreach ($table["fields"] as $fieldName => $dftValue)
            $tableFields[] = "`".$table["name"]."`.`".$fieldName."` = '".$this->db->escape($this->fields[$fieldName])."'";
          $tablesFields[] = implode(",",$tableFields);
        }
        $queries[] = "UPDATE `".$mainTable["name"]."` ".implode("\n",$tablesJoins)."\nSET ".implode(",",$tablesFields)."\nWHERE `".$mainTable["name"]."`.`".$mainTable["key"]."` = '".$this->id."'";
      }
      else {
        foreach ($this->tables as $table) {
          $tableFields = array();
          foreach ($table["fields"] as $fieldName => $dftValue)
            $tableFields[] = $fieldName==$table["key"] ? "'".$this->id."'" : "'".$this->db->escape($this->fields[$fieldName])."'";
          $queries[] = "INSERT INTO `".$table["name"]."`(`".implode("`,`",array_keys($table["fields"]))."`)\nVALUES(".implode(",",$tableFields).")";
        }
      }
      
//      pp($queries);
      
      foreach ($queries as $query)
        $this->db->query($query, __FILE__, __LINE__, false);
      
      $this->existsInDB = true;
      $this->altered = false;
    }
    
    return $this->existsInDB;
  }
  
}
