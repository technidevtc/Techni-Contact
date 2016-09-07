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

// Constants for product_filter
define("PRODUCT_FILTER_ADVERTISER_MASK", 0x00000001);
define("PRODUCT_FILTER_SUPPLIER_MASK", 0x00000002);
define("PRODUCT_FILTER_FIRSTREF_MASK", 0x00000004);

class Export
{
	/* Connection Handle */
	var $handle = NULL;
	
	/* Product's fields */
	var $id = 0;
	var $name = "";
	var $partner_id = 0;
	var $partner_name = "";
	var $nb_pdt = 0;
	var $create_time = 0;
	var $timestamp = 0;
	var $generate_time = 0;
	
	/* products_filter = binary var
		byte 1 = wether we accept advertiser's products
		byte 2 = same but for supplier's
		byte 3 = wether we only get the 1st reference or not
	*/
	var $products_filter = 0;
	var $compulsory_fields = array();
	var $facultative_fields = array();
	var $id_parent = 0;
	
	var $products = array();
	var $families = NULL;
	var $tablelist = NULL;
	var $c_keys = array();
	var $f_keys = array();
	
	var $exist = false;
	var $lastErrorMessage = "";
	
	/* Tableau contenant la liste des taux de tva */
	var $tauxTVA = NULL;
	
	/* Constructor */
	function Export(& $handle, $id = NULL)
	{
		$this->handle = & $handle;
		if ($id != NULL)
		{
			$this->id = $id;
			$this->Load();
		}
	}
	
	function GenerateID()
	{
		do
		{
			$id = mt_rand(1, 999999999);
			$result = & $this->handle->query("select id from exports where id = " . $id, __FILE__, __LINE__);
		}
		while ($this->handle->numrows($result, __FILE__, __LINE__) >= 1);
		
		$this->id = $id;
	}
	
	// TODO copy from parent_id
	function Create($partner_id, $parent_id = 0)
	{
		$query = "select name as partner_name, products_filter, compulsory_fields, facultative_fields " .
		"from exports_partner " .
		"where id = " . $partner_id;
		$result = & $this->handle->query($query, __FILE__, __LINE__, false);
		if ($this->handle->numrows($result, __FILE__, __LINE__) == 1)
		{
			$record = & $this->handle->fetchAssoc($result);
			foreach($record as $name => $value) $this->$name = $value;
			
			$this->GenerateID();
			$this->partner_id = $partner_id;
			$this->id_parent = $parent_id;
			$this->products = array();
			
			$this->compulsory_fields = unserialize($this->compulsory_fields);
			$this->facultative_fields = unserialize($this->facultative_fields);
			$this->c_keys = array_keys($this->compulsory_fields);
			$this->f_keys = array_keys($this->facultative_fields);
			$this->tablelist = NULL;
			
			return true;
		}
		else
		{
			$this->lastErrorMessage = "Le partenaire spécifié pour l'export n'existe pas.";
			return false;
		}
	}
	
	function Load()
	{
		$this->exist = false;
		
		$query = "select id, name, partner_id, partner_name, products_filter, nb_pdt, create_time, timestamp, generate_time, compulsory_fields, facultative_fields, id_parent " .
		"from exports " .
		"where id = " . $this->id;
		
		$result = & $this->handle->query($query, __FILE__, __LINE__, false);
		if ($this->handle->numrows($result, __FILE__, __LINE__) > 0)
		{
			$record = & $this->handle->fetchAssoc($result);
			foreach($record as $name => $value) $this->$name = $value;
			if ($this->generate_time == 0)
			{
				$query = "select name as partner_name, products_filter, compulsory_fields, facultative_fields " .
				"from exports_partner " .
				"where id = " . $this->partner_id;
				$result = & $this->handle->query($query, __FILE__, __LINE__, false);
				
				if ($this->handle->numrows($result, __FILE__, __LINE__) == 1)
				{
					$record = & $this->handle->fetchAssoc($result);
					foreach($record as $name => $value) $this->$name = $value;
				}
				else $this->lastErrorMessage = "Le partenaire spécifié pour l'export n'existe pas.";
			}
			$this->compulsory_fields = unserialize($this->compulsory_fields);
			$this->facultative_fields = unserialize($this->facultative_fields);
			$this->c_keys = array_keys($this->compulsory_fields);
			$this->f_keys = array_keys($this->facultative_fields);
			$this->exist = true;
			$this->tablelist = NULL;
		}
		else $this->lastErrorMessage = "L'export n'existe pas dans la base de donnée.";
	}
	
