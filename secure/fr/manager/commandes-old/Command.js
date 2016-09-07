<!--

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
				eval('hide' + img.parentNode.parentNode.id+'();');
			}
			break;
		default:break;
	}
}

// Shipping options
function editShipppingFee()
{
	if (document.getElementById('ShippingFee').firstChild.nodeName.toLowerCase() != 'input')
	{
		document.getElementById('ShippingFeeMod').href = 'javascript: saveShippingFee()';
		document.getElementById('ShippingFeeMod').innerHTML = 'Sauver';
		sf = document.getElementById('ShippingFee');
		sf.innerHTML = '<input id="ShippingFeeEdit" type="text" class="total_edit" onkeypress="checkKeyEnter(event, saveShippingFee)" value="' + sf.innerHTML.substr(0,sf.innerHTML.length-1) + '" />';
		document.getElementById('ShippingFeeEdit').focus();
	}
}

function saveShippingFee()
{
	var val = parseFloat(document.getElementById('ShippingFeeEdit').value);
	AlterCommand('&alterShippingFee=' + val);
}

function toggleShippingFee_bg(action)
{
	switch (action)
	{
		case 'over' :document.getElementById('ShippingFee').style.backgroundColor = '#FFE28D';break;
		case 'out'  :document.getElementById('ShippingFee').style.backgroundColor = '#FFFFFF';break;
		default:break;
	}
}

// Payment Mean options
function editPaymentMean(a)
{
	a = document.getElementById('PaymentMeanMod');
	if (a.innerHTML == 'Modifier')
	{
		a.href = "javascript: saveStatus('PaymentMean')";
		a.innerHTML = 'Sauver';
		
		innerHTMLstring = '<select id="PaymentMeanEdit" class="in_place_select">';
		for (pl in PaymentMeanList)
			innerHTMLstring += '<option value="' + pl + '"' + (pl == parseInt(document.getElementById('PaymentMeanValue').value) ? ' selected' : '') + '>' + PaymentMeanList[pl] +'</option>';
		innerHTMLstring  += '</select>';
		document.getElementById('PaymentMean').innerHTML = innerHTMLstring;
		
		document.getElementById('PaymentMeanEdit').focus();
	}
}

// Payment Status options
function editPaymentStatus()
{
	a = document.getElementById('PaymentStatusMod');
	if (a.innerHTML == 'Modifier')
	{
		a.href = "javascript: saveStatus('PaymentStatus')";
		a.innerHTML = 'Sauver';
		
		innerHTMLstring = '<select id="PaymentStatusEdit" class="in_place_select">';
		for (pl in PaymentStatusList)
			innerHTMLstring += '<option value="' + pl + '"' + (pl == parseInt(document.getElementById('PaymentStatusValue').value) ? ' selected' : '') + '>' + PaymentStatusList[pl] +'</option>';
		innerHTMLstring  += '</select>';
		document.getElementById('PaymentStatus').innerHTML = innerHTMLstring;
		
		document.getElementById('PaymentStatusEdit').focus();
	}
}

