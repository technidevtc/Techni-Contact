<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

require_once(ICLASS."ManagerUser.php");
$db = DBHandle::get_instance();
$user = new ManagerUser($db);

if (!$user->login()) {
	print "not logged";
	exit();
}

$starting_date = mktime(0,0,0,1,1,2009);

// orders
$res = $db->query("SELECT id, idClient, produits FROM commandes WHERE timestamp > ".$starting_date,__FILE__,__LINE__);
while ($cmd = $db->fetchAssoc($res)) {
  $produits = mb_unserialize($cmd["produits"]);
  $olc = count($produits); // order line count
  $occ = count($produits[0]); // order col count
  $cmd_lines = array();
  for ($oli=1; $oli<$olc; $oli++){
    $cmd_line = array();
    for ($oci=0; $oci<$occ; $oci++)
      $cmd_line[$produits[0][$oci]] = $produits[$oli][$oci];
    $cmd_lines[] = $cmd_line;
  }
  
  foreach ($cmd_lines as $cmd_line) {
    $db->query("INSERT INTO `orders_lines_tmp` (`UserID`,`ProductID`,`OrderID`) VALUES ('".$cmd["idClient"]."','".$cmd_line["idTC"]."','O".$cmd["id"]."')",__FILE__,__LINE__);
  }
}

// leads
$res = $db->query("
  SELECT cus.id as user_id, rc.id AS product_id, lead.id as lead_id
  FROM contacts lead
  INNER JOIN products_fr pfr ON lead.idProduct = pfr.id AND pfr.active = 1
  INNER JOIN advertisers a ON pfr.idAdvertiser = a.id AND a.actif = 1
  INNER JOIN clients cus ON lead.email = cus.login
  LEFT JOIN references_content rc ON lead.idProduct = rc.idProduct AND rc.classement = 1
  WHERE rc.id IS NOT NULL AND lead.timestamp > ".$starting_date."
  UNION
  SELECT cus.id as user_id, p.idTC AS product_id, lead.id as lead_id
  FROM contacts lead
  INNER JOIN products p ON lead.idProduct = p.id
  INNER JOIN products_fr pfr ON lead.idProduct = pfr.id AND pfr.active = 1
  INNER JOIN advertisers a ON pfr.idAdvertiser = a.id AND a.actif = 1
  INNER JOIN clients cus ON lead.email = cus.login
  LEFT JOIN references_content rc ON lead.idProduct = rc.idProduct AND rc.classement = 1
  WHERE rc.id IS NULL AND lead.timestamp > ".$starting_date."
  ",__FILE__,__LINE__);
echo $db->get_last_query()."<br/>".$db->numrows($res);
while ($lead = $db->fetchAssoc($res)) {
  $db->query("INSERT INTO `orders_lines_tmp` (`UserID`,`ProductID`,`OrderID`) VALUES ('".$lead["user_id"]."','".$lead["product_id"]."','L".$lead["lead_id"]."')",__FILE__,__LINE__);
}

?>