	function Save()
	{
		$this->timestamp = time();
		if (!$this->exist)
		{
			$this->create_time = $this->timestamp;
			if (empty($this->id)) $this->generateID();
			$query = "insert into exports (";		$query2 = "values (";
			$query .= "id, ";						$query2 .= $this->id . ", ";
			$query .= "name, ";						$query2 .= "'" . $this->handle->escape($this->name) . "', ";
			$query .= "partner_id, ";				$query2 .= $this->partner_id . ", ";
			$query .= "partner_name, ";				$query2 .= "'" . $this->handle->escape($this->partner_name) . "', ";
			$query .= "products_filter, ";			$query2 .= $this->products_filter . ", ";
			$query .= "nb_pdt, ";					$query2 .= $this->nb_pdt . ", ";
			$query .= "create_time, ";				$query2 .= $this->create_time . ", ";
			$query .= "timestamp, ";				$query2 .= $this->timestamp . ", ";
			$query .= "generate_time, ";			$query2 .= $this->generate_time . ", ";
			$query .= "compulsory_fields, ";		$query2 .= "'" . $this->handle->escape(serialize($this->compulsory_fields)) . "', ";
			$query .= "facultative_fields, ";		$query2 .= "'" . $this->handle->escape(serialize($this->facultative_fields)) . "', ";
			$query .= "id_parent) ";				$query2 .= $this->id_parent . ")";
			$query .= $query2;
		}
		else
		{
			$query .= "update exports set " .
			"name = " .						"'" . $this->handle->escape($this->name) . "', " .
			"partner_id = " .				$this->partner_id . ", " .
			"partner_name = " .				"'" . $this->handle->escape($this->partner_name) . "', " .
			"products_filter = " .			$this->products_filter . ", " .
			"nb_pdt = " .					$this->nb_pdt . ", " .
			"create_time = " .				$this->create_time . ", " .
			"timestamp = " .				$this->timestamp . ", " .
			"generate_time = " .			$this->generate_time . ", " .
			"compulsory_fields = " .		"'" . $this->handle->escape(serialize($this->compulsory_fields)) . "', " .
			"facultative_fields = " .		"'" . $this->handle->escape(serialize($this->facultative_fields)) . "', " .
			"id_parent = " .				$this->id_parent . " " .
			"where id = " . $this->id;
		}
		
		if (!$this->handle->query($query, __FILE__, __LINE__, false))
		{
			$this->lastErrorMessage = "Erreur fatale SQL lors de l'ajout/la modification de l'export " . $this->id;
			return false;
		}
		
		$this->exist = true;
		
		return true;
	}
	
	function LoadProduct($id) { return $this->LoadProducts((int)$id); }
	function LoadAllProducts() { return $this->LoadProducts("all"); }
	function LoadArrayProducts($ids) { return $this->LoadProducts($ids); }
	
