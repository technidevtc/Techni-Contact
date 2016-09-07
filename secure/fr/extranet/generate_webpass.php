<?php

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$handle = DBHandle::get_instance();

$all = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
$len = strlen($all);

$result = & $handle->query("select id from extranetusers", __FILE__, __LINE__);
while (list($id) = $handle->fetch($result))
{
	
	$webpass = '';
	for($i = 0; $i < 32; $i++) $webpass .= $all[mt_rand(0,$len-1)];
	print $id . " - " . $webpass . "<br />\n";
	//$handle->query("update extranetusers set webpass = '" . $webpass . "' where id = " . $id, __FILE__, __LINE__);
}
?>