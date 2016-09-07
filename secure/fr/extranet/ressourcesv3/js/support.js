function support_send_msg(){
	document.getElementById('support_erros').innerHTML = '';
	var etat			= 1;
	var support_text	= document.getElementById('support_text').value;
	var service			= document.getElementById('support_select').options[document.getElementById('support_select').selectedIndex].value;
	
	if(support_text.length<10){
		document.getElementById('support_erros').innerHTML 	+= '<br />';
		document.getElementById('support_erros').innerHTML	+= '- Le <b>message</b> est obligatoire';
		etat 	= 0;
	}else{
		if(support_text.match(/^\s*$/)){
			document.getElementById('support_erros').innerHTML 	+= '<br />';
			document.getElementById('support_erros').innerHTML	+= '- Le <b>message</b> est obligatoire';
			etat 	= 0;
		}
	}
	
	
	if(etat==1){
	
		var formData = new FormData();
		formData.append("service", service);
		formData.append("support_text", support_text);
		
		$.ajax({
			url: 'extranet_v3_support_send.php',  //Server script to process data
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
				document.getElementById('loader_panel-table').style.display	= 'block';
				document.getElementById('support_erros').innerHTML = '';
			},
			success: function(data) {
				
					document.getElementById('loader_panel-table').style.display	= 'none';
					
					if(data=='1'){
						document.getElementById('support_text').value = '';
						document.getElementById('support_erros').innerHTML = '<font color="green">Merci pour votre message. Nous allons commencer &agrave; traiter votre demande.</font>';
					}else if(data=='-1'){
						window.document='login.html';
					}else{
						document.getElementById('support_erros').innerHTML = 'Erreur, merci de r&eacute;essayer !';
					}
					
				},
			error: function() {
					//errorHandler,
					document.getElementById('loader_panel-table').style.display	= 'none';
					document.getElementById('support_erros').innerHTML = 'Erreur, merci de r&eacute;essayer !';
				},
			// Form data
			data: formData,
			//Options to tell jQuery not to process data or worry about content-type.
			cache: false,
			contentType: false,
			processData: false
		});
	
	}//end if etat
}