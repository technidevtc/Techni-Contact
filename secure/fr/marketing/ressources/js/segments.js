/************************************************************************************************/
/********************************	Start Segment listing ***************************************/
/************************************************************************************************/


	var hidden_groupe_limitation 		= 2;
	var hidden_field_limitation 		= 12;
	var hidden_groupe_incrementation	= 1;
	var hidden_field_incrementation		= 1;
	
	
$( document ).ready(function() {
	//var hidden_groupe_limitation 		= 2;
	//var hidden_field_limitation 		= 12;
	
	//In the edit page we have to calculate the remaining elements
	//And init them after the page load (PHP)
	if(window.location.pathname!='fr/marketing/segments-edit.php'){
		if(document.getElementById('hidden_from_config_groupe_limitation')){
			hidden_groupe_limitation 		= document.getElementById('hidden_from_config_groupe_limitation').value;
		}
		
		if(document.getElementById('hidden_from_config_field_limitation')){
			hidden_field_limitation 		= document.getElementById('hidden_from_config_field_limitation').value;
		}
	}
	
	//alert(hidden_groupe_limitation+' ** '+hidden_field_limitation);
});

//Function to init the elements on the Edit Page !
function init_elements_on_a_edit_page(group_count, fields_count){	
	var value_of_the_global_groups_limit	= document.getElementById('hidden_from_config_groupe_limitation').value;
	var value_of_the_global_fields_limit	= document.getElementById('hidden_from_config_field_limitation').value;
	
	//Increment every one because on the create page we have a prebuild one element (groupe and field)
	value_of_the_global_groups_limit++;
	value_of_the_global_fields_limit++;
	
	//Calculate the remainings !
	var value_remaining_groups				= value_of_the_global_groups_limit - group_count;
	var value_remaining_fields				= value_of_the_global_fields_limit - fields_count;
	
	//Init the elements !
	hidden_groupe_limitation				= value_remaining_groups;
	hidden_field_limitation					= value_remaining_fields;
	
	hidden_groupe_incrementation			= group_count;
	hidden_field_incrementation				= fields_count;
	
	document.getElementById('hidden_groupe_limitation').value		= hidden_groupe_limitation;
	document.getElementById('hidden_field_limitation').value		= hidden_field_limitation;
	
	document.getElementById('hidden_groupe_incrementation').value	= hidden_groupe_incrementation;
	document.getElementById('hidden_field_incrementation').value	= hidden_field_incrementation;
	
	//Show to the user the remaining elements !
	tell_user_remaining_items(hidden_groupe_limitation, hidden_field_limitation);
}




//Function to show the segments list (table)
function segments_load_list_show(){
	document.getElementById('panel-table').style.opacity = '0.2';
	document.getElementById('loader_panel-table').style.display = 'block';
				
	//Page start
	var f_ps				= document.getElementById('fps').value;
	//Page pagination
	var f_pp 				= document.getElementById('fpp').value;

	var f_search			= document.getElementById('f_search').value;
	//For the table order !
	var table_order			= document.getElementById('table_order').value;
	
	var f_type				= '';
	
	document.getElementById('panel-table').innerHTML = '&nbsp;';

	if(document.getElementById('f_search_statique').checked){
		f_type ='statique';
	}else if(document.getElementById('f_search_dynamique').checked){
		f_type ='dynamique';
	}
	
	var OAjax;
	
	if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
	else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

	OAjax.open('POST','/fr/marketing/segments-list-load.php',true);
	OAjax.onreadystatechange = function(){
		// OAjax.readyState == 1   ==>  connexion ?tablie
		// OAjax.readyState == 2   ==>  requete recue
		// OAjax.readyState == 3   ==>  reponse en cours
		
		
			if ((OAjax.readyState == 1) || (OAjax.readyState == 2) || (OAjax.readyState == 3) ){
				//document.getElementById('panel-table').style.opacity = '0.2';
				//document.getElementById('loader_panel-table').style.display = 'block';
			}
			
			if (OAjax.readyState == 4 && OAjax.status==200){
				document.getElementById('loader_panel-table').style.display = 'none';

				//if(OAjax.responseText !=''){

					//document.getElementById('panel-table').style.opacity = '1';
					document.getElementById('panel-table').innerHTML+=''+OAjax.responseText+'';
					setTimeout(segments_table_visible, 200);	

				//}else{
					//mmf_hide_autocomplete();
				//}
			}
		}
		
	OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	OAjax.send('f_ps='+f_ps+'&f_pp='+f_pp+'&f_search='+f_search+'&f_type='+f_type+'&table_order='+table_order);
	
	return false;
}

function segments_table_visible(){
	document.getElementById('panel-table').style.opacity = '1';
	//alert('finish');
}

function segments_get_more_now(){
	var personnalised_pp	=	document.getElementById('f_select_p').options[document.getElementById('f_select_p').selectedIndex].value;
	document.getElementById('fpp').value	= personnalised_pp;

	//To avoid the empty page when we change the number per page 
	//(Ex: We have 17elements and we show 5 per page 
	//and are in the page number 3, Once we will change the count per page
	//We gonna have a empty table. For that we force it to go to the first page !
	document.getElementById('fps').value = 1;
	
	segments_load_list_show();
}

