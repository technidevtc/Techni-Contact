<?php
/* ================================================================/

  Techni-Contact V3 - MD2I SAS
  http://www.techni-contact.com

  Auteur : Hook Network SARL - http://www.hook-network.com
  Date de création : 2 avril 2006

  Mises à jour : update OD 07/04/2011

  Fichier : /secure/manager/clients/index.php
  Fichier : /secure/manager/clients/index.php
  Description : Accueil gestion clients

  /================================================================= */

require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

require(ADMIN."logs.php");

$handle = DBHandle::get_instance();
$user = new BOUser();

if(!$user->login())
{
	header("Location: ".ADMIN_URL."login.html");
	exit();
}
if (!$user->get_permissions()->has("m-comm--sm-customers","e")) {
  print "CustomerError" . __ERRORID_SEPARATOR__ . "Vous n'avez pas les droits adéquats pour réaliser cette opération." . __ERROR_SEPARATOR__ . __MAIN_SEPARATOR__;
  exit();
}


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
// passage de la liste des fonctions au js
echo '<script text="javascript">
var customerfunctionList = new Array(';
foreach($pl as $key => $post)
  echo ($key != 0 ? ',' : '').'"'.$post.'"';
echo ');';
echo '</script>';

require(ADMIN.'customers.php');
require(SITE.'commandes.php');
require(SITE.'devis.php');
require(ICLASS.'Command.php');
require(ICLASS.'CustomerDevis.php');
require(ADMIN.'tva.php');

require(DOCTRINE_MODEL_PATH.'Estimate.php');
require(DOCTRINE_MODEL_PATH.'Order.php');
require(DOCTRINE_MODEL_PATH.'Invoice.php');
//require(DOCTRINE_MODEL_PATH.'ClientsContacts.php');

$title = $navBar = 'Gestion des fiches clients';
require(ADMIN.'head.php');

$clientId = filter_input(INPUT_GET, 'idClient', FILTER_VALIDATE_INT);
if (!$clientId)
  $clientEmail = filter_input(INPUT_GET, 'idClient', FILTER_VALIDATE_EMAIL);
if (!$clientEmail)
  $clientEmail = filter_input(INPUT_GET, 'mailClient', FILTER_VALIDATE_EMAIL);
$input = $clientId ? $clientId : $clientEmail;

?>
<link rel="stylesheet" type="text/css" href="clients.css" />
<link href="<?php echo ADMIN_URL ?>css/ui/ui.datepicker.css" rel="stylesheet" title="style" media="all" />
<script src="Customer.js" type="text/javascript"></script>
<script src="Customers.js" type="text/javascript"></script>
<script src="../js/ManagerFunctions.js" type="text/javascript"></script>
<script type="text/javascript" src="../contacts/leads.js"></script>
<script type="text/javascript">
var RevDate = new Date();
//document.write( unescape( "%3Cscript src='" + (("https:" == document.location.protocol) ? "https://api2.reversoform.com/includes/js/reversoObj.js" : "http://api.reversoform.com/includes/js/reversoObj.js") + "?t="+RevDate.getTime()+"' type='text/javascript'%3E%3C/script%3E" ) );
</script>
<script type="text/javascript">
var reversoLoaded = window.ClassReverso !== undefined;
// Reverso
if (reversoLoaded) {
  var ObjReverso = new ClassReverso(); // var name is hard used in the vendor js ...
  ObjReverso.serial    = '8389615817859'; // needed to have the vendor js init
  ObjReverso.phone     = 'tel1C';
  
  $(document).on("keyup", "input.reverso-enabled", function(){
    var tel = $(this).val().match(/\d+/g).join("");
    if (tel.length >=10 && reversoLoaded) {
      ObjReverso.alreadyReversed = false; // to avoid the cache
      if (this.id == "tel1C") {
        ObjReverso.phone     = 'tel1C';
        ObjReverso.company   = 'societeC';
        ObjReverso.firstname = 'prenomC';
        ObjReverso.lastname  = 'nomC';
        ObjReverso.address   = 'adresseC';
        ObjReverso.zip       = 'cpC';
        ObjReverso.city      = 'villeC';
        ObjReverso.country   = 'paysC';
        ObjReverso.naf       = 'code_nafC';
        ObjReverso.siret     = 'num_siretC';
      } else if (this.id == "tel1") {
        ObjReverso.phone     = 'tel1';
        ObjReverso.company   = 'societe';
        ObjReverso.firstname = 'prenom';
        ObjReverso.lastname  = 'nom';
        ObjReverso.address   = 'adresse';
        ObjReverso.zip       = 'cp';
        ObjReverso.city      = 'ville';
        ObjReverso.country   = 'pays';
        ObjReverso.naf       = 'code_naf';
        ObjReverso.siret     = 'num_siret';
      }
      
      ObjReverso.reverso(tel);
    }
  });
}
</script>
<div class="titreStandard">Liste clients</div>
<?php
require('CustomerCW.php');
?>
<br />
<div class="bg">
  <div id="RechercheClient" class="float-left">
    <div style="width: 702px">
<!--      <input type="hidden" name="searchType" />-->
      <div class="caption">Rechercher un client (mail, société, ID client, ID commande, ID lead)</div>
      <div id="colg"  style="width: 702px">
        <table cellspacing="0" cellpadding="0">
          <tr>
            <td style="width: 150px">
			<?php
				if(!empty($_GET['email'])){ ?>
					<input id="checkTerm" type="text" name="queryTerm" value="<?php echo $_GET['email'] ?>" />
			<?php
			}else {
			?>
			<input id="checkTerm" type="text" name="queryTerm" value="<?php echo $input ?>" />
				<?php } ?>
			</td>
            <td style="width: 250px">
			<input id="checkTelephone" type="checkbox" name="checkTelephone"  value="checkTelephone" onChange="checkCustomer()" /> Recherche téléphone/fax</td>
            <td style="width: 120px"><input type="button" class="button" value="Créer un nouveau client" onclick="ShowCustomerCW()"></td>
          </tr>
        </table>
        <input type="hidden" id="requestedCustomerId" value="" />
        <input type="hidden" id="requestedCustomerMail" value="" />
      </div>
      <div class="zero"></div>
    </div>
	
  </div>
  <div class="float-right">
    <div id="rdvLayerButtonContainer"><span id="errorRdv" style="color: red"></span>
	<button class="ui-state-default ui-corner-all" id="rdvLayerButton">Prévoir un RDV téléphonique</button>
	</div>
	
    <?php 
		$sql_logo  = "SELECT website_origin FROM clients WHERE login='".$_GET['email']."' ";
		$req_logo  =  mysql_query($sql_logo);
		$data_logo = mysql_fetch_object($req_logo);
		
		$sql_logo_id  = "SELECT website_origin FROM clients WHERE id='".$clientId."' ";
		$req_logo_id  =  mysql_query($sql_logo_id);
		$data_logo_id = mysql_fetch_object($req_logo_id);
		
		if(($data_logo->website_origin =="MOB") || ($data_logo_id->website_origin =="MOB") ){
			$img_logo = "<img src='../ressources/images/logo-website-mobaneo.jpg' />";			
		}
		
		if(($data_logo->website_origin =="MER") || ($data_logo_id->website_origin =="MER")){
			$img_logo = "<img src='../ressources/images/logo-website-mercateo.jpg' />";			
		}
		
		
		
	?>
    <div id="logo-send"><?= $img_logo ?></div>
  </div> 
  <div class="zero"></div>

  <div id="listeRdv"></div>
  <div id="showCustomerList"></div>
  <div id="showCustomerBlocks"></div>
  <div id="client-recommended-products" class="recommended-products">
    <br/>
    <div class="bg">
      <div class="block">
        <div class="title">Produits suggérés</div>
      </div>
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
  </div>
  <div id="showOrderList">
    <br/>
    <div class="bg">
      <div class="block">
        <div class="title">Historique commandes</div>
      </div>
      <button type="button" class="btn ui-state-default ui-corner-all fr">Nouvelle commande</button>
      <div class="zero"></div>
      <div id="client-order-list"></div>
      <div class="zero"></div>
    </div>
  </div>
  <div id="showLeadsList"></div>
  <div id="showEstimateList">
    <br/>
    <div class="bg">
      <div class="block">
        <div class="title">Historique devis</div>
      </div>
      <button type="button" class="btn ui-state-default ui-corner-all fr">Nouveau devis</button>
      <button type="button" class="btn ui-state-default ui-corner-all fr">Nouveau devis Ad Hoc</button>
      <div class="zero"></div>
      <div id="client-estimate-list"></div>
      <div class="zero"></div>
    </div>
  </div>
  <div id="showInvoiceList">
    <br/>
    <div class="bg">
      <div class="block">
        <div class="title">Historique Factures & Avoirs</div>
      </div>
      <button type="button" class="btn ui-state-default ui-corner-all fr">Nouvelle facture</button>
      <div class="zero"></div>
      <div id="client-invoice-list"></div>
      <div class="zero"></div>
    </div>
	
  </div>
  
  <div id="showEstimatesList"></div>
  <div id="showSavedProductsList"></div>
  <div id="showInternalNotes"></div>
  
 
  <div id="fiche_utilisateurs"></div>
  <div id="produit_clients"></div>
  <?php
	if(isset($_GET['params'])){
		if( $_GET['params'] == 'display_bars'){ 
			$sql_updsate = "UPDATE call_spool_vpc SET `ligne_active`='1',call_operator='".$_SESSION["id"]."'
							WHERE id='".$_GET['idCall']."' ";
			mysql_query($sql_updsate);
			
			$sql_compagne  = "SELECT campaign_name FROM call_spool_vpc WHERE id='".$_GET['idCall']."' ";
			$req_compagne  = mysql_query($sql_compagne);
			$data_compagne = mysql_fetch_object($req_compagne);		
		?>
		<div id="bottomBar">
			<div style="visibility: visible; margin-top: -50px;" id="callBar"></div>
		<?php if($_GET['type'] != 'rdv'){ ?>
			<div style="visibility : visible; width: 600px;" id="inCallbar">
		<?php }else{	?>
			<div id="inCallbar" style="visibility: visible; width: 470px;">
		<?php } ?>
		<div>
		<div class="name_campgne"><?= $data_compagne->campaign_name ?></div>
		<div>
		<a class="btn ui-state-default ui-corner-all" onclick="setCallOkVPC(<?= $_GET['idCall'] ?>)" href="#"> Client joint sans action</a>
		<a class="btn ui-statelike-choice-no ui-corner-all" onclick="setCallNokVPC(<?= $_GET['idCall'] ?>)" href="#"> Appel en absence</a>
		<?php if($_GET['type'] != 'rdv'){ ?>
		<a class="btn ui-statelike-choice-no ui-corner-all" onclick="setCallOkNoLeadVPC(<?= $_GET['idCall'] ?>)" href="#">Action sur client</a>
		<?php }	?>
		</div>
		</div>
			</div>
		</div>
	<?php 
			
		}
	}
  ?>  
  


<script type="text/javascript">
  var __SID__ = '<?php echo $sid  ?>';
  var __ADMIN_URL__ = '<?php echo ADMIN_URL  ?>';
  var __MAIN_SEPARATOR__ = '<?php echo __MAIN_SEPARATOR__  ?>';
  var __ERROR_SEPARATOR__ = '<?php echo __ERROR_SEPARATOR__  ?>';
  var __ERRORID_SEPARATOR__ = '<?php echo __ERRORID_SEPARATOR__  ?>';
  var __OUTPUT_SEPARATOR__ = '<?php echo __OUTPUT_SEPARATOR__  ?>';
  var __OUTPUTID_SEPARATOR__ = '<?php echo __OUTPUTID_SEPARATOR__  ?>';
  var __DATA_SEPARATOR__ = '<?php echo __DATA_SEPARATOR__  ?>';

  function findCustomer(by_type) {
    document.RechercheClient.searchType.value = by_type;
    document.RechercheClient.submit();
  }

  function checkEnter(by_type, e) {
    // supporté par ie et firefox, le plus important
    if (e.keyCode == 13) {
      document.RechercheClient.searchType.value = by_type;
      document.RechercheClient.submit();
      return false;
    }
    return true;
  }

  $(document).ready(function() {
	if ($('#checkTerm').val() != '') {
      checkCustomer();
    }
  });
  
  $("#checkTerm").bind("keypress", function(e) {
    if (e.keyCode == 13) {
      checkCustomer();
    }
  });


  function getRDV(){
    var idCustomer = "<?php echo $clientId ?>"|0 || $("#requestedCustomerId").val();
    $.ajax({
      type: "GET",
      data: "relationId="+idCustomer+"&relationType=client-prospect",
      dataType: "json",
      url: "<?php echo ADMIN_URL ?>ressources/ajax/AJAX_rdv.php",
      success: function(data) {
        if (data.reponse != "" && data.reponse != "liste vide") {

          var html =
            "<fieldset class=\"section-you\">"+
              "<legend>Liste des Rendez-vous</legend>"+
              "<div class=\"bloc\">"+
                "<div class=\"bloc-titre2\">Rendez-vous liés à ce client</div>"+
                "<div id=\"affiche_notes_internes\" class=\"conversation\">"+
                  "<h2>Rappels prévus pour "+data.reponse[0].coordInfo.prenom+" "+data.reponse[0].coordInfo.nom+", Sté "+data.reponse[0].coordInfo.societe+"</h2>"+
                  "<div class=\"zero\"></div>";
          
          $.each(data.reponse, function(){
              var SupprButton = <?php echo $user->id ?> == this.operator ? "<img src=\"../ressources/icons/hexa-no-16x16.png\" style=\"cursor: pointer\" alt=\"Supprimer\" class=\"float-right\" />" : "";
              html +=
                  "<ul class=\"list grey\" data-id=\""+this.id+"\">"+
                    "<li class=\"conversation first\"><pre style=\"white-space : pre-line\">"+
                    "créé par "+this.nom_operator+" prévu le "+HN.TC.get_formated_datetime(this.timestamp_call, " à ")+
                    SupprButton+
                    "</pre></li>"+
                  "</ul>";
          });
          html +=
                "</div>"+
              "</div>"+
            "</fieldset>";
          
          $("#listeRdv").html(html);
        
        } else if (data.reponse == "liste vide") {
          $("#listeRdv").html("");
        }
      }
    });
  }

  $(document).ready(function() {    
    $("#rdvLayerButton").on("click", function(){
      var clientId = $("#requestedCustomerId").val(),
          clientEmail = $("#requestedCustomerMail").val();
      $("#rdvDb").data("vars", {
        relationType: "client-prospect",
        relationId: clientId,
        clientId: clientEmail,
        onSuccess: function(){
          getRDV();
          showInternalNotes(clientId);
        }
      }).dialog("open");
    });
    
    $("#listeRdv").on("click", "ul.list img[alt='Supprimer']", function(){
      var rdvId = $(this).closest("ul.list").data("id");
      deleteRDV(rdvId).done(function(){
        getRDV();
      });
    });
    
    getRDV();
  });

</script>
<link rel="stylesheet" type="text/css" href="<?php echo ADMIN_URL ?>ressources/css/item-list.css" />
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
	
	function setCallOkNoLeadVPC(id_pille){
		var joinabilite = "<?= $_GET['joinabilite'] ?>";
		var appel 		= "<?= $_GET['appel'] ?>";
		var users 		= "<?= $_GET['users'] ?>";
		$.ajax({
				url: '../pile_appels_commerciaux/AJAX_vpc/AJAX_action_vpc.php?id_ligne='+id_pille+'&action=action_sur_client ',
				type: 'GET',
				success:function(data){
					location.href='<?php echo ADMIN_URL ?>pile_appels_commerciaux/pile_appels_VPC.php?joinabilite='+joinabilite+'&appel='+appel+'&users='+users;
				}
		});
	}
  // recommended products action buttons
  $("#client-recommended-products").on("click", ".actions .icon", function(){
    var $this = $(this),
        pdt_id = $this.closest(".entry").data("id")|0;
    if ($this.hasClass("page-white-add")) {
      document.location.href = HN.TC.ADMIN_URL+"contacts/lead-create.php?pdtId="+pdt_id+"&idClient="+client_email+"&idCampaign=999996";
    } else if ($this.hasClass("basket-put")) {
      document.location.href = HN.TC.ADMIN_URL+"estimates/estimate-detail.php?id=new&client_id"+client_id+"&product_id="+pdt_id;
    } else if ($this.hasClass("cart-put")) {
      document.location.href = HN.TC.ADMIN_URL+"orders/order-detail.php?id=new&client_id="+client_id+"&product_id="+pdt_id;
    }
  });
  
  order_conversation = new HN.TC.Messenger({
    id_sender: <?php echo $user->id ?>,
    type_sender: HN.TC.__MSGR_USR_TYPE_BOU__,
    context: HN.TC.__MSGR_CTXT_CUSTOMER_TC_CMD__
  });
  ol = new HN.TC.ItemList({
    domHandle: "#client-order-list",
    columns: [
      { name: "tool", label: "Voir", type: "misc",
        onCellWrite: function(rowData, colName){
          return "<a href=\""+HN.TC.ADMIN_URL+"orders/order-detail.php?id="+rowData.id+"\" class=\"icon application-double\"></a>"+
                 " <span data-bhv=\"show_conv\" class=\"ui-icon ui-icon-circle-arrow-n\" title=\"Voir les conversations\"></span>";
        },
        onCellEvent: {
          click: function(rowData, col, e){
            e.stopPropagation();
            e.preventDefault();
            var $target = $(e.target),
                bhv = $target.data("bhv"),
                $tr = $(this).closest("tr"),
                $trn = $tr.next();
            if (e.target !== this && $target.parent().get(0) === this && $(e.target).index() == 0) {
              open($target.attr("href"), "_blank");
              return false;
            }
            switch (bhv) {
              case "show_conv":
                $target.data("bhv","hide_conv")
                  .removeClass("ui-icon-circle-arrow-n")
                  .addClass("ui-icon-circle-arrow-s");
                if ($trn.hasClass("conv")) {
                  $trn.show();
                }
                else {
                  order_conversation.setReferenceTo(rowData.id).getConversation(function(data){
                    if (!data.length) {
                      $target
                        .removeData("bhv")
                        .removeAttr("data-bhv")
                        .removeClass("ui-icon-circle-arrow-s")
                        .addClass("ui-icon-circle-arrow-n disabled")
                        .attr("title", "Aucune conversation en cours");
                    }
                    else {
                      var $trn = $("<tr>", { "class": "conv" }).insertAfter($tr),
                          $td = $("<td>", { "class": "block", "colspan": col.parent.cols.length }).appendTo($trn),
                          $ul = $("<ul>", { "class": "messages" }).appendTo($td);
                      for (var i=0; i<data.length; i++) {
                        var post = data[i];
                        $("<li>")
                          .append($("<div>", { "class": "header", html: "Message de "+post.sender_name+" à "+post.recipient_name+" envoyé le "+post.timestamp }))
                          .append($("<div>", { "class": "content", html: post.text }))
                          .appendTo($ul);
                      }
                    }
                  });
                }
                break;
              case "hide_conv":
                $target.data("bhv","show_conv")
                  .removeClass("ui-icon-circle-arrow-s")
                  .addClass("ui-icon-circle-arrow-n");
                if ($trn.hasClass("conv"))
                  $trn.hide();
                break;
            }
          }
        }
      },
      { name: "type", label: "Type Commande", type: "const", filters: ["="], constStrings: HN.TC.Order.typeList },
      { name: "campaign_id", label: "ID Campagne", type: "int", filters: ["=","between"], constStrings: HN.TC.Order.estimateSourceList },
      { name: "id", label: "réf.", type: "int", filters: ["=","between"] },
      { name: "societe", label: "Société", type: "string", filters: ["=","like"] },
      { name: "created", label: "Date", type: "date", filters: ["=","between"] },
      { name: "payment_status", label: "Statut paiement", type: "const", filters: ["="], constStrings: HN.TC.Order.paymentStatusList },
      { name: "processing_status", label: "Statut traitement", type: "const", filters: ["="], constStrings: HN.TC.Order.globalProcessingStatusList },
      { name: "total_ttc", label: "Total TTC", type: "price", filters: ["=",">=","<=","between"] },
      { name: "validation_status", label: "Statut", type: "misc", filters: [
          { direct: true, text: "validée", ctext: "est", getFilterParam: function(data){ return ["o.validated > ?", 1]; } },
          { direct: true, text: "annulée", ctext: "est", getFilterParam: function(data){ return ["o.cancelled > ?", 1]; } },
          { direct: true, text: "OK compta", ctext: "est", getFilterParam: function(data){ return ["o.oked > ? AND o.validated = ?", [1,0]]; } }
        ],
        onCellWrite: function(rowData, colName){
          if (rowData.validated != 0)
            return "<span class=\"icon accept\" title=\"commande validée\"></span>";
          if (rowData.cancelled != 0)
            return "<span class=\"icon delete\" title=\"commande annulée\"></span>";
          if (rowData.oked != 0)
            return "<span class=\"icon clock\" title=\"commande OK compta\"></span>";
          return "";
        }
      }
    ],
    source: {
      fields: "o.id, o.type, o.campaign_id, o.societe, o.created, o.payment_status, o.processing_status, o.total_ttc, o.validated, o.cancelled, o.oked",
      tables: [
        ["from", "Order o"]
      ]
    },
    onRowEvent: {
      click: function(rowData, e){
        location.href = HN.TC.ADMIN_URL+"orders/order-detail.php?id="+rowData.id;
      }
    }
  });
  $("#showOrderList button").on("click", function(){
    document.location.href=HN.TC.ADMIN_URL+"orders/order-detail.php?id=new&client_id="+client_id;
  });
  
  el = new HN.TC.ItemList({
    domHandle: "#client-estimate-list",
    columns: [
      { name: "tool", label: "Voir", type: "misc",
        onCellWrite: function(rowData, colName){ return "<a href=\""+HN.TC.ADMIN_URL+"estimates/estimate-detail.php?id="+rowData.id+"\" class=\"icon application-double\"></a>"; },
        onCellEvent: { click: function(rowData, col, e){ e.stopPropagation(); e.preventDefault(); open($(this).find("a").attr("href"), "_blank"); } }
      },
      { name: "id", label: "ID", type: "string", filters: ["=","like"] },
      { name: "source", label: "source", type: "const", filters: ["="], constStrings: HN.TC.Estimate.sourceList },
      { name: "societe", label: "Nom client", type: "string" },
      { name: "sup_name", label: "Fournisseur principal", type: "string", filters: ["=","like"] },
      { name: "created", label: "Date de création", type: "date", filters: ["=","between"] },
      { name: "created_user_name", label: "Suivi par", type: "string", filters: ["=","like"] },
      { name: "status", label: "Etat", type: "const", filters: ["="], constStrings: HN.TC.Estimate.statusList },
      { name: "total_ht", label: "Total HT", type: "price", filters: ["=",">=","<=","between"] },
      { name: "total_ttc", label: "Total TTC", type: "price", filters: ["=",">=","<=","between"] },
      { name: "waiting_info_status", label: "en attente d'infos", type: "const", filters: ["="], constStrings: HN.TC.Estimate.waitingInfoList }
    ],
    source: {
      fields: "e.id,"+
              "e.source,"+
              "e.societe,"+
              "ms.nom1 AS sup_name,"+
              "e.created,"+
              "cu.login AS created_user_name,"+
              "e.status,"+
              "e.total_ht,"+
              "e.total_ttc,"+
              "e.waiting_info_status",
      tables: [
        ["from", "Estimate e"],
        ["leftJoin", "e.main_supplier ms"],
        ["leftJoin", "e.created_user cu"]
      ]
    },
    onRowEvent: {
      click: function(rowData, e){
        location.href = HN.TC.ADMIN_URL+"estimates/estimate-detail.php?id="+rowData.id;
      }
    }
  });
  $("#showEstimateList button").eq(0).on("click", function(){
    document.location.href=HN.TC.ADMIN_URL+"estimates/estimate-detail.php?id=new&client_id="+client_id;
  }).end().eq(1).on("click", function(){
    document.location.href=HN.TC.ADMIN_URL+"estimates/estimate-detail.php?id=new&type=ad_hoc&client_id="+client_id;
  });
  
  il = new HN.TC.ItemList({
    domHandle: "#client-invoice-list",
    columns: [
      { name: "tool", label: "Voir", type: "misc",
        onCellWrite: function(rowData, colName){ return "<a href=\""+HN.TC.ADMIN_URL+"invoices/invoice-detail.php?id="+rowData.id+"\" class=\"icon application-double\"></a>"; },
        onCellEvent: { click: function(rowData, col, e){ e.stopPropagation(); e.preventDefault(); open($(this).find("a").attr("href"), "_blank"); } }
      },
      { name: "type", label: "Type", type: "const", filters: ["="], constStrings: HN.TC.Invoice.typeList },
      { name: "rid", label: "Numéro", type: "int", filters: ["=","between"], onCellWrite: function(rowData, colName){ return rowData[colName] || "non défini"; } },
      { name: "activity", label: "Activité", type: "const", filters: ["="], constStrings: HN.TC.Invoice.activityList },
      { name: "societe", label: "Société", type: "string", filters: ["=","like"] },
      { name: "order_id", label: "ID commande source", type: "int", filters: ["=","between"] },
      { name: "estimate_id", label: "ID devis source", type: "int", filters: ["=","between"] },
      { name: "created", label: "Date de création", type: "date", filters: ["=","between"] },
      { name: "due_date", label: "Echéance", type: "date", filters: ["=","between"], onCellWrite: function(rowData, colName){
          return HN.TC.Invoice.typeList[rowData.type].toLowerCase() == "avoir" ? "avoir" : (rowData[colName] != 0 ? HN.TC.get_formated_date(rowData[colName]) : " - ");
        }
      },
      { name: "status", label: "Etat", type: "const", filters: ["="], constStrings: HN.TC.Invoice.statusList },
      { name: "total_ht", label: "Total HT", type: "price", filters: ["=",">=","<=","between"] },
      { name: "total_ttc", label: "Total TTC", type: "price", filters: ["=",">=","<=","between"] }
    ],
    source: {
      fields: "i.id, i.rid, i.type, i.activity, i.societe, i.order_id, i.estimate_id, i.created, i.due_date, i.status, i.total_ht, i.total_ttc",
      tables: [
        ["from", "Invoice i"]
      ]
    },
    onRowEvent: {
      click: function(rowData, e){
        location.href = HN.TC.ADMIN_URL+"invoices/invoice-detail.php?id="+rowData.id;
      }
    }
  });
  $("#showInvoiceList button").on("click", function(){
    document.location.href=HN.TC.ADMIN_URL+"invoices/invoice-detail.php?id=new&client_id="+client_id;
  }); 

</script>
<style>
#inCallbar {
    height: 60px;
    margin-left: auto;
    margin-right: auto;
    margin-top: -55px;
    padding-left: 125px;
    width: 650px;
}

