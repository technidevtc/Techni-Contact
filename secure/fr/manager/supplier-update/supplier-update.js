function mmf_load_listner_hide_autocompete(){
	$('html').click(function() {
		mmf_hide_autocomplete();
	});
}

function module_maj_fournisseurs_autocomplete(e){
	
	if(e.keyCode==13){
		//Enter
		//Click
		mmf_lunch_search();
		mmf_hide_autocomplete();
		
	}else if(e.keyCode==27){
		//Esc
		mmf_hide_autocomplete();
		
	}else if(e.keyCode==17){
		//Ctrl => 17
	
	}else{
	
		var nom_fournisseur	= document.getElementById('mmf_nom_fournisseur').value;
		var OAjax;
		mmf_hide_autocomplete();
		
		if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
		else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

		OAjax.open('POST','/fr/manager/supplier-update/search_autocomplete.php',true);
		OAjax.onreadystatechange = function(){
			// OAjax.readyState == 1   ==>  connexion ?tablie
			// OAjax.readyState == 2   ==>  requete recue
			// OAjax.readyState == 3   ==>  reponse en cours
			
			
				if ((OAjax.readyState == 1) || (OAjax.readyState == 2) || (OAjax.readyState == 3) ){
					document.getElementById('mmf_autocomp_loader').style.display = 'block';
				}
				
				if (OAjax.readyState == 4 && OAjax.status==200){
					document.getElementById('mmf_autocomp_loader').style.display = 'none';

					if(OAjax.responseText !=''){

						document.getElementById('mmf_autocomp_container').innerHTML+=''+OAjax.responseText+'';
						document.getElementById('mmf_autocomp_container').style.display = 'block';

					}else{
						mmf_hide_autocomplete();
					}
				}
			}
			
		OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
		OAjax.send('nom_fournisseur='+nom_fournisseur);
	}//end if e.keyCode
}

function mmf_autofill(name){
	document.getElementById('mmf_nom_fournisseur').value	= name;
	mmf_hide_autocomplete();
	
	mmf_lunch_search();
}

function mmf_lunch_search(){
	
	var nom_fournisseur	= document.getElementById('mmf_nom_fournisseur').value;
	var OAjax;
	document.getElementById('mmf_one_fournisseur_container').innerHTML='';
	
	if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
	else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

	OAjax.open('POST','/fr/manager/supplier-update/search_one_supplier.php',true);
	OAjax.onreadystatechange = function(){
		// OAjax.readyState == 1   ==>  connexion ?tablie
		// OAjax.readyState == 2   ==>  requete recue
		// OAjax.readyState == 3   ==>  reponse en cours
		
		
			if ((OAjax.readyState == 1) || (OAjax.readyState == 2) || (OAjax.readyState == 3) ){
				document.getElementById('mmf_one_fournisseur_loader').style.display = 'block';
			}
			
			if (OAjax.readyState == 4 && OAjax.status==200){
				document.getElementById('mmf_one_fournisseur_loader').style.display = 'none';

				if(OAjax.responseText !=''){

					document.getElementById('mmf_one_fournisseur_container').innerHTML+=''+OAjax.responseText+'';
					document.getElementById('mmf_one_fournisseur_container').style.display = 'block';

					//Show import button
					document.getElementById('mmf_create_import').style.display = 'block';
					
					//Load hidden id advertiser
					document.getElementById('mmf_import_id_advertiser').value = document.getElementById('mmf_import_id_advertiser_returned').value;
					
					mmf_get_last_imports_advertiser(nom_fournisseur);
					
				}else{
					document.getElementById('mmf_one_fournisseur_container').innerHTML='';
					document.getElementById('mmf_one_fournisseur_container').style.display = 'none';
					
					//Hide import button
					document.getElementById('mmf_create_import').style.display = 'none';
					
					//Clean hidden id advertiser
					document.getElementById('mmf_import_id_advertiser').value = '';
				}
			}
		}
		
	OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	OAjax.send('nom_fournisseur='+nom_fournisseur);

}

function mmf_hide_autocomplete(){
	document.getElementById('mmf_autocomp_container').innerHTML		= '';
	document.getElementById('mmf_autocomp_container').style.display	= 'none';
}

// Show/Hide Help
function supplier_update_help(){

	if(document.getElementById('supplier-update-help-container').style.display=='block'){
		//Hide block
		$( "#supplier-update-help-container" ).animate({
				display: "none",
				height: "toggle"
			}, 500, function() {
				// Animation complete.
			});
	
	}else{
		//Show block 
		$( "#supplier-update-help-container" ).animate({
				display: "block",
				height: "toggle"
			}, 500, function() {
				// Animation complete.
			});
			
	}

}

	
	
