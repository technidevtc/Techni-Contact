<?php

	//To get the user's role table access

	//L'utilisation
	$query_get_user_tables_access_permissions	="SELECT 
														id, id_user,
														content
														
													FROM
														marketing_roles_acces_tables
													WHERE
														id_user=".$_SESSION['marketing_user_id']." 
													AND 
														type='segment'";
												
	$res_get_user_tables_access_permissions 	= $db->query($query_get_user_tables_access_permissions, __FILE__, __LINE__);
										
	$content_get_user_tables_access_permissions = $db->fetchAssoc($res_get_user_tables_access_permissions);
	
//print_r($content_get_user_tables_access_permissions);
	
	//L'export
	$query_get_user_tables_export_permissions	="SELECT 
														id, id_user,
														content
														
													FROM
														marketing_roles_acces_tables
													WHERE
														id_user=".$_SESSION['marketing_user_id']."
													AND 
														type='export'";
												
	$res_get_user_tables_export_permissions 	= $db->query($query_get_user_tables_export_permissions, __FILE__, __LINE__);
										
	$content_get_user_tables_export_permissions = $db->fetchAssoc($res_get_user_tables_export_permissions);
?>