	function LoadProducts($ids)
	{
		if ($this->exist)
		{
			if (is_string($ids) && strtolower($ids) == "all")
			{
				$query = "select idTC, compulsory_fields, facultative_fields " .
				"from exports_products " .
				"where id_export = " . $this->id;
				
				$result = & $this->handle->query($query, __FILE__, __LINE__, false);
				if (($nb_pdt = $this->handle->numrows($result, __FILE__, __LINE__)) > 0)
				{
					while ($rec = & $this->handle->fetch($result))
						$this->products[$rec[0]] = array(unserialize($rec[1]), unserialize($rec[2]));
					$this->nb_pdt = $nb_pdt;
				}
				else
				{
					$this->lastErrorMessage = "Il n'existe aucun produit dans l'export n° " . $this->id;
					return false;
				}
			}
			elseif (is_array($ids) && !empty($ids))
			{
				$query = "select idTC, compulsory_fields, facultative_fields " .
				"from exports_products " .
				"where id_export = " . $this->id . " and idTC in (";
				$nb_ids = count($ids);
				$nb_ids_ok = 0;
				for ($i = 0; $i < $nb_ids; $i++)
				{
					if ((int)$ids[$i] > 0)
					{
						if ($nb_ids_ok > 0) $query .= ", ";
						$query .= $ids[$i];
						$nb_ids_ok++;
					}
				}
				$query .= ")";
				
				if ($nb_ids != $nb_ids_ok)
				{
					$this->lastErrorMessage = "Plusieurs identifiants de produits spécifiés ne sont pas valide.";
					if ($nb_ids_ok == 0)
						$this->lastErrorMessage .= " Aucun identifiant de produit à charger n'est valide.";
					
					return false;
				}
				
				$result = & $this->handle->query($query, __FILE__, __LINE__, false);
				if ($this->handle->numrows($result, __FILE__, __LINE__) > 0)
				{
					while ($rec = & $this->handle->fetch($result))
						$this->products[$rec[0]] = array(unserialize($rec[1]), unserialize($rec[2]));
				}
				else
				{
					$this->lastErrorMessage = "Aucun des produits spécifiés n'existe dans l'export n° " . $this->id;
					return false;
				}
			}
			elseif ((is_int($ids) || is_string($ids)) && (int)$ids > 0)
			{
				$query = "select idTC, compulsory_fields, facultative_fields " .
				"from exports_products " .
				"where id_export = " . $this->id . " and idTC = " . $ids;
				
				$result = & $this->handle->query($query, __FILE__, __LINE__, false);
				if ($this->handle->numrows($result, __FILE__, __LINE__) == 1)
				{
					$rec = & $this->handle->fetch($result);
					$this->products[$rec[0]] = array(unserialize($rec[1]), unserialize($rec[2]));
				}
				else
				{
					$this->lastErrorMessage = "Le produit ayant pour identifiant TC " . $id . " n'existe pas dans l'export n° " . $this->id;
					return false;
				}
			}
			else
			{
				$this->lastErrorMessage = "Le numéro identifiant du produit à charger n'est pas valide";
				return false;
			}
			
			// TODO corriger le bug qui fait que ca le fait pour tous les produits meme si on ne les a pas tous load en meme temps
			$ids = array_keys($this->products); $nb_ids = count($ids);
			$nb_ckeys = count($this->c_keys);
			$nb_fkeys = count($this->f_keys);
			for ($i = 0; $i < $nb_ids; $i++)
			{
				$pdt = array();
				for ($j = 0; $j < $nb_ckeys; $j++)
					$pdt[$this->c_keys[$j]] = $this->products[$ids[$i]][0][$j];
				
				for ($j = 0; $j < $nb_fkeys; $j++)
					$pdt[$this->f_keys[$j]] = $this->products[$ids[$i]][1][$j];
				
				$this->products[$ids[$i]] = $pdt;
			}
			return true;
		}
		else
		{
			$this->lastErrorMessage = "L'export n'a pas été chargé";
			return false;
		}
	}
	
	function SaveProducts()
	{
		if ($this->exist)
		{
			$listpdt = array();
			$result = & $this->handle->query("select idTC from exports_products where id_export = " . $this->id, __FILE__, __LINE__, false);
			while ($rec = & $this->handle->fetch($result)) $listpdt[$rec[0]] = true;
			$nb_pdt = count($listpdt);
			
			$ids = array_keys($this->products); $nb_ids = count($ids);
			for ($i = 0; $i < $nb_ids; $i++)
			{
				$c_fields = $f_fields = array();
				foreach ($this->c_keys as $ckey) $c_fields[] = $this->products[$ids[$i]][$ckey];
				foreach ($this->f_keys as $fkey) $f_fields[] = $this->products[$ids[$i]][$fkey];
				$c_fields = serialize($c_fields);
				$f_fields = serialize($f_fields);
				
				if (isset($listpdt[$ids[$i]]))
				{
					$query = "update exports_products set " .
					"compulsory_fields = " .					"'" . $this->handle->escape($c_fields) . "', " .
					"facultative_fields = " .					"'" . $this->handle->escape($f_fields) . "' " .
					"where idTC = " . $ids[$i] . " and id_export = " . $this->id;
				}
				else
				{
					$query = "insert into exports_products (";	$query2 = "values (";
					$query .= "idTC, ";							$query2 .= $ids[$i] . ", ";
					$query .= "id_export, ";					$query2 .= $this->id . ", ";
					$query .= "compulsory_fields, ";			$query2 .= "'" . $this->handle->escape($c_fields) . "', ";
					$query .= "facultative_fields) ";			$query2 .= "'" . $this->handle->escape($f_fields) . "')";
					$query .= $query2;
					$nb_pdt++;
				}
				
				if (!$this->handle->query($query, __FILE__, __LINE__, false))
				{
					$this->lastErrorMessage = "Erreur fatale SQL lors de la sauvegarde des produits de l'export " . $this->id;
					return false;
				}
			}
			
			$this->nb_pdt = $nb_pdt;
			return true;
		}
		else
		{
			$this->lastErrorMessage = "L'export n'a pas été chargé";
			return false;
		}
	}
	
