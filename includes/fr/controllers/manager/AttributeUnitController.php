<?php

class AttributeUnitController
{

  public function getList($args = array())
  {
    $attribute_id = isset($args['attribute_id']) ? (int)$args['attribute_id'] : 0;
    if (!$attribute_id)
      return [];

    $q = Doctrine_Query::create()
      ->select('au.*')
      ->from('AttributeUnit au')
      ->where('au.attribute_id = ?', $attribute_id)
      ->orderBy('au.multiplier ASC, au.name');

    return $q->fetchArray();
  }

  public function get($id)
  {
    $q = Doctrine_Query::create()
      ->select('au.*')
      ->from('AttributeUnit au')
      ->where('au.id = ?', $id);

    return $q->fetchOne([], Doctrine_Core::HYDRATE_ARRAY);
  }

  public function getBy($args)
  {
    $attribute_id = $args['attribute_id'] ?: 0;
    $name = $args['name'] ?: '';

    $q = Doctrine_Query::create()
      ->select('au.*')
      ->from('AttributeUnit au');
    if ($attribute_id > 0)
      $q->andWhere('au.attribute_id = ?', $attribute_id);
    if ($name != '')
      $q->andWhere('au.name LIKE ?', '%'.$name.'%');

    return $q->fetchOne([], Doctrine_Core::HYDRATE_ARRAY);
  }

  private function setFromArray($attributeUnit, $data)
  {
    foreach ($data as $k => $v)
      $attributeUnit->{$k} = $v;
  }

  public function create($data, $returnObject = false)
  {
    $attributeUnit = new AttributeUnit();

    $this->setFromArray($attributeUnit, $data);

    $attributeUnit->save();
    //print_r($attributeUnit->toArray());

    return $returnObject ? $attributeUnit : $attributeUnit->id;
  }

  public function update($data)
  {
    $attributeUnit = Doctrine_Query::create()
      ->select('*')
      ->from('AttributeUnit')
      ->where('id = ?', $data['id'])
      ->fetchOne();

    $this->setFromArray($attributeUnit, $data);

    $attributeUnit->save();

    return $attributeUnit->id;
  }

  public function delete($id)
  {
    $facetCount = Doctrine_Query::create()
      ->select('COUNT(id)')
      ->from('Facet')
      ->where('attribute_unit_id = ?', $id)
      ->groupBy('attribute_unit_id')
      ->fetchOne(array(), Doctrine_Core::HYDRATE_SINGLE_SCALAR);

    $facetLineCount = Doctrine_Query::create()
      ->select('COUNT(id)')
      ->from('FacetLine')
      ->where('attribute_unit_id = ?', $id)
      ->groupBy('attribute_unit_id')
      ->fetchOne(array(), Doctrine_Core::HYDRATE_SINGLE_SCALAR);

    $ProductCount = Doctrine_Query::create()
      ->select('COUNT(id)')
      ->from('ProductAttribute')
      ->where('attribute_unit_id = ?', $id)
      ->groupBy('attribute_unit_id')
      ->fetchOne(array(), Doctrine_Core::HYDRATE_SINGLE_SCALAR);

    if ($facetCount + $facetLineCount + $ProductCount == 0) {
      $rows = Doctrine_Query::create()
        ->delete()
        ->from('AttributeUnit')
        ->where('id = ?', $id)
        ->execute();

      return $rows;
    } else {
      $message = "Impossible de supprimer l'unité car elle est présente dans : <br>\n";
      if ($facetCount > 0)
        $message .= "- ".$facetCount." facette(s)<br>\n";
      if ($facetLineCount > 0)
        $message .= "- ".$facetLineCount." ligne de facette(s)<br>\n";
      if ($ProductCount > 0)
        $message .= "- ".$ProductCount." produit(s)<br>\n";
      throw new Exception(json_encode(['error' => $message]), 422);
    }
  }

  public function merge($args) {
    if (!isset($args['unitIds']) || count($args['unitIds']) < 2)
      return 0;

    $unitIds = $args['unitIds'];
    $mainUnitId = array_shift($unitIds);

    // update linked facets
    $q = Doctrine_Query::create()
      ->update('Facet')
      ->set('attribute_unit_id', '?', $mainUnitId)
      ->whereIn('attribute_unit_id', $unitIds)
      ->execute();

    // update linked facet lines
    $q = Doctrine_Query::create()
      ->update('FacetLine')
      ->set('attribute_unit_id', '?', $mainUnitId)
      ->whereIn('attribute_unit_id', $unitIds)
      ->execute();

    // update linked products
    $q = Doctrine_Query::create()
      ->update('ProductAttribute')
      ->set('attribute_unit_id', '?', $mainUnitId)
      ->whereIn('attribute_unit_id', $unitIds)
      ->execute();

    // delete the now useless units
    $q = Doctrine_Query::create()
      ->delete('AttributeUnit')
      ->whereIn('id', $unitIds)
      ->execute();

    return $mainUnitId;
  }
}
