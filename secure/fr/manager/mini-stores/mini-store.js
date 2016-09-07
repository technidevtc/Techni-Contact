function str_repeat(i, m) {for (var o = []; m > 0; o[--m] = i);return(o.join(''));}

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
        case 'b':a = a.toString(2);break;
        case 'c':a = String.fromCharCode(a);break;
        case 'd':a = parseInt(a);break;
        case 'e':a = m[6] ? a.toExponential(m[6]) : a.toExponential();break;
        case 'f':a = m[6] ? parseFloat(a).toFixed(m[6]) : parseFloat(a);break;
        case 'o':a = a.toString(8);break;
        case 's':a = ((a = String(a)) && m[6] ? a.substring(0, m[6]) : a);break;
        case 'u':a = Math.abs(a);break;
        case 'x':a = a.toString(16);break;
        case 'X':a = a.toString(16).toUpperCase();break;
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
if (!HN.TC.BO.MS) HN.TC.BO.MS = {};

$(function(){HN.TC.BO.MS.Init();});

var ILTcat, ILTpdt, ILThomeSort;
HN.TC.BO.MS.Init = function (triggerType) {
	$msForm = $(document.msForm);
	$itemString = $msForm.find("input[name='itemString']");
	$pdtListString = $msForm.find("input[name='pdtListString']");
	$catListString = $msForm.find("input[name='catListString']");
        $homeSortListString = $msForm.find("input[name='homeSortListString']");
	// Error after page submit
	if (errorFields.length > 0) {
		var $SerrorFields = "";
		for(var i=0; i < errorFields.length; i++) {
			$SerrorFields += (i?",":"")+"label[for='"+errorFields[i]+"']";
		}
		$msForm.find($SerrorFields).css({color: "#b00000"});
	}
	
	// Items List Tables
	ILTcat = new HN.TC.BO.MS.ILT({
		cols: ["id","name"],
		editFunc: function($tds){
			HN.TC.BO.MS.CB.SelectFamByID($tds.eq(0).html());
			HN.TC.BO.MS.CSDB.Show();
		},
		$listString: $catListString,
		DOMhdl: $("#categories-selection-section table.item-list-table tbody").get(0),
		$baseRow: $("<tr>\
			<td class=\"id\"></td>\
			<td class=\"name\"></td>\
			<td class=\"actions\">\
				<div class=\"ib icon icon-up\" title=\"monter\"></div>\
				<div class=\"ib icon icon-down\" title=\"descendre\"></div>\
				<div class=\"ib icon icon-edit\" title=\"éditer\"></div>\
				<div class=\"ib icon icon-del\" title=\"supprimer\"></div>\
			</td>\
		</tr>")
	});
	ILTpdt = new HN.TC.BO.MS.ILT({
		cols: ["id","catID","name"],
		colsDataAsString: [0,1],
		$listString: $pdtListString,
		DOMhdl: $("#products-selection-section table.item-list-table tbody").get(0),
		$baseRow: $("<tr>\
			<td class=\"catID\"></td>\
			<td class=\"id\"></td>\
			<td class=\"name\"></td>\
			<td class=\"actions\">\
				<div class=\"ib icon icon-up\" title=\"monter\"></div>\
				<div class=\"ib icon icon-down\" title=\"descendre\"></div>\
				<div class=\"ib icon icon-del\" title=\"supprimer\"></div>\
			</td>\
		</tr>")
	});
        ILThomeSort = new HN.TC.BO.MS.ILT({
		cols: ["id","name"],
		editFunc: function($tds){
			HN.TC.BO.MS.CB.SelectHomeMS($tds.eq(0).html());
                        //HN.TC.BO.MS.CB.SelectFamByID($tds.eq(0).html());
			$('#home_carrousel_sort_dialog').dialog('open');
                        //HN.TC.BO.MS.CSDB.Show();
		},
		$listString: $homeSortListString,
		DOMhdl: $("#carrousel-selection-section table.item-list-table tbody").get(0),
		$baseRow: $("<tr>\
			<td class=\"id\"></td>\
			<td class=\"name\"></td>\
			<td class=\"actions\">\
				<div class=\"ib icon icon-up\" title=\"monter\"></div>\
				<div class=\"ib icon icon-down\" title=\"descendre\"></div>\
			</td>\
		</tr>")
	});
	
	// By default
	switch (defaultType) {
		case "pdt":
      ILTpdt.setData(itemsAsJSON);
      $pdtListString.val(ILTpdt.getDataIDsAsString());
      if (ILTpdt.invalidCount > 0)
        $("#pdtListInvalid").show().html("Il y a "+ILTpdt.invalidCount+" produits invalides sélectionnés.<br/>Il est préférable d'enregistrer à nouveau cette mini-boutique avant toute autre modification");
      else
        $("#pdtListInvalid").hide().html("");
      break;
		case "cat":
      ILTcat.setData(itemsAsJSON);
      $catListString.val(ILTcat.getDataIDsAsString());
      if (ILTcat.invalidCount > 0)
        $("#catListInvalid").show().html("Il y a "+ILTpdt.invalidCount+" familles invalides sélectionnées.<br/>Il est préférable d'enregistrer à nouveau cette mini-boutique avant toute autre modification");
      else
        $("#catListInvalid").hide().html("");
      break;
		default:break;
	}
        
   switch(triggerType){
      case "homeSort":
      ILThomeSort.setData(MSHomeSort);
      $homeSortListString.val(ILThomeSort.getDataIDsAsString());
      $('#home_carrousel_sort_dialog').dialog('open');
      if (ILTcat.invalidCount > 0)
        $("#catListInvalid").show().html("Il y a "+ILTpdt.invalidCount+" familles invalides sélectionnées.<br/>Il est préférable d'enregistrer à nouveau cette mini-boutique avant toute autre modification");
      else
        $("#catListInvalid").hide().html("");
      break;
      default:break;
        }
	
	// radio change
	$msForm.find("input[name='type']").change(function(){
		switch($(this).val()) {
			case "pdt":
				$("#products-selection-section").show();
				$("#categories-selection-section").hide();
				break;
			case "cat":
				$("#categories-selection-section").show();
				$("#products-selection-section").hide();
				break;
			default:break;
		}
	});
	$msForm.find("input[name='type']:checked").change();
	
	// buttons
	$("#btn-add-category")
		.mousedown(function(){$(this).addClass("down");})
		.mouseup(function(){if ($(this).hasClass("down")) {$(this).removeClass("down");HN.TC.BO.MS.CSDB.Show();}})
		.mouseleave(function(){$(this).removeClass("down");});
	$("#btn-select-products")
		.mousedown(function(){$(this).addClass("down");})
		.mouseup(function(){if ($(this).hasClass("down")) {$(this).removeClass("down");HN.TC.BO.MS.MISM.SetSelectedItems($pdtListString.val());HN.TC.BO.MS.MPSDB.Show();}})
		.mouseleave(function(){$(this).removeClass("down");});
	
	// Submit
	$msForm.submit(function(){
		switch($msForm.find("input[name='type']:checked").val()) {
			case "pdt":
				$itemString.val($pdtListString.val());
				break;
			case "cat":
				$itemString.val($catListString.val());
				break;
			default:break;
		}
		return true;
	});
	
	//ILT.setData();
	/* Category Selection Dialog Box */
	HN.TC.BO.MS.CB = new HN.Mods.SCSM();
	HN.TC.BO.MS.CB.setID("CSDB");
	HN.TC.BO.MS.CB.Build();

	HN.TC.BO.MS.CSDB = new HN.Mods.DialogBox("CSDB");
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
	
	/* Multiple Product Selection Dialog Box*/
	HN.TC.BO.MS.MISM = new HN.Mods.MISM("MPSDB");
	HN.TC.BO.MS.MISM.Build();

	HN.TC.BO.MS.MPSDB = new HN.Mods.DialogBox("MPSDB");
	HN.TC.BO.MS.MPSDB.setTitleText("Choisir une liste de produits");
	HN.TC.BO.MS.MPSDB.setMovable(true);
	HN.TC.BO.MS.MPSDB.showCancelButton(true);
	HN.TC.BO.MS.MPSDB.showValidButton(true);
	HN.TC.BO.MS.MPSDB.setValidFct(function(){
		//log(HN.TC.BO.MS.MISM.GetSelectedItems());
    //ILTpdt.setData(HN.TC.BO.MS.MISM.GetSelectedItemsJSON());
    //$pdtListString.val(ILTpdt.getDataIDsAsString());
    //$pdtListString.val(HN.TC.BO.MS.MISM.GetSelectedItems());
    //log(ILTpdt.getDataIDsAsString());
    //log(HN.TC.BO.MS.MISM.GetSelectedItems());
		//tostr(HN.TC.BO.MS.MISM.GetSelectedItemsJSON());
		//tostr(HN.TC.BO.MS.MISM.GetSelectedItemsJSON()[0]);
		// ajax request to get names, also validate the products id's
    $.ajax({
			async: true,
			cache: false,
			data: {action: "get products", data: HN.TC.BO.MS.MISM.GetSelectedItems()},
			dataType: "json",
			error: function (XMLHttpRequest, textStatus, errorThrown) {alert("Fatal error while loading the Products List");},
			success: function (data, textStatus) {
				if (data.error && data.error != "")
					alert(data.error);
				else {
          ILTpdt.setData(data.data); // refreshing the list table
          var validDataIdString = ILTpdt.getDataIDsAsString(); // new valid string of products id's
          $pdtListString.val(validDataIdString);
          HN.TC.BO.MS.MISM.SetSelectedItems(validDataIdString);
          HN.TC.BO.MS.MPSDB.Hide();
				}
			},
			timeout: 10000,
			type: "POST",
			url: "AJAX_JSON_server.php"
		});
	});
	HN.TC.BO.MS.MPSDB.Build();
	
};

