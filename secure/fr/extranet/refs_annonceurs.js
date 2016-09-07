<!--

// Traitement valeurs tableau
var regexp = /(")/g;
var regexpArray = new Array();
regexpArray['"'] = '&quot;';

// Tableau affiche
var refShown = 0;

// Colonne a coller (0 par defaut car incopiable avec la derniere
var colNumberToPaste = 0;
var cut = 0;

// Ligne a coller
var rowNumberToPaste = -1;
var Rcut = 0;

// Charger et afficher le tableau
function loadRefTable()
{
	var out = "\n<table cellspacing=\"0\" cellpadding=\"0\">\n<tr>\n";
	var i, j;

	// Les colonnes
	for(i = 0; i < refCols.length; ++i)
	{
		// Colonne definie par l'utilisateur
		if(i > 1 && i < refCols.length - 1)
		{
			out += '	<th class="' + (i == colNumberToPaste ? 'gray' : 'user')  + '" onContextMenu="showContextMenu(event, ' + i + ', -1); return false" class="user">';
			out += '<input type="text" size="3" ';
			out += 'value="' + refCols[i].replace(regexp, function($0, $1){ return regexpArray[$1]; })  + '" ';
			out += 'onblur="saveColContent(this.value, ' + i + ')"></th>' + "\n";
		}
		else
		{
			out += '	<th onContextMenu="showContextMenu(event, ' + i + ', -1); return false" >';
			out += '&nbsp;' + refCols[i] + "&nbsp;</th>\n";
		}
	}

	out += "</tr>\n";

	// Chaque ligne
	for(i = 0; i < refRows.length; ++i)
	{
		out += "<tr>\n";
		
		for(j = 0; j < refRows[i].length; ++j)
		{
			if((j == colNumberToPaste && j > 0 && j < refCols.length - 1) || i == rowNumberToPaste)
				gray = true
			else gray = false
			
			if (j == 0)
			{
				out += '	<td class="refTC" onContextMenu="showContextMenu(event, ' + j + ', ' + i + '); return false">';
				if (refRows[i][j] == '') out += '<i>nouvelle référence</i>';
				else out += refRows[i][j].replace(regexp, function($0, $1){ return regexpArray[$1]; });
				out += "	</td>\n";
			}
			else
			{
				out += '	<td ' + (gray ? 'class="gray" ' : '');
				out += 'onContextMenu="showContextMenu(event, ' + j + ', ' + i + '); return false">';
				out += '<input ' + (j == refRows[i].length-1 ? 'class="Right" ' : '') + 'type="text" size="3" ';
				out += 'value="' + refRows[i][j].replace(regexp, function($0, $1){ return regexpArray[$1]; }) + '" ';
				out += 'onblur="saveCellContent(this.value, ' + j + ', ' + i + ')">';
				out += "	</td>\n";
			}
		}
		out += "</tr>\n";
	}
	
	out += "</table>\n";
	
	// Ecrire tableau
	writeData('references', out, 0);
	
	ths = document.getElementById('references').getElementsByTagName('th');
	tds = document.getElementById('references').getElementsByTagName('td');
	
	var k;
	for (k = 0; k < ths.length; k++)
	{
		if (ths[k].className == 'user')
		{
			ths[k].i = ths[k].getElementsByTagName('input')[0];
			ths[k].onmouseover = function () { this.style.backgroundColor = '#316AC5'; this.i.style.backgroundColor = '#316AC5'; this.i.style.color = '#FFFFFF'; }
			ths[k].onmouseout = function () { this.style.backgroundColor = '#F4FAFF'; this.i.style.backgroundColor = '#F4FAFF'; this.i.style.color = '#000000'; }
		}
		else if (ths[k].className != 'gray')
		{
			ths[k].onmouseover = function () { this.style.backgroundColor = '#316AC5'; this.style.color = '#ffffff'; }
			ths[k].onmouseout = function () { this.style.backgroundColor = '#E9EFF8'; this.style.color = '#000000'; }
		}
	}
	for (k = 0; k < tds.length; k++)
	{
		if (tds[k].className == 'refTC')
		{
			tds[k].onmouseover = function () { this.style.backgroundColor = '#316AC5'; }
			tds[k].onmouseout = function () { this.style.backgroundColor = '#E9EFF8'; }
		}
		else if (tds[k].className != 'gray')
		{
			tds[k].i = tds[k].getElementsByTagName('input')[0];
			tds[k].onmouseover = function () { this.style.backgroundColor = '#316AC5'; this.i.style.backgroundColor = '#316AC5'; this.i.style.color = '#FFFFFF'; }
			tds[k].onmouseout = function () { this.style.backgroundColor = '#FFFFFF'; this.i.style.backgroundColor = '#FFFFFF'; this.i.style.color = '#000000'; }
		}
	}
	

	////////////////////////////////////////////

	/*debug  = '<br><br><br><br>Déboggage : <br><br>Nombre de lignes : ' + refRows.length + '<br>Nombre de colonnes : ' + refCols.length;
	debug += '<br><br>Nombre de colonnes de chaque ligne : <br>';

	for(i = 0; i < refRows.length; ++i)
	{
		debug += 'ligne ' + i + ' : ' + refRows[i].length + '<br>';
	}


	writeData('references', debug, 1);
	*/

	//writeData('references', '<br><br><input type="button" value="Générer le code transmis" onClick="createRefCode()">', 1);


	///////////////////////////////////////////
}


// Generer code reference
function createRefCode()
{
	var i, j;
	var code = refCols.length + '<=>';

	for(i = 0; i < refCols.length; ++i)
	{
		if(i > 0) code += '<->';
		code += refCols[i].replace(/(<\->|<_>|<=>)/g, '');
	}
	
	for(i = 0; i < refRows.length; ++i)
	{
		code += '<_>';
		for(j = 0; j < refCols.length; ++j)
		{
			if(j > 0) code += '<->';
			code += refRows[i][j].replace(/(<\->|<_>|<=>)/g, '');;
			//alert("i="+i+" j="+j+"\nrefRows="+refRows[i][j]+"\n"+code);
		}
	}

	refShown = 0;
	writeData('refcode', '<input type="hidden" name="code_ref" value="' + code.replace(/"/g, '&quot;') + '">', 0);
}


// Ecrire / ajouter contenu
function writeData(where, what, add)
{
	// Zone valide
	if(document.getElementById(where))
	{
		if(add == 1) // Ajout
			document.getElementById(where).innerHTML += what;
		else
			document.getElementById(where).innerHTML = what;
	}
}


// Afficher le tableau de references
function displayRef()
{
	if(refShown == 0)
	{
		refShown = 1;
		loadRefTable();
	}
}


// Cacher le tableau de reference
function hideRef()
{
	if(refShown == 1)
	{
		refShown = 0;
		writeData('references', '', 0); // Ecraser
	}
}


// Fermer le menu contextuel
function hideContextMenu()
{
	// Si valide
	if(document.getElementById('menu'))
		document.getElementById('menu').style.visibility = 'hidden';
}


// Afficher le menu de la case x,y
function showContextMenu(e, x, y)
{
	// Construire le menu en fonction de la case
	var done = buildContextMenu(x, y);

	// Si menu non vide et objet valide
	if(done == 1 && document.getElementById('menu'))
	{
		// Positionner et afficher
		document.getElementById('menu').style.top        = getMouse('y', e) + 'px';
		document.getElementById('menu').style.left       = (getMouse('x', e) + 2) + 'px';
		document.getElementById('menu').style.visibility = 'visible';
	}
}

// Obtenir une coordonnee de la souris (x ou y)
function getMouse(which, e)
{
	coord = 0;
	switch(which)
	{
		case 'x' : coord = (navigator.appName.indexOf('Microsoft') != -1) ? event.clientX + document.body.scrollLeft + document.documentElement.scrollLeft : e.pageX; break;
		case 'y' : coord = (navigator.appName.indexOf('Microsoft') != -1) ? event.clientY + document.body.scrollTop + document.documentElement.scrollTop : e.pageY; break;
	}
	return coord;
}


// Construire le menu de la case x,y
function buildContextMenu(x, y)
{
	var data = 0;

	if(new String(x).match(/^[0-9]+$/) && new String(y).match(/^(\-1|[0-9])+$/) && x < refCols.length && y < refRows.length)
	{
		
		// Initialiser
		writeData('menu', '');
		
		
		// Annuler copie ou cut
		if(colNumberToPaste != 0)
		{
			if(cut == 0) addMenuItem('Annuler copie colonne', 'cancelColCopy()');
			else addMenuItem('Annuler coupe colonne', 'cancelColCut()');
		}
		
		// Ajouter des colonnes si x != derniere colonne
		if(x > 0 && x < refCols.length - 1)
		{
			data = 1;
			addMenuItem('Ajouter 1 colonne', 'addRefColumn(1, ' + (x + 1) + ', 1)');
			addMenuItem('Ajouter n colonnes', 'addRefColumn(askHowMany(\'Combien de colonnes souhaitez-vous rajouter ?\'), ' + (x + 1) + ', 1)');
		}
		
		// Menu couper / copier si colonne utilisateur
		if(x > 1 && x < refCols.length - 1)
		{
			data = 1;
			addMenuItem('Couper colonne', 'cutRefColumn(' + x + ')');
			addMenuItem('Copier colonne', 'copyRefColumn(' + x + ')');
			addMenuItem('Supprimer colonne', 'delRefColumn(' + x + ')');
		}
		
		// Menu coller si colonne utilisateur
		if(colNumberToPaste > 1 && colNumberToPaste < refCols.length - 1 && x < refCols.length - 1)
		{
			data = 1;
			addMenuItem('Coller colonne', 'pasteRefColumn(' + (x + 1) + ')');
		}
		
		// Espace vide
		if(data == 1) addMenuItem('&nbsp;', 'void(0)');
		
		data = 1;
		
		// Annuler copie ou cut
		if(rowNumberToPaste != -1)
		{
			if(cut == 0) addMenuItem('Annuler copie ligne', 'cancelRowCopy()');
			else addMenuItem('Annuler coupe ligne', 'cancelRowCut()');
		}
		
		addMenuItem('Ajouter 1 ligne',  'addRefRow(1, ' + (y + 1) + ', 1)');
		addMenuItem('Ajouter n lignes', 'addRefRow(askHowMany(\'Combien de lignes souhaitez-vous rajouter ?\'), ' + (y + 1) + ', 1)');
		
		if(y > -1)
		{
			addMenuItem('Couper ligne', 'cutRefRow(' + y + ')');
			addMenuItem('Copier ligne', 'copyRefRow(' + y + ')');
			addMenuItem('Supprimer ligne', 'delRefRow(' + y + ')');
		}
		
		if(rowNumberToPaste != -1)
			addMenuItem('Coller ligne', 'pasteRefRow(' + (y + 1) + ')');
		
	}
	
	return data;
}


// Ajouter l'element content dans le menu contextuel
function addMenuItem(content, action)
{
	// Effet + action sur le click sur element non vide du menu
	effects = (content != '&nbsp;') ? 'onClick="javascript:' + action + '"; onMouseOver="lightItem(1, this)" onMouseOut="lightItem(0, this)"' : '';
	// Ajouter l'element au menu
	writeData('menu', '<div style="padding-left: 7px; padding-right: 7px"' + effects + '>' + content + '</div>', 1);
}

// Effet graphique (fond + couleur texte) sur l'element o
function lightItem(state, o)
{
	// Mouseouver
	if(state == 1)
	{
		o.style.background = '#316AC5';
		o.style.color      = '#ffffff';
	}
	else
	{
		o.style.background ='#ffffff';
		o.style.color      = '#000000';
	}
}

// Effectuer une demande (nb lignes, colonnes) et en retourner le resultat
function askHowMany(what)
{
	var answer;

	do answer = window.prompt(what, 1);	// Valeur par defaut proposee : 1
	while(answer != null && !answer.match(/^[0-9]+$/));  // null = annuler/close | sinon controle format

	// 0 elements a ajouter si annulation/fermeture
	if(answer == null)
	{
		answer = 0;
	}

	return answer;
}


// Ajouter n colonnes a partir de la position pos
function addRefColumn(number, pos, reload)
{
	var i, j;
	number = parseInt(number);

	// Fermer menu contextuel
	hideContextMenu();

	// Controle format + 0 < position < derniere colonne
	if(new String(number).match(/^[1-9][0-9]*$/) && new String(pos).match(/^[1-9][0-9]*$/) && pos < refCols.length)
	{
		// Decaller les colonnes suivantes
		for(i = refCols.length - 1; i >= pos; --i)
		{
			// Du nb de colonnes a rajouter
			refCols[i + number] = refCols[i];
			// Chaque ligne de la colonne
			for(j = 0; j < refRows.length; ++j) refRows[j][i + number] = refRows[j][i];
		}

		// Initialiser les nouvelles colonnes
		for(i = pos; i < pos + number; ++i)
		{
			refCols[i] = '';
			// Pour chaque ligne
			for(j = 0; j < refRows.length; ++j) refRows[j][i] = '';
		}

		// Colonnes rajoutees, decallage de celle coupee/collee
		if(colNumberToPaste > 1 && pos <= colNumberToPaste) colNumberToPaste += number;
		
		// Recharger le tableau
		if(reload == 1) loadRefTable();
	}
}


// Ajouter n lignes a partir de la position pos
function addRefRow(number, pos, reload)
{
	var i, j;
	number = parseInt(number);

	// Fermer menu contextuel
	hideContextMenu();

	// Controle format + 0 < position < derniere colonne
	if(new String(number).match(/^[1-9][0-9]*$/) && new String(pos).match(/^[0-9]*$/))
	{
		// Decaller les lignes suivantes du nb de lignes à rajouter
		for(i = refRows.length - 1; i >= pos; --i) refRows[i + number] = refRows[i];
		
		// Initialiser les nouvelles lignes
		for(i = pos; i < pos + number; ++i)
		{
			refRows[i] = new Array();
			for(j = 0; j < refCols.length; ++j) refRows[i][j] = '';
		}
		
		// Ligne rajoutee, decallage de celle coupee/collee
		if(rowNumberToPaste != -1 && pos <= rowNumberToPaste)
		   rowNumberToPaste += number;
		
		// Recharger le tableau
		if(reload == 1) loadRefTable();
	}
}


// Supprimer la colonne a la position pos
function delRefColumn(pos)
{
	var i, j;

	// Fermer menu contextuel
	hideContextMenu();

	// Format + colonne != premiere et derniere
	if(new String(pos).match(/^[1-9][0-9]*$/) && pos < refCols.length - 1)
	{
		// Decaler simplement les colonnes suivantes
		for(i = pos; i < refCols.length; ++i)
		{
			refCols[i] = refCols[i + 1];
			// Pour chaque ligne
			for(j = 0; j < refRows.length; ++j) refRows[j][i] = refRows[j][i + 1];
		}
		
		for(j = 0; j < refRows.length; ++j) --refRows[j].length;
		
		--refCols.length;
		
		// si colonne select == colonne coupee ou collee
		if(pos == colNumberToPaste) cut = colNumberToPaste = 0;
		
		// Recharger le tableau
		loadRefTable();
	}
}


// Supprimer la ligne a la position pos
function delRefRow(pos)
{
	var i;

	// Fermer menu contextuel
	hideContextMenu();

	// Format
	if(new String(pos).match(/^[0-9]+$/) && pos < refRows.length)
	{
		// Decaler simplement les colonnes suivantes
		for(i = pos; i < refRows.length; ++i) refRows[i] = refRows[i + 1];
		
		--refRows.length;
		
		// si ligne select == ligne coupee ou collee
		if(pos == rowNumberToPaste)
		{
			Rcut = 0
			rowNumberToPaste = -1;
		}
		
		// Recharger le tableau
		loadRefTable();
	}
}


// Copier la colonne a la position pos
function copyRefColumn(pos)
{

	// Fermer menu contextuel
	hideContextMenu();

	// Format + colonne != premiere et derniere
	if(new String(pos).match(/^[1-9][0-9]*$/) && pos < refCols.length - 1)
	{
		colNumberToPaste = pos;
		cut = 0;
		// Recharger le tableau
		loadRefTable();
	}

}


// Copier la ligne a la position pos
function copyRefRow(pos)
{

	// Fermer menu contextuel
	hideContextMenu();

	// Format
	if(new String(pos).match(/^[0-9]+$/) && pos < refRows.length)
	{
		rowNumberToPaste = pos;
		Rcut = 0;
		// Recharger le tableau
		loadRefTable();
	}
}


// Couper la colonne a la position pos
function cutRefColumn(pos)
{

	// Fermer menu contextuel
	hideContextMenu();

	// Format + colonne != premiere et derniere
	if(new String(pos).match(/^[1-9][0-9]*$/) && pos < refCols.length - 1)
	{
		colNumberToPaste = pos;
		cut = 1;
		// Recharger le tableau
		loadRefTable();
	}
}


// Couper la ligne a la position pos
function cutRefRow(pos)
{

	// Fermer menu contextuel
	hideContextMenu();

	// Format
	if(new String(pos).match(/^[0-9]+$/) && pos < refRows.length)
	{
		rowNumberToPaste = pos;
		Rcut = 1;
		// Recharger le tableau
		loadRefTable();
	}
}


// Coller la colonne coupee/copiee a la position pos
function pasteRefColumn(pos)
{
	var i;

	// Fermer menu contextuel
	hideContextMenu();

	if(colNumberToPaste > 1 && colNumberToPaste < refCols.length - 1 && new String(pos).match(/^[0-9]+$/) && pos != refCols.length)
	{
		// Ajout colonne + copie du contenu
		addRefColumn(1, pos, 0);

		// La nouvelle colonne cree un decallage
		/*if(pos <= colNumberToPaste) pris en compte ans addRefColumn
		{
			++colNumberToPaste;
		}*/
		
		refCols[pos] = refCols[colNumberToPaste];
		
		for(i = 0; i < refRows.length; ++i)
			refRows[i][pos] = refRows[i][colNumberToPaste];
		
		// Suppression si couper
		if(cut == 1)
		{
			delRefColumn(colNumberToPaste);
			cut = colNumberToPaste = 0;
		}
		
		// Recharger le tableau
		loadRefTable();
	}
}


// Coller la ligne coupee/copiee a la position pos
function pasteRefRow(pos)
{

	// Fermer menu contextuel
	hideContextMenu();

	if(new String(pos).match(/^[0-9]+$/) && pos <= refRows.length)
	{
		// Ajout ligne + copie du contenu
		addRefRow(1, pos, 0);

		// La nouvelle ligne cree un decallage
		/*if(pos <= rowNumberToPaste)   pris en compte dans addRefRow
		{
			++rowNumberToPaste;
		}*/

		refRows[pos] = refRows[rowNumberToPaste];

		// Suppression si couper
		if(Rcut == 1)
		{
			delRefRow(rowNumberToPaste);
			Rcut = 0;
			rowNumberToPaste = -1;
		}
		
		// Recharger le tableau
		loadRefTable();
	}
}


// Sauvegarder le contenu value de la colonne a la position pos
function saveColContent(value, pos)
{
	// Impossible de modifier la premiere ou la derniere colonne
	if(new String(pos).match(/^[1-9][0-9]*$/) && pos < refCols.length - 1)
		refCols[pos] = value;
}


// Sauvegarder le contenu value d'une cellule x, y
function saveCellContent(value, x, y)
{
	if(new String(x).match(/^[0-9]+$/) && x < refCols.length && new String(y).match(/^[0-9]+$/) && y < refRows.length)
		refRows[y][x] = value;
}


// Annuler copie colonne
function cancelColCopy()
{
	hideContextMenu();

	colNumberToPaste = 0;

	// Recharger le tableau
	loadRefTable();
}

// Annuler coupe colonne
function cancelColCut()
{
	hideContextMenu();

	colNumberToPaste = 0;
	cut = 0;

	// Recharger le tableau
	loadRefTable();
}


// Annuler copie ligne
function cancelRowCopy()
{
	hideContextMenu();

	rowNumberToPaste = -1;

	// Recharger le tableau
	loadRefTable();
}


// Annuler coupe ligne
function cancelRowCut()
{
	hideContextMenu();

	rowNumberToPaste = -1;
	Rcut = 0;

	// Recharger le tableau
	loadRefTable();
}

//-->