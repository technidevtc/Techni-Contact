<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
$handle = DBHandle::get_instance();

$action = $_GET['action'];

if($action == 'addCount'){
$ipClient = $_GET['ipClient'];
$timenow 			 = date('Y-m-d H:i:s');
$timestamp_created   = strtotime($timenow);


	$sqlIp  = "SELECT count as Ctt , id
			 	FROM ip_calque_search
			   WHERE ipUser='".$ipClient."' ";
	$reqIp  =  mysql_query($sqlIp);
	$rowsIp =  mysql_num_rows($reqIp);
	
	if($rowsIp == 0){
		$sqlInsert  =  "INSERT INTO `ip_calque_search` (`id`, `ipUser`, `count`,`timsetamp`) 
					    VALUES (NULL, '$ipClient', '1','$timestamp_created')";
		mysql_query($sqlInsert);
		$totalCC = 1;
	}else{
		$dataIp =  mysql_fetch_object($reqIp);
		$sql_check  = "SELECT id
					   FROM ip_calque_search
					   WHERE  NOW()> DATE_ADD(From_unixtime(timsetamp) , INTERVAL 6 HOUR )
					   AND ipUser= '".$ipClient."' ";
		$req_check  =  mysql_query($sql_check);
		$rows_check =  mysql_num_rows($req_check);
		
		// Si la session de l'utilisateur tjrs valide et le conteur inferieur a 3 alors count++
		if( ($rows_check == 0) && ($dataIp->Ctt <3 ) ){
			$ttCount = $dataIp->Ctt + 1;
			$sqlUpdate = "UPDATE  `ip_calque_search` SET  `count` =  '$ttCount' WHERE  `id` ='".$dataIp->id."'";
			mysql_query($sqlUpdate);
			$totalCC = $ttCount;
		}
		
		// si la session de  l'utilisateur dépasse 1H mais le conteur inferieur a 3 dans ce cas une nouvelle session et le conteur =1
		if( ($rows_check != 0) && ($dataIp->Ctt <3 ) ){
			$sqlUpdate = "UPDATE  `ip_calque_search` SET  `count` =  '1',`timsetamp`='$timestamp_created'
						  WHERE  `id` ='".$dataIp->id."'";
			mysql_query($sqlUpdate);
			$totalCC = 1;
		}
		// Si la session de l'utilisateur tjrs valide et le conteur superieur a 3 
		if( ($rows_check == 0) && ($dataIp->Ctt >= 3 ) ){
			$totalCC = 3;
		}
		
		// si la session de  l'utilisateur dépasse 1H mais le conteur inferieur a 3 dans ce cas une nouvelle session et le conteur =1
		if( ($rows_check != 0) && ($dataIp->Ctt >=3 ) ){
			$sqlUpdate = "UPDATE  `ip_calque_search` SET  `count` =  '1',timsetamp='$timestamp_created'
						  WHERE  `id` ='".$dataIp->id."'";
			mysql_query($sqlUpdate);
			$totalCC = 1;
		}
	}

	echo $totalCC;
}

if($action == 'closeCount'){
$ipClient = $_GET['ipClient'];
	
	$sqlIp  = "SELECT count as Ctt , id
			 	FROM ip_calque_search
			   WHERE ipUser='".$ipClient."' ";
	$reqIp  =  mysql_query($sqlIp);
	$rowsIp =  mysql_num_rows($reqIp);
	if($rowsIp > 0){
		$dataIp =  mysql_fetch_object($reqIp);
		$sqlUpdate = "UPDATE  `ip_calque_search` SET  `count` =  '3' WHERE  `id` ='".$dataIp->id."'";
		mysql_query($sqlUpdate);
		$totalCC = 3;
	}else{
		$sqlInsert  =  "INSERT INTO `ip_calque_search` (`id`, `ipUser`, `count`) VALUES (NULL, '$ipClient', '3')";
		mysql_query($sqlInsert);
		$totalCC = 3;
	}
	echo $totalCC;	
}

if($action == 'closingBrowse'){
$ipClient = $_GET['ipClient'];
	$sqlDelete = "DELETE FROM ip_calque_search WHERE ipUser='".$ipClient."' ";
	mysql_query($sqlDelete);
	
}



?>