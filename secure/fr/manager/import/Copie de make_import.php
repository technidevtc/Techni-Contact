<?php

/*================================================================/

 Techni-Contact V4 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 13 février 2006

 Fichier : /secure/manager/families/FamiliesSearch.php
 Description : Fichier interface de recherche des familles AJAX

/=================================================================*/

require('../config.php');

require(ICLASS . 'ManagerUser.php');

$db = $handle = DBHandle::get_instance();
$user   = & new ManagerUser($handle);

header("Content-Type: text/html; charset=iso-8859-1");

if (version_compare(PHP_VERSION,'5','>='))
	require_once('domxml-php4-to-php5.php');

if(!$user->login())
{
	print "Votre session a expirée, veuillez réactualiser la page pour retourner à la page de login" . __MAIN_SEPARATOR__;
	exit();
}

$hohoho = <<< EOF

	<product place="1" delete="true">
		<name>Produit à effacer</name>
	</product>
	<product place="2">
		<category>famille de test</category>
		<name>Produit de test 1</name>
		<fastdesc>Description rapide du produit de test 1</fastdesc>
<!--	<alias>liste des alias du produit</alias>
		<keywords>mot clés servant au moteur de recherche interne</keywords>-->
		<description>
			Description complète du produit de test 1
			ligne 2
			ligne 3
			...
			ligne n
		</description>
		<technical_data>
			Données techniques du produit de test 1
			ligne 2
			ligne 3
			...
			ligne n
		</technical_data>
<!--	<delivery_fee>250</delivery_fee>-->
		<delivery_time>de 24 à 48h sous conditions</delivery_time>
		<url_image>www.sitedetest.com/image_produit_1056546.jpg</url_image>
		
		<reference>
			<supplier_reference>RACD46816</supplier_reference>
			<label>reference 1 du produit de test 1</label>
<!--		<brand>hook-nertwork inc.</brand>-->
			<mixed_data_1 unit="Kg" type="string">...</mixed_data_1>
			<mixed_data_2 unit="cm" type="integer">...</mixed_data_2>
			<mixed_data_3>...</mixed_data_3>
			...
			<mixed_data_n>...</mixed_data_n>
			<price currency="EUR">537</price>
			<deleted_price>770</deleted_price>
			<unit>2</unit>
			<VAT>20</VAT>
		</reference>
	</product>

EOF;
/*
$f = fopen("big.xml", "w");
fwrite($f, '<?xml version="1.0" encoding="ISO-8859-1"?>' . "\n" .
'<catalogue lang="FR" date="2007-04-02 16:00" GMT="+1" type="complete">');
for ($i = 0; $i < 10000; $i++) fwrite($f, $hohoho);
fwrite($f, "</catalogue>\n");
fclose($f);
*/

function getFirstChild($name)
{
}

function getNextTwin($node)
{
	$nodeName = $node->node_name();
	$next = null;
	while ($next = $node->next_sibling()) if ($next->node_name() == $nodeName) return $next;
	return null;
}

if (!$dom = domxml_open_file("big.xml"))
{
	echo "Erreur lors de l'analyse du document\n";
	exit;
}

$root = $dom->document_element();

print "lang=" . $root->get_attribute('lang') . "<br />\n";
print "date=" . $root->get_attribute('date') . "<br />\n";
print "GMT="  . $root->get_attribute('GMT')  . "<br />\n";
print "type=" . $root->get_attribute('type') . "<br />\n";

$product = $root->first_child();

$n = 0;
//$products = $root->get_elements_by_tagname('product');

//print $products[1513]->node_value();

while(true)
{
	if ($product->node_name() == "product")
	{
		$n++;
		//print "name=" . $product->node_value() . "<br />\n";
		//print "place=" . $product->get_attribute('place') . "<br />\n";
	}
	if (!($product = $product->next_sibling())) break;
	if ($n%1000 == 0) print $n . "<br />\n";
	
	
}

print "end";

?>
