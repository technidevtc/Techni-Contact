<?php

/*================================================================/

 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com

 Auteur : Hook Network SARL - http://www.hook-network.com
 Date de cr&eacute;ation : 11/04/2011

 Fichier : /manager/clients/client.php
 Description : &eacute;dition des donn&eacute;es d'un client

/=================================================================*/

if(strcmp(strtoupper(substr(dirname(__FILE__),0,3)),'C:\\')=='0'){
	require_once '../../../../config.php';
}else{
	require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';
}

$handle = DBHandle::get_instance();

$user = new BOUser();

if(!$user->login())
{
  print "CustomerError" . __ERRORID_SEPARATOR__ . "Votre session a expirée, vous devez vous relogger." . __ERROR_SEPARATOR__ . __MAIN_SEPARATOR__;
  exit();
}
if (!$user->get_permissions()->has("m-comm--sm-customers","e")) {
  print "CustomerError" . __ERRORID_SEPARATOR__ . "Vous n'avez pas les droits adéquats pour réaliser cette opération." . __ERROR_SEPARATOR__ . __MAIN_SEPARATOR__;
  exit();
}

//header("Content-Type: text/plain; charset=utf-8");
if (!defined("__JOB_FONCTIONS_LIST__"))
{
      // Préparation liste des fonctions
        $n = $pc = 0; $pl = array(); // Post List
        if ($fh = fopen(MISC_INC."list_post.csv","r")) {
                define("__JOB_FONCTIONS_LIST__", true);
                while (($data = fgetcsv($fh, 128, ";")) !== false) 
                  if(strpos($data[1], '------------') === false)
                    $pl[$n++] = $data[0];
                array_shift($pl); // cette ligne porte simplement l'intitulé «Fonction»
                $pc = $n - 2; // Post Count -> La 1ère ligne est l'intitulé des colonnes la 2ème est «fonction»
                fclose($fh);
        }
}

//require(ADMIN  . 'statut.php');
$clientID = empty ($clientID) && $_GET['customerID'] ? $_GET['customerID'] : '';
if ($clientID != '') {
  if (preg_match('/^\d+$/',$clientID)) {
    $clientInfos = new CustomerUser($handle,$clientID);
    if ($clientInfos === false) {
      $error = true;
      $errorstring .= "- Il n'existe pas de client ayant pour num&eacute;ro identifiant ".$clientID."<br />\n";
      exit;
    } else {
      $page = 'client';
    }
  } else {
    $error = true;
    $errorstring .= "- Le num&eacute;ro d'identifiant client est invalide<br />\n";
    exit;
  }
}else
  exit;

switch ($clientInfos->titre)
{
  case 1  : $titre = 'M.'; break;
  case 2  : $titre = 'Mme'; break;
  case 3  : $titre = 'Mlle'; break;
  default : $titre = 'M.'; break;
}

switch ($clientInfos->titre_l)
{
  case 1  : $titre_l = 'M.'; break;
  case 2  : $titre_l = 'Mme'; break;
  case 3  : $titre_l = 'Mlle'; break;
  default : $titre_l = 'M.'; break;
}

if (isset($_POST['field_account']) && isset($_POST['value_account']))
{
  switch ($_POST['field_account'])
  {
    case 'activated' :
      $clientInfos['actif'] = $_POST['value_account'] != '0' ? 1 : 0;
      $handle->query("update clients set actif = {$clientInfos['actif']} where id = $clientID", __FILE__, __LINE__);
      break;
  }
}
 
$trans = array('"' => '\"', "\r" => '\r', "\n" => '\n');
$CustomerInfos = array(
  'titre' => $clientInfos->titre,
  'nom' => $clientInfos->nom,
  'prenom' => $clientInfos->prenom,
  'fonction' => $clientInfos->fonction,
  'tel1' => $clientInfos->tel1,
  'fax1' => $clientInfos->fax1,
  'tel2' => $clientInfos->tel2,
  'email' => $clientInfos->email,
  'website_origin' => $clientInfos->website_origin,
  'service' => $data_service->fonction_service
);
$CompanyInfos = array(
  'societe' => $clientInfos->societe,
  'nb_salarie' => $clientInfos->nb_salarie,
  'secteur_activite' => $clientInfos->secteur_activite,
  'secteur_qualifie' => $clientInfos->secteur_qualifie,
  'code_naf' => $clientInfos->code_naf,
  'num_siret' => $clientInfos->num_siret,
  'tva_intra' => $clientInfos->tva_intra
);
$BillingAddress = array(
  'adresse' => $clientInfos->adresse,
  'complement' => $clientInfos->complement,
  'cp' => $clientInfos->cp,
  'ville' => $clientInfos->ville,
  'pays' => $clientInfos->pays
);
$ShippingAddress = array(
  'coord_livraison' => $clientInfos->coord_livraison,
  'titre_l' => $clientInfos->titre_l,
  'nom_l' => $clientInfos->nom_l,
  'prenom_l' => $clientInfos->prenom_l,
  'societe_l' => $clientInfos->societe_l,
  'adresse_l' => $clientInfos->adresse_l,
  'complement_l' => $clientInfos->complement_l,
  'cp_l' => $clientInfos->cp_l,
  'ville_l' => $clientInfos->ville_l,
  'pays_l' => $clientInfos->pays_l
);
require 'CustomerInfosAW.php';

