<?php
/* 
 * DB Maintenance Cyclic Script
 */

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
$handle = DBHandle::get_instance();

// Advertisers
// Without any product
// Bad advertisers linking
echo strtotime("2008-08-24 22:42:38");
echo (strtotime("2008-08-24 22:42:38") - 86400 * 20);
// Old Cards
if ($res = $handle->query("show table status from technico like 'paniers'", __FILE__, __LINE__)) {
	$status = $handle->fetchAssoc($res);

	$timeThreshold = strtotime($status["Update_time"]) - 86400 * 10;
	
	$handle->query("delete from paniers where timestamp < " . $timeThreshold, __FILE__, __LINE__, false);
}


?>