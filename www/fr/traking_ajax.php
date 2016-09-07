<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
$db = DBHandle::get_instance();

//$action  = $_GET['action'];
//if($action == '')
$timenow 			 = date('Y-m-d H:i:s');
$timestamp_created   = strtotime($timenow);
$ip_client			 = $_SERVER['REMOTE_ADDR'];
$id_commercial 		 = $_GET['id_commercial'];
$id_proucts 		 = $_GET['id_proucts'];

	$date_now	  	   = date('Y-m-d');
	$yesterday_start   = strtotime($date_now.' 00:00:00');
	$yesterday_end     = strtotime($date_now.' 23:59:59');

	
	$sql_verify  = "SELECT id 
			        FROM stats_retention_pop_up 
				    WHERE ip='".$ip_client."' ";
	$req_verify  =  mysql_query($sql_verify);
	$rows_verify =  mysql_num_rows($req_verify); 


if($rows_verify == '0'){
	$sql_insert = "INSERT INTO `stats_retention_pop_up` 
						  (`id`, `timsetamp`, `id_commercial`, `id_product`, `ip`) 
				   VALUES (NULL, ".$timestamp_created.", ".$id_commercial.", ".$id_proucts.", '".$ip_client."')";				   
	mysql_query($sql_insert);
	echo 'yes';
}else {

$sql_check  = "SELECT id
			   FROM stats_retention_pop_up
			   WHERE  NOW()> DATE_ADD(From_unixtime(timsetamp) , INTERVAL 12 HOUR )
			   AND ip= '".$ip_client."' ";
$req_check  =  mysql_query($sql_check);
$rows_check =  mysql_num_rows($req_check);

if($rows_check > 0 ){
	$data_check  = mysql_fetch_object($req_check);
		
	$sql_update = " UPDATE `stats_retention_pop_up` SET `timsetamp` = '".$timestamp_created."' 
				    WHERE `id` ='".$data_check->id."' ";
	mysql_query($sql_update);
	
	echo 'yes';
}
}
			   

?>