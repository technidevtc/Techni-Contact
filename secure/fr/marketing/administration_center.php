<div class="za">
	<div id="page-heading" class="ng-scope">
		<h1><i class="fa fa-users"></i> Administration</h1>
    </div>
	
	
	<div class="row">
		<div class="col-md-12" style="width:100%;">
			<div class="panel panel-primary">
				<div class="panel-heading">
					<h4>
						Utilisateurs
					</h4>
				</div>
				
				<div id="administration_add_user_btn">
					<input type="button" id="products_btn_lst_create" value="Ajouter un utilisateur" onclick="javascript:document.location='administration-users-create.php'" class="btn btn-primary" style="margin-top:0;"> 
				</div>
				
				<div class="panel-body">
					
					<!-- <div class="panel-form">
						<?php //require_once('administration_users_form.html'); ?>
					</div> -->
					
					<div id="loader_panel-table">
						<img src="<?php echo MARKETING_URL.'ressources/images/lightbox-ico-loading.gif'; ?>" />
					</div>
					
					<div id="panel-table">
						&nbsp;
					</div>
					
					<input type="hidden" name="fps" id="fps" value="1" />
					<input type="hidden" name="fpp" id="fpp" value="10" />
					<script type="text/javascript">
						load_administration_users();
					</script>
					
					
					<!-- To open a link in a new window ! -->
					<form id="administration_external_formid" method="GET" action="#" target="_self"></form>
					
				</div><!-- end div .panel-heading -->
			</div><!-- end div .panel panel-primary -->
			
	
		</div><!-- end div .col-md-12 -->
	</div><!-- end div .row -->
	
	

</div><!-- end div .za -->