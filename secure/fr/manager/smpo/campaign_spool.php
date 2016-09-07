<?php
require_once substr(dirname(__FILE__),0,strpos(dirname(__FILE__),'/',stripos(dirname(__FILE__),'technico')+1)+1).'config.php';

$db = DBHandle::get_instance();

$title = $navBar = "Campagne d'appels";
require(ADMIN."head.php");

define('DAILY_NB_UNANSWERED_CALLS', 3); // defines the number of unanswered call per day

if (!$userPerms->has($fntByName["m-smpo--sm-campaign"], "re")) { ?>
  <div class="bg">
    <div class="fatalerror">Vous n'avez pas les droits adéquats pour réaliser cette opération.</div>
  </div>
<?php } elseif (empty($_GET['idCampaign'])) { ?>
  <div class="bg">
    <div class="fatalerror">Campagne introuvable</div>
  </div>
<?php } elseif (($campaign = new Campaign($_GET['idCampaign'])) && !$campaign->exists) { ?>
  <div class="bg">
    <div class="fatalerror">Campagne inexistante</div>
  </div>
<?php } else {
  	
	// Préparation liste des fonctions
	$n = $pc = 0; $pl = array(); // Post List
	if ($fh = fopen(MISC_INC . "list_post.csv","r")) {
		while (($datacsv = fgetcsv($fh, 128, ";")) !== false) $pl[$n++] = $datacsv;
		$pc = $n - 1; // Post Count -> La 1ère ligne est l'intitulé des colonnes
		fclose($fh);
	}
	
	// Préparation liste des tailles salariales
	$n = $nec = 0; $nel = array(); // Number of Employee List
	if ($fh = fopen(MISC_INC . "list_number-of-employees.csv","r")) {
		while (($datacsv = fgetcsv($fh, 64, ";")) !== false) $nel[$n++] = $datacsv[0];
		$nec = $n - 1; // Number of Employee Count -> La 1ère ligne est l'intitulé des colonnes
		fclose($fh);
	}

	// Préparation liste des secteurs d'activité
        $activity_sectors = Doctrine_Core::getTable('ActivitySector');
        $activity_sectorsList = $activity_sectors->findAll();
?>

<link rel="stylesheet" type="text/css" href="smpo.css" />
<script src="../js/ManagerFunctions.js" type="text/javascript"></script>
<script type="text/javascript" src="leads.js"></script>
<div class="titreStandard">Campagne d'appels <?php echo $campaign->nom ?></div>
<br/>
<div class="section">
  <input type="hidden" name="idCampaign" value="<?php echo $_GET['idCampaign'] ?>" />
  <div style="color: #FF0000" id="show_error_message"><?php if (!empty($errorstring)) echo $errorstring ?></div>
  <div class="block">
  <?php if ($userPerms->has($fntByName["m-smpo--sm-spool-filter-campagnes-appels"], "r")) {
			$display = 'display:block';
		}else {
			$display = 'display:none';
		}
	?>
	<div class="block  fl"  style="border: medium none; width: 50%;<?= $display ?>">
      <div class="title" >Options de filtrage</div>
      <div class="text">
        <fieldset class="selector">
        <legend>Fonction :</legend>
        <select id="fonction-selector" name="fonction" class="edit"  onChange="updateListe()">
              <option value=""> - </option>
            <?php for ($i = 1; $i <= $pc; $i++) {
                if(strpos($pl[$i][1], '-----------') === false){?>
              <option value="<?php if(!empty($pl[$i][0])) echo $pl[$i][0]; else echo$pl[$i][1] ?>"<?php if (( $infos["fonction"] == $pl[$i][0]) && $pl[$i][0] != '') { ?> selected="selected"<?php } ?>><?php echo htmlentities($pl[$i][1]) ?></option>
              <?php }
            }?>
        </select>
		</fieldset>
	 
	<?php
	/*
	  <fieldset class="selector">
        <legend>Taille salariale :</legend>
        <select id="salaries-selector" name="salaries" class="edit"  onChange="updateListe()">
              <option value=""> - </option>
            <?php for ($i = 1; $i <= $nec; $i++) { ?>
              <option value="<?php echo $nel[$i] ?>"<?php if ((isset($_COOKIE["salaries"]) && $show && $_COOKIE["salaries"] == htmlentities($nel[$i])) || $infos["salaries"] == $nel[$i]) { ?> selected="selected"<?php } ?>><?php echo htmlentities($nel[$i]) ?></option>
            <?php } ?>
        </select>
      </fieldset>
	*/
	?>
	  <fieldset class="selector">
        <legend>Secteur :</legend>
        <select id="secteur-selector" name="secteur" class="edit" onChange="updateListe()">
              <option value=""> - </option>
              <?php
          if(!empty($activity_sectorsList))
            foreach ($activity_sectorsList as $activity_sector) { ?>
            <option value="<?php echo $activity_sector['sector'] ?>"><?php echo to_entities($activity_sector['sector']) ?></option>
          <?php } ?>
        </select>
      </fieldset>
	  
	  <fieldset class="check-box">
        <legend>Nb appels affichés :</legend>
        <select id="shown-call-count"  onChange="updateListe()">
          <option value="5">5</option>
          <option value="10">10</option>
          <option value="20">20</option>
          <option value="50">50</option>
        </select>
      </fieldset>
		
        <fieldset class="check-box">
          <legend>Contacts non appelés :</legend>
          <label for="uncalled">Oui</label> <input type="checkbox" name="uncalled" id="uncalled" value="1"  onChange="updateListe()" />
        </fieldset>
		
	  <fieldset class="check-box">
          <legend>Appels en absence :</legend>
          <label for="unanswered">Oui</label> <input type="checkbox" name="unanswered" id="unanswered" value="1"  onChange="updateListe()" />
        </fieldset>
      </div>
	
    </div>
	<?php if ($userPerms->has($fntByName["m-smpo--sm-spool-filter-campagnes-appels"], "r")) {
			$width_tab = 'width: 47%;; margin-top: -176px;'; 
		}else $width_tab = 'width: 100%;';
	?>
    
     <div class="block  fr"  style="border: medium none; <?= $width_tab ?>">
      <div class="title">Reporting</div>
      <div class="text">
        <table class="reporting fl">
          <caption>Données campagne</caption>
          <tr>
            <td class="libelle">Nb contact éligibles</td><td class="valeur"><span id="nbCallsTotal"></span></td>
          </tr>
          <tr>
            <td class="libelle">Reste à appeler</td><td class="valeur"><span id="nbCallsToDo"></span></td>
          </tr>
          <tr>
            <td class="libelle">Nb contacts aboutis</td><td class="valeur"><span id="nbCallsMade"></span></td>
          </tr>
          <tr>
            <td class="libelle">Nb contacts non aboutis</td><td class="valeur"><span id="nbCallsInAbsence"></span></td>
          </tr>
          <tr>
            <td class="libelle">Joignabilité</td><td class="valeur"><span id="callsMadeRate"></span> %</td>
          </tr>
          <tr>
            <td class="libelle">Nb contacts transformés</td><td class="valeur"><span id="nbLeadsMade"></span></td>
          </tr>
          <tr>
            <td class="libelle">Taux de transfo</td><td class="valeur"><span id="leadsMadeRate"></span> %</td>
          </tr>
        </table>

        <table class="reporting fr">
          <caption>Perfs individuelles</caption>
          <thead>
            <tr>
              <td></td><td>Transformés</td><td>Taux</td>
            </tr>
          </thead>
          <tbody id="operatorStats">
          </tbody>
        </table>
        <div class="zero"></div>
      </div>
    </div>
    <div class="zero"></div>
  </div>
  <br />
  <table id="item-list" class="item-list" cellspacing="0" cellpadding="0">
    <thead>
      <tr>
        <th style="width : 60px">Date/Heure</th>
        <th style="width : 60px">Date lead</th>
        <th>Société</th>
        <th>Prénom Nom</th>
        <th style="width : 140px">Fonction</th>
        <th style="width : 140px">Secteur</th>
        <th style="width : 70px">Taille</th>
        <th>Produit demandé</th>
        <th style="width : 90px">Nb d'appels en absence</th>
        <th style="width : 30px"></th>
      </tr>
    </thead>
    <tbody id="content-list">
      <tr class="tr-new"><td class="date" colspan="9"><img src="../../ressources/images/lightbox-ico-loading.gif" alt="chargement..."></td></tr>
    </tbody>
  </table>
</div>
<script type="text/javascript">
 function decode_utf8(s) {
  return decodeURIComponent(escape(s));
}
 <!--
var AJAXHandle = {
  type: "GET",
  url: "AJAX_campaign-spool.php",
  dataType: "json",
  error: function (XMLHttpRequest, textStatus, errorThrown) {
    var tbody = $("#content-list");
    tbody.empty();
    tbody.append( '<tr class="tr-new"><td class="date" colspan="9"> '+textStatus+'</td></tr>');
  },
  success: function (data, textStatus) {
    var tbody = $("#content-list").empty();
    var operatorBody = $("#operatorStats").empty();

    if (data.error) {
      tbody.append('<tr class="tr-new"><td class="date" colspan="9" style="color : red"> '+data.error+'</td></tr>');
    }
    else if (data.reponses == 'vide') {
      tbody.append('<tr class="tr-new"><td class="date" colspan="9"> Aucun appel dans la file d\'attente. </td></tr>');
    }
    else {
      for (var i=0; i<data.reponses.length; i++) {
        var r = data.reponses[i];
        if (r.nbrDailyCalls < <?php echo DAILY_NB_UNANSWERED_CALLS ?>) {

          var rdvExists = data.reponses[i].rdvExists ? ' rdvExists' : '';
          // tr type
          var tr = '<tr'+
                   ' class="tr-normal'+rdvExists+'"'+
                   ' onmouseover="this.className=\'tr-hover\'"'+
                   ' onmouseout="this.className=\'tr-normal'+rdvExists+'\'"'+
                   ' onclick="isAlreadyPickedUp('+r.id+', \''+r.id_client+'\', \''+r.id_lead+'\', \''+r.tel+'\');\" >';

          // date format
          var date = new Date(r.timestamp*1000);
          var year = date.getFullYear();
          var month = date.getMonth()+1;
          month = month.toString();
          if (month.length !=2){month = '0'+month};
          var day = date.getDate().toString();
          if (day.length !=2){day = '0'+day};
          var hours = date.getHours().toString();
          if (hours.length !=2){hours = '0'+hours};
          var minutes = date.getMinutes().toString();
          if (minutes.length !=2){minutes = '0'+minutes};
          var seconds = date.getSeconds().toString();
          if (seconds.length !=2){seconds = '0'+seconds};
          date = day+'/'+month+'/'+year+' '+hours+':'+minutes;

          // date format
          var dateLead = new Date(r.dateLead*1000);
          var year = dateLead.getFullYear();
          var month = dateLead.getMonth()+1;
          month = month.toString();
          if (month.length !=2){month = '0'+month};
          var day = dateLead.getDate().toString();
          if (day.length !=2){day = '0'+day};
          var hours = dateLead.getHours().toString();
          if (hours.length !=2){hours = '0'+hours};
          var minutes = dateLead.getMinutes().toString();
          if (minutes.length !=2){minutes = '0'+minutes};
          var seconds = dateLead.getSeconds().toString();
          if (seconds.length !=2){seconds = '0'+seconds};
          dateLead = day+'/'+month+'/'+year+' '+hours+':'+minutes;

          var nom_client = r.nom+' '+r.prenom;

          tbody.append(
          tr +
          "	<td class=\"date\">"+date+"</td>" +
          "	<td class=\"date\">"+dateLead+"</td>" +
          "	<td class=\"date\">"+r.societe+"</td>" +
          "	<td class=\"date\">"+nom_client+"</td>" +
          "	<td class=\"date\">"+r.fonction+"</td>" +
          "	<td class=\"produit\">" + r.secteur + "</td>" +
          "	<td class=\"type\">" +r.salaries+  "</td>" +
          "	<td class=\"date\">"+r.product_name+"</td>" +
          "	<td class=\"nombre\">" +r.nbrCallsInAbsence+"</td>" +
          "	<td class=\"nombre\"><img src=\"../ressources/icons/telephone.png\" /></td>" +
          "</tr>");
        }
      }
    }

    if (data.dataCampaign) {
      $('#nbCallsTotal').html(data.dataCampaign[0].nbCallsTotal);
      $('#nbCallsToDo').html(data.dataCampaign[0].nbCallsToDo);
      $('#callsMadeRate').html(data.dataCampaign[0].callsMadeRate);
      $('#nbCallsMade').html(data.dataCampaign[0].nbCallsMade);
      $('#nbLeadsMade').html(data.dataCampaign[0].nbLeadsMade);
      $('#nbCallsInAbsence').html(data.dataCampaign[0].nbCallsInAbsence);
      $('#leadsMadeRate').html(data.dataCampaign[0].leadsMadeRate);
    }

    if (data.opStats) {
      for (j = 0; j < data.opStats.length; j++) {
        var tr = '<tr>';
        operatorBody.append(
        tr +
        "	<td>"+data.opStats[j].operatorName+"</td>" +
        "	<td style=\"text-align : center\">"+data.opStats[j].nbLeadsMadeByOp+"</td>" +
        "	<td style=\"text-align : center\">"+data.opStats[j].leadsMadeRateByOp+"</td></tr>");
      }
    }
  }
};

var AJAXHandleProcessCall = {
  type : "GET",
  url: "AJAX_process-call-campaign.php",
  dataType: "json",
  error: function (XMLHttpRequest, textStatus, errorThrown) {
    $('#show_error_message').text(textStatus);
  },
  success: function (data, textStatus) {
    if (data.error) {
      $('#show_error_message').text(data.error);
    }

    var me = this;
    if (data.result == 'ok') {
      document.location.href = 'dial:'+me.tel;
      window.setTimeout(function(){
        location.href= '<?php echo ADMIN_URL ?>contacts/lead-create.php?idClient='+me.idClient+'&idLead='+me.idLead+'&idCallCampaign='+me.idCallCampaign+'&idCampaign='+me.idCampaign
      }, 100);
    }
  }
};

var lastUpdateListTime = 0;
function updateListe() {
  var date  = new Date();
  var updateListeTO = date.getTime() - lastUpdateListTime;
  if (updateListeTO < 500)
    updateListeTO = 500 - updateListeTO;
  else
    updateListeTO = 0;
    
  setTimeout(function(){

    var tbody = $("#content-list");
	
	
    var idCampaign = $('input[name=idCampaign]').val();
    var unanswered = $('#unanswered').attr('checked') == true ? 1 : 0;
    var uncalled   = $('#uncalled').attr('checked') == 'checked' ? 1 : 0;
	
	var fonction = typeof $('#fonction-selector').val() !== 'undefined' ? $('#fonction-selector').val() : '';
    var salaries = typeof $('#salaries-selector').val() !== 'undefined' ? $('#salaries-selector').val() : '';
    var secteur  = typeof $('#secteur-selector').val() !== 'undefined' ? $('#secteur-selector').val() : '';
	var shown_call_count = typeof $('#shown-call-count').val() !== 'undefined' ? $('#shown-call-count').val() : '';
	
	
    AJAXHandle.data = "fonction="+fonction+"&salaries="+salaries+"&secteur="+secteur+"&uncalled="+uncalled+"&idCampaign="+idCampaign+"&unanswered="+unanswered+"&shown-call-count="+shown_call_count;
    $.ajax(AJAXHandle);
    
    lastUpdateListTime = date.getTime();
  }, updateListeTO);
}

var AJAXHandleTestCall = {
  type: "GET",
  url: "AJAX_process-call-campaign.php",
  dataType: "json",
  error: function (XMLHttpRequest, textStatus, errorThrown) {
    $('#show_error_message').text(textStatus);
  },
  success: function (data, textStatus) {
    if (data.error) {
      $('#show_error_message').text(data.error);
    }

    if (data.result == 'testNok') {
      updateListe();
      alert('Ce contact est déjà en cours d\'appel');
    }
    else if (data.result == 'testOk') {
      makeCall(this.idCallCampaign, this.idClient, this.idLead, this.tel);
    }
  }
};

function isAlreadyPickedUp(idCallCampaign, idClient, idLead, tel){
  clearInterval(timer);
  AJAXHandleTestCall.data = "id_call="+idCallCampaign+"&testPickedUp=1";
  AJAXHandleTestCall.idClient = idClient;
  AJAXHandleTestCall.idCallCampaign = idCallCampaign;
  AJAXHandleTestCall.idLead = idLead;
  AJAXHandleTestCall.tel = tel;
  $.ajax(AJAXHandleTestCall);
  
}

function makeCall(idCallCampaign, idClient, idLead, tel){
  AJAXHandleProcessCall.data = "id_call="+idCallCampaign+"&setPickedUp=1";
  AJAXHandleProcessCall.idClient = idClient;
  AJAXHandleProcessCall.idCallCampaign = idCallCampaign;
  AJAXHandleProcessCall.idLead = idLead;
 <?php if($campaign->id){ ?>
  AJAXHandleProcessCall.idCampaign = <?php echo $campaign->id ?>;
 <?php } ?>
  AJAXHandleProcessCall.tel = tel;
  $.ajax(AJAXHandleProcessCall);
}

updateListe();
timer = setInterval('updateListe();', 5000);
//-->
</script>
<?php } // end if permission & if campaign exists ?>
<?php require(ADMIN."tail.php") ?>