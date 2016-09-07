<?php

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$title = 'Gestion des Commandes Clients';

$navBar = '<a href="index.php?SESSION" class="navig">Gestion des Commandes Clients</a> &raquo; Supprimer une commande';

require(ADMIN . 'head.php');

$commandID = isset($_GET['commandID']) ? $_GET['commandID'] : '';

?>
<div class="titreStandard">Commande n°<?php echo $commandID ?></div>
<br />
<div class="bg" style="position: relative">
<?php
if (!$user->get_permissions()->has("m-comm--sm-orders","d")) {
  print "	<h3>Vous n'avez pas les droits adéquats pour réaliser cette opération</h3>";
}
else {
  if (preg_match('/^[1-9]{1}[0-9]{0,8}$/', $commandID))
  {
    $handle->query("delete from commandes where id = " . $commandID,__FILE__, __LINE__);
    $handle->query("delete from commandes_advertisers where idCommande = " .$commandID, __FILE__, __LINE__);
    print "	<h3>Commande supprimée avec succés</h3>";
  }
  else
  {
    print "	<h3>Une erreur est survenue lors de la suppression de cette commande</h3>";
  }
}
?>
</div>
