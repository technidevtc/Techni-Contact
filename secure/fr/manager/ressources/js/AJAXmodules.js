//var sss="";for(var oo in SIbC)sss+=oo+"="+SIbC[oo]+"\n";alert(sss);
/*****************************************************************************/
/******                      Javascript Modules                         ******/
/*****************************************************************************/

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

if (!window.HN) HN = window.HN = {};
if (!HN.GVars) HN.GVars = {};
if (!HN.Mods) HN.Mods = {};
if (!HN.Classes) HN.Classes = {};

/****************************************/
/** Dialog Box Module **/
/****************************************/
HN.Mods.DialogBox = function() {
	var that = this;
	var id = arguments[0] ? arguments[0] : "";
	var cid = "", pid = "";
	var movable = false;
	var showMinButton = false, showMaxButton = false, showCloseButton = false, showValidButton = false, showCancelButton = false;
	var ValidFct = null;
	var shadow = false;
	var titletext = "";
	var width = 200, height = 100;
	
	var built = false;
	var win = null, winshad = null;
	var close_button = null, cancel_button = null, valid_button = null, min_button = null, max_button = null, move_img = null;
	
	this.setID = function(_id) { id = _id; };
	this.setMovable = function(_movable) { movable = _movable; };
	this.showMinButton = function(_showMinButton) { showMinButton = _showMinButton; };
	this.showMaxButton = function(_showMaxButton) { showMaxButton = _showMaxButton; };
	this.showCloseButton = function(_showCloseButton) { showCloseButton = _showCloseButton; };
	this.showValidButton = function(_showValidButton) { showValidButton = _showValidButton; };
	this.setValidFct = function(_ValidFct) { ValidFct = _ValidFct; };
	this.showCancelButton = function(_showCancelButton) { showCancelButton = _showCancelButton; };
	//this.setCloseButtonFct = function(_CloseButtonFct) { CloseButtonFct = _CloseButtonFct; }
	this.setShadow = function(_shadow) { shadow = _shadow; };
	this.setTitleText = function(_titletext) { titletext = _titletext; };

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
	};
	
	this.Show = function () {
		if (built) win.style.visibility = "visible";
		if (shadow) winshad.style.visibility = "visible";
	};
	
	this.Hide = function () {
		if (built) win.style.visibility = "hidden";
		if (shadow) winshad.style.visibility = "hidden";
	};
	
};

/*********************/
/** Class JSTable **/
/*********************/

