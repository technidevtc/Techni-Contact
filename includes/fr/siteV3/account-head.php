<?php
/*
 * $accountCustom initialized in includes/fr/siteV3/breadcrumbs.php
 */
$customerInfos = $user->getCoordFromArray();
$customerInfos["titre"] = CustomerUser::getTitle($customerInfos["titre"]);

$mslt = Doctrine_Query::create() // mini store list from typology
  ->select('ms.id, ms.ref_name')
  ->from('MiniStores ms')
  ->innerJoin('ms.activity_sector_surqualifications ass')
  ->where('ass.qualification = ?', $user->secteur_qualifie)
  ->fetchArray();
foreach ($mslt as $k => &$ms) {
  $ms['pic'] = MiniStores::getPic($ms['id'], 'vignette');
  $ms['url'] = MiniStores::getUrl($ms['id'], $ms['ref_name']);
  if (empty($ms['pic']))
    unset($mslt[$k]);
}
unset($ms);
?>
<div class="account white-bg" id="body">
  <div class="left-account-panel">
    <div class="account-left-menu">
      <div class="account-left-menu-title"><a href="index.html">Tableau de bord du compte</a></div>
      <ul>
        <li><a <?php if($accountCustom == 'demandes'){ ?>class="currentPage" <?php } ?>href="lead-list.html">Mes demandes</a></li>
        <li><a <?php if($accountCustom == 'order'){ ?>class="currentPage" <?php } ?>href="order-list.html">Mes commandes</a></li>
        <li><a <?php if($accountCustom == 'devis'){ ?>class="currentPage" <?php } ?>href="pdfestimate-list.html">Mes devis</a></li>
        <li class="product-saves"><a <?php if($accountCustom == 'sauvegardes'){ ?>class="currentPage" <?php } ?>href="saved-products-list.html">Mes produits sauvegardés</a></li>
        <li><a <?php if($accountCustom == 'infos'){ ?>class="currentPage" <?php } ?>href="infos.html">Mes coordonnées</a></li>
		<li><a href="promouvoir-mon-activite.html">Promouvoir mon activité </a></li>
        <!--<li><a <?php if($accountCustom == 'contact'){ ?>class="currentPage" <?php } ?><a href="contact-form.html">Contact</a></li> -->
      </ul>
     <?php if (!empty($mslt)) : ?>
      <div class="mini-stores">
        <div class="blue-smaller-title">Ceci devrait vous intéresser...</div>
        <ul>
         <?php foreach ($mslt as $ms) : ?>
          <li><a href="<?php echo $ms['url'] ?>"><img src="<?php echo $ms['pic'] ?>" alt="" /></a></li>
        <?php endforeach ?>
        </ul>
      </div>
     <?php endif ?>
      <div id="account-recommended-products-block" class="account-recommended-products">
        <div class="blue-smaller-title">Cela devrait vous intéresser </div>
        <ul></ul>
        <script type="text/javascript">
          HN.TC.GetNuukikRecommendedProducts("account-recommended-products-block", [126, "users", "<?php echo $user->id ?>", "recommendation"], "list", null, function(){
            window._gaq && _gaq.push(['_trackEvent', 'Nuukik', 'Reco compte client FO', $(this).closest(".reco-pdt-block").data("pdt").infos.name]);
          });
        </script>
      </div>
    </div>
  </div>
