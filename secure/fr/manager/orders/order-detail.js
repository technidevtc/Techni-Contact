HN.TC.Order = $.extend(function(_data, user_id, user_login){
  
  var me = this,
      id = _data.id,
      pre_id = "#item-cart",
      cur_so, // current Supplier Order Object
      ado = HN.TC.AjaxDoctrineObject.create()
        .setObject('Order')
        .setLoadQueryParams(['id = ?', id]);
  
  me.data = _data;
  me.pre_id = pre_id;
  me.ci = new HN.TC.ItemCart({
    pre_id: pre_id,
    handleCartObject: ado,
    user_id: user_id,
    user_login: user_login,
    options: { sup_comment: true },
    onUpdate: function(){ me.saveDelayed(); }
  }, _data);
  me.internal_note = new HN.TC.InternalNotes({
    id_reference: id,
    context: HN.TC.InternalNotes.CLIENT_COMMAND
  });
  me.conversation = new HN.TC.Messenger({
    id_sender: user_id,
    type_sender: HN.TC.__MSGR_USR_TYPE_BOU__,
    id_recipient: me.data.client_id,
    type_recipient: HN.TC.__MSGR_USR_TYPE_INT__,
    context: HN.TC.__MSGR_CTXT_CUSTOMER_TC_CMD__,
    reference_to: id
  });
  me.uploadFile = new HN.TC.ajaxUploadFile({
    itemId: id,
    context: "orders-order-detail",
    fileElementId: "docFile"
  });
  me.pjMessFile = new HN.TC.ajaxUploadFile({
    itemId: id,
    context: "order-tmppjmess",
    fileElementId: "pjMessFile"
  });
  
  var onEditFuncs = {
    "activity": {
      edit: function(){ },
      validate: function(){
        if ($(this).data("old_val") != $(this).val()) {
          me.updateCartActivityState();
          $(pre_id+"-send_invoice_mail-line")[$.inArray($(this).val()|0, HN.TC.Order.activityDeferredList) > -1 ? "hide" : "show"]();
          me.ci.updateCartCalcs(true);
          me.save();
        }
      }
    },
    "client_id": {
      edit: function(){ me.client_id_acf.enable(); },
      validate: function(){
        if ($(this).data("old_val") != $(this).val())
          me.client_id_acf.confirm();
        me.client_id_acf.disable();
      }
    },
    "processing_status": {
      edit: function(){
        $(pre_id+"-processing_status_texts").show();
        if (parseInt($(this).val()) == HN.TC.Order.GLOBAL_PROCESSING_STATUS_FORECAST_SHIPPING_DATE && $(pre_id+"-forecasted_ship").data("info-value") == 0) {
          var now = $.now()/1000;
          $(pre_id+"-forecasted_ship")
            .data("info-value", now)
            .val(HN.TC.get_formated_date(now));
        }
      },
      validate: function(){
        $(pre_id+"-processing_status_texts").hide();
        
        var pst_input = '#item-cart-processing_status_texts div input'+pre_id,
            doSave = $(this).data("old_val") != $(this).val();
        
        switch (parseInt($(this).val())) {
          case HN.TC.Order.GLOBAL_PROCESSING_STATUS_ASS_CLOSED :
            if ($(pst_input+"-sav_closed_text").data("old_val") != $(pst_input+"-sav_closed_text").val())
              doSave = true;
          break;
          
          case HN.TC.Order.GLOBAL_PROCESSING_STATUS_ASS_OPEN :
            if ($(pst_input+"-sav_opened_text").data("old_val") != $(pst_input+"-sav_opened_text").val())
              doSave = true;
          break;
          
          case HN.TC.Order.GLOBAL_PROCESSING_STATUS_FORECAST_SHIPPING_DATE :
            var $fsi = $(pst_input+"-forecasted_ship");
            if ($fsi.data("old_val") != $fsi.val()) {
              var t = HN.TC.get_timestamp($fsi.val()),
                  date = HN.TC.get_formated_date(t);
              $fsi.data("info-value", t);
              $fsi.val(date);
              // copy date to text field
              $(pst_input+"-forecast_shipping_text").val(date);
              doSave = true;
            }
          break;
          
          case HN.TC.Order.GLOBAL_PROCESSING_STATUS_SHIPPED :
            if ($(pst_input+"-shipped_text").data("old_val") != $(pst_input+"-shipped_text").val())
              doSave = true;
          break;
          
          case HN.TC.Order.GLOBAL_PROCESSING_STATUS_CANCELED : 
            if ($(pst_input+"-cancelled_text").data("old_val") != $(pst_input+"-cancelled_text").val())
              doSave = true;
          break;
          
          case HN.TC.Order.GLOBAL_PROCESSING_STATUS_PARTLY_CANCELED : 
            if ($(pst_input+"-partly_cancelled_text").data("old_val") != $(pst_input+"-partly_cancelled_text").val())
              doSave = true;
          break;

        }
        if (doSave) {
          me.save();
          me.loadConversation(true);
        }
      }
    }
  };
  
  me.htmlInit = function(){
    me.parent.htmlInit.call(this);
    
    // Dialog Boxes
    me.$sendSoDb = $(pre_id+"-sso-db").dialog({
      width: 1000,
      autoOpen: false,
      modal: true,
      buttons: {
        "Annuler": function(){
          $(this).dialog("close");
        },
        "Envoyer la commande": function(){
          me.sendSo(function(){
            me.$sendSoDb.dialog("close");
          });
        }
      }
    });
    me.$soArcDb = $(pre_id+"-so-arc-db").dialog({
      width: 400,
      autoOpen: false,
      modal: true,
      buttons: {
        "Annuler": function(){
          $(this).dialog("close");
        },
        "Lier ARC": function(){
          $(this).find("iframe").get(0).contentDocument['so-arc'].submit();
        }
      }
    });
    me.$uploadDocDb = $(pre_id+"-upload-doc-db").dialog({
      width: 400,
      autoOpen: false,
      modal: true,
      buttons: {
        "Annuler": function(){
          $(this).dialog("close");
        },
        "Envoyer": function(){
          me.uploadFile.aliasFileName = $(this).find("input[name='aliasFileName']").val();
          me.uploadFile.loadingImg = $(this).find("img.loading-gif");
          me.uploadFile.doAjaxFileUpload(function(){
            me.$uploadDocDb.dialog("close");
            me.getUploadedFilesList();
          });
        }
      }
    });
    me.$uploadMsnAttachmentDb = $(pre_id+"-upload-msn-attachment-db").dialog({
      width: 400,
      autoOpen: false,
      modal: true,
      buttons: {
        "Annuler": function(){
          $(this).dialog("close");
        },
        "Envoyer": function(){
          me.pjMessFile.aliasFileName = $(this).find("input[name='aliasPjMessFileName']").val();
          me.pjMessFile.loadingImg = $(this).find("img.loading-gif");
          me.pjMessFile.doAjaxFileUpload(function(){
            me.$uploadMsnAttachmentDb.dialog("close");
            me.getPjMessFilesList();
          });
        }
      }
    });
    
    // buttons
    $(pre_id+"-delete").on("click", function(){
      if (confirm("Supprimer la commande n°"+id+" ?")) {
        ado.setData(null).setMethod("delete").execute(function(data){
          if (data == 1)
            document.location.href = "orders.php";
          else
            $(pre_id+"-error-msg").text("Erreur lors de la suppression de la commande.").stop().stop().hide().fadeIn(67).fadeOut(100).fadeIn(67).delay(4000).fadeOut(1500);
        });
      }
    });
    $(pre_id+"-resend-client-email").on("click", function(){
      var multiRecipientsMails = $('input[name=secondaryContacts]') ? $('input[name=secondaryContacts]').val() : '';
      ado.setData([multiRecipientsMails]).setMethod("sendClientEmail").execute(function(data){
        $(pre_id+"-success-msg").text("Email renvoyé au client avec succès !").stop().stop().hide().fadeIn(67).fadeOut(100).fadeIn(67).delay(4000).fadeOut(1500);
      });
    });
    $(pre_id+"-save-order").on("click", function(){
      me.save(function(){
        $(pre_id+"-success-msg").text("Modifications enregistrées avec succès !").stop().stop().hide().fadeIn(67).fadeOut(100).fadeIn(67).delay(4000).fadeOut(1500);
      });
    });
    $(pre_id+"-see-supplier-lead").on("click", function(){
      if (me.data.lead_id != 0)
        window.open(HN.TC.ADMIN_URL+"supplier-leads/lead-detail.php?id="+me.data.lead_id, "_blank");
    });
    $(pre_id+"-see-estimate").on("click", function(){
      if (me.data.estimate_id != 0)
        window.open(HN.TC.ADMIN_URL+"estimates/estimate-detail.php?id="+me.data.estimate_id, "_blank");
    });
    $(pre_id+"-oking").on("click", function(){
      ado.setData(null).setMethod("oking").execute(function(data){
        if (data.oked != 0) {
          $.extend(me.data, data);
          $(pre_id+"-oking").remove();
          $(pre_id+"-oked").html("Par <strong>"+data.oked_user_login+"</strong> le <strong>"+HN.TC.get_formated_datetime(data.oked, " à ")+"</strong>");
          if (me.data.validated == 0)
            $(pre_id+"-validation").show();
        }
      });
    });
    $(pre_id+"-add-doc").on("click", function(){
      me.$uploadDocDb.dialog("open");
    });
    $(pre_id+"-print").on("click", function(){
      if (me.data.web_id != 0)
        open(HN.TC.URL+"pdf/commande/"+me.data.web_id+"#zoom=100");
    });
    $(pre_id+"-validate").on("click", function(){
      var multiRecipientsMails = $('input[name=secondaryContacts]')? $('input[name=secondaryContacts]').val() : '';
      ado.setData([$(pre_id+"-send_recap_mail").prop("checked")&1, $(pre_id+"-send_invoice_mail").prop("checked")&1, multiRecipientsMails]).setMethod("validate").execute(function(data){
        if (data.validated != 0) {
          $.extend(me.data, data);
          $(pre_id+"-validation").html("Validée par <strong>"+data.validated_user_login+"</strong> le <strong>"+HN.TC.get_formated_datetime(data.validated, " à ")+"</strong>");
          $(pre_id+"-processing_status").val(data.processing_status);
          me.loadConversation(false);
        }
      });
    });
    $(pre_id+"-fonction").on("change", function(){
      var checked = $(this).prop("checked"),
          new_fonction = checked ? "Je suis un particulier" : "";
      if (me.data.fonction != new_fonction) {
        me.data.fonction = new_fonction;
        me.save();
      }
    });
    
    // don't show validation info if not oked
    if (me.data.oked != 0)
      $(pre_id+"-validation").show();
    
    if (me.data.waiting_info_status & HN.TC.__MSGR_CTXT_CUSTOMER_TC_CMD__)
      $(pre_id+"-close-conv").show();
    
    $(pre_id+"-forecasted_ship").datepicker();
    
    $(pre_id+"-send_invoice_mail-line")[$.inArray(me.data.activity|0, HN.TC.Order.activityDeferredList) > -1 ? "hide" : "show"]();
    
    // change/save buttons
    $(pre_id+"-activity,"+
      pre_id+"-client_id,"+
      pre_id+"-fonction,"+
      pre_id+"-type,"+
      pre_id+"-alternate_id,"+
      pre_id+"-payment_mode,"+
      pre_id+"-payment_mean,"+
      pre_id+"-payment_status,"+
      pre_id+"-processing_status,"+
      pre_id+"-email").next().on("click", function(){
      
      
      var $field = $(this).prevAll("input, select").first(),
          fieldName = $field.attr("id").split(pre_id.substring(1)+"-")[1],
          prop = $field.prop("readonly") !== undefined ? "readonly" : "disabled";
      if ($field.prop(prop)) {
        $field.data("old_val", $field.val());
        $(this).html("Sauver");
        $field.prop(prop,false).focus();
        if (onEditFuncs[fieldName])
          onEditFuncs[fieldName].edit.apply($field);
      }
      else {
        $(this).html("Changer");
        $field.prop(prop,true);
        me.data[fieldName] = $field.val();
        if (onEditFuncs[fieldName])
          onEditFuncs[fieldName].validate.apply($field);
        else if ($field.data("old_val") != $field.val())
          me.save();
      }
    });
    
    $(pre_id+"-processing_status").on("change", function(){
      $(pre_id+"-processing_status_texts input.status-text").hide();
      switch (parseInt($(this).val())) {
        case HN.TC.Order.GLOBAL_PROCESSING_STATUS_WAITING_PAYMENT_VALIDATION:
        case HN.TC.Order.GLOBAL_PROCESSING_STATUS_WAITING_VALIDATION:
        case HN.TC.Order.GLOBAL_PROCESSING_STATUS_WAITING_CB:
        case HN.TC.Order.GLOBAL_PROCESSING_STATUS_WAITING_PROCESSING:
        case HN.TC.Order.GLOBAL_PROCESSING_STATUS_PROCESSING:
          break;
        case HN.TC.Order.GLOBAL_PROCESSING_STATUS_ASS_OPEN:
          $(pre_id+"-sav_opened_text").data("old_val", $(pre_id+"-sav_opened_text").val());
          $(pre_id+"-sav_opened_text").show();
          break;
        case HN.TC.Order.GLOBAL_PROCESSING_STATUS_ASS_CLOSED:
          $(pre_id+"-sav_closed_text").data("old_val", $(pre_id+"-sav_closed_text").val());
          $(pre_id+"-sav_closed_text").show();
          break;
        case HN.TC.Order.GLOBAL_PROCESSING_STATUS_FORECAST_SHIPPING_DATE:
          $(pre_id+"-forecasted_ship").data("old_val", $(pre_id+"-forecasted_ship").val());
          $(pre_id+"-forecasted_ship").show();
        case HN.TC.Order.GLOBAL_PROCESSING_STATUS_PARTLY_SHIPPED:
          break;
        case HN.TC.Order.GLOBAL_PROCESSING_STATUS_SHIPPED:
          $(pre_id+"-shipped_text").data("old_val", $(pre_id+"-shipped_text").val());
          $(pre_id+"-shipped_text").show();
          break;
        case HN.TC.Order.GLOBAL_PROCESSING_STATUS_PARTLY_CANCELED:
          $(pre_id+"-partly_cancelled_text").data("old_val", $(pre_id+"-partly_cancelled_text").val());
          $(pre_id+"-partly_cancelled_text").show();
          break;
        case HN.TC.Order.GLOBAL_PROCESSING_STATUS_CANCELED:
          $(pre_id+"-cancelled_text").data("old_val", $(pre_id+"-cancelled_text").val());
          $(pre_id+"-cancelled_text").show();
          break;
      }
    }).change();
    
    // client id autocomplete field
    me.client_id_acf = new HN.TC.AutoCompleteField({
      field: pre_id+"-client_id",
      feedFunc: function(val, cb){
        var q = HN.TC.AjaxQuery.create()
          .select("c.id, c.societe, c.nom, c.prenom")
          .from("Clients c")
          .where("c.actif = ?", 1);
        if ($.isNumeric(val)) {
          var c_id_iv = HN.TC.AjaxQuery.getQueryNumIntervals("c.id", val, HN.TC.AjaxQuery.CUS_MAX_ID);
          q.andWhere(c_id_iv[0]+" OR c.societe like ? OR c.nom like ?", $.merge(c_id_iv[1], [val+"%", val+"%"]));
        }
        else {
          q.andWhere("c.societe like ? OR c.nom like ?", [val+"%", val+"%"]);
        }
        q.limit(10).execute(function(data){
          var cb_data = [];
          for (var i=0; i<data.length; i++)
            cb_data.push([data[i].id, data[i].societe, data[i].nom+" "+data[i].prenom]);
          cb(cb_data);
        });
      },
      onConfirm: function(){
        me.loadClient(this.$f.val(), function(){
          me.setClientFileLink();
          me.save();
        });
      },
      disabled: true
    });
    
    // supplier real buy prices
    $(pre_id+"-supplier_orders-margins")
      .on("dblclick", "td[data-bhv='editable']", function(){
        var $this = $(this);
        if (!$this.data("editing")) {
          var text = $this.html();
          $this.data("old_val", text);
          $this.empty();
          $("<input>", { type: "text", value: text }).appendTo(this).focus()
          $this.data("editing", true);
        }
      })
      .on("blur", "td[data-bhv='editable'] input", function(){
        var text = sprintf("%.02f",parseFloat($(this).val())||0),
            $td = $(this).closest("td");
        $td.empty().html(text).data("editing", false);
        if (text != $td.data("old_val")) {
          me.data.supplier_orders[$td.closest("tr").index()][$td.data("so-info")] = text;
          me.updateSRPCalcs();
          me.save();
        }
      })
      .on("keydown", "td[data-bhv='editable'] input", function(e){
        if (e.keyCode == 13) {
          $(this).blur();
        }
        else if (e.keyCode == 27) {
          $(this).blur();
          e.stopPropagation();
        }
      });
    
    // init functions
    me.ci.init();
    me.updateSo(); // after cart items init, because it sets some vars
    me.updateSRP();
    me.updateSRPCalcs();
    me.getUploadedFilesList();
    me.getPjMessFilesList();
    me.setClientFileLink();
    me.loadInternalNotes(true);
    me.loadConversation(true);
  };
  
  me.updateSo = function(){ // Supplier Order
    
    // getting supplier names from lines
    var supplierNames = {};
    for (var i=0; i<me.data.lines.length; i++) {
      var line = me.data.lines[i];
      supplierNames[line.sup_id.toString()] = line.sup_name;
    }
    
    var $supplier_orders = $(pre_id+"-supplier_orders tbody").empty();
    for (var i in me.data.supplier_orders) (function(){
      var so = me.data.supplier_orders[i],
          $td = $("<td>")
      $td.append("<span class=\"icon "+(so.mail_sent!=0?"accept":"cancel")+"\"></span> "+supplierNames[so.sup_id.toString()]+" - ");
      if (so.mail_sent != 0) {
        $td.append(
          "Commande <a class=\"_blank\" href=\""+HN.TC.ADMIN_URL+"supplier-orders/supplier-order-detail.php?id="+so.id+"\">"+so.sup_id+"-"+so.order_id+"</a> "+
          "envoyée le "+HN.TC.get_formated_datetime(so.mail_time, " à ")+" "+
          "par "+so.sender.name+" - "
        );
        $("<button>", {
          type: "button",
          "class": "btn ui-state-default ui-corner-all",
          text: "Renvoyer le mail",
          click: function(){ cur_so = so; me.resendSo(); }
        }).appendTo($td);
      }
      else {
        $("<button>", {
          type: "button",
          "class": "btn ui-state-default ui-corner-all",
          "text": "Envoyer la commande au fournisseur",
          click: function(){ cur_so = so; me.fillSendSoDb(so); }
        }).appendTo($td);
      }
      $td.append(" - <span class=\"icon "+(so.arc_time!=0?"accept":"cancel")+"\"></span> ");
      if (so.arc_time != 0) {
        $("<a>", {
          href: HN.TC.PDF_URL_ARC+so.arc+"#zoom=100",
          text: "Voir ARC",
          class: "_blank"
        }).appendTo($td);
        $td.append(" - ");
        $("<a>", {
          href: "#edit-arc",
          text: "Modifier ARC",
          click: function(){
            me.$soArcDb.dialog("option", "title", "Modifier l'ARC de l'ordre fournisseur <span class=\"green\">"+so.sup_id+"-"+so.order_id+"</span>");
            me.$soArcDb.find("iframe").attr("src", HN.TC.ADMIN_URL+"ressources/iframes/supplier-order-arc.php?id="+so.id).end().dialog("open");
            return false;
          }
        }).appendTo($td);
      }
      else {
        $("<a>", {
          href: "#link-arc",
          text: "Lier ARC",
          click: function(){
            me.$soArcDb.dialog("option", "title", "Lier un ARC à l'ordre fournisseur <span class=\"green\">"+so.sup_id+"-"+so.order_id+"</span>");
            me.$soArcDb.find("iframe").attr("src", HN.TC.ADMIN_URL+"ressources/iframes/supplier-order-arc.php?id="+so.id).end().dialog("open");
            return false;
          }
        }).appendTo($td);
      }
      $("<tr>").append($td).appendTo($supplier_orders);
    }());
  };
  
  me.updateSRP = function(){ // Supplier Real Buy Prices
    // getting supplier names from lines
    var supplierNames = {};
    for (var i=0; i<me.data.lines.length; i++) {
      var line = me.data.lines[i];
      supplierNames[line.sup_id.toString()] = line.sup_name;
    }
    
    var $tbody = $(pre_id+"-supplier_orders-margins tbody").empty();
    for (var i in me.data.supplier_orders) {
      var so = me.data.supplier_orders[i];
      $("<tr>")
        .append("<td>"+supplierNames[so.sup_id.toString()]+"</td>"+
                "<td class=\"price\" data-bhv=\"editable\" data-so-info=\"total_ht_real\">"+sprintf("%.02f",parseFloat(so.total_ht_real)||0)+"</td>"+
                "<td class=\"price\" data-bhv=\"editable\" data-so-info=\"fdp_ht_real\">"+sprintf("%.02f",parseFloat(so.fdp_ht_real)||0)+"</td>")
        .appendTo($tbody);
    }
    
  };
  
  me.updateSRPCalcs = function(){
    var total_ht_real = 0;
    $(pre_id+"-supplier_orders-margins tbody tr td:nth-child(2)").each(function(){
      total_ht_real += parseFloat($(this).text()) || 0;
    });
    var $tds = $(pre_id+"-supplier_orders-margins tfoot td.price");
    $tds.eq(0).text(sprintf("%.02f",parseFloat(me.data.total_ht - total_ht_real)||0)+"€");
    $tds.eq(1).text(sprintf("%.02f",parseFloat(me.data.total_ht/total_ht_real*100)||0)+"%");
  };
  
  me.fillSendSoDb = function(so){
    var $tbody_lines = me.$sendSoDb.find(".cart-items tbody").empty(),
        $totals = me.$sendSoDb.find(".cart-totals tbody tr td.total");
    for (var li=0; li<me.data.lines.length; li++) {
      if (me.data.lines[li].sup_id == so.sup_id) {
        var line = $.extend({}, me.data.lines[li]),
            sup_name = line.sup_name,
            image = {
              link: line.pdt_id && line.pdt_ref_name != "" && line.pdt_cat_id ? HN.TC.get_pdt_fo_url(line.pdt_id, line.pdt_ref_name, line.pdt_cat_id) : null,
              url: line.pdt_id ? HN.TC.get_pdt_pic_url(line.pdt_id,"thumb_small",1) : HN.TC.get_dft_pdt_pic_url("thumb_small")
            };
        $tbody_lines.append(
          "<tr class=\"line\">"+
            "<td rowspan=\"3\" class=\"image\">"+(image.link?"<a href=\""+image.link+"\" target=\"_blank\">":"")+"<img src=\""+image.url+"\" alt=\"\"/>"+(image.link?"</a>":"")+"</td>"+
            "<td rowspan=\"2\" class=\"sup-ref\">"+line.sup_ref+"</td>"+
            "<td rowspan=\"2\" class=\"idtc\">"+
              (line.pdt_id|0 ? "<a href=\""+HN.TC.get_pdt_bo_url(line.pdt_id)+"\"" : "<span") + " data-line-info=\"pdt_ref_id\">"+
                line.pdt_ref_id+
              (line.pdt_id|0 ? "</a>" : "</span>")+
            "</td>"+
            "<td class=\"desc\" data-line-info=\"desc\">"+line.desc+"</td>"+
            "<td class=\"price\" data-line-info=\"pau_ht\">"+sprintf("%.02f",line.pau_ht*1)+"</td>"+
            "<td class=\"qty\" data-line-info=\"quantity\">"+line.quantity+"</td>"+
            "<td class=\"price\" data-line-info=\"total_ht\">"+sprintf("%.02f",Math.round(line.pau_ht*line.quantity*100)/100)+"</td>"+
          "</tr>"+
          "<tr>"+
            "<td class=\"desc\">Éco participation</td>"+
            "<td class=\"price\"></td>"+
            "<td class=\"qty\"></td>"+
            "<td class=\"price\" data-line-info=\"et_total_ht\">"+sprintf("%.02f",line.et_total_ht)+"</td>"+
          "</tr>"+
          "<tr>"+
            "<td colspan=\"6\" class=\"comment\" data-line-info=\"sup_comment\">Comm. Four. : "+line.sup_comment+"</td>"+
          "</tr>"
        );
      }
    }
    $totals.eq(0).text(sprintf("%.02f",parseFloat(so.total_ht)));
    $totals.eq(1).text(sprintf("%.02f",parseFloat(so.total_ttc)));
    
    me.$sendSoDb.dialog("option", "title", "Vous êtes sur le point d'envoyer à <span class=\"green\">"+sup_name+"</span> une commande comprenant les éléments suivants :")
    me.$sendSoDb.dialog("open");
  };
  
  me.sendSo = function(){
    var cb = $.isFunction(arguments[0]) ? arguments[0] : false;
    HN.TC.AjaxDoctrineObject.create()
      .setObject("SupplierOrder")
      .setLoadQueryParams(["id = ?", cur_so.id])
      .setData([$(pre_id+"-sso-db").find("textarea").val()])
      .setMethod("send").execute(function(data){
        for (var i=0; i<me.data.supplier_orders.length; i++)
          if (me.data.supplier_orders[i].id == data.id) {
            $.extend(me.data.supplier_orders[i], data);
            break;
          }
        me.updateSo();
        if (cb) cb();
      });
  };
  
  me.resendSo = function(){
    //var multiRecipientsMails = new Array(me.data.status,me.data.type,$('input[name=secondaryContacts]').val());
    var cb = $.isFunction(arguments[0]) ? arguments[0] : false;
    HN.TC.AjaxDoctrineObject.create()
      .setObject("SupplierOrder")
      .setLoadQueryParams(["id = ?", cur_so.id])
      .setData(null)
      .setMethod("sendPartnerMail").execute(function(data){
        if (cb) cb();
      });
  };
  
  me.onArcLinked = function(){
    me.refresh(function(){
      me.$soArcDb.dialog("close");
    });
  };
  
  me.save = function(){
    me.parent.save.call(this);
    var cb = $.isFunction(arguments[0]) ? arguments[0] : false;
    // manage client title and delivery address client title
    if(me.data.titre)
      me.data.titre = HN.TC.getCustomerTitleIndexFromLabel(me.data.titre);
    if(me.data.titre2)
      me.data.titre2 = HN.TC.getCustomerTitleIndexFromLabel(me.data.titre2);
    
    // only get new ht real infos for supplier orders
    for (var soi=0; soi<me.data.supplier_orders.length; soi++) {
      var so = me.data.supplier_orders[soi];
      me.data.supplier_orders[soi] = {
        id: so.id,
        total_ht_real: so.total_ht_real,
        fdp_ht_real: so.fdp_ht_real
      }
    }
    var listMailsRecipients = $('input[name=secondaryContacts]') ? $('input[name=secondaryContacts]').val() : '';
    if (listMailsRecipients != '')
      me.data.listMailsRecipients = listMailsRecipients;
    ado.setData([me.data]).setMethod("updateWithLines").execute(function(data){
      var new_lines = data.lines;
      delete (data.lines);
      $.extend(me.data, data);
      for (var li=0; li<me.data.lines.length; li++)
        me.data.lines[li].setData(new_lines[li]);
      me.ci.refreshLines();
      
      $(pre_id+"-updated").text(HN.TC.get_formated_datetime(data.updated));
      if (me.user_login)
        $(pre_id+"-updated_user_login").text(me.user_login);
      
      //me.data.supplier_orders = data.supplier_orders;
      me.updateSo();
      me.updateSRP();
      me.updateSRPCalcs();
      if (cb) cb();
    });
  };
  
  me.refresh = function(){
    var cb = $.isFunction(arguments[0]) ? arguments[0] : false;
    HN.TC.AjaxQuery.create()
      .select('o.*,'+
              'ol.*,'+
              'ols.nom1,'+
              'so.*,'+
              'sos.name,'+
              'r.id,'+
              'r.refSupplier,'+
              'olp.id,'+
              'olpfr.ref_name,'+
              'olpf.id,'+
              'cu.login as created_user_login,'+
              'uu.login as updated_user_login,'+
              'iu.login as in_charge_user_login,'+
              'vu.login as validated_user_login,'+
              'ou.login as oked_user_login,'+
              'l.origin as lead_source')
      .from('Order o')
      .leftJoin('o.lines ol')
      .leftJoin('ol.supplier ols')
      .leftJoin('o.supplier_orders so')
      .leftJoin('so.sender sos')
      .leftJoin('ol.pdt_ref r')
      .leftJoin('ol.product olp')
      .leftJoin('olp.product_fr olpfr')
      .leftJoin('olp.families olpf')
      .leftJoin('o.created_user cu')
      .leftJoin('o.updated_user uu')
      .leftJoin('o.in_charge_user iu')
      .leftJoin('o.validated_user vu')
      .leftJoin('o.oked_user ou')
      .leftJoin('o.lead l')
      .where('o.id = ?', id)
      .fetchOne(function(data){
        if (data) {
          delete data.lines;
          $.extend(me.data, data);
          me.updateHtmlData(data);
          me.updateSo();
          me.updateSRP();
          me.updateSRPCalcs();
          if (cb) cb();
        }
      });
  };
  
}, HN.TC.Order);

HN.TC.Order.prototype = HN.TC.Cart;
HN.TC.Order.prototype.constructor = HN.TC.Order;
HN.TC.Order.prototype.parent = HN.TC.Cart;
