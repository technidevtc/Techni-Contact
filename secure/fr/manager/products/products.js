if (!window.HN) HN = window.HN = {};
if (!HN.TC) HN.TC = {};
if (!HN.TC.BO) HN.TC.BO = {};
if (!HN.TC.BO.Pdt) HN.TC.BO.Pdt = {};

HN.TC.BO.Pdt.Init = function (_pdtID, _type) {
  pdm = new HN.TC.BO.Pdt.pdm($("#doc-selection table.doc-selection-table tbody").get(0), _pdtID, _type);
  $("#btn-add-doc")
    .mousedown(function(){ this.className = "down"; })
    .mouseup(function(){ if (this.className == "down") { this.className = ""; pdm.add(); } })
    .mouseleave(function(){ this.className = ""; });
  $("#btn-save-doc").click(function(){
    pdm.save();
    return false;
  });
  pdm.load();
  
  PDSDB = new HN.Mods.DialogBox("PDSDB");
  PDSDB.setTitleText("Choisir un document (PDF)");
  PDSDB.setMovable(true);
  PDSDB.showCloseButton(true);
  PDSDB.Build();
  
  // products linked on the product's cart
  window.rpm = new HN.TC.BO.Pdt.RelatedProductManager();
  rpm.init($("input[name='productsLinked']").val().split(","));
};

