/*****************************************************************************/
/******						Javascript Classes							******/
/*****************************************************************************/

/*******************/
/** Class TabList **/
/*******************/
// Constructor
function TabList (id, trigger_fct, init_tabs)
{
	this.id = id;
	this.trigger_fct = trigger_fct;
	this.tc = new Array; // Tab Collection
	this.selected = null; // Selected tab
	
	for (var init_tab in init_tabs)
	{
		this.Add(init_tab, init_tabs[init_tab]);
	}
}

TabList.prototype.Add = function (layer_id, Text) {
	var tab = document.createElement('div');
	var tab_lb = document.createElement('img');
	var tab_bg = document.createElement('div');
	var tab_rb = document.createElement('img');
	
	tab.className = 'tab';
	tab.layerID = layer_id;
	tab_bg.appendChild(document.createTextNode(Text));
	tab.appendChild(tab_lb); tab.tab_lb = tab_lb;
	tab.appendChild(tab_bg); tab.tab_bg = tab_bg;
	tab.appendChild(tab_rb); tab.tab_rb = tab_rb;
	
	var _this = this;	// pointer sur l'objet lui-même pour prise en compte dans les fonctions suivantes
	
	tab.onmouseover = function () {
		if (_this.selected != this)
		{
			this.tab_lb.className = 'tab_lb_a';
			this.tab_rb.className = 'tab_rb_a';
			this.tab_bg.className = 'tab_bg_a';
		}
	}
	tab.onmouseout = function () {
		if (_this.selected != this)
		{
			this.tab_lb.className = 'tab_lb_i';
			this.tab_rb.className = 'tab_rb_i';
			this.tab_bg.className = 'tab_bg_i';
		}
	}
	tab.onclick = function () {
		if (_this.selected != this)
		{
			prev_selected = _this.selected;
			_this.selected = this;
			if (prev_selected) prev_selected.onmouseout();
			this.tab_lb.className  = 'tab_lb_s';
			this.tab_rb.className = 'tab_rb_s';
			this.tab_bg.className = 'tab_bg_s';
			_this.trigger_fct(_this.tc, this.layerID);
		}
	}
	
	// disable text slection
	if (typeof tab.onselectstart != "undefined") tab.onselectstart = function () { return false; } //IE
	else if (typeof tab.style.MozUserSelect != "undefined") tab.style.MozUserSelect = "none"; // Firefox
	else tab.onmousedown = function () { return false; } // Others
	
	tab.onmouseout();
	this.tc[layer_id] = tab;
}

TabList.prototype.Draw = function () {
	for (var t in this.tc)
		document.getElementById(this.id).appendChild(this.tc[t]);
}

	
/**********************/
/** Class SearchMenu **/
/**********************/
// Constructor
function SearchMenu (id, SearchMenuList, ElementType)
{
	this.id = id;
	this.ElementType = ElementType;
	this.oc = new Array;
	for (var searchtype in SearchMenuList)
	{
		switch (searchtype)
		{
			case "[A-Z]" :
				var A = 'A'; var Z = 'Z';
				for (var i = A.charCodeAt(0); i <= Z.charCodeAt(0); i++)
					this.Add(String.fromCharCode(i), SearchMenuList[searchtype]);
				break; 
			default :
				this.Add(searchtype, SearchMenuList[searchtype]);
		}
	}
}

// Element Creation function
SearchMenu.prototype.Add = function (Text, TriggerFct) {
	var o = document.createElement(this.ElementType);
	o.appendChild(document.createTextNode(Text));
	o.selectFunction = TriggerFct;
	o.down = false;
	o.onclick = function () { this.className = ''; this.selectFunction(this.firstChild.nodeValue); }
	o.onmouseover = function () { if (this.down) this.className = 'down'; else this.className = 'over'; }
	o.onmouseout = function () { this.className = ''; }
	o.onmousedown = function () {
		if (document.JSbutton) document.onmouseup(); // si un bouton a été enfoncé, mais l'event up n'a pas eu lieu
		document.JSbutton = this;
		this.saveonmouseup = document.onmouseup;
		document.onmouseup = function () {
			document.JSbutton.down = false;
			document.onmouseup = document.JSbutton.saveonmouseup;
			document.JSbutton = null;
		}
		this.down = true;
		this.className = 'down';
	}
	
	if (typeof o.onselectstart != "undefined") o.onselectstart = function () { return false; } //IE
	else if (typeof o.style.MozUserSelect != "undefined") o.style.MozUserSelect = "none"; // Firefox
	
	this.oc.push(o);
}
	
// Drawing function
SearchMenu.prototype.Draw = function () {
	for (var o in this.oc)
		document.getElementById(this.id).appendChild(this.oc[o]);
}

// Cleaning function
SearchMenu.prototype.Clean = function () {
	for (var o in this.oc)
		document.getElementById(this.id).removeChild(this.oc[o]);
}

/***********************/
/** Class ElementList **/
/***********************/

function ElementList (id, ElementType, TriggerFct)
{
	this.id = id;
	this.ElementType = ElementType;
	this.TriggerFct = TriggerFct;
	this.oc = new Array;
	this.SelectedObject = null;
}