if (!preg_match('/^(http|https|ftp):\/\//', $clientInfos->url))
  $clientInfos->url = 'http://' . $clientInfos->url;
?>
<br />
<br />
<script type="text/javascript">
var __CUSTOMER_ID__ = <?php echo $clientID ?>,
    CustomerInfos = <?php echo json_encode($CustomerInfos) ?>,
    CompanyInfos = <?php echo json_encode($CompanyInfos) ?>,
    BillingAddress  = <?php echo json_encode($BillingAddress) ?>,
    ShippingAddress = <?php echo json_encode($ShippingAddress) ?>;


function updateWebsiteLogo(website_origin) {
  $("#logo-website").html(website_origin == "<?php echo $website_origin_list[WEBSITE_ORIGIN_TC] ?>" ? "" : "<img src=\"../ressources/images/logo-website-"+website_origin.toLowerCase()+".jpg\" alt=\"\" />");
  
}

//updateWebsiteLogo("<?php echo $website_origin_list[$CustomerInfos['website_origin']] ?>");

</script>

<div class="titreStandard">Informations g&eacute;n&eacute;rales sur le client n&deg;<?php echo $clientID ?> 
<?php if ($user->get_permissions()->has("m-comm--sm-customers","red")) { ?>	
	| <a href="<?php echo ADMIN_URL ?>clients/del.php?id=<?php echo $clientID ?>" onclick="return confirm('Supprimer d&eacute;finitivement ce client ?')">Supprimer d&eacute;finitivement ce client</a>
<?php  } ?>
</div>
<br />
<div class="bg">
  <form id="ChangeAccount" name="ChangeAccount" method="post" action="index.php?id=<?php echo $clientID . '&' . session_name() . '=' . session_id() ?>">
    <input type="hidden" name="field_account" />
    <input type="hidden" name="value_account" />
  </form>
        <div id="PerfReqCW" class="PerfReqLabel">Modification en cours...</div>
                <div class="zero"></div>
  <div id="recap">


                <div class="InfosBlocR">
      <div class="titreBloc">Informations livraison</div>
      <div class="coordCustomer">
        <table cellspacing="0" cellpadding="0">
          <tr><td class="iic">Soci&eacute;t&eacute; :<td id="societe_l_label"><?php echo $clientInfos->societe_l ?></td></tr>
          <tr><td class="iic">Titre :<td id="titre_l_label"><?php echo $titre_l ?></td></tr>
          <tr><td class="iic">Pr&eacute;nom Nom :</td><td><span  id="prenom_l_label"><?php echo $clientInfos->prenom_l ?></span> <span id="nom_l_label"><?php echo $clientInfos->nom_l ?></span></td></tr>
          <tr><td class="iic">Email :<td id="email_l_label"><?php echo $clientInfos->email ?></td></tr>
          <tr><td class="iic">T&eacute;l&eacute;phone 2 :</td><td id="tel2_label" ><?php if($clientInfos->tel2){ ?><a href="tel:<?php echo $clientInfos->tel2 ?>"><?php } ?><?php echo $clientInfos->tel2 ?> <?php if($clientInfos->tel2){ ?><img src="../ressources/icons/telephone.png" alt="Tel:" /></a><?php } ?></td></tr>
          <tr><td class="iic">Soci&eacute;t&eacute; :</td><td id="societe_l_label"><?php echo $clientInfos->societe_l ?></td></tr>
          <tr><td class="iic">Adresse :</td><td id="adresse_l_label"><?php echo $clientInfos->adresse_l ?></td></tr>
          <tr><td class="iic">Compl&eacute;ment :</td><td id="complement_l_label"><?php echo $clientInfos->complement_l ?></td></tr>
          <tr><td class="iic">Code Postal :</td><td id="cp_l_label"><?php echo $clientInfos->cp_l ?></td></tr>
          <tr><td class="iic">Ville :</td><td id="ville_l_label"><?php echo $clientInfos->ville_l ?></td></tr>
          <tr><td class="iic">Pays :</td><td id="pays_l_label"><?php echo $clientInfos->pays_l ?></td></tr>
          <tr><td class="iic">Commentaire de livraison :</td><td id="comment_l_label"><?php echo $clientInfos->infos_sup_l ?></td></tr>
        </table>
        <div class="adr_modif"><a href="javascript:ShowCustomerInfosAW();" title="Cliquer ici pour modifier">Modifier</a></div>
        <div class="zero"></div>
      </div>
    </div>
	
	
    <div class="InfosBloc">
      <div class="titreBloc">Informations Personnelles du client</div>
      <div class="coordCustomer">
        <table cellspacing="0" cellpadding="0">
          <tr><td class="iic">Soci&eacute;t&eacute; :<td id="societe_label"><?php echo $clientInfos->societe ?></td></tr>
          <tr><td class="iic">Titre :<td id="titre_label"><?php echo $titre ?></td></tr>
          <tr><td class="iic">Pr&eacute;nom Nom :<td><span id="prenom_label"><?php echo $clientInfos->prenom ?></span> <span id="nom_label"><?php echo $clientInfos->nom ?></span></td></tr>
          <tr><td class="iic">Email :</td><td id="email_label"><?php echo $clientInfos->email ?></td></tr>
          <tr><td class="iic">Fonction :</td><td id="fonction_label"><?php echo $clientInfos->fonction ?></td></tr>
		  
		  <?php  
			$sql_service = "SELECT fonction_service FROM clients WHERE iD='$clientID' ";
			$req_service = mysql_query($sql_service);
			$data_service= mysql_fetch_object($req_service);
			//if(!empty($data_service->fonction_service)){	
		  ?>
		  <tr><td class="iic">Service :</td><td id="service_label"><?= $data_service->fonction_service ?></td></tr>
			<?php // } ?>
          <tr><td class="iic">T&eacute;l&eacute;phone :</td><td id="tel1_label" ><?php if($clientInfos->tel1){ ?><a href="tel:<?php echo $clientInfos->tel1 ?>"><?php } ?><?php echo $clientInfos->tel1 ?> <?php if($clientInfos->tel1){ ?><img src="../ressources/icons/telephone.png" alt="Tel:" /></a><?php } ?></td></tr>
          <tr><td class="iic">Fax :</td><td id="fax1_label"><?php echo $clientInfos->fax1 ?></td></tr>
          <tr><td class="iic">Fax 2 :</td><td id="fax2_label"><?php echo $clientInfos->fax2 ?></td></tr>
          <tr><td class="iic">Adresse 1 :</td><td id="adresse_label"><?php echo $clientInfos->adresse ?></td></tr>
          <tr><td class="iic">Adresse 2 :</td><td id="complement_label"><?php echo $clientInfos->complement ?></td></tr>
          <tr><td class="iic">Code Postal :</td><td id="cp_label"><?php echo $clientInfos->cp ?></td></tr>
          <tr><td class="iic">Ville :</td><td id="ville_label"><?php echo $clientInfos->ville ?></td></tr>
          <tr><td class="iic">Pays :</td><td id="pays_label"><?php echo $clientInfos->pays ?></td></tr>
        </table>
        <div class="adr_modif"><a href="javascript:ShowCustomerInfosAW();" title="Cliquer ici pour modifier">Modifier</a></div>
        <div class="zero"></div>
      </div>
    </div>
    <div class="zero"></div>
    

  </div>
    <div id="InfosList">
      <input type="hidden" id="LoginValue" value="<?php echo $clientInfos->login ?>" />
      <input type="hidden" id="CodeValue" value="<?php echo $clientInfos->code ?>" />
      <div class="infos">
        <div style="width: 100px; float: left">Login :</div>
        <div style="width: 100px; float: left "><a id="LoginMod" href="javascript: editLogin();">Modifier</a></div>
        <div class="valeur" id="Login"><?php echo $clientInfos->login ?></div>
      </div>
      <div class="infos">
        <div style="width: 100px; float: left">Code Client :</div>
        <div style="width: 100px; float: left "><a id="CodeMod" href="javascript: editCode();">Modifier</a></div>
        <div class="valeur" id="Code"><?php echo $clientInfos->code ?></div>
      </div>
      <div class="infos">
        <div class="intitule">Date de cr&eacute;ation du compte :</div>
        <div class="valeur">le <?php echo date("d/m/Y &#224; H:i.s", $clientInfos->timestamp) ?></div>
      </div>
      <div class="infos">
        <div class="intitule">Derni&egrave;re mise &agrave jour du compte :</div>
        <div class="valeur" id="Timestamp">le <?php echo date("d/m/Y &#224; H:i.s", $clientInfos->last_update) ?></div>
      </div>
      <div class="infos">
        <div class="intitule">Ce client est : <i id="ActiveState"><?php echo $clientInfos->actif ? '<b style="color: #00D000">actif</b>' : '<b style="color: #D00000">non actif</b>' ?></i></div>
        <div class="valeur"><a href="Javascript:ToggleActiveState();" id="ActiveStateMod"><?php echo $clientInfos->actif ? 'D&eacute;sactiver' : 'Activer' ?> le compte de ce client</a></div>
      </div>
      <div class="infos">
        <div class="intitule">Site d'origine :</div>
        <div class="valeur" id="website_origin_label"><?php echo $website_origin_list[$clientInfos->website_origin] ?></div>
      </div>
      <button id="reset_pwd" onClick="if(confirm('&Ecirc;tes-vous s&ucirc;r de vouloir r&eacute;initialiser les codes l\'acc&egrave;s de ce client?')){reset_password();}">Renvoyer codes d'acc&egrave;s</button>
      <div class="zero"></div>
    </div>
    <div class="InfosBlocR marginTop16">
      <div class="titreBloc">Informations sur la Soci&eacute;t&eacute;</div>
      <div class="coordCustomer">
        <table cellspacing="0" cellpadding="0">
          <tr><td class="iic">fonction :</td><td id="fonction_label"><?php echo $clientInfos->fonction ?></td></tr>
          <tr><td class="iic">Taille salariale :</td><td id="nb_salarie_label"><?php echo $clientInfos->nb_salarie ?></td></tr>
          <tr><td class="iic">Secteur d'activit&eacute; :</td><td id="secteur_activite_label"><?php echo $clientInfos->secteur_activite ?></td></tr>
          <tr><td class="iic">Qualif. Sect. acti. :</td><td id="secteur_qualifie_label"><?php echo $clientInfos->secteur_qualifie ?></td></tr>
          <tr><td class="iic">Code NAF :</td><td id="code_naf_label"><?php echo $clientInfos->code_naf ?></td></tr>
          <tr><td class="iic">Num&eacute;ro SIRET :</td><td id="num_siret_label"><?php echo $clientInfos->num_siret ?></td></tr>
          <tr><td class="iic">TVA Intra :</td><td id="tva_intra_label"><?php echo $clientInfos->tva_intra ?></td></tr>
          <tr><td class="iic">url :</td><td id="url_label"><?php echo $clientInfos->url ?></td></tr>
        </table>
        <div class="adr_modif"><a href="javascript:ShowCustomerInfosAW();" title="Cliquer ici pour modifier">Modifier</a></div>
        <div class="zero"></div>
      </div>
    </div>
    <div class="zero"></div>
    <div class="InfosBloc widerBloc">
      <div class="titreBloc">Contacts secondaires</div>
      <div class="coordCustomer">
        <div class="actions"><div class="cancel icon page-white-delete"></div><div class="edit icon page-white-edit"></div></div>
        <div id="showAddContactForm" class="fr ui-icon ui-icon-circle-plus"></div>
        <div id="hideAddContactForm" class="fr ui-icon ui-icon-circle-minus"></div>
        <div class="zero"></div>
        <div id="blocContactsSecondairesInfos"></div>
        <div id="blocContactsSecondairesEdit"></div>
        <div class="blocFormContactsSecondaires block">
          <div class="title">Ajouter un contact :</div>
          <table cellspacing="0" cellpadding="0">
            <tr>
              <td>
                <div><label>Nom :</label> <input id="new-contact-nom" name="nom" class="c_i" type="text" value="" /></div>
                <div><label>Prénom :</label> <input id="new-contact-prenom" name="prenom" class="c_i" type="text" value="" /></div>
                <div><label>Email :</label> <input id="new-contact-email" name="email" class="c_i" type="text" value="" /></div>
                <div><label>Tél :</label> <input id="new-contact-tel" name="tel1" class="c_i" type="text" value="" /></div>
                <div><label>Tél 2 :</label> <input id="new-contact-tel2" name="tel2" class="c_i" type="text" value="" /></div>
                <div><label>Fax :</label> <input id="new-contact-fax" name="fax1" class="c_i" type="text" value="" /></div>
                <div><label>Fax 2 :</label> <input id="new-contact-fax2" name="fax2" class="c_i" type="text" value="" /></div>
                <!--<div><label>Fonction :</label> <input id="new-contact-fonction" name="fonction" class="c_i" type="text" value="" /></div>-->
                <div><label>Fonction :</label> <select id="new-contact-fonction" class="c_i" name="fonction">
               <?php
               foreach($pl as $poste)
              echo '<option  value="'.$poste.'">'.$poste.'</option>';
               ?>
                  </select>
                </div>
              </td>
            </tr>

          </table><button type="button" class="btn ui-state-default ui-corner-all">Ajouter le contact</button></div>
        </div>
        </div>
      </div>
    <div id="InfosError" class="fr"></div>
    <div class="zero"></div>

