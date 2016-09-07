if (!window.HN) HN = window.HN = {};
/*
HN.include_js = function (file) {
    var html_doc = document.getElementsByTagName('head')[0];
    js = document.createElement('script');
    js.setAttribute('type', 'text/javascript');
    js.setAttribute('src', file);
    html_doc.appendChild(js);

    js.onreadystatechange = function () {
        if (js.readyState == 'complete') {
            alert('JS onreadystate fired');
        }
    }

    js.onload = function () {
        alert('JS onload fired');
    }
    return false;
}
*/

// taken from old jquery lib version
HN.userAgent = navigator.userAgent.toLowerCase();
HN.browser = {
	version: (HN.userAgent.match( /.+(?:rv|it|ra|ie)[\/: ]([\d.]+)/ ) || [])[1],
	safari: /webkit/.test( HN.userAgent ),
	opera: /opera/.test( HN.userAgent ),
	msie: /msie/.test( HN.userAgent ) && !/opera/.test( HN.userAgent ),
	mozilla: /mozilla/.test( HN.userAgent ) && !/(compatible|webkit)/.test( HN.userAgent )
};

HN.png2alpha = function (class2fix) {
	if ((HN.browser.msie && HN.browser.version >= 5.5 && HN.browser.version < 7.0)) {
	//} else {
		
		var class2fix = String(arguments[0] ? arguments[0] : "");
		if (class2fix != "")
			var class2fix = new RegExp(arguments[0]);
	
		var imgs = document.getElementsByTagName("img");
		for (var i=0; i<imgs.length; i++) {
			var img = imgs[i];
			var imgName = img.src.toLowerCase();
			if (imgName.substring(imgName.length-3, imgName.length) == "png" && (class2fix == "" || class2fix.test(img.className))) {
				//img.onload = function () { alert(this.src+" loaded !"); };
				//alert(img.readyState);
				//var s="";for (k in img) s+=k+"="+img[k]+"<br/>\n";
				//document.getElementById("log").innerHTML = s;
				
				var span = document.createElement("span");
				if (img.id) span.id = img.id;
				if (img.className) span.className = img.className;
				if (img.title) span.title = img.title;
				span.style.display = "inline-block";
				span.style.filter = "progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true, sizingMethod=scale, src='" + img.src + "')";
				span.style.width = img.width+"px";
				span.style.height = img.height+"px";
				if (img.parentNode.nodeName.toLowerCase() == "a") {
					span.style.cursor = "pointer";
					span.onclick = function () { document.location.href = img.parentNode.href; };
				}
				// only works for ie <= 7.0
				for (attr in img.style) {
					if (attr != "cssText" && attr != "accelerator") {
						if ((typeof img.style[attr] == "boolean" && img.style[attr] != false) ||
								(typeof img.style[attr] == "number" && img.style[attr] != 0) ||
								(typeof img.style[attr] == "string" && img.style[attr] != "")) {
							span.style[attr] = img.style[attr];
						}
					}
				}
				img.parentNode.replaceChild(span, img);
				i--;
			}
		}
	}
};

HN.File = function (_name, _url, _dataType) {
	this.name = _name;
	this.url = _url;
	this.dataType = _dataType;
	this.data = null;
	this.loaded = false;
};

HN.FileManager = function () {};
HN.FileManager.prototype = {
	files: [],
	add: function (name, url, dataType) {
		this.files[name] = new HN.File(name, url, dataType);
	},
	getUrl: function (name) { return this.files[name] ? this.files[name].url : null },
	getDataType: function (name) { return this.files[name] ? this.files[name].dataType : null },
	getData: function (name, callback_function) {
		if (this.files[name]) {
			if (this.files[name].loaded) {
				callback_function(this.files[name].data);
			}
			else {
				var _this = this;
				$.ajax({
					async: true,
					data: "",
					dataType: this.files[name].dataType,
					error: function (XMLHttpRequest, textStatus, errorThrown) {
						callback_function(null);
					},
					success: function (data, textStatus) {
						_this.files[name].loaded = true;
						_this.files[name].data = data;
						callback_function(_this.files[name].data);
					},
					type: "GET",
					url: this.files[name].url
				});
			//callback_function(this.files[name].url);
			}
		}
		else {
			callback_function(null);
		}
	}
};

