<?php
if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}
$db = DBHandle::get_instance();
$user = new BOUser();

$action= $_GET['action'];

if($action == "campagne"){
	$id_lign  = $_GET['id_ligne'];

	$sql_check  = "SELECT call_operator,ligne_active FROM call_spool_vpc WHERE id='".$id_lign."' ";
	$req_check  = mysql_query($sql_check);
	$data_check = mysql_fetch_object($req_check);

	if($data_check->ligne_active == '0'){
		$sql_select  = "SELECT client_id FROM call_spool_vpc WHERE id='".$id_lign."' ";
		$req_select  = mysql_query($sql_select);
		$data_select = mysql_fetch_object($req_select);		
		
		$actual_link   = ADMIN_URL."clients/?idClient=".$data_select->client_id."&idCall=".$id_lign."&params=display_bars";
		$sql_insert    = "INSERT INTO `current_action_vpc` (`id`, `id_ligne_vpc`, `id_user_bo`, `url_action`) 
						  VALUES (NULL, '".$id_lign."', '".$_SESSION["id"]."', '".$actual_link."')";
		mysql_query($sql_insert);
		echo 'ok';
	}else{
		$sql_users  = "SELECT name FROM bo_users WHERE id='".$data_check->call_operator."' ";
		$req_users  = mysql_query($sql_users);
		$data_users = mysql_fetch_object($req_users);
		echo 'ligne occupé par  : '.$data_users->name;
	}
}

if($action == "relance"){
	$id_lign  = $_GET['id_ligne'];
	$sql_check  = "SELECT call_operator,ligne_active FROM call_spool_vpc WHERE id='".$id_lign."' ";
	$req_check  = mysql_query($sql_check);
	$data_check = mysql_fetch_object($req_check);
	if($data_check->ligne_active == '0'){
		
		$sql_select  = "SELECT estimate_id FROM call_spool_vpc WHERE id='".$id_lign."' ";
		$req_select  = mysql_query($sql_select);
		$data_select = mysql_fetch_object($req_select);
		
		$actual_link   = ADMIN_URL."estimates/estimate-detail.php?id=".$data_select->estimate_id."&idCall=".$id_lign."&params=display_bars";
		$sql_insert    = "INSERT INTO `current_action_vpc` (`id`, `id_ligne_vpc`, `id_user_bo`, `url_action`) 
						  VALUES (NULL, '".$id_lign."', '".$_SESSION["id"]."', '".$actual_link."')";
		mysql_query($sql_insert);
		echo 'ok';
	}else{
		$sql_users  = "SELECT name FROM bo_users WHERE id='".$data_check->call_operator."' ";
		$req_users  = mysql_query($sql_users);
		$data_users = mysql_fetch_object($req_users);
		echo 'ligne occupé par  : '.$data_users->name;
	}
}

if($action == "feedback"){
	$id_lign  = $_GET['id_ligne'];
	$sql_check  = "SELECT call_operator,ligne_active FROM call_spool_vpc WHERE id='".$id_lign."' ";
	$req_check  = mysql_query($sql_check);
	$data_check = mysql_fetch_object($req_check);
	if($data_check->ligne_active == '0'){
		
		$sql_select  = "SELECT order_id FROM call_spool_vpc WHERE id='".$id_lign."' ";
		$req_select  = mysql_query($sql_select);
		$data_select = mysql_fetch_object($req_select);
		
		$actual_link   = ADMIN_URL."orders/order-detail.php?id=".$data_select->order_id."&idCall=".$id_lign."&params=display_bars";
		$sql_insert    = "INSERT INTO `current_action_vpc` (`id`, `id_ligne_vpc`, `id_user_bo`, `url_action`) 
						  VALUES (NULL, '".$id_lign."', '".$_SESSION["id"]."', '".$actual_link."')";
		mysql_query($sql_insert);
		echo 'ok';
	}else{
		$sql_users  = "SELECT name FROM bo_users WHERE id='".$data_check->call_operator."' ";
		$req_users  = mysql_query($sql_users);
		$data_users = mysql_fetch_object($req_users);
		echo 'ligne occupé par  : '.$data_users->name;
	}
}

if($action == "rdv"){
	$id_lign  = $_GET['id_ligne'];
	$sql_check  = "SELECT call_operator,ligne_active FROM call_spool_vpc WHERE id='".$id_lign."' ";
	$req_check  = mysql_query($sql_check);
	$data_check = mysql_fetch_object($req_check);
	if($data_check->ligne_active == '0'){
		
		$sql_select  = "SELECT client_id,estimate_id FROM call_spool_vpc WHERE id='".$id_lign."' ";
		$req_select  = mysql_query($sql_select);
		$data_select = mysql_fetch_object($req_select);
		
		if(!empty($data_select->client_id)){
			$actual_link   = ADMIN_URL."clients/?idClient=".$data_select->client_id."&idCall=".$id_lign."&params=display_bars";
		}
		if(!empty($data_select->estimate_id)){
			$actual_link   = ADMIN_URL."estimates/estimate-detail.php?id=".$data_select->estimate_id."&idCall=".$id_lign."&params=display_bars";
		}
		
		$sql_insert    = "INSERT INTO `current_action_vpc` (`id`, `id_ligne_vpc`, `id_user_bo`, `url_action`) 
						  VALUES (NULL, '".$id_lign."', '".$_SESSION["id"]."', '".$actual_link."')";
		mysql_query($sql_insert);
		echo 'ok';
	}else{
		$sql_users  = "SELECT name FROM bo_users WHERE id='".$data_check->call_operator."' ";
		$req_users  = mysql_query($sql_users);
		$data_users = mysql_fetch_object($req_users);
		echo 'ligne occupé par  : '.$data_users->name;
	}
}

if($action == "requalif"){
	$id_lign  = $_GET['id_ligne'];
	$sql_check  = "SELECT call_operator,ligne_active FROM call_spool_vpc WHERE id='".$id_lign."' ";
	$req_check  = mysql_query($sql_check);
	$data_check = mysql_fetch_object($req_check);
	if($data_check->ligne_active == '0'){
		
		$sql_select  = "SELECT client_id,id_contact FROM call_spool_vpc WHERE id='".$id_lign."' ";
		$req_select  = mysql_query($sql_select);
		$data_select = mysql_fetch_object($req_select);
		$actual_link   = ADMIN_URL."supplier-leads/lead-detail.php?id=".$data_select->id_contact."&idCall=".$id_lign."&params=display_bars";
		
		$sql_insert    = "INSERT INTO `current_action_vpc` (`id`, `id_ligne_vpc`, `id_user_bo`, `url_action`) 
						  VALUES (NULL, '".$id_lign."', '".$_SESSION["id"]."', '".$actual_link."')";
		mysql_query($sql_insert);
		echo 'ok';
	}else{
		$sql_users  = "SELECT name FROM bo_users WHERE id='".$data_check->call_operator."' ";
		$req_users  = mysql_query($sql_users);
		$data_users = mysql_fetch_object($req_users);
		echo 'ligne occupé par  : '.$data_users->name;
	}
}




?>