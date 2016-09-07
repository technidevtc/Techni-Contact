HN.TC.ItemCartSupp = function(pre_id){
  var me = this;
  
  me.pdtList = [];
  me.data = {};
  me.handleCartObject = null;
  
  me.init = function(){
    
    // delegated items table events
    $(pre_id+"-items")
      .on("dblclick", "td.editable", function(){
        $(this).find("span.editable").first().dblclick();
      })
      .on("dblclick", "span.editable", function(e){
        e.stopPropagation(); // prevent an infinite loop with td.editable
        var $span = $(this);
        if (!$span.data("editing")) {
          var text = $span.html(),
              $elem = null,
              $td = $span.closest("td"),
              td_class_1 = $td.attr("class").split(" ")[0];
          $span.empty();
          switch(td_class_1) {
            case "price":
            case "vat":
            case "sup-ref":
            case "sup-name":
            case "delivery-time":
              $elem = $("<input>", { type: "text", value: text });
              break;
            case "disc":
              $elem = $("<input>", { type: "text", value: text.substr(0,text.length-1) });
              break;
            case "desc":
            case "comment":
              $elem = $("<textarea>", { value: text });
              break;
          }
          if ($elem)
            $elem.appendTo(this).focus();
          $span.data("editing", true);
        }
      })
      .on("blur", "span.editable input, span.editable textarea", function(){
        var $edit = $(this),
            text = $edit.val(),
            $span = $edit.parent(),
            $td = $span.closest("td"),
            td_class_1 = $td.attr("class").split(" ")[0];
        if ($span.data("editing")) {
          switch(td_class_1) {
            case "price":
              text = sprintf("%0.2f",parseFloat(text) || 0);
              $span.html(text);
              me.updateCartCalcs();
              break;
            case "vat":
              text = parseInt(text) || 1;
              $span.html(text);
              me.updateCartCalcs();
              break;
            case "sup-ref":
            case "sup-name":
            case "delivery-time":
            case "desc":
            case "comment":
              $span.html(text);
              break;
            case "disc":
              text = parseFloat(text) || 0;
              $span.html(text+"%");
              me.updateCartCalcs();
              break;
          }
          $span.data("editing", false);
          var $tr = $td.closest("tr");
          if (!$tr.hasClass("line"))
            $tr = $tr.prevAll("tr.line").first();
          $tr.data("line-infos")[$span.data("line-info")] = text;
          
        }
      })
      .on("keydown", "span.editable input, span.editable textarea", function(e){
        if (e.keyCode == 13 && (e.target.nodeName.toLowerCase() != "textarea" || e.ctrlKey)) {
          $(this).blur();
        }
        else if (e.keyCode == 27) {
          $(this).blur();
          e.stopPropagation();
        }
      });
    
    // editable delivery fees
    $(pre_id+"-fdp-ht")
      .on("dblclick", function(){
        var text = $(this).text(),
            $input = $("<input>", { type: "text", value: text.substr(0,text.length-1) });
        $(this).data("old_val", text).empty().append($input);
        $input.focus();
      })
      .on("blur", "input", function(){
        var $td = $(this).closest("td");
        $td.empty().text($td.data("old_val"));
      })
      .on("keydown", "input", function(e){
        if (e.keyCode == 13) {
          $(this).closest("td").empty().text(sprintf("%.02f",parseFloat($(this).val()) || 0)+"€");
          me.updateCartCalcs();
        }
        else if (e.keyCode == 27) {
          $(this).blur();
          e.stopPropagation();
        }
      });
    
    // add the lines to the cart
    if (me.data) {
      var fdp_ht = me.data.fdp_ht;
      for (var li=0; li<me.data.lines.length; li++) (function(){
        var line = me.data.lines[li];
        line.sup_name = me.data.supplier.nom1;
        if (line.pdt_id != 0 && line.pdt_ref.product) {
          line.pdt_id = parseInt(line.pdt_id);
          line.pdt_ref_name = line.pdt_ref.product.product_fr.ref_name;
          line.pdt_cat_id = line.pdt_ref.product.families[0].id;
        }
        delete line.pdt_ref;
        me.addLine(line);
      }());
      if (me.getDftFdp() != fdp_ht) { // force the right fdp and lock it if it's not equal to the auto one
        me.data.fdp_ht = fdp_ht;
        $(pre_id+"-fdp-ht").text(sprintf("%.02f",parseFloat(me.data.fdp_ht)||0)+"€").data("old_val", parseFloat(me.data.fdp_ht)||0);
        me.updateCartCalcs();
      }
    }
  };

  me.addLine = function(add_infos, qty){
    var line = $.extend({
      id: 0,
      pdt_id: 0,
      pdt_ref_name: "",
      pdt_cat_id: 0,
      pdt_ref_id: 0,
      pdt_ref_hidden_id: 0,
      sup_ref: "",
      sup_id: 0,
      sup_name: "",
      desc: "",
      pau_ht: 0,
      pu_ht: 0,
      et_ht: 0,
      discount: 0,
      tva_code: 1,
      not_vpc: 0,
      sup_comment: ""
    }, add_infos);
    line.quantity = parseInt(qty) || parseInt(line.quantity) || 1;

    var image = {
      link: line.pdt_id && line.pdt_ref_name != "" && line.pdt_cat_id ? HN.TC.get_pdt_fo_url(line.pdt_id, line.pdt_ref_name, line.pdt_cat_id) : null,
      url: line.pdt_id ? HN.TC.get_pdt_pic_url(line.pdt_id,"thumb_small",1) : HN.TC.get_dft_pdt_pic_url("thumb_small")
    };
    
    line.pau_ht = sprintf("%.02f",Math.round((parseFloat(line.pau_ht)||0)*100)/100);
    
    $("<tr class=\"line\">")
      .append(
        "<td rowspan=\"3\" class=\"image\">"+(image.link?"<a href=\""+image.link+"\" target=\"_blank\">":"")+"<img src=\""+image.url+"\" alt=\"\"/>"+(image.link?"</a>":"")+"</td>"+
        "<td rowspan=\"2\" class=\"sup-ref\">"+line.sup_ref+"</td>"+
        "<td rowspan=\"2\" class=\"idtc\">"+
          (line.pdt_ref_id != 0 ? (
              (line.pdt_id != 0 ? "<a href=\""+HN.TC.get_pdt_bo_url(line.pdt_id)+"\" data-line-info=\"pdt_ref_id\">" : "<span data-line-info=\"pdt_ref_id\">")+
                line.pdt_ref_id+
              (line.pdt_id != 0 ? "</a>" : "</span>")
            ) :
            (line.pdt_ref_hidden_id != 0 ? "<span data-line-info=\"pdt_ref_hidden_id\" data-value=\""+line.pdt_ref_hidden_id+"\">"+(line.not_vpc != 0 ? "référence non en vente en ligne" : "simple produit en base")+"</span>" : "")
          )+
        "</td>"+
        "<td rowspan=\"2\" class=\"sup-name\"><a href=\""+HN.TC.get_adv_bo_url(line.sup_id)+"\" data-line-info=\"sup_id\" data-value=\""+line.sup_id+"\" class=\"_blank\"><span data-line-info=\"sup_name\">"+line.sup_name+"</span></a></td>"+
        "<td class=\"desc\" data-line-info=\"desc\">"+line.desc+"</td>"+
        "<td class=\"price\" data-line-info=\"pau_ht\">"+line.pau_ht+"</td>"+
        "<td class=\"qty\" data-line-info=\"quantity\">"+line.quantity+"</td>"+
        "<td class=\"price\" data-line-info=\"total_ht\"></td>"+
        "<td class=\"vat\" data-line-info=\"tva_code\">"+line.tva_code+"</td>")
      .data("line-infos", line)
      .appendTo(pre_id+"-items tbody");
    
    $("<tr class=\"sub-line\">")
      .append(
        "<td class=\"desc\">Éco participation</td>"+
        "<td class=\"price\" data-line-info=\"et_ht\">"+line.et_ht+"</td>"+
        "<td class=\"qty\"></td>"+
        "<td class=\"price\" data-line-info=\"et_total_ht\"></td>"+
        "<td class=\"vat\"></td>")
      .appendTo(pre_id+"-items tbody");
    
    $("<tr class=\"line-2\">")
      .append("<td colspan=\"8\" class=\"comment editable\">Comm. Four. : <span class=\"editable\" data-line-info=\"sup_comment\">"+line.sup_comment+"</span></td>")
      .appendTo(pre_id+"-items tbody");
    
    me.updateCartCalcs();
  };
  
  me.getDftFdp = function(){
    return me.data.stotal_ht < HN.TC.fdp_franco ? HN.TC.fdp : 0;
  };
  
  me.updateCartCalcs = function(){
    var d = me.data,
            tvaTable = [];
    d.stotal_ht = d.total_tva = d.total_ht = d.total_ttc = 0; // only stotal_ht remains the same
    d.fdp_ht = d.fdp_tva = d.fdp_ttc = 0;
    
    $(pre_id+"-items tbody > tr.line").each(function(){
      var $this = $(this),
          line = $this.data("line-infos");
      
      $this = $this.add($this.nextUntil("tr.line", "tr.sub-line"));
      
      line.pau_ht = parseFloat($this.find("[data-line-info='pau_ht']").text()) || 0;
      line.quantity = parseInt($this.find("[data-line-info='quantity']").text()) || 1;
      line.total_ht = line.pau_ht * line.quantity;
      line.tva_code = parseInt($this.find("[data-line-info='tva_code']").text());
      line.tva_rate = HN.TC.get_tva_rate(line.tva_code);
      line.pau_tva = line.pau_ht * line.tva_rate/100;
      line.total_tva = line.pau_tva * line.quantity;
      line.total_ttc = Math.round((line.total_ht + line.total_tva)*100)/100;
      line.et_ht = parseFloat($this.find("[data-line-info='et_ht']").text()) || 0;
      line.et_total_ht = Number((line.et_ht * line.quantity).toFixed(6));
      line.et_tva = line.et_ht * line.tva_rate/100;
      line.et_total_tva = Number((line.et_tva * line.quantity).toFixed(6));
      d.stotal_ht += line.total_ht + line.et_total_ht;
      d.total_tva += line.total_tva + line.et_total_tva;
      
			if (!tvaTable[line.tva_code])
        tvaTable[line.tva_code] = { base: 0, rate: line.tva_rate, total: 0 };
      tvaTable[line.tva_code].base += line.total_ht;
      tvaTable[line.tva_code].total += line.total_tva;
      
      $this.find("[data-line-info='total_ht']").text(sprintf("%.02f", Math.round(line.total_ht*100)/100));
      $this.find("[data-line-info='et_total_ht']").text(sprintf("%.02f", Math.round(line.et_total_ht*100)/100));
    });
    
    if ($(pre_id+"-fdp-ht").data("old_val") !== undefined)
      d.fdp_ht = parseFloat($(pre_id+"-fdp-ht").text());
    else
      d.fdp_ht = me.getDftFdp();
  
    d.fdp_tva = d.fdp_ht * HN.TC.get_tva_rate(1) / 100;
    d.fdp_ht = Math.round(d.fdp_ht*100)/100;
    d.fdp_ttc = d.fdp_ht + d.fdp_tva;
    tvaTable[1].base += d.fdp_ht;
    tvaTable[1].total += d.fdp_tva;
    
    d.total_tva = Math.round((d.total_tva + d.fdp_tva)*100)/100;
		d.total_ht = Math.round((d.stotal_ht + d.fdp_ht)*100)/100;
    d.stotal_ht = Math.round(d.stotal_ht*100)/100;
		d.total_ttc = d.total_ht + d.total_tva;
    
    // tva table
    $(pre_id+"-vat tbody").remove();
    if (tvaTable.length)
      $(pre_id+"-vat thead").after("<tbody></tbody>");
    for (tva_code in tvaTable)
      $(pre_id+"-vat tbody")
        .append("<tr>"+
                "<td class=\"label\"></td>"+
                "<td class=\"base\">"+sprintf("%.02f", Math.round(tvaTable[tva_code].base*100)/100)+"</td>"+
                "<td class=\"rate\">"+sprintf("%.02f", Math.round(tvaTable[tva_code].rate*100)/100)+"</td>"+
                "<td class=\"total\">"+sprintf("%.02f", Math.round(tvaTable[tva_code].total*100)/100)+"</td>"+
                "</tr>");
    $(pre_id+"-vat tfoot td")
      .filter(".base").text(sprintf("%.02f", d.stotal_ht)).end()
      .filter(".total").text(sprintf("%.02f", d.total_tva))
    
    // totals
    $(pre_id+"-s-total-ht").text(sprintf("%.02f", d.stotal_ht)+"€");
    $(pre_id+"-fdp-ht").text(sprintf("%.02f", d.fdp_ht)+"€");
    $(pre_id+"-total-ht").text(sprintf("%.02f", d.total_ht)+"€");
    $(pre_id+"-total-ttc").text(sprintf("%.02f", d.total_ttc)+"€");
    
  };
  
  me.saveCart = function(){
    // only update supplier comments
    $(pre_id+"-items tbody").find("tr.line").each(function(i, elem){
      me.data.lines[i].sup_comment = $(this).data("line-infos").sup_comment;
    });
  };
  
};

