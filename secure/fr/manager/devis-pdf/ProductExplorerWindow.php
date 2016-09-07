<?php
?>
<script type="text/javascript">
<!--
function showProductExplorerWindow()
{
	document.getElementById('ProductExplorerWindowShad').style.display = 'inline';
	document.getElementById('ProductExplorerWindow').style.display = 'inline';
	document.getElementById('ProductExplorerWindowShad').style.height = document.getElementById('ProductExplorerWindow').offsetHeight + 'px';
}

function hideProductExplorerWindow()
{
	document.getElementById('ProductExplorerWindowShad').style.display = 'none';
	document.getElementById('ProductExplorerWindow').style.display = 'none';
}

function initPEWindows()
{
	document.getElementById('ProductExplorerFamilyTab').onclick = function () {
		this.style.backgroundColor = '#F4FAFF';
		//document.getElementById('ProductExplorerSupplierTab').style.backgroundColor = '';
		document.getElementById('ProductExplorerSearchTab').style.backgroundColor = '';
		document.getElementById('ProductExplorerFamilyLayer').style.display = 'inline';
		//document.getElementById('ProductExplorerSupplierLayer').style.display = 'none';
		document.getElementById('ProductExplorerSearchLayer').style.display = 'none';
		document.getElementById('ProductExplorerWindowShad').style.height = document.getElementById('ProductExplorerWindow').offsetHeight + 'px';
	}
	/*document.getElementById('ProductExplorerSupplierTab').onclick = function () {
		this.style.backgroundColor = '#F4FAFF';
		document.getElementById('ProductExplorerFamilyTab').style.backgroundColor = '';
		document.getElementById('ProductExplorerSearchTab').style.backgroundColor = '';
		document.getElementById('ProductExplorerSupplierLayer').style.display = 'inline';
		document.getElementById('ProductExplorerFamilyLayer').style.display = 'none';
		document.getElementById('ProductExplorerSearchLayer').style.display = 'none';
		document.getElementById('ProductExplorerWindowShad').style.height = document.getElementById('ProductExplorerWindow').offsetHeight + 'px';
	}*/
	document.getElementById('ProductExplorerSearchTab').onclick = function () {
		this.style.backgroundColor = '#F4FAFF';
		document.getElementById('ProductExplorerFamilyTab').style.backgroundColor = '';
		//document.getElementById('ProductExplorerSupplierTab').style.backgroundColor = '';
		document.getElementById('ProductExplorerSearchLayer').style.display = 'inline';
		document.getElementById('ProductExplorerFamilyLayer').style.display = 'none';
		//document.getElementById('ProductExplorerSupplierLayer').style.display = 'none';
		document.getElementById('ProductExplorerWindowShad').style.height = document.getElementById('ProductExplorerWindow').offsetHeight + 'px';
	}
}

//-->
</script>
	<div id="ProductExplorerWindowShad"></div>
	<div id="ProductExplorerWindow">
		<div class="window_title_bar">
			<img class="wtb_close_img" src="../ressources/window_close.gif" onMouseDown="swap_cbtn(this,'down')" onMouseOut="swap_cbtn(this,'out')" onMouseUp="swap_cbtn(this,'up')" />			
			<div onmousedown="grab(document.getElementById('ProductExplorerWindow'), document.getElementById('ProductExplorerWindowShad'))">
				<img class="wtb_move_img" src="../ressources/window_move.gif" />
				<div class="wtb_text">Sélectionner un produit</div>
				<div class="zero"></div>
			</div>
		</div>
		<ul id="ProductExplorerTabs">
			<li class="PETab" id="ProductExplorerFamilyTab">family</li>
			<!--<li class="PETab" id="ProductExplorerSupplierTab">supplier</li>-->
			<li class="PETab" id="ProductExplorerSearchTab">search</li>
		</ul>
		<div class="zero"></div>
		<div id="ProductExplorerLayers">
<?php
require('ProductExplorerFamilyLayer.php');
?>
			<!--<div class="PELayer" id="ProductExplorerSupplierLayer"></div>-->
			<div class="PELayer" id="ProductExplorerSearchLayer"></div>
		</div>
<script type="text/javascript">initPEWindows();</script>
		<!--
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
		</div>-->
	</div>
