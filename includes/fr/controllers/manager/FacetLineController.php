<?php

class FacetLineController
{

  public function getList($args)
  {
    $facet_id = $args['facet_id'] ?: 0;

    $q = Doctrine_Query::create()
      ->select('fcl.*, au.id, au.name, aus.id')
      ->from('FacetLine fcl')
      ->leftJoin('fcl.attribute_unit au');

    if ($facet_id > 0) {
      $q->where('fcl.facet_id = ?', $facet_id);
    }
    $q->orderBy('fcl.position');

    return $q->fetchArray();
  }

  public function get($id)
  {
    $q = Doctrine_Query::create()
      ->select('fcl.*, au.id, au.name')
      ->from('FacetLine fcl')
      ->leftJoin('fcl.attribute_unit au')
      ->where('fcl.id = ?', $id);

    return $q->fetchOne([], Doctrine_Core::HYDRATE_ARRAY);
  }

  private function setFromArray($facetLine, $data)
  {
    foreach ($data as $k => $v)
      $facetLine->{$k} = $v;
  }

  public function create($data, $returnObject = false)
  {
    $facetLine = new FacetLine();

    $this->setFromArray($facetLine, $data);

    $facetLine->save();
    //print_r($facetLine->toArray());

    return $returnObject ? $facetLine : $facetLine->id;
  }

  public function update($data)
  {
    $facetLine = Doctrine_Query::create()
      ->select('*')
      ->from('FacetLine')
      ->where('id = ?', $data['id'])
      ->fetchOne();

    $this->setFromArray($facetLine, $data);

    $facetLine->save();

    return $facetLine->id;
  }

  public function delete($id)
  {
    $rows = Doctrine_Query::create()
      ->delete()
      ->from('FacetLine')
      ->where('id = ?', $id)
      ->execute();

    return $rows;
  }

  public function updatePositions($args)
  {
    $q = Doctrine_Query::create()
      ->select('fcl.id, fcl.position')
      ->from('FacetLine fcl')
      ->whereIn('fcl.id', $args);

    $lines = $q->execute();
    $idToPos = array_flip($args);

    foreach ($lines as $line)
      $line->position = $idToPos[(int)$line->id] + 1;

    $lines->save();

    return 1;
  }

}
