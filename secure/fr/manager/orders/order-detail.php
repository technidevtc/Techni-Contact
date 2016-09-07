<?php
if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

$db = DBHandle::get_instance();
$user = new BOUser();

if (!$user->login()) {
	header('Location: '.ADMIN_URL.'login.html');
	exit();
}

try {

if (!$user->get_permissions()->has("m-comm--sm-orders", "r"))
  throw new Exception("Vous n'avez pas les droits adéquats pour réaliser cette opération.");

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_STRING);
$product_id = filter_input(INPUT_GET, 'product_id', FILTER_VALIDATE_INT);

if (empty($id))
  throw new Exception("Numéro de commande invalide.");

if ($id == 'new') { // new order
  $o = new Order();
  
  if ($client_id = filter_input(INPUT_GET, 'client_id', FILTER_VALIDATE_INT)) {
    if (!$o->importFromClient($client_id))
      throw new Exception("Le client ayant pour identifiant ".$client_id." n'existe pas.");
    $o->save();
    header('Location: order-detail.php?id='.$o->id.($product_id?"&product_id=".$product_id:""));
    exit();
  } else {
    $o->save();
    header('Location: order-detail.php?id='.$o->id.($product_id?"&product_id=".$product_id:""));
    exit();
  }
}



$q = Doctrine_Query::create()
    ->select('o.*,
              ol.*,
              IF(0,ol.id,ols.nom1) AS sup_name,
              so.*,
              sos.name,
              IF(0,ol.id,olpfr.ref_name) AS pdt_ref_name,
              IF(0,ol.id,olpf.id) AS pdt_cat_id,
              cu.login as created_user_login,
              uu.login as updated_user_login,
              iu.login as in_charge_user_login,
              vu.login as validated_user_login,
              ou.login as oked_user_login,
              l.origin as lead_source')
    ->from('Order o')
    ->leftJoin('o.lines ol')            // lines
    ->leftJoin('ol.supplier ols')       // direct relation to the supplier of this line
    ->leftJoin('o.supplier_orders so')  // supplier orders
    ->leftJoin('so.sender sos')         // bouser that sent the supplier order
    ->leftJoin('ol.product olp')        // product
    ->leftJoin('olp.product_fr olpfr')  // "
    ->leftJoin('olp.families olpf')     // "
    ->leftJoin('o.created_user cu')     // bousers
    ->leftJoin('o.updated_user uu')     // "
    ->leftJoin('o.in_charge_user iu')   // "
    ->leftJoin('o.validated_user vu')   // "
    ->leftJoin('o.oked_user ou')        // "
    ->leftJoin('o.lead l')              // parent supplier lead
    ->where('o.id = ?', $id);
$o = $q->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);
if (!$o)
  throw new Exception("La commande n°".$id." n'existe pas.");

$website_origin = $o['website_origin'];

	if($website_origin == 'TC'){
		$selected1 = ' selected="true" ';		
	}	 
	if($website_origin == 'MOB'){
		$selected2 = ' selected="true" ';
		$website_origin = "mobaneo";
	} 
	if($website_origin == 'MER'){
		$selected3 = ' selected="true" ';
		$website_origin = "mercateo";
	} 

$tva_rates = Tva::getFullList($o['created']);

$fdp = 20;
$fdp_franco = 300;
$res = $db->query('select config_name, config_value from config where config_name = \'fdp\' or config_name = \'fdp_franco\'', __FILE__, __LINE__ );
while ($rec = $db->fetch($res))
  $$rec[0] = $rec[1];

$title = $navBar = "Détail de la commande n°".$id;

