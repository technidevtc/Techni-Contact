<?php

/*================================================================/

  Techni-Contact V2 - MD2I SAS
  http://www.techni-contact.com

  Auteur : Hook Network SARL - http://www.hook-network.com
  Date de création : 20 décembre 2004

  Mises à jour :
    31 mai 2005 : = réécriture avec nouveau système des rangs
    29 octobre 2007 : Nettoyage code (norme XHTML) + ajout styles css + ajout gestion automatique des langues étrangères

  Fichier : /secure/manager/index.php
  Description : Accueil administration de l'application Web

/=================================================================*/

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$title  = 'Accueil';
$navBar = '<div align="center">MANAGER DE VOTRE SITE WEB TECHNI CONTACT</div>';

define('MD2I_ADMIN', true);

require(ADMIN."head.php");

$db = DBHandle::get_instance();

$res = $db->query("SELECT COUNT(id) FROM advertisers WHERE category != 1 AND deleted != 1", __FILE__, __LINE__);
list($nbAdvertisers) = $db->fetch($res);

$res = $db->query("SELECT COUNT(id) FROM advertisers WHERE category = 1 AND deleted != 1", __FILE__, __LINE__);
list($nbSuppliers) = $db->fetch($res);

$res = $db->query("SELECT COUNT(id) FROM products_fr WHERE active = 1 AND deleted != 1", __FILE__, __LINE__);
list($nbProducts) = $db->fetch($res);

$res = $db->query("SELECT COUNT(id) FROM families", __FILE__, __LINE__);
list($nbCategories) = $db->fetch($res);


if($user->rank == HOOK_NETWORK || $user->rank == COMMADMIN)
  $logs = & last15($handle);

if($user->rank != CONTRIB)
{
  if($result = & $handle->query("select id from products_add where type = 'c'", __FILE__, __LINE__))
  {
    if(($nb = $handle->numrows($result, __FILE__, __LINE__)) > 0)
    {
      $add = '- <a href="products/add_wait.php?' . $sid . '">' . $nb . ' en attente de validation de création</a>';
    }
  }
  
  if($result = & $handle->query("select id from products_add where type = 'm'", __FILE__, __LINE__))
  {
    if(($nb = $handle->numrows($result, __FILE__, __LINE__)) > 0)
    {
      $mod = '<br /> - <a href="products/edit_wait.php?' . $sid . '">' . $nb . ' en attente de validation de modification</a>';
    }
  }
    
  $extranet = '';
  $from_extranet = false;
  if($result = & $handle->query("select id from products_add_adv where type = 'c' and reject = 0", __FILE__, __LINE__))
  {
    if(($nb = $handle->numrows($result, __FILE__, __LINE__)) > 0)
    {
      $from_extranet = true;
      $extranet = '<br /> - <a href="products/add_wait.php?' . $sid . '&from=adv">' . $nb . ' en attente de validation de création</a>';
    }
  }
  
  if($result = & $handle->query("select id from products_add_adv where type = 'm' and reject = 0", __FILE__, __LINE__))
  {
    if(($nb = $handle->numrows($result, __FILE__, __LINE__)) > 0)
    {
      if(!$from_extranet)
      {
        //                $extranet .= '<br>Extranet :';
        $from_extranet = true;
      }
      $extranet .= '<br /> - <a href="products/edit_wait.php?' . $sid . '&from=adv">' . $nb . ' en attente de validation de modification</a>';
    }
  }
  
  if($result = & $handle->query("select id from sup_requests", __FILE__, __LINE__))
  {
    if(($nb = $handle->numrows($result, __FILE__, __LINE__)) > 0)
    {
      if(!$from_extranet)
      {
        //              $extranet .= '<br>Extranet :';
      }
      $extranet .= '<br /> - <a href="products/sup_wait.php?' . $sid . '">' . $nb . ' en attente de validation de suppression</a>';
    }
  }
  
  $extranet_a = '';
  $from_extranet_a = false;
  if($result = & $handle->query("select id from advertisers_adv", __FILE__, __LINE__))
  {
    if(($nb = $handle->numrows($result, __FILE__, __LINE__)) > 0)
    {
      $from_extranet_a = true;
      $extranet_a = '<br /> - <a href="advertisers/edit_wait.php?' . $sid . '&from=adv">' . $nb . ' en attente de validation de modification</a>';
    }
  }
}

