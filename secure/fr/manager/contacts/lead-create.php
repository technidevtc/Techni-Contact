
<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
require(ADMIN."head.php");
echo '<link rel="stylesheet" href="../css/style_autoc.css" />
<script type="text/javascript" src="../js/script.js"></script>';
$idCall = false;
// call type, lead spool or campaign
if (isset($_GET["idCall"]) && isset($_GET["idCallCampaign"])) {
  $callType = false;
}
elseif (isset($_GET["idCall"]) && preg_match("/^[1-9]{1}[0-9]{0,8}$/", $_GET["idCall"])) {
  $callType = 1; // lead spool
  $ajaxProcessCallUrl = 'AJAX_process-call';
  $jsDataArgs = '"id_call="+idCall+"&id_lead="+idLead+';
  $ajaxRedirectTo = 'calls_list.php';
  $idCall = $_GET["idCall"];
}
elseif (isset($_GET["idCallCampaign"]) && preg_match("/^[1-9]{1}[0-9]{0,8}$/", $_GET["idCallCampaign"])) {
  $callType = 2; // campaign
  $ajaxProcessCallUrl = 'AJAX_process-call-campaign';
  $jsDataArgs = '"id_call="+idCall+"&id_campaign="+idLead+';
  $ajaxRedirectTo = 'campaign_spool.php?idCampaign=';
  $idCall = $_GET["idCallCampaign"];
}
else {
  $callType = false;
}

$pdtId = isset($_GET["pdtId"]) && is_numeric($_GET["pdtId"]) ? $_GET["pdtId"] : "";
$idClient = isset($_GET["idClient"]) && preg_match('`^[[:alnum:]]([-_.]?[[:alnum:]])*@[[:alnum:]]([-_.]?[[:alnum:]])*\.([a-z]{2,4})$`', $_GET["idClient"]) ? $_GET["idClient"] : "";
$idLead = isset($_GET["idLead"]) && preg_match("/^[1-9]{1}[0-9]{0,8}$/", $_GET["idLead"]) ? $_GET["idLead"] : "";
$idCampaign = isset($_GET["idCampaign"]) && preg_match("/^[1-9]{1}[0-9]{0,8}$/", $_GET["idCampaign"]) ? $_GET["idCampaign"] : "";



$callExists = false;
if ($userPerms->has($fntByName["m-smpo--sm-call-list"], "re") && $callType === 1) {
  $call = new Calls($idCall);
  if (($call->timestamp_in_line || strcasecmp($call->call_result, 'not_called') == 0 || strcasecmp($call->call_result, 'absence') == 0 || strcasecmp($call->call_result, 'customer_calls_back') == 0) && $call->id_client == $idClient && $call->id_lead == $idLead)
    $callExists = true;
}

if ($userPerms->has($fntByName["m-smpo--sm-campaign"], "re") && $callType === 2) {
  $call = new CallsCampaign($idCall);
  if (($call->timestamp_in_line || strcasecmp($call->call_result, 'not_called') == 0 || strcasecmp($call->call_result, 'absence') == 0 || strcasecmp($call->call_result, 'customer_calls_back') == 0) && $call->id_client == $idClient && $call->id_campaign == $idCampaign)
    $callExists = true;
}

// Préparation liste des fonctions
$n = $pc = 0;
$pl = array(); // Post List
if ($fh = fopen(MISC_INC."list_post.csv","r")) {
  while (($data = fgetcsv($fh,128,";")) !== false)
    $pl[$n++] = $data;
  $pc = $n - 1; // Post Count -> La 1ère ligne est l'intitulé des colonnes
  fclose($fh);
}

// Préparation liste des tailles salariales
$n = $nec = 0;
$nel = array(); // Number of Employee List
if ($fh = fopen(MISC_INC."list_number-of-employees.csv","r")) {
  while (($data = fgetcsv($fh,64,";")) !== false)
    $nel[$n++] = $data[0];
  $nec = $n - 1; // Number of Employee Count -> La 1ère ligne est l'intitulé des colonnes
  fclose($fh);
}

// Préparation liste des secteurs d'activité
$q = Doctrine_Core::getTable('ActivitySector')
    ->createQuery('as')
    ->select('as.id, as.sector, ass.qualification')
    ->leftJoin('as.Surqualifications ass');

$activity_sectors = $activity_sectorsList = $q->fetchArray();

mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $activity_sectors);
// correction des erreurs de parse json
foreach($activity_sectors as &$sectorList){
  if(is_array($sectorList) && !empty($sectorList))
  foreach($sectorList as &$surqualificationList){
    if(is_array($surqualificationList) && !empty($surqualificationList))
    foreach($surqualificationList as $index => &$surqualification)
    {
      $surqualification['qualification'] = preg_replace('/\r\n|\n\r|\n|\r/', '', $surqualification['qualification']) ;
      $surqualification['qualification'] = htmlspecialchars($surqualification['qualification'], ENT_QUOTES) ;
    }
  }
}
$jsonedActivitySectorList = json_encode($activity_sectors);


// Préparation liste des pays en majuscule
$n = $cc = 0;
$cl = array(); // Country List
if ($fh = fopen(MISC_INC."list_country.csv","r")) {
  while (($data = fgetcsv($fh,128,";")) !== false)
    $cl[$n++] = mb_strtoupper($data[0]);
  $cc = $n - 1; // Country Count -> La 1ère ligne est l'intitulé des colonnes
  fclose($fh);
}

$country_selected = "FRANCE";

$title = $navBar = "Générer un lead";

