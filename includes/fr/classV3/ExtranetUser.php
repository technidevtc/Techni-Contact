<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 13 juillet 2005

 Fichier : /includes/classV2/ExtranetUser.php4
 Description : Classe utilisateur extranet

/=================================================================*/

//Session is commented because it is already declared on includes/fr/extranetV3/head_autoconnect_uid.php
if(!isset($_SESSION)){
	session_name('extranet');
	session_start();
}


/*$oldid = session_id();
session_regenerate_id();
unlink('/tmp/sess_' . $oldid);*/


class ExtranetUser {
  
  public $handle = null;
  
  public $id       = -1;
  public $login    = '';
  public $ip       = '';
  public $active   = 0;
  public $name     = '';
  public $email    = '';
  public $adresse  = '';
  public $cadresse = '';
  public $ville    = '';
  public $cp       = '';
  public $pays     = '';
  public $contact  = '';
  public $url      = '';
  public $tel1     = '';
  public $tel2     = '';
  public $fax1     = '';
  public $fax2     = '';
  public $parent   = 0;
  
  public $delai_livraison = '';
  public $prixPublic      = 0;
  public $margeRemise     = 0;
  public $idTVA           = 1;
  public $peuChangerTaux  = 0;
  public $contraintePrix  = 0;
  
  public $create_time = 0;
  public $ic_active = 0;

  public $litigation_time = 0;
  
  public $client_id = 0;
  
  

  public function __construct() {
    $this->handle = DBHandle::get_instance();
  }
  
