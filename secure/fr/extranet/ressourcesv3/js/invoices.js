function invoices_lunch_search(){
	document.getElementById('panel-table').style.opacity = '0.2';
	document.getElementById('loader_panel-table').style.display = 'block';
				
	//Page start
	var f_ps						= document.getElementById('fps').value;
	//Page pagination
	var f_pp 						= document.getElementById('fpp').value;
	
	
	document.getElementById('panel-table').innerHTML = '&nbsp;';
	
	var OAjax;
	
	if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
	else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

	OAjax.open('POST','/fr/extranet/extranet-v3-invoices-load.php',true);
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
				
				document.getElementById('panel-table').innerHTML+=''+OAjax.responseText+'';
				setTimeout(invoices_table_visible, 200);
			}
		}
		
	OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	OAjax.send('f_ps='+f_ps+'&f_pp='+f_pp);
}


function invoices_table_visible(){
	document.getElementById('panel-table').style.opacity = '1';
}

function invoices_get_more_now(){
	var personnalised_pp	=	document.getElementById('f_select_p').options[document.getElementById('f_select_p').selectedIndex].value;
	document.getElementById('fpp').value	= personnalised_pp;

	invoices_lunch_search();
}

function invoices_load_other_page(number){
	document.getElementById('fps').value = number;
	invoices_lunch_search();
}

function invoices_reset_pagination_parameter(){
	document.getElementById('fps').value = '1';
	document.getElementById('fpp').value = '10';
}