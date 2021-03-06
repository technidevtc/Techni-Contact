<?php

/**
 * ProductsFamilies
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class ProductsFamilies extends BaseProductsFamilies
{
  public function setUp() {
    parent::setUp();
    $this->hasOne('Families as families', array(
        'local' => 'idFamily',
        'foreign' => 'id'
      )
    );
    $this->hasOne('ProductsFr as product_fr', array(
        'local' => 'idProduct',
        'foreign' => 'id'
      )
    );
    $this->hasOne('Products as product', array(
        'local' => 'idProduct',
        'foreign' => 'id'
      )
    );
    $this->hasOne('Advertisers as advertiser', array(
        'local' => 'id',
        'foreign' => 'idAdvertiser',
        'refClass' => 'Products'
      )
    );
  }
}