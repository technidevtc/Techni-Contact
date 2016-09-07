<?php
if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
  require_once '../../../../config.php';
}else{
  require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

$title = $navBar = "Gestion des Familles";
$baseHref = '/fr/manager/families/';
require ADMIN.'head.php';

$canDelete = $user->get_permissions()->has('m-prod--sm-categories','d');
?>

<link href="assets/app-family.css" rel="stylesheet" />
<div class="no-conflict">
  <div class="bg">
    <app-family>Chargement...</app-family>
  </div>
</div>
<script>
  var canDelete = <?php echo $canDelete ?>;
</script>
<script src="assets/angular2.js"></script>
<script src="assets/app-family.js"></script>

<?php require ADMIN.'tail.php' ?>
