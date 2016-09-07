<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

require_once(ICLASS."ManagerUser.php");

$handle = DBHandle::get_instance();
$user = new ManagerUser($handle);

if (!$user->login()) {
	print "not logged";
	exit();
}

$db = DBHandle::get_instance();

echo "Disabling contacts table keys...<br/>\n<br/>\n";
$db->query("ALTER TABLE `contacts` DISABLE KEYS", __FILE__, __LINE__);

// primary leads
$res = $db->query("
	SELECT
		c.id AS lead_id, pf.idFamily AS pdt_cat_id
		FROM contacts c
		INNER JOIN products_families pf ON c.idProduct = pf.idProduct
		WHERE
			c.idFamily = 0 AND
			c.parent = 0
		GROUP BY c.id", __FILE__, __LINE__);

echo "Found ".$db->numrows($res)." primary contacts<br/>\n";
$cn = 0;
while($c = $db->fetchAssoc($res)) {
	$db->query("UPDATE contacts SET idFamily = ".$c["pdt_cat_id"]." WHERE id = ".$c["lead_id"], __FILE__, __LINE__);
	if (!($cn%10)) echo $cn." contacts updated<br/>\n";
	++$cn;
}

echo "<br/>\n<br/>";

$res = $db->query("
	SELECT
		c.id AS lead_id, c.parent
		FROM contacts c
		WHERE
			c.idFamily = 0 AND
			c.parent != 0", __FILE__, __LINE__);

echo "Found ".$db->numrows($res)." secondary contacts<br/>\n";
$cn = 0;
while($c = $db->fetchAssoc($res)) {
	$res2 = $db->query("SELECT c.id, c.idFamily AS cat_id, c.parent, c.idProduct FROM contacts c WHERE c.id = ".$c["parent"], __FILE__, __LINE__);
	if ($db->numrows($res2) < 1) {
		echo "-> secondary contact ".$c["lead_id"]." has a non existent parent: ".$c["parent"]."<br/>\n";
	}
	else {
		$cp = $db->fetchAssoc($res2);
		if (empty($cp["cat_id"]))
			echo "-> primary contact ".$cp["id"]." has an incorrect family ID: ".$cp["cat_id"]." [parent=".$cp["parent"]." / idProduct=".$cp["idProduct"]."]<br/>\n";
		else {
			$db->query("UPDATE contacts SET idFamily = ".$cp["cat_id"]." WHERE id = ".$c["lead_id"], __FILE__, __LINE__);
			if (!($cn%10)) echo $cn." contacts updated<br/>\n";
			++$cn;
		}
	}
}

echo "<br/>\n<br/>\nEnabling contacts table keys..<br/>\n<br/>\n";
$db->query("ALTER TABLE `contacts` ENABLE KEYS", __FILE__, __LINE__);


?>