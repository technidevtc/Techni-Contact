<?php

/**
 * Contacts
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Contacts extends BaseContacts
{
  public function setUp() {
    parent::setUp();
    $this->hasOne('Clients as client', array(
        'local' => 'email',
        'foreign' => 'login'
      )
    );
    $this->hasOne('BoUsers as created_user', array(
        'local' => 'id_user',
        'foreign' => 'id'
      )
    );
    $this->hasOne('BoUsers as comm_user', array(
        'local' => 'id_user_commercial',
        'foreign' => 'id'
      )
    );
    $this->hasOne('BoUsers as processed_user', array(
        'local' => 'id_user_processed',
        'foreign' => 'id'
      )
    );
    $this->hasOne('Advertisers as advertiser', array(
        'local' => 'idAdvertiser',
        'foreign' => 'id'
      )
    );
    $this->hasOne('Products as product', array(
        'local' => 'idProduct',
        'foreign' => 'id'
      )
    );
    $this->hasOne('Families as category', array(
        'local' => 'idFamily',
        'foreign' => 'id'
      )
    );
    $this->hasOne('Estimate as estimate', array(
        'local' => 'id',
        'foreign' => 'lead_id'
      )
    );
    $this->hasOne('Contacts as primary_lead', array(
        'local' => 'parent',
        'foreign' => 'id'
      )
    );
    $this->hasMany('Contacts as secondary_leads', array(
        'local' => 'id',
        'foreign' => 'parent'
      )
    );
  }
  
  const STATUS_IS_CHARGED = 0x1; // if the lead is charged = 1
  const STATUS_IS_REJECTED = 0x2; // if it is rejected = 2
  const STATUS_IS_DOUBLET = 0x4; // if it is a doublet = 4
  const STATUS_IS_VISIBLE = 0x8; // if the advertiser can see it's personal information = 8
  const STATUS_IS_REJECTABLE = 0x10; // if the advertiser can reject it = 16
  const STATUS_IS_REJECTION_REFUSED = 0x20; // if the lead has been refused for a rejection = 32
  const STATUS_IS_IN_FORFEIT = 0x40; // the advertiser's lead has a forfeit = 64
  const STATUS_IS_CHARGEABLE = 0x80; // the lead is not yet charged = 128
  const STATUS_IS_CREDITED = 0x100; // the lead was charged then rejected, it is credited over the next month = 256
  const STATUS_IS_DISCHARGED = 0x200; // the lead was charged then rejected then paid back = 512
  public static $statusIsList = array(
    self::STATUS_IS_CHARGED => "facturé",
    self::STATUS_IS_REJECTED => "rejeté",
    self::STATUS_IS_DOUBLET => "doublon",
    self::STATUS_IS_VISIBLE => "visible",
    self::STATUS_IS_REJECTABLE => "rejetable",
    self::STATUS_IS_REJECTION_REFUSED => "rejet refusé",
    self::STATUS_IS_IN_FORFEIT => "en forfait",
    self::STATUS_IS_CHARGEABLE => "facturable",
    self::STATUS_IS_CREDITED => "rejeté déduit",
    self::STATUS_IS_DISCHARGED => "rejeté avoir"
  );
  public static function getStatusIsText($const) {
    return isset(self::$statusIsList[$const]) ? self::$statusIsList[$const] : "";
  }

  const STATUS_NOT_CHARGED = 0;
  const STATUS_CHARGED = 25; // self::STATUS_IS_CHARGED | self::STATUS_IS_VISIBLE | self::STATUS_IS_REJECTABLE
  const STATUS_CHARGEABLE = 152; //self::STATUS_IS_CHARGEABLE | self::STATUS_IS_VISIBLE | self::STATUS_IS_REJECTABLE
  const STATUS_CHARGED_PERMANENT = 9; // self::STATUS_IS_CHARGED | self::STATUS_IS_VISIBLE
  const STATUS_REJECTED = 2; // self::STATUS_IS_REJECTED
  const STATUS_REJECTED_WAIT = 27; // self::STATUS_IS_CHARGED | self::STATUS_IS_REJECTED | self::STATUS_IS_VISIBLE | self::STATUS_IS_REJECTABLE
  const STATUS_CHARGEABLE_REJECTED_WAIT = 154; // self::STATUS_IS_CHARGEABLE | self::STATUS_IS_REJECTED | self::STATUS_IS_VISIBLE | self::STATUS_IS_REJECTABLE
  const STATUS_REJECTED_REFUSED = 41; // self::STATUS_IS_CHARGED | self::STATUS_IS_VISIBLE | self::STATUS_IS_REJECTION_REFUSED
  const STATUS_DOUBLET = 12; // self::STATUS_IS_DOUBLET | self::STATUS_IS_VISIBLE
  const STATUS_IN_FORFEIT = 72; // self::STATUS_IS_IN_FORFEIT | self::STATUS_IS_VISIBLE
  const STATUS_CREDITED = 258; // self::STATUS_IS_REJECTED | self::STATUS_IS_CREDITED
  const STATUS_DISCHARGED = 514; // self::STATUS_IS_REJECTED | self::STATUS_IS_DISCHARGED
  public static $statusList = array(
    self::STATUS_NOT_CHARGED => "non facturé",
    self::STATUS_CHARGEABLE => "facturable",
    self::STATUS_CHARGED => "facturé",
    self::STATUS_CHARGED_PERMANENT => "facturé",
    self::STATUS_REJECTED => "rejeté",
    self::STATUS_REJECTED_WAIT => "demande de rejet en attente de validation",
    self::STATUS_CHARGEABLE_REJECTED_WAIT => "demande de rejet en attente de validation",
    self::STATUS_REJECTED_REFUSED => "rejet refusé",
    self::STATUS_DOUBLET => "doublon non facturé",
    self::STATUS_IN_FORFEIT => "compris dans le forfait",
    self::STATUS_CREDITED => "rejeté - déduit de la période de ",
    self::STATUS_DISCHARGED => "rejeté - avec avoir "
  );
  public static function getStatusText($const) {
    return isset(self::$statusList[$const]) ? self::$statusList[$const] : "";
  }
  
}