  public function login($login = '', $pass = '') {
    $ret = false;
    
    if (isset($_SESSION['login']) && isset($_SESSION['pass']) && isset($_SESSION['ip']) && isset($_SESSION['id']) && $login == '') {
      $result = $this->handle->query("
        SELECT
          a.actif,
          a.nom1,
          a.email,
          a.adresse1,
          a.adresse2,
          a.ville,
          a.cp,
          a.pays,
          a.contact,
          a.url,
          a.tel1,
          a.tel2,
          a.fax1,
          a.fax2,
          a.parent,
          a.delai_livraison,
          a.prixPublic,
          a.margeRemise,
          a.idTVA,
          a.peuChangerTaux,
          a.contraintePrix,
          a.create_time,
          a.ic_active,
          a.ic_extranet,
          a.category,
          a.timestamp,
          a.litigation_time,
          a.client_id,
		  
		  e.c,
		  e.webpass		  
		  
        FROM extranetusers e, advertisers a
        WHERE
          e.login = '" . $this->handle->escape($_SESSION['login']) . "' AND
          e.pass = '" . $this->handle->escape($_SESSION['pass']) . "' AND
          e.id = '" . $this->handle->escape($_SESSION['id']) . "' AND
          e.id = a.id AND
          a.deleted != 1", __FILE__, __LINE__);
      
      if ($this->handle->numrows($result, __FILE__, __LINE__) == 1 && $_SESSION['ip'] == $this->getIP()) {

        $ret = true;

        $adv = $this->handle->fetchAssoc($result);

        $this->id       = $_SESSION['id'];
        $this->ip       = $_SESSION['ip'];
        $this->pass     = $_SESSION['pass'];
        $this->login    = $_SESSION['login'];
        $this->active   = $adv['actif'];
        $this->name     = $adv['nom1'];
        $this->email    = $adv['email'];
        $this->adresse  = $adv['adresse1'];
        $this->cadresse = $adv['adresse2'];
        $this->ville    = $adv['ville'];
        $this->cp       = $adv['cp'];
        $this->pays     = $adv['pays'];
        $this->contact  = $adv['contact'];
        $this->url      = $adv['url'];
        $this->tel1     = $adv['tel1'];
        $this->tel2     = $adv['tel2'];
        $this->fax1     = $adv['fax1'];
        $this->fax2     = $adv['fax2'];
        $this->parent   = $adv['parent'];
		
        
		//Condition add on 13/11/2014 15h32m
		//To auto redirect the user when hi's category changed from 1 to (0, 2, 3, 4 or 5)
			
			$_SESSION['extranet_user_ip']				= $_SESSION['ip'];
			$_SESSION['extranet_user_id']				= $_SESSION['id'];
			$_SESSION['extranet_user_actif']			= $adv['actif'];
			$_SESSION['extranet_user_c']				= $adv['c'];	
			$_SESSION['extranet_user_webpass']			= $adv['webpass'];
			$_SESSION['extranet_user_contact']			= $adv['contact'];
			$_SESSION['extranet_user_name1']			= $adv['nom1'];
			$_SESSION['extranet_user_email']			= $adv['email'];
			$_SESSION['extranet_user_category']			= $adv['category'];
			$_SESSION['extranet_user_litigation_time']	= $adv['litigation_time'];
			$_SESSION['extranet_user_parent']			= $adv['parent'];

		//End modification code !
			
			
        if ($this->parent == 61049) {
          $this->delai_livraison = $adv['delai_livraison'];
          $this->prixPublic      = $adv['prixPublic'];
          $this->margeRemise     = $adv['margeRemise'];
          $this->idTVA           = $adv['idTVA'];
          $this->peuChangerTaux  = $adv['peuChangerTaux'];
          $this->contraintePrix  = $adv['contraintePrix'];
        }
        
        $this->create_time     = $adv['create_time'];
        $this->ic_active       = $adv['ic_active'];
        $this->ic_extranet     = $adv['ic_extranet'];
        $this->category        = $adv['category'];
        $this->timestamp       = $adv['timestamp'];
        $this->litigation_time = $adv['litigation_time'];
        
        $this->client_id       = $adv['client_id'];
      }
      else {
        // Données session falsifiées ou adresse ip a changé, on logge l'action !
        ExtranetLog($this->handle, $_SESSION['login'], $_SESSION['ip'], 'Erreur de session : piratage des données d\'identification ou adresse IP invalide (Adresse courante : ' . $this->getIP() . ', Mot de passe : ' .  $_SESSION['pass'] . ')');

        @session_destroy();
      }

    }
    elseif($login != '') {
      $ip = $this->getIP();

      $result = $this->handle->query("
        SELECT
          e.id,
          e.pass,
          a.actif,
          a.nom1,
          a.email,
          a.adresse1,
          a.adresse2,
          a.ville,
          a.cp,
          a.pays,
          a.contact,
          a.url,
          a.tel1,
          a.tel2,
          a.fax1,
          a.fax2,
          a.parent,
          a.delai_livraison,
          a.prixPublic,
          a.margeRemise,
          a.idTVA,
          a.peuChangerTaux,
          a.contraintePrix,
          a.create_time,
          a.ic_active,
          a.ic_extranet,
          a.category,
          a.timestamp,
          a.litigation_time,
          a.client_id
        FROM extranetusers e, advertisers a
        WHERE
          e.login = '" . $this->handle->escape($login) . "' AND
          e.id = a.id AND
          a.deleted != 1", __FILE__, __LINE__);
      
      
      if ($this->handle->numrows($result, __FILE__, __LINE__) == 1) {
        $adv = $this->handle->fetchAssoc($result);

        if (strlen($adv['pass']) == 32 && strlen($pass) < 32) {
          $pass = md5($pass);
        }

        // Contrôler le pass
        if ($pass == $adv['pass']) {
          $ret = true;

          $this->id       = $adv['id'];
          $this->ip       = $ip;
          $this->login    = $login;
          $this->active   = $adv['actif'];
          $this->name     = $adv['nom1'];
          $this->email    = $adv['email'];
          $this->adresse  = $adv['adresse1'];
          $this->cadresse = $adv['adresse2'];
          $this->ville    = $adv['ville'];
          $this->cp       = $adv['cp'];
          $this->pays     = $adv['pays'];
          $this->contact  = $adv['contact'];
          $this->url      = $adv['url'];
          $this->tel1     = $adv['tel1'];
          $this->tel2     = $adv['tel2'];
          $this->fax1     = $adv['fax1'];
          $this->fax2     = $adv['fax2'];
          $this->parent   = $adv['parent'];

          if ($this->parent == 61049) {
            $this->delai_livraison = $adv['delai_livraison'];
            $this->prixPublic      = $adv['prixPublic'];
            $this->margeRemise     = $adv['margeRemise'];
            $this->idTVA           = $adv['idTVA'];
            $this->peuChangerTaux  = $adv['peuChangerTaux'];
            $this->contraintePrix  = $adv['contraintePrix'];
          }
        
          $this->create_time     = $adv['create_time'];
          $this->ic_active       = $adv['ic_active'];
          $this->ic_extranet     = $adv['ic_extranet'];
          $this->category        = $adv['category'];
          $this->timestamp       = $adv['timestamp'];
          $this->litigation_time = $adv['litigation_time'];
          
          $this->client_id   = $adv['client_id'];

          $_SESSION['login'] = $login;
          $_SESSION['pass']  = $pass;
          $_SESSION['ip']    = $ip;
          $_SESSION['id']    = $adv['id'];
		  
		  
			
			

          // Login réussi, on logge l'action !
          if ($this->active) {   
            $this->handle->query('UPDATE extranetusers SET c = 1 WHERE id = \'' . $this->handle->escape($this->id) . '\'', __FILE__, __LINE__);
            ExtranetLog($this->handle, $login, $ip, 'Identification de l\'utilisateur (' . $pass . ')');
          }
          else {
            ExtranetLog($this->handle, $login, $ip, 'Compte utilisateur inactif');
          }
        }
        else {
          ExtranetLog($this->handle, $login, $ip, 'Erreur lors de l\'identification - données soumises incorrectes');
        }
      }
      else {
        ExtranetLog($this->handle, $login, $ip, 'Erreur lors de l\'identification - données soumises incorrectes');
      }
    }
    
    return $ret;
  }

  
  
  /* Obtenir l'adresse IP utilisateur
     o : référence chaîne adresse ip */
  public function getIP() {
    return $_SERVER['REMOTE_ADDR'];
  }

  public function getInfosFromId($idUser) {

    $query = "select e.id, a.actif, a.nom1 as name, a.email, a.adresse1, a.adresse2, a.ville, a.cp, a.pays, a.contact, a.url, a.tel1, a.tel2, a.fax1, a.fax2, a.parent, a.create_time, a.ic_active, a.ic_extranet, a.category, a.timestamp from extranetusers e inner join advertisers a on  e.id = a.id  where e.id = ".$idUser." AND a.deleted != 1";

    if ($result = $this->handle->query($query)){

      if ($this->handle->numrows($result, __FILE__, __LINE__) == 1) {
        $record = $this->handle->fetchAssoc($result);
        return $record;
        
      } else return false;
    } else return false;
  }

}

