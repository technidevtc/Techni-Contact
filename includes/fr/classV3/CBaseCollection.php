<?php

// BaseCollection has now three main behaviors :
// - default one when keyName is not set, get every single record of the childObject DB table 
//  -> updating will save all the children in the DB
// - when keyName and keyValue are set, get every record filtered by keyName = keyValue
//  -> updating will save all the children in the DB with the "filter" field keyName set to keyValue
// - when joinTableName is set, joinKeyName, keyName and keyValue must also be set
//  -> a join between the joinTableName DB table and the childObject DB table will be performed, using 
//     joinKeyName = childObject's keyName AND joinTableName.keyName = joinTableName.keyValue
//     updating will not trigger the save method of the children, the childObject DB table will not be updated,
//     this will only change the joint entries in joinTableName
abstract class BaseCollection implements IteratorAggregate {

  protected $altered = false;
  protected $db = null;
  
  protected $parent;
  protected $childObjectName;
  protected $childObjectFuncs;
  protected $childObjectKeyName;
  
  protected $keyName;
  protected $keyValue;
  protected $joinTableName;
  protected $joinKeyName;
  
  protected $collection = array();
  protected $collectionLength = 0;
  protected $collectionIndex = array();
  protected $collectionBasket = array();
  protected $collectionBasketIndex = array();
  
  public function __construct($args = null) {
    $this->db = DBHandle::get_instance();
    $this->childObjectTable = eval('return '.$this->childObjectName.'::$_tables;');
    $this->childObjectFuncs = array(
      //"get" => create_function('','$args = func_get_args(); return call_user_func_array("'.$this->childObjectName.'::get",$args);'),
      "get" => create_function('$args = null','return '.$this->childObjectName.'::get($args);'),
      "delete" => create_function('$id','return '.$this->childObjectName.'::delete($id);')
    );
    $this->childObjectKeyName = $this->childObjectTable["key"];
    $this->load($args);
  }
  
  public function __destruct() {
    /*if ($this->altered) {
      $this->save();
    }*/
  }

  public function getIterator() {
    return new ArrayIterator($this->collection);
  }
  
  public function getKeyName() { return $this->keyName; }
  public function getKeyValue() { return $this->keyValue; }
  public function setKeyValue($keyValue) { if (is_string($keyValue) || is_numeric($keyValue)) { $this->keyValue = $keyValue; return true; } return false;}
  public function getJoinTableName() { return $this->joinTableName; }
  public function getJoinKeyName() { return $this->joinKeyName; }
  
  public function load($args = null) {
    
    if (!is_array($args))
      $args = array($args);
    
    // using the static function of the child object
    if (isset($this->keyName)) {
      if (is_object($args[0])) {
        $this->parent = array_shift($args);
        $this->keyValue = $this->parent->id;
      }
      elseif (is_string($args[0]) || is_numeric($args[0])) {
        $this->keyValue = array_shift($args);
      }
      if (isset($this->keyValue)) {
        if (isset($this->joinTableName) && isset($this->joinKeyName))
          array_unshift($args, "inner join ".$this->joinTableName." on ".$this->joinKeyName, $this->joinTableName.".".$this->keyName." = ".$this->keyValue);
        else
          array_unshift($args, $this->keyName." = ".$this->keyValue);
        $rows = call_user_func_array(array($this->childObjectName, "get"), $args);
        //$rows = $this->childObjectFuncs["get"]($args);
      }
      else {
        $rows = array();
      }
    }
    else {
      $rows = call_user_func_array(array($this->childObjectName, "get"), $args);
      //$rows = $this->childObjectFuncs["get"]($args);
    }
    
    $this->reset();
    
    // using the array of results to init children objects without any additionnal sql query (see BaseObject)
    foreach($rows as &$row) {
      $child = new $this->childObjectName($row);
      $child->setParent($this);
      $this->collection[] = $child;
      $this->collectionIndex[$child->id] = $this->collectionLength;
      $this->collectionLength++;
    }
    unset($row);
    
    $this->altered = false;
    
    return $this;
  }
  
