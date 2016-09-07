<?php 
	require_once('functions.php'); 
	//require_once('check_session.php');

	if(empty($_SESSION['marketing_user_id'])){
		throw new Exception('<a href="/fr/marketing/login.php">Veuillez vous reconnecter</a>.');
	}
		
	$source_type	= mysql_escape_string($_POST['type']);	
	$source_id		= mysql_escape_string($_POST['id']);	
	
	if(empty($source_type)){
		throw new Exception('Informations manquantes, merci de r&eacute;essayer !');
	}else if(!empty($source_type) && (strcmp($source_type,'segment')==0 && empty($source_id))){
		throw new Exception('Informations manquantes, merci de r&eacute;essayer !');
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
	
		//If it's a segment we have to load the Table informations and check the right Access !
		//If it's a Table we have to check the right Access !
		//Then We load the Fields !
		
		if(strcmp($source_type,'segment')==0){
			$query_get_table_info	="SELECT
											id_table
										FROM
											marketing_segment 
										WHERE 
											id=".$source_id."";
								
			$res_get_table_info 	= $db->query($query_get_table_info, __FILE__, __LINE__);
			$data_get_table_info 	= $db->fetchAssoc($res_get_table_info);
			
			$source_id	= $data_get_table_info['id_table'];
		}
		//Else it's a table we have already it's ID !
		
		//Now we have the ID of the Table in both case then we must check the Table right Access !
		if(strpos($content_get_user_tables_access_permissions['content'],'#'.$source_id.'#')===FALSE){
			throw new Exception('<a href="/fr/marketing/">Vous n\'avez pas le droit d\'acc&eacute;der &agrave; cette table !</a>');
		}
		
		
		//Loading the Fields !
		$res_get_tables_fields_query	= "SELECT
												mt_fields.id,
												mt_fields.name_fo,
												mt_fields.name_sql,
												mt_fields.name_sql_as,
												mt_fields.special_field,
												mt_fields.special_field_query,
												mt_fields.field_type,
												mt_fields.field_str_replace
												
											FROM 
												marketing_tables_fields AS mt_fields 
											WHERE
												mt_fields.id_table=".$source_id." 
											AND
												mt_fields.filter_flag='yes' 
												
											ORDER BY mt_fields.position ASC";	

		$res_get_tables_fields = $db->query($res_get_tables_fields_query, __FILE__, __LINE__);

		if(mysql_num_rows($res_get_tables_fields)>0){
			
			while($content_get_tables_fields	= $db->fetchAssoc($res_get_tables_fields)){
				echo('<div class="table_field" 
						data-field-id="'.$content_get_tables_fields['id'].'"
						data-field-special="'.$content_get_tables_fields['special_field'].'"
						data-field-type="'.$content_get_tables_fields['field_type'].'"
						data-field-select="'.$content_get_tables_fields['field_str_replace'].'"
						data-field-name-sql="'.$content_get_tables_fields['name_sql'].'"
						data-field-name-sql-as="'.$content_get_tables_fields['name_sql_as'].'"
					>');
					echo($content_get_tables_fields['name_fo']);
				echo('</div>');
			}
			
		}//end if(mysql_num_rows($res_get_tables_fields)>0)
	
	
	
	}//End Else check if user have the access to this page !
?>