// Processing Status options
function editProcessingStatus()
{
	a = document.getElementById('ProcessingStatusMod');
	if (a.innerHTML == 'Modifier')
	{
		a.href = "javascript: saveStatus('ProcessingStatus')";
		a.innerHTML = 'Sauver';
		
		innerHTMLstring = '<select id="ProcessingStatusEdit" class="in_place_select" onchange="ProcessingStatusChange()">';
		for (pl in ProcessingStatusList)
			innerHTMLstring += '<option value="' + pl + '"' + (pl == parseInt(document.getElementById('ProcessingStatusValue').value) ? ' selected' : '') + '>' + ProcessingStatusList[pl] +'</option>';
		innerHTMLstring  += '</select>';
		document.getElementById('ProcessingStatus').innerHTML = innerHTMLstring;
    ProcessingStatusChange();
		
		document.getElementById('ProcessingStatusEdit').focus();
	}
}
function ProcessingStatusChange() {
  $("#ProcessingStatus input").remove();
  if ($("#ProcessingStatusEdit").val() == 25)
    $("#ProcessingStatus").append("<input type=\"text\" id=\"PlannedDeliveryDateValue\" size=\"50\" value=\""+$("#PlannedDeliveryDate").val()+"\"/>");
//  else if ($("#ProcessingStatusEdit").val() == 21)
//    $("#ProcessingStatus").append("<input type=\"text\" id=\"openSavValue\" size=\"50\" value=\""+$("#openSav").val()+"\"/>");
  else if ($("#ProcessingStatusEdit").val() == 22)
    $("#ProcessingStatus").append("<input type=\"text\" id=\"closeSavValue\" size=\"50\" value=\""+$("#closeSav").val()+"\"/>");
  else if ($("#ProcessingStatusEdit").val() == 40)
    $("#ProcessingStatus").append("<input type=\"text\" id=\"dispatchCommentValue\" size=\"50\" value=\""+$("#dispatchComment").val()+"\"/>");
  else if ($("#ProcessingStatusEdit").val() == 90)
    $("#ProcessingStatus").append("<input type=\"text\" id=\"partiallyCancelledReasonValue\" size=\"50\" value=\""+$("#partiallyCancelledReason").val()+"\"/>");
  else if ($("#ProcessingStatusEdit").val() == 99)
    $("#ProcessingStatus").append("<input type=\"text\" id=\"cancelReasonValue\" size=\"50\" value=\""+$("#cancelReason").val()+"\"/>");
  else
    $("#ProcessingStatus input").remove();
}
function saveStatus(statusType)
{
	var PlannedDeliveryDate = $("#PlannedDeliveryDateValue").val();
        var openSav = $("#openSavValue").val();
        var closeSav = $("#closeSavValue").val();
        var dispatchComment = $("#dispatchCommentValue").val();
        var partiallyCancelledReason = $("#partiallyCancelledReasonValue").val();
        var cancelReason = $("#cancelReasonValue").val();
  if (statusType == "ProcessingStatus")
    AlterCommand("&alter"+statusType+"="+parseInt(document.getElementById(statusType+'Edit').value)+(PlannedDeliveryDate?"&plannedDeliveryDate="+PlannedDeliveryDate:"")+(openSav?"&openSav="+openSav:"")+(closeSav?"&closeSav="+closeSav:"")+(dispatchComment?"&dispatchComment="+dispatchComment:"")+(partiallyCancelledReason?"&partiallyCancelledReason="+partiallyCancelledReason:"")+(cancelReason?"&cancelReason="+cancelReason:"")+"&sendEmail="+$("#sendEmailCB").attr("checked"));
  else
    AlterCommand('&alter' + statusType + '=' + parseInt(document.getElementById(statusType+'Edit').value));
}

function checkKeyEnter(e, func)
{
	// supporté par ie et firefox, le plus important
	if (e.keyCode == 13)
	{
		func();
		return false;
	}
	else return true;
}

function AlterCommand(fieldlist)
{
  //document.write('CommandAlter.php?<?php echo $sid ?>&commandID=<?php echo $commandID ?>' + fieldlist);
//  document.write(fieldlist, window.location.pathname, window.location);

  var destination ;
  destination = window.location.pathname == '/fr/manager/orders/orderDetail.php' ? 'orderAlter.php?' : 'CommandAlter.php?';
	makeRequest(destination + __SID__ + '&commandID=' + __COMMAND_ID__ + fieldlist, 'ProcessCommandChanges');
}

function showNewClientOptions()
{
	document.getElementById('clientID_fixed').style.display = 'none';
	document.getElementById('button_change').style.display  = 'none';
	document.getElementById('clientID_edit').style.display  = 'inline';
	document.getElementById('button_search').style.display  = 'inline';
	document.getElementById('button_save').style.display    = 'inline';
	document.getElementById('button_cancel').style.display  = 'inline';
}
function hideNewClientOptions()
{
	document.getElementById('clientID_edit').style.display  = 'none';
	document.getElementById('button_search').style.display  = 'none';
	document.getElementById('button_save').style.display    = 'none';
	document.getElementById('button_cancel').style.display  = 'none';
	document.getElementById('clientID_fixed').style.display = 'inline';
	document.getElementById('button_change').style.display  = 'inline';
}
function saveNewClient()
{
	AlterCommand('&newClient=' + document.getElementById('clientID_edit').value);
}
function cancelNewClient()
{
	document.getElementById('clientID_edit').value = document.getElementById('clientID_fixed').value;
	hideNewClientOptions();
}

function AddProduct(id, idTC, quantity)
{
	AlterCommand('&addProduct=' + id + '-' + idTC + '-' + quantity);
}

function AddMultipleProducts(id, idTC, quantity)
{
	AlterCommand('&addProduct=' + id + '-' + idTC + '-' + quantity +'&addMultipleProducts=1');
}

function DelProduct(id, idTC)
{
	AlterCommand('&delProduct=' + id + '-' + idTC);
}

function updateProductsQuantity()
{
	qties = document.getElementById('ProductsList').getElementsByTagName('input');
	var updtstr = '';
	for (i = 0; i < qties.length; i++)
	{
		if (qties[i].parentNode.className == 'ref-qte')
                  updtstr += qties[i].parentNode.parentNode.getElementsByTagName('td')[1].getElementsByTagName('input')[0].value + '-' + qties[i].value + '_';
			
	}
	if (updtstr != '') AlterCommand('&UpdatePdtsQties='+updtstr);
}

