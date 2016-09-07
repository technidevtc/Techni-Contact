<?php
header("Content-type: text/html; charset: UTF-8");

if (DEBUG) $tab_microtime["page initial PHP"] = microtime(true);

require_once(ICLASS."CUserSession.php");
require_once(ICLASS."CCart.php");

$db = $handle = DBHandle::get_instance();
if (!isset($session)) { $session = new UserSession($handle); }
if (!isset($cart)) { $cart = new Cart($handle, $session->getID()); }

// gets params url from $_SERVER["REQUEST_URI"] to be independant from url rewriting
preg_match_all("/\/.*\?(.+)$/",$_SERVER["REQUEST_URI"],$params);
if (!empty($params[1][0])) {
  $params = explode("&",$params[1][0]);
  for ($i=0,$l=count($params);$i<$l;++$i) {
    $param = explode("=",$params[$i],2);
    if (preg_match("/^[a-zA-Z0-9-_]+$/",$param[0]))
      $get[$param[0]] = $param[1];
  }
}

// campaign ID
$campaignID = !empty($_COOKIE["campaignID"]) ? $_COOKIE["campaignID"] : 0;
$newCampaignID = isset($get['campaignID']) && preg_match("/^\d+$/",$get['campaignID']) ? (int)$get['campaignID'] : 0;
if (preg_match('/.+/i', $get['codesf'])) {
  $newCampaignID = 4;
} else if (preg_match('/Twenga\(via(?:\s|\+|%20)+Shopping(?:\s|\+|%20)+Flux\)/i', $get['utm_source'])) {
  $newCampaignID = 27;
}
if (!$campaignID || $campaignID == $newCampaignID)
  setCookie('campaignID', $newCampaignID, time()+86400*15, '/', DOMAIN);

