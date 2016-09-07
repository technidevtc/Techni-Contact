<?php

/**
 * MiniStoresMarketingLinks
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class MiniStoresMarketingLinks extends BaseMiniStoresMarketingLinks
{
  public function setUp() {
    parent::setUp();
      $this->hasOne('MiniStores as mini_stores', array(
        'local' => 'mini_store_id',
        'foreign' => 'id'
      )
    );
    $this->hasOne('ActivitySector as activity_sectors', array(
        'local' => 'activity_sector_id',
        'foreign' => 'id'
      )
    );
    $this->hasOne('ActivitySectorSurqualification as activity_sector_surqualifications', array(
        'local' => 'activity_sector_surqualification_id',
        'foreign' => 'id'
      )
    );
  }

}