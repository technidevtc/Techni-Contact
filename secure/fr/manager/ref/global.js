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

// Nombre de colonne fixe à gauche et à droite
var fixedColsLeft = 3;
var fixedColsRight = 6;

var wasSupplierShown;
var warPrixPublic;
var margeRemiseOld;
var idTVAdftOld;
var supplierPrevShowRef = -1;

// Charger et afficher le tableau
function loadRefTable() {
  var out = '<center><table border="1" bordercolor="#000000" cellspacing="0" cellpadding="3"><tr>';
  var i, j;

  // Les colonnes
  for (i=0; i<refCols.length; i++) {

    // Colonne definie par l'utilisateur
    if (i >= fixedColsLeft && i < refCols.length - fixedColsRight) {
      out += '<td class="intitule" onContextMenu="showContextMenu(event, ' + i + ', -1); return false" ';

      if (i == colNumberToPaste) {
        out += 'bgcolor="grey"';
      }
      else {
        out += 'onMouseOver="this.style.background=\'#316AC5\'; this.style.color=\'#ffffff\'" ';
        out += 'onMouseOut="this.style.background=\'#E9EFF8\';  this.style.color=\'#000000\'" ';
      }

      out += 'style="cursor: default; margin: 0; padding: 0"><input type="text" class="ref-col"';
      out += 'value="' + refCols[i].replace(regexp, function ($0, $1) {
        return regexpArray[$1];
      }) + '" size="7" ';
      out += 'onBlur="saveColContent(this.value, ' + i + ')"></td>';
    }
    else {
      // si fournisseur, on affiche toutes les colonnes, sinon, on affiche seulement les 2 1ères et la dernière
      if (isSupplier || i <= 1 || i == refCols.length - fixedColsRight + 4) {
        out += '<td class="intitule" onContextMenu="showContextMenu(event, ' + i + ', -1); return false" ';
        out += 'onMouseOver="this.style.background=\'#316AC5\'; this.style.color=\'#ffffff\'" ';
        out += 'onMouseOut="this.style.background=\'#E9EFF8\'; this.style.color=\'#000000\'" ';
        out += 'style="cursor:default"><center>&nbsp;' + refCols[i] + '&nbsp;</center></td>';
      }
    }
  }

  out += '</tr>'

  // Chaque ligne
  for (i = 0; i < refRows.length; ++i) {
    out += '<tr>';

    for (j = 0; j < refRows[i].length; ++j) {
      // si fournisseur, on affiche toutes les colonnes, sinon, on affiche les 2 1ères + les colonnes définies par l'utilisateur + la dernière
      if (isSupplier || j <= 1 || (j >= fixedColsLeft && j < refCols.length - fixedColsRight) || j == refRows[i].length - fixedColsRight + 4) {
        out += '<td  id="ref_tc_'+i+'"  onContextMenu="showContextMenu(event, ' + j + ', ' + i + '); return false" ';

        if ((j == colNumberToPaste && j >= fixedColsLeft && j < refCols.length - fixedColsRight) || i == rowNumberToPaste) {
          out += 'bgcolor="grey"';
        }
        else {
          if (j == 0) {
            out += 'class="intitule" bgcolor="#E9EFF8"';
            out += 'onMouseOver="this.style.background=\'#316AC5\'; this.style.color=\'#ffffff\'" ';
            out += 'onMouseOut="this.style.background=\'#E9EFF8\'; this.style.color=\'#000000\'" ';
            out += 'style="cursor:default';
          }
          // fond rouge pour les colonnes contenant les prix et marge/remise invalides
          else if (isSupplier && j >= refRows[i].length - fixedColsRight + 2 && refRows[i][j] == 'NaN') {
            out += 'bgcolor="#D00000"';
            out += 'onMouseOver="this.style.background=\'#316AC5\'; this.style.color=\'#ffffff\'" ';
            out += 'onMouseOut="this.style.background=\'#D00000\'; this.style.color=\'#000000\'';
          }
          else {
            out += 'bgcolor="#FFFFFF"';
            out += 'onMouseOver="this.style.background=\'#316AC5\'; this.style.color=\'#ffffff\'" ';
            out += 'onMouseOut="this.style.background=\'#FFFFFF\'; this.style.color=\'#000000\'';
          }
        }

        out += '" style="margin: 0; padding: 0">';

        if (j == 0) {
          out += '&nbsp;';
          if (refRows[i][j] == '') out += '<i>nouvelle référence</i>';
          out += refRows[i][j].replace(regexp, function ($0, $1) {
            return regexpArray[$1];
          });
          out += '&nbsp;';
        }
        else if (j == 1) {
          out += '<input type="text" class="ref-col"';
          out += 'value="' + refRows[i][j].replace(regexp, function ($0, $1) {
            return regexpArray[$1];
          }) + '" size="8" ';
          out += 'onBlur="saveCellContent(this, ' + j + ', ' + i + ')">';
        }
        else if (isSupplier && j == 2) {
          out += '<input type="text" class="ref-col"';
          out += 'value="' + refRows[i][j].replace(regexp, function ($0, $1) {
            return regexpArray[$1];
          }) + '" size="19" ';
          out += 'onBlur="saveCellContent(this, ' + j + ', ' + i + ')">';
        }
        else if (isSupplier && j == refRows[i].length - fixedColsRight + 0) {
          out += '<input type="text" class="ref-col"';
          out += 'value="' + refRows[i][j].replace(regexp, function ($0, $1) {
            return regexpArray[$1];
          }) + '" size="4" ';
          out += 'onBlur="saveCellContent(this, ' + j + ', ' + i + ')">';
        }
        // pour la colonne TVA, on met un select
        else if (isSupplier && j == refRows[i].length - fixedColsRight + 1) {
          out += '<select name="idTVA" onBlur="saveCellContent(this, ' + j + ', ' + i + ')">';
          out += displayOptionsTVA(getValidTVA(refRows[i][j], idTVAdft), 'rate only');
          out += '</select>';
        }
        else if (isSupplier && j == refRows[i].length - fixedColsRight + 2) {
          out += '<input type="text" class="ref-col"';
          out += 'value="' + refRows[i][j].replace(regexp, function ($0, $1) {
            return regexpArray[$1];
          }) + '" size="13" ';
          out += 'onBlur="saveCellContent(this, ' + j + ', ' + i + ')">';
        }
        else if (isSupplier && j == refRows[i].length - fixedColsRight + 3) {
          out += '<input type="text" class="ref-col"';
          out += 'value="' + refRows[i][j].replace(regexp, function ($0, $1) {
            return regexpArray[$1];
          }) + '" size="5" ';
          out += 'onBlur="saveCellContent(this, ' + j + ', ' + i + ')">';
        }
        else if (isSupplier && j == refRows[i].length - fixedColsRight + 4) {
          out += '<input type="text" class="ref-col"';
          out += 'value="' + refRows[i][j].replace(regexp, function ($0, $1) {
            return regexpArray[$1];
          }) + '" size="10" ';
          out += 'onBlur="saveCellContent(this, ' + j + ', ' + i + ')">';
        }
        else if (isSupplier && j == refRows[i].length - fixedColsRight + 5) {
          out += '<input type="text" class="ref-col"';
          out += 'value="' + refRows[i][j].replace(regexp, function ($0, $1) {
            return regexpArray[$1];
          }) + '" size="5" ';
          out += 'onBlur="saveCellContent(this, ' + j + ', ' + i + ')">';
        }
        else {
          out += '<input type="text" class="ref-col"';
          out += 'value="' + refRows[i][j].replace(regexp, function ($0, $1) {
            return regexpArray[$1];
          }) + '" size="7" ';
          out += 'onBlur="saveCellContent(this, ' + j + ', ' + i + ')">';
        }
        out += '</td>';
      }
    }

    out += '</tr>';
  }


  out += '</table></center>';

  // Ecrire tableau
  writeData('references', out, 0);

  window.colorReferenceCols && colorReferenceCols();

}

