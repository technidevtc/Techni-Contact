<?php

/**
 * UploadedFiles
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class UploadedFiles extends BaseUploadedFiles
{
  public function setUp() {
    parent::setUp();
    $this->hasOne('MessengerPjs as attachment', array(
        'local' => 'id',
        'foreign' => 'id_uploaded_files'
      )
    );
  }

}