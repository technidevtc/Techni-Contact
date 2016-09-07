
var __ROWCOUNT_DFT__ = 30;	// Default number of rows showed in multi-page mode
var __RESSOURCES__ = "../ressources/";

/*****************************************************************************/
/******						Javascript Classes							******/
/*****************************************************************************/

if (!window.HN) HN = window.HN = {};

function trim(s)
{
	return s.replace(/(^\s*)|(\s*$)/g, '');
}     

// Purge all functions in children objects to avoid IE memory leaks (http://javascript.crockford.com/memory/leak.html)
function purge(d) {
	var a = d.attributes, i, l, n;
	if (a) {
		l = a.length;
		for (i = 0; i < l; i += 1) {
			n = a[i].name;
			if (typeof d[n] === 'function') d[n] = null;
		}
	}
	a = d.childNodes;
	if (a) {
		var l = a.length;
		for (i = 0; i < l; i += 1) purge(d.childNodes[i]);
	}
}

/*********************/
/** Class JSTable **/
/*********************/

// Constructor
HN.JSTable = function ()
{
	var that = this;
	
	var id = "", tclass = "";
	var headers = null;
	var ColIndex = [];
	var indexes = [];
	var rc = []; // Row Collection
	var idata = null;
	var ColumnCount = 0, RowCount = 0;
	var CellFct = null, RowFct = null;
	var EditTools = null;
	var MultiPage = false, CurrentPage = 1, LastPage = 1;
	var col2sort = 0;
	var maindiv, table, thead, tbody;
	var built = false, drawn = false;
	var rs = [];
	
	function sortA(a, b) {
		if (a.cc[col2sort].textvalue > b.cc[col2sort].textvalue) return 1;
		if (a.cc[col2sort].textvalue < b.cc[col2sort].textvalue) return -1;
		return 0;
	}
	function sortD(a, b) {
		if (a.cc[col2sort].textvalue < b.cc[col2sort].textvalue) return 1;
		if (a.cc[col2sort].textvalue > b.cc[col2sort].textvalue) return -1;
		return 0;
	}
	function sortTable(h) {
		if (!built) return false;
		col2sort = h.colNum;
		for (var i = 0; i < thead.tr.cc.length; i++) {
			if (i != col2sort)
			{
				thead.tr.cc[i].sort_order = "D";
				thead.tr.cc[i].span_order.style.visibility = "hidden";
			}
		}
		h.span_order.style.visibility = "visible";
		if (h.sort_order == "D") { rc.sort(sortA); h.sort_order = "A"; h.span_order.className = "sort-order sort-asc"; }
		else { rc.sort(sortD); h.sort_order = "D"; h.span_order.className = "sort-order sort-desc"; }
		that.Refresh();
	}
	function CreateHeader(colNum, text, sortable) {
		if (thead && thead.tr)
		{
			var th = document.createElement("th");
			th.id = thead.tr.id + "-col" + colNum;
			th.className = "column-" + colNum;
			
			th.colNum = colNum;
			th.sort_order = "D";
			if (sortable) th.onclick = function() { sortTable(this); }
			th.onmouseover = function() { this.className = "column-" + this.colNum + " over"; }
			th.onmouseout  = function() { this.className = "column-" + this.colNum; }
			
			th.div0 = document.createElement("div"); th.div0.id = th.id + "-div0"; th.div0.className = "inner-th";
			th.div1 = document.createElement("div"); th.div1.id = th.id + "-div1"; th.div1.className = "inner-th-div";
			th.span_order = document.createElement("span"); th.span_order.id = th.id + "-span0"; th.span_order.className = "sort-order sort-asc";
			th.span_separator = document.createElement("span"); th.span_separator.id = th.id + "-span1"; th.span_separator.className = "separator";
			
			th.div0.onmouseover = function() { this.className = "inner-th over"; this.parentNode.className = "inner-th-div over"; this.parentNode.span_separator.className = "separator over"; }
			th.div0.onmouseout  = function() { this.className = "inner-th"; this.parentNode.className = "inner-th-div"; this.parentNode.span_separator.className = "separator"; }
			
			th.div1.appendChild(th.span_separator);
			th.div1.appendChild(document.createTextNode(text));
			th.div1.appendChild(th.span_order);
			th.div0.appendChild(th.div1);
			th.appendChild(th.div0);
			
			return th;
		}
		else return false;
	}
	
	this.setID = function(_id) { id = _id; }
	this.setClass = function(_class) { tclass = _class; }
	this.setHeaders = function(_headers) { headers = _headers; }
	this.getHeaders = function() { return headers; }
	this.setColIndex = function(_ColIndex) { ColIndex = _ColIndex; }
	this.getRow = function(row) { return rc[row]; }
	this.getCell = function(row, col) { return row.cc[col]; }
	this.setInitialData = function(_idata) { idata = _idata; }
	this.setColumnCount = function(_ColumnCount) { ColumnCount = _ColumnCount; }
	this.setRowCount = function(_RowCount) { RowCount = _RowCount; }
	
	this.setCellFct = function(_CellFct) { CellFct = _CellFct; }
	this.setRowFct = function(_RowFct) { RowFct = _RowFct; }
	
	this.setEditTools = function(_EditTools) { EditTools = _EditTools; }
	
	this.setMultiPage = function(_mp) {
		MultiPage = _mp;
		if (MultiPage && RowCount == 0) RowCount = __ROWCOUNT_DFT__;
	}
	this.setCurrentPage = function(_cp) { CurrentPage = _cp; }
	this.getLastPage = function() { return LastPage; }
	
	this.Build = function () {
		if ((maindiv = document.getElementById(id)) && headers && !built)
		{
			if (ColumnCount == 0) ColumnCount = headers.length;
			table = document.createElement("table"); table.id = id + "-table"; table.className = tclass;
			thead = document.createElement("thead"); thead.id = table.id + "-thead";
			tbody = document.createElement("tbody"); tbody.id = table.id + "-tbody";
			maindiv.appendChild(table);
			table.appendChild(thead);
			table.appendChild(tbody);
			
			// HEADERS //
			var tr = document.createElement("tr");
			tr.id = thead.id + "-row0";
			tr.cc = new Array(); // Cols Collection
			thead.tr = tr;
			
			// Tools Column Header
			if (EditTools) {
				var th = CreateHeader("edit", "", false);
				tr.appendChild(th);
				tr.EditTools = th;
			}
			
			// Normal columns headers
			for (var col = 0; col < ColumnCount; col++)
			{
				var th = CreateHeader(col, headers[col], true);
				tr.appendChild(th);
				tr.cc.push(th);
			}
			
			// Disable text selection
			if (typeof tr.onselectstart != "undefined") tr.onselectstart = function () { return false; } //IE
			else if (typeof tr.style.MozUserSelect != "undefined") tr.style.MozUserSelect = "none"; // Firefox
			else tr.onmousedown = function () { return false; } // Others
			
			thead.appendChild(tr);
			
			// DATA //
			if (idata)
			{
				for (var row = 0; row < idata.length; row++)
				{
					var tr = document.createElement("tr");
					tr.id = tbody.id + "-row" + row;
					tr.rowNum = row;
					tr.cc = new Array();
					
					var nbcols = ColumnCount < idata[row].length ? ColumnCount : idata[row].length;
					
					// Tools column
					if (EditTools)
					{
						var td = document.createElement("td");
						td.id = tr.id + "-coledit";
						td.className = "column-edit";
						
						for (var tool in EditTools)
						{
							//alert(EditTools[tool]);
							tr[tool] = document.createElement(EditTools[tool]["element"]);
							tr[tool].id = td.id + "-" + tool;
							tr[tool].className = tool;
							for (attribute in EditTools[tool]["attributes"])
								tr[tool][attribute] = EditTools[tool]["attributes"][attribute];
							td.appendChild(tr[tool]);
						}
						tr.appendChild(td);
						tr.EditTools = td;
					}
					
					// Data columns
					for (var col = 0 ; col < nbcols; col++)
					{
						var td = document.createElement("td");
						td.id = tr.id + "-col" + col;
						td.colNum = col;
						td.className = "column-" + col;
						
						td.textvalue = idata[row][col];
						td.appendChild(document.createTextNode(idata[row][col]));
						
						for (fct in CellFct) td[fct] = CellFct[fct];
						
						tr.appendChild(td);
						tr.cc.push(td);
					}
					// Indexes
					for (var col = 0; col < ColIndex.length; col++)
					{
						if (!indexes[ColIndex[col]]) indexes[ColIndex[col]] = [];
						indexes[ColIndex[col]][idata[row][ColIndex[col]]] = tr;
					}
					// Special columns
					if (idata[row].length > ColumnCount)
					{
						for (var col = ColumnCount; col < headers.length; col++)
						{
							if (typeof(headers[col]) == "function")
								headers[col](idata[row][col], tr);
						}
					}
					
					for (fct in RowFct) tr[fct] = RowFct[fct];
					
					rc.push(tr);
				}
			}
			built = true;
		}
	}
	
	this.getRowByIndex = function (value, colindex) {
		return indexes[colindex][value];
	}
	
	this.Refresh = function () {
		if (!built) this.Build();
		var nodes = tbody.childNodes; var l = nodes.length;
		for (var i=l-1; i >= 0; i--) tbody.removeChild(nodes[i]);
		
		var start, end;
		if (MultiPage && RowCount > 0 && idata.length > 0)
		{
			LastPage = Math.ceil(idata.length / RowCount);
			if (CurrentPage < 1) CurrentPage = 1;
			else if ((CurrentPage-1) * RowCount >= idata.length) CurrentPage = LastPage;
			start = (CurrentPage-1) * RowCount;
			end = start + RowCount;
			if (end > idata.length) end = idata.length;
		}
		else
		{
			CurrentPage = LastPage = 1;
			start = 0;
			end = RowCount != 0 ? (RowCount < idata.length ? RowCount : idata.length) : idata.length;
		}
		
		for (var i = start; i < end; i++) tbody.appendChild(rc[i]);
	}
	
	this.AlterCell = function (row, colNum, value) {
		if (colNum < ColumnCount)
			row.cc[colNum].innerHTML = row.cc[colNum].textvalue = value;
		else
			if (typeof(headers[colNum]) == "function") headers[colNum](value, row);
	}
	
	this.AlterRow = function (row, data) {
		var nbcols = ColumnCount < data.length ? ColumnCount : idata.length;
		
		for (var col = 0; col < nbcols; col++)
			row.cc[col].innerHTML = row.cc[col].textvalue = data[col];
		
		if (data.length > ColumnCount)
		{
			for (var col = ColumnCount; col < headers.length; col++)
			{
				if (typeof(headers[col]) == "function")
					headers[col](data[col], row);
			}
		}
	}
	
	// Selections functions
	this.Select = function (row) { if (!rs[row.rowNum]) rs[row.rowNum] = row; }
	this.UnSelect = function (row) { if (rs[row.rowNum]) delete(rs[row.rowNum]); }
	this.ToggleSelect = function (row) {
		if (rs[row.rowNum]) delete(rs[row.rowNum]);
		else { rs[row.rowNum] = row; }
	}
	this.SelectAll = function () {
		for (var i = 0; i < rc.length; i++)
		{
			rs[rc[i].rowNum] = rc[i];
			if (EditTools.check) rc[i].check.checked = true;
		}
	}
	this.isSelected = function (row) { if (rs[row.rowNum]) return true; else return false; }
	
	this.getSelectedRows = function () { return rs; }
	
	this.SelectAllFiltered = function () {
		rs = [];
		for (var i = 0; i < rc.length; i++)
		{
			rs[rc[i].rowNum] = rc[i];
			document.getElementById(rc[i].rowNum + "-coledit-check").checked = true;
		}
	}
	this.ClearSelection = function () { rs = []; }
	
	this.Destroy = function() {
		if (built)
		{
			this.ClearSelection();
			for (var i = 0; i < rc.length; rc++) purge(rc[i]);
			delete(rc);
			rc = [];
			
			purge(maindiv);
			var nodes = maindiv.childNodes;
			var l = nodes.length;
			for (var i = l-1; i >= 0; i--) maindiv.removeChild(nodes[i]);
			built = false;
		}
	}
}

