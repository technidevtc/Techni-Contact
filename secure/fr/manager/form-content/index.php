<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$dom = new DomDocument("1.0", "utf-8");
$dom->load(XML_FORM_CONTENT);

$xPath = new DOMXPath($dom);
$form = $xPath->query("//forms/form[attribute::name=\"main_contact\"]");
if ($form->length > 0) {
	$form = $form->item(0);
	$subjects = $xPath->query("child::subjects", $form);
	if ($subjects->length > 0) {
		$subjects = $subjects->item(0);
		$options = $xPath->query("child::option", $subjects);
	}
	else {
		$subjects = $dom->createElement("subjects");
		$form->appendChild($subjects);
	}
}

$title = $navBar = "Configuration des formulaires";
require(ADMIN."head.php");

?>
<script type="text/javascript">
$(function(){
	if (!window.HN) HN = window.HN = {};
	if (!HN.TC) HN.TC = {};
	if (!HN.TC.BO) HN.TC.BO = {};
	if (!HN.TC.BO.FC) HN.TC.BO.FC = {};
	HN.TC.BO.FC.Init = function () {
		var optionTable = new HN.TC.BO.FC.optionTable($("#main_contact_options tbody").get(0));
	<?php foreach($options as $option) { ?>
		optionTable.add("<?php echo str_replace('"','\"',utf8_decode($option->getAttribute("text"))) ?>", "<?php echo str_replace('"','\"',utf8_decode($option->getAttribute("emails"))) ?>");
	<?php } ?>
		$("#btn-add-option")
			.mousedown(function(){ this.className = "down"; })
			.mouseup(function(){ if (this.className == "down") { this.className = ""; optionTable.add(); } })
			.mouseleave(function(){ this.className = ""; });
		$("#btn-save").click(function(){
			$.ajax({
				async: true,
				cache: false,
				data: "action=set"+optionTable.getSerializedData(),
				dataType: "json",
				error: function (XMLHttpRequest, textStatus, errorThrown) { alert("Fatal error while setting the Array of Selected Items by Categories"); },
				success: function (data, textStatus) {
					if (data.error)
						alert(data.error);
					else {
						optionTable.clear();
						for (var value in data.data) {
							optionTable.add(data.data[value].text, data.data[value].emails);
						}
						alert("Modification effectuée avec succés !");
					}
				},
				timeout: 10000,
				type: "GET",
				url: "AJAX_form-content.php"
			});
		});
	};
	
	HN.TC.BO.FC.optionTable = function (_domHdl) {
		this.count = 0;
		this.domHdl = typeof _domHdl == "string" ? document.getElementById(_domHdl) : _domHdl;
	};
	HN.TC.BO.FC.optionTable.prototype = {
		offsetIndex: 1,
		add: function () {
			var me = this;
			var text = "", emails = "";
			if (arguments.length > 0)
				text = arguments[0];
			if (arguments.length > 1)
				emails = arguments[1];
			
			var tr = document.createElement("tr");
			this.domHdl.appendChild(tr);
				var td_value = document.createElement("td");
				td_value.className = "value";
				td_value.appendChild(document.createTextNode(this.count+this.offsetIndex));
				tr.appendChild(td_value);
				var td_text = document.createElement("td");
				td_text.className = "text";
				tr.appendChild(td_text);
					var input_text = document.createElement("input");
					input_text.type = "text";
					input_text.value = text;
					input_text.onfocus = function(){ tr.className = "selected"; };
					input_text.onblur = function(){ tr.className = ""; };
					td_text.appendChild(input_text);
				var td_emails = document.createElement("td");
				td_emails.className = "emails";
				tr.appendChild(td_emails);
					var input_emails = document.createElement("input");
					input_emails.type = "text";
					input_emails.value = emails;
					input_emails.onfocus = function(){ tr.className = "selected"; };
					input_emails.onblur = function(){ tr.className = ""; };
					td_emails.appendChild(input_emails);
				var td_actions = document.createElement("td");
				td_actions.className = "actions";
				tr.appendChild(td_actions);
					var icon_up = document.createElement("div");
					icon_up.className = "ib icon icon-up";
					icon_up.title = "monter";
					icon_up.onclick = function(){
						var tr_index = parseInt($("tr", me.domHdl).index(tr));
						if (tr_index > 0) {
							me.domHdl.insertBefore(tr, $("tr", me.domHdl).get(tr_index-1));
							me.reIndex(tr_index-1);
						}
					};
					td_actions.appendChild(icon_up);
					var icon_down = document.createElement("div");
					icon_down.className = "ib icon icon-down";
					icon_down.title = "descendre";
					icon_down.onclick = function(){
						var $trs = $("tr", me.domHdl);
						var tr_index = parseInt($trs.index(tr));
						if (tr_index < $trs.length-1) {
							me.domHdl.insertBefore($("tr", me.domHdl).get(tr_index+1), tr);
							me.reIndex(tr_index);
						}
					};
					td_actions.appendChild(icon_down);
					var icon_edit = document.createElement("div");
					icon_edit.className = "ib icon icon-edit";
					icon_edit.title = "éditer";
					icon_edit.onclick = function(){ input_text.focus(); };
					td_actions.appendChild(icon_edit);
					var icon_del = document.createElement("div");
					icon_del.className = "ib icon icon-del";
					icon_del.title = "supprimer";
					icon_del.onclick = function(){
						me.del(parseInt($("tr", me.domHdl).index(tr)));
					};
					td_actions.appendChild(icon_del);
			
			this.count++;
		},
		del: function (tr_index) {
			$("tr:eq("+tr_index+")", this.domHdl).remove();
			this.reIndex(tr_index);
		},
		clear: function () {
			$("tr", this.domHdl).remove();
			this.count = 0;
		},
		reIndex: function () {
			var fromIndex = 0;
			if (arguments.length > 0)
				fromIndex = parseInt(fromIndex);
			var trs =  $("tr", this.domHdl).get().slice(fromIndex);
			for (var i=0; i < trs.length; i++)
				$("td:first-child",trs[i]).html(i+fromIndex+this.offsetIndex);
			
		},
		getSerializedData: function () {
			var s = ""; i = 0;
			$("tr", this.domHdl).each(function(){
				var value = $("td:first-child",this).html();
				var text = escape($("td:eq(1) input",this).val());
				var emails = escape($("td:eq(2) input",this).val());
				s += "&value"+i+"="+value+"&text"+i+"="+text+"&emails"+i+"="+emails;
				i++;
			});
			return s;
		}
	};
	HN.TC.BO.FC.Init();
});
</script>
<style type="text/css">
.ib { float: none; display: -moz-inline-stack; display: inline-block }
.wrapper { font: normal 12px arial, helvetica, sans-serif; margin: 5px; padding: 5px; border: 1px solid #cccccc; background: #fdfdfd }
.form-content { width: 700px }
.form-content img { border: 0 }
.option-list { width: 100%; font: normal 10px verdana, helvetica, sans-serif; border: 1px solid #000000; border-collapse: collapse; background-color: #ffffff }
.option-list tr.selected { background: #ffd9a7 }
.option-list th { height: 17px; padding: 1px; background: #f0f0f0 }
.option-list td { height: 17px; padding: 1px; border: 1px solid #000000; border-width: 1px 0 }
.option-list .value { width: 80px; text-align: center }
.option-list .text { width: 220px }
.option-list .text input { width: 216px; margin: 0; padding: 2px; font: normal 10px verdana, helvetica, sans-serif; border: 0 }
.option-list .emails { width: 270px }
.option-list .emails input { width: 266px; margin: 0; padding: 2px; font: normal 10px verdana, helvetica, sans-serif; border: 0 }
.option-list .actions { width: 100px; text-align: center }
.option-list .actions .icon { width: 16px; height: 16px; background-repeat: no-repeat; cursor: pointer }
.option-list .actions .icon-up { background: url(arrow_up.png) }
.option-list .actions .icon-down { background: url(arrow_down.png) }
.option-list .actions .icon-edit { margin: 0 5px 0 10px; background: url(table_edit.png) }
.option-list .actions .icon-del { background: url(table_delete.png) }

.grey-block { clear:both; border: 1px solid #e4e4e4; background: #fcfcfc url(block-bg-grey-150.gif) repeat-x }
a.btn-red { display: block; padding: 5px; font: bold 12px arial, sans-serif; color: #ffffff; text-align: center; background: #9d0503 url(dot-bg.png) repeat-x }
a.btn-red:hover { text-decoration: underline }
#btn-save { float: right; width: 200px; margin: 5px 0 0 }
#btn-add-option { float: right; width: 150px; height: 20px; margin: 5px 0; background: url(btn-add-option-up.png) no-repeat; cursor: pointer }
#btn-add-option.down { background: url(btn-add-option-down.png) no-repeat }
</style>
<div class="titreStandard">Options du formulaire de contact client</div>
<br/>
<div class="wrapper">
	<div class="form-content">
		<div class="title"></div>
		<div id="btn-add-option"></div>
		<div class="zero"></div>
		<table id="main_contact_options" class="option-list">
			<thead>
			<tr>
				<th class="value">Classement</th>
				<th class="text">Texte</th>
				<th class="text">Emails</th>
				<th class="actions">actions</th>
			</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
		<a href="#" class="btn-red" id="btn-save">Sauvegarder les changements</a>
		<div class="zero"></div>
	</div>
</div>
<?php
require(ADMIN."tail.php");
?>
