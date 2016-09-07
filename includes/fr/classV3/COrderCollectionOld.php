<?php

class OrderCollectionOld  {

/* Handle connexion */
      private $handle = NULL;
      private $db = null;
        
  public $order = array();


  public function __construct($args = null) {
    $this->db = DBHandle::get_instance();
//    parent::__construct($args);

  }
	
  public function __destruct() {}
  
  public function getAll(){

    $query = "select idCommande, idAdvertiser, totalOrdreHT, totalOrdreTTC, fdpOrdreHT, fdpOrdreTTC, statut_traitement, dispatch_time, isMailSent, timestampIMS, mailComment, idSender, arc, timestampArc, annulation, motif_annulation, attente_info from commandes_advertisers ";

    $result = $this->db->query($query);

    if ($this->db->numrows($result, __FILE__, __LINE__) != 0){

      while ($order = $this->db->fetchAssoc($result, __FILE__, __LINE__)){
        $this->order[] = $order;
      }

      return $this->order;
    }else
      return false;
  }

  public function getInWarning(){

    $query = "select idCommande, idAdvertiser, totalOrdreHT, totalOrdreTTC, fdpOrdreHT, fdpOrdreTTC, statut_traitement, dispatch_time, isMailSent, timestampIMS, mailComment, idSender, arc, timestampArc, timestampSeen, annulation, motif_annulation, attente_info from commandes_advertisers ".
              "where statut_traitement <= 3 "; //fiche commande non visitée via le mail de notification ou via la liste des commandes ou commande lue et validée dont l'ARC n'a pas encore été lié

    $result = $this->db->query($query);

    if ($this->db->numrows($result, __FILE__, __LINE__) != 0){

      while ($order = $this->db->fetchAssoc($result, __FILE__, __LINE__)){
        $this->order[] = $order;
      }

      return $this->order;
    }else
      return false;
  }

}

?>