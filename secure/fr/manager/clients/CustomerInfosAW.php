<?php

$handle = DBHandle::get_instance();
require_once(ADMIN."logs.php");

$user = new BOUser();

if(!$user->login()) {
  print "CustomerError" . __ERRORID_SEPARATOR__ . "Votre session a expirée, vous devez vous relogger." . __ERROR_SEPARATOR__ . __MAIN_SEPARATOR__;
  exit();
}
if (!$user->get_permissions()->has("m-comm--sm-customers","e")) {
  print "CustomerError" . __ERRORID_SEPARATOR__ . "Vous n'avez pas les droits adéquats pour réaliser cette opération." . __ERROR_SEPARATOR__ . __MAIN_SEPARATOR__;
  exit();
}

header("Content-Type: text/plain; charset=utf-8");

?>
<script type="text/javascript">
<!--

<?php

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
  
function toggle_showCoordLivraison() {
  if ($("#toggle_coordlivraison").prop("checked")) {
    $("#coord_livraison").val(0);
    $("#coordlivraisonshown").hide();
    $("#coordlivraisonmsg").html("Cliquez ici si les coordonnées de livraison sont différentes de celles de facturation");
  } else {
    $("#coord_livraison").val(1);
    $("#coordlivraisonshown").show();
    $("#coordlivraisonmsg").html("Cliquez ici si les coordonnées de livraison sont les mêmes que celles de facturation");
  }
}

function trim(s) {
  return s.replace(/(^\s*)|(\s*$)/g, '');
}

function ShowCustomerInfosAW() {
	
  if (document.getElementById('CustomerInfosAW').style.display != 'inline') {
	  
    if (ShippingAddress['coord_livraison'] == '1')
      document.getElementById('toggle_coordlivraison').checked = false;
    else
      document.getElementById('toggle_coordlivraison').checked = true;
    
    toggle_showCoordLivraison();
    for (var p in CustomerInfos)
      document.getElementById(p).value = CustomerInfos[p];
    for (var p in CompanyInfos) {
      if (p == 'secteur_qualifie') {
        if (CompanyInfos[p] != '') {
          add_qualification_form();
          var sector_list = $.parseJSON('<?php echo $jsonedActivitySectorList ?>');
		  
          $.each(sector_list, function(){
            if (this.sector == CompanyInfos['secteur_activite']) {
              var matched = false;
              $.each(this.Surqualifications, function(){
                var replacedCompany = CompanyInfos[p].replace("'", '&#039;');
                replacedCompany = replacedCompany.replace(/\r\n|\n\r|\n|\r/g, '');
                if(this.qualification.match(replacedCompany))
                  matched = CompanyInfos[p];
              })
              if(matched){
                $('select[name=sector_qualification]').val(matched.replace('&#039;', "'"));
              } else {
                $('input[name=qualification_sector_text]').val(CompanyInfos[p]);
              }
            }
          });
        }
      } else {
        document.getElementById(p).value = CompanyInfos[p];
      }
    }
    for (var p in BillingAddress)
      document.getElementById(p).value = BillingAddress[p];
    for (var p in ShippingAddress)
      document.getElementById(p).value = ShippingAddress[p];
    document.getElementById('CustomerInfosAW').style.display = 'inline';
  }
}

function HideCustomerInfosAW() {
  document.getElementById('CustomerInfosAW').style.display = 'none';
  document.getElementById('CustomerInfosError').innerHTML = '<br />';
  document.getElementById('CompanyInfosError').innerHTML = '<br />';
  document.getElementById('BillingAddressError').innerHTML = '<br />';
  document.getElementById('ShippingAddressError').innerHTML = '<br />';
}

