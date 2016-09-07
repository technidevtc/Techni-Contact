<?php
?>
<script type="text/javascript">
<!--
function toggle_showCoordLivraison()
{
	if (document.getElementsByName('toggle_coordlivraison')[0].checked)
	{
		document.getElementById('coordlivraisonshown').style.display  = 'none';
		document.getElementById('coordlivraisonmsg').innerHTML  = 'Cliquez ici si les coordonnées de livraison sont différentes de celles de facturation';
	}
	else
	{
		document.getElementById('coordlivraisonshown').style.display  = 'block';
		document.getElementById('coordlivraisonmsg').innerHTML  = 'Cliquez ici si les coordonnées de livraison sont les mêmes que celles de facturation';
	}
	
	document.getElementById('CoordChangeWindowShad').style.height = document.getElementById('CoordChangeWindow').offsetHeight + 'px';
}
function trim(s)
{
	return s.replace(/(^\s*)|(\s*$)/g, '');
}

function showCoordChangeWindow()
{
	if (document.getElementById('coord_livraison').value != '1')
	{
		document.getElementsByName('toggle_coordlivraison')[0].checked = true;
		toggle_showCoordLivraison();
	}
	
	switch (document.getElementById('titre_l_fixed').innerHTML)
	{
		case 'M.'   : document.coord.titre_l.value = 1; break;
		case 'Mlle' : document.coord.titre_l.value = 2; break;
		case 'Mme'  : document.coord.titre_l.value = 3; break;
		default : document.coord.titre_l.value = 1; break;
	}
	document.coord.nom_l.value = document.getElementById('nom_l_fixed').innerHTML;
	document.coord.prenom_l.value = document.getElementById('prenom_l_fixed').innerHTML;
	document.coord.societe_l.value = document.getElementById('societe_l_fixed').innerHTML;
	document.coord.adresse_l.value = document.getElementById('adresse_l_fixed').innerHTML;
	document.coord.complement_l.value = document.getElementById('complement_l_fixed').innerHTML;
        document.coord.tel2.value = document.getElementById('tel2_fixed').innerHTML;
	document.coord.cp_l.value = document.getElementById('cp_l_fixed').innerHTML;
	document.coord.ville_l.value = document.getElementById('ville_l_fixed').innerHTML;
	document.coord.pays_l.value = document.getElementById('pays_l_fixed').innerHTML;
	document.coord.infos_sup_l.innerHTML = document.getElementById('infos_sup_l_fixed').innerHTML;
	document.getElementById('CoordChangeWindowShad').style.display = 'inline';
	document.getElementById('CoordChangeWindow').style.display = 'inline';
	document.getElementById('CoordChangeWindowShad').style.height = document.getElementById('CoordChangeWindow').offsetHeight + 'px';
}

function hideCoordChangeWindow()
{
	document.getElementById('CoordChangeWindowShad').style.display = 'none';
	document.getElementById('CoordChangeWindow').style.display = 'none';
	document.getElementById('ShipAddressError').innerHTML = '<br />';
}

function saveShipAddress()
{
	var fieldlist = '';
	if (document.coord.toggle_coordlivraison.checked)
	{
		fieldlist += '&coord_livraison=0';
	}
	else
	{
		fieldlist += '&coord_livraison=1';
		fieldlist += '&titre_l=' + escape(document.coord.titre_l.value);
		fieldlist += '&nom_l=' + escape(document.coord.nom_l.value);
		fieldlist += '&prenom_l=' + escape(document.coord.prenom_l.value);
		fieldlist += '&societe_l=' + escape(document.coord.societe_l.value);
		fieldlist += '&adresse_l=' + escape(document.coord.adresse_l.value);
		fieldlist += '&complement_l=' + escape(document.coord.complement_l.value);
		fieldlist += '&cp_l=' + escape(encodeURI(document.coord.cp_l.value));
		fieldlist += '&ville_l=' + escape(document.coord.ville_l.value);
		fieldlist += '&pays_l=' + escape(document.coord.pays_l.value);
                fieldlist += '&tel2=' + escape(document.coord.tel2.value);
		fieldlist += '&infos_sup_l=' + escape(encodeURI(document.coord.infos_sup_l.value));
	}
	AlterCommand('&alterShipAddress=1' + fieldlist);
}

