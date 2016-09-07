function str_repeat(i, m) { for (var o = []; m > 0; o[--m] = i); return(o.join('')); }

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
        case 'b': a = a.toString(2); break;
        case 'c': a = String.fromCharCode(a); break;
        case 'd': a = parseInt(a); break;
        case 'e': a = m[6] ? a.toExponential(m[6]) : a.toExponential(); break;
        case 'f': a = m[6] ? parseFloat(a).toFixed(m[6]) : parseFloat(a); break;
        case 'o': a = a.toString(8); break;
        case 's': a = ((a = String(a)) && m[6] ? a.substring(0, m[6]) : a); break;
        case 'u': a = Math.abs(a); break;
        case 'x': a = a.toString(16); break;
        case 'X': a = a.toString(16).toUpperCase(); break;
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
		_log.style.zIndex = "999999";
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

$(function(){
  $msForm = $(document.msForm);
	// Error after page submit
	if (errorFields.length > 0) {
		var $SerrorFields = "";
		for(var i=0; i < errorFields.length; i++) {
			$SerrorFields += (i?",":"")+"label[for='"+errorFields[i]+"']";
		}
		$msForm.find($SerrorFields).css({color: "#b00000"});
	}
});