//The number of vars
var global_errors_validation	= '';
//We use this variable to put a groupe id in the OUTPUT XML 
var local_groupe_id_output		= 0;
//We use this variable for testing if the groupe ID Exist !
var local_groupe_count_id		= 1;
//Increment in the Fields read Loop
var local_fields_count			= 1;


//To put the Field Return !
var local_element_output	= '';



//Creation From Validation
function segment_create_validation(){
	
	global_errors_validation		= '';
	local_groupe_id_output			= 0;
	
	var etat						= 1;
	var segment_name				= document.getElementById('segment_name').value;
	var segment_typology			= '';
	var segment_used_table			= document.getElementById('segment_table_list').options[document.getElementById('segment_table_list').selectedIndex].value;
	
	//Segment Typology
	if(document.getElementById('segment_static_typology').checked){
		segment_typology	= 'statique';
	}else{
		segment_typology	= 'dynamique';
	}
	
	
	//The char to send 
	var global_informations_send	= '<?xml version="1.0" encoding="UTF-8"?>';
	
	
	
	//alert('Nombre de groupe utilis\351 '+user_groupes+' * '+segment_typology+' * IDTable:'+segment_used_table);
	
	
	
	
	//Validate the segment name
	if(segment_name.length<3){
		//document.getElementById('product_add_form_validation_error').innerHTML 	+= '<br />';
		global_errors_validation	= '<div class="segment_errors_head">- Le <b>nom</b> du segment est obligatoire</div>';
		etat 	= 0;
	}else{
		if(segment_name.match(/^\s*$/)){
			global_errors_validation	= '<div class="segment_errors_head">- Le <b>nom</b> du segment est obligatoire</div>';
			etat 	= 0;
		}
	}
	
	
	global_informations_send	+= '<contents>';
	global_informations_send	+= '<content id="segment_create">';
	global_informations_send	+= '<groupes>';
	
		//We have to fetch every groupe and detect it's child count
		//If the count>0 we have to validate each one else we do not have to take it !


		//Init the groupe start
		var local_groupe_count=1;
					
		$('#segment_middle_part').children(local_groupe_count).each(function(){
		//while(user_groupes>0){
			
			//We have two kind of groupe count 
				//==> The first one "local_groupe_count" Is for the position on the Queue
				//==> The Second one "local_groupe_count_id" is the ID of the element
				//Because we can add and remove, add and remove groupes so the ID's of groupes 
				//Changes because it's always incrementing to avoid to have a used groupe ID
			
			var temp_local_groupe_count_id	= $(this).attr("id");
			local_groupe_count_id			= temp_local_groupe_count_id.replace('groupe_','');
				
			//Detect if the Groupe exist

			if($('#groupe_middle_'+local_groupe_count_id).children().length>0){
				
				
				
				//Increment the Groupe ID to put in the OUTPUT 
				//Because we must have a suite ID's
				local_groupe_id_output++;

				
				//Groupe Head
				global_informations_send	+= '<groupe id="'+local_groupe_id_output+'">';
				
				//Validate the Groupe Name !
				var groupe_name				= document.getElementById('groupe_name_'+local_groupe_count_id).value;
				
				
				global_informations_send	+= '<name>'+groupe_name+'</name>';
				if(local_groupe_count_id==1){
					global_informations_send	+= '<operation>1</operation>';
				}else{
					//It's not the first groupe so we have to Know if it's a "AND" Or "OR"
					if(document.getElementById('row_groupe_what_'+local_groupe_count_id+'_and').checked){
						global_informations_send	+= '<operation>AND</operation>';
					}else{
						global_informations_send	+= '<operation>OR</operation>';
					}
					
				}
				
				//Validate the groupe name
				if(groupe_name.length<3){
					//document.getElementById('product_add_form_validation_error').innerHTML 	+= '<br />';
					global_errors_validation	+= '<div class="segment_errors_head">- Le <b>nom</b> du groupe  '+local_groupe_count+' est obligatoire</div>';
					etat 	= 0;
				}else{
					if(groupe_name.match(/^\s*$/)){
						global_errors_validation	+= '<div class="segment_errors_head">- Le <b>nom</b> du groupe  '+local_groupe_count+' est obligatoire</div>';
						etat 	= 0;
					}
				}
				
				//Start Fields Head
				global_informations_send	+= '<fields count="'+$( '#groupe_middle_'+local_groupe_count_id ).children().length+'">';
				
				
				
				
				
				//Test If the groupe Have a Childs
					//(if it have a child) we have to test each one with the appropriate function By it's Type !
					//if($( '#groupe_middle_'+local_groupe_count_id ).children().length>0){
						
					//Init the count for the Fields in the next Groupe
					var local_fields_count=1;
						
					$('#groupe_middle_'+local_groupe_count_id).children(local_fields_count).each(function(){
						//alert($(this).attr("id"));
					
						//Get the ID of each element and send it to the validation !
						//Because we have two types of count 
							//=> The First one "local_fields_count" is for getting the position in the Jquery Children
							//=> The Second one "id_element" to get the ID of the Field element 
							
						var id_element_full	= $(this).attr("id");
						
						var id_element		= id_element_full.replace('field_element_container_','');
						
						global_informations_send	+= '<field id="'+local_fields_count+'">';
						
							//Get the element Type and validate it and add the elements !!!
							local_element_output	= '';
						
							//We pass the id_element and the position of the element
							//If it's on the first position or not
							//And the position of the groupe to use it in the Errors Output ..
							var local_etat	= validate_this_field(id_element, local_fields_count, local_groupe_count);
								
							if(local_etat=='-1'){
								etat 	= 0;
							}
							
							//We add the Field informations to the list !
							global_informations_send	+= local_element_output;

						global_informations_send	+= '</field>';
						
						//And increment the count value
						local_fields_count++;
						

					});//End while the fields elements 
				
				
					global_informations_send	+= '</fields>';
				global_informations_send	+= '</groupe>';
			

			}
			//End test if this groupe have at least one element !
			//When we found the Groupe we have to desincrement the remaining groupe variable 
			//We have to increment it also if their's no child !
			
				
			
			//Increment the id to test if the next groupe ID Exist !
			local_groupe_count++;
		});//End while (Loop for the Groupes)
	
	global_informations_send	+= '</groupes>';
	global_informations_send	+= '</content>';
	global_informations_send	+= '</contents>';
		
	//REplace all "&" that exist on the XML
	var find = '&';
	var re = new RegExp(find, 'g');

	global_informations_send_temp	= global_informations_send;
	global_informations_send = global_informations_send_temp.replace(re, '#amp;');
	
	
	//Write in console
	//console.log(global_informations_send);
	//console.dirxml(global_informations_send);
	var XML_global_informations_send = new DOMParser();
	var doc__ = XML_global_informations_send.parseFromString(global_informations_send, "text/xml");  
	console.log(doc__.firstChild);
	
	//Before Send we check If we have Errors
	if(etat==1){
		
		var variable_show_start_process		= '<br /><br /><br /><center>'+
													'<img src="ressources/images/loading.gif" alt="Cr&eacute;ation en cours.." title="Cr&eacute;action en cours.." />'+
												'</center>';
		//Show the Modal for the send process
		segment_form_validation_display_errors_in_modal('segment_actions_ask', 'Cr\351ation de segment !', variable_show_start_process);
		
		//Start Send
		var OAjax;
	
		if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
		else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

		OAjax.open('POST','/fr/marketing/segments-create-confirm.php',true);
		OAjax.onreadystatechange = function(){
			// OAjax.readyState == 1   ==>  connexion ?tablie
			// OAjax.readyState == 2   ==>  requete recue
			// OAjax.readyState == 3   ==>  reponse en cours
			
			
				if ((OAjax.readyState == 1) || (OAjax.readyState == 2) || (OAjax.readyState == 3) ){
					//document.getElementById('panel-table').style.opacity = '0.2';
					//document.getElementById('loader_panel-table').style.display = 'block';
				}
				
				if (OAjax.readyState == 4 && OAjax.status==200){
					//document.getElementById('familly_autocomplete_loader_'+field_element_id).style.display	= 'none';

					//if(OAjax.responseText !=''){
						//document.getElementById('panel-table').style.opacity = '1';
						document.getElementById('segment_actions_ask').innerHTML	= OAjax.responseText;
						
						//Detect if this element exist and execute it's content !
						if(document.getElementById('segmet_final_results_container_js')){
							eval(document.getElementById('segmet_final_results_container_js').innerHTML);
						}
						
					//}else{
						//mmf_hide_autocomplete();
					//}
				}
			}
			
		OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
		OAjax.send('segment_used_table='+segment_used_table+'&segment_typology='+segment_typology+'&segment_name='+segment_name+'&content='+global_informations_send);
		
		return false;
		
		
		
	}else{
		//Show Errors !
		segment_form_validation_display_errors_in_modal('segment_actions_ask', 'Erreurs de validation !', global_errors_validation);
	}
	
}//End segment_create_validation

