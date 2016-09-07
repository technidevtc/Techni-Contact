function swap_cbtn(img, action)
{
	switch (action)
	{
		case 'out':if (img.src == __ADMIN_URL__ + 'ressources/window_close_down.gif') {img.src = __ADMIN_URL__ + 'ressources/window_close.gif';}break;
		case 'down':img.src = __ADMIN_URL__ + 'ressources/window_close_down.gif';break;
		case 'up':
			if (img.src == __ADMIN_URL__ + 'ressources/window_close_down.gif')
			{
				img.src = __ADMIN_URL__ + 'ressources/window_close.gif';
				eval('Hide' + img.parentNode.parentNode.id+'();');
			}
			break;
		default:break;
	}
}

function trim(s)
{
	return s.replace(/(^\s*)|(\s*$)/g, '');
}

function GetTitle(val)
{
	switch (val)
	{
		case '1'  :return 'M.';
		case '2'  :return 'Mme';
		case '3'  :return 'Mlle';
		default :return 'M.';
	}
}

  var AJAXHandle = {
      
      error: function(XMLHttpRequest, textStatus, errorThrown){
        if(AJAXHandle.url == "../ressources/ajax/AJAX_customerSearch.php"){
          $('#showErrors').html(XMLHttpRequest, textStatus, errorThrown);
        };
      },
      success: function(data, textStatus) {
        if(AJAXHandle.url == "../ressources/ajax/AJAX_customerSearch.php"){
          
          var html = '';
          html += '<ul>';
          $.each(data, function(cle, valeur){
            if(valeur.length == 1){ // if there is only one result
              var tel = '';
                if(valeur[0].tel)
                  tel = '<span>'+valeur[0].tel+'</span>';
                html += '<li onClick="showCustomerBlocks('+valeur[0].customerID+', \''+valeur[0].email+'\')">\n\
                          <span>'+valeur[0].customerID+'</span> - \n\
                          <span>'+valeur[0].company+'</span> - \n\
                          <span>'+valeur[0].name+'</span> - \n\
                          <span>'+valeur[0].cp+'</span> - \n\
                          <span>'+valeur[0].city+'</span> - \n\
                          <span>'+valeur[0].email+'</span> - \n\
                          '+tel+'\n\
                        </li>';
//              $('#requestedCustomerMail').val(valeur[0].email);
              showCustomerBlocks(valeur[0].customerID, valeur[0].email);
            }else{ // if there are several results
              $.each(valeur, function(cle2, valeur2){
                var tel = '';
                if(valeur2.tel)
                  tel = '<span>'+valeur2.tel+'</span>';
                html += '<li onClick="showCustomerBlocks('+valeur2.customerID+', \''+valeur2.email+'\')">\n\
                          <span>'+valeur2.customerID+'</span> - \n\
                          <span>'+valeur2.company+'</span> - \n\
                          <span>'+valeur2.name+'</span> - \n\
                          <span>'+valeur2.cp+'</span> - \n\
                          <span>'+valeur2.city+'</span> - \n\
                          <span>'+valeur2.email+'</span> - \n\
                          '+tel+'\n\
                        </li>';
              });
//              $('#requestedCustomerMail').val('');
            if(cle == 'email')
              showCustomerBlocks(valeur[0].customerID, valeur[0].email);
            }
          });
          html += '</ul>';
          $('#showCustomerList').html(html);
        }

        if(AJAXHandle.url == "client.php"){
          $('#showCustomerBlocks').html(data);
        }
        if(typeof(client_id) != 'undefined'){
          var data = {'client_id' : client_id};
          var CSC = new HN.TC.CustomerSecondaryContacts(data);
          HN.TC.CustomerSecondaryContacts.refreshBlocs();

          $('.actions').on('click', function(){
            $('#blocContactsSecondairesInfos').toggle();
            $('#blocContactsSecondairesEdit').toggle();
            $('.actions .page-white-edit').toggle(0);
            $('.actions .cancel').toggle(0);
          });

          $('#showAddContactForm').on('click', function(){
            $(this).hide();
            $('#hideAddContactForm').show();
            $('.blocFormContactsSecondaires').show();
          });
          $('#hideAddContactForm').on('click', function(){
            $(this).hide();
            $('#showAddContactForm').show();
            $('.blocFormContactsSecondaires').hide();
          });

          $('#blocContactsSecondairesEdit').on('click', '.deleteClientContact', function(){
            //console.log($(this).closest('table').data('client-contact-num'));
            if(confirm('Souhaitez-vous supprimer définitivement ce contact ?')){
              var contactNum = $(this).closest('table').data('client-contact-num');
              me.data = data;
              HN.TC.CustomerSecondaryContacts.deleteClientContact(contactNum);
              HN.TC.CustomerSecondaryContacts.refreshBlocs();
            }
          });
        
          $('.blocFormContactsSecondaires').on('click', 'button', function(){
            $('.blocFormContactsSecondaires input,.blocFormContactsSecondaires select').each(function(){
             // console.log('each input',$(this).val(), $(this).attr('name'));
              //var inputName = 
             // me.data[inputName] = $(this).val();
              data[$(this).attr('name')] =$(this).val();
            });
            data.num = 0;
            me.data = data;
              //console.log('contactNum = '+me.data.num, data, me.data);

            me.createClientContact(me.data);
            HN.TC.CustomerSecondaryContacts.refreshBlocs();
            $('.blocFormContactsSecondaires input').val('');
            $('.blocFormContactsSecondaires').hide();
            $('#hideAddContactForm').hide();
            $('#showAddContactForm').show();
          });
        }
      }
  };
  
  var AJAXHandleEstimateList = {

      error: function(XMLHttpRequest, textStatus, errorThrown){
          $('#showErrors').html(XMLHttpRequest, textStatus, errorThrown);
      },
      success: function(data, textStatus) {
        $('#showEstimatesList').html(data);
      }
  }

  var AJAXHandleLeadList = {

      error: function(XMLHttpRequest, textStatus, errorThrown){
          $('#showErrors').html(XMLHttpRequest, textStatus, errorThrown);
      },
      success: function(data, textStatus) {
        $('#showLeadsList').html(data);
      }
  }
  
  var AJAXHandleSavedProducts = {

      error: function(XMLHttpRequest, textStatus, errorThrown){
          $('#showErrors').html(XMLHttpRequest, textStatus, errorThrown);
      },
      success: function(data, textStatus) {
        $('#showSavedProductsList').html(data);
      }
  }

  var AJAXHandleInternalNotes = {
      error: function(XMLHttpRequest, textStatus, errorThrown){
          $('#showErrors').html(XMLHttpRequest, textStatus, errorThrown);
      },
      success: function(data, textStatus) {
        $('#showInternalNotes').html(data);
      }
  }