/* Item List Table */
HN.TC.BO.MS.ILT = function (settings) {
	var me = this;
	this.count = 0;
  this.invalidCount = 0;
	this.cols = settings.cols;
	this.editFunc = settings.editFunc;
	this.colsDataAsString = settings.colsDataAsString ? settings.colsDataAsString : [0];
	this.$listString = settings.$listString;
	this.DOMhdl = typeof settings.DOMhdl == "string" ? document.getElementById(settings.DOMhdl) : settings.DOMhdl;
	this.$DOMhdl = $(this.DOMhdl);
	this.$baseRow = settings.$baseRow;
};
HN.TC.BO.MS.ILT.prototype = {
	$baseRow: null,
	getBaseRow: function () {
		return this.$baseRow.clone(true).get(0);
	},
	offsetIndex: 1,
	add: function () {
		var me = this;
		var tr = this.getBaseRow();
		var $tr = $(tr);
		
		for (var ci=0, cc=this.cols.length; ci<cc; ci++)
			$tr.find("td."+this.cols[ci]).html(arguments.length > ci ? arguments[ci] : "");
		
		$tr.find("div.icon-up").click(function(){
			var tr_index = parseInt($("tr", me.DOMhdl).index(tr));
			if (tr_index > 0) {
				me.DOMhdl.insertBefore(tr, me.$DOMhdl.find("tr").get(tr_index-1));
				me.$listString.val(me.getDataIDsAsString());
			}
		});
		$tr.find("div.icon-down").click(function(){
			var $trs = $("tr", me.DOMhdl);
			var tr_index = parseInt($trs.index(tr));
			if (tr_index < $trs.length-1) {
				me.DOMhdl.insertBefore(me.$DOMhdl.find("tr").get(tr_index+1), tr);
				me.$listString.val(me.getDataIDsAsString());
			}
		});
		$tr.find("div.icon-edit").click(function(){
			if (me.editFunc)
				me.editFunc($tr.find("td"));
		});
		$tr.find("div.icon-del").click(function(){
			me.del(parseInt(me.$DOMhdl.find("tr").index(tr)));
			me.$listString.val(me.getDataIDsAsString());
		});
		
		this.DOMhdl.appendChild(tr);
	},
	del: function (tr_index) {
    var tr = this.$DOMhdl.find("tr:eq("+tr_index+")");
    if (tr.length) {
      tr.remove();
      this.count--;
    }
	},
	clear: function () {
		this.$DOMhdl.find("tr").remove();
		this.count = 0;
    this.invalidCount = 0;
	},
	getDataIDsAsString: function () {
		var me = this;
		var s = "";
		this.$DOMhdl.find("tr").each(function(i){
			s += (i>0?"|":"");
			for (var cdi=0, cdc=me.colsDataAsString.length; cdi<cdc; cdi++)
				s += (cdi>0?",":"")+$(this).find("td:eq("+me.colsDataAsString[cdi]+")").html();
		});
		return s;
	},
	setData: function (data) {
		this.clear();
		for (var i=0; i < data.length; i++) {
			if (data[i]) {
				var row = [];
				for (var ci=0; ci<this.cols.length; ci++)
					row.push(data[i][this.cols[ci]]);
				this.add.apply(this, row);
			}
			else {
				this.add.apply(this, ["-","-","cet identifiant produit n'existe plus"]);
        this.invalidCount++;
			}
		}
	}	
}

