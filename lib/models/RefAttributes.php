<?php

/**
 * RefAttributes
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class RefAttributes extends BaseRefAttributes
{
  public function setUp() {
    parent::setUp();
    $this->hasMany('RefAttributesIntervals as intervals', array(
        'local' => 'id',
        'foreign' => 'attributeId'
      )
    );
    $this->hasMany('RefAttributesValues as values', array(
        'local' => 'id',
        'foreign' => 'attributeId'
      )
    );
    $this->hasMany('RefAttributesVirtual as virtual_lines', array(
        'local' => 'id',
        'foreign' => 'attributeId'
      )
    );
    $this->hasOne('Families as family', array(
        'local' => 'categoryId',
        'foreign' => 'id'
      )
    );
  }
}