<div class="za">
	<div id="page-heading" class="ng-scope">
		<h1><i class="fa fa-users"></i> Administration</h1>
    </div>
	
	
	<div class="row">
		<div class="col-md-12" style="width:100%;">
			<div class="panel panel-primary">
				<div class="panel-heading">
					<h4>
						Modifier un utilisateur
					</h4>
				</div>
					
				<div id="administration_delete_user_btn">
					<input type="button" id="products_btn_lst_delete" value="Supprimer cet utilisateur" onclick="users_ask_delete()" class="btn btn-primary red-btn" style="margin-top:0;"> 
				</div>
				
				<div class="panel-body">
					
					
					<div id="loader_panel-table">
						<img src="<?php echo MARKETING_URL.'ressources/images/lightbox-ico-loading.gif'; ?>" />
					</div>
					
					
					<div id="users_add_container">
					
						<?php
							$id			= mysql_escape_string($_GET['id']);
							
							if(!empty($id)){
							
								//Check existing user !
								$check_user_query		= "SELECT
																id, name,
																description, login,
																date_creation, active
															FROM
																marketing_users
															WHERE
																id=".$id."";
								$res_get_user 			= $db->query($check_user_query, __FILE__, __LINE__);
								
								if(mysql_num_rows($res_get_user)>0){
									$content_get_user	= $db->fetchAssoc($res_get_user);
								?>
								
									<form method="post" action="#" onsubmit="return users_edit_verification()"> 
										<input type="hidden" id="user_id" name="user_id" value="<?php echo $content_get_user['id']; ?>" /> 
										<div id="users_container_content">
									
											<div class="row">
												<div class="p_one_e_left">
													<label for="user_name">
														Nom <span class="srequired">*</span>
													</label>
												</div>
												<div class="p_one_e_right">
													<input type="text" id="user_name" name="user_name" value="<?php echo utf8_decode($content_get_user['name']); ?>" />
												</div>
											</div><!-- div .row -->
											
											<br />
											<div class="row">
												<div class="p_one_e_left">
													<label for="user_login">
														Login <span class="srequired">*</span>
													</label>
												</div>
												<div class="p_one_e_right">
													<input type="text" id="user_login" name="user_login" value="<?php echo $content_get_user['login']; ?>" DISABLED="TRUE" />
												</div>
											</div><!-- div .row -->
											
											<br />
											<div class="row">
												<div class="p_one_e_left">
													<label for="user_password">
														Mot de passe <span class="srequired">*</span>
														<font style="font-size:73%">(vide=non changement)</font>
													</label>
												</div>
												<div class="p_one_e_right">
													<input type="password" id="user_password" name="user_password" value="" />
												</div>
											</div><!-- div .row -->
											
											<br />
											<div class="row">
												<div class="p_one_e_left">
													<label for="user_cpassword">
														Confirmation mot de passe <span class="srequired">*</span>
													</label>
												</div>
												<div class="p_one_e_right">
													<input type="password" id="user_cpassword" name="user_cpassword" value="" />
												</div>
											</div><!-- div .row -->
											
											<br />
											<div class="row">
												<div class="p_one_e_left">
													<label for="user_description">
														Description
													</label>
												</div>
												<div class="p_one_e_right">
													<input type="text" id="user_description" name="user_description" value="<?php echo utf8_decode($content_get_user['description']); ?>" />
												</div>
											</div><!-- div .row -->
											
											<br />
											<div class="row">
												<div class="p_one_e_left">
													<label>
														Active
													</label>
												</div>
												<div class="p_one_e_right">
													<?php
													
													if(strcmp($content_get_user['active'],'yes')==0){
														echo('<input type="radio" id="user_active_no" name="user_active" value="0" />
													<label for="user_active_no">Non</label>');
														echo('&nbsp;&nbsp;&nbsp;&nbsp;');
														echo('<input type="radio" id="user_active_yes" name="user_active" value="1" checked="true" />
													<label for="user_active_yes">Oui</label>');
													}else{
														echo('<input type="radio" id="user_active_no" name="user_active" value="0" checked="true" />
													<label for="user_active_no">Non</label>');
														echo('&nbsp;&nbsp;&nbsp;&nbsp;');
														echo('<input type="radio" id="user_active_yes" name="user_active" value="1" />
													<label for="user_active_yes">Oui</label>');
													}
													
													?>
												
												</div>
											</div><!-- div .row -->
									
										</div><!-- end div #users_container_content -->
										
										<br /><br />
										<div id="users_access_container_content">

											<div class="row">
												<div class="p_one_e_left">
													<label for="user_name">
														R&ocirc;les d'acc&egrave;s <span class="srequired">*</span>
													</label>
												</div>
												<div class="p_one_e_right">
													<?php require_once('administration_users_load_edit_access_pages.php'); ?>
												</div>
											</div><!-- div .row -->
												
									
										</div><!-- end div #users_access_container_content -->
										
										<br /><br />
										<div id="users_tables_container_content">
									
											<div class="row">
												<div class="p_one_e_left">
													<label for="user_name">
														R&ocirc;les d'utilisation <span class="srequired">*</span>
													</label>
												</div>
												<div class="p_one_e_right">
													<?php require_once('administration_users_load_edit_access_tables.php'); ?>
												</div>
											</div><!-- div .row -->
												
									
										</div><!-- end div #users_tables_container_content -->
										
										
										<div id="users_container_footer">
											<input type="button" id="users_send" value="Valider" class="btn btn-primary" onclick="javascript:users_edit_call();" />
										
										</div> <!-- end div #users_add_container_footer -->
										
										
										<div id="users_form_validation_error"></div>
									</form>
								
								<?php
								}else{
									echo('Utilisateur inexistant !');
									echo('<br /><br />');
									echo('<a href="administration.php">Retour.</a>');
								}//end else if(mysql_num_rows($res_get_user)>0)
			
			
							}else{
								echo('Utilisateur inexistant !');
								echo('<br /><br />');
								echo('<a href="administration.php">Retour.</a>');
							}
						?>
						
					</div><!-- end div #users_add_container -->
						
					
					
					
					
				</div><!-- end div .panel-heading -->
			</div><!-- end div .panel panel-primary -->
			
	
		</div><!-- end div .col-md-12 -->
	</div><!-- end div .row -->
	
	

</div><!-- end div .za -->