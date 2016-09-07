<?php 
	require_once('functions.php'); 
	require_once('check_session.php');

	//Check the permissions to access to this page
	//Every page or module have a different ID !
	$page_permission_id = "10";
	require_once('check_session_page.php');
	
	
	$message_id			= mysql_escape_string($_GET['id']);
	
	//Get the "id_table" From the Segment linked to the Message !
	$query_get_message	= "SELECT
								m_message.id, 
								m_message.date_creation, 
								m_message.name AS message_name, 
								m_message.object, 
								m_message.email_sender, 
								m_message.name_sender, 
								m_message.email_reply, 
								m_message.content, 
								m_message.id_segment,
								
								m_segment.name AS segment_name, 
								m_segment.id_table, 

								m_campaigns.name AS campaigne_name
								
								
							FROM 
								marketing_messages m_message
								INNER JOIN marketing_segment AS m_segment	ON m_segment.id=m_message.id_segment
								LEFT JOIN marketing_campaigns AS m_campaigns	ON m_campaigns.id_message=m_message.id 
							WHERE 
								m_message.id=".$message_id."";

	$res_get_message = $db->query($query_get_message, __FILE__, __LINE__);	
	$content_get_message	= $db->fetchAssoc($res_get_message);
	
	if(empty($content_get_message['id'])){
		//Inexistant Message !
		header('location: /fr/marketing/my-messages.php');
	}
	
	//Check the User's role Table
	if(strpos($content_get_user_tables_access_permissions['content'],'#'.$content_get_message['id_table'].'#')===FALSE){
		header('location: /fr/marketing/my-messages.php');
	}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="ISO-8859-1">
	<title>Modifier un message Techni-contact.com</title>
	<meta name="description" content="Techni-contact.com Marketing">
  
	<?php require_once('header_tags.php'); ?>
	<script type="text/javascript" src="ressources/js/messages.js"></script>
	<script type="text/javascript" src="ressources/js/message/jquery.zclip.min.js"></script>
	<script type="text/javascript">
		$( document ).ready(function() {
			//Init the Fields and show the WYSIWYG;
			setTimeout(function(){ message_select_segment_edit_page(); }, 1000);
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
					<?php require_once('messages_edit_center.php'); ?>
				</div>
				<div id="content_footer">
					<?php require_once('footer.php'); ?>
				</div>
			</div>
		</div>
		
	</div>
</body>
</html>