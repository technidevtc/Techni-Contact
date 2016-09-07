<div class="za">
	<div id="page-heading" class="ng-scope">
		<h1 class="campaigns_h1_left"><i class="fa fa-server"></i> Campages</h1>
		
		<div id="campaigns_add_btn">
			<input type="button" id="campaigns_btn_lst_create" value="Cr&eacute;er une campagne" onclick="javascript:document.location='create-campaign.php'" class="btn btn-primary" style="margin-top:0;"> 
		</div>
		
    </div>
	
	
	<div class="row">
		<div class="col-md-12" style="width:100%;">
			<div class="panel panel-primary">
				<div class="panel-heading">
					<h4>
						Mes campagnes
					</h4>
				</div><!-- end div .panel-heading -->
				
				<div class="panel-body">
					
					<div class="panel-form">
						<?php require_once('campaigns_search_form.php'); ?>
					</div>
					
					<div id="loader_panel-table">
						<img src="<?php echo MARKETING_URL.'ressources/images/lightbox-ico-loading.gif'; ?>" />
					</div>
					
					<div id="panel-table">
						&nbsp;
					</div>
					
					
					<script type="text/javascript">
						campaigns_load_list_show();
					</script>
					
					
					<!-- To open a link in a new window ! -->
					<form id="campaigns_external_formid" method="POST" action="#" target="_blank">
						<input type="hidden" id="campaign_preview_hidden_id" name="campaign_preview_hidden_id" value="" />
					</form>
					
				</div><!-- end div .panel-body -->
				
				
				<div id="campaign_actions_ask"></div>
				
			</div><!-- end div .panel panel-primary -->
			
	
		</div><!-- end div .col-md-12 -->
	</div><!-- end div .row -->
	
	

</div><!-- end div .za -->