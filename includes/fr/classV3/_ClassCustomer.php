<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 20 décembre 2004

 Mises à jour :

       31 mai 2005 : = nouveau gestionnaire de rangs
                     + gestion session sécurisée avec contrôle adresse ip

 Fichier : /includes/classV2/ManagerUser.php
 Description : Classe utilisateur manager

/=================================================================*/

/*$oldid = session_id();
session_regenerate_id();
unlink('/tmp/sess_' . $oldid);*/

class Customer
{
	/* Handle connexion */
	var $handle = NULL;
	
	/* Statut de la commande
	0 = n'existe pas dans la DB
	1 = existe pas dans la DB
	*/
	var $statut = 0;
	var $lastErrorMessage = '';
	
	/* Champs de la commande*/
	var $id = 0;
	var $last_update = 0;
	var $login = 0;
	var $pass = 0;
	var $timestamp = 0;
	var $titre = 1;
	var $nom = '';
	var $prenom = '';
	var $fonction = '';
	var $societe = '';
	var $nb_salarie = '';
	var $secteur_activite = '';
  var $secteur_qualifie = '';
	var $code_naf = '';
	var $num_siret = '';
	var $adresse = '';
	var $complement = '';
	var $ville = '';
	var $cp = '';
	var $pays = '';
	var $titre_l = '';
	var $nom_l = '';
	var $prenom_l = '';
	var $societe_l = '';
	var $adresse_l = '';
	var $complement_l = '';
	var $ville_l = '';
	var $cp_l = '';
	var $pays_l = '';
	var $coord_livraison = 0;
	var $tel1 = '';
	var $tel2 = '';
  var $tel_match = '';
	var $fax1 = '';
	var $fax2 = '';
	var $url = '';
	var $activationCode = '';
	var $death = 0;
	var $actif = 0;
	var $email = '';
  var $origin = 'O';
  var $website_origin = 'TC';
  var $code = '';
  var $tva_intra = '';
	
	/* Constructeur, set la session à utiliser
	i : référence sur la connexion au SGBDR */
	function Customer(& $handle, $id = NULL, $init = '') {
		$this->handle = & $handle;
		if ($id != NULL) {
			$this->id = $id;
			$this->Load();
			if ($this->statut == 0 && $init == 'create')
        $this->Create(false);
		} elseif ($init == 'create') {
      $this->Create(true);
    }
	}
	
	function generateID() {
		do {
			$id = mt_rand(1, 999999999);
			$result = & $this->handle->query("select id from clients where id = " . $id, __FILE__, __LINE__);
		}	while ($this->handle->numrows($result, __FILE__, __LINE__) == 1);
		
		$this->id = $id;
	}
	
	function Create($generate_id = true) {
		if ($this->statut == 0) {
			
      if ($generate_id)
        $this->generateID();
			
			// pas de handle->escape() vu que tous les champs sont vides
			$this->last_update = time();
			$this->timestamp = time();
      $this->tel_match = Clients::getTelMatchString($this->tel1.'_'.$this->tel2);
			$query = "insert into clients (id, last_update, login, pass, timestamp, titre, nom, prenom, fonction, societe, nb_salarie, secteur_activite, secteur_qualifie, code_naf, num_siret, adresse, complement, ville, " .
			"cp, pays, titre_l, nom_l, prenom_l, societe_l, adresse_l, complement_l, ville_l, cp_l, pays_l, coord_livraison, tel1, tel2, tel_match, fax1, fax2, url, activationCode, death, actif, email, origin, website_origin, code, tva_intra)" .
			"values (" . $this->id . ", " . $this->last_update . ", '" . $this->login . "', '" . $this->pass . "', " . $this->timestamp . ", '" . $this->titre . "', '" . $this->nom .
			"', '" . $this->prenom . "', '" . $this->fonction . "', '" . $this->societe . "', '" . $this->nb_salarie . "', '" . $this->secteur_activite . "', '" . $this->secteur_qualifie . "', '" . $this->code_naf .
			"', '" . $this->num_siret . "', '" . $this->adresse . "', '" . $this->complement . "', '" . $this->ville . "', '" . $this->cp . "', '" . $this->pays . "', '" . $this->titre_l .
			"', '" . $this->nom_l . "', '" . $this->prenom_l . "', '" . $this->societe_l . "', '" . $this->adresse_l . "', '" . $this->complement_l . "', '" . $this->ville_l .
			"', '" . $this->cp_l . "', '" . $this->pays_l . "', " . $this->coord_livraison . ", '" . $this->tel1 . "', '" . $this->tel2 . "', '" . $this->tel_match . "', '" . $this->fax1 . "', '" . $this->fax2 .
			"', '" . $this->url . "', '" . $this->activationCode . "', " . $this->death . ", " . $this->actif . ", '" . $this->email . "', '". $this->origin . "', '". $this->website_origin . "', '". $this->code ."', '". $this->tva_intra ."')";
			
			if ($this->handle->query($query, __FILE__, __LINE__, false)) $this->statut = 1;
			else $this->lastErrorMessage = "Erreur fatale MySQL lors de la création du client";
		
      $this->updateAddresses();
      //$this->updateContacts();
      
    } else {
			$this->lastErrorMessage = "Le client existe déjà dans la base de donnée";
    }
	}
	
	function Load() {
		$this->lastErrorMessage = '';
		
		$result = & $this->handle->query("select * from clients where id = " . $this->id, __FILE__, __LINE__);
		if ($this->handle->numrows($result, __FILE__, __LINE__) > 0) {
			$record = & $this->handle->fetchAssoc($result);
			foreach($record as $name => $value) $this->$name = $value;
			$this->statut = 1;
		} else {
			$this->statut = 0;
		}
	}
	