function set_qte(idTC, value)
{
	if (isNaN(quantity = parseInt(document.getElementById('qte'+idTC).value)))
	{
          console.log(dft_qte_list[idTC]);
		quantity = dft_qte_list[idTC] ? dft_qte_list[idTC] : '';
	}
	else
	{
		if (value == '+1') quantity++;
		else if (value == '-1' && quantity > 0) quantity--;
		else if (quantity < 0) quantity = 0;
		dft_qte_list[idTC] = quantity;
	}
	
	document.getElementById('qte'+idTC).value = quantity;
}

function ProcessCommandChanges(response)
{
	try
	{
		if (response.readyState == 4)
		{
			if (response.status == 200)
			{
				document.getElementById('performingRequestMW').style.visibility = 'hidden';
				mainsplit = response.responseText.split(__MAIN_SEPARATOR__);
				
				var errors = mainsplit[0].split(__ERROR_SEPARATOR__);
				for (var i = 0; i < errors.length-1; i++)
				{
					var errorID = errors[i].split(__ERRORID_SEPARATOR__);
					if (errorID.length == 2)
					{
						var idErrorId = document.getElementById(errorID[0]).innerHTML;
                                                idErrorId += '<span class="errorUpdateAjax">'+errorID[1]+'</span><br />';
                                                document.getElementById(errorID[0]).innerHTML = idErrorId;
					}
				}
				
				var output = mainsplit[1].split(__OUTPUT_SEPARATOR__);
				for (var i = 0; i < output.length-1; i++)
				{
					var outputID = output[i].split(__OUTPUTID_SEPARATOR__);
					if (outputID.length == 2)
					{
						switch (outputID[0])
						{
							case 'clientID_fixed' :
								document.getElementById('clientID_fixed').value = outputID[1];
								document.getElementById('NewClientError').innerHTML = "<br />";
								hideNewClientOptions();
								break;
							case 'BillingAddress' :
								var data = outputID[1].split(__DATA_SEPARATOR__);
								for (var j = 0; j < data.length-1; j+=2) document.getElementById(data[j]).innerHTML = data[j+1];
								break;
							case 'ShipAddress' :
								var data = outputID[1].split(__DATA_SEPARATOR__);
								document.getElementById(data[0]).value = data[1];
								for (var j = 2; j < data.length-1; j+=2) document.getElementById(data[j]).innerHTML = data[j+1];
								hideCoordChangeWindow();
								break;
							case 'Totals' :
                                                          var destination ;
                                                          var typeId ;
                                                              destination = window.location.pathname == '/fr/manager/orders/orderDetail.php' ? 'orderDetail.php?' : 'CommandMain.php?';
                                                              typeId =  window.location.pathname == '/fr/manager/orders/orderDetail.php' ? '&idOrdre=' : '&commandID=';
                                                              document.location.href = destination + __SID__ + typeId + __COMMAND_ID__;
								//var data = outputID[1].split('<?php echo __DATA_SEPARATOR__ ?>');
								//for (var j = 0; j < data.length-1; j+=2) document.getElementById(data[j]).innerHTML = data[j+1] + '€';
								break;
							case 'PaymentMeanValue' :
								document.getElementById('PaymentMeanValue').value = outputID[1];
								document.getElementById('PaymentMean').innerHTML = PaymentMeanList[parseInt(outputID[1])];
								document.getElementById('PaymentMeanMod').href = 'javascript: editPaymentMean()';
								document.getElementById('PaymentMeanMod').innerHTML = 'Modifier';
								break;
                                                        case 'CommandeTypeValue' :
								document.getElementById('CommandeTypeValue').value = outputID[1];
								document.getElementById('CommandeType').innerHTML = TypeCommandeList[parseInt(outputID[1])];
								document.getElementById('CommandeTypeMod').href = 'javascript: editCommandeType()';
								document.getElementById('CommandeTypeMod').innerHTML = 'Modifier';
								break;
							case 'PaymentStatusValue' :
								document.getElementById('PaymentStatusValue').value = outputID[1];
								document.getElementById('PaymentStatus').innerHTML = PaymentStatusList[parseInt(outputID[1])];
								document.getElementById('PaymentStatusMod').href = 'javascript: editPaymentStatus()';
								document.getElementById('PaymentStatusMod').innerHTML = 'Modifier';
								break;
                                                        case 'OrderStatusValue' :
								document.getElementById('OrderStatusValue').value = outputID[1];
								document.getElementById('OrderStatusValueShow').innerHTML = OrderStatusList[parseInt(outputID[1])];
								document.getElementById('OrderStatusMod').href = 'javascript: editOrderStatus()';
								document.getElementById('OrderStatusMod').innerHTML = 'Modifier';
								break;
							case 'ProcessingStatusValue' :
								document.getElementById('ProcessingStatusValue').value = outputID[1];
                                                                if(window.location.pathname != '/fr/manager/orders/orderDetail.php'){
                                                                  document.getElementById('ProcessingStatus').innerHTML = ProcessingStatusList[parseInt(outputID[1])];
                                                                  document.getElementById('ProcessingStatusMod').href = 'javascript: editProcessingStatus()';
                                                                  document.getElementById('ProcessingStatusMod').innerHTML = 'Modifier';
                                                                }
								break;
              case "sendEmail":
                $("#sendEmailMsg").text(outputID[1])
                 .fadeIn(100).fadeOut(100).fadeIn(100).fadeOut(100)
                 .fadeIn(100).fadeOut(100).fadeIn(100).fadeOut(2500);
                break;
              case "PlannedDeliveryDate":
                $("#PlannedDeliveryDate").val(outputID[1]);
                if(window.location.pathname == '/fr/manager/orders/orderDetail.php'){
                  if (outputID[1] != "")
                  $("#ProcessingDeliveryDate").html(" "+outputID[1]);
                }else if (outputID[1] != "")
                  $("#ProcessingStatus").append(" "+outputID[1]);
                break;

              case "statutTimestamp":
                var sttsTmstmp = new Date(outputID[1]*1000);
                var statusTimestamp = (sttsTmstmp.getDate()<10?'0'+sttsTmstmp.getDate():sttsTmstmp.getDate())+'/'+((sttsTmstmp.getMonth()+1)<10?'0'+(sttsTmstmp.getMonth()+1):(sttsTmstmp.getMonth()+1))+'/'+sttsTmstmp.getFullYear()+' '+(sttsTmstmp.getHours()<10?'0'+sttsTmstmp.getHours():sttsTmstmp.getHours())+':'+(sttsTmstmp.getMinutes()<10?'0'+sttsTmstmp.getMinutes():sttsTmstmp.getMinutes());
                $("#ProcessingStatusTimestamp").html('MAJ : '+statusTimestamp);
                break;

              case "OrderStatusTimestamp":
                var sttsTmstmp = new Date(outputID[1]*1000);
                var statusTimestamp = (sttsTmstmp.getDate()<10?'0'+sttsTmstmp.getDate():sttsTmstmp.getDate())+'/'+((sttsTmstmp.getMonth()+1)<10?'0'+(sttsTmstmp.getMonth()+1):(sttsTmstmp.getMonth()+1))+'/'+sttsTmstmp.getFullYear()+' '+(sttsTmstmp.getHours()<10?'0'+sttsTmstmp.getHours():sttsTmstmp.getHours())+':'+(sttsTmstmp.getMinutes()<10?'0'+sttsTmstmp.getMinutes():sttsTmstmp.getMinutes());
                $("#OrderStatusTimestamp").html('MAJ : '+statusTimestamp);
                break;

              case "partiallyCancelledReason":
                $("#partiallyCancelledReason").val(outputID[1]);

              case "cancelReason":
                $("#cancelReason").val(outputID[1]);

              case "openSav":
                $("#openSav").val(outputID[1]);

              case "closeSav":
                $("#closeSav").val(outputID[1]);

              case "dispatchComment":
                $("#dispatchComment").val(outputID[1]);
//                if(window.location.pathname == '/fr/manager/orders/orderDetail.php'){
//                  if (outputID[1] != "")
//                  $("#ProcessingCancelReason").html(" "+outputID[1]);
//                }else if (outputID[1] != "")
                  $("#ProcessingStatus").append(" "+outputID[1]);
                break;

							case 'UpdatePdtsQties' :
								document.location.href = 'CommandMain.php?' + __SID__ + '&commandID=' + __COMMAND_ID__;
								break;
							case 'AddProduct' :
								document.location.href = 'CommandMain.php?' + __SID__ + '&commandID=' + __COMMAND_ID__;
								break;
                                                        case 'AddMultipleProducts' :
                                                                return false;
								break;
							case 'DelProduct' :
								document.location.href = 'CommandMain.php?' + __SID__ + '&commandID=' + __COMMAND_ID__;
								break;
							default:
								document.getElementById(outputID[0]).innerHTML = outputID[1];
						}
					}
				}
                                getConversation();
			}
			else
			{
//                          console.log(response.responseText);
//                          if(outputID[0] != 'AddMultipleProducts'){
                            alert(' Un problème est survenu au cours de la requête.');
//                          }
			}
		}
		else
		{
			document.getElementById('performingRequestMW').style.visibility = 'visible';
		}
	}
	catch(e)
	{
		alert("Une exception s'est produite : " + e.description);
	}
}

  var AJAXHandle3 = {
	type : "GET",
	url: "AJAXconfirmSendMail.php",
	dataType: "html",
	error: function (XMLHttpRequest, textStatus, errorThrown) {
		$("#CQDB").text(textStatus);
	},
	success: function (data, textStatus) {

            $("#CQDB").html(data);

	}
  };

    var AJAXHandle2 = {
	type : "GET",
	url: "AJAXloadArc.php",
	dataType: "html",
	error: function (XMLHttpRequest, textStatus, errorThrown) {
		$("#CQDB").text(textStatus);
	},
	success: function (data, textStatus) {

            $("#CQDB").html(data);

	}
  };

  
