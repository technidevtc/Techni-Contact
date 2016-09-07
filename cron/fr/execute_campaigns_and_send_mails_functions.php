<?php
	class send_mail{	
		function is_str_contain($string, $keyword){
			if (empty($string) || empty($keyword)) return false;
				$keyword_first_char = $keyword[0];
				$keyword_length = strlen($keyword);
				$string_length = strlen($string);
			if ($string_length < $keyword_length) return false;
			if ($string_length == $keyword_length) {
			  if ($string == $keyword) return true;
			  else return false;
			}
			if ($keyword_length == 1) {
			  for ($i = 0; $i < $string_length; $i++) {
				if ($keyword_first_char == $string[$i]) {
				  return true;
				}
			  }
			}
			if ($keyword_length > 1) {
			  for ($i = 0; $i < $string_length; $i++) {
				if (($string_length + 1 - $i) >= $keyword_length) {
				  if ($keyword_first_char == $string[$i]) {
					$match = 1;
					for ($j = 1; $j < $keyword_length; $j++) {
					  if (($i + $j < $string_length) && $keyword[$j] == $string[$i + $j]) {
						$match++;
					  }
					  else {
						return false;
					  } }
					if ($match == $keyword_length) {
					  return true;
					}
					}
				}else {
				  return false;
				} }	}
				return false;
		}		
		public function send_mail_platforms_look($id_company,$nom_company){
			
			$total= 1;
			$global_sender_email	= 'z.outarocht@techni-contact.com';
			$global_sender_name		= utf8_decode($content_get_message['name_sender']);
			$global_mail_response	= 'no-reply@techni-contact.com';
			$global_mail_object		= 'Compagnies non envoyé';
			$global_email_content	= "<html>";
				$global_email_content	.= "<head>";
					$global_email_content	.= "<meta charset=\"UTF-8\" />";
				$global_email_content	.= "</head>";
				$global_email_content	.= "<body>";
					$global_email_content	.= 'Bonjour, Mr tristan,';
					$global_email_content	.= '<p>Je tiens à vous informer que le système a trouvé qu\'il y a <b>'.$total.'</b> compagnie(s) à envoyer. </p>';
					$global_email_content	.= '<p>Le problème est dû à la plateforme qui est verrouillé. <br />
													<b>les Ids trouvé sont</b> : '.$id_company.' <br />
													<b>les nom des compagnies trouvé sont </b> : '.$nom_company.' <br /></p>';
				$global_email_content	.= "</body>";
			$global_email_content	.= "</html>";
			$send_etat	= php_mailer_external_send($global_sender_name, $global_sender_email, '', 'z.outarocht@techni-contact.com', '', '', $global_mail_response, '', '', '', '', '', $global_mail_object, $global_email_content);
			if(strcmp($send_etat,1)==0){
				echo('<div style="width:600px; margin:auto; padding-top:10%;">');
					echo('<div style="float:left; width:88px; margin-top:-13px;">');
						echo('<img src="/fr/marketing/ressources/images/icons/ok_big.png" />');
					echo('</div>');
					echo('<div style="float:left; font-size: 20px;">');
						echo('<font color="green">Message envoy&eacute; avec succ&egrave;s</font>');
					echo('</div>');
				echo('</div>');
				$bat_etat	= 'ok';
			}else{
				echo('<center><font color="red"></font></center>');
				echo('<div style="width:600px; margin:auto; padding-top:10%;">');
					echo('<div style="float:left; width:88px; margin-top:-13px;">');
						echo('<img src="/fr/marketing/ressources/images/icons/ko_big.png" />');
					echo('</div>');
					echo('<div style="float:left; font-size: 20px;">');
						echo('<font color="red">Message non envoy&eacute;</font>');
					echo('</div>');
				echo('</div>');
				$bat_etat	= 'ko';
			}
			
			
		}		
		public function check_platforms_locked(){
			$sql_verify_platforms_look  	= "SELECT sync_end FROM marketing_synchronization_flag ";
			$req_verify_platforms_look 		= mysql_query($sql_verify_platforms_look);
			$data_verify_platforms_locked	= mysql_fetch_object($req_verify_platforms_look);
			return $data_verify_platforms_locked->sync_end;
		}		
		public function traitement_adhoc($message_id,$id_compagies){
			
		$date_debut = date('Y/m/d H:i:s');
		$date_send  = date('Y/m/d');
		
		/*
		$sql_verify_date  = "SELECT id FROM marketing_check_send_mail WHERE date_send='".$date_send."' AND id_campaign='".$id_compagies."' ";
		$req_verify_date  = mysql_query($sql_verify_date);
		$rows_verify_date = mysql_num_rows($req_verify_date);
		if($rows_verify_date == '0'){
			$sql_insert = "INSERT INTO `marketing_check_send_mail` (
						`id` ,
						`id_campaign` ,
						`email` ,
						`date_send`
						)VALUES (NULL ,  '$id_compagies',  '',  '$date_send')";
			mysql_query($sql_insert);
		}
		*/
			$query_get_message		="	SELECT 
										m_messages.id,
										m_messages.id_segment,
										m_messages.name,
										m_messages.email_sender,
										m_messages.name_sender,
										m_messages.email_reply,
										m_messages.object,
										m_messages.content
									FROM 
										marketing_messages m_messages
									WHERE 
										m_messages.id=".$message_id." ";
			$req_get_message    = mysql_query($query_get_message);
			$content_get_message   = mysql_fetch_assoc($req_get_message);
			
			$check_email = '0';
			$findme = 'Email';
			
			$sPattern = "/{{(.|\n)*?}}/";
			preg_match_all($sPattern,$content_get_message['object'],$objectMatch);
			$dynamic_fields_total = array();
			$local_count_object = 0;
			
			while(!empty($objectMatch[0][$local_count_object])){
			
				$local_field_infos	= str_replace('{{','', $objectMatch[0][$local_count_object]);
				$local_field_infos	= str_replace('}}','', $local_field_infos);
				$local_explode		= explode('#', $local_field_infos);
			array_push($dynamic_fields_total, array($objectMatch[0][$local_count_object], $local_explode[0], $local_explode[1], '', '', '', ''));
				$local_count_object++;
			}
			
			preg_match_all($sPattern,$content_get_message['content'],$contentMatch);
			
			$local_count_content = 0;
			while(!empty($contentMatch[0][$local_count_content])){
				$local_field_infos	= str_replace('{{','', $contentMatch[0][$local_count_content]);
				$local_field_infos	= str_replace('}}','', $local_field_infos);
				$local_explode		= explode('#', $local_field_infos);
			array_push($dynamic_fields_total, array($contentMatch[0][$local_count_content], $local_explode[0], $local_explode[1], '', '', '', ''));
				$local_count_content++;
			}
			
			$local_loop_global_array	= 0;
			
			while(!empty($dynamic_fields_total[$local_loop_global_array][1])){
					
			$query_get_local_field		="	SELECT 
												name_fo,
												special_field, 
												special_field_query, 
												field_type, 
												field_str_replace,
												id_table 
											FROM 
												marketing_tables_fields 
											WHERE 
												id ='".$dynamic_fields_total[$local_loop_global_array][1]."' ";
			
			$req_get_local_field        = mysql_query($query_get_local_field);
			$content_get_local_field 	= mysql_fetch_assoc($req_get_local_field);
			$dynamic_fields_total[$local_loop_global_array][3]	= $content_get_local_field['special_field'];
			$dynamic_fields_total[$local_loop_global_array][4] 	= $content_get_local_field['special_field_query'];
			$dynamic_fields_total[$local_loop_global_array][5]	= $content_get_local_field['field_type'];
			$dynamic_fields_total[$local_loop_global_array][6]	= $content_get_local_field['field_str_replace'];
			$segment_fields .= $dynamic_fields_total[$local_loop_global_array][2].' AS "'.$dynamic_fields_total[$local_loop_global_array][2].'", ';
			
			$local_loop_global_array++;
			$champ_sql .= $content_get_local_field['name_fo'];	
		
			}
			$id_table = $content_get_local_field['id_table'];
			
			
			
			if($this->is_str_contain($champ_sql, $findme) == false){
				
				if(!empty($id_table)){
					$id_table_final = $id_table;
				}else {
					$id_segement = $content_get_message['id_segment'];
					$sql_id_tbl  = "SELECT id_table FROM marketing_segment WHERE id='".$id_segement."' ";
					$req_id_tbl  = mysql_query($sql_id_tbl);
					$data_id_tbl = mysql_fetch_assoc($req_id_tbl);
					$id_table_final = $data_id_tbl['id_table'];
				}
				
				$sql_mar = "SELECT  name_sql FROM marketing_tables_fields 
							WHERE id_table='$id_table_final' 
							AND name_fo LIKE '%email%' ";
				$req_mar = mysql_query($sql_mar);
				$data_mar= mysql_fetch_object($req_mar);
				
				$segment_fields		.=' '.$data_mar->name_sql.' AS   email , ';
				$segment_fields		= substr($segment_fields, 0, -2);
				
				$query_get_segment_infos	="	SELECT 
													condition_from, 
													condition_where, 
													condition_group 
												FROM 
													marketing_segment 
												WHERE 
												id=".$content_get_message['id_segment']." ";
				
			    $req_get_segment_infos		= mysql_query($query_get_segment_infos);
				$content_get_segment_infos	= mysql_fetch_assoc($req_get_segment_infos);
				
								
			$segment_query				= "SELECT ";
			
			$segment_query				.= " ".$segment_fields." ";
			$segment_query				.= " FROM ".$content_get_segment_infos['condition_from']." ";
			
			if(!empty($content_get_segment_infos['condition_where'])){
				$segment_query			.= " WHERE ".$content_get_segment_infos['condition_where'];
			}
			
			if(!empty($content_get_segment_infos['condition_group'])){
				$segment_query			.= " GROUP BY  ".$content_get_segment_infos['condition_group'];
			}
			echo $segment_query.'<br />';
			$res_segment_query 			= mysql_query($segment_query);
			$res_segment_query_email	= mysql_query($segment_query);
			
			$rows_segment_query_email 	= mysql_num_rows($res_segment_query_email); 
			
			
			while($content_segment_query		= mysql_fetch_assoc($res_segment_query)){
				
				$content_segment_query_email		= mysql_fetch_assoc($res_segment_query_email);
				
				$local_loop_global_array		= 0;
				$variable_temp_objet			= $content_get_message['object'];
				$variable_temp_contenu			= $content_get_message['content'];
				$variable_temp_name_sender		= $content_get_message['name_sender'];
				
				$string1 = "";
				$user_ramdom_key = "1234567890";
				srand((double)microtime()*time());
					for($i=0; $i<30; $i++) {
					$string1 .= $user_ramdom_key[rand()%strlen($user_ramdom_key)];
					}
				$random_key = $string1;				
				
			while(!empty($dynamic_fields_total[$local_loop_global_array][0])){
				
				$local_final_value	= "";
					switch($dynamic_fields_total[$local_loop_global_array][3]){
						case 'families_st':
						case 'families_nd':
						case 'families_rd':
							$local_special_query	= $dynamic_fields_total[$local_loop_global_array][4];
							$local_special_query	= str_replace('######', $content_segment_query[$dynamic_fields_total[$local_loop_global_array][2]],$local_special_query);
							$res_local_special 			= mysql_query($local_special_query);
							$content_local_special		= mysql_fetch_assoc($res_local_special);
							$local_final_value	= $content_local_special["name"];
							
						break;
						case 'no':
							switch($dynamic_fields_total[$local_loop_global_array][5]){
								case 'text':
								case 'number':
									$local_final_value	= $content_segment_query[$dynamic_fields_total[$local_loop_global_array][2]];
								break;
								case 'select':
									$local_select_array	= explode('|||', $dynamic_fields_total[$local_loop_global_array][6]);
									$local_select_array_loop	= 0;
									while($local_select_array[$local_select_array_loop]){									
										$local_select_array_second	= explode('#', $local_select_array[$local_select_array_loop]);
										if(strcmp($local_select_array_second[0],$content_segment_query[$dynamic_fields_total[$local_loop_global_array][2]])==0){
											$local_final_value	= $local_select_array_second[1];
											break;
										}
										$local_select_array_loop++;
									}
								break;
								case 'date';
									if(!empty($content_segment_query[$dynamic_fields_total[$local_loop_global_array][2]])){
									$local_final_value	= date('d/m/Y', $content_segment_query[$dynamic_fields_total[$local_loop_global_array][2]]);
									}else{
										$local_final_value	= "";
									}
								break;
							}				
						break;				
					}//End switch Field Type !
				
				$variable_temp_objet	= str_replace($dynamic_fields_total[$local_loop_global_array][0], $local_final_value, $variable_temp_objet);
				$variable_temp_contenu	= str_replace($dynamic_fields_total[$local_loop_global_array][0], $local_final_value, $variable_temp_contenu);
					$local_loop_global_array++;
				}
			
				$global_mail_object		= utf8_decode($variable_temp_objet);
				$contenu_send 			= $variable_temp_contenu;
			
				$email = $content_segment_query_email['email'];
				
				
				//echo $content_segment_query['email'].'<br /><br />';
				
				$sql_verify_email = "SELECT email,etat,id 
									 FROM marketing_base_emails 
									 WHERE email='".$email."' ";
				$req_verify_email = mysql_query($sql_verify_email);
				$rows_verify_email= mysql_num_rows($req_verify_email);
				if($rows_verify_email == 0){
				$sql_insert = "INSERT INTO marketing_base_emails(id, email, motif, disable_source, etat,
								date_insert, date_last_edit, id_user_add, id_user_edit)
							   values(NULL, '".$email."', 0, '', 'ok',
								NOW(), '0000-00-00 00:00:00', 0, 0)";
			    mysql_query($sql_insert);
			   
			    $sql_max_base_email = "SELECT MAX(id) as total FROM marketing_base_emails";
			    $req_max_base_email = mysql_query($sql_max_base_email);
			    $data_max_base_email= mysql_fetch_object($req_max_base_email);
			    
			    $sql_insert_operation = "INSERT INTO marketing_base_emails_operations(id, id_email, 
											id_campaign, generated_key, email_etat, 
											date_insert, api_etat, special_1,
											special_2, special_3, special_4, special_5)
										VALUES(NULL, '".$data_max_base_email->total."',
											'$id_compagies', '$random_key', 'ok',
											NOW(), '', '', '', '', '', '')";
			    mysql_query($sql_insert_operation);
			    //echo $sql_insert_operation.'<br /><br />';
				
				$contenu_send .='<div><br /><center>Si vous souhaitez vous d&eacute;sinscrire de notre newsletter , <a href="http://techni-contact.com/votre-demande-de-desabonnement.html?tokens='.$data_max_base_email->total.'-'.$random_key.'" target="_blank">cliquez ici</a></center></div>';
				
				$email_final = $email;
				
				
				//$email_final = 'outarocht.zakaria@gmail.com';
				$global_sender_email	= $email_final;
				$global_sender_name		= utf8_decode($content_get_message['name_sender']);
				$global_mail_response	= 'no-reply@techni-contact.com';
				$mailin = new Mailin("https://api.sendinblue.com/v2.0","MnYUwd05CQZy8aWh");
				$to = array($email_final=>"to whom");
				$subject = $content_get_message['object'];
				$from = array($content_get_message['email_sender'],$global_sender_name);
				$html = $contenu_send;
				$replyto = array($content_get_message['email_reply'],"reply to");
				$headers = array("Content-Type"=> "text/html; charset=iso-8859-1","X-Mailin-Tag" => "$random_key");
				
				$sql_ver_emm  = "SELECT email 
								 FROM marketing_check_send_mail 
								 WHERE email ='".$email_final."'
								 AND id_campaign = '".$id_compagies."' ";
				$req_ver_emm  = mysql_query($sql_ver_emm);
				$rows_ver_emm = mysql_num_rows($req_ver_emm);
				
				if(empty($rows_ver_emm)){
					$sql_insert = "INSERT INTO `marketing_check_send_mail` (
						`id` ,
						`id_campaign` ,
						`email` ,
						`date_send`
						)VALUES (NULL ,  '$id_compagies',  '$email_final',  '$date_send')";
					mysql_query($sql_insert);
					var_dump($mailin->send_email($to,$subject,$from,$html,"","","",$replyto,"",$headers));
					
				}else{
					echo 'Email deja envoyé !<br /><br />';
				}
				
				//echo 'ADHOC';
				$etat_send = 'ok';
				}else {
				
				$data_verify_email = mysql_fetch_object($req_verify_email);
				if($data_verify_email->etat == 'ko'){
				$sql_insert_operation = "INSERT INTO marketing_base_emails_operations(id, id_email, 
											id_campaign, generated_key, email_etat, 
											date_insert, api_etat, special_1,
											special_2, special_3, special_4, special_5)
										VALUES(NULL, '".$data_verify_email->id."',
											'$id_compagies', '$random_key', 'ko',
											NOW(), '', '', '', '', '', '')";
			    mysql_query($sql_insert_operation);
				
				$etat_send = 'ko';	
				}else {
				$sql_insert_operation = "INSERT INTO marketing_base_emails_operations(id, id_email, 
											id_campaign, generated_key, email_etat, 
											date_insert, api_etat, special_1,
											special_2, special_3, special_4, special_5)
										VALUES(NULL, '".$data_verify_email->id."',
											'$id_compagies', '$random_key', 'ok',
											NOW(), '', '', '', '', '', '')";
			    mysql_query($sql_insert_operation);
				
				$contenu_send .='<div><br /><center>Si vous souhaitez vous d&eacute;sinscrire de notre newsletter , <a href="http://techni-contact.com/votre-demande-de-desabonnement.html?tokens='.$data_verify_email->id.'-'.$random_key.'" target="_blank">cliquez ici</a></center></div>';
				
				$email_final = $email;
				 
				//$email_final = 'outarocht.zakaria@gmail.com';
				$global_sender_email	= $email_final;
				$global_sender_name		= utf8_decode($content_get_message['name_sender']);
				$global_mail_response	= 'no-reply@techni-contact.com';
				$mailin = new Mailin("https://api.sendinblue.com/v2.0","MnYUwd05CQZy8aWh");
				$to = array($email_final=>"to whom");
				$subject = $content_get_message['object'];
				$from = array($content_get_message['email_sender'],$global_sender_name);
				$html = $contenu_send;
				$replyto = array($content_get_message['email_reply'],"reply to");
				$headers = array("Content-Type"=> "text/html; charset=iso-8859-1","X-Mailin-Tag" => "$random_key");
				
				$sql_ver_emm  = "SELECT email 
								 FROM marketing_check_send_mail 
								 WHERE email ='".$email_final."'
								 AND id_campaign = '".$id_compagies."' ";
				$req_ver_emm  = mysql_query($sql_ver_emm);
				$rows_ver_emm = mysql_num_rows($req_ver_emm);
				
				if(empty($rows_ver_emm)){
					$sql_insert = "INSERT INTO `marketing_check_send_mail` (
						`id` ,
						`id_campaign` ,
						`email` ,
						`date_send`
						)VALUES (NULL ,  '$id_compagies',  '$email_final',  '$date_send')";
					mysql_query($sql_insert);
					var_dump($mailin->send_email($to,$subject,$from,$html,"","","",$replyto,"",$headers));
					
				}else{
					echo 'Email deja envoyé !<br /><br />';
				}
				
				//echo 'ADHOC';
				$etat_send = 'ok';				
				}
				
				}
			
			}
			if($etat_send == 'ok') {
			$sql_compagne = "SELECT type FROM marketing_campaigns WHERE `id` ='$id_compagies' ";
			$req_compagne = mysql_query($sql_compagne);
			$data_compagne= mysql_fetch_object($req_compagne);
			if($data_compagne->type == 'adhoc'){
				$sql_update_companie = "UPDATE `marketing_campaigns` 
											SET `date_last_sent` = NOW() 
											WHERE `id` ='$id_compagies'";
				mysql_query($sql_update_companie);
			}else {
				$sql_update_companie = "UPDATE `marketing_campaigns` 
											SET `date_last_sent` = NOW() ,
											    `date_send` = NOW()
											WHERE `id` ='$id_compagies'";
				mysql_query($sql_update_companie);
			}
			}
			
			
			}else if($this->is_str_contain($champ_sql, $findme) == true){
				
			$segment_fields		= substr($segment_fields, 0, -2);
			
			$query_get_segment_infos	="	SELECT 
													condition_from, 
													condition_where, 
													condition_group 
												FROM 
													marketing_segment 
												WHERE 
												id=".$content_get_message['id_segment']." ";
			$req_get_segment_infos		= mysql_query($query_get_segment_infos);
			$data_get_segment_infos		= mysql_fetch_assoc($req_get_segment_infos);
			
			$segment_query				= "SELECT ";
			$segment_query				.= " ".$segment_fields." ";
			$segment_query				.= " FROM ".$data_get_segment_infos['condition_from']." ";
			
			if(!empty($data_get_segment_infos['condition_where'])){
				$segment_query			.= " WHERE ".$data_get_segment_infos['condition_where'];
			}
			
			if(!empty($data_get_segment_infos['condition_group'])){
				$segment_query			.= " GROUP BY  ".$data_get_segment_infos['condition_group'];
			}
			
			$req_segment = mysql_query($segment_query);
			$data_segment = mysql_fetch_object($req_segment);
			$id_table = $content_get_local_field['id_table'];	
				$sql_mar = "SELECT  name_sql FROM marketing_tables_fields 
							WHERE id_table='$id_table' 
							AND name_fo LIKE '%email%' ";
				$req_mar = mysql_query($sql_mar);
				$data_mar = mysql_fetch_object($req_mar);
				
				$query_get_segment_infos	="	SELECT 
												condition_from, 
												condition_where, 
												condition_group 
											FROM 
												marketing_segment 
											WHERE 
												id=".$content_get_message['id_segment']." ";
				$req_get_segment_infos		= mysql_query($query_get_segment_infos);
				$data_get_segment_infos		= mysql_fetch_assoc($req_get_segment_infos);
				
				$segment_query_email				= "SELECT ";
				$segment_query_email				.= " ".$data_mar->name_sql." as email ";
				$segment_query_email				.= " FROM ".$data_get_segment_infos['condition_from']." ";
				
				
				
				if(!empty($data_get_segment_infos['condition_where'])){
					$segment_query_email			.= " WHERE ".$data_get_segment_infos['condition_where'];
				}
				
				if(!empty($data_get_segment_infos['condition_group'])){
					$segment_query_email			.= " GROUP BY  ".$data_get_segment_infos['condition_group'];
				}
				
			$res_segment_query 			= mysql_query($segment_query);
			$req_query_email 			= mysql_query($segment_query_email);
			echo $res_segment_query.'<br /><br />';
			
			while($content_segment_query		= mysql_fetch_assoc($res_segment_query)){
				$data_query_email 				= mysql_fetch_assoc($req_query_email);
				
				$local_loop_global_array	= 0;
				$variable_temp_objet			= $content_get_message['object'];
				$variable_temp_contenu			= $content_get_message['content'];
				$variable_temp_name_sender		= $content_get_message['name_sender'];
				
				$string1 = "";
				$user_ramdom_key = "1234567890";
				srand((double)microtime()*time());
				for($i=0; $i<30; $i++) {
				$string1 .= $user_ramdom_key[rand()%strlen($user_ramdom_key)];
				}
				$random_key = $string1;
				
				while(!empty($dynamic_fields_total[$local_loop_global_array][0])){
					
					$local_final_value	= "";
					switch($dynamic_fields_total[$local_loop_global_array][3]){
						case 'families_st':
						case 'families_nd':
						case 'families_rd':
							$local_special_query	= $dynamic_fields_total[$local_loop_global_array][4];
							$local_special_query	= str_replace('######', $content_segment_query[$dynamic_fields_total[$local_loop_global_array][2]],$local_special_query);
							$res_local_special 			= mysql_query($local_special_query);
							$content_local_special		= mysql_fetch_assoc($res_local_special);
							$local_final_value	= $content_local_special["name"];
							
						break;
						case 'no':
							switch($dynamic_fields_total[$local_loop_global_array][5]){
								case 'text':
								case 'number':
									$local_final_value	= $content_segment_query[$dynamic_fields_total[$local_loop_global_array][2]];
								break;
								case 'select':
									$local_select_array	= explode('|||', $dynamic_fields_total[$local_loop_global_array][6]);
									$local_select_array_loop	= 0;
									while($local_select_array[$local_select_array_loop]){									
										$local_select_array_second	= explode('#', $local_select_array[$local_select_array_loop]);
										if(strcmp($local_select_array_second[0],$content_segment_query[$dynamic_fields_total[$local_loop_global_array][2]])==0){
											$local_final_value	= $local_select_array_second[1];
											break;
										}
										$local_select_array_loop++;
									}
								break;
								case 'date';
									if(!empty($content_segment_query[$dynamic_fields_total[$local_loop_global_array][2]])){
									$local_final_value	= date('d/m/Y', $content_segment_query[$dynamic_fields_total[$local_loop_global_array][2]]);
									}else{
										$local_final_value	= "";
									}
								break;
							}				
						break;				
					}//End switch Field Type !
				
					$variable_temp_objet			= str_replace($dynamic_fields_total[$local_loop_global_array][0], $local_final_value, $variable_temp_objet);				
					$variable_temp_contenu			= str_replace($dynamic_fields_total[$local_loop_global_array][0], $local_final_value, $variable_temp_contenu);
					$local_loop_global_array++;
				}
				
			$global_mail_object		= utf8_decode($variable_temp_objet);
			$contenu_send 			= $variable_temp_contenu;
			$global_sender_name		= utf8_decode($variable_temp_name_sender);
			$global_mail_response	= 'no-reply@techni-contact.com';
				
				$email  = $data_query_email['email'];
				
				$sql_email_verify_ko = "SELECT  DISTINCT(etat),id							
										FROM 	marketing_base_emails
										WHERE email='".$email."' GROUP BY etat";
										
				$req_email_verify_ko = mysql_query($sql_email_verify_ko);
				$rows_email_verify_ko= mysql_num_rows($req_email_verify_ko);
				if($rows_email_verify_ko == 0){
					$sql_insert = "INSERT INTO marketing_base_emails(id, email, motif, disable_source, etat,
								date_insert, date_last_edit, id_user_add, id_user_edit)
							   values(NULL, '".$email."', 0, '', 'ok',
								NOW(), '0000-00-00 00:00:00', 0, 0)";
					mysql_query($sql_insert);
				
			    $sql_max_base_email = "SELECT MAX(id) as total FROM marketing_base_emails";
			    $req_max_base_email = mysql_query($sql_max_base_email);
			    $data_max_base_email= mysql_fetch_object($req_max_base_email);
			    
			    $sql_insert_operation = "INSERT INTO marketing_base_emails_operations(id, id_email, 
											id_campaign, generated_key, email_etat, 
											date_insert, api_etat, special_1,
											special_2, special_3, special_4, special_5)
										VALUES(NULL, '".$data_max_base_email->total."',
											'$id_compagies', '$random_key', 'ok',
											NOW(), '', '', '', '', '', '')";
				mysql_query($sql_insert_operation);
				
				$contenu_send .='<div><br /><center>Si vous souhaitez vous d&eacute;sinscrire de notre newsletter , <a href="http://techni-contact.com/votre-demande-de-desabonnement.html?tokens='.$data_max_base_email->total.'-'.$random_key.'" target="_blank">cliquez ici</a></center></div>';
				//$email_final = 'z.outarocht@techni-contact.com';
				$email_final = $email;
				$mailin = new Mailin("https://api.sendinblue.com/v2.0","MnYUwd05CQZy8aWh");
				$to = array($email_final=>"to whom");
				$subject = $global_mail_object;
				$from = array($content_get_message['email_sender'],$global_sender_name);
				$html = $contenu_send;
				$replyto = array($content_get_message['email_reply'],"reply to");
				$headers = array("Content-Type"=> "text/html; charset=iso-8859-1","X-Mailin-Tag" => "$random_key");
				
				$sql_ver_emm  = "SELECT email 
								 FROM marketing_check_send_mail 
								 WHERE email ='".$email_final."'
								 AND id_campaign = '".$id_compagies."' ";
				$req_ver_emm  = mysql_query($sql_ver_emm);
				$rows_ver_emm = mysql_num_rows($req_ver_emm);
				
				if(empty($rows_ver_emm)){
					$sql_insert = "INSERT INTO `marketing_check_send_mail` (
						`id` ,
						`id_campaign` ,
						`email` ,
						`date_send`
						)VALUES (NULL ,  '$id_compagies',  '$email_final',  '$date_send')";
					mysql_query($sql_insert);
					var_dump($mailin->send_email($to,$subject,$from,$html,"","","",$replyto,"",$headers));
					
				}else{
					echo 'Email deja envoyé !<br /><br />';
				}
				
				
				$etat_send = 'ok';	
				}else{
					$data_email_verify_ko = mysql_fetch_object($req_email_verify_ko);
					
					if($data_email_verify_ko->etat == 'ko'){
					$sql_insert_operation = "INSERT INTO marketing_base_emails_operations(id, id_email, 
											id_campaign, generated_key, email_etat, 
											date_insert, api_etat, special_1,
											special_2, special_3, special_4, special_5)
										VALUES(NULL, '".$data_email_verify_ko->id."',
											'$id_compagies', '$random_key', 'ko',
											NOW(), '', '', '', '', '', '')";
					
					mysql_query($sql_insert_operation);
					$etat_send = 'ko';	
				}else {
					$sql_insert_operation = "INSERT INTO marketing_base_emails_operations(id, id_email, 
											id_campaign, generated_key, email_etat, 
											date_insert, api_etat, special_1,
											special_2, special_3, special_4, special_5)
										VALUES(NULL, '".$data_email_verify_ko->id."',
											'$id_compagies', '$random_key', 'ok',
											NOW(), '', '', '', '', '', '')";
											
					mysql_query($sql_insert_operation);
					
				$contenu_send .='<div><br /><center>Si vous souhaitez vous d&eacute;sinscrire de notre newsletter , <a href="http://techni-contact.com/votre-demande-de-desabonnement.html?tokens='.$data_email_verify_ko->id.'-'.$random_key.'" target="_blank">cliquez ici</a></center></div>';
				
				//$email_final = 'z.outarocht@techni-contact.com';
				$email_final = $email;
				$mailin = new Mailin("https://api.sendinblue.com/v2.0","MnYUwd05CQZy8aWh");
				$to = array($email_final=>"to whom");
				$subject = $global_mail_object	;
				$from = array($content_get_message['email_sender'],$global_sender_name);
				$html = $contenu_send;
				$replyto = array($content_get_message['email_reply'],"reply to");
				$headers = array("Content-Type"=> "text/html; charset=iso-8859-1","X-Mailin-Tag" => "$random_key");
				
								$sql_ver_emm  = "SELECT email 
								 FROM marketing_check_send_mail 
								 WHERE email ='".$email_final."'
								 AND id_campaign = '".$id_compagies."' ";
				$req_ver_emm  = mysql_query($sql_ver_emm);
				$rows_ver_emm = mysql_num_rows($req_ver_emm);
				
				if(empty($rows_ver_emm)){
					$sql_insert = "INSERT INTO `marketing_check_send_mail` (
						`id` ,
						`id_campaign` ,
						`email` ,
						`date_send`
						)VALUES (NULL ,  '$id_compagies',  '$email_final',  '$date_send')";
					mysql_query($sql_insert);
					var_dump($mailin->send_email($to,$subject,$from,$html,"","","",$replyto,"",$headers));
				}else{
					echo 'Email deja envoyé !<br /><br />';
				}
				
				//echo $contenu_send;
				$etat_send = 'ok';	
				}	
					
				}
				
			}
				  		 
			if($etat_send == 'ok') {
			$sql_compagne = "SELECT type FROM marketing_campaigns WHERE `id` ='$id_compagies' ";
			$req_compagne = mysql_query($sql_compagne);
			$data_compagne= mysql_fetch_object($req_compagne);
			if($data_compagne->type == 'adhoc'){
				$sql_update_companie = "UPDATE `marketing_campaigns` 
											SET `date_last_sent` = NOW() 
											WHERE `id` ='$id_compagies'";
				mysql_query($sql_update_companie);
			}else {
				$sql_update_companie = "UPDATE `marketing_campaigns` 
											SET `date_last_sent` = NOW() ,
											    `date_send` = NOW()
											WHERE `id` ='$id_compagies'";
				mysql_query($sql_update_companie);
			}
			}
			
												
			}
			 
			
			if($i  > 0){
				$email_final			= "z.outarocht@techni-contact.com";
				$global_sender_email	= "z.outarocht@techni-contact.com";
				//$global_sender_email	= "t.henryg@techni-contact.com";
				$global_sender_name		= "Reporting envoie compagne";
				$global_mail_response	= 'no-reply@techni-contact.com';
				$object_send = "Alerte Marketing - ".date('Y/m/d');
				$content_send= "<div>
									<p>Bonjour</b>
									<p>Le Cron Alerte Marketing a été exécuté avec succès</p>
									<p><strong>Date début : ".$date_debut." </strong></p>
								</div>";
				//$send_etat	= php_mailer_external_send($global_sender_name, $global_sender_email, '', $email_final, '', '', $global_mail_response, '', '', '', '', '', $object_send, $content_send);
				
				
				$email_final2			= "t.henryg@techni-contact.com";
				$global_sender_email2	= "t.henryg@techni-contact.com";
				//$global_sender_email	= "t.henryg@techni-contact.com";
				
				//$send_etat	= php_mailer_external_send($global_sender_name, $global_sender_email2, '', $email_final2, '', '', $global_mail_response, '', '', '', '', '', $object_send, $content_send);
			}
			
	}
	
	
	}
	$send_mail = new send_mail();
?>