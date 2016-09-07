<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';  
$db = DBHandle::get_instance();

$id_send      = $_GET['id_send'];
$source       = $_GET['source'];
$action		  = $_GET['action'];


if($action == 'client'){
	$sql_update = "UPDATE  `clients` SET  `website_origin` =  '$source' WHERE `id` =$id_send";
	mysql_query($sql_update);
}

if($action == 'order'){
	$sql_update = "UPDATE  `order` SET  `website_origin` =  '$source' WHERE `id` =$id_send";
	mysql_query($sql_update);
}

if($action == 'invoice'){
	$sql_update = "UPDATE  `invoice` SET  `website_origin` =  '$source' WHERE `id` =$id_send";
	mysql_query($sql_update);
}

if($action == 'estimate'){
	$sql_update = "UPDATE  `estimate` SET  `website_origin` =  '$source' WHERE `id` =$id_send";
	mysql_query($sql_update);
}

if($action == 'supplier_order'){
	$sql_update = "UPDATE  `order` SET  `website_origin` =  '$source' WHERE `id` =$id_send";
	mysql_query($sql_update);
}


 
?>  