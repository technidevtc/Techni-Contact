<?php

abstract class BaseTable {

	protected $db = null;
	protected $existsInDB = false;
	protected $altered = false;
	protected $tables;
	protected $linkedTables;
	protected $fields;
	protected $linkedFields;
	
	protected $id;
	
	protected static function get ($tables, $arg_list) {
		$db = DBHandle::get_instance();
		$table = $tables[0];
		
		$orders = array();
		$filters = array();
		$selectedFieldsOK = array();
		for ($i = 0, $arg_count = count($arg_list); $i < $arg_count; $i++) {
			if (preg_match("/(\S*)\s*=\s*(.+)/", $arg_list[$i], $matches)) { // arg is where x = a
				if (isset($table["fields"][$matches[1]]))
					$filters[] = $matches[1]." = ".$matches[2];
			}
			elseif (preg_match("/(\S*)\s+in\s+(\(.+\))/i", $arg_list[$i], $matches)) { // arg is where x in (a, b, ..)
				if (isset($table["fields"][$matches[1]]))
					$filters[] = $matches[1]." in ".$matches[2];
			}
			elseif (preg_match("/(\S*)\s+like\s+(.+)/i", $arg_list[$i], $matches)) { // arg is where x like a
				if (isset($table["fields"][$matches[1]]))
					$filters[] = $matches[1]." like ".$matches[2];
			}
			elseif (preg_match("/order by \s*(.+)/i", $arg_list[$i], $matches)) { // arg is order by x
				$orderFields = explode(",", $matches[1]);
				foreach($orderFields as $orderField) {
					$orderField = explode(" ", trim($orderField));
					if (isset($table["fields"][$orderField[0]]))
						$orders[] = $orderField[0]." ".(!strcasecmp($orderField[1],"desc") ? "desc" : "asc");
				}
			}
			else {
				$selectedFields = explode(",", $arg_list[$i]);
				foreach($selectedFields as $selectedField) {
					$selectedField = trim($selectedField);
					if (isset($table["fields"][$selectedField]))
						$selectedFieldsOK[] = $selectedField;
				}
			}
		}
		$filters = implode(" AND ", $filters);
		if (!empty($filters))
			$filters = " WHERE ".$filters;
		
		$orders = implode(",", $orders);
		if (!empty($orders))
			$orders = " ORDER BY ".$orders;
		
		if (empty($selectedFieldsOK))
			$selectedFieldsOK = array_keys($table["fields"]);
		$selectedFieldsOK = "`".implode("`,`", $selectedFieldsOK)."`";
		
		$res = $db->query("SELECT ".$selectedFieldsOK." FROM ".$table["name"].$filters.$orders, __FILE__, __LINE__);
		
		$advs = array();
		while ($adv = $db->fetchAssoc($res))
			$advs[] = $adv;
		
		return $advs;
	}
	
	protected static function delete($id, $tables, $linkedTables) {
		$db = DBHandle::get_instance();
		foreach($tables as $table)
			$db->query("DELETE FROM `".$table["name"]."` WHERE `".$table["key"]."` = '".$db->escape($id)."'", __FILE__, __LINE__);
		
		foreach($linkedTables as $table)
			$db->query("DELETE FROM `".$table["name"]."` WHERE `".$table["key"]."` = '".$db->escape($id)."'", __FILE__, __LINE__);
		
		return true;
	}
	
	protected function __construct($id) {
		$this->db = DBHandle::get_instance();
		if (!empty($id)) {
			$this->id = $id;
			$this->load();
		}
		else
			$this->id = null;
	}
	
	protected function __destruct() {
		/*if ($this->altered) {
			$this->save();
		}*/
	}
	
	public function __set($name, $value) {
		print " setting $name to $value -> ";
		if (isset($this->fields[$name])) {
			print " OK! | ";
			if ($this->fields[$name] != $value) {
				$this->fields[$name] = $value;
				$this->altered = true;
			}
			return true;
		}
		else return false;
	}

