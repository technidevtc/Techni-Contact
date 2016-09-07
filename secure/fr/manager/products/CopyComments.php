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

$query = "select id, idProduct, timestamp, precisions from contacts where timestamp >= " . mktime(0,0,0,11,01,2006);
print "Execution de la requête : " . $query . "<br/>\n";
$result = & $handle->query($query, __FILE__, __LINE__);

$nbc = $handle->numrows($result, __FILE__, __LINE__);
print "Nombre de commentaires total détectés : " . $nbc . "<br/>\n";

print "Début de la copie des commentaires...<br/>\n";
$nbcomment = 0;

print "Nombre de commentaires insérés :<br/>\n";
$start1 = microtime_float();

while($comment = & $handle->fetchAssoc($result))
{
	do
	{
		$id = mt_rand(1, 999999999);
		$result2 = & $handle->query("select id from products_comments where id = " . $id, __FILE__, __LINE__);
	}
	while ($handle->numrows($result2, __FILE__, __LINE__) >= 1);
	
	$query = "insert into products_comments (";		$query2 = "values (";
	$query .= "id, ";								$query2 .= $id . ", ";
	$query .= "productID, ";						$query2 .= $comment['idProduct'] . ", ";
	$query .= "contactID, ";						$query2 .= $comment['id'] . ", ";
	$query .= "timestamp, ";						$query2 .= $comment['timestamp'] . ", ";
	$query .= "text, ";								$query2 .= "'" . $handle->escape($comment['precisions']) . "', ";
	$query .= "`show`) ";							$query2 .= (empty($comment['precisions']) ? 0 : 1) . ") ";
	$query .= $query2;
	
	if (($handle->query($query, __FILE__, __LINE__, false)) && ($handle->affected(__FILE__, __LINE__) == 1))
	{
		$nbcomment++;
		if ($nbcomment%100 == 0)
		{
			$end1 = microtime_float();
			print "-> " . $nbcomment . "\t(" . (($end1-$start1)*1000) . "ms)<br />\n";
		}
	}
}

print "Nombre de commentaires copiés : " . $nbcomment . "<br/>\n";
if ($nbc != $nbcomment) print "<br/>\nLe nombre de commentaire insérés ne correspond pas au nombre de commentaire total !<br/>\n";

$end3 = microtime_float();
print "<br/>\ntemps total d'execution : " . (($end3-$start1)*1000) . "ms<br/>\n";


?>