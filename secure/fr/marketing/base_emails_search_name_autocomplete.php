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
		
		$search_sql_where		=" m_base_emails.email like '%".$f_search."%' ";					
		$search_sql_order_by	=  "order by m_base_emails.email ASC ";
		
		$res_get_base_emails_query	= "SELECT 
											m_base_emails.id, 
											m_base_emails.email 
											
										FROM
											marketing_base_emails AS m_base_emails
										WHERE
									
										".$search_sql_where."
										
										".$search_sql_order_by."  
										LIMIT 0, 5";	

									
		$res_get_base_emails = $db->query($res_get_base_emails_query, __FILE__, __LINE__);
		
		while($content_get_base_emails	= $db->fetchAssoc($res_get_base_emails)){
		
			echo('<div class="row" onclick="javascript:base_emails_fill_this_one(\''.addslashes($content_get_base_emails['email']).'\')" style="cursor:pointer;">');
			
				echo($content_get_base_emails['email']);
				
			echo('</div>');
		
		}//End While	
	}

?>