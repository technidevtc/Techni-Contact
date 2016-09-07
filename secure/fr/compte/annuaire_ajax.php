<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
$handle = DBHandle::get_instance();


$etat 		= $_GET['value_radio'];
$action 	= $_GET['action'];
$id_client 	= $_GET['id_client'];

if($action == 'params_produit'){
	$sql="UPDATE  `annuaire_questionnaire` SET  `etat` =  '$etat' WHERE id_client=$id_client";
	mysql_query($sql);
	echo $sql;
}

if($action == 'params_soceite'){
	$sql="UPDATE  `annuaire_client` SET  `etat` =  '$etat' WHERE client_id=$id_client";
	mysql_query($sql);
	echo  $sql;
}

?>