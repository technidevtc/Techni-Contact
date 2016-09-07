/* Javascripts Manager Techni-contact */

/**
 * sprintf() for JavaScript v.0.4
 *
 * Copyright (c) 2007 Alexandru Marasteanu <http://alexei.417.ro/>
 * Thanks to David Baird (unit test and patch).
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 */

function str_repeat(i, m) { for (var o = []; m > 0; o[--m] = i); return(o.join('')); }

function sprintf () {
  var i = 0, a, f = arguments[i++], o = [], m, p, c, x;
  while (f) {
    if (m = /^[^\x25]+/.exec(f)) o.push(m[0]);
    else if (m = /^\x25{2}/.exec(f)) o.push('%');
    else if (m = /^\x25(?:(\d+)\$)?(\+)?(0|'[^$])?(-)?(\d+)?(?:\.(\d+))?([b-fosuxX])/.exec(f)) {
      if (((a = arguments[m[1] || i++]) == null) || (a == undefined)) throw("Too few arguments.");
      if (/[^s]/.test(m[7]) && (typeof(a) != 'number'))
        throw("Expecting number but found " + typeof(a));
      switch (m[7]) {
        case 'b': a = a.toString(2); break;
        case 'c': a = String.fromCharCode(a); break;
        case 'd': a = parseInt(a); break;
        case 'e': a = m[6] ? a.toExponential(m[6]) : a.toExponential(); break;
        case 'f': a = m[6] ? parseFloat(a).toFixed(m[6]) : parseFloat(a); break;
        case 'o': a = a.toString(8); break;
        case 's': a = ((a = String(a)) && m[6] ? a.substring(0, m[6]) : a); break;
        case 'u': a = Math.abs(a); break;
        case 'x': a = a.toString(16); break;
        case 'X': a = a.toString(16).toUpperCase(); break;
      }
      a = (/[def]/.test(m[7]) && m[2] && a > 0 ? '+' + a : a);
      c = m[3] ? m[3] == '0' ? '0' : m[3].charAt(1) : ' ';
      x = m[5] - String(a).length;
      p = m[5] ? str_repeat(c, x) : '';
      o.push(m[4] ? a + p : p + a);
    }
    else throw ("Huh ?!");
    f = f.substring(m[0].length);
  }
  return o.join('');
}

function testAndWrite(id, text)
{
    document.getElementById(id).innerHTML = text;
}


function trim(s)
{
     return s.replace(/(^\s*)|(\s*$)/g, '');
}     


function getContent(file, query)
{
    if(navigator.appName.indexOf('Explorer') > -1)
    {
        var xmlHttp = new ActiveXObject('Microsoft.XMLHTTP');
        xmlHttp.open('GET', file + '?' + query, false);
        xmlHttp.send();
    }
    else
    {
        var xmlHttp = new XMLHttpRequest();
        xmlHttp.open('GET', file + '?' + query, false);
        xmlHttp.send(null);
    }

    return xmlHttp.responseText;

}


function goTo(page)
{
    eval('parent.location = \''+ page +'\'');
}


function isEmail(email)
{
    r = /^[A-Za-z0-9](([_\.\-]?[a-zA-Z0-9]+)*)@([A-Za-z0-9]+)(([\.\-]?[a-zA-Z0-9]+)*)\.([A-Za-z]{2,})$/g;
    return r.test(email);
}


function isUrl(url)
{
    r = /^(((ht|f)tp(s?))\:\/\/)?(([a-zA-Z0-9]+([@\-\.]?[a-zA-Z0-9]+)*)(\:[a-zA-Z0-9\-\.]+)?@)?(www.|ftp.|[a-zA-Z]+.)?[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,})(\:[0-9]+)?\/?/g;
    return r.test(url);
}


function isInteger(nb)
{
    r = /^[0-9]+$/g;
    return r.test(nb);
}


function isReal(nb)
{
    r = /^[\.0-9]+$/g;
    return r.test(nb);
}


function isAlphaNum(c)
{
    r = /^[0-9A-Z]$/g;
    return r.test(c);
}


