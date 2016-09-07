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
	
	//Looking for the elements then check if the user have the right to access to the actual Table on the Fetch !
	
	$query_get_tables_info	="SELECT
									id,
									name_fo
								FROM
									marketing_tables 
								ORDER BY name_fo ";
							
	$res_get_tables_info 	= $db->query($query_get_tables_info, __FILE__, __LINE__);
	
	echo('<select id="select_source_data" onchange="javascript:segments_source_choosed(\'table\')" size="5" style="height:83px;">');
		echo('<option value="" selected="true">&nbsp;</option>');
		
	while($data_get_tables_info 	= $db->fetchAssoc($res_get_tables_info)){
		
		//if the user have the right to access to the actual Table
		if(strpos($content_get_user_tables_access_permissions['content'],'#'.$data_get_tables_info['id'].'#')!==FALSE){
			echo('<option value="'.$data_get_tables_info['id'].'">'.$data_get_tables_info['name_fo'].'</option>');
		}	
	}//End while !
	
	echo('</select>');
	
	
	
	
	}//End Else check if user have the access to this page !
?>