function segments_load_other_page(number){
	document.getElementById('fps').value = number;
	segments_load_list_show();
}

//Make the order on the segment list (Table)
function segment_list_order_by(critere){
	document.getElementById('table_order').value	= critere;
	
	//Go back to the first page (Flag)
	document.getElementById('fps').value = 1;
	
	//Refrech
	segments_load_list_show();
}

//Autocomplete of the segment name in my-segment listing
function segment_name_div_autocomplete(){
	
	document.getElementById('segments_search_autosuggest_loading').style.display = 'inline';
	
				
	var f_search			= document.getElementById('f_search').value;

	var OAjax;
	
	if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
	else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

	OAjax.open('POST','/fr/marketing/segments-name-autocomplete.php',true);
	OAjax.onreadystatechange = function(){
		// OAjax.readyState == 1   ==>  connexion ?tablie
		// OAjax.readyState == 2   ==>  requete recue
		// OAjax.readyState == 3   ==>  reponse en cours
		
		
			if ((OAjax.readyState == 1) || (OAjax.readyState == 2) || (OAjax.readyState == 3) ){
				//document.getElementById('panel-table').style.opacity = '0.2';
				//document.getElementById('loader_panel-table').style.display = 'block';
			}
			
			if (OAjax.readyState == 4 && OAjax.status==200){
				document.getElementById('segments_search_autosuggest').style.display = 'block';
				document.getElementById('segments_search_autosuggest_content').innerHTML = '';
				document.getElementById('segments_search_autosuggest_loading').style.display = 'none';

				if(OAjax.responseText !=''){

					//document.getElementById('panel-table').style.opacity = '1';
					document.getElementById('segments_search_autosuggest_content').innerHTML+=''+OAjax.responseText+'';


				}else{
					segments_search_hide_autosuggest();
				}
			}
		}
		
	OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	OAjax.send('f_search='+f_search);
	
	return false;

}

//Function to hide the autocomplete 
function segments_search_hide_autosuggest(){
	document.getElementById('segments_search_autosuggest').style.display='none';
}

//Function to fill after the click on the autocomplete
function segment_fill_this_one(string){
	document.getElementById('f_search').value=string;
	segments_search_hide_autosuggest();
	segments_load_list_show();
}


/************************************************************************************************/
/********************************	End Segment listing *****************************************/
/************************************************************************************************/


/************************************************************************************************/
/********************************	Start Segment Create & Edit *********************************/
/************************************************************************************************/
//Initiate the vars (on create page)
function init_limits(){
	document.getElementById('hidden_groupe_limitation').value		= hidden_groupe_limitation;
	document.getElementById('hidden_field_limitation').value		= hidden_field_limitation;
	
	document.getElementById('hidden_groupe_incrementation').value	= hidden_groupe_incrementation;
	document.getElementById('hidden_field_incrementation').value	= hidden_field_incrementation;
}

//Activate the event for the draggable fields
function activate_draggable_for_the_fields(){
	$(function() {
		//The move & sort
		$( "#segment_top_part_right" ).sortable({
			//connectWith: ".element_droppable_listner",
			//placeholder: "element_draggable_placeholder"
			//placeholder: "ui-state-highlight"
		
		});
		$( "#segment_top_part_right" ).disableSelection();
		
		//To catch the element !
		activate_droppable_for_the_fields();
		
		/*$(document).on( "click", "#Elementid_or_class", function() {
			//Make action for everyclick !
		});*/
	
	});
}

