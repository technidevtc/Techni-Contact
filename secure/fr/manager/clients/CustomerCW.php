<?php

$handle = DBHandle::get_instance();
require_once ADMIN.'logs.php';

$user = new BOUser();

if (!$user->login()) {
  print "CustomerError" . __ERRORID_SEPARATOR__ . "Votre session a expirée, vous devez vous relogger." . __ERROR_SEPARATOR__ . __MAIN_SEPARATOR__;
  exit();
}
if (!$user->get_permissions()->has("m-comm--sm-customers","e")) {
  print "CustomerError" . __ERRORID_SEPARATOR__ . "Vous n'avez pas les droits adéquats pour réaliser cette opération." . __ERROR_SEPARATOR__ . __MAIN_SEPARATOR__;
  exit();
}

// Préparation liste des secteurs d'activité
$q = Doctrine_Core::getTable('ActivitySector')
  ->createQuery('as')
  ->select('as.id, as.sector, ass.qualification')
  ->leftJoin('as.Surqualifications ass');

$activity_sectors = $activity_sectorsList = $q->fetchArray();

mb_convert_variables("UTF-8", "ASCII,UTF-8,ISO-8859-1", $activity_sectors);
// correction des erreurs de parse json
foreach($activity_sectors as &$sectorList){
  if(is_array($sectorList) && !empty($sectorList))
  foreach($sectorList as &$surqualificationList){
    if(is_array($surqualificationList) && !empty($surqualificationList))
    foreach($surqualificationList as $index => &$surqualification)
    {
      $surqualification['qualification'] = preg_replace('/\r\n|\n\r|\n|\r/', '', $surqualification['qualification']) ;
      $surqualification['qualification'] = htmlspecialchars($surqualification['qualification'], ENT_QUOTES) ;
    }
  }
}
$jsonedActivitySectorList = json_encode($activity_sectors);

?>
<script type="text/javascript">
<!--
var CustomerFL = new Array("email", "tel1","societe",  "titre", "nom", "prenom", "fonction","service", "fax1", "adresse", "complement", "cp", "ville",
  "pays", "nb_salarie", "secteur_activite", "secteur_qualifie", "code_naf", "num_siret", "tva_intra", "coord_livraison", "titre_l", "prenom_l", "tel2", "adresse_l", "complement_l", "nom_l", "societe_l",
  "ville_l", "cp_l", "pays_l", "website_origin");
<?php
if (!defined("__NUMBER_OF_EMPLOYEE_LIST__")) {
  // Préparation liste des tailles salariales
  $n = $nec = 0; $nel = array(); // Number of Employee List
  if ($fh = fopen(MISC_INC . "list_number-of-employees.csv","r")) {
    define("__NUMBER_OF_EMPLOYEE_LIST__", true);
    while (($data = fgetcsv($fh, 64, ";")) !== false) $nel[$n++] = $data[0];
    $nec = $n - 1; // Number of Employee Count -> La 1ère ligne est l'intitulé des colonnes
    fclose($fh);
  }
  /*
  print 'var enl = ["' . $nel[0] . '"';
  for ($i=1; $i<$nec; $i++) print ',"' . $nel[$i] . '"';
  print "];\n";
  print "var enc = " . $nec . ";\n";*/
}

//if (!defined("__LINE_OF_BUSINESS_LIST__"))
//{
//	// Préparation liste des secteurs d'activité
//	$n = $lbc = 0; $lbl = array(); // Line of Business List
//	if ($fh = fopen(MISC_INC . "list_activity-sector.csv","r")) {
//		define("__LINE_OF_BUSINESS_LIST__", true);
//		while (($data = fgetcsv($fh, 64, ";")) !== false) $lbl[$n++] = $data[0];
//		$lbc = $n - 1; // Line of Business Count -> La 1ère ligne est l'intitulé des colonnes
//		fclose($fh);
//	}
//
//	/*
//	print 'var lbl = ["' . $lbl[1] . '"';
//	for ($i=2; $i<=$lbc; $i++) print ',"' . $lbl[$i] . '"';
//	print "];\n";
//	print "var lbc = " . $lbc . ";\n";*/
//}

