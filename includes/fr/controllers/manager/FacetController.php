<?php

class FacetController
{

  public function getList($args)
  {
    $family_id = isset($args['family_id']) ? (int)$args['family_id'] : 0;
    if (!$family_id)
      return [];

    $facets = Doctrine_Query::create()
      ->select('fc.*, a.id, a.name, au.id, au.name, aus.id, aus.name')
      ->from('Facet fc')
      ->innerJoin('fc.attribute a')
      ->leftJoin('fc.attribute_unit au')
      ->leftJoin('a.units aus')
      ->where('fc.family_id = ?', $family_id)
      ->orderBy('fc.position')
      ->fetchArray();

    // get the product count _after_ to avoid a logical conflict with getting the attribute unit list
    $facetsProductcount = Doctrine_Query::create()
      ->select('fc.id, SUM(IF(pa.attribute_id = fc.attribute_id AND pfr.active = 1 AND pfr.deleted = 0,1,0)) AS product_count')
      ->from('Facet fc')
      ->leftJoin('fc.family f')
      ->leftJoin('f.products p')
      ->leftJoin('p.product_fr pfr')
      ->leftJoin('p.product_attributes pa')
      ->where('fc.family_id = ?', $family_id)
      ->groupBy('fc.id')
      ->fetchArray();

    // add product_count to the facets array
    foreach ($facets as &$facet) {
      $matchedfacet = Utils::array_find(function($facetProductCount) use ($facet) {
        return $facetProductCount['id'] == $facet['id'];
      }, $facetsProductcount);
      $facet['product_count'] = $matchedfacet['product_count'];
    }
    unset($facet);

    return $facets;
  }

  public function get($id)
  {
    $q = Doctrine_Query::create()
      ->select('fc.*, a.name, au.id, au.name, aus.id, aus.name, SUM(IF(pfr.active = 1 AND pfr.deleted = 0,1,0)) AS product_count')
      ->from('Facet fc')
      ->innerJoin('fc.attribute a')
      ->leftJoin('fc.attribute_unit au')
      ->leftJoin('a.units aus')
      ->leftJoin('a.product_attributes pa')
      ->leftJoin('pa.product p')
      ->leftJoin('p.product_fr pfr')
      ->leftJoin('p.families f')
      ->where('fc.id = ?', $id)
      ->andWhere('f.id = fc.family_id OR f.id IS NULL')
      ->groupBy('fc.id');

    return $q->fetchOne([], Doctrine_Core::HYDRATE_ARRAY);
  }

  private function setFromArray($facet, $data)
  {
    foreach ($data as $k => $v)
      $facet->{$k} = $v;
  }

  public function create($data, $returnObject = false)
  {
    $facet = new Facet();

    $this->setFromArray($facet, $data);

    $facet->save();
    // print_r($facet->toArray());
    require_once CONTROLLER.'manager/AttributeController.php';
    require_once CONTROLLER.'manager/FacetLineController.php';
    $attrCtrl = new AttributeController();
    $facetLineCtrl = new FacetLineController();

    $values = $attrCtrl->getValues(['id' => $facet->attribute_id, 'family_id' => $facet->family_id]);

    foreach ($values as $pos => $value) {
      $facetLineCtrl->create([
        'facet_id' => $facet->id,
        'attribute_unit_id' => $facet->attribute_unit_id,
        'type' => FacetLine::TYPE_VALUE,
        'value' => $value['value'],
        'active' => 1,
        'position' => $pos+1,
      ]);
    }

    return $returnObject ? $facet : $facet->id;
  }

  public function update($data)
  {
    $facet = Doctrine_Query::create()
      ->select('*')
      ->from('Facet')
      ->where('id = ?', $data['id'])
      ->fetchOne();

    $this->setFromArray($facet, $data);

    $facet->save();

    return $facet->id;
  }

  public function delete($id)
  {
    $rows = Doctrine_Query::create()
      ->delete()
      ->from('Facet')
      ->where('id = ?', $id)
      ->execute();

    $rows2 = Doctrine_Query::create()
      ->delete()
      ->from('FacetLine')
      ->where('facet_id = ?', $id)
      ->execute();

    return $rows;
  }

  public function updatePositions($args)
  {
    $q = Doctrine_Query::create()
      ->select('fc.id, fc.position')
      ->from('Facet fc')
      ->whereIn('fc.id', $args);

    $facets = $q->execute();
    $idToPos = array_flip($args);

    foreach ($facets as $facet)
      $facet->position = $idToPos[(int)$facet->id] + 1;

    $facets->save();

    return 1;
  }

}
