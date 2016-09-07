<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

//header("Content-Type: text/plain; charset=iso-8859-1");
header("Content-Type: text/plain; charset=utf-8");

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
  $ac = new RefAttributeCollection(array($cur_cat_id, "order by name ASC"));
?>
<table class="item-list-table cat3-alt" cellspacing="0" cellpadding="0">
  <tbody>
   <?php foreach ($ac as $a) { ?>
    <tr class="<?php echo $a->get_values()->len() ? "scat1" : "" ?>">
      <td class="tree"></td>
      <td class="name-value"><span><?php echo $a->name ?></span> <div class="icon icon-add" data-type="attr" data-id="<?php echo $a->id ?>"></div></td>
    </tr>
     <?php foreach ($a->get_values() as $av) { ?>
      <tr class="selem1">
        <td class="tree"></td>
        <td class="name-value"><span><?php echo $av->value ?></span> <div class="icon icon-add" data-type="attr-value" data-id="<?php echo $av->id ?>"></div></td>
      </tr>
     <?php } ?>
   <?php } ?>
  </tbody>
</table>
<?php
}
?>