//After the Catch The function detect the type and redirect
//The creation to the appropriate Function
	//Input 
		//The ID of the elment of the reception
		//The ID of the droppable Field
		//The Special field (In case of the Family)
		//The Type of the Droppable Field
		//The Data Field Select
function build_the_row_now(field_element_id, draggable_field_special, draggable_field_type, draggable_field_select){
	
	//The switch case for the special Field
	switch(draggable_field_special){
		
		//Family 1St
		case 'families_st':
			build_a_row_in_family_st(field_element_id);
		break;
		
		//Family 2Nd
		case 'families_nd':
			build_a_row_in_family_nd(field_element_id);
		break;
		
		//Family 3Rd
		case 'families_rd':
			build_a_row_in_family_rd(field_element_id);
		break;
		
		//The other's
		default:
			//Parameters
				//Type Of the Row if it's a normal or a calculating Field
				//Row ID
				//ID Droppable Field
				//If the fied is special (1St Family, 2Nd Family, 3Rd Family or not)
				//Field Type (Texte, Number, Select or Date)
				//Values of the Select
			build_a_row_normal('normal_row', field_element_id, draggable_field_type, draggable_field_select);
			
		break;		
	}//End Switch for the Special Field
	
}//End function build_the_row_now


//Function to build a 1St Familly Row 
function build_a_row_in_family_st(field_element_id){
	var print_in_operator	= '';
	var print_in_value		= '';
	
	//The Operator Block !
	print_in_operator	= "<div class=\"familly_autocomplete_container_row\">"+
								"<div class=\"familly_autocomplete_field\">"+
									"<input type=\"text\" name=\"ffield_st_row"+field_element_id+"\" id=\"ffield_st_row"+field_element_id+"\" onkeyup=\"javascript:load_family_autocomplete_st(event, '"+field_element_id+"')\" title=\"Autocomplete Famille Niveau 1\" style=\"width:200px;\" />"+
								"</div>"+
								"<div id=\"familly_autocomplete_container_row_"+field_element_id+"\" class=\"familly_autocomplete_result\">"+
									"<span class=\"familly_autocomplete_list_close\">"+
										"<img src=\"ressources/images/icons/cross.png\" alt=\"Fermer\" title=\"Fermer\" onclick=\"close_the_autocomplete_now('"+field_element_id+"');\">"+
									"</span>"+
									
									"<div id=\"familly_autocomplete_loader_"+field_element_id+"\" class=\"familly_autocomplete_loader\">"+
										"<img src=\"ressources/images/lightbox-ico-loading.gif\" alt=\"Chargement..\" />"+
									"</div>"+
									
									"<div id=\"familly_autocomplete_list_"+field_element_id+"\">"+
										"&nbsp;"+
									"</div>"+
									
								"</div>"+
							"</div>";
							
	//The Value Block !
	print_in_value		= "<input type=\"hidden\" id=\"field_element_type_"+field_element_id+"\" value=\"family_st\" />"+
							"<input type=\"hidden\" id=\"field_element_"+field_element_id+"_value_1\" value=\"\" />"+
							"<span id=\"family_field_show_"+field_element_id+"\"></span>";
	
	$("#field_element_container_"+field_element_id).find(" .edl_operator").html(print_in_operator);
	$("#field_element_container_"+field_element_id).find(" .edl_value").html(print_in_value);
	
	document.getElementById("ffield_st_row"+field_element_id+"").focus();
}


