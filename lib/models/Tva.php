<?php

/**
 * Tva
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Tva extends BaseTva
{
  public function setUp() {
    parent::setUp();
    $this->hasMany('EstimateLine as estimate_line', array(
        'local' => 'id',
        'foreign' => 'tva_code'
      )
    );
  }
  
  public static $tvaList = array();
  
  public static function getRate($id) {
    if (empty(self::$tvaList)) {
      $tvas = Doctrine_Query::create()
          ->select('*')
          ->from('Tva')
          ->fetchArray();
      foreach ($tvas as $tva)
        self::$tvaList[$tva['id']] = (float)$tva['taux'];
    }
    return isset(self::$tvaList[$id]) ? self::$tvaList[$id] : 0;
  }
  
  public static $tvaFullList = array();
  
  public static function getFullList($timestamp = null, $maxId = null) {
    if (empty(self::$tvaFullList)) {
      // hard coded historical vat rates to assert it's not gonna change, et because it's simplier that way too
      if ($timestamp && $timestamp < mktime(0,0,0,1,1,2014)) {
        self::$tvaFullList = array(
          array(
            'id' => 1,
            'timestamp' => 1147583115,
            'intitule' => 'Taux normal',
            'taux' => 19.6
          ),
          array(
            'id' => 2,
            'timestamp' => 1144274938,
            'intitule' => 'Taux réduit',
            'taux' => 5.5
          ),
          array(
            'id' => 3,
            'timestamp' => 1144272358,
            'intitule' => 'Taux super réduit',
            'taux' => 2.1
          ),
          array(
            'id' => 4,
            'timestamp' => 1256292029,
            'intitule' => 'Taux presse / edition',
            'taux' => 0.0
          )
        );
        if ($maxId)
          self::$tvaFullList = array_slice(self::$tvaFullList, 0, $maxId);
      } else {
          $q = Doctrine_Query::create()
            ->select('*')
            ->from('Tva');
          if ($maxId)
            $q->where('id <= ?', $maxId);
          $q->orderBy('id');
          
          self::$tvaFullList = $q->fetchArray();
      }
    }
    return self::$tvaFullList;
  }
  
  public static function calculatePriceFromId($id, $priceHT = false){ //idTva, product price
    $arrayTva = array();

    if(!isset($priceHT) && !is_numeric($priceHT))
      return false;
    else{
      $arrayTva["tauxTVA"] = self::getRate($id);		
      $arrayTva["priceTVA"] = round($priceHT * $arrayTva["tauxTVA"] / 100, 6);
      $arrayTva["priceTTC"] = $priceHT + $arrayTva["priceTVA"];
      return $arrayTva;
    }
  }
}