function mmf_lunch_new_import(){
	//Popup Add Attachment
	var $uploadMsnAttachmentDb = $("#mmf_popup_upload-msn-attachment-db").dialog({
	  width: 490,
	  autoOpen: false,
	  modal: true,
	  buttons: {
		"Annuler": function(){
			$(this).dialog("close");
			//Clear errors
			document.getElementById('mmf_import_errors').innerHTML = '';
		},
		"Envoyer": function(){
			//Clear errors
			document.getElementById('mmf_import_errors').innerHTML = '';
			
			var etat = true;
			
			if ($('input[name=mmf_import_number_reference]:checked').length == 0) {
				etat = false;
			}
			
			if ($('input[name=mmf_import_number_tarif]:checked').length == 0) {
				etat = false;
			}
			
			if(document.getElementById('module_internalnotes_pjMessFile').value==''){
				etat = false;
			}
			
			/*
			//For the input integer
			if(document.getElementById('mmf_import_number_reference').value<0 || document.getElementById('mmf_import_number_reference').value==''){
				etat = false;
			}
			
			if(document.getElementById('mmf_import_number_tarif').value<0 || document.getElementById('mmf_import_number_tarif').value==''){
				etat = false;
			}*/
			
			
			if(etat==true){
				document.getElementById('mmf_popup_import_form').submit();
			}else{
				document.getElementById('mmf_import_errors').innerHTML = '<br />Vous devez remplir les champs obligatoires !';
				
			}
			
		}
	  }
	});
	
	//Ajouter une pièce jointe Button Listner
	$("#mmf_fournisseur_new_import").on("click", function(){
		$uploadMsnAttachmentDb.dialog("open");
	});

}

function mmf_processing_step(id){
	//add loader
	document.getElementById('mmf_import_step_'+id).className='processing';	
}

function mmf_validate_step(id){
	//Remove loader	
	//Add CheckOK
	document.getElementById('mmf_import_step_'+id).className='processing_ok';
}

function mmf_add_internal_note(advertiser, context_param, content_param){

	var data_send = {	id_reference:advertiser, 
						context:	context_param, 
						content:	content_param,
						attachments_id_affect_to_note: ''};
	
	$.ajax({
		type: "POST",
			url: HN.TC.ADMIN_URL+"ressources/ajax/AJAX_Doctrine_Interface.php",
			data: { 
				type: "Doctrine_Object", 
				object: "InternalNotes", 
				method: "create", 
				/*loadQueryParams: me.loadQueryParams,*/ 
				data: data_send
			},
			dataType: "json",
			error: function(jqXHR, textStatus, errorThrown){
				//console.log(textStatus);
			},
			success: function(data, textStatus, jqXHR){
				if (data && data.success) {
				  mmf_validate_step('6');
				}else{
				  
				}
			
		}
    });
}


function mmf_cancel_this_operation(id_operation){
	var OAjax;
	document.getElementById('cancel_operation_results').innerHTML='';
	
	if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
	else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

	OAjax.open('POST','/fr/manager/supplier-update/AJAX_cancel_operation.php',true);
	OAjax.onreadystatechange = function(){
		// OAjax.readyState == 1   ==>  connexion ?tablie
		// OAjax.readyState == 2   ==>  requete recue
		// OAjax.readyState == 3   ==>  reponse en cours
		
			if ((OAjax.readyState == 1) || (OAjax.readyState == 2) || (OAjax.readyState == 3) ){
				//document.getElementById('mmf_one_fournisseur_loader').style.display = 'block';
			}
			
			if (OAjax.readyState == 4 && OAjax.status==200){
				//document.getElementById('mmf_one_fournisseur_loader').style.display = 'none';

				if(OAjax.responseText !=''){
					document.getElementById('cancel_operation_results').innerHTML+=''+OAjax.responseText+'';
				}else{

				}
			}
		}
		
	OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	OAjax.send('id_operation='+id_operation);
}

function mmf_get_last_imports(){
	var OAjax;
	document.getElementById('mmf_last_imports').innerHTML='';
	
	if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
	else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

	OAjax.open('POST','/fr/manager/supplier-update/last_imports.php',true);
	OAjax.onreadystatechange = function(){
		// OAjax.readyState == 1   ==>  connexion ?tablie
		// OAjax.readyState == 2   ==>  requete recue
		// OAjax.readyState == 3   ==>  reponse en cours
		
			if ((OAjax.readyState == 1) || (OAjax.readyState == 2) || (OAjax.readyState == 3) ){
				//document.getElementById('mmf_one_fournisseur_loader').style.display = 'block';
			}
			
			if (OAjax.readyState == 4 && OAjax.status==200){
				//document.getElementById('mmf_one_fournisseur_loader').style.display = 'none';

				if(OAjax.responseText !=''){
					document.getElementById('mmf_last_imports').innerHTML+=''+OAjax.responseText+'';
				}else{

				}
			}
		}
		
	OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	OAjax.send();
}

function mmf_get_last_imports_advertiser(nom_fournisseur){
	var OAjax;
	document.getElementById('mmf_last_imports').innerHTML='';
	
	if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
	else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

	OAjax.open('POST','/fr/manager/supplier-update/last_imports.php',true);
	OAjax.onreadystatechange = function(){
		// OAjax.readyState == 1   ==>  connexion ?tablie
		// OAjax.readyState == 2   ==>  requete recue
		// OAjax.readyState == 3   ==>  reponse en cours
		
			if ((OAjax.readyState == 1) || (OAjax.readyState == 2) || (OAjax.readyState == 3) ){
				//document.getElementById('mmf_one_fournisseur_loader').style.display = 'block';
			}
			
			if (OAjax.readyState == 4 && OAjax.status==200){
				//document.getElementById('mmf_one_fournisseur_loader').style.display = 'none';

				if(OAjax.responseText !=''){
					document.getElementById('mmf_last_imports').innerHTML+=''+OAjax.responseText+'';
				}else{

				}
			}
		}
		
	OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	OAjax.send('nom_fournisseur='+nom_fournisseur);
}