// show menu
$main_categories = array();
$res = $db->query("
  SELECT fr.id, fr.name, fr.ref_name
  FROM families f
  INNER JOIN families_fr fr ON fr.id = f.id
  WHERE f.idParent = 0
  ORDER BY f.rank ASC", __FILE__, __LINE__);
while ($row = $db->fetchAssoc($res))
  $main_categories[] = $row;

$menu_categories = "";
$main_categories[] = array('id' => 99, 'name' => 'Espace Thématique', 'ref_name'=> 'espace-thematique');
$main_categories_count = count($main_categories);
foreach ($main_categories as $num_cat => $main_cat) {
  $last_cat = $num_cat == $main_categories_count-1;
	  // if ($num_cat > 0){
		  $menu_categories .= "<div class=\"separator\"></div>";
		  $cat1id = isset($cat1['id']) ? $cat1['id'] : (!empty($pdt['cat1_id']) ? $pdt['cat1_id'] : '' );
		  if(!empty($main_cat["ref_name"])){
		  $menu_categories .= ($pageName == 'home'?'<h2>':'')."<a href=\"";
		  $menu_categories .= $last_cat ? URL.$main_cat["ref_name"].".html\" data-main-cat-id=\"".$main_cat["id"]."\" " : URL."familles/".$main_cat["ref_name"].".html\" data-main-cat-id=\"".$main_cat["id"]."\" ";
		  $last_cat_class = $last_cat ? ' last' : '';
		  $menu_categories .= $cat1id == $main_cat["id"] ? ($num_cat==0?"class=\"first selected\" ":" class=\"selected".$last_cat_class."\"") : ($num_cat==0?"class=\"first\" ": "class=\"".$last_cat_class."\"");
		  $menu_categories .= ">".$main_cat["name"] . "</a>".($pageName == 'home'?'</h2>':'');
		  }
	  // }
}

$menu_categories_options = "<option value=\"\" disabled selected>Naviguer vers ...</option>";
foreach ($main_categories as $main_cat){
	if(!empty($main_cat["ref_name"])){
		$menu_categories_options .= "<option value=\"".URL.($main_cat['id']==99 ? "" : "familles/").$main_cat['ref_name'].".html\">".$main_cat['name']."</option>";
	}
}

  
  

include_once(LANG_LOCAL_INC . "meta-titles-" . DB_LANGUAGE . "_local.php");

if (!isset($title)) $title = COMMON_TITLE;
if (!isset($head_motto)) $head_motto = COMMON_HEAD_MOTTO;
if (!isset($meta_desc)) $meta_desc = COMMON_META_DESC;
//if (!isset($meta_keys)) $meta_keys = COMMON_META_KEYS;
if (!isset($motto)) $motto = COMMON_MOTTO;

if (!empty($_SERVER["HTTPS"])) $secure = true;
else $secure = false;
$protocol = $secure ? "https://" : "http://";
$res_url = ($secure ? SECURE_URL : URL)."ressources/";

/** Page detection for background selection **/
if(!empty ($pageName)){
  if($pageName == 'home' || $pageName == 'liste_categories')
    $smallBackground = false;
  else
    $smallBackground = true;
}else
  $smallBackground = false;
/** Page detection for background selection **/

/** setting commercial help message and tel **/
// personnalisé par $pdt_referent_commercial_id sur pages produit, formulaire lead (f & a), familles 3 et pages fournisseurs
if (isset($pdt_referent_commercial_id)) {
  $comm_infoTable = Doctrine_Core::getTable('BoUsers');
  $comm_info = $comm_infoTable->find($pdt_referent_commercial_id);
  
  $commercial_infos['phone'] = str_replace(' ', '.', $comm_info->phone);
  $commercial_infos['help_msg'] = $comm_info->help_msg; 
  $commercial_infos['gender'] = 'man'; //man, woman, neutral (todo)
} else {
  $commercial_infos['phone'] = '01.55.60.29.29';
  $commercial_infos['help_msg'] = '<span id="contact-crew-blue-text">Commandez par téléphone au</span><br /><br /><span id="contact-crew-grey-text">01 55 60 29 29</span>';
  //$commercial_infos['phone'] = '01.70.06.02.99';
  //$commercial_infos['help_msg'] = '<span id="contact-crew-blue-text">Commandez par téléphone au</span><br /><br /><span id="contact-crew-grey-text">01 70 06 02 99</span>';  
  $commercial_infos['gender'] = 'man';
}

// echo $_SERVER['SCRIPT_NAME'];

if(isset($_GET['pricettc'])){
	if($_GET['pricettc'] == 'true'){
		$sql_comm_phone  =  "SELECT  tel_g_shopping,phone
							 FROM    bo_users
							 WHERE   id='".$pdt_referent_commercial_id."' ";
	// echo $sql_comm_phone;
		$req_comm_phone  =  mysql_query($sql_comm_phone);
		$data_comm_phone =  mysql_fetch_object($req_comm_phone);
		if(!empty($data_comm_phone->tel_g_shopping)){
		  $comm_phone	=  $data_comm_phone->tel_g_shopping;
		  // setCookie('comm_phone', $comm_phone);
		  $_SESSION['comm_phone'] = $comm_phone; 
		  $_SESSION['comm_id'] 	  = $pdt_referent_commercial_id; 
		  $_SESSION["loggedin_time"] = time();
		}else{
			// echo 'aaaa'.$data_comm_phone->phone;
		  $_SESSION['comm_phone'] = $data_comm_phone->phone;
		  $_SESSION['comm_id'] 	  = $pdt_referent_commercial_id; 
		  $_SESSION["loggedin_time"] = time(); 
		}
	}
}
	
	if(isset($pdt_referent_commercial_id) && isset( $_SESSION['comm_id'] )){
			if($_SESSION['comm_id'] == $pdt_referent_commercial_id ){
				$sql_comm_phone  =  "SELECT  tel_g_shopping ,phone
									 FROM    bo_users
									 WHERE   id='".$pdt_referent_commercial_id."' ";
				$req_comm_phone  =  mysql_query($sql_comm_phone);
				$data_comm_phone =  mysql_fetch_object($req_comm_phone);
				
				if(!empty($data_comm_phone->tel_g_shopping)){
					$comm_phone	=  $data_comm_phone->tel_g_shopping;
					$_SESSION['comm_phone']    = $comm_phone; 
					$_SESSION['comm_id'] 	   = $pdt_referent_commercial_id; 
					$commercial_infos['phone'] = $_SESSION['comm_phone'];
					
					// $right_phone  = $comm_phone;
				}else {
					
					$commercial_infos['phone'] 	  = $data_comm_phone->phone;
					$_SESSION['comm_phone']       = $data_comm_phone->phone;
					// $commercial_infos['help_msg'] = '<span id="contact-crew-blue-text">Commandez par téléphone au</span><br /><br /><span id="contact-crew-grey-text">'.$data_comm_phone->phone.'</span>';
					// $right_phone = $data_comm_phone->phone;
				}								
			}else{
				$sql_comm_phone  =  "SELECT  tel_g_shopping ,phone
									 FROM    bo_users
									 WHERE   id='".$pdt_referent_commercial_id."' ";
				$req_comm_phone  =  mysql_query($sql_comm_phone);
				$data_comm_phone =  mysql_fetch_object($req_comm_phone);
				
				if(!empty($data_comm_phone->tel_g_shopping)){
					$comm_phone	=  $data_comm_phone->tel_g_shopping;
					$_SESSION['comm_phone']    = $comm_phone; 
					$commercial_infos['phone'] = $_SESSION['comm_phone'];
					// $_SESSION['comm_id'] 	   = $pdt_referent_commercial_id; 
					// $right_phone = $data_comm_phone->phone;
				}else {
					
					$commercial_infos['phone'] 	  = $data_comm_phone->phone;
					$_SESSION['comm_phone']       = $data_comm_phone->phone;
					// $commercial_infos['help_msg'] = '<span id="contact-crew-blue-text">Commandez par téléphone au</span><br /><br /><span id="contact-crew-grey-text">'.$data_comm_phone->phone.'</span>';
					// $right_phone = $data_comm_phone->phone;
				}
				
			}			
	}else if(!isset( $_SESSION['comm_id']) && (isset($pdt_referent_commercial_id)) ){
		$sql_comm_phone_vide  =  "SELECT  tel_g_shopping ,phone
									 FROM    bo_users
									 WHERE   id='".$pdt_referent_commercial_id."' ";
		$req_comm_phone_vide  =  mysql_query($sql_comm_phone_vide);
		$data_comm_phone_vide =  mysql_fetch_object($req_comm_phone_vide);
		
		$commercial_infos['phone'] 	  = $data_comm_phone_vide->phone;
		// $commercial_infos['help_msg'] = '<span id="contact-crew-blue-text">Commandez par téléphone au</span><br /><br /><span id="contact-crew-grey-text">'.$data_comm_phone_vide->phone.'</span>';
		
	}

$login_session_duration = 500; 
if(((time() - $_SESSION['loggedin_time']) > $login_session_duration)){ 
	// session_unset();     // unset $_SESSION variable for the run-time 
    // session_destroy();   // destroy session data in storage
	// unset($_SESSION['loggedin_time'])
} 


?>
<?php if (DEBUG) $tab_microtime["header pre PHP"] = microtime(true) ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<style>
#fly-dropdown{
	display:none;
}
</style>

  <title><?php echo $title ?></title>
  <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0,minimum-scale=1.0,maximum-scale=1.0"/>
  <meta name="description" content="<?php echo $meta_desc ?>"/>
 <?php if($canonicalLink) : ?>
  <link rel="canonical" href="<?php echo URL.'produits/'.$canonicalLink.'.html' ?>" />
 <?php endif ?>
 <?php if (!empty($MetaLinksNextPrev)) echo $MetaLinksNextPrev; ?>
  <meta name="author" content="Techni-Contact.com"/>
 <?php if (TEST || DEBUG || defined("NOINDEX_NOFOLLOW")) { ?>
  <meta name="robots" content="noindex,nofollow"/>
 <?php } elseif (defined("NOINDEX_DOFOLLOW")) { ?>
  <meta name="robots" content="noindex,dofollow"/>
 <?php } elseif (defined("NOINDEX_FOLLOW")) { ?>
  <meta name="robots" content="noindex,follow"/>
 <?php }?>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />
  <link rel="stylesheet" type="text/css" href="<?php echo $res_url; ?>css/styles.css" />
  <link rel="stylesheet" type="text/css" href="<?php echo $res_url; ?>css/toolbar.css" />
  <link rel="stylesheet" type="text/css" href="<?php echo $res_url; ?>css/flick-jquery-ui.css" />
  <link rel="stylesheet" type="text/css" href="<?php echo $res_url; ?>fancybox/jquery.fancybox.css" />
  <!--[if lte IE 8]>
  <link rel="stylesheet" type="text/css" href="<?php echo $res_url; ?>css/styles-ie8.css"  />
  <![endif]-->
  <!--[if lte IE 7]>
  <link rel="stylesheet" type="text/css" href="<?php echo $res_url; ?>css/styles-ie7.css"  />
  <![endif]-->
  <!--[if IE 9]>
  <link rel="stylesheet" type="text/css" href="<?php echo $res_url; ?>css/styles-ie9.css"  />
  <![endif]-->
  <script type="text/javascript" src="<?php echo $res_url; ?>scripts/jquery-1.8.3.js"></script>
  <script type="text/javascript" src="<?php echo $res_url; ?>scripts/jquery-ui-1.10.4.js"></script>
  <script type="text/javascript" src="<?php echo $res_url; ?>scripts/jquery.ui.dialog.js"></script>
  <script type="text/javascript" src="<?php echo $res_url; ?>scripts/jquery.tools.min.js"></script>
  <script type="text/javascript" src="<?php echo $res_url; ?>fancybox/jquery.fancybox.pack.js"></script>
  <script type="text/javascript" src="<?php echo $res_url; ?>scripts/HN.Lib.js"></script>
  <script type="text/javascript" src="<?php echo $res_url; ?>scripts/HN.Locals.js"></script>
  <script type="text/javascript" src="<?php echo $res_url; ?>scripts/HN.TC.js"></script>
  <script type="text/javascript" src="<?php echo $res_url; ?>json/categories-menu.js"></script>
  <script type="text/javascript" src="<?php echo $res_url; ?>scripts/AJAX_search.js"></script>
  <script type="text/javascript">
    var JS_DOMAIN = "<?php echo DOMAIN ?>";
    var cartID = "<?php echo $cart->id ?>";
    var lien_bottom_search = '<span class="bottom_search_link_r"> ou <a href="<?php echo URL ?>recherche.html?moteur=calque_recherche\">Demandez une recherche gratuite</a></span>';
  </script>
  <script type="text/javascript" src="<?php echo $res_url ?>scripts/search-layer.js"></script>
