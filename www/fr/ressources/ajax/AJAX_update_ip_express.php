<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
$handle = DBHandle::get_instance();

$action = $_GET['action'];

if($action == 'addCount'){
$timenow 			 = date('Y-m-d H:i:s');
$timestamp_created   = strtotime($timenow);
$ipClient  	 = $_GET['ipClient'];
$timenow     = date('Y-m-d H:i:s');
$idProducts  = $_GET['idProducts'];

	$sqlIp  = "SELECT count as Ctt , id ,idProducts
			 	FROM ip_onglet_express
			   WHERE ipUser='".$ipClient."' ";
	$reqIp  =  mysql_query($sqlIp);
	$rowsIp =  mysql_num_rows($reqIp);
	
	if($rowsIp == 0){
		$sqlInsert  =  "INSERT INTO `ip_onglet_express` (`id`, `ipUser`, `idProducts`, `count`, `timsetamp`) 
						VALUES (NULL, '$ipClient', '$idProducts', '1', '$timestamp_created')";
		mysql_query($sqlInsert);
		$totalCC = 1;
	}else{
		$dataIp =  mysql_fetch_object($reqIp);
		
		$sql_check  = "SELECT id
					   FROM ip_onglet_express
					   WHERE  NOW()> DATE_ADD(From_unixtime(timsetamp) , INTERVAL 2 HOUR )
					   AND ipUser= '".$ipClient."' ";
		$req_check  =  mysql_query($sql_check);
		$rows_check =  mysql_num_rows($req_check);
		
		if($rows_check == 0){
			if(($dataIp->idProducts != $idProducts) && ($dataIp->Ctt < 2) ){
				$ttCount = $dataIp->Ctt + 1;
				$sqlUpdate = "UPDATE  `ip_onglet_express` SET  
											`count` 	 =  '$ttCount' ,  
											`idProducts` =  '$idProducts' ,  
											`timsetamp` =   '$timestamp_created' 
								WHERE  `id` ='".$dataIp->id."'";
				// echo $sqlUpdate;
				mysql_query($sqlUpdate);
				$totalCC = $ttCount;
			}
		}else{
			$sqlUpdate = "UPDATE  `ip_onglet_express` SET  
										`count` 	 =  '1' ,  
										`idProducts` =  '$idProducts' ,  
										`timsetamp` =   '$timestamp_created' 
							WHERE  `id` ='".$dataIp->id."'";
			mysql_query($sqlUpdate);
			$totalCC = 1;
		}		
	}
	echo $totalCC;
}

if($action == 'selectCount'){
	$ipClient  	 = $_GET['ipClient'];
	
	$sqlIp  = "SELECT count as Ctt 
			 	FROM ip_onglet_express
			   WHERE ipUser='".$ipClient."' ";
	$reqIp  =  mysql_query($sqlIp);
	$dataIp =  mysql_fetch_object($reqIp);
	echo $dataIp->Ctt;
}




?>