//Function That load the Autocomplete St Family
function load_family_autocomplete_st(e, field_element_id){
	//Get the actual value
	var field_value		= document.getElementById("ffield_st_row"+field_element_id).value;
	
	var code = e.keyCode || e.which;
	
	if(code==13){
		//Enter => Close the Autocomplete !
		close_the_autocomplete_now(field_element_id);
	}else if(!inArray(code, keycode_avoid) && field_value!=''){
		
		document.getElementById('familly_autocomplete_container_row_'+field_element_id).style.display = "block";
		document.getElementById('familly_autocomplete_loader_'+field_element_id).style.display	= "block";
		document.getElementById('familly_autocomplete_list_'+field_element_id).innerHTML 		= '&nbsp;';
		
		//Start AJAX !
		var OAjax;
	
		if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
		else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

		OAjax.open('POST','/fr/marketing/family-st-search.php',true);
		OAjax.onreadystatechange = function(){
			// OAjax.readyState == 1   ==>  connexion ?tablie
			// OAjax.readyState == 2   ==>  requete recue
			// OAjax.readyState == 3   ==>  reponse en cours
			
			
				if ((OAjax.readyState == 1) || (OAjax.readyState == 2) || (OAjax.readyState == 3) ){
					//document.getElementById('panel-table').style.opacity = '0.2';
					//document.getElementById('loader_panel-table').style.display = 'block';
				}
				
				if (OAjax.readyState == 4 && OAjax.status==200){
					document.getElementById('familly_autocomplete_loader_'+field_element_id).style.display	= 'none';

					//if(OAjax.responseText !=''){
						//document.getElementById('panel-table').style.opacity = '1';
						document.getElementById('familly_autocomplete_list_'+field_element_id).innerHTML+=''+OAjax.responseText+'';
					//}else{
						//mmf_hide_autocomplete();
					//}
				}
			}
			
		OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
		OAjax.send('field_value='+field_value+'&id_row='+field_element_id);
		
		return false;
		
	}
	//alert(field_value);
	
}//End load_family_autocomplete_st


/***********************************************************************************************/
/************************************ Second Level !! ******************************************/
/***********************************************************************************************/


//Function to build a 2Nd Familly Row 
function build_a_row_in_family_nd(field_element_id){
	var print_in_operator	= '';
	var print_in_value		= '';
	
	//The Operator Block !
	print_in_operator	= "<div class=\"familly_autocomplete_container_row\">"+
								"<div class=\"familly_autocomplete_field\">"+
									"<input type=\"text\" name=\"ffield_nd_row"+field_element_id+"\" id=\"ffield_nd_row"+field_element_id+"\" onkeyup=\"javascript:load_family_autocomplete_nd(event, '"+field_element_id+"')\" title=\"Autocomplete Famille Niveau 2\" style=\"width:200px;\" />"+
								"</div>"+
								"<div id=\"familly_autocomplete_container_row_"+field_element_id+"\" class=\"familly_autocomplete_result\">"+
									"<span class=\"familly_autocomplete_list_close\">"+
										"<img src=\"ressources/images/icons/cross.png\" alt=\"Fermer\" title=\"Fermer\" onclick=\"close_the_autocomplete_now('"+field_element_id+"');\">"+
									"</span>"+
									
									"<div id=\"familly_autocomplete_loader_"+field_element_id+"\" class=\"familly_autocomplete_loader\">"+
										"<img src=\"ressources/images/lightbox-ico-loading.gif\" alt=\"Chargement..\" />"+
									"</div>"+
									
									"<div id=\"familly_autocomplete_list_"+field_element_id+"\">"+
										"&nbsp;"+
									"</div>"+
									
								"</div>"+
							"</div>";
							
	//The Value Block !
	print_in_value		= "<input type=\"hidden\" id=\"field_element_type_"+field_element_id+"\" value=\"family_nd\" />"+
							"<input type=\"hidden\" id=\"field_element_"+field_element_id+"_value_1\" value=\"\" />"+
							"<span id=\"family_field_show_"+field_element_id+"\"></span>";
	
	$("#field_element_container_"+field_element_id).find(" .edl_operator").html(print_in_operator);
	$("#field_element_container_"+field_element_id).find(" .edl_value").html(print_in_value);
	
	document.getElementById("ffield_nd_row"+field_element_id+"").focus();
}


//Function That load the Autocomplete Nd Family
function load_family_autocomplete_nd(e, field_element_id){
	//Get the actual value
	var field_value		= document.getElementById("ffield_nd_row"+field_element_id).value;
	
	var code = e.keyCode || e.which;
	
	if(code==13){
		//Enter => Close the Autocomplete !
		close_the_autocomplete_now(field_element_id);
	}else if(!inArray(code, keycode_avoid) && field_value!=''){
		
		document.getElementById('familly_autocomplete_container_row_'+field_element_id).style.display = "block";
		document.getElementById('familly_autocomplete_loader_'+field_element_id).style.display	= "block";
		document.getElementById('familly_autocomplete_list_'+field_element_id).innerHTML 		= '&nbsp;';
		
		//Start AJAX !
		var OAjax;
	
		if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
		else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

		OAjax.open('POST','/fr/marketing/family-nd-search.php',true);
		OAjax.onreadystatechange = function(){
			// OAjax.readyState == 1   ==>  connexion ?tablie
			// OAjax.readyState == 2   ==>  requete recue
			// OAjax.readyState == 3   ==>  reponse en cours
			
			
				if ((OAjax.readyState == 1) || (OAjax.readyState == 2) || (OAjax.readyState == 3) ){
					//document.getElementById('panel-table').style.opacity = '0.2';
					//document.getElementById('loader_panel-table').style.display = 'block';
				}
				
				if (OAjax.readyState == 4 && OAjax.status==200){
					document.getElementById('familly_autocomplete_loader_'+field_element_id).style.display	= 'none';

					//if(OAjax.responseText !=''){
						//document.getElementById('panel-table').style.opacity = '1';
						document.getElementById('familly_autocomplete_list_'+field_element_id).innerHTML+=''+OAjax.responseText+'';
					//}else{
						//mmf_hide_autocomplete();
					//}
				}
			}
			
		OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
		OAjax.send('field_value='+field_value+'&id_row='+field_element_id);
		
		return false;
		
	}
	
}//End load_family_autocomplete_nd

