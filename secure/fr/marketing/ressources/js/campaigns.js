

//Validate the Fields !
//Use the same function to save the difference is 'saved' or 'programmed'
//And test on it again to show the correct message in Server Side !

//campaign_validate()
//campaign_save()
//campaign_lunch()

function campaign_get_segment_count(){
	//Clear the content !
	document.getElementById('campaign_message_count_value').innerHTML	= "";
	
	var message_id	=  document.getElementById('campaign_message_selection').options[document.getElementById('campaign_message_selection').selectedIndex].value;
	
	if(message_id!=""){
		//Preparing for Ajax call 
		
		$("#campaign_load_segment_count_loading").show('fast');
		//document.getElementById('campaign_load_segment_count_loading').style.display = 'block';
		
		var OAjax;
	
		if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
		else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

		OAjax.open('POST','/fr/marketing/campaign-get-segment-count.php',true);
		OAjax.onreadystatechange = function(){
			// OAjax.readyState == 1   ==>  connexion ?tablie
			// OAjax.readyState == 2   ==>  requete recue
			// OAjax.readyState == 3   ==>  reponse en cours
			
			
				if ((OAjax.readyState == 1) || (OAjax.readyState == 2) || (OAjax.readyState == 3) ){
					//document.getElementById('panel-table').style.opacity = '0.2';
					//document.getElementById('loader_panel-table').style.display = 'block';
				}
				
				if (OAjax.readyState == 4 && OAjax.status==200){
					$("#campaign_load_segment_count_loading").hide('fast');

					//if(OAjax.responseText !=''){

						//document.getElementById('panel-table').style.opacity = '1';
						document.getElementById('campaign_message_count_value').innerHTML+=''+OAjax.responseText+'';
						
						//Init the listner !
						//init_field_copy_click_listner();
						
						
					//}else{
						//segments_search_hide_autosuggest();
					//}
				}
			}
			
		OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
		OAjax.send('message_id='+message_id);
		
	}else{
		document.getElementById('campaign_message_count_value').innerHTML	= "&nbsp;";
	}
}

function campaign_type_change(value){
	if(value=="adhoc"){
		$("#campaign_date_container").show("slow");
	}else if(value=="trigger"){
		$("#campaign_date_container").hide("slow");
	}
	//alert(value);
}