<?php if (SHOW_TAGS) { ?>

  <?php if (defined("__HOME__")) { ?>
  <meta property="og:title" content="Techni Contact"/> 
  <meta property="og:description" content="Avec techni-contact.com, trouvez et achetez des biens d'équipements pour les professionnels et les collectivités."/>
  <meta property="og:type" content="website" /> 
  <meta property="og:image" content="http://www.techni-contact.com/ressources/images/logo-TC.gif" /> 
  <meta property="og:site_name" content="Techni Contact" /> 
  <meta property="og:url" content="http://www.techni-contact.com/" /> 
  <meta property="fb:app_id" content="229019513787954" /> 
  <meta property="fb:admins" content="684597046,100000413065618,1674194962" />
  <?php } elseif (defined("PRODUCT_PAGE")) { ?>
  <!-- FB like meta tag -->
  <meta property="og:title" content="<?php echo $pdt["name"] ?>"/>
  <meta property="og:description" content="<?php echo $meta_desc ?>"/>
  <meta property="og:type" content="product" />
  <meta property="og:image" content="<?php echo $pdt["pic_url"][0]["card"] ?>" />
  <meta property="og:site_name" content="Techni Contact" />
  <meta property="og:url" content="<?php echo $pdt["url"] ?>?utm_source=facebook&utm_medium=reseaux-sociaux&utm_campaign=bouton-recommandation-FB&campaignID=7" />
  <meta property="fb:app_id" content="229019513787954" /> 
  <meta property="fb:admins" content="684597046,100000413065618,1674194962" />
 <!-- FB like meta tag -->
  <?php } ?>

  <?php /*
  <script type="text/javascript">
    var _gaq = _gaq || [];
   // Variables personnalisées sous Google Analytics
    _gaq.push(['_setAccount', 'UA-4217476-2']);
    _gaq.push(['_setDomainName', '.techni-contact.com']);
  <?php if (defined("FAMILIES_PAGES")) : ?>
   <?php if ($catTree->length > 0) : ?>
    _gaq.push(['_setPageGroup', '3', '<?php echo $cat1['ref_name'] ?>']);
   <?php elseif ($catTree->length > 1) : ?>
    _gaq.push(['_setPageGroup', '3', '<?php echo $cat1['ref_name'] ?>']);
    _gaq.push(['_setPageGroup', '4', '<?php echo $cat2['ref_name'] ?>']);
   <?php elseif($catTree->length > 2) : ?>
    _gaq.push(['_setPageGroup', '3', '<?php echo $cat1['ref_name'] ?>']);
    _gaq.push(['_setPageGroup', '4', '<?php echo $cat2['ref_name'] ?>']);
    _gaq.push(['_setPageGroup', '5', '<?php echo $cat3['ref_name'] ?>']);
   <?php endif ?>
    setTimeout("_gaq.push(['_trackEvent', '30_seconds', 'read'])",30000);
  <?php elseif (defined('PRODUCT_PAGE')) : ?>
    _gaq.push(['_setPageGroup', '3', '<?php echo $pdt['cat1_ref_name'] ?>']);
    _gaq.push(['_setPageGroup', '4', '<?php echo $pdt['cat2_ref_name'] ?>']);
    _gaq.push(['_setPageGroup', '5', '<?php echo $pdt['cat3_ref_name'] ?>']);
    _gaq.push(['_setPageGroup', '1', '<?php echo $adv_cat_list[$pdt['adv_cat']]['acronym'] ?>']);
    _gaq.push(['_setPageGroup', '2', '<?php echo $pdt['adv_name'] ?>']);
    setTimeout("_gaq.push(['_trackEvent', '50_seconds', 'read'])",50000);
  <?php elseif (defined('__HOME__')) : ?>
    setTimeout("_gaq.push(['_trackEvent', '40_seconds', 'read'])",40000);
  <?php else : ?>
    setTimeout("_gaq.push(['_trackEvent', '30_seconds', 'read'])",30000);
  <?php endif ?>
    _gaq.push(['_trackPageview']);
    (function() {
      var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
      ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
      var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
    })();
  </script>
  
  */ ?>
<?php } ?>
  <!-- google plus link   -->
  <link href="https://plus.google.com/114869972974922674327" rel="publisher" />
 <?php/* if (!$secure) : ?>
  <script type="text/javascript" src="http://prod.web2mobile.fr/web2mob_technicontact.js"></script>
 <?php endif 

 <script type="text/javascript" src="<?php echo $res_url; ?>scripts/jquery.fancybox.js"></script>
 <script type="text/javascript" src="<?php echo $res_url; ?>scripts/jquery.cookie.js"></script>
 <link rel="stylesheet" type="text/css" href="<?php echo $res_url; ?>css/jquery.fancybox.css" media="screen" />
 
 <script type="text/javascript">   
    $(document).ready(function() {
		
		if($.cookie('the_cookie') != 1) { // Si the_cookie n'a pas pour valeur 1 alors on l'initialise et on joue l'appel de la popup
			$.cookie('the_cookie', '1', { expires: 1 }); // valeur en jour avant expiration du cookie			
			$.fancybox(
				 $("#popup").html(),
				 {
					type : 'iframe',
					href : 'message_excuses_TC.html', // url vers notre page html qui sera chargée dans la popup en mode iframe
					maxWidth : 730,
					maxHeight : 230,
					fitToView : false,
					width : '70%',
					height : '70%',
					autoSize : false		
			}
			);setTimeout("parent.$.fancybox.close()", 70000000000); // temps en milliseconde avant fermeture de la popup
		}
    });
</script>

<!-- Hotjar Tracking Code for http://www.techni-contact.com/ -->
<script> 
 (function(h,o,t,j,a,r){ 
 h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)}; 
 h._hjSettings={hjid:180258,hjsv:5}; 
 a=o.getElementsByTagName('head')[0]; 
 r=o.createElement('script');r.async=1; 
 r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv; 
 a.appendChild(r); 
 })(window,document,'//static.hotjar.com/c/hotjar-','.js?sv='); 
</script>
*/?>