require(ADMIN.'head.php');
?>
<link rel="stylesheet" type="text/css" href="<?php echo ADMIN_URL ?>ressources/css/item-cart.css" />
<link rel="stylesheet" type="text/css" href="orders.css" />
<script type="text/javascript" src="order-detail.js"></script>
<script type="text/javascript">
HN.TC.tva_rates = <?php print json_encode($tva_rates) ?>;
HN.TC.fdp = <?php echo $fdp ?>;
HN.TC.fdp_franco = <?php echo $fdp_franco ?>;
var order = new HN.TC.Order(<?php echo json_encode($o) ?>, <?php echo $user->id ?>, "<?php echo $user->login ?>");
</script>
<div class="bg">
  <div id="item-cart" class="cart">
    
    <!-- dialog Boxes -->
    <div id="item-cart-sso-db">
      <table class="cart-items">
        <thead>
          <tr>
            <th>Image</th>
            <th>Réf. Fourn.</th>
            <th>Réf. TC</th>
            <th>Désignation</th>
            <th>P.A.U. € HT</th>
            <th>Qté.</th>
            <th>Total € HT</th>
          </tr>
        </thead>
        <tbody>
        </tbody>
      </table>
      <table class="cart-totals">
        <tbody>
          <tr>
            <td>Total HT :</td>
            <td class="total">0.00€</td>
          </tr>
          <tr>
            <td>Total TTC :</td>
            <td class="total">0.00€</td>
          </tr>
        </tbody>
      </table>
      <div class="zero"></div>
      <label>Vous pouvez si vous le souhaitez ajouter un commentaire à votre commande : </label>
      <textarea class="comment"></textarea>
    </div>
    <div id="item-cart-so-arc-db">
      <iframe src="about:blank" width="100%" frameborder="0" height="80"></iframe>
    </div>
    <div id="item-cart-upload-doc-db" title="Ajouter un document" class="db">
      <form name="loadDoc" method="post" action="" enctype="multipart/form-data">
        <img class="loading-gif" src="<?php echo ADMIN_URL ?>ressources/images/lightbox-ico-loading.gif">
        <input type="hidden" name="action" value="load-doc" />
        <input type="hidden" name="supplier" value="" />
        <input type="hidden" name="cmdId" value="<?php echo $o['id'] ?>" />
        Nom : <input type="text" name="aliasFileName" value="" /><br />
        <br />
        Sélectionnez le document à lier à la commande <?php echo $o['id'] ?><br />
        <br />
        <input type="file" name="docFile"  id="docFile"  accept="application/pdf" /><br />
      </form>
    </div>
    <div id="item-cart-upload-msn-attachment-db" title="Ajouter une pièce jointe" class="db">
      <form name="loadDoc" method="post" action="" enctype="multipart/form-data">
      <img class="loading-gif" src="<?php echo ADMIN_URL ?>ressources/images/lightbox-ico-loading.gif">
      <input type="hidden" name="action" value="load-doc" />
      <input type="hidden" name="supplier" value="" />
      <input type="hidden" name="cmdId" value="<?php echo $o['id'] ?>" />
      Nom : <input type="text" name="aliasPjMessFileName" value="" /><br />
      <br />
      Sélectionnez le document à lier au message<br />
      <br />
      <input type="file" name="pjMessFile"  id="pjMessFile"  accept="application/pdf, image/jpeg" /><br />
      </form>
    </div>
    
    <!-- product selection layers -->
    <ul id="item-cart-pdt-preview" class="pdt-previews layer">
    </ul>
    <div id="item-cart-pdt-detail" class="pdt-detail layer">
      <div class="picture"><img id="item-cart-pdt-detail-pic" class="vmaib" src="<?php echo SECURE_URL ?>ressources/images/produits/no-pic-thumb_big.gif" /><div class="vsma"></div></div>
      <div class="infos">
        <div class="vmaib">
          <a id="item-cart-pdt-detail-p-fo-url" class="_blank" href="" title="Voir la fiche en ligne"><img src="../ressources/icons/monitor_go.png" alt="" class="view-fo" /></a>
          <a id="item-cart-pdt-detail-p-bo-url" class="_blank" href="" title="Editer la fiche produit"><strong id="item-cart-pdt-detail-name"></strong></a><br />
          <span id="item-cart-pdt-detail-p-fastdesc"></span><br />
          Code fiche produit: <strong id="item-cart-pdt-detail-p-id"></strong><br />
          Famille : <a id="item-cart-pdt-detail-f-bo-pdt-list-url" class="_blank" href=""><strong id="item-cart-pdt-detail-f-name"></strong></a><br />
          <span>Fournisseur</span> : <a id="item-cart-pdt-detail-a-bo-url" class="_blank" href=""><strong id="item-cart-pdt-detail-a-name"></strong></a><br />
          <a id="item-cart-see-pdt-sheet" href="#pdt_sheet">Voir description produit</a>
        </div><div class="vsma"></div>
      </div>
      <div class="zero"></div>
      <div id="item-cart-pdt-detail-references" class="refs">
      <table>
        <thead>
          <tr>
            <th>Réf. TC</th>
            <th>Réf. Four.</th>
            <th>Libellé</th>
            <th>P.A.U. € HT</th>
            <th>P.U. € HT</th>
            <th>Quantité</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
        </tbody>
      </table>
      </div>
    </div>
    
    <!-- top buttons & links -->
    <?php	if ($user->get_permissions()->has("m-comm--sm-orders", "d")){ ?>
	<button id="item-cart-delete" type="button" class="btn ui-state-default ui-corner-all"><span class="icon cancel"></span> Supprimer la commande</button>
    <?php } ?>
	<button id="item-cart-resend-client-email" type="button" class="btn ui-state-default ui-corner-all"><span class="icon email-go"></span> Renvoyer la commande</button>
    <button id="item-cart-save-order" type="button" class="btn ui-state-default ui-corner-all save_source"><span class="icon disk"></span> Enregistrer les modifications</button>
    <a class="_blank" href="orders.php">&#x00ab; Allez à la liste des commandes</a>
	<a href="<?= ADMIN_URL ?>clients/?idClient=<?php echo $o['client_id'] ? $o['client_id'] : "" ?>" class="_blank fr" id="item-cart-goto-client">Aller à la fiche client »</a>
    <div class="zero"></div>
    
    <!-- system messages -->
    <div id="item-cart-system-msg">
      <div id="item-cart-error-msg" class="fl"></div>
      <div id="item-cart-success-msg" class="fr"></div>
    </div>
    <div class="zero"></div>
    
    <!-- infos -->
    <table id="item-cart-infos" class="cart-infos">
      <tbody>
        <tr>
          <td class="col-1">
            <div>
              <label>Activité :</label>
              <select id="item-cart-activity" class="c_i" data-cart-info="activity" disabled="disabled">
               <?php foreach (Order::$activityList as $k => $v) : ?>
                <option value="<?php echo $k ?>"<?php echo $o['activity']==$k ? " selected=\"selected\"": "" ?>><?php echo $v ?></option>
               <?php endforeach ?>
              </select>
              <button type="button" class="btn ui-state-default ui-corner-all">Changer</button>
            </div>
			<div>
			  <label>Source d'origine :</label>
              <select id="item-cart-source_origine" class="c_i" data-cart-info="website_origin" disabled="disabled">
                <option value="TC"  <?= $selected1 ?>>Techni-Contact</option>
				<option value="MOB" <?= $selected2 ?>>Mobaneo</option>
				<option value="MER" <?= $selected3 ?>>Mercateo</option>
              </select>
              <button type="button" id="change_select" class="btn ui-state-default ui-corner-all">Changer</button>
              <button type="button" id="sauver_select" class="btn ui-state-default ui-corner-all" style="display:none">Sauver</button>
            </div>
			
            <div><label>N° de client :</label> <input id="item-cart-client_id" class="c_i" type="text" data-cart-info="client_id" value="<?php echo $o['client_id'] ? $o['client_id'] : "" ?>" readonly="readonly" /> <button type="button" class="btn ui-state-default ui-corner-all">Changer</button></div>
            <div><label>Société :</label> <span class="c_i" data-cart-info="societe"><?php echo to_entities($o['societe']) ?></span></div>
            <div>
              <label>Type client :</label>
              <span>
                <input id="item-cart-fonction" type="checkbox"<?php if (preg_match("/\bparticulier\s*$/i", $o['fonction'])) : ?> checked="checked"<?php endif ?> />
                est un particulier
              </span>
            </div>
            <div>
              <label>Type de commande :</label>
              <select id="item-cart-type" class="c_i" data-cart-info="type" disabled="disabled">
               <?php foreach (Order::$typeList as $k => $v) : ?>
                <option value="<?php echo $k ?>"<?php echo $o['type']==$k ? " selected=\"selected\"": "" ?>><?php echo $v ?></option>
               <?php endforeach ?>
              </select>
              <button type="button" class="btn ui-state-default ui-corner-all">Changer</button>
            </div>
            <div>
              <label>ID prestashop :</label> <input id="item-cart-alternate_id" class="c_i" type="text" data-cart-info="alternate_id" value="<?php echo $o['alternate_id'] ? $o['alternate_id'] : "" ?>" readonly="readonly" /> <button type="button" class="btn ui-state-default ui-corner-all">Changer</button>
            </div>
            <div>
              <label>OK compta :</label>
             <?php if (!$o['oked']) : ?>
               <?php if ($user->get_permissions()->has("m-comm--sm-orders-oking", "re")) : ?>
                <button id="item-cart-oking" type="button" class="btn ui-state-default ui-corner-all">Valider</button>
               <?php endif ?>
             <?php endif ?>
              <span id="item-cart-oked"><?php if ($o['oked']) : ?>Par <strong><?php echo to_entities($o['oked_user_login']) ?></strong> le <strong><?php echo date('d/m/Y à H:i:s', $o['oked']) ?></strong><?php endif ?></span>
            </div>
            <div>
              <label>Mode de règlement :</label>
              <select id="item-cart-payment_mode" class="c_i" data-cart-info="payment_mode" disabled="disabled">
               <?php foreach (Order::$paymentModeList as $k => $v) : ?>
                <option value="<?php echo $k ?>"<?php echo $o['payment_mode']==$k ? " selected=\"selected\"": "" ?>><?php echo $v ?></option>
               <?php endforeach ?>
              </select>
              <button type="button" class="btn ui-state-default ui-corner-all">Changer</button>
            </div>
            <div>
              <label>Mode de paiment :</label>
              <select id="item-cart-payment_mean" class="c_i" data-cart-info="payment_mean" disabled="disabled">
               <?php foreach (Order::$paymentMeanList as $k => $v) : ?>
                <option value="<?php echo $k ?>"<?php echo $o['payment_mean']==$k ? " selected=\"selected\"": "" ?>><?php echo $v ?></option>
               <?php endforeach ?>
              </select>
              <button type="button" class="btn ui-state-default ui-corner-all">Changer</button>
            </div>
            <div>
              <label>Statut de paiment :</label>
              <select id="item-cart-payment_status" class="c_i" data-cart-info="payment_status" disabled="disabled">
               <?php foreach (Order::$paymentStatusList as $k => $v) : ?>
                <option value="<?php echo $k ?>"<?php echo $o['payment_status']==$k ? " selected=\"selected\"": "" ?>><?php echo $v ?></option>
               <?php endforeach ?>
              </select>
              <button type="button" class="btn ui-state-default ui-corner-all">Changer</button>
            </div>
            <div>
              <label>Statut de traitement :</label>
              <select id="item-cart-processing_status" class="c_i" data-cart-info="processing_status" disabled="disabled">
               <?php foreach (Order::$globalProcessingStatusList as $k => $v) : ?>
                <option value="<?php echo $k ?>"<?php echo $o['processing_status']==$k ? " selected=\"selected\"": "" ?>><?php echo $v ?></option>
               <?php endforeach ?>
              </select>
              <button type="button" class="btn ui-state-default ui-corner-all">Changer</button>
            </div>
            <div id="item-cart-processing_status_texts">
              <div>
                <input id="item-cart-sav_opened_text" class="c_i status-text" type="text" data-cart-info="sav_opened_text" value="<?php echo to_entities($o['sav_opened_text']) ?>" />
                <input id="item-cart-sav_closed_text" class="c_i status-text" type="text" data-cart-info="sav_closed_text" value="<?php echo to_entities($o['sav_closed_text']) ?>" />
                <input id="item-cart-forecasted_ship" class="c_i status-text" type="text" data-cart-info="forecasted_ship" value="<?php echo date('d/m/Y', $o['forecasted_ship']) ?>" data-info-value="<?php echo $o['forecasted_ship'] ?>" />
                <input id="item-cart-forecast_shipping_text" class="c_i" type="hidden" data-cart-info="forecast_shipping_text" value="<?php echo to_entities($o['forecast_shipping_text']) ?>" />
                <input id="item-cart-shipped_text" class="c_i status-text" type="text" data-cart-info="shipped_text" value="<?php echo to_entities($o['shipped_text']) ?>" />
                <input id="item-cart-partly_cancelled_text" class="c_i status-text" type="text" data-cart-info="partly_cancelled_text" value="<?php echo to_entities($o['partly_cancelled_text']) ?>" />
                <input id="item-cart-cancelled_text" class="c_i status-text" type="text" data-cart-info="cancelled_text" value="<?php echo to_entities($o['cancelled_text']) ?>" />
              </div>
              <div><label>Envoyer un email :</label> <input id="item-cart-send_mail" class="c_i" type="checkbox" data-cart-info="send_mail" checked="checked" /></div>
            </div>
          </td>
          <td class="col-2">
            <br />
            <div><label>Email :</label> <input id="item-cart-email" class="c_i" type="text" data-cart-info="email" value="<?php echo to_entities($o['email']) ?>" readonly="readonly"/> <button type="button" class="btn ui-state-default ui-corner-all">Changer</button></div>
            <div><label>Tel :</label> <span class="c_i" data-cart-info="tel"><?php echo Utils::get_dial_html($o['tel']) ?></span></div>
            <div><label>Fax :</label> <span class="c_i" data-cart-info="fax"><?php echo to_entities($o['fax']) ?></span></div>
            <br />
            <div><button id="item-cart-add-doc" type="button" class="btn ui-state-default ui-corner-all">Ajouter un document</button></div>
            <div>
              <ul id="item-cart-doc-list" class="doc-list"></ul>
            </div>
                        <?php // contacts secondaires
              $listContacts = Doctrine_Query::create()
                      ->select()
                      ->from('ClientsContacts')
                      ->where('client_id = ?', $o['client_id'])
                      ->fetchArray();
              if(!empty($listContacts)){
                $dialog = '<div id="secondary_contacts_mails_dialog" title="Joindre les destinataires suivants au mail">
                  <table>';
                foreach ($listContacts as $key => $contact)
                  $dialog .= '<tr>
                    <td><label for="mail'.$key.'">'.$contact['prenom'].' '.$contact['nom'].' '.$contact['email'].' '.$contact['fonction'].' : </label></td>
                    <td><input type="checkbox" name="mail'.$key.'" id="mail'.$key.'" value="'.$contact['email'].'" /></td>
                  </tr>';
                $dialog .= '</table>
                </div>';
                echo  $dialog;
              }
            ?>
			
            <div style="font-weight: bold">Emails secondaires :</div>
            <div style="display : inline-block"><input type="text" name="secondaryContacts" value="" /><?php if(count($listContacts)): ?> <div class="iconAddSecondaryContact ui-icon ui-icon-circle-plus" data-id-client="<?php echo $o['client_id'] ? $o['client_id'] : "" ?>" ></div><?php endif ; ?></div>
          </td>
          <td class="col-3">
            <br />
           <?php if ($o['estimate_id']) : ?>
            <div>
              <label>Contenu du devis n° :</label>
              <span><?php echo to_entities($o['estimate_id']) ?></span>
              <button id="item-cart-see-estimate" type="button" class="btn ui-state-default ui-corner-all">Voir devis</button>
            </div>
            <div><label>Chargé de l'affaire :</label> <span><?php echo to_entities($o['in_charge_user_login']) ?></span></div>
           <?php else : ?>
            <div>
              <label>Commande créé par :</label>
              <span><?php if ($o['type'] == Order::TYPE_INTERNET) : ?><i>Commande internet</i><?php else : to_entities($o['created_user_login']); endif ?></span>
            </div>
           <?php endif ?>
            <div><label>Date de création :</label> <span><?php echo date('d/m/Y H:i:s', $o['created']) ?></span></div>
            <div><label>Date dernière mise à jour :</label> <span id="item-cart-updated"><?php echo date('d/m/Y H:i:s', $o['updated']) ?></span></div>
            <div><label>Dernière mise à jour par :</label> <span id="item-cart-updated_user_login"><?php echo to_entities($o['updated_user_login']) ?></span></div>
            <div><label>ID lead d'origine :</label> <span><?php echo ($o['lead_id'] ? $o['lead_id'] : "Aucun") ?></span>
           <?php if ($o['lead_id']) : ?>
            <button id="item-cart-see-supplier-lead" type="button" class="btn ui-state-default ui-corner-all">Voir</button></div>
            <div><label>Source lead :</label> <span><?php echo $o['lead_source'] ?></span></div>
           <?php endif ?>
          </td>
        </tr>
      </tbody>
    </table>
    
    <!-- supplier orders table -->
   <?php if ($user->get_permissions()->has("m-comm--sm-partners-orders", "re")) : ?>
    <table id="item-cart-supplier_orders" class="cart-items">
      <thead>
        <tr>
          <th>Fournisseurs concernés</th>
        </tr>
      </thead>
      <tbody>
      </tbody>
    </table>
   <?php endif ?>
    
    <!-- real profit table -->
   <?php if ($user->get_permissions()->has("m-comm--sm-partners-orders-real-profits", "re")) : ?>
    <table id="item-cart-supplier_orders-margins" class="cart-items">
      <thead>
        <tr>
          <th>Fournisseur</th>
          <th>P.A. Total</th>
          <th>Dont Frais de port</th>
        </tr>
      </thead>
      <tbody>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="2">Marge brute</td>
          <td class="price">0.00€</td>
        </tr>
        <tr>
          <td colspan="2">Rentabilité</td>
          <td class="price">0.00%</td>
        </tr>
      </tfoot>
    </table>
   <?php endif ?>
    
    <!-- cart items vat, global comment and totals -->
    <button id="item-cart-add-line" type="button" class="btn ui-state-default ui-corner-all fr">Ajouter une ligne produit</button>
    <button id="item-cart-create-ref" type="button" class="btn ui-state-default ui-corner-all fr">Créer un produit en base</button>
    <table id="item-cart-items" class="cart-items">
      <thead>
        <tr>
          <th>Image</th>
          <th>Réf. Fourn.</th>
          <th>Réf. TC</th>
          <th>Fournisseur</th>
          <th>Désignation</th>
          <th>P.A.U. € HT</th>
          <th>P.U. € HT</th>
          <th>Qté.</th>
          <th>Remise</th>
          <th>Total € HT</th>
          <th>Code TVA</th>
          <th>Sup.</th>
        </tr>
      </thead>
      <tbody>
      </tbody>
    </table>
    <table id="item-cart-vat" class="cart-vat">
      <thead>
        <tr>
          <th colspan="2">Base € HT</th>
          <th>Taux</th>
          <th>Montant TVA</th>
        </tr>
      </thead>
      <tfoot>
        <tr>
          <td class="label">Total</td>
          <td class="base">0.00</td>
          <td class="rate">&nbsp;</td>
          <td class="total">0.00</td>
        </tr>
      </tfoot>
    </table>
    <table class="cart-totals">
      <tbody>
        <tr>
          <td>Sous-total HT :</td>
          <td id="item-cart-s-total-ht">0.00€</td>
        </tr>
        <tr id="item-cart-fdp-line">
          <td>Frais de Port HT :</td>
          <td id="item-cart-fdp-ht">0.00€</td>
        </tr>
        <tr>
          <td>Total HT :</td>
          <td id="item-cart-total-ht">0.00€</td>
        </tr>
        <tr>
          <td>Total TTC :</td>
          <td id="item-cart-total-ttc">0.00€</td>
        </tr>
      </tbody>
    </table>
    <table id="item-cart-global-comment" class="cart-global-comment">
      <thead>
        <tr>
          <th>Commentaire global<div class="actions"><span class="cancel icon page-white-delete"></span><span class="accept icon page-white-go"></span><span class="edit icon page-white-edit"></span></div></th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <div><span class="c_i" data-cart-info="comment" data-edit-type="textarea"><?php echo to_entities($o['comment']) ?></span></div>
          </td>
        </tr>
      </tbody>
    </table>
    <div class="zero"></div>
    
    <!-- addresses -->
    <table id="item-cart-addresses" class="cart-address">
      <thead>
        <tr>
          <th>Adresse de facturation<div class="actions"><span class="cancel icon page-white-delete"></span><span class="accept icon page-white-go"></span><span class="edit icon page-white-edit"></span></div></th>
          <th>Adresse de livraison<div class="actions"><span class="cancel icon page-white-delete"></span><span class="accept icon page-white-go"></span><span class="edit icon page-white-edit"></span></div></th>
          <th>Instructions de livraison<div class="actions"><span class="cancel icon page-white-delete"></span><span class="accept icon page-white-go"></span><span class="edit icon page-white-edit"></span></div></th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <div><span class="c_i titre" data-cart-info="titre" data-edit-type="select-title"><?php echo to_entities(Clients::getTitleText($o['titre'])) ?></span> <span class="c_i lastname" data-cart-info="nom"><?php echo to_entities($o['nom']) ?></span> <span class="c_i firstname" data-cart-info="prenom"><?php echo to_entities($o['prenom']) ?></span></div>
            <div><span class="c_i company" data-cart-info="societe"><?php echo to_entities($o['societe']) ?></span></div>
            <div><span class="c_i road" data-cart-info="adresse"><?php echo to_entities($o['adresse']) ?></span></div>
            <div><span class="c_i road" data-cart-info="cadresse"><?php echo to_entities($o['cadresse']) ?></span></div>
            <div><span class="c_i pc" data-cart-info="cp"><?php echo to_entities($o['cp']) ?></span> <span class="c_i city" data-cart-info="ville"><?php echo to_entities($o['ville']) ?></span></div>
            <div><span class="c_i country" data-cart-info="pays"><?php echo to_entities($o['pays']) ?></span></div>
            <div id="item-cart-tva-intra-line">TVA intra : <span class="c_i tva_intra" data-cart-info="tva_intra"><?php echo to_entities($o['tva_intra']) ?></span></div>
            <div>tel : <span class="c_i phone" data-cart-info="tel"><?php echo Utils::get_dial_html($o['tel']) ?></span></div>
          </td>
          <td>
            <div><span class="c_i titre" data-cart-info="titre2" data-edit-type="select-title"><?php echo to_entities(Clients::getTitleText($o['titre2'])) ?></span> <span class="c_i lastname" data-cart-info="nom2"><?php echo to_entities($o['nom2']) ?></span> <span class="c_i firstname" data-cart-info="prenom2"><?php echo to_entities($o['prenom2']) ?></span></div>
            <div><span class="c_i company" data-cart-info="societe2"><?php echo to_entities($o['societe2']) ?></span></div>
            <div><span class="c_i road" data-cart-info="adresse2"><?php echo to_entities($o['adresse2']) ?></span></div>
            <div><span class="c_i road" data-cart-info="cadresse2"><?php echo to_entities($o['cadresse2']) ?></span></div>
            <div><span class="c_i pc" data-cart-info="cp2"><?php echo to_entities($o['cp2']) ?></span> <span class="c_i city" data-cart-info="ville2"><?php echo to_entities($o['ville2']) ?></span></div>
            <div><span class="c_i country" data-cart-info="pays2"><?php echo to_entities($o['pays2']) ?></span></div>
            <div>tel : <span class="c_i phone" data-cart-info="tel2"><?php echo Utils::get_dial_html($o['tel2']) ?></span></div>
          </td>
          <td>
            <div><span class="c_i" data-cart-info="delivery_infos" data-edit-type="textarea"><?php echo to_entities($o['delivery_infos']) ?></span></div>
          </td>
        </tr>
      </tbody>
    </table>
    
    <!-- internal notes and messenger -->
	
	
	
	<!-- Internal notes and messenger -->
	<div class="module_internal_notes">
		<button id="module_internal_notes_item-cart-show-note" class="btn ui-state-default ui-corner-all fr"><span class="icon note"></span> Laisser une note</button>
		<button id="module_internal_notes_item-cart-add-note" class="btn ui-state-default ui-corner-all fr"><span class="icon note-add"></span> Poster la note</button>
		<button id="module_internal_notes_item-cart-cancel-note" class="btn ui-state-default ui-corner-all fr"><span class="icon note-delete"></span> Annuler</button>
		<div id="module_internal_notes_item-cart-note" class="block note">
			<div>Laisser une note :</div>
			<textarea></textarea>
			<div class="attachments">
				<button id="module_internal_notes_add-msn-attachment" type="button" class="btn ui-state-default ui-corner-all">Ajouter une pièce jointe</button>
				Formats autoris&eacute;s : PDF, Document Word ou image '.jpg'
				<ul id="module_internal_notes_item-cart-attachment-list" class="attachment-list">
				</ul>
			</div>
		</div>
		<div class="zero"></div>
		<div id="module_internal_notes_item-cart-notes">
			<div class="block fold-block folded">
				<div class="title">
					Notes internes liées à ce fournisseur
					<span class="icon-fold folded">+</span>
					<span class="icon-fold unfolded">-</span>
				</div>
				<div class="messages fold-content">
					<ul>
					</ul>
				</div>
			</div>
		</div>
		<br />
	

	<!-- attachment dialog box -->
		<div id="module_internal_notes_upload-msn-attachment-db" title="Ajouter une pièce jointe" class="db">
			<form name="loadDoc" method="post" action="" enctype="multipart/form-data">
				<input type="hidden" name="action" value="load-doc" /> 
				<input type="hidden" name="supplier" value="" />
				<input type="hidden" id="id_client" value="<?= $o['client_id'] ?>" />
				<!-- <input type="hidden" name="cmdId" value="<?php echo $o['id'] ?>" /> -->
				Nom : <input type="text" name="module_internalnotes_aliasPjMessFileName"	id="module_internalnotes_aliasPjMessFileName" value="" />
				<br />
				<br />
				Sélectionnez le document à cette commande (PDF, Document Word ou image '.jpg')<br />
				<br />
				<input type="file" name="module_internalnotes_pjMessFile"  id="module_internalnotes_pjMessFile" accept="application/pdf, application/msword, image/jpeg" />
				
				<br />
				<img id="module_internal_notes_upload_img_loading" class="loading-gif" src="<?php echo EXTRANET_URL ?>ressources/images/lightbox-ico-loading.gif" />
			</form>
		</div>
	</div>
	<!-- End attachment dialog box -->


	<!-- latest additions -->
	<input type="hidden" id="module_internal_notes_hidden_global_id" value="<?php echo $_GET["id"] ?>" />
	<input type="hidden" id="module_internal_notes_hidden_attachments_id" value="" />
	<script type="text/javascript">
		module_internal_notes_init_internal_notes(<?php echo $_GET["id"] ?>);
	</script>

	<!-- End Internal notes and messenger -->
	
    <br />
	
    <button id="item-cart-show-post" class="btn ui-state-default ui-corner-all fr"><span class="icon page-white"></span> Envoyer un message</button>
    <button id="item-cart-add-post" class="btn ui-state-default ui-corner-all fr"><span class="icon page-white-add"></span> Poster le message</button>
    <button id="item-cart-cancel-post" class="btn ui-state-default ui-corner-all fr"><span class="icon page-white-delete"></span> Annuler</button>
    <button id="item-cart-close-conv" class="btn ui-state-default ui-corner-all fr"><span class="icon page-white-key"></span> Clore la conversation</button>
    <div id="item-cart-post" class="block post">
      <div>Envoyer un message au client :</div>
      <textarea></textarea>
      <div class="attachments">
        <button id="item-cart-add-msn-attachment" type="button" class="btn ui-state-default ui-corner-all">Ajouter une pièce jointe</button>
        <ul id="item-cart-attachment-list" class="attachment-list">
        </ul>
      </div>
    </div>
    <div class="zero"></div>
    <div id="item-cart-conversation">
      <div class="block fold-block folded">
        <div class="title">Conversation liée à cette commande<span class="icon-fold folded">+</span><span class="icon-fold unfolded">-</span></div>
        <div class="messages fold-content">
          <ul>
          </ul>
        </div>
      </div>
    </div>
    <br />
    
    <!-- bottom buttons -->
    <button id="item-cart-print" class="btn ui-state-default ui-corner-all fl">Imprimer le bon de commande</button>
    <div id="item-cart-validation" class="validation">
     <?php if ($o['validated']) : ?>
      Validée par <strong><?php echo to_entities($o['validated_user_login']) ?></strong> le <strong><?php echo date('d/m/Y à H:i:s', $o['validated']) ?></strong>
     <?php elseif ($user->get_permissions()->has("m-comm--sm-orders-validating", "re")) : ?>
      <label>Envoyer le mail de confirmation :</label>
      <input id="item-cart-send_recap_mail" class="c_i" type="checkbox" data-cart-info="send_recap_mail"<?php if ($o['type'] != Order::TYPE_INTERNET) : ?> checked="checked"<?php endif ?> />
      <span id="item-cart-send_invoice_mail-line">
        <label>Envoyer le mail de facture :</label>
        <input id="item-cart-send_invoice_mail" class="c_i" type="checkbox" data-cart-info="send_invoice_mail" checked="checked" />
      </span>
      <button id="item-cart-validate" class="btn ui-state-default ui-corner-all">Valider cette commande</button>
     <?php endif ?>
    </div>
    <div class="zero"></div>
    
  </div>
