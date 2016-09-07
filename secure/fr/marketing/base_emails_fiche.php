<?php 
	require_once('functions.php'); 
	require_once('check_session.php');

	//Check the permissions to access to this page
	//Every page or module have a different ID !
	$page_permission_id = "17";
	require_once('check_session_page.php');
	
	$email_id				= mysql_escape_string($_GET['id']);
	
	if(empty($email_id)){
		header('Location: /fr/marketing/base-email.php');
	}
	
	//Get Email Basic Informations
	$res_get_email_query	= "SELECT
									m_base_emails.id, 
									m_base_emails.email,
									m_base_emails.etat, 
									m_base_emails.date_insert, 
									m_base_emails.date_last_edit, 
									m_base_emails.disable_source,

									m_b_e_motifs.label 
								
								FROM 
									marketing_base_emails AS m_base_emails
										LEFT JOIN marketing_base_email_motifs AS m_b_e_motifs ON m_base_emails.motif=m_b_e_motifs.id
								WHERE 
									m_base_emails.id=".$email_id."";	
							
	$res_get_email 			= $db->query($res_get_email_query, __FILE__, __LINE__);
	$content_get_email		= $db->fetchAssoc($res_get_email);
	if(empty($content_get_email['id'])){
		header('Location: /fr/marketing/base-email.php');
	}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="ISO-8859-1">
	<title>Fiche Email Techni-contact.com</title>
	<meta name="description" content="Techni-contact.com Marketing">
  
	<?php require_once('header_tags.php'); ?>
	<script type="text/javascript" src="ressources/js/base_emails.js"></script>
	
	<script type="text/javascript">
		$( document ).ready(function() {
			base_emails_load_one_basic_infos();
			
			base_emails_load_one_detailled_infos();

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
					<?php require_once('base_emails_fiche_center.php'); ?>
				</div>
				<div id="content_footer">
					<?php require_once('footer.php'); ?>
				</div>
			</div>
		</div>
		
	</div>
</body>
</html>