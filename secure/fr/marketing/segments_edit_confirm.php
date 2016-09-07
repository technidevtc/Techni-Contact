<?php 
	require_once('functions.php'); 
	//require_once('check_session.php');

	if(empty($_SESSION['marketing_user_id'])){
		throw new Exception('<a href="/fr/marketing/login.php">Veuillez vous reconnecter</a>.');
	}
	
	
			$segment_used_table		= mysql_escape_string($_POST['segment_used_table']);
	
	//Check the permissions to access to this Page
	//Every page or module have a different ID !	
	require_once('check_session_page_query.php');
	$page_permission_id = "2";
	$page_id	= '#'.$page_permission_id.'#';
	
	//Check the permissions to access to this Table
	require_once('check_session_table_query.php');
	$table_id	= '#'.$segment_used_table.'#';
	
	
	//Check if the user have the right to access to this page !
	if(strpos($content_get_user_page_permissions['content'],$page_id)===FALSE){
		echo('<a href="/fr/marketing/">Vous n\'avez pas le droit d\'acc&eacute;der &agrave; cette page !</a>');
	}else if(strpos($content_get_user_tables_access_permissions['content'],$table_id)===FALSE){
		echo('<a href="/fr/marketing/">Vous n\'avez pas le droit d\'acc&eacute;der &agrave; cette table !</a>');
	}else if(!empty($_SESSION['marketing_user_id'])){
		
		
		$segment_id				= mysql_escape_string($_POST['segment_id']);
		$segment_typology		= mysql_escape_string($_POST['segment_typology']);
		$segment_name			= mysql_escape_string($_POST['segment_name']);
		$content				= $_POST['content'];
		
		//Convert the converted "&"
		$content				= str_replace('#amp;','&amp;', $content);
		
		
		//The name of the param in the Webservice !
		$wb_segment_name		="segment_edit";
		
		//We declare the vars that we will use in the next blocks
		//The id of the segment that we will create 
		//Because we need first to save the segment and build the groupes and fields
		//$segment_id						= '';
		//We gonna use it to update the segment 
		$segment_condition_select		= '';
		//We gonna use it to update the segment
		$segment_condition_from			= '';
		//We gonna use it to update the segment
		$segment_condition_where		= '';
		//We gonna use it to update the segment
		$segment_condition_group		= '';
		
		//To get the ID of the last inserted group !
		$local_group_id					= '';
		
		//To use it in the Fields Loop
		$local_field_count				= 0;
		
		//to save the local Query informations
		$local_query_where				= '';
		
		//to save the global Query informations		
		$global_query_where				= '';
		
		//Etat execution 
		$global_etat					= '';
		
		if(!empty($segment_used_table) && !empty($segment_typology) && !empty($segment_name) && !empty($content)){
			try{
				
				if(strcmp($segment_typology, 'dynamique')==0){
					$segment_typology	= 'dynamique';
				}else{
					$segment_typology	= 'statique';
				}
				
				
				//Insert the segment that we will update in the end of the execution !
			
				//Reading the XML ..
				$xml = new SimpleXMLElement($content);
				
				
				$groupes= $xml->xpath("/contents/content[@id='".$wb_segment_name."']/groupes/groupe");
				if(!empty($groupes)){
					
					//Get the Table Informations
					$query_get_table_info	="SELECT
													name_sql, 
													condition_from,
													condition_group
												FROM
													marketing_tables
												WHERE
													id=".$segment_used_table."";
											
					$res_get_table_info 	= $db->query($query_get_table_info, __FILE__, __LINE__);
					$data_get_table_info 	= $db->fetchAssoc($res_get_table_info);
					
					$segment_condition_from = " \n ".$data_get_table_info['name_sql']." ".$data_get_table_info['condition_from']." \n ";
					
					if(!empty($data_get_table_info['condition_group'])){
						$segment_condition_group= " ".$data_get_table_info['condition_group']." \n ";
					}
					
					//Creation de segment 
					//Insert into DB the Segment to get it's ID because we gonna use it in groups and fields
					$query_segment_update	="	UPDATE marketing_segment  SET
													id_table=".$segment_used_table.", 
													name='".$segment_name."', 
													type='".$segment_typology."', 
													condition_from='".$segment_condition_from."', 
													condition_group='".$segment_condition_group."', 
													date_change=NOW() 
												WHERE 
													id=".$segment_id."";
													
					$res_segment_update 	= $db->query($query_segment_update, __FILE__, __LINE__);
					
					
					//Delete all the segment previous Groups and Fields !
					//Because we gonna create new one's !
					//First of all we have to delete the Fields of every Group Then delete the groups !
					$query_segment_groups_delete	= "SELECT 
															id
														FROM	
															marketing_groupes 
														WHERE 
															id_segment=".$segment_id."";
															
					$res_segment_groups_delete 			= $db->query($query_segment_groups_delete, __FILE__, __LINE__);
					
					//Fetch all the groups and delete all the fields !
					while($data_segment_groups_delete 	= $db->fetchAssoc($res_segment_groups_delete)){
						
						$query_segment_fields_delete	= "DELETE FROM marketing_groupes_fields 
															WHERE 
																id_groupe=".$data_segment_groups_delete['id']."";
						$res_segment_fields_delete		= $db->query($query_segment_fields_delete, __FILE__, __LINE__);
						
					}//End while fetching groups to delete it's Fields !
					
					//Delete the groups !
					$query_segment_groups_delete	= "DELETE FROM marketing_groupes WHERE id_segment=".$segment_id."";
					$res_segment_groups_delete		= $db->query($query_segment_groups_delete, __FILE__, __LINE__);
					
					
					
					
					//Init the local query variable 
					$local_query_where		= "\n ";
					$local_groupe_count		= 1;
					while(list( , $groupes_A) = each($groupes)){
						
						
						//Groupe Name
						$local_groupe_name		= $xml->xpath("/contents/content[@id='".$wb_segment_name."']/groupes/groupe[@id='".$local_groupe_count."']/name");
						list( , $local_groupe_name_A) = each($local_groupe_name);
						
						//Groupe Operation
						$local_groupe_operation	= $xml->xpath("/contents/content[@id='".$wb_segment_name."']/groupes/groupe[@id='".$local_groupe_count."']/operation");
						list( , $local_groupe_operation_A) = each($local_groupe_operation);
						
						//Groupe Fields Count
						$local_fields_count		= $xml->xpath("/contents/content[@id='".$wb_segment_name."']/groupes/groupe[@id='".$local_groupe_count."']/fields//@count");;
						list( , $local_fields_count_A) = each($local_fields_count);
						
						
						//Start The query !
						if(strcmp($local_groupe_operation_A,'1')==0){
							$local_query_where .= " ( \n";
						}else if(strcmp($local_groupe_operation_A,'AND')==0){
							$local_query_where .= " AND ( \n";
						}else if(strcmp($local_groupe_operation_A,'OR')==0){
							$local_query_where .= " OR ( \n";
						}
						
						
						//Ignore the "'"
						$local_groupe_name_A	= str_replace("'","\'",$local_groupe_name_A);
						
						//Insert groupe informations Then loop the fields and insert them all !
						$query_group_insert	="	INSERT INTO marketing_groupes  
													(id, id_segment,
													name, what)
													
												VALUES
													(NULL, ".$segment_id.", 
													'".$local_groupe_name_A."', '".$local_groupe_operation_A."')";
							
						$res_group_insert 	= $db->query($query_group_insert, __FILE__, __LINE__);
						
						$local_group_id	= mysql_insert_id();
						//$local_group_id = 1;
						if(empty($local_group_id)){
							throw new Exception('Erreur dans la cr&eacute;ation de groupe du segment !');
						}	

						
						//echo('Gp Number: <b>'.$local_groupe_count.'</b> Operation: '.$local_groupe_operation_A.' Name: '.$local_groupe_name_A.' Field Count :'.$local_fields_count_A.'<br />');
						
						
						//Init the fields Count !
						$local_field_count	= 0;
						//Loop to fetch all the Fields !
						while($local_field_count!=$local_fields_count_A){
							//Increment the fields count !
							$local_field_count++;
							
							//Get all the informations of the field
							//We know that is the maximum of infos 
							//and we can have less than that !
							
							//Field Operation
							$local_field_operation		= $xml->xpath("/contents/content[@id='".$wb_segment_name."']/groupes/groupe[@id='".$local_groupe_count."']/fields/field[@id='".$local_field_count."']/operation");
							list( , $local_field_operation_A) = each($local_field_operation);
						
							//Field ID
							$local_field_id			= $xml->xpath("/contents/content[@id='".$wb_segment_name."']/groupes/groupe[@id='".$local_groupe_count."']/fields/field[@id='".$local_field_count."']/field_id");
							list( , $local_field_id_A) = each($local_field_id);
							
							//Field Special
							$local_field_special			= $xml->xpath("/contents/content[@id='".$wb_segment_name."']/groupes/groupe[@id='".$local_groupe_count."']/fields/field[@id='".$local_field_count."']/field_special");
							list( , $local_field_special_A) = each($local_field_special);
							
							//Field Type
							$local_field_type			= $xml->xpath("/contents/content[@id='".$wb_segment_name."']/groupes/groupe[@id='".$local_groupe_count."']/fields/field[@id='".$local_field_count."']/field_type");
							list( , $local_field_type_A) = each($local_field_type);
							
							//Field Selectionned
							$local_field_selectionned	= $xml->xpath("/contents/content[@id='".$wb_segment_name."']/groupes/groupe[@id='".$local_groupe_count."']/fields/field[@id='".$local_field_count."']/field_selectionned");
							list( , $local_field_selectionned_A) = each($local_field_selectionned);
							
							//Field Value1
							$local_field_value1			= $xml->xpath("/contents/content[@id='".$wb_segment_name."']/groupes/groupe[@id='".$local_groupe_count."']/fields/field[@id='".$local_field_count."']/field_value1");
							list( , $local_field_value1_A) = each($local_field_value1);
							
							//Field Value2
							$local_field_value2			= $xml->xpath("/contents/content[@id='".$wb_segment_name."']/groupes/groupe[@id='".$local_groupe_count."']/fields/field[@id='".$local_field_count."']/field_value2");
							list( , $local_field_value2_A) = each($local_field_value2);
							
							
							//echo('**Fields: Operation:'.$local_field_operation_A.' ID: '.$local_field_id_A.' Special: '.$local_field_special_A.' Type :'.$local_field_type_A.' Selectionned: '.$local_field_selectionned_A.' Value1: '.$local_field_value1_A.' Value2: '.$local_field_value2_A.' <br />');
							
							//The switch case to detect the type of the field and make action of every type ..
							switch($local_field_special_A){
								case 'family_st':
									//echo('<br /><b>Calling file !=> Family_st</b><br />');
									require('segment_create_elements_special_family_1st.php');
									
									//In the special fields(St, Nd and Rd) we do not save 
									//the field_selectionned and the field_value2
									//Insert the field 
									$query_field_insert	="	INSERT INTO marketing_groupes_fields  
													(id, id_groupe,
													id_field, what,
													field_selectionned, field_value1,
													field_value2)
													
												VALUES
													(NULL, ".$local_group_id.", 
													'".$local_field_id_A."', '".$local_field_operation_A."', 
													'', '".$local_field_value1_A."', 
													'')";
													
									$res_field_insert 	= $db->query($query_field_insert, __FILE__, __LINE__);
									
									
								break;
								
								case 'family_nd':
									//echo('<br /><b>Calling file !=> Family_st</b><br />');
									require('segment_create_elements_special_family_2nd.php');
									
									//In the special fields(St, Nd and Rd) we do not save 
									//the field_selectionned and the field_value2
									//Insert the field 
									$query_field_insert	="	INSERT INTO marketing_groupes_fields  
													(id, id_groupe,
													id_field, what,
													field_selectionned, field_value1,
													field_value2)
													
												VALUES
													(NULL, ".$local_group_id.", 
													'".$local_field_id_A."', '".$local_field_operation_A."', 
													'', '".$local_field_value1_A."', 
													'')";
													
									$res_field_insert 	= $db->query($query_field_insert, __FILE__, __LINE__);
									
								break;
								
								case 'family_rd':
									//echo('<br /><b>Calling file !=> Family_st</b><br />');
									require('segment_create_elements_special_family_3rd.php');
									
									//In the special fields(St, Nd and Rd) we do not save 
									//the field_selectionned and the field_value2
									//Insert the field 
									$query_field_insert	="	INSERT INTO marketing_groupes_fields  
													(id, id_groupe,
													id_field, what,
													field_selectionned, field_value1,
													field_value2)
													
												VALUES
													(NULL, ".$local_group_id.", 
													'".$local_field_id_A."', '".$local_field_operation_A."', 
													'', '".$local_field_value1_A."', 
													'')";
													
									$res_field_insert 	= $db->query($query_field_insert, __FILE__, __LINE__);
									
								break;
								
								case 'no':
									//It's a normal field
									switch($local_field_type_A){
										case 'text':
											//echo('<br /><b>Calling file !=> Text </b><br />');
											require('segment_create_elements_normal_text.php');
											
											//Insert the field 
											$query_field_insert	="	INSERT INTO marketing_groupes_fields  
															(id, id_groupe,
															id_field, what,
															field_selectionned, field_value1,
															field_value2)
															
														VALUES
															(NULL, ".$local_group_id.", 
															'".$local_field_id_A."', '".$local_field_operation_A."', 
															'".$local_field_selectionned_A."', '".$local_field_value1_A."', 
															'')";
															
											$res_field_insert 	= $db->query($query_field_insert, __FILE__, __LINE__);
											
										break;
										
										case 'number':
											//echo('<br /><b>Calling file !=> Number</b><br />');
											require('segment_create_elements_normal_number.php');
											
											//Insert the field 
											$query_field_insert	="	INSERT INTO marketing_groupes_fields  
															(id, id_groupe,
															id_field, what,
															field_selectionned, field_value1,
															field_value2)
															
														VALUES
															(NULL, ".$local_group_id.", 
															'".$local_field_id_A."', '".$local_field_operation_A."', 
															'".$local_field_selectionned_A."', '".$local_field_value1_A."', 
															'')";
															
											$res_field_insert 	= $db->query($query_field_insert, __FILE__, __LINE__);
											
											
										break;
										
										case 'select':
											//echo('<br /><b>Calling file !=> Select</b><br />');
											require('segment_create_elements_normal_select.php');
											
											$local_field_value1_A = str_replace("'","\'", $local_field_value1_A);
											
											//In the Select we do not save the "field_selectionned"
											//Insert the field 
											$query_field_insert	="	INSERT INTO marketing_groupes_fields  
															(id, id_groupe,
															id_field, what,
															field_selectionned, field_value1,
															field_value2)
															
														VALUES
															(NULL, ".$local_group_id.", 
															'".$local_field_id_A."', '".$local_field_operation_A."', 
															'', '".$local_field_value1_A."', 
															'')";
															
											$res_field_insert 	= $db->query($query_field_insert, __FILE__, __LINE__);
											
										break;
										
										case 'date':
											//echo('<br /><b>Calling file !=> Date</b><br />');
											require('segment_create_elements_normal_date.php');
											
											//In the Select we do not save the "field_selectionned"
											//Insert the field 
											$query_field_insert	="	INSERT INTO marketing_groupes_fields  
															(id, id_groupe,
															id_field, what,
															field_selectionned, field_value1,
															field_value2)
															
														VALUES
															(NULL, ".$local_group_id.", 
															'".$local_field_id_A."', '".$local_field_operation_A."', 
															'".$local_field_selectionned_A."', '".$local_field_value1_A."', 
															'".$local_field_value2_A."')";
															
											$res_field_insert 	= $db->query($query_field_insert, __FILE__, __LINE__);
											
										break;
									}//End switch Field Type
								break;
								
							}//End switch 
							
						}//End Fields While !
						
						
						
						
						
						//Close the groupe Query 
						$local_query_where .= " ) \n";						
						
						//Increment the number of groupes 
						$local_groupe_count++;
						
						
					}//End while groupes
				
					//Concat the groupe Query to the main query !
					$global_query_where 	.= $local_query_where;
						
/*
echo('La requ&ecirc;te finale !<br /><br />');	
echo('<b>SELECT</b>');
echo('<br />');
echo(' count(*) c');
echo('<br />');
echo('<b>FROM</b>');
echo('<br />');
echo(' '.$segment_condition_from);
echo('<br />');
echo('<b>WHERE</b>');
echo('<br />');
echo($global_query_where);
if(!empty($segment_condition_group)){
	echo('<br />');
	echo('Group BY '.$segment_condition_group);
}
echo('<br /><br />');
*/

					//Updating the segment with the last informations !
					$query_group_final	='	UPDATE marketing_segment   
												SET
													condition_where="'.$global_query_where.'",
													date_change=NOW()
												WHERE 
													id='.$segment_id.' ';
							
					$res_group_final 	= $db->query($query_group_final, __FILE__, __LINE__);

		
					echo('<br /><br /><br />');
					echo('<div id="segmet_final_results_container">');
						echo('<div style="width: 25px; float:left; margin-top: -2px">');
							echo('<img src="ressources/images/icons/green_ok.png" alt="- "/>');
						echo('</div>');
						echo('<div>');
							echo('<font color="green">Segment modifi&eacute; avec succ&egrave;s !</font>');
						echo('</div>');
						
						echo('<div id="segmet_final_results_container_js" style="display:none;">');
							echo('redirect_page_after_segment_edit();');
						echo('</div>');
					echo('</div>');
					echo('<br /><br />');
					
					
					//Insert into history !
					$query_insert_history	="INSERT INTO  marketing_users_history(id, action, 
																		id_user, action_date)
									VALUES(NULL, 'Edit Segment ID: ".$segment_id."',
									".$_SESSION['marketing_user_id'].", NOW())";
					$db->query($query_insert_history, __FILE__, __LINE__);
					
					
					
				}else{
					throw new Exception('Le contenu de votre segment est vide !');
				}
			
			}catch(Exception $e){
				echo('Erreur : '.$e);
			}//End Try Catch !
		}else{
			echo('Informations manquantes, merci de r&eacute;essayer !');
		}//End Verification if the informations sent is not empty !
	}else{
		echo('<a href="/fr/marketing/login.php">Veuillez vous reconnecter</a>.');
	}//ENd the first IF the user has the right for this page and he's connected !
?>