<!--

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

// date form
var COMMON_ALL_M = "Tous";
var COMMON_ALL_F = "Toutes";
var COMMON_ALL_CHOICE = "COMMON_ALL_CHOICE";

var MonthLabels = new Array('janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre');
var DayLabes = new Array('Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi');

var dateBegin = new Date(); dateBegin.setTime(1072911600*1000);
var dateCur   = new Date();

function FillYearOptions(yID, mID, dID)
{
	var y = $('#'+yID)[0];
	yb = parseInt(dateBegin.getFullYear());
	yc = parseInt(dateCur.getFullYear());
	y.options.length = (yc-yb) + 2;

	y.options[0].value = 0;
	y.options[0].text  = COMMON_ALL_F;
	for (var i = 1; i < y.options.length; i++)
	{
		y.options[y.options.length-i].value = yb + i - 1;
		y.options[y.options.length-i].text  = yb + i - 1;
	}
	FillMonthOptions(yID, mID, dID);
}

function FillMonthOptions(yID, mID, dID)
{
	var y = $('#'+yID)[0];
	var m = $('#'+mID)[0];
	var year = parseInt($(y.options[$(y.options.selectedIndex)]).value);

	if (year == 0) m.options.length = 1;
	else if (year < dateCur.getFullYear()) m.options.length = 13;
	else m.options.length = dateCur.getMonth() + 2;

	m.options[0].value = 0;
	m.options[0].text  = COMMON_ALL_M;
	for (var i = 1; i < m.options.length; i++)
	{
		m.options[i].value = i;
		m.options[i].text  = MonthLabels[i-1];
	}
	FillDayOptions(yID, mID, dID);
}

function FillDayOptions(yID, mID, dID)
{
	var y = $('#'+yID)[0];
	var m = $('#'+mID)[0];
	var d = $('#'+dID)[0];

	var year  = parseInt(y.value);
        var month = parseInt(m.value);

	if (year == parseInt(dateCur.getFullYear()) && month == parseInt(dateCur.getMonth()+1))
	{
		var date = new Date(dateCur);
		d.options.length = date.getDate() + 1;
	}
	else
	{
		var date = new Date(year, month, 0);
		if (month == 0) d.options.length = 1;
		else d.options.length = date.getDate() + 1;
	}

	d.options.value = 0;
	d.options.text  = COMMON_ALL_M;
	for (var i = 1; i < d.options.length; i++)
	{
		date.setDate(i);
		d.options[i].value = i;
		d.options[i].text  = DayLabes[date.getDay()] + " " + i;
	}
}

function FillYearOptions2(yID, mID, dID)
{
	var y = $('#'+yID)[0];
	yb = parseInt(dateBegin.getFullYear());
	yc = parseInt(dateCur.getFullYear());
	y.options.length = (yc-yb) + 2;

	y.options[0].value = 0;
	y.options[0].text  = " - ";
	for (var i = 1; i < y.options.length; i++)
	{
		y.options[y.options.length-i].value = yb + i - 1;
		y.options[y.options.length-i].text  = yb + i - 1;
	}
	FillMonthOptions2(yID, mID, dID);
}

function FillMonthOptions2(yID, mID, dID)
{
	var y = $('#'+yID)[0];
	var m = $('#'+mID)[0];
	var year = parseInt(y.options[y.options.selectedIndex].value);

	if (year == 0) m.options.length = 1;
	else if (year < dateCur.getFullYear()) m.options.length = 13;
	else m.options.length = dateCur.getMonth() + 2;

	m.options[0].value = 0;
	m.options[0].text  = " - ";
	for (var i = 1; i < m.options.length; i++)
	{
		m.options[i].value = i;
		m.options[i].text  = MonthLabels[i-1];
	}
	FillDayOptions2(yID, mID, dID);
}

function FillDayOptions2(yID, mID, dID)
{
	var y = $('#'+yID)[0];
	var m = $('#'+mID)[0];
	var d = $('#'+dID)[0];
	var year  = parseInt(y.value);
	var month = parseInt(m.value);

	if (year == parseInt(dateCur.getFullYear()) && month == parseInt(dateCur.getMonth()+1))
	{
		var date = new Date(dateCur);
		d.options.length = date.getDate() + 1;
	}
	else
	{
		var date = new Date(year, month, 0);
		if (month == 0) d.options.length = 1;
		else d.options.length = date.getDate() + 1;
	}

	d.options.value = 0;
	d.options.text  = " - ";
	for (var i = 1; i < d.options.length; i++)
	{
		date.setDate(i);
		d.options[i].value = i;
		d.options[i].text  = DayLabes[date.getDay()] + " " + i;
	}
}

function SetDateOptions(yid, mid, did, year, month, day)
{
	$('#'+yid)[0].value = year;
	$('#'+yid)[0].onchange();
	$('#'+mid)[0].value = month;
	$('#'+mid)[0].onchange();
	$('#'+did)[0].value = day;
}

function ShowDateSection()
{
	document.getElementById('DateFilter').style.display = "block";
	document.getElementById('DateIntervalFilter').style.display = "none";
        $('input[name=dateFilterType]')[0].value = "simple";
}

function ShowDateIntervalSection()
{
	document.getElementById('DateFilter').style.display = "none";
	document.getElementById('DateIntervalFilter').style.display = "block";
        $('input[name=dateFilterType]')[0].value = "interval";
}

//-->