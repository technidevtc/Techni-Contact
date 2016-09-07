//Function that load the list of Data
//Ex: When we choose the "Table" as Source we get the tables on a Select !
//Ex: When we choose the "Segment" as Source we get the segments on a Select !
function segment_export_change_source(source, segment_id){
	
	//Call to clear all the elements !
	clear_all_the_blocks();
	
	//Test on source "table" or "segment"
	if(source=='tables'){
		var OAjax;
	
		if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
		else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

		OAjax.open('POST','/fr/marketing/segments-export-get-list-tables-name.php',true);
		OAjax.onreadystatechange = function(){
			// OAjax.readyState == 1   ==>  connexion ?tablie
			// OAjax.readyState == 2   ==>  requete recue
			// OAjax.readyState == 3   ==>  reponse en cours
			
			
				if ((OAjax.readyState == 1) || (OAjax.readyState == 2) || (OAjax.readyState == 3) ){
					//document.getElementById('panel-table').style.opacity = '0.2';
					//document.getElementById('loader_panel-table').style.display = 'block';
				}
				
				if (OAjax.readyState == 4 && OAjax.status==200){
					//document.getElementById('loader_panel-table').style.display = 'none';

					//if(OAjax.responseText !=''){

						//document.getElementById('panel-table').style.opacity = '1';
						document.getElementById('segment_source_choice_zone').innerHTML =''+OAjax.responseText+'';

					//}else{
						//mmf_hide_autocomplete();
					//}
				}
			}
			
		OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
		OAjax.send();
		
		return false;
		
	}else if(source=='segments'){
		var OAjax;
	
		if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
		else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

		OAjax.open('POST','/fr/marketing/segments-export-get-list-segments-name.php',true);
		OAjax.onreadystatechange = function(){
			// OAjax.readyState == 1   ==>  connexion ?tablie
			// OAjax.readyState == 2   ==>  requete recue
			// OAjax.readyState == 3   ==>  reponse en cours
			
			
				if ((OAjax.readyState == 1) || (OAjax.readyState == 2) || (OAjax.readyState == 3) ){
					//document.getElementById('panel-table').style.opacity = '0.2';
					//document.getElementById('loader_panel-table').style.display = 'block';
				}
				
				if (OAjax.readyState == 4 && OAjax.status==200){
					//document.getElementById('loader_panel-table').style.display = 'none';

					//if(OAjax.responseText !=''){

						//document.getElementById('panel-table').style.opacity = '1';
						document.getElementById('segment_source_choice_zone').innerHTML =''+OAjax.responseText+'';
						//Detect if the segment ID is not null then charge the Fields Block !
						if(segment_id){
							segments_source_choosed('segment');
						}
					//}else{
						//mmf_hide_autocomplete();
					//}
				}
			}
			
		OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
		
		if(segment_id){
			OAjax.send('segment_id='+segment_id);
		}else{
			OAjax.send();
		}
		return false;
	}
	
	
}


//Function to clear the elements on source change
function clear_all_the_blocks(){
	//The Fields Block !
	document.getElementById('segment_top_part_right').innerHTML	= '<span style="opacity:0.5;">Veuillez selectionner une source..</span>';
	
	//The selected Fields !
	document.getElementById('segment_export_reception_elements').innerHTML	= '&nbsp;';
	
	//Hide the important elements !
	hide_important_elements();
}

function segments_source_choosed(source){
	
	var id	=	document.getElementById('select_source_data').options[document.getElementById('select_source_data').selectedIndex].value;
	
	//Clear everything !
	clear_all_the_blocks();
			
	//If the selection is a blank one we have to clear everything
	if(id==''){
		//Do not do anything !
	}else{

			document.getElementById('segment_top_part_right').innerHTML	= '<img id="segments_load_fields_loading" src="ressources/images/loading2.gif" style="display:block;" />';
			
			
			//Loading the Fields !
			var OAjax;
			
			if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
			else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

			OAjax.open('POST','/fr/marketing/segments-export-get-list-fields.php',true);
			OAjax.onreadystatechange = function(){
				// OAjax.readyState == 1   ==>  connexion ?tablie
				// OAjax.readyState == 2   ==>  requete recue
				// OAjax.readyState == 3   ==>  reponse en cours
				
				
					if ((OAjax.readyState == 1) || (OAjax.readyState == 2) || (OAjax.readyState == 3) ){
						//document.getElementById('panel-table').style.opacity = '0.2';
						//document.getElementById('loader_panel-table').style.display = 'block';
					}
					
					if (OAjax.readyState == 4 && OAjax.status==200){
						//document.getElementById('loader_panel-table').style.display = 'none';

						//if(OAjax.responseText !=''){

							//document.getElementById('panel-table').style.opacity = '1';
							document.getElementById('segment_top_part_right').innerHTML=''+OAjax.responseText+'';
							
							
							//Activate the Draggability
							segments_export_activate_draggable_for_fields();
							
						//}else{
							//mmf_hide_autocomplete();
						//}
					}
				}
				
			OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
			

			OAjax.send('type='+source+'&id='+id);
			
			return false;
	}//End else if the blank one is choosed !
}