/************************/
/** Class PageSwitcher **/
/************************/
HN.PageSwitcher = function()
{
	var that = this;
	var id = "";
	var CurrentPage = 1, LastPage = 1;
	var style = "vBulletin";
	var TriggerFct = null;
	var Dynamic = true;
	var Scheme = new Array(-1000, -400, -200, -100, -40, -10, -2, -1, 0, 1, 2, 10, 40, 100, 200, 400, 1000);
	var flcr = 3;	// First Last Condition Range : Number of existing pages required before or after the current one to show the "<< First" and "Last >>" links
	var base = null;
	var built = false; drawn = false;
	var maindiv = null;
	
	this.setID = function(_id) {
		/*if (id != "" && drawn && id != _id)
		{
			document.getElementById(id).removeChild(base);
			drawn = false;
		}	Automatically done by JS engine */
		id = _id;
	}
	this.setCurrentPage = function(_CurrentPage) { CurrentPage = _CurrentPage; }
	this.setLastPage = function(_LastPage) { LastPage = _LastPage; }
	this.setStyle = function(_style) { style = _style; }
	this.setTriggerFct = function(_TriggerFct) { TriggerFct = _TriggerFct; }
	this.setDynamic = function(_Dynamic) { Dynamic = _Dynamic; }
	//this.setScheme = function(_Scheme) { Scheme = _Scheme; }
	
	function PSclick (link)
	{
		if (link.num_page)
		{
			if (Dynamic)
			{
				CurrentPage = link.num_page;
				that.Refresh();
			}
			if (TriggerFct) TriggerFct(link.num_page);
		}
	}
	
	this.Build = function () {
		base = document.createElement("div");
		built = true;
		
		switch (style)
		{
			case "vBulletin" :
				base.className = "PageSwitcher vBulletin";
				
				base.position = document.createElement("div"); base.position.className = "position";
				base.appendChild(base.position);
				
				base.first = document.createElement("div"); base.first.className = "";
				base.first.link = document.createElement("a");
				base.first.link.innerHTML = "<strong>«</strong> First";
				base.first.link.onclick = function () { PSclick(this); return false; }
				base.first.appendChild(base.first.link);
				base.appendChild(base.first);
				
				base.previous = document.createElement("div"); base.previous.className = "";
				base.previous.link = document.createElement("a");
				base.previous.link.appendChild(document.createTextNode("<"));
				base.previous.link.onclick = function () { PSclick(this); return false; }
				base.previous.appendChild(base.previous.link);
				base.appendChild(base.previous);
				
				base.scheme = new Array();
				for (var i = 0; i < Scheme.length; i++)
				{
					var page = document.createElement("div");
					if (Scheme[i] == 0)
					{
						page.className = "current";
						page.appendChild(document.createTextNode(""));
					}
					else
					{
						page.link = document.createElement("a");
						page.link.appendChild(document.createTextNode(""));
						page.link.onclick = function () { PSclick(this); return false; }
						page.appendChild(page.link);
					}
					
					base.scheme[i] = page;
					base.appendChild(page);
				}
				
				base.next = document.createElement("div"); base.next.className = "";
				base.next.link = document.createElement("a");
				base.next.link.appendChild(document.createTextNode(">"));
				base.next.link.onclick = function () { PSclick(this); return false; }
				base.next.appendChild(base.next.link);
				base.appendChild(base.next);
				
				base.last = document.createElement("div"); base.last.className = "";
				base.last.link = document.createElement("a");
				base.last.link.innerHTML = "Last <strong>»</strong>";
				base.last.link.onclick = function () { PSclick(this); return false; }
				base.last.appendChild(base.last.link);
				base.appendChild(base.last);
				
				break;
				
			default : built = false;
		}
		
	}
	
	function ShowLink (o, page, write)
	{
		o.style.display = "inline";
		o.link.href = "#" + page;
		o.link.num_page = page;
		if (write) o.link.firstChild.nodeValue = page;
	}
	
	function HideLink(o)
	{
		o.style.display = "none";
		o.link.href = "";
		o.link.num_page = null;
	}
	
	this.Refresh = function () {
		if (id != "")
		{
			if (!built) this.Build();
			maindiv = document.getElementById(id);
			maindiv.appendChild(base);
			
			base.position.innerHTML = "Page " + CurrentPage + " of " + LastPage;
			
			if (CurrentPage > flcr) ShowLink(base.first, 1);
			else HideLink(base.first);
			
			if (CurrentPage > 1) ShowLink(base.previous, (CurrentPage-1));
			else HideLink(base.previous);
			
			if (CurrentPage < LastPage) ShowLink(base.next, (CurrentPage+1));
			else HideLink(base.next);
			
			if (CurrentPage <= (LastPage - flcr)) ShowLink(base.last, LastPage);
			else HideLink(base.last);
			
			for (var i = 0; i < Scheme.length; i++)
			{
				if (Scheme[i] == 0)
				{
					base.scheme[i].firstChild.nodeValue = CurrentPage;
				}
				else
				{
					cpage = CurrentPage + Scheme[i];
					if (cpage >= 1 && cpage <= LastPage) ShowLink(base.scheme[i], cpage, true);
					else HideLink(base.scheme[i]);
				}
			}
			
			//alert(base.scheme[3].link.firstChild.nodeValue);
			drawn = true;
		}
		
	}
}

