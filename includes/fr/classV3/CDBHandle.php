<?php

class DBHandle
{
	// Class constants
	const host = "localhost";
	const login = "technico";
	const pass = "os2GL72yOF6wBl6m";
	const dbName = "technico-test";

	private $handle = null;
	private $last_query = "";
	
	// Class instance
	private static $instance;

	// Un constructeur privé ; empêche la création directe d'objet
	private function __construct() {
		if (($this->handle = mysql_connect(self::host, self::login, self::pass)) === false) {
			throw new Exception(mysql_error());
		}
		if (!mysql_select_db(self::dbName, $this->handle)) {
				throw new Exception(mysql_error());
		}
	}
	
	public function __destruct() {
		mysql_close($this->handle);
	}
	
	// La méthode singleton
	public static function get_instance () {
		if (!isset(self::$instance)) {
			$c = __CLASS__;
			self::$instance = new $c;
      mysql_set_charset('utf8');
		}
		return self::$instance;
	}

	// Prévient les utilisateurs sur le clônage de l'instance
	public function __clone() {
		trigger_error("Cloning is not allowed", E_USER_ERROR);
	}
	
	public function get_last_query() {
		return $this->last_query;
	}
	
	public function & query($query, $file = "", $line = "") {
		$this->last_query = $query;
//		print $query;
		$start = microtime(true);
		if (($res = mysql_query($query, $this->handle)) !== false) {
			$time = (microtime(true)-$start)*1000;
			if ($time > 1000)
				flog("\"".date("d/m/Y - H:i:s")."\";\"".str_replace('"','""',$query)."\";\"".sprintf("%.03f",$time)."\";\"".$file."\";\"".$line."\"\n", "mysql-slow-queries.log");
			return $res;
		}
		else {
			$errorstring = date("[Y-m-d H:i:s]") . " " . $query . " => " . mysql_error() . " - Fichier : " . $file . " - Ligne : " . $line;
			//print $errorstring;
			throw new Exception($errorstring);
			return false;
		}
	}

	public function numrows(&$res, $file = "", $line = "") {
		if (($num = mysql_num_rows($res)) !== false) {
			return $num;
		}
		else {
			$errorstring = date("[Y-m-d H:i:s]") . " " . $query . " => " . mysql_error() . " - Fichier : " . $file . " - Ligne : " . $line;
			//var_dump(debug_backtrace());
			throw new Exception($errorstring);
			return false;
		}
	}

	public function affected($file = "", $line = "") {
		if (($num = mysql_affected_rows($this->handle)) !== false) {
			return $num;
		}
		else {
			$errorstring = date("[Y-m-d H:i:s]") . " " . $query . " => " . mysql_error() . " - Fichier : " . $file . " - Ligne : " . $line;
			throw new Exception($errorstring);
			return false;
		}
	}

	public function & fetch(&$res) {
		return mysql_fetch_row($res);
	}

	public function & fetchAssoc(&$res) {
		return mysql_fetch_assoc($res);
	}

	public function & fetchArray(&$res) {
		return mysql_fetch_array($res);
	}
	
	public function & escape($string) {
		return mysql_real_escape_string($string, $this->handle);
	}

	public function & getTables($file = "", $line = "") {
		if (($res = mysql_list_tables(self::dbName, $this->handle)) !== false) {
			$tables = array();
			while ($table = $this->fetch($res)) {
				$tables[] = & $table;
			}
			return $tables;
		}
		else {
			$errorstring = date("[Y-m-d H:i:s]") . " Table listing from DB " . self::dbName . " => " . mysql_error() . " - Fichier : " . $file . " - Ligne : " . $line;
			throw new Exception($errorstring);
			return false;
		}
	}
  
  public function set_charset($charset) {
    mysql_set_charset($charset);
  }
  
  public function get_charset() {
    return mysql_client_encoding($this->handle);
  }
	
}
