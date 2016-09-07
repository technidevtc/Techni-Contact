<?php

/*================================================================/

 Techni-Contact V2 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de cr�ation : 15 juillet 2005

 Fichier : /includes/extranet/head2.php4
 Description : Fichier g�n�rique ent�te extranet

/=================================================================*/

if ($user->parent == '61049')
	$rub = array(
    WHERE_INDEX => 'index',
    WHERE_COMMANDS => 'commandes',
    WHERE_PRODUCTS_CARD => 'products',
    WHERE_INFOS => 'infos',
    WHERE_INVOICING => 'invoicing',
    WHERE_INVOICES_LIST => 'invoices_list'
  );
elseif ($user->category == __ADV_CAT_SUPPLIER__)
  $rub = array(
    WHERE_INDEX => 'index',
    WHERE_CONTACT => 'requests',
    WHERE_PRODUCTS_CARD => 'products',
    WHERE_STATS => 'stats',
    WHERE_INFOS => 'infos'
  );
else
  $rub = array(
    WHERE_INDEX => 'index',
    WHERE_CONTACT => 'requests',
    WHERE_PRODUCTS_CARD => 'products',
    WHERE_STATS => 'stats',
    WHERE_INFOS => 'infos',
    WHERE_INVOICING => 'invoicing',
    WHERE_INVOICES_LIST => 'invoices_list',
    WHERE_INVOICES => 'invoices'
  );

// getting Doctrine Class specific constants and static vars using Reflection, for passing them to js
$includes = get_included_files();
$js_vars = array();
$js_ns = "HN.TC."; // Javascript Base Namespace
foreach($includes as $include) {
  if (preg_match('`^'.DOCTRINE_MODEL_PATH.'\b(\w+)(?<!Table)\b\.php`i', $include, $matches)) {
    if (isset($reflectable_classes[$matches[1]])) {
      $refl = new ReflectionClass($matches[1]);
      $r_consts = $refl->getConstants();
      $r_static_vars = $refl->getStaticProperties();
      $js_vars[$js_ns.$matches[1]] = $js_ns.$matches[1]." || {}";
      foreach ($r_consts as $k => $v)
        if (!preg_match('/^STATE_[A-Z]+$/', $k)) // filter out Doctrine Record States
          $js_vars[$js_ns.$matches[1].'.'.$k] = $v;
      foreach ($r_static_vars as $k => $v)
        if (is_array($v) && !preg_match('/^_+/', $k)) // filter out Doctrine Static Vars
          $js_vars[$js_ns.$matches[1].'.'.$k] = json_encode($v);
    }
  }
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title><?php echo TITLE ?> - <?php print($title) ?></title>
<link href="<?php echo EXTRANET_URL ?>ressources/css/smoothness/jquery-ui-1.8.23.custom.css" rel="stylesheet" type="text/css">
<link href="<?php echo EXTRANET_URL ?>ressources/css/extranet.css" rel="stylesheet" type="text/css">

<!--
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1/jquery-ui.min.js"></script>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.7.3/i18n/ui.datepicker-fr.min.js"></script>
-->

	<script type="text/javascript" src="<?php echo EXTRANET_URL ?>ressources/js/jquery/jquery_1.7.2.min.js"></script>
<!--
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1/jquery-ui.min.js"></script>
-->	
	<script type="text/javascript" src="<?php echo EXTRANET_URL ?>ressources/js/jqueryui/1/jquery-ui.min.js"></script>

	<script type="text/javascript" src="<?php echo EXTRANET_URL ?>ressources/js/jqueryui/ui.datepicker-fr.min.js"></script>
	

<script type="text/javascript" src="<?php echo EXTRANET_URL ?>ManageCookies.js"></script>
<script type="text/javascript" src="<?php echo EXTRANET_URL ?>ressources/js/HN.TC.js?_=<?php echo time() ?>"></script>
<script type="text/javascript">
  $.extend(HN.TC, {
    URL: "<?php echo URL ?>",
    SECURE_URL: "<?php echo SECURE_URL ?>",
    EXTRANET_URL: "<?php echo EXTRANET_URL ?>",
    PDF_URL: "<?php echo PDF_URL ?>",
    PDF_URL_ARC: '<?php echo PDF_URL_ARC ?>',
    PRODUCTS_IMAGE_SECURE_URL: "<?php echo PRODUCTS_IMAGE_SECURE_URL ?>"
  });
 <?php foreach ($js_vars as $k => $v) { ?>
   <?php echo $k." = ".$v ?>;
 <?php } ?>
  function SetLanguage(lang) {
    var language_local = ReadCookie('language_local');
    if (language_local != lang) {
      var date = new Date;
      date.setFullYear(date.getFullYear()+1);
      WriteCookie('language_local', lang, date, "/", "techni-contact.com");
    }
  }
</script>
</head>
<body>
<div class="haut">
	<div class="lang">
<?php foreach($language_list as $lang => $lang_name) if ($lang != $language_local && $lang_name != '') { ?>
		<a href="<?php echo $_SERVER['REQUEST_URI'] ?>" onclick="SetLanguage('<?php echo $lang ?>')"><img src="<?php echo SECURE_RESSOURCES_URL."images/flags/".$lang ?>.gif" alt="<?php echo $lang_name ?>" width="24" height="15" border="0"/></a>
<?php } ?>

	</div>
	<div class="logo-tc"></div>
	<div class="menu">
<?php foreach($rub as $k => $v) { ?>
	<?php if (($k != WHERE_INVOICING) || ($user->ic_active && $user->ic_extranet)) { ?>
		<div class="menu-<?php echo ($k==WHERE?"current":"autre") ?>"><a href="<?php echo EXTRANET_URL.$v.".html?".$sid ?>"><?php echo $k ?></a></div>
	<?php } ?>
<?php } ?>


	</div>
	<div class="miseAZero"></div>
</div>