/***********************************************************************************************/
/************************************ Second Level !! ******************************************/
/***********************************************************************************************/


//Function to build a 3Rd Familly Row 
function build_a_row_in_family_rd(field_element_id){
	var print_in_operator	= '';
	var print_in_value		= '';
	
	//The Operator Block !
	print_in_operator	= "<div class=\"familly_autocomplete_container_row\">"+
								"<div class=\"familly_autocomplete_field\">"+
									"<input type=\"text\" name=\"ffield_rd_row"+field_element_id+"\" id=\"ffield_rd_row"+field_element_id+"\" onkeyup=\"javascript:load_family_autocomplete_rd(event, '"+field_element_id+"')\" title=\"Autocomplete Famille Niveau 3\" style=\"width:200px;\" />"+
								"</div>"+
								"<div id=\"familly_autocomplete_container_row_"+field_element_id+"\" class=\"familly_autocomplete_result\">"+
									"<span class=\"familly_autocomplete_list_close\">"+
										"<img src=\"ressources/images/icons/cross.png\" alt=\"Fermer\" title=\"Fermer\" onclick=\"close_the_autocomplete_now('"+field_element_id+"');\">"+
									"</span>"+
									
									"<div id=\"familly_autocomplete_loader_"+field_element_id+"\" class=\"familly_autocomplete_loader\">"+
										"<img src=\"ressources/images/lightbox-ico-loading.gif\" alt=\"Chargement..\" />"+
									"</div>"+
									
									"<div id=\"familly_autocomplete_list_"+field_element_id+"\">"+
										"&nbsp;"+
									"</div>"+
									
								"</div>"+
							"</div>";
							
	//The Value Block !
	print_in_value		= "<input type=\"hidden\" id=\"field_element_type_"+field_element_id+"\" value=\"family_rd\" />"+
							"<input type=\"hidden\" id=\"field_element_"+field_element_id+"_value_1\" value=\"\" />"+
							"<span id=\"family_field_show_"+field_element_id+"\"></span>";
	
	$("#field_element_container_"+field_element_id).find(" .edl_operator").html(print_in_operator);
	$("#field_element_container_"+field_element_id).find(" .edl_value").html(print_in_value);
	
	document.getElementById("ffield_rd_row"+field_element_id+"").focus();
}

//Function That load the Autocomplete Rd Family
function load_family_autocomplete_rd(e, field_element_id){
	//Get the actual value
	var field_value		= document.getElementById("ffield_rd_row"+field_element_id).value;
	
	var code = e.keyCode || e.which;
	
	if(code==13){
		//Enter => Close the Autocomplete !
		close_the_autocomplete_now(field_element_id);
	}else if(!inArray(code, keycode_avoid) && field_value!=''){
		
		document.getElementById('familly_autocomplete_container_row_'+field_element_id).style.display = "block";
		document.getElementById('familly_autocomplete_loader_'+field_element_id).style.display	= "block";
		document.getElementById('familly_autocomplete_list_'+field_element_id).innerHTML 		= '&nbsp;';
		
		//Start AJAX !
		var OAjax;
	
		if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
		else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

		OAjax.open('POST','/fr/marketing/family-rd-search.php',true);
		OAjax.onreadystatechange = function(){
			// OAjax.readyState == 1   ==>  connexion ?tablie
			// OAjax.readyState == 2   ==>  requete recue
			// OAjax.readyState == 3   ==>  reponse en cours
			
			
				if ((OAjax.readyState == 1) || (OAjax.readyState == 2) || (OAjax.readyState == 3) ){
					//document.getElementById('panel-table').style.opacity = '0.2';
					//document.getElementById('loader_panel-table').style.display = 'block';
				}
				
				if (OAjax.readyState == 4 && OAjax.status==200){
					document.getElementById('familly_autocomplete_loader_'+field_element_id).style.display	= 'none';

					//if(OAjax.responseText !=''){
						//document.getElementById('panel-table').style.opacity = '1';
						document.getElementById('familly_autocomplete_list_'+field_element_id).innerHTML+=''+OAjax.responseText+'';
					//}else{
						//mmf_hide_autocomplete();
					//}
				}
			}
			
		OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
		OAjax.send('field_value='+field_value+'&id_row='+field_element_id);
		
		return false;
		
	}
	
}//End load_family_autocomplete_rd




