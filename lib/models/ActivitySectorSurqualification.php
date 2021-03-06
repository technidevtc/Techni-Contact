<?php

/**
 * ActivitySectorSurqualification
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * ALTER TABLE `activity_sector_surqualification` ADD CONSTRAINT `aaai` FOREIGN KEY (`activity_sector_id`) REFERENCES `activity_sector`(`id`)
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class ActivitySectorSurqualification extends BaseActivitySectorSurqualification
{
  public function setUp() {
    parent::setUp();
    $this->actAs('Searchable', array(
        'fields' => array('keywords'),
        'batchUpdates' => true
      )
    );
    $this->hasOne('ActivitySector', array(
        'local' => 'activity_sector_id',
        'foreign' => 'id'
      )
    );
    $this->hasMany('MiniStores as mini_stores', array(
        'local' => 'activity_sector_surqualification_id',
        'foreign' => 'mini_store_id',
        'refClass' => 'MiniStoresMarketingLinks'
      )
    );
  }
}