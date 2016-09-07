<?php 
	require_once('functions.php'); 
	require_once('check_session.php');
	
	//Check the permissions to access to this page
	//Every page or module have a different ID !
	$page_permission_id = "4";
	require_once('check_session_page.php');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="ISO-8859-1">
  <title>Administration cr&eacute;ation d'utilisateur - Techni-contact.com</title>
  <meta name="description" content="Techni-contact.com Marketing">
  
  <?php require_once('header_tags.php'); ?>
  <script type="text/javascript" src="ressources/js/administration.js"></script>
  
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
					<?php require_once('administration_users_create_center.php'); ?>
				</div>
				<div id="content_footer">
					<?php require_once('footer.php'); ?>
				</div>
			</div>
		</div>
		
	</div>
</body>
</html>