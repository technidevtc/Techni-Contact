<?php

/* ================================================================/

  Techni-Contact V3 - MD2I SAS
  http://www.techni-contact.com

  Auteur : OD pour Hook Network SARL - http://www.hook-network.com
  Date de création : 21 mars 2011


  Fichier : /includes/classV2/CCallSpool.php
  Description : Classe de gestion de la pile d'appels sortants (call center SMPO)

  /================================================================= */

class CallsSpool extends BaseCollection {

  protected $childObjectName = "Calls";
//  protected $keyName = "";

  public function __construct($args = null) {
    parent::__construct($args);
  }

  public function __destruct() {}

  public function getAll(){
    var_dump($this->collection);
  }

  public function getSome(){
    $this->load('call_result = call_ok');
    var_dump($this->collection);
  }

  public static function resetDailyAbsence(){
    $db = DBHandle::get_instance();
    // daily absence and timestamp have index -> the query is instant
    $db->query("UPDATE call_spool SET daily_absence = 0 WHERE daily_absence != 0 AND timestamp < ".(mktime(23, 59, 59)-(24* 60* 60)), __FILE__, __LINE__);
  }
}