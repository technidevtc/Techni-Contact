function str_repeat(i, m) {
  for (var o = []; m > 0; o[--m] = i);
  return(o.join(''));
}

function sprintf () {
  var i = 0, a, f = arguments[i++], o = [], m, p, c, x;
  while (f) {
    if (m = /^[^\x25]+/.exec(f)) o.push(m[0]);
    else if (m = /^\x25{2}/.exec(f)) o.push('%');
    else if (m = /^\x25(?:(\d+)\$)?(\+)?(0|'[^$])?(-)?(\d+)?(?:\.(\d+))?([b-fosuxX])/.exec(f)) {
      if (((a = arguments[m[1] || i++]) == null) || (a == undefined)) throw("Too few arguments.");
      if (/[^s]/.test(m[7]) && (typeof(a) != 'number'))
        throw("Expecting number but found " + typeof(a));
      switch (m[7]) {
        case 'b':
          a = a.toString(2);
          break;
        case 'c':
          a = String.fromCharCode(a);
          break;
        case 'd':
          a = parseInt(a);
          break;
        case 'e':
          a = m[6] ? a.toExponential(m[6]) : a.toExponential();
          break;
        case 'f':
          a = m[6] ? parseFloat(a).toFixed(m[6]) : parseFloat(a);
          break;
        case 'o':
          a = a.toString(8);
          break;
        case 's':
          a = ((a = String(a)) && m[6] ? a.substring(0, m[6]) : a);
          break;
        case 'u':
          a = Math.abs(a);
          break;
        case 'x':
          a = a.toString(16);
          break;
        case 'X':
          a = a.toString(16).toUpperCase();
          break;
      }
      a = (/[def]/.test(m[7]) && m[2] && a > 0 ? '+' + a : a);
      c = m[3] ? m[3] == '0' ? '0' : m[3].charAt(1) : ' ';
      x = m[5] - String(a).length;
      p = m[5] ? str_repeat(c, x) : '';
      o.push(m[4] ? a + p : p + a);
    }
    else throw ("Huh ?!");
    f = f.substring(m[0].length);
  }
  return o.join('');
}

function log(s) {
  var date = new Date();
  var log = document.getElementById("log");
  if (!log) {
    var _log = document.createElement("div");
    _log.id = "log";
    _log.style.position = "absolute";
    _log.style.left = "1000px";
    _log.style.top = "75px";
    _log.style.width = "500px";
    _log.style.height = "500px";
    _log.style.border = "1px solid #000000";
    _log.style.overflow = "auto";
    _log.style.font = "normal 10px lucida console, arial, sans-serif";
    _log.style.color = "#ffffff";
    _log.style.textAlign = "left";
    _log.style.backgroundColor = "#444444";
    document.body.appendChild(_log);
    log = _log;
  }
  log.innerHTML += sprintf("%02d:%02d:%02d.%03d : %s", date.getHours(), date.getMinutes(), date.getSeconds(), date.getMilliseconds(), s)+"<br/>\n";
  //log.innerHTML += date.getHours()+":"+date.getMinutes()+":"+date.getSeconds()+"."+date.getMilliseconds()+" : "+s+"<br/>\n";
  log.scrollTop = log.scrollHeight;
  return;
}
function tostr(o) {
  var s = "";
  for (var i in o) s+= i + "=" + o[i] + "<br/>\n";
  log(s);
}

if (!window.HN) HN = window.HN = {};
if (!HN.TC) HN.TC = {};
if (!HN.TC.BO) HN.TC.BO = {};
if (!HN.TC.BO.Leads) HN.TC.BO.Leads = {};

$(function(){
  HN.TC.BO.Leads.Init();
});

var ILTcat, ILTpdt;
HN.TC.BO.Leads.Init = function (_advID) {
	
  var tri = 0;
  var trs = $("table.item-list tbody tr").get();
  $(trs).filter("[class='']:odd").addClass("odd");
  $(trs).find("td:gt(0)").click(function(){
    var path = '';
    if(window.location.pathname != '/fr/manager/clients/' ){
      document.location.href = path+"lead-detail.php?id="+$(this).closest("tr").find("td.id").html();
    }
  });
	
  var createTreeLevel = function(dn) {
    // Adding | and + pics
    var $td = $(trs[tri]).find("td:first");
    for (var i=1; i<dn; i++)
      $td.append("<div class=\"more\"></div>");
    var div_folder = document.createElement("div");
    div_folder.className = "add";
    $td.append(div_folder);
		
    tri++;
    var trs_cat = [];
    var trs_start = tri;
    while(tri < trs.length) {
      if ($(trs[tri]).hasClass("selem"+dn)) {
        for (var i=1; i<=dn; i++)
          $(trs[tri]).find("td:first").append("<div class=\"more\"></div>");
        trs_cat.push(trs[tri]);
        tri++;
      }
      else if ($(trs[tri]).hasClass("scat"+(dn+1))) {
        trs_cat.push(trs[tri]);
        createTreeLevel(dn+1);
      }
      else {
        break;
      }
    }
    var trs_over = trs.slice(trs_start, tri);
    $(trs_cat).filter(":odd").addClass("odd");
		
    $(div_folder).click(function(){
      if ($(div_folder).hasClass("add")) {
        $(trs_cat).show();
        $(trs_cat).find("td:first div.sub").click().click();
      }
      else
        $(trs_over).hide();
			
      $(div_folder).toggleClass("add").toggleClass("sub");
      return false;
    });
  };
	
  while (tri < trs.length) {
    if ($(trs[tri]).hasClass("scat1"))
      createTreeLevel(1);
    else
      tri++;
  }
	
/* Category Selection Dialog Box */
/*HN.TC.BO.MS.CSDB = new HN.Mods.DialogBox("CSDB");
	HN.TC.BO.MS.CSDB.setTitleText("Choisir une famille");
	HN.TC.BO.MS.CSDB.setMovable(true);
	HN.TC.BO.MS.CSDB.showCancelButton(true);
	HN.TC.BO.MS.CSDB.showValidButton(true);
	HN.TC.BO.MS.CSDB.setValidFct(function() {
		var family = HN.TC.BO.MS.CB.getCurFam();
		if (family.id != 0) {
			ILTcat.add(family.id, family.name);
			$catListString.val(ILTcat.getDataIDsAsString());
			HN.TC.BO.MS.CSDB.Hide();
		}
	});
	HN.TC.BO.MS.CSDB.Build();
	*/
};

var COMMON_ALL_M = "Tous";
var COMMON_ALL_F = "Toutes";
var COMMON_ALL_CHOICE = "COMMON_ALL_CHOICE";

var MonthLabels = new Array('janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre');
var DayLabes = new Array('Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi');

var dateBegin = new Date();
dateBegin.setTime(1072911600*1000);
var dateCur   = new Date();

function FillYearOptions(yID, mID, dID) {
  var y = document.getElementById(yID);
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

function FillMonthOptions(yID, mID, dID) {
  var y = document.getElementById(yID);
  var m = document.getElementById(mID);
  var year = parseInt(y.options[y.options.selectedIndex].value);
	
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

function FillDayOptions(yID, mID, dID) {
  var y = document.getElementById(yID);
  var m = document.getElementById(mID);
  var d = document.getElementById(dID);
  var year  = parseInt(y.options[y.options.selectedIndex].value);
  var month = parseInt(m.options[m.options.selectedIndex].value);
	
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
	
  d.options[0].value = 0;
  d.options[0].text  = COMMON_ALL_M;
  for (var i = 1; i < d.options.length; i++)
  {
    date.setDate(i);
    d.options[i].value = i;
    d.options[i].text  = DayLabes[date.getDay()] + " " + i;
  }
}

function FillYearOptions2(yID, mID, dID) {
  var y = document.getElementById(yID);
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

function FillMonthOptions2(yID, mID, dID) {
  var y = document.getElementById(yID);
  var m = document.getElementById(mID);
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

function FillDayOptions2(yID, mID, dID) {
  var y = document.getElementById(yID);
  var m = document.getElementById(mID);
  var d = document.getElementById(dID);
  var year  = parseInt(y.options[y.options.selectedIndex].value);
  var month = parseInt(m.options[m.options.selectedIndex].value);
	
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
	
  d.options[0].value = 0;
  d.options[0].text  = " - ";
  for (var i = 1; i < d.options.length; i++)
  {
    date.setDate(i);
    d.options[i].value = i;
    d.options[i].text  = DayLabes[date.getDay()] + " " + i;
  }
}

function gotoPage(page) {
  if (!isNaN(page = parseInt(page))) {
    document.LeadList.page.value = page;
    document.LeadList.submit();
  }
}
function LeadSort(order) {
  document.LeadList.sort.value = order;
  document.LeadList.submit();
}

function SetDateOptions(yid, mid, did, year, month, day) {
  document.getElementById(yid).value = parseInt(year,10);
  document.getElementById(yid).onchange();
  document.getElementById(mid).value = parseInt(month,10);
  document.getElementById(mid).onchange();
  document.getElementById(did).value = parseInt(day,10);
}

function ShowDateSection() {
  $("#DateFilter").show();
  $("#DateIntervalFilter").hide();
  $("input[name='dateFilterType']").val("simple");
}

function ShowDateIntervalSection() {
  $("#DateFilter").hide();
  $("#DateIntervalFilter").show();
  $("input[name='dateFilterType']").val("interval");
}
