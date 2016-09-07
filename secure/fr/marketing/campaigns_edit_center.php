<div class="za">
	<div id="page-heading" class="ng-scope">
		<h1 class="campaigns_h1_left"><i class="fa fa-server"></i> Campagnes</h1>
		
    </div>
	
	
	<div class="row">
		<div class="col-md-12" style="width:100%;">
			<div class="panel panel-primary">
				<div class="panel-heading">
					<h4>
						Cr&eacute;er une campagne
					</h4>
				</div><!-- end div .panel-heading -->
				
			</div><!-- end div .panel panel-primary -->
			
		</div><!-- end div .col-md-12 -->
	</div><!-- end div .row -->
	
	<div id="campaigns_creation_container" class="row">
		<div id="campaigns_top_part" class="row">
			<div id="campaigns_top_part_left">
				<div class="row">
				
					<input type="hidden" id="campaigns_hidden_id" value="<?php echo $content_get_campaign['id']; ?>" />
					
					<div class="left_element">
						<label for="message_title">
							Titre de la campagne :
						</label>
					</div>
					<div class="right_element">
						<input type="text" id="campaign_title" name="campaign_title" class="campaign_field" value="<?php echo utf8_decode($content_get_campaign['c_name']); ?>" />
					</div>					
				</div><!-- End .row -->
				
				<div class="row">
					<div class="left_element">
						<label for="">
							Message associ&eacute; :
						</label>
					</div>
					<div class="right_element">
						<select id="campaign_message_selection" name="campaign_message_selection">
							<?php
								echo('<option value="'.$content_get_campaign['id_message'].'">');
									echo utf8_decode($content_get_campaign['m_name']);
								echo('</option>');
							?>
						</select>
					</div>					
				</div><!-- End .row -->
				
				<div class="row">
					<div class="left_element">
						<label for="sender_name">
							Type :
						</label>
					</div>
					<div class="right_element">
						<!-- <input type="radio" id="campaign_type_adhoc" name="campaign_type" value="adhoc" onchange="campaign_type_change()" checked="true" />
						<label for="campaign_type_adhoc">AD Hoc</label>
						&nbsp;&nbsp;&nbsp;
						<input type="radio" id="campaign_type_trigger" name="campaign_type" value="tigger" onchange="campaign_type_change()" checked="true" />
						<label for="campaign_type_trigger">Trigger</label>-->
						
						<?php
							if(strcmp($content_get_campaign['type'],'adhoc')==0){
								echo('<input type="checkbox" id="campaign_type" name="campaign_type" value="adhoc" checked="true" />');
							}else{
								echo('<input type="checkbox" id="campaign_type" name="campaign_type" value="trigger" />');
							}
						?>

					</div>					
				</div><!-- End .row -->
				
				<div id="campaign_date_container" class="row" <?php if(strcmp($content_get_campaign['type'],'adhoc')==0){ echo('style="display:block;"'); } ?>>
					<div class="left_element">
						<label for="campaign_date_value">Date</label>
					</div>
					<div class="right_element">
						<input type="date" id="campaign_date_value" name="campaign_date_value" placeholder="JJ-MM-AAAA" style="width:175px;" value="<?php 
							$campaign_date_send_temp	= explode('-',$content_get_campaign['date_send']);
							//echo($campaign_date_send_temp[2].'/'.$campaign_date_send_temp[1].'/'.$campaign_date_send_temp[0]);
							echo($campaign_date_send_temp[0].'-'.$campaign_date_send_temp[1].'-'.$campaign_date_send_temp[2]);
						?>" />
					</div>					
				</div><!-- End .row -->
				
				<div id="campaign_time_container" class="row">
					<div class="left_element">
						<label for="campaign_time_value">Heure</label>
					</div>
					<div class="right_element">
						<input type="number" id="campaign_time_value" name="campaign_time_value" value="<?php echo $content_get_campaign['hour_send']; ?>" />
					</div>					
				</div><!-- End .row -->
				
				<div id="campaign_time_container" class="row">
					<div class="left_element">
						<label for="campaign_time_value">Minute</label>
					</div>
					<div class="right_element">
					<?php if($content_get_campaign['minute_send'] == '00'){
							echo '<select id="campaign_minute_value" name="campaign_minute_value" style="width: 172px;" >
										<option value="00" selected>00</option>
										<option value="30">30</option>
									</select>';
						  }else {
							echo '<select id="campaign_minute_value" name="campaign_minute_value" style="width: 172px;" >
										<option value="00" >00</option>
										<option value="30" selected>30</option>
									</select>';  
						  }
						
					?>
					
					</div>					
				</div>
				
			</div>
			
			<div id="campaigns_top_part_right">
			
				<div id="campaign_activation_container" class="row">
					<div class="left_element">
						<label>
							Active :
						</label>
					</div>
					<div class="right_element">
						<?php
							if(strcmp($content_get_campaign['etat'],'Programmed')==0){
								echo('<input type="checkbox" id="campaign_actived" name="campaign_actived" value="oui" checked="true" />');
							}else{
								echo('<input type="checkbox" id="campaign_actived" name="campaign_actived" value="non" />');
							}
						?>
					</div>					
				</div><!-- End .row -->		
				<?php
					if(isset($_GET['stats'])){
						echo '<input type="hidden" id="stats" value="stats" />';
					}else echo '<input type="hidden" id="stats" value="0" />';
				?>
				<div id="campaign_message_count_container" class="row">
					<img id="campaign_load_segment_count_loading" src="ressources/images/loading2.gif" />
					<div id="campaign_message_count_value" class="left_element">
						&nbsp;
					</div>
					<script type="text/javascript">
						campaign_get_segment_count();
					</script>
					<div class="right_element">
						<!-- <input type="button" id="campaign_calculate_segment_count" class="btn btn-default" value="Calculer" onclick="campaign_get_segment_count()" /> -->
					</div>					
				</div><!-- End .row -->	
			
			</div>
			
			<div id="campaigns_etat_operation">
				&nbsp;
			</div>
			
		</div><!-- end div .row -->
		
		<div id="campaigns_middle_part" class="row">	
			
		</div><!-- end div #message_middle_part -->
		
		<div id="campaigns_bottom_part" class="row"></div><!-- end div .row -->
		
		<div id="campaigns_send_part" class="row">
			<input type="button" class="btn btn-primary" value="Enregistrer" style="height:38px;" onclick="campaign_save()" />
			&nbsp;&nbsp;&nbsp;
			<!-- <input type="button" class="btn btn-primary" value="Lancer la campagne" onclick="campaign_lunch()" /> -->
		</div><!-- end div .row -->
		
	</div><!-- end div #message_creation_container -->	

	
	<div id="campaign_errors"></div>
	<div id="campaign_actions_ask"></div>
	
</div><!-- end div .za -->