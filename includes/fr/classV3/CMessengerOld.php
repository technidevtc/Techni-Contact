<?php

/* ================================================================/

  Techni-Contact V2 - MD2I SAS
  http://www.techni-contact.com

  Auteur : OD pour Hook Network SARL - http://www.hook-network.com
  Date de création : 16 février 2011


  Fichier : /includes/classV2/CMessenger.php
  Description : Classe de gestion de messagerie

  /================================================================= */

class MessengerOld extends BaseObject {
  /* Handle connexion */

  private $handle = NULL;
  protected $db = null;

  /* messenger fields */
  public $id = 0;
  private $id_sender = 0;
  private $type_sender = 0;
  public $id_recipient = 0;
  private $type_recipient = 0;
  private $timestamp = 0;
  public $object = 0; // not used for the moment
  public $text = 0;
  private $id_parent = 0; // not used for the moment
  public $context = 0;
  public $reference_to = 0;
  protected $IdMax = 999999999;
  protected static $_tables = array(
      array(
          "name" => "messenger",
          "key" => "id",
          "fields" => array(
              "id" => 0,
              "id_sender" => 0,
              "type_sender" => 0,
              "id_recipient" => 0,
              "type_recipient" => 0,
              "object" => 0,
              "text" => 0,
              "id_parent" => 0,
              "context" => 0,
              "reference_to" => 0)
      )
  );
  /* Constructeur */

  function __construct(&$handle,$user,$context) {
    $this->db = DBHandle::get_instance();
    $this->handle = $handle;
    try {

      if ($context >= __MSGR_CTXT_SUPPLIER_TC_ORDER__ && $context <= __MSGR_CTXT_CUSTOMER_ADVERTISER_LEAD)
        $this->context = $context;
      else {
        throw new Exception("Messagerie : Contexte invalide.");
      }

      switch ($user) {
        case $user instanceof Advertiser:
          if ($user->actif) {
            $this->type_sender = __MSGR_USR_TYPE_ADV__;
            $this->id_sender = $user->id;
          }
          else
            throw new Exception("Messagerie : Utilisateur inactif ou inconnu.");
          break;

        case $user instanceof CustomerUser:
          if ($user->actif) {
            $this->type_sender = __MSGR_USR_TYPE_INT__;
            $this->id_sender = $user->id;
          }
          else
            throw new Exception("Messagerie : Utilisateur inactif ou inconnu.");
          break;

        case ($user instanceof BOUser) == true:
          if ($user->active) {
            $this->type_sender = __MSGR_USR_TYPE_BOU__;
            $this->id_sender = $user->id; // bypass an id = 0 for TC and set the TC   $user->id == 0 ? '61049' :
          }
          else
            throw new Exception("Messagerie : Utilisateur inactif ou inconnu.");
          break;

        default :
          throw new Exception("Messagerie : type d'utilisateur invalide.");
      }
    } catch (Exception $e) {
      echo "Error : ".$e->getMessage()."\n";
      return false;
    }
  }

  private static function getNames($id_sender,$type_sender,$id_recipient,$type_recipient, $handle = null) {
    try {
      if ($id_sender !== false && $type_sender) {
        switch ((int) $type_sender) {
          case __MSGR_USR_TYPE_ADV__:
            $sender = new AdvertiserOld($id_sender);
            $sender_name = $sender->nom1;
            break;

          case __MSGR_USR_TYPE_INT__:
            $sender = new CustomerUser($handle,$id_sender);
            $sender_name = $sender->nom.' '.$sender->prenom;
            if ($sender->societe)
              $sender_name = $sender->societe;
            break;

          case __MSGR_USR_TYPE_BOU__:
            $sender = new BOUser($id_sender);
//                      var_dump($id_sender, $type_sender, $sender->name);
            $sender_name = $sender->name;
            break;

          default :
            throw new Exception("Messagerie : type d'emetteur invalide.".$type_sender.__MSGR_USR_TYPE_BOU__);
            break;
        }
      }
      else
        throw new Exception('Messagerie : Erreur d\'identification de l\emetteur');
//            var_dump($id_recipient, $type_recipient);
      if ($id_recipient !== false && $type_recipient) {
        switch ($type_recipient) {
          case __MSGR_USR_TYPE_ADV__:
            $sender = new AdvertiserOld($id_recipient);
            $recipient_name = $sender->nom1;
            break;

          case __MSGR_USR_TYPE_INT__:
            $sender = new CustomerUser($handle,$id_recipient);
            $recipient_name = $sender->nom.' '.$sender->prenom;
            if ($sender->societe)
              $recipient_name .= ' Sté '.$sender->societe;
            break;

          case __MSGR_USR_TYPE_BOU__:
            $sender = new BOUser($id_recipient);
            $recipient_name = $sender->name;
            break;

          default :
            throw new Exception("Messagerie : type de destinataire invalide.");
        }
      }
      else
        throw new Exception('Messagerie : Erreur d\'identification du destinataire');

      $arrayReturn = array(
          'sender_name' => $sender_name,
          'recipient_name' => $recipient_name
      );

      return $arrayReturn;
    } catch (Exception $e) {
      echo "Error : ".$e->getMessage()."\n";
      return false;
    }
  }

//        private static function setUser(ExtranetUser $user){
//          try{
//            if($user->active)
//              return $user->id;
//            else {
//              throw new Exception("Messagerie : Emetteur incorrect.");
//            }
//          }catch(Exception $e){
//            echo "Error : " . $e->getMessage() . "\n";
//                  return false;
//          }
//        }