function saveCustomer() {
	var service = $('#service').val();
	var tdtd 	= $("#service_label").val();
	
  var fieldlist = '';
  for (var p in CustomerInfos)
    fieldlist += '&' + p + '=' + document.getElementById(p).value;
  for (var p in CompanyInfos){
    if (p == 'secteur_qualifie') {
      if ($('select[name=sector_qualification]').attr('selected', true) && $('select[name=sector_qualification]').val() != '' && typeof($('select[name=sector_qualification]').val()) != "undefined") {
        fieldlist += '&' + p + '=' + escape( $('select[name=sector_qualification]').val());
      } else if($('input[name=qualification_sector_text]').val()) {
         fieldlist += '&' + p + '=' + escape($('input[name=qualification_sector_text]').val() );
      } else {
        fieldlist += '&' + p + '=';
      }
    } else {
      fieldlist += '&' + p + '=' + document.getElementById(p).value;
    }
  }
  service = $("#service").val();
  fieldlist += '&service='+service;
  for (var p in BillingAddress) fieldlist += '&' + p + '=' + document.getElementById(p).value;
  
  if (document.getElementById('coord_livraison').value == '1')
    for (var p in ShippingAddress) fieldlist += '&' + p + '=' + document.getElementById(p).value;
  else
    fieldlist += '&coord_livraison=0';
    var site = $("#website_origin").val();
    if(site === "MOB"){	
	  $("#logo-send").html("<img src='../ressources/images/logo-website-mobaneo.jpg' />");
	  // var send_html = '<option value="TC">Techni-Contact</option><option value="MOB">Mobaneo</option><option value="MER" >Mercateo</option>';
    }
	if(site === "MER"){	
	  $("#logo-send").html("<img src='../ressources/images/logo-website-mercateo.jpg' />");
	  // var send_html = '<option value="TC">Techni-Contact</option><option value="MOB">Mobaneo</option><option value="MER" >Mercateo</option>';
    }
	
	 //$(".select_update").html(send_html);
	
  
  AlterCustomer('&alterInfos=1' + encodeURI(fieldlist));
}

