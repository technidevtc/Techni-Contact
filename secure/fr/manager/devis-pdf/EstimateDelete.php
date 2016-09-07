<?php

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$title = 'Gestion des Devis Clients';

$navBar = '<a href="index.php?SESSION" class="navig">Gestion des Devis Clients</a> &raquo; Supprimer un devis';

require(ADMIN . 'head.php');

$estimateID = isset($_GET['estimateID']) ? $_GET['estimateID'] : '';

?>
<div class="titreStandard">Devis n°<?php echo $estimateID ?></div>
<br />
<div class="bg" style="position: relative">
<?php
if (!$user->get_permissions()->has("m-comm--sm-estimates","d")) {
  print "	<h3>Vous n'avez pas les droits adéquats pour réaliser cette opération</h3>";
}
else {
  if (preg_match('/^[0-9a-v]{26,32}$/', $estimateID))
  {
    $handle->query("delete from paniers where id = '" . $estimateID . "'",__FILE__, __LINE__);
    $handle->query("delete from paniers_produits where idPanier = '" .$estimateID . "'", __FILE__, __LINE__);
    print "	<h3>Devis supprimé avec succés</h3>";
  }
  else
  {
    print "	<h3>Une erreur est survenue lors de la suppression de ce devis</h3>";
  }
}
?>
</div>
