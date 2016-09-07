<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$user = new BOUser();
if (!$user->login()) {
	echo to_entities("Votre session a expirée, veuillez vous identifier à nouveau après avoir rafraichi votre page");
	exit();
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id)
  exit();

$so = Doctrine_Query::create()
    ->select('so.*,
              s.*,
              o.*,
              sos.*,
              ol.*')
    ->from('SupplierOrder so')
    ->leftJoin('so.supplier s')
    ->leftJoin('so.order o')
    ->leftJoin('so.sender sos')
    ->leftJoin('o.lines ol')
    ->where('so.id = ?', $id)
    ->andWhere('ol.sup_id = so.sup_id')
    ->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);
if (empty($so['id']))
  exit();

$siteName = $website_origin_list[$so['order']['website_origin']];
if ($so['order']['website_origin'] == "MOB")
  $logo = SECURE_URL."ressources/images/logo-website-mobaneo.jpg";
else
  $logo = SECURE_URL."ressources/images/logo_TC.jpg";
$siteUrl = $website_origin_url_list[$so['order']['website_origin']];

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr" xml:lang="fr">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <title><?php echo "Commande par ".$siteName." n°".$so['rid'] ?></title>
  <script type="text/javascript" src="<?php echo EXTRANET_URL ?>ManageCookies.js"></script>
  <style type="text/css">
    body { margin: 0; font: normal 10px arial, helvetica, sans-serif }
    img { border: 0 }
    .bill_presentation { width: 100%; border: black solid 1px; border-collapse: collapse }
    .bill_presentation tr td {text-align: center;  border: black solid 1px; font-size: 12px }
    .bill_presentation tr td.instructions { text-align: left; padding: 5px 20px 5px 20px }
    .note {font-size: 12px }
    .bill_info { width: 100%; border: black solid 1px; border-collapse: collapse; font-size: 12px }
    .bill_info thead tr td {text-align: center;  border: black solid 1px; font-weight: bold }
    .bill_info tbody tr td {border-right: black solid 1px; padding: 0px 10px 0px 10px }
    .bill_total { width: 50%; border: black solid 1px; border-collapse: collapse; font-size: 12px; float: right }
    .bill_total tbody tr td {border: black solid 1px; padding: 0px 10px 0px 10px; text-align: right }
    .bill_delivery_info{ width: 25%; border: black solid 1px; border-collapse: collapse; font-size: 12px; float: left }
    .bill_delivery_info thead tr td {text-align: center;  border: black solid 1px; font-weight: bold  }
    .bill_delivery_info tbody tr td {border: black solid 1px; padding: 0px 10px 0px 10px  }
    .bill_table {font-size: 12px;}
    .zero { clear: both }
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
            <td><strong>Donneur d'ordre</strong></td>
            <td><strong>Commande passée à la société</strong></td>
          </tr>
        </thead>
        <tbody>
          <tr>
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
            <td style="text-align: right;">
              <strong><?php echo to_entities($so['supplier']['nom1']) ?></strong><br />
              <?php echo to_entities($so['supplier']['adresse1']) ?><br />
              <?php echo to_entities($so['supplier']['adresse2']) ?><br />
              <?php echo to_entities($so['supplier']['cp']." ".$so['supplier']['ville']) ?><br />
              <?php echo to_entities($so['supplier']['pays']) ?><br />
              <strong>N° de commande : <?php echo $so['rid'] ?></strong>
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
          <td><?php echo $so['rid'] ?></td>
          <td><?php echo date('d/m/Y à H:i:s', $so['mail_time']) ?></td>
          <td><?php echo to_entities($so['sender']['name']) ?></td>
          <td><?php echo to_entities($so['sender']['phone']) ?></td>
        </tr>
        <tr>
          <td colspan="4" class="instructions">Instructions particulières : <?php echo to_entities($so['mail_comment']) ?></td>
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
            <td style="width: 15%">Votre Référence</td>
            <td style="width: 40%">Désignation</td>
            <td style="width: 5%">Qté</td>
            <td style="width: 20%">Montant unitaire HT</td>
            <td style="width: 20%">Montant total HT</td>
          </tr>
        </thead>
        <tbody>
         <?php foreach ($so['order']['lines'] as $line) : ?>
          <tr>
            <td style="text-align: center"><?php echo $line['sup_ref'] ?></td>
            <td style="padding: 0 10px"><?php echo to_entities($line['desc']).(empty($line['sup_comment']) ? "" : "<br />".to_entities($line['sup_comment'])) ?></td>
            <td style="text-align: center"><?php echo $line['quantity'] ?></td>
            <td style="text-align: center"><?php echo sprintf("%.02f", $line['pau_ht']) ?> €</td>
            <td style="text-align: center"><?php echo sprintf("%.02f", $line['pau_ht']*$line['quantity']) ?> €</td>
          </tr>
         <?php if ($line['et_ht'] > 0) : ?>
          <tr>
            <td></td>
            <td style="padding: 0 10px">Éco participation</td>
            <td></td>
            <td style="text-align: center"><?php echo sprintf("%.02f", $line['et_ht']) ?> €</td>
            <td style="text-align: center"><?php echo sprintf("%.02f", $line['et_total_ht']) ?> €</td>
          </tr>
         <?php endif ?>
         <?php endforeach ?>
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
              <strong><?php echo to_entities($so['order']['societe2']) ?></strong><br />
              <?php echo to_entities($so['order']['nom2']." ".$so['order']['prenom2']) ?><br />
              <?php echo to_entities($so['order']['adresse2']) ?><br />
              <?php echo to_entities($so['order']['cadresse2']) ?><br />
              <?php echo to_entities($so['order']['ville2']." ".$so['order']['cp2']) ?><br />
              <?php echo to_entities($so['order']['pays2']) ?><br />
              <?php echo to_entities($so['order']['tel2']) ?><br />
              <?php echo to_entities($so['order']['delivery_infos']) ?><br />
            </td>
          </tr>
        </tbody>
      </table>
      <table class="bill_total">
        <tbody>
          <tr>
            <td style="width : 50%">Sous-total HT</td>
            <td style="width : 50%"><strong><?php echo sprintf("%0.2f", $so['stotal_ht']) ?> €</strong></td>
          </tr>
          <tr>
            <td style="width : 50%">Frais en sus</td>
            <td style="width : 50%"><strong><?php echo sprintf("%0.2f", $so['fdp_ht']) ?> €</strong></td>
          </tr>
          <tr>
            <td style="width : 50%">Total HT</td>
            <td style="width : 50%"><strong><?php echo sprintf("%0.2f", $so['total_ht']) ?> €</strong></td>
          </tr>
          <tr>
            <td style="width : 50%">Montant TVA</td>
            <td style="width : 50%"><strong><?php echo sprintf("%0.2f", $so['total_tva']) ?> €</strong></td>
          </tr>
          <tr>
            <td style="width : 50%">Total TTC</td>
            <td style="width : 50%"><strong><?php echo sprintf("%0.2f", $so['total_ttc']) ?> €</strong></td>
          </tr>
        </tbody>
      </table>
      <div class="zero"></div>
      <br />
      <br />
      <div class="footer"><strong>Comment contacter le service achat?</strong> :<br />
        <br />
        Envoyez nous un message depuis la fiche commande disponible sur votre extranet : <a href="<?php echo EXTRANET_URL ?>"><?php echo EXTRANET_URL ?></a>
      </div>
    </div>
  </div>
  <script type="text/javascript">window.print();</script>
</body>
</html>