</div>

 <?php
	if(isset($_GET['params'])){
		if( $_GET['params'] == 'display_bars'){ 
			$sql_updsate = "UPDATE call_spool_vpc SET `ligne_active`='1',call_operator='".$_SESSION["id"]."'
							WHERE id='".$_GET['idCall']."' ";
			mysql_query($sql_updsate);
			
			$sql_compagne  = "SELECT order_id,campaign_name FROM call_spool_vpc WHERE id='".$_GET['idCall']."' ";
			$req_compagne  = mysql_query($sql_compagne);
			$data_compagne = mysql_fetch_object($req_compagne);
			
			$sql_societe   = "SELECT societe FROM `order` WHERE id='".$data_compagne->order_id."' "; 
			$req_societe   = mysql_query($sql_societe);
			$data_societe  = mysql_fetch_object($req_societe);
		?>
		<div id="bottomBar">
			<div style="visibility: visible; margin-top: -50px;width:500px;" id="callBar">
				<div style="visibility : visible;padding: 5px;" id="inCallbar">
					<div class="name_campgne">Feedback livraison société <?= $data_societe->societe ?></div>
					<div>
					<a class="btn ui-state-default ui-corner-all" onclick="setCallOkVPC(<?= $_GET['idCall'] ?>)" href="#"> Client joint</a>
					<a class="btn ui-statelike-choice-no ui-corner-all" onclick="setCallNokVPC(<?= $_GET['idCall'] ?>)" href="#"> Appel en absence</a>
					
					</div>
				</div>
			</div>
		</div>
	<?php }
	}
	?> 
