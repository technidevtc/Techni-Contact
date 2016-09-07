<?php

	if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
		require_once '../../../../config.php';
	}else{
		require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
	}

	$title = $navBar = "MAJ Fournisseurs";
	$db = DBHandle::get_instance();
	$user = new BOUser();

	require(ADMIN.'head.php');
?>
<?php 
	if ($user->get_permissions()->has("m-prod--sm-maj-fournisseurs", "r")){	
?>
		<link rel="stylesheet" type="text/css" href="<?php echo ADMIN_URL ?>supplier-update/supplier-update.css" />
		<script type="text/javascript" src="<?php echo ADMIN_URL ?>supplier-update/supplier-update.js"></script>
		<div id="module_maj_fournisseurs">
			<div class="titreStandard">Recherche d'un fournisseur</div>
			<div class="bg">
			
				<br />
				<div class="mmf_head_form_left">
					Entrer le nom d'un fournisseur &agrave; rechercher :
				</div>
				<div class="mmf_head_form_middle">
					<input type="text" name="mmf_nom_fournisseur" id="mmf_nom_fournisseur" class="champstexte" onkeyup="module_maj_fournisseurs_autocomplete(event)" />
					<div id="mmf_autocomp_loader">&nbsp;</div>
					<div id="mmf_autocomp_container"></div>
				</div>
				<div class="mmf_head_form_right">
					<input type="button" value="rechercher" id="mmf_fournisseur_submit" class="bouton" onclick="mmf_lunch_search();" />
				</div>
				<div class="mmf_head_form_right_help">
					<img src="/fr/manager/ressources/images/supplier-update-help.png" alt="Informations fonctionnement" title="Informations fonctionnement" onclick="supplier_update_help()" />
				</div>
				
				<div id="supplier-update-help-container">
					<div class="mmf_help_header">
						<div class="mmf_help_header_left">
							Informations fonctionnement module
						</div>
						<div class="mmf_help_header_right">
							<img src="/fr/manager/ressources/b_drop.png" alt="Fermer" title="Fermer" onclick="supplier_update_help()" /> 
						</div>
					</div>
					
					<div class="mmf_help_content">
						Ce module vous permet de mettre &agrave; jour tr&egrave;s facilement les tarifs d'un fournisseur:
						
						<br /><br />
						<div style="padding-left: 10px;">
							1 - R&eacute;cup&eacute;rez le fichier Excel du fournisseur et placez les colonnes contenant les r&eacute;f&eacute;rences et les prix associ&eacute;s en premier (le nom des colonnes n'est pas important).
							<br /><br />
							
							2 - Recherchez un fournisseur dans le moteur ci-contre
							<br /><br />
							
							3 - Sur la fiche fournisseur, cliquez sur le bouton "Importer"
							<br /><br />
							
							4 - S&eacute;lectionnez les correspondances des colonnes obligatoires (ref fournisseur et Tarif)
							<br /><br />
							
							5 - S&eacute;lectionnez le chemin du fichier Excel  (1997/2003) &agrave; importer
							<br /><br />
							
							6 - Cliquez sur "Envoyer"
							<br /><br />
							
							7 - La matrice se charge de tout et va importer les tarifs
							<br /><br />
							
							8 - Une fois l'op&eacute;ration termin&eacute;e, vous pourrez t&eacute;l&eacute;charger la liste des nouveaux produits et des produits obsol&egrave;tes.
							<br /><br />
						
						</div>
						
					</div>
				
				</div>
				
			</div><!-- End div .bg -->
			

		</div>
		
		<!-- attachment dialog box Container -->
				<div class="mmf_popup">
					
					<div id="mmf_create_import">
						<input type="button" value="Importer" id="mmf_fournisseur_new_import" class="bouton">
						
						
						<!-- attachment dialog box -->
							<div id="mmf_popup_upload-msn-attachment-db" title="Importer un nouveau fichier" class="db">
								<form name="loadDoc" id="mmf_popup_import_form" method="post" action="/fr/manager/supplier-update/import.php" enctype="multipart/form-data">
									<input type="hidden"id="mmf_import_id_advertiser" name="mmf_import_id_advertiser" value="" /> 
									
									<h2 class="mmf_popup_h2_1">Champs obligatoires</h2>
									<div class="one_row">
										<div class="one_row_left">
											Num&eacute;ro colonne R&eacute;f&eacute;rence : <span class="mmf_required">*</span>
										</div>
										<div class="one_row_right">
											<input type="radio" name="mmf_import_number_reference" id="mmf_import_number_reference_A" value="a" />
											<label for="mmf_import_number_reference_A">A</label>
											
											<input type="radio" name="mmf_import_number_reference" id="mmf_import_number_reference_B" value="b" />
											<label for="mmf_import_number_reference_B">B</label>
											
											<input type="radio" name="mmf_import_number_reference" id="mmf_import_number_reference_C" value="c" />
											<label for="mmf_import_number_reference_C">C</label>
											
											<input type="radio" name="mmf_import_number_reference" id="mmf_import_number_reference_D" value="d" />
											<label for="mmf_import_number_reference_D">D</label>
											
											<input type="radio" name="mmf_import_number_reference" id="mmf_import_number_reference_E" value="e" />
											<label for="mmf_import_number_reference_E">E</label>
											
											<input type="radio" name="mmf_import_number_reference" id="mmf_import_number_reference_F" value="f" />
											<label for="mmf_import_number_reference_F">F</label>
											
											<input type="radio" name="mmf_import_number_reference" id="mmf_import_number_reference_G" value="g" />
											<label for="mmf_import_number_reference_G">G</label>
										</div>
									</div>
									
									<div class="one_row">
										<div class="one_row_left">
											Num&eacute;ro colonne Tarif : <span class="mmf_required">*</span> 
										</div>
										<div class="one_row_right">
											
											<input type="radio" name="mmf_import_number_tarif" id="mmf_import_number_tarif_A" value="a" />
											<label for="mmf_import_number_tarif_A">A</label>
											
											<input type="radio" name="mmf_import_number_tarif" id="mmf_import_number_tarif_B" value="b" />
											<label for="mmf_import_number_tarif_B">B</label>
											
											<input type="radio" name="mmf_import_number_tarif" id="mmf_import_number_tarif_C" value="c" />
											<label for="mmf_import_number_tarif_C">C</label>
											
											<input type="radio" name="mmf_import_number_tarif" id="mmf_import_number_tarif_D" value="d" />
											<label for="mmf_import_number_tarif_D">D</label>
											
											<input type="radio" name="mmf_import_number_tarif" id="mmf_import_number_tarif_E" value="e" />
											<label for="mmf_import_number_tarif_E">E</label>
											
											<input type="radio" name="mmf_import_number_tarif" id="mmf_import_number_tarif_F" value="f" />
											<label for="mmf_import_number_tarif_F">F</label>
											
											<input type="radio" name="mmf_import_number_tarif" id="mmf_import_number_tarif_G" value="g" />
											<label for="mmf_import_number_tarif_G">G</label>
										</div>
									</div>
									
									<h2 class="mmf_popup_h2_2">Champs facultatifs</h2>
									
									<div class="one_row one_rowi">
										<div class="one_row_left">
											Num&eacute;ro colonne Famille : 
										</div>
										<div class="one_row_right">
											
											<input type="radio" name="mmf_import_number_famille" id="mmf_import_number_famille_A" value="a" />
											<label for="mmf_import_number_famille_A">A</label>
											
											<input type="radio" name="mmf_import_number_famille" id="mmf_import_number_famille_B" value="b" />
											<label for="mmf_import_number_famille_B">B</label>
											
											<input type="radio" name="mmf_import_number_famille" id="mmf_import_number_famille_C" value="c" />
											<label for="mmf_import_number_famille_C">C</label>
											
											<input type="radio" name="mmf_import_number_famille" id="mmf_import_number_famille_D" value="d" />
											<label for="mmf_import_number_famille_D">D</label>
											
											<input type="radio" name="mmf_import_number_famille" id="mmf_import_number_famille_E" value="e" />
											<label for="mmf_import_number_famille_E">E</label>
											
											<input type="radio" name="mmf_import_number_famille" id="mmf_import_number_famille_F" value="f" />
											<label for="mmf_import_number_famille_F">F</label>
											
											<input type="radio" name="mmf_import_number_famille" id="mmf_import_number_famille_G" value="g" />
											<label for="mmf_import_number_famille_G">G</label>
										</div>
									</div>
									
									<div class="one_row one_rowi">
										<div class="one_row_left">
											Num&eacute;ro colonne Nom : 
										</div>
										<div class="one_row_right">
											
											<input type="radio" name="mmf_import_number_nom" id="mmf_import_number_nom_A" value="a" />
											<label for="mmf_import_number_nom_A">A</label>
											
											<input type="radio" name="mmf_import_number_nom" id="mmf_import_number_nom_B" value="b" />
											<label for="mmf_import_number_nom_B">B</label>
											
											<input type="radio" name="mmf_import_number_nom" id="mmf_import_number_nom_C" value="c" />
											<label for="mmf_import_number_nom_C">C</label>
											
											<input type="radio" name="mmf_import_number_nom" id="mmf_import_number_nom_D" value="d" />
											<label for="mmf_import_number_nom_D">D</label>
											
											<input type="radio" name="mmf_import_number_nom" id="mmf_import_number_nom_E" value="e" />
											<label for="mmf_import_number_nom_E">E</label>
											
											<input type="radio" name="mmf_import_number_nom" id="mmf_import_number_nom_F" value="f" />
											<label for="mmf_import_number_nom_F">F</label>
											
											<input type="radio" name="mmf_import_number_nom" id="mmf_import_number_nom_G" value="g" />
											<label for="mmf_import_number_nom_G">G</label>
										</div>
									</div>
									
									<div class="one_row one_rowi">
										<div class="one_row_left">
											Num&eacute;ro colonne unit&eacute; de vente : 
										</div>
										<div class="one_row_right">
											
											<input type="radio" name="mmf_import_number_unite_vente" id="mmf_import_number_unite_vente_A" value="a" />
											<label for="mmf_import_number_unite_vente_A">A</label>
											
											<input type="radio" name="mmf_import_number_unite_vente" id="mmf_import_number_unite_vente_B" value="b" />
											<label for="mmf_import_number_unite_vente_B">B</label>
											
											<input type="radio" name="mmf_import_number_unite_vente" id="mmf_import_number_unite_vente_C" value="c" />
											<label for="mmf_import_number_unite_vente_C">C</label>
											
											<input type="radio" name="mmf_import_number_unite_vente" id="mmf_import_number_unite_vente_D" value="d" />
											<label for="mmf_import_number_unite_vente_D">D</label>
											
											<input type="radio" name="mmf_import_number_unite_vente" id="mmf_import_number_unite_vente_E" value="e" />
											<label for="mmf_import_number_unite_vente_E">E</label>
											
											<input type="radio" name="mmf_import_number_unite_vente" id="mmf_import_number_unite_vente_F" value="f" />
											<label for="mmf_import_number_unite_vente_F">F</label>
											
											<input type="radio" name="mmf_import_number_unite_vente" id="mmf_import_number_unite_vente_G" value="g" />
											<label for="mmf_import_number_unite_vente_G">G</label>
										</div>
									</div>
									
									<div class="one_row one_rowi">
										<div class="one_row_left">
											Num&eacute;ro colonne quantit&eacute; par carton : 
										</div>
										<div class="one_row_right">	
											
											<input type="radio" name="mmf_import_number_quantite_carton" id="mmf_import_number_quantite_carton_A" value="a" />
											<label for="mmf_import_number_quantite_carton_A">A</label>
											
											<input type="radio" name="mmf_import_number_quantite_carton" id="mmf_import_number_quantite_carton_B" value="b" />
											<label for="mmf_import_number_quantite_carton_B">B</label>
											
											<input type="radio" name="mmf_import_number_quantite_carton" id="mmf_import_number_quantite_carton_C" value="c" />
											<label for="mmf_import_number_quantite_carton_C">C</label>
											
											<input type="radio" name="mmf_import_number_quantite_carton" id="mmf_import_number_quantite_carton_D" value="d" />
											<label for="mmf_import_number_quantite_carton_D">D</label>
											
											<input type="radio" name="mmf_import_number_quantite_carton" id="mmf_import_number_quantite_carton_E" value="e" />
											<label for="mmf_import_number_quantite_carton_E">E</label>
											
											<input type="radio" name="mmf_import_number_quantite_carton" id="mmf_import_number_quantite_carton_F" value="f" />
											<label for="mmf_import_number_quantite_carton_F">F</label>
											
											<input type="radio" name="mmf_import_number_quantite_carton" id="mmf_import_number_quantite_carton_G" value="g" />
											<label for="mmf_import_number_quantite_carton_G">G</label>
										</div>
									</div>
									
									<div class="one_row one_rowi">
										<div class="one_row_left">
											Num&eacute;ro colonne Eco-part : 
										</div>
										<div class="one_row_right">	
											
											<input type="radio" name="mmf_import_number_ecopart" id="mmf_import_number_ecopart_A" value="a" />
											<label for="mmf_import_number_ecopart_A">A</label>
											
											<input type="radio" name="mmf_import_number_ecopart" id="mmf_import_number_ecopart_B" value="b" />
											<label for="mmf_import_number_ecopart_B">B</label>
											
											<input type="radio" name="mmf_import_number_ecopart" id="mmf_import_number_ecopart_C" value="c" />
											<label for="mmf_import_number_ecopart_C">C</label>
											
											<input type="radio" name="mmf_import_number_ecopart" id="mmf_import_number_ecopart_D" value="d" />
											<label for="mmf_import_number_ecopart_D">D</label>
											
											<input type="radio" name="mmf_import_number_ecopart" id="mmf_import_number_ecopart_E" value="e" />
											<label for="mmf_import_number_ecopart_E">E</label>
											
											<input type="radio" name="mmf_import_number_ecopart" id="mmf_import_number_ecopart_F" value="f" />
											<label for="mmf_import_number_ecopart_F">F</label>
											
											<input type="radio" name="mmf_import_number_ecopart" id="mmf_import_number_ecopart_G" value="g" />
											<label for="mmf_import_number_ecopart_G">G</label>
										</div>
									</div>
									
									
									<div class="one_row">
										<div class="one_row_left">
											S&eacute;lectionnez le document &agrave; importer (Excel '.csv' ou '.xls')  <span class="mmf_required">*</span>
										</div>
										<div class="one_row_right">
											<br />
											<input type="file" name="mmf_pjMessFile"  id="module_internalnotes_pjMessFile" accept=".csv, application/vnd.ms-excel" />
											<!-- application/vnd.openxmlformats-officedocument.spreadsheetml.sheet -->
										</div>
									</div>
									
									<br />
									<div class="one_row">
										<span class="mmf_required">* informations obligatoires</span> 
									</div>
									
									<div id="mmf_import_errors"></div>
									<!-- <img id="mmf_upload_img_loading" class="loading-gif" src="/fr/ressources/images/lightbox-ico-loading.gif" /> -->
								</form>
							</div>
						<!-- End attachment dialog box -->

							
						<?php
						echo('</div>');//end div #mmf_create_import
					?>
				
					<script type="text/javascript">
						//Listner hide autocomplete
						mmf_load_listner_hide_autocompete();
						//Listner Call Popup
						mmf_lunch_new_import();
					</script>
				
				</div> <!-- end div .mmf_popup -->
				<!-- attachment dialog box Container -->
				
		<?php
			require_once('index_container.php');
		?>
		
		
<?php 
	}else{ 
?>
		<div class="bg" style="position: relative">
			<h2>Vous n'avez pas les droits adéquats pour réaliser cette opération.</h2>
		</div>
<?php
	}
?>
<?php require(ADMIN.'tail.php') ?>