  private function addMessage() {

    $this->timestamp = time();
    $this->generateID();
    $query = "insert into messenger (id, id_sender, type_sender, id_recipient, type_recipient, timestamp, object, text, id_parent, context, reference_to) ".
            "values ('".$this->db->escape($this->id)."', '".$this->db->escape($this->id_sender)."', '".$this->db->escape($this->type_sender)."', '".
            $this->db->escape($this->id_recipient)."', '".$this->db->escape($this->type_recipient)."', '".
            $this->db->escape($this->timestamp)."', '".$this->db->escape($this->object)."', '".$this->db->escape(utf8_decode($this->text))."', '".
            $this->db->escape($this->id_parent)."', '".$this->db->escape($this->context)."', '".$this->db->escape($this->reference_to)."')";
//            var_dump($query);exit;
    return $this->db->query($query,__FILE__,__LINE__);
  }

  public function generateID() {
    do {
      $id = mt_rand(1,999999999);
      $result = & $this->db->query("select id from messenger where id = ".$id,__FILE__,__LINE__);
    } while ($this->db->numrows($result,__FILE__,__LINE__) == 1);

    $this->id = $id;
  }

  public function getAllConversations() {
    $result = array();
    $query = 'select id, id_sender, type_sender, id_recipient, type_recipient, timestamp, object, text, id_parent, context, reference_to from messenger '.
            'where context = '.$this->context.' and (id_sender = '.$this->id_sender.' or id_recipient = '.$this->id_sender.') '.
            'order by timestamp';
    $res = $this->db->query($query,__FILE__,__LINE__);

    if ($this->db->numrows($res,__FILE__,__LINE__)) {
      while ($resultat = $this->db->fetchAssoc($res,__FILE__,__LINE__))
        $result[] = $resultat;

      return $result;
    }
    else
      return false;
  }

  public function getConversationFromReference($reference_to) {

    if (empty($reference_to) || !preg_match("/^\d+$/",$reference_to))
      return false;

    $result = array();
    $query = 'select id, id_sender, type_sender, id_recipient, type_recipient, timestamp, object, text, id_parent, BIT_COUNT(context), reference_to from messenger '.
            'where ((context = '.$this->context.' and (id_sender = '.$this->id_sender.' or id_recipient = '.$this->id_sender.'))
              OR (context & '.$this->context.' AND type_recipient = '.__MSGR_USR_TYPE_BOU__.')
            ) and reference_to = '.$reference_to.' '.
            'order by timestamp desc';
    
    $res = $this->db->query($query,__FILE__,__LINE__);

    if ($this->db->numrows($res,__FILE__,__LINE__)) {
      while ($resultat = $this->db->fetchAssoc($res,__FILE__,__LINE__)) {
        $names = self::getNames($resultat['id_sender'],$resultat['type_sender'],$resultat['id_recipient'],$resultat['type_recipient'], $this->db);
//              echo " passe \n";
        $resultat['sender_name'] = $names['sender_name'];
        $resultat['recipient_name'] = $names['recipient_name'];
        $result[] = $resultat;
      }

      return $result;
    } else
      return false;
  }

  protected function sendMessageTo($text,$recipient,$reference_to,$object = null) {
    $this->parent = null;

    try {
      if (!empty($text))
        $this->text = $text; else
        throw new Exception("Messagerie : Message vide.");
      if (!empty($recipient) && preg_match("/^[1-9]{1}[0-9]{0,8}$/",$recipient))
        $this->id_recipient = $recipient;
      else
        throw new Exception("Messagerie : Destinataire incorrect.");

      if (!empty($reference_to) && preg_match("/^[1-9]{1}[0-9]{0,8}$/",$reference_to))
        $this->reference_to = $reference_to;
      else
        throw new Exception("Messagerie : Format de référence incorrect.");

      if ($this->id_sender === false)
        throw new Exception("Messagerie : Emetteur non défini.");
      $this->reference_to = $reference_to;

      if (!$this->addMessage())
        throw new Exception("Messagerie : Erreur à l'enregistrement du message.");
      else
        return true;
    } catch (Exception $e) {
      echo "Error : ".$e->getMessage()."\n";
      return false;
    }
  }

  public function sendMessageToAdvertiser($text,$recipient,$reference_to,$object = null) {
    $this->type_recipient = __MSGR_USR_TYPE_ADV__;
    return $this->sendMessageTo($text,$recipient,$reference_to,$object = null);
  }

  public function sendMessageToCustomer($text,$recipient,$reference_to,$object = null) {
    $this->type_recipient = __MSGR_USR_TYPE_INT__;
    return $this->sendMessageTo($text,$recipient,$reference_to,$object = null);
  }

  public function sendMessageToManagerUser($text,$recipient,$reference_to,$object = null) {
    $this->type_recipient = __MSGR_USR_TYPE_BOU__;
    return $this->sendMessageTo($text,$recipient,$reference_to,$object = null);
  }

}