if (!$userPerms->has($fntByName["m-smpo--sm-lead-create"], "r")) { ?>
  <div class="bg">
    <div class="fatalerror">Vous n'avez pas les droits adéquats pour réaliser cette opération.</div>
  </div>
<?php } else { ?>
<?php /*<script type="text/javascript" charset="UTF-8" src="https://<?php echo AVAIL_JAVASCRIPT_API_URL ?>"></script>
<script type="text/javascript">
  var emark = new Emark();
</script> */ ?>
<script type="text/javascript">
var RevDate = new Date();
//document.write( unescape( "%3Cscript src='" + (("https:" == document.location.protocol) ? "https://api2.reversoform.com/includes/js/reversoObj.js" : "http://api.reversoform.com/includes/js/reversoObj.js") + "?t="+RevDate.getTime()+"' type='text/javascript'%3E%3C/script%3E" ) );
</script>
<script type="text/javascript">
var reversoLoaded = window.ClassReverso !== undefined;
// Reverso
if (reversoLoaded) {
  var ObjReverso = new ClassReverso();
  ObjReverso.serial		= '8389615817859';
  ObjReverso.phone		= 'telephone';
  ObjReverso.company		= 'societe';
  ObjReverso.firstname	= 'prenom';
  ObjReverso.lastname		= 'nom';
  ObjReverso.address		= 'adresse';
  ObjReverso.zip			= 'cp';
  ObjReverso.city			= 'ville';
  ObjReverso.country		= 'pays';
  ObjReverso.naf			= 'code_naf';
  ObjReverso.siret		= 'num_siret';
}
  if (!HN.TC.BO) HN.TC.BO = {};
  if (!HN.TC.BO.Leads) HN.TC.BO.Leads = {};
  HN.TC.BO.Leads.ShowRecommendedProducts = function(pdtId, userId, template, domId){
    pdtId = parseInt(pdtId, 10) || null;
    userId = parseInt(userId, 10) || null;
    var nuukikData = {
      zoneId: template == "telephone-sortant" || template == "campagne-d-appels" ? 120 : 119
    };
    if (pdtId !== null) {
      nuukikData.controller = "products";
      nuukikData.path = [pdtId, "recommendation"];
      if (userId !== null)
        nuukikData.params = { userId: userId };
    } else if (userId !== null) {
      nuukikData.controller = "users";
      nuukikData.path = [userId, "recommendation"];
    } else {
      return false;
    }
    $.ajax({
      type: "POST",
      url: "AJAX_interface.php",
      data: {
        actions: [{
          action: "get_recommended_products",
          data: nuukikData,
        }]
      },
      dataType: "json",
      error: function (XMLHttpRequest, textStatus, errorThrown) {},
      success: function (data, textStatus) {
        //data = {data:{pdtList: data}};
        if (data.error) {
          $("#"+domId+"_error").text(data.error);
          $("#"+domId).hide();
        }
        else {
          $("#"+domId+"_error").text("");
          $("#"+domId).show();
          var html = "";
          for (var i in data.data.pdtList) {

            var pdt = data.data.pdtList[i];

            if($("#cat2_children_list_"+pdt.infos.cat2_id).length == 0)
              $("#cat2_children_list").after('<div id="cat2_children_list_'+pdt.infos.cat2_id+'" title="Liste des familles appartenant à "></div>')
            // dialog declaration
            $("#cat2_children_list_"+pdt.infos.cat2_id).dialog({
              width: 550,
              autoOpen: false,
              modal: true,
              position: [350,200]
            });

            $('.close_cat2_children_list'+pdt.infos.cat2_id).live(
              'click', function(){
              $("#cat2_children_list"+pdt.infos.cat2_id).dialog('close');
            });
            
            html += "<div class=\"pdt-preview fl"+(pdt.infos.adv_cat==<?php echo __ADV_CAT_BLOCKED__ ?>?" red":"")+"\">"+
                      "<div class=\"picture\"><img class=\"vmaib\" src=\""+pdt.pics[0].thumb_small+"\"/><div class=\"vsma\"></div></div>"+
                      "<div class=\"infos\">"+
                        "<div class=\"vmaib\">"+
                          "<a class=\"_blank\" href=\""+pdt.urls.fo_url+"\" title=\"Voir la fiche en ligne\"><img src=\"../ressources/icons/monitor_go.png\" alt=\"\" class=\"view-fo\"/></a>"+
                          "<a class=\"_blank\" href=\""+pdt.urls.bo_url+"\" title=\"Editer la fiche produit\"><strong>"+pdt.infos.name+"</strong></a><br/>"+
                          "<span>"+pdt.infos.fastdesc+"</span><br/>"+
                          "Code fiche produit: <strong>"+pdt.infos.id+"</strong><br/>"+
                          "Famille 3 : <a class=\"_blank\" href=\""+pdt.urls.cat3_bo_search_url+"\"><strong>"+pdt.infos.cat3_name+"</strong></a><br/>"+
                          "Famille 2 : <a  class=\"pdt_cat2_bo_search_url2\" href=\"\" name=\"cat2Id-"+pdt.infos.cat2_id+"\"><strong>"+pdt.infos.cat2_name+"</strong></a><br/>"+
                          pdt.infos.adv_cat_name+" : <a class=\"_blank\" href=\""+pdt.urls.adv_bo_url+"\"><strong>"+pdt.infos.adv_name+"</strong></a><br/>"+
                          "<a href=\"#pdt_sheet\">Voir description produit</a><br/>"+
                          "<a href=\"#use_for_lead\">Utiliser ce produit pour générer un lead</a>"+
                        "</div><div class=\"vsma\"></div>"+
                      "</div>"+
                      "<div class=\"zero\"></div>"+
                    "</div>";
            
            // cat3 list
            if (pdt["cat2_children"][pdt.infos.cat2_id].length > 0) {
              $('a[name=cat2Id-'+pdt.infos.cat2_id+']').live(
                'click', function (){
                var nameLinkCat2 = $(this).attr('name');
                var categorieName = $(this).text();
                nameLinkCat2 = nameLinkCat2.replace('cat2Id-', '');
                $('#cat2_children_list_'+pdt.infos.cat2_id).dialog('open');
                $('#cat2_children_list '+pdt.infos.cat2_id).html('');
                $('#ui-dialog-title-cat2_children_list_'+pdt.infos.cat2_id).text('Liste des familles appartenant à ')
                var html = '';
                html += '<ul>';
                $.each(pdt["cat2_children"][nameLinkCat2], function(){
                  html += '<li><a href="'+HN.TC.ADMIN_URL+'search.php?search_type=2&amp;search='+this.id+'" class="pdt_cat3_bo_search_url close_cat2_children_list" target="_blank"><strong class="pdt_cat3_name">'+this.name+'</strong></a></li>';
                })
                html += '</ul>';

                $('#ui-dialog-title-cat2_children_list_'+pdt.infos.cat2_id).append(categorieName);

                $('#cat2_children_list_'+pdt.infos.cat2_id).html(html);
                return false;
              });
            }
            else
              $("#cat2_children_list_"+pdt.infos.cat2_id).dialog('close');

          } // end for
          html += "<div class=\"zero\"></div>";
          $("#"+domId+"_container").empty().html(html).find("a[href^='#use_for_lead']").each(function(i){
            $(this).click(function(){
              $("#lc_pdt_search input[name='pdt_id']").val(data.data.pdtList[i].infos.id);
              $("#lc_pdt_search_go").click();
              $("input[name='campaignID']").val("999991");
              return false;
            });
          }).end().find("a[href^='#pdt_sheet']").each(function(i){
            $(this).click(function(){
              HN.TC.BO.Leads.ShowProductDetails(data.data.pdtList[i]);
              return false;
            });
          }).end().find("a._blank").click(function(){ window.open(this.href, "_blank"); return false });
        } // end else data.error
      }
    });
  };
  
  HN.TC.BO.Leads.ShowProductDetails = function(pdt){

    for (var fieldName in pdt.infos)
      if (!$.isArray(pdt.infos[fieldName]))
        $("#lc_pdt_sheet").find(".pdt_"+fieldName).html(pdt.infos[fieldName]);
    
    // price specific processing
    $("#lc_pdt_sheet .pdt_price").html(
      pdt.infos["saleable"] ?
        (pdt.infos["ref_count"] > 1 ?
          "à partir de<br/><strong>"+pdt.infos["price"]+"\u20ac HT</strong>" :
          "<strong>"+pdt.infos["price"]+"\u20ac HT</strong>") :
        (pdt.infos["hasPrice"] ?
          "<b>Prix indicatif:</b><br/><strong>"+pdt.infos["price"]+"\u20ac HT</strong>" :
          "<b>Prix:</b> "+pdt.infos["price"]));
    
    // url's
    for (var fieldName in pdt.urls)
      $("#lc_pdt_sheet").find(".pdt_"+fieldName).attr("href", pdt.urls[fieldName]);            

    // pics
    $("#lc_pdt_sheet .pdt_pic_url").attr("src",pdt.pics[0]["card"]);
    
    // refs
    if (pdt.infos["ref_count"] > 0) {
      var ref_header_html = "<tr><th>Réf. TC</th><th>Libellé</th>";
      for (var k=0; k<pdt.refs[0].length; k++)
        ref_header_html += "<th>"+pdt.refs[0][k]+"</th>";
      ref_header_html += "<th>Prix HT</th></tr>";
      $("#pdt_refs_header").empty().html(ref_header_html);

      var ref_rows_html = "";
      for (var r=1; r<pdt.refs.length; r++) {
        ref_rows_html += "<tr><td>"+pdt.refs[r]["id"]+"</td><td>"+pdt.refs[r]["label"]+"</td>";
        for (var c=0; c<pdt.refs[r]["content"].length; c++)
          ref_rows_html += "<td>"+pdt.refs[r]["content"][c]+"</td>";
        ref_rows_html += "<td>"+pdt.refs[r]["price"]+"\u20ac</td></tr>";
      }
      $("#pdt_refs_rows").empty().html(ref_rows_html);
      $("#pdt_refs").show();
    }
    else
      $("#pdt_refs").hide();

    $("#lc_pdt_sheet").show();
  };
  
  HN.TC.BO.Leads.createLeadTree = function(table){
    var tri = 0;
    var trs = $(table).find("tr").get();
    $(trs).filter("[class='']:odd").addClass("odd");

    var createTreeLevel = function(dn) {
      // Adding | and + pics
      var $td = $(trs[tri]).find("td:first");
      for (var i=1; i<dn; i++)
        $td.append("<div class=\"more\"></div>");
      var div_folder = document.createElement("div");
      div_folder.className = "add";
      $td.append(div_folder);

      tri++;
      var trs_cat = [];
      var trs_start = tri;
      while(tri < trs.length) {
        if ($(trs[tri]).hasClass("selem"+dn)) {
          for (var i=1; i<=dn; i++)
            $(trs[tri]).find("td:first").append("<div class=\"more\"></div>");
          trs_cat.push(trs[tri]);
          tri++;
        }
        else if ($(trs[tri]).hasClass("scat"+(dn+1))) {
          trs_cat.push(trs[tri]);
          createTreeLevel(dn+1);
        }
        else {
          break;
        }
      }
      var trs_over = trs.slice(trs_start, tri);
      $(trs_cat).filter(":odd").addClass("odd");

      $(div_folder).click(function(){
        if ($(div_folder).hasClass("add")) {
          $(trs_cat).show();
          $(trs_cat).find("td:first div.sub").click().click();
        }
        else
          $(trs_over).hide();

        $(div_folder).toggleClass("add").toggleClass("sub");
        return false;
      });
    };

    while (tri < trs.length) {
      if ($(trs[tri]).hasClass("scat1"))
        createTreeLevel(1);
      else
        tri++;
    }
  };
  
  $(function(){
    var lsels = "input[type='text'],input[type='hidden'],input[type='checkbox'],select,textarea"; // lead source element list string
    var pdt;
    
    // reverso form
    var lead_form = $("#lc_lead_form");

      // Reverso Initialisation
  if (reversoLoaded) {
    ObjReverso.fireCallback = function( response ) {
      if ( response!="NULL") {
        if (response.last_name) $("input[name='nom']", lead_form).val(response.last_name);
        if (response.first_name) $("input[name='prenom']", lead_form).val(response.first_name);
        if (response.address) $("input[name='adresse']", lead_form).val(response.address);
        if (response.zip) $("input[name='cp']", lead_form).val(response.zip);
        if (response.city) $("input[name='ville']", lead_form).val(response.city);
        if (response.country) $("select[name='pays']", lead_form).val(response.country);
        if (response.company) $("input[name='societe']", lead_form).val(response.company);
        if (response.service) $("input[name='service']", lead_form).val(response.service);
        $("input[name='reversoReversed']", lead_form).val(1);
      }else{
        $("input[name='reversoReversed']", lead_form).val(0);
      }
    };
  }
  $("input[name='telephone']", lead_form).keyup(function(){
    var tel = this.value.match(/\d+/g).join("");
    if (tel.length >=10 && reversoLoaded) ObjReverso.reverso(tel);
  });
    $("#lc_lead_form input[name='telephone']").keyup(function(){
      var tel = (this.value.match(/\d+/g) || []).join("");
      if (tel.length >=10) {
        //ObjReverso.reverso(tel);
        $(this).next().attr("href","tel:"+tel);
      }
    });

    $("#lc_see_pdt_sheet").click(function(){ HN.TC.BO.Leads.ShowProductDetails(pdt); });
    $("#lc_pdt_sheet").draggable().find("img.close").click(function(){ $("#lc_pdt_sheet").hide(); });
    $("#lc_pdt_script").draggable().find("img.close").click(function(){ $("#lc_pdt_script").hide(); });
    $("#lc_ld").draggable().find("img.close").click(function(){ $("#lc_ld").hide(); });
    $("#lc_pdt_preview, #lc_pdt_sheet").find("a._blank").click(function(){ window.open(this.href, "_blank"); return false });
    $("#lc_pdt_search_go").click(function(){

      var customerEmail = $("#lc_pdt_search input[name='customer_email']").val();

      var ajaxHandleCall = {
        url: "AJAX_getLastCall.php",
        dataType: "json",
        error: function (XMLHttpRequest, textStatus, errorThrown) {
          $("#lastCallStatus").text(textStatus);
        },
        success: function (data, textStatus) {
          var html = '';
          if (data.error) {
            $("#showCallStatus").html("<div id=\"callStatus\">"+data.error+'</div>');
          }
          else if(data.reponse) {
            var callText = data.reponse.callResult == 'not_called' ? "Ce contact est en attente d'appel " : "Ce contact a été appelé par "+data.reponse.operator;
            html += "<div id=\"callStatus\">Attention !  "+callText+"<br />"
                    +"le "+data.reponse.date+".<br />"
                    +"Statut de l'appel : "+data.reponse.status+"<br />";
            if (data.reponse.pendingCall)
              html += '<button name="getMeOut" id="getMeOut" class="button">Sortir le contact de la pile</button>';
              html += '</div>';
            $("#showCallStatus").html(html);
            $("#showCallStatus").html(html).find("button").click(function(){
              ajaxHandleCall.type = "POST";
              ajaxHandleCall.data = "action=GetOutOfSpool&customerEmail="+$("#lc_pdt_search input[name='customer_email']").val()+"&idCall="+data.reponse.callId;
              $.ajax(ajaxHandleCall);
              $('#callBar').hide();
              $('#inCallbar').hide();
            });
          }
          if (data.sortie == 'ok') {
            html += "<div id=\"callStatus\">Ce contact a été sorti de la pile d'appels<br />";
            html += '</div>';
            $("#showCallStatus").html(html);
          }
        }
      };
      
      // get last call infos
      if (customerEmail != "") {
        ajaxHandleCall.type = "GET";
        ajaxHandleCall.data = "action=get_call_infos&customerEmail="+customerEmail;
        $.ajax(ajaxHandleCall);
      }
      
      getNotesInternes();
      
      var pdtId = $("#lc_pdt_search input[name='pdt_id']").val();
      if (pdtId != "" && customerEmail == "")
          $("#lc_pdt_search select[name='origin']").val("Téléphone entrant");
      else if (pdtId == "" && customerEmail != "")
          $("#lc_pdt_search select[name='origin']").val("Téléphone sortant");
      var campaign;
      <?php if($callType==2) echo 'campaign = true;' ?>
      if (campaign == true)
          $("#lc_pdt_search select[name='origin']").val("Campagne d'appels");
      var origin = $("#lc_pdt_search select[name='origin']").val();
      
      var template = HN.TC.toDashAz09(origin);
      
      // reseting recommended product block by default
      $("#lc_pdt_suggest").hide();
      $("#lc_pdt_suggest_error").text("");
      
      // fill the form with customer's infos if an email was set
      if (customerEmail != "") {
        $.ajax({
          type: "POST",
          url: "AJAX_interface.php",
          data: {"actions":[{"action":"get_customer_infos","customerEmail": customerEmail}]},
          dataType: "json",
          error: function (XMLHttpRequest, textStatus, errorThrown) {
            $("#lc_customer_search_error").text(textStatus);
          },
          success: function (data, textStatus) {
            if (data.error && data.error.text && data.error.text != "") {
              $("#lc_customer_search_error").text(data.error.text);
              $("#lc_lead_history").hide();
            } else {
              $("#lc_customer_search_error").text("");
              var $lsel = $("#lc_lead_form").find(lsels);
              for (var fieldName in data.data.customer_infos) {
                var $lse = $lsel.filter("[name='"+fieldName+"']");
                if ($lse.attr("type") == "checkbox")
                  $lse.attr("checked",data.data.customer_infos[fieldName]=="1"?true:false);
                else
                  $lse.val(data.data.customer_infos[fieldName]);

                if(fieldName == 'secteur_activite')
                  add_qualification_form();
                if(fieldName == 'qualification_sector_text')
                  $('input[name=qualification_sector_text]').val(data.data.customer_infos[fieldName]);
                if(fieldName == 'sector_qualification')
                  $('select[name=sector_qualification]').val(data.data.customer_infos[fieldName]).selectValue;
              }
              
              $("#lc_lead_form input[name='telephone']").next().attr("href","tel:"+(data.data.customer_infos["telephone"].match(/\d+/g) || []).join("")); // telephone dial
              
              HN.TC.BO.Leads.ShowRecommendedProducts(pdtId, data.data.customer_infos["id"], template, "lc_pdt_suggest");
              
              var html = "";
              for (var li=0; li<data.data.lead_list.length; li++) {
                var lead = data.data.lead_list[li];
                html += "<tr"+(lead.clc?" class=\"scat1\"":"")+">"+
                          "<td class=\"tree\"></td>"+
                          "<td class=\"date\">"+lead["date"]+"</td>"+
                          "<td class=\"id\">"+lead["id"]+"</td>"+
                          "<td>"+lead["company"]+"</td>"+
                          "<td>"+lead["pdt_name"]+"</td>"+
                          "<td>"+lead["pdt_id"]+"</td>"+
                          "<td>"+lead["adv_name"]+"</td>"+
                          "<td>"+lead["adv_category_name"]+"</td>"+
                          "<td>"+lead["invoice_status"]+"</td>"+
                          "<td>"+lead["income"]+"</td>"+
                          "<td>"+lead["clc"]+"</td>"+
                          "<td>"+lead["income_total"]+"</td>"+
                        "</tr>";
                if (lead.clc) {
                  for (var li2=0; li2<lead.lead2_list.length; li2++) {
                    var lead2 = lead.lead2_list[li2];
                    html += "<tr class=\"selem1\">"+
                              "<td class=\"tree\"></td>"+
                              "<td class=\"date\">"+lead2["date"]+"</td>"+
                              "<td class=\"id\">"+lead2["id"]+"</td>"+
                              "<td>"+lead2["company"]+"</td>"+
                              "<td>"+lead2["pdt_name"]+"</td>"+
                              "<td>"+lead2["pdt_id"]+"</td>"+
                              "<td>"+lead2["adv_name"]+"</td>"+
                              "<td>"+lead2["adv_category_name"]+"</td>"+
                              "<td>"+lead2["invoice_status"]+"</td>"+
                              "<td>"+lead2["income"]+"</td>"+
                              "<td>-</td>"+
                              "<td>-</td>"+
                            "</tr>";
                  } //end lead2_list
                } // end lead.clc
              } // end lead_list
              $("#lc_lead_history tbody").empty().html(html).find("td:gt(0)").click(function(){
               var lead_id = $(this).closest("tr").find("td.id").html();
                $.ajax({
                  type: "POST",
                  url: "AJAX_interface.php",
                  data: {"actions":[{"action":"get_lead_infos","leadId": lead_id}]},
                  dataType: "json",
                  error: function (XMLHttpRequest, textStatus, errorThrown) {},
                  success: function (data, textStatus) {
                    if (!data.error) {
                      var lead = data.data.lead_detail;
                      for (var fieldName in lead)
                        if (!$.isArray(lead[fieldName]))
                          $("#lc_ld_"+fieldName).html(lead[fieldName]);
                      $("#lc_ld").show();
                      
                      var html = "";
                      for (fieldName in lead.customFields) {
                        html += "<div class=\"label\">"+fieldName+" :</div>"+
                                "<div class=\"value\">"+lead.customFields[fieldName]+"</div>"+
                                "<div class=\"zero\"></div>";
                      }
                      $("#lc_ld_customFields").html(html);
                    }
                  }
                });
              });
              HN.TC.BO.Leads.createLeadTree("#lc_lead_history tbody");
              $("#lc_lead_history").show();
            }
          }
        });
      } else { // customerEmail != ""
        HN.TC.BO.Leads.ShowRecommendedProducts(pdtId, null, template, "lc_pdt_suggest");
        $("#lc_lead_history").hide();
      }
      
      // get product infos
      $.ajax({
        type: "POST",
        url: "AJAX_interface.php",
        data: {"actions":[{"action":"get_pdt_infos","pdtId": pdtId}]},
        dataType: "json",
        error: function (XMLHttpRequest, textStatus, errorThrown) {
          $("#lc_pdt_preview, #lc_pdt_suggest").hide(); // not hiding the lead block, the customer code will do it if necessary
          $("#lc_pdt_search_error").text(textStatus);
        },
        success: function (data, textStatus) {
          if (data.error) {
            $("#lc_pdt_preview, #lc_pdt_suggest").hide(); // see above
            $("#lc_pdt_search_error").text(data.error);
            $("#lc_pdt_suggest_error").text("");
          }
          else {
            $("#lc_pdt_preview").show(); // show preview block only
            $("#lc_pdt_search_error").text("");
            $("#lc_lead_form label").removeClass("error"); // remove any error class from the labels

            pdt = data.data.pdtList[0]; // only one product
            
            // pdt infos that are strings or numbers
            for (var fieldName in pdt.infos)
              if (!$.isArray(pdt.infos[fieldName]))
                $("#lc_pdt_preview").find(".pdt_"+fieldName).html(pdt.infos[fieldName]);
            
            $("#lc_pdt_preview").find(".pdt-preview")[pdt.infos.adv_cat==<?php echo __ADV_CAT_BLOCKED__ ?>?"addClass":"removeClass"]("red");
            
            // checkbox leads 2 out
            $("#lc_lead_form input[name='sl']")[pdt.infos["adv_noLeads2out"] == "1" ? "hide" : "show"]();
            
            // url's
            for (var fieldName in pdt.urls)
              $("#lc_pdt_preview").find(".pdt_"+fieldName).attr("href", pdt.urls[fieldName]);            
            
            // pics
            $("#lc_pdt_preview .pdt_pic_url").attr("src",pdt.pics[0]["thumb_small"]);

            // cat3 list
            if (pdt["cat2_children"][pdt.infos.cat2_id].length > 0) {
              $('a.pdt_cat2_bo_search_url').live(
                'click', function (){

                $('#cat2_children_list').dialog('open');
                $('#cat2_children_list').html('');
                $('#ui-dialog-title-cat2_children_list').text('Liste des familles appartenant à ')
                var html = '';
                html += '<ul>';
                $.each(pdt["cat2_children"][pdt.infos.cat2_id], function(){
                  html += '<li><a href="'+HN.TC.ADMIN_URL+'search.php?search_type=2&amp;search='+this.id+'" class="pdt_cat3_bo_search_url close_cat2_children_list" target="_blank"><strong class="pdt_cat3_name">'+this.name+'</strong></a></li>';
                })
                html += '</ul>';
                
                $('#ui-dialog-title-cat2_children_list').append(pdt.infos["cat2_name"]);
                
                $('#cat2_children_list').html(html);
                return false;
              });
            }
            else
              $("#cat2_children_list").dialog('close');
            
            // not required fields
            var nrfl = pdt.infos["adv_notRequiredFields"];
            nrfl.push("fax"); // always add fax
            $("#lc_lead_form label").each(function(){ // add or remove '(optionnel)' for each label
              if ($.inArray($(this).attr("for"),nrfl) != -1)
                $(this).text($(this).text().replace(/(\s*\(optionnel\))?\s*:$/i," (optionnel) :"));
              else
                $(this).text($(this).text().replace(/\s*\(optionnel\)\s*:$/i," :"));
            });

            // custom fields
            var cfl = pdt.infos["adv_customFields"];
            var $cfc = $("#lc_lead_form_custom_fields")[cfl ? "show" : "hide"]().find(":not(legend)").remove().end(); // remove any old custom fields
            for (var k=0; k<cfl.length; k++) {
              var cf = cfl[k];
              var html = "";
              switch (cf["type"]) {
                case "text":
                  html += "<label for=\""+cf["name"]+"\">"+cf["label"]+" "+(cf["required"]=="1" ? "" : "(optionnel) ")+":</label>"+
                    "<input name=\""+cf["name"]+"\" type=\"text\" maxlength=\""+cf["length"]+"\" class=\"value\" value=\"\"/>";
                  break;
                case "select":
                  html += "<label for=\""+cf["name"]+"\">"+cf["label"]+" "+(cf["required"]=="1" ? "" : "(optionnel) ")+":</label>"+
                    "<select name=\""+cf["name"]+"\" class=\"value\">"+
                    "<option value=\"\">-</option>";
                  var valueList = cf["valueList"].split(",");
                  for (var i=0; i<valueList.length; i++) {
                    var val = valueList[i];
                    html += "<option value=\""+val+"\""+(cf["valueDefault"]==val ? " selected=\"selected\"" : "")+">"+val+"</option>";
                  }
                  html += "</select>";
                  break;
                case "textarea":
                  html += "<label for=\""+cf["name"]+"\">"+cf["label"]+" "+(cf["required"]=="1" ? "" : "(optionnel) ")+":</label>"+
                    "<textarea name=\""+cf["name"]+"\" class=\"value\" rows=\"4\" cols=\"40\"></textarea>";
                  break;
                default: break;
              }
                html += "<div class=\"zero\"></div>";
                $cfc.append(html);
            }
          }
        }
      });
    });
    
    $("#lc_lead_form_go").click(function(){
		
      if (pdt && pdt.infos) {
		  
        var fields = {};
        $("#lc_lead_form").find(lsels).each(function(){
          fields[$(this).attr("name")] = $(this).attr("type")=="checkbox" ? $(this).attr("checked") : $(this).val();
        });
        fields['qualification'] = $('select[name=sector_qualification]').val() != '' ? $('select[name=sector_qualification]').val() : ($('input[name=qualification_sector_text]').val() != '' ? $('input[name=qualification_sector_text]').val() : '');
        var origin = $("#lc_pdt_search select[name='origin']").val();
        $.ajax({
          type: "POST",
          url: "AJAX_interface.php",
          data: {"actions":[{"action":"create_lead","pdtId":pdt.infos.id,"catId":pdt.infos.cat3_id,"fields":fields,"origin":origin}]},
          dataType: "json",
          error: function (XMLHttpRequest, textStatus, errorThrown) {
            $("#lc_lead_form_error").text(textStatus);
          },
          success: function (data, textStatus) {
            $("#lc_lead_form_error").text("");
            $("#lc_lead_form label").removeClass("error").removeAttr("title");
            if (data.error) {
              $("#lc_lead_form_error").text(data.error.text ? data.error.text : data.error);
              if ($.isPlainObject(data.error.list)) {
                for (var fieldName in data.error.list) {
                  $("#lc_lead_form label[for='"+fieldName+"']").addClass("error").attr("title",data.error.list[fieldName]);
                }
              }
            }
            else {
              if (data.data.text == "OK") {
                $("#lc_lead_form_dialog").dialog("open");
                <?php if($callExists){ ?>setCallOk(<?php echo $idCall ?>, <?php echo $idCampaign ? $idCampaign : $idLead ?>, '<?php echo $idClient ?>'); <?php } ?>
              }
            }
          }
        });
      }
    });
    
    $("#lc_lead_form_dialog").dialog({
      width: 550,
      autoOpen: false,
      modal: true
    })
    .find("a:eq(0)").click(function(){
      document.location.reload();
      return false;
    })
    .end()
    .find("a:eq(1)").click(function(){
      $("#lc_lead_form_dialog").dialog("close");
      pdt = {}; // reset to no product selected
      $("#lc_pdt_search input[name='pdt_id']").val(""); // reset provided product's id
      $("#lc_pdt_preview, #lc_pdt_sheet").hide(); // hide product's infos
      return false;
    });
    
    // tmp for test
    //$("#lc_pdt_search input[name='pdt_id']").val("3568309");
    //$("#lc_pdt_search input[name='customer_email']").val("frederic.morange@free.fr");
    
  });

  // manage calls
  function setCallOk(idCall, idLead, idClient){
    $('#callBar').hide();
    $('#inCallbar').hide();
      AJAXHandleProcessCall.data = <? echo $jsDataArgs ?>"&id_client="+idClient+"&callOk=1";
      AJAXHandleProcessCall.redir = 0;
    $.ajax(AJAXHandleProcessCall);
  }

  function setCallOkRedir(idCall, idLead, idClient){
    $('#callBar').hide();
    $('#inCallbar').hide();
      AJAXHandleProcessCall.data = <? echo $jsDataArgs ?>"&id_client="+idClient+"&callOk=1";
      AJAXHandleProcessCall.redir = 1;
    $.ajax(AJAXHandleProcessCall);
  }

  function setCallOkNoLead(idCall, idLead, idClient){
    $('#callBar').hide();
    $('#inCallbar').hide();
      AJAXHandleProcessCall.data = <? echo $jsDataArgs ?>"&id_client="+idClient+"&callOkNoLead=1";
      AJAXHandleProcessCall.redir = 1;
    $.ajax(AJAXHandleProcessCall);
  }

  function setCallNok(idCall, idLead, idClient){
    $('#callBar').hide();
    $('#inCallbar').hide();
      AJAXHandleProcessCall.data = <? echo $jsDataArgs ?>"&id_client="+idClient+"&callNok=1";
      AJAXHandleProcessCall.redir = 1;
    $.ajax(AJAXHandleProcessCall);
    
  }

 var AJAXHandleProcessCall = {
	type: "GET",
	url: "../smpo/<?php echo $ajaxProcessCallUrl ?>.php",
	dataType: "json",
	error: function (XMLHttpRequest, textStatus, errorThrown) {
            $('#show_error_message').text(textStatus);
	},
	success: function (data, textStatus) {
          if(data.error){
            $('#show_error_message').text(data.error);
          }

          if(data.result == 'ok'){

            var idCampaign = data.idCampaign ? data.idCampaign : '';

            if(this.redir ==1)
              document.location.href= '<?php echo ADMIN_URL ?>smpo/<?php echo $ajaxRedirectTo ?>'+idCampaign;
            else if(idCampaign)
              $('#lc_lead_form_dialog').append('<br />-<a href="<?php echo ADMIN_URL ?>smpo/campaign_spool.php?idCampaign='+idCampaign+'">Aller à la campagne d\'appels</a>')
          }
        }
  };

  function qualified_sector_request(){

    var qualification = $('select[name=sector_qualification]').val() != '' ? $('select[name=sector_qualification]').val() : ($('input[name=qualification_sector_text]').val() != '' ? $('input[name=qualification_sector_text]').val() : '');
    var email = $('input[name=email]').val();
    $.ajax({
          type: "POST",
          url: "AJAX_interface.php",
          data: {"actions":[{"action":"update_qualified_sector","qualification":qualification,"email":email}]},
          dataType: "json",
          error: function (XMLHttpRequest, textStatus, errorThrown) {
            $("#lc_lead_form_error").text(textStatus);
          },
          success: function (data, textStatus) {
//            console.log('success');
          }
    });
  };


</script>
<?php if (!empty($pdtId)) { ?>
<script type="text/javascript">
  $(function(){
    $("#lc_pdt_search input[name='pdt_id']").val("<?php echo $pdtId ?>");
    $("#lc_pdt_search_go").click();
  });
</script>
<?php } ?>
<link rel="stylesheet" type="text/css" href="leads.css" />
<link href="<?php echo ADMIN_URL ?>css/ui/ui.datepicker.css" rel="stylesheet" title="style" media="all" />
<style>
  #bloc-IMOrderDetail { border: 1px solid #CCCCCC; font-family: Arial, Helvetica, sans-serif; width : 580px;}
  .bloc-IM-titre { font-size: 12px; font-weight: bold; color: #000; background-color: #E5E1D8; padding: 5px; }
  .bloc-IM-content {padding: 10px;}

  .bloc { border: 1px solid #CCCCCC; font-family: Arial, Helvetica, sans-serif; }
  .bloc-titre2 { font-size: 12px; font-weight: bold; color: #000000; background-color: #E5E1D8; padding: 5px; }
    conversations
  .conversation { width: 99%; font: normal 11px arial, helvetica, sans-serif }
  .conversation .title, .conversation h2 { position: relative; top: 1px; left: 0; float: left; height: 14px; padding: 2px 10px 1px; font: normal 11px arial, helvetica, sans-serif; color: #b00000; border: 1px solid #7f7f7f; background: url(../ressources/images/conversation-title-bg.png) repeat-x; margin-bottom: 0px}
  .conversation ul { clear: both; margin: 0; padding: 0; list-style-type: none; border: 1px solid #4d4d4d }
  .conversation ul.grey { background: #f2f2f2 }
  .conversation ul.white { background: #ffffff }
  .conversation li { margin: 0; padding: 3px 5px; border-top: 1px solid #c6c6c6 }
  .conversation li.first { border-top: 0 }

.DB-bg  { display: none; position: fixed; left: 0; top: 0; width: 100%; height: 100%; background: #000000; -ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity=40)"; filter: alpha(opacity=40); opacity:.40 }
.DB { display: none; position: absolute; padding: 10px; font: bold 13px tahoma, arial, sans-serif; color: #000000; text-align: center; border: 1px solid #cccccc; background: #f4faff }
#RDVBox { left: 20px; top: 50px; width: 900px; }
#RDVBox form table {margin: 0 auto}
#RDVBox form table tr{height: 30px}
#RDVBox form table td.label{width: 150px; vertical-align: top}
.ui-datepicker {z-index: 3000;}
.ui-datepicker-header {height: 20px}
.ui-datepicker .ui-datepicker-prev { left:2px; }
.ui-datepicker .ui-datepicker-next { right:2px; }
.ui-datepicker .ui-datepicker-prev-hover { left:1px; }
.ui-datepicker .ui-datepicker-next-hover { right:1px; }
</style>

<div class="lead-section">
  <a href="leads.php">&lt;&lt; Retourner à la liste des demandes de contact</a>
  <br/>
  <br/>
  <div id="lc_pdt_search" class="block">
    <div class="title">Sélection du produit</div>
    <div class="text fl">
      <label for="pdt_id">Id fiche produit :</label>
      <input class="value" type="text" name="pdt_id" value=""/>
      <span id="lc_pdt_search_error" class="error"></span>
      <div class="zero"></div>
      <label for="origin">Origine :</label>
      <select class="value" name="origin">
        <option value="Téléphone entrant">Téléphone entrant</option>
        <option value="Téléphone sortant">Téléphone sortant</option>
        <option value="Campagne Adwords">Campagne Adwords</option>
        <option value="Mail">Mail</option>
        <option value="Campagne d'appels">Campagne d'appels</option>
		<option value="Chat">Chat</option>
		<option value="Click to call">Click to call</option>
      </select>
      <div class="zero"></div>
      <div class="zero"></div>
      <label for="customer_email">Email client (facultatif) :</label>
      <div class="input_container">
	<input class="value" type="text" name="customer_email" id="country_id" onkeyup="autocomplet()" value="<?php echo $idClient ?>"/>
	   <ul id="country_list_id"></ul>
	  </div>
	  
	  
      <input id="lc_pdt_search_go" class="service_go" type="button" value="OK"/> &nbsp;
	  <input type="button"  value="Voir sa fiche client" onclick="verify_email()" /> 
      <span id="lc_customer_search_error" class="error"></span>
    </div>
    <div id="showCallStatus"></div>
    <div class="zero"></div>
  </div>
  <br/>
  <div id="listeRdv"></div>
    <fieldset class="section-you">
        <legend>Notes internes</legend>
        <div id="showInternalNotes"></div>
      </fieldset>
  <div id="lc_pdt_preview" class="block">
    <div class="title">Information produit</div>
    <div class="pdt-preview">
      <div class="picture"><img class="pdt_pic_url vmaib" src=""/><div class="vsma"></div></div>
      <div class="infos">
        <div class="vmaib">
          <a class="pdt_fo_url _blank" href="" title="Voir la fiche en ligne"><img src="../ressources/icons/monitor_go.png" alt="" class="view-fo"/></a>
          <a class="pdt_bo_url _blank" href="" title="Editer la fiche produit"><strong class="pdt_name"></strong></a><br/>
          <span class="pdt_fastdesc"></span><br/>
          Code fiche produit: <strong class="pdt_id"></strong><br/>
          Famille 3 : <a class="pdt_cat3_bo_search_url _blank" href=""><strong class="pdt_cat3_name"></strong></a><br/>
          Famille 2 : <a class="pdt_cat2_bo_search_url" href=""><strong class="pdt_cat2_name"></strong></a><br/>
          <span class="pdt_adv_cat_name"></span> : <a class="pdt_adv_bo_url _blank" href=""><strong class="pdt_adv_name"></strong></a> - <span class="pdt_adv_salesman_name"></span><br/>
          <a id="lc_see_pdt_sheet" href="#pdt_sheet">Voir description produit</a>
        </div><div class="vsma"></div>
        <div id="cat2_children_list" title="Liste des familles appartenant à "></div>
      </div>
      <div class="zero"></div>
    </div>
    <div class="zero"></div>
  </div>
  <div id="lc_pdt_sheet" class="layer">
    <img class="close" src="../ressources/images/empty.gif" alt=""/>
    <div class="title">Aperçu fiche produit</div>
    <div class="text pdt-sheet">
      <div class="infos">
        <div class="infos-head">
          <h1 class="pdt_name"></h1>
          <strong class="pdt_fastdesc"></strong><br/>
          Code fiche produit: <span class="pdt_id"></span><br/>
          Partenaire : <strong class="pdt_adv_name"></strong> (<span class="pdt_adv_cat_name"></span>)<br/>
        </div>
        <div class="picture"><img class="pdt_pic_url vmaib" src=""/><div class="vsma"></div></div>
        <div class="infos-right">
          <div class="pdt_price"></div>
          Frais de port : <span class="pdt_shipping_fee"></span><br/>
          Commande minimum : <span class="pdt_adv_min_amount"></span><br/>
          Livraison : <span class="pdt_delivery_time"></span><br/>
          Garantie : <span class="pdt_warranty"></span>
        </div>
        <div class="zero"></div>
      </div>
      <div class="desc">
        <h2>Description</h2>
        <div class="pdt_descc"></div>
      </div>
      <div id="pdt_refs" class="refs">
        <table>
          <thead id="pdt_refs_header"></thead>
          <tbody id="pdt_refs_rows"></tbody>
        </table>
      </div>
      
    </div>
  </div>
  <br/>
    <div id="lc_pdt_script" class="layer" style="position:fixed; left: 800px; top:400px">
    <img class="close" src="../ressources/images/empty.gif" alt=""/>
    <div class="title" style="background-color:red">Script produit</div>
    <div class="text pdt-sheet">
      <div class="infos">
      </div>
    </div>
  </div>
  <br/>
  <div id="lc_pdt_suggest" class="block">
    <div class="title">Suggestion produits</div>
    <div id="lc_pdt_suggest_container"></div>
  </div>
  <br/>
  <div id="lc_lead_history" class="block">
    <div class="title">Historique lead du client</div>
    <div class="item-list-container">
      <table class="item-list">
        <thead>
          <tr>
            <th class="tree"></th>
            <th class="date">Date/Heure</th>
            <th class="id">ID</th>
            <th>Société</th>
            <th>Nom produit</th>
            <th>ID Produit</th>
            <th>Nom partenaire</th>
            <th>Type partenaire</th>
            <th>Etat lead</th>
            <th>Revenu lead</th>
            <th>Nb lead 2</th>
            <th>Revenu total</th>
          </tr>
        </thead>
        <tbody>
        </tbody>
      </table>
    </div>
  </div>
  <div id="lc_ld" class="layer">
    <img class="close" src="../ressources/images/empty.gif" alt=""/>
    <div class="title">Aperçu du lead n°<span id="lc_ld_id"></span> du <span id="lc_ld_date"></span></div>
    <div class="text">
      <div class="label">Message facultatif :</div>
      <div class="value"><span id="lc_ld_precisions"></span></div>
      <div class="zero"></div>
      <br/>
      <div class="label">Nom du produit :</div>
      <div class="value"><span id="lc_ld_pdt_name"></span></div>
      <div class="zero"></div>
      <div class="label">Famille du produit :</div>
      <div class="value"><span id="lc_ld_cat_name"></span></div>
      <div class="zero"></div>
      <div class="label"><span id="lc_ld_adv_category_name"></span> :</div>
      <div class="value"><span id="lc_ld_adv_name"></span> (<span id="lc_ld_adv_id"></span>)</div>
      <div class="zero"></div>
      <br/>
      <div class="label">Nom :</div>
      <div class="value"><span id="lc_ld_nom"></span></div>
      <div class="zero"></div>
      <div class="label">Prénom :</div>
      <div class="value"><span id="lc_ld_prenom"></span></div>
      <div class="zero"></div>
      <div class="label">Fonction :</div>
      <div class="value"><span id="lc_ld_fonction"></span></div>
      <div class="zero"></div>
      <div class="label">Email :</div> 
      <div class="value"><span id="lc_ld_email"></span></div>
      <div class="zero"></div>
      <div class="label">Site Internet :</div>
      <div class="value"><span id="lc_ld_url"></span></div>
      <div class="zero"></div>
      <div class="label">Téléphone :</div>
      <div class="value"><span id="lc_ld_tel"></span></div>
      <div class="zero"></div>
      <div class="label">Fax :</div>
      <div class="value"><span id="lc_ld_fax"></span></div>
      <div class="zero"></div>
      <div class="label">Nom de la société :</div>
      <div class="value"><span id="lc_ld_societe"></span></div>
      <div class="zero"></div>
      <div class="label">Nb de salarié :</div>
      <div class="value"><span id="lc_ld_salaries"></span></div>
      <div class="zero"></div>
      <div class="label">Secteur d'activité :</div>
      <div class="value"><span id="lc_ld_secteur"></span></div>
      <div class="zero"></div>
      <div class="label">Code NAF :</div>
      <div class="value"><span id="lc_ld_naf"></span></div>
      <div class="zero"></div>
      <div class="label">SIRET :</div>
      <div class="value"><span id="lc_ld_siret"></span></div>
      <div class="zero"></div>
      <div class="label">Adresse :</div>
      <div class="value"><span id="lc_ld_adresse"></span></div>
      <div class="zero"></div>
      <div class="label">Code Postal :</div>
      <div class="value"><span id="lc_ld_cp"></span></div>
      <div class="zero"></div>
      <div class="label">Ville :</div>
      <div class="value"><span id="lc_ld_ville"></span></div>
      <div class="zero"></div>
      <div class="label">Pays :</div>
      <div class="value"><span id="lc_ld_pays"></span></div>
      <div class="zero"></div>
      <div id="lc_ld_customFields"></div>
    </div>
  </div>
  <br/>
  <div id="lc_lead_form" class="block">
    <div class="title">Formulaire de lead</div>
    <div class="text">
      <fieldset class="section-you">
        <legend>Information personnelles</legend>
        <label for="telephone">Téléphone :</label>
        <input name="telephone" type="text" maxlength="255" class="value" value=""/> <a href="tel:"><img src="../ressources/icons/telephone.png" alt=""/></a>
        <button onClick="getScriptProduct()" id="callScriptProduct" class="fr">Afficher script</button>
        <div class="zero"></div>
        <label for="email">Email :</label>
        <input name="email" type="text" maxlength="255" class="value" value=""/>
       <?php if ($idLead) : ?>
        <div  class="fr"><span id="errorRdv" style="color: red"></span><button id="rdvLayerButton">Prévoir un RDV téléphonique</button></div>
       <?php endif ?>
        <div class="zero"></div>
        <label for="nom">Nom :</label>
        <input name="nom" type="text" maxlength="255" class="value" value=""/>
        <div class="zero"></div>
        <label for="prenom">Prénom :</label>
        <input name="prenom" type="text" maxlength="255" class="value" value=""/>
        <div class="zero"></div>
        <label for="fax">Fax (optionnel) :</label>
        <input name="fax" type="text" maxlength="255" class="value" value=""/>
        <div class="zero"></div>
        <label for="fonction">Fonction :</label>
        <select name="fonction" class="value">
          <option value=""> - </option>
          <?php for ($i = 1; $i <= $pc; $i++) { ?>
            <option value="<?php echo $pl[$i][0] ?>"><?php echo $pl[$i][1] ?></option>
          <?php } ?>
        </select>
		<div class="zero"></div>
		
		
		<div id="result_service">
		<label for="service">Service :</label>
		 <select id="service" name="service">
            <option value=""> - </option>
            <option value="Maire" <?= $selected_true1 ?>>Maire</option>
            <option value="Elu municipal / Adjoint au maire" <?= $selected_true2 ?>>Elu municipal / Adjoint au maire</option>
            <option value="Service Technique / Maintenance" <?= $selected_true3 ?>>Service Technique / Maintenance</option>
            <option value="Service Achats" <?= $selected_true4 ?>>Service Achats</option>
            <option value="Service Sports" <?= $selected_true5 ?>>Service Sports</option>
            <option value="Service Communication" <?= $selected_true6 ?>>Service Communication</option>
            <option value="Service Urbanisme" <?= $selected_true7 ?>>Service Urbanisme</option>
            <option value="Service RH" <?= $selected_true8 ?>>Service RH</option>
            <option value="Service Travaux" <?= $selected_true9 ?>>Service Travaux</option>
          </select>
		</div>  
		  
        <div class="zero"></div>
      </fieldset>
      <fieldset class="section-company">
        <legend>Information société / organisation</legend>
        <label for="societe">Nom société / organisation :</label>
        <input name="societe" type="text" maxlength="255" class="value" value=""/>
        <div class="zero"></div>
        <label for="adresse">Adresse :</label>
        <input name="adresse" type="text" maxlength="255" class="value" value=""/>
        <div class="zero"></div>
        <label for="cadresse">Complément(ZI,BP,etc) :</label>
        <input name="cadresse" type="text" maxlength="255" class="value" value=""/>
        <div class="zero"></div>
        <label for="cp">Code Postal :</label>
        <input name="cp" type="text" maxlength="255" class="value" value=""/>
        <div class="zero"></div>
        <label for="ville">Ville :</label>
        <input name="ville" type="text" maxlength="255" class="value" value=""/>
        <div class="zero"></div>
        <label for="pays">Pays :</label>
        <select name="pays" class="value">
          <?php for ($i = 1; $i <= $cc; $i++) { ?>
            <option value="<?php echo $cl[$i] ?>"<?php if ($cl[$i] == $country_selected) { ?> selected="selected"<?php } ?>><?php echo $cl[$i] ?></option>
          <?php } ?>
        </select>
        <div class="zero"></div>
        <label for="nb_salarie">Taille salariale :</label>
        <select name="nb_salarie" class="value">
          <option value=""> - </option>
          <?php for ($i = 1; $i <= $nec; $i++) { ?>
            <option value="<?php echo $nel[$i] ?>"><?php echo $nel[$i] ?></option>
          <?php } ?>
        </select>
        <div class="zero"></div>
        <label for="secteur_activite">Secteur d'activité :</label>
        <select name="secteur_activite" class="value">
          <option value=""> - </option>
          <?php 
          if(!empty($activity_sectorsList))
            foreach ($activity_sectorsList as $activity_sector) { ?>
            <option value="<?php echo $activity_sector['sector'] ?>"><?php echo to_entities($activity_sector['sector']) ?></option>
          <?php } ?>
        </select>
        <div class="zero"></div>
        <label for="code_naf">Code NAF :</label>
        <input name="code_naf" type="text" maxlength="255" class="value" value=""/>
        <div class="zero"></div>
        <label for="num_siret">Numéro SIRET :</label>
        <input name="num_siret" type="text" maxlength="255" class="value" value=""/>
        <div class="zero"></div>
        <input type="hidden" name="reversoReversed" value="0">
      </fieldset>
      <fieldset id="lc_lead_form_custom_fields" class="section-custom-fields">
        <legend>Autres informations</legend>
      </fieldset>
      <fieldset>
        <legend>Projet</legend>
        <label for="precisions">Description en quelques mots :</label>
        <textarea class="value" name="precisions" rows="4" cols="40"></textarea>
        <div class="zero"></div>
        <label for="campaignID">ID Campagne :</label>
        <input name="campaignID" type="text" maxlength="255" class="value" value="<?php echo $idCampaign ?>"/>
        <div class="zero"></div>
      </fieldset>
      <input type="checkbox" name="sl" checked="checked"/>Envoyer la demande à toutes les sociétés susceptibles de traiter le projet
    </div>
    <input id="lc_lead_form_go" type="button" value="Envoyer le contact" /> <span id="lc_lead_form_error" class="error"></span>
    <br /><br />
    <fieldset>
    <legend>Poster une note</legend>
    <div id="InternalNotesForm">
      <div id="bloc-IMOrderDetail">
        <div class="bloc-IM-titre">Note interne :</div>
        <div class="bloc-IM-content">
          Laisser une note :<br />
          <br />
          <textarea name="contenu_note_interne" cols="65" rows="6"></textarea><br />
          <div class="bloc-preview">
            <a href="#" onClick="sendNoteInterne();return false;">Poster la note</a>
          </div>
        </div>
      </div>
    </div>
  </fieldset>
  </div>
  <div id="lc_lead_form_dialog" title="Demande de devis">
    La demande de devis a bien été envoyée, souhaitez-vous :<br/>
    - <a href="">Envoyer une autre demande de devis</a><br/>
    - <a href="">Envoyer une autre demande au même client</a><br/>
    - <a href="../smpo/calls_list.php">Aller à la liste des appels</a>
  </div>
  <?php
    if ($callExists) {
    $visibilityCall = (strcasecmp($call->call_result, 'not_called') == 0 || strcasecmp($call->call_result, 'absence') == 0 || strcasecmp($call->call_result, 'customer_calls_back') == 0) ? 'visible' : 'hidden';
  ?>
  <div id="bottomBar">
  <div id="callBar" style="visibility : <?php echo $visibilityCall ?>;"></div>
  <div id="inCallbar" style="visibility : <?php echo $visibilityCall ?>;">
    <div>
    <a href="#" onClick="setCallOkRedir(<?php echo $idCall ?>, <?php echo $idCampaign ? $idCampaign : $idLead ?>, '<?php echo $idClient ?>');return false;" class="btn ui-state-default ui-corner-all">
          Appel abouti transformé
        </a>
    <a href="#" onClick="setCallNok(<?php echo $idCall ?>, <?php echo $idCampaign ? $idCampaign : $idLead ?>, '<?php echo $idClient ?>');return false;" class="btn ui-statelike-choice-no ui-corner-all">
          Appel en absence
        </a>
    <a href="#" onClick="setCallOkNoLead(<?php echo $idCall ?>, <?php echo $idCampaign ? $idCampaign : $idLead ?>, '<?php echo $idClient ?>');return false;" class="btn ui-statelike-choice-no ui-corner-all">
          Appel abouti sans lead
        </a>
    </div>
  </div>
  </div>
  <?php } ?>
</div>
<?php if ($idClient) : ?>
<script type="text/javascript">
  $(document).ready(function(){
    $("#lc_pdt_search_go").click();
  });
</script>
<?php endif ?>

<script type="text/javascript">

 /****************************************************************************
  * Internal notes 
  ****************************************************************************/
  var AJAXHandleInternalNotesInAjax = {
    dataType: "json",
    error: function(XMLHttpRequest, textStatus, errorThrown){
      if (AJAXHandleInternalNotesInAjax.url == "AJAX_internalNotes.php") {
        AJAX_InternalNotes_error(XMLHttpRequest, textStatus, errorThrown)
      };
    },
    success: function(data, textStatus) {
      if (AJAXHandleInternalNotesInAjax.url == "AJAX_internalNotes.php") {
        AJAX_InternalNotes_success(data, textStatus);
      };
    }
  };

  function AJAX_InternalNotes_error(XMLHttpRequest, textStatus, errorThrown) {
    $("#showInternalNotes").empty().append('<div class="bloc-titre2" style="color : red">Erreur : '+textStatus+'</div>');
  }

  function AJAX_InternalNotes_success (data, textStatus) {
    var divInternalNotes = $("#showInternalNotes").empty();

    if (data.notes) {
      if (data.notes != 'vide') {
        var html = 
          "<div class=\"bloc\">"+
            "<div class=\"bloc-titre2\">Notes internes li&eacute;es &agrave; ce client</div>"+
            "<div id=\"affiche_notes_internes\" class=\"conversation\">";

        for (i=0; i<data.notes.length; i++) {
          html += 
              "<h2>Message de "+data.notes[i]['sender_name']+" envoy&eacute; le "+data.notes[i]['date']+"</h2>"+
              "<div class=\"zero\"></div>"+
              "<ul class=\"list grey\">"+
                "<li class=\"conversation first\"><pre style=\"white-space : pre-line\">"+
                  data.notes[i]['content']+
                "</pre></li></ul>";
        };

        html +=
            "</div>"+
          "</div>";

        divInternalNotes.append(html);
      }

    } else if(data.error) {
      alert('NOTES INTERNES :\n'+data.error);
    } else if(data.result) {
      divInternalNotes.append(data.result);
      var textarea = $('textarea[name=contenu_note_interne]');
      textarea[0].value = '';
      alert('NOTES INTERNES :\n'+'Message envoyé');
    }
  }

  function getNotesInternes(){
    var idCustomer = $('input[name=customer_email]').val();

    if (idCustomer != '') {
      $("#showInternalNotes").empty().append( '<div class="bloc-titre2"><img src="../../ressources/images/lightbox-ico-loading.gif" alt="chargement..."></div>');
      AJAXHandleInternalNotesInAjax.data = "idCustomer="+idCustomer+"&action=get";
      AJAXHandleInternalNotesInAjax.type = "GET";
      AJAXHandleInternalNotesInAjax.url = "AJAX_internalNotes.php";
      $.ajax(AJAXHandleInternalNotesInAjax);
    }
  }

  function sendNoteInterne(){
    var idCustomer = $('input[name=customer_email]').val(),
        contenuNoteInterne = $('textarea[name=contenu_note_interne]').val();
    
    AJAXHandleInternalNotesInAjax.data = "idCustomer="+idCustomer+"&contenu="+encodeURIComponent(contenuNoteInterne)+"&action=add";
    AJAXHandleInternalNotesInAjax.type = "POST";
    AJAXHandleInternalNotesInAjax.url = "AJAX_internalNotes.php";
    $.ajax(AJAXHandleInternalNotesInAjax);

    getNotesInternes();
  }
  
  if ($('input[name=customer_email]').val() != '')
    getNotesInternes();
  
  
 /****************************************************************************
  * Script product
  ****************************************************************************/
  var AJAXHandleScriptProduct = {
    dataType: "json",
    type: "GET",
    url: "AJAX_getScriptProduct.php",
    error: function(XMLHttpRequest, textStatus, errorThrown){},
    success: function(data, textStatus) {
      if (data.reponse) {
        var sheet = $("#lc_pdt_script"),
            infos = sheet.find('div[class=infos]');
        infos.html('');

        if (data.reponse != 'vide') {
          infos.html(data.reponse);
          sheet.show();
          $('#callScriptProduct').show();
        } else {
          $('#callScriptProduct').hide();
        }
      }
    }
  };

  $("#lc_pdt_script").hide()

  function getScriptProduct(){
    var idProduct = $('input[name=pdt_id]').val();
    AJAXHandleScriptProduct.data = 'idProduct='+idProduct;
    $.ajax(AJAXHandleScriptProduct);
  }

  $('#lc_pdt_search_go').click(function(){
    getScriptProduct();
  });
  
  $('.service_go').click(function(){
    var email = $("#country_id").val();
	$.ajax({
			url: 'verify_service.php?email='+email,
			type: 'GET',
			success:function(data){
				$('#result_service').html(data);
			}
		});
  });
  
  
 /****************************************************************************
  * Postal code autocomplete
  ****************************************************************************/
  var champCodePostal = $('input[name=cp]');
  
  champCodePostal.keyup( function(){
    if(champCodePostal.val().match('[0-9]{5}') && $("input[name='reversoReversed']").val() == 0){

      $.ajax({
        type: "GET",
        data: "code_postal="+champCodePostal.val(),
        dataType: "json",
        url: "<?php echo ADMIN_URL ?>ressources/ajax/AJAX_codesPostaux.php",
        success: function(data) {

          var refBox = $('input[name=ville]');

          if(data['reponses'].length > 1){

            var html = '<table id="cpAutocomplete" class="auto-completion-box" style="min-width: 221px; top: '+(refBox.offset().top + refBox.height() + 7)+'px; left: '+refBox.offset().left+'px; -moz-user-select: none;" >';
            $.each(data['reponses'], function(){
              html += '<tr class=""><td class="prop">'+this.commune+'</td><td class="results"></td></tr>';

            });
            html += '</table>';

            $('#cpAutocomplete').remove(); // avoid multiple layers in case of multiple keyups
            $('body').append(html);

            $.each($('#cpAutocomplete tr'), function(){
              $(this).mouseenter(function(){
                $(this).addClass('over');
              }).mouseleave(function(){
                $(this).removeClass('over');
              }).click(function(){
                refBox.val($(this).find('td.prop').html());
                $('#cpAutocomplete').remove();
              });
            });

            refBox.blur(function(){
              setTimeout(function(){$('#cpAutocomplete').remove();}, 200);
            });

          }else if(data['reponses'].length == 1){
            refBox.val(data['reponses'][0].commune);
          }
        }
      });
    }
  });
  
  
 /****************************************************************************
  * surqualification des secteurs d'activité
  ****************************************************************************/
  var sector_list = $.parseJSON('<?php echo $jsonedActivitySectorList ?>');
  var sector_select = $('select[name=secteur_activite]');

  sector_select.change(function(){add_qualification_form();});

  function add_qualification_form(){
    $('select[name=sector_qualification]').prev('label').remove();
    $('select[name=sector_qualification]').next('div').remove();
    $('#qualification_form_zone').remove();
    $('select[name=sector_qualification]').remove();
    
    var qualification_list = new Array();
    $.each(sector_list, function(){
       var sector = this['sector'];
       var surqualification = this['Surqualifications'];

       $('select[name=secteur_activite] option:selected').each(function(){
         if(sector == $(this).val())
           qualification_list = surqualification;
       });
    })

    var qualification_options = new Array();
    if(qualification_list.length != 0)
      $.each(qualification_list, function(index){
        qualification_options[index] = this.qualification;
      });

      var select = '<label for="sector_qualification">Secteur qualifié :</label><select class="value" name="sector_qualification">';
      select += '<option value="">-</option>';
      if(qualification_options.length != 0)
        $.each(qualification_options, function(){
          select += '<option value="'+this.replace(/^\s+|\s+$/g,"")+'">'+this.replace(/^\s+|\s+$/g,"")+'</option>';
        });
    select += '</select> <div id="qualification_form_zone"><label for="qualification_sector_text">Secteur qualifié hors liste :</label><input type="text" value="" name="qualification_sector_text" /> <button onClick="qualified_sector_request()">Enregistrer hors lead</button></div><div class="zero"></div>';
    $('select[name=secteur_activite]').next('div').after(select);
  }
  // surqualification des secteurs d'activité

 /****************************************************************************
  * RDV
  ****************************************************************************/
  function getRDV(){
    $.ajax({
          type: "GET",
          data: "relationId=<?php echo $idLead ?>&relationType=lead",
          dataType: "json",
          url: "<?php echo ADMIN_URL ?>ressources/ajax/AJAX_rdv.php",
          success: function(data) {
            if(data.reponse != '' && data.reponse != 'liste vide'){
              var listeRdv = $('#listeRdv');

              var html = '<fieldset class="section-you">\n\
                    <legend>Liste des Rendez-vous</legend>'+
                    "<div class=\"bloc\">"+
                        "<div class=\"bloc-titre2\">Rendez-vous liés à ce contact</div>"+
                        "<div id=\"affiche_notes_internes\" class=\"conversation\">"+
                        "<h2>Rappels prévus pour "+$('input[name=prenom]').val()+" "+$('input[name=nom]').val()+", Sté "+$('input[name=societe]').val()+"</h2>"+
                          "<div class=\"zero\"></div>";
              $.each(data.reponse, function(){
                  var dateRdv = new Date((this.timestamp_call*1000));
                  var loggedUser = <?php echo $user->id ?>;
                  var SupprButton = loggedUser == this.operator ? '<img src="../ressources/icons/hexa-no-16x16.png" style="cursor: pointer" alt="Supprimer" class="fr" onClick="deleteRDV('+this.id+')" />' : '';
                  html += 
                            "<ul class=\"list grey\">"+
                                "<li class=\"conversation first\"><pre style=\"white-space : pre-line\">"+
                                "créé par "+this.nom_operator+" prévu le "+(dateRdv.getDate().toString().length == 1 ? '0'+dateRdv.getDate() : dateRdv.getDate())+"/"+((dateRdv.getMonth()+1).toString().length == 1 ? '0'+(dateRdv.getMonth()+1) : (dateRdv.getMonth()+1))+"/"+dateRdv.getFullYear()+" à "+(dateRdv.getHours().toString().length == 1 ? '0'+dateRdv.getHours() : dateRdv.getHours())+":"+(dateRdv.getMinutes().toString().length == 1 ? '0'+dateRdv.getMinutes() : dateRdv.getMinutes())+
                                SupprButton+
                                "</pre></li></ul>";


                  
              });
              html += "</div></div>";
              html += '</fieldset>';
              listeRdv.html(html);
            }else if(data.reponse == 'liste vide'){
                $('#listeRdv').html('');
            }
          }
      });
  }

  $(document).ready(function() {
    
    $("#rdvLayerButton").on("click", function(){
      $("#rdvDb").data("vars", {
        relationType: "lead",
        relationId: <?php echo $idLead ? $idLead : 0 ?>,
        callId: <?php echo $idCall ? $idCall : 0 ?>,
        clientId: "<?php echo $idClient ? $idClient : "" ?>",
        campaignId: <?php echo $idCampaign ? $idCampaign : 0 ?>,
        onSuccess: function(){
          getRDV();
        }
      }).dialog("open");
    });
    
    getRDV();
  });
  
  
 /****************************************************************************
  * automatic activity sector surqualification
  ****************************************************************************/
  var societe = $('input[name=societe]');

  societe.blur( function(){
    if($(this).val() != ''){ // && $("input[name='reversoReversed']").val() == 0

      $.ajax({
        type: "GET",
        data: {"params":[{"action":"processSector", "raison_sociale": $(this).val()}]},
        dataType: "json",
        url: "../../ressources/ajax/AJAX_surqualificationSecteursActivites.php",
        success: function(data) {
          if(data['retour'].length == 1){
            $('select[name=secteur_activite] option[value='+data.data[0].sector+']').attr('selected', true);
            add_qualification_form();
              $('input[name=code_naf]').val(data.data[0].Surqualifications[0].naf);
            var inSelect = false;
  //          if(data.data[0].Surqualifications[0].qualification){
               $('select[name=sector_qualification] option').each(function(){
                 if(data.data[0].Surqualifications[0].qualification == $(this).val())
                   inSelect = true;
               });
              if(inSelect)
                $('select[name=sector_qualification] option[value='+data.data[0].Surqualifications[0].qualification+']').attr('selected', true);
              else
                $('input[name=qualification_sector_text]').val(data.data[0].Surqualifications[0].qualification);
  //          }
          }
        }
      });
    }
  });
  
  
 /****************************************************************************
  * others
  ****************************************************************************/
  
  // dialog declaration
  $("#cat2_children_list").dialog({
    width: 550,
    autoOpen: false,
    modal: true,
    position: [350,200]
  });

  $('.close_cat2_children_list').live(
    'click', function(){
    $("#cat2_children_list").dialog('close');
  });

</script>

<?php } require(ADMIN."tail.php") ?>
