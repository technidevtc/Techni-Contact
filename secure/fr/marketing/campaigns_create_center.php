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
				
					<input type="hidden" id="campaigns_hidden_id" value="" />
					
					<div class="left_element">
						<label for="message_title">
							Titre de la campagne :
						</label>
					</div>
					<div class="right_element">
						<input type="text" id="campaign_title" name="campaign_title" class="campaign_field" />
					</div>					
				</div><!-- End .row -->
				
				<div class="row">
					<div class="left_element">
						<label for="sender_email">
							Message associ&eacute; :
						</label>
					</div>
					<div class="right_element">
					
						<select id="campaign_message_selection" class="campaign_field" onchange="campaign_get_segment_count()">
							<option value="">&nbsp;</option>
						<?php
							
							$query_get_messages	= "SELECT
														m_messages.id, 
														m_messages.name, 
														m_messages.id_segment, 
														m_segment.id_table 
													FROM
														marketing_messages AS m_messages
														INNER JOIN marketing_segment AS m_segment ON m_messages.id_segment=m_segment.id
													WHERE
														m_messages.id NOT IN (
																	SELECT
																		m_campaigns.id_message
																	FROM
																		marketing_campaigns m_campaigns
																)
													ORDER BY m_messages.date_creation";

							$res_get_messages = $db->query($query_get_messages, __FILE__, __LINE__);	
							
							//Show only the segment to the user that have the right to use !
							//require_once('check_session_table_query.php');

			
							while($content_get_messages	= $db->fetchAssoc($res_get_messages)){
								if(strpos($content_get_user_tables_access_permissions['content'],'#'.$content_get_messages['id_table'].'#')!==FALSE){
									echo('<option value="'.$content_get_messages['id'].'">'.utf8_decode($content_get_messages['name']).'</option>');
								}
							}//End while
							
						
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
						
						<input type="checkbox" id="campaign_type" name="campaign_type" value="trigger" />

					</div>					
				</div><!-- End .row -->
				
				<div id="campaign_date_container" class="row">
					<div class="left_element">
						<label for="campaign_date_value">Date</label>
					</div>
					<div class="right_element">
						<input type="date" id="campaign_date_value" name="campaign_date_value" placeholder="JJ-MM-AAAA" style="width:175px;" />
					</div>					
				</div><!-- End .row -->
				
				<div id="campaign_time_container" class="row">
					<div class="left_element">
						<label for="campaign_time_value">Heure</label>
					</div>
					<div class="right_element">
						<input type="number" id="campaign_time_value" name="campaign_time_value" />
					</div>					
				</div><!-- End .row -->
				
				<div id="campaign_time_container" class="row">
					<div class="left_element">
						<label for="campaign_time_value">Minute</label>
					</div>
					<div class="right_element">
					<select id="campaign_minute_value" name="campaign_minute_value" style="width: 172px;" >
						<option value="00">00</option>
						<option value="30">30</option>
					</select>
					</div>					
				</div><!-- End .row -->
				
				
			</div>
		
			<div id="campaigns_top_part_right">
			
				<div id="campaign_activation_container" class="row">
					<div class="left_element">
						<label>
							Active :
						</label>
					</div>
					<div class="right_element">
						<input type="checkbox" id="campaign_actived" name="campaign_actived" value="non" />
					</div>					
				</div><!-- End .row -->		

				<div id="campaign_message_count_container" class="row">
					<img id="campaign_load_segment_count_loading" src="ressources/images/loading2.gif" />
					<div id="campaign_message_count_value" class="left_element">
						&nbsp;
					</div>
					<div class="right_element">
						<input type="button" id="campaign_calculate_segment_count" class="btn btn-default" value="Calculer" onclick="campaign_get_segment_count()" />
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