//Function that show the important elements
function show_important_elements(){
	document.getElementById('segment_middle_part').style.display	= "block";
	document.getElementById('groupe_add_container').style.display	= "block";
	document.getElementById('segment_send_part').style.display	= "block";
}

//Function that hide the important elements
function hide_important_elements(){
	document.getElementById('segment_middle_part').style.display	= "none";
	document.getElementById('groupe_add_container').style.display	= "none";
	document.getElementById('segment_send_part').style.display	= "none";
}



//Function that Active the Drag for the Fields !
function segments_export_activate_draggable_for_fields(){
	
	//Make the buttons and reception element visible !
	show_important_elements();
	
	//The move & sort
	$( "#segment_top_part_right, #segment_export_reception_elements" ).sortable({
		connectWith: ".connected_container_fields",
		placeholder: "export_field_sortable",
		tolerance: 'pointer'
		
	}).disableSelection();
	
	
}

//Add all the fields 
function segment_export_add_all_fields(){
	//Check if IT have at least one Element !
	if($("#segment_top_part_right").children().length>0){
		
		//local_fields_count=1;
		$("#segment_top_part_right").children().each(function(){
			
			//Affect the actual Field to the result container !
			$("#segment_export_reception_elements").append($(this));
			
			$(this).css("display","none");
			$(this).toggle("explode", 900 );
			//highlight
			
		});
	}
}

//Remove all the fields
function segment_export_remove_all_fields(){
	//Check if IT have at least one Element !
	if($("#segment_export_reception_elements").children().length>0){
		
		//local_fields_count=1;
		$("#segment_export_reception_elements").children().each(function(){
			
			//Affect the actual Field to the result container !
			$("#segment_top_part_right").append($(this));
			
			$(this).css("display","none");
			$(this).toggle("explode", 900 );
			
		});
	}
}


//Start Export

function segment_export_lunch(){
	var etat				= 1;
	var source				= '';
	var selected_value		= document.getElementById('select_source_data').options[document.getElementById('select_source_data').selectedIndex].value;
	var selected_fields		= '';
	
	
	if(document.getElementById('segment_source_table').checked){
		source	= 'table';
	}else if(document.getElementById('segment_source_segment').checked){
		source	= 'segment';
	}
	
	if(source==''){
		etat	= 0;
	}
	
	if(selected_value==''){
		etat	= 0;
	}	
	
	//Count the selectionned elements !
	if($("#segment_export_reception_elements").children().length==0){
		etat	= 0;
	}else{
		$("#segment_export_reception_elements").children().each(function(){
			selected_fields += $(this).attr('data-field-id')+'##';
		});
		
	}
	
	
	if(etat==1){
		
		document.getElementById('export_v_source').value			= source;
		document.getElementById('export_v_selected_value').value	= selected_value;
		document.getElementById('export_v_selected_fields').value	= selected_fields;
		
		//alert(source+' ** '+selected_value+' ** '+selected_fields);
		
		document.getElementById('export_send_form').submit();
		
	}else{
		segment_export_validation_display_errors_in_modal('segment_actions_ask', 'Erreurs de validation !', '<br />Vous devez selectionner au moins un champ d\'export !');
	}
}

//Function th show the Form Validation Errors in Modal !
function segment_export_validation_display_errors_in_modal(id, title, errors){
	document.getElementById(id).innerHTML	= errors;
	$( "#"+id ).dialog({
		resizable: false,
		draggable: false,
		height:300,
		width: 450,
		modal: true,
		title: title,
		buttons: {
			/*"Oui": function(){
				$( this ).dialog( "close" );
			},*/
			"Ok": function(){
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
