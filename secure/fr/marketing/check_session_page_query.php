<?php

		$query_get_user_page_permissions	="SELECT 
													id, id_user,
													content
													
												FROM
													marketing_roles_acces_users
												WHERE
													id_user=".$_SESSION['marketing_user_id']."";
													
		$res_get_user_page_permissions 	= $db->query($query_get_user_page_permissions, __FILE__, __LINE__);
											
		$content_get_user_page_permissions = $db->fetchAssoc($res_get_user_page_permissions);


?>