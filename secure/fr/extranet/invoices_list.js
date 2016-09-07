var MonthLabels = new Array('janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre');
var DayLabes = new Array('Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi');

//var dateBegin = new Date(); dateBegin.setTime(1296514800*1000); // 1296514800 = 01/02/2011  // starts on 01/02/2011
//var dateCur   = new Date();
var dateEnd   = new Date();

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
	else if(year == 2011) m.options.length = dateCur.getMonth() + 1;
        else m.options.length = dateCur.getMonth() + 2;

        m.options[0].value = 0;
	m.options[0].text  = COMMON_ALL_M;
	for (var i = 1; i < m.options.length; i++)
	{
          if(year == 2011){
                m.options[i].value = i+1;
		m.options[i].text  = MonthLabels[i];
          }else{
		m.options[i].value = i;
		m.options[i].text  = MonthLabels[i-1];
              }
	}
        FillDayOptions(yID, mID, dID);
}

function FillDayOptions(yID, mID, dID) {
	var y = document.getElementById(yID);
	var m = document.getElementById(mID);
	var d = document.getElementById(dID);
	var year  = parseInt(y.options[y.options.selectedIndex].value);
	var month = parseInt(m.options[m.options.selectedIndex].value);
        var day = parseInt(d.value);

	if (year == parseInt(dateCur.getFullYear()) && month == parseInt(dateCur.getMonth()+1))
	{
		var date = new Date(dateCur);
                day = date.getDate()+1;
	}
	else
	{
		var date = dateCur;
                day = date.getDate();
	}
        d.value = day;
        FillInterval(year, month, day);

}

function SetDateOptions(yid, mid, did, year, month, day) {
	document.getElementById(yid).value = parseInt(year,10);
	document.getElementById(yid).onchange();
	document.getElementById(mid).value = parseInt(month,10);
	document.getElementById(mid).onchange();
	document.getElementById(did).value = parseInt(day,10);
}

function FillInterval(y, m, d){
//  var dateBegin;
//  var dateCur;

  if(m != 0 && y != 0){
    dateBegin = '01/'+m+'/'+y;
    dateEnd = d+'/'+m+'/'+y;
  }

  document.getElementById('DateBegin').value = dateBegin;
  document.getElementById('DateEnd').value = dateEnd;
}

