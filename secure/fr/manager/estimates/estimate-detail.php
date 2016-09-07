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

if (!$user->get_permissions()->has("m-comm--sm-estimates","r"))
  throw new Exception("Vous n'avez pas les droits adéquats pour réaliser cette opération.");
 
$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_STRING);
$product_id = filter_input(INPUT_GET, 'product_id', FILTER_VALIDATE_INT);

if (empty($id))
  throw new Exception("Numéro de devis invalide.");

if ($id == 'new') { // new estimate
  $e = new Estimate();
  $type = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_STRING);
  
  if ($type == "ad_hoc")
    $e->type = Estimate::TYPE_AD_HOC;
  else
    $e->type = Estimate::TYPE_NORMAL;

  if ($client_id = filter_input(INPUT_GET, 'client_id', FILTER_VALIDATE_INT)) {
    if (!$e->importFromClient($client_id))
      throw new Exception("Le client ayant pour identifiant ".$client_id." n'existe pas.");
    $e->save();
    header('Location: estimate-detail.php?id='.$e->id.($product_id?"&product_id=".$product_id:""));
    exit();
  } elseif ($lead_id = filter_input(INPUT_GET, 'lead_id', FILTER_VALIDATE_INT)) {
    if (!$e->importFromLead($lead_id))
      throw new Exception("Le lead n°".$lead_id." n'existe pas.");
    $e->save();
    header('Location: estimate-detail.php?id='.$e->id.'&product_id='.$e->lead->idProduct);
    exit();
  } else {
    $e->save();
    header('Location: estimate-detail.php?id='.$e->id.($product_id?"&product_id=".$product_id:""));
    exit();
  }
}



$q = Doctrine_Query::create()
    ->select('e.*,
              el.*,
              IF(0,el.id,els.nom1) AS sup_name,
              IF(0,el.id,elpfr.ref_name) AS pdt_ref_name,
              IF(0,el.id,elpf.id) AS pdt_cat_id,
              cu.login AS created_user_login,
              uu.login AS updated_user_login,
              l.origin AS lead_source')
    ->from('Estimate e')
    ->leftJoin('e.lines el')            // lines
    ->leftJoin('el.supplier els')       // direct relation to the supplier of this line
    ->leftJoin('el.product elp')        // product
    ->leftJoin('elp.product_fr elpfr')  // "
    ->leftJoin('elp.families elpf')     // "
    ->leftJoin('e.created_user cu')     // bousers
    ->leftJoin('e.updated_user uu')     // "
    ->leftJoin('e.order o')             // child order
    ->leftJoin('e.invoice i')           // child invoice
    ->leftJoin('e.lead l')              // parent supplier lead
    ->where('e.id = ?', $id);
$e = $q->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);

if (empty($e['id'])){
	$sql_delete = "DELETE FROM current_action_vpc WHERE id_ligne_vpc='".$_GET['idCall']."' ";
	mysql_query($sql_delete);
	
	$sql_delete = "DELETE FROM call_spool_vpc WHERE id='".$_GET['idCall']."' ";
	mysql_query($sql_delete);
  throw new Exception("Le devis n°".$id." n'existe pas.");
  
}
$tva_rates = Tva::getFullList($e['created']);

$fdp = 20;
$fdp_franco = 300;
$res = $db->query('select config_name, config_value from config where config_name = \'fdp\' or config_name = \'fdp_franco\'', __FILE__, __LINE__ );
while ($rec = $db->fetch($res))
  $$rec[0] = $rec[1];

