<?php

/**
 * ProductsRelated
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class ProductsRelated extends BaseProductsRelated
{
  public function setUp() {
    parent::setUp();
    $this->hasOne('Products as product_related_to', array(
        'local' => 'pdt_id',
        'foreign' => 'id'
      )
    );
    $this->hasMany('Products as products_related', array(
        'local' => 'pdt_related_id',
        'foreign' => 'id'
      )
    );
  }

}