function contacts_lunnch_search(){
	document.getElementById('panel-table').style.opacity = '0.2';
	document.getElementById('loader_panel-table').style.display = 'block';
				
	//Page start
	var f_ps				= document.getElementById('fps').value;
	//Page pagination
	var f_pp 				= document.getElementById('fpp').value;
	var fetat 				= document.getElementById('fetat').value;
	var f_date_jours		= document.getElementById('f_date_jours').value;
	var f_date_debut		= document.getElementById('f_date_debut').value;
	var f_date_fin			= document.getElementById('f_date_fin').value;
	var f_search			= document.getElementById('f_search').value;
	
	// Ajouter Le 01/07/2015 a 15:19 MA Par Zak Out
	var f_mois				= document.getElementById('f_mois').value;
	var f_mois_debut				= document.getElementById('f_mois_debut').value;
	var f_mois_fin				= document.getElementById('f_mois_fin').value;
	// Ajouter Le 01/07/2015 a 15:19 MA Par Zak Out
	
	var f_contacts_type		= document.getElementById('f_contacts_type').options[document.getElementById('f_contacts_type').selectedIndex].value;
	
	document.getElementById('panel-table').innerHTML = '&nbsp;';

	var OAjax;
	
	if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
	else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

	OAjax.open('POST','/fr/extranet/extranet-v3-contacts-load.php',true);
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
					setTimeout(contacts_table_visible, 200);	

				//}else{
					//mmf_hide_autocomplete();
				//}
			}
		}
		
	OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	OAjax.send('f_ps='+f_ps+'&f_pp='+f_pp+'&fetat='+fetat+'&f_date_debut='+f_date_debut+'&f_date_fin='+f_date_fin+'&f_search='+f_search+'&f_contacts_type='+f_contacts_type+'&f_mois='+f_mois+'&f_mois_debut='+f_mois_debut+'&f_mois_fin='+f_mois_fin+'&f_date_jours='+f_date_jours);
		
	
}

function contacts_table_visible(){
	document.getElementById('panel-table').style.opacity = '1';
	//alert('finish');
}

function contacts_get_more_now(){
	var personnalised_pp	=	document.getElementById('f_select_p').options[document.getElementById('f_select_p').selectedIndex].value;
	document.getElementById('fpp').value	= personnalised_pp;

	contacts_lunnch_search();
}

function contact_get_filtre_etat(){
	var etat = $("#filtre_etat").val();
	$("#fetat").val(etat);
	contacts_lunnch_search();
	
}

function contacts_load_other_page(number,etat){
	document.getElementById('fps').value = number;
	$("#fetat").val(etat);
	contacts_lunnch_search();
}

function contacts_extract(){
	
	//Page start
	var f_ps				= document.getElementById('fps').value;
	//Page pagination
	var f_pp 				= document.getElementById('fpp').value;
	var fetat 				= document.getElementById('fetat').value;
	var f_date_jours		= document.getElementById('f_date_jours').value;
	var f_date_debut		= document.getElementById('f_date_debut').value;
	var f_date_fin			= document.getElementById('f_date_fin').value;
	var f_search			= document.getElementById('f_search').value;
	
	// Ajouter Le 01/07/2015 a 15:19 MA Par Zak Out
	var f_mois				= document.getElementById('f_mois').value;
	var f_mois_debut				= document.getElementById('f_mois_debut').value;
	var f_mois_fin				= document.getElementById('f_mois_fin').value;
	// Ajouter Le 01/07/2015 a 15:19 MA Par Zak Out
	var f_contacts_type		= document.getElementById('f_contacts_type').options[document.getElementById('f_contacts_type').selectedIndex].value;
	//alert(fetat);
	window.open('/fr/extranet/extranet-v3-contacts-extracts.php?f_ps='+f_ps+'&f_pp='+f_pp+'&fetat='+fetat+'&f_date_debut='+f_date_debut+'&f_date_fin='+f_date_fin+'&f_search='+f_search+'&f_contacts_type='+f_contacts_type+'&f_mois='+f_mois+'&f_mois_debut='+f_mois_debut+'&f_mois_fin='+f_mois_fin+'&f_date_jours='+f_date_jours,'_blank');
	
}


