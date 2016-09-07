<?php

class RefAttributeVirtualCollection extends BaseCollection {

  protected $childObjectName = "RefAttributeVirtual";
  protected $keyName = "categoryId";
  
  public function update() {
    $childrenToProcess = array_merge($this->collection, $this->collectionBasket);
    foreach($childrenToProcess as $child)
      $child->get_values()->update(); // update the attribute value collection
    parent::update();
  }
  
  public function remove($childToRemove) {
    $childToRemove = parent::remove($childToRemove);
    if ($childToRemove !== false)
      $childToRemove->get_values()->clear(); // clean the attribute value collection
    return $childToRemove;
  }
  
  public function clear() {
    parent::clear();
    foreach ($this->collectionBasket as $childToRemove) // clean all the attributes values collection
      $childToRemove->get_values()->clear();
  }
  
}