if (!defined("__JOB_FONCTIONS_LIST__")) {
// Préparation liste des fonctions
  $n = $pc = 0; $pl = array(); // Post List
  if ($fh = fopen(MISC_INC."list_post.csv","r")) {
    define("__JOB_FONCTIONS_LIST__", true);
    while (($data = fgetcsv($fh, 128, ";")) !== false)
      if(strpos($data[1], '------------') === false)
        $pl[$n++] = $data[0];
    
    $pc = $n - 1; // Post Count -> La 1ère ligne est l'intitulé des colonnes
    fclose($fh);
  }
}
  
if (!defined("__COUNTRY_LIST__")) {
  // Préparation liste des pays en majuscule
  $n = $cc = 0; $cl = array(); // Country List
  if ($fh = fopen(MISC_INC . "list_country.csv","r")) {
    define("__COUNTRY_LIST__", true);
    while (($data = fgetcsv($fh, 128, ";")) !== false) $cl[$n++] = mb_strtoupper($data[0]);
    $cc = $n - 1; // Country Count -> La 1ère ligne est l'intitulé des colonnes
    fclose($fh);
  }
  /*
  print 'var cl = ["' . $cl[1] . '"';
  for ($i=2; $i<=$cc; $i++) print ',"' . $cl[$i] . '"';
  print "];\n";
  print "var cc = " . $cc . ";\n";*/
}
?>

function toggle_showCoordLivraisonC() {
  if ($("#toggle_coordlivraisonC").prop("checked")) {
    $("#coord_livraisonC").val(0);
    $("#coordlivraisonshownC").hide();
    $("#coordlivraisonmsgC").html("Cliquez ici si les coordonnées de livraison sont différentes de celles de facturation");
  } else {
    $("#coord_livraisonC").val(1);
    $("#coordlivraisonshownC").show();
    $("#coordlivraisonmsgC").html("Cliquez ici si les coordonnées de livraison sont les mêmes que celles de facturation");
  }
}

function ShowCustomerCW() {
  if (document.getElementById('CustomerCW').style.display != 'inline') {
    document.getElementById('CustomerCW').style.display = 'inline';
    document.getElementById('toggle_coordlivraisonC').checked = true;
    toggle_showCoordLivraisonC();
  }
}

function ClearCustomerCE()
{
  document.getElementById('AccountInfosCE').innerHTML = '<br />';
        document.getElementById('CustomerInfosCE').innerHTML = '<br />';
        document.getElementById('CompanyInfosCE').innerHTML = '<br />';
        document.getElementById('BillingAddressCE').innerHTML = '<br />';
}

function HideCustomerCW()
{
  if (document.getElementById('CustomerCW').style.display != 'none')
  {
    document.getElementById('CustomerCW').style.display = 'none';
    ClearCustomerCE();
  }
}

function CreateCustomer(dftValues) {
  var fieldlist = typeof dftValues == "object" ? $.extend({}, dftValues) : {};

  for (var p=0; p < CustomerFL.length; p++) {
    var fn = CustomerFL[p];
    if (fn == 'secteur_qualifie'){
      if ($('select[name=sector_qualification]').attr('selected', true) && $('select[name=sector_qualification]').val() != '' && typeof($('select[name=sector_qualification]').val()) != "undefined"){
        fieldlist[fn] = $('select[name=sector_qualification]').val();
      } else if($('input[name=qualification_sector_text]').val()) {
         fieldlist[fn] = $('input[name=qualification_sector_text]').val();
      }
    } else {
      fieldlist[fn] = $("#"+CustomerFL[p]+"C").val();
    }
  }
  $.ajax({
    url: HN.TC.ADMIN_URL+"clients/CustomerCreate.php",
    type: "POST",
    data: fieldlist,
    dataType: "text",
    error: function(jqXHR, textStatus, errorThrown){
      alert('Un problème est survenu au cours de la requête.');
    },
    success: function(data, textStatus, jqXHR){
      $("#PerfReqCCW").css("visibility", "hidden");
      var mainsplit = data.split(__MAIN_SEPARATOR__);
      
      if (mainsplit[0] == "") {
        HideCustomerCW();
        mainsplit[1] = mainsplit[1].replace(/[^0-9]+/, "");
        document.location.href = "index.php?idClient="+mainsplit[1];
      } else {
        var errors = mainsplit[0].split(__ERROR_SEPARATOR__);
        ClearCustomerCE();
        for (var i=0; i<errors.length-1; i++) {
          var errorID = errors[i].split(__ERRORID_SEPARATOR__);
          if (errorID.length == 2) {
            if (errorID[0] == "CustomerCE") {
              alert("Une ou plusieurs erreurs fatales sont intervenues lors de la création du compte client :\n"+errorID[1]);
            }
            else {
              $("#"+errorID[0]).html(errorID[1]);
            }
          }
        }
      }
	  
	    var site = $("#website_originC").val();
		if(site === "MOB"){	
		  $("#logo-send").html("<img src='../ressources/images/logo-website-mobaneo.jpg' />");
		  var send_html = '<option value="TC">Techni-Contact</option><option value="MOB">Mobaneo</option><option value="MER" >Mercateo</option>';
		}
		if(site === "MER"){	
		  $("#logo-send").html("<img src='../ressources/images/logo-website-mercateo.jpg' />");
		  var send_html = '<option value="TC">Techni-Contact</option><option value="MOB">Mobaneo</option><option value="MER" >Mercateo</option>';
		}

	  
    }
  });
}

