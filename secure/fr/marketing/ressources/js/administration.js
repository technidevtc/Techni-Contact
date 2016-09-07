function load_administration_users(){
	document.getElementById('panel-table').style.opacity = '0.2';
	document.getElementById('loader_panel-table').style.display = 'block';
				
	//Page start
	var f_ps				= document.getElementById('fps').value;
	//Page pagination
	var f_pp 				= document.getElementById('fpp').value;

	
	document.getElementById('panel-table').innerHTML = '&nbsp;';

	var OAjax;
	
	if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
	else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

	OAjax.open('POST','/fr/marketing/administration-users-load.php',true);
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
					setTimeout(administration_table_visible, 200);	

				//}else{
					//mmf_hide_autocomplete();
				//}
			}
		}
		
	OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	OAjax.send('f_ps='+f_ps+'&f_pp='+f_pp);
		
}

function administration_table_visible(){
	document.getElementById('panel-table').style.opacity = '1';
	//alert('finish');
}

function users_get_more_now(){
	var personnalised_pp	=	document.getElementById('f_select_p').options[document.getElementById('f_select_p').selectedIndex].value;
	document.getElementById('fpp').value	= personnalised_pp;
	
	load_administration_users();
}

function users_load_other_page(number){
	document.getElementById('fps').value = number;
	
	load_administration_users();
}

