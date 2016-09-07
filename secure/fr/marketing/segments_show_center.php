<div class="za">
	<div id="page-heading" class="ng-scope">
		<h1 class="segments_h1_left"><i class="fa fa-dashboard"></i> Segments</h1>
		
		<div id="segments_add_btn">
			<input type="button" id="segments_btn_lst_create" value="Cr&eacute;er un segment" onclick="javascript:document.location='segments-create.php'" class="btn btn-primary" style="margin-top:0;"> 
		</div>
		
    </div>
	
	
	<div class="row">
		<div class="col-md-12" style="width:100%;">
			<div class="panel panel-primary">
				<div class="panel-heading">
					<h4>
						Mes segments
					</h4>
				</div><!-- end div .panel-heading -->
				
				<div class="panel-body">
					
					<div class="panel-form">
						<?php require_once('segments_search_form.php'); ?>
					</div>
					
					<div id="loader_panel-table">
						<img src="<?php echo MARKETING_URL.'ressources/images/lightbox-ico-loading.gif'; ?>" />
					</div>
					
					<div id="panel-table">
						&nbsp;
					</div>
					
					
					<script type="text/javascript">
						segments_load_list_show();
					</script>
					
					
					<!-- To open a link in a new window ! -->
					<form id="segments_external_formid" method="GET" action="#" target="_self"></form>
				</div><!-- end div .panel-body -->
				
				
				<div id="segment_actions_ask"></div>
				
			</div><!-- end div .panel panel-primary -->
			
	
		</div><!-- end div .col-md-12 -->
	</div><!-- end div .row -->
	
	

</div><!-- end div .za -->