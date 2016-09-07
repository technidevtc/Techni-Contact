<?php 
	require_once('functions.php'); 
	require_once('check_session.php');

	//Check the permissions to access to this page
	//Every page or module have a different ID !
	$page_permission_id = "13";
	require_once('check_session_page.php');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="ISO-8859-1">
	<title>Cr&eacute;er une campagne Techni-contact.com</title>
	<meta name="description" content="Techni-contact.com Marketing">
  
	<?php require_once('header_tags.php'); ?>
	<script type="text/javascript" src="ressources/js/campaigns.js"></script>
	
	
	<script type="text/javascript" src="ressources/js/jquery_checkboxes.js"></script>
	<link rel="stylesheet" href="ressources/css/jquery_switcher.css" type="text/css" media="screen" charset="utf-8" />
	
	<script type="text/javascript">
		$( document ).ready(function() {
			
			
			
			
			//Campaign Actived			
			var campaign_actived_checkbox = ($('#campaign_actived:checkbox')).iphoneStyle({				
				checkedLabel: 'Oui',
				uncheckedLabel: 'Non',
				onChange: function(elem, value){
					if(value.toString()=="true"){
						var campaign_actived_value="oui";
					}else{
						var campaign_actived_value="non";
					}
					document.getElementById('campaign_actived').value	= campaign_actived_value;
				}
			});
			
			//Campaign Type			
			var campaign_type_checkbox = ($('#campaign_type:checkbox')).iphoneStyle({
				labelOffClass: 'iPhoneCheckLabelTrigger',
				checkedLabel: 'AD Hoc',
				uncheckedLabel: 'Trigger',
				onChange: function(elem, value){
					if(value.toString()=="true"){
						var campaign_type_value="adhoc";
					}else{
						var campaign_type_value="trigger";
					}
					document.getElementById('campaign_type').value	= campaign_type_value;
					campaign_type_change(campaign_type_value);
				}
			});

		});
	</script>
</head>
<body ng-app="ZTechnicoAppMarketing">
	<div id="container_global">
		<div id="global_header">
			<?php require_once('header_top.php'); ?>
		</div>
		<div id="container_middle_global">
			<div id="content_left">
				<?php require_once('left_menu.php'); ?>
			</div>
			<div id="content_middle_global">
				<div id="content_middle">
					<?php require_once('campaigns_create_center.php'); ?>
				</div>
				<div id="content_footer">
					<?php require_once('footer.php'); ?>
				</div>
			</div>
		</div>
		
	</div>
</body>
</html>