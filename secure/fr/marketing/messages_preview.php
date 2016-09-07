<?php 
	require_once('functions.php'); 
	//require_once('check_session.php');

	if(empty($_SESSION['marketing_user_id'])){
		//throw new Exception('<a href="/fr/marketing/login.php">Veuillez vous reconnecter</a>.');
		echo('<a href="/fr/marketing/login.php">Veuillez vous reconnecter</a>.');
		die;
	}
	
	$message_id				= mysql_escape_string($_POST['message_preview_hidden_id']);
		
	//Save Log !
	$message_log	= "Message: Preview message ID: ".$message_id;
	$query_marketing_log	="INSERT INTO marketing_users_history(id, action,
									id_user, action_date)
								VALUES(NULL, '".$message_log."',
									".$_SESSION['marketing_user_id'].", NOW())";
				
	$res_marketing_log 	= $db->query($query_marketing_log, __FILE__, __LINE__);
	
	

	
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
		
		echo('<html>');
			echo('<head>');
			
				echo('<style>');
					echo('#message_headers_table td{ ');
						echo(' border:1px solid #c9c9c9; ');
					echo('}');
				echo('</style>');
				
			echo('</head>');
			echo('<body>');
			
				//Mail Headers
				echo('<div>');
					echo('<center>');
						echo('<b>En-t&ecirc;te</b>');
						echo('<br /><br />');
					
						echo('<table id="message_headers_table" style="border:1px solid #E2DBDB;">');
							echo('<tr>');
								echo('<td style="width: 200px;">');
									echo('Titre du message : ');
								echo('</td>');
								
								echo('<td>');
									echo($content_get_message['name']);
								echo('</td>');
							echo('</tr>');
							
							echo('<tr>');
								echo('<td style="width: 200px;">');
									echo('Mail exp&eacute;diteur : ');
								echo('</td>');
								
								echo('<td>');
									echo($content_get_message['email_sender']);
								echo('</td>');
							echo('</tr>');
							
							echo('<tr>');
								echo('<td style="width: 200px;">');
									echo('Nom exp&eacute;diteur : ');
								echo('</td>');
								
								echo('<td>');
									echo($content_get_message['name_sender']);
								echo('</td>');
							echo('</tr>');
							
							echo('<tr>');
								echo('<td style="width: 200px;">');
									echo('Mail r&eacute;ponse : ');
								echo('</td>');
								
								echo('<td>');
									echo($content_get_message['email_reply']);
								echo('</td>');
							echo('</tr>');
							
							echo('<tr>');
								echo('<td style="width: 200px;">');
									echo('Objet : ');
								echo('</td>');
								
								echo('<td>');
									echo($content_get_message['object']);
								echo('</td>');
							echo('</tr>');
						
						echo('</table>');
						
					echo('</center>');
				echo('</div>');
				
				//Mail Content
				echo('<br /><br /><br />');
				echo('<div>');
					echo('<center>');
						echo('<b>Contenu</b>');
						echo('<br /><br />');
					echo('</center>');
					
					echo($content_get_message['content']);
				
				echo('</div>');
				
			
			echo('</body>');
		echo('</html>');
		
	}else{
		echo('Vous avez des erreurs dans votre formulaire !');
		die;
	}
	
?>