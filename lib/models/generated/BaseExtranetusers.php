<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Extranetusers', 'doctrine');

/**
 * BaseExtranetusers
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property string $login
 * @property string $pass
 * @property integer $c
 * @property string $webpass
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseExtranetusers extends Auto_Id_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('extranetusers');
        $this->hasColumn('id', 'integer', 2, array(
             'type' => 'integer',
             'length' => 2,
             'fixed' => false,
             'unsigned' => true,
             'primary' => true,
             'autoincrement' => false,
             ));
        $this->hasColumn('login', 'string', 255, array(
             'type' => 'string',
             'length' => 255,
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'default' => '',
             'notnull' => true,
             'autoincrement' => false,
             ));
        $this->hasColumn('pass', 'string', 32, array(
             'type' => 'string',
             'length' => 32,
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'default' => '',
             'notnull' => true,
             'autoincrement' => false,
             ));
        $this->hasColumn('c', 'integer', 1, array(
             'type' => 'integer',
             'length' => 1,
             'fixed' => false,
             'unsigned' => true,
             'primary' => false,
             'default' => '0',
             'notnull' => true,
             'autoincrement' => false,
             ));
        $this->hasColumn('webpass', 'string', 40, array(
             'type' => 'string',
             'length' => 40,
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'default' => '',
             'notnull' => true,
             'autoincrement' => false,
             ));
    }

    public function setUp()
    {
        parent::setUp();
        
    }
}