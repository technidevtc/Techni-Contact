<?php

/**
 * ClientsContacts
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class ClientsContacts extends BaseClientsContacts
{
  public function setTableDefinition() {
    parent::setTableDefinition();
    $this->setAttribute(Doctrine_Core::ATTR_VALIDATE, VALIDATE_ALL);
      $this->setColumnOptions(array(
       /* 'titre',
        'nom',
        'prenom',*/
        'email',
      /*  'tel1',
        'tel2',
        'fax1',
        'fax2',
        'fonction'*/
      ), array(
        'notblank' => true,
        'regexp' => '/^[[:alnum:]]([-_.]?[[:alnum:]])*@[[:alnum:]]([-_.]?[[:alnum:]])*\.([a-z]{2,4})$/',
      )
    );
      
    $this->setColumnOption('num', 'range', array(0,30));
  }

  public function setUp() {
    parent::setUp();
    $this->hasOne('Clients as client', array(
        'local' => 'client_id',
        'foreign' => 'id'
      )
    );
  }
  
  public function preInsert() {
    $this->create_time = $this->timestamp = time();
  }
  
  public function preUpdate() {
    if ($this->isModified())
      $this->timestamp = time();
  }
  
  public function postInsert($event) {
    parent::postInsert($event);

    $contactList = Doctrine_Query::create()->select()->from('clientsContacts')->where('client_id = ?', $this->client_id)->orderBy('num desc')->fetchArray();
    foreach($contactList as $contact)
      $numContact[] = $contact['num'];

    if(count($contactList)>1){
      $this->num = (max($numContact))+1;
    }else
      $this->num = 0;

    $this->save();
  }

  public function updateClientsContacts($data){

    $this->fromArray($data);
    $this->save();
    
    return $this->toArray();
    
  }
  
  public function deleteClientsContacts($data){
    $this->fromArray($data);
    $deletedContact = Doctrine_Query::create()
            ->delete()
            ->from('ClientsContacts')
            ->where('client_id = ? AND num = ?', array_values($data));

    if($deletedContact->execute()){
      $contactList = Doctrine_Query::create()->select()->from('clientsContacts')->where('client_id = ? AND num > ?', array($this->client_id, $this->num))->fetchArray();

      foreach($contactList as $contact)
        Doctrine_Query::create()->update('clientsContacts')->set('num', ($contact['num'])-1)->where('id = ?', $contact['id'])->execute();
    }
    return $this->toArray();
  }
  
 /* public function createClientsContacts($data){
    $this->fromArray($data);
    
    $contactList = Doctrine_Query::create()->select()->from('clientsContacts')->where('client_id = ?', $this->client_id)->orderBy('num desc')->fetchArray();
    foreach($contactList as $contact)
      $numContact[] = $contact['num'];

    if(count($contactList)>1){
      $this->num = (max($numContact))+1;
    }else
      $this->num = 0;
    
    $this->save();
    
    return $this->toArray();
  }*/
  
  /*function create(){
   try{
     $this->email != '' && !preg_match('`^[[:alnum:]]([-_.]?[[:alnum:]])*@[[:alnum:]]([-_.]?[[:alnum:]])*\.([a-z]{2,4})$`', $this->email);
	//th = "Adresse email invalide";
   }catch(Doctrine_Connection_Exception $e){
     
   }
   }*/
   

  public static $cf2ccf = array( // client fields to client contacts fields, usefull for code clarity
    'nom' => 'nom',
    'prenom' => 'prenom',
    'email' => 'email',
    'tel1' => 'tel1',
    'tel2' => 'tel2',
    'fax1' => 'fax1',
    'fax2' => 'fax2',
    'fonction' => 'fonction'
  );
  
}