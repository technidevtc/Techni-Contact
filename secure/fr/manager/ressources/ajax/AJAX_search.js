if (!window.HN) HN = window.HN = {};
if (!HN.UI) HN.UI = {};
if (!HN.Cvars) HN.Cvars = {};
HN.Cvars.AJAX_search = HN.TC.Locals.RessourcesURL+"ajax/AJAX_search.php";
/*
// Author: Diego Perini <dperini@nwbox.com>
HN.UI.getSelectionStart = function (o) {
	if (o.createTextRange) {
		var r = document.selection.createRange().duplicate();
		r.moveEnd('character', o.value.length);
		if (r.text == '') return o.value.length;
		return o.value.lastIndexOf(r.text);
	}
	else return o.selectionStart;
}

// Author: Diego Perini <dperini@nwbox.com>
HN.UI.getSelectionEnd = function (o) {
	if (o.createTextRange) {
		var r = document.selection.createRange().duplicate();
		r.moveStart('character', -o.value.length);
		return r.text.length;
	}
	else return o.selectionEnd;
}
*/
HN.UI.AutoCompletion = function (_searchBox) {
	var _this = this;
	this.defaultValue = "";
	this.queryURL = HN.Cvars.AJAX_search;
	this.searchBox = _searchBox;
	this.results = [];
	this.overResult = -1;
	this.visible = false;
	this.requestTimeout = null;
	this.lastWords = "";
	this.doHideProps = true;
	
	if (this.searchBox.value == "") this.searchBox.value = this.defaultValue;
	//this.searchBox.onblur = function () { _this.hideProps(); };
	this.searchBox.onfocus = function () {
		if (this.value != "" && this.value == _this.defaultValue)
			_this.defaultValue = this.value = "";
	};
	this.searchBox.onblur = function (e) {
		/*if (!e) var e = window.event;
		e.cancelBubble = true;
		if (e.stopPropagation) e.stopPropagation();*/
		setTimeout( function () {if (_this.doHideProps) _this.hideProps(); else _this.doHideProps = true;}, 10 );
	};
	this.searchBox.onkeyup = function (e) {
		if (!e) e = window.event;
		if (_this.lastWords != this.value && this.value.length > 1) {
			_this.lastWords = this.value;
			clearTimeout(_this.requestTimeout);
			_this.requestTimeout = setTimeout( function () {
					$.ajax({
						data: "search="+encodeURIComponent(_this.lastWords),
						dataType: "json",
						error: function (XMLHttpRequest, textStatus, errorThrown) {
							//log("ERROR for request loading "+ajax_ps+" videos from ind="+ajax_ind);
						},
						success: function (data, textStatus) {
							_this.clearProps();
							//_this.addPreProp("Nous vous suggérons");
							for (var i = 0; i < data.length; i++) {
								_this.addProp(data[i][0], data[i][1]);
							}
							_this.showProps();
						},
						type: "GET",
						url: _this.queryURL
					})
				}, 250);
		}
	};
	this.searchBox.onkeydown = function (e) {
		if (!e) e = window.event;
		if (e.keyCode == 38) {
			if (_this.visible) {
				if (_this.overResult == -1)
					_this.overResult = 0;
				else
					_this.results[_this.overResult--].parentNode.onmouseout();
				
				if (_this.overResult < 0) _this.overResult = _this.results.length-1;
				_this.results[_this.overResult].parentNode.onmouseover();
				_this.lastWords = _this.searchBox.value = _this.results[_this.overResult].innerHTML;
			}
			else {
				if (_this.results.length > 0) _this.showProps();
			}
		}
		else if (e.keyCode == 40) {
			if (_this.visible) {
				if (_this.overResult == -1)
					_this.overResult = 0;
				else
					_this.results[_this.overResult++].parentNode.onmouseout();
				
				if (_this.overResult >= _this.results.length) _this.overResult = 0;
				_this.results[_this.overResult].parentNode.onmouseover();
				_this.lastWords = _this.searchBox.value = _this.results[_this.overResult].innerHTML;
			}
			else {
				if (_this.results.length > 0) _this.showProps();
			}
		}
	};
	
	this.hideProps = function () {this.visible = false;this.propsBox.style.visibility = "hidden";};
	this.showProps = function () {this.visible = true;this.propsBox.style.visibility = "visible";};
	this.addPreProp = function (preProp) {
		var tr = document.createElement("tr");
		var td_prop = document.createElement("td");
				td_prop.className = "prop";
				td_prop.innerHTML = "<i>"+preProp+"</i>";
		var td_results = document.createElement("td");
				td_results.className = "results";
		tr.appendChild(td_prop);
		tr.appendChild(td_results);
		this.propsContainer.appendChild(tr);
	};
	this.addProp = function (prop, results) {
		var tr = document.createElement("tr");
				tr.onclick = function () {
                                  var searchBoxClassName = _this.searchBox.className.split(' ');
					_this.searchBox.value = prop;
                                        $('input[type=hidden][name=search]').val(prop);
					_this.hideProps();
                                        $('.'+searchBoxClassName[0]).parentsUntil('form').parent().submit();
				};
				//tr.onmousedown = function () { _this.doHideProps = false; };
				tr.onmouseover = function () {
					this.className = "over";
					for (var i = 0; i < _this.results.length; i++)
						if (this == _this.results[i].parentNode) _this.overResult = i;
				};
				tr.onmouseout = function () {this.className = "";};
		var td_prop = document.createElement("td");
				td_prop.className = "prop";
				td_prop.innerHTML = prop;
		var td_results = document.createElement("td");
				td_results.className = "results";
				td_results.innerHTML = results == "" ? "" : results + " résultats";
		tr.appendChild(td_prop);
		tr.appendChild(td_results);
		this.results.push(td_prop);
		this.propsContainer.appendChild(tr);
	};
	this.clearProps = function () {
		this.results = [];
		this.overResult = -1;
		$(this.propsContainer).empty();
	};
	
	function findDOMpos (o) {
		var left = o.offsetLeft, top = o.offsetTop;
		while(o.offsetParent) {
			o = o.offsetParent;
			left += o.offsetLeft;
			top += o.offsetTop;
		}
		return {x: left, y: top};
	};
	
	this.propsBox = document.createElement("table");
	this.propsBox.className = "auto-completion-box";
	this.propsBox.style.minWidth = (this.searchBox.offsetWidth-2) + "px";
	var pos = findDOMpos(this.searchBox);
	this.propsBox.style.top = (pos.y + this.searchBox.offsetHeight + 1) + "px";
	this.propsBox.style.left = (pos.x + 1) + "px";
	this.propsBox.id = this.searchBox.id != "" ? this.searchBox.id+"-AC-box" : "";
	this.propsBox.onmousedown = function () {_this.doHideProps = false;};
	
	/* Disable Selection */
	this.propsBox.onselectstart = function () {return false;};
	this.propsBox.unselectable = "on";
	this.propsBox.style.MozUserSelect = "none";
	this.propsBox.cursor = "default";
	
	this.propsContainer = document.createElement("tbody");
	this.propsBox.appendChild(this.propsContainer);
	
	document.body.appendChild(this.propsBox);
};
