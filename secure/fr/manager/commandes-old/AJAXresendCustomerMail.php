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
  
  if(isset($_GET['idCommande']) && isset($_GET['idCustomer']) && is_numeric($_GET['idCommande']) && is_numeric($_GET['idCustomer']) && $_GET['idCommande'] > 0 && $_GET['idCustomer'] > 0){

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
  $("form[name='confirm-resendCustomerMail']").submit();
  });
</script>
<form name="confirm-resendCustomerMail" method="post" action="">
<input type="hidden" name="confirm-resendCustomerMail" value="1"/>
<input type="hidden" name="customer" value="<?php echo $_GET['idCustomer'] ?>"/>
Vous &ecirc;tes sur le point de renvoyer &agrave; <?php echo $cmd->coord["nom"].' '.$cmd->coord["prenom"] ?> un mail de confirmation de commande.<br/>
 <br/>
 <input type="button" value="Annuler"/> &nbsp; &nbsp; <input type="button" value="Envoyer le mail"/>
</form>
 <?php
        }
  }
 
}
?>