function blacklist_address(){
	//Show the Popup
	var title 	= 'Désinscrire des adresses !';
	var errors	= '<br /><br /><br /><center>'+
						'<img src="ressources/images/loading.gif" alt="Op&eacute;ration en cours.." title="Op&eacute;ration en cours.." />'+
					'</center>';
	blacklist_show_processing_display_modal('base_emails_actions_ask', title, errors);
	execute_the_blacklisting_process();
}

function blacklist_show_processing_display_modal(div_id, title, errors){
	document.getElementById(div_id).innerHTML	= errors;
	$( "#"+div_id ).dialog({
		resizable: false,
		draggable: false,
		height:330,
		width: 500,
		modal: true,
		title: title,
		buttons: {
			/*"Oui": function(){
				
			},*/
			"Fermer": function(){
				$( this ).dialog( "close" );
				base_emails_load_list_show();
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

//Execute the blacklisting
function execute_the_blacklisting_process(){
	
	var etat 	= 1;
	//Check if we have adresses
	var emails	= document.getElementById('f_blacklist_area').value;
	var motif	=  document.getElementById('f_blacklist_select').options[document.getElementById('f_blacklist_select').selectedIndex].value;
	
	if(emails.length<10){
		document.getElementById('base_emails_actions_ask').innerHTML	= 'Vous devez renseigner au moins une adresse !';
		etat	= 0;
	}
	
	if(etat==1){
		var OAjax;
	
		if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
		else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

		OAjax.open('POST','/fr/marketing/base-email-disable-group.php',true);
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

						//document.getElementById('panel-table').style.opacity = '1';
						document.getElementById('base_emails_actions_ask').innerHTML=''+OAjax.responseText+'';
						if(document.getElementById('base_email_final_results_container_js')){
							eval(document.getElementById('base_email_final_results_container_js').innerHTML);
						}

					//}else{
						//mmf_hide_autocomplete();
					//}
				}
			}
			
		OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
		OAjax.send('motif='+motif+'&emails='+emails);
		
		return false;	
		
	}
}

//Search
function base_emails_load_list_show(){
	
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

	OAjax.open('POST','/fr/marketing/base-email-list-load.php',true);
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
					setTimeout(base_emails_table_visible, 200);	

				//}else{
					//mmf_hide_autocomplete();
				//}
			}
		}
		
	OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	OAjax.send('f_ps='+f_ps+'&f_pp='+f_pp+'&f_search='+f_search+'&table_order='+table_order);
	
	return false;
}

function base_emails_table_visible(){
	document.getElementById('panel-table').style.opacity = '1';
}

function base_emails_get_more_now(){
	var personnalised_pp	=	document.getElementById('f_select_p').options[document.getElementById('f_select_p').selectedIndex].value;
	document.getElementById('fpp').value	= personnalised_pp;

	//To avoid the empty page when we change the number per page 
	//(Ex: We have 17elements and we show 5 per page 
	//and are in the page number 3, Once we will change the count per page
	//We gonna have a empty table. For that we force it to go to the first page !
	document.getElementById('fps').value = 1;
	
	base_emails_load_list_show();
}

function base_emails_load_other_page(number){
	document.getElementById('fps').value = number;
	base_emails_load_list_show();
}

//Make the order on the message list (Table)
function base_emails_list_order_by(critere){
	document.getElementById('table_order').value	= critere;
	
	//Go back to the first page (Flag)
	document.getElementById('fps').value = 1;
	
	//Refrech
	base_emails_load_list_show();
}

//Load the Auto-complete !
function base_emails_name_div_autocomplete(){
	document.getElementById('base_emails_search_autosuggest_loading').style.display = 'inline';
	
				
	var f_search			= document.getElementById('f_search').value;

	var OAjax;
	
	if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
	else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

	OAjax.open('POST','/fr/marketing/base-email-name-autocomplete.php',true);
	OAjax.onreadystatechange = function(){
		// OAjax.readyState == 1   ==>  connexion ?tablie
		// OAjax.readyState == 2   ==>  requete recue
		// OAjax.readyState == 3   ==>  reponse en cours
		
		
			if ((OAjax.readyState == 1) || (OAjax.readyState == 2) || (OAjax.readyState == 3) ){
				//document.getElementById('panel-table').style.opacity = '0.2';
				//document.getElementById('loader_panel-table').style.display = 'block';
			}
			
			if (OAjax.readyState == 4 && OAjax.status==200){
				document.getElementById('base_emails_search_autosuggest').style.display = 'block';
				document.getElementById('base_emails_search_autosuggest_content').innerHTML = '';
				document.getElementById('base_emails_search_autosuggest_loading').style.display = 'none';

				if(OAjax.responseText !=''){

					//document.getElementById('panel-table').style.opacity = '1';
					document.getElementById('base_emails_search_autosuggest_content').innerHTML+=''+OAjax.responseText+'';


				}else{
					base_emails_load_list_show();
				}
			}
		}
		
	OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	OAjax.send('f_search='+f_search);
	
	return false;	
}


//Hide the Auto-complet e!
function base_emails_search_hide_autosuggest(){
	document.getElementById('base_emails_search_autosuggest').style.display='none';
}

//Function to fill after the click on the autocomplete
function base_emails_fill_this_one(string){
	document.getElementById('f_search').value=string;
	base_emails_search_hide_autosuggest();
	base_emails_load_list_show();
}

/************************************************************************************************/
/****************************************** Fiche Email *****************************************/
/************************************************************************************************/

//Load a Email Basic informations
function base_emails_load_one_basic_infos(){
	var email_id			= document.getElementById('base_email_hidden_id').value;
	
	document.getElementById('base_email_top_element').innerHTML	= '';
	$("#base_email_top_element").hide('fast');
	
	var OAjax;
	
	if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
	else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

	OAjax.open('POST','/fr/marketing/fiche-basic-infos-email-base-email.php',true);
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

					//document.getElementById('panel-table').style.opacity = '1';
					document.getElementById('base_email_top_element').innerHTML+=''+OAjax.responseText+'';
					$("#base_email_top_element").show('fast');

				//}else{
					//
				//}
			}
		}
		
	OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	OAjax.send('email_id='+email_id);
	
	return false;	
}


