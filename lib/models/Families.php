<?php

/**
 * Families
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Families extends BaseFamilies
{
  public function setUp() {
    parent::setUp();
    $this->hasMany('Products as products', array(
        'local' => 'idFamily',
        'foreign' => 'idProduct',
        'refClass' => 'ProductsFamilies'
      )
    );
    $this->hasOne('FamiliesFr as family_fr', array(
        'local' => 'id',
        'foreign' => 'id'
      )
    );
    $this->hasMany('Families as children', array(
        'local' => 'id',
        'foreign' => 'idParent'
      )
    );
    $this->hasOne('Families as parent', array(
        'local' => 'idParent',
        'foreign' => 'id'
      )
    );
    $this->hasMany('Facet as facets', array(
        'local' => 'id',
        'foreign' => 'family_id'
      )
    );
  }

  private static $domXML;
  private static $xPathXML;

  public $maxId = 9999;

  public static function getDomXML() {
    if (!isset(self::$domXML)) {
      self::$domXML = new DomDocument();
      self::$domXML->validateOnParse = true;
      self::$domXML->load(XML_CATEGORIES_ALL);
    }
    return self::$domXML;
  }

  public static function getXPathXML() {
    if (!isset(self::$xPathXML)) {
      self::$xPathXML = new DOMXPath(self::getDomXML());
    }
    return self::$xPathXML;
  }

}
