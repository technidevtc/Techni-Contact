<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
$db     = DBHandle::get_instance();

$user = new BOUser();


//$handle = DBHandle::get_instance();
$user   = $userChildScript = new BOUser();


$action  = $_GET["action"];

/*
function toAscii($str) {
	$clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $str);
	$clean = strtolower(trim($clean, '-'));
	$clean = preg_replace("/[\/_|+ -]+/", '-', $clean);

	return $clean;
}
*/
function toAscii($url = ''){
   $bad = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ';
   $good = 'aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr';  
   $url= strtr(utf8_decode($url), utf8_decode($bad), $good);
   return preg_replace("/[^A-Za-z0-9_-]+/", "-", $url);
}

if($action == "create_guide"){
	$name_guide   =  mysql_real_escape_string($_POST['name_guide']);
	$titre_guide  =  mysql_real_escape_string($_POST['titre_guide']);
	$meta_title   =  mysql_real_escape_string($_POST['meta_title']);
	$meta_desc    =  mysql_real_escape_string($_POST['meta_desc']);
	$desc         =  mysql_real_escape_string($_POST['desc']);
	$id_famille   =  mysql_real_escape_string($_POST['id_famille']);
	$id_first_famille =  mysql_real_escape_string($_POST['id_first_famille']);
	$ref_name     =  toAscii($titre_guide);
	$ref_name     =  strtolower($ref_name);
	$user_bo_add  =  $_SESSION["id"];
	
	
	$sql_insert   = "INSERT INTO `guides_achat` (
						`id` ,
						`id_famille_parent` ,
						`guide_name` ,
						`title_h` ,
						`title_meta` ,
						`desc_meta` ,
						`quide_content` ,
						`ref_name` ,
						`create_date` ,
						`update_date` ,
						`user_bo_create` ,
						`user_bo_update`
						)VALUES (
						NULL ,  '$id_first_famille',  '$name_guide',  '$titre_guide',  '$meta_title',  '$meta_desc',  '$desc',  '$ref_name', NOW() ,'0000-00-00 00:00:00',  '$user_bo_add',  '')";
	mysql_query($sql_insert);
	
	$sql_max  = "SELECT MAX(id) as total FROM guides_achat";
	$req_max  =  mysql_query($sql_max);
	$data_max =  mysql_fetch_object($req_max);
	
	$id_guide = $data_max->total;
	
	$id_familie_explode = explode('-',$id_famille);
	
	foreach($id_familie_explode as $value_familie){
		
		if(!empty($value_familie)){
			$sql_link = "INSERT INTO  `guides_linked_familles` (
							`id` ,
							`id_guide` ,
							`id_familles_first` ,
							`id_familles_three`
							)
							VALUES (NULL ,  '$id_guide',  '$id_first_famille',  '$value_familie')";
			mysql_query($sql_link);
		}
	}
	
	header('Location: ../index.php?add_guide=success');

}

if($action == "check_famille_first"){
		$id_famille  = $_GET['id_famille'];
		
		$sql_famillie  = "SELECT idParent
							FROM families 
						  WHERE id='".$id_famille."' ";
		$req_famillie  =  mysql_query($sql_famillie);
		$data_famillie =  mysql_fetch_object($req_famillie);
		
		$sql_famillie2  = "SELECT ff.idParent , fr.name ,fr.id
							FROM families ff , families_fr  fr
						  WHERE ff.id = fr.id 
						  AND fr.id='".$data_famillie->idParent."' ";
		$req_famillie2  =  mysql_query($sql_famillie2);
		$data_famillie2 =  mysql_fetch_object($req_famillie2);
		//echo $sql_famillie2;
		
		$sql_famillie3  = "SELECT idParent ,id
							FROM families 
						   WHERE id='".$data_famillie2->idParent."' ";
		$req_famillie3  =  mysql_query($sql_famillie3);
		$data_famillie3 =  mysql_fetch_object($req_famillie3);
		
		$sql_families_first  = "SELECT id,name
							   FROM families_fr
							   WHERE id='".$data_famillie3->id."'";
        $req_families_first  =  mysql_query($sql_families_first);
		$data_families_first =  mysql_fetch_object($req_families_first);
			
		
		echo $data_families_first->name;
		//echo $data_famillie2->name;
}