HN.Window = function() {
	
	var that = this;
	var id = "", cid = "", pid = ""
	var movable = false;
	var showMinButton = false, showMaxButton = false, showCloseButton = false, showValidButton = false, showCancelButton = false;
	var ValidFct = null;
	var shadow = false;
	var titletext = "";
	var width = 200, height = 100;
	
	var built = false;
	var win = null, winshad = null;
	var close_button = null, cancel_button = null, valid_button = null, min_button = null, max_button = null, move_img = null;
	
	this.setID = function(_id) { id = _id; }
	this.setMovable = function(_movable) { movable = _movable; }
	this.showMinButton = function(_showMinButton) { showMinButton = _showMinButton; }
	this.showMaxButton = function(_showMaxButton) { showMaxButton = _showMaxButton; }
	this.showCloseButton = function(_showCloseButton) { showCloseButton = _showCloseButton; }
	this.showValidButton = function(_showValidButton) { showValidButton = _showValidButton; }
	this.setValidFct = function(_ValidFct) { ValidFct = _ValidFct; }
	this.showCancelButton = function(_showCancelButton) { showCancelButton = _showCancelButton; }
	//this.setCloseButtonFct = function(_CloseButtonFct) { CloseButtonFct = _CloseButtonFct; }
	this.setShadow = function(_shadow) { shadow = _shadow; }
	this.setTitleText = function(_titletext) { titletext = _titletext; }

	this.Build = function () {
		if (win = document.getElementById(id))
		{
			if (shadow) { winshad = document.createElement("div"); winshad.id = id + "Shad"; }
			
			var title_bar = document.createElement("div");
			title_bar.className = "window_title_bar";
			if (showCloseButton)
			{
				close_button = document.createElement("div");
				close_button.className = "close_img close_img_up";
				close_button.onmouseout = function () { if (this.className == "close_img close_img_down") this.className = "close_img close_img_up"; }
				close_button.onmousedown = function () { this.className = "close_img close_img_down"; }
				close_button.onmouseup = function () { if (this.className == "close_img close_img_down") { this.className = "close_img close_img_up"; that.Hide(); } }
				title_bar.appendChild(close_button);
			}
			if (showCancelButton)
			{
				cancel_button = document.createElement("div");
				cancel_button.className = "cancel_img cancel_img_up";
				cancel_button.onmouseout = function () { if (this.className == "cancel_img cancel_img_down") this.className = "cancel_img cancel_img_up"; }
				cancel_button.onmousedown = function () { this.className = "cancel_img cancel_img_down"; }
				cancel_button.onmouseup = function () { if (this.className == "cancel_img cancel_img_down") { this.className = "cancel_img cancel_img_up"; that.Hide(); } }
				title_bar.appendChild(cancel_button);
			}
			if (showValidButton)
			{
				valid_button = document.createElement("div");
				valid_button.className = "valid_img valid_img_up";
				valid_button.onmouseout = function () { if (this.className == "valid_img valid_img_down") this.className = "valid_img valid_img_up"; }
				valid_button.onmousedown = function () { this.className = "valid_img valid_img_down"; }
				valid_button.onmouseup = function () { if (this.className == "valid_img valid_img_down") { this.className = "valid_img valid_img_up"; ValidFct(); } }
				title_bar.appendChild(valid_button);
			}
			
			var inner_title_bar = document.createElement("div");
			if (movable)
			{
				if (shadow) inner_title_bar.onmousedown = function() { grab(win, winshad); }
				else inner_title_bar.onmousedown = function() { grab(win); }
				move_img = document.createElement("div");
				move_img.className = "move_img";
				move_img.border = "0";
				inner_title_bar.appendChild(move_img);
			}
			
			win.title_bar_text = document.createElement("div"); win.title_bar_text.className = "titletext";
			win.title_bar_text.appendChild(document.createTextNode(titletext));
			inner_title_bar.appendChild(win.title_bar_text);
			
			var title_bar_zero = document.createElement("div"); title_bar_zero.style.clear = "both";
			inner_title_bar.appendChild(title_bar_zero);
			
			title_bar.appendChild(inner_title_bar);
			win.insertBefore(title_bar, win.firstChild);
			if (shadow) win.parentNode.insertBefore(winshad, win);
			//contentdiv.insertBefore(win, contentdiv.firstChild);
			
			built = true;
		}
	}
	
	this.Show = function () {
		if (built) win.style.visibility = "visible";
		if (shadow) winshad.style.visibility = "visible";
	}
	
	this.Hide = function () {
		if (built) win.style.visibility = "hidden";
		if (shadow) winshad.style.visibility = "hidden";
	}
	
	this.toString = function() {
		var val = '<div id="hoho"><a href="hehe">huhu</a></div>';
		return val;
	}
	
}

