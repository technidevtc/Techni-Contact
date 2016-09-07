<?php 
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
	$db = DBHandle::get_instance();
	
	$orderchange1  =  $_GET['orderchange1'];
	$orderchange2  =  $_GET['orderchange2'];
	
	/*
	$sqlPTD1  = "SELECT idProduct, idFamily FROM products_flagship WHERE `order` = '".$orderchange1."'  ";
	$reqPTD1  =  mysql_query($sqlPTD1);
	$dataPTD1 =  mysql_fetch_object($reqPTD1);
	*/
	$idPdt1  = $_GET['idPdtCurrent'];
	// $idFam1   = $dataPTD1->idFamily;
	
	
	/*$sqlPTD2  = "SELECT idProduct, idFamily FROM products_flagship WHERE `order` = '".$orderchange2."'  ";
	$reqPTD2  =  mysql_query($sqlPTD2);
	$dataPTD2 =  mysql_fetch_object($reqPTD2);
	*/
	$idPdt2  = $_GET['idProductNext'];
	
	
	$sqlUpdate1  = "UPDATE `products_flagship` SET  `order` =  '$orderchange2' WHERE  `idProduct` =$idPdt1";
	mysql_query($sqlUpdate1);
	
	// echo $sqlUpdate1."\n";
	
	$sqlUpdate2  = "UPDATE `products_flagship` SET  `order` =  '$orderchange1' WHERE  `idProduct` =$idPdt2";
	mysql_query($sqlUpdate2);
	
	// echo $sqlUpdate2."\n";

?>