<?php

/*================================================================/

 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de création : 2 avril 2006

 Mises à jour :

 Fichier : /secure/manager/tva/index.php
 Description : Accueil gestion des taux de TVA

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
require(ADMIN."config.php");
require(ADMIN."tva.php");

$title = $navBar = "Options de configuration par défaut";
require(ADMIN."head.php");


///////////////////////////////////////////////////////////////////////////

$errorstring = "";

$listeConfigs = displayConfigs($handle);

$config_name  = isset($_POST['config_name'])  ? substr(trim($_POST['config_name']), 0, 255) : "";
$config_value = isset($_POST['config_value']) ? substr(trim($_POST['config_value']), 0, 255) : "";

if($config_name != "" && $config_value != "") {
  if($user->rank == HOOK_NETWORK || $user->rank == COMMADMIN || !$user->get_permissions()->has("m-admin--sm-default-options","e")) {
    if (isset($listeConfigs[$config_name])) {
      switch ($config_name) {
        case "idTVAdft" :
          if (!existTVA($handle, $config_value))
          $errorstring .= "- Le taux de TVA par défaut choisi n'existe pas<br/>\n";
          break;

        case "fdp" :
          if (!preg_match("/^[0-9]+((\.|,)[0-9]+){0,1}$/", $config_value))
          $errorstring .= "- Les frais de port par défaut saisis sont invalides<br/>\n";
          break;

        case "fdp_franco" :
          if (!preg_match("/^[0-9]+((\.|,)[0-9]+){0,1}$/", $config_value))
          $errorstring .= "- Le franco des frais de port saisi est invalide<br/>\n";
          break;

        case "fdp_idTVA" :
          if (!existTVA($handle, $config_value))
          $errorstring .= "- Le taux de TVA des frais de port choisi n'existe pas<br/>\n";
          break;
      }
    }
  }
  else {
    $errorstring .= "- Vous n'avez pas les droits requies pour effectuer cette opération <br/>\n";
  }

  if (empty($errorstring)) {
    $ok = setConfig($handle, $config_name, $config_value);
    if ($ok) $listeConfigs = displayConfigs($handle);
  }
}

///////////////////////////////////////////////////////////////////////////

?>
<link rel="stylesheet" type="text/css" href="config.css" />
<div class="titreStandard">Options de configuration par défaut</div>
<br/>
<script type="text/javascript">
function edit_config(editvalue) {
  document.editconfig.config_name.value = editvalue;
  document.editconfig.submit();
}
</script>
<div class="bg">
  <form id="editconfig" name="editconfig" method="post" action="index.php">
    <input type="hidden" name="config_name" value="<?php echo $config_name ?>" />
    <table id="config" cellpadding="0" cellspacing="0" border="0">
      <thead>
        <tr>
          <th style="width: 130px">Configuration</th>
          <th style="width: 400px">Description</th>
          <th style="width: 400px">Valeur</th>
          <th class="none" style="width: 100px"></th>
        </tr>
      </thead>
      <tbody>
      <?php $i=0; foreach ($listeConfigs as $k => $v) { ?>
        <tr style="background-color: #<?php echo $i++%2?"f4faff":"dde3ec" ?>">
        <td><?php echo to_entities($k) ?></td>
        <td><?php echo to_entities($v[0]) ?></td>
       <?php if ($k == "idTVAdft" || $k == "fdp_idTVA") { $tauxtva = loadTVA($handle, $v[1]) ?>
        <td><?php echo to_entities($tauxtva[2]) ?></td>
       <?php } else { ?>
        <td><?php echo to_entities($v[1]) ?></td>
       <?php } ?>
        <td class="modConfig"><a href="javascript: edit_config('<?php echo $k ?>')">modifier</a></td>
        </tr>
      <?php } ?>
      </tbody>
    </table>
  </form>
</div>
<?php
$next = false;
if ($config_name != "" && isset($listeConfigs[$_POST['config_name']])) {
  if ($config_value != "") {
    if (empty($errorstring)) {
      if($ok)
        $out = "Changement de la configuration du ".$listeConfigs[$config_name][0]." effectué avec succés";
      else
        $out = "Erreur lors du changement de la configuration du ".$listeConfigs[$config_name][0]."";
?>
<br/>
<div class="titreStandard">Enregistrement de la configuration</div>
<br/>
<div class="bg">
  <div class="confirm"><?php echo $out ?></div>
  <br/>
  <br/>
</div>
<?php
    }
    else
      $next = true;
  }
  else
    $next = true;
}

if ($next) {
  if ($config_value == "")
    $config_value = $listeConfigs[$config_name][1];
?>
<br/>
<div class="titreStandard">Modification de la configuration</div>
<br/>
<div class="bg">
 <?php if (!empty($errorstring)) { ?>
  <div style="color: #e00000"><?php echo $errorstring ?></div><br/>
 <?php } ?>
  <form id="modconfig" name="modconfig" method="post" action="index.php">
    <div class="desc"><?php echo $listeConfigs[$config_name][0] ?></div>
    <br/>
    <input type="hidden" name="config_name" value="<?php echo $config_name ?>" />
    <table>
      <tr>
        <td class="intitule"><?php echo $config_name ?> :</td>
        <td>
         <?php if ($config_name == "idTVAdft" || $config_name == "fdp_idTVA") { $listeTVAs = displayTVAs($handle, " order by taux desc") ?>
          <select name="config_value">
           <?php foreach ($listeTVAs as $v) { ?>
            <option value="<?php echo $v[0] ?>" <?php if($config_value==$v[0]) { ?>selected="selected"<?php } ?>><?php echo $v[1]." ".$v[2] ?> %</option>
           <?php } ?>
          </select>
         <?php } else { ?>
          <input type="text" size="40" maxlength="255" name="config_value" onBlur="this.value = trim(this.value)" value="<?php echo to_entities($config_value) ?>">
         <?php } ?>
        </td>
      </tr>
    </table>
    <br/>
    <div style="text-align: center"><input type="button" class="bouton" value="Valider" name="ok" onClick="this.form.submit(); this.disabled=true"> &nbsp; <input type="reset" value="Annuler" class="bouton" name="nok"></div>
  </form>
</div>
<?php
} // fin affichage form
?>
<?php require(ADMIN."tail.php") ?>
