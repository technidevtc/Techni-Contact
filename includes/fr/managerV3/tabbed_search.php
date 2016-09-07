<?php
/*================================================================/

	Techni-Contact V3 - MD2I SAS
	http://www.techni-contact.com

	Auteur : Hook Network SARL - http://www.hook-network.com
	Date de création : 21 Juillet 2011


	Fichier : /includes/managerV3/tabbed_search.php
	Description : recherche globale en onglet

/=================================================================*/

?>
<style>
#glob_pdt_sheet  h1 { font-weight: bold; font-size: 22px; color: #b00000 }
#glob_pdt_sheet  h2 { font-weight: bold; font-size: 15px; color: #b00000 }
#glob_pdt_sheet  .picture { float: left; width: 250px; height: 226px; padding: 2px; text-align: center; border: 1px solid #cccccc }
#glob_pdt_sheet  .infos { padding: 7px 5px; border: 1px solid #e4e4e4; background: url(../ressources/images/block-bg-grey-150.gif) repeat-x #fcfcfc }
#glob_pdt_sheet  .infos .infos-head { padding: 3px 0 }
#glob_pdt_sheet  .infos .infos-right { float: left; height: 200px; padding: 0 0 12px 20px; font: normal 11px arial, helvetica, sans-serif }
#glob_pdt_sheet  .infos .infos-right strong { font-size: 12px; margin: 0; padding: 0 }
#glob_pdt_sheet  .infos .infos-right .pdt_price { width: 150px; margin: 10px 0; font-size: 18px; color: #b00000; text-align: center }
#glob_pdt_sheet  .infos .infos-right .pdt_price strong { font-weight: bold; font-size: 23px }
#glob_pdt_sheet  .desc { margin: 10px 0 0; padding: 7px; border: 1px solid #e4e4e4; background: #fcfcfc }
#glob_pdt_sheet  .refs { margin: 10px 0 0 }
#glob_pdt_sheet  .refs table { width: 100%; font-size: 11px; border: 1px solid #8b8b8b; border-collapse: collapse }
#glob_pdt_sheet  .refs th { padding: 5px; font-weight: bold; color: #ffffff; text-align: center; background: #b00000 }
#glob_pdt_sheet  .refs td { padding: 5px; text-align: center; border-left: 1px solid #E8E8E8 }
#glob_pdt_sheet  .refs td:first-child { border: 0 }
</style>
<link type="text/css" rel="stylesheet" href="<?php echo ADMIN_URL ?>css/tabbed_search.css">
<div id="tabbed_searchLayer">
  <div id="tabbed_searchLayerList">
    <div id="tabbed-search-form">
      <form action="<?php echo ADMIN_URL ?>search.php" method="get">
        <input type="hidden" name="tabbed_search_sort" />
        <input type="hidden" name="tabbed_search_sortway" />
        <input type="hidden" name="tabbed_search_lastsort" />
        <div class="tabbed-search-boxBis">
       <?php if ($userPerms->has($fntByName["m-prod--sm-products"], "r")) { ?>
        <input type="radio" name="tabbed_search_type" value="1" checked="checked" /><label>Pr</label>
       <?php } ?>
       <?php if ($userPerms->has($fntByName["m-prod--sm-categories"], "r")) { ?>
        <input type="radio" name="tabbed_search_type" value="2" /><label>Fam</label>
       <?php } ?>
        </div>
        <div class="tabbed-search-box">
          <input type="text" name="search" id="tabbed-search-input" class="tabbed_search inputText" title="Entrez ici votre recherche" autocomplete="off"/>
        </div>
      </form>
    </div>
    <div class="zero"></div>
    <div id="tabbed_search_show_results"></div>
  </div>
  <div id="tabbed_searchLayerOnglet"><span class="tabbed_searchLayerOnglet">Recherche</span></div>
  <div class="zero"></div>
</div>
<script type="text/javascript">

  var adv_cat_list = new Array();
<?php
foreach ($adv_cat_list as $id_adv_cat => $part)
{
 print 'adv_cat_list[' . $id_adv_cat . '] = ["' . str_replace('"', '\"', $part['name']) . '", "' . $part['desc'] . '", "' . str_replace("'", "\'", $part['pre']) . '"];
   ';
}
?>
  
  $("#tabbed_searchLayer").css("left", -($("#tabbed_searchLayerList").width()));
  $("#tabbed_searchLayerOnglet").toggle(function(){
    $("#tabbed_searchLayer").animate({"left": "+="+$("#tabbed_searchLayerList").width()+"px"}, "fast");
  },
  function(){
    $("#tabbed_searchLayer").animate({"left": "-="+$("#tabbed_searchLayerList").width()+"px"}, "fast");
  }
);

