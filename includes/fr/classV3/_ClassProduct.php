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

/*
 * Product's are manipulated in hastable to be naturally AJAX usable and very memory efficient
 * example :
	{
		"id product 1" : {
			"id" : data,
			"idAdvertiser" : data,
			..
			"field n" : data n,
			"nbrefs" : n,
			"references" : {
				"id ref 1" : {
					"id" : data,
					"idProduct" : data,
					"label" : data,
					..
					"field n" : data
				},
				"id ref 1" : { data },
				..
				"id ref n" : { data }
			}
		},
		"id product 2" : { data },
		..
		"id product n" : { data n }
	}
 */

class ProductsManager {

	private $handle = null;
	private $pdts = null;
	
	// Constructor
	function __construct(& $handle) {
		$this->handle = $handle;
	}
	
	public function GetProductsByIDs($PdtIDs) {
		if (empty($PdtIDs)) return false;
		try {
			$QueryIDs = implode(",", $PdtIDs);
			
			// Loading the Product's main params
			$query = "select products.*, products_fr.* from products, products_fr where products.id = products_fr.id and products.id in (" . $QueryIDs .")";
			if (!($res = $this->handle->query($query, __FILE__, __LINE__, false))) {
				throw new Exception("MySQL : Error while Loading the products " . $QueryIDs . ".");
			}
			$this->pdts = array();
			while ($rec = $this->handle->fetchAssoc($res)) {
				$this->pdts[$rec["id"]] = $rec;
				$this->pdts[$rec["id"]]["nbrefs"] = 0;
				$this->pdts[$rec["id"]]["references"] = array();
			}
			
			// Filling the references list for each product
			$this->FillReferencesForProductsIds($PdtIDs);
			
			return $this->pdts;
		}
		catch (Exception  $e) {
			echo "Error : " . $e->getMessage() . "\n";
		}
	}
	
	public function GetProductsByReferencesIDs ($RefIDs) {
		if (empty($RefIDs)) return false;
		try {
			// Loading the corresponding Product's ID
			$QueryIDs = implode(",", $RefIDs);
			$query = "select idProduct from references_content where id in (" . $QueryIDs .")";
			if (!($res = $this->handle->query($query, __FILE__, __LINE__, false))) {
				throw new Exception("MySQL : Error while Loading the references " . $QueryIDs . ".");
			}
			
			// Setting the Products'ID array
			$PdtIDs = array();
			while ($rec = $this->handle->fetch($res)) $PdtIDs[] = $rec[0];
			
			// We return the result of the adapted Product's loading function
			return $this->GetProductsByIDs($PdtIDs, true);
			
		}
		catch (Exception  $e) {
			echo "Error : " . $e->getMessage() . "\n";
		}
	}
	
