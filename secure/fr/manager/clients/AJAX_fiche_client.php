<?php
if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

function string_to_url($string) {
     $search = array('à', 'ä', 'â', 'é', 'è', 'ë', 'ê', 'ï', 'ì', 'î', 'ù', 'û', 'ü', 'ô', 'ö', '&', ' ', '?', '!', 'ç', ';', '/');
     $replace = array('a', 'a', 'a', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'u', 'u', 'u', 'o', 'o', '', '-', '', '', 'c', '', '-');
     return urlencode(str_replace($search, $replace, strtolower($string)));
}

$handle = DBHandle::get_instance();
  
				$client_id = $_GET['client_id'];
				$sql_verify = "SELECT id,societe,nom,prenom,adresse,cp,
									  ville,pays,tel,email,site_web,logo,etat,secteur,question_activite
							   FROM annuaire_client 
							   WHERE client_id='".$client_id."' ";
				$req_verify = mysql_query($sql_verify);
				$rows_verify= mysql_num_rows($req_verify);
				

	if($rows_verify > 0){
	$data_get_client = mysql_fetch_object($req_verify);
	
	$url_final = string_to_url($data_get_client->societe);
?>
<br>
<div class="bg" style="overflow:hidden;">
	  <div class="block" style="overflow:hidden;">
		<div class="title">Données fiches utilisateur </div>
		<div style="padding:10px">
		
		
		<div class="epace_bottom">
			<label><strong>Promouvoir cet société dans l’annuaire utilisateur : </strong></label>
			
		<?php
		if($data_get_client->etat == 1){
				echo 'Oui : <input type="radio" checked name="promouvoir_societe" id="promouvoir_societe_on" value="1" />
					  Non : <input type="radio" name="promouvoir_societe" id="promouvoir_societe_off" value="0" />';
			}else{
				echo 'Oui : <input type="radio" name="promouvoir_societe" id="promouvoir_societe_on" value="1" />
					  Non : <input type="radio" checked name="promouvoir_societe" id="promouvoir_societe_off" value="0" />';
			}
		?>
		</div>	
					<div id="coordonnees">
					<div class="titreBloc">Coordonnées</div>
					<input type="hidden" id="client_id" value="<?= $client_id ?>" />
						<div style="padding: 10px;">
							<div class="forms_societe">
							<div id="result_forms">
								<div class="epace_bottom">
									<label><strong>Société : </strong></label>
									<span class="margin-form"><?= $data_get_client->societe ?></span>
									<span id="update_forms_societe"><input type="text" class="margin-form_aac" id="societe_up" value="<?= $data_get_client->societe ?>" /></span>
								</div>
								
								<div class="epace_bottom">
									<label><strong>Secteur : </strong></label>
									<span class="margin-form"><?= $data_get_client->secteur ?></span>
									<span id="update_forms_secteur">
									<select id="secteur_up" class="margin-form_aac">
										<?php
											$sql_sector = "SELECT sector FROM activity_sector";
											$req_sector = mysql_query($sql_sector);
											while($data_sector = mysql_fetch_object($req_sector)){
												if($data_sector->sector == $data_get_client->secteur) $selected=" selected='true' ";
												else $selected=" ";
												echo '<option value="'.$data_sector->sector.'" '.$selected.' >'.$data_sector->sector.'</option>';
											}
										?>
									</select>
									</span>
								</div>
								
								<div class="epace_bottom">
									<label><strong>Nom     : </strong></label>
									<span class="margin-form"><?= $data_get_client->nom ?></span>
									<span id="update_forms_nom"><input type="text" class="margin-form_aac" id="nom_up" value="<?= $data_get_client->nom ?>" /></span>
								</div>
								
								<div class="epace_bottom">
									<label><strong>Prénom  : </strong></label>
									<span class="margin-form"><?= $data_get_client->prenom ?></span>
									<span id="update_forms_prenom"><input type="text" class="margin-form_aac" id="prenom_up" value="<?= $data_get_client->prenom ?>" /></span>
								</div>
								
								<div class="epace_bottom">
									<label><strong>Adresse  : </strong></label>
									<span class="margin-form"><?= $data_get_client->adresse ?></span>
									<span id="update_forms_adresse"><input type="text" class="margin-form_aac" id="adresse_up" value="<?= $data_get_client->adresse ?>" /></span>
								</div>
								
								
								<div class="epace_bottom">
									<label><strong>Email   : </strong></label>
									<span class="margin-form"><?= $data_get_client->email ?></span>
									<span id="update_forms_email"><input type="text" class="margin-form_aac" id="email_up" value="<?= $data_get_client->email ?>" /></span>
								</div>
								
								<div class="epace_bottom">
									<label><strong>Tel     : </strong></label>
									<span class="margin-form"><?= $data_get_client->tel ?></span>
									<span id="update_forms_tel"><input type="text" class="margin-form_aac" id="tel_up" value="<?= $data_get_client->tel ?>" /></span>
								</div>
								
								<div class="epace_bottom">
									<label><strong>Code Postal : </strong></label>
									<span class="margin-form"><?= $data_get_client->cp ?></span>
									<span id="update_forms_cp"><input type="text" class="margin-form_aac" id="cp_up" value="<?= $data_get_client->cp ?>" /></span>
								</div>
								
								<div class="epace_bottom">
									<label><strong>Ville   : </strong></label>
									<span class="margin-form"><?= $data_get_client->ville ?></span>
									<span id="update_forms_ville"><input type="text" class="margin-form_aac" id="ville_up" value="<?= $data_get_client->ville ?>" /></span>
								</div>
								
								<div class="epace_bottom">
									<label><strong>Pays    : </strong></label>
									<span class="margin-form"><?= $data_get_client->pays ?></span>
									<span id="update_forms_pays"><input type="text" class="margin-form_aac" id="pays_up" value="<?= $data_get_client->pays ?>" /></span>
								</div>
								
								<div class="epace_bottom">
									<label><strong>Site web    : </strong></label>
									<span class="margin-form"><?= $data_get_client->site_web ?></span>
									<span id="update_forms_site_web"><input type="text" class="margin-form_aac" id="site_web_up" value="<?= $data_get_client->site_web ?>" /></span>
								</div>
							</div>

							
								<div class="btn-update" id="change_text">[ Modifier ]  </div>
								<div class="btn-update" id="valider" style="display:none">[ Valider ]  </div>
								<div class="btn-update" id="btn-annuler" style="display:none">  [ Annuler ]</div>
								<br />
							</div>
						</div>
					</div>
					
					<div>
						<div id="activite">
							<input type="hidden" id="id_quest" value="<?= $data_get_client->id ?>" />
							<div class="titreBloc">Coordonnées</div>
							<div id="result_valide">
								<div class="epace_bottom" style="padding: 10px;">
										<label><strong>Activité   : </strong></label>
										
										<span class="margin-form_aac" id="activite_txt">
											<?= nl2br($data_get_client->question_activite) ?>
										</span>
										
										<span id="update_forms_activite" style="display:none;">
											<textarea type="text" class="margin-form_aac" id="activite_txt_get"><?= $data_get_client->question_activite ?></textarea>
										</span>
								</div>
								<div id="change_text_activite" class="btn-update-ff">[ Modifier ] </div>
								<div id="btn-valider-active" class="btn-update-ff" style="display:none"> [ Valider ]</div>
								<div id="btn-annuler-active" class="btn-update-ff" style="display:none"> [ Annuler ]</div>
							</div>
						</div>
					</div>
					
					<div id="img_logo" style="overflow: hidden; margin-bottom: 10px;">
						<?php
							if(!empty($data_get_client->logo)){
								echo '<div style="float: left; overflow: hidden;"><img src="'.URL.$data_get_client->logo.'" style="width: 90px;" /></div>
								<div onclick="delete_image_logo('.$data_get_client->id.')" style="cursor: pointer;">
								<img width="20px" src="../images/DeleteRed.png" alt="">
							</div>
								';
							}
						?>
						
					</div>
					<div>
					<a href="<?= URL?>utilisateurs/<?= $data_get_client->id ?>-<?= $client_id ?>-<?=$url_final?>.html" target="_blink">Voir sa fiche utilisateur</a>
					</div>
					</div>
			</div>
			</div>
			
   <script>
	$('#change_text_activite').click(function() {
		$("#activite_txt").hide();
		$("#change_text_activite").hide();
		$("#btn-annuler-active").show();
		$("#btn-valider-active").show();
		$("#update_forms_activite").show();
	});
	
	$('#btn-annuler-active').click(function() {
		$("#activite_txt").show();
		$("#change_text_activite").show();
		$("#btn-annuler-active").hide();
		$("#btn-valider-active").hide();
		$("#update_forms_activite").hide();
		
    });
	
	$('#btn-valider-active').click(function() {
		var id_quest = $("#id_quest").val();
		var question_activite = $("#activite_txt_get").val();
		
		var textAreaString = question_activite.replace(/\n\r/g,"<br />");
		var textAreaString = question_activite.replace(/\n/g,"<br />");
		
		$.ajax({
				url: 'annuaire_ajax.php?id_quest='+id_quest+'&action=valide_change_activite&question_activite='+textAreaString,
				type: 'GET',
				success:function(data){
					$("#result_valide").html(data);
				}
		});
	});
	
	function delete_image_logo(id){
		if(confirm("Etes vous sur de supprimer le logo !")){
			$.ajax({
				url: 'annuaire_ajax.php?action=delete_image_logo&id='+id,
				type: 'GET',
				success:function(data){
					$("#img_logo").empty();
				}
			});			
		}
	}
	
	$(".btn-update").click(function() {
		$(".margin-form").hide();
		$("#update_forms_societe").show();
		$("#update_forms_secteur").show();
		$("#update_forms_nom").show();
		$("#update_forms_prenom").show();
		$("#update_forms_email").show();
		$("#update_forms_adresse").show();
		$("#update_forms_tel").show();
		$("#update_forms_cp").show();
		$("#update_forms_ville").show();
		$("#update_forms_pays").show();
		$("#update_forms_site_web").show();
		$("#btn-annuler").show();
		$("#change_text").hide();
		$("#valider").show();
	});
	
	$( "#btn-annuler" ).click(function() {
		$(".margin-form").show();
		$("#update_forms_societe").hide();
		$("#update_forms_secteur").hide();
		$("#update_forms_nom").hide();
		$("#update_forms_prenom").hide();
		$("#update_forms_email").hide();
		$("#update_forms_adresse").hide();
		$("#update_forms_tel").hide();
		$("#update_forms_cp").hide();
		$("#update_forms_ville").hide();
		$("#update_forms_pays").hide();
		$("#update_forms_site_web").hide();
		$("#btn-annuler").hide();
		$("#valider").hide();
		$("#change_text").show();
	});
	
	$( "#valider" ).click(function() {
		var client_id = $("#client_id").val();
		var societe   = $("#societe_up").val();
		var secteur   = $("#secteur_up").val();
		var nom       = $("#nom_up").val();
		var prenom    = $("#prenom_up").val();
		var adresse   = $("#adresse_up").val();
		var email     = $("#email_up").val();
		var tel       = $("#tel_up").val();
		var cp        = $("#cp_up").val();
		var ville     = $("#ville_up").val();
		var pays      = $("#pays_up").val();
		var site_web  = $("#site_web_up").val();
		
		$.ajax({
				url: 'AJAX_update_client.php?client_id='+client_id+'&societe='+societe+"&secteur="+secteur+"&nom="+nom+"&prenom="+prenom+"&email="+email+"&tel="+tel+"&cp="+cp+"&ville="+ville+"&pays="+pays+"&adresse="+adresse+"&site_web="+site_web,
				type: 'GET',
				success:function(data){
					$("#btn-annuler").hide();
					$("#change_text").show();
					$("#valider").hide();
					$('#result_forms').html(data);
				}
		});
	});
	
	
	$('#promouvoir_societe_on').click(function() {
	 var val = $('input:radio[name=promouvoir_societe]:checked').val();
     var id_client = $("#client_id").val();
	 	$.ajax({
				url: 'annuaire_ajax.php?value_radio='+val+'&id_client='+id_client+'&action=params_soceite',
				type: 'GET',
				success:function(data){
				
				}
		});
  });
  
    $('#promouvoir_societe_off').click(function() {
	 var val = $('input:radio[name=promouvoir_societe]:checked').val();
	  var id_client = $("#client_id").val();
	  alert(id_client);
	 	$.ajax({
				url: 'annuaire_ajax.php?value_radio='+val+'&id_client='+id_client+'&action=params_soceite',
				type: 'GET',
				success:function(data){
				
				}
		});
  });
</script>
	<?php } ?>
	