/**********************************************/
/**************** 	Enable	*******************/
/**********************************************/

//Ask To activate a Email
function ask_autorize_this_address(email_id){
	var title 	= 'Confirmation d\'action !';
	var errors	= '<br /><br /><br /><center>'+
						'&Ecirc;tes vous s&ucirc;r de vouloir r&eacute;activer cette adresse ?'+
					'</center>';
	autorize_this_address_display_modal('base_emails_actions_ask', title, errors, email_id);
}

function autorize_this_address_display_modal(div_id, title, errors, email_id){
	document.getElementById(div_id).innerHTML	= errors;
	$( "#"+div_id ).dialog({
		resizable: false,
		draggable: false,
		height:330,
		width: 500,
		modal: true,
		title: title,
		buttons: {
			"Oui": function(){
				autorize_this_address(div_id, email_id);
				setTimeout(function(){ 
					$( "#"+div_id ).dialog( "close" ); 
				}, 1450);
			},
			"Annuler": function(){
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

function autorize_this_address(div_id, email_id){
	var elements_to_show	= '<br /><br /><br /><center>'+
						'<img src="ressources/images/loading.gif" alt="Op&eacute;ration en cours.." title="Op&eacute;ration en cours.." />'+
					'</center>';
					
	$("#"+div_id).html(elements_to_show);
	
	var OAjax;
	
	if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
	else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

	OAjax.open('POST','/fr/marketing/base-email-enable-one.php',true);
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

					//document.getElementById('panel-table').style.opacity = '1';
					document.getElementById(div_id).innerHTML=''+OAjax.responseText+'';
					base_emails_load_one_basic_infos();

				//}else{
					//
				//}
			}
		}
		
	OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	OAjax.send('email_id='+email_id);
	
	return false;
}

/**********************************************/
/****************** 	Disable	***************/
/**********************************************/


function ask_blacklist_this_address(email_id){
	var title 	= 'Confirmation d\'action !';
	var errors	= '<br /><br /><br /><center>'+
						'&Ecirc;tes vous s&ucirc;r de vouloir d&eacute;sabonner cette adresse ?'+
					'</center>'+
					'<input type="hidden" id="base_email_blacklist_hidden_id" value="'+email_id+'" />'+
					'<span id="base_email_container_hidden_id" style="display:none;">base_emails_actions_ask</span>';
					
	blacklist_this_address_display_modal('base_emails_actions_ask', title, errors, email_id);
	blacklist_load_the_motifs_on_modal('base_emails_actions_ask');
}

//Load the motifs on the Modal
function blacklist_load_the_motifs_on_modal(div_id){
	var OAjax;
	
	if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
	else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

	OAjax.open('POST','/fr/marketing/base-email-motifs-list-load.php',true);
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

					//document.getElementById('panel-table').style.opacity = '1';
					document.getElementById(div_id).innerHTML+=''+OAjax.responseText+'';

				//}else{
					//
				//}
			}
		}
		
	OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	OAjax.send();
	
	return false;
}

function blacklist_this_address_display_modal(div_id, title, errors, email_id){
	document.getElementById(div_id).innerHTML	= errors;
	$( "#"+div_id ).dialog({
		resizable: false,
		draggable: false,
		height:330,
		width: 500,
		modal: true,
		title: title,
		buttons: {
			/*"Oui": function(){
				autorize_this_address(div_id, email_id);
			},*/
			"Annuler": function(){
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

function blacklist_this_address(){
	var div_id		= document.getElementById('base_email_container_hidden_id').innerHTML; 
	var email_id	= document.getElementById('base_email_blacklist_hidden_id').value;
	var motif		= document.getElementById('f_blacklist_select').options[document.getElementById('f_blacklist_select').selectedIndex].value;
	
	var elements_to_show	= '<br /><br /><br /><center>'+
						'<img src="ressources/images/loading.gif" alt="Op&eacute;ration en cours.." title="Op&eacute;ration en cours.." />'+
					'</center>';
					
	$("#"+div_id).html(elements_to_show);
	
	var OAjax;
	
	if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
	else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

	OAjax.open('POST','/fr/marketing/base-email-disable-one.php',true);
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

					//document.getElementById('panel-table').style.opacity = '1';
					document.getElementById(div_id).innerHTML=''+OAjax.responseText+'';
					base_emails_load_one_basic_infos();
					
					//Close the Modal
					setTimeout(function(){ 
						$( "#"+div_id ).dialog( "close" ); 
					}, 1450);
				
				//}else{
					//
				//}
			}
		}
		
	OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	OAjax.send('email_id='+email_id+'&motif='+motif);
	
	return false;
}


/**********************************************/
/********** 	Listing Informations **********/
/**********************************************/

//Load the detailled informations
function base_emails_load_one_detailled_infos(){
	
	document.getElementById('panel-table').style.opacity = '0.2';
	document.getElementById('loader_panel-table').style.display = 'block';
				
	//Page start
	var f_ps				= document.getElementById('fps').value;
	//Page pagination
	var f_pp 				= document.getElementById('fpp').value;

	//var f_search			= document.getElementById('f_search').value;
	var f_search			= "";
	
	//For the table order !
	var table_order			= document.getElementById('table_order').value;
	
	var email_id			= document.getElementById('base_email_hidden_id').value;
	
	document.getElementById('panel-table').innerHTML = '&nbsp;';

	
	var OAjax;
	
	if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
	else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

	OAjax.open('POST','/fr/marketing/operation-list-base-email.php',true);
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
					setTimeout(base_emails_detail_table_visible, 200);	

				//}else{
					//mmf_hide_autocomplete();
				//}
			}
		}
		
	OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	OAjax.send('f_ps='+f_ps+'&f_pp='+f_pp+'&f_search='+f_search+'&table_order='+table_order+'&email_id='+email_id);
	
	return false;
}

function base_emails_detail_table_visible(){
	document.getElementById('panel-table').style.opacity = '1';
}

function base_emails_detail_get_more_now(){
	var personnalised_pp	=	document.getElementById('f_select_p').options[document.getElementById('f_select_p').selectedIndex].value;
	document.getElementById('fpp').value	= personnalised_pp;

	//To avoid the empty page when we change the number per page 
	//(Ex: We have 17elements and we show 5 per page 
	//and are in the page number 3, Once we will change the count per page
	//We gonna have a empty table. For that we force it to go to the first page !
	document.getElementById('fps').value = 1;
	
	base_emails_load_one_detailled_infos();
}

function base_emails_detail_load_other_page(number){
	document.getElementById('fps').value = number;
	base_emails_load_one_detailled_infos();
}

//Make the order on the message list (Table)
function base_emails_detail_list_order_by(critere){
	document.getElementById('table_order').value	= critere;
	
	//Go back to the first page (Flag)
	document.getElementById('fps').value = 1;
	
	//Refrech
	base_emails_load_one_detailled_infos();
}