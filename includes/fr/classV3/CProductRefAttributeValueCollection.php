<?php

class ProductRefAttributeValueCollection extends RefAttributeValueCollection {
  
  //protected $childObjectName = "ProductRefAttributeValue";
  protected $keyName = "productId";
  protected $joinTableName = "products_ref_attributes_values";
  protected $joinKeyName = "attributeValueId";
  
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
    foreach($this->collection as $child) // save children to update any usedCount change
      $child->save();
    foreach($this->childrenToRemove as $child) // same for removed children, and also delete database record if usedCount = 0
      if ($child->usedCount == 0)
        $this->childObjectFuncs["delete"]($child->id);
      else
        $child->save();
    $this->childrenToRemove = array();
    $this->childrenToRemoveIndex = array();
  }*/
  
}

?>