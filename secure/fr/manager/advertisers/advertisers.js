function jsonToString(obj) {
	var t = typeof(obj);
	if (t != "object" || obj === null) {
		// simple data type
		if (t == "string") obj = '"' + obj.replace(/"/g,'\\"') + '"';
		return String(obj);
	} else {
		// recurse array or object
		var n,
		v,
		json = [],
		arr = (obj && obj.constructor == Array);

		for (n in obj) {
			v = obj[n];
			t = typeof(v);
			if (t == "string") v = '"' + v.replace(/"/g,'\\"') + '"';
			else if (t == "object" && v !== null) v = jsonToString(v);
			json.push((arr ? "": '"' + n + '":') + String(v));
		}
		return (arr ? "[": "{") + String(json) + (arr ? "]": "}");
	}
};

if (!window.HN) HN = window.HN = {};
if (!HN.TC) HN.TC = {};
if (!HN.TC.BO) HN.TC.BO = {};
if (!HN.TC.BO.Adv) HN.TC.BO.Adv = {};
if (!HN.TC.BO.RazCredit) HN.TC.BO.RazCredit = {};

var cfm;
HN.TC.BO.Adv.Init = function (_advID) {
	// modify products margin
  (function(){
    var $tips = $("#mod-pdt-margin-dialog .tips");
    $("#mod-pdt-margin-dialog").dialog({
      width: 550,
      autoOpen: false,
      modal: true,
      buttons: {
        "Appliquer": function(){
          var self = this;
          $.ajax({
            type: "GET",
            url: "AJAX_interface.php",
            data: "advID="+_advID+"&action=mod-pdt-margin&adv_margin="+$("#adv_margin").val()+"&adv_price_type="+$("#adv_price_type").val(),
            dataType: "json",
            error: function (XMLHttpRequest, textStatus, errorThrown) {
              $tips.text(textStatus);
            },
            success: function (data, textStatus) {
              if (data.error) {
                $tips.text(data.error);
              }
              else {
                $tips.text(data.data);
                setTimeout(function(){$(self).dialog("close");},2500);
              }
            }
          });
        },
        "Annuler": function(){$(this).dialog("close");}
      },
      open: function(){
        $tips.text("Cette action modifiera le taux de marge ou remise de tous les produits de ce fournisseur");
      }
    });
    $("#mod-pdt-margin-btn").click(function(){
      $("#mod-pdt-margin-dialog").dialog("open");
    });
  })();
  
  // send bop codes
  (function(){
    var $tips = $("#send-bop-codes-dialog .tips");
    $("#send-bop-codes-dialog").dialog({
      width: 550,
      autoOpen: false,
      modal: true,
      buttons: {
        "Confirmer l'envoi": function(){
          var self = this;
          $.ajax({
            type: "GET",
            url: "AJAX_interface.php",
            data: "advID="+_advID+"&action=send-bop-codes",
            dataType: "json",
            error: function (XMLHttpRequest, textStatus, errorThrown) {
              $tips.text(textStatus);
            },
            success: function (data, textStatus) {
              if (data.error) {
                $tips.text(data.error);
              }
              else {
                $tips.text(data.data);
                setTimeout(function(){$(self).dialog("close");},2500);
              }
            }
          });
        },
        "Annuler": function(){$(this).dialog("close");}
      },
      open: function(){
        $tips.text("Cette action enverra un mail au partenaire contenant ses identifiants de connexion extranet");
      }
    });
    $("#send-bop-codes-btn").click(function(){
      $("#send-bop-codes-dialog").dialog("open");
    });
  })();
  
  // custom fields
  if ($("#custom-field").length > 0) {
		cfm = new HN.TC.BO.Adv.cfm($("#custom-field table.custom-field-table tbody").get(0), _advID);
		$("#btn-add-custom-field")
			.mousedown(function(){this.className = "down";})
			.mouseup(function(){if (this.className == "down") {this.className = "";cfm.add();}})
			.mouseleave(function(){this.className = "";});
		$("#btn-save").click(function(){
			cfm.save();
			return false;
		});
		cfm.load();
	}
	
	// invoicing customization
	var $ic = $("div.invoicing-customization");
	$ic.find("input[name='ic_active']").click(function(){
		if ($(this).prop("checked"))
			$ic.find("div.customization-list").show();
		else
			$ic.find("div.customization-list").hide();
	});
	if ($ic.find("input[name='ic_active']").prop("checked"))
		$ic.find("div.customization-list").show();
	
	$ic.find("div.customization-list fieldset input").prop("checked",true);
	for (var ic_field in ic_fields)
		for (var i=0; i<ic_fields[ic_field].length; i++)
			if (typeof(ic_fields[ic_field]) == "object")
				$ic.find("input[name='"+ic_field+"'][value='"+ic_fields[ic_field][i].replace(/\'/gi,"\\\'")+"']").prop("checked",false);
			else if (typeof(ic_fields[ic_field]) == "string")
				$ic.find("textarea[name='"+ic_field+"']").val(unescape(ic_fields[ic_field]));
	
	
	// invoicing settings
	var $is = $("div.invoicing-settings");
	$is.find("select[name='is_type']").change(function(){
		switch($(this).val()) {
			case "lead":
				$is.find("table.is-lead").show();
				$is.find("table.is-budget").hide();
				$is.find("table.is-forfeit").hide();
				break;
			case "budget":
				$is.find("table.is-lead").hide();
				$is.find("table.is-budget").show();
				$is.find("table.is-forfeit").hide();
				break;
			case "forfeit":
				$is.find("table.is-lead").hide();
				$is.find("table.is-budget").hide();
				$is.find("table.is-forfeit").show();
				break;
			default:break;
		}
	}).change();
	
	HN.TC.BO.Adv.is_AJAXHandle = {
		type : "GET",
		url: "AJAX_invoicing-settings.php",
		dataType: "json",
		error: function (XMLHttpRequest, textStatus, errorThrown) {
			$("#PerfReqLabelInvoicingSettings").text(textStatus);
		},
		success: function (data, textStatus) {
			if (data.error) $("#PerfReqLabelInvoicingSettings").text(data.error);
			else {
				var $tbody = $is.find("table.is-historic tbody");
				$tbody.empty();
				for (var i=0; i<data.length;i++) {
                                     if(data[i].type != 'reset_credit'){
					var dateo = new Date();
					dateo.setTime(data[i].date*1000);
					date = dateo.getDate() + "/" + (dateo.getMonth()+1) + "/" + dateo.getFullYear() + " " + dateo.getHours() + ":" + dateo.getMinutes();
					
					var type = data[i].type;
					switch (type) {
						case "lead":type = "au lead";break;
						case "budget":type = "au budget";break;
						case "forfeit":type = "au forfait";break;
						default:break;
					}
					if (i == 0)
						$is.find("select[name='is_type']").val(type).change();
					
					var detail = [];
					for (var field in data[i].fields) {
						var text = data[i].fields[field];
						if (i == 0)
							$is.find("input[name='"+field+"'], textarea[name='"+field+"'], select[name='"+field+"']").val(data[i].fields[field]);
						switch(field) {
							case "lead_unit_cost":
							case "budget_unit_cost":text += "€ par lead";break;
							case "budget_max_leads":text +=" leads max";break;
							case "budget_capping_periodicity":
							case "forfeit_periodicity":
								if (text == "year") text = "périodicité par année";
								else if (text == "month") text = "périodicité par mois";
								break;
							case "forfeit_amount":text += "€ max";break;
							default:break;
						}
						detail.push(text);
					}
					detail = detail.join(" / ");
					
					$tbody.append(
						"<tr id=\"is-mod-"+i+"\">" +
						"	<td class=\"date\">"+date+"</td>" +
						"	<td class=\"type\">"+type+"</td>" +
						"	<td class=\"detail\">"+detail+"</td>" +
						"	<td class=\"undo\">" +
						(i == 0 ? "		<img src=\"arrow_undo_red.png\" alt=\"annuler\" title=\"annuler\" onclick=\"HN.TC.BO.Adv.UndoInvoicingSetting("+i+")\"/>" : "") +
						"	</td>" +
						"</tr>");
                                    }
				}
				$("#PerfReqLabelInvoicingSettings").text("");
			}
		}
	};
	HN.TC.BO.Adv.GetInvoicingSetting = function(){
		HN.TC.BO.Adv.is_AJAXHandle.data = "action=get&advID="+_advID;
		$.ajax(HN.TC.BO.Adv.is_AJAXHandle);
	};
	HN.TC.BO.Adv.AddInvoicingSetting = function(){
		var is_type = $is.find("select[name='is_type']").val();
		var is_field = {};
		$is.find("table.is:visible").find("input, select, textarea").each(function(){
			is_field[$(this).attr("name")] = $(this).val();
		});
		var as = "action=add";
		as += "&advID="+_advID;
		as += "&is_type="+is_type;
		as += "&is_field="+escape(jsonToString(is_field));
		
		HN.TC.BO.Adv.is_AJAXHandle.data = as;
		$.ajax(HN.TC.BO.Adv.is_AJAXHandle);
	};
	HN.TC.BO.Adv.UndoInvoicingSetting = function(id){
		id = parseInt(id);
		if (document.getElementById("is-mod-"+id)) {
			if (confirm("Voulez-vous vraiment annuler cette modification ?")) {
				HN.TC.BO.Adv.is_AJAXHandle.data = "action=undo&advID="+_advID+"&id="+id;
				$.ajax(HN.TC.BO.Adv.is_AJAXHandle);
			}
		}
	};
	HN.TC.BO.Adv.GetInvoicingSetting();
	
	// form validation
	$("form[name='editAdvertiser'] input[type='button'][name='ok'], form[name='addAdvertiser'] input[type='button'][name='ok']").click(function(){
		ic_fields = {};
		$ic.find("div.customization-list fieldset input").each(function(){
			if (!$(this).prop("checked")) {
				if (!ic_fields[$(this).attr("name")])
					ic_fields[$(this).attr("name")] = [];
				ic_fields[$(this).attr("name")].push($(this).attr("value"));
			}
		});
		$ic.find("div.customization-list textarea").each(function(){
			var vals = $(this).val();
			if (vals != "") {
				vals = vals.split("|");
				for (var i=0; i<vals.length; i++)
					vals[i] = vals[i].toUpperCase();
				ic_fields[$(this).attr("name")] = escape(vals.join("|"));
			}
		});
		$ic.find("input[name='ic_fields']").val(jsonToString(ic_fields));
		
		this.form.submit();
		this.disabled = true
	});
};

HN.TC.BO.Adv.cfm = function (_DOMhdl, _advID) {
	this.count = 0;
	this.DOMhdl = typeof _DOMhdl == "string" ? document.getElementById(_DOMhdl) : _DOMhdl;
	this.$DOMhdl = $(this.DOMhdl);
	this.advID = _advID;
	var table = document.createElement("table");
	table.innerHTML = "\<tbody><tr>\
			<td><input name=\"cf_name\" type=\"text\" class=\"name\"/></td>\
			<td><input name=\"cf_label\" type=\"text\" class=\"label\"/></td>\
			<td>\
				<select name=\"cf_type\" class=\"type\">\
					<option value=\"text\">Texte</option>\
					<option value=\"select\">Liste de sélection</option>\
					<option value=\"textarea\">Champ de texte</option>\
				</select>\
			</td>\
			<td><input name=\"cf_required\" type=\"checkbox\" class=\"required\"/></td>\
			<td><input name=\"cf_valueList\" type=\"text\" class=\"value-list\"/></td>\
			<td><input name=\"cf_valueDefault\" type=\"text\" class=\"value-default\"/></td>\
			<td>\
				<select name=\"cf_validationType\" class=\"validation-type\">\
					<option value=\"none\">aucun</option>\
					<option value=\"integer\">entier</option>\
					<option value=\"date\">Date</option>\
					<option value=\"email\">Email</option>\
					<option value=\"url\">Url</option>\
				</select>\
			</td>\
			<td><input name=\"cf_length\" type=\"text\" class=\"length\"/></td>\
			<td class=\"actions\">\
				<div class=\"ib icon icon-up\" title=\"monter\"></div>\
				<div class=\"ib icon icon-down\" title=\"descendre\"></div>\
				<div class=\"ib icon icon-del\" title=\"supprimer\"></div>\
			</td>\
		</tr></tbody>";
	this.baseRow = $(table).find("tr").get(0);
	//alert($(this.baseRow).find("td").html());
};
HN.TC.BO.Adv.cfm.prototype = {
	baseRow: null,
	getBaseRow: function () {
		return $(this.baseRow).clone(true).get(0);
	},
	offsetIndex: 1,
	add: function () {
		var me = this;
		var name = "", label = "", type = "", required = false, valueList = "", valueDefault = "", validationType = "", length = "64";
		if (arguments.length > 0) name = arguments[0];
		if (arguments.length > 1) label = arguments[1];
		if (arguments.length > 2) type = arguments[2];
		if (arguments.length > 3) required = parseInt(arguments[3]);
		if (arguments.length > 4) valueList = arguments[4];
		if (arguments.length > 5) valueDefault = arguments[5];
		if (arguments.length > 6) validationType = arguments[6];
		if (arguments.length > 7) length = arguments[7];
		
		var tr = this.getBaseRow();
		var $tr = $(tr);
		$tr.find("input[name='cf_name']").val(name);
		$tr.find("input[name='cf_label']").val(label);
		$tr.find("select[name='cf_type']").val(type);
		$tr.find("input[name='cf_required']").prop("checked", required);
		$tr.find("input[name='cf_valueList']").val(valueList);
		$tr.find("input[name='cf_valueDefault']").val(valueDefault);
		$tr.find("select[name='cf_validationType']").val(validationType);
		$tr.find("input[name='cf_length']").val(length);
		$tr.find("div.icon-up").click(function(){
			var tr_index = parseInt($("tr", me.DOMhdl).index(tr));
			if (tr_index > 0) {
				me.DOMhdl.insertBefore(tr, me.$DOMhdl.find("tr").get(tr_index-1));
				//me.reIndex(tr_index-1);
			}
		});
		$tr.find("div.icon-down").click(function(){
			var $trs = $("tr", me.DOMhdl);
			var tr_index = parseInt($trs.index(tr));
			if (tr_index < $trs.length-1) {
				me.DOMhdl.insertBefore(me.$DOMhdl.find("tr").get(tr_index+1), tr);
				//me.reIndex(tr_index);
			}
		});
		$tr.find("div.icon-del").click(function(){
			me.del(parseInt(me.$DOMhdl.find("tr").index(tr)));
		});
		
		this.DOMhdl.appendChild(tr);
	},
	del: function (tr_index) {
		this.$DOMhdl.find("tr:eq("+tr_index+")").remove();
		//this.reIndex(tr_index);
	},
	clear: function () {
		this.$DOMhdl.find("tr").remove();
		this.count = 0;
	},
	/*reIndex: function () {
		var fromIndex = 0;
		if (arguments.length > 0)
			fromIndex = parseInt(fromIndex);
		var trs =  $("tr", this.DOMhdl).get().slice(fromIndex);
		for (var i=0; i < trs.length; i++)
			$("td:first-child",trs[i]).html(i+fromIndex+this.offsetIndex);
		
	},*/
	getSerializedData: function () {
		var s = "";i = 0;
		this.$DOMhdl.find("tr").each(function(){
			var $tr = $(this);
			s += "&name"+i+"="+escape($tr.find("input[name='cf_name']").val())
			+ "&label"+i+"="+escape($tr.find("input[name='cf_label']").val())
			+ "&type"+i+"="+escape($tr.find("select[name='cf_type']").val())
			+ "&required"+i+"="+escape($tr.find("input[name='cf_required']").prop("checked")?1:0)
			+ "&valueList"+i+"="+escape($tr.find("input[name='cf_valueList']").val())
			+ "&valueDefault"+i+"="+escape($tr.find("input[name='cf_valueDefault']").val())
			+ "&validationType"+i+"="+escape($tr.find("select[name='cf_validationType']").val())
			+ "&length"+i+"="+escape($tr.find("input[name='cf_length']").val());
			i++;
		});
		return s;
	},
	load: function(){
		$.ajax({
			async: true,
			cache: false,
			data: "action=get&advID="+this.advID,
			dataType: "json",
			error: function (XMLHttpRequest, textStatus, errorThrown) {alert("Fatal error while loading the Custom Fields List");},
			success: function (data, textStatus) {
				if (data.error != "")
					alert(data.error);
				else {
					cfm.clear();
					for (var i=0; i < data.data.length; i++) {
						cfm.add(
							data.data[i].name,
							data.data[i].label,
							data.data[i].type,
							data.data[i].required,
							data.data[i].valueList,
							data.data[i].valueDefault,
							data.data[i].validationType,
							data.data[i].length);
					}
				}
			},
			timeout: 10000,
			type: "GET",
			url: "AJAX_custom-fields.php"
		});
	},
	save: function(){
		$.ajax({
			async: true,
			cache: false,
			data: "action=set&advID="+this.advID+cfm.getSerializedData(),
			dataType: "json",
			error: function (XMLHttpRequest, textStatus, errorThrown) {alert("Fatal error while saving the Custom Fields List");},
			success: function (data, textStatus) {
				if (data.error != "")
					alert(data.error);
				else {
					cfm.clear();
					for (var i=0; i < data.data.length; i++) {
						cfm.add(
							data.data[i].name,
							data.data[i].label,
							data.data[i].type,
							data.data[i].required,
							data.data[i].valueList,
							data.data[i].valueDefault,
							data.data[i].validationType,
							data.data[i].length);
					}
					alert("Modification effectuée avec succés !");
				}
			},
			timeout: 10000,
			type: "GET",
			url: "AJAX_custom-fields.php"
		});
	}
};


// Raz Credit
HN.TC.BO.RazCredit.Init = function (_advID) {

	var $is = $("div.credit-settings");

	HN.TC.BO.RazCredit.is_AJAXHandle = {
		type : "GET",
		url: "AJAX_reset-credit.php",
		dataType: "json",
		error: function (XMLHttpRequest, textStatus, errorThrown) {
			$("#PerfReqLabelResetCredit").text(textStatus);
		},
		success: function (data, textStatus) {
			if (data.error) $("#PerfReqLabelResetCredit").text(data.error);
			else {
				var $tbody = $is.find("table.RazCredit-historic tbody");
				$tbody.empty();
        var j = (data.length-1);
				for (var i=0; i<data.length;i++) {
          if (data[i].type == 'reset_credit') {
            var dateo = new Date();
            dateo.setTime(data[i].date*1000);
            date = dateo.getDate() + "/" + (dateo.getMonth()+1) + "/" + dateo.getFullYear() + " " + dateo.getHours() + ":" + dateo.getMinutes();

            var detail = [];
            for (var field in data[i].fields) {
              var dataType = typeof data[i].fields[field];
              if (dataType == 'string') {
              var text = data[i].fields[field]+=" €";
              detail.push(text);
              }
            }


            $tbody.append(
              "<tr id=\"is-mod-"+i+"\">" +
              "	<td class=\"date\">"+date+"</td>" +
              "	<td class=\"detail\">"+detail+"</td>" +
              "	<td class=\"undo\">" +
              (i == j ? "		<img src=\"arrow_undo_red.png\" alt=\"annuler\" title=\"annuler\" onclick=\"HN.TC.BO.RazCredit.UndoInvoicingSetting("+i+")\"/>" : "") +
              "	</td>" +
              "</tr>");
          }
				}
        $("input.RazCredit-amount").attr('value' , data['totalCreditAmount']);
				$("#PerfReqLabelResetCredit").text("");
			}
		}
	};
	HN.TC.BO.RazCredit.GetCreditAmount = function(){
		HN.TC.BO.RazCredit.is_AJAXHandle.data = "action=getAmount&advID="+_advID;
		$.ajax(HN.TC.BO.RazCredit.is_AJAXHandle);
	};
        HN.TC.BO.RazCredit.GetCreditHistory = function(){
		HN.TC.BO.RazCredit.is_AJAXHandle.data = "action=getHistory&advID="+_advID;
		$.ajax(HN.TC.BO.RazCredit.is_AJAXHandle);
	};
	HN.TC.BO.RazCredit.Reset = function(){
		var is_type = 'reset_credit';
		var is_field = {};
		var input_credit_amount = $is.find("input.RazCredit-amount");
                
                if(input_credit_amount.attr("name") == 'credit_amount'){
                    is_field['credit_amount'] = input_credit_amount.val();
                }

		var as = "action=add";
		as += "&advID="+_advID;
		as += "&is_type="+is_type;
		as += "&is_field="+escape(jsonToString(is_field));

		HN.TC.BO.RazCredit.is_AJAXHandle.data = as;
		$.ajax(HN.TC.BO.RazCredit.is_AJAXHandle);

                HN.TC.BO.RazCredit.GetCreditAmount();
                HN.TC.BO.RazCredit.GetCreditHistory();
	};
	HN.TC.BO.RazCredit.UndoInvoicingSetting = function(id){
		id = parseInt(id);
		if (document.getElementById("is-mod-"+id)) {
			if (confirm("Voulez-vous vraiment annuler cette modification ?")) {
				HN.TC.BO.RazCredit.is_AJAXHandle.data = "action=undo&advID="+_advID+"&id="+id;
				$.ajax(HN.TC.BO.RazCredit.is_AJAXHandle);
			}
		}
                HN.TC.BO.RazCredit.GetCreditAmount();
                HN.TC.BO.RazCredit.GetCreditHistory();
	};
	HN.TC.BO.RazCredit.GetCreditAmount();
        HN.TC.BO.RazCredit.GetCreditHistory();

};

