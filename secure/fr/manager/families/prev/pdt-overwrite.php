<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
      
// Loading XML
$dom = new DomDocument();
$dom->validateOnParse = true;
$dom->load(XML_CATEGORIES_ALL);
$xPath = new DOMXPath($dom);

// Globals stats
$cat0 = $xPath->query("parent::categories",$dom->getElementById(XML_KEY_PREFIX."0"))->item(0);
$stats_key = explode("|",$xPath->query("child::stats_key",$cat0)->item(0)->nodeValue);
$stats = explode("|",$xPath->query("child::stats",$cat0)->item(0)->nodeValue);
for($sk = 0, $slen = count($stats); $sk < $slen; $sk++) $global[$stats_key[$sk]] = $stats[$sk];

$cat1List = $xPath->query("child::category", $cat0);

//$catTree = $xPath->query("ancestor-or-self::category",$dom->getElementById($curCategory));

$title = $navBar = "Sélection des produits à afficher en priorité par famille";
require(ADMIN."head.php");


?>
<link type="text/css" rel="stylesheet" href="<?php echo ADMIN_URL ?>ressources/css/HN.Mods.DialogBox.blue.css"/>
<link type="text/css" rel="stylesheet" href="<?php echo ADMIN_URL ?>ressources/css/HN.Mods.MISM.blue.css">
<script type="text/javascript" src="AJAXclasses.js" ></script>
<script type="text/javascript" src="AJAXmodules.js" ></script>
<script type="text/javascript" src="<?php echo ADMIN_URL ?>ressources/js/ManagerFunctions.js"></script>
<script type="text/javascript">
$(function(){
	if (!window.HN) HN = window.HN = {};
	if (!HN.TC) HN.TC = {};
	if (!HN.TC.BO) HN.TC.BO = {};
	if (!HN.TC.BO.ISbC) HN.TC.BO.ISbC = {};
	
	var catID, type, cat1ID, cat2ID, cat3ID;
	// Multiple Products Selection Dialog Box (MPSDB)
	HN.TC.BO.ISbC.MISM = new HN.Mods.MISM("MPSDB");
	HN.TC.BO.ISbC.MISM.Build();
	//4 4562 823
	//MISM.JailCategories(5,4562);
	//MISM.OpenCategories(5,4562,823);

	HN.TC.BO.ISbC.MPSDB = new HN.Mods.DialogBox("MPSDB");
	HN.TC.BO.ISbC.MPSDB.setTitleText("Choisir une liste de produits");
	HN.TC.BO.ISbC.MPSDB.setMovable(true);
	HN.TC.BO.ISbC.MPSDB.showCancelButton(true);
	HN.TC.BO.ISbC.MPSDB.showValidButton(true);
	HN.TC.BO.ISbC.MPSDB.setValidFct(function() {
		if (catID != null && type != null) {
			HN.TC.BO.ISbC.setASICT(catID, type, HN.TC.BO.ISbC.MISM.GetSelectedItems(), function(){
				HN.TC.BO.ISbC.getASICT(catID);
			});
		}
	});
	//HN.TC.BO.ISbC.MPSDB.setShadow(true);
	HN.TC.BO.ISbC.MPSDB.Build();
	
	var cat1_cur, cat2_cur, cat1_hide_timer, cat2_hide_timer, cat3_hide_timer;
	var $categories = $("div.categories, div.cat3");
	$categories.find("li.cat1").mouseover(function(){
		if (cat1_cur) {
			$("div:first", cat1_cur).removeClass("hover").next("div.cat2").hide();
			clearTimeout(cat1_hide_timer);
		}
		$("div:first", this).addClass("hover").next("div.cat2").show().next("ul").show();
               // console.log($("div:first", this).next("div.cat2"), $("div:first", this).next("div.cat2").find('ul'))
                
		cat1_cur = this;
	})
	.mouseout(function(){
		var cat1 = this;
		cat1_hide_timer = setTimeout(function(){ $("div:first", cat1).removeClass("hover").next("div.cat2").hide(); }, 800);
	});
	
	$categories.find("li.cat2").mouseover(function(){
                clearTimeout(cat3_hide_timer);
		if (cat2_cur) {
			$("div:first", cat2_cur).removeClass("hover");$("div.cat3").hide();
			clearTimeout(cat2_hide_timer);
		}
		$("div:first", this).addClass("hover");

                var ulCat3 = $(this).find("ul.cat3");
                $(".pan-left div.cat3").html(ulCat3.clone());
                $("div.cat3").show();
		cat2_cur = this;
	}).mouseout(function(){
		var cat2 = this;
		cat2_hide_timer = setTimeout(function(){ $("div:first", cat2).removeClass("hover"); $(".pan-left div.cat3").hide(); }, 800);
	});
        
	$(".pan-left div.cat3 li.cat3").live({
          mouseover:function(){
            clearTimeout(cat1_hide_timer);
            clearTimeout(cat2_hide_timer);
            $("div:first", this).addClass("hover");
          },
          mouseout : function(){
                  $("div:first", this).removeClass("hover");
          }
        });
        
        $(".pan-left div.cat3").live({
          mouseover:function(){
            clearTimeout(cat1_hide_timer);
            clearTimeout(cat2_hide_timer);
            clearTimeout(cat3_hide_timer);
          },
          mouseout: function(){
                  cat3_hide_timer = setTimeout(function(){ $(".pan-left div.cat2").hide(); $(".pan-left div.cat3").hide(); }, 800);
          }
        })
        
	$categories.find(".pdt-list-block .edit").click(function(){
		HN.TC.BO.ISbC.MISM.SetSelectedItems($(this).nextAll("input").val());
		type = this.parentNode.id;
		HN.TC.BO.ISbC.MPSDB.Show();
	});
	$("#btn-update-FO").click(function(){
		$.ajax({
			async: true,
			cache: false,
			data: "action=updateFO",
			dataType: "json",
			error: function (XMLHttpRequest, textStatus, errorThrown) { alert("Echec de la mise à jour du Front Office"); },
			success: function (data, textStatus) { alert("Front Office mis à jour avec succés"); },
			timeout: 10000,
			type: "GET",
			url: "AJAX_pdt-selection.php"
		});
		return false;
	});
	
	// Product Vertical Block Creation Function
	HN.TC.BO.ISbC.createPdtVb = function (pdt_infos) {
		/*<td>
			<div class="pdt-vb">
				<div class="picture">
					<a href="PRODUCT_EDITION_LINK"><img src="PRODUCTS_IMAGE_URL" alt="" class="vmaib"/></a>
					<div class="vsma"></div>
				</div>
				<div class="infos">
					<strong>PRODUCT_NAME</strong>
					<a href="PRODUCT_EDITION_LINK">Voir la fiche produit</a>
					<div class="price">PRODUCT_PRICE</div>
				</div>
			</div>
		</td>*/
		var td = document.createElement("td");
		var pdt_vb = document.createElement("div");
		pdt_vb.className = "pdt-vb";
		td.appendChild(pdt_vb);
			
			var picture = document.createElement("div");
			picture.className = "picture";
			pdt_vb.appendChild(picture);
				var a1 = document.createElement("a");
				a1.href = pdt_infos.url;
				picture.appendChild(a1);
				var img = document.createElement("img");
				img.src = pdt_infos.pic_url;
				img.alt = "";
				img.className = "vmaib";
				a1.appendChild(img);
			
			var infos = document.createElement("div");
			infos.className = "infos";
			pdt_vb.appendChild(infos);
				var strong = document.createElement("strong");
				strong.appendChild(document.createTextNode(pdt_infos.name));
				infos.appendChild(strong);
				var a2 = document.createElement("a");
				a2.href = pdt_infos.url;
				a2.appendChild(document.createTextNode("Voir la fiche produit"));
				infos.appendChild(a2);
				var price = document.createElement("div");
				price.className = "price";
				price.appendChild(document.createTextNode(pdt_infos.price));
				infos.appendChild(price);
		
		return td;
	}
	
	HN.TC.BO.ISbC.setASICT = function (_catID, _type, _asic) {
		var cbf;
		if (arguments.length > 3)
			cbf = arguments[3];
		$.ajax({
			async: true,
			cache: false,
			data: "action=set&catID="+_catID+"&type="+_type+"&asic="+_asic,
			dataType: "json",
			error: function (XMLHttpRequest, textStatus, errorThrown) { alert("Fatal error while setting the Array of Selected Items by Categories"); },
			success: function (data, textStatus) {
				if (data.error)
					alert(data.error);
				HN.TC.BO.ISbC.MPSDB.Hide();
				if (cbf) cbf(); // Optional CallBack Function
			},
			timeout: 10000,
			type: "GET",
			url: "AJAX_pdt-selection.php"
		});
	};
	
	HN.TC.BO.ISbC.getASICT = function (_catID) { // Get Array of Selected Items by Categories and Type
		var cbf;
		if (arguments.length > 1)
			cbf = arguments[1];
		$.ajax({
			async: true,
			cache: false,
			data: "action=get&catID="+_catID,
			dataType: "json",
			error: function (XMLHttpRequest, textStatus, errorThrown) { alert("Fatal error while loading Products Data"); },
			success: function (data, textStatus) {
				$(".pan-right .pdt-list-block table").remove();
				$(".pan-right .pdt-list-block input").val("");
				if (data.length != 0) {
					for (type in data) {
						var ibcls = "";
						var table = document.createElement("table");
						table.className = "pdt-vb-list grey-block";
						var tbody = document.createElement("tbody");
						table.appendChild(tbody);
						var tr = [], tri = 0, ic = 0;
						for (var i = 0; i < data[type].length; i++) {
							ibcls += (i>0?"|":"")+data[type][i]["catID"];
							for (var j = 0; j < data[type][i]["itemList"].length; j++) {
								if (ic%6 == 0) tr[tri] = document.createElement("tr");
								tr[tri].appendChild(HN.TC.BO.ISbC.createPdtVb(data[type][i]["itemList"][j]));
								ibcls += ","+data[type][i]["itemList"][j].id;
								if (ic%6 == 5) tri++;
								ic++;
							}
						}
						for (tri=0; tri<tr.length; tri++)
							tbody.appendChild(tr[tri]);
						$("#"+type+" input").val(ibcls);
						$("#"+type).append(table);
					}
				}
				if (cbf) cbf(); // Optional CallBack Function
			},
			timeout: 10000,
			type: "GET",
			url: "AJAX_pdt-selection.php"
		});
	};
	
	// Menu click behaviour
	$(".pan-left ul div, .pan-left div.cat3 li.cat3").live({
          click: function(){
		var me = this;
		var oldCatID = catID;
		catID = this.id;
		catID = catID.substr("K".length, catID.length);
		if (oldCatID == catID) return false;
		
		HN.TC.BO.ISbC.MPSDB.Hide();
		if (cat1ID) $("#K"+cat1ID).removeClass("selected");
		if (cat2ID) $("#K"+cat2ID).removeClass("selected");
		if (cat3ID) $("#K"+cat3ID).removeClass("selected");
		$(".categories .header .cat1").html("");
		$(".categories .header .cat2").html("");
		$(".categories .header .cat3").html("");
		switch (this.parentNode.className) {
			case "cat1" :
				cat1ID = catID;
				cat2ID = null;
				cat3ID = null;
				$("#K"+cat1ID).addClass("selected");
				$(".categories .header .cat1").html($("#K"+cat1ID).html());
				
				HN.TC.BO.ISbC.MISM.JailCategories(cat1ID);
				HN.TC.BO.ISbC.MISM.OpenCategories(cat1ID);
				
				$("#pdt_flagship").hide();
				$("#pdt_pic").hide();
				$("#pdt_selection").show();
				$("#pdt_favourite").show();
				$("#pdt_mostviewed").show();
				$("#pdt_latest").show();
				break;
				
			case "cat2" :
				cat1ID = $(this).parents(".cat1").find("div:first").get(0).id;
				cat1ID = cat1ID.substr("K".length, cat1ID.length);
				cat2ID = catID;
				cat3ID = null;
				$("#K"+cat1ID).addClass("selected");
				$("#K"+cat2ID).addClass("selected");
				$(".categories .header .cat1").html($("#K"+cat1ID).html()+" >");
				$(".categories .header .cat2").html($("#K"+cat2ID).html());
				
				HN.TC.BO.ISbC.MISM.JailCategories(cat1ID,cat2ID);
				HN.TC.BO.ISbC.MISM.OpenCategories(cat1ID,cat2ID);
				
				if (cat1_cur) {
					clearTimeout(cat1_hide_timer);
					$(".pan-left div.cat2").hide();
				}
				
				$("#pdt_flagship").hide();
				$("#pdt_pic").show();
				$("#pdt_selection").show();
				$("#pdt_favourite").show();
				$("#pdt_mostviewed").show();
				$("#pdt_latest").show();
				break;
				
			case "cat3" :
				cat1ID = $(".pan-left .cat1").find("div.hover").get(0).id;
				cat1ID = cat1ID.substr("K".length, cat1ID.length);
				cat2ID = $(".pan-left #K"+cat1ID).next('div.cat2').find("div.hover").get(0).id;
				cat2ID = cat2ID.substr("K".length, cat2ID.length);
				cat3ID = catID;
				$("#K"+cat1ID).addClass("selected");
				$("#K"+cat2ID).addClass("selected");
				$("#K"+cat3ID).addClass("selected");
				$(".categories .header .cat1").html($("#K"+cat1ID).html()+" >");
				$(".categories .header .cat2").html($("#K"+cat2ID).html()+" >");
				$(".categories .header .cat3").html($("#K"+cat3ID).html());
				HN.TC.BO.ISbC.MISM.JailCategories(cat1ID,cat2ID,cat3ID);
				HN.TC.BO.ISbC.MISM.OpenCategories(cat1ID,cat2ID,cat3ID);
				
				if (cat1_cur) {
					clearTimeout(cat1_hide_timer);
					$('.pan-left div.cat2').hide();
				}
				
				if (cat2_cur) {
					clearTimeout(cat2_hide_timer);
                                        $('.pan-left div.cat2').hide();
                                        $('.pan-left div.cat3').hide();
				}
				$("#pdt_flagship").hide();
				$("#pdt_pic").show();
				$("#pdt_selection").show();
				$("#pdt_favourite").hide();
				$("#pdt_mostviewed").hide();
				$("#pdt_latest").hide();
				break;
			default :
				$("#pdt_flagship").hide();
				$("#pdt_pic").hide();
				$("#pdt_selection").hide();
				$("#pdt_favourite").hide();
				$("#pdt_mostviewed").hide();
				$("#pdt_latest").hide();
			break;
		}
		
		HN.TC.BO.ISbC.getASICT(catID);
		
		return false;
          }
        });
	
});
//MISM.SetSelectedItems('');
</script>
<style type="text/css">
/*#MPSDBShad { z-index: 3; position: absolute; top: 135px; left: 255px; width: 724px; min-height: 244px; visibility: hidden; background: #000000; filter: Alpha (opacity=50, finishopacity=50, style=1) -moz-opacity:.50; opacity:.50; }*/
#MPSDB { z-index: 4; position: absolute; top: 130px; left: 250px; width: 720px; min-height: 240px; visibility: hidden; border: 2px solid #205683 }
.categories { font: normal 12px arial, helvetica, sans-serif; margin: 5px; padding: 5px; border: 1px solid #cccccc; background: #fdfdfd }
.categories img { border: 0 }
.categories .header { height: 18px; margin: 0 0 5px 0; padding: 5px 10px; font: bold 12px arial, helvetica sans-serif; color: #333333; background: #F6F6F6; }
.categories .header a { font: bold 12px arial, helvetica sans-serif; color: #333333; text-decoration: underline }
.categories .header a:hover { text-decoration: none }
.categories .header .cat1 { font-size: 15px; text-transform: uppercase }
.categories .header .cat2 { font-size: 13px; text-transform: uppercase }
.categories .header .cat3 { font-size: 12px; text-transform: uppercase }
.pan-left { position: relative; z-index: 10; float: left;font-weight: bold }
.pan-left ul { position: relative; margin: 0; padding: 0; list-style-type: none; width: 200px }
/*.pan-left li.cat1 { position: relative }*/
.pan-left li.cat1 div:not(.cat2) { display: block; padding: 3px 5px; color: #000000; border: 1px solid #b00000; border-width: 0 1px 1px 1px; background: url(header-menu-bg.gif) repeat-x 0 -5px; cursor: pointer; zoom: 1 }
.pan-left li.cat1:first-child div { border-width: 1px }
.pan-left li.cat1 div.hover { color: #ffffff; background: #b00000  }
.pan-left li.cat1 div.selected { color: #333333; background: #F6F6F6 }
.pan-left li.cat1 ul { list-style-type: none;}
.pan-left li.cat1 li.cat2 { position: relative }
.pan-left li.cat1 li.cat2 div { padding: 3px 7px; color: #000000; border: 0; background: #fdfdfd; cursor: pointer; zoom: 1 }
.pan-left li.cat1 li.cat2 div.hover { color: #ffffff; background: #b00000 }
.pan-left li.cat1 li.cat2 div.selected { color: #ffffff; background: #F6F6F6 }
.pan-left li.cat1 li.cat2 ul { display: none; position: absolute; left: 202px; top: -1px; width: 200px; margin: 0; padding: 0; list-style-type: none; border: 1px solid #b00000 }
.pan-left div.cat3 li.cat3 div { padding: 3px 7px; color: #000000; border: 0; background: #fdfdfd; cursor: pointer; zoom: 1 }
.pan-right { float: right; width: 820px; margin: 0 0 0 10px }
.pan-right .pdt-list-block { display: none; margin: 0 0 10px 0 }
.pan-right .pdt-list-block .title { float: left; padding: 1px; font: bold 13px arial, sans-serif; text-decoration: underline; color: #b00000 }
.pan-right .pdt-list-block .edit { float: left; width: 16px; height: 16px; margin: 0 0 0 10px; background: url(../ressources/icons/table_edit.png) no-repeat; cursor: pointer }

.pan-left li.cat1 div.cat2{display: none; position: absolute; left: 202px; top: 0; width: 220px; margin: 0; padding: 0; border: 1px solid #b00000; max-height: 240px; overflow-y: auto}
.pan-left div.cat3{display: none; position: relative; left: 226px; top: 0; width: 218px; margin: 0; padding: 0; border: 1px solid #b00000; max-height: 240px; overflow-y: auto}
.pan-left div.cat3 li.cat3 div.hover { color: #ffffff; background: #b00000 }
.pan-left div.cat3 li.cat3 div.selected { color: #ffffff; background: #b00000 }
/* vertical product block */header-menu-bg
.pdt-vb-list {  }
.pdt-vb-list td { padding: 7px 5px 0 }
.pdt-vb { position: relative; width: 113px; height: 161px; padding: 5px; margin: 0 auto 7px; font: normal 10px arial, helvetica, sans-serif; border: 1px solid #edcece; background: #ffffff }
.pdt-vb .picture { width: 113px; height: 80px; text-align: center }
.pdt-vb .infos { width: 116px; padding: 2px 0 0 0 }
.pdt-vb .infos strong { font: bold 10px arial, helvetica, sans-serif; margin: 0; padding: 0 }
.pdt-vb .infos a { display: block; color: #0234d1; text-decoration: underline }
.pdt-vb .infos .price { padding: 5px 0; font: bold 15px arial, helvetica, sans-serif; color: #68a61d }
.pdt-vb .infos .cart-add-green { position: absolute; bottom: 3px; left: 4px }

.grey-block { clear:both; border: 1px solid #e4e4e4; background: #fcfcfc url(block-bg-grey-150.gif) repeat-x }
a.btn-red { display: block; padding: 5px; font: bold 12px arial, sans-serif; color: #ffffff; text-align: center; background: #b00000 repeat-x }
a.btn-red:hover { text-decoration: underline }
#btn-update-FO { margin: 15px 0 0; width: 188px;}
</style>
<div class="titreStandard">Sélection des produits à afficher en priorité par famille</div>
<br />
<div class="categories">
	<div class="header">
		<span class="cat1"></span>
		<span class="cat2"></span>
		<span class="cat3"></span>
	</div>
	<div class="pan-left">
		<ul class="cat1 fl">
		<?php foreach($cat1List as $cat1) { $cat2List = $xPath->query("child::category", $cat1) ?>
			<li class="cat1">
				<div id="K<?php echo $cat1->getAttribute("id") ?>"><?php echo $cat1->getAttribute("name") ?></div>
                                <div class="cat2">
                                  <ul>
                                  <?php foreach($cat2List as $cat2) { $cat3List = $xPath->query("child::category", $cat2) ?>
                                          <li class="cat2">
                                                  <div id="K<?php echo $cat2->getAttribute("id") ?>"><?php echo $cat2->getAttribute("name") ?></div>
                                                
                                                  <ul class="cat3">
                                                  <?php foreach($cat3List as $cat3) { ?>
                                                          <li class="cat3" id="#<?php echo $cat3->getAttribute("id") ?>">
                                                                  <div id="K<?php echo $cat3->getAttribute("id") ?>"><?php echo $cat3->getAttribute("name") ?></div>
                                                          </li>
                                                  <?php } ?>
                                                  </ul>
                                          </li>
                                  <?php } ?>
                                  </ul>
                                </div>
			</li>
		<?php } ?>
		</ul>
          <div class="cat3 fl"></div>
          <div class="zero"></div>
		<a href="#" class="btn-red" id="btn-update-FO">Mettre à jour le FO</a>
	</div>
	<div class="pan-right">
		<div id="pdt_flagship" class="pdt-list-block">
			<div class="title">Produits phares</div><div class="edit"></div><div class="zero"></div>
			<input type="hidden"/>
		</div>
		<div id="pdt_pic" class="pdt-list-block">
			<div class="title">Images produits</div><div class="edit"></div><div class="zero"></div>
			<input type="hidden"/>
		</div>
		<div id="pdt_selection" class="pdt-list-block">
			<div class="title">Notre sélection de produits</div><div class="edit"></div><div class="zero"></div>
			<input type="hidden"/>
		</div>
		<div id="pdt_favourite" class="pdt-list-block">
			<div class="title">Vos produits préférés</div><div class="edit"></div><div class="zero"></div>
			<input type="hidden"/>
		</div>
		<div id="pdt_mostviewed" class="pdt-list-block">
			<div class="title">Les produits les plus consultés</div><div class="edit"></div><div class="zero"></div>
			<input type="hidden"/>
		</div>
		<div id="pdt_latest" class="pdt-list-block">
			<div class="title">Nos nouveautés</div><div class="edit"></div><div class="zero"></div>
			<input type="hidden"/>
		</div>
	</div>
	<div class="zero"></div>
</div>
<div id="MPSDB"></div>
<?php
require(ADMIN."tail.php");
?>
