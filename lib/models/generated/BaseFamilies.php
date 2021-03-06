<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Families', 'doctrine');

/**
 * BaseFamilies
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property integer $idParent
 * @property string $pdt_overwrite
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseFamilies extends Auto_Id_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('families');
        $this->hasColumn('id', 'integer', 3, array(
             'type' => 'integer',
             'length' => 3,
             'fixed' => false,
             'unsigned' => true,
             'primary' => true,
             'autoincrement' => false,
             ));
        $this->hasColumn('idParent', 'integer', 3, array(
             'type' => 'integer',
             'length' => 3,
             'fixed' => false,
             'unsigned' => true,
             'primary' => false,
             'default' => '0',
             'notnull' => true,
             'autoincrement' => false,
             ));
        $this->hasColumn('pdt_overwrite', 'string', null, array(
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