<?php

/*================================================================/

 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com

 Auteur : OD pour Hook Network SARL - http://www.hook-network.com
 Date de création : 14 février 2011

 Fichier : /secure/fr/manager/order_delivery_bill_print.php
 Description : Aperçu bon de livraison

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

if (!isset($_GET['idOrder']) || !preg_match('/^[1-9]{1}[0-9]{0,8}\-[1-9]{1}[0-9]{0,8}$/', $_GET['idOrder']))
{ 
    exit();
}
$title = 'Aperçu du bon de livraison n°'.$_GET['idOrder'];

$order = & new OrderOld($handle ,$_GET['idOrder']);

$sender = new BOUser($order->idSender);

$ste = $order->coord["coord_livraison"] ? $order->coord["societe_l"] : $order->coord["societe"];
$nomPrenom = $order->coord["coord_livraison"] ? $order->coord["nom_l"].' '.$order->coord["prenom_l"] : $order->coord["nom"].' '.$order->coord["prenom"];
$adresse = $order->coord["coord_livraison"] ? $order->coord["adresse_l"].' '.$order->coord["complement_l"] : $order->coord["adresse"].' '.$order->coord["complement"];
$cpLoca = $order->coord["coord_livraison"] ? $order->coord["cp_l"].' '.$order->coord["ville_l"] : $order->coord["cp"].' '.$order->coord["ville"];
$pays = $order->coord["coord_livraison"] ? $order->coord["pays_l"] : $order->coord["pays"];
$tel = $order->coord["coord_livraison"] ? $order->coord["tel2"] : $order->coord["tel1"];

 if (!$order->lastErrorMessage) { ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title><?php echo TITLE ?> - <?php print($title) ?></title>
<link href="<?php echo EXTRANET_URL ?>css/extranet.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="<?php echo EXTRANET_URL ?>ManageCookies.js"></script>
<style type="text/css">
  .bill_presentation { width: 100%; border: black solid 1px; border-collapse: collapse;}
  .bill_presentation tr td {text-align: center;  border: black solid 1px; font-size: 12px;}
  .bill_presentation tr td.instructions { text-align: left; padding: 5px 20px 5px 20px;}
  .note {font-size: 12px;}
  .bill_info { width: 100%; border: black solid 1px; border-collapse: collapse;  font-size: 12px;}
  .bill_info thead tr td {text-align: center;  border: black solid 1px; font-weight: bold;}
  .bill_info tbody tr td {border-right: black solid 1px; padding: 0px 10px 0px 10px;}
  .bill_table {font-size: 12px;}
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
          <td><strong>Adresse de l'expéditeur</strong></td>
          <td><strong>Adresse de livraison</strong></td>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            Techni-Contact / M.D2i<br />
  253 rue Gallieni<br />
  F-92774 BOULOGNE BILLANCOURT cedex<br />
  01 55 60 29 29<br />
  S.A.S. au capital de 160 000 &euro;<br />
  RCS Nanterre B 392 772 497<br />
  Tva Intra. : FR12 392 772 497<br />
  <a href="http://www.techni-contact.com">http://www.techni-contact.com</a>
          </td>
          <td style="text-align: right;">
            <?php echo $ste ?><br />
            <?php echo $nomPrenom ?><br />
            <?php echo $adresse ?><br />
            <?php echo $cpLoca ?><br />
            <?php echo $pays ?><br />
            <?php echo $tel ?>
          </td>
        </tr>
      </tbody>
    </table>
    <h1>Bon de livraison</h1>
    <table class="bill_presentation">
      <tr>
        <td><strong>Numéro</strong></td>
        <td>Date</td>
        <td>Contact</td>
        <td>Téléphone</td>
      </tr>
      <tr>
        <td><?php echo 'BL-'.$_GET['idOrder'] ?></td>
        <td><?php echo date('d/m/Y à H:i:s') ?></td>
        <td><?php echo $nomPrenom ?></td>
        <td><?php echo $tel ?></td>
      </tr>
      <tr>
        <td colspan="4" class="instructions">Instructions de livraison : <?php echo to_entities($order->coord["infos_sup_l"]) ?></td>
      </tr>
    </table>
    <br />
    <table class="bill_info">
      <thead>
        <tr>
          <td style="width : 20%">Référence</td>
          <td style="width : 50%">Désignation</td>
          <td style="width : 10%">Qté</td>
          <td style="width : 20%">Qté exp.</td>
        </tr>
      </thead>
      <tbody>
        <?php foreach($order->items as $item){
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
          <td style="text-align : center"><?php echo $item['idTC'] ?></td>
          <td style="padding: 0 10 0 10"><?php echo $desc ?></td>
          <td style="text-align : center"><?php echo $item['quantity'] ?></td>
          <td></td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
         <br />
      <br />
        <div class="note"><strong>Information relative à la réception de marchandise</strong> :<br />
      <br />
      Le client s'engage, après ouverture et <strong>vérification du contenu du ou des colis en présence du transporteur</strong>, à signer le récépissé de livraison
      présenté par le livreur. En cas de problème, le client portera toutes réserves correspondantes aux observations constatées sur la feuille
      d'émargement fera co-signer le livreur et conservera un exemplaire du document. Les réserves portées sur le bon de transport doivent
      impérativement être confirmées (auprès du transporteur) par courrier recommandé avec avis de réception dans les 3 jours.<br />
En cas d'avarie de transport, le client peut refuser le produit endommagé au livreur et indiquer " REFUS POUR AVARIE" et détailler avec précisions
le type d'avarie: Exemple: "palette défilmée", "carton ouvert", "marchandise éclatée"....sur la feuille d'émargement faute de quoi tout recours contre
ce dernier serait impossible. Par la signature du récépissé de livraison en dehors de réserves précises, et l'acceptation des produits, le client reconnait
avoir reçu la marchandise dans un état lui donnant toute satisfaction. <strong>Dès lors, toute réclamation liée à l'état des produits livrés ne pourra être reçue</strong>.


    </div>
    <br />
    <br />
    <div class="footer"><strong>Comment contacter le service client ?</strong> :<br />
      <br />
Envoyez nous un message depuis votre compte client sur www.techni-contact.com (rubrique « S'identifier » en haut de page) ou sav@techni-contact.com
    </div>
  </div>
  <script type="text/javascript">
<!--


//-->
</script>
</div>
</body>
</html>
<?php } ?>