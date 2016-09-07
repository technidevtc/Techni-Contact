<?php

//GetStats.php?manager=1ea2f92b496ef5ba256c6d3d7bba1a8c&Type=S&ID=42367&Source=0&Year=0&Month=0&Day=0
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

require(ADMIN  . 'generator.php');
require(ADMIN  . 'logs.php');

function microtime_float()
{
  list($usec, $sec) = explode(" ", microtime());
  return ((float)$usec + (float)$sec%1000);
}

$handle = DBHandle::get_instance();

$listpdt = array();
$result = $handle->query("select id, idTC, idAdvertiser from products", __FILE__, __LINE__);
while($rec = $handle->fetch($result))
	$listpdt[] = $rec;

$nbpdt = count($listpdt);

$debut = mktime(0,0,0,02,1,2007);
$fin = mktime(0,0,0,02,22,2007);
//$fin = time();

print "Génération pseudo stats...<br />";
$start1 = microtime_float();

print "Désactivation des clés de la table...<br />";
//$handle->query("alter table stats_cmd disable keys", __FILE__, __LINE__);
$end1 = microtime_float();
print("temps partiel d'execution : " . (($end1-$start1)*1000) . 'ms<br />');

$idCmd = mt_rand(1, 999999999);
$f = fopen("/data/technico/statstechnico-access_log", 'w');
for($i = 0; $i < 200000; $i++)
{
	$pdt2add = & $listpdt[mt_rand(0,$nbpdt-1)];
	$timestamp = mt_rand($debut, $fin);
	$line = 'eys33-2-82-230-50-151.fbx.proxad.net - - [19/Feb/2007:00:19:41 +0100] "' .
	"GET /?" . $pdt2add[0] . "-" . $timestamp . "-" . $pdt2add[2] . "-" . 3 . "-" . $pdt2add[1] . 
	' HTTP/1.1" 200 0 "http://www.techni-contact.com/produits/1240-9810971-gyrophare-voiture.html" "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 2.0.50727; .NET CLR 3.0.04506.30)"' . "\n";
	fwrite($f, $line);
	//if (mt_rand(0,9) < 5) $idCmd = mt_rand(1, 999999999);
	//$price2 = mt_rand(10,10000);
	//$price = $price2 * mt_rand(101,200) / 100;
	//$handle->query("insert into stats_cmd (idProduct, idTC, idAdvertiser, idCommand, price, price2, timestamp) values (" . $pdt2add[0] . ", " . $pdt2add[1] . ", " . $pdt2add[2] . ", " . $idCmd . ", " . $price . ", " . $price2 . ", " . $timestamp . ")", __FILE__, __LINE__);
}
fclose($f);
print "pseudo stats générées !<br />";
$end2 = microtime_float();
print("temps partiel d'execution : " . (($end2-$start1)*1000) . 'ms<br />');

print "Réactivation des clés de la table...<br />";
//$handle->query("alter table stats_cmd enable keys", __FILE__, __LINE__);
$end3 = microtime_float();
print("temps total d'execution : " . (($end3-$start1)*1000) . 'ms<br />');




?>