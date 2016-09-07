<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$db = DBHandle::get_instance();
$user = new BOUser();

if (!$user->login()) {
	print "not logged";
	exit();
}

$mts["SQL GET"]["start"] = $mts["TOTAL TIME"]["start"] = microtime(true);

$res = $db->query("
  SELECT
    p.id AS pdt_id,
    (SELECT idFamily FROM products_families WHERE idProduct = p.id LIMIT 0,1) AS cat_id,
    rcols.content AS attr_names,
    rcontent.content AS attr_values
  FROM products p
  INNER JOIN advertisers a ON a.id = p.idAdvertiser AND a.category = ".__ADV_CAT_SUPPLIER__."
  INNER JOIN references_cols rcols ON rcols.idProduct = p.id
  INNER JOIN references_content rcontent ON rcontent.idProduct = p.id
  HAVING cat_id IS NOT NULL
  ORDER BY cat_id ASC, pdt_id ASC", __FILE__, __LINE__);

$mts["SQL GET"]["end"] = $mts["PHP"]["start"] = microtime(true);

$last_cat_id = $last_pdt_id = null;
$cat3AttrsValuesPdt = $cat3AttrPdt = $incorrect = array();
$lc = 0;
while ($ref = $db->fetchAssoc($res)) {
  $attr_names = array_slice(mb_unserialize($ref["attr_names"]),3,-5);
  $attr_values = mb_unserialize($ref["attr_values"]);
  
  if (count($attr_names) != count($attr_values)) {
    $incorrect[$ref["pdt_id"]] = true;
    continue;
  }
  for ($k=0, $l=count($attr_names); $k<$l; $k++) {
    $an = trim($attr_names[$k]);
    $av = trim($attr_values[$k]);
    if ($an != "" && $av != "") {
      $cat3AttrsValuesPdt[$ref["cat_id"]][$an][$av][$ref["pdt_id"]] = true; // to get every values for every attributes of a cat 3 and get the usedCount of each value
      $cat3AttrPdt[$ref["cat_id"]][$an][$ref["pdt_id"]] = true; // to get the usedCount of an attribute of a cat 3
    }
  }
  
  $lc++;
  //if ($lc == 100000) break;
}
$mts["PHP"]["end"] = $mts["SQL TRUNCATE"]["start"] = microtime(true);

$db->query("TRUNCATE TABLE ref_attributes", __FILE__, __LINE__);
$db->query("TRUNCATE TABLE ref_attributes_values", __FILE__, __LINE__);
$db->query("TRUNCATE TABLE products_ref_attributes_values", __FILE__, __LINE__);
//$db->query("TRUNCATE TABLE categories_selection_ref_attributes", __FILE__, __LINE__);

$mts["SQL TRUNCATE"]["end"] = $mts["SQL INSERT"]["start"] = microtime(true);

$stats = array();
foreach ($cat3AttrsValuesPdt as $cat3Id => $attrNames) {
  foreach ($attrNames as $attrName => $attrValues) {
    
    $collision = false;
    $attrUsedCount = count($cat3AttrPdt[$cat3Id][$attrName]);
    do {
      $attrId = rand(0,0xffffffff);
      try {
        $db->query("INSERT INTO ref_attributes (id, categoryId, name, usedCount) VALUES (".$attrId.",".$cat3Id.",'".$db->escape($attrName)."',".$attrUsedCount.")", __FILE__, __LINE__);
        $collision = false;
      } catch (Exception $e) {
        $collision = true;
        $stats["collision_attr_count"]++;
      }
    } while ($collision);
    
    foreach ($attrValues as $attrValue => $pdtIds) {
      $valUsedCount = count($pdtIds);
      $collision = false;
      do {
        $attrValId = rand(0,0xffffffff);
        try {
          $db->query("INSERT INTO ref_attributes_values (id, attributeId, value, usedCount) VALUES (".$attrValId.",".$attrId.",'".$db->escape($attrValue)."',".$valUsedCount.")", __FILE__, __LINE__);
          $collision = false;
        } catch (Exception $e) {
          $collision = true;
          $stats["collision_val_count"]++;
        }
      } while ($collision);
      
      foreach ($pdtIds as $pdtId => $v) {
        $db->query("INSERT INTO products_ref_attributes_values (productId, attributeValueId) VALUES (".$pdtId.",".$attrValId.")", __FILE__, __LINE__);
        $stats["val_pdt_relations"]++;
      }
      $stats["val_created"]++;
    }
    $stats["attr_created"]++;
  }
  $stats["cat3_created"]++;
}

$mts["SQL INSERT"]["end"] = $mts["TOTAL TIME"]["end"] = microtime(true);

foreach ($mts as $mtn => $mt) print $mtn . " = <b>" . ($mt["end"]-$mt["start"])*1000 . "ms</b><br/>\n";

echo "loops = ".$lc."<br/>";
echo "famille initialisée = ".$stats["cat3_created"]."<br/>";
echo "attributs créés = ".$stats["attr_created"]."<br/>";
echo "valeurs d'attribut créées = ".$stats["val_created"]."<br/>";
echo "relation produits <-> valeur d'attributs insérées = ".$stats["val_pdt_relations"]."<br/>";
echo "collision insertion attributs = ".$stats["collision_attr_count"]."<br/>";
echo "collision insertion valeurs d'attributs = ".$stats["collision_val_count"]."<br/>";
echo "incorrect products : <br/>";
foreach ($incorrect as $id => $v)
  echo $id."<br/>";
?>
