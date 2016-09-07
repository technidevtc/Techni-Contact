<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('ReferencesCols', 'doctrine');

/**
 * BaseReferencesCols
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $idProduct
 * @property string $content
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseReferencesCols extends Auto_Id_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('references_cols');
        $this->hasColumn('idProduct', 'integer', 3, array(
             'type' => 'integer',
             'length' => 3,
             'fixed' => false,
             'unsigned' => true,
             'primary' => true,
             'autoincrement' => false,
             ));
        $this->hasColumn('content', 'string', null, array(
             'type' => 'string',
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             ));
    }

    public function setUp()
    {
        parent::setUp();
        
    }
}