function tabbed_search_sortBy(arg){
  $("input[name=tabbed_search_sort]").val(arg);
  var sortway = $("input[name=tabbed_search_sortway]").val();
  var new_sortway = sortway == '' ? 'desc' : (sortway == 'asc' ? 'desc': (sortway == 'desc' ? 'asc': 'desc'));
  var sortway = $("input[name=tabbed_search_sortway]").val(new_sortway);
  triggertabbed_search();
}
  
function triggertabbed_search(){
  $.ajax({
      type: "GET",
      data: "search_type="+$('input[name=tabbed_search_type]:checked').val()+"&search="+$('input.tabbed_search').val()+"&sort="+$('input[name=tabbed_search_sort]').val()+"&sortway="+$('input[name=tabbed_search_sortway]').val()+"&lastsort="+$('input[name=tabbed_search_lastsort]').val(),
      lastSort: $('input[name=tabbed_search_sort]').val(),
      dataType: "json",
      url: "<?php echo ADMIN_URL ?>ressources/ajax/AJAX_tabbed_search.php",
      success: function(data) {

      var lastSort = this.lastSort;
      if(data)
        if(data.error){
          $('#tabbed_searchLayerList').html('Erreur à la recherche des rendez-vous : '+data.error+' ');
        }else{
          if(data.count != 0){
            $('#tabbed_search_show_results').html('');
            var html = '<div id="search-results">\n\
    <table class="php-list" cellspacing="0" cellpadding="0">\n\
      <thead>\n\
        <tr>\n\
          <th style="width: auto">Image</th>\n\
          <th style="width: 20%">Nom</th>\n\
          <th style="width: 5%">ID</th>\n\
          <th style="width: 20%">Description rapide</th>\n\
          <th style="width: 20%"><a href="javascript: tabbed_search_sortBy(\'adv_name\');">Nom partenaire</a></th>\n\
          <th style="width: 20%"><a href="javascript: tabbed_search_sortBy(\'adv_cat\');">Type partenaire</a></th>\n\
        </tr>\n\
      </thead>\n\
      <tbody>';
            $.each(data.data_row, function(){
              html += '<tr>\n\
          <td><a href="javascript:HN.TC.BO.Global.show_prod_detailled_desc('+this.idProduct+', '+this.idTC+');" title="Voir la fiche produit"><img src="'+this.links_url.fo_pdt_pic_url+'" alt=""></a></td>\n\
          <td class="title"><a href="'+this.links_url.bo_pdt_url+'">'+this.name+'</a></td>\n\
          <td class="pdtID"><a href="'+this.links_url.bo_lead_create_url+'">'+this.idProduct+'</a></td>\n\
          <td>'+this.fastdesc+'</td>\n\
          <td class="adv_name"><a href="'+this.links_url.bo_adv_url+'">'+this.adv_name+'</a></td>\n\
          <td class="adv_cat">'+adv_cat_list[this.adv_cat][0]+'</td>\n\
        </tr>';
            });
            html += '</tbody>\n\
                     </table>\n\
</div>';
          $('input[name=tabbed_search_lastsort]').val(lastSort);
            $('#tabbed_search_show_results').html(html);
            $.cookie('lastTabbedSearch_term', $('input.tabbed_search').val(),{ path: '/' });
            $.cookie('lastTabbedSearch_type', $('input[name=tabbed_search_type]:checked').val(),{ path: '/' });
          }
        }
      }
  });
}

$(function(){
  tabbedSearchAC = new HN.UI.AutoCompletion($(".tabbed-search-box .tabbed_search").get(0));
  var time_offset = <?php echo time() ?>*1000 - (new Date()).getTime();
  setInterval(function(){
    var today = new Date();
    today.setTime(today.getTime()+time_offset);
    $("#ddate").html(sprintf("%02d/%02d/%04d - %02dh%02dm%02ds",today.getDate(),today.getMonth()+1,today.getFullYear(),today.getHours(),today.getMinutes(),today.getSeconds()));
  },1000);
  $('#tabbed-search-input-AC-box').css('left', $('#tabbed-search-input').offset().left+$('#tabbed_searchLayerList').width());
});

