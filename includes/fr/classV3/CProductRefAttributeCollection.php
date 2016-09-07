<?php

class ProductRefAttributeCollection extends RefAttributeCollection {
  
  protected $childObjectName = "ProductRefAttribute";
  protected $keyName = "productId";
  protected $joinTableName = "products_ref_attributes";
  protected $joinKeyName = "attributeId";
  
  /*public function update() {
    $altered = $this->altered;
    parent::update(); // doesn't save children
    $childrenToProcess = array_merge($this->collection, $this->childrenToRemove);
    if ($altered) {
      foreach($childrenToProcess as $child) {
        $res = $this->db->query("SELECT count(`".$this->keyName."`) FROM `".$this->joinTableName."` WHERE `".$this->joinKeyName."` = '".$child->id."'", __FILE__, __LINE__);
        list($count) = $this->db->fetch($res);
        $child->usedCount = $count;
      }
    }
    foreach($this->collection as $child) // save children to update any usedCount or attribute value collection change
      $child->save();
    foreach($this->childrenToRemove as $child) { // same for removed children, and also delete database record if usedCount = 0 along with linked attributes values
      if ($child->usedCount == 0) {
        $child->get_values()->update(); // directly update the attributes values to avoid one useless query
        $this->childObjectFuncs["delete"]($child->id);
      }
      else
        $child->save();
    }
    $this->childrenToRemove = array();
    $this->childrenToRemoveIndex = array();
  }
  
  public function remove($childToRemove) { // clean the attribute value collection
    $childToRemove = parent::remove($childToRemove);
    if ($childToRemove !== false)
      $childToRemove->get_values()->clear();
    return $childToRemove;
  }
  
  public function clear() {
    parent::clear();
    foreach ($this->childrenToRemove as $childToRemove)
      $childToRemove->get_values()->clear();
  }*/
  
}

?>