//Function th show the Form Validation Errors in Modal !
function segment_form_validation_display_errors_in_modal(id, title, errors){
	document.getElementById(id).innerHTML	= errors;
	$( "#"+id ).dialog({
		resizable: false,
		draggable: false,
		height:400,
		width: 500,
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


//Function that catch the Field ID and redirect it to the appropriate function for the validation
function validate_this_field(id_element, field_position, local_groupe_count_id){
	
	//alert(id_element+' * '+local_groupe_count_id+' *  * '+global_errors_validation);
	
	var local_etat	= '1';
	if(document.getElementById('field_element_container_'+id_element)){
//alert('In IF 1');		
		if(document.getElementById('field_element_type_'+id_element)){
//alert('In IF 2');				
			var field_type	= document.getElementById('field_element_type_'+id_element).value;
			
			//Switch case for every element !
			switch(field_type){
				
				//Special Field => Family ST
				case 'family_st':
					local_etat = validate_this_field_type_family_st(id_element, field_position);
					if(local_etat=='-1'){
						var local_field_name_error	= $("#field_element_container_"+id_element).find(".edl_field").html();
						global_errors_validation += '<div class="segment_errors_head">- Le champ <b>'+local_field_name_error+'</b> du groupe '+local_groupe_count_id+' est obligatoire</div>';
					}
				break;
				
				//Special Field => Family ND
				case 'family_nd':
					local_etat = validate_this_field_type_family_nd(id_element, field_position);
					if(local_etat=='-1'){
						var local_field_name_error	= $("#field_element_container_"+id_element).find(".edl_field").html();
						global_errors_validation += '<div class="segment_errors_head">- Le champ <b>'+local_field_name_error+'</b> du groupe '+local_groupe_count_id+' est obligatoire</div>';
					}
				break;
				
				//Special Field => Family RD
				case 'family_rd':
					local_etat = validate_this_field_type_family_rd(id_element, field_position);
					if(local_etat=='-1'){
						var local_field_name_error	= $("#field_element_container_"+id_element).find(".edl_field").html();
						global_errors_validation += '<div class="segment_errors_head">- Le champ <b>'+local_field_name_error+'</b> du groupe '+local_groupe_count_id+' est obligatoire</div>';
					}
				break;
				
				//Texte
				case 'texte':
					local_etat = validate_this_field_type_texte(id_element, field_position, local_groupe_count_id);
				break;
				
				//Number
				case 'number':
					local_etat = validate_this_field_type_number(id_element, field_position, local_groupe_count_id);
				break;
				
				//Select
				case 'select':
					local_etat = validate_this_field_type_select(id_element, field_position, local_groupe_count_id);
				break;
				
				//Date
				case 'date':
					local_etat = validate_this_field_type_date(id_element, field_position, local_groupe_count_id);
				break;
				
			}//End switch
		}else{
			//The Row is empty
			local_etat	= '-1';
			global_errors_validation += '<div class="segment_errors_head">- Ligne vide dans le groupe '+local_groupe_count_id+'</div>';
		}//End else if test if the Row is not Empty !
		
	}else{
		//The Row is empty
		local_etat	= '-1';
		global_errors_validation += '<div class="segment_errors_head">- Ligne vide dans le groupe '+local_groupe_count_id+'</div>';
	}
	
	
	//Return '1' if everyting is OK Else return '-1'
	return local_etat;
}//End function validate_this_field

/******************************************************************************************/
/************************ Start of the Second Part of the validation **********************/
/******************************************************************************************/

//Type Family 1St
function validate_this_field_type_family_st(id_element, field_position){
	var local_etat	= '1';
	//Detect if the 1St Family's Exist
	if(document.getElementById('field_element_'+id_element+'_value_1')){
		//Detect if the 1St Family's ID is valid
		if(document.getElementById('field_element_'+id_element+'_value_1').value.length!=0){
			
			//Test if the field is on the first place 
			//Else check if the "AND" or "OR" is checked
			//Get the informations
			if(field_position==1){
				local_element_output ='<operation>1</operation>';
			}else{
				if(document.getElementById('row_field_what_'+id_element+'_and').checked){
					local_element_output +='<operation>AND</operation>';
				}else{
					local_element_output +='<operation>OR</operation>';
				}//End else if the "AND" OR "OR" is CHECKED
			}//End else if the position of Field is 1
			
			//Field ID
			local_element_output +='<field_id>'+document.getElementById('field_element_'+id_element+'_id').value+'</field_id>';
			
			//Field Special
			local_element_output +='<field_special>family_st</field_special>';
			
			//Field Value
			local_element_output +='<field_value1>'+document.getElementById('field_element_'+id_element+'_value_1').value+'</field_value1>';

		}else{
			local_etat = '-1';
		}
	}else{
		local_etat = '-1';
	}//End else test if the element exist !
	return local_etat;
}//End Function validate_this_field_type_family_st

//Type Family 2Nd
function validate_this_field_type_family_nd(id_element, field_position){
	var local_etat	= '1';
	//Detect if the 2Nd Family's Exist
	if(document.getElementById('field_element_'+id_element+'_value_1')){
		//Detect if the 1St Family's ID is valid
		if(document.getElementById('field_element_'+id_element+'_value_1').value.length!=0){
			
			//Test if the field is on the first place 
			//Else check if the "AND" or "OR" is checked
			//Get the informations
			if(field_position==1){
				local_element_output ='<operation>1</operation>';
			}else{
				if(document.getElementById('row_field_what_'+id_element+'_and').checked){
					local_element_output +='<operation>AND</operation>';
				}else{
					local_element_output +='<operation>OR</operation>';
				}//End else if the "AND" OR "OR" is CHECKED
			}//End else if the position of Field is 1
			
			//Field ID
			local_element_output +='<field_id>'+document.getElementById('field_element_'+id_element+'_id').value+'</field_id>';
			
			//Field Special
			local_element_output +='<field_special>family_nd</field_special>';
			
			//Field Value
			local_element_output +='<field_value1>'+document.getElementById('field_element_'+id_element+'_value_1').value+'</field_value1>';

		}else{
			local_etat = '-1';
		}
	}else{
		local_etat = '-1';
	}//End else test if the element exist !
	return local_etat;
}//End Function validate_this_field_type_family_nd

//Type Family 3Rd
function validate_this_field_type_family_rd(id_element, field_position){
	var local_etat	= '1';
	//Detect if the 3Rd Family's Exist
	if(document.getElementById('field_element_'+id_element+'_value_1')){
		//Detect if the 1St Family's ID is valid
		if(document.getElementById('field_element_'+id_element+'_value_1').value.length!=0){
			
			//Test if the field is on the first place 
			//Else check if the "AND" or "OR" is checked
			//Get the informations
			if(field_position==1){
				local_element_output ='<operation>1</operation>';
			}else{
				if(document.getElementById('row_field_what_'+id_element+'_and').checked){
					local_element_output +='<operation>AND</operation>';
				}else{
					local_element_output +='<operation>OR</operation>';
				}//End else if the "AND" OR "OR" is CHECKED
			}//End else if the position of Field is 1
			
			//Field ID
			local_element_output +='<field_id>'+document.getElementById('field_element_'+id_element+'_id').value+'</field_id>';
			
			//Field Special
			local_element_output +='<field_special>family_rd</field_special>';
			
			//Field Value
			local_element_output +='<field_value1>'+document.getElementById('field_element_'+id_element+'_value_1').value+'</field_value1>';

		}else{
			local_etat = '-1';
		}
	}else{
		local_etat = '-1';
	}//End else test if the element exist !
	return local_etat;
}//End Function validate_this_field_type_family_rd


//Type Text
function validate_this_field_type_texte(id_element, field_position, local_groupe_count_id){
	var local_etat	= '1';
	//Detect if the Text select Exist
	if(document.getElementById('select_text_'+id_element)){
		
		if(field_position==1){
			local_element_output ='<operation>1</operation>';
		}else{
			if(document.getElementById('row_field_what_'+id_element+'_and').checked){
				local_element_output +='<operation>AND</operation>';
			}else{
				local_element_output +='<operation>OR</operation>';
			}//End else if the "AND" OR "OR" is CHECKED
		}//End else if the position of Field is 1
			
		//Field ID
		local_element_output +='<field_id>'+document.getElementById('field_element_'+id_element+'_id').value+'</field_id>';

		//Field Special
		local_element_output +='<field_special>no</field_special>';
		
		//Field Type
		local_element_output +='<field_type>text</field_type>';		
			
		var text_selection			= document.getElementById('select_text_'+id_element).options[document.getElementById('select_text_'+id_element).selectedIndex].value;
		
		//Switch to get the appropriate section/Value
		switch(text_selection){
			case 'egale':
				var local_field_name_error	= $("#field_element_container_"+id_element).find(".edl_field").html();
				var field_value1			= document.getElementById('field_element_'+id_element+'_value_1').value;
				
				//change on 20/05/2015 15:01 
				//Tristan => Vérification saisie champ texte <1 au lieu de <5
				//if(field_value1.length<5){
				if(field_value1.length<1){
					global_errors_validation += '<div class="segment_errors_head">- Le champ <b>'+local_field_name_error+'</b> du groupe '+local_groupe_count_id+' est obligatoire</div>';
					local_etat = '-1';
				}else{
					if(field_value1.match(/^\s*$/)){
						global_errors_validation += '<div class="segment_errors_head">- Le champ <b>'+local_field_name_error+'</b> du groupe '+local_groupe_count_id+' est obligatoire</div>';
						local_etat = '-1';
					}
				}
				
				//Field Selectionned
				local_element_output +='<field_selectionned>egale</field_selectionned>';	
		
				//Field Value1
				local_element_output +='<field_value1>'+field_value1+'</field_value1>';	
			break;
			
			case 'contient':
				var local_field_name_error	= $("#field_element_container_"+id_element).find(".edl_field").html();
				var field_value1			= document.getElementById('field_element_'+id_element+'_value_1').value;
				
				if(field_value1.length<3){
					global_errors_validation += '<div class="segment_errors_head">- Le champ <b>'+local_field_name_error+'</b> du groupe '+local_groupe_count_id+' est obligatoire</div>';
					local_etat = '-1';
				}else{
					if(field_value1.match(/^\s*$/)){
						global_errors_validation += '<div class="segment_errors_head">- Le champ <b>'+local_field_name_error+'</b> du groupe '+local_groupe_count_id+' est obligatoire</div>';
						local_etat = '-1';
					}
				}
				
				//Field Selectionned
				local_element_output +='<field_selectionned>contient</field_selectionned>';	
		
				//Field Value1
				local_element_output +='<field_value1>'+field_value1+'</field_value1>';
			break;
			
			case 'ne_contient_pas':
				var local_field_name_error	= $("#field_element_container_"+id_element).find(".edl_field").html();
				var field_value1			= document.getElementById('field_element_'+id_element+'_value_1').value;
				
				if(field_value1.length<5){
					global_errors_validation += '<div class="segment_errors_head">- Le champ <b>'+local_field_name_error+'</b> du groupe '+local_groupe_count_id+' est obligatoire</div>';
					local_etat = '-1';
				}else{
					if(field_value1.match(/^\s*$/)){
						global_errors_validation += '<div class="segment_errors_head">- Le champ <b>'+local_field_name_error+'</b> du groupe '+local_groupe_count_id+' est obligatoire</div>';
						local_etat = '-1';
					}
				}
				
				//Field Selectionned
				local_element_output +='<field_selectionned>ne_contient_pas</field_selectionned>';	
		
				//Field Value1
				local_element_output +='<field_value1>'+field_value1+'</field_value1>';
			break;
			
			case 'commence_par':
				var local_field_name_error	= $("#field_element_container_"+id_element).find(".edl_field").html();
				var field_value1			= document.getElementById('field_element_'+id_element+'_value_1').value;
				
				if(field_value1.length<5){
					global_errors_validation += '<div class="segment_errors_head">- Le champ <b>'+local_field_name_error+'</b> du groupe '+local_groupe_count_id+' est obligatoire</div>';
					local_etat = '-1';
				}else{
					if(field_value1.match(/^\s*$/)){
						global_errors_validation += '<div class="segment_errors_head">- Le champ <b>'+local_field_name_error+'</b> du groupe '+local_groupe_count_id+' est obligatoire</div>';
						local_etat = '-1';
					}
				}
				
				//Field Selectionned
				local_element_output +='<field_selectionned>commence_par</field_selectionned>';	
		
				//Field Value1
				local_element_output +='<field_value1>'+field_value1+'</field_value1>';
			break;
			
			case 'termine_par':
				var local_field_name_error	= $("#field_element_container_"+id_element).find(".edl_field").html();
				var field_value1			= document.getElementById('field_element_'+id_element+'_value_1').value;
				
				if(field_value1.length<5){
					global_errors_validation += '<div class="segment_errors_head">- Le champ <b>'+local_field_name_error+'</b> du groupe '+local_groupe_count_id+' est obligatoire</div>';
					local_etat = '-1';
				}else{
					if(field_value1.match(/^\s*$/)){
						global_errors_validation += '<div class="segment_errors_head">- Le champ <b>'+local_field_name_error+'</b> du groupe '+local_groupe_count_id+' est obligatoire</div>';
						local_etat = '-1';
					}
				}
				
				//Field Selectionned
				local_element_output +='<field_selectionned>termine_par</field_selectionned>';	
		
				//Field Value1
				local_element_output +='<field_value1>'+field_value1+'</field_value1>';
			break;
			
			case 'vide':
				
				//Field Selectionned
				local_element_output +='<field_selectionned>vide</field_selectionned>';	
			break;
			
			case 'non_vide':
				//Field Selectionned
				local_element_output +='<field_selectionned>non_vide</field_selectionned>';	
			break;
			
		}//End switch		
	}else{
		local_etat = '-1';
	}//End else test if the element exist !	
	return local_etat;	
}//End function validate_this_field_type_texte


//Type Number
function validate_this_field_type_number(id_element, field_position, local_groupe_count_id){
	var local_etat	= '1';
	//Detect if the Number select Exist
	if(document.getElementById('select_number_'+id_element)){
		
		if(field_position==1){
			local_element_output ='<operation>1</operation>';
		}else{
			if(document.getElementById('row_field_what_'+id_element+'_and').checked){
				local_element_output +='<operation>AND</operation>';
			}else{
				local_element_output +='<operation>OR</operation>';
			}//End else if the "AND" OR "OR" is CHECKED
		}//End else if the position of Field is 1
			
		//Field ID
		local_element_output +='<field_id>'+document.getElementById('field_element_'+id_element+'_id').value+'</field_id>';

		//Field Special
		local_element_output +='<field_special>no</field_special>';
		
		//Field Type
		local_element_output +='<field_type>number</field_type>';		
			
		var number_selection		= document.getElementById('select_number_'+id_element).options[document.getElementById('select_number_'+id_element).selectedIndex].value;
		
		switch(number_selection){
			
			case 'egale':
				var local_field_name_error	= $("#field_element_container_"+id_element).find(".edl_field").html();
				var field_value1			= document.getElementById('field_element_'+id_element+'_value_1').value;
				
				if(field_value1.length<1){
					global_errors_validation += '<div class="segment_errors_head">- Le champ <b>'+local_field_name_error+'</b> du groupe '+local_groupe_count_id+' est obligatoire</div>';
					local_etat = '-1';
				}else{
					if(field_value1.match(/^\s*$/)){
						global_errors_validation += '<div class="segment_errors_head">- Le champ <b>'+local_field_name_error+'</b> du groupe '+local_groupe_count_id+' est obligatoire</div>';
						local_etat = '-1';
					}
				}
				
				//Field Selectionned
				local_element_output +='<field_selectionned>egale</field_selectionned>';	
		
				//Field Value1
				local_element_output +='<field_value1>'+field_value1+'</field_value1>';
			break;
			
			case 'different':
				var local_field_name_error	= $("#field_element_container_"+id_element).find(".edl_field").html();
				var field_value1			= document.getElementById('field_element_'+id_element+'_value_1').value;
				
				if(field_value1.length<1){
					global_errors_validation += '<div class="segment_errors_head">- Le champ <b>'+local_field_name_error+'</b> du groupe '+local_groupe_count_id+' est obligatoire</div>';
					local_etat = '-1';
				}else{
					if(field_value1.match(/^\s*$/)){
						global_errors_validation += '<div class="segment_errors_head">- Le champ <b>'+local_field_name_error+'</b> du groupe '+local_groupe_count_id+' est obligatoire</div>';
						local_etat = '-1';
					}
				}
				
				//Field Selectionned
				local_element_output +='<field_selectionned>different</field_selectionned>';	
		
				//Field Value1
				local_element_output +='<field_value1>'+field_value1+'</field_value1>';
			break;		
			
			case 'lt':
				var local_field_name_error	= $("#field_element_container_"+id_element).find(".edl_field").html();
				var field_value1			= document.getElementById('field_element_'+id_element+'_value_1').value;
				
				if(field_value1.length<1){
					global_errors_validation += '<div class="segment_errors_head">- Le champ <b>'+local_field_name_error+'</b> du groupe '+local_groupe_count_id+' est obligatoire</div>';
					local_etat = '-1';
				}else{
					if(field_value1.match(/^\s*$/)){
						global_errors_validation += '<div class="segment_errors_head">- Le champ <b>'+local_field_name_error+'</b> du groupe '+local_groupe_count_id+' est obligatoire</div>';
						local_etat = '-1';
					}
				}
				
				//Field Selectionned
				local_element_output +='<field_selectionned>lt</field_selectionned>';	
		
				//Field Value1
				local_element_output +='<field_value1>'+field_value1+'</field_value1>';
			break;	
			
			case 'gt':
				var local_field_name_error	= $("#field_element_container_"+id_element).find(".edl_field").html();
				var field_value1			= document.getElementById('field_element_'+id_element+'_value_1').value;
				
				if(field_value1.length<1){
					global_errors_validation += '<div class="segment_errors_head">- Le champ <b>'+local_field_name_error+'</b> du groupe '+local_groupe_count_id+' est obligatoire</div>';
					local_etat = '-1';
				}else{
					if(field_value1.match(/^\s*$/)){
						global_errors_validation += '<div class="segment_errors_head">- Le champ <b>'+local_field_name_error+'</b> du groupe '+local_groupe_count_id+' est obligatoire</div>';
						local_etat = '-1';
					}
				}
				
				//Field Selectionned
				local_element_output +='<field_selectionned>gt</field_selectionned>';	
		
				//Field Value1
				local_element_output +='<field_value1>'+field_value1+'</field_value1>';
			break;	
			
			case 'lt_egal':
				var local_field_name_error	= $("#field_element_container_"+id_element).find(".edl_field").html();
				var field_value1			= document.getElementById('field_element_'+id_element+'_value_1').value;
				
				if(field_value1.length<1){
					global_errors_validation += '<div class="segment_errors_head">- Le champ <b>'+local_field_name_error+'</b> du groupe '+local_groupe_count_id+' est obligatoire</div>';
					local_etat = '-1';
				}else{
					if(field_value1.match(/^\s*$/)){
						global_errors_validation += '<div class="segment_errors_head">- Le champ <b>'+local_field_name_error+'</b> du groupe '+local_groupe_count_id+' est obligatoire</div>';
						local_etat = '-1';
					}
				}
				
				//Field Selectionned
				local_element_output +='<field_selectionned>lt_egal</field_selectionned>';	
		
				//Field Value1
				local_element_output +='<field_value1>'+field_value1+'</field_value1>';
			break;	
			
			case 'gt_egal':
				var local_field_name_error	= $("#field_element_container_"+id_element).find(".edl_field").html();
				var field_value1			= document.getElementById('field_element_'+id_element+'_value_1').value;
				
				if(field_value1.length<1){
					global_errors_validation += '<div class="segment_errors_head">- Le champ <b>'+local_field_name_error+'</b> du groupe '+local_groupe_count_id+' est obligatoire</div>';
					local_etat = '-1';
				}else{
					if(field_value1.match(/^\s*$/)){
						global_errors_validation += '<div class="segment_errors_head">- Le champ <b>'+local_field_name_error+'</b> du groupe '+local_groupe_count_id+' est obligatoire</div>';
						local_etat = '-1';
					}
				}
				
				//Field Selectionned
				local_element_output +='<field_selectionned>gt_egal</field_selectionned>';	
		
				//Field Value1
				local_element_output +='<field_value1>'+field_value1+'</field_value1>';
			break;
			
			case 'vide':
				//Field Selectionned
				local_element_output +='<field_selectionned>vide</field_selectionned>';	
			break;
			
			case 'non_vide':
				//Field Selectionned
				local_element_output +='<field_selectionned>non_vide</field_selectionned>';	
			break;
			
		}//End switch		
	}else{
		local_etat = '-1';
	}//End else test if the element exist !	
	return local_etat;	
}//End Function validate_this_field_type_number


//Type Select
function validate_this_field_type_select(id_element, field_position, local_groupe_count_id){
	var local_etat	= '1';
	
	//Detect if the Select select Exist
	if(document.getElementById('select_select_'+id_element)){
		
		if(field_position==1){
			local_element_output ='<operation>1</operation>';
		}else{
			if(document.getElementById('row_field_what_'+id_element+'_and').checked){
				local_element_output +='<operation>AND</operation>';
			}else{
				local_element_output +='<operation>OR</operation>';
			}//End else if the "AND" OR "OR" is CHECKED
		}//End else if the position of Field is 1
			
		//Field ID
		local_element_output +='<field_id>'+document.getElementById('field_element_'+id_element+'_id').value+'</field_id>';

		//Field Special
		local_element_output +='<field_special>no</field_special>';
		
		//Field Type
		local_element_output +='<field_type>select</field_type>';		
			
		var select_selection		= document.getElementById('select_select_'+id_element).options[document.getElementById('select_select_'+id_element).selectedIndex].value;
		
		//Field Value1
		local_element_output +='<field_value1>'+select_selection+'</field_value1>';
		
	}else{
		local_etat = '-1';
	}//End else test if the element exist !	
	return local_etat;	
}//End Function validate_this_field_type_select


//Type Date
function validate_this_field_type_date(id_element, field_position, local_groupe_count_id){
	var local_etat	= '1';
	
	// regular expression to match required date format => (dd/mm/yyyy)
    re = /^\d{1,2}\/\d{1,2}\/\d{4}$/;
	
	//Detect if the Select select Exist
	if(document.getElementById('select_date_'+id_element)){
		
		if(field_position==1){
			local_element_output ='<operation>1</operation>';
		}else{
			if(document.getElementById('row_field_what_'+id_element+'_and').checked){
				local_element_output +='<operation>AND</operation>';
			}else{
				local_element_output +='<operation>OR</operation>';
			}//End else if the "AND" OR "OR" is CHECKED
		}//End else if the position of Field is 1
			
		//Field ID
		local_element_output +='<field_id>'+document.getElementById('field_element_'+id_element+'_id').value+'</field_id>';

		//Field Special
		local_element_output +='<field_special>no</field_special>';
		
		//Field Type
		local_element_output +='<field_type>date</field_type>';		
			
		var date_selection		= document.getElementById('select_date_'+id_element).options[document.getElementById('select_date_'+id_element).selectedIndex].value;
		
		switch(date_selection){
			
			case 'egale':
				var local_field_name_error	= $("#field_element_container_"+id_element).find(".edl_field").html();
				var field_value1			= document.getElementById('field_element_'+id_element+'_value_1').value;
				
				//Field Selectionned
				local_element_output +='<field_selectionned>egale</field_selectionned>';
				
				if(field_value1 != '' && !field_value1.match(re)) {
					//Field Value1
					local_element_output +='<field_value1>'+field_value1+'</field_value1>';
				}else{
					global_errors_validation += '<div class="segment_errors_head">- Le champ <b>'+local_field_name_error+'</b> du groupe '+local_groupe_count_id+' est obligatoire</div>';
					local_etat = '-1';
				}
			break;
			
			case 'entre':
				var local_field_name_error	= $("#field_element_container_"+id_element).find(".edl_field").html();
				var field_value1			= document.getElementById('field_element_'+id_element+'_value_1').value;
				var field_value2			= document.getElementById('field_element_'+id_element+'_value_2').value;
				
				//Field Selectionned
				local_element_output +='<field_selectionned>entre</field_selectionned>';
				
				if(field_value1 != '' && !field_value1.match(re) && field_value2 != '' && !field_value2.match(re)) {
					//Field Value1
					local_element_output +='<field_value1>'+field_value1+'</field_value1>';
				
					//Field Value2
					local_element_output +='<field_value2>'+field_value2+'</field_value2>';
				}else{
					global_errors_validation += '<div class="segment_errors_head">- Le champ <b>'+local_field_name_error+'</b> du groupe '+local_groupe_count_id+' est obligatoire</div>';
					local_etat = '-1';
				}
			break;		
			
			case 'lt':
				var local_field_name_error	= $("#field_element_container_"+id_element).find(".edl_field").html();
				var field_value1			= document.getElementById('field_element_'+id_element+'_value_1').value;
				
				//Field Selectionned
				local_element_output +='<field_selectionned>lt</field_selectionned>';
				
				if(field_value1 != '' && !field_value1.match(re)) {
					//Field Value1
					local_element_output +='<field_value1>'+field_value1+'</field_value1>';
				}else{
					global_errors_validation += '<div class="segment_errors_head">- Le champ <b>'+local_field_name_error+'</b> du groupe '+local_groupe_count_id+' est obligatoire</div>';
					local_etat = '-1';
				}
			break;	
			
			case 'lt_egale':
				var local_field_name_error	= $("#field_element_container_"+id_element).find(".edl_field").html();
				var field_value1			= document.getElementById('field_element_'+id_element+'_value_1').value;
				
				//Field Selectionned
				local_element_output +='<field_selectionned>lt_egale</field_selectionned>';
				
				if(field_value1 != '' && !field_value1.match(re)) {
					//Field Value1
					local_element_output +='<field_value1>'+field_value1+'</field_value1>';
				}else{
					global_errors_validation += '<div class="segment_errors_head">- Le champ <b>'+local_field_name_error+'</b> du groupe '+local_groupe_count_id+' est obligatoire</div>';
					local_etat = '-1';
				}
			break;	
			
			case 'gt':
				var local_field_name_error	= $("#field_element_container_"+id_element).find(".edl_field").html();
				var field_value1			= document.getElementById('field_element_'+id_element+'_value_1').value;
				
				//Field Selectionned
				local_element_output +='<field_selectionned>gt</field_selectionned>';
				
				if(field_value1 != '' && !field_value1.match(re)) {
					//Field Value1
					local_element_output +='<field_value1>'+field_value1+'</field_value1>';
				}else{
					global_errors_validation += '<div class="segment_errors_head">- Le champ <b>'+local_field_name_error+'</b> du groupe '+local_groupe_count_id+' est obligatoire</div>';
					local_etat = '-1';
				}
			break;	
			
			case 'gt_egale':
				var local_field_name_error	= $("#field_element_container_"+id_element).find(".edl_field").html();
				var field_value1			= document.getElementById('field_element_'+id_element+'_value_1').value;
				
				//Field Selectionned
				local_element_output +='<field_selectionned>gt_egale</field_selectionned>';
				
				if(field_value1 != '' && !field_value1.match(re)) {
					//Field Value1
					local_element_output +='<field_value1>'+field_value1+'</field_value1>';
				}else{
					global_errors_validation += '<div class="segment_errors_head">- Le champ <b>'+local_field_name_error+'</b> du groupe '+local_groupe_count_id+' est obligatoire</div>';
					local_etat = '-1';
				}
			break;	
			
			case 'aujourdhui_plus':
				var local_field_name_error	= $("#field_element_container_"+id_element).find(".edl_field").html();
				var field_value1			= document.getElementById('field_element_'+id_element+'_value_1').value;
				
				if(field_value1.length<1){
					global_errors_validation += '<div class="segment_errors_head">- Le champ <b>'+local_field_name_error+'</b> du groupe '+local_groupe_count_id+' est obligatoire</div>';
					local_etat = '-1';
				}else{
					if(field_value1.match(/^\s*$/)){
						global_errors_validation += '<div class="segment_errors_head">- Le champ <b>'+local_field_name_error+'</b> du groupe '+local_groupe_count_id+' est obligatoire</div>';
						local_etat = '-1';
					}
				}
				
				//Field Selectionned
				local_element_output +='<field_selectionned>aujourdhui_plus</field_selectionned>';
				
				//Field Value1
				local_element_output +='<field_value1>'+field_value1+'</field_value1>';
			break;	
			
			case 'aujourdhui_moins':
				var local_field_name_error	= $("#field_element_container_"+id_element).find(".edl_field").html();
				var field_value1			= document.getElementById('field_element_'+id_element+'_value_1').value;
				
				if(field_value1.length<1){
					global_errors_validation += '<div class="segment_errors_head">- Le champ <b>'+local_field_name_error+'</b> du groupe '+local_groupe_count_id+' est obligatoire</div>';
					local_etat = '-1';
				}else{
					if(field_value1.match(/^\s*$/)){
						global_errors_validation += '<div class="segment_errors_head">- Le champ <b>'+local_field_name_error+'</b> du groupe '+local_groupe_count_id+' est obligatoire</div>';
						local_etat = '-1';
					}
				}
				
				
				//Field Selectionned
				local_element_output +='<field_selectionned>aujourdhui_moins</field_selectionned>';
				
				//Field Value1
				local_element_output +='<field_value1>'+field_value1+'</field_value1>';				
			break;
			
		}//End Switch
		
	}else{
		local_etat = '-1';
	}//End else test if the element exist !	
	return local_etat;
}//End Function validate_this_field_type_date

/******************************************************************************************/
/************************ End of the Second Part of the validation ************************/
/******************************************************************************************/

//Function for the redirection 
function redirect_page_after_segment_create(){
	//window.setInterval(document.location='/fr/marketing/my-segments.php', 3000);
	setTimeout(function(){ document.location='/fr/marketing/my-segments.php'; }, 3000);
}