if($action == "get_id_famille_first"){
		$id_famille  = $_GET['id_famille'];
		
		$sql_famillie  = "SELECT idParent
							FROM families 
						  WHERE id='".$id_famille."' ";
		$req_famillie  =  mysql_query($sql_famillie);
		$data_famillie =  mysql_fetch_object($req_famillie);
		
		$sql_famillie2  = "SELECT ff.idParent , fr.name ,fr.id
							FROM families ff , families_fr  fr
						  WHERE ff.id = fr.id 
						  AND fr.id='".$data_famillie->idParent."' ";
		$req_famillie2  =  mysql_query($sql_famillie2);
		$data_famillie2 =  mysql_fetch_object($req_famillie2);
		//echo $sql_famillie2;
		
		$sql_famillie3  = "SELECT idParent ,id
							FROM families 
						   WHERE id='".$data_famillie2->idParent."' ";
		$req_famillie3  =  mysql_query($sql_famillie3);
		$data_famillie3 =  mysql_fetch_object($req_famillie3);
		
		$sql_families_first  = "SELECT id,name
							   FROM families_fr
							   WHERE id='".$data_famillie3->id."'";
        $req_families_first  =  mysql_query($sql_families_first);
		$data_families_first =  mysql_fetch_object($req_families_first);
			
		
		echo $data_families_first->id;
		//echo $data_famillie2->name;
}


if($action == "autocomplate_dynamic"){
	$id_famille = $_GET['id_famille'];
	
	$sql = "SELECT idParent,id FROM families WHERE id='".$id_famille."' ";
	$req =  mysql_query($sql);
	$data=  mysql_fetch_object($req);
	

	$sql_parent = "SELECT idParent FROM families WHERE id='".$data->idParent."'";
	$req_parent =  mysql_query($sql_parent);
	$data_parent=  mysql_fetch_object($req_parent);
	
	$sql_all = "SELECT id FROM families WHERE idParent='".$data_parent->idParent."'";
	$req_all =  mysql_query($sql_all);
	
	$sql_send = " AND ff.idParent IN(";
	while($data_all = mysql_fetch_object($req_all)){
			$sql_send .= " '".$data_all->id."',";
	}
	$rest = substr($sql_send, 0, -1);
	$rest .=  ")";
	echo $rest;
	//echo $data->idParent;	
}

if($action == "delete_guide"){
	$id = $_GET['id'];
	
	$sql_delete = "DELETE FROM guides_achat WHERE id=$id ";
	mysql_query($sql_delete);
	echo $sql_delete;
	$sql_delete_link = "DELETE FROM guides_linked_familles WHERE id_guide=$id ";
	mysql_query($sql_delete_link);
}

if($action == "name_families"){
	$name = $_GET['name'];
	$id   = $_GET['id'];
	
	$sql_img  = "SELECT id,path_img FROM guides_visuel WHERE id_famille='".$id."' ";
	$req_img  =  mysql_query($sql_img);
	$data_img =  mysql_fetch_object($req_img);
	echo '	
	<input type="hidden" name="id_families" id="id_families" value="'.$id.'" />
	<div>
		<div style="border: 1px solid #ddd;padding: 10px;">Nom de la Famille 1 : <strong>'.$name.'</strong></div>	
		<div id="id_photo_facade">';
		if(empty($data_img->path_img)){
			echo '<img id="preview_facade" src="images/imgres.jpg" style="width: 165px;" /><br />';
		}else {
			echo '<img id="preview_facade" src="'.URL.'ressources/images/guides/'.$data_img->path_img.'" style="width: 165px;" /><br />';
		}
			
	echo'	<input type="file" name="adress_picture" id="photo_facade" onChange="fileSelected(this.id);" data-buttonText="Your label here." />
		</div>
	</div>';	
}

if($action == "delete_img"){
	$id   = $_GET['id'];
	$sql_update = "UPDATE `guides_visuel` SET `path_img`='' WHERE id_famille='".$id."' ";
	mysql_query($sql_update);

}

