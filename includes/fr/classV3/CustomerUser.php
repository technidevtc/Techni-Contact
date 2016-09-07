<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 20 décembre 2004

 Mises à jour :

       31 mai 2005 : = nouveau gestionnaire de rangs
                     + gestion session sécurisée avec contrôle adresse ip

 Fichier : /includes/classV2/ManagerUser.php4
 Description : Classe utilisateur manager

/=================================================================*/

session_name('customer');
session_start();

/*$oldid = session_id();
session_regenerate_id();
unlink('/tmp/sess_' . $oldid);*/

class CustomerUser
{
	/* id */
	var $id = 0;

	/* Login = Email*/
	var $logged = false;

	/* Handle connexion */
	var $handle = NULL;

	/* Handle sur une panier */
	var $basket = NULL;

	/* page où aller après une action automatique (tel que le login) */
	var $next_page = '';

	/* numéro du devis en cours de commande */
	var $cmd_devis = 0;

	/* Constructeur, set la session à utiliser et initialise les variables de l'objet
	Par défaut on considère qu'il est impossible de changer les données de la super globale $_SESSION sans avoir
	un accés aux données de la machine via une console
	i : référence sur la connexion au SGBDR */
	function CustomerUser(& $handle, & $handle_basket)
	{
		$this->handle = & $handle;
		$this->basket = & $handle_basket;
		
		if (!isset($_SESSION['started']))
		{
			
			if (isset($_COOKIE['session_id']) && $this->basket->setID($_COOKIE['session_id']) && $this->basket->getExist())
			{
				session_destroy();
				session_id($_COOKIE['session_id']);
				session_start();
				$this->basket->doTimestamp();
			}
			else
			{
				session_regenerate_id();
				$this->basket->setID(session_id(), false);
				$this->basket->create();
				setCookie('session_id',  session_id(), time() + 24 * 3600 * 30, '/', DOMAIN);
			}
			$_SESSION = array();
			$_SESSION['started'] = true;
			$_SESSION['URL'] = URL;
		}
		else
		{
			$this->basket->setID(session_id());
			$this->logged = isset($_SESSION['logged']);
			
			if (empty($_SESSION['URL']) || $_SESSION['URL'] != URL) $this->delog();
			$_SESSION['URL'] = URL;
			
			$this->next_page = isset($_SESSION['next_page']) ? $_SESSION['next_page'] : '';
			
			if ($this->logged)
			{
				$this->cmd_devis = isset($_SESSION['cmd_devis']) ? $_SESSION['cmd_devis'] : 0;
				
				if ($_SESSION['ip'] == $this->getIP() || $this->getIP() == SERVER_IP)
					$this->id = $_SESSION['id'];
				else
				{
					@session_destroy();  // Ip a changé depuis la dernière session
					$this->logged = false;
				}
				
				if (!$this->basket->getHasCoord()) $this->basket->affectClient($this->id);
			}
			setCookie('session_id',  session_id(), time() + 24 * 3600 * 30, '/', DOMAIN);
		}
	}

	function isLogged() { return $this->logged; }
	function getID() { return $this->id; }

	function setNextPage($page) { $this->next_page = $page; $_SESSION['next_page'] = & $this->next_page; return true; }
	function getNextPage() { return $this->next_page; }

	function setCmdDevis($devis) { $this->cmd_devis = $devis; $_SESSION['cmd_devis'] = & $this->cmd_devis; return true; }
	function getCmdDevis() { return $this->cmd_devis; }

	/* Identification utilisateur
	i : login
	i : pass
	o : true si identifié, false si erreur */
	function login($login = '', $pass = '')
	{
		$ret = false;
		
		if ($this->logged)
		{
			$ret = true;
		}
		elseif ($login != '' && $pass != '')
		{
			$pass = & md5($pass);
			
			if ($result = & $this->handle->query('select id from clients where login = \'' . $this->handle->escape($login) . '\' and pass = \'' . $pass . '\' and actif = 1', __FILE__, __LINE__))
			{
				if ($this->handle->numrows($result, __FILE__, __LINE__) == 1)
				{
					
					$ret = true;
					
					$record = & $this->handle->fetch($result);
					$this->logged = true;
					$this->id     = & $record[0];
					
					/* Si une session existait mais que ce n'est pas le même client, on réaffecte les données client au panier,
					on unlock le panier, et on détruit la variable session indiquant que la commande en cours provenait d'un devis.
					Si c'est un premier log de la session, mais que le client n'est pas le même que le dernier à avoir utiliser le panier,
					on réaffecte les données client au panier, et on unlock le panier. */
					if (isset($_SESSION['id']))
					{
						if ($_SESSION['id'] != $this->id)
						{
							$this->basket->affectClient($this->id);
							$this->basket->unlock();
							if ($this->cmd_devis != 0)
							{
								$this->cmd_devis = 0;
								unset($_SESSION['cmd_devis']);
							}
							$_SESSION['id'] = & $this->id;
						}
					}
					else
					{
						if ($this->basket->getOldClientID() != $this->id)
						{
							$this->basket->affectClient($this->id);
							$this->basket->unlock();
						}
						$_SESSION['id'] = & $this->id;
					}
					$_SESSION['logged'] = true;
					$_SESSION['ip']     = & $this->getIP();
					
				}
			}
		}
		return $ret;
	}

	function delog()
	{
		$this->logged = false;
		$this->id = -1;
		
		unset($_SESSION['logged']);
		unset($_SESSION['ip']);
	}


	/* Obtenir l'adresse IP utilisateur
	o : référence chaîne adresse ip */
	function & getIP()
	{
		return $_SERVER['REMOTE_ADDR'];
	}


	function & loadCoord($kind = 'both')
	{
		if ($this->id && ($result = & $this->handle->query('select titre, nom, prenom, societe, fonction, nb_salarie, secteur_activite, code_naf, num_siret, adresse, complement, ville, cp, pays, infos_sup, titre_l, nom_l, prenom_l, societe_l, adresse_l, complement_l, ville_l, cp_l, pays_l, infos_sup_l, coord_livraison, tel1, tel2, fax1, fax2, url from clients where id = \'' . $this->id . '\'' , __FILE__, __LINE__)))
		{
			$ret = & $this->handle->fetchArray($result, $kind);  
			return $ret;
		}
		else return false;
	}

	function loadLogin()
	{
		if ($this->id && ($result = & $this->handle->query("select login from clients where id = '$this->id'" , __FILE__, __LINE__)))
		{
			$ret = & $this->handle->fetch($result);
			return $ret[0];
		}
		else return false;
	}

	function updateCoord(& $tab_coord)
	{
		$ret = false;
		
		if ($this->id)
		{
			$query = 'update clients set last_update = \'' . time() .  '\'';
			
			foreach($tab_coord as $coord => $value)
			$query .= ', ' . $coord . ' = \'' . $this->handle->escape($value) . '\'';
			
			$query .= ' where id = \'' . $this->id .'\'';
			
			$this->handle->query($query, __FILE__, __LINE__);
			
			$ret = true;
		}
		
		return $ret;
	}
  
}

?>
