//Function to generat stats from a interval
function stats_lunch_generation_type_interval(){

	//Looking for the selected value
	var stats_interval_v1	=	document.getElementById('stats_interval_v1').options[document.getElementById('stats_interval_v1').selectedIndex].value;
	
	var stats_interval_v2	=	document.getElementById('stats_interval_v2').options[document.getElementById('stats_interval_v2').selectedIndex].value;
	
	if(typeof FormData == "undefined"){

		//var formData = $('#stats_form_id').serialize();
		var formData = 'stats_interval_v1='+stats_interval_v1;
		formData += '&stats_interval_v2='+stats_interval_v2;
		//alert(formData);

	}else{
		var formData = new FormData($('form')[0]);
		formData.append("stats_interval_v1", stats_interval_v1);
		formData.append("stats_interval_v2", stats_interval_v2);
	}
	
	if(stats_interval_v2<stats_interval_v1){
		alert('La date de d\351but doit \350tre inf\351rieur \340 la date de fin !');
	}else if(stats_interval_v1!='' && stats_interval_v2!=''){
		$.ajax({
			url: 'extranet_v3_stats_load_from_interval.php',  //Server script to process data
			type: 'POST',
			xhr: function() {  // Custom XMLHttpRequest
				var myXhr = $.ajaxSettings.xhr();
				if(myXhr.upload){
					// Check if upload property exists
					//myXhr.upload.addEventListener('pictureprogress',picture_progressHandlingFunction, false); // For handling the progress of the upload
				}
				return myXhr;
			},
			//Ajax events
			beforeSend: function() {
					document.getElementById('loader_panel-table').style.display	= 'block';
				},
			success: function(data) {
				
					document.getElementById('loader_panel-table').style.display	= 'none';
					
					var html_result_container		= document.getElementById('stats_load_results');
					html_result_container.innerHTML	= data;
					eval(html_result_container.innerHTML);
					//jQuery.globalEval(document.getElementById('stats_load_results'));
				},
			error: function() {
					//errorHandler,
					alert('Erreur, merci de r\351essayer !');
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

function stats_lunch_generation_type_simple(){
//Looking for the selected value
	var stats_simple_v1	=	document.getElementById('stats_simple_v1').options[document.getElementById('stats_simple_v1').selectedIndex].value;
	
	if(typeof FormData == "undefined"){
		var formData = 'stats_simple_v1='+stats_simple_v1;

	}else{
		var formData = new FormData();
		formData.append("stats_simple_v1", stats_simple_v1);
	}
	
	if(stats_simple_v1==''){
		alert('Le mois s\351lectionn\351 ne doit pas \350tre vide !');
	}else if(stats_simple_v1!=''){
		$.ajax({
			url: 'extranet_v3_stats_load_from_month.php',  //Server script to process data
			type: 'POST',
			xhr: function() {  // Custom XMLHttpRequest
				var myXhr = $.ajaxSettings.xhr();
				if(myXhr.upload){
					// Check if upload property exists
					//myXhr.upload.addEventListener('pictureprogress',picture_progressHandlingFunction, false); // For handling the progress of the upload
				}
				return myXhr;
			},
			//Ajax events
			beforeSend: function() {
					document.getElementById('loader_panel-table').style.display	= 'block';
				},
			success: function(data) {
				
					document.getElementById('loader_panel-table').style.display	= 'none';
					
					var html_result_container		= document.getElementById('stats_load_results');
					html_result_container.innerHTML	= data;
					eval(document.getElementById('stats_load_results').innerHTML);
					//jQuery.globalEval(document.getElementById('stats_load_results'));
				},
			error: function() {
					//errorHandler,
					alert('Erreur, merci de r\351essayer !');
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

function stats_global_export(){
	
	var stats_h_month	= document.getElementById('stats_h_month').value;
	var stats_h_data	= document.getElementById('stats_h_data').value;
	var stats_h_values	= document.getElementById('stats_h_values').value;
	
	if(stats_h_data!='' && stats_h_values!=''){
		
		document.getElementById('stats_form_export').submit();
		
	}else{
		alert('Aucune information \340 exporter !');
	}
}


/*****************************************************************/
/************************ Start Stats By Products ****************/
/*****************************************************************/

//Type "simple" ou "interval"
function stats_lunch_search_by_product(type){
	
	document.getElementById('stat_searchtype').value = type;
	
	var f_ps							= document.getElementById('fps').value;
	//Page pagination
	var f_pp 							= document.getElementById('fpp').value;
	//hidden if (if a user filter the results)
	var stats_products_search_hidden	= document.getElementById('stats_products_search_hidden').value;
	
	var stats_interval_v1				= document.getElementById('stats_interval_v1').options[document.getElementById('stats_interval_v1').selectedIndex].value;
	var stats_interval_v2				= document.getElementById('stats_interval_v2').options[document.getElementById('stats_interval_v2').selectedIndex].value;
	var stats_simple_v1					= document.getElementById('stats_simple_v1').options[document.getElementById('stats_simple_v1').selectedIndex].value;
	
	if(typeof FormData == "undefined"){
		var formData = 'f_ps='+f_ps;
		formData += '&f_pp='+f_pp;
		formData += '&stats_products_search_hidden='+stats_products_search_hidden;
		
		formData += '&type='+type;
		formData += '&stats_interval_v1='+stats_interval_v1;
		formData += '&stats_interval_v2='+stats_interval_v2;
		formData += '&stats_simple_v1='+stats_simple_v1;
		
	}else{
		var formData = new FormData();
		formData.append("f_ps", f_ps);
		formData.append("f_pp", f_pp);
		formData.append("stats_products_search_hidden", stats_products_search_hidden);
		
		//Type simple or interval and the value of the selectedIndex
		formData.append("type", type);
		formData.append("stats_interval_v1", stats_interval_v1);
		formData.append("stats_interval_v2", stats_interval_v2);
		formData.append("stats_simple_v1", stats_simple_v1);
	}
	
	//alert(formData);
	
	$.ajax({
		url: 'extranet_v3_stats_load_products.php',  //Server script to process data
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
				document.getElementById('panel-table').style.opacity = '0.2';
			},
		success: function(data) {
				
				document.getElementById('loader_panel-table').style.display	= 'none';
				
				document.getElementById('panel-table').innerHTML	= '';
				document.getElementById('panel-table').innerHTML	= data;
				
				setTimeout(stats_table_visible, 200);
				
			},
		error: function() {
				document.getElementById('loader_panel-table').style.display	= 'none';
				//errorHandler,
				document.getElementById('loader_panel-table').innerHTML	+= 'Erreur, merci de r&eacute;essayer !';
			},
		// Form data
		data: formData,
		//Options to tell jQuery not to process data or worry about content-type.
		cache: false,
		contentType: false,
		processData: false
	});
}


function stats_table_visible(){
	document.getElementById('panel-table').style.opacity = '1';
}

function stats_products_get_more_now(){
	var personnalised_pp	=	document.getElementById('f_select_p').options[document.getElementById('f_select_p').selectedIndex].value;
	document.getElementById('fpp').value	= personnalised_pp;

	stats_lunch_search_by_product(document.getElementById('stat_searchtype').value);
}

function stats_products_load_other_page(number){
	document.getElementById('fps').value = number;
	stats_lunch_search_by_product(document.getElementById('stat_searchtype').value);
}

function stats_products_global_export(){

	//Duplicating values
	document.getElementById('stat_searchtype_export').value				= document.getElementById('stat_searchtype').value;
	document.getElementById('stats_products_search_hidden_export').value= document.getElementById('stats_products_search_hidden').value;
	
	document.getElementById('stats_interval_v1_export').value	= document.getElementById('stats_interval_v1').options[document.getElementById('stats_interval_v1').selectedIndex].value;
	document.getElementById('stats_interval_v2_export').value	= document.getElementById('stats_interval_v2').options[document.getElementById('stats_interval_v2').selectedIndex].value;
	document.getElementById('stats_simple_v1_export').value	= document.getElementById('stats_simple_v1').options[document.getElementById('stats_simple_v1').selectedIndex].value;
		
	//Submit
	document.getElementById('stats_products_form_export').submit();
}

function stats_product_autocomplete(){
	$(document).ready(function() {
		$("#stats_products_search").keyup(function(e){
			var product = $("#stats_products_search").val();

			var code = e.keyCode || e.which;
		
			//Detect if Enter was pressed
			if(code==13){
						forward_select_me_manual();
						$("#forwardvalidate").css({ "background-image": "none" });
			}else if(!inArray(code, keycode_avoid) && product!=''){
			//}else{	
				stats_product_hide_filtrage_popup();
				if (isIE () == 7 || isIE () == 8 || isIE () == 9 || isIE () == 10) {
					document.getElementById('products_input_autoloader').style.display = 'block';
				}else{
					document.getElementById('products_input_autoloader').style.display = 'initial';
				}
				
				document.getElementById('products_search_autosuggest').style.display = 'none';
				document.getElementById('products_search_autosuggest_content').innerHTML ='';
				
				var OAjax;
				if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
				else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

				OAjax.open('POST','/fr/extranet/extranet_v3_products_search_stats.php',true);
				OAjax.onreadystatechange = function(){
					// OAjax.readyState == 1   ==>  connexion ?tablie
					// OAjax.readyState == 2   ==>  requete recue
					// OAjax.readyState == 3   ==>  reponse en cours
					
						//if ((OAjax.readyState == 1) || (OAjax.readyState == 2) || (OAjax.readyState == 3) ){
							//document.getElementById('contacts_popup_forward_loader').style.display = 'block';	
						//}
						
						if (OAjax.readyState == 4 && OAjax.status==200){
							if(OAjax.responseText=='-1'){
								window.document='login.html';
							}else if(OAjax.responseText=='0'){
								//document.getElementById('products_search_autosuggest').innerHTML+=''+OAjax.responseText+'';
							}else if(OAjax.responseText==''){
								//document.getElementById('products_search_autosuggest').style.display = 'block';
							}else{
								document.getElementById('products_search_autosuggest_content').innerHTML+=''+OAjax.responseText+'';
								document.getElementById('products_search_autosuggest').style.display = 'block';
							}
							
							
							
						}
						
						document.getElementById('products_input_autoloader').style.display = 'none';
						
						
					}
					
				OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
				OAjax.send('product='+product);
				
			}
		});
	});
}

function stats_filter_by_product(id){
	stats_product_hide_autosuggest();
	document.getElementById('stats_products_search_hidden').value	= id;
	stats_lunch_search_by_product(document.getElementById('stat_searchtype').value);
}

function stats_product_hide_filtrage_popup(){
	document.getElementById('products_search_autosuggest').style.display = 'none';
}

function stats_product_hide_autosuggest(){
	document.getElementById('products_search_autosuggest_content').innerHTML	= '';
	document.getElementById('products_search_autosuggest').style.display			= 'none';
}

/*****************************************************************/
/************************ Start Stats By Category ****************/
/*****************************************************************/

//Type "simple" ou "interval"
function stats_lunch_search_by_category(type){
	
	document.getElementById('stat_searchtype').value = type;
	
	var f_ps							= document.getElementById('fps').value;
	//Page pagination
	var f_pp 							= document.getElementById('fpp').value;
	
	var stats_interval_v1				= document.getElementById('stats_interval_v1').options[document.getElementById('stats_interval_v1').selectedIndex].value;
	var stats_interval_v2				= document.getElementById('stats_interval_v2').options[document.getElementById('stats_interval_v2').selectedIndex].value;
	var stats_simple_v1					= document.getElementById('stats_simple_v1').options[document.getElementById('stats_simple_v1').selectedIndex].value;
	
	if(typeof FormData == "undefined"){
		var formData = 'f_ps='+f_ps;
		formData += '&f_pp='+f_pp;
		
		formData += '&type='+type;
		formData += '&stats_interval_v1='+stats_interval_v1;
		formData += '&stats_interval_v2='+stats_interval_v2;
		formData += '&stats_simple_v1='+stats_simple_v1;
		
	}else{
		var formData = new FormData();
		formData.append("f_ps", f_ps);
		formData.append("f_pp", f_pp);
		
		//Type simple or interval and the value of the selectedIndex
		formData.append("type", type);
		formData.append("stats_interval_v1", stats_interval_v1);
		formData.append("stats_interval_v2", stats_interval_v2);
		formData.append("stats_simple_v1", stats_simple_v1);
	
	}
	
	$.ajax({
		url: 'extranet_v3_stats_load_category.php',  //Server script to process data
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
				document.getElementById('panel-table').style.opacity = '0.2';
			},
		success: function(data) {
				
				document.getElementById('loader_panel-table').style.display	= 'none';
				
				document.getElementById('panel-table').innerHTML	= '';
				document.getElementById('panel-table').innerHTML	= data;
				
				setTimeout(stats_table_visible, 200);
				
			},
		error: function() {
				document.getElementById('loader_panel-table').style.display	= 'none';
				//errorHandler,
				document.getElementById('loader_panel-table').innerHTML	+= 'Erreur, merci de r&eacute;essayer !';
			},
		// Form data
		data: formData,
		//Options to tell jQuery not to process data or worry about content-type.
		cache: false,
		contentType: false,
		processData: false
	});
}

function stats_category_load_other_page(number){
	document.getElementById('fps').value = number;
	stats_lunch_search_by_category(document.getElementById('stat_searchtype').value);
}

function stats_category_get_more_now(){
	var personnalised_pp	=	document.getElementById('f_select_p').options[document.getElementById('f_select_p').selectedIndex].value;
	document.getElementById('fpp').value	= personnalised_pp;

	stats_lunch_search_by_category(document.getElementById('stat_searchtype').value);
}

function stats_category_global_export(){

	//Duplicating values
	document.getElementById('stat_searchtype_export').value				= document.getElementById('stat_searchtype').value;
	
	document.getElementById('stats_interval_v1_export').value	= document.getElementById('stats_interval_v1').options[document.getElementById('stats_interval_v1').selectedIndex].value;
	document.getElementById('stats_interval_v2_export').value	= document.getElementById('stats_interval_v2').options[document.getElementById('stats_interval_v2').selectedIndex].value;
	document.getElementById('stats_simple_v1_export').value	= document.getElementById('stats_simple_v1').options[document.getElementById('stats_simple_v1').selectedIndex].value;
	
	//Submit
	document.getElementById('stats_category_form_export').submit();
}



/*****************************************************************/
/************************ Start products detail  ****************/
/*****************************************************************/

//Type "simple" ou "interval"
function stats_product_detail_lunch_generation(type){

	//hidden product id
	var stats_products_hidden_id		= document.getElementById('stats_products_hidden_id').value;
	
	var stats_interval_v1				= document.getElementById('stats_interval_v1').options[document.getElementById('stats_interval_v1').selectedIndex].value;
	var stats_interval_v2				= document.getElementById('stats_interval_v2').options[document.getElementById('stats_interval_v2').selectedIndex].value;
	var stats_simple_v1					= document.getElementById('stats_simple_v1').options[document.getElementById('stats_simple_v1').selectedIndex].value;
	
	if(stats_interval_v2<stats_interval_v1){
		alert('La date de d\351but doit \350tre inf\351rieur \340 la date de fin !');
	}else{
	
		if(typeof FormData == "undefined"){
			var formData = 'stats_products_hidden_id='+stats_products_hidden_id;
			
			formData += '&type='+type;
			formData += '&stats_interval_v1='+stats_interval_v1;
			formData += '&stats_interval_v2='+stats_interval_v2;
			formData += '&stats_simple_v1='+stats_simple_v1;
			
			formData += '&stats_product_name='+document.getElementById('product_title_h4').innerHTML;
			
		}else{
			var formData = new FormData();
			formData.append("stats_products_hidden_id", stats_products_hidden_id);
			
			//Type simple or interval and the value of the selectedIndex
			formData.append("type", type);
			formData.append("stats_interval_v1", stats_interval_v1);
			formData.append("stats_interval_v2", stats_interval_v2);
			formData.append("stats_simple_v1", stats_simple_v1);
			
			//Title of the product to show it in the title of the graph !
			formData.append("stats_product_name", document.getElementById('product_title_h4').innerHTML);
		
		}
		
		$.ajax({
			url: 'extranet_v3_stats_load_products_detail.php',  //Server script to process data
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
					document.getElementById('panel-table').style.opacity = '0.2';
				},
			success: function(data) {
					
					document.getElementById('loader_panel-table').style.display	= 'none';
					
					document.getElementById('loader_panel-table').style.display	= 'none';
						
					var html_result_container		= document.getElementById('stats_load_results');
					html_result_container.innerHTML	= data;
					eval(html_result_container.innerHTML);
					
					setTimeout(stats_table_visible, 200);
					
					
					
				},
			error: function() {
					document.getElementById('loader_panel-table').style.display	= 'none';
					//errorHandler,
					document.getElementById('loader_panel-table').innerHTML	+= 'Erreur, merci de r&eacute;essayer !';
				},
			// Form data
			data: formData,
			//Options to tell jQuery not to process data or worry about content-type.
			cache: false,
			contentType: false,
			processData: false
		});
		
	}//end else compare intervals
}

function stats_products_export(){
	var stats_h_month	= document.getElementById('stats_h_month').value;
	//var stats_h_data	= document.getElementById('stats_h_data').value;
	//var stats_h_values	= document.getElementById('stats_h_values').value;
	
	var product_name	= document.getElementById('product_title_h4').innerHTML;
	//var stats_h_month_temp	= 'Produit: '+product_name+'#'+stats_h_month;
	var stats_h_month_temp	= 'Statistiques pour fiche '+product_name+'#'+stats_h_month;
	
	
	document.getElementById('stats_h_month_final').value = '';
	document.getElementById('stats_h_month_final').value = stats_h_month_temp;
	
	if(stats_h_data!='' && stats_h_values!=''){
		
		document.getElementById('stats_form_export').submit();
		
	}else{
		alert('Aucune information \340 exporter !');
	}
}


/*****************************************************************/
/************************ Start products detail  ****************/
/*****************************************************************/

//Type "simple" ou "interval"
function stats_category_detail_lunch_generation(type){

	//hidden product id
	var stats_category_hidden_id		= document.getElementById('stats_category_hidden_id').value;
	
	var stats_interval_v1				= document.getElementById('stats_interval_v1').options[document.getElementById('stats_interval_v1').selectedIndex].value;
	var stats_interval_v2				= document.getElementById('stats_interval_v2').options[document.getElementById('stats_interval_v2').selectedIndex].value;
	var stats_simple_v1					= document.getElementById('stats_simple_v1').options[document.getElementById('stats_simple_v1').selectedIndex].value;
	
	if(stats_interval_v2<stats_interval_v1){
		alert('La date de d\351but doit \350tre inf\351rieur \340 la date de fin !');
	}else{
	
		if(typeof FormData == "undefined"){
			var formData = 'stats_category_hidden_id='+stats_category_hidden_id;
			
			formData += '&type='+type;
			formData += '&stats_interval_v1='+stats_interval_v1;
			formData += '&stats_interval_v2='+stats_interval_v2;
			formData += '&stats_simple_v1='+stats_simple_v1;
			
		}else{
		
			var formData = new FormData();
			formData.append("stats_category_hidden_id", stats_category_hidden_id);
			
			//Type simple or interval and the value of the selectedIndex
			formData.append("type", type);
			formData.append("stats_interval_v1", stats_interval_v1);
			formData.append("stats_interval_v2", stats_interval_v2);
			formData.append("stats_simple_v1", stats_simple_v1);
		
		}
		
		$.ajax({
			url: 'extranet_v3_stats_load_category_detail.php',  //Server script to process data
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
					document.getElementById('panel-table').style.opacity = '0.2';
				},
			success: function(data) {
					
					document.getElementById('loader_panel-table').style.display	= 'none';
					
					document.getElementById('loader_panel-table').style.display	= 'none';
						
					var html_result_container		= document.getElementById('stats_load_results');
					html_result_container.innerHTML	= data;
					eval(html_result_container.innerHTML);
					
					setTimeout(stats_table_visible, 200);
					
				},
			error: function() {
					document.getElementById('loader_panel-table').style.display	= 'none';
					//errorHandler,
					document.getElementById('loader_panel-table').innerHTML	+= 'Erreur, merci de r&eacute;essayer !';
				},
			// Form data
			data: formData,
			//Options to tell jQuery not to process data or worry about content-type.
			cache: false,
			contentType: false,
			processData: false
		});
		
	}//end else compare intervals
}

function stats_category_export(){
	var stats_h_month	= document.getElementById('stats_h_month').value;
	//var stats_h_data	= document.getElementById('stats_h_data').value;
	//var stats_h_values	= document.getElementById('stats_h_values').value;
	
	var product_name	= document.getElementById('product_title_h4').innerHTML;
	var stats_h_month_temp	= 'Catégorie: '+product_name+'#'+stats_h_month;
	
	document.getElementById('stats_h_month_final').value = '';
	document.getElementById('stats_h_month_final').value = stats_h_month_temp;
	
	if(stats_h_data!='' && stats_h_values!=''){
		
		document.getElementById('stats_form_export').submit();
		
	}else{
		alert('Aucune information \340 exporter !');
	}
}

