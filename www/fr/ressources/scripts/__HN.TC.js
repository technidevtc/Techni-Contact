/**
 * jQuery hashchange event - v1.3 - 7/21/2010
 * http://benalman.com/projects/jquery-hashchange-plugin/
 *
 * Copyright (c) 2010 "Cowboy" Ben Alman
 * Dual licensed under the MIT and GPL licenses.
 * http://benalman.com/about/license/
 */
(function($,e,b){var c="hashchange",h=document,f,g=$.event.special,i=h.documentMode,d="on"+c in e&&(i===b||i>7);function a(j){j=j||location.href;return"#"+j.replace(/^[^#]*#?(.*)$/,"$1")}$.fn[c]=function(j){return j?this.bind(c,j):this.trigger(c)};$.fn[c].delay=50;g[c]=$.extend(g[c],{setup:function(){if(d){return false}$(f.start)},teardown:function(){if(d){return false}$(f.stop)}});f=(function(){var j={},p,m=a(),k=function(q){return q},l=k,o=k;j.start=function(){p||n()};j.stop=function(){p&&clearTimeout(p);p=b};function n(){var r=a(),q=o(m);if(r!==m){l(m=r,q);$(e).trigger(c)}else{if(q!==m){location.href=location.href.replace(/#.*/,"")+q}}p=setTimeout(n,$.fn[c].delay)}$.browser.msie&&!d&&(function(){var q,r;j.start=function(){if(!q){r=$.fn[c].src;r=r&&r+a();q=$('<iframe tabindex="-1" title="empty"/>').hide().one("load",function(){r||l(a());n()}).attr("src",r||"javascript:0").insertAfter("body")[0].contentWindow;h.onpropertychange=function(){try{if(event.propertyName==="title"){q.document.title=h.title}}catch(s){}}}};j.stop=k;o=function(){return a(q.location.href)};l=function(v,s){var u=q.document,t=$.fn[c].domain;if(v!==s){u.title=h.title;u.open();t&&u.write('<script>document.domain="'+t+'"<\/script>');u.close();q.location.hash=v}}})();return j})()})(jQuery,this);

if (!window.HN) HN = window.HN = {};

$.extend(HN.TC, {
  url: (function(){
    var o={protocol:location.protocol,path:[],page:"",params:{}};
    o.path = location.pathname.substring(1).split("/");
    o.page = o.path.pop() || "";
    o.ext = o.page ? (/\.([^.]+)$/.exec(o.page) || [])[1] || "" : "";
    o.page = o.page ? o.page.replace(/\.[^.]+$/, "") : "";
    location.search.substring(1).replace(/([^&]+)=([^&]+)/gi,function(s,$1,$2){o.params[$1]=$2});
    return o;
  }()),
  arrayRemove: function(array, from, to){
    var rest = array.slice((to || from) + 1 || array.length);
    array.length = from < 0 ? array.length + from : from;
    return array.push.apply(array, rest);
  },
  CustomerTitleList: function(){
    titleList = new Array();
    titleList[1] = "M.";
    titleList[2] = "Mme";
    titleList[3] = "Mlle";
    return titleList;
  },
  get_pdt_fo_url: function(p_id, p_rn, f_id){ return HN.TC.Locals.URL+"produits/"+f_id+"-"+p_id+"-"+p_rn+".html"; },
  get_fam_fo_url: function(f_rn){ return HN.TC.Locals.URL+"familles/"+f_rn+".html"; },
  getCustomerTitleIndexFromLabel: function(label){
    var titreList = HN.TC.CustomerTitleList();

    for(var a=1;a<=titreList.length; a++){
      if(titreList[a] == label)
        return a;
    }
    return false;
  },
  hasFeature: (function(){
    var o = {},
        div = $("<div>")[0],
        input = $("<input>")[0];
    o.css = {
      placeholder: input.placeholder !== undefined
    };
    return o;
  }()),
  addPlaceholder: function($inputs, text, useCssPh){
    if (useCssPh && HN.TC.hasFeature.css.placeholder) {
      $inputs.attr("placeholder", text);
    } else {
      $inputs.each(function(){
        var $input = $(this);
        $input.wrap("<div class=\"placeholder-wrapper\">").after("<span class=\"placeholder\">"+text+"</span>");
        if ($input.val() !== "")
          $input.next().hide();
      });
      $inputs.on("blur", function(){
        if ($(this).val() === "")
          $(this).next().show();
      }).next().on("mousedown", function(){
        var $ph = $(this),
            $input = $ph.prev();
        $ph.hide();
        setTimeout(function(){ $input.focus(); }, 0);
      });
    }
  }
});
if (!HN.TC.GVars) HN.TC.GVars = {};

var cm = new HN.CookieManager();

HN.TC.MainMenu = function () {
  // not on secure parts, no on cart page
  if (HN.TC.url.protocol != "http:" || HN.TC.url.page == "panier")
    return false;

  var me = this,
      $headerMenu = $("#header-menu"),
      $headerSubmenu = $("#header-submenu"),
      showTO, hideTO, state = "hidden";;
  $("#header-menu a").not(".last").each(function(){
    var cat1ID = $(this).data("main-cat-id");
    if (HN.TC.categories[cat1ID]) {
        var cat1 = HN.TC.categories[cat1ID], cat2, cat3, cat2url, cat3url, cat3last,
            colCount = cat1.children.length>(16-2) ? 5 : 4,
            cat2PerCol = Math.ceil((cat1.children.length+2)/colCount),
            colCat2Count = 0,
            html = "", c2i, c3i,
            $gigamenu = $("<div class=\"gigamenu colCount"+colCount+"\"></div>");

      html += "<div class=\"col\">"
      for (c2i=0; c2i<cat1.children.length; c2i++) {
        cat2 = cat1.children[c2i];
        cat2url = HN.TC.get_fam_fo_url(cat2.ref_name);
        html += "<ul>"+
                  "<li class=\"cat2\">"+
                    "<a href=\""+cat2url+"\">"+cat2.name+"</a>"
                  "</li>";
        for (c3i=0; c3i<cat2.children.length; c3i++) {
          cat3 = cat2.children[c3i];
          cat3last = c3i == cat2.children.length-1;
          cat3url = HN.TC.get_fam_fo_url(cat3.ref_name);
          html += "<li class=\"cat3\">"+
                    "<a"+(cat3last?" class=\"last\"":"")+" href=\""+cat3url+"\">"+cat3.name+"</a>"+
                    (cat3last ? " … <a class=\"more\" href=\""+cat2url+"\"><b></b><b></b></a>" : "")
                  "</li>";
        }
        html += "</ul>";
        colCat2Count++;
        if (colCat2Count >= cat2PerCol) {
          html += "</div><div class=\"col\">";
          colCat2Count = 0;
        }
      }

      html += "</div>"+
              "<div class=\"zero\"></div>"+
              "<div class=\"html-zone\">"+cat1.text_content.replace(/(https?:\/\/)([^\/]+)/gi, "$1"+HN.TC.URLinfos.siteURL)+"</div>";
      $gigamenu.html(html);
      $headerSubmenu.append($gigamenu);
      $(this).data("gigamenu", $gigamenu);
    }
  });

  me.reorganize = function($gigamenu){
    $gigamenu.data("reorganized", true);
  };
  me.startShow = function(){
    switch (state) {
      case "hidden":
        showTO = setTimeout(me.showSubmenu, 500);
        state = "showing";
        break;
      case "showing": break;
      case "hiding":
        clearTimeout(hideTO);
        me.showSubmenu();
        break;
      case "visible":
        me.showSubmenu();
        break;
    }
  };
  me.showSubmenu = function(){
    var $a = $headerMenu.find("a.hover"),
        $gigamenu = $a.data("gigamenu");
    $headerSubmenu.find(".gigamenu").hide();
    $("#header-submenu-top-arrow").css({ left: $a.position().left +($a.width()/2) });
    if ($gigamenu) {
      $headerSubmenu.show();
      $gigamenu.show();
      if (!$gigamenu.data("reorganized"))
        me.reorganize($gigamenu);
    } else {
      $headerSubmenu.hide();
    }
    state = "visible";
  };
  me.startHide = function(){
    switch (state) {
      case "hidden": break;
      case "showing":
        clearTimeout(showTO);
        me.hideSubmenu();
        break;
      case "hiding": break;
      case "visible":
        hideTO = setTimeout(me.hideSubmenu, 250);
        state = "hiding";
        break;
    }
  };
  me.hideSubmenu = function(){
    $headerMenu.find("a").removeClass("hover");
    $headerSubmenu.hide().find(".gigamenu").hide();
    state = "hidden";
  };

  $headerMenu
    .on("mouseenter", "a", function(){
      $headerMenu.find("a").removeClass("hover");
      $(this).addClass("hover");
      me.startShow();
    })
    .on("mouseleave", function(){
      me.startHide();
    });
  $headerSubmenu
    .on("mouseenter", function(){
      me.startShow();
    })
    .on("mouseleave", function(){
      me.startHide();
    })

  // google tracking
  $headerMenu.on("click", "a", function(){
    if (window._gaq)
      _gaq.push(['_trackEvent', 'Menu header', 'Liens Familles 1', $(this).text()]);
  });
  $headerSubmenu
    .on("click", ".gigamenu li.cat2 a", function(){
      if (window._gaq)
        _gaq.push(['_trackEvent', 'Menu header', 'Liens Familles 2', $(this).text()]);
    })
    .on("click", ".gigamenu li.cat3 a", function(){
      if (window._gaq)
        _gaq.push(['_trackEvent', 'Menu header', 'Liens Familles 3', $(this).text()]);
    });
}

// Account Functionalities
HN.TC.SecureInit = function () {

	var requestSent = false;
  var cur_place = []; // where we are
  if (HN.TC.url.protocol == "https:") {
    if (HN.TC.url.path[1] == "compte")
      cur_place = ["account",  HN.TC.url.page];
    else if (HN.TC.url.path[1] == "commande")
      cur_place = ["order-step",  HN.TC.url.page];
  }

  // secure global message dialog box
  window.$db_msg = $("#message-dialog");
  window.db_msg_close_to = null;
  $db_msg.dialog({
    width: 500,
    autoOpen: false,
    modal: true,
    draggable: false,
    resizable: false,
    beforeClose: function(){
      // to avoid some jquery pb by opening/closing dialog too fast when a timeout is scheduled
      clearTimeout(db_msg_close_to);
    }
  });
  window.$db_msg_title = $("#ui-dialog-title-message-dialog");
  var db_msg_close_lnk = "<br /><br /><div class=\"blue-close\"><span>[Fermer]</span></div>";

	var $account = $("#body div.account");
	if ($account.length > 0) {
		// Infos Modify Submit button
		$account.find("#infos-modify-save").click(function(){
			if (!requestSent) document.coord.submit();
			requestSent = true;
			return false;
		});

		// Personnal Infos Resume
		$infos_resume = $account.find("div.infos-resume");
		if ($infos_resume.length > 0) {
			var $global_actions = $account.find("div.infos-resume > div.actions");
			var save = $global_actions.find("a:first");
			var cancel = $global_actions.find("a:last");

			$account.find("div.infos-resume > ul li").each(function(){
				var li = $(this);
				$("div.actions a:first", li).click(function(){
				var $input = li.find('input');
                                if($input.attr('name') == 'pass'){
                                  var html = '\
                                  <div class="zero added_confirm_tags"></div>\n\
                                  <label class="added_confirm_tags">Confirmer:</label>\n\
                                  <input type="password" name="pass2" class="edit added_confirm_tags" value=""/>';
                                  li.append(html);
                                }
					var text = $("div.text", li).hide();
					var actions = $("div.actions", li).hide();
					var edit = $("input.edit", li).show();
					var help = $("div.help", li).show();
					var helpTimeout;
					var helpHTML = help.html();
					$global_actions.show();
					$('input[name=pass2]').live(
                                      'blur', function(){
                                        if($('input[name=pass2]').val() != $('input[name=pass]').val())
                                          alert('Confirmation du mot de passe incorrect');
                                      });
					var save_click = function(){
						$.ajax({
							data: "action=edit&field=" + edit.attr("name") + "&data=" + edit.val()+($('input[name=pass2]').length >0 ? "&data2="+$('input[name=pass2]').val(): ''),
							dataType: "json",
							error: function (XMLHttpRequest, textStatus, errorThrown) {
								help.html("Erreur fatale !")
									.addClass("error")
									.fadeOut(50).fadeIn(50)
									.fadeOut(50).fadeIn(50)
									.fadeOut(50).fadeIn(50);
								clearTimeout(helpTimeout);
								helpTimeout = setTimeout(function(){help.html(helpHTML).removeClass("error");}, 3000);
							},
							success: function (data, textStatus) {
								if (data.data) {
									text.show().html(data.data);
									actions.show();
									edit.hide();
									help.hide();
									$global_actions.hide();
									save.unbind("click", save_click);
									cancel.unbind("click", cancel_click);
								}
								else if (data.error) {
									help.html(data.error)
										.addClass("error")
										.fadeOut(50).fadeIn(50)
										.fadeOut(50).fadeIn(50)
										.fadeOut(50).fadeIn(50);
									clearTimeout(helpTimeout);
									helpTimeout = setTimeout(function(){help.html(helpHTML).removeClass("error");}, 3000);
								}
							},
							type: "GET",
							url: "AccountAjaxMod.php"
						});
						return false;
					};
					save.bind("click", save_click);

					var cancel_click = function(){
						clearTimeout(helpTimeout);
						help.stop(true).css("opacity", "").hide().html(helpHTML).removeClass("error");
						text.show();
						actions.show();
						edit.hide();
                                                $('.added_confirm_tags').remove();
						$global_actions.hide();
						save.unbind("click", save_click);
						cancel.unbind("click", cancel_click);
						return false;
					};
					cancel.bind("click", cancel_click);

					return false;
				});

			});
		}

		var cartID = $account.find("input[name='cartID']").val();
		// Download estimate
		$account.find("div.dl-esti").click(function(){
			window.open(HN.TC.Locals.URL+"pdf/devis_generate.php?cartID="+cartID, "_blank");
		});

		// Estimate to Order
		$account.find("div.esti-to-order").click(function(){
			if (!requestSent) {
				document.location.href = HN.TC.Locals.AccountURL+"esti-to-order.php?cartID="+cartID;
				requestSent = true;
			}
		});

		// Contact Form
		var $contact_form = $account.find("div.contact-form");
		if ($contact_form.length > 0) {
			// Contact Form Selects
			$contact_form.find("select[name='type']").change(function(){
				var $select_id = $contact_form.find("select[name='id']").empty();
				var $label_id = $contact_form.find("label[for='id']").empty();
				switch (this.value) {
					case "1" :
						for (k in HN.TC.GVars.account_orders)
							$select_id.append("<option value=\""+HN.TC.GVars.account_orders[k].id+"\">"+HN.TC.GVars.account_orders[k].id+" - "+HN.TC.GVars.account_orders[k].date+"</option>");
						$label_id.html("Numéro de commande:");
						break;
					case "2" :
						for (k in HN.TC.GVars.account_estimates)
							$select_id.append("<option value=\""+HN.TC.GVars.account_estimates[k].id+"\">"+HN.TC.GVars.account_estimates[k].id+" - "+HN.TC.GVars.account_estimates[k].date+"</option>");
						$label_id.html("Numéro de devis:");
						break;
                                        case "3" :
						/*for (k in HN.TC.GVars.account_estimates)
							$select_id.append("<option value=\""+HN.TC.GVars.account_estimates[k].id+"\">"+HN.TC.GVars.account_estimates[k].id+" - "+HN.TC.GVars.account_estimates[k].date+"</option>");
						$label_id.html("Numéro de devis:");*/
						break;
                                        case "4" :
						for (k in HN.TC.GVars.account_leads){
                                                    var html = '<optgroup label="'+HN.TC.GVars.account_leads[k].fastdesc+'">';
                                                    for (l in HN.TC.GVars.account_leads[k].leads)
							html += "<option value=\""+HN.TC.GVars.account_leads[k].leads[l].id+"\">"+HN.TC.GVars.account_leads[k].leads[l].id+" - "+HN.TC.GVars.account_leads[k].leads[l].date+" à "+HN.TC.GVars.account_leads[k].leads[l].adv_name+"</option>";
                                                    html += '</optgroup>';
                                                    $select_id.append(html);
                                                }
                                                $label_id.html("Numéro de devis:");
						break;
					default:break;
				}
			}).change();
			if (HN.TC.GVars.account_cfType && HN.TC.GVars.account_cfID) {
				$contact_form.find("select[name='type']").val(HN.TC.GVars.account_cfType).change();
				$contact_form.find("select[name='id']").val(HN.TC.GVars.account_cfID);
			}

			// Contact Form Submit button
			$contact_form.find("div.send-contact-request").click(function(){
				if (!requestSent) {
					var list = [];
					$contact_form.find("div[class*='js-checkbox'][class*='checked']").each(function(){
						list.push(escape($(".js-checkbox-label", this.parentNode).html()));
					});
					$contact_form.find("input[name=js-checkbox-checked-list]").val(list.join("<_>"));
					requestSent = true;
					document.account_contact_form.submit();
				}
			});
		}

	}

	var $esti_send = $("#body").find(".btn-estimate-send, .estimate-send");
	if ($esti_send.length > 0) {

		// Send Estimate to Colleague functionalities
		var sec_db = document.createElement("div");
		sec_db.className = "spc-db";

		var sec_db_bg = document.createElement("div");
		sec_db_bg.className = "spc-db-bg";
		sec_db.appendChild(sec_db_bg);

		var umail_label = document.createElement("label");
		umail_label.innerHTML = "Votre E-mail : ";
		sec_db_bg.appendChild(umail_label);
		var umail = document.createElement("input");
		umail.type = "text";
		umail.size = "50";
		umail.maxlength = "255";
		umail.value = HN.TC.GVars.userEmail;
		sec_db_bg.appendChild(umail);
		sec_db_bg.appendChild(document.createElement("br"));
		sec_db_bg.appendChild(document.createTextNode("Envoyer ce devis à un collègue :"));
		sec_db_bg.appendChild(document.createElement("br"));

		var fmail = [];
		for (var i = 0; i < 5 ; i++) {
			fmail_label = document.createElement("label");
			fmail_label.innerHTML = "E-mail "+(i+1)+" : ";
			sec_db_bg.appendChild(fmail_label);
			fmail[i] = document.createElement("input");
			fmail[i].type = "text";
			fmail[i].size = "50";
			fmail[i].maxlength = "255";
			sec_db_bg.appendChild(fmail[i]);
			sec_db_bg.appendChild(document.createElement("br"));
		}

		var send = document.createElement("input");
		send.type = "button";
		send.value = "Envoyer";
		send.className = "send";
		send.onclick = function() {
			var fmails_str = "";
			for (var i = 0; i < fmail.length; i++) {
				fmails_str += "&fmail"+(i+1)+"="+fmail[i].value;
			}
			$.ajax({
				data: "action=sendEstimate&umail="+umail.value+fmails_str+"&cartID="+sec.cartID,
				dataType: "json",
				error: function (XMLHttpRequest, textStatus, errorThrown) {
					alert(textStatus+" "+errorThrown);
				},
				success: function (data, textStatus) {
					if (!data.data) {
						if (data.error) {
							alert("Error : " + data.error);
						}
						if (data.warning) {
							alert("Warning : " + data.warning);
						}
					}
					else {
						alert(data.data);
						sec.Hide();
					}
				},
				type: "GET",
				url: HN.TC.Locals.AJAXSendColleagues
			});
		};
		sec_db_bg.appendChild(send);

		$("#body").append(sec_db);

		var sec = new HN.Window(sec_db);
		sec.setTitleText("Envoyer un devis à un collègue");
		sec.setMovable(true);
		sec.showCancelButton(true);
		sec.setValidFct( function() {sec.Hide();} );
		sec.Build();

		$esti_send.click(function(){
			sec.cartID = $("#body").find("input[name='cartID']").val();
                        if (sec.cartID == null) sec.cartID = $(this).find('a').attr('href');
			if (sec.cartID == null) sec.cartID = this.href.substr("javascript:cartID::".length, this.href.length);
			sec.Show();
			return false;
		});

	}

	// Order and Estimate steps
	var $order_steps = $("#body div.order-steps");
	if ($order_steps.length > 0) {

    /*$(".cart-table td.al a").on("click", function(){
      $.fancybox()
    })*/

    // estimate step 3
    $btn_esti_actions = $order_steps.find("div.btn-estimate-actions");

    if ($btn_esti_actions.length > 0) {
      var cartID = $order_steps.find("input[name='cartID']").val();
      $btn_esti_actions.find("div.btn-estimate-print, div.link-estimate-print").live('click', function(){
        if(!cartID)
          cartID = $(this).closest('tr').find('td:last').find('a').attr('href');
        if(!cartID)
          cartID = $(this).find('a').attr('data-cart-id');
        if(!cartID)
          cartID = $(this).attr('data-cart-id');
        window.open(HN.TC.Locals.URL+"pdf/devis_generate.php?cartID="+cartID, "_blank");
      });
    }

    // Order and Estimate lists
    var $table_list = $(".account-order-list");
    if ($table_list.length > 0) {
      // estimate list
      $btn_esti_actions = $table_list.find("a.link-estimate-print").each( function(){
        $(this).click(function(){
          cartID = $(this).attr('data-cart-id');
          window.open(HN.TC.Locals.URL+"pdf/devis_generate.php?cartID="+cartID, "_blank");
        });
      });
    }

    // order step 3
    if (cur_place[0] == "order-step" && cur_place[1] == "order-step3") {
      var $payment_block = $("#payment-block"),

          reqInProgress = false; // preload cvv helper img

      // payment submission
      $(".btn-proceed-payment").on("click", function(){
        // one payment method must be selected
        var payment_mode = parseInt($payment_block.find("input[name='mode_paiement']:checked").val());
        if (isNaN(payment_mode)) {
          $db_msg_title.text("Erreur validation commande");
          $db_msg.html("Afin de poursuivre votre commande, veuillez sélectionner votre mode de paiement."+db_msg_close_lnk).dialog("open");
          return false;
        }
        // cgv's must be checked
        $cgv = $payment_block.find("input[name='cgv']");
        if (!$cgv.prop("checked")) {
          $db_msg_title.text("Erreur validation commande");
          $db_msg.html("Afin de poursuivre la commande, merci de prendre connaissance et d'accepter nos Conditions Générales de Vente."+db_msg_close_lnk).dialog("open");
          return false;
        }

        if (!reqInProgress) {
          reqInProgress = true;
          $db_msg_title.text("Validation commande");
          $db_msg.html("Validation de la commande en cours ...<br /><br /><img src=\""+HN.TC.Locals.RessourcesURL+"images/wait.gif\" alt=\"\" />").dialog("open");
          if (window.curOrderID) {
            if (payment_mode == 0) {
              $("#be2bill_form").submit();

            } else {
              $.ajax({
                data: { orderID: window.curOrderID, payment_mode: payment_mode },
                dataType: "json",
                type: "POST",
                url: HN.TC.Locals.OrderURL+"order-edit.php"
              }).done(function(msg){
                var r = msg.response;
                if (r && r.orderID) {
                  if (payment_mode > 0) {
                    reqInProgress = false;
                    location.href = HN.TC.Locals.OrderURL+"order-confirmed.html?orderID="+r.orderID;
                  } else {
                    // should never happen
                    reqInProgress = false;
                  }
                } else {
                  var error_msg;
                  if (msg.error && msg.error.text)
                    error_msg = msg.error.text;
                  else
                    error_msg = "Erreur inconnue";
                  $db_msg_title.text("Erreur validation commande");
                  $db_msg.html(error_msg+db_msg_close_lnk).dialog("open");
                  reqInProgress = false;
                }
              }).fail(function(jqXHR, textStatus, errorThrown){
                $db_msg_title.text("Erreur validation commande");
                $db_msg.html((jqXHR.responseText || (textStatus+" : "+errorThrown))+db_msg_close_lnk).dialog("open");
                reqInProgress = false;
              });
            }
          } else {
            $.ajax({
              data: { payment_mode: payment_mode },
              dataType: "json",
              type: "POST",
              url: HN.TC.Locals.OrderURL+"order-add.php"
            }).done(function(msg){
              var r = msg.response;
              if (r && r.orderID) {
                window.curOrderID = r.orderID;
                if (payment_mode == 0) {
                  // BC
                  if (r.b2b_params) {
                    var $b2binputs = $("#be2bill_form input");
                    $.each(r.b2b_params, function(k, v){
                      $b2binputs.filter("[name='"+k+"']").val(v);
                    });
                    $("#be2bill_form").submit();
                  }
                } else {
                  // others
                  reqInProgress = false;
                  location.href = HN.TC.Locals.OrderURL+"order-confirmed.html?orderID="+r.orderID;
                }
              } else {
                var error_msg;
                if (msg.error && msg.error.text)
                  error_msg = msg.error.text;
                else
                  error_msg = "Erreur inconnue";
                $db_msg_title.text("Erreur validation commande");
                $db_msg.html(error_msg+db_msg_close_lnk).dialog("open");
                reqInProgress = false;
              }
            }).fail(function(jqXHR, textStatus, errorThrown){
              $db_msg_title.text("Erreur validation commande");
              $db_msg.html((jqXHR.responseText || (textStatus+" : "+errorThrown))+db_msg_close_lnk).dialog("open");
              reqInProgress = false;
            });
          }
        }

      });
      $payment_block.find("div.order-cgv a:first").on("click", function(){
        window.open("CGDV VPC.html","CGDV_VPC","status=1,width=800,height=600,scrollbars=1");
        return false;
      });
    }

    // order confirmation
    $order_steps.find("a.option-print").click(function(){window.open(this.href, "_blank");return false;});
  }

  // Create/Edit addresses functions, only where it should be executed
  if ($.inArray(cur_place[0], ["account","order-step", "estimate-step"]) !== -1 && window.addressesByType) {

    // usefull address list indexed by their id, if addressesByType is set
    HN.TC.getAddressesById = function(){
      var abi = {};
      for (var type in addressesByType)
        for (var id in addressesByType[type]['list'])
          abi[id] = addressesByType[type]['list'][id];
      return abi;
    };

    // address DB
    var $db = $("#account-edit-address-form-dialog"),
        type_da = HN.TC.ClientsAdresses.TYPE_DELIVERY, // it's shorter this way
        type_ba = HN.TC.ClientsAdresses.TYPE_BILLING,
        addressesById = HN.TC.getAddressesById();
    $db.dialog({
      width: 520,
      autoOpen: false,
      modal: true,
      draggable: false,
      resizable: false
    });
    var $db_title = $("#ui-dialog-title-account-edit-address-form-dialog"),
        $fieldList = $db.find("input, select, textarea"),
        $labelList = $fieldList.prev();

    HN.TC.showEditAddressForm = function(type, id) {
      var address = addressesByType[type]['list'][id],
          typeText = HN.TC.ClientsAdresses.typeList[type];

      // reset error state
      $db.find(".response-error").html("");
      $fieldList.removeClass("error");
      $labelList.removeClass("error");

      $("#aeafd-delivery-infos")[type==type_da ? "show" : "hide"]();
      $("#aeafd-type").text(typeText.charAt(0).toUpperCase()+typeText.substring(1));

      if (id != null) {
        if (address) {
          for (var f in address)
            $fieldList.filter("[name='"+f+"']").val(address[f]);
          $fieldList.filter("[name='set_as_main']").prop("checked", address.num == 0);
          $("#aeafd-btn").data({ action: "update", type: type, id: id}).text("Enregistrer");
          $db_title.text("Modification d'une adresse de "+typeText)
        } else if (type == type_ba) {
          // no address found in the billing list -> consider we're copying the selected delivery one
          address = addressesByType[type_da]['list'][delivery_address_id];
          if (address) {
            for (var f in address)
              $fieldList.filter("[name='"+f+"']").val(address[f]);
            $fieldList.filter("[name='set_as_main']").prop("checked", true);
            $("#aeafd-btn").data({ action: "create", type: type }).text("Enregistrer");
            $db_title.text("Modification d'une adresse de "+typeText)
          } else {
            return false;
          }
        } else {
          return false;
        }
      } else {
        $fieldList.each(function(){
          var $f = $(this);
          if ($f.attr("type") == "checkbox")
            $f.prop("checked", false);
          else
            $f.val("");
        });
        $fieldList.filter("[name='pays']").val("FRANCE");
        $("#aeafd-btn").data({ action: "create", type: type }).text("Créer adresse");
        $db_title.text("Création d'une adresse de "+typeText)
      }

      $db.dialog("open");
    };

    HN.TC.doClientAddressAjaxRequest = function(data, cb, show_msg_db){
      if (show_msg_db) {
        switch (data.action) {
          case "create": $db_msg_title.text("Création de l'adresse «"+data.data.nom_adresse+"»"); break;
          case "update": $db_msg_title.text("Modification de l'adresse «"+data.data.nom_adresse+"»"); break;
          case "remove":
            if (!data.data)
              data.data = addressesByType[data.type]['list'][data.id];
            $db_msg_title.text("Suppression de l'adresse «"+data.data.nom_adresse+"»");
            break;
        }
      }
      $.ajax({
        data: data,
        dataType: "json",
        type: "POST",
        url: HN.TC.Locals.AccountURL+"AccountAddressAjaxMod.php"
      }).done(function(msg){
        if (msg.response) {
          if (data.action == "create" || data.action == "update") {
            $db.find(".response-error").html("");
            $db.dialog("close");
          }
          if (show_msg_db && msg.response.text) {
            $db_msg.html(msg.response.text).dialog("open");
            db_msg_close_to = setTimeout(function(){ $db_msg.dialog("close"); }, 2500);
          }
          if (msg.response.addressesByType) {
            addressesByType = msg.response.addressesByType;
            addressesById = HN.TC.getAddressesById()
          }
          if (cb)
            cb(msg.response);
        } else {
          var error_msg;
          if (msg.error && msg.error.text)
            error_msg = msg.error.text;
          else
            error_msg = "Erreur inconnue";

          switch (data.action) {
            case "create":
            case "update":
              if (msg.error && msg.error.fields) {
                $fieldList.removeClass("error");
                $labelList.removeClass("error");
                for (var fn in msg.error.fields) {
                  $labelList.filter("[for='"+fn+"']").addClass("error");
                  $fieldList.filter("[name='"+fn+"']").addClass("error");
                }
              }
              $db.find(".response-error").html(error_msg);
              break;
            case "remove":
              if (show_msg_db)
                $db_msg.html(error_msg).dialog("open");
              break;
          }
        }
      }).fail(function(jqXHR, textStatus, errorThrown){
        if (data.action == "create" || data.action == "update")
          $db.dialog("close");
        // always show the fatal error message (don't take into account the show_msg_db var)
        $db_msg.html(jqXHR.responseText || (textStatus+" : "+errorThrown)).dialog("open");
      });
    };

    $("#aeafd-btn").on("click", function(){
      var data = $(this).data();
      data.data = {};
      $fieldList.each(function(){
        var $f = $(this);
        data.data[$f.attr("name")] = $f.attr("type") == "checkbox" ? ($f.prop("checked")|0) : $f.val();
      })
      HN.TC.doClientAddressAjaxRequest(data, function(response){
        HN.TC.processClientAddressAjaxResponse(data, response);
      }, cur_place[1] == "infos");
    });

    if (cur_place[1] == "infos") {

      HN.TC.processClientAddressAjaxResponse = function(data, response){
        HN.TC.showAddressList();
      };

      HN.TC.showAddressList = function(){
        for (var type in addressesByType) {
          var $ul;
          switch (parseInt(type)) {
            case type_da: $ul = $("#delivery-address-list"); break;
            case type_ba: $ul = $("#billing-address-list"); break;
          }
          $ul.empty();
          var uac = addressesByType[type]['length'], id;
          for (id in addressesByType[type]['list']) {
            var ua = addressesByType[type]['list'][id];
            var titreListe = HN.TC.CustomerTitleList();
            $ul.append(
              "<li class=\"address-block\">"+
                "<div class=\"blue-smaller-title\">"+ua.nom_adresse+"</div>"+
                "<ul>"+
                  "<li>"+(typeof ua.titre!='undefined'?titreListe[ua.titre]:'')+" "+ua.nom+"</li>"+
                  "<li>"+ua.prenom+"</li>"+
                  "<li>"+ua.societe+"</li>"+
                  "<li>"+ua.adresse+"</li>"+
                  "<li>"+ua.complement+"</li>"+
                  "<li>"+ua.cp+"</li>"+
                  "<li>"+ua.ville+"</li>"+
                  "<li>"+ua.pays+"</li>"+
                  "<li>"+ua.tel1+"</li>"+
                  "<li>"+ua.fax1+"</li>"+
                  "<li>"+ua.infos_sup+"</li>"+
                "</ul>"+
                "<span class=\"color-blue address-action\" data-action=\"edit\" data-id=\""+ua.id+"\" data-type=\""+type+"\">Modifier cette adresse</span><br />"+
                (type == type_ba || uac > 1 ?
                  "<span class=\"color-blue address-action\" data-action=\"remove\" data-id=\""+ua.id+"\" data-type=\""+type+"\">Supprimer</span>" :
                  ""
                )+
              "</li>");
          }
          $ul.closest(".address-type-block").find(".create-address-link").css("visibility", uac < HN.TC.CLIENT_MAX_ADDRESS_BY_TYPE ? "visible" : "hidden");
        }
      };

      $("#delivery-address-list, #billing-address-list").on("click", ".address-action", function(){
        var $this = $(this);
        switch ($this.data("action")) {
          case "edit":
            HN.TC.showEditAddressForm($this.data("type"), $this.data("id"));
            break;
          case "remove":
            if (confirm("Souhaitez-vous vraiment supprimer cette adresse ?"))
              HN.TC.doClientAddressAjaxRequest({ action: "remove", type: $this.data("type"), id: $this.data("id") }, HN.TC.showAddressList, true);
            break;
        }

      });

      HN.TC.showAddressList();

    } else if (cur_place[1] == "order-step2" || cur_place[1] == "estimate-step2") {

      var $dab = $("#cart-delivery-address-block"), // delivery address block
          $bab = $("#cart-billing-address-block"); // delivery address block

      HN.TC.processClientAddressAjaxResponse = function(data, response){
        if (data.action == "create") {
          if (response.address) {
            if (response.address.type_adresse == type_da) {
              delivery_address_id = response.address.id;
              // no billing address list -> set the billing address id to the new delivery one
              if (addressesByType[type_ba]['length'] == 0)
                billing_address_id = delivery_address_id;
            } else if (response.address.type_adresse == type_ba) {
              billing_address_id = response.address.id;
            }
            HN.TC.showCartAddresses();
          } else {
            $db_msg.html("Erreur inattendue").dialog("open");
          }
        } else {
          HN.TC.showCartAddresses();
        }
      };

      HN.TC.showCartAddresses = function(){
        for (var type in addressesByType) {
          var $ul, $select, caid;
          switch (parseInt(type)) {
            case type_da:
              $ul = $dab.find(".address-infos ul");
              $sel_b = $dab.find(".address-selection");
              $sel = $sel_b.find("select");
              $cal = $dab.find(".create-address-link");
              caid = delivery_address_id;
              break;
            case type_ba:
              $ul = $bab.find(".address-infos ul");
              $sel_b = $bab.find(".address-selection");
              $sel = $sel_b.find("select");
              $cal = $bab.find(".create-address-link");
              caid = billing_address_id;
              break;
          }

          // show current address infos
          HN.TC.showCartAddressInfos($ul, caid);

          // fill address selection options or hide it if there is nothing
          var uac = addressesByType[type]['length'], id, html = "";
          for (id in addressesByType[type]['list']) {
            var ua = addressesByType[type]['list'][id];
              html += "<option value=\""+id+"\">"+ua.nom_adresse+"</option>";
          }
          $sel.html(html).val(caid); // val() does not trigger change()
          $sel_b[uac > 1 ? "show" : "hide"]();

          // show/hide create address link
          $cal[uac < HN.TC.CLIENT_MAX_ADDRESS_BY_TYPE ? "show" : "hide"]();

        }
      };

      HN.TC.showCartAddressInfos = function($ul, caid){
        var ca = addressesById[caid];
        var titreListe = HN.TC.CustomerTitleList();
        if (ca) {
          $ul.html(
            "<li>"+(typeof ca.titre!='undefined'?titreListe[ca.titre]:'')+" "+ca.prenom+" "+ca.nom+"</li>"+
            "<li>"+ca.societe+"</li>"+
            "<li>"+ca.adresse+"</li>"+
            "<li>"+ca.complement+" "+ca.cp+" "+ca.ville+"</li>"+
            "<li class=\"delivery-country\">"+ca.pays+"</li>"+
            "<li>Tél: "+ca.tel1+"</li>"+
            "<li>Fax: "+ca.fax1+"</li>"+
            "<li><i>Infos supplémentaires:</i><br />"+ca.infos_sup+"</li>"
          );
        }
      };

      // by default, we consider that a type != than a billing one is a delivery one (avoid managing useless exceptions)
      $("#cart-addresses-block").find(".infos-resume").each(function(){
        var type = $(this).data("type");
        $(this)
          .find(".address-infos .address-action").on("click", function(){
            var caid = type != type_ba ? delivery_address_id : billing_address_id;
            switch ($(this).data("action")) {
              case "edit":
                HN.TC.showEditAddressForm(type, caid);
                break;
            }
          }).end()
          .find(".address-selection select").on("change keyup", function(){
            var $ab = type != type_ba ? $dab : $bab,
                caid = $(this).val();
            if (type == type_da) {
              delivery_address_id = caid;
              // no billing address list -> also set the billing address when the delivery one is changed
              if (addressesByType[type_ba]['length'] == 0) {
                billing_address_id = caid;
                $ab = $ab.add($bab);
              }
            } else if (type == type_ba) {
              billing_address_id = caid;
            }
            HN.TC.showCartAddressInfos($ab.find(".address-infos ul"), caid);
          }).end()
          .find(".create-address-link .address-action").on("click", function(){
            HN.TC.showEditAddressForm(type)
          });
      });

      HN.TC.showCartAddresses();

    }
  }

  // Order step 2 and Estimate step 2
  $(".btn-validate-step2-data").click(function(){
    if ($("#cart-delivery-address-block .delivery-country").text() != "FRANCE") {
      alert('Vous ne pouvez pas choisir une adresse de livraison hors de France.');
    } else if (!requestSent) {
      $.ajax({
        data: { action: "setAdresses", data: [delivery_address_id, billing_address_id], cartID: $cartID },
        dataType: "json",
        type: "GET",
        url: HN.TC.Locals.AJAXCartManager
      }).done(function(data){
        if (data.data) {
          document.location.href = HN.TC.Locals.OrderURL+(cur_place[0] == "order-step" ? "order-step3.html" : "estimate-step3.html");
        } else {
          if (data.error) {
            alert("Error : " + data.error);
          }
          if (data.warning) {
            alert("Warning : " + data.warning);
          }
          requestSent = false;
        }
      }).fail(function(jqXHR, textStatus, errorThrown){
        alert(textStatus+" "+errorThrown);
        requestSent = false;
      });

      requestSent = true;
    }
  });

  // show dialog box depending on the hash
  $(window).hashchange(function(){
    var hashParts = location.hash.substring(1).split("|");
    for (var k=0; k< hashParts.length; k++) {
      var parts = hashParts[k].split("_"),
          cmd = parts[0],
          args = parts[1];
      switch (cmd) {
        case "account-contact-dialog":
          HN.TC.accountContactDialog.apply(this, args.split(","));
          break;
      }
    }
  }).hashchange();

}

HN.TC.Init = function () {
	var requestSent = false;
	var urlInfos = new HN.URLinfos();
	var $pdt_sheet = $("#product-sheet");


	/** Global functionalities
	***************************************/

	// Every Checkboxes and Radio Groups
	$("#body").find("div.js-checkbox").append("<div class=\"js-check-mark\"></div>").click(function(){
		$(this).toggleClass("checked").find("input[type=hidden]").val($(this).hasClass("checked")?1:0);
	}).each(function(){if ($(this).find("input[type=hidden]").val() > 0) $(this).addClass("checked");});
	$("#body").find("div.js-radio").append("<div class=\"js-check-mark\"></div>").click(function(){
		var $input = $(this).find("input[type=radio]");
		$input.attr("checked", "checked");
		$("input[type=radio][name='"+$input.attr("name")+"']").parent().removeClass("checked");
		$(this).addClass("checked");
	}).each(function(){if ($(this).find("input[type=radio]:checked").length > 0) $(this).addClass("checked");});

  // Mini Stores Carrousel
  var $mainCarrousel = $("div.mini-stores-carrousel div.mask-mini-stores"),
      $mainCarrouselItems = $mainCarrousel.find(".items li"),
      mcItemCount = $mainCarrouselItems.length;

  if (mcItemCount) { // carrousel is present

    // preparing the nav links
    var $mainCarrouselLinks = $("#mini-stores-carrousel-links"),
        nbrLinks = 0;
    $mainCarrouselItems.each(function(index){
      $mainCarrouselLinks.append('<div class="carrouselLinkToItem '+(index?'carrouselUnactiveDot':'carrouselActiveDot')+'" data-mini-store-id="'+(index)+'"><div class="carrouselLinkFrame"></div></div>');
      nbrLinks++;
    });
    $mainCarrouselLinks.css({'width': (nbrLinks*31)+'px'});
    var $mainCarrouselLinkList = $mainCarrouselLinks.find(".carrouselLinkToItem");

    // api
    $mainCarrousel.scrollable({
      loop: true,
      size: mcItemCount,
      speed: 300
    }).circular().autoscroll({interval: 2500});

    var $mainCarrouselApi = $mainCarrousel.scrollable();

    $mainCarrouselLinkList.each(function(){
      $(this).click(function(){
        $mainCarrouselApi.seekTo($(this).data("mini-store-id"));
        $mainCarrouselApi.pause(6000);
      });
    });

    $mainCarrouselApi.onSeek(function(e, index) {
      var realIndex = index>mcItemCount ? index%mcItemCount: index; // jquery scrollable provide the li index including the "cloned" ones
      $mainCarrouselLinkList.removeClass("carrouselActiveDot").addClass("carrouselUnactiveDot");
      $mainCarrouselLinkList.filter("[data-mini-store-id='"+realIndex+"']").removeClass('carrouselUnactiveDot').addClass('carrouselActiveDot');
    });
  }

  // They trust us Carrousel
  var $TTUCarrousel = $("div.they-trust-us-carrousel div.mask-they-trust-us");
  $TTUCarrousel.scrollable({
		loop: true,
		size: 5,
		speed: 300,
		prev: "div.scroll-l",
		next: "div.scroll-r"
	}).circular();

	$("#body div.pdt-hb, #body td.pdt-vb, #body table.pdt-db > tbody tr, #body table.cat1-cat2-list > tbody td, #body table.cat2-cat3-list > tbody td, div.half-width-block").each(function(){
		var href = $("a", this).attr("href");

		$(".picture > img, span.see-link, button.see-link",this).click(function(){document.location.href = href;}).css("cursor", "pointer");
	})

  HN.TC.GVars.adv_help_timeout;
  HN.TC.GVars.adv_help = document.createElement("div");
  HN.TC.GVars.adv_help.className = "adv-helper";
  HN.TC.GVars.adv_help.innerHTML = "Toute demande relative à ce produit sera directement envoyée à notre fabriquant partenaire, qui reprendra contact avec vous sous un délai moyen constaté de 24/48H.";
  $("#body")
    .find("span.see-link").live({
      mouseenter: function(){$(this).toggleClass("u");},
      mouseleave: function(){$(this).toggleClass("u");}
    })
    .end()
    .find("span.what-is-it").live({
      mouseenter: function(){
        var $me = $(this);
        clearTimeout(HN.TC.GVars.adv_help_timeout);
        HN.TC.GVars.adv_help.style.left = ($me.position().left-45)+"px";
        HN.TC.GVars.adv_help.style.top = ($me.position().top-72)+"px";
        $me.toggleClass("u").after(HN.TC.GVars.adv_help);
      },
      mouseleave: function(){
        var $me = $(this);
        $me.toggleClass("u");
        HN.TC.GVars.adv_help_timeout = setTimeout(function(){$(HN.TC.GVars.adv_help).remove();}, 1000);
      }
    });

  $("body a[class^='btn-esti-ask'], a[class='ask-estimate-link']").live("click", function(){
    var parts = this.href.substring(this.href.lastIndexOf("/")+1, this.href.length).split(":")[1].split("-");
    var catID = parts[0];
    var pdtID = parts[1];
    var idTC = parts[2];
    var qty = parts.length > 3 ? parts[3] : 1;

    var advType = $(this).data("adv-type") == 1 ? '-f' : '-a';

    /*if ($pdt_sheet.length > 0) window.open(HN.TC.Locals.URL + "lead"+advType+".html?pdtID="+pdtID+"&catID="+catID, "_blank");
    else */
    document.location.href = HN.TC.Locals.URL + "lead"+advType+".html?pdtID="+pdtID+"&catID="+catID;
    return false;
  });

  // product page Carrousel
  var $productPageCarrousel = $("div.product-page-carrousel div.grey-block");

  $productPageCarrousel.scrollable({
          loop: false,
          size: (3),
          speed: 300,
          prev: "div.scroll-l-product-page",
          next: "div.scroll-r-product-page"
  });
  $("div.product-page-carrousel div.grey-block ul li img").mouseenter(function(){
    var $img = $(this),
        $li = $img.closest("li"),
        $card_img = $(".product-page-picture img"),
        index = $li.index(),
        src = $img.attr("src");
    $card_img.removeClass("video").data("index", index);
    if (src == HN.TC.Locals.RessourcesURL+"images/picto-video.png") {
      $card_img.addClass("video");
    } else {
      $card_img.fadeOut(300, function(){ $card_img.attr("src", src).fadeIn(300); });
    }

  });
  $("div.product-page-carrousel div.grey-block ul.items li img[src='"+HN.TC.Locals.RessourcesURL+"images/picto-video.png']").click(function(){
    $('.product-page-picture img').click();
      var nextLi = $(this).parent().parent().find('li')[1];
      src = $(nextLi).attr('src');
  })

  // Add to cart ajax layer
  var cart_add_overlay_bg_black = document.createElement("div");
  cart_add_overlay_bg_black.id = "cart-add-overlay-bg-black";
  var cart_add_overlay = document.createElement("div");
  cart_add_overlay.id = "cart-add-overlay";
    var cao_oc = document.createElement("div");
    cao_oc.className = "outer-content";
    cart_add_overlay.appendChild(cao_oc);
      var cao_ic = document.createElement("div");
      cao_ic.className = "inner-content";
      cao_oc.appendChild(cao_ic);
        var cao_title = document.createElement("div");
        cao_title.className = "title";
        cao_title.innerHTML = "Vous avez ajouté le produit suivant à votre panier:";
        cao_ic.appendChild(cao_title);
        var cao_desc = document.createElement("div");
        cao_desc.className = "desc";
        cao_ic.appendChild(cao_desc);
      // Resume shopping button
        var cao_btn_resume = document.createElement("div");
        cao_btn_resume.innerHTML = '';
        cao_btn_resume.className = "btn-resume-shopping";
        cao_btn_resume.onclick = function(){if(typeof (_gaq) != 'undefined'){_gaq.push(['_trackEvent', 'Pop up panier', 'Mise au panier', 'Retour catalogue']);}location.reload();};//$(cart_add_overlay).hide();$(cart_add_overlay_bg_black).hide();
        cao_ic.appendChild(cao_btn_resume);
      // Go to Cart button
        var cao_btn_cart = document.createElement("div");
        cao_btn_cart.innerHTML = '';
        cao_btn_cart.className = "btn-goto-cart";
        cao_btn_cart.onclick = function(){if(typeof (_gaq) != 'undefined'){_gaq.push(['_trackEvent', 'Pop up panier', 'Mise au panier', 'Accès panier'])};document.location.href = HN.TC.Locals.URL + "panier.html";};
        cao_ic.appendChild(cao_btn_cart);

        var zerodiv = document.createElement("div");
        zerodiv.className = "zero";
        cao_ic.appendChild(zerodiv);

  $("body").append(cart_add_overlay).append(cart_add_overlay_bg_black);

  // show Cart dialog
  $("body").on("click", "a[class^='btn-cart-add-big'], a.btn-cart-add-pink", function(){
    $('#cart-add-product-dialog').html("");
    if ($("#product-refs").length > 0) {
      $("#product-refs").clone(true).appendTo('#cart-add-product-dialog');
      if(typeof (_gaq) != 'undefined'){
        $('#cart-add-product-dialog .btn-cart-add-small-btn').attr('onClick', "_gaq.push(['_trackEvent', 'Fiche produit', 'Mise au panier', 'Bouton principal - Choix du modèle']);");
      }
      $("#cart-add-product-dialog").dialog("open");
    } else {
      var idProduct = $(this).attr('href').match(/\-[0-9]{1,10}\-/g);
      idProduct = idProduct[0].replace(/\-/g, '');
      $.ajax({
        data: {"actions":[{"action":"get_pdt_refs","pdtId":idProduct}]},
        dataType: "json",
        type: "POST",
        url: HN.TC.Locals.AJAXGetProductsInfos,
        error: function (XMLHttpRequest, textStatus, errorThrown) {
        },
        success: function (data, textStatus) {
          if(data.error){
            $('#cart-add-product-dialog').html(data.error);
            $('#cart-add-product-dialog').dialog('open');
          } else if(data.data && data.data.pdt_infos) {
            var d = data.data,
                html = '';
            html +=
            '<div class="cat3-prod-list-pic cart-list fl">'+
              '<div class="picture fl">'+
                '<div class="cat3-picture-border">'+
                  '<img class="vmaib" alt="'+d.pdt_infos[0].infos.name+'" src="'+d.pdt_infos[0].pics[0].thumb_small+'"><div class="vsma"></div>'+
                '</div>'+
              '</div>'+
              '<div class="fl cat3-prod-list-infos">'+
                '<div class="blue-small-title">'+d.pdt_infos[0].infos.name+'</div>'+
                '<span>'+d.pdt_infos[0].infos.fastdesc+'</span>'+
               (d.pdt_infos[0].infos.adv_delivery_time ?
                '<p class="cat3-checked-line">Livraison : '+d.pdt_infos[0].infos.adv_delivery_time+'</p>' : ''
               ) +
               (d.pdt_infos[0].infos.shipping_fee ?
                '<p class="cat3-checked-line">Frais de port : '+d.pdt_infos[0].infos.shipping_fee+'</p>' : ''
               ) +
              '</div>'+
              '<div class="zero"></div>'+
            '</div>'+
            '<div class="zero"></div>'+
            '<div id="product-refs" class="product-page-cart-table cart-table">'+
              '<div class="display-infos">'+
                '<table cellspacing="0" cellpadding="0">'+
                  '<thead>'+
                    '<tr>'+
                      '<th>Réf. TC</th>'+
                      '<th>Libellé</th>'+
                      '<th>'+d.custom_cols.join('</th><th>')+'</th>'+
                     (!d.pdt_set_as_estimate ?
                      '<th>Prix HT</th>'+
                      '<th>Quantité</th>'+
                      '<th>Ajouter au panier</th>' : ''
                     ) +
                    '</tr>'+
                  '</thead>'+
                  '<tbody>';
                  for (var a=0; a<d.refs.length; a++) {
                    html +=
                    '<tr>'+
                      '<td class="first">'+d.refs[a]['id']+'</td>'+
                      '<td>'+d.refs[a]['label']+'</td>'+
                      '<td>'+d.refs[a]['content'].join('</td><td>')+'</td>'+
                     (!d.pdt_set_as_estimate ?
                      '<td class="price">'+
                        d.refs[a]['price']+' €'+
                        (d.refs[a]['ecotax'] > 0 ? '<small>dont éco part : '+d.refs[a]['ecotax']+' €</small>' : '') +
                      '</td>'+
                      '<td class="quantity"><div class="vmaib"><div class="sub"></div><input type="text" name="qty" value="1"/><div class="add"></div></div></td>'+
                      '<td class="cart-add"><a href="'+d.refs[a]['cart_add_url']+'" class="btn-cart-add-small-btn vmaib" title="Ajouter au panier"></a></td>' : ''
                     ) +
                    '</tr>';
                  }
              html +=
                  '</tbody>'+
                '</table>'+
              '</div>'+
              '<div class="mobile-infos">'+
                '<table>'+
                  '<thead>'+
                    '<tr>'+
                      '<th>Infos réf. produit</th>'+
                     (!d.pdt_set_as_estimate ?
                      '<th>Qté</th>'+
                      '<th></th>' : ''
                     ) +
                    '</tr>'+
                  '</thead>'+
                  '<tbody>';
                  for (var a=0; a<d.refs.length; a++) {
                    html +=
                    '<tr>'+
                      '<td class="infos">'+
                        '<ul>'+
                          '<li><div class="label">Référence :</div><div class="text">'+d.refs[a]['id']+'</div></li>'+
                          '<li><div class="label">Libellé :</div><div class="text">'+d.refs[a]['label']+'</div></li>';
                        for (var ci=0; ci<d.refs[a]['content'].length; ci++) {
                          html +=
                          '<li><div class="label">'+d.custom_cols[ci]+'</div><div class="text">'+d.refs[a]['content'][ci]+'</div></li>';
                        }
                        html +=
                         (!d.pdt_set_as_estimate ?
                          '<li class="price"><div class="label">Prix :</div><div class="text">'+d.refs[a]['price']+' €</div></li>' : ''
                         ) +
                        '</ul>'+
                      '</td>'+
                     (!d.pdt_set_as_estimate ?
                      '<td class="quantity"><input type="text" name="qty" value="1"/></td>'+
                      '<td class="cart-add"><a href="'+d.refs[a]['cart_add_url']+'" class="btn-cart-add-small-btn vmaib" title="Ajouter au panier"></a></td>' : ''
                     ) +
                    '</tr>';
                  }
                  '</tbody>'+
                '</table>'+
              '</div>'+
              '<div class="zero"></div>'+
            '</div>';

            $('#cart-add-product-dialog').html(html).dialog("open");

            return false;
          }
        }
      });

    }
    $('#cat3-show-product-infos-dialog').dialog('close');

    return false;
  });

  // Add to Cart
  var $btn_cart_add_small = $("body a[class^='btn-cart-add-small']");
  $btn_cart_add_small.live('click', function(){
    var parts = this.href.substring(this.href.lastIndexOf("/")+1, this.href.length).split(":")[1].split("-");
    var catID = parts[0];
    var pdtID = parts[1];
    var idTC = parts[2];
    var qty = parts.length > 3 ? parts[3] : 1;
    var ttcPrice = HN.TC.GVars.showTtcPrice == true ? '&ttcPrice=true': '';

    $.ajax({
      data: "action=add&pdtID="+pdtID+"&idTC="+idTC+"&catID="+catID+"&qty="+qty+ttcPrice,
      dataType: "json",
      error: function (XMLHttpRequest, textStatus, errorThrown) {
        alert(textStatus+" "+errorThrown);
      },
      success: function (_data, textStatus) {
        if (_data.data) {
          var data = _data.data;
          data["cart_item_count"] = parseInt(data["cart_item_count"]);
          data["quantity"] = parseInt(data["quantity"]);
          data["price"] = parseFloat(data["price"]);

          $("#header-cart-item-count").html(data["cart_item_count"] + " article"+(data["cart_item_count"] > 1 ? "s" : ""));
          var html =
            "<div class=\"account-order-table\">"+
              "<div class=\"display-infos\">"+
                "<table>"+
                  "<thead>"+
                    "<tr>"+
                      "<th colspan=\"2\">Produit - Désignation</th>"+
                      "<th>Référence</th>"+
                      "<th>Prix HT</th>"+
                      "<th>Qté</th>"+
                      "<th>Prix total "+(ttcPrice?'TTC':'HT')+"</th>"+
                    "</tr>"+
                  "</thead>"+
                  "<tbody>"+
                  "<tr class=\"item-line\">"+
                    "<td><img src=\""+data["pic_url"]+"\" alt=\""+data["label"]+"\" /></td>"+
                    "<td class=\"designation\">"+data["cart_desc"]+"</td>"+
                    "<td>"+data["idTC"]+"</td>"+
                    "<td>"+data["price"]+" €</td>"+
                    "<td>"+data["quantity"]+"</td>"+
                    "<td>"+data["total_price"].toFixed(2)+" €</td>"+
                  "</tr>"+
                  "<tr class=\"comment-line\">"+
                    "<td class=\"comment\" colspan=\"6\"><input type=\"text\" class=\"comment\" value=\"\"/></td>"+
                  "</tr>"+
                  "</tbody>"+
                "</table>"+
              "</div>"+
              "<div class=\"mobile-infos\">"+
                "<table>"+
                  "<thead>"+
                    "<tr>"+
                      "<th>Produit</th>"+
                      "<th>Qté</th>"+
                      "<th>Total "+(ttcPrice?'TTC':'HT')+"</th>"+
                    "</tr>"+
                  "</thead>"+
                  "<tbody>"+
                  "<tr>"+
                    "<td class=\"infos\">"+
                      "<div class=\"clearfix\">"+
                        "<img src=\""+data["pic_url"]+"\" alt=\""+data["label"]+"\" />"+
                        data["cart_desc"]+
                      "</div>"+
                      "<ul class=\"others clearfix\">"+
                        "<li><div class=\"label\">Référence :</div><div class=\"text\">"+data["idTC"]+"</div></li>"+
                        "<li><div class=\"label\">Prix U. :</div><div class=\"text\">"+data["price"].toFixed(2)+" €</div></li>"+
                      "</ul>"+
                    "</td>"+
                    "<td class=\"quantity\">"+data["quantity"]+"</td>"+
                    "<td class=\"total\">"+data["total_price"].toFixed(2)+" €</td>"+
                  "</tr>"+
                  "<tr class=\"comment-line\">"+
                    "<td class=\"comment\" colspan=\"3\"><input type=\"text\" class=\"comment\" value=\"\"/></td>"+
                  "</tr>"+
                  "</tbody>"+
                "</table>"+
              "</div>"+
            "</div>"+
            "<div class=\"zero\"></div>";
          $(cart_add_overlay).find(".desc").html(html);
          var $input_comments = $(cart_add_overlay).find(".desc td input.comment");

          HN.TC.addPlaceholder($input_comments, "Facultatif : cliquez ici pour rentrer si besoin la finition, couleur, RAL... désirés", true);
          $input_comments.on("blur", function(){
            $.ajax({
              data: {
                action: "mod",
                cartID: cartID,
                idTC: data["idTC"],
                comment: $(this).val()
              },
              dataType: "json",
              type: "POST",
              url: HN.TC.Locals.AJAXCartManager
            }).done(function(data){
              if (!data.data) {
                if (data.error) {
                  alert("Error : " + data.error);
                } else if (data.warning) {
                  alert("Warning : " + data.warning);
                }
              }
            });
          });

          $('#cart-add-product-dialog').dialog('close');
          $('#cat3-show-product-infos-dialog').dialog('close');

          $(cart_add_overlay_bg_black).show();
          $(cart_add_overlay).show();

				}
				else {
					if (_data.error) {
						alert("Error : " + _data.error);
					}
					if (_data.warning) {
						alert("Warning : " + _data.warning);
					}
				}
			},
			type: "GET",
			url: HN.TC.Locals.AJAXCartManager
		});

		return false;
	});

	// Product Sheet
	if ($pdt_sheet.length > 0) {

		// Picture functionalities
		var $pdt_sheet_pic_block = $pdt_sheet.find("div.picture-block");
		var loading_img = HN.TC.Locals.RessourcesURL+"images/wait.gif";

		$pdt_sheet_pic_block.find("div.list a").click(function(){
			var me = this;
			if (this.className == "selected") return false;
			$pdt_sheet_pic_block.find("div.picture img").attr("src", this.href);

			$pdt_sheet_pic_block.find("div.list a").removeClass("selected");
			this.className = "selected";

			return false;
		});
		$pdt_sheet_pic_block.find("div.list a:first").click();

		$pdt_sheet_pic_block.find("div.picture img").click(function(){
			pic_zoom_overlay.width = "";
			pic_zoom_overlay.height = "";
			$(pzo_img).hide();
			$(pzo_loading_img).show();
			if (pzo_img.src == loading_img) {
				$(pic_zoom_overlay).css({
					left: ($(window).width()-(490+10))/2,
					top: 200
				});
			}
			$(pic_zoom_overlay).show();
			var pic_url_parts = this.src.split("/");
			pic_url_parts[pic_url_parts.length-2] = "zoom";
			var pic_url = pic_url_parts.join("/");

			var objImagePreloader = new Image();
			objImagePreloader.onload = function() {
				pzo_img.src = pic_url;
				$(pic_zoom_overlay).animate({
					left: ($(window).width()-(objImagePreloader.width+10))/2,
					top: 200,
					width: objImagePreloader.width,
					height: objImagePreloader.height
				},500,function() {
					$(pzo_loading_img).hide();
					$(pzo_img).width(objImagePreloader.width).height(objImagePreloader.height).show();
				});
				objImagePreloader.onload = function(){};
			};
			objImagePreloader.src = pic_url;
		});

		$pdt_sheet_pic_block.find("div.zoom").click(function(){
			$pdt_sheet_pic_block.find("div.picture img").click();
		});


		// Leads Functionalities
		var $pdt_sheet_infos_actions = $pdt_sheet.find("div.infos div.actions");
		var pdtID = $pdt_sheet.find("input[name='pdtID']").val();
		$pdt_sheet_infos_actions.find(".get-infos").click(function(){document.location.href = HN.TC.Locals.URL+"lead.html?pdtID="+pdtID+"&type=1";});
		$pdt_sheet_infos_actions.find(".ask-callback").click(function(){document.location.href = HN.TC.Locals.URL+"lead.html?pdtID="+pdtID+"&type=2";});
		//$pdt_sheet_infos_actions.find(".ask-estimate").click(function(){document.location.href = HN.TC.Locals.URL+"lead.html?pdtID="+pdtID+"&type=3";});
		$pdt_sheet_infos_actions.find(".make-appointment").click(function(){document.location.href = HN.TC.Locals.URL+"lead.html?pdtID="+pdtID+"&type=4";});


		// Send Product to Colleague functionalities
		var spc_db = document.createElement("div");
		spc_db.className = "spc-db";

		var spc_db_bg = document.createElement("div");
		spc_db_bg.className = "spc-db-bg";
		spc_db.appendChild(spc_db_bg);

		var umail_label = document.createElement("label");
		umail_label.innerHTML = "Votre E-mail : ";
		spc_db_bg.appendChild(umail_label);
		var umail = document.createElement("input");
		umail.type = "text";
		umail.size = "50";
		umail.maxlength = "255";
		umail.value = HN.TC.GVars.userEmail;
		spc_db_bg.appendChild(umail);
		spc_db_bg.appendChild(document.createElement("br"));
		spc_db_bg.appendChild(document.createTextNode("Envoyer cette fiche produit à un collègue :"));
		spc_db_bg.appendChild(document.createElement("br"));

		var fmail = [];
		for (var i = 0; i < 5 ; i++) {
			fmail_label = document.createElement("label");
			fmail_label.innerHTML = "E-mail "+(i+1)+" : ";
			spc_db_bg.appendChild(fmail_label);
			fmail[i] = document.createElement("input");
			fmail[i].type = "text";
			fmail[i].size = "50";
			fmail[i].maxlength = "255";
			spc_db_bg.appendChild(fmail[i]);
			spc_db_bg.appendChild(document.createElement("br"));
		}

		var send = document.createElement("input");
		send.type = "button";
		send.value = "Envoyer";
		send.className = "send";
		send.onclick = function() {
			var fmails_str = "";
			for (var i = 0; i < fmail.length; i++) {
				fmails_str += "&fmail"+(i+1)+"="+fmail[i].value;
			}

			$.ajax({
				data: "action=sendProduct&umail="+umail.value+fmails_str+"&pdtID="+HN.TC.GVars.pdtID+"&catID="+HN.TC.GVars.catID,
				dataType: "json",
				error: function (XMLHttpRequest, textStatus, errorThrown) {
					alert(textStatus+" "+errorThrown);
				},
				success: function (data, textStatus) {
					if (!data.data) {
						if (data.error) {
							alert("Error : " + data.error);
						}
						if (data.warning) {
							alert("Warning : " + data.warning);
						}
					}
					else {
						alert(data.data);
						spc.Hide();
					}
				},
				type: "GET",
				url: HN.TC.Locals.AJAXSendColleagues
			});
		};
		spc_db_bg.appendChild(send);

		$("#body").append(spc_db);

		var spc = new HN.Window(spc_db);
		spc.setTitleText("Envoyer une fiche produit à un collègue");
		spc.setMovable(true);
		spc.showCancelButton(true);
		spc.setValidFct( function() {spc.Hide();} );
		spc.Build();

		$pdt_sheet.find("a.option-send-friend").on("click", function(){
      spc.Show();
      return false;
    });
		$pdt_sheet.find(".option-print").on("click", function(){
      if (window._gaq)
        _gaq.push(['_trackEvent', 'Fiche produit', 'Clic', 'Impression fiche produit']);
      window.print();
      return false;
    });

		$("div.product div.pdt-docs").find("a").click(function(){
			window.location.href = $(this).data('docLocation');
			return false;
		});

	}

  // Main Contact Form
	$("div.main-contact-form .btn-send-message").click(function(){
		if (!requestSent) {
			requestSent = true;
			document.main_contact_form.submit();
		}
	});

	// Add/Sub quantities buttons in cart tables
  $("div.cart-table td.quantity .add, div.cart-table td.quantity .sub").live("click", function(){
    var $td = $(this).closest("td"),
        $input = $td.find("input"),
        val = parseInt($input.val()),
        $btn = $td.parent().find("a[class^='btn-cart-add']");
    val = val + ($(this).hasClass("add") ? 1 : (val > 1 ? -1 : 0));
    $input.val(val);
    if ($btn.length)
      $btn.attr("href",$btn.attr("href").replace(/(^\D*)(\d+-\d+-\d+)(-\d+)?/,"$1$2-"+val));

    $input.live('blur',function(){
      var val2 = $input.val();
      if (/^\s*\d+\s*$/.test(val2)) {
        val = parseInt(val2);
        if (val < 1) val = 1;
      }
      $input.val(val);
      if ($btn.length)
        $btn.attr("href",$btn.attr("href").replace(/(^\D*)(\d+-\d+-\d+)(-\d+)?/,"$1$2-"+val));
    });
  });
  $("div.cart-table").on("blur","td.quantity input", function(){
    var $input = $(this),
        val = $input.val(),
        $btn =  $input.closest("td").parent().find("a[class^='btn-cart-add']");
    if (/^\s*\d+\s*$/.test(val)) {
      val = parseInt(val);
      if (val < 1)
        val = 1;
    } else {
      val = 1;
    }
    $input.val(val);
    if ($btn.length)
      $btn.attr("href",$btn.attr("href").replace(/(^\D*)(\d+-\d+-\d+)(-\d+)?/,"$1$2-"+val));
  });

	// Subsequent functionalities require a form named "panier"
	var $cart_form = $("form[name='panier']");
	if ($cart_form.length > 0) {

		var cart_form = $cart_form.get(0);
		var idsList = [];
    $cart_form.find("input[name='pdt']").each(function(){
      var ids = $(this).val().split("-");
      idsList.push({id: ids[0], idTC: ids[1]});
    });

		var cartID = cart_form.cartID.value;

		// Delete item buttons
		$("div.cart-table tr:not(.insurance) td .delete", cart_form).each(function(i){
      $(this).click(function(){
        if (!requestSent) {
          cart_form.todo.value = "delpdt_"+idsList[i].id+"-"+idsList[i].idTC;
          cart_form.submit();
          requestSent = true;
        }
      });
		});

    // insurance
    /*$cart_form.find("tr.insurance a._blank").click(function(){
      window.open(this.href,"ClicProtect","menubar=no, status=no, scrollbars=no, menubar=no, width=560, height=640");
      return false;
    });
    $("#cart-add-insurance").click(function(){
      if (!requestSent) {
        cart_form.todo.value = "addInsurance";
        cart_form.submit();
        requestSent = true;
        return false;
      }
    });
    $("#cart-del-insurance").click(function(){
      if (!requestSent) {
        cart_form.todo.value = "delInsurance";
        cart_form.submit();
        requestSent = true;
      }
    });*/

		// Comment AJAX mod
		$("div.cart-table").on("blur", "td input.comment", function(){
      var idTC = $(this).closest("tr").prev().find("input[name='pdt']").val().split("-")[1];
      $.ajax({
        data: {
          action: "mod",
          cartID: cartID,
          idTC: idTC,
          comment: $(this).val()
        },
        dataType: "json",
        type: "POST",
        url: HN.TC.Locals.AJAXCartManager
      }).done(function(data){
        if (!data.data) {
          if (data.error) {
            alert("Error : " + data.error);
          } else if (data.warning) {
            alert("Warning : " + data.warning);
          }
        }
      });
    });

    HN.TC.addPlaceholder($("div.cart-table td input.comment"), "Facultatif : cliquez ici pour rentrer si besoin la finition, couleur, RAL... désirés", true);

		// Recalculate button
		$("div.cart-table td.quantity a", cart_form).click(function(){


      cart_form.todo.value = "updqte";
      var itemQuantities = [];
	  /*****/
	  if($(".cart-mobile").css("display")=="none"){

		 //Web
		$(".cart-table .table-type-2 :first input[name='qty']").each(function(i){
			var qty = parseInt($(this).val());
			if (isNaN(qty))
			  return false;
			else
			  itemQuantities.push(idsList[i].idTC+"-"+qty);
		});
	  }else{

		$(".cart-mobile .cart-table input[name='qty']").each(function(i){
			var qty = parseInt($(this).val());
			if (isNaN(qty))
			  return false;
			else
			  itemQuantities.push(idsList[i].idTC+"-"+qty);
		});
	  }
	  /*****/
     /* $(".cart-table input[name='qty']").each(function(i){
        var qty = parseInt($(this).val());
        if (isNaN(qty))
          return false;
        else
          itemQuantities.push(idsList[i].idTC+"-"+qty);
      });*/
      if (itemQuantities.length) {
        var itemQuantitiesStr = itemQuantities.join("<_>");
        if($('input[type=hidden][name=updatestring]').length){
          $('input[type=hidden][name=updatestring]').val(itemQuantitiesStr);
        }else{
          $("form[name='panier']").append('<input type="hidden" name="updatestring" value="'+itemQuantitiesStr+'" />');
        }


        $.ajax({
          data: "todo=updqte&cartID="+cartID+"&qty="+itemQuantitiesStr,
          dataType: "json",
          error: function (XMLHttpRequest, textStatus, errorThrown) {
            alert(textStatus+" "+errorThrown);
          },
          success: function (data, textStatus) {

            if (!data.data) {
              if (data.error) {
                alert("Error : " + data.error);
              }
              if (data.warning) {
                alert("Warning : " + data.warning);
              }
            }else{
		     $(data.data.items).each(function(){

                var hidden = $('form[name=panier] div.cart-table table input[type=hidden][value$='+this.idTC+']');
                if(hidden.parent().text() == this.idTC){
                  hidden.closest('tr').find('td.quantity').next('td').text(this.sumHT.toFixed(2)+' €1');
                }

				document.location.href = "panier.html";

                $('table.cart-totals tr.stotal-ht td.amount').text(data.data.totalsCart.stotalHT.toFixed(2)+' €1');
                $('table.cart-totals tr.fdp td.amount').text((parseFloat(data.data.totalsCart.fdpHT).toFixed(2)+' €1'));
                $('table.cart-totals tr.total-ht td.amount').text(data.data.totalsCart.totalHT.toFixed(2)+' €1');
                $('table.cart-totals tr.tva td.amount').text(data.data.totalsCart.totalTVA.toFixed(2)+' €1');
                $('table.cart-totals tr.total-ttc td.amount').text(data.data.totalsCart.totalTTC.toFixed(2)+' €1');

			  })

              $('.cartConstraintsAdv, .cartConstraintsPdt').remove();
              if(data.data.notValidAdvList != '')
                HN.TC.notValidAdvListShow (data.data.notValidAdvList);
              if(data.data.notValidPdtList != '')
                HN.TC.notValidPdtListShow (data.data.notValidPdtList);
            }
          },
          type: "POST",
          url: HN.TC.Locals.AJAXCartManager
        });
        //requestSent = true;
      }
      return false;
		});

		// Make Order button
		$(".btn-order", cart_form).click(function(){
			if (!requestSent) {
				document.location.href = HN.TC.Locals.OrderURL + "order-step1.html?estimate=0";
				requestSent = true;
			}
		});

		// Make Estimate button
		$(".btn-estimate", cart_form).click(function(){
			if (!requestSent) {
				document.location.href = HN.TC.Locals.OrderURL + "estimate-step1.html?estimate=1";
				requestSent = true;
			}
		});

		// Constraint checks
        HN.TC.notValidPdtListShow = function(notValidPdtList){
		if (notValidPdtList) {
			for (pdtID in notValidPdtList) {
				var $tr1 = $cart_form.find("input[name='pdt'][value='"+notValidPdtList[pdtID]["idTC1"]+"']").parents("tr").css({'border-color': '#b00000'});
				var $trn = $cart_form.find("input[name='pdt'][value='"+notValidPdtList[pdtID]["idTCn"]+"']").parents("tr").next();
				while ($trn.next().hasClass("item-promotion") || $trn.next().hasClass("item-discount")) $trn = $trn.next();

				var td1 = $tr1.find("td:first");
				var tdn = $trn.find("td:last");
				var x1 = Math.floor(td1.position().left);
				var y1 = Math.floor(td1.position().top);
				var x2 = Math.floor(tdn.position().left + tdn.innerWidth());
				var y2 = Math.floor(tdn.position().top + tdn.innerHeight());

				var bt = document.createElement("div"), bl = document.createElement("div"), br = document.createElement("div"), bb = document.createElement("div");
				bt.setAttribute('class', 'cartConstraintsPdt');bl.setAttribute('class', 'cartConstraintsPdt');br.setAttribute('class', 'cartConstraintsPdt');bb.setAttribute('class', 'cartConstraintsPdt');
                                $(bt).css({position: "absolute", left: x1+2, top: y1+2, width: x2-x1-4, height: 2, backgroundColor: "#b00000"});
				$(bl).css({position: "absolute", left: x1+0, top: y1+2, width: 2, height: y2-y1+0, backgroundColor: "#b00000"});
				$(br).css({position: "absolute", left: x2-x1-2, top: y1+2, width: 2, height: y2-y1+0, backgroundColor: "#b00000"});
				$(bb).css({position: "absolute", left: x1+2, top: y2, width: x2-x1-4, height: 2, backgroundColor: "#b00000"});
				$cart_form.find("div.cart-table table").after(bt,bl,br,bb);

                                $trn.before('<tr class="item-min-adv cartConstraintsAdv"><td colspan="8">La quantité mimimum requise pour commander ce(s) produit(s) est de '+notValidAdvList[pdtID]["qty_min"]+' ('+notValidAdvList[pdtID]["qty"]+' actuellement)</td></tr>');

			}
		}
        }

	HN.TC.notValidAdvListShow = function(notValidAdvList){
		if (notValidAdvList) {
			for (advID in notValidAdvList) {
				var $tr1 = $cart_form.find("input[name='pdt'][value$='"+notValidAdvList[advID]["idTC1"]+"']").parents("tr");
				var $trn = $cart_form.find("input[name='pdt'][value$='"+notValidAdvList[advID]["idTCn"]+"']").parents("tr").next();
				while ($trn.next().hasClass("item-promotion") || $trn.next().hasClass("item-discount")) $trn = $trn.next();

				var td1 = $tr1.find("td:first");
				var tdn = $trn.find("td:last");
				var x1 = Math.floor(td1.position().left);
				var y1 = Math.floor(td1.position().top);
				var x2 = Math.floor(tdn.position().left + tdn.innerWidth());
				var y2 = Math.floor(tdn.position().top + tdn.innerHeight());

				var bt = document.createElement("div"), bl = document.createElement("div"), br = document.createElement("div"), bb = document.createElement("div");
                                bt.setAttribute('class', 'cartConstraintsAdv');bl.setAttribute('class', 'cartConstraintsAdv');br.setAttribute('class', 'cartConstraintsAdv');bb.setAttribute('class', 'cartConstraintsAdv');
				$(bt).css({position: "absolute", left: x1-1, top: y1-1, width: x2-x1+2, height: 2, backgroundColor: "#b00000"});
				$(bl).css({position: "absolute", left: x1-3, top: y1-1, width: 2, height: y2-y1+3, backgroundColor: "#b00000"});
                                $(br).css({position: "absolute", left: x2+1, top: y1-1, width: 2, height: y2-y1+3, backgroundColor: "#b00000"});
				$(bb).css({position: "absolute", left: x1-1, top: y2, width: x2-x1+2, height: 2, backgroundColor: "#b00000"});
				$cart_form.find("div.cart-table table").after(bt,bl,br,bb);

                                $trn.before('<tr class="item-min-adv cartConstraintsAdv"><td colspan="8">Le total de commande pour l\'ensemble des produits entourés en rouge doit être au minimum de<br />'+notValidAdvList[advID]['sum_min'].toFixed(2)+'€ HT ('+notValidAdvList[advID]['sum'].toFixed(2)+'€ HT actuellement)</td></tr>');

			}
		}
        }

      HN.TC.notValidAdvListShow(HN.TC.GVars.notValidAdvList);
      HN.TC.notValidPdtListShow(HN.TC.GVars.notValidPdtList);

    // promotion code toggle
    $("#toggle-promotion-code").on("click", function(){
      $(this).parent().toggleClass("promo-hidden");
    });

    HN.TC.questionsAnswers = function(type){ // panier
      var title = "",
          html = "",
          width = 840;
      switch (type) {
        case 'paiement':
          title = "Comment payer ma commande ?";
          html += "\
Nous acceptons les modes de paiement suivants :<br />\
<br />\
- Carte bancaire<br />\
- Chèque<br />\
- Virement<br />\
- Mandat administratif<br />\
<br />\
Le paiement par carte bancaire entraine le traitement immédiat de votre commande, c'est le moyen le plus rapide d'acheter sur le site.<br />\
<br />\
Si vous choisissez un règlement par chèque, il doit être envoyé à l'adresse suivante, libellé au nom de Techni-Contact, accompagné du bon de commande signé et tamponné :<br />\
<br />\
Techni-Contact / Service commandes<br />\
253 rue Gallieni<br />\
F-92774 BOULOGNE BILLANCOURT cedex<br />\
<br />\
Si vous choisissez un règlement par virement bancaire :<br />\
Il doit parvenir coordonnées aux bancaires suivantes accompagné d'un justificatif et du bon de commande signé et tamponné par fax au 01 83 62 36 12 ou par voie postale.<br />\
<br />\
RIB :<br />\
<br />\
BNP PARIBAS BOUCLE DE SEINE (01896)<br />\
Code Banque : 30004<br />\
Code Guichet : 01896<br />\
Numéro de Compte : 00010001645<br />\
Clé RIB : 13<br />\
Numéro de compte bancaire international (IBAN): FR76 3000 4018 9600 0100 0164 513<br />\
BIC (Bank Identification Code): BNPAFRPPGNV<br />\
A l'attention de le Société MDII<br />\
<br />\
Merci de préciser dans le motif votre numéro de commande<br />\
<br />";
          break;

        case 'mandat':
          title = "Les mandats administratifs sont il acceptés ?";
          html += "\
Oui, nous travaillons avec de nombreuses collectivités et nous acceptons les mandats administratifs.<br />\
<br />\
Les coordonnées bancaires de Techni-Contact sont les suivantes :<br />\
<br />\
BNP PARIBAS BOUCLE DE SEINE (01896)<br />\
Code Banque : 30004<br />\
Code Guichet : 01896<br />\
Numéro de Compte : 00010001645<br />\
Clé RIB : 13<br />\
Numéro de compte bancaire international (IBAN): FR76 3000 4018 9600 0100 0164 513<br />\
BIC (Bank Identification Code): BNPAFRPPGNV<br />\
A l'attention de le Société MDII<br />\
<br />\
Merci de préciser dans le motif votre numéro de commande<br />\
<br />";
          break;

        case 'livraison':
          title = "Livrez vous en Corse et à l'étanger ?";
          width = 500;
          html += "\
Les frais de port indiqués sur le site sont valables pour la France continentale.<br />\
Pour une livraison en Corse, DOM-TOM ou à l'étranger, il vous suffit de contacter l'un de nos experts au 01 55 60 29 29.<br />\
Il établira avec vous un devis personnalisé.<br />\
<br />\
<br />";
          break;

        case 'particulier':
          title = "Les particuliers peuvent il commander ?";
          width = 500;
          html += "\
Techni-Contact.com est un site de vente de produits professionnels à destination des professionnels.<br />\
Il est régit par la réglementation du commerce entre professionnels.<br />\
Les commandes de particuliers peuvent être acceptées sachant qu'ils sont informés que la livraison s'effectue en journée aux heures de bureau uniquement, sans plage horaire définie.<br />\
<br />";
          break;
      }
      html += "<div class=\"blue-close\"><span>[Fermer]</span></div>";
      $("#ui-dialog-title-q-a-dialog").text(title);
      $("#q-a-dialog").html(html).dialog("option", "width", width).dialog("open");
    }

    $("#q-a-dialog").dialog({
      width: 840,
      autoOpen: false,
      modal: true,
      draggable: false,
      resizable: false
    });

	}

  // Products save list
  var $btn_save_users_product_list = $("#body a[class^='btn-users-product-list'], #cat3-show-product-infos-dialog a[class^='btn-users-product-list']");
  $btn_save_users_product_list.live('click', function(){
    if ($('#cat3-show-product-infos-dialog').data("uiDialog"))
      $('#cat3-show-product-infos-dialog').dialog('close');
    var parts = this.href.substring(this.href.lastIndexOf("/")+1, this.href.length).split(":")[1].split("-");
    var action = parts[0];
    var pdtID = parts[1];

    $.ajax({
      data: "action="+action+"&productID="+pdtID,
      dataType: "json",
      error: function (XMLHttpRequest, textStatus, errorThrown) {
        alert(textStatus+" "+errorThrown);
      },
      success: function (_data, textStatus) {
        if (_data.data) {
          switch (_data.data.status) {
            case 'saved':
              if (!_data.data.logged) {
                var html =
                  "<p>Ce produit a été sauvegardé pour une durée de 48H.</p>"+
                  "<p>Votre liste de produits favoris est diponible sous le bloc &laquo Mon compte &raquo, en haut à droite de la page.</p>"+
                  "<p>Pour <strong>sauvegarder de façon permanente</strong> nous vous invitons à créer un <strong>compte gratuit</strong> ou à vous connecter à votre compte.</p>"+
                  "<div class=\"btn-sign-up\" onclick=\"$('#saved-products-list-dialog').dialog('close'); HN.TC.ShowCreateAccountForm(); \"></div>"+
                  "<a href=\""+HN.TC.Locals.AccountURL+"login.html\" class=\"btn-i-login\"></a>"+
                  "<div class=\"bottom\"><a href=\"\" onclick=\"$('#saved-products-list-dialog').dialog('close'); return false;\" class=\"blue-title\">[ Fermer ]</a></div>";
                $('#saved-products-list-dialog').html(html).dialog("open");
              }
              var url = _data.data.logged == 1 ? HN.TC.Locals.AccountURL+'saved-products-list.html' : HN.TC.Locals.URL+'liste-produits-sauvegardes.html';
              var link = '<div class="puce puce-9"></div><span class="color-green">Produit sauvegardé</span> <a href="'+url+'" class="color-blue">Voir liste</a>';
              $('.savedProductsListZone_'+pdtID).html(link);
              break;
            case 'removed':
              window.location.reload();
              break;
            case 'emptied':

              break;
          }
        }
        else {
          if (_data.error) {
            alert("Error : " + _data.error);
          }
          if (_data.warning) {
            alert("Warning : " + _data.warning);
          }
        }
      },
      type: "GET",
      url: HN.TC.Locals.AJAXProductsListManager
    });
    return false;
  });

  // product fancybox
  $(".product-page-picture img").on("click", function(){
      var url_list = [];
      for (var k=0; k<pic_url_list.length; k++)
        url_list.push(pic_url_list[(k+parseInt($(this).data("index")))%pic_url_list.length].zoom);
      $.fancybox.open(url_list, { padding : 0 });
  });

    // order steps fancybox
  /*$(".cart-table td[class!=quantity] a").on("click", function(e){
    e.preventDefault();
      var pic_url_list = $(".cart-table td[class!=quantity] a");
      var url_list = [];
      for (var k=0; k<pic_url_list.length; k++){
        var prodInfos = $(pic_url_list[k]).attr('href').split('-');
        url_list.push(HN.TC.Locals.RessourcesURL+'/images/produits/zoom/'+prodInfos[2]+'-1.jpg');
      }

      $.fancybox.open(url_list, { padding : 0 });
  });*/

	HN.png2alpha();

  $("#header-mobile-nav select").on("change", function(){
    location.href = $(this).val();
  });
  //$("#")
};


HN.TC.c3pva = {cat3Id: null, page: 1, sort: "relevant", filters: {}, filter_count: 0 }; // cat3 product's view arguments
HN.TC.updateCat3ProductsView = function(cat3Id, action, $tag){
  HN.TC.c3pva.cat3Id = cat3Id;
  var $pdtFiltering = $("#pdt-filtering"),
      isCheckbox = $tag && $tag.is(":checkbox");

  if (!action)
    return false;

  args = action.match(/^([+-])_([a-z0-9-]+)_(.+)/i);

  var actionModifier = isCheckbox ? ($tag.prop('checked') ? '+' : '-') : args[1],
      actionType = args[2].toLowerCase(),
      actionArg = args[3];

  switch (actionType) {
    case "page": HN.TC.c3pva.page = parseInt(actionArg); break;
    case "sort": HN.TC.c3pva.sort = actionArg.toLowerCase(); break;
    case "filter":
      var argMatch = actionArg.match(/^([a-z0-9-]+)(?:_(.+))?/),
          argName = argMatch[1].toLowerCase(),
          argVal = argMatch[2] && argMatch[2].toLowerCase();

      if (argName == "all") {
        HN.TC.c3pva.filters = {};
        HN.TC.c3pva.filter_count = 0;
        $pdtFiltering.find("input[type='checkbox']").prop("checked", false);
      } else if (!argVal) {
        return false;
      }

      if (actionModifier == "+") {
        if (!HN.TC.c3pva.filters[argName])
          HN.TC.c3pva.filters[argName] = {};
        HN.TC.c3pva.filters[argName][argVal] = $.trim($tag.next().html());
        HN.TC.c3pva.filter_count++;
      } else if (HN.TC.c3pva.filters[argName]) {
        $pdtFiltering.find("input[name='+_filter_"+argName+"_"+argVal+"']").prop("checked", false);
        HN.TC.c3pva.filter_count--;
        if (HN.TC.c3pva.filters[argName][argVal])
          delete(HN.TC.c3pva.filters[argName][argVal]);
        else
          delete HN.TC.c3pva.filters[argName];
      }
      break;
    default:break;
  }

  // populating criterias block
  $pdtFiltering.find("ul.current-criterias li").not(":last").remove();
  if (HN.TC.c3pva.filter_count) {
    $pdtFiltering.find(".current-criterias").show();
    var html = '';
    for (var fn in HN.TC.c3pva.filters) {
      for (var fv in HN.TC.c3pva.filters[fn]) {
        if (typeof HN.TC.c3pva.filters[fn][fv] == "string") {
          html += '<li>'+
                    '<span>'+HN.TC.c3pva.filters[fn][fv]+'</span>'+
                    '<img src="'+HN.TC.Locals.RessourcesURL+'images/blue-picto-remove.png" alt="X" data-action="-_filter_'+fn+'_'+fv+'" />'+
                  '</li>';
        }
      }
    }
    $pdtFiltering.find("ul.current-criterias li:last").before(html);
  } else {
    $pdtFiltering.find(".current-criterias").hide();
  }

  $("#ajax-pdt-list").load(HN.TC.Locals.AJAX_Cat3ProductsView, HN.TC.c3pva);
};

HN.TC.GetNuukikRecommendedProducts = function(domId, params, type, query_string, onClickFunc){
  query_string = query_string || "";
  $.ajax({
      type: "POST",
      url: HN.TC.Locals.AJAXGetProductsInfos,
      data: {"actions":[{"action":"get_nuukik_pdts_infos", "params": params}]},
      dataType: "json",
      error: function (XMLHttpRequest, textStatus, errorThrown) {},
      success: function (data, textStatus) {
        if (data.error === "" && data.data.pdtList.length) {
          switch (type) {
            case 'carrousel':
              $("#"+domId+" div.AvailCarrousel div.mask ul.items").show()
                .append(HN.TC.GetPdtVbHtmlCarrousel(data.data.pdtList, query_string));
              HN.TC.NuukikCarrouselStart();
              break;

            case 'list':
              var $pdtList = $("#"+domId).show()
                .find("ul")
                .append(HN.TC.GetPdtVbList(data.data.pdtList, query_string, true));
              onClickFunc && $pdtList.on("click", "a.link-block", onClickFunc);
              break;

            default:
              var $pdtList = $("#"+domId).show()
                .find(".grey-block-inlay").append(HN.TC.GetPdtVb(data.data.pdtList.slice(0,4), query_string))
                .append("<div class=\"zero\"></div>");
              onClickFunc && $pdtList.on("click", "a.link-block", onClickFunc);
              break;
          }
        } else {
          $("#"+domId).hide();
        }
      }
  });
};

HN.TC.GetSeenProducts = function(seenProducts, domId, type){
    var query_string = (arguments.length>2?"?"+arguments[2]:"");
    $.ajax({
        type: "POST",
        url: HN.TC.Locals.AJAXGetProductsInfos,
        data: {"actions":[{"action":"get_pdts_infos","pdtIds":seenProducts, "keepInitialOrder":1}]},
        dataType: "json",
        error: function (XMLHttpRequest, textStatus, errorThrown) {},
        success: function (data, textStatus) {
          if (!data.error) {
            switch(type){

              case 'list':

				//Start Change on 02/01/2015

					$("#"+domId+" ul").show().append(HN.TC.GetPdtVbList_seen_with_js(data.data.pdtList, query_string)).find("td.pdt-vb").each(function(){
						var $infos = $(this).find(".infos");
						$(this).find(".picture > img, span.see-link").wrap("<a href=\""+$infos.find("a").attr("href")+"\"/>");
					});

					/*
					$("#"+domId+" ul").show().append(HN.TC.GetPdtVbList(data.data.pdtList, query_string)).find("td.pdt-vb").each(function(){
						var $infos = $(this).find(".infos");
						$(this).find(".picture > img, span.see-link").wrap("<a href=\""+$infos.find("a").attr("href")+"\"/>");
					});*/

				//End Change on 02/01/2015

                break;

              default:
                $("#"+domId).show().append(HN.TC.GetPdtVb(data.data.pdtList, query_string)).find("td.pdt-vb").each(function(){
                  var $infos = $(this).find(".infos");
                  $(this).find(".picture > img, span.see-link").wrap("<a href=\""+$infos.find("a").attr("href")+"\"/>");
                });
                break;
            }
          }
        }
    });
};

HN.TC.GetPdtVbList = function(pdtList, query_string, addH5Tag){
  var $pdtVbList = $();
  pdtList = pdtList || [];
  for (var k=0; k<pdtList.length; k++) (function(){
    var pdt = pdtList[k];
      $pdtVbList = $pdtVbList.add(
      $("<li>", {
        "class": "reco-pdt-block",
        "html": "<div class=\"product-list-picture\">"+
                  "<a href=\""+pdt.urls["fo_url"]+"\" class=\"link-block\">"+
                      "<img src=\""+pdt.pics[0]["thumb_small"]+"\" alt=\""+pdt.infos["name"]+" - "+pdt.infos["fastdesc"]+"\" class=\"vmaib\"/><div class=\"vsma\"></div>"+
                  "</a>"+
                "</div>"+
                "<div class=\"product-list-infos\">"+
                  (addH5Tag?'<h5>':'')+"<a href=\""+pdt.urls["fo_url"]+"\" class=\"product-list-label link-block\">"+pdt.infos["name"]+"</a>"+(addH5Tag?'</h5>':'')+
                  "<div class=\"product-list-price\">"+pdt.infos["price"]+"</div>"+
                "</div>",
        "data-id": pdt.infos['id']
      }).data("pdt", pdt)
    );
  }());
  return $pdtVbList;
};


/* Start Change on 31/12/2014 */
//New function to Parse the seen products
//And add some Js vars for the iAdvize partner

	HN.TC.GetPdtVbList_seen_with_js = function(pdtList, query_string, addH5Tag){
	  var $pdtVbList = $();
	  pdtList = pdtList || [];

	  //Js to print
	  //var	js_print ='';
	  var local_loop =0;

	  for (var k=0; k<pdtList.length; k++) (function(){

		//Product container
		var pdt = pdtList[k];

		//Js to print
		//Only the first Three products !
		local_loop++;
		if(local_loop<4){
			/*js_print	= "<script type=\"text/javascript\">";
			js_print		+= "var iad_last_vue_product_"+local_loop+"=\""+pdt.infos["id"]+"#"+pdt.infos["name"]+"\";";
			js_print	+= "</script>";
			*/

			var js_print   = document.createElement("script");
			js_print.type  = "text/javascript";
			//script.src   = "path/to/the/javascript.js";    // use this for linked script
			js_print.text  = "var iad_last_vue_product_"+local_loop+"=\""+pdt.infos["name"]+" || "+pdt.infos["id"]+"\";";

			document.getElementById('already-seen-products').appendChild(js_print);
		}


		//var pdt = pdtList[k];
		$pdtVbList = $pdtVbList.add(
		  $("<li>", {
			"class": "reco-pdt-block",
			"html": "<div class=\"product-list-picture\">"+
					  "<a href=\""+pdt.urls["fo_url"]+"\" class=\"link-block\">"+
						  "<img src=\""+pdt.pics[0]["thumb_small"]+"\" alt=\""+pdt.infos["name"]+" - "+pdt.infos["fastdesc"]+"\" class=\"vmaib\"/><div class=\"vsma\"></div>"+
					  "</a>"+
					"</div>"+
					"<div class=\"product-list-infos\">"+
					  (addH5Tag?'<h5>':'')+"<a href=\""+pdt.urls["fo_url"]+"\" class=\"product-list-label link-block\">"+pdt.infos["name"]+""+
					  "</a>"+(addH5Tag?'</h5>':'')+
					  "<div class=\"product-list-price\">"+pdt.infos["price"]+"</div>"+
					"</div>",


			"data-id": pdt.infos['id']
		  }).data("pdt", pdt)
		);
	  }());

	  //console.log('aaaaaaaaaaaaa');
	  //console.log("**"+js_print+"**");
	  //console.log($pdtVbList);
	  //console.log("**\n\n**");


	  return $pdtVbList;
	};

/* End Change on 31/12/2014 */


HN.TC.GetPdtVbHtmlCarrousel = function(pdtList, query_string){
    var html = "";
    for (var k=0; k<pdtList.length; k++) {
        var pdtr = pdtList[k];
        html += "<li data-id=\""+pdtr.infos['id']+"\">"+
                  "<div class=\"grey-block-pdt\">"+
                    "<div class=\"grey-block-picture\">"+
                      "<a href=\""+pdtr.urls["fo_url"]+"\" class=\"link-block\">"+
                          "<img src=\""+pdtr.pics[0]["thumb_small"]+"\" alt=\""+pdtr.infos["name"]+" - "+pdtr.infos["fastdesc"]+"\" class=\"vmaib\"/><div class=\"vsma\"></div>"+
                      "</a>"+
                    "</div>"+
                    "<div class=\"grey-block-infos\">"+
                      "<a href=\""+pdtr.urls["fo_url"]+"\" class=\"grey-block-label link-block\"><strong>"+pdtr.infos["name"]+"</strong></a>"+
                      "<div class=\"grey-block-price\">"+pdtr.infos["price"]+"</div>"+
                    "</div>"+
                  "</div>"+
                "</li>";
    }
    return html;
};

// Avail Carrousel
HN.TC.NuukikCarrouselStart = function(){
  var $NuukikCarrousel = $("div.AvailCarrousel div.mask");
  $NuukikCarrousel.scrollable({
    loop: true,
    size: $("div.AvailCarrousel div.mask ul li").length,
    speed: 300,
    prev: "div.scroll-l",
    next: "div.scroll-r"
  }).circular().autoscroll({interval: 2500});
}

HN.TC.jsonToString = function(obj) {
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
}

HN.TC.GetPdtVb = function(pdtList, query_string){
  var $pdtVbList = $();
  for (var k=0; k<pdtList.length; k++) (function(){
    var pdt = pdtList[k];
    $pdtVbList = $pdtVbList.add(
      $("<div>", {
        "class": "grey-block-pdt reco-pdt-block",
        "html": "<a href=\""+pdt.urls["fo_url"]+query_string+"\" class=\"link-block\">"+
                  "<div class=\"picture\">"+
                    "<img src=\""+pdt.pics[0]["thumb_small"]+"\" alt=\""+pdt.infos["name"]+" - "+pdt.infos["fastdesc"]+"\" class=\"vmaib\"/><div class=\"vsma\"></div>"+
                  "</div>"+
                  "<div class=\"infos\">"+
                    "<span class=\"label\"><strong>"+pdt.infos["name"]+"</strong></span>"+
                    "<div class=\"price\">"+pdt.infos["price"]+"</div>"+
                  "</div>"+
                "</a>"
      }).data("pdt", pdt)
    );
  }());
  return $pdtVbList;
};

$("input[name=login], input[name=pass]").live(
'keyup', function(e) {
  if(e.keyCode == 13 && $("input[name=login]").val() != '' && $("input[name=pass]").val() != '') {
    HN.TC.Login();
  }
});

HN.TC.PassWordRecovery = function(){
  var email = $('#right-col-myaccount-zone form[name=psswrdRcvr] input[name=email]').val();
  $('#right-col-myaccount-zone span.error').remove();
  $.ajax({
    type: "POST",
    dataType:'jsonp',
    async: true,
    crossDomain : true,
    url: HN.TC.Locals.AccountURL+'password-recovery.html',
    data: "email="+email,
    error : function (XMLHttpRequest, textStatus, errorThrown) {
      var html = '<span class="error">Erreur de requète</span>';
      $('#right-col-myaccount-zone').append(html);
    },
    success : function (data, textStatus) {
      if(data.error){
        var html = '<span class="error">'+data.error+'</span>';
        $('#right-col-myaccount-zone').append(html);
      }else{
        var html = '<div class="right-col-myaccount-password-recovery-confirmation-text">Un e-mail de récupération de mot de passe vient de vous etre envoyé à l\'adresse : '+email+'</div>\n\
        <div onclick="javascript:HN.TC.ShowLoginForm(\'cancel\')" style="right: 6px" class="right-col-myaccount-button fr">Ok</div>\n\
        <div class="clear"></div>\n\
        <br />\n\
        <div class="clear"></div>';
        $('#right-col-myaccount-zone').slideUp(300, function(){$('#right-col-myaccount-zone').html(html);});
        $('#right-col-myaccount-zone').slideDown(300);
      }
    }
  })
}

HN.TC.ShowLoginForm = function(commande){
  var self = this;
  switch(commande){
    case 'show':
        var htmlOrigin = $('#right-col-myaccount-zone').html();

        var html = '<form id="login">';
        html += '<div class="right-col-myaccount-fields clearfix">';
        html += '<br /><label for="login">Adresse e-mail*:</label>';
        html += '<input type="text" value="" name="login"/><br />';
        html += '<label for="pass">Mot de passe*:</label>';
        html += '<input type="password" value="" name="pass"/><br />';
        html += '</div>';
        html += '<a href="#" class="show-login-btn" onClick="javascript:HN.TC.ShowLoginForm(\'recover-password\')">Mot de passe oublié ? Cliquez ici</a>';
        html += '<div class="right-col-myaccount-button-bar clearfix">';
        html += '<div class="right-col-myaccount-button fl btn-validate" style="left: 6px" onClick="javascript:HN.TC.Login()">OK</div>';
        html += '<div class="right-col-myaccount-button fr" style="right: 6px"  onClick="javascript:HN.TC.ShowLoginForm(\'cancel\')">Annuler</div>';
        html += '</div>';
        html += '<div class="clear"></div><br />';
        html += '</form>';
        html += '<div class="clear"></div>';

        $('#right-col-myaccount-zone').slideUp(300, function(){$('#right-col-myaccount-zone').html(html);});
        self.myaccountZoneHtmlOrigin = htmlOrigin;
        $('#right-col-myaccount-zone').slideDown(300);
      break;

      case 'recover-password':
        html = '<form name="psswrdRcvr">';
        html += '<div class="right-col-myaccount-fields clearfix">';
        html += '<label for="email">Votre e-mail:</label>';
        html += '<input class="text" type="text" name="email">';
        html += '</div>';
        html += '<div class="right-col-myaccount-button-bar clearfix">';
        html += '<div class="right-col-myaccount-button fl btn-validate" style="left: 6px" onClick="javascript:HN.TC.PassWordRecovery()">Envoyer</div>';
        html += '<div class="right-col-myaccount-button fr" style="right: 6px"  onClick="javascript:HN.TC.ShowLoginForm(\'cancel\')">Annuler</div>';
        html += '</div>';
        html += '</form>';
        html += '<div class="clear"></div><br />';
        $('#right-col-myaccount-zone').slideUp(300, function(){$('#right-col-myaccount-zone').html(html);});
        $('#right-col-myaccount-zone').slideDown(300);
        break;

      case 'cancel':
        $('#right-col-myaccount-zone').slideUp(300, function(){
          $('#right-col-myaccount-zone').html(self.myaccountZoneHtmlOrigin);
          if (HN.TC.Locals.mobile) {
            $('#right-col-myaccount-zone').hide();
          } else {
            $('#right-col-myaccount-zone').slideDown(300, function(){
              $('#right-col-myaccount-zone').css("display", "");
            })
          }
        });
        break;
  }
  return false;
}

HN.TC.Login = function(){
  $('#right-col-myaccount-zone span.error').remove();
  $.ajax({
    type: "POST",
    dataType:'jsonp',
    async: true,
    crossDomain : true,
    url: HN.TC.Locals.AccountURL+'ajax-login.html',
    data: "login="+$('form#login input[name=login]').val()+"&pass="+$('form#login input[name=pass]').val(),
    error : function (XMLHttpRequest, textStatus, errorThrown) {
      var html = '<span class="error">Erreur de connexion</span>';
      $('#right-col-myaccount-zone').append(html);
    },
    success : function (data, textStatus) {
      if (data.error) {
        var html = '<span class="error">'+data.error+'</span>';
        $('#right-col-myaccount-zone').append(html);
      } else {
        window.location.reload();
      }
    }
  });
}

HN.TC.Logout = function(){
    $('#right-col-myaccount-zone span.error').remove();
  $.ajax({
    type: "POST",
    dataType:'jsonp',
    async: true,
    crossDomain : true,
    url: HN.TC.Locals.AccountURL+'ajax-logout.html',
    error : function (XMLHttpRequest, textStatus, errorThrown) {
      var html = '<span class="error">Erreur de déconnexion</span>';
      $('#right-col-myaccount-zone').append(html);
    },
    success : function (data, textStatus) {
      if(data != ''){
         window.location.reload();
      }
    }
  })
}


$(function(){
  //fixed block position
  var $rcf = $("div.right-col-fixed");
  if ($rcf.length && window.location.pathname != "/") {
    var rightColFixedTopDft = parseInt($rcf.offset().top) - parseInt($rcf.css("margin-top")),
        rightColFixedLeftDft = parseInt($rcf.parent().offset().left);

    $(window).scroll(function(){
      var currentScrollTop = $(this).scrollTop(),
          rightColFixedTop = parseInt($rcf.offset().top) - parseInt($rcf.css("margin-top")),
          rightColFixedHeight = $rcf.outerHeight(true),
          footerTop = $('div#footer').offset().top,
          relativeFooterTop = parseInt($('div#footer').offset().top) - (rightColFixedTopDft+rightColFixedHeight),
          rightColFixedBottom = rightColFixedTop + rightColFixedHeight,
          virtualBottomTop = (parseInt($(this).scrollTop())+$(window).height() - (parseInt($('div#footer').height())+rightColFixedHeight));

      if (currentScrollTop > rightColFixedTop && $rcf.hasClass('bottomBlocked') != true) {
        $rcf.css({ position: 'fixed', top: '0px', left: rightColFixedLeftDft });
      } else if(rightColFixedTopDft > rightColFixedTop && $rcf.css('position') == 'fixed') {
        $rcf.css({ position: 'relative', left: 0 });
      }

      if (rightColFixedBottom > footerTop) {
        $rcf.css({ position: 'relative', top: relativeFooterTop+'px', left: 0 });
        $rcf.addClass('bottomBlocked');
      } else if (rightColFixedTop > currentScrollTop && $rcf.hasClass('bottomBlocked')) {
        $rcf.css({ position: 'fixed', top: '0px', left: rightColFixedLeftDft });
        $rcf.removeClass('bottomBlocked')
      }
    }).scroll();

    $(window).resize(function(){
      rightColFixedLeftDft = parseInt($rcf.parent().offset().left);
      if ($rcf.css("position") == "fixed")
        $rcf.css("left", rightColFixedLeftDft);
    });
  }
})

// Show product feedback
HN.TC.ToggleProductFeedback = function(elem){
  var $block = $(elem).closest(".grey-block"),
      pdtId = $block.data("id"),
      $feedback = $("#cat3-feedback-"+pdtId);

  if ($feedback.length) {
    if (!$feedback.is(":animated")) {
      if ($feedback.hasClass("unfolded")) {
        $feedback
          .removeClass("unfolded")
          .find(".in").slideUp("slow").end()
          .find(".top-arrow").fadeOut("slow");
      } else {
        $feedback
          .addClass("unfolded")
          .find(".in").slideDown("slow").end()
          .find(".top-arrow").fadeIn("slow");
      }
    }
  } else {
    $.ajax({
      data: "idProduct="+pdtId,
      dataType: "json",
      type: "GET",
      url: HN.TC.Locals.RessourcesURL+"ajax/AJAXGetProductsFeedback.php"
    }).done(function(data, textStatus, jqXHR){
      var html =
        "<div id=\"cat3-feedback-"+pdtId+"\" class=\"cat3-feedback-block unfolded\">"+
          "<div class=\"grey-block in\">"+
            "<div class=\"blue-title\">Les avis clients</div>"+
            "<button id=\"close-feedback-button-"+pdtId+"\" class=\"grey-btn gb68 see-link close-button\">Fermer <img class=\"fr\" src=\""+HN.TC.Locals.RessourcesURL+"/images/picto-close.png\" alt=\"X\" /></button>";
      for (var k=0, l=Math.min(data['list'].length, 3); k<l; k++) {
        html +=
            "<div class=\"infos\">"+
              "<img class=\"logo-client\" src=\""+HN.TC.Locals.RessourcesURL+"images/upper-grey-account-logo.png\" alt=\"face\" /> "+
              "<span>"+data['list'][k].prenom+"</span> <em>le "+data['list'][k].date+"</em> "+
              HN.TC.Locals.getStarRateHtml(data['list'][k].note)+
            "</div>"+
            "<div class=\"comment\">"+data['list'][k].comment+"</div>";
      }
      html +=
          "</div>"+
          "<div class=\"top-arrow\"></div>"+
        "</div>";

      $block.after(html);
      $("#cat3-feedback-"+pdtId)
        .find(".in").slideDown("slow").end()
        .find(".top-arrow").fadeIn("slow");

    }).fail(function(jqXHR, textStatus, errorThrown){
      // future error management
    });
  }
}

HN.TC.Locals.getStarRateHtml = function(note){
  if(note < 0)
    note = 0;
  if(note > HN.TC.Locals.MAX_STAR_RATE)
    note = HN.TC.Locals.MAX_STAR_RATE;

  var full_stars = Math.floor(note/2);
  var half_stars = note%2;
  var empty_stars = 5-(full_stars+half_stars);

  var html = '<ul class="star-rating">';
  for(var a=1; a<=HN.TC.Locals.NB_STARS;a++){
    if(a <= full_stars)
      html += '<li class="star-full"></li>';
    else if(half_stars){
      html += '<li class="star-half"></li>';
      half_stars -= 1;
    }else
      html += '<li class="star-empty"></li>';
  }
  html += '</ul>';
  return html;
}

HN.TC.getUrlVars = function(){
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for(var i = 0; i < hashes.length; i++)
    {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    return vars;
}

HN.TC.ShowCreateAddressForm = function(indey){
  $("#myaccount-create-adress-form-dialog").dialog("open");
}

HN.TC.ShowCreateAccountForm = function(){
  $("#myaccount-create-account-form-dialog").dialog("open");
}

HN.TC.ShowcontactezNousForm = function(){
  $("#contactez-nous-form-dialog").dialog("open");
}

HN.TC.ShowCatalogueForm = function(){
  $("#show-catalogue-form").dialog("open");
}

HN.TC.ShowCreateAccountForm_create_question = function(){

  $("#create-question").dialog("open");
  $('#parag_desc').show();

  var answer_title = $("#title_quest_bar").text();
  $("#title_quest_bar").hide();
  $("#ui-dialog-title-create-question").html(answer_title);
}

function close_popup_create(){
	$("#create-question").dialog("close");
}

function close_popup_send_contact(){
	$("#contactez-nous-form-dialog").dialog("close");
}

function close_popup_send_commande(){
	$("#show-catalogue-form").dialog("close");
}


function close_popup_repondre(){
	var id_calque = $("#id_calque").val();
	$("#product-reponse"+id_calque).dialog("close");
}

HN.TC.accountContactDialog = function(type, idOrder){
    /* 1 commande
   * 2 devis pdf
   * 3 devis manager
   * 4 lead
   **/
  $.ajax({
    data: "type="+type+"&id=" + idOrder,
    dataType: "html",
    type: "GET",
    url: HN.TC.Locals.AccountURL+"contact-form.html",
    error: function (XMLHttpRequest, textStatus, errorThrown) {
      $("div#cat3-feedback-"+idProduct).remove();
    },
    success: function (data, textStatus) {
      $("#account-contact-dialog").html(data);
      $(".send-contact-request").addClass("xhr-send-contact-request");
      $(".xhr-send-contact-request").removeClass("send-contact-request");
      var titleContactDialog;
      switch (parseInt(type)) {
        case 1: titleContactDialog = "Nous contacter concernant une commande"; break;
        case 2:
        case 3: titleContactDialog = "Nous contacter concernant un devis"; break;
        case 4: titleContactDialog = "Contacter un partenaire"; break;
      }
      $("#ui-dialog-title-account-contact-dialog").text(titleContactDialog);
      $("#account-contact-dialog").dialog("open");
    }
  });
}

HN.TC.printOrderConfirmed = function(orderID){
  window.open(HN.TC.Locals.URL+"pdf/commande/"+orderID, "_blank");
}

$(function(){
  // global dialog close button
  $("body").on("click", ".ui-dialog-content .blue-close span", function(){
    $(this).closest(".ui-dialog-content").dialog("close");
  });

  // valid account message sending
  var $acd = $("#account-contact-dialog");
  $acd.on("click", "div.xhr-send-contact-request", function(){
    var type = $acd.find("input[name='type']").val(),
        id = $acd.find("input[name='id']").val(),
        object = $acd.find("select[name='object']").val(),
        msg = $acd.find("textarea[name='message']").val();
    if (msg == "" || msg == "Quelle est votre question ?") {
      alert("Message vide");
    } else {
      $.ajax({
        data: "type="+type+"&id="+id+"&object="+object+"&message="+msg,
        dataType: "html",
        type: "POST",
        url: HN.TC.Locals.AccountURL+"contact-form.html"
      }).done(function(data){
        $("#account-contact-dialog").html("Message envoyé avec succès");
        setTimeout(function(){
          $("#account-contact-dialog").dialog("close"); // to trigger the hash change
          window.location.reload();
        }, 2000);
      }).fail(function(jqXHR, textStatus, errorThrown){
      });
    }
  });

  // empty textarea
  $acd.on("focus", "textarea.contact-message", function(){
    if ($(this).text() == "Quelle est votre question ?")
      $(this).text("");
  });

  // category 2's cat2 siblings accordion
  $(".categories .cat-siblings").accordion({ active: false, collapsible: true, heightStyle: "content" });

  // product's cat3 accordion
  var $pdtCat3Siblings = $(".product-page .cat-siblings"),
      $pdtCat3uls = $pdtCat3Siblings.children(".cat3-cat-filtering "),
      $pdtCat3Current = $pdtCat3uls.filter(".current"),
      pdtCat3Index = $pdtCat3uls.index($pdtCat3Current);
  $pdtCat3Siblings.accordion({ active: pdtCat3Index, collapsible: true, heightStyle: "content" });

  // feedback show/hide
  $("#ajax-pdt-list")
    .on("click", "a.ToggleProductFeedback", function(){
      HN.TC.ToggleProductFeedback(this);
      return false;
    })
    .on("click", ".cat3-feedback-block .close-button", function(){
      HN.TC.ToggleProductFeedback($(this).closest(".cat3-feedback-block").prev());
    });

  // img apercu
  $('.cat3-prod-list-pic .picture').live({
    mouseover : function(){
      $(this).find('.cat3-product-show').show();
    },
    mouseout : function(){
      $(this).find('.cat3-product-show').hide();
    }
  });

  $('.cat3-product-show').live(
  'click', function(){
    $('#cat3-show-product-infos-dialog').prev().addClass('ui-no-title');
    var hrefProduct = $(this).prev().prev('a').attr('href').split('/');
    var linkProduct = hrefProduct[4].split('-');
    var idProduct = linkProduct[1];
    $.ajax({
      data: {"actions":[{"action":"get_pdt_infos","pdtId":idProduct}]},
      dataType: "json",
      type: "POST",
      url: HN.TC.Locals.AJAXGetProductsInfos,
      error: function (XMLHttpRequest, textStatus, errorThrown) {

      },
      success: function (data, textStatus) {
        var html = '<div class="cat3-dialog-left-block fl">';
        html += '<div class="cat3-dialog-picture">';
        html += '<img class="vmaib" src="'+data.data.pdtList[0].pics[0].card+'" alt="image produit" /><div class="vsma"></div>';
        html += '</div>';
        html += '<div class="cat3-dialog-carrousel">';
        html += '<div class="grey-block">';
        html += '<ul class="items">';
        $(data.data.pdtList[0].pics).each(function(){
          html += '<li><img src="'+this.thumb_small+'" alt="" /></li>';
        });
        html += '</ul>';
        html += '</div>';
        html += '<div class="scroll-l-cat3"><img src="'+HN.TC.Locals.RessourcesURL+'images/carrousel-arrow-left.png" alt=""></div>';
        html += '<div class="scroll-r-cat3"><img src="'+HN.TC.Locals.RessourcesURL+'images/carrousel-arrow-right.png" alt=""></div>';
        html += '</div>';
        html += '</div>';
        html += '<div class="cat3-dialog-right-block fr">';
        html += '<h2><a class="blue-small-title blue-smaller-title" href="'+data.data.pdtList[0].urls.fo_url+'">'+data.data.pdtList[0].infos.name+'</a></h2>';
        html += data.data.pdtList[0].infos.fastdesc;
        if(data.data.pdtList[0].infos.adv_category == 1){
          html += '<p class="cat3-checked-line"><img src="'+HN.TC.Locals.RessourcesURL+'images/green-check.png" alt="" />Livraison: '+data.data.pdtList[0].infos.adv_delivery_time+'</p>';
        }
        if(data.data.pdtList[0].infos.pdt_set_as_estimate == false){
          html += '<p class="cat3-checked-line"><img src="'+HN.TC.Locals.RessourcesURL+'images/green-check.png" alt="" />Livraison: '+data.data.pdtList[0].infos.shipping_fee+'</p>';
        }
        if(data.data.pdtList[0].infos.average_note > 0){
          html += '<span class="cat3-checked-line"><img src="'+HN.TC.Locals.RessourcesURL+'images/picto-avis.png" alt="picto-avis" /> Avis client ';
          html += HN.TC.Locals.getStarRateHtml(data.data.pdtList[0].infos.average_note);
          html += ' <a class="color-blue" href="'+data.data.pdtList[0].urls.fo_url+'">Lire les avis</a></span>';
        }
        html += '<br /><br />';
        if(data.data.pdtList[0].infos.adv_category == 1){
          html += '<div class="conseils-d-experts">';
          html += '<img  class="vmaib" src="'+HN.TC.Locals.RessourcesURL+'images/picto-supplier.png" alt="Bénéficiez du conseil d\'experts sur ce produit" title="Bénéficiez du conseil d\'experts sur ce produit" />';
          html += '<span class="fr">Bénéficiez du conseils<br />d\'experts sur ce produit</span></div>';
        }else{
          html += '<div class="plusieurs-devis">';
          html += '<img  class="vmaib" src="'+HN.TC.Locals.RessourcesURL+'images/picto-advertiser.png" alt="Gagnez du temps en recevant plusieurs devis" title="Gagnez du temps en recevant plusieurs devis" />';
          html += '<span class="fr">Gagnez du temps,<br /> en recevant plusieurs devis</span></div>';
        }
        html += '<div class="cat3-price fr">';
        if(data.data.pdtList[0].infos.hasPrice == true){
          html += data.data.pdtList[0].infos.pdt_set_as_estimate ? 'Sur devis' : 'à partir de : <span>'+data.data.pdtList[0].infos.price+" HT</span>";
        }else
            html += 'à partir de : <span>'+data.data.pdtList[0].infos.price+'</span>';
        html += '</div>';
        html += '<div class="cat3-action fr">';
        if(data.data.pdtList[0].infos.adv_category == 1){

          if((data.data.pdtList[0].infos.saleable != 0 && !data.data.pdtList[0].infos.pdt_set_as_estimate)){
            html += '<a href="'+data.data.pdtList[0].urls.cart_add_url+'" class="'+((data.data.pdtList[0].infos.saleable != 0 && !data.data.pdtList[0].infos.pdt_set_as_estimate) ? (data.data.pdtList[0].infos.nb_refs > 1 ? 'btn-cart-add-big-pink' : 'btn-cart-add-small-single') : 'btn-esti-ask-orange' )+'" data-adv-type="'+data.data.pdtList[0].infos.adv_category+'" ></a>';
            html += '<a href="'+data.data.pdtList[0].urls.cart_add_url+'" data-adv-type="'+data.data.pdtList[0].infos.adv_category+'" class="ask-estimate-link"><img src="'+HN.TC.Locals.RessourcesURL+'images/puce-estimate-small.png" alt="" />Demander un devis</a>';
          }//else      href="<?php echo $pdt["cart_add_url"]; ?>"  data-adv-type="<?php echo $pdt["adv_cat"]; ?>"
          //  html += '<a href="'+data.data.pdtList[0].urls.cart_add_url+'" class="'+((data.data.pdtList[0].infos.saleable && !data.data.pdtList[0].infos.pdt_set_as_estimate) ? (data.data.pdtList[0].infos.nb_refs > 1 ? 'btn-cart-add-big-pink' : 'btn-cart-add-small-single'): 'btn-esti-ask-orange')+'" data-adv-type="'+data.data.pdtList[0].infos.adv_category+'" ></a>';
        }else{
          html += '<a href="'+HN.TC.Locals.URL+'lead-a.html?pdtID='+data.data.pdtList[0].infos.id+'&catID='+data.data.pdtList[0].infos.cat_id+'" class="btn-esti-ask-orange"></a>';
        }
        var savedProductsListUrl = HN.TC.GVars.userLogged == true ? HN.TC.Locals.AccountURL+'saved-products-list.html' : HN.TC.Locals.URL+'liste-produits-sauvegardes.html';
        html += '<div class="save-product-link savedProductsListZone_'+data.data.pdtList[0].infos.id+'">';
        if($.inArray(data.data.pdtList[0].infos.id, HN.TC.GVars.savedProductsList) >= 0){
          html += '<div class="puce puce-9"></div><span class="color-green">Produit sauvegardé</span> ';
          html += '<a href="'+savedProductsListUrl+'" class="color-blue">Voir liste</a><br />';
        }else{
          html += '<a href="saveProductList:add-'+data.data.pdtList[0].infos.id+'" class="btn-users-product-list"><div class="puce puce-5"></div>Sauvegarder ce produit</a>';
        }
        html += '</div>';
        html += '<a href="'+data.data.pdtList[0].urls.fo_url+'" class="color-blue"><div class="puce puce-7"></div>Voir la fiche complète</a>';
        html += '</div>';
        html += '</div>';
        $('#cat3-show-product-infos-dialog').html(html);

        // cat3 page Carrousel
        var $Cat3DiagCarrousel = $("div.cat3-dialog-carrousel div.grey-block");
        $Cat3DiagCarrousel.scrollable({
                loop: true,
                size: data.data.pdtList[0].infos.pics_count,
                speed: 300,
                prev: "div.scroll-l-cat3",
                next: "div.scroll-r-cat3"
        }).circular().autoscroll({interval: 2500});
        $('#cat3-show-product-infos-dialog').dialog("open");
      }
    });
  });
});


$(function(){
    $("#page-product-zoom-image-dialog").dialog({
    width: 840,
    autoOpen: false,
    modal: true,
    draggable: false,
    resizable: false
  });

  $("#cart-add-product-dialog").dialog({
    top: 100,
    width: 840,
    autoOpen: false,
    modal: true,
    draggable: true,
    resizable: false
  });
  $("#cart-add-product-dialog").prev('div.ui-dialog-titlebar').find('.ui-dialog-title').addClass('blue-title');

  $("#myaccount-create-account-form-dialog").dialog({
    width: 930,
    autoOpen: false,
    modal: true,
    draggable: false,
    resizable: false
  });
  
  $("#contactez-nous-form-dialog").dialog({
    width: 737,
    autoOpen: false,
    modal: true,
    draggable: false,
    resizable: false
  });
  
  $("#show-catalogue-form").dialog({
    width: 775,
    autoOpen: false,
    modal: true,
    draggable: false,
    resizable: false
  });
  
  
  $("#create-question").dialog({
    width: 630,
    autoOpen: false,
    modal: true,
    draggable: false,
    resizable: false
  });
  $("#saved-products-list-dialog").dialog({
    width: 430,
    height: 370,
    autoOpen: false,
    modal: true,
    draggable: false,
    resizable: false
  });

  $("#infos-dialog").dialog({
    width: 840,
    autoOpen: false,
    modal: true,
    draggable: true,
    resizable: false
  });

  $("#cat3-show-product-infos-dialog").dialog({
    width: 684,
    autoOpen: false,
    modal: true,
    draggable: false,
    resizable: false
  });



/*
 * Quick Account creation
 */

var ERRORS_TEXT_QAC = new Array();
ERRORS_TEXT_QAC["nom"] = "Merci de renseigner votre nom.";
ERRORS_TEXT_QAC["prenom"] = "Merci de renseigner votre prénom.";
ERRORS_TEXT_QAC["email"] = "Merci de renseigner votre Email. Ex:xxx@domaine.com";
ERRORS_TEXT_QAC["societe"] = "Merci de renseigner le nom de votre société.";
ERRORS_TEXT_QAC["pass"] = "Merci de saisir un mot de passe.";
ERRORS_TEXT_QAC["pass2"] = "Merci de confirmer votre mot de passe.";

var create_account_dialog_form = $("form[name='create_account_dialog_form']");

var submited = false;

  $(".edit-qac").focus(function() {
    //var tooltip = $(this).tooltip();
    //tooltip.hide();
    var bad = false;
    if ($(this).hasClass("badInfos") == true) {
      $(this).removeClass("badInfos");
      $(this).next().find('.leadform_ok').html('');
      bad = true;
    }
    var id = $(this).attr("id");
    var idArray = id.split("_"); // 0 => "field", 1 => id
    $("#qac-error_"+idArray[1]).hide();
    if (idArray[1]) {
      if (ERRORS_TEXT_QAC[idArray[1]]) {
        var errContent = ERRORS_TEXT_QAC[idArray[1]];
      }
      else {
        errContent = "Merci de renseigner " + idArray[1];
      }

      if (errContent.replace(/^\s*|\s*$/,"") != "" && bad == true) {
        //$("#tooltip").html(errContent);
        //tooltip.show();
        //$(this).next().find('.leadform_error').html(errContent).show();
      }
    }
  }).blur(function() {
    formData = create_account_dialog_form.serialize();

    var id = $(this).attr("id");
    var idArray = id.split("_"); // 0 => "field", 1 => id
    var fname = idArray[1];

    if (submited != true)
    $.ajax({
      type: "POST",
      data: formData+"&onlyCheck=true",
      url: HN.TC.Locals.RessourcesURL+"ajax/AJAXQuickAccountCreation.php",
      success: function(data) {
        data = data.replace(/^\s*|\s*$/,"");
        if (data != 'checkOk') {
          var errors = data.split('|');

          var i = 0;
          var fieldNameError = false;
          // Last element of errors will be empty
          for (i=0; i<errors.length; i++) {

            if (submited == true) {
              var errorContent = '';
              if (ERRORS_TEXT_QAC[errors[i]]) {
                errorContent = ERRORS_TEXT_QAC[errors[i]];
              }
              else {
                errorContent = "Merci de renseigner " + errors[i];
              }
              $("#qac-error_"+errors[i]).html("Merci de renseigner ce champ.");
              $("#qac-error_"+errors[i]).show();
              $("#qac-field_"+errors[i]).addClass("badInfos");
              $("#qac-ok_"+errors[i]).html("<img src='"+HN.TC.Locals.RessourcesURL+"images/form-warn-error.png' class='okImg' alt='X' />");

              if (errors[i] == fname)
                fieldNameError = true;
            }
            else {
              if (errors[i] == fname) {
                fieldNameError = true;
              }
            }// end if submitted == true
          }// end for

          if (fieldNameError == true) {
            var errorContent = 'Merci de renseigner ce champ.';
            if (ERRORS_TEXT_QAC[fname]) {
              errorContent = ERRORS_TEXT_QAC[fname];
            }
            else {
              errorContent = "Merci de renseigner " + fname;
            }

            $("#qac-error_"+fname).html(errorContent);
            $("#qac-error_"+fname).show();
            $("#qac-field_"+fname).addClass("badInfos");
            $("#qac-ok_"+fname).html("<img src='"+HN.TC.Locals.RessourcesURL+"images/form-warn-error.png' class='okImg' alt='X' />");
          }
          else {

              $("#qac-ok_"+fname).html("<img src='"+HN.TC.Locals.RessourcesURL+"images/form-check-ok.png' class='okImg' alt='ok' />");
              $("#qac-ok_"+fname).show();
              $("#qac-error_"+fname).hide();
              $("#qac-error_"+fname).html("");
          }
        }
        else {
          if (fname != '') {
            $("#qac-ok_"+fname).html("<img src='"+HN.TC.Locals.RessourcesURL+"images/form-check-ok.png' class='okImg' alt='ok' />");
            $("#qac-ok_"+fname).show();
          }
        }// end if data == checkOk

      }// end success
    });// end ajax
  });// end blur

  $(".btn-create-account", create_account_dialog_form).click(function(){
    submited = true;
    // Ajax request to valid form
    formData = create_account_dialog_form.serialize();
    $.ajax({
      type: "POST",
      data: formData,
      url: HN.TC.Locals.RessourcesURL+"ajax/AJAXQuickAccountCreation.php",
      success: function(data) {
        data = data.replace(/^\s*|\s*$/,"");
        if (data == 'createOk') {
          $("[id^='qac-error_']").html("").hide();
          $('#myaccount-create-account-form-dialog').dialog('close');
          alert('Votre compte a été créé. Vous allez recevoir un mail de confirmation.');
          self.location.href=window.location;
        }else if(data == 'alreadyExists'){
          $("id^=qac-error_").html("");
          $("id^=qac-error_").hide();
          $('#myaccount-create-account-form-dialog').dialog('close');
          alert('Ce compte existe déjà, veuillez vous identifier via le bouton "S\'identifier"');
        }else {
          data = data.replace(/^\s*|\s*$/,"");
          var errors = data.split('|');

          var i = 0;
          // Last element of errors will be empty
          for (i=0; i<errors.length-1; i++) {
            var errorContent = 'Merci de renseigner ce champ.';
            if (ERRORS_TEXT_QAC[errors[i]]) {
              errorContent = ERRORS_TEXT_QAC[errors[i]];
            }
            else {
              errorContent = "Merci de renseigner " + errors[i];
            }
            $("#qac-error_"+errors[i]).html(errorContent);
            $("#qac-error_"+errors[i]).show();
            $("#qac-field_"+errors[i]).addClass("badInfos");
            $("#qac-ok_"+errors[i]).html("<img src='"+HN.TC.Locals.RessourcesURL+"images/form-warn-error.png' class='okImg' alt='X' />");
          }
        }
      }
    });
    return false;
  });

  // responsive version
  var prevWinSize;
  $(window).on("resize", function(){
    var curWinSize = window.innerWidth; // we use window.innerWidth directly to match the css rule @media max-width which includes the scrollbar
    if (curWinSize <= 600 && (!prevWinSize || prevWinSize > 600)) {
      $("body").addClass("mobile");
      $("#cart-add-product-dialog").data("uiDialog") && $("#cart-add-product-dialog").dialog("option", "width", '');
      $("#foreignDeliveryLayer").data("uiDialog") && $("#foreignDeliveryLayer").dialog("option", "width", '');
      $("#account-edit-address-form-dialog").data("uiDialog") && $("#account-edit-address-form-dialog").dialog("option", "width", '');
      $("#account-contact-dialog").data("uiDialog") && $("#account-contact-dialog").dialog("option", "width", '');
      $("#message-dialog").data("uiDialog") && $("#message-dialog").dialog("option", "width", '');
      $("#q-a-dialog").data("uiDialog") && $("#q-a-dialog").dialog("option", "width", '');
      $("#page-product-zoom-image-dialog").data("uiDialog") && $("#page-product-zoom-image-dialog").dialog("option", "width", '');
      $("#cart-add-product-dialog").data("uiDialog") && $("#cart-add-product-dialog").dialog("option", "width", '');
      $("#myaccount-create-account-form-dialog").data("uiDialog") && $("#myaccount-create-account-form-dialog").dialog("option", "width", '');
      $("#saved-products-list-dialog").data("uiDialog") && $("#saved-products-list-dialog").dialog("option", "width", '');
      $("#infos-dialog").data("uiDialog") && $("#infos-dialog").dialog("option", "width", '');
      $("#cat3-show-product-infos-dialog").data("uiDialog") && $("#cat3-show-product-infos-dialog").dialog("option", "width", '');
      $('#right-col-myaccount-zone').appendTo("#header-mobile-login");
      HN.TC.Locals.mobile = true;
    } else if (curWinSize > 600 && (!prevWinSize || prevWinSize <= 600)) {
      $("body").removeClass("mobile");
      $("#cart-add-product-dialog").data("uiDialog") && $("#cart-add-product-dialog").dialog("option", "width", 840);
      $("#foreignDeliveryLayer").data("uiDialog") && $("#foreignDeliveryLayer").dialog("option", "width", 840);
      $("#account-edit-address-form-dialog").data("uiDialog") && $("#account-edit-address-form-dialog").dialog("option", "width", 520);
      $("#account-contact-dialog").data("uiDialog") && $("#account-contact-dialog").dialog("option", "width", 480);
      $("#message-dialog").data("uiDialog") && $("#message-dialog").dialog("option", "width", 500);
      $("#q-a-dialog").data("uiDialog") && $("#q-a-dialog").dialog("option", "width", 840);
      $("#page-product-zoom-image-dialog").data("uiDialog") && $("#page-product-zoom-image-dialog").dialog("option", "width", 840);
      $("#cart-add-product-dialog").data("uiDialog") && $("#cart-add-product-dialog").dialog("option", "width", 840);
      $("#myaccount-create-account-form-dialog").data("uiDialog") && $("#myaccount-create-account-form-dialog").dialog("option", "width", 930);
      $("#saved-products-list-dialog").data("uiDialog") && $("#saved-products-list-dialog").dialog("option", "width", 370);
      $("#infos-dialog").data("uiDialog") && $("#infos-dialog").dialog("option", "width", 840);
      $("#cat3-show-product-infos-dialog").data("uiDialog") && $("#cat3-show-product-infos-dialog").dialog("option", "width", 684);
      $('#right-col-myaccount-zone').appendTo("#my-account .col-right-arrowed-block-center");
      HN.TC.Locals.mobile = false;
    }
    prevWinSize = curWinSize;
  }).trigger("resize");

});


function showReassuranceDialog(index){
  var title = "",
      html = "";
  switch (index) {
    case 1:
      title = "Notre valeur ajoutée";
      html += "\
Notre mission est d'accompagner les entreprises et les collectivités dans leurs démarches d'achats professionels.<br />\
Nous mettons pour cela à votre disposition :<br />\
<br />\
- Un vaste catalogue de matériels et d'équipements en vente en ligne,<br />\
- Des experts métiers pour vous guider et répondre gratuitement à vos besoins de façon personnalisée,<br />\
- Une mise en relation directe avec notre réseau de partenaires fournisseurs afin d'obtenir des devis comparatifs,<br />\
- Des espaces métier dédiés.<br />\
- Un puissant outil de gestion pour vos devis et vos commandes.<br />\
<br />\
Techni-contact est ouvert à tous les professionnels, quelque soit leur secteur d'activité.<br />\
<br />\
Plus de 300.000 entreprises nous ont fait confiance.<br />\
<br />";
      break;

    case 2:
      title = "Comment commander ?";
      html += "\
Sur Techni-Contact, vous pouvez facilement commander en ligne des dizaines de milliers d'équipements et matériels professionnels. <br />\
Il vous suffit de mettre le ou les produits au panier et compléter le processus de commande en ligne.<br />\
<br />\
Si votre besoin est spécifique ou si vous désirez un devis, contactez simplement l'un de nos experts dont les coordonnées apparaissent en haut à droite sur les fiches produits. Vous pouvez aussi leur demander un devis via le formulaire dédié.<br />\
<br />\
Enfin, pour mieux répondre à TOUS vos besoins professionnels, Techni-Contact.com vous permet d'obtenir plusieurs devis simultanément sur une large gamme de produits partenaires. <br />\
Vous gagnez ainsi du temps en étant mis directement en relation avec les fournisseurs du produit désiré.<br />\
<br />\
<br />\
<br />";
      break;

    case 3:
      title = "Vos moyens de paiement";
      html += "\
Techni-Contact met à votre disposition de nombreux moyens de paiement<br />\
<br />\
- Carte Bancaire (service 100% sécurisé via notre partenaire bancaire)<br />\
- Chèque<br />\
- Virement<br />\
- Mandat administratif (pour les collectivités)<br />\
<br />";
      break;
  }
  html += "<div class=\"blue-close\"><span>[Fermer]</span></div>";

  $("#ui-dialog-title-infos-dialog").text(title);
  $('#infos-dialog').html(html).dialog("open");
}
