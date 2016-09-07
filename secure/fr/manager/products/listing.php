<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
require(ADMIN."products.php");

include LANG_LOCAL_INC . "includes-" . DB_LANGUAGE . "_local.php";
include LANG_LOCAL_INC . "www-" . DB_LANGUAGE . "_local.php";
//include LANG_LOCAL_INC . "common-" . DB_LANGUAGE . "_local.php";
//include LANG_LOCAL_INC . "infos-" . DB_LANGUAGE . "_local.php";

$title = "Base de données des produits";
$navBar = "Base de données des produits";
require(ADMIN."head.php");

$db = DBHandle::get_instance();

define("MAX_RESULTS", 1000);

// Filter vars
$page       = isset($_GET["page"])       ? (int)(trim($_GET["page"])) : 1;
$lastpage   = isset($_GET["lastpage"])   ? (int)(trim($_GET["lastpage"])) : 1;
$sort       = isset($_GET["sort"])       ? trim($_GET["sort"]) : "";
$lastsort   = isset($_GET["lastsort"])   ? trim($_GET["lastsort"]) : "";
$sortway    = isset($_GET["sortway"])    ? trim($_GET["sortway"]) : "";
$deleteList = isset($_GET["deleteList"]) ? explode("|",trim($_GET["deleteList"])) : array();
$activeList = isset($_GET["activeList"]) ? explode("|",trim($_GET["activeList"])) : array();

// Processing products deletion
foreach($deleteList as $pdtID) {
	$pdtID = (int)$pdtID;
	delProduct($handle, $pdtID, $pdtID, $user->id);
}

// Processing products activation
foreach($activeList as $activeElem) {
	list($pdtID, $active) = explode(",",$activeElem);
	$pdtID = (int)$pdtID;
	$active = ((int)$active) > 0 ? 1 : 0;
	$db->query("UPDATE products_fr SET active = ".$active." WHERE id = ".$pdtID, __FILE__, __LINE__);
}

if ($page < 1) $page = 1;
if ($lastpage < 1) $lastpage = 1;

define("NB", 20);
if (($page-1) * NB >= $pdtList["count"]) $page = ($pdtList["count"] - $pdtList["count"]%NB) / NB + 1;
if (($lastpage-1) * NB >= $pdtList["count"]) $lastpage = ($pdtList["count"] - $pdtList["count"]%NB) / NB + 1;

if ($sort == $lastsort && $sort != '') {
	if ($lastpage == $page) $sortway = ($sortway == 'asc' ? 'desc' : 'asc');
	else $sortway = ($sortway == 'asc' ? 'asc' : 'desc');
}
else $sortway = 'asc';

$sortway_const = $sortway == "asc" ? SORT_ASC : SORT_DESC;
$sortwayi_const = $sortway_const == SORT_ASC ? SORT_DESC : SORT_ASC;

