<?php
class FrontFacetController
{

  public function getList($familyId, $showEmpty = false)
  {

    $q = Doctrine_Query::create()
      ->select('fc.*, fcl.*, fcau.*, fclau.*')
      ->from('Facet fc')
      ->innerJoin('fc.lines fcl WITH fcl.active = 1')
      ->leftJoin('fc.attribute_unit fcau')
      ->leftJoin('fcl.attribute_unit fclau')
      ->where('fc.family_id = ?', $familyId)
      ->andWhere('fc.active = 1')
      ->orderBy('fc.position')
      ->addOrderBy('fcl.position');

    if (!$showEmpty) {
      $q->addSelect('a.id, pa.value, pra.value')
        ->innerJoin('fc.attribute a')
        ->innerJoin('a.product_attributes pa')
        ->leftJoin('pa.product_reference_attributes pra')
        ->innerJoin('pa.product p')
        ->innerJoin('p.product_fr pfr WITH pfr.active = 1 AND pfr.deleted = 0')
        ->innerJoin('p.advertiser adv WITH adv.actif = 1')
        ->innerJoin('p.families f WITH f.id = fc.family_id');
    }

    $facets = $q->fetchArray();

    if (!$showEmpty) {
      $facets = array_filter($facets, function(&$facet) {
        $facet['lines'] = array_values(array_filter($facet['lines'], function($facetLine) use ($facet) {
          $facetLine['value'] = trim($facetLine['value']);
          foreach ($facet['attribute']['product_attributes'] as $pa) {
            if ($pa['product_reference_attributes']) {
              foreach ($pa['product_reference_attributes'] as $pra) {
                $pra['value'] = trim($pra['value']);
                if (($facetLine['type'] == FacetLine::TYPE_VALUE && strcasecmp($pra['value'], $facetLine['value']) == 0)
                  || ($facetLine['type'] == FacetLine::TYPE_INTERVAL && $pra['value'] >= $facetLine['start'] && $pra['value'] <= $facetLine['end']))
                  return true;
              }
            } else {
              $pa['value'] = trim($pa['value']);
              if (($facetLine['type'] == FacetLine::TYPE_VALUE && strcasecmp($pa['value'], $facetLine['value']) == 0)
                || ($facetLine['type'] == FacetLine::TYPE_INTERVAL && $pa['value'] >= $facetLine['start'] && $pa['value'] <= $facetLine['end']))
                return true;
            }
          }
          return false;
        }));
        return count($facet['lines']);
      });
    }

    foreach ($facets as &$facet) {
      $facet['unit_text'] = ((int)$facet['show_unit_bitfield'] & Facet::SHOW_UNIT_TITLE) > 0 ? $facet['attribute_unit']['name'] : '';

      $showUnitValues = (int)$facet['show_unit_bitfield'] & Facet::SHOW_UNIT_VALUES;
      foreach ($facet['lines'] as &$line)
        $line['unit_text'] = $showUnitValues ? $line['attribute_unit']['name'] : '';
      unset($line);
    }
    unset($facet);

    return $facets;
  }

  public function get($id)
  {
    $q = Doctrine_Query::create()
      ->select('fc.*, fcl.*')
      ->from('Facet fc')
      ->innerJoin('fc.lines fcl')
      ->leftJoin('fc.attribute_unit fcau')
      ->leftJoin('fcl.attribute_unit fclau')
      ->where('fc.id = ?', $id)
      ->andWhere('fc.active = 1');

    return $q->fetchOne([], Doctrine_Core::HYDRATE_ARRAY);
  }

  public function getByRef($familyId, $refTitle, $refValue = null)
  {
    $q = Doctrine_Query::create()
      ->select('fc.*, fcl.*')
      ->from('Facet fc')
      ->innerJoin('fc.lines fcl')
      ->leftJoin('fc.attribute_unit fcau')
      ->leftJoin('fcl.attribute_unit fclau')
      ->where('fc.family_id = ?', $familyId)
      ->andWhere('fc.ref_title = ?', $refTitle)
      ->andWhere('fc.active = 1');
    if (!empty($refValue))
      $q->andWhere('fcl.ref_value = ?', $refValue);

    return $q->fetchOne([], Doctrine_Core::HYDRATE_ARRAY);
  }
}
