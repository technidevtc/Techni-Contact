<?php

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$handle = DBHandle::get_instance();

function microtime_float()
{
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec%1000);
}


// Faire les idTC manquant de la table products

$start1 = microtime_float();
ob_start();
$n = 0;

////////////////////////////////////////////////////////////////////////////////

$result = & $handle->query("select id from products where idTC = 0");
while($record = & $handle->fetch($result))
	$pdt_no_idTC[] = $record[0];

$end1 = microtime_float();
print("<br />Temps partiel d'execution pdt_no_idTC : " . (($end1-$start1)*1000) . 'ms');

////////////////////////////////////////////////////////////////////////////////

$result = & $handle->query("select id from products_add where idTC = 0");
while($record = & $handle->fetch($result))
	$pdtadd_no_idTC[$record[0]] = true;

$end1 = microtime_float();
print("<br />Temps partiel d'execution pdtadd_no_idTC : " . (($end1-$start1)*1000) . 'ms');

////////////////////////////////////////////////////////////////////////////////

$result = & $handle->query("select idTC from products where idTC != 0");
while($record = & $handle->fetch($result))
	$pdt_idTC[$record[0]] = true;

$end1 = microtime_float();
print("<br />Temps partiel d'execution pdt_idTC : " . (($end1-$start1)*1000) . 'ms');

////////////////////////////////////////////////////////////////////////////////

$result = & $handle->query("select idTC from products_add where idTC != 0", __FILE__, __LINE__);
while($record = & $handle->fetch($result))
	$pdtadd_idTC[$record[0]] = true;

$end1 = microtime_float();
print("<br />Temps partiel d'execution pdtadd_idTC : " . (($end1-$start1)*1000) . 'ms');

////////////////////////////////////////////////////////////////////////////////

$result = & $handle->query("select idTC from products_add_adv where idTC != 0", __FILE__, __LINE__);
while($record = & $handle->fetch($result))
	$pdtaddadv_idTC[$record[0]] = true;

$end1 = microtime_float();
print("<br />Temps partiel d'execution pdtaddadv_idTC : " . (($end1-$start1)*1000) . 'ms');

////////////////////////////////////////////////////////////////////////////////

$result = & $handle->query("select id from references_content", __FILE__, __LINE__);
while($record = & $handle->fetch($result))
	$ref_idTC[$record[0]] = true;

$end1 = microtime_float();
print("<br />Temps partiel d'execution ref_idTC : " . (($end1-$start1)*1000) . 'ms<br />');

////////////////////////////////////////////////////////////////////////////////

foreach ($pdt_no_idTC as $id)
{
	
	do
	{
		$idTC = mt_rand(0,999999999);
		if (!isset($pdt_idTC[$idTC]) && !isset($pdtadd_idTC[$idTC]) && !isset($pdtaddadv_idTC[$idTC]) && !isset($ref_idTC[$idTC]))
		break;
	} while(true);
	
	if ($n%10 == 0) print "<br />";
	$handle->query("update products set idTC = " . $idTC ." where id = " . $id);
	$pdt_idTC[$idTC] = true;
	if (isset($pdtadd_no_idTC[$id]))
	{
		$handle->query("update products_add set idTC = " . $idTC ." where id = " . $id);
		$pdtadd_idTC[$idTC] = true;
	}
	print $id . " " . $idTC . " -- ";

	if ($n%200 == 199)
	{
		$end1 = microtime_float();
		print("<br /><br />Temps partiel d'execution : " . (($end1-$start1)*1000) . 'ms<br />');
		ob_flush();
		flush();
	}
	$n++;
}
ob_end_flush();

$end1 = microtime_float();
print("<br />Temps total d'execution : " . (($end1-$start1)*1000) . 'ms<br />');



exit();
?>