ElementList.prototype.Add = function (ElementID, ElementText)
{
	var o = document.createElement(this.ElementType);
	o.ElementID = ElementID;
	o.ParentObject = this;
	o.appendChild(document.createTextNode(ElementText));
	o.onmouseover = function () { if (this.ParentObject.SelectedObject != this) this.className = 'over'; }
	o.onmouseout = function () { if (this.ParentObject.SelectedObject != this) this.className = ''; }
	o.onmousedown = function () {
		if (this.ParentObject.SelectedObject) this.ParentObject.SelectedObject.className = '';
		this.ParentObject.SelectedObject = this;
		this.className = 'selected';
		this.ParentObject.TriggerFct(this.ElementID);
	}
	
	if (typeof o.onselectstart != "undefined") o.onselectstart = function () { return false; } //IE
	else if (typeof o.style.MozUserSelect != "undefined") o.style.MozUserSelect = "none"; // Firefox
	
	this.oc.push(o);
}

ElementList.prototype.Clear = function () {
	for (var o in this.oc) delete(this.oc[o]);
	this.oc.length = 0;
}

ElementList.prototype.Clean = function () {
	for (var o in this.oc) document.getElementById(this.id).removeChild(this.oc[o]);
}

ElementList.prototype.Draw = function () {
	for (var o in this.oc) document.getElementById(this.id).appendChild(this.oc[o]);
}

ElementList.prototype.ProcessResponse = function (xhr) {
	
	this.Clean();
	this.Clear();
	
	var mainsplit = xhr.responseText.split(__MAIN_SEPARATOR__);

	if (mainsplit[0] == '') // Pas d'erreur
	{
		var outputs = mainsplit[1].split(__OUTPUT_SEPARATOR__);
		for (var i = 0; i < outputs.length-1; i++)
		{
			var outputID = outputs[i].split(__OUTPUTID_SEPARATOR__);
			if (outputID.length == 2)
			{
				this.Add(outputID[0],outputID[1]);
			}
		}
		this.Draw();
	}
	else
	{
		document.getElementById(this.id).innerHTML = mainsplit[0];
	}
}

/**********************/
/** Class AJAXHandle **/
/**********************/

function AJAXHandle (ParentObject, PerfReqLabel_id)
{
	if (window.XMLHttpRequest)
	{
		var xhr = new XMLHttpRequest();
		if (xhr.overrideMimeType) xhr.overrideMimeType("text/xml"); // Évite un bug du navigateur Safari
	}
	else if (window.ActiveXObject)
	{
		try { var xhr = new ActiveXObject("Msxml2.XMLHTTP"); } // essaie de charger l'objet pour IE
		catch (e)
		{
			try { var xhr = new ActiveXObject("Microsoft.XMLHTTP"); } // essaie de charger l'objet pour une autre version IE
			catch (e) { var xhr = false; }
		}
	}

	if (xhr === false)
	{
		window.alert("Votre navigateur ne prend pas en charge l'objet XMLHTTPRequest.");
		return false;
	}
	else
	{
		this.xhr = xhr;
		this.PerfReqLabel_id = PerfReqLabel_id;
		this.ParentObject = ParentObject;
		this.RequestInProgess = false;
	}
}

AJAXHandle.prototype.QueryA = function (query) {
	if (this.RequestInProgess)
	{
		document.getElementById(this.PerfReqLabel_id).innerHTML = "Une requête est déjà en cours de traitement";
	}
	else
	{
		this.RequestInProgess = true;
		var _this = this;
		this.xhr.abort();
		this.xhr.onreadystatechange = function () { _this.ProcessResponse(); }
		this.xhr.open('GET', query, true);
		this.xhr.send(null);
	}
}

AJAXHandle.prototype.ProcessResponse = function () {
	try
	{
		if (this.xhr.readyState == 4)
		{
			if (this.xhr.status == 200)
			{
				this.ParentObject.ProcessResponse(this.xhr);
				this.RequestInProgess = false;
				document.getElementById(this.PerfReqLabel_id).innerHTML = '<br />';
			}
			else
			{
				alert('Un problème est survenu au cours de la requête.');
			}
		}
		else
		{
			document.getElementById(this.PerfReqLabel_id).innerHTML = 'Requête en cours';
		}
	}
	catch(e)
	{
		alert("Une exception s'est produite : " + e.description);
	}

}

/**********************/
/** Class DateInputs **/
/**********************/
/*
function DateInputs (begin, end, accuracy) {
	
	this.accuracy = accuracy;
	
	if (typeof(begin) == "number") { this.DateBegin = new Date(); this.DateBegin.setTime(begin*1000); }
	else if (typeof(begin) == "string") if (begin != "current") this.DateBegin = new Date(begin);
	
	if (typeof(end) == "number") { this.DateEnd = new Date(); this.DateEnd.setTime(end*1000); }
	else if (typeof(end) == "string") if (end != "current") this.DateEnd = new Date(end);
	
	switch (this.accuracy)	
	{
		case "seconde" :	this.seconde = document.createElement('select');
		case "minute" :		this.minute = document.createElement('select');
		case "hour" :		this.hour = document.createElement('select');
		case "day" :		this.day = document.createElement('select');
		case "month" :		this.month = document.createElement('select');
		case "year" :		this.year = document.createElement('select'); break;
		default : this.day = document.createElement('select');
			this.month = document.createElement('select');
			this.year = document.createElement('select');
	}
	
	
	
	
}
*/




















