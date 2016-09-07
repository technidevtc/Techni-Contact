<?php
if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}


$db = DBHandle::get_instance();

$title = $navBar = "Liste des Devis fournisseurs";
require(ADMIN."head.php");

if (!$userPerms->has($fntByName["m-comm--sm-supplier-leads"], "re")) {
?>

<div class="bg">
  <div class="fatalerror">Vous n'avez pas les droits adéquats pour réaliser cette opération.</div>
</div>
<?php
}
else {
  $f = BOFunctionality::get("id","name='m-prod--sm-partners-appear-as-profil'");
  if (!empty($f)) {
    $ups = BOUserPermission::get("id_user","id_functionality=".$f[0]["id"]);
    foreach($ups as $up)
      $comIdList[] = $up["id_user"];
    if (!empty($comIdList)) {
      $comList = BOUser::get("id, name, login, email, phone","id in (".implode(",",$comIdList).")");
    }
  }
?>
<link rel="stylesheet" type="text/css" href="leads.css" />
<script src="../js/ManagerFunctions.js" type="text/javascript"></script>
<script type="text/javascript" src="leads.js"></script>
<link rel="stylesheet"  type="text/css" href="style.css" />
<div class="titreStandard">Liste des devis fournisseurs</div>
<br/>
<div class="section">
  <div style="color: #FF0000" id="show_error_message"></div>
  <div id="filtering-options" class="block filtering">
    <div class="title">Options de filtrage et actions</div>
    <div class="text">
      <form id="form-print" action="print.php" method="post" target="_blank">
        <input type="hidden" name="leadIds"/>
        <button id="btn-print-sleads-list" class="btn ui-state-default ui-corner-all">imprimer</button>
      </form>
      <fieldset>
        <legend>Contacts passés depuis :</legend>
        <select name="time">
          <option value=""> - </option>
          <option value="900">- 15 minutes</option>
          <option value="1800">- 30 minutes</option>
          <option value="3600">- 1 heure</option>
          <option value="7200">- 2 heures</option>
          <option value="10800">- 3 heures</option>
          <option value="86400">- 1 jour</option>
          <option value="172800">- 2 jours</option>
          <option value="259200">- 3 jours</option>
        </select>
      </fieldset>
      <fieldset>
        <legend>Commerciaux :</legend>
        <ul>
         <?php foreach($comList as $com) { ?>
          <li><input type="checkbox" name="commercial" value="<?php echo $com["id"] ?>"/><label for="com"><?php echo $com["name"] ?></label></li>
         <?php } ?>
        </ul>
      </fieldset>
      <fieldset>
        <legend>Société ou Email :</legend>
        <input type="text" name="search" size="50"/>
      </fieldset>
      <fieldset>
        <legend>Origine :</legend>
        <ul>
          <li><input type="checkbox" name="france" checked="checked"/><label for="france">France</label></li>
          <li><input type="checkbox" name="foreign" checked="checked"/><label for="foreign">Etranger</label></li>
        </ul>
      </fieldset>
      <fieldset>
        <legend>Historique :</legend>
        <input type="checkbox" name="historic"/><label for="historic">Inclure les leads historiques</label>
      </fieldset>
	  <fieldset>
        <legend>Etat traitement :</legend>
		
        <input type="radio" name="etat_traitement" id="etat_traitement" value="0" checked="TRUE" />
		<label for="etat_traitement">Indifférent</label>
		<br />
		
		
		<input type="radio" name="etat_traitement" id="etat_traitement_not_started" value="-" />
		<label for="etat_traitement_not_started">Non d&eacute;marr&eacute;</label>
		<br />
		
		
		<input type="radio" name="etat_traitement" id="etat_traitement_processing" value="1" />
		<label for="etat_traitement_processing">Devis en cours</label>
		
      </fieldset>
      <div class="zero"></div>
    </div>
  </div>

  <br />
  <div id="msg-tooltip" class="tooltip"></div>
  <table class="item-list">
    <thead>
      <tr>
      <th class="see">Voir</th>
      <th>Photo pdt</th>
      <th class="date">Date/Heure</th>
      <th>Login com.</th>
      <th>Login com. envoyé</th>
      <th>Nom pdt</th>
      <th>Fournisseur</th>
      <th>Lien</th>
      <th>Note</th>
      <th>Société</th>
	  <th>Statut devis</th>
	  <th>En charge</th>
      <th>Email</th>
      <th>Pays</th>
      <th class="actions"></th>
    </tr>
    </thead>
    <tbody id="content-list">
      <tr class="tr-new"><td class="date" colspan="14"><img src="../../ressources/images/lightbox-ico-loading.gif" alt="chargement..."></td></tr>
    </tbody>
  </table>
	
	
	
  <div id="actions-dialog" title="Devis intraitable">
    Vous considérez ce devis comme intraitable<br/>
    Un mail sera donc envoyé à la société <span id="att-company"></span> (<span id="att-email"></span>) pour l'en avertir.<br/>
    <br/>
    Merci de préciser le motif de votre décision :<br/>
    <select>
      <option value="Nous avons besoin de plus d'informations concernant votre besoin">Nous avons besoin de plus d'informations concernant votre besoin</option>
      <option value="Nous n'assurons la livraison qu'en France">Nous n'assurons la livraison qu'en France</option>
      <option value="Nous ne faisons pas / plus le produit demandé">Nous ne faisons pas / plus le produit demandé</option>
      <option value="Nous ne pouvons répondre à votre demande dans le budget alloué">Nous ne pouvons répondre à votre demande dans le budget alloué</option>
      <option value="Autre">Autre</option>
    </select><br/>
    Commentaires : <textarea rows="3" cols="37"></textarea>
  </div>
</div>

<script type='text/javascript'>//<![CDATA[ 
$(window).load(function(){
jQuery(document).ready(function ($) {
    $('[data-popup-target]').click(function () {
        $('html').addClass('overlay');
        var activePopup = $(this).attr('data-popup-target');
        $(activePopup).addClass('visible');
    });
    $(document).keyup(function (e) {
        if (e.keyCode == 27 && $('html').hasClass('overlay')) {
            clearPopup();
        }
    });
    $('.popup-exit').click(function () {
        clearPopup();
    });
    $('.popup-overlay').click(function () {
        clearPopup();
    });
    function clearPopup() {
		$('.popup.visible').addClass('transitioning').removeClass('visible');
        $('html').removeClass('overlay');
        setTimeout(function () {
            $('.popup').removeClass('transitioning');
        }, 200);
    }
});
});//]]> 

</script>

	
<script type="text/javascript">
var $tbody = $("#content-list");
$tbody.delegate("tr", "hover", function(){ $(this).closest("tr").toggleClass("hl"); })

var curSlId; // Current Supplier Id (used by actions)
var ttto; // ToolTipTimeOut
var options = {}; // list options
$("#msg-tooltip").hover(function(){ clearTimeout(ttto); }, function(){ ttto = setTimeout(function(){ $("#msg-tooltip").hide(); }, 1000);});

function getSupplierLeads(options) {
	var tab_html = "";
  var data = {"actions":[{"action":"get_supplier_leads"}]};
  $.extend(data.actions[0], options);
  $.ajax({
    type: "POST",
    url: "AJAX_interface.php",
    data: data,
    dataType: "json",
    error: function (XMLHttpRequest, textStatus, errorThrown) {
      $tbody.empty().append("<tr class=\"tr-new\"><td class=\"date\" colspan=\"14\">"+textStatus+"</td></tr>");
    },
    success: function (r, textStatus) {
      if (r.error) {
        $tbody.empty().append("<tr class=\"tr-new\"><td class=\"date\" colspan=\"14\">"+textStatus+"</td></tr>");
        return;
      }
      $tbody.empty();
      var sll = r.data.sLeadList;

      for (var sli=0; sli<sll.length; sli++) (function(){
        var sl = sll[sli];
		var nbr_lead_per_user_per_day = sl.nbr;

        var name_user = sl.origin == 'Internaute' ? sl.origin : (sl.name_user == null ? 'N/A' : sl.name_user);

        if (typeof this.current_lead_per_user_per_day == 'undefined' || this.current_lead_per_user_per_day == 0){
          this.current_lead_per_user_per_day = nbr_lead_per_user_per_day;
          this.parentLead = sl.id;
        }

        var typeLead = nbr_lead_per_user_per_day != 1 ? (this.current_lead_per_user_per_day == nbr_lead_per_user_per_day ? 'principal' : 'secondary') : '';

		var lead_status_show	= '';
		if(sl.lead_id!=''){
			lead_status_show = '<a href="/fr/manager/estimates/estimate-detail.php?id='+sl.lead_id+'" target="_blank">';
				lead_status_show += sl.lead_status;
			lead_status_show += '</a>';
		}else{
			lead_status_show = sl.lead_status;
		}
		
		tab_html =  "<td>"+
              (typeLead == '' || typeLead == 'principal' ? 
                "<a href=\"lead-detail.php?id="+sl.id+"\" class=\"_blank\"><img src=\"../ressources/icons/application_double.png\" alt=\"\"/></a>"+
                "<a href=\"#impression\"><img class=\"print\" src=\"../ressources/icons/printer.png\" alt=\"\"/></a>" :
                ""
              )+
              "<a href=\"<?php echo ADMIN_URL ?>estimates/estimate-detail.php?id=new&lead_id="+sl.id+"\" class=\"_blank\"><span class=\"icon page-white-add\"></span></a>"+
              (typeLead == 'principal' ?
                "<a href=\"#unfoldLevel\"><div class=\"unfold\"></div></a>" :
                ""
              )+
            "</td>"+
            "<td><a href=\""+sl.pdt_fo_url+"\" class=\"_blank\"><img src=\""+sl.pdt_pic_url+"\" alt=\"\"/></a></td>"+
            "<td>"+sl.timestamp+"<br />"+name_user+"</td>"+
            "<td>"+sl.com_login+"</td>"+
            "<td>"+sl.com_p_login+"</td>"+
            "<td><a href=\""+sl.pdt_fo_url+"\" class=\"lightblue _blank\">"+sl.pdt_name+"</a></td>"+
            "<td class=\"upper\"><a href=\""+sl.adv_bo_url+"\" class=\"darkblue _blank\">"+sl.adv_name+"</a></td>"+
            "<td class=\"message\"><u>message</u></td>";
            
			if(sl.msg_add == ''){
				tab_html +="<td><div id='popup_window' data-popup-target='#example-popup' onclick=affiche_popup('"+sl.id+"','add_note')><div id='result_notes_add_"+sl.id+"'><img src='../images/icons/add.gif' title='Ajouter une note'/></div></div></td>";
			}else {
				tab_html +="<td><div id='popup_window' data-popup-target='#example-popup' onclick=affiche_popup('"+sl.id+"','views_note')><div id='result_notes_add_"+sl.id+"'><img src='../images/icons/eye.gif' title='Voir ou modifier la note'/></div></div></td>";
			}
            
			tab_html +=
            "<td>"+sl.societe+"</td>"+
			"<td>"+lead_status_show+"</td>"+
			"<td>"+sl.bo_user_login+"</td>"+
            "<td>"+sl.email+"</td>"+
            "<td>"+sl.pays+"</td>"+
            "<td class=\"actions\"></td>";
		
		
        $("<tr/>", {
          id: "lead_"+sl.id,
          class: (typeLead == 'secondary' ? 'secondary-'+this.parentLead : ''),
          html: tab_html
           
        }).appendTo($tbody).find("td.message").hover(
          function(){
            var pos = $(this).position();
            clearTimeout(ttto);
            ttto = setTimeout(function(){
              $("#msg-tooltip").html(sl.msg!=""?sl.msg:"<i>aucun message</i>").css({left: pos.left-408, top: pos.top}).show();
            }, $("#msg-tooltip").is(":visible") ? 0 : 500);
          },
          function(){
            clearTimeout(ttto);
            if ($("#msg-tooltip").is(":visible")) {
              ttto = setTimeout(function(){
                $("#msg-tooltip").hide();
              }, 1000);
            }
          }
        ).end().find("td.actions").each(function(){
          if (typeLead == '' || typeLead == 'principal') {
            /*$(this).append($("<div/>", {
              "class": "icon accept",
              "title": "devis envoyé",
              "click": function(){
                setProcessingStatus(sl.id, <?php echo __LEAD_P_STATUS_PROCESSED__ ?>, {});
              }
            }))*/
            $(this).append($("<div/>", {
              "class": "icon delete",
              "title": "devis intraitable",
              "click": function(){
                curSlId = sl.id;
                $("#actions-dialog").dialog("open");
              }
            }));
          }
        });
        this.current_lead_per_user_per_day--;
      })();
      $('tr[class^="secondary-"]').hide();
    }
  });
}

$("#actions-dialog").dialog({
  width: 470,
  autoOpen: false,
  modal: true,
  buttons: {
    "Envoyer": function(){
      setProcessingStatus(curSlId, <?php echo __LEAD_P_STATUS_NOT_PROCESSABLE__ ?>, {
        reason: $("#actions-dialog").find("select").val(),
        comment: $("#actions-dialog").find("textarea").val()
      });
      $("#actions-dialog").dialog("close");
    }
  }
});

function setProcessingStatus(leadId, pStatus, vars){
  var data = {"actions":[{"action":"set_processing_status","leadId":leadId,"status":pStatus}]}
  $.extend(data.actions[0], vars);
  $.ajax({
    type: "POST",
    url: "AJAX_interface.php",
    data: data,
    dataType: "json",
    error: function (XMLHttpRequest, textStatus, errorThrown) {
    },
    success: function (r, textStatus) {
      if (!r.error) {
        getSupplierLeads(options);
      }
    }
  });
}

//Time listner and Time renitialiser !
var nextFilteringUpdate;
function filterUpdate(){
  clearTimeout(nextFilteringUpdate);
  nextFilteringUpdate = setTimeout(function(){ listUpdate(); }, 500);
}

//Listners !
$("#filtering-options")
  .find("select, input:checkbox, input:radio").change(function(){ filterUpdate(); }).end()
  .find("input:text").keypress(function(){ filterUpdate(); });

  
//Elements to send
var nextListUpdate;
function listUpdate(){
  options = {};
  $("#filtering-options").find("select, input[type='text'], input[type='checkbox']:checked, input[type='radio']:checked").each(function(){
    var name = $(this).attr("name");
    var val = $(this).val();
    if (val != "") {
      if (options[name] === undefined) {
        options[name] = val;
      }
      else {
        if (!$.isArray(options[name]))
          options[name] = [options[name]];
        options[name].push(val);
      }
    }
  });
  
  
  getSupplierLeads(options);
  
  var to = arguments.length > 0 ? arguments[0] : 120000;
  clearTimeout(nextListUpdate);
  nextListUpdate = setTimeout(function(){
    listUpdate();
  },to);
}
listUpdate();

$("#btn-print-sleads-list").click(function(){
  var leadIds = [];
  $tbody.find("tr").each(function(){
    leadIds.push($(this).attr("id").split("_")[1]);
  });
  var form = $("#form-print").get(0);
  form.leadIds.value = leadIds.join(",");
  form.submit();
});

$tbody.find("img.print").live("click",function(){
  var leadIds = [];
  var form = $("#form-print").get(0);
  leadIds.push($(this).closest("tr").attr("id").split("_")[1]);
  $('tr.secondary-'+leadIds[0]).each(function(){
    leadIds.push($(this).attr("id").split("_")[1]);
  })
  form.leadIds.value = leadIds.join(",");
  form.submit();
  return false;
});

$('div.unfold').live(
  'click', function(){
    $(this).css('backgroundPosition', ($(this).css('backgroundPosition') != '-10px 50%' ? '-10px' : '0px'));
    var parentLeadId = $(this).closest('tr').attr("id").split("lead_")[1];
    $('tr.secondary-'+parentLeadId).toggle();
  });
</script>

<script type='text/javascript'>//<![CDATA[ 
$(window).load(function(){
jQuery(document).ready(function ($) {
    $('[data-popup-target]').click(function () {
        $('html').addClass('overlay');
        var activePopup = $(this).attr('data-popup-target');
        $(activePopup).addClass('visible');
    });
    $(document).keyup(function (e) {
        if (e.keyCode == 27 && $('html').hasClass('overlay')) {
            clearPopup();
        }
    });
    $('.popup-exit').click(function () {
        clearPopup();
    });
    $('.popup-overlay').click(function () {
        clearPopup();
    });
    function clearPopup() {
		$('.popup.visible').addClass('transitioning').removeClass('visible');
        $('html').removeClass('overlay');
        setTimeout(function () {
            $('.popup').removeClass('transitioning');
        }, 200);
    }
});
});//]]>  
</script>

<script>
	function affiche_popup(id_contact,action){
		$( "#example-popup" ).addClass( "visible");
		$.ajax({
			type: "GET",
			url: "get_precisions.php?action=get_message&id_contact="+id_contact+"&send_val="+action,
			success: function (d) {
			$("#content_ajax").html(d); 
			}
		});
	}
	
	function active_modif(){
		$("textarea").prop('disabled', false);
		$("#btn-precisions_additional").show();
		$("#btn-precisions_additional_modif").hide();
	}
	
	function update_additionall(id_contact,action){
		// var message = $("#precisions_additional").val().replace(/\n/g, "\n\g");
		var message_final = $("#precisions_additional").val().replace(/(\r\n|\n|\r)/gm, '%0D%0A');
		if(message_final == ''){
			alert('merci de remplir ce champ !!');
		}else {
		$.ajax({
			type: "GET",
			url: "get_precisions.php?action=update_message&id_contact="+id_contact+"&message="+message_final,
			success: function (d) {
				if(action == 'views_note'){
					$("#result_notes_add_"+id_contact).empty();
					clearPopup_close();
					$("#result_notes_add_"+id_contact).html("<div id='popup_window' data-popup-target_close='#example-popup' onclick=affiche_popup('"+id_contact+"','views_note')><img src='../images/icons/eye.gif' title='Voir ou modifier la note'/></div>");
				}else {
					$("#result_notes_add_"+id_contact).empty();
					clearPopup_close();
					$("#result_notes_add_"+id_contact).html("<div id='popup_window' data-popup-target_close='#example-popup' onclick=affiche_popup('"+id_contact+"','views_note')><img src='../images/icons/eye.gif' title='Voir ou modifier la note'/></div>");
				}
			
			}
		});
		}
	}
	 function clearPopup_close() {
		$('.popup.visible').addClass('transitioning').removeClass('visible');
        $('html').removeClass('overlay');
        setTimeout(function () {
            $('.popup').removeClass('transitioning');
        }, 200);
    }
	
	    $('[data-popup-target_close]').click(function () {
        $('html').addClass('overlay');
        var activePopup = $(this).attr('data-popup-target_close');
        $(activePopup).addClass('visible');
    });
    $(document).keyup(function (e) {
        if (e.keyCode == 27 && $('html').hasClass('overlay')) {
            clearPopup();
        }
    });
    
</script>


<div id="example-popup" class="popup">
    <div class="popup-body">	
	    <div class="popup-content">
            	<h2 class="popup-title"> Note sur Lead </h2>
			<div id="content_ajax">
			</div>
			<span class="popup-exit"><button class="btn ui-state-default ui-corner-all">Fermer</button></span>
        </div>
    </div>
	</div>
<?php } ?>
<?php require(ADMIN."tail.php") ?>