//I separated the droppable with the draggable fields because I need this
//Function in every change on Rows (Add or Delete)..
function activate_droppable_for_the_fields(){
		/*
			To activate add a functionnality to move the Fields Rows !
		$( "#segment_middle_part .element_droppable_listner" ).sortable({		
		});
		$( ".element_droppable_listner" ).disableSelection();
		
		*/
		
		$( ".element_droppable_listner" ).droppable({
			//To add class When we hover the listner !
			hoverClass: "element_droppable_listner_onhover",			
			
			//To delete the source element (Field) On Overhover
			over: function(event, ui) {
				//$(ui.draggable).remove();
			},
			drop: function( event, ui ) {
				//alert('drop !');
				//.attr()
				
				//Get the ID of the actual Field Row !
				var field_element_id			= $(this).attr("id");
				//Remove "field_element_container_X"
				field_element_id				= field_element_id.replace("field_element_container_", "");
				
				
				//The Options of the element on move !
				var draggable_field_id 			= ui.draggable.attr("data-field-id");
				var draggable_field_special		= ui.draggable.attr("data-field-special");
				var draggable_field_type		= ui.draggable.attr("data-field-type");
				var draggable_field_select		= ui.draggable.attr("data-field-select");
				var draggable_field_name_sql	= ui.draggable.attr("data-field-name-sql");
				var draggable_field_sql_as 		= ui.draggable.attr("data-field-name-sql-as");
				
				//Call The function that build the element !
				build_the_row_now(field_element_id, draggable_field_special, draggable_field_type, draggable_field_select);
				
				$(this).find(" .edl_field").html(ui.draggable.html());
		
				//Put the ID of this Field
				$(this).find(" .edl_hidden").html('<input type="hidden" id="field_element_'+field_element_id+'_id" value="'+draggable_field_id+'" />');
				
				
				/*$(this).find(" .edl_operator").html('Operator options ..');
				$(this).find(" .edl_value").html('Value options .. <br /> Id:'+draggable_field_id+' ** Special:'+draggable_field_special+' ** Type:'+draggable_field_type+' ** Select:'+draggable_field_select+' ** NameSQL:'+draggable_field_name_sql+' ** SQLAS:'+draggable_field_sql_as);
				*/
				
				//Remove Class to disable the Drop on this element 
				//After the Drop REFRESH (in case of element Add or Delete)
				$( this ).removeClass('element_droppable_listner');

				//Disable the Drop on the actual Listner !
                $( this ).droppable('disable');

				
				//To delete the draggable FIELD after the Drop !
					//$(ui.helper).remove(); //destroy clone
					//$(ui.draggable).remove(); //remove from list
				
			}//End drop option
		});
}

/************************************************************************************************/
/********************************	End Segment Create & Edit ***********************************/
/************************************************************************************************/


/************************************************************************************************/
/********************************	Start Segment Create ****************************************/
/************************************************************************************************/
//Function to force Finish the animation on a HTML Element
//Ex: without this function if we click twice very quickly 
//On Add Row (Field) the first animation will be stopped !
//For this reason we force the finish of the previous animation
/*function force_finish_animation(id_html_element){
	//$("#id_html_element").finish();
	$(document).finish();
}
*/

//Disable All the buttons of Add (Field and Groupe)
function disable_add_buttons(){
	//We disable the buttons
	$(".btn_row_field_add").attr("disabled", true);
	$("#groupe_add_btn").attr("disabled", true);
}

//Enable All the buttons of Add (Field and Groupe)
function enable_add_buttons(){
	//We disable the buttons
	$(".btn_row_field_add").attr("disabled", false);
	$("#groupe_add_btn").attr("disabled", false);
}


//Function to get the table fileds on segment create
function tables_get_fields_now(){
	var table_id	= document.getElementById('segment_table_list').options[document.getElementById('segment_table_list').selectedIndex].value;
	
	//Clear the fields
	segment_change_table_clear_fields();
	
	if(table_id!=''){
		//Call the new table fields
		segment_load_table_fields(table_id);
	}
}

//Function to load the table fields
function segment_load_table_fields(table_id){
	document.getElementById('segments_load_fields_loading').style.display = 'none';
	
	var OAjax;
	
	if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
	else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

	OAjax.open('POST','/fr/marketing/segments-load-table-fields.php',true);
	OAjax.onreadystatechange = function(){
		// OAjax.readyState == 1   ==>  connexion ?tablie
		// OAjax.readyState == 2   ==>  requete recue
		// OAjax.readyState == 3   ==>  reponse en cours
		
		
			if ((OAjax.readyState == 1) || (OAjax.readyState == 2) || (OAjax.readyState == 3) ){
				//document.getElementById('panel-table').style.opacity = '0.2';
				//document.getElementById('loader_panel-table').style.display = 'block';
			}
			
			if (OAjax.readyState == 4 && OAjax.status==200){
				document.getElementById('segments_load_fields_loading').style.display = 'none';

				if(OAjax.responseText !=''){

					//document.getElementById('panel-table').style.opacity = '1';
					document.getElementById('segment_top_part_right').innerHTML =''+OAjax.responseText+'';

					$( "#segment_top_part_right" ).effect('slide', 500);

				}else{
					document.getElementById('segments_load_fields_loading').style.display = 'none';
					
					document.getElementById('segment_top_part_right').innerHTML ='Erreur, merci de r&eacute;essayer !';
					
				}
			}
		}
		
	OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	OAjax.send('table_id='+table_id);
}

//Function to clear the groupes and the fields container
function segment_change_table_clear_fields(){
	document.getElementById('segment_top_part_right').innerHTML = '<span style="opacity:0.5;">Veuillez selectionner une table</span>';
	
	//Clear the groupes
	segment_build_prebuild_groupe();
}