function forward_select_me(id, type){

	
	//Detect the type of the element (Manager or Coordoonnées)
	//To have the accessibility to this element !
	//When Type is "man"		=> Manager
	//When Type is "coo"		=> Coordoonnées

	
	//Building Element
	var element = '<div id="forward_mail_element_'+type+'_'+id+'" class="contact-detail-form-onee-c opa">';
		element += document.getElementById('forward_mail_element_'+type+'_'+id).innerHTML;
	element += '</div>';
	
	$("#forward_mail_element_"+type+"_"+id).remove();
	document.getElementById('forward_list_mails').innerHTML += element;
	
	$("#forward_mail_element_"+type+"_"+id+" i").attr('class', 'fa fa-remove');
	$("#forward_mail_element_"+type+"_"+id+" i").attr('title', 'Enlever');
	$("#forward_mail_element_"+type+"_"+id+" i").attr("onclick","forward_deselect_me('"+id+"', '"+type+"')");	
}

function forward_deselect_me(id, type){

	//Building Element
	var element = '<div id="forward_mail_element_'+type+'_'+id+'" class="contact-detail-form-onee-c opa">';
		element += document.getElementById('forward_mail_element_'+type+'_'+id).innerHTML;
	element += '</div>';
	
	$("#forward_mail_element_"+type+"_"+id).remove();
	
	//To move it back to it's origin container
	//If it's a manager or coordoonées
	if(type=='man'){
		document.getElementById('forward_list_mails_source_manager').innerHTML += element;
	}else{
		document.getElementById('forward_list_mails_source_coordonnees').innerHTML += element;
	}
	
	$("#forward_mail_element_"+type+"_"+id+" i").attr('class', 'fa fa-plus-circle');
	$("#forward_mail_element_"+type+"_"+id+" i").attr('title', 'Ajouter');
	$("#forward_mail_element_"+type+"_"+id+" i").attr("onclick","forward_select_me('"+id+"', '"+type+"')");
}


//Forward Popup Email validation !

function isValidEmailAddress(emailAddress) {
	var pattern = new RegExp(/^[\w-]+(\.[\w-]+)*@([\w-]+\.)+[a-zA-Z]{2,4}$/);

	return pattern.test(emailAddress);
}

function activate_listner_popup_email(){
	$(document).ready(function() {
		$("#transfert_mail_input").keyup(function(e){
			var email = $("#transfert_mail_input").val();

			var code = e.keyCode || e.which;
			
			if(email != 0){
				if(isValidEmailAddress(email)){
					 //alert('Valid Email');
					$("#forwardvalidate").css({ "background-image": "url('ressourcesv3/icons/accept.png')" });
					
					//Detect if Enter was pressed
					if(code==13){
						forward_select_me_manual();
						$("#forwardvalidate").css({ "background-image": "none" });
					}
				  
				}else{
				  //alert('InValid Email');
				  $("#forwardvalidate").css({ "background-image": "url('ressourcesv3/icons/cross.png')" });
				}
			} else {
				$("#forwardvalidate").css({ "background-image": "none" });
			}
		});
	});
}

function forward_select_me_manual(){

	//Validation mail
	var email = $("#transfert_mail_input").val();
	
	if( (email != 0) && isValidEmailAddress(email) ){
	
		//Building HTML
		
		var incremented_id	= 1;
		while(document.getElementById('forward_mail_element'+incremented_id+'')){
			incremented_id++;
		}
		var element = '<div id="forward_mail_element'+incremented_id+'" class="contact-detail-form-onee-c opa">';
			element += '<div class="contact-dst_list-popup-onee-l1">';
				element += '&nbsp;';
			element += '</div>';
			
			element += '<div class="contact-dst_list-popup-onee-l2">';
				element += email;
			element += '</div>';
			
			element += '<div class="contact-dst_list-popup-onee-l3" style="display:none;">';
				element += 'manuelle';
			element += '</div>';
			
			element += '<div class="contact-dst_list-popup-onee-r">';
				element += '<i class="fa fa-remove" onclick="forward_deselect_me_manual(\''+incremented_id+'\')" title="Supprimer"></i>';
			element += '</div>';			
		element += '</div>';
		
		document.getElementById('forward_list_mails').innerHTML += element;
		$("#transfert_mail_input").val('');
		
		$("#forwardvalidate").css({ "background-image": "none" });
	}else{
		$("#forwardvalidate").css({ "background-image": "url('ressourcesv3/icons/cross.png')" });
	}

}

function forward_deselect_me_manual(id){
	$("#forward_mail_element"+id).remove();
}

