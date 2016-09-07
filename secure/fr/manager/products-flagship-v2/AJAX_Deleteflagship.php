<?php 
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
	$db = DBHandle::get_instance();
	
	$idPdt  = $_GET['idPdt'];
	
	
	$sqlOo  = "SELECT  `order` as oo FROM products_flagship WHERE idProduct='".$idPdt."' ";
	$reqOo  =  mysql_query($sqlOo);
	$dataOo =  mysql_fetch_object($reqOo);
	
	$sqlDelete  =  "DELETE FROM products_flagship WHERE idProduct='".$idPdt."' ";
	mysql_query($sqlDelete);
	
	$sqlMaxOo  = "SELECT idProduct , `order` as oo FROM products_flagship WHERE `order` > '".$dataOo->oo."' ";
	$reqMaxOo  =  mysql_query($sqlMaxOo);
	while($dataMaxOo =  mysql_fetch_object($reqMaxOo)){
		$orderNew = $dataMaxOo->oo - 1;
		$sqlupdate  =  "UPDATE `products_flagship` SET `order` =  '$orderNew' WHERE `idProduct` ='".$dataMaxOo->idProduct."' ";
		// echo $sqlupdate.'<br />';
		mysql_query($sqlupdate);
	}
	
	
	
	
?>