if($action == "update_guide"){
	$id_guide     =  $_POST['id_guide'];
	$name_guide   =  mysql_real_escape_string($_POST['name_guide']);
	$titre_guide  =  mysql_real_escape_string($_POST['titre_guide']);
	$meta_title   =  mysql_real_escape_string($_POST['meta_title']);
	$meta_desc    =  mysql_real_escape_string($_POST['meta_desc']);
	$desc         =  mysql_real_escape_string($_POST['desc']);
	$id_famille_3   =  mysql_real_escape_string($_POST['id_famille']);
	$id_first_famille =  mysql_real_escape_string($_POST['id_first_famille']);
	$delete_families =  mysql_real_escape_string($_POST['delete_families']);
	$ref_name     =  toAscii($titre_guide);
	$user_bo_add  =  $_SESSION["id"];

	if(!empty($delete_families)){
		$id_familie_explode_delete = explode('-',$delete_families);
			foreach($id_familie_explode_delete as $value_familie_delete){
				if(!empty($value_familie_delete)){
				$sql_delete  = "DELETE FROM guides_linked_familles WHERE id_guide='".$id_guide."' AND id_familles_three='".$value_familie_delete."' ";
				echo $sql_delete.'<br />';
				$req_delete  =  mysql_query($sql_delete);
				}
			}
	}
	
	$sql_update_guide = "UPDATE `guides_achat` SET  
							`id_famille_parent` =  '$id_first_famille',
							`guide_name` =  '$name_guide',
							`title_h` =  '$titre_guide',
							`title_meta` =  '$meta_title',
							`desc_meta` =  '$meta_desc',
							`quide_content` =  '$desc',
							`update_date` = NOW() ,
							`user_bo_update` =  '$user_bo_add' 
						WHERE  `id` ='".$id_guide."'";
	mysql_query($sql_update_guide);
	
	if(!empty($id_famille_3)){
		$id_familie_explode_delete = explode('-',$id_famille_3);
			foreach($id_familie_explode_delete as $value_familie_delete){
				if(!empty($value_familie_delete)){
				
				$sql_verify  = "SELECT id FROM guides_linked_familles 
							    WHERE id_guide='".$id_guide."' AND id_familles_three='".$value_familie_delete."' ";
				$req_verify  =  mysql_query($sql_verify);
				$rows_verify =  mysql_num_rows($req_verify);
				if($rows_verify > 0){
					$data_verify = mysql_fetch_object($req_verify);
					$sql_update_check = "UPDATE `guides_linked_familles` SET id_familles_three='".$value_familie_delete."' 
									     WHERE id='".$data_verify->id."' ";
					mysql_query($sql_update_check);
				}else{
					$sql_link = "INSERT INTO  `guides_linked_familles` (
								`id` ,
								`id_guide` ,
								`id_familles_first` ,
								`id_familles_three`
								)
								VALUES (NULL ,  '$id_guide',  '$id_first_famille',  '$value_familie_delete')";
					echo $sql_link.'<br />';
					mysql_query($sql_link);
				}
				}
	}
}
	header('Location: ../edit-guide.php?id='.$id_guide.'&update_guide=success');
}

if($action == "charger_familie_popup"){
	
	$id_guide  = $_GET['id'];
	
	$sql_guide  = "SELECT guide_name
				   FROM guides_achat
				   WHERE id='".$id_guide."' ";
	$req_guide  =  mysql_query($sql_guide);
	$data_guide =  mysql_fetch_object($req_guide);
	
	echo '<div style="border: 1px solid #ddd;padding: 10px;">Liste des Familles 3 pour le Guide : <strong>'.$data_guide->guide_name.'</strong></div>';
	echo '<br /><ul class="liste-ul-style">';
	$sql_f3 = "SELECT name
					   FROM guides_linked_familles glf, families_fr ffr
					   WHERE glf.id_familles_three = ffr.id
					   AND id_guide='".$id_guide."' ";
	$req_f3 = mysql_query($sql_f3);
	while($data_f3 = mysql_fetch_object($req_f3)){
		echo '<li>- '.$data_f3->name.'</li>';
	}
	echo '</ul><br />';
}
?>