/***********************************************************************************************/
/*************************** Functions used for Three cases ************************************/
/***********************************************************************************************/

//Function to fill the selection from the Family Autocomplete !
function fill_family_from_autocomplete(family, field_element_id, id_tofill, text_to_fill){
	document.getElementById("field_element_"+field_element_id+"_value_1").value	= id_tofill;
	document.getElementById("family_field_show_"+field_element_id+"").innerHTML	= text_to_fill;
	
	//Clear the field !
	document.getElementById("ffield_"+family+"_row"+field_element_id+"").value	= '';
	
	//Close the autocomplete 
	close_the_autocomplete_now(field_element_id);
}

//Function to close the autocomplete
function close_the_autocomplete_now(field_element_id){
	document.getElementById("familly_autocomplete_container_row_"+field_element_id).style.display	= 'none';
}




/**********************************************************************************************/
/**************************** Build a Normal Row **********************************************/
/**********************************************************************************************/

/**********************************************/
/****************** Start TEXT ****************/
/**********************************************/

//Function to Build a normal row !=Familly !
function build_a_row_normal(row_type, field_element_id, draggable_field_type, draggable_field_select){
	
	//The switch case for the Field Type
	switch(draggable_field_type){
		
		//Text
		case 'text':
			build_a_row_type_texte(row_type, field_element_id);
		break;
		
		//Number
		case 'number':
			build_a_row_type_number(row_type, field_element_id);
		break;
		
		//Select
		case 'select':
			build_a_row_type_select(row_type, field_element_id, draggable_field_select);
		break;
		
		//Date
		case 'date':
			build_a_row_type_date(row_type, field_element_id);
		break;		
	}//End Switch for the Special Field	
}//End Function build_a_row_normal


//Function that build a Text row !
function build_a_row_type_texte(row_type, field_element_id){
	
	var row_destination_id	= '';
	var print_in_operator	= '';
	var print_in_value		= '';
	
	if(row_type=='normal_row'){
		row_destination_id	= "field_element_container_"+field_element_id;
	}else{
		row_destination_id	= 'field_element_container_calculate';
	}

	
	//The Operator Block !
	print_in_operator	= "<select class=\"\" id=\"select_text_"+field_element_id+"\" onchange=\"refresh_a_row_type_texte('normal_row', '"+field_element_id+"')\">"+
								"<option value=\"egale\" checked=\"true\">&Eacute;gale</option>"+
								"<option value=\"contient\">Contient</option>"+
								"<option value=\"commence_par\">Commence par</option>"+
								"<option value=\"termine_par\">Termine par</option>"+
								"<option value=\"ne_contient_pas\">Ne contient pas</option>"+
								"<option value=\"vide\">Vide</option>"+
								"<option value=\"non_vide\">Non vide</option>"+
							"</select>";
	
		//The Value Block !
	print_in_value		= "<input type=\"hidden\" id=\"field_element_type_"+field_element_id+"\" value=\"texte\" />"+
							"<input type=\"search\" id=\"field_element_"+field_element_id+"_value_1\" value=\"\" />";
	

	$("#"+row_destination_id).find(" .edl_operator").html(print_in_operator);
	$("#"+row_destination_id).find(" .edl_value").html(print_in_value);
	
	document.getElementById("field_element_"+field_element_id+"_value_1").focus();
	
}//End Function build_a_row_type_texte

