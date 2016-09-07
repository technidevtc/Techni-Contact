/*****************************************************************************/
/******						Javascript Classes							******/
/*****************************************************************************/

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
		window.alert("Your browser does not support the XMLHTTPRequest object.");
		return false;
	}
	
	this.QueryA = function (query) {
		if (RequestInProgess) document.getElementById(PerfReqLabel_id).innerHTML = "Already performing a request";
		else {
			RequestInProgess = true;
			xhr.readyState == 0;
			xhr.onreadystatechange = function () { that.ProcessResponse(); }
			xhr.open('GET', query, true);
			xhr.send(null);
		}
	}
	
	this.QueryS = function (query) {
		if (RequestInProgess) document.getElementById(PerfReqLabel_id).innerHTML = "Already performing a request";
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
					document.getElementById(PerfReqLabel_id).innerHTML = '<br/>';
				}
				else alert('Error while performing the request.');
			}
			else document.getElementById(PerfReqLabel_id).innerHTML = "Performing Request";
		}
		catch(e) {
			RequestInProgess = false;
			if (browser == "ie") xhr.abort();
			alert("An exception occured : " + e.description);
		}
	}
	
}
