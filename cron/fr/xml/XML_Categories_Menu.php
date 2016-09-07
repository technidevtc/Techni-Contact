<?php

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

error_reporting(E_ALL & ~E_NOTICE);

$db = DBHandle::get_instance();

//$db = $conn->getDbh();

$selectionTypeList = array( // all preselection types by category level
  1 => array(
  ),
  2 => array(
    'pdt_pic',
  ),
  3 => array(
  )
);

// main graph var
$categories = array();

// Root Family, necessary to get the main families
$categories[0] = array();

// ID = 0, Name = 1, Ref Name = 2, Parent ID = 3
$sth = $db->query("
  SELECT
    f.id,
    f.idParent,
    f.rank,
    fr.name,
    fr.ref_name,
    fr.text_content,
    COUNT(pf.idProduct) AS pdt_count
  FROM families f
  INNER JOIN families_fr fr ON f.id = fr.id
  LEFT JOIN products_families pf ON f.id = pf.idFamily
  LEFT JOIN products_fr pfr ON pfr.id = pf.idProduct
  LEFT JOIN advertisers a ON a.id = pfr.idAdvertiser
  LEFT JOIN products_stats ps ON pf.idProduct = ps.id
  WHERE pfr.id IS NULL OR (pfr.active = 1 AND pfr.deleted = 0 AND a.actif = 1)
  GROUP BY f.id
  ORDER BY f.idParent, f.rank, fr.name");

//$sth->execute();
//while ($cat = $sth->fetch(PDO::FETCH_ASSOC)) {
while($cat = $db->fetchAssoc($sth)){
  $cat['name'] = htmlspecialchars($cat['name']); // for & < > ' " in the name attribute
  
  if (isset($categories[$cat['id']])) // category already has children
    $categories[$cat['id']] = array_merge($cat, $categories[$cat['id']]);
  else
    $categories[$cat['id']] = $cat;
  $categories[$cat['idParent']]['children'][] = $cat['id'];
}

$xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
$JSONcatList = array();

if (isset($categories[0]["children"])) {
  $cat0_inner_xml = "";
  $cat0_pdt_count = 0;
  
  foreach ($categories[0]["children"] as $cat1ID) {
    
    if (isset($categories[$cat1ID]["children"])) {
      $cat1_inner_xml = "";
      $cat1_pdt_count = 0;
      $JSONcat1 = array(
        'id' => $categories[$cat1ID]['id'],
        'name' => $categories[$cat1ID]['name'],
        'ref_name' => $categories[$cat1ID]['ref_name'],
        'text_content' => $categories[$cat1ID]['text_content'],
        'children' => array()
      );
      
      foreach ($categories[$cat1ID]["children"] as $cat2ID) {
        
        if (isset($categories[$cat2ID]["children"])) {
          $cat2_inner_xml = "";
          $cat2_pdt_count = 0;
          $cat2_cat_count = 0;
          $JSONcat2 = array(
            'id' => $categories[$cat2ID]['id'],
            'name' => $categories[$cat2ID]['name'],
            'ref_name' => $categories[$cat2ID]['ref_name'],
            'children' => array()
          );
          
          $vci = 0; // valid cat index
          foreach ($categories[$cat2ID]["children"] as $cat3ID) {
            $cat3_pdt_count = (int)$categories[$cat3ID]["pdt_count"];
            if ($cat3_pdt_count > 0) {
              $cat2_pdt_count += $cat3_pdt_count;
              $cat2_cat_count++;
              if ($vci < CAT2_CAT3_MENU_COUNT) {
                $JSONcat3 = array(
                  'id' => $categories[$cat3ID]['id'],
                  'name' => $categories[$cat3ID]['name'],
                  'ref_name' => $categories[$cat3ID]['ref_name']
                );
                $JSONcat2['children'][] = $JSONcat3;
              }
              $vci++;
            } else {
              unset($categories[$cat3ID]);
            }
          }
          
          if ($cat2_pdt_count > 0) {
            $cat1_inner_xml .= 
              "  <category id=\"" . $cat2ID . "\" name=\"" . $categories[$cat2ID]["name"] . "\" ref_name=\"" . $categories[$cat2ID]["ref_name"] . "\" pdt_count=\"" . $cat2_pdt_count . "\">\n".
              (!empty($categories[$cat2ID]['pdt_pic']) ?
              "   <pdt_pic>".$categories[$cat2ID]['pdt_pic']."</pdt_pic>\n" : "").
              $cat2_inner_xml.
              "  </category>\n";
            $cat1_pdt_count += $cat2_pdt_count;
            $JSONcat1['children'][] = $JSONcat2;
          } else {
            unset($categories[$cat2ID]);
          }
        }
      }
      
      if ($cat1_pdt_count > 0) {
        $cat0_inner_xml .=
          " <category id=\"" . $cat1ID . "\" name=\"" . $categories[$cat1ID]["name"] . "\" ref_name=\"" . $categories[$cat1ID]["ref_name"] . "\" pdt_count=\"" . $cat1_pdt_count . "\">\n".
          (!empty($categories[$cat1ID]['text_content']) ?
          "  <menu_html><![CDATA[".$categories[$cat1ID]['text_content']."]]></menu_html>\n" : "").
          $cat1_inner_xml.
          " </category>\n";
        $cat0_pdt_count += $cat1_pdt_count;
        $JSONcatList[$JSONcat1['id']] = $JSONcat1;
      } else {
        unset($categories[$cat1ID]);
      }
    }
  }
  
  if ($cat0_pdt_count > 0) {
    $xml .= 
      "<categories pdt_count=\"" . $cat0_pdt_count . "\">\n".
      $cat0_inner_xml.
      "</categories>\n";
  }
}


$fh = fopen(XML_CATEGORIES_MENU, "w+");
fwrite($fh, $xml);
fclose($fh);

$fh = fopen(JSON_CATEGORIES_MENU, "w+");
fwrite($fh, "HN.TC.categories = ".json_encode($JSONcatList));
fclose($fh);
