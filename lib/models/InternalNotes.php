<?php

/**
 * InternalNotes
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class InternalNotes extends BaseInternalNotes
{
  public function setTableDefinition() {
    parent::setTableDefinition();
    $this->hasColumn('context', 'integer', 1, array(
        'type' => 'integer',
        'range' => array(1, 4),
        'length' => 1,
        'fixed' => false,
        'unsigned' => false,
        'primary' => false,
        'default' => '0',
        'notnull' => true,
        'autoincrement' => false
      )
    );
  }
  
  public function setUp() {
    parent::setUp();
    $this->hasOne('BoUsers as operator', array(
        'local' => 'operator',
        'foreign' => 'id'
      )
    );
  }
  
  public function preSave($event) {
    global $user;
    if ($this->isValid()) {
      $this->timestamp = time();
      $this->operator = $user->id;
    }
    else {
      $event->skipOperation();
      //throw new Exception("Données non valides.");
    }
  }
  
  const SUPPLIER_ORDER = 1;
  const CLIENT_COMMAND = 2;
  const CLIENT_ACCOUNT = 3;
  const ESTIMATE = 4;
  const INVOICE = 5;
  const NOTE_FOURNISSEUR = 6;
  public static $contextList = array(
    self::SUPPLIER_ORDER,
    self::CLIENT_COMMAND,
    self::CLIENT_ACCOUNT,
    self::ESTIMATE,
    self::INVOICE,
	self::NOTE_FOURNISSEUR
  );
  public static function getContextText($context_const) {
    switch ($context_const) {
      case self::SUPPLIER_ORDER: return "ordre_fournisseur";
      case self::CLIENT_COMMAND: return "commande_client";
      case self::CLIENT_ACCOUNT: return "compte_client";
      case self::ESTIMATE: return "devis";
      case self::INVOICE: return "facture_avoir";
	  case self::NOTE_FOURNISSEUR: return "note_fournisseur";
      default: return "";
    }
  }
  
}