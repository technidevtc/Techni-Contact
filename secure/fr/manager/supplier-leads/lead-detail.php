<?php
if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

$db = DBHandle::get_instance();

$id = isset($_GET['id']) ? (int)trim($_GET['id']) : 0;
$title = $navBar = "Détail du devis fournisseur n°".$id;
require(ADMIN."head.php");

try {
  if (!$user->get_permissions()->has("m-comm--sm-supplier-leads","r"))
    throw new Exception("Vous n'avez pas les droits adéquats pour réaliser cette opération.");

  if ($id == 0)
    throw new Exception("Identifiant devis fournisseur non valide.");

    $res1 = $db->query("
    SELECT
      c.id,
      c.email,
      FROM_UNIXTIME(c.timestamp,'%Y %j') as daytime
    FROM contacts c
    LEFT JOIN products p ON p.id = c.idProduct
    LEFT JOIN products_fr pfr ON pfr.id = p.id
    LEFT JOIN advertisers a ON a.id = c.idAdvertiser AND a.actif = 1 AND a.category = ".__ADV_CAT_SUPPLIER__."
    LEFT JOIN references_content rc ON rc.idProduct = p.id AND rc.classement = 1 AND rc.vpc = 1 AND rc.deleted = 0
    LEFT JOIN families_fr ffr ON ffr.id = c.idFamily
    LEFT JOIN bo_users bou1 ON bou1.id = c.id_user_commercial
    LEFT JOIN bo_users bou2 ON bou2.id = c.id_user_processed
    STRAIGHT_JOIN (
        SELECT
          SUM(IF(config_name='fdp', config_value, 0)) AS fdp,
          SUM(IF(config_name='fdp_franco', config_value, 0)) AS fdp_franco
        FROM config
        WHERE config_name IN ('fdp', 'fdp_franco')
    ) pdt_fdp
    WHERE c.id = ".$id, __FILE__, __LINE__);

  if ($db->numrows($res1) != 1){
	$sql_delete = "DELETE FROM current_action_vpc WHERE id_ligne_vpc='".$_GET['idCall']."' ";
	mysql_query($sql_delete);
  throw new Exception("Le devis fournisseur n°".$id." n'existe pas");
  }
  $leadPrincipal = $db->fetchAssoc($res1);

  $res2 = $db->query("
    SELECT
      c.id,
      c.idFamily as cat_id,
      c.timestamp AS date,
      c.nom,
      c.prenom,
      c.fonction,
      c.societe,
      c.salaries,
      c.secteur,
      c.naf,
      c.siret,
      c.adresse,
      c.cadresse,
      c.cp,
      c.ville,
      c.pays,
      c.tel,
      c.fax,
      c.email,
      c.url,
      c.precisions,
      c.precisions_additional,
      c.type,
      c.campaignID,
      c.customFields,
      c.invoice_status,
      c.income,
      c.income_total,
      c.parent,
      c.reject_timestamp,
      c.credited_on,
      c.processing_status,
      pfr.name AS pdt_name,
      pfr.fastdesc AS pdt_fastdesc,
      pfr.id AS pdt_id,
      pfr.ref_name AS pdt_ref_name,
      pfr.descc AS pdt_descc,
      IF(p.warranty='', a.warranty, p.warranty) AS pdt_warranty,
      IF(pfr.delai_livraison='', a.delai_livraison, pfr.delai_livraison) AS pdt_delivery_time,
      IF(a.contraintePrix>0 AND IFNULL(rc.price2, p.price2)>0, a.contraintePrix, 0) AS pdt_adv_min_amount,
      a.id AS adv_id,
      a.nom1 AS adv_name,
      a.category AS adv_cat,
      a.is_fields AS adv_is_fields,
      rc.price AS pdt_price,
      ffr.name AS cat_name,
      ffr.ref_name as cat_ref_name,
      IFNULL(bou1.name,'-') AS com_name,
      IFNULL(bou2.name,'-') AS com_p_name,
          FROM_UNIXTIME(c.timestamp,'%Y %j') as daytime
    FROM contacts c
    LEFT JOIN products p ON p.id = c.idProduct
    INNER JOIN products_fr pfr ON pfr.id = c.idProduct AND pfr.active = 1
    INNER JOIN advertisers a ON a.id = c.idAdvertiser AND a.actif = 1 AND a.category = ".__ADV_CAT_SUPPLIER__."
    LEFT JOIN references_content rc ON rc.idProduct = p.id AND rc.classement = 1 AND rc.vpc = 1 AND rc.deleted = 0
    LEFT JOIN families_fr ffr ON ffr.id = c.idFamily
    LEFT JOIN bo_users bou1 ON bou1.id = c.id_user_commercial
    LEFT JOIN bo_users bou2 ON bou2.id = c.id_user_processed
    STRAIGHT_JOIN (
        SELECT
          SUM(IF(config_name='fdp', config_value, 0)) AS fdp,
          SUM(IF(config_name='fdp_franco', config_value, 0)) AS fdp_franco
        FROM config
        WHERE config_name IN ('fdp', 'fdp_franco')
    ) pdt_fdp
    WHERE c.email = '".$leadPrincipal['email']."' and FROM_UNIXTIME(c.timestamp,'%Y %j') = '".$leadPrincipal['daytime']."'
    ORDER BY daytime DESC, c.email, c.timestamp ASC, c.id ASC", __FILE__, __LINE__);

  if ($db->numrows($res2) < 1){
	$sql_delete = "DELETE FROM current_action_vpc WHERE id_ligne_vpc='".$_GET['idCall']."' ";
	mysql_query($sql_delete);
	
	$sql_delete = "DELETE FROM call_spool_vpc WHERE id='".$_GET['idCall']."' ";
	mysql_query($sql_delete);
	
	throw new Exception("Le devis fournisseur n°".$id." n'existe pas");
  }
  $lead_number = 1;
  while($lead = $db->fetchAssoc($res2)){
    $id = $lead['id'];
    $listIds .= $lead_number == 1 ? $id : ','.$id;

  // stocking values for Rendez-vous module
  if($lead_number == 1){
    $RdvLeadID = $id;
    $RdvCustomerMail = strtolower($lead["email"]);
  }
  // loading references
  $lead["pdt_refs"] = array();
  $pdt_max_margin = 1;
  $res3 = $db->query("
    SELECT content
    FROM references_cols
    WHERE idProduct = ".$lead["pdt_id"], __FILE__, __LINE__);
  list($content_cols) = $db->fetch($res3);
  $content_cols = mb_unserialize($content_cols);
  $pdt_ref_headers = array_slice($content_cols, 3, -5);

  $res4 = $db->query("
    SELECT id, label, content, refSupplier, price, price2, idTVA, unite
    FROM references_content
    WHERE idProduct = ".$lead["pdt_id"]." AND vpc = 1 AND deleted = 0
    ORDER BY classement", __FILE__, __LINE__);
  while ($ref = $db->fetchAssoc($res4)) {
    $ref["content"] = mb_unserialize($ref["content"]);
    if ($ref["price2"] > 0 && $pdt_max_margin < $ref["price"]/$ref["price2"])
      $pdt_max_margin = $ref["price"]/$ref["price2"];
    $lead["pdt_refs"][] = $ref;
  }

  $lead["pdt_ref_count"] = count($pdt["refs"]);
  $lead["pdt_adv_min_amount"] = $lead["pdt_adv_min_amount"] > 0 ? sprintf("%.2f", $lead["pdt_adv_min_amount"]*$pdt_max_margin)."€ HT" : "-";
  $lead["pdt_shipping_fee"] = $lead["pdt_fdp"] == 0 ? "Offerts" : $lead["pdt_fdp"]." € HT";

  $customFields = mb_unserialize($lead['customFields']);
  if (empty($customFields))
    $customFields = array();

  // single lead cost
  if ($lead["adv_is_fields"] != "") $lead["adv_is_fields"] = mb_unserialize($lead["adv_is_fields"]);
  else $lead["adv_is_fields"] = array();

  if (!empty($lead["adv_is_fields"]))
    $is_fields = $lead["adv_is_fields"][0];

  $fo_pdt_url = URL."produits/".$lead["cat_id"]."-".$lead["pdt_id"]."-".$lead["pdt_ref_name"].".html";
  $fo_cat_url = URL."familles/".$lead["cat_ref_name"].".html";
  $lead["pdt_pic"]["thumb_small"] = is_file(PRODUCTS_IMAGE_INC."thumb_small/".$lead["pdt_id"]."-1.jpg") ? PRODUCTS_IMAGE_SECURE_URL."thumb_small/".$lead["pdt_id"]."-1.jpg" : PRODUCTS_IMAGE_SECURE_URL."no-pic-thumb_small.gif";
  $lead["pdt_pic"]["card"] = is_file(PRODUCTS_IMAGE_INC."card/".$lead["pdt_id"]."-1.jpg") ? PRODUCTS_IMAGE_SECURE_URL."card/".$lead["pdt_id"]."-1.jpg" : PRODUCTS_IMAGE_SECURE_URL."no-pic-card.gif";
  $bo_pdt_url = ADMIN_URL."products/edit.php?id=".$lead["pdt_id"];
  $bo_adv_url = ADMIN_URL."advertisers/edit.php?id=".$lead["adv_id"];
  $adv_cat_name = $adv_cat_list[$lead["adv_cat"]]["name"];
  $cat3_bo_pdt_list = ADMIN_URL."search.php?search_type=2&search=".$lead["cat_id"];

 
  if($lead_number == 1){
?>

<link rel="stylesheet" type="text/css" href="leads.css" />
<link href="<?php echo ADMIN_URL ?>css/ui/ui.datepicker.css" rel="stylesheet" title="style" media="all" />
<div class="section">
	<a href="leads.php">&lt;&lt; Retourner à la liste des devis fournisseurs</a><br/>
	<br/>
  <div id="listeRdv"></div>
	<div class="block">
		<div class="title">Détails du devis fournisseur n°<?php echo $id ?> du <?php echo date("d/m/Y à H:i", $lead["date"]) ?></div>
		<div class="text">
      <div class="fr" style="text-align: center">
        <form action="print.php" method="post">
          <button id="btn-create-estimate" class="btn ui-state-default ui-corner-all"><span class="icon page-white-add"></span> Créer un devis</button>
          <button id="rdvLayerButton" class="btn ui-state-default ui-corner-all"><span class="icon telephone"></span> Prévoir un RDV téléphonique</button>
          <input type="hidden" name="leadIds"/>
          <button id="btn-print-slead" class="btn ui-state-default ui-corner-all"><span class="icon printer"></span> Imprimer</button>
        </form>
        <span id="errorRdv" class="error"></span>
      </div>
			<div class="label">Nom du produit :</div>
			<div class="value">
				<a href="<?php echo $bo_pdt_url ?>" target="_blank"><?php echo $lead["pdt_name"] ?></a>
				<a href="<?php echo $fo_pdt_url ?>" target="_blank"><img src="../ressources/icons/monitor_go.png" alt="" class="view-fo" title="Voir la fiche en ligne"/></a>
			</div>
			<div class="zero"></div>
			<div class="label">Famille du produit :</div>
			<div class="value">
				<a href="<?php echo $cat3_bo_pdt_list ?>" target="_blank"><?php echo $lead["cat_name"] ?></a>
				<a href="<?php echo $fo_cat_url ?>" target="_blank"><img src="../ressources/icons/monitor_go.png" alt="" class="view-fo" title="Voir la famille du produit en ligne"></a>
			</div>
			<div class="zero"></div>
			<div class="label">Description rapide du produit :</div>
			<div class="value"><?php if(!empty($lead["pdt_fastdesc"])) { echo $lead["pdt_fastdesc"]; } else { ?><i class="lightgray">lead secondaire</i><?php } ?></div>
			<div class="zero"></div>
			<div class="label">Annonceur :</div>
			<div class="value"><a href="<?php echo $bo_adv_url ?>" target="_blank"><?php echo $lead["adv_name"] ?> (<?php echo $lead["adv_id"] ?>)</a></div>
			<div class="zero"></div>
			<div class="label">Commercial responsable :</div>
			<div class="value"><?php echo $lead["com_name"] ?></div>
			<div class="zero"></div>
			<div class="label">Commercial ayant envoyé le devis :</div>
			<div class="value">
        <span id="user-name-processed"><?php echo $lead["com_p_name"] ?></span>
      </div>
			<div class="zero"></div>
         <?php if($lead_number == 1){ // only for principal lead ?>
			<div class="label">Etat de traitement :</div>
			<div class="value">
                          <span id="processing-status">
                            <?php echo $lead_processing_status_list[$lead["processing_status"]] ?>
                            <?php if ($lead["processing_status"] != __LEAD_P_STATUS_NOT_PROCESSABLE__) { ?>
                              - <a href="#3">Devis intraitable</a>
                            <?php } ?>
                          </span>
        
			</div>
			<div class="zero"></div>
         <?php } ?>
			<div class="label">Message facultatif :</div>
			<div class="value"><?php echo (!empty($lead["precisions"]) ? $lead["precisions"] : "N/C") ?></div>
			<div class="zero"></div>
			
		<?php
			if(!empty($lead["precisions_additional"])){
				
				$note = strlen($lead["precisions_additional"]);
				if($note > 35){
					echo '<input type="hidden" value="1" id="precision_hidden"/>';
					$chaine_note = substr($lead["precisions_additional"], 0, 35);
					echo '<div class="label">Note sur Lead :</div>
						<div class="value">'.$chaine_note.'...</div> <div id="show_hide_suite" style="cursor:pointer; color:#336600" onclick="affiche_suite();">Afficher la suite  </div>
						<div id="text_message" style="display:none;"><textarea rows="8" cols="35">'.$lead["precisions_additional"].'</textarea></div>
						<div class="zero"></div>';
				}else {
					echo '<div class="label">Note sur Lead :</div>
						<div class="value">'.$lead["precisions_additional"].'</div>
						<div class="zero"></div>';
				}
				
			}
		?>
		</div>
	</div>
	<br/>
	<div class="block">
		<div class="title">Informations personnelles de l'internaute</div>
		<div class="text">
			<div class="label">Nom :</div>
			<div class="value"><?php echo $lead["nom"] ?></div>
			<div class="zero"></div>
			<div class="label">Prénom :</div>
			<div class="value"><?php echo ucwords(strtolower($lead["prenom"])) ?></div>
			<div class="zero"></div>
			<div class="label">Fonction :</div>
			<div class="value"><?php echo ucwords(strtolower($lead["fonction"])) ?></div>
			<div class="zero"></div>
			<div class="label">Email :</div>
			<div class="value"><?php echo strtolower($lead["email"]) ?></div>
			<div class="zero"></div>
			<div class="label">Site Internet :</div>
			<div class="value"><?php echo (!empty($lead["url"]) ? $lead["url"] : "N/C") ?></div>
			<div class="zero"></div>
			<div class="label">Téléphone :  </div>
      <div class="value"><a href="tel:<?php echo preg_replace('/[^0-9\+.]?/', '', $lead["tel"]) ?>"><?php echo preg_replace('/[^0-9\+.]?/', '', $lead["tel"]) ?> <img src="../ressources/icons/telephone.png" alt="tel"  style="vertical-align:middle" /></a></div>
			<div class="zero"></div>
			<div class="label">Fax :</div>
			<div class="value"><?php echo (!empty($lead["fax"]) ? $lead["fax"] : "N/C") ?></div>
			<div class="zero"></div>
		</div>
	</div>
	<br/>
	<div class="block">
		<div class="title">Informations sur son entreprise</div>
		<div class="text">
			<div class="label">Nom de la société :</div>
			<div class="value"><?php echo $lead["societe"] ?></div>
			<div class="zero"></div>
			<div class="label">Nb de salarié :</div>
			<div class="value"><?php echo (!empty($lead["salaries"]) ? $lead["salaries"] : "N/C") ?></div>
			<div class="zero"></div>
			<div class="label">Secteur d'activité :</div>
			<div class="value"><?php echo ucwords(strtolower($lead["secteur"])) ?></div>
			<div class="zero"></div>
			<div class="label">Code NAF :</div>
			<div class="value"><?php echo (!empty($lead["naf"]) ? $lead["naf"] : "N/C") ?> </div>
			<div class="zero"></div>
			<div class="label">SIRET :</div>
			<div class="value"><?php echo (!empty($lead["siret"]) ? $lead["siret"] : "N/C") ?></div>
			<div class="zero"></div>
			<div class="label">Adresse :</div>
			<div class="value"><?php echo ucwords(strtolower($lead["adresse"])) ?><?php if(!empty($lead["cadresse"])) { ?><br/><?php echo ucwords(strtolower($lead["cadresse"])) ?><?php } ?></div>
			<div class="zero"></div>
			<div class="label">Code Postal :</div>
			<div class="value"><?php echo $lead["cp"] ?></div>
			<div class="zero"></div>
			<div class="label">Ville :</div>
			<div class="value"><?php echo $lead["ville"] ?></div>
			<div class="zero"></div>
			<div class="label">Pays :</div>
			<div class="value"><?php echo $lead["pays"] ?></div>
			<div class="zero"></div>
		 <?php foreach($customFields as $fieldName => $fieldData) { ?>
			<div class="label"><?php echo $fieldName ?> :</div>
			<div class="value"><?php echo to_entities($fieldData) ?></div>
			<div class="zero"></div>
		 <?php } ?>
		</div>
	</div>
  <br/>
  <?php
   }// end if($lead_number == 1)
?>
  <div id="sld_pdt_preview" class="block">
    <div class="title">Information produit <?php echo $lead_number ?></div>
    <div class="pdt-preview">
      <div class="picture"><img class="vmaib" src="<?php echo $lead["pdt_pic"]["thumb_small"] ?>"/><div class="vsma"></div></div>
      <div class="infos">
        <div class="vmaib">
          <a class="_blank" href="<?php echo $fo_pdt_url ?>" title="Voir la fiche en ligne"><img src="../ressources/icons/monitor_go.png" alt="" class="view-fo"/></a>
          <a class="_blank" href="<?php echo $bo_pdt_url ?>" title="Editer la fiche produit"><strong><?php echo $lead["pdt_name"] ?></strong></a><br/>
          <span><?php echo $lead["pdt_fastdesc"] ?></span><br/>
          Code fiche produit: <strong><?php echo $lead["pdt_id"] ?></strong><br/>
          Famille : <a class="_blank" href="<?php echo $fo_pdt_url ?>"><strong><?php echo $lead["cat_name"] ?></strong></a><br/>
          <span><?php echo $adv_cat_name ?></span> : <a class="_blank" href="<?php echo $bo_adv_url ?>"><strong><?php echo $lead["adv_name"] ?></strong></a><br/>
          <a id="sld_see_pdt_sheet" href="#pdt_sheet">Voir description produit</a>
        </div><div class="vsma"></div>
      </div>
      <div class="zero"></div>
    </div>
    <?php if($lead_number > 1){ ?>
    <div class="fr" style="width: 600px">
      <div class="label">Message facultatif :</div>
      <div class="value"><?php echo (!empty($lead["precisions"]) ? $lead["precisions"] : "N/C") ?></div>
    </div>
    <?php } ?>
    <div class="zero"></div>
  </div>
  <div id="sld_pdt_sheet" class="layer">
    <img class="close" src="../ressources/images/empty.gif" alt=""/>
    <div class="title">Aperçu fiche produit</div>
    <div class="text pdt-sheet">
      <div class="infos">
        <div class="infos-head">
          <h1><?php echo $lead["pdt_name"] ?></h1>
          <strong><?php echo $lead["pdt_fastdesc"] ?></strong><br/>
          Code fiche produit: <span><?php echo $lead["pdt_id"] ?></span><br/>
          Partenaire : <strong><?php echo $lead["adv_name"] ?></strong> (<span><?php echo $adv_cat_name ?></span>)<br/>
        </div>
        <div class="picture"><img class="vmaib" src="<?php echo $lead["pdt_pic"]["card"] ?>"/><div class="vsma"></div></div>
        <div class="infos-right">
          <div class="pdt_price">à partir de<br/><strong><?php echo sprintf("%.2f",$lead["pdt_price"]) ?>€ HT</strong></div>
          Frais de port : <span><?php echo $lead["pdt_shipping_fee"] ?></span><br/>
          Commande minimum : <span><?php echo $lead["pdt_adv_min_amount"] ?></span><br/>
          Livraison : <span><?php echo $lead["pdt_delivery_time"] ?></span><br/>
          Garantie : <span><?php echo $lead["pdt_warranty"] ?></span>
        </div>
        <div class="zero"></div>
      </div>
      <div class="desc">
        <h2>Description</h2>
        <div><?php echo $lead["pdt_descc"] ?></div>
      </div>
      <div id="pdt_refs" class="refs">
        <table>
          <thead>
            <tr>
              <th>Réf. TC</th>
              <th>Libellé</th>
             <?php foreach ($pdt_ref_headers as $pdt_ref_header) {?>
              <th><?php echo $pdt_ref_header ?></th>
             <?php } ?>
              <th>Prix HT</th>
            </tr>
          </thead>
          <tbody>
           <?php foreach ($lead["pdt_refs"] as $pdt_ref) {?>
            <tr>
              <td><?php echo $pdt_ref["id"] ?></td>
              <td><?php echo $pdt_ref["label"] ?></td>
             <?php foreach ($pdt_ref["content"] as $pdt_ref_ccol) { ?>
              <td><?php echo $pdt_ref_ccol ?></td>
             <?php } ?>
              <td><?php echo sprintf("%.2f",$pdt_ref["price"]) ?>€ HT</td>
            </tr>
           <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

<?php
  $lead_number++;
  }
?>

  <div id="actions-dialog" title="Devis intraitable">
    Vous considérez ce devis comme intraitable<br/>
    Un mail sera donc envoyé à la société <span id="att-company"></span> (<span id="att-email"></span>) pour l'en avertir.<br/>
    <br/>
    Merci de préciser le motif de votre décision :<br/>
    <select>
      <option value="Nous avons besoin de plus d'informations concernant votre besoin">Nous avons besoin de plus d'informations concernant votre besoin</option>
      <option value="Nous n'assurons la livraison qu'en France">Nous n'assurons la livraison qu'en France</option>
      <option value="Nous ne faisons pas / plus le produit demandé">Nous ne faisons pas / plus le produit demandé</option>
      <option value="Nous ne pouvons répondre à votre demande dans le budget alloué">Nous ne pouvons répondre à votre demande dans le budget alloué</option>
      <option value="Autre">Autre</option>
    </select><br/>
    Commentaires : <textarea rows="3" cols="37"></textarea>
  </div>
</div>


<?php
	if(isset($_GET['params'])){
		if( $_GET['params'] == 'display_bars'){ 
			$sql_updsate = "UPDATE call_spool_vpc SET `ligne_active`='1',call_operator='".$_SESSION["id"]."'
							WHERE id='".$_GET['idCall']."' ";
			mysql_query($sql_updsate);
			
			$sql_compagne  = "SELECT campaign_name,client_id FROM call_spool_vpc WHERE id='".$_GET['idCall']."' ";
			$req_compagne  = mysql_query($sql_compagne);
			$data_compagne = mysql_fetch_object($req_compagne);
			
			$sql_societe   = "SELECT societe FROM `contacts` WHERE id='".$data_compagne->client_id."' "; 
			$req_societe   = mysql_query($sql_societe);
			$data_societe  = mysql_fetch_object($req_societe);
		?>
		<div id="bottomBar">
			<div style="visibility: visible; margin-top: -50px;width:500px;" id="callBar">
				<div style="visibility : visible;padding: 5px;" id="">
					<div class="name_campgne">Requalif lead société <?= $data_societe->societe ?></div>
					<div style="margin-left: 130px;">
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
</script>	

<script type="text/javascript">
  $("#btn-create-estimate").on("click", function(){
    document.location.href="<?php echo ADMIN_URL ?>estimates/estimate-detail.php?id=new&lead_id=<?php echo $id ?>";
    return false;
  });
  $("#btn-print-slead").click(function(){
    var leadIds = [<?php echo $listIds ?>];
    var form = $(this).closest("form").get(0);
    form.leadIds.value = leadIds.join(",");
    form.submit();
  });
  $("#rdvLayerButton").on("click", function(){
    $("#rdv-dialog").dialog('open');
    $.datepicker.setDefaults($.datepicker.regional['']);
    $( "#datepicker" ).datepicker($.datepicker.regional['fr']);
    $( "#datepicker" ).datepicker("option", "minDate", 0);
    $('#errorRdv').html('');
    return false;
  });
  
  $("#processing-status a").live("click",function(){
    var newStatus = $(this).attr("href").substring(1);
    if (newStatus == <?php echo __LEAD_P_STATUS_NOT_PROCESSABLE__ ?>)
      $("#actions-dialog").dialog("open");
    else
      setProcessingStatus(<?php echo $id ?>, <?php echo __LEAD_P_STATUS_PROCESSED__ ?>, {});
    return false;
  });

  $("#actions-dialog").dialog({
    width: 470,
    autoOpen: false,
    modal: true,
    buttons: {
      "Envoyer": function(){
        setProcessingStatus(<?php echo $id ?>, <?php echo __LEAD_P_STATUS_NOT_PROCESSABLE__ ?>, {
          reason: $("#actions-dialog").find("select").val(),
          comment: $("#actions-dialog").find("textarea").val()
        });
        $("#actions-dialog").dialog("close");
      }
    }
  });

  function setProcessingStatus(leadId, pStatus, vars){
    var data = {"actions":[{"action":"set_processing_status","leadId":leadId,"status":pStatus}]}
    $.extend(data.actions[0], vars);
    $.ajax({
      type: "POST",
      url: "AJAX_interface.php",
      data: data,
      dataType: "json",
      error: function (XMLHttpRequest, textStatus, errorThrown) {
      },
      success: function (r, textStatus) {
        if (!r.error) {
          $("#user-name-processed").html(r.data["user-name-processed"]);
          $("#processing-status").html(r.data["processing-status-text"]);
          switch (r.data["processing-status"]) {
            case 1: $("#processing-status").append(" - <a href=\"#3\">Devis intraitable</a>"); break;
            case 2: $("#processing-status").append(" - <a href=\"#3\">Devis intraitable</a>"); break;
            case 3: $("#processing-status").append(""); break;
          }
        }
      }
    });
  }

  $("#sld_pdt_sheet").draggable().find("img.close").click(function(){ $("#sld_pdt_sheet").hide(); });
  $("#sld_pdt_preview, #sld_pdt_sheet").find("a._blank").click(function(){ window.open(this.href, "_blank"); return false });
  $("#sld_see_pdt_sheet").click(function(){ $("#sld_pdt_sheet").show(); return false; });


// calque RDV

function getRDV(){
  var idCustomer = <?php echo $RdvLeadID ?>;
  $.ajax({
        type: "GET",
        data: "relationId="+idCustomer+"&relationType=client-relance-supplier-lead",
        dataType: "json",
        url: "<?php echo ADMIN_URL ?>ressources/ajax/AJAX_rdv.php",
        success: function(data) {
          if(data.reponse != '' && data.reponse != 'liste vide'){
            var listeRdv = $('#listeRdv');

            var html = '<fieldset class="section-you">\n\
                  <legend>Liste des Rendez-vous</legend>'+
                  "<div class=\"bloc\">"+
                      "<div class=\"bloc-titre2\">Rendez-vous liés à ce client</div>"+
                      "<div id=\"affiche_notes_internes\" class=\"conversation\">"+
                      "<h2>Rappels prévus pour "+data.reponse[0].coordInfo.prenom+" "+data.reponse[0].coordInfo.nom+", Sté "+data.reponse[0].coordInfo.societe+"</h2>"+
                        "<div class=\"zero\"></div>";
            $.each(data.reponse, function(){
                var dateRdv = new Date((this.timestamp_call*1000));
                var loggedUser = <?php echo $user->id ?>;
                var SupprButton = loggedUser == this.operator ? '<img src="../ressources/icons/hexa-no-16x16.png" style="cursor: pointer" alt="Supprimer" class="float-right" onClick="deleteRDV('+this.id+')" />' : '';
                html +=
                          "<ul class=\"list grey\">"+
                              "<li class=\"conversation first\"><pre style=\"white-space : pre-line\">"+
                              "créé par "+this.nom_operator+" prévu le "+(dateRdv.getDate().toString().length == 1 ? '0'+dateRdv.getDate() : dateRdv.getDate())+"/"+((dateRdv.getMonth()+1).toString().length == 1 ? '0'+(dateRdv.getMonth()+1) : (dateRdv.getMonth()+1))+"/"+dateRdv.getFullYear()+" à "+(dateRdv.getHours().toString().length == 1 ? '0'+dateRdv.getHours() : dateRdv.getHours())+":"+(dateRdv.getMinutes().toString().length == 1 ? '0'+dateRdv.getMinutes() : dateRdv.getMinutes())+
                              SupprButton+
                              "</pre></li></ul>";
            });
            html += "</div></div>";
            html += '</fieldset>';
            listeRdv.html(html);
          }else if(data.reponse == 'liste vide'){
              $('#listeRdv').html('');
          }
        }
    });

}

$(document).ready(function() {
  
  $("#rdvLayerButton").on("click", function(){
    $("#rdvDb").data("vars", {
      relationType: "client-relance-supplier-lead",
      relationId: <?php echo $RdvLeadID ?>,
      clientId: "<?php echo $RdvCustomerMail ?>",
      onSuccess: function(){
        getRDV();
      }
    }).dialog("open");
  });
  
  getRDV();
});

function affiche_suite(){
	var precision_hidden = $("#precision_hidden").val();
	if(precision_hidden == 1){
		$("#text_message").show();
		$("#precision_hidden").val(2);
		$("#show_hide_suite").html("Cacher la suite");
	}else {
		$("#text_message").hide();
		$("#precision_hidden").val(1);
		$("#show_hide_suite").html("Afficher la suite");
	}
}

  // RDV Manager
</script>
<?php
} catch (Exception $e) {
?>
<div class="section">
	<a href="leads.php">&lt;&lt; Retourner à la liste des devis fournisseurs</a><br/>
	<br/>
	<div class="block">
    <div class="fatalerror"><?php echo $e->getMessage() ?></div>
  </div>
</div>
<?php
}
require(ADMIN."tail.php");