<script type="text/javascript">var switchTo5x=true;</script>
<script type="text/javascript" src="https://ws.sharethis.com/button/buttons.js"></script>
<script type="text/javascript">stLight.options({publisher: "cff709ed-8f94-44b4-9555-b399facbdbf0", doNotHash:true, 
doNotCopy:true,hashAddressBar:false});</script>

</head>
<body<?php if($smallBackground){echo ' class="small-bg"';}else{echo ' class="big-bg"';}?>>


<a id="popup" style="display:none;"></a>
<div id="tooltip"></div>
<div id="message-dialog" title="Message"></div>

<?php if (TEST) include SECURE_PATH."manager/maintenance/head.php" ?>

<div id="outer-frame">
   <div id="outer-frame-left-grad"<?php if($smallBackground){echo ' class="outer-frame-small-grad"';}else{echo ' class="outer-frame-big-grad"';}?>></div>
  <div id="outer-frame-right-grad"<?php if($smallBackground){echo ' class="outer-frame-small-grad"';}else{echo ' class="outer-frame-big-grad"';}?>></div>
  <div id="wrapper">
   
    <div id="header">
      <div id="header-menu">
        <?php echo $menu_categories; ?>
      </div>
      <div id="header-up">
        <div id="header-up-menu-cart" class="fr">
          <a href="<?php echo URL; ?>panier.html"><img alt="Panier" src="<?php echo $res_url; ?>images/upper-grey-cart-logo.png" />Mon Panier</a>
        </div>
        <div id="header-up-menu-create-account" class="fr">
          <a href="#" onclick="javascript:HN.TC.ShowCreateAccountForm('show');return false;">S'inscrire</a>
        </div>
        <div id="header-up-menu-account" class="fr">
         <?php if (!$session->logged) : ?>
          <a href="#" onclick="window._gaq && _gaq.push(['_trackEvent', 'Bloc login', 'Ouverture bloc login', 'Ouverture login par lien header']);HN.TC.ShowLoginForm('show');return false;"><img alt="Compte" src="<?php echo $res_url; ?>images/upper-grey-account-logo.png" />S'identifier</a>
         <?php else : ?>
          <a href="<?php echo COMPTE_URL . "index.html"; ?>"><img alt="Compte" src="<?php echo $res_url; ?>images/upper-grey-account-logo.png" />Mon compte</a>
         <?php endif ?>
         <!-- <a href="<?php echo COMPTE_URL; ?>404.html"><img alt="Compte" src="<?php echo $res_url; ?>images/upper-grey-account-logo.png" />Mon compte</a>-->
        </div>
        
        <div id="header-up-menu-main" class="fr">
          <a href="<?php echo URL; ?>nous.html">Qui sommes-nous ?</a> |
          <a href="<?php echo URL; ?>index-utilisateurs.html">Nos clients</a> |
          <a href="<?php echo URL; ?>partenaire.html">Devenir partenaire</a> |
          <a href="<?php echo URL; ?>contact.html">Contact</a> |
          <a href="<?php echo URL; ?>catalogues.html">Nos catalogues</a> |
          <a href="<?php echo URL; ?>guides-achat/nos-guides.html">Nos guides </a> |
          <a href="<?php echo URL; ?>blog">Blog</a> 
          
        </div>
        <div class="zero"></div>
      </div>
      <div id="header-mobile-login">
      </div>
      <div id="header-mid">
        <?php if ($pageName == 'home') { ?><h1 class="header-logo"><?php }else{ ?><div class="header-logo"><?php } ?>
          <a href="<?php echo URL; ?>"><img src="<?php echo $res_url; ?>images/header-TC-logo.png" alt="Techni-Contact facilite tous vos achats professionnels : fourniture de matériels et d'équipements professionnels"/></a>
        <?php if ($pageName == 'home') { ?></h1><?php }else{ ?></div><?php } ?>
        
        <div id="header-search">
        <div id="header-search-form-zone">
			<?php 
				
				if (($_SERVER['PHP_SELF'] == '/fr/commande/order-step1.html') || 
				    ($_SERVER['PHP_SELF'] == '/fr/commande/order-step2.html') || 
					($_SERVER['PHP_SELF'] == '/fr/commande/order-step3.html') ||
					($_SERVER['PHP_SELF'] == '/fr/commande/order-confirmed.html') ){
					$action  = "";
					$disable = "disabled=true";
					$img_vignette = "https://".SECURE_FULL_DOMAIN."/fr/commande/vignette-header-catalogue.jpg";
					$botton  = ' type="button" ';
				}else{
					$action =  ' action="'.URL.'rechercher.html" ';
					$disable = "";
					$img_vignette = URL."ressources/images/vignette-header-catalogue.jpg";
					$botton  = ' type="submit" ';
				} 
			?>
		<div class="contentSeachTel">	
          <form class="search" <?= $action ?> method="get">
          <input type="text" autocomplete="off" placeholder="Chercher un produit, une référence..." value="<?= $_GET['search'] ?>" required  name="search"  id="header-search-input" oninvalid="this.setCustomValidity('Veuillez rentrer dans le champs de recherche le produit ou le service recherché')" oninput="setCustomValidity('')" class=" wf2_invalid wf2_isBlank wf2_defaultValue wf2_lostFocus" <?= $disable ?> />
          <input <?= $botton ?> src="<?php echo $res_url; ?>images/header-input-search-button.png" value="" id="header-search-input-submit"/>
		  </form>
		  
		   <div class="txtContact">Pas envie de chercher votre matériel ?  <span class="clickContact" <?php if(TEST): ?> onclick="javascript:HN.TC.ShowContacteEquipeForm('show');" <?php endif; ?>>Contactez directement notre équipe </span> </div>
        </div>
		
		<?php
			// echo "aaaaaa : ".$pdt['adv_cat'];
			if($_SERVER['SCRIPT_NAME'] == "/product.html"){
				
				if( ($pdt['adv_cat'] == '0') || ($pdt['adv_cat'] == '2') ){
				
				}else{				
					
		?>
		
		<div id="fly-dropdown" style="display:none;" >
			<div class="titleFly">Gagnez du temps, c'est gratuit ! 
				<img src="<?= URL ?>/ressources/images/close.png" class="imgClose" onclick="closeCalque()" /> </div>
			<div style="margin-bottom: 8px;">Demandez à nos experts<br /> de rechercher <br /> les équipements pour vous !</div>
			<div class="imgLeftFly" onclick="javascript:HN.TC.ShowContacteEquipeForm('show');">
				<img src="<?= URL ?>/ressources/images/arrow1.png"  />
				Envoyer une demande<br />(cela ne prend que 30 sec)
			</div>
		</div>
		<?php } 
			}else { ?>
		<div id="fly-dropdown" style="display:none;" >
			<div class="titleFly">Gagnez du temps, c'est gratuit ! 
				<img src="<?= URL ?>/ressources/images/close.png" class="imgClose" onclick="closeCalque()" /> </div>
			<div style="margin-bottom: 8px;">Demandez à nos experts<br /> de rechercher <br /> les équipements pour vous !</div>
			<div class="imgLeftFly" onclick="javascript:HN.TC.ShowContacteEquipeForm('show');">
				<img src="<?= URL ?>/ressources/images/arrow1.png"  />
				Envoyer une demande<br />(cela ne prend que 30 sec)
			</div>
		</div>		
		<?php } ?>
		
		
		
		
		<div id="show-contact-equipe-form" style="display:none;">
				<div id="txt_infosHead">	
					<p> Simple et gratuit : nos équipes cherchent les équipements pour vous !  </p><br /><br />
				</div>
				<div class="pointer_imgHead">
					<img src="<?= URL ?>ressources/images/doigt-pointeur.jpg" />
				</div>
				
				<div style="overflow: hidden;">
					<div id="result_message_demandeHead" style="padding: 10px;margin-bottom: 20px;display:none;"></div>
					<div id="loding_imgHead" style="overflow: hidden;margin: auto; width: 100px;display:none;">
							<img src="<?= URL ?>ressources/images/loading.gif" />
					</div>
					
					<div id="formss-question_demandeHead" >
					<div>
						<label><strong>Que recherchez vous ? </strong></label><br>
						<textarea cols="74" rows="5" style="width: 316px;" id="demandeHead" placeholder="Exemple : Pour notre prochain séminaire, nous recherchons 10 tables pliantes rondes de 180 cm de diamètre"></textarea>
					</div>
					<div>
						<div style="float:left;margin-right:10px;">
						<label><strong>Nom</strong></label><br>
						<input type="text" id="nomHead" value="" class="form-question-express" />
						</div>
						
						<div>
						<label><strong>Téléphone</strong></label><br>
						<input type="text"  id="telephone_deHead" value="" class="form-question-express" />
						</div>
					</div>
					
					<div class="btn-send-demande1">
						<center>
						<div class="btn-create_question" style="width: 225px;margin-left: -77px;" id="DemandeEquipe" <?php if(!TEST): ?> onclick="ga('send', 'event', 'Devis Express', 'Demande de recherche', 'Validation pop up');DemandeEquipe('3')" <?php endif; ?> >>>> Trouvez-moi ce matériel SVP</div>						
						</center>
				    </div>
				</div>
				
				</div>
				
			</div>
		<div class="zero"></div>
      </div>
       
        
        <div id="header-mid-coords">
          <span class="big-blue-title header-mid-coords-tel">
           <?php 
			if (isset($pdt_referent_commercial_id)) {
				if(!isset($_SESSION['comm_phone'])){
					echo $commercial_infos['phone'];
				
				}else {
					echo $_SESSION['comm_phone'];
				}
			}else{
					echo $commercial_infos['phone'];
			}
		    // initiated in head.php ?>
          </span><br />
          
        </div>
        <div class="zero"></div>
		
      </div>
      </div>
 
      <div id="header-mobile-nav">
        <select>
          <?php echo $menu_categories_options ?>
        </select>
      </div>
      
      <!-- absolute menu position -->
      <div id="header-submenu">
        <div id="header-submenu-top-arrow"></div>
      </div>
    </div><!-- /div#header -->