//Change the content Of a Text Element !
function refresh_a_row_type_texte(row_type, field_element_id){
	//Get The Selected Item !
	var selected_item	= document.getElementById("select_text_"+field_element_id).options[document.getElementById("select_text_"+field_element_id).selectedIndex].value;
	
	if(row_type=='normal_row'){
		row_destination_id	= "field_element_container_"+field_element_id;
	}else{
		row_destination_id	= 'field_element_container_calculate';
	}
		
	//The switch case for the Field Text Selection
	switch(selected_item){
	
		//Egale, Contient And Ne Contient pas
		case "egale":
		case "contient":
		case "ne_contient_pas":
		case "commence_par":
		case "termine_par":
						print_in_value		= "<input type=\"hidden\" id=\"field_element_type_"+field_element_id+"\" value=\"texte\" />"+
							"<input type=\"search\" id=\"field_element_"+field_element_id+"_value_1\" value=\"\" />";
			
			$("#"+row_destination_id).find(" .edl_value").html(print_in_value);	
			
			document.getElementById("field_element_"+field_element_id+"_value_1").focus();
		break;
		
		//Vide
		case "vide":

			print_in_value		= "<input type=\"hidden\" id=\"field_element_type_"+field_element_id+"\" value=\"texte\" />"+
							"<input type=\"hidden\" id=\"field_element_"+field_element_id+"_value_1\" value=\"\" />";
			
			$("#"+row_destination_id).find(" .edl_value").html(print_in_value);	
		break;
		
		//Non Vide
		case "non_vide":
			print_in_value		= "<input type=\"hidden\" id=\"field_element_type_"+field_element_id+"\" value=\"texte\" />"+
							"<input type=\"hidden\" id=\"field_element_"+field_element_id+"_value_1\" value=\"\" />";
			
			$("#"+row_destination_id).find(" .edl_value").html(print_in_value);			
		break;
		
		
	}//End Switch for the Special Field	
	
}//End Function refresh_a_row_type_texte


/**********************************************/
/****************** End TEXT ******************/
/**********************************************/

/**********************************************/
/****************** Start NUMBER **************/
/**********************************************/

//Function to build a Number Row 
function build_a_row_type_number(row_type, field_element_id){
	
	var row_destination_id	= '';
	var print_in_operator	= '';
	var print_in_value		= '';
	
	if(row_type=="normal_row"){
		row_destination_id	= "field_element_container_"+field_element_id;
	}else{
		row_destination_id	= 'field_element_container_calculate';
	}

	
	//The Operator Block !
	print_in_operator	= "<select class=\"\" id=\"select_number_"+field_element_id+"\" onchange=\"refresh_a_row_type_number('normal_row', '"+field_element_id+"')\">"+
								"<option value=\"egale\" checked=\"true\">&Eacute;gale</option>"+
								"<option value=\"different\" >Diff&eacute;rent</option>"+
								"<option value=\"lt\">></option>"+
								"<option value=\"lt_egal\">>=</option>"+
								"<option value=\"gt\"><</option>"+
								"<option value=\"gt_egal\"><=</option>"+
								"<option value=\"vide\">Vide</option>"+
								"<option value=\"non_vide\">Non vide</option>"+
							"</select>";
	
		//The Value Block !
	print_in_value		= "<input type=\"hidden\" id=\"field_element_type_"+field_element_id+"\" value=\"number\" />"+
							"<input type=\"number\" id=\"field_element_"+field_element_id+"_value_1\" value=\"\" />";
	

	$("#"+row_destination_id).find(" .edl_operator").html(print_in_operator);
	$("#"+row_destination_id).find(" .edl_value").html(print_in_value);
	
	document.getElementById("field_element_"+field_element_id+"_value_1").focus();	
}//End Function 


