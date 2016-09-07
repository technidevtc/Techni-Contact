
function activate_products_autocomplete(){
	$(document).ready(function() {
		$("#products_search").keyup(function(e){
			var product = $("#products_search").val();

			var code = e.keyCode || e.which;
	
			//Detect if Enter was pressed
			if(code==13){
						//forward_select_me_manual();
						$("#forwardvalidate").css({ "background-image": "none" });
						
						//Back to the first page 
						document.getElementById('fps').value="1";
						
						//Clear popup Autocomplete
						product_hide_filtrage_popup();
						
						//Search by string and erasing the category filter !
						products_search_by_string()
						
			}else if(!inArray(code, keycode_avoid) && product!=''){
			//}else{	
				product_hide_filtrage_popup();
				
				document.getElementById('products_input_autoloader').style.display = 'initial';
				document.getElementById('products_search_autosuggest').style.display = 'none';
				document.getElementById('products_search_autosuggest_content').innerHTML ='';
				
				var OAjax;
				if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
				else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

				OAjax.open('POST','/fr/extranet/extranet_v3_products_search.php',true);
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


function product_fill_this_string(id){
	document.getElementById('products_search').value = id;
	
	//Hide the autocomplete Popup !
	product_hide_filtrage_popup();
	
	//Clearing the category filters and lunck the search
	products_search_by_string();
}

function product_hide_filtrage_popup(){
	document.getElementById('products_search_autosuggest').style.display = 'none';
	document.getElementById('products_families_filter').style.display = 'none';
}


//Jquery listner click button Filter
$( document ).ready(function() {
	$("#products_category_filter").click(function() {
	  product_families_filter_call();
	});
	
	//Hide Popup
	/*$( document ).click(function(e){
		if(e.target.nodeName !='INPUT' && e.target.nodeName !='LABEL'){
			product_hide_filtrage_popup();
		}
		
	});*/
});

function product_families_filter_call(){
	product_hide_filtrage_popup();
	document.getElementById('products_families_filter').style.display = 'block';
}

function products_clear_popup_input_selection(){
	/*
	var source_elements = document.getElementById('products_familie_filter');
	var local_loop		= 0;
	
	while(source_elements.children[local_loop]){
		source_elements.children[local_loop].children[0].checked=false;
		local_loop++;
	}//end while
	*/
}

function product_families_filter_apply(){

	//Clearing the string filter
	document.getElementById('products_search').value	= '';
	
	//Clear the selection div
	document.getElementById('product_filter_result_content').innerHTML='';
	//document.getElementById('product_filter_result_container').style.opacity='0';

	//Get the container
	var source_elements = document.getElementById('products_familie_filter');
	
	//the selection element
	var one_element		= '';
	
	var local_loop		= 0;
	if(source_elements.innerHTML!='' && source_elements.children.length){
		while(source_elements.children[local_loop]){
			//alert(source_elements.children[local_loop].children[0].value);
			//alert(source_elements.children[local_loop].children[1].value);
			if(source_elements.children[local_loop].children[0].checked==true){
				one_element = '<div id="product_filter_selected_one_element'+local_loop+'" class="product_filter_selected_one_element">';
				
					one_element += '<div class="left">';
						one_element += '<input type="hidden" id="selected_prdfltr_helement'+local_loop+'" value="'+source_elements.children[local_loop].children[0].value+'">';
						
						one_element += source_elements.children[local_loop].children[1].value;
					one_element += '</div>';
					
					one_element += '<div class="right">';
						one_element += '<img src="ressourcesv3/icons/cross.png" alt="Supprimer" title="Supprimer" onclick="product_families_filter_removeme(\''+local_loop+'\')" />';
					one_element += '</div>';
					
				one_element += '</div>';
				
				document.getElementById('product_filter_result_content').innerHTML +=one_element;
				
			}
			local_loop++;
		}
	
		document.getElementById('product_filter_result_container').style.display='block';

  
  
		//Clear the selection in the Popup
		products_clear_popup_input_selection();
		
		//Hide popup
		product_hide_filtrage_popup();
		
		//Clear pagination parameter
		products_reset_pagination_parameter();
		
		//Lunch search
		products_lunch_search();
		
	}//end if
}//end function


function product_families_filter_removeme(id){

	//Clearing the string filter
	document.getElementById('products_search').value	= '';
	
	$("#product_filter_selected_one_element"+id).remove();
	
	document.getElementById('prdfltr_element'+id).checked=false;
	
	products_reset_pagination_parameter();
	
	//Call search function !
	products_lunch_search();
}

function products_get_families_selected(){
	var source_elements		= document.getElementById('product_filter_result_content').getElementsByTagName('input');

	var final_elements		= '';
	var local_loop			= 0;
	
	if(source_elements[local_loop] && source_elements[local_loop].type=='hidden'){
		while(source_elements[local_loop]){
			final_elements+=source_elements[local_loop].value+'|';
			local_loop++;
		}
	}

	return final_elements;
}

function products_search_by_string(){

	if(document.getElementById('products_search').value!=''){
		//Clearing the category filter
		//Because we are lunching the search by a string (name or id)
		document.getElementById('product_filter_result_content').innerHTML	= '';

		products_lunch_search();
	}else{
		alert('Veuillez saisir le nom du produit que vous recherchez.');
	}
}

function products_lunch_search(){
	document.getElementById('panel-table').style.opacity = '0.2';
	document.getElementById('loader_panel-table').style.display = 'block';
				
	//Page start
	var f_ps						= document.getElementById('fps').value;
	//Page pagination
	var f_pp 						= document.getElementById('fpp').value;
	
	//Get the product filter by category !
	//var products_search				= document.getElementById('products_search').value;
	var products_families_filter	= products_get_families_selected();
	
	//Get the string to make filter is it's not null
	var products_string_filter		= document.getElementById('products_search').value;
	
	
	document.getElementById('panel-table').innerHTML = '&nbsp;';
	
	var OAjax;
	
	if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
	else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

	OAjax.open('POST','/fr/extranet/extranet-v3-products-load.php',true);
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
					setTimeout(products_table_visible, 200);	

				//}else{
					//mmf_hide_autocomplete();
				//}
			}
		}
		
	OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	OAjax.send('f_ps='+f_ps+'&f_pp='+f_pp+'&pff='+products_families_filter+'&psf='+products_string_filter);
}

function products_table_visible(){
	document.getElementById('panel-table').style.opacity = '1';
}


function products_get_more_now(){
	var personnalised_pp	=	document.getElementById('f_select_p').options[document.getElementById('f_select_p').selectedIndex].value;
	document.getElementById('fpp').value	= personnalised_pp;

	products_lunch_search();
}

function products_load_other_page(number){
	document.getElementById('fps').value = number;
	products_lunch_search();
}

function products_reset_pagination_parameter(){
	document.getElementById('fps').value = '1';
	document.getElementById('fpp').value = '10';
}

function product_delete_popup_call(id){
	$( "#product-dialog-delete-confirm" ).dialog({
		resizable: false,
		height:220,
		modal: true,
		buttons: {
			"Oui": function(){
				$( this ).dialog( "close" );
				product_delete_confirm(id);
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


function show_product_errors_container(){
	$( "#product_add_form_validation_error" ).dialog({
		resizable: false,
		height: 280,
		width: 350,
		modal: true,
		buttons: {
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
	
	$("span.ui-dialog-title").text('Erreurs de validation !');

}

// Start the second Confirmation Popup "Result"
function product_delete_result_popup_call(){
	$( "#product-dialog-delete-confirm-result" ).dialog({
		resizable: false,
		height:220,
		/*width: 200,*/
		modal: true,
		buttons: {
			"Ok": function(){
				$( this ).dialog( "close" );
				products_lunch_search();
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


function product_delete_result_popup_initiate(){
	product_delete_result_popup_call();
	document.getElementById('product-dialog-delete-confirm-rloader').style.display = 'block';
	document.getElementById('product-dialog-delete-confirm-response').innerHTML = '';
}

function product_delete_result_popup_finish(){
	document.getElementById('product-dialog-delete-confirm-rloader').style.display = 'none';
}

function product_delete_confirm(id){

	product_delete_result_popup_initiate();
	
	var OAjax;
	if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
	else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

	OAjax.open('POST','/fr/extranet/extranet-v3-products-request-delete.php',true);
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
					document.getElementById('product-dialog-delete-confirm-response').innerHTML+=''+OAjax.responseText+'';
					
				}
				
				product_delete_result_popup_finish();
			}
				
		}
		
	OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	OAjax.send('id='+id);
	
}


function product_hide_autosuggest(){
	document.getElementById('products_search_autosuggest_content').innerHTML	= '';
	document.getElementById('products_search_autosuggest').style.display			= 'none';
}


// End the second Confirmation Popup "Result"



//Calling the Document Ready
function product_category_autocomplete(){
	$(document).ready(function() {
		$("#product_category_search").keyup(function(e){
			var category = $("#product_category_search").val();

			var code = e.keyCode || e.which;
		
			if(category.length>0){
				//Detect if Enter was pressed
				if(code==13){
							
				}else if(!inArray(code, keycode_avoid) && category!=''){
				//}else{	
					
					document.getElementById('product_search_category_loader').style.display = 'initial';
					document.getElementById('product_search_category_autocomplete').style.display = 'block';
					document.getElementById('product_search_category_result').innerHTML ='';
					
					var OAjax;
					if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
					else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

					OAjax.open('POST','/fr/extranet/extranet_v3_products_search_category.php',true);
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
									document.getElementById('product_search_category_autocomplete').style.display = 'none';
								}else{
									document.getElementById('product_search_category_result').innerHTML+=''+OAjax.responseText+'';
									//document.getElementById('product_search_category_autocomplete').style.display = 'block';
									
									products_category_autocmplete_blocks();
									$('.accordionButton').click();
								}
								
								
								
							}
							
							document.getElementById('product_search_category_loader').style.display = 'none';
							
							
						}
						
					OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
					OAjax.send('category='+category);
					
				}
			
			}else{
				//Hide the autocomplete
				product_category_autocomplete_hide();
			}//test length
		});
		
		//Add listner to the Price Input "Number"
		//$("#product_price_simpleval").numeric();
		jQuery('#product_price_simpleval').keyup(function () { 
			this.value = this.value.replace(/[^0-9\.]/g,'');
		});
		
	});
}

function product_category_autocomplete_hide(){
	document.getElementById('product_search_category_autocomplete').style.display	= 'none';
	if(document.getElementById('product_category_hidden').value!=''){
		document.getElementById('product_category_search').style.display			= 'none';
		document.getElementById('product_category_selected_cat_text').style.display	= 'block';		document.getElementById('product_picto_edit_category').style.display		= 'block';
	}
}

function product_get_this_category(id, name){
	product_category_autocomplete_hide();
	
	//The id of the selected category
	//
	document.getElementById('product_category_hidden').value 					= id;
	
	//The Div of the selected category
	document.getElementById('product_category_selected_cat_text').innerHTML		= name;
	document.getElementById('product_category_selected_cat_text').style.display	= 'block';
	
	product_get_this_category_step2();
}

//The suite of the first function
//So we can use it in the edit Page
//Because the field will be already filled
function product_get_this_category_step2(){

	document.getElementById('product_category_selected_cat_text').style.display	= 'block';

	//The button of edit
	document.getElementById('product_picto_edit_category').style.display		= 'block';

	//Input search
	document.getElementById('product_category_search').style.display			= 'none';
}

//When the user want to edit his selected category
function product_edit_category(){
	//Show the input search
	document.getElementById('product_category_search').style.display			= 'block';
	//Clear the input search
	document.getElementById('product_category_search').value					= '';
	
	//Hide the previous selection but keep it
	document.getElementById('product_category_selected_cat_text').style.display	= 'none';
	
	//Hide the edit button
	document.getElementById('product_picto_edit_category').style.display		= 'none'; 
	
	//Show the edit cancel button
	document.getElementById('product_picto_cancel_edit_category').style.display	= 'block';
	
	//Start the selection on the input
	document.getElementById('product_category_search').focus();
}

//When the user want to cancel the edit of the selected category
function product_edit_cancel_category(){

	//Hide the input search
	document.getElementById('product_category_search').style.display			= 'none';
	
	//Show the previous selection but keep it
	document.getElementById('product_category_selected_cat_text').style.display	= 'block';
	
	//Show the edit button
	document.getElementById('product_picto_edit_category').style.display		= 'block'; 

	//Hide the edit cancel button
	document.getElementById('product_picto_cancel_edit_category').style.display	= 'none';
}

function products_category_autocmplete_blocks(){

	//ACCORDION BUTTON ACTION (ON CLICK DO THE FOLLOWING)
	$('.accordionButton').click(function() {

		//REMOVE THE ON CLASS FROM ALL BUTTONS
		$('.accordionButton').removeClass('on');
		  
		//NO MATTER WHAT WE CLOSE ALL OPEN SLIDES
	 	//$('.accordionContent').slideUp('slow');
		$('.accordionButton').next().slideUp('slow');
   
   
	
		//IF THE NEXT SLIDE WASN'T OPEN THEN OPEN IT
		if($(this).next().is(':hidden') == true) {

			//ADD THE ON CLASS TO THE BUTTON
			$(this).addClass('on');
			  
			//OPEN THE SLIDE
			$(this).next().slideDown('slow', function(){
				$('html, body').animate({  
					//Ignore the scroll to the current element 27/11/2013
					//scrollTop:$(this).prev().offset().top  
				}, 'fast'); 
			} );
			
			
			/******************************/
			
			
			
			/*******************************/
		 }else {
				$('html, body').animate({  
					//scrollTop:$(this).parent().offset().top

					//Ignore the scroll to the current element 27/11/2013
					//scrollTop:$('#engine_content').offset().top 
				}, 'fast'); 

		}
		  
	 });
	  
	
	/*** REMOVE IF MOUSEOVER IS NOT REQUIRED ***/
	
	//ADDS THE .OVER CLASS FROM THE STYLESHEET ON MOUSEOVER 
	$('.accordionButton').mouseover(function() {
		$(this).addClass('over');
		
	//ON MOUSEOUT REMOVE THE OVER CLASS
	}).mouseout(function() {
		$(this).removeClass('over');										
	});
	
	/*** END REMOVE IF MOUSEOVER IS NOT REQUIRED ***/
	
	
	/********************************************************************************************************************
	CLOSES ALL S ON PAGE LOAD
	********************************************************************************************************************/	
	$('.accordionContent').hide();

}

function product_set_price_radio(number){
	if(number==1){
		document.getElementById('product_price_simple').style.display = 'block';
		document.getElementById('product_price_simple').focus();
	}else{
		document.getElementById('product_price_simple').style.display = 'none';
	}
}


function products_add_pictures_listner_popup(){
	document.getElementById('product-dialog-add-picture-response-msg').innerHTML	= '';
	$( "#product-dialog-add-picture-popup" ).dialog({
		resizable: false,
		height: 280,
		width: 350,
		modal: true,
		buttons: {
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
}

//Function to detect if we have already a id (Add Case)
//Input: picture, docs, checkonly_draft, checkonly_submit
function product_detect_existing_id(operation){
	
	var product_new_id	= document.getElementById('product_hidden_newid').value;
	
	if(product_new_id==''){

		var OAjax;
		if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
		else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

		OAjax.open('POST','/fr/extranet/extranet_v3_products_add_autoid.php',true);
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
					}else{
						document.getElementById('product_hidden_newid').value+=''+OAjax.responseText+'';
						product_new_id = OAjax.responseText;
						
						//After the generation of a new ID
						//We call the next function depending of the needs
							//	checkonly_draft 		=> To save a draft
							//	checkonly_submit 		=> To submit
							//	checkonly_draft_preview	=> To save a draft and preview it
							//	picture 				=> To reload the pictures
							//	docs 					=> To reload the docs
							
						if(operation=='checkonly_draft'){
							product_add_save_call_step2('draft');
						}else if(operation=='checkonly_submit'){
							product_add_save_call_step2('submit');
						}else if(operation=='checkonly_draft_preview'){
							product_add_save_call_step2('draft_preview');
						}else if(operation=='picture' || operation=='docs'){
							products_lunch_upload_picture(product_new_id);
						}
					}
				}
			
			}
			
		OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
		OAjax.send();
	
	}else{
	
		if(operation=='checkonly_draft'){
			product_add_save_call_step2('draft');
		}else if(operation=='checkonly_submit'){
			product_add_save_call_step2('submit');		
		}else if(operation=='checkonly_draft_preview'){
			product_add_save_call_step2('draft_preview');
		}else if(operation=='picture'){
			products_lunch_upload_picture(product_new_id);
		}else if(operation=='docs'){
			products_lunch_upload_file(product_new_id);
		}
	}//end else
}

function check_form_upload_picture(){

	var file = document.getElementById('product_picture_file').files[0];
	
	if(file){
		var name = file.name;
		var size = file.size;
		var type = file.type;
	
		//alert(name+' * '+size+' * '+type);
		
		if(type=='image/jpeg'){
			product_detect_existing_id('picture');
		}else{
			alert('Merci de choisir une photo ".jpg". ');
		}//end if test type photo
	
	}else{
		alert('Merci de choisir une photo. ');
	}//end else if(file)
}

//Function Upload Pictures
function products_lunch_upload_picture(product_new_id){
	//$('#product_add_picture_btn').click(function(){
		var formData = new FormData();
		//Add the hidden id
		formData.append("idf", product_new_id);
		formData.append("type", "new");
		formData.append("product_picture_file", document.getElementById('product_picture_file').files[0]);
		
		
		
		$.ajax({
			url: 'extranet_v3_products_pictures_add.php',  //Server script to process data
			type: 'POST',
			xhr: function() {  // Custom XMLHttpRequest
				var myXhr = $.ajaxSettings.xhr();
				if(myXhr.upload){ // Check if upload property exists
					myXhr.upload.addEventListener('pictureprogress',picture_progressHandlingFunction, false); // For handling the progress of the upload
				}
				return myXhr;
			},
			//Ajax events
			beforeSend: function() {
					document.getElementById('product-dialog-add-picture-loader').style.display='block';
					//beforeSendHandler,
				},
			success: function(data) {
					
					document.getElementById('product-dialog-add-picture-response-msg').innerHTML='';
					document.getElementById('product-dialog-add-picture-loader').style.display='none';
					
					if(data=='1'){
						//Show OK
						document.getElementById('product-dialog-add-picture-response-msg').innerHTML='<font color="green">Transfert effectu&eacute; avec succ&eacute;s</font>';
						
						//Flush the file input
						document.getElementById('product_picture_file').value	= '';
						//Close the Popup
						$( "#product-dialog-add-picture-popup" ).dialog( "close" );
						//Reload the pictures
						products_add_reload_pictures();
						
					}else{
						document.getElementById('product-dialog-add-picture-response-msg').innerHTML=data;
					}
					
					
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
	//});
}//End Function 

//Function to Handle the ProgressBar for the Picture LOAD
function picture_progressHandlingFunction(e){
    if(e.lengthComputable){
        $('pictureprogress').attr({value:e.loaded,max:e.total});
		//alert(e.loaded+' * '+e.total);
    }
}

function products_add_reload_pictures(){
	var product_new_id	= document.getElementById('product_hidden_newid').value;
	
	//Type is Edit or new
	var type			= 'new';
	
	if(product_new_id!=''){
		var OAjax;
		if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
		else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

		OAjax.open('POST','/fr/extranet/extranet_v3_products_pictures_load_not_validated.php',true);
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
					}else{
						document.getElementById('product_pictures_uploaded').innerHTML='';
						document.getElementById('product_pictures_uploaded').innerHTML=''+OAjax.responseText+'';
						
						//Applicate the Drag an Drop
						products_add_listner_dragdrop_pictures_list(type);
						
						//Applicate the zoom
						products_add_listner_zoom_fancy_pictures();
					}
				}
			
			}
			
		OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
		OAjax.send('idf='+product_new_id);
		
	}

}

//Activating the listner in the products pictures Add for Drag and drop
function products_add_listner_dragdrop_pictures_list(type){
		$("#product_pictures_uploaded_ul").sortable({ 
			opacity: 0.6, cursor: 'move', update: function() {
				if(document.getElementById('product_pictures_uploaded_change_flag').innerHTML==1){
				
					document.getElementById('product_pictures_uploaded_change_order_msg').innerHTML	= 'Enregistrement position en cours..';
				
					//Turn Off the flag to avoid the multiple save and lost of data
					document.getElementById('product_pictures_uploaded_change_flag').innerHTML	= 0;
					var idproduct	= document.getElementById('product_hidden_newid').value;
					
					//To make changes on the correct folders
					var order = $(this).sortable("serialize") + '&idp='+idproduct+'&type='+type;
					//var order = $('#product_pictures_uploaded_ul').serialize()+'&idp='+idproduct;
					$.post("extranet_v3_products_pictures_change_positions.php", order, function(theResponse){
						$("#product_pictures_uploaded_change_order_msg").html(theResponse);
						//Turn On the Flag to be able to update positions..
						document.getElementById('product_pictures_uploaded_change_flag').innerHTML	= 1;
						
						if(type=='new'){
							products_add_reload_pictures();
						}else if(type=='edit'){
							products_edit_reload_pictures('normal');
						}
						
					});
					
				}//End IF
			}//End Update Function
		});
}

//Listner Zoom in pictures 
function products_add_listner_zoom_fancy_pictures(){
	$(".fancybox").fancybox({
		helpers : {
			title : {
				type : 'float'
			}
		}
	});
}


function products_delete_this_picture(id_fiche, id_picture, type){
	if(id_fiche!='' && id_picture!='' && type!=''){
	

		var formData = new FormData();
		formData.append("idf", id_fiche);
		formData.append("idp", id_picture);
		formData.append("type", type);
		
		
		$.ajax({
			url: 'extranet_v3_products_pictures_delete.php',  //Server script to process data
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
					document.getElementById('product_pictures_uploaded_change_order_msg').innerHTML	= 'Chargement en cours..';
					document.getElementById('product_pictures_uploaded_ul').style.opacity			= '0.7';
				},
			success: function(data) {
					
					document.getElementById('product_pictures_uploaded_change_order_msg').innerHTML	= '';
					document.getElementById('product_pictures_uploaded_ul').style.opacity			= '1';
					
					if(data=='1'){
					
						//Show OK
						if(type=='new'){
							products_add_reload_pictures();
						}else if(type=='edit'){
							products_edit_reload_pictures('normal');
						}
						
					}else{
						alert('Erreur, merci de r\351essayer !');
					}
					
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
		
	}//end if
}


//Preview code video
function products_video_preview(){

	document.getElementById('product-dialog-video-preview-response-msg').innerHTML	= '';
	document.getElementById('product-dialog-video-preview-response-msg').innerHTML	= document.getElementById('product_video').value;
	
	$( "#product-dialog-video-preview-popup" ).dialog({
		resizable: false,
		width:'auto',
		modal: true,
		buttons: {
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
	
}

//Start validation 

//Function test if input is integer
/*function IsPosInteger(nbr){
    return (/^\d+$/.test(nbr) && (parseInt(nbr,10)>=0));
}*/



//Calling the save Draft or Send
//Using a intermediate function 
//To check the existence of a id
function product_add_save_call(type){
	if(type=='draft'){
		product_detect_existing_id('checkonly_draft');
	}else if(type=='draft_preview'){
		product_detect_existing_id('checkonly_draft_preview');
	}else if(type=='send'){
		product_detect_existing_id('checkonly_submit');
	}
}

function product_add_save_call_step2(type){
	var etat 				= 1;
	document.getElementById('product_add_form_validation_error').innerHTML	= '';
	
	var title				= document.getElementById('product_titre').value;
	var desc_fast			= document.getElementById('product_desc_rapide').value;
	
	var	category_hidden		= document.getElementById('product_category_hidden').value;
	
	var desc_long			= CKEDITOR.instances.product_desc.getData();
	//var desc_long			= document.getElementById('product_desc').innerHTML;
	
	
	var keywords			= document.getElementById('product_keyword').value;
	
	var product_price		= '';

	
	//Start tests
	
	if(title.length<3){
		document.getElementById('product_add_form_validation_error').innerHTML 	+= '<br />';
		document.getElementById('product_add_form_validation_error').innerHTML	+= '- Le <b>Titre</b> est obligatoire';
		etat 	= 0;
	}else{
		if(title.match(/^\s*$/)){
			document.getElementById('product_add_form_validation_error').innerHTML 	+= '<br />';
			document.getElementById('product_add_form_validation_error').innerHTML	+= '- Le <b>Titre</b> est obligatoire';
			etat 	= 0;
		}
	}
	
	if(desc_fast.length<3){
		document.getElementById('product_add_form_validation_error').innerHTML 	+= '<br />';
		document.getElementById('product_add_form_validation_error').innerHTML	+= '- Le <b>Description rapide</b> est obligatoire';
		etat 	= 0;
	}else{
		if(desc_fast.match(/^\s*$/)){
			document.getElementById('product_add_form_validation_error').innerHTML 	+= '<br />';
			document.getElementById('product_add_form_validation_error').innerHTML	+= '- Le <b>Description rapide</b> est obligatoire';
			etat 	= 0;
		}
	}

	
	if(category_hidden.match(/^\s*$/)){
		document.getElementById('product_add_form_validation_error').innerHTML 	+= '<br />';
		document.getElementById('product_add_form_validation_error').innerHTML	+= '- La <b>cat&eacute;gorie</b> est obligatoire';
		etat 	= 0;
	}
	
	if(desc_long.match(/^\s*$/) ){
		document.getElementById('product_add_form_validation_error').innerHTML 	+= '<br />';
		document.getElementById('product_add_form_validation_error').innerHTML	+= '- La <b>Description</b> est obligatoire';
		etat 	= 0;
	}
	
	
	if(document.getElementById('product_price0').checked==true){
		product_price		= 'sur devis';
	}else{
		if(document.getElementById('product_price_simpleval').value=='' || document.getElementById('product_price_simpleval').value=='0'){
			document.getElementById('product_add_form_validation_error').innerHTML 	+= '<br />';
			document.getElementById('product_add_form_validation_error').innerHTML	+= '- La <b>prix</b> est obligatoire';
			etat 				= 0;
		}else{
			product_price		= document.getElementById('product_price_simpleval').value;
		}
	}//end else if product price
	
	if(etat==1){
		var id_fiche	= document.getElementById('product_hidden_newid').value;
		
		var formData = new FormData();
		formData.append("id", id_fiche);
		formData.append("title", title);
		formData.append("desc_fast", desc_fast);
		formData.append("cat", category_hidden);
		formData.append("desc_long", desc_long);
		formData.append("keywords", keywords);
		formData.append("product_price", product_price);
		
		//Start Send 
		var page_destination	= '';
		if(type=='draft'){
			page_destination	= 'extranet_v3_products_add_save_draft.php';
		}else if(type=='draft_preview'){
			page_destination	= 'extranet_v3_products_add_save_draft.php';
		}else if(type=='submit'){
			page_destination	= 'extranet_v3_products_add_submit.php';
		}
		$.ajax({
			url: page_destination,  //Server script to process data
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
					document.getElementById('product-dialog-add-loading_save_draft_send').style.display	= 'block';
				},
			success: function(data) {
					
					document.getElementById('product-dialog-add-loading_save_draft_send').style.display	= 'none';
					
					if(data=='1'){
						//Show OK
						if(type=='draft'){
							document.getElementById('product_add_form_validation_error').innerHTML += '<font color="green">Brouillon enregistr&eacute; avec succ&egrave;s</font>';
							
							document.getElementById('product_add_form_validation_error').style.display="block";
							
						}else if(type=='draft_preview'){
				
							//Calling the preview !
							document.getElementById('formpreviewid').value	= id_fiche;
							document.formpreview.submit();
						
						}else if(type=='submit'){
							document.getElementById('product_add_form_validation_error').innerHTML += '<font color="green">Produit enregistr&eacute; avec succ&egrave;s. Il sera en ligne d&egrave;s sa validation par un op&eacute;rateur TECHNI-CONTACT</font>';
							
							//Clearing the function on the buttons
							//Redirecting
							document.getElementById('product_add_save_draft').onclick	= '';
							document.getElementById('product_add_send').onclick			= '';
							
							//Redirect after 3seconds
							window.setInterval(3000, document.location='/fr/extranet/extranet-v3-products.html');
							
						}//end else
					}else if(data=='-1'){
						//document.getElementById('product_add_form_validation_error').innerHTML += 'Vous devez vous reconnecter !';
						window.document='login.html';
					}else{
						document.getElementById('product_add_form_validation_error').innerHTML += 'Erreur, merci de r\351essayer !';
						
						$("html, body").animate({ scrollTop: $(document).height() }, 1000);
					}
					
				},
			error: function() {
					document.getElementById('product-dialog-add-loading_save_draft_send').style.display	= 'none';
					//errorHandler,
					document.getElementById('product_add_form_validation_error').innerHTML	+= 'Erreur, merci de r\351essayer !';
				},
			// Form data
			data: formData,
			//Options to tell jQuery not to process data or worry about content-type.
			cache: false,
			contentType: false,
			processData: false
		});
	
	
	}else{
		show_product_errors_container();
		//$("html, body").animate({ scrollTop: $(document).height() }, 1000);
	}//end if(etat==1)

}//End function



/**********************************************************************/
/************************* 	Start Product Edit  ************************/
/**********************************************************************/
//Of course we will use the other function but this part 
//is only for the product edit (& Draft Edit)

//Accept 
//operation=new			=> 	Deleting the existing pictures product on "products_adv" 
//							and duplicating the content of "products" in "products_adv"
//							and reload them normally
//operation=normal		=> 	To not delete picture existing on the "products_adv"
//							and reload them normally
function products_edit_reload_pictures(operation){
	var product_new_id	= document.getElementById('product_hidden_newid').value;
	
	//Type is Edit or new
	var type			= 'edit';
	
	if(product_new_id!=''){
		var OAjax;
		if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
		else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

		OAjax.open('POST','/fr/extranet/extranet_v3_products_pictures_load_validated.php',true);
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
					}else{
						document.getElementById('product_pictures_uploaded').innerHTML='';
						document.getElementById('product_pictures_uploaded').innerHTML=''+OAjax.responseText+'';
						
						//Applicate the Drag an Drop
						products_add_listner_dragdrop_pictures_list(type);
						
						//Applicate the zoom
						products_add_listner_zoom_fancy_pictures();
					}
				}
			
			}
			
		OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
		OAjax.send('idf='+product_new_id+"&operation="+operation);
		
	}

}

function check_edit_form_upload_picture(){

	var file = document.getElementById('product_picture_file').files[0];
	
	if(file){
		var name = file.name;
		var size = file.size;
		var type = file.type;
	
		//alert(name+' * '+size+' * '+type);
		
		if(type=='image/jpeg'){
			products_edit_lunch_upload_picture();
		}else{
			alert('Merci de choisir une photo ".jpg". ');
		}//end if test type photo
	
	}else{
		alert('Merci de choisir une photo. ');
	}//end else if(file)
}




//Function Upload Pictures
function products_edit_lunch_upload_picture(){

		var product_new_id 	= document.getElementById('product_hidden_newid').value;
		
	//$('#product_add_picture_btn').click(function(){
		var formData = new FormData();
		//Add the hidden id
		formData.append("idf", product_new_id);
		formData.append("type", "edit");
		formData.append("product_picture_file", document.getElementById('product_picture_file').files[0]);
		
		
		
		$.ajax({
			url: 'extranet_v3_products_pictures_add.php',  //Server script to process data
			type: 'POST',
			xhr: function() {  // Custom XMLHttpRequest
				var myXhr = $.ajaxSettings.xhr();
				if(myXhr.upload){ // Check if upload property exists
					myXhr.upload.addEventListener('pictureprogress',picture_progressHandlingFunction, false); // For handling the progress of the upload
				}
				return myXhr;
			},
			//Ajax events
			beforeSend: function() {
					document.getElementById('product-dialog-add-picture-loader').style.display='block';
					//beforeSendHandler,
				},
			success: function(data) {
					
					document.getElementById('product-dialog-add-picture-response-msg').innerHTML='';
					document.getElementById('product-dialog-add-picture-loader').style.display='none';
					
					if(data=='1'){
						//Show OK
						document.getElementById('product-dialog-add-picture-response-msg').innerHTML='<font color="green">Transfert effectu&eacute; avec succ&eacute;s</font>';
						
						//Flush the file input
						document.getElementById('product_picture_file').value	= '';
						//Close the Popup
						$( "#product-dialog-add-picture-popup" ).dialog( "close" );
						//Reload the pictures
						products_edit_reload_pictures('normal');
						
					}else{
						document.getElementById('product-dialog-add-picture-response-msg').innerHTML=data;
					}
					
					
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
	//});
}//End Function 


//Calling the save Draft or Send
//Using a intermediate function 
//To check the existence of a id
function product_edit_save_call(type){
	if(type=='draft'){
		product_edit_save_call_step2('checkonly_draft');
	}else if(type=='draft_preview'){
		product_edit_save_call_step2('checkonly_draft_preview');
	}else{
		product_edit_save_call_step2('checkonly_submit');
	}
}

//Check the extension file 
function hasExtension(inputID, exts) {
    var fileName = document.getElementById(inputID).value;
    return (new RegExp('(' + exts.join('|').replace(/\./g, '\\.') + ')$')).test(fileName);
}


function product_edit_save_call_step2(type){
	var etat 				= 1;
	document.getElementById('product_add_form_validation_error').innerHTML	= '';
	
	var title				= document.getElementById('product_titre').value;
	var desc_fast			= document.getElementById('product_desc_rapide').value;
	
	var	category_hidden		= document.getElementById('product_category_hidden').value;
	
	var desc_long			= CKEDITOR.instances.product_desc.getData();
	//var desc_long			= document.getElementById('product_desc').innerHTML;
	
	
	var keywords			= document.getElementById('product_keyword').value;
	var video				= document.getElementById('product_video').value;
	
	var product_price		= '';

	//The documentations
	
	//Start tests
	
	if(title.length<3){
		document.getElementById('product_add_form_validation_error').innerHTML 	+= '<br />';
		document.getElementById('product_add_form_validation_error').innerHTML	+= '- Le <b>Titre</b> est obligatoire';
		etat 	= 0;
	}else{
		if(title.match(/^\s*$/)){
			document.getElementById('product_add_form_validation_error').innerHTML 	+= '<br />';
			document.getElementById('product_add_form_validation_error').innerHTML	+= '- Le <b>Titre</b> est obligatoire';
			etat 	= 0;
		}
	}
	
	if(desc_fast.length<3){
		document.getElementById('product_add_form_validation_error').innerHTML 	+= '<br />';
		document.getElementById('product_add_form_validation_error').innerHTML	+= '- Le <b>Description rapide</b> est obligatoire';
		etat 	= 0;
	}else{
		if(desc_fast.match(/^\s*$/)){
			document.getElementById('product_add_form_validation_error').innerHTML 	+= '<br />';
			document.getElementById('product_add_form_validation_error').innerHTML	+= '- Le <b>Description rapide</b> est obligatoire';
			etat 	= 0;
		}
	}

	
	if(category_hidden.match(/^\s*$/)){
		document.getElementById('product_add_form_validation_error').innerHTML 	+= '<br />';
		document.getElementById('product_add_form_validation_error').innerHTML	+= '- La <b>cat&eacute;gorie</b> est obligatoire';
		etat 	= 0;
	}
	
	if(desc_long.match(/^\s*$/) ){
		document.getElementById('product_add_form_validation_error').innerHTML 	+= '<br />';
		document.getElementById('product_add_form_validation_error').innerHTML	+= '- La <b>Description</b> est obligatoire';
		etat 	= 0;
	}
	
	
	if(document.getElementById('product_price0').checked==true){
		product_price		= 'sur devis';
	}else{
		if(document.getElementById('product_price_simpleval').value=='' || document.getElementById('product_price_simpleval').value=='0'){
			document.getElementById('product_add_form_validation_error').innerHTML 	+= '<br />';
			document.getElementById('product_add_form_validation_error').innerHTML	+= '- La <b>prix</b> est obligatoire';
			etat 				= 0;
		}else{
			product_price		= document.getElementById('product_price_simpleval').value;
		}
	}//end else if product price
	
	
	if ( document.getElementById('product_doc1_name_btn').value!=''){
		if(!hasExtension('product_doc1_name_btn',['.pdf'])){
			document.getElementById('product_add_form_validation_error').innerHTML+='<br />- Le <b>document n&deg;1</b> doit &ecirc;tre un PDF';
			etat	 = 0;
		}
	}
	
	if ( document.getElementById('product_doc2_name_btn').value!=''){
		if(!hasExtension('product_doc2_name_btn',['.pdf'])){
			document.getElementById('product_add_form_validation_error').innerHTML+='<br />- Le <b>document n&deg;2</b> doit &ecirc;tre un PDF';
			etat	 = 0;
		}
	}
	
	if ( document.getElementById('product_doc3_name_btn').value!=''){
		if(!hasExtension('product_doc3_name_btn',['.pdf'])){
			document.getElementById('product_add_form_validation_error').innerHTML+='<br />- Le <b>document n&deg;3</b> doit &ecirc;tre un PDF';
			etat	 = 0;
		}
	}
	
	if(etat==1){
		var id_fiche	= document.getElementById('product_hidden_newid').value;
		
		var formData = new FormData();
		formData.append("id", id_fiche);
		formData.append("title", title);
		formData.append("desc_fast", desc_fast);
		formData.append("cat", category_hidden);
		formData.append("desc_long", desc_long);
		formData.append("keywords", keywords);
		formData.append("product_price", product_price);
		formData.append("video", video);
		
		if(document.getElementById('product_doc1_name_btn').files[0]){
			formData.append("product_doc1", document.getElementById('product_doc1_name_btn').files[0]);
		}
		
		if(document.getElementById('product_doc2_name_btn').files[0]){
			formData.append("product_doc2", document.getElementById('product_doc2_name_btn').files[0]);
		}
		
		if(document.getElementById('product_doc3_name_btn').files[0]){
			formData.append("product_doc3", document.getElementById('product_doc3_name_btn').files[0]);
		}
		
		//Add the params if the user clicked on deleted document
		//Parsing the url params
		
		var qsParm		= new Array();
		qs(qsParm);
		
		//Passing the parameters
		if(qsParm['nodoc1']){
			formData.append("nodoc1", qsParm['nodoc1']);
		}
		
		if(qsParm['nodoc2']){
			formData.append("nodoc2", qsParm['nodoc2']);
		}
		
		if(qsParm['nodoc3']){
			formData.append("nodoc3", qsParm['nodoc3']);
		}


		//Start Send 
		var page_destination	= '';
		if(type=='checkonly_draft' || type=='checkonly_draft_preview'){
			page_destination	= 'extranet_v3_products_edit_save_draft.php';
		}else{
			page_destination	= 'extranet_v3_products_edit_submit.php';
		}
		$.ajax({
			url: page_destination,  //Server script to process data
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
					document.getElementById('product-dialog-add-loading_save_draft_send').style.display	= 'block';
					
					document.getElementById('product_add_form_validation_error').innerHTML="";
				},
			success: function(data) {
					
					document.getElementById('product-dialog-add-loading_save_draft_send').style.display	= 'none';
					
					if(data=='1'){
						//Show OK
						if(type=='checkonly_draft'){
							document.getElementById('product_add_form_validation_error').innerHTML += '<font color="green">Brouillon enregistr&eacute; avec succ&egrave;s</font>';
							
							document.getElementById('product_add_form_validation_error').style.display="block";
							
						}else if(type=='checkonly_draft_preview'){
						
							//Calling the preview !
							document.getElementById('formpreviewid').value	= id_fiche;
							//document.formpreview.action="";
							document.formpreview.submit();
							//window.open('extranet_v3_product_preview.html?id='+id_fiche+'&t=e','_blank');
							
						}else{
							document.getElementById('product_add_form_validation_error').innerHTML += '<font color="green">Produit enregistr&eacute; avec succ&egrave;s. Il sera en ligne d&egrave;s sa validation par un op&eacute;rateur TECHNI-CONTACT</font>';
							
							document.getElementById('product_add_form_validation_error').display="block";
							
							//Clearing the function on the buttons
							//Redirecting
							document.getElementById('product_add_save_draft').onclick	= '';
							document.getElementById('product_add_send').onclick			= '';
							
							//Redirect after 3seconds
							window.setInterval(3000, document.location='/fr/extranet/extranet-v3-products.html');
							
						}//end else
					}else if(data=='-1'){
						//document.getElementById('product_add_form_validation_error').innerHTML += 'Vous devez vous reconnecter !';
						window.document='login.html';
					}else{
						document.getElementById('product_add_form_validation_error').innerHTML += 'Erreur, merci de r\351essayer !';
						
						$("html, body").animate({ scrollTop: $(document).height() }, 1000);

					}
					
				},
			error: function() {
					document.getElementById('product-dialog-add-loading_save_draft_send').style.display	= 'none';
					//errorHandler,
					document.getElementById('product_add_form_validation_error').innerHTML	+= 'Erreur, merci de r\351essayer !';
				},
			// Form data
			data: formData,
			//Options to tell jQuery not to process data or worry about content-type.
			cache: false,
			contentType: false,
			processData: false
		});
	
	
	}else{
		show_product_errors_container();
		//$("html, body").animate({ scrollTop: $(document).height() }, 1000);
	}//end if(etat==1)

}//End function


/**********************************************************************/
/************************* 	Start Documentations  *********************/
/**********************************************************************/

function products_add_documentation_listner_popup(number){
	document.getElementById('product-dialog-add-documentation'+number+'-response-msg').innerHTML	= '';
	$( "#product-dialog-add-documentation"+number+"-popup" ).dialog({
		resizable: false,
		height: 280,
		width: 350,
		modal: true,
		buttons: {
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
}




//Documentations
function check_form_upload_documentation(number){

	var etat	= 1;
	var name	= document.getElementById('product_add_documentation_name1').value;
	document.getElementById('product-dialog-add-documentation'+number+'-response-msg').innerHTML	+= '';

	//Check for the name and the type of the document
	if(name.length<3){
		document.getElementById('product-dialog-add-documentation'+number+'-response-msg').innerHTML+='<br /><font color="red"> - Merci de saisir un nom valide</font>';
		etat	 = 0;
	}
	
	if (!$('#upload').hasExtension(['.doc', '.docx', '.pdf'])){
		document.getElementById('product-dialog-add-documentation'+number+'-response-msg').innerHTML+='<br /><font color="red"> - Le document doit &ecirc;tre un word(.doc, .docx) ou PDF</font>';
		etat	 = 0;
	}
	
	
	if(etat==1){
		//alert('Call the upload Man !');
	}

}


//Function Preview on Edit
function preview_edit_product(){
	
	//Calling the Save draft and call preview if everything is OK !
	product_edit_save_call('draft_preview');

}//End Function 

function preview_add_product(){
	
	//Calling the Save draft and call preview if everything is OK !
	product_add_save_call('draft_preview');
}


/************************************************************************************************************/
/************************************************************************************************************/
/*********************************			START PENDING  				*************************************/
/************************************************************************************************************/
/************************************************************************************************************/

//Accept add, edit, delete
function products_pending_lunch_search(operation){

	
	document.getElementById('loader_panel-table').style.display = 'block';

	//Source page & Container destination
	var page_to_call		= '';
	if(operation=='add'){
		page_to_call		= 'extranet_v3_products_load_pending_add.php';
		div_destination		= 'pstabs-1';
	}else if(operation=='edit'){
		page_to_call		= 'extranet_v3_products_load_pending_edit.php';
		div_destination		= 'pstabs-2';
	}else if(operation=='delete'){
		page_to_call		= 'extranet_v3_products_load_pending_delete.php';
		div_destination		= 'pstabs-3';
	}
	
	document.getElementById(div_destination).style.opacity = '0.2';
		
	
	var f_ps						= document.getElementById('fps-'+operation).value;
	//Page pagination
	var f_pp 						= document.getElementById('fpp-'+operation).value;
	
	//var products_search				= document.getElementById('products_search').value;
	//var products_families_filter	= products_get_families_selected();
	
	document.getElementById(div_destination).innerHTML = '&nbsp;';
	
	var OAjax;
	
	if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
	else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

	OAjax.open('POST','/fr/extranet/'+page_to_call,true);
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
					
					document.getElementById(div_destination).innerHTML+=''+OAjax.responseText+'';
					setTimeout(products_table_tabs_visible(div_destination), 200);	

				//}else{
					//mmf_hide_autocomplete();
				//}
			}
		}
		
	OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	OAjax.send('f_ps='+f_ps+'&f_pp='+f_pp);
	
	
}//End function products_pending_lunch_search

function products_pending_load_other_page(operation,number){
	document.getElementById('fps-'+operation).value = number;
	products_pending_lunch_search(operation);
}

function products_pending_get_more_now(operation){
	var personnalised_pp	=	document.getElementById('f_select_p-'+operation).options[document.getElementById('f_select_p-'+operation).selectedIndex].value;
	document.getElementById('fpp-'+operation).value	= personnalised_pp;

	products_pending_lunch_search(operation);
}

function products_table_tabs_visible(block){
	document.getElementById(block).style.opacity = '1';
}


/************************************************************************************************************/
/************************************************************************************************************/
/*********************************			START DRAFT  				*************************************/
/************************************************************************************************************/
/************************************************************************************************************/


//Accept add, edit
function products_draft_lunch_search(operation){
	
	document.getElementById('loader_panel-table').style.display = 'block';

	//Source page	& Container destination
	var page_to_call		= '';
	if(operation=='add'){
		page_to_call		= 'extranet_v3_products_load_draft_add.php';
		div_destination		= 'pdtabs-1';
	}else if(operation=='edit'){
		page_to_call		= 'extranet_v3_products_load_draft_edit.php';
		div_destination		= 'pdtabs-2';
	}
	
	document.getElementById(div_destination).style.opacity = '0.2';
	
	
	var f_ps				= document.getElementById('fps-'+operation).value;
	//Page pagination
	var f_pp 				= document.getElementById('fpp-'+operation).value;
	
	//var products_search				= document.getElementById('products_search').value;
	//var products_families_filter	= products_get_families_selected();
	
	document.getElementById(div_destination).innerHTML = '&nbsp;';
	
	var OAjax;
	
	if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
	else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

	OAjax.open('POST','/fr/extranet/'+page_to_call,true);
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
					
					document.getElementById(div_destination).innerHTML+=''+OAjax.responseText+'';
					setTimeout(products_table_tabs_visible(div_destination), 200);	

				//}else{
					//mmf_hide_autocomplete();
				//}
			}
		}
		
	OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	OAjax.send('f_ps='+f_ps+'&f_pp='+f_pp);
	
	
}//End function products_draft_lunch_search

function products_draft_load_other_page(operation,number){
	document.getElementById('fps-'+operation).value = number;
	products_draft_lunch_search(operation);
}

function products_draft_get_more_now(operation){
	var personnalised_pp	=	document.getElementById('f_select_p-'+operation).options[document.getElementById('f_select_p-'+operation).selectedIndex].value;
	document.getElementById('fpp-'+operation).value	= personnalised_pp;

	products_draft_lunch_search(operation);
}


/**********************************************************************/
/************************* 	Start Product Delete  *********************/
/**********************************************************************/

//Accept
	//	list_draft_add			=> Delete from Add Draft list 
	//	list_draft_edit			=> Delete from Edit Draft list
	//	detail_page_draft_add	=> Delete from Draft add detail page
	//	detail_page_draft_edit	=> Delete from Draft edit detail page
function product_delete_draft(id, operation){
	$( "#product-dialog-draft-delete" ).dialog({
      resizable: false,
      height:200,
      modal: true,
      buttons: {
        "Confirmer": function() {
			product_delete_draft_confirm(id, operation);
			if(operation=='list_draft_add' || operation=='list_draft_edit'){
				$( this ).dialog( "close" );
			}
        },
		"Annuler": function(){
			$( this ).dialog( "close" );
		}
      }
    });
}

function product_delete_draft_confirm(id_fiche, operation){
	//We use a external id because we gonna use the same functions
	//in the list and in the detail page
	//var id_fiche = document.getElementById('product_hidden_newid').value;

	if(id_fiche!=''){
		var formData = new FormData();
		formData.append("idf", id_fiche);
		formData.append("operation", operation);
		
		
		$.ajax({
			url: 'extranet_v3_products_draft_delete.php',  //Server script to process data
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

				},
			success: function(data) {
				
					if(data=='1'){
					
						//Show OK
						//From list Or from detail page
						if(operation=='detail_page_draft_add' || operation=='detail_page_draft_edit'){
							//Redirect to draft lists
							document.getElementById('product-dialog-draft-preview-response-msg').innerHTML	= '<font color="green"><br />';
							document.getElementById('product-dialog-draft-preview-response-msg').innerHTML	+= ' Brouillon supprim&eacute; avec succ&egrave;s.';
							document.getElementById('product-dialog-draft-preview-response-msg').innerHTML	+= '</font>';
							
							window.setInterval(3000, document.location='/fr/extranet/extranet-v3-products-draft.html');
							
						}else if(operation=='list_draft_add'){
							//Show draft deleted Ok
							//Reload draft list edit
							products_draft_lunch_search('add');
							
						}else if(operation=='list_draft_edit'){
							//Show draft deleted Ok
							//Reload draft list edit
							products_draft_lunch_search('edit');
						}
						
						//Loading the number of Draft (in Left Menu)
						calculate_user_count_draft();
						
					}else{
						alert('Erreur, merci de r\351essayer !');
					}
					
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

	}//end if id_fiche!=''
}
