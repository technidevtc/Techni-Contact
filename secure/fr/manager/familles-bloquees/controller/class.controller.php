<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
$db     = DBHandle::get_instance();

$action  = $_GET["action"];

if($action == "views_blog"){
	$id = $_GET['id_famille'];
	$name = $_GET['name'];
	
	$sql_verify  = "SELECT id ,blocked_activated,eligible
				    FROM families_leads_blocked_for_tc_sales
				    WHERE idFamily ='".$id."'";
	$req_verify  =  mysql_query($sql_verify);
	$rows_verify =  mysql_num_rows($req_verify);
	$data_verify  = mysql_fetch_object($req_verify);
	echo '<div style="border:1px solid #ddd;padding: 12px;width: 535px;overflow: hidden;">';
			echo '<div style="float: left;">';
					echo '<div><b>Nom de la famille :</b> '.$name.' </div>';
					echo '<div><b>ID :</b> '.$id.'</div>';
				if($data_verify->blocked_activated == 1){
					echo '<div><b>Statut :</b> Activé</div>';
				}else{
					echo '<div><b>Statut :</b> Désactivé </div>';
				}
				
				if($data_verify->eligible == 1){
					echo '<div><b>Eligible :</b> Oui </div>';
				}else{
					echo '<div><b>Eligible :</b> Non </div>';
				}
				
				
			echo '</div>';
			
			if($rows_verify != 0) $disabled=" ";
			else $disabled=" disabled ";
			
			echo '<div style="border:1px solid #ddd;padding: 12px;width: 250px;float: right;">';
				echo '<div><b>Envoi de mail aux commerciaux </b></div>';
				if($data_verify->blocked_activated == 1){
					echo 'Activé <input type="radio"    name="x" value="1" '.$disabled.' checked onclick="enabled_families('.$id.')" > 
					      Désactivé <input type="radio" name="x" value="0" '.$disabled.' onclick="disabled_families('.$id.')">';
				}else{
					echo 'Activé <input type="radio"     name="x" value="1" '.$disabled.' onclick="enabled_families('.$id.')" > 
					      Désactivé <input type="radio"  name="x" value="0" '.$disabled.' checked onclick="disabled_families('.$id.')" >';
				}
			echo '</div>';
	
	echo '</div>';
	
}

if($action == "enabled_families"){
	$idFamily = $_GET['id_famille'];
	
	$sql_update = "UPDATE `families_leads_blocked_for_tc_sales` 
					   SET    `blocked_activated` = '1',
							  `unblocked_timestamp` = NOW( ) ,
							  `update_timestamp` = NOW( ) ,
							  `blocked_activated_type` = 'manual'
					  WHERE   `idFamily` ='$idFamily'";
	mysql_query($sql_update);	
}

if($action == "disabled_families"){
	$idFamily = $_GET['id_famille'];
	
	$sql_update = "UPDATE `families_leads_blocked_for_tc_sales` 
					   SET    `blocked_activated` = '0',
							  `blocked_timestamp` = NOW( ) ,
							  `update_timestamp` = NOW( ) ,
							  `blocked_activated_type` = 'manual'
					  WHERE   `idFamily` ='$idFamily'";
	mysql_query($sql_update);	
}

?>