HN.TC.BO.Pdt.RelatedProductManager = function(){
  var me = this,
      pre_id = "#linked-products",
      $pdtLinked,
      $pdtLinkedList,
      laaci = null; // Last Active Auto Complete Input
  
  me.pdtList = [];
  
  me.init = function(pdtIdsInit){
    $pdtLinked = $(pre_id);
    $pdtLinkedList = $pdtLinked.find("table tbody");
    
    $("#linked-products-add-line").on("click", function(){
      me.addAutocompleteLine();
    });
    
    $pdtLinked.on("click", "td.action .icon", function(){
      var $icon = $(this),
          $tr = $icon.closest("tr");
      if ($icon.hasClass("arrow-up")) {
        $tr.insertBefore($tr.prev());
      } else if ($icon.hasClass("arrow-down")) {
        $tr.insertAfter($tr.next());
      } else if ($icon.hasClass("table-delete")) {
        me.deleteLine($tr);
      }
    });
    
    // a little trick to avoid auto add when focusout is fired from the auto complete pdt search as we click on the product detail layer
    var preventAutoAdd = false;
    $(pre_id+"-pdt-detail")
      .on("mousedown", function(){ preventAutoAdd = true; })
      .on("mouseup", function(){ preventAutoAdd = false; });
    
    // delegated items table events
    $pdtLinked.on("keyup blur", "input.autocomplete-ref-search", function(e){ // autocomplete pdt search
      var input = this,
          val = $(input).val(),
          w = $(input).outerWidth(),
          pos = $(input).position(),
          delay = (e.type == "focusout" && !preventAutoAdd) || e.keyCode == 13 ? 0 : 500;
          
      if (!$(input).data("line_added_count"))
        $(input).data("line_added_count", 0);
      
      if (val != "") {
        var isIdList = /\d+(\s*,\s*\d+)+/.test(val);
        if (isIdList)
          val = val.replace(/\s+/g, "").split(",");
        me.getPdtListDelayed(val, delay, function(justLoaded){
          if (justLoaded || input != laaci || e.keyCode == 13) {
            laaci = input; // this is the latest active input
            if (me.pdtList.length) {
              if (me.pdtList.length == 1) { // only one product = directly show product detail layer if there is at least 1 reference
                me.hidePdtPreviews();
                me.showPdtDetail(0, w+pos.left, pos.top-120);
              } else {
                me.showPdtPreviews(w+pos.left, pos.top - me.pdtList.length*25); // show pre-selection layer
                me.hidePdtDetail();
              }
            } else { // nothing to show, hide everything
              me.hidePdtPreviews();
              me.hidePdtDetail();
            }
          }
          // one pdt and only one item that can be added -> we add it directly if there is no delay
          if (delay == 0) {
            if (isIdList) {
              $(pre_id+"-pdt-preview").find("li").each(function(){
                me.addLine($(this).data("line"));
              });
              me.hidePdtPreviews();
              me.hidePdtDetail();
              me.deleteLine($(laaci).closest("tr"));
            } else if (me.pdtList.length == 1) {
              me.addLine($(pre_id+"-pdt-preview").find("li").first().data("line"));
              me.hidePdtPreviews();
              me.hidePdtDetail();
              me.deleteLine($(laaci).closest("tr"));
            }
          }
        });
      }
    });
    
    $(window)
      .on("keydown", function(e){
        if (e.keyCode == 27) {
          me.hidePdtPreviews()
          me.hidePdtDetail();
          if ($(laaci).data("line_added_count") >= 1)
            me.deleteLine($(laaci).closest("tr"));
        }
      })
      .on("click", function(e){
        if (!$(e.target).closest(pre_id+"-pdt-preview, "+pre_id+"-pdt-detail, "+pre_id+" input.autocomplete-ref-search").length) {
          me.hidePdtPreviews()
          me.hidePdtDetail();
          if ($(laaci).data("line_added_count") >= 1)
            me.deleteLine($(laaci).closest("tr"));
        }
      });
    
    if (pdtIdsInit && pdtIdsInit.length) {
      me.getPdtList(pdtIdsInit, function(){
        for (var i=0; i<me.pdtList.length; i++)
          me.addLine(me.pdtList[i]);
      });
    }
  };
  
  me.addAutocompleteLine = function(){
    $pdtLinkedList.append(
      "<tr class=\"ac-line\">"+
        "<td colspan=\"5\">"+
          "<input type=\"text\" value=\"\" placeholder=\"idTC/Ref Fourn./id ou Nom Produit\" class=\"autocomplete-ref-search\" />"+
        "</td>"+
        "<td class=\"action\">"+
          "<span class=\"icon table-delete\"></span>"+
        "</td>"+
      "</tr>");
  };
  
  me.addLine = function(line){
        //  2385793 , 4626458 , 12391135 , 6518 , 2385061
    if (!$pdtLinkedList.find("tr.line[data-id='"+line.id+"']").length) {
      var image = {
        link: line.id|0 && line.ref_name != "" && line.cat_id|0 ? HN.TC.get_pdt_fo_url(line.id, line.ref_name, line.cat_id) : null,
        url: line.id|0 ? HN.TC.get_pdt_pic_url(line.id,"thumb_small",1) : HN.TC.get_dft_pdt_pic_url("thumb_small")
      };
      $("<tr class=\"line\" data-id=\""+line.id+"\">")
        .append(
          "<td class=\"image\">"+
            (image.link?"<a href=\""+image.link+"\" target=\"_blank\">":"")+
              "<img src=\""+image.url+"\" alt=\"\"/>"+
            (image.link?"</a>":"")+
          "</td>"+
          "<td class=\"id\">"+
            (line.id|0 ? "<a href=\""+HN.TC.get_pdt_bo_url(line.id)+"\" target=\"_blank\">" : "<span>")+
              (line.id || "<i>non défini</i>")+
            (line.id|0 ? "</a>" : "</span>")+
          "</td>"+
          "<td class=\"desc\">"+line.desc+"</td>"+
          "<td class=\"cat-name\">"+
            "<a href=\""+HN.TC.get_fam_pdt_list_url(line.cat_id)+"\" target=\"_blank\">"+
              line.cat_name+
            "</a>"+
          "</td>"+
          "<td class=\"partner-name\">"+
            "<a href=\""+HN.TC.get_adv_bo_url(line.partner_id)+"\" target=\"_blank\">"+
              line.partner_name+
            "</a>"+
          "</td>"+
          "<td class=\"action\">"+
            "<span class=\"icon arrow-up\"></span>"+
            "<span class=\"icon arrow-down\"></span>"+
            "<span class=\"icon table-delete\"></span>"+
          "</td>"
        )
        .data("line", line)
        .appendTo($pdtLinkedList);
      
      $(laaci).data("line_added_count", $(laaci).data("line_added_count")+1);
    }
  };
  
  me.showPdtPreviews = function(x, y){
    $(pre_id+"-pdt-preview").empty().css({ left: x, top: y }).show();
    for (var i=0; i<me.pdtList.length; i++) (function(){
      var pi = i,
          pdt = me.pdtList[pi];
      $("<li>")
        .append("<div class=\"picture\"><img class=\"vmaib\" src=\""+HN.TC.get_pdt_pic_url(pdt.id,"thumb_small",1)+"\"/><div class=\"vsma\"></div></div>"+
                "<div class=\"infos\">"+
                  "<div class=\"vmaib\">"+
                    "<div><strong>"+pdt.id+" - "+pdt.ref_name+"</strong></div>"+
                    "<div>"+pdt.fastdesc+"</div>"+
                    "<div>Partenaire : <strong>"+pdt.partner_name+"</strong></div>"+
                  "</div><div class=\"vsma\"></div>"+
                "</div>"+
                "<div class=\"zero\"></div>")
        .on("mouseenter", function(){
          var ul_pos = $(pre_id+"-pdt-preview").position(),
              li_pos = $(this).position(),
              ul_w = $(pre_id+"-pdt-preview").outerWidth();
          me.showPdtDetail(pi, ul_pos.left+li_pos.left+ul_w-2, ul_pos.top+li_pos.top-pi*7-50);
        })
        .on("click", function(){
          me.addLine($(this).data("line"));
        })
        .data("line", pdt)
        .appendTo(pre_id+"-pdt-preview");
    }());
  };
  
  me.hidePdtPreviews = function(){
    $(pre_id+"-pdt-preview").hide();
  };
  
  me.showPdtDetail = function(pi, x, y){
    var pre_id_detail = pre_id+"-pdt-detail";
        pdt = me.pdtList[pi],
        refs = pdt.references || [];
    $(pre_id_detail).css({ left: x, top: y }).show();
    $(pre_id_detail+"-pic").attr("src", HN.TC.get_pdt_pic_url(pdt.id,"thumb_big",1));
    $(pre_id_detail+"-p-fo-url").attr("href", HN.TC.get_pdt_fo_url(pdt.id,pdt.ref_name,pdt.cat_id));
    $(pre_id_detail+"-p-bo-url").attr("href", HN.TC.get_pdt_bo_url(pdt.id));
    $(pre_id_detail+"-name").text(pdt.name);
    $(pre_id_detail+"-p-fastdesc").text(pdt.fastdesc);
    $(pre_id_detail+"-p-id").text(pdt.id);
    $(pre_id_detail+"-f-bo-pdt-list-url").attr("href", HN.TC.get_fam_pdt_list_url(pdt.cat_id));
    $(pre_id_detail+"-f-name").text(pdt.cat_name);
    $(pre_id_detail+"-a-bo-url").attr("href", HN.TC.get_adv_bo_url(pdt.partner_id));
    $(pre_id_detail+"-a-name").text(pdt.partner_name);
    $(pre_id_detail+"-references")[refs.length?"show":"hide"]().find("tbody").empty();
    for (var ri=0; ri<refs.length; ri++) {
      var ref = refs[ri];
      $("<tr>")
        .append("<td class=\"idtc\">"+ref.pdt_ref_id+"</td>"+
                "<td class=\"sup-ref\">"+ref.sup_ref+"</td>"+
                "<td class=\"desc\">"+ref.desc+"</td>"+
                "<td class=\"price\">"+sprintf("%.02f",parseFloat(ref.pau_ht))+"€ HT</td>"+
                "<td class=\"price\">"+sprintf("%.02f",parseFloat(ref.pu_ht))+"€ HT</td>")
        .appendTo(pre_id_detail+"-references tbody");
    }
  };
  
  me.hidePdtDetail = function(){
    $(pre_id+"-pdt-detail").hide();
  };
  
  me.deleteLine = function(tr){
    $(tr).remove();
  };
  
  me.getPdtIdsString = function(){
    var ids = [];
    $pdtLinkedList.find("tr.line").each(function(){
      ids.push($(this).data("id"));
    });
    return ids.join(",");
  }
};