  public function update() {
    if (isset($this->joinTableName)) {
      if ($this->altered && isset($this->joinKeyName) && isset($this->keyName) && isset($this->keyValue)) {
        $queries[] = "DELETE FROM `".$this->joinTableName."` WHERE `".$this->keyName."` = '".$this->keyValue."'";
        foreach ($this->collection as $child)
          $queries[] = "INSERT INTO `".$this->joinTableName."` (`".$this->keyName."`,`".$this->joinKeyName."`) VALUES ('".$this->keyValue."','".$child->id."')";
        foreach ($queries as $query) {
          $this->db->query($query, __FILE__, __LINE__, false);
        }
        $this->collectionBasket = array();
        $this->collectionBasketIndex = array();
        $this->altered = false;
      }
    }
    else {
      if ($this->altered) {
        foreach ($this->collectionBasketIndex as $childId => $i)
          $this->childObjectFuncs["delete"]($childId);
        $this->collectionBasket = array();
        $this->collectionBasketIndex = array();
        $this->altered = false;
      }
      foreach ($this->collection as $child) {
        $child->save();
      }
    }
    
    return $this;
  }
  
  public function add($data) {
    $child = null;
    if (is_object($data) && get_class($data) === $this->childObjectName && !isset($this->collectionIndex[$data->id])) { // consider that it exists in the DB
      if (isset($this->joinTableName)) {
        if ($data->existsInDB()) // only add existing children in a join case
          $child = $data;
      }
      else {
        $child = $data;
        if (isset($this->keyName) && isset($this->keyValue))
          $child->{$this->keyName} = $this->keyValue;
      }
      if ($child) {
        $child->setParent($this);
        $this->collection[] = $child;
        end($this->collection);
        $this->collectionIndex[$child->id] = key($this->collection);
        $this->collectionLength++;
        $childToUnsetIndex = $this->collectionBasketIndex[$child->id];
        if (isset($childToUnsetIndex)) // a child with the same id was deleted, but we're adding a new object, so we simply unset the old one
          unset($this->collectionBasket[$childToUnsetIndex], $this->collectionBasketIndex[$child->id]);
        $this->altered = true;
      }
    }
    elseif ((is_string($data) || is_numeric($data)) && !isset($this->collectionIndex[$data])) {
      // adding the id of a deleted child is considered as undeleting it to avoid a sql query
      $childToUndeleteIndex = $this->collectionBasketIndex[$data];
      if (isset($childToUndeleteIndex)) {
        $this->collection[] = $this->collectionBasket[$childToUndeleteIndex];
        end($this->collection);
        $this->collectionIndex[$data] = key($this->collection);
        $this->collectionLength++;
        unset($this->collectionBasket[$childToUndeleteIndex], $this->collectionBasketIndex[$data]);
        $this->altered = true;
      }
      else { // new child
        $child = new $this->childObjectName($data);
        if (isset($this->joinTableName)) {
          if (!$child->existsInDB()) // unset the newly created child if it does not exist in the DB
            unset($child);
        }
        else {
          if (isset($this->keyName) && isset($this->keyValue))
            $child->{$this->keyName} = $this->keyValue;
        }
        if ($child) {
          $child->setParent($this);
          $this->collection[] = $child;
          end($this->collection);
          $this->collectionIndex[$child->id] = key($this->collection);
          $this->collectionLength++;
          $this->altered = true;
        }
      }
    }
    elseif (is_array($data) && !isset($data[$this->childObjectKeyName]) || !isset($this->collectionIndex[$data[$this->childObjectKeyName]])) {
      $child = new $this->childObjectName($data);
      if (isset($this->joinTableName)) {
        if (!$child->existsInDB()) // unset the newly created child if it does not exist in the DB
          unset($child);
      }
      else {
        if (isset($this->keyName) && isset($this->keyValue))
          $child->{$this->keyName} = $this->keyValue;
      }
      $child->setParent($this);
      $this->collection[] = $child;
      end($this->collection);
      $this->collectionIndex[$child->id] = key($this->collection);
      $this->collectionLength++;
      $childToUnsetIndex = $this->collectionBasketIndex[$child->id];
      if (isset($childToUnsetIndex)) // a child with the same id was deleted, but we're adding a new object, so we simply unset the old one
        unset($this->collectionBasket[$childToUnsetIndex], $this->collectionBasketIndex[$child->id]);
      $this->altered = true;
    }
    
    return $child;
  }
  
