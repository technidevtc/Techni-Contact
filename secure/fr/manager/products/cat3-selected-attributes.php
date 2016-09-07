<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

//header("Content-Type: text/html; charset=iso-8859-1");
header("Content-Type: text/html; charset=utf-8");

$user = new BOUser();

if (!$user->login()) {
	$o["error"] = "Votre session a expirée, veuillez vous identifier à nouveau après avoir rafraichi votre page";
  print json_encode($o);
	exit();
}

if (!$user->get_permissions()->has("m-prod--sm-categories","r")) {
  $o["error"] = "Vous n'avez pas les droits adéquats pour réaliser cette opération.";
  print json_encode($o);
	exit();
}

if (!isset($cur_cat_id))
  if (isset($_REQUEST["cat3Id"]) && is_numeric($_REQUEST["cat3Id"]))
    $cur_cat_id = $_REQUEST["cat3Id"];

if (isset($cur_cat_id)) {
  $sac = new RefAttributeCollection(array($cur_cat_id, "selected = 1", "order by order ASC"));
  if ($sac->len()) {
?>
<div class="title">Liste des attributs sélectionnés pour la famille :</div>
<table class="cat3-alt"><tbody><tr>
 <?php foreach ($sac as $sa) { ?>
  <td><ul>
    <li><span><?php echo $sa->name ?></span> <div class="icon icon-add" data-type="attr" data-id="<?php echo $sa->id ?>"></div></li>
   <?php foreach ($sa->get_values("order by order ASC") as $sav) { ?>
    <li><span><?php echo $sav->value ?></span> <div class="icon icon-add" data-type="attr-value" data-id="<?php echo $sav->id ?>"></div></li>
   <?php } ?>
  </ul></td>
 <?php } ?>
</tr></tbody></table>
<div class="zero"></div>
<?php
  }
}
?>
