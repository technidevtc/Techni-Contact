<div class="za">
	<div id="page-heading" class="ng-scope">
		<h1 class="base_emails_h1_left"><i class="fa fa-database"></i> Base Email</h1>
		
		
    </div>
	
	
	<div class="row">
		<div class="col-md-12" style="width:100%;">
			<div class="panel panel-primary">
				<div class="panel-heading">
					<h4>
						Fiche email - <?php echo $content_get_email['email']; ?>
					</h4>
				</div><!-- end div .panel-heading -->
				
				<div class="panel-body">
					
					<div class="panel-form">
						<input type="hidden" id="base_email_hidden_id" value="<?php echo $content_get_email['id']; ?>" />
						<?php require_once('base_emails_fiche_form.php'); ?>
					</div>
					
					<div id="loader_panel-table">
						<img src="<?php echo MARKETING_URL.'ressources/images/lightbox-ico-loading.gif'; ?>" style="display:none;" />
					</div>
					
					<div id="panel-table">
						&nbsp;
					</div>
					
					
					<script type="text/javascript">
						
					</script>
					
					
					<!-- To open a link in a new window ! -->
					<form id="base_emails_external_formid" method="POST" action="#" target="_blank">
						<input type="hidden" id="base_emails_preview_hidden_id" name="base_emails_preview_hidden_id" value="<?php echo $email_id; ?>" />
					</form>
					
				</div><!-- end div .panel-body -->
				
				
				<div id="base_emails_actions_ask"></div>
				
			</div><!-- end div .panel panel-primary -->
			
	
		</div><!-- end div .col-md-12 -->
	</div><!-- end div .row -->
	
	

</div><!-- end div .za -->