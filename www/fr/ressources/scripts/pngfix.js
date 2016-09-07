

function pngImg2alphaLoad (class2fix) {
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
}