//Change the Content of a Number Row
function refresh_a_row_type_number(row_type, field_element_id){
//Get The Selected Item !
	var selected_item	= document.getElementById("select_number_"+field_element_id).options[document.getElementById("select_number_"+field_element_id).selectedIndex].value;
	
	if(row_type=="normal_row"){
		row_destination_id	= "field_element_container_"+field_element_id;
	}else{
		row_destination_id	= 'field_element_container_calculate';
	}
		
	//The switch case for the Field Number Selection
	switch(selected_item){
	
		//Egale, Différent, < And >
		case "egale":
		case "different":
		case "lt":
		case "gt":
		case "lt_egal":
		case "gt_egal":
						print_in_value		= "<input type=\"hidden\" id=\"field_element_type_"+field_element_id+"\" value=\"number\" />"+
							"<input type=\"number\" id=\"field_element_"+field_element_id+"_value_1\" value=\"\" />";
			
			$("#"+row_destination_id).find(" .edl_value").html(print_in_value);	
			
			document.getElementById("field_element_"+field_element_id+"_value_1").focus();
		break;
		
		//Vide
		case "vide":

			print_in_value		= "<input type=\"hidden\" id=\"field_element_type_"+field_element_id+"\" value=\"number\" />"+
							"<input type=\"hidden\" id=\"field_element_"+field_element_id+"_value_1\" value=\"\" />";
			
			$("#"+row_destination_id).find(" .edl_value").html(print_in_value);	
		break;
		
		//Non Vide
		case "non_vide":
			print_in_value		= "<input type=\"hidden\" id=\"field_element_type_"+field_element_id+"\" value=\"number\" />"+
							"<input type=\"hidden\" id=\"field_element_"+field_element_id+"_value_1\" value=\"\" />";
			
			$("#"+row_destination_id).find(" .edl_value").html(print_in_value);			
		break;
		
		
	}//End Switch for the Special Field	
}//End Function refresh_a_row_type_number

/**********************************************/
/****************** End NUMBER ****************/
/**********************************************/


/**********************************************/
/****************** Start SELECT **************/
/**********************************************/
//Function to build a Number Row 
function build_a_row_type_select(row_type, field_element_id, draggable_field_select){
	
	var row_destination_id	= '';
	var print_in_operator	= '';
	var print_in_value		= '';
	
	if(row_type=="normal_row"){
		row_destination_id	= "field_element_container_"+field_element_id;
	}else{
		row_destination_id	= 'field_element_container_calculate';
	}

	//The Operator Block !
	print_in_operator	= "<select class=\"\" id=\"select_select_"+field_element_id+"\" >";
	
	var explode_first_result	= explode('|||', draggable_field_select);
	var explode_second_result	= '';
	var explode_local_loop		= 0;
	while(explode_first_result[explode_local_loop]){
		explode_second_result	= explode('#', explode_first_result[explode_local_loop]);

		print_in_operator	+= "<option value=\""+explode_second_result[0]+"\">"+explode_second_result[1];
		print_in_operator	+= "</option>";
		
		explode_local_loop++;
	}

	print_in_operator	+= "</select>";
	

	//The Value Block !
	print_in_value		= "<input type=\"hidden\" id=\"field_element_type_"+field_element_id+"\" value=\"select\" />";
	

	$("#"+row_destination_id).find(" .edl_operator").html(print_in_operator);
	$("#"+row_destination_id).find(" .edl_value").html(print_in_value);
	
	
}//End Function 


/**********************************************/
/****************** End SELECT ****************/
/**********************************************/


/**********************************************/
/****************** Start DATE ****************/
/**********************************************/

//Function That Build a Date Row 
function build_a_row_type_date(row_type, field_element_id){
	
	var row_destination_id	= '';
	var print_in_operator	= '';
	var print_in_value		= '';
	
	if(row_type=="normal_row"){
		row_destination_id	= "field_element_container_"+field_element_id;
	}else{
		row_destination_id	= 'field_element_container_calculate';
	}

	
	//The Operator Block !
	print_in_operator	= "<select class=\"\" id=\"select_date_"+field_element_id+"\" onchange=\"refresh_a_row_type_date('normal_row', '"+field_element_id+"')\">"+
								"<option value=\"egale\" checked=\"true\">&Eacute;gale</option>"+
								"<option value=\"entre\">Entre</option>"+
								"<option value=\"lt\">></option>"+
								"<option value=\"lt_egale\">>=</option>"+
								"<option value=\"gt\"><</option>"+
								"<option value=\"gt_egale\"><=</option>"+
								"<option value=\"aujourdhui_plus\">Aujourd'hui +</option>"+
								"<option value=\"aujourdhui_moins\">Aujourd'hui -</option>"+
							"</select>";
	
							/*"<option value=\"date+\">Date +</option>"+
							"<option value=\"date-\">Date -</option>"+*/
	//The Value Block !
	print_in_value		= "<input type=\"hidden\" id=\"field_element_type_"+field_element_id+"\" value=\"date\" />"+
							"<input type=\"date\" id=\"field_element_"+field_element_id+"_value_1\" value=\"\" />";
	

	$("#"+row_destination_id).find(" .edl_operator").html(print_in_operator);
	$("#"+row_destination_id).find(" .edl_value").html(print_in_value);
	
	document.getElementById("field_element_"+field_element_id+"_value_1").focus();
}


