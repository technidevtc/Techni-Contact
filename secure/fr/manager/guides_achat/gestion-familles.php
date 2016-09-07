<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
$db = DBHandle::get_instance();



$title = $navBar = "Gestion des guides d'achat";
require(ADMIN."head.php");

//if ((!$userPerms->has($fntByName["m-comm--sm-pile-appel-personaliser"], "re")) || (!$userPerms->has($fntByName["m-comm--sm-pile-appels-complete"], "re")) ) {
if ((!$userChildScript->get_permissions()->has("m-mark--sm-gestion-achat","r")) ) {
?>
<div class="bg">
  <div class="fatalerror">Vous n'avez pas les droits ad&eacute;quats pour r&eacute;aliser cette op&eacute;ration.</div>
</div>
<?php
}

else {
  /*$f = BOFunctionality::get("id","name='bi-kpi'");
  if (!empty($f)) {
    $ups = BOUserPermission::get("id_user","id_functionality=".$f[0]["id"]);
    foreach($ups as $up)
      $comIdList[] = $up["id_user"];
    if (!empty($comIdList)) {
      $comList = BOUser::get("id, name, login, email, phone","id in (".implode(",",$comIdList).")");
    }
  }*/
  
?>
<script src="../js/ManagerFunctions.js" type="text/javascript"></script>
<link rel="stylesheet"  type="text/css" href="style.css" />
<link rel="stylesheet"  type="text/css" href="leads.css" />
<?php echo '<script type="text/javascript" src="../js/script.js"></script>'; ?>	


<div class="titreStandard">Compteurs + Filtres + Données du tableau</div>
<br/>
<div class="section">
  <div style="margin-bottom: 20px;">
    <div class="text">
     
  
  <div  style="float: left;">
	<br />
	
	<div class="blocka" style="float: right;">
		<div id="popup_window"><a href="creer_guides_achat.php">Créer un guide</a></div>		
	</div>

	
  </div>
      <div class="zero"></div>
    </div>
  </div>
	<?php
function random($universal_key) {
	$string1 = "";
	$user_ramdom_key = "1234567890";
	srand((double)microtime()*time());
	for($i=0; $i<$universal_key; $i++) {
	$string1 .= $user_ramdom_key[rand()%strlen($user_ramdom_key)];
	}
	return $string1;
}

   if (isset($_POST['id_families'])){
	  
	   	$key_1     		= random(10);
		$key_2   		= random(10);
		$target_path = "/data/technico-test/www/fr/ressources/images/guides/";
	$validextensions = array("jpeg", "jpg", "png");
    $ext = explode('.', basename($_FILES['adress_picture']['name']));
    $file_extension = end($ext);                     
    $name_adress_picture    =    $key_1;
    $name_adress_picture    =    $name_adress_picture.$key_2;
    $name_adress_picture    =    $name_adress_picture.'.';
    $name_adress_picture    =    $name_adress_picture.$ext[count($ext) - 1];
	    if (move_uploaded_file($_FILES['adress_picture']['tmp_name'], $target_path.$name_adress_picture)) {
			$sql_img  = "SELECT id,path_img FROM guides_visuel WHERE id_famille='".$_POST['id_families']."' ";
			$req_img  =  mysql_query($sql_img);
			$data_img =  mysql_fetch_object($req_img);
			$photo_facade_path    = $name_adress_picture;
			if(empty($data_img->path_img)){
				if(empty($data_img->id)){
				$sql_insert  = "INSERT INTO `guides_visuel` (
											`id` ,
											`id_famille` ,
											`path_img`
											)
											VALUES (
											NULL ,  '".$_POST['id_families']."',  '$photo_facade_path')";
				mysql_query($sql_insert);
				}else {
					$sql_update = "UPDATE `guides_visuel` SET `path_img`='".$photo_facade_path."' WHERE id_famille='".$_POST['id_families']."' ";
					mysql_query($sql_update);
				}
			}else{				
				$sql_update = "UPDATE `guides_visuel` SET `path_img`='".$photo_facade_path."' WHERE id_famille='".$_POST['id_families']."' ";
				mysql_query($sql_update);
			}
		}
   } 
?>
  <div id="result_forms"></div>
  
</div>

<script src="script_guide.js"></script>
<script>
	$(document).ready(function() {
			show_vpc_table_famille();
	});
</script>
<?php } ?>
<?php require(ADMIN."tail.php") ?>