function users_add_call(){
	var name					= document.getElementById('user_name').value;
	var login					= document.getElementById('user_login').value;
	var password				= document.getElementById('user_password').value;
	var cpassword				= document.getElementById('user_cpassword').value;
	var	description				= document.getElementById('user_description').value;
	var active					= '';
	
	var access_pages			= '';
	var access_tables_segment	= '';
	var access_tables_export	= '';
	
	
	var etat		= true;
	document.getElementById('users_form_validation_error').innerHTML	= '';
	
	if(name.length<3){
		document.getElementById('users_form_validation_error').innerHTML 	+= '<br />';
		document.getElementById('users_form_validation_error').innerHTML	+= '- Le <b>Nom d\'utilisateur</b> est obligatoire';
		etat 	= false;
	}else if(name.match(/^\s*$/)){
		document.getElementById('users_form_validation_error').innerHTML 	+= '<br />';
		document.getElementById('users_form_validation_error').innerHTML	+= '- Le <b>Nom d\'utilisateur</b> est obligatoire';
		etat 	= false;
	}
	
	if(login.length<3){
		document.getElementById('users_form_validation_error').innerHTML 	+= '<br />';
		document.getElementById('users_form_validation_error').innerHTML	+= '- Le <b>login</b> est obligatoire';
		etat 	= false;
	}else if(login.match(/^\s*$/)){
		document.getElementById('users_form_validation_error').innerHTML 	+= '<br />';
		document.getElementById('users_form_validation_error').innerHTML	+= '- Le <b>login</b> est obligatoire';
		etat 	= false;
	}
	
	if(document.getElementById('user_active_yes').checked==true){
		active = 'yes';
	}else{
		active = 'no';
	}
	
	if(etat){
		var OAjax;
		if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
		else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

		OAjax.open('POST','/fr/marketing/administration-users-check-login.php',true);
		OAjax.onreadystatechange = function(){
			// OAjax.readyState == 1   ==>  connexion ?tablie
			// OAjax.readyState == 2   ==>  requete recue
			// OAjax.readyState == 3   ==>  reponse en cours
			
			
				if ((OAjax.readyState == 1) || (OAjax.readyState == 2) || (OAjax.readyState == 3) ){
					//document.getElementById('panel-table').style.opacity = '0.2';
					//document.getElementById('loader_panel-table').style.display = 'block';
				}
				
				if (OAjax.readyState == 4 && OAjax.status==200){
					
					if(OAjax.responseText=='1'){
						//the login is available					
						
					}else{
						etat 	= false;
					}
				}else{
					etat 	= false;
				}
			}
		OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
		OAjax.send('login='+login);
	}//end check login
	
	if(password.length<3){
		document.getElementById('users_form_validation_error').innerHTML 	+= '<br />';
		document.getElementById('users_form_validation_error').innerHTML	+= '- Le <b>mot de passe</b> est obligatoire';
		etat 	= false;
	}else if(password.match(/^\s*$/)){
		document.getElementById('users_form_validation_error').innerHTML 	+= '<br />';
		document.getElementById('users_form_validation_error').innerHTML	+= '- Le <b>mot de passe</b> est obligatoire';
		etat 	= false;
	}
	
	if(password!=cpassword){
		document.getElementById('users_form_validation_error').innerHTML 	+= '<br />';
		document.getElementById('users_form_validation_error').innerHTML	+= '- Vous devez confirmer le mot de passe';
		etat 	= false;
	}
	
	
	if(etat){
		//Preparing elements for the access
		var local_loop	= 1;
		//Loop for all the elements role access
		while(local_loop!=document.getElementById('access_pages_count').value){
			if(document.getElementById('user_access_page_'+local_loop)){
				if(document.getElementById('user_access_page_'+local_loop).checked==true){
					access_pages +='#'+document.getElementById('user_access_page_'+local_loop).value+'#';
				}
			}
			local_loop++;
		}//end while
		
		//Test if the user checked the "segment creation" (id=2), "segment - edit" (id=6) 
		//or "segment - delete" (id=5)
		//And he did not checked "my segments" we have to grant it !
		if( access_pages.indexOf('#2#')!=-1 || access_pages.indexOf('#6#')!=-1 || access_pages.indexOf('#5#')!=-1){
			access_pages +='#1#';
		}
		
		
		//Preparing elements for the tables segment & Export 
		var local_loop	= 1;
		//Loop for all the elements role access
		while(local_loop!=document.getElementById('access_tables_count').value){
		
			//For the Segment
			if(document.getElementById('user_access_table_segment'+local_loop)){
				if(document.getElementById('user_access_table_segment'+local_loop).checked==true){
					access_tables_segment +='#'+document.getElementById('user_access_table_segment'+local_loop).value+'#';
				}
			}
			
			//For the Export
			if(document.getElementById('user_access_table_export'+local_loop)){
				if(document.getElementById('user_access_table_export'+local_loop).checked==true){
					access_tables_export +='#'+document.getElementById('user_access_table_export'+local_loop).value+'#';
				}
			}
			
			local_loop++;
		}//end while
		
		
		//Sending to the final page of creation
		var OAjax;
		if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
		else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

		OAjax.open('POST','/fr/marketing/administration-users-create-confirm.php',true);
		OAjax.onreadystatechange = function(){
			// OAjax.readyState == 1   ==>  connexion ?tablie
			// OAjax.readyState == 2   ==>  requete recue
			// OAjax.readyState == 3   ==>  reponse en cours
			
			
				if ((OAjax.readyState == 1) || (OAjax.readyState == 2) || (OAjax.readyState == 3) ){
					//document.getElementById('panel-table').style.opacity = '0.2';
					//document.getElementById('loader_panel-table').style.display = 'block';
				}
				
				if (OAjax.readyState == 4 && OAjax.status==200){
					
					if(OAjax.responseText=='1'){
						//the user is created redirect page !					
						document.getElementById('users_form_validation_error').innerHTML 	+= '<br />';
						document.getElementById('users_form_validation_error').innerHTML	+= '<font color="green">Utilisateur cr&eacute;e avec succ&egrave;s</font>';
						
						//Redirect after 5seconds
							//window.setInterval(5000, document.location='/fr/marketing/administration.php');
							setTimeout(function(){ document.location='/fr/marketing/administration.php'; }, 2000);
						
					}else{
						//Error and show it in the block !
						document.getElementById('users_form_validation_error').innerHTML 	+= '<br />';
						document.getElementById('users_form_validation_error').innerHTML	+= '- Erreur : '+OAjax.responseText;
					}
				}else{
					//document.getElementById('users_form_validation_error').innerHTML 	+= '<br />';
					//document.getElementById('users_form_validation_error').innerHTML	+= '- Erreur lors de la cr&eacute;ation de l\'utilisateur';
				}
			}
		OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
		OAjax.send('name='+name+'&login='+login+'&password='+password+'&access_pages='+access_pages+'&access_tables_segment='+access_tables_segment+'&access_tables_export='+access_tables_export+'&description='+description+'&active='+active);
		
	
	}//end if check etat
	
}


