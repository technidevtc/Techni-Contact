<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 18 juin 2005

 Fichier : /secure/manager/newsletter/main.php
 Description : Page principale newsletter

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$title = $navBar = 'Newsletter';
require(ADMIN . 'head.php');



?>
<script language="JavaScript">
<!--


//////////////////////////////////////////////////////////////
// Ensemble code JS ancienne version
//////////////////////////////////////////////////////////////


/************************************************************************************************/
/*	Duch  [www.icilalune.com]	gregory@icilalune.com 					*/
/*	Nabab [ITSys] 	                nabab@gmx.fr						*/
/************************************************************************************************/

var style = new Array('g','/g','i','/i','s','/s','email','/email','lien=','/lien','img','/img','quote','/quote','fixed','/fixed','cpp','/cpp','url','/url');
var stockage = new Array('0','0','0','0','0','0','0','0','0','0','0','0','0','0','0','0','0','0','0','0','0','0','0','0','0','0','0');
lien="http://";

function MM_findObj(n, d) { //v3.0
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i>d.layers.length;i++) x=MM_findObj(n,d.layers[i].document); return x;
}

function storeCaret (textEl)
{
	if (textEl.createTextRange) 
		textEl.caretPos = document.selection.createRange().duplicate();
}

function insertAtCaret (textEl, text)
{
	if (textEl.createTextRange && textEl.caretPos)
	{
		var caretPos = textEl.caretPos;
		caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? text + ' ' : text;
	}
}

function insertInCaret (textEl, text, text2)
{
	if (textEl.createTextRange && textEl.caretPos)
	{
		var caretPos = textEl.caretPos;
		selectedtext = caretPos.text;
		caretPos.text =	caretPos.text.charAt(caretPos.text.length - 1) == '' ? text + '' : text;
		caretPos.text = caretPos.text + selectedtext + text2;
	}
}

function palette(id,vernav)
{
    if (vernav>=4)
    {
 	  MM_findObj('contenu').focus();
	  if (MM_findObj('contenu').createTextRange && MM_findObj('contenu').caretPos)
	  {
	  	  var caretPos = MM_findObj('contenu').caretPos;
	 	  if (caretPos.text.length>0)
		  {
			if (id!=8)
			{
				insertInCaret(MM_findObj('contenu'),"["+style[id]+"]","["+style[id+1]+"]");
			}
			else
			{
				geturl = prompt("veuillez entrer l'url",'http://');
				insertInCaret(MM_findObj('contenu'),"["+style[id]+geturl+"]","["+style[id+1]+"]");
			}
		  }
		  else
		  {
			if ( (countbalise('['+style[id],'contenu')+countbalise('['+style[id+1],'contenu'))%2 == 0)
			{
				if (id!=8)
				{
					insertAtCaret(MM_findObj('contenu'),"["+style[id]+"]");
				}
				else
				{	
					geturl = prompt("veuillez entrer l'url",'http://');
					insertAtCaret(MM_findObj('contenu'),"["+style[id]+geturl+"]");
				}
			}
			else
			{
				insertAtCaret(MM_findObj('contenu'),"["+style[id+1]+"]");
			}
			MM_findObj('contenu').focus()
		  }
	  }
    }
    else
    {
      if (stockage[id] == '0')
      {
        var temp = document.hop.contenu.value;
        document.hop.contenu.value=temp+' '+'['+style[id]+']';
        stockage[id] = '1';
      }
      else
      {
        var temp = document.hop.contenu.value;
        document.hop.contenu.value=temp+'['+style[id+1]+']'+' ';
        stockage[id] = '0';
      }        
    }
}

function countbalise(b,ch)
{
	count = 0;
	pos = MM_findObj(ch).value.indexOf(b);
	while ( pos != -1 )
	{
		count++;
		pos = MM_findObj(ch).value.indexOf(b,pos+1);
	}
	return count;
}

function insertElt(MyString,vernav)
{
    if (vernav>=4)
    {
	  MM_findObj('contenu').focus();
	  if ((MM_findObj('contenu').createTextRange) && (MM_findObj('contenu').caretPos))
	  {
		var caretPos = MM_findObj('contenu').caretPos;
		if (caretPos.text.length>0)
			insertInCaret(MM_findObj('contenu'),MyString,"");
		else
			insertAtCaret(MM_findObj('contenu'),MyString);
	  }
    }
    else
    {
      var temp = document.hop.contenu.value;
      document.hop.contenu.value=temp+MyString;
    }
}

