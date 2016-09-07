<div class="za">
	<div id="page-heading" class="ng-scope">
		<h1 class="messages_h1_left"><i class="fa fa-envelope"></i> Messages</h1>
		
    </div>
	
	
	<div class="row">
		<div class="col-md-12" style="width:100%;">
			<div class="panel panel-primary">
				<div class="panel-heading">
					<h4>
						Modifier un message
					</h4>
				</div><!-- end div .panel-heading -->
				
				
				
				
			</div><!-- end div .panel panel-primary -->
			
	
		</div><!-- end div .col-md-12 -->
	</div><!-- end div .row -->
	

			
	<div id="message_creation_container" class="row">
		<div id="message_top_part" class="row">
			<div id="message_top_part_left">
				<div class="row">
					<input type="hidden" id="message_hidden_id" value="<?php echo $content_get_message['id']; ?>" />
					
					<form id="message_form_preview" action="messages-preview.php" method="POST" target="_blank">
						<input type="hidden" id="message_preview_hidden_id" name="message_preview_hidden_id" value="<?php echo $content_get_message['id']; ?>" />
					</form>
					
					<form id="message_form_test" action="messages-test.php" method="POST" target="_blank">
						<input type="hidden" id="message_test_hidden_id" name="message_test_hidden_id" value="<?php echo $content_get_message['id']; ?>" />
						<input type="hidden" id="message_test_email_hidden_id" name="message_test_email_hidden_id" value="" />
					</form>
							
					
					<div class="left_element">
						<label for="message_title">
							Titre du message :
						</label>
					</div>
					<div class="right_element">
						<input type="text" id="message_title" name="message_title" class="message_field" value="<?php echo utf8_decode($content_get_message['message_name']); ?>" />
					</div>					
				</div><!-- End .row -->
				
				<div class="row">
					<div class="left_element">
						<label for="sender_email">
							Mail exp&eacute;diteur :
						</label>
					</div>
					<div class="right_element">
						<input type="text" id="sender_email" name="sender_email" class="message_field" value="<?php echo $content_get_message['email_sender']; ?>" />
					</div>					
				</div><!-- End .row -->
				
				<div class="row">
					<div class="left_element">
						<label for="sender_name">
							Nom exp&eacute;diteur :
						</label>
					</div>
					<div class="right_element">
						<input type="text" id="sender_name" name="sender_name" class="message_field" value="<?php echo utf8_decode($content_get_message['name_sender']); ?>" />
					</div>					
				</div><!-- End .row -->
				
				<div class="row">
					<div class="left_element">
						<label for="reply_email">
							Mail r&eacute;ponse :
						</label>
					</div>
					<div class="right_element">
						<input type="text" id="reply_email" name="reply_email" class="message_field" value="<?php echo utf8_decode($content_get_message['email_reply']); ?>" />
					</div>					
				</div><!-- End .row -->
				
				<div class="row">
					<div class="left_element">
						<label for="segment_list_select">
							Segment cible :
						</label>
					</div>
					<div class="right_element">

						<select id="message_segment_selection" class="message_field">
							
						<?php							

							echo('<option value="'.$content_get_message['id_segment'].'">'.utf8_decode($content_get_message['segment_name']).'</option>');

						?>
						</select>
						
					</div>					
				</div><!-- End .row -->
				
				<div class="row">
					<div class="left_element">
						<label for="message_object">
							Objet :
						</label>
					</div>
					<div class="right_element">
						<input type="text" id="message_object" name="message_object" class="message_field" value="<?php echo utf8_decode($content_get_message['object']); ?>" />
					</div>					
				</div><!-- End .row -->
				
			</div>
			
			
			<div id="message_top_part_right_container">
				<img id="message_load_fields_loading" src="ressources/images/loading2.gif" />
				<div id="message_top_part_right" placeholder="Veuillez selectionner un segment">
					<span style="opacity:0.5;"><br />&nbsp;&nbsp;Veuillez selectionner un segment</span>
				</div>
				
			
				<input type="button" id="message_hidden_copy" value="Valider la copie" class="btn btn-default" />
				
				<div id="message_tester_container">
					<div id="message_tester_left">
						<select id="message_emails_test_select">
						<?php
						
							$res_get_emails_test_query	= "SELECT
																id, email
																
															FROM
																marketing_messages_mails_test
															WHERE 
																actived='yes'
															ORDER BY email ASC 
																";
	
	
							$res_get_emails_test = $db->query($res_get_emails_test_query, __FILE__, __LINE__);
							
							while($content_get_emails_test	= $db->fetchAssoc($res_get_emails_test)){
								echo('<option value="'.$content_get_emails_test['id'].'">'.$content_get_emails_test['email'].'</option>');
							}
						?>
						</select>
					</div>
					<div id="message_tester_right">
						<input type="button" id="message_test_btn" onclick="message_tester()" value="Tester" class="btn btn-default" />
					</div>
				</div><!-- end div #message_tester_container -->
			</div><!-- end div #message_top_part_right_container -->
			
			<div id="message_etat_operation">
				&nbsp;
			</div>
			
		</div><!-- end div .row -->
		

		<div id="message_preview_container_btn">
			<input type="button" id="message_preview" class="btn btn-default" value="Preview" onclick="message_preview()" />
		</div>
					
		<div id="message_middle_part" class="row">
			
			<?php
				//require_once('wysiwyg_editor.php');
				require_once('ckeditor.php');
			?>
	
			
		</div><!-- end div #message_middle_part -->
		
		<div id="message_bottom_part" class="row"></div><!-- end div .row -->
		
		<div id="message_send_part" class="row">
			<input type="button" class="btn btn-primary" value="Enregistrer" onclick="message_save()" />
		</div><!-- end div .row -->
		
	</div><!-- end div #message_creation_container -->	

	
	<div id="message_errors"></div>
	<div id="message_actions_ask"></div>
	
</div><!-- end div .za -->