//Function to load the Pre-build elements (1Group and 1 Field)
function segment_build_prebuild_groupe(){
	
	//Show hidden Blocs!
	document.getElementById('stats_remaining_elements').style.display	= 'block';
	document.getElementById('segment_middle_part').style.display		= 'block';
	document.getElementById('groupe_add_container').style.display		= 'block';
	document.getElementById('segment_send_part').style.display		= 'block';
	
	//Initiate the vars
	init_limits();

	//Hide the container !
	$("#segment_middle_part").hide("fast");
	
	//Creation of the first group with the first Field row Listner !
	var actual_groupe_id	= 1;
	var prebuilt_elements	= " <div id=\"groupe_"+actual_groupe_id+"\" class=\"groupe_container\">"+
									"<div class=\"groupe_top\">"+
										"<div id=\"groupe_1_what\" class=\"groupe_what\">&nbsp;</div>"+
										
										"<div id=\"groupe_1_delete\" class=\"groupe_delete\" >"+
											"&nbsp;"+
										"</div>"+
										
										"<div id=\"groupe_1_name\" class=\"groupe_name\">"+
											"<input type=\"search\" id=\"groupe_name_"+actual_groupe_id+"\" placeholder=\"Nom du groupe "+actual_groupe_id+"\" />"+
										"</div>"+
					
										"<div class=\"groupe_add_listner_field\">"+
											"<input type=\"button\" class=\"btn btn-default btn_row_field_add\" value=\"Ajouter un champ\" onclick=\"segment_add_new_field_row('"+actual_groupe_id+"')\" />"+
										"</div>"+
					
									"</div><!-- end div .groupe_top -->"+
				
									"<div id=\"groupe_middle_"+actual_groupe_id+"\" class=\"groupe_middle\">"+
										"<div id=\"field_element_container_"+actual_groupe_id+"\" class=\"element_droppable_listner groupe_field_onerow\">"+
											"<div class=\"edl_what\">"+
												" "+
											"</div>"+
					
											"<div class=\"edl_second_container\">"+
												"<div class=\"edl_field\">"+
													"<span class=\"field_placeholder\">Glissez & d&eacute;posez votre champ pour ajouter un nouveau filtre</span>"+
												"</div><!-- end div .edl_field -->"+
												"<div class=\"edl_operator\">"+
													"&nbsp;"+
												"</div><!-- end div .edl_operator -->"+
												"<div class=\"edl_value\">"+
													"&nbsp;"+
												"</div><!-- end div .edl_value -->"+
												"<div class=\"edl_hidden\">"+
							
												"</div><!-- end div .edl_hidden -->"+
												"<div class=\"edl_remove\" style=\"margin-top:15px;\">"+
													"<div title=\"Vider ce champ\""+
													" onclick=\"javascript:field_listner_clean_row('1');\">"+
														"<i class=\"fa fa-refresh\"></i></div>"+	
												"</div><!-- end div .edl_remove -->"+
											"</div>"+
					
										"</div><!-- end div .element_droppable_listner -->"+
				
				
									"</div><!-- end div .groupe_middle-->"+
				
								"</div><!-- div #groupe_X-->";
									
	document.getElementById('segment_middle_part').innerHTML	 = prebuilt_elements;
	
	//Tell the user the number of remaining items
	tell_user_remaining_items(hidden_groupe_limitation, hidden_field_limitation);
		
	//Show again the container !	
	$("#segment_middle_part").show("slow");

	
	//Init the Sortable and Droppable
	activate_draggable_for_the_fields();

}