function insertTag(MyString,vernav)
{
    if (vernav>=4)
    {
	  MM_findObj('contenu').focus();
	  if (MM_findObj('contenu').createTextRange && MM_findObj('contenu').caretPos)
	  {
		var caretPos = MM_findObj('contenu').caretPos;
		if (caretPos.text.length>0)
		{
			insertInCaret(MM_findObj('contenu'),"["+MyString+"]","[/"+MyString+"]");
		}
		else
		{
			if ( (countbalise('['+MyString,'contenu')+countbalise('[/'+MyString,'contenu'))%2 == 0)
			{
				insertAtCaret(MM_findObj('contenu'),"["+MyString+"]");
			}
			else
			{
				insertAtCaret(MM_findObj('contenu'),"[/"+MyString+"]");
			}
			MM_findObj('contenu').focus();
		}
	  }
    }
    else
    {
      if (stockage[12] == '0')
      {
        var temp = document.hop.contenu.value;
        document.hop.contenu.value=temp+' '+'['+MyString+']';
        stockage[12] = '1';
      }
      else
      {
        var temp = document.hop.contenu.value;
        document.hop.contenu.value=temp+'[/'+MyString+']'+' ';
        stockage[12] = '0';
      }
    }  
}


///Color
col0 = new Array(255,0,0,255,0,0);
col1 = new Array(0,0,255,0,0,255);
col2 = new Array(0,0,0,0,255,0);
col3 = new Array(0,255,0,255,0,0);

var base_hexa = "0123456789ABCDEF";

function dec2Hexa(number)
{
   return base_hexa.charAt(Math.floor(number / 16)) + base_hexa.charAt(number % 16);
}

function RGB2Hexa(TR,TG,TB)
{
  return "#" + dec2Hexa(TR) + dec2Hexa(TG) + dec2Hexa(TB);
}
function lightCase(MyObject)
{
	MM_findObj('ColorUsed').bgColor = MyObject.bgColor;
}

function rgb(dm,ta,vernav){
  if (vernav>=4)
  {
    fm = dm + 18;
    for (i=dm;i<fm+1;i++)
	{
	r = Math.floor(ta[0] + (i-dm)*(ta[1]-ta[0])/(fm-dm));
	g = Math.floor(ta[2] + (i-dm)*(ta[3]-ta[2])/(fm-dm));
	b = Math.floor(ta[4] + (i-dm)*(ta[5]-ta[4])/(fm-dm));
  codehex = r + '' + g + '' + b;
  document.write('		<td bgColor=\"' + RGB2Hexa(r,g,b) + '\" onClick=\"lightCase(this);\" title=\"Couleur survolée par la souris : #' + codehex + '\" width=\"4\" height=\"17\"></td>\n');
	}
  }
}


function texteGROS(champ, gras){
	var area = document.getElementById(champ);
	urlHtml=new String(area.value);
	var temp=null;
	temp = prompt("Veuillez saisir le texte souhaité","");
	if (temp == null || temp == "" || temp ==" ")
		return;
	var taille=null;
	while (taille == null || taille == "" || taille ==" " || taille < "1" || taille > "6")
		taille = prompt("Veuillez choisir une taille pour le texte.\n1=minimum, 3 = standard, 6 = maximum","");
	if (gras=='1')
		urlHtml+= "<b>";
	urlHtml+= "<font size=\""
	urlHtml+=taille;
	urlHtml+="\"";
	var color=MM_findObj('ColorUsed').bgColor;
	if (color!=null && color!='')
		urlHtml+=" color=\""+color+"\"";		
	urlHtml+=">"+temp+"</font>";
	if (gras=='1')
		urlHtml+="</b>";		
	area.value=urlHtml;
}


function editorial(){
	var area = document.getElementById("edito");
	htmlText = new String(area.value);
	arrayNewText= "";	
	for (var i=0; i< htmlText.length; i++) {
		if((htmlText.charAt(i)== "\n") ){
			arrayNewText +="<br>";
		} else {
			arrayNewText += htmlText.charAt(i);
		}
	}
	area.value=arrayNewText;
}




//////////////////////////////////////////////////////////////
// Ensemble code JS ancienne version
//////////////////////////////////////////////////////////////



