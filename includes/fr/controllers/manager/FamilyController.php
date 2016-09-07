<?php

class FamilyController
{

  const MAX_DEPTH = 3;

  //function __construct() {}

  /**
   * case with filter
   * --> filter=char
   * logistique > chariot a plateaux
   * logistique > chariot a plateaux > chariot a 3 plateaux
   * ...
   *
   * case without filter
   * --> depth=1
   * logistique
   * ...
   * --> depth=2
   * logistique
   *   manutention
   *   ...
   *   remorquage
   * ...
   * --> depth=3 or no depth
   * logistique
   *   manutention
   *     vehicules electriques
   *     ...
   *   ...
   * ...
   */
  public function getList($args)
  {
    $filter = $args['filter'] ?: '';
    $parentId = $args['parent_id'] ?: 0;
    $maxDepth = $args['max_depth'] ?: 1;
    $limit = $args['limit'] ?: 50;
    $offset = $args['offset'] ?: 0;

    if (!empty($filter)) {

      // using a raw SQL because doctrine does not support Full Text search in select
      $dbh = Doctrine_Manager::connection()->getDbh();
      $sth = $dbh->prepare('
        SELECT f.id, ffr.name, ffr.ref_name,
               fp.id AS p__id, fpfr.name AS p__name, fpfr.ref_name AS p__ref_name,
               fpp.id AS pp__id, fppfr.name AS pp__name, fppfr.ref_name AS pp__ref_name,
               IF(fpp.id IS NOT NULL, 3, IF(fp.id IS NOT NULL, 2, 1)) AS depth,
               MATCH (ffr.name) AGAINST (:filter IN BOOLEAN MODE) AS score
        FROM families f
        INNER JOIN families_fr ffr ON ffr.id = f.id
        LEFT JOIN families fp ON fp.id = f.idParent
        LEFT JOIN families_fr fpfr ON fpfr.id = fp.id
        LEFT JOIN families fpp ON fpp.id = fp.idParent
        LEFT JOIN families_fr fppfr ON fppfr.id = fpp.id
        WHERE MATCH (ffr.name) AGAINST (:filter IN BOOLEAN MODE)
        ORDER BY depth ASC, score DESC, fppfr.id ASC, fpfr.name ASC, ffr.name ASC
        LIMIT :limit OFFSET :offset');

      $sth->bindValue(':filter', Utils::get_multiword_search_sql_pattern($filter));
      $sth->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
      $sth->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
      $sth->execute();

      $allFamilies = [0 => ['children' => []]]; // all families as flat array
      $familyTree = []; // result families as tree

      // helper function
      $addFamily = function($row, $prefix = '') use (&$allFamilies) {
        $allFamilies[$row[$prefix.'id']] = [
          'id' => $row[$prefix.'id'],
          'name' => $row[$prefix.'name'],
          'ref_name' => $row[$prefix.'ref_name'],
        ];
      };
      $addChild = function($id, $childId) use (&$allFamilies) {
        $allFamilies[$id]['children'][$childId] = &$allFamilies[$childId];
      };

      while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {

        $addFamily($row);
        if (isset($row['p__id'])) {

          if (!isset($allFamilies[$row['p__id']]))
            $addFamily($row, 'p__');

          $addChild($row['p__id'], $row['id']);

          if (isset($row['pp__id'])) {
            if (!isset($allFamilies[$row['pp__id']])) {
              $addFamily($row, 'pp__');
              $addChild(0, $row['pp__id']);
            }

            $addChild($row['pp__id'], $row['p__id']);
          } else {
            $addChild(0, $row['p__id']);
          }
        } else {
          $addChild(0, $row['id']);
        }

        /*$fam = [];
        foreach ($row AS $colName => $colValue) {
          $colParts = explode('__', $colName, 2);

          if (!isset($colParts[1])) {
            if ($colName !== 'score')
              $fam[$colName] = $colValue;
          } elseif ($colParts[0] == 'p' && isset($colValue)) {
            $fam['parent'][$colParts[1]] = $colValue;
          } elseif ($colParts[0] == 'pp' && isset($colValue)) {
            $fam['parent']['parent'][$colParts[1]] = $colValue;
          }

        }

        $families[] = $fam;*/
      }

      function array_values_recursive(&$family) {
        $family['children'] = array_values($family['children']);

        foreach ($family['children'] as &$fam)
          if (isset($fam['children']))
            array_values_recursive($fam);

        unset($fam);
      }

      array_values_recursive($allFamilies[0]);

      return $allFamilies[0]['children'];

    } else {

      $q = Doctrine_Query::create()
        ->select('f1.id, f1.idParent, ffr1.name, ffr1.ref_name')
        ->from('Families f1')
        ->innerJoin('f1.family_fr ffr1');

      if (is_int($parentId)) {
        $q->where('f1.idParent = ?', $parentId);
        if ($parentId === 0)
          $q->orderBy('f1.id');
        else
          $q->orderBy('ffr1.name');
      }

      if (!is_int($maxDepth) || $maxDepth > self::MAX_DEPTH)
        $maxDepth = self::MAX_DEPTH;
      elseif ($maxDepth < 1)
        $maxDepth = 1;

      $curDepth = 1;

      while (++$curDepth <= $maxDepth) {
        $f = 'f'.$curDepth;
        $ffr = 'ffr'.$curDepth;
        $q->addSelect($f.'.id, '.$f.'.idParent, '.$ffr.'.name, '.$ffr.'.ref_name')
          ->leftJoin('f'.($curDepth-1).'.children '.$f)
          ->leftJoin($f.'.family_fr '.$ffr)
          ->addOrderBy($ffr.'.name');
      }

      return $q->fetchArray();
    }

  }

  public function get($id)
  {
    $q = Doctrine_Query::create()
      ->select('f.*, ffr.*,
                fp.id, fpfr.name, fpfr.ref_name,
                fpp.id, fppfr.name, fppfr.ref_name')
      ->from('Families f')
      ->innerJoin('f.family_fr ffr')
      ->leftJoin('f.parent fp')
      ->leftJoin('fp.family_fr fpfr')
      ->leftJoin('fp.parent fpp')
      ->leftJoin('fpp.family_fr fppfr')
      ->where('f.id = ?', $id);

    return $q->fetchOne([], Doctrine_Core::HYDRATE_ARRAY);
  }

  private function setFromArray($family, $data)
  {
    $family->fromArray([
      'idParent' => $data['idParent'],
      'pdt_overwrite' => $data['pdt_overwrite'],
      'family_fr' => [
        'name' => $data['name'],
        'ref_name' => empty($data['ref_name']) ? Utils::toDashAz09($data['name']) : $data['ref_name'],
        'title' => $data['title'],
        'meta_desc' => $data['meta_desc'],
        'text_content' => $data['text_content'],
      ]
    ]);
    // $family->idParent = $data['idParent'];
    // $family->pdt_overwrite = $data['pdt_overwrite'];
    // $family->family_fr->name = $data['name'];
    // $family->family_fr->ref_name = $data['ref_name'];
    // $family->family_fr->title = $data['title'];
    // $family->family_fr->meta_desc = $data['meta_desc'];
    // $family->family_fr->text_content = $data['text_content'];
  }

  public function create($data)
  {
    // specifically create from families_fr to avoid adding an entry in families even if there's a ref_name duplicate
    $family_fr = new FamiliesFr();
    $family_fr->fromArray([
      'name' => $data['name'],
      'ref_name' => empty($data['ref_name']) ? Utils::toDashAz09($data['name']) : $data['ref_name'],
      'title' => $data['title'],
      'meta_desc' => $data['meta_desc'],
      'text_content' => $data['text_content'],
      'family' => [
        'idParent' => $data['idParent'],
        'pdt_overwrite' => $data['pdt_overwrite'],
      ]
    ]);

    // print_r($family_fr->toArray());
    try {
      $family_fr->save();
    } catch (Exception $e) {
      return 0;
    }

    return $family_fr->id;
  }

  public function update($data)
  {
    $family = Doctrine_Query::create()
      ->select('f.*, ffr.*')
      ->from('Families f')
      ->innerJoin('f.family_fr ffr')
      ->where('f.id = ?', $data['id'])
      ->fetchOne();

    $this->setFromArray($family, $data);

    $family->save();

    return $family->id;
  }

  public function delete($id)
  {
    $rows = Doctrine_Query::create()
      ->delete()
      ->from('Families')
      ->where('id = ?', $id)
      ->execute();

    Doctrine_Query::create()
      ->delete()
      ->from('FamiliesFr')
      ->where('id = ?', $id)
      ->execute();

    return $rows;
  }

  public function updateFO() {
    include_once CRON_PATH.'xml/XML_Generator.php';
    return 1;
  }

}
