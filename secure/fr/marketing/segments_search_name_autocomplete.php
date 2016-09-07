<?php	
	require_once('functions.php'); 

	//Getting params
	$f_search						= mysql_escape_string($_POST['f_search']);
		
	if(!empty($_SESSION['marketing_user_id']) && !empty($f_search)){
	
		/*
		$search_sql_fileds		= ", MATCH (m_seg.name) AGAINST (\"".$f_search."\" IN BOOLEAN 	MODE) as name_score ";	
		$search_sql_where		=" AND (
									MATCH (m_seg.name) AGAINST (\"".$f_search."\" IN BOOLEAN MODE)
									)
								";					
		$search_sql_order_by	=  "order by ((name_score*0.8)) DESC ";
		*/
		
		$search_sql_where		=" AND (
									m_seg.name like '%".$f_search."%'
									) 
								";					
		$search_sql_order_by	=  "order by m_seg.name ASC ";
		
		$res_get_segments_query	= "SELECT 
										m_seg.id,
										m_seg.name,
										m_seg.type,
										m_seg.results_count,
										m_seg.date_creation,
										m_seg.date_last_execution_start,
										
										m_tables.id AS table_id,
										m_tables.name_fo AS table_name
										
										".$search_sql_fileds."
										
									FROM
										marketing_segment m_seg, 
										marketing_tables m_tables
									WHERE
										m_seg.id_table=m_tables.id
									
									".$search_sql_where."
									
									".$search_sql_order_by."  
									LIMIT 0, 5";	

									
		$res_get_segments = $db->query($res_get_segments_query, __FILE__, __LINE__);
		
		while($content_get_segments	= $db->fetchAssoc($res_get_segments)){
		
			echo('<div class="row" onclick="javascript:segment_fill_this_one(\''.addslashes($content_get_segments['name']).'\')" style="cursor:pointer;">');
			
				echo($content_get_segments['name']);
				
			echo('</div>');
		
		}//End While	
	}

?>