HN.TC.BO.Pdt.RelatedProductManager.prototype = {
  sortByPos: function(o1, o2){
    return o1.position - o2.position;
  },
  sortRefs: function(r1,r2){
    return r1.classement - r2.classement;
  },
  getPdtListLastQuery: null,
  getPdtListTO: null,
  getPdtListDelayed: function(query, delay, cb){
    var me = this;
    clearTimeout(me.getPdtListTO);
    me.getPdtListTO = setTimeout(function(){
      if (query != me.getPdtListLastQuery) {
        me.getPdtListLastQuery = query;
        me.getPdtList(query, cb);
      } else {
        cb(false);
      }
    }, delay);
  },
  getPdtList: function(q, cb){
    var me = this, i,
        isIdList = $.isArray(q),
        IdListPos = {},
        mq = HN.TC.AjaxMultiQueries.create(),
        bq = HN.TC.AjaxQuery.create()
          .select("p.id,"+
                  "pfr.name AS name,"+
                  "pfr.ref_name AS ref_name,"+
                  "pfr.fastdesc AS fastdesc,"+
                  "f.id AS cat_id,"+
                  "ffr.name AS cat_name,"+
                  "a.id AS partner_id,"+
                  "a.nom1 AS partner_name,"+
                  "r.id,"+
                  "r.idProduct AS pdt_id,"+
                  "r.id AS pdt_ref_id,"+
                  "r.sup_id,"+
                  "r.refSupplier AS sup_ref,"+
                  "r.label,"+
                  "r.label_long,"+
                  "r.price2 AS pau_ht,"+
                  "r.price AS pu_ht,"+
                  "r.idTVA AS tva_code,"+
                  "r.content,"+
                  "r.classement,"+
                  "rh.content")
          .from("Products p")
          .innerJoin("p.product_fr pfr")
          .innerJoin("p.families f")
          .innerJoin("f.family_fr ffr")
          .innerJoin("p.advertiser a")
          .leftJoin("p.references r")
          .leftJoin("r.headers rh")
          .where("a.actif = ?", 1)
          .andWhere("pfr.active = ?", 1)
          .andWhere("pfr.deleted = ?", 0)
          .andWhere("r.deleted IS NULL OR (r.vpc = ? AND r.deleted = ?)", [1,0]);
    
    if (isIdList) {
      IdListPos = {};
      for (i=0; i<q.length; i++)
        IdListPos[q[i]] = i+1;
      mq.addQuery(bq.clone().andWhereIn("p.id", q));
    } else {
      if ($.isNumeric(q)) {
        var p_id_iv = HN.TC.AjaxQuery.getQueryNumIntervals("p.id", q, true),
            r_id_iv = HN.TC.AjaxQuery.getQueryNumIntervals("r.id", q, true);
        mq.addQuery(bq.clone().andWhere(p_id_iv[0], p_id_iv[1]))
          .addQuery(bq.clone().andWhere(r_id_iv[0], r_id_iv[1]));
      }
      mq.addQuery(bq.clone().andWhere("MATCH (r.refSupplier) AGAINST (? IN BOOLEAN MODE)", q+"*")); // search in supplier refs grouped by product
      mq.addQuery(bq.clone().andWhere("MATCH (pfr.name) AGAINST (? IN BOOLEAN MODE)", q+"*")); // search in product's name
    }
    
    mq.setLinkedLimit(10).execute(function(data){
      for (var di=0; di<data.length; di++) {
        for (var pi=0; pi<data[di].length; pi++) { // each product
          var pdt = data[di][pi];
          pdt.desc = pdt.name+"\n"+pdt.fastdesc;
          if (pdt.references) {
            pdt.references.sort(me.sortRefs);
            for (var ri=0; ri<pdt.references.length; ri++) { // each references
              var ref = pdt.references[ri];
              $.extend(ref, {
                id: 0,
                sup_name: pdt.sup_name,
                pdt_ref_name: pdt.ref_name,
                pdt_cat_id: pdt.cat_id,
                delivery_time: pdt.delivery_time
              });
              // desc rule :
              if (ref.label_long != "") { // long label if there is one
                ref.desc = ref.label_long;
              } else if (ref.c_cols.length) { // or label + tech cols if there is any
                ref.desc = ref.label;
                for (var cci=0; cci<ref.c_cols.length; cci++)
                  ref.desc += " - "+ref.headers.c_headers[cci]+": "+ref.c_cols[cci];
              } else { // or pdt name + label
                ref.desc = pdt.name+(ref.label!="" ? " - "+ref.label : "");
              }
            }
          }
        }
      }
      me.pdtList = [];
      for (i=0; i<data.length; i++)
        me.pdtList = me.pdtList.concat(data[i]);
      if (isIdList) {
        for (i=0; i<me.pdtList.length; i++)
          me.pdtList[i].position = IdListPos[me.pdtList[i].id];
        me.pdtList.sort(me.sortByPos);
      }
      cb(true);
    });
  }
};