/***************************/
/** Class FamiliesBrowser **/
/***************************/

HN.FamiliesBrowser = function () {
	var that = this;
	var cur_family_id = 0;
	var id = "";
	var win = null, bg = null, menu = null, colg = null, titre = null, sf = null, colc = null, desc = null, ssf = null;
	
	this.setID = function(_id) { id = _id; }
	this.getCurFamID = function() { return cur_family_id; }
	
	this.Build = function () {
		if (win = document.getElementById(id))
		{
			win.className = "family-window";
			if (bg) {
				purge(bg);
				for (var node = bg.childNodes.length-1; node >= 0; node--) bg.removeChild(bg.childNodes[node]);
			}
			else {
				bg = document.createElement('div'); bg.className = "family-window-bg";
			}
				menu = document.createElement('div'); menu.className = "menu";
				cols = document.createElement('div'); cols.className = "cols";
					colg = document.createElement('div'); colg.className = "colg";
						titre = document.createElement('div'); titre.className = "titre";
						sf = document.createElement('div'); sf.className = "sf";
					colc = document.createElement('div'); colc.className = "colc";
						desc = document.createElement('h1'); desc.className = "desc";
						ssf = document.createElement('div'); ssf.className = "ssf";
			
			win.appendChild(bg);
				bg.appendChild(menu);
				bg.appendChild(cols);
					cols.appendChild(colg);
						colg.appendChild(titre);
							titre.appendChild(document.createTextNode("Familles"));
						colg.appendChild(sf);
					cols.appendChild(colc);
						colc.appendChild(desc);
							desc.appendChild(document.createTextNode("Choisissez une famille"));
						colc.appendChild(ssf);
			
			
			menu.current_f = null;
			menu.childrenList = [];
			for (var i = 0; i < families[0][nbchildren]; i++)
			{
				var a = document.createElement("a");
				a.href = "#";
				a.family_id = families[0][children][i];
				a.appendChild(document.createTextNode(families[a.family_id][name]));
				menu.childrenList[a.family_id] = a;
				menu.appendChild(a);
				menu.appendChild(document.createTextNode(" "));

				a.Select = function () { // lors de la sélection
					if (this.parentNode.current_f && this.parentNode.current_f != this) this.parentNode.current_f.UnSelect();
					this.parentNode.current_f = this;
					this.className = 'current';
					
					titre.innerHTML = families[this.family_id][name];
					families[this.family_id][children].sort(fam_sort_ref_name); // Tri par nom référence pour affichage
					
					purge(sf);
					purge(ssf);
					for (var node = sf.childNodes.length-1; node >= 0; node--) sf.removeChild(sf.childNodes[node]);
					for (var node = ssf.childNodes.length-1; node >= 0; node--) ssf.removeChild(ssf.childNodes[node]);
					sf.current_sf = null;
					ssf.current_ssf = null;
					sf.childrenList = [];
					ssf.childrenList = [];
					
					for (var j = 0; j < families[this.family_id][nbchildren]; j++)
					{
						var a2 = document.createElement("a");
						a2.href = "#";
						a2.family_id = families[this.family_id][children][j];
						a2.appendChild(document.createTextNode(families[a2.family_id][name]));
						sf.childrenList[a2.family_id] = a2;
						sf.appendChild(a2);
						
						a2.Select = function () {
							if (this.parentNode.current_sf && this.parentNode.current_sf != this) this.parentNode.current_sf.UnSelect();
							this.parentNode.current_sf = this;
							this.className = 'currentUnfolded';
							
							families[this.family_id][children].sort(fam_sort_ref_name); // Tri par nom référence pour affichage
							
							purge(ssf);
							for (var node = ssf.childNodes.length-1; node >= 0; node--) ssf.removeChild(ssf.childNodes[node]);
							ssf.current_ssf = null;
							ssf.childrenList = [];
							for (var k = 0; k < families[this.family_id][nbchildren]; k++)
							{
								var a3 = document.createElement("a");
								a3.href = "#";
								a3.family_id = families[this.family_id][children][k];
								a3.appendChild(document.createTextNode(families[a3.family_id][name]));
								ssf.childrenList[a3.family_id] = a3;
								ssf.appendChild(a3);
								
								a3.onclick = function () {
									cur_family_id = this.family_id;
									if (this.parentNode.current_ssf && this.parentNode.current_ssf != this) this.parentNode.current_ssf.className = '';
									this.parentNode.current_ssf = this;
									this.className = 'current';
									
									desc.innerHTML = "Famille " + families[this.family_id][name];
									
									return false;
								}
							}
							
							return false;
						}
						
						a2.UnSelect = function () { // fonction pour cacher la ssf courante
							this.className = '';
						}
						
						a2.onclick = a2.Select;
					}
					
					return false;
				}
				
				a.UnSelect = function () { // lors de la déselection
					this.className = '';
				}
				
				a.onclick = a.Select;
				aa = a.cloneNode(true);
				aa.apointer = a;
				aa.onclick = function() { this.apointer.onclick(); }
				sf.appendChild(aa);
			}
		}
	}
	
	this.SelectFamByID = function (fid) {
		var fidr = fid;
		var famTree = [];
		var n = 0;
		while (fidr != 0)
		{
			famTree[n++] = fidr;
			fidr = families[fidr][idParent];
		}
		famTree.reverse();
		for (var i = 0; i < n; i++)
		{
			switch (i)
			{
				case 0: menu.childrenList[famTree[0]].onclick(); break;
				case 1: sf.childrenList[famTree[1]].onclick(); break;
				case 2: ssf.childrenList[famTree[2]].onclick(); break;
				default : break;
			}
		}
	}
	
}

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

