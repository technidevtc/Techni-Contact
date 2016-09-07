<?php 

try{
	//Importing file function mail send
	require("../../../includes/fr/classV3/phpmailer_2014/PHPMailerAutoload.php");

	//Send the mail using a external file (Client)
	require("../../../includes/fr/classV3/phpmailer_2014/Email_functions_external_mail_send.php");
	
	require_once('functions.php'); 
	//require_once('check_session.php');

	if(empty($_SESSION['marketing_user_id'])){
		//throw new Exception('<a href="/fr/marketing/login.php">Veuillez vous reconnecter</a>.');
		echo('<a href="/fr/marketing/login.php">Veuillez vous reconnecter</a>.');
		die;
	}
	
	$message_id				= mysql_escape_string($_POST['message_test_hidden_id']);
	$email_id				= mysql_escape_string($_POST['message_test_email_hidden_id']);
	
	//Save Log !
	$message_log	= "Message: Test message ID: ".$message_id." For the E-mail ID: ".$email_id;
	$query_marketing_log	="INSERT INTO marketing_users_history(id, action,
									id_user, action_date)
								VALUES(NULL, '".$message_log."',
									".$_SESSION['marketing_user_id'].", NOW())";
				
	$res_marketing_log 	= $db->query($query_marketing_log, __FILE__, __LINE__);
	
	
	$segment_fields		= "";
	$segment_query		= "";
	
	if(!empty($message_id)){
		//Get the Message Informations !
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
										m_messages.id=".$message_id." 
									";
				
		$res_get_message 		= $db->query($query_get_message, __FILE__, __LINE__);
		$content_get_message	= $db->fetchAssoc($res_get_message);
		
		//Get email infos
		$query_get_mail		="	SELECT 
									id, 
									email 
								FROM 
									marketing_messages_mails_test 
								WHERE 
									id=".$email_id." 
								";
				
		$res_get_mail 			= $db->query($query_get_mail, __FILE__, __LINE__);
		$content_get_mail		= $db->fetchAssoc($res_get_mail);
		
		
		
		$sPattern = "/{{(.|\n)*?}}/";

		preg_match_all($sPattern,$content_get_message['object'],$objectMatch);
		
		$dynamic_fields_total = array();
		
		//We will look for the values of "Object"
		$local_count_object = 0;
		while(!empty($objectMatch[0][$local_count_object])){
			echo '11';
			$local_field_infos	= str_replace('{{','', $objectMatch[0][$local_count_object]);
			$local_field_infos	= str_replace('}}','', $local_field_infos);
			$local_explode		= explode('#', $local_field_infos);
			
			array_push($dynamic_fields_total, array($objectMatch[0][$local_count_object], $local_explode[0], $local_explode[1], '', '', '', ''));
			$local_count_object++;
		
		}
		
		
		preg_match_all($sPattern,$content_get_message['content'],$contentMatch);
		
		//We will look for the values of "Content"
		$local_count_content = 0;
		while(!empty($contentMatch[0][$local_count_content])){
			
			$local_field_infos	= str_replace('{{','', $contentMatch[0][$local_count_content]);
			$local_field_infos	= str_replace('}}','', $local_field_infos);
			$local_explode		= explode('#', $local_field_infos);
			
			array_push($dynamic_fields_total, array($contentMatch[0][$local_count_content], $local_explode[0], $local_explode[1], '', '', '', ''));
			$local_count_content++;
		}
		

		//Get All the dynamic fields !
		
		$local_loop_global_array	= 0;
		while(!empty($dynamic_fields_total[$local_loop_global_array][1])){
			echo 'dans while';
			//Lookin for the Field Other Informations from the DataBase !
			
			$query_get_local_field		="	SELECT 
												special_field, 
												special_field_query, 
												field_type, 
												field_str_replace 
											FROM 
												marketing_tables_fields 
											WHERE 
												id=".$dynamic_fields_total[$local_loop_global_array][1]." 
										";
					
			$res_get_local_field 		= $db->query($query_get_local_field, __FILE__, __LINE__);
			$content_get_local_field	= $db->fetchAssoc($res_get_local_field);
			
			
			$dynamic_fields_total[$local_loop_global_array][3]	= $content_get_local_field['special_field'];
			$dynamic_fields_total[$local_loop_global_array][4] 	= $content_get_local_field['special_field_query'];
			$dynamic_fields_total[$local_loop_global_array][5]	= $content_get_local_field['field_type'];
			$dynamic_fields_total[$local_loop_global_array][6]	= $content_get_local_field['field_str_replace'];
			
			//Build the Query Fields !
			$segment_fields .= $dynamic_fields_total[$local_loop_global_array][2].' AS "'.$dynamic_fields_total[$local_loop_global_array][2].'", ';
			
			$local_loop_global_array++;
		}
		
		//Remove the Two last chars !
		$segment_fields		= substr($segment_fields, 0, -2);
		
		//Get the Segment Informations to Build the Query !
		$query_get_segment_infos		="	SELECT 
												condition_from, 
												condition_where, 
												condition_group 
											FROM 
												marketing_segment 
												
											WHERE 
												id=".$content_get_message['id_segment']." 
									";
		$res_get_segment_infos 		= $db->query($query_get_segment_infos, __FILE__, __LINE__);
		$content_get_segment_infos	= $db->fetchAssoc($res_get_segment_infos);
		
		$segment_query				= "SELECT ";
		$segment_query				.= " ".$segment_fields." ";
		$segment_query				.= " FROM ".$content_get_segment_infos['condition_from']." ";
		
		if(!empty($content_get_segment_infos['condition_where'])){
			$segment_query			.= " WHERE ".$content_get_segment_infos['condition_where'];
		}
		
		if(!empty($content_get_segment_infos['condition_group'])){
			$segment_query			.= " GROUP BY  ".$content_get_segment_infos['condition_group'];
		}
		
		//$segment_query				.= " LIMIT 17, 1";
		$segment_query				.= " LIMIT 0, 1";

		$res_segment_query 			= $db->query($segment_query, __FILE__, __LINE__);
		$content_segment_query		= $db->fetchAssoc($res_segment_query);
		/*$local_loop_global_array	= 0;
		while(!empty($dynamic_fields_total[$local_loop_global_array][0])){
			echo($dynamic_fields_total[$local_loop_global_array][0].' - ');
			echo($dynamic_fields_total[$local_loop_global_array][1].' - ');
			echo($dynamic_fields_total[$local_loop_global_array][2].' - ');
			echo($dynamic_fields_total[$local_loop_global_array][3].' - ');
			echo($dynamic_fields_total[$local_loop_global_array][4].' - ');
			echo($dynamic_fields_total[$local_loop_global_array][5].' - ');
			echo($dynamic_fields_total[$local_loop_global_array][6].' - ');
			echo('<br />###############<br />');
			$local_loop_global_array++;
		}*/
		//echo($segment_query.'<br /><br /><br />##########<br /><br />');
		//Looking for the Special Fields and Replace the content with the correct information 
		//We do the same for the other Fields type "Text, Integer, Select And Date"
		$local_loop_global_array	= 0;
		while(!empty($dynamic_fields_total[$local_loop_global_array][0])){
				$local_final_value	= "";
				switch($dynamic_fields_total[$local_loop_global_array][3]){
					case 'families_st':
					case 'families_nd':
					case 'families_rd':
						$local_special_query	= $dynamic_fields_total[$local_loop_global_array][4];
						$local_special_query	= str_replace('######', $content_segment_query[$dynamic_fields_total[$local_loop_global_array][2]],$local_special_query);
						$res_local_special 			= $db->query($local_special_query, __FILE__, __LINE__);
						$content_local_special		= $db->fetchAssoc($res_local_special);
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
				$content_get_message['object']	= str_replace($dynamic_fields_total[$local_loop_global_array][0], $local_final_value, $content_get_message['object']);
				$content_get_message['content']	= str_replace($dynamic_fields_total[$local_loop_global_array][0], $local_final_value, $content_get_message['content']);
			$local_loop_global_array++;
		}
		
			
		$global_sender_email	= $content_get_message['email_sender'];
		$global_sender_name		= utf8_decode($content_get_message['name_sender']);
		
		$global_mail_response	= $content_get_message['email_reply'];
		
		$global_mail_object		= utf8_decode($content_get_message['object']);
		
		
		$global_email_content	= "<html>";
			$global_email_content	.= "<head>";
				$global_email_content	.= "<meta charset=\"UTF-8\" />";
			$global_email_content	.= "</head>";
			$global_email_content	.= "<body>";
			
				$global_email_content	.= $content_get_message['content'];
			
			$global_email_content	.= "</body>";
		$global_email_content	.= "</html>";
		
		
		//echo $global_email_content;
		
		//($header_from_name, $header_from_email, $header_send1_name, $header_send1_email, $header_send2_name, $header_send2_email, $header_reply1_email, $header_reply1_name, $header_copy1_email, $header_copy1_name, $header_copy2_email, $header_copy2_name, $subject, $message_to_send)

		
		$send_etat	= php_mailer_external_send($global_sender_name, $global_sender_email, '', $content_get_mail['email'], '', '', $global_mail_response, '', '', '', '', '', $global_mail_object, $global_email_content);

		
		if(strcmp($send_etat,1)==0){
			echo('<div style="width:600px; margin:auto; padding-top:10%;">');
				echo('<div style="float:left; width:88px; margin-top:-13px;">');
					echo('<img src="/fr/marketing/ressources/images/icons/ok_big.png" />');
				echo('</div>');
				echo('<div style="float:left; font-size: 20px;">');
					echo('<font color="green">Message envoy&eacute; avec succ&egrave;s</font>');
				echo('</div>');
			echo('</div>');
			
			echo('<script type="text/javascript">');
				echo('setInterval(function(){ self.close(); }, 5000);');
			echo('</script>');
			
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
		
		//Save the logs in the DataBase
		$global_email_headers	= "Sender Email: ".$global_sender_email."\n\r";
		$global_email_headers	.= "Sender Name: ".$global_sender_name."\n\r";
		$global_email_headers	.= "Response: ".$global_mail_response."\n\r";
		$global_email_headers	.= "Object: ".$global_mail_object."\n\r";
		$global_email_headers	.= "Sent email: ".$content_get_mail['email']."\n\r";
		
		$global_email_headers	= str_replace("'","\'",$global_email_headers);
		$global_email_content	= str_replace("'","\'",$global_email_content);
		
		$query_marketing_log	="INSERT INTO marketing_messages_test_history(id, id_email,
									hearders, content, 
									date_sent, created_user,
									etat)
								VALUES(NULL, ".$email_id.",
									'".$global_email_headers."', '".$global_email_content."',
									NOW(), ".$_SESSION['marketing_user_id'].",
									'".$bat_etat."')";
		
		$res_marketing_log 	= $db->query($query_marketing_log, __FILE__, __LINE__);
		
	}else{
		echo('Vous avez des erreurs dans votre formulaire !');
		die;
	}
	
}catch(Exception $e){
	echo('Error: '.$e);
}
?>