<!--
                <br />
    <div class="InfosBlocR">
      <div class="titreBloc">Coordonn&eacute;es (livraison)</div>
      <div class="coordCustomer">
        <table cellspacing="0" cellpadding="0">
          <tr><td class="iic">Titre :<td id="titre_l_label"><?php echo $titre_l ?></td></tr>
          <tr><td class="iic">Nom :</td><td id="nom_l_label"><?php echo $clientInfos->nom_l ?></td></tr>
          <tr><td class="iic">Pr&eacute;nom :<td id="prenom_l_label"><?php echo $clientInfos->prenom_l ?></td></tr>
          <tr><td class="iic">Soci&eacute;t&eacute; :</td><td id="societe_l_label"><?php echo $clientInfos->societe_l ?></td></tr>
          <tr><td class="iic">Adresse :</td><td id="adresse_l_label"><?php echo $clientInfos->adresse_l ?></td></tr>
          <tr><td class="iic">Compl&eacute;ment :</td><td id="complement_l_label"><?php echo $clientInfos->complement_l ?></td></tr>
          <tr><td class="iic">Code Postal :</td><td id="cp_l_label"><?php echo $clientInfos->cp_l ?></td></tr>
          <tr><td class="iic">Ville :</td><td id="ville_l_label"><?php echo $clientInfos->ville_l ?></td></tr>
          <tr><td class="iic">Pays :</td><td id="pays_l_label"><?php echo $clientInfos->pays_l ?></td></tr>
        </table>
        <div class="adr_modif"><a href="javascript: ShowCustomerInfosAW()" title="Cliquer ici pour modifier">Modifier</a></div>
      </div>
    </div>
    <div class="InfosBloc">
      <div class="titreBloc">Coordonn&eacute;es (facturation)</div>
      <div class="coordCustomer">
        <table cellspacing="0" cellpadding="0">
          <tr><td class="iic">Adresse :</td><td id="adresse_label"><?php echo $clientInfos->adresse ?></td></tr>
          <tr><td class="iic">Compl&eacute;ment :<td id="complement_label"><?php echo $clientInfos->complement ?></td></tr>
          <tr><td class="iic">Code Postal :</td><td id="cp_label"><?php echo $clientInfos->cp ?></td></tr>
          <tr><td class="iic">Ville :</td><td id="ville_label"><?php echo $clientInfos->ville ?></td></tr>
          <tr><td class="iic">Pays :</td><td id="pays_label"><?php echo $clientInfos->pays ?></td></tr>
        </table>
        <div class="adr_modif"><a href="javascript: ShowCustomerInfosAW()" title="Cliquer ici pour modifier">Modifier</a></div>
      </div>
    </div>
    <div class="zero"></div>-->