function CreateHiddenCustomer() {
  CreateCustomer({ hidden: 1 });
}

//-->
</script>
  <div id="CustomerCW">
    <div class="window_title_bar">
      <img class="wtb_close_img" src="../ressources/window_close.gif" onMouseDown="swap_cbtn(this,'down')" onMouseOut="swap_cbtn(this,'out')" onMouseUp="swap_cbtn(this,'up')" />			
      <div onmousedown="grab(document.getElementById('CustomerCW'))">
        <img class="wtb_move_img" src="../ressources/window_move.gif" />
        <div class="wtb_text">Créer un compte client</div>
        <div class="zero"></div>
      </div>
    </div>
    <div id="PerfReqCCW" class="PerfReqLabelW">Création du compte client en cours...</div>
    <div class="window_bg">
      <div class="FieldList" id="FieldList">
        <div class="note">Note : les champs avec une * sont obligatoires</div>
        <div id="AccountInfosCE" class="InfosError"></div>
        <div id="CustomerInfosCE" class="InfosError"></div>
        <div id="CompanyInfosCE" class="InfosError"></div>
        <div id="BillingAddressCE" class="InfosError"></div>
        <div id="nShippingAddressCE" class="InfosError"></div>
        <div class="field"><div class="intitule" style="width: 120px">Email * :</div><input type="text" id="emailC" size="50" value="" maxlength="255" onBlur="this.value = trim(this.value)" /></div>
        <div class="field"><div class="intitule" style="width: 120px">Téléphone * :</div><input type="text" id="tel1C" class="reverso-enabled" size="25" value="" maxlength="255" onBlur="this.value = trim(this.value)" /></div>
        <div class="field"><div class="intitule" style="width: 120px">Société * :</div><input type="text" id="societeC" size="50" value="" maxlength="255" onBlur="this.value = trim(this.value); maj = this.value.charAt(0).toUpperCase(); if(this.value.length > 1) this.value = maj + this.value.substring(1, this.value.length); else this.value = maj" /></div>
        <div class="field"><div class="intitule" style="width: 120px">Titre * :</div><select id="titreC" class="titre"><option value="1">M.</option><option value="2">Mme</option><option value="3">Mlle</option></select></div>
        <div class="field"><div class="intitule" style="width: 120px">Nom * :</div><input type="text" id="nomC" size="25" value="" maxlength="255" onBlur="this.value = trim(this.value.toUpperCase())" /></div>
        <div class="field"><div class="intitule" style="width: 120px">Prénom * :</div><input type="text" id="prenomC" size="25" value="" maxlength="255" onBlur="this.value = trim(this.value); maj = this.value.charAt(0).toUpperCase(); if(this.value.length > 1) this.value = maj + this.value.substring(1, this.value.length); else this.value = maj" /></div>
        <div class="field">
          <div class="intitule" style="width: 120px">Fonction :</div>
          <select id="fonctionC">
            <option value=""> - </option>
           <?php for ($i = 1; $i <= $pc; $i++) : ?>
            <option value="<?php echo $pl[$i] ?>"><?php echo $pl[$i] ?></option>
           <?php endfor ?>
          </select>
        </div>
		
		<div class="field">
          <div class="intitule" style="width: 120px">Service :</div>
          <select id="serviceC">
            <option value=""> - </option>
            <option value="Maire">Maire</option>
            <option value="Elu municipal / Adjoint au maire">Elu municipal / Adjoint au maire</option>
            <option value="Service Technique / Maintenance">Service Technique / Maintenance</option>
            <option value="Service Achats">Service Achats</option>
            <option value="Service Sports">Service Sports</option>
            <option value="Service Communication">Service Communication</option>
            <option value="Service Urbanisme">Service Urbanisme</option>
            <option value="Service RH">Service RH</option>
            <option value="Service Travaux">Service Travaux</option>
          </select>
        </div>
		
		
        <div class="field"><div class="intitule" style="width: 120px">Fax :</div><input type="text" id="fax1C" size="25" value="" maxlength="255" onBlur="this.value = trim(this.value)" /></div>
        <div class="field"><div class="intitule" style="width: 120px">Adresse * :</div><input type="text" id="adresseC" size="70" value="" maxlength="255" onBlur="this.value = trim(this.value)" /></div>
        <div class="field"><div class="intitule" style="width: 120px">Adresse 2 :</div><input type="text" id="complementC" size="15" value="" maxlength="255" onBlur="this.value = trim(this.value)" /></div>
        <div class="field"><div class="intitule" style="width: 120px">CP * :</div><input type="text" id="cpC" size="5" maxlength="20" value="" onBlur="this.value = trim(this.value)" /></div>
        <div class="field"><div class="intitule" style="width: 120px">Ville * :</div><input type="text" id="villeC" size="20" maxlength="255" value="" onBlur="this.value = trim(this.value.toUpperCase())" /></div>
        <div class="field">
          <div class="intitule" style="width: 120px">Pays * :</div>
          <select id="paysC">
           <?php for ($i = 1; $i <= $cc; $i++) : ?>
            <option value="<?php echo $cl[$i] ?>"<?php echo ($cl[$i] == 'FRANCE' ? ' selected="selected"' : '') ?>><?php echo $cl[$i] ?></option>
           <?php endfor ?>
          </select>
        </div>
        <div class="field">
          <div class="intitule" style="width: 120px">Taille salariale :</div>
          <select id="nb_salarieC">
          <option value=""> - </option>
         <?php for ($i = 1; $i <= $nec; $i++) : ?>
          <option value="<?php echo $nel[$i] ?>"><?php echo $nel[$i] ?></option>
         <?php endfor ?>
          </select>
        </div>
        <div class="field">
          <div class="intitule" style="width: 120px">Secteur d'activité :</div>
          <select id="secteur_activiteC">
            <option value=""> - </option>
          <?php if (!empty ($activity_sectorsList)) : ?>
           <?php foreach ($activity_sectorsList as $activity_sector) : ?>
            <option value="<?php echo to_entities($activity_sector['sector']) ?>"><?php echo to_entities($activity_sector['sector']) ?></option>
           <?php endforeach ?>
          <?php endif ?>
          </select>
        </div>
        <div class="field"><div class="intitule" style="width: 120px">Code NAF :</div><input type="text" id="code_nafC" size="30" value="" maxlength="255" onBlur="this.value = trim(this.value)" /></div>
        <div class="field"><div class="intitule" style="width: 120px">N&deg; Siret :</div><input type="text" id="num_siretC" size="30" value="" maxlength="255" onBlur="this.value = trim(this.value)" /></div>
        <div class="field"><div class="intitule" style="width: 120px">TVA Intra :</div><input type="text" id="tva_intraC" size="30" value="" maxlength="255" onBlur="this.value = trim(this.value)" /></div>
        <br />
        <div class="coordlivraison">
          <input type="hidden" value="" id="coord_livraisonC" />
          <input type="checkbox" id="toggle_coordlivraisonC" onclick="toggle_showCoordLivraisonC()" />
          <label for="toggle_coordlivraisonC"><span id="coordlivraisonmsgC"></span></label>
        </div>
        <div id="coordlivraisonshownC">
          <div id="ShippingAddressCE" class="InfosError"><br /></div>
          <div class="field" style="margin-right: 15px"><div class="intitule" style="width: 120px">Titre * :</div><select id="titre_lC" class="titre"><option value="1">M.</option><option value="2">Mme</option><option value="3">Mlle</option></select></div>
          <div class="field"><div class="intitule" style="width: 120px">Nom * :</div><input type="text" id="nom_lC" size="25" value="" maxlength="255" onBlur="this.value = trim(this.value.toUpperCase())" /></div>
          <div class="field"><div class="intitule" style="width: 120px">Prénom * :</div><input type="text" id="prenom_lC" size="25" value="" maxlength="255" onBlur="this.value = trim(this.value); maj = this.value.charAt(0).toUpperCase(); if(this.value.length > 1) this.value = maj + this.value.substring(1, this.value.length); else this.value = maj" /></div>
          <div class="field"><div class="intitule" style="width: 120px">Téléphone 2 :</div><input type="text" id="tel2C" size="25" value="" maxlength="255" onBlur="this.value = trim(this.value)" /></div>
          <div class="zero"></div>
          <div class="field"><div class="intitule" style="width: 120px">Société * :</div><input type="text" id="societe_lC" size="50" maxlength="255" value="" onBlur="this.value = trim(this.value); maj = this.value.charAt(0).toUpperCase(); if(this.value.length > 1) this.value = maj + this.value.substring(1, this.value.length); else this.value = maj" /></div>
          <div class="field"><div class="intitule" style="width: 120px">Adresse * :</div><input type="text" id="adresse_lC" size="70" maxlength="255" value="" onBlur="this.value = trim(this.value)" /></div>
          <div class="field" style="margin-right: 20px"><div class="intitule" style="width: 120px">Complément :</div><input type="text" id="complement_lC" size="15" maxlength="255" value="" onBlur="this.value = trim(this.value)" /></div>
          <div class="field"><div class="intitule" style="width: 120px">CP * :</div><input type="text" id="cp_lC" size="5" maxlength="20" value="" onBlur="this.value = trim(this.value)" /></div>
          <div class="field"><div class="intitule" style="width: 120px">Ville * :</div><input type="text" id="ville_lC" size="20" maxlength="255" value="" onBlur="this.value = trim(this.value.toUpperCase())" /></div>
          <div class="field">
            <div class="intitule" style="width: 120px">Pays * :</div>
            <select id="pays_lC">
             <?php for ($i = 1; $i <= $cc; $i++) : ?>
              <option value="<?php echo $cl[$i] ?>"<?php echo ($cl[$i] == 'FRANCE' ? ' selected' : '')?>><?php echo $cl[$i] ?></option>
             <?php endfor ?>
            </select>
          </div>
          <div class="zero"></div>
        </div>
        <div class="field">
          <div class="intitule" style="width: 120px">Site d'origine123 :</div>
          <select id="website_originC">
			<option value="TC">Techni-Contact</option>
            <option value="MOB">Mobaneo</option>
            <option value="MER">Mercateo</option>
           <?php 
			/*
		   foreach ($website_origin_list as $wo_abr => $wo_name) : ?>
            <option value="<?php echo $wo_abr ?>"><?php echo $wo_name ?></option>
           <?php endforeach */?>
          </select>
        </div>
        <br />
        <div>
          <input class="fValidTrois" type="button" value="Créer" onclick="CreateCustomer()" />
          <input class="fValidTrois" type="button" value="Créer sans password" onclick="CreateHiddenCustomer()" />
        </div>
      </div>
    </div>
  </div>

