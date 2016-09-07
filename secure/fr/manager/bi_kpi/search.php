<?php
if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

$db = DBHandle::get_instance();


if(isset($_GET['families_send'])){
	
	$annee 		= $_GET['annee'];
	$mois_debut = $_GET['mois_debut'];
	$mois_fin	= $_GET['mois_fin'];
	
	$date_debut = '01-'.$mois_debut.'-'.$_GET['annee'].'';
	$date_fin = '30-'.$mois_fin.'-'.$_GET['annee'].'';
	
	$timestamp_debut = strtotime($date_debut);
	$timestamp_fin = strtotime($date_fin);

	
	$families_send = $_GET['families_send'];
	$s = explode(" ",$families_send);
	$sql_search = "SELECT id,name  FROM families_fr  ";
	$i = 0;
	foreach($s as $mot){
		if(strlen($mot) > 3 ){
			if($i == 0){
				$sql_search .= " WHERE ";
			}else {
				$sql_search .= " OR ";
			}
			
			$sql_search .= " name LIKE '%$mot%' ";
			$i++;
		}
	}
	$req_search = mysql_query($sql_search);
	$rows       = mysql_num_rows($req_search);
	
	if($rows > 0){
		
		while($data_search = mysql_fetch_object($req_search)){
			
			$sql_bettwen="SELECT idFamily 
							FROM contacts 
							WHERE create_time 
							BETWEEN $timestamp_debut AND $timestamp_fin
							AND idFamily ='".$data_search->id."' ";
			$req_bettwen  = mysql_query($sql_bettwen);
			$rows_bettwen = mysql_num_rows($req_bettwen);
			
			if($rows_bettwen > 0 ){
				$data_bettwen = mysql_fetch_object($req_bettwen);
				echo 'id : '.$data_bettwen->idFamily.' Nom : '.$data_search->name.'<br /><br />';
			}
			
			
			//echo 'id : '.$data_search->id.' Nom : '.$data_search->name.'<br /><br />';
		}
	
	}

}

?>
