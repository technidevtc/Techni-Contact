<?php

if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

$title = "Gestion des fiches clients";
$navBar = "<a href=\"".ADMIN_URL."clients/index.php\" class=\"navig\">Gestion des fiches clients</a> &raquo; Supprimer un définitivement un client";
require(ADMIN."head.php");

$customer_id = isset($_GET["id"]) && is_numeric($_GET["id"]) ? (int)$_GET["id"] : 0;

if ($customer_id === 0) { ?>
  <div class="bg">
    <div class="fatalerror">Identifiant produit incorrect.</div>
  </div>
<?php
  require(ADMIN."tail.php");
  exit();
}
if (!$user->get_permissions()->has("m-comm--sm-customers", "d")) { ?>
  <div class="bg">
    <div class="fatalerror">Vous n'avez pas les droits adéquats pour réaliser cette opération.</div>
  </div>
<?php
  require(ADMIN."tail.php");
  exit();
}
CustomerUser::delete($customer_id);
?>
<div class="bg">
  <div class="confirm">Suppression du client <?php echo $customer_id ?> effectuée avec succès</div>
</div>
<?php require(ADMIN."tail.php") ?>
