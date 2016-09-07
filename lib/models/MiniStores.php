<?php

/**
 * MiniStores
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class MiniStores extends BaseMiniStores
{
  public function setUp() {
    parent::setUp();
    $this->hasMany('ActivitySectorSurqualification as activity_sector_surqualifications', array(
        'local' => 'mini_store_id',
        'foreign' => 'activity_sector_surqualification_id',
        'refClass' => 'MiniStoresMarketingLinks'
      )
    );
    
    $this->hasMany('MiniStoresApplication as mini_stores_application', array(
        'local' => 'id',
        'foreign' => 'miniStoreID'
      )
    );
  }
  
  public static function getUrl($id, $ref_name) {
    return URL."miniboutiques/".$id."-".$ref_name.".html";
  }
  
  public static function getPic($id, $type = 'home') {
    switch ($type) {
      case 'vignette': $mspp_inc = MSPP_VIGN; $mspp_url = URL_MSPP_VIGN; break;
      case 'espace': $mspp_inc = MSPP_ESPA; $mspp_url = URL_MSPP_ESPA; break;
      case 'home':
      default : $mspp_inc = MSPP_HOME; $mspp_url = URL_MSPP_HOME;
    }
    return is_file($mspp_inc.$id.".jpg") ? $mspp_url.$id.".jpg" : "";
  }
  
  public static $labelList = array(
    'pdt' => "Produit",
    'cat' => "Famille"
  );
  public static function getLabelType($const) {
    return isset(self::$labelList[$const]) ? self::$labelList[$const] : "";
  }
}