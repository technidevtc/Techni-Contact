<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
$db = DBHandle::get_instance();

$keyword = '%'.$_POST['keyword'].'%';
$sql = "SELECT name  FROM families_fr WHERE name LIKE '$keyword'  ORDER BY name ASC LIMIT 0, 10 ";
$req = mysql_query($sql);
$rows= mysql_num_rows($req);
if($rows > 0 ){
	
while($data = mysql_fetch_assoc($req)){
	$email = str_replace('"','',$data['name']);	
	echo '<li onclick="set_item_kpi(\''.$email.'\')">'.$email.'</li>';
}
}else {
	echo '<li>Appuyez sur Entrée pour lancer la recherche.</li>';
	
}
?>