	function AddProduct($idProduct, $idFamily)
	{
		// TODO Rajouter ici le filtrage des produits avec ou sans vente en ligne
		// TODO Rajouter les filtrage (num, notag, etc..)
		$result = & $this->handle->query("
			select
				p.id, p.idAdvertiser, p.idTC, pfr.name, pfr.fastdesc,
				pfr.ref_name, pfr.alias, pfr.keywords, pfr.descc, pfr.descd,
				pfr.delai_livraison as delivery_time, p.refSupplier, p.price, p.price2, p.unite,
				p.idTVA, p.ean
			from
				products p, products_fr pfr, products_families pf
			where
				p.id = pfr.id and
				p.id = " . $idProduct . " and
				p.id = pf.idProduct and
				pf.idFamily = " . $idFamily, __FILE__, __LINE__, false);
		
		if ($this->handle->numrows($result, __FILE__, __LINE__) != 1)
		{
			$this->lastErrorMessage = "Impossible de charger le produit " . $idProduct;
			return false;
		}
		$pdtInfos = & $this->handle->fetchAssoc($result);
		
		$result = & $this->handle->query("select a.id, a.nom1, a.prixPublic, a.delai_livraison, a.idTVA, a.parent " .
			"from advertisers a " .
			"where a.id = " . $pdtInfos['idAdvertiser'], __FILE__, __LINE__, false);
		
		if ($this->handle->numrows($result, __FILE__, __LINE__) != 1)
		{
			$this->lastErrorMessage = "Impossible de charger l'annonceur du produit " . $idProduct;
			return false;
		}
		$advInfos = & $this->handle->fetchAssoc($result);
		
		if ($advInfos['parent'] != __ID_TECHNI_CONTACT__ && !($this->products_filter & PRODUCT_FILTER_ADVERTISER_MASK))
		{
			$this->lastErrorMessage = "Les produits non en vente en ligne ne peuvent être rajoutés à cet export";
			return false;
		}
		
		if ($advInfos['parent'] == __ID_TECHNI_CONTACT__ && !($this->products_filter & PRODUCT_FILTER_SUPPLIER_MASK))
		{
			$this->lastErrorMessage = "Les produits avec vente en ligne activé ne peuvent pas être rajoutés à cet export";
			return false;
		}
		
		$refs = array();
		$result = & $this->handle->query("select content from references_cols where idProduct = " . $idProduct, __FILE__, __LINE__, false);
		list($ref_col_content) = $this->handle->fetch($result);
		if ($this->handle->numrows($result, __FILE__, __LINE__) == 1)
		{
			if ($advInfos['parent'] == __ID_TECHNI_CONTACT__)
			{
				$colstart = 3;
				$colend = -5;
			}
			else
			{
				$colstart = 2;
				$colend = -1;
			}
			
			$content_headers = unserialize($ref_col_content);
			$content_headers = array_slice($content_headers, $colstart, $colend);
			$content_len = count($content_headers);
			
			$ref_content = array();
			$result = & $this->handle->query("
        SELECT id as idTC, label, content, refSupplier, price, price2, unite, idTVA, classement
        FROM references_content
        WHERE idProduct = " . $idProduct . " AND vpc = 1 AND deleted = 0
        ORDER BY classement", __FILE__, __LINE__, false);
			
			while ($ref = & $this->handle->fetchAssoc($result))
			{
				$custom_content = array();
				$ref['content'] = unserialize($ref['content']);
				for ($i = 0; $i < $content_len; $i++)
					$custom_content[$content_headers[$i]] = $ref['content'][$i];
				$ref['content'] = $custom_content;
				$refs[] = & $ref;
			}
		}
		//print nl2br(print_r($refs, true));
		$fields_list = array_merge($this->compulsory_fields, $this->facultative_fields);
		
		if (empty($refs))
		{
			$pdt = array();
			
			foreach ($fields_list as $field_name => $set)
			{
				switch($set["source_type"])
				{
					case "UI" :
					case "US" :
						$pdt[$field_name] = $set["default"];
						break;
					case "UC" :
						switch($set["default"])
						{
							case "__PRODUCT_ID__" :				$pdt[$field_name] = $pdtInfos["id"]; break;
							case "__PRODUCT_IDTC__" :			$pdt[$field_name] = $pdtInfos["idTC"]; break;
							case "__PRODUCT_ADV_ID__" :			$pdt[$field_name] = $advInfos["id"]; break;
							case "__PRODUCT_ADV_NAME__" :		$pdt[$field_name] = $advInfos["nom1"]; break;
							case "__PRODUCT_REFSUPPLIER__" :	$pdt[$field_name] = $pdtInfos["refSupplier"]; break;
							case "__PRODUCT_PRICE_PS__" :
								if ($advInfos['parent'] == __ID_TECHNI_CONTACT__) $pdt[$field_name] = (int)$advInfos['prixPublic'] == 1 ? $ref["price"] : $ref["price2"];
								else $pdt[$field_name] = $ref["price"];
								break;
							case "__PRODUCT_PRICE_P__" :		$pdt[$field_name] = $pdtInfos["price"]; break;
							case "__PRODUCT_PRICE_S__" :		$pdt[$field_name] = $pdtInfos["price2"]; break;
							case "__PRODUCT_PRICE_TTC__" :		$pdt[$field_name] = ceil($pdtInfos["price"] * (100+$this->getTauxTVA($pdtInfos["idTVA"]))) / 100; break;
							case "__PRODUCT_UNIT__" :			$pdt[$field_name] = $pdtInfos["unite"]; break;
							case "__PRODUCT_TVA__" :			$pdt[$field_name] = $this->getTauxTVA($pdtInfos["idTVA"]); break;
							case "__PRODUCT_EAN__" :			$pdt[$field_name] = $pdtInfos["ean"]; break;
							case "__PRODUCT_NAME__" :			$pdt[$field_name] = $pdtInfos["name"]; break;
							case "__PRODUCT_FASTDESC__" :		$pdt[$field_name] = $pdtInfos["fastdesc"]; break;
							case "__PRODUCT_REF_NAME__" :		$pdt[$field_name] = $pdtInfos["ref_name"]; break;
							case "__PRODUCT_ALIAS__" :			$pdt[$field_name] = $pdtInfos["alias"]; break;
							case "__PRODUCT_KEYWORDS__" :		$pdt[$field_name] = $pdtInfos["keywords"]; break;
							case "__PRODUCT_DESCC__" :			$pdt[$field_name] = $pdtInfos["descc"]; break;
							case "__PRODUCT_DESCD__" :			$pdt[$field_name] = $pdtInfos["descd"]; break;
							case "__PRODUCT_DESCC_NO_TAG__" :			$pdt[$field_name] = preg_replace('/&euro;/i', '€', html_entity_decode(filter_var($pdtInfos["descc"], FILTER_SANITIZE_STRING), ENT_QUOTES)); break;
							case "__PRODUCT_DESCD_NO_TAG__" :			$pdt[$field_name] = preg_replace('/&euro;/i', '€', html_entity_decode(filter_var($pdtInfos["descd"], FILTER_SANITIZE_STRING), ENT_QUOTES)); break;
							case "__PRODUCT_DELIVERY_TIME__" :	$pdt[$field_name] = empty($pdtInfos["delai_livraison"]) ? $advInfos["delai_livraison"] : $pdtInfos["delai_livraison"]; break;
							case "__PRODUCT_REF_COUNT__" :		$pdt[$field_name] = 0; break;
							case "__PRODUCT_SHIP_FEE__" :
							case "__PRODUCT_SHIP_FEE_TTC__" :
								$fdpInfos = array();
								if ($result = & $this->handle->query("select config_name, config_value from config where config_name in ('fdp', 'fdp_franco', 'fdp_idTVA')", __FILE__, __LINE__, false) && $this->handle->numrows($result, __FILE__, __LINE__) == 3)
								{
									while ($record = & $this->handle->fetch($result)) $fdpInfos[$record[0]] = $record[1];
								}
								else
								{
									$fdpInfos["fdp"] = 20;
									$fdpInfos["fdp_franco"] = 300;
									$fdpInfos["fdp_idTVA"] = 1;
								}
								if ($pdtInfos["price"] > $fdpInfos["fdp_franco"]) $fdpInfos["fdp"] = 0;
								
								if ($set["default"] == "__PRODUCT_SHIP_FEE_TTC__")
									$pdt[$field_name] = ceil($fdpInfos["fdp"]*(100 + $this->getTauxTVA($fdpInfos["fdp_idTVA"])))/100;
								else
									$pdt[$field_name] = $fdpInfos["fdp"];
								
								break;
								
							case "__PRODUCT_URL__" :
								$pdt[$field_name] = URL . 'produits/' . $idFamily . '-' . $idProduct . '-' . $pdtInfos["ref_name"] . '.html';
								break;
								
							case "__PRODUCT_IMAGE_URL__" :
								$pdt[$field_name] = PRODUCTS_IMAGE_URL . $idProduct . "-card.jpg";
								break;
								
							case "__PRODUCT_MAIN_IMAGE_URL__" :
								$pdt[$field_name] = PRODUCTS_IMAGE_URL . $idProduct . "-zoom.jpg";
								break;
								
							case "__PRODUCT_FAMILY_TREE__" :
								if ($this->families === NULL) $this->InitFamiliesTree();
								
								$tree_deepness = 3;		// Number of family to take into account = 3 by default
								$tree_separator = ">";	// Default separator = '>'
								$filters = explode(" ", $set["filter_type"]);
								foreach($filters as $filter)
								{
									$filter_set = explode("=", $filter);
									switch($filter_set[0])
									{
										case "nb" : $tree_deepness = $filter_set[1]; break;
										case "sep" : $tree_separator = $filter_set[1]; break;
										default : break;
									}
								}
								
								$fam_tree = array($this->families[$idFamily]["name"]);	// Family Tree
								$nb_loop = 1;					// Number of loop fot the number of parent families to show
								$idFamTemp = $idFamily;			// Temp id for tree construction purpose
								while ($this->families[$idFamTemp]['idParent'] != 0 && $nb_loop < $tree_deepness)
								{
									$idFamTemp = $this->families[$idFamTemp]["idParent"];
									$fam_tree[] = $this->families[$idFamTemp]["name"];
								}
								$fam_tree = array_reverse($fam_tree);
								$pdt[$field_name] = implode($tree_separator, $fam_tree);
								
								break;
							default : break;
						}
					
					default : break;
				}
			}
			$this->products[$pdtInfos["idTC"]] = & $pdt;
		}
		else
		{
			foreach($refs as $numref => $ref)
			{
				$pdt = array();
				foreach ($fields_list as $field_name => $set)
				{
					switch($set["source_type"])
					{
						case "UI" :
						case "US" :
							$pdt[$field_name] = $set["default"];
							break;
						case "UC" :
							switch($set["default"])
							{
								case "__CURRENT_YEAR__" :			$pdt[$field_name] = date('Y'); break;
								
								case "__PRODUCT_ID__" :				$pdt[$field_name] = $pdtInfos["id"]; break;
								case "__PRODUCT_IDTC__" :			$pdt[$field_name] = $ref["idTC"]; break;
								case "__PRODUCT_ADV_ID__" :			$pdt[$field_name] = $advInfos["id"]; break;
								case "__PRODUCT_ADV_NAME__" :		$pdt[$field_name] = $advInfos["nom1"]; break;
								case "__PRODUCT_REFSUPPLIER__" :	$pdt[$field_name] = $ref["refSupplier"]; break;
								case "__PRODUCT_PRICE_PS__" :
									if ($advInfos['parent'] == __ID_TECHNI_CONTACT__) $pdt[$field_name] = (int)$advInfos['prixPublic'] == 1 ? $ref["price"] : $ref["price2"];
									else $pdt[$field_name] = $ref["price"];
									break;
								case "__PRODUCT_PRICE_P__" :		$pdt[$field_name] = $ref["price"]; break;
								case "__PRODUCT_PRICE_S__" :		$pdt[$field_name] = $ref["price2"]; break;
								case "__PRODUCT_PRICE_TTC__" :		$pdt[$field_name] = ceil($ref["price"] * (100+$this->getTauxTVA($ref["idTVA"]))) / 100; break;
								case "__PRODUCT_UNIT__" :			$pdt[$field_name] = $ref["unite"]; break;
								case "__PRODUCT_LABEL__" :			$pdt[$field_name] = $ref["label"]; break;
								case "__PRODUCT_TVA__" :			$pdt[$field_name] = $this->getTauxTVA($ref["idTVA"]); break;
								case "__PRODUCT_EAN__" :			$pdt[$field_name] = $pdtInfos["ean"]; break;
								case "__PRODUCT_NAME__" :			$pdt[$field_name] = $pdtInfos["name"]; break;
								case "__PRODUCT_FASTDESC__" :		$pdt[$field_name] = $pdtInfos["fastdesc"]; break;
								case "__PRODUCT_REF_NAME__" :		$pdt[$field_name] = $pdtInfos["ref_name"]; break;
								case "__PRODUCT_ALIAS__" :			$pdt[$field_name] = $pdtInfos["alias"]; break;
								case "__PRODUCT_KEYWORDS__" :		$pdt[$field_name] = $pdtInfos["keywords"]; break;
								case "__PRODUCT_DESCC__" :			$pdt[$field_name] = $pdtInfos["descc"]; break;
								case "__PRODUCT_DESCD__" :			$pdt[$field_name] = $pdtInfos["descd"]; break;
								case "__PRODUCT_DESCC_NO_TAG__" :			$pdt[$field_name] = preg_replace('/&euro;/i', '€', html_entity_decode(filter_var($pdtInfos["descc"], FILTER_SANITIZE_STRING), ENT_QUOTES)); break;
								case "__PRODUCT_DESCD_NO_TAG__" :			$pdt[$field_name] = preg_replace('/&euro;/i', '€', html_entity_decode(filter_var($pdtInfos["descd"], FILTER_SANITIZE_STRING), ENT_QUOTES)); break;
								case "__PRODUCT_DELIVERY_TIME__" :	$pdt[$field_name] = empty($pdtInfos["delai_livraison"]) ? $advInfos["delai_livraison"] : $pdtInfos["delai_livraison"]; break;
								case "__PRODUCT_CUSTOM_COLS__" :	$pdt[$field_name] = $ref['content']; break;
								case "__PRODUCT_REF_COUNT__" :		$pdt[$field_name] = count($refs); break;
								case "__PRODUCT_REF_ORDER__" :		$pdt[$field_name] = $numref + 1; break;
								case "__PRODUCT_SHIP_FEE__" :
								case "__PRODUCT_SHIP_FEE_TTC__" :
									$fdpInfos = array();
									if ($result = & $this->handle->query("select config_name, config_value from config where config_name in ('fdp', 'fdp_franco', 'fdp_idTVA')", __FILE__, __LINE__, false) && $this->handle->numrows($result, __FILE__, __LINE__) == 3)
									{
										while ($record = & $this->handle->fetch($result)) $fdpInfos[$record[0]] = $record[1];
									}
									else
									{
										$fdpInfos["fdp"] = 20;
										$fdpInfos["fdp_franco"] = 300;
										$fdpInfos["fdp_idTVA"] = 1;
									}
									if ($ref["price"] > $fdpInfos["fdp_franco"]) $fdpInfos["fdp"] = 0;
									
									if ($set["default"] == "__PRODUCT_SHIP_FEE_TTC__")
										$pdt[$field_name] = ceil($fdpInfos["fdp"]*(100 + $this->getTauxTVA($fdpInfos["fdp_idTVA"])))/100;
									else
										$pdt[$field_name] = $fdpInfos["fdp"];
									
									break;
									
								case "__PRODUCT_URL__" :
									$pdt[$field_name] = URL . 'produits/' . $idFamily . '-' . $idProduct . '-' . $pdtInfos["ref_name"] . '.html';
									break;
									
								case "__PRODUCT_IMAGE_URL__" :
									$pdt[$field_name] = PRODUCTS_IMAGE_URL . $idProduct . "-card.jpg";
									break;
									
								case "__PRODUCT_MAIN_IMAGE_URL__" :
									$pdt[$field_name] = PRODUCTS_IMAGE_URL . $idProduct . "-zoom.jpg";
									break;
									
								case "__PRODUCT_FAMILY_TREE__" :
									if ($this->families === NULL) $this->InitFamiliesTree();
									
									$tree_deepness = 3;		// Number of family to take into account = 3 by default
									$tree_separator = ">";	// Default separator = '>'
									$filters = explode(" ", $set["filter_type"]);
									foreach($filters as $filter)
									{
										$filter_set = explode("=", $filter);
										switch($filter_set[0])
										{
											case "nb" : $tree_deepness = $filter_set[1]; break;
											case "sep" : $tree_separator = $filter_set[1]; break;
											default : break;
										}
									}
									
									$fam_tree = array($this->families[$idFamily]["name"]);	// Family Tree
									$nb_loop = 1;					// Number of loop fot the number of parent families to show
									$idFamTemp = $idFamily;			// Temp id for tree construction purpose
									while ($this->families[$idFamTemp]['idParent'] != 0 && $nb_loop < $tree_deepness)
									{
										$idFamTemp = $this->families[$idFamTemp]["idParent"];
										$fam_tree[] = $this->families[$idFamTemp]["name"];
									}
									$fam_tree = array_reverse($fam_tree);
									$pdt[$field_name] = implode($tree_separator, $fam_tree);
									
									break;
								default : break;
							}
						
						default : break;
					}
				}
				$this->products[$ref["idTC"]] = $pdt;
				
				// We stop here if we only have to get the 1st reference
				if ($this->products_filter & PRODUCT_FILTER_FIRSTREF_MASK) break;
			}
		}
		
		return true;
	}
	
	function DelProduct($idTC)
	{
		$query = "delete from exports_products where id_export = " . $this->id . " and idTC = " . $idTC;
		if ($result = & $this->handle->query($query, __FILE__, __LINE__, false))
		{
			if (isset($this->products[$idTC])) unset($this->products[$idTC]);
			return true;
		}
		else return false;
	}
	
	function InitFamiliesTree()
	{
		$this->families = array();
		$this->families[0]['name'] = '';
		$this->families[0]['ref_name'] = '';
		$this->families[0]['idParent'] = 0;
		
		$result = & $this->handle->query("select f.id, fr.name, fr.ref_name, f.idParent from families f, families_fr fr where f.id = fr.id", __FILE__, __LINE__);
		while ($family = & $this->handle->fetchAssoc($result))
		{
			$this->families[$family['id']]['name'] = $family['name'];
			$this->families[$family['id']]['ref_name'] = $family['ref_name'];
			$this->families[$family['id']]['idParent'] = $family['idParent'];
			if (!isset($families[$family['idParent']]['nbchildren']))
				$this->families[$family['idParent']]['nbchildren'] = 1;
			else
				$this->families[$family['idParent']]['nbchildren']++;
			$this->families[$family['idParent']]['children'][$this->families[$family['idParent']]['nbchildren']-1] = $family['id'];
		}
	}
	
	function getTauxTVA($idTVA)
	{
	    if ($this->tauxTVA === NULL)
		{
			$this->tauxTVA = array();
		    $result = & $this->handle->query("select id, taux from tva", __FILE__, __LINE__ );
			while($record = & $this->handle->fetch($result))
				$this->tauxTVA[$record[0]] = $record[1];
		}
		
		return $this->tauxTVA[$idTVA];
	}
	
	function UpdateStatus()
	{
		
		$result = $this->handle->query("select count(id) from imports_products where id_import = " . $this->id . " and status = " . __IP_NOT_VALID__, __file__, __line__, false);
		list($this->nbp_notvalid) = $this->handle->fetch($result);
		
		$result = $this->handle->query("select count(id) from imports_products where id_import = " . $this->id . " and status = " . __IP_VALID__, __file__, __line__, false);
		list($this->nbp_valid) = $this->handle->fetch($result);
		
		$result = $this->handle->query("select count(id) from imports_products where id_import = " . $this->id . " and status = " . __IP_FINALIZED__, __file__, __line__, false);
		list($this->nbp_final) = $this->handle->fetch($result);
		
		if ($this->nbp_notvalid > 0)
		{
			if ($this->nbp_valid > 0)
			{
				if ($this->nbp_final > 0) $this->status = __I_NVF__;
				else $this->status = __I_NV__;
			}
			else
			{
				if ($this->nbp_final > 0) $this->status = __I_NF__;
				else $this->status = __I_N__;
			}
		}
		else
		{
			if ($this->nbp_valid > 0)
			{
				if ($this->nbp_final > 0) $this->status = __I_VF__;
				else $this->status = __I_V__;
			}
			else
			{
				if ($this->nbp_final > 0) $this->status = __I_F__;
				else $this->status = __I_0__;
			}
		}
	}
	
}

?>
