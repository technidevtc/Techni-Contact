<?php

/*================================================================/

 Techni-Contact V4 - MD2I SAS
 http://www.techni-contact.com

 Auteur : OD pour Hook Network SARL - http://www.hook-network.com
 Date de création : 19 janvier 2011

 Mises à jour :

 Fichier : /secure/manager/import/supplier.php
 Description : Import des modifications de prix des fournisseurs
 
/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$title = 'Mise à jour fournisseurs';
$navBar = '<a href="imports.php?SESSION" class="navig">Liste des importations</a> &raquo; Editer un import';

require(ADMIN . 'head.php');

require('_ClassImport.php');
require('_ClassImportUpdateSupplier.php');


if (!isset($_GET['id']))
	$es = "Le numéro identifiant de l'import n'a pas été spécifié";
elseif (($ImportID = (int)($_GET['id'])) <= 0)
	$es = "Le numéro identifiant de l'import est incorrect";
else
{
	$imp = & new Import($handle, $ImportID);
	if (!$imp->exist) $es = "L'import n° " . $ImportID . " n'existe pas.";
        else {
          $nbRefWithoutProduct = $imp->getNbRefWithoutProduct();
          $nbProductByFileRef = $imp->getNbProductByFileRef();
        }
}
?>
<div class="titreStandard">Import n°<span id="id"><?php echo $ImportID ?></span> pour <?php echo $imp->getSupplierName() ?> du <?php echo date('d/m/Y \à H:i:s', $imp->timestamp) ?></div>
<br />
<link href="HN.css" rel="stylesheet" type="text/css"/>
<div class="bg" id="bg_parent">
<?php
if (!empty($es))
{
?>
	<div class="InfosError"><?php echo $es ?></div>
</div>
<?php
	exit();
}
?>
<style type="text/css">
.legend-group { font: bold 11px Tahoma, Helvetica, sans-serif; display: inline; }
.legend-label { padding: 2px 10px 2px 15px; font-variant: small-caps; border: 1px solid #000000; }
.not-valid { background-color: #FFFFFF; }
.not-valid-update { background-color: #FFB0B0; }
.valid { background-color: #D0FFFF; }
.valid-update { background-color: #B0FFB0; }
.finalized { background-color: #E0E0FF; }
.finalized-update { background-color: #FFE0E0; }
.cancelled { background-color: #FFB0B0; }
</style>

<script src="Classes.js" type="text/javascript"></script>
<script src="../js/ManagerFunctions.js" type="text/javascript"></script>
<script type="text/javascript">
  function toSuppliersPage(){
    window.location.href  = "suppliers.php";
  }
</script>
<div class="infos">
Fournisseur : <?php echo $imp->getSupplierName() ?> <br />
<br />
-      Date de mise à jour : <?php echo date('d/m/Y \à H:i:s', $imp->timestamp) ?><br />
<br />
-      État : <?php echo $imp->getStatus() ?> <br />
<br />
-      Nombre de produits mis à jour : <?php echo $imp->nbp_final  ?><br />
<br />
-      Nouveaux produits à intégrer manuellement : <?php echo $nbRefWithoutProduct ?><br />
<br />
-      Produits obsolètes à supprimer : <?php echo $imp->nbp_final-$nbProductByFileRef ?><br />
<br />
<br />
</div>
<?php if($imp->status == __I_V__){ ?>
<form action="supplierExtract.php" method="post">
<input type="hidden" name="idImport" value="<?php echo $ImportID ?>" />
  <?php if($nbRefWithoutProduct > 0){ ?>
  Nouveautés : <input type="submit" class="bouton" name="nouveautes" value="Extraire au format Excel" /><br />
  <?php } ?>
  <?php if($imp->nbp_final-$nbProductByFileRef > 0){ ?>
  Produits obsolètes : <input type="submit" class="bouton" name="obsoletes" value="Extraire au format Excel" />
  <?php } ?>
</form>
<?php } ?>
<br />
<div>
	<input type="button" class="bouton" value="Retour" onclick="toSuppliersPage();"/>
        <?php if($imp->status == __I_VF__) { ?>
	<input type="button" class="bouton" value="Finaliser l'import" onclick="MakeImport();"/>
        <?php }elseif($imp->status == __I_V__) { ?>
	<input type="button" class="bouton" value="Annuler l'import" onclick="CancelImport();"/>
        <?php } ?>

</div>

<script type="text/javascript">

    var AJAXHandle = {
	type : "GET",
	url: "SupplierManagment.php",
	dataType: "json",
	error: function (XMLHttpRequest, textStatus, errorThrown) {
		$("#importStatus").text(textStatus);
	},
	success: function (data, textStatus) {
		
                  if(data.result[0] == 'ok')
                    $("#importStatus").text('ok');
                  else
                    $("#importStatus").text('erreur');
		
	}
};

function CancelImport(){

    var id = $("#id").text();
    AJAXHandle.data = "action=annule&idImport="+id;
    $.ajax(AJAXHandle);
}

function MakeImport(){

    var id = $("#id").text();
    AJAXHandle.data = "action=importe&idImport="+id;
    $.ajax(AJAXHandle);
}

</script>

<?php

require(ADMIN . 'tail.php');

?>