function contacts_execute_the_forward(){
	document.getElementById('contacts_popup_forward_errors').innerHTML = '';
	document.getElementById('contacts_popup_forward_errors').style.display = 'none';
	
	var global_send_infos	= '';
	var forward_childrens 	= $("#forward_list_mails").children();
	var contact_id			= document.getElementById('forward_id_contact').value;
	
	if(forward_childrens[0]!=undefined){
	
		document.getElementById('contacts_popup_forward_loader').style.display = 'block';
		
		var local_loop	= 0;
		while(forward_childrens[local_loop]!=undefined){
		
			var forward_childrens_2	 = $(forward_childrens[local_loop]).children();
			
			//Extract the final informations !
			//alert(forward_childrens_2[1].innerHTML+' <= Name');
			//alert(forward_childrens_2[2].innerHTML+' <= Mail');
			
			//global_send_infos += forward_childrens_2[1].innerHTML+'|'+forward_childrens_2[2].innerHTML+'#';
			
			//Firstname lastname | email | source #
			
			//To delete the first "&nbsp;"
			var full_name	= forward_childrens_2[0].innerHTML.replace("&nbsp;", "");
			
			global_send_infos += full_name+'|'+forward_childrens_2[1].innerHTML+'|'+forward_childrens_2[2].innerHTML+'#';
			
			local_loop++;
		}
		
		//Start Send
			
			var OAjax;
			if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
			else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

			OAjax.open('POST','/fr/extranet/extranet-v3-contacts-forward.php',true);
			OAjax.onreadystatechange = function(){
				// OAjax.readyState == 1   ==>  connexion ?tablie
				// OAjax.readyState == 2   ==>  requete recue
				// OAjax.readyState == 3   ==>  reponse en cours
				
					//if ((OAjax.readyState == 1) || (OAjax.readyState == 2) || (OAjax.readyState == 3) ){
						//document.getElementById('contacts_popup_forward_loader').style.display = 'block';	
					//}
					
					if (OAjax.readyState == 4 && OAjax.status==200){
						document.getElementById('contacts_popup_forward_errors').innerHTML+=''+OAjax.responseText+'';
						document.getElementById('contacts_popup_forward_errors').style.display = 'block';
						
						//Clear the list of emails !
						contacts_clear_list_email();
						
					}
					
					document.getElementById('contacts_popup_forward_loader').style.display = 'none';
					
					
				}
				
			OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
			OAjax.send('id='+contact_id+'&br='+global_send_infos);
			
		//End Send	
		
		
		
	}else{
		document.getElementById('contacts_popup_forward_errors').innerHTML	= 'Vous devez ajouter au moins une adresse mail !';
		document.getElementById('contacts_popup_forward_errors').style.display = 'block';
	}
	
		
}


function contacts_clear_list_email(){

	var forward_childrens 	= $("#forward_list_mails").children();
	var local_loop	= 0;
	while(forward_childrens[local_loop]!=undefined){
		var forward_childrens_2	 = $(forward_childrens[local_loop]).children();
		$(forward_childrens_2[0]).children().click();			
		local_loop++;
	}
}

function contact_detail_map_gecode(address, city, country, company){
	var x 					= '46.677625';
	var y 					= '2.639160';
	var zoom 				= 4;
	var zoom_point_found	= 15;
	
	var mapCanvas = document.getElementById('contact_detail_map');
    var mapOptions = {
      center: new google.maps.LatLng(x, y),
      zoom: zoom,
	  minZoom: 3,
      mapTypeId: google.maps.MapTypeId.ROADMAP
    }
    var map = new google.maps.Map(mapCanvas, mapOptions);
	
	
	geocoder = new google.maps.Geocoder();
	if (geocoder) {
		geocoder.geocode( { 'address': address+', '+city+', '+country}, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				if (status != google.maps.GeocoderStatus.ZERO_RESULTS) {
					map.setCenter(results[0].geometry.location);
					map.setZoom(zoom_point_found);
					
					//alert(results[0].geometry.location+' => '+results[0].geometry.location.lat()+' * '+results[0].geometry.location.lng());
					document.getElementById('contact_detail_map_newwindow_a').href="https://www.google.fr/maps/place//@"+results[0].geometry.location.lat()+","+results[0].geometry.location.lng()+","+zoom_point_found+"z";
					
					document.getElementById('contact_detail_map_newwindow').style.display='block';
					
					var infowindow = new google.maps.InfoWindow(
						{ content: '<b>'+address+', '+country+'</b>',
						size: new google.maps.Size(200,50)
						});

					var marker = new google.maps.Marker({
						position: results[0].geometry.location,
						map: map, 
						title: company
					}); 
					google.maps.event.addListener(marker, 'click', function() {
						infowindow.open(map,marker);
					});

				}else{
					//alert("No results found");
				}
			}else {
			  //alert("Geocode was not successful for the following reason: " + status);
			}
		});
    }
}

