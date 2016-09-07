function message_segment_show_elements(){
	
	//Show the control buttons 
	//document.getElementById('message_send_part').style.display	= "block";
	//document.getElementById('message_preview_container_btn').style.display	= "block";
	//document.getElementById('message_middle_part').style.display	= "block";
	
	$("#message_send_part").show("slow");
	$("#message_preview_container_btn").show("slow");
	$("#message_middle_part").show("slow");
	
}

function message_segment_hide_elements(){
	
	//Show the control buttons 
	//document.getElementById('message_send_part').style.display	= "block";
	//document.getElementById('message_preview_container_btn').style.display	= "block";
	//document.getElementById('message_middle_part').style.display	= "block";
	
	$("#message_send_part").hide("slow");
	$("#message_preview_container_btn").hide("slow");
	$("#message_middle_part").hide("slow");
		
}

function message_select_segment(){
	//Clear All the elements (Object Field, Fields Block and Text Editor)
	//Get the ID of the new segment
	//Test if the ID is not null and Load the Fields !
	
	document.getElementById('message_object').value				= "";
	document.getElementById('message_top_part_right').innerHTML	= "<span style=\"opacity:0.5;\"><br />&nbsp;&nbsp;Veuillez selectionner un segment</span>";
	
	//console.log(CKEDITOR.instances);
	//alert(CKEDITOR.instances.message_ckeditor.getData());
	CKEDITOR.instances.message_ckeditor.setData('');
	
	var segment_id	= document.getElementById('message_segment_selection').options[document.getElementById('message_segment_selection').selectedIndex].value;
	
	if(segment_id!=''){
		
		//Show the Editor and the other actions !
		message_segment_show_elements();
		
		
		document.getElementById('message_load_fields_loading').style.display = 'block';
		
		//Get the segment fields !
		
		var OAjax;
	
		if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
		else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

		OAjax.open('POST','/fr/marketing/messages-get-segment-fields.php',true);
		OAjax.onreadystatechange = function(){
			// OAjax.readyState == 1   ==>  connexion ?tablie
			// OAjax.readyState == 2   ==>  requete recue
			// OAjax.readyState == 3   ==>  reponse en cours
			
			
				if ((OAjax.readyState == 1) || (OAjax.readyState == 2) || (OAjax.readyState == 3) ){
					//document.getElementById('panel-table').style.opacity = '0.2';
					//document.getElementById('loader_panel-table').style.display = 'block';
				}
				
				if (OAjax.readyState == 4 && OAjax.status==200){
					document.getElementById('message_top_part_right').innerHTML = '';
					document.getElementById('message_load_fields_loading').style.display = 'none';

					//if(OAjax.responseText !=''){

						//document.getElementById('panel-table').style.opacity = '1';
						document.getElementById('message_top_part_right').innerHTML+=''+OAjax.responseText+'';
					
						document.getElementById('message_top_part_right').style.display	= "none";
						$("#message_top_part_right").show("slow");
						
						//Init the listner !
						//init_field_copy_click_listner();
						
						
					//}else{
						//segments_search_hide_autosuggest();
					//}
				}
			}
			
		OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
		OAjax.send('segment_id='+segment_id);
		
		return false;
		
	}else{
		message_segment_hide_elements();
	}//End test if the id segment is not empty
	
}

//http://www.krizna.com/jquery/copy-to-clipboard-multiple-links-using-zclip/

