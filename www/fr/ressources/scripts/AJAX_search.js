
HN.TC.AutoCompletion = function (searchBoxID) {
	var _this = this;
	//this.defaultValue = "Que recherchez-vous au sein de Techni-contact ?";
	this.queryURL = HN.TC.Locals.RessourcesURL+"ajax/AJAX_search.php";
	this.searchBox = typeof searchBoxID == "string" ? document.getElementById(searchBoxID) : searchBoxID;
	this.results = [];
	this.overResult = -1;
	this.visible = false;
	this.requestTimeout = null;
	this.lastWords = "";
	this.doHideProps = true;
	this.Xoffset = 34;
	this.Yoffset = 3;
	
	//this.searchBox.value = this.defaultValue;
	//this.searchBox.onblur = function () { _this.hideProps(); };
	this.searchBox.onfocus = function () {
		if (this.value != "" && this.value == _this.defaultValue)
			_this.defaultValue = this.value = "";

    $('#searchAds').hide();
	};
	this.searchBox.onblur = function (e) {
		/*if (!e) var e = window.event;
		e.cancelBubble = true;
		if (e.stopPropagation) e.stopPropagation();*/
		setTimeout( function () {if (_this.doHideProps) _this.hideProps(); else _this.doHideProps = true;}, 10 );
	};

        jQuery.fn.highlight = function(pat) {
           function innerHighlight(node, pat) {
            var skip = 0;
            if (node.nodeType == 3) {
             var pos = node.data.toUpperCase().indexOf(pat);
             if (pos >= 0) {
              var spannode = document.createElement('span');
              spannode.className = 'search-highlight';
              var middlebit = node.splitText(pos);
              var endbit = middlebit.splitText(pat.length);
              var middleclone = middlebit.cloneNode(true);
              spannode.appendChild(middleclone);
              middlebit.parentNode.replaceChild(spannode, middlebit);
              skip = 1;
             }
            }
            else if (node.nodeType == 1 && node.childNodes && !/(script|style)/i.test(node.tagName)) {
             for (var i = 0; i < node.childNodes.length; ++i) {
              i += innerHighlight(node.childNodes[i], pat);
             }
            }
            return skip;
           }
           return this.each(function() {
            innerHighlight(this, pat.toUpperCase());
           });
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
							if(data.categories.length > 0){
                                                          _this.addPreProp("Catégories");
                                                          for (var i = 0; i < data.categories.length; i++) {
                                                                  _this.addPropList(data.categories[i][0], data.categories[i][1], data.categories[i][2]);
                                                          }
                                                        }
                                                        if(data.products.length > 0){
                                                          _this.addPreProp("Produits");
                                                          for (var i = 0; i < data.products.length; i++) {
                                                                  _this.addProp(data.products[i][0], data.products[i][3], data.products[i][2], data.products[i][1], data.products[i][4]);
                                                          }
                                                        }
							_this.showProps();
                                                        $('#header-search-input-AC-box').highlight(_this.lastWords);
						},
						type: "GET",
						url: _this.queryURL
					})
				}, 250);
		}
	};
	this.searchBox.onkeydown = function (e) {
		if (!e) e = window.event;
                var overResultWatch = 0;
		if (e.keyCode == 38) {
			if (_this.visible) {
				if (_this.overResult == -1)
					_this.overResult = 0;
				else
					_this.results[_this.overResult--].parentNode.onmouseout();
                                var formerOverResult = _this.overResult;
                                if($(_this.results[_this.overResult]).attr('class') != 'catProp')
                                  _this.overResult = _this.overResult-2;
                                
                                if(_this.overResult == 3 && formerOverResult== 5)_this.overResult++; //passage between products and categories
				if (_this.overResult < 0) _this.overResult = _this.results.length-1;
				_this.results[_this.overResult].parentNode.onmouseover();

                                overResultWatch = $(_this.results[_this.overResult]).attr('class') != 'catProp' ? _this.overResult-1 : _this.overResult;

                                _this.lastWords = _this.searchBox.value = $(_this.results[overResultWatch]).find('.search-bold').length > 0 ? $(_this.results[overResultWatch]).find('.search-bold').text() : $(_this.results[overResultWatch]).text();
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

                                overResultWatch = $(_this.results[_this.overResult]).attr('class') != 'catProp' ? _this.overResult-1 : _this.overResult;
                                
                                _this.lastWords = _this.searchBox.value = $(_this.results[overResultWatch]).find('.search-bold').length > 0 ? $(_this.results[overResultWatch]).find('.search-bold').text() : $(_this.results[overResultWatch]).text();
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
				td_prop.className = "propList";
                                $(td_prop).attr('colspan', '3');
				td_prop.innerHTML = preProp;
		tr.appendChild(td_prop);
		this.propsContainer.appendChild(tr);
	};
        this.addPropList = function (propList, results, refCat) {
		var tr = document.createElement("tr");
				tr.onmouseover = function () {
					this.className = "over";
				};

				tr.onmouseout = function () {this.className = "";};
		var td_propList = document.createElement("td");
				td_propList.className = "catProp";
                                $(td_propList).attr('colspan', '2');
                                /*var currentSearch = location.search.substring(1);
                                var currentSearchList = currentSearch.split('&');
                                var newSearch = HN.TC.Locals.URL;
                                for (var i=0;i<currentSearchList.length;i++) {
                                  if(!currentSearchList[i].match(/catDetailID=/gi))
                                    newSearch = newSearch+location.pathname.substring(1)+'?'+currentSearchList[i];
                                }
                                newSearch = newSearch+'&catDetailID='+idCat;*/
				td_propList.innerHTML = '<a href="'+HN.TC.Locals.URL+'familles/'+refCat+'.html" class="displayblock">'+propList+'</a>';
                var td_results = document.createElement("td");
				td_results.className = "results";
				td_results.innerHTML = results == "" ? "" : "(" + results + ")";
		tr.appendChild(td_propList);
                tr.appendChild(td_results);
		this.results.push(td_propList);
		this.propsContainer.appendChild(tr);
	};
	this.addProp = function (prop, fastdesc, id, nbCat, urlProd) { 
		var tr = document.createElement("tr");
				tr.onclick = function () {
					var classNames = this.firstChild.className.split(' ');
                                        if(classNames[1] == 'MultiCat'){
                                          _this.searchBox.value = prop;
                                          _this.hideProps();
                                          $('form[class=search]').submit();
                                        }else{
                                          document.location.href = urlProd;
                                        }
				};
				//tr.onmousedown = function () { _this.doHideProps = false; };
				tr.onmouseover = function () {
					this.className = "over";
					for (var i = 0; i < _this.results.length; i++)
						if (this == _this.results[i].parentNode) _this.overResult = i;
				};
				tr.onmouseout = function () {this.className = "";};
    var td_image = document.createElement("td");
				td_image.className = "propImage "+(nbCat > 1 ? 'MultiCat' : 'SingleCat');
        td_image.style.width = "72px";
        td_image.style.textAlign = "center";
				td_image.innerHTML = '<img src="'+HN.TC.Locals.PRODUCTS_IMAGE_INC+'thumb_small/'+id+'-1.jpg" alt="miniature" class="vmaib" style="max-width: 62px; max-height: 62px" /><div class="vsma"></div>';
		var td_prop = document.createElement("td");
				td_prop.className = "prop";
				td_prop.innerHTML = '<span class="search-bold">'+prop+'</span><br />'+fastdesc;
		var td_results = document.createElement("td");
				td_results.className = "results";
				td_results.innerHTML = '';//results == "" ? "" : "(" + results + ")";
		tr.appendChild(td_image);
                tr.appendChild(td_prop);
		tr.appendChild(td_results);
		this.results.push(td_prop);
                this.results.push(td_image);
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
	this.propsBox.style.top = (pos.y + this.searchBox.offsetHeight + this.Yoffset) + "px";
//	this.propsBox.style.right = (document.documentElement.clientWidth - (pos.x + this.searchBox.offsetWidth + this.Xoffset)) + "px";
	this.propsBox.id = this.searchBox.id != "" ? this.searchBox.id+"-AC-box" : "";
	this.propsBox.onmousedown = function () {_this.doHideProps = false;};
	
	/* Disable Selection */
	this.propsBox.onselectstart = function () {return false;};
	this.propsBox.unselectable = "on";
	this.propsBox.style.MozUserSelect = "none";
	this.propsBox.cursor = "pointer";
	
	this.propsContainer = document.createElement("tbody");
	this.propsBox.appendChild(this.propsContainer);
	
	document.body.appendChild(this.propsBox);
};
