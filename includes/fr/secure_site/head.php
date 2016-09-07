<?php
require_once(ICLASS . 'CUserSession.php');
require_once(ICLASS . 'CCart.php');

if (!isset($handle)) { $handle = DBHandle::get_instance(); }
if (!isset($session)) { $session = & new UserSession($handle); }
if (!isset($cart)) { $cart = & new Cart($handle, $session->getID()); }

$menu_families = '';
$top_family_list = array();
$result = & $handle->query("select fr.id, fr.name, fr.ref_name from families f, families_fr fr where f.idParent = 0 and f.id = fr.id order by fr.id", __FILE__, __LINE__);
while ($row = & $handle->fetchAssoc($result)) $top_family_list[] = & $row;
if(!defined("__FAMILIES_DYNAMIC__") && !defined("__FAMILIES_FIXED__")) {
	foreach($top_family_list as $top_fam) {
		$menu_families .= '<a href="';
		switch ($top_fam['ref_name']) {
			/*case 'maintenance' :
				$menu_families .= 'http://' . $top_fam['ref_name'] . '.techni-contact.com/">';
				break;*/
			default :
				$menu_families .= URL . 'familles/' . $top_fam['ref_name'] . '.html">';
		}
		$menu_families .= htmlentities($top_fam['name']) . '</a> ';
	}
}
else {
	foreach($top_family_list as $top_fam)
		if ($top_fam['id'] == $top_family['id']) $menu_families .= '<span class="select">' . htmlentities($top_fam['name']) . '</span> ';
		else {
			$menu_families .= '<a href="';
			switch ($top_fam['ref_name']) {
				/*case 'maintenance' :
					$menu_families .= 'http://' . $top_fam['ref_name'] . '.techni-contact.com/">';
					break;*/
				default :
					$menu_families .= URL . 'familles/' . $top_fam['ref_name'] . '.html">';
			}
			$menu_families .= htmlentities($top_fam['name']) . '</a> ';
		}
}

if (!isset($slogan)) $slogan = HEAD_SLOGAN;
if (!isset($title)) $title = HEAD_TITLE;
if (!isset($meta_desc)) $meta_desc = HEAD_META_DESC;
if (!isset($meta_keys)) $meta_keys = HEAD_META_KEYS;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title><?php echo $title ?></title>
<?php	if (TEST) { ?>
	<meta name="robots" content="noindex,nofollow"/>
<?php	} ?>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<meta name="description" content="<?php echo $meta_desc ?>" />
	<meta name="keywords" content="<?php echo $meta_keys ?>" />
  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
	<script type="text/javascript" src="<?php echo URL ?>ressources/scripts/AJAX_search.js"></script>
	<link href="<?php echo SECURE_RESSOURCES_URL ?>styles.css" rel="stylesheet" type="text/css" />
	<link href="<?php echo SECURE_RESSOURCES_URL ?>forms.css" rel="stylesheet" type="text/css" />
</head>
<body>
<?php	if (TEST) include MAINTENANCE_PATH . "head.php" ?>
<center>
<div id="main">
	<div class="in">
		<div id="hd">
			<div class="jn">
				<div class="hn">
					<div class="logo"><a href="<?php echo URL ?>"><img src="<?php echo SECURE_RESSOURCES_URL ?>logo.gif" alt="technicontact" border="0" /></a></div>
					<div class="slogan"><?php echo $slogan ?></div>
					<div class="lang">
<?php
foreach($language_list as $lang => $data)
	if ($lang != DB_LANGUAGE) print '						<a href="' . $data['domain'] . '.techni-contact.com"><img src="' . SECURE_RESSOURCES_URL . $lang . '.gif" alt="' . $data['name'] . '" width="24" height="15" border="0"/></a>' . "\n";
?>
					</div>
					<img class="man" src="<?php echo SECURE_RESSOURCES_URL ?>homme.gif" alt="<?php echo HEAD_MAN ?>" border="0"/>
<?php
if (!defined('__PANIER__')) {
	$nb_items = $cart->itemCount;
?>	
					<div class="panier">
						<a href="<?php echo URL ?>panier.html"><img src="<?php echo SECURE_RESSOURCES_URL ?>panier.gif" alt="" /></a>
						<?php if ($nb_items == 0) print '<div class="vide"><a href="' . URL . 'panier.html">' . HEAD_YOUR_CARD_IS_EMPTY . '</a></div>';
						elseif ($nb_items == 1) print '<div class="rempli"><a href="' . URL . 'panier.html">' . HEAD_YOUR_CARD_IS . HEAD_1_ITEM . '</a></div>';
						elseif ($nb_items >= 2) print '<div class="rempli"><a href="' . URL . 'panier.html">' . HEAD_YOUR_CARD_IS . $nb_items . ' ' . HEAD_N_ITEMS . '</a></div>';
						?></a>
					</div>
<?php
}
?>
					<div class="zero"></div>
				</div>
			</div>
		</div>
		<div id="menu">
		<?php echo $menu_families . "\n" ?>
		</div>
