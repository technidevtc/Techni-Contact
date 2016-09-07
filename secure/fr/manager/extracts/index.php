<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$db = DBHandle::get_instance();

$title = $navBar = "Extracts personnalisés";
require(ADMIN."head.php");

?>
<style type="text/css">
  ul.extracts { padding: 0 0 0 40px; list-style-type: disc }
  ul.extracts li { margin: 0 0 10px; list-style-type: disc }
</style>
<div class="titreStandard">Liste des extracts personnalisés</div>
<br/>
<ul class="extracts">
  <li>
    <div class="extract">Revenu lead par familles 3 et annonceur</div>
    <form action="single_leads_per_cat3_per_partner.php" method="post">
      <input type="submit" value="Télécharger l'extract" />
    </form>
  </li>
  <li>
    <div class="extract">Liste des partenaires (id/nom/type/mail/mail_com/nb_pdt)</div>
    <form action="partners_list.php" method="post">
      <input type="submit" value="Télécharger l'extract" />
    </form>
  </li>
  <li>
    <div class="extract">Liste des annonceurs n'ayant pas de facturation personnalisées</div>
    <form action="advertisers_no_ic_list.php" method="post">
      <input type="submit" value="Télécharger l'extract" />
    </form>
  </li>
  <li>
    <div class="extract">Liste des annonceurs avec leur type de facturation</div>
    <form action="advertisers_is_list.php" method="post">
      <input type="submit" value="Télécharger l'extract" />
    </form>
  </li>
  <li>
    <div class="extract">Ensemble des contacts issus des formulaires de contact génériques/compte client</div>
    <form action="contacts_form_list.php" method="post">
      <input type="submit" value="Télécharger l'extract" />
    </form>
  </li>
  <li>
    <div class="extract">Taux de transfo produits</div>
    <form action="products_stats_list.php" method="post">
      <input type="submit" value="Télécharger l'extract" />
    </form>
  </li>
  <li>
    <div class="extract">Ensemble des produits présents dans 2 familles minimum</div>
    <form action="products_several_families.php" method="post">
      <input type="submit" value="Télécharger l'extract" />
    </form>
  </li>
  <li>
    <div class="extract">Ensemble des produit ayant des descriptions identiques <i style="color: red">(Cette procédure peut prendre plusieurs dizaines de secondes, attendre la création du fichier xls)</i></div>
    <form action="products_same_desc.php" method="post">
      <input type="submit" value="Télécharger l'extract" />
    </form>
  </li>
</ul>

<?php require(ADMIN."tail.php") ?>