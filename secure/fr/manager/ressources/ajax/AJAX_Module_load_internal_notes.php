<?php

if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

$db = DBHandle::get_instance();
$user = new BOUser();

header("Content-Type: text/plain; charset=utf-8");
mb_convert_variables("UTF-8","ASCII,UTF-8,ISO-8859-1,CP1252",$_POST);
//$o = array("data" => array());

try {
  if (!$user->login())
    throw new Exception("Votre session a expiré, veuillez vous identifier à nouveau après avoir rafraîchi votre page.");
	
	
	switch ($_POST["type"]){
    
		case "Module_load_internal_notes":
		
			if(strcmp($_POST["method"],'select_notes_and_attach_by_id')=='0'){
			
				switch ($_POST["internal_notes_id_context"]){
				
					//Case 2 is like Case 6
					case "1":
					case "2":
					case "3":
					case "4":
					case "5":
					case "6":
					
							$res = $db->query(" 	SELECT
														i.*, bo_u.login as operator_name
													FROM
														internal_notes i,
														bo_users bo_u
													WHERE
														i.operator=bo_u.id	
													AND
														i.context = ".$_POST["internal_notes_id_context"]."
													AND
														i.id_reference = '".$_POST["internal_notes_reference_id"]."'
													ORDER BY
														timestamp desc", __FILE__, __LINE__);

							while($internal_notes = $db->fetchAssoc($res)){

								echo('<li>');
									echo('<div class="header">');
										echo('Message de '.$internal_notes['operator_name'].' envoy&eacute; le '.date("d-m-Y H:i:s", $internal_notes['timestamp']));
									echo('</div>');
									
									//Start Search for Attachments !

										$res_attachments = $db->query(" SELECT
																		id, timestamp,
																		context, item_id,
																		filename, alias_filename,
																		extension
																	FROM
																		uploaded_files
																	WHERE
																		item_id=".$internal_notes['id']."
																	AND
																		context like '%".$_POST["context"]."'
																	ORDER BY
																		timestamp desc", __FILE__, __LINE__);
											
										if(mysql_num_rows($res_attachments)>0){
											echo('<div class="clip icon attach"></div>');
											echo('<div class="files" style="display:none;">');
											
											while($internal_notes_attachments = $db->fetchAssoc($res_attachments)) {
												$attachment_full_path = BO_UPLOAD_DIR.''.$uploadContextData[$_POST['module_bo_upload_dir']]['dir'].''.$internal_notes_attachments['filename'].'.'.$internal_notes_attachments['extension'];
												echo('<a href="'.$attachment_full_path.'" class="_blank">'.$internal_notes_attachments['alias_filename'].'.'.$internal_notes_attachments['extension'].'</a>');
												echo('<br />');
											}
											echo('</div>');
										}//end if(mysql_num_rows($res_attachments)>0){	
										
									//End Search for Attachments !
									
									echo('<div class="content">');
										echo($internal_notes['content']);
									echo('</div>');
								echo('</li>');
							}//end while($internal_notes = $db->fetchAssoc($res)){
					
					break;
					
					
					
					case "1":
					
						//Search in the supplier_order get order_id and sup_id where id=$_POST["internal_notes_reference_id"]
						//Execute the second query for the internal_notes !
						
					break;
					
					
				
				
				
				
				
				
				}//end switch ($_POST["type"]){
				
				
			

			
			
			}
			
			
	
		break;
		
	}
	
}catch(Exception $e){

	echo($e);
}

?>