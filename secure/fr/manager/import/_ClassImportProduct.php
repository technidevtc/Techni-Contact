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

require_once(ADMIN . 'generator.php');
require_once(ADMIN . 'logo.php');

function DeleteProduct(& $handle, $id)
{
	if (!$handle->query("delete from imports_products where id = " . $id, __FILE__, __LINE__, false)) return false;
	if (!$handle->query("delete from imports_references where import_product_id = " . $id, __FILE__, __LINE__, false)) return false;
	return true;
}

class ImportProduct
{
	/* Connection Handle */
	var $handle = NULL;
	
	//var $parent_import_o = NULL;
	
	/* Product's fields */
	var $id = 0;
	var $id_import = 0;
	var $id_final = 0;
	//var $update = 0;
	var $online_sell = 0;
	var $name = "";
	var $ref_name = "";
	var $family_name = "";
	var $fastdesc = "";
	var $descc = "";
	var $descd = "";
	var $delivery_time = "";
	var $url_image = "";
	var $url_docs = "a:0:{}";
	var $price = "";
	var $alias = "";
	var $keywords = "";
	var $ref_count = 0;
	var $mixed_data_entitle = "a:0:{}";
	var $status = 0;
	
	var $references = array();
	
	/* TVA Table */
	var $tauxTVA = NULL;
	var $VATids = NULL;
	
	var $exist = false;
	var $lastErrorMessage = "";
	
	//var $productFields = array("id", "id_import", "id_final", "family_name", "family_ref_name", "name", "ref_name", "fastdesc", "descc", "descd", "delivery_time", "url_image", "ref_count", "mixed_data_entitle", "status");
	//var $referenceFields = array("id", "ref_supplier", "label", "mixed_data", "price", "price_deleted", "price2", "marge", "unit", "VAT", "order");
	
	/* Constructor */
	function ImportProduct(& $handle, $id = NULL)
	{
		$this->handle = & $handle;
		if ($id != NULL)
		{
			$this->id = $id;
			$this->Load();
		}
	}
/*
	function SetParentImport(& $parent_import_o)
	{
		$this->parent_import_o = $parent_import_o;
		$this->id_import = $parent_import_o->id;
	}
*/
	function GenerateProductID()
	{
		do
		{
			$id = mt_rand(1, 999999999);
			$result = & $this->handle->query("select id from imports_products where id = " . $id, __FILE__, __LINE__);
		}
		while ($this->handle->numrows($result, __FILE__, __LINE__) >= 1);
		
		$this->id = $id;
	}
	
	function GenerateReferenceID()
	{
		do
		{
			$id = mt_rand(1, 999999999);
			$result = & $this->handle->query("select id from imports_references where id = " . $id, __FILE__, __LINE__);
		}
		while ($this->handle->numrows($result, __FILE__, __LINE__) >= 1);
		
		return $id;
	}
	
	/*
	function Create($generate_id = true)
	{
		if ($generate_id) $this->generateID();
		
		$this->coord = array('idClient' => '', 'totalHT' => 0, 'totalTTC' => 0, 'titre' => '', 'nom' => '', 'prenom' => '', 'societe' => '', 'adresse' => '', 'complement' => '',
		'ville' => '', 'cp' => '', 'pays' => '', 'titre_l' => '', 'nom_l' => '', 'prenom_l' => '', 'societe_l' => '', 'adresse_l' => '', 'complement_l' => '', 'ville_l' => '',
		'cp_l' => '', 'pays_l' => '', 'coord_livraison' => 0);
		
		$this->produits = array();
		$this->s_status = array();
		
		$this->status = 1;
	}
	*/
	function Load()
	{
		$this->exist = false;
		
		$query = "select id, id_import, id_final, online_sell, name, ref_name, family_name, fastdesc, descc, descd, delivery_time, url_image, url_docs, price, alias, keywords, ref_count, mixed_data_entitle, status " .
		"from imports_products " .
		"where id = " . $this->id;
		
		$result = & $this->handle->query($query, __FILE__, __LINE__, false);
		if ($this->handle->numrows($result, __FILE__, __LINE__) > 0)
		{
			$record = & $this->handle->fetchAssoc($result);
			
			foreach($record as $name => $value) $this->$name = $value;
			if (empty($this->mixed_data_entitle)) $this->mixed_data_entitle = serialize(array());
			if (empty($this->url_docs)) $this->url_docs = serialize(array());
			
			$query = "select id, id_final, ref_supplier, label, mixed_data, unit, VAT, price2, marge, price, price_deleted, `order`" .
			" from imports_references" .
			" where import_product_id = " . $this->id .
			" order by `order`";
			
			$result = & $this->handle->query($query, __FILE__, __LINE__, false);
			$this->ref_count = $this->handle->numrows($result, __FILE__, __LINE__);
			while ($rec = & $this->handle->fetchAssoc($result))
			{
				if (empty($rec['mixed_data'])) $rec['mixed_data'] = serialize(array());
				$this->references[] = & $rec;
			}
			
			if ($this->online_sell && empty($this->references)) $this->lastErrorMessage = "Le produit " . $this->id . " ne possède aucune référence.";
			$this->exist = true;
		}
		else $this->lastErrorMessage = "Le produit " . $this->id . " n'existe pas dans la base de donnée.";
	}
	
