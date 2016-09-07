function infos_table_visible(divid){
	document.getElementById(divid).style.opacity = '1';
}

function infos_basic_load(){
	
	$.ajax({
		url: 'extranet_v3_infos_load_basic.php',  //Server script to process data
		type: 'POST',
		xhr: function() {  // Custom XMLHttpRequest
			var myXhr = $.ajaxSettings.xhr();
			if(myXhr.upload){ // Check if upload property exists
				//myXhr.upload.addEventListener('pictureprogress',picture_progressHandlingFunction, false); // For handling the progress of the upload
			}
			return myXhr;
		},
		//Ajax events
		beforeSend: function() {
				document.getElementById('infos_basic_loader').style.display	= 'block';
				document.getElementById('infos_basic_content').style.opacity = '0.2';
			},
		success: function(data) {
				
				document.getElementById('infos_basic_loader').style.display	= 'none';
				document.getElementById('infos_basic_content').innerHTML	= data;
				setTimeout(infos_table_visible('infos_basic_content'), 200);
				
			},
		error: function() {
				document.getElementById('infos_basic_loader').style.display	= 'none';
				//errorHandler,
				document.getElementById('infos_basic_content').innerHTML	= 'Erreur, merci de r&eacute;essayer !';
			},
		// Form data
		data: '',
		//Options to tell jQuery not to process data or worry about content-type.
		cache: false,
		contentType: false,
		processData: false
	});
	
}


function infos_contacts_load(){

	$.ajax({
		url: 'extranet_v3_infos_load_contacts.php',  //Server script to process data
		type: 'POST',
		xhr: function() {  // Custom XMLHttpRequest
			var myXhr = $.ajaxSettings.xhr();
			if(myXhr.upload){ // Check if upload property exists
				//myXhr.upload.addEventListener('pictureprogress',picture_progressHandlingFunction, false); // For handling the progress of the upload
			}
			return myXhr;
		},
		//Ajax events
		beforeSend: function() {
				document.getElementById('infos_contacts_loader').style.display	= 'block';
				document.getElementById('infos_contacts_content').style.opacity = '0.2';
			},
		success: function(data) {
				
				document.getElementById('infos_contacts_loader').style.display	= 'none';
				document.getElementById('infos_contacts_content').innerHTML	= data;
				setTimeout(infos_table_visible('infos_contacts_content'), 200);
				
			},
		error: function() {
				document.getElementById('infos_contacts_loader').style.display	= 'none';
				//errorHandler,
				document.getElementById('infos_contacts_content').innerHTML	= 'Erreur, merci de r&eacute;essayer !';
			},
		// Form data
		data: '',
		//Options to tell jQuery not to process data or worry about content-type.
		cache: false,
		contentType: false,
		processData: false
	});
}

function infos_connexion_load(){
	$.ajax({
		url: 'extranet_v3_infos_load_connexion.php',  //Server script to process data
		type: 'POST',
		xhr: function() {  // Custom XMLHttpRequest
			var myXhr = $.ajaxSettings.xhr();
			if(myXhr.upload){ // Check if upload property exists
				//myXhr.upload.addEventListener('pictureprogress',picture_progressHandlingFunction, false); // For handling the progress of the upload
			}
			return myXhr;
		},
		//Ajax events
		beforeSend: function() {
				document.getElementById('infos_connexion_loader').style.display	= 'block';
				document.getElementById('infos_connexion_content').style.opacity = '0.2';
			},
		success: function(data) {
				
				document.getElementById('infos_connexion_loader').style.display	= 'none';
				document.getElementById('infos_connexion_content').innerHTML	= data;
				setTimeout(infos_table_visible('infos_connexion_content'), 200);
				
			},
		error: function() {
				document.getElementById('infos_connexion_loader').style.display	= 'none';
				//errorHandler,
				document.getElementById('infos_connexion_content').innerHTML	= 'Erreur, merci de r&eacute;essayer !';
			},
		// Form data
		data: '',
		//Options to tell jQuery not to process data or worry about content-type.
		cache: false,
		contentType: false,
		processData: false
	});
}

function infos_connexion_show_password(){
	document.getElementById('user_connexion_container').innerHTML	= document.getElementById('user_connexion_hpassword').value;
}


