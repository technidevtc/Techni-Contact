/*! sprintf.js | Copyright (c) 2007-2013 Alexandru Marasteanu <hello at alexei dot ro> | 3 clause BSD license */
(function(e){function r(e){return Object.prototype.toString.call(e).slice(8,-1).toLowerCase()}function i(e,t){for(var n=[];t>0;n[--t]=e);return n.join("")}var t=function(){return t.cache.hasOwnProperty(arguments[0])||(t.cache[arguments[0]]=t.parse(arguments[0])),t.format.call(null,t.cache[arguments[0]],arguments)};t.format=function(e,n){var s=1,o=e.length,u="",a,f=[],l,c,h,p,d,v;for(l=0;l<o;l++){u=r(e[l]);if(u==="string")f.push(e[l]);else if(u==="array"){h=e[l];if(h[2]){a=n[s];for(c=0;c<h[2].length;c++){if(!a.hasOwnProperty(h[2][c]))throw t('[sprintf] property "%s" does not exist',h[2][c]);a=a[h[2][c]]}}else h[1]?a=n[h[1]]:a=n[s++];if(/[^s]/.test(h[8])&&r(a)!="number")throw t("[sprintf] expecting number but found %s",r(a));switch(h[8]){case"b":a=a.toString(2);break;case"c":a=String.fromCharCode(a);break;case"d":a=parseInt(a,10);break;case"e":a=h[7]?a.toExponential(h[7]):a.toExponential();break;case"f":a=h[7]?parseFloat(a).toFixed(h[7]):parseFloat(a);break;case"o":a=a.toString(8);break;case"s":a=(a=String(a))&&h[7]?a.substring(0,h[7]):a;break;case"u":a>>>=0;break;case"x":a=a.toString(16);break;case"X":a=a.toString(16).toUpperCase()}a=/[def]/.test(h[8])&&h[3]&&a>=0?"+"+a:a,d=h[4]?h[4]=="0"?"0":h[4].charAt(1):" ",v=h[6]-String(a).length,p=h[6]?i(d,v):"",f.push(h[5]?a+p:p+a)}}return f.join("")},t.cache={},t.parse=function(e){var t=e,n=[],r=[],i=0;while(t){if((n=/^[^\x25]+/.exec(t))!==null)r.push(n[0]);else if((n=/^\x25{2}/.exec(t))!==null)r.push("%");else{if((n=/^\x25(?:([1-9]\d*)\$|\(([^\)]+)\))?(\+)?(0|'[^$])?(-)?(\d+)?(?:\.(\d+))?([b-fosuxX])/.exec(t))===null)throw"[sprintf] huh?";if(n[2]){i|=1;var s=[],o=n[2],u=[];if((u=/^([a-z_][a-z_\d]*)/i.exec(o))===null)throw"[sprintf] huh?";s.push(u[1]);while((o=o.substring(u[0].length))!=="")if((u=/^\.([a-z_][a-z_\d]*)/i.exec(o))!==null)s.push(u[1]);else{if((u=/^\[(\d+)\]/.exec(o))===null)throw"[sprintf] huh?";s.push(u[1])}n[2]=s}else i|=2;if(i===3)throw"[sprintf] mixing positional and named placeholders is not (yet) supported";r.push(n)}t=t.substring(n[0].length)}return r};var n=function(e,n,r){return r=n.slice(0),r.splice(0,0,e),t.apply(null,r)};e.sprintf=t,e.vsprintf=n})(typeof exports!="undefined"?exports:window);

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
  url: (function(){
    var o={protocol:location.protocol,path:[],page:"",params:{}};
    location.pathname.substring(1).replace(/([^\/]+)/g,function(s,$1){o.path.push($1)});
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
  toEntities: function(s){
    if (!HN.$dummy_div)
      HN.$dummy_div = $("<div/>");
    return HN.$dummy_div.text(s||"").html();
  },
  noDbQuote: function(s){
    return s && s.replace(/"/,'&quot;');
  }
};

HN.TC = {
  get_pdt_fo_url: function(p_id, p_rn, f_id){ return HN.TC.URL+"produits/"+f_id+"-"+p_id+"-"+p_rn+".html"; },
  get_pdt_bo_url: function(p_id){ return HN.TC.ADMIN_URL+"products/edit.php?id="+p_id; },
  get_pdt_pic_url: function(p_id, format, num){ return HN.TC.ADMIN_URL+"ressources/images/products/"+p_id+"-"+format+"-"+num+".jpg"; },
  get_dft_pdt_pic_url: function(format){ return HN.TC.PRODUCTS_IMAGE_SECURE_URL+"no-pic-"+format+".gif"; },
  get_adv_fo_url: function(a_id){ return HN.TC.URL+"fournisseur/"+a_id+".html"; },
  get_adv_bo_url: function(a_id){ return HN.TC.ADMIN_URL+"advertisers/edit.php?id="+a_id; },
  get_fam_fo_url: function(f_rn){ return HN.TC.URL+"familles/"+f_rn+".html"; },
  get_fam_bo_url: function(){ return ""; },
  get_fam_pdt_list_url: function(f_id){ return HN.TC.ADMIN_URL+"search.php?search_type=2&search="+f_id; },
  get_adv_cat_name: function(a_cat_id){ return HN.TC.adv_cat_list ? HN.TC.adv_cat_list[a_cat_id].name : "non défini"; },
  get_tva_rate: function(tva_code){ return HN.TC.tva_rates ? (function(){ var rate = 0; $.each(HN.TC.tva_rates, function(i,e){ if (parseInt(e.id)==tva_code) { rate = parseFloat(e.taux); return false; } }); return rate; }()) : 0 },
  get_formated_date: function(t){
    t = new Date(parseInt(t)*1000||Date.now());
    return sprintf("%02d", t.getDate()) + "/" + sprintf("%02d", t.getMonth()+1) + "/" + sprintf("%04d", t.getFullYear());
  },
  get_formated_datetime: function(t, dt_sep){
    t = new Date(parseInt(t)*1000||Date.now());
    return sprintf("%02d", t.getDate()) + "/" + sprintf("%02d", t.getMonth()+1) + "/" + sprintf("%04d", t.getFullYear()) + (dt_sep || " ") +
           sprintf("%02d", t.getHours()) + ":" + sprintf("%02d", t.getMinutes()) + ":" + sprintf("%02d", t.getSeconds());
  },
  get_date_object: function(datestring){
    var date = datestring.match(/\s*(\d{1,2})\s*[\/-]{1}\s*(\d{1,2})\s*[\/-]{1}\s*(\d{2,4})\s*(\D*\s*(\d{1,2}):(\d{1,2})[:\.]?(\d*))?\s*/)||[];
    return (new Date(date[3], date[2]-1, date[1], date[5]||0, date[6]||0, (date[7]||"").substr(0,2), (date[7]||"").substr(2,3)));
  },
  get_timestamp: function(date){
    return ((typeof date === "string" ? HN.TC.get_date_object(date) : date).getTime()/1000)|0;
  },
  removeDiacritics: function(str) { // from http://stackoverflow.com/questions/18123501/replacing-accented-characters-with-plain-ascii-ones
    var defaultDiacriticsRemovalMap = [
      {'base':'A', 'letters':/[\u0041\u24B6\uFF21\u00C0\u00C1\u00C2\u1EA6\u1EA4\u1EAA\u1EA8\u00C3\u0100\u0102\u1EB0\u1EAE\u1EB4\u1EB2\u0226\u01E0\u00C4\u01DE\u1EA2\u00C5\u01FA\u01CD\u0200\u0202\u1EA0\u1EAC\u1EB6\u1E00\u0104\u023A\u2C6F]/g},
      {'base':'AA','letters':/[\uA732]/g},
      {'base':'AE','letters':/[\u00C6\u01FC\u01E2]/g},
      {'base':'AO','letters':/[\uA734]/g},
      {'base':'AU','letters':/[\uA736]/g},
      {'base':'AV','letters':/[\uA738\uA73A]/g},
      {'base':'AY','letters':/[\uA73C]/g},
      {'base':'B', 'letters':/[\u0042\u24B7\uFF22\u1E02\u1E04\u1E06\u0243\u0182\u0181]/g},
      {'base':'C', 'letters':/[\u0043\u24B8\uFF23\u0106\u0108\u010A\u010C\u00C7\u1E08\u0187\u023B\uA73E]/g},
      {'base':'D', 'letters':/[\u0044\u24B9\uFF24\u1E0A\u010E\u1E0C\u1E10\u1E12\u1E0E\u0110\u018B\u018A\u0189\uA779]/g},
      {'base':'DZ','letters':/[\u01F1\u01C4]/g},
      {'base':'Dz','letters':/[\u01F2\u01C5]/g},
      {'base':'E', 'letters':/[\u0045\u24BA\uFF25\u00C8\u00C9\u00CA\u1EC0\u1EBE\u1EC4\u1EC2\u1EBC\u0112\u1E14\u1E16\u0114\u0116\u00CB\u1EBA\u011A\u0204\u0206\u1EB8\u1EC6\u0228\u1E1C\u0118\u1E18\u1E1A\u0190\u018E]/g},
      {'base':'F', 'letters':/[\u0046\u24BB\uFF26\u1E1E\u0191\uA77B]/g},
      {'base':'G', 'letters':/[\u0047\u24BC\uFF27\u01F4\u011C\u1E20\u011E\u0120\u01E6\u0122\u01E4\u0193\uA7A0\uA77D\uA77E]/g},
      {'base':'H', 'letters':/[\u0048\u24BD\uFF28\u0124\u1E22\u1E26\u021E\u1E24\u1E28\u1E2A\u0126\u2C67\u2C75\uA78D]/g},
      {'base':'I', 'letters':/[\u0049\u24BE\uFF29\u00CC\u00CD\u00CE\u0128\u012A\u012C\u0130\u00CF\u1E2E\u1EC8\u01CF\u0208\u020A\u1ECA\u012E\u1E2C\u0197]/g},
      {'base':'J', 'letters':/[\u004A\u24BF\uFF2A\u0134\u0248]/g},
      {'base':'K', 'letters':/[\u004B\u24C0\uFF2B\u1E30\u01E8\u1E32\u0136\u1E34\u0198\u2C69\uA740\uA742\uA744\uA7A2]/g},
      {'base':'L', 'letters':/[\u004C\u24C1\uFF2C\u013F\u0139\u013D\u1E36\u1E38\u013B\u1E3C\u1E3A\u0141\u023D\u2C62\u2C60\uA748\uA746\uA780]/g},
      {'base':'LJ','letters':/[\u01C7]/g},
      {'base':'Lj','letters':/[\u01C8]/g},
      {'base':'M', 'letters':/[\u004D\u24C2\uFF2D\u1E3E\u1E40\u1E42\u2C6E\u019C]/g},
      {'base':'N', 'letters':/[\u004E\u24C3\uFF2E\u01F8\u0143\u00D1\u1E44\u0147\u1E46\u0145\u1E4A\u1E48\u0220\u019D\uA790\uA7A4]/g},
      {'base':'NJ','letters':/[\u01CA]/g},
      {'base':'Nj','letters':/[\u01CB]/g},
      {'base':'O', 'letters':/[\u004F\u24C4\uFF2F\u00D2\u00D3\u00D4\u1ED2\u1ED0\u1ED6\u1ED4\u00D5\u1E4C\u022C\u1E4E\u014C\u1E50\u1E52\u014E\u022E\u0230\u00D6\u022A\u1ECE\u0150\u01D1\u020C\u020E\u01A0\u1EDC\u1EDA\u1EE0\u1EDE\u1EE2\u1ECC\u1ED8\u01EA\u01EC\u00D8\u01FE\u0186\u019F\uA74A\uA74C]/g},
      {'base':'OI','letters':/[\u01A2]/g},
      {'base':'OO','letters':/[\uA74E]/g},
      {'base':'OU','letters':/[\u0222]/g},
      {'base':'P', 'letters':/[\u0050\u24C5\uFF30\u1E54\u1E56\u01A4\u2C63\uA750\uA752\uA754]/g},
      {'base':'Q', 'letters':/[\u0051\u24C6\uFF31\uA756\uA758\u024A]/g},
      {'base':'R', 'letters':/[\u0052\u24C7\uFF32\u0154\u1E58\u0158\u0210\u0212\u1E5A\u1E5C\u0156\u1E5E\u024C\u2C64\uA75A\uA7A6\uA782]/g},
      {'base':'S', 'letters':/[\u0053\u24C8\uFF33\u1E9E\u015A\u1E64\u015C\u1E60\u0160\u1E66\u1E62\u1E68\u0218\u015E\u2C7E\uA7A8\uA784]/g},
      {'base':'T', 'letters':/[\u0054\u24C9\uFF34\u1E6A\u0164\u1E6C\u021A\u0162\u1E70\u1E6E\u0166\u01AC\u01AE\u023E\uA786]/g},
      {'base':'TZ','letters':/[\uA728]/g},
      {'base':'U', 'letters':/[\u0055\u24CA\uFF35\u00D9\u00DA\u00DB\u0168\u1E78\u016A\u1E7A\u016C\u00DC\u01DB\u01D7\u01D5\u01D9\u1EE6\u016E\u0170\u01D3\u0214\u0216\u01AF\u1EEA\u1EE8\u1EEE\u1EEC\u1EF0\u1EE4\u1E72\u0172\u1E76\u1E74\u0244]/g},
      {'base':'V', 'letters':/[\u0056\u24CB\uFF36\u1E7C\u1E7E\u01B2\uA75E\u0245]/g},
      {'base':'VY','letters':/[\uA760]/g},
      {'base':'W', 'letters':/[\u0057\u24CC\uFF37\u1E80\u1E82\u0174\u1E86\u1E84\u1E88\u2C72]/g},
      {'base':'X', 'letters':/[\u0058\u24CD\uFF38\u1E8A\u1E8C]/g},
      {'base':'Y', 'letters':/[\u0059\u24CE\uFF39\u1EF2\u00DD\u0176\u1EF8\u0232\u1E8E\u0178\u1EF6\u1EF4\u01B3\u024E\u1EFE]/g},
      {'base':'Z', 'letters':/[\u005A\u24CF\uFF3A\u0179\u1E90\u017B\u017D\u1E92\u1E94\u01B5\u0224\u2C7F\u2C6B\uA762]/g},
      {'base':'a', 'letters':/[\u0061\u24D0\uFF41\u1E9A\u00E0\u00E1\u00E2\u1EA7\u1EA5\u1EAB\u1EA9\u00E3\u0101\u0103\u1EB1\u1EAF\u1EB5\u1EB3\u0227\u01E1\u00E4\u01DF\u1EA3\u00E5\u01FB\u01CE\u0201\u0203\u1EA1\u1EAD\u1EB7\u1E01\u0105\u2C65\u0250]/g},
      {'base':'aa','letters':/[\uA733]/g},
      {'base':'ae','letters':/[\u00E6\u01FD\u01E3]/g},
      {'base':'ao','letters':/[\uA735]/g},
      {'base':'au','letters':/[\uA737]/g},
      {'base':'av','letters':/[\uA739\uA73B]/g},
      {'base':'ay','letters':/[\uA73D]/g},
      {'base':'b', 'letters':/[\u0062\u24D1\uFF42\u1E03\u1E05\u1E07\u0180\u0183\u0253]/g},
      {'base':'c', 'letters':/[\u0063\u24D2\uFF43\u0107\u0109\u010B\u010D\u00E7\u1E09\u0188\u023C\uA73F\u2184]/g},
      {'base':'d', 'letters':/[\u0064\u24D3\uFF44\u1E0B\u010F\u1E0D\u1E11\u1E13\u1E0F\u0111\u018C\u0256\u0257\uA77A]/g},
      {'base':'dz','letters':/[\u01F3\u01C6]/g},
      {'base':'e', 'letters':/[\u0065\u24D4\uFF45\u00E8\u00E9\u00EA\u1EC1\u1EBF\u1EC5\u1EC3\u1EBD\u0113\u1E15\u1E17\u0115\u0117\u00EB\u1EBB\u011B\u0205\u0207\u1EB9\u1EC7\u0229\u1E1D\u0119\u1E19\u1E1B\u0247\u025B\u01DD]/g},
      {'base':'f', 'letters':/[\u0066\u24D5\uFF46\u1E1F\u0192\uA77C]/g},
      {'base':'g', 'letters':/[\u0067\u24D6\uFF47\u01F5\u011D\u1E21\u011F\u0121\u01E7\u0123\u01E5\u0260\uA7A1\u1D79\uA77F]/g},
      {'base':'h', 'letters':/[\u0068\u24D7\uFF48\u0125\u1E23\u1E27\u021F\u1E25\u1E29\u1E2B\u1E96\u0127\u2C68\u2C76\u0265]/g},
      {'base':'hv','letters':/[\u0195]/g},
      {'base':'i', 'letters':/[\u0069\u24D8\uFF49\u00EC\u00ED\u00EE\u0129\u012B\u012D\u00EF\u1E2F\u1EC9\u01D0\u0209\u020B\u1ECB\u012F\u1E2D\u0268\u0131]/g},
      {'base':'j', 'letters':/[\u006A\u24D9\uFF4A\u0135\u01F0\u0249]/g},
      {'base':'k', 'letters':/[\u006B\u24DA\uFF4B\u1E31\u01E9\u1E33\u0137\u1E35\u0199\u2C6A\uA741\uA743\uA745\uA7A3]/g},
      {'base':'l', 'letters':/[\u006C\u24DB\uFF4C\u0140\u013A\u013E\u1E37\u1E39\u013C\u1E3D\u1E3B\u017F\u0142\u019A\u026B\u2C61\uA749\uA781\uA747]/g},
      {'base':'lj','letters':/[\u01C9]/g},
      {'base':'m', 'letters':/[\u006D\u24DC\uFF4D\u1E3F\u1E41\u1E43\u0271\u026F]/g},
      {'base':'n', 'letters':/[\u006E\u24DD\uFF4E\u01F9\u0144\u00F1\u1E45\u0148\u1E47\u0146\u1E4B\u1E49\u019E\u0272\u0149\uA791\uA7A5]/g},
      {'base':'nj','letters':/[\u01CC]/g},
      {'base':'o', 'letters':/[\u006F\u24DE\uFF4F\u00F2\u00F3\u00F4\u1ED3\u1ED1\u1ED7\u1ED5\u00F5\u1E4D\u022D\u1E4F\u014D\u1E51\u1E53\u014F\u022F\u0231\u00F6\u022B\u1ECF\u0151\u01D2\u020D\u020F\u01A1\u1EDD\u1EDB\u1EE1\u1EDF\u1EE3\u1ECD\u1ED9\u01EB\u01ED\u00F8\u01FF\u0254\uA74B\uA74D\u0275]/g},
      {'base':'oi','letters':/[\u01A3]/g},
      {'base':'ou','letters':/[\u0223]/g},
      {'base':'oo','letters':/[\uA74F]/g},
      {'base':'p','letters':/[\u0070\u24DF\uFF50\u1E55\u1E57\u01A5\u1D7D\uA751\uA753\uA755]/g},
      {'base':'q','letters':/[\u0071\u24E0\uFF51\u024B\uA757\uA759]/g},
      {'base':'r','letters':/[\u0072\u24E1\uFF52\u0155\u1E59\u0159\u0211\u0213\u1E5B\u1E5D\u0157\u1E5F\u024D\u027D\uA75B\uA7A7\uA783]/g},
      {'base':'s','letters':/[\u0073\u24E2\uFF53\u00DF\u015B\u1E65\u015D\u1E61\u0161\u1E67\u1E63\u1E69\u0219\u015F\u023F\uA7A9\uA785\u1E9B]/g},
      {'base':'t','letters':/[\u0074\u24E3\uFF54\u1E6B\u1E97\u0165\u1E6D\u021B\u0163\u1E71\u1E6F\u0167\u01AD\u0288\u2C66\uA787]/g},
      {'base':'tz','letters':/[\uA729]/g},
      {'base':'u','letters':/[\u0075\u24E4\uFF55\u00F9\u00FA\u00FB\u0169\u1E79\u016B\u1E7B\u016D\u00FC\u01DC\u01D8\u01D6\u01DA\u1EE7\u016F\u0171\u01D4\u0215\u0217\u01B0\u1EEB\u1EE9\u1EEF\u1EED\u1EF1\u1EE5\u1E73\u0173\u1E77\u1E75\u0289]/g},
      {'base':'v','letters':/[\u0076\u24E5\uFF56\u1E7D\u1E7F\u028B\uA75F\u028C]/g},
      {'base':'vy','letters':/[\uA761]/g},
      {'base':'w','letters':/[\u0077\u24E6\uFF57\u1E81\u1E83\u0175\u1E87\u1E85\u1E98\u1E89\u2C73]/g},
      {'base':'x','letters':/[\u0078\u24E7\uFF58\u1E8B\u1E8D]/g},
      {'base':'y','letters':/[\u0079\u24E8\uFF59\u1EF3\u00FD\u0177\u1EF9\u0233\u1E8F\u00FF\u1EF7\u1E99\u1EF5\u01B4\u024F\u1EFF]/g},
      {'base':'z','letters':/[\u007A\u24E9\uFF5A\u017A\u1E91\u017C\u017E\u1E93\u1E95\u01B6\u0225\u0240\u2C6C\uA763]/g}
    ];
    for (var i=0; i<defaultDiacriticsRemovalMap.length; i++) {
      str = str.replace(defaultDiacriticsRemovalMap[i].letters, defaultDiacriticsRemovalMap[i].base);
    }
    return str;
  },
  toDashAz09: function(str){
    return this.removeDiacritics(str).toLowerCase().replace(/[^a-z0-9-]/g, '-');
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
  get_dial_html: function(tel){
    return tel == "" ? "" : "<a href=\"tel:"+tel+"\">"+tel+" <span class=\"icon telephone\"></span></a>";
  },
  getAjaxErrorText: function(jqXHR, textStatus, errorThrown){
    var error;
    try { error = $.parseJSON(jqXHR.responseText)['error']; }
    catch (e) { error = textStatus+" : "+errorThrown }
    return error;
  },
  CustomerTitleList: function(){
    titleList = new Array();
    titleList[1] = "M.";
    titleList[2] = "Mme";
    titleList[3] = "Mlle";
    return titleList;
  },
  getCustomerTitleIndexFromLabel: function(label){
    var titreList = HN.TC.CustomerTitleList();

    for(var a=1; a<=titreList.length; a++){
      if(titreList[a] == label)
        return a;
    }
    return false;
  },
  buildCustomerTitleSelectTag: function(select){
    ret = $("<select>");
    var titreList = HN.TC.CustomerTitleList();
    var values = new Array();
    values[1] = { value: 1, text: titreList[1] };
    values[2] = { value: 2, text: titreList[2] };
    values[3] = { value: 3, text: titreList[3]};
    for(var a=1; a<values.length; a++)
      switch(typeof select){
        case 'string':
          if(values[a].text == select)
            values[a]['selected'] = 'selected';
          break;
        case 'number':
        case 'integer':
        case 'int':
          if(a == select)
            values[a]['selected'] = 'selected';
          break;
      }
    ret.append($("<option>",values[1]));
    ret.append($("<option>",values[2]));
    ret.append($("<option>",values[3]));
    return ret;
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
      url: HN.TC.ADMIN_URL+"ressources/ajax/AJAX_Doctrine_Interface.php",
      data: { type: "Doctrine_query", data: me.getAsObject() },
      dataType: "json",
      error: function(jqXHR, textStatus, errorThrown){},
      success: function(data, textStatus, jqXHR){
        me.response = data;
        if (data.success) {
          cb(data.data);
        }
        else {
          console.log(data.errorMsg);
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
}
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
}

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
      url: HN.TC.ADMIN_URL+"ressources/ajax/AJAX_Doctrine_Interface.php",
      data: { type: "Doctrine_multiple_queries", data: me.getAsObject() },
      dataType: "json",
      error: function(jqXHR, textStatus, errorThrown){},
      success: function(data, textStatus, jqXHR){
        me.response = data;
        if (data.success) {
          cb(data.data);
        }
        else {
          console.log(data.errorMsg);
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
        //console.log(textStatus);
      },
      success: function(data, textStatus, jqXHR){
        me.response = data;
        if (data && data.success) {
          if(typeof(cb) == 'function')
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

HN.TC.AjaxNuukik = {
  get: function(zoneId, controller, path, params){
    return $.ajax({
      type: "POST",
      url: HN.TC.SECURE_RESSOURCES_URL+"ajax/AJAXNuukik.php",
      data: { zoneId: zoneId, controller: controller, path: path, params: params},
      dataType: "json"
    });
  }
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
    me.feedFunc.call(this, val, function(data){
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

HN.TC.InternalNotes = function(_data){
  this.data = $.extend({
      id_reference: "",
      context: 1,
      content: ""
    }, _data);
  this.ado = HN.TC.AjaxDoctrineObject.create()
    .setObject("InternalNotes")
    .setData(this.data);

};
HN.TC.InternalNotes.prototype = {
  setRefId: function(val){ this.data.id_reference = val; return this; },
  setContext: function(val){ this.data.context = val; return this; },
  setContent: function(val){ this.data.content = val; return this; },
  create: function(cb){
    this.data.id_reference = this.data.id_reference.toString();
    if (this.data.id_reference.length && this.data.content != "")
      this.ado.create(cb);
  }
};
HN.TC.InternalNotes.getAll = function(context, id_reference, cb){
  HN.TC.AjaxQuery.create()
    .select("i.*, o.login as operator_name")
    .from("InternalNotes i")
    .leftJoin("i.operator o")
    .where("i.context = ?",context)
    .andWhere("i.id_reference = ?", id_reference)
    .orderBy("timestamp desc")
    .execute(function(data){
      for (var i=0; i<data.length; i++)
        data[i].timestamp = HN.TC.get_formated_datetime(data[i].timestamp);
      cb(data);
    });
};

HN.TC.Messenger = function(_data){
  this.data = $.extend({
      id_sender: 0,
      type_sender: 0,
      id_recipient: 0,
      type_recipient: 0,
      text: "",
      context: 0,
      reference_to: 0
    }, _data);
  this.attachmentCtx = "";
  this.ado = HN.TC.AjaxDoctrineObject.create().setObject("Messenger");
};
HN.TC.Messenger.prototype = {
  setIdSender: function(val){ this.data.id_sender = parseInt(val) || 0; return this; },
  setTypeSender: function(val){ this.data.type_sender = parseInt(val) || 0; return this; },
  setIdRecipient: function(val){ this.data.id_recipient = parseInt(val) || 0; return this; },
  setTypeRecipient: function(val){ this.data.type_recipient = parseInt(val) || 0; return this; },
  setText: function(val){ this.data.text = val.toString(); return this; },
  setContext: function(val){ this.data.context = parseInt(val) || 0; return this; },
  setReferenceTo: function(val){ this.data.reference_to = parseInt(val) || 0; return this; },
  setAttachmentCtx: function(val){ this.attachmentCtx = val.toString(); return this; },
  postMessage: function(cb){
    var listMailsMultipleRecipient = $('input[name=secondaryContacts]') ? $('input[name=secondaryContacts]').val() : '';
    if (this.data.id_sender && this.data.type_sender && this.data.id_recipient && this.data.type_recipient && this.data.context && this.data.reference_to && this.data.text.length > 0 && this.attachmentCtx.length > 0)
      this.ado.setMethod("postMessage").setData([this.data, this.attachmentCtx, listMailsMultipleRecipient]).execute(cb);
  },
  getConversation: function(cb){
    if (this.data.id_sender && this.data.context && this.data.reference_to) {
      this.ado.setMethod("getConversation").setData([this.data.context, this.data.id_sender, this.data.reference_to]).execute(function(data){
        for (var i=0; i<data.length; i++)
          data[i].timestamp = HN.TC.get_formated_datetime(data[i].timestamp);
        cb(data);
      });
    }
  },
  close: function(cb){
    if (this.data.context && this.data.reference_to) {
      this.ado.setMethod("closeConversation").setData([this.data.context, this.data.reference_to]).execute(function(data){
        cb(data);
      });
    }
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
  me.$fdb = $("<div>",{ "class": "il-filters" }).dialog({
    width: 500,
    autoOpen: false,
    modal: true,
    buttons: {
      "Ajouter": function(){
        me.fdbAddFunc();
        $(this).dialog("close");
      }
    },
    open: function(event, ui){
      if (me.$fdb.data("type") == "date") {
        me.$fdb.find("input").datepicker();
      }
    },
    close: function(event, ui){
      me.$fdb.find("input.hasDatepicker").datepicker("destroy");
    }
  });
  // append filter select
  $("<select>", {
    change: function(){
      var $val = $(this).val(),
          $option = $(this).children(":selected");
      // show 1 or 2 inputs
      me.$fdb.find("input").hide().filter(":lt("+($option.data("filter")=="between"?2:1)+")").show();
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
    var qsl = this.source.fields.match(/((?:(?:\([^\)]*\))|[^,])+)(?=,)?/g) // ignore ',' inside ()
    for (var i=0; i<qsl.length; i++) {
      var qs = qsl[i].split(/\s+as\s+/i),
          n, s, h = false;
      if (qs.length > 1) { // we have an alias
        n = qs[1];
        if (qs[0].match(/\([^\)]+\)/)) { // we have parentheses, so we probably have a function and must use HAVING instead of WHERE
          s = n;
          h = true;
        } else {
          s = qs[0]
        }
      } else {
        qs = qs[0].match(/(\S+\.)?(\S+)/);
        n = qs[2];
        s = qs[0];
      }
      if (this.colsByName[n]) {
        this.colsByName[n].source = s;
        this.colsByName[n].having = h;
      }
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
  addOnRowEvent: function(rel){
    var me = this;
    me.onRowEvent = rel;
    for (var re in rel)
      this.$tbody.on(re, "tr", function(e){ rel[re].call(this, me.items[parseInt($(this).data("index"))], e); });
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
        q[me.cols[i].having?"addHaving":"andWhere"].apply(q, me.cols[i].getActiveFiltersParams());
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
          row_html += "<td class=\""+col.type+" "+col.name+"\">"+(col.onCellWrite ? col.onCellWrite.call(col, line, col.name)||line[col.name]||"" : line[col.name])+"</td>";
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
              { text: "&#x2039;", offset: cpo-ipp  , "class": (curPage-1>0 ? "" : "hidden") },
              { text: curPage-2 , offset: cpo-ipp*2, "class": (curPage-2>0 ? "" : "hidden") },
              { text: curPage-1 , offset: cpo-ipp  , "class": (curPage-1>0 ? "" : "hidden") },
              { text: curPage   , offset: cpo      , "class": "current" },
              { text: curPage+1 , offset: cpo+ipp  , "class": (curPage+1<=maxPage ? "" : "hidden") },
              { text: curPage+2 , offset: cpo+ipp*2, "class": (curPage+2<=maxPage ? "" : "hidden") },
              { text: "&#x203a;", offset: cpo+ipp  , "class": (curPage+1<=maxPage ? "" : "hidden") },
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
    me.$fdb.data("type", col.type);
    var $select = me.$fdb.find("select").empty().append(col.domOptions.clone(true)).change(),
        $inputs = me.$fdb.find("input").empty();
    $select.val($select.find("option").first().val());
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
        having: false,
        constStrings: {}, // for "const" type
        onCellWrite: (function(){
          switch (settings.type) {
            case "date":
              return function(rowData, colName){
                return rowData[colName]|0 ? HN.TC.get_formated_datetime(rowData[colName]) : "-";
              };
            case "date_only":
              return function(rowData, colName){
                return rowData[colName]|0 ? HN.TC.get_formated_date(rowData[colName]) : "-";
              };
            case "price":
              return function(rowData, colName){
                return sprintf("%0.2f",Math.round(rowData[colName]*100)/100)+"€";
              };
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

  me.domOptions = $();
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
              me.domOptions = me.domOptions.add($("<option>", { value: fi, html: HN.TC.ItemList.FILTER_strings[f] }).data("filter", f));
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
}
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
        if (f == "like")
          d[0] += "%";
        switch (me.type) {
          case "date": // overwrite some stuff when it's a date
            var t1 = HN.TC.get_timestamp(d[0]),
                t2 = f == "=" ? t1+86400 : HN.TC.get_timestamp(d[1]);
            colQueries.push("("+me.source+" >= ? AND "+me.source+" <= ?)");
            colParams.push(t1, t2);
            break;
          case "price":
            if (f == "=") {
              var p1 = Math.floor(d[0]*100)/100;
              colQueries.push("("+me.source+" >= ? AND "+me.source+" < ?)");
              colParams.push(p1, p1+0.01);
            }
            else {
              colQueries.push(me.source+" "+f+" ?");
              colParams.push(d[0]);
            }
            /*var p1 = Math.round(d[0]*100)/100-0.005,
                p2 = f == "=" ? p1+0.01 : Math.round(d[1]*100)/100;
            colQueries.push("("+me.source+" > ? AND "+me.source+" < ?)");
            colParams.push(p1, p2);*/
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
}
HN.TC.ItemList.FILTER_strings = {
  "=": "est égal à",
  ">": "est > à",
  "<": "est < à",
  ">=": "est >= à",
  "<=": "est <= à",
  "like": "contient",
  "between": "entre..."
};

HN.TC.Cart = {
  pre_id: "",
  htmlInit: function(){
    var me = this,
        pre_id = me.pre_id;

    // internal notes
    $(pre_id+"-show-note").on("click", function(){
      $(this).hide();
      $(pre_id+"-add-note, "+pre_id+"-cancel-note").show();
      $(pre_id+"-note").show(300);
    });
    $(pre_id+"-add-note").on("click", function(){
      me.internal_note.setContent($(pre_id+"-note textarea").val()).create(function(){
        $(pre_id+"-add-note").hide();
        $(pre_id+"-cancel-note").hide();
        $(pre_id+"-show-note").show();
        $(pre_id+"-note textarea").val("");
        $(pre_id+"-note").hide(300);
        me.loadInternalNotes(true);
      });
    });
    $(pre_id+"-cancel-note").on("click", function(){
      $(this).hide();
      $(pre_id+"-add-note").hide();
      $(pre_id+"-show-note").show();
      $(pre_id+"-note").hide(300);
    });

    // conversation
    $(pre_id+"-show-post").on("click", function(){
      $(this).hide();
      $(pre_id+"-add-post, "+pre_id+"-cancel-post").show();
      $(pre_id+"-post").show(300);
    });
    $(pre_id+"-add-post").on("click", function(){
      me.conversation
        .setText($(pre_id+"-post textarea").val())
        .setAttachmentCtx(me.pjMessFile.context) // get the context of the ajax upload file object setting, not the filelist (not pjMessFile'S')
        .postMessage(function(data){
          $(pre_id+"-add-post").hide();
          $(pre_id+"-cancel-post").hide();
          $(pre_id+"-show-post").show();
          $(pre_id+"-post").hide(300, function(){
            $(pre_id+"-post textarea").val("");
            $(pre_id+"-attachment-list").empty();
          });
          me.loadConversation(true);
        });
    });
    $(pre_id+"-cancel-post").on("click", function(){
      $(this).hide();
      $(pre_id+"-add-post").hide();
      $(pre_id+"-show-post").show();
      $(pre_id+"-post").hide(300);
    });
    // button to add an attachment
    $(pre_id+"-add-msn-attachment").on("click", function(){
      me.$uploadMsnAttachmentDb.dialog("open");
    });
    // show attachment related to a post
    $(pre_id+"-conversation").on("click", ".attach", function(){
      var $this = $(this);
      $this.next().css({ left: $this.position().left+20 }).toggle(300);
    });
    // close the current conversation
    $(pre_id+"-close-conv").on("click", function(){
      me.conversation.close(function(data){
        $(pre_id+"-close-conv").hide();
      });
    });

    // folding block
    $(pre_id).on("click", ".fold-block", function(e){
      var $e = $(e.target),
          $fb = $(this);
      if ($e.hasClass("icon-fold")) {
        if ($e.hasClass("folded"))
          $fb.find(".fold-content").slideDown(function(){
            $fb.removeClass("folded").addClass("unfolded");
          });
        else
          $fb.find(".fold-content").slideUp(function(){
            $fb.removeClass("unfolded").addClass("folded");
          });
      }
    });

    // ignore fdp/tva if needed
    if (me.data.activity != null)
      me.updateCartActivityState();
  },
  getUploadedFilesList: function(){
    var me = this;
    var uploadedFiles = new HN.TC.getUploadedFiles({
      itemId: me.data.id,
      context: me.uploadFile.context
    });
    var r = uploadedFiles.getUploadedFilesList();

    $(me.pre_id+"-doc-list").empty();
    for (var i=0; i<r.list.length; i++) (function(){
      var f = r.list[i],
          fn = f.alias_filename || f.filename;
      $("<li>")
        .append("<span>"+fn+"."+f.extension+"</span>")
        .append($("<a>", {
          "class": "_blank",
          "title": "Voir",
          href: r.directory+f.filename+"."+f.extension,
          html: "<span class=\"icon eye\"></span>"
        }))
        .append($("<span>", {
          "class": "icon cross",
          "title": "Supprimer",
          click: function(){ me.deleteUploadedFile(f.id, fn+"."+f.extension); }
        }))
        .appendTo(me.pre_id+"-doc-list");
    }());
  },
  deleteUploadedFile: function(fileId, filename){
    if (confirm("Souhaitez-vous supprimer le fichier "+filename+" ?")) {
      var deleteFile = new HN.TC.deleteUploadedFile(fileId);
      deleteFile.deleteFileFunction();
    }
    this.getUploadedFilesList();
  },
  getPjMessFilesList: function(){
    var me = this;
    var pjMessFiles = new HN.TC.getUploadedFiles({
      itemId: me.data.id,
      context: me.pjMessFile.context // same context as the ajaxUploadFile object
    });
    me.pjMessFiles = pjMessFiles.getUploadedFilesList();

    $(me.pre_id+"-attachment-list").empty();
    for (var i=0; i<me.pjMessFiles.list.length; i++) (function(){
      var f = me.pjMessFiles.list[i],
          fn = f.alias_filename || f.filename;
      $("<li>")
        .append("<span>"+fn+"."+f.extension+"</span>")
        .append($("<a>", {
          "class": "_blank",
          "title": "Voir",
          href: me.pjMessFiles.directory+f.filename+"."+f.extension,
          html: "<span class=\"icon eye\"></span>"
        }))
        .append($("<span>", {
          "class": "icon cross",
          "title": "Supprimer",
          click: function(){ me.deletePjMessFile(f.id, fn+"."+f.extension); }
        }))
        .appendTo(me.pre_id+"-attachment-list");
    }());
  },
  deletePjMessFile: function(fileId, filename){
    if (confirm("Souhaitez-vous supprimer le fichier "+filename+" ?")) {
      var deletePjFile = new HN.TC.deleteUploadedFile(fileId);
      deletePjFile.context = this.pjMessFile.context; // same context as the ajaxUploadFile object
      deletePjFile.deleteFileFunction();
    }
    this.getPjMessFilesList();
  },
  loadClient: function(id){
    var me = this,
        cb = $.isFunction(arguments[1]) ? arguments[1] : false;
    HN.TC.AjaxQuery.create()
      .select("id AS client_id,"+
              "email,"+
              "titre,"+
              "nom,"+
              "prenom,"+
              "societe,"+
              "adresse,"+
              "complement AS cadresse,"+
              "ville,"+
              "cp,"+
              "pays,"+
              "infos_sup AS delivery_infos,"+
              "tel1 AS tel,"+
              "fax1 AS fax,"+
              "IF(coord_livraison,titre_l,titre) AS titre2,"+
              "IF(coord_livraison,nom_l,nom) AS nom2,"+
              "IF(coord_livraison,prenom_l,prenom) AS prenom2,"+
              "IF(coord_livraison,societe_l,societe) AS societe2,"+
              "IF(coord_livraison,adresse_l,adresse) AS adresse2,"+
              "IF(coord_livraison,complement_l,complement) AS cadresse2,"+
              "IF(coord_livraison,ville_l,ville) AS ville2,"+
              "IF(coord_livraison,cp_l,cp) AS cp2,"+
              "IF(coord_livraison,pays_l,pays) AS pays2,"+
              "IF(coord_livraison,tel2,tel1) AS tel2,"+
              "IF(coord_livraison,fax2,fax1) AS fax2,"+
              "code AS client_code,"+
              "tva_intra")
      .from("Clients")
      .where("actif = ?", 1)
      .andWhere("id = ?", id)
      .fetchOne(function(data){
        if (data) {
          delete data.id;
          $.extend(me.data, data);
          me.updateHtmlData(data);
          if (cb) cb();
        }
        else {
          $(me.pre_id+"-error-msg").text("Le client ayant pour identifiant le n°"+id+" n'existe pas.").stop().stop().hide().fadeIn(67).fadeOut(100).fadeIn(67).delay(10000).fadeOut(1500);
        }
      });
  },
  updateHtmlData: function(data){
    $(".c_i").each(function(){
      var $this = $(this),
          fn = $this.data("cart-info"),
          tagName = $this.prop("tagName").toLowerCase(),
          type = tagName == "input" ? $this.attr("type") : "",
          text = data[fn];
      if (text !== undefined) {
        if (/^tel\d?/.test(fn) && tagName != "input" && tagName != "textarea")
          $this.html(HN.TC.get_dial_html(text));
        else if (tagName == "input" && type == "checkbox")
          $this.prop("checked", !!text);
        else if (tagName == "input" || tagName == "select" || tagName == "textarea")
          $this.val(text);
        else
          $this.html(text);
        if ($this.data("info-value") !== undefined)
          $this.data("info-value", text);
      }
    });
  },
  loadInternalNotes: function(show){
    var me = this;
    HN.TC.InternalNotes.getAll(this.internal_note.data.context, this.internal_note.data.id_reference, function(data){
      var $in = $(me.pre_id+"-notes");
      $ul = $in.find("ul").empty();
      if (data.length) {
        for (var i=0; i<data.length; i++) {
          var note = data[i];
          $("<li>")
            .append($("<div>", { "class": "header", html: "Message de "+note.operator_name+" envoyé le "+note.timestamp }))
            .append($("<div>", { "class": "content", html: note.content }))
            .appendTo($ul);
        }
        $in.show()
        if (show)
          $in.find("span.icon-fold.folded").click();
      }
      else {
        $in.hide();
      }
    });
  },
  loadConversation: function(show){
    var me = this;
    me.conversation.getConversation(function(data){
      var $in = $(me.pre_id+"-conversation"),
          $ul = $in.find("ul").empty(),
          html = "";
      if (data.length) {
        for (var i=0; i<data.length; i++) {
          var post = data[i];
          html += "<li>"+
                    "<div class=\"header\">Message de "+post.sender_name+" à "+post.recipient_name+" envoyé le "+post.timestamp+"</div>";
          if (post.attachments.length) {
            html += "<div class=\"clip icon attach\"></div>"+
                    "<div class=\"files\">";
            for (var ai=0; ai<post.attachments.length; ai++) {
              var f = post.attachments[ai].file;
              html += "<a href=\""+f.url+"\" class=\"_blank\">"+f.shown_name+"</a><br/>";
            }
            html += "</div>";
          }
          html +=   "<div class=\"content\">"+post.text+"</div>"+
                  "</li>";
        }
        $ul.html(html);
        $in.show();
        if (show)
          $in.find("span.icon-fold.folded").click();
      }
      else {
        $in.hide();
      }
    });
  },
  updateCartActivityState: function(){
    if ($.inArray(this.data.activity|0, this.constructor.activityNoFdpList) === -1) {
      this.ci.options.fdp = this.constructor == HN.TC.Estimate && this.data.type == HN.TC.Estimate.TYPE_AD_HOC ? false : true;
      $(this.pre_id+"-fdp-line").show();
    } else {
      this.ci.options.fdp = false;
      $(this.pre_id+"-fdp-line").hide();
    }

    this.ci.options.tva = $.inArray(this.data.activity|0, this.constructor.activityNoTvaList) === -1;

    if ($.inArray(this.data.activity|0, this.constructor.activityTvaIntraList) === -1) {
      $(this.pre_id+"-tva-intra-line").hide();
    } else {
      $(this.pre_id+"-tva-intra-line").show();
    }
  },
  setClientFileLink: function(){
    var val = $(this.pre_id+"-client_id").val();
    if (val != "") {
      $(this.pre_id+"-goto-client")
        .show()
        .attr("href", HN.TC.ADMIN_URL+"clients/?idClient="+val)
        .html("Aller à la fiche client &#x00bb;");
    } else {
      $(this.pre_id+"-goto-client").hide();
    }
  },
  saveDelayed: function(to){
    var me = this;
    clearTimeout(me.saveTO);
    me.saveTO = setTimeout(function(){
      me.save.call(me);
    },to||1000)
  },
  save: function(){
    var me = this;
    clearTimeout(me.saveTO);
    if (me.ci)
      me.ci.saveCart();
    $(".c_i").each(function(){
      var $this = $(this);
      me.data[$this.data("cart-info")] = $this.data("info-value") != null ?
        $this.data("info-value") :
        ($this.attr("type") == "checkbox" ?
          ($this.val() == "on" ? $this.prop("checked")|0 : ($this.prop("checked") ? $this.val() : "")) :
          ($.inArray($this[0].nodeName.toLowerCase(), ["input", "select", "textarea"]) !== -1 ?
            $.trim($this.val()) :
            $.trim($this.text())
          )
        );
    });
  }
};
HN.TC.ItemCart = function(settings, data){
  $.extend(true, this, {
    options: {
      fdp: true,
      tva: true,
      comment: true,
      sup_comment: false
    }
  }, settings);
  var me = this,
      pre_id = me.pre_id;

  if (data != null)
    me.data = data;
  me.pdtList = [];

  me.init = function(){

    // a little trick to avoid auto add when focusout is fired from the auto complete pdt search as we click on the product detail layer
    var preventAutoAdd = false;
    $(pre_id+"-pdt-detail")
      .on("mousedown", function(){ preventAutoAdd = true; })
      .on("mouseup", function(){ preventAutoAdd = false; });

    // delegated items table events
    var laaci = null; // Last Active Auto Complete Input
    $(pre_id+"-items")
      .on("keyup blur", "input.autocomplete-ref-search", function(e){ // autocomplete pdt search

        var input = this,
            val = $(input).val(),
            w = $(input).outerWidth(),
            pos = $(input).position(),
            delay = (e.type == "focusout" && !preventAutoAdd) || e.keyCode == 13 ? 0 : 500;

        // storing the number of line added via this input
        if (!$(input).data("line_added_count"))
          $(input).data("line_added_count", 0);

        if (val != "") {
          me.getPdtListDelayed(val, delay).done(function(justLoaded){
            //console.log(val, me.pdtList.length, input != laaci, e.keyCode);
            if (justLoaded || input != laaci || e.keyCode == 13) {
              laaci = input; // this is the latest active input
              if (me.pdtList.length) {
                if (me.pdtList.length == 1 && me.pdtList[0].references) { // only one product = directly show product detail layer if there is at least 1 reference
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
            var previewAddInfos = me.getPdtPreviewsLines(),
                detailAddInfos = me.getPdtDetailLines();
            if (delay == 0 && me.pdtList.length == 1 && (previewAddInfos.length + detailAddInfos.length) == 1) {
              me.addLine(previewAddInfos[0] || detailAddInfos[0], 1);
              me.hidePdtPreviews();
              me.hidePdtDetail();
              me.deleteLine($(laaci).closest("tr"));
            }
          });
        }
      })
      .on("click", "span.icon.basket-delete", function(){
        me.deleteLine($(this).closest("tr"));
      })
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
              if (me.onUpdate)
                me.onUpdate();
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

    // delegated global events
    $(pre_id)
      .on("click", "td.qty img", function(){
        var $input = $(this).parent().find("input"),
            qty = parseInt($input.val());
        if (isNaN(qty) || qty < 1)
          qty = 1;
        else
          if ($(this).hasClass("add"))
            qty++;
          else if ($(this).hasClass("sub") && qty > 1)
            qty--;
        $input.val(qty);
      })
      .on("keypress", "td.qty input", function(e){
        if (e.charCode != 0 && (e.charCode < 48 || e.charCode > 57))
          return false;
      })
      .on("blur", "td.qty input", function(){
        $(this).val(Math.abs($(this).val())||1);
      })
      .on("click", "span.icon.basket-add", function(){
        var qty = $(this).closest("td.tool").prevAll("td.qty").last().find("input").val() || 1;
        me.addLine($(this).data("line"), qty);
        $(laaci).data("line_added_count", $(laaci).data("line_added_count")+1);
      });

    // delegated items table event, after the global ones
    $(pre_id)
      .on("click", pre_id+"-items td.qty img", function(){
        me.updateCartCalcs();
      })
      .on("blur", pre_id+"-items td.qty input", function(){
        me.updateCartCalcs();
      })

    // add product line button
    $(pre_id+"-add-line").on("click", function(){
      me.addAutocompleteLine();
    });

    // create product button
    $(pre_id+"-create-ref").on("click", function(){
      me.addProductCreationLine();
    });

    // editable addresses and global comment
    $(pre_id+"-addresses,"+pre_id+"-global-comment")
      .on("dblclick", "tbody td", function(){
        $(this).closest("table").find("thead th").eq($(this).index()).find("span.edit:visible").click();
      })
      .on("click", "span.edit", function(){
        var $th = $(this).closest("th"),
            $td = $(this).hide().closest("table").find("tbody").find("td").eq($th.index());
        $th.find("span.accept, span.cancel").css({ display: "inline-block" });
        $td.find("div > span").each(function(){
          var $span = $(this),
              texte = $.trim($span.text());
              switch($span.data("edit-type")){
              case "textarea":
                ret = $("<textarea>", { value: texte });
                break;
              case "select-title":
                ret = HN.TC.buildCustomerTitleSelectTag(texte);
                break;
              default:
                ret = $("<input>",{ type: "text", value: texte });
            }
         $span.data("old_val", texte).empty().append(ret);
        }).find("input, textarea").first().focus();
      })
      .on("click", "span.accept", function(){
        var titre = HN.TC.CustomerTitleList();

        var $th = $(this).closest("th"),
            $td = $(this).hide().closest("table").find("tbody").find("td").eq($th.index()),
            $cil = $(".c_i"); // cart info list update any other corresponding cart info in the page
        $th.find("span.cancel").hide().end().find("span.edit").css({ display: "inline-block" });
        $td.find("span").each(function(){
          var $span = $(this),
              text = $span.find("input, textarea, select").val(),
              $ci = $cil.filter("[data-cart-info='"+$span.data("cart-info")+"']").empty();
          $ci.html(/^tel\d?/.test($ci.data("cart-info")) ? HN.TC.get_dial_html(text) : (($ci.data("cart-info") == 'titre' || $ci.data("cart-info") == 'titre2') ? titre[text] : text));
        });
        if (me.onUpdate)
          me.onUpdate();
      })
      .on("click", "span.cancel", function(){
        var $th = $(this).closest("th"),
            $td = $(this).hide().closest("table").find("tbody").find("td").eq($th.index());
        $th.find("span.accept").hide().end().find("span.edit").css({ display: "inline-block" });
        $td.find("span").each(function(){
          var $span = $(this),
              text = $span.data("old_val");
          $span.empty().html(/^tel\d?/.test($span.data("cart-info")) ? HN.TC.get_dial_html(text) : text);
        });
      })
      .on("keydown", "tbody td", function(e){
        if (e.keyCode == 27) {
          $(this).closest("table").find("thead th").eq($(this).index()).find("span.cancel:visible").click();
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
          var $td = $(this).closest("td");
          $(this).closest("td").empty().text(sprintf("%.02f",parseFloat($(this).val()) || 0)+"€");
          me.updateCartCalcs();
        }
        else if (e.keyCode == 27) {
          $(this).blur();
          e.stopPropagation();
        }
      });

    $(window)
      .on("keydown", function(e){ // escape key hide every layers
        if (e.keyCode == 27) {
          me.hidePdtPreviews()
          me.hidePdtDetail();
          if ($(laaci).data("line_added_count") >= 1)
            me.deleteLine($(laaci).closest("tr"));
        }
      })
      .on("click", function(e){ // click out of specifics layers does the same
        if (!$(e.target).closest(pre_id+"-pdt-preview, "+pre_id+"-pdt-detail, "+pre_id+"-items input.autocomplete-ref-search").length) {
          me.hidePdtPreviews()
          me.hidePdtDetail();
          if ($(laaci).data("line_added_count") >= 1)
            me.deleteLine($(laaci).closest("tr"));
        }
      })

    // add the lines to the cart
    if (me.data.lines.length) {
      var fdp_ht = me.data.fdp_ht;
      for (var li=0; li<me.data.lines.length; li++)
        me.addLine(new HN.TC.ItemCart.Line(me.data.lines[li]), null, true);
      if (me.options.fdp && me.getDftFdp() != fdp_ht) { // force the right fdp and lock it if it's not equal to the auto one
        me.data.fdp_ht = fdp_ht;
        $(pre_id+"-fdp-ht").text(sprintf("%.02f",parseFloat(me.data.fdp_ht)||0)+"€").data("old_val", parseFloat(me.data.fdp_ht)||0);
      }
      me.updateCartCalcs(true);
    }
  };

  me.showPdtPreviews = function(x, y){
    $(pre_id+"-pdt-preview").empty().css({ left: x, top: y }).show();
    for (var i=0; i<me.pdtList.length; i++) (function(){
      var pi = i,
          pdt = me.pdtList[pi];
      //HN.TC.ItemCart.Line
      if (pdt.references) { // normal product with references
        $("<li>")
          .append("<div class=\"picture\"><img class=\"vmaib\" src=\""+HN.TC.get_pdt_pic_url(pdt.id,"thumb_small",1)+"\"/><div class=\"vsma\"></div></div>"+
                  "<div class=\"infos\">"+
                    "<div class=\"vmaib\">"+
                      "<div><strong>"+pdt.id+" - "+pdt.ref_name+"</strong></div>"+
                      "<div>"+pdt.fastdesc+"</div>"+
                      "<div>Fournisseur : <strong>"+pdt.sup_name+"</strong></div>"+
                    "</div><div class=\"vsma\"></div>"+
                  "</div>"+
                  "<div class=\"zero\"></div>")
          .on("mouseenter", function(){
            var ul_pos = $(pre_id+"-pdt-preview").position(),
                li_pos = $(this).position(),
                ul_w = $(pre_id+"-pdt-preview").outerWidth();
            me.showPdtDetail(pi, ul_pos.left+li_pos.left+ul_w-2, ul_pos.top+li_pos.top-pi*7-50);
          })
          .appendTo(pre_id+"-pdt-preview");
      }
      else { // standalone references
        var line = new HN.TC.ItemCart.Line(pdt);
        $("<li class=\"standalone\">")
          .append("<div class=\"infos\">"+
                    "<div class=\"vmaib\">"+
                      "<div class=\"lx2\"><strong>"+pdt.sup_ref+"</strong> - "+pdt.desc+"</div>"+
                      "<div>Fournisseur : <strong>"+pdt.sup_name+"</strong></div>"+
                    "</div><div class=\"vsma\"></div>"+
                  "</div>"+
                  "<div class=\"tool\"><span class=\"icon basket-add\"></span><span class=\"vsma\"></span></div>"+
                  "<div class=\"zero\"></div>")
          .find("span.basket-add").data("line",line).end()
          .on("mouseenter", function(){
            me.hidePdtDetail();
          })
          .appendTo(pre_id+"-pdt-preview");
      }
    }());
  };

  me.hidePdtPreviews = function(){
    $(pre_id+"-pdt-preview").empty().hide();
  };

  me.getPdtPreviewsLines = function(){
    var line_list = [];
    $(pre_id+"-pdt-preview").find("span.basket-add").each(function(){
      line_list.push($(this).data("line"));
    })
    return line_list;
  };

  me.showPdtDetail = function(pi, x, y){
    var pre_id_detail = pre_id+"-pdt-detail";
        pdt = me.pdtList[pi],
        refs = pdt.references;
    $(pre_id_detail).css({ left: x, top: y }).show();
    $(pre_id_detail+"-pic").attr("src", HN.TC.get_pdt_pic_url(pdt.id,"thumb_big",1));
    $(pre_id_detail+"-p-fo-url").attr("href", HN.TC.get_pdt_fo_url(pdt.id,pdt.ref_name,pdt.cat_id));
    $(pre_id_detail+"-p-bo-url").attr("href", HN.TC.get_pdt_bo_url(pdt.id));
    $(pre_id_detail+"-name").text(pdt.name);
    $(pre_id_detail+"-p-fastdesc").text(pdt.fastdesc);
    $(pre_id_detail+"-p-id").text(pdt.id);
    $(pre_id_detail+"-f-bo-pdt-list-url").attr("href", HN.TC.get_fam_pdt_list_url(pdt.cat_id));
    $(pre_id_detail+"-f-name").text(pdt.cat_name);
    $(pre_id_detail+"-a-bo-url").attr("href", HN.TC.get_adv_bo_url(pdt.adv_id));
    $(pre_id_detail+"-a-name").text(pdt.adv_name);
    $(pre_id_detail+"-references tbody").empty();
    for (var ri=0; ri<refs.length; ri++) {
      var ref = refs[ri],
          line = new HN.TC.ItemCart.Line(ref);
      $("<tr>")
        .append("<td class=\"idtc\">"+ref.pdt_ref_id+"</td>"+
                "<td class=\"sup-ref\">"+ref.sup_ref+"</td>"+
                "<td class=\"desc\">"+ref.desc+"</td>"+
                "<td class=\"price\">"+sprintf("%.02f",parseFloat(ref.pau_ht))+"€ HT</td>"+
                "<td class=\"price\">"+sprintf("%.02f",parseFloat(ref.pu_ht))+"€ HT</td>"+
                "<td class=\"qty\">"+
                  "<img class=\"add\" src=\"../ressources/quantite_plus.gif\" alt=\"Ajouter\" />"+
                  "<img class=\"sub\" src=\"../ressources/quantite_moins.gif\" alt=\"Retirer\" />"+
                  "<input type=\"text\" value=\"1\" />"+
                "</td>"+
                "<td class=\"tool\"><span class=\"icon basket-add\"></span></td>")
        .find("span.basket-add").data("line", line).end()
        .appendTo(pre_id_detail+"-references tbody");

    }
  };

  me.hidePdtDetail = function(){
    $(pre_id+"-pdt-detail").hide();
    $(pre_id+"-pdt-detail-references tbody").empty();
  };

  me.getPdtDetailLines = function(){
    var line_list = [];
    $(pre_id+"-pdt-detail-references tbody").find("span.basket-add").each(function(){
      line_list.push($(this).data("line"));
    })
    return line_list;
  };

  me.addAutocompleteLine = function(){
    var pdt_id = parseInt(arguments[0]) || "";
    var $tr = $("<tr>", {
      "class": "ac-line",
      "html": "<td colspan=\""+($(pre_id+"-items th").length-1)+"\">"+
                "<input type=\"text\" class=\"autocomplete-ref-search\" placeholder=\"idTC/Ref Fourn./id ou Nom Produit\" class=\"placeholder\" value=\""+(pdt_id || "")+"\"/>"+
              "</td>"+
              "<td class=\"tool\">"+
                "<span class=\"icon basket-delete\"></span>"+
              "</td>"
    }).appendTo(pre_id+"-items > tbody");
    if (pdt_id)
      $tr.find("input").blur().focus();

  };

  me.addProductCreationLine = function(){
    var line = new HN.TC.ItemCart.Line();

    var $tr = $("<tr>", {"class": "pc-line"})
      .append(
        "<td colspan=\""+($(pre_id+"-items th").length-1)+"\">"+
          "<div class=\"col-1\">"+
            "<label>Fournisseur : </label><input type=\"text\" data-pci=\"sup_name\"/><br/>"+
            "<label>Ref Fournisseur : </label><input type=\"text\" data-pci=\"sup_ref\"/><br/>"+
            "<label>Prix d'achat: </label><input type=\"text\" data-pci=\"pau_ht\"/><br/>"+
            "<label>Prix public : </label><input type=\"text\" data-pci=\"pu_ht\"/><br/>"+
            "<label>Fiche produit liée : </label><input type=\"text\" data-pci=\"pdt_id\"/><br/>"+
          "</div>"+
          "<div class=\"col-2\">"+
            "<label>Non VPC : </label><input type=\"checkbox\" data-pci=\"not_vpc\"/><br/>"+
            "<label>Libellé : </label><textarea data-pci=\"desc\"></textarea>"+
          "</div>"+
          "<div class=\"col-3\">"+
            "<button class=\"btn ui-state-default ui-corner-all vmaib\">Créer le produit</button><div class=\"vsma\"></div>"+
          "</div>"+
        "</td>"+
        "<td class=\"tool\">"+
          "<span class=\"icon basket-delete\"></span>"+
        "</td>")
      .appendTo(pre_id+"-items > tbody");

    // supplier autocomplete field
    var sup_acf = new HN.TC.AutoCompleteField({
      field: $tr.find("input[data-pci='sup_name']"),
      feedFunc: function(val, cb){
        var q = HN.TC.AjaxQuery.create()
          .select("id, nom1")
          .from("Advertisers")
          .where("actif = ?", 1)
          .andWhere("category = ?", HN.TC.__ADV_CAT_SUPPLIER__);
        if ($.isNumeric(val)) {
          var a_id_iv = HN.TC.AjaxQuery.getQueryNumIntervals("id", val, HN.TC.AjaxQuery.ADV_MAX_ID);
          q.andWhere(a_id_iv[0]+" OR nom1 like ?", $.merge(a_id_iv[1], [val+"%"]));
        }
        else {
          q.andWhere("nom1 like ?", val+"%");
        }
        q.limit(10).execute(function(data){
          var cb_data = [];
          for (var i=0; i<data.length; i++)
            cb_data.push([data[i].id, data[i].nom1]);
          cb(cb_data);
        });
      },
      onSelect: function(){
        line.sup_id = this.curRowData[0];
      },
      colToGet: 1
    });

    $tr.find(".col-3 button").on("click", function(){
      $tr.find("input, textarea").filter("[data-pci]").each(function(){
        line[$(this).data("pci")] = $(this).attr("type") == "checkbox" ? ($(this).prop("checked")?1:0) : $(this).val();
      });
      line.vpc = !line.not_vpc|0;
      delete line.not_vpc;
      me.addLine(line, 1);
      $tr.find("span.basket-delete").click();
    });

  };

  me.addLine = function(line, qty, noUpdateCalcs){
    line.quantity = parseInt(qty) || parseInt(line.quantity) || 1;

    var $input_qty = [];
    if (line.pdt_ref_id|0) {
      $input_qty = $(pre_id+"-items td.idtc [data-line-info='pdt_ref_id']:contains('"+line.pdt_ref_id+"')").closest("tr").find("td.qty").find("input");
      if ($input_qty.length)
        $input_qty.val(parseInt($input_qty.val())+line.quantity);
    }

    if (!$input_qty.length) {
      $(pre_id+"-items tbody").append(me.getLineDom(line));
      //$("<tr class=\"line\">").html(me.getLineHtml(line)).data("line-infos", line).appendTo(pre_id+"-items tbody");
      if (me.options.comment) {
        $("<tr class=\"line-2\">")
          .append("<td colspan=\"5\" class=\"comment editable\">Commentaire : <span class=\"editable\" data-line-info=\"comment\">"+line.comment+"</span></td><td colspan=\"5\" class=\"delivery-time editable\">Délai de livraison : <span class=\"editable\" data-line-info=\"delivery_time\">"+line.delivery_time+"</span></td>")
          .appendTo(pre_id+"-items tbody");
      }
      if (me.options.sup_comment) {
        $("<tr class=\"line-2\">")
          .append("<td colspan=\"10\" class=\"comment editable\">Comm. Four. : <span class=\"editable\" data-line-info=\"sup_comment\">"+line.sup_comment+"</span></td>")
          .appendTo(pre_id+"-items tbody");
      }
    }
    if (!noUpdateCalcs)
      me.updateCartCalcs();
  };

  me.getLineDom = function(line, image){

    var $dom = $(),
        image = {
          link: line.pdt_id|0 && line.pdt_ref_name != "" && line.pdt_cat_id|0 ? HN.TC.get_pdt_fo_url(line.pdt_id, line.pdt_ref_name, line.pdt_cat_id) : null,
          url: line.pdt_id|0 ? HN.TC.get_pdt_pic_url(line.pdt_id,"thumb_small",1) : HN.TC.get_dft_pdt_pic_url("thumb_small")
        },
        rowspan = 2 + me.options.comment + me.options.sup_comment;

    var line_pau_ht = sprintf("%.02f",parseFloat(line.pau_ht)||0),
        line_pu_ht = sprintf("%.02f",parseFloat(line.pu_ht)||0),
        line_total_ht = sprintf("%.02f",parseFloat(line.total_ht)||0),
        line_et_ht = sprintf("%.02f",parseFloat(line.et_ht)||0),
        line_et_total_ht = sprintf("%.02f",parseFloat(line.et_total_ht)||0);

    $dom = $dom.add(
      "<tr class=\"line\">"+
        "<td rowspan=\""+rowspan+"\" class=\"image\">"+(image.link?"<a href=\""+image.link+"\" target=\"_blank\">":"")+"<img src=\""+image.url+"\" alt=\"\"/>"+(image.link?"</a>":"")+"</td>"+
        "<td rowspan=\"2\" class=\"sup-ref\"><span>"+line.sup_ref+"</span></td>"+
        "<td rowspan=\"2\" class=\"idtc\"><span>"+
          (line.pdt_id|0 ? "<a href=\""+HN.TC.get_pdt_bo_url(line.pdt_id)+"\"" : "<span") + " data-line-info=\"pdt_ref_id\">"+
            (line.pdt_ref_id || "<i>non encore défini</i>")+
          (line.pdt_id|0 ? "</a>" : "</span>")+
        "</span></td>"+
        "<td rowspan=\"2\" class=\"sup-name\"><span><a href=\""+HN.TC.get_adv_bo_url(line.sup_id)+"\" data-line-info=\"sup_id\" data-value=\""+line.sup_id+"\"><span data-line-info=\"sup_name\">"+line.sup_name+"</span></a></span></td>"+
        "<td class=\"desc editable\"><span class=\"editable\" data-line-info=\"desc\">"+line.desc+"</span></td>"+
        "<td class=\"price editable\"><span class=\"editable\" data-line-info=\"pau_ht\">"+line_pau_ht+"</span></td>"+
        "<td class=\"price editable\"><span class=\"editable\" data-line-info=\"pu_ht\">"+line_pu_ht+"</span></td>"+
        "<td class=\"qty\">"+
          "<img class=\"add\" src=\"../ressources/quantite_plus.gif\" alt=\"Ajouter\" />"+
          "<img class=\"sub\" src=\"../ressources/quantite_moins.gif\" alt=\"Retirer\" />"+
          "<input data-line-info=\"quantity\" type=\"text\" value=\""+line.quantity+"\" />"+
        "</td>"+
        "<td class=\"disc editable\"><span class=\"editable\" data-line-info=\"discount\">"+line.discount+"%</span></td>"+
        "<td class=\"price\"><span data-line-info=\"total_ht\">"+line_total_ht+"</span></td>"+
        "<td rowspan=\"2\" class=\"vat editable\"><span class=\"editable\" data-line-info=\"tva_code\">"+line.tva_code+"</span></td>"+
        "<td rowspan=\""+rowspan+"\" class=\"tool\"><span class=\"icon basket-delete\"></span></td>"+
      "</tr>"+
      "<tr class=\"sub-line\">"+
        "<td colspan=\"2\">Eco participation</td>"+
        "<td class=\"price editable\"><span class=\"editable\" data-line-info=\"et_ht\">"+line_et_ht+"</span></td>"+
        "<td colspan=\"2\" class=\"price editable\"></td>"+
        "<td class=\"price\"><span data-line-info=\"et_total_ht\">"+line_et_total_ht+"</span></td>"+
      "</tr>"
    );

    $dom.data("line-infos", line);
    return $dom;
  };

  me.refreshLines = function(){
    $(pre_id+"-items tbody").find("tr.line").each(function(){
      $(this).nextUntil("tr.line", "tr.sub-line").remove();
      $(this).after(me.getLineDom($(this).data("line-infos"))).remove();
    });
  };

  me.isMainLine = function($tr){
    var mlc = ["line","ac-line","pc-line"]; // main line classes
    return !!$tr.length && $tr.attr("class").split(/\s+/g).some(function(c){ return mlc.indexOf(c) !== -1; });
  };

  me.deleteLine = function(tr){
    var $tr = $(tr),
        $trl = $(); // tr to remove

    // get "master" line
    while ($tr.length && !me.isMainLine($tr))
      $tr = $tr.prev();

    // get children lines
    do {
      $trl = $trl.add($tr);
      $tr = $tr.next();
    }
    while ($tr.length && !me.isMainLine($tr));

    $trl.remove();

    me.updateCartCalcs();
  };

  me.getDftFdp = function(){
    return me.data.stotal_ht < HN.TC.fdp_franco ? HN.TC.fdp : 0;
  };

  me.updateCartCalcs = function(noOnUpdate){
    var d = me.data,
            tvaTable = [];
    d.stotal_ht = d.total_tva = d.total_ht = d.total_ttc = 0;
    d.fdp_ht = d.fdp_tva = d.fdp_ttc = 0;

    $(pre_id+"-items tbody > tr.line").each(function(){
      var $this = $(this),
          line = $this.data("line-infos");

      $this = $this.add($this.nextUntil("tr.line", "tr.sub-line"));

      line.pau_ht = parseFloat($this.find("[data-line-info='pau_ht']").text()) || 0;
      line.pu_ht = parseFloat($this.find("[data-line-info='pu_ht']").text()) || 0;
      line.quantity = parseInt($this.find("[data-line-info='quantity']").val()) || 1;
      line.discount = parseFloat($this.find("[data-line-info='discount']").text()) || 0;
      line.total_ht = Number((line.pu_ht * line.quantity * (100-line.discount)/100).toFixed(6));
      line.tva_code = parseInt($this.find("[data-line-info='tva_code']").text());
      line.tva_rate = me.options.tva ? HN.TC.get_tva_rate(line.tva_code) : 0;
      line.pu_tva = line.pu_ht * line.tva_rate/100;
      line.total_tva = Number((line.pu_tva * line.quantity * (100-line.discount)/100).toFixed(6));
      line.total_ttc = Number((line.total_ht + line.total_tva).toFixed(6));
      line.et_ht = parseFloat($this.find("[data-line-info='et_ht']").text()) || 0;
      line.et_total_ht = Number((line.et_ht * line.quantity).toFixed(6));
      line.et_tva = line.et_ht * line.tva_rate/100;
      line.et_total_tva = Number((line.et_tva * line.quantity).toFixed(6));
      d.stotal_ht += line.total_ht + line.et_total_ht;
      d.total_tva += line.total_tva + line.et_total_tva;

			if (me.options.tva) {
        if (!tvaTable[line.tva_code])
          tvaTable[line.tva_code] = { base: 0, rate: line.tva_rate, total: 0 };
        tvaTable[line.tva_code].base += line.total_ht + line.et_total_ht;
        tvaTable[line.tva_code].total += line.total_tva + line.et_total_tva;
      }

      $this.find("[data-line-info='total_ht']").text(sprintf("%.02f", line.total_ht));
      $this.find("[data-line-info='et_total_ht']").text(sprintf("%.02f", line.et_total_ht));
    });

    if (me.options.fdp) {
      if ($(pre_id+"-fdp-ht").data("old_val") !== undefined)
        d.fdp_ht = parseFloat($(pre_id+"-fdp-ht").text());
      else
        d.fdp_ht = me.getDftFdp();

      d.fdp_tva = me.options.tva ? Number(((d.fdp_ht * HN.TC.get_tva_rate(1))/100).toFixed(6)) : 0;
      d.fdp_ttc = d.fdp_ht + d.fdp_tva;
			if (me.options.tva) {
        if (!tvaTable[1])
          tvaTable[1] = { base: 0, rate: HN.TC.get_tva_rate(1), total: 0 };
        tvaTable[1].base += d.fdp_ht;
        tvaTable[1].total += d.fdp_tva;
      }
    }

    d.total_tva = Math.round((d.total_tva + d.fdp_tva)*100)/100;
		d.total_ht = Math.round((d.stotal_ht + d.fdp_ht)*100)/100;
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

    if (!noOnUpdate && me.onUpdate)
      me.onUpdate();
  };

  me.saveCart = function(){

    me.data.lines = [];
    $(pre_id+"-items tbody").find("tr.line").each(function(){
      //var line = $.extend(true, {}, $(this).data("line-infos"));

      /*if (line.pdt_ref_id|0) {
        line.pdt_ref = { // update the long label
          label_long: line.desc
        };
      }
      else {
        line.pdt_ref = new HN.TC.ItemCart.Line.pdt_ref();
        line.pdt_ref.setData({
          idProduct: line.pdt_id,
          sup_id: line.sup_id,
          label_long: line.desc,
          refSupplier: line.sup_ref,
          price: line.pu_ht,
          price2: line.pau_ht,
          vpc: !line.not_vpc|0
        });
      }*/

      me.data.lines.push($(this).data("line-infos"));
    });

    for (var k in me.data)
      if (me.data[k] == null)
        me.data[k] = "";

  };
};
HN.TC.ItemCart.prototype = {
  sortRefs: function(r1,r2){
    return r1.classement - r2.classement;
  },
  getPdtListLastQuery: null,
  getPdtListTO: null,
  getPdtListDelayed: function(query, delay, filter, exactNums){
    var me = this,
        dfd = new $.Deferred();
    clearTimeout(me.getPdtListTO);
    me.getPdtListTO = setTimeout(function(){
      if (query != me.getPdtListLastQuery) {
        me.getPdtListLastQuery = query;
        me.getPdtList(query, filter, exactNums).done(function(){
          dfd.resolve(true);
        });
      } else {
        dfd.resolve(false);
      }
    }, delay);
    return dfd.promise();
  },
  getPdtList: function(q, filter, exactNums){
    var me = this,
        dfd = new $.Deferred(),
        filters = {},
        is_num = $.isNumeric(q),
        mq = HN.TC.AjaxMultiQueries.create(),
        bq = HN.TC.AjaxQuery.create() // common query
          .select("p.id,"+
                  "pfr.name AS name,"+
                  "pfr.ref_name AS ref_name,"+
                  "pfr.fastdesc AS fastdesc,"+
                  "IF(pfr.delai_livraison='', a.delai_livraison, pfr.delai_livraison) AS delivery_time,"+
                  "f.id AS cat_id,"+
                  "ffr.name AS cat_name,"+
                  "a.id AS adv_id,"+
                  "a.nom1 AS sup_name,"+
                  "r.id,"+
                  "r.idProduct AS pdt_id,"+
                  "r.id AS pdt_ref_id,"+
                  "r.sup_id,"+
                  "r.refSupplier AS sup_ref,"+
                  "r.label,"+
                  "r.label_long,"+
                  "r.price2 AS pau_ht,"+
                  "r.price AS pu_ht,"+
                  "r.ecotax AS et_ht,"+
                  "r.idTVA AS tva_code,"+
                  "r.content,"+
                  "r.classement,"+
                  "rh.content")
          .from("Products p")
          .innerJoin("p.product_fr pfr")
          .innerJoin("p.families f")
          .innerJoin("f.family_fr ffr")
          .innerJoin("p.advertiser a")
          .innerJoin("p.references r")
          .leftJoin("r.headers rh")
          .where("a.category = ? AND a.actif = ?", [HN.TC.__ADV_CAT_SUPPLIER__, 1])
          .andWhere("r.deleted = ?", 0);

    // get filter list
    if (typeof filter === "string")
      filter = [filter];
    else if (!$.isArray(filter))
      filter = [];

    if (filter.length === 0)
      filter = ["pdt_id", "ref_id", "ref_ref_supplier", "pdt_name"];

    for (var i=0; i<filter.length; i++)
      filters[filter[i]] = true;

    if (is_num) {
      if (filters.pdt_id) {
        if (exactNums) {
          mq.addQuery(bq.clone().andWhere("p.id = ?", q));
        } else {
          var p_id_iv = HN.TC.AjaxQuery.getQueryNumIntervals("p.id", q, true);
          mq.addQuery(bq.clone().andWhere(p_id_iv[0], p_id_iv[1]));  // search in product id's
        }
      }
      if (filters.ref_id) {
        if (exactNums) {
          mq.addQuery(bq.clone().andWhere("r.id = ?", q));
        } else {
          var r_id_iv = HN.TC.AjaxQuery.getQueryNumIntervals("r.id", q, true);
          mq.addQuery(bq.clone().andWhere(r_id_iv[0], r_id_iv[1])); // and in ref id's (idTC)
        }
      }
    }

    if (filters.ref_ref_supplier) {
      mq.addQuery(bq.clone().andWhere("MATCH (r.refSupplier) AGAINST (? IN BOOLEAN MODE)", q+"*")); // search in supplier refs grouped by product
      mq.addQuery(
        HN.TC.AjaxQuery.create() // search in supplier refs of standalone references
          .select("r.id,"+
                  "r.id AS pdt_ref_id,"+
                  "r.sup_id,"+
                  "r.refSupplier AS sup_ref,"+
                  "IF(r.label_long<>'', r.label_long, r.label) AS desc,"+
                  "r.price2 AS pau_ht,"+
                  "r.price AS pu_ht,"+
                  "r.ecotax AS et_ht,"+
                  "r.idTVA AS tva_code,"+
                  "r.classement,"+
                  "s.nom1 AS sup_name")
          .from("ReferencesContent r")
          .innerJoin("r.supplier s")
          .where("s.category = ? AND s.actif = ?", [HN.TC.__ADV_CAT_SUPPLIER__, 1])
          .andWhere("r.deleted = ? AND r.idProduct = ?", [0,0])
          .andWhere("MATCH (r.refSupplier) AGAINST (? IN BOOLEAN MODE)", q+"*")
      );
    }

    if (filters.pdt_name) {
      mq.addQuery(bq.clone().andWhere("MATCH (pfr.name) AGAINST (? IN BOOLEAN MODE)", q+"*")); // search in product's name
    }

    mq.setLinkedLimit(10).execute(function(data){
      for (var di=0; di<data.length; di++) {
        for (var pi=0; pi<data[di].length; pi++) { // each product
          if (data[di][pi].references) {
            var pdt = data[di][pi];
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
          } else {
            delete data[di][pi].id;
          }
        }
      }
      me.pdtList = [];
      for (var i=0; i<data.length; i++)
        me.pdtList = me.pdtList.concat(data[i]);
      dfd.resolve(me.pdtList);
    });
    return dfd.promise();
  }
};
HN.TC.ItemCart.Line = function(data){
  this.setData(data);
};
HN.TC.ItemCart.Line.prototype = {
  // default fields
  id: 0,
  pdt_id: 0,
  pdt_ref_id: 0,
  sup_id: 0,
  sup_ref: "",
  desc: "",
  pau_ht: 0,
  pu_ht: 0,
  et_ht: 0,
  quantity: 0,
  promotion: 0,
  discount: 0,
  total_ht: 0,
  total_ttc: 0,
  et_total_ht: 0,
  tva_code: 1,
  delivery_time: "",
  comment: "",
  sup_comment: "",

  // calculated fields
  total_a_ht: 0,
  total_tva: 0,
  total_ht_pre: 0,
  sup_name: "",
  pdt_ref_name: "",
  pdt_cat_id: 0,

  // relations
  //pdt_ref: null, // must be a HN.TC.ItemCart.Line.pdt_ref object

  setData: function(data){
    if (typeof data == "object")
      for (var k in data)
        if (this[k] !== undefined)
          this[k] = data[k];
  }
};
/*HN.TC.ItemCart.Line.pdt_ref = function(data){
  this.setData(data);
};
HN.TC.ItemCart.Line.pdt_ref.prototype = {
  id: 0,
  idProduct: 0,
  sup_id: 0,
  label: "",
  label_long: "",
  // content: "", will be auto filled in php
  refSupplier: "",
  price: 0,
  price2: 0,
  // unite: 0, same here
  // marge: 0, and here
  idTVA: 1,
  // classement: 0, and here
  vpc: 1,
  // deleted: 0 and here
  setData: function(data){
    if (typeof data == "object")
      for (var k in data)
        if (this[k] !== undefined)
          this[k] = data[k];
  }
};*/

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
      url: HN.TC.ADMIN_URL+"files-upload/AJAX_files_upload.php",
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
      url: HN.TC.ADMIN_URL+"files-upload/AJAX_get_uploaded_files_list.php",
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
        //alert(HN.TC.getAjaxErrorText(jqXHR, textStatus, errorThrown));
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
      url: HN.TC.ADMIN_URL+"files-upload/AJAX_delete_uploaded_file.php",
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

HN.TC.CustomerSecondaryContacts = function(_data){
  me = this;
  me.data = $.extend({
      client_id: ""
    }, _data);
  me.ado = HN.TC.AjaxDoctrineObject.create()
    .setObject("ClientsContacts");
    //.setData(me.data);
  //console.log(me.data.client_id, me.ado);

  me.onEditFuncs = {
    "nom": {
      edit: function(){ },
      validate: function(){
       // console.log($(this).data("old_val"), $(this).val());
        if ($(this).data("old_val") != $(this).val()) {
          var contactNum = $(this).closest('table').data('client-contact-num');
          //me.data.num = contactNum;
          me.updateClientsContacts(contactNum);
        }
      }
    },
    "prenom": {
      edit: function(){ },
      validate: function(){
       // console.log($(this).data("old_val"), $(this).val());
        if ($(this).data("old_val") != $(this).val()) {
          var contactNum = $(this).closest('table').data('client-contact-num');
          //me.data.num = contactNum;
          me.updateClientsContacts(contactNum);
        }
      }
    },
    "email": {
      edit: function(){ },
      validate: function(){
       // console.log($(this).data("old_val"), $(this).val());
        if ($(this).data("old_val") != $(this).val()) {
          var contactNum = $(this).closest('table').data('client-contact-num');
          //me.data.num = contactNum;
          me.updateClientsContacts(contactNum);
        }
      }
    },
    "tel1": {
      edit: function(){ },
      validate: function(){
       // console.log($(this).data("old_val"), $(this).val());
        if ($(this).data("old_val") != $(this).val()) {
          var contactNum = $(this).closest('table').data('client-contact-num');
          //me.data.num = contactNum;
          me.updateClientsContacts(contactNum);
        }
      }
    },
    "tel2": {
      edit: function(){ },
      validate: function(){
       // console.log($(this).data("old_val"), $(this).val());
        if ($(this).data("old_val") != $(this).val()) {
          var contactNum = $(this).closest('table').data('client-contact-num');
          //me.data.num = contactNum;
          me.updateClientsContacts(contactNum);
        }
      }
    },
    "fax1": {
      edit: function(){ },
      validate: function(){
       // console.log($(this).data("old_val"), $(this).val());
        if ($(this).data("old_val") != $(this).val()) {
          var contactNum = $(this).closest('table').data('client-contact-num');
          //me.data.num = contactNum;
          me.updateClientsContacts(contactNum);
        }
      }
    },
    "fax2": {
      edit: function(){ },
      validate: function(){
       // console.log($(this).data("old_val"), $(this).val());
        if ($(this).data("old_val") != $(this).val()) {
          var contactNum = $(this).closest('table').data('client-contact-num');
          //me.data.num = contactNum;
          me.updateClientsContacts(contactNum);
        }
      }
    },
    "fonction": {
      edit: function(){ },
      validate: function(){
       // console.log($(this).data("old_val"), $(this).val());
        if ($(this).data("old_val") != $(this).val()) {
          var contactNum = $(this).closest('table').data('client-contact-num');
          //me.data.num = contactNum;
          me.updateClientsContacts(contactNum);
        }
      }
    }
  }

  me.updateClientsContacts = function(contactNum){
   // me.parent.save.call(this);
    var cb = $.isFunction(arguments[0]) ? arguments[0] : false;
    me.data.num = contactNum;
    var params = new Array();
    params = ["num = ? and client_id = ?", [contactNum , me.data.client_id]];

    me.ado.setData([me.data]).setMethod("updateClientsContacts").setLoadQueryParams(params).execute(function(data){
      if (cb) cb();
    });

  }

  me.deleteContact = function(client_id, contactNum){
    var cb = $.isFunction(arguments[0]) ? arguments[0] : false;

    var thisData = {};
    thisData.client_id = client_id;
    thisData.num = contactNum;
    me.data.num = contactNum;
    var params = new Array();
    params = ["num = ? and client_id = ?", [contactNum , thisData.client_id]];
 //setData([me.data])
    me.ado.setData([thisData]).setMethod("deleteClientsContacts").setLoadQueryParams(params).execute();
  }

  me.createClientContact = function(data){

    me.ado.setData(me.data).setLoadQueryParams(['client_id = ?', data.client_id]).setMethod("create").create(data);
    /*console.log(me.ado,  this.ado.response);
    if(me.ado.response.errorMsg != '')
      alert('Champ E-mail vide ou incorrect');*/
  }

  me.getClientContactsList = function(client_id){
    var clientId = typeof(me.data.client_id) != 'undefined' && me.data.client_id != '' ? me.data.client_id : client_id;
    HN.TC.CustomerSecondaryContacts.getAll(clientId, function(requestedData){
      for(var a=0; a<requestedData.length; a++)
        console.log(requestedData[a]['email']);
    });
  }
};

HN.TC.CustomerSecondaryContacts.prototype = {
  setClientId: function(val){ this.data.client_id = val; return this; },
  create: function(cb){
    this.data.client_id = this.data.client_id.toString();
    if (this.data.client_id.length){
      this.ado.setLoadQueryParams('');
      this.ado.create(cb);
    }

  }
};

HN.TC.CustomerSecondaryContacts.getAll = function(client_id, cb){
  data = '';
  HN.TC.AjaxQuery.create()
    .select("cc.*")
    .from("ClientsContacts cc")
    .where("cc.client_id = ?",client_id)
    .execute(function(data){
      for (var i=0; i<data.length; i++)
        data[i].timestamp = HN.TC.get_formated_datetime(data[i].timestamp);
      cb(data);
    });
};

$(document).on('click', '.iconAddSecondaryContact', function(){
/*  client_id = $(this).data('idClient');
  var listSecondaryContactsMails = HN.TC.CustomerSecondaryContacts.getAll(client_id, function(requestedData){
    var arrayValues = new Array();
    for(var a=0; a<requestedData.length; a++)
        arrayValues[a] = requestedData[a]['email'];
      console.log(arrayValues);
  });*/
  $('#secondary_contacts_mails_dialog').dialog({
    width: 500,
    autoOpen: false,
    modal: true,
    buttons: {
      "OK": function(){
        var $listMails = new Array();
          $(this).find('input[type=checkbox]:checked').each(function(index){
            $listMails[index] = $(this).attr('value');
        });
        if($listMails.length>0)
          $('input[name=secondaryContacts]').val($listMails.join(', '));
        $(this).dialog("close");
      }
    }
  });
  $('#secondary_contacts_mails_dialog').dialog('open');
})

// global _blank live function
// target="_blank"
$(document)
  .on("click", "a._blank, area._blank", function(){ open($(this).attr("href"), "_blank"); return false; })
  /*.on("focus", "input[placeholder]", function(){
    var $this = $(this);
    if ($this.val() == $this.attr("placeholder")) {
      $this.val("");
      $this.removeClass("placeholder");
    }
  })
  .on("blur", "input[placeholder]", function(){
    var $this = $(this);
    if ($this.val() == "") {
      $this.val($this.attr("placeholder"));
      $this.addClass("placeholder");
    }
  });*/

$(function(){
  window.$commonOkDb = $("<div>").dialog({
    width: 500,
    autoOpen: false,
    modal: true,
    buttons: {
      "OK": function(){
        $(this).trigger("ok");
        $(this).dialog("close");
      }
    }
  });
  window.$commonConfirmDb = $("<div>").dialog({
    width: 500,
    autoOpen: false,
    modal: true,
    buttons: {
      "OK": function(){
        $(this).trigger("confirm", [true]);
        $(this).dialog("close");
      },
      "Annuler": function(){
        $(this).trigger("confirm", [false]);
        $(this).dialog("close");
      }
    }
  });

});