HN.TC.SupplierOrder = $.extend(function(_data, user_id, user_login){
  
  var me = this,
      id = _data.id,
      pre_id = "#item-cart",
      ci = new HN.TC.ItemCartSupp(pre_id),
      ado = HN.TC.AjaxDoctrineObject.create()
        .setObject('SupplierOrder')
        .setLoadQueryParams(['id = ?', id]),
      updatableFields = {
        total_ht: 1,
        total_ttc: 1,
        fdp_ht: 1,
        processing_status: 1,
        forecast_shipping_text: 1,
        cancellation: 1,
        cancellation_reason: 1,
        send_mail: 1
      };
  
  me.data = _data;
  me.pre_id = pre_id;
  ci.data = _data;
  ci.handleCartObject = ado;
  ci.user_id = user_id;
  ci.user_login = user_login;
  me.internal_note = new HN.TC.InternalNotes({
    id_reference: id,
    context: HN.TC.InternalNotes.SUPPLIER_ORDER
  });
  me.conversation = new HN.TC.Messenger({
    id_sender: user_id,
    type_sender: HN.TC.__MSGR_USR_TYPE_BOU__,
    id_recipient: me.data.sup_id,
    type_recipient: HN.TC.__MSGR_USR_TYPE_ADV__,
    context: HN.TC.__MSGR_CTXT_SUPPLIER_TC_ORDER__,
    reference_to: me.data.id
  });
  me.pjMessFile = new HN.TC.ajaxUploadFile({
    itemId: id,
    context: "supplier-order-tmppjmess",
    fileElementId: "pjMessFile"
  });
  
  var onEditFuncs = {
    "forecast_shipping_text": {
      edit: function(){ $(pre_id+"-forecast_shipping_texts").show(); },
      validate: function(){ $(pre_id+"-forecast_shipping_texts").hide(); }
    }
  };
  
  me.htmlInit = function(){
    me.parent.htmlInit.call(this);
    
    me.loadInternalNotes(true); // initial loading of internal notes
    me.loadConversation(true);
    
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
    
    $(pre_id+"-delivery-order, "+pre_id+"-purchase-order").on("click", function(){
      open(this.href, "TC_order_print", "toolbar=no, location=no, status=no, menubar=no, scrollbars=yes, height=670, width=1040");
      return false;
    });
    
    // Cancel DB
    me.$soCancelDb = $(pre_id+"-cancel-db").dialog({
      width: 500,
      autoOpen: false,
      modal: true,
      buttons: {
        "Annuler": function(){
          $(this).dialog("close");
        },
        "Confirmer l'annulation": function(){
          var $this = $(this);
          me.cancel(function(){
            $(pre_id+"-conversation").find("span.icon-fold.folded").click();
            $this.dialog("close");
          });
        }
      }
    });
    
    // ARC DB
    me.$soArcDb = $(pre_id+"-so-arc-db").dialog({
      width: 400,
      autoOpen: false,
      modal: true,
      buttons: {
        "Fermer": function(){
          $(this).dialog("close");
        },
        "Lier ARC": function(){
          $(this).find("iframe").get(0).contentDocument['so-arc'].submit();
        }
      }
    });
    
    $(pre_id+"-see-order").on("click", function(){
      window.open(HN.TC.ADMIN_URL+"orders/order-detail.php?id="+me.data.order_id, "_blank");
    });
    $(pre_id+"-cancel").on("click", function(){
      me.$soCancelDb.dialog("option", "title", "Annulation de l'ordre fournisseur n°<span class=\"green\">"+me.data.rid+"</span>");
      me.$soCancelDb.dialog("open");
    });
    $(pre_id+"-save").on("click", function(){
      me.save(function(){
        $(pre_id+"-success-msg").text("Modifications enregistrées avec succès !").stop().stop().hide().fadeIn(67).fadeOut(100).fadeIn(67).delay(4000).fadeOut(1500);
      });
    });
    
    if (me.data.cancellation|0)
      $(pre_id+"-cancel").hide();
    
    if (me.data.order.waiting_info_status & HN.TC.__MSGR_CTXT_SUPPLIER_TC_ORDER__)
      $(pre_id+"-close-conv").show();
    
    $(pre_id+"-processing_status,"+
      pre_id+"-forecast_shipping_text").next().on("click", function(){
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
        if (onEditFuncs[fieldName])
          onEditFuncs[fieldName].validate.apply($field);
      }
    });
    
    ci.init();
    me.updateArc(); // after cart items init, because it sets some vars
    me.getPjMessFilesList();
  };
  
  me.updateArc = function(){
    
    var $so_arc = $(pre_id+"-arc").empty(),
        so = me.data;
    $so_arc.append("<span class=\"icon "+(so.arc_time!=0?"accept":"cancel")+"\"></span> ");
    if (so.arc_time != 0) {
      $("<a>", {
        href: HN.TC.PDF_URL_ARC+so.arc+"#zoom=100",
        text: "Voir ARC",
        class: "_blank"
      }).appendTo($so_arc);
      $so_arc.append(" - ");
      $("<a>", {
        href: "#edit-arc",
        text: "Modifier ARC",
        click: function(){
          me.$soArcDb.dialog("option", "title", "Modifier l'ARC de l'ordre fournisseur <span class=\"green\">"+so.rid+"</span>");
          me.$soArcDb.find("iframe").attr("src", HN.TC.ADMIN_URL+"ressources/iframes/supplier-order-arc.php?id="+so.id).end().dialog("open");
          return false;
        }
      }).appendTo($so_arc);
    }
    else {
      $("<a>", {
        href: "#link-arc",
        text: "Lier ARC",
        click: function(){
          me.$soArcDb.dialog("option", "title", "Lier un ARC à l'ordre fournisseur <span class=\"green\">"+so.rid+"</span>");
          me.$soArcDb.find("iframe").attr("src", HN.TC.ADMIN_URL+"ressources/iframes/supplier-order-arc.php?id="+so.id).end().dialog("open");
          return false;
        }
      }).appendTo($so_arc);
    }
  };
  
  me.onArcLinked = function(){
    me.refresh(function(){
      me.$soArcDb.dialog("close");
    });
  };
  
  me.cancel = function(){
    $(pre_id+"-processing_status").val(HN.TC.SupplierOrder.PROCESSING_STATUS_CANCELLED);
    me.save.apply(me, arguments);
  };
  
  me.save = function(){
    var cb = $.isFunction(arguments[0]) ? arguments[0] : false;
    
    ci.saveCart();
    var udata = {};
    $(".c_i").each(function(){
      var $this = $(this),
          fn = $this.data("cart-info");
      if (updatableFields[fn])
        udata[fn] = me.data[fn] = $this.data("info-value")
                                  || ($this.attr("type") == "checkbox" ? $this.prop("checked")|0 : ($.trim($this.val()) || $.trim($this.text())));
    });
    udata.lines = [];
    for (var li=0; li<me.data.lines.length; li++)
      udata.lines.push({ id: me.data.lines[li].id, sup_comment: me.data.lines[li].sup_comment });
    
    udata.total_ht = me.data.total_ht;
    udata.total_ttc = me.data.total_ttc;
    udata.fdp_ht = me.data.fdp_ht;
    udata.fdp_ttc = me.data.fdp_ttc;
    
    ado.setData([udata]).setMethod("updateWithLines").execute(function(data){
      $.extend(me.data, data);
      me.updateArc();
      me.loadInternalNotes(false);
      me.loadConversation(false);
      if (me.data.cancellation|0) {
        $(pre_id+"-cancel").hide();
        $(pre_id).addClass("cancelled").parent().addClass("cancelled");
      }
      else {
        $(pre_id+"-cancel").show();
        $(pre_id).removeClass("cancelled").parent().removeClass("cancelled");
      }
      if (cb) cb();
    });
  };
  
  me.refresh = function(){
    var cb = $.isFunction(arguments[0]) ? arguments[0] : false;
    HN.TC.AjaxQuery.create()
      .select('so.*,'+
              's.*,'+
              'o.*,'+
              'sos.name,'+
              'ol.*,'+
              'r.id,'+
              'r.refSupplier,'+
              'rp.id,'+
              'rpfr.ref_name,'+
              'rpf.id,'+
              'rh.sup_id,'+
              'rh.sup_ref,'+
              'rh.not_vpc')
      .from('SupplierOrder so')
      .innerJoin('so.supplier s')
      .innerJoin('so.order o')
      .leftJoin('so.sender sos')
      .innerJoin('o.lines ol')
      .leftJoin('ol.pdt_ref r')
      .leftJoin('r.product rp')
      .leftJoin('rp.product_fr rpfr')
      .leftJoin('rp.families rpf')
      .leftJoin('ol.pdt_ref_hidden rh')
      .where('so.id = ?', id)
      .andWhere('ol.sup_id = so.sup_id')
      .fetchOne(function(data){
        if (data) {
          delete data.lines;
          $.extend(me.data, data);
          me.updateHtmlData(data);
          me.updateArc();
          if (cb) cb();
        }
      });
  };
  
}, HN.TC.SupplierOrder);

HN.TC.SupplierOrder.prototype = HN.TC.Cart;
HN.TC.SupplierOrder.prototype.constructor = HN.TC.SupplierOrder;
HN.TC.SupplierOrder.prototype.parent = HN.TC.Cart;
