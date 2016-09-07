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

if (!$user->get_permissions()->has("m-comm--sm-partners-orders", "r"))
  throw new Exception("Vous n'avez pas les droits adéquats pour réaliser cette opération.");

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (empty($id))
  throw new Exception("Numéro d'ordre fournisseur invalide.");
  
$q = Doctrine_Query::create()
    ->select('so.*,
              s.*,
              o.*,
              sos.name,
              ol.*,
              r.id,
              r.refSupplier,
              rp.id,
              rpfr.ref_name,
              rpf.id')
    ->from('SupplierOrder so')
    ->innerJoin('so.supplier s')
    ->innerJoin('so.order o')
    ->leftJoin('so.sender sos')
    ->innerJoin('o.lines ol')
    ->leftJoin('ol.pdt_ref r')
    ->leftJoin('r.product rp')
    ->leftJoin('rp.product_fr rpfr')
    ->leftJoin('rp.families rpf')
    ->where('so.id = ?', $id)
    ->andWhere('ol.sup_id = so.sup_id');
$so = $q->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);
if (!$so)
  throw new Exception("L'ordre fournisseur n°".$id." n'existe pas.");

$so['lines'] = $so['order']['lines'];
unset($so['order']['lines']);

$tva_rates = Tva::getFullList($so['order']['created']);

$fdp = 20;
$fdp_franco = 300;
$res = $db->query('select config_name, config_value from config where config_name = \'fdp\' or config_name = \'fdp_franco\'', __FILE__, __LINE__ );
while ($rec = $db->fetch($res))
  $$rec[0] = $rec[1];

