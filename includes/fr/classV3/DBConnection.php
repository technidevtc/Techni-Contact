<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de cr�ation : 20 d�cembre 2004

 Mises � jour :

       31 mai 2005 : - suppression r�f�rence param�tre m�thode query
                     + stockage contenu derni�re requ�te effectu�e
                     + Log �chec connexion et suppression du message d'erreur visible
                     + gestion fichier et ligne fonction appelante
                     
       28 juin 2005 : + ajout m�thode listing tables d'une bdd

 Fichier : /includes/classV2/DBConnection.php4
 Description : Interface connexion Mysql

/=================================================================*/

require(LOGS . 'sql.php');

class DBConnection
{
	/* H�te de connexion */
	var $host = 'localhost';
	
	/* Nom d'utilisateur */
	var $login = 'technico';
	
	/* mot de passe */
	var $pass = 'os2GL72yOF6wBl6m';
	
	/* Base de donn�es */
	var $dbName = 'technico-test';
	
	/* Requ�te ex�cut�e */
	var $query = '';
	
	/* Handle connexion */
	var $handle = NULL;
	
	/* Constructeur, ouvre une connexion au SGBDR
	i : fichier source, ligne source */
	function __construct($file = '', $line = '') {
		if ((!$this->handle = & mysql_connect($this->host, $this->login, $this->pass)) || !mysql_select_db($this->dbName, $this->handle)) {
			echo mysql_error();
			SQLLog('Impossible d\'�tablir une connexion au SGBD / s�lectionner la base de conn�es concern�e - Fichier : ' . $file . ' - Ligne : ' . $line);
			exit();
		}
	}
	
	function __destruct() {
	}
	/* Ex�cuter une requ�te sql
	i : la cha�ne requ�te sql � ex�cuter
	i : fichier source, ligne source
	o : r�f�rence r�sultat requ�te ou false si erreur */
	function & query($query, $file = '', $line = '', $can_die = true) {
		$ret = false;
		if($this->handle != NULL) {
			$this->query = $query;
			$start = microtime(true);
			if(!($ret = & mysql_query($query, $this->handle))) {
				$errorstring = date("[Y-m-d H:i:s]") . " " . $query . " => " . mysql_error() . " - Fichier : " . $file . " - Ligne : " . $line;
				if (DEBUG)
					print $errorstring;
				SQLLog($errorstring);
				/*$headers  = "From: Techni-Contact Bug Tracker <web@techni-contact.com>\r\n";
				$headers .= "MIME-Version: 1.0\n";
				$headers .= "Content-type: text/html\n";
				$subject = "[www.Techni-Contact.com] - Erreur Fatale MySQL";
				$content = $query . ' => ' . mysql_error() . ' - Fichier : ' . $file . ' - Ligne : ' . $line;
				mail("frederic.morange@hook-network.com", $subject, $content, $headers);*/
				if ($can_die) {
					die('Erreur fatale mysql');
				}
				else
					print mysql_error();
			}
			$time = (microtime(true)-$start)*1000;
			if ($time > 1000) {
				$fh = fopen(LOGS."mysql-query-time.log", "a");
				fwrite($fh, "\"".date("d/m/Y - H:i:s")."\";\"".str_replace('"','""',$query)."\";\"".sprintf("%.03f",$time)."\";\"".$file."\";\"".$line."\"\n");
				fclose($fh);
			}
			//SQLLog($query.";");
		}
		return $ret;
	}
	
	/* Retourne le nombre de r�sultats d'une requ�te sql
	i : r�f�rence r�sultat requ�te
	i : fichier source, ligne source
	o : le nombre de r�ponses ou -1 si erreur */
	function numrows(& $result, $file = '', $line = '') {
		$ret = mysql_num_rows($result);
		if($ret == -1) {
			SQLLog($this->query . ' => ' . mysql_error() . ' - Fichier : ' . $file . ' - Ligne : ' . $line);
		}
		return $ret;
	}
	
	/* Retourne le nombre d'enregistrements affect�s par un insert / update / delete
	i : fichier source, ligne source
	o : le nombre d'enregistrements affect�s ou -1 si erreur */
	function affected($file = '', $line = '') {
		$ret = mysql_affected_rows($this->handle);
		if($ret == -1) {
			SQLLog($this->query . ' => ' . mysql_error() . ' - Fichier : ' . $file . ' - Ligne : ' . $line);
		}
		return $ret;
	}
	
	/* Retourner une ligne de r�sultat
	i : r�f�rence sur le r�sultat de la requ�te
	o : r�f�rence sur la ligne de r�sultat ou false si erreur / pas de r�sultat */
	function & fetch(& $result, $kind = 'num') {
		if ($kind === 'assoc') return mysql_fetch_assoc($result);
		elseif ($kind === 'both') return mysql_fetch_array($result);
		else return mysql_fetch_row($result);
	}
	
	/* Retourner une ligne de r�sultat
	i : r�f�rence sur le r�sultat de la requ�te
	o : r�f�rence sur la ligne de r�sultat ou false si erreur / pas de r�sultat */
	function & fetchArray(& $result, $kind = 'both') {
		if ($kind === 'assoc') return mysql_fetch_assoc($result);
		elseif ($kind === 'num') return mysql_fetch_row($result);
		else return mysql_fetch_array($result);
	}

	function & fetchAssoc(& $result) {
		return mysql_fetch_assoc($result);
	}
	
	/* Fermer la connexion au SGBDR */
	function close() {
		if($this->handle) {
			mysql_close($this->handle);
			$this->handle = NULL;
			$this->query  = '';
		}
	}
	
	/* Echapper une chaine de caract�res
	i : cha�ne � prot�ger
	o : r�f�rence sur la cha�ne prot�g�e */
	function & escape($string) {
		$ret = & $string;
		if($this->handle != NULL) {
			$ret = & mysql_real_escape_string($string, $this->handle);
		}
		return $ret;
	}
	
	/* Retourne un tableau des tables pr�sentes
	o : r�f tableau tables */
	function & getTables($file = '', $line = '') {
		$ret = array();
		if($this->handle != NULL) {
			if($result = mysql_list_tables($this->dbName, $this->handle)) {
				while($row = & $this->fetch($result)) {
					$ret[] = & $row;
				}
			}
			else {
				SQLLog('Listing tables de ' . $this->dbName . ' => ' . mysql_error() . ' - Fichier : ' . $file . ' - Ligne : ' . $line);
			}
		}
		return $ret;
	}

}

?>
