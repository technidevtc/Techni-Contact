<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : OD pour Hook Network SARL - http://www.hook-network.com
 Date de création : 19 janvier 2011

 Mises à jour :

 Fichier : /secure/fr/manager/import/_ClassImportUpdateSupplier.php
 Description : Classe utilisateur manager

/=================================================================*/

require_once(ADMIN . 'generator.php');

//function DeleteProduct(& $handle, $id)
//{
//	if (!$handle->query("delete from imports_products where id = " . $id, __FILE__, __LINE__, false)) return false;
//	if (!$handle->query("delete from imports_references where import_product_id = " . $id, __FILE__, __LINE__, false)) return false;
//	return true;
//}

class ImportUpdateSupplier
{
	/* Connection Handle */
	var $handle = NULL;
	
	//var $parent_import_o = NULL;
	
	/* Product's fields */
	var $id = 0;
	var $id_import = 0;
	var $id_advertiser = 0;
	var $reference = 0;
	var $price = 0;
	var $former_price = "";
	var $nb_idTC = "";
	var $status = 0;
	
//	var $references = array();
	
//	/* TVA Table */
//	var $tauxTVA = NULL;
//	var $VATids = NULL;
	
	var $exist = false;
        var $referenceExist = false;
	var $lastErrorMessage = "";
	
	//var $productFields = array("id", "id_import", "id_final", "family_name", "family_ref_name", "name", "ref_name", "fastdesc", "descc", "descd", "delivery_time", "url_image", "ref_count", "mixed_data_entitle", "status");
	//var $referenceFields = array("id", "ref_supplier", "label", "mixed_data", "price", "price_deleted", "price2", "marge", "unit", "VAT", "order");
	
	/* Constructor */
	function ImportUpdateSupplier(& $handle, $id = NULL)
	{
		$this->handle = & $handle;
		if ($id != NULL)
		{
			$this->id = $id;
			$this->Load();
		}
	}

	function GenerateProductID()
	{
		do
		{
			$id = mt_rand(1, 999999999);
			$result = & $this->handle->query("select id from imports_suppliers where id = " . $id, __FILE__, __LINE__);
		}
		while ($this->handle->numrows($result, __FILE__, __LINE__) >= 1);

		$this->id = $id;
	}
	
	function Load()
	{
		$query = "select id, id_import, reference,  price, former_price, nb_idTC, status " .
		"from imports_suppliers " .
		"where id = " . $this->id;

		$result = & $this->handle->query($query, __FILE__, __LINE__, false);
		if (!$this->handle->numrows($result, __FILE__, __LINE__) > 0)
                  $this->lastErrorMessage = "Le produit " . $this->id . " n'existe pas dans la base de donnée.";
                else
                  $this->exist = true;

	}

        function referenceExists($idAdvertiser = NULL, $reference = NULL){

          if($idAdvertiser && $reference){
            $this->id_advertiser = $idAdvertiser;
            $this->reference = $reference;
            $query = "SELECT count(rc.idProduct) AS nb_products
                      FROM references_content rc
                      INNER JOIN products p ON p.id = rc.idProduct
                      WHERE rc.refSupplier = '" . $this->reference . "' AND rc.deleted = 0 AND p.idAdvertiser = '" . $this->id_advertiser."'";
//        var_dump($query);
		$result = & $this->handle->query($query, __FILE__, __LINE__, false);
                $nb_products = $this->handle->fetchAssoc($result, __FILE__, __LINE__);
//                var_dump($nb_products);
		if (!$nb_products['nb_products'] > 0){
                  $this->lastErrorMessage = "Le produit " . $this->id . " n'existe pas dans la base de donnée.";
                  return false;
                }else{
                  $this->referenceExist = true;
                  $this->nb_idTC = $nb_products['nb_products'];
                  return true;
                }
          }
        }

