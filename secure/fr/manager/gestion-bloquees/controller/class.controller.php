<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
$db     = DBHandle::get_instance();

$user = new BOUser();


//$handle = DBHandle::get_instance();
$user   = $userChildScript = new BOUser();


$action  = $_GET["action"];


function toAscii($url = ''){
   $bad = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ';
   $good = 'aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr';  
   $url= strtr(utf8_decode($url), utf8_decode($bad), $good);
   return preg_replace("/[^A-Za-z0-9_-]+/", "-", $url);
}

function reduit_texte($texte, $minlen, $maxlen, $separateur = ' ', $suffix = ''){
    $resultat = $texte;
    if (strlen($resultat) > $maxlen) {
        if (($pos = strrpos(substr($resultat, 0, $maxlen + strlen( $separateur )
), $separateur)) !== false) {
            if ($pos < $minlen) {
                $resultat = substr($resultat, 0, $maxlen) . $suffix;
            } else {
                $resultat = substr($resultat, 0, $pos) . $suffix;
            }
        } else {
            $resultat = substr($resultat, 0, $maxlen) . $suffix;
        }
    }
    return $resultat;
}

function random($universal_key) {
	$string1 = "";
	$user_ramdom_key = "1234567890";
	srand((double)microtime()*time());
	for($i=0; $i<$universal_key; $i++) {
	$string1 .= $user_ramdom_key[rand()%strlen($user_ramdom_key)];
	}
	return $string1;
}

if($action == "create_article"){
	
	$titre_article  =  mysql_real_escape_string($_POST['titre_article']);
	$meta_title   =  mysql_real_escape_string($_POST['meta_title']);
	$meta_desc    =  mysql_real_escape_string($_POST['meta_desc']);
	$statut       =  mysql_real_escape_string($_POST['statut']);
	$desc         =  mysql_real_escape_string($_POST['desc']);
	
	$keyUp_enter =  mysql_real_escape_string($_POST['keyUp-enter']);
	$ref_name     =  toAscii($titre_article);
	$ref_name     =  strtolower($ref_name);
	$user_bo_add  =  $_SESSION["id"];
	
	if(empty($meta_title)){
		$meta_title = $titre_article." - Le blog Techni-Contact ";
	}
	
	if(empty($meta_desc)){
		$content = strip_tags($desc);
		$meta_desc = reduit_texte(stripslashes($content), 15, 400, ' ', '');
				
	}
	
	
	
	$sql_insert   = "INSERT INTO    `blog_articles` (
									`id` ,
									`article_title` ,
									`title_meta` ,
									`desc_meta` ,
									`ref_name` ,
									`content` ,
									`promo_image` ,
									`timestamp_created` ,
									`timestamp_updated` ,
									`bo_user_created` ,
									`bo_user_updated` ,
									`statut`
									)
									VALUES (
									NULL ,  '$titre_article',  '$meta_title',  '$meta_desc',  '$ref_name',  '$desc ',  '', NOW() ,  '0000-00-00 00:00:00',  '$user_bo_add',  '0',  '$statut')";
	// mysql_query($sql_insert);
	// echo $sql_insert;
	$sql_max  = "SELECT MAX(id) as total FROM blog_articles";
	$req_max  =  mysql_query($sql_max);
	$data_max =  mysql_fetch_object($req_max);
	
	$id_articles = $data_max->total;
	
	$keyUp_enter_explode = explode('-',$keyUp_enter);
	
	foreach($keyUp_enter_explode as $value_enter){
		
		if(!empty($value_enter)){
			$sql_link = "INSERT INTO  `blog_tags_linked_articles` (
							`id` ,
							`id_tag` ,
							`id_article` ) 
						  VALUES ('NULL', '$value_enter',  '$id_articles')";
			// mysql_query($sql_link);
		}
	}
	
	$key_1     		= random(10);
	$key_2   		= random(10);
	$target_path = "/data/technico-test/www/fr/ressources/images/catalogues/"; 
	// $target_path = URL."ressources/images/catalogues/";1.gif
	echo $target_path;
	$validextensions = array("jpeg", "jpg", "png");
    $ext = explode('.', basename($_FILES['adress_picture']['name']));
    $file_extension = end($ext);                     
    $name_adress_picture    =    $key_1;
    $name_adress_picture    =    $name_adress_picture.$key_2;
    $name_adress_picture    =    $name_adress_picture.'.';
    $name_adress_picture    =    $name_adress_picture.$ext[count($ext) - 1];
	// echo $_FILES['adress_picture']['tmp_name'];
	    if (move_uploaded_file($_FILES['adress_picture']['tmp_name'], $target_path.$name_adress_picture)) {
			$sql_update  ="UPDATE  `blog_articles` SET  `promo_image` =  '$name_adress_picture' WHERE  `id` =$id_articles";
			echo 'aaaa';
			mysql_query($sql_update);
			
			
		}
	
	// header('Location: ../index.php?add_article=success');
}

