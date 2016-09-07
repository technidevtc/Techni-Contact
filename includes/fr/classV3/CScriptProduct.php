<?php

class ScriptProduct  {

  protected $IdMax = 999999999;
  protected static $_tables = array(
      array(
          "name" => "scripts_products",
          "key" => "id_relation",
          "fields" => array(
              "timestamp" => 0,
              "id_relation" => 0,
              "type_relation" => 0,
              "content" => ""
          )
      )
  );

  protected static $_relation_type = array(
      1 => 'famille 2',
      2 => 'famille 3',
      3 => 'partenaire',
  );

  protected $id_relation;
  protected $type_relation;
  protected $fields;
  protected $existsInDB = false;
  protected $altered = false;
  protected $db = null;

  public static function get($arg_list = null) {
    $db = DBHandle::get_instance();
    $table = array(
          "name" => "scripts_products",
          "key" => "id_relation",
          "fields" => array(
              "timestamp" => 0,
              "id_relation" => 0,
              "type_relation" => 0,
              "content" => ""
          )
      );

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

    if (empty($selectedFieldsOK))
      $selectedFieldsOK = array_keys($table["fields"]);
    $selectedFieldsOK = "`".$table["name"]."`.`".implode("`,`".$table["name"]."`.`", $selectedFieldsOK)."`";

    $res = $db->query("SELECT ".$selectedFieldsOK." FROM `".$table["name"]."`".$joins.$filters.$orders.$limits, __FILE__, __LINE__);

    $rows = array();
    while ($row = $db->fetchAssoc($res))
      $rows[] = $row;

    return $rows;
  }

  public static function getRelationTypeList(){
    return self::$_relation_type;
  }

  public  static function delete($id_relation, $type_relation) {
    $db = DBHandle::get_instance();

    $db->query("DELETE FROM `scripts_products` WHERE `id_relation` = '".$db->escape($id_relation)."' AND `type_relation` = '".$db->escape($type_relation)."'", __FILE__, __LINE__);

    return $db->affected();
  }

  public function __construct($id_relation, $type_relation) {
    $this->tables = self::$_tables;
    $this->db = DBHandle::get_instance();
    $this->keyName = $this->tables[0]["key"];
    $this->id_relation = $id_relation;
    $this->type_relation = $type_relation;
    
    $this->load();
    
    if ((!empty ($id_relation) && preg_match("/^[1-9]{1}[0-9]{0,8}$/", $id_relation)) && (!empty ($type_relation) && preg_match("/^[1-".count(self::$_relation_type)."]$/", $type_relation))){
      $args = array('id_relation = '.$id_relation, 'type_relation = '.$type_relation);
      $script = $this->get($args);
      $this->existsInDB = !empty ($script) ? true : false;

      if(!$this->existsInDB){
        $this->create(array('id_relation' => $id_relation, 'type_relation' => $type_relation));
      }
    }
    else {
      throw new Exception('Type d\'argument incorrect');
    }
  }

  public function create($data = null) {

    foreach ($this->tables[0]['fields'] as $fieldName => $dftValue) {
                $this->$fieldName = $dftValue;
        }

    if (!empty($data)) {
      $this->setData($data);

      $this->id_relation = $data['id_relation'];
      $this->type_relation = $data['type_relation'];

      $this->altered = true;

    }
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

  public function load(){

//    if (!empty($this->id_relation) && !empty($this->type_relation)){
//      $this->fields[$this->keyName] = $this->id_relation ? $this->id_relation : '';
//      $this->fields['type_relation'] = $this->type_relation ? $this->id_relation : '';
//    }
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
    $sql = "SELECT ".implode(",",$tablesFields)." FROM `".$mainTable["name"]."`\n".implode("\n",$tablesJoins)."\n WHERE `".$mainTable["name"]."`.`".$mainTable["key"]."` = '".$this->id_relation."' AND `".$mainTable["name"]."`.`type_relation` = '".$this->type_relation."'";
//    var_dump($sql);
    $res = $this->db->query($sql, __FILE__, __LINE__);
    if ($this->db->numrows($res, __FILE__, __LINE__) == 1) {
      $data = $this->db->fetchAssoc($res);
      foreach($this->tables as &$table)
        foreach($table["fields"] as $fieldName => $dftValue)
          $this->fields[$fieldName] = isset($data[$fieldName]) ? $data[$fieldName] : $dftValue;
      unset($table);
      $this->existsInDB = true;
    }
    else{
      foreach($this->tables as &$table)
        foreach($table["fields"] as $fieldName => $dftValue)
          $this->fields[$fieldName] =  $dftValue;
      $this->existsInDB = false;
    }

    $this->altered = false;
    return $this->existsInDB;
  }

  public function save() {
    
    if ($this->altered) {
      if (isset($this->fields["timestamp"]))
        $this->timestamp = $this->fields["timestamp"] = time();
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
        $queries[] = "UPDATE `".$mainTable["name"]."` ".implode("\n",$tablesJoins)."\nSET ".implode(",",$tablesFields)."\nWHERE `".$mainTable["name"]."`.`".$mainTable["key"]."` = '".$this->id_relation."'  AND `".$mainTable["name"]."`.`type_relation` = '".$this->type_relation."'";
      }
      else {
        foreach ($this->tables as $table) {
          $tableFields = array();
          foreach ($table["fields"] as $fieldName => $dftValue)
            $tableFields[] = $fieldName==$table["key"] ? "'".$this->id_relation."'" : "'".$this->db->escape($this->fields[$fieldName])."'";
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

?>
