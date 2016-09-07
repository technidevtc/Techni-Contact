<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
$db = DBHandle::get_instance();

$references = array();
$res = $db->query("select * from references_content");
$db->query("DROP TABLE `references_content`");
$db->query("
CREATE TABLE IF NOT EXISTS `references_content` (
  `id` varchar(9) NOT NULL default '0',
  `idProduct` mediumint(8) unsigned NOT NULL default '0',
  `label` varchar(255) NOT NULL default '',
  `content` text NOT NULL,
  `refSupplier` varchar(50) NOT NULL default '',
  `price` varchar(255) NOT NULL default '',
  `price2` varchar(255) NOT NULL default '0',
  `unite` mediumint(9) NOT NULL default '1',
  `marge` float NOT NULL default '-1',
  `idTVA` tinyint(4) NOT NULL default '1',
  `classement` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `idProduct` (`idProduct`),
  KEY `classement` (`classement`),
  FULLTEXT KEY `refSupplier` (`refSupplier`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='r?rences produits';");
$db->query("alter table `references_content` disable keys;");

while ($result = $db->fetchAssoc($res)) {
	$db->query("
		INSERT INTO
			`references_content` (
				`id`,
				`idProduct`,
				`label`,
				`content`,
				`refSupplier`,
				`price`,
				`price2`,
				`unite`,
				`marge`,
				`idTVA`,
				`classement`)
		VALUES (
			'" . $db->escape($result["id"]) . "',
			'" . $db->escape($result["idProduct"]) . "',
			'" . $db->escape($result["label"]) . "',
			'" . $db->escape($result["content"]) . "',
			'" . $db->escape($result["refSupplier"]) . "',
			'" . $db->escape($result["price"]) . "',
			'" . $db->escape($result["price2"]) . "',
			'" . $db->escape($result["unite"]) . "',
			'" . $db->escape($result["marge"]) . "',
			'" . $db->escape($result["idTVA"]) . "',
			'" . $db->escape($result["classement"]) . "')");
}

$db->query("alter table `references_content` enable keys;");

?>