<script type="text/javascript">
   function setCallOkVPC(id_pille){
	var joinabilite = "<?= $_GET['joinabilite'] ?>";
	var appel 		= "<?= $_GET['appel'] ?>";
	var users 		= "<?= $_GET['users'] ?>";
		$.ajax({
				url: '../pile_appels_commerciaux/AJAX_vpc/AJAX_action_vpc.php?id_ligne='+id_pille+'&action=client_joint ',
				type: 'GET',
				success:function(data){
					location.href='<?php echo ADMIN_URL ?>pile_appels_commerciaux/pile_appels_VPC.php?joinabilite='+joinabilite+'&appel='+appel+'&users='+users;
				}
		});
	}	
	function setCallNokVPC(id_pille){
	var joinabilite = "<?= $_GET['joinabilite'] ?>";
	var appel 		= "<?= $_GET['appel'] ?>";
	var users 		= "<?= $_GET['users'] ?>";
		$.ajax({
				url: '../pile_appels_commerciaux/AJAX_vpc/AJAX_action_vpc.php?id_ligne='+id_pille+'&action=client_en_absence ',
				type: 'GET',
				success:function(data){
					location.href='<?php echo ADMIN_URL ?>pile_appels_commerciaux/pile_appels_VPC.php?joinabilite='+joinabilite+'&appel='+appel+'&users='+users;
				}
		});
	}
	
	
	$( "#change_select" ).click(function() {
		$("#item-cart-source_origine").prop('disabled', false);
		$("#sauver_select").show();
		$("#change_select").hide();
	});
	
	$( "#sauver_select" ).click(function() {
		$("#item-cart-source_origine").prop('disabled', true);
		$("#sauver_select").hide();
		$("#change_select").show();
		var id_client = $("#id_client").val();
		var source    = $("#item-cart-source_origine").val();
		
		$.ajax({
				url: '../ressources/ajax/AJAX_update_source.php?action=order&id_send='+<?= $id ?>+'&source='+source,
				type: 'GET',
				success:function(data){
					if(source == 'MOB'){
						$("#logo-website").html('<img class="top-website-logo" src="../ressources/images/logo-website-mobaneo.jpg" alt="mobaneo" />');
					}
					if(source == 'MER'){
						$("#logo-website").html('<img class="top-website-logo" src="../ressources/images/logo-website-mercateo.jpg" alt="mobaneo" />');
					}
					if(source == 'TC'){
						$("#logo-website").html('');
					}					
				}
		});
		
	});
	$( ".save_source" ).click(function() {
		var id_client = $("#id_client").val();
		var source    = $("#item-cart-source_origine").val();
		
		$.ajax({
				url: '../ressources/ajax/AJAX_update_source.php?action=order&id_send='+<?= $id ?>+'&source='+source,
				type: 'GET',
				success:function(data){
					if(source == 'MOB'){
						$("#logo-website").html('<img class="top-website-logo" src="../ressources/images/logo-website-mobaneo.jpg" alt="mobaneo" />');
					}
					if(source == 'MER'){
						$("#logo-website").html('<img class="top-website-logo" src="../ressources/images/logo-website-mercateo.jpg" alt="mobaneo" />');
					}
					if(source == 'TC'){
						$("#logo-website").html('');
					}					
				}
		});
	});	
</script>

<script type="text/javascript">
  order.htmlInit();
 <?php if ($product_id) : ?>
  order.ci.addAutocompleteLine(<?php echo $product_id ?>);
 <?php endif ?>
</script>
<?php
} catch (Exception $e) {
  require(ADMIN.'head.php');
?>
<div class="bg" style="position: relative">
	<h2><?php echo $e->getMessage() ?></h2>
</div>
<?php
}
require(ADMIN.'tail.php');