	public function __get($name) {
		print " getting $name -> ";
		if (isset($this->fields[$name])) {
			print " OK! | ";
			return $this->fields[$name];
		}
		else return "hoh";
	}
	
	public function generateId() {
		do {
			$id = mt_rand(1, $this->IdMax);
			$res = $this->db->query("SELECT ".$this->tables[0]["key"]." FROM ".$this->tables[0]["name"]." WHERE ".$this->tables[0]["key"]." = ". $id, __FILE__, __LINE__);
		}
		while ($this->db->numrows($res, __FILE__, __LINE__) == 1);
		
		$this->id = $id;
		$this->altered = true;
	}
	
	public function getLinks($linkedTableName) {
		if (isset($this->linkedFields[$linkedTableName])) {
			return $this->linkedFields[$linkedTableName];
		}
		else return false;
	}
	
	public function clearLinks($linkedTableName) {
		if (isset($this->linkedFields[$linkedTableName])) {
			$this->linkedFields[$linkedTableName] = array();
			$this->altered = true;
		}
		else return false;
	}
	
	public function addLink($linkedTableName, $id) {
		if (isset($this->linkedFields[$linkedTableName]) && !in_array($id, $this->linkedFields[$linkedTableName])) {
			$this->linkedFields[$linkedTableName][] = $id;
			$this->altered = true;
			return true;
		}
		else return false;
	}
	
	public function delLink($linkedTableName, $id) {
		if (isset($this->linkedFields[$linkedTableName]) && in_array($id, $this->linkedFields[$linkedTableName])) {
			unset($this->linkedFields[$linkedTableName][array_search($id, $this->linkedFields[$linkedTableName])]);
			$this->altered = true;
			return true;
		}
		else return false;
	}
	
	
	
	public function create() {
		$this->existsInDB = false;
		$this->generateId();
		
		$this->fields = array();
		$this->linkedFields = array();
		foreach ($this->tables as $table) {
			if (isset($table["join"]))
				unset($table["fields"][$table["key"]]);
			foreach($table["fields"] as $fieldName => $dftValue)
				$this->fields[$fieldName] = $dftValue;
		}
		foreach ($this->linkedTables as $linkedTable) {
			unset($linkedTable["fields"][$linkedTable["key"]]);
			$this->linkedFields[$linkedTable["linkname"]] = array();
			/*foreach($linkedTable["fields"] as $fieldName => $dftValue)
				if (!isset($linkedFields[$fieldName])) $linkedFields[$fieldName] = $dftValue;
			$this->linkedFields[$linkedTable["name"]] = */
		}
		
		$this->altered = false;
	}
	
	public function load() {
		$tablesFields = array();
		$tablesJoins = array();
		$mainTable = $this->tables[0];
		foreach ($this->tables as $table) {
			if (isset($table["join"])) {
				unset($table["fields"][$table["key"]]);
				$tablesJoins[] = strtoupper($table["join"])." JOIN `".$table["name"]."` ON `".$mainTable["name"]."`.`".$mainTable["key"]."` = `".$table["name"]."`.`".$table["key"]."`";
			}
			$tablesFields[] = "`".$table["name"]."`.`".implode("`,`".$table["name"]."`.`", array_keys($table["fields"]))."`";
		}
		$sql = "SELECT ".implode(",",$tablesFields)." FROM `".$mainTable["name"]."`\n".implode("\n",$tablesJoins)."\n WHERE `".$mainTable["name"]."`.`".$mainTable["key"]."` = '".$this->id."'";
		
		$res = $this->db->query($sql, __FILE__, __LINE__);
		if ($this->db->numrows($res, __FILE__, __LINE__) == 1) {
			$data = $this->db->fetchAssoc($res);
			foreach($this->tables as $table)
				foreach($table["fields"] as $fieldName => $dftValue)
					$this->fields[$fieldName] = isset($data[$fieldName]) ? $data[$fieldName] : $dftValue;
			$this->existsInDB = true;
		}
		else
			$this->existsInDB = false;
		
		// we'll only get the first field which is not the key
		foreach ($this->linkedTables as $linkedTable) {
			unset($linkedTable["fields"][$linkedTable["key"]]);
			$sql = "SELECT `".$linkedTable["name"]."`.`".implode("`,`".$linkedTable["name"]."`.`", array_keys($linkedTable["fields"]))."` FROM ".$linkedTable["name"]." WHERE `".$linkedTable["name"]."`.`".$linkedTable["key"]."` = '".$this->id."'";
			$res = $this->db->query($sql, __FILE__, __LINE__);
			$this->linkedFields[$linkedTable["linkname"]] = array();

			while($linkedFields = $this->db->fetchAssoc($res)) {
				//foreach($linkedTable["fields"] as $fieldName => $dftValue)
				//	if (!isset($linkedFields[$fieldName])) $linkedFields[$fieldName] = $dftValue;
				$this->linkedFields[$linkedTable["linkname"]][] = current($linkedFields);
			}
		}
		
		$this->altered = false;
		return $this->existsInDB;
	}
	
