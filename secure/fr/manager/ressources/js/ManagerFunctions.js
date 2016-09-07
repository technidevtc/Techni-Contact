var mousex = 0;
var mousey = 0;
var grabx = 0;
var graby = 0;

var dragobjs = new Array;

function dragobj(elem)
{
	this.backZindex = elem.style.zIndex;
	elem.style.zIndex = 100;
	this.handle = elem;
	this.orix = elem.offsetLeft;
	this.oriy = elem.offsetTop;
	this.elex = elem.offsetLeft;
	this.eley = elem.offsetTop;
}

function falsefunc() { return false; } // used to block cascading events

function init()
{
	document.onmousemove = update; // update(event) implied on NS, update(null) implied on IE
	update();
}

function getMouseXY(e) // works on IE6,FF,Moz,Opera7
{ 
	if (!e) e = window.event; // works on IE, but not NS (we rely on NS passing us the event)

	if (e)
	{
		if (e.pageX || e.pageY)
		{ // this doesn't work on IE6!! (works on FF,Moz,Opera7)
			mousex = e.pageX;
			mousey = e.pageY;
		}
		else if (e.clientX || e.clientY)
		{ // works on IE6,FF,Moz,Opera7
			// Note: I am adding together both the "body" and "documentElement" scroll positions
			//       this lets me cover for the quirks that happen based on the "doctype" of the html page.
			//         (example: IE6 in compatibility mode or strict)
			//       Based on the different ways that IE,FF,Moz,Opera use these ScrollValues for body and documentElement
			//       it looks like they will fill EITHER ONE SCROLL VALUE OR THE OTHER, NOT BOTH 
			//         (from info at http://www.quirksmode.org/js/doctypes.html)
			mousex = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
			mousey = e.clientY + document.body.scrollTop + document.documentElement.scrollTop;
		}
	}
}

function update(e)
{
	getMouseXY(e); // NS is passing (event), while IE is passing (null)
}

function grab()
{
	document.onmousedown = falsefunc; // in NS this prevents cascading of events, thus disabling text selection
	for (var i=0; i< grab.arguments.length; i++)
	{
		dragobjs[i] = new dragobj(grab.arguments[i]);
	}
	document.onmousemove = drag;
	document.onmouseup = drop;
	grabx = mousex;
	graby = mousey;
	update();
}

function drag(e) // parameter passing is important for NS family 
{
	for (var obj in dragobjs)
	{
		dragobjs[obj].elex = dragobjs[obj].orix + (mousex-grabx);
		dragobjs[obj].eley = dragobjs[obj].oriy + (mousey-graby);
		dragobjs[obj].handle.style.position = "absolute";
		dragobjs[obj].handle.style.left = (dragobjs[obj].elex).toString(10) + 'px';
		dragobjs[obj].handle.style.top  = (dragobjs[obj].eley).toString(10) + 'px';
	}
	update(e);
	return false; // in IE this prevents cascading of events, thus text selection is disabled
}

function drop()
{
	for (var obj in dragobjs)
	{
		//alert(obj+" "+dragobjs[obj].elex);
		dragobjs[obj].handle.style.zIndex = dragobjs[obj].backZindex;
		delete(dragobjs[obj]);
	}
	update();
	document.onmousemove = update;
	document.onmouseup = null;
	document.onmousedown = null;   // re-enables text selection on NS
}

init();

function makeRequest(request, response_function)
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
	
	xhr.onreadystatechange = function () { eval(response_function + '(xhr);'); }// response_function(xhr); //function() { response_function(xhr); };
	xhr.open('GET', request, true);
	xhr.send(null);
	//alert(xhr.responseText);
	//document.write(xhr.responseText);
}
