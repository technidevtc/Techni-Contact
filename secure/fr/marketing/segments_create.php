<?php 
	require_once('functions.php'); 
	require_once('check_session.php');

	//Check the permissions to access to this page
	//Every page or module have a different ID !
	$page_permission_id = "2";
	require_once('check_session_page.php');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="ISO-8859-1">
	<title>Cr&eacute;er un segment - Techni-contact.com</title>
	<meta name="description" content="Techni-contact.com Marketing">
  
	<?php require_once('header_tags.php'); ?>
	<script type="text/javascript" src="ressources/js/segments.js"></script>
	<script type="text/javascript" src="ressources/js/segments_row_build.js"></script>
	<script type="text/javascript" src="ressources/js/segments_create.js"></script>
	<script type="text/javascript">
		$( document ).ready(function() {
			//segment_build_prebuild_groupe();
			
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
					<?php require_once('segments_create_center.php'); ?>
				</div>
				<div id="content_footer">
					<?php require_once('footer.php'); ?>
				</div>
			</div>
		</div>
		
	</div>
</body>
</html>