if (!isset($add)){ $add = ''; }
if (!isset($mod)){ $mod = ''; }
if (!isset($del)){ $del = ''; }
if ($user->rank == HOOK_NETWORK) {
  include(ADMIN."hook.php");
?>
<div class="titreRestricted">Zone d'action pour les techniciens</div>
<br />
<div class="bg">
<?php
  if(isset($_GET['action'])) {
    switch($_GET['action']) {
      case RESET_SQL_LOG :
        if(resetLog(SQL_LOG_FILE)) { print '<div class="confirm">Fichier ' . SQL_LOG_FILE . ' remis à zéro.</div>'; }
        else { print '<div class="error">Erreur lors de la remise à zéro du fichier ' . SQL_LOG_FILE . '.</div>'; }
        break;

      case CAT_SQL_LOG :
        if(!catLog(SQL_LOG_FILE)) { print '<div class="error">Fichier Log ' . SQL_LOG_FILE . ' vide.</div>'; }
        break;

      case RESET_PHP_LOG :
        if(resetLog(PHP_LOG_FILE)) { print '<div class="confirm">Fichier ' . PHP_LOG_FILE . ' remis à zéro.</div>'; }
        else { print '<div class="error">Erreur lors de la remise à zéro du fichier ' . PHP_LOG_FILE . '.</div>'; }
        break;

      case CAT_PHP_LOG :
        if(!catLog(PHP_LOG_FILE)) { print '<div class="error">Fichier ' . PHP_LOG_FILE . ' vide.</div>'; }
        break;

      default :
      print '<div class="error">Action inconnue.</div>';
    }
    print('<br /><br />');
  } // fin isset action
?>
  <ul>
    <li><a href="index.php?action=<?php echo CAT_SQL_LOG . '&' . $sid ?>">Afficher le Log SQL</a> / <a href="index.php?action=<?php echo RESET_SQL_LOG . '&' . $sid ?>">Remettre à zéro le Log SQL</a></li>
    <li><a href="index.php?action=<?php echo CAT_PHP_LOG . '&' . $sid ?>">Afficher le Log PHP</a> / <a href="index.php?action=<?php echo RESET_PHP_LOG . '&' . $sid ?>">Remettre à zéro le Log PHP</a></li>
    <li><a href="hook.php?<?php echo $sid ?>&action=sql">Optimisation des bases SQL</a></li>
    <li><a href="hook.php?<?php echo $sid ?>&action=idr">Intégrité des relations</a></li>
  </ul>
</div>
<br/>
<br/>
<?php
}  // fin droits gestion configuration
?>

      <div class="content-box">
        <div class="two-column">
          <div class="column">
           <?php if ($userPerms->has($fntByName["m-comm--sm-leads"], "r")) { ?>
            <div class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
              <div class="portlet-header ui-widget-header">Dernières demandes de contacts</div>
              <div class="portlet-content">
                <table class="item-list" cellspacing="0" cellpadding="0">
                  <thead>
                    <tr>
                      <th class="date">Heure</th>
                      <th>Nom produit</th>
                      <th>Société</th>
                    </tr>
                  </thead>
                  <tbody>
                <?php for ($i=0,$hlc=count($hleadList); $i<$hlc && $i<5; $i++) { $hlead = $hleadList[$i];
                          if($hlead['date'] >= $morning && $hlead['timestamp'] <= $evening){ // leads of the day only
                ?>
                    <tr onclick="document.location.href='<?php echo ADMIN_URL."contacts/lead-detail.php?id=".$hlead["id"] ?>'">
                      <td class="date"><?php echo date("H:i", $hlead["date"]) ?></td>
                      <td><?php echo $hlead["pdt_name"] ?></td>
                      <td><?php echo $hlead["company"] ?></td>
                    </tr>
                <?php
                  }
                }
                ?>
                  </tbody>
                </table>
              </div>
            </div>
           <?php } ?>
            <div class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
              <div class="portlet-header ui-widget-header">Données clé Production</div>
              <div class="portlet-content">
                <p>
                  <strong><?php echo $nbProducts ?></strong> fiches produits <br/>
                  <strong><?php echo $nbCategories ?></strong> familles <br/>
                  <strong><?php echo $nbSuppliers ?></strong> fournisseurs<br/>
                  <strong><?php echo $nbAdvertisers ?></strong> annonceurs
                </p>
              </div>
            </div>
           <?php if ($userPerms->has($fntByName["m-prod--sm-extranet"], "r")) { ?>
            <div class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
              <div class="portlet-header ui-widget-header">Données extranet</div>
              <div class="portlet-content">
                <p>
                 <?php echo $add .' &nbsp; '. $mod .' &nbsp; '. $del . $extranet  ?>
                </p>
              </div>
            </div>
           <?php } ?>
            <div class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
              <div class="portlet-header ui-widget-header">Tutoriels</div>
              <div class="portlet-content">
                <p>
                  <a href="#">Lien vers tutos</a>
                </p>
              </div>
            </div>
          </div>
          <div class="column column-right">
           <?php if ($userPerms->has($fntByName["m-prod--sm-products"], "r")) { ?>
            <div class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
              <div class="portlet-header ui-widget-header">Fil activité</div>
              <div class="portlet-content">
                <div class="hastable">
                  <?php
                  // getting id ordered by timestamp then getting full information is a lot faster it seems
                  $res = $db->query("SELECT id FROM products ORDER BY timestamp DESC LIMIT 0,5", __FILE__, __LINE__);
                  if ($db->numrows($res,__FILE__,__LINE__) > 0) {
                    while (list($pdtId) = $db->fetch($res))
                      $pdtIdList[] = $pdtId;
                    $res = $db->query("
                      SELECT p.id, pfr.name, pfr.ref_name, pfr.fastdesc, pf.idFamily AS cat_id, a.id AS adv_id, a.nom1 AS adv_name
                      FROM products p
                      INNER JOIN products_fr pfr ON p.id = pfr.id
                      LEFT JOIN products_families pf ON p.id = pf.idProduct
                      LEFT JOIN advertisers a ON p.idAdvertiser = a.id
                      WHERE p.id IN (".implode(",",$pdtIdList).")
                      GROUP BY p.id
                      ORDER BY p.timestamp DESC",__FILE__,__LINE__) ?>
                    <ul>
                    <?php while ($pdt = $db->fetchAssoc($res)) {
                      $fo_pic_url = is_file(PRODUCTS_IMAGE_INC."thumb_big/".$pdt["id"]."-1".".jpg") ? PRODUCTS_IMAGE_SECURE_URL."thumb_big/".$pdt["id"]."-1".".jpg" : PRODUCTS_IMAGE_SECURE_URL."no-pic-thumb_big.gif" ?>
                      <li style="clear: both; margin-top: 5px;">
                        <a href="<?php echo URL."produits/".$pdt["cat_id"]."-".$pdt["id"]."-".$pdt["ref_name"].".html" ?>" target="_blank"><img style="margin-top: -5px; height: 40px; width: 55px; margin-right: 5px; float: left; overflow: hidden;" src="<?php echo $fo_pic_url ?>" border="0"></a>
                        <a href="products/edit.php?id=<?php echo $pdt["id"] ?>"><?php echo to_entities($pdt["name"]) ?></a><br/>
                        <?php echo $pdt["fastdesc"] ?><br/>
                        <a href="<?php echo ADMIN_URL."advertisers/edit.php?id=".$pdt["adv_id"] ?>"><?php echo to_entities($pdt["adv_name"]) ?></a>
                        <br/>
                      <li/>
                    <?php } ?>
                  </ul>
                  <?php } ?>
                </div>
              </div>
            </div>
           <?php } ?>
            <div class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
              <div class="portlet-header ui-widget-header">Alertes Google</div>
              <div class="portlet-content">
                <p id="rss-content"></p>
                <script type="text/javascript">$("#rss-content").load("<?php echo ADMIN_URL."rss.php" ?>");</script>
              </div>
            </div>
          </div>
        </div>
        <div class="clear"></div>

      </div>
      <!-- End .content-box -->
    

<?php require(ADMIN."tail.php") ?>