// Product list var
$pdtList = array("data_row" => array(), "data_col" => array(), "count" => 0, "start_time" => microtime(true), "end_time" => 0);
$pdtList["start_time"] = microtime(true);
$res = $db->query("
	SELECT
		p.id, p.price as pdt_price, p.timestamp,
		pfr.name, pfr.ref_name, pfr.fastdesc, pfr.active,
		pf.idFamily as catID,
		ps.hits, ps.orders, ps.leads,
		rc.id as ref_idtc, rc.refSupplier as ref_refSupplier, rc.price as ref_price
	FROM products p
	INNER JOIN products_stats ps ON p.id = ps.id
	INNER JOIN products_fr pfr ON p.id = pfr.id
	INNER JOIN products_families pf ON p.id = pf.idProduct
	LEFT JOIN
		references_content rc ON p.id = rc.idProduct AND rc.classement = 1
	GROUP BY p.id
	ORDER BY p.timestamp DESC limit 0,1000");
$pdtList["end_time"] = microtime(true);

$hits2leads_w = 0.2;	// weight of products leads
$hits2orders_w = 0.8;	// weight of products orders

while ($pdt = $db->fetchAssoc($res)) {
	$pdt["hits2leads"] = $pdt["hits"] > 0 ? $pdt["leads"] / $pdt["hits"] : 0;
	$pdt["hits2orders"] = $pdt["hits"] > 0 ? $pdt["orders"] / $pdt["hits"] : 0;
	$pdt["transfo"] = round(($pdt["hits2leads"] + $pdt["hits2orders"])*100, 2);
	
	if ($pdt["pdt_price"] == "ref")
		$pdt["price"] = $pdt["ref_price"];
	else
		$pdt["price"] = $pdt["pdt_price"];

	if (!preg_match('/^[0-9]+((\.|,)[0-9]+){0,1}$/', $pdt["price"]))
		$pdt["price"] = -1;

	$pdtList["data_row"][] = $pdt;
	foreach ($pdt as $k => $v)
		$pdtList["data_col"][$k][] = $v;
	
	$pdtList["count"]++;
	if ($pdtList["count"] >= MAX_RESULTS) break;
}

if ($pdtList["count"] > 0) {
	
	switch ($sort) {
		case "name"      : array_multisort($pdtList["data_col"]["name"], $sortway_const, $pdtList["data_col"]["timestamp"], SORT_DESC, $pdtList["data_row"]); break;
		case "fastdesc"  : array_multisort($pdtList["data_col"]["fastdesc"], $sortway_const, $pdtList["data_col"]["timestamp"], SORT_DESC, $pdtList["data_row"]); break;
		case "price"     : array_multisort($pdtList["data_col"]["price"], $sortway_const, $pdtList["data_col"]["timestamp"], SORT_DESC, $pdtList["data_row"]); break;
		case "timestamp" : array_multisort($pdtList["data_col"]["timestamp"], $sortwayi_const, SORT_NUMERIC, $pdtList["data_col"]["name"], SORT_ASC, $pdtList["data_row"]); break;
		case "hits" : array_multisort($pdtList["data_col"]["hits"], $sortwayi_const, SORT_NUMERIC, $pdtList["data_col"]["name"], SORT_ASC, $pdtList["data_row"]); break;
		case "leads" : array_multisort($pdtList["data_col"]["leads"], $sortwayi_const, SORT_NUMERIC, $pdtList["data_col"]["name"], SORT_ASC, $pdtList["data_row"]); break;
		case "orders" : array_multisort($pdtList["data_col"]["orders"], $sortwayi_const, SORT_NUMERIC, $pdtList["data_col"]["name"], SORT_ASC, $pdtList["data_row"]); break;
		case "transfo" : array_multisort($pdtList["data_col"]["transfo"], $sortwayi_const, SORT_NUMERIC, $pdtList["data_col"]["name"], SORT_ASC, $pdtList["data_row"]); break;
		default : array_multisort($pdtList["data_col"]["timestamp"], $sortwayi_const, SORT_NUMERIC, $pdtList["data_col"]["name"], SORT_ASC, $pdtList["data_row"]); break;
	}
	$lastsort = $sort;
	$lastpage = $page;
}

?>

<script type="text/javascript">
$(function(){
	$pdtList = $("form[name='pdtList']");
	$("#delete-pdtList").click(function(){
		var deleteList = [];
		$("table.php-list input[name='delete']:checked").each(function(){
			deleteList.push($(this).parent().parent().find("input[name='pdtID']").val());
		});
		$pdtList.find("input[name='deleteList']").val(deleteList.join("|"));
		$pdtList.submit();
	});
	$("#active-pdtList").click(function(){
		var activeList = [];
		$("table.php-list input[name='active']").each(function(){
			var active = $(this).attr("checked")?1:0;
			var $tr = $(this).parent().parent()
			var pdtID = parseInt($tr.find("input[name='pdtID']").val());
			var activated = parseInt($tr.find("input[name='activated']").val())?1:0;
			if (active ^ activated) // XOR bitwise - > 1 if state changed, 0 if not
				activeList.push(pdtID+","+active);
		});
		$pdtList.find("input[name='activeList']").val(activeList.join("|"));
		$pdtList.submit();
	});
});
</script>
<div class="titreStandard">Liste des produits de l'annonceur</div>
<br />
<div class="bg" style="background: #f8f6f6">
	<div id="search-results">
		
		<form name="pdtList" method="get" action="index.php">
			<div>
				<input type="hidden" name="page" value="<?php echo $page ?>" />
				<input type="hidden" name="lastpage" value="<?php echo $lastpage ?>" />
				<input type="hidden" name="sort" value="<?php echo $sort ?>" />
				<input type="hidden" name="lastsort" value="<?php echo $lastsort ?>" />
				<input type="hidden" name="sortway" value="<?php echo $sortway ?>" />
				<input type="hidden" name="deleteList" value="" />
				<input type="hidden" name="activeList" value="" />
			</div>
		</form>
		<table class="php-list" cellspacing="0" cellpadding="0">
			<thead>
				<tr>
					<th style="width: 8%">Image</th>
					<th style="width: 18%"><a href="javascript: document.pdtList.sort.value = 'name'; document.pdtList.submit();">Nom</a></th>
					<th style="width: 18%"><a href="javascript: document.pdtList.sort.value = 'fastdesc'; document.pdtList.submit();">Description rapide</a></th>
					<th style="width: 10%">Réf. Four. 1</th>
					<th style="width: 7%"><a href="javascript: document.pdtList.sort.value = 'price'; document.pdtList.submit();">Prix</a></th>
					<th style="width: 10%"><a href="javascript: document.pdtList.sort.value = 'timestamp'; document.pdtList.submit();">Date dernière mise à jour/création</a></th>
					<th style="width: 5%"><a href="javascript: document.pdtList.sort.value = 'hits'; document.pdtList.submit();">Nombre de vues total</a></th>
					<th style="width: 5%"><a href="javascript: document.pdtList.sort.value = 'leads'; document.pdtList.submit();">Nombre de lead</a></th>
					<th style="width: 5%"><a href="javascript: document.pdtList.sort.value = 'orders'; document.pdtList.submit();">Nombre de commandes</a></th>
					<th style="width: 5%"><a href="javascript: document.pdtList.sort.value = 'transfo'; document.pdtList.submit();">Taux de transfo</a></th>
					<th style="width: 3%">Actif</th>
					<th style="width: 3%">Supprimer</th>
					<th style="width: 3%"></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach($pdtList["data_row"] as $pdt) {
							$fo_pdt_url = URL."produits/".$pdt["idFamily"]."-".$pdt["id"]."-".$pdt["ref_name"].".html";
							$bo_pdt_url = ADMIN_URL."products/edit.php?id=".$pdt["id"] ?>
				<tr>
					<td><a href="<?php echo $bo_pdt_url ?>" title="Voir la fiche produit"><img src="<?php echo PRODUCTS_IMAGE_SECURE_URL."thumb_small/".$pdt["id"]."-1.jpg" ?>" alt=""></a></td>
					<td class="title"><input type="hidden" name="pdtID" value="<?php echo $pdt["id"] ?>"/><a href="<?php echo $bo_pdt_url ?>"><?php echo $pdt["name"] ?></a></td>
					<td><?php echo $pdt["fastdesc"]?></td>
					<td>
						<?php if (!empty($pdt["ref_idtc"])) { ?>
							<?php echo $pdt["ref_refSupplier"] ?>
						<?php } else { ?>
							N.A.
						<?php } ?>
					</td>
					<td>
						<?php if ($pdt["price"] == -1) { ?>
							sur devis
						<?php } else { ?>
							<?php echo sprintf("%.2f",$pdt["price"])."€ HT" ?>
						<?php } ?>
					</td>
					<td><?php echo date("Y/m/d h:i:s",$pdt["timestamp"]) ?></td>
					<td><?php echo $pdt["hits"]?></td>
					<td><?php echo $pdt["leads"]?></td>
					<td><?php echo $pdt["orders"]?></td>
					<td><?php echo $pdt["transfo"]?>%</td>
					<td><input type="hidden" name="activated" value="<?php echo $pdt["active"] ?>"/><input type="checkbox" name="active"<?php if($pdt["active"]) { ?> checked="checked"<?php } ?>/></td>
					<td><input name="delete" type="checkbox" value=""/></td>
					<td><a href="<?php echo $fo_pdt_url ?>" target="_blank"><img src="<?php echo ADMIN_URL ?>ressources/icons/firefox_icon_25x25.png" alt="" title="Voir la fiche en ligne"></a></td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
		<input id="delete-pdtList" type="button" value="Supprimer les produits sélectionnés" />
		<input id="active-pdtList" type="button" value="Activer/Désactiver les produits" />
	</div>
</div>
<?php
require(ADMIN . 'tail.php');
?>