function infos_basic_edit(){

	document.getElementById('infos-dialog-basic-edit-response').innerHTML	= '';
	$( "#infos-dialog-basic-edit-popup" ).dialog({
		resizable: false,
		height: 350,
		/*width: "80%",*/
		width: "60%",
		modal: true,
		buttons: {
			"Confirmer": function() {
				infos_basic_edit_confirm();
			},
			"Fermer": function(){
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
	
	//Load Editable Basic infos
	infos_basic_load_edit();
}

//Load Editable Basic infos
function infos_basic_load_edit(){
	$.ajax({
		url: 'extranet_v3_infos_load_basic_edit.php',  //Server script to process data
		type: 'POST',
		xhr: function() {  // Custom XMLHttpRequest
			var myXhr = $.ajaxSettings.xhr();
			if(myXhr.upload){ // Check if upload property exists
				//myXhr.upload.addEventListener('pictureprogress',picture_progressHandlingFunction, false); // For handling the progress of the upload
			}
			return myXhr;
		},
		//Ajax events
		beforeSend: function() {
				document.getElementById('infos-dialog-basic-edit-popup-loader').style.display	= 'block';
			},
		success: function(data) {
				
				document.getElementById('infos-dialog-basic-edit-popup-loader').style.display	= 'none';
				document.getElementById('infos-dialog-basic-edit-response').innerHTML	= data;				
			},
		error: function() {
				document.getElementById('infos-dialog-basic-edit-popup-loader').style.display	= 'none';
				//errorHandler,
				document.getElementById('infos-dialog-basic-edit-response').innerHTML	= 'Erreur, merci de r&eacute;essayer !';
			},
		// Form data
		data: '',
		//Options to tell jQuery not to process data or worry about content-type.
		cache: false,
		contentType: false,
		processData: false
	});
}


function isValidEmailAddress(emailAddress) {
	var pattern = new RegExp(/^[\w-]+(\.[\w-]+)*@([\w-]+\.)+[a-zA-Z]{2,4}$/);

	return pattern.test(emailAddress);
}


//Confirm edit changes Basic informations
function infos_basic_edit_confirm(){

	var etat 				= 1;
	var name				= document.getElementById('infos_basic_name').value;
	var adresse				= document.getElementById('infos_basic_adresse').value;
	var ville				= document.getElementById('infos_basic_ville').value;
	var code_postale		= document.getElementById('infos_basic_cp').value;
	var pays				= document.getElementById('infos_basic_pays').value;
	var tel					= document.getElementById('infos_basic_tel').value;
	var fax					= document.getElementById('infos_basic_fax').value;
	var url					= document.getElementById('infos_basic_url').value;
	var contact_name		= document.getElementById('infos_basic_contact').value;
	var contact_mail		= document.getElementById('infos_basic_econtact').value;
	
	document.getElementById('infos_basic_edit_errors').innerHTML 	= '';
	
	if(name.length<3){
		document.getElementById('infos_basic_edit_errors').innerHTML 	+= '<br />';
		document.getElementById('infos_basic_edit_errors').innerHTML	+= '- Le <b>Nom soci&eacute;t&eacute;</b> est obligatoire';
		etat 	= 0;
	}else if(name.match(/^\s*$/)){
		document.getElementById('infos_basic_edit_errors').innerHTML 	+= '<br />';
		document.getElementById('infos_basic_edit_errors').innerHTML	+= '- Le <b>Nom soci&eacute;t&eacute;</b> est obligatoire';
		etat 	= 0;
	}
	
	if(adresse.length<3){
		document.getElementById('infos_basic_edit_errors').innerHTML 	+= '<br />';
		document.getElementById('infos_basic_edit_errors').innerHTML	+= '- L\'<b>Adresse</b> est obligatoire';
		etat 	= 0;
	}else if(adresse.match(/^\s*$/)){
		document.getElementById('infos_basic_edit_errors').innerHTML 	+= '<br />';
		document.getElementById('infos_basic_edit_errors').innerHTML	+= '- L\'<b>Adresse</b> est obligatoire';
		etat 	= 0;
	}
	
	if(ville.length<3){
		document.getElementById('infos_basic_edit_errors').innerHTML 	+= '<br />';
		document.getElementById('infos_basic_edit_errors').innerHTML	+= '- La <b>Ville</b> est obligatoire';
		etat 	= 0;
	}else if(ville.match(/^\s*$/)){
		document.getElementById('infos_basic_edit_errors').innerHTML 	+= '<br />';
		document.getElementById('infos_basic_edit_errors').innerHTML	+= '- La <b>Ville</b> est obligatoire';
		etat 	= 0;
	}
	
	if(code_postale.length<3){
		document.getElementById('infos_basic_edit_errors').innerHTML 	+= '<br />';
		document.getElementById('infos_basic_edit_errors').innerHTML	+= '- Le <b>Code postal</b> est obligatoire';
		etat 	= 0;
	}else if(code_postale.match(/^\s*$/)){
		document.getElementById('infos_basic_edit_errors').innerHTML 	+= '<br />';
		document.getElementById('infos_basic_edit_errors').innerHTML	+= '- Le <b>Code postal</b> est obligatoire';
		etat 	= 0;
	}
	
	if(pays.length<3){
		document.getElementById('infos_basic_edit_errors').innerHTML 	+= '<br />';
		document.getElementById('infos_basic_edit_errors').innerHTML	+= '- Le <b>Pays</b> est obligatoire';
		etat 	= 0;
	}else if(pays.match(/^\s*$/)){
		document.getElementById('infos_basic_edit_errors').innerHTML 	+= '<br />';
		document.getElementById('infos_basic_edit_errors').innerHTML	+= '- Le <b>Pays</b> est obligatoire';
		etat 	= 0;
	}
	
	if(tel.length<3){
		document.getElementById('infos_basic_edit_errors').innerHTML 	+= '<br />';
		document.getElementById('infos_basic_edit_errors').innerHTML	+= '- Le <b>T&eacute;l&eacute;phone</b> est obligatoire';
		etat 	= 0;
	}else if(tel.match(/^\s*$/)){
		document.getElementById('infos_basic_edit_errors').innerHTML 	+= '<br />';
		document.getElementById('infos_basic_edit_errors').innerHTML	+= '- Le <b>T&eacute;l&eacute;phone</b> est obligatoire';
		etat 	= 0;
	}
	
	if(contact_name.length<3){
		document.getElementById('infos_basic_edit_errors').innerHTML 	+= '<br />';
		document.getElementById('infos_basic_edit_errors').innerHTML	+= '- Le <b>Contact principal</b> est obligatoire';
		etat 	= 0;
	}else if(contact_name.match(/^\s*$/)){
		document.getElementById('infos_basic_edit_errors').innerHTML 	+= '<br />';
		document.getElementById('infos_basic_edit_errors').innerHTML	+= '- Le <b>Contact principal</b> est obligatoire';
		etat 	= 0;
	}
	
	if(!isValidEmailAddress(contact_mail)){
		document.getElementById('infos_basic_edit_errors').innerHTML 	+= '<br />';
		document.getElementById('infos_basic_edit_errors').innerHTML	+= '- L\'<b>Email contact principal</b> est obligatoire';
		etat 	= 0;
	}
	
	if(etat==0){
		//Scroll to the Bottom of the Popup
		$('#infos-dialog-basic-edit-popup').animate({ scrollTop: $(document).height() }, 1000);
	}else if(etat==1){
	
		var formData = new FormData();
		formData.append("name", name);
		formData.append("adresse", adresse);
		formData.append("ville", ville);
		formData.append("code_postale", code_postale);
		formData.append("pays", pays);
		formData.append("tel", tel);
		formData.append("fax", fax);
		formData.append("url", url);
		formData.append("contact_name", contact_name);
		formData.append("contact_mail", contact_mail);
		
	
		$.ajax({
			url: 'extranet_v3_infos_load_basic_edit_confirm.php',  //Server script to process data
			type: 'POST',
			xhr: function() {  // Custom XMLHttpRequest
				var myXhr = $.ajaxSettings.xhr();
				if(myXhr.upload){ // Check if upload property exists
					//myXhr.upload.addEventListener('pictureprogress',picture_progressHandlingFunction, false); // For handling the progress of the upload
				}
				return myXhr;
			},
			//Ajax events
			beforeSend: function() {
					document.getElementById('infos-dialog-basic-edit-popup-loader').style.display	= 'block';
					document.getElementById('infos_basic_edit_errors').innerHTML			= '';
				},
			success: function(data) {
				
					if(data=='1'){
						document.getElementById('infos-dialog-basic-edit-response').innerHTML += '<font color="green">Informations modifi&eacute;es avec succ&egrave;s</font>';
						
						//Close the Popup
						$( "#infos-dialog-basic-edit-popup" ).dialog( "close" );
					
						//Reload the basic content
						infos_basic_load();
						
						
					}else if(data=='-1'){
						//document.getElementById('infos-dialog-basic-edit-response').innerHTML += 'Vous devez vous reconnecter !';
						window.document='login.html';
					}else{
						document.getElementById('infos_basic_edit_errors').innerHTML = 'Erreur, merci de r\351essayer !';
						
						//Scroll to the Bottom of the Popup
						$('#infos-dialog-basic-edit-popup').animate({ scrollTop: $(document).height() }, 1000);
						
						//$("html, body").animate({ scrollTop: $(document).height() }, 1000);
						
					}//end else
					
					document.getElementById('infos-dialog-basic-edit-popup-loader').style.display	= 'none';
			
				},
			error: function() {
					document.getElementById('infos-dialog-basic-edit-popup-loader').style.display	= 'none';
					//errorHandler,
					document.getElementById('infos-dialog-basic-edit-response').innerHTML	= 'Erreur, merci de r&eacute;essayer !';
				},
			// Form data
			data: formData,
			//Options to tell jQuery not to process data or worry about content-type.
			cache: false,
			contentType: false,
			processData: false
		});
	}
	
}

/*****************************************************************/
/******************* 		Connexion 	**************************/
/*****************************************************************/
function infos_connexion_edit(){
	document.getElementById('infos-dialog-connexion-edit-response').innerHTML	= '';
	$( "#infos-dialog-connexion-edit-popup" ).dialog({
		resizable: false,
		height: 300,
		width: "50%",
		modal: true,
		buttons: {
			"Confirmer": function() {
				infos_connexion_edit_confirm();
			},
			"Fermer": function(){
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
	
	//Load Editable connexion infos
	infos_connexion_load_edit();
}

function infos_connexion_load_edit(){
	$.ajax({
		url: 'extranet_v3_infos_load_connexion_edit.php',  //Server script to process data
		type: 'POST',
		xhr: function() {  // Custom XMLHttpRequest
			var myXhr = $.ajaxSettings.xhr();
			if(myXhr.upload){ // Check if upload property exists
				//myXhr.upload.addEventListener('pictureprogress',picture_progressHandlingFunction, false); // For handling the progress of the upload
			}
			return myXhr;
		},
		//Ajax events
		beforeSend: function() {
				document.getElementById('infos-dialog-connexion-edit-popup-loader').style.display	= 'block';
			},
		success: function(data) {
				
				document.getElementById('infos-dialog-connexion-edit-popup-loader').style.display	= 'none';
				document.getElementById('infos-dialog-connexion-edit-response').innerHTML	= data;				
			},
		error: function() {
				document.getElementById('infos-dialog-connexion-edit-popup-loader').style.display	= 'none';
				//errorHandler,
				document.getElementById('infos-dialog-connexion-edit-response').innerHTML	= 'Erreur, merci de r&eacute;essayer !';
			},
		// Form data
		data: '',
		//Options to tell jQuery not to process data or worry about content-type.
		cache: false,
		contentType: false,
		processData: false
	});
}


function infos_connexion_edit_confirm(){
	var etat 				= 1;
	var login				= document.getElementById('infos_login_popup').value;
	var password			= document.getElementById('infos_password_popup').value;
	var passwordc			= document.getElementById('infos_passwordc_popup').value;

	
	document.getElementById('infos_connexion_edit_errors').innerHTML 	= '';
	
	if(login.length<3){
		document.getElementById('infos_connexion_edit_errors').innerHTML 	+= '<br />';
		document.getElementById('infos_connexion_edit_errors').innerHTML	+= '- Le <b>Login</b> est obligatoire';
		etat 	= 0;
	}else if(login.match(/^\s*$/)){
		document.getElementById('infos_connexion_edit_errors').innerHTML 	+= '<br />';
		document.getElementById('infos_connexion_edit_errors').innerHTML	+= '- Le <b>Login</b> est obligatoire';
		etat 	= 0;
	}
	
	if(password.length<3){
		document.getElementById('infos_connexion_edit_errors').innerHTML 	+= '<br />';
		document.getElementById('infos_connexion_edit_errors').innerHTML	+= '- Le <b>Mot de passe</b> est obligatoire';
		etat 	= 0;
	}else if(password.match(/^\s*$/)){
		document.getElementById('infos_connexion_edit_errors').innerHTML 	+= '<br />';
		document.getElementById('infos_connexion_edit_errors').innerHTML	+= '- Le <b>Mot de passe</b> est obligatoire';
		etat 	= 0;
	}
	
	if(password!=passwordc){
		document.getElementById('infos_connexion_edit_errors').innerHTML 	+= '<br />';
		document.getElementById('infos_connexion_edit_errors').innerHTML	+= '- Erreur confirmation du mot de passe';
		etat 	= 0;
	}
	
	
	if(etat==0){
		//Scroll to the Bottom of the Popup
		$('#infos-dialog-connexion-edit-popup').animate({ scrollTop: $(document).height() }, 1000);
	}else if(etat==1){
	
		var formData = new FormData();
		formData.append("login", login);
		formData.append("password", password);
		
	
		$.ajax({
			url: 'extranet_v3_infos_load_connexion_edit_confirm.php',  //Server script to process data
			type: 'POST',
			xhr: function() {  // Custom XMLHttpRequest
				var myXhr = $.ajaxSettings.xhr();
				if(myXhr.upload){ // Check if upload property exists
					//myXhr.upload.addEventListener('pictureprogress',picture_progressHandlingFunction, false); // For handling the progress of the upload
				}
				return myXhr;
			},
			//Ajax events
			beforeSend: function() {
					document.getElementById('infos-dialog-connexion-edit-popup-loader').style.display	= 'block';
					document.getElementById('infos_connexion_edit_errors').innerHTML			= '';
				},
			success: function(data) {
				
					//1 	=> That is OK
					//-1 	=> Error session or connexion !
					//-2	=> Login already reserved
					//0		=> Error forms
					if(data=='1'){
						document.getElementById('infos-dialog-connexion-edit-response').innerHTML += '<font color="green">Informations modifi&eacute;es avec succ&egrave;s</font>';
						
						//Close the Popup
						$( "#infos-dialog-connexion-edit-popup" ).dialog( "close" );
					
						//Reload the connexion content
						infos_connexion_load();
						
					}else if(data=='-2'){
						document.getElementById('infos_connexion_edit_errors').innerHTML = 'Login non disponible !';
					}else if(data=='-1'){
						//document.getElementById('infos-dialog-connexion-edit-response').innerHTML += 'Vous devez vous reconnecter !';
						window.document='login.html';
					}else{
						document.getElementById('infos_connexion_edit_errors').innerHTML = 'Erreur, merci de r\351essayer !';
						
						//Scroll to the Bottom of the Popup
						$('#infos-dialog-connexion-edit-popup').animate({ scrollTop: $(document).height() }, 1000);
						
						//$("html, body").animate({ scrollTop: $(document).height() }, 1000);
						
					}//end else
					
					document.getElementById('infos-dialog-connexion-edit-popup-loader').style.display	= 'none';
			
				},
			error: function() {
					document.getElementById('infos-dialog-connexion-edit-popup-loader').style.display	= 'none';
					//errorHandler,
					document.getElementById('infos-dialog-connexion-edit-response').innerHTML	= 'Erreur, merci de r&eacute;essayer !';
				},
			// Form data
			data: formData,
			//Options to tell jQuery not to process data or worry about content-type.
			cache: false,
			contentType: false,
			processData: false
		});
	
	}//end else 
	
}


/*****************************************************************/
/******************* 		Contacts ADD	**********************/
/*****************************************************************/
function infos_contacts_add_listner(){
	document.getElementById('infos-dialog-contacts-response').innerHTML	= '';
	$( "#infos-dialog-contacts-popup" ).dialog({
		resizable: false,
		height: 320,
		width: 350,
		modal: true,
		buttons: {
			"Confirmer": function() {
				infos_contacts_add_confirm();
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
	
	//Load Add Contact infos
	infos_contacts_load_add();
}


function infos_contacts_load_add(){
	$.ajax({
		url: 'extranet_v3_infos_load_contacts_add.php',  //Server script to process data
		type: 'POST',
		xhr: function() {  // Custom XMLHttpRequest
			var myXhr = $.ajaxSettings.xhr();
			if(myXhr.upload){ // Check if upload property exists
				//myXhr.upload.addEventListener('pictureprogress',picture_progressHandlingFunction, false); // For handling the progress of the upload
			}
			return myXhr;
		},
		//Ajax events
		beforeSend: function() {
				document.getElementById('infos-dialog-contacts-popup-loader').style.display	= 'block';
			},
		success: function(data) {
				
				document.getElementById('infos-dialog-contacts-popup-loader').style.display	= 'none';
				document.getElementById('infos-dialog-contacts-response').innerHTML	= data;				
			},
		error: function() {
				document.getElementById('infos-dialog-contacts-popup-loader').style.display	= 'none';
				//errorHandler,
				document.getElementById('infos-dialog-contacts-response').innerHTML	= 'Erreur, merci de r&eacute;essayer !';
			},
		// Form data
		data: '',
		//Options to tell jQuery not to process data or worry about content-type.
		cache: false,
		contentType: false,
		processData: false
	});
}

function infos_contacts_add_confirm(){
	var etat 				= 1;
	var prenom				= document.getElementById('infos_prenom_popup').value;
	var nom					= document.getElementById('infos_nom_popup').value;
	var email				= document.getElementById('infos_contact_email_popup').value;

	document.getElementById('infos_contacts_errors').innerHTML 	= '';
	
	if(prenom.length<3){
		document.getElementById('infos_contacts_errors').innerHTML 	+= '<br />';
		document.getElementById('infos_contacts_errors').innerHTML	+= '- Le <b>Pr&eacute;nom</b> est obligatoire';
		etat 	= 0;
	}else if(prenom.match(/^\s*$/)){
		document.getElementById('infos_contacts_errors').innerHTML 	+= '<br />';
		document.getElementById('infos_contacts_errors').innerHTML	+= '- Le <b>Pr&eacute;nom</b> est obligatoire';
		etat 	= 0;
	}
	
	if(nom.length<3){
		document.getElementById('infos_contacts_errors').innerHTML 	+= '<br />';
		document.getElementById('infos_contacts_errors').innerHTML	+= '- Le <b>Nom</b> est obligatoire';
		etat 	= 0;
	}else if(nom.match(/^\s*$/)){
		document.getElementById('infos_contacts_errors').innerHTML 	+= '<br />';
		document.getElementById('infos_contacts_errors').innerHTML	+= '- Le <b>Nom</b> est obligatoire';
		etat 	= 0;
	}

	if(!isValidEmailAddress(email)){
		document.getElementById('infos_contacts_errors').innerHTML 	+= '<br />';
		document.getElementById('infos_contacts_errors').innerHTML	+= '- L\'<b>Email</b> est obligatoire';
		etat 	= 0;
	}
	
	
	if(etat==0){
		//Scroll to the Bottom of the Popup
		$('#infos-dialog-contacts-popup').animate({ scrollTop: $(document).height() }, 1000);
	}else if(etat==1){
	
		var formData = new FormData();
		formData.append("prenom", prenom);
		formData.append("nom", nom);
		formData.append("email", email);
		
		$.ajax({
			url: 'extranet_v3_infos_load_contacts_add_confirm.php',  //Server script to process data
			type: 'POST',
			xhr: function() {  // Custom XMLHttpRequest
				var myXhr = $.ajaxSettings.xhr();
				if(myXhr.upload){ // Check if upload property exists
					//myXhr.upload.addEventListener('pictureprogress',picture_progressHandlingFunction, false); // For handling the progress of the upload
				}
				return myXhr;
			},
			//Ajax events
			beforeSend: function() {
					document.getElementById('infos-dialog-contacts-popup-loader').style.display	= 'block';
					document.getElementById('infos_contacts_errors').innerHTML			= '';
				},
			success: function(data) {
				
					//1 	=> That is OK
					//-1 	=> Error session or connexion !
					//0		=> Error forms
					if(data=='1'){
						document.getElementById('infos-dialog-contacts-response').innerHTML += '<font color="green">Contact ajout&eacute; avec succ&egrave;s</font>';
						
						//Close the Popup
						$( "#infos-dialog-contacts-popup" ).dialog( "close" );
					
						//Reload the connexion content
						infos_contacts_load();
						
					}else if(data=='-2'){
						document.getElementById('infos_contacts_errors').innerHTML = 'Cet Email existe d&eacute;j&agrave; !';
					}else if(data=='-1'){
						//document.getElementById('infos-dialog-connexion-edit-response').innerHTML += 'Vous devez vous reconnecter !';
						window.document='login.html';
					}else{
						document.getElementById('infos_contacts_errors').innerHTML = 'Erreur, merci de r\351essayer !';
						
						//Scroll to the Bottom of the Popup
						$('#infos-dialog-contacts-popup').animate({ scrollTop: $(document).height() }, 1000);
						
						//$("html, body").animate({ scrollTop: $(document).height() }, 1000);
						
					}//end else
					
					document.getElementById('infos-dialog-contacts-popup-loader').style.display	= 'none';
			
				},
			error: function() {
					document.getElementById('infos-dialog-contacts-popup-loader').style.display	= 'none';
					//errorHandler,
					document.getElementById('infos-dialog-contacts-response').innerHTML	= 'Erreur, merci de r&eacute;essayer !';
				},
			// Form data
			data: formData,
			//Options to tell jQuery not to process data or worry about content-type.
			cache: false,
			contentType: false,
			processData: false
		});
	
	}//end else if etat
	
}


/*****************************************************************/
/******************* 		Contacts EDIT	**********************/
/*****************************************************************/

function infos_contacts_edit_listner(id){
	document.getElementById('infos-dialog-contacts-response').innerHTML	= '';
	$( "#infos-dialog-contacts-popup" ).dialog({
		resizable: false,
		height: 320,
		width: 350,
		modal: true,
		buttons: {
			"Confirmer": function() {
				infos_contacts_edit_confirm(id);
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
	
	//Load Editable contact infos
	infos_contacts_load_edit(id);
}


function infos_contacts_load_edit(id){

	var formData = new FormData();
	formData.append("id", id);
	
	$.ajax({
		url: 'extranet_v3_infos_load_contacts_edit.php',  //Server script to process data
		type: 'POST',
		xhr: function() {  // Custom XMLHttpRequest
			var myXhr = $.ajaxSettings.xhr();
			if(myXhr.upload){ // Check if upload property exists
				//myXhr.upload.addEventListener('pictureprogress',picture_progressHandlingFunction, false); // For handling the progress of the upload
			}
			return myXhr;
		},
		//Ajax events
		beforeSend: function() {
				document.getElementById('infos-dialog-contacts-popup-loader').style.display	= 'block';
			},
		success: function(data) {
		
				document.getElementById('infos-dialog-contacts-popup-loader').style.display	= 'none';
				if(data==0){
					document.getElementById('infos_contacts_errors').innerHTML = 'Erreur, merci de r\351essayer !';
				}else{
					document.getElementById('infos-dialog-contacts-response').innerHTML	= data;
				}
					
			},
		error: function() {
				document.getElementById('infos-dialog-contacts-popup-loader').style.display	= 'none';
				//errorHandler,
				document.getElementById('infos-dialog-contacts-response').innerHTML	= 'Erreur, merci de r&eacute;essayer !';
			},
		// Form data
		data: formData,
		//Options to tell jQuery not to process data or worry about content-type.
		cache: false,
		contentType: false,
		processData: false
	});
}


function infos_contacts_edit_confirm(id){
	var etat 				= 1;
	var prenom				= document.getElementById('infos_prenom_popup').value;
	var nom					= document.getElementById('infos_nom_popup').value;
	var email				= document.getElementById('infos_contact_email_popup').value;

	document.getElementById('infos_contacts_errors').innerHTML 	= '';
	
	if(prenom.length<3){
		document.getElementById('infos_contacts_errors').innerHTML 	+= '<br />';
		document.getElementById('infos_contacts_errors').innerHTML	+= '- Le <b>Pr&eacute;nom</b> est obligatoire';
		etat 	= 0;
	}else if(prenom.match(/^\s*$/)){
		document.getElementById('infos_contacts_errors').innerHTML 	+= '<br />';
		document.getElementById('infos_contacts_errors').innerHTML	+= '- Le <b>Pr&eacute;nom</b> est obligatoire';
		etat 	= 0;
	}
	
	if(nom.length<3){
		document.getElementById('infos_contacts_errors').innerHTML 	+= '<br />';
		document.getElementById('infos_contacts_errors').innerHTML	+= '- Le <b>Nom</b> est obligatoire';
		etat 	= 0;
	}else if(nom.match(/^\s*$/)){
		document.getElementById('infos_contacts_errors').innerHTML 	+= '<br />';
		document.getElementById('infos_contacts_errors').innerHTML	+= '- Le <b>Nom</b> est obligatoire';
		etat 	= 0;
	}

	if(!isValidEmailAddress(email)){
		document.getElementById('infos_contacts_errors').innerHTML 	+= '<br />';
		document.getElementById('infos_contacts_errors').innerHTML	+= '- L\'<b>Email</b> est obligatoire';
		etat 	= 0;
	}
	
	
	if(etat==0){
		//Scroll to the Bottom of the Popup
		$('#infos-dialog-contacts-popup').animate({ scrollTop: $(document).height() }, 1000);
	}else if(etat==1){
	
		var formData = new FormData();
		formData.append("id", id);
		formData.append("prenom", prenom);
		formData.append("nom", nom);
		formData.append("email", email);
		
		$.ajax({
			url: 'extranet_v3_infos_load_contacts_edit_confirm.php',  //Server script to process data
			type: 'POST',
			xhr: function() {  // Custom XMLHttpRequest
				var myXhr = $.ajaxSettings.xhr();
				if(myXhr.upload){ // Check if upload property exists
					//myXhr.upload.addEventListener('pictureprogress',picture_progressHandlingFunction, false); // For handling the progress of the upload
				}
				return myXhr;
			},
			//Ajax events
			beforeSend: function() {
					document.getElementById('infos-dialog-contacts-popup-loader').style.display	= 'block';
					document.getElementById('infos_contacts_errors').innerHTML			= '';
				},
			success: function(data) {
				
					//1 	=> That is OK
					//-1 	=> Error session or connexion !
					//0		=> Error forms
					if(data=='1'){
						document.getElementById('infos-dialog-contacts-response').innerHTML += '<font color="green">Contact ajout&eacute; avec succ&egrave;s</font>';
						
						//Close the Popup
						$( "#infos-dialog-contacts-popup" ).dialog( "close" );
					
						//Reload the connexion content
						infos_contacts_load();
						
					}else if(data=='-1'){
						//document.getElementById('infos-dialog-connexion-edit-response').innerHTML += 'Vous devez vous reconnecter !';
						window.document='login.html';
					}else{
						document.getElementById('infos_contacts_errors').innerHTML = 'Erreur, merci de r\351essayer !';
						
						//Scroll to the Bottom of the Popup
						$('#infos-dialog-contacts-popup').animate({ scrollTop: $(document).height() }, 1000);
						
						//$("html, body").animate({ scrollTop: $(document).height() }, 1000);
						
					}//end else
					
					document.getElementById('infos-dialog-contacts-popup-loader').style.display	= 'none';
			
				},
			error: function() {
					document.getElementById('infos-dialog-contacts-popup-loader').style.display	= 'none';
					//errorHandler,
					document.getElementById('infos-dialog-contacts-response').innerHTML	= 'Erreur, merci de r&eacute;essayer !';
				},
			// Form data
			data: formData,
			//Options to tell jQuery not to process data or worry about content-type.
			cache: false,
			contentType: false,
			processData: false
		});
	
	}//end else if etat
}


/*****************************************************************/
/******************* 		Contacts DELETE	**********************/
/*****************************************************************/

function infos_contacts_delete_listner(id){
	document.getElementById('infos-dialog-contacts-response').innerHTML	= '';
	$( "#infos-dialog-contacts-popup" ).dialog({
		resizable: false,
		height: 280,
		width: 320,
		modal: true,
		buttons: {
			"Confirmer": function() {
				infos_contacts_delete_confirm(id);
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
	
	//Load Delete contacts infos
	infos_contacts_load_delete();
}

function infos_contacts_load_delete(){

	document.getElementById('infos-dialog-contacts-popup-loader').style.display			= 'none';
	document.getElementById('infos-dialog-contacts-response').innerHTML	= '';
	
	document.getElementById('infos-dialog-contacts-response').innerHTML	= '<br />&Ecirc;tes vous s&ucirc;r de vouloir supprimer cette adresse email ?';
	
	document.getElementById('infos-dialog-contacts-response').innerHTML	+= '<div id="infos_contacts_errors" class="row"></div>';

}

function infos_contacts_delete_confirm(id){

	var formData = new FormData();
	formData.append("id", id);
	
	$.ajax({
		url: 'extranet_v3_infos_load_contacts_delete_confirm.php',  //Server script to process data
		type: 'POST',
		xhr: function() {  // Custom XMLHttpRequest
			var myXhr = $.ajaxSettings.xhr();
			if(myXhr.upload){ // Check if upload property exists
				//myXhr.upload.addEventListener('pictureprogress',picture_progressHandlingFunction, false); // For handling the progress of the upload
			}
			return myXhr;
		},
		//Ajax events
		beforeSend: function() {
				document.getElementById('infos-dialog-contacts-popup-loader').style.display	= 'block';
				document.getElementById('infos_contacts_errors').innerHTML			= '';
			},
		success: function(data) {
			
				//1 	=> That is OK
				//-1 	=> Error session or connexion !
				//0		=> Error forms
				if(data=='1'){
					document.getElementById('infos-dialog-contacts-response').innerHTML += '<font color="green">Contact supprim&eacute; avec succ&egrave;s</font>';
					
					//Close the Popup
					$( "#infos-dialog-contacts-popup" ).dialog( "close" );
				
					//Reload the connexion content
					infos_contacts_load();
					
				}else if(data=='-1'){
					//document.getElementById('infos-dialog-connexion-edit-response').innerHTML += 'Vous devez vous reconnecter !';
					window.document='login.html';
				}else{
					document.getElementById('infos_contacts_errors').innerHTML = 'Erreur, merci de r\351essayer !';
					
					//Scroll to the Bottom of the Popup
					$('#infos-dialog-contacts-popup').animate({ scrollTop: $(document).height() }, 1000);
					
					//$("html, body").animate({ scrollTop: $(document).height() }, 1000);
					
				}//end else
				
				document.getElementById('infos-dialog-contacts-popup-loader').style.display	= 'none';
		
			},
		error: function() {
				document.getElementById('infos-dialog-contacts-popup-loader').style.display	= 'none';
				//errorHandler,
				document.getElementById('infos-dialog-contacts-response').innerHTML	= 'Erreur, merci de r&eacute;essayer !';
			},
		// Form data
		data: formData,
		//Options to tell jQuery not to process data or worry about content-type.
		cache: false,
		contentType: false,
		processData: false
	});

}