</div>

<script type="text/javascript">
function reset_password(){
  $.ajax({
    type: "POST",
    data: 'idClient='+<?php echo $clientInfos->id ?>+'&action=rst_pwd',
    dataType: "json",
    url: 'AJAX_password-resend.php',
    success: function(data) {
      if(data.reponse){
        $('#reset_pwd').after('<span style="color:green">'+data.reponse+'</span>');
        $('#reset_pwd').remove();
      }else if(data.error){
        $('#reset_pwd').after('<span style="color:red">'+data.error+'</span>');
      }
    }
  });
}

 $(document).ready(function() {
	$.ajax({
		url: 'AJAX_fiche_client.php?client_id='+<?php echo $clientInfos->id ?>,
		type: 'GET',
		success:function(data){
			$("#fiche_utilisateurs").html(data);
		}
		});
  });
  
   $(document).ready(function() {
	   var client_id  = "<?= $clientInfos->id ?>";
	   var email      = "<?= $clientInfos->email ?>";
	$.ajax({
		url: 'AJAX_produit_client.php?client_id='+client_id+'&email='+email,
		type: 'GET',
		success:function(data){
			$("#produit_clients").html(data);
		}
		});
  });
</script>
<style>
.comment_poste li {
    float: left;
    margin-right: 15px;
}
</style>

