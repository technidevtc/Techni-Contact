<div class="za">
	<div id="page-heading" class="ng-scope">
		<h1><i class="fa fa-users"></i> Administration</h1>
    </div>
	
	
	<div class="row">
		<div class="col-md-12" style="width:100%;">
			<div class="panel panel-primary">
				<div class="panel-heading">
					<h4>
						Ajouter un utilisateur
					</h4>
				</div>
					
				<div class="panel-body">
					
					<div class="panel-form">
						<?php //require_once('administration_users_form.html'); ?>
					</div>
					
					<div id="loader_panel-table">
						<img src="<?php echo MARKETING_URL.'ressources/images/lightbox-ico-loading.gif'; ?>" />
					</div>
					
					
					<div id="users_add_container">
					
						<form method="post" action="#" onsubmit="return users_add_verification()"> 
							<div id="users_container_content">
						
								<div class="row">
									<div class="p_one_e_left">
										<label for="user_name">
											Nom <span class="srequired">*</span>
										</label>
									</div>
									<div class="p_one_e_right">
										<input type="text" id="user_name" name="user_name" value="" />
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
										<input type="text" id="user_login" name="user_login" value="" />
									</div>
								</div><!-- div .row -->
								
								<br />
								<div class="row">
									<div class="p_one_e_left">
										<label for="user_password">
											Mot de passe <span class="srequired">*</span>
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
										<input type="text" id="user_description" name="user_description" value="" />
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
										<input type="radio" id="user_active_no" name="user_active" value="0" />
										<label for="user_active_no">Non</label>
										
										&nbsp;&nbsp;&nbsp;&nbsp;
										<input type="radio" id="user_active_yes" name="user_active" value="1" checked="true"/>
										<label for="user_active_yes">Oui</label>
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
										<?php require_once('administration_users_load_add_access_pages.php'); ?>
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
										<?php require_once('administration_users_load_add_access_tables.php'); ?>
									</div>
								</div><!-- div .row -->
									
						
							</div><!-- end div #users_tables_container_content -->
							
							
							<div id="users_container_footer">
								<input type="button" id="users_send" value="Valider" class="btn btn-primary" onclick="javascript:users_add_call();" />
							
							</div> <!-- end div #users_add_container_footer -->
							
							
							<div id="users_form_validation_error"></div>
						</form>
					</div><!-- end div #users_add_container -->
						
					
					
					
					
				</div><!-- end div .panel-heading -->
			</div><!-- end div .panel panel-primary -->
			
	
		</div><!-- end div .col-md-12 -->
	</div><!-- end div .row -->
	
	

</div><!-- end div .za -->