	public function GetCompleteReferencesByReferencesID ($RefIDs) {
		if (empty($RefIDs)) return false;
		try {
			$QueryIDs = implode(",", $RefIDs);
			$res = $this->handle->query("
			SELECT
				p.id AS idProduct, p.idTC AS pdt_idTC, p.ean, p.warranty, p.shipping_fee, p.contrainteProduit AS quantity_min,
				pfr.name, pfr.fastdesc, pfr.ref_name, pfr.descc, pfr.descd, pfr.delai_livraison AS delivery_time,
				rcols.content AS ccols_headers,
        rc.id AS idTC, rc.label, rc.label_long, rc.price, rc.price2, rc.refSupplier, rc.unite, rc.idTVA, rc.ecotax, rc.classement, rc.content AS ccols_content,
				a.id AS advID, a.category AS adv_cat, a.delai_livraison AS adv_delivery_time, a.contraintePrix AS adv_amount_min, a.warranty AS adv_warranty, a.shipping_fee AS adv_shipping_fee
			FROM products p
			INNER JOIN products_fr pfr ON p.id = pfr.id AND pfr.active = 1
			INNER JOIN advertisers a ON p.idAdvertiser = a.id AND a.actif = 1
      INNER JOIN references_cols rcols ON p.id = rcols.idProduct
			INNER JOIN references_content rc ON p.id = rc.idProduct AND rc.id in (".$QueryIDs.")", __FILE__, __LINE__, false);
			
			$refs = array();
			while ($ref = $this->handle->fetchAssoc($res)) {
        $ccols_headers = mb_unserialize($ref["ccols_headers"]);
        $ccols_headers = array_slice($ccols_headers, 3, -5); // get only custom cols headers
        $ccols_content = mb_unserialize($ref["ccols_content"]);
        
        for($k=0, $l=count($ccols_headers); $k<$l; $k++)
          $ref["customCols"][$ccols_headers[$k]] = $ccols_content[$k];
        
				$refs[$ref["idTC"]] = $ref;
			}
			
			return $refs;
		}
		catch (Exception  $e) {
			echo "Error : " . $e->getMessage() . "\n";
		}
	}
	
	private function FillReferencesForProductsIds($PdtIDs) {
		if (empty($PdtIDs)) return false;
		try {
			$QueryIDs = implode(",", $PdtIDs);
			
			$query = "select * from references_content where idProduct in (" . $QueryIDs .")";
			if (!($res = $this->handle->query($query, __FILE__, __LINE__, false))) {
				throw new Exception("MySQL : Error while Loading the references of the products " . $QueryIDs . ".");
			}
			while ($rec = $this->handle->fetchAssoc($res)) {
				$this->pdts[$rec["idProduct"]]["references"][$rec["id"]] = $rec;
				$this->pdts[$rec["idProduct"]]["nbrefs"]++;
			}
		}
		catch (Exception $e) {
			echo "Error : " . $e->getMessage() . "\n";
		}
	}
	
	
}












/*



class Product {

	private $handle = null;
	private $exists = false;
	private $data = array();
	private $references = array();

	private $DBTableStruct = array(
		"products" => array(
			"fields" => array(
				"id" => array("type" => "int", "generated" => "self", "clamped" => array(1,16777215)),
				"idAdvertiser" => array("type" => "int"),
				"idTC" => array("type" => "int", "generated" => "generateIDTC"),
				"timestamp" => array("type" => "int", "get" => true, "set" => false, "generated" => "time"),
				"cg" => array("type" => "int", "clamped" => array(0,1)),
				"ci" => array("type" => "int", "clamped" => array(0,1)),
				"cc" => array("type" => "int", "clamped" => array(0,1)),
				"refSupplier" => array("type" => "string"),
				"price" => array("type" => "string"),
				"price2" => array("type" => "string"),
				"unite" => array("type" => "int"),
				"marge" => array("type" => "float"),
				"idTVA" => array("type" => "int"),
				"contrainteProduit" => array("type" => "int"),
				"tauxRemise" => array("type" => "text"),
				"similar_items" => array("type" => "text")
			),
			"index" => array(
				"id" => "key",
				"idAdvertiser" => "index"
			)
		),

		"products_fr" => array(
			"fields" => array(
				"name" => array("type" => "string"),
				"fastdesc" => array("type" => "string"),
				"ref_name" => array("type" => "string", "filtered" => "Google"),
				"alias" => array("type" => "string"),
				"keywords" => array("type" => "string"),
				"descc" => array("type" => "text"),
				"descd" => array("type" => "text"),
				"delai_livraison" => array("type" => "string"),
				"active" => array("type" => "int", "clamped" => array(0,1))
			),
			"index" => array(
				"id" => "key",
				"idAdvertiser" => "index",
				"name" => "fulltext",
				"alias" => "fulltext",
				"keywords" => "fulltext"
			)
		)
	);

	// Constructor
	function __construct(& $handle, $id = null) {
		$this->handle = $handle;
		if ($id == null) {
			$this->data["id"] = $this->GenerateID();
		}
		else {
			$this->data["id"] = $id;
			$this->Load();
		}
	}

	function __destruct() {
		$this->Save();
	}

	// Overloading set/get functions
	public function __set($name, $value) {
		$this->data[$name] = $value;
	}

	public function __get($name) {
		if (array_key_exists($name, $this->data))
			return $this->data[$name];
	}

	private function Load() {

		$fields = $tables = $joins = $joinsquery = array();
		foreach ($this->DBTableStruct as $table => $data) {
			$tables[] = $table;
			$fields[] = array_keys($data["fields"]);

			foreach ($fields as $k => $v)
				$fields[$k] = $table.".".$v;

			foreach($data["index"] as $field => $value)
				if ($value == "key") $joins[$field][] = $table;
		}

		foreach ($joins as $field => $tables) {
			$tablecount = count($tables);
			for ($i = 1; $i < $tablecount; $i++)
				$joinsquery[] = $tables[0].".".$field . " = " . $tables[$i].".".$field;
		}

		$this->query = "select " . implode(",", $fields) . " from " . implode(",", $tables) . " where " . implode(" and ", $joinsquery);

		if ($this->handle->numrows($res, __FILE__, __LINE__) == 0) {
			$this->exists = false;
		}
		else {
			$this->exists = true;
			$rec = & $this->handle->fetchAssoc($res);

			foreach ($rec as $k => $v)
				$this->data[$k] = $v;
		}
	}

	private function Save() {

		try {
			if (!$this->exists) {
				if (empty($this->data["id"])) $this->generateID();
				foreach ($this->DBTableStruct as $table => $data) {
					$keys = array_keys($data["fields"]);
					$values = array();

					foreach($keys as $key) {
						$values[] = "'" . $this->handle->escape($this->data[$key]) . "'";
					}
					$this->query = "insert into " . $table . " (" . implode(",", $keys) . ") values (" . implode(",", $values) . ")";

					if (!$this->handle->query($this->query, __FILE__, __LINE__, false)) {
						throw new Exception("MySQL : Error while adding the product " . $id . ".");
					}
				}
				$this->exists = true;
			}
			else {
				foreach ($this->DBTableStruct as $table => $data) {
					$keys = array_keys($data["fields"]);
					$sets = array();

					foreach($keys as $key) {
						$sets[] = $key . " = '" . $this->handle->escape($this->data[$key]) . "'";
					}

					$this->query = "update " . $table . " set " . implode(",", $sets);

					if (!$this->handle->query($this->query, __FILE__, __LINE__, false)) {
						throw new Exception("MySQL : Error while updating the product " . $id . ".");
					}
				}
			}
		}
		catch ($e) {
			echo "Error : " . $e->getMessage() . "\n";
			return false;
		}

		return true;
	}

	private function GenerateID() {
		do {
			$id = mt_rand(1, 999999999);
			$result = & $this->handle->query("select id from products where id = " . $id, __FILE__, __LINE__);
		}
		while ($this->handle->numrows($result, __FILE__, __LINE__) >= 1);

		$this->data["id"] = $id;
	}

	private function LoadReferences() {


	}

	private function SaveReferences() {
	}

	static public function GetProductsBy

}
*/
?>