// Constructor
HN.Mods.JSTable = function() {
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
		if (MultiPage && RowCount == 0) RowCount = 25;
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
	};
	
	this.AlterRow = function (row, data) {
		var nbcols = ColumnCount < data.length ? ColumnCount : idata[0].length;
		
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
	};
	
	this.AddRow = function (data) {
		idata = idata.concat(data);
		for (var row = 0; row < data.length; row++)
		{
			var tr = document.createElement("tr");
			tr.id = tbody.id + "-row" + row;
			tr.rowNum = row;
			tr.cc = new Array();
			
			var nbcols = ColumnCount < data[row].length ? ColumnCount : data[row].length;
			
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
				
				td.textvalue = data[row][col];
				td.appendChild(document.createTextNode(data[row][col]));
				
				for (fct in CellFct) td[fct] = CellFct[fct];
				
				tr.appendChild(td);
				tr.cc.push(td);
			}
			// Indexes
			for (var col = 0; col < ColIndex.length; col++)
			{
				if (!indexes[ColIndex[col]]) indexes[ColIndex[col]] = [];
				indexes[ColIndex[col]][data[row][ColIndex[col]]] = tr;
			}
			// Special columns
			if (data[row].length > ColumnCount)
			{
				for (var col = ColumnCount; col < headers.length; col++)
				{
					if (typeof(headers[col]) == "function")
						headers[col](data[row][col], tr);
				}
			}
			
			for (fct in RowFct) tr[fct] = RowFct[fct];
			
			rc.push(tr);
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
HN.Mods.PageSwitcher = function() {
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

/****************************************/
/** Multiple Items Selection Module **/
/****************************************/
HN.Mods.MISM = function() {
	var me = this;
	var FamHdl = new HN.Classes.Families();
	var PdtHdl = new HN.Classes.Products();
	var id = arguments[0] ? arguments[0] : "";
	var SIbC = {}; // Selected Items by Category
	var cat1 = [];
	var nbsi = 0;
	var pan_left, pan_right, item_list; // DOM pointers
	var timerHide;
	var $domHdl; // jQuery DOM Handle
	
	var menu_cgf = {
		"cat1" : { "height" : 20 },
		"cat2" : { "height" : 20 },
		"cat3" : { "height" : 20 }
	};
	
	// Unfolded Menu Items DOM Handles
	var umidh = {
		"cat1" : [],
		"cat2" : [],
		"cat3" : []
	};
	
	// Jail categories
	var jail = {};
	
	this.GetID = function () { return id; };
	this.SetID = function (_id) { id = _id; };
	
	this.JailCategories = function () {
		jail = {};
		if (arguments.length > 0) jail.cat1 = arguments[0];
		if (arguments.length > 1) jail.cat2 = arguments[1];
		if (arguments.length > 2) jail.cat3 = arguments[2];
	};
	
	this.OpenCategories = function () {
		if (arguments.length > 0) {
			var k1 = 0;
			while (k1 < cat1.length) {
				if (cat1[k1].id == arguments[0]) {
					if (cat1[k1].folded) cat1[k1].a_DOMhdl.onclick();
					break;
				}
				k1++;
			}
			if (arguments.length > 1 && k1 < cat1.length) {
				var k2 = 0;
				while (k2 < cat1[k1].cat2.length) {
					if (cat1[k1].cat2[k2].id == arguments[1]) {
						cat1[k1].cat2[k2].a_DOMhdl.onclick();
						break;
					}
					k2++;
				}
				
				if (arguments.length > 2 && k2 < cat1[k1].cat2.length) {
					var k3 = 0;
					while (k3 < cat1[k1].cat2[k2].cat3.length) {
						if (cat1[k1].cat2[k2].cat3[k3].id == arguments[2]) {
							cat1[k1].cat2[k2].cat3[k3].a_DOMhdl.onclick();
							break;
						}
						k3++;
					}
				}
			}
		}
		
	};
	
	// Get Selected Items
	// Output : catID1, pdtID1, pdtID2, ... | catID2, ... | ...
	this.GetSelectedItems = function () {
		var k = 0;
		var ssi = "";
		for (var catID in SIbC)
		{
			ssi += (k++ > 0 ? "|" : "") + catID;
			for (var pdtID in SIbC[catID]) ssi += "," + pdtID;
		}
		return ssi;
	};
	
	/* Set Selected Items by Category
	 * Input : catID1, pdtID1, pdtID2, ... | catID2, ... | ...
	 * Make an array indexed with Category ID, containing array indexed with Items ID (using objects)
	 */
	this.SetSelectedItems = function (ssi) {
		item_list.innerHTML = "";
		
		// Make the array
		SIbC = {};
		var asic = ssi.split("|"); // Array of Selected Items and Categories
		for (var i = 0 ; i < asic.length; i++) {
			var asi = asic[i].split(","); // Array of Selected Items
			if (asi[0] != "" && asi.length > 1) {
				if (!SIbC[asi[0]]) SIbC[asi[0]] = {};
				// Any Selected Item is considered not valid by default
				for (var j = 1 ; j < asi.length; j++)
					SIbC[asi[0]][asi[j]] = false;
			}
		}
		
		// Initialise the Selected Items Numbers
		nbsi = 0;
		for (k1 in cat1) {
			var nbi1 = 0;
			cat1[k1].nbsi = 0;
			for (k2 in cat1[k1].cat2) {
				var nbi2 = 0;
				cat1[k1].cat2[k2].nbsi = 0;
				for (k3 in cat1[k1].cat2[k2].cat3) {
					var nbi3 = 0;
					cat1[k1].cat2[k2].cat3[k3].nbsi = 0;
					if (SIbC[cat1[k1].cat2[k2].cat3[k3].id]) // If this category contains some Selected Items
						for (var iID in SIbC[cat1[k1].cat2[k2].cat3[k3].id]) nbi3++;
					cat1[k1].cat2[k2].cat3[k3].nbsi = nbi3;
					cat1[k1].cat2[k2].cat3[k3].nbsi_DOMhdl.innerHTML = nbi3 > 0 ? "(" + nbi3 + ")" : "";
					//if (nbi3 > 0) alert(cat1[k1].cat2[k2].cat3[k3].name+"="+nbi3);
					nbi2 += nbi3;
				}
				nbi1 += nbi2;
				cat1[k1].cat2[k2].nbsi = nbi2;
				cat1[k1].cat2[k2].nbsi_DOMhdl.innerHTML = nbi2 > 0 ? "(" + nbi2 + ")" : "";
			}
			nbsi += nbi1;
			cat1[k1].nbsi = nbi1;
			cat1[k1].nbsi_DOMhdl.innerHTML = nbi1 > 0 ? "(" + nbi1 + ")" : "";
		}
		//alert(SIbC);
	};
	
	// Input : Array of Selected Items Objects [{ ItemsID : CategoryID }, {..}, ..]
	/*this.SetSelectedItemsPerCategory = function (AoSIO) {
		for (var k in AoSIO)
			for (var ItemID in AoSIO[k]) 
	}*/
	
	var hideStart = function (o) {
		timerHide = setTimeout(function(){
			o.style.display = "none";
		}, 500);
	};
	
	var hideStop = function () {
		clearTimeout(timerHide);
	};
	
	this.Build = function () {
		$domHdl = $("#"+id); // jQuery Dom Handle
		
		$domHdl.empty();
		$domHdl.addClass("MISM");
		
		// disable text selection
		if (typeof $domHdl.get(0).onselectstart != "undefined") $domHdl.get(0).onselectstart = function () { return false; } //IE
		else if (typeof $domHdl.get(0).style.MozUserSelect != "undefined") $domHdl.get(0).style.MozUserSelect = "none"; // Firefox
		else $domHdl.get(0).onmousedown = function () { return false; } // Others
			
		/* Left Menu HTML */
		
		// Main Categories Objects Array
		cat1 = FamHdl.GetFamilyChildren(FamHdl.GetFamilyByID(0));
		
		/* DOM structure
		<ul class="MISM-left"></ul>
		<div class="MISM-right">
			<div class="cat-tree">
				<span class="cat1"></span>
				<span class="cat2"></span>
				<span class="cat3"></span>
			</div>
			<div class="item-list"></div>
		</div>*/
		pan_left = document.createElement("ul");
		pan_left.className = "MISM-left";
		$domHdl.get(0).appendChild(pan_left);
		
		pan_right = document.createElement("div");
		pan_right.className = "MISM-right";
		$domHdl.get(0).appendChild(pan_right);
		
		var	zero_div = document.createElement("div");
		zero_div.className = "zero";
		$domHdl.get(0).appendChild(zero_div);
		
		var cat_tree = document.createElement("div");
		cat_tree.className = "cat-tree";
		pan_right.appendChild(cat_tree);
		
		item_list = document.createElement("div");
		item_list.className = "item-list";
		pan_right.appendChild(item_list);
		
		var tree_cat1 = document.createElement("span");
		tree_cat1.className = "cat1";
		cat_tree.appendChild(tree_cat1);
		
		var tree_cat2 = document.createElement("span");
		tree_cat2.className = "cat2";
		cat_tree.appendChild(tree_cat2);
		
		var tree_cat3 = document.createElement("span");
		tree_cat3.className = "cat3";
		cat_tree.appendChild(tree_cat3);
		
		for (k1 in cat1) {
			/* DOM structure
			<li class="menu-item-1-block">
				<a class="menu-item-1-title" href="id:_CAT1_ID_"><div class="pipe"></div>_CAT1_NAME_</a>
				<ul class="menu-item-1-sublist">
					...
				</ul>
			</li>
			*/
			var	li1 = document.createElement("li");
			li1.className = "menu-item-1-block";
			var	a1 = document.createElement("a");
			a1.className = "menu-item-1-title";
			a1.href = "id:" + cat1[k1].id;
			a1.innerHTML = "<span class=\"pipe\"></span><span class=\"text\">" + cat1[k1].name + "</span>";
			a1.onclick = function () {
				if (!jail.cat1 || jail.cat1 == cat1[this.index].id) {
					if (item_list.innerHTML == "")
						tree_cat1.innerHTML = cat1[this.index].name+" > ";
					if (cat1[this.index].folded) {
						$(cat1[this.index].DOMhdl).slideDown("fast", function () {
							cat1[this.index].folded = false;
							umidh.cat1 = []; // Only 1 unfolded lvl 1 category at a time
							umidh.cat1[this.index] = cat1.DOMhdl; // Adding this lvl 1 category
						});
					}
					else {
						$(cat1[this.index].DOMhdl).slideUp("fast", function () {
							cat1[this.index].folded = true;
							umidh.cat1 = []; // Clearing the Pointers to Unfolded lvl 1 categories
						});
					}
					
					for (var c1o in cat1) {
						if (cat1[this.index] != cat1[c1o] && !cat1[c1o].folded)
							$(cat1[c1o].DOMhdl).slideUp("fast", function () { cat1[this.index].folded = true; } );
					}
				}
				return false;
			};
			a1.onmouseover = function () {
				for (var i in umidh.cat2) {
					umidh.cat2[i].style.display = "none";
				}
				//for (var c2o in cat1[this.index].cat2) cat1[this.index].cat2[c2o].DOMhdl.style.display = "none";
			};
			var ul1 = document.createElement("ul");
			ul1.className = "menu-item-1-sublist";
			
			var	count1 = document.createElement("span");
			count1.className = "count";
			count1.innerHTML = "";
			
			li1.appendChild(a1);
			li1.appendChild(ul1);
			a1.appendChild(count1);
			
			// DOM helper pointers
			li1.index = a1.index = ul1.index = parseInt(k1);
			
			// Caterogry Object Vars
			cat1[k1].a_DOMhdl = a1; // DOM Handle to Action link
			cat1[k1].DOMhdl = ul1; //DOM Handle to SubMenu List
			cat1[k1].folded = true;
			cat1[k1].nbi = 0;
			cat1[k1].nbsi = 0;
			cat1[k1].nbsi_DOMhdl = count1;
			
			cat1[k1].cat2 = FamHdl.GetFamilyChildren(FamHdl.GetFamilyByID(cat1[k1].id));
			var cat2 = cat1[k1].cat2;
			for (k2 in cat2) {
				/* DOM structure
				<li class="menu-item-2-block">
					<a class="menu-item-2-title" href="id:_CAT2_ID_"><div class="dbl-arrow"></div>_CAT2_NAME_</a>
					<ul class="menu-item-2-sublist">
						...
					</ul>
				</li>
				*/
				var	li2 = document.createElement("li");
				li2.className = "menu-item-2-block";
				var	a2 = document.createElement("a");
				a2.className = "menu-item-2-title";
				a2.href = "id:" + cat2[k2].id;
				a2.innerHTML = "<span class=\"dbl-arrow\"></span><span class=\"text\">" + cat2[k2].name + "</span>";
				a2.onmouseover = function () {
					var cat2 = cat1[this.pindex].cat2;
					if (!jail.cat2 || jail.cat2 == cat2[this.index].id) {
						if (item_list.innerHTML == "")
							tree_cat2.innerHTML = cat2[this.index].name+" > ";
						
						hideStop();
						offset_top = (this.pindex+1) * menu_cgf.cat1.height + this.index * menu_cgf.cat2.height - (cat2[this.index].cat3.length-1) * menu_cgf.cat3.height;
						if (offset_top < 18) offset_top = 18;
						cat2[this.index].DOMhdl.style.top = offset_top + "px";
						cat2[this.index].DOMhdl.style.display = "block";
						
						// Global Pointer to this active lvl 2 category
						umidh.cat2 = [];
						umidh.cat2[this.index] = cat2[this.index].DOMhdl;
						
					}
					for (var c2o in cat2) {
						if (cat2[this.index] != cat2[c2o])
							cat2[c2o].DOMhdl.style.display = "none";
					}
				};
				a2.onclick = function () {
					if (!jail.cat2 || jail.cat2 == cat1[this.pindex].cat2[this.index].id) {
						if (item_list.innerHTML == "")
							tree_cat2.innerHTML = cat1[this.pindex].cat2[this.index].name+" > ";
					}
					return false;
				};
				
				var ul2 = document.createElement("ul");
				ul2.className = "menu-item-2-sublist";
				ul2.onmouseout = function () { hideStart(this); };
				ul2.onmouseover = function () { hideStop(); };
				
				var	count2 = document.createElement("span");
				count2.className = "count";
				count2.innerHTML = "";
				
				li2.appendChild(a2);
				li2.appendChild(ul2);
				a2.appendChild(count2);
				
				// DOM helper pointers
				li2.index = a2.index = ul2.index = parseInt(k2);
				li2.pindex = a2.pindex = ul2.pindex = parseInt(k1);
				
				// Caterogry Object Vars
				cat2[k2].a_DOMhdl = a2; // DOM Handle to Action link
				cat2[k2].DOMhdl = ul2; //DOM Handle to SubMenu List
				cat2[k2].folded = true;
				cat2[k2].nbi = 0;
				cat2[k2].nbsi = 0;
				cat2[k2].nbsi_DOMhdl = count2;
				
				cat2[k2].cat3 = FamHdl.GetFamilyChildren(FamHdl.GetFamilyByID(cat2[k2].id));
				var cat3 = cat2[k2].cat3;
				for (k3 in cat3) {
					/* DOM structure
					<li class="menu-item-3-block">
						<a class="menu-item-3-title" href="id:_CAT3_ID_">_CAT3_NAME_</a>
					</li>
					*/
					var	li3 = document.createElement("li");
					li3.className = "menu-item-3-block";
					var	a3 = document.createElement("a");
					a3.className = "menu-item-3-title";
					a3.href = "id:" + cat3[k3].id;
					a3.innerHTML = "<span class=\"text\">" + cat3[k3].name + "</span>";
					a3.onmouseover = function () {
						if (!jail.cat3 || jail.cat3 == cat1[this.ppindex].cat2[this.pindex].cat3[this.index].id) {
							if (item_list.innerHTML == "")
								tree_cat3.innerHTML = cat1[this.ppindex].cat2[this.pindex].cat3[this.index].name;
						}
					};
					a3.onclick = function () {
						var item_cat1 = cat1[this.ppindex];
						var item_cat2 = item_cat1.cat2[this.pindex];
						var item_cat3 = item_cat2.cat3[this.index];
						
						if (!jail.cat3 || jail.cat3 == item_cat3.id) {
							tree_cat1.innerHTML = item_cat1.name+" > ";
							tree_cat2.innerHTML = item_cat2.name+" > ";
							tree_cat3.innerHTML = item_cat3.name;
							
							item_cat2.DOMhdl.style.display = "none";
							PdtHdl.GetProductsByFamID([item_cat2.cat3[this.index].id], function (items) {
								/* DOM structure
								<div class="item-block">
									<img class="item-image" src="11850598.jpg" alt=""/>
									<div class="item-prop">
										<div class="item-title">Cuve de transport de gas-oil pour utilitaire</div>
										<div class="item-check"></div>
										<div class="item-desc">Station service gas-oil 340 litres 9795-9797</div>
									</div>
								</div>
								*/
								item_list.innerHTML = "";
								
								for (var id in items) {
									// The Selected Item is Valid (it does exist)
									if (SIbC[item_cat3.id] && typeof SIbC[item_cat3.id][items[id].id] != "undefined")
										SIbC[item_cat3.id][items[id].id] = true;
									
									var	item_block = document.createElement("a");
									item_block.className = "item-block";
									if (SIbC[item_cat3.id] && SIbC[item_cat3.id][items[id].id]) item_block.className += " selected";
									item_block.href = "id:" + items[id].id;
									item_block.onclick = function () {
										if (this.className == "item-block") {
											if (item_cat3.nbsi == 0) SIbC[item_cat3.id] = {};
											SIbC[item_cat3.id][this.href.substr(3,10)] = true;
											
											this.className = "item-block selected";
											item_cat1.nbsi_DOMhdl.innerHTML = "(" + ++item_cat1.nbsi + ")";
											item_cat2.nbsi_DOMhdl.innerHTML = "(" + ++item_cat2.nbsi + ")";
											item_cat3.nbsi_DOMhdl.innerHTML = "(" + ++item_cat3.nbsi + ")";
										}
										else {
											this.className = "item-block";
											item_cat1.nbsi_DOMhdl.innerHTML = --item_cat1.nbsi > 0 ? "(" + item_cat1.nbsi + ")" : "";
											item_cat2.nbsi_DOMhdl.innerHTML = --item_cat2.nbsi > 0 ? "(" + item_cat2.nbsi + ")" : "";
											item_cat3.nbsi_DOMhdl.innerHTML = --item_cat3.nbsi > 0 ? "(" + item_cat3.nbsi + ")" : "";
											
											if (item_cat3.nbsi == 0) delete SIbC[item_cat3.id]
											else delete SIbC[item_cat3.id][this.href.substr(3,10)];
										}
										return false;
									};
									var	item_image = document.createElement("img");
									item_image.className = "item-image";
									item_image.src = "../AJAXressources/modules/products-images.php?id=" + items[id].id;
									item_image.alt = items[id].id + " image";
									var	item_prop = document.createElement("div");
									item_prop.className = "item-prop";
									var	item_title = document.createElement("div");
									item_title.className = "item-title";
									item_title.innerHTML = items[id].name;
									var	item_check = document.createElement("div");
									item_check.className = "item-check";
									var	item_desc = document.createElement("div");
									item_desc.className = "item-desc";
									item_desc.innerHTML = items[id].fastdesc;
									
									item_block.appendChild(item_image);
									item_block.appendChild(item_prop);
									item_prop.appendChild(item_title);
									item_prop.appendChild(item_check);
									item_prop.appendChild(item_desc);
									
									item_list.appendChild(item_block);
								}
								
								// Those Selected Items are not valid
								/*if (SIbC[item_cat3.id]) {
									for (var itemID in SIbC[item_cat3.id]) {
										if (!SIbC[item_cat3.id][itemID]) {
											delete SIbC[item_cat3.id][itemID];
											item_cat1.nbsi_DOMhdl.innerHTML = --item_cat1.nbsi > 0 ? "(" + item_cat1.nbsi + ")" : "";
											item_cat2.nbsi_DOMhdl.innerHTML = --item_cat2.nbsi > 0 ? "(" + item_cat2.nbsi + ")" : "";
											item_cat3.nbsi_DOMhdl.innerHTML = --item_cat3.nbsi > 0 ? "(" + item_cat3.nbsi + ")" : "";
										}
									}
								}*/
								
							}, 2);
						}
						return false;
					};
					var	count3 = document.createElement("span");
					count3.className = "count";
					count3.innerHTML = "";
					
					li3.appendChild(a3);
					a3.appendChild(count3);
					
					// DOM helper pointers
					li3.index = a3.index = parseInt(k3);
					li3.pindex = a3.pindex = parseInt(k2);
					li3.ppindex = a3.ppindex = parseInt(k1);
					
					// Caterogry Object Vars
					cat3[k3].a_DOMhdl = a3; // DOM Handle to Action link
					cat3[k3].nbi = 0;
					cat3[k3].nbsi = 0;
					cat3[k3].nbsi_DOMhdl = count3;
					
					ul2.appendChild(li3);
				}
				ul1.appendChild(li2);
			}
			pan_left.appendChild(li1);
		}
	};
};

/****************************************/
/**  Simple Category Selection Module  **/
/****************************************/
HN.Mods.SCSM = function() {
	var that = this;
	var FamHdl = new HN.Classes.Families();
	var cat1 = [];
	var family = {id : 0, name : "", ref_name : ""};
	var id = "";
	var built = false;
	var win = null, bg = null, menu = null, colg = null, titre = null, sf = null, colc = null, desc = null, ssf = null;
	
	this.setID = function(_id) { id = _id; }
	this.getCurFamID = function() { return family.id; }
	this.getCurFam = function() { return family; }
	
	/*var fam_sort_ref_name = function(a, b) {
		if (families[a][ref_name] > families[b][ref_name]) return 1;
		if (families[a][ref_name] < families[b][ref_name]) return -1;
		return 0;
	};*/

	this.Build = function () {
		if ((win = document.getElementById(id)))
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
			
			cat1 = FamHdl.GetFamilyChildren(FamHdl.GetFamilyByID(0));
			for (k1 in cat1) {
				var a = document.createElement("a");
				a.href = "#";
				a.family_id = cat1[k1].id;
				a.appendChild(document.createTextNode(cat1[k1].name));
				a.index = k1;
				menu.childrenList[a.family_id] = a;
				menu.appendChild(a);
				menu.appendChild(document.createTextNode(" "));
				
				a.Select = function () { // lors de la sélection
					if (this.parentNode.current_f && this.parentNode.current_f != this) this.parentNode.current_f.UnSelect();
					this.parentNode.current_f = this;
					this.className = 'current';
					
					titre.innerHTML = cat1[this.index].name;
					//families[this.family_id][children].sort(fam_sort_ref_name); // Tri par nom référence pour affichage
					
					purge(sf);
					purge(ssf);
					for (var node = sf.childNodes.length-1; node >= 0; node--) sf.removeChild(sf.childNodes[node]);
					for (var node = ssf.childNodes.length-1; node >= 0; node--) ssf.removeChild(ssf.childNodes[node]);
					sf.current_sf = null;
					ssf.current_ssf = null;
					sf.childrenList = [];
					ssf.childrenList = [];
					
					cat1[this.index].cat2 = FamHdl.GetFamilyChildren(FamHdl.GetFamilyByID(cat1[this.index].id));
					var cat2 = cat1[this.index].cat2;
					for (k2 in cat2) {
						var a2 = document.createElement("a");
						a2.href = "#";
						a2.family_id = cat2[k2].id;
						a2.appendChild(document.createTextNode(cat2[k2].name));
						a2.index = k2;
						sf.childrenList[a2.family_id] = a2;
						sf.appendChild(a2);
						
						a2.Select = function () {
							if (this.parentNode.current_sf && this.parentNode.current_sf != this) this.parentNode.current_sf.UnSelect();
							this.parentNode.current_sf = this;
							this.className = 'currentUnfolded';
							
							//families[this.family_id][children].sort(fam_sort_ref_name); // Tri par nom référence pour affichage
							
							purge(ssf);
							for (var node = ssf.childNodes.length-1; node >= 0; node--) ssf.removeChild(ssf.childNodes[node]);
							ssf.current_ssf = null;
							ssf.childrenList = [];
							cat2[this.index].cat3 = FamHdl.GetFamilyChildren(FamHdl.GetFamilyByID(cat2[this.index].id));
							var cat3 = cat2[this.index].cat3;
							for (k3 in cat3) {
								var a3 = document.createElement("a");
								a3.href = "#";
								a3.family_id = cat3[k3].id;
								a3.appendChild(document.createTextNode(cat3[k3].name));
								a3.index = k3;
								ssf.childrenList[a3.family_id] = a3;
								ssf.appendChild(a3);
								
								a3.onclick = function () {
									family.id = this.family_id;
									family.name = cat3[this.index].name;
									family.ref_name = cat3[this.index].ref_name;
									if (this.parentNode.current_ssf && this.parentNode.current_ssf != this) this.parentNode.current_ssf.className = '';
									this.parentNode.current_ssf = this;
									this.className = 'current';
									
									desc.innerHTML = "Famille " + cat3[this.index].name;
									
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
				aa.onclick = function() { this.apointer.onclick(); return false; }
				sf.appendChild(aa);
			}
			built = true;
		}
	};
	
	this.SelectFamByID = function (fid) {
		var fidr = fid;
		var famTree = [];
		var n = 0;
		while (fidr != 0) {
			famTree[n++] = fidr;
			fidr = FamHdl.GetFamilyByID(fidr).parentid;
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
	};
	
}
