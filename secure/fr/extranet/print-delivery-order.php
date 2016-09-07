<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
require(ICLASS."ExtranetUser.php");

$db = DBHandle::get_instance();

require(EXTRANET.'head_autoconnect_uid_print.php');


$user = new ExtranetUser($db);
/*
if (!$user->login($login, $pass) || !$user->active) {
  header('Location: '.EXTRANET_URL.'login.html');
  exit();
}*/

$so_rid = filter_input(INPUT_GET, 'idOrder', FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => '/^\d+-\d+$/i'))) or die();
list($sup_id, $order_id) = explode("-", $so_rid, 2);
if ($sup_id != $user_id){}
  // exit();

$so = Doctrine_Query::create()
    ->select('so.*,
              s.*,
              o.*,
              ol.*')
    ->from('SupplierOrder so')
    ->leftJoin('so.supplier s')
    ->leftJoin('so.order o')
    ->leftJoin('o.lines ol')
    ->where('so.sup_id = ?', $sup_id)
    ->andWhere('so.order_id = ?', $order_id)
    ->andWhere('ol.sup_id = so.sup_id')
    ->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);
if (empty($so['id']))
  exit();

$siteName = $website_origin_list[$so['order']['website_origin']];
if ($so['order']['website_origin'] == "MOB") {
  $logo = SECURE_URL."ressources/images/logo-website-mobaneo.jpg";
  $sav = "sav@mobaneo.com";
} else if ($so['order']['website_origin'] == "TC"){
  $logo = SECURE_URL."ressources/images/logo_TC.jpg";
  $sav = "sav@techni-contact.com";
}else if ($so['order']['website_origin'] == "MER"){
  $logo = SECURE_URL."ressources/images/logo-website-mercateo.jpg";
  $sav = "service@mercateo.fr";
}
$siteUrl = $website_origin_url_list[$so['order']['website_origin']];

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr" xml:lang="fr">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
 <?php
	if ($so['order']['website_origin'] == "MER"){
		echo '<title>Numéro de commande Mercateo : '.$so['order']['alternate_id'].'</title>';
	}else{
		echo '<title>Aperçu du bon de livraison n° '.$so['rid'].'</title>';
	}
?>
  
  <style type="text/css">
    body { margin: 0; font: normal 12px verdana, helvetica, sans-serif }
    .bill_presentation { width: 100%; border: black solid 1px; border-collapse: collapse }
    .bill_presentation tr td {text-align: center;  border: black solid 1px }
    .bill_presentation tr td.instructions { text-align: left; padding: 5px 20px 5px 20px }
    .bill_info { width: 100%; border: black solid 1px; border-collapse: collapse }
    .bill_info thead tr td { text-align: center;  border: black solid 1px; font-weight: bold }
    .bill_info tbody tr td { border-right: black solid 1px; padding: 1px 10px }
  </style>