function ElementList (id, ElementType, _ilo)
{
	this.id = id;
	this.ElementType = ElementType;
	this.ilo = _ilo;	// Initialisation List Object
	this.ec = [];			// Element Collection
	this.ei = [];			// Element Index
	this.SelectedObject = null;
}

ElementList.prototype.Add = function (ElementID, ElementText, ElementImg)
{
	if (typeof this.ei[ElementID] === "undefined")
	{
		var e = document.createElement(this.ElementType);
		e.ElementID = ElementID;
		e.ParentObject = this;
		
		e.appendChild(document.createTextNode(ElementText));
		e.appendChild(ElementImg);
		
		for (var i in this.ilo) e[i] = this.ilo[i];
		
		if (typeof e.onselectstart != "undefined") e.onselectstart = function () { return false; } //IE
		else if (typeof e.style.MozUserSelect != "undefined") e.style.MozUserSelect = "none"; // Firefox
		
		this.ec.push(e);
		this.ei[ElementID] = this.ec.length-1;
	}
}

ElementList.prototype.Delete = function (Elements)
{
	if (typeof Elements.constructor != Array)
		Elements = [Elements];
	
	var E2D = [];	// Elements To Delete
	for (var i in Elements)
		E2D[typeof Elements[i].constructor == String ? Elements[i] : Elements[i].ElementID] = true
	
	for (var eid in E2D)
	{
		this.ec[this.ei[eid]].parentNode.removeChild(this.ec[this.ei[eid]]);
		delete this.ec[this.ei[eid]];
		delete this.ei[eid];
		//var s=""; for (var j in this.ec) s+=j+"="+this.ec[j]+"\n"; alert(s);
	}
}