function checkCustomer(){

  var checkTerm = $('#checkTerm').val();
  $('#showCustomerList').html('');
  $('#showCustomerBlocks').html('');
  $('#showEstimatesList').html('');
  $('#showOrderList').hide();
  $('#showEstimateList').hide();
  $('#showInvoiceList').hide();
  $('#showLeadsList').html('');
  $('#showSavedProductsList').html('');
  $('#showInternalNotes').html('');
  AJAXHandle.dataType = "json";
  var tailTel = '';
  if($('#checkTelephone:checked').val()){
    tailTel = '&telCheck=ok';
    AJAXHandle.timeout = 5000;
  }
  AJAXHandle.data = "term="+checkTerm+tailTel;
  AJAXHandle.type = "GET";
  AJAXHandle.url = "../ressources/ajax/AJAX_customerSearch.php";
  $.ajax(AJAXHandle);
  $('#requestedCustomerId').val('');
  $('#requestedCustomerMail').val('');
  $('#listeRdv').html('');
  $('#rdvLayerButtonContainer').hide();
}


function showCustomerBlocks(idCustomer, mailCustomer){
  client_id = idCustomer; // global val
  client_email = mailCustomer;
  
  AJAXHandle.data = "customerID="+idCustomer;
  AJAXHandle.dataType = "text";
  AJAXHandle.type = "GET";
  AJAXHandle.url = "client.php";
  $.ajax(AJAXHandle);
  $('#showCustomerList').html('');
  showEstimatesList(idCustomer);
  
  showRecommendedProducts();
  
  // order
  $("#showOrderList").show();
  ol.setSourceFilters([ ["where", ["o.client_id = ?", client_id]] ]);
  ol.colsByName["created"].sort("DESC");
  
  // estimate
  $("#showEstimateList").show();
  el.setSourceFilters([ ["where", ["e.client_id = ?", client_id]] ]);
  el.colsByName["created"].sort("DESC");
  
  // invoices
  $("#showInvoiceList").show();
  il.setSourceFilters([ ["where", ["i.client_id = ?", client_id]] ]);
  il.colsByName["created"].sort("DESC");
  
  showLeadsList(idCustomer);
  
  showSavedProductsList(idCustomer);
  
  showInternalNotes(idCustomer);
  $('#rdvLayerButtonContainer').show();
  $('#requestedCustomerId').val(idCustomer);
  $('#requestedCustomerMail').val(mailCustomer);
  getRDV();
}