//-->
</script>
  <div id="CustomerInfosAW">
    <div class="window_title_bar">
      <img class="wtb_close_img" src="../ressources/window_close.gif" onMouseDown="swap_cbtn(this,'down')" onMouseOut="swap_cbtn(this,'out')" onMouseUp="swap_cbtn(this,'up')" />			
      <div onmousedown="grab(document.getElementById('CustomerInfosAW'))">
        <img class="wtb_move_img" src="../ressources/window_move.gif" />
        <div class="wtb_text">Modifier les Informations client</div>
        <div class="zero"></div>
      </div>
    </div>
	
    <div class="window_bg">
      <div class="FieldList">
        <div class="note">Note : les champs avec une * sont obligatoires</div>
        <div id="CustomerInfosError" class="InfosError"></div>
        <div id="CompanyInfosError" class="InfosError"></div>
        <div id="BillingAddressError" class="InfosError"></div>
        <div class="field"><div class="intitule" style="width: 120px">Email * :</div><input type="text" id="email" size="50" value="" maxlength="255" onBlur="this.value = trim(this.value)" /></div>
        <div class="field" style="margin-right: 35px"><div class="intitule" style="width: 120px">T&eacute;l&eacute;phone * :</div><input type="text" id="tel1" class="reverso-enabled" size="25" value="" maxlength="255" onBlur="this.value = trim(this.value)" /></div>
        <div class="field"><div class="intitule" style="width: 120px">Soci&eacute;t&eacute; * :</div><input type="text" id="societe" size="50" value="" maxlength="255" onBlur="this.value = trim(this.value); maj = this.value.charAt(0).toUpperCase(); if(this.value.length > 1) this.value = maj + this.value.substring(1, this.value.length); else this.value = maj" /></div>
        <div class="field" style="margin-right: 15px"><div class="intitule" style="width: 120px">Titre * :</div><select id="titre" class="titre"><option value="1">M.</option><option value="2">Mme</option><option value="3">Mlle</option></select></div>
        <div class="field"><div class="intitule" style="width: 120px">Nom * :</div><input type="text" id="nom" size="25" value="" maxlength="255" onBlur="this.value = trim(this.value.toUpperCase())" /></div>
        <div class="field"><div class="intitule" style="width: 120px">Pr&eacute;nom * :</div><input type="text" id="prenom" size="25" value="" maxlength="255" onBlur="this.value = trim(this.value); maj = this.value.charAt(0).toUpperCase(); if(this.value.length > 1) this.value = maj + this.value.substring(1, this.value.length); else this.value = maj" /></div>
        <div class="field">
          <div class="intitule" style="width: 120px">Fonction :</div>
          <select id="fonction">
            <option value=""> - </option>
           <?php for ($i = 1; $i <= $pc; $i++) : ?>
            <option value="<?php echo $pl[$i] ?>"><?php echo $pl[$i] ?></option>
           <?php endfor ?>
          </select>
        </div>
		
		<?php  
			$sql_service = "SELECT fonction_service,website_origin FROM clients WHERE iD='$clientID' ";
			$req_service = mysql_query($sql_service);
			$data_service= mysql_fetch_object($req_service);
			if($data_service->fonction_service == 'Maire' ) $selected_true1=" selected ";
			if($data_service->fonction_service == 'Elu municipal / Adjoint au maire' ) $selected_true2=" selected='true' ";
			if($data_service->fonction_service == 'Service Technique / Maintenance' ) $selected_true3=" selected='true' ";
			if($data_service->fonction_service == 'Service Achats' ) $selected_true4=" selected='true' ";
			if($data_service->fonction_service == 'Service Sports' ) $selected_true5=" selected='true' ";
			if($data_service->fonction_service == 'Service Communication' ) $selected_true6=" selected='true' ";
			if($data_service->fonction_service == 'Service Urbanisme' ) $selected_true7=" selected='true' ";
			if($data_service->fonction_service == 'Service RH' ) $selected_true8=" selected='true' ";
			if($data_service->fonction_service == 'Service Travaux' ) $selected_true9=" selected='true' ";
		?>
		
		<div class="field">
          <div class="intitule" style="width: 120px">Service :</div>
          <select id="service">
            <option value=""> - </option>
            <option value="Maire" <?= $selected_true1 ?>>Maire</option>
            <option value="Elu municipal / Adjoint au maire" <?= $selected_true2 ?>>Elu municipal / Adjoint au maire</option>
            <option value="Service Technique / Maintenance" <?= $selected_true3 ?>>Service Technique / Maintenance</option>
            <option value="Service Achats" <?= $selected_true4 ?>>Service Achats</option>
            <option value="Service Sports" <?= $selected_true5 ?>>Service Sports</option>
            <option value="Service Communication" <?= $selected_true6 ?>>Service Communication</option>
            <option value="Service Urbanisme" <?= $selected_true7 ?>>Service Urbanisme</option>
            <option value="Service RH" <?= $selected_true8 ?>>Service RH</option>
            <option value="Service Travaux" <?= $selected_true9 ?>>Service Travaux</option>
          </select>
        </div>
		
        <div class="field"><div class="intitule"  style="width: 120px">Fax :</div><input type="text" id="fax1" size="25" value="" maxlength="255" onBlur="this.value = trim(this.value)" /></div>
        <div class="field"><div class="intitule" style="width: 120px">Adresse * :</div><input type="text" id="adresse" size="70" value="" maxlength="255" onBlur="this.value = trim(this.value)" /></div>
        <div class="field" style="margin-right: 20px"><div class="intitule" style="width: 120px">Adresse 2 :</div><input type="text" id="complement" size="15" value="" maxlength="255" onBlur="this.value = trim(this.value)" /></div>
        <div class="field"><div class="intitule" style="width: 120px">CP * :</div><input type="text" id="cp" size="5" maxlength="20" value="" onBlur="this.value = trim(this.value)" /></div>
        <div class="field"><div class="intitule" style="width: 120px">Ville * :</div><input type="text" id="ville" size="20" maxlength="255" value="" onBlur="this.value = trim(this.value.toUpperCase())" /></div>
        <div class="field">
          <div class="intitule" style="width: 120px">Pays * :</div>
          <select id="pays">
           <?php for ($i = 1; $i <= $cc; $i++) : ?>
            <option value="<?php echo $cl[$i] ?>"><?php echo $cl[$i] ?></option>
           <?php endfor ?>
          </select>
        </div>
        <div class="field">
          <div class="intitule" style="width: 120px">Taille salariale :</div>
          <select id="nb_salarie">
          <option value=""> - </option>
         <?php for ($i = 1; $i <= $nec; $i++) : ?>
          <option value="<?php echo $nel[$i] ?>"><?php echo $nel[$i] ?></option>
         <?php endfor ?>
          </select>
        </div>
        <div class="field">
          <div class="intitule" style="width: 120px">Secteur d'activit&eacute; * :</div>
          <select id="secteur_activite">
          <option value=""> - </option>
          <?php if (!empty ($activity_sectorsList)) : ?>
           <?php foreach ($activity_sectorsList as $activity_sector) : ?>
            <option value="<?php echo to_entities($activity_sector['sector']) ?>"><?php echo to_entities($activity_sector['sector']) ?></option>
           <?php endforeach ?>
          <?php endif ?>
          </select>
        </div>
        <div class="field"><div class="intitule" style="width: 120px">Code NAF :</div><input type="text" id="code_naf" size="30" value="" maxlength="255" onBlur="this.value = trim(this.value)" /></div>
        <div class="field"><div class="intitule" style="width: 120px">N&deg; Siret :</div><input type="text" id="num_siret" size="30" value="" maxlength="255" onBlur="this.value = trim(this.value)" /></div>
        <div class="field"><div class="intitule" style="width: 120px">TVA Intra :</div><input type="text" id="tva_intra" size="30" value="" maxlength="255" onBlur="this.value = trim(this.value)" /></div>
        
        <div class="coordlivraison">
          <input type="hidden" value="" id="coord_livraison" />
          <input type="checkbox" id="toggle_coordlivraison" onclick="toggle_showCoordLivraison()" />
          <span id="coordlivraisonmsg"></span>
        </div>
        <div id="coordlivraisonshown">
          <div id="ShippingAddressError" class="InfosError"><br /></div>
          <div class="field" style="margin-right: 15px"><div class="intitule" style="width: 120px">Titre * :</div><select id="titre_l" class="titre"><option value="1">M.</option><option value="2">Mlle</option><option value="3">Mme</option></select></div>
          <div class="field"><div class="intitule" style="width: 120px">Nom * :</div><input type="text" id="nom_l" size="25" value="" maxlength="255" onBlur="this.value = trim(this.value.toUpperCase())" /></div>
          <div class="field"><div class="intitule" style="width: 120px">Pr&eacute;nom * :</div><input type="text" id="prenom_l" size="25" value="" maxlength="255" onBlur="this.value = trim(this.value); maj = this.value.charAt(0).toUpperCase(); if(this.value.length > 1) this.value = maj + this.value.substring(1, this.value.length); else this.value = maj" /></div>
          <div class="field"><div class="intitule" style="width: 120px">Soci&eacute;t&eacute; * :</div><input type="text" id="societe_l" size="50" maxlength="255" value="" onBlur="this.value = trim(this.value); maj = this.value.charAt(0).toUpperCase(); if(this.value.length > 1) this.value = maj + this.value.substring(1, this.value.length); else this.value = maj" /></div>
          <div class="field" style="margin-right: 35px"><div class="intitule" style="width: 120px">T&eacute;l&eacute;phone 2 :</div><input type="text" id="tel2" size="25" value="" maxlength="255" onBlur="this.value = trim(this.value)" /></div>
          <div class="field"><div class="intitule" style="width: 120px">Adresse * :</div><input type="text" id="adresse_l" size="70" maxlength="255" value="" onBlur="this.value = trim(this.value)" /></div>
          <div class="field" style="margin-right: 20px"><div class="intitule" style="width: 120px">Compl&eacute;ment :</div><input type="text" id="complement_l" size="15" maxlength="255" value="" onBlur="this.value = trim(this.value)" /></div>
          <div class="field"><div class="intitule" style="width: 120px">CP * :</div><input type="text" id="cp_l" size="5" maxlength="20" value="" onBlur="this.value = trim(this.value)" /></div>
          <div class="field"><div class="intitule" style="width: 120px">Ville * :</div><input type="text" id="ville_l" size="20" maxlength="255" value="" onBlur="this.value = trim(this.value.toUpperCase())" /></div>
          <div class="field">
		  
            <div class="intitule" style="width: 120px">Pays * :</div>
            <select id="pays_l">
             <?php for ($i = 1; $i <= $cc; $i++) : ?>
              <option value="<?php echo $cl[$i] ?>"<?php echo ($cl[$i] == 'FRANCE' ? ' selected' : '')?>><?php echo $cl[$i] ?></option>
             <?php endfor ?>
            </select>
          </div>
        </div>
        <div class="field">
          <div class="intitule" style="width: 120px">Site d'origine : </div>
          <select id="website_origin" class="select_update">
            <?php
			    if($data_service->website_origin == 'TC')  $selected1 = ' selected="true" ';
				if($data_service->website_origin == 'MOB') $selected2 = ' selected="true" ';
				if($data_service->website_origin == 'MER') $selected3 = ' selected="true" ';
			?>
			<option value="TC"  <?= $selected1 ?>>Techni-Contact</option>
            <option value="MOB" <?= $selected2 ?>>Mobaneo</option>
            <option value="MER" <?= $selected3 ?>>Mercateo</option>
           
			<?php /*foreach ($website_origin_list as $wo_abr => $wo_name) : 
				//if($data_service->website_origin == $wo_abr) $selected = ' selected="true" ';
				//else $selected='';
				endforeach*/
			?>
          </select>
        </div>
        <br />
        <div><input class="fValidTrois" type="button" value="Valider" onclick="saveCustomer()" /></div>
      </div>
    </div>
  </div>