        function Save()
	{
		if (!$this->exist)
		{
			if ($this->id == 0) $this->GenerateProductID();
			$query = "insert into imports_suppliers ("; $query2 = "values (";
			$query .= "id, ";						$query2 .= $this->id . ", ";
			$query .= "id_import, ";				$query2 .= $this->id_import . ", ";
			$query .= "reference, ";				$query2 .= "'" . $this->handle->escape($this->reference) . "', ";
			$query .= "price, ";					$query2 .= "'" . ($this->ref_count > 0 ? "ref" : $this->handle->escape($this->price)) . "', ";
			$query .= "former_price, ";				$query2 .= "'" . $this->handle->escape($this->former_price) . "', ";
			$query .= "nb_idTC, ";					$query2 .= "'" . $this->handle->escape($this->nb_idTC) . "', ";
			$query .= "status) ";					$query2 .= $this->status . ")";
			$query .= $query2;
		}
		else
		{
			$query .= "update imports_suppliers set " .
			"id_import = " .				$this->id_import . ", " .
			"reference = " .						"'" . $this->handle->escape($this->reference) . "', " .
			"price = " .					"'" . ($this->ref_count > 0 ? "ref" : $this->handle->escape($this->price)) . "', " .
			"former_price = " .					"'" . $this->handle->escape($this->former_price) . "', " .
			"nb_idTC = " .					"'" . $this->handle->escape($this->nb_idTC) . "', " .
			"status = " .					$this->status . " " .
			"where id = " . $this->id;

//			if (!$this->handle->query("delete from imports_references where import_product_id = " . $this->id, __FILE__, __LINE__, false))
//			{
//				$this->lastErrorMessage = "Erreur fatale SQL lors de la suppression des références du produit " . $this->id;
//				return false;
//			}
		}
//                var_dump($query);
		if (!$this->handle->query($query, __FILE__, __LINE__, false))
		{
			$this->lastErrorMessage = "Erreur fatale SQL lors de la modification du prix " . $this->id;
			return false;
		}

//		for($i = 0; $i < $this->ref_count; $i++)
//		{
//			$ref = & $this->references[$i];
//
//			if (empty($ref['id'])) $ref['id'] = $this->GenerateReferenceID();
//			$ref['id_final'] = (int)$ref['id_final'];
//
//			$query = "insert into imports_references ("; $query2 = "values (";
//			$query .= "id, ";						$query2 .= $ref['id'] . ", ";
//			$query .= "import_product_id, "	;		$query2 .= $this->id . ", ";
//			$query .= "id_final, ";					$query2 .= $ref['id_final'] . ", ";
//			$query .= "ref_supplier, ";				$query2 .= "'" . $this->handle->escape($ref['ref_supplier']) . "', ";
//			$query .= "label, ";					$query2 .= "'" . $this->handle->escape($ref['label']) . "', ";
//			$query .= "mixed_data, ";				$query2 .= "'" . $this->handle->escape($ref['mixed_data']) . "', ";
//			$query .= "unit, ";						$query2 .= "'" . $ref['unit'] . "', ";
//			$query .= "VAT, ";						$query2 .= "'" . $ref['VAT'] . "', ";
//			$query .= "price2, ";					$query2 .= "'" . $ref['price2'] . "', ";
//			$query .= "marge, ";					$query2 .= "'" . $ref['marge'] . "', ";
//			$query .= "price, ";					$query2 .= "'" . $ref['price'] . "', ";
//			$query .= "price_deleted, ";			$query2 .= "'" . $ref['price_deleted'] . "', ";
//			$query .= "`order`) ";					$query2 .= "'" . $ref['order'] . "')";
//			$query .= $query2;
//
//
//			if (!$this->handle->query($query, __FILE__, __LINE__, false))
//			{
//				$this->lastErrorMessage = "Erreur fatale SQL lors de l'ajout de la référence " . $ref['id'] . " du produit " . $this->id . ".";
//				return false;
//			}
//		}

		//if ($this->parent_import_o) $this->parent_import_o->UpdateStatus();

		$this->exist = true;

		return true;
	}

	function UpdateStatus()
	{
//          fichier non valide    __IP_NOT_VALID__
//          en attente            __IP_VALID__
//          importé               __IP_FINALIZED__
//          import annulé         __IP_FINALIZED_UPDATE__


		if ($this->status >= __IP_FINALIZED__) return true;

		// Test the validity of product's fields
		if ($this->reference == "" ||
			$this->price == "" ||
			$this->price == 0
                        )
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
//							empty($ref['label']) ||
//							empty($ref['price2']) ||
							empty($ref['price']) //||
//							empty($ref['unit']) ||
//							empty($ref['VAT']) ||
//							empty($ref['order'])
                                                        )
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
						if (//empty($ref['label']) ||
							empty($ref['price'])// ||
//							empty($ref['order'])
							)
						{
							$this->status = ($this->id_final != 0) ? __IP_NOT_VALID_UPDATE__ : __IP_NOT_VALID__;
							$this->lastErrorMessage = "Un ou plusieurs champs obligatoires d'une ou plusieurs références du produit " . $this->id . " n'ont pas été renseignés.";
							return false;
						}
					}
				}
			}

			$this->status = ($this->id_final != 0) ? __IP_VALID_UPDATE__ : __IP_VALID__;
			return true;
		}
	}

        function setFormerPrice($priceType = null){
//var_dump($priceType);
          if(!$this->referenceExist || $priceType === null)
            return false;
//var_dump($this->referenceExist, $this->reference);
          $PT = $priceType ? 'price' : 'price2';
          $query = "SELECT rc.".$PT."
                    FROM references_content rc 
                    INNER JOIN products p ON p.id = rc.idProduct 
                    WHERE rc.refSupplier = '".$this->reference."' AND rc.deleted = 0";
//            var_dump($query);echo '<br><br>';
//            exit;
          $result = $this->handle->query($query, __FILE__, __LINE__, false);
          if (!$result)
		{
			$this->lastErrorMessage = "Erreur fatale SQL lors de la récupération le l'ancien prix " . $this->id;
			return false;
		}

          $formerPrice = $this->handle->fetchAssoc($result, __FILE__, __LINE__);
          $this->former_price = $formerPrice[$PT];
//var_dump($formerPrice);
          return true;
        }
	
}

?>
