HN.TC.Invoice = $.extend(function(_data, user_id, user_login){
  
  var me = this,
      id = _data.id,
      pre_id = "#item-cart",
      ado = HN.TC.AjaxDoctrineObject.create()
        .setObject('Invoice')
        .setLoadQueryParams(['id = ?', id]);
  
  me.data = _data;
  me.pre_id = pre_id;
  me.ci = new HN.TC.ItemCart({
    pre_id: pre_id,
    handleCartObject: ado,
    user_id: user_id,
    user_login: user_login,
    onUpdate: function(){ me.saveDelayed(); }
  }, _data);
  me.internal_note = new HN.TC.InternalNotes({
    id_reference: id,
    context: HN.TC.InternalNotes.INVOICE
  });
  me.conversation = new HN.TC.Messenger({
    id_sender: user_id,
    type_sender: HN.TC.__MSGR_USR_TYPE_BOU__,
    id_recipient: me.data.client_id,
    type_recipient: HN.TC.__MSGR_USR_TYPE_INT__,
    context: HN.TC.__MSGR_CTXT_CUSTOMER_TC_INVOICE__,
    reference_to: id
  });
  
  var onEditFuncs = {
    "activity": {
      edit: function(){ },
      validate: function(){
        if ($(this).data("old_val") != $(this).val()) {
          me.updateCartActivityState();
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
    "due_date": {
      edit: function(){
        $(this).data("datepicker").dpDiv.removeClass("hidden"); // hack to "hide" the datepicker
      },
      validate: function(){
        var $this = $(this),
            t = HN.TC.get_timestamp($this.val());
        $this.data("datepicker").dpDiv.addClass("hidden");
        $this.data("info-value", t);
        $this.val(HN.TC.get_formated_datetime(t));
        me.save();
      }
    },
    "payment_mean": {
      edit: function(){},
      validate: function(){
        if ($(this).data("old_val") != $(this).val()) {
          me.setClientCode();
          me.save();
        }
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
    
    $(pre_id+"-save-invoice").on("click", function(){
      me.save(function(){
        $(pre_id+"-success-msg").text("Modifications enregistrées avec succès !").stop().stop().hide().fadeIn(67).fadeOut(100).fadeIn(67).delay(4000).fadeOut(1500);
      });
    });
    $(pre_id+"-see-order").on("click", function(){
      if (me.data.order_id|0)
        window.open(HN.TC.ADMIN_URL+"orders/order-detail.php?id="+me.data.order_id, "_blank");
    });
    $(pre_id+"-see-estimate").on("click", function(){
      if (me.data.estimate_id|0)
        window.open(HN.TC.ADMIN_URL+"estimates/estimate-detail.php?id="+me.data.estimate_id, "_blank");
    });
    $(pre_id+"-see-invoice").on("click", function(){
      if (me.data.invoice_rid|0)
        window.open(HN.TC.ADMIN_URL+"invoices/invoice-detail.php?rid="+me.data.invoice_rid, "_blank");
    });
    $(pre_id+"-see-credit-note").on("click", function(){
      if (me.data.credit_note_id|0)
        window.open(HN.TC.ADMIN_URL+"invoices/invoice-detail.php?id="+me.data.credit_note_id, "_blank");
    });
    $(pre_id+"-generate-credit-note").on("click", function(){
      ado.setData(null).setMethod("generateCreditNote").execute(function(data){
        if (data|0) {
          me.data.credit_note_id = data;
          $(pre_id+"-see-credit-note").show();
        }
      });
    });
    $(pre_id+"-validate").on("click", function(){
      ado.setData([1,$(pre_id+"-send_invoice_mail").prop("checked")&1,$('input[name=secondaryContacts]').val()]).setMethod("validate").execute(function(data){
        if (data && data.rid) {
          $.extend(me.data, data);
          if (data.due_date|0)
            $(pre_id+"-due_date").val(HN.TC.get_formated_datetime(data.due_date));
          $(pre_id+"-infos .c_i[data-cart-info='issued']").val(HN.TC.get_formated_datetime(data.issued));
          $(pre_id+"-updated").text(HN.TC.get_formated_datetime(data.updated));
          $("#sub-nav .page-title h1").text(HN.TC.Invoice.typeList[me.data.type|0]+" n°"+data.rid); // update title
          me.loadInternalNotes(true);
          $(pre_id+"-generate-credit-note").show();
          $(pre_id+"-validation").remove();
          $(pre_id+"-resend").show();
        }
      });
    });
    $(pre_id+"-resend").on("click", function(){
      var params = new Array(me.data.status,me.data.type,$('input[name=secondaryContacts]').val());
      ado.setData(params).setMethod("sendMail").execute(function(data){
        if (data == 1)
          me.loadInternalNotes(true);
      });
    });
    $(pre_id+"-print").on("click", function(){
      if (me.data.web_id)
        open(HN.TC.URL+"pdf/"+HN.TC.Invoice.typeList[me.data.type|0].toLowerCase()+"/"+me.data.web_id+"#zoom=100");
    });
    $(pre_id+"-fonction").on("change", function(){
      var checked = $(this).prop("checked"),
          new_fonction = checked ? "Je suis un particulier" : "";
      if (me.data.fonction != new_fonction) {
        me.data.fonction = new_fonction;
        me.save();
      }
    });
    
    if (me.data.status == HN.TC.Invoice.STATUS_NOT_VALIDATED)
      $(pre_id+"-validation").show();
    else
      $(pre_id+"-resend").show();
    
    if (me.data.type == HN.TC.Invoice.TYPE_INVOICE) {
      if (me.data.credit_note_id|0)
        $(pre_id+"-see-credit-note").show();
      if (me.data.status == HN.TC.Invoice.STATUS_VALIDATED)
        $(pre_id+"-generate-credit-note").show();
      $(pre_id+"-due_date").datepicker().data("datepicker").dpDiv.addClass("hidden");
    }
    
    // buttons
    $(pre_id+"-activity,"+
      pre_id+"-client_id,"+
      pre_id+"-fonction,"+
      pre_id+"-order_id,"+
      pre_id+"-estimate_id,"+
      pre_id+"-invoice_rid,"+
      pre_id+"-due_date,"+
      pre_id+"-payment_mode,"+
      pre_id+"-payment_mean,"+
      pre_id+"-code,"+
      pre_id+"-email").next("button").on("click", function(){
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
          me.setClientCode();
          me.save();
        });
      },
      disabled: true
    });
    
    me.ci.init();
    me.setClientFileLink();
    me.loadInternalNotes(true);
    me.loadConversation(true);
  };
  
  me.setClientCode = function(){
    var $field = $(pre_id+"-code"),
        val = "";
    if (me.data.payment_mean == HN.TC.Invoice.PAYMENT_MEAN_PAYPAL)
      val = "9PAYPAL";
    else
      val = me.data.client_code || "9"+me.data.societe.substr(0,1).toUpperCase()+me.data.client_id;
    $field.val(val);
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
      
      if (cb) cb();
    });
  };
  
}, HN.TC.Invoice);

HN.TC.Invoice.prototype = HN.TC.Cart;
HN.TC.Invoice.prototype.constructor = HN.TC.Invoice;
HN.TC.Invoice.prototype.parent = HN.TC.Cart;
