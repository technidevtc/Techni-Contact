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
	if (!HN.TC.BO.ISbC) HN.TC.BO.ISbC = {}; // Item Selected by Category
	
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
	HN.TC.BO.ISbC.MPSDB.setValidFct(function() { HN.TC.BO.ISbC.setASI(HN.TC.BO.ISbC.MISM.GetSelectedItems(), function(){ HN.TC.BO.ISbC.getASI(); }); });
	//HN.TC.BO.ISbC.MPSDB.setShadow(true);
	HN.TC.BO.ISbC.MPSDB.Build();
	
	var $categories = $("div.categories");
	$categories.find(".pdt-list-block .edit").click(function(){
		HN.TC.BO.ISbC.MISM.SetSelectedItems($(this).nextAll("input").val());
		HN.TC.BO.ISbC.MPSDB.Show();
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
				a1.href = pdt_infos.admin_url;
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
				var catIDpdtID = document.createElement("div");
				catIDpdtID.className = "catIDpdtID";
				catIDpdtID.innerHTML = pdt_infos.catID+","+pdt_infos.id;
				infos.appendChild(catIDpdtID);
				
			
			var toleft = document.createElement("div");
			toleft.className = "to-left";
			toleft.onclick = function(){
				var $tds = $(this).parents("tbody").find("td");
				var $td = $(this).parents("td");
				var index = $tds.index($td);
				if (index > 0) {
					var $td_tmp = $(document.createElement("td"));
					$td.after($td_tmp);
					$tds.eq(index-1).before($td);
					$td_tmp.before($tds.eq(index-1)).remove();
					var ssi = "";
					$("#pdt_flagship table div.catIDpdtID").each(function(){ ssi += (ssi==""?"":"|")+this.innerHTML; });
					HN.TC.BO.ISbC.setASI(ssi);
				}
			};
			pdt_vb.appendChild(toleft);

			var toright = document.createElement("div");
			toright.className = "to-right";
			toright.onclick = function(){
				var $tds = $(this).parents("tbody").find("td");
				var $td = $(this).parents("td");
				var index = $tds.index($td);
				if (index < $tds.length-1) {
					var $td_tmp = $(document.createElement("td"));
					$td.before($td_tmp);
					$tds.eq(index+1).after($td);
					$td_tmp.after($tds.eq(index+1)).remove();
					var ssi = "";
					$("#pdt_flagship table div.catIDpdtID").each(function(){ ssi += (ssi==""?"":"|")+this.innerHTML; });
					HN.TC.BO.ISbC.setASI(ssi);
				}
			};
			pdt_vb.appendChild(toright);
			
		return td;
	}
	
	HN.TC.BO.ISbC.setASI = function (_asic) {
		var ssi = "";
		var asic = _asic.split("|");
		for (var i = 0 ; i < asic.length; i++) {
			var asi = asic[i].split(",");
			if (asi[0] != "" && asi.length > 1) {
				for (var j = 1; j < asi.length; j++)
					ssi += (ssi==""?"":"|")+asi[0]+","+asi[j];
			}
		}
		
		var cbf;
		if (arguments.length > 1)
			cbf = arguments[1];
		$.ajax({
			async: true,
			cache: false,
			data: "action=set&ssi="+ssi,
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
			url: "AJAX_products-flagship.php"
		});
	};
	
	HN.TC.BO.ISbC.getASI = function () { // Get Array of Selected Items by Categories and Type
		var cbf;
		if (arguments.length > 1)
			cbf = arguments[1];
		$.ajax({
			async: true,
			cache: false,
			data: "action=get",
			dataType: "json",
			error: function (XMLHttpRequest, textStatus, errorThrown) { alert("Fatal error while loading Products Data"); },
			success: function (data, textStatus) {
				$("div.categories div.pdt-list-block table").remove();
				$("div.categories div.pdt-list-block input").val("");
				if (data.length != 0) {
					var ibcls = "";
					var table = document.createElement("table");
					table.className = "pdt-vb-list grey-block";
					var tbody = document.createElement("tbody");
					table.appendChild(tbody);
					var tr = [], tri = 0, ic = 0;
					for (var i = 0; i < data.length; i++) {
						ibcls += (i>0?"|":"")+data[i]["catID"];
						if (ic%6 == 0) tr[tri] = document.createElement("tr");
						tr[tri].appendChild(HN.TC.BO.ISbC.createPdtVb(data[i]));
						ibcls += ","+data[i].id;
						if (ic%6 == 5) tri++;
						ic++;
					}
					for (tri=0; tri<tr.length; tri++)
						tbody.appendChild(tr[tri]);
					$("#pdt_flagship input").val(ibcls);
					$("#pdt_flagship").append(table);
				}
				if (cbf) cbf(); // Optional CallBack Function
			},
			timeout: 10000,
			type: "GET",
			url: "AJAX_products-flagship.php"
		});
	};
	
	HN.TC.BO.ISbC.getASI();
	
});
</script>
<style type="text/css">
/*#MPSDBShad { z-index: 3; position: absolute; top: 135px; left: 255px; width: 724px; min-height: 244px; visibility: hidden; background: #000000; filter: Alpha (opacity=50, finishopacity=50, style=1) -moz-opacity:.50; opacity:.50; }*/
#MPSDB { z-index: 4; position: absolute; top: 130px; left: 250px; width: 720px; min-height: 240px; visibility: hidden; border: 2px solid #205683 }
.categories { font: normal 12px arial, helvetica, sans-serif; margin: 5px; padding: 5px; border: 1px solid #cccccc; background: #fdfdfd }
.categories img { border: 0 }
.pdt-list-block { margin: 0 0 10px 0 }
.pdt-list-block .title { float: left; padding: 1px; font: bold 13px arial, sans-serif; text-decoration: underline; color: #b00000 }
.pdt-list-block .edit { float: left; width: 16px; height: 16px; margin: 0 0 0 10px; background: url(../ressources/icons/table_edit.png) no-repeat; cursor: pointer }

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
.pdt-vb .infos .catIDpdtID { display: none }
.pdt-vb .to-left { position: absolute; left: 3px; bottom: 2px; width: 16px; height: 16px; background: url(to-left.png) no-repeat; cursor: pointer }
.pdt-vb .to-right { position: absolute; right: 3px; bottom: 2px; width: 16px; height: 16px; background: url(to-right.png) no-repeat; cursor: pointer }

.grey-block { clear:both; border: 1px solid #e4e4e4; background: #fcfcfc url(block-bg-grey-150.gif) repeat-x }
</style>
<div class="titreStandard">Sélection des produits à afficher en priorité par famille</div>
<br />
<div class="categories">
	<div id="pdt_flagship" class="pdt-list-block">
		<div class="title">Produits phares</div><div class="edit"></div><div class="zero"></div>
		<input type="hidden"/>
	</div>
</div>
<div id="MPSDB"></div>
<?php
require(ADMIN."tail.php");
?>