	function Save()
	{
		if (!$this->exist)
		{
			if ($this->id == 0) $this->GenerateProductID();
			$query = "insert into imports_products ("; $query2 = "values (";
			$query .= "id, ";						$query2 .= $this->id . ", ";
			$query .= "id_import, ";				$query2 .= $this->id_import . ", ";
			$query .= "id_final, ";					$query2 .= $this->id_final . ", ";
			//$query .= "update, ";					$query2 .= $this->update . ", ";
			$query .= "online_sell, ";				$query2 .= $this->online_sell . ", ";
			$query .= "name, ";						$query2 .= "'" . $this->handle->escape($this->name) . "', ";
			$query .= "ref_name, ";					$query2 .= "'" . $this->handle->escape($this->ref_name) . "', ";
			$query .= "family_name, ";				$query2 .= "'" . $this->handle->escape($this->family_name) . "', ";
			$query .= "fastdesc, ";					$query2 .= "'" . $this->handle->escape($this->fastdesc) . "', ";
			$query .= "descc, ";					$query2 .= "'" . $this->handle->escape($this->descc) . "', ";
			$query .= "descd, ";					$query2 .= "'" . $this->handle->escape($this->descd) . "', ";
			$query .= "delivery_time, ";			$query2 .= "'" . $this->handle->escape($this->delivery_time) . "', ";
			$query .= "url_image, ";				$query2 .= "'" . $this->handle->escape($this->url_image) . "', ";
			$query .= "url_docs, ";					$query2 .= "'" . $this->handle->escape($this->url_docs) . "', ";
			$query .= "price, ";					$query2 .= "'" . ($this->ref_count > 0 ? "ref" : $this->handle->escape($this->price)) . "', ";
			$query .= "alias, ";					$query2 .= "'" . $this->handle->escape($this->alias) . "', ";
			$query .= "keywords, ";					$query2 .= "'" . $this->handle->escape($this->keywords) . "', ";
			$query .= "ref_count, ";				$query2 .= $this->ref_count . ", ";
			$query .= "mixed_data_entitle, ";		$query2 .= "'" . $this->handle->escape($this->mixed_data_entitle) . "', ";
			$query .= "status) ";					$query2 .= $this->status . ")";
			$query .= $query2;
		}
		else
		{
			$query .= "update imports_products set " .
			"id_import = " .				$this->id_import . ", " .
			"id_final = " .					$this->id_final . ", " .
			//"update = " .					$this->update . ", " .
			"online_sell = " .				$this->online_sell . ", " .
			"name = " .						"'" . $this->handle->escape($this->name) . "', " .
			"ref_name = " .					"'" . $this->handle->escape($this->ref_name) . "', " .
			"family_name = " .				"'" . $this->handle->escape($this->family_name) . "', " .
			"fastdesc = " .					"'" . $this->handle->escape($this->fastdesc) . "', " .
			"descc = " .					"'" . $this->handle->escape($this->descc) . "', " .
			"descd = " .					"'" . $this->handle->escape($this->descd) . "', " .
			"delivery_time = " .			"'" . $this->handle->escape($this->delivery_time) . "', " .
			"url_image = " .				"'" . $this->handle->escape($this->url_image) . "', " .
			"url_docs = " .					"'" . $this->handle->escape($this->url_docs) . "', " .
			"price = " .					"'" . ($this->ref_count > 0 ? "ref" : $this->handle->escape($this->price)) . "', " .
			"alias = " .					"'" . $this->handle->escape($this->alias) . "', " .
			"keywords = " .					"'" . $this->handle->escape($this->keywords) . "', " .
			"ref_count = " .				$this->ref_count . ", " .
			"mixed_data_entitle = " .		"'" . $this->handle->escape($this->mixed_data_entitle) . "', " .
			"status = " .					$this->status . " " .
			"where id = " . $this->id;
			
			if (!$this->handle->query("delete from imports_references where import_product_id = " . $this->id, __FILE__, __LINE__, false))
			{
				$this->lastErrorMessage = "Erreur fatale SQL lors de la suppression des références du produit " . $this->id;
				return false;
			}
		}
		//print $query;
		
		if (!$this->handle->query($query, __FILE__, __LINE__, false))
		{
			$this->lastErrorMessage = "Erreur fatale SQL lors de l'ajout/la modification du produit " . $this->id;
			return false;
		}
		
		for($i = 0; $i < $this->ref_count; $i++)
		{
			$ref = & $this->references[$i];
			
			if (empty($ref['id'])) $ref['id'] = $this->GenerateReferenceID();
			$ref['id_final'] = (int)$ref['id_final'];
			
			$query = "insert into imports_references ("; $query2 = "values (";
			$query .= "id, ";						$query2 .= $ref['id'] . ", ";
			$query .= "import_product_id, "	;		$query2 .= $this->id . ", ";
			$query .= "id_final, ";					$query2 .= $ref['id_final'] . ", ";
			$query .= "ref_supplier, ";				$query2 .= "'" . $this->handle->escape($ref['ref_supplier']) . "', ";
			$query .= "label, ";					$query2 .= "'" . $this->handle->escape($ref['label']) . "', ";
			$query .= "mixed_data, ";				$query2 .= "'" . $this->handle->escape($ref['mixed_data']) . "', ";
			$query .= "unit, ";						$query2 .= "'" . $ref['unit'] . "', ";
			$query .= "VAT, ";						$query2 .= "'" . $ref['VAT'] . "', ";
			$query .= "price2, ";					$query2 .= "'" . $ref['price2'] . "', ";
			$query .= "marge, ";					$query2 .= "'" . $ref['marge'] . "', ";
			$query .= "price, ";					$query2 .= "'" . $ref['price'] . "', ";
			$query .= "price_deleted, ";			$query2 .= "'" . $ref['price_deleted'] . "', ";
			$query .= "`order`) ";					$query2 .= "'" . $ref['order'] . "')";
			$query .= $query2;
			
			//print $query;
			
			if (!$this->handle->query($query, __FILE__, __LINE__, false))
			{
				$this->lastErrorMessage = "Erreur fatale SQL lors de l'ajout de la référence " . $ref['id'] . " du produit " . $this->id . ".";
				return false;
			}
		}
		
		//if ($this->parent_import_o) $this->parent_import_o->UpdateStatus();
		
		$this->exist = true;
		
		return true;
	}
	
