<?php

/*================================================================/

 Techni-Contact V4 - MD2I SAS
 http://www.techni-contact.com

 Auteur : OD pour Hook Network SARL - http://www.hook-network.com
 Date de création : 03 février 201

 Fichier : /secure/manager/commandes/AJAXconfirmMailSend.php
 Description : Fichier interface de confirmation popup d'envoi d'email AJAX

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

require(ADMIN."logs.php");

$handle = DBHandle::get_instance();
$user = new BOUser();

header("Content-Type: text/plain; charset=utf-8");

$o = array();

if(!$user->login())
{
	$o['error'] = "Votre session a expirée, veuillez réactualiser la page pour retourner à la page de login";
}
else
{

  if (!$user->get_permissions()->has("m-comm--sm-orders","e")) {
    print "ProductsError".__ERRORID_SEPARATOR__."Vous n'avez pas les droits adéquats pour réaliser cette opération".__ERROR_SEPARATOR__.__MAIN_SEPARATOR__;
    exit();
  }
  
  if(isset($_GET['idCommande']) && isset($_GET['idSupplier']) && is_numeric($_GET['idCommande']) && is_numeric($_GET['idSupplier']) && $_GET['idCommande'] > 0 && $_GET['idSupplier'] > 0){

    $cmd = new Command($handle, $_GET['idCommande']);
	if ($cmd->statut < 10) {
		$errorstring .= '- La commande ayant pour numéro identifiant ' . $_GET['idCommande'] . " n'existe pas<br />\n";
	}  else {
           ?>
<style type="text/css">
 table.liste_produits_popup { width: 900px; border: 1px solid #CCCCCC; border-collapse: collapse; background-color: #FFFFFF; font: 11px Arial, Helvetica, sans-serif; color: #000000; }
 table.liste_produits_popup  thead, table.libelle_total td.text { font-weight: bold; color: #FFFFFF; background-color: #5D6068; margin: 0; padding: 0; text-align: center; vertical-align: middle; }
 table.liste_produits_popup  tr td, table.libelle_total tr td { padding: 2px 4px; border-left: 1px solid #CCCCCC; vertical-align: top; }
 table.libelle_total { width: 200px; border: 1px solid #CCCCCC; border-collapse: collapse; background-color: #FFFFFF; font: 11px Arial, Helvetica, sans-serif; color: #000000; }
 table.libelle_total tr td.valeur { width: 60px; text-align: right;}
 table.libelle_total {float: right;}
</style>
<script type="text/javascript">
 //CQDB = Charge Question Dialog Box
  $("#CQDB input[type='button']:first").click(function(){
  $("div.DB-bg").hide();
  $("#CQDB").hide();
  });
  $("#CQDB input[type='button']:last").click(function(){
  $("form[name='confirm-sendMail']").submit();
  });
</script>
<form name="confirm-sendMail" method="post" action="">
<input type="hidden" name="confirm-mail" value="1"/>
<input type="hidden" name="supplier" value="<?php echo $_GET['idSupplier'] ?>"/>
Vous &ecirc;tes sur le point d'envoyer &agrave; <?php echo $cmd->getAdvertiserName($_GET['idSupplier']) ?> une commande comprenant les &eacute;l&eacute;ments suivants :<br/>
 <br/>
 <table class="liste_produits_popup">
   <thead><tr><td>Image</td><td>Ref TC</td><td>Ref Fournisseur</td><td>Qt&eacute;</td><td>Prix fournisseur Unitaire</td><td>Prix total HT</td></tr></thead>
   <tbody>
   <?php
     $dft_qte_list = array();
     $totalHT = 0;
     $totalTTC = 0;
	foreach ($cmd->items as &$item){
            if($item["idAdvertiser"] == $_GET['idSupplier']){
              $totalHT += $item["sum_base_price2"];
              $diffTVA = $totalHT*($item["tauxTVA"]/100);
              $totalTTC = $totalHT+$diffTVA;
              $dft_qte_list[$item["idTC"]] = $item["quantity"];
              $pdt_image = is_file(PRODUCTS_IMAGE_INC."thumb_small/".$item["idProduct"]."-1.jpg") ? SECURE_RESSOURCES_URL."images/produits/thumb_small/".$item["idProduct"]."-1.jpg" : SECURE_RESSOURCES_URL."images/produits/no-pic-thumb_small.gif";
              $refPdtName = $cmd->getRefNameById($item["idProduct"]);
              ?>
              <tr>
                <td class="center"><a href="<?php echo URL.'produits/'.$item['idFamily'].'-'.$item['idProduct'].'-'.$refPdtName.'.html' ?>" target="_blank"><img src="<?php echo $pdt_image ?>" /></a></td>
                <td class="center"><a href="<?php echo ADMIN_URL ?>products/edit.php?id=<?php echo $item["idProduct"] ?>"><?php echo $item["idTC"] ?></a></td>
                <td class="center"><?php echo $cmd->getRefSupplier($item["idProduct"], $item["idTC"]) ?></td>
                <td class="right"><?php echo $item["quantity"] ?></td>
                <td class="ref-prix"><?php echo sprintf("%0.2f", $item["price2"]) ?></td>
                <td class="ref-prix"><?php echo sprintf("%0.2f", $item["sum_base_price2"]) ?></td>
              </tr>
<?php		
        }
     }

   ?>
   </tbody>
 </table>
 <br />
 <table class="libelle_total">
   <tr>
     <td class="text">Total commande HT</td>
     <td class="valeur"><?php echo sprintf("%0.2f", $totalHT) ?></td>
   </tr>
   <tr>
     <td class="text">Total commande TTC</td>
     <td class="valeur"><?php echo sprintf("%0.2f", $totalTTC) ?></td>
   </tr>
 </table>
 
<br/>

 <br/>
Vous pouvez si vous le souhaitez ajouter un commentaire &agrave; votre commande : <br/>
<textarea name="commentMail" id="commentMail" value="" rows="8"></textarea>
 <br/>
 <input type="button" value="Annuler"/> &nbsp; &nbsp; <input type="button" value="Envoyer la commande"/>
</form>
 <?php
        }
  }
 
}
?>