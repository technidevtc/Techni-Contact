<?php 
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
	$db = DBHandle::get_instance();
	
	$idPdt   = $_GET['idPdt'];
	$sqlPdt  = "SELECT id FROM products_fr WHERE id='".$idPdt."' ";
	$reqPdt  =  mysql_query($sqlPdt);
	$rowsPdt =  mysql_num_rows($reqPdt);
	
	if($rowsPdt > 0){
		
		$sqlCheck  = "SELECT idProduct FROM products_flagship WHERE idProduct='".$idPdt."' ";
		$reqCheck  =  mysql_query($sqlCheck);
		$rowsCheck =  mysql_num_rows($reqCheck);
		
		if($rowsCheck > 0){
			echo '2';
		}else{
		
		$sql  = "SELECT idProduct , `order` as oo FROM products_flagship ORDER BY `order` DESC ";
		$req  =  mysql_query($sql);
		while($data  =  mysql_fetch_object($req)){
			$newOrder = $data->oo + 1;
			$sqlupdate  =  "UPDATE `products_flagship` SET `order` =  '$newOrder' 
							WHERE `idProduct` ='".$data->idProduct."' ";
			mysql_query($sqlupdate);
		}
		
		$sqlFamilles  = "SELECT idFamily FROM  `products_families` 
						 WHERE  `idProduct` =$idPdt";
		$reqFamilles  =  mysql_query($sqlFamilles);
		$dataFamilles =  mysql_fetch_object($reqFamilles);
		
		$sqlInsert  = "INSERT INTO `products_flagship` (`idProduct`, `idFamily`, `order`) VALUES ('$idPdt', '".$dataFamilles->idFamily."', '1')";
		mysql_query($sqlInsert);
		echo '1';
		}
	}else{
		echo '0';
	}
	