function init_field_copy_click_listner(){
		
	/*$('#message_copy_field_btn_id_0').zclip({
		path:'/fr/marketing/ressources/js/message/ZeroClipboard.swf',
		copy: function(){
			//alert($("#"+id+"").attr('text'));
			//return $(this).text();
			return "{{"+$('#message_copy_field_btn_id_0').attr('text')+"}}";
		},
		afterCopy: function() {}
	});*/
		
	/*$('.message_copy_field_btn').each(function(){
		$(this).zclip({
			path:"/fr/marketing/ressources/js/message/ZeroClipboard.swf",
			copy: function(){
					return $(this).attr('text');
				},
			afterCopy: function(){
					alert('After Copy !');
				}
        });
	});*/
	
	/*
	var clip = new ZeroClipboard($("#message_copy_field_btn_id_0"), {
      moviePath: "/fr/marketing/ressources/js/message/ZeroClipboard.swf"
    }); 
	
	clip.on('click', function(client) {
		alert('!!');
		var text = $('#message_copy_field_btn_id_0').attr('text');
		client.setText(text);
	});

	alert('Listner OK ! '+$('#message_copy_field_btn_id_0').attr('text'));
	*/
}

function message_copy_that_field(id, text){
	
	document.getElementById('message_hidden_copy').style.display	= "block";
	document.getElementById('message_tester_container').style.marginTop	= "31px";
	
	var id_of_the_generated_element	= $("#message_top_part div:last").first().attr('id');
		
	//Remove the Flash element (previous) !
	/*if(id_of_the_generated_element!='message_top_part_right'){
		$("#message_top_part div:last").first().remove();
	}*/
	
	$('#message_hidden_copy').zclip({
		path:'/fr/marketing/ressources/js/message/ZeroClipboard.swf',
		copy: function(){
			//alert($("#"+id+"").attr('text'));
			//return $(this).text();
			return text;
		},
		afterCopy: function() {
			
			//console.log('Copied Element ! => '+text);
			document.getElementById('message_hidden_copy').style.display	= "none"; 
			
			//Remove the Flash element (previous) !
			var id_of_the_generated_element	= $("#message_top_part div:last").first().attr('id');
			/*if(id_of_the_generated_element!='message_top_part_right'){
				$("#message_top_part div:last").first().remove();
			}*/
			
			document.getElementById('message_tester_container').style.marginTop	= "5px";
			
			
			$("#message_top_part div.zclip").remove();
			
		}//End After Copy !
	});
	

	
	//var id_to_click	= $("#message_top_part div:last").first().attr('id');
	//console.log(id_to_click);
	
	
	//document.getElementById('message_hidden_copy').style.display	= "block";
	//document.getElementById('message_tester_container').style.marginTop	= "31px";
	
	//Change the Color of the Clicked Element !
	$("#"+id).animate({backgroundColor: 'rgb(160, 250, 171)'},'fast');
	$("#"+id).animate({backgroundColor: '#DEDFE2'},'fast');
			

	
}


