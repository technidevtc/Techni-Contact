<!--
function ToggleActiveState()
{
	AlterCustomer('&toggleActiveState=1');
}

function editLogin()
{
	a = document.getElementById('LoginMod');
	if (a.innerHTML == 'Modifier')
	{
		a.href = "javascript: saveLogin()";
		a.innerHTML = 'Sauver';
		document.getElementById('Login').innerHTML = '<input type="text" id="LoginEdit" class="in_place_edit" value="' + document.getElementById('LoginValue').value + '">';
		document.getElementById('LoginEdit').focus();
	}
}

function editCode(){
	a = document.getElementById('CodeMod');
	if (a.innerHTML == 'Modifier')
	{
		a.href = "javascript: saveCode()";
		a.innerHTML = 'Sauver';
		document.getElementById('Code').innerHTML = '<input type="text" id="CodeEdit" class="in_place_edit" value="' + document.getElementById('CodeValue').value + '">';
		document.getElementById('CodeEdit').focus();
	}
}

function saveLogin(){
	AlterCustomer('&alterLogin=' + document.getElementById('LoginEdit').value);
}

function saveCode()
{
	AlterCustomer('&alterCode=' + document.getElementById('CodeEdit').value);
}

function AlterCustomer(fieldlist)
{
	makeRequest('CustomerAlter.php?' + __SID__ + '&customerID=' + __CUSTOMER_ID__ + fieldlist, 'ProcessCustomerChanges');
}

function ProcessCustomerChanges(response)
{
	
	try
	{
		if (response.readyState == 4)
		{
			if (response.status == 200)
			{
				
				document.getElementById('PerfReqCW').style.visibility = 'hidden';
				mainsplit = response.responseText.split(__MAIN_SEPARATOR__);
				
				var CustomerError = false;
				var errors = mainsplit[0].split(__ERROR_SEPARATOR__);
				for (var i = 0; i < errors.length-1; i++)
				{
					var errorID = errors[i].split(__ERRORID_SEPARATOR__);
					if (errorID.length == 2)
					{
						if (errorID[0] == 'CustomerError')
						{
							CustomerError = true;
							alert("Une ou plusieurs erreurs graves sont intervenues lors de la validation\n"+errorID[1]);
						}
						else document.getElementById(errorID[0]).innerHTML = errorID[1];
					}
				}
				
				if (!CustomerError)
				{
					var output = mainsplit[1].split(__OUTPUT_SEPARATOR__);
					var hideIW = false;
					var hideIE = false;
					for (var i = 0; i < output.length-1; i++)
					{
						var outputID = output[i].split(__OUTPUTID_SEPARATOR__);
						if (outputID.length == 2)
						{
							switch (outputID[0])
							{
								case 'CustomerInfos' :
									var data = outputID[1].split(__DATA_SEPARATOR__);
									for (var j = 0; j < data.length-1; j+=2)
									{
										CustomerInfos[data[j]] = data[j+1];
										if (data[j] == 'titre') {
                      document.getElementById(data[j]+'_label').innerHTML = GetTitle(data[j+1]);
										} else {
                      if (data[j] == 'website_origin')
                        updateWebsiteLogo(data[j+1]);
                      document.getElementById(data[j]+'_label').innerHTML = data[j+1];
                    }
									}
									hideIW = true;
									break;
								case 'CompanyInfos' :
									
									var data = outputID[1].split(__DATA_SEPARATOR__);
									for (var j = 0; j < data.length-1; j+=2)
									{
										CompanyInfos[data[j]] = data[j+1];
										document.getElementById(data[j]+'_label').innerHTML = data[j+1];
									}
									hideIW = true;
									break;
								case 'BillingAddress' :
									var data = outputID[1].split(__DATA_SEPARATOR__);
									for (var j = 0; j < data.length-1; j+=2)
									{
										BillingAddress[data[j]] = data[j+1];
										document.getElementById(data[j]+'_label').innerHTML = data[j+1];
									}
									hideIW = true;
									break;
								case 'ShippingAddress' :
									var data = outputID[1].split(__DATA_SEPARATOR__);
									for (var j = 0; j < data.length-1; j+=2)
									{
										ShippingAddress[data[j]] = data[j+1];
										if (data[j] == 'titre_l')
											document.getElementById(data[j]+'_label').innerHTML = GetTitle(data[j+1]);
										else if (data[j] != 'coord_livraison')
											document.getElementById(data[j]+'_label').innerHTML = data[j+1];
									}
									hideIW = true;
									break;
								case 'LoginValue' :
									document.getElementById('LoginValue').value = outputID[1];
									document.getElementById('Login').innerHTML = outputID[1];
									document.getElementById('LoginMod').href = 'javascript: editLogin()';
									document.getElementById('LoginMod').innerHTML = 'Modifier';
									hideIE = true;
									break;
								case 'CodeValue' :
									document.getElementById('CodeValue').value = outputID[1];
									document.getElementById('Code').innerHTML = outputID[1];
									document.getElementById('CodeMod').href = 'javascript: editCode()';
									document.getElementById('CodeMod').innerHTML = 'Modifier';
									hideIE = true;
									break;
								case 'ActiveState' :
									if (outputID[1] == '0')
									{
										document.getElementById('ActiveState').innerHTML = '<b style="color: #D00000">non actif</b>';
										document.getElementById('ActiveStateMod').innerHTML = 'Activer le compte de ce client';
									}
									else
									{
										document.getElementById('ActiveState').innerHTML = '<b style="color: #00D000">actif</b>';
										document.getElementById('ActiveStateMod').innerHTML = 'D&eacute;sactiver le compte de ce client';
									}
									break;
								default:
									document.getElementById(outputID[0]).innerHTML = outputID[1];
							}
						}
					}
					if (hideIW) HideCustomerInfosAW();
					if (hideIE) document.getElementById('InfosError').innerHTML = '';
				}
			}
			else
			{
				alert('Un problème est survenu au cours de la requête.');
			}
		}
		else
		{
			document.getElementById('PerfReqCW').style.visibility = 'visible';
		}
	}
	catch(e)
	{
		alert("Une exception s'est produite : " + e.description);
	}
}

//-->