	function UpdateStatus()
	{
		if ($this->status >= __IP_FINALIZED__) return true;
		
		// Test the validity of product's fields
		if ($this->ref_name == "" ||
			$this->fastdesc == "" ||
			$this->descc == "")
		{
			$this->status = ($this->id_final != 0) ? __IP_NOT_VALID_UPDATE__ : __IP_NOT_VALID__;
			$this->lastErrorMessage = "Un ou plusieurs champs obligatoires du produit " . $this->id . " n'ont pas été renseignés.";
			return false;
		}
		else
		{
			// Test the validity of each reference's fields
			//print_r($this->references);
			if ($this->online_sell)
			{
				if (empty($this->delivery_time))
				{
					$this->status = ($this->id_final != 0) ? __IP_NOT_VALID_UPDATE__ : __IP_NOT_VALID__;
					$this->lastErrorMessage = "Un ou plusieurs champs obligatoires du produit " . $this->id . " n'ont pas été renseignés.";
					return false;
				}
				
				if (empty($this->ref_count))
				{
					$this->status = ($this->id_final != 0) ? __IP_NOT_VALID_UPDATE__ : __IP_NOT_VALID__;
					$this->lastErrorMessage = "Le produit " . $this->id . " a sa vente en ligne activée, mais ne possède aucune référence.";
					return false;
				}
				else
				{
					foreach($this->references as $ref)
					{
						if (empty($ref['ref_supplier']) ||
							empty($ref['label']) ||
							empty($ref['price2']) ||
							empty($ref['price']) ||
							empty($ref['unit']) ||
							empty($ref['VAT']) ||
							empty($ref['order']))
						{
							$this->status = ($this->id_final != 0) ? __IP_NOT_VALID_UPDATE__ : __IP_NOT_VALID__;
							$this->lastErrorMessage = "Un ou plusieurs champs obligatoires d'une ou plusieurs références du produit " . $this->id . " n'ont pas été renseignés.";
							return false;
						}
					}
				}
			}
			else
			{
				if (empty($this->ref_count))
				{
					if (empty($this->price))
					{
						$this->status = ($this->id_final != 0) ? __IP_NOT_VALID_UPDATE__ : __IP_NOT_VALID__;
						$this->lastErrorMessage = "Le produit " . $this->id . " n'a aucune indication de prix/disponibilité.";
						return false;
					}
				}
				else
				{
					foreach($this->references as $ref)
					{
						if (empty($ref['label']) ||
							empty($ref['price']) ||
							empty($ref['order']))
						{
							$this->status = ($this->id_final != 0) ? __IP_NOT_VALID_UPDATE__ : __IP_NOT_VALID__;
							$this->lastErrorMessage = "Un ou plusieurs champs obligatoires d'une ou plusieurs références du produit " . $this->id . " n'ont pas été renseignés.";
							return false;
						}
					}
				}
			}
			
			// Test if the family name does exist, or if a complete tree is given and legit
			if (!empty($this->family_name))
			{
				$famtree = explode("->", $this->family_name);
				$lentree = count($famtree);
				if ($lentree == 1)
				{
					$fam_ref_name = Utils::toDashAz09($this->family_name);
					$result = & $this->handle->query("select fam.id, fam.idParent from families fam, families_fr fam_fr where fam_fr.ref_name = '" . $this->handle->escape($fam_ref_name) . "' and fam.id = fam_fr.id", __FILE__, __LINE__, false);
					if ($this->handle->numrows($result, __FILE__, __LINE__) == 0) // Family doesn't exist
					{
						$this->status = ($this->id_final != 0) ? __IP_NOT_VALID_UPDATE__ : __IP_NOT_VALID__;
						$this->lastErrorMessage = "La famille du produit " . $this->id . " n'existe pas dans la base de donnée, veuillez la créer, en sélectionner une autre, ou spécifier un arbre complet pour création automatique";
						return false;
					}
					else // Family exist
					{
						list($famID, $famParentID) = $this->handle->fetch($result);
						if ($famParentID == 0) // Family is not a level 3 one, but a level 1
						{
							$this->status = ($this->id_final != 0) ? __IP_NOT_VALID_UPDATE__ : __IP_NOT_VALID__;
							$this->lastErrorMessage = "La famille du produit " . $this->id . " n'est pas une famille de niveau 3, mais de niveau 1, veuillez en sélectionner une autre, ou spécifier un arbre complet pour création automatique";
							return false;
						}
						else
						{
							$result = & $this->handle->query("select idParent from families where id = " . $famParentID, __FILE__, __LINE__, false);
							list($famParentID) = $this->handle->fetch($result);
							if ($famParentID == 0) // Family is not a level 3 one, but a level 2
							{
								$this->status = ($this->id_final != 0) ? __IP_NOT_VALID_UPDATE__ : __IP_NOT_VALID__;
								$this->lastErrorMessage = "La famille du produit " . $this->id . " n'est pas une famille de niveau 3, mais de niveau 2, veuillez en sélectionner une autre, ou spécifier un arbre complet pour création automatique";
								return false;
							}
							else
							{
								$result = & $this->handle->query("select idParent from families where id = " . $famParentID, __FILE__, __LINE__, false);
								list($famParentID) = $this->handle->fetch($result);
								if ($famParentID != 0) // Family is not a level 3 one, but a level ? -> family is bugged
								{
									$this->status = ($this->id_final != 0) ? __IP_NOT_VALID_UPDATE__ : __IP_NOT_VALID__;
									$this->lastErrorMessage = "La famille du produit " . $this->id . " n'est pas une famille de niveau 3, mais de niveau inconnu, veuillez en sélectionner une autre ou spécifier un arbre complet pour création automatique et corriger le problème via le module des familles";
									return false;
								}
							}
						}
					}
				}
				elseif ($lentree == 3)
				{
					$fam_ref_name1 = Utils::toDashAz09($famtree[0]);
					$result = & $this->handle->query("select fam.id from families fam, families_fr fam_fr where fam_fr.ref_name = '" . $this->handle->escape($fam_ref_name1) . "' and fam.idParent = 0 and fam.id = fam_fr.id", __FILE__, __LINE__, false);
					if ($this->handle->numrows($result, __FILE__, __LINE__) == 0)
					{
						$this->status = ($this->id_final != 0) ? __IP_NOT_VALID_UPDATE__ : __IP_NOT_VALID__;
						$this->lastErrorMessage = "La famille de niveau 1 du produit " . $this->id . " n'existe pas, la création automatique sera impossible";
						return false;
					}
					else // Level 1 family exists
					{
						list($fam1ID) = $this->handle->fetch($result);
						$fam_ref_name2 = Utils::toDashAz09($famtree[1]);
						$fam_ref_name3 = Utils::toDashAz09($famtree[2]);
						$result = & $this->handle->query("select fam.id, fam.idParent from families fam, families_fr fam_fr where fam_fr.ref_name = '" . $this->handle->escape($fam_ref_name2) . "' and fam.id = fam_fr.id", __FILE__, __LINE__, false);
						if ($this->handle->numrows($result, __FILE__, __LINE__) == 1) // Level 2 family exists
						{
							list($fam2ID, $fam2ParentID) = $this->handle->fetch($result);
							if ($fam2ParentID != $fam1ID) // It doesn't have the right parent
							{
								$this->status = ($this->id_final != 0) ? __IP_NOT_VALID_UPDATE__ : __IP_NOT_VALID__;
								$this->lastErrorMessage = "Le nom de la famille de niveau 2 du produit " . $this->id . " existe déjà (enfant d'une autre famille ou est une famille de niveau 1) : la création automatique sera impossible";
								return false;
							}
							
							$result = & $this->handle->query("select fam.id, fam.idParent from families fam, families_fr fam_fr where fam_fr.ref_name = '" . $this->handle->escape($fam_ref_name3) . "' and fam.id = fam_fr.id", __FILE__, __LINE__, false);
							if ($this->handle->numrows($result, __FILE__, __LINE__) == 1) // Level 3 family exists
							{
								list($fam3ID, $fam3ParentID) = $this->handle->fetch($result);
								if ($fam3ParentID != $fam2ID)
								{
									$this->status = ($this->id_final != 0) ? __IP_NOT_VALID_UPDATE__ : __IP_NOT_VALID__;
									$this->lastErrorMessage = "Le nom de la famille de niveau 3 du produit " . $this->id . " existe déjà (enfant d'une autre famille ou est une famille de niveau 1) : la création automatique sera impossible";
									return false;
								}
							}
						}
						else // Level 2 family doesn't exists
						{
							$result = & $this->handle->query("select fam.id, fam.idParent from families fam, families_fr fam_fr where fam_fr.ref_name = '" . $this->handle->escape($fam_ref_name3) . "' and fam.id = fam_fr.id", __FILE__, __LINE__, false);
							if ($this->handle->numrows($result, __FILE__, __LINE__) == 1) // Level 3 family exists
							{
								$this->status = ($this->id_final != 0) ? __IP_NOT_VALID_UPDATE__ : __IP_NOT_VALID__;
								$this->lastErrorMessage = "Le nom de la famille de niveau 3 du produit " . $this->id . " existe déjà (enfant d'une autre famille ou est une famille de niveau 1) : la création automatique sera impossible";
								return false;
							}
						}
					}
				}
				elseif ($lentree == 2 || $lentree > 3)
				{
					$this->status = ($this->id_final != 0) ? __IP_NOT_VALID_UPDATE__ : __IP_NOT_VALID__;
					$this->lastErrorMessage = "L'arbre de famille du produit " . $this->id . " n'est pas valide.";
					return false;
				}
			}
			else
			{
				$this->status = ($this->id_final != 0) ? __IP_NOT_VALID_UPDATE__ : __IP_NOT_VALID__;
				$this->lastErrorMessage = "Aucune famille n'a été choisie ou définie pour le produit " . $this->id . ".";
				return false;
			}
			
			$this->status = ($this->id_final != 0) ? __IP_VALID_UPDATE__ : __IP_VALID__;
			return true;
		}
	}
	
