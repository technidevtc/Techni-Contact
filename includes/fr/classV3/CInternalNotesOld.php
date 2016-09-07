<?php
/* ================================================================/

  Techni-Contact V3 - MD2I SAS
  http://www.techni-contact.com

  Auteur : OD pour Hook Network SARL - http://www.hook-network.com
  Date de création : 31 mars 2011


  Fichier : /includes/classV2/CInternalNotes.php
  Description : Classe de gestion des notes internes

  /================================================================= */

class InternalNotesOld extends BaseObject {
  /* Handle connexion */

  protected $IdMax = 999999999;
  private $contexts = array(
    1 => 'ordre_fournisseur',
    2 => 'commande_client',
    3 => 'compte_client',
  );

  protected static $_tables = array(
      array(
          "name" => "internal_notes",
          "key" => "id",
          "fields" => array(
              "id" => 0,
              "id_reference" => "",
              "context" => "",
              "timestamp" => "",
              "operator" => "",
              "content" => ""
              )
      )
  );

  public static function get() {
    $args = func_get_args();
    return BaseObject::get(self::$_tables, $args);
  }

  public static function delete($id) {
    return BaseObject::delete($id, self::$_tables);
  }
/**
 *
 * @param <string> $context = 'ordre_fournisseur' ou 'commande_client' ou 'compte_client' , mandatory
 * @param <type> $args
 */
  public function __construct($context, $args = null) {
    $this->tables = self::$_tables;

    try {
      if(in_array($context, $this->contexts)){
        $flippedContexts = array_flip($this->contexts);
        
        parent::__construct($args);
        $this->fields['context'] = $flippedContexts[$context];
      }else
        throw new Exception('Context incorrect');

    } catch (Exception $exc) {
      echo $exc->getMessage();
    }

//    if ($this->existsInDB)
//      $this->build();
  }

  public function create($data = null) {
    parent::create($data);
//    $this->built = false;
  }

  public function load() {
    $r = parent::load();
//    $this->built = false;
    return $r;
  }

  public function save() {
    $r = parent::save();
    return $r;
  }

  public function addNote( $user, $contenu, $id_reference ){

    try {
      if(!empty($contenu)){
        $this->fields['operator'] = $user->id;
        $this->fields['content'] = $contenu;
        $this->fields['id_reference'] = $id_reference;
        $this->fields['timestamp'] = time();
        return $this->save();
      }else
        throw new Exception('Contenu vide');

    } catch (Exception $exc) {
      echo $exc->getMessage();
    }

  }

  public function getAllNotesByIdRef( $id_reference ){
    
    return $this->get("id_reference = '".$id_reference."' and context = ".$this->fields['context']." order by timestamp desc");

  }

}