<script type="text/javascript">
// Postal code autocomplete
var champCodePostal = $('#cpC');

champCodePostal.keyup( function(){
  if(champCodePostal.val().match('[0-9]{5}') ){

    $.ajax({
      type: "GET",
      data: "code_postal="+champCodePostal.val(),
      dataType: "json",
      url: "<?php echo ADMIN_URL ?>ressources/ajax/AJAX_codesPostaux.php",
      success: function(data) {

        var refBox = $('#villeC');

        if(data['reponses'].length > 1){

          var html = '<table id="cpAutocomplete" class="auto-completion-box" style="min-width: 221px; top: '+(refBox.offset().top + refBox.height() + 3)+'px; left: '+refBox.offset().left+'px; -moz-user-select: none;" >';
          $.each(data['reponses'], function(){
            html += '<tr class=""><td class="prop">'+this.commune+'</td><td class="results"></td></tr>';

          });
          html += '</table>';

          $('#cpAutocomplete').remove(); // avoid multiple layers in case of multiple keyups
          $('body').append(html);

          $.each($('#cpAutocomplete tr'), function(){
            $(this).mouseenter(function(){
              $(this).addClass('over');
            }).mouseleave(function(){
              $(this).removeClass('over');
            }).click(function(){
              refBox.val($(this).find('td.prop').html());
              $('#cpAutocomplete').remove();
            });
          });

          refBox.blur(function(){
            setTimeout(function(){$('#cpAutocomplete').remove();}, 200);
          });

        }else if(data['reponses'].length == 1){
          refBox.val(data['reponses'][0].commune);
        }
      }
    });
  } // endif(id == champCodePostal)
});
// Postal code autocomplete