<style>	
#coordonnees {
    border: 1px solid #ddd;
    float: left;
    margin-right: 40px;
    width: 300px;
}

#activite {
    border: 1px solid #ddd;
    float: left;
    margin-right: 40px;
    width: 340px;	
}

#form_activite_produit {
    overflow: hidden;
}
.forms_activite > div {
    font-weight: bold;
}
.forms_activite input {
    height: 23px;
    width: 205px;
}
#text-presentation {
    margin-bottom: 25px;
}
#preview_facade {
    width: 100px;
}

#valider_modif {
    color: #0071bc;
    cursor: pointer;
    font-weight: bold;
    margin-bottom: 10px;
    overflow: hidden;
}

#annuler_modif {
    color: #0071bc;
    cursor: pointer;
    font-weight: bold;
    margin-bottom: 10px;
    overflow: hidden;
}

#preview_facade_produit0, #preview_facade_produit1, #preview_facade_produit2, #preview_facade_produit3, #preview_facade_produit4, #preview_facade_produit5, #preview_facade_produit6, #preview_facade_produit7, #preview_facade_produit8, #preview_facade_produit9, #preview_facade_produit10 {
    width: 100px;
}
.forms_activite {
    margin-top: -3px;
}
.btn-update {
    color: #0071bc;
    cursor: pointer;
    float: right;
    font-weight: bold;
    overflow: hidden;
	margin-bottom : 10px;
}

.btn-update-ff {
    color: #0071bc;
    cursor: pointer;
    float: right;
    font-weight: bold;
    overflow: hidden;
	margin-bottom : 10px;
}

.epace_bottom {
    margin-bottom: 5px;
	 overflow: hidden;
}
#pdt_photo {
    float: left;
    margin-right: 40px;
}
.forms_question {
    overflow: hidden;
}
.froms_bottom {
    margin: auto;
    padding: 35px;
    width: 508px;
}
.margin-form{
    display: inline;
    float: right;
    width: 200px;
}
.margin-form_aac{
	float: right;
	width:265px;
	height : 30px !important;
}

#update_forms_societe, #update_forms_secteur, #update_forms_nom, #update_forms_prenom, #update_forms_email, #update_forms_tel, #update_forms_cp, #update_forms_ville, #update_forms_pays,#update_forms_adresse,#update_forms_site_web {
    display: none;
}
.border_users {
    border: 1px solid #ddd;
    margin-bottom: 10px;
    overflow: hidden;
    padding: 10px;
}

	
</style>