<script type="text/javascript">
// Postal code autocomplete
var champCodePostal = $('#cp');

champCodePostal.keyup( function(){
  if(champCodePostal.val().match('[0-9]{5}') ){

    $.ajax({
      type: "GET",
      data: "code_postal="+champCodePostal.val(),
      dataType: "json",
      url: "<?php echo ADMIN_URL ?>ressources/ajax/AJAX_codesPostaux.php",
      success: function(data) {

        var refBox = $('#ville');

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
var sector_select = $('#secteur_activite');

sector_select.change(function(){add_qualification_form();});

function add_qualification_form(){
  $('#sector_surqualification2').remove();

  var qualification_list = new Array();
  $.each(sector_list, function(){

     var sector = this['sector'];
     var surqualification = this['Surqualifications'];

     $('#secteur_activite option:selected').each(function(){
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

      var select = '<div class="field" style="display:inline" id="sector_surqualification2"><div class="intitule" style="width: 120px">Secteur qualifi&eacute; :</div><select class="value" id="sector_qualification" name="sector_qualification">';
      select += '<option value="">-</option>';
      if(qualification_options.length != 0)
        $.each(qualification_options, function(){
          select += '<option value="'+this.replace(/^\s+|\s+$/g,"")+'">'+this.replace(/^\s+|\s+$/g,"")+'</option>';
        });
    select += '</select> <div id="qualification_form_zone"><label for="qualification_sector_text">Secteur qualifi&eacute; hors liste :</label><input type="text" value="" name="qualification_sector_text" /></div></div>';

    $('#secteur_activite').parent().after(select);
  }

}

// surqualification des secteurs d'activité

// automatic activity sector surqualification
var societe = $('#societe');

societe.blur( function(){
  if($(this).val() != ''){ // && $("input[name='reversoReversed']").val() == 0

    $.ajax({
      type: "GET",
      data: {"params":[{"action":"processSector", "raison_sociale": $(this).val()}]},
      dataType: "json",
      url: "../../ressources/ajax/AJAX_surqualificationSecteursActivites.php",
      success: function(data) {
        if(data['retour'].length == 1){
          $('#secteur_activite option[value=\''+data.data[0].sector+'\']').attr('selected', true);
          add_qualification_form();
//          if(data.data[0].Surqualifications[0].qualification, data.data[0].Surqualifications[0].naf)
            $('#code_naf').val(data.data[0].Surqualifications[0].naf);
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

                </script>