	function finalize()
	{
		if ($this->status >= __IP_VALID__ && $this->status < __IP_FINALIZED__)
		{
			if ($this->id_final != 0)
			{
				$result = & $this->handle->query("select id from products where id = " . $this->id_final, __FILE__, __LINE__);
				if ($this->handle->numrows($result) == 0) return $this->finalize_insert();
				else return $this->finalize_update();
			}
			else return $this->finalize_insert();
		}
		else $this->lastErrorMessage = "Impossible de finaliser l'import du produit " . $this->id . " car il n'est pas valide";
		return false;
	}
	
	function finalize_insert()
	{
		$this->id_final = generateID(1, 16777215, 'id', 'products', $this->handle);
		$idTC = generateIDTC($this->handle);
		
		$result = & $this->handle->query("select idAdvertiser from imports where id = " . $this->id_import, __FILE__, __LINE__);
		list($idAdvertiser) = $this->handle->fetch($result);
		
		$timestamp = time();
		
		$queryA = "insert into products (";		$query2 = "values (";
		$queryA .= "id, ";						$query2 .= $this->id_final . ", ";
		$queryA .= "idAdvertiser, ";			$query2 .= $idAdvertiser . ", ";
		$queryA .= "idTC, ";					$query2 .= $idTC . ", ";
		$queryA .= "timestamp, ";				$query2 .= $timestamp . ", ";
		$queryA .= "price) ";					$query2 .= "'" . $this->handle->escape($this->price) . "')";
		$queryA .= $query2;
		
		$queryB = "insert into products_fr (";	$query2 = "values (";
		$queryB .= "id, ";						$query2 .= $this->id_final . ", ";
		$queryB .= "idAdvertiser, ";			$query2 .= $idAdvertiser . ", ";
		$queryB .= "name, ";					$query2 .= "'" . $this->handle->escape($this->name) . "', ";
		$queryB .= "fastdesc, ";				$query2 .= "'" . $this->handle->escape($this->fastdesc) . "', ";
		$queryB .= "ref_name, ";				$query2 .= "'" . $this->handle->escape($this->ref_name) . "', ";
		$queryB .= "alias, ";					$query2 .= "'" . $this->handle->escape($this->alias) . "', ";
		$queryB .= "keywords, ";				$query2 .= "'" . $this->handle->escape($this->keywords) . "', ";
		$queryB .= "descc, ";					$query2 .= "'" . $this->handle->escape($this->descc) . "', ";
		$queryB .= "descd, ";					$query2 .= "'" . $this->handle->escape($this->descd) . "', ";
		$queryB .= "delai_livraison) ";			$query2 .= "'" . $this->handle->escape($this->delivery_time) . "')";
		$queryB .= $query2;
		
		$famtree = explode("->", $this->family_name);
		$lentree = count($famtree);
		if ($lentree == 1)
		{
			$fam_ref_name = Utils::toDashAz09($this->family_name);
			$result = & $this->handle->query("select id from families_fr where ref_name = '" . $this->handle->escape($fam_ref_name) . "'", __FILE__, __LINE__, false);
			list($fam3ID) = $this->handle->fetch($result);
		}
		elseif ($lentree == 3)
		{
			$fam_ref_name = Utils::toDashAz09($famtree[0]);
			$result = & $this->handle->query("select id from families_fr where ref_name = '" . $this->handle->escape($fam_ref_name) . "'", __FILE__, __LINE__, false);
			list($fam1ID) = $this->handle->fetch($result);
			
			$fam_ref_name = Utils::toDashAz09($famtree[1]);
			$result = & $this->handle->query("select id from families_fr where ref_name = '" . $this->handle->escape($fam_ref_name) . "'", __FILE__, __LINE__, false);
			if ($this->handle->numrows($result, __FILE__, __LINE__) == 0)
			{
				$fam2ID = generateID(12, 9999, 'id', 'families', $this->handle);
				$queryF2A = "insert into families (id, idParent) values(" . $fam2ID . ", '" . $fam1ID . "')";
				$queryF2B = "insert into families_fr (id, name, ref_name) values(" . $fam2ID . ", '" . $this->handle->escape($famtree[1]) . "', '" . $this->handle->escape($fam_ref_name) . "')";
			}
			else
				list($fam2ID) = $this->handle->fetch($result);
			
			$fam_ref_name = Utils::toDashAz09($famtree[2]);
			$result = & $this->handle->query("select id from families_fr where ref_name = '" . $this->handle->escape($fam_ref_name) . "'", __FILE__, __LINE__, false);
			if ($this->handle->numrows($result, __FILE__, __LINE__) == 0)
			{
				$fam3ID = generateID(12, 9999, 'id', 'families', $this->handle);
				$queryF3A = "insert into families (id, idParent) values(" . $fam3ID . ", '" . $fam2ID . "')";
				$queryF3B = "insert into families_fr (id, name, ref_name) values(" . $fam3ID . ", '" . $this->handle->escape($famtree[2]) . "', '" . $this->handle->escape($fam_ref_name) . "')";
			}
			else
				list($fam3ID) = $this->handle->fetch($result);
		}
		elseif ($lentree == 2 || $lentree > 3)
		{
			$this->status = ($this->id_final != 0) ? __IP_NOT_VALID_UPDATE__ : __IP_NOT_VALID__;
			$this->lastErrorMessage = "L'arbre de famille du produit " . $this->id . " n'est pas valide. Veuillez contacter l'administrateur si cette erreur se reproduit";
			return false;
		}
		
		$queryC = "insert into products_families (";	$query2 = "values (";
		$queryC .= "idProduct, ";						$query2 .= $this->id_final . ", ";
		$queryC .= "idFamily) ";						$query2 .= $fam3ID . ")";
		$queryC .= $query2;
		
		if ($this->online_sell)
		{
			$content = array("Référence TC", "Libellé", "Référence Fournisseur");
			$mixed_data_entitle = mb_unserialize($this->mixed_data_entitle);
			foreach ($mixed_data_entitle as $data_entitle) $content[] = $data_entitle;
			array_push($content, "Unité", "Taux TVA", "Prix Fournisseur", "Marge", "Prix Public");
			$content = serialize($content);
			
			$queryD = "insert into references_cols (";		$query2 = "values (";
			$queryD .= "idProduct, ";						$query2 .= $this->id_final . ", ";
			$queryD .= "content) ";							$query2 .= "'" . $this->handle->escape($content) . "')";
			$queryD .= $query2;
		}
		else
		{
			if (!empty($this->ref_count))
			{
				$content = array("Référence TC", "Libellé");
				$mixed_data_entitle = mb_unserialize($this->mixed_data_entitle);
				foreach ($mixed_data_entitle as $data_entitle) $content[] = $data_entitle;
				array_push($content, "Prix");
				$content = serialize($content);
				
				$queryD = "insert into references_cols (";		$query2 = "values (";
				$queryD .= "idProduct, ";						$query2 .= $this->id_final . ", ";
				$queryD .= "content) ";							$query2 .= "'" . $this->handle->escape($content) . "')";
				$queryD .= $query2;
			}
		}
		// stats default to 0
		$queryS = "INSERT INTO products_stats (`id`, `hits`, `orders`, `estimates`, `leads`, `first_hit_time`) VALUES (".$this->id_final.",0,0,0,0,".$timestamp.")";
		
		if (isset($queryF2A) && (!$this->handle->query($queryF2A, __FILE__, __LINE__, false) || !$this->handle->query($queryF2B, __FILE__, __LINE__, false)))
		{
			$this->lastErrorMessage = "Erreur fatale SQL lors de la création automatique de la famille de niveau 2 du produit " . $this->id . ".";
			return false;
		}
		if (isset($queryF3A) && (!$this->handle->query($queryF3A, __FILE__, __LINE__, false) || !$this->handle->query($queryF3B, __FILE__, __LINE__, false)))
		{
			$this->lastErrorMessage = "Erreur fatale SQL lors de la création automatique de la famille de niveau 3 du produit " . $this->id . ".";
			return false;
		}
		
		//print "<br/>A ".$queryA . "<br/>B ".$queryB . "<br/>C ".$queryC . "<br/>D ".$queryD;
		//print "<br/>F2A ".$queryF2A . "<br/>F2B ".$queryF2B . "<br/>F3A ".$queryF3A . "<br/>F3B ".$queryF3B;
		if (!$this->handle->query($queryA, __FILE__, __LINE__, false) ||
			!$this->handle->query($queryB, __FILE__, __LINE__, false) ||
			!$this->handle->query($queryC, __FILE__, __LINE__, false) || 
			!$this->handle->query($queryS, __FILE__, __LINE__, false))
		{
			$this->lastErrorMessage = "Erreur fatale SQL lors de la finalisation de l'import du produit " . $this->id . ".";
			return false;
		}
		
		if (isset($queryD) && !$this->handle->query($queryD, __FILE__, __LINE__, false))
		{
			$this->lastErrorMessage = "Erreur fatale SQL lors de la finalisation de l'import des références du produit " . $this->id . ".";
			return false;
		}
		
		foreach($this->references as $ref)
		{
			$idTC = generateIDTC($this->handle);
			
			$query = "insert into references_content (";	$query2 = "values (";
			$query .= "id, ";								$query2 .= $idTC . ", ";
			$query .= "idProduct, ";						$query2 .= $this->id_final . ", ";
			$query .= "label, "	;							$query2 .= "'" . $this->handle->escape($ref['label']) . "', ";
			$query .= "content, ";							$query2 .= "'" . $this->handle->escape($ref['mixed_data']) . "', ";
			$query .= "refSupplier, ";						$query2 .= "'" . $this->handle->escape($ref['ref_supplier']) . "', ";
			$query .= "unite, ";							$query2 .= $ref['unit'] . ", ";
			$query .= "idTVA, ";							$query2 .= $this->getVATids($ref['VAT']) . ", ";
			$query .= "price2, ";							$query2 .= $ref['price2'] . ", ";
			$query .= "marge, ";							$query2 .= $ref['marge'] . ", ";
			$query .= "price, ";							$query2 .= $ref['price'] . ", ";
			$query .= "classement) ";						$query2 .= $ref['order'] . ")";
			$query .= $query2;
			
			if (!$this->handle->query($query, __FILE__, __LINE__, false))
			{
				$this->lastErrorMessage = "Erreur fatale SQL lors de la finalisation de la référence " . $ref['id'] . " du produit " . $this->id . ".";
				return false;
			}
		}
		
		if (!empty($this->url_image)) {
			$ext = strtolower(substr($this->url_image, -3));
			if ($ext == "jpg") {
				if ($content = Utils::fetchURL($this->url_image)) {
					$fileName = $this->id_final."-1.jpg";
					$fh = fopen(PRODUCTS_IMAGE_INC."zoom/".$fileName, "w");
					fwrite($fh, $content);
					fclose($fh);
					ImageResize(250, 225, PRODUCTS_IMAGE_INC."zoom/".$fileName, PRODUCTS_IMAGE_INC."card/".$fileName);
					ImageResize(147, 110, PRODUCTS_IMAGE_INC."zoom/".$fileName, PRODUCTS_IMAGE_INC."thumb_big/".$fileName);
					ImageResize(112, 84, PRODUCTS_IMAGE_INC."zoom/".$fileName, PRODUCTS_IMAGE_INC."thumb_small/".$fileName);
				}
			}
		}
		
		$docs = mb_unserialize($this->url_docs);
		$i = 1;
		foreach($docs as $doc)
		{
			if (!empty($doc))
			{
				$ext = strtolower(substr($doc, -3));
				if ($ext == "pdf" || $ext == "doc")
				{
					if ($content = Utils::fetchURL($doc))
					{
						$fh = fopen(PRODUCTS_FILES_INC . $this->id_final . "-" . $i . "." . $ext, "w");
						fwrite($fh, $content);
						fclose($fh);
					}
				}
			}
			$i++;
		}
		
		$this->status = __IP_FINALIZED__;
		return true;
	}
	
