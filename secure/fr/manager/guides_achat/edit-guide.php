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
}else {
?>

<script src="../js/ManagerFunctions.js" type="text/javascript"></script>
<link rel="stylesheet"  type="text/css" href="style.css" />
<link rel="stylesheet"  type="text/css" href="guide.css" />
<link rel="stylesheet"  type="text/css" href="css/style_autoc.css" />
<?php echo '<script type="text/javascript" src="../js/script.js"></script>'; ?>	
<link rel="stylesheet" href="box.css">
<script type="text/javascript" src="../ckeditor_new/ckeditor.js"></script>
<script type="text/javascript" src="../ckfinder/ckfinder.js"></script>

<div class="titreStandard">Gestion des guides d'achat Techni-Contact</div>
<br/>
<div class="section">
   
<div id="result"></div>
	<?php
		$id_guide  = $_GET['id'];
		$sql_guide = "SELECT 	`id`, `id_famille_parent`, `guide_name`, 
								`title_h`, `title_meta`, `desc_meta`, `quide_content`, 
								`ref_name`, `create_date`, `update_date`, `user_bo_create`, `user_bo_update`
					  FROM guides_achat 
					  WHERE id='".$id_guide."' ";
		$req_guide =  mysql_query($sql_guide);
		$data_guide=  mysql_fetch_object($req_guide);
		
		$sql_dyn = "SELECT id,id_familles_three FROM guides_linked_familles WHERE id_guide='".$data_guide->id."'";
		$req_dyn =  mysql_query($sql_dyn);  
		$data_dyn = mysql_fetch_object($req_dyn);
		
		$sql_count_f3  = "SELECT id,id_familles_three FROM guides_linked_familles WHERE id_guide='".$data_guide->id."' ";
		$req_count_f3  =  mysql_query($sql_count_f3);
		$rows_count_f3 =  mysql_num_rows($req_count_f3);
		
		if($_GET['update_guide'] == 'success'){
			echo '<div class="alert-box success">Modification effectuée avec succès</div>';
		}
	
	?>	
  
  <div id="Content_guide">
  
	<form action="controller/class.controller.php?action=update_guide" method="POST">
	
	<input type="hidden"   id="first_famille" value=""  />
	<input type="hidden"   id="nbr_families" value="<?= $rows_count_f3 ?>"  />
	<input type="hidden"   id="delete_families" name="delete_families" value=""  />
	<input type="hidden"   id="id_guide" name="id_guide" value="<?= $id_guide ?>"  />
	<input type="hidden"  name="id_first_famille" id="id_first_famille" value="<?= $data_guide->id_famille_parent ?>"  />
	<input type="hidden"  id="id_three_famille3" value="<?= $data_dyn->id_familles_three ?>"  />
	<div>
		<div id="left-form-guide">
			
			<div><label>Nom thématique  </label>
				<input type="text" name="name_guide" style="width: 180px;height: 20px;" value="<?= $data_guide->guide_name ?>" required />
			</div> <br />
			
			<div><label>Titre du guide(H1)  </label>
				<input type="text" name="titre_guide"  style="width: 180px;height: 20px;" value="<?= $data_guide->title_h ?>" required/>
			</div> <br />
			
			<div><label>Meta title </label>
				<input type="text" name="meta_title" style="width: 250px;height: 20px;" value="<?= $data_guide->title_meta ?>" required/>
			</div> <br />
			
			<div><label>Meta description  </label>
				<textarea name="meta_desc" rows="4" cols="32" style="margin-left: 0px;    width: 245px;" required><?= $data_guide->desc_meta ?></textarea>
			</div> <br />
		</div>
		<div id="right-form-guide">
			<div class="right-dynamic-autocomplete"><label>Choix de la famille  </label><br /><br>
			<div class="input_container"><input type="text" value="" style="width: 195px;height: 20px;" onkeyup="autocomplet()" id="familles_id" />
				<ul id="familles_list_id"></ul>
				</div>
			</div>
			
			<?php
				$sql_fist_families  = "SELECT name FROM families_fr WHERE id='".$data_guide->id_famille_parent."' ";
				$req_fist_families  =  mysql_query($sql_fist_families);
				$data_fist_families =  mysql_fetch_object($req_fist_families);
			?>
			<div id="first-famile"><strong>Famille 1 : </strong><?= $data_fist_families->name ?> </div>
			<div class="right-dynamic-familles" >
				<?php
				
					while($data_count_f3 =  mysql_fetch_object($req_count_f3)){
						$id_f3 .= '-'.$data_count_f3->id_familles_three;
						
						$sql_families_f3_name  =  "SELECT name FROM families_fr WHERE id='".$data_count_f3->id_familles_three."' ";
						$req_families_f3_name  =   mysql_query($sql_families_f3_name);
						$data_families_f3_name =   mysql_fetch_object($req_families_f3_name);
						echo '<div id="famille_dynamic_'.$data_count_f3->id_familles_three.'" style="overflow: hidden;margin-bottom:7px;">
							  <div>
								<div style="float: left;"> '.$data_families_f3_name->name.'</div>
									<div style="float: right;">
									<img src="images/delete-icon-ie6.png" onclick="delete_div_update(\''.$data_count_f3->id_familles_three.'\',\''.$data_families_f3_name->name.'\')" style="cursor: pointer;">
									</div><br>
							</div>
							</div>';
					}
				
				?>
			</div>
			<input type="hidden" name="id_famille" id="id_famille" value="<?= $id_f3 ?>"  />
		</div>
	</div>
	
	
	
	<div style="clear: both;">
		<div style="float: right;margin-top: -35px;margin-right: 2px;">
			<a href="<?= URL ?>guides-achat/<?= $id_guide ?>-<?= $data_guide->ref_name ?>.html" class="bouton" target="_blink">
				Voir le guide en ligne
			</a>	
		</div>
		<div><label>Guide  </label>
			<textarea name="desc" id="desc"><?= $data_guide->quide_content ?></textarea>
		</div>
	</div>
	<br /><br />
	<div><center><input type="submit" class="bouton" value="Valider" />
				<a href="index.php" class="bouton" >Retour</a>
	</center></div>
</form>
<script>
CKEDITOR.config.enterMode = CKEDITOR.ENTER_BR;
var editor = CKEDITOR.replace('desc');
CKFinder.setupCKEditor( editor, '../ckfinder/' );
	//initSample();
</script>
	
</div>
  <script>
$(document).ready(function(){ 
	var id_first_famille = $("#id_three_famille3").val();
	$.ajax({		
		url: 'controller/class.controller.php?action=autocomplate_dynamic&id_famille='+id_first_famille,
		type: 'GET',
		success:function(data){
			$("#first_famille").val(data);
		}
	});
});

</script>


<script src="script_guide.js"></script>

<?php } ?>
<?php require(ADMIN."tail.php") ?>
<style>
#cke_desc {
	overflow:hidden;
}
</style>