$title = $navBar = "Détail du ".$e['type_text']." n°".$e['id'];
$website_origin = $e['website_origin'];



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
<link rel="stylesheet" type="text/css" href="estimates.css" />
<script type="text/javascript" src="estimate-detail.js"></script>
<script type="text/javascript" src="<?php echo ADMIN_URL ?>ressources/js/ajaxSendFax.js"></script>
<script type="text/javascript">
HN.TC.tva_rates = <?php echo json_encode($tva_rates) ?>;
HN.TC.fdp = <?php echo $fdp ?>;
HN.TC.fdp_franco = <?php echo $fdp_franco ?>;
var estimate = new HN.TC.Estimate(<?php echo json_encode($e) ?>, <?php echo $user->id ?>, "<?php echo $user->login ?>");
</script>

<div class="bg">

  <div id="item-cart" class="cart">
    
    <!-- Dialog Boxes -->
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
    
    <!-- Top buttons & links -->
   <?php if ($e['status'] <= Estimate::STATUS_SENT) : ?>
		<?php if ($user->get_permissions()->has("m-comm--sm-estimates","red")){ ?>
    <button id="item-cart-delete" type="button" class="btn ui-state-default ui-corner-all"><span class="icon cancel"></span> Supprimer le devis</button>
		<?php } ?>
   <?php endif ?>
   
   <?php if ($e['status'] != Estimate::STATUS_LOST) : ?>
    <button id="item-cart-cancel" type="button" class="btn ui-state-default ui-corner-all"><span class="icon cancel"></span> Affaire perdue</button>
    <button id="item-cart-schedule-phone-call" type="button" class="btn ui-state-default ui-corner-all"><span class="icon telephone"></span> Prévoir un RDV téléphonique</button>
    <button id="item-cart-save-estimate" type="button" class="btn ui-state-default ui-corner-all save_source"><span class="icon disk"></span> Enregistrer les modifications</button>
    
   <?php endif ?>
    <a class="_blank" href="estimates.php"> Allez à la liste des devis</a>
	<a href="<?= ADMIN_URL ?>clients/?idClient=<?php echo $e['client_id'] ? $e['client_id'] : "" ?>" class="_blank fr" id="item-cart-goto-client">Aller à la fiche client »</a>
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
               <?php foreach (Estimate::$activityList as $k => $v) : ?>
                <option value="<?php echo $k ?>"<?php echo $e['activity']==$k ? " selected=\"selected\"": "" ?>><?php echo $v ?></option>
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
			
            <div>
              <label>CGV associées :</label>
              <select id="item-cart-associated_gsc" class="c_i" data-cart-info="associated_gsc" disabled="disabled">
               <?php foreach (Estimate::$associatedGSCList as $k => $v) : ?>
                <option value="<?php echo $k ?>"<?php echo $e['associated_gsc']==$k ? " selected=\"selected\"": "" ?>><?php echo $v ?></option>
               <?php endforeach ?>
              </select>
              <button type="button" class="btn ui-state-default ui-corner-all">Changer</button>
            </div>
            <div><label>N° de client :</label> <input id="item-cart-client_id" class="c_i" type="text" data-cart-info="client_id" value="<?php echo $e['client_id'] ? $e['client_id'] : "" ?>" readonly="readonly" /> <button type="button" class="btn ui-state-default ui-corner-all">Changer</button></div>
            <div><label>Société :</label> <span class="c_i" data-cart-info="societe"><?php echo to_entities($e['societe']) ?></span></div>
            <div>
              <label>Source :</label>
              <select id="item-cart-source" class="c_i" data-cart-info="source" disabled="disabled">
               <?php foreach (Estimate::$sourceList as $k => $v) : ?>
                <option value="<?php echo $k ?>"<?php echo $e['source']==$k ? " selected=\"selected\"": "" ?>><?php echo $v ?></option>
               <?php endforeach ?>
              </select>
              <button type="button" class="btn ui-state-default ui-corner-all">Changer</button>
            </div>
            <div><label>Etat :</label> <span id="item-cart-status"><?php echo Estimate::getStatusText($e['status']) ?></span></div>
            <div><label>Validité :</label> <input id="item-cart-validity" class="c_i" type="text" data-cart-info="validity" value="<?php echo $e['validity'] ? $e['validity'] : "" ?>" readonly="readonly" /> <button type="button" class="btn ui-state-default ui-corner-all">Changer</button></div>
            <div>
              <label>Mode règlement :</label>
              <select id="item-cart-payment_mode" class="c_i" data-cart-info="payment_mode" disabled="disabled">
               <?php foreach (Estimate::$paymentModeList as $k => $v) : ?>
                <option value="<?php echo $k ?>"<?php echo $e['payment_mode']==$k ? " selected=\"selected\"": "" ?>><?php echo $v ?></option>
               <?php endforeach ?>
              </select>
              <button type="button" class="btn ui-state-default ui-corner-all">Changer</button>
            </div>
            <div>
              <label>Mode de paiment :</label>
              <select id="item-cart-payment_mean" class="c_i" data-cart-info="payment_mean" disabled="disabled">
               <?php foreach (Estimate::$paymentMeanList as $k => $v) : ?>
                <option value="<?php echo $k ?>"<?php echo $e['payment_mean']==$k ? " selected=\"selected\"": "" ?>><?php echo $v ?></option>
               <?php endforeach ?>
              </select>
              <button type="button" class="btn ui-state-default ui-corner-all">Changer</button>
            </div>
            <div>
              <label>Relance :</label>
              <span>
                <input id="item-cart-no_reminder" type="checkbox"<?php if ($e['no_reminder']) : ?> checked="checked"<?php endif ?> />
                ne pas relancer
              </span>
            </div>
          </td>
          <td class="col-2">
            <br />
            <div><label>Email :</label> <input id="item-cart-email" class="c_i" type="text" data-cart-info="email" value="<?php echo to_entities($e['email']) ?>" readonly="readonly"/> <button type="button" class="btn ui-state-default ui-corner-all">Changer</button></div>
            <div><label>Tel :</label> <span class="c_i" data-cart-info="tel"><?php echo Utils::get_dial_html($e['tel']) ?></span></div>
            <div><label>Fax :</label> <span class="c_i" data-cart-info="fax"><?php echo to_entities($e['fax']) ?></span></div>
          <?php if ($e['status'] != Estimate::STATUS_LOST && $user->get_permissions()->has("m-comm--sm-orders-oking", "re")) : ?>
           <?php if ($e['type'] == Estimate::TYPE_NORMAL) : ?>
            <div><label></label> <button id="item-cart-create-order" type="button" class="btn ui-state-default ui-corner-all">Transformer en commande</button></div>
            <div id="item-cart-see-order-line"><label></label> <button id="item-cart-see-order" type="button" class="btn ui-state-default ui-corner-all">Voir la commande</button></div>
            <div id="item-cart-generate-invoice-line"><label></label> <button id="item-cart-generate-invoice" type="button" class="btn ui-state-default ui-corner-all">Générer la facture</button></div>
            <div id="item-cart-see-invoice-line"><label></label> <button id="item-cart-see-invoice" type="button" class="btn ui-state-default ui-corner-all">Voir la facture</button></div>
           <?php elseif ($e['type'] == Estimate::TYPE_AD_HOC) : ?>
            <div><label></label> <button id="item-cart-set-status-won" type="button" class="btn ui-state-default ui-corner-all">Passer en gagné</button></div>
            <div id="item-cart-generate-invoice-line"><label></label> <button id="item-cart-generate-invoice" type="button" class="btn ui-state-default ui-corner-all">Générer la facture</button></div>
            <div id="item-cart-see-invoice-line"><label></label> <button id="item-cart-see-invoice" type="button" class="btn ui-state-default ui-corner-all">Voir la facture</button></div>
           <?php endif ?>
          <?php endif // lost ?>
              <?php // contacts secondaires
              $listContacts = Doctrine_Query::create()
                      ->select()
                      ->from('ClientsContacts')
                      ->where('client_id = ?', $e['client_id'])
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
            <div style="display : inline-block"><input type="text" name="secondaryContacts" value="" /><?php if(count($listContacts)): ?> <div class="iconAddSecondaryContact ui-icon ui-icon-circle-plus" data-id-client="<?php echo $e['client_id'] ? $e['client_id'] : "" ?>" ></div><?php endif ; ?></div>
          </td>
          <td class="col-3">
            <br />
            <div><label>Devis créé par :</label> <span><?php echo to_entities($e['created_user_login']) ?></span></div>
            <div><label>Date de création :</label> <span><?php echo date('d/m/Y H:i:s', $e['created']) ?></span></div>
            <div><label>Date dernière mise à jour :</label> <span id="item-cart-updated"><?php echo date('d/m/Y H:i:s', $e['updated']) ?></span></div>
            <div><label>Dernière mise à jour par :</label> <span id="item-cart-updated_user_login"><?php echo to_entities($e['updated_user_login']) ?></span></div>
            <div>
              <label>ID lead d'origine :</label> <span><?php echo ($e['lead_id'] ? $e['lead_id'] : "Aucun") ?></span>
           <?php if ($e['lead_id']) : ?>
              <button id="item-cart-see-supplier-lead" type="button" class="btn ui-state-default ui-corner-all">Voir</button>
            </div>
            <div><label>Source lead :</label> <span><?php echo $e['lead_source'] ?></span></div>
           <?php else : ?>
            </div>
           <?php endif ?>
            <div><label>Vu par le client :</label> <span class="icon <?php echo $e['client_seen'] ? "accept" : "cross" ?> no-pointer"></span></div>
          </td>
        </tr>
      </tbody>
    </table>
    
    <div id="item-cart-recommended-products" class="recommended-products">
      <i>Produits recommandés</i>
      <ul class="entries clearfix">
        <li class="entry" data-id="5023413">
          <div class="pic"><a href=""><img src="http://www.techni-contact.com/ressources/images/produits/thumb_small/poteau-guide-files-5023413-1.jpg" class="vmaib" /></a><div class="vsma"></div></div>
          <div class="title">Poteau guide-files</div>
          <div class="actions"><span class="icon page-white-add" title="créer un lead avec ce produit"></span><span class="icon basket-put" title="ajouter au devis"></span></div>
        </li>
        <li class="entry" data-id="14172177">
          <div class="pic"><img src="http://www.techni-contact.com/ressources/images/produits/thumb_small/poteau-guide-files-5023413-1.jpg" class="vmaib" /><div class="vsma"></div></div>
          <div class="title">Poteau guide-files</div>
          <div class="actions"><span class="icon page-white-add" title="créer un lead avec ce produit"></span><span class="icon basket-put" title="ajouter au devis"></span></div>
        </li>
        <li class="entry" data-id="7292635">
          <div class="pic"><img src="http://www.techni-contact.com/ressources/images/produits/thumb_small/poteau-guide-files-5023413-1.jpg" class="vmaib" /><div class="vsma"></div></div>
          <div class="title">Poteau guide-files</div>
          <div class="actions"><span class="icon page-white-add" title="créer un lead avec ce produit"></span><span class="icon basket-put" title="ajouter au devis"></span></div>
        </li>
        <li class="entry" data-id="2178628">
          <div class="pic"><img src="http://www.techni-contact.com/ressources/images/produits/thumb_small/poteau-guide-files-5023413-1.jpg" class="vmaib" /><div class="vsma"></div></div>
          <div class="title">Poteau guide-files</div>
          <div class="actions"><span class="icon page-white-add" title="créer un lead avec ce produit"></span><span class="icon basket-put" title="ajouter au devis"></span></div>
        </li>
      </ul>
    </div>
    
    <!-- cart items vat, global comment and totals -->
   <?php if ($e['status'] != Estimate::STATUS_LOST) : ?>
    <button id="item-cart-add-line" type="button" class="btn ui-state-default ui-corner-all fr">Ajouter une ligne produit</button>
    <button id="item-cart-create-ref" type="button" class="btn ui-state-default ui-corner-all fr">Créer un produit en base</button>
   <?php endif ?>
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
       <?php if ($e['type'] == Estimate::TYPE_NORMAL) : ?>
        <tr id="item-cart-fdp-line">
          <td>Frais de Port HT :</td>
          <td id="item-cart-fdp-ht">0.00€</td>
        </tr>
       <?php endif ?>
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
            <div><span class="c_i" data-cart-info="comment" data-edit-type="textarea"><?php echo to_entities($e['comment']) ?></span></div>
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
            <div><span class="c_i titre" data-cart-info="titre" data-edit-type="select-title"><?php echo to_entities(Clients::getTitleText($e['titre'])) ?></span> <span class="c_i lastname" data-cart-info="nom"><?php echo to_entities($e['nom']) ?></span> <span class="c_i firstname" data-cart-info="prenom"><?php echo to_entities($e['prenom']) ?></span></div>
            <div><span class="c_i company" data-cart-info="societe"><?php echo to_entities($e['societe']) ?></span></div>
            <div><span class="c_i road" data-cart-info="adresse"><?php echo to_entities($e['adresse']) ?></span></div>
            <div><span class="c_i road" data-cart-info="cadresse"><?php echo to_entities($e['cadresse']) ?></span></div>
            <div><span class="c_i pc" data-cart-info="cp"><?php echo to_entities($e['cp']) ?></span> <span class="c_i city" data-cart-info="ville"><?php echo to_entities($e['ville']) ?></span></div>
            <div><span class="c_i country" data-cart-info="pays"><?php echo to_entities($e['pays']) ?></span></div>
            <div id="item-cart-tva-intra-line">TVA intra : <span class="c_i tva_intra" data-cart-info="tva_intra"><?php echo to_entities($e['tva_intra']) ?></span></div>
            <div>tel : <span class="c_i phone" data-cart-info="tel"><?php echo Utils::get_dial_html($e['tel']) ?></span></div>
          </td>
          <td>
            <div><span class="c_i titre" data-cart-info="titre2" data-edit-type="select-title"><?php echo to_entities(Clients::getTitleText($e['titre2'])) ?></span> <span class="c_i lastname" data-cart-info="nom2"><?php echo to_entities($e['nom2']) ?></span> <span class="c_i firstname" data-cart-info="prenom2"><?php echo to_entities($e['prenom2']) ?></span></div>
            <div><span class="c_i company" data-cart-info="societe2"><?php echo to_entities($e['societe2']) ?></span></div>
            <div><span class="c_i road" data-cart-info="adresse2"><?php echo to_entities($e['adresse2']) ?></span></div>
            <div><span class="c_i road" data-cart-info="cadresse2"><?php echo to_entities($e['cadresse2']) ?></span></div>
            <div><span class="c_i pc" data-cart-info="cp2"><?php echo to_entities($e['cp2']) ?></span> <span class="c_i city" data-cart-info="ville2"><?php echo to_entities($e['ville2']) ?></span></div>
            <div><span class="c_i country" data-cart-info="pays2"><?php echo to_entities($e['pays2']) ?></span></div>
            <div>tel : <span class="c_i phone" data-cart-info="tel2"><?php echo Utils::get_dial_html($e['tel2']) ?></span></div>
          </td>
          <td>
            <div><span class="c_i" data-cart-info="delivery_infos" data-edit-type="textarea"><?php echo to_entities($e['delivery_infos']) ?></span></div>
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
					Notes internes liées à ce devis
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
				Sélectionnez le document à ce devis (PDF, Document Word ou image '.jpg')<br />
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
	<input type="hidden" id="id_client" value="<?= $e['client_id'] ?>" />
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
        <div class="title">Conversation liée à ce devis<span class="icon-fold folded">+</span><span class="icon-fold unfolded">-</span></div>
        <div class="messages fold-content">
          <ul>
          </ul>
        </div>
      </div>
    </div>
    <br />
	
    <?php
	if(isset($_GET['params'])){
		if( $_GET['params'] == 'display_bars'){ 
			$sql_updsate = "UPDATE call_spool_vpc SET `ligne_active`='1',call_operator='".$_SESSION["id"]."'
							WHERE id='".$_GET['idCall']."' ";
			mysql_query($sql_updsate);
			
			$sql_compagne  = "SELECT campaign_name,estimate_id FROM call_spool_vpc WHERE id='".$_GET['idCall']."' ";
			$req_compagne  = mysql_query($sql_compagne);
			$data_compagne = mysql_fetch_object($req_compagne);
			
			$sql_societe   = "SELECT societe FROM `estimate` WHERE id='".$data_compagne->estimate_id."' "; 
			$req_societe   = mysql_query($sql_societe);
			$data_societe  = mysql_fetch_object($req_societe);
			
		?>
		<div id="bottomBar">
			<div style="visibility: visible; margin-top: -50px;width:500px;" id="callBar">
				<div style="visibility : visible;padding: 5px;width: 275px;margin-left: 120px;" id="inCallbar">
					<div class="name_campgne">Relance devis société <?= $data_societe->societe ?></div>
					<div>
					<a class="btn ui-state-default ui-corner-all" style="width: 68px;" onclick="setCallOkVPC(<?= $_GET['idCall'] ?>)" href="#"> Client joint</a>
					<a class="btn ui-statelike-choice-no ui-corner-all" onclick="setCallNokVPC(<?= $_GET['idCall'] ?>)" href="#" style="width: 107px;"> Appel en absence</a>
					</div>
				</div>
			</div>
		</div>
	<?php }
	}
	?> 

	
    <!-- bottom buttons -->
  <?php if ($e['status'] != Estimate::STATUS_LOST) : ?>
    <button id="item-cart-print-estimate" class="btn ui-state-default ui-corner-all fl">Imprimer le devis</button>
    
    <button id="item-cart-print-pro-forma-invoice" class="btn ui-state-default ui-corner-all fl">Facture pro forma</button>
    
   <?php if ($e['status'] == Estimate::STATUS_IN_PROCESS) : ?>
    <button id="item-cart-send-estimate" class="btn ui-state-default ui-corner-all fr">Envoyer le devis</button>
   <?php endif ?>
   <?php if ($e['status'] != Estimate::STATUS_WON) : ?>
    <button id="item-cart-update-estimate" class="btn ui-state-default ui-corner-all fr">Mettre à jour le devis</button>
   <?php endif ?>
    <button id="item-cart-resend-estimate" class="btn ui-state-default ui-corner-all fr">Renvoyer le devis</button>
    <div class="zero"></div>
    <br />
    <?php if(!empty($e['fax'])): ?>
    <button id="item-cart-send-fax" data-type="estimate" data-number="<?php echo $e['fax']; ?>" data-id-item="<?php echo $e['id']; ?>" class="btn ui-state-default ui-corner-all fl">Devis par fax</button>
    <?php endif; ?>
    <div class="zero"></div>
  <?php endif // lost ?>
  </div>
</div>
<script type="text/javascript">
  estimate.htmlInit();
 <?php if ($product_id) : ?>
  estimate.ci.addAutocompleteLine(<?php echo $product_id ?>);
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
				url: '../ressources/ajax/AJAX_update_source.php?action=estimate&id_send='+<?= $id ?>+'&source='+source,
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
				url: '../ressources/ajax/AJAX_update_source.php?action=estimate&id_send='+<?= $id ?>+'&source='+source,
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

<script>
$(document).ready(function(){
	$('.editable').bind('blur', function(){
		alert("This input field has lost its focus.");
	});
});
</script>