</head>
<body>
  <div class="centre bloc">
    <div style="text-align: center;">
      <img src="<?php echo $logo ?>" alt="<?php echo $siteName ?>" />
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
			<?php
				if (($so['order']['website_origin'] == "MOB") || ($so['order']['website_origin'] == "TC")) { ?>
			<td>
              <strong><?php echo $siteName ?> / M.D2i</strong><br />
              253 rue Gallieni<br />
              F-92774 BOULOGNE BILLANCOURT cedex<br />
              01 55 60 29 29<br />
              S.A.S. au capital de 160 000 €<br />
              RCS Nanterre B 392 772 497<br />
              Tva Intra. : FR12 392 772 497<br />
              <a href="<?php echo $siteUrl ?>"><?php echo $siteUrl ?></a>
            </td>	
			<?php	}else { ?>
			<td>
              <strong>Mercateo France SAS</strong><br />
              27, avenue de l’Opéra<br />
              75001 Paris<br />
              RCS Paris 539 293 902<br />
              TVA intra : FR94 539 293 902<br />
              www.mercateo.fr<br />
			  
              01 77 68 89 09<br />
              <a href="<?php echo $siteUrl ?>"><?php echo $siteUrl ?></a>
            </td>
            <?php } ?>
            <td style="text-align: right;">
              <strong><?php echo to_entities($so['order']['societe2']) ?></strong><br />
              <?php echo to_entities($so['order']['nom2']." ".$so['order']['prenom2']) ?><br />
              <?php echo to_entities($so['order']['adresse2']." ".$so['order']['cadresse2']) ?><br />
              <?php echo to_entities($so['order']['cp2']." ".$so['order']['ville2']) ?><br />
              <?php echo to_entities($so['order']['pays2']) ?><br />
              <?php echo to_entities($so['order']['tel2']) ?><br />
			<?php if ($so['order']['website_origin'] == "MER"){ ?>
			 <strong>N° de commande : <?php echo $so['order']['alternate_id'] ?></strong>
			<?php }else{ ?>
			 <strong>N° de commande : <?php echo $so['rid'] ?></strong>
			<?php } ?>
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
		<?php if ($so['order']['website_origin'] == "MER"){ ?>
			<td><?php echo "BL-".$so['order']['alternate_id']  ?></td>
		<?php }else{ ?>
			 <td><?php echo "BL-".$so['rid'] ?></td>
		<?php } ?>
          
          <td><?php echo date('d/m/Y à H:i:s') ?></td>
          <td><?php echo to_entities($so['order']['nom2']." ".$so['order']['prenom2']) ?></td>
          <td><?php echo to_entities($so['order']['tel2']) ?></td>
        </tr>
        <tr>
          <td colspan="4" class="instructions">Instructions de livraison : <?php echo to_entities($so['order']['delivery_infos']) ?></td>
        </tr>
      </table>
      <br />
      <table class="bill_info">
        <thead>
          <tr>
            <td style="width: 20%">Référence</td>
            <td style="width: 50%">Désignation</td>
            <td style="width: 10%">Qté</td>
            <td style="width: 20%">Qté exp.</td>
          </tr>
        </thead>
        <tbody>
         <?php foreach ($so['order']['lines'] as $line) : ?>
          <tr>
            <td style="text-align: center"><?php echo empty($line['pdt_ref_id']) ? "-" : $line['pdt_ref_id'] ?></td>
            <td style="padding: 0 10px"><?php echo to_entities($line['desc']) ?></td>
            <td style="text-align: center"><?php echo $line['quantity'] ?></td>
            <td></td>
          </tr>
         <?php endforeach ?>
        </tbody>
      </table>
      <br />
      <br />
      <div class="note">
        <strong>Information relative à la réception de marchandise</strong> :<br />
        <br />
		 <?php
	if($so['order']['website_origin'] == "MER"){  ?>
		Cette commande a été passée sur Mercateo.fr, la plate-forme d'approvisionnement pour professionnels et les
		CGV de Mercateo France SAS sappliquent. La facture vous sera envoyée séparément.<br />
		Notre service client se tient à votre disposition du lundi au vendredi, de 9h à 17h au <strong>01 77 68 89 09</strong> ou par e-mail à 
		<a href="mailto:service@mercateo.fr">service@mercateo.fr.</a><br />
		Merci de votre confiance et à bientôt sur Mercateo.fr

		
	<?php }else{ ?>
		Le client s'engage, après ouverture et <strong>vérification du contenu du ou des colis en présence du transporteur</strong>, à signer le récépissé de livraison
        présenté par le livreur. En cas de problème, le client portera toutes réserves correspondantes aux observations constatées sur la feuille
        d'émargement fera co-signer le livreur et conservera un exemplaire du document. Les réserves portées sur le bon de transport doivent
        impérativement être confirmées (auprès du transporteur) par courrier recommandé avec avis de réception dans les 3 jours.<br />
        En cas d'avarie de transport, le client peut refuser le produit endommagé au livreur et indiquer " REFUS POUR AVARIE" et détailler avec précisions
        le type d'avarie: Exemple: "palette défilmée", "carton ouvert", "marchandise éclatée"....sur la feuille d'émargement faute de quoi tout recours contre
        ce dernier serait impossible. Par la signature du récépissé de livraison en dehors de réserves précises, et l'acceptation des produits, le client reconnait
        avoir reçu la marchandise dans un état lui donnant toute satisfaction. <strong>Dès lors, toute réclamation liée à l'état des produits livrés ne pourra être reçue</strong>.		
	<?php } ?>
	   
      </div>
      <br />
      <br />
      <?php if ($so['order']['website_origin'] != "MER"){ ?>
	  <div class="footer"><strong>Comment contacter le service client ?</strong> : <a href="mailto:<?php echo $sav ?>"><?php echo $sav ?></a>
	  <?php } ?>
      </div>
    </div>
  </div>
  <script type="text/javascript">window.print();</script>
</body>
</html>