<script>
function triggerSubmenuTopArrow(){
  $('#header-menu a').each(function(){
    $(this).bind( 'mouseover', function(){
      var leftPosition = $(this).offset().left - $(this).parent().parent().offset().left + ($(this).width() / 2);
      $('#header-submenu-top-arrow').css({left: leftPosition+'px'});
    });
  });
}

function DemandeEquipe(type){
	
	var demande 		= $("#demandeHead").val();
	var email	 		= "";
	var telephone 		= $("#telephone_deHead").val();
	var nom	    		= $("#nomHead").val();
	var prenom			= "";
	var name_products 	= "";
	var ids_products	= "";
	var id_famillies	= "";
	var id_advertiser	= "";
	var typeLead		= type;

	var textAreaString = demande.replace(/\n\r/g,"<br />");
	var textAreaString = demande.replace(/\n/g,"<br />");
	
	var n = nom.length;
	var q = prenom.length;
	var d = demande.length;		
	
	if( (nom != '' || nom == null) && n>2 ){
	 valid_forms_nom = 1;
	}else {
		$("#nomHead").css("border", "2px solid red");
		$("#nomHead").attr("placeholder", "Merci de saisir votre Nom");
     valid_forms_nom = 0;
	}
	
	if( (demande != '' || demande == null) && d>9 ){
	 valid_forms_demande = 1;
	}else {
		$("#demandeHead").css("border", "2px solid red");
		$("#demandeHead").attr("placeholder", "Merci de saisir votre question");
     valid_forms_demande = 0;
	}
	
	if( (telephone != '' || telephone == null) ){
	 valid_forms_tel = 1;
	}else {
		
		$("#telephone_deHead").css("border", "2px solid red");
		$("#telephone_deHead").attr("placeholder", "Merci de saisir votre tel");
     valid_forms_tel = 0;
	}
	

	if(valid_forms_nom == 1  && valid_forms_demande == 1 && valid_forms_tel == 1 ){
		$.ajax({
				url: '<?= URL ?>ajax_question/ajax_create_demande.php?textAreaString='+textAreaString+'&email='+email+"&telephone="+telephone+"&nom="+nom+"&prenom="+prenom+"&ids_products="+ids_products+"&id_famillies="+id_famillies+"&id_advertiser="+id_advertiser+"&typeLead="+typeLead,
				type: 'GET',
				beforeSend: function() {
					$('#formss-question_demandeHead').hide();
					$('#txt_infosHead').hide();
					$('.pointer_imgHead').hide();
					$('#loding_imgHead').show();
        			
				},				
				success:function(data){
					$('#result_message_demandeHead').show();
					$('#result_message_demandeHead').html(data);
				},
				complete: function() {
					$('#loding_imgHead').hide();
				}
		});	
	}
}
<?php 
	if($_SERVER['SCRIPT_NAME'] == "/product.html"){
		if( ($pdt['adv_cat'] == '0') || ($pdt['adv_cat'] == '2') ){
				
		}else{
?>
$(document).ready(function(){
	var ipClient  =  '<?= $_SERVER["REMOTE_ADDR"] ?>';
	$.ajax({
		url: '<?= URL ?>ressources/ajax/AJAX_update_ip.php?action=addCount&ipClient='+ipClient,  
		type: 'GET',
		success:function(data){
			if(data == 3){
				$("#fly-dropdown").remove();
			}else{
				setTimeout(function(){
				   $('#fly-dropdown').fadeIn(1000);
				}, 2000);
			}
		}
	});	
});
	<?php } 
	
	}else { ?>
$(document).ready(function(){
	var ipClient  =  '<?= $_SERVER["REMOTE_ADDR"] ?>';
	
	$.ajax({
		url: '<?= URL ?>ressources/ajax/AJAX_update_ip.php?action=addCount&ipClient='+ipClient,  
		type: 'GET',
		success:function(data){
			if(data == 3){
				$("#fly-dropdown").remove();
			}else{
				setTimeout(function(){
				   $('#fly-dropdown').fadeIn(1000);
				}, 2000);
			}
		}
	});	
});	
	
	
	<?php } ?>
function closeCalque(){
	var ipClient  =  '<?= $_SERVER["REMOTE_ADDR"] ?>';
	$.ajax({
		url: '<?= URL ?>ressources/ajax/AJAX_update_ip.php?action=closeCount&ipClient='+ipClient,  
		type: 'GET',
		success:function(data){
			if(data == 3){
				$("#fly-dropdown").remove();
			}
		}
	});	
}


var referrer = document.referrer.split('?')[1];
console.log(referrer);
/*
$(document).ready(function() {
var pathname = document.referrer;
console.log(pathname);
});
*/

$( ".imgClose" ).click(function() {
  $('#fly-dropdown').fadeOut(1000);
});


//20000 
</script>
<?php require(SITE . 'breadcrumb.php'); ?>
<?php if (DEBUG) $tab_microtime["header HTML & PHP"] = microtime(true); ?>