//Function Add new Field row
function segment_add_new_field_row(id_groupe_destination){

	//The limitation
	var local_hidden_field_limitation = document.getElementById('hidden_field_limitation').value;
	
	//The row to increment !
	var local_hidden_field_incrementation 	= document.getElementById('hidden_field_incrementation').value;

	
	//Test if we have the right to add a new row 
	if(local_hidden_field_limitation>0){
		
		//Disable All the buttons of Add (Field and Groupe)
		disable_add_buttons();
	
		//Decrement the elements !
		local_hidden_field_limitation--;
		document.getElementById('hidden_field_limitation').value	= local_hidden_field_limitation;
		
		//The number of created elements !
		local_hidden_field_incrementation++;
		document.getElementById('hidden_field_incrementation').value	= local_hidden_field_incrementation;
	
		//Tell the user the number of remaining items
		tell_user_remaining_items(document.getElementById('hidden_groupe_limitation').value,  local_hidden_field_limitation);
		
		//Start builiding elements !
		/*********************************************************/
		
		//Detect if it's the first child or not !		
		//We will not add the Radio (Et || Ou) because it's the first element
		var field_row_radio		= "&nbsp;";
		var field_row_delete	= "&nbsp;";
		if($( '#groupe_middle_'+id_groupe_destination ).children().length>0){
			
			//Test if the groupe has child (Fields Rows) 
			//Because we can't delete the first row to avoid the problemes ("AND" or "OR") 
			
			field_row_radio		= " <input type=\"radio\" id=\"row_field_what_"+local_hidden_field_incrementation+"_and\" name=\"row_field_what"+local_hidden_field_incrementation+"\" value=\"and\" checked=\"true\" />"+
									"<label for=\"row_field_what_"+local_hidden_field_incrementation+"_and\">Et</label>"+
									"<input type=\"radio\" id=\"row_field_what_"+local_hidden_field_incrementation+"_or\" name=\"row_field_what"+local_hidden_field_incrementation+"\" value=\"or\" />"+
									"<label for=\"row_field_what_"+local_hidden_field_incrementation+"_or\">Ou</label>";
									
			//When it's not the first Row we do not show the delete option !
			field_row_delete	= "<div title=\"Supprimer ce champ\""+ 
									"onclick=\"javascript:field_listner_delete_row('"+id_groupe_destination+"', '"+local_hidden_field_incrementation+"');\">"+
									"<i class=\"fa fa-trash-o\"></i></div>";
		}else{
			//It's the first Row !
			field_row_delete	= "<div title=\"Vider ce champ\" style=\"margin-top:10px;\" "+ 
									"onclick=\"javascript:field_listner_clean_row('"+local_hidden_field_incrementation+"');\">"+
									"<i class=\"fa fa-refresh\"></i></div>";
		}//end if !
		
		var built_element	= "<div id=\"field_element_container_"+local_hidden_field_incrementation+"\" class=\"element_droppable_listner groupe_field_onerow ui-droppable\">"+
									"<div class=\"edl_what\">"+
										""+field_row_radio+""+
									"</div>"+
			
									"<div class=\"edl_second_container\">"+
										"<div class=\"edl_field\">"+
											"<span class=\"field_placeholder\">Glissez & d&eacute;posez votre champ pour ajouter un nouveau filtre</span>"+
										"</div><!-- end div .edl_field -->"+
										"<div class=\"edl_operator\">"+
											"&nbsp;"+
										"</div><!-- end div .edl_operator -->"+
										"<div class=\"edl_value\">"+
											"&nbsp;"+
										"</div><!-- end div .edl_value -->"+
										"<div class=\"edl_hidden\">"+
					
										"</div><!-- end div .edl_hidden -->"+
										"<div class=\"edl_remove\">"+
											field_row_delete+
										"</div><!-- end div .edl_remove -->"+
									"</div>"+
			
								"</div><!-- end div .element_droppable_listner -->";
		
		/*********************************************************/
		//End Building elements !



		//document.getElementById('groupe_middle_'+id_groupe_destination).innerHTML	+= built_element;
		
		$("#groupe_middle_"+id_groupe_destination).append(built_element);
		
		
		//Stop the previous animation if it's the case
		//force_finish_animation();
		
		//Add a effect when the new row is on page !
		$("#field_element_container_"+local_hidden_field_incrementation).effect('slide', 500, function(){
			//Enable the Add Buttons (Field, Groupe..)
			enable_add_buttons();
		});
		
		
		//Refresh the listner on Drop !
		activate_droppable_for_the_fields();
		
		
		
	}else{
		//alert('You do not have the right !!');
		var title 	= 'Limite des \351lements !';
		var errors	= 'Vous avez atteint la limite d\'\351lements !';
		segment_error_display_modal('segment_errors', title, errors);
	}
	

	//Container where to add the row => groupe_middle_"+id_groupe_destination+"
}

//Function to Clean the First Row in every Group !
function field_listner_clean_row(id_row){
	$("#field_element_container_"+id_row+"").find(" .edl_field").html('<span class=\"field_placeholder\">Glissez & d&eacute;posez votre champ pour ajouter un nouveau filtre</span>');
	
	$("#field_element_container_"+id_row+"").find(" .edl_operator").html('&nbsp;');
	
	$("#field_element_container_"+id_row+"").find(" .edl_value").html('&nbsp;');
	
	$("#field_element_container_"+id_row+"").find(" .edl_hidden").html('&nbsp;');
	
	//Remove all class  => "groupe_field_onerow ui-droppable ui-droppable-disabled"
	//And Add all the class name to this one to be able for the Droppable
	$("#field_element_container_"+id_row+"").removeClass().addClass("element_droppable_listner groupe_field_onerow ui-droppable");
	
	
	//Init The droppable for the actual Element
	$("#field_element_container_"+id_row+"").droppable();
	$("#field_element_container_"+id_row+"").droppable('enable');
	
	//To be sure because it's not working on Second and Third groupe we refresh all the fields listners Drop
	activate_droppable_for_the_fields();
}


//Function to tell to the user the elements that he can use (Groupe and Fields)
function tell_user_remaining_items(nb_groupes, nb_fields){

	var string_to_show	= "Vous pouvez utiliser ";
	if(nb_groupes==1){
		string_to_show	+= "<b> 1 Groupe</b> ";
	}else if(nb_groupes>1){
		string_to_show	+= "<b> "+nb_groupes+" Groupes</b> ";
	}else{
		string_to_show	+= "<b> 0 Groupe</b> ";
	}
	
	string_to_show	+= " et ";
	if(nb_fields==1){
		string_to_show	+= "<b> 1 Champ</b> ";
	}else if(nb_fields>1){
		string_to_show	+= "<b> "+nb_fields+" Champs</b> ";
	}else{
		string_to_show	+= "<b> 0 Champ</b> ";
	}
	
	document.getElementById('stats_remaining_elements').innerHTML	= string_to_show;
	
	$("#stats_remaining_elements").effect('pulsate', 250);
	
}

