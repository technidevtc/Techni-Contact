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
						Modifier un segment
					</h4>
				</div><!-- end div .panel-heading -->
				
				
				
				
			</div><!-- end div .panel panel-primary -->
			
	
		</div><!-- end div .col-md-12 -->
	</div><!-- end div .row -->
	
	<div style="display:none;">
		<input type="hidden" id="hidden_segment_id" value="<?php echo $segment_id; ?>" />
		
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
						<label>
							Source :
						</label>
					</div>
					<div class="right_element">
						<input type="radio" id="segment_source_table" name="segment_source_export" value="statique"  onchange="segment_export_change_source('tables')"
						<?php if(empty($segment_id)){ echo('checked="true"'); } ?> />
						
						<label for="segment_source_table">
							Table
						</label>
						
						&nbsp;&nbsp;
						<input type="radio" id="segment_source_segment" name="segment_source_export" value="dynamique" onchange="segment_export_change_source('segments')" 
						<?php if(!empty($segment_id)){ echo('checked="true"'); } ?> />
						
						<label for="segment_source_segment">
							Segment
						</label>
					</div>					
				</div><!-- End .row -->
				
				<div class="row">
					<div class="left_element">
						<label>
							&nbsp;
						</label>
					</div>	
					<div id="segment_source_choice_zone" class="right_element">
						&nbsp;
					</div>
				</div><!-- End .row -->
				
			</div>
			
			
			<div id="segment_top_part_right" class="connected_container_fields" placeholder="Veuillez selectionner une table">
				<span style="opacity:0.5;">Veuillez selectionner une source..</span>
			</div>
		</div><!-- end div .row -->
		
		<div id="stats_remaining_elements">
			
		</div>
		<div id="groupe_add_container">
			<input type="button" id="groupe_add_btn_add_all" class="btn btn-default" value="Ajouter tout" onclick="segment_export_add_all_fields()" />
			&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="button" id="groupe_add_btn_remove_all" class="btn btn-default" value="Retirer tout" onclick="segment_export_remove_all_fields()" />
		</div>
					
		<div id="segment_middle_part" class="row" style="min-height:150px;">
			
			<div id="segment_export_reception_elements" class="connected_container_fields" style="z-index:-10;">
				&nbsp;
			</div>
			
			
		</div><!-- end div #segment_middle_part -->
		
		<div id="segment_bottom_part" class="row">
			
		</div><!-- end div .row -->
		
		<div id="segment_send_part" class="row">
			<input type="button" class="btn btn-primary" value="Exporter" onclick="segment_export_lunch()" />
			<form id="export_send_form" target="_blank" action="/fr/marketing/segments-export-confirm.php" method="POST" style="display:none;">
				<input type="hidden" id="export_v_source" name="export_v_source" value="" />
				<input type="hidden" id="export_v_selected_value" name="export_v_selected_value" value="" />
				<input type="hidden" id="export_v_selected_fields" name="export_v_selected_fields" value="" />
			</form>
		</div><!-- end div .row -->
		
	</div><!-- end div #segment_creation_container -->	

	
	<div id="segment_errors"></div>
	<div id="segment_actions_ask"></div>
	
</div><!-- end div .za -->