<?php

class ProductReferenceAttributeController
{

  public function getList($args = array())
  {
    $reference_id = $args['reference_id'] ?: 0;
    $product_attribute_id = $args['product_attribute_id'] ?: 0;

    $q = Doctrine_Query::create()
      ->select('pra.*')
      ->from('ProductReferenceAttribute pra');

    if ($reference_id > 0) {
      $q->where('pra.product_reference_id = ?', $reference_id);
    } elseif ($product_attribute_id > 0) {
      $q->where('pra.product_attribute_id = ?', $product_attribute_id);
    } else {
      $q->orderBy('a.name')
        ->limit(1000);
    }

    return $q->fetchArray();
  }

  public function get($id)
  {
    $q = Doctrine_Query::create()
      ->select('pra.*')
      ->from('ProductReferenceAttribute pa')
      ->where('pra.id = ?', $id);

    return $q->fetchOne([], Doctrine_Core::HYDRATE_ARRAY);
  }

  private function setFromArray($productReferenceAttribute, $data)
  {
    foreach ($data as $k => $v)
      $productReferenceAttribute->{$k} = $v;
  }

  public function create($data, $returnObject = false)
  {
    $productReferenceAttribute = new ProductReferenceAttribute();

    $this->setFromArray($productReferenceAttribute, $data);

    $productReferenceAttribute->save();
    //print_r($productReferenceAttribute->toArray());

    return $returnObject ? $productReferenceAttribute : $productReferenceAttribute->id;
  }

  public function update($data)
  {
    $productReferenceAttribute = Doctrine_Query::create()
      ->select('*')
      ->from('ProductReferenceAttribute')
      ->where('id = ?', $data['id'])
      ->fetchOne();

    $this->setFromArray($productReferenceAttribute, $data);

    $productReferenceAttribute->save();

    return $productReferenceAttribute->id;
  }

  public function delete($id)
  {
    $rows = Doctrine_Query::create()
      ->delete()
      ->from('ProductReferenceAttribute')
      ->where('id = ?', $id)
      ->execute();

    return $rows;
  }

  public function deleteByReferenceId($referenceId) {
    $rows = Doctrine_Query::create()
      ->delete()
      ->from('ProductReferenceAttribute')
      ->where('product_reference_id = ?', $referenceId)
      ->execute();

    return $rows;
  }

  public function deleteByProductAttributeId($pdtAttrId) {
    $rows = Doctrine_Query::create()
      ->delete()
      ->from('ProductReferenceAttribute')
      ->where('product_attribute_id = ?', $pdtAttrId)
      ->execute();

    return $rows;
  }

}