//Function to show the modal errors !
function segment_error_display_modal(id, title, errors){
	document.getElementById(id).innerHTML	= errors;
	$( "#"+id ).dialog({
		resizable: false,
		draggable: false,
		height:220,
		width: 500,
		modal: true,
		title: title,
		buttons: {
			"Fermer": function(){
				$( this ).dialog( "close" );
			},
			/*"Non": function(){
				$( this ).dialog( "close" );
			}*/
		},
		show: {
			effect: "fade",
			duration: 300
		},
		hide: {
			effect: "puff",
			duration: 220
		}
    });
}

//Function for delete row
function field_listner_delete_row(idgroupe, idelement){
	//Ask the confirmation
	var title 	= 'Confirmation de suppression !';
	var errors	= '&Ecirc;tes vous s&ucirc;r de vouloir supprimer ce filtre ?';
	segment_ask_delete_row_display_modal('segment_actions_ask', title, errors, idgroupe, idelement);
	
}

//Function to show the confirm for the action Ask !
function segment_ask_delete_row_display_modal(id, title, errors, idgroupe, idelement){
	document.getElementById(id).innerHTML	= errors;
	$( "#"+id ).dialog({
		resizable: false,
		draggable: false,
		height:220,
		width: 500,
		modal: true,
		title: title,
		buttons: {
			"Oui": function(){
				field_listner_delete_row_confirmation(idgroupe, idelement);
				$( this ).dialog( "close" );
			},
			"Non": function(){
				$( this ).dialog( "close" );
			}
		},
		show: {
			effect: "fade",
			duration: 300
		},
		hide: {
			effect: "puff",
			duration: 220
		}
    });
}

//Function for Confirm delete row
function field_listner_delete_row_confirmation(idgroupe, idelement){
	
	//The animation of the Hide !
	$("#field_element_container_"+idelement).hide("slow", function(){
		//Remove a field Row
		document.getElementById("groupe_middle_"+idgroupe).removeChild(document.getElementById("field_element_container_"+idelement));
		
	});
	

	//Incrementation
	var local_hidden_field_limitation 		= document.getElementById('hidden_field_limitation').value;
	local_hidden_field_limitation++;
	document.getElementById('hidden_field_limitation').value	= local_hidden_field_limitation;
	
	//Tell to user the remaining elements !
	tell_user_remaining_items(document.getElementById('hidden_groupe_limitation').value, local_hidden_field_limitation);
	
	//Refresh the Drag fields after the Delete
	activate_droppable_for_the_fields();

}



//Function for add a new groupe
function segment_add_groupe(){
	//Get the remaining groupes
	
	//The limitation
	var local_hidden_groupe_limitation = document.getElementById('hidden_groupe_limitation').value;
	
	//The row to increment !
	var local_hidden_groupe_incrementation 	= document.getElementById('hidden_groupe_incrementation').value;

	
	//Test if we have the right to add a new row 
	if(local_hidden_groupe_limitation>0){
		//Disable the add buttons (Fields, Groupes)
		disable_add_buttons();
		
		//Incrementing to put the new id !
		local_hidden_groupe_incrementation++;
		document.getElementById('hidden_groupe_incrementation').value	= local_hidden_groupe_incrementation;
		
		
		//Decrementing the remaining rows
		local_hidden_groupe_limitation--;
		document.getElementById('hidden_groupe_limitation').value	= local_hidden_groupe_limitation;
		
		//Tell the user the number of remaining items
		tell_user_remaining_items(local_hidden_groupe_limitation, document.getElementById('hidden_field_limitation').value);
		
		
		//Start Building element !
		var actual_groupe_id= local_hidden_groupe_incrementation;
		
		var groupe_row_radio		= " <input type=\"radio\" id=\"row_groupe_what_"+local_hidden_groupe_incrementation+"_and\" name=\"row_groupe_what"+local_hidden_groupe_incrementation+"\" value=\"AND\" checked=\"true\" />"+
									"<label for=\"row_groupe_what_"+local_hidden_groupe_incrementation+"_and\">Et</label>"+
									"<input type=\"radio\" id=\"row_groupe_what_"+local_hidden_groupe_incrementation+"_or\" name=\"row_groupe_what"+local_hidden_groupe_incrementation+"\" value=\"OR\" />"+
									"<label for=\"row_groupe_what_"+local_hidden_groupe_incrementation+"_or\">Ou</label>";
									
									
		var built_element	= " <div id=\"groupe_"+actual_groupe_id+"\" class=\"groupe_container\">"+
									"<div class=\"segment-groupe-line-separator\">&nbsp;</div>"+
									"<div class=\"groupe_top\">"+
										"<div id=\"groupe_"+actual_groupe_id+"_what\" "+ 
											"class=\"groupe_what\">"+groupe_row_radio+"</div>"+
										
										"<div id=\"groupe_"+actual_groupe_id+"_delete\" "+ 
											"class=\"groupe_delete\">"+
											"<div title=\"Supprimer ce groupe\" onclick=\"field_listner_delete_groupe('"+actual_groupe_id+"');\">"+
												"<i class=\"fa fa-trash\"></i>"+
											"</div>"+
										"</div>"+
										
										"<div id=\"groupe_"+actual_groupe_id+"_name\" class=\"groupe_name\">"+
											"<input type=\"search\" id=\"groupe_name_"+actual_groupe_id+"\" placeholder=\"Nom du groupe "+actual_groupe_id+"\" />"+
										"</div>"+
					
										"<div class=\"groupe_add_listner_field\">"+
											"<input type=\"button\" class=\"btn btn-default btn_row_field_add\" value=\"Ajouter un champ\" onclick=\"segment_add_new_field_row('"+actual_groupe_id+"')\" />"+
										"</div>"+
					
									"</div><!-- end div .groupe_top -->"+
				
									"<div id=\"groupe_middle_"+actual_groupe_id+"\" class=\"groupe_middle\">"+
										
									"</div><!-- end div .groupe_middle-->"+
				
								"</div><!-- div #groupe_X-->";
		
		//Put the Built element on place !
		//document.getElementById('segment_middle_part').innerHTML	+= built_element;
		$("#segment_middle_part").append(built_element);

		//Add a effect when the new groupe is on page !
		$("#groupe_"+actual_groupe_id).effect('slide', 500, function(){
			//Enable the Add Buttons (Field, Groupe..)
			enable_add_buttons();
		});
		
	}else{
		var title 	= 'Limite des \351lements !';
		var errors	= 'Vous avez atteint la limite des groupes !';
		segment_error_display_modal('segment_errors', title, errors);
	}//end else if test remaining groupes !
}