function users_edit_call(){
	var id						= document.getElementById('user_id').value;
	var name					= document.getElementById('user_name').value;
	var password				= document.getElementById('user_password').value;
	var cpassword				= document.getElementById('user_cpassword').value;
	var	description				= document.getElementById('user_description').value;
	var active					= '';
	
	var access_pages			= '';
	var access_tables_segment	= '';
	var access_tables_export	= '';
	
	
	var etat		= true;
	document.getElementById('users_form_validation_error').innerHTML	= '';
	
	if(name.length<3){
		document.getElementById('users_form_validation_error').innerHTML 	+= '<br />';
		document.getElementById('users_form_validation_error').innerHTML	+= '- Le <b>Nom d\'utilisateur</b> est obligatoire';
		etat 	= false;
	}else if(name.match(/^\s*$/)){
		document.getElementById('users_form_validation_error').innerHTML 	+= '<br />';
		document.getElementById('users_form_validation_error').innerHTML	+= '- Le <b>Nom d\'utilisateur</b> est obligatoire';
		etat 	= false;
	}
	
	
	if(document.getElementById('user_active_yes').checked==true){
		active = 'yes';
	}else{
		active = 'no';
	}
	
	
	
	if(password.length==0){
		//We do not change the password
	}else if(password.length<3){
		document.getElementById('users_form_validation_error').innerHTML 	+= '<br />';
		document.getElementById('users_form_validation_error').innerHTML	+= '- Le <b>mot de passe</b> est obligatoire';
		etat 	= false;
	}else if(password.match(/^\s*$/)){
		document.getElementById('users_form_validation_error').innerHTML 	+= '<br />';
		document.getElementById('users_form_validation_error').innerHTML	+= '- Le <b>mot de passe</b> est obligatoire';
		etat 	= false;
	}
	
	if(password!=cpassword){
		document.getElementById('users_form_validation_error').innerHTML 	+= '<br />';
		document.getElementById('users_form_validation_error').innerHTML	+= '- Vous devez confirmer le mot de passe';
		etat 	= false;
	}
	
	
	if(etat){
		//Preparing elements for the access
		var local_loop	= 1;
		//Loop for all the elements role access
		while(local_loop!=document.getElementById('access_pages_count').value){
			if(document.getElementById('user_access_page_'+local_loop)){
				if(document.getElementById('user_access_page_'+local_loop).checked==true){
					access_pages +='#'+document.getElementById('user_access_page_'+local_loop).value+'#';
				}
			}
			local_loop++;
		}//end while
		
		//**** ESP Platform !
		
		//Test if the user checked the "segment creation" (id=2), "segment - edit" (id=6) 
		//or "segment - delete" (id=5)
		//And he did not checked "my segments" we have to grant it !
		if( access_pages.indexOf('#2#')!=-1 || access_pages.indexOf('#6#')!=-1 || access_pages.indexOf('#5#')!=-1){
			access_pages +='#1#';
		}
		
		//Test if the user checked the "message creation" (id=9), "message - edit" (id=10) 
		//or "message - delete" (id=11)
		//And he did not checked "my messages" we have to grant it !
		if( access_pages.indexOf('#9#')!=-1 || access_pages.indexOf('#10#')!=-1 || access_pages.indexOf('#11#')!=-1){
			access_pages +='#8#';
		}
		
		//Test if the user checked the "campaign creation" (id=13), "campaign - edit" (id=14) 
		//or "campaign - delete" (id=15)
		//And he did not checked "my campaigns" we have to grant it !
		if( access_pages.indexOf('#13#')!=-1 || access_pages.indexOf('#14#')!=-1 || access_pages.indexOf('#15#')!=-1){
			access_pages +='#12#';
		}
		
		//Test if the user checked the "base email - edit" (id=17)
		//And he did not checked "my campaigns" we have to grant it !
		if( access_pages.indexOf('#17#')!=-1 ){
			access_pages +='#16#';
		}
		
		//Preparing elements for the tables segment & Export 
		var local_loop	= 1;
		//Loop for all the elements role access
		while(local_loop!=document.getElementById('access_tables_count').value){
		
			//For the Segment
			if(document.getElementById('user_access_table_segment'+local_loop)){
				if(document.getElementById('user_access_table_segment'+local_loop).checked==true){
					access_tables_segment +='#'+document.getElementById('user_access_table_segment'+local_loop).value+'#';
				}
			}
			
			//For the Export
			if(document.getElementById('user_access_table_export'+local_loop)){
				if(document.getElementById('user_access_table_export'+local_loop).checked==true){
					access_tables_export +='#'+document.getElementById('user_access_table_export'+local_loop).value+'#';
				}
			}
			
			local_loop++;
		}//end while
		
		
		//Sending to the final page of creation
		var OAjax;
		if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
		else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

		OAjax.open('POST','/fr/marketing/administration-users-edit-confirm.php',true);
		OAjax.onreadystatechange = function(){
			// OAjax.readyState == 1   ==>  connexion ?tablie
			// OAjax.readyState == 2   ==>  requete recue
			// OAjax.readyState == 3   ==>  reponse en cours
			
			
				if ((OAjax.readyState == 1) || (OAjax.readyState == 2) || (OAjax.readyState == 3) ){
					//document.getElementById('panel-table').style.opacity = '0.2';
					//document.getElementById('loader_panel-table').style.display = 'block';
				}
				
				if (OAjax.readyState == 4 && OAjax.status==200){
					
					if(OAjax.responseText=='1'){
						//the user is created redirect page !					
						document.getElementById('users_form_validation_error').innerHTML 	+= '<br />';
						document.getElementById('users_form_validation_error').innerHTML	+= '<font color="green">Utilisateur modifi&eacute; avec succ&egrave;s</font>';
						
						//Redirect after 5seconds
							//window.setInterval(5000, document.location='/fr/marketing/administration.php');
							setTimeout(function(){ document.location='/fr/marketing/administration.php'; }, 2000);
						
					}else{
						//Error and show it in the block !
						document.getElementById('users_form_validation_error').innerHTML 	+= '<br />';
						document.getElementById('users_form_validation_error').innerHTML	+= '- Erreur : '+OAjax.responseText;
					}
				}else{
					//document.getElementById('users_form_validation_error').innerHTML 	+= '<br />';
					//document.getElementById('users_form_validation_error').innerHTML	+= '- Erreur lors de la cr&eacute;ation de l\'utilisateur';
				}
			}
		OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
		OAjax.send('id='+id+'&name='+name+'&password='+password+'&access_pages='+access_pages+'&access_tables_segment='+access_tables_segment+'&access_tables_export='+access_tables_export+'&description='+description+'&active='+active);
		
	
	}//end if check etat
}