function showRecommendedProducts(){
  HN.TC.AjaxNuukik.get(127, "users", [client_id, "recommendation"]).done(function(pdtList){
    var $recoPdts = $("#client-recommended-products"),
        $pdtList = $recoPdts.find("ul.entries");
    if (pdtList.length) {
      $pdtList.empty();
      for (var i=0; i<pdtList.length; i++) {
        var pdt = pdtList[i];
        $pdtList.append(
          "<li class=\"entry\" data-id=\""+pdt.id+"\">"+
            "<div class=\"pic\"><a href=\""+pdt.url+"\" target=\"_blank\"><img src=\""+pdt.pic+"\" class=\"vmaib\" /></a><div class=\"vsma\"></div></div>"+
            "<div class=\"title\">"+pdt.name+"</div>"+
            "<div class=\"actions\">"+
              "<span class=\"icon page-white-add\" title=\"créer un lead avec ce produit\"></span>"+
              (pdt.saleable?"<span class=\"icon basket-put\" title=\"créer un devis avec ce produit\"></span>":"")+
              (pdt.saleable?"<span class=\"icon cart-put\" title=\"créer une commande avec ce produit\"></span>":"")+
            "</div>"+
          "</li>"
        )
      }
      $recoPdts.show();
    } else {
      $recoPdts.hide();
    }
  });
}

function showEstimatesList(idCustomer){

  AJAXHandleEstimateList.data = "customerID="+idCustomer;
  AJAXHandleEstimateList.dataType = "text";
  AJAXHandleEstimateList.type = "GET";
  AJAXHandleEstimateList.url = "estimatesList.php";
  $.ajax(AJAXHandleEstimateList);
}

function showLeadsList(idCustomer){

  AJAXHandleLeadList.data = "customerID="+idCustomer;
  AJAXHandleLeadList.dataType = "html";
  AJAXHandleLeadList.type = "GET";
  AJAXHandleLeadList.url = "leadsList.php";
  $.ajax(AJAXHandleLeadList);
}

function showSavedProductsList(idCustomer){

  AJAXHandleSavedProducts.data = "customerID="+idCustomer;
  AJAXHandleSavedProducts.dataType = "html";
  AJAXHandleSavedProducts.type = "GET";
  AJAXHandleSavedProducts.url = "savedProductsList.php";
  $.ajax(AJAXHandleSavedProducts);
}

function showInternalNotes(idCustomer){
  AJAXHandleInternalNotes.data = "customerID="+idCustomer;
  AJAXHandleInternalNotes.dataType = "text";
  AJAXHandleInternalNotes.type = "GET";
  AJAXHandleInternalNotes.url = "internalNotesList.php";
  $.ajax(AJAXHandleInternalNotes);
}