//Function for delete groupe !
function field_listner_delete_groupe(idgroupe){
	//Ask the confirmation
	var title 	= 'Confirmation de suppression !';
	var errors	= '&Ecirc;tes vous s&ucirc;r de vouloir supprimer ce groupe ?';
	segment_ask_delete_groupe_display_modal('segment_actions_ask', title, errors, idgroupe);
	
}

//Function To ask the Row delete !
function segment_ask_delete_groupe_display_modal(id, title, errors, idgroupe){
	document.getElementById(id).innerHTML	= errors;
	$( "#"+id ).dialog({
		resizable: false,
		draggable: false,
		height:220,
		width: 500,
		modal: true,
		title: title,
		buttons: {
			"Oui": function(){
				segment_delete_groupe_confirmation(idgroupe);
				$( this ).dialog( "close" );
			},
			"Non": function(){
				$( this ).dialog( "close" );
			}
		},
		show: {
			effect: "fade",
			duration: 300
		},
		hide: {
			effect: "puff",
			duration: 220
		}
    });
}

//Function for Delete Groupe confirmation
function segment_delete_groupe_confirmation(idgroupe){
	
	//Get the count of childs to increment it in the Field variable limit 
	var row_child	= $( '#groupe_middle_'+idgroupe ).children().length;
	
	//Get the actual elements
	
	var local_hidden_groupe_limitation 	= document.getElementById('hidden_groupe_limitation').value;
	var local_hidden_field_limitation 	= document.getElementById('hidden_field_limitation').value;
	
	var elements_will_be_destroyed		= $( '#groupe_middle_'+idgroupe ).children().length;
	

	//Calculate the new values
	local_hidden_field_limitation = parseInt(local_hidden_field_limitation) + parseInt(elements_will_be_destroyed);
	local_hidden_groupe_limitation++;
	
	//Save the new values
	document.getElementById('hidden_field_limitation').value	= local_hidden_field_limitation;
	document.getElementById('hidden_groupe_limitation').value	= local_hidden_groupe_limitation;
	
	
	//The animation of the Hide !
	$("#groupe_"+idgroupe).hide("slow", function(){
		//Remove the Group
		document.getElementById("segment_middle_part").removeChild(document.getElementById("groupe_"+idgroupe));
		
	});

	
	//alert(row_child);
	
	//Tell the user the number of remaining items
	tell_user_remaining_items(local_hidden_groupe_limitation, local_hidden_field_limitation);
}

/************************************************************************************************/
/********************************	End Segment Create ******************************************/
/************************************************************************************************/



/************************************************************************************************/
/********************************	Start Segment Edit ******************************************/
/************************************************************************************************/
//The edit elements

/************************************************************************************************/
/********************************	End Segment Edit ********************************************/
/************************************************************************************************/

