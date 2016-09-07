<?php

class ProductRefAttribute extends RefAttribute {

  protected $productId;
  
  public function get_values() {
    if (!isset($this->values))
      $this->values = new ProductRefAttributeValueCollection($this->parent->getKeyValue());
    return $this->values;
  }
  
}

?>