// Singleton per URL
HN.URLinfos_instances = [];
HN.URLinfos = function () {
	var url;
	if (arguments.length == 0)
		url = window.location.href;
	else
		url = arguments[0];
	
	if (!HN.URLinfos_instances[url]) {
		HN.URLinfos_instances[url] = new function () {
			this.absURL = url;
			this.protocol = this.absURL.substring(0, this.absURL.indexOf("://"));
			var noprotocolURL = this.absURL.substring(this.protocol.length + "://".length, this.absURL.length);
			this.siteURL = noprotocolURL.substring(0, noprotocolURL.indexOf("/"));
			this.baseURL = this.protocol + "://" + this.siteURL + "/";
			this.relURL = this.absURL.substring(this.baseURL.length, this.absURL.length);
			
			this.tree = [];
			var treeURLparts = this.relURL.split("/");
			var relURL = "";
			for (var i = 0; i < treeURLparts.length; i++) {
				relURL += treeURLparts[i] + (i != treeURLparts.length-1 ? "/" : "");
				this.tree[i] = {
					text: treeURLparts[i],
					relURL: relURL,
					absURL: this.baseURL + relURL
				};
			}
			
			var lastURLpart = this.tree[this.tree.length-1].text.split("#");
			
			this.fileName = lastURLpart[0];
			this.fileNameNoExt = this.fileName.substr(0, this.fileName.lastIndexOf("."));
			this.fileNameExt = this.fileName.substr(this.fileName.lastIndexOf(".")+1, this.fileName.length);
			this.isIndex = this.fileName.toLowerCase() == "" || /^index\.\w+/.test(this.fileName.toLowerCase());
			
			this.anchor = lastURLpart.length > 1 ? lastURLpart[1] : "";
		}
	}
	return HN.URLinfos_instances[url];
};

/*****************************************************************************/
/******						Drag'n Drop Functions						******/
/*****************************************************************************/

HN.DnD = {
	mousex : 0,
	mousey : 0,
	grabx : 0,
	graby : 0,
	dragobjs : [],
	dragobj : function (elem) {
		this.backZindex = elem.style.zIndex;
		elem.style.zIndex = 100;
		this.handle = elem;
		this.orix = elem.offsetLeft;
		this.oriy = elem.offsetTop;
		this.elex = elem.offsetLeft;
		this.eley = elem.offsetTop;
	},
	dragInit : function () {
		document.onmousemove = HN.DnD.update; // update(event) implied on NS, update(null) implied on IE
		HN.DnD.update();
	},
	getMouseXY : function (e) { // works on IE6,FF,Moz,Opera7
		if (!e) e = window.event; // works on IE, but not NS (we rely on NS passing us the event)
		
		if (e) {
			if (e.pageX || e.pageY) { // this doesn't work on IE6!! (works on FF,Moz,Opera7)
				HN.DnD.mousex = e.pageX;
				HN.DnD.mousey = e.pageY;
			}
			else if (e.clientX || e.clientY) {
				// works on IE6,FF,Moz,Opera7
				// Note: I am adding together both the "body" and "documentElement" scroll positions
				//       this lets me cover for the quirks that happen based on the "doctype" of the html page.
				//         (example: IE6 in compatibility mode or strict)
				//       Based on the different ways that IE,FF,Moz,Opera use these ScrollValues for body and documentElement
				//       it looks like they will fill EITHER ONE SCROLL VALUE OR THE OTHER, NOT BOTH 
				//         (from info at http://www.quirksmode.org/js/doctypes.html)
				HN.DnD.mousex = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
				HN.DnD.mousey = e.clientY + document.body.scrollTop + document.documentElement.scrollTop;
			}
		}
	},
	update : function (e) {
		HN.DnD.getMouseXY(e); // NS is passing (event), while IE is passing (null)
	},
	grab : function () {
		document.onmousedown = function () { return false; }; // in NS this prevents cascading of events, thus disabling text selection
		for (var i=0; i< arguments.length; i++) {
			HN.DnD.dragobjs[i] = new HN.DnD.dragobj(arguments[i]);
		}
		document.onmousemove = HN.DnD.drag;
		document.onmouseup = HN.DnD.drop;
		HN.DnD.grabx = HN.DnD.mousex;
		HN.DnD.graby = HN.DnD.mousey;
		HN.DnD.update();
	},
	drag : function (e) { // parameter passing is important for NS family 
		for (var obj in HN.DnD.dragobjs) {
			HN.DnD.dragobjs[obj].elex = HN.DnD.dragobjs[obj].orix + (HN.DnD.mousex-HN.DnD.grabx);
			HN.DnD.dragobjs[obj].eley = HN.DnD.dragobjs[obj].oriy + (HN.DnD.mousey-HN.DnD.graby);
			HN.DnD.dragobjs[obj].handle.style.position = "absolute";
			HN.DnD.dragobjs[obj].handle.style.left = (HN.DnD.dragobjs[obj].elex).toString(10) + 'px';
			HN.DnD.dragobjs[obj].handle.style.top  = (HN.DnD.dragobjs[obj].eley).toString(10) + 'px';
		}
		HN.DnD.update(e);
		return false; // in IE this prevents cascading of events, thus text selection is disabled
	},
	drop : function () {
		for (var obj in HN.DnD.dragobjs) {
			//alert(obj+" "+dragobjs[obj].elex);
			HN.DnD.dragobjs[obj].handle.style.zIndex = HN.DnD.dragobjs[obj].backZindex;
			delete(HN.DnD.dragobjs[obj]);
		}
		HN.DnD.update();
		document.onmousemove = HN.DnD.update;
		document.onmouseup = null;
		document.onmousedown = null;   // re-enables text selection on NS
	}
};