//Function to show the modal errors !
function campaign_error_display_modal(id, title, errors){
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

//Parse a date in yyyy-mm-dd format
function parseDate(input){
	if(input){
		  var parts = input.split('-');
		var string_to_return	= parts[2]+'-'+parts[1]+'-'+parts[0];
		return string_to_return;
	}else{
		return "";
	}
  
	//new Date(year, month [, day [, hours[, minutes[, seconds[, ms]]]]])
	//return new Date(parts[0], parts[1]-1, parts[2]); // Note: months are 0-based
}

//Validate the Form !
function campaign_validate_the_form(){
	
	var etat 	= 1;
	var	errors	= "";
	
	var campaign_title				= document.getElementById('campaign_title').value;
	
	var campaign_message_selection	= document.getElementById('campaign_message_selection').options[document.getElementById('campaign_message_selection').selectedIndex].value;

	
	var campaign_type				= document.getElementById('campaign_type').value;
	//var campaign_date				= document.getElementById('campaign_time_value').value;
	var campaign_date				= "";
	var campaign_hour				= document.getElementById('campaign_time_value').value;
	var campaign_minute				= document.getElementById('campaign_minute_value').value;
	
	var campaign_actived			= document.getElementById('campaign_actived').value;
	
	
	// regular expression to match required date format
	re_date = /^(\d{1,2})\-(\d{1,2})\-(\d{4})$/;
	var re_hour	= /^\d{1,2}$/;
	
	if(campaign_title.length<5){
		errors	= "- Le <b>titre du message</b> est obligatoire !<br />";
		etat=0;
	}
	
	if(campaign_message_selection==""){
		errors	+= "- Le <b>message associ&eacute;</b> est obligatoire !<br />";
		etat=0;
	}
	
	if(campaign_type=="adhoc"){
		var campaign_date_temp 	= document.getElementById('campaign_date_value').value;
		var campaign_date_temp2	= parseDate(campaign_date_temp);
		//alert(campaign_date_temp+' ** '+campaign_date_temp2+' ## '+campaign_date_temp2.match(re_date));
		
		if(campaign_date_temp=="" && !campaign_date_temp2.match(re_date)) {
			errors	+= "- La <b>date</b> est obligatoire (Ex: JJ-MM-AAAA) !<br />";
			etat=0;
		}
	}
	
	if((campaign_hour<-1 || campaign_hour>23 ) || !campaign_hour.match(re_hour)) {
			errors	+= "- L'<b>Heure</b> est obligatoire (Ex: 09) !<br />";
			etat=0;
	}
	
	
	
	
	//alert('Type: '+campaign_type+' ** Actived: '+campaign_actived);
	
	
	if(etat!=1){
		var title 	= 'Erreurs de validation !';
		campaign_error_display_modal('campaign_errors', title, errors);
	}
	
	//Return 1 or 0
	return etat;	
}


//Save the Campaign !
function campaign_save(){
	var etat_validate 	= campaign_validate_the_form();

	if(etat_validate==1){
		var etat = 1;
		var campaign_id					= document.getElementById('campaigns_hidden_id').value;
		var campaign_title				= document.getElementById('campaign_title').value;
		var campaign_message_selection	= document.getElementById('campaign_message_selection').options[document.getElementById('campaign_message_selection').selectedIndex].value;
		var campaign_type				= document.getElementById('campaign_type').value;
		
		if(campaign_type=="adhoc"){
			var campaign_date_temp 	= document.getElementById('campaign_date_value').value;
			var campaign_date	= parseDate(campaign_date_temp);
		}else{
			var campaign_date				= "";
		}
		
		var campaign_hour				= document.getElementById('campaign_time_value').value;
		var campaign_minute				= document.getElementById('campaign_minute_value').value;
		var campaign_actived			= document.getElementById('campaign_actived').value;
		var stats           			= document.getElementById('stats').value;
		
		
		if(campaign_title!='' && campaign_message_selection!='' && campaign_type!='' && campaign_hour!='' && campaign_actived!='' && campaign_minute_value!='' ){
		
			//Hide the Popup !
			$("#campaigns_etat_operation").hide("slow");
			
			var OAjax;
	
				if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
				else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

				OAjax.open('POST','/fr/marketing/campaign-save.php',true);
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
							if(stats =='stats'){
								setTimeout(function(){ document.location='/fr/marketing/rapport_campagne.php?id_campagne='+campaign_id; }, 1500);
							}else{
								$("#campaigns_etat_operation").show("slow");
								document.getElementById('campaigns_etat_operation').innerHTML=''+OAjax.responseText+'';
								
								if(document.getElementById('campaign_final_results_container_js')){
									eval(document.getElementById('campaign_final_results_container_js').innerHTML);
								}

								setTimeout(function(){ document.location='/fr/marketing/my-campaigns.php'; }, 1500);
							}	
							//}else{
								//
							//}
						}
					}
					
				OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
				OAjax.send('campaign_id='+campaign_id+'&campaign_title='+campaign_title+'&campaign_message_selection='+campaign_message_selection+'&campaign_type='+campaign_type+'&campaign_date='+campaign_date+'&campaign_hour='+campaign_hour+'&campaign_minute='+campaign_minute+'&campaign_actived='+campaign_actived);
				
				//return false;
		
		}else{
			etat =0;
			
			var title 	= 'Erreurs de validation !';
			campaign_error_display_modal('campaign_errors', title, 'Vous avez des erreurs dans le formualire !');
		}
		
		
	}//End if Etat_validate external function
}


//Load the campaigns !
function campaigns_load_list_show(){
	
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

	OAjax.open('POST','/fr/marketing/campaigns-list-load.php',true);
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
					setTimeout(campaigns_table_visible, 200);	

				//}else{
					//mmf_hide_autocomplete();
				//}
			}
		}
		
	OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	OAjax.send('f_ps='+f_ps+'&f_pp='+f_pp+'&f_search='+f_search+'&table_order='+table_order);
	
	return false;
}

function campaigns_table_visible(){
	document.getElementById('panel-table').style.opacity = '1';
	//alert('finish');
}

