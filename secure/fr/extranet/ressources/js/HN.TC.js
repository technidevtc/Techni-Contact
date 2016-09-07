// usefull global functions
function str_repeat(i, m){ for (var o = []; m > 0; o[--m] = i); return(o.join('')); }
function sprintf(){
  var i = 0, a, f = arguments[i++], o = [], m, p, c, x;
  while (f) {
    if (m = /^[^\x25]+/.exec(f)) o.push(m[0]);
    else if (m = /^\x25{2}/.exec(f)) o.push('%');
    else if (m = /^\x25(?:(\d+)\$)?(\+)?(0|'[^$])?(-)?(\d+)?(?:\.(\d+))?([b-fosuxX])/.exec(f)) {
      if (((a = arguments[m[1] || i++]) == null) || (a == undefined)) throw("Too few arguments.");
      if (/[^s]/.test(m[7]) && (typeof(a) != 'number'))
        throw("Expecting number but found " + typeof(a));
      switch (m[7]) {
        case 'b': a = a.toString(2); break;
        case 'c': a = String.fromCharCode(a); break;
        case 'd': a = parseInt(a); break;
        case 'e': a = m[6] ? a.toExponential(m[6]) : a.toExponential(); break;
        case 'f': a = m[6] ? parseFloat(a).toFixed(m[6]) : parseFloat(a); break;
        case 'o': a = a.toString(8); break;
        case 's': a = ((a = String(a)) && m[6] ? a.substring(0, m[6]) : a); break;
        case 'u': a = Math.abs(a); break;
        case 'x': a = a.toString(16); break;
        case 'X': a = a.toString(16).toUpperCase(); break;
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

/**
 * jQuery hashchange event - v1.3 - 7/21/2010
 * http://benalman.com/projects/jquery-hashchange-plugin/
 * 
 * Copyright (c) 2010 "Cowboy" Ben Alman
 * Dual licensed under the MIT and GPL licenses.
 * http://benalman.com/about/license/
 */
(function($,e,b){var c="hashchange",h=document,f,g=$.event.special,i=h.documentMode,d="on"+c in e&&(i===b||i>7);function a(j){j=j||location.href;return"#"+j.replace(/^[^#]*#?(.*)$/,"$1")}$.fn[c]=function(j){return j?this.bind(c,j):this.trigger(c)};$.fn[c].delay=50;g[c]=$.extend(g[c],{setup:function(){if(d){return false}$(f.start)},teardown:function(){if(d){return false}$(f.stop)}});f=(function(){var j={},p,m=a(),k=function(q){return q},l=k,o=k;j.start=function(){p||n()};j.stop=function(){p&&clearTimeout(p);p=b};function n(){var r=a(),q=o(m);if(r!==m){l(m=r,q);$(e).trigger(c)}else{if(q!==m){location.href=location.href.replace(/#.*/,"")+q}}p=setTimeout(n,$.fn[c].delay)}$.browser.msie&&!d&&(function(){var q,r;j.start=function(){if(!q){r=$.fn[c].src;r=r&&r+a();q=$('<iframe tabindex="-1" title="empty"/>').hide().one("load",function(){r||l(a());n()}).attr("src",r||"javascript:0").insertAfter("body")[0].contentWindow;h.onpropertychange=function(){try{if(event.propertyName==="title"){q.document.title=h.title}}catch(s){}}}};j.stop=k;o=function(){return a(q.location.href)};l=function(v,s){var u=q.document,t=$.fn[c].domain;if(v!==s){u.title=h.title;u.open();t&&u.write('<script>document.domain="'+t+'"<\/script>');u.close();q.location.hash=v}}})();return j})()})(jQuery,this);

/**
 * jQuery AjaxFileUpload - v2.1 - 4/12/2011
 * http://www.phpletter.com/Our-Projects/AjaxFileUpload/
 */
(function($){
  $.extend({

    handleError: function(s, xhr, status, e){
      // If a local callback was specified, fire it
      if (s.error)
        s.error(xhr, status, e);

      // Fire the global callback
      if (s.global)
        jQuery.event.trigger("ajaxError", [xhr, s, e]);
    },
    createUploadIframe: function(id, uri){
      //create frame
      var frameId = 'jUploadFrame' + id;
      var iframeHtml = '<iframe id="' + frameId + '" name="' + frameId + '" style="position:absolute; top:-9999px; left:-9999px"';
      if (window.ActiveXObject) {
        if (typeof uri == 'boolean') {
          iframeHtml += ' src="' + 'javascript:false' + '"';
        }
        else if (typeof uri == 'string') {
          iframeHtml += ' src="' + uri + '"';
        }
      }
      iframeHtml += ' />';
      jQuery(iframeHtml).appendTo(document.body);

      return jQuery('#' + frameId).get(0);
    },
    createUploadForm: function(id, fileElementId, data){
      //create form
      var formId = 'jUploadForm' + id;
      var fileId = 'jUploadFile' + id;
      var form = jQuery('<form  action="" method="POST" name="' + formId + '" id="' + formId + '" enctype="multipart/form-data"></form>');
      if (data) {
        for (var i in data) {
          jQuery('<input type="hidden" name="' + i + '" value="' + data[i] + '" />').appendTo(form);
        }
      }
      var oldElement = jQuery('#' + fileElementId);
      var newElement = jQuery(oldElement).clone();
      jQuery(oldElement).attr('id', fileId);
      jQuery(oldElement).before(newElement);
      jQuery(oldElement).appendTo(form);

      //set attributes
      jQuery(form).css('position', 'absolute');
      jQuery(form).css('top', '-1200px');
      jQuery(form).css('left', '-1200px');
      jQuery(form).appendTo('body');
      return form;
    },
    ajaxFileUpload: function(s){
      // TODO introduce global settings, allowing the client to modify them for all requests, not only timeout
      s = jQuery.extend({}, jQuery.ajaxSettings, s);
      var id = new Date().getTime()
      var form = jQuery.createUploadForm(id, s.fileElementId, (typeof (s.data) == 'undefined' ? false : s.data));
      var io = jQuery.createUploadIframe(id, s.secureuri);
      var frameId = 'jUploadFrame' + id;
      var formId = 'jUploadForm' + id;
      // Watch for a new set of requests
      if (s.global && !jQuery.active++) {
        jQuery.event.trigger("ajaxStart");
      }
      var requestDone = false;
      // Create the request object
      var xml = {}
      if (s.global) jQuery.event.trigger("ajaxSend", [xml, s]);
      // Wait for a response to come back
      var uploadCallback = function(isTimeout){
        var io = document.getElementById(frameId);
        try {
          if (io.contentWindow) {
            xml.responseText = io.contentWindow.document.body ? io.contentWindow.document.body.innerHTML : null;
            xml.responseXML = io.contentWindow.document.XMLDocument ? io.contentWindow.document.XMLDocument : io.contentWindow.document;
          }
          else if (io.contentDocument) {
            xml.responseText = io.contentDocument.document.body ? io.contentDocument.document.body.innerHTML : null;
            xml.responseXML = io.contentDocument.document.XMLDocument ? io.contentDocument.document.XMLDocument : io.contentDocument.document;
          }
        }
        catch (e) {
          jQuery.handleError(s, xml, null, e);
        }
        if (xml || isTimeout == "timeout") {
          requestDone = true;
          var status;
          try {
            status = isTimeout != "timeout" ? "success" : "error";
            // Make sure that the request was successful or notmodified
            if (status != "error") {
              // process the data (runs the xml through httpData regardless of callback)
              var data = jQuery.uploadHttpData(xml, s.dataType);
              // If a local callback was specified, fire it and pass it the data
              if (s.success) s.success(data, status);

              // Fire the global callback
              if (s.global) jQuery.event.trigger("ajaxSuccess", [xml, s]);
            }
            else jQuery.handleError(s, xml, status);
          }
          catch (e) {
            status = "error";
            jQuery.handleError(s, xml, status, e);
          }

          // The request was completed
          if (s.global) jQuery.event.trigger("ajaxComplete", [xml, s]);

          // Handle the global AJAX counter
          if (s.global && !--jQuery.active) jQuery.event.trigger("ajaxStop");

          // Process result
          if (s.complete) s.complete(xml, status);

          jQuery(io).unbind()

          setTimeout(function(){
            try {
              jQuery(io).remove();
              jQuery(form).remove();
            }
            catch (e) {
              jQuery.handleError(s, xml, null, e);
            }
          }, 100)

          xml = null

        }
      }
      // Timeout checker
      if (s.timeout > 0) {
        setTimeout(function(){
          // Check to see if the request is still happening
          if (!requestDone) uploadCallback("timeout");
        }, s.timeout);
      }
      try {
        var form = jQuery('#' + formId);
        jQuery(form).attr('action', s.url);
        jQuery(form).attr('method', 'POST');
        jQuery(form).attr('target', frameId);
        if (form.encoding) {
          jQuery(form).attr('encoding', 'multipart/form-data');
        }
        else {
          jQuery(form).attr('enctype', 'multipart/form-data');
        }
        jQuery(form).submit();
      }
      catch (e) {
        jQuery.handleError(s, xml, null, e);
      }

      jQuery('#' + frameId).load(uploadCallback);
      return {
        abort: function(){}
      };

    },
    uploadHttpData: function(r, type){
      var data = !type;
      data = type == "xml" || data ? r.responseXML : r.responseText;
      // If the type is "script", eval it in global context
      if (type == "script") jQuery.globalEval(data);
      // Get the JavaScript object, if JSON is used.
      if (type == "json") eval("data = " + data);
      // evaluate scripts within html
      if (type == "html") jQuery("<div>").html(data).evalScripts();

      return data;
    }
  });
}(jQuery));

// HN namespace with utils functions and vars
HN = window.HN = {
  browser: (function(){
    var ua = navigator.userAgent.toLowerCase();
    var b = {
      version: (ua.match( /.+(?:rv|it|ra|ie)[\/: ]([\d.]+)/ ) || [])[1],
      chrome: /applewebkit/.test( ua ) && /safari/.test( ua ) && /chrome/.test( ua ),
      safari: /applewebkit/.test( ua ) && /safari/.test( ua ) && !/chrome/.test( ua ),
      opera: /opera/.test( ua ),
      msie: /msie/.test( ua ) && !/opera/.test( ua ),
      mozilla: /mozilla/.test( ua ) && !/(compatible|webkit)/.test( ua ),
      firefox: /firefox/.test( ua ),
      firefoxVer: (ua.match( /.+firefox\/([\d.]+)/ ) || [])[1],
      windows: /windows/.test( ua ),
      macintosh: /macintosh/.test( ua ),
      windows_xp: /windows nt 5.1/.test( ua ),
      windows_vista: /windows nt 6.0/.test( ua ),
      windows_seven: /windows nt 6.1/.test( ua ),
      os_32: (!/wow64/.test( ua ) && !/win64; ia64/.test( ua ) && !/win64; x64/.test( ua )),
      os_64: /wow64/.test( ua ) || /win64; ia64/.test( ua ) || /win64; x64/.test( ua ),
      iphone: /iphone/.test( ua ) && /applewebkit/.test( ua ),
      ipad: /ipad/.test( ua ) && /applewebkit/.test( ua )
    };
    if (b.msie && b.version < 9) {
      if (/trident\/5/.test( ua ))
        b.version = 9;
      else if (/trident\/4/.test( ua ))
        b.version = 8;
    }
    return b;
  }()),
  url: {
    params: (function(){var p={};document.location.search.substring(1).replace(/([^&]+)=([^&]+)/gi,function(s,$1,$2){p[$1]=$2});return p;}())
  },
  arrayRemove: function(array, from, to){
    var rest = array.slice((to || from) + 1 || array.length);
    array.length = from < 0 ? array.length + from : from;
    return array.push.apply(array, rest);
  }
};

HN.TC = {
  get_pdt_fo_url: function(p_id, p_rn, f_id){ return HN.TC.URL+"produits/"+f_id+"-"+p_id+"-"+p_rn+".html"; },
  get_dft_pdt_pic_url: function(format){ return HN.TC.PRODUCTS_IMAGE_SECURE_URL+"no-pic-"+format+".gif"; },
  get_adv_fo_url: function(a_id){ return HN.TC.URL+"fournisseur/"+a_id+".html"; },
  get_fam_fo_url: function(f_rn){ return HN.TC.URL+"familles/"+f_rn+".html"; },
  get_formated_date: function(t){
    val = new Date();
    val.setTime(parseInt(t)*1000);
    val = sprintf("%02d", val.getDate()) + "/" + sprintf("%02d", val.getMonth()+1) + "/" + sprintf("%04d", val.getFullYear());
    return val;
  },
  get_formated_datetime: function(t, dt_sep){
    t = new Date(parseInt(t)*1000||0);
    return sprintf("%02d", t.getDate()) + "/" + sprintf("%02d", t.getMonth()+1) + "/" + sprintf("%04d", t.getFullYear()) + (dt_sep || " ") +
           sprintf("%02d", t.getHours()) + ":" + sprintf("%02d", t.getMinutes()) + ":" + sprintf("%02d", t.getSeconds());
  },
  get_timestamp: function(t){
    var date = t.match(/\s*(\d{1,2})\s*[\/-]{1}\s*(\d{1,2})\s*[\/-]{1}\s*(\d{2,4})\s*(\D*\s*(\d{1,2}):(\d{1,2})[:\.]?(\d*))?\s*/)||[];
    return (new Date(date[3], date[2]-1, date[1], date[5]||0, date[6]||0, (date[7]||"").substr(0,2), (date[7]||"").substr(2,3))).getTime()/1000;
  },
  mktime: function(H,i,s,n,j,Y){
    var c = new Date();
    return (new Date(
        Y !== undefined ? Y : c.getFullYear(),
        n !== undefined ? n-1 : c.getMonth(),
        j !== undefined ? j : c.getDate(),
        H !== undefined ? H : c.getHours(),
        i !== undefined ? i : c.getMinutes(),
        s !== undefined ? s : c.getSeconds()
      )
    ).getTime()/1000;
  },
  getAjaxErrorText: function(jqXHR, textStatus, errorThrown){
    var error;
    try { error = $.parseJSON(jqXHR.responseText)['error']; }
    catch (e) { error = textStatus+" : "+errorThrown }
    return error;
  }
};

// Ajax queries 'à la doctrine'
HN.TC.AjaxQuery = function(){
  this.queryParts = [];
  this.response = null;
  this.hydrationMode = "array";
};
HN.TC.AjaxQuery.prototype = (function(){
  
  var o = {},
      baseMethods = [
        "addFrom",
        "addGroupBy",
        "addHaving",
        "addOrderBy",
        "addSelect",
        "addWhere",
        "andWhere",
        "andWhereIn",
        "andWhereNotIn",
        "delete",
        "distinct",
        "from",
        "groupBy",
        "having",
        "innerJoin",
        "leftJoin",
        "limit",
        "offset",
        "orWhere",
        "orWhereIn",
        "orWhereNotIn",
        "orderBy",
        "select",
        "set",
        "update",
        "where",
        "whereIn"
      ];
  
  for (var i=0; i<baseMethods.length; i++) (function(){
    var method = baseMethods[i];
    o[method] = function(){
      args = Array.prototype.slice.call(arguments);
      this.queryParts.push([method, args]);
      return this;
    };
  }());
  
  o.setHydrationMode = function(hydrationMode){
    this.hydrationMode = hydrationMode;
    return this;
  }
  o.fetchArray = function(cb){
    return this.execute(cb, "array");
  };
  o.fetchOne = function(cb){
    return this.execute(cb, "fetchOne");
  };
  o.count = function(cb){
    return this.execute(cb, "count");
  };
  o.execute = function(cb){
    var me = this;
    var p=[],a_=arguments;for(var k=a_.callee.length;k<a_.length;k++)p.push(a_[k]);
    me.hydrationMode = p[0] ? p[0] : me.hydrationMode;
    $.ajax({
      type: "POST",
      url: HN.TC.EXTRANET_URL+"ressources/ajax/AJAX_Doctrine_Interface.php",
      data: { type: "Doctrine_query", data: me.getAsObject() },
      dataType: "json",
      error: function(jqXHR, textStatus, errorThrown){},
      success: function(data, textStatus, jqXHR){
        me.response = data;
        if (data.success) {
          cb(data.data);
        }
        else {
          //console.log(data.errorMsg);
        }
      }
    });
    return this;
  };
  o.reset = function(){
    this.queryParts = [];
    return this;
  };
  o.getAsObject = function(){
    return { queryParts: this.queryParts, hydrationMode: this.hydrationMode };
  };
  o.clone = function(){
    var clone = new HN.TC.AjaxQuery();
    clone.hydrationMode = this.hydrationMode;
    clone.queryParts = this.queryParts.slice(0);
    return clone;
  }
  
  return o;
}());
HN.TC.AjaxQuery.create = function(){
  return new HN.TC.AjaxQuery();
};

HN.TC.AjaxQuery.GBL_MAX_ID = 0xffffffff; // global max id length
HN.TC.AjaxQuery.PDT_MAX_ID = 0xffffff;
HN.TC.AjaxQuery.CUS_MAX_ID = 0xffffffff;
HN.TC.AjaxQuery.REF_MAX_ID = 0xffffffff;
HN.TC.AjaxQuery.CAT_MAX_ID = 0xffffff;
HN.TC.AjaxQuery.ADV_MAX_ID = 0xffff;
HN.TC.AjaxQuery.BOU_MAX_ID = 0xffff;
HN.TC.AjaxQuery.getQueryNumIntervalsOld = function(f, n, midl){
  var qs = f+" = ?",
      qp = [n],
      iv_s = iv_e = n;
  while (iv_s.length < midl) {
    iv_s += "0";
    iv_e += "9";
    qs += " OR ("+f+" >= ? AND "+f+" <= ?)";
    qp.push(iv_s, iv_e)
  }
  return [qs, qp];
};
HN.TC.AjaxQuery.getQueryNumIntervals = function(f, n, max, wc){ // field, number, max number, wether to return the string with CAST OPERATOR along with the PARAMS or not
  if (wc == null) {
    if (max == null || typeof max == "boolean") {
      wc = max;
      max = 0xffffffff;
    }
  }
  var iv_s = iv_e = parseInt(n),
      qs = f+" = "+(wc?"CAST(? AS UNSIGNED)":iv_s),
      qp = [iv_s];
  iv_s*=10; iv_e=iv_e*10+9;
  while (iv_e <= max) {
    qs += " OR ("+f+" >= "+(wc?"CAST(? AS UNSIGNED)":iv_s)+" AND "+f+" <= "+(wc?"CAST(? AS UNSIGNED)":iv_e)+")";
    qp.push(iv_s, iv_e)
    iv_s*=10; iv_e=iv_e*10+9;
  }
  return [qs, wc?qp:[]];
};

HN.TC.AjaxMultiQueries = function(){
  this.queries = [];
  this.response = null;
};
HN.TC.AjaxMultiQueries.prototype = {
  linkedLimit: 0,
  // see AJAX_Doctrine_Interface.php for an explaination on this param
  setLinkedLimit: function(limit){
    this.linkedLimit = parseInt(limit) || 0;
    return this;
  },
  addQuery: function(q){
    this.queries.push(q);
    return this;
  },
  execute: function(cb){
    var me = this;
    $.ajax({
      type: "POST",
      url: HN.TC.EXTRANET_URL+"ressources/ajax/AJAX_Doctrine_Interface.php",
      data: { type: "Doctrine_multiple_queries", data: me.getAsObject() },
      dataType: "json",
      error: function(jqXHR, textStatus, errorThrown){},
      success: function(data, textStatus, jqXHR){
        me.response = data;
        if (data.success) {
          cb(data.data);
        }
        else {
          //console.log(data.errorMsg);
        }
      }
    });
    return this;
  },
  getAsObject: function(){
    var queriesObjects = [];
    for (var qi=0; qi<this.queries.length; qi++)
      queriesObjects.push(this.queries[qi].getAsObject());
    return { queriesObjects: queriesObjects, linkedLimit: this.linkedLimit };
  }
};
HN.TC.AjaxMultiQueries.create = function(){
  return new HN.TC.AjaxMultiQueries();
};

HN.TC.AjaxDoctrineObject = function(){
  this.object = "";
  this.method = "";
  this.loadQueryParams = [];
  this.data = {};
  this.response = null;
};
HN.TC.AjaxDoctrineObject.prototype = {
  setObject: function(object){ this.object = object; return this; },
  setMethod: function(method){ this.method = method; return this; },
  setLoadQueryParams: function(params){ this.loadQueryParams = params; return this; },
  setData: function(data){ this.data = data; return this; },
  update: function(cb){ this.method = "update"; this.execute(cb); },
  create: function(cb){ this.method = "create"; this.execute(cb); },
  execute: function(cb){
    var me = this;
    $.ajax({
      type: "POST",
      url: HN.TC.ADMIN_URL+"ressources/ajax/AJAX_Doctrine_Interface.php",
      data: { type: "Doctrine_Object", object: me.object, method: me.method, loadQueryParams: me.loadQueryParams, data: me.data },
      dataType: "json",
      error: function(jqXHR, textStatus, errorThrown){
      },
      success: function(data, textStatus, jqXHR){
        me.response = data;
        if (data && data.success) {
          cb(data.data);
        }
        else {
          //console.log((data && data.errorMsg) || "empty response");
        }
      }
    });
    return this;
  }
};
HN.TC.AjaxDoctrineObject.create = function(){
  return new HN.TC.AjaxDoctrineObject();
};

HN.TC.AutoCompleteField = function(_params){
	var me = this;
  $.extend(true, me, {
    field: null,
    defaultValue: null,
    feedFunc: function(){ return false; },
    onSelect: null, // when a value is selected
    onConfirm: null, // when confirm is called or TODO when double hitting the enter key with an existing value
    disabled: false,
    colToGet: 0
  }, _params);
	me.$f = $(me.field);
  
	var requestTO = null,
      doHideProps = true;
	
  me.lastSearchedVal = "";
  me.data = [];
  me.curRowData = [];
  
	if (me.defaultValue) {
    me.$f
      .val(me.defaultValue)
      .one("focus", function(){
        $(this).val("");
      });
  }
  
	me.$f
    .on("blur", function(){
      if (!me.disabled) {
        if (doHideProps && me.$propsBox.is(":visible") && $.isFunction(me.onSelect))
          me.onSelect.apply(me);
        setTimeout(function(){
          if (doHideProps) // only hide if we're not on the propsbox
            me.hideProps();
          else
            doHideProps = true;
        }, 10);
      }
    })
    .on("keyup", function(e){
      if (!me.disabled) {
        var val = $(this).val();
        clearTimeout(requestTO);
        if (me.lastSearchedVal != val && val.length > 1) {
          requestTO = setTimeout(function(){
            me.lastSearchedVal = val;
            me.getResults(val);
          }, 250);
        }
      }
    })
    .on("keydown", function(e){
      if (!me.disabled && me.data.length) {
        var hoverProp = me.$propsLines.filter(".hover").index();
        
        if (e.keyCode == 27) { // escape key
          me.hideProps();
        }
        else if (e.keyCode == 13) {
          if (me.$propsBox.is(":visible") && hoverProp != -1) {
            me.$propsLines.eq(hoverProp).mouseenter().click();
          }
          else {
            if ($.isFunction(me.enterFunc))
              me.enterFunc.apply(this);
          }
        }
        else {
          if (e.keyCode == 38 || e.keyCode == 40) { // arrow up and arrow down
            me.showProps();
            var hoverProp = me.$propsLines.filter(".hover").index();
            
            if (hoverProp != -1)
              me.$propsLines.eq(hoverProp).mouseleave();
            
            hoverProp = hoverProp + (e.keyCode == 38 ? -1 : +1);
            
            if (hoverProp < 0)
              hoverProp = me.$propsLines.length-1;
            else if (hoverProp >= me.$propsLines.length)
              hoverProp = 0;
            
            me.curRowData = me.data[hoverProp];
            me.$propsLines.eq(hoverProp).mouseenter();
            me.lastSearchedVal = me.curRowData[me.colToGet];
            $(this).val(me.lastSearchedVal);
          }
        }
      }
    })
    .data("AChandle", me); // handle to this object from the field
  
    me.$propsBox = $("<div>", {
      "class": "auto-complete-box",
      mousedown: function(){ doHideProps = false; }, // prevents the hiding of the results when onblur is fired from the input
      html: "<table><tbody></tbody></table>"
    }).insertAfter(me.$f);
    
    if (me.$f.attr("id") && me.$f.attr("id") != "")
      me.$propsBox.attr("id", me.$f.attr("id")+"-ac-box");
    
    me.$propsContainer = $(me.$propsBox).find("tbody");
    me.$propsLines = $([]);
};
HN.TC.AutoCompleteField.prototype = {
	disable: function(){
    this.disabled = true;
    this.hideProps();
  },
  enable: function(){
    this.disabled = false;
  },
  hideProps: function(){
    this.$propsBox.hide();
  },
	showProps: function(){
    var pos = this.$f.position();
    this.$propsBox.show().css({
      minWidth: this.$f.outerWidth(),
      top: pos.top + this.$f.outerHeight() - 1,
      left: pos.left
    });
  },
  addPreProp: function(preProp){
    $("<tr>")
      .append($("<td>", { "class": "prop", html: "<i>"+preProp+"</i>" }))
      .append($("<td>", { "class": "results" }))
      .appendTo(this.$propsContainer);
	},
	addProp: function(rowData){
    var me = this,
        $tr = $("<tr>", {
          click: function(){
            me.curRowData = rowData;
            me.lastSearchedVal = rowData[me.colToGet];
            me.$f.val(me.lastSearchedVal);
            me.hideProps();
            if ($.isFunction(me.onSelect))
              me.onSelect.apply(me);
          },
          mousedown: function(){
            $(this).mouseenter();
          },
          mouseenter: function(){
            me.$propsLines.filter(".hover").removeClass("hover");
            $(this).addClass("hover");
          },
          mouseleave: function(){
            $(this).removeClass("hover");
          }
        });
      for (var i=0; i<rowData.length; i++)
        $tr.append($("<td>", { "class": "ac-col-"+(i+1), html: rowData[i] }));
      $tr.appendTo(me.$propsContainer);
	},
	clearProps: function(){
		this.$propsContainer.empty();
	},
  getResults: function(val){
    var me = this;
    me.clearProps();
    me.feedFunc(val, function(data){
      me.data = data;
      me.curRowData = [];
      for (var i=0; i<data.length; i++)
        me.addProp(data[i]);
      me.$propsLines = me.$propsContainer.children();
      me[data.length ? "showProps" : "hideProps"]();
    });
  },
  confirm: function(){
    if ($.isFunction(this.onConfirm))
      this.onConfirm.apply(this);
  }
};

HN.TC.ItemList = function(settings){
  var me = this;
  
  me.$dh = $(settings.domHandle);
  me.cols = [];
  me.colsByName = {}; // cols Indexed by Field Name
  me.sorts = [];
  me.$table = me.$thead = me.$tbody = me.$pages = null;
  me.items = [];
  me.items_count = 0;
  me.itemPerPage = settings.itemPerPage || 20;
  me.offset = 0;
  me.source = { fields: [], tables: [], filters: [] };
  me.onRowInsert = settings.onRowInsert;
  
  // events
  me.onRowEvent = {};
  me.onHeaderEvents = {};
  me.onCellEvents = {};
  
  // base dom objects
  me.$table = $("<table>", { "class": "item-list" }).appendTo(me.$dh);
  me.$thead = $("<tr>").appendTo($("<thead>").appendTo(me.$table));
  me.$tbody = $("<tbody>").appendTo(me.$table);
  me.$pages = $("<div>", { "class": "pages" }).appendTo(me.$dh);
  
  for (var i=0; i<settings.columns.length; i++)
    me.addColumn(settings.columns[i]);
  if (settings.columns.length)
    me.drawHeaders();
  if (settings.source)
    me.setSource(settings.source);
  if ($.isPlainObject(settings.onRowEvent))
    me.addOnRowEvent(settings.onRowEvent);
  
  // Init Filtering Dialog Box
  me.fdbAddFunc = function(){ return false; } // Filtering Dialog Box Add Function
  me.$fdb = $("<div>",{ "class": "filters" }).dialog({
    width: 500,
    autoOpen: false,
    modal: true,
    buttons: {
      "Ajouter": function(){
        me.fdbAddFunc();
        $(this).dialog("close");
      }
    }
  });
  // append filter select
  $("<select>", {
    change: function(){
      var $val = $(this).val();
      // show 1 or 2 inputs
      me.$fdb.find("input").hide().filter(":lt("+($val=="between"?2:1)+")").show();
    }
  }).appendTo(me.$fdb);
  // append inputs
  for (var k=0; k<2; k++)
    $("<input>", { type: "text" }).appendTo(me.$fdb);
  
};
HN.TC.ItemList.prototype = {
  drawHeaders: function(){
    for (var i=0; i<this.cols.length; i++) {
      this.$thead.append(this.cols[i].getDom());
      this.cols[i].index = i;
    }
  },
  setSource: function(src){
    $.extend(this.source, src);
    var qsl = this.source.fields.split(/\s*,\s*/);
    for (var i=0; i<qsl.length; i++) {
      var qs = qsl[i].match(/((\S+\.)?(\S+))(\s*as\s*(\S+))?/i),
          n = qs[5] || qs[3],
          s = qs[1];
      if (this.colsByName[n])
        this.colsByName[n].source = s;
    }
  },
  setSourceFilters: function(filters){
    var update = false;
    if ($.isArray(filters)) {
      if ($.isArray(filters[0]))
        this.source.filters = filters
      else if (filters[1])
        this.source.filters = [filters];
      else
        this.source.filters = ["where", filters];
      update = !!arguments[1];
    }
    else if ($.isArray(arguments[1])) {
      this.source.filters = [[filters, arguments[1]]];
      update = !!arguments[2];
    }
    if (update)
      this.updateView();
    return this;
  },
  addOnRowEvent: function(re){
    var me = this;
    me.onRowEvent = re;
    for (var e in re)
      this.$tbody.on(e, "tr", function(){ re[e].call(this, me.items[parseInt($(this).data("index"))]); });
  },
  removeOnRowEvent: function(e){
    if (e == null)
      for (var e in me.onRowEvent)
        this.$tbody.off(e, "tr");
    else
      this.$tbody.off(e, "tr");
  },
  updateView: function(){
    var me = this;
    var cb=[],p=[],a_=arguments;for(var k=a_.callee.length;k<a_.length;k++)if(typeof(a_[k])=="function")cb.push(a_[k]);else p.push(a_[k]);
    
    var q = HN.TC.AjaxQuery.create()
            .select(me.source.fields);
    for (var i=0; i<me.source.tables.length; i++)
      q[me.source.tables[i][0]](me.source.tables[i][1]);
    for (var i=0; i<me.source.filters.length; i++)
      q[me.source.filters[i][0]].apply(q, me.source.filters[i][1]);
    for (var i=0; i<me.cols.length; i++)
      if (me.cols[i].activeFilters.length) {
        //console.log(me.cols[i].getActiveFiltersParams());
        q.andWhere.apply(q, me.cols[i].getActiveFiltersParams());
      }
    for (var i=0; i<me.sorts.length; i++)
      q.addOrderBy(me.sorts[i]+" "+me.colsByName[me.sorts[i]].sortWay);
    
    //console.log(q.queryParts);
    var mq = HN.TC.AjaxMultiQueries.create();
    mq.addQuery(q.clone().setHydrationMode("count"))
    mq.addQuery(q.limit(me.itemPerPage).offset(me.offset).setHydrationMode("array"));
    
    mq.execute(function(data){
      //console.log(data);
      me.items_count = parseInt(data[0]) || 0;
      me.items = data[1];
      me.$tbody.empty();
      var html = "";
      for (var i=0; i<me.items.length; i++) {
        var line = me.items[i],
            row_html = "",
            $tr = $("<tr>").data("index", i);
        for (var ci=0; ci<me.cols.length; ci++) {
          var col = me.cols[ci];
          row_html += "<td class=\""+col.type+" "+col.name+"\">"+(col.onCellWrite ? col.onCellWrite.call(col, line, col.name)||line[col.name] : line[col.name])+"</td>";
        }
        $tr.html(row_html);
        me.$tbody.append($tr);
        if ($.isFunction(me.onRowInsert))
          me.onRowInsert.call($tr.get(0), line);
      }
      
      // showing pages buttons
      me.$pages.empty();
      if (me.offset > 0 || me.items_count > me.itemPerPage) {
        var curPage = Math.ceil(me.offset/me.itemPerPage) + 1,
            maxPage = Math.ceil(me.items_count/me.itemPerPage),
            ipp = me.itemPerPage,
            cpo = (curPage-1) * ipp,
            mpo = (maxPage-1) * ipp,
            pagesToShow = [
              { text: "&#x00ab;", offset: 0        , "class": (curPage-2>0 ? "" : "hidden") },
              { text: "&#x2039;" , offset: cpo-ipp  , "class": (curPage-1>0 ? "" : "hidden") },
              { text: curPage-2 , offset: cpo-ipp*2, "class": (curPage-2>0 ? "" : "hidden") },
              { text: curPage-1 , offset: cpo-ipp  , "class": (curPage-1>0 ? "" : "hidden") },
              { text: curPage   , offset: cpo      , "class": "current" },
              { text: curPage+1 , offset: cpo+ipp  , "class": (curPage+1<=maxPage ? "" : "hidden") },
              { text: curPage+2 , offset: cpo+ipp*2, "class": (curPage+2<=maxPage ? "" : "hidden") },
              { text: "&#x203a;" , offset: cpo+ipp  , "class": (curPage+1<=maxPage ? "" : "hidden") },
              { text: "&#x00bb;", offset: mpo      , "class": (curPage+2<=maxPage ? "" : "hidden") }
            ];
        for (var i=0; i<pagesToShow.length; i++) (function(){
          var ps = pagesToShow[i]; // page selection
          $("<a>", {
            "class": "page"+(ps["class"]!="" ? " "+ps["class"] : ""),
            href: "#offset-"+ps["offset"],
            html: ps["text"],
            click: function(){
              me.offset = ps["offset"];
              me.updateView();
              return false;
            }
          }).appendTo(me.$pages);
        }());
      }
      //me.updateView();
      if (cb.length)
        cb[0]();
    });
    
  },
  addColumn: function(s){
    var col = new HN.TC.ItemList.Column(this, s);
    this.cols.push(col);
    this.colsByName[col.name] = col;
  },
  showFilterDB: function(col){
    var me = this;
    me.$fdb.dialog("option", "title", "Ajouter un filtre à la colonne "+col.label);
    var $select = me.$fdb.find("select").empty().append(col.domOptions).change(),
        $inputs = me.$fdb.find("input").empty();
    me.fdbAddFunc = function(){
      var inputs = [];
      $inputs.filter(":visible").each(function(i,v){
        inputs.push($(v).val());
      });
      col.addFilter([$select.val(), inputs]);
    };
    me.$fdb.dialog("open");
  }
  
};
HN.TC.ItemList.Column = function(parent, settings){
  var me = this,
      dftSettings = {
        name: "", // col name
        label: "", // header
        type: "int", // col behavior type
        source: "",
        constStrings: {}, // for "const" type
        onCellWrite: (function(){
          switch (settings.type) {
            case "date":
              return function(rowData, colName){
                return HN.TC.get_formated_datetime(rowData[colName]);
              };
            case "price":
              return null;
            case "const":
              return function(rowData, colName){
                return this.constStrings[parseInt(rowData[colName])||0];
              }
            default:
              return null;
          }
        }()), // when data is written
        filters: [], // filters to show
        sortable: settings.type == "const" || settings.type == "misc" ? false : true, // default to true except for const type
        activeFilters: []
      };
  
  me.parent = parent;
  $.extend(this, dftSettings, settings);
  
  // base col dom
  me.$th = $("<th>", { "class": me.name });
  me.$div = $("<div>").append("<span>"+me.label+"</span>").appendTo(me.$th);
  me.$filter = $("<div>", {
    "class": "filtering",
    "click": function(){ me.toggleOptions(); }
  }).appendTo(me.$div);
  me.$ul = $("<ul>", { "class": "options" }).appendTo(me.$filter);
  
  if ($.isPlainObject(settings.onHeaderEvent))
    me.addOnHeaderEvent(settings.onHeaderEvent);
  if ($.isPlainObject(settings.onCellEvent))
    me.addOnCellEvent(settings.onCellEvent);
    
  //for (var k in me.onCellEvent)
    //me.parent.$tbody.on(k, "td", function(e){ me.onCellEvent[k].call(this, me.parent.items[parseInt($(this).closest("tr").data("index"))], me.name, e); });
    //if (!$.data(me.parent.$tbody.get(0), "event")[k])
  
  if (me.sortable) {
    switch (me.type) {
      case "int":
      case "float":
      case "price":
        $("<li>",{
          html: "de <b class=\"start\">0</b> à <b class=\"end\">N</b>",
          click: function(){ me.sort("ASC"); }
        }).appendTo(me.$ul);
        $("<li>",{
          html: "de <b class=\"end\">N</b> à <b class=\"start\">0</b>",
          click: function(){ me.sort("DESC"); }
        }).appendTo(me.$ul);
        break;
      case "date":
        $("<li>",{
          html: "du <b class=\"start\">plus récent</b> au <b class=\"end\">plus ancien</b>",
          click: function(){ me.sort("DESC"); }
        }).appendTo(me.$ul);
        $("<li>",{
          html: "du <b class=\"end\">plus ancien</b> au <b class=\"start\">plus récent</b>",
          click: function(){ me.sort("ASC"); }
        }).appendTo(me.$ul);
        break;
      case "string":
      default:
        $("<li>",{
          html: "de <b class=\"start\">A</b> à <b class=\"end\">Z</b>",
          click: function(){ me.sort("ASC"); }
        }).appendTo(me.$ul);
        $("<li>",{
          html: "de <b class=\"end\">Z</b> à <b class=\"start\">A</b>",
          click: function(){ me.sort("DESC"); }
        }).appendTo(me.$ul);
    }
    $("<li>",{ "class": "vsep" }).appendTo(me.$ul);
  }
  
  me.domOptions = [];
  if (me.filters.length) {
    switch (me.type) {
      case "const":
        var fi = $.inArray("=", me.filters);
        if (fi != -1) {
          for (var k in me.constStrings) (function(){
            var c = k,
                cs = me.constStrings[k];
            $("<li>", {
              html: cs,
              click: function(){ me.addFilter([fi, [c]]); }
            }).appendTo(me.$ul);
          }());
        }
        break;
      default:
        for (var i=0; i<me.filters.length; i++) (function(){
          var fi = i,
              f = me.filters[fi];
          if (typeof f == "string") {
            if (HN.TC.ItemList.FILTER_strings[f])
              me.domOptions.push($("<option>", { value: fi, html: HN.TC.ItemList.FILTER_strings[f] }).get(0));
          }
          else if ($.isPlainObject(f)) {
            if (f.direct) {
              $("<li>", {
                text: f.text,
                click: function(){ me.addFilter([fi]); }
              }).appendTo(me.$ul);
            }
            else {
            }
          }
        }());
        if (me.domOptions.length) {
          $("<li>", {
            html: "ajouter un filtre",
            click: function(){ me.parent.showFilterDB(me); }
          }).appendTo(me.$ul);
        }
    }
  }
  
  if (me.$ul.children().length == 0) {
    me.$filter.hide();
    me.$div.addClass("no-filter");
  }
  
  if ($.isFunction(settings.onInit))
    settings.onInit.call(me.$th.get(0), me);
};
HN.TC.ItemList.Column.prototype = {
  sortWay: "ASC",
  addOnHeaderEvent: function(hel){
    var me = this,
        p = me.parent,
        phel = p.onHeaderEvents;
    for (var hen in hel) (function(){
      var en = hen;
      if (!phel[en]) {
        phel[en] = function(e){
          var col = p.cols[$(this).index()];
          if (phel[en][col.name])
            phel[en][col.name].call(this, col, e);
        };
        p.$thead.on(en, "th", phel[en]);
      }
      phel[en][me.name] = hel[en];
    }());
  },
  removeOnHeaderEvent: function(he){
    var phel = me.parent.onHeaderEvents;
    if (he == null) {
      for (var he in phel)
        delete phel[he][this.name];
    }
    else
      delete phel[he][this.name];
  },
  addOnCellEvent: function(cel){
    var me = this,
        p = me.parent,
        pcel = p.onCellEvents;
    for (var cen in cel) (function(){
      var en = cen;
      if (!pcel[en]) {
        pcel[en] = function(e){
          var col = p.cols[$(this).index()];
          if (pcel[en][col.name])
            pcel[en][col.name].call(this, p.items[parseInt($(this).parent().data("index"))], col, e);
        };
        p.$tbody.on(en, "td", pcel[en]);
      }
      pcel[en][me.name] = cel[en];
    }());
  },
  removeOnCellEvent: function(ce){
    var pcel = me.parent.onCellEvents;
    if (ce == null) {
      for (var ce in pcel)
        delete pcel[ce][this.name];
    }
    else
      delete pcel[ce][this.name];
  },
  addFilter: function(f){
    var me = this;
    me.activeFilters.push(f);
    me.parent.updateView(function(){ me.updateView(); });
  },
  removeFilter: function(i){
    var me = this;
    HN.arrayRemove(me.activeFilters, i);
    me.parent.updateView(function(){ me.updateView(); });
  },
  toggleOptions: function(){
    if (this.$filter.hasClass("on"))
      this.hideOptions();
    else
      this.showOptions();
  },
  showOptions: function(){
    for (var i=0; i<this.parent.cols.length; i++)
      this.parent.cols[i].hideOptions();
    this.$filter.addClass("on");
  },
  hideOptions: function(){
    this.$filter.removeClass("on");
  },
  sort: function(way){
    var me = this;
    way = way.toUpperCase();
    me.sortWay = way == "TOGGLE" ? (me.sortWay != "ASC" ? "ASC" : "DESC") : (way != "ASC" ? "DESC" : "ASC");
    var sortIndex = $.inArray(me.name, me.parent.sorts);
    if (sortIndex != -1)
      HN.arrayRemove(me.parent.sorts, sortIndex);
    me.parent.sorts.unshift(me.name);
    me.parent.updateView(function(){
      me.updateView();
    });
  },
  getDom: function(){
    return this.$th.get(0);
  },
  getActiveFiltersParams: function(){
    var me = this,
        colQueries = [],
        colParams = [];
    for (var k=0; k<me.activeFilters.length; k++) {
      var af = me.activeFilters[k],
          i = af[0],
          d = af[1],
          f = me.filters[i];
      if (typeof f == "string") {
        switch (me.type) {
          case "date": // overwrite some stuff when it's a date
            var t1 = HN.TC.get_timestamp(d[0]),
                t2 = f == "=" ? t1+86400 : HN.TC.get_timestamp(d[1]);
            colQueries.push("("+me.source+" >= ? AND "+me.source+" <= ?)");
            colParams.push(t1, t2);
            break;
          case "price":
            break;
          case "misc":
            break;
          default:
            switch (f) {
              case "between":
                colQueries.push("("+me.source+" >= ? AND "+me.source+" <= ?)");
                colParams.push(d[0], d[1]);
                break;
              default:
                colQueries.push(me.source+" "+f+" ?");
                colParams.push(d[0]);
            }
        }
      }
      else if ($.isPlainObject(f)) {
        var fp = f.getFilterParam(d);
        colQueries.push("("+fp[0]+")");
        colParams.push.apply(colParams, $.isArray(fp[1]) ? fp[1] : [fp[1]]);
      }
    }
    return [colQueries.join(" OR "), colParams];
  },
  updateView: function(){
    var me = this;
    me.$filter[me.activeFilters.length?"addClass":"removeClass"]("filtered");
    me.$ul.find("li.filter").remove();
    var $last_li = me.$ul.find("li").last();
    for (var k=0; k<me.activeFilters.length; k++) (function(){
      var af = me.activeFilters[k],
          afi = k,
          i = af[0],
          d = af[1],
          f = me.filters[i],
          text = "";
      if (typeof f == "string") {
        text += "<i>"+HN.TC.ItemList.FILTER_strings[f]+"</i> ";
        if (me.type == "const")
          text += "<b>"+me.constStrings[d[0]]+"</b>";
        else {
          text += "<b>";
          for (var di=0; di<d.length; di++)
            text += (di ? (di==d.length-1 ? "</b><i> et </i><b>" : "</b><i>, </i><b>") : "") + d[di];
          text += "</b>";
        }
      }
      else {
        text += "<i>"+(f.ctext||"")+"</i> <b>"+f.text+"</b>";
      }
      $("<li>", {
        "class": "filter",
        html: "sup. "+text,
        click: function(){ me.removeFilter(afi); }
      }).insertBefore($last_li);
    }());
  }
  
};
HN.TC.ItemList.FILTER_strings = {
  "=": "est égal à",
  ">": "est > à",
  "<": "est < à",
  ">=": "est >= à",
  "<=": "est <= à",
  "like": "contient",
  "between": "entre..."
};

HN.TC.ajaxUploadFile = function(o){
  var me = this;
  me.itemId = null;
  me.context = null;
  me.fileElementId = 'docFile';
  me.aliasFileName = null;
  me.loadingImg = "";
  $.extend(me, o);

  me.doAjaxFileUpload = function(){
    var cb = $.isFunction(arguments[0]) ? arguments[0] : false;
    
    $(me.loadingImg).ajaxStart(function(){
      $(this).show();
    }).ajaxComplete(function(){
      $(this).hide();
    });

    $.ajaxFileUpload({
      url: HN.TC.EXTRANET_URL+"ressources/ajax/AJAX_files_upload.php",
      secureuri: false,
      fileElementId: this.fileElementId,
      dataType: 'json',
      async: false,
      data: {
        itemId: me.itemId,
        context: me.context,
        fileElementId: me.fileElementId,
        aliasFileName: me.aliasFileName
      },
      // it is not possible to get the iframe headers to callback the error function, so only success can be triggered
      success: function(data, status){
        if (data.error) {
          alert(data.error);
        } else {
          alert(data.response);
          if (cb) cb();
        }
      }
    });

    return false;
  }
}
HN.TC.getUploadedFiles = function(o){
  var me = this;
  me.itemId = null;
  me.context = null;
  $.extend(me, o);

  me.getUploadedFilesList = function(){

    var response;
    
    $.ajax({
      url: HN.TC.EXTRANET_URL+"ressources/ajax/AJAX_get_uploaded_files_list.php",
      secureuri: false,
      type: 'post',
      dataType: 'json',
      data: {
        itemId: me.itemId,
        context: me.context
      },
      async: false,
      success: function(data, textStatus, jqXHR){
        response = data.response;
      },
      error: function(jqXHR, textStatus, errorThrown){
        alert(HN.TC.getAjaxErrorText(jqXHR, textStatus, errorThrown));
      }
    });
    
    return response;
  }
}
HN.TC.deleteUploadedFile = function(fileId){
  var me = this;
  me.context = null;

  me.deleteFileFunction = function(){

    var response;

    $.ajax({
      url: HN.TC.EXTRANET_URL+"ressources/ajax/AJAX_delete_uploaded_file.php",
      secureuri: false,
      type: 'post',
      dataType: 'json',
      data: {
        fileId: fileId,
        context: me.context
      },
      async: false,
      success: function(data, textStatus, jqXHR){
        response = data.response;
        alert(response);
      },
      error: function(jqXHR, textStatus, errorThrown){
        alert(HN.TC.getAjaxErrorText(jqXHR, textStatus, errorThrown));
      }
    });
    
    return response;
  }
}

// global _blank live function
// target="_blank"
$(document)
  .on("click", "a._blank, area._blank", function(){ open($(this).attr("href"), "_blank"); return false; })