// pdm : Product's Documents Manager
HN.TC.BO.Pdt.pdm = function (_DOMhdl, _pdtID, _type) {
  this.count = 0;
  this.DOMhdl = typeof _DOMhdl == "string" ? document.getElementById(_DOMhdl) : _DOMhdl;
  this.$DOMhdl = $(this.DOMhdl);
  this.pdtID = _pdtID;
  this.type = (_type == "add_adv" || _type == "edit_adv") ? "adv" : "";
  var table = document.createElement("table");
  table.innerHTML = "\<tbody><tr>\
      <td><input name=\"doc_title\" type=\"text\" class=\"doc-title\"/></td>\
      <td><input name=\"doc_filename\" type=\"text\" class=\"doc-filename\"/></td>\
      <td><input name=\"doc_num\" type=\"hidden\"/><input type=\"button\"/ class=\"btn-doc-num\" value=\"Uploader le document\"><div class=\"doc-uploaded\"></div></td>\
      <td><div class=\"doc-filesize\"></div></td>\
      <td class=\"actions\">\
        <div class=\"ib icon icon-up\" title=\"monter\"></div>\
        <div class=\"ib icon icon-down\" title=\"descendre\"></div>\
        <div class=\"ib icon icon-del\" title=\"supprimer\"></div>\
      </td>\
    </tr></tbody>";
  this.baseRow = $(table).find("tr").get(0);
  //alert($(this.baseRow).find("td").html());
};
HN.TC.BO.Pdt.pdm.prototype = {
  baseRow: null,
  getBaseRow: function () {
    return $(this.baseRow).clone(true).get(0);
  },
  offsetIndex: 1,
  add: function () {
    var me = this;
    var title = "", filename = "", num = me.$DOMhdl.find("tr").length+1, uploaded = 0, filesize = 0;
    if (arguments.length > 0) title = arguments[0];
    if (arguments.length > 1) filename = arguments[1];
    if (arguments.length > 2) num = arguments[2];
    if (arguments.length > 3) uploaded = arguments[3];
    if (arguments.length > 4) filesize = arguments[4];
    
    var tr = this.getBaseRow();
    var $tr = $(tr);
    $tr.find("input[name='doc_title']").val(title);
    $tr.find("input[name='doc_filename']").val(filename);
    $tr.find("input[name='doc_num']").val(num);
    if (uploaded) {
      $tr.find("div.doc-uploaded").addClass(uploaded==1?"uploaded":"confirmed");
      $tr.find("div.doc-filesize").html(filesize);
    }
    
    $tr.find("input.btn-doc-num").click(function(){
      window.frames["doc-selection-iframe"].location.href = "product-doc.php?id="+me.pdtID+"&type="+me.type+"&num="+num;
      PDSDB.Show();
    });
    $tr.find("div.icon-up").click(function(){
      var tr_index = parseInt($("tr", me.DOMhdl).index(tr));
      if (tr_index > 0) {
        me.DOMhdl.insertBefore(tr, me.$DOMhdl.find("tr").get(tr_index-1));
      }
    });
    $tr.find("div.icon-down").click(function(){
      var $trs = $("tr", me.DOMhdl);
      var tr_index = parseInt($trs.index(tr));
      if (tr_index < $trs.length-1) {
        me.DOMhdl.insertBefore(me.$DOMhdl.find("tr").get(tr_index+1), tr);
      }
    });
    $tr.find("div.icon-del").click(function(){
      me.del(parseInt(me.$DOMhdl.find("tr").index(tr)));
    });
    
    this.DOMhdl.appendChild(tr);
  },
  del: function (tr_index) {
    this.$DOMhdl.find("tr:eq("+tr_index+")").remove();
  },
  clear: function () {
    this.$DOMhdl.find("tr").remove();
    this.count = 0;
  },
  getSerializedData: function () {
    var s = ""; i = 0;
    this.$DOMhdl.find("tr").each(function(){
      var $tr = $(this);
      s += "&title"+i+"="+escape($tr.find("input[name='doc_title']").val())
      + "&filename"+i+"="+escape($tr.find("input[name='doc_filename']").val())
      + "&num"+i+"="+escape($tr.find("input[name='doc_num']").val());
      i++;
    });
    return s;
  },
  load: function(){
    var me = this;
    $.ajax({
      async: true,
      cache: false,
      data: "action=getdocs&pdtID="+this.pdtID+"&type="+this.type,
      dataType: "json",
      error: function (XMLHttpRequest, textStatus, errorThrown) { alert("Fatal error while loading the Document List"); },
      success: function (data, textStatus) {
        if (data.error)
          alert(data.error);
        else {
          me.clear();
          for (var i=0; i < data.docs.length; i++) {
            me.add(
              data.docs[i].title,
              data.docs[i].filename,
              data.docs[i].num,
              data.docs[i].uploaded,
              data.docs[i].filesize);
          }
        }
      },
      timeout: 10000,
      type: "GET",
      url: "AJAX_pdt-edition.php"
    });
  },
  save: function(){
    var me = this;
    $.ajax({
      async: true,
      cache: false,
      data: "action=setdocs&pdtID="+this.pdtID+"&type="+this.type+this.getSerializedData(),
      dataType: "json",
      error: function (XMLHttpRequest, textStatus, errorThrown) { alert("Fatal error while saving the Document List"); },
      success: function (data, textStatus) {
        if (data.error)
          alert(data.error);
        else {
          me.clear();
          for (var i=0; i < data.docs.length; i++) {
            me.add(
              data.docs[i].title,
              data.docs[i].filename,
              data.docs[i].num,
              data.docs[i].uploaded,
              data.docs[i].filesize);
          }
          alert("Modification effectuée avec succés !");
        }
      },
      timeout: 10000,
      type: "GET",
      url: "AJAX_pdt-edition.php"
    });
  },
  setUploaded: function(num, uploaded, filesize) {
    var $tr, found = false;
    this.$DOMhdl.find("tr").each(function(){
      $tr = $(this);
      if ($tr.find("input[name='doc_num']").val() == num) {
        found = true;
        return false;
      }
    });
    
    if (found) {
      $tr.find("div.doc-uploaded").removeClass("uploaded").removeClass("confirmed");
      if (uploaded) {
        $tr.find("div.doc-uploaded").addClass(uploaded==1?"uploaded":"confirmed");
        $tr.find("div.doc-filesize").html(filesize);
      }
    }
  }
};