	function finalize_update()
	{
		$result = & $this->handle->query("select idAdvertiser from imports where id = " . $this->id_import, __FILE__, __LINE__);
		list($idAdvertiser) = $this->handle->fetch($result);
		
		$timestamp = time();
		
		// Setting queries for product update
		$queryA = "update products set " .
		"timestamp = " .				$timestamp . ", " .
		"price = " .					"'" . $this->handle->escape($this->price) . "' " .
		"where id = " . $this->id_final;
		
		$queryB = "update products_fr set " .
		"name = " .					"'" . $this->handle->escape($this->name) . "', " .
		"fastdesc = " .				"'" . $this->handle->escape($this->fastdesc) . "', " .
		"ref_name = " .				"'" . $this->handle->escape($this->ref_name) . "', " .
		"alias = " .				"'" . $this->handle->escape($this->alias) . "', " .
		"keywords = " .				"'" . $this->handle->escape($this->keywords) . "', " .
		"descc = " .				"'" . $this->handle->escape($this->descc) . "', " .
		"descd = " .				"'" . $this->handle->escape($this->descd) . "', " .
		"delai_livraison = " .		"'" . $this->handle->escape($this->delivery_time) . "' " .
		"where id = " . $this->id_final;
		
		// Analyzing the family tree and creation of new families if needed
		$famtree = explode("->", $this->family_name);
		$lentree = count($famtree);
		if ($lentree == 1)
		{
			// Simple family, it must exist
			$fam_ref_name = Utils::toDashAz09($this->family_name);
			$result = & $this->handle->query("select id from families_fr where ref_name = '" . $this->handle->escape($fam_ref_name) . "'", __FILE__, __LINE__, false);
			list($fam3ID) = $this->handle->fetch($result);
		}
		elseif ($lentree == 3)
		{
			// Complete tree, creation of families id needed
			$fam_ref_name = Utils::toDashAz09($famtree[0]);
			$result = & $this->handle->query("select id from families_fr where ref_name = '" . $this->handle->escape($fam_ref_name) . "'", __FILE__, __LINE__, false);
			list($fam1ID) = $this->handle->fetch($result);
			
			$fam_ref_name = Utils::toDashAz09($famtree[1]);
			$result = & $this->handle->query("select id from families_fr where ref_name = '" . $this->handle->escape($fam_ref_name) . "'", __FILE__, __LINE__, false);
			if ($this->handle->numrows($result, __FILE__, __LINE__) == 0)
			{
				$fam2ID = generateID(12, 9999, 'id', 'families', $this->handle);
				$queryF2A = "insert into families (id, idParent) values(" . $fam2ID . ", '" . $fam1ID . "')";
				$queryF2B = "insert into families_fr (id, name, ref_name) values(" . $fam2ID . ", '" . $this->handle->escape($famtree[1]) . "', '" . $this->handle->escape($fam_ref_name) . "')";
			}
			else
				list($fam2ID) = $this->handle->fetch($result);
			
			$fam_ref_name = Utils::toDashAz09($famtree[2]);
			$result = & $this->handle->query("select id from families_fr where ref_name = '" . $this->handle->escape($fam_ref_name) . "'", __FILE__, __LINE__, false);
			if ($this->handle->numrows($result, __FILE__, __LINE__) == 0)
			{
				$fam3ID = generateID(12, 9999, 'id', 'families', $this->handle);
				$queryF3A = "insert into families (id, idParent) values(" . $fam3ID . ", '" . $fam2ID . "')";
				$queryF3B = "insert into families_fr (id, name, ref_name) values(" . $fam3ID . ", '" . $this->handle->escape($famtree[2]) . "', '" . $this->handle->escape($fam_ref_name) . "')";
			}
			else
				list($fam3ID) = $this->handle->fetch($result);
		}
		elseif ($lentree == 2 || $lentree > 3)
		{
			// Bad family tree
			$this->status = ($this->id_final != 0) ? __IP_NOT_VALID_UPDATE__ : __IP_NOT_VALID__;
			$this->lastErrorMessage = "L'arbre de famille du produit " . $this->id . " n'est pas valide. Veuillez contacter l'administrateur si cette erreur se reproduit";
			return false;
		}
		
		// Setting SQL queries for families
		$queryC1 = "select idProduct, idFamily from products_families where idProduct = " . $this->id_final . " and idFamily = " . $fam3ID;
		$queryC2 = "insert into products_families (";	$query2 = "values (";
		$queryC2 .= "idProduct, ";						$query2 .= $this->id_final . ", ";
		$queryC2 .= "idFamily) ";						$query2 .= $fam3ID . ")";
		$queryC2 .= $query2;
		
		// Setting SQL queries for references cols header
		$queryD1 = "delete from references_cols where idProduct = " . $this->id_final;
		if ($this->online_sell)
		{
			// For products from suppliers
			$content = array("Référence TC", "Libellé", "Référence Fournisseur");
			$mixed_data_entitle = mb_unserialize($this->mixed_data_entitle);
			foreach ($mixed_data_entitle as $data_entitle) $content[] = $data_entitle;
			array_push($content, "Unité", "Taux TVA", "Prix Fournisseur", "Marge", "Prix Public");
			$content = serialize($content);
			
			$queryD2 = "insert into references_cols (";		$query2 = "values (";
			$queryD2 .= "idProduct, ";						$query2 .= $this->id_final . ", ";
			$queryD2 .= "content) ";						$query2 .= "'" . $this->handle->escape($content) . "')";
			$queryD2 .= $query2;
		}
		else
		{
			// For products from advertisers
			if (!empty($this->ref_count))
			{
				$content = array("Référence TC", "Libellé");
				$mixed_data_entitle = mb_unserialize($this->mixed_data_entitle);
				foreach ($mixed_data_entitle as $data_entitle) $content[] = $data_entitle;
				array_push($content, "Prix");
				$content = serialize($content);
				
				$queryD2 = "insert into references_cols (";		$query2 = "values (";
				$queryD2 .= "idProduct, ";						$query2 .= $this->id_final . ", ";
				$queryD2 .= "content) ";						$query2 .= "'" . $this->handle->escape($content) . "')";
				$queryD2 .= $query2;
			}
		}
		
		// Executing the family lvl 2 creation queries if it exists
		if (isset($queryF2A) && (!$this->handle->query($queryF2A, __FILE__, __LINE__, false) || !$this->handle->query($queryF2B, __FILE__, __LINE__, false)))
		{
			$this->lastErrorMessage = "Erreur fatale SQL lors de la création automatique de la famille de niveau 2 du produit " . $this->id . ".";
			return false;
		}
		// Executing the family lvl 3 creation queries if it exists
		if (isset($queryF3A) && (!$this->handle->query($queryF3A, __FILE__, __LINE__, false) || !$this->handle->query($queryF3B, __FILE__, __LINE__, false)))
		{
			$this->lastErrorMessage = "Erreur fatale SQL lors de la création automatique de la famille de niveau 3 du produit " . $this->id . ".";
			return false;
		}
		
		//print "<br/>A ".$queryA . "<br/>B ".$queryB . "<br/>C ".$queryC . "<br/>D ".$queryD;
		//print "<br/>F2A ".$queryF2A . "<br/>F2B ".$queryF2B . "<br/>F3A ".$queryF3A . "<br/>F3B ".$queryF3B;
		// Executing the main product update queries
		if (!$this->handle->query($queryA, __FILE__, __LINE__, false) ||
			!$this->handle->query($queryB, __FILE__, __LINE__, false))
		{
			$this->lastErrorMessage = "Erreur fatale SQL lors de la finalisation de l'import du produit " . $this->id . ".";
			return false;
		}
		
		// Executing the family attribution queries
		if (!($result = & $this->handle->query($queryC1, __FILE__, __LINE__, false)) ||
			(($this->handle->numrows($result, __FILE__, __LINE__) == 0) && !$this->handle->query($queryC2, __FILE__, __LINE__, false)))
		{
			$this->lastErrorMessage = "Erreur fatale SQL lors de l'attribution de la famille du produit " . $this->id . ".";
			return false;
		}
		
		// Executing the references headers creation queries
		if ((!$this->handle->query($queryD1, __FILE__, __LINE__, false)) ||
			(isset($queryD2) && !$this->handle->query($queryD2, __FILE__, __LINE__, false)))
		{
			$this->lastErrorMessage = "Erreur fatale SQL lors de la création des en-têtes des références du produit " . $this->id . ".";
			return false;
		}
		
		$query = "delete from references_content where idProduct = " . $this->id_final;
		if (!$this->handle->query($query, __FILE__, __LINE__, false))
		{
			$this->lastErrorMessage = "Erreur fatale SQL lors de la réinitialisation des références du produit " . $this->id . ".";
			return false;
		}
		
		foreach($this->references as $ref)
		{
			if (empty($ref['id_final'])) $ref['id_final'] = generateIDTC($this->handle);
			
			$query = "insert into references_content (";	$query2 = "values (";
			$query .= "id, ";								$query2 .= $ref['id_final'] . ", ";
			$query .= "idProduct, ";						$query2 .= $this->id_final . ", ";
			$query .= "label, "	;							$query2 .= "'" . $this->handle->escape($ref['label']) . "', ";
			$query .= "content, ";							$query2 .= "'" . $this->handle->escape($ref['mixed_data']) . "', ";
			$query .= "refSupplier, ";						$query2 .= "'" . $this->handle->escape($ref['ref_supplier']) . "', ";
			$query .= "unite, ";							$query2 .= $ref['unit'] . ", ";
			$query .= "idTVA, ";							$query2 .= $this->getVATids($ref['VAT']) . ", ";
			$query .= "price2, ";							$query2 .= $ref['price2'] . ", ";
			$query .= "marge, ";							$query2 .= $ref['marge'] . ", ";
			$query .= "price, ";							$query2 .= $ref['price'] . ", ";
			$query .= "classement) ";						$query2 .= $ref['order'] . ")";
			$query .= $query2;
			
			if (!$this->handle->query($query, __FILE__, __LINE__, false))
			{
				$this->lastErrorMessage = "Erreur fatale SQL lors de la finalisation de la référence " . $ref['id'] . " du produit " . $this->id . ".";
				return false;
			}
		}
		
		if (!empty($this->url_image))
		{
			$ext = strtolower(substr($this->url_image, -3));
			if ($ext == "jpg")
			{
				if ($content = Utils::fetchURL($this->url_image))
				{
					$fh = fopen(PRODUCTS_IMAGE_INC . "zoom/" . $this->id_final . ".jpg", "w");
					fwrite($fh, $content);
					fclose($fh);
					ImageResize(100,  75, PRODUCTS_IMAGE_INC . "zoom/" . $this->id_final . ".jpg", PRODUCTS_IMAGE_INC . $this->id_final . ".jpg");
					ImageResize(240, 240, PRODUCTS_IMAGE_INC . "zoom/" . $this->id_final . ".jpg", PRODUCTS_IMAGE_INC . 'cards/' . $this->id_final . ".jpg");
				}
			}
		}
		
		$docs = mb_unserialize($this->url_docs);
		$i = 1;
		foreach($docs as $doc)
		{
			if (!empty($doc))
			{
				$ext = strtolower(substr($doc, -3));
				if ($ext == "pdf" || $ext == "doc")
				{
					if ($content = Utils::fetchURL($doc))
					{
						$fh = fopen(PRODUCTS_FILES_INC . $this->id_final . "-" . $i . "." . $ext, "w");
						fwrite($fh, $content);
						fclose($fh);
					}
				}
			}
			$i++;
		}
		
		$this->status = __IP_FINALIZED_UPDATE__;
		return true;
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
	
	function getVATids($VAT)
	{
	    if ($this->VATids === NULL)
		{
			$this->VATids = array();
		    $result = & $this->handle->query("select taux, id from tva", __FILE__, __LINE__ );
			while($record = & $this->handle->fetch($result))
				$this->VATids[$record[0]] = $record[1];
		}
		
		return isset($this->VATids[$VAT]) ? $this->VATids[$VAT] : 1;
	}
	
}

?>