function isValidDate(j, m, y)
{
    var d = new Date(y, m - 1, j);
    var a = d.getFullYear();
   
    if(a <= 100)
    {
        a += 1900;
    }

    return (d.getDate() == j) && ((d.getMonth() + 1) == m) && (a == y);
}


	/********************************************************************************/
	/************ Function to Load Familles that do not have any product ************/
	/********************************************************************************/

function load_familles_empty(){
	 var OAjax;
    if (window.XMLHttpRequest) OAjax = new XMLHttpRequest();
    else if (window.ActiveXObject) OAjax = new ActiveXObject('Microsoft.XMLHTTP');

    OAjax.open('POST',"AJAX_load_families_empty.php",true);
    OAjax.onreadystatechange = function(){
	
		// OAjax.readyState == 1   ==>  connexion établie
		// OAjax.readyState == 2   ==>  requête reçue
		// OAjax.readyState == 3   ==>  réponse en cours
		
	
		if ((OAjax.readyState == 1) || (OAjax.readyState == 2) || (OAjax.readyState == 3) ){
			document.getElementById('families_management_search_load').style.display = 'block';
			//alert('reponse en cours ');
		}
		
		if (OAjax.readyState == 4 && OAjax.status==200){

			document.getElementById('families_management_search_load').style.display = 'none'; //visibility = 'hidden';
			if(OAjax.responseText !=''){
				document.getElementById('families_right_content').innerHTML=''+OAjax.responseText+'';
			   //document.getElementById('families_right_content').style.display = 'block';
			}else{
				//document.getElementById('families_right_content').style.display = 'none';
				document.getElementById('families_right_content').innerHTML='Informations indisponibles !';
			}
			
		}
    }
        
    OAjax.setRequestHeader('Content-type','application/x-www-form-urlencoded');
    OAjax.send();

}


	/********************************************************************************/
	/*************************  Module Internal Notes Start *************************/
	/********************************************************************************/

	//Global parametres depend on Page URL

	if(window.location.pathname=='/fr/manager/advertisers/edit.php'){
		//Case Fournisseur => context:6
		var module_internal_notes_pre_id 		= '#module_internal_notes_item-cart';
		var module_internal_notes_context_id	= HN.TC.InternalNotes.NOTE_FOURNISSEUR;
		var module_internal_notes_context_text	= "production-fournisseur-pjmess";
		
		//From config_file to have the full path where the file was upload => Variable $uploadContextData = array( ....
		var module_internal_notes_bo_upload_dir = "production-fournisseur-pjmess";
		
	}else if(window.location.pathname=='/fr/manager/orders/order-detail.php'){
		//Case Orders => context:2
		var module_internal_notes_pre_id 		= '#module_internal_notes_item-cart';
		var module_internal_notes_context_id	= HN.TC.InternalNotes.CLIENT_COMMAND;
		var module_internal_notes_context_text	= "order-internalnotes-pjmess";
		
		//From config_file to have the full path where the file was upload => Variable $uploadContextData = array( ....
		var module_internal_notes_bo_upload_dir = "order-internalnotes-pjmess";
	
	}else if(window.location.pathname=='/fr/manager/supplier-orders/supplier-order-detail.php'){
		//Case Supplier_order => context:1
		var module_internal_notes_pre_id 		= '#module_internal_notes_item-cart';
		var module_internal_notes_context_id	= HN.TC.InternalNotes.SUPPLIER_ORDER;
		var module_internal_notes_context_text	= "supplier-orders-internalnotes-pjmess";
		
		//From config_file to have the full path where the file was upload => Variable $uploadContextData = array( ....
		var module_internal_notes_bo_upload_dir = "supplier-orders-internalnotes-pjmess";
		
	}else if(window.location.pathname=='/fr/manager/estimates/estimate-detail.php'){
		//Case ESTIMATE => context:4
		var module_internal_notes_pre_id 		= '#module_internal_notes_item-cart';
		var module_internal_notes_context_id	= HN.TC.InternalNotes.ESTIMATE;
		var module_internal_notes_context_text	= "estimate-internalnotes-tmppjmess";
		
		//From config_file to have the full path where the file was upload => Variable $uploadContextData = array( ....
		var module_internal_notes_bo_upload_dir = "estimate-internalnotes-tmppjmess";
		
	}else if(window.location.pathname=='/fr/manager/invoices/invoice-detail.php'){
		//Case INVOICE => context:5
		var module_internal_notes_pre_id 		= '#module_internal_notes_item-cart';
		var module_internal_notes_context_id	= HN.TC.InternalNotes.INVOICE;
		var module_internal_notes_context_text	= "invoice-internalnotes-tmppjmess";
		
		//From config_file to have the full path where the file was upload => Variable $uploadContextData = array( ....
		var module_internal_notes_bo_upload_dir = "invoice-internalnotes-tmppjmess";
		
	}else if(window.location.pathname=='/fr/manager/clients/' || window.location.pathname=='/fr/manager/clients/index.php'){
		//Quand on cherche par id ou mail pour le 2eme lien quand on clique sur un client à partir de la Liste RDV
		//Case CLIENT_ACCOUNT => context:3
		var module_internal_notes_pre_id 		= '#module_internal_notes_item-cart';
		var module_internal_notes_context_id	= HN.TC.InternalNotes.CLIENT_ACCOUNT;
		var module_internal_notes_context_text	= "clients-internalnotes-tmppjmess";
		
		//From config_file to have the full path where the file was upload => Variable $uploadContextData = array( ....
		var module_internal_notes_bo_upload_dir = "clients-internalnotes-tmppjmess";
	
	}
	
	


