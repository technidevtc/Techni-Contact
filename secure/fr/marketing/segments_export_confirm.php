<?php 
	require_once('functions.php'); 
	//require_once('check_session.php');

	
	if(empty($_SESSION['marketing_user_id'])){
		throw new Exception('<a href="/fr/marketing/login.php">Veuillez vous reconnecter</a>.');
	}
	
	
	$export_v_source			= mysql_escape_string($_POST['export_v_source']);
	$export_v_selected_value	= mysql_escape_string($_POST['export_v_selected_value']);
	$export_v_selected_fields	= mysql_escape_string($_POST['export_v_selected_fields']);
	
	if(empty($export_v_source) || empty($export_v_selected_value) || empty($export_v_selected_fields)){
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
	
	try{
		
		
		//Check if we can use the platform (if we not sync the Database !) !
		$query_get_sync_flag	="SELECT
									sync_end
								FROM 
									marketing_synchronization_flag  
								WHERE 
									id=1 ";
		$res_get_sync_flag 	= $db->query($query_get_sync_flag, __FILE__, __LINE__);
		$data_get_sync_flag 	= $db->fetchAssoc($res_get_sync_flag);
		
		if(strcmp($data_get_sync_flag['sync_end'],'no')==0){
			//We can't use the platfom
			
			echo('<br /><br /><br />');
			echo('<div id="segmet_final_results_container" style="padding-left:23%;">');
				echo('<div style="width: 75px; float:left; margin-top: -7px">');
					echo('<img src="ressources/images/icons/stop-icon.png" alt="- "/>');
				echo('</div>');
				echo('<div>');
					echo('<font color="red">');
						echo('La synchronisation de la base de donn&eacute;es est en cours.. ');
						echo('<br />');
						echo('Merci de r&eacute;essayer ult&eacute;rieurement !');
					echo('</font>');
				echo('</div>');
				
				//echo('<div id="segmet_final_results_container_js" style="display:none;">');
					//echo('refresh_page_after_segment_execution();');
				//echo('</div>');
			echo('</div>');
			echo('<br /><br />');
			
		}else if(strcmp($data_get_sync_flag['sync_end'],'yes')==0){
					//We can use the platfom
					
					
			
			//If it's a segment we have to look for the Table ID
			//Else if it's a Table We have to check the user's table right 
			if(strcmp($export_v_source,'segment')==0){
				//Get the segment Table ID
				$query_get_segment_informations	="SELECT 
													
													m_seg.id_table,	m_seg.name, 	
													m_seg.condition_from, m_seg.condition_where, 	
													m_seg.condition_group, m_seg.type
													
												FROM
													marketing_segment m_seg 
													
												WHERE
													m_seg.id=".$export_v_selected_value." ";
															
				$res_get_segment_informations 	= $db->query($query_get_segment_informations, __FILE__, __LINE__);
													
				$data_get_segment_informations = $db->fetchAssoc($res_get_segment_informations);
				
				$table_id	= '#'.$data_get_segment_informations['id_table'].'#';
			}else{
				$table_id	= '#'.$export_v_selected_value.'#';
			}
			

			
			
			//Check if the user have the right to access to this page !
			if(strpos($content_get_user_page_permissions['content'],$page_id)===FALSE){
				echo('<a href="/fr/marketing/">Vous n\'avez pas le droit d\'acc&eacute;der &agrave; cette page !</a>');
			}else if(strpos($content_get_user_tables_access_permissions['content'],$table_id)===FALSE){
				echo('<a href="/fr/marketing/">Vous n\'avez pas le droit d\'acc&eacute;der &agrave; cette table !</a>');
			}else if(!empty($_SESSION['marketing_user_id'])){
				
				//Steps 
				/**************/
				//1. Decomposing the selected Fields !
				//2. Looking for the informations for each Field and Push them in a Fields Global Array !
				//3. Build The export query in a Global variable to use it one (no difference if it was a Table or Segment)
				//4. If their's results Build the file Header (Fetch the Global Fields Array)!
				//5. Fetch Horizontaly the cols (Query result) and test IF :
					//It's a special one (Execute the special query previously saved in the Global Fields Array !
					//It's a select replace the values to Show !
				//6. Fetch Vertically (Next Row)
				//7. Close the Pipe !
				/*************/
				

				$query_insert_history	="INSERT INTO  marketing_users_history(id, action, 
																				id_user, action_date)
											VALUES(NULL, 'Export: ".$export_v_source."|||ID: ".$export_v_selected_value."|||Values: ".$export_v_selected_fields."',
											".$_SESSION['marketing_user_id'].", NOW())";
				$db->query($query_insert_history, __FILE__, __LINE__);
				
				
					
				$global_query_select_block	= '';
				
				
				
				//1. Decomposing the selected Fields !
				//2. Looking for the informations for each Field and Push them in a Fields Global Array !
				
				//Declare the Global Fields Array 
				/*$global_fields_array	=array(
											"id" => "",
											"name_fo" => "",
											"name_sql" => "",
											"name_sql_as" => "",
											"special_field" => "",
											"special_field_query" => "",
											"field_type" => "",
											"field_str_replace" => ""
										);*/
				$global_fields_array	= array();
				
				$local_selected_fields_count		= 0;
				//Decomposing the selected Fields 
				$temp_selected_fields	= explode('##',$export_v_selected_fields);
				while($temp_selected_fields[$local_selected_fields_count]){
					
					//Select the field informations and Push It in the Global Fields Array !
					$query_get_fields_informations	="SELECT 
														
															mt_fields.id, 
															mt_fields.name_fo, 
															mt_fields.name_sql, 
															mt_fields.name_sql_as, 
															mt_fields.special_field, 
															mt_fields.special_field_query, 
															mt_fields.field_type, 
															mt_fields.field_str_replace
															
														FROM
															marketing_tables_fields mt_fields
															
														WHERE
															mt_fields.id=".$temp_selected_fields[$local_selected_fields_count]." ";
							
					$res_get_fields_informations 	= $db->query($query_get_fields_informations, __FILE__, __LINE__);
														
					$data_get_fields_informations = $db->fetchAssoc($res_get_fields_informations);
					
					/*array_push($global_fields_array, $data_get_segment_informations['id'], $data_get_segment_informations['name_fo'], $data_get_segment_informations['name_sql'], $data_get_segment_informations['name_sql_as'], $data_get_segment_informations['special_field'], $data_get_segment_informations['special_field_query'], $data_get_segment_informations['field_type'], $data_get_segment_informations['field_str_replace']);
					*/
					
					$local_fields_array	=array(
											"id" => $data_get_fields_informations['id'],
											"name_fo" => decode_export($data_get_fields_informations['name_fo']),
											"name_sql" => $data_get_fields_informations['name_sql'],
											"name_sql_as" => $data_get_fields_informations['name_sql_as'],
											"special_field" => $data_get_fields_informations['special_field'],
											"special_field_query" => $data_get_fields_informations['special_field_query'],
											"field_type" => $data_get_fields_informations['field_type'],
											"field_str_replace" => $data_get_fields_informations['field_str_replace']
										);
										
					array_push($global_fields_array, $local_fields_array);
					
					
					$global_query_select_block	.="		".$data_get_fields_informations['name_sql']."	AS	\"".$data_get_fields_informations['name_sql_as']."\", ";
					
					
					//Incrementing 
					$local_selected_fields_count++;
				}//End while fetch the Selected Fields !
				
				//Removing the two last chars
				$global_query_select_block	= substr($global_query_select_block, 0, -2);
				
				
				
				//3. Build The export query in a Global variable to use it one (no difference if it was a Table or Segment)
				//4. If their's results Build the file Header (Fetch the Global Fields Array)!
				if(strcmp($export_v_source,'segment')==0){
					
					$global_export_query	= "SELECT
													".$global_query_select_block."	
													
												FROM  
														".$data_get_segment_informations['condition_from']." 
												
												";
					if(!empty($data_get_segment_informations['condition_where'])){
						$global_export_query	.=	" WHERE 
														".$data_get_segment_informations['condition_where']." ";
					}
					
					if(!empty($data_get_segment_informations['condition_group'])){
						$global_export_query	.=	" GROUP BY ".$data_get_segment_informations['condition_group']." ";
					}
					
				}else{

					//It's From a Table
					//Get the Table informations !
					$query_get_table_informations	="SELECT 
													
														m_tables.id, 
														m_tables.name_sql, 
														m_tables.condition_from, 
														m_tables.condition_where, 
														m_tables.condition_group 
														
													FROM
														marketing_tables m_tables
														
													WHERE
														m_tables.id=".$export_v_selected_value." ";
															
					$res_get_table_informations  = $db->query($query_get_table_informations, __FILE__, __LINE__);
													
					$data_get_table_informations = $db->fetchAssoc($res_get_table_informations);
					
					$global_export_query	= "SELECT
													".$global_query_select_block."	
													
												FROM 
													".$data_get_table_informations['name_sql']." ";
					
					if(!empty($data_get_table_informations['condition_from'])){
						$global_export_query	.=	" ".$data_get_table_informations['condition_from']." ";
					}
					
					if(!empty($data_get_table_informations['condition_where'])){
						$global_export_query	.=	" WHERE 
														".$data_get_table_informations['condition_where']." ";
					}			
					
					if(!empty($data_get_segment_informations['condition_group'])){
						$global_export_query	.=	" GROUP BY ".$data_get_segment_informations['condition_group']." ";
					}
					
				}
				
			
				$res_get_final_result  		= $db->query($global_export_query, __FILE__, __LINE__);
				
				
				//Test if we have results
				if(mysql_num_rows($res_get_final_result)>0){
					
					//Creating a name of the file
					$file_generated_name		= "exports/export_file_".date("d-m-Y___H_i_s_")."_".rand(0, 999999);
					$csv_name_file				= $file_generated_name.".csv";
					$zipname_to_download		= $file_generated_name.".zip";
					$tarname_to_download		= $file_generated_name.".tar";
					$gzname_to_download			= $file_generated_name.".gz";
					
					
					//Open the file on read !
					$fp = fopen($csv_name_file, 'w');
					if($fp==false){
						throw new Exception('Impossible de créer le fichier !');
					}
					
					//Writing the file header !
					$local_loop	= 0;
					while($global_fields_array[$local_loop]["id"]){
						fputs ($fp, $global_fields_array[$local_loop]["name_fo"].";");
						$local_loop++;
					}
					fputs ($fp, "\n");
					
					//Two wiles the first one to fetch the rows 
					//and the second one to fetch every column !			
					while($data_get_final_result = $db->fetchAssoc($res_get_final_result)){
						
						$local_while_columns	= 0;
						
						//While the count of the fields in the global Array>local loop elements (fields)
						while($local_while_columns<$local_selected_fields_count){
						
							
							
							Switch($global_fields_array[$local_while_columns]['special_field']){
								
								case "families_st":
									$query_get_family	= $global_fields_array[$local_while_columns]['special_field_query'];
									$query_get_family	= str_replace('######',$data_get_final_result[$global_fields_array[$local_while_columns]['name_sql_as']],$query_get_family);
							
									$res_get_family  = $db->query($query_get_family, __FILE__, __LINE__);
									
									$data_get_family = $db->fetchAssoc($res_get_family);
									fputs($fp, decode_export($data_get_family['name']).";");
									
								break;
								
								case "families_nd":
									$query_get_family	= $global_fields_array[$local_while_columns]['special_field_query'];
									$query_get_family	= str_replace('######',$data_get_final_result[$global_fields_array[$local_while_columns]['name_sql_as']],$query_get_family);
							
									$res_get_family  = $db->query($query_get_family, __FILE__, __LINE__);
									
									$data_get_family = $db->fetchAssoc($res_get_family);
									fputs($fp, decode_export($data_get_family['name']).";");
									
								break;
								
								case "families_rd":
									$query_get_family	= $global_fields_array[$local_while_columns]['special_field_query'];
									$query_get_family	= str_replace('######',$data_get_final_result[$global_fields_array[$local_while_columns]['name_sql_as']],$query_get_family);
							
									$res_get_family  = $db->query($query_get_family, __FILE__, __LINE__);
									
									$data_get_family = $db->fetchAssoc($res_get_family);
									fputs($fp, decode_export($data_get_family['name']).";");
									
								break;
								
								case "no":
									switch($global_fields_array[$local_while_columns]['field_type']){
										
										case "text":
										case "number":
											//Show the value !
											$returned_value	= decode_export(normalize_export_csv($data_get_final_result[$global_fields_array[$local_while_columns]['name_sql_as']]));
											fputs($fp, $returned_value.";");
										break;
										
										case "select":
											//Get the informations from the Field and replace the values from the result row
											//To get the converted value (Value to show)
											
											$returned_value	= '';
											
											$local_selected_count		= 0; 
											$temp_select_local	= explode('|||',$global_fields_array[$local_while_columns]['field_str_replace']);
											
											while(!empty($temp_select_local[$local_selected_count])){
												
												$temp_select_local2	= explode('#',$temp_select_local[$local_selected_count]);
												
												if(strcmp($data_get_final_result[$global_fields_array[$local_while_columns]['name_sql_as']],$temp_select_local2[0])==0){
													$returned_value = decode_export($temp_select_local2[1]);
													break;
												}
												$local_selected_count++;
											}//End while 								
											
											fputs($fp, $returned_value.";");
										break;
										
										case "date":
											//Convert the TimeStamp to a normal Date !
											fputs($fp, date('d/m/Y H:i:s', $data_get_final_result[$global_fields_array[$local_while_columns]['name_sql_as']]).";");
										break;
											
									}//End the second Switch 
								break;
							}//End the First Switch !
							
					
							//Increment the number of columns !
							$local_while_columns++;
						}//End while Column !
						
						//Add a BackRow !
						fputs ($fp, "\n");
					}//End while Rows !
					
					
					//Close the File !
					fclose($fp);
					
					
					//Compressing file and download IT !
					
					$zip = new ZipArchive;
					$zip->open($zipname_to_download, ZipArchive::CREATE);
					
					//foreach($files as $file){
						$download_file = file_get_contents($csv_name_file);
						$zip->addFromString(basename($csv_name_file),$download_file);
					//}
					
					# close zip
					$zip->close();
					
					# send the file to the browser as a download
					header('Content-disposition: attachment; filename='.$zipname_to_download.'');
					header('Content-type: application/zip');
					readfile($zipname_to_download);
					
					
					
					//For .tar File !
					/*
					$a = new PharData($tarname_to_download);
					// ADD FILES TO archive.tar FILE
					$a->addFile($csv_name_file);

					// COMPRESS archive.tar FILE. COMPRESSED FILE WILL BE archive.tar.gz
					$a->compress(Phar::GZ);
					# send the file to the browser as a download
					header('Content-disposition: attachment; filename='.$tarname_to_download.'');
					header('Content-type: application/tar');
					readfile($tarname_to_download);
					*/
					
					//For .gz File !
					/*
					//change compress.zlib:// to compress.zip:// for zip compression
					//change compress.zlib:// to compress.bzip2:// for bzip2 compression
					file_put_contents("compress.zlib://$gzname_to_download", file_get_contents($csv_name_file));
					
					# send the file to the browser as a download
					header('Content-disposition: attachment; filename='.$gzname_to_download.'');
					header('Content-type: application/gz');
					readfile($gzname_to_download);
					*/
					
					
					//Delete the CSV and ZIP Files Previously created !
					unlink($csv_name_file);
					unlink($zipname_to_download);
					//unlink($tarname_to_download);
					//unlink($gzname_to_download);
					
					
				}else{
					echo('0 results !');
				}//End else if we have results !
				
				
			}else{
				echo('<a href="/fr/marketing/login.php">Veuillez vous reconnecter</a>.');
			}//ENd the first IF the user has the right for this page and he's connected !
		
		}//End check use of the platform !
		
	}catch(Exception $e){
		echo('Erreur: '.$e);
	}
?>