// surqualification des secteurs d'activité
var sector_list = $.parseJSON('<?php echo $jsonedActivitySectorList ?>');
var sector_select = $('#secteur_activiteC');

sector_select.change(function(){add_qualification_form();});

function add_qualification_form(){
  $('#sector_surqualification').remove();

  var qualification_list = new Array();
  $.each(sector_list, function(){
     var sector = this['sector'];
     var surqualification = this['Surqualifications'];

     $('#secteur_activiteC option:selected').each(function(){
       if(sector == $(this).val())
         qualification_list = surqualification;
     });
  })

  if(sector_select.val() != ''){
    var qualification_options = new Array();
    if(qualification_list.length != 0)
      $.each(qualification_list, function(index){
        qualification_options[index] = this.qualification;
      });

      var select = '<div class="field" id="sector_surqualification"><div class="intitule" style="width: 120px">Secteur qualifié :</div><select class="value" id="sector_qualification" name="sector_qualification">';
      select += '<option value="">-</option>';
      if(qualification_options.length != 0)
        $.each(qualification_options, function(){
          select += '<option value="'+this.replace(/^\s+|\s+$/g,"")+'">'+this.replace(/^\s+|\s+$/g,"")+'</option>';
        });
    select += '</select> <div id="qualification_form_zone"  style="display:inline"><label for="qualification_sector_text">Secteur qualifié hors liste :</label><input type="text" value="" name="qualification_sector_text" /></div></div>';


    $('#secteur_activiteC').parent().after(select);
  }
  
}