function generer()
{
    var source  = '<html><head><title>TECHNI-CONTACT - Les Professionnels en direct</title><meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
        source += '<link href="<?php print(URL) ?>emailing/css.css" rel="stylesheet" type="text/css"></head><body>';
        source += '<table width="484" border="0" align="center" cellpadding="0" cellspacing="0"><tr><td colspan="3">';
        source += '<a href="<?php print(URL) ?>" target="_blank"><img src="<?php print(URL) ?>emailing/emailing_02.gif" width="484" height="75" border="0">';
        source += '</a></td></tr><tr><td width="63" rowspan="3" background="<?php print(URL) ?>emailing/emailing_04.gif"><p></p></td>';
        source += '<td width="421" colspan="2" valign="top" class="edito"><table border="0" align="center"><tr><td>';
        source += '<a href="<?php print(URL) ?>devenirAnnonceur.html" class="menu-haut" target="_blank">Pr&eacute;senter vos Produits</a></td><td>';
        source += '<a href="<?php print(URL) ?>recevoirCatalogues.html" target="_blank" class="menu-haut">Recevoir nos Catalogues</a></td><td>';
        source += '<a href="<?php print(URL) ?>contact.html"  target="_blank" class="menu-haut">Nous Contacter</a></td></tr></table>';
        source += '<img src="<?php print(URL) ?>emailing/catalogues.jpg" width="134" height="106" align="right"><br>';
	
    if(document.all.edito.value != null && document.all.edito.value != '')
    {
        source += document.all.edito.value;
    }
	
    source += '<br><br></td></tr><tr><td colspan="2" valign="top" class="titre">La S&eacute;lection du mois</td></tr><tr>';
    source += '<td width="210" valign="top"><img src="<?php print(URL) ?>emailing/spacer.gif" width="5" height="5">';

<?php

for($i = 1 ; $i <= 10; $i += 2)
{
    print('    if(document.all.id' . $i . '.value != null && document.all.id' . $i . '.value != \'\'){' . "\n");
    print('        source += \'<table width="200" border="0"><tr><td bgcolor="#FF0000" class="produit-titre">\';' . "\n");
    print('        source += document.all.nom' . $i . '.value;' . "\n");
    print('        source += \'</td></tr><tr><td bgcolor="#ECECEC" class="produit-image"><a href="' . URL . 'produits/\';' . "\n");
    print('        source += document.all.fam' . $i . '.value + \'-\';' . "\n");
    print('        source += document.all.id' . $i . '.value + \'-\' + document.all.ref' . $i . '.value;' . "\n");
    print('        source += \'.html" target="_blank"><img src="' . URL . 'images/produits/\';' . "\n");
    print('        source += document.all.id' . $i . '.value;' . "\n");
    print('        source += \'.jpg" border="0"></a><br>\';' . "\n");
    print('        if(document.all.com' . $i . '.value != null && document.all.com' . $i  .'.value != \'\'){' . "\n");
    print('            source += \'<div class="produit-texte">\';' . "\n");
    print('            source += document.all.com' . $i . '.value;' . "\n");
    print('            source += \'</div>\';' . "\n");
    print('        }' . "\n");
    print('        source += \'<div class="produit-infos"><a href="' . URL . 'contacts/infos/\';' . "\n");
    print('        source += document.all.fam' . $i . '.value + \'-\' + document.all.id' . $i . '.value;' . "\n");
    print('        source += \'.html" target="_blank" class="produit-infos">Demande d\\\'informations </a>&raquo;</div></td></tr></table>\';' . "\n");
    print('    }' . "\n");
    
    if($i == 9)
    {
        $i = 0;
        print('    source += \'</td><td width="211" valign="top"> <img src="' . URL . 'emailing/spacer.gif" width="5" height="5">\';' . "\n");

    }
}


?>


    source += '<img src="<?php print(URL) ?>emailing/spacer.gif" width="5" height="5"></td></tr><tr><td colspan="3"><img src="<?php print(URL) ?>emailing/emailing_03.gif" width="484" height="40"></td></tr></table><div class="produit-infos"><b>NOTE IMPORTANTE :</b> Ceci n\'est pas du SPAM. Vous avez soit autorisé l\'un de nos partenaires à vous envoyer des mails publicitaires, soit votre email est en libre accès sur le web et a été ciblé par nos moteurs. </div></body></html>';
	
    document.all.source.value = source;

}


function previsualiser()
{
    var f1 = document.getElementById('source').value;
    var f2 = window.open('source.html', 'code', 'width=550, height=250, left=100, top=100, scrollbars=yes');
    f2.document.write(f1);

}