if($action == "update_article"){
	
	$titre_article  =  mysql_real_escape_string($_POST['titre_article']);
	$meta_title   =  mysql_real_escape_string($_POST['meta_title']);
	$meta_desc    =  mysql_real_escape_string($_POST['meta_desc']);
	$statut       =  mysql_real_escape_string($_POST['statut']);
	$desc         =  mysql_real_escape_string($_POST['desc']);
	$id_articles  =  mysql_real_escape_string($_POST['id_articles']);
	
	$keyUp_enter =  mysql_real_escape_string($_POST['keyUp-enter']);
	$ref_name     =  toAscii($titre_article);
	$ref_name     =  strtolower($ref_name);
	$user_bo_add  =  $_SESSION["id"];
	
	
	
	
	//`ref_name` 		='$ref_name',
	$sql_update   = "UPDATE  `blog_articles` 
					 SET  
						`article_title` ='$titre_article',
						`title_meta` 	='$meta_title',
						`desc_meta` 	='$meta_desc ',
						
						`content` 		='$desc',
						`timestamp_updated` =NOW(),
						`bo_user_updated` =$user_bo_add,
						`statut`= '$statut'
					 WHERE  `id` =$id_articles";
	mysql_query($sql_update);
	
	$sql_delete = "DELETE FROM blog_tags_linked_articles WHERE id_article='".$id_articles."'";
	mysql_query($sql_delete);
	
	$keyUp_enter_explode = explode('-',$keyUp_enter);
	
	foreach($keyUp_enter_explode as $value_enter){
		
		if(!empty($value_enter)){
			$sql_link = "INSERT INTO  `blog_tags_linked_articles` (
							`id` ,
							`id_tag` ,
							`id_article` ) 
						  VALUES ('NULL', '$value_enter',  '$id_articles')";
			mysql_query($sql_link);
		}
	}
	
	header('Location: ../index.php?update_article=success');
}


if($action =="delete_article"){
	$id  =  $_GET['id'];
	$sql_delete = "DELETE FROM blog_articles WHERE id='".$id."'";
	mysql_query($sql_delete);
	
	$sql_delete_article = "DELETE FROM blog_tags_linked_articles WHERE id_article='".$id."'";
	mysql_query($sql_delete_article);	
}

if($action =="charger_tag_popup"){
	$sql  =  "SELECT id,name FROM blog_tags_names ORDER BY name ASC";
	$req  =   mysql_query($sql);
	while($data  =  mysql_fetch_object($req)){
		echo '<div style="    width: 160px;">
				<div class="name-tag_sty" id="name-tag_sty'.$data->id.'" onclick="double_click('.$data->id.')"><span id="name_tag_'.$data->id.'">'.$data->name.'</span>
										  <input type="text" id="value_tag_'.$data->id.'" value="'.$data->name.'" style="display:none;    width: 110px;" />
				</div>
				<div style=" float: right;    margin-top: -6px;"><img src="images/supprimer-vide-ordures-corbeille-corbeille-icone-5257-96.png"     width="35px" onclick="delete_tag('.$data->id.')" style="cursor: pointer;" /></div>
			  </div>';
	}
}
if($action =="create_tag"){
	$name = mysql_real_escape_string($_GET['name']);
	$ref_name = toAscii($name);
	$ref_name     =  strtolower($ref_name);
	$sql_add =  "INSERT INTO `blog_tags_names` (`id`, `name`, `ref_name`) VALUES (NULL, '$name', '$ref_name')";
	mysql_query($sql_add);
	
	$sql  =  "SELECT id,name FROM blog_tags_names ORDER BY name ASC";
	$req  =   mysql_query($sql);
	while($data  =  mysql_fetch_object($req)){
		echo '<div style="width: 160px;">
				<div>
				<div class="name-tag_sty" id="name-tag_sty'.$data->id.'" onclick="double_click('.$data->id.')"><span id="name_tag_'.$data->id.'">'.$data->name.'</span>
										  <input type="text" id="value_tag_'.$data->id.'" value="'.$data->name.'" style="display:none;    width: 110px;" />
				</div>
				<div style=" float: right;    margin-top: -6px;"><img src="images/supprimer-vide-ordures-corbeille-corbeille-icone-5257-96.png"     width="35px" onclick="delete_tag('.$data->id.')" style="cursor: pointer;" /></div>
				</div>
			  </div>';
	}
}

if($action =="delete_tag"){
	$id = $_GET['id'];
	$sql_delete = "DELETE FROM blog_tags_names WHERE id='".$id."' ";
	mysql_query($sql_delete);
	
	$sql  =  "SELECT id,name FROM blog_tags_names ORDER BY name ASC";
	$req  =   mysql_query($sql);
	while($data  =  mysql_fetch_object($req)){
		echo '<div style="width: 160px;">
				<div>
				<div class="name-tag_sty" id="name-tag_sty'.$data->id.'" onclick="double_click('.$data->id.')"><span id="name_tag_'.$data->id.'">'.$data->name.'</span>
										  <input type="text" id="value_tag_'.$data->id.'" value="'.$data->name.'" style="display:none;    width: 110px;" />
				</div>
				<div style=" float: right;    margin-top: -6px;"><img src="images/supprimer-vide-ordures-corbeille-corbeille-icone-5257-96.png"     width="35px" onclick="delete_tag('.$data->id.')" style="cursor: pointer;" /></div>
				</div>
			  </div>';
	}
}

if($action =="update_tag"){
	$id   = $_GET['id'];
	$name = mysql_real_escape_string($_GET['name']);
	$sql_update  ="UPDATE  `blog_tags_names` SET  `name` =  '$name' WHERE  `id` =$id";
	mysql_query($sql_update);
	
	
	$sql  =  "SELECT id,name FROM blog_tags_names ORDER BY name ASC";
	$req  =   mysql_query($sql);
	while($data  =  mysql_fetch_object($req)){
		echo '<div style="width: 160px;">
				<div>
				<div class="name-tag_sty" id="name-tag_sty'.$data->id.'" onclick="double_click('.$data->id.')"><span id="name_tag_'.$data->id.'">'.$data->name.'</span>
										  <input type="text" id="value_tag_'.$data->id.'" value="'.$data->name.'" style="display:none;    width: 110px;" />
				</div>
				<div style=" float: right;    margin-top: -6px;"><img src="images/supprimer-vide-ordures-corbeille-corbeille-icone-5257-96.png"     width="35px" onclick="delete_tag('.$data->id.')" style="cursor: pointer;" /></div>
				</div>
			  </div>';
	}
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


?>

