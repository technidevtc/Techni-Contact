<?php
mysql_connect('localhost','technico','os2GL72yOF6wBl6m');
mysql_select_db('technico-test');
mysql_query("SET NAMES 'utf8'");

$keyword = '%'.$_POST['keyword'].'%';
$sql = "SELECT email FROM clients WHERE email LIKE '$keyword'  ORDER BY email ASC LIMIT 0, 10 ";
$req = mysql_query($sql);
while($data = mysql_fetch_assoc($req)){
	$email = str_replace('"','',$data['email']);
	
	echo '<li onclick="set_item(\''.$email.'\')">'.$email.'</li>';
}
?>