HN.TC.CustomerSecondaryContacts.refreshBlocs = function(blockType){
//console.log('refreshed');
  HN.TC.CustomerSecondaryContacts.getAll(me.data.client_id, function(requestedData){
    
    if(blockType == 'info' || typeof(blockType) == 'undefined'){
      // refresh block info
      $('#blocContactsSecondairesInfos').html('');
       var scb = ''; // secondary contacts block

      if(requestedData.length)
        scb = '<table cellspacing="0" cellpadding="0"><tr><td>';
      for(var a=0; a<requestedData.length; a++)
        scb += '<div>'+requestedData[a]['prenom']+' '+requestedData[a]['nom']+' - '+requestedData[a]['fonction']+' - <a href="tel:'+requestedData[a]['tel1']+'">'+requestedData[a]['tel1']+' - '+requestedData[a]['email']+'</a></div>';

      if(requestedData.length)
        scb += '</td></tr></table>';

      if(scb != '')
        $('#blocContactsSecondairesInfos').html(scb);
    }
    
    if(blockType == 'edit' || typeof(blockType) == 'undefined'){
    //refresh block edit
      $('#blocContactsSecondairesEdit').html('');
          var scb = ''; // secondary contacts block

          for(var a=0; a<requestedData.length; a++){

            scb += '<table class="secondaryContact" data-client-contact-num='+requestedData[a]['num']+' cellspacing="0" cellpadding="0"><tr><td>';
            scb += '<div class="deleteClientContact ui-icon ui-icon-trash"></div>\n\
                    <div><label>Nom :</label> <input id="contacts-secondaires-nom" class="c_i" type="text" data-customer-info="cs_nom" name="nom" value="'+requestedData[a]['nom']+'" readonly="readonly" /> <button type="button" class="btn ui-state-default ui-corner-all">Changer</button></div>\n\
                    <div><label>Prénom :</label> <input id="contacts-secondaires-prenom" class="c_i" type="text" data-customer-info="cs_prenom" name="prenom"  value="'+requestedData[a]['prenom']+'" readonly="readonly" /> <button type="button" class="btn ui-state-default ui-corner-all">Changer</button></div>\n\
                    <div><label>E-mail :</label> <input id="contacts-secondaires-email" class="c_i" type="text" data-customer-info="cs_email" name="email"  value="'+requestedData[a]['email']+'" readonly="readonly" /> <button type="button" class="btn ui-state-default ui-corner-all">Changer</button></div>\n\
                    <div><label>Tél 1 :</label> <input id="contacts-secondaires-tel1" class="c_i" type="text" data-customer-info="cs_tel1" name="tel1"  value="'+requestedData[a]['tel1']+'" readonly="readonly" /> <button type="button" class="btn ui-state-default ui-corner-all">Changer</button></div>\n\
                    <div><label>Tél 2 :</label> <input id="contacts-secondaires-tel2" class="c_i" type="text" data-customer-info="cs_tel2" name="tel2"  value="'+requestedData[a]['tel2']+'" readonly="readonly" /> <button type="button" class="btn ui-state-default ui-corner-all">Changer</button></div>\n\
                    <div><label>Fax 1 :</label> <input id="contacts-secondaires-fax1" class="c_i" type="text" data-customer-info="cs_fax1" name="fax1"  value="'+requestedData[a]['fax1']+'" readonly="readonly" /> <button type="button" class="btn ui-state-default ui-corner-all">Changer</button></div>\n\
                    <div><label>Fax 2 :</label> <input id="contacts-secondaires-fax2" class="c_i" type="text" data-customer-info="cs_fax2" name="fax2"  value="'+requestedData[a]['fax2']+'" readonly="readonly" /> <button type="button" class="btn ui-state-default ui-corner-all">Changer</button></div>\n\
                    <!--<div><label>Fonction :</label> <input id="contacts-secondaires-fonction" class="c_i" type="text" data-customer-info="cs_fonction" name="fonction"  value="'+requestedData[a]['fonction']+'" readonly="readonly" /> <button type="button" class="btn ui-state-default ui-corner-all">Changer</button></div>-->\n\
                    <div><label>Fonction :</label> <select id="contacts-secondaires-fonction" class="c_i" data-customer-info="cs_fonction" name="fonction" disabled="disabled">';
            for(var b=0; b<customerfunctionList.length; b++)
              scb += '<option '+(requestedData[a]['fonction'] == customerfunctionList[b] ? ' selected="selected" ': '')+' value="'+customerfunctionList[b]+'">'+customerfunctionList[b]+'</option>';
            scb += '</select> <button type="button" class="btn ui-state-default ui-corner-all">Changer</button></div>';
            scb += '</td></tr></table>';
          }
         //console.log(customerfunctionList);
          if(scb != '')
            $('#blocContactsSecondairesEdit').html(scb);
          
          var pre_id = '#contacts-secondaires'
          $(pre_id+"-nom,"+
          pre_id+"-prenom,"+
          pre_id+"-email,"+
          pre_id+"-tel1,"+
          pre_id+"-tel2,"+
          pre_id+"-fax1,"+
          pre_id+"-fax2,"+
          pre_id+"-fonction").next('button.btn').on("click", function(){

          
            var $field = $(this).prevAll("input, select").first(),
                fieldName = $field.attr("id").split(pre_id.substring(1)+"-")[1],
                prop = $field.prop("readonly") !== undefined ? "readonly" : "disabled";
                //console.log($field);
            var relatedFields = $field.parent().siblings().find('input[readonly=readonly], select[readonly=readonly]');
            relatedFields.each(function(){
              var inputName = $(this).attr('name')
              me.data[inputName] = $(this).val();
            });
            
            if ($field.prop(prop)) {
              $field.data("old_val", $field.val());
              $(this).html("Sauver");
              $field.prop(prop,false).focus();
              if (me.onEditFuncs[fieldName])
                me.onEditFuncs[fieldName].edit.apply($field);
            }
            else {
              $(this).html("Changer");
              $field.prop(prop,true);
              me.data[fieldName] = $field.val();
              if (me.onEditFuncs[fieldName]){
                //console.log('pass1', data, me.data);
                me.onEditFuncs[fieldName].validate.apply($field);
              }else if ($field.data("old_val") != $field.val()){
                //console.log('pass2', $field);
                //me.data[$field] = $field.val();
                me.updateClientsContacts(); //client_id, data
              }
            }
          });
        }
      });
}

HN.TC.CustomerSecondaryContacts.deleteClientContact = function(contactNum){
  //console.log('contactNum = '+contactNum);
  me.deleteContact(me.data.client_id, contactNum);
}