//Ask for delete user
function users_ask_delete(){
	var user_id = document.getElementById('user_id').value;
	if(confirm('\352tes vous s\373r de vouloir supprimer cet utilisateur ?')){
		users_confirm_delete(user_id);
	}
}

function users_confirm_delete(user_id){
	//Sending to the final page of creation
	var OAjax;
	if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
	else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

	OAjax.open('POST','/fr/marketing/administration-users-delete-confirm.php',true);
	OAjax.onreadystatechange = function(){
		// OAjax.readyState == 1   ==>  connexion ?tablie
		// OAjax.readyState == 2   ==>  requete recue
		// OAjax.readyState == 3   ==>  reponse en cours
		
		
			if ((OAjax.readyState == 1) || (OAjax.readyState == 2) || (OAjax.readyState == 3) ){
				//document.getElementById('panel-table').style.opacity = '0.2';
				//document.getElementById('loader_panel-table').style.display = 'block';
			}
			
			if (OAjax.readyState == 4 && OAjax.status==200){
				
				if(OAjax.responseText=='1'){
					//the user is created redirect page !					
					document.getElementById('users_form_validation_error').innerHTML 	+= '<br />';
					document.getElementById('users_form_validation_error').innerHTML	+= '<font color="green">Utilisateur modifi&eacute; avec succ&egrave;s</font>';
					
					//Redirect after 5seconds
						//window.setInterval(5000, document.location='/fr/marketing/administration.php');
						setTimeout(function(){ document.location='/fr/marketing/administration.php'; }, 2000);
					
				}else{
					//Error and show it in the block !
					document.getElementById('users_form_validation_error').innerHTML 	+= '<br />';
					document.getElementById('users_form_validation_error').innerHTML	+= '- Erreur : '+OAjax.responseText;
				}
			}else{
				//document.getElementById('users_form_validation_error').innerHTML 	+= '<br />';
				//document.getElementById('users_form_validation_error').innerHTML	+= '- Erreur ';
			}
		}
	OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	OAjax.send('id='+user_id);
	
}