.ui-statelike-choice-no {
    background: #f6f6f6 url("images/ui-bg_highlight-soft_100_f6f6f6_1x100.png") repeat-x scroll 50% 50%;
    border: 1px solid #f66;
    color: #990000;
    font-weight: bold;
    outline: medium none;
}

.ui-statelike-choice-no {
    background: #f6f6f6 url("images/ui-bg_highlight-soft_100_f6f6f6_1x100.png") repeat-x scroll 50% 50%;
    border: 1px solid #f66;
    color: #990000;
    font-weight: bold;
    outline: medium none;
}

a.btn {
    display: block;
    float: left;
    margin: 5px;
    padding: 0.4em 1em 0.4em 20px;
    position: relative;
}

#valider_modif0,#valider_modif1,#valider_modif2,#valider_modif3,#valider_modif4,#valider_modif5, {
    color: #0071bc;
    cursor: pointer;
    font-weight: bold;
    margin-bottom: 10px;
    overflow: hidden;
}

#annuler_modif0,#annuler_modif1,#annuler_modif2,#annuler_modif3,#annuler_modif4,#annuler_modif5 {
    color: #0071bc;
    cursor: pointer;
    font-weight: bold;
    margin-bottom: 10px;
    overflow: hidden;
}

#result_equip0,#result_equip1,#result_equip2,#result_equip3,#result_equip4,#result_equip5 {
    border: 1px solid rgb(221, 221, 221);
    float: left;
    margin-right: 10px;
    padding: 10px;
    width: 300px;
}

.name_campgne {
	color: #000; 
	font-weight: bold;
}

.margin-form_aac{
	float: right;
	width:265px;
	height : 30px !important;
}
</style>
<?php require(ADMIN.'tail.php'); ?>