//-->
</script>
<div class="titreStandard">Editorial - Démarche à suivre</div><br>
<div class="bg">
&middot; L'&eacute;ditorial correspond au message se situant en haut de l'email. <br>
&middot; Vous pouvez ins&eacute;rer du texte directement (il sera alors en noir, avec
une taille moyenne), ou choisir une typographie diff&eacute;rente, en utilisant
les boutons &quot;texte GRAS&quot; et &quot;texte NON GRAS&quot;. Dans les deux
cas il vous sera demand&eacute; de choisir la taille de la police, de 1 (minimum)
&agrave; 6 (maximum). <br>
&middot; Pour afficher un texte en couleur, s&eacute;lectionner pr&eacute;alablement
la couleur sur le &quot;spectre&quot; de couleur hexad&eacute;cimal. Cliquez
ensuite sur un des deux boutons de mise en forme (GRAS ou NON GRAS).<br>
<br>
    <div align="center">
    <br>
    <table border="0" align="center" cellpadding="0" cellspacing="0">
      <tr>
        <td><input name="button3" type="button" class="button" onClick="javascript:texteGROS('edito','1')" value="Texte GRAS"></td>
		<td><input name="button3" type="button" class="button" onClick="javascript:texteGROS('edito','0')" value="Texte NON GRAS"></td>
        <TD id=ColorUsed
                      style="BORDER-RIGHT: 2px ridge; BORDER-TOP: 2px ridge; BORDER-LEFT: 2px ridge; CURSOR: default; BORDER-BOTTOM: 2px ridge"
                      width=15>&nbsp;</TD>
        <td><SCRIPT language=JavaScript type=text/javascript>
		               <!--
		                   rgb(0,col0,4)
		                   rgb(18,col1,4)
		                   rgb(36,col2,4)
		                   rgb(0,col3,4)
		               // -->
		               </SCRIPT></td>
      </tr>
    </table>
    <br>
    <textarea name="edito" cols="50" rows="3" id="edito"></textarea>
    <br>
    <br>
</div>

</div>
<br><br>
<div class="titreStandard">Sélection des produits - Démarche à suivre</div><br>
<div class="bg">
  <br>
  &middot; La s&eacute;lection de produits s'effectue dans la colonne de gauche. Vous 
  pouvez naviguer par classement alphab&eacute;tique de produit ou par fournisseur.
  L'ic&ocirc;ne <img src="../images/web.gif" width="18" height="18" align="absmiddle">
  vous permez d'ouvrir dans une nouvelle fen&ecirc;tre la fiche du produit.<br>
  &middot; Pour s&eacute;lectionner un produit, cliquez sur son nom dans la colonne de
  gauche. Ses r&eacute;f&eacute;rences apparaissent alors dans le petit encart 
  ci-dessous. Il s'agit alors d'attribuer la position du produit dans l'e-mail, 
  de 1 &agrave; 10 suivant la disposition des champs de formulaire ci-dessous.<br>
  &middot; Enfin, vous pouvez ajouter du texte &agrave; chacun des produits, ou laissez
  l'espace vide.<br>
	<br><br>
    <iframe width="95%" name="choix" height="25" src="purpose.php?<?php print(session_name() . '=' . session_id()) ?>" align="center" frameborder="0"></iframe>
  <br>
  <br>
  <p align="center">
<?php

for($i = 1; $i <= 10; ++$i)
{
    if($i > 2)
    {
        print('<br><br>');
    }

    print($i . "\n");
    print('<input name="id' . $i . '" type="text" id="id' . $i . '" size="10">' . "\n");
    print('<input type="text" name="nom' . $i . '" id="nom' . $i . '">' . "\n");
    print('<input type="hidden" name="ref' . $i . '">' . "\n");
    print('<input type="hidden" name="fam' . $i++ . '">' . "\n");
    print('&nbsp;&nbsp;' . $i . "\n");
    print('<input name="id' . $i . '" type="text" id="id' . $i . '" size="10">' . "\n");
    print('<input type="text" name="nom' . $i . '" id="nom' . $i . '">' . "\n");
    print('<input type="hidden" name="ref' . $i . '">' . "\n");
    print('<input type="hidden" name="fam' . $i . '">' . "\n");
    print('<br> &nbsp;&nbsp; ' . "\n");
    print('<textarea name="com' . --$i . '" cols="24" rows="2" id="com' . $i . '"></textarea>' . "\n");
    print('&nbsp; &nbsp;&nbsp;' . "\n");
    print('<textarea name="com' . ++$i .'" cols="24" rows="2" id="com' . $i . '"></textarea>' . "\n");
    

}


?>
  </p>
  <br>
  <div align="center">
    
  <input name="Bouton" type="button" value="G&eacute;n&eacute;rer le code" onClick="javascript:editorial(); generer()">
  <input name="Bouton2" type="button" value="S&eacute;lectionner tout le code" onClick="javascript:document.all.source.focus();document.all.source.select();">
  <input name="Bouton22" type="button" value="Pr&eacute;visualiser" onClick="javascript:previsualiser();">
  <br>
  <textarea name="source" cols="50" rows="5" id="source"></textarea>
</div></div>
<?php


require(ADMIN . 'tail.php');

?>