//Function to get item_Id to affect for the Attachments 
//Normal Case it's the Id Param in the page
//Commercial -> Clients we use to id of The Contact because we can search with Email address
function get_item_id_to_use_it_in_attachments(){

	//To load Attachments files that are not affected to a Internal Note
	//Condition for the CLIENT_ACCOUNT => context:3 Because the InternalNote in context 3 Save Email in itemId
	if(module_internal_notes_context_id==3){
		return document.getElementById('module_internal_notes_hidden_global_id_for_attachment_pending').value;
	}else{
		return document.getElementById('module_internal_notes_hidden_global_id').value;
	}
}	
	

function module_internal_notes_init_internal_notes(id_advertiser){
	//Declare Listners
	
	//Add note
	
	$(module_internal_notes_pre_id+"-show-note").on("click", function(){
		$(this).hide();
		$(module_internal_notes_pre_id+"-add-note, "+module_internal_notes_pre_id+"-cancel-note").show();
		$(module_internal_notes_pre_id+"-note").show(300);	  
	});
	
	//Cancel note
	$(module_internal_notes_pre_id+"-cancel-note").on("click", function(){
		$(this).hide();
		$(module_internal_notes_pre_id+"-add-note").hide();
		$(module_internal_notes_pre_id+"-show-note").show();
		$(module_internal_notes_pre_id+"-note").hide(300);
    });
	
	//Send note
	$(module_internal_notes_pre_id+"-add-note").on("click", function(){
		module_internal_notes_add_internal_notes();
		//Clear the attachment list
		$(module_internal_notes_pre_id+"-attachment-list").empty();

		//Clear the note area 
		$(module_internal_notes_pre_id+"-note textarea").val('');
			
    });
	
	//Click on Add Attachment
	$(module_internal_notes_pre_id+"-add-msn-attachment").on("click", function(){
     $uploadMsnAttachmentDb.dialog("open");
    });
	

	// close the current conversation
    $(module_internal_notes_pre_id+"-close-conv").on("click", function(){
      me.conversation.close(function(data){
        $(module_internal_notes_pre_id+"-close-conv").hide();
      });
    });
    
	
	//Show/Hide attachment related to a post
    $(module_internal_notes_pre_id+"-notes").on("click", ".attach", function(){
      var $this = $(this);
      $this.next().css({ left: $this.position().left+20 }).toggle(300);
    });
	
    // close the current conversation
    $(module_internal_notes_pre_id+"-close-conv").on("click", function(){
      me.conversation.close(function(data){
        $(module_internal_notes_pre_id+"-close-conv").hide();
      });
    });
	
	
    // folding block
    $(module_internal_notes_pre_id+"-notes").on("click", ".fold-block", function(e){	
      var $e = $(e.target),
          $fb = $(this);
      if ($e.hasClass("icon-fold")) {
        if ($e.hasClass("folded"))
          $fb.find(".fold-content").slideDown(function(){
            $fb.removeClass("folded").addClass("unfolded");
          });
        else
          $fb.find(".fold-content").slideUp(function(){
            $fb.removeClass("unfolded").addClass("folded");
          });
      }
    });
	
	
	//Popup Add Attachment
	var $uploadMsnAttachmentDb = $("#module_internal_notes_upload-msn-attachment-db").dialog({
	  width: 400,
	  autoOpen: false,
	  modal: true,
	  buttons: {
		"Annuler": function(){
		  $(this).dialog("close");
		},
		"Envoyer": function(){
		  //pjMessFile.aliasFileName = $(this).find("input[name='aliasPjMessFileName']").val();
		  //pjMessFile.loadingImg = $(this).find("img.loading-gif");
		  //pjMessFile.doAjaxFileUpload(function(){
			$uploadMsnAttachmentDb.dialog("close");
			
						$("#module_internal_notes_upload_img_loading").css('display:block');
						
						$.ajaxFileUpload({
						  url: HN.TC.ADMIN_URL+"files-upload/AJAX_files_upload.php",
						  secureuri: false,
						  fileElementId: 'module_internalnotes_pjMessFile',
						  //Pour chercher que les pices jointes des internales notes non pas à l'envoi de message
						  //source_appel: 'internal_notes',
						  dataType: "data",
						  //dataType: 'json',
						  async: false,
						  data: {
							itemId: get_item_id_to_use_it_in_attachments(),
							//context: HN.TC.InternalNotes.NOTE_FOURNISSEUR,
							context: module_internal_notes_context_text,
							fileElementId: 'module_internalnotes_pjMessFile',
							aliasFileName: document.getElementById('module_internalnotes_aliasPjMessFileName').value
						  },
						  // it is not possible to get the iframe headers to callback the error function, so only success can be triggered
						  success: function(data_return, status){

						  
							//Change returned data from String to Json to be sure that the script will come here
							var json_start  	= data_return.indexOf('{"response"');
							var data_to_convert	= data_return.substr(json_start,data_return.length);				
							var data = JSON.parse(data_to_convert);
							
							if (data.error) {
							  alert(data.error);
							} else {
							  alert(data.response);
							  document.getElementById('module_internal_notes_hidden_attachments_id').value+=data.params+';';
							  //if (cb) cb();
							  
							}
							
							$("#module_internal_notes_upload_img_loading").css('display:none');
							module_internal_notes_load_attachments_notes();
							  
						  }
						});
			
		  //});
		}
	  }
	});


	//Ajouter une pièce jointe Button Listner
	$("#module_internal_notes_add-msn-attachment").on("click", function(){
	  $uploadMsnAttachmentDb.dialog("open");
	});


	//Load internal
	module_internal_notes_load_internal_notes();
	//Load attachment not affected to internal
	module_internal_notes_load_attachments_notes();
	
}




