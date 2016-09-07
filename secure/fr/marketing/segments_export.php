<?php 
	require_once('functions.php'); 
	require_once('check_session.php');

	//Check the permissions to access to this page
	//Every page or module have a different ID !
	$page_permission_id = "3";
	require_once('check_session_page.php');
	
	//Get the Segment ID
	$segment_id						= mysql_escape_string($_GET['id']);
	if(!empty($segment_id)){
		//header('Location: '.MARKETING_URL.'my-segments.php');
		//exit;
		
		//Get the segment Table ID
		$query_get_segment_informations	="SELECT 
											
											m_seg.id_table, m_seg.id_table,
											m_seg.name, 	m_seg.condition_from,
											m_seg.condition_where, 	m_seg.condition_group,
											m_seg.type
											
										FROM
											marketing_segment m_seg
											
										WHERE
											m_seg.id=".$segment_id." ";
													
		$res_get_segment_informations 	= $db->query($query_get_segment_informations, __FILE__, __LINE__);
											
		$data_get_segment_informations = $db->fetchAssoc($res_get_segment_informations);
		
		//Check if this user have the right to edit this "table => segment" ! 
		if(strpos($content_get_user_tables_access_permissions['content'],'#'.$data_get_segment_informations['id_table'].'#')===FALSE){	
			header('Location: '.MARKETING_URL.'my-segments.php');
			exit;
		}
	
	}
	
	
	
	
	
	/*
	//Check the permissions to access to this Table
	require_once('check_session_table_query.php');
	$table_id	= '#'.$segment_used_table.'#';
	
	if(strpos($content_get_user_tables_access_permissions['content'],$table_id)===FALSE){
		header('Location: '.MARKETING_URL.'my-segments.php');
		exit;
	}*/
?>
<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<title>Exporter un segment - Techni-contact.com</title>
	<meta name="description" content="Techni-contact.com Marketing">
  
	<?php require_once('header_tags.php'); ?>
	<script type="text/javascript" src="ressources/js/segments.js"></script>
	<script type="text/javascript" src="ressources/js/segments_row_build.js"></script>
	<script type="text/javascript" src="ressources/js/segments_export.js"></script>
	<script type="text/javascript">
		$( document ).ready(function() {
			<?php
			
				if(!empty($segment_id)){
					echo('segment_export_change_source(\'segments\', '.$segment_id.')');
				}else{
					echo('segment_export_change_source(\'tables\')');
				}
			
			?>			
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
					<?php require_once('segments_export_center.php'); ?>
				</div>
				<div id="content_footer">
					<?php require_once('footer.php'); ?>
				</div>
			</div>
		</div>
		
	</div>
</body>
</html>