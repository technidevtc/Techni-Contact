<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
require(ADMIN . 'generator.php');

$handle = DBHandle::get_instance();

$cols_headers = $handle->escape(serialize(array("Référence TC", "Libellé", "Référence Fournisseur", "Unité", "Taux TVA", "Prix Fournisseur", "Marge", "Prix Public")));
$cols_content = $handle->escape(serialize(array()));

echo "Loading References to update...";
$res = $handle->query("SELECT idProduct, content FROM `references_cols` WHERE content not like '%s:12:\"Référence TC\"%'", __FILE__, __LINE__, false);
echo " OK !";
echo "<br/>\n";
echo "<br/>\n";

$pdtcount = 0;
while ($pdt = $handle->fetchAssoc($res)) {
	echo "Product : " . $pdt["idProduct"];
	$content = mb_unserialize($pdt["content"]);
	array_unshift($content, "Référence TC");
	$handle->query("update references_cols set content = '" . $handle->escape(serialize($content)) . "' where idProduct = " . $pdt["idProduct"], __FILE__, __LINE__, false);
	echo " --> Update OK";
	echo "<br/>\n";
}

echo "<br/>\n";
echo $pdtcount . " products updated";

?>