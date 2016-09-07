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
	<form action="controller/class.controller.php?action=update_article" method="POST">
	
	<div>
		<div id="left-form-guide">
			<?php
				$id_article   = $_GET['id'];
				$sql_article  = "SELECT id,article_title,timestamp_created,timestamp_updated,statut,
										title_meta,desc_meta,content,bo_user_updated
								 FROM   `blog_articles`
								 WHERE   id='".$id_article."' ";
				$req_article  =  mysql_query($sql_article);
				$data_article =  mysql_fetch_object($req_article);
				
				if($data_article->timestamp_updated == "0000-00-00 00:00:00"){
					$date_modif = " - ";
				}else{
					$date_modif = date("d/m/Y", strtotime($data_article->timestamp_updated));
				}
				
				$sql_user  = "SELECT name
							  FROM bo_users
							  WHERE id='".$data_article->bo_user_updated."' ";
				$req_user   = mysql_query($sql_user);
				$data_user  = mysql_fetch_object($req_user);
				
			?>
			
			<div>
				<div><b>Crée le :<?= date("d/m/Y", strtotime($data_article->timestamp_created)) ?></b></div>
				<div><b>Modifié le :<?= $date_modif ?></b></div>
				<div><b>Dernière mofid par : <?= $data_user->name ?></b></div>
			</div>
			<br />
			<div><label>Titre du guide (H1)  </label><input type="text" name="titre_article" value="<?= $data_article->article_title?>"  style="width: 180px;height: 20px;" required/></div> <br />
			<div><label>Meta title </label><input type="text" name="meta_title" style="width: 250px;height: 20px;"value="<?= $data_article->title_meta?>" required/></div> <br />
			<div><label>Statut </label>
								<select style="width: 250px;height: 20px;" name="statut" required>
									<?php
										if($data_article->statut == '1'){
											echo '<option value="1">Publié</option>
												  <option value="0">Brouillon</option>';
										}else{
											echo '<option value="0">Brouillon</option>
												  <option value="1">Publié</option>';
										}
									?>									
								</select>
			</div> <br />
			<div><label>Meta description  </label><textarea name="meta_desc" rows="4" cols="32" style="margin-left: 0px;    width: 245px;" required><?= $data_article->desc_meta ?> </textarea></div> <br />
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
				<div id="tag_selected">
					<?php
						$sql_tags_selected  =  "SELECT btn.name ,btn.id
											    FROM blog_tags_linked_articles btla, blog_tags_names btn 
											    WHERE id_article='".$id_article."'
											    AND btla.id_tag = btn.id
												ORDER BY name ASC ";
						
						$req_tags_selected  =   mysql_query($sql_tags_selected);
						$var_id_tags_selected = "";
						while($data_tags_selected = mysql_fetch_object($req_tags_selected)){
							echo '<div style="overflow: hidden;margin-bottom: -3px;" id="delete_tag_html_'.$data_tags_selected->id.'">
								   <div style="float:left;margin-top: 5px;" class="des_label">'.$data_tags_selected->name.'</div>
									<div style=" float: right;    margin-top: -6px;">
										<img src="images/supprimer-vide-ordures-corbeille-corbeille-icone-5257-96.png" width="35px" onclick="delete_tag_html('.$data_tags_selected->id.')" style="cursor: pointer;" />
									</div>
								  </div>';
						    $var_id_tags_selected .="-".$data_tags_selected->id;
						}
					?>
					<input type="hidden" value="<?= $var_id_tags_selected ?>" name="keyUp-enter"  id="keyUp-enter"/>
					<input type="hidden" value="<?= $id_article ?>" name="id_articles" />
				</div>
			</div>
			
		</div>
	</div>
	<div style="clear: both;">	
		<div><label>Contenu de l'article  </label>
			<textarea name="desc" id="desc"><?= $data_article->content ?></textarea>
		</div>
	</div>
	<br /><br />
	<div><center><input type="submit" class="bouton" value="Valider" /></center></div>
</form>
<script src="script_blog.js"></script>
<script>
CKEDITOR.config.enterMode = CKEDITOR.ENTER_BR;
var editor = CKEDITOR.replace('desc');
CKFinder.setupCKEditor( editor, '../ckfinder/' );ki
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