function sendMail( idCommande, idSupplier){

$("div.DB-bg").show();
 $("#CQDB").show();
    AJAXHandle3.data = "idCommande="+idCommande+"&idSupplier="+idSupplier;
    $.ajax(AJAXHandle3);
 $(window).scrollTop(100);
 return false;
}

function resendCustomerMail( idCommande, idCustomer){

  if(idCustomer == '')
    alert('Client inconnu');
  else{
    $("div.DB-bg").show();
    $("#CQDB").show();
    AJAXHandleResendCustomerMail.data = "idCommande="+idCommande+"&idCustomer="+idCustomer;
    $.ajax(AJAXHandleResendCustomerMail);
 $(window).scrollTop(100);
  }
 return false;
}

var AJAXHandleResendCustomerMail = {
	type : "GET",
	url: "AJAXresendCustomerMail.php",
	dataType: "html",
	error: function (XMLHttpRequest, textStatus, errorThrown) {
		$("#CQDB").text(textStatus);
	},
	success: function (data, textStatus) {

            $("#CQDB").html(data);

	}
  };

function loadArc(idCommande, idSupplier){
$("div.DB-bg").show();
 $("#CQDB").show();
 AJAXHandle2.data = "idCommande="+idCommande+"&idSupplier="+idSupplier;
    $.ajax(AJAXHandle2);
 $(window).scrollTop(100);
 return false;
}

