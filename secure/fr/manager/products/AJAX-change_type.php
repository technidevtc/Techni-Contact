<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
$db = DBHandle::get_instance();	
	$id_pdt = $_GET['id_pdt'];
	
	$sql_update = "UPDATE `products` SET  `price` =  'ref' 
				   WHERE  `id` ='".$id_pdt."' ";
	mysql_query($sql_update);

?>