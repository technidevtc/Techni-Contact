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

<div class="titreStandard">Gestion des guides d'achat Techni-Contact</div>
<br/>
<div class="section">
   
<div id="result"></div>
	
  <div id="Content_guide">
	<form action="controller/class.controller.php?action=create_guide" method="POST">
	<input type="hidden" name="id_famille" id="id_famille" value=""  />
	<input type="hidden"   id="first_famille" value=""  />
	<input type="hidden"   id="nbr_families" value="0"  />
	<input type="hidden"  name="id_first_famille" id="id_first_famille" value=""  />
	<div>
		<div id="left-form-guide">
			
			<div><label>Nom thématique </label><input type="text" name="name_guide" style="width: 180px;height: 20px;" required /></div> <br />
			<div><label>Titre du guide (H1)  </label><input type="text" name="titre_guide"  style="width: 180px;height: 20px;" required/></div> <br />
			<div><label>Meta title </label><input type="text" name="meta_title" style="width: 250px;height: 20px;" required/></div> <br />
			<div><label>Meta description  </label><textarea name="meta_desc" rows="4" cols="32" style="margin-left: 0px;    width: 245px;" required></textarea></div> <br />
		</div>
		<div id="right-form-guide">
			<div class="right-dynamic-autocomplete"><label>Choix de la famille  </label><br /><br>
			<div class="input_container"><input type="text" value="" style="width: 195px;height: 20px;" onkeyup="autocomplet()" id="familles_id" />
				<ul id="familles_list_id"></ul>
				</div>
			</div>
			<div id="first-famile"></div>
			<div class="right-dynamic-familles" style="display:none;">
				
			</div>
		</div>
	</div>
	<div style="clear: both;">	
		<div><label>Guide  </label>
			<textarea name="desc" id="desc"></textarea>
		</div>
	</div>
	<br /><br />
	<div><center><input type="submit" class="bouton" value="Valider" /></center></div>
</form>
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

