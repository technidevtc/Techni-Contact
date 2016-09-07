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

if (!$user->get_permissions()->has("m-comm--sm-invoices", "r"))
  throw new Exception("Vous n'avez pas les droits adéquats pour réaliser cette opération.");

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_STRING);
$rid = filter_input(INPUT_GET, 'rid', FILTER_VALIDATE_INT);
$product_id = filter_input(INPUT_GET, 'product_id', FILTER_VALIDATE_INT);

if (empty($id) && empty($rid))
  throw new Exception("Numéro de facture ou d'avoir invalide.");

if ($id == 'new') { // new invoice
  $i = new Invoice();
  
  if ($client_id = filter_input(INPUT_GET, 'client_id', FILTER_VALIDATE_INT)) {
    if (!$i->importFromClient($client_id))
      throw new Exception("Le client ayant pour identifiant ".$client_id." n'existe pas.");
    $i->save();
    header('Location: invoice-detail.php?id='.$i->id);
    exit();
  }
  else {
    $i->save();
    header('Location: invoice-detail.php?id='.$i->id);
    exit();
  }
}

$q = Doctrine_Query::create()
  ->select('i.*,
            il.*,
            o.id, o.alternate_id,
            IF(0,il.id,ils.nom1) AS sup_name,
            c.code AS client_code,
            cn.id AS credit_note_id,
            IF(0,il.id,ilpfr.ref_name) AS pdt_ref_name,
            IF(0,il.id,ilpf.id) AS pdt_cat_id,
            cu.login AS created_user_login,
            uu.login AS updated_user_login,
            iu.login AS issued_user_login')
  ->from('Invoice i')
  ->leftJoin('i.lines il')            // lines
  ->leftJoin('il.supplier ils')       // direct relation to the supplier of this line
  ->leftJoin('i.order o')             // order
  ->leftJoin('i.client c')            // normal client
  ->leftJoin('i.credit_note cn')      // corresponding credit note if there is one
  ->leftJoin('il.product ilp')        // product
  ->leftJoin('ilp.product_fr ilpfr')  // "
  ->leftJoin('ilp.families ilpf')     // "
  ->leftJoin('i.created_user cu')     // bousers
  ->leftJoin('i.updated_user uu')     // "  
  ->leftJoin('i.issued_user iu');     // "
if (empty($id))
  $q->where('i.rid = ?', $rid);
else
  $q->where('i.id = ?', $id);
$i = $q->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);
if (!$i)
  throw new Exception("La facture ou l'avoir n°".$id." n'existe pas.");

$tva_rates = Tva::getFullList($i['created']);

$fdp = 20;
$fdp_franco = 300;
$res = $db->query('select config_name, config_value from config where config_name = \'fdp\' or config_name = \'fdp_franco\'', __FILE__, __LINE__ );
while ($rec = $db->fetch($res))
  $$rec[0] = $rec[1];

$title = $navBar = $i['type_text']." n°".($i['rid'] ? $i['rid'] : "XXXXXXXX");
$website_origin = $i['website_origin']; 

	if($website_origin == 'TC'){
		$selected1 = ' selected="true" ';		
	}	 
	if($website_origin == 'MOB'){
		$selected2 = ' selected="true" ';
		// echo '<img class="top-website-logo" src="../ressources/images/logo-website-mobaneo.jpg" alt="">';
		$website_origin = "mobaneo";
	} 
	if($website_origin == 'MER'){
		$selected3 = ' selected="true" ';
		// echo '<img class="top-website-logo" src="../ressources/images/logo-website-mercateo.jpg" alt="">';
		$website_origin = "mercateo";
	} 