//Delete a picture
function module_internal_notes_deletePjMessFile(fileId, filename){
	if (confirm("Souhaitez-vous supprimer le fichier "+filename+" ?")) {
		var deletePjFile = new HN.TC.deleteUploadedFile(fileId);
		deletePjFile.context = module_internal_notes_context_text; // same context as the ajaxUploadFile object
		deletePjFile.deleteFileFunction();
		
		//Load attachment not affected to internal
		module_internal_notes_load_attachments_notes();
	}
}
	
function module_internal_notes_add_internal_notes(){

	var data_send = {id_reference:document.getElementById('module_internal_notes_hidden_global_id').value, 
					context:module_internal_notes_context_id, 
					content:$(module_internal_notes_pre_id+"-note textarea").val(),
					attachments_id_affect_to_note:document.getElementById('module_internal_notes_hidden_attachments_id').value};
	
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
			//me.response = data;
			if (data && data.success) {
			  /*if(typeof(cb) == 'function')
				cb(data.data);*/
				
					//Clear the attachment list
					//$(module_internal_notes_pre_id+"-attachment-list").empty();

					//Clear the note area 
					//$(module_internal_notes_pre_id+"-note textarea").empty();
				
				//to close the popup
				$(module_internal_notes_pre_id+"-cancel-note").click();

				
			}else{
			  //console.log((data && data.errorMsg) || "empty response");
			}
			
			module_internal_notes_load_internal_notes();
			
		}
    });
	//document.getElementById(module_internal_notes_pre_id+'-notes').style.display	= 'block';
	$(module_internal_notes_pre_id+'-notes').css('display:block');
	
	//Pour vider le cache des id pièces jointes en attentes (caché)
	document.getElementById('module_internal_notes_hidden_attachments_id').value='';

}

