<!--

function swap_cbtn(img, action)
{
	switch (action)
	{
		case 'out': if (img.src == __ADMIN_URL__ + 'ressources/window_close_down.gif') { img.src = __ADMIN_URL__ + 'ressources/window_close.gif'; } break;
		case 'down': img.src = __ADMIN_URL__ + 'ressources/window_close_down.gif'; break;
		case 'up':
			if (img.src == __ADMIN_URL__ + 'ressources/window_close_down.gif')
			{
				img.src = __ADMIN_URL__ + 'ressources/window_close.gif';
				eval('hide' + img.parentNode.parentNode.id+'();');
			}
			break;
		default: break;
	}
}

function saveStatus(statusType)
{
	AlterEstimate('&alter' + statusType + '=' + parseInt(document.getElementById(statusType+'Edit').value));
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

function AlterEstimate(fieldlist)
{
	//document.write('EstimateAlter.php?<?php echo $sid ?>&estimateID=<?php echo $estimateID ?>' + fieldlist);
	makeRequest('EstimateAlter.php?' + __SID__ + '&estimateID=' + __ESTIMATE_ID__ + fieldlist, 'ProcessEstimateChanges');
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
	AlterEstimate('&newClient=' + document.getElementById('clientID_edit').value);
}
function cancelNewClient()
{
	document.getElementById('clientID_edit').value = document.getElementById('clientID_fixed').value;
	hideNewClientOptions();
}

function AddProduct(id, idTC, quantity)
{
	AlterEstimate('&addProduct=' + id + '-' + idTC + '-' + quantity);
}

function DelProduct(idTC)
{
	AlterEstimate('&delProduct=' + idTC);
}

function updateProductsQuantity()
{
	qties = document.getElementById('ProductsList').getElementsByTagName('input');
	var updtstr = '';
	for (i = 0; i < qties.length; i++)
	{
		if (qties[i].parentNode.className == 'ref-qte')
			updtstr += qties[i].parentNode.parentNode.getElementsByTagName('td')[0].getElementsByTagName('input')[0].value + '-' + qties[i].value + '_';
	}
	if (updtstr != '') AlterEstimate('&UpdatePdtsQties='+updtstr);
}

function set_qte(idTC, value)
{
	if (isNaN(quantity = parseInt(document.getElementById('qte'+idTC).value)))
	{
		quantity = dft_qte_list[idTC];
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

function ProcessEstimateChanges(response)
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
						document.getElementById(errorID[0]).innerHTML = errorID[1];
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
								document.location.href = 'EstimateMain.php?' + __SID__ + '&estimateID=' + __ESTIMATE_ID__;
								//var data = outputID[1].split('<?php echo __DATA_SEPARATOR__ ?>');
								//for (var j = 0; j < data.length-1; j+=2) document.getElementById(data[j]).innerHTML = data[j+1] + '€';
								break;
							case 'PaymentMeanValue' :
								document.getElementById('PaymentMeanValue').value = outputID[1];
								document.getElementById('PaymentMean').innerHTML = PaymentMeanList[parseInt(outputID[1])];
								document.getElementById('PaymentMeanMod').href = 'javascript: editPaymentMean()';
								document.getElementById('PaymentMeanMod').innerHTML = 'Modifier';
								break;
							case 'PaymentStatusValue' :
								document.getElementById('PaymentStatusValue').value = outputID[1];
								document.getElementById('PaymentStatus').innerHTML = PaymentStatusList[parseInt(outputID[1])];
								document.getElementById('PaymentStatusMod').href = 'javascript: editPaymentStatus()';
								document.getElementById('PaymentStatusMod').innerHTML = 'Modifier';
								break;
							case 'ProcessingStatusValue' :
								document.getElementById('ProcessingStatusValue').value = outputID[1];
								document.getElementById('ProcessingStatus').innerHTML = ProcessingStatusList[parseInt(outputID[1])];
								document.getElementById('ProcessingStatusMod').href = 'javascript: editProcessingStatus()';
								document.getElementById('ProcessingStatusMod').innerHTML = 'Modifier';
								break;
							case 'UpdatePdtsQties' :
								document.location.href = 'EstimateMain.php?' + __SID__ + '&estimateID=' + __ESTIMATE_ID__;
								break;
							case 'AddProduct' :
								document.location.href = 'EstimateMain.php?' + __SID__ + '&estimateID=' + __ESTIMATE_ID__;
								break;
							case 'DelProduct' :
								document.location.href = 'EstimateMain.php?' + __SID__ + '&estimateID=' + __ESTIMATE_ID__;
								break;
							default:
								document.getElementById(outputID[0]).innerHTML = outputID[1];
						}
					}
				}
			}
			else
			{
				alert('Un problème est survenu au cours de la requête.');
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

//-->