// Payment Status options
function editOrderStatus()
{
	a = document.getElementById('OrderStatusMod');
	if (a.innerHTML == 'Modifier')
	{
		a.href = "javascript: saveStatus('OrderStatus')";
		a.innerHTML = 'Sauver';

		innerHTMLstring = '<select id="OrderStatusEdit" class="in_place_select">';
		for (pl in OrderStatusList)
			innerHTMLstring += '<option value="' + pl + '"' + (pl == parseInt(document.getElementById('OrderStatusValue').value) ? ' selected' : '') + '>' + OrderStatusList[pl] +'</option>';
		innerHTMLstring  += '</select>';
		document.getElementById('OrderStatusValueShow').innerHTML = innerHTMLstring;

		document.getElementById('OrderStatusEdit').focus();
	}
}

// Command type options
function editCommandeType()
{
	a = document.getElementById('CommandeTypeMod');
	if (a.innerHTML == 'Modifier')
	{
		a.href = "javascript: saveStatus('CommandeType')";
		a.innerHTML = 'Sauver';

		innerHTMLstring = '<select id="CommandeTypeEdit" class="in_place_select">';
		for (pl in TypeCommandeList)
			innerHTMLstring += '<option value="' + pl + '"' + (pl == parseInt(document.getElementById('CommandeTypeValue').value) ? ' selected' : '') + '>' + TypeCommandeList[pl] +'</option>';
		innerHTMLstring  += '</select>';
		document.getElementById('CommandeType').innerHTML = innerHTMLstring;

		document.getElementById('CommandeTypeEdit').focus();
	}
}

function AddProducts()
{
  var nbr = 0;
        $('.referenceContentID').each(function(cle){
          var referenceContentID = this.value;
          var quantity = $('#qteProduct'+(cle+1)).val();
          var id = '';
          if(referenceContentID != '' && quantity != ''){
            AddMultipleProducts(id, referenceContentID, quantity);
            nbr++;
          }
        });
        if(nbr > 0){
          var html = '<a href="CommandMain.php?' + __SID__ + '&commandID=' + __COMMAND_ID__ +'">Enregistrer les modifications</a>';
          $('#listInputQtte').html(html);
        }
}

//-->