	function save() {
		$ret = false;
		
		if ($this->statut == 1) {
			$this->last_update = time();
      $this->tel_match = Clients::getTelMatchString($this->tel1.'_'.$this->tel2);
			$query .= "update clients set last_update = " . $this->last_update .
			", login = '" . $this->handle->escape($this->login).
			"', pass = '" . $this->pass .
			"', timestamp = " . $this->timestamp .
			", titre = '" .$this->titre .
			"', nom = '" . $this->handle->escape($this->nom) .
			"', prenom = '" . $this->handle->escape($this->prenom) .
			"', fonction = '" . $this->handle->escape($this->fonction) .
			"', societe = '" . $this->handle->escape($this->societe) .
			"', nb_salarie = '" . $this->handle->escape($this->nb_salarie) .
			"', secteur_activite = '" . $this->handle->escape($this->secteur_activite) .
      "', secteur_qualifie = '" . $this->handle->escape($this->secteur_qualifie) .
			"', code_naf = '" . $this->handle->escape($this->code_naf).
			"', num_siret = '" . $this->handle->escape($this->num_siret) .
			"', adresse = '" . $this->handle->escape($this->adresse) .
			"', complement = '" . $this->handle->escape($this->complement) .
			"', ville = '" . $this->handle->escape($this->ville) .
			"', cp = '" . $this->cp .
			"', pays = '" . $this->handle->escape($this->pays) .
			"', titre_l = '" . $this->titre_l .
			"', nom_l = '" . $this->handle->escape($this->nom_l) .
			"', prenom_l = '" . $this->handle->escape($this->prenom_l) .
			"', societe_l = '" . $this->handle->escape($this->societe_l) .
			"', adresse_l = '" . $this->handle->escape($this->adresse_l) .
			"', complement_l = '" . $this->handle->escape($this->complement_l) .
			"', ville_l = '" . $this->handle->escape($this->ville_l) .
			"', cp_l = '" . $this->cp_l .
			"', pays_l = '" . $this->handle->escape($this->pays_l) .
			"', coord_livraison = " . $this->coord_livraison .
			", tel1 = '" . $this->handle->escape($this->tel1) .
			"', tel2 = '" . $this->handle->escape($this->tel2) .
			"', tel_match = '" . $this->handle->escape($this->tel_match) .
			"', fax1 = '" . $this->handle->escape($this->fax1) .
			"', fax2 = '" . $this->handle->escape($this->fax2) .
			"', url = '" . $this->handle->escape($this->url) .
			"', activationCode = '" . $this->activationCode .
			"', death = " . $this->death .
			", actif = " . $this->actif .
			", email = '" . $this->handle->escape($this->email) .
			"', origin = '" . $this->handle->escape($this->origin) .
			"', website_origin = '" . $this->handle->escape($this->website_origin) .
      "', code = '" . $this->handle->escape($this->code) .
      "', tva_intra = '" . $this->handle->escape($this->tva_intra) .
			"' where id = " . $this->id;
			
      if ($this->handle->query($query, __FILE__, __LINE__, false))
        $ret = true;
			else
        $this->lastErrorMessage = "Erreur fatale MySQL lors de la sauvegarde du client";
		
    }	else {
			$this->lastErrorMessage = "Le client n'existe pas dans la base de donnée, veuillez le créer avant de pouvoir le sauvegarder";
    }
		
    $this->updateAddresses();
    //$this->updateContacts();
    
		return $ret;
	}
        
   public function updateContacts(){
    /*$q = Doctrine_Query::create()
        ->select()
        ->from('ClientsContacts')
        ->where('client_id = ?', $this->id)
        ->andWhere('num = ?', 0);
    $cal = $q
        ->execute();
        
    foreach (ClientsContacts::$cf2ccf as $cf => $af)
        $cal[0]->$af = $this->$cf;
    $cal[0]->client_id = $this->id;
    
    $cal->save();*/
  }
	
  function updateAddresses() {
    // temp until we don't use 2 kind of client object
    $q = Doctrine_Query::create()
      ->select('*')
      ->from('ClientsAdresses')
      ->where('client_id = ?', $this->id)
      ->andWhere('num = ?', 0);
    if ($this->coord_livraison == 0)
      $q->andWhere('type_adresse = ?', ClientsAdresses::TYPE_DELIVERY);
    $cal = $q
      ->orderBy('type_adresse ASC') // delivery fields type are always < to billing ones
      ->execute();
    
    // create the delivery address if it doesn't exist
    if (!count($cal)) {
      $cal = new Doctrine_Collection('ClientsAdresses');
      $cal[0] = new ClientsAdresses();
      $cal[0]->nom_adresse = "Adresse principale";
      $cal[0]->client_id = $this->id;
    }
    // no specified delivery fields, copy default fields to the main delivery one
    if ($this->coord_livraison == 0) {
      foreach (Clients::$cbf2caf as $cf => $af)
        $cal[0]->$af = $this->$cf;
    // specified delivery infos -> copy "_l" fields to the main delivery address, and the normal fields to the main billing one
    } else {
      // create the billing address if it's not present
      if (!isset($cal[1])) {
        $cal[1] = new ClientsAdresses();
        $cal[1]->nom_adresse = "Adresse de facturation";
        $cal[1]->type_adresse = ClientsAdresses::TYPE_BILLING;
        $cal[1]->client_id = $this->id;
      }
      foreach (Clients::$cdf2caf as $cf => $af)
        $cal[0]->$af = $this->$cf;
      foreach (Clients::$cbf2caf as $cf => $af)
        $cal[1]->$af = $this->$cf;
    }
    
    $cal->save();
  }
  
}
