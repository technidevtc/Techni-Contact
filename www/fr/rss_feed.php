<?php

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$db = DBHandle::get_instance();

//if (version_compare(PHP_VERSION,'5','>=')) require_once('domxml-php4-to-php5.php');
// If the family id is setted
if (!isset($_GET['family_id']))
{
	header('Location: ' . URL . '404.php');
	exit();
}

$family_id = (int)$_GET['family_id'];
// If the family does exist
if (!($result = $db->query("select fr.id, fr.ref_name, fr.name from families_fr fr where fr.id = " . $family_id, __FILE__, __LINE__, false))
	|| ($db->numrows($result, __FILE__, __LINE__) != 1))
{
	header('Location: ' . URL . '404.php');
	exit();
}

// If the family is a lvl 3 family (not a parent of any family)
if (!($result2 = $db->query("select id from families where idParent = " . $family_id, __FILE__, __LINE__, false))
	|| ($db->numrows($result2, __FILE__, __LINE__) != 0))
{
	header('Location: ' . URL . '404.php');
	exit();
}

// Family is ok
$family = $db->fetchAssoc($result);

// Getting 20 last products from this family
if (!($result = $db->query("select p.id, pfr.ref_name, pfr.name, pfr.fastdesc, pfr.descc, p.timestamp " .
	"from products p, products_fr pfr, products_families pf " .
	"where p.id = pfr.id and p.id = pf.idProduct and pf.idFamily = " . $family["id"] . " " .
	"order by p.timestamp " .
	"limit 0,20 ", __FILE__, __LINE__, false)))
{
	header('Location: ' . URL . '404.php');
	exit();
}

//include(LANG_LOCAL_INC . "includes-" . DB_LANGUAGE . "_local.php");
include(LANG_LOCAL_INC . "www-" . DB_LANGUAGE . "_local.php");
//include(LANG_LOCAL_INC . "common-" . DB_LANGUAGE . "_local.php");
//include(LANG_LOCAL_INC . "infos-" . DB_LANGUAGE . "_local.php");

// We have the products, so we start the rss feed
$rss = "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n";
$rss .="<rss version=\"2.0\">\n";
$rss .= "	<channel>\n";
$rss .= "		<title>" . WWW_RSS_NEW_TC_PRODUCTS . "</title>\n";
$rss .= "		<link>" . URL . "familles/" . $family["ref_name"] . ".html</link>\n";
$rss .= "		<description>" . WWW_RSS_NEW_FAMILY_PRODUCTS . " " . $fam['name'] . "</description>\n";
$rss .= "		<language>en-fr</language>\n";
$rss .= "		<lastBuildDate>Tue, 10 Jun 2003 04:00:00 GMT</lastBuildDate>\n";
$rss .= "		<lastBuildDate>" . date('r') . "</lastBuildDate>\n";
$rss .= "		<docs>http://blogs.law.harvard.edu/tech/rss</docs>\n";
$rss .= "		<generator>Hook-Network RSS 2.0</generator>\n";
$rss .= "		<managingEditor>e.verry@techni-contact.com</managingEditor>\n";
$rss .= "		<webMaster>frederic@hook-network.com</webMaster>\n";

while ($pdt = $db->fetchAssoc($result))
{
	$rss .= "		<item>\n";
	
	$rss .= "			<title><![CDATA[" . $pdt["name"] . "]]></title>\n";
	$rss .= "			<link>" . URL . "produits/" . $$family["id"] . "-" . $pdt["id"] . "-" . $pdt["ref_name"] . ".html</link>\n";
	$rss .= "			<description><![CDATA[<strong>" . $pdt["fastdesc"] . "</strong><br/><br/>" . $pdt["descc"] . "]]></description>\n";
	$rss .= "			<pubDate>" . date("r", (int)$pdt["timestamp"]) . "</pubDate>\n";
	$rss .= "		</item>\n";
}

$rss .= "	</channel>\n";
$rss .= "</rss>\n";

print $rss;

?>