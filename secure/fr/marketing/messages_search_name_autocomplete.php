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
									m_message.name like '%".$f_search."%'
									) 
								";					
		$search_sql_order_by	=  "order by m_message.name ASC ";
		
		$res_get_messages_query	= "SELECT 
										m_message.id,
										m_message.name 
										
									FROM
										marketing_messages AS m_message, 
										marketing_segment AS m_segment 
									WHERE
										m_segment.id=m_message.id_segment
									
									".$search_sql_where."
									
									".$search_sql_order_by."  
									LIMIT 0, 5";	

									
		$res_get_messages = $db->query($res_get_messages_query, __FILE__, __LINE__);
		
		while($content_get_messages	= $db->fetchAssoc($res_get_messages)){
		
			echo('<div class="row" onclick="javascript:messages_fill_this_one(\''.addslashes($content_get_messages['name']).'\')" style="cursor:pointer;">');
			
				echo($content_get_messages['name']);
				
			echo('</div>');
		
		}//End While	
	}

?>