HN.DnD.dragInit();

/******************/
/** Class Window **/
/******************/

HN.Window = function () {

	var me = this;
	var win = arguments.length > 0 ? (typeof arguments[0] == "string" ? document.getElementById(arguments[0]) : arguments[0]) : "";
	var movable = false;
	var showMinButton = false, showMaxButton = false, showCloseButton = false, showValidButton = false, showCancelButton = false;
	var ValidFct = null;
	var shadow = false;
	var titletext = "";
	var width = 200, height = 100;
	
	var built = false;
	var close_button = null, cancel_button = null, valid_button = null, min_button = null, max_button = null, move_img = null;
	
	this.setID = function(_id) { win = document.getElementById(_id); };
	this.setMovable = function(_movable) { movable = _movable; };
	this.showMinButton = function(_showMinButton) { showMinButton = _showMinButton; };
	this.showMaxButton = function(_showMaxButton) { showMaxButton = _showMaxButton; };
	this.showCloseButton = function(_showCloseButton) { showCloseButton = _showCloseButton; };
	this.showValidButton = function(_showValidButton) { showValidButton = _showValidButton; };
	this.setValidFct = function(_ValidFct) { ValidFct = _ValidFct; };
	this.showCancelButton = function(_showCancelButton) { showCancelButton = _showCancelButton; };
	//this.setCloseButtonFct = function(_CloseButtonFct) { CloseButtonFct = _CloseButtonFct; }
	//this.setShadow = function(_shadow) { shadow = _shadow; };
	this.setTitleText = function(_titletext) { titletext = _titletext; };

	this.Build = function () {
		if (win) {
			
			var title_bar = document.createElement("div");
			title_bar.className = "window_title_bar";
			if (showCloseButton) {
				close_button = document.createElement("div");
				close_button.className = "close_img close_img_up";
				close_button.onmouseout = function () { if (this.className == "close_img close_img_down") this.className = "close_img close_img_up"; }
				close_button.onmousedown = function () { this.className = "close_img close_img_down"; }
				close_button.onmouseup = function () { if (this.className == "close_img close_img_down") { this.className = "close_img close_img_up"; me.Hide(); } }
				title_bar.appendChild(close_button);
			}
			if (showCancelButton) {
				cancel_button = document.createElement("div");
				cancel_button.className = "cancel_img cancel_img_up";
				cancel_button.onmouseout = function () { if (this.className == "cancel_img cancel_img_down") this.className = "cancel_img cancel_img_up"; }
				cancel_button.onmousedown = function () { this.className = "cancel_img cancel_img_down"; }
				cancel_button.onmouseup = function () { if (this.className == "cancel_img cancel_img_down") { this.className = "cancel_img cancel_img_up"; me.Hide(); } }
				title_bar.appendChild(cancel_button);
			}
			if (showValidButton) {
				valid_button = document.createElement("div");
				valid_button.className = "valid_img valid_img_up";
				valid_button.onmouseout = function () { if (this.className == "valid_img valid_img_down") this.className = "valid_img valid_img_up"; }
				valid_button.onmousedown = function () { this.className = "valid_img valid_img_down"; }
				valid_button.onmouseup = function () { if (this.className == "valid_img valid_img_down") { this.className = "valid_img valid_img_up"; ValidFct(); } }
				title_bar.appendChild(valid_button);
			}
			
			var inner_title_bar = document.createElement("div");
			if (movable) {
				inner_title_bar.onmousedown = function() { HN.DnD.grab(win); }
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
			
			built = true;
		}
	};
	
	this.Show = function () {
		if (built) win.style.visibility = "visible";
	};
	
	this.Hide = function () {
		if (built) win.style.visibility = "hidden";
	};
	
};