  public function remove($childToRemove) {
    $k = $this->index($childToRemove);
    $childToRemove = false;
    if ($k != -1) {
      $childToRemove = $this->item($k);
      unset($this->collection[$k], $this->collectionIndex[$childToRemove->id]);
      $this->collectionLength--;
      $this->collectionBasket[] = $childToRemove;
      end($this->collectionBasket);
      $this->collectionBasketIndex[$childToRemove->id] = key($this->collectionBasket);
      $this->altered = true;
    }
    
    return $childToRemove;
  }
  
  public function clear() {
    foreach($this->collection as $childToRemove) {
      $this->collectionBasket[] = $childToRemove;
      end($this->collectionBasket);
      $this->collectionBasketIndex[$childToRemove->id] = key($this->collectionBasket);
    }
    $this->collection = array();
    $this->collectionIndex = array();
    $this->collectionLength = 0;
    $this->altered = true;
  }
  
  public function reset() {
    // reseting all collection vars
    $this->collection = array();
    $this->collectionIndex = array();
    $this->collectionLength = 0;
    $this->collectionBasket = array();
    $this->collectionBasketIndex = array();
    $this->altered = false;
  }
  
  public function basket($i = null) { // list of deleted items
    if (isset($i))
      return isset($this->collectionBasket[$i]) ? $this->collectionBasket[$i] : false;
    else
      return new ArrayIterator($this->collectionBasket);
  }
  
  public function len() {
    return $this->collectionLength;
  }
  
  public function item($i = null) { // return the item by it's index
    if (isset($i))
      return isset($this->collection[$i]) ? $this->collection[$i] : false;
    else
      return new ArrayIterator($this->collection);
  }
  
  public function get($id) { // get an item by it's id
    return isset($this->collectionIndex[$id]) ? $this->collection[$this->collectionIndex[$id]] : null;
  }
  
  public function index($data) { // get the index of an item by it's id
    if (is_object($data) && get_class($data) === $this->childObjectName)
      $i = $this->collectionIndex[$data->id];
    elseif (is_string($data) || is_numeric($data))
      $i = $this->collectionIndex[$data];
    return isset($i) ? $i : -1;
  }
  
  public function findIndexBy($fieldName, $fieldValue) { // find index of an item by a value of one of it's field
    if (isset($this->childObjectTable[0]["fields"][$fieldName]))
      foreach ($this->collection as $k => $child)
        if ($child->$fieldName == $fieldValue)
          return $k;
    return -1;
  }
  
  public function findBy($fieldName, $fieldValue) { // find an item by a value of one of it's field
    if (isset($this->childObjectTable[0]["fields"][$fieldName]))
      foreach ($this->collection as $child)
        if ($child->$fieldName == $fieldValue)
          return $child;
    return null;
  }
  
  protected static $fieldNameToSort = null;
  
  protected static function sortNumAsc($a, $b) {
    $fn = BaseCollection::$fieldNameToSort;
    return $a->$fn > $b->$fn ? 1 : ($a->$fn < $b->$fn ? -1 : 0);
  }
  protected static function sortNumDesc($a, $b) {
    $fn = BaseCollection::$fieldNameToSort;
    return $a->$fn < $b->$fn ? 1 : ($a->$fn > $b->$fn ? -1 : 0);
  }
  protected static function sortStringAsc($a, $b) {
    $fn = BaseCollection::$fieldNameToSort;
    return strcasecmp($a->$fn, $b->$fn);
  }
  protected static function sortStringDesc($a, $b) {
    $fn = BaseCollection::$fieldNameToSort;
    return -strcasecmp($a->$fn, $b->$fn);
  }
  
  public function sort($fieldName, $way = "ASC", $flag = "string") {
    if (isset($this->childObjectTable[0]["fields"][$fieldName])) {
      BaseCollection::$fieldNameToSort = $fieldName;
      usort($this->collection, __CLASS__."::sort".($flag == "num" ? "Num" : "String").($way == "desc" ? "Desc" : "Asc"));
      $this->collectionIndex = array();
      foreach ($this->collection as $child)
        $this->collectionIndex[$child->id] = $child;
      return true;
    }
    else
      return false;
  }
  
}
