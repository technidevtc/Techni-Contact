<?php

class AttributeController
{

  public function getList($args = array())
  {
    $family_id = $args['family_id'] ?: 0;
    $excludeFacets = $args['excludeFacets'] ?: 0;
    $filter = $args['filter'] ?: '';
    $limit = $args['limit'] ?: 50;
    $offset = $args['offset'] ?: 0;

    $q = Doctrine_Query::create()
      ->select('a.*, au.*')
      ->from('Attribute a')
      ->leftJoin('a.units au');

    if ($family_id > 0) {
      $q->addSelect('fc.id, COUNT(p.id) AS product_count')
        ->innerJoin('a.product_attributes pa')
        ->innerJoin('pa.product p')
        ->innerJoin('p.product_fr pfr')
        ->innerJoin('p.families f')
        ->leftJoin('a.facets fc WITH fc.family_id = f.id')
        ->where('f.id = ?', $family_id)
        ->andWhere('pfr.active = 1')
        ->andWhere('pfr.deleted = 0')
        ->groupBy('a.id')
        ->orderBy('a.name ASC, au.multiplier ASC');

      if ($excludeFacets)
        $q->andWhere('fc.id IS NULL');

    } else {
      if (!empty($filter)) {
        $ftSearch = Utils::get_multiword_search_sql_pattern($filter);
        $q->addSelect('MATCH(a.name) AGAINST(\''.$ftSearch.'\' IN BOOLEAN MODE) AS score')
          ->andWhere('MATCH(a.name) AGAINST(? IN BOOLEAN MODE)', $ftSearch)
          ->orderBy('score DESC, a.name ASC');
      } else {
        $q->orderBy('a.name ASC, au.multiplier ASC');
      }

      $q->offset($offset)
        ->limit($limit);
    }

    return $q->fetchArray();
  }

  public function get($id)
  {
    $q = Doctrine_Query::create()
      ->select('a.*, au.*')
      ->from('Attribute a')
      ->leftJoin('a.units au')
      ->where('a.id = ?', $id);

    return $q->fetchOne([], Doctrine_Core::HYDRATE_ARRAY);
  }

  public function getBy($args)
  {
    $id = $args['id'] ?: 0;
    $family_id = $args['family_id'] ?: 0;

    $q = Doctrine_Query::create()
      ->select('a.*, au.*, fc.id, COUNT(p.id) AS product_count')
      ->from('Attribute a')
      ->leftJoin('a.units au')
      ->innerJoin('a.product_attributes pa')
      ->innerJoin('pa.product p')
      ->innerJoin('p.product_fr pfr')
      ->innerJoin('p.families f')
      ->leftJoin('a.facets fc WITH fc.family_id = f.id')
      ->where('a.id = ?', $id)
      ->andWhere('pfr.active = 1')
      ->andWhere('pfr.deleted = 0')
      ->groupBy('a.id');
    if ($family_id > 0)
      $q->andWhere('f.id = ?', $family_id);

    return $q->fetchOne([], Doctrine_Core::HYDRATE_ARRAY);
  }

  public function getValues($args) {
    $id = $args['id'] ?: 0;
    $family_id = $args['family_id'] ?: 0;

    if ($id > 0) {
      $q = Doctrine_Query::create()
      ->select('IFNULL(pra.value, pa.value) AS val, count(DISTINCT pa.id) as product_count')
      ->from('ProductAttribute pa')
      ->leftJoin('pa.product_reference_attributes pra')
      ->where('pa.attribute_id = ?', $id)
      ->groupBy('val');

      if ($family_id > 0) {
        $q->innerJoin('pa.product p')
        ->innerJoin('p.product_fr pfr WITH pfr.active = 1 AND pfr.deleted = 0')
        ->innerJoin('p.families f WITH f.id = ?', $family_id);
      }

      $_values = $q->execute([], Doctrine_Core::HYDRATE_SCALAR);
      $values = [];
      foreach ($_values as $_value)
        $values[] = ['value' => $_value['pra_val'], 'product_count' => $_value['pa_product_count']];

      return $values;
    } else {
      return [];
    }
  }

  public function getByName($name)
  {
    $q = Doctrine_Query::create()
      ->select('a.*, au.id, au.name')
      ->from('Attribute a')
      ->leftJoin('a.units au')
      ->where('a.name = ?', $name);

    return $q->fetchOne([], Doctrine_Core::HYDRATE_ARRAY);
  }

  private function setFromArray($attribute, $data)
  {
    foreach ($data as $k => $v)
      $attribute->{$k} = $v;
  }

  public function create($data, $returnObject = false)
  {
    $attribute = new Attribute();

    $this->setFromArray($attribute, $data);

    $attribute->save();
    //print_r($attribute->toArray());

    return $returnObject ? $attribute : $attribute->id;
  }

  public function update($data)
  {
    $attribute = Doctrine_Query::create()
      ->select('*')
      ->from('Attribute')
      ->where('id = ?', $data['id'])
      ->fetchOne();

    $this->setFromArray($attribute, $data);

    $attribute->save();

    return $attribute->id;
  }

  public function delete($id)
  {
    $facetCount = Doctrine_Query::create()
      ->select('COUNT(id)')
      ->from('Facet')
      ->where('attribute_id = ?', $id)
      ->groupBy('attribute_id')
      ->fetchOne(array(), Doctrine_Core::HYDRATE_SINGLE_SCALAR);

    $ProductCount = Doctrine_Query::create()
      ->select('COUNT(id)')
      ->from('ProductAttribute')
      ->where('attribute_id = ?', $id)
      ->groupBy('attribute_id')
      ->fetchOne(array(), Doctrine_Core::HYDRATE_SINGLE_SCALAR);

    if ($facetCount + $ProductCount == 0) {
      $rows = Doctrine_Query::create()
        ->delete()
        ->from('Attribute')
        ->where('id = ?', $id)
        ->execute();

      return $rows;
    } else {
      $message = "Impossible de supprimer l'attribut car il est présent dans :<br>\n";
      if ($facetCount > 0)
        $message .= "- ".$facetCount." facette(s)<br>\n";
      if ($ProductCount > 0)
        $message .= "- ".$ProductCount." produit(s)<br>\n";
      throw new Exception(json_encode(['error' => $message]), 422);
    }

  }

  public function merge($args) {
    if (!isset($args['attrIds']) || count($args['attrIds']) < 2)
      return 0;

    $attrIds = $args['attrIds'];
    $mainAttrId = array_shift($attrIds);

    // update linked attribute units
    $q = Doctrine_Query::create()
      ->update('AttributeUnit')
      ->set('attribute_id', '?', $mainAttrId)
      ->whereIn('attribute_id', $attrIds)
      ->execute();

    // update linked facets
    $q = Doctrine_Query::create()
      ->update('Facet')
      ->set('attribute_id', '?', $mainAttrId)
      ->whereIn('attribute_id', $attrIds)
      ->execute();

    // update linked products
    $q = Doctrine_Query::create()
      ->update('ProductAttribute')
      ->set('attribute_id', '?', $mainAttrId)
      ->whereIn('attribute_id', $attrIds)
      ->execute();

    // delete the now useless attributes
    $q = Doctrine_Query::create()
      ->delete('Attribute')
      ->whereIn('id', $attrIds)
      ->execute();

    return $mainAttrId;
  }
}