	public function save() {
		$fields = array();
		if (!$this->existsInDB) {
			$tablesNames = array();
			$tablesFields = array();
			$tablesJoins = array();
			$mainTable = $this->tables[0];
			foreach ($this->tables as $table) {
				$tablesNames[] = "`".$table["name"]."`";
				unset($table["fields"][$table["key"]]);
				if (isset($table["join"]))
					$tablesJoins[] = strtoupper($table["join"])." JOIN `".$table["name"]."` ON `".$mainTable["name"]."`.`".$mainTable["key"]."` = `".$table["name"]."`.`".$table["key"]."`";
				$tableFields = array();
				foreach ($table["fields"] as $fieldName => $dftValue)
					$tableFields[] = "`".$table["name"]."`.`".$fieldName."` = '".$this->db->escape($this->fields[$fieldName])."'";
				$tablesFields[] = implode(",",$tableFields);
			}
			$queries[] = "UPDATE `".$mainTable["name"]."` ".implode("\n",$tablesJoins)."\nSET ".implode(",",$tablesFields)."\nWHERE `".$mainTable["name"]."`.`".$mainTable["key"]."` = '".$this->id."'";
		}
		else {
			foreach ($this->tables as $table) {
				$tableFields = array();
				foreach ($table["fields"] as $fieldName => $dftValue)
					$tableFields[] = $fieldName==$table["key"] ? "'".$this->id."'" : "'".$this->db->escape($this->fields[$fieldName])."'";
				$queries[] = "INSERT INTO `".$table["name"]."`(`".implode("`,`",array_keys($table["fields"]))."`)\nVALUES(".implode(",",$tableFields).")";
			}
		}
		
		// each linkedFields table only contains one simple array of linked id's
		// we only have the first field setted, so we complete with defaults values the others if there is any (unlikely)
		foreach($this->linkedTables as $linkedTable) {
			$queries[] = "DELETE FROM `".$linkedTable["name"]."` WHERE `".$linkedTable["key"]."` = ".$this->id;
			foreach($this->linkedFields[$linkedTable["linkname"]] as $linkedFieldsLine) {
				$tableFields = array();
				$linkedFieldCount = 0;
				foreach ($linkedTable["fields"] as $fieldName => $dftValue) {
					if ($fieldName==$linkedTable["key"])
						$tableFields[] = $this->id;
					else {
						if ($linkedFieldCount == 0)
							$tableFields[] = $this->db->escape($linkedFieldsLine);
						else
							$tableFields[] = $dftValue;
						$linkedFieldCount++;
					}
				}
				$queries[] = "INSERT INTO `".$linkedTable["name"]."`(`".implode("`,`",array_keys($linkedTable["fields"]))."`) VALUES(".implode(",",$tableFields).")";
			}
		}
		//pp($queries);
		
		/*foreach ($queries as $query)
			$this->db->query($query, __FILE__, __LINE__, false);*/
		
		$this->existsInDB = true;
	}
	
}

?>