require(ADMIN.'head.php');
?>
<link rel="stylesheet" type="text/css" href="<?php echo ADMIN_URL ?>ressources/css/item-cart.css" />
<link rel="stylesheet" type="text/css" href="invoices.css" />
<script type="text/javascript" src="invoice-detail.js"></script>
<script type="text/javascript">
HN.TC.tva_rates = <?php print json_encode($tva_rates) ?>;
HN.TC.fdp = <?php echo $fdp ?>;
HN.TC.fdp_franco = <?php echo $fdp_franco ?>;
var invoice = new HN.TC.Invoice(<?php echo json_encode($i) ?>, <?php echo $user->id ?>, "<?php echo $user->login ?>");
</script>
<div class="bg">
  <div id="item-cart" class="cart type-<?php echo $i['type'] ?>">
    
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
    
    <!-- Top buttons & links -->
    <a id="item-cart-goto-client" class="_blank fr" href=""></a>
    <button id="item-cart-save-invoice" type="button" class="btn ui-state-default ui-corner-all save_source fr"><span class="icon disk"></span> Enregistrer les modifications</button>
    <a class="_blank" href="invoices.php">&#x00ab; Allez à la liste des factures et avoirs</a>
    <a id="item-cart-goto-client" class="_blank fr" href=""></a>
    <div class="zero"></div>
    
    <!-- system messages -->
    <div id="item-cart-system-msg">
      <div id="item-cart-error-msg" class="fl"></div>
      <div id="item-cart-success-msg" class="fr"></div>
    </div>
    <div class="zero"></div>
    
    <!-- order infos -->
    <table id="item-cart-infos" class="cart-infos">
      <tbody>
        <tr>
          <td class="col-1">
            <div>
              <label>Activité :</label>
              <select id="item-cart-activity" class="c_i" data-cart-info="activity" disabled="disabled">
               <?php foreach (Invoice::$activityList as $k => $v) : ?>
                <option value="<?php echo $k ?>"<?php echo $i['activity']==$k ? " selected=\"selected\"": "" ?>><?php echo $v ?></option>
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
			
            <div><label>N° de client :</label> <input id="item-cart-client_id" class="c_i" type="text" data-cart-info="client_id" value="<?php echo $i['client_id'] ? $i['client_id'] : "" ?>" readonly="readonly" /> <button type="button" class="btn ui-state-default ui-corner-all">Changer</button></div>
            <div><label>Société :</label> <span class="c_i" data-cart-info="societe"><?php echo to_entities($i['societe']) ?></span></div>
            <div>
              <label>Type client :</label>
              <span>
                <input id="item-cart-fonction" type="checkbox"<?php if (preg_match("/\bparticulier\s*$/i", $o['fonction'])) : ?> checked="checked"<?php endif ?> />
                est un particulier
              </span>
            </div>
            <div><label>Commande source :</label> <input id="item-cart-order_id" class="c_i" type="text" data-cart-info="order_id" value="<?php echo $i['order']['alternate_id'] ? $i['order']['alternate_id'] : ($i['order_id'] ? $i['order_id'] : "") ?>" readonly="readonly" /> <button type="button" class="btn ui-state-default ui-corner-all">Changer</button> <button id="item-cart-see-order" type="button" class="btn ui-state-default ui-corner-all">Voir</button></div>
           <?php if ($i['type'] == Invoice::TYPE_INVOICE) : ?>
            <div><label>Devis source :</label> <input id="item-cart-estimate_id" class="c_i" type="text" data-cart-info="estimate_id" value="<?php echo $i['estimate_id'] ? $i['estimate_id'] : "" ?>" readonly="readonly" /> <button type="button" class="btn ui-state-default ui-corner-all">Changer</button> <button id="item-cart-see-estimate" type="button" class="btn ui-state-default ui-corner-all">Voir</button></div>
            <div>
              <label>Mode de règlement :</label>
              <select id="item-cart-payment_mode" class="c_i" data-cart-info="payment_mode" disabled="disabled">
               <?php foreach (Invoice::$paymentModeList as $k => $v) : ?>
                <option value="<?php echo $k ?>"<?php echo $i['payment_mode']==$k ? " selected=\"selected\"": "" ?>><?php echo $v ?></option>
               <?php endforeach ?>
              </select>
              <button type="button" class="btn ui-state-default ui-corner-all">Changer</button>
            </div>
            <div>
              <label>Mode de paiment :</label>
              <select id="item-cart-payment_mean" class="c_i" data-cart-info="payment_mean" disabled="disabled">
               <?php foreach (Invoice::$paymentMeanList as $k => $v) : ?>
                <option value="<?php echo $k ?>"<?php echo $i['payment_mean']==$k ? " selected=\"selected\"": "" ?>><?php echo $v ?></option>
               <?php endforeach ?>
              </select>
              <button type="button" class="btn ui-state-default ui-corner-all">Changer</button>
            </div>
            <div><label>Facture émise le :</label> <input class="c_i" type="text" data-cart-info="issued" value="<?php echo $i['issued'] ? date("d/m/Y H:i:s", $i['issued']) : "non émise" ?>" readonly="readonly" /></div>
            <div><label>Echéance :</label> <input id="item-cart-due_date" class="c_i" type="text" data-cart-info="due_date" data-info-value="<?php echo $i['due_date'] ?>" value="<?php echo $i['due_date'] ? date("d/m/Y H:i:s", $i['due_date']) : "non défini" ?>" readonly="readonly" /> <button type="button" class="btn ui-state-default ui-corner-all">Changer</button></div>
           <?php else : // TYPE_CREDIT_NOTE ?>
            <div><label>Facture source :</label> <input id="item-cart-invoice_rid" class="c_i" type="text" data-cart-info="invoice_rid" value="<?php echo $i['invoice_rid'] ? $i['invoice_rid'] : "" ?>" readonly="readonly" /> <button type="button" class="btn ui-state-default ui-corner-all">Changer</button> <button id="item-cart-see-invoice" type="button" class="btn ui-state-default ui-corner-all">Voir</button></div>
            <div><label>Avoir émis le :</label> <input class="c_i" type="text" data-cart-info="issued" value="<?php echo $i['issued'] ? date("d/m/Y H:i:s", $i['issued']) : "non émis" ?>" readonly="readonly" /></div>
           <?php endif // type ?>
            <div><label>Code client :</label> <input id="item-cart-code" class="c_i" type="text" data-cart-info="code" value="<?php echo $i['code'] ?>" readonly="readonly" /> <button type="button" class="btn ui-state-default ui-corner-all">Changer</button></div>
          </td>
          <td class="col-2">
            <br />
            <div><label>Email :</label> <input id="item-cart-email" class="c_i" type="text" data-cart-info="email" value="<?php echo to_entities($i['email']) ?>" readonly="readonly"/> <button type="button" class="btn ui-state-default ui-corner-all">Changer</button></div>
            <div><label>Tel :</label> <span class="c_i" data-cart-info="tel" data-info-value="<?php echo $i['tel'] ?>"><a href="tel:<?php echo $i['tel'] ?>"><?php echo $i['tel'] ?> <span class="icon telephone"></span></a></span></div>
            <div><label>Fax :</label> <span class="c_i" data-cart-info="fax"><?php echo to_entities($i['fax']) ?></span></div>
            <br />
           <?php if ($i['type'] == Invoice::TYPE_INVOICE) : ?>
            <div><button id="item-cart-generate-credit-note" type="button" class="btn ui-state-default ui-corner-all">Générer un nouvel avoir</button></div>
            <br/>
            <div><button id="item-cart-see-credit-note" type="button" class="btn ui-state-default ui-corner-all">Voir le dernier avoir correspondant</button></div>
           <?php endif ?>
            <?php // contacts secondaires
              $listContacts = Doctrine_Query::create()
                      ->select()
                      ->from('ClientsContacts')
                      ->where('client_id = ?', $i['client_id'])
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
            <div style="display : inline-block"><input type="text" name="secondaryContacts" value="" /><?php if(count($listContacts)): ?> <div class="iconAddSecondaryContact ui-icon ui-icon-circle-plus" data-id-client="<?php echo $i['client_id'] ? $i['client_id'] : "" ?>" ></div><?php endif ; ?></div>
          </td>
          <td class="col-3">
            <br />
            <div><label>Date de création :</label> <span><?php echo date("d/m/Y H:i:s", $i['created']) ?></span></div>
            <div><label>Créé par :</label> <span><?php echo to_entities($i['created_user_login']) ?></span></div>
            <div><label>Date dernière mise à jour :</label> <span id="item-cart-updated"><?php echo date("d/m/Y H:i:s", $i['updated']) ?></span></div>
            <div><label>Dernière mise à jour par :</label> <span id="item-cart-updated_user_login"><?php echo to_entities($i['updated_user_login']) ?></span></div>
            <br />
          </td>
        </tr>
      </tbody>
    </table>
    
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
            <div><span class="c_i" data-cart-info="comment" data-edit-type="textarea"><?php echo to_entities($i['comment']) ?></span></div>
          </td>
        </tr>
      </tbody>
    </table>
    <div class="zero"></div>
    
    <!-- order addresses -->
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
            <div><span class="c_i titre" data-cart-info="titre" data-edit-type="select-title"><?php echo to_entities(Clients::getTitleText($i['titre'])) ?></span> <span class="c_i lastname" data-cart-info="nom"><?php echo $i['nom'] ?></span> <span class="c_i firstname" data-cart-info="prenom"><?php echo $i['prenom'] ?></span></div>
            <div><span class="c_i company" data-cart-info="societe"><?php echo $i['societe'] ?></span></div>
            <div><span class="c_i road" data-cart-info="adresse"><?php echo $i['adresse'] ?></span></div>
            <div><span class="c_i road" data-cart-info="cadresse"><?php echo $i['cadresse'] ?></span></div>
            <div><span class="c_i pc" data-cart-info="cp"><?php echo $i['cp'] ?></span> <span class="c_i city" data-cart-info="ville"><?php echo $i['ville'] ?></span></div>
            <div><span class="c_i country" data-cart-info="pays"><?php echo $i['pays'] ?></span></div>
            <div id="item-cart-tva-intra-line">TVA intra : <span class="c_i tva_intra" data-cart-info="tva_intra"><?php echo to_entities($i['tva_intra']) ?></span></div>
            <div>tel : <span class="c_i phone" data-cart-info="tel"><?php echo $i['tel'] ?></span></div>
          </td>
          <td>
            <div><span class="c_i titre" data-cart-info="titre2" data-edit-type="select-title"><?php echo to_entities(Clients::getTitleText($i['titre2'])) ?></span> <span class="c_i lastname" data-cart-info="nom2"><?php echo $i['nom2'] ?></span> <span class="c_i firstname" data-cart-info="prenom2"><?php echo $i['prenom2'] ?></span></div>
            <div><span class="c_i company" data-cart-info="societe2"><?php echo $i['societe2'] ?></span></div>
            <div><span class="c_i road" data-cart-info="adresse2"><?php echo $i['adresse2'] ?></span></div>
            <div><span class="c_i road" data-cart-info="cadresse2"><?php echo $i['cadresse2'] ?></span></div>
            <div><span class="c_i pc" data-cart-info="cp2"><?php echo $i['cp2'] ?></span> <span class="c_i city" data-cart-info="ville2"><?php echo $i['ville2'] ?></span></div>
            <div><span class="c_i country" data-cart-info="pays2"><?php echo $i['pays2'] ?></span></div>
            <div>tel : <span class="c_i phone" data-cart-info="tel2"><?php echo $i['tel2'] ?></span></div>
          </td>
          <td>
            <div><span class="c_i" data-cart-info="delivery_infos" data-edit-type="textarea"><?php echo $i['delivery_infos'] ?></span></div>
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
					Notes internes liées à cette facture
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
				Sélectionnez le document à cette facture (PDF, Document Word ou image '.jpg')<br />
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
	<input type="hidden" id="id_client" value="<?= $i['client_id'] ?>" />
	<script type="text/javascript">
		module_internal_notes_init_internal_notes(<?php echo $_GET["id"] ?>);
	</script>

	<!-- End Internal notes and messenger -->
	
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
				url: '../ressources/ajax/AJAX_update_source.php?action=invoice&id_send='+<?= $id ?>+'&source='+source,
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
				url: '../ressources/ajax/AJAX_update_source.php?action=invoice&id_send='+<?= $id ?>+'&source='+source,
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
	
	
	
    
    <!-- bottom buttons -->
    <button id="item-cart-print" class="btn ui-state-default ui-corner-all fl">Imprimer <?php echo $i['the_type'] ?></button>
   <?php if ($i['status'] == Invoice::STATUS_NOT_VALIDATED) : ?>
    <div id="item-cart-validation" class="validation">
      <label>Envoyer le mail :</label>
      <input id="item-cart-send_invoice_mail" class="c_i" type="checkbox" data-cart-info="send_invoice_mail" checked="checked" />
      <button id="item-cart-validate" class="btn ui-state-default ui-corner-all">Valider <?php echo $i['the_type'] ?></button>
    </div>
   <?php endif ?>
    <button id="item-cart-resend" class="btn ui-state-default ui-corner-all fr">Renvoyer <?php echo $i['the_type'] ?></button>
    <div class="zero"></div>
  </div>
</div>
<script type="text/javascript">
  invoice.htmlInit();
 <?php if ($product_id) : ?>
  invoice.ci.addAutocompleteLine(<?php echo $product_id ?>);
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
