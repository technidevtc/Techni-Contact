<?php
?>
<script type="text/javascript">
<!--
function showProductChooserWindow()
{
	document.getElementById('ProductChooserWindowShad').style.display = 'inline';
	document.getElementById('ProductChooserWindow').style.display = 'inline';
	document.getElementById('ProductChooserWindowShad').style.height = document.getElementById('ProductChooserWindow').offsetHeight + 'px';
}

function hideProductChooserWindow()
{
	document.getElementById('ProductChooserWindowShad').style.display = 'none';
	document.getElementById('ProductChooserWindow').style.display = 'none';
}

function initPEWindows()
{
	document.getElementById('ProductChooserFamilyTab').onclick = function () {
		this.style.backgroundColor = '#F4FAFF';
		//document.getElementById('ProductChooserSupplierTab').style.backgroundColor = '';
		document.getElementById('ProductChooserSearchTab').style.backgroundColor = '';
		document.getElementById('ProductChooserFamilyLayer').style.display = 'inline';
		//document.getElementById('ProductChooserSupplierLayer').style.display = 'none';
		document.getElementById('ProductChooserSearchLayer').style.display = 'none';
		document.getElementById('ProductChooserWindowShad').style.height = document.getElementById('ProductChooserWindow').offsetHeight + 'px';
	}
//	document.getElementById('ProductChooserSupplierTab').onclick = function () {
//		this.style.backgroundColor = '#F4FAFF';
//		document.getElementById('ProductChooserFamilyTab').style.backgroundColor = '';
//		document.getElementById('ProductChooserSearchTab').style.backgroundColor = '';
//		document.getElementById('ProductChooserSupplierLayer').style.display = 'inline';
//		document.getElementById('ProductChooserFamilyLayer').style.display = 'none';
//		document.getElementById('ProductChooserSearchLayer').style.display = 'none';
//		document.getElementById('ProductChooserWindowShad').style.height = document.getElementById('ProductChooserWindow').offsetHeight + 'px';
//	}
	document.getElementById('ProductChooserSearchTab').onclick = function () {
		this.style.backgroundColor = '#F4FAFF';
		document.getElementById('ProductChooserFamilyTab').style.backgroundColor = '';
		//document.getElementById('ProductChooserSupplierTab').style.backgroundColor = '';
		document.getElementById('ProductChooserSearchLayer').style.display = 'inline';
		document.getElementById('ProductChooserFamilyLayer').style.display = 'none';
		//document.getElementById('ProductChooserSupplierLayer').style.display = 'none';
		document.getElementById('ProductChooserWindowShad').style.height = document.getElementById('ProductChooserWindow').offsetHeight + 'px';
	}
}

//-->
</script>
	<div id="ProductChooserWindowShad"></div>
	<div id="ProductChooserWindow">
		<div class="window_title_bar">
			<img class="wtb_close_img" src="../ressources/window_close.gif" onMouseDown="swap_cbtn(this,'down')" onMouseOut="swap_cbtn(this,'out')" onMouseUp="swap_cbtn(this,'up')" />			
			<div onmousedown="grab(document.getElementById('ProductChooserWindow'), document.getElementById('ProductChooserWindowShad'))">
				<img class="wtb_move_img" src="../ressources/window_move.gif" />
				<div class="wtb_text">Choix des produits</div>
				<div class="zero"></div>
			</div>
		</div>
		<ul id="ProductChooserTabs">
			<li class="PETab" id="ProductChooserFamilyTab"></li>
			<!--<li class="PETab" id="ProductChooserSupplierTab">supplier</li>-->
			<li class="PETab" id="ProductChooserSearchTab"></li>
		</ul>
		<div class="zero"></div>
		<div id="ProductChooserLayers">
<?php
require('ProductChooserFamilyLayer.php');
?>
			<!--<div class="PELayer" id="ProductChooserSupplierLayer"></div>-->
			<div class="PELayer" id="ProductChooserSearchLayer"></div>
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
