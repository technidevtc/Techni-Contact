HN.TC.Estimate = $.extend(function(_data, user_id, user_login){
  
  var me = this,
      id = _data.id,
      pre_id = "#item-cart",
      ado = HN.TC.AjaxDoctrineObject.create()
        .setObject('Estimate')
        .setLoadQueryParams(['id = ?', id]);
  
  me.data = _data;
  me.pre_id = pre_id;
  me.ci = new HN.TC.ItemCart({
    pre_id: pre_id,
    handleCartObject: ado,
    user_id: user_id,
    user_login: user_login,
    options: {
      sup_comment: true,
      fdp: _data.type == HN.TC.Estimate.TYPE_NORMAL
    }, // no fdp for ad hoc estimates
    onUpdate: function(){
      me.saveDelayed();
      var firstLine = $(pre_id+"-items tbody > tr.line").first().data("line-infos");
      if (firstLine && firstLine.pdt_id && firstLine.pdt_cat_id != 0)
        me.showRecommendedProducts(firstLine.pdt_id, this.data.client_id);
    }
  }, _data);
  me.internal_note = new HN.TC.InternalNotes({
    id_reference: id,
    context: HN.TC.InternalNotes.ESTIMATE
  });
  me.conversation = new HN.TC.Messenger({
    id_sender: user_id,
    type_sender: HN.TC.__MSGR_USR_TYPE_BOU__,
    id_recipient: me.data.client_id,
    type_recipient: HN.TC.__MSGR_USR_TYPE_INT__,
    context: HN.TC.__MSGR_CTXT_CUSTOMER_TC_ESTIMATE__,
    reference_to: id
  });
  me.pjMessFile = new HN.TC.ajaxUploadFile({
    itemId: id,
    context: "estimate-tmppjmess",
    fileElementId: "pjMessFile"
  });
  //me.saveTO = null;
  
  var onEditFuncs = {
    "activity": {
      edit: function(){ },
      validate: function(){
        var val = $(this).val();
        if ($(this).data("old_val") != val) {
          me.updateCartActivityState();
          var associated_gsc = HN.TC.Estimate.activityToAssociatedGSCList[val];
          if (associated_gsc !== undefined) {
            $(pre_id+"-associated_gsc").val(associated_gsc);
            me.data["associated_gsc"] = associated_gsc;
          }
          me.ci.updateCartCalcs(true);
          me.save();
        }
      }
    },
    "client_id": {
      edit: function(){ me.client_id_acf.enable(); },
      validate: function(){
        if ($(this).data("old_val") != $(this).val())
          me.client_id_acf.confirm(); // cart save done here
        me.client_id_acf.disable();
      }
    }
  };
  
  me.htmlInit = function(){
    me.parent.htmlInit.call(this);
    
    // Message Dialog Box
    me.$msgDb = $("<div>").dialog({
      width: 500,
      autoOpen: false,
      modal: true,
      buttons: {
        "OK": function(){
          $(this).dialog("close");
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
    $(pre_id+"-cancel").on("click", function(){
      if (confirm("Annuler le devis n°"+id+" ?")) {
        ado.setData(null).setMethod("cancel").execute(function(data){
          if (data == 1)
            document.location.href = "estimates.php";
          else
            $(pre_id+"-error-msg").text("Erreur lors de l'annulation du devis.").stop().stop().hide().fadeIn(67).fadeOut(100).fadeIn(67).delay(4000).fadeOut(1500);
        });
      }
    });
    $(pre_id+"-delete").on("click", function(){
      if (confirm("Supprimer le devis n°"+id+" ?")) {
        ado.setData(null).setMethod("delete").execute(function(data){
          if (data == 1)
            document.location.href = "estimates.php";
          else
            $(pre_id+"-error-msg").text("Erreur lors de la suppression du devis.").stop().stop().hide().fadeIn(67).fadeOut(100).fadeIn(67).delay(4000).fadeOut(1500);
        });
      }
    });
    $(pre_id+"-schedule-phone-call").on("click", function(){
      $("#rdvDb").data("vars", {
        relationType: "estimate",
        relationId: me.data.id,
        clientId: me.data.email
      }).dialog("open");
    });
    $(pre_id+"-create-order").on("click", function(){
      console.log("ok");
      if (me.data.lines.length) {
      console.log("ok2");
        ado.setData(null).setMethod("createOrder").execute(function(data){
      console.log("ok3");
          $.extend(me.data, data);
          $(pre_id+"-create-order").after("<strong>GAGNE</strong> <span class=\"icon money_euro\"></span>").remove();
          $(pre_id+"-see-order-line, "+pre_id+"-see-invoice-line").show();
          $(pre_id+"-delete").remove();
          $(pre_id+"-update-estimate").remove();
          $(pre_id+"-status").text(HN.TC.Estimate.statusList[me.data.status]);
          me.loadInternalNotes(false);
        });
      }
    });
    $(pre_id+"-see-order").on("click", function(){
      if (me.data.order_id != 0)
        window.open(HN.TC.ADMIN_URL+"orders/order-detail.php?id="+me.data.order_id, "_blank");
    });
    $(pre_id+"-set-status-won").on("click", function(){
      me.data.status = HN.TC.Estimate.STATUS_WON;
      ado.setData([me.data.status]).setMethod("updateStatus").execute(function(data){
        $(pre_id+"-set-status-won").after("<strong>GAGNE</strong> <span class=\"icon money_euro\"></span>").remove();
        me.loadInternalNotes(true);
        $(pre_id+"-delete").remove();
      });
    });
    $(pre_id+"-generate-invoice").on("click", function(){
      ado.setData(null).setMethod("createInvoice").execute(function(data){
        if (data) {
          me.data.invoice_id = data;
          $(pre_id+"-generate-invoice-line").hide();
          $(pre_id+"-see-invoice-line").show();
        }
      });
    });
    $(pre_id+"-see-invoice").on("click", function(){
      if (me.data.invoice_id != 0)
        window.open(HN.TC.ADMIN_URL+"invoices/invoice-detail.php?id="+me.data.invoice_id, "_blank");
    });
    $(pre_id+"-see-supplier-lead").on("click", function(){
      if (me.data.lead_id != 0)
        window.open(HN.TC.ADMIN_URL+"supplier-leads/lead-detail.php?id="+me.data.lead_id, "_blank");
    });
    $(pre_id+"-save-estimate").on("click", function(){
		
      me.save(function(){
        $(pre_id+"-success-msg").text("Modifications enregistrées avec succès !").stop().stop().hide().fadeIn(67).fadeOut(100).fadeIn(67).delay(4000).fadeOut(1500);
      });
    });
    $(pre_id+"-send-estimate").on("click", function(){
      var listMailsRecipients = $('input[name=secondaryContacts]') ? $('input[name=secondaryContacts]').val() : '';
      me.data.status = HN.TC.Estimate.STATUS_SENT;
      ado.setData([me.data.status, listMailsRecipients]).setMethod("updateStatus").execute(function(data){
        me.loadInternalNotes(false);
        me.$msgDb.dialog("option", "title", "Envoi du devis n°"+me.data.id);
        me.$msgDb.html("Le devis a été envoyé avec succès à l'adresse email suivante :<br/>"+me.data.email).dialog("open");
        $(pre_id+"-send-estimate").hide();
        $(pre_id+"-update-estimate").show();
        $(pre_id+"-resend-estimate").show();
      });
    });
    $(pre_id+"-update-estimate").on("click", function(){
      var listMailsRecipients = $('input[name=secondaryContacts]') ? $('input[name=secondaryContacts]').val() : '';
      me.data.status = HN.TC.Estimate.STATUS_UPDATED;
      ado.setData([me.data.status,listMailsRecipients]).setMethod("updateStatus").execute(function(data){
        me.loadInternalNotes(false);
        me.$msgDb.dialog("option", "title", "Mise à jour du devis n°"+me.data.id);
        me.$msgDb.html("Le devis a été mis à jour avec succès et renvoyé à l'adresse email suivante :<br/>"+me.data.email).dialog("open");
        $(pre_id+"-delete").remove();
      });
    });
    $(pre_id+"-resend-estimate").on("click", function(){
      var listMailsRecipients = $('input[name=secondaryContacts]') ? $('input[name=secondaryContacts]').val() : '';
      ado.setData([listMailsRecipients]).setMethod("resend").execute(function(data){
        if (data == 1)
          me.loadInternalNotes(false);
      });
    });
    $(pre_id+"-print-estimate").on("click", function(){
      if (me.data.web_id != 0)
        open(HN.TC.URL+"pdf/devis-commercial/"+me.data.web_id+"#zoom=100");
    });
    $(pre_id+"-print-pro-forma-invoice").on("click", function(){
      if (me.data.web_id != 0)
        open(HN.TC.URL+"pdf/facture-pro-format/"+me.data.web_id+"#zoom=100");
    });
    $(pre_id+"-send-fax").on("click", function(){
      if($(this).data('idItem') != '' && $(this).data('number') != '' && $(this).data('type') != '')
        if (me.data.web_id != 0)
          if(confirm('Confirmez vous l\'envoi d\'un fax au '+$(this).data('number')+'?'))
            $.sendFax($(this).attr('data-number'),$(this).data('idItem'), $(this).data('type'));
    });
    
    // button visibility state at load
    if (me.data.type == HN.TC.Estimate.TYPE_NORMAL) {
      if (me.data.status == HN.TC.Estimate.STATUS_WON)
        $(pre_id+"-create-order").after("<strong>GAGNE</strong> <span class=\"icon money_euro\"></span>").remove();
      if (me.data.order_id|0)
        $(pre_id+"-see-order-line").show();
      if (me.data.invoice_id|0)
        $(pre_id+"-see-invoice-line").show();
    }
    else if (me.data.type == HN.TC.Estimate.TYPE_AD_HOC) {
      if (me.data.status == HN.TC.Estimate.STATUS_WON)
        $(pre_id+"-set-status-won").after("<strong>GAGNE</strong> <span class=\"icon money_euro\"></span>").remove();
      else
        $(pre_id+"-set-status-won").show();
      if (me.data.invoice_id|0)
        $(pre_id+"-see-invoice-line").show();
      else
        $(pre_id+"-generate-invoice-line").show();
    }
    if (me.data.status != HN.TC.Estimate.STATUS_IN_PROCESS)
      $(pre_id+"-update-estimate, "+pre_id+"-resend-estimate").show();
    
    if (me.data.waiting_info_status & HN.TC.__MSGR_CTXT_CUSTOMER_TC_ESTIMATE__)
      $(pre_id+"-close-conv").show();
    
    // buttons
    $(pre_id+"-activity,"+
      pre_id+"-associated_gsc,"+
      pre_id+"-client_id,"+
      pre_id+"-source,"+
      pre_id+"-validity,"+
      pre_id+"-payment_mode,"+
      pre_id+"-payment_mean,"+
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
    $(pre_id+"-no_reminder").on("change", function(){
      var no_reminder = $(this).prop("checked")|0;
      if (me.data.no_reminder != no_reminder) {
        me.data.no_reminder = no_reminder;
        me.save();
      }
    });
    
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
    
    // recommended products action buttons
    $(pre_id+"-recommended-products").on("click", ".actions .icon", function(){
      var $this = $(this),
          pdt_id = $this.closest(".entry").data("id")|0;
      if ($this.hasClass("page-white-add")) {
        window.open(HN.TC.ADMIN_URL+"contacts/lead-create.php?pdtId="+pdt_id+"&idClient="+me.ci.data.email+"&idCampaign=999992")
      } else if ($this.hasClass("basket-put")) {
        me.ci.getPdtList(pdt_id, "pdt_id", true).done(function(pdtList){
          if (pdtList.length) {
            if (pdtList[0].references.length == 1)
              me.ci.addLine(new HN.TC.ItemCart.Line(pdtList[0].references[0]), 1);
            else
              me.ci.addAutocompleteLine(pdtList[0].id);
          }
        });
      }
    });
    
    me.ci.init();
    me.getPjMessFilesList();
    me.setClientFileLink();
    me.loadInternalNotes(true);
    me.loadConversation(true);
    me.data.lines[0] && me.showRecommendedProducts(me.data.lines[0].pdt_id, me.data.client_id);
  };
  
  me.showRecommendedProducts = function(pdt_id, user_id){
    user_id = parseInt(user_id, 10) || null;
    var args = [121, "products", [pdt_id, "recommendation"]];
    if (user_id !== null)
      args.push({ user: user_id });
    HN.TC.AjaxNuukik.get.apply(null, args).done(function(pdtList){
      var $recoPdts = $(pre_id+"-recommended-products"),
          $pdtList = $recoPdts.find("ul.entries");
      if (pdtList.length) {
        $pdtList.empty();
        for (var i=0; i<pdtList.length; i++) {
          var pdt = pdtList[i];
          $pdtList.append(
            "<li class=\"entry\" data-id=\""+pdt.id+"\">"+
              "<div class=\"pic\"><a href=\""+pdt.url+"\" target=\"_blank\"><img src=\""+pdt.pic+"\" class=\"vmaib\" /></a><div class=\"vsma\"></div></div>"+
              "<div class=\"title\">"+pdt.name+"</div>"+
              "<div class=\"actions\">"+
                "<span class=\"icon page-white-add\" title=\"créer un lead avec ce produit\"></span>"+
                (pdt.saleable?"<span class=\"icon basket-put\" title=\"ajouter au devis\"></span>":"")+
              "</div>"+
            "</li>"
          )
        }
        $recoPdts.show();
      } else {
        $recoPdts.hide();
      }
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
      $(pre_id+"-status").text(HN.TC.Estimate.statusList[data.status]);
      
      if (cb) cb();
    });
  };
  
}, HN.TC.Estimate);

HN.TC.Estimate.prototype = HN.TC.Cart;
HN.TC.Estimate.prototype.constructor = HN.TC.Estimate;
HN.TC.Estimate.prototype.parent = HN.TC.Cart;
