<?php 
	require_once('functions.php'); 
	//require_once('check_session.php');

	if(empty($_SESSION['marketing_user_id'])){
		throw new Exception('<a href="/fr/marketing/login.php">Veuillez vous reconnecter</a>.');
	}
		
		
	//Check the permissions to access to this Page
	//Every page or module have a different ID !	
	require_once('check_session_page_query.php');
	$page_permission_id = "3";
	$page_id	= '#'.$page_permission_id.'#';
	
	//Check the permissions to access to this Table
	require_once('check_session_table_query.php');
	//$table_id	= '#'.$segment_used_table.'#';
	
	if(strpos($content_get_user_page_permissions['content'],$page_id)===FALSE){
		echo('<a href="/fr/marketing/">Vous n\'avez pas le droit d\'acc&eacute;der &agrave; cette page !</a>');
	}else{
	
	
		$segment_id		= mysql_escape_string($_POST['segment_id']);
		
		
		//Looking for the elements then check if the user have the right to access to the actual Table on the Fetch !
		/*
		$query_get_segments_info	="SELECT 
											
										m_seg.id, m_seg.id_table,
										m_seg.name 
											
									FROM
											marketing_segment m_seg
									ORDER BY name ";
								*/
		$query_get_segments_info	="SELECT 
											
										m_seg.id, m_seg.id_table,
										m_seg.name 
											
									FROM
											marketing_segment m_seg
									ORDER BY date_creation DESC ";						
								
		$res_get_segments_info 	= $db->query($query_get_segments_info, __FILE__, __LINE__);
		
		echo('<select id="select_source_data" onchange="javascript:segments_source_choosed(\'segment\')" size="5" style="height:83px;">');
			if(empty($segment_id)){
				echo('<option value="" selected="true">&nbsp;</option>');
			}
			
		while($data_get_segments_info 	= $db->fetchAssoc($res_get_segments_info)){
			
			//if the user have the right to access to the actual Table
			if(strpos($content_get_user_tables_access_permissions['content'],'#'.$data_get_segments_info['id_table'].'#')!==FALSE){
				echo('<option value="'.$data_get_segments_info['id'].'"');
				if(strcmp($data_get_segments_info['id'],$segment_id)==0){ echo(' selected="true" '); }
				echo('>'.$data_get_segments_info['name'].'</option>');
			}	
		}//End while !
		
		echo('</select>');
	
	
	
	
	}//End Else check if user have the access to this page !
?>