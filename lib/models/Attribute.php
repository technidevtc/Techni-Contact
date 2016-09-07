<?php

/**
 * Attribute
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Attribute extends BaseAttribute
{
  public function setUp() {
    parent::setUp();
    $this->hasMany('ProductAttribute as product_attributes', array(
        'local' => 'id',
        'foreign' => 'attribute_id'
      )
    );
    $this->hasMany('AttributeUnit as units', array(
        'local' => 'id',
        'foreign' => 'attribute_id'
      )
    );
    $this->hasMany('Facet as facets', array(
        'local' => 'id',
        'foreign' => 'attribute_id'
      )
    );
  }

}