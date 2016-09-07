<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
$db = DBHandle::get_instance();



$title = $navBar = "Rédaction d'un article de blog";
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
<link rel="stylesheet"  type="text/css" href="guide.css" />
<link rel="stylesheet"  type="text/css" href="css/style_autoc.css" />
<?php echo '<script type="text/javascript" src="../js/script.js"></script>'; ?>	

<!--
<script src="../ckeditor_new/ckeditor.js"></script>
<script type="text/javascript" src="../ckfinder/ckfinder.js"></script>
<script src="js/sample.js"></script>
-->

<script type="text/javascript" src="../ckeditor_new/ckeditor.js"></script>
<script type="text/javascript" src="../ckfinder/ckfinder.js"></script>

<div class="titreStandard">Rédaction d'un article de blog</div>
<br/>
<div class="section">
   
<div id="result"></div>
	
  <div id="Content_guide">
	<form action="controller/class.controller.php?action=create_article" method="POST" enctype="multipart/form-data">
	<input type="hidden" value="" name="keyUp-enter"  id="keyUp-enter"/>
	<div>
		<div id="left-form-guide">
			
			<div><label>Titre du guide (H1)  </label><input type="text" name="titre_article"  style="width: 180px;height: 20px;" required/></div> <br />
			<div><label>Meta title </label><input type="text" name="meta_title" style="width: 250px;height: 20px;"/></div> <br />
			<div><label>Statut </label>
								<select style="width: 250px;height: 20px;" name="statut" required>
									<option value="0">Brouillon</option>
									<option value="1">Publié</option>
									
								</select>
			</div> <br />
			<div><label>Meta description  </label><textarea name="meta_desc" rows="4" cols="32" style="margin-left: 0px;    width: 245px;"></textarea></div> <br />
			
			<div>
			<label>Image promotionnelle </label>
			<div>
				<div id="id_photo_facade">
					<img id="preview_facade" src="images/imgres.jpg" style="width: 165px;" /><br />
					<input type="file" name="adress_picture" id="photo_facade" onChange="fileSelected(this.id);" style="margin-left: 242px;" data-buttonText="Your label here." />
				</div>	
			</div>
			</div> 
			<br />
		</div>
		
		<div id="right-form-guide">
			<div class="first-right">
				<label>Tags (double cliquez pour séléctionner)  </label><br /><br>
				<div class="list_tags">
					<?php
						$sql_tags  =  "SELECT id,name FROM blog_tags_names ORDER BY name ASC";
						$req_tags  =   mysql_query($sql_tags);
						while($data_tags = mysql_fetch_object($req_tags)){
							echo '<div id="dbl_clicl_event_'.$data_tags->id.'" onclick="double_click_add_article('.$data_tags->id.')">
									<div class="des_label" id="des_label_'.$data_tags->id.'" >'.$data_tags->name.'</div>
								  </div>';
						}
					?>
				</div>
			</div>
			<div id="twoo-right">
				<label>Tags séléctionnés </label><br /><br>
				<div id="tag_selected"></div>
			</div>
			
		</div>
	</div>
	<div style="clear: both;">	
		<div><label>Contenu de l'article  </label>
			<textarea name="desc" id="desc"></textarea>
		</div>
	</div>
	<br /><br />
	<div><center><input type="submit" class="bouton" value="Valider" /></center></div>
</form>
<script src="script_blog.js"></script>
<script>
CKEDITOR.config.enterMode = CKEDITOR.ENTER_BR;
var editor = CKEDITOR.replace('desc');
CKFinder.setupCKEditor( editor, '../ckfinder/' );
	//initSample();
</script>
	
	
	
  </div>
  


<script src="script_guide.js"></script>

</script>
<?php } ?>
<?php require(ADMIN."tail.php") ?>
<style>
#cke_desc {
	overflow:hidden;
}
</style>

