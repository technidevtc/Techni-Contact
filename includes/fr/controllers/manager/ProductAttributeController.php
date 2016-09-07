<?php

class ProductAttributeController
{

  public function getList($args = array())
  {
    $product_id = $args['product_id'] ?: 0;

    $q = Doctrine_Query::create()
      ->select('pa.*, a.id, a.name, au.id, au.name')
      ->from('ProductAttribute pa')
      ->innerJoin('pa.attribute a')
      ->leftJoin('pa.attribute_unit au');

    if ($product_id > 0) {
      $q->addSelect('COUNT(pra.id) AS reference_count')
        ->leftJoin('pa.product_reference_attributes pra')
        ->where('pa.product_id = ?', $product_id)
        ->groupBy('pa.id')
        ->orderBy('pa.position');

    } else {
      $q->orderBy('a.name');
    }

    return $q->fetchArray();
  }

  public function get($id)
  {
    $q = Doctrine_Query::create()
      ->select('pa.*, a.id, a.name, au.id, au.name')
      ->from('ProductAttribute pa')
      ->innerJoin('pa.attribute a')
      ->leftJoin('pa.attribute_unit au')
      ->where('pa.id = ?', $id);

    return $q->fetchOne([], Doctrine_Core::HYDRATE_ARRAY);
  }

  public function getBy($args)
  {
    $product_id = $args['product_id'] ?: 0;
    $attribute_id = $args['attribute_id'] ?: 0;
    $value = $args['value'] ?: '';

    $q = Doctrine_Query::create()
      ->select('pa.*, a.id, a.name, au.id, au.name')
      ->from('ProductAttribute pa')
      ->innerJoin('pa.attribute a')
      ->leftJoin('pa.attribute_unit au');
    if ($product_id > 0)
      $q->andWhere('pa.product_id = ?', $product_id);
    if ($attribute_id > 0)
      $q->andWhere('pa.attribute_id = ?', $attribute_id);
    if ($value != '')
      $q->andWhere('pa.value LIKE ?', '%'.$value.'%');

    return $q->fetchOne([], Doctrine_Core::HYDRATE_ARRAY);
  }

  private function setFromArray($productAttribute, $data)
  {
    foreach ($data as $k => $v)
      $productAttribute->{$k} = $v;
  }

  public function create($data, $returnObject = false)
  {
    $productAttribute = new ProductAttribute();

    if (isset($data['updateFacets'])) {
      $updateFacets = true;
      unset($data['updateFacets']);
    }

    $this->setFromArray($productAttribute, $data);

    $productAttribute->save();

    if (isset($updateFacets))
      $this->updateFacets($productAttribute);

    return $returnObject ? $productAttribute : $productAttribute->id;
  }

  public function update($data)
  {
    $productAttribute = Doctrine_Query::create()
      ->select('*')
      ->from('ProductAttribute')
      ->where('id = ?', $data['id'])
      ->fetchOne();

    if (isset($data['updateFacets'])) {
      $updateFacets = true;
      unset($data['updateFacets']);
    }

    $this->setFromArray($productAttribute, $data);

    $productAttribute->save();

    if (isset($updateFacets))
      $this->updateFacets($productAttribute);

    return $productAttribute->id;
  }

  public function delete($id)
  {
    $rows = Doctrine_Query::create()
      ->delete()
      ->from('ProductAttribute')
      ->where('id = ?', $id)
      ->execute();

    return $rows;
  }

  private function updateFacets($productAttribute) {
    $facet = Doctrine_Query::create()
      ->select('fc.*, fcl.*')
      ->from('Facet fc')
      ->innerJoin('fc.lines fcl')
      ->innerJoin('fc.family f')
      ->innerJoin('f.products p')
      ->where('p.id = ?', $productAttribute['product_id'])
      ->andWhere('fc.attribute_id = ?', $productAttribute['attribute_id'])
      ->orderBy('fcl.position')
      ->fetchOne([], Doctrine_Core::HYDRATE_ARRAY);

    $facetLineCount = count($facet['lines']);
    if ($facetLineCount > 0) {
      $found = false;
      $refValue = Utils::toDashAz09($productAttribute['value']);
      $isNumeric = is_numeric($refValue);
      if ($isNumeric)
        $refValue = (float)$refValue;

      foreach ($facet['lines'] as $facetLine) {
        if ($isNumeric && $facetLine['type'] == FacetLine::TYPE_INTERVAL) {
          if ($refValue >= (float)$facetLine['start'] && $refValue <= (float)$facetLine['end']) {
            $found = true;
            break;
          }
        } elseif ($facetLine['type'] == FacetLine::TYPE_VALUE) {
          if ($refValue == $facetLine['ref_value']) {
            $found = true;
            break;
          }
        }
      }

      if (!$found) {
        $newFacetLine = new FacetLine();
        $newFacetLine->facet_id = $facet['id'];
        $newFacetLine->attribute_unit_id = $facet['attribute_unit_id'];
        $newFacetLine->type = FacetLine::TYPE_VALUE;
        $newFacetLine->value = $productAttribute['value'];
        $newFacetLine->active = 1;
        $newFacetLine->position = $facetLineCount + 1;

        $newFacetLine->save();
      }
    }
  }

}