// Generer code reference
function createRefCode() {
  var i, j;
  var code = '';
  var haha = '';

  if (isSupplier) {
    code += refCols.length + '<=>';
    for (i = 0; i < refCols.length; ++i) {
      if (i > 0) code += '<->';
      code += refCols[i].replace(/(<\->|<_>|<=>)/g, '');
    }

    for (i = 0; i < refRows.length; ++i) {
      code += '<_>';

      refRows[i][refRows[i].length - fixedColsRight + 1] = '' + getValidTVA(refRows[i][refRows[i].length - fixedColsRight + 1], idTVAdft);

      for (j = 0; j < refCols.length; ++j) {
        if (j > 0) code += '<->';
        code += refRows[i][j].replace(/(<\->|<_>|<=>)/g, '');
      }
    }
  }
  else {
    code += (refCols.length - fixedColsRight) + '<=>';
    for (i = 0; i < refCols.length; ++i) {
      // on ne copie pas la colonne Référence Fournisseur, on copie les colonnes définies par l'utilisateur, et la colonne de prix
      if ((i != 2 && i < refCols.length - fixedColsRight) || i == refCols.length - fixedColsRight + 4) {
        if (i > 0) code += '<->';
        code += refCols[i].replace(/(<\->|<_>|<=>)/g, '');
      }
    }

    for (i = 0; i < refRows.length; ++i) {
      code += '<_>';
      for (j = 0; j < refCols.length; ++j) {
        if ((j != 2 && j < refCols.length - fixedColsRight) || j == refCols.length - fixedColsRight + 4) {
          if (j > 0) code += '<->';
          code += refRows[i][j].replace(/(<\->|<_>|<=>)/g, '');
        }
      }
    }
  }

  refShown = 0;
  writeData('references', '<input type="hidden" name="code_ref" value="' + code.replace(/"/g, '&quot;') + '">', 0);
}

// Ecrire / ajouter contenu
function writeData(where, what, add) {
  // Zone valide
  if (document.getElementById(where)) {
    // Ajout
    if (add == 1) {
      document.getElementById(where).innerHTML += what;
    }
    else {
      document.getElementById(where).innerHTML = what;
    }
  }
}

// Afficher le tableau de references
function displayRef() {
  refCols[refCols.length - fixedColsRight + 3] = prixPublic ? 'Remise' : 'Marge';
  refCols[refCols.length - fixedColsRight + 4] = isSupplier ? 'Prix Public' : 'Prix';

  wasSupplierShown = typeof(suppliersData[advPrevShowRef]) != 'undefined' ? true : false;
  //wasPrixPublic = (suppliersData[advPrevShowRef][1] == '1') ? true : false;
  if (refShown == 0) {
    refShown = 1;

    if (advPrevShowRef != advCurSelected) reinitLoadRefTable();
    else loadRefTable();
  }
  else {
    if (advPrevShowRef != advCurSelected) reinitLoadRefTable();
  }

  advPrevShowRef = advCurSelected;
  if (isSupplier) supplierPrevShowRef = advCurSelected;
}

// Réinitialise quelques valeurs dans le tableau de references
function reinitLoadRefTable() {
  if (isSupplier) {
    for (j = 0; j < refRows.length; ++j) {
      if (supplierPrevShowRef != -1) // si l'on a en mémoire un fournisseur précédemment montré
      {

        // si la marge qui était présente était celle par défaut, on affecte la nouvelle marge par défaut
        margeRemiseOld = parseFloat(suppliersData[supplierPrevShowRef][2]);
        var margeRemiseRef = parseFloat(refRows[j][refCols.length - fixedColsRight + 3]);
        if (!isNaN(margeRemiseRef)) {
          if (Math.round(margeRemiseRef * 100) / 100 == Math.round(margeRemiseOld * 100) / 100) margeRemiseRef = margeRemiseDft;
          refRows[j][refCols.length - fixedColsRight + 3] = '' + margeRemiseRef;
        }

        // si le taux de TVA est celui qui était par défaut, on affecte le nouveau par défaut
        idTVAdftOld = suppliersData[supplierPrevShowRef][4];
        if (getValidTVA(refRows[j][refCols.length - fixedColsRight + 1], idTVAdft) == idTVAdftOld) refRows[j][refCols.length - fixedColsRight + 1] = '' + idTVAdft;
      }
      else {
        refRows[j][refCols.length - fixedColsRight + 1] = '' + getValidTVA(refRows[j][refCols.length - fixedColsRight + 1], idTVAdft);
      }

      if (!wasSupplierShown) refRows[j][refCols.length - fixedColsRight + 2] = '';
    }

    var colToChange;

    if (wasSupplierShown) {
      if (!prixPublic) colToChange = refCols.length - fixedColsRight + 2;
      else colToChange = refCols.length - fixedColsRight + 4;
    }
    else colToChange = refCols.length - fixedColsRight + 4;

    loadRefTable();

    for (j = 0; j < refRows.length; ++j)
    changeSupplierCellsContent(getCellField('references', 0, 0, colToChange, j + 1), colToChange, j, false);

  }
  else loadRefTable();

}

// Cacher le tableau de references
function hideRef() {
  if (refShown == 1) {
    refShown = 0;

    // Ecraser
    writeData('references', '', 0);
  }
}

// Fermer le menu contextuel
function hideContextMenu() {
  // Si valide
  if (document.getElementById('menu')) {
    document.getElementById('menu').style.visibility = 'hidden';
  }

}

// Afficher le menu de la case x,y
function showContextMenu(e, x, y) {
  // Construire le menu en fonction de la case
  var done = buildContextMenu(x, y);

  // Si menu non vide et objet valide
  if (done == 1 && document.getElementById('menu')) {
    // Positionner et afficher
    document.getElementById('menu').style.position = "fixed";
    document.getElementById('menu').style.top = (getMouse('y', e)-$(window).scrollTop())+"px";
    document.getElementById('menu').style.left = ((getMouse('x', e) + 2)-$(window).scrollLeft())+"px";
    document.getElementById('menu').style.visibility = 'visible';
  }

}

// Obtenir une coordonnee de la souris (x ou y)
function getMouse(which, e) {
  coord = 0;
  switch (which) {
    case 'x': coord = (navigator.appName.indexOf('Microsoft') != -1) ? event.clientX + document.body.scrollLeft : e.pageX; break;
    case 'y': coord = (navigator.appName.indexOf('Microsoft') != -1) ? event.clientY + document.body.scrollTop : e.pageY; break;
  }
  return coord;
}

// Construire le menu de la case x,y
function buildContextMenu(x, y) {
  var data = 0;

  if (new String(x).match(/^[0-9]+$/) && new String(y).match(/^(\-1|[0-9])+$/) && x < refCols.length && y < refRows.length) {

    // Initialiser
    writeData('menu', '');


    // Annuler copie ou cut
    if (colNumberToPaste != 0) {
      if (cut == 0) {
        addMenuItem('Annuler copie colonne', 'cancelColCopy()');
      }
      else {
        addMenuItem('Annuler coupe colonne', 'cancelColCut()');
      }
    }

    // Ajouter des colonnes si x != fixedColsLeft & fixedColsRight colonnes
    if (((isSupplier && x > 1) || (!isSupplier && x > 0)) && x < refCols.length - fixedColsRight) {
      data = 1;
      addMenuItem('Ajouter 1 colonne', 'addRefColumn(1, ' + ((x == 1 ? x + 1 : x) + 1) + ', 1)');
      addMenuItem('Ajouter n colonnes', 'addRefColumn(askHowMany(\'Combien de colonnes souhaitez-vous rajouter ?\'), ' + ((x == 1 ? x + 1 : x) + 1) + ', 1)');
    }

    // Menu couper / copier si x != premiere et fixedColsRight dernières colonnes
    if (x >= fixedColsLeft && x < refCols.length - fixedColsRight) {
      data = 1;
      addMenuItem('Couper colonne', 'cutRefColumn(' + x + ')');
      addMenuItem('Copier colonne', 'copyRefColumn(' + x + ')');
      addMenuItem('Supprimer colonne', 'delRefColumn(' + x + ')');
    }

    // Menu coller si donees a coller + x != fixedColsRight dernières colonnes
    if (colNumberToPaste >= fixedColsLeft && colNumberToPaste < refCols.length - fixedColsRight && x < refCols.length - fixedColsRight) {
      data = 1;
      addMenuItem('Coller colonne', 'pasteRefColumn(' + (x + 1) + ')');
    }

    // Espace vide
    if (data == 1) {
      addMenuItem('&nbsp;', 'void(0)');
    }

    data = 1;

    // Annuler copie ou cut
    if (rowNumberToPaste != -1) {
      if (cut == 0) {
        addMenuItem('Annuler copie ligne', 'cancelRowCopy()');
      }
      else {
        addMenuItem('Annuler coupe ligne', 'cancelRowCut()');
      }
    }

    addMenuItem('Ajouter 1 ligne', 'addRefRow(1, ' + (y + 1) + ', 1)');
    addMenuItem('Ajouter n lignes', 'addRefRow(askHowMany(\'Combien de lignes souhaitez-vous rajouter ?\'), ' + (y + 1) + ', 1)');

    if (y > -1) {
      addMenuItem('Couper ligne', 'cutRefRow(' + y + ')');
      addMenuItem('Copier ligne', 'copyRefRow(' + y + ')');
      addMenuItem('Supprimer ligne', 'delRefRow(' + y + ')');
    }

    if (rowNumberToPaste != -1) {
      addMenuItem('Coller ligne', 'pasteRefRow(' + (y + 1) + ')');
    }


  }

  return data;
}


// Ajouter l'element content dans le menu contextuel
function addMenuItem(content, action) {
  // Effet + action sur le click sur element non vide du menu
  effects = (content != '&nbsp;') ? 'onClick="javascript:' + action + '"; onMouseOver="lightItem(1, this)" onMouseOut="lightItem(0, this)"' : '';

  // Ajouter l'element au menu
  writeData('menu', '<div style="padding-left: 7px; padding-right: 7px"' + effects + '>' + content + '</div>', 1);
}

// Effet graphique (fond + couleur texte) sur l'element o
function lightItem(state, o) {

  // Mouseouver
  if (state == 1) {
    o.style.background = '#316AC5';
    o.style.color = '#ffffff';
  }
  else {
    o.style.background = '#ffffff';
    o.style.color = '#000000';

  }
}

// Effectuer une demande (nb lignes, colonnes) et en retourner le resultat
function askHowMany(what) {
  var answer;

  do {
    answer = window.prompt(what, 1); // Valeur par defaut proposee : 1
  }
  while (answer != null && !answer.match(/^[0-9]+$/)); // null = annuler/close | sinon controle format
  // 0 elements a ajouter si annulation/fermeture
  if (answer == null) {
    answer = 0;
  }

  return answer;
}

// Ajouter n colonnes a partir de la position pos
function addRefColumn(number, pos, reload) {
  var i, j;
  number = parseInt(number);

  // Fermer menu contextuel
  hideContextMenu();

  // Controle format + 0 < position < fixedColsRight dernières colonnes
  if (new String(number).match(/^[1-9][0-9]*$/) && new String(pos).match(/^[0-9]+$/) && pos >= fixedColsLeft && pos <= refCols.length - fixedColsRight) {
    // Decaller les colonnes suivantes
    for (i = refCols.length - 1; i >= pos; --i) {
      // Du nb de colonnes a rajouter
      refCols[i + number] = refCols[i];

      // Chaque ligne de la colonne
      for (j = 0; j < refRows.length; ++j) {
        refRows[j][i + number] = refRows[j][i];
      }

    }

    // Initialiser les nouvelles colonnes
    for (i = pos; i < pos + number; ++i) {
      refCols[i] = '';

      // Pour chaque ligne
      for (j = 0; j < refRows.length; ++j) {
        refRows[j][i] = '';
      }

    }

    // Colonnes rajoutees, decallage de celle coupee/collee
    if (colNumberToPaste != 0 && pos <= colNumberToPaste) {
      colNumberToPaste += number;
    }

    if (reload == 1) {
      // Recharger le tableau
      loadRefTable();
    }
  }
}

// Ajouter n lignes a partir de la position pos
function addRefRow(number, pos, reload) {
  var i, j;
  number = parseInt(number);

  // Fermer menu contextuel
  hideContextMenu();

  // Controle format + 0 < position < derniere colonne
  if (new String(number).match(/^[1-9][0-9]*$/) && new String(pos).match(/^[0-9]*$/)) {
    // Decaller les lignes suivantes
    for (i = refRows.length - 1; i >= pos; --i) {
      // Du nb de lignes a rajouter
      refRows[i + number] = refRows[i];
    }

    // Initialiser les nouvelles lignes
    for (i = pos; i < pos + number; ++i) {
      refRows[i] = new Array();

      for (j = 0; j < refCols.length; ++j) {
        refRows[i][j] = '';
      }

    }

    // Ligne rajoutee, decallage de celle coupee/collee
    if (rowNumberToPaste != -1 && pos <= rowNumberToPaste) {
      rowNumberToPaste += number;
    }

    if (reload == 1) {
      // Recharger le tableau
      loadRefTable();
    }
  }
}

// Supprimer la colonne a la position pos
function delRefColumn(pos) {
  var i, j;

  // Fermer menu contextuel
  hideContextMenu();

  // Format + colonne != fixedColsLeft et fixedColsRight dernières colonnes
  if (new String(pos).match(/^[0-9]+$/) && pos >= fixedColsLeft && pos < refCols.length - fixedColsRight) {
    // Decaler simplement les colonnes suivantes
    for (i = pos; i < refCols.length; ++i) {
      refCols[i] = refCols[i + 1];

      // Pour chaque ligne
      for (j = 0; j < refRows.length; ++j) {
        refRows[j][i] = refRows[j][i + 1];
      }
    }

    for (j = 0; j < refRows.length; ++j) {
      --refRows[j].length;
    }

    --refCols.length;

    // si colonne select == colonne coupee ou collee
    if (pos == colNumberToPaste) {
      cut = colNumberToPaste = 0;
    }


    // Recharger le tableau
    loadRefTable();
  }
}


// Supprimer la ligne a la position pos
function delRefRow(pos) {
  var i;
  
  var id_tc  = $("td#ref_tc_"+pos).text();
  var id_tc_ff = parseInt(id_tc);
  
  
  var id_existe  =  $("#id_ref_tc_delete").val();
  var id_final   = id_existe+"|"+id_tc_ff;
  $("#id_ref_tc_delete").val(id_final);
  
  

  // Fermer menu contextuel
  hideContextMenu();

  // Format
  if (new String(pos).match(/^[0-9]+$/) && pos < refRows.length) {
    // Decaler simplement les colonnes suivantes
    for (i = pos; i < refRows.length; ++i) {
      refRows[i] = refRows[i + 1];

    }

    --refRows.length;

    // si ligne select == ligne coupee ou collee
    if (pos == rowNumberToPaste) {
      Rcut = 0
      rowNumberToPaste = -1;
    }

    // Recharger le tableau
    loadRefTable();
  }
}



// Copier la colonne a la position pos
function copyRefColumn(pos) {

  // Fermer menu contextuel
  hideContextMenu();

  // Format + colonne != premiere et fixedColsRight dernières
  if (new String(pos).match(/^[0-9]+$/) && pos >= fixedColsLeft && pos < refCols.length - fixedColsRight) {
    colNumberToPaste = pos;
    cut = 0;

    // Recharger le tableau
    loadRefTable();
  }

}

// Copier la ligne a la position pos
function copyRefRow(pos) {

  // Fermer menu contextuel
  hideContextMenu();

  // Format
  if (new String(pos).match(/^[0-9]+$/) && pos < refRows.length) {
    rowNumberToPaste = pos;
    Rcut = 0;

    // Recharger le tableau
    loadRefTable();
  }
}

// Couper la colonne a la position pos
function cutRefColumn(pos) {

  // Fermer menu contextuel
  hideContextMenu();

  // Format + colonne != premiere et fixedColsRight dernières
  if (new String(pos).match(/^[0-9]+$/) && pos >= fixedColsLeft && pos < refCols.length - fixedColsRight) {
    colNumberToPaste = pos;
    cut = 1;

    // Recharger le tableau
    loadRefTable();

  }

}

// Couper la ligne a la position pos
function cutRefRow(pos) {

  // Fermer menu contextuel
  hideContextMenu();

  // Format
  if (new String(pos).match(/^[0-9]+$/) && pos < refRows.length) {
    rowNumberToPaste = pos;
    Rcut = 1;

    // Recharger le tableau
    loadRefTable();
  }

}

// Coller la colonne coupee/copiee a la position pos
function pasteRefColumn(pos) {
  var i;

  // Fermer menu contextuel
  hideContextMenu();

  if (colNumberToPaste >= fixedColsLeft && colNumberToPaste < refCols.length - fixedColsRight && new String(pos).match(/^[0-9]+$/) && pos >= fixedColsLeft && pos <= refCols.length - fixedColsRight) {
    // Ajout colonne + copie du contenu
    addRefColumn(1, pos, 0);

    // La nouvelle colonne cree un decallage
/*if(pos <= colNumberToPaste) pris en compte ans addRefColumn
      {
          ++colNumberToPaste;
      }*/

    refCols[pos] = refCols[colNumberToPaste];

    for (i = 0; i < refRows.length; ++i) {
      refRows[i][pos] = refRows[i][colNumberToPaste];
    }

    // Suppression si couper
    if (cut == 1) {
      delRefColumn(colNumberToPaste);
      cut = colNumberToPaste = 0;
    }


    // Recharger le tableau
    loadRefTable();

  }

}

// Coller la ligne coupee/copiee a la position pos
function pasteRefRow(pos) {

  // Fermer menu contextuel
  hideContextMenu();

  var i;

  if (new String(pos).match(/^[0-9]+$/) && pos <= refRows.length) {
    // Ajout ligne + copie du contenu
    addRefRow(1, pos, 0);

    // La nouvelle ligne cree un decallage
/*if(pos <= rowNumberToPaste)   pris en compte dans addRefRow
      {
          ++rowNumberToPaste;
      }*/

    //refRows[pos] = refRows[rowNumberToPaste];	<-- faux, car cela revient non pas a copier la ligne, mais à affecter le pointeur de rowNumberToPaste à pos
    for (i = 0; i < refRows[pos].length; ++i) {
      refRows[pos][i] = refRows[rowNumberToPaste][i];
    }

    // Suppression si couper
    if (Rcut == 1) {
      delRefRow(rowNumberToPaste);
      Rcut = 0;
      rowNumberToPaste = -1;
    }


    // Recharger le tableau
    loadRefTable();

  }

}

// Sauvegarder le contenu value de la colonne a la position pos
function saveColContent(value, pos) {
  // Impossible de modifier la premiere ou aux fixedColsRight dernières colonnes
  if (new String(pos).match(/^[0-9]+$/) && pos >= fixedColsLeft && pos < refCols.length - fixedColsRight) {
    refCols[pos] = value;
  }
}

// Sauvegarder le contenu value d'une cellule x, y
function saveCellContent(field, x, y) {
  if (new String(x).match(/^[0-9]+$/) && x < refCols.length && new String(y).match(/^[0-9]+$/) && y < refRows.length) {
    if (refRows[y][x] != field.value) {
      if (isSupplier) {
        if (x >= refCols.length - fixedColsRight + 2 && x < refCols.length - fixedColsRight + 5) changeSupplierCellsContent(field, x, y, true);
        else if (x == refCols.length - fixedColsRight + 1) field.value = getValidTVA(field.value, idTVAdft);
        refRows[y][x] = field.value;
      }
      else refRows[y][x] = field.value;
    }
  }
}

// Renvoie l'objet input correspond à une cellule donnée
function getCellField(parentDiv, tableNum, inputNum, x, y) {
  return document.getElementById(parentDiv).getElementsByTagName('table')[tableNum].getElementsByTagName('tr')[y].getElementsByTagName('td')[x].getElementsByTagName('input')[inputNum];
}

// Cherche une valeur 'float' valide dans la même colonne à partir d'une cellule donnée
function getColValidValue(x, y) {
  if (x >= 0 && x < refRows[y].length) {
    var fy = y;
    var validValue = Number.NaN;

    while (fy > 0 && isNaN(validValue)) // on cherche une valeur saisie plus haut dans la même colonne
    validValue = parseFloat(refRows[--fy][x]);
    if (isNaN(validValue)) // si pas de valeur trouvée au dessus dans la même colonne
    {
      fy = y;
      while (fy < refRows.length - 1 && isNaN(validValue)) // on cherche une valeur saisie plus bas dans la même colonne
      validValue = parseFloat(refRows[++fy][x]);
      if (isNaN(validValue)) // si pas de valeur trouvée, on regarde s'il n'en existait pas une valide avant dans la même cellule
      validValue = parseFloat(refRows[y][x]);
    }
    return validValue;
  }
  else return Number.NaN;
}

// Change et vérifie le contenu des cellules spécifiques au fournisseur
function changeSupplierCellsContent(field, x, y, allowCheckOtherCells) {
  var inputIndex = x - (refCols.length - fixedColsRight + 2);
  switch (inputIndex) {
  case 0:
    // changement de price2 (prix fournisseur)
    price2Input = field;
    margeRemiseInput = field.parentNode.nextSibling.getElementsByTagName('input')[0];
    priceInput = field.parentNode.nextSibling.nextSibling.getElementsByTagName('input')[0];
    break;

  case 1:
    // changement de la marge ou de la remise
    price2Input = field.parentNode.previousSibling.getElementsByTagName('input')[0];
    margeRemiseInput = field;
    priceInput = field.parentNode.nextSibling.getElementsByTagName('input')[0];
    break;

  case 2:
    // changement de price (prix public)
    price2Input = field.parentNode.previousSibling.previousSibling.getElementsByTagName('input')[0];
    margeRemiseInput = field.parentNode.previousSibling.getElementsByTagName('input')[0];
    priceInput = field;
    break;
  }

  var price2Ref = parseFloat(price2Input.value);
  var margeRemiseRef = parseFloat(margeRemiseInput.value);
  var priceRef = parseFloat(priceInput.value);

  var checkOtherCells = false;

  if (isNaN(price2Ref) && isNaN(margeRemiseRef) && isNaN(priceRef)) {
    priceInput.value = '';
    price2Input.value = '';
    margeRemiseInput.value = '';
  }
  else {
    var pricesok = true;
    var setEmptyAll = false;

    switch (inputIndex) {
    case 0:
      // prix fournisseur
      if (isNaN(price2Ref)) { // si prix fournisseur pas ok
        if (!isNaN(margeRemiseRef) && !isNaN(priceRef)) // si le prix public et la marge ou remise sont valides, on calcul le prix fournisseur suivant type de prix
          price2Ref = priceRef * (1 - margeRemiseRef/100);
        else price2Ref = getColValidValue(x, y); // sinon on affecte le prix éventuellement trouvé dans la même colonne
      }

      if (!isNaN(price2Ref)) { // si prix fournisseur ok
        if (!prixPublic) { // si type de prix fournisseur
          if (!isNaN(margeRemiseRef)) // si marge ok, on calcul le prix public
            priceRef = price2Ref / (1 - margeRemiseRef/100);
          else { // si marge pas ok
            if (!isNaN(priceRef)) // si prix public ok, on calcul la marge
              margeRemiseRef = (1 - price2Ref/priceRef) * 100;
            else { // si prix public pas ok
              if (isNaN(margeRemiseRef = getColValidValue(x + 1, y))) // on affecte une marge valide dans la colonne, sinon on affecte la marge par défaut
              margeRemiseRef = margeRemiseDft;
              priceRef = price2Ref / (1 - margeRemiseRef/100);
            }
          }
        }
        else { // si type de prix public
          if (!isNaN(priceRef)) // si prix public ok, on calcul la remise
            margeRemiseRef = (1 - price2Ref/priceRef) * 100;
          else { // si prix public pas ok
            if (isNaN(margeRemiseRef)) { // si remise pas ok, on affecte celle par défaut, puis calcul prix public
              if (isNaN(margeRemiseRef = getColValidValue(x + 1, y))) // on affecte une remise valide dans la colonne, sinon on affecte la remise par défaut
                margeRemiseRef = margeRemiseDft;
            }
            priceRef = price2Ref / (1 - margeRemiseRef/100);
          }
        }
      }
      else { // si prix fournisseur pas ok
        if (!isNaN(priceRef)) { // si prix public ok, on calcul le prix fournisseur en fonction du type de prix
          if (isNaN(margeRemiseRef)) { // si marge ou remise pas ok
            if (isNaN(margeRemiseRef = getColValidValue(x + 1, y))) // on affecte une marge ou remise valide dans la colonne, sinon on affecte la marge ou remise par défaut
              margeRemiseRef = margeRemiseDft;
          }
          price2Ref = priceRef * (1 - margeRemiseRef/100);
        }
        else { // si prix public pas ok
          if (!isNaN(margeRemiseRef)) { // si marge ou remise ok
            if (margeRemiseRef == margeRemiseDft) setEmptyAll = true; // si la marge ou remise est celle par défaut, on vide le tout
            else pricesok = false; // sinon on prévient que les prix fournisseur et public sont incorrects
          }
          else // si marge ou remise pas ok
          setEmptyAll = true; // on vide le tout
        }
      }
      break;

    case 1:
      // marge
      if (isNaN(margeRemiseRef)) { // si marge ou remise pas ok
        if (!isNaN(price2Ref) && !isNaN(priceRef)) { // si les prix fournisseur et public sont valides, on calcul la marge ou remise
           margeRemiseRef = (1 - price2Ref/priceRef) * 100;
        }
        else margeRemiseRef = margeRemiseDft; // sinon on affecte celle par défaut
      }

      if (!prixPublic) { // si type de prix fournisseur
        if (!isNaN(price2Ref)) // si prix fournisseur ok, on calcul le prix public
          priceRef = price2Ref / (1 - margeRemiseRef/100);
        else { // si prix fournisseur pas ok
          if (!isNaN(priceRef)) // si prix public ok, on calcul le prix fournisseur
            price2Ref = priceRef * (1 - margeRemiseRef/100);
          else {
            if (!isNaN(price2Ref = getColValidValue(x - 1, y))) // s'il existe un prix fournisseur valide dans la même colonne
              priceRef = price2Ref / (1 - margeRemiseRef/100); // on calcul le prix public
            else pricesok = false; // sinon on prévient que les prix fournisseur et public sont incorrects
          }
        }
      }
      else { // si type de prix public
        if (!isNaN(priceRef)) // si prix public ok, on calcul le prix fournisseur
        price2Ref = priceRef * (1 - margeRemiseRef/100);
        else { // si prix public pas ok
          if (!isNaN(price2Ref)) // si prix fournisseur ok, on calcul le prix public
          priceRef = price2Ref / (1 - margeRemiseRef/100);
          else { // sinon on prévient que les prix fournisseur et public sont incorrects
            if (!isNaN(priceRef = getColValidValue(x + 1, y))) // s'il existe un prix public valide dans la même colonne
              price2Ref = priceRef * (1 - margeRemiseRef/100); // on calcul le prix fournisseur
            else pricesok = false; // sinon on prévient que les prix fournisseur et public sont incorrects
          }
        }
      }
      break;

    case 2:
      // prix public
      if (isNaN(priceRef)) { // si prix public pas ok
        if (!isNaN(margeRemiseRef) && !isNaN(price2Ref)) { // si le prix fournisseur et la marge ou remise sont valides, on calcul le prix public suivant type de prix
          priceRef = price2Ref / (1 - margeRemiseRef/100);
        }
        else priceRef = getColValidValue(x, y); // sinon on affecte le prix éventuellement trouvé dans la même colonne
      }

      if (!isNaN(priceRef)) { // si prix public ok
        if (!prixPublic) { // si type de prix fournisseur
          if (!isNaN(price2Ref)) // si prix fournisseur ok, on calcul la marge
            margeRemiseRef = (priceRef / price2Ref - 1) * 100;
          else { // si prix fournisseur pas ok
            if (isNaN(margeRemiseRef)) { // si marge pas ok
              if (isNaN(margeRemiseRef = getColValidValue(x - 1, y))) // on affecte une marge valide dans la colonne, sinon on affecte la marge par défaut
                margeRemiseRef = margeRemiseDft;
            }
            price2Ref = priceRef * (1 - margeRemiseRef/100);
          }
        }
        else { // si type de prix public
          if (!isNaN(margeRemiseRef)) // si remise ok, on calcul le prix fournisseur
          price2Ref = priceRef * (1 - margeRemiseRef/100);
          else { // si remise pas ok
            if (!isNaN(price2Ref)) // si prix fournisseur ok, on calcul la remise
            margeRemiseRef = (1 - priceRef2 / priceRef) * 100;
            else { // si prix fournisseur pas ok
              if (isNaN(margeRemiseRef = getColValidValue(x - 1, y))) // on affecte une remise valide dans la colonne, sinon on affecte la remise par défaut
                margeRemiseRef = margeRemiseDft;
              price2Ref = priceRef * (1 - margeRemiseRef/100);
            }
          }
        }
      }
      else { // si prix public pas ok
        if (!isNaN(price2Ref)) { // si prix fournisseur ok, on calcul le prix public en fonction du type de prix
          if (isNaN(margeRemiseRef)) { // si marge ou remise pas ok
            if (isNaN(margeRemiseRef = getColValidValue(x - 1, y))) // on affecte une marge ou remise valide dans la colonne, sinon on affecte la marge ou remise par défaut
              margeRemiseRef = margeRemiseDft;
          }
          priceRef = price2Ref / (1 - margeRemiseRef/100);
        }
        else { // si prix fournisseur pas ok
          if (!isNaN(margeRemiseRef)) { // si marge ou remise ok
            if (margeRemiseRef == margeRemiseDft) setEmptyAll = true; // si la marge ou remise est celle par défaut, on vide le tout
            else pricesok = false; // sinon on prévient que les prix fournisseur et public sont incorrects
          }
          else // si marge ou remise pas ok
          setEmptyAll = true; // on vide le tout
        }
      }
      break;
    }

    if (setEmptyAll) {
      priceInput.value = '';
      price2Input.value = '';
      margeRemiseInput.value = '';
    }
    else {
      if (!pricesok) {
        priceRef = Number.NaN;
        price2Ref = Number.NaN;
        priceInput.parentNode.style.backgroundColor = '#D00000';
        price2Input.parentNode.style.backgroundColor = '#D00000';
        if (y != rowNumberToPaste) {
          priceInput.parentNode.onmouseout = function () {
            this.style.background = '#D00000';
            this.style.color = '#000000'
          };
          price2Input.parentNode.onmouseout = function () {
            this.style.background = '#D00000';
            this.style.color = '#000000'
          };
        }
      }
      else {
        checkOtherCells = true;
        priceRef = Math.round(priceRef * 100) / 100;
        price2Ref = Math.round(price2Ref * 100) / 100;
        if (refRows[y][refCols.length - fixedColsRight + 4] == 'NaN') {
          if (y == rowNumberToPaste) priceInput.parentNode.style.backgroundColor = '#808080';
          else {
            priceInput.parentNode.style.backgroundColor = '#FFFFFF';
            priceInput.parentNode.onmouseout = function () {
              this.style.background = '#FFFFFF';
              this.style.color = '#000000'
            };
          }
        }
        if (refRows[y][refCols.length - fixedColsRight + 2] == 'NaN') {
          if (y == rowNumberToPaste) price2Input.parentNode.style.backgroundColor = '#808080';
          else {
            price2Input.parentNode.style.backgroundColor = '#FFFFFF';
            price2Input.parentNode.onmouseout = function () {
              this.style.background = '#FFFFFF';
              this.style.color = '#000000'
            };
          }
        }

      }
      priceInput.value = priceRef;
      price2Input.value = price2Ref;
      margeRemiseInput.value = Math.round(margeRemiseRef * 100000) / 100000;
    }
  }

  refRows[y][refCols.length - fixedColsRight + 4] = priceInput.value;
  refRows[y][refCols.length - fixedColsRight + 3] = margeRemiseInput.value;
  refRows[y][refCols.length - fixedColsRight + 2] = price2Input.value;

  if (checkOtherCells && allowCheckOtherCells) { // On vérifie si des lignes invalides peuvent être maj
    var fy = y;
    var invalidValue = false;
    var price2RefTmp, margeRemiseRefTmp, priceRefTmp;

    while (fy > 0 && !invalidValue) { // on cherche une valeur invalide plus haut dans la même colonne
      fy--;
      // au moins une valeur doit être valide parmis les prix fournisseur, public et marge/remise pour que la ligne soit prise en compte
      price2RefTmp = parseFloat(refRows[fy][refCols.length - fixedColsRight + 2]);
      margeRemiseRefTmp = parseFloat(refRows[fy][refCols.length - fixedColsRight + 3]);
      priceRefTmp = parseFloat(refRows[fy][refCols.length - fixedColsRight + 4]);
      if (!(isNaN(price2RefTmp) && isNaN(margeRemiseRefTmp) && isNaN(priceRefTmp))) {
        if (isNaN(price2RefTmp) || isNaN(margeRemiseRefTmp) || isNaN(priceRefTmp)) invalidValue = true;
      }
    }
    if (!invalidValue) { // si pas de valeur invalide trouvée au dessus dans la même colonne
      fy = y;
      while (fy < refRows.length - 1 && !invalidValue) { // on cherche une valeur saisie plus bas dans la même colonne
        fy++;
        // au moins une valeur doit être valide parmis les prix fournisseur, public et marge/remise pour que la ligne soit prise en compte
        price2RefTmp = parseFloat(refRows[fy][refCols.length - fixedColsRight + 2]);
        margeRemiseRefTmp = parseFloat(refRows[fy][refCols.length - fixedColsRight + 3]);
        priceRefTmp = parseFloat(refRows[fy][refCols.length - fixedColsRight + 4]);
        if (!(isNaN(price2RefTmp) && isNaN(margeRemiseRefTmp) && isNaN(priceRefTmp))) {
          if (isNaN(price2RefTmp) || isNaN(margeRemiseRefTmp) || isNaN(priceRefTmp)) invalidValue = true;
        }
      }
      if (invalidValue) { // si valeur invalide trouvée en dessous dans la même colonne
        changeSupplierCellsContent(getCellField('references', 0, 0, x, fy + 1), x, fy, true);
      }
    }
    else { // si valeur invalide trouvée au dessus dans la même colonne
      changeSupplierCellsContent(getCellField('references', 0, 0, x, fy + 1), x, fy, true);
    }
  }
}

// Annuler copie colonne
function cancelColCopy() {
  hideContextMenu();

  colNumberToPaste = 0;

  // Recharger le tableau
  loadRefTable();
}

// Annuler coupe colonne
function cancelColCut() {
  hideContextMenu();

  colNumberToPaste = 0;
  cut = 0;

  // Recharger le tableau
  loadRefTable();
}

// Annuler copie ligne
function cancelRowCopy() {
  hideContextMenu();

  rowNumberToPaste = -1;

  // Recharger le tableau
  loadRefTable();
}

// Annuler coupe ligne
function cancelRowCut() {
  hideContextMenu();

  rowNumberToPaste = -1;
  Rcut = 0;

  // Recharger le tableau
  loadRefTable();
}