$(document).ready(function(){
  $('#tabbed-search-form form').submit(function(e){
    e.preventDefault();
    triggertabbed_search();
    return false;
  });

  // dialog declaration
  $("#prod_detailled_desc_dialog").dialog({
    width: 800,
    autoOpen: false,
    modal: true
  });

  $(".ui-dialog").draggable("option", "containment", '.ui-widget-overlay');

  $('.close_prod_detailled_desc_dialog').live(
    'click', function(){
    $("#cat2_prod_detailled_desc_dialog").dialog('close');
  });

  if($.cookie('lastTabbedSearch_term') != '' && $.cookie('lastTabbedSearch_type') != ''){
    $('input.tabbed_search').val($.cookie('lastTabbedSearch_term'));
    $('input[name=tabbed_search_type][value='+$.cookie('lastTabbedSearch_type')+']').attr('checked','checked');
    triggertabbed_search();
  }
});



if (!HN.TC.BO) HN.TC.BO = {};
if (!HN.TC.BO.Global) HN.TC.BO.Global = {};
HN.TC.BO.Global.show_prod_detailled_desc = function(prodId, idTC){

  $("#prod_detailled_desc_dialog").dialog('open');
  $('#ui-dialog-title-prod_detailled_desc_dialog').html('');

  $("#prod_detailled_desc_dialog").html('<div id="glob_pdt_sheet" class="layer">\n\
    <img class="close" src="../ressources/images/empty.gif" alt=""/>\n\
    <div class="text pdt-sheet">\n\
      <div class="infos">\n\
        <div class="infos-head">\n\
          <h1 class="pdt_name"></h1>\n\
          <strong class="pdt_fastdesc"></strong><br/>\n\
          Code fiche produit: <span class="pdt_id"></span><br/>\n\
          Partenaire : <strong class="pdt_adv_name"></strong> (<span class="pdt_adv_cat_name"></span>)<br/>\n\
        </div>\n\
        <div class="picture"><img class="pdt_pic_url vmaib" src=""/><div class="vsma"></div></div>\n\
        <div class="infos-right">\n\
          <div class="pdt_price"></div>\n\
          Frais de port : <span class="pdt_shipping_fee"></span><br/>\n\
          Commande minimum : <span class="pdt_adv_min_amount"></span><br/>\n\
          Livraison : <span class="pdt_delivery_time"></span><br/>\n\
          Garantie : <span class="pdt_warranty"></span>\n\
        </div>\n\
        <div class="zero"></div>\n\
      </div>\n\
      <div class="desc">\n\
        <h2>Description</h2>\n\
        <div class="pdt_descc"></div>\n\
      </div>\n\
      <div id="pdt_refs" class="refs">\n\
        <table>\n\
          <thead id="pdt_refs_header"></thead>\n\
          <tbody id="pdt_refs_rows"></tbody>\n\
        </table>\n\
      </div>\n\
    </div>\n\
  </div>');

  $.ajax({
      type: "POST",
      url: "<?php echo ADMIN_URL ?>contacts/AJAX_interface.php",
      data: {
        "actions":[{
          "action": "get_pdt_infos",
          "pdtId": prodId,
          "idTC": idTC
        }]
      },
      dataType: "json",
      error: function (XMLHttpRequest, textStatus, errorThrown) {},
      success: function (data, textStatus) {
        if (data.error) {
          $("#info_error").text(data.error);
          $("#info_error").hide();
        }
        else {
          var pdt = data.data.pdtList[0]
        
     for (var fieldName in pdt.infos)
      if (!$.isArray(pdt.infos[fieldName]))
        $("#glob_pdt_sheet").find(".pdt_"+fieldName).html(pdt.infos[fieldName]);

     $('#ui-dialog-title-prod_detailled_desc_dialog').html('description de '+pdt.infos.name);

    // price specific processing
    $("#glob_pdt_sheet .pdt_price").html(
      pdt.infos["saleable"] ?
        (pdt.infos["ref_count"] > 1 ?
          "à partir de<br/><strong>"+pdt.infos["price"]+"\u20ac HT</strong>" :
          "<strong>"+pdt.infos["price"]+"\u20ac HT</strong>") :
        (pdt.infos["hasPrice"] ?
          "<b>Prix indicatif:</b><br/><strong>"+pdt.infos["price"]+"\u20ac HT</strong>" :
          "<b>Prix:</b> "+pdt.infos["price"]));

    // url's
    for (var fieldName in pdt.urls)
      $("#glob_pdt_sheet").find(".pdt_"+fieldName).attr("href", pdt.urls[fieldName]);

    // pics
    $("#glob_pdt_sheet .pdt_pic_url").attr("src",pdt.pics[0]["card"]);

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

    $("#glob_pdt_sheet").show();

        }
      }
  });
}
</script>
      <div id="prod_detailled_desc_dialog" title="Description de "></div>
