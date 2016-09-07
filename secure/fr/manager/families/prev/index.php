<?php
if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

//require(ICLASS."Command.php");
require(ADMIN."statut.php");

$title = $navBar = "Gestion des Familles";
require(ADMIN."head.php");

$db = DBHandle::get_instance();

$families = array(); // 0 => name, 1 => ref_name, 2 => idParent, 3 => nbchildren, 4 => children list
$families[0] = array("","",0);

$res = $db->query("	SELECT 
						f.id, f.idParent, 
						fr.name, fr.ref_name, 
						fr.title, fr.meta_desc, 
						fr.text_content, 
						(
							SELECT
								count(product_f.idProduct)
							FROM
								products_families product_f
							WHERE
								product_f.idFamily=f.id	
						)

							AS countproduct
					FROM 
						families f, 
						families_fr fr 
						
					WHERE 
						f.id = fr.id", __FILE__, __LINE__);
						
while ($family = $db->fetchAssoc($res)) {
	$families[$family["id"]]["name"] = $family["name"];
	$families[$family["id"]]["ref_name"] = $family["ref_name"];
	$families[$family["id"]]["idParent"] = $family["idParent"];
	$families[$family["id"]]["title"] = $family["title"];
	$families[$family["id"]]["meta_desc"] = $family["meta_desc"];
	$families[$family["id"]]["text_content"] = $family["text_content"];
	
	$families[$family["id"]]["countproduct"] = $family["countproduct"];
	if (!isset($families[$family["idParent"]]["nbchildren"]))
		$families[$family["idParent"]]["nbchildren"] = 1;
	else
		$families[$family["idParent"]]["nbchildren"]++;
	$families[$family["idParent"]]["children"][$families[$family["idParent"]]["nbchildren"]-1] = $family["id"];
}

?>
<link rel="stylesheet" type="text/css" href="Families.css" />
<script type="text/javascript">
var families = new Array();

//Declaration variables en Javascript pour faire la matrice tableau familles value childrens
//var name = 0; var ref_name = 1; var idParent = 2; var title = 3; var meta_desc = 4; var text_content = 5; var nbchildren = 6; var children = 7;
var name = 0; var ref_name = 1; var idParent = 2; var title = 3; var meta_desc = 4; var text_content = 5; var countproduct = 6; var nbchildren = 7; var children = 8;
var __SID__ = "<?php echo $sid ?>";
var __ADMIN_URL__ = "<?php echo ADMIN_URL ?>";
var __MAIN_SEPARATOR__ = "<?php echo __MAIN_SEPARATOR__ ?>";
var __ERROR_SEPARATOR__ = "<?php echo __ERROR_SEPARATOR__ ?>";
var __ERRORID_SEPARATOR__ = "<?php echo __ERRORID_SEPARATOR__ ?>";
var __OUTPUT_SEPARATOR__ = "<?php echo __OUTPUT_SEPARATOR__ ?>";
var __OUTPUTID_SEPARATOR__ = "<?php echo __OUTPUTID_SEPARATOR__ ?>";
var __DATA_SEPARATOR__ = "<?php echo __DATA_SEPARATOR__ ?>";

$(function(){
  $("#btn-update-FO").click(function(){
    $.ajax({
      async: true,
      cache: false,
      data: "action=updateFO",
      dataType: "json",
      error: function (XMLHttpRequest, textStatus, errorThrown) { alert("Echec de la mise à jour du Front Office"); },
      success: function (data, textStatus) { alert("Front Office mis à jour avec succés"); },
      timeout: 10000,
      type: "GET",
      url: "AJAX_pdt-selection.php"
    });
    return false;
  });
});

<?php
$order   = array("\r\n", "\n", "\r");
foreach ($families as $id => $fam) {

	print "families[".$id."] = [\"".str_replace('"','\"',$fam["name"])."\",\"".$fam["ref_name"]."\",".$fam["idParent"].",\"".str_replace('"','\"',$fam["title"])."\",\"".str_replace('"','\"',$fam["meta_desc"])."\",\"".str_replace(array('"',"\r","\n"),array('\"',"\\r","\\n"),$fam["text_content"])."\",\"".str_replace(array('"',"\r","\n"),array('\"',"\\r","\\n"),$fam["countproduct"])."\",";
	//print "families[".$id."] = [\"".str_replace('"','\"',$fam["name"])."\",\"".$fam["ref_name"]."\",".$fam["idParent"].",\"".str_replace('"','\"',$fam["title"])."\",\"".str_replace('"','\"',$fam["meta_desc"])."\",\"".str_replace(array('"',"\r","\n"),array('\"',"\\r","\\n"),$fam["text_content"])."\",";
	if (isset($fam["nbchildren"]))
		print $fam["nbchildren"].",[".implode(",",$fam["children"])."]";
	else
    print "0,[]";
	print "];\n";
}

$menu_families = "";
foreach ($families[0]["children"] as $id)
	$menu_families .= "<a href=\"familles/".$id."\">".to_entities($families[$id]["name"])."</a> ";

?>
</script>
<script src="../../js/ManagerFunctions.js" type="text/javascript"></script>
<script src="families.js" type="text/javascript"></script>

<div id="attributes_values_group_dialog" title="Grouper les valeurs de la propriété"></div>

<div class="titreStandard">Gestion des familles</div>
<br />
<div class="bg">
  <div id="create_virtual_cat3_attributes_dialog" title="Création facette virtuelle"></div>
  <div id="add_product_to_virtual_attr_dialog" title="Gestion des produits de la facette"></div>

  <div id="set_cat3_selected_attributes_dialog" title="Sauvegarde navigation par facette">
    Les réglages de navigation par facette pour cette famille ont été enregistrés avec succès
  </div>
	<div id="FamiliesSearchDBShad" class="DBshad"></div>
	<div id="FamiliesSearchDB" class="DB">
		<div class="window_title_bar">
			<img class="wtb_close_img" src="../ressources/window_close.gif" onMouseDown="swap_cbtn(this,'down')" onMouseOut="swap_cbtn(this,'out')" onMouseUp="swap_cbtn(this,'up')" />			
			<div onmousedown="grab(document.getElementById('FamiliesSearchDB'), document.getElementById('FamiliesSearchDBShad'))">
				<img class="wtb_move_img" src="../ressources/window_move.gif" />
				<div class="wtb_text">Recherche d'une famille</div>
				<div class="zero"></div>
			</div>
		</div>
		<div class="window_bg_small">
			<input id="FamiliesSearchText" type="text" size="30" />
			<input type="button" class="button" style="width: 75px" value="Rechercher" onclick="FindFamilies()" /><br />
			<input id="FamiliesBeginBy" type="checkbox" class="checkbox" checked="checked" /> Commence par
			<input id="FamiliesCaseSensitive" type="checkbox" class="checkbox" /> Respecter la casse
		</div>
	</div>
	<div id="FamiliesResultsDBShad" class="DBshad"></div>
	<div id="FamiliesResultsDB" class="DB">
		<div class="window_title_bar">
			<img class="wtb_close_img" src="../ressources/window_close.gif" onMouseDown="swap_cbtn(this,'down')" onMouseOut="swap_cbtn(this,'out')" onMouseUp="swap_cbtn(this,'up')" />			
			<div onmousedown="grab(document.getElementById('FamiliesResultsDB'), document.getElementById('FamiliesResultsDBShad'))">
				<img class="wtb_move_img" src="../ressources/window_move.gif" />
				<div class="wtb_text">Résultat de la recherche</div>
				<div class="zero"></div>
			</div>
		</div>
		<div class="window_bg_small">
			<div id="FamiliesResultsError" class="InfosError"></div>
			<div id="FamiliesResults">&nbsp;</div>
		</div>
	</div>
	<a href="#" class="btn-red fr" id="btn-update-FO">Mettre à jour le FO</a>
	<div style="zoom: 1"><a href="javascript: ShowFamiliesSearchDB()">Rechercher une famille</a></div>
	<div id="PerfReqF" class="PerfReqLabel">Modification en cours...</div>
	<div id="FamiliesError" class="InfosError"></div>
	<div class="zero"></div>
	<div id="Families">
	
		<div class="families_left">
	
			
				<div id="menu"><?php echo $menu_families ?></div>
				<div id="colg">
					<div class="titre" id="colg_titre">Familles</div>
					<div class="sf" id="colg_sf"><?php echo $menu_families ?></div>
				</div>
				<div id="colc">
					<h1 id="desc">Choisissez une famille</h1>
					<form id="fam" name="fam" action="">
					</form>
			  <div id="cat3-attributes-selection" class="cat3-attributes-block">
				<button id="update-cat3-attributes-selection" class="btn ui-state-default ui-corner-all">sauvegarder</button>
				<button id="get-cat3-products" class="btn ui-state-default ui-corner-all">Télécharger l'extract des produits</button>
				<div class="title">Navigation par facettes</div>
				<table class="item-list-table" cellspacing="0" cellpadding="0">
				  <thead>
					<tr>
					  <th class="tree"></th>
					  <th class="attr">Nom attribut / Valeur</th>
					  <th class="usedCount">Produits concernés</th>
					  <th class="usedCount">Nb intervalles</th>
					  <th class="actions">Actions</th>
					</tr>
				  </thead>
				  <tbody></tbody>
				</table>
			  </div>
			  <button id="create-virtual-cat3-attributes" class="btn ui-state-default ui-corner-all">Créer facette Ad Hoc</button>
			  <div id="cat3-attributes" class="cat3-attributes-block">
				<div class="title">Attributs de la famille</div>
				<table class="item-list-table" cellspacing="0" cellpadding="0">
				  <thead>
					<tr>
					  <th class="tree"></th>
					  <th class="attr">Nom attribut / Valeur</th>
					  <th class="usedCount">Produits concernés</th>
					  <th class="usedCount">Nb intervalles</th>
					  <th class="actions">Ajouter</th>
					</tr>
				  </thead>
				  <tbody></tbody>
				</table>
			  </div>
				</div>
				
				<div class="zero"></div>
				
				<script type="text/javascript">
				var i, j, k;
				var af = document.getElementById('menu').getElementsByTagName('a');
				var af2 = document.getElementById('colg').getElementsByTagName('a');
				var cur_family_id = 0;
				for (i = 0; i < af.length; i++) {
					var af_url = af[i].href.split('/');
					af[i].family_id = af_url[af_url.length-1]; // on stock l'id de la famille lié à ce lien
					af[i].Select = function () { // lors de la sélection
						cur_family_id = this.family_id;
						this.className = 'current';
						if (this.parentNode.current_f && (this.parentNode.current_f != this)) this.parentNode.current_f.UnSelect();
						this.parentNode.current_f = this;
						
						/* Initialisation du titre et de la liste des sous-familles de niveau 2 */
						document.getElementById('colg_titre').innerHTML = families[this.family_id][name];
						var s = '';
						families[this.family_id][children].sort(sort_ref_name); // Tri par nom référence pour affichage
						
						//Contenu listes 2eme niveau !
						for (j = 0; j < families[this.family_id][nbchildren]; j++)
						{
							var child_id = families[this.family_id][children][j];
							s += '<a href="familles/' + child_id + '">' + families[child_id][name] + '</a><div class="ssf"></div>';
						}
						document.getElementById('colg_sf').innerHTML = s;
						//document.getElementById('colg_sf').current_sf = null; // variable permettant de savoir quelle ssf dépliée est en cours
						
						/* Initialisation des sous-familles de niveau 3 */
						var asf = document.getElementById('colg_sf').getElementsByTagName('a');
						var ssf = document.getElementById('colg_sf').getElementsByTagName('div');
						for (j = 0; j < asf.length; j++) // pour chaque lien construit
						{
							var asf_url = asf[j].href.split('/');
							asf[j].family_id = asf_url[asf_url.length-1]; // on stock l'id de la famille
							asf[j].ssfNext = ssf[j]; // on pointe sur le prochain div de la ssf
							
							asf[j].InitSSF = function () { // fonction de construction de la ssf lors du 1er clique
								var s = '';
								families[this.family_id][children].sort(sort_ref_name); // Tri par nom référence pour affichage
								for (k = 0; k < families[this.family_id][nbchildren]; k++)
								{
									var child_id = families[this.family_id][children][k];
									s += '<a href="familles/' + child_id + '">' + families[child_id][name] + ' ('+families[child_id][countproduct]+')</a>';
								}
								this.ssfNext.innerHTML = s;
								var assf = this.ssfNext.getElementsByTagName('a');
								for (k = 0; k < assf.length; k++)
								{
									var assf_url = assf[k].href.split('/');
									assf[k].family_id = assf_url[assf_url.length-1]; // on stock l'id de la famille
									assf[k].onclick = function () {
										cur_family_id = this.family_id;
										this.parentNode.parentNode.current_sf.className = 'notCurrentUnfolded';
										if (this.parentNode.current_ssf && (this.parentNode.current_ssf != this)) this.parentNode.current_ssf.className = '';
										this.parentNode.current_ssf = this;
										this.className = 'current';
										
										//Recherche recursive ' ' remplacer par '+'
										var fam_find 	= ' ';
										var fam_replace = new RegExp(fam_find, 'g');
										var fam_rd_link = families[this.family_id][name].replace(fam_replace, '+');
										//fam_rd_link = fam_rd_link.replace(fam_replace, '+');

										//Ajout de lien cliquable vers la page de réponses
										var fam_rd_to_print	= "Famille " + families[this.family_id][name] + " (niveau 3) ";
										fam_rd_to_print		+= '<span class="see_products"><a href="/fr/manager/search.php?search='+fam_rd_link+'&search_type=2" target="_blank">- Voir les produits de cette famille</a></span>';
										
										document.getElementById('desc').innerHTML = fam_rd_to_print;
										
										var FamiliesOptionsList = '';
										var SubFamiliesOptionsList = '';
										var idp = families[this.family_id][idParent];
										var idpp = families[idp][idParent];
										for (l = 0; l < families[0][nbchildren]; l++)
										{
											FamiliesOptionsList += '<option value="' + families[0][children][l] + '"';
											if (families[0][children][l] == idpp) FamiliesOptionsList += ' selected';
											FamiliesOptionsList += '>' + families[families[0][children][l]][name] + '</option>';
										}
										
										for (l = 0; l < families[idpp][nbchildren]; l++)
										{
											SubFamiliesOptionsList += '<option value="' + families[idpp][children][l] + '"';
											if (families[idpp][children][l] == idp) SubFamiliesOptionsList += ' selected';
											SubFamiliesOptionsList += '>' + families[families[idpp][children][l]][name] + '</option>';
										}
						
										document.getElementById('fam').innerHTML = "" +
										'<div class="line">' +
											'<div class="entitle">Changer le nom :</div>' +
											'<input class="capture" type="text" name="editNameValue" value="' + families[this.family_id][name] + '"/>' +
											'<input class="button" type="button" value="Valider" onclick="editName()" />' +
										'</div>' +
										'<div class="zero"></div>' +
										'<br />' +
										'<div class="line">' +
											'<div class="entitle">Changer la famille parente :</div>' +
											'<select class="captureS" style="margin-right: 5px" onchange="ShowSubFamiliesOptionsList(this.value, ' + idp + ',' + idpp + ')">' +
												FamiliesOptionsList + 
											'</select>' +
											'<select class="captureS" name="editParentValue">' +
												SubFamiliesOptionsList + 
											'</select>' +
											'<input class="button" type="button" value="Valider" onclick="editParent()" />' +
										'</div>' +
										'<div class="zero"></div>' +
							'<br />' +
							'<div class="line">' +
							'	<div class="entitle">URI SEO :</div>' +
							'	<input class="capture" type="text" id="editRefNameValue" value="'+families[this.family_id][ref_name]+'"/>' +
							'	<input class="button" type="button" value="Valider" onclick="editRefName()" />' +
							'</div>' +
							'<div class="zero"></div>' +
							'<br />' +
							'<div class="line">' +
							'	<div class="entitle">Titre de la page en FO :</div>' +
							'	<input class="capture" type="text" id="editTitleValue" value="'+families[this.family_id][title]+'"/>' +
							'	<input class="button" type="button" value="Valider" onclick="editTitle()" />' +
							'</div>' +
							'<div class="zero"></div>' +
							'<br />' +
							'<div class="line">' +
							'	<div class="entitle">Description de la page en FO :</div>' +
							'	<input class="capture" type="text" id="editDescValue" value="'+families[this.family_id][meta_desc]+'"/>' +
							'	<input class="button" type="button" value="Valider" onclick="editDesc()" />' +
							'</div>' +
							'<div class="zero"></div>' +
							'<br />' +
							'<div class="line">' +
							'	<div class="entitle">Texte de présentation en FO :</div>' +
							'	<textarea class="capture" id="editContentValue" >'+$("<div/>").text(families[this.family_id][text_content]).html()+'</textarea>' +
							'	<input class="button" type="button" value="Valider" onclick="editContent()" />' +
							'</div>' +
							'<div class="zero"></div>' +
										'<br />' +
										'<a href="delete" onclick="delfam(); return false">Supprimer cette sous-famille</a>';
							
				//            $("#cat3-attributes-selection, #cat3-attributes, #create-virtual-cat3-attributes").show()
				//              .find("tbody").empty().append("<tr><td colspan=\"4\">Chargement en cours...</td></tr>");
				//
				//            $.ajax({
				//              type: "POST",
				//              url: "AJAX_interface.php",
				//              data: {"actions":[/*{"action":"get_cat3_selected_attributes","cat3Id":cur_family_id}, */{"action":"get_cat3_attributes","cat3Id":cur_family_id}]},
				//              dataType: "json",
				//              error: function (XMLHttpRequest, textStatus, errorThrown) {},
				//              success: function (res, textStatus) {
				//                if (!res.error) {
				//                  var selected_list = not_selected_list = "";
				//                  for (var ai=0; ai<res.data.cat3_attributes.length; ai++) {
				//                    var attr = res.data.cat3_attributes[ai];
				//                    var virtual = attr.virtual == 1 ? ' virtual' : '';
				//                    var html = "<tr id=\"cat3_attr_"+attr.id+"\" class=\"attr"+(attr.values.length?" scat1":"")+"\">"+
				//                                 "<td></td>"+
				//                                 "<td class=\"name-value\">"+attr.name+"</td>"+
				//                                 "<td class=\"usedCount\">"+attr.usedCount+"</td>"+
				//                                 "<td class=\"usedCount\">"+attr.nbInterval+"</td>"+
				//                                 "<td class=\"actions active_"+attr.active+virtual+"\"></td>"+
				//                               "</tr>";
				//                    for (var avi=0; avi<attr.values.length; avi++) {
				//                      var attrVal = attr.values[avi];
				//                      html += "<tr id=\"cat3_attr_"+attr.id+"_value_"+attrVal.id+"\" class=\"attr-value selem1\">"+
				//                                "<td></td>"+
				//                                "<td class=\"name-value\">"+attrVal.value+"</td>"+
				//                                "<td class=\"usedCount\">"+attrVal.usedCount+"</td>"+
				//                                "<td class=\"usedCount\"></td>"+
				//                                "<td class=\"actions\"></td>"+
				//                              "</tr>";
				//                    }
				//                    if (attr.selected == 1)
				//                      selected_list += html;
				//                    else
				//                      not_selected_list += html;
				//                  }
				//                  $("#cat3-attributes-selection tbody").empty().append(selected_list);
				//                  $("#cat3-attributes tbody").empty().append(not_selected_list != "" ? not_selected_list : "<tr><td colspan=\"4\">Aucun attribut disponible</td></tr>");
				//
				//                  init_cat3_attr_selection_actions("#cat3-attributes-selection tbody tr");
				//                  init_cat3_attr_actions("#cat3-attributes tbody tr");
				//                }
				//
				//                createTreeLevel();
				//              }
				//            });
				refresh_cat3_attributes_tables()
										return false;
									}
								}
								this.ShowSSF();
								return false;
							}
							asf[j].ShowSSF = function () { // fonction pour montrer la ssf
								if (this.parentNode.current_sf && (this.parentNode.current_sf != this)) this.parentNode.current_sf.HideSSFext();
								this.parentNode.current_sf = this;
								this.ssfNext.style.display = 'inline';
								this.className = 'currentUnfolded';
								this.Select();
								this.onclick = this.HideSSF;
								return false;
							}
							asf[j].HideSSF = function () { // fonction pour cacher la ssf courante
								this.ssfNext.style.display = 'none';
								this.className = 'currentFolded';
								this.Select();
								this.onclick = this.ShowSSF;
								return false;
							}
							asf[j].HideSSFext = function () { // fonction pour cacher la ssf non courante
								this.ssfNext.style.display = 'none';
								this.className = '';
								this.onclick = this.ShowSSF;
								return false;
							}
							asf[j].Select = function () { // fonction d'initialisation lors de la sélection de la sf
								cur_family_id = this.family_id;
								if (this.ssfNext.current_ssf) this.ssfNext.current_ssf.className = ''; // si une sous-famille enfante est sélectionnée, on la déselectionne
								
								document.getElementById('desc').innerHTML = "Famille " + families[this.family_id][name] + " (niveau 2)";
								
								var FamiliesOptionsList = '';
								var idp = families[this.family_id][idParent];
								for (l = 0; l < families[0][nbchildren]; l++)
								{
									FamiliesOptionsList += '<option value="' + families[0][children][l] + '"';
									if (families[0][children][l] == idp) FamiliesOptionsList += ' selected';
									FamiliesOptionsList += '>' + families[families[0][children][l]][name] + '</option>';
								}
								
								document.getElementById('fam').innerHTML = "" +
								'<div class="line">' +
									'<div class="entitle">Changer le nom :</div>' +
									'<input class="capture" type="text" name="editNameValue" value="' + families[this.family_id][name] + '"/>' +
									'<input class="button" type="button" value="Valider" onclick="editName()" />' +
								'</div>' +
								'<div class="zero"></div>' +
								'<br />' +
								'<div class="line">' +
									'<div class="entitle">Changer la famille parente :</div>' +
									'<select class="captureS" name="editParentValue">' +
										FamiliesOptionsList + 
									'</select>' +
									'<input class="button" type="button" value="Valider" onclick="editParent()" />' +
								'</div>' +
								'<div class="zero"></div>' +
								'<br />' +
								'<div class="line">' +
									'<div class="entitle">Ajouter une sous-famille :</div>' +
									'<input class="capture" type="text" name="addvalue" />' +
									'<input class="button" type="button" value="Ajouter" onclick="addfam()" />' +
								'</div>' +
								'<div class="zero"></div>' +
						'<br />' +
						'<div class="line">' +
						'	<div class="entitle">URI SEO :</div>' +
						'	<input class="capture" type="text" id="editRefNameValue" value="'+families[this.family_id][ref_name]+'"/>' +
						'	<input class="button" type="button" value="Valider" onclick="editRefName()" />' +
						'</div>' +
						'<div class="zero"></div>' +
						'<br />' +
						'<div class="line">' +
						'	<div class="entitle">Titre de la page en FO :</div>' +
						'	<input class="capture" type="text" id="editTitleValue" value="'+families[this.family_id][title]+'"/>' +
						'	<input class="button" type="button" value="Valider" onclick="editTitle()" />' +
						'</div>' +
						'<div class="zero"></div>' +
						'<br />' +
						'<div class="line">' +
						'	<div class="entitle">Description de la page en FO :</div>' +
						'	<input class="capture" type="text" id="editDescValue" value="'+families[this.family_id][meta_desc]+'"/>' +
						'	<input class="button" type="button" value="Valider" onclick="editDesc()" />' +
						'</div>' +
							'<br />' +
								'<a href="delete" onclick="delfam(); return false"' + (families[this.family_id][nbchildren] > 0 ? ' style="text-decoration: line-through"' : '') + '>Supprimer cette sous-famille</a>';
						$("#cat3-attributes-selection, #cat3-attributes-list").hide();
							}
							
							asf[j].onclick = asf[j].InitSSF; // au départ le clique pointe sur le constructeur de la ssf
						}
						
						document.getElementById('desc').innerHTML = "Famille " + families[this.family_id][name] + " (niveau 1)";
						document.getElementById('fam').innerHTML = "" +
					'<input type="hidden" name="editNameValue" value="'+families[this.family_id][name]+'"/>' +
						'<div class="line">' +
						'	<div class="entitle">Ajouter une sous-famille :</div>' +
						'	<input class="capture" type="text" id="addvalue" />' +
						'	<input class="button" type="button" value="Ajouter" onclick="addfam()" />' +
						'</div>' +
						'<div class="zero"></div>' +
					'<br />' +
						'<div class="line">' +
						'	<div class="entitle">Titre de la page en FO :</div>' +
						'	<input class="capture" type="text" id="editTitleValue" value="'+families[this.family_id][title]+'"/>' +
						'	<input class="button" type="button" value="Valider" onclick="editTitle()" />' +
						'</div>' +
						'<div class="zero"></div>' +
					'<br />' +
						'<div class="line">' +
						'	<div class="entitle">Description de la page en FO :</div>' +
						'	<input class="capture" type="text" id="editDescValue" value="'+families[this.family_id][meta_desc]+'"/>' +
						'	<input class="button" type="button" value="Valider" onclick="editDesc()" />' +
						'</div>' +
					'<br />' +
					'<div class="line">' +
					'	<div class="entitle">HTML de présentation en FO :</div>' +
					'	<textarea class="capture" id="editContentValue" >'+$("<div/>").text(families[this.family_id][text_content]).html()+'</textarea>' +
					'	<input class="button" type="button" value="Valider" onclick="editContent()" />' +
					'</div>' +
						'<div class="zero"></div>';
					$("#cat3-attributes-selection, #cat3-attributes-list").hide();
						
						return false;
					}
					af[i].UnSelect = function () { // lors de la déselection
						this.className = '';
					}
					
					af[i].onclick = af[i].Select;
					
					af2[i].num_a = i;
					af2[i].onclick = function () { document.getElementById('menu').getElementsByTagName('a')[this.num_a].Select(); return false; }
				}

				$("#set_cat3_selected_attributes_dialog").dialog({
				  width: 550,
				  autoOpen: false,
				  modal: true
				});
				$("#update-cat3-attributes-selection").click(function(){
				  var saIds = [];
				  $("#cat3-attributes-selection tbody tr.attr").each(function(){
					var saId = $(this).attr("id").match(/^cat3_attr_(\d+)/i)[1];
					var savIds = [];
					$("#cat3-attributes-selection tbody tr[id^='cat3_attr_"+saId+"_value_']").each(function(){
					  savIds.push($(this).attr("id").match(/^cat3_attr_\d+_value_(\d+)/i)[1]);
					});
					saIds.push({ saId: saId, savIds: savIds });
				  });
				  $.ajax({
					type: "POST",
					url: "AJAX_interface.php",
					data: {"actions":[{"action":"set_cat3_selected_attributes","cat3Id":cur_family_id,"saIds": saIds}]},
					dataType: "json",
					error: function (XMLHttpRequest, textStatus, errorThrown) {
					},
					success: function (data, textStatus) {
					  if (!data.error) {
						$("#set_cat3_selected_attributes_dialog").dialog("open");
					  }
					}
				  });
				});
				$("#get-cat3-products").click(function(){
				  window.open("category_products_extract.php?cat3Id="+cur_family_id, "_blank");
				});

				var toggle_activation = function(idRefAttr){
				   $.ajax({
					type: "POST",
					url: "AJAX_interface.php",
					data: {"actions":[{"action":"toggle_activation","idRefAttr":idRefAttr}]},
					dataType: "json",
					error: function (XMLHttpRequest, textStatus, errorThrown) {
					},
					success: function (data, textStatus) {
					  if (!data.error) {
						  var icon_active = $('#cat3_attr_'+idRefAttr).find('div.activation');
						  icon_active.toggleClass('icon-deactivate').toggleClass('icon-activate');
						  icon_active.attr('title', icon_active.attr('title') == 'activer' ? 'désactiver' : 'activer');
					  }
					}
				  });
				  return false;
				};

				// edit virtual cat3 attribute product list
				$('.products-add').live(
				  'click', function(){
					var virt_attr_id = $(this).closest('tr').attr("class").match(/^id_virt_attr_(\d+)/i)[1];

				  $('#create_virtual_cat3_attributes_dialog').dialog('close');
				  
				  $.ajax({
					type: "POST",
					url: "AJAX_interface.php",
					data: {"actions":[{"action":'get_virtual_attr_products_list',"cat3Id":cur_family_id,"cat3_attr_id": virt_attr_id}]},
					dataType: "json",
					virt_attr_id: virt_attr_id,
					error: function (XMLHttpRequest, textStatus, errorThrown) {
					},
					success: function (data, textStatus) {
					  if (!data.error) {
						//$("#set_cat3_selected_attributes_dialog").dialog("open");
						//console.log('ok');
						var list_products = '';
						$.each(data.data, function(){
						  list_products += list_products == '' ? this: ', '+this;
						});
						var html = '<input type="hidden" name="virt_attr_id" value="'+virt_attr_id+'"  /><textarea name="virt_attr_products_list" cols="60" rows="5">'+list_products+'</textarea>\n\
				<button id="update_virt_attr_products_list" class="btn ui-state-default ui-corner-all fr">sauvegarder</button>    ';
						$('#add_product_to_virtual_attr_dialog').dialog('open');
						$('div#create_virtual_cat3_attributes_dialog div#add_product_to_virtual_attr_dialog').remove();
						$("#add_product_to_virtual_attr_dialog").html(html);
					  }
					}
				  });
				  
				});

				// delete virtual attr
				function delete_cat3_virtual_attributes(attr_id, attr_name){
				  if(confirm('Supprimer la facette virtuelle '+attr_name+'?')){
					$.ajax({
					type: "POST",
					url: "AJAX_interface.php",
					data: {"actions":[{"action":'delete_virtual_attr_',"cat3Id":cur_family_id,"cat3_attr_id": attr_id}]},
					dataType: "json",
					attr_name: attr_name,
					error: function (XMLHttpRequest, textStatus, errorThrown) {
					},
					success: function (data, textStatus) {
					  if (!data.error) {
						alert('La facette virtuelle '+attr_name+' a été supprimée');
						refresh_cat3_attributes_tables();
					  }
					}
				  });
				  }
				}

				// update virtual attr
				$('#update_virt_attr_products_list').live(
				  'click', function(){

				  var  products_list = $('textarea[name=virt_attr_products_list]').val();
				  var virt_attr_id = $('input[type=hidden][name=virt_attr_id]').val();
				  $.ajax({
					type: "POST",
					url: "AJAX_interface.php",
					data: {"actions":[{"action":'update_virtual_attr_products_list',"cat3Id":cur_family_id,"cat3_attr_id": virt_attr_id, "products_list": products_list}]},
					dataType: "json",
					virt_attr_id: virt_attr_id,
					error: function (XMLHttpRequest, textStatus, errorThrown) {
					},
					success: function (data, textStatus) {
					  if (!data.error) {

					  }
					}
				  });
				  $('#add_product_to_virtual_attr_dialog').dialog('close');
				});

				// show virtual cat3 attribute dialog
				$('#create-virtual-cat3-attributes').click(function(){
				  $('#create_virtual_cat3_attributes_dialog').dialog('open');
				  $("#create_virtual_cat3_attributes_dialog").load("virtual-cat3-attributes-values.php", { cur_family_id: cur_family_id});
				});

				$("#save-virtual-cat3-attributes-values").live('click', function(){

				  var saIds = [];
				  $("table.cat3-group tbody tr").each(function(){
					var saIds2 = [];
					$(this).find('input').each(function(){
					  saIds2.push(this.value);

					});
					var id_attr = this.className.split('id_virt_attr_');
					  var idAttr = id_attr[1];
					  saIds2.push(idAttr);
					saIds.push(saIds2);
				  });

				  var virt_attr_id = $('input[name=virtual_attr_id]').val();
				  var action = virt_attr_id != '' ? 'update_virtual_attr' : 'create_virtual_attr';

				  $.ajax({
					type: "POST",
					url: "AJAX_interface.php",
					data: {"actions":[{"action":action,"cat3Id":cur_family_id,"attr_name":$('input[name=virtual-attr-name]').val(),"cat3_attr_id": virt_attr_id,"saIds": saIds}]},
					dataType: "json",
					error: function (XMLHttpRequest, textStatus, errorThrown) {
					},
					success: function (data, textStatus) {
					  if (!data.error) {
						//$("#set_cat3_selected_attributes_dialog").dialog("open");
						//console.log('ok');
					  }
					}
				  });
				  $('#create_virtual_cat3_attributes_dialog').dialog('close');

				  refresh_cat3_attributes_tables();
				});

				//edit virtual cat3 attributes
				var edit_virtual_cat3_attributes = function(id){
				  $('#create_virtual_cat3_attributes_dialog').dialog('open');
				  $("#create_virtual_cat3_attributes_dialog").load("virtual-cat3-attributes-values.php", { cur_family_id: cur_family_id, attrId : id});
				};

				var init_cat3_attr_selection_actions = function(trs){
				  $(trs).find("td.actions").empty().each(function(){
					var $tr = $(this).closest("tr");
					var isAttr = $tr.hasClass("attr");
					var id = $tr.attr("id");
					if (isAttr) {
					  $("<div/>", { "class": "icon icon-up", "title": "monter", "click": function(){
						  $tr.prevAll(".attr").first().before($tr.parent().find("tr[id^='"+id+"']"));
						}
					  }).appendTo(this);
					  $("<div/>", { "class": "icon icon-down", "title": "descendre", "click": function(){
						  $tr.nextAll(".attr").first().after($tr.parent().find("tr[id^='"+id+"']"));
						}
					  }).appendTo(this);

					if($(this).hasClass('virtual')){
					  $("<div/>", { "class": "icon icon-edit", "title": "éditer", "click": function(){
				//          toggle_activation($(this).closest('tr').attr('id').match(/^cat3_attr_(\d+)/i)[1]);
						  edit_virtual_cat3_attributes($(this).closest('tr').attr('id').match(/^cat3_attr_(\d+)/i)[1]);
						  }
						}).appendTo(this);
					}else
					  $("<div/>", { "class": "icon shape-group", "title": "grouper", "click": function(){
						  $('#attributes_values_group_dialog').dialog('open');
						  $("#attributes_values_group_dialog").load("cat3-attributes-values-group.php", { cur_family_id: cur_family_id, cat3_attr: id });

						  $("#update-cat3-attributes-values-group").live('click', function(){
							
							var saIds = [];
							$("table.cat3-group tbody tr").each(function(){
							  var saIds2 = [];
							  $(this).find('input').each(function(){
								saIds2.push(this.value);

							  });
							  var id_attr = this.className.split('id_attr_');
								var idAttr = id_attr[1];
								saIds2.push(idAttr);
							  saIds.push(saIds2);
							});
							
							$.ajax({
							  type: "POST",
							  url: "AJAX_interface_groups.php",
							  data: {"actions":[{"action":"set_cat3_values_groups","cat3Id":cur_family_id,"cat3_attr": id,"saIds": saIds}]},
							  dataType: "json",
							  error: function (XMLHttpRequest, textStatus, errorThrown) {
							  },
							  success: function (data, textStatus) {
								if (!data.error) {
								  //$("#set_cat3_selected_attributes_dialog").dialog("open");
								  //console.log('ok');
								}
							  }
							});
							$("#update-cat3-attributes-values-group").die('click');
							$('#attributes_values_group_dialog').dialog('close');

				//
				//            $("#cat3-attributes-selection, #cat3-attributes").show()
				//              .find("tbody").empty().append("<tr><td colspan=\"4\">Chargement en cours...</td></tr>");
				//
				//            $.ajax({
				//              type: "POST",
				//              url: "AJAX_interface.php",
				//              data: {"actions":[/*{"action":"get_cat3_selected_attributes","cat3Id":cur_family_id}, */{"action":"get_cat3_attributes","cat3Id":cur_family_id}]},
				//              dataType: "json",
				//              error: function (XMLHttpRequest, textStatus, errorThrown) {},
				//              success: function (res, textStatus) {
				//                if (!res.error) {
				//                  var selected_list = not_selected_list = "";
				//                  for (var ai=0; ai<res.data.cat3_attributes.length; ai++) {
				//                    var attr = res.data.cat3_attributes[ai];
				//                    var html = "<tr id=\"cat3_attr_"+attr.id+"\" class=\"attr"+(attr.values.length?" scat1":"")+"\">"+
				//                                 "<td></td>"+
				//                                 "<td class=\"name-value\">"+attr.name+"</td>"+
				//                                 "<td class=\"usedCount\">"+attr.usedCount+"</td>"+
				//                                 "<td class=\"usedCount\">"+attr.nbInterval+"</td>"+
				//                                 "<td class=\"actions\"></td>"+
				//                               "</tr>";
				//                    for (var avi=0; avi<attr.values.length; avi++) {
				//                      var attrVal = attr.values[avi];
				//                      html += "<tr id=\"cat3_attr_"+attr.id+"_value_"+attrVal.id+"\" class=\"attr-value selem1\">"+
				//                                "<td></td>"+
				//                                "<td class=\"name-value\">"+attrVal.value+"</td>"+
				//                                "<td class=\"usedCount\">"+attrVal.usedCount+"</td>"+
				//                                "<td class=\"actions\"></td>"+
				//                              "</tr>";
				//                    }
				//                    if (attr.selected == 1)
				//                      selected_list += html;
				//                    else
				//                      not_selected_list += html;
				//                  }
				//                  $("#cat3-attributes-selection tbody").empty().append(selected_list);
				//                  $("#cat3-attributes tbody").empty().append(not_selected_list != "" ? not_selected_list : "<tr><td colspan=\"4\">Aucun attribut disponible</td></tr>");
				//
				//                  init_cat3_attr_selection_actions("#cat3-attributes-selection tbody tr");
				//                  init_cat3_attr_actions("#cat3-attributes tbody tr");
				//                }
				//
				//                createTreeLevel();
				//              }
				//            });
				refresh_cat3_attributes_tables();
						  });
						}
					  }).appendTo(this);
					  if($(this).hasClass('virtual')){
						$("<div/>", { "class": "icon icon-del", "title": "supprimer", "click": function(){
							delete_cat3_virtual_attributes($(this).closest('tr').attr('id').match(/^cat3_attr_(\d+)/i)[1], $(this).closest('tr').find('td:nth-child(2)').html());
							}
						  }).appendTo(this);
					  }else
					  $("<div/>", { "class": "icon icon-del", "title": "supprimer", "click": function(){
						  var $trs = $("#cat3-attributes-selection tbody tr[id^='"+$(this).closest("tr").attr("id")+"']").appendTo("#cat3-attributes tbody");
						  init_cat3_attr_actions($trs);
						}
					  }).appendTo(this);

					  if($(this).hasClass('active_1')){
						$("<div/>", { "class": "icon icon-deactivate activation", "title": "désactiver", "click": function(){
						  toggle_activation($(this).closest('tr').attr('id').match(/^cat3_attr_(\d+)/i)[1]);
						  }
						}).appendTo(this);
						$(this).removeClass('active_1');
					  }else{
						$("<div/>", { "class": "icon icon-activate activation", "title": "activer", "click": function(){
						  toggle_activation($(this).closest('tr').attr('id').match(/^cat3_attr_(\d+)/i)[1]);
						  }
						}).appendTo(this);
						$(this).removeClass('active_0');
					  }
					}
					else {
					  $("<div/>", { "class": "icon icon-up", "title": "monter", "click": function(){
						  $tr.prev(".attr-value").before($tr);
						}
					  }).appendTo(this);
					  $("<div/>", { "class": "icon icon-down", "title": "descendre", "click": function(){
						  $tr.next(".attr-value").after($tr);
						}
					  }).appendTo(this);
					}
				  });
				}
				var init_cat3_attr_actions = function(trs){
				  var $tbody = $(trs).closest("tbody");
				  var trList = $tbody.find("tr.attr").get();
				  trList.sort(function(tra,trb){
					var a = $(tra).find("td.name-value").text().toLowerCase();
					var b = $(trb).find("td.name-value").text().toLowerCase();
					if (a > b) return 1;
					if (a < b) return -1;
					return 0;
				  });
				  $.each(trList, function(i, v){
					$tbody.append($tbody.find("tr[id^='"+$(v).attr("id")+"']"));
				  });
				  //$("#cat3-attributes tbody").empty().append(trs);
				  
				  $(trs).find("td.actions").empty().end().filter(".attr").find("td.actions").each(function(){
					$("<div/>", { "class": "icon icon-add", "title": "ajouter", "click": function(){
						var $trs = $tbody.find("tr[id^='"+$(this).closest("tr").attr("id")+"']").appendTo("#cat3-attributes-selection tbody");
						init_cat3_attr_selection_actions($trs);
					  }
					}).appendTo(this);
				  });
				};

				var tri, trs;
				var createTreeLevel = function(){
				  tri = 0;
				  trs = $("#cat3-attributes tbody tr, #cat3-attributes-selection tbody tr").get();
				  $(trs).filter("[class='attr']:odd").addClass("odd");
				  
				  while (tri < trs.length) {
					if ($(trs[tri]).hasClass("scat1"))
					  createTreeLevelRecursive(1);
					else
					  tri++;
				  }
				}

				var createTreeLevelRecursive = function(dn) {
				  // Adding | and + pics
				  var $td = $(trs[tri]).find("td:first");
				  for (var i=1; i<dn; i++)
					$td.append("<div class=\"more\"></div>");
				  var div_folder = document.createElement("div");
				  div_folder.className = "add";
				  $td.append(div_folder);
				  
				  tri++;
				  var trs_cat = [];
				  var trs_start = tri;
				  while(tri < trs.length) {
					if ($(trs[tri]).hasClass("selem"+dn)) {
					  for (var i=1; i<=dn; i++)
						$(trs[tri]).find("td:first").append("<div class=\"more\"></div>");
					  trs_cat.push(trs[tri]);
					  tri++;
					}
					else if ($(trs[tri]).hasClass("scat"+(dn+1))) {
					  trs_cat.push(trs[tri]);
					  createTreeLevelRecursive(dn+1);
					}
					else {
					  break;
					}
				  }
				  var trs_over = trs.slice(trs_start, tri);
				  $(trs_cat).filter(":odd").addClass("odd");
				  
				  $(div_folder).click(function(){
					if ($(div_folder).hasClass("add")) {
					  $(trs_cat).show();
					  $(trs_cat).find("td:first div.sub").click().click();
					}
					else
					  $(trs_over).hide();
					
					$(div_folder).toggleClass("add").toggleClass("sub");
					return false;
				  });
				};

				// declare the dialog
				$("#attributes_values_group_dialog").dialog({ width: 580, autoOpen: false });
				$("#attributes_values_group_dialog").draggable({ handle: '.window_title_bar',  containment: '#page-content'});
				$("#create_virtual_cat3_attributes_dialog").dialog({
				  width: 550,
				  autoOpen: false,
				  modal: true
				});
				//$("#create_virtual_cat3_attributes_dialog").draggable({ handle: '.window_title_bar',  containment: '#page-content'});

				$("#add_product_to_virtual_attr_dialog").dialog({
				  width: 550,
				  autoOpen: false,
				  modal: true
				});
				$("#add_product_to_virtual_attr_dialog").draggable({ handle: '.window_title_bar',  containment: '#page-content'});


				function refresh_cat3_attributes_tables(){
				  $("#cat3-attributes-selection, #cat3-attributes, #create-virtual-cat3-attributes").show()
				  .find("tbody").empty().append("<tr><td colspan=\"4\">Chargement en cours...</td></tr>");

				  $.ajax({
					type: "POST",
					url: "AJAX_interface.php",
					data: {"actions":[/*{"action":"get_cat3_selected_attributes","cat3Id":cur_family_id}, */{"action":"get_cat3_attributes","cat3Id":cur_family_id}]},
					dataType: "json",
					error: function (XMLHttpRequest, textStatus, errorThrown) {},
					success: function (res, textStatus) {
					  if (!res.error) {
						var selected_list = not_selected_list = "";
						for (var ai=0; ai<res.data.cat3_attributes.length; ai++) {
						  var attr = res.data.cat3_attributes[ai];
						  var virtual = attr.virtual == 1 ? ' virtual' : '';
						  var html = "<tr id=\"cat3_attr_"+attr.id+"\" class=\"attr"+(attr.values.length?" scat1":"")+"\">"+
									   "<td></td>"+
									   "<td class=\"name-value\">"+attr.name+"</td>"+
									   "<td class=\"usedCount\">"+attr.usedCount+"</td>"+
									   "<td class=\"usedCount\">"+attr.nbInterval+"</td>"+
									   "<td class=\"actions active_"+attr.active+virtual+"\"></td>"+
									 "</tr>";
						  for (var avi=0; avi<attr.values.length; avi++) {
							var attrVal = attr.values[avi];
							html += "<tr id=\"cat3_attr_"+attr.id+"_value_"+attrVal.id+"\" class=\"attr-value selem1\">"+
									  "<td></td>"+
									  "<td class=\"name-value\">"+attrVal.value+"</td>"+
									  "<td class=\"usedCount\">"+attrVal.usedCount+"</td>"+
									  "<td class=\"usedCount\"></td>"+
									  "<td class=\"actions\"></td>"+
									"</tr>";
						  }
						  if (attr.selected == 1)
							selected_list += html;
						  else
							not_selected_list += html;
						}
						$("#cat3-attributes-selection tbody").empty().append(selected_list);
						$("#cat3-attributes tbody").empty().append(not_selected_list != "" ? not_selected_list : "<tr><td colspan=\"4\">Aucun attribut disponible</td></tr>");

						init_cat3_attr_selection_actions("#cat3-attributes-selection tbody tr");
						init_cat3_attr_actions("#cat3-attributes tbody tr");
					  }

					  createTreeLevel();
					}
				  });
				}

				</script>
	
		</div><!-- #.families_left -->
		
		<div class="families_right">
			<div class="families_right_header">
				Familles n'ayant aucun produit &nbsp;
			</div>
			<div id="families_right_content">
				chargement en cours..
			</div>
			<div id="families_management_search_load"></div>
			<script type="text/javascript">
				load_familles_empty();
			</script>
		</div><!-- .families_right -->

		
	</div> <!-- end #Families -->
</div>
<?php require(ADMIN."tail.php") ?>
