<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

require_once(ICLASS."ManagerUser.php");
$db = DBHandle::get_instance();
$user = new ManagerUser($db);

if (!$user->login()) {
	print "not logged";
	exit();
}

$mts["GET PRODUCTS"]["start"] = $mts["TOTAL TIME"]["start"] = microtime(true);

$pdtList = array();
$res = $db->query("
  SELECT
    p.id AS pdt_id,
    pfr.ref_name AS pdt_ref_name,
    GROUP_CONCAT(pf.idFamily SEPARATOR ',') AS cat_ids
  FROM products p
  INNER JOIN products_fr pfr ON p.id = pfr.id AND pfr.active = 1
  INNER JOIN products_stats ps ON p.id = ps.id
  INNER JOIN products_families pf ON pf.idProduct = p.id
  INNER JOIN advertisers a ON p.idAdvertiser = a.id AND a.actif = 1
  GROUP BY p.id", __FILE__, __LINE__);
while ($pdt = $db->fetchAssoc($res)) {
  $pdt["cat_ids"] = explode(",",$pdt["cat_ids"]);
  $pdtList[$pdt["pdt_id"]] = $pdt;
}

$mts["GET PRODUCTS"]["end"] = $mts["GET 404 LIST"]["start"] = microtime(true);

$list_404 = file_get_contents("404_list.txt");
$list_404 = explode("\n",$list_404);

$mts["GET 404 LIST"]["end"] = $mts["PROCESSING"]["start"] = microtime(true);

$pdt_cat_changed = $odt_name_changed = $pdt_cat_name_changed = $pdt_deleted = $url_existed = 0;
$pdt_unknown_change = array();

foreach ($list_404 as $url) {
  //http://www.techni-contact.com/produits/100-10364266-transporteur-a-bande.html
  if (preg_match("/\.techni-contact\.com\/(produits\/(\d+)-(\d+)-([a-z_-]+)\.html)$/", $url, $matches)) {
    $partUrl = $matches[1];
    $catId = $matches[2];
    $pdtId = $matches[3];
    $pdtRN = $matches[4];
    
    if (isset($pdtList[$pdtId])) {
      if (!in_array($catId, $pdtList[$pdtId]["cat_ids"]))
        $newCatId = $pdtList[$pdtId]["cat_ids"][0];
      else
        $newCatId = $catId;
      if ($pdtRN != $pdtList[$pdtId]["pdt_ref_name"])
        $newPdtRN = $pdtList[$pdtId]["pdt_ref_name"];
      else
        $newPdtRN = $pdtRN;
      
      $newPartUrl = "produits/".$pdtList[$pdtId]["cat_ids"][0]."-".$pdtList[$pdtId]["pdt_id"]."-".$pdtList[$pdtId]["pdt_ref_name"].".html";
      
      if ($newCatId != $catId || $newPdtRN != $pdtRN) {
        if ($newCatId != $catId && $newPdtRN != $pdtRN)
          $pdt_cat_name_changed++;
        elseif ($newCatId != $catId)
          $pdt_cat_changed++;
        else
          $pdt_name_changed++;
        
        try {
          $db->query("INSERT INTO redirect_urls VALUES ('".$db->escape($partUrl)."','".$db->escape($newPartUrl)."',1309230000)", __FILE__, __LINE__);
        } catch (Exception $e) {
          $url_existed++;
        }
      }
      else {
        $pdt_unknown_change[] = $partUrl." --> ".$newPartUrl;
      }
    }
    else {
      $pdt_deleted++;
    }
  }
}

$mts["PROCESSING"]["end"] = $mts["TOTAL TIME"]["end"] = microtime(true);

foreach ($mts as $mtn => $mt) print $mtn . " = <b>" . ($mt["end"]-$mt["start"])*1000 . "ms</b><br/>\n";

echo "Changement de catégorie = ".$pdt_cat_changed."<br/>";
echo "Changement de nom = ".$pdt_name_changed."<br/>";
echo "Changement de catégorie et de nom = ".$pdt_cat_name_changed."<br/>";
echo "Supprimé = ".$pdt_deleted."<br/>";
echo "Collisions insertion old_url = ".$url_existed."<br/>";
echo "Changement non déterminé = ".count($pdt_unknown_change)."<br/>".implode("<br/>",$pdt_unknown_change);

?>