//Change the Content of a Date Row
function refresh_a_row_type_date(row_type, field_element_id){
//Get The Selected Item !
	var selected_item	= document.getElementById("select_date_"+field_element_id).options[document.getElementById("select_date_"+field_element_id).selectedIndex].value;
	
	if(row_type=="normal_row"){
		row_destination_id	= "field_element_container_"+field_element_id;
	}else{
		row_destination_id	= 'field_element_container_calculate';
	}
		
	//The switch case for the Field Date Selection
	switch(selected_item){
	
		//Egale, >, >=, <, <=
		case "egale":
		case "lt":
		case "lt_egale":
		case "gt":
		case "gt_egale":
						print_in_value		= "<input type=\"hidden\" id=\"field_element_type_"+field_element_id+"\" value=\"date\" />"+
							"<input type=\"date\" id=\"field_element_"+field_element_id+"_value_1\" value=\"\" />";
			
			$("#"+row_destination_id).find(" .edl_value").html(print_in_value);	
			
			document.getElementById("field_element_"+field_element_id+"_value_1").focus();
		break;
		
		//Entre
		case "entre":
						print_in_value		= "<input type=\"hidden\" id=\"field_element_type_"+field_element_id+"\" value=\"date\" />"+
							"<input type=\"date\" id=\"field_element_"+field_element_id+"_value_1\" value=\"\" />"+
							"<input type=\"date\" id=\"field_element_"+field_element_id+"_value_2\" class=\"marge_field2\" value=\"\" />";
			
			$("#"+row_destination_id).find(" .edl_value").html(print_in_value);	
			
			document.getElementById("field_element_"+field_element_id+"_value_1").focus();
		break;
		
		/*
		//Date +
		case "date+":
						print_in_value		= "<input type=\"hidden\" id=\"field_element_type_"+field_element_id+"\" value=\"date\" />"+
							"<input type=\"date\" id=\"field_element_"+field_element_id+"_value_1\" value=\"\" />"+
							"<input type=\"number\" id=\"field_element_"+field_element_id+"_value_2\" class=\"marge_field2\" value=\"\" title=\"Nombre de jours (+)\" />";
			
			$("#"+row_destination_id).find(" .edl_value").html(print_in_value);	
			
			document.getElementById("field_element_"+field_element_id+"_value_1").focus();
		break;
		
		//Date -
		case "date-":
						print_in_value		= "<input type=\"hidden\" id=\"field_element_type_"+field_element_id+"\" value=\"date\" />"+
							"<input type=\"date\" id=\"field_element_"+field_element_id+"_value_1\" value=\"\" />"+
							"<input type=\"number\" id=\"field_element_"+field_element_id+"_value_2\" class=\"marge_field2\" value=\"\" title=\"Nombre de jours (-)\" />";
			
			$("#"+row_destination_id).find(" .edl_value").html(print_in_value);	
			
			document.getElementById("field_element_"+field_element_id+"_value_1").focus();
		break;
		*/
		
		//aujourdhui -
		case "aujourdhui_moins":
						print_in_value		= "<input type=\"hidden\" id=\"field_element_type_"+field_element_id+"\" value=\"date\" />"+
							"<input type=\"number\" id=\"field_element_"+field_element_id+"_value_1\" pattern=\"[0-9]\" value=\"\" />";
			
			$("#"+row_destination_id).find(" .edl_value").html(print_in_value);	
			
			document.getElementById("field_element_"+field_element_id+"_value_1").focus();
		break;
		
		//aujourdhui +
		case "aujourdhui_plus":
						print_in_value		= "<input type=\"hidden\" id=\"field_element_type_"+field_element_id+"\" value=\"date\" />"+
							"<input type=\"number\" id=\"field_element_"+field_element_id+"_value_1\" value=\"\" />";
			
			$("#"+row_destination_id).find(" .edl_value").html(print_in_value);	
			
			document.getElementById("field_element_"+field_element_id+"_value_1").focus();
		break;
		
	}//End Switch for the Special Field	
}//End Function refresh_a_row_type_date

/**********************************************/
/****************** END DATE ******************/
/**********************************************/