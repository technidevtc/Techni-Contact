<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

require(ADMIN."logs.php");

$handle = DBHandle::get_instance();
$user = new BOUser();

if(!$user->login()) {
	header("Location: ".ADMIN_URL."login.html");
	exit();
}


if ((!isset($_POST['idImport'])
	&& !is_numeric($_POST['idImport'])) && (!isset($_POST['nouveautes']) || !isset($_POST['obsoletes']))){
	header("Location: ".ADMIN_URL."requests.html");
	exit();
}
else {

  require('_ClassImport.php');
  $imp = & new Import($handle, $_POST['idImport']);

	if (!$imp->exist) exit();

	require_once("Spreadsheet/Excel/Writer.php");

	$workbook = new Spreadsheet_Excel_Writer();

        if($_POST['nouveautes']){

          $workbook->send("Extrait des nouvelles références de " . $imp->getSupplierName() .".xls");

          // Creating a worksheet
          $worksheet = $workbook->addWorksheet('Liste des nouvelles références');

          $col_headers = array("Prix", "Référence fournisseur");

          $l = 0;
          foreach ($col_headers as $col_header) $worksheet->write($l, $c++, $col_header);
          $chi = array_flip($col_headers); // Cols Headers Index
          $nfchi = count($chi); // Next Free Col Header Index
          $l++;

          $result = $handle->query("
		SELECT price, reference
		FROM imports_suppliers
		WHERE id_import = ". $_POST['idImport'] ." and nb_idtc = 0", __FILE__, __LINE__);

          while ($cols = $handle->fetchAssoc($result)) {

                $c = 0;
		foreach($cols as $colName => &$colData) {


                  $worksheet->write($l, $c++, $colData);

		}
		unset($colData);

		$l++;

          }

        }  elseif($_POST['obsoletes']){

          $workbook->send("Extrait des produits obsolètes de " . $imp->getSupplierName() .".xls");

          // Creating a worksheet
          $worksheet = $workbook->addWorksheet('Liste des produits obsolètes');

          // Headers
          $col_headers = array("Nom produit", "ID fiche", "Libellé", "idTC", "Référence fournisseur", "Prix Public","Prix Fournisseur", "Typologie prix");
//        Nom produit fiche + ID fiche + Libellé tableau prix + ID TC + ref fournisseur en base manager + prix + typologie (fournisseur ou public)

          $l = 0;
          foreach ($col_headers as $col_header) $worksheet->write($l, $c++, $col_header);
          $chi = array_flip($col_headers); // Cols Headers Index
          $nfchi = count($chi); // Next Free Col Header Index
          $l++;

          $imp = & new Import($handle, $_POST['idImport']);

          $query = "SELECT rc.id AS idRC, rc.refSupplier
                    FROM references_content rc 
                    LEFT JOIN products p ON p.id = rc.idProduct 
                    LEFT JOIN products_fr pfr ON p.id = pfr.id 
                    WHERE rc.deleted = 0 AND p.idAdvertiser = '" . $imp->idAdvertiser ."' AND pfr.active = 1;";

          $result = $handle->query($query, __FILE__, __LINE__);

          $base = array();
          $a=0;
          if($handle->numrows($result) > 0)
            while ($cols = $handle->fetchAssoc($result)) {
              $base[$cols['refSupplier']] = $cols['idRC'];
            }
          
          $query = "select reference from imports_suppliers ".
                  "where id_import = '" . $_POST['idImport'] . "'" ;

          $result = $handle->query($query, __FILE__, __LINE__);

          unset($cols);
          $xls = array();
          if($handle->numrows($result) > 0)
            while ($cols = $handle->fetch($result)) {

              $xls[] = $cols[0];
            }

          $base = array_flip($base);

          $base = array_diff($base, $xls);

          if(count($base) > 0){

            $where = '';

            $a = 0;
            foreach ($base as $id => $ref){
              
              if($a!=0) $where .= ' OR ';
              $where .= 'rc.id = '.$id;
              $a++;
            }
//        Nom produit fiche + ID fiche (refTC) + Libellé tableau prix + ID TC + ref fournisseur en base manager + prix + typologie (fournisseur ou public)
            $query = "SELECT pfr.name, p.id, rc.label, rc.id, rc.refSupplier, rc.price, rc.price2, a.prixPublic
                      FROM products p 
                      LEFT JOIN references_content rc ON p.id = rc.idProduct AND rc.deleted = 0
                      LEFT JOIN products_fr pfr ON p.id = pfr.id 
                      LEFT JOIN advertisers a ON p.idAdvertiser = a.id 
                      WHERE " . $where;
            
            $result = $handle->query($query, __FILE__, __LINE__);
            while ($cols = $handle->fetchAssoc($result)) {

                  $c = 0;
                  foreach($cols as $colName => &$colData) {

                    if($colName == 'prixPublic') $colData = $colData == 1 ? 'Public' : 'Fournisseur';
                    $worksheet->write($l, $c++, $colData);

                  }
                  unset($colData);

                  $l++;
            }
          }


        }

	// Let's send the file
	$workbook->close();
}

?>