function contact_archive_me(contact_id, source){
	if(contact_id!=''){
		var OAjax;
		if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
		else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

		OAjax.open('POST','/fr/extranet/extranet-v3-contacts-archive.php',true);
		OAjax.onreadystatechange = function(){
			// OAjax.readyState == 1   ==>  connexion ?tablie
			// OAjax.readyState == 2   ==>  requete recue
			// OAjax.readyState == 3   ==>  reponse en cours
			
				//if ((OAjax.readyState == 1) || (OAjax.readyState == 2) || (OAjax.readyState == 3) ){
					//document.getElementById('contacts_popup_forward_loader').style.display = 'block';	
				//}
				
				if (OAjax.readyState == 4 && OAjax.status==200){
					if(OAjax.responseText=='1'){
						alert('Contact archiv\351');
					}else if(OAjax.responseText=='0'){
						alert('Erreur, Merci de recharger la page');
					}else if(OAjax.responseText=='-1'){
						alert('Votre session a expir\351. Veuillez vous reconnecter !');
						window.location	= 'login.html';
					}else{
						alert('Erreur, merci de r\351essayer !');
					}
					
					if(source=='listing'){
						contacts_lunnch_search();
					}else{
						document.getElementById('h4-contact-detail-archive').style.display = 'none';
					}
				}			
				
			}
			
		OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
		OAjax.send('id='+contact_id);
	}
}



function contact_delete_me(contact_id, source){
	if(contact_id!=''){
		var OAjax;
		if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
		else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

		OAjax.open('POST','/fr/extranet/extranet_v3_contacts_deleted.php',true);
		OAjax.onreadystatechange = function(){
			// OAjax.readyState == 1   ==>  connexion ?tablie
			// OAjax.readyState == 2   ==>  requete recue
			// OAjax.readyState == 3   ==>  reponse en cours
			
				//if ((OAjax.readyState == 1) || (OAjax.readyState == 2) || (OAjax.readyState == 3) ){
					//document.getElementById('contacts_popup_forward_loader').style.display = 'block';	
				//}
				
				if (OAjax.readyState == 4 && OAjax.status==200){
					if(OAjax.responseText=='1'){
						alert('Contact Supprim\351');
					}else if(OAjax.responseText=='0'){
						alert('Erreur, Merci de recharger la page');
					}else if(OAjax.responseText=='-1'){
						alert('Votre session a expir\351. Veuillez vous reconnecter !');
						window.location	= 'login.html';
					}else{
						alert('Erreur, merci de r\351essayer !');
					}
					
					if(source=='listing'){
						contacts_lunnch_search();
					}else{
						document.getElementById('h4-contact-detail-archive').style.display = 'none';
					}
				}			
			}
		if(confirm('Êtes-vous sûr de vouloir mettre ce contact dans le dossier corbeille ? ')){
		OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
		OAjax.send('id='+contact_id);
		}
	}
}

function contact_activate_listner_search_input(){
	$(document).ready(function() {
		$("#f_search").keyup(function(e){
			var email = $("#f_search").val();

			var code = e.keyCode || e.which;

			//Detect if Enter was pressed
			if(code==13){
				contacts_lunnch_search();
			}
		});
	});
}


function contacts_reject_select_litsner(){
	//var reject_type		= document.getElementById('reject-option').options[document.getElementById('reject-option').selectedIndex].value;
	var reject_type		= document.getElementById('reject-option').selectedIndex;
	
	if(reject_type==6){
		document.getElementById('reject-reason-container').style.display	= 'block';
	}else{
		document.getElementById('reject-reason-container').style.display	= 'none';
	}
}


//Function to validate form in case if the user choose the "Other" option
//So we have to validate the text field !
function contact_reject_check_form(){
	document.getElementById('contact_reject_justificatif_erros').style.display	= 'none';
	var reject_type		= document.getElementById('reject-option').selectedIndex;
	
	if(reject_type==6){
		if(document.getElementById('reject-reason').value.length<10){
			document.getElementById('contact_reject_justificatif_erros').style.display	= 'block';
			return false;
		}else if(document.getElementById('reject-reason').value.match(/^\s*$/)){
			document.getElementById('contact_reject_justificatif_erros').style.display	= 'block';
			return false;
		}else{
			document.getElementById('contact_reject_justificatif_erros').style.display	= 'none';
			return true;
		}
	}else{
		document.getElementById('contact_reject_justificatif_erros').style.display	= 'none';
		return true;
	}
}