function module_internal_notes_load_internal_notes(){
	$(module_internal_notes_pre_id+'-notes').css('display:block');
	//document.getElementById(module_internal_notes_pre_id+'-notes').style.display	= 'block'; 
		
	$.ajax({
		type: "POST",
			url: HN.TC.ADMIN_URL+"ressources/ajax/AJAX_Module_load_internal_notes.php",
			data: { 
				type: "Module_load_internal_notes", 
				/*object: "InternalNotes", */
				method: "select_notes_and_attach_by_id",
				internal_notes_reference_id: document.getElementById('module_internal_notes_hidden_global_id').value,
				internal_notes_id_context: module_internal_notes_context_id,
				context: module_internal_notes_context_text,
				module_bo_upload_dir: module_internal_notes_bo_upload_dir
			},
			dataType: "text",
			error: function(jqXHR, textStatus, errorThrown){
				//console.log(textStatus);
			},
		success: function(data, textStatus, jqXHR){
			
			//alert(data);
			if(data==''){
				document.getElementById('module_internal_notes_item-cart-notes').style.display = 'none';
			}else{
				document.getElementById('module_internal_notes_item-cart-notes').style.display = 'block';
			}
			
			$(module_internal_notes_pre_id+"-notes ul").empty();
			$(module_internal_notes_pre_id+"-notes ul").append(data);
			
			$(module_internal_notes_pre_id+"-notes").find("span.icon-fold.folded").click();
			
			$(module_internal_notes_pre_id+"-cancel-note").click();

		}
    });
	
}

//Load attachments that are not affected to any note in the form (to see or delete them)
function module_internal_notes_load_attachments_notes(){
	
	$.ajax({
		url: HN.TC.ADMIN_URL+"files-upload/AJAX_get_uploaded_files_list.php",
			secureuri: false,
			type: 'post',
			dataType: 'json',
			data: {
				itemId: get_item_id_to_use_it_in_attachments(),
				//context: HN.TC.InternalNotes.NOTE_FOURNISSEUR
				context: module_internal_notes_context_text,
				//Pour chercher que les pices jointes des internales notes non pas à l'envoi de message
				//source_appel: 'internal_notes'
			},
			async: false,

			success: function(data, textStatus, jqXHR){
				//var data = new Array(new Array());
				//data = data_return.response;
				data = data.response;
				
				$(module_internal_notes_pre_id+"-attachment-list").empty();

				for (var i=0; i<data.list.length; i++) (function(){
					var f = data.list[i],
					fn = f.alias_filename || f.filename;
						$("<li>")
						.append("<span>"+fn+"."+f.extension+"</span>")
						.append($("<a>", 
						{
							"class": "_blank",
							"title": "Voir",
							href: data.directory+f.filename+"."+f.extension,
							html: "<span class=\"icon eye\"></span>"
						}))
					.append($("<span>", {
						"class": "icon cross",
						"title": "Supprimer",
						click: function(){ module_internal_notes_deletePjMessFile(f.id, fn+"."+f.extension); }
					}))
					.appendTo(module_internal_notes_pre_id+"-attachment-list");
					
					//Add attachments id to the hidden element to store the attachments that are not affected to a note
					document.getElementById('module_internal_notes_hidden_attachments_id').value+=f.id+';';
				}());
				
				
				
				
			},
			error: function(jqXHR, textStatus, errorThrown){
				//alert(HN.TC.getAjaxErrorText(jqXHR, textStatus, errorThrown));
			}
    });
	
}


	/********************************************************************************/
	/*************************  Module Internal Notes End ***************************/
	/********************************************************************************/