function campaigns_get_more_now(){
	var personnalised_pp	=	document.getElementById('f_select_p').options[document.getElementById('f_select_p').selectedIndex].value;
	document.getElementById('fpp').value	= personnalised_pp;

	//To avoid the empty page when we change the number per page 
	//(Ex: We have 17elements and we show 5 per page 
	//and are in the page number 3, Once we will change the count per page
	//We gonna have a empty table. For that we force it to go to the first page !
	document.getElementById('fps').value = 1;
	
	campaigns_load_list_show();
}

function campaigns_load_other_page(number){
	document.getElementById('fps').value = number;
	campaigns_load_list_show();
}

//Make the order on the message list (Table)
function campaigns_list_order_by(critere){
	document.getElementById('table_order').value	= critere;
	
	//Go back to the first page (Flag)
	document.getElementById('fps').value = 1;
	
	//Refrech
	campaigns_load_list_show();
}

//Load the Auto-complete !
function campaigns_name_div_autocomplete(){
	document.getElementById('campaigns_search_autosuggest_loading').style.display = 'inline';
	
				
	var f_search			= document.getElementById('f_search').value;

	var OAjax;
	
	if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
	else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

	OAjax.open('POST','/fr/marketing/campaigns-name-autocomplete.php',true);
	OAjax.onreadystatechange = function(){
		// OAjax.readyState == 1   ==>  connexion ?tablie
		// OAjax.readyState == 2   ==>  requete recue
		// OAjax.readyState == 3   ==>  reponse en cours
		
		
			if ((OAjax.readyState == 1) || (OAjax.readyState == 2) || (OAjax.readyState == 3) ){
				//document.getElementById('panel-table').style.opacity = '0.2';
				//document.getElementById('loader_panel-table').style.display = 'block';
			}
			
			if (OAjax.readyState == 4 && OAjax.status==200){
				document.getElementById('campaigns_search_autosuggest').style.display = 'block';
				document.getElementById('campaigns_search_autosuggest_content').innerHTML = '';
				document.getElementById('campaigns_search_autosuggest_loading').style.display = 'none';

				if(OAjax.responseText !=''){

					//document.getElementById('panel-table').style.opacity = '1';
					document.getElementById('campaigns_search_autosuggest_content').innerHTML+=''+OAjax.responseText+'';


				}else{
					campaigns_search_hide_autosuggest();
				}
			}
		}
		
	OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	OAjax.send('f_search='+f_search);
	
	return false;	
}


//Hide the Auto-complet e!
function campaigns_search_hide_autosuggest(){
	document.getElementById('campaigns_search_autosuggest').style.display='none';
}

//Function to fill after the click on the autocomplete
function campaigns_fill_this_one(string){
	document.getElementById('f_search').value=string;
	campaigns_search_hide_autosuggest();
	campaigns_load_list_show();
}


/*************************************************************************************************************/
/************************************************ Delete Mode ************************************************/

function campaign_ask_delete_display_modal(id_campaign){
	//Ask the confirmation
	var title 	= 'Confirmation de suppression !';
	var errors	= '&Ecirc;tes vous s&ucirc;r de vouloir supprimer cette campagne ?';
	campaign_ask_delete_display_modal_2('campaign_actions_ask', title, errors, id_campaign);
}

//Function to show the confirm for the action Ask !
function campaign_ask_delete_display_modal_2(div_id, title, errors, id_campaign){
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
				campaign_delete_after_confirmation(id_campaign);
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


function campaign_delete_after_confirmation(id_campaign){
	document.getElementById("campaign_actions_ask").innerHTML	= '<br /><br /><br /><center>'+
						'<img src="ressources/images/loading.gif" alt="Suppression en cours.." title="Suppression en cours.." />'+
					'</center>';
				
	//Start the process to delete the element !
	var OAjax;
	
	if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
	else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

	OAjax.open('POST','/fr/marketing/delete-campaign.php',true);
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
					document.getElementById('campaign_actions_ask').innerHTML=''+OAjax.responseText+'';
					if(document.getElementById('campaign_final_results_container_js')){
						eval(document.getElementById('campaign_final_results_container_js').innerHTML);
					}

				//}else{
					//mmf_hide_autocomplete();
				//}
			}
		}
		
	OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	OAjax.send('campaign_id='+id_campaign);
	
	return false;	
}