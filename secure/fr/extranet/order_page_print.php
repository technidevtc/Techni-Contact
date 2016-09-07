<?php

/*================================================================/

 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com

 Auteur : OD pour Hook Network SARL - http://www.hook-network.com
 Date de création : 14 février 2011

 Fichier : /secure/extranet/order_page_print.html
 Description : Impression commande extranet

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
include('language_local.php');

$title = COMMAND_TITLE;

define('WHERE', WHERE_COMMANDS);
require(EXTRANET . 'head.php');

if ($user->parent != '61049')
{
    header('Location: ' . EXTRANET_URL . 'requests.html');
    exit();
}
if (!isset($_GET['idOrder']) || !preg_match('/^'.$user->id.'\-[1-9]{1}[0-9]{0,8}$/', $_GET['idOrder']))
{
    header('Location: ' . EXTRANET_URL . 'commandes.html');
    exit();
}

$order = & new Order($handle ,$_GET['idOrder']);

$supplier = new AdvertiserOld($order->idAdvertiser);

$conv = new MessengerOld($handle, $supplier, __MSGR_CTXT_SUPPLIER_TC_ORDER__);
$conversations = $conv->getConversationFromReference($order->idCommande);

$sender = new BOUser($order->idSender);

 if (!$order->lastErrorMessage) { ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title><?php echo 'Commande par Techni-contact n°'.$_GET['idOrder'] ?></title>
<link href="<?php echo EXTRANET_URL ?>css/extranet.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="<?php echo EXTRANET_URL ?>ManageCookies.js"></script>
<style type="text/css">
  .bill_presentation { width: 100%; border: black solid 1px; border-collapse: collapse;}
  .bill_presentation tr td {text-align: center;  border: black solid 1px; font-size: 12px;}
  .bill_presentation tr td.instructions { text-align: left; padding: 5px 20px 5px 20px;}
  .note {font-size: 12px;}
  .bill_info { width: 100%; border: black solid 1px; border-collapse: collapse; font-size: 12px;}
  .bill_info thead tr td {text-align: center;  border: black solid 1px; font-weight: bold; }
  .bill_info tbody tr td {border-right: black solid 1px; padding: 0px 10px 0px 10px;}
  .bill_total { width: 50%; border: black solid 1px; border-collapse: collapse; font-size: 12px; float: right}
  .bill_total tbody tr td {border: black solid 1px; padding: 0px 10px 0px 10px; text-align: right}
  .bill_delivery_info{ width: 25%; border: black solid 1px; border-collapse: collapse; font-size: 12px; float: left}
  .bill_delivery_info thead tr td {text-align: center;  border: black solid 1px; font-weight: bold; }
  .bill_delivery_info tbody tr td {border: black solid 1px; padding: 0px 10px 0px 10px; }
  .bill_table {font-size: 12px;}
  .zero { clear: both; }
</style>
</head>
<body>

<div class="centre bloc">
	<div style="text-align: center;">
          <img src="http://www.techni-contact.com/media/images/logo_TC_tout_pour_les_pros.jpg" alt="Techni-contact"/>
	</div>
<br />
  <div class="commande">
    <table class="bill_info">
      <thead>
        <tr>
          <td><strong>Donneur d'ordre</strong></td>
          <td><strong>Commande passée à la société</strong></td>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <strong>Techni-Contact / M.D2i</strong><br />
  253 rue Gallieni<br />
  F-92774 BOULOGNE BILLANCOURT cedex<br />
  S.A.S. au capital de 160 000 €<br />
  RCS Nanterre B 392 772 497<br />
  Tva Intra. : FR12 392 772 497<br />
  <a href="http://www.techni-contact.com">http://www.techni-contact.com</a>
          </td>
          <td style="text-align: right;">
            <strong><?php echo $supplier->nom1 ?></strong><br />
            <strong><?php echo $supplier->adresse1 ?></strong><br />
            <strong><?php echo $supplier->adresse2 ?></strong><br />
            <strong><?php echo $supplier->cp.' '.$supplier->ville ?></strong><br />
            <strong><?php echo $supplier->pays ?></strong><br />
            <strong>N° de commande : </strong><?php echo $_GET['idOrder'] ?>
          </td>
        </tr>
        </tbody>
    </table>
    <h1>Commande</h1>
    <table class="bill_presentation">
      <tr>
        <td><strong>Numéro</strong></td>
        <td>Date</td>
        <td>Contact</td>
        <td>Téléphone</td>
      </tr>
      <tr>
        <td><?php echo $_GET['idOrder'] ?></td>
        <td><?php echo date('d/m/Y à H:i:s', $order->timestampIMS) ?></td>
        <td><?php echo $sender->name ?></td>
        <td><?php echo $sender->phone ?></td>
      </tr>
      <tr>
        <td colspan="4" class="instructions">Instructions particulières : <?php echo $order->mailComment ?></td>
      </tr>
    </table>
    <br />
    <div class="note"><strong>Important : </strong> merci de préciser le numéro de commande sur votre facture. Dans le cas contraire, votre facture ne pourra pas être prise en compte.
    </div>
    <br />
    <br />
    <table class="bill_info">
      <thead>
        <tr>
          <td style="width : 15%">Votre Référence</td>
          <td style="width : 40%">Désignation</td>
          <td style="width : 5%">Qté</td>
          <td style="width : 20%">Montant
unitaire HT
</td>
          <td style="width : 20%">Montant total HT</td>
        </tr>
      </thead>
      <tbody>
        <?php
        $Price2HT = 0;
        $Price2TTC = 0;
                              
        foreach($order->items as $item){
          $desc = $item["label"];
          if(!empty($item["customCols"])){
            $desc = $desc.' - ';
            $a = 0;
            foreach ($item["customCols"] as $cle => $valeur){
              $desc .= $a!=0 ? ', ' : '';
              $desc .= $cle.': '.$valeur;
              $a++;
            }
          }
          ?>

        <tr>
          <td style="text-align : center"><?php echo $item['refSupplier'] ?></td>
          <td style="padding: 0 10 0 10"><?php echo $desc ?></td>
          <td style="text-align : center"><?php echo $item['quantity'] ?></td>
          <td style="text-align : center"><?php echo sprintf("%0.2f", $item['price2']) ?> €</td>
          <td style="text-align : center"><?php echo sprintf("%0.2f", $item['price2']*$item['quantity']) ?> €</td>
        </tr>
        <?php

        // total amounts
        $Price2HT += $item["price2"]*$item["quantity"];
        $Price2TTC += ($item["price2"]*$item["quantity"])*$item["tauxTVA"]/100;
        }

        $fdp_ordre = $order->fdpOrdreHT ? $order->fdpOrdreHT : 0;
        $fdp_ordre_TVA = $order->fdpOrdreTTC ? $order->fdpOrdreTTC : 0;
        ?>
      </tbody>
    </table>
    <br />

    <table class="bill_delivery_info">
      <thead>
        <tr>
          <td>Livraison directe à cette adresse</td>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <?php if($order->coord["coord_livraison"]){?>
            <strong><?php echo $order->coord['societe_l'] ?></strong><br />
            <?php echo $order->coord['nom_l'].' '.$order->coord['prenom_l'] ?><br />
            <?php echo $order->coord['adresse_l'] ?><br />
            <?php echo $order->coord['complement_l'] ?><br />
            <?php echo $order->coord['ville_l'].' '.$order->coord['cp_l'] ?><br />
            <?php echo $order->coord['pays_l'] ?><br />
            <?php echo $order->coord['tel2'] ? $order->coord['tel2'] : $order->coord['tel1'] ?><br />
            <?php echo to_entities($order->coord['infos_sup_l']) ?><br />
            <?php }else{?>
            <strong><?php echo $order->coord['societe'] ?></strong><br />
            <?php echo $order->coord['nom'].' '.$order->coord['prenom'] ?><br />
            <?php echo $order->coord['adresse'] ?><br />
            <?php echo $order->coord['complement'] ?><br />
            <?php echo $order->coord['ville'].' '.$order->coord['cp'] ?><br />
            <?php echo $order->coord['pays'] ?><br />
            <?php echo $order->coord['tel1'] ?><br />
            <?php echo to_entities($order->coord['infos_sup']) ?><br />
            <?php }?>
          </td>
        </tr>
      </tbody>
    </table>


    <table class="bill_total">
      <tbody>
        <tr>
          <td style="width : 50%">Sous-total HT</td>
          <td style="width : 50%"><strong><?php echo sprintf("%0.2f", $order->totalOrdreHT-$fdp_ordre) ?> €</strong></td>
        </tr>
        <tr>
          <td style="width : 50%">Frais en sus</td>
          <td style="width : 50%"><strong><?php echo sprintf("%0.2f", $fdp_ordre) ?> €</strong></td>
        </tr>
        <tr>
          <td style="width : 50%">Total HT</td>
          <td style="width : 50%"><strong><?php echo sprintf("%0.2f", $order->totalOrdreHT) ?> €</strong></td>
        </tr>
        <tr>
          <td style="width : 50%">Montant TVA</td>
          <td style="width : 50%"><strong><?php echo sprintf("%0.2f", $Price2TTC+$fdp_ordre_TVA) ?> €</strong></td>
        </tr>
        <tr>
          <td style="width : 50%">Total TTC</td>
          <td style="width : 50%"><strong><?php echo sprintf("%0.2f", $order->totalOrdreTTC) ?> €</strong></td>
        </tr>
      </tbody>
    </table>
    <div class="zero"></div>
    <br />
    <br />
    <div class="footer"><strong>Comment contacter le service  achat?</strong> :<br />
      <br />
      Envoyez nous un message depuis la fiche commande disponible sur votre extranet : <a href="https://secure.techni-contact.com/fr/extranet/">https://secure.techni-contact.com/fr/extranet/</a>
    </div>
  </div>
  <script type="text/javascript">
<!--

window.print();

//-->
</script>
</div>
</body>
</html>

<?php } ?>