//-->
</script>
	<div id="CoordChangeWindowShad"></div>
	<div id="CoordChangeWindow">
		<div class="window_title_bar">
			<img class="wtb_close_img" src="../ressources/window_close.gif" onMouseDown="swap_cbtn(this,'down')" onMouseOut="swap_cbtn(this,'out')" onMouseUp="swap_cbtn(this,'up')" />			
			<div onmousedown="grab(document.getElementById('CoordChangeWindow'), document.getElementById('CoordChangeWindowShad'))">
				<img class="wtb_move_img" src="../ressources/window_move.gif" />
				<div class="wtb_text">Modifier les coordonnées de livraison</div>
				<div class="zero"></div>
			</div>
		</div>
		<div class="window_bg">
			<form id="coord" name="coord" method="post" action="commande_coord.html">
				<div class="titreBloc">Coordonnées de livraison</div>
				<div class="coordlivraison">
					<input type="checkbox" name="toggle_coordlivraison" onclick="toggle_showCoordLivraison()" />
					<span id="coordlivraisonmsg">Cliquez ici si les coordonnées de livraison sont <?php echo $cmd->coord['coord_livraison'] == 1 ? 'les mêmes que celles de facturation' : 'différentes de celles de facturation' ?></span>
				</div>
				<div id="coordlivraisonshown">
					<div id="ShipAddressError"><br /></div>
					<div class="coordfloat" style="margin-right: 50px"><div class="intitule">Titre :</div><select name="titre_l" class="titre"><option value="1">M.</option><option value="2">Mlle</option><option value="3">Mme</option></select></div>
					<div class="coordfloat"><div class="intitule">Nom :</div><input type="text" name="nom_l" size="20" maxlength="20" value="" onBlur="this.value = trim(this.value.toUpperCase())" /></div>
					<div class="coordfloatR"><div class="intitule">Prénom :</div><input type="text" name="prenom_l" size="20" maxlength="20" value="" onBlur="this.value = trim(this.value); maj = this.value.charAt(0).toUpperCase(); if(this.value.length > 1) this.value = maj + this.value.substring(1, this.value.length); else this.value = maj" /></div>
					<div class="zero"></div>
					<div class="coord_norm"><div class="intitule">Société :</div><input type="text" name="societe_l" size="50" maxlength="50" value="" onBlur="this.value = trim(this.value); maj = this.value.charAt(0).toUpperCase(); if(this.value.length > 1) this.value = maj + this.value.substring(1, this.value.length); else this.value = maj" /></div>
					<div class="coord_norm"><div class="intitule">Adresse :</div><input type="text" name="adresse_l" size="70" maxlength="255" value="" /></div>
					<div class="coordfloat"><div class="intitule">Complément (ZI, BP, etc) :</div><input type="text" name="complement_l" size="20" maxlength="20" value="" /></div>
					<div class="coordfloatR"><div class="intitule">Tel :</div><input type="text" name="tel2" size="20" maxlength="20" value="" /></div>
                                        <div class="zero"></div>
                                        <div class="coordfloat" style="margin-right: 50px"><div class="intitule">CP :</div><input type="text" name="cp_l" size="5" maxlength="6" value="" onBlur="this.value = trim(parseInt(this.value))" /></div>
					<div class="coordfloat"><div class="intitule">Ville :</div><input type="text" name="ville_l" size="20" maxlength="25" value="" onBlur="this.value = trim(this.value.toUpperCase())" /></div>
					<div class="coordfloatR"><div class="intitule">Pays :</div><input type="text" name="pays_l" size="20" maxlength="25" value="" onBlur="this.value = trim(this.value.toUpperCase())" /></div>
					<div class="zero"></div>
					<div class="intitule_area">Instructions de livraison :</div>
					<textarea type="text" name="infos_sup_l" cols="110" rows="2"/></textarea>
					<br/>
				</div>
				<div><input class="fValidTrois" type="button" value="Valider" onclick="saveShipAddress()" /></div>
			</form>
		</div>
	</div>