/************************************************************************************************/
/********************************	Start Segment Refresh  **************************************/
/************************************************************************************************/
//Refresh
function segment_refresh(segment_id){
	var title 	= 'Rafraichissement du segment !';
	var errors	= '<br /><br /><br /><center>'+
						'<img src="ressources/images/loading.gif" alt="Rafraichissement en cours.." title="Rafraichissement en cours.." />'+
					'</center>';
	segment_ask_refresh_segment_display_modal('segment_actions_ask', title, errors);
	
	//Start the process to init the refresh !
	var OAjax;
	
	if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
	else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

	OAjax.open('POST','/fr/marketing/segments-refresh.php',true);
	OAjax.onreadystatechange = function(){
		// OAjax.readyState == 1   ==>  connexion ?tablie
		// OAjax.readyState == 2   ==>  requete recue
		// OAjax.readyState == 3   ==>  reponse en cours
		
		
			if ((OAjax.readyState == 1) || (OAjax.readyState == 2) || (OAjax.readyState == 3) ){
				//document.getElementById('panel-table').style.opacity = '0.2';
				//document.getElementById('loader_panel-table').style.display = 'block';
			}
			
			if (OAjax.readyState == 4 && OAjax.status==200){
				document.getElementById('loader_panel-table').style.display = 'none';

				//if(OAjax.responseText !=''){

					//document.getElementById('panel-table').style.opacity = '1';
					document.getElementById('segment_actions_ask').innerHTML=''+OAjax.responseText+'';
					if(document.getElementById('segmet_final_results_container_js')){
						eval(document.getElementById('segmet_final_results_container_js').innerHTML);
					}

				//}else{
					//mmf_hide_autocomplete();
				//}
			}
		}
		
	OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	OAjax.send('segment_id='+segment_id);
	
	return false;
	
}


//Function to show the modal errors !
function segment_ask_refresh_segment_display_modal(id, title, errors){
	document.getElementById(id).innerHTML	= errors;
	$( "#"+id ).dialog({
		resizable: false,
		draggable: false,
		height:320,
		width: 500,
		modal: true,
		title: title,
		buttons: {
			"Fermer": function(){
				$( this ).dialog( "close" );
			},
			/*"Non": function(){
				$( this ).dialog( "close" );
			}*/
		},
		show: {
			effect: "fade",
			duration: 300
		},
		hide: {
			effect: "puff",
			duration: 220
		}
    });
}


//Function for the redirection 
function refresh_page_after_segment_execution(){
	setTimeout(function(){ 
		//document.location='/fr/marketing/my-segments.php'; 
		
		$( "#segment_actions_ask").dialog( "close" );
	}, 2000);
	
	//Reload the search !
	segments_load_list_show();
}


/************************************************************************************************/
/********************************	End Segment Refresh  ****************************************/
/************************************************************************************************/

/************************************************************************************************/
/********************************	Start Segment Refresh  **************************************/
/************************************************************************************************/

//Delete
function segment_delete(segment_id){
	document.getElementById("segment_actions_ask").innerHTML	= '<br /><br /><br /><center>'+
						'<img src="ressources/images/loading.gif" alt="Suppression en cours.." title="Suppression en cours.." />'+
					'</center>';
				
	//Start the process to delete the element !
	var OAjax;
	
	if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
	else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

	OAjax.open('POST','/fr/marketing/segments-delete.php',true);
	OAjax.onreadystatechange = function(){
		// OAjax.readyState == 1   ==>  connexion ?tablie
		// OAjax.readyState == 2   ==>  requete recue
		// OAjax.readyState == 3   ==>  reponse en cours
		
		
			if ((OAjax.readyState == 1) || (OAjax.readyState == 2) || (OAjax.readyState == 3) ){
				//document.getElementById('panel-table').style.opacity = '0.2';
				//document.getElementById('loader_panel-table').style.display = 'block';
			}
			
			if (OAjax.readyState == 4 && OAjax.status==200){
				document.getElementById('loader_panel-table').style.display = 'none';

				//if(OAjax.responseText !=''){

					//document.getElementById('panel-table').style.opacity = '1';
					document.getElementById('segment_actions_ask').innerHTML=''+OAjax.responseText+'';
					if(document.getElementById('segmet_final_results_container_js')){
						eval(document.getElementById('segmet_final_results_container_js').innerHTML);
					}

				//}else{
					//mmf_hide_autocomplete();
				//}
			}
		}
		
	OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	OAjax.send('segment_id='+segment_id);
	
	return false;
}


//Function to ask how the modal errors !
function segment_ask_delete_segment_display_modal(segment_id){
	document.getElementById("segment_actions_ask").innerHTML	= "<br /><br /><br />&Ecirc;tes vous s&ucirc;r de vouloir supprimer ce segment ?";
	$( "#segment_actions_ask" ).dialog({
		resizable: false,
		draggable: false,
		height:320,
		width: 500,
		modal: true,
		title: "Suppression de segment !",
		buttons: {
			"Oui": function(){
				segment_delete(segment_id)
				//$( this ).dialog( "close" );
			},
			"Non": function(){
				$( this ).dialog( "close" );
			}
		},
		show: {
			effect: "fade",
			duration: 300
		},
		hide: {
			effect: "puff",
			duration: 220
		}
    });
}


//Function for the redirection 
function redirect_page_after_segment_delete(){
	setTimeout(function(){ document.location='/fr/marketing/my-segments.php'; }, 1500);
}

/************************************************************************************************/
/********************************	End Segment Delete  *****************************************/
/************************************************************************************************/