<?php
error_reporting(E_ALL ^ E_NOTICE);
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$mts["TOTAL TIME"]["start"] = microtime(true);

$db = DBHandle::get_instance();

$fuseau = date("I") == "1" ? "02:00" : "01:00";

$si = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
$si.= "<sitemapindex xmlns=\"http://www.google.com/schemas/sitemap/0.84\">\n";

$mts["SQL GET PRODUCTS"]["start"] = microtime(true);
$cat3PdtList = array();
$res = $db->query("
  SELECT
    pfr.id AS pdt_id,
    pfr.ref_name AS pdt_ref_name,
    pf.idFamily AS cat3_id
  FROM products_fr pfr
  INNER JOIN products_families pf ON pf.idProduct = pfr.id
  INNER JOIN products_stats ps ON pfr.id = ps.id
  INNER JOIN (SELECT id FROM advertisers WHERE actif = 1) AS a ON a.id = pfr.idAdvertiser
  WHERE pfr.active = 1 AND pfr.deleted = 0", __FILE__, __LINE__); // must be ordered for the next loop
$mts["SQL GET PRODUCTS"]["end"] = microtime(true);
$mts["PHP FETCH PRODUCTS"]["start"] = microtime(true);
while ($pdt = $db->fetchAssoc($res)) {
  $cat3PdtList[$pdt["cat3_id"]][] = $pdt;
}
$mts["PHP FETCH PRODUCTS"]["end"] = microtime(true);

$mts["SQL GET FAMILIES"]["start"] = microtime(true);
$catList = array();
$res = $db->query("
  SELECT
    (SELECT idParent FROM families WHERE id = f.idParent) AS cat1_id,
    (SELECT ffr1.ref_name FROM families_fr ffr1 WHERE ffr1.id = (SELECT idParent FROM families WHERE id = f.idParent)) AS cat1_ref_name,
    f.idParent AS cat2_id,
    (SELECT ffr2.ref_name FROM families_fr ffr2 WHERE ffr2.id = f.idParent) AS cat2_ref_name,
    f.id AS cat3_id,
    ffr3.ref_name AS cat3_ref_name
  FROM families f
  INNER JOIN families_fr ffr3 ON ffr3.id = f.id
  WHERE f.id IN (".implode(",",array_keys($cat3PdtList)).")
  ORDER BY cat1_id ASC, cat2_ref_name ASC, cat3_ref_name ASC", __fILE__, __LINE__);
$mts["SQL GET FAMILIES"]["end"] = microtime(true);
$mts["PHP FETCH FAMILIES"]["start"] = microtime(true);
while ($cat = $db->fetchAssoc($res)) {
  $catList[$cat["cat1_id"]]["ref_name"] = $cat["cat1_ref_name"];
  $catList[$cat["cat1_id"]]["children"][$cat["cat2_id"]]["ref_name"] = $cat["cat2_ref_name"];
  $catList[$cat["cat1_id"]]["children"][$cat["cat2_id"]]["children"][$cat["cat3_id"]]["ref_name"] = $cat["cat3_ref_name"];
}
$mts["PHP FETCH FAMILIES"]["end"] = microtime(true);

$mts["PHP PROCESS XML"]["start"] = microtime(true);

foreach ($catList as $cat1Id => $cat1Infos) {
  $si.= " <sitemap>\n".
        "  <loc>".URL."sitemap-".$cat1Infos["ref_name"].".xml.gz</loc>\n".
        "  <lastmod>".date("Y-m-d\TH:i:s+".$fuseau)."</lastmod>\n".
        " </sitemap>\n";
  $s = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
  $s.= "<urlset xmlns=\"http://www.google.com/schemas/sitemap/0.84\">\n";
  
  $s.= " <url><loc>".URL."familles/".$cat1Infos["ref_name"].".html</loc></url>\n";
  foreach ($cat1Infos["children"] as $cat2Id => $cat2Infos) {
    $s.= "  <url><loc>".URL."familles/".$cat2Infos["ref_name"].".html</loc></url>\n";
    
    foreach ($cat2Infos["children"] as $cat3Id => $cat3Infos) {
      $s.= "   <url><loc>".URL."familles/".$cat3Infos["ref_name"].".html</loc></url>\n";
      foreach ($cat3PdtList[$cat3Id] as $pdt) {
        $s.="    <url><loc>".URL."produits/".$pdt["cat3_id"]."-".$pdt["pdt_id"]."-".$pdt["pdt_ref_name"].".html</loc></url>\n";
      }
    }
  }
  $s.= "</urlset>\n";
  $zp = gzopen(WWW_PATH."sitemap-".$cat1Infos["ref_name"].".xml.gz","w9");
  gzwrite($zp, $s);
  gzclose($zp);
  // $fp = fopen(WWW_PATH."sitemap-".$cat1Infos["ref_name"].".xml","w");
  // fwrite($fp,$s);
  // fclose($fp);
}

$mts["PHP PROCESS XML"]["end"] = microtime(true);

$si.="</sitemapindex>";
$zp = gzopen(WWW_PATH."sitemapindex.xml.gz","w9");
gzwrite($zp, $si);
gzclose($zp);
// $fp = fopen(WWW_PATH."sitemapindex.xml","w");
// fwrite($fp,$si);
// fclose($fp);

$mts["TOTAL TIME"]["end"] = microtime(true);
//foreach ($mts as $mtn => $mt) print $mtn." = ".(($mt["end"]-$mt["start"])*1000)."ms\n";

$ch = curl_init(urlencode("http://www.google.com/webmasters/planssitemap/ping?plansitemap=".URL."sitemapindex.xml.gz"));
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_exec($ch);
curl_close($ch);

?>