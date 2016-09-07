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

class Import
{
	/* Connection Handle */
	var $handle = NULL;
	
	/* Product's fields */
	var $id = 0;
	var $timestamp = 0;
	var $idAdvertiser = 0;
	var $create_time = 0;
	var $nbp_final = 0;
	var $nbp_valid = 0;
	var $nbp_notvalid = 0;
	var $type = 0;
	var $status = 0;
	
	var $exist = false;
	var $lastErrorMessage = "";
	
	/* Constructor */
	function Import(& $handle, $id = NULL)
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
			$result = & $this->handle->query("select id from imports where id = " . $id, __FILE__, __LINE__);
		}
		while ($this->handle->numrows($result, __FILE__, __LINE__) >= 1);
		
		$this->id = $id;
	}
	
	function Load()
	{
		$this->exist = false;
		
		$query = "select id, timestamp, idAdvertiser, create_time, nbp_final, nbp_valid, nbp_notvalid, type, status " .
		"from imports " .
		"where id = " . $this->id;

                $result = & $this->handle->query($query, __FILE__, __LINE__, false);
		if ($this->handle->numrows($result, __FILE__, __LINE__) > 0)
		{
			$record = & $this->handle->fetchAssoc($result);
			foreach($record as $name => $value) $this->$name = $value;
			$this->exist = true;
		}
		else $this->lastErrorMessage = "L'import n'existe pas dans la base de donnée.";
	}
	
	function Save()
	{
		$this->timestamp = time();
		if (!$this->exist)
		{
			$this->create_time = $this->timestamp;
			if (empty($this->id)) $this->generateID();
			$query = "insert into imports (";		$query2 = "values (";
			$query .= "id, ";						$query2 .= $this->id . ", ";
			$query .= "timestamp, ";				$query2 .= $this->timestamp . ", ";
			$query .= "idAdvertiser, ";				$query2 .= $this->idAdvertiser . ", ";
			$query .= "create_time, ";				$query2 .= $this->create_time . ", ";
			$query .= "nbp_final, ";				$query2 .= $this->nbp_final . ", ";
			$query .= "nbp_valid, ";				$query2 .= $this->nbp_valid . ", ";
			$query .= "nbp_notvalid, ";				$query2 .= $this->nbp_notvalid . ", ";
			$query .= "type, ";						$query2 .= $this->type . ", ";
			$query .= "status) ";					$query2 .= $this->status . ")";
			$query .= $query2;
		}
		else
		{
			$query .= "update imports set " .
			"timestamp = " .				$this->timestamp . ", " .
			"idAdvertiser = " .				$this->idAdvertiser . ", " .
			"create_time = " .				$this->create_time . ", " .
			"nbp_final = " .				$this->nbp_final . ", " .
			"nbp_valid = " .				$this->nbp_valid . ", " .
			"nbp_notvalid = " .				$this->nbp_notvalid . ", " .
			"type = " .					$this->type . ", " .
			"status = " .					$this->status . " " .
			"where id = " . $this->id;
		}
                
		if (!$this->handle->query($query, __FILE__, __LINE__, false))
		{
			$this->lastErrorMessage = "Erreur fatale SQL lors de l'ajout/la modification de l'import " . $this->id;
			return false;
		}
		
		$this->exist = true;
		
		return true;
	}
	
	function UpdateStatus()
	{
		
		$result = $this->handle->query("select count(id) from imports_products where id_import = " . $this->id . " and status >= " . __IP_NOT_VALID__ . " and status < " . __IP_VALID__, __file__, __line__, false);
		list($this->nbp_notvalid) = $this->handle->fetch($result);
		
		$result = $this->handle->query("select count(id) from imports_products where id_import = " . $this->id . " and status >= " . __IP_VALID__ . " and status < " . __IP_FINALIZED__, __file__, __line__, false);
		list($this->nbp_valid) = $this->handle->fetch($result);
		
		$result = $this->handle->query("select count(id) from imports_products where id_import = " . $this->id . " and status >= " . __IP_FINALIZED__, __file__, __line__, false);
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

        	function UpdateStatusSupplier()
	{
                // total number of active products by references of the advertiser
                $query = "SELECT COUNT(p.idTC)
                          FROM references_content rc 
                          LEFT JOIN products p ON p.id = rc.idProduct 
                          LEFT JOIN products_fr pfr ON p.id = pfr.id 
                          WHERE rc.deleted = 0 AND p.idAdvertiser = '" . $this->idAdvertiser."' AND pfr.active = 1;";
//        var_dump($query);
		$result = & $this->handle->query($query, __FILE__, __LINE__, false);
                list($this->nbp_final) = $this->handle->fetch($result);

		$result = $this->handle->query("select count(id) from imports_suppliers where id_import = " . $this->id . " and status >= " . __IP_NOT_VALID__ . " and status < " . __IP_VALID__, __file__, __line__, false);
		list($this->nbp_notvalid) = $this->handle->fetch($result);

		$result = $this->handle->query("select count(id) from imports_suppliers where id_import = " . $this->id . " and status >= " . __IP_VALID__ . " and status < " . __IP_FINALIZED__, __file__, __line__, false);
		list($this->nbp_valid) = $this->handle->fetch($result);

//		$result = $this->handle->query("select count(id) from imports_suppliers where id_import = " . $this->id . " and status >= " . __IP_FINALIZED__, __file__, __line__, false);
//		list($this->nbp_final) = $this->handle->fetch($result);
//          fichier non valide    __IP_NOT_VALID__
//          en attente            __IP_VALID__
//          importé               __IP_FINALIZED__
//          import annulé         __IP_FINALIZED_UPDATE__
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
				if ($this->nbp_final > 0) $this->status = __I_VF__; //
				else $this->status = __I_V__;
			}
			else
			{
				if ($this->nbp_final > 0) $this->status = __I_0__;//__I_F__
				else $this->status = __I_0__;
			}
		}
	}

        function getSupplierName(){

          $query = 'select nom1 from advertisers where id = '.$this->idAdvertiser;
          $result = $this->handle->query($query, __file__, __line__, false);
		$res = $this->handle->fetch($result);

                return $res[0];

        }

        function getSupplierPriceType(){

          $query = 'select prixPublic from advertisers where id = '.$this->idAdvertiser;
          $result = $this->handle->query($query, __file__, __line__, false);
		$res = $this->handle->fetch($result);

                return $res[0];

        }

        function finalizeImport(){

          $query = "select reference, price from imports_suppliers where id_import = ". $this->id;

          $priceType = $this->getSupplierPriceType();
          
          $result = $this->handle->query($query, __file__, __line__, false);
		if ($this->handle->numrows($result, __FILE__, __LINE__) > 0)
		{
			while($record = & $this->handle->fetchAssoc($result)){

                          $this->updateReference($record['reference'], $record['price'], $priceType);

                        }
                        $this->handle->query("update imports set status = ".__I_V__." where id = ". $this->id, __file__, __line__, false);
                        return true;
		}else
                  return false;

        }

        function cancelImport(){

          $query = "select reference, former_price from imports_suppliers where id_import = ". $this->id;

          $priceType = $this->getSupplierPriceType();

          $result = $this->handle->query($query, __file__, __line__, false);
		if ($this->handle->numrows($result, __FILE__, __LINE__) > 0)
		{
			while($record = & $this->handle->fetchAssoc($result)){

                          $this->updateReference($record['reference'], $record['former_price'], $priceType);

                        }
                        $this->handle->query("update imports set status = ".__I_0__." where id = ". $this->id, __file__, __line__, false);
                        return true;
		}else
                  return false;

        }

        function updateReference($ref = null, $price = null, $priceType = null){

          if($ref != null && $price != null && $priceType != null){

            if($priceType == 1){
              $this->handle->query("update references_content rc ".
              "join products p on p.id = rc.idProduct ".
              "set rc.price = ".$price." where rc.refSupplier = '".$ref."' and p.idAdvertiser = ".$this->idAdvertiser, __file__, __line__, false);
              $this->handle->query("update references_content rc ".
              "join products p on p.id = rc.idProduct ".
              "set rc.price2 = round(rc.price*(1-(rc.marge/100)),2) where rc.refSupplier = '".$ref."' and p.idAdvertiser = ".$this->idAdvertiser, __file__, __line__, false);
            }elseif($priceType == 0){
              $this->handle->query("update references_content rc ".
              "join products p on p.id = rc.idProduct ".
              "set rc.price2 = ".$price." where rc.refSupplier = '".$ref."' and p.idAdvertiser = ".$this->idAdvertiser, __file__, __line__, false);
              $this->handle->query("update references_content rc ".
              "join products p on p.id = rc.idProduct ".
              "set rc.price = round(rc.price2/(1-(rc.marge/100)),2) where rc.refSupplier = '".$ref."' and p.idAdvertiser = ".$this->idAdvertiser, __file__, __line__, false);
            }

          }

        }

        function getStatus(){

          if( isset($this->status) ){

            $status = '<div class="legend-group"><span id="importStatus" ';

            switch ($this->status)
            {
              // N = Not Valid = 100 ; V = Valid = 10 ; F = Finalized = 1;
//              define("__I_NVF__", 111);
//define("__I_NV__", 110);
//define("__I_NF__", 101);
//define("__I_N__", 100);
//define("__I_VF__", 011);
//define("__I_V__", 010);
//define("__I_F__", 001);
//define("__I_0__", 000);

//                    case __IP_NOT_VALID__ : $status = "En attente d'import"; break;
//                    case __IP_VALID__ : $status = "Importé"; break;
//                    case __IP_FINALIZED__ : $status = "Import annulé"; break;
                    case __I_NVF__ : $status .= 'class="legend-label not-valid">non importable'; break;
                    case __I_NV__ : $status .= 'class="legend-label not-valid">non valide'; break;
                    case __I_NF__ : $status .= 'class="legend-label not-valid">non valide'; break;
                    case __I_N__ : $status .= 'class="legend-label not-valid">non valide'; break;
                    case __I_VF__ : $status .= 'class="legend-label valid">en attente'; break;
                    case __I_V__ : $status .= 'class="legend-label finalized">importé'; break;
                    case __I_F__ : $status .= 'class="legend-label finalized">importé'; break;
                    case __I_0__ : $status .= 'class="legend-label cancelled">annulé'; break;
                    default : $status = ''; break;
            }
            $status .= '</span></div>';

            return $status;
          }

        }
	
        function  getNbRefWithoutProduct(){
            // file ref without product
            $result = & $this->handle->query("select count(id) as nbRefWithoutProduct from imports_suppliers".
            " where id_Import = ". $this->id . " and nb_idTC = 0" , __FILE__, __LINE__, false);
            $rec = & $this->handle->fetchAssoc($result);
//            $nbRefWithoutProduct = $rec['nbRefWithoutProduct'];

            return $rec['nbRefWithoutProduct'];
        }

        function  getNbProductByFileRef(){
            // nb products by file ref
            $result = & $this->handle->query("select sum(nb_idTC) as nbProductByFileRef from imports_suppliers".
            " where id_Import = ". $this->id . " and nb_idTC != 0" , __FILE__, __LINE__, false);
            $rec = & $this->handle->fetchAssoc($result);
//            $nbProductByFileRef = $rec['nbProductByFileRef'];

            return $rec['nbProductByFileRef'];
        }
}

?>
