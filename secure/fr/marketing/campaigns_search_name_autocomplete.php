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
									m_campaigns.name like '%".$f_search."%'
									) 
								";					
		$search_sql_order_by	=  "order by m_campaigns.name ASC ";
		
		$res_get_campaigns_query	= "SELECT 
										m_campaigns.id,
										m_campaigns.name 
										
									FROM
										marketing_campaigns AS m_campaigns, 
										marketing_messages AS m_messages
									WHERE
										m_campaigns.id_message=m_messages.id
									
									".$search_sql_where."
									
									".$search_sql_order_by."  
									LIMIT 0, 5";	

									
		$res_get_campaigns = $db->query($res_get_campaigns_query, __FILE__, __LINE__);
		
		while($content_get_campaigns	= $db->fetchAssoc($res_get_campaigns)){
		
			echo('<div class="row" onclick="javascript:campaigns_fill_this_one(\''.addslashes($content_get_campaigns['name']).'\')" style="cursor:pointer;">');
			
				echo($content_get_campaigns['name']);
				
			echo('</div>');
		
		}//End While	
	}

?>