$title = $navBar = "Détail de l'ordre fournisseur n°".$so['sup_id']."-".$so['order_id'];
$website_origin = $so['order']['website_origin'];
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
require(ADMIN.'head.php');
?>
<link rel="stylesheet" type="text/css" href="<?php echo ADMIN_URL ?>ressources/css/item-cart.css" />
<link rel="stylesheet" type="text/css" href="supplier-orders.css" />
<script type="text/javascript" src="supplier-order-detail.js"></script>
<script type="text/javascript">
HN.TC.tva_rates = <?php print json_encode($tva_rates) ?>;
HN.TC.fdp = <?php echo $fdp ?>;
HN.TC.fdp_franco = <?php echo $fdp_franco ?>;
var supplier_order = new HN.TC.SupplierOrder(<?php echo json_encode($so) ?>, <?php echo $user->id ?>, "<?php echo $user->login ?>");
</script>
<div class="bg<?php echo $so['cancellation'] ? " cancelled": "" ?>">
  <div id="item-cart" class="cart<?php echo $so['cancellation'] ? " cancelled": "" ?>">
    
    <!-- dialog Boxes -->
    <div id="item-cart-cancel-db">
      Merci préciser la raison de cette annulation :<br/>
      <textarea class="c_i" data-cart-info="cancellation_reason"></textarea>
    </div>
    <div id="item-cart-so-arc-db">
      <iframe src="about:blank" width="100%" frameborder="0" height="80"></iframe>
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
    
    <!-- top buttons & links -->
    <button id="item-cart-cancel" type="button" class="btn ui-state-default ui-corner-all fr"><span class="icon cancel"></span> Annuler l'ordre</button>
    <button id="item-cart-save" type="button" class="btn ui-state-default ui-corner-all fr"><span class="icon disk"></span> Enregistrer les modifications</button>
    <a class="_blank" href="supplier-orders.php">&#x00ab; Allez à la liste des ordres fournisseurs</a>
    <a id="item-cart-goto-client" class="_blank fr" href="<?php echo ADMIN_URL."clients/?idClient=".$so['order']['client_id'] ?>">Aller à la fiche client &#x00bb;</a>
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
            <div><label>N° de client :</label> <span><?php echo $so['order']['client_id'] ?></span></div>
            <div><label>Fournisseur :</label> <span><?php echo to_entities($so['supplier']['nom1']) ?></span></div>
            <div><label>Ordre donné par :</label> <span><?php echo to_entities($so['sender']['name']) ?></span></div>
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
			<div>
              <label>Statut de traitement :</label>
              <select id="item-cart-processing_status" class="c_i" data-cart-info="processing_status" disabled="disabled">
               <?php foreach(SupplierOrder::$editableProcessingStatusList as $k => $v) : ?>
                <option value="<?php echo $k ?>"<?php echo $so['processing_status']>=$k ? " selected=\"selected\"": "" ?>><?php echo $v ?></option>
               <?php endforeach ?>
              </select>
              <button type="button" class="btn ui-state-default ui-corner-all">Changer</button>
            </div>
            <div><label>Date d'expédition prévisionnelle :</label> <input id="item-cart-forecast_shipping_text" class="c_i" type="text" data-cart-info="forecast_shipping_text" value="<?php echo $so['forecast_shipping_text'] ?>" readonly="readonly" placeholder="Non encore fixée" /> <button type="button" class="btn ui-state-default ui-corner-all">Changer</button></div>
            <div id="item-cart-forecast_shipping_texts">
              <div><label>Envoyer un email</label> <input id="item-cart-send_mail" class="c_i" type="checkbox" data-cart-info="send_mail" /></div>
            </div>
          </td>
          <td class="col-2">
            <div><label>Email :</label> <span class="c_i" data-cart-info="email"><?php echo to_entities($so['order']['email']) ?></span></div>
            <div><label>Tel :</label> <span class="c_i" data-cart-info="tel"><?php echo Utils::get_dial_html($so['order']['tel']) ?></span></div>
            <div><label>Fax :</label> <span class="c_i" data-cart-info="fax"><?php echo to_entities($so['order']['fax']) ?></span></div>
            <div><button id="item-cart-see-order" type="button" class="btn ui-state-default ui-corner-all">Voir commande client</button></div>
          </td>
          <td class="col-3">
            <div><label>Date de création :</label> <span><?php echo date('d/m/Y H:i:s', $so['order']['created']) ?></span></div>
            <div><label>Date dernière mise à jour :</label> <span id="item-cart-updated"><?php echo date('d/m/Y H:i:s', $so['order']['updated']) ?></span></div>
          </td>
        </tr>
      </tbody>
    </table>
    
    <!-- arc -->
    <div>
      <span id="item-cart-arc"></span>
      - <a id="item-cart-delivery-order" href="print-delivery-order.php?id=<?php echo $id ?>">Voir bon de livraison</a>
      - <a id="item-cart-purchase-order" href="print-purchase-order.php?id=<?php echo $id ?>">Voir bon de commande</a>
    </div>
    
    <!-- cart items vat, and totals -->
    <table id="item-cart-items" class="cart-items">
      <thead>
        <tr>
          <th>Image</th>
          <th>Réf. Fourn.</th>
          <th>Réf. TC</th>
          <th>Fournisseur</th>
          <th>Désignation</th>
          <th>P.A.U. € HT</th>
          <th>Qté.</th>
          <th>Total € HT</th>
          <th>Code TVA</th>
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
        <tr>
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
    <div class="zero"></div>
    
    <!-- addresses -->
    <table id="item-cart-addresses" class="cart-address">
      <thead>
        <tr>
          <th>Adresse de facturation</th>
          <th>Adresse de livraison</th>
          <th>Instructions de livraison</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <div><span class="c_i lastname" data-cart-info="nom"><?php echo $so['order']['nom'] ?></span> <span class="c_i firstname" data-cart-info="prenom"><?php echo $so['order']['prenom'] ?></span></div>
            <div><span class="c_i company" data-cart-info="societe"><?php echo $so['order']['societe'] ?></span></div>
            <div><span class="c_i road" data-cart-info="adresse"><?php echo $so['order']['adresse'] ?></span></div>
            <div><span class="c_i road" data-cart-info="cadresse"><?php echo $so['order']['cadresse'] ?></span></div>
            <div><span class="c_i pc" data-cart-info="cp"><?php echo $so['order']['cp'] ?></span> <span class="c_i city" data-cart-info="ville"><?php echo $so['order']['ville'] ?></span></div>
            <div><span class="c_i country" data-cart-info="pays"><?php echo $so['order']['pays'] ?></span></div>
            <div>tel : <span class="c_i phone" data-cart-info="tel"><?php echo Utils::get_dial_html($so['order']['tel']) ?></span></div>
          </td>
          <td>
            <div><span class="c_i lastname" data-cart-info="nom2"><?php echo $so['order']['nom2'] ?></span> <span class="c_i firstname" data-cart-info="prenom2"><?php echo $so['order']['prenom2'] ?></span></div>
            <div><span class="c_i company" data-cart-info="societe2"><?php echo $so['order']['societe2'] ?></span></div>
            <div><span class="c_i road" data-cart-info="adresse2"><?php echo $so['order']['adresse2'] ?></span></div>
            <div><span class="c_i road" data-cart-info="cadresse2"><?php echo $so['order']['cadresse2'] ?></span></div>
            <div><span class="c_i pc" data-cart-info="cp2"><?php echo $so['order']['cp2'] ?></span> <span class="c_i city" data-cart-info="ville2"><?php echo $so['order']['ville2'] ?></span></div>
            <div><span class="c_i country" data-cart-info="pays2"><?php echo $so['order']['pays2'] ?></span></div>
            <div>tel : <span class="c_i phone" data-cart-info="tel2"><?php echo Utils::get_dial_html($so['order']['tel2']) ?></span></div>
          </td>
          <td>
            <span class="c_i" data-cart-info="delivery_infos" data-edit-type="textarea"><?php echo $so['order']['delivery_infos'] ?></span>
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
	
	
	
	
    <br/>
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
        <div class="title">Conversation liée à cet ordre fournisseur<span class="icon-fold folded">+</span><span class="icon-fold unfolded">-</span></div>
        <div class="messages fold-content">
          <ul>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
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
				url: '../ressources/ajax/AJAX_update_source.php?action=supplier_order&id_send='+<?= $so['order_id'] ?>+'&source='+source,
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
				url: '../ressources/ajax/AJAX_update_source.php?action=supplier_order&id_send='+<?= $so['order_id'] ?>+'&source='+source,
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

<script type="text/javascript">supplier_order.htmlInit();</script>
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