<?php
	if (!defined("__PANIER__") && !defined("__COMMAND_STEP__") && !defined("__ACCOUNT_COMMAND__")) {
?>
		<div id="colg">
			<div class="lien"><a href="<?php echo URL ?>nouveaux_visiteurs.html">Nouveaux Visiteurs</a></div>
			<div class="titre"><?php echo HEAD_SEARCH ?></div>
			<form action="<?php echo URL ?>rechercher.html" method="post" class="search">
				<input type="text" name="search" id="search-text" class="text" value="" />
				<input type="submit" name="Submit" value="&nbsp;" class="ok" onmousedown="this.style.backgroundImage='url(<?php echo SECURE_RESSOURCES_URL ?>inputOkOn.gif)'" onmouseup, onmouseout="this.style.backgroundImage='url(<?php echo SECURE_RESSOURCES_URL ?>inputOk.gif)'" /><br />
			</form>
<?php
if (defined("__FAMILIES_DYNAMIC__")) {
?>
			<div class="ghome">
				<div class="lien"><a href="<?php echo COMPTE_URL ?>compte.html"><?php echo HEAD_MY_ACCOUNT ?></a></div>
			</div>
<?php
	function print_family_menu($family, $depth, & $tag_opened) {
		switch ($depth) {
			case 1:
				print '			<div class="titre">' . htmlentities($family['name']) . "</div>\n";
				break;
			
			case 2:
				if ($tag_opened[2]) print "			</div>\n"; else $tag_opened[2] = true;
				print '			<div class="sf" id="folder' . $family['id'] . '_f"><a href="javascript: unfold(' . $family['id'] . ');">' . htmlentities($family['name']) . "</a></div>\n";
				print '			<div class="sfn" id="folder' . $family['id'] . '_u"><strong style="cursor: pointer" onclick="javascript: foldall();">' . htmlentities($family['name']) . "</strong>\n";
				break;
				
			case 3:
				print ' <a href="' . FAM_URL . htmlentities($family['ref_name']) . '.html">' . htmlentities($family['name']) . "</a>";
				break;
			
			default: break;
		}
		
		if (isset($family['child'])) {
			$depth++;
			if ($depth == 3) print '				<div class="ssf">';
			
			foreach($family['child'] as $sub_family) print_family_menu($sub_family, $depth, $tag_opened);
			
			if ($depth == 2) { print "			</div>\n"; $tag_opened[2] = false; }
			elseif ($depth == 3) print "</div>";
		}
	}
	
	$tag_opened = array(1 => false, 2 => false, 3 => false);
	print_family_menu($top_family, 1, $tag_opened);
?>
<script type="text/javascript" src="<?php echo SECURE_RESSOURCES_URL ?>scripts.js"></script>
<script type="text/javascript">
<!--
var folder_list = new Array();
<?php
	foreach($top_family['child'] as $fam) print "folder_list[{$fam['id']}] = '" . htmlentities($fam['name'], ENT_QUOTES) . "';\n";
?>

function unfold(id)
{
	var k = 0;
	var id_prev = 0;
	for (var i in folder_list)
	{
		if (id == i)
		{
			document.getElementById('folder'+i+'_u').style.display = 'block';
			document.getElementById('folder'+i+'_f').style.display = 'none';
			document.getElementById('folder'+i+'_s').style.display = 'block';
			document.getElementById('folder'+i+'_s').parentNode.className = 'elt';
			document.getElementById('folder'+i+'_s').parentNode.style.display = 'block';
		}
		else
		{
			document.getElementById('folder'+i+'_u').style.display = 'none';
			document.getElementById('folder'+i+'_f').style.display = 'block';
			document.getElementById('folder'+i+'_s').style.display = 'none';
			if (k%2 != 1 || id_prev != id) document.getElementById('folder'+i+'_s').parentNode.style.display = 'none';
		}
		id_prev = i;
		k++;
	}
	document.getElementById('navig').innerHTML = '<a href="<?php echo FAM_URL . htmlentities($top_family['ref_name']) . '.html' ?>"><?php echo htmlentities($top_family['name']) ?></a> &raquo; <h1>' + folder_list[id] + '</h1>';
}

function foldall()
{
	var k = 0;
	for (var i in folder_list)
	{
		document.getElementById('folder'+i+'_u').style.display = 'none';
		document.getElementById('folder'+i+'_f').style.display = 'block';
		document.getElementById('folder'+i+'_s').style.display = 'block';
		if (k%2 == 0) document.getElementById('folder'+i+'_s').parentNode.style.display = 'block';
		if (k%4 == 2) document.getElementById('folder'+i+'_s').parentNode.className = 'eltDeux';
		k++;
	}
	document.getElementById('navig').innerHTML = "<h1><?php echo HEAD_AVAILABLE_CATEGORIES ?> &quot;<?php echo htmlentities($top_family['name']) ?>&quot; : </h1>";
}

//-->
</script>
<?php
}
elseif(defined("__FAMILIES_FIXED__")) {
?>
			<div class="ghome">
				<div class="lien"><a href="<?php echo COMPTE_URL ?>compte.html"><?php echo HEAD_MY_ACCOUNT ?></a></div>
			</div>
<?php
	function print_family_menu($family, $depth, & $tag_opened) {
		switch ($depth) {
			case 1:
				print '			<div class="titre">' . htmlentities($family['name']) . "</div>\n";
				break;
			
			case 2:
				if ($tag_opened[2]) print "			</div>\n"; else $tag_opened[2] = true;
				if (isset($family['current']))
				{
					print '			<div class="sf"><strong style="cursor: pointer" onclick="javascript: document.location.href=\'' . FAM_URL . htmlentities($family['ref_name']) . '.html\'">' . htmlentities($family['name']) . "</strong>\n";
				}
				else
				{
					print '			<div class="sf"><a href="' . FAM_URL . htmlentities($family['ref_name']) . '.html">' . htmlentities($family['name']) . "</a></div>\n";
					$tag_opened[2] = false;
				}
				break;
				
			case 3:
				print ' <a ' . (isset($family['current']) ? 'class="current" ' : '') . 'href="' . FAM_URL . htmlentities($family['ref_name']) . '.html" id="folder' . $family['id'] . '" onclick="swap_class(this)">' . htmlentities($family['name']) . "</a>";
				break;
			
			default: break;
		}
		
		if (isset($family['child'])) {
			$depth++;
			if ($depth == 3) print '				<div class="ssf">';
			
			foreach($family['child'] as $sub_family) print_family_menu($sub_family, $depth, $tag_opened);
			
			if ($depth == 2 && $tag_opened[2]) { print "			</div>\n"; $tag_opened[2] = false; }
			elseif ($depth == 3) print "</div>\n";
		}
	}
	
	$tag_opened = array(1 => false, 2 => false, 3 => false);
	print_family_menu($top_family, 1, $tag_opened);
?>
			<script type="text/javascript" src="<?php echo SECURE_RESSOURCES_URL ?>scripts.js"></script>
<?php
}
elseif(defined("__HOME__")) {
?>
			<div class="ghome">
				<div class="lien"><a href="<?php echo COMPTE_URL ?>compte.html"><?php echo HEAD_MY_ACCOUNT ?></a></div>
				<div class="lien"><a href="<?php echo URL ?>nous.html"><?php echo HEAD_OUR_COMPANY ?></a></div>
				<div class="lien"><a href="<?php echo URL ?>plan.html"><?php echo HEAD_GUIDED_TOUR ?></a></div>
				<div class="lien"><a href="<?php echo URL ?>devenirAnnonceur.html"><?php echo HEAD_BECOME_PARTNAIR ?></a></div>
				<div class="lien"><a href="<?php echo URL ?>recrutement.html"><?php echo HEAD_RECRUITMENT ?></a></div>
				<div class="lien"><a href="<?php echo URL ?>contact.html"><?php echo HEAD_CONTACT ?></a></div>
			</div>
			<div class="titre"><?php echo HEAD_CATEGORIES ?></div>
			<div class="sf"><?php echo $menu_families ?></div>
<?php
}
else {
?>
			<div class="ghome">
				<div class="lien"><a href="<?php echo COMPTE_URL ?>compte.html"><?php echo HEAD_MY_ACCOUNT ?></a></div>
			</div>
<?php
}
?>
			<div class="gif_one">
				<!--<a href="<?php echo URL ?>produits/2434-13067236-e-mail-video.html?xtor=ADC-19"><img src="<?php echo SECURE_RESSOURCES_URL ?>gif_one.gif" alt="Email Vidéo"/></a>-->
			</div>
		</div>
<?php
	} // end ! __PANIER__
?>