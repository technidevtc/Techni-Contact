<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
$db = DBHandle::get_instance();

$keyword = '%'.$_POST['keyword'].'%';
$first_famille = $_POST['first_famille'];
	$sql = "SELECT fr.id,fr.name 
			FROM families_fr fr , families ff
		WHERE fr.id = ff.id
			AND name LIKE '$keyword' "; 
if(empty($first_famille)){
	$sql .= " AND ff.idParent NOT IN('0','1','2','3','4','5','6','6329','7','8','9','10','11')";
}else {
	$sql .= " ".$first_famille." ";
}		
	$sql .=	" ORDER BY name ASC LIMIT 0, 10 ";
$req = mysql_query($sql); 

//echo $sql ;
while($data = mysql_fetch_assoc($req)){
	$name_bdd = mysql_real_escape_string($data['name']);
	$name = str_replace('"','',$name_bdd);
	$id   = str_replace('"','',$data['id']);
	echo '<li onclick="set_item(\''.$name.'\',\''.$id.'\')">'.$name.'<img src="images/add_small.png" class="img-autocomplate"></li>';
}
?>