//Function to show the modal errors !
function message_error_display_modal(id, title, errors){
	document.getElementById(id).innerHTML	= errors;
	$( "#"+id ).dialog({
		resizable: false,
		draggable: false,
		height:300,
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

function validateEmail(email) {
    var re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}

//Use a unique function to validate the form and get the return to proceed 
//to the other steps
//For the message form validation
function message_validate_the_form(){
	var etat 	= 1;
	var	errors	= "";
	
	//Get elements !
	var message_title				= document.getElementById('message_title').value;
	var sender_email				= document.getElementById('sender_email').value;
	var sender_name					= document.getElementById('sender_name').value;
	var reply_email					= document.getElementById('reply_email').value;
	var message_segment_selection	= document.getElementById('message_segment_selection').options[document.getElementById('message_segment_selection').selectedIndex].value;
	var message_object				= document.getElementById('message_object').value;
	
	var message_content				= CKEDITOR.instances.message_ckeditor.getData();
	
	if(message_title.length<5){
		errors	= "- Le <b>titre du message</b> est obligatoire !<br />";
		etat=0;
	}
	
	if(!validateEmail(sender_email)){
		errors	+= "- Le <b>Mail exp&eacute;diteur</b> est obligatoire !<br />";
		etat=0;	
	}
	
	if(sender_name.length<5){
		errors	+= "- Le <b>Nom exp&eacute;diteur</b> est obligatoire !<br />";
		etat=0;
	}
	
	if(!validateEmail(reply_email)){
		errors	+= "- Le <b>Mail r&eacute;ponse</b> est obligatoire !<br />";
		etat=0;	
	}
	
	if(message_segment_selection==''){
		errors	+= "- Le <b>Segment</b> est obligatoire !<br />";
		etat=0;	
	}
	
	
	if(message_object.length<5){
		errors	+= "- L\'<b>Objet</b> est obligatoire !<br />";
		etat=0;
	}
	
	if(message_content.length<50){
		errors	+= "- Le <b>Contenu du message</b> est obligatoire !<br />";
		etat=0;
	}
	
	if(etat!=1){
		var title 	= 'Erreurs de validation !';
		message_error_display_modal('message_errors', title, errors);
	}
	
	//Return 1 or 0
	return etat;	
}


//Javascript recursive replace
function recursive_replace(to_search, to_replace, string){
	var re = new RegExp(to_search, 'g');
	string = string.replace(re, to_replace);
	return string;
}

/******************************/
//Input 
//	Action to make after the save (test, preview or save)
function message_save_db(message_action){
	//Validate the form
	//Save the form and test if the caller has demand the redirection of page or Not !
	
	var etat_validate 	= message_validate_the_form();
	
	if(etat_validate==1){
		//Save the form
		
		var etat = 1;
		var message_id					= document.getElementById('message_hidden_id').value;
		var message_title				= document.getElementById('message_title').value;
		var sender_email				= document.getElementById('sender_email').value;
		var sender_name					= document.getElementById('sender_name').value;
		var reply_email					= document.getElementById('reply_email').value;
		var message_segment_selection	= document.getElementById('message_segment_selection').options[document.getElementById('message_segment_selection').selectedIndex].value;
		var message_object				= document.getElementById('message_object').value;
		var message_content				= CKEDITOR.instances.message_ckeditor.getData();

		//Double verification
		if(message_title!='' && sender_email!='' && sender_name!='' && reply_email!='' && message_segment_selection!='' && message_object!='' && message_content!=''){
			
			message_object	= recursive_replace('&', '#and#', message_object);
			message_content = recursive_replace('&', '#and#', message_content);
			
			//Test if the save button was initied => Redirect the page to the listing !
			
				//Hide the Popup !
				$("#message_etat_operation").hide("slow");
				
				var OAjax;
	
				if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
				else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

				OAjax.open('POST','/fr/marketing/messages-save.php',true);
				OAjax.onreadystatechange = function(){
					// OAjax.readyState == 1   ==>  connexion ?tablie
					// OAjax.readyState == 2   ==>  requete recue
					// OAjax.readyState == 3   ==>  reponse en cours
					
					
						if ((OAjax.readyState == 1) || (OAjax.readyState == 2) || (OAjax.readyState == 3) ){
							//document.getElementById('panel-table').style.opacity = '0.2';
							//document.getElementById('loader_panel-table').style.display = 'block';
						}
						
						if (OAjax.readyState == 4 && OAjax.status==200){

							//if(OAjax.responseText !=''){

								$("#message_etat_operation").show("slow");
								document.getElementById('message_etat_operation').innerHTML=''+OAjax.responseText+'';
								
								if(document.getElementById('message_final_results_container_js')){
									eval(document.getElementById('message_final_results_container_js').innerHTML);
								}
					
								//After the server return
								//Test on action
								if(message_action=='test'){
									
									document.getElementById('message_test_email_hidden_id').value	= document.getElementById('message_emails_test_select').options[document.getElementById('message_emails_test_select').selectedIndex].value;
									
									document.getElementById('message_form_test').submit();
									
								}else if(message_action=='preview'){
									document.getElementById('message_form_preview').submit();
								}else if(message_action=='save'){
									//alert('Save Ok => Redirect !');
									//setTimeout(function(){ document.location='/fr/marketing/my-messages.php'; }, 2000);
									setTimeout(function(){ document.location='/fr/marketing/my-messages.php'; }, 1500);
								}
								
								
							//}else{
								//
							//}
						}
					}
					
				OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
				OAjax.send('message_id='+message_id+'&message_title='+message_title+'&sender_email='+sender_email+'&sender_name='+sender_name+'&reply_email='+reply_email+'&id_segment='+message_segment_selection+'&message_object='+message_object+'&message_content='+message_content);
				
				//return false;
				
			
		}else{
			etat =0;
			
			var title 	= 'Erreurs de validation !';
			message_error_display_modal('message_errors', title, 'Vous avez des erreurs dans le formualire !');
		}
		
		return etat;
	}//Test if the form is valid
	
}
/*
//For the message save (on Edit)
function message_edit_save(redirect_or_not){
	//Validate the form
	//Save the form and test if the caller has demand the redirection of page or Not !
	
	var etat_validate 	= message_validate_the_form();
	
	if(etat_validate==1){
		//Save the form
		
		var etat = 1;
	

		
		//Test if the save button was initied => Redirect the page to the listing !
		if(redirect_or_not=='yes'){
			//Show the Popup !
			
		}else{
			//Do not do anything
			
		}//End test if the user want a redirection
		
		return etat;
	}//Test if the form is valid
}
*/
/******************************/


//For the email test send
function message_tester(){
	//Get the selected email and test if it's OK
	//Validate the form and Save IT WITHOUT redirect the Page
	
	var message_id	= document.getElementById('message_hidden_id').value;
	var etat_validate 	= message_validate_the_form();
	
	if(etat_validate==1){
		
		//if(message_type=='add'){
			var etat_save 	= message_save_db('test');
		//}else if(message_type=='edit'){
			//var etat_save 	= message_edit_save('no');
		//}
		
		//Call for the Test (Send a unique email !)
		/*if(etat_save==1){
			//Open a new window with the correct link !
			alert('Message Tester ! '+document.getElementById('message_preview_hidden_id').value);
			document.getElementById('message_form_test').submit();
		}*/
		
	}//End if test on validate form
}


//For the preview
function message_preview(){
	
	var message_id		= document.getElementById('message_hidden_id').value;
	var etat_validate 	= message_validate_the_form();
	
	if(etat_validate==1){
		
		//if(message_id==''){
			var etat_save 	= message_save_db('preview');
		//}else if(message_type=='edit'){
			//var etat_save 	= message_edit_save('no');
		//}
		
		//Call for the Test (Send a unique email !)
		/*if(etat_save==1){
			//Open a new window with the correct link !
			alert('Preview ! '+document.getElementById('message_preview_hidden_id').value);
			document.getElementById('message_form_preview').submit();
		}*/
		
	}//End if test on validate form
}

//For the message save
function message_save(){
	var message_id		= document.getElementById('message_hidden_id').value;
	
	//if(message_id==''){
		var etat_save 	= message_save_db('save');
		
		//if(etat_save==1){
			//Open a new window with the correct link !
			//alert('Save Ok => Redirect !');
			//setTimeout(function(){ document.location='/fr/marketing/my-messages.php'; }, 2000);
		//}
		
	//}else if(message_type=='edit'){
		//var etat_save 	= message_edit_save('yes');
	//}
}

/*****************************************************************************/
/****************************** Listing Messages *****************************/
/*****************************************************************************/

function messages_load_list_show(){
	document.getElementById('panel-table').style.opacity = '0.2';
	document.getElementById('loader_panel-table').style.display = 'block';
				
	//Page start
	var f_ps				= document.getElementById('fps').value;
	//Page pagination
	var f_pp 				= document.getElementById('fpp').value;

	var f_search			= document.getElementById('f_search').value;
	//For the table order !
	var table_order			= document.getElementById('table_order').value;
	
	
	document.getElementById('panel-table').innerHTML = '&nbsp;';

	
	var OAjax;
	
	if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
	else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

	OAjax.open('POST','/fr/marketing/messages-list-load.php',true);
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
					setTimeout(messages_table_visible, 200);	

				//}else{
					//mmf_hide_autocomplete();
				//}
			}
		}
		
	OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	OAjax.send('f_ps='+f_ps+'&f_pp='+f_pp+'&f_search='+f_search+'&table_order='+table_order);
	
	return false;
}

function messages_table_visible(){
	document.getElementById('panel-table').style.opacity = '1';
	//alert('finish');
}

function messages_get_more_now(){
	var personnalised_pp	=	document.getElementById('f_select_p').options[document.getElementById('f_select_p').selectedIndex].value;
	document.getElementById('fpp').value	= personnalised_pp;

	//To avoid the empty page when we change the number per page 
	//(Ex: We have 17elements and we show 5 per page 
	//and are in the page number 3, Once we will change the count per page
	//We gonna have a empty table. For that we force it to go to the first page !
	document.getElementById('fps').value = 1;
	
	messages_load_list_show();
}

function messages_load_other_page(number){
	document.getElementById('fps').value = number;
	messages_load_list_show();
}

//Make the order on the message list (Table)
function messages_list_order_by(critere){
	document.getElementById('table_order').value	= critere;
	
	//Go back to the first page (Flag)
	document.getElementById('fps').value = 1;
	
	//Refrech
	messages_load_list_show();
}

//Autocomplete of the message name in my-message listing
function messages_name_div_autocomplete(){
	
	document.getElementById('messages_search_autosuggest_loading').style.display = 'inline';
	
				
	var f_search			= document.getElementById('f_search').value;

	var OAjax;
	
	if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
	else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

	OAjax.open('POST','/fr/marketing/messages-name-autocomplete.php',true);
	OAjax.onreadystatechange = function(){
		// OAjax.readyState == 1   ==>  connexion ?tablie
		// OAjax.readyState == 2   ==>  requete recue
		// OAjax.readyState == 3   ==>  reponse en cours
		
		
			if ((OAjax.readyState == 1) || (OAjax.readyState == 2) || (OAjax.readyState == 3) ){
				//document.getElementById('panel-table').style.opacity = '0.2';
				//document.getElementById('loader_panel-table').style.display = 'block';
			}
			
			if (OAjax.readyState == 4 && OAjax.status==200){
				document.getElementById('messages_search_autosuggest').style.display = 'block';
				document.getElementById('messages_search_autosuggest_content').innerHTML = '';
				document.getElementById('messages_search_autosuggest_loading').style.display = 'none';

				if(OAjax.responseText !=''){

					//document.getElementById('panel-table').style.opacity = '1';
					document.getElementById('messages_search_autosuggest_content').innerHTML+=''+OAjax.responseText+'';


				}else{
					messages_search_hide_autosuggest();
				}
			}
		}
		
	OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	OAjax.send('f_search='+f_search);
	
	return false;

}

//Function to hide the autocomplete 
function messages_search_hide_autosuggest(){
	document.getElementById('messages_search_autosuggest').style.display='none';
}

//Function to fill after the click on the autocomplete
function messages_fill_this_one(string){
	document.getElementById('f_search').value=string;
	messages_search_hide_autosuggest();
	messages_load_list_show();
}


//Preview the message from the listing
function message_preview_from_listing(id){
	
	//Init the form
	document.getElementById('messages_external_formid').action	= "/fr/marketing/messages-preview.php";
	document.getElementById('message_preview_hidden_id').value	= id;
	
	//Send the form
	document.getElementById('messages_external_formid').submit();
}

/*************************************************************************************************************/
/************************************************ Edit Mode **************************************************/

function message_select_segment_edit_page(){
	//Clear All the elements (Object Field, Fields Block and Text Editor)
	//Get the ID of the new segment
	//Test if the ID is not null and Load the Fields !
	
	//document.getElementById('message_object').value				= "";
	document.getElementById('message_top_part_right').innerHTML	= "<span style=\"opacity:0.5;\"><br />&nbsp;&nbsp;Veuillez selectionner un segment</span>";
	
	//console.log(CKEDITOR.instances);
	//alert(CKEDITOR.instances.message_ckeditor.getData());
	//CKEDITOR.instances.message_ckeditor.setData('');
	
	var segment_id	= document.getElementById('message_segment_selection').options[document.getElementById('message_segment_selection').selectedIndex].value;
	
	if(segment_id!=''){
		
		//Show the Editor and the other actions !
		message_segment_show_elements();
		
		
		document.getElementById('message_load_fields_loading').style.display = 'block';
		
		//Get the segment fields !
		
		var OAjax;
	
		if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
		else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

		OAjax.open('POST','/fr/marketing/messages-get-segment-fields.php',true);
		OAjax.onreadystatechange = function(){
			// OAjax.readyState == 1   ==>  connexion ?tablie
			// OAjax.readyState == 2   ==>  requete recue
			// OAjax.readyState == 3   ==>  reponse en cours
			
			
				if ((OAjax.readyState == 1) || (OAjax.readyState == 2) || (OAjax.readyState == 3) ){
					//document.getElementById('panel-table').style.opacity = '0.2';
					//document.getElementById('loader_panel-table').style.display = 'block';
				}
				
				if (OAjax.readyState == 4 && OAjax.status==200){
					document.getElementById('message_top_part_right').innerHTML = '';
					document.getElementById('message_load_fields_loading').style.display = 'none';

					//if(OAjax.responseText !=''){

						//document.getElementById('panel-table').style.opacity = '1';
						document.getElementById('message_top_part_right').innerHTML+=''+OAjax.responseText+'';
					
						document.getElementById('message_top_part_right').style.display	= "none";
						$("#message_top_part_right").show("slow");
						
						//Init the listner !
						//init_field_copy_click_listner();
						
						
					//}else{
						//segments_search_hide_autosuggest();
					//}
				}
			}
			
		OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
		OAjax.send('segment_id='+segment_id);
		
		return false;
		
	}else{
		message_segment_hide_elements();
	}//End test if the id segment is not empty
	
} 


/*************************************************************************************************************/
/************************************************ Delete Mode ************************************************/

function message_ask_delete_display_modal(id_message){
	//Ask the confirmation
	var title 	= 'Confirmation de suppression !';
	var errors	= '&Ecirc;tes vous s&ucirc;r de vouloir supprimer ce message ?';
	message_ask_delete_display_modal_2('message_actions_ask', title, errors, id_message);
}

//Function to show the confirm for the action Ask !
function message_ask_delete_display_modal_2(div_id, title, errors, id_message){
	document.getElementById(div_id).innerHTML	= errors;
	$( "#"+div_id ).dialog({
		resizable: false,
		draggable: false,
		height:220,
		width: 500,
		modal: true,
		title: title,
		buttons: {
			"Oui": function(){
				message_delete_after_confirmation(id_message);
				setTimeout(function(){ 
					$( "#"+div_id ).dialog( "close" ); 
				}, 1450);
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


//Confirm the delete !
function message_delete_after_confirmation(id_message){
	document.getElementById("message_actions_ask").innerHTML	= '<br /><br /><br /><center>'+
						'<img src="ressources/images/loading.gif" alt="Suppression en cours.." title="Suppression en cours.." />'+
					'</center>';
				
	//Start the process to delete the element !
	var OAjax;
	
	if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
	else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

	OAjax.open('POST','/fr/marketing/delete-message.php',true);
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
					document.getElementById('message_actions_ask').innerHTML=''+OAjax.responseText+'';
					if(document.getElementById('message_final_results_container_js')){
						eval(document.getElementById('message_final_results_container_js').innerHTML);
					}

				//}else{
					//mmf_hide_autocomplete();
				//}
			}
		}
		
	OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	OAjax.send('message_id='+id_message);
	
	return false;
}