<?php

//=> While of groupes => Get the fields => While of fields <br />
//Init the remaining elements => init the counts<br />
//Delete the BackSlashes !<br />
//Remove the fropp classes for the loaded elements !<br />

	//Count Groupes
	$local_groupes_count	= 1;
	//Count Fields
	$local_fields_count		= 1;

	//Get the groupes
	$query_get_segment_groups	="SELECT 
										
										m_groupes.id, m_groupes.name,
										m_groupes.what
										
									FROM
										marketing_groupes m_groupes
										
									WHERE
										m_groupes.id_segment=".$segment_id."
									ORDER BY m_groupes.id ASC";
												
	$res_get_segment_groups 	= $db->query($query_get_segment_groups, __FILE__, __LINE__);
										
	while($data_get_segment_groups 	= $db->fetchAssoc($res_get_segment_groups)){
		
		echo("<div id=\"groupe_".$local_groupes_count."\" class=\"groupe_container\">");
			
			//Print the group separator !
			if($local_groupes_count!=1){
				echo('<div class="segment-groupe-line-separator">&nbsp;</div>');
			}
		
			echo("<div class=\"groupe_top\">");
			
				echo('<div id="groupe_'.$local_groupes_count.'_what" class="groupe_what">');
			
					if(strcmp($data_get_segment_groups['what'],'1')==0){
						echo("<div id=\"groupe_".$local_groupes_count."_what\" class=\"groupe_what\">&nbsp;</div>");
						
					}else if(strcmp($data_get_segment_groups['what'],'AND')==0){
						echo("<input type=\"radio\" id=\"row_groupe_what_".$local_groupes_count."_and\" name=\"row_groupe_what".$local_groupes_count."\" value=\"AND\" checked=\"true\" />");
						echo("<label for=\"row_groupe_what_".$local_groupes_count."_and\">Et</label>");
						echo("<input type=\"radio\" id=\"row_groupe_what_".$local_groupes_count."_or\" name=\"row_groupe_what".$local_groupes_count."\" value=\"OR\" />");
						echo("<label for=\"row_groupe_what_".$local_groupes_count."_or\">Ou</label>");
						
					}else if(strcmp($data_get_segment_groups['what'],'OR')==0){
						echo("<input type=\"radio\" id=\"row_groupe_what_".$local_groupes_count."_and\" name=\"row_groupe_what".$local_groupes_count."\" value=\"AND\" />");
						echo("<label for=\"row_groupe_what_".$local_groupes_count."_and\">Et</label>");
						echo("<input type=\"radio\" id=\"row_groupe_what_".$local_groupes_count."_or\" name=\"row_groupe_what".$local_groupes_count."\" value=\"OR\" checked=\"true\" />");
						echo("<label for=\"row_groupe_what_".$local_groupes_count."_or\">Ou</label>");
						
					}//End operations!
					
				echo('</div>');

				if($local_groupes_count==1){
					echo("<div id=\"groupe_".$local_groupes_count."_delete\" class=\"groupe_delete\" >");
						echo("&nbsp;");
					echo("</div>");
				}else{
					echo("<div id=\"groupe_".$local_groupes_count."_delete\" class=\"groupe_delete\" >");
						echo("<div title=\"Supprimer ce groupe\" onclick=\"field_listner_delete_groupe('".$local_groupes_count."');\"><i class=\"fa fa-trash\"></i></div>");
					echo("</div>");
				}
				
				
				echo("<div id=\"groupe_1_name\" class=\"groupe_name\">");
					echo("<input type=\"search\" id=\"groupe_name_".$local_groupes_count."\" placeholder=\"Nom du groupe ".$local_groupes_count."\" value=\"".$data_get_segment_groups['name']."\" />");
				echo("</div>");

				echo("<div class=\"groupe_add_listner_field\">");
					echo("<input type=\"button\" class=\"btn btn-default btn_row_field_add\" value=\"Ajouter un champ\" onclick=\"segment_add_new_field_row('".$local_groupes_count."')\" />");
				echo("</div>");
			echo("</div><!-- end div .groupe_top -->");
		
		
			echo("<div id=\"groupe_middle_".$local_groupes_count."\" class=\"groupe_middle\">");
		
				//Start the fields
				
				//Get the groupes
				$query_get_segment_fields	="SELECT 
													
													m_groupe_fields.id_field AS id,	
													m_groupe_fields.what,
													m_groupe_fields.field_selectionned,	
													m_groupe_fields.field_value1,
													m_groupe_fields.field_value2,
													
													
													m_tables_fields.name_fo,
													m_tables_fields.special_field,
													m_tables_fields.field_type,
													m_tables_fields.field_str_replace
													
												FROM
													marketing_groupes_fields m_groupe_fields
														INNER JOIN marketing_tables_fields AS m_tables_fields ON m_groupe_fields.id_field=m_tables_fields.id
													
												WHERE
													m_groupe_fields.id_groupe=".$data_get_segment_groups['id']." 
													
												ORDER BY m_groupe_fields.id ASC";
															
				$res_get_segment_fields 	= $db->query($query_get_segment_fields, __FILE__, __LINE__);
									
				//Local Loop to init the field position on the group !
				$local_field_loop	= 1;
				while($data_get_segment_fields 	= $db->fetchAssoc($res_get_segment_fields)){
					
					echo("<div id=\"field_element_container_".$local_fields_count."\" class=\"groupe_field_onerow ui-droppable ui-droppable-disabled\">");
						echo("<div class=\"edl_what\">");
						
							if(strcmp($data_get_segment_fields['what'],'1')==0){
								echo(" ");
							}else if(strcmp($data_get_segment_fields['what'],'AND')==0){
								echo("<input type=\"radio\" id=\"row_field_what_".$local_fields_count."_and\" name=\"row_field_what".$local_fields_count."\" value=\"and\" checked=\"true\"><label for=\"row_field_what_".$local_fields_count."_and\">Et</label><input type=\"radio\" id=\"row_field_what_".$local_fields_count."_or\" name=\"row_field_what".$local_fields_count."\" value=\"or\"><label for=\"row_field_what_".$local_fields_count."_or\">Ou</label>");
							}else if(strcmp($data_get_segment_fields['what'],'OR')==0){
								echo("<input type=\"radio\" id=\"row_field_what_".$local_fields_count."_and\" name=\"row_field_what".$local_fields_count."\" value=\"and\"><label for=\"row_field_what_".$local_fields_count."_and\">Et</label><input type=\"radio\" id=\"row_field_what_".$local_fields_count."_or\" name=\"row_field_what".$local_fields_count."\" value=\"or\" checked=\"true\"><label for=\"row_field_what_".$local_fields_count."_or\">Ou</label>");
							}
							
						echo("</div>");
					
						echo("<div class=\"edl_second_container\">");
							echo("<div class=\"edl_field\">");
								echo($data_get_segment_fields['name_fo']);
							echo("</div><!-- end div .edl_field -->");
							
							//Call function that build this blocks !
							build_edited_row($db, $local_fields_count, $data_get_segment_fields['id'], $data_get_segment_fields['special_field'], $data_get_segment_fields['field_type'], $data_get_segment_fields['field_selectionned'], $data_get_segment_fields['field_value1'], $data_get_segment_fields['field_value2'], $data_get_segment_fields['field_str_replace']);

							
							echo("<div class=\"edl_remove\" style=\"margin-top:15px;\">");
							
								if($local_field_loop==1){
									echo("<div title=\"Vider ce champ\" onclick=\"javascript:field_listner_clean_row('".$local_fields_count."');\">");
									echo("<i class=\"fa fa-refresh\"></i></div>");	
								}else{
									echo("<div title=\"Supprimer ce champ\" onclick=\"javascript:field_listner_delete_row('".$local_groupes_count."', '".$local_fields_count."');\">");
									echo("<i class=\"fa fa-trash-o\"></i></div>");
								}
							echo("</div><!-- end div .edl_remove -->");
						echo("</div>");
					
					echo("</div><!-- end div .element_droppable_listner -->");
					
					
					//Increment on this local loop to detect it the field is on the first position or not !
					$local_field_loop++;					
					
					//Increment the Fields
					$local_fields_count++;
				}//End While !
				
				//End the fields
				
		
			echo("</div><!-- end div .groupe_middle-->");
		
		echo("</div><!-- div #groupe_X-->");
		
		//Increment the Groups 
		$local_groupes_count++;
	}//End while groups !

	
	function build_edited_row($db, $id_row, $id_field, $special_type, $type, $selected_value, $field_value1, $field_value2, $select_values){

		
		switch($special_type){
			
			case 'families_st':
			
				echo("<div class=\"edl_operator\">");
					echo('<div class="familly_autocomplete_container_row">');
						echo('<div class="familly_autocomplete_field">');
							echo('<input type="text" name="ffield_st_row'.$id_row.'" id="ffield_st_row'.$id_row.'" onkeyup="javascript:load_family_autocomplete_st(event, \''.$id_row.'\')" style="width:200px;" title="Autocomplete Famille Niveau 1">');
						echo('</div>');
						echo('<div id="familly_autocomplete_container_row_'.$id_row.'" class="familly_autocomplete_result" style="display: none;">');
							echo('<span class="familly_autocomplete_list_close">');
								echo('<img src="ressources/images/icons/cross.png" alt="Fermer" title="Fermer" onclick="close_the_autocomplete_now(\''.$id_row.'\');" />');
							echo('</span>');
							echo('<div id="familly_autocomplete_loader_'.$id_row.'" class="familly_autocomplete_loader" style="display: none;">');
								echo('<img src="ressources/images/lightbox-ico-loading.gif" alt="Chargement.." />');
							echo('</div>');
							echo('<div id="familly_autocomplete_list_'.$id_row.'">');
								echo('&nbsp;');
							echo('</div>');
						echo('</div>');
					echo('</div>');
				echo("</div><!-- end div .edl_operator -->");
				
				echo("<div class=\"edl_value\">");
					echo('<input type="hidden" id="field_element_type_'.$id_row.'" value="family_st" />');
					echo('<input type="hidden" id="field_element_'.$id_row.'_value_1" value="'.$field_value1.'" />');
					
					//Get the Family Name
					$query_get_family1	=" SELECT 
												name	
											FROM
												families_fr
											WHERE
												id=".$field_value1." 
										";
					
					
					$res_get_family1 	= $db->query($query_get_family1, __FILE__, __LINE__);

					$data_get_family1 	= $db->fetchAssoc($res_get_family1);
					
					echo('<span id="family_field_show_'.$id_row.'">'.$data_get_family1['name'].'</span>');
				echo("</div><!-- end div .edl_value -->");
				
				echo("<div class=\"edl_hidden\">");
					echo('<input type="hidden" id="field_element_'.$id_row.'_id" value="'.$id_field.'" />');
				echo("</div><!-- end div .edl_hidden -->");
		
		
			break;
			
			case 'families_nd':
			
				echo("<div class=\"edl_operator\">");
					echo('<div class="familly_autocomplete_container_row">');
						echo('<div class="familly_autocomplete_field">');
							echo('<input type="text" name="ffield_nd_row'.$id_row.'" id="ffield_nd_row'.$id_row.'" onkeyup="javascript:load_family_autocomplete_nd(event, \''.$id_row.'\')" style="width:200px;" title="Autocomplete Famille Niveau 2" />');
						echo('</div>');
						echo('<div id="familly_autocomplete_container_row_'.$id_row.'" class="familly_autocomplete_result" style="display: none;">');
							echo('<span class="familly_autocomplete_list_close">');
								echo('<img src="ressources/images/icons/cross.png" alt="Fermer" title="Fermer" onclick="close_the_autocomplete_now(\''.$id_row.'\');" />');
							echo('</span>');
							echo('<div id="familly_autocomplete_loader_'.$id_row.'" class="familly_autocomplete_loader" style="display: none;">');
								echo('<img src="ressources/images/lightbox-ico-loading.gif" alt="Chargement.." />');
							echo('</div>');
							echo('<div id="familly_autocomplete_list_'.$id_row.'">&nbsp;</div>');
						echo('</div>');
					echo('</div>');
				echo("</div><!-- end div .edl_operator -->");
				
				echo("<div class=\"edl_value\">");
					echo('<input type="hidden" id="field_element_type_'.$id_row.'" value="family_nd" />');
					echo('<input type="hidden" id="field_element_'.$id_row.'_value_1" value="'.$field_value1.'">');
					
					//Get the Family Name
					$query_get_family2	="SELECT 
												name	
											FROM
												families_fr
											WHERE
												id=".$field_value1." ";
															
					$res_get_family2 	= $db->query($query_get_family2, __FILE__, __LINE__);

					$data_get_family2	= $db->fetchAssoc($res_get_family2);
					echo('<span id="family_field_show_'.$id_row.'">'.$data_get_family2['name'].'</span>');
				echo("</div><!-- end div .edl_value -->");
				
				echo("<div class=\"edl_hidden\">");
					echo('<input type="hidden" id="field_element_'.$id_row.'_id" value="'.$id_field.'" />');
				echo("</div><!-- end div .edl_hidden -->");
				
			break;
			
			case 'families_rd':
				
				echo("<div class=\"edl_operator\">");
				
					echo('<div class="familly_autocomplete_container_row">');
						echo('<div class="familly_autocomplete_field">');
							echo('<input type="text" name="ffield_rd_row'.$id_row.'" id="ffield_rd_row'.$id_row.'" onkeyup="javascript:load_family_autocomplete_rd(event, \''.$id_row.'\')" style="width:200px;" title="Autocomplete Famille Niveau 3" />');
						echo('</div>');
						echo('<div id="familly_autocomplete_container_row_'.$id_row.'" class="familly_autocomplete_result" style="display: none;">');
							echo('<span class="familly_autocomplete_list_close">');
								echo('<img src="ressources/images/icons/cross.png" alt="Fermer" title="Fermer" onclick="close_the_autocomplete_now(\''.$id_row.'\');" />');
							echo('</span>');
							echo('<div id="familly_autocomplete_loader_'.$id_row.'" class="familly_autocomplete_loader" style="display: none;">');
								echo('<img src="ressources/images/lightbox-ico-loading.gif" alt="Chargement.." />');
							echo('</div>');
							echo('<div id="familly_autocomplete_list_'.$id_row.'">&nbsp;</div>');
						
						echo('</div>');
					echo('</div>');
					
					echo("&nbsp;");
				echo("</div><!-- end div .edl_operator -->");
				
				echo("<div class=\"edl_value\">");
					echo('<input type="hidden" id="field_element_type_'.$id_row.'" value="family_rd" />');
					echo('<input type="hidden" id="field_element_'.$id_row.'_value_1" value="'.$field_value1.'" />');
					
					//Get the Family Name
					$query_get_family3	="SELECT 
												name	
											FROM
												families_fr
											WHERE
												id=".$field_value1." ";
															
					$res_get_family3 	= $db->query($query_get_family3, __FILE__, __LINE__);

					$data_get_family3	= $db->fetchAssoc($res_get_family3);
					echo('<span id="family_field_show_'.$id_row.'">'.$data_get_family3['name'].'</span>');
				echo("</div><!-- end div .edl_value -->");
				
				echo("<div class=\"edl_hidden\">");
					echo('<input type="hidden" id="field_element_'.$id_row.'_id" value="'.$id_field.'" />');
				echo("</div><!-- end div .edl_hidden -->");
				
			break;
			
			case 'no':
				switch($type){
					case 'text':
						
						echo("<div class=\"edl_operator\">");
							echo('<div class="edl_operator">');
								echo('<select class="" id="select_text_'.$id_row.'" onchange="refresh_a_row_type_texte(\'normal_row\', \''.$id_row.'\')">');
									
									echo('<option value="egale" ');
									if(strcmp($selected_value,'egale')==0){ echo(' selected="true" '); } 
									echo('>&Eacute;gale</option>');
									
									echo('<option value="contient" ');
									if(strcmp($selected_value,'contient')==0){ echo(' selected="true" '); } 
									echo('>Contient</option>');
									
									echo('<option value="commence_par" ');
									if(strcmp($selected_value,'commence_par')==0){ echo(' selected="true" '); } 
									echo('>Commence par</option>');
									
									echo('<option value="termine_par" ');
									if(strcmp($selected_value,'termine_par')==0){ echo(' selected="true" '); } 
									echo('>Termine par</option>');
									
									echo('<option value="ne_contient_pas" ');
									if(strcmp($selected_value,'ne_contient_pas')==0){ echo(' selected="true" '); } 
									echo('>Ne contient pas</option>');
									
									echo('<option value="vide" ');
									if(strcmp($selected_value,'vide')==0){ echo(' selected="true" '); } 
									echo('>Vide</option>');
									
									echo('<option value="non_vide" ');
									if(strcmp($selected_value,'non_vide')==0){ echo(' selected="true" '); } 
									echo('>Non vide</option>');
								echo('</select>');
							echo('</div>');
						echo("</div><!-- end div .edl_operator -->");
						
						echo("<div class=\"edl_value\">");
							echo('<input type="hidden" id="field_element_type_'.$id_row.'" value="texte" />');
							
							//Show the field when "vide" and "non_vide" not selectionned
							if(strcmp($selected_value,'vide')!=0 && strcmp($selected_value,'non_vide')!=0){
								echo('<input type="search" id="field_element_'.$id_row.'_value_1" value="'.$field_value1.'" />');
							}
							
						echo("</div><!-- end div .edl_value -->");
						
						echo("<div class=\"edl_hidden\">");
							echo('<input type="hidden" id="field_element_'.$id_row.'_id" value="'.$id_field.'" />');
						echo("</div><!-- end div .edl_hidden -->");
						
					break;
					
					case 'number':
					
						echo("<div class=\"edl_operator\">");
							echo('<select class="" id="select_number_'.$id_row.'" onchange="refresh_a_row_type_number(\'normal_row\', \''.$id_row.'\')">');
							
								echo('<option value="egale" ');
								if(strcmp($selected_value,'egale')==0){ echo(' selected="true" '); }
								echo('>&Eacute;gale</option>');
								
								echo('<option value="different" ');
								if(strcmp($selected_value,'different')==0){ echo(' selected="true" '); }
								echo('>Diff&eacute;rent</option>');
								
								echo('<option value="lt" ');
								if(strcmp($selected_value,'lt')==0){ echo(' selected="true" '); }
								echo('>&gt;</option>');
								
								echo('<option value="lt_egal" ');
								if(strcmp($selected_value,'lt_egal')==0){ echo(' selected="true" '); }
								echo('>&gt;=</option>');
								
								echo('<option value="gt" ');
								if(strcmp($selected_value,'gt')==0){ echo(' selected="true" '); }
								echo('>&lt;</option>');
								
								echo('<option value="gt_egal" ');
								if(strcmp($selected_value,'gt_egal')==0){ echo(' selected="true" '); }
								echo('>&lt;=</option>');
								
								echo('<option value="vide" ');
								if(strcmp($selected_value,'vide')==0){ echo(' selected="true" '); }
								echo('>Vide</option>');
								
								echo('<option value="non_vide" ');
								if(strcmp($selected_value,'non_vide')==0){ echo(' selected="true" '); }
								echo('>Non vide</option>');
							echo('</select>');
						echo("</div><!-- end div .edl_operator -->");
						
						echo("<div class=\"edl_value\">");
							echo('<input type="hidden" id="field_element_type_'.$id_row.'" value="number" />');
							
							//Show the field when "vide" and "non_vide" not selectionned
							if(strcmp($selected_value,'vide')!=0 && strcmp($selected_value,'non_vide')!=0){
								echo('<input type="number" id="field_element_'.$id_row.'_value_1" value="'.$field_value1.'" step="0.01" />');
								//echo('<script type="text/javascript">');
									//echo("$('#field_element_".$id_row."_value_1').val($('#field_element_".$id_row."_value_1').val().replace(',', '.'));");
								//echo('</script>');
							}
							
						echo("</div><!-- end div .edl_value -->");
						
						echo("<div class=\"edl_hidden\">");
							echo('<input type="hidden" id="field_element_'.$id_row.'_id" value="'.$id_field.'" />');
						echo("</div><!-- end div .edl_hidden -->");
		
					break;
					
					case 'select':
						echo("<div class=\"edl_operator\">");
							echo('<select id="select_select_'.$id_row.'">');
							
								$local_select_array			= explode('|||',$select_values);
								$local_select_array_count	= 0;
								while(!empty($local_select_array[$local_select_array_count])){
									$local_select_array2	= explode('#',$local_select_array[$local_select_array_count]);
									
									echo('<option value="'.$local_select_array2['0'].'" ');
									if(strcmp($local_select_array2['0'],$field_value1)==0){ echo(' selected="true" '); }	
									echo('>'.$local_select_array2['1'].'</option>');
									
									//Incrementing the count !
									$local_select_array_count++;
								}//End while !
							echo('</select>');
						echo("</div><!-- end div .edl_operator -->");
						
						echo("<div class=\"edl_value\">");
							echo('<input type="hidden" id="field_element_type_'.$id_row.'" value="select" />');
						echo("</div><!-- end div .edl_value -->");
						
						echo("<div class=\"edl_hidden\">");
							echo('<input type="hidden" id="field_element_'.$id_row.'_id" value="'.$id_field.'" />');
						echo("</div><!-- end div .edl_hidden -->");
						
					break;
					
					case 'date':
						
						echo("<div class=\"edl_operator\">");
							echo('<select class="" id="select_date_'.$id_row.'" onchange="refresh_a_row_type_date(\'normal_row\', \''.$id_row.'\')">');
								echo('<option value="egale" ');
								if(strcmp($selected_value,'egale')==0){ echo(' selected="true" '); }
								echo(' >&Eacute;gale</option>');
								
								echo('<option value="entre" ');
								if(strcmp($selected_value,'entre')==0){ echo(' selected="true" '); }
								echo('>Entre</option>');
								
								echo('<option value="lt" ');
								if(strcmp($selected_value,'lt')==0){ echo(' selected="true" '); }
								echo('>&gt;</option>');
								
								echo('<option value="lt_egale" ');
								if(strcmp($selected_value,'lt_egale')==0){ echo(' selected="true" '); }
								echo('>&gt;=</option>');
								
								echo('<option value="gt" ');
								if(strcmp($selected_value,'gt')==0){ echo(' selected="true" '); }
								echo('>&lt;</option>');
								
								echo('<option value="gt_egale" ');
								if(strcmp($selected_value,'gt_egale')==0){ echo(' selected="true" '); }
								echo('>&lt;=</option>');
								
								echo('<option value="aujourdhui_plus" ');
								if(strcmp($selected_value,'aujourdhui_plus')==0){ echo(' selected="true" '); }
								echo('>Aujourd\'hui +</option>');
								
								echo('<option value="aujourdhui_moins" ');
								if(strcmp($selected_value,'aujourdhui_moins')==0){ echo(' selected="true" '); }
								echo('>Aujourd\'hui -</option>');
								
							echo('</select>');
								
						echo("</div><!-- end div .edl_operator -->");
		
						echo("<div class=\"edl_value\">");
							echo('<input type="hidden" id="field_element_type_'.$id_row.'" value="date" />');
							
							//Switch case for the content of the selection !
							switch($selected_value){
								
								case 'egale':
									echo('<input type="date" id="field_element_'.$id_row.'_value_1" value="'.date('Y-m-d',strtotime($field_value1)).'" />');
								break;
								
								case 'entre':
									echo('<input type="date" id="field_element_'.$id_row.'_value_1" value="'.date('Y-m-d',strtotime($field_value1)).'" />');
									echo('<input type="date" id="field_element_'.$id_row.'_value_2" class="marge_field2" value="'.date('Y-m-d',strtotime($field_value2)).'">');
								break;
								
								case 'lt':
									echo('<input type="date" id="field_element_'.$id_row.'_value_1" value="'.date('Y-m-d',strtotime($field_value1)).'" />');
								break;
								
								case 'lt_egale':
									echo('<input type="date" id="field_element_'.$id_row.'_value_1" value="'.date('Y-m-d',strtotime($field_value1)).'" />');
								break;
								
								case 'gt':
									echo('<input type="date" id="field_element_'.$id_row.'_value_1" value="'.date('Y-m-d',strtotime($field_value1)).'" />');
								break;
								
								case 'gt_egale':
									echo('<input type="date" id="field_element_'.$id_row.'_value_1" value="'.date('Y-m-d',strtotime($field_value1)).'" />');
								break;
								
								case 'aujourdhui_plus':
									echo('<input type="number" id="field_element_'.$id_row.'_value_1" value="'.$field_value1.'" />');
								break;
								
								case 'aujourdhui_moins':
									echo('<input type="number" id="field_element_'.$id_row.'_value_1" value="'.$field_value1.'" />');
								break;
								
							}//End witch content of the Date !
						
						echo("</div><!-- end div .edl_value -->");
		
		
						echo("<div class=\"edl_hidden\">");
							echo('<input type="hidden" id="field_element_'.$id_row.'_id" value="'.$id_field.'" />');
						echo("</div><!-- end div .edl_hidden -->");
						
					break;
					
				}//End second switch (Type no)			
			break;
		}//End Switch !
	}//End function !
	
	
	
/*
//Creation of the first group with the first Field row Listner !
	var actual_groupe_id	= 1;
	var prebuilt_elements	= " "+
									
				
									"<div id=\"groupe_middle_"+actual_groupe_id+"\" class=\"groupe_middle\">"+
										
				
				
									"</div><!-- end div .groupe_middle-->"+
				
								"";
									
	document.getElementById('segment_middle_part').innerHTML	 = prebuilt_elements;
	
	//Tell the user the number of remaining items
	tell_user_remaining_items(hidden_groupe_limitation, hidden_field_limitation);
	
*/

?>	