ElementList.prototype.Clear = function () {
	for (var eid in this.ei)
	{
		delete this.ec[this.ei[eid]];
		delete this.ei[eid];
	}
	this.ec.length = 0;
}

ElementList.prototype.Clean = function () {
	for (var o in this.ec) document.getElementById(this.id).removeChild(this.ec[o]);
}

ElementList.prototype.Draw = function () {
	for (var o in this.ec) document.getElementById(this.id).appendChild(this.ec[o]);
}

/**********************/
/** Class AJAXHandle **/
/**********************/

function AJAXHandle (_ProcessResponseFct, _PerfReqLabel_id)
{
	var that = this;
	var PerfReqLabel_id = _PerfReqLabel_id;
	var ProcessResponseFct = _ProcessResponseFct;
	var RequestInProgess = false;
	
	var browser = "";
	if (document.recalc) browser = "ie";
	else if (window.__defineGetter__) browser = "gecko";
	else if (window.opera) browser = "opera";
	else if (navigator.userAgent.match("Safari")) browser = "safari";
	
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
	
	this.QueryA = function (query) {
		if (RequestInProgess) document.getElementById(PerfReqLabel_id).innerHTML = "Requête déjà en cours";
		else {
			RequestInProgess = true;
			xhr.readyState == 0;
			xhr.onreadystatechange = function () { that.ProcessResponse(); }
			xhr.open('GET', query, true);
			xhr.send(null);
		}
	}
	
	this.ProcessResponse = function () {
		try {
			if (xhr.readyState == 4) {
				if (xhr.status == 200) {
					ProcessResponseFct(xhr);
					RequestInProgess = false;
					if (browser == "ie") xhr.abort();
					document.getElementById(PerfReqLabel_id).innerHTML = '<br />';
				}
				else alert('Un problème est survenu au cours de la requête.');
			}
			else document.getElementById(PerfReqLabel_id).innerHTML = "Requête en cours";
		}
		catch(e) {
			RequestInProgess = false;
			if (browser == "ie") xhr.abort();
			alert("Une exception s'est produite : " + e.description);
		}
	}
	
}