// surqualification des secteurs d'activité

// automatic activity sector surqualification
var societe = $('#societeC');

societe.blur( function(){
  if($(this).val() != ''){ // && $("input[name='reversoReversed']").val() == 0

    $.ajax({
      type: "GET",
      data: {"params":[{"action":"processSector", "raison_sociale": $(this).val()}]},
      dataType: "json",
      url: "../../ressources/ajax/AJAX_surqualificationSecteursActivites.php",
      success: function(data) {
        if(data['retour'].length == 1){
          $('#secteur_activiteC option[value=\''+data.data[0].sector+'\']').attr('selected', true);
          add_qualification_form();
//          if(data.data[0].Surqualifications[0].qualification, data.data[0].Surqualifications[0].naf)
            $('#code_nafC').val(data.data[0].Surqualifications[0].naf);
          var inSelect = false;
//          if(data.data[0].Surqualifications[0].qualification){
             $('#sector_qualification option').each(function(){
               if(data.data[0].Surqualifications[0].qualification == $(this).val())
                 inSelect = true;
             });
            if(inSelect)
              $("#sector_qualification option[value='"+data.data[0].Surqualifications[0].qualification+"']").attr("selected", true);
            else
              $('input[name=qualification_sector_text]').val(data.data[0].Surqualifications[0].qualification);
//          }
        }
      }
    });
  }
});
// automatic activity sector surqualification
//-->
        </script>