<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
require(ADMIN . 'generator.php');

$handle = DBHandle::get_instance();

$cols_headers = $handle->escape(serialize(array("Référence TC", "Libellé", "Référence Fournisseur", "Unité", "Taux TVA", "Prix Fournisseur", "Marge", "Prix Public")));
$cols_content = $handle->escape(serialize(array()));

echo "Loading product's list to update...";
//$res = $handle->query("select p.*, pfr.*, a.category from products p, products_fr pfr, advertisers a where p.id = pfr.id and p.idAdvertiser = a.id and a.category = 1 and p.price REGEXP '^[0-9]+((\.|,)[0-9]{0,2})?$' and p.price2 REGEXP '^[0-9]+((\.|,)[0-9]{0,2})?$' and p.price2 != '0'", __FILE__, __LINE__, false);
$res = $handle->query("select p.*, pfr.*, a.category from products p, products_fr pfr, advertisers a where p.id = pfr.id and p.idAdvertiser = a.id and a.category = 1 and p.price REGEXP '^[0-9]+((\.|,)[0-9]{0,2})?$'", __FILE__, __LINE__, false);
echo " OK !";
echo "<br/>\n";
echo "<br/>\n";

$pdtcount = 0;
while ($pdt = $handle->fetchAssoc($res)) {
	echo "Product : " . $pdt["id"];
	
	$idTC = generateIDTC($handle);
	
	$handle->query("delete from references_cols where idProduct = " . $pdt["id"], __FILE__, __LINE__, false);
	$handle->query("delete from references_content where idProduct = " . $pdt["id"], __FILE__, __LINE__, false);
	echo " --> Cleaning OK";
	if ($handle->query("insert into references_cols (idProduct, content) values (" . $pdt["id"] . ", '" . $cols_headers . "')", __FILE__, __LINE__, false)) {
		echo " --> headers OK";
		if ($handle->query("
			insert into references_content (
			id, idProduct, label, content, refSupplier, price, price2, unite, marge, idTVA, classement
			) values (
			" . $pdt["idTC"] . ",
			" . $pdt["id"] . ",
			'" . $handle->escape($pdt["fastdesc"]) . "',
			'" . $cols_content . "',
			'" . $handle->escape($pdt["refSupplier"]) . "',
			'" . $handle->escape($pdt["price"]) . "',
			'" . $handle->escape($pdt["price2"]) . "',
			" . $pdt["unite"] . ",
			" . $pdt["marge"] . ",
			" . $pdt["idTVA"] . ",
			" . 1 . ")", __FILE__, __LINE__, false)) {
			echo " --> content OK";
			
			if ($handle->query("update products set idTC = " . $idTC . ", refSupplier = '', price = 'ref', price2 = '', unite = 1, marge = -1, idTVA = 0 where id = " . $pdt["id"], __FILE__, __LINE__, false)) {
				echo " --> product's update OK";
				echo "<br/>\n";
				$pdtcount++;
			}
			else {
				echo "ERROR while updating the product !";
			}
		}
		else {
			echo "ERROR while writing the reference content !";
		}
	}
	else {
		echo "ERROR while writing the references header !";
	}
}

echo "<br/>\n";
echo $pdtcount . " products updated";

?>