$(document).ready(function(){
  // dialog declaration
  $("#prod_fast_populating_dialog").dialog({
    width: 550,
    autoOpen: false,
    modal: true
  });

  $('.close_prod_fast_populating_dialog').live(
    'click', function(){
    $("#prod_fast_populating_dialog").dialog('close');
  });

  $('#btn_input_products').click(function(){
    var html = 'Saisir une liste d\'id produits séparés par virgule ou sauts de ligne.<br />\n\
    <textarea id="add-id-prod-list" cols="60" rows="5"></textarea><br />\n\
    <input id="btn-add-prod-list" class="bouton" type="button" value="Ajouter la liste"/>'
    $('#prod_fast_populating_dialog').html(html);
    $('#prod_fast_populating_dialog').dialog('open');

  });

  $('#btn-add-prod-list').live('click',function(){
    var prod_list = $('#add-id-prod-list').val();
    prod_list2 = prod_list.replace(/\n/g, ',');
    $.ajax({
            async: true,
            cache: false,
            data: {action: "getwithoutfamily products", data: prod_list2},
            dataType: "json",
            error: function (XMLHttpRequest, textStatus, errorThrown) {alert("Fatal error while loading the Products List");},
            success: function (data, textStatus) {
                    if (data.error && data.error != "")
                            alert(data.error);
                    else {
                      ILTpdt.setData(data.data); // refreshing the list table
                      var validDataIdString = ILTpdt.getDataIDsAsString(); // new valid string of products id's
                      $pdtListString.val(validDataIdString);
                      HN.TC.BO.MS.MISM.SetSelectedItems(validDataIdString);
                      HN.TC.BO.MS.MPSDB.Hide();
                      $("#prod_fast_populating_dialog").dialog('close');
                      if (data.idList_error && data.idList_error != ""){
                        var listError = '';
                        $.each(data.idList_error, function(index){
                          listError += index == 0 ? this : ', '+this;
                        })
                        alert('Les id produits suivant n\'ont pu être intégrés : \n\
'+listError);
                      }
                    }
            },
            timeout: 10000,
            type: "POST",
            url: "AJAX_JSON_server.php"
    });

  });

});
