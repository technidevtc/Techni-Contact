<div class="za">
	<div id="page-heading" class="ng-scope">
		<h1 class="segments_h1_left"><i class="fa fa-dashboard"></i> Segments</h1>
		
		<!--
		<div id="segments_add_btn">
			<input type="button" id="segments_btn_lst_create" value="Ajouter un segment" onclick="javascript:document.location='segments-create.php'" class="btn btn-primary" style="margin-top:0;"> 
		</div>
		-->
		
		<input type="hidden" id="hidden_from_config_groupe_limitation" value="<?php echo MARKETING_LIMITATION_GROUPES; ?>" />
		<input type="hidden" id="hidden_from_config_field_limitation" value="<?php echo MARKETING_LIMITATION_FIELDS; ?>" />
		
    </div>
	
	
	<div class="row">
		<div class="col-md-12" style="width:100%;">
			<div class="panel panel-primary">
				<div class="panel-heading">
					<h4>
						Cr&eacute;er un segment
					</h4>
				</div><!-- end div .panel-heading -->
				
				
				
				
			</div><!-- end div .panel panel-primary -->
			
	
		</div><!-- end div .col-md-12 -->
	</div><!-- end div .row -->
	
	<div style="display:none;">
		<input type="hidden" id="hidden_groupe_limitation" value="2" />
		<input type="hidden" id="hidden_field_limitation" value="12" />
		
		<input type="hidden" id="hidden_groupe_incrementation" value="1" />
		<input type="hidden" id="hidden_field_incrementation" value="1" />
	</div>
			
	<div id="segment_creation_container" class="row">
		<div id="segment_top_part" class="row">
			<div id="segment_top_part_left">
				<div class="row">
					<div class="left_element">
						<label for="segment_name">
							Nom du segment :
						</label>
					</div>
					<div class="right_element">
						<input type="text" id="segment_name" name="segment_name" style="width:184px;" />				
					</div>					
				</div><!-- End .row -->
				
				<div class="row">
					<div class="left_element">
						<label>
							Typologie :
						</label>
					</div>
					<div class="right_element">
						<input type="radio" id="segment_static_typology" name="segment_typology" value="statique" checked="true" />
						<label for="segment_static_typology">
							Statique
						</label>
						
						&nbsp;&nbsp;
						<input type="radio" id="segment_dynamique_typology" name="segment_typology" value="dynamique" />
						<label for="segment_dynamique_typology">
							Dynamique
						</label>
					</div>					
				</div><!-- End .row -->
				
				<div class="row">
					<div class="left_element">
						<label for="segment_table_list">
							Nom table :
						</label>
					</div>
					<div class="right_element">
						<?php
							require_once('segments_create_get_tables_list.php');
						?>
					</div>					
				</div><!-- End .row -->
				
			</div>
			
			<img id="segments_load_fields_loading" src="ressources/images/loading2.gif" />
			<div id="segment_top_part_right" placeholder="Veuillez selectionner une table">
				<span style="opacity:0.5;">Veuillez selectionner une table</span>
			</div>
		</div><!-- end div .row -->
		
		<div id="stats_remaining_elements">
			
		</div>
		<div id="groupe_add_container">
			<input type="button" id="groupe_add_btn" class="btn btn-default" value="Ajouter un groupe" onclick="segment_add_groupe()" />
		</div>
					
		<div id="segment_middle_part" class="row">
			
			
			
		</div><!-- end div #segment_middle_part -->
		
		<div id="segment_bottom_part" class="row">
			C
		</div><!-- end div .row -->
		
		<div id="segment_send_part" class="row">
			<input type="button" class="btn btn-primary" value="Valider" onclick="segment_create_validation()" />
		</div><!-- end div .row -->
		
	</div><!-- end div #segment_creation_container -->	

	
	<div id="segment_errors"></div>
	<div id="segment_actions_ask"></div>
	
</div><!-- end div .za -->