<?php
?>
<script type="text/javascript">
<!--

function getClients(params)
{
	makeRequest('CustomerSearch.php?<?php echo $sid ?>' + params, 'ProcessClients');
}

function ProcessClients(response)
{
	try
	{
		if (response.readyState == 4)
		{
			if (response.status == 200)
			{
				document.getElementById('performingRequestCW').style.visibility = 'hidden';
				document.getElementById('requestResultCW').innerHTML = response.responseText;
				document.getElementById('ClientSearchWindowShad').style.height = document.getElementById('ClientSearchWindow').offsetHeight + 'px';
				if (document.getElementById('_clientID_')) document.getElementById('colr').style.visibility = 'visible';
				else document.getElementById('colr').style.visibility = 'hidden';
			}
			else
			{
				alert('Un problème est survenu au cours de la requête.');
			}
		}
		else
		{
			document.getElementById('performingRequestCW').style.visibility = 'visible';
		}
	}
	catch(e)
	{
		alert("Une exception s'est produite : " + e.description);
	}
}

function showClientSearchWindow()
{
	document.getElementById('ClientSearchWindowShad').style.display = 'inline';
	document.getElementById('ClientSearchWindow').style.display = 'inline';
	document.getElementById('ClientSearchWindowShad').style.height = document.getElementById('ClientSearchWindow').offsetHeight + 'px';
}

function hideClientSearchWindow()
{
	document.getElementById('ClientSearchWindowShad').style.display = 'none';
	document.getElementById('ClientSearchWindow').style.display = 'none';
}

function findCustomer(by_type)
{
	var params = '&searchType=' + by_type;
	
	var clientID = document.RechercheClient.clientID;
	var clientSociety = document.RechercheClient.clientSociety;
	var commandID = document.RechercheClient.commandID;
	
	switch (by_type)
	{
		case 'by_ID' : params += '&clientID=' + clientID.value; clientID.focus(); break;
		case 'by_society' : params += '&clientSociety=' + clientSociety.value; clientSociety.focus(); break;
		case 'by_cmdID' : params += '&commandID=' + commandID.value; commandID.focus(); break;
	}
	
	getClients(params);
}

function checkEnter(by_type, e)
{
	// supporté par ie et firefox, le plus important
	if (e.keyCode == 13)
	{
		findCustomer(by_type);
		return false;
	}
	else return true;
}

function changeClient()
{
	if (document.getElementById('_clientID_'))
	{
		id = parseInt(document.getElementById('_clientID_').innerHTML);
		if (!isNaN(id)) document.getElementById('clientID_edit').value = id;
		hideClientSearchWindow();
	}
}

//-->
</script>
	<div id="ClientSearchWindowShad"></div>
	<div id="ClientSearchWindow">
		<div class="window_title_bar">
			<img class="wtb_close_img" src="../ressources/window_close.gif" onMouseDown="swap_cbtn(this,'down')" onMouseOut="swap_cbtn(this,'out')" onMouseUp="swap_cbtn(this,'up')" />			
			<div onmousedown="grab(document.getElementById('ClientSearchWindow'), document.getElementById('ClientSearchWindowShad'))">
				<img class="wtb_move_img" src="../ressources/window_move.gif" />
				<div class="wtb_text">Rechercher un client</div>
				<div class="zero"></div>
			</div>
		</div>
		<div class="window_bg">
			<div style="text-align: center">
				- <a href="Javascript: getClients('&pattern=_NUMBER_')">0-9</a>
<?php
for($i = ord('A'); $i <= ord('Z'); ++$i)
{
    print '				- <a href="Javascript: getClients(\'&pattern=' . chr($i) . '\')">' . chr($i) . "</a>\n";
}
?>
				-
				<br />
				<form id="RechercheClient" name="RechercheClient" method="get" action="">
					<div class="caption">Options de recherche</div>
					<div id="colr" style="visibility: hidden">
						<input type="button" class="fValidDeux" style="width: 150px" value="Attribuer ce client" onclick="changeClient()" />
					</div>
					<div id="colg">
						<table cellspacing="0" cellpadding="0">
							<tr>
								<td style="width: 175px">- par son id :</td><td style="width: 150px"><input type="text" name="clientID" value="" onkeypress="checkEnter('by_ID', event)" /></td><td style="width: 120px"><input type="button" class="button" value="Rechercher" onclick="findCustomer('by_ID')"></td>
							<!--</tr><tr>
								<td style="width: 175px">- par son nom :</td><td style="width: 150px"><input type="text" name="clientName" value="<?php echo $clientName ?>" onkeypress="checkEnter('by_name', event)" /></td><td style="width: 120px"><input type="button" class="button" value="Rechercher" onclick="findCustomer('by_name')"></td>-->
							</tr><tr>
								<td style="width: 175px">- par le nom de sa société :</td><td style="width: 150px"><input type="text" name="clientSociety" value="" onkeypress="checkEnter('by_society', event)" /></td><td style="width: 120px"><input type="button" class="button" value="Rechercher" onclick="findCustomer('by_society')"></td>
							</tr><tr>
								<td style="width: 175px">- par une de ses commandes :</td><td style="width: 150px"><input type="text" name="commandID" value="" onkeypress="checkEnter('by_cmdID', event)" /></td><td style="width: 120px"><input type="button" class="button" value="Rechercher" onclick="findCustomer('by_cmdID')"></td>
							</tr>
						</table>
					</div>
				</form>
			</div>
			<br />
			<div id="performingRequestCW">Recherche en cours...</div>
			<div id="requestResultCW"></div>
		</div>
	</div>
