<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

//header("Content-Type: text/plain; charset=iso-8859-1");
header("Content-Type: text/plain; charset=utf-8");

$user = new BOUser();

if (!$user->login()) {
	$o["error"] = "Votre session a expirÃ©e, veuillez vous identifier Ã  nouveau aprÃ¨s avoir rafraichi votre page";
	print json_encode($o);
	exit();
}

if (!$user->get_permissions()->has("m-prod--sm-categories","r")) {
  $o["error"] = "Vous n'avez pas les droits adÃ©quats pour rÃ©aliser cette opÃ©ration.";
  print json_encode($o);
	exit();
}

if (!isset($cur_family_id))
  if (isset($_REQUEST["cur_family_id"]) && is_numeric($_REQUEST["cur_family_id"]))
    $cur_family_id = $_REQUEST["cur_family_id"];

if (!isset($cat3_attr))
  if (isset($_REQUEST["cat3_attr"]))
    $cat3 = substr ($_REQUEST["cat3_attr"], 10);
    if(is_numeric($cat3))
      $cat3_attr = $cat3;

if (isset($cat3_attr) && isset($cur_family_id)) {
  $ac = new RefAttributeIntervalCollection(array($cur_family_id, 'attributeId = '.$cat3_attr, "order by name ASC"));
?>
<table class="item-list-table cat3-group" cellspacing="0" cellpadding="0">
  <thead>
    <tr>
      <th>Valeur départ</th>
      <th>Valeur fin</th>
      <th>Unité</th>
      <th>Ordre</th>
    </tr>
  </thead>
  <tbody>
   <?php
   $tableLenght = 0;
   foreach ($ac as $a) {  ?>
    <tr class="<?php echo 'id_attr_'.$a->id ?>">
      <td class="name-value"><span><input type="text" size="24" value="<?php echo $a->start_from ?>" class="ref-col-dialog"></span></td>
      <td class="name-value"><span><input type="text" size="24" value="<?php echo $a->goes_to ?>" class="ref-col-dialog"></span></td>
      <td class="name-value"><span><input type="text" size="24" value="<?php echo $a->name ?>" class="ref-col-dialog"></span></td>
      <td class="name-value"><span><input type="text" size="24" value="<?php echo $a->position ?>" class="ref-col-dialog"></span></td>
    </tr>
   <?php $tableLenght++;
   }
   for($a=$tableLenght; $a<10; $a++){
     ?>
     <tr class="<?php //echo $a->get_values()->len() ? "scat1" : "" ?>">
      <td class="name-value"><span><input type="text" size="24" value="" class="ref-col-dialog"></span></td>
      <td class="name-value"><span><input type="text" size="24" value="" class="ref-col-dialog"></span></td>
      <td class="name-value"><span><input type="text" size="24" value="" class="ref-col-dialog"></span></td>
      <td class="name-value"><span><input type="text" size="24" value="" class="ref-col-dialog"></span></td>
    </tr>
   <? }?>
  </tbody>
</table>
<button id="update-cat3-attributes-values-group